<?php

return [
    'prompt' => <<<'PROMPT'
DIRECCIÓN TÉCNICA — VÍDEO METEOROLÓGICO

[CINEMATOGRAPHY]
Locked-off shot. Static camera on tripod, fixed frame throughout.
Single continuous take. Deep focus f/8 — everything sharp from
presenter to LED screen. 50mm equivalent lens. Color temperature
5600K broadcast daylight. Even key + fill lighting, warm and diffused.
Subtle backlight for presenter edge separation. 24fps.

[SUBJECT — CHARACTER CONSISTENCY]
Se proporcionan 3 imágenes de referencia:
1. PRESENTADORA: Mujer sobre fondo verde croma. Mantener aspecto
   y vestimenta exactos — identical face, hair, skin tone, build,
   outfit across the entire video. La referencia es la única fuente
   de verdad para la identidad visual del personaje.
2. PLATÓ: Estudio profesional de televisión con pantalla LED.
3. MAPA: Pre-rendered broadcast graphic de España.

[COMPOSITING — CHROMA KEY]
Sustituir el verde (#00FF00) del fondo de la presentadora por el plató.
Edges nítidos en pelo, piel y ropa — clean matte, zero green spill.
Sustituir el verde (#00FF00) de la pantalla LED del plató por el MAPA.
El mapa llena la pantalla como una LED real emitiendo el gráfico.
El resultado final = emisión real de informativos meteorológicos de TV nacional.

[SCREEN — STATIC BROADCAST GRAPHIC]
La pantalla LED muestra EXCLUSIVAMENTE el gráfico del mapa proporcionado,
reproducido píxel a píxel tal como se entrega. Es un frozen static overlay —
el contenido completo y único de la pantalla. Toda la información meteorológica
la comunica la presentadora verbalmente. La superficie de la pantalla conserva
iluminación uniforme. El contenido geográfico (ciudades, fronteras, posiciones)
permanece exactamente como en la imagen fuente.

[ACTION — PRESENTER DIRECTION]
Gestos naturales y profesionales. Lenguaje corporal de presentadora de televisión.
Cuando menciona una ciudad, señalar hacia su posición correcta en la pantalla del plató.
Usar las coordenadas de pantalla de abajo para saber dónde está cada ciudad.
Desplazarse lateralmente para no tapar la zona que comenta — moverse a la izquierda
cuando habla de ciudades del este, y viceversa.

POSICIONES EN PANTALLA — Dónde señala la presentadora:
La pantalla LED del plató muestra el mapa. Disposición visual de las 15 ciudades en pantalla:

    0%       20%       40%       60%       80%      100%
    |         |         |         |         |         |
 0%-+─────────+─────────+─────────+─────────+─────────+
    | A Coruña           Bilbao                        |
10%-+                                                  +
    | Vigo                                             |
20%-+                                                  +
    |          Valladolid  Zaragoza          Barcelona  |
30%-+                                                  +
    |                  MADRID                    Palma  |
40%-+                                                  +
    |                            Valencia              |
50%-+                                                  +
    |          Córdoba           Alicante              |
60%-+    Sevilla              Murcia                    +
    |                                                  |
70%-+              Málaga                              +
    |                                                  |
80%-+                                                  +
    |                                                  |
90%-+ Las Palmas                                       +
    | (Canarias)                                       |
100%+─────────+─────────+─────────+─────────+─────────+

GESTOS DE BARRIDO entre ciudades — la mano sigue la dirección geográfica real:
- Madrid → Barcelona: diagonal ARRIBA-DERECHA
- Madrid → Sevilla: diagonal ABAJO-IZQUIERDA
- Madrid → Valencia: horizontal hacia la DERECHA
- Barcelona → Valencia: descenso por la costa DERECHA
- Bilbao → A Coruña: barrido horizontal hacia la IZQUIERDA
- Sevilla → Málaga: gesto corto hacia la DERECHA
- Valencia → Alicante → Murcia: descenso por la costa DERECHA

[AUDIO]
The presenter speaks with a clear, professional broadcast voice in European Spanish.
Ambient sound: quiet broadcast studio with subtle room tone.

[STYLE & AMBIANCE]
Photorealistic, national television broadcast quality.
Warm, even studio lighting. Deep focus — presenter and LED screen equally sharp.
Clean broadcast aesthetic. Professional color grading — natural skin tones,
accurate map colors under studio lighting.
PROMPT,
];
