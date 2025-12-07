# Corrección de Resoluciones Soportadas por Sora API

## Error Encontrado

```
Video generation API error: Sora video generation: HTTP 400 - Invalid value: '640x360'. 
Supported values are: '720x1280', '1280x720', '1024x1792', and '1792x1024'.
```

## Resoluciones Correctas según la API de Sora

La API de Sora **solo acepta** estas 4 resoluciones:

### Resoluciones Horizontales (Landscape)
- **`1280x720`** (720p horizontal) - ✅ **RECOMENDADO para testing** (menor costo)
- **`1792x1024`** (mayor calidad horizontal)

### Resoluciones Verticales (Portrait)
- **`720x1280`** (720p vertical)
- **`1024x1792`** (mayor calidad vertical)

---

## Cambios Realizados

### 1. Configuración Actualizada (`config/sora.php`)
- ✅ Resoluciones actualizadas a las 4 soportadas por la API
- ✅ Default cambiado a `1280x720` (menor costo disponible)
- ✅ Comentarios actualizados con las resoluciones correctas

### 2. Validación Actualizada (`SoraVideoRepository.php`)
- ✅ Validación actualizada para aceptar solo las 4 resoluciones válidas
- ✅ Mensaje de error mejorado para mostrar las resoluciones soportadas
- ✅ Default actualizado a `1280x720`

### 3. Variables de Entorno (`.env.example`)
- ✅ Default actualizado a `1280x720`
- ✅ Comentarios actualizados con las resoluciones correctas

---

## Configuración Recomendada para Testing

### Opción 1: Mínimo Costo (Recomendado) ✅
```env
SORA_RESOLUTION=1280x720
```
- **Resolución**: 1280x720 (720p horizontal)
- **Costo**: Menor disponible
- **Uso**: Testing y desarrollo

### Opción 2: Mayor Calidad Horizontal
```env
SORA_RESOLUTION=1792x1024
```
- **Resolución**: 1792x1024
- **Costo**: Mayor (más píxeles)
- **Uso**: Producción cuando necesites máxima calidad horizontal

### Opción 3: Vertical/Portrait
```env
SORA_RESOLUTION=720x1280
```
- **Resolución**: 720x1280 (vertical)
- **Costo**: Similar a 1280x720
- **Uso**: Videos verticales para móviles/redes sociales

---

## Comparación de Resoluciones

| Resolución | Orientación | Píxeles | Costo Relativo | Uso Recomendado |
|------------|-------------|---------|----------------|-----------------|
| `1280x720` | Horizontal | 921,600 | **Más bajo** ✅ | Testing, desarrollo |
| `720x1280` | Vertical | 921,600 | Bajo | Videos verticales |
| `1792x1024` | Horizontal | 1,835,008 | Medio-Alto | Producción alta calidad |
| `1024x1792` | Vertical | 1,835,008 | Medio-Alto | Producción vertical alta calidad |

---

## Nota Importante

⚠️ **Las resoluciones anteriores (640x360, 854x480) NO son soportadas por la API de Sora.**

La resolución más baja disponible es `1280x720`, que es la recomendada para testing y minimizar costes.

---

## Próximos Pasos

1. ✅ Configuración ya está corregida
2. 🔄 Ejecuta `php artisan config:clear` para limpiar caché
3. 🧪 Prueba generar un video con `SORA_RESOLUTION=1280x720`
4. ✅ El error debería estar resuelto

---

## Resumen

- ❌ **Antes**: Resoluciones incorrectas (640x360, 854x480)
- ✅ **Ahora**: Solo las 4 resoluciones válidas según la API
- ✅ **Default**: `1280x720` (menor costo disponible)
- ✅ **Error resuelto**: La validación ahora coincide con la API
