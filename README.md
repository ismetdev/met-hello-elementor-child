# Met Hello Elementor Child

A WordPress child theme of **Hello Elementor**. It gives native blog Posts and
their surrounding pages a custom editorial design (petrol and gold), without
touching the parent theme, Elementor-built pages, or the MetCPT plugin.

Author: [ismetdev](https://github.com/ismetdev)

## What it styles

- **Single blog Posts** (`single.php`): editorial hero, feature image, article
  body, share and back row.
- **Category, tag and date archives** (`archive.php`): uniform card grid.
- **Search results** (`search.php`), **404** (`404.php`), **author profiles**
  (`author.php`): the same hero and card system.
- **Maintenance** and **403 Forbidden**: standalone, self-contained pages.

Scope is deliberately narrow. It only affects native Posts and the pages listed
above. Elementor pages and MetCPT post types are left alone.

## Requirements

- Parent theme **Hello Elementor** installed and available.
- WordPress 6.0+, PHP 7.4+.

## Structure

```
met-hello-elementor-child/
├── style.css                  Theme header only. Not enqueued.
├── functions.php              Bootstrap: version, constants, loads inc/.
├── 404.php  archive.php  author.php  search.php  single.php
│                              Template hierarchy files. WordPress requires
│                              these at the theme root.
├── error-403.php              Apache ErrorDocument target. Path is referenced
│                              from .htaccess, so it must stay at the root.
├── screenshot.png             Theme thumbnail. Root is required.
├── readme.txt                 WordPress-style readme and changelog.
├── assets/
│   └── css/theme.css          The design system. This is what gets enqueued.
├── inc/
│   ├── setup.php              Text domain, styled-view test, body class.
│   ├── updater.php            GitHub release updates.
│   ├── assets.php             Fonts, stylesheet enqueue, resource hints.
│   ├── template-tags.php      Reading time, primary term, back link.
│   ├── social.php             Share links, author links, inline SVG icons.
│   └── maintenance.php        Maintenance mode, styled 403, page renderer.
├── template-parts/
│   ├── met-card.php           Listing card, shared by archive/search/author.
│   └── maintenance-page.php   Maintenance page body.
├── dropins/
│   └── maintenance.php        Copy to wp-content/maintenance.php by hand.
├── libs/plugin-update-checker/  Bundled third-party library. Do not edit.
└── DOCS/                      Project docs. Excluded from the release zip.
```

Rule of thumb: template hierarchy files stay at the root because WordPress looks
for them there. Everything else belongs in `inc/`, `assets/` or `template-parts/`.

## Installation

Install from a GitHub Release so automatic updates work.

1. Download `met-hello-elementor-child.zip` from the
   [latest release](https://github.com/ismetdev/met-hello-elementor-child/releases/latest).
2. WordPress admin, **Appearance > Themes > Add New > Upload Theme**, choose the
   zip, **Install**, **Activate**.

Later releases appear on **Dashboard > Updates** and **Appearance > Themes** like
any other theme update.

## Optional wiring

See `readme.txt` for details.

- **Maintenance toggle:** `define( 'MET_HELLO_CHILD_MAINTENANCE', true );` in
  `wp-config.php`.
- **Update maintenance drop-in:** copy `dropins/maintenance.php` to
  `wp-content/maintenance.php`.
- **403 page:** add to `.htaccess`, outside the WordPress markers:
  `ErrorDocument 403 /wp-content/themes/met-hello-elementor-child/error-403.php`
- **Private repo updates:** `define( 'MET_HELLO_CHILD_GITHUB_TOKEN', '…' );`

## Development

Coding standard: WordPress Coding Standards, configured in `phpcs.xml.dist`.

```
composer install
composer run lint
```

Project context lives in [DOCS/](DOCS/): current state, the decisions behind the
code, the build log, and the writing rules.

## Updates and releases

Automatic updates use the bundled
[Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)
(v5, theme mode) pointed at this repository's Releases.

To cut a release:

1. Bump the version in **both** `style.css` (the `Version:` header) and the
   `MET_HELLO_CHILD_VERSION` constant in `functions.php`.
2. Add a changelog entry to `readme.txt`.
3. Commit, push `main`, then tag and push the tag:
   ```
   git tag v1.5.0
   git push origin v1.5.0
   ```
4. The `release.yml` GitHub Action builds a correctly structured
   `met-hello-elementor-child.zip` and publishes the Release. Sites running the
   theme pick up the update automatically.

## License

GPL-2.0-or-later.
