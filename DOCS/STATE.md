# STATE

Where the project stands today. Update when the shipped version, the open work, or
the environment changes.

Last updated: 2026-08-11

## At a glance

| | |
|---|---|
| Shipped version | **1.11.1**, released and **live on staging**. Deploy completed 2026-08-10. Includes the never-tagged 1.8.0, 1.9.0 and 1.10.0 work, folded into 1.11.0 |
| Local version | **1.12.0, built and verified on local, not released.** Rebuilds the homepage in Elementor per Group MD/CEO feedback after the 2026-08-11 presentation. See "v1.12.0" below |
| Staging status | **v2.iiumholdings.com.my is live on 1.11.1** with the full new design: custom chrome on, homepage as the front page, and all content pages moved. Presented to the Group MD/CEO on 2026-08-11 |
| Repository | https://github.com/ismetdev/met-hello-elementor-child (public) |
| Branch | `main` |
| Tags | `v1.4.0`, `v1.4.1`, `v1.4.2`, `v1.5.0`, `v1.6.0`, `v1.7.0`, `v1.7.1`, `v1.7.2`, `v1.11.0`, `v1.11.1` (1.8.0 to 1.10.0 folded into 1.11.0, never tagged separately) |
| Parent theme | `hello-elementor` |
| Requires | WordPress 6.0+ (tested to 6.5), PHP 7.4+ |
| Text domain | `met-hello-child` (`/languages`) |
| License | GPL-2.0-or-later |

## Layout

