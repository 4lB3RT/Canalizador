# Post Plan

Crea un post de planificación en español para X en el hilo de Canalizador.
Actúa como un **Product Owner**: define funcionalidades desde la perspectiva del usuario, sin tecnicismos.
Funciona como un Jira público usando el formato **User Story + Acceptance Criteria (Gherkin)**.

## Configuración del hilo

**IMPORTANTE**: El post debe ser respuesta al hilo de Canalizador.
- ID del hilo principal: `1969071304604041350`
- URL: https://x.com/4LB3RTTT/status/1969071304604041350

## Instrucciones

1. Obtén la fecha actual con `date +%Y-%m-%d`
2. Usa el argumento como contexto para definir la funcionalidad
3. Redacta como PO: enfócate en el valor para el usuario, no en la implementación técnica
4. Publica con `mcp__twitter__tweet` usando `reply_to: "1969071304604041350"`

## Estructura del post (User Story + Gherkin)

```
[FECHA] 📋

[TÍTULO - Nombre corto de la funcionalidad]

Como: [tipo de usuario]
Quiero: [qué funcionalidad]
Para: [qué beneficio/valor]

Contexto: [por qué surge esta necesidad, qué problema resuelve, o información relevante]

Criterios de aceptación:
□ Dado [contexto], cuando [acción], entonces [resultado]
□ Dado [contexto], cuando [acción], entonces [resultado]
□ ...

#BuildInPublic #Canalizador #Roadmap
```

## Principios INVEST para validar la historia

- **I**ndependent - ¿Se puede desarrollar sin depender de otras?
- **N**egotiable - ¿El alcance es flexible?
- **V**aluable - ¿Aporta valor real al usuario?
- **E**stimable - ¿Se puede estimar el esfuerzo?
- **S**mall - ¿Es suficientemente pequeña?
- **T**estable - ¿Se puede verificar que funciona?

## Hashtags recomendados

#BuildInPublic #Canalizador #Roadmap #IndieDev #DevLife #Planificacion

## Ejemplo

```
2026-01-21 📋

Avatar Gaming 3 Perspectivas

Como: creador de contenido gaming
Quiero: subir mi foto y obtener 3 imágenes en un setup gaming
Para: tener un pack profesional para mis redes

Contexto: Los creadores necesitan imágenes consistentes para su branding pero no tienen acceso a sesiones de fotos profesionales en setups gaming.

Criterios de aceptación:
□ Dado una foto, cuando la subo, entonces obtengo 3 imágenes
□ Dado el resultado, cuando lo veo, entonces hay vista frontal, 30° izq y 30° der
□ Dado las imágenes, cuando las descargo, entonces son realistas y de alta calidad

#BuildInPublic #Canalizador #Roadmap #Gaming #AI
```

## Argumento

$ARGUMENTS - Describe la funcionalidad que quieres planificar (qué usuario, qué quiere, para qué)
