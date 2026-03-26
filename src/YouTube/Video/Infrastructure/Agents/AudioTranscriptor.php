<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Agents;

use Canalizador\YouTube\Video\Infrastructure\Tools\AudioExtractor;
use Canalizador\YouTube\Video\Infrastructure\Tools\AudioTranscription;
use Canalizador\YouTube\Video\Infrastructure\Tools\VideoDownloader;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\PendingRequest as PendingTextRequest;

final readonly class AudioTranscriptor
{
    public function __construct(
        private VideoDownloader    $videoDownloader,
        private AudioExtractor     $audioExtractor,
        private AudioTranscription $audioTranscription,
    ) {
    }

    public function execute(string $message): PendingTextRequest
    {
        return Prism::text()
            ->using(Provider::OpenAI, config('openai.model'))
            ->withSystemPrompt(
                'You are a helpful chat assistant. Given a message with a YouTube video, follow these steps:

STEP 1 — Call `VideoDownloader` with the provided video ID. This tool downloads the YouTube video and returns the local file path.
STEP 2 — Call `AudioExtractor` with the local video file path. This tool extracts the audio from the video and returns the local audio file path.
STEP 3 — Call `AudioTranscription` with the local audio file path. This tool transcribes the audio and returns a structured JSON with segments and words with timestamps.

DO NOT produce a final message until STEP 3 is complete.
If you produce any output before calling all tools, the task is considered a failure.

IMPORTANT RULES:
- DO NOT stop after any intermediate step. If you stop before transcription, you have FAILED the task.
- DO NOT return any output before step 3 is complete.
- Treat the output of one tool as the input for the next.
- If any step fails, return a clear error message indicating which step failed and why.

For each step, return a JSON block with:
- step: "VideoDownloader" | "AudioExtractor" | "AudioTranscription"
- status: "success" | "error"
- output_path: <path or null>
- error_message: <message if any>

DO NOT include explanations, logs, metadata, or intermediate results.
The transcription must be complete, readable text from the original video content.
'
            )
            ->withTools([
                $this->videoDownloader,
                $this->audioExtractor,
                $this->audioTranscription,
            ])
            ->withPrompt($message)
            ->withMaxSteps(10);
    }
}
