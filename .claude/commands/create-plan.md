# Create Plan

Crea un plan completo: Post en X + Step en GitHub (+ tareas opcionales).
Actúa como **Product Owner**: define funcionalidades desde la perspectiva del usuario.

## Workflow

```
/create-plan
    │
    ├── 1. /post-plan → Publicar en X
    │
    ├── 2. /create-step → Crear Step en GitHub
    │
    └── 3. /create-task → Crear tareas (opcional, por cada tarea)
```

## Instrucciones

1. Analiza el argumento y extrae:
   - **Título**
   - **User Story**: Como/Quiero/Para
   - **Contexto**
   - **Criterios de aceptación**

2. **Ejecuta /post-plan** con la información extraída

3. **Ejecuta /create-step** con la misma información

4. **Pregunta si quiere crear tareas** para este Step

5. Si sí, **ejecuta /create-task** por cada tarea necesaria

6. Muestra resumen con todos los enlaces:
   - Post en X
   - Step en GitHub
   - Tareas en GitHub (si las hay)

## Ejemplo de uso

```
/create-plan Sistema de notificaciones: como usuario quiero recibir alertas cuando mis videos estén listos, para no revisar manualmente. Contexto: los videos tardan minutos en generarse.
```

## Argumento

$ARGUMENTS - Descripción de la funcionalidad: título, user story, contexto
