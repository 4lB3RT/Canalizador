<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Shared\Domain\Services;

use Google_Client;
use Google_Service_YouTube;

interface YouTubeServiceFactory
{
    public function create(Google_Client $client): Google_Service_YouTube;
}
