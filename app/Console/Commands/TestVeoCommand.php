<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class TestVeoCommand extends Command
{
    protected $signature = 'veo:test';
    protected $description = 'Test Veo 3.1 video generation with a simple prompt';

    private const string DEFAULT_PROMPT = 'A golden retriever puppy running through a sunny meadow with flowers, cinematic lighting, slow motion';

    private const string API_BASE = 'https://generativelanguage.googleapis.com/v1beta';
    private const string MODEL = 'veo-3.1-generate-preview';

    public function handle(): int
    {
        $apiKey = config('services.google.veo_api_key');

        if (empty($apiKey)) {
            $this->error('GOOGLE_VEO_API_KEY not configured in .env');
            return 1;
        }

        $prompt = self::DEFAULT_PROMPT;
        $this->info("Prompt: {$prompt}");
        $this->newLine();

        // 1. Iniciar generación
        $this->info('1. Enviando request a Veo...');

        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post(self::API_BASE . '/models/' . self::MODEL . ':predictLongRunning', [
            'instances' => [
                ['prompt' => $prompt],
            ],
            'parameters' => [
                'aspectRatio' => '16:9',
                'resolution' => '720p',
                'durationSeconds' => 4,
            ],
        ]);

        if (!$response->successful()) {
            $this->error('Error en request inicial:');
            $this->error($response->body());
            return 1;
        }

        $data = $response->json();
        $this->info('Response: ' . json_encode($data, JSON_PRETTY_PRINT));

        if (!isset($data['name'])) {
            $this->error('No se recibió operation name');
            return 1;
        }

        $operationName = $data['name'];
        $this->info("Operation: {$operationName}");
        $this->newLine();

        // 2. Polling
        $this->info('2. Polling para obtener resultado...');

        $maxAttempts = 60;
        $interval = 10;

        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep($interval);

            $statusResponse = Http::withHeaders([
                'x-goog-api-key' => $apiKey,
            ])->get(self::API_BASE . '/' . $operationName);

            if (!$statusResponse->successful()) {
                $this->warn("Polling attempt {$i}: Error - " . $statusResponse->status());
                continue;
            }

            $statusData = $statusResponse->json();

            if (isset($statusData['done']) && $statusData['done'] === true) {
                $this->info('Video generado!');
                $this->info('Response: ' . json_encode($statusData, JSON_PRETTY_PRINT));

                if (isset($statusData['error'])) {
                    $this->error('Error: ' . ($statusData['error']['message'] ?? 'Unknown'));
                    return 1;
                }

                // 3. Descargar video
                $videoUri = $this->extractVideoUri($statusData);

                if ($videoUri) {
                    $this->newLine();
                    $this->info('3. Descargando video...');
                    $this->downloadVideo($videoUri, $apiKey);
                }

                return 0;
            }

            $this->line("Polling attempt {$i}: En progreso...");
        }

        $this->error('Timeout: El video no se generó en el tiempo esperado');
        return 1;
    }

    private function extractVideoUri(array $data): ?string
    {
        // Intentar diferentes estructuras de respuesta
        $paths = [
            ['response', 'generateVideoResponse', 'generatedSamples', 0, 'video', 'uri'],
            ['response', 'generatedSamples', 0, 'video', 'uri'],
            ['response', 'video', 'uri'],
        ];

        foreach ($paths as $path) {
            $value = $data;
            foreach ($path as $key) {
                if (!isset($value[$key])) {
                    break;
                }
                $value = $value[$key];
            }
            if (is_string($value)) {
                $this->info("Video URI: {$value}");
                return $value;
            }
        }

        $this->warn('No se encontró URI del video en la respuesta');
        return null;
    }

    private function downloadVideo(string $uri, string $apiKey): void
    {
        $response = Http::withHeaders([
            'x-goog-api-key' => $apiKey,
        ])->timeout(120)->get($uri);

        if (!$response->successful()) {
            $this->error('Error descargando video: ' . $response->status());
            return;
        }

        $outputDir = storage_path('app/videos');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $filename = 'veo_test_' . date('Y-m-d_His') . '.mp4';
        $path = $outputDir . '/' . $filename;

        File::put($path, $response->body());
        $this->info("Video guardado: {$path}");
    }
}
