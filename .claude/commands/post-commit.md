# Post Commit

Crea UN SOLO post en español narrando el commit del día como storytelling personal de developer.
El post se publica como respuesta al hilo principal de Canalizador.

## Configuración del hilo

**IMPORTANTE**: El post debe ser respuesta al hilo de Canalizador.
- ID del hilo principal: `1969071304604041350`
- URL: https://x.com/4LB3RTTT/status/1969071304604041350

## Instrucciones

1. Obtén la fecha actual con `date +%Y-%m-%d`
2. Ejecuta `git log -1 --pretty=format:"%s%n%n%b"` para el último commit
3. Ejecuta `git diff HEAD~1 HEAD` para ver los cambios de código
4. Ejecuta `git diff --stat HEAD~1 HEAD` para estadísticas

5. **Busca posts de planificación relacionados:**
   - Usa `mcp__twitter__get_my_tweets` para obtener tus posts recientes
   - Identifica posts de planificación (contienen 📋, #Roadmap, o "Tareas:")
   - Si el commit resuelve o avanza alguna tarea planificada, guarda el ID del post para enlazarlo

6. Crea UN SOLO POST con esta estructura:

### Estructura del post

```
[FECHA]

[SENSACIÓN DEL DÍA - 1-2 frases honestas sobre cómo te sientes]

[NARRACIÓN TÉCNICA - Qué hiciste y por qué, con #hashtags inline en palabras clave. Incluye problemas encontrados, decisiones tomadas, comparativas si aplica]

[CÓDIGO RELEVANTE - Si hay código interesante, inclúyelo]

[REFERENCIA A PLANIFICACIÓN - Si aplica: "Avance de: [enlace al post de planificación]" o "✓ Completado de: [enlace]"]

[NEXT STEPS - Lista con ✅]

[PD OPCIONAL - 💡 Ideas o pensamientos adicionales]
```

7. Publica con `mcp__twitter__tweet` usando `reply_to: "1969071304604041350"`

8. Después de publicar, ejecuta el comando `/commit` para hacer commit de los cambios pendientes

## Formato de enlaces a planificación

Cuando el commit está relacionado con un post de planificación previo, incluye:
- `Avance de: https://x.com/4LB3RTTT/status/[ID_POST_PLAN]` → si es progreso parcial
- `✓ Completado: https://x.com/4LB3RTTT/status/[ID_POST_PLAN]` → si completa la tarea

## Hashtags SEO inline recomendados

#BuildInPublic #DevLife #IndieDev #AI #GenerativeAI #Sora #OpenAI #Laravel #PHP #CleanCode #API #VideoAI #Programacion #DesarrolloWeb #Canalizador

## Ejemplo completo de un post (con referencia a planificación)

```
2025-10-17

La sensación de hoy es que he avanzado poco, pero me quedo con muchas ganas de seguir.

Hoy me he centrado en mejorar #prompts y optimizar #costes, aunque lo que ha terminado ocurriendo es que he aumentado los costes… pero por motivos de #investigación 🧪

He estado probando otros modelos diferentes a #GPT4 y he dado el salto a #GPT5. Me encontré con un problema de #timeout:

GPT-4 → <30s de respuesta
GPT-5 → >30s de respuesta

Avance de: https://x.com/4LB3RTTT/status/1234567890

✅ Next Steps
- Terminar la investigación y solucionar el timeout con #GPT5
- Montar una #UI decente para empezar pruebas

#BuildInPublic #Canalizador
```

## Argumento opcional

$ARGUMENTS - Contexto adicional sobre cómo te sientes o qué quieres destacar
