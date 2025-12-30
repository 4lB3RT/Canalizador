<?php

declare(strict_types=1);

namespace Canalizador\Video\Infrastructure\Services\YouTube;

use Canalizador\Video\Domain\Exceptions\VideoGenerationFailed;
use Canalizador\Video\Domain\Services\FileSystem;
use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Exception;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;

final class GoogleYouTubeVideoUploader implements YouTubeVideoUploader
{
    public function __construct(
        private readonly FileSystem $fileSystem
    ) {
    }

    /**
     * @throws VideoGenerationFailed
     */
    public function upload(
        Google_Client $client,
        Google_Service_YouTube $service,
        Google_Service_YouTube_Video $video,
        string $videoPath,
        int $chunkSize
    ): string {
        $videoFileSize = $this->fileSystem->size($videoPath);
        $mimeType = $this->fileSystem->mimeType($videoPath);

        $client->setDefer(true);
        $insertRequest = $service->videos->insert('snippet,status', $video);

        $media = new Google_Http_MediaFileUpload(
            $client,
            $insertRequest,
            $mimeType,
            null,
            true,
            $chunkSize
        );

        $media->setFileSize($videoFileSize);

        $handle = $this->fileSystem->openReadStream($videoPath);

        try {
            $status = false;
            while (!$status && !$this->fileSystem->eof($handle)) {
                $chunk = $this->fileSystem->readChunk($handle, $chunkSize);
                if ($chunk !== false) {
                    $status = $media->nextChunk($chunk);
                }
            }

            $client->setDefer(false);

            if ($status && isset($status['id'])) {
                return $status['id'];
            }

            throw VideoGenerationFailed::apiError('Failed to upload video to YouTube');
        } catch (Google_Service_Exception $e) {
            throw VideoGenerationFailed::apiError("YouTube API error: {$e->getMessage()}");
        } catch (\Exception $e) {
            throw VideoGenerationFailed::apiError("YouTube upload error: {$e->getMessage()}");
        } finally {
            $this->fileSystem->close($handle);
        }
    }
}
