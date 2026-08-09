# PRD: content listing pages (v1.11.0)

Status: Phase 1 built and verified on local 2026-08-09 (v1.11.0). Phase 2 waits
on content. Owner decisions taken 2026-08-09, recorded in section 2. Build added
one file beyond the list in section 5: `template-parts/listing-card.php`, the
per-card markup the shortcode loops over.

## 1. Context

Five published pages on live staging show a Page Hero and nothing else:
`/media/`, `/news-announcement/`, `/gallery/`, `/csr-initiatives/`,
`/press-releases/`. All five already carry `_met_hero_variant = standard` with
an eyebrow and subtitle, so the header is done. They need bodies.

These are Pages, not archives. The theme already has archive templates
(`archive.php`, `search.php`, `author.php`) for category, tag, date, search and
author views. This work is about landing pages that a visitor reaches from the
main menu.

**The real blocker is content, not templates.** Measured on local, 2026-08-09:

| Category | Published posts |
|---|---|
| `announcements` | 6 |
| `am` | 7 |
| `activities` | 2 |
| `uncategorized` | 1 |
| `news` | 0 |
| `csr` | 0 |
| press releases | category does not exist |

16 published posts, 58 image attachments. Three of the five pages have nothing
to list. So this PRD builds the mechanism once and then only the pages that have
something to show.

### What the tools can and cannot do

Established by reading the plugin directories, not by assumption.

- **Elementor free has no dynamic post-listing widget.** Posts, Portfolio,
  Archive Posts and Loop Grid are all Pro. The widget files are absent.
- Elementor free **does** have Shortcode, Image Gallery and Image Carousel.
- Essential Addons **free** has Post Grid, Post Timeline and Filterable Gallery.
  Post Block and Post Carousel are Pro.
- Ultimate Addons free has Basic Posts.
- MetCPT already registers `news_grid` and `category_posts` for standard posts.
- This theme registers **no shortcodes at all** today.

## 2. Decisions

### D-a. Listings render from a theme shortcode inside an Elementor page

