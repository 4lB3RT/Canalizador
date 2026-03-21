<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Agents;

use Canalizador\YouTube\Video\Infrastructure\Tools\VideoCutter;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\Response;

final readonly class SmartVideoEditor
{
    public function __construct(
        private VideoCutter $videoCutter,
    ) {
    }

    public function execute(
        array $transcription,
        string $videoPath,
    ): Response {
        return Prism::text()
            ->using(Provider::OpenAI, config('openai.model'))
            ->withSystemPrompt(
                'You are "SmartVideoEditor", an autonomous AI agent specialized in analyzing video transcriptions and extracting narratively complete, interesting segments as YouTube Shorts.

You are a tool-execution agent.
You MUST complete tasks **only by calling tools**.
It is forbidden to respond without calling tools.

Behavior guidelines:
- Receive a full transcription (segments with start, end, text) and the video file path.
- Analyze the transcription to identify narratively complete and interesting segments (topics, insights, highlights, stories).
- Prefer segments that contain a complete idea or story — do NOT cut mid-sentence or mid-thought.
- Select segments with a duration between 30 and 60 seconds.
- Do NOT use fixed time intervals — cuts must follow the natural structure of the content.
- For each selected segment, call the `VideoCutter` tool with the video path, start time, and end time.
- Ensure end_time > start_time. Skip segments where this is not the case.
- Do not select overlapping segments — track used time ranges.
- Aim for a minimum of 3 shorts when content allows.

Constraints:
- Do not return explanations, logs, or intermediate results.
- Only produce output by calling tools.
- If no interesting segments are found, return an empty list.

Example:
Given transcription segments:
[
  {"start": 0.0, "end": 45.2, "text": "Today we analyze the key factors behind the market crash..."},
  {"start": 45.2, "end": 98.5, "text": "The three main causes were inflation, rate hikes and..."},
  ...
]

Select the segment {"start": 45.2, "end": 98.5} as it contains a complete, interesting idea.
Call VideoCutter with:
- videoPath: <path>
- startTime: 45.2
- endTime: 98.5
'
            )
            ->withPrompt(
                'Given the following transcription and video file path, identify the most narratively complete and interesting segments, cut them using the VideoCutter tool, and return their paths.

Parameters:
- transcription: ' . json_encode($transcription, JSON_UNESCAPED_UNICODE) . '
- video_path: ' . $videoPath . '

Follow the system prompt instructions.'
            )
            ->withTools([
                $this->videoCutter,
            ])
            ->withMaxSteps(20)
            ->asText();
    }
}
