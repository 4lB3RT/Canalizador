# Template: Descripción de tarea

## Formato

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

## Ejemplo

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
