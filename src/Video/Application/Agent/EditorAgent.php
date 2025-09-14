<?php

namespace Canalizador\Video\Application\Agent;

use Canalizador\Video\Application\Service\VideoTranscriptionService;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

readonly class EditorAgent
{
    public function __construct(private VideoTranscriptionService $transcriber)
    {
    }

    public function run(string $videoId, int $maxSegments = 10): array
    {
        $payload  = $this->transcriber->getTranscription(new VideoId($videoId));
        $words    = $payload['words_forcefit'] ?? $payload['words'] ?? [];
        $duration = (float) ($payload['duration'] ?? 0.0);
        $text     = trim(implode(' ', array_map(fn ($w) => $w['w'], $words)));

        $segmentSchema = new ObjectSchema(
            name: 'segment',
            description: 'Clip propuesto',
            properties: [
                new StringSchema('title', 'Título del clip'),
                new StringSchema('description', 'Descripcion extensa del clip'),
                new StringSchema('start_sec', 'Inicio en minutos'),
                new StringSchema('end_sec', 'Fin en minutos'),
                new StringSchema('reason', 'Motivo/gancho'),
                new NumberSchema('confidence', 'Confianza entre 0 y 1'),
            ],
            requiredFields: ['title', 'description', 'start_sec', 'end_sec', 'reason', 'confidence']
        );

        $resultSchema = new ObjectSchema(
            name: 'SegmentationResult',
            description: 'Lista de clips',
            properties: [
                new ArraySchema('segments', 'Clips propuestos', $segmentSchema),
            ],
            requiredFields: ['segments']
        );

        $system = implode("\n", [
            'Eres un editor de vídeo.',
            'Elige clips de 20 a 60 segundos con inicio y cierre coherentes.',
            'Evita redundancias y prioriza fragmentos con gancho y una idea completa.',
            'Responde exclusivamente en JSON válido del esquema.',
        ]);

        $input = [
            'video_id'     => $videoId,
            'duration_sec' => $duration,
            'constraints'  => ['min_sec' => 20, 'max_sec' => 60, 'max_segments' => $maxSegments],
            'text'         => $text,
            'words'        => $words,
            'language'     => 'es',
        ];

        $agent = Prism::structured()
            ->using(Provider::OpenAI, 'gpt-4o')
            ->withSchema($resultSchema)
            ->withSystemPrompt($system)
            ->withPrompt(json_encode($input, JSON_UNESCAPED_UNICODE));

        dd($agent->asStructured()->structured);

        return is_array($result) ? $result : ['segments' => []];
    }
}
