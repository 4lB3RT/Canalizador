<?php

declare(strict_types = 1);

namespace Canalizador\Video\Application\Service;

use Canalizador\Video\Domain\ValueObjects\VideoId;
use OpenAI\Client as OpenAIClient;

class VideoTranscriptionService
{
    private const WHISPER_MODEL  = 'whisper-1';
    private const AUDIO_FORMAT   = 'mp3';
    private const AUDIO_LANGUAGE = 'es';
    private const AUDIO_PATH     = '/tmp/';

    private const JSON_PATH    = '/tmp/';
    private const CACHE_PREFIX = 'yt_audio_';

    protected OpenAIClient $client;

    public function __construct()
    {
        $this->client = \OpenAI::client(config('services.openai.key'));
    }

    public function getTranscription(VideoId $videoId): array
    {
        $audioPath  = $this->downloadAudioPreviewFromYoutube($videoId, 3);
        $transcript = $this->transcribeWithWhisper($audioPath);

        return $transcript;
    }

    public function downloadAudioPreviewFromYoutube(VideoId $videoId, int $minutes): string
    {
        $cacheKey   = md5($videoId->value() . "_{$minutes}min");
        $outputPath = self::AUDIO_PATH . self::CACHE_PREFIX . $cacheKey . '_preview.' . self::AUDIO_FORMAT;

        if (file_exists($outputPath) && filesize($outputPath) > 0) {
            return $outputPath;
        }

        $duration    = max(1, $minutes) * 60;
        $mm          = (int) floor($duration / 60);
        $ss          = (int) ($duration % 60);
        $durationStr = sprintf('%02d:%02d', $mm, $ss);

        $url = 'https://www.youtube.com/watch?v=' . rawurlencode($videoId->value());

        $cmd = sprintf(
            'yt-dlp --extract-audio --audio-format %s --download-sections %s -o %s %s',
            escapeshellarg(self::AUDIO_FORMAT),
            escapeshellarg("*00:00-{$durationStr}"),
            escapeshellarg($outputPath),
            escapeshellarg($url)
        );

        try {
            $output     = [];
            $resultCode = 0;
            exec($cmd, $output, $resultCode);
        } catch (\Throwable $e) {
            \Log::error('Error downloading audio preview: ' . $e->getMessage(), [
                'videoId' => $videoId->value(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to download audio preview for video ID: ' . $videoId->value());
        }

        if ($resultCode !== 0 || !is_file($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Audio preview download failed or file is invalid for video ID: ' . $videoId->value());
        }

        return $outputPath;
    }

    public function transcribeWithWhisper(string $audioPath): array
    {
        $cacheFile = self::JSON_PATH . md5($audioPath) . '_transcription.json';

        if (is_file($cacheFile)) {
            $cached = json_decode((string) file_get_contents($cacheFile), true);

            return is_array($cached) ? $cached : [];
        }

        try {
            $resp = $this->client->audio()->transcribe([
                'model'                   => self::WHISPER_MODEL,
                'file'                    => fopen($audioPath, 'rb'),
                'response_format'         => 'verbose_json',
                'language'                => self::AUDIO_LANGUAGE,
                'prompt'                  => 'Transcribe el audio al español con máxima precisión.',
                'timestamp_granularities' => ['segment', 'word'],
            ]);

            $data     = json_decode(json_encode($resp), true);
            $segments = $data['segments'] ?? [];
            $words    = $this->extractWordsFromSegments($segments);

            if (empty($words) && !empty($segments)) {
                $words = $this->approximateWordsFromSegments($segments, 0.9);
            }

            $wordSegmentsForceFit = [];
            foreach ($segments as $idx => $seg) {
                $slice                      = $this->sliceWordsForSegment($words, (float) $seg['start'], (float) $seg['end']);
                $fixed                      = $this->forceFitWordsToSegment($seg, $slice);
                $wordSegmentsForceFit[$idx] = $fixed;
            }

            $wordsForceFit = [];
            foreach ($wordSegmentsForceFit as $arr) {
                foreach ($arr as $w) {
                    $wordsForceFit[] = $w;
                }
            }

            $normalized = [
                'segments'               => $segments,
                'words'                  => $words,
                'words_forcefit'         => $wordsForceFit,
                'word_segments_forcefit' => $wordSegmentsForceFit,
                'duration'               => $this->guessDuration($segments, (float) ($data['duration'] ?? 0.0)),
            ];

            file_put_contents($cacheFile, json_encode($normalized, JSON_UNESCAPED_UNICODE));

            return $normalized;
        } catch (\Throwable $e) {
            \Log::error('Error during transcription: ' . $e->getMessage(), [
                'audioPath' => $audioPath,
                'trace'     => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    private function guessDuration(array $segments, float $fallback): float
    {
        if ($fallback > 0) {
            return $fallback;
        }
        $lastEnd = 0.0;
        foreach ($segments as $seg) {
            $lastEnd = max($lastEnd, (float) ($seg['end'] ?? 0.0));
        }

        return $lastEnd;
    }

    private function extractWordsFromSegments(array $segments): array
    {
        $words = [];
        foreach ($segments as $seg) {
            if (!empty($seg['words']) && is_array($seg['words'])) {
                foreach ($seg['words'] as $w) {
                    if (isset($w['word'], $w['start'], $w['end'])) {
                        $token = trim((string) $w['word']);
                        if ($token === '') {
                            continue;
                        }
                        $ws = (float) $w['start'];
                        $we = (float) $w['end'];
                        if ($we <= $ws) {
                            continue;
                        }
                        $words[] = ['w' => $token, 'start' => $ws, 'end' => $we];
                    }
                }
            }
        }
        usort($words, fn ($a, $b) => $a['start'] <=> $b['start']);

        return $words;
    }

    private function approximateWordsFromSegments(array $segments, float $alpha = 0.9): array
    {
        $approx = [];
        foreach ($segments as $seg) {
            $text  = (string) ($seg['text'] ?? '');
            $start = (float) ($seg['start'] ?? 0.0);
            $end   = (float) ($seg['end'] ?? 0.0);
            if ($text === '' || $end <= $start) {
                continue;
            }

            $approx = array_merge(
                $approx,
                $this->approximateWordTimestamps($start, $end, $text, $alpha)
            );
        }

        return $approx;
    }

    private function sliceWordsForSegment(array $words, float $segStart, float $segEnd, float $pad = 0.02): array
    {
        $out = [];
        foreach ($words as $w) {
            $ws = (float) $w['start'];
            $we = (float) $w['end'];
            if ($we < $segStart - $pad) {
                continue;
            }
            if ($ws > $segEnd + $pad) {
                break;
            }
            if ($we > $segStart && $ws < $segEnd) {
                $out[] = $w;
            }
        }
        // ordenar
        usort($out, fn ($a, $b) => $a['start'] <=> $b['start']);

        return $out;
    }

    private function forceFitWordsToSegment(array $segment, array $segWords, float $minDur = 0.01): array
    {
        $segStart = (float) ($segment['start'] ?? 0.0);
        $segEnd   = (float) ($segment['end'] ?? 0.0);
        $segDur   = max(0.001, $segEnd - $segStart);

        if (empty($segWords)) {
            return [['w' => '[silencio]', 'start' => $segStart, 'end' => $segEnd]];
        }

        $base    = [];
        $sumBase = 0.0;
        foreach ($segWords as $w) {
            $ws = max($segStart, min($segEnd, (float) $w['start']));
            $we = max($segStart, min($segEnd, (float) $w['end']));
            if ($we <= $ws) {
                $we = $ws + $minDur;
                if ($we > $segEnd) {
                    $we = $segEnd;
                }
            }
            $dur    = max($minDur, $we - $ws);
            $base[] = ['w' => (string) $w['w'], 'dur' => $dur];
            $sumBase += $dur;
        }
        if ($sumBase <= 0.0) {
            $uniform = $segDur / count($base);
            foreach ($base as &$b) {
                $b['dur'] = $uniform;
            } unset($b);
            $sumBase = $segDur;
        }

        $factor = $segDur / $sumBase;

        $out = [];
        $t   = $segStart;
        foreach ($base as $i => $b) {
            $dur = max($minDur, $b['dur'] * $factor);
            $end = $t + $dur;
            if ($i === count($base) - 1) {
                $end = $segEnd;
            }
            $out[] = ['w' => $b['w'], 'start' => round($t, 3), 'end' => round($end, 3)];
            $t     = $end;
        }

        $drift = $segEnd - end($out)['end'];
        if (abs($drift) >= 0.001) {
            $out[count($out) - 1]['end'] = round($segEnd, 3);
        }

        for ($i = 1; $i < count($out); $i++) {
            if ($out[$i]['start'] < $out[$i - 1]['end']) {
                $out[$i]['start'] = $out[$i - 1]['end'];
                if ($out[$i]['end'] < $out[$i]['start']) {
                    $out[$i]['end'] = $out[$i]['start'];
                }
            }
        }

        return $out;
    }

    private function approximateWordTimestamps(
        float $startSeg,
        float $endSeg,
        string $text,
        float $alpha = 0.9,
        array $pauseMap = [',' => 0.12, '.' => 0.25, '!' => 0.25, '?' => 0.25, ':' => 0.15, ';' => 0.15]
    ): array {
        $duration = max(0.01, $endSeg - $startSeg);

        $rawTokens = preg_split('/(\s+)/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words     = [];
        foreach ($rawTokens as $tk) {
            if (preg_match('/^(.+?)([,\.\!\?\:\;]+)$/u', $tk, $m)) {
                $core  = $m[1];
                $punct = $m[2];
                if ($core !== '') {
                    $words[] = $core;
                }
                $chars = preg_split('//u', $punct, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                foreach ($chars as $c) {
                    $words[] = $c;
                }
            } else {
                $words[] = $tk;
            }
        }
        if (empty($words)) {
            return [];
        }

        $pausesIdx = [];
        $P         = 0.0;
        foreach ($words as $i => $w) {
            if (isset($pauseMap[$w])) {
                $pausesIdx[$i] = (float) $pauseMap[$w];
                $P += (float) $pauseMap[$w];
            }
        }
        $P       = min($P, max(0.0, $duration * 0.35));
        $weights = [];
        $W       = 0.0;
        foreach ($words as $i => $w) {
            if (isset($pauseMap[$w])) {
                $weights[$i] = 0.0;
                continue;
            }
            $len         = max(1, mb_strlen(preg_replace('/[^\p{L}\p{N}]/u', '', $w)));
            $wi          = pow($len, $alpha);
            $weights[$i] = $wi;
            $W += $wi;
        }
        if ($W <= 0.0) {
            $W = 1.0;
        }

        $allocSpeech = max(0.0, $duration - $P);

        $timeline = [];
        $t        = $startSeg;
        foreach ($words as $i => $w) {
            if (isset($pauseMap[$w])) {
                $pause      = $pausesIdx[$i] ?? 0.0;
                $tEnd       = min($endSeg, $t + $pause);
                $timeline[] = ['w' => $w, 'start' => $t, 'end' => $tEnd, 'is_pause' => true];
                $t          = $tEnd;
                continue;
            }
            $dur_i      = ($weights[$i] / $W) * $allocSpeech;
            $tEnd       = min($endSeg, $t + $dur_i);
            $timeline[] = ['w' => $w, 'start' => $t, 'end' => $tEnd, 'is_pause' => false];
            $t          = $tEnd;
        }

        $lastEnd = $timeline[count($timeline) - 1]['end'] ?? $startSeg;
        $drift   = $endSeg - $lastEnd;
        if (abs($drift) > 1e-3) {
            $speechDur = 0.0;
            foreach ($timeline as $w) {
                if (!$w['is_pause']) {
                    $speechDur += ($w['end'] - $w['start']);
                }
            }
            if ($speechDur > 0.0) {
                $factor  = ($speechDur + $drift) / $speechDur;
                $tCursor = $startSeg;
                foreach ($timeline as &$w) {
                    if ($w['is_pause']) {
                        $tCursor = $w['end'];
                        continue;
                    }
                    $len        = ($w['end'] - $w['start']) * $factor;
                    $w['start'] = $tCursor;
                    $w['end']   = min($endSeg, $tCursor + $len);
                    $tCursor    = $w['end'];
                }
                unset($w);
            } else {
                $uniform = $duration / max(1, count($timeline));
                $tCursor = $startSeg;
                foreach ($timeline as &$w) {
                    $w['start'] = $tCursor;
                    $w['end']   = min($endSeg, $tCursor + $uniform);
                    $tCursor    = $w['end'];
                }
                unset($w);
            }
        }

        $out = [];
        foreach ($timeline as $w) {
            if (!$w['is_pause']) {
                $out[] = ['w' => $w['w'], 'start' => round($w['start'], 3), 'end' => round($w['end'], 3)];
            }
        }

        return $out;
    }
}
