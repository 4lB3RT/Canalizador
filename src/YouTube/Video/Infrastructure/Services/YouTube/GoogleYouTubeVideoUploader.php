<?php

declare(strict_types = 1);

namespace Helmreel\YouTube\Video\Infrastructure\Services\YouTube;

use Helmreel\VideoProduction\Video\Domain\Services\FileSystem;
use Helmreel\YouTube\Video\Domain\Exceptions\YouTubeOperationFailed;
use Google\Service\Exception;
use Google_Client;
use Google_Http_MediaFileUpload;
use Google_Service_Exception;
use Google_Service_YouTube;
use Google_Service_YouTube_Video;

final readonly class GoogleYouTubeVideoUploader implements YouTubeVideoUploader
{
    public function __construct(
        private FileSystem $fileSystem
    ) {
    }

    /**
     * @throws YouTubeOperationFailed
     * @throws Exception
     */
    public function upload(
        Google_Client $client,
        Google_Service_YouTube $service,
        Google_Service_YouTube_Video $video,
        string $videoPath,
        int $chunkSize
    ): array
    {
        $videoFileSize = $this->fileSystem->size($videoPath);
        $mimeType      = $this->fileSystem->mimeType($videoPath);

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
                return [
                    'id' => $status['id'],
                    'channel_id' => $status['snippet']['channelId'] ?? null,
                ];
            }

            throw YouTubeOperationFailed::apiError('Failed to upload video to YouTube');
        } catch (Google_Service_Exception $e) {
            throw YouTubeOperationFailed::apiError("YouTube API error: {$e->getMessage()}");
        } catch (\Exception $e) {
            throw YouTubeOperationFailed::apiError("YouTube upload error: {$e->getMessage()}");
        } finally {
            $this->fileSystem->close($handle);
        }
    }
}
