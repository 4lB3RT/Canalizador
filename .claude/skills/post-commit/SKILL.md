# Post Commit

Crea UN SOLO post en español narrando el commit del día como storytelling personal de developer.
El post se publica como respuesta al hilo principal de Canalizador, en **un único tweet** (≤280 caracteres).
Entre 800 y 1500 caracteres — un solo post, sin hilo.

## Instrucciones

1. Obtén la fecha actual con `date +%Y-%m-%d`
2. Ejecuta `git log -1 --pretty=format:"%s%n%n%b"` para el último commit
3. Ejecuta `git diff HEAD~1 HEAD` para ver los cambios de código
4. Ejecuta `git diff --stat HEAD~1 HEAD` para estadísticas

5. **Busca posts de planificación relacionados:**
   - Usa `mcp__x-server__get_my_tweets` para obtener tus posts recientes
   - Identifica posts de planificación (contienen 📋 o "Tareas:")
   - Si el commit resuelve o avanza alguna tarea planificada, guarda el ID del post para enlazarlo

6. Redacta el post siguiendo la estructura y ejemplo del `template.md`
   - Entre 800 y 1500 caracteres — un solo post (X permite posts largos), sin hilo
   - Lenguaje humano y cercano, sin plantillas rígidas
   - Sin emojis excesivos

7. **Muestra el borrador al usuario y espera aprobación.**
   Pregunta: "¿Publicamos este post? Escribe **S** para publicar o indica qué cambiar."
   Si pide cambios: regenera el post completo con el feedback y vuelve al punto 7.

8. Publica con `mcp__x-server__tweet` con `reply_to` del `thread.md`
   **IMPORTANTE**: Publicar en un único tweet. La cuenta es verificada y soporta posts largos. NO fragmentar en hilo bajo ningún concepto.

9. Después de publicar, ejecuta el comando `/commit` para hacer commit de los cambios pendientes

## Argumento opcional

$ARGUMENTS - Contexto adicional sobre cómo te sientes o qué quieres destacar
