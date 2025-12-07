# Optimización de Duración - Reducción de Costes

## Respuesta: SÍ, reducir segundos reduce costes ✅

**El coste es proporcional a la duración del video.** Cada segundo adicional aumenta el coste linealmente.

---

## Cálculo de Ahorro por Duración

### Fórmula de Coste
```
Coste = Precio por segundo × Duración en segundos
```

### Ejemplo con Precio Estimado de $0.08/segundo

| Duración | Coste Estimado | Ahorro vs 9s |
|----------|----------------|--------------|
| **5 segundos** | $0.40 | **44% más barato** |
| **7 segundos** | $0.56 | **22% más barato** |
| **9 segundos** (actual) | $0.72 | Base |
| **12 segundos** | $0.96 | 33% más caro |

---

## Duración Mínima Recomendada para Pruebas

### Opción 1: Mínimo Absoluto (5 segundos) 💰💰💰
```env
SORA_DURATION=5
```
- **Coste**: ~$0.40 por video (44% más barato que 9s)
- **Uso**: Validar que el sistema funciona correctamente
- **Limitación**: El script está configurado para 9s, puede no alinearse perfectamente

### Opción 2: Balance Pruebas (7 segundos) 💰💰
```env
SORA_DURATION=7
```
- **Coste**: ~$0.56 por video (22% más barato que 9s)
- **Uso**: Pruebas más completas manteniendo ahorro significativo
- **Ventaja**: Mejor balance entre coste y funcionalidad

### Opción 3: Alineado con Script (9 segundos) 💰
```env
SORA_DURATION=9
```
- **Coste**: ~$0.72 por video (actual)
- **Uso**: Producción y pruebas completas
- **Ventaja**: Perfectamente alineado con el script generado

---

## Comparación de Costes Totales

### Coste por Video (Estimado)

| Configuración | Duración | Coste/Video | Ahorro vs 9s |
|---------------|----------|-------------|--------------|
| Mínimo (5s) | 5s | ~$0.40 | **44%** |
| Balance (7s) | 7s | ~$0.56 | **22%** |
| Actual (9s) | 9s | ~$0.72 | Base |
| Anterior (12s) | 12s | ~$0.96 | +33% |

### Coste por 10 Videos

| Duración | Coste Total | Ahorro vs 9s |
|----------|-------------|--------------|
| 5s | ~$4.00 | **$3.20 ahorrados** |
| 7s | ~$5.60 | **$1.60 ahorrados** |
| 9s | ~$7.20 | Base |
| 12s | ~$9.60 | +$2.40 más caro |

### Coste por 100 Videos

| Duración | Coste Total | Ahorro vs 9s |
|----------|-------------|--------------|
| 5s | ~$40.00 | **$32.00 ahorrados** |
| 7s | ~$56.00 | **$16.00 ahorrados** |
| 9s | ~$72.00 | Base |
| 12s | ~$96.00 | +$24.00 más caro |

---

## Consideraciones Importantes

### ⚠️ Duración Mínima Permitida
- **Típicamente**: 1-5 segundos (verificar en documentación de OpenAI)
- **Recomendado para pruebas**: 5 segundos mínimo
- **Menos de 5s**: Puede ser demasiado corto para validar funcionalidad

### ⚠️ Alineación con Script
- Tu script está configurado para **9 segundos**
- Si reduces la duración del video a 5-7s:
  - ✅ El video se generará correctamente
  - ⚠️ Puede cortarse antes de que termine el script completo
  - 💡 Para pruebas básicas esto está bien, pero para producción usa 9s

### ⚠️ Calidad del Video
- Videos más cortos pueden tener menos contexto visual
- Para pruebas de funcionalidad: 5-7s es suficiente
- Para validar calidad completa: usa 9s

---

## Recomendación por Caso de Uso

### 1. Pruebas Iniciales (Máximo Ahorro) 💰💰💰
```env
SORA_DURATION=5
```
- **Ahorro**: 44% vs 9s
- **Uso**: Validar que la integración funciona
- **Coste**: ~$0.40/video

### 2. Pruebas Regulares (Balance) 💰💰
```env
SORA_DURATION=7
```
- **Ahorro**: 22% vs 9s
- **Uso**: Pruebas más completas
- **Coste**: ~$0.56/video

### 3. Desarrollo/QA (Alineado) 💰
```env
SORA_DURATION=9
```
- **Ahorro**: Base
- **Uso**: Validar funcionalidad completa
- **Coste**: ~$0.72/video

### 4. Producción (Completo)
```env
SORA_DURATION=9
```
- **Uso**: Videos finales para usuarios
- **Ventaja**: Alineado con script completo
- **Coste**: ~$0.72/video

---

## Cómo Cambiar la Duración

### Opción 1: Variable de Entorno (Recomendado)
```env
# En tu archivo .env
SORA_DURATION=5  # Para pruebas mínimas
# o
SORA_DURATION=7  # Para pruebas balanceadas
# o
SORA_DURATION=9  # Para producción (default)
```

### Opción 2: Configuración Directa
Edita `config/sora.php`:
```php
'duration' => (int) env('SORA_DURATION', 5), // Cambiar default a 5
```

---

## Resumen

✅ **SÍ, reducir segundos reduce costes proporcionalmente**

**Ahorro estimado:**
- **5s vs 9s**: ~44% más barato (~$0.32 ahorrados por video)
- **7s vs 9s**: ~22% más barato (~$0.16 ahorrados por video)

**Recomendación:**
- Para **pruebas iniciales**: Usa **5 segundos** (máximo ahorro)
- Para **pruebas regulares**: Usa **7 segundos** (balance)
- Para **producción**: Usa **9 segundos** (alineado con script)

---

## Próximos Pasos

1. ✅ Decide qué duración usar según tu caso de uso
2. 📝 Actualiza `SORA_DURATION` en tu `.env`
3. 🔄 Ejecuta `php artisan config:clear`
4. 🧪 Genera un video de prueba
5. 💰 Verifica el coste real en tu dashboard de OpenAI
6. 📊 Compara con las estimaciones y ajusta si es necesario
