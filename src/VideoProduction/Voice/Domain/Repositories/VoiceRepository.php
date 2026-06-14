<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Domain\Repositories;

use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\VideoProduction\Voice\Domain\Entities\Voice;
use Helmreel\VideoProduction\Voice\Domain\Entities\VoiceCollection;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceSettings;

interface VoiceRepository
{
    public function save(Voice $voice): void;

    public function findById(VoiceId $id): ?Voice;

    /**
     * @return Voice[]
     */
    public function findByUserId(IntegerId $userId): array;

    public function delete(VoiceId $id): void;

    public function clone(string $audioPath, string $name): string;

    public function get(): VoiceCollection;

    public function generateSpeech(string $text, string $platformId, VoiceSettings $settings): string;
}
