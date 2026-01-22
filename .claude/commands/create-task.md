# Create Task

Crea una Task técnica (Vertical Slice) vinculada a un Step.
Cada tarea debe ser funcional: la aplicación siempre queda funcionando después de completarla.

## Tool a usar

`mcp__github__create_task` con los siguientes parámetros:

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

3. Construye la descripción con este formato:
   ```markdown
   ## Objetivo

   [Qué se logra con esta tarea]

   ## Archivos a modificar

   - `ruta/archivo1.php` - [qué cambiar]
   - `ruta/archivo2.php` - [qué cambiar]
   - `ruta/archivo3.php` - [crear nuevo]

   ## Definition of Done

   - [ ] [Criterio 1]
   - [ ] [Criterio 2]
   - [ ] Tests pasan
   - [ ] La aplicación funciona correctamente
   ```

4. Llama a `mcp__github__create_task`

5. Muestra el resultado: Task #número + URL

## Ejemplo de descripción generada

```markdown
## Objetivo

Crear endpoint REST para recibir imagen del usuario y validarla.

## Archivos a modificar

- `routes/api.php` - Añadir ruta POST /api/avatar/upload
- `app/Http/Controllers/AvatarController.php` - Crear controller con método upload
- `app/Http/Requests/UploadAvatarRequest.php` - Crear form request con validación
- `app/Services/AvatarService.php` - Crear servicio para procesar imagen

## Definition of Done

- [ ] Endpoint POST /api/avatar/upload funciona
- [ ] Valida formatos jpg, png
- [ ] Retorna error 422 si formato inválido
- [ ] Retorna 200 con path de imagen si válido
- [ ] Tests pasan
- [ ] La aplicación funciona correctamente
```

## Ejemplo de uso

```
/create-task Crear endpoint upload de imagen para el step #6: recibir imagen jpg/png y validarla
```

## Argumento

$ARGUMENTS - Descripción de la tarea y número del step padre