See the structure tree in [README.md](../README.md#structure). The short version:
WordPress requires the template hierarchy files (`single.php`, `archive.php`,
`author.php`, `search.php`, `404.php`), `style.css`, `functions.php` and
`screenshot.png` at the theme root, so they stay there. `error-403.php` also
stays because `.htaccess` points at its path. Everything else lives in `inc/`,
`assets/`, `template-parts/` or `dropins/`.

## What the theme does

Gives native WordPress blog Posts and their surrounding pages a custom editorial
design (petrol `#0E3B40`, gold `#B98A2E`). It does not touch the parent theme,
Elementor pages, or the MetCPT custom post types.

| Surface | File | Status |
|---|---|---|
| Single blog post | [single.php](../single.php) | Shipped 1.1.0, extended 1.4.2 |
| Category, tag, date archives | [archive.php](../archive.php) | Shipped 1.2.0 |
| Search results | [search.php](../search.php) | Shipped 1.3.0 |
| Author profiles | [author.php](../author.php) | Shipped 1.3.0 |
| 404 | [404.php](../404.php) | Shipped 1.3.0 |
| Maintenance (503) | [template-parts/maintenance-page.php](../template-parts/maintenance-page.php) | Shipped 1.3.0, needs a `wp-config.php` toggle |
| 403 Forbidden | [error-403.php](../error-403.php) | Shipped 1.3.0, needs `.htaccess` wiring |
| Maintenance drop-in | [dropins/maintenance.php](../dropins/maintenance.php) | Added 1.5.0, copy to `wp-content/` by hand |
| Shared card partial | [template-parts/met-card.php](../template-parts/met-card.php) | Shipped 1.3.0 |
| Page Hero (opt-in Elementor Page header) | [inc/page-hero.php](../inc/page-hero.php), [template-parts/page-hero.php](../template-parts/page-hero.php) | Shipped 1.6.0. Standard variant applied to all 16 target Pages on local `v2` and live staging. Business variant rolled out to the 9 `/business/` Pages, confirmed on both |
| Scroll to Top (sitewide button) | [inc/scroll-top.php](../inc/scroll-top.php), [assets/css/scroll-top.css](../assets/css/scroll-top.css), [assets/js/scroll-top.js](../assets/js/scroll-top.js) | Shipped 1.7.0, fixed 1.7.1-1.7.2, confirmed working on live staging. Customizer: Appearance > Customize > Scroll to Top |
| Design system CSS | [assets/css/theme.css](../assets/css/theme.css) | Components under `.met-view`, gated to styled views. Tokens now live in theme.json (below) |
| Canonical design tokens | [theme.json](../theme.json) | Added 1.9.0, single source of truth as of that version. Reconciled from 40 design files, see [PLAN/PRD-design-system.md](../PLAN/PRD-design-system.md) |
| Sitewide token alias layer | [assets/css/tokens.css](../assets/css/tokens.css), [assets/css/elementor-base.css](../assets/css/elementor-base.css) | tokens.css added 1.8.0, rewritten 1.9.0 as an alias over theme.json. Loads on every page, including every Elementor Page. See [DECISIONS D27](DECISIONS.md#d27) |
| Self-hosted fonts | [assets/fonts/](../assets/fonts/) | Added 1.9.0. Geist, Instrument Serif. Retires the Google Fonts CDN on the main site; the standalone death-path pages (maintenance, 403) still use it, see D7 |
| Page template | [page.php](../page.php) | Added 1.9.0. Hello Elementor ships none; fixes a duplicate `<h1>`, an open comment form, and a width bug on any Page once it stops being Elementor. See [DECISIONS D29](DECISIONS.md#d29) |
| Block content patterns | [assets/css/patterns.css](../assets/css/patterns.css) | Added 1.9.0. Component styles for block-authored Page bodies, scoped under `.met-page`, reading theme.json properties directly. Loads only on Pages that render through page.php |
| Post listing shortcode | [inc/listing.php](../inc/listing.php), [template-parts/listing-card.php](../template-parts/listing-card.php), [assets/css/listing.css](../assets/css/listing.css) | Added 1.11.0. `[met_posts]` renders a token-styled list of Posts by category slug, for use in an Elementor Shortcode widget. Slug, not term ID, so it survives the move to staging. See [DECISIONS D44](DECISIONS.md#d44) |
| External photo albums | [inc/albums.php](../inc/albums.php) | Added 1.11.0. `_met_album_url` post meta plus a "View the full album" button in `single.php`. Photos stay on Facebook; WordPress holds the pointer. See [DECISIONS D46](DECISIONS.md#d46) |
| Migration tool (**temporary**) | [inc/migration-tools.php](../inc/migration-tools.php) | Added 1.9.0. `manage_options`-gated admin-post actions to flip the two meta keys a migration needs. **Delete this and its `require_once` when phase 4 ships or WP-CLI access arrives.** See D29 |
| `<main>` landmark on Elementor Pages | [inc/setup.php](../inc/setup.php) | Added 1.8.0. Elementor's own page templates printed none. See D27 |
| Auto-update pipeline | [inc/updater.php](../inc/updater.php), [release.yml](../.github/workflows/release.yml) | Shipped 1.4.0, verified by the 1.4.1 release |

## Scope boundary

This is the thing most likely to break by accident.

`met_hello_child_is_styled_view()` in [inc/setup.php](../inc/setup.php) is the
single gate for the stylesheet, the font preconnect hints, and the full-width
body class. It uses
`is_singular( 'post' )` plus `is_category() || is_tag() || is_date()`, not the
broad `is_single()` or `is_archive()`. That keeps MetCPT Events, Tenders and
Careers, Pages, and the blog home out. See [DECISIONS.md](DECISIONS.md#d3).

Checked from the MetCPT side on 2026-07-29. The plugin owns `/events/`,
`/tenders/`, `/careers/` and the raw CPT archive fallback. The theme owns the news
and blog category, tag and date archives. No overlap.

## Environment

- Local dev site: `v2` (Local by Flywheel; renamed from `github-test` on 2026-08-03).
- Target site: https://v2.iiumholdings.com.my
- Two machines. Every commit so far is authored by "Ismet Home" (home machine).
  This laptop is "Ismet Office". GitHub is the source of truth. Run
  `git pull --ff-only origin main` before editing on either machine. The sibling
  MetTranslate repo hit a tag divergence in July 2026 by skipping this.
- Claude Code transcripts do not sync between machines. The sessions that built
  v1.0.0 to v1.4.2 exist only on the home machine. That is why this file,
  [DECISIONS.md](DECISIONS.md) and [PROJECT_LOG.md](PROJECT_LOG.md) live in the
  repo.

## Related projects

Same site, separate repos, separate release cycles.

- **MetCPT**, `wp-content/plugins/metcpt`. Events, Tenders, Careers CPTs, their
  shortcodes and archive templates. Formerly named "Haraka".
- **MetTranslate**, `wp-content/plugins/mettranslate`. Same release pattern:
  Plugin Update Checker plus a tag-triggered GitHub Action.

## Open items

**The frontend revamp is deployed.** As of 2026-08-10 the new design system,
custom chrome, homepage and all rebuilt content pages are live on staging at
1.11.1, presented to the Group MD/CEO on 2026-08-11. What remains is a short list
of page designs and some content entry (see "Deploy: done" and "Not done" below).
Start there.

**Background on how the design is delivered.** Page bodies are built in Elementor
(free plus Essential Addons and UAE) with `theme.json` token values written into
each widget; listings use the theme's own `[met_posts]` shortcode. The block-
editor migration once planned in
[PLAN/PRD-block-system.md](../PLAN/PRD-block-system.md) is suspended for page
bodies (D35); the design foundation is
[PLAN/PRD-design-system.md](../PLAN/PRD-design-system.md), reviewed in
[PLAN/DESIGN-SYSTEM-GALLERY.html](../PLAN/DESIGN-SYSTEM-GALLERY.html)
(owner-approved 2026-08-07).

### The one rule to read before touching a page

**Page Hero is the site's fixed header. It is never redesigned per page.** A
design file supplies the **body content below it** and nothing else. See
[D30](DECISIONS.md#d30). Getting this wrong is what made the first migrated
page fail owner review.

### Direction changed 2026-08-08: back to Elementor for page bodies

The block-editor migration is **suspended for page bodies**. Pages are built in
Elementor free plus Essential Addons and UAE, with `theme.json` token values
written into each widget at build time. See [D35](DECISIONS.md#d35) for why this
is not a repeat of D27's failure. Phase 4 (chrome into the theme) still stands
and is being brought forward as v1.10.0.

### Done, verified on local `v2`, not released

- **Phase 0, foundation.** `theme.json` as the canonical design system,
  self-hosted fonts, `tokens.css` as an alias layer, `page.php`, contrast sweep.
  See [D28](DECISIONS.md#d28), [D29](DECISIONS.md#d29).
- **Phase 0a, environment parity.** Essential Addons 6.7.2 and Ultimate Addons
  2.9.2 active, matching staging. Local is at `http://v2`.
- **Novamira MCP** installed and connected (local only, never staging). Gives
  `execute-php` and filesystem access, which is what makes programmatic
  Elementor authoring viable. Credential lives in a gitignored `.mcp.json`.
  Required an nginx fix for `HTTP_AUTHORIZATION` in `conf/nginx/site.conf.hbs`.
- **42 Pages exist**, matching the live staging sitemap, with Page Hero meta,
  excerpts and Yoast fields. Hierarchy verified: 9 under `/business/`, 9 under
  `/board-of-directors/`.
- **22 pages designed and built in Elementor**, all pre-flight clean:
  `/whistleblowing/`, `/contact-us/` (WPForms 197), `/sitemap/`,
  `/iium-holdings-group-of-companies/` (rebuilt 2026-08-09, page 152, 103
  widgets), `/iium-holdings-25th-anniversary/` (built 2026-08-09, page 161, 163
  widgets), `/news-announcement/` (page 170), `/media/` (page 171), `/gallery/`
  (page 168), `/press-releases/` (page 169), `/csr-initiatives/` (page 167),
  `/rise2030-strategy-blueprint/` (page 160), all built 2026-08-09;
  `/business/` (the division landing page, page 172, built 2026-08-10); and the
  nine `/business/` subsidiary child pages (178 to 186, built 2026-08-11). The
  last ten are local only, not yet deployed. All built
  from **real Elementor widgets**, never a
  pasted HTML block. See [D41](DECISIONS.md#d41), which also lists the four
  Elementor gotchas that have each cost a rebuild. Images are imported into the
  local media library, never hotlinked from staging ([D42](DECISIONS.md#d42)).
  Post listings on the last two use the `[met_posts]` shortcode, not an addon
  widget, so they survive the move to staging ([D44](DECISIONS.md#d44)).
- **Headless screenshot verification** is now part of the build, not optional.
  See [D43](DECISIONS.md#d43) for how to drive Chrome from Node, and for the two
  traps in reading the captures.
- **Main menu restructured**: 28 placeholder custom links converted to real page
  links. 4 stay custom as non-linking headers (About Us, Education,
  Infrastructure, Healthcare) because no page exists behind them.
- **Font-size token bug fixed** in `tokens.css` and `patterns.css`. See
  [D36](DECISIONS.md#d36). It had silently shrunk every Page Hero title and
  every blog-post `h2`.

### v1.10.0, homepage and site chrome. Built, verified on local, not released

Built 2026-08-08 on branch `feat/home-chrome`, from the approved design at
`C:\Users\IIUM Holdings\Downloads\iiumh-homepage-final.html`. All seven build
steps done; `phpcs` clean; Novamira `check-design` clean (one warn only, the two
sector colours, which are canonical theme.json tokens). Structural verification
done; owner does the visual pass.

- **Homepage** is a Page Template (`page-templates/template-homepage.php`,
  [D37](DECISIONS.md#d37)), assigned to Page 156 "IIUMH Home" and set as the
  front page. Nine partials in `template-parts/home/`, styled by
  `assets/css/home.css` and `assets/js/home.js`, data helpers and Customizer in
  `inc/homepage.php`. Mapped onto `theme.json` tokens, no new fonts, Geist bold
  headings, Instrument Serif for numerals only.
- **Site chrome** (`header.php`, `footer.php`, `template-parts/site-header.php`,
  `site-footer.php`, `nav-drawer.php`, `assets/css/chrome.css`,
  `assets/js/chrome.js`, `inc/chrome.php`, two walker classes) ships behind the
  Customizer toggle `met_hello_child_chrome_enabled`, default off
  ([D38](DECISIONS.md#d38)). **Left ON on local so the owner can review the full
  design; it is a theme_mod, not committed code.** Off falls through to the
  parent theme, confirmed byte-identical revert.
- **Hero slides** are `met_hero_slide`, a non-public CPT
  ([D39](DECISIONS.md#d39)). None exist yet, so the hero shows one static
  fallback slide.
- **Sector** is `_met_sector` Page meta ([D40](DECISIONS.md#d40)); all nine
  `/business/` Pages backfilled.
- **Page Hero** gained an explicit **None / Standard / Business** radio,
  default None. Existing heroes render unchanged.

`inc/migration-tools.php` gained a one-time `_met_sector` backfill action.

**Owner-set priority order for this build.** First, do not break what works
(Page Hero on 42 Pages, scroll-to-top, the menu, the 4 built Elementor pages,
MetCPT shortcodes, Yoast output). Second, and jointly: performance, SEO, beauty
and responsiveness, as acceptance criteria rather than aspirations. Third,
everything else. A section is not done until all four of the second group pass,
checked per section rather than at the end.

Concretely that means: LCP image prioritised and zero CLS, everything else lazy
with explicit dimensions, no CSS background images; one `h1` with correct
heading order and Yoast left untouched in `wp_head()`; WCAG AA contrast with
full keyboard operation and `prefers-reduced-motion` honoured; and verification
at 390 / 768 / 1280 / 1920 with no horizontal scroll.

### The six formerly-empty staging pages, now live on staging

These were empty on staging (hero only). All were built on local and moved up on
2026-08-10; they are now live. Local page IDs kept for reference:

| Page | Local page ID | Content |
|---|---|---|
| `/media/` | 171 | hub, three cards, no shortcode |
| `/news-announcement/` | 170 | `[met_posts featured="yes"]`, all categories |
| `/gallery/` | 168 | `[met_posts category="gallery" layout="album"]`, 2 albums |
| `/press-releases/` | 169 | `[met_posts category="press-releases"]`, 3 posts |
| `/csr-initiatives/` | 167 | `[met_posts category="csr"]`, 3 posts |
| `/sitemap/` | 163 | built earlier |

The `press-releases` and `gallery` categories were recreated on staging with the
same slugs, and the content posts (news, press releases, CSR, gallery albums)
were moved as named posts with their categories, featured images, and for
gallery albums the `_met_album_url` value. Housekeeping done: `/sample-page/` and
`/board-of-directors/naaimah-backup/` cleared.

**The working method, owner-set.** Staging is the reference, not local. A staging
page with good design is finished and is left alone. A page that is empty, or has
bad design, is rebuilt on local and then moved up. Local is a workshop holding
only the pages being built, never a copy of the site.

### Deploy: done 2026-08-10

The first full deploy is complete. Staging went from 1.7.2 to **1.11.1**, the
chrome was turned on, all content posts and Elementor pages were moved, and the
homepage was set as the front page. Every page was verified one by one by the
owner. The site was presented to the Group MD/CEO on 2026-08-11. The full,
executed procedure is [DEPLOY-TO-STAGING.md](DEPLOY-TO-STAGING.md).

A hotfix, **v1.11.1**, was released mid-deploy: the homepage companies grid
hardcoded the local `/business/` page ID (172), which is 33 on staging, so it
resolved empty. It now resolves the parent by slug. See the PROJECT_LOG entry.

**Temporary staging access.** The owner created a throwaway admin
(`admin-claude-temporary`) for the deploy and planned to delete it once done. If
it still exists, it should be removed.

### v1.12.0, homepage rebuilt in Elementor. Built, verified on local, not released

Built 2026-08-11, from Group MD/CEO feedback given after the 2026-08-11
presentation of the (then still Page-Template) homepage. Full detail, the
options put to the owner, and the reasoning behind each is
[PLAN/PRD-homepage-elementor.md](../PLAN/PRD-homepage-elementor.md).

- **Homepage (Page 156) is now an Elementor page**, template "Elementor Full
  Width" (`elementor_header_footer`), not "Theme": that template calls
  `get_header()`/`get_footer()` the same as "Theme" would, but skips
  `page.php`'s Page Hero/fallback-intro branch, which would otherwise print a
  second `<h1>` above the design's own hero. See [D47](DECISIONS.md#d47).
  **The v1.10.0-era Page Template build is untouched** —
  `page-templates/template-homepage.php`, `inc/homepage.php`, and all nine
  partials in `template-parts/home/` stay on disk as the rollback: re-select
  the Homepage template and they render exactly as before.
- **A shortcode bridge**, [inc/home-shortcodes.php](../inc/home-shortcodes.php),
  lets Elementor own the eyebrow/heading/description/button furniture while
  four already-approved designs (hero slider, announcement cards, portfolio
  gallery, newsroom list) keep rendering from the same partials and data
  helpers as before, via `[met_home_hero]`, `[met_home_announcements]`,
  `[met_companies]`, `[met_home_newsroom]`. Two new shortcodes,
  `[met_tenders]` and `[met_careers]`, read MetCPT's `metcpt_tender` and
  `metcpt_career` post types and meta keys but render theme markup, so MetCPT
  itself is never modified. `[met_companies order="..." exclude="..."]`
  gives the homepage its own company sequence, independent of the
  `/business/` page order.
- **`home.css` and `home.js` needed one fix to work off the old template.**
  Every rule in `home.css` and every DOM query in `home.js` is scoped under
  one `.met-home` ancestor, which used to be `<main class="met-home">` in the
  Page Template. `met_hello_child_wrap_home_content()` (in
  `inc/home-shortcodes.php`, on the `the_content` filter) wraps Elementor's
  rendered output in that same element, so both files work completely
  unchanged. Scoped to the front page, only when a home shortcode is present.
- **The footer can now move to Elementor**, a second Customizer toggle,
  `met_hello_child_footer_enabled` (default on, independent of the header
  toggle). Built in Header Footer Elementor (bundled with UAE) on `surface`
  `#F7F3EC`, not the petrol the header still uses, so the logo reads — the
  Group MD/CEO's actual complaint. See [D48](DECISIONS.md#d48). One HFE detail
  worth remembering if this is ever rebuilt: the `ehf_template_type` post meta
  value must be the literal string `type_footer`, not `footer`.
- **Hero slides gained a per-slide headline size field**, `_met_slide_size`,
  28-72px, empty keeps today's size. `template-parts/home/hero.php` emits it
  as an inline `--met-hero-title-max` custom property; the CSS floor is
  `min(2.2rem, the custom max)` rather than a bare `2.2rem`, because `clamp()`
  floor always outranks its ceiling once the floor is larger, and a bare
  `2.2rem` floor would have silently ignored any custom max under 35px.
- **Infrastructure renamed to Facilities, slug included** ([D49](DECISIONS.md#d49)):
  `met_hello_child_sectors()`, the label map, the `theme.json` colour token
  (`sector-infrastructure` to `sector-facilities`), every CSS class name, and
  the saved `_met_sector` meta on Daya Bersih (182), IIUM Advanced
  Technologies (183) and IIUM Properties (184). A one-time, reversible
  migration action is in `inc/migration-tools.php`
  (`admin-post.php?action=met_hello_child_rename_sector`). The eyebrow-parse
  fallback in `inc/sectors.php` keeps `infrastructure` as a permanent legacy
  alias, so any page whose hero eyebrow still reads the old text resolves
  correctly. Content strings with no code path (hero eyebrows on the three
  Facilities subsidiary pages, the `/business/` landing page's division band,
  a hero slide's own body text, three Yoast meta descriptions, the main menu
  item) were found and fixed by a direct database sweep, not by grep alone —
  worth re-sweeping after any future find-and-replace of a division name.
- **Stats are now plain Elementor text**, not the Customizer fields in
  `inc/homepage.php`. Those fields still exist and still work if the
  Page Template is ever restored, but on the Elementor build they are
  dormant; the owner was told this in the planning conversation.
- Verified: `phpcs` clean theme-wide; Novamira `check-design` returns `ok`,
  two expected warnings (the education/healthcare sector colours, both
  canonical `theme.json` tokens, and "elevate" as filler copy, which is the
  owner's own RISE2030 wording); no horizontal scroll and exactly one `h1` at
  390/768/1366/1920; the four unchanged sections screenshot-compared against
  the pre-change baseline.

### Not done, for a future session

- **The v1.12.0 homepage rebuild is local only, not deployed and not
  committed.** See above.
- **`/business/` landing page (page 172) is built on local but not deployed.**
  Designed 2026-08-10 from `business-content.html`. It is image-heavy (9 company
  photos, 9 logos, 9 small featured photos, 1 closing background), so it is a
  Group B page in the deploy guide: move it, then re-check the images on staging.
  All nine `Explore` links are placeholder `#`, to be pointed at the subsidiary
  pages after import. The nine small photos reuse the subsidiary pages' own
  featured images.
- **All nine `/business/` subsidiary child pages are built on local, not
  deployed.** Designed 2026-08-11 from their approved design files: IIUM Higher
  Education (178), IIUM Schools (179), IIUM Educare (180), IIUM Consultancy and
  Innovation (181), Daya Bersih (182), IIUM Advanced Technologies (183), IIUM
  Properties (184), IIUM Medical Specialist Centre (185), IKOP Pharma (186).
  One shared section grammar, divisional accent the only colour that varies.
  All are Group B (image-heavy). **Page Hero is set to None on all nine**, and
  must be set to None on staging too, or the old band returns above the new
  hero. Stock photography is placeholder and can be swapped on staging. See the
  2026-08-11 PROJECT_LOG entry.
- **Remaining page designs**, still on their original staging design, not yet
  redesigned: Board of Directors and Management Team.
  (Homepage, 25th Anniversary, Group of Companies, News, Media, Gallery, Press
  Releases, CSR, RISE2030, the Business landing and all nine subsidiary pages
  are done.)
- **Homepage content, now populated**: 3 hero slides exist (Hero Slides menu),
  and all nine `/business/` pages have a featured image, so the sector-tinted
  placeholder is no longer the common case. Both remain plain content entry
  going forward, not code.
- **Elementor Google Fonts**, still deferred (see below).
- **MetCPT items**, a separate repo (see below).

### Elementor Google Fonts, deferred by the owner 2026-08-08

The Elementor Kit (`post-10.css`) declares `font-family:"Inter"` and
`font-family:"Roboto"`, so Elementor requests both from Google on every page,
including the homepage, which uses neither. This contradicts the self-hosted
font decision (D15, D28) and costs two render-blocking requests sitewide.
MetCPT's `style-events.css` line 1 additionally `@import`s DM Sans, tracked
below with the other MetCPT items.

Left alone because the four built Elementor Pages each reference Inter and
Roboto, so switching them off would change their typography and needs a visual
review first. Owner chose to handle it as its own task.

The fix when it happens: set Elementor, Site Settings, Global Fonts to Geist and
Instrument Serif. Elementor then stops fetching them, because it only requests
fonts it recognises as Google fonts. No theme code needed. The blunt
alternative, the `elementor/frontend/print_google_fonts` filter, removes the
requests but leaves anything relying on those fonts to fall back.

### What this migration taught, worth not relearning

**Structural verification is not visual verification.** curl and grep passed
every round of the first page, including the two rounds that were visibly
wrong. Only a browser caught them. Do not build pages in a batch without a
screenshot between each one.

**A child stylesheet must declare the parent's stylesheets as dependencies.**
`chrome.css` and `home.css` were enqueued with `array()`, so WordPress printed
them *before* the parent's `reset.css`. That reset styles bare elements,
including `[type=button], button { border: 1px solid #c36; display: inline-block }`
and `a { color: #c36 }`. Those selectors tie with a single class on specificity,
so the later file won and the mobile hamburger stayed visible at every width
while buttons wore a pink border. It cost several rounds because the media query
and the widths were all correct; the problem was one line further down the
cascade. Symptoms that look like "my colour or display rule is being ignored"
should send you to the printed stylesheet order first, not to specificity.
`inc/assets.php` had this right for `theme.css` all along:
`array( 'hello-elementor', 'hello-elementor-theme-style' )`.

### Still open from 1.8.0, owner's to do on live staging

Tracked in [PLAN/STAGING-CHECKLIST-1.8.0.md](../PLAN/STAGING-CHECKLIST-1.8.0.md):
image optimisation and CSS delivery, the Board of Directors 154 KiB unused-CSS
outlier, footer social link accessible names, the desktop CLS 0.096
shared-chrome source, and the re-measure against the 2026-08-04 PageSpeed
baseline. Elementor Global Colours and Fonts are now moot for any migrated
page, since those pages no longer run Elementor.

Also outstanding, and only the owner can clear them: commit the 40 design
files into the repo under `design/` (they exist in OneDrive only), the host
opcache fix (128 MB full, 58.91% hit rate), and the `wp-config.php` and PHP
settings in PRD-block-system section 3.5.5.

Closed on 2026-08-03: Page Hero and Scroll to Top, both confirmed on live
staging.

Closed on 2026-08-03 (owner-confirmed, see PROJECT_LOG):
Scroll to Top's phone/tablet visibility fix (1.7.2) confirmed on staging.
Owner decided the Lighthouse performance pass is not a fair test of the
theme, since 90%+ of each page's content is the owner's own Elementor build,
not theme code: abandoned, not tracked further. Owner sees no visible colour
problem with the current accent colour; the computed WCAG contrast fail
stands as a known, unpoliced fact (D26) but is not being changed. Page Hero
(standard variant) applied to all 16 target Pages on live staging, confirmed
working. Page Hero business variant rolled out to the 9 `/business/` Pages,
confirmed on both local and live staging.

Closed on 2026-08-01: stale "Haraka" comments renamed to "MetCPT"; the missing
`dropins/maintenance.php` added.

Closed on 2026-08-03: stay on the Google Fonts CDN, see
[DECISIONS.md](DECISIONS.md) D15. Ship English only with no translation
catalogue, see D24. Ran `composer install` and `phpcs` for the
first time.
Found and fixed two config defects (prefix list, CRLF line endings) and 9 real
findings. `phpcs` is clean. See [PROJECT_LOG.md](PROJECT_LOG.md) for detail.
`composer.lock` is committed so both machines lint against the same standard
versions.

## How to move local work to staging

Code ships through the release pipeline below. Everything else, Elementor pages,
media, menus, Customizer settings and page meta, moves as named items by hand.
The database is never copied. Full procedure with rollback for each part:
[DEPLOY-TO-STAGING.md](DEPLOY-TO-STAGING.md).

## How to cut the next release

1. Bump the version in both [style.css](../style.css) (`Version:` header) and
   `MET_HELLO_CHILD_VERSION` in [functions.php](../functions.php).
2. Add a `= X.Y.Z =` block to the changelog in [readme.txt](../readme.txt#L62).
3. Commit, push `main` first, then `git tag vX.Y.Z && git push origin vX.Y.Z`.
4. `release.yml` builds the zip inside a folder named exactly
   `met-hello-elementor-child`, checks that `style.css`, `functions.php` and the
   update library are present, then publishes the Release with the zip attached.
   Sites pick it up on Dashboard, Updates.
