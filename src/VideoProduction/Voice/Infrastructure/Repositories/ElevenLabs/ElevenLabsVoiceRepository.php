<?php

declare(strict_types=1);

namespace Helmreel\VideoProduction\Voice\Infrastructure\Repositories\ElevenLabs;

use Helmreel\Shared\Shared\Domain\Services\HttpClient;
use Helmreel\Shared\Shared\Domain\Services\HttpResponseValidator;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\DateTime;
use Helmreel\Shared\Shared\Domain\ValueObjects\Essentials\IntegerId;
use Helmreel\Shared\Shared\Domain\ValueObjects\LocalPath;
use Helmreel\VideoProduction\Voice\Domain\Entities\Voice;
use Helmreel\VideoProduction\Voice\Domain\Entities\VoiceCollection;
use Helmreel\VideoProduction\Voice\Domain\Exceptions\VoiceGenerationFailed;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceCloner;
use Helmreel\VideoProduction\Voice\Domain\Repositories\VoiceRepository;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceId;
use Helmreel\VideoProduction\Voice\Domain\ValueObjects\VoiceSettings;
use Helmreel\VideoProduction\Voice\Infrastructure\DAO\VoiceDAO;

final class ElevenLabsVoiceRepository implements VoiceRepository
{
    private const VOICES_URL = 'https://api.elevenlabs.io/v1/voices';

    public function __construct(
        private readonly VoiceCloner $voiceCloner,
        private readonly ElevenLabsTextToSpeech $textToSpeech,
        private readonly HttpClient $httpClient,
        private readonly HttpResponseValidator $responseValidator,
        private readonly ElevenLabsVoiceTransformer $transformer,
        private readonly string $apiKey,
        private readonly int $timeout,
    ) {
    }

    public function clone(string $audioPath, string $name): string
    {
        return $this->voiceCloner->clone($audioPath, $name);
    }

    public function get(): VoiceCollection
    {
        try {
            $response = $this->httpClient->get(
                self::VOICES_URL,
                ['xi-api-key' => $this->apiKey],
                $this->timeout,
            );

            $this->responseValidator->validateSuccess($response, 'ElevenLabs List Voices');
        } catch (\Throwable $e) {
            throw VoiceGenerationFailed::apiError($e->getMessage());
        }

        $json = $response->json();
        $apiVoices = is_array($json['voices'] ?? null) ? $json['voices'] : [];

        return $this->transformer->toCollection($apiVoices);
    }

    public function generateSpeech(string $text, string $platformId, VoiceSettings $settings): string
    {
        return $this->textToSpeech->synthesize($text, $platformId, $settings);
    }

    public function save(Voice $voice): void
    {
        $settings = $voice->settings();

        VoiceDAO::updateOrCreate(
            ['voice_id' => $voice->id()->value()],
            [
                'user_id' => $voice->userId()?->value(),
                'name' => $voice->name(),
                'source_audio_path' => $voice->sourceAudioPath()?->value(),
                'converted_audio_path' => $voice->convertedAudioPath()?->value(),
                'platform_id' => $voice->platformId(),
                'stability' => $settings->stability,
                'similarity_boost' => $settings->similarityBoost,
                'style' => $settings->style,
                'speed' => $settings->speed,
                'use_speaker_boost' => $settings->useSpeakerBoost,
                'created_at' => $voice->createdAt()->value(),
            ]
        );
    }

    public function findById(VoiceId $id): ?Voice
    {
        $dao = VoiceDAO::query()->find($id->value());

        if ($dao === null) {
            return null;
        }

        return $this->toEntity($dao);
    }

    /**
     * @return Voice[]
     */
    public function findByUserId(IntegerId $userId): array
    {
        return VoiceDAO::query()
            ->where('user_id', $userId->value())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (VoiceDAO $dao) => $this->toEntity($dao))
            ->all();
    }

    public function delete(VoiceId $id): void
    {
        VoiceDAO::query()->where('voice_id', $id->value())->delete();
    }

    private function toEntity(VoiceDAO $dao): Voice
    {
        return new Voice(
            id: new VoiceId($dao->voice_id),
            userId: $dao->user_id !== null ? new IntegerId((int) $dao->user_id) : null,
            name: $dao->name,
            sourceAudioPath: $dao->source_audio_path ? new LocalPath($dao->source_audio_path) : null,
            createdAt: new DateTime($dao->created_at->toDateTimeImmutable()),
            platformId: $dao->platform_id,
            convertedAudioPath: $dao->converted_audio_path ? new LocalPath($dao->converted_audio_path) : null,
            settings: new VoiceSettings(
                stability: (float) ($dao->stability ?? 0.5),
                similarityBoost: (float) ($dao->similarity_boost ?? 0.75),
                style: (float) ($dao->style ?? 0.0),
                speed: (float) ($dao->speed ?? 1.0),
                useSpeakerBoost: (bool) ($dao->use_speaker_boost ?? true),
            ),
        );
    }
}
