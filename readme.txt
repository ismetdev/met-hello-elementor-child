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
* It never touches the MetCPT plugin (Events / Tenders / Careers custom post
  types and their single templates).
* Elementor-built Pages are left untouched, except for an opt-in Page Hero band
  (see Optional wiring below), which a Page only gets if an editor turns it on.
* A sitewide Scroll to Top button is the one exception to "theme keeps the
  content, Elementor keeps the chrome": on by default everywhere, configurable
  in Appearance > Customize > Scroll to Top.

== Installation ==

1. Ensure the parent theme "Hello Elementor" is installed.
2. Upload this folder to wp-content/themes/ (or upload the zip via
   Appearance > Themes > Add New > Upload Theme).
3. Activate "Met Hello Elementor Child" under Appearance > Themes.

== Optional wiring ==

These extra surfaces need one-time wiring; the themed Posts/archives/search/404/
author pages work with no setup.

Scroll to Top (on by default, no setup needed):
  A floating button on every page, hidden until the reader scrolls down about
  one screen. Configure it under Appearance > Customize > Scroll to Top: turn
  it off, move it to the left, or change its colour (default is the brand
  petrol). If a full-page caching plugin is active, purge it after changing any
  of these, same as any other Customizer change.

Page Hero (Elementor Pages):
  On any Page, open the "Page Hero" box in the editor and pick a variant
  (Standard or Business) to print a full-width header band above the Elementor
  content, matching the design of the themed blog Posts and archives. Leave the
  variant on "None" and the Page is untouched. Requires the Page's Layout
  (Page Attributes) to be set to Elementor Full Width or Elementor Canvas; other
  layouts render no hero.

Maintenance mode (theme toggle):
  Add to wp-config.php (above the "stop editing" line):
      define( 'MET_HELLO_CHILD_MAINTENANCE', true );
  Logged-out visitors then see a styled 503 maintenance page; administrators keep
  browsing the live site. Remove the line (or set it to false) to go back online.
  You can also toggle it programmatically via the `met_hello_child_maintenance`
  filter. If LiteSpeed Cache is active, purge all after toggling.

Maintenance during WordPress updates (drop-in):
  Copy the bundled dropins/maintenance.php to wp-content/maintenance.php.
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

= 1.8.0 =
* Add: sitewide design token layer. `assets/css/tokens.css` (`:root` custom
  properties only) and `assets/css/elementor-base.css` (base rules scoped to
  Elementor's own class names) load on every page of the site, including the
  homepage and every Elementor Page, not just the theme's own styled views.
  `assets/css/theme.css` now depends on `tokens.css` instead of defining its
  own `:root` block, so there is one token source, not two. See DECISIONS D27.
* Fix: every Elementor Page (Full Width or Canvas template) rendered with no
  `<main>` landmark, since Hello Elementor's own header/footer templates
  never print one for Elementor's page templates. That also left the parent
  theme's skip link pointing at a target that did not exist. The child theme
  now wraps Elementor Page content in `<main id="content" class="site-main">`
  on both templates. No Elementor or parent theme file touched. See D27.
* Fix: a visible keyboard focus outline and reduced-motion support now apply
  on Elementor-rendered content, matching what the theme's own views already
  had. Typography and colour on Elementor-authored pages are set through
  Elementor's own Global Fonts and Global Colours (Site Settings), not this
  stylesheet: Elementor bakes its Kit defaults into CSS scoped to each
  widget, which always outranks a generic rule. See DECISIONS D27.

= 1.7.2 =
* Fix: Scroll to Top could go missing at some breakpoints (found: visible on
  laptop/desktop widths, not on phone/tablet). Cause: a `transform` on some
  ancestor element (commonly set by a mobile-menu slide animation) makes
  `position:fixed` position against that ancestor instead of the real screen.
  Fixed by moving the button to be a direct child of `<body>` at runtime via
  JavaScript, so no ancestor markup can trap it again, regardless of what
  built the header or at what breakpoint.

= 1.7.1 =
* Fix: Scroll to Top's Customizer colour could silently fail to show, two
  separate causes found and fixed.
  1) On sites running CSS optimisation (observed with LiteSpeed Cache's CSS
     Combine), the colour was set via a `:root` variable in a separate
     `<style>` tag, and such plugins are free to move or reorder
     `<link>`/`<style>` tags; if the button's own stylesheet default ended up
     printed after our override, the default won even though the correct
     value was present earlier in the source. Fixed by setting the colour as
     an inline `style` attribute directly on the button element, which no
     such tool touches.
  2) Independently, a generic `[type=button]` reset rule shipped by another
     plugin or the environment (an attribute selector, the same specificity
     tier as a class) could still beat our `.met-to-top` class selector on
     cascade order alone. Fixed by qualifying every selector with the
     element type (`button.met-to-top`), one specificity tier higher, so our
     rule wins regardless of load order or what else is on the page.

= 1.7.0 =
* Add a sitewide Scroll to Top button (inc/scroll-top.php,
  assets/css/scroll-top.css, assets/js/scroll-top.js), on by default on every
  page, hidden until the reader scrolls down about one screen. Configurable
  under Appearance > Customize > Scroll to Top: on/off, left or right, and an
  accent colour, all with a live preview.
* Self-contained: does not depend on assets/css/theme.css, since that file does
  not load on every page. Under 1KB of new JavaScript; the CSS is close to it.
  Turning the button off removes its CSS, JS, and markup entirely.

= 1.6.0 =
* Add Page Hero: an opt-in, editor-controlled header band for Elementor Pages
  (inc/page-hero.php, template-parts/page-hero.php), reusing the existing
  editorial design. Two variants, Standard (the shared .met-hero band) and
  Business (a taller hero with a tag and up to two buttons), chosen and filled
  in from a new "Page Hero" meta box. A Page with no variant set is unchanged.
  Renders on the Elementor Full Width and Canvas page layouts only.
* The design stylesheet and font preconnect hints now also load on a Page with
  a hero set; the full-width body class stays limited to the original blog
  Posts/archives/search/author/404 views, since Full Width Pages already render
  edge to edge.

= 1.5.0 =
* Restructure the theme to the standard WordPress layout. functions.php is now a
  bootstrap that loads six modules from inc/ (setup, updater, assets,
  template-tags, social, maintenance). No behaviour changed.
* Move the design CSS to assets/css/theme.css. style.css now holds the theme
  header only and is not enqueued.
* Move maintenance-template.php to template-parts/maintenance-page.php.
* Add phpcs.xml.dist (WordPress Coding Standards), composer.json, .editorconfig
  and .gitattributes.
* Exclude development files from the release zip.
* Add the missing dropins/maintenance.php that the installation notes told you
  to copy into wp-content/.
* Rename the remaining "Haraka" references to "MetCPT".

= 1.4.2 =
* Single post: add Threads, X, and Telegram share buttons (now X, Facebook,
  LinkedIn, WhatsApp, Telegram, Threads), built from a reusable helper.
* Single post: the Back button now points to the post's category archive
  (falls back to the Newsroom URL only when the post has no category).
* Single post: the author name now links to the author's archive page.

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
