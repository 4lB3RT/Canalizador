<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Services\YouTube;

use Google_Service_Exception;

final class GoogleYouTubeErrorExtractor implements YouTubeErrorExtractor
{
    public function extract(Google_Service_Exception $e): string
    {
        $errors = $e->getErrors();
        if (!empty($errors) && isset($errors[0]['message'])) {
            return $errors[0]['message'];
        }

        return $e->getMessage();
    }
}
