<?php

declare(strict_types = 1);

namespace Canalizador\Script\Infrastructure\Repositories\OpenAI;

use Canalizador\Script\Domain\Repositories\ScriptGenerator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

final class OpenAIScriptGenerator implements ScriptGenerator
{
    private const string MODEL = 'gpt-4o-mini';

    public function generate(?string $prompt = null): string
    {
        $defaultPrompt = $prompt ?? 'Genera un guion creativo y atractivo para un video. El guion debe ser claro, estructurado y fácil de seguir.';

        $systemPrompt = 'Eres un experto escritor de guiones para videos. Tu tarea es generar guiones creativos, atractivos y bien estructurados.

FORMATO DE RESPUESTA OBLIGATORIO:
Debes responder ÚNICAMENTE con un objeto JSON válido. NO incluyas ningún texto antes o después del JSON. El formato exacto es:

{
  "introduction": "Texto de la introducción (10-15% del guion). Hook inicial que capture la atención, presentación del tema principal y establecimiento del contexto.",
  "development": "Texto del desarrollo (70-80% del guion). Puntos principales del contenido, explicaciones claras y concisas, ejemplos o casos prácticos si aplica, con transiciones fluidas entre ideas.",
  "conclusion": "Texto de la conclusión (10-15% del guion). Resumen de los puntos clave, call to action o mensaje final, y cierre memorable.",
  "full_script": "Texto completo del guion combinando las tres secciones de forma fluida y natural, listo para ser narrado."
}

REGLAS CRÍTICAS:
- Responde SOLO con el JSON, sin markdown, sin explicaciones, sin texto adicional
- El JSON debe comenzar con { y terminar con }
- El JSON debe ser válido y parseable
- Usa escape de comillas dobles dentro de los strings con \\"
- Usa un lenguaje natural y conversacional en todo el contenido
- Incluye pausas naturales marcadas con "..." cuando sea apropiado
- Mantén un tono apropiado para el tema
- El campo "full_script" debe ser el guion completo y fluido, listo para ser narrado
- Las secciones deben tener la proporción indicada (introducción 10-15%, desarrollo 70-80%, conclusión 10-15%)

IMPORTANTE: Tu respuesta debe ser SOLO JSON, nada más.';

        $userPrompt = $defaultPrompt . "\n\nResponde ÚNICAMENTE con el JSON solicitado, sin ningún texto adicional.";

        $response = Prism::text()
            ->using(Provider::OpenAI, self::MODEL)
            ->withSystemPrompt($systemPrompt)
            ->withPrompt($userPrompt)
            ->withProviderOptions([
                'response_format' => ['type' => 'json_object'],
            ])
            ->asText();

        $text = trim($response->text);
        
        $cleanedText = $text;
        if (str_starts_with($text, '```json')) {
            $cleanedText = preg_replace('/^```json\s*/', '', $text);
            $cleanedText = preg_replace('/\s*```$/', '', $cleanedText);
        } elseif (str_starts_with($text, '```')) {
            $cleanedText = preg_replace('/^```\s*/', '', $text);
            $cleanedText = preg_replace('/\s*```$/', '', $cleanedText);
        }
        
        $cleanedText = trim($cleanedText);
        
        $jsonResponse = json_decode($cleanedText, true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($jsonResponse['full_script'])) {
            return $jsonResponse['full_script'];
        }

        return $text;
    }
}
