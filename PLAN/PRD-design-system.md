# PRD: design system foundation

Status: approved 2026-08-07, **implemented the same day** in `theme.json`,
`assets/fonts/` and `assets/css/tokens.css`. Section 7's steps are done and
phpcs clean on local `v2`, not released. Recorded as
[DECISIONS D28](../DOCS/DECISIONS.md#d28). Two items in section 6 remain open
by design: `--text-hero` (still the placeholder value, owner picks from the
gallery) and whether infrastructure should keep sharing the brand gold.
Feeds: [PRD-block-system.md](PRD-block-system.md) phase 0, step 1
Reference: [DESIGN-SYSTEM-DECISIONS.md](DESIGN-SYSTEM-DECISIONS.md) (the findings),
[DESIGN-SYSTEM-GALLERY.html](DESIGN-SYSTEM-GALLERY.html) (the visual review, open in
a browser)

## 1. Goal

Turn the 40 approved design files' 21 partial token sets into one canonical
system: named, valued, contrast-checked, and ready to become `theme.json`.

## 2. Scope

**In.** Colour (primitives, semantics, contrast fixes), type scale, spacing and
section rhythm, layout widths, elevation, radius, z-index, focus, motion, font
self-hosting. The token names and values only, not the components built from
them, those are harvested per page batch as [PRD-block-system.md](PRD-block-system.md)
phase 1 runs.

**Out.** Any theme.json write, any CSS file change, any template change. This
document is the spec. The block-system PRD's phase 0 is where it becomes code.

## 3. Approach

Extracted 1,676 token declarations from 40 files (`ABOUT US PAGE HTML 1.html`
excluded as a distinct, abandoned earlier design, see
[DESIGN-SYSTEM-DECISIONS.md](DESIGN-SYSTEM-DECISIONS.md) section 1). Found two
systems, not 21: System A, 32 files, and System B, 8 files, a later and more
complete revision. Eleven tokens conflict between them, all with identical file
membership. Reviewed as a live, interactive gallery. No exceptions raised.

Two-layer naming: primitives name the paint, semantics name the job. The block
editor writes semantic slugs into every saved page, so this is the one decision
in the whole project that is expensive to change after pages exist. Settled
before any page is built.

## 4. Decisions, final

### 4.1 Colour primitives

```
--ink-900   #0F1419      --paper-100   #F7F3EC      --petrol-900  #082226
--ink-600   #5A6168      --paper-000   #FFFFFF      --petrol-700  #0E3B40
--ink-500   #646B72*     --gold-700    #85621E*     --petrol-500  #1A5258
--ink-400   #8A9098      --gold-500    #B98A2E
                         --gold-400    #D4A547

--edu-700    #3F6B93*    --health-700  #A3465F*     --green-700  #487256*
--edu-500    #4A7BA8     --health-500  #B5566F      --green-500  #5B8C6E
```
`*` new: proposed in the gallery to fix a measured contrast failure or fill a
naming gap. Not in any design file at this exact value.

### 4.2 Semantic layer, and the slug staff see in the editor

| Semantic token | Editor slug | Value |
|---|---|---|
| `--color-text-primary` | Text | `ink-900` |
| `--color-text-secondary` | Text secondary | `ink-600` |
| `--color-text-tertiary` | Text muted | `ink-500` (fixed, was `ink-400`) |
| `--color-text-on-dark` | Text on dark | `paper-000` |
| `--color-text-on-dark-secondary` | | `rgba(255,255,255,.72)` |
| `--color-surface` | Surface | `paper-100` |
| `--color-surface-raised` | Surface raised | `paper-000` |
| `--color-surface-inverse` | Surface dark | `petrol-700` |
| `--color-surface-inverse-deep` | Surface deepest | `petrol-900` |
| `--color-accent` | Accent | `gold-500`. Display only: headings, icons, decoration, large text |
| `--color-accent-text` | Accent text | `gold-700` (fixed, was `gold-500`). Body text, labels, small links |
| `--color-accent-soft` | Accent soft | `gold-400` |
| `--color-sector-education` / `-text` | Education | `edu-500` / `edu-700` |
| `--color-sector-healthcare` / `-text` | Healthcare | `health-500` / `health-700` |
| `--color-sector-environment` / `-text` | Environment | `green-500` / `green-700` |
| `--color-sector-infrastructure` | Infrastructure | `gold-500`, unresolved, section 6 |
| `--color-border` / `-strong` / `-on-dark` | | `hairline` / `-strong` / `-light` |
| `--color-focus` | | `gold-500` |

Display-vs-text split exists only for accent and the three sector colours,
because those are the four pairs that failed AA at small sizes. Ink and paper
already pass at every size, so they stay single tokens.

### 4.3 Type

```
--text-xs 12px  --text-sm 13px  --text-base 15px  --text-md 17px
--text-lg 19px  --text-xl 22px
--text-2xl  clamp(28px,3vw,36px)
--text-3xl  clamp(36px,4.5vw,56px)
--text-4xl  clamp(44px,6vw,80px)
--text-hero clamp(44px,7vw,92px)   ** placeholder, see section 6
```
`--font-sans: "Geist", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif`
`--font-serif: "Instrument Serif", "Iowan Old Style", Georgia, serif`

### 4.4 Spacing, rhythm, layout

```
--space-1 4px  --space-2 8px  --space-3 12px  --space-4 16px  --space-5 24px
--space-6 32px --space-7 48px --space-8 64px  --space-9 96px  --space-10 128px
--section-y       clamp(72px,10vw,128px)
--section-y-slow  clamp(96px,12vw,160px)
--container       1280px
--container-narrow 880px
--gutter          clamp(20px,4vw,40px)
--reading         72ch
```

### 4.5 Elevation, radius, z-index, motion, focus

None existed in any design file. Proposed, shown in the gallery, unopposed.

```
--radius-sm 4px  --radius 8px  --radius-lg 14px  --radius-xl 20px
--shadow-1 .. --shadow-4   petrol-tinted, not grey, see the gallery for values
--z-base 0  --z-raised 10  --z-sticky 100  --z-header 200
--z-overlay 800  --z-modal 900  --z-toast 1000
--ease cubic-bezier(.2,.8,.2,1)   --ease-out cubic-bezier(.16,1,.3,1)
--t-fast 180ms  --t-med 280ms  --t-slow 520ms
--color-focus: var(--gold-500), 2px solid, 3px offset
```

### 4.6 Fonts: self-hosted

Geist and Instrument Serif move to `assets/fonts/`, declared as `theme.json`
`fontFace` entries. Closes the [D15](../DOCS/DECISIONS.md#d15) CDN TODO and the
[D27](../DOCS/DECISIONS.md#d27) Inter-versus-Geist gap in one step, since
Elementor pages get their fonts from the same `theme.json` source once Global
Fonts is repointed there.

## 5. Contrast, before and after

| Pair | Before | After |
|---|---|---|
| Accent text on Surface | 2.72, fail | 4.88, pass |
| Accent text on White | 3.12, fail | 5.58, pass |
| Text tertiary on Surface | 2.81, fail | 4.72, pass |
| Education text on White | 4.48, fail | 5.62, pass |
| Environment text on White | 3.87, fail | 5.50, pass |
| Healthcare text on White | 4.64, pass already | 5.84, pass |

Display colours (`--color-accent`, used for headings, icons, large text and
decoration) are unchanged. The brand looks the same. Only where those colours
were being used as small text does the value change, and only there.

**A live bug this closes, found while reviewing the shipped theme.**
[assets/css/theme.css:61](../assets/css/theme.css#L61) colours the eyebrow label
(`--text-sm`) with `var(--gold)`, and line 147 colours in-body post links the
same way. Both are on the currently-shipped blog. Both are the exact small-text
failure this fix targets. Repointing those two declarations to
`--color-accent-text` in the phase 0 token sweep fixes a real accessibility
defect on the live site, not a hypothetical one.

## 6. Still open, not blocking

1. **`--text-hero`.** The most consequential single value found, 12 candidates
   in the design files, none clearly dominant. Ships with the current
   `tokens.css` value as a placeholder. One-line change in `theme.json` whenever
   the owner picks from the gallery's hero-variant selector.
2. **Whether `--color-sector-infrastructure` should equal `--color-accent`.**
   Both are `gold-500` today, which may be intentional (infrastructure as the
   "default" sector) or accidental (never given its own colour). A brand
   decision, not a technical one. No change until decided.

## 7. Steps

1. Write the values in sections 4.1 to 4.6 into `theme.json`, with the four
   `custom` switches off (`color.custom`, `color.defaultPalette`,
   `typography.customFontSize`, `spacing.customSpacingSize`) so the editor
   offers only this system.
2. Self-host the two fonts, wire `fontFace` entries, retire the Google Fonts
   enqueue.
3. Rewrite `tokens.css` as an alias layer: every existing flat name
   (`--gold`, `--ink-faint`, and so on) points at the matching semantic token,
   so `theme.css`, `scroll-top.css` and `elementor-base.css` need no rewrite.
4. Sweep `theme.css` for every place a display colour is used as small text and
   repoint it to the matching `-text` token. The two confirmed instances are
   section 5. Check the rest while there.
5. Verify: every existing page, Elementor and native, renders unchanged in
   layout. Colours shift by the amounts in section 5, nothing else moves.
   `phpcs` clean.

This is [PRD-block-system.md](PRD-block-system.md) phase 0, steps 1 and 1a,
now fully specified. Phase 0 proceeds from here.

## 8. Done when

- `theme.json` ships the values in section 4, verified against the gallery.
- The two confirmed contrast failures in section 5 measure as passing on the
  live site.
- Native views (single, archive, author, search, 404) render with no layout
  change, colours only, per section 5's before/after.
- A colour picked in the block editor offers only this palette, nothing else.
