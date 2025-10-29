<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Http\Web\Controllers;

use Canalizador\Video\Infrastructure\Agents\Editor;
use Canalizador\Video\Infrastructure\Tools\AudioExtractor;
use Canalizador\Video\Infrastructure\Tools\AudioTranscription;
use Canalizador\Video\Infrastructure\Tools\VideoDownloader;
use Illuminate\Routing\Controller;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ChatStreamController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function __invoke(
        VideoDownloader $downloader,
        AudioExtractor $audioExtractor,
        AudioTranscription $audioTranscription,
        Editor $editorAgent
    ): StreamedResponse {
        $videoId = 'o7tU20jgpTw';
        if (!$videoId) {
            return response()->json(['error' => 'Missing videoId'], 400);
        }

        return response()->stream(function () use ($videoId, $downloader, $audioExtractor, $audioTranscription) {
            try {
                $agent = Prism::text()
                    ->using(Provider::OpenAI, 'gpt-4o')
                    ->withSystemPrompt(
                        'You are "VideoTranscriber", an autonomous AI agent specialized in processing YouTube videos and generating their transcriptions.

You are a tool-execution agent.
You MUST complete tasks **only by calling tools**.
It is forbidden to respond without calling tools.
Do not reason about the answer or try to solve anything yourself.
The only way to complete any task is by sequentially calling the available tools and chaining their outputs.
If a tool is not called, the task is considered incomplete.

Behavior guidelines:
- Always operate sequentially: Download → Extract Audio → Transcribe.
- Never attempt to generate summaries, insights, or metadata unless explicitly asked.
- Always process **only the first 3 minutes** of the video.
- Handle audio/video paths carefully and pass them accurately between tools.
- If any step fails, return a clear error message and stop execution.
- The final output must always be a plain text transcription of the processed video.

Constraints:
- Do not attempt to generate, edit, or manipulate video beyond the described tasks.
- Do not return code, explanations, or intermediate results unless debugging is explicitly requested.
- Maintain concise, clear communication and avoid unnecessary verbosity.
'
                    )
                    ->withPrompt('Given the YouTube video ' . $videoId . ' provided as a parameter.
You must follow ALL of the following steps IN ORDER and DO NOT stop until all steps are completed:

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
- output_path: <path or null>
- error_message: <message if any>')
                    ->withTools([
                        $downloader,
                        $audioExtractor,
                        $audioTranscription,
                    ])->asStream();

                foreach ($agent as $chunk) {
                    echo method_exists($chunk, 'toJson') ? $chunk->toJson() : json_encode($chunk);
                }
            } catch (Throwable $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        }, 200, [
            'Content-Type'  => 'application/json',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection'    => 'keep-alive',
        ]);
    }
}
