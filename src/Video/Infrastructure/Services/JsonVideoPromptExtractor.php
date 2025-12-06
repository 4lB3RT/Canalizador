<?php

declare(strict_types = 1);

namespace Canalizador\Video\Infrastructure\Services;

use Canalizador\Script\Domain\Entities\Script;
use Canalizador\Video\Domain\Services\VideoPromptExtractor;

final readonly class JsonVideoPromptExtractor implements VideoPromptExtractor
{
    public function extract(Script $script): string
    {
        $scriptContent = $script->content()->value();
        $jsonData = json_decode($scriptContent, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            // Usar video_prompt si existe y es completo (incluye todos los detalles del guion)
            // El nuevo prompt asegura que video_prompt respete todo el full_script
            if (isset($jsonData['video_prompt'])) {
                return $jsonData['video_prompt'];
            }
            
            // Fallback: usar full_script directamente para asegurar que se respete todo el guion
            if (isset($jsonData['full_script'])) {
                return $jsonData['full_script'];
            }
        }

        // Si no es JSON válido, retornar el contenido completo
        return $scriptContent;
    }
}
