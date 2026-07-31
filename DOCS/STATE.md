# STATE

Where the project stands today. Update when the shipped version, the open work, or
the environment changes.

Last updated: 2026-08-01

## At a glance

| | |
|---|---|
| Shipped version | **1.4.2** (`style.css` header and `MET_HELLO_CHILD_VERSION`) |
| Repository | https://github.com/ismetdev/met-hello-elementor-child (public) |
| Branch | `main`, in sync with `origin/main` at `6c3dcef` |
| Tags | `v1.4.0`, `v1.4.1`, `v1.4.2` |
| Parent theme | `hello-elementor` |
| Requires | WordPress 6.0+ (tested to 6.5), PHP 7.4+ |
| Text domain | `met-hello-child` (`/languages`) |
| License | GPL-2.0-or-later |

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
| Maintenance (503) | [maintenance-template.php](../maintenance-template.php) | Shipped 1.3.0, needs a `wp-config.php` toggle |
| 403 Forbidden | [error-403.php](../error-403.php) | Shipped 1.3.0, needs `.htaccess` wiring |
| Shared card partial | [template-parts/met-card.php](../template-parts/met-card.php) | Shipped 1.3.0 |
| Design system CSS | [style.css](../style.css) | Tokens on `:root`, components under `.met-view` |
| Auto-update pipeline | [functions.php:30-47](../functions.php#L30-L47), [release.yml](../.github/workflows/release.yml) | Shipped 1.4.0, verified by the 1.4.1 release |

## Scope boundary

This is the thing most likely to break by accident.

`met_hello_child_is_styled_view()` in
[functions.php:72-78](../functions.php#L72-L78) is the single gate for the
stylesheet, the font preconnect hints, and the full-width body class. It uses
`is_singular( 'post' )` plus `is_category() || is_tag() || is_date()`, not the
broad `is_single()` or `is_archive()`. That keeps MetCPT Events, Tenders and
Careers, Pages, and the blog home out. See [DECISIONS.md](DECISIONS.md#d3).

Checked from the MetCPT side on 2026-07-29. The plugin owns `/events/`,
`/tenders/`, `/careers/` and the raw CPT archive fallback. The theme owns the news
and blog category, tag and date archives. No overlap.

## Environment

- Local dev site: `github-test` (Local by Flywheel).
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

1. **Stale "Haraka" branding in comments.** About 4 comments still say "Haraka"
   instead of "MetCPT": [functions.php:8](../functions.php#L8),
   [README.md:5](../README.md#L5), [readme.txt:25](../readme.txt#L25),
   [style.css:4](../style.css#L4). Cosmetic, no functional effect. Raised from the
   MetCPT session on 2026-07-29 and deferred to a theme session. Still open.
2. **Self-host the fonts.** Geist and Instrument Serif load from the Google Fonts
   CDN. The TODO at [functions.php:92-100](../functions.php#L92-L100) keeps the
   swap to one function: drop files in `/assets/fonts`, ship a local `@font-face`
   sheet, return its URL.
3. **No `/languages` directory yet.** The text domain is declared and strings are
   wrapped, but no `.pot` has been generated.
4. **`maintenance.php` naming.** `readme.txt` tells users to copy a bundled
   `maintenance.php` into `wp-content/`, but the repo ships
   `maintenance-template.php`. Reconcile the two.

## How to cut the next release

1. Bump the version in both [style.css](../style.css#L8) (`Version:` header) and
   `MET_HELLO_CHILD_VERSION` in [functions.php:22](../functions.php#L22).
2. Add a `= X.Y.Z =` block to the changelog in [readme.txt](../readme.txt#L62).
3. Commit, push `main` first, then `git tag vX.Y.Z && git push origin vX.Y.Z`.
4. `release.yml` builds the zip inside a folder named exactly
   `met-hello-elementor-child`, checks that `style.css`, `functions.php` and the
   update library are present, then publishes the Release with the zip attached.
   Sites pick it up on Dashboard, Updates.
