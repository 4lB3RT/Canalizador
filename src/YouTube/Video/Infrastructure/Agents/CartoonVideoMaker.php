<?php

declare(strict_types=1);

namespace Canalizador\YouTube\Video\Infrastructure\Agents;

use Canalizador\YouTube\Video\Infrastructure\Tools\AudioExtractor;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Text\PendingRequest as PendingTextRequest;

final readonly class CartoonVideoMaker
{
    public function __construct(
        private AudioExtractor $audioExtractor,
    ) {
    }

    public function execute(string $message): PendingTextRequest
    {
        return Prism::text()
            ->using(Provider::OpenAI, 'sora-2')
            ->withSystemPrompt(
                'Eres un agente que genera videos a partir de audios extraídos de videos.

REGLAS:
- El video debe ser en estilo dibujo animado.
- Usa el audio proporcionado para crear la animación.
- Si hay diálogos, representa a los personajes como caricaturas.
- El fondo y los elementos visuales deben ser acordes al contexto del audio.
- No incluyas texto en pantalla, solo animación.
- Si ocurre un error, devuelve un mensaje claro.'
            )
            ->withTools([
                $this->audioExtractor,
            ])
            ->withPrompt($message)
            ->withMaxSteps(2);
    }
}
