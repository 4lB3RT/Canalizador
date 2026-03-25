<?php

declare(strict_types=1);

namespace Canalizador\VideoProduction\Clip\Domain\Services;

interface VideoComposer
{
    /** Extrae el audio de un vídeo y lo guarda como MP3 */
    public function extractAudio(string $videoPath, string $outputAudioPath): void;

    /** Reemplaza el audio de un vídeo con otro fichero de audio */
    public function replaceAudio(string $videoPath, string $audioPath, string $outputPath): void;

    /** Resta un audio de otro para obtener el residuo (audio de fondo) */
    public function subtractAudio(string $originalPath, string $toSubtractPath, string $outputPath): void;

    /** Mezcla dos pistas de audio en una sola */
    public function mixAudio(string $audioPath1, string $audioPath2, string $outputPath): void;
}
