=== Met Hello Elementor Child ===

Contributors: ismetdev
Author: ismetdev
Author URI: https://github.com/ismetdev
Theme URI: https://github.com/ismetdev/met-hello-elementor-child
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Template: hello-elementor
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Child theme of Hello Elementor. It gives native WordPress blog Posts
(single.php), their category/tag/date archives (archive.php), search results,
404, and author profiles a custom editorial design, and provides matching
standalone maintenance and 403 pages.

Scope is deliberately narrow:

* It styles native blog Posts, their archives, search, 404, and author pages.
* It never edits or renames the parent theme (hello-elementor).
* It never touches the Haraka plugin (Events / Tenders / Careers custom post
  types and their single templates).
* Elementor-built pages are left untouched.

== Installation ==

1. Ensure the parent theme "Hello Elementor" is installed.
2. Upload this folder to wp-content/themes/ (or upload the zip via
   Appearance > Themes > Add New > Upload Theme).
3. Activate "Met Hello Elementor Child" under Appearance > Themes.

== Optional wiring ==

These extra surfaces need one-time wiring; the themed Posts/archives/search/404/
author pages work with no setup.

Maintenance mode (theme toggle):
  Add to wp-config.php (above the "stop editing" line):
      define( 'MET_HELLO_CHILD_MAINTENANCE', true );
  Logged-out visitors then see a styled 503 maintenance page; administrators keep
  browsing the live site. Remove the line (or set it to false) to go back online.
  You can also toggle it programmatically via the `met_hello_child_maintenance`
  filter. If LiteSpeed Cache is active, purge all after toggling.

Maintenance during WordPress updates (drop-in):
  Copy the bundled maintenance.php into wp-content/ (i.e. wp-content/maintenance.php).
  WordPress shows it automatically while core/plugins/themes update. This file is
  NOT part of the theme folder and must be placed manually on each deploy.

403 Forbidden page (server ErrorDocument):
  Add this line to your .htaccess, OUTSIDE the "# BEGIN WordPress" / "# END
  WordPress" markers (so WordPress does not overwrite it):
      ErrorDocument 403 /wp-content/themes/met-hello-elementor-child/error-403.php
  Application-level 403s raised by WordPress itself (e.g. a failed nonce) are
  additionally styled automatically via the theme's wp_die() handler — no setup
  needed for those.

== Changelog ==

= 1.4.1 =
* Replace the placeholder screenshot with a real 1200x900 branded theme
  thumbnail. Verifies the end-to-end GitHub update flow.

= 1.4.0 =
* Add GitHub-based automatic updates via the bundled Plugin Update Checker
  library (theme mode). New releases published on
  https://github.com/ismetdev/met-hello-elementor-child appear on the
  Appearance > Themes and Dashboard > Updates screens like any other theme
  update. Optional private-repo auth via MET_HELLO_CHILD_GITHUB_TOKEN.
* Set the theme author to ismetdev (https://github.com/ismetdev).

= 1.3.0 =
* Add search.php, author.php, and 404.php, all reusing the shared design.
* Add standalone, self-contained (inlined-CSS) maintenance and 403 pages:
  a theme maintenance toggle (503 + cache-bypass, admins exempt), a
  wp-content/maintenance.php update drop-in, an ErrorDocument 403 file, and a
  styled wp_die() handler for application-level 403s.
* Author profile header shows avatar, name, post count, biography, and
  website/social links (Yoast-aware), degrading cleanly when fields are empty.
* Refactor the design CSS into a shared `.met-view` scope with reusable
  `.met-hero` band and `.met-listing`/`.met-card` grid; extract the card into
  template-parts/met-card.php (used by archive, search, author). single.php and
  archive.php migrated to the shared classes (no visual change intended).
* Enqueue scope, preconnect hints, and the full-width body class now cover the
  new views via met_hello_child_is_styled_view(); a single
  `met-hello-child-fullwidth` body class replaces the per-view ones.

= 1.2.1 =
* Phase 4 hardening: fix double-escaped featured-image alt text in single.php
  and archive.php (the_post_thumbnail() already escapes attributes). Escaping
  audit, conditional-asset, i18n, cross-plugin, and accessibility review passed
  with no other changes required.

= 1.2.0 =
* Phase 3: Add archive.php for category/tag/date archives — a compact petrol
  header band plus a uniform responsive card grid (auto-fill minmax(320px,1fr),
  single column on mobile). Cards show featured image (petrol pattern fallback
  when absent, so the grid stays even), primary-category eyebrow, linked title,
  date + reading time, and a trimmed excerpt. Styled pagination with gold
  accents. Design CSS extended and still conditionally enqueued (now on
  category/tag/date archives too); scoped under .met-archive.

= 1.1.0 =
* Phase 2: Add single.php for native blog Posts using the new editorial design
  (petrol hero, feature-image frame, article body, share/back row). Load Geist +
  Instrument Serif (Google Fonts CDN, self-host-ready). Enqueue the design CSS
  and force full-width (Option A) on single Posts only. Add reading-time,
  primary-term, and filterable back-link helpers. Header/footer come from
  Elementor via get_header()/get_footer(). Design CSS is scoped to the article
  region so it never affects the header/footer.

= 1.0.0 =
* Phase 1: Initial child-theme scaffold. Enqueues the parent then child
  stylesheet, defines the version constant, and loads the text domain. Renders
  identically to plain Hello Elementor. No custom templates or design CSS yet.
