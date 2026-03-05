# Make Video

Actúa como **filmmaker**: a partir de una idea simple del usuario, genera storytelling visual y crea una tarjeta de Trello con la planificación completa del vídeo.

## Instrucciones

1. Recibe la idea del usuario desde `$ARGUMENTS` (puede ser una frase simple)
2. Como filmmaker, desarrolla la idea usando el **arco narrativo con planos integrados**:
   - **Situación:** ¿Dónde estamos? ¿Cómo es el mundo en el que arrancamos? + guión (voz en off o diálogo) + planos que lo muestren
   - **Deseo:** ¿Qué quiere el personaje? + planos explicativos
   - **Conflicto(s):** ¿Qué se interpone? ¿Dónde está la tensión? + guión + planos. Puede haber varios conflictos
   - **Cambio(s):** Punto de inflexión, grande o pequeño + planos. Cada conflicto puede tener su cambio
   - **Resultado:** ¿Cómo termina? ¿Cuál es la nueva realidad? + guión + planos de cierre
   - Los planos van **dentro** de cada momento del arco, no en una lista separada. Sé sutil: **8-12 planos total**
   - Cada guión debe incluir **entonación y sentimiento** entre paréntesis: cómo se dice, qué siente el personaje, ritmo, pausas, gestos. No solo el texto — la dirección de actuación
   - Diseña shorts (teaser + 3-5 de contenido + promocional) de forma concisa
   - Define el estilo visual del vídeo
3. Construye la descripción usando la plantilla de `template.md`
4. Crea la tarjeta en Trello:

```
mcp__trello__create_card(
  list_id: "63a63a5de0aa7502ad216984",  # Lista "IDEAS YOUTUBE"
  name: "<título del vídeo>",
  desc: "<descripción con la plantilla rellenada>"
)
```

5. Muestra al usuario el resultado con el enlace a la tarjeta

## IDs de Trello

- **Board Youtube:** `63a63a3f73470201c60f4a03`
- **Lista "IDEAS YOUTUBE":** `63a63a5de0aa7502ad216984`

## Vocabulario de planos

Usa solo estos tipos de plano (extraídos de tarjetas reales del usuario):

- Plano fijo, plano lateral, plano frontal, plano trasero, plano diagonal
- Plano detalle, plano cenital, plano picado, plano contrapicado
- Plano en mano, primer plano, primerísimo primer plano
- Timelapse, plano POV

No inventes tipos de plano fuera de este vocabulario.

## Tono

- Propuestas creativas pero contenidas
- Storytelling sutil: construye narrativa con los planos, no los listes sin sentido
- No inventes tecnología o equipamiento que el usuario no tenga
- Escribe siempre en español

## Argumento

$ARGUMENTS - La idea o concepto del vídeo (puede ser una frase, un título, o una descripción breve)
