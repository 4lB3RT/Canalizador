<?php

declare(strict_types = 1);

namespace App\Services;

use Canalizador\Recommendation\Domain\Entities\Recommendation;
use Canalizador\Recommendation\Domain\Entities\RecommendationCollection;
use Canalizador\Recommendation\Domain\ValueObjects\Message;
use Canalizador\Recommendation\Domain\ValueObjects\Score;
use Canalizador\Recommendation\Domain\ValueObjects\Type;
use Canalizador\Recommendation\Domain\ValueObjects\Value;
use Canalizador\Recommendation\Domain\ValueObjects\ValueCollection;
use Canalizador\Video\Domain\ValueObjects\VideoId;
use OpenAI\Client as OpenAIClient;

final class YoutubeInsightsAnalysisAgent
{
    protected OpenAIClient $client;
    private const MODEL_NAME = 'gpt-4.1';

    public function __construct()
    {
        $this->client = \OpenAI::client(config('services.openai.key'));
    }

    public function ask(string $prompt, array $options = []): ?string
    {
        $systemMessage = [
            'role' => 'system',
            'content' => $options['system'] ?? 'Eres un analista experto en YouTube.'
        ];

        $userMessage = [
            'role' => 'user',
            'content' => $prompt
        ];

        try {
            $response = $this->client->chat()->create(array_merge([
                'model'    => self::MODEL_NAME,
                'messages' => [$systemMessage, $userMessage],
            ], $options));

            return $response->choices[0]->message->content ?? null;
        } catch (\Exception $e) {
            dd($e);
            \Log::error('Error in ask method: ' . $e->getMessage());

            return null;
        }

        return $response->choices[0]->message->content ?? null;
    }

    public function analyzeInsights(array $insights, ?string $instruction = null): ?string
    {
        $insightsText       = json_encode($insights, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $defaultInstruction = 'Eres un analista de datos de YouTube de nivel experto. Analiza los siguientes insights de un video y proporciona un informe detallado con:
- Diagnóstico de la situación actual del video
- Oportunidades de mejora
- Acciones concretas recomendadas (con justificación)
- Riesgos o problemas detectados
- Sugerencias para el título, descripción, miniatura y horario de publicación
- Cualquier otro aspecto relevante para maximizar el rendimiento y el crecimiento del canal
No asumas nada, justifica cada recomendación con los datos proporcionados. Si falta información, indícalo y sugiere cómo obtenerla.';
        $prompt = ($instruction ?: $defaultInstruction) . "\n\nINSIGHTS DEL VIDEO (en formato JSON):\n" . $insightsText;

        return $this->ask($prompt);
    }

    public function recommendations(VideoId $videoId, array $insights, ?string $instruction = null): RecommendationCollection
    {
        $insightsText = json_encode($insights, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $defaultInstruction = <<<EOT
Eres un analista de datos de YouTube de nivel experto y tu tarea es optimizar el rendimiento de un video para un público de España. Todas las acciones y valores sugeridos deben estar redactados en español de España, usando expresiones y términos propios de este país.

Analiza los siguientes insights y genera una lista de acciones concretas a realizar sobre el video en formato JSON.

Además, proporciona una explicación descriptiva de las recomendaciones y su razonamiento, con una extensión aproximada de 300 palabras, redactada en español de España. Esta explicación debe ayudar a comprender por qué se sugieren las acciones y cómo pueden impactar en el rendimiento del video.

Solo debes sugerir acciones de los siguientes tipos: change_title, change_description, change_tags.

Cada acción debe tener los siguientes campos:

- type: tipo de acción (solo puede ser: change_title, change_description, change_tags)
- message: justificación basada en los datos
- recommendationValue: el nuevo valor sugerido basado en los insights reales del video. IMPORTANTE: recommendationValue debe ser siempre un array de strings, incluso para change_title y change_description. Ejemplo: ["nuevo título"], ["nueva descripción"], ["etiqueta1", "etiqueta2"].
- score: puntuación de relevancia o impacto esperado (opcional)

Ejemplo:
[
  {
    "type": "change_description",
    "message": "La descripción actual no motiva a los usuarios a ver el video completo.",
    "recommendationValue": ["Aprende a crecer en YouTube en 2025"],
    "score": 0.9
  },
  {
    "type": "change_tags",
    "message": "Las etiquetas actuales no reflejan las tendencias de búsqueda en España.",
    "recommendationValue": ["youtube", "crecimiento", "2025"],
    "score": 0.8
  }
]

No expliques nada fuera del array JSON. Si no hay acciones necesarias, devuelve un array vacío. Si falta información, devuelve un array vacío.

Ten en cuenta:

- El campo `recommendationValue` debe ser siempre un array de strings, aunque solo haya un valor.
- Solo debes sugerir cambios de título, descripción y etiquetas.
- Analiza tendencias, anomalías, oportunidades y riesgos.
- Prioriza las acciones más relevantes.
- Si el video ya está optimizado, devuelve un array vacío.
EOT;

        $prompt = ($instruction ?: $defaultInstruction)
            . "\n\nINSIGHTS DEL VIDEO (en formato JSON):\n\n"
            . $insightsText
            . "\n\n"
            . "La respuesta debe ser un array JSON de recomendaciones, cada una con las siguientes claves: 'type', 'message', 'recommendationValue', 'score' (opcional), y 'id' (opcional, puede ser null). Ejemplo:\n"
            . '[{"type": "change_title", "message": "El título no contiene palabras clave relevantes.", "recommendationValue": "Nuevo título optimizado", "score": 0.9, "id": null}]';

        $response = $this->ask($prompt);
        $actions  = json_decode($response, true);

        if (!is_array($actions)) {
            return RecommendationCollection::empty();
        }

        $recommendations = RecommendationCollection::empty();
        foreach ($actions as $action) {
            $recommendations->add(
                new Recommendation(
                    videoId: $videoId,
                    message: new Message($action['message']),
                    values: new ValueCollection(
                        array_map(fn ($value) => new Value($value), $action['recommendationValue'])
                    ),
                    type: Type::tryFrom($action['type']),
                    score: new Score((float) $action['score'])
                )
            );
        }

        return $recommendations;
    }
}
