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

**Why.** Ship first, keep the migration to a one-function change. Self-hosting is
tracked in [STATE.md](STATE.md#open-items), not forgotten.

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
