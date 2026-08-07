# PRD: sitewide design token layer and demo-week performance pass

Status: draft, awaiting approval
Author: Ismet, with Claude
Date: 2026-08-04
Target version: 1.8.0
Deadline: 2026-08-06 end of day. GMD demo is 2026-08-07.
Options and reasoning: [PROPOSAL-frontend-revamp.md](PROPOSAL-frontend-revamp.md)
Decision: Option A now, Option C (block theme, `theme.json`) as the target.
**Corrected 2026-08-04, mid-build, after live testing:** the CSS base layer
cannot make Elementor-authored typography or colour inherit the tokens.
Elementor bakes its own defaults into per-widget CSS that always outranks a
generic stylesheet rule. Elementor's own Global Fonts/Colours (step 10) is
the only thing that reaches it, and step 10 moved from "nice to have" to
load-bearing as a result. Full story: [DECISIONS D27](../DOCS/DECISIONS.md#d27).

## 0. Short version

Read this part. The rest is build detail for whoever writes the code.

**The problem.** Mobile PageSpeed is 61 on the homepage and Board of Directors,
with an 11.9 second LCP. Almost all of that loss is unoptimised images and
render-blocking CSS. JavaScript is not the cause. Separately, the design tokens
that exist in the design folder never made it onto the site, so every page has
its own hardcoded colours and sizes.

**The fix, in two halves.**

1. **Configuration, on staging, no code.** Optimise images, fix CSS delivery,
   tune the plugins. This is where the score comes back.
2. **Code, released as 1.8.0.** One small stylesheet holding the design
   tokens, loaded on every page. A base layer for what Elementor doesn't
   already control per widget (focus outline, motion, container width). And
   a fix for a real bug: every Elementor Page had no `<main>` landmark.
3. **Elementor Global Fonts and Global Colours, on staging.** The only thing
   that reaches typography and colour on Elementor-authored pages. Not
   optional, not later: without this, the token layer's colours and fonts
   never appear on a single Elementor page. Geist is not available here
   (Elementor Free's font list, no custom fonts); Inter substitutes.

Then a responsive walk of every page.

**What changes for the site admin.** Nothing in the daily workflow. Elementor
still edits every page. The difference is that colours and fonts are picked from
a named list instead of typed as hex codes, new pages come out consistent
automatically, and headings scale fluidly so there is less per-breakpoint
fiddling. Existing pages keep their hardcoded values until the page-by-page pass
after the demo.

**If time runs out.** Steps 1 to 3 alone fix the score and need no code. Stop
there and the demo still improves.

**Targets:** mobile 61 to 85+, LCP 11.9s to under 2.5s, accessibility 90 to 96+,
no desktop regression, every page clean at 390, 768, 1280 and 1920px.

## 1. Goal

Two things, in priority order.

1. Get mobile PageSpeed from 61 to 85+ on the worst pages, mostly through images
   and CSS delivery.
2. Put one set of design tokens on every page of the site, so the design stops
   drifting page to page and starts scaling fluidly instead of at 3 fixed
   breakpoints.

Baseline, PageSpeed Insights 2026-08-04:

| Page | Desktop | Mobile | Mobile LCP | TBT |
|---|---|---|---|---|
| Homepage | 90 | 61 | 11.9s | 0ms |
| /board-of-directors/ | 89 | 61 | 10.9s | 30ms |
| /business/ikop-pharma-sdn-bhd/ | 96 | 74 | 4.8s | 0ms |

Accessibility 90, Best Practices 100, SEO 92 on all three.

## 2. Scope

### In

- Image and CSS delivery work on **live staging**, by configuration.
- A new sitewide stylesheet holding the `:root` design tokens, loaded on every
  page including all Elementor Pages.
- A new sitewide stylesheet holding base rules for Elementor-rendered content,
  scoped to Elementor's own class names.
- `assets/css/theme.css` loses its duplicate `:root` block and consumes the new
  one instead.
- Elementor Site Settings: Global Colours, Global Fonts, custom breakpoints.
- Three cheap accessibility fixes: `main` landmark, focusable skip link,
  discernible link names.
- The desktop CLS 0.096 shared-chrome fix.
- Release 1.8.0 and pull it to staging.
- A fast responsive walk of all pages at 4 widths.

### Out

Do not touch any of these, even if they look wrong.

- `assets/css/scroll-top.css` and `assets/js/scroll-top.js`. Deliberately
  self-contained, see [D26](../DOCS/DECISIONS.md). They do not get a dependency
  on the new token file.
- `met_hello_child_is_styled_view()` and `met_hello_child_page_has_hero()`. The
  new stylesheets get their own enqueue, exactly as Scroll to Top did. Widening
  either gate also widens the full-width body class, see
  [D3](../DOCS/DECISIONS.md) and [D25](../DOCS/DECISIONS.md).
- The MetCPT plugin, its CPTs, its templates.
- The Elementor header and footer structure, see [D2](../DOCS/DECISIONS.md).
  Adding an accessible name to a footer social link is content, not structure,
  and is allowed.
- Removing Essential Addons or Ultimate Addons. Decided: keep, configure, remove
  later under Option C.
- The [D26](../DOCS/DECISIONS.md) accent colour contrast fail. Known, unpoliced,
  not changing this week.
- SEO. Owner instruction.
- Any page rebuild.
- Rewriting page-level Elementor content to use the tokens. That is the
  page-by-page pass, and it comes after the demo.

### The boundary that matters

The token layer must make pages **inherit** the design system where they have no
opinion. It must not fight hardcoded widget values. A page that looks correct
today must still look correct on Friday. If a token rule visibly changes a page
for the worse, the rule is wrong, not the page.

## 3. Approach

### 3.1 Why a separate token file, not theme.css

`theme.css` is gated to styled views and Page Hero pages
([inc/assets.php:44](../inc/assets.php#L44)). On the homepage and most Elementor
Pages it never loads, so its `:root` tokens do not exist there. Same constraint
Scroll to Top hit, same answer: ship a separate, small, sitewide file.

Three files result, with one clear owner each.

| File | Loads | Holds | Size target |
|---|---|---|---|
| `assets/css/tokens.css` | Sitewide | `:root` tokens only. Nothing else. No selectors | Under 2KB |
| `assets/css/elementor-base.css` | Sitewide | Base rules scoped to Elementor class names | Under 4KB |
| `assets/css/theme.css` | Unchanged gate | Everything it has today, minus its `:root` block | Smaller than today |

`tokens.css` becomes the single source of truth. When the site moves to Option C,
this file is what turns into `theme.json`.

### 3.2 The merged token set

The theme and the design folder each hold part of the set, and they are not
identical. `tokens.css` is the **union**, and neither existing value gets changed.

From [assets/css/theme.css](../assets/css/theme.css), keep as-is:
all colours, both fonts, `--text-xs` to `--text-xl`, `--text-2xl`, `--text-3xl`,
`--text-title`, `--space-2` to `--space-8`, `--radius`, `--radius-lg`, `--ease`,
`--t-fast`, `--t-med`, `--container`, `--gutter`, `--reading`, `--pattern-light`.

Add, from `CLAUDE DESIGN\CLAUDE\homepage.html`:

```
--text-hero: clamp(44px, 7vw, 92px);
--edu:    #4A7BA8;
--infra:  #B98A2E;
--health: #B5566F;
```

Before writing the file, diff the two token blocks value by value. If any token
name exists in both with a different value, stop and report it. Do not pick one.

### 3.3 How the Elementor base layer avoids collisions

Scope every rule in `elementor-base.css` to Elementor's own class names:
`.elementor-widget-heading .elementor-heading-title`, `.elementor-button`,
`.elementor-widget-text-editor`, `.e-con`, `.elementor-section` and similar.

This matters because `theme.css` scopes its components under `.met-view`, and
theme templates are not Elementor-rendered. Scoping to Elementor classes means
the two layers cannot touch each other, with no `:not()` gymnastics.

Specificity, learned from [D26](../DOCS/DECISIONS.md): qualify selectors so they
sit one tier above a bare class. Do not reach for `!important` first. If a rule
genuinely cannot win without it, add it and write a comment saying what it is
fighting.

What the base layer sets, and nothing more:

- Fluid heading sizes using the `clamp()` tokens, so `h1` to `h4` scale at every
  width instead of at 3 breakpoints.
- `--container` as the content max-width, `--gutter` as the side padding.
- Section vertical rhythm from the `--space-*` scale.
- Button colour, radius, transition and hover from the tokens.
- Link colour and, importantly, a visible `:focus-visible` state.
- `box-sizing`, `img { max-width: 100% }`, and `overflow-x` protection on `body`.
- `prefers-reduced-motion` honoured.

### 3.4 What is code, and what is not

This is the operational trap in this project, so it is stated plainly.

| Work | Lives in | Reaches staging by |
|---|---|---|
| The three stylesheets, enqueue, accessibility fixes | The repo | Release 1.8.0, then update on staging |
| Elementor Global Colours, Fonts, breakpoints | The **database** | Doing it again by hand on staging |
| LiteSpeed settings, image optimisation | Staging's **server and media library** | Doing it directly on staging |

Only the first row is releasable. The other two do not travel with a theme
release. Anything done on local `v2` in rows two and three has to be repeated on
staging, and staging is what the GMD sees.

Because of that, and because the deadline is Thursday, **do the configuration and
image work directly on staging.** It is reversible, it does not touch code, and
doing it twice wastes a day we do not have.

## 4. Steps

Ordered by measured impact. Steps 1 to 3 are configuration on staging and need no
code. Steps 4 to 9 are code, built on local `v2`.

Before starting: `git pull --ff-only origin main` ([D18](../DOCS/DECISIONS.md)).

### Step 1: images on staging

The largest single win. Up to 2,752 KiB on the homepage alone.

1. LiteSpeed Cache, Image Optimization: enable WebP replacement, run optimisation
   across the media library.
2. Find the homepage LCP image. Preload it, and make sure it is **not** lazy
   loaded.
3. Lazy load everything below the fold.
4. Give every image explicit `width` and `height`. PageSpeed flags missing
   dimensions on the homepage, and this also feeds step 3.
5. Re-serve any image larger than its display size at the size actually used.

**Test:** re-run PageSpeed on the homepage, mobile. "Improve image delivery"
savings should drop below 200 KiB. Record the new LCP.

### Step 2: CSS delivery on staging

Worth 2,240ms to 5,420ms of mobile delay.

1. LiteSpeed Cache: enable CSS minify, generate Critical CSS, and load
   non-critical CSS asynchronously.
2. Set browser cache lifetimes. PageSpeed reports 233 to 264 KiB available.
3. Elementor Settings, Features: enable Improved Asset Loading, Improved CSS
   Loading, Inline Font Icons.
4. Elementor Settings, Advanced: set Google Fonts loading to Swap, or disable
   Elementor's Google Fonts if the theme's two families cover the site. The
   theme already sends `display=swap`
   ([inc/assets.php:28](../inc/assets.php#L28)), so the flagged font-display cost
   is Elementor's, not the theme's.
5. Essential Addons: enable Asset Generation. Disable every unused element in
   both Essential Addons and Ultimate Addons.

**Test:** re-run PageSpeed on all three pages, mobile. Render-blocking savings
should drop under 500ms.

### Step 3: find the Board of Directors outlier

`/board-of-directors/` loads 154 KiB of unused CSS and 5,420ms of render-blocking
work. Other pages load 12 KiB and about 2,300ms. Something loads there and
nowhere else.

Open the page, list the stylesheets, compare against the homepage, identify the
widget or plugin responsible. Report what it is. Only remove it if the fix is
obvious and safe, otherwise write it down for the page-by-page pass.

**Test:** the cause is named in the report, with the stylesheet handle.

### Step 4: `assets/css/tokens.css`

Create it with the merged set from section 3.2. `:root` only, no selectors, no
components. Comment at the top saying it is the single source of truth and that
it is the file that becomes `theme.json` under Option C.

**Test:** file exists, contains no selector other than `:root`, under 2KB.

### Step 5: `assets/css/elementor-base.css`

**Revised 2026-08-04, after live testing.** Section 3.3's original list
included heading and text-editor typography rules. Tested live on local `v2`
and found losing every time to Elementor's own generated per-widget CSS,
which bakes the Kit's default typography into a rule scoped to that widget's
element ID, always more specific than a generic class rule. Removed as dead
code. The lever for typography and colour on Elementor content is step 10
(Global Fonts/Colours), not this file. See DECISIONS D27's correction.

What stays in this file: focus outline, reduced motion, box-sizing, overflow
safety, and container width (the last one unverified against the same
override, kept anyway since it is harmless if it also loses).

**Test:** every selector in the file names an Elementor class. No bare element
selectors except inside an Elementor-scoped rule. Confirm in DevTools,
Computed panel, on a real widget, that no rule in this file claims to win a
property Elementor's own generated CSS also sets.

### Step 6: enqueue both, sitewide

Add one function to [inc/assets.php](../inc/assets.php), modelled on the
Scroll to Top enqueue in [inc/scroll-top.php](../inc/scroll-top.php#L193). No
gate. `elementor-base.css` declares `tokens.css` as a dependency so order is
guaranteed. Version both with `MET_HELLO_CHILD_VERSION`.

Do not modify `met_hello_child_is_styled_view()` or
`met_hello_child_page_has_hero()`.

**Test:** view source on the homepage, a business page, a blog post and a
category archive. Both files load on all four, `tokens.css` first.

### Step 7: strip the duplicate tokens from theme.css

Delete the `:root` block from [assets/css/theme.css](../assets/css/theme.css).
Add `met-hello-child-tokens` to its `wp_enqueue_style` dependency array so it
cannot load without them.

**This step must produce no visual change at all.** Before and after screenshots
of a single blog post, a category archive, search, author and 404 must match.
This is the step most likely to break something already working.

**Test:** the five views above look identical to before. If any differs, a token
was missed in step 4.

### Step 8: accessibility fixes

All three fail on every page today.

1. **`main` landmark.** Find out why Lighthouse reports none. Check what Hello
   Elementor's Full Width and Canvas templates output. Fix in the child theme.
2. **Skip link not focusable.** Add or fix a skip link that is hidden until
   focused and visible when focused.
3. **Links without a discernible name.** Almost certainly the footer social
   icons, since it fails on every page. Add accessible names. If it is content in
   Elementor, fix it there and say so.

**Test:** Lighthouse accessibility 96+ on all three pages. The three named audits
pass.

### Step 9: the desktop CLS 0.096

Identical on all three pages, so it is the shared header or hero, not page
content. Find it and fix it. Most likely a logo or hero image without reserved
dimensions.

**Test:** desktop CLS under 0.05 on all three pages.

### Step 10: Elementor Site Settings, on staging

**Elevated 2026-08-04: this is now load-bearing, not a nice-to-have.**
Step 5's correction found that Elementor bakes its Kit defaults into CSS
scoped to each widget, which always beats a generic stylesheet rule. This
step is the only thing that reaches Elementor-authored typography and
colour at all. Not code. Do this after 1.8.0 is live on staging, so the
token layer is already in place for the theme's native views.

1. Global Colours: Primary `#0E3B40`, Secondary `#B98A2E`, Text `#0F1419`,
   Accent `#C99A3A`. Add Education `#4A7BA8`, Infrastructure `#B98A2E`,
   Healthcare `#B5566F`.
2. Global Fonts: Primary **Inter**, not Geist. Geist is not in Elementor
   Free's font list, and self-hosted custom fonts are Elementor Pro only.
   Inter is already in the list and visually close. Owner's call,
   2026-08-04: accept the small mismatch against the theme's native views
   (which keep true Geist) rather than add a plugin or a font-registration
   snippet three days before the demo. Secondary Instrument Serif (confirm
   it is in the list; if not, treat it the same way).
3. Layout: content width 1200px, matching `--container`.
4. Breakpoints: leave the defaults unless the responsive walk finds a reason.
   Adding breakpoints multiplies generated CSS, which is what we are cutting.

Do not re-point existing widgets at the globals this week. That is the
page-by-page pass. New widgets and any widget still on "Default" pick up
these globals automatically, which is most of what exists today.

**Test:** a new widget added to a scratch page inherits the right colour and font
with no manual setting.

### Step 11: release 1.8.0

1. Bump `Version:` in [style.css](../style.css) and `MET_HELLO_CHILD_VERSION` in
   [functions.php](../functions.php).
2. Add the `= 1.8.0 =` changelog block to [readme.txt](../readme.txt).
3. Add **D27** to [DECISIONS.md](../DOCS/DECISIONS.md): why a second sitewide
   stylesheet exists, why it did not widen the D3 gate, and that it is the
   forerunner of `theme.json` under Option C.
4. Update [STATE.md](../DOCS/STATE.md) and add a
   [PROJECT_LOG.md](../DOCS/PROJECT_LOG.md) entry with the before and after
   numbers.
5. `phpcs` clean.
6. Commit, push `main`, then tag `v1.8.0` and push the tag.
7. Update the theme on staging. **Purge the LiteSpeed cache**, including Critical
   CSS, then check on staging, not local.

### Step 12: responsive walk, all pages, fast pass

Every page at 390px, 768px, 1280px, 1920px. Fix only what is clearly broken:
horizontal scroll, overlapping text, an unreadable size, a cut-off image, a
broken grid. Log everything else for later, do not fix it now.

**Test:** no page scrolls horizontally at 390px. Nothing overlaps at any of the
four widths.

### Step 13: re-measure and freeze

Re-run PageSpeed on all three pages, mobile and desktop. Record before and after
in PROJECT_LOG. After this, no further changes before the demo.

## 5. Risks

| Risk | What to do |
|---|---|
| The token layer changes a page for the worse, and it is found on demo day | Ship it Wednesday, not Thursday. Step 12 walks every page afterwards. If a rule is fighting a page, delete the rule |
| Step 7 silently changes the blog views | Screenshot the five views before touching `theme.css`. That step's only acceptable outcome is no visual change |
| LiteSpeed CSS Combine reorders or drops the new files | This exact thing caused the 1.7.1 bug, see [D26](../DOCS/DECISIONS.md). Purge cache including Critical CSS after release, then verify on staging. Never conclude from View Source, check computed styles |
| A hardcoded Elementor value beats the base layer, or an unrelated rule beats it | Qualify selectors one tier up, as D26 did with `button.met-to-top`. `!important` only with a comment naming what it fights |
| Steps 1, 2 and 10 are database and server settings and do not travel with a release | Section 3.4. Do them on staging, and write down what was changed so it can be repeated when staging migrates to the live site |
| Critical CSS is generated before 1.8.0 ships, so it is missing the new files | Regenerate Critical CSS after the release, not before |
| The whole plan is 13 steps in 3 days | Steps 1, 2 and 3 alone fix the score, and they need no code. If time runs out, stop after step 3 and the demo still improves. Steps 4 to 7 are the design half. Step 12 is not optional |
| Two machines, stale copy | `git pull --ff-only origin main` before editing. Push `main` before the tag ([D18](../DOCS/DECISIONS.md)) |

## 6. Done when

| Check | Baseline | Target |
|---|---|---|
| Homepage mobile | 61 | 85+ |
| Board of Directors mobile | 61 | 85+ |
| IKOP Pharma mobile | 74 | 90+ |
| Desktop, all three | 90, 89, 96 | No regression |
| Worst mobile LCP | 11.9s | Under 2.5s |
| Accessibility, all three | 90 | 96+ |
| Desktop CLS | 0.096 | Under 0.05 |
| Blog views after step 7 | Correct | Visually identical |
| Token layer | Does not exist | Changing one value in `tokens.css` visibly changes every page |
| Responsive walk | Not done | All pages clean at 390, 768, 1280, 1920 |
| Release | 1.7.2 | 1.8.0 tagged, live on staging, cache purged |
