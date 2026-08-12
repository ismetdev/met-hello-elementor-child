# DECISIONS

Design and process decisions that still apply, with the reason for each.

Reconstructed on 2026-08-01 from the code, the `readme.txt` changelog, git
history, and the MetCPT session that re-confirmed several of them. Where the
original discussion is lost, the reason given is the one the code documents.

Add new entries at the bottom. Do not delete superseded ones, mark them.

---

## D1: child theme, never a fork of Hello Elementor

**Decision.** All custom design lives in a child theme. The parent
`hello-elementor` is never edited, renamed, or vendored.

**Why.** The parent stays updatable. The child stylesheet declares
`hello-elementor` and `hello-elementor-theme-style` as dependencies
([functions.php:120-125](../functions.php#L120-L125)), so it always loads last and
its overrides win without `!important`.

---

## D2: Elementor keeps the chrome, the theme keeps the content <a id="d2"></a>

**Decision.** Templates call `get_header()` and `get_footer()`, so the live header
and footer come from Elementor. The design source template's hardcoded `<footer>`
was not ported ([style.css:29-30](../style.css#L29-L30)).

**Why.** One header and footer for the whole site, editable in Elementor by
non-developers. All theme CSS is scoped under `.met-view` so nothing leaks into
that chrome.

---

## D3: narrow scope gate, native Posts only <a id="d3"></a>

**Decision.** `met_hello_child_is_styled_view()`
([functions.php:72-78](../functions.php#L72-L78)) uses `is_singular( 'post' )` plus
`is_category() || is_tag() || is_date()`, never the broad `is_single()` or
`is_archive()`.

**Why.** The MetCPT plugin, formerly Haraka, owns Events, Tenders and Careers:
their singles, their shortcode listing pages, and a raw archive fallback. A broad
conditional would collide on both styles and class names. The narrow gate also
keeps out Pages and the blog home, which are built in Elementor.

**Consequence.** This one function gates three behaviours: stylesheet enqueue,
font preconnect hints, and the full-width body class. Widening it widens all
three. Checked against the plugin on 2026-07-29: no overlap either way, and the
theme reads no MetCPT options.

---

## D4: conditional asset loading <a id="d4"></a>

**Decision.** The stylesheet and the Google Fonts load only on styled views. Every
other page stays plain Hello Elementor
([functions.php:112-127](../functions.php#L112-L127)). Preconnect hints use the
same gate ([functions.php:136-147](../functions.php#L136-L147)).

**Why.** Performance. No reason to send an editorial stylesheet to Elementor pages
that use none of it.

---

## D5: full width by theme rule, not a per-page setting ("Option A")

**Decision.** The theme adds a `met-hello-child-fullwidth` body class on styled
views and strips Hello Elementor's centered max-width with
`body.met-hello-child-fullwidth .site-main.met-view`
([style.css:63-77](../style.css#L63-L77)).

**Why.** Edge to edge is part of the design, not an editor choice, so it cannot be
forgotten on a new post. The selector outranks the parent's
`body:not([class*=elementor-page-]) .site-main`, and the child sheet loads later,
so it wins without `!important`.

**Note.** In 1.3.0 the per-view body classes were collapsed into this one class.

---

## D6: shared `.met-view` scope with per-page modifiers

**Decision.** Design tokens on `:root`. Every template's `<main>` carries
`.met-view` plus a modifier: `.met-single`, `.met-archive`, `.met-search`,
`.met-author`, `.met-404`. Shared components (`.met-hero` band, `.met-listing`
grid, `.met-card`, pagination) sit under `.met-view`. The single post keeps its
own `.post-*` layout.

**Why.** Refactored in 1.3.0 when search, author and 404 arrived. Three more views
copy-pasting the same card would not have been maintainable. The card moved to
[template-parts/met-card.php](../template-parts/met-card.php) and is shared by
archive, search and author. `single.php` and `archive.php` moved to the shared
classes with no intended visual change.

---

## D7: standalone pages inline their own CSS <a id="d7"></a>

**Decision.** The maintenance and 403 pages render through
`met_hello_child_render_standalone()`
([functions.php:489-548](../functions.php#L489-L548)) with all CSS inlined, no
`get_header()` or `get_footer()`, and `noindex, nofollow`.

**Why.** These run where the enqueued stylesheet is not available: a `wp_die()`
death path, or an `ErrorDocument` file hit by Apache with WordPress barely
booted. Self-contained is the only thing that renders reliably. They still look
on brand: same petrol background, gold hex pattern, same two typefaces.

---

## D8: maintenance mode returns a real 503, admins exempt, cache safe

**Decision.** Toggled by `define( 'MET_HELLO_CHILD_MAINTENANCE', true )` in
`wp-config.php` or the `met_hello_child_maintenance` filter. Runs on
`template_redirect`, sends `nocache_headers()`, `Retry-After: 3600` and
`status_header( 503 )`, and skips admin, cron, WP-CLI, and any logged-in user with
`manage_options` ([functions.php:398-421](../functions.php#L398-L421)).

**Why.** 503 tells crawlers the outage is temporary, so pages are not deindexed.
The no-cache headers stop LiteSpeed Cache from freezing the maintenance page into
the cache. The admin exemption plus the front-end-only hook keeps `wp-login.php`
and `wp-admin` reachable, so there is no lockout.

---

## D9: styled 403 through `wp_die_handler`, HTML path only

**Decision.** Filter `wp_die_handler` and render the branded 403 when
`403 === $status && ! is_admin()`. Everything else falls through to
`_default_wp_die_handler` ([functions.php:433-471](../functions.php#L433-L471)).

**Why.** That filter covers only the HTML death path. AJAX, JSON, REST and XML-RPC
have their own handlers, so admin AJAX and the REST API are provably unaffected.
Server-level 403s are handled separately by the optional `ErrorDocument` line,
which must sit outside the `# BEGIN WordPress` markers so WordPress does not
overwrite it.

---

## D10: Yoast aware primary term, with a fallback

**Decision.** `met_hello_child_get_primary_term()`
([functions.php:189-207](../functions.php#L189-L207)) uses
`_yoast_wpseo_primary_category` when set, otherwise the first term, otherwise
`null`.

**Why.** Editors already pick a primary category in Yoast, and the eyebrow label
should match what SEO and breadcrumbs show. Reading the meta key directly means no
hard dependency on Yoast being active.

The same applies to author social links
([functions.php:239-302](../functions.php#L239-L302)): read WordPress contact
methods, which Yoast extends, accept a full URL or a bare handle, expand known
networks, and return an empty array when there is nothing.

---

## D11: the back button follows the post's own category

**Decision (1.4.2).** The single post back button targets the post's category
archive, falling back to the Newsroom URL only when the post has no category. The
Newsroom default `/news-announcement/` stays filterable through
`met_hello_child_back_link_url`
([functions.php:217-226](../functions.php#L217-L226)).

**Why.** Back should return the reader to the list they likely came from. This
replaces the 1.1.0 behaviour of always returning to Newsroom.

---

## D12: share targets and their order

**Decision (1.4.2).** X, Facebook, LinkedIn, WhatsApp, Telegram, Threads, built
from one helper, `met_hello_child_get_share_links()`
([functions.php:334-370](../functions.php#L334-L370)), with inline SVG icons from
`met_hello_child_social_icon()`.

**Why.** WhatsApp and Telegram matter for a Malaysian audience. Threads and X were
added in 1.4.2. The order follows normal share-bar convention. Keeping the list in
one helper means adding a network is one array entry plus an icon.

---

## D13: auto-updates from GitHub Releases, delivered as a release asset

**Decision (1.4.0).** Bundle YahnisElsts Plugin Update Checker v5 in theme mode
([functions.php:30-47](../functions.php#L30-L47)), branch `main`, with
`enableReleaseAssets()`.

**Why.** The theme should update from Appearance, Themes and Dashboard, Updates
like any other theme, with no extra plugin. `enableReleaseAssets()` is the key
part: GitHub's auto-generated source zip unpacks to
`met-hello-elementor-child-1.4.2/`, which WordPress would install as a different
theme. The workflow-built asset unpacks to a clean `met-hello-elementor-child/`.
Same approach as MetTranslate, so all three repos share one pattern.

Auth is optional and env driven. `MET_HELLO_CHILD_GITHUB_TOKEN` in `wp-config.php`
is used only if defined, so a public repo needs nothing and no token is ever
committed.

---

## D14: the release zip is built on Linux, by CI, on tag push

**Decision.** [release.yml](../.github/workflows/release.yml) fires on `v*` tags,
stages the theme inside a folder named exactly `met-hello-elementor-child`,
excludes `.git`, `.github`, `.gitignore`, `.gitattributes`, `.claude`,
`node_modules` and `build`, zips it, and checks that `style.css`, `functions.php`
and the update library are present before publishing.

**Why.** Building on Linux with forward-slash paths avoids Windows and PowerShell
backslash corruption in the zip. Both dev machines are Windows, so a hand-built
zip was a real risk. The presence checks turn a silently broken package into a
failed build.

---

## D15: fonts from the Google CDN for now, behind one function <a id="d15"></a>

**Decision.** Geist and Instrument Serif load from the Google Fonts CDN through
one function, `met_hello_child_fonts_url()`
([functions.php:98-100](../functions.php#L98-L100)), with a TODO describing the
self-hosting path.

**Why.** Ship first, keep the migration to a one-function change.

**Settled on 2026-08-03: stay on the CDN.** Google Fonts is treated as a trusted
dependency for this site, and the theme has bigger things to spend effort on. The
one-function structure stays, so the decision can be reversed cheaply if a
privacy or performance requirement ever forces it. Noted for the record: browsers
partition their HTTP cache per site now, so the old "the visitor already has it
cached from another site" benefit no longer exists, and self-hosting is usually
the faster option. Not enough to act on.

---

## D16: prefix everything

**Decision.** Every function, constant, CSS class and body class carries
`met_hello_child_`, `MET_HELLO_CHILD_` or `met-`.

**Why.** Three components on this site ship from separate repos (this theme,
MetCPT, MetTranslate) and share one PHP namespace at runtime. Prefixing is what
keeps that collision free.

---

## D17: cross-repo edits stay in their own repo <a id="d17"></a>

**Decision (2026-07-29).** When the MetCPT session found stale "Haraka" comments
in this theme, they were not fixed from that session, even though absolute paths
made it possible.

**Why.** This theme is a separate repo with its own release cycle. A theme edit
made in a plugin session muddies both git histories and leaves an uncommitted
change in a working tree nobody is watching. The fix is open item 1 in
[STATE.md](STATE.md#open-items).

---

## D18: GitHub is the source of truth across two machines

**Decision.** Run `git pull --ff-only origin main` before editing, on either
machine. When releasing, push `main` before pushing the `vX.Y.Z` tag.

**Why.** This repo is worked on from a home machine (git author "Ismet Home") and
an office laptop ("Ismet Office"). In July 2026 the sibling MetTranslate repo was
edited from a stale laptop copy, because a release made at home had not been
pulled. That caused a branch and tag divergence and needed a rebase plus a moved
remote tag. Pushing a tag first can point it at a commit the remote does not have.

**Corollary.** Claude Code transcripts are per machine and do not sync. That is
why this file, [STATE.md](STATE.md) and [PROJECT_LOG.md](PROJECT_LOG.md) are
committed. The repo is the only thing both machines see.

---

## D19: one writing standard for the whole project

**Decision (2026-08-01).** All writing follows
[WRITING_RULES.md](WRITING_RULES.md): no em dash, not verbose, clear and complete,
no bombastic words, simple English. Commit messages follow the same rules plus
conventional commit format. This covers chat replies, docs, commits, PRDs and
plans.

**Why.** Long output costs tokens and reading time for no gain.

---

## D20: standard theme layout, within what WordPress allows <a id="d20"></a>

**Decision (2026-08-01, v1.5.0).** `functions.php` is a bootstrap only. Behaviour
lives in six modules under `inc/`. The design CSS moved to
`assets/css/theme.css`, and `style.css` keeps the theme header and nothing else.
The maintenance page moved to `template-parts/maintenance-page.php`. The
WordPress drop-in was added at `dropins/maintenance.php`.

**Why.** A 549-line `functions.php` mixing updates, enqueues, template tags,
icons and error pages is the usual place a WordPress theme rots. Splitting it
means a change has one obvious home.

**What could not move, and why.** WordPress resolves the template hierarchy at
the theme root only. `single.php`, `archive.php`, `author.php`, `search.php` and
`404.php` must stay there, as must `style.css`, `functions.php` and
`screenshot.png`. `error-403.php` stays because live `.htaccess` files point at
its full path, so moving it would break the 403 page on deployed sites.

**Behaviour note.** The only user-visible change is the enqueued stylesheet URL,
now `assets/css/theme.css`. Nothing else about the rendered output changed. All
19 public functions kept their names, so any external code hooking them still
works.

---

## D21: lint config in the repo, dependencies out of it

**Decision (2026-08-01).** Ship `phpcs.xml.dist` (WordPress Coding Standards,
text domain and prefix enforced) and `composer.json`. Keep `vendor/` out of git,
and keep dev files out of the release zip through `.gitattributes` and the
`release.yml` exclude list.

**Why.** The standard should be checked into the repo so both machines lint the
same way. The dependencies should not be, because a WordPress theme is deployed
by copying files and no site needs phpcs.

**Corrected 2026-08-07.** `PLAN/` was missing from both exclude lists and had
been shipping in every release zip since it was created in 1.6.0: 228 KB of
internal PRDs and the design gallery, delivered to every site that installs
the theme. `composer.lock` was in the `release.yml` list but not
`.gitattributes`. Both added to both lists. The two lists are easy to let
drift apart because only one of them, `release.yml`, actually builds the
published zip; `.gitattributes` `export-ignore` only affects `git archive`.
`.gitattributes` now carries a comment saying to keep them in step.

---

## D22: plan on Opus, code on Sonnet 5 <a id="d22"></a>

**Decision (2026-08-01).** Planning, research and review run on the default
model. All coding runs on **Sonnet 5**.

The handover is explicit. After the user approves a plan, Claude must stop before
writing any code and say:

> Plan approved. Switch to Sonnet 5 now: run `/model sonnet`. Tell me when you
> have, and I will start.

Claude does not begin editing files until the user confirms the switch. If the
user says to proceed anyway, that is their call, so proceed and note the model in
use.

Coding means writing or editing project files: PHP, CSS, JS, config, templates.
It does not mean the small edits that are part of planning, such as updating a
doc in `DOCS/`.

**Why.** Sonnet 5 is fast and cheap enough for implementation once the plan is
settled, and the plan is where the expensive thinking belongs. Splitting the two
keeps cost down without weakening the design work. Same reason as
[WRITING_RULES.md](WRITING_RULES.md): work smart, do not burn tokens by habit.

**How this is enforced.** `CLAUDE.md` at the repo root carries the rule, because
Claude Code loads that file at the start of every session on both machines.
Nothing in `DOCS/` is loaded automatically. If the rule ever needs to change,
change it in both places.

---

## D23: read these docs partially, and cap the log <a id="d23"></a>

**Decision (2026-08-01).** `CLAUDE.md` states how much of each doc to read.
`PROJECT_LOG.md` defaults to its top 40 lines. `DECISIONS.md` is searched by
topic rather than read whole. `STATE.md` and `WRITING_RULES.md` are read whole
because they stay small. When `PROJECT_LOG.md` passes about 200 lines, entries
older than the current year move to `DOCS/archive/PROJECT_LOG-<year>.md`.

**Why.** The log is the only append-forever file here, and almost every task
needs just the newest entries. Left alone it would grow until reading it cost
more than the work. The other files are bounded, so a blanket rule against
reading them fully would cost more in missed context than it saves in tokens.

**Measured on 2026-08-01**, so the trade-off is on record rather than guessed:
`CLAUDE.md` about 630 tokens and the only file loaded automatically,
`DECISIONS.md` about 4,100, `PROJECT_LOG.md` about 2,600, `STATE.md` about 1,500,
`WRITING_RULES.md` about 830.

**Amended 2026-08-07, because the rule could not fire.** The original trigger
was "entries older than the current year". `PROJECT_LOG.md` reached 712 lines
with every entry dated 2026, so there was nothing older than the current year
to move and the cap never applied. The rule assumed a project that spans
years; this one did a year's work in five weeks.

**The rule now.** When `PROJECT_LOG.md` passes about 200 lines, move
everything **older than the current version era** into
`DOCS/archive/PROJECT_LOG-<year>.md`, keeping the live file to the active
release and its immediate context. Append to the existing year file rather
than creating a second one. Leave an index table at the bottom of the live
log naming what each archived period covers, so a reader can decide whether
to open the archive without opening it.

**Also amended: the "top 40 lines" default.** That held while entries were
short. A single entry can now run past 150 lines. The instruction in
`CLAUDE.md` is now "read the newest entry whole, then stop", which is what
the 40-line rule was always approximating.

**First archive, 2026-08-07.** v1.0.0 through v1.8.0, twenty entries,
2026-07-07 to 2026-08-04, moved unedited into
[archive/PROJECT_LOG-2026.md](archive/PROJECT_LOG-2026.md). Live log went from
712 lines to 209.

---

## D24: the theme ships English only, no translation catalogue

**Decision (2026-08-03).** No `.pot` template, no `languages/` catalogue, no
translation work. The theme's interface text stays English.

**Why.** The site is English, so a translation catalogue would be effort spent
with no return. Generating and maintaining a `.pot` only pays off when somebody
is actually translating it.

**What stays anyway.** Every string keeps its `__()` / `esc_html__()` wrapper and
the `met-hello-child` text domain, and `phpcs` keeps enforcing the domain. That
costs nothing now and is what makes this reversible: if the site ever needs a
second language, generating the catalogue is a single `wp i18n make-pot` run
against code that is already prepared. Stripping the wrappers to "clean up" would
throw that away, so do not.

**Scope.** This is about the theme's own interface text. It says nothing about
the MetTranslate plugin, which handles page content and is a separate project
with its own repo and release cycle.

---

## D25: Page Hero is opt-in per Page, gated separately from the styled views <a id="d25"></a>

**Decision (2026-08-03).** Elementor Pages can carry a header band ("Page
Hero", [inc/page-hero.php](../inc/page-hero.php)), but only when an editor sets
a variant in the Page's own "Page Hero" meta box. There is no slug list in code.
The stylesheet and font preconnect hints load on such a Page via a second,
narrower test, `met_hello_child_page_has_hero()`, kept separate from
`met_hello_child_is_styled_view()` ([D3](#d3)). The full-width body class stays
tied to the original test only.

**Why.** `met_hello_child_is_styled_view()` also drives the full-width body
class, which strips Hello Elementor's centered container. Folding Pages into
that test would force full width on every Page carrying a hero, fighting
whatever layout Elementor already has for it. Elementor Full Width Pages (what
all 16 target Pages use) already render edge to edge, so the class is not
needed there; keeping the gates separate means a Page can opt into the hero
without opting into anything else the styled-view test controls.

Opt-in via meta, rather than a hardcoded slug list, means adding the hero to a
new Page is an editor action, not a code release.

**Where it renders.** The hero prints via
`elementor/page_templates/header-footer/before_content` (Elementor Full Width,
what all 16 launch Pages use) and, as a safety net, the matching Canvas hook.
Default and Theme page layouts render no hero; the meta box states this next to
the variant field. See
[PLAN/PRD-page-hero.md](../PLAN/PRD-page-hero.md) for the fuller design
reasoning, including the performance budget.

**Data.** Copy (eyebrow, subtitle, business-variant CTA buttons) lives in
`_met_hero_*` post meta, edited from the Page editor. No hero title field: the
hero always prints `get_the_title()`.

---

## D26: Scroll to Top is a deliberate, sitewide exception to D2 <a id="d26"></a>

**Decision (2026-08-03).** A floating Scroll to Top button
([inc/scroll-top.php](../inc/scroll-top.php)) renders on `wp_footer` on every
page of the site: the homepage, every Elementor Page, blog Posts, archives, and
everything else. On by default, configurable (on/off, side, colour) in the
Customizer under "Scroll to Top".

**Why an exception, not a new rule.** [D2](#d2) says Elementor keeps the
chrome, the theme keeps the content. A floating button is chrome. Three reasons
this one piece of chrome goes in the theme anyway, checked 2026-08-03:

- Elementor free has no Back to Top widget; it is Elementor Pro only.
- Hello Elementor 3.4.9 does not provide one either.
- Building it per Page in Elementor would mean repeating and keeping it in
  sync across every page by hand, the same inconsistency problem
  [D25](#d25)'s Page Hero was built to solve.

D2 still governs everything else. This does not open the door to moving other
chrome into the theme without the same reasoning.

**Why it cannot use theme.css.** [assets/css/theme.css](../assets/css/theme.css)
is gated to the styled views and Page Hero pages
(`met_hello_child_is_styled_view()`, `met_hello_child_page_has_hero()`), so on
the homepage and most Elementor Pages it never loads, and its `:root` tokens do
not exist there. The button ships two small, self-contained files
(`assets/css/scroll-top.css`, `assets/js/scroll-top.js`) with no dependency on
theme.css, so either can load without the other, and the button works
correctly on pages that carry none of the theme's other design system.

**Why a real `<button>`, not a link.** It performs an action (scroll), not a
navigation, so it is keyboard-activatable on Enter and Space with no JS beyond
the click handler, and reads correctly to a screen reader with no extra ARIA.

**Customizer, one colour.** A single accent colour drives the border, the
arrow, and the hover fill; there is no separate picker for each, which would
let someone configure an unreadable combination. See
[PLAN/PRD-scroll-top.md](../PLAN/PRD-scroll-top.md) for the fuller reasoning,
including the performance budget and the accessibility checklist.

**The colour is an inline `style` attribute on the button, not a `wp_add_inline_style()` `<style>` tag (fixed in 1.7.1).** The first cut used
`wp_add_inline_style()`, which prints a `:root{--met-tt-accent:...}` block. On
a site running CSS optimisation (found on live staging: LiteSpeed Cache's CSS
Combine), that block is a `<style>` tag like any other, free to be moved,
merged, or reordered. If `scroll-top.css`'s own file default ends up printed
after our override, the file's default wins the cascade, even though the
correct value is present earlier in the raw source, which is exactly what
made this look right in "View Source" while showing the wrong colour on
screen. An inline `style=""` attribute is part of the element itself, not a
stylesheet resource, so no combiner or critical-CSS tool can touch it. The
Customizer's live-preview JS was updated to match: it sets the property on the
button element directly, not on `documentElement`.

**Selectors are qualified `button.met-to-top`, not bare `.met-to-top` (also fixed in 1.7.1).** A second, independent bug behind the same "colour doesn't
show" symptom. Even after the fix above put the correct `--met-tt-accent`
value directly on the element, DevTools on local `v2` showed a generic
`[type=button], [type=submit], button { border: 1px solid #c36; color: #c36;
... }` rule (source not this theme) winning over our
`.met-to-top { border: ...var(--met-tt-accent); color: ...var(--met-tt-accent);
}` rule. The reason: `[type=button]` is an attribute selector, the same
specificity tier as a class selector (0,1,0), so it ties with `.met-to-top`
rather than losing to it, and the tie is broken by cascade order, which this
theme cannot control or predict. Qualifying every rule with the element type,
`button.met-to-top` (0,1,1), puts this component one specificity tier above
any bare class or attribute selector, so it wins regardless of what else is
on the page or in what order it loads. This is the general fix for "some
unrelated CSS keeps overriding my button," not a one-off patch for this one
conflict.

**The button re-parents itself to a direct child of `<body>` at runtime (fixed in 1.7.2).** A third, separate bug: on live staging, the button was visible
at laptop/desktop widths but missing at phone/tablet widths. `position:fixed`
positions against the nearest ancestor that has a `transform` (or `filter` /
`perspective`) set, not the screen, if one exists between the element and
`<body>`. A responsive header's mobile-menu slide animation is a common way
for a site to end up with exactly that, usually on a wrapper the theme does
not control and that only applies at some breakpoints. `assets/js/scroll-top.js`
now moves the button to be a direct child of `<body>` as its first action, the
same technique modals and tooltips use for this exact class of bug, so no
ancestor markup, on this site or the next redesign, can trap it again.

---

## D27: a sitewide token layer, and a `<main>` landmark the parent theme never gave Elementor Pages <a id="d27"></a>

**Decision (2026-08-04, v1.8.0).** Two new files, `assets/css/tokens.css`
and `assets/css/elementor-base.css`, load on every page of the site with no
gate: not just the theme's own styled views, not just Pages with a Page Hero.
`tokens.css` holds only the `:root` custom properties, merged from
`theme.css` and the `CLAUDE DESIGN` reference files with no value changed.
`elementor-base.css` is the one file in this theme that writes selectors
targeting Elementor's own class names. What it can and cannot reach is
narrower than first planned; see the correction below.

`theme.css` no longer defines its own `:root`. It declares
`met-hello-child-tokens` as a dependency in
[inc/assets.php](../inc/assets.php) instead, so there is one token source,
not two that could drift apart.

**Why sitewide, like Scroll to Top ([D26](#d26)), not gated like `theme.css`.**
The homepage and most Elementor Pages never load `theme.css`
([D4](#d4)), so a token layer meant to reach every page cannot depend on it.
Same constraint, same answer as Scroll to Top: a small, self-contained file
with no dependency on the gated stylesheet.

**Why this does not widen [D3](#d3) or [D25](#d25).** Those gates control
three specific things: the main stylesheet, the font preconnect hints, and
the full-width body class. `tokens.css` and `elementor-base.css` are a fourth,
independent thing, enqueued by their own function
(`met_hello_child_enqueue_tokens()`), not by widening
`met_hello_child_is_styled_view()` or `met_hello_child_page_has_hero()`.
Confirmed on local `v2` that the full-width body class still does not appear
on Elementor Pages after this change. Not yet confirmed on staging, since
1.8.0 has not been released there.

**Why `elementor-base.css` cannot collide with `theme.css`.** `theme.css`
scopes every component rule under `.met-view`, a class only the theme's own
templates print. `elementor-base.css` scopes every rule to Elementor's own
class names (`.elementor-widget-heading`, `.elementor-button`, `.e-con`, and
so on). Neither file's selectors can match an element the other one targets,
so the two layers cannot fight each other, and neither uses `!important`
except the one documented exception below.

**The one `!important`, and why it is there.** The
`prefers-reduced-motion: reduce` block in `elementor-base.css` uses
`!important` on every animation and transition property. It has to beat
whatever duration Elementor, Essential Addons or Ultimate Addons set, inline
or otherwise, for a visitor who has asked their OS to reduce motion. That
request should always win, so this is the one place the rule in
[PLAN/PRD-design-tokens.md](../PLAN/PRD-design-tokens.md) about not reaching
for `!important` first gets a named exception.

**The `<main>` landmark bug, found by reading the parent theme and Elementor's
own source, not by guessing.** Hello Elementor's `template-parts/single.php`,
`archive.php`, `search.php` and `404.php` each print
`<main id="content" class="site-main">`. Its `header.php` does not: it only
opens `<body>` and prints the site header. Elementor's own page templates,
`header-footer.php` (Full Width, what [D25](#d25) says all 16 launch Pages
use) and `canvas.php`, call `get_header()` then Elementor's content then
`get_footer()`, with nothing else in between. The result: every Elementor
Page on this site, confirmed by curling a live Full Width Page on local `v2`,
shipped with no `<main>` element and no landmark for assistive technology to
jump to. The parent theme's own skip link, which targets `#content`, was
pointing at nothing on every one of those pages.

**The fix, and why it is safe.** Two functions in
[inc/setup.php](../inc/setup.php),
`met_hello_child_open_elementor_main()` and
`met_hello_child_close_elementor_main()`, print the opening and closing
`<main>` tags. Hooked on `elementor/page_templates/header-footer/before_content`
/ `after_content` and the matching `canvas` hooks, the same actions
[D25](#d25)'s Page Hero already uses, at priority 5 for the open (so `<main>`
opens before the hero band prints, keeping the hero inside the landmark as
page content, not ahead of it) and priority 20 for the close. No Elementor or
parent theme file is edited. Verified on a live Full Width Page on local `v2`:
exactly one `<main>` open, exactly one close, header and footer unchanged,
skip link now resolves to a real element. Not verified against a live Canvas
Page, since none exists on local `v2`; the code path is the same function
hooked to Elementor's documented `canvas` hooks, read directly from
`elementor/modules/page-templates/templates/canvas.php`.

**What stayed out, on purpose.** No page-level Elementor edit. No change to
Essential Addons, Ultimate Addons, or which plugins the site runs. No change
to the accent colour contrast fail ([D26](#d26)), still known and unpoliced.
Full reasoning, the measured PageSpeed baseline, and the staging checklist
for the parts that cannot be done from a repo (images, CSS delivery, Global
Colours, the responsive walk) are in
[PLAN/PRD-design-tokens.md](../PLAN/PRD-design-tokens.md) and
[PLAN/STAGING-CHECKLIST-1.8.0.md](../PLAN/STAGING-CHECKLIST-1.8.0.md).

**Where this is going.** `tokens.css` is written to become the token source
if the site moves to a block theme with `theme.json`, the target direction
recorded in [PLAN/PROPOSAL-frontend-revamp.md](../PLAN/PROPOSAL-frontend-revamp.md).
Nothing in this decision commits to that move; it only avoids doing throwaway
work ahead of it.

**Correction, same day, found by testing live on local `v2`.** The first cut
of `elementor-base.css` also set `font-family` and `font-size` on default
Elementor heading and text-editor widgets, on the theory that a widget with
no local styling would fall through to a generic cascade rule. Testing on a
real page (Whistle Blowing) in DevTools disproved that: Elementor generates
a CSS file per page (e.g. `post-86.css`) and bakes the Kit's typography
default into a rule scoped to that exact widget's element ID, three class
selectors deep with one of them unique to the widget. That beats a generic
two-class rule like `.elementor-widget-heading .elementor-heading-title`
every time, on plain CSS specificity, regardless of load order, and it does
this whether or not the widget has been customised, because "Default" is
itself a value Elementor resolves and bakes in. `!important` would work but
was rejected: fighting Elementor's own generator forever, on every property,
is not a stylesheet's job.

**The actual lever for typography and colour on Elementor-authored content
is Elementor's own Global Fonts and Global Colours (Site Settings, PRD step
10).** That is where Elementor's generator reads the value it bakes in, so
it is the only place that reaches every widget, including ones this file
cannot touch. The heading and text-editor rules were removed from
`elementor-base.css` as dead code, same day. What remains, focus outline,
reduced motion, box-sizing, overflow safety, is not Kit-driven the same way
and does survive the cascade. Container width is left in with a caveat, not
yet verified against the same override.

**The Geist font is not in Elementor Free's bundled font list**, found while
trying to set Global Fonts to it. Elementor Free's picker only offers its own
list plus system fonts; self-hosted custom fonts are an Elementor Pro
feature. Decided: set Elementor's Global Fonts to **Inter**, already in the
list and visually close, rather than add a new plugin or a font-registration
snippet three days before the demo. The theme's own native views (blog,
archive, search, author, 404) keep true Geist, unaffected, since they load
it directly ([D15](#d15)). This means Elementor-authored pages and the
theme's native views will not carry byte-identical typography until Geist is
registered for Elementor after the demo, a known, accepted gap, not an
oversight.

---

## D28: theme.json is the canonical design system, superseding the D27 token layer <a id="d28"></a>

**Decision (2026-08-07, v1.9.0).** `theme.json` at the theme root holds the
design system: colour, type scale, spacing, section rhythm, layout widths,
elevation, radius, z-index, motion and focus. `assets/css/tokens.css` no longer
holds values. It is now an alias layer, mapping every old flat name
(`--gold`, `--ink`, `--space-5`) onto the matching `--wp--preset--*` or
`--wp--custom--*` property, so `theme.css`, `scroll-top.css` and
`elementor-base.css` keep working with no rewrite.

**Why this replaces [D27](#d27)'s approach.** D27 shipped a sitewide token
layer meant to govern Elementor from CSS. Its own same-day correction proved
that cannot work: Elementor generates a stylesheet per page and bakes the Kit
default into a rule scoped to each widget's element ID, which outranks any
generic rule on plain specificity, whatever the load order. A stylesheet
cannot govern Elementor content. The only way to govern the content is to own
it, which is what the block-system migration does
([PLAN/PRD-block-system.md](../PLAN/PRD-block-system.md)).

**Two-layer naming, and why the slugs matter.** Primitives name the paint
(`--gold-500`). Semantics name the job (`--color-accent`). Everything
references semantics, never primitives. This is not style preference: the
block editor writes the palette **slug** into every saved page as
`has-{slug}-color`, so a palette named after paint would bake the paint name
into 30 pages of content and make a future rebrand a database find and
replace. Named after the job, a rebrand is one value in one file.

**Display and text are separate tokens for every accent.** `--color-accent`
`#B98A2E` stays for headings, icons, borders and decoration. A darker sibling
`--color-accent-text` `#85621E` is used for body text, labels and small
links, because the display shade computes to 2.72:1 on the paper background
and fails WCAG AA. The same split exists for the three sector colours. The
brand looks unchanged; only small text moves.

**The system was approved visually before it was built.**
[PLAN/DESIGN-SYSTEM-GALLERY.html](../PLAN/DESIGN-SYSTEM-GALLERY.html) renders
every token, every component and every open A/B choice as real markup, opened
in a browser and signed off 2026-08-07. The findings behind it, including the
discovery that the 40 design files contain two systems rather than the 21 first
estimated, are in
[PLAN/DESIGN-SYSTEM-DECISIONS.md](../PLAN/DESIGN-SYSTEM-DECISIONS.md).

**Fonts are self-hosted through `theme.json`** `fontFace` entries, closing the
[D15](#d15) TODO. The standalone death-path pages (`dropins/maintenance.php`,
`error-403.php`, `inc/maintenance.php`) still load from the Google CDN,
unchanged and on purpose: they run outside a booted WordPress ([D7](#d7)) and
cannot read `theme.json`.

---

## D29: Pages render through the theme's own page.php, and migration is two meta keys <a id="d29"></a>

**Decision (2026-08-07, v1.9.0).** The child theme ships `page.php`. A Page
moves off Elementor by clearing **both** `_elementor_edit_mode` and
`_wp_page_template`. `_elementor_data` is never touched, so the move is
reversible.

**Why `page.php` had to exist.** Neither Hello Elementor nor this theme had
one, so every Page fell through `index.php` to
`hello-elementor/template-parts/single.php`, a template written for blog
posts. That gave any non-Elementor Page three faults at once: a second `<h1>`
alongside Page Hero's, an open comment form (Site Health confirms comments
default to open on this site), and a `<main class="site-main">` that the
parent's `body:not([class*=elementor-page-]) .site-main{max-width:1140px}`
rule squeezes to blog-post width the moment the Elementor body class goes
away.

**How `page.php` beats the width rule without a fight.** The parent's rule
keys on the **class** `.site-main`. The parent's skip link keys on the **id**
`#content`. Keeping `id="content"` and dropping the class removes the
constraint and keeps the skip link working, with no `!important` and no
specificity contest. Same philosophy as [D26](#d26): win by structure, not by
force.

**Two meta keys, not one, found by testing.** The first cut of
[PLAN/PRD-block-system.md](../PLAN/PRD-block-system.md) section 4.5 said
clearing `_elementor_edit_mode` was enough. It is not. That meta only decides
whether Elementor's **editor** treats the Page as builder content. Front-end
template selection reads `_wp_page_template`, the Page Attributes "Template"
value, which stays at `elementor_header_footer` and sends WordPress past
`page.php` to `index.php`'s generic fallback, reintroducing the exact width
bug `page.php` exists to prevent. `inc/migration-tools.php` now clears both
and stores the original template under `_met_migration_original_template`, so
rollback restores the real previous value rather than a guess.

**`page.php` calls `get_header()` and `get_footer()`.** Obvious, and it was
missing from the first cut, which rendered every migrated Page as a bare HTML
fragment with no `<head>`, no enqueued stylesheets and no site chrome. Caught
by fetching the real page and grepping for stylesheet handles that were not
there. Recorded because it is the clearest example of the rule this migration
runs on: verify by reading rendered output, not by reasoning about code.

**`inc/migration-tools.php` is temporary.** It exists because this migration
is driven from an environment with no WP-CLI and no database client, and the
meta it flips is not exposed through the REST API. It is `manage_options`
gated and nonce checked. Remove it, and its `require_once` in
`functions.php`, when phase 4 ships or WP-CLI access arrives.

---

## D30: Page Hero is the fixed sitewide header. Design files supply body content only <a id="d30"></a>

**Decision (2026-08-07, owner instruction).** Every migrated Page keeps the
Page Hero band from [D25](#d25) as its header, unchanged. The per-page design
files in `CLAUDE DESIGN` supply the **body content below the header** and
nothing else. Page Hero is never redesigned, replaced or removed to match an
individual page's mockup.

**Why.** Page Hero is already deployed on all 16 target Pages on local and
live staging, and it exists precisely so no page's header looks different
from any other's. Each design file is a full-page mockup carrying its own
top-of-page treatment, and those treatments differ from each other: some open
with a plain light intro, some with a coloured band. Reproducing each one
faithfully would give the site 30 different headers, which is the exact
inconsistency this whole project exists to remove. Fidelity to one file is
worth less than consistency across all of them.

**What this means in practice.**

- The `<h1>` is always `get_the_title()`, printed by Page Hero. A design
  file's own display headline ("Speak up. We're listening.") becomes the hero
  **subtitle**, or is dropped if the hero already reads well without it. It
  never becomes a second heading.
- A design file's opening intro section is not rebuilt. Its body copy moves
  into the first block of page content.
- Anything in a design file above its first real content section is header
  material, and header material is Page Hero's, not the page's.

**How this was found.** The first migrated Page reproduced
`whistleblowing.html`'s light intro section literally, which put a plain
intro where every other Page on the site has a petrol band. Corrected on
owner review the same day. The `met-page__intro`, `met-page__title` and
`met-page__lede` rules in `assets/css/patterns.css` and the fallback intro
markup in `page.php` remain, and are correct, but they only ever apply to a
Page that has **no** hero variant set, which no migrated Page in this project
should have.

**This does not weaken [D25](#d25).** Page Hero stays opt-in per Page through
its meta box, and a Page with no variant set still renders no hero. The rule
here is about the migration: every Page in it opts in, with the standard or
business variant, and the variant is a content decision made in the meta box,
not a design decision remade in CSS per page.

### Amended 2026-08-11: the nine subsidiary pages are the exception

The nine `/business/` subsidiary child pages (178 to 186) have **Page Hero set
to None**, and their approved design's own hero band is built as the first
Elementor section instead.

**Why the exception holds.** D30 exists to stop the site growing thirty
different headers. These nine share one hero design with each other, so the
consistency argument is satisfied within the set: same dark ground, same gold
eyebrow naming the division, same heading scale, same two buttons. They are
also the deepest pages in the tree, where the design carries a photograph and a
call to action that Page Hero has no field for.

**The rule this does not break:** exactly one `h1` per page. Page Hero is off
precisely so the design hero can own the `h1`. Turning Page Hero back on for
any of these nine without removing its hero section would print two.

**How this was found.** The build first kept Page Hero and dropped the design's
hero, reading the owner's "neglect any header or footer" as covering it. The
owner corrected it: that instruction meant the site chrome, and the design's
hero band is page content.

---

## D35: Page bodies are built in Elementor again, governed by the token system <a id="d35"></a>

**Decision (2026-08-08, owner instruction).** Page bodies are built with
Elementor free plus Essential Addons and UAE. The block-editor migration in
[PRD-block-system.md](../PLAN/PRD-block-system.md) is suspended for page bodies.

**Why this is not a return to [D27](#d27)'s failed approach.** D27 tried to
govern Elementor from a stylesheet and failed, because Elementor bakes its Kit
defaults into per-widget CSS scoped to element IDs, which outranks any generic
rule. This decision accepts that and inverts it: the tokens are written **into
each widget's own settings at build time**, so they win on the same specificity
Elementor uses. The design system is still the single source of truth; only the
delivery mechanism changed.

**What made it viable.** Novamira's `execute-php` lets `_elementor_data` be
written programmatically, so a page is authored as a data structure with token
values rather than clicked together by hand. Without that this would be
unmaintainable.

**The cost, stated plainly.** Elementor stays a dependency, which was the thing
the block migration existed to remove. Phase 4 of the PRD (chrome into the
theme) still stands and is being brought forward as v1.10.0, so the header,
footer and homepage leave Elementor even though page bodies do not.

**Addon defaults must be overridden, every time.** Essential Addons ships its
own brand colours (`#f56a6a` coral, `#ff622a` orange, `#333` grey) that bleed
into any widget left at defaults. On the Group of Companies page 22 colour
controls had to be set explicitly. Treat every addon widget as hostile to the
design system until proven otherwise, and run `check-design` to catch it.

---

## D36: theme.json font-size slugs that start with a digit get a hyphen <a id="d36"></a>

**Decision (2026-08-08, found by owner review).** Every alias of a
`theme.json` preset must be verified against the CSS custom property WordPress
actually generates, and must carry a literal fallback value.

**The bug.** `theme.json` defines font sizes with slugs `2xl` and `3xl`.
WordPress does not generate `--wp--preset--font-size--2xl`. It inserts a hyphen
between the digit and the letters, producing `--wp--preset--font-size--2-xl`.
`tokens.css` aliased the names that do not exist, so `--text-2xl` and
`--text-3xl` resolved to nothing, every `font-size` using them was invalid, and
the affected elements silently fell back to the browser default size.

**What it broke.** The Page Hero title on all 16 Pages, and every `h2` in every
blog post. Later found again in `patterns.css`, affecting three more headings.

**Why it survived so long.** A broken `var()` still renders the text. curl and
grep see correct markup, correct classes and correct stylesheet links. Only a
human eye or a computed-style check catches it. This is the same lesson as the
migration's "structural verification is not visual verification", arriving from
a new direction: the markup was never wrong, the cascade was.

**The rule now.** Every `var(--wp--preset--*)` reference carries a fallback:
`var(--wp--preset--font-size--3-xl, clamp(36px, 4.5vw, 56px))`. A future slug
rename then degrades to a visibly wrong size rather than a silently absent one.

---

## D37: the homepage is a Page Template, not front-page.php <a id="d37"></a>

**Decision (2026-08-08, owner instruction, for v1.10.0).** The homepage design
ships as `page-templates/template-homepage.php` carrying a
`Template Name: Homepage` header, assigned per Page in Page Attributes, with the
front page then chosen in Settings, Reading.

**Why not `front-page.php`.** It would work, but it takes the choice away from
the owner: `front-page.php` outranks both the Page Template dropdown and the
Reading setting, so the dropdown would list a template that never applies. The
owner asked to control this from the admin, which is also the more conventional
WordPress answer. A Page Template can additionally be applied to any Page, not
only the front one.

**Consequence.** `front-page.php` must never be added to this theme while this
decision stands, or it will silently override the template.

---

## D38: the new header and footer ship behind a Customizer toggle <a id="d38"></a>

**Decision (2026-08-08, for v1.10.0).** The custom site chrome is gated by a
Customizer checkbox, `met_hello_child_chrome_enabled`, default off. When off,
`header.php` and `footer.php` fall through to the parent theme's versions.

**Why.** Switching the chrome changes every page on the site at once. A toggle
makes that reversible in one click with no file deletion and no deploy.

**Header Footer Elementor is installed but currently owns nothing.**
`get_hfe_header_id()` and `get_hfe_footer_id()` both return false, so the parent
Hello Elementor templates render the chrome today. The toggle still adds
`hfe_header_enabled` and `hfe_footer_enabled` filters as insurance, because if a
template is ever assigned, `HFE_Default_Compat::override_header()` runs
`remove_all_actions('wp_head')` and discards the theme's `header.php` output
entirely. Note the fallback must use `require get_template_directory()`, not
`locate_template()`, which would find the child file and recurse.

**Menu locations stay content-only.** Assigning a menu in Appearance, Menus
never switches the chrome on or off. Coupling them would give a routine content
action a sitewide structural side effect and would destroy the rollback.

---

## D39: hero slides are a non-public theme CPT, not a MetCPT type <a id="d39"></a>

**Decision (2026-08-08, built in v1.10.0).** The homepage hero carousel reads
`met_hero_slide`, a custom post type registered in `inc/hero-slides.php` with
`public => false`, `show_in_rest => false`, no archive and no single view. The
featured image is the slide background; the copy is classic meta fields shaped
like the Page Hero fields.

**Why here and not in MetCPT.** MetCPT owns the site's *public* content types
(Events, Tenders, Careers), which have their own URLs, archives and REST
exposure. A hero slide is presentation furniture for one theme template: it has
no public URL and no meaning outside the homepage. Putting it in the theme keeps
it with the template that consumes it, and `show_in_rest => false` deliberately
forces the classic editor so the classic meta box is the correct tool. Zero
slides falls back to one static slide from the site identity; one slide renders
with no dots or arrows.

---

## D40: business sector is Page meta, not a taxonomy <a id="d40"></a>

**Decision (2026-08-08, built in v1.10.0).** A `/business/` child Page's division
is stored in a single `_met_sector` Page meta key, sanitised against
`education / infrastructure / healthcare` (`inc/sectors.php`). It is read by the
homepage companies grid and the header mega menu to colour-code cards and
columns.

**Why not a taxonomy.** A real taxonomy is content shape, with its own archive,
REST surface and admin UI, and that belongs in the MetCPT plugin, not in this
presentation theme. A meta key is enough for colour-coding and keeps the theme
from owning content structure it should not.

**The eyebrow fallback is a convenience, then a backfill.**
`met_hello_child_get_page_sector()` resolves saved meta first, then parses the
Page Hero eyebrow ("Education Division" -> education), then returns ''. The
parse means the nine Pages render with the right colour on day one with zero
data entry; the one-time backfill in `inc/migration-tools.php` then writes the
real meta so the parse is no longer relied on. All nine were backfilled on
2026-08-08.

---

## D41: pages are built from real Elementor widgets, never one HTML widget <a id="d41"></a>

**Decision (2026-08-09, owner instruction, after a rejected build).** A design
file is a *reference*, not a payload. Every page is composed from Elementor
containers and real widgets: `heading`, `text-editor`, `image`, `button`,
`icon`, `divider`, plus the two addon plugins where they earn their place. The
`html` widget is reserved for the one thing Elementor and the addons genuinely
cannot express, and even then it holds a fragment, not a page.

**What went wrong.** The first 25th Anniversary build pasted the approved design
file's markup, `<style>` block included, into a single `html` widget. It looked
efficient. It was not a build. The design's absolutely positioned sections and
`min-height:82vh` hero collided with Elementor's own flex layout and destroyed
the page: a giant blank band, the hero squashed into a narrow column, and five
sections missing entirely. The owner rejected it on sight. Worse, it had been
reported as verified after checking only the HTML source, which is precisely the
failure STATE.md already warns about.

**Why it matters beyond that one page.** A page shipped as pasted markup is not
editable by the owner in Elementor, does not inherit Elementor's responsive
controls, and cannot be maintained by anyone who is not editing raw HTML. The
whole reason for D35 was to build *with* the tool rather than against it.

**Token values are baked per widget at build time**, matching the pattern the
Whistleblowing page already used: hex values from `theme.json`, and
`typography_font_family: "Geist"`. Do not write `var(--wp--preset--*)` into
widget settings.

### Elementor gotchas that have each cost a rebuild

Check these four before declaring any Elementor page done. Every one was found
by looking at a screenshot, never by reading the markup.

1. **Grid containers default to two rows.** `container_type: grid` emits
   `grid-template-rows: repeat(2, 1fr)` from Elementor's own base CSS, so a grid
   holding a single row of cards reserves an equal empty row beneath it. On the
   Group of Companies page that added roughly 370px of dead space under every
   grid. Fix: set `grid_rows_grid` to `array('unit'=>'custom','size'=>'auto')`
   on every breakpoint. Deleting the setting is not enough; it falls back to the
   two-row default.
2. **Background overlay opacity defaults to 0.5.** A gradient scrim set through
   `background_overlay_*` renders at half strength, so text over photos comes out
   unreadable. Always set
   `background_overlay_opacity => array('unit'=>'px','size'=>1)`.
3. **Images in grid cells need an explicit width.** CSS grid items do not shrink
   below their content, so a 1024px logo forces its column wide and the page
   gains horizontal scroll. Set `image_size` to a registered size, a real
   attachment `id`, and an explicit `width`.
4. **Font Awesome is version 5 here**, not 6. `fa-shield-halved` and other FA6
   names do not exist and make Elementor print PHP warnings into the rendered
   card. Use `fa-shield-alt` and check
   `plugins/elementor/assets/lib/font-awesome/css/all.css` before inventing a
   name.

---

## D42: page images are imported into the media library, never hotlinked <a id="d42"></a>

**Decision (2026-08-09).** Any image a design file points at on
`v2.iiumholdings.com.my` is imported into the local media library with
`media_handle_sideload()` before the page is built, and the page references the
local attachment.

**Why.** Staging sits behind Cloudflare with hotlink protection. Those URLs
return `200` to a server-side fetch and to `curl`, so they look fine in every
structural check, but a browser loading them from a page on `http://v2` sends a
cross-origin `Referer` and gets blocked. The result is a page full of broken
images that passes every automated test. The 25th Anniversary emblem failed this
way in front of the owner.

**Consequence.** A built page has zero external image references. That is also
what we want when the page eventually moves to staging, since the media then
lives in the site's own library rather than depending on another origin.

---

## D43: visual verification means a screenshot, and the agent takes it <a id="d43"></a>

**Decision (2026-08-09).** Before reporting any page as done, render it in
headless Chrome and look at it. `curl` and `grep` do not count.

**How.** Chrome is installed at
`C:/Program Files/Google/Chrome/Application/chrome.exe`. Node 24 drives it over
the DevTools Protocol: launch with `--headless=new --remote-debugging-port`,
connect to the target's WebSocket, call `Emulation.setDeviceMetricsOverride` for
a real viewport, then `Page.captureScreenshot` with `captureBeyondViewport:true`
for a full page. Scripts live in the session scratchpad.

**Two things to know about the capture.**
`min-height` in `vh` follows the emulated window height, so a tall capture
window inflates any `vh` hero and the screenshot lies about its height. Judge
`vh` sections at a real viewport height. And `captureBeyondViewport` does not
trigger `loading="lazy"` images below the fold, so they photograph blank; scroll
to them with `Runtime.evaluate` before concluding an image is broken.

**Also worth running every time:** measure `document.documentElement.scrollWidth`
against `clientWidth` at 390, 768, 1366 and 1920, and count `h1` elements. Both
are one line of JavaScript and both have caught real defects.

---

## D44: post listings are a theme shortcode, not an addon widget <a id="d44"></a>

**Decision (2026-08-09, built in v1.11.0).** A list of Posts on a page comes from
the theme's own `[met_posts]` shortcode (`inc/listing.php`), dropped into an
Elementor Shortcode widget. Not Essential Addons Post Grid, and not MetCPT's
`news_grid`.

**The deciding reason is the move to staging.** Elementor and Essential Addons
query controls store category **term IDs**. Local term IDs are not staging term
IDs, so a page exported from local and imported to staging lists the wrong posts,
or none, and every structural check still passes. This is the same class of
failure as the attachment IDs in [D42](#d42) and the image URLs in
[DEPLOY-TO-STAGING.md](DEPLOY-TO-STAGING.md). A shortcode written
`category="announcements"` is a **slug**, and slugs are identical on both sites,
so it survives the move as plain text.

**Two more reasons.** Essential Addons ships coral and orange brand defaults that
bleed into any control left alone ([D35](#d35) records 22 colour controls needing
explicit values on one page); a shortcode keeps the listing design in one
stylesheet instead of re-entered per page. And MetCPT already delivers listings
on this site as shortcodes, so this keeps one idiom, not two. MetCPT's own
styling was rejected because `.mcpt-v2` defines its own `--mcpt-*` variables,
reads no theme tokens, and still imports Google Fonts, against D15 and D28.

**The cost, stated plainly.** The listing itself is not visually editable in
Elementor; it is configured by shortcode attributes (`category`, `count`,
`layout`, `columns`, `featured`, `paged`). That is the trade for portability and
one-place styling. The rest of the page is still built from native widgets, so
D41 holds.

**Implementation notes worth keeping.** A misspelled or unknown slug returns the
empty state, never the whole blog: `met_hello_child_listing_tax_query()` returns
`false` when an include slug matched no term, and the renderer treats that as
"show nothing". The stylesheet is enqueued only when the page actually uses the
shortcode, detected by `met_hello_child_page_has_shortcode()`, which checks both
`post_content` and the `_elementor_data` blob, because Elementor stores widget
content in meta, not in `post_content`. `listing.css` reads `theme.json`
properties directly like `home.css`, not the `tokens.css` alias layer that
`theme.css` uses; keeping the two layers apart is the D36 lesson.

---

## D45: news, press releases, CSR and gallery are one post type by category <a id="d45"></a>

**Decision (2026-08-09, owner instruction, built in v1.11.0).** News,
announcements, press releases, CSR items and gallery albums are all the standard
`post` type, separated by **category**, not by post type. New categories
`press-releases` and `gallery` were created; `csr` already existed.

**Why not custom post types.** A CPT earns its place when its fields differ from
a post. These do not: each is a title, a date, a featured image and a body. As
plain posts they share `single.php`, the archive templates, RSS, Yoast and the
search index with no extra code. If a field ever does diverge, it belongs in
MetCPT, which owns the site's public content types ([D39](#d39)), not in this
presentation theme.

---

## D46: gallery albums live on Facebook, WordPress holds the pointer <a id="d46"></a>

**Decision (2026-08-09, owner practice made a feature, built in v1.11.0).** A
gallery item is a Post in the `gallery` category with a cover image and a
description. The photos themselves stay in a Facebook album. A new post meta key
`_met_album_url` holds the external album link; `single.php` renders a "View the
full album" button when it is set, and `[met_posts layout="album"]` marks the
card.

**Why this is right, not a workaround.** The owner has done this for three years
after filling the hosting disk in year one by uploading hundreds of
high-resolution event photos to the media library. Facebook hosts the images;
the site carries an indexable page on its own domain with the description and
share row. Adding image sizes or bulk-importing those photos would work directly
against the reason this decision exists. **A future session must not "fix" this
by uploading the album images.** No new `add_image_size` was added; listings
reuse `met-card` and `met-thumb`.

**The card links to the post, not straight to Facebook.** The post is the
on-domain, indexable surface. Sending the card directly to the external album is
a one-attribute change if that is ever wanted.

---

## D47: the homepage is an Elementor page, built on a shortcode bridge <a id="d47"></a>

**Decision (2026-08-11, owner instruction, Group MD/CEO feedback).** The
homepage (Page 156) moved from the v1.10.0 Page Template
(`page-templates/template-homepage.php`, [D37](#d37)) to a real Elementor
page, template "Elementor Full Width" (`elementor_header_footer`), so the
Group MD/CEO can edit the editorial furniture, eyebrows, headings,
descriptions, buttons, without a developer.

**Why not literally the "Theme" template the owner named.** The owner's
instruction said "change the template ... to Theme". That page template value
routes through this theme's own `page.php`, which (correctly, for every other
Page on the site) prints a Page Hero or a fallback `<h1>` intro whenever
`_met_hero_variant` is empty. The homepage's own Elementor content already
supplies its hero and its one `<h1>`, so `page.php`'s fallback would have
printed a second, plain intro band above it, the exact class of bug D30 was
written to prevent. "Elementor Full Width" calls the same `get_header()` and
`get_footer()` as "Theme" would, keeping the header and footer theme-controlled
exactly as intended, but skips `page.php`'s hero/intro branch entirely, which
is what every other Elementor-built Page on this site already relies on. The
owner's stated goal, "we need to use Elementor for the homepage", is met
either way; this is the version of it that does not conflict with D30.

**Why a shortcode bridge and not a rebuild of the approved sections.** Four
sections were already shown to and approved by the Group MD/CEO: the hero
slider, the announcement cards, the portfolio gallery, and the newsroom list.
Rebuilding them as native Elementor widgets would mean re-approving work that
was already signed off, and is exactly the kind of drift a second build tool
invites. Instead, `inc/home-shortcodes.php` registers six shortcodes
(`[met_home_hero]`, `[met_home_announcements]`, `[met_home_newsroom]`,
`[met_companies]`, `[met_tenders]`, `[met_careers]`) that render from the same
data helpers and, for the four approved sections, the same markup as the
partials in `template-parts/home/`. Elementor supplies only the furniture
around them.

**The old build is the rollback, untouched.**
`page-templates/template-homepage.php`, `inc/homepage.php`, and every partial
in `template-parts/home/` are unmodified. Re-selecting the Homepage template
on Page 156 renders them exactly as before; `met_hello_child_is_home_view()`
still gates their own `home.css`/`home.js` enqueue on that template, so
nothing about the old path changed.

**One gap this uncovered.** Every rule in `home.css` and every DOM query in
`home.js` is scoped under one ancestor element, `.met-home`, which used to be
`<main class="met-home">` printed by the Page Template. An Elementor page has
no such single wrapping element by default, its sections are separate
top-level containers. `met_hello_child_wrap_home_content()`, hooked on
`the_content` at priority 20, wraps Elementor's fully rendered output in
`<div class="met-home met-home--js">`, reproducing the same single-ancestor
structure with no changes to `home.css` or `home.js` themselves. Scoped to the
front page, and only when a home shortcode is actually present, so it is inert
everywhere else and inert again if the homepage's content ever changes to
something that uses none of the six shortcodes.

**Consequence for the homepage stats band.** The four stat figures
(Incorporated, Companies, Industries, Employees) were Customizer fields in
`inc/homepage.php`. On the Elementor build they are plain Elementor text,
because there is no Elementor-native way to pull a Customizer `theme_mod` into
a text widget without custom code the owner did not ask for. The Customizer
fields still exist and still work if the Page Template is ever restored; on
the Elementor page they are dormant. The owner was told this while the plan
was being written.

---

## D48: the footer can move to Elementor independently of the header <a id="d48"></a>

**Decision (2026-08-11, owner instruction, Group MD/CEO feedback).** The
single chrome toggle from [D38](#d38) split into two:
`met_hello_child_chrome_enabled` (the header, unchanged) and
`met_hello_child_footer_enabled` (new, default on). Turning the footer toggle
off hands the footer to a footer built in the Header Footer Elementor plugin
(bundled with UAE), via `hfe_footer_enabled()` and `hfe_render_footer()`,
falling back to the theme footer if none is assigned.

**Why the header stays theme code.** The Group MD/CEO's only complaint was the
footer: petrol background, logo lost against it. He said he is happy with the
menu. The header carries the mega menu, the drawer, and keyboard/focus
handling built and tuned over several rounds; rebuilding it in Elementor's
free menu widget (no mega-menu support) would spend the most effort on the one
part nobody asked to change, for a design that already works.

**The Elementor footer sits on a light ground**, `surface` `#F7F3EC`, not the
`surface-dark`/`surface-deepest` the header still uses, specifically so the
logo reads, that was the actual complaint being fixed.

**How Header Footer Elementor actually decides which template renders,
because this cost real time to work out.** This plugin is settings-based, not
condition-per-call the way Elementor Pro's Theme Builder is: exactly one
`elementor-hf` post is "the" site footer, chosen by matching
`ehf_target_include_locations` post meta against the current page, filtered to
posts whose `ehf_template_type` meta equals the **literal string**
`type_header`, `type_before_footer` or `type_footer`, not `header` or
`footer`. Setting the meta to `footer` instead of `type_footer` produces no
visible error; `hfe_footer_enabled()` and `get_hfe_footer_id()` simply return
`false` forever, because `Header_Footer_Elementor::get_template_id()` is
called with the full setting-name string as the comparison value. If a footer
(or header) built this way is ever missing on the front end with no error
anywhere, check this meta value first.

**`met_hello_child_disable_hfe_when_chrome_on()` in `inc/chrome.php`
now applies its four HFE-disabling filters in two independent pairs**, one
gated on the header toggle and one on the footer toggle, so turning the
footer toggle off actually lets an HFE footer render instead of being
filtered back off by the theme's own insurance filters.

---

## D49: Infrastructure is renamed to Facilities, slug included <a id="d49"></a>

**Decision (2026-08-11, owner instruction, Group MD/CEO feedback).** The
"Infrastructure" division is renamed "Facilities" sitewide: the label in
`met_hello_child_sector_label()`, the sector slug itself in
`met_hello_child_sectors()` (`infrastructure` to `facilities`), the
`theme.json` colour preset slug (`sector-infrastructure` to
`sector-facilities`), every `met-*--infrastructure` CSS class in
`home.css`/`chrome.css`, and the saved `_met_sector` meta on the three
Facilities subsidiary Pages (Daya Bersih 182, IIUM Advanced Technologies 183,
IIUM Properties 184).

**Why the slug too, not only the label.** A label-only rename was offered as
the faster, zero-migration option. The owner explicitly asked for the full
rename, so the code and the visible interface agree: a future contributor
reading `met_hello_child_sectors()` sees `facilities` and does not have to
know it once meant something else.

**The slug rename is reversible.** A new admin-post action,
`met_hello_child_handle_rename_sector()` in `inc/migration-tools.php`, accepts
a `direction` argument (`forward`, the default, or `back`) and rewrites
`_met_sector` on the `/business/` child Pages accordingly. Same temporary-tool
convention as the rest of that file.

**The eyebrow-parse fallback keeps `infrastructure` as a permanent alias.**
`met_hello_child_sector_from_eyebrow()` in `inc/sectors.php` checks for the
substring `infrastructure` before checking the current sector list, and maps
it to `facilities`. This is not a migration step to later remove: it is the
same safety net D40 already relies on for any Page whose Page Hero eyebrow
still reads old text, and there is no cost to leaving it in place.

**What grep does not find.** A sitewide `grep -r infrastructure` catches code,
but not the four kinds of hand-authored content that also named the division:
a Page's own `_elementor_data` body copy (the three Facilities subsidiary
pages' hero eyebrows, the `/business/` landing page's division band), a hero
slide's body text, Yoast meta descriptions (three found: the homepage, the
`/business/` page, Corporate Profile), and menu item titles (one: the
"Business" mega-menu column header). All four were found by querying the
database directly for the substring, not by trusting a code-only sweep. **A
future rename of any division name must repeat this same four-part sweep**,
not stop at `grep`.

**One thing this correctly did not rename.** Plain English uses of the word
"infrastructure" that are not the division name, Daya Bersih's own service
list ("Roadworks, pavement, and infrastructure"), IAT's "IT systems and
infrastructure", IMSC's "sharing infrastructure, diagnostic imaging", were
left as written. Only the exact division-name occurrences (the heading
"Infrastructure" and the three-item "education, infrastructure, and
healthcare" list phrase) were replaced.
