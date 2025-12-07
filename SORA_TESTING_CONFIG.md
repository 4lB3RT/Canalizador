# Configuración de Pruebas Sora-2 - Mínimo Coste

## Comparación de Modelos

### sora-2 (RECOMENDADO para pruebas) ✅
- **Costo**: Menor (modelo estándar)
- **Calidad**: Buena para pruebas y desarrollo
- **Uso**: Ideal para testing y prototipado

### sora-2-pro
- **Costo**: Mayor (modelo profesional)
- **Calidad**: Superior, mejor para producción
- **Uso**: Solo cuando necesites máxima calidad

## Recomendación: Usar `sora-2` para Pruebas

**Razón**: El modelo `sora-2` es más económico y suficiente para hacer pruebas y validar el funcionamiento del sistema.

---

## Configuración Óptima para Pruebas (Mínimo Coste)

### Configuración Actual (Optimizada para Pruebas)

```env
# Modelo más económico
SORA_MODEL=sora-2

# Duración mínima (alineada con script de 9s)
SORA_DURATION=9

# Resolución mínima para pruebas
SORA_RESOLUTION=640x360
```

### Estimación de Costes

**Configuración Actual (360p, 9s, sora-2):**
- Resolución: 640x360 (360p) - **Mínima disponible**
- Duración: 9 segundos - **Alineada con script**
- Modelo: sora-2 - **Más económico**

**Comparación con configuración anterior:**
- Antes: 720p, 12s, sora-2 = **100% costo base**
- Ahora: 360p, 9s, sora-2 = **~30-35% del costo base**
- **Ahorro: ~65-70%** 🎉

---

## Opciones de Configuración por Caso de Uso

### 1. Pruebas Mínimas (MÁXIMO AHORRO) 💰
```env
SORA_MODEL=sora-2
SORA_DURATION=5
SORA_RESOLUTION=640x360
```
**Uso**: Validar que el sistema funciona correctamente

### 2. Pruebas Estándar (ACTUAL) ✅
```env
SORA_MODEL=sora-2
SORA_DURATION=9
SORA_RESOLUTION=640x360
```
**Uso**: Pruebas completas con duración alineada al script

### 3. Desarrollo/QA (BALANCE)
```env
SORA_MODEL=sora-2
SORA_DURATION=9
SORA_RESOLUTION=854x480
```
**Uso**: Validar calidad antes de producción

### 4. Producción (CALIDAD)
```env
SORA_MODEL=sora-2-pro
SORA_DURATION=9
SORA_RESOLUTION=1280x720
```
**Uso**: Videos finales para usuarios

---

## Resumen de Optimizaciones Aplicadas

✅ **Modelo**: `sora-2` (no pro) - Más económico  
✅ **Resolución**: `640x360` (360p) - Mínima disponible  
✅ **Duración**: `9s` - Alineada con script  
✅ **Ahorro total estimado**: **~65-70%** comparado con configuración inicial

---

## Notas Importantes

1. **360p es suficiente para pruebas**: La calidad es aceptable para validar funcionalidad
2. **sora-2 vs sora-2-pro**: Para pruebas, siempre usa `sora-2`
3. **Duración mínima**: 9s está alineada con tu script, pero puedes reducir a 5-7s si solo necesitas validar funcionamiento
4. **Para producción**: Aumenta resolución y considera `sora-2-pro` si necesitas máxima calidad

---

## Próximos Pasos

1. ✅ Configuración ya está optimizada para pruebas
2. Ejecuta `php artisan config:clear` si es necesario
3. Prueba la generación de videos
4. Monitorea costes en tu dashboard de OpenAI
5. Ajusta según necesidades (puedes aumentar calidad cuando lo necesites)