The page body is built in Elementor from real widgets, as
[D41](../DOCS/DECISIONS.md#d41) requires. Where a list of posts belongs, the
page holds one Elementor **Shortcode** widget containing `[met_posts …]`. The
theme renders the list with its own markup and `theme.json` tokens.

Why not Essential Addons Post Grid, which needs no code:

1. **It does not survive the move to staging.** Elementor query controls store
   **term IDs**. Local term IDs are not staging term IDs, so an imported page
   lists the wrong posts or none, and every structural check passes. This is the
   same class of failure as the attachment IDs in
   [D42](../DOCS/DECISIONS.md#d42) and it is documented in
   [DEPLOY-TO-STAGING.md](../DOCS/DEPLOY-TO-STAGING.md). A shortcode written as
   `category="announcements"` is a **slug**, and slugs match on both sites.
2. **The design would live in widget settings, not in a stylesheet.** Essential
   Addons ships coral `#f56a6a` and orange `#ff622a` defaults that bleed into
   any control left alone; D35 records 22 colour controls needing explicit
   values on a single page. Multiply that by five pages and every future token
   change becomes a manual re-edit of every page.
3. **One idiom, not two.** MetCPT already delivers listings on this site as
   shortcodes, and its CSS loader already detects a shortcode sitting inside
   `_elementor_data`. Adding a second mechanism would leave the site with two.

Why not MetCPT's existing `news_grid`: its `.mcpt-v2` scope defines its own
`--mcpt-*` variables, reads no theme tokens, and `style-events.css` still
imports Google Fonts, which contradicts D15 and D28. It would look off-brand,
and theme presentation does not belong in the plugin repo.

Why not five PHP page templates: the owner could no longer change these pages in
Elementor, and every layout tweak would become a code change and a release.

**Cost, stated plainly:** the listing itself is not visually editable in
Elementor. It is configured by shortcode attributes. That is the trade accepted
in exchange for portability and one-place styling.

### D-b. All news-shaped content stays on the standard `post` type

Press releases, CSR items, news, announcements and gallery albums are all the
same shape: title, date, featured image, body. They are separated by
**category**, not by post type. They then share `single.php`, the archive
templates, RSS, Yoast and the search index for free.

New categories to create: `press-releases`, `gallery`. `csr` already exists.

No new CPT. A CPT is justified when the fields differ, and they do not. If one
ever does, it belongs in MetCPT, which owns public content types
([D39](../DOCS/DECISIONS.md#d39)).

### D-c. Gallery albums live on Facebook; WordPress holds the cover and the link

**Owner's practice, three years running, and it is the right call.** Uploading
hundreds of high-resolution event photos to the media library filled the hosting
disk in year one. Since then each event gets a Facebook album, and the website
carries one post pointing at it.

This becomes a first-class feature instead of a hand-typed link:

- Each album is a post in the `gallery` category with a featured image as the
  cover and a short description.
- A new post meta key `_met_album_url` holds the external album URL.
- `single.php` renders a styled "View the full album" button when it is set.
- The `album` listing layout marks those cards with an external-link glyph.

**The album card links to the post, not straight to Facebook.** The post is
indexable content on your own domain, it carries the description and the share
row, and it matches what you already do. Sending the card directly to Facebook
is a one-attribute change if you decide otherwise later.

**No new image sizes.** The theme already generates three (`met-poster`,
`met-card`, `met-thumb`) on top of core's. Adding more would work against the
disk-space reason this decision exists. Listings reuse `met-card` and
`met-thumb`.

### D-d. These five pages keep the theme's Page Hero

Unlike the 25th Anniversary and Group of Companies pages, whose approved designs
carried their own heroes and were set to `None`, these five keep
`_met_hero_variant = standard`. The Elementor body starts below the theme hero.
That is [D30](../DOCS/DECISIONS.md#d30) and it is not reopened.

### D-e. Scope: mechanism first, then the pages that have content

Phase 1 ships the shortcode, its stylesheet, the album meta, and the two pages
that can be filled today. Phase 2 waits on content.

## 3. Phase 1: build now

### 3.1 `inc/listing.php` — the shortcode

Registers `[met_posts]`. Follows the file conventions already in `inc/`:
`ABSPATH` guard, `@package MetHelloElementorChild` docblock, every function
prefixed `met_hello_child_`.

Attributes, all optional:

| Attribute | Default | Meaning |
|---|---|---|
| `category` | `''` | Comma-separated **slugs**. Empty means all. |
| `exclude_category` | `''` | Comma-separated slugs to leave out. |
| `count` | `9` | Posts to show. `-1` for all. |
| `layout` | `grid` | `grid`, `list` or `album`. |
| `columns` | `3` | `2`, `3` or `4`. Ignored by `list`. |
| `featured` | `no` | `yes` renders the first post large, the newsroom treatment. |
| `paged` | `no` | `yes` honours the `paged` query var and prints pagination. |
| `empty` | `''` | Overrides the empty-state sentence. |

Rules:

- Unknown slugs resolve to nothing rather than to "all posts". Silently
  returning the whole blog when a slug is misspelled is the worst failure here.
- `shortcode_atts()` for defaults, then sanitise each value: slugs through
  `sanitize_title`, `count` and `columns` cast to int and clamped, `layout`
  checked against a whitelist, booleans through a yes/no test.
- Query with `WP_Query`, `post_status => publish`, `ignore_sticky_posts => true`,
  `no_found_rows => true` unless `paged="yes"`.
- Always `wp_reset_postdata()`.
- Return a string. Never echo.
- Empty result returns `<p class="met-empty-note">…</p>`, matching the homepage
  partials' empty states, never an empty container.

Reuse `met_hello_child_get_primary_term()` and `met_hello_child_reading_time()`
from `inc/template-tags.php:18` and `:33`.

**Do not reuse `template-parts/met-card.php`.** It takes no arguments, reads the
loop globals, and its styles live in `theme.css`, which uses the `tokens.css`
alias layer (`--space-6`, `--gold`). This shortcode reads `theme.json` custom
properties directly, the way `home.css` does. Mixing the two token layers is how
[D36](../DOCS/DECISIONS.md#d36) happened. Keep the layers apart.

### 3.2 `assets/css/listing.css`

Everything scoped under `.met-list`, so it cannot touch `.met-view`, `.met-page`,
`.met-home` or `.elementor`. Class idiom and token usage copied from
`assets/css/home.css`: `met-` prefix, BEM-ish elements, `is-` states, mobile
first with `min-width` queries, and `var(--wp--preset--*)` /
`var(--wp--custom--*)` **always with a literal fallback** (D36).

Three layouts:

- `grid` — cards at `met-card` size, 16:10 media, eyebrow, title, date, excerpt.
- `list` — `met-thumb` size, horizontal row, no excerpt.
- `album` — cover-forward card, larger media, title and date, external-link
  glyph when `_met_album_url` is set.

Column counts are a modifier, `.met-list--cols-3`, resolving to
`repeat(auto-fill, minmax(260px, 1fr))` at the low end and fixed steps above,
matching `home.css` lines 704-737 and 799.

### 3.3 Conditional enqueue

Add `met_hello_child_page_has_shortcode( $tag )` to `inc/listing.php`. It checks
`has_shortcode( $post->post_content, $tag )` **and**, separately, whether
`'[' . $tag` appears in the `_elementor_data` post meta, returning true if
either matches. This mirrors MetCPT's `metcpt_page_has_shortcode()` at
`includes/core/helpers.php:21`, which exists for exactly this reason, but avoids
its bug: MetCPT's Elementor branch returns unconditionally, so a third detection
path there would be unreachable.

Enqueue on `wp_enqueue_scripts`, gated on that test, with dependencies
`array( 'hello-elementor', 'hello-elementor-theme-style' )`. That dependency
array is not optional. Omitting it is what caused the hamburger and pink-border
bug, because the parent's `reset.css` styles bare `a` and `button` at equal
specificity and wins on source order. See STATE.md.

Handle: `met-hello-child-listing`. No JavaScript in Phase 1.

### 3.4 `inc/albums.php` — the external album link

One post meta key, `_met_album_url`, following the pattern of
`met_hello_child_page_hero_fields()` in `inc/page-hero.php:44-54`.

- Meta box "External album" on the `post` edit screen, one URL field, with a
  line of help text naming Facebook as the usual target.
- Sanitise with `esc_url_raw()` restricted to `http` and `https`.
- Nonce, `current_user_can( 'edit_post' )` check, autosave guard, the same shape
  as the Page Hero save handler.
- Accessor `met_hello_child_get_album_url( $post_id = null )` returning `''`
  when unset.

### 3.5 `single.php` — the album button

One insertion after `the_content()` and `wp_link_pages()`, inside
`.post-body__inner` at [single.php:117](../single.php#L117). Renders only when
`met_hello_child_get_album_url()` is non-empty:

```
<p class="post-album">
  <a class="met-btn met-btn--primary post-album__link"
     href="…" target="_blank" rel="noopener noreferrer">
    View the full album
    <span class="screen-reader-text">(opens on Facebook)</span>
  </a>
</p>
```

Styles go in `assets/css/theme.css` alongside the other `.met-single` rules,
because `single.php` is a styled view and already loads that file. This is the
one place the two token layers legitimately meet, and it stays on the
`theme.css` side of the line.

Nothing else in `single.php` changes.

### 3.6 Categories

Create `press-releases` and `gallery` as categories on local. `csr` exists and
is reused. No code: these are content, created in the admin, and they must be
created again on staging with the same slugs.

### 3.7 `/news-announcement/`, page 170

Built in Elementor from native widgets. Page Hero stays `standard`; the eyebrow
and subtitle are already set and the body starts below it.

Body, top to bottom:

1. A section holding one Shortcode widget:
   `[met_posts featured="yes" count="9" columns="3" paged="yes"]`
   All categories, so nothing is hidden by a slug the owner forgot to add. Each
   card shows its category as the eyebrow.
2. A closing band linking to `/press-releases/` and `/gallery/`.

**Open, low cost either way:** the `am` category holds 7 of the 16 posts and
reads as thought-leadership articles rather than company news. They are included
by default. If they should not appear here, add
`exclude_category="am"` to the shortcode. That is editing one line in one
widget, no rebuild.

### 3.8 `/media/`, page 171

A hub, not a listing. Media is a dropdown in the main menu, and this is its
landing page.

Built in Elementor from native widgets: a three-card grid linking to News &
Announcements, Press Releases and Gallery, each with an icon, a heading, a line
of description and a link. Below it, a media-enquiries block pointing at
`/contact-us/`.

No shortcode on this page. Optionally one `[met_posts count="3" layout="list"]`
strip showing the three most recent items, decided during the build once the
three cards are on screen.

## 4. Phase 2: waits on content

Not built now. Each is one Elementor page plus one shortcode, an afternoon each
once there is something to list.

| Page | Waiting on | Shortcode when ready |
|---|---|---|
| `/press-releases/` | first press release post | `[met_posts category="press-releases" count="12" paged="yes"]` |
| `/gallery/` | first album posts | `[met_posts category="gallery" layout="album" columns="3" count="12" paged="yes"]` |
| `/csr-initiatives/` | owner's decision on static sections vs a feed | static Elementor sections, optionally `[met_posts category="csr"]` at the foot |

`/sitemap/` is already built in Elementor on local (page 163, 14.8 KB of
`_elementor_data`). It needs **moving, not building**. See
[DEPLOY-TO-STAGING.md](../DOCS/DEPLOY-TO-STAGING.md) Part B.

## 5. Files

**New**

- `inc/listing.php`
- `inc/albums.php`
- `assets/css/listing.css`

**Modified**

| File | Change |
|---|---|
| `functions.php` | Version to `1.11.0`; two `require_once` after `inc/homepage.php`, in the order `listing`, `albums` |
| `single.php` | The album button block in section 3.5, nothing else |
| `assets/css/theme.css` | `.post-album` rules only |
| `style.css`, `readme.txt` | Version and changelog |
| `DOCS/STATE.md`, `DOCS/DECISIONS.md`, `DOCS/PROJECT_LOG.md` | D44 to D46 and a log entry |

**Do not touch:** `inc/setup.php`, `inc/assets.php`, `inc/scroll-top.php`,
`inc/homepage.php`, `inc/chrome.php`, `template-parts/met-card.php`,
`archive.php`, `tokens.css`, `patterns.css`, `home.css`, `chrome.css`,
`theme.json`, any parent or plugin file.

## 6. Verification

Per section, not at the end.

1. `vendor/bin/phpcs` clean.
2. **Shortcode unit checks** on a scratch page, before either real page is
   built: no attributes; a valid slug; a misspelled slug (must return the empty
   state, not every post); `count="-1"`; each of the three layouts; each column
   count; `paged="yes"` with pagination clicked through; `featured="yes"` with
   exactly one post in the result.
3. **Escaping.** Every attribute value reaches the output escaped. Confirm by
   passing `category='"><script>'` and reading the source.
4. **Headless Chrome screenshot** of both pages at 390, 768, 1366 and 1920, per
   [D43](../DOCS/DECISIONS.md#d43). Not curl.
5. `scrollWidth` against `clientWidth` at all four widths. Count `h1` elements:
   exactly one, from the Page Hero.
6. Album button: set `_met_album_url` on one post, confirm the button renders,
   opens in a new tab, and that the post without it renders no empty gap.
7. Novamira `check-design` on both pages. The four sector colours warn; that is
   expected and fine.
8. Confirm `listing.css` does **not** load on a page with no shortcode, and
   **does** load when the shortcode sits inside `_elementor_data` rather than
   `post_content`.
9. Confirm the Page Hero still renders on both pages and that the homepage,
   `/whistleblowing/` and a blog post are unchanged.

## 7. Decisions to record

- **D44**: listings are a theme shortcode inside an Elementor page, not an
  addon widget. Term IDs do not survive the move to staging; slugs do.
- **D45**: news, press releases, CSR and gallery albums are all the standard
  `post` type separated by category. No new CPT.
- **D46**: gallery albums are hosted on Facebook, with WordPress holding the
  cover image, the description and `_met_album_url`. Written down because it is
  a deliberate hosting-cost decision, not an oversight, and because a future
  session would otherwise "fix" it by uploading the images.
