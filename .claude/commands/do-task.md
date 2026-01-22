# Do Task

Ejecuta una tarea: obtiene el plan de GitHub, crea rama, implementa, y crea PR.

## Workflow

```
/do-task #número
    │
    ├── 1. Obtener tarea de GitHub (mcp__github__get_issue)
    │
    ├── 2. Preguntar rama base (master recomendado / actual / otra)
    │
    ├── 3. Crear rama: task/#número-titulo-slug
    │
    ├── 4. Mover tarea a "Doing"
    │
    ├── 5. Ejecutar plan de acción (archivos a modificar)
    │
    ├── 6. Al terminar:
    │       ├── /post-commit
    │       ├── Push rama
    │       ├── Crear PR vinculada a la tarea
    │       └── Mover tarea a "Review"
    │
    └── 7. Mostrar resumen con enlaces
```

## Instrucciones

### 1. Obtener la tarea

Usa `mcp__github__get_issue` con el número de la tarea.
Extrae:
- Título
- Objetivo
- Archivos a modificar
- Definition of Done

### 2. Preguntar rama base

Pregunta al usuario desde qué rama partir:
- `master` (Recomendado)
- Rama actual
- Otra (que escriba el nombre)

### 3. Crear rama

```bash
git checkout [rama-base]
git pull origin [rama-base]
git checkout -b task/#[número]-[titulo-slug]
```

Formato: `task/#14-crear-endpoint-upload`

### 4. Mover a Doing

Usa `mcp__github__move_task_status` con:
- `issue_number`: número de la tarea
- `status`: "doing"

### 5. Ejecutar plan de acción

Lee la sección "Archivos a modificar" de la tarea y:
- Crea/modifica cada archivo según el plan
- Verifica que cada cambio funciona
- Sigue los principios de Vertical Slice

### 6. Al terminar

1. **Ejecuta /post-commit** para narrar los cambios

2. **Push de la rama**:
   ```bash
   git push -u origin task/#[número]-[titulo-slug]
   ```

3. **Crear PR**:
   ```bash
   gh pr create --title "[Título de la tarea]" --body "Closes #[número]

   ## Cambios
   [Lista de cambios realizados]

   ## Definition of Done
   [Checklist de la tarea]"
   ```

4. **Mover a Review**: `mcp__github__move_task_status` con status "review"

### 7. Mostrar resumen

- Enlace a la PR
- Enlace a la tarea
- Archivos modificados
- Estado: Review

## Ejemplo de uso

```
/do-task #14
```

## Argumento

$ARGUMENTS - Número de la tarea (ej: #14 o 14)
