# Design system: findings and decisions

Status: decisions needed, nothing built
Date: 2026-08-07
Feeds: `PRD-design-system.md` (not yet written), then
[PRD-block-system.md](PRD-block-system.md) phase 0

Every number here was measured from the design files or computed, not estimated.
Source data: 1,676 token declarations extracted from the `:root` blocks of 41
HTML files in `CLAUDE DESIGN`.

## 1. Two corrections to what I said earlier

Both were wrong in the same direction. I overstated the mess.

**"There are 21 design systems."** There are two. The 21 distinct `:root` blocks
differ mostly by whitespace and by which subset of tokens they carry, not by
value. Of 71 token names, 57 have exactly one value everywhere.

**"Two competing naming philosophies, primitive and semantic."** No. Every
semantic name (`--accent`, `--primary`, `--secondary`, `--bg-soft`, `--bg-pure`,
`--text-main`, `--text-light`) comes from **one file**: `ABOUT US PAGE HTML 1.html`,
dated 2026-01-15, four months older than anything else. Its colours are a
different identity altogether: `--primary: #006766` teal, not petrol `#0E3B40`.
`--text-main: #2C3E50` blue-grey, not ink `#0F1419`.

That file is not a variant of this design system. It is an abandoned earlier one.
**Excluded from the corpus.** Working set: **40 files.**

## 2. The real finding: System A and System B

Eleven tokens have genuine value conflicts. Every one splits **32 files to 8**,
with identical membership on all eleven. That is not drift. It is one system and
a later revision of it.

**System B, 8 files:** `iium-holdings-redesign`, `board-of-directors`,
`director`, `iium-holdings-25th-anniversary`, `our-subsidiaries`, `contact-us`,
`contact-us-redesigned`, `contact-directory`.

**System A, 32 files:** everything else, including `homepage`, all 9 director
profiles, all 9 subsidiary pages, `whistleblowing`, `rise2030`,
`management-team`, `about-the-group`, `contact-us-v2`.

### 2.1 Where they disagree

| Token | System A (32) | System B (8) | My recommendation |
|---|---|---|---|
| `--container` | `1200px` | `1280px` | **Decide by eye.** B is more modern, A matches the shipped theme |
| `--paper` | `#F4EFE7` | `#F7F3EC` | **B.** Lighter, and it raises every contrast ratio slightly |
| `--gold-soft` | `#C99A3A` | `#D4A547` | **Decide by eye.** Both pass on petrol |
| `--petrol-deep` | `#0A0A0A` | `#082226` | **B, strongly.** A's value is near-black, not petrol. B's is an actual deep petrol and belongs in the palette |
| `--radius` | `10px` | `8px` | **Decide by eye.** B is tighter, more current |
| `--radius-lg` | `16px` | `14px` | Follow whatever `--radius` picks |
| `--gutter` | `clamp(20px,4vw,40px)` | `clamp(20px,4vw,32px)` | **A.** 40px gutters at desktop suit a 1200px+ container |
| `--text-2xl` | `clamp(24px,2.6vw,32px)` | `clamp(28px,3vw,36px)` | **Decide by eye.** B is a bigger, more confident scale |
| `--text-3xl` | `clamp(30px,3.4vw,42px)` | `clamp(36px,4.5vw,56px)` | Follow whatever `--text-2xl` picks. Do not mix |
| `--font-serif` | `Instrument Serif, Georgia, serif` | adds `"Iowan Old Style"` | **B.** A better fallback, costs nothing |
| `--text-hero` | **12 different values** | see below | **Must be settled.** Worst drift in the corpus |

### 2.2 `--text-hero` is the worst offender in the whole corpus

Thirty files, **twelve different values**, from `clamp(28px,3.5vw,46px)` to
`clamp(44px,8vw,108px)`. The two most common are `clamp(38px,5.6vw,68px)` and
`clamp(30px,3.8vw,50px)`, 8 files each. The shipped `tokens.css` uses
`clamp(44px,7vw,92px)`, which appears in exactly **one** design file.

This single token explains most of the "why do my pages not feel the same"
problem. It has to be one value, chosen by eye in the gallery.

### 2.3 What System B has that System A lacks entirely

These are not conflicts. They are gaps in A that B already filled. **Take all of
them**, regardless of which system wins on the table above.

