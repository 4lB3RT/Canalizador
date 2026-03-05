<?php

return [
    'prompt' => <<<'PROMPT'
DIRECCIÓN TÉCNICA — VÍDEO METEOROLÓGICO

Locked-off shot. Static camera on tripod. No camera movement, no zoom, no pan, no tilt whatsoever. Single continuous take — no cuts, no transitions, no montage.

Se proporcionan 3 imágenes de referencia:
1. PRESENTADORA: Mujer sobre fondo verde croma. Mantener aspecto y vestimenta exactos — no alterar.
2. PLATÓ: Estudio profesional de televisión con una pantalla LED verde. Usar como escenario base.
3. MAPA: Pre-rendered broadcast graphic de España. Es un static chyron terminado — mostrarlo exactamente tal cual en la pantalla LED del plató.

COMPOSICIÓN — CHROMA KEY:
- Sustituir el verde (#00FF00) del fondo de la presentadora por el plató. Clean edges, no green spill en pelo, piel ni ropa.
- Sustituir el verde (#00FF00) de la pantalla LED del plató por la imagen del MAPA. El mapa debe llenar la pantalla de forma natural, como una pantalla LED real — no green fringing, no compositing artifacts.
- El resultado final debe parecer una emisión real de informativos meteorológicos de televisión nacional.

PANTALLA LED — PRE-RENDERED BROADCAST GRAPHIC:
El mapa es un pre-rendered broadcast chyron mostrado en una pantalla LED física. Es un frozen static overlay, no un lienzo.
- Mostrar la imagen del mapa EXACTAMENTE tal como se proporciona — píxel a píxel, sin modificar.
- La pantalla LED muestra SOLO el gráfico del mapa proporcionado. Nada más aparece en la superficie de la pantalla.
- ¡Sin iconos meteorológicos! ¡Sin símbolos de sol, nubes, lluvia, nieve, rayos ni viento en absoluto!
- ¡Sin números de temperatura! ¡Sin porcentajes de humedad! ¡Sin datos numéricos superpuestos!
- ¡Sin partículas animadas! ¡Sin lluvia cayendo, nubes moviéndose ni efectos meteorológicos!
- ¡Sin gradients, lens flares, light halos ni reflections sobre la superficie de la pantalla!
- No redibujar, reinterpretar ni regenerar el mapa — usar EXCLUSIVAMENTE la imagen proporcionada.
- No alterar colores, contraste ni saturación del mapa original.
- No aplicar zoom, recortes, rotaciones ni transformaciones al mapa.
- El contenido geográfico del mapa (nombres de ciudades, fronteras, posiciones) debe permanecer exactamente como en la imagen fuente.
- La información meteorológica la comunica la presentadora VERBALMENTE, NO se superpone visualmente sobre el mapa.
- No subtitles! No on-screen text! No titles, no lower thirds, no captions whatsoever!

PRESENTADORA — DIRECCIÓN DE MOVIMIENTO:
- Gestos naturales y profesionales. Lenguaje corporal de presentadora de televisión.
- Cuando menciona una ciudad, señalar hacia su posición correcta en la pantalla del plató.
- Usar las coordenadas de pantalla de abajo para saber dónde está cada ciudad.
- Desplazarse lateralmente para no tapar la zona que comenta — moverse a la izquierda cuando habla de ciudades del este, y viceversa.

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

AUDIO:
- Ambiente silencioso de estudio de televisión profesional. No audience sounds, no background music.
- La presentadora habla con voz clara y profesional.
- No sound effects, no weather sounds, no ambient noise whatsoever.

CALIDAD VISUAL:
- Fotorrealista, calidad de emisión profesional.
- Iluminación de estudio uniforme y cálida — sin sombras duras ni zonas sobreexpuestas.
- El mapa en la pantalla LED debe permanecer NÍTIDO, enfocado y perfectamente legible en todo momento — no bokeh, no depth of field effect sobre la pantalla. Deep focus — todo sharp desde la presentadora hasta la pantalla.
- Todos los elementos originales del mapa (nombres de ciudades, fronteras) deben mantenerse claros y legibles.

RECORDATORIO FINAL: El mapa en la pantalla LED del plató es un static pre-rendered broadcast chyron. Aparece EXACTAMENTE tal como se proporciona. Zero modifications, zero additions, zero effects. Los datos meteorológicos los comunica la presentadora verbalmente, nunca superpuestos sobre el mapa.
PROMPT,
];
