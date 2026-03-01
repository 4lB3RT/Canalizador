# Post Commit

Crea UN SOLO post en español narrando el commit del día como storytelling personal de developer.
El post se publica como respuesta al hilo principal de Canalizador.

## Instrucciones

1. Obtén la fecha actual con `date +%Y-%m-%d`
2. Ejecuta `git log -1 --pretty=format:"%s%n%n%b"` para el último commit
3. Ejecuta `git diff HEAD~1 HEAD` para ver los cambios de código
4. Ejecuta `git diff --stat HEAD~1 HEAD` para estadísticas

5. **Busca posts de planificación relacionados:**
   - Usa `mcp__x__get_my_tweets` para obtener tus posts recientes
   - Identifica posts de planificación (contienen 📋 o "Tareas:")
   - Si el commit resuelve o avanza alguna tarea planificada, guarda el ID del post para enlazarlo

6. Crea UN SOLO POST siguiendo la estructura y ejemplo del `template.md`
   Usa la configuración de hilo de `thread.md` para el `reply_to`

7. Publica con `mcp__x__tweet`

8. Después de publicar, ejecuta el comando `/commit` para hacer commit de los cambios pendientes

## Argumento opcional

$ARGUMENTS - Contexto adicional sobre cómo te sientes o qué quieres destacar
