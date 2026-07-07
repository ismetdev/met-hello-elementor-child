# Met Hello Elementor Child

A WordPress child theme of **Hello Elementor** that gives native blog Posts and
their surrounding surfaces a custom editorial design (petrol + gold), without
touching the parent theme, Elementor-built pages, or the Haraka plugin.

Author: [ismetdev](https://github.com/ismetdev)

## What it styles

- **Single blog Posts** (`single.php`) — editorial hero, feature image, article
  body, share/back row.
- **Category / tag / date archives** (`archive.php`) — uniform card grid.
- **Search results** (`search.php`), **404** (`404.php`), **author profiles**
  (`author.php`) — sharing the same hero + card system.
- **Maintenance** and **403 Forbidden** — standalone, self-contained pages.

Scope is deliberately narrow: it only affects native Posts and the listed
surfaces. Elementor pages and Haraka custom post types are left untouched.

## Requirements

- Parent theme **Hello Elementor** installed and available.
- WordPress 6.0+, PHP 7.4+.

## Installation

Install from a GitHub Release so automatic updates work:

1. Download `met-hello-elementor-child.zip` from the
   [latest release](https://github.com/ismetdev/met-hello-elementor-child/releases/latest).
2. WordPress admin → **Appearance → Themes → Add New → Upload Theme** → choose the
   zip → **Install** → **Activate**.

Future releases appear on **Dashboard → Updates** and **Appearance → Themes**
like any other theme update.

## Optional wiring

See `readme.txt` for details.

- **Maintenance toggle:** `define( 'MET_HELLO_CHILD_MAINTENANCE', true );` in
  `wp-config.php`.
- **Update maintenance drop-in:** copy `maintenance.php` into `wp-content/`.
- **403 page:** add to `.htaccess` (outside the WordPress markers):
  `ErrorDocument 403 /wp-content/themes/met-hello-elementor-child/error-403.php`
- **Private repo updates:** `define( 'MET_HELLO_CHILD_GITHUB_TOKEN', '…' );`

## Updates & releases

Automatic updates use the bundled
[Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)
(v5, theme mode) pointed at this repository's Releases.

To cut a release:

1. Bump the version in **both** `style.css` (the `Version:` header) and the
   `MET_HELLO_CHILD_VERSION` constant in `functions.php`.
2. Commit, then tag and push:
   ```
   git tag v1.4.0
   git push origin v1.4.0
   ```
3. The `release.yml` GitHub Action builds a correctly-structured
   `met-hello-elementor-child.zip` and publishes the Release. Sites running the
   theme pick up the update automatically.

## License

GPL-2.0-or-later.
