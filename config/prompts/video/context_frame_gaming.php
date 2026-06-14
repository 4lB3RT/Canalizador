<?php

return [
    'prompt' => <<<'PROMPT'
Genera una imagen fotorrealista del presentador que aparece en la imagen de referencia, ubicado en un setup de gaming moderno, lista para usarse como primer fotograma de un vídeo.

=== PRESENTADOR (referencia) ===
La imagen de referencia es la única fuente de verdad para la identidad visual: misma cara, pelo, tono de piel, complexión. Mantén el parecido exacto con la persona de la referencia.
- Nombre: {avatar_name}
- Biografía: {biography}
- Estilo: {presentation_style}
- Descripción física: {avatar_description}

=== ESCENA ===
- Plano medio (medium close-up), el presentador centrado mirando a cámara con expresión natural y cercana.
- Fondo: setup de gaming real con monitor(es), teclado RGB, luces ambientales de colores (azul/morado). Profundidad de campo suave (bokeh) en el fondo.
- Iluminación cálida y favorecedora con acentos RGB. Estética de creador de contenido.
- Fotorrealista, calidad de fotografía profesional. NADA de fondo verde/croma: el fondo debe ser el setup real.

NO incluyas texto, marcas de agua ni gráficos superpuestos.
PROMPT,
];
