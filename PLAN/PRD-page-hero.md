# PRD: Page Hero

Status: draft, awaiting approval
Author: Ismet, with Claude
Date: 2026-08-03
Target version: 1.6.0
Scope note: this covers the page header only. Body layout of these pages is out.

## 1. Goal

Give selected Elementor-built Pages the same header quality as the shipped single
post and archive views, so a reader moving between a news post, a category
archive and a corporate page sees one design, not three.

Today each of those Pages carries a header hand-built in Elementor. They differ in
spacing, type scale and colour. That break in rhythm is what costs reading
retention.

### Success measures

| Measure | Now | Target |
|---|---|---|
| Pages with a header matching the theme design system | 0 of 16 | 16 of 16 |
| Distinct header designs across the site | 5 or more | 2 (standard, business) |
| Header copy editable without a code release | No | Yes |

## 2. Scope

### In

- A reusable page hero component with two variants:
  - **Standard**: the compact petrol band already shipped as `.met-hero`.
  - **Business**: the taller hero from the CLAUDE DESIGN files, with a tag pill,
    icon mark, name, positioning line and two buttons.
- A "Page Hero" meta box on the Page edit screen. Fields: variant, eyebrow,
  subtitle, and for the business variant, two CTA label and URL pairs.
- Rendering on the Elementor Full Width page layout, which all 16 Pages use.
- A performance budget, measured before and after on staging.
- Applying the standard variant to these 16 Pages:

  `/news-announcement/`, `/press-releases/`, `/events/`, `/gallery/`,
  `/csr-initiatives/`, `/tenders/`, `/careers/`, `/whistleblowing/`,
  `/contact-us/`, `/iium-holdings-group-of-companies/`, `/board-of-directors/`,
  `/management-team/`, `/board-charter/`, `/code-of-business-conduct/`,
  `/rise2030-strategy-blueprint/`, `/corporate-profile/`

- Removing the old hand-built Elementor header section from each of those 16
  Pages. Without this the page shows two headers.
- Local testing on the `github-test` site before anything touches v2.

### Out

- The 9 business Pages under `/business/`. The business variant is built and unit
  tested on one local page in this release, then rolled out to the 9 Pages in
  1.7.0. Rolling out needs a per-subsidiary icon set, which is its own task.
- Background images in the hero. Both variants are flat petrol with the existing
  geometric pattern overlay.
- Breadcrumbs. The eyebrow carries section context, same as the shipped heroes.
- A hero title override field. The hero always prints the page title. Add later
  only if a real page needs it.
- Any change to the page body, the Elementor Theme Builder site header, or the
  footer.
- Any change to single posts, archives, search, author or 404. Those already ship.
- MetCPT single templates at `/event/`, `/tender/`, `/career/`. The plugin owns
  those.

## 3. Approach

### 3.1 Opt in per page, no slug list

The hero renders when the Page has a hero variant saved in post meta. There is no
hardcoded list of page slugs in the theme. Adding a page later is an editor
action, not a code release.

### 3.2 Keep the existing gate untouched

