# PROJECT LOG

What was built and when. Newest first.

Reading this file: the newest entries are at the top, so the first 40 lines are
usually enough. Read further only when you need older history. When this file
passes about 200 lines, archive entries older than the current year into
`DOCS/archive/PROJECT_LOG-<year>.md`.

**Provenance.** Reconstructed on 2026-08-01. Versions 1.0.0 to 1.4.2 were built on
the home machine, and Claude Code transcripts do not sync between machines, so the
office laptop has none of those sessions. The entries below come from the
`readme.txt` changelog, git history, the code itself, and one cross-reference in
the MetCPT session of 2026-07-29. Dates for 1.0.0 to 1.3.0 are phase attributions,
not commit dates: all three commits in this repo were made on 2026-07-07, when the
finished theme was first pushed to GitHub. From here on, log as work happens.

---

## 2026-08-03: v1.7.2, fix Scroll to Top missing on phone/tablet widths

Ran the PRD's step-6 verification pass on both shipped features (Page Hero,
Scroll to Top). No headless browser available in this environment, so this
was done by fetching real pages from local `v2` over HTTP and inspecting the
served HTML/CSS directly, plus computing WCAG contrast ratios from the actual
hex values, rather than Lighthouse or a screenshot. Confirmed: one `<h1>` per
page, correct `aria-label`/`aria-hidden`, served CSS matches the repo
(specificity fix from the prior entry is live), keyboard/reduced-motion/
screen-reader behaviour all correct, `44px+` touch target. Found the current
live-staging accent colour (`#e0dd31`) fails WCAG AA badly against white
(1.44:1), which is expected under D26's "not policed" design but worth
knowing. Local only has 2 real Pages (`whistle-blowing`,
`sample-page`), so `/board-charter/`, `/gallery/`, `/events/` and the
Lighthouse pass could not be checked from here; user ran those manually.

User's manual pass on live staging found a real bug: the Scroll to Top
button was visible at laptop/desktop widths but missing at phone/tablet
widths. Root cause: `position:fixed` positions against the nearest ancestor
with a `transform` set, not the screen, if one exists between the button and
`<body>`. Neither Hello Elementor's nor Elementor plugin's own core CSS sets
a transform on `<body>` or a wrapper (checked both), so the culprit is most
likely in the live site's own Theme Builder header/mobile-menu markup, not
identified further since it did not need to be: the fix does not depend on
knowing which element it is.

