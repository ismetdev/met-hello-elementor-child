# PRD: homepage to Elementor, footer to Elementor, sector rename

Owner feedback from the Group MD/CEO presentation on 2026-08-11. This plan covers
the homepage only, plus two changes that reach wider: the footer, and the sector
rename from Infrastructure to Facilities.

Status: approved options, not yet built. Written 2026-08-11.

## Owner decisions already taken

Do not reopen these. They were chosen from options presented on 2026-08-11.

| Ref | Decision | Choice |
|---|---|---|
| 1 | How approved sections survive the move | **1A** shortcode bridge |
| 2 | Header and footer | **2B** footer to Elementor, header stays theme code |
| 3 | Hero headline size | **3A** number field per slide |
| 4 | Tender and career listings | **4A** new theme shortcodes, MetCPT untouched |
| 5 | Portfolio source and order | **5A** `/business/` Pages, ordered by shortcode attribute |
| 6 | Infrastructure to Facilities | **6C** rename the label **and** the internal slug |
| 7 | Announcement and newsroom listings | **7B** existing partial markup, exposed as shortcodes |

Copy for the two new sections is owner-approved and is in section 5 below. The
owner will adjust wording in Elementor if the MD/CEO asks, so it does not need to
be perfect in code.

## The governing constraint

**The existing homepage code is not touched.** `inc/homepage.php`,
`page-templates/template-homepage.php` and all nine partials in
`template-parts/home/` stay byte-identical. They stop being called the moment the
Page template dropdown changes, because `met_hello_child_is_home_view()` tests
`is_page_template()`. That is the rollback: re-select the Homepage template and
the old page returns exactly as it was, `home.css` and `home.js` included.

The new work lives in new files. Where card markup has to exist in two places,
that duplication is accepted as the price of a guaranteed rollback.

## Confirmed facts about the current build

Verified by reading the code and querying the local database on 2026-08-11.

- The homepage is Page 156, `show_on_front=page`, template
  `page-templates/template-homepage.php`.
- `met_hello_child_is_home_view()` is `is_page_template( MET_HELLO_CHILD_HOME_TEMPLATE )`,
  so `home.css` and `home.js` self-disable when the template changes.
- Chrome is one Customizer toggle, `met_hello_child_chrome_enabled`, currently on.
- UAE registers the `elementor-hf` post type. `get_hfe_header_id()` and
  `get_hfe_footer_id()` both return false, so no Elementor header or footer is
  assigned yet.
- `met_hello_child_get_companies()` reads published child Pages of `/business/`,
  ordered `menu_order ASC` then `title ASC`, sector from `_met_sector` meta.
  **All nine pages have `menu_order = 0`**, which is why the order is alphabetical.
- Content available: 3 hero slides, 6 announcement posts, 10 published
  `metcpt_tender`, plus `metcpt_career`.
- MetCPT already has `[tenders_preview]` and `[careers_preview]`, rendering
  `tdp-*` markup that does not match the homepage design.

## Design source

