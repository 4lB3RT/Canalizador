# Calculadora de Costes Sora-2

## Configuración Actual (Optimizada para Pruebas)

```env
SORA_MODEL=sora-2
SORA_DURATION=9
SORA_RESOLUTION=640x360
```

---

## Cálculo de Coste por Video

### Factores que Afectan el Coste

1. **Modelo**: `sora-2` vs `sora-2-pro`
2. **Duración**: Segundos de video generado
3. **Resolución**: Píxeles totales (ancho × alto)

### Fórmula Estimada

```
Coste por video = (Precio por segundo × Duración) × Factor de resolución × Factor de modelo
```

---

## Precios Estimados (Basados en Patrones de APIs Similares)

⚠️ **NOTA**: Estos son precios estimados. Los precios reales pueden variar. Verifica en tu dashboard de OpenAI.

### Precios Estimados por Segundo de Video

| Modelo | Precio Estimado por Segundo | Fuente |
|--------|----------------------------|--------|
| `sora-2` | $0.05 - $0.10 USD/segundo | Estimación basada en APIs similares |
| `sora-2-pro` | $0.15 - $0.30 USD/segundo | Estimación (2-3x más caro que sora-2) |

### Factores de Resolución (Multiplicadores estimados)

| Resolución | Píxeles | Factor Estimado |
|-----------|---------|-----------------|
| 640x360 (360p) | 230,400 | 1.0x (base) |
| 854x480 (480p) | 409,920 | 1.5x - 1.8x |
| 1280x720 (720p) | 921,600 | 2.5x - 3.0x |

---

## Cálculo de Coste por Video (Configuración Actual)

### Tu Configuración Actual
- **Modelo**: `sora-2`
- **Duración**: 9 segundos
- **Resolución**: 640x360 (360p)

### Coste Estimado por Video

**Escenario Conservador (Precio más alto estimado):**
```
Coste = $0.10/segundo × 9 segundos × 1.0 (360p) = $0.90 USD por video
```

**Escenario Optimista (Precio más bajo estimado):**
```
Coste = $0.05/segundo × 9 segundos × 1.0 (360p) = $0.45 USD por video
```

**Coste Estimado Promedio:**
```
~$0.60 - $0.75 USD por video
```

---

## Comparación de Costes por Configuración

### 1. Configuración Actual (Pruebas - Mínimo Coste) ✅
```
Modelo: sora-2
Duración: 9s
Resolución: 640x360 (360p)
Coste estimado: $0.45 - $0.90 USD/video
Promedio: ~$0.60 - $0.75 USD/video
```

### 2. Configuración Anterior (Sin Optimizar)
```
Modelo: sora-2
Duración: 12s
Resolución: 1280x720 (720p)
Coste estimado: $1.50 - $3.00 USD/video
Promedio: ~$2.00 - $2.50 USD/video
```

### 3. Configuración Producción (Máxima Calidad)
```
Modelo: sora-2-pro
Duración: 9s
Resolución: 1280x720 (720p)
Coste estimado: $3.38 - $8.10 USD/video
Promedio: ~$5.00 - $6.00 USD/video
```

---

## Ahorro Estimado

### Comparación: Configuración Actual vs Anterior

| Métrica | Anterior | Actual | Ahorro |
|---------|----------|--------|--------|
| Coste por video | ~$2.00 - $2.50 | ~$0.60 - $0.75 | **~70%** |
| Coste por 10 videos | ~$20 - $25 | ~$6 - $7.50 | **~$13 - $17.50** |
| Coste por 100 videos | ~$200 - $250 | ~$60 - $75 | **~$125 - $175** |

### Comparación: Configuración Actual vs Producción

| Métrica | Producción | Actual | Ahorro |
|---------|------------|--------|--------|
| Coste por video | ~$5.00 - $6.00 | ~$0.60 - $0.75 | **~88%** |
| Coste por 10 videos | ~$50 - $60 | ~$6 - $7.50 | **~$43 - $52.50** |
| Coste por 100 videos | ~$500 - $600 | ~$60 - $75 | **~$425 - $525** |