Fixed by re-parenting the button to a direct child of `<body>` at runtime, as
the first action in `assets/js/scroll-top.js`, before the scroll listener
attaches. Full reasoning in [DECISIONS.md D26](DECISIONS.md#d26). Not yet
retested on live staging.

---

## 2026-08-03: v1.7.1 confirmed live: auto-updater worked, colour fix verified

Retried the GitHub auto-updater on live staging for 1.7.1, this time with no
error. Confirms the earlier 1.7.0 cURL 52 "Empty reply from server" was a
one-off network blip, not a persistent block from the host: no code or
config change was made to the updater between the two attempts. Closing that
as resolved; no further action unless it recurs.

User confirmed on live staging: colour, position, and on/off all work
correctly. Both fixes below hold up outside the local environment they were
diagnosed in.

---

## 2026-08-03: v1.7.1, fix Scroll to Top colour on live staging

1.7.0 shipped and installed on live staging by manual upload (the built-in
GitHub updater failed with a cURL 52 "Empty reply from server" against the
GitHub release asset; not investigated further since the manual path worked
and the user chose not to retry the auto-updater first).

On staging, the button's on/off and position settings worked from the
Customizer, but a custom colour did not visually apply, always showing the
default petrol. View-source confirmed the correct hex value was present in
the page (`--met-tt-accent:#d59f0f;` inside the
`met-hello-child-scroll-top-inline-css` `<style>` block added by
`wp_add_inline_style()`), which ruled out a save or page-cache problem and
pointed at a CSS cascade/ordering issue instead. Staging runs LiteSpeed
Cache; its CSS optimisation features (Combine/Critical CSS) can reorder
`<link>`/`<style>` tags, letting `scroll-top.css`'s own file default win over
our override despite the correct value being earlier in the raw HTML.

Fixed by moving the colour from a `:root` `<style>` block to an inline
`style` attribute on the button element itself
([inc/scroll-top.php](../inc/scroll-top.php)), which no CSS-combining tool can
touch. Updated the Customizer live-preview script to match (targets the
button element, not `documentElement`).

That fix alone was not enough: retested on local `v2` and the colour still
did not show. View-source confirmed the correct value was now on the button's
own `style=""` attribute, which meant the remaining problem was not caching
or ordering at all. DevTools (screenshots from the user) showed the actual
culprit: a generic `[type=button], [type=submit], button { border: 1px solid
#c36; color: #c36; ... }` rule, source unidentified (not this theme's CSS),
beating our `.met-to-top` rule. `[type=button]` is an attribute selector, the
same specificity tier as a class, so it ties with `.met-to-top` rather than
losing, and cascade order (which this theme does not control) broke the tie
in the other rule's favour.

Fixed by qualifying every selector in `assets/css/scroll-top.css` with the
element type, `button.met-to-top` instead of `.met-to-top`, one specificity
tier higher than any bare class or attribute selector, so it wins
unconditionally rather than depending on load order. Both fixes are recorded
together in [DECISIONS.md D26](DECISIONS.md#d26). `phpcs` clean; neither fix
had shipped yet (v1.7.1 was not pushed or tagged until both were confirmed).

---

## 2026-08-03: Page Hero applied to all 16 target Pages; v1.7.0, Scroll to Top

Manually applied the 1.6.0 Page Hero to the remaining 15 of the 16 target
Pages on local `v2` (`/whistleblowing/` was done first, see the entry below).
All 16 now carry a hero.

Then built and shipped Scroll to Top: a sitewide floating button, on by
default, configurable (on/off, left/right, accent colour) under Appearance >
Customize > Scroll to Top. Full design in
[PLAN/PRD-scroll-top.md](../PLAN/PRD-scroll-top.md), reasoning in
[DECISIONS.md D26](DECISIONS.md#d26).

New: `inc/scroll-top.php` (Customizer section/settings/sanitisers, sitewide
enqueue gated on the on/off setting, the `wp_footer` render), and two
self-contained assets, `assets/css/scroll-top.css` and
`assets/js/scroll-top.js`, plus `assets/js/scroll-top-customizer.js` for the
Customizer live preview only. This is the theme's first sitewide JS and CSS,
and its first Customizer integration; see D26 for why it cannot depend on
`assets/css/theme.css`.

The on/off setting uses the `refresh` Customizer transport rather than
`postMessage`, unlike colour and position: turning the button off removes it
from the page entirely (no CSS, JS, or markup), and there is no cheap way to
simulate that removal live in the preview iframe without also rendering
markup that is not really there. Colour and position preview live; on/off
needs a preview reload.

Measured file sizes against the PRD's 1KB-per-file performance budget:
`scroll-top.js` is 914 bytes, under budget.
`scroll-top.css` is 1418 bytes, about 400 bytes over, after trimming comments
and shortening values once. The overage buys the accessibility and responsive
requirements from the PRD (a `prefers-reduced-motion` branch, a mobile
breakpoint, the left-position override) as real CSS rather than something cut
to hit a number. Both are still small in absolute terms and gzip further.

`phpcs` clean throughout.

---

## 2026-08-03: v1.6.0, Page Hero for Elementor Pages

Built and shipped Page Hero: an opt-in header band for Elementor Pages,
matching the design already shipped for blog Posts and archives. Full design in
[PLAN/PRD-page-hero.md](../PLAN/PRD-page-hero.md), reasoning in
[DECISIONS.md D25](DECISIONS.md#d25).

New: `inc/page-hero.php` (gate, "Page Hero" meta box, save handler, the two
Elementor `before_content` render hooks), `template-parts/page-hero.php`
(Standard variant reuses `.met-hero` unchanged; Business variant is new
markup and CSS in `assets/css/theme.css`). `inc/assets.php`'s stylesheet and
preconnect gates now also fire on a Page with a hero set; the full-width body
class was deliberately left alone, see D25.

The business variant shipped with an icon mark (ported from the CLAUDE DESIGN
reference), then the icon was removed at the user's request right after the
first live test, since a generic placeholder icon wasn't wanted on every
subsidiary page. Removed the markup and its now-dead CSS in the same session.

Tested live on `/whistleblowing/` on the local site (renamed `github-test` ->
`v2` this session). First attempt showed a completely bare page: no site
header, no hero, no styling. Cause was a missed instruction, not a bug: the
Page's Layout (Page Attributes) was not set to Elementor Full Width, so neither
Elementor's own chrome nor the hero hook rendered. Once set, both the Standard
and Business variants worked. `phpcs` clean throughout.

Remaining work is tracked in [STATE.md](STATE.md#open-items): 15 of the 16
target Pages still need the layout set, the meta box filled in, and the old
Elementor header section removed by hand; the PRD's performance/responsive pass
has not run; live staging has not been touched.

---

## 2026-08-03: first phpcs run, config fixed, 9 findings fixed

`composer install` and `phpcs` had never been run since the config shipped in
1.5.0. Ran them for the first time. Composer and PHP CLI needed manual TLS,
mbstring and zip extension flags to work on this machine's bundled PHP, since
none of php.ini's extensions are enabled by default.

Two defects in `phpcs.xml.dist` itself, found before any real code issue:

- The registered prefix list only had `met_hello_child`, so the template-local
  variables (`met_card_link` and similar, using the shorter `met_` convention)
  all flagged as unprefixed. WPCS also rejects a plain `met` prefix as too short
  (minimum 4 characters), so the fix is registering `met_` (with the trailing
  underscore) rather than `met`.
- `Generic.Files.LineEndings` flagged CRLF on every file, because git checks out
  CRLF on Windows by policy. This would fail on both machines forever without
  fixing anything real, so the sniff is now excluded, with `.gitattributes`
  already doing the actual normalisation in the repository.

After the config fix: 41 real violations in 9 files. `phpcbf` auto-fixed 32
(inline CSS indentation and alignment in the standalone pages). The remaining 9,
fixed by hand:

- 2 missing `@package` tags, in `error-403.php` and `dropins/maintenance.php`.
- 1 wrong `@return void` tag on a function that does return a value
  (`met_hello_child_wp_die_handler`).
- 1 param comment missing a full stop, on the `$args` array shape docblock.
- 1 unused `$handler` parameter, required by the `wp_die_handler` filter
  signature. Documented and ignored, not removed.
- 1 missing enqueue version, on the fonts stylesheet. The `null` is deliberate,
  documented and ignored.
- 3 non-enqueued `<link rel="stylesheet">` tags, in the two standalone pages and
  the shared renderer. All three run where `wp_enqueue_style()` is not
  available, per D7. Documented and ignored, not restructured.

`phpcs` is now clean. Verified against the running site after the fix: the 403
file (served directly, bypassing WordPress), a single post, and the maintenance
page (tested via a temporary mu-plugin, removed after) all render correctly with
no PHP notices.

`composer.lock` is now committed, so both machines lint against identical
standard versions. No version bump: nothing user-facing changed.

Closed the last two open items the same day, both as decisions rather than work:
stay on the Google Fonts CDN (D15), and ship English only with no translation
catalogue (D24). The theme now has no open items.

Also found and removed two zero-byte stray files at the repo root
(`C:UsersIIUM`, `Holdings.claudeclaude-notify-signalsstop`), created by an
unquoted path in a `claude-notify-signals` hook splitting on the space in
`C:\Users\IIUM Holdings\...`. Not part of this repo's own tooling; worth fixing
in the hook config, not here.

---

## 2026-08-01: v1.5.0 restructure to the standard theme layout

Structural refactor, no change to what the site renders.

- `functions.php` went from 549 lines to a bootstrap of about 45. Behaviour split
  into `inc/setup.php`, `inc/updater.php`, `inc/assets.php`,
  `inc/template-tags.php`, `inc/social.php`, `inc/maintenance.php`.
- Design CSS moved to `assets/css/theme.css`. `style.css` now holds the theme
  header only and is not enqueued. Both files kept git history through `git mv`.
- `maintenance-template.php` moved to `template-parts/maintenance-page.php`.
- Added `MET_HELLO_CHILD_DIR` and `MET_HELLO_CHILD_URI` so no file repeats
  `get_stylesheet_directory()`.
- The update checker is now built inside a function instead of leaving a global
  variable behind.
- Added `phpcs.xml.dist`, `composer.json`, `.editorconfig`, `.gitattributes`.
  Added `/vendor/` to `.gitignore`.
- `release.yml` now excludes DOCS, composer, phpcs and editor config from the
  zip. Before this, users would have received all of them.
- Fixed the last "Haraka" references, which were open item 1.
- Added `dropins/maintenance.php`. `readme.txt` had been telling users to copy a
  bundled file that did not exist. That was open item 4.

Checked: `php -l` clean on all 16 theme PHP files, and all 19 existing functions
are present with unchanged names.

What did not move, because WordPress does not allow it: the template hierarchy
files, `style.css`, `functions.php` and `screenshot.png` must sit at the theme
root. `error-403.php` stays too, because deployed `.htaccess` files reference its
path. See [DECISIONS.md](DECISIONS.md#d20).

Version bumped to 1.5.0 in `style.css` and `functions.php`, with a `readme.txt`
changelog entry.

**Verified against the running Local site**, all clean, no PHP notices anywhere:

| Check | Result |
|---|---|
| Single post, category, search, author, 404 | Render, correct `.met-view` modifier, correct body class |
| Stylesheet URL | `assets/css/theme.css?ver=1.5.0`, served, 18.5 KB |
| `style.css` | Served as header only, 1 KB, never enqueued |
| Home and Pages | No child CSS, no full-width class. Scope gate holds |
| Share buttons | X, Facebook, LinkedIn, WhatsApp, Telegram, Threads |
| Back link | Resolves to the post's category archive |
| Author link | Resolves to the author archive |
| Font preconnect | Both hints present, on styled views only |
| Maintenance page | 503, `Retry-After: 3600`, noindex, no home button, renders from its new path |

The maintenance check used a temporary mu-plugin gated behind a query string,
removed straight after. Tagged and released as v1.5.0.

---

## 2026-08-01: project docs added (office laptop)

Added `DOCS/STATE.md`, `DOCS/DECISIONS.md`, `DOCS/PROJECT_LOG.md` and
`DOCS/WRITING_RULES.md`. Triggered by losing access to the earlier chat sessions:
the design reasoning existed only in transcripts on the home machine, so it was
rebuilt from the artifacts and committed where both machines can see it.

Searched every Claude Code transcript on this laptop (`~/.claude/projects/`): no
theme sessions, only today's. The MetCPT session `d3a31ece` held seven useful
references to this theme, which is where the scope-boundary check and the "Haraka"
comment issue came from.

`WRITING_RULES.md` sets the writing standard for replies, docs, commits and plans.

No code changed.

---

## 2026-07-29: cross-checked against MetCPT (from the plugin session)

While auditing the MetCPT plugin, the theme was read to settle which component
owns which archive page. Findings:

- The theme reads no MetCPT options. The two are fully decoupled.
- Ownership is clean. The plugin owns `/events/`, `/tenders/`, `/careers/` and the
  raw CPT archive fallback. The theme owns news and blog category, tag and date
  archives, scoped by `is_category() || is_tag() || is_date()`.
- Found: about 4 comments still say "Haraka", the plugin's former name, in
  `functions.php`, `README.md`, `readme.txt`, `style.css`. Cosmetic only.
- Decided: do not fix it from the plugin session. Separate repo, separate release
  cycle. See [DECISIONS.md](DECISIONS.md#d17). Still open.

No code changed in this repo.

---

## 2026-07-07: v1.4.2, single post share, back and author links (`6c3dcef`)

- Share row extended to X, Facebook, LinkedIn, WhatsApp, Telegram and Threads,
  built from a reusable `met_hello_child_get_share_links()` helper and an extended
  inline SVG icon set.
- Back button now targets the post's own category archive, falling back to the
  Newsroom URL only when the post has no category.
- Author name now links to the author archive page.

`functions.php`, `single.php`, `style.css`, `readme.txt`. +89 / -12.

---

## 2026-07-07: v1.4.1, real branded screenshot (`f5de4f8`)

Replaced the 1x1 placeholder `screenshot.png` (70 bytes) with a rendered 1200x900
branded thumbnail (83 KB). Small change, real purpose: it ran the whole release
pipeline end to end (tag, Action, zip, Release, WordPress update screen) on a
change where failure cost nothing.

---

## 2026-07-07: v1.4.0, GitHub auto-updates and first public release (`b6d6583`)

First commit in the repo. The accumulated 1.0.0 to 1.4.0 work published as a
public repo: 133 files, about 12,987 lines, most of it the bundled update library.

- Bundled YahnisElsts Plugin Update Checker v5 in theme mode, pointed at
  `ismetdev/met-hello-elementor-child`, branch `main`, with
  `enableReleaseAssets()` so updates come from the workflow-built zip instead of
  GitHub's auto-generated source archive.
- Optional private-repo auth via `MET_HELLO_CHILD_GITHUB_TOKEN`, read from
  `wp-config.php` and never committed.
- Added `.github/workflows/release.yml`: fires on `v*` tags, builds the zip on
  Linux inside a correctly named folder, checks `style.css`, `functions.php` and
  the update library are present, publishes the Release with the zip attached.
- Theme author set to ismetdev.

---

## v1.3.0: search, author, 404, standalone pages, shared design system

The largest step. Three new views plus the refactor needed to support them.

- **New views:** `search.php`, `author.php`, `404.php`, all reusing one design.
- **Refactor:** CSS reorganised into a shared `.met-view` scope with a reusable
  `.met-hero` band and `.met-listing` / `.met-card` grid. The card moved to
  `template-parts/met-card.php` and is shared by archive, search and author.
  `single.php` and `archive.php` migrated to the shared classes with no intended
  visual change.
- **Scope plumbing:** enqueue scope, preconnect hints and the full-width body
  class unified behind `met_hello_child_is_styled_view()`. Per-view body classes
  collapsed into one `met-hello-child-fullwidth`.
- **Author header:** avatar, name, post count, biography, website and social links
  (Yoast aware), degrading cleanly when fields are empty.
- **Standalone pages:** maintenance toggle (503 plus cache bypass, admins exempt),
  a `wp-content/maintenance.php` update drop-in, an `ErrorDocument` 403 file, and
  a styled `wp_die()` handler for application-level 403s, all with inlined CSS.

---

## v1.2.1: phase 4 hardening

Escaping audit, conditional-asset check, i18n, cross-plugin and accessibility
review. One real bug found and fixed: double-escaped featured image `alt` text in
`single.php` and `archive.php`, because `the_post_thumbnail()` already escapes
attributes. Everything else passed with no changes.

---

## v1.2.0: phase 3, archives

`archive.php` for category, tag and date archives. Compact petrol header band plus
a uniform responsive card grid (`auto-fill minmax(320px, 1fr)`, single column on
mobile). Cards show a featured image, with a petrol pattern fallback when absent
so the grid stays even, a primary-category eyebrow, linked title, date, reading
time and a trimmed excerpt. Styled pagination with gold accents. CSS scoped under
`.met-archive`. Enqueue scope widened to these archives only.

---

## v1.1.0: phase 2, the single post

`single.php` in the new editorial design: petrol hero, feature image frame,
article body, share and back row. Geist and Instrument Serif loaded from the
Google Fonts CDN behind one function, ready to self-host. Design CSS enqueued and
full width forced ("Option A") on single Posts only. Added the reading-time,
primary-term and filterable back-link helpers. Header and footer come from
Elementor via `get_header()` and `get_footer()`, and the CSS is scoped to the
article region so it never affects them.

---

## v1.0.0: phase 1, scaffold

Child theme scaffold. Enqueues parent then child stylesheet, defines the version
constant, loads the text domain. Renders identically to plain Hello Elementor: no
custom templates, no design CSS. A deliberate no-op baseline to prove the child
theme was wired correctly before any design landed.
