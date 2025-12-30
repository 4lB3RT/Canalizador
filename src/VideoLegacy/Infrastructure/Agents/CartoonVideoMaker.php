<?php

declare(strict_types=1);

namespace Canalizador\VideoLegacy\Infrastructure\Agents;

use Canalizador\VideoLegacy\Infrastructure\Tools\AudioExtractor;
use Canalizador\VideoLegacy\Infrastructure\Tools\AudioTranscription;
use Canalizador\VideoLegacy\Infrastructure\Tools\CartoonVideoGenerator;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Text\PendingRequest as PendingTextRequest;

final readonly class CartoonVideoMaker
{
    public function __construct(
        private AudioExtractor $audioExtractor,
    ) {
    }

    public function execute(string $message): PendingTextRequest
    {
        return Prism::image()
            ->using(Provider::OpenAI, 'sora-2')
            ->withSystemPrompt(
                "Eres un agente que genera videos a partir de audios extraídos de videos.
                \n
                \nREGLAS:
                \n- El video debe ser en estilo dibujo animado.
                \n- Usa el audio proporcionado para crear la animación.
                \n- Si hay diálogos, representa a los personajes como caricaturas.
                \n- El fondo y los elementos visuales deben ser acordes al contexto del audio.
                \n- No incluyas texto en pantalla, solo animación.
                \n- Si ocurre un error, devuelve un mensaje claro."
            )
            ->withTools(
                [
                    $this->audioExtractor,
                ]
            )
            ->withPrompt($message)
            ->withMaxSteps(2);
    }
}