---

## Cómo Verificar los Precios Reales

### Opción 1: Dashboard de OpenAI
1. Ve a https://platform.openai.com/usage
2. Busca la sección de "Sora" o "Video Generation"
3. Revisa los precios por segundo y resolución

### Opción 2: API Response
Al generar un video, revisa la respuesta de la API. Algunas APIs incluyen información de coste en los metadatos.

### Opción 3: Generar un Video de Prueba
1. Genera un video con tu configuración actual
2. Revisa tu facturación en OpenAI
3. Divide el coste entre el número de videos generados

### Opción 4: Documentación Oficial
Consulta la documentación oficial de precios:
- https://platform.openai.com/docs/pricing
- https://openai.com/api/pricing/

---

## Tabla de Costes Estimados por Configuración

| Modelo | Duración | Resolución | Coste Estimado/Video | Coste/10 Videos | Coste/100 Videos |
|--------|----------|------------|---------------------|-----------------|------------------|
| sora-2 | 9s | 360p | $0.60 - $0.75 | $6 - $7.50 | $60 - $75 |
| sora-2 | 9s | 480p | $0.90 - $1.35 | $9 - $13.50 | $90 - $135 |
| sora-2 | 9s | 720p | $1.50 - $2.25 | $15 - $22.50 | $150 - $225 |
| sora-2 | 12s | 720p | $2.00 - $3.00 | $20 - $30 | $200 - $300 |
| sora-2-pro | 9s | 360p | $1.35 - $2.70 | $13.50 - $27 | $135 - $270 |
| sora-2-pro | 9s | 720p | $3.38 - $6.75 | $33.75 - $67.50 | $337.50 - $675 |

---

## Recomendaciones

### Para Pruebas (Mínimo Coste)
✅ **Usa**: `sora-2`, 9s, 360p
💰 **Coste**: ~$0.60 - $0.75 por video
📊 **Ahorro**: Máximo posible manteniendo funcionalidad

### Para Desarrollo/QA
✅ **Usa**: `sora-2`, 9s, 480p
💰 **Coste**: ~$0.90 - $1.35 por video
📊 **Balance**: Buena calidad para validar antes de producción

### Para Producción
✅ **Usa**: `sora-2-pro`, 9s, 720p (si necesitas máxima calidad)
💰 **Coste**: ~$3.38 - $6.75 por video
📊 **Nota**: Considera si realmente necesitas sora-2-pro o si sora-2 con 720p es suficiente

---

## Notas Importantes

1. ⚠️ **Los precios son estimaciones**: Los precios reales pueden variar significativamente
2. 📊 **Verifica en tu dashboard**: Los precios exactos están en tu cuenta de OpenAI
3. 💰 **Factores adicionales**: Puede haber costes adicionales por almacenamiento o transferencia
4. 🔄 **Precios variables**: Los precios pueden cambiar sin previo aviso
5. 📈 **Descuentos por volumen**: Algunas APIs ofrecen descuentos por uso alto

---

## Próximos Pasos

1. ✅ Genera un video de prueba con la configuración actual
2. 📊 Revisa el coste real en tu dashboard de OpenAI
3. 💰 Actualiza esta tabla con los precios reales
4. 📈 Ajusta la configuración según tu presupuesto

---

## Ejemplo de Cálculo Manual

Si OpenAI cobra **$0.08 por segundo** para `sora-2` en resolución 360p:

```
Coste por video = $0.08/segundo × 9 segundos = $0.72 USD
```

Para **10 videos**:
```
10 videos × $0.72 = $7.20 USD
```

Para **100 videos**:
```
100 videos × $0.72 = $72.00 USD
```

---

**Última actualización**: Basado en estimaciones. Verifica precios reales en tu dashboard de OpenAI.
