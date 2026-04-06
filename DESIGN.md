# Clinical Sanctuary Design System

## 1. Overview & Creative North Star
**Creative North Star: The Digital Curator**
Clinical Sanctuary is a high-end editorial design system tailored for medical precision and diagnostic excellence. It moves away from the sterile, rigid "software" look in favor of a curated, trust-inducing aesthetic. By utilizing intentional asymmetry, tonal layering, and sophisticated typography scales, the system communicates security and professional calm. It is designed to feel like a high-end medical journal—authoritative yet accessible.

## 2. Colors
The palette is built on a foundation of deep blues and clinical teals, emphasizing stability and clarity.

- **The "No-Line" Rule:** Sectioning must never rely on 1px solid borders. Separation is achieved through background color shifts (e.g., transitioning from `surface_container_lowest` to `surface_container_low`) or the use of generous whitespace.
- **Surface Hierarchy & Nesting:** Use `surface_container_lowest` (#ffffff) for primary content cards to create a "floating" effect on the `surface` (#f9f9ff) background. Use higher tiers (`surface_container_highest`) for decorative or secondary informational bento-style boxes.
- **The "Glass & Gradient" Rule:** Floating overlays and bottom-anchored content labels must use `glass-effect` (White at 70% opacity with a 20px backdrop blur) to maintain context and depth. 
- **Signature Textures:** Main Action Buttons utilize the `bg-medical-gradient` (a 135-degree linear gradient from `#005bb0` to `#0a74da`) to give them a luminous, high-quality finish.

## 3. Typography
Clinical Sanctuary uses a dual-font approach to balance personality with readability.

- **Display & Headlines (Manrope):** Chosen for its modern, geometric structure. Large headings (2.25rem) use "Extrabold" weight and tight tracking (-0.05em) to create a confident editorial impact.
- **Body & Labels (Inter):** A workhorse typeface for clinical data. It provides exceptional legibility at small sizes (10px - 0.75rem) and professional clarity for forms.

**Typography Scale (Ground Truth):**
- **Display 1:** 2.25rem (36px) - Heavy headlines.
- **Headline 1:** 1.5rem (24px) - Card titles.
- **Sub-headline:** 1.25rem (20px) - Emphasis points.
- **Body Large:** 1.125rem (18px) - Featured descriptions.
- **Body Standard:** 0.875rem (14px) - Standard functional text.
- **Label / Detail:** 0.75rem (12px) - Secondary instructions.
- **Micro / Caption:** 0.6875rem (11px) or 10px - Legal and fine print.

## 4. Elevation & Depth
Elevation is communicated through soft, environmental light and tonal stacking rather than harsh outlines.

- **The Layering Principle:** Stack cards by contrasting `surface_container_lowest` against `surface_container_low`.
- **Ambient Shadows:** 
  - **Standard Card Elevation:** `0px 12px 32px rgba(10, 22, 40, 0.06)` — This creates a soft, large-radius glow that feels natural.
  - **Functional Elevation:** Use the `shadow-lg` preset for active states and `shadow-inner` for success-state indicators to provide tactile feedback.
- **The "Ghost Border" Fallback:** When high-density data requires boundaries, use `outline_variant` at 15% opacity.

## 5. Components
- **Buttons:** Primary buttons use the medical gradient with rounded-lg (0.25rem to 0.5rem) corners. They must include a subtle `shadow-primary/20` to lift them from the page.
- **Form Fields:** Inputs are borderless, utilizing a `ring-1 ring-outline-variant` for structure. On focus, they transition to a `ring-2 ring-primary` and a pure white background.
- **Bento Cards:** Use rounded-xl (0.5rem) containers with varied background tones (`secondary-container/20`) to create a mosaic of information.
- **Status Chips:** Use high-contrast pill shapes (rounded-full) with tracking-widest (all-caps) for system-level notifications like "Preview" or "Clinical Tip".

## 6. Do's and Don'ts
- **Do:** Use `mix-blend-multiply` on medical photography inside `surface_container` boxes to make images feel like part of the UI rather than separate assets.
- **Do:** Maintain a `selection:bg-primary-fixed` style for text highlighting to preserve brand continuity.
- **Don't:** Use standard black (#000000) for text. Use `on_surface` (#101c2e) to maintain a soft, premium feel.
- **Don't:** Overuse icons. Icons should be `Material Symbols Outlined` at weight 400, used as focal points rather than decorative fillers.
- **Do:** Use 15-minute expiration windows for security links to maintain clinical integrity and trust.