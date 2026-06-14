<?php

return [
    'prompt' => <<<'PROMPT'
Genera un retrato fotorrealista de un presentador ficticio para usarlo como foto de perfil de avatar.

=== PRESENTADOR ===
- Nombre: {avatar_name}
- Biografía: {biography}
- Estilo: {presentation_style}
- Categoría: {category}

=== ESCENA ===
- Retrato de busto (plano medio corto), la persona centrada mirando a cámara con expresión natural y cercana acorde a su estilo.
- Apariencia coherente con la categoría: para gaming, un creador de contenido joven y carismático; para meteorology, un presentador del tiempo profesional y cuidado.
- Iluminación favorecedora, fondo neutro y limpio (desenfocado), estética de foto de perfil profesional.
- Fotorrealista, calidad de fotografía profesional. Una sola persona, rostro bien visible.

PERSONA FICTICIA (OBLIGATORIO):
- El presentador es una persona COMPLETAMENTE INVENTADA y genérica.
- NO debe parecerse a ninguna persona real, celebridad, actor, deportista, político ni figura pública conocida.
- Rasgos faciales comunes y neutros, sin parecido con nadie identificable.

NO incluyas texto, marcas de agua, logos ni gráficos superpuestos.
PROMPT,
];