| Token | Value | Why it matters |
|---|---|---|
| `--section-y` | `clamp(72px,10vw,128px)` | Vertical section rhythm. A has **no rhythm token at all**, which is why section spacing varies page to page |
| `--section-y-slow` | `clamp(96px,12vw,160px)` | The larger rhythm step |
| `--hairline-light` | `rgba(255,255,255,0.12)` | Borders on dark backgrounds. A has none, so dark sections have no correct border colour |
| `--radius-sm` | `4px` | Completes the radius scale |
| `--radius-xl` | `20px` | Completes the radius scale |
| `--t-slow` | `520ms` | Completes the motion scale |
| `--ease-out` | `cubic-bezier(0.16,1,0.3,1)` | Entrance easing. A has only one curve |
| `--text-4xl` | `clamp(44px,6vw,80px)` | Fills the gap between `3xl` and `hero` |

### 2.4 Small duplications to collapse

- `--edu` / `--infra` / `--health` (6 to 8 files) and `--div-education` /
  `--div-infrastructure` / `--div-healthcare` (2 files) are the same three
  colours under two names. Keep one naming.
- `--pharma: #5B8C6E` in `rise2030` and `--green: #5B8C6E` in `daya-bersih` are
  the **same value under two names**, and it is a fourth sector colour that never
  reached `tokens.css`.
- `--infra` is `#B98A2E`, which is the same value as `--gold`. Decide whether
  infrastructure genuinely shares the brand accent or needs its own colour.

## 3. Three layers no file has, anywhere

Confirmed by scanning all 40 files: **zero** shadow, elevation, z-index or focus
tokens exist. These are proposals, marked as mine, derived from what the existing
design implies.

**Elevation.** Four steps, tuned to a warm paper background rather than pure
white, so shadows read as petrol-tinted rather than grey.

**Z-index scale.** Needed now, not later: Scroll to Top, a sticky header, the
mobile menu and any future modal all stack. Without a scale they fight with
arbitrary numbers. Proposal: `base 0`, `raised 10`, `sticky 100`, `header 200`,
`overlay 800`, `modal 900`, `toast 1000`.

**Focus.** [assets/css/elementor-base.css](../assets/css/elementor-base.css)
already sets a focus outline but reads no token. Needs `--color-focus`, a width
and an offset, so every interactive element focuses identically.

## 4. Contrast audit, computed

WCAG 2.1. AA normal text needs 4.5:1, AA large text needs 3:1.

| Pair | Ratio | AA normal | AA large |
|---|---|---|---|
| ink `#0F1419` on paper | 16.17 | Pass | Pass |
| ink-soft `#5A6168` on paper | 5.49 | Pass | Pass |
| **ink-faint `#8A9098` on paper** | **2.81** | **Fail** | **Fail** |
| **gold `#B98A2E` on paper** | **2.72** | **Fail** | **Fail** |
| gold `#B98A2E` on white | 3.12 | Fail | Pass |
| gold `#B98A2E` on petrol | 3.92 | Fail | Pass |
| gold `#B98A2E` on petrol-deep B `#082226` | 5.32 | Pass | Pass |
| gold-soft A `#C99A3A` on petrol | 4.75 | Pass | Pass |
| gold-soft B `#D4A547` on petrol | 5.40 | Pass | Pass |
| white on petrol | 12.22 | Pass | Pass |
| edu `#4A7BA8` on white | 4.48 | Fail, just | Pass |
| health `#B5566F` on white | 4.64 | Pass | Pass |
| green `#5B8C6E` on white | 3.87 | Fail | Pass |

### 4.1 Two findings worse than D26