Every Elementor-built section follows
`C:\Users\IIUM Holdings\Downloads\iiumh-homepage-final.html` for layout, order,
spacing and hierarchy, with `theme.json` values baked into each widget as hex,
per [D41](../DOCS/DECISIONS.md#d41). Do not write `var(--wp--preset--*)` into
widget settings.

Token mapping, same as the rest of the site:

| Design | Use |
|---|---|
| Display headings | Geist 700/800 |
| Numerals only (stat figures, the 25 emblem, R/I/S/E letters) | Instrument Serif |
| `--gold` fills | `#B98A2E` accent |
| gold on small text over light | `#85621E` accent-text |
| `--petrol` / `--petrol-900` | `#0E3B40` / `#082226` |
| `--sand` bands | `#F7F3EC` surface |
| white ground | `#FFFFFF` surface-raised |
| `--edu` / `--infra` / `--health` | `#4A7BA8` / `#B98A2E` / `#B5566F` |
| `--maxw 1200px` | boxed container 1200px |

## Section ownership after the change

| # | Section | Furniture | Content |
|---|---|---|---|
| 1 | Hero slider | none | `[met_home_hero]` |
| 2 | Announcements | Elementor | `[met_home_announcements]` |
| 3 | Tenders | Elementor | `[met_tenders]` |
| 4 | Careers | Elementor | `[met_careers]` |
| 5 | Who we are | Elementor | Elementor |
| 6 | Stats | Elementor | Elementor |
| 7 | Our portfolio | Elementor | `[met_companies]` |
| 8 | Looking ahead (RISE2030) | Elementor | Elementor |
| 9 | Newsroom | Elementor | `[met_home_newsroom]` |
| 10 | CTA | Elementor | Elementor |

"Furniture" means eyebrow, heading, description and button. Six shortcodes total.

---

## Build order

Each step ends with a headless screenshot and a look at it, per
[D43](../DOCS/DECISIONS.md#d43). Do not batch steps without looking.

### Step 0: branch and baseline

1. `git pull --ff-only origin main`, branch `feat/home-elementor`.
2. Capture the current homepage at 390, 768, 1366 and 1920. These are the
   reference images. Every rebuilt section is compared against them.

### Step 1: the sector rename, Infrastructure to Facilities (6C)

Do this first. It touches the widest surface, and doing it before the homepage is
rebuilt means the homepage is built once, with the right label.

**Code, in order:**

1. `inc/sectors.php`: `met_hello_child_sectors()` returns
   `array( 'education', 'facilities', 'healthcare' )`. Label map
   `'facilities' => __( 'Facilities', 'met-hello-child' )`.
2. `inc/sectors.php`: the eyebrow fallback parse maps "Facilities Division" and
   keeps "Infrastructure Division" as a legacy alias, so an unmigrated page still
   resolves.
3. `theme.json`: rename the preset slug `sector-infrastructure` to
   `sector-facilities`, same hex `#B98A2E`. Keep the name field as "Facilities".
4. CSS, all occurrences of the old class and token:
   `assets/css/home.css` lines around 237, 790, 862, 930;
   `assets/css/chrome.css` around 341; `assets/css/tokens.css` line 34
   (`--infra` alias points at the new token, alias name may stay).
5. `template-parts/home/actions.php` line 60 and
   `template-parts/site-footer.php` line 161 (`/business/#infrastructure`
   becomes `#facilities`). These two are in files the homepage rule protects, but
   `actions.php` is dead code after this project and `site-footer.php` is being
   replaced in step 2. Change both anyway so a rollback is not wrong.

**Data migration**, as a one-time `manage_options` action added to
`inc/migration-tools.php`, following the `_met_sector` backfill already there:

6. Rewrite `_met_sector` from `infrastructure` to `facilities` on every Page that
   has it. Three pages today: Daya Bersih 182, IIUM Advanced Technologies 183,
   IIUM Properties 184. Report the count changed.

**Content strings**, which no function controls. Edit in Elementor:

7. Hero eyebrow "Infrastructure Division · IIUM Holdings Group" on pages 182, 183
   and 184, to "Facilities Division · IIUM Holdings Group".
8. The "Infrastructure" division band on the `/business/` landing page 172,
   including its heading and any body copy naming the division.
9. Main menu: the non-linking "Infrastructure" header item.
10. Footer "Our Businesses" menu: the "Infrastructure" item.

**Verify:** the nine `/business/` pages still resolve a sector, the homepage
filter shows "Facilities" with the gold dot, and a grep for `infrastructure`
across `--include=*.php --include=*.css --include=*.json` returns only the
legacy alias comment.

**Rollback:** the slug change is one commit. The migration action is reversible by
running it in the opposite direction, so write it to accept a direction argument.

### Step 2: footer to Elementor, header stays (2B)

The chrome toggle is all or nothing today. Split it.

1. `inc/chrome.php`: add a second theme_mod, `met_hello_child_footer_enabled`,
   default true, sanitised the same way as the existing toggle. `footer.php`
   falls through to the parent when it is off. The header toggle keeps its
   current name and behaviour, so nothing about the header changes.
2. Customizer: both checkboxes live in the existing "Site Header & Footer"
   section, with descriptions saying what each one does and that turning the
   footer off hands it to Elementor.
3. `met_hello_child_disable_hfe_when_chrome_on()` currently forces all four HFE
   filters off. Scope it: force the header filters off only while the theme
   header is on, and leave the footer filters alone so UAE can render a footer.
4. Build the footer in **Templates, Theme Builder, Footer** in UAE. Follow the
   design file's footer, but on a **light ground** so the logo reads. Use
   `#F7F3EC` surface with `#0F1419` text, or white with a hairline top border.
   The MD/CEO's complaint is only that the logo is lost on dark.
5. Set the display condition to Entire Site.
6. Turn `met_hello_child_footer_enabled` off.

**Verify:** header unchanged on every page type, exactly one footer, the logo is
clearly visible, footer menus still render, and the three footer menu locations
either move into the Elementor footer or are rebuilt there. Check a Post, an
Elementor Page, a block Page and the homepage.

**Rollback:** tick the footer checkbox back on. The theme footer returns.

### Step 3: hero headline size control (3A)

1. `inc/hero-slides.php`: add `_met_slide_headline_size` to
   `met_hello_child_hero_slide_fields()`, with an absint sanitiser that clamps to
   28 to 72 and returns empty for 0 or blank.
2. Meta box: a number input, min 28, max 72, step 1, with help text saying to
   leave it empty for the default size and to lower it for long titles.
3. `met_hello_child_get_hero_slides()` returns the value in the slide array.
4. The hero shortcode in step 4 emits `style="--met-slide-size:NNpx"` on the
   slide element when a value is set, and nothing when it is not.
5. `home.css` gets one rule: the headline reads
   `clamp(28px, calc(var(--met-slide-size, 44px) * 0.7 + 1.2vw), var(--met-slide-size, 68px))`,
   or an equivalent that keeps a large desktop number safe on a phone. The
   existing default size must be unchanged when the field is empty.

**Verify:** the three existing slides look identical with the field empty. Set
one slide to 32 and one to 68, confirm both scale sensibly at 390, 768 and 1366.

### Step 4: the shortcode bridge (1A, 7B, 4A, 5A)

New file `inc/home-shortcodes.php`, required from `functions.php` after
`inc/homepage.php`. It registers six shortcodes and nothing else.

**Asset loading.** Reuse the pattern in `inc/listing.php`: detect the shortcodes
in `post_content` and in `_elementor_data`, and when present enqueue `home.css`
with dependencies `array( 'hello-elementor', 'hello-elementor-theme-style' )`.
That dependency array is not optional; see the cascade note in
[STATE.md](../DOCS/STATE.md). `home.js` is enqueued the same way, because the
carousel, the reveal observer and the company filter all live in it.

**The six shortcodes.** Each one renders only the content, never the eyebrow,
heading, description or button, because Elementor now owns those.

| Shortcode | Renders | Data helper | Attributes |
|---|---|---|---|
| `[met_home_hero]` | the slider | `met_hello_child_get_hero_slides()` | none |
| `[met_home_announcements]` | poster cards | `met_hello_child_get_announcements()` | `count`, default 4 |
| `[met_home_newsroom]` | feature plus list | `met_hello_child_get_newsroom_posts()` | `count`, default 5 |
| `[met_companies]` | filter bar plus cards | `met_hello_child_get_companies()` | `order`, `exclude`, `filters` |
| `[met_tenders]` | tender rows | new query on `metcpt_tender` | `count`, default 4 |
| `[met_careers]` | career rows | new query on `metcpt_career` | `count`, default 4 |

**`[met_companies]` attributes**, which carry decision 5A:

- `order`: a comma-separated list of page slugs. Listed slugs come first, in that
  exact order. Anything not listed follows in the existing order, so a new
  company page never vanishes because someone forgot to add it.
- `exclude`: comma-separated slugs to drop.
- `filters`: `yes` or `no`, default `yes`.

The homepage will use:

```
[met_companies
  order="iium-higher-education-sdn-bhd,iium-schools-sdn-bhd,iium-educare-sdn-bhd,iium-consultancy-and-innovation-sdn-bhd,daya-bersih-sdn-bhd,iium-properties-sdn-bhd,iium-medical-specialist-centre-sdn-bhd,ikop-pharma-sdn-bhd"
  exclude="iium-advanced-technologies-sdn-bhd"]
```

That produces the MD/CEO's sequence: four Education, then Daya Bersih and IIUM
Properties under Facilities, then the two Healthcare companies, with IIUM
Advanced Technologies removed. Slugs, never IDs, because IDs differ between local
and staging (D44).

**`[met_tenders]` and `[met_careers]`.** New queries, not MetCPT's shortcodes, so
MetCPT stays untouched and keeps its own release cycle. Read the same post types
and meta that MetCPT writes:

- Tenders: `metcpt_tender`, meta `tender_close_date` and `tender_ref`, upcoming
  first, closed excluded.
- Careers: `metcpt_career`, meta `career_close_date`, open roles only.

Render them with theme classes and `theme.json` colours so they sit beside the
other homepage sections. Both need a real empty state, because a day with no open
tender is normal. Reuse the row shape from the design file's action cards.

**Escaping and safety.** Everything is escaped at output. The card markup is
copied from the existing partials, which are already correct, so copy them
faithfully rather than rewriting. `phpcs` must pass.

### Step 5: build the ten sections in Elementor

Switch Page 156's template to **Elementor Full Width** (the "Theme" option in the
dropdown), which stops the old template rendering. Then build, following the
design file section by section, in this order. Screenshot after each.

1. **Hero.** One Shortcode widget holding `[met_home_hero]`, full width, no
   container padding. Nothing else. The slider design does not change.
2. **Announcements.** Two-column head row: section head on the left, ghost
   "View all" button on the right, collapsing to stacked on mobile. Below it,
   `[met_home_announcements]`. Copy: eyebrow "Announcements", h2 "Latest from the
   group", description "Festive greetings, milestones, and group notices."
3. **Tenders.** New section. Copy in section 5 below. Elementor furniture plus
   `[met_tenders]`.
4. **Careers.** New section, same shape as tenders.
5. **Who we are.** Two-column grid: text left, image with the 25 emblem right.
   The emblem number is Instrument Serif. Copy from the design file.
6. **Stats.** Four figures in a row, two by two on mobile. Instrument Serif
   numerals. 2001 Incorporated, 9 Companies, 3 Industries, 1,000+ Employees.
   These are Customizer values today; in Elementor they become plain text.
7. **Our portfolio.** Section head, then `[met_companies]` with the attributes
   above. The filter bar and cards come from the shortcode, so the gallery the
   MD/CEO likes is unchanged apart from order and the Facilities label.
8. **Looking ahead.** Petrol band. Left: eyebrow, h2, lead, the RM419 million
   target. Right: the four R/I/S/E thrusts, letters in Instrument Serif.
9. **Newsroom.** Head row identical in design to Announcements, per the owner's
   instruction, then `[met_home_newsroom]`.
10. **CTA.** Sand band, heading and description left, dark button right,
    stacking on mobile.

**Elementor gotchas to check on every section**, all from D41, each of which has
already cost a rebuild:

- Grid containers default to two rows. Set `grid_rows_grid` to custom `auto` on
  every breakpoint.
- Background overlay opacity defaults to 0.5. Set it explicitly.
- Images in grid cells need an explicit width or the column blows out.
- Font Awesome here is version 5, not 6.

### Step 6: verification

Per section, not at the end.

- **Responsive**, the owner's stated second priority: 390, 768, 1366 and 1920.
  No horizontal scroll at any width. Check `scrollWidth` against `clientWidth`,
  do not eyeball it. The two new quick-action sections must not squeeze at 768.
- **One `h1`**, from the hero. Everything else is `h2` or lower, in order.
- **Images** load. Watch for the lazy-load trap in D43: a tall full-page capture
  photographs below-fold images blank. Scroll to them before calling one broken.
- **Keyboard**: the carousel, the company filter and every link reachable and
  escapable, focus visible.
- **Empty states**: no hero slides, no announcements, no open tenders, no open
  careers. All four must look deliberate.
- **`phpcs` clean.** Run `vendor/bin/phpcs`.
- **Novamira `check-design`** on the rendered homepage. Fix every fail. The
  off-palette warning for the three sector colours is expected.
- **Compare against the step 0 baseline** for the four sections that must not
  change: hero, announcement cards, portfolio gallery, newsroom list.

### Step 7: ship

1. Version bump in `style.css` and `functions.php`, changelog in `readme.txt`.
   This is a feature release: **1.12.0**.
2. Update `DOCS/STATE.md`, `DOCS/PROJECT_LOG.md`, and add decisions to
   `DOCS/DECISIONS.md` (see below).
3. Commit, push `main`, then tag.
4. Add the homepage to `DOCS/DEPLOY-TO-STAGING.md` as its own case: it is now an
   Elementor page and moves by template export, unlike before.

---

## Copy for the two new sections

Owner-approved 2026-08-11. Editable in Elementor afterwards.

**Tenders.** Eyebrow: Procurement. Heading: Open tenders, open process.
Description: Current tender opportunities across the Group, with reference
numbers and closing dates. Button: View all tenders.

**Careers.** Eyebrow: Careers. Heading: Build your career with us. Description:
Open roles across our education, facilities and healthcare companies. Button:
View all openings.

Note the careers description says "facilities", not "infrastructure", matching
the rename.

## New decisions to record in DECISIONS.md

- **D47: the homepage is an Elementor page, built on a shortcode bridge.** D37
  made it a Page Template. The MD/CEO needs the editorial furniture editable
  without a developer. The four approved designs stay in theme code and reach
  Elementor through shortcodes, so nothing approved is rebuilt in a second tool.
  The old template and partials stay on disk, unused, as the rollback.
- **D48: the header stays theme code, the footer moves to Elementor.** The
  complaint was the dark footer. The header carries the mega menu, the drawer and
  the focus trap, and the MD/CEO is happy with it, so it is the wrong thing to
  rebuild. The chrome toggle splits in two.
- **D49: Infrastructure is renamed to Facilities, slug included.** Owner
  instruction, chosen over a label-only change so the code and the interface
  agree. The slug migration is a one-time reversible action in
  `inc/migration-tools.php`.

## Risks

| Risk | Handling |
|---|---|
| The Elementor footer looks different from the theme footer | Build it from the design file, compare against the baseline capture, keep the toggle for instant revert |
| `home.css` not loading on the Elementor homepage | The shortcode detection must read `_elementor_data`, not only `post_content`. This is the single most likely defect |
| Stylesheet order regression | Always enqueue with the parent dependency array. This has cost several rounds before |
| Sector rename breaks the nine business pages | Migration action reports its count; verify all nine resolve a sector before moving on |
| The MD/CEO sees a changed hero, gallery or news list | Those four render from unchanged theme code. Compare against the step 0 baseline before showing him |
| Staging move | Homepage becomes a Group B Elementor page. Add it to the deploy guide, and remember the sector migration must run on staging too |

## Out of scope

The nine subsidiary pages, the `/business/` landing page, Board of Directors and
Management Team. The only thing touching those here is the Facilities rename in
step 1. MetCPT is a separate repository and is not modified.
