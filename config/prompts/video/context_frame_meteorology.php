<?php

return [
    'prompt' => <<<'PROMPT'
Genera una imagen fotorrealista del presentador que aparece en la imagen de referencia, ubicado en un plató de televisión meteorológico, lista para usarse como primer fotograma de un vídeo.

=== PRESENTADOR (referencia) ===
La imagen de referencia es la única fuente de verdad para la identidad visual: misma cara, pelo, tono de piel, complexión, vestimenta acorde. Mantén el parecido exacto con la persona de la referencia.
- Nombre: {avatar_name}
- Biografía: {biography}
- Estilo: {presentation_style}
- Descripción física: {avatar_description}

=== ESCENA ===
- El presentador de pie en un plató profesional de informativos meteorológicos, con una gran pantalla LED detrás mostrando un mapa de España.
- Plano medio/americano, presentador a un lado (regla de tercios), mirando a cámara con gesto profesional y natural.
- Iluminación de plató cálida y uniforme, estética de televisión nacional. Fotorrealista, calidad broadcast.
- NADA de fondo verde/croma: el fondo debe ser el plató real con la pantalla LED y el mapa.

NO incluyas texto, marcas de agua ni gráficos superpuestos ajenos al plató.
PROMPT,
];
