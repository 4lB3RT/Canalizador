# Optimización de Costes Sora-2

## Análisis Actual

### Configuración Actual
- **Duración**: 12 segundos (hardcoded)
- **Resolución**: 1280x720 (720p)
- **Script configurado**: 9 segundos

### Factores que Afectan el Coste

1. **Duración del Video** ⭐⭐⭐ (Impacto Alto)
   - El costo es proporcional a la duración
   - Reducir de 12s a 9s = **25% de ahorro**
   - Alineado con la duración del script

2. **Resolución** ⭐⭐⭐ (Impacto Alto)
   - Resoluciones más bajas = menor costo
   - Opciones disponibles:
     - 1280x720 (720p) - Actual
     - 854x480 (480p) - **~40% menos píxeles**
     - 640x360 (360p) - **~75% menos píxeles**
   - Para redes sociales, 480p suele ser suficiente

3. **Longitud del Prompt** ⭐ (Impacto Bajo)
   - El prompt actual es descriptivo pero no excesivo
   - Ya está optimizado para ser completo pero conciso

## Recomendaciones de Optimización

### 1. Reducir Duración (Prioridad Alta) ✅
- **Cambio**: De 12s a 9s
- **Ahorro estimado**: ~25%
- **Riesgo**: Bajo (alineado con script)
- **Implementación**: Cambiar constante

### 2. Reducir Resolución (Prioridad Media-Alta) ✅
- **Cambio**: De 720p a 480p
- **Ahorro estimado**: ~30-40%
- **Riesgo**: Bajo-Medio (calidad visual aceptable para mayoría de casos)
- **Implementación**: Hacer configurable

### 3. Hacer Parámetros Configurables (Prioridad Media) ✅
- **Beneficio**: Flexibilidad para ajustar según necesidades
- **Implementación**: Variables de entorno + config file

### 4. Optimizar Prompt (Prioridad Baja) ⚠️
- **Estado actual**: Ya está optimizado
- **Nota**: El prompt debe ser descriptivo para calidad, no reducir más

## Plan de Implementación

1. Crear archivo de configuración `config/sora.php`
2. Actualizar `SoraVideoRepository` para usar configuración
3. Reducir duración a 9 segundos (alineado con script)
4. Reducir resolución a 480p por defecto
5. Agregar variables de entorno para override

## Estimación de Ahorro Total

- **Duración (12s → 9s)**: ~25% ahorro
- **Resolución (720p → 480p)**: ~35% ahorro
- **Ahorro combinado**: ~**50-55% de reducción de costes**

## Consideraciones

- La calidad visual en 480p sigue siendo buena para la mayoría de casos de uso
- Si se necesita mayor calidad, se puede aumentar vía configuración
- La duración de 9s está alineada con el script generado