`met_hello_child_is_styled_view()` in [inc/setup.php](../inc/setup.php#L38) stays
exactly as it is. CLAUDE.md flags it as the thing most likely to break by
accident, and it also controls the full-width body class, which would fight the
Elementor layout on these pages.

Instead add a second, narrower test:

```php
met_hello_child_page_has_hero()  // is_page() && a variant is saved in meta
```

The stylesheet and the font preconnect hints load when **either** test passes.
The full-width body class stays tied to the original test only.

This is safe because every rule in
[assets/css/theme.css](../assets/css/theme.css) is scoped under `.met-view` and
the tokens on `:root` are inert. Loading the sheet on an Elementor page cannot
restyle Elementor content.

### 3.3 Render before the content, on the Full Width layout

All 16 Pages use the **Elementor Full Width** page layout. Confirmed with the
project owner on 2026-08-03. That layout keeps the Elementor Theme Builder site
header and footer and drops the theme's centred container, so content is already
edge to edge. One hook does the job:

```php
elementor/page_templates/header-footer/before_content
```

The hero prints there, above the Elementor content and below the site nav. No
container wraps it, so it is full bleed with no CSS tricks. No child `page.php`
and no `100vw` self-bleed rule are needed.

A one-line safety net also binds
`elementor/page_templates/canvas/before_content`, in case a page is ever switched
to Canvas. A static "already rendered" flag stops a double print. Pages set to the
Default or Theme layout render no hero. That is acceptable: the meta box help
text states the requirement, and step 8 checks the layout on each of the 16.

**Page layout, which to use.** Elementor offers four. For this site:

| Layout | Site nav and footer | Theme container | Verdict |
|---|---|---|---|
| **Elementor Full Width** | Yes | No | **Use this.** What you already do. Sections go edge to edge, the nav and footer stay, and the hero hook fires. |
| Elementor Canvas | No | No | Only for standalone pages with no nav, such as a landing page or a holding page. Lightest of the four, but losing the nav costs more than it saves. |
| Theme | Yes | Yes | Wraps content in a max-width box and prints the page title again. Fights full-bleed sections. Avoid. |
| Default | Depends | Depends | Inherits whatever is set in Elementor, Settings, Default Page Layout. It is a pointer, not a layout, so the same page can change behaviour when a global setting changes. Avoid for anything that matters. |

Your habit is the right one. Keep every page on Full Width. Nothing in this PRD
asks you to change a page layout.

### 3.4 Data model

All keys are protected meta, prefixed `_met_hero_`.

| Key | Type | Used by | Notes |
|---|---|---|---|
| `_met_hero_variant` | string | both | `''`, `standard`, or `business`. Empty means no hero. This is the switch. |
| `_met_hero_eyebrow` | string | both | Standard: the small gold label. Business: the tag pill text. Falls back to the parent page title, then to nothing. |
| `_met_hero_subtitle` | string | both | One or two lines under the title. Optional. |
| `_met_hero_cta1_label` | string | business | |
| `_met_hero_cta1_url` | string | business | |
| `_met_hero_cta2_label` | string | business | |
| `_met_hero_cta2_url` | string | business | |

The title is always `get_the_title()`. Nothing is stored for it.

Sanitising: `sanitize_text_field` on text, `esc_url_raw` on URLs, a whitelist check
on the variant. Saving is nonce checked and gated on `edit_page`. Output is
escaped at print time.

### 3.5 Markup and CSS

New partial `template-parts/page-hero.php`. It prints a `<header>` carrying
`met-view met-page` so the existing scoped rules apply, then the variant markup.

Standard reuses the shipped `.met-hero` classes unchanged. That is the whole point
of the feature, and it means the standard variant costs almost no new CSS.

Business adds `.met-page-hero--business` with `__tag`, `__mark`, `__name`,
`__line` and `__actions`, ported from
`CLAUDE DESIGN/CLAUDE/iium-higher-education.html` lines 120 to 172. It reuses the
existing `--petrol`, `--gold-soft`, `--pattern-light` and spacing tokens. No new
tokens.

New code goes in `inc/page-hero.php`, loaded from `functions.php` alongside the
other `inc/` files.

### 3.6 Performance

Performance is a stated priority, so it gets a budget, not a hope.

**The feature should make these pages faster, not slower.** Each of the 16 Pages
currently builds its header from Elementor widgets: extra DOM, extra Elementor
CSS, and on some pages an image. Step 8 deletes that section and replaces it with
about 15 lines of static HTML. The expected net effect is fewer DOM nodes and
fewer requests.

**Budget.**

| Metric | Rule |
|---|---|
| New JavaScript | 0 bytes. The hero is HTML and CSS only, no script, no interaction. |
| New CSS | The business variant adds under 3KB uncompressed to `theme.css`. The standard variant adds close to nothing, it reuses shipped rules. |
| New HTTP requests on the 16 Pages | At most 2, `theme.css` and the Google Fonts sheet, and only where they are not already loading. |
| LCP | Under 2.5s on staging, and not worse than the current page. |
| CLS | Under 0.1. |
| DOM nodes on the 16 Pages | Lower after step 8 than before it. |

**Where the cost is, and the calls made.**

- *One stylesheet, not a split one.* `theme.css` is 18.5KB uncompressed, roughly
  4KB gzipped, and it now loads on the 16 Pages as well. Splitting out a
  hero-only sheet would save a little on first paint but would break cache reuse:
  a reader who lands on a corporate page and then opens a news post would
  download two files instead of reusing one. One file wins on any multi-page
  visit, which is the visit we care about.
- *One font URL, not a narrower one.* The hero uses two or three Geist weights,
  so a trimmed Google Fonts URL would be smaller in isolation. It would also be a
  different URL from the one the post pages request, so the browser would fetch
  and cache both. Keep the single shared URL in
  [`met_hello_child_fonts_url()`](../inc/assets.php#L24). Same reasoning as above.
- *No image in the hero.* Both variants are flat petrol. The pattern overlay is an
  inline SVG data URI already in the CSS, so it costs no request. This is the main
  reason the hero cannot hurt LCP: the largest element is text, and text paints as
  soon as the CSS lands.
- *Font swap is the only shift risk.* `display=swap` is already set. The band
  height is driven by padding and line count, so a swap moves text within a fixed
  band rather than resizing the page. Step 6 measures CLS to confirm.
- *Server cost is one meta read.* `met_hello_child_page_has_hero()` calls
  `get_post_meta()` on the queried page. WordPress primes the meta cache for the
  main query, so this is a cache hit, not a query. The remaining fields read from
  the same primed cache.
- *Admin code stays out of the front end.* The meta box registration and save
  handler are wrapped so they never load on a front-end request.

**Measurement.** Step 6 records Lighthouse mobile LCP, CLS and DOM node count for
three representative Pages before and after: `/board-charter/` (text only),
`/gallery/` (image heavy), `/events/` (MetCPT shortcode listing). Numbers go in
the PROJECT_LOG entry in step 9. If any page regresses on LCP, the release does
not ship until it is understood.

## 4. Steps

Each step is checkable on the local `github-test` site.

1. Add `inc/page-hero.php` with `met_hello_child_page_has_hero()` and the meta key
   constants. Wire it into `functions.php`.
   *Check: the function returns false everywhere, nothing on the site changes.*
2. Add the "Page Hero" meta box: fields, nonce, save handler, sanitising. Business
   CTA fields show only when the variant is `business`.
   *Check: set values on a scratch Page, reload the editor, values persist. A
   non-editor user cannot save.*
3. Extend the stylesheet and preconnect gates in [inc/assets.php](../inc/assets.php)
   to fire on `met_hello_child_page_has_hero()` too. Leave the body class alone.
   *Check: `theme.css` loads on the scratch Page and on no other Page. The
   full-width body class is absent.*
4. Add `template-parts/page-hero.php` with the standard variant, the Full Width
   hook, the Canvas safety net and the double-render guard.
   *Check: the hero prints once on a Full Width scratch Page and once on a Canvas
   one, is full bleed with no added CSS, and prints nothing on a Default or Theme
   layout page.*
5. Add the business variant markup and CSS.
   *Check: a scratch Page set to `business` matches the reference HTML side by
   side.*
6. Responsive, accessibility and performance pass at 320, 768, 1024 and 1920px.
   *Check: no horizontal scrollbar, one `h1` per page, gold on petrol passes
   contrast, buttons reachable and visible on keyboard focus.*
   *Check: Lighthouse mobile on `/board-charter/`, `/gallery/` and `/events/`,
   before and after, meets the section 3.6 budget. Record the numbers.*
7. Run `composer install` then `phpcs`.
   *Check: clean, no new findings.*
8. Apply the hero to the 16 Pages on local. On each one, confirm the page layout
   is Elementor Full Width, set the variant and eyebrow, then delete the old
   Elementor header section.
   *Check: every one of the 16 shows exactly one header, and it matches the
   archive header.*
9. Version bump in `style.css` and `functions.php`, `readme.txt` changelog entry,
   DOCS update: STATE open items, a DECISIONS entry for the split gate, a
   PROJECT_LOG entry.
10. Tag `v1.6.0`, let the release pipeline build, update the theme on v2 staging,
    then repeat step 8 on the live staging Pages.

## 5. Risks

| Risk | What to do |
|---|---|
| Deleting the old Elementor header section on 16 live Pages is manual and not undoable in bulk | Do all 16 on local first. On staging, export each page's Elementor template before editing. Do them one at a time, checking each. |
| Someone switches a page to the Default or Theme layout later and the hero silently disappears | The meta box states the requirement next to the variant field. Step 8 checks the layout on all 16. A missing hero is a visible, recoverable fault, not a broken page. |
| The hook injection double-renders | The static guard covers it, tested in step 4. |
| Widening the asset gate slows the 16 Pages | Budget and reasoning in section 3.6. Measured before and after in step 6 on three representative pages. The release stops if LCP regresses. |
| An Elementor global style overrides a hero rule | Every hero rule is scoped under `.met-view`, which Elementor never emits. Specificity is checked visually in step 5. |
| MetCPT listing shortcodes on `/events/`, `/tenders/`, `/careers/` clash with hero styles | MetCPT templates carry no hero or `.met-view` markup, checked 2026-08-03. Step 8 re-checks those three pages by eye. |

## 6. Done when

1. All 16 Pages on v2 staging show one header, built by the theme, visually
   matching the shipped archive header.
2. No Page outside those 16 changes in any way.
3. Header copy on any of the 16 can be changed from the Page editor with no code
   release.
4. The business variant renders correctly on a local test Page, ready for the
   1.7.0 rollout.
5. The section 3.6 performance budget is met on all three measured Pages, with
   the before and after numbers recorded in PROJECT_LOG.
6. `phpcs` is clean and 1.6.0 is tagged and installed on staging.
