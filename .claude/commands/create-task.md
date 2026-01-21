# Create Task

Crea una Task (tarea técnica) vinculada a un Step y la añade al tablero Tareas.

## Tool a usar

`mcp__github__create_task` con los siguientes parámetros:

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| title | string | sí | Título de la tarea |
| description | string | sí | Descripción técnica |
| parent_step | integer | sí | Número del Step padre (#) |
| priority | string | no | high, medium (default), low |

## Instrucciones

1. Analiza el argumento y extrae:
   - **Título**: Nombre de la tarea
   - **Descripción**: Qué hay que hacer técnicamente
   - **Parent Step**: Número del Step al que pertenece
   - **Prioridad**: high/medium/low (si no se indica, usar medium)

2. Llama a `mcp__github__create_task` con los parámetros extraídos

3. Muestra el resultado: Task #número + URL

## Ejemplo de uso

```
/create-task Crear endpoint upload de imagen: recibir imagen del usuario y validar formato (jpg, png). Parent: #6
```

## Argumento

$ARGUMENTS - Descripción de la tarea: título, descripción técnica, número del step padre
