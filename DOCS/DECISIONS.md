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

## D2: Elementor keeps the chrome, the theme keeps the content

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

## D4: conditional asset loading

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

## D7: standalone pages inline their own CSS

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

## D15: fonts from the Google CDN for now, behind one function

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

## D23: read these docs partially, and cap the log

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
