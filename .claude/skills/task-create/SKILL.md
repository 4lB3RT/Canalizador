# Create Task

Crea una Task técnica (Vertical Slice) vinculada a un Step.
Cada tarea debe ser funcional: la aplicación siempre queda funcionando después de completarla.

## Tool a usar

`mcp__github-server__create_task` con los siguientes parámetros:

| Parámetro | Tipo | Requerido | Descripción |
|-----------|------|-----------|-------------|
| title | string | sí | Título de la tarea |
| description | string | sí | Descripción técnica completa |
| parent_step | integer | sí | Número del Step padre (#) |
| priority | string | no | high, medium (default), low |

## Principios de Vertical Slicing

1. **Funcional de punta a punta**: La tarea entrega valor completo, no parcial
2. **No rompe la aplicación**: Después de completar la tarea, todo sigue funcionando
3. **Independiente**: Se puede desarrollar y desplegar sin depender de otras tareas
4. **Testeable**: Se puede verificar que funciona correctamente

## Instrucciones

1. Analiza el argumento y el Step padre para entender el contexto

2. Define la tarea como **Vertical Slice**:
   - **Título**: Acción clara y concisa
   - **Objetivo**: Qué se logra al completar esta tarea
   - **Archivos a modificar/crear**: Lista específica de archivos
   - **Cambios por archivo**: Qué hacer en cada archivo
   - **Definition of Done**: Criterios para considerar la tarea completada

3. Construye la descripción siguiendo el template en `description.md`

4. Llama a `mcp__github-server__create_task`

5. Muestra el resultado: Task #número + URL

## Ejemplo de uso

```
/task-create Crear endpoint upload de imagen para el step #6: recibir imagen jpg/png y validarla
```

## Argumento

$ARGUMENTS - Descripción de la tarea y número del step padre
