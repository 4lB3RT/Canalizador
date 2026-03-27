<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Services;

use Canalizador\Shared\Shared\Domain\ValueObjects\Essentials\LocalPath;
use Canalizador\YouTube\Video\Domain\Exceptions\VideoFragmentationFailed;
use Canalizador\YouTube\Video\Domain\Repositories\SmartVideoFragmenter;
use Canalizador\YouTube\Video\Infrastructure\Agents\SmartVideoEditor;
use Prism\Prism\ValueObjects\ToolResult;

final class PrismSmartVideoFragmenter implements SmartVideoFragmenter
{
    public function __construct(
        private readonly SmartVideoEditor $smartVideoEditor,
    ) {
    }

    /**
     * @param  array<int, array{start: float, end: float, text: string}> $transcription
     * @return LocalPath[]
     * @throws VideoFragmentationFailed
     */
    public function fragment(LocalPath $videoPath, array $transcription): array
    {
        if (empty($transcription)) {
            throw VideoFragmentationFailed::emptyResult($videoPath->value());
        }

        $response = $this->smartVideoEditor->execute($transcription, $videoPath->value());

        $paths = $this->extractPathsFromToolResults($response->toolResults ?? []);

        if (empty($paths)) {
            throw VideoFragmentationFailed::emptyResult($videoPath->value());
        }

        $paths = array_values(array_unique($paths));

        return array_map(
            static fn (string $path) => new LocalPath($path),
            $paths
        );
    }

    private function extractPathsFromToolResults(array $toolResults): array
    {
        $paths = [];

        foreach ($toolResults as $result) {
            if (!$result instanceof ToolResult) {
                continue;
            }

            $value = $result->result;

            if (!is_string($value)) {
                continue;
            }

            $path = trim($value);
            if (str_ends_with($path, '.mp4') && is_file($path) && filesize($path) > 0) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
