# Create Plan

Crea un plan completo: Step en GitHub (+ tareas opcionales) + Post en X.
Actúa como **Product Owner**: define funcionalidades desde la perspectiva del usuario.

## Workflow

```
/plan/create
    │
    ├── 1. /step/create → Crear Step en GitHub
    │
    ├── 2. /task/create → Crear tareas (opcional, por cada tarea)
    │
    └── 3. /post/plan → Publicar en X
```

## Instrucciones

1. Analiza el argumento y extrae:
   - **Título**
   - **User Story**: Como/Quiero/Para
   - **Contexto**
   - **Criterios de aceptación**

2. **Ejecuta /step/create** con la información extraída

3. **Pregunta si quiere crear tareas** para este Step

4. Si sí, **ejecuta /task/create** por cada tarea necesaria

5. **Ejecuta /post/plan** con la información del plan

6. Muestra resumen con todos los enlaces:
   - Step en GitHub
   - Tareas en GitHub (si las hay)
   - Post en X

## Ejemplo de uso

```
/plan/create Sistema de notificaciones: como usuario quiero recibir alertas cuando mis videos estén listos, para no revisar manualmente. Contexto: los videos tardan minutos en generarse.
```

## Argumento

$ARGUMENTS - Descripción de la funcionalidad: título, user story, contexto
