<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Agents;

use Canalizador\Video\Infrastructure\Tools\AudioExtractor;
use Canalizador\Video\Infrastructure\Tools\AudioTranscription;
use Canalizador\Video\Infrastructure\Tools\VideoDownloader;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Text\PendingRequest as PendingTextRequest;
use Prism\Prism\Text\Response;

final readonly class AudioTranscriptor
{
    public function __construct(
        private VideoDownloader    $downloader,
        private AudioExtractor     $audioExtractor,
        private AudioTranscription $audioTranscription,
    ) {
    }

    public function execute(string $message): PendingTextRequest
    {
        return Prism::text()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withSystemPrompt(
                'You are a helpful chat assistant. Given a message with a YouTube video, follow these steps:

STEP 1 — Immediately call `DownloaderTool` with the provided video ID.
STEP 2 — When it returns, immediately call `AudioExtractor` with the video file path and videoId.
STEP 3 — When it returns, immediately call `AudioTranscriptor` with the audio file path.

DO NOT produce a final message until STEP 3 is complete.
If you produce any output before calling all tools, the task is considered a failure.

IMPORTANT RULES:
- DO NOT stop after any intermediate step. If you stop before transcription, you have FAILED the task.
- DO NOT return any output before step 3 is complete.
- Treat the output of one tool as the input for the next.
- If any step fails, return a clear error message indicating which step failed and why.

For each step, return a JSON block with:
- step: "Downloader" | "AudioExtractor" | "Transcriptor"
- status: "success" | "error"
- message: <message if any>
- output_path: <path or null>
- error_message: <message if any>

DO NOT include explanations, logs, metadata, or intermediate results.
The transcription must be complete, readable text from the original video content.
'
            )
            ->withTools([
                $this->downloader,
                $this->audioExtractor,
                $this->audioTranscription,
            ])
            ->withPrompt($message)
            ->withMaxSteps(10);
    }
}
