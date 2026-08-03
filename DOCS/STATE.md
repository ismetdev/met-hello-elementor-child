# STATE

Where the project stands today. Update when the shipped version, the open work, or
the environment changes.

Last updated: 2026-08-03

## At a glance

| | |
|---|---|
| Shipped version | **1.7.2** |
| Repository | https://github.com/ismetdev/met-hello-elementor-child (public) |
| Branch | `main` |
| Tags | `v1.4.0`, `v1.4.1`, `v1.4.2`, `v1.5.0`, `v1.6.0`, `v1.7.0`, `v1.7.1`, `v1.7.2` |
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
| Page Hero (opt-in Elementor Page header) | [inc/page-hero.php](../inc/page-hero.php), [template-parts/page-hero.php](../template-parts/page-hero.php) | Shipped 1.6.0, applied to all 16 target Pages on local `v2` |
| Scroll to Top (sitewide button) | [inc/scroll-top.php](../inc/scroll-top.php), [assets/css/scroll-top.css](../assets/css/scroll-top.css), [assets/js/scroll-top.js](../assets/js/scroll-top.js) | Shipped 1.7.0. Customizer: Appearance > Customize > Scroll to Top |
| Design system CSS | [assets/css/theme.css](../assets/css/theme.css) | Tokens on `:root`, components under `.met-view` |
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

Scroll to Top (see [PLAN/PRD-scroll-top.md](../PLAN/PRD-scroll-top.md)) shipped
in 1.7.0. Two bugs found and fixed since (see [DECISIONS.md D26](DECISIONS.md#d26)):

- Colour lost under CSS optimisation and a selector-specificity tie: fixed in
  1.7.1, confirmed working on live staging (2026-08-03).
- Button missing at phone/tablet widths, present at laptop/desktop: fixed in
  1.7.2 by re-parenting the button to `<body>` at runtime. **Not yet retested
  on live staging.**
- The 1.7.0 cURL 52 auto-updater failure was a one-off network blip, not a
  persistent host block: closed, see PROJECT_LOG.
- Step-6 accessibility pass done by code/HTTP inspection (no headless browser
  in this environment): one `<h1>`, correct `aria-label`/`aria-hidden`,
  keyboard/reduced-motion/screen-reader behaviour, 44px+ touch target all
  confirmed on local. The live accent colour (`#e0dd31`) computes to 1.44:1
  contrast against white, failing WCAG AA; not a bug (D26 leaves colour
  unpoliced) but worth a note to the site owner.
- Lighthouse pass (step 8) not run: no headless browser available; needs a
  manual pass on `/board-charter/`, `/gallery/`, `/events/` on live staging.

Page Hero (see [PLAN/PRD-page-hero.md](../PLAN/PRD-page-hero.md)) shipped in
1.6.0. All 16 target Pages now have it applied on local `v2`; one `<h1>` per
page confirmed. Still open:

- The PRD's step 6 responsive/Lighthouse pass (3 representative pages) has not
  been run, same blocker as above.
- The business hero variant is built and shipped, but only tested on
  `/whistleblowing/` as a local scratch page. The 9 `/business/` subsidiary
  pages are 1.7.0-plus scope, per the PRD (icon marks were dropped from the
  business variant per a mid-build request, see PROJECT_LOG).
- Whether the Page Hero has been applied to the 16 real Pages on live staging
  (as opposed to the theme code being live there) is unconfirmed.

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

## How to cut the next release

1. Bump the version in both [style.css](../style.css) (`Version:` header) and
   `MET_HELLO_CHILD_VERSION` in [functions.php](../functions.php).
2. Add a `= X.Y.Z =` block to the changelog in [readme.txt](../readme.txt#L62).
3. Commit, push `main` first, then `git tag vX.Y.Z && git push origin vX.Y.Z`.
4. `release.yml` builds the zip inside a folder named exactly
   `met-hello-elementor-child`, checks that `style.css`, `functions.php` and the
   update library are present, then publishes the Release with the zip attached.
   Sites pick it up on Dashboard, Updates.
