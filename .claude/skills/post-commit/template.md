# Template: Post de commit

## Estructura del post

```
[FECHA]

[SENSACIÓN DEL DÍA - 1-2 frases honestas sobre cómo te sientes]

[NARRACIÓN TÉCNICA - Qué hiciste y por qué. Incluye problemas encontrados, decisiones tomadas, comparativas si aplica]

[CÓDIGO RELEVANTE - Si hay código interesante, inclúyelo]

[REFERENCIA A PLANIFICACIÓN - Si aplica: "Avance de: [enlace al post de planificación]" o "✓ Completado de: [enlace]"]

[NEXT STEPS - Lista con ✅]

[PD OPCIONAL - 💡 Ideas o pensamientos adicionales]
```

## Formato de enlaces a planificación

Cuando el commit está relacionado con un post de planificación previo, incluye:
- `Avance de: https://x.com/4LB3RTTT/status/[ID_POST_PLAN]` → si es progreso parcial
- `✓ Completado: https://x.com/4LB3RTTT/status/[ID_POST_PLAN]` → si completa la tarea

## Ejemplo completo (con referencia a planificación)

```
2025-10-17

La sensación de hoy es que he avanzado poco, pero me quedo con muchas ganas de seguir.

Hoy me he centrado en mejorar prompts y optimizar costes, aunque lo que ha terminado ocurriendo es que he aumentado los costes… pero por motivos de investigación 🧪

He estado probando otros modelos diferentes a GPT-4 y he dado el salto a GPT-5. Me encontré con un problema de timeout:

GPT-4 → <30s de respuesta
GPT-5 → >30s de respuesta

Avance de: https://x.com/4LB3RTTT/status/1234567890

✅ Next Steps
- Terminar la investigación y solucionar el timeout con GPT-5
- Montar una UI decente para empezar pruebas
```