**`--ink-faint` is the bigger problem, and it was never recorded.**
[D26](../DOCS/DECISIONS.md#d26) tracks the gold. Nobody tracked `--ink-faint`,
which at 2.81 on paper fails even the large-text threshold. It is used for
captions, metadata and eyebrow labels, which are exactly the small text where it
matters most.

**Gold on paper is the worst pair on the site at 2.72**, worse than gold on
white. Every gold label sitting on a cream section fails.

### 4.2 The fix: a display value and a text value for every accent

Standard practice, and it keeps the brand looking identical. The bright colour
stays for headings, rules, icons and decoration. A darker sibling is used for
small text. At 14px the two read as the same colour.

| Role | Value | On white | On paper A | On paper B |
|---|---|---|---|---|
| Accent, display | `#B98A2E` unchanged | 3.12 | 2.72 | large text and decoration only |
| **Accent, text** | **`#85621E`** | 5.58 | 4.88 | 5.05 |
| **Text tertiary**, replacing ink-faint | **`#646B72`** | 5.40 | 4.72 | 4.88 |
| **Education, text** | **`#3F6B93`** | 5.62 | 4.91 | 5.08 |
| **Environment, text** | **`#487256`** | 5.50 | 4.81 | 4.97 |
| Healthcare, text | needs a value, `#B5566F` fails on paper at 4.05 | | | |

Note `#8F6A22`, the value I gave you earlier in chat, passes on white at 4.94 but
**fails on paper at 4.31**. `#85621E` is the corrected value. Use that one.

## 5. Proposed two-layer naming

You picked two layers. This is the map. Primitives name the paint and are never
used directly. Semantics name the job and are what everything references, and
what staff see in the block editor.

**Primitives**

```
--ink-900 --ink-600 --ink-400
--paper-100 --paper-000
--petrol-900 --petrol-700 --petrol-500
--gold-700 --gold-500 --gold-400
--edu-700 --edu-500   --health-700 --health-500
--green-700 --green-500
```

**Semantics, and the palette slug staff will see**

| Semantic token | Slug in the editor | Maps to |
|---|---|---|
| `--color-text-primary` | Text | `--ink-900` |
| `--color-text-secondary` | Text secondary | `--ink-600` |
| `--color-text-tertiary` | Text muted | `--ink-400` |
| `--color-text-on-dark` | Text on dark | `--paper-000` |
| `--color-surface` | Surface | `--paper-100` |
| `--color-surface-raised` | Surface raised | `--paper-000` |
| `--color-surface-inverse` | Surface dark | `--petrol-700` |
| `--color-surface-inverse-deep` | Surface deepest | `--petrol-900` |
| `--color-accent` | Accent | `--gold-500` |
| `--color-accent-text` | Accent text | `--gold-700` |
| `--color-accent-soft` | Accent soft | `--gold-400` |
| `--color-border` | | `--hairline` |
| `--color-border-strong` | | `--hairline-strong` |
| `--color-border-on-dark` | | `--hairline-light` |
| `--color-focus` | | new, section 3 |
| `--color-sector-*` | Education / Infrastructure / Healthcare / Environment | sector primitives |

Slugs are what WordPress writes into every saved page as `has-{slug}-color`.
They are effectively permanent once pages exist, which is why this table is
being signed off before batch 1, not after.

`tokens.css` keeps the old flat names as aliases pointing at the semantic layer,
so `theme.css`, `scroll-top.css` and `elementor-base.css` need no rewrite.

## 6. What the gallery must show

One self-contained HTML file, opens offline, no build step.

1. **Every A-versus-B conflict rendered side by side**, section 2.1, with both
   options shown as real components, not swatches. This is the point of the
   gallery.
2. `--text-hero`, all 12 values, rendered as real headlines at real size.
3. Full colour system: primitives, semantic mapping, and every foreground and
   background pair with its computed ratio and a pass or fail badge.
4. Type scale rendered at real size with role labels and `clamp()` ranges.
5. Spacing and section rhythm drawn as comparable bars.
6. Elevation, radius, z-index, motion. Motion as clickable demos.
7. Every component in every state: default, hover, focus-visible, active,
   disabled, error. Buttons, links, cards, form fields, tables, lists,
   blockquote, eyebrow labels, section headers, both hero variants, figures and
   stats blocks, CTA bands, breadcrumbs, pagination.
8. The same components at 390, 768 and 1280.

## 7. Decisions needed from the owner

| # | Decision | My recommendation |
|---|---|---|
| 1 | `--container` 1200 or 1280 | By eye |
| 2 | `--paper` `#F4EFE7` or `#F7F3EC` | `#F7F3EC` |
| 3 | `--gold-soft` `#C99A3A` or `#D4A547` | By eye |
| 4 | `--petrol-deep` `#0A0A0A` or `#082226` | `#082226`, strongly |
| 5 | `--radius` 10/16 or 8/14 | By eye |
| 6 | Type scale A or B | By eye, but do not mix 2xl and 3xl across systems |
| 7 | `--text-hero`, one of 12 | By eye. The most consequential single choice here |
| 8 | Adopt all 8 System B gap-fillers, section 2.3 | Yes |
| 9 | Accent text `#85621E`, tertiary `#646B72` | Yes, this is what reaches Accessibility 100 |
| 10 | Sector naming: `--edu` or `--div-education` | `--color-sector-education` under the new scheme |
| 11 | Is `--infra` really the same as `--gold`? | Owner call, it is a brand question |
| 12 | Healthcare text value | I will propose one in the gallery |
| 13 | Elevation, z-index, focus proposals | Review in the gallery |

Decisions 1 to 7 are all "by eye" and are exactly what the gallery exists to
answer. Nothing needs deciding before it is built.

## 8. Consequence for batch 1

`board-of-directors.html` is **System B**. The other nine batch 1 pages are
System A. Whichever system wins, one design in that batch needs translating into
it. Small, but it should not be a surprise mid-build.
