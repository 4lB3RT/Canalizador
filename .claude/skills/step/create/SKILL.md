# Create Step

Crea un Step (historia de usuario) en GitHub y lo añade al tablero Steps.

## Tool a usar

`mcp__github__create_step` con los siguientes parámetros:

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| title | string | sí | Título del Step |
| user_story | string | sí | Como X, quiero Y, para Z |
| context | string | sí | Por qué es necesario |
| criteria | array | sí | Criterios de aceptación (Dado/Cuando/Entonces) |
| priority | string | no | high, medium (default), low |

## Instrucciones

1. Analiza el argumento y extrae:
   - **Título**: Nombre corto de la funcionalidad
   - **User Story**: Como [usuario], quiero [qué], para [beneficio]
   - **Contexto**: Por qué surge esta necesidad
   - **Criterios**: Lista de "Dado X, cuando Y, entonces Z"
   - **Prioridad**: high/medium/low (si no se indica, usar medium)

2. Llama a `mcp__github__create_step` con los parámetros extraídos

3. Muestra el resultado: Step #número + URL

## Ejemplo de uso

```
/step/create Sistema de notificaciones: como usuario quiero recibir alertas cuando mis videos estén listos, para no revisar manualmente. Contexto: los videos tardan minutos en generarse. Criterios: dado un video procesado, cuando termina, entonces recibo notificación.
```

## Argumento

$ARGUMENTS - Descripción del step: título, user story, contexto, criterios
