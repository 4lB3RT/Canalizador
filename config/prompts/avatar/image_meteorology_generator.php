<?php

return [
    'avatar_prompt' => <<<'PROMPT'
You are an expert at generating photorealistic images of weather presenters on a GREEN SCREEN (chroma key) background for later compositing in post-production.

=== CONTEXT AND PURPOSE ===
Your task is to generate a photorealistic, high-quality image of a person standing in front of a bright green chroma key screen. The person is a weather presenter who will be composited over a real weather map of Spain in post-production. The image must be realistic, photographic quality, with broadcast-standard lighting. This is NOT an artistic or stylized image — it must look like a real green screen studio photograph.

=== AVATAR INFORMATION ===
You have access to the following information about this avatar:
- Name: {avatar_name}
- Biography: {biography}
- Presentation Style: {presentation_style}
- Physical Description: {avatar_description}

Use this information to ensure the person in the generated image matches the avatar's physical appearance, personality, and style as described.

=== GREEN SCREEN REQUIREMENTS ===

1. BACKGROUND (CRITICAL — this is the most important requirement):
   - The background MUST be a perfectly uniform, bright green chroma key screen (#00FF00)
   - The green MUST be evenly lit with ZERO shadows, wrinkles, creases, or color variations
   - The green screen MUST extend fully behind and below the presenter — no floor visible, no edges of the screen
   - NO objects, screens, desks, podiums, cameras, or any equipment visible
   - NO maps, weather graphics, or any content on the background — ONLY solid bright green

2. PRESENTER POSITIONING:
   - FULL BODY visible from head to feet — the entire person must be in frame
   - Centered in the frame with generous space on all sides (for compositing flexibility)
   - Standing position — no desk, no podium, no chair
   - Leave enough clear green margin around the presenter for clean chroma keying

3. LIGHTING (designed for clean chroma key extraction):
   - Professional broadcast key light + fill light on the presenter's face and body
   - Slight backlight / edge light to create a clean separation between presenter and green screen
   - Separate, even green screen lighting — the green must be uniformly bright with no hotspots
   - Warm, professional broadcast color temperature on the presenter
   - NO green light spill on the presenter's skin, hair, or clothing

4. CLOTHING RESTRICTIONS:
   - The presenter MUST NOT wear any green clothing, accessories, or jewelry
   - Avoid highly reflective materials that could pick up green spill from the background
   - Professional weather presenter attire (suit, blazer, dress) in non-green colors

=== IMAGE QUALITY REQUIREMENTS ===
- **PHOTOREALISTIC**: Must look like a real photo taken in a green screen studio
- **HIGH QUALITY**: Sharp focus, natural skin texture, realistic materials and fabric
- **CLEAN EDGES**: Crisp, well-defined edges on the presenter's silhouette (hair, shoulders, arms, legs, feet) — critical for chroma keying
- **NO GREEN SPILL**: No green color contamination on the presenter's skin, hair, or clothing edges

=== IMAGE COMPOSITION ===
Generate 1 single image:
- Front view, facing the camera directly, centered in the frame
- Hands visible and relaxed at sides or one hand slightly raised as if about to gesture toward a screen behind
- The presenter should look natural and confident, as if they are presenting in front of a real weather map that will be added later

=== CRITICAL RULES ===
- MUST have a perfectly uniform bright green (#00FF00) chroma key background with NO variations
- MUST be photorealistic — no stylization, no artistic effects, no cartoon-like appearance
- MUST match the avatar description exactly
- MUST show FULL BODY from head to feet
- MUST NOT include any maps, screens, desks, studio equipment, or any objects
- MUST NOT dress the presenter in green clothing or accessories
- MUST have clean edge separation between presenter and green background
- MUST use professional lighting with NO green spill on the presenter
PROMPT,

    'set_prompt' => <<<'PROMPT'
You are an expert at generating photorealistic images of professional television broadcast studios for weather news programs.

=== CONTEXT AND PURPOSE ===
Your task is to generate a photorealistic image of an EMPTY professional TV weather studio set (no people). This image will be used as a background in a weather news broadcast where a presenter will speak directly to camera. The studio should have a modern, generic broadcast aesthetic — no weather maps, no chroma key screens. The image must look like a real photograph taken inside a television studio.

=== STUDIO SET REQUIREMENTS ===

1. MAIN DISPLAY / BACKGROUND:
   - The studio MUST have a large screen or video wall prominently visible behind the presenter area
   - The screen MUST display a perfectly uniform, bright green chroma key color (#00FF00) — solid green, no gradients, no graphics
   - The green MUST be evenly lit with ZERO shadows, hotspots, or color variations across the screen surface
   - This green screen area will be replaced with a weather map in post-production via chroma key compositing

2. STUDIO ELEMENTS:
   - Professional broadcast news desk or anchor area in the foreground
   - Modern studio design consistent with a major television network weather segment
   - Broadcast-quality studio lighting rigs visible or implied (softboxes, LED panels)
   - Studio cameras or camera tracks may be partially visible at the edges
   - Additional smaller monitors or screens showing news graphics are acceptable
   - Professional flooring (polished, studio-grade)

3. LIGHTING:
   - Professional broadcast studio lighting — even, warm, high-quality
   - Ambient studio lighting creating a professional broadcast atmosphere
   - Subtle accent lighting on the desk and studio furniture
   - No harsh shadows or uneven illumination

4. ATMOSPHERE:
   - Clean, modern, professional television studio aesthetic
   - High-end production value — this should look like a major network weather studio
   - Organized and polished — no cables, no clutter, no behind-the-scenes mess

=== CRITICAL CONSTRAINTS ===
- ABSOLUTELY NO PEOPLE in the image — the studio must be completely empty of humans
- The ONLY green (#00FF00) in the image must be the main LED screen/video wall — nowhere else
- NO specific weather maps, temperature data, or city names on any screen
- Must be photorealistic — no stylization, no artistic effects, no 3D renders
- Camera angle: wide shot from a studio camera position, showing the full set from the front

=== IMAGE QUALITY REQUIREMENTS ===
- **PHOTOREALISTIC**: Must look like a real photograph of a TV studio
- **HIGH QUALITY**: Sharp focus, realistic materials, accurate lighting
- **PROFESSIONAL**: Broadcast-quality studio that looks like it belongs to a major TV network
PROMPT,
];
