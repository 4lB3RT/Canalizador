<?php

declare(strict_types=1);

namespace Canalizador\Video\Application\Agent;

use Canalizador\Video\Domain\ValueObjects\VideoId;

class VideoEditorAgent
{
    public function transcribe(VideoId $videoId, string $transcript): array
    {
        // Possible exception: transcript may be empty or invalid
        return [
            'videoId' => $videoId->value(),
            'transcript' => $transcript,
        ];
    }
}

