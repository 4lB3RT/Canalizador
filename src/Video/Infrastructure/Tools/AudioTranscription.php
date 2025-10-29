<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Tools;

use Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Tool;
use Prism\Prism\ValueObjects\Media\Audio;
use Throwable;

final class AudioTranscription extends Tool
{
    private const string JSON_PATH = '/tmp/transcriptions/';

    public function __construct()
    {
        parent::__construct();

        $this->as('AudioTranscription')
            ->for('Transcribe an audio file and return a structured JSON with segments and words with timestamps.')
            ->withStringParameter('audioPath', 'The local file path to the audio file to transcribe.')
            ->using($this);
    }

    public function __invoke(string $audioPath): string
    {
        $cacheFile = self::JSON_PATH . md5($audioPath) . '_transcription.json';

        if (is_file($cacheFile)) {;
            $cached = (string) file_get_contents($cacheFile);

            return $cached;
        }

        if (!is_dir(self::JSON_PATH)) {
            mkdir(self::JSON_PATH, 0777, true);
        }

        try {
            $audio = Prism::audio()
                ->using(Provider::OpenAI, self::MODEL)
                ->withInput(Audio::fromLocalPath($audioPath))
                ->withProviderOptions([
                    'language'                => self::AUDIO_LANGUAGE,
                    'timestamp_granularities' => ['segment', 'word'],
                    'response_format'         => 'verbose_json',
                ])
                ->withClientOptions(['timeout' => 600])
                ->asText();

            $data     = json_decode(json_encode($audio->additionalContent), true);
            dd($data);
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

            $normalized = json_encode($normalized, JSON_UNESCAPED_UNICODE);

            file_put_contents($cacheFile, $normalized);

            return $normalized;
        } catch (Throwable $e) {
            dd($e);
            Log::error('Error during transcription: ' . $e->getMessage(), [
                'audioPath' => $audioPath,
                'trace'     => $e->getTraceAsString(),
            ]);

            return '{"error": "Transcription failed: ' . addslashes($e->getMessage()) . '"}';
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
