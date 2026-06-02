<?php

declare(strict_types=1);

namespace Helmreel\YouTube\Video\Infrastructure\Services\YouTube;

use Google_Service_Exception;

interface YouTubeErrorExtractor
{
    public function extract(Google_Service_Exception $e): string;
}
