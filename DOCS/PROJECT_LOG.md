# PROJECT LOG

What was built and when. Newest first.

**Reading this file.** Newest first. Read the top entry whole, then stop,
unless the task is about older history. Everything before v1.9.0 is in
[archive/PROJECT_LOG-2026.md](archive/PROJECT_LOG-2026.md); there is an index
of it at the bottom of this file, so you can tell whether you need to open it
without opening it.

**Archived 2026-08-07**, at 712 lines, down to 209. See
[DECISIONS D23](DECISIONS.md#d23), which was amended the same day: the
original rule said to archive entries older than the current year, and every
entry was from the current year, so it could never fire. It now archives by
version era.

**Writing here:** log as work happens, newest at the top. The provenance note
for v1.0.0 to v1.4.2, which were built on the home machine with no synced
transcripts, moved to the archive with those entries.

---

## 2026-08-11: homepage rebuilt in Elementor, v1.12.0 (local)

Group MD/CEO feedback after the 2026-08-11 presentation, in ten numbered
points, all implemented. Full plan and the options put to the owner:
[PLAN/PRD-homepage-elementor.md](../PLAN/PRD-homepage-elementor.md). Decisions:
[D47](DECISIONS.md#d47), [D48](DECISIONS.md#d48), [D49](DECISIONS.md#d49).

**Homepage is now an Elementor page** (Page 156, template "Elementor Full
Width"), not the v1.10.0 Page Template. Editorial furniture, eyebrows,
headings, descriptions, buttons, is Elementor widgets the owner can edit
directly. The four sections already approved by the Group MD/CEO, hero
slider, announcement cards, portfolio gallery, newsroom list, render from
the exact same code as before through a new shortcode bridge
(`inc/home-shortcodes.php`), not rebuilt. Two new shortcodes,
`[met_tenders]` and `[met_careers]`, read MetCPT's post types and meta keys
without modifying MetCPT. `[met_companies]` takes `order`/`exclude`
attributes; the homepage now lists Education (4), then Facilities (Daya
Bersih, IIUM Properties, with IIUM Advanced Technologies excluded), then
Healthcare (2), the exact sequence requested.

**Old build untouched, and is the rollback.**
`page-templates/template-homepage.php`, `inc/homepage.php`, and every
partial in `template-parts/home/` are unmodified; re-selecting the Homepage
template on Page 156 renders them exactly as before.

**One real defect found and fixed mid-build**: `home.css` and `home.js` are
entirely scoped under one `.met-home` ancestor that no longer existed once
the page stopped using the Page Template. Fixed with one `the_content` filter
that wraps Elementor's rendered output in the same element, so neither file
needed any change.

**Footer can now move to Elementor** (Header Footer Elementor, bundled with
UAE), a second Customizer toggle independent of the header, which stays
theme code because the owner is happy with the menu and it is the riskier
half to rebuild. The Elementor footer sits on `surface` `#F7F3EC` instead of
petrol, so the logo reads, the actual complaint. Cost real debugging time:
the plugin's `ehf_template_type` meta must read the literal string
`type_footer`, not `footer`, or the footer silently never renders with no
visible error. Building it also surfaced a genuine, pre-existing menu bug:
`wp_update_nav_menu_item()` resets every unspecified field to its default,
so renaming one menu item's title without also passing its existing parent
ID and URL silently promoted "Infrastructure" from a Business submenu column
into its own top-level nav item. Caught by screenshot, not by the rename
logic itself, and fixed by restoring the item's parent, URL and a
deterministic re-numbering of its siblings.

**Hero slides gained a per-slide headline size field** (28-72px, empty is
unchanged), so a long programme or event name can be sized down instead of
covering the photo. The CSS floor had to become `min(2.2rem, the custom
max)` rather than a bare `2.2rem`, because `clamp()`'s floor always wins
over its ceiling once the floor is the larger number.

**Infrastructure renamed to Facilities, slug included**, per direct owner
instruction (not a label-only change). Code, the `theme.json` colour token,
and the three affected Pages' saved sector meta were renamed with a
reversible migration action. The harder part was content no grep-for-code
catches: three Facilities subsidiary pages' hero eyebrows, the `/business/`
landing page's division band, one hero slide's own body text, three Yoast
meta descriptions, and one main-menu item, all found by querying the
database directly rather than trusting a code-only sweep.

Verified: `phpcs` clean theme-wide; Novamira `check-design` returns `ok`
with only the two expected sector-colour warnings and "elevate" (the owner's
own RISE2030 wording, not invented copy); no horizontal scroll and exactly
one `h1` at 390/768/1366/1920; the four unchanged sections
screenshot-compared against a pre-change baseline. Version bumped to
1.12.0. Built on branch `feat/home-elementor`, not yet committed, not
deployed.

---

## 2026-08-11: all nine /business/ subsidiary pages built (local)

Built every subsidiary child page from its owner-approved design file, in three
batches by division. All nine are local only, not deployed.

| Page | ID | Sections | Accent |
|---|---|---|---|
| IIUM Higher Education | 178 | 9 | Education `#4A7BA8` |
| IIUM Schools | 179 | 9 | Education |
| IIUM Educare | 180 | 10 | Education |
| IIUM Consultancy and Innovation | 181 | 9 | Education |
| Daya Bersih | 182 | 10 | Infrastructure `#B98A2E` |
| IIUM Advanced Technologies | 183 | 5 | Infrastructure |
| IIUM Properties | 184 | 5 | Infrastructure |
| IIUM Medical Specialist Centre | 185 | 8 | Healthcare `#B5566F` |
| IKOP Pharma | 186 | 9 | Healthcare |

One shared section grammar across all nine, so the set reads as one system:
hero, a coloured stat or fact band, an about split, then division-specific
middle sections, and a closing CTA where the design has one. The two
Infrastructure pages with the least content (IAT, Properties) carry a
label-and-value fact band instead of numeric stats, and no CTA, because their
approved designs have none. Divisional accent is the only colour that changes.

**Page Hero is off on all nine.** This is the one departure from
[D30](DECISIONS.md#d30) and it was the owner's correction mid-build: the
instruction to "neglect the header" meant the site chrome, not the design's own
hero band, which is content. `_met_hero_variant` is set to None on each page and
the design hero is the first Elementor section, so each page still has exactly
one h1. **On staging these nine must also be set to None**, or the old band
returns above the new hero.

Built with a reusable PHP helper library in the Novamira sandbox
(`met-subsidiary-builder.php`, disabled between batches) rather than repeating
widget arrays nine times. Native Elementor throughout, with one exception: the
IKOP certification chip row is a single small HTML fragment, because Elementor
free containers will not hug their content into inline pills. That is the D41
"only what the plugins cannot do" case, same as the /business/ image stacks.

Three fixes found by screenshot, not by markup: Font Awesome icon values need
the `fas ` style prefix or Elementor prints PHP warnings into the card; the
partner flags rendered blank as container backgrounds and had to become image
widgets; and one downloaded flag was a corrupt 579-byte file that had to be
re-imported. Verified on every page at 390/768/1366: no horizontal overflow,
exactly one h1, images loading.

All are Group B (image-heavy) for the next deploy. Stock photography is
placeholder and can be swapped on staging.

---

## 2026-08-10: /business/ division landing page built (local)

Built the `/business/` landing page (page 172, previously empty) from the
owner-approved `business-content.html`. Intro, three division bands (Education,
Infrastructure, Healthcare) each with a gradient, a translucent count and a
short blurb, then nine alternating company feature rows, and a closing photo
band with a CTA to `/contact-us/`. Page Hero kept standard; the design's intro
headline is an h2 so the page keeps one h1.

Native Elementor throughout (containers, headings, text, image, button, icon)
except the nine overlapping image stacks, which use one small HTML widget each:
a large photo, a small photo overlapping the corner with a white border, and a
big index number behind. That composition needs absolute positioning Elementor
free cannot express cleanly, so it is the D41 "only what the plugins cannot do"
case. Alternating sides use flex `row` / `row-reverse` with
`flex_direction_mobile: column`, so media leads on mobile and the sides swap on
desktop.

Owner decisions: all nine `Explore` links are placeholder `#` (the owner points
them at the subsidiary pages after import), and the small overlapping photo
reuses each subsidiary page's existing featured image rather than importing
Unsplash stock. Ten images imported (nine company photos from the old
production site, one Unsplash closing background); the nine logos and nine
featured photos were already in the library.

Verified at 390/768/1366/1920: no overflow, one h1, images load (the full-page
capture showed blanks below the fold, the D43 lazy-load trap; a scrolled
viewport capture confirmed every row renders). `check-design` returns no
failures, only the expected off-palette warning for the brand and division
accent colours. Built on local only; it is a Group B (image-heavy) page for the
next deploy.

---

## 2026-08-10: first full deploy to staging, complete and verified

Staging went live with the whole revamp. This was the first time any of the
1.8.0-to-1.11.1 work reached v2.iiumholdings.com.my; the site had been on 1.7.2.

What was done, in order, by the owner on the live wp-admin (the agent cannot
reach staging: Novamira is local only and HTTPS to staging is blocked from the
shell, so the whole staging side was owner-executed from a written guide):

1. Updated the theme to 1.11.1 with the chrome toggle off, confirmed nothing
   changed, then turned chrome on and verified header, footer, menu, drawer.
2. Built the three footer menus and assigned their locations.
3. Set the Customizer values (logo, homepage stats and images, anniversary logo).
4. Recreated the `press-releases` and `gallery` categories with matching slugs.
5. Moved the content posts (news, press releases, CSR, gallery albums) with their
   categories, featured images, and album URLs.
6. Moved every Elementor page via Save as Template, export, import onto the
   existing page. Re-picked images on the image-heavy pages; uploaded RISE2030's
   two backgrounds, which were local only.
7. Set the homepage: assigned the Homepage template to a page, Page Hero None,
   and made it the static front page.
8. Housekeeping: cleared `/sample-page/` and the `naaimah-backup` duplicate.

Every page was checked one by one and approved. The site was presented to the
Group MD/CEO on 2026-08-11.

**Two things caught during the deploy, worth keeping:**

- The listing pages looked empty at first. The posts were correct (the category
  archive proved it); the pages were serving stale LiteSpeed cache from before
  the posts existed. Purge plus Regenerate Elementor CSS fixed it. Lesson: after
  moving content, always purge before judging a page empty.
- The homepage companies grid was empty because of the hardcoded `/business/` ID.
  Fixed and released as v1.11.1 mid-deploy (entry below).

The step-by-step procedure, now executed once, is in
[DEPLOY-TO-STAGING.md](DEPLOY-TO-STAGING.md), including the comprehensive
walkthrough for the posts, pages and front page.

---

## 2026-08-09: v1.11.1 hotfix, /business/ parent resolved by slug

While deploying, the homepage companies grid came up empty on staging. Cause:
`met_hello_child_get_companies()` and the sector backfill hardcoded
`post_parent => 172`, the /business/ page ID on local. On staging that page is
ID 33 (confirmed via the public REST API). Added
`met_hello_child_business_parent_id()` in `inc/sectors.php`, which resolves the
parent by slug (`get_page_by_path('business')`) and is filterable; both callers
now use it. Same class of bug as the term IDs (D44) and attachment IDs (D42):
hardcoded local IDs do not survive the move. Released as v1.11.1.

---

## 2026-08-09: v1.11.0 released (code)

Committed the folded 1.9.0, 1.10.0 and 1.11.0 work to `main` (commit 5e19747),
tagged `v1.11.0`, and pushed. The release Action built and published the theme
zip (451 KB), so the update is now offered on any site running this theme.
phpcs was clean across all 45 files before the commit; `.mcp.json` stays
gitignored and was verified out of the commit.

This ships **code only**. The staging site (v2.iiumholdings.com.my) still runs
1.7.2 until the owner applies the update on the live dashboard and then moves
the content, menus, Customizer settings and front page by hand, per
[DEPLOY-TO-STAGING.md](DEPLOY-TO-STAGING.md). The chrome toggle defaults off, so
the theme update alone changes nothing on staging until it is switched on. The
Novamira MCP is local only and HTTPS to staging is blocked from the shell, so
none of the staging-side steps can be automated from here.

---

## 2026-08-09: RISE2030 strategy page built in Elementor

Built `/rise2030-strategy-blueprint/` (page 160) from the owner-approved design
(`rise2030-content.html`), choosing Hero Option A and Objectives Layout 1. Six
sections: photo hero with the RISE2030 wordmark, About + a 2x2 KPI band, the
four-thrust framework with all ten strategic objectives nested under R/I/S/E,
divisional targets for the four divisions, five group-initiative cards, and a
closing band. Page Hero set to None, since the design carries its own hero.

Built from native Elementor containers and widgets with theme.json hex baked per
widget, per [D41](DECISIONS.md#d41). HTML widgets used only where the plugins
cannot express the design: the two-tone headings (RISE2030, and the
REALISE/IMPROVE/SECURE/ELEVATE thrust titles) and the four coloured letter
badges. Everything else, including the nested objective grids with hairline
dividers, the KPI and divisional grids, and the initiative icon cards, is native.
The two background photos were imported into the media library, never hotlinked
([D42](DECISIONS.md#d42)).

**One Elementor flex quirk cost a fix:** container children default to
`flex-grow: 1`, so the 56px letter badges stretched into wide bars inside their
flex-row header. Setting `_flex_grow: 0` on the child did not take, so the badges
were converted to fixed 56px HTML squares. Worth remembering for any fixed-size
box inside a flex-row container.

Verified at 390/768/1366/1920: no horizontal overflow, exactly one h1 (the hero),
no broken images, badges square, two-tone titles correct. Novamira `check-design`
returns no failures, two expected warnings: "elevate" (the actual name of Thrust
4 in the R.I.S.E. framework, not filler) and off-palette colours (the approved
design's petrol/gold/sand brand values plus the four intentional thrust and
division accents, the same reason the sector colours warn). With this, the only
remaining page designs are Board of Directors, Management Team, and the nine
`/business/` subsidiary pages.

---

## 2026-08-09: content listing system (v1.11.0), Media and News pages built

Built the mechanism for the site's list-of-posts pages, then the two pages that
have content today. The three that do not (press releases, gallery, CSR) wait on
content; each is one Elementor page plus one shortcode when ready. Full plan:
[PLAN/PRD-content-pages.md](../PLAN/PRD-content-pages.md).

**The `[met_posts]` shortcode** (`inc/listing.php`) renders a token-styled list
of Posts filtered by category **slug**, dropped into an Elementor Shortcode
widget on an otherwise native page. The reason it is a shortcode and not an
Essential Addons Post Grid is the move to staging: addon query controls store
category term IDs, which differ between local and staging, so an imported page
would list the wrong posts while passing every check. A slug is identical on both
sites. See [D44](DECISIONS.md#d44). Attributes: `category`, `exclude_category`,
`count`, `layout` (grid/list/album), `columns`, `featured`, `paged`, `empty`.
Card markup is `template-parts/listing-card.php`; styles are
`assets/css/listing.css`, scoped under `.met-list`, reading `theme.json`
properties directly. The stylesheet loads only when the page actually uses the
shortcode, detected in both `post_content` and `_elementor_data`.

**Content model.** News, press releases, CSR and gallery are all the standard
`post` type, separated by category, not new post types ([D45](DECISIONS.md#d45)).
Created two categories, `press-releases` and `gallery`; `csr` already existed.

**Gallery albums stay on Facebook.** A gallery Post holds a cover and a
description; the photos live in a Facebook album, linked by a new `_met_album_url`
meta key (`inc/albums.php`). `single.php` renders a "View the full album" button
when it is set. This is the owner's three-year practice, kept deliberately to
avoid refilling the hosting disk; it is written down as [D46](DECISIONS.md#d46)
so a later session does not "fix" it by uploading the images. No new image size
was added.

**Two pages built** in Elementor from native widgets, both keeping the theme's
standard Page Hero above the body:

| Page | ID | Body |
|---|---|---|
| `/news-announcement/` | 170 | `[met_posts featured="yes" count="9" columns="3" paged="yes"]` plus a dark CTA band to Press Releases and Gallery |
| `/media/` | 171 | A three-card hub (News, Press Releases, Gallery) plus a Media enquiries CTA to Contact us. No shortcode, so `listing.css` correctly does not load here |

**Verification.** phpcs clean across the new and changed PHP. Ten shortcode unit
checks including a misspelled slug and a `'"><script>` injection, both of which
return the empty state rather than every post or raw markup. Headless Chrome at
390/768/1366/1920 on both pages: no horizontal overflow, exactly one `h1`, nine
cards with nine images and zero broken on News, chrome responsive with the drawer
at mobile. The two image-less posts render the sector-tinted fallback, not a
broken image. Album button confirmed on a single post, then the test meta
removed. Novamira `check-design` returns zero violations.

**Theme code this release:** new `inc/listing.php`, `inc/albums.php`,
`template-parts/listing-card.php`, `assets/css/listing.css`; `single.php` gained
the album button; `theme.css` gained `.post-album` rules; version to 1.11.0.
Nothing committed or tagged.

**Follow-ups the same day, after owner review:**

- `/gallery/` (page 168) built once the owner created two `gallery` album posts,
  using `[met_posts category="gallery" layout="album" columns="3" paged="yes"]`.
  Verified clean at 390/768/1366: no overflow, one h1, two album cards with the
  out-link badge, zero broken images.
- Fixed a stale-primary-term bug in `met_hello_child_get_primary_term()`
  (`inc/template-tags.php`). It honoured Yoast's `_yoast_wpseo_primary_category`
  meta without checking the post is still in that term. Yoast does not clear that
  meta when a post's categories change, so a post moved off "Uncategorized" still
  showed "Uncategorized" on its card and single. Added a `has_term()` guard. Found
  by the owner on one post; a scan showed it was the only affected post, but the
  fix is generic. This is old shipped code, not new to 1.11.0.
- Fixed a gap in `listing.css`: the column-count rules were written for
  `.met-list--grid` only, so `layout="album"` would have rendered single-column.
  Album now shares the same responsive column grid.
- `/press-releases/` (page 169) and `/csr-initiatives/` (page 167) built once the
  owner added three posts to each category, both `[met_posts columns="3"
  paged="yes"]`. The redundant section heading was dropped from gallery, press
  releases and CSR: the Page Hero already titles the page. Their stale Page Hero
  subtitles ("There are no press releases at this time", "This page is currently
  being updated") were replaced now that the pages have content. All three
  verified at 390/768/1366/1920: no overflow, one h1, correct card counts, zero
  broken images. With this, all six formerly-empty staging pages are built on
  local and only need moving.

---

## 2026-08-09: two pages rebuilt in Elementor, and the build method corrected

**The day started with a rejected build.** The 25th Anniversary page was first
produced by pasting the approved design file's markup, `<style>` block included,
into a single Elementor `html` widget. Its absolutely positioned sections fought
Elementor's flex layout and the page collapsed: a huge blank band, the hero
squashed into a narrow column, five sections missing. It had been reported as
verified after checking only the HTML source. The owner rejected it immediately
and was right to. See [D41](DECISIONS.md#d41).

**Both pages were then rebuilt from real widgets.**

| Page | ID | Widgets | Containers | HTML widgets |
|---|---|---|---|---|
| `/iium-holdings-25th-anniversary/` | 161 | 163 | 101 | 0 |
| `/iium-holdings-group-of-companies/` | 152 | 103 | 82 | 0 |

Both use `heading`, `text-editor`, `image`, `button`, `divider` and `icon`, with
`theme.json` hex values baked per widget. Both have Page Hero set to None because
each design carries its own hero. On Group of Companies the owner asked for the
nine companies to be grouped by division in menu order rather than filtered, so
the filter buttons were dropped in favour of three labelled groups with the same
coloured dots as the mega menu.

**A screenshot tool now exists**, and it changed the work. Headless Chrome driven
over the DevTools Protocol from Node, full-page capture at a real viewport. Every
defect below was found by looking at a picture; none were visible in the markup.
See [D43](DECISIONS.md#d43).

**Four Elementor defects, each of which had shipped looking fine to curl:**

1. Grid containers default to `repeat(2, 1fr)` rows, so every single-row grid
   reserved an empty row underneath. On Group of Companies that was about 370px
   of dead space per grid; fixing it cut the page from 7650px to 4833px.
   Deleting the setting is not enough, it falls back to the default. Set
   `grid_rows_grid` to custom `auto` on every breakpoint.
2. Background overlay opacity defaults to 0.5, so the gradient scrims over the
   hero and the four era headers rendered at half strength and the text was
   unreadable.
3. Company logos had no width constraint. CSS grid items do not shrink below
   their content, so a 1024px logo pushed the page to 1516px wide at a 1366
   viewport.
4. `fa-shield-halved` is a Font Awesome 6 name and this Elementor bundles FA 5.
   It printed four PHP warnings into a mission card.

**All 24 images were hotlinked from staging and would have rendered blank.**
Cloudflare hotlink protection returns 200 to a server fetch but blocks a browser
sending a cross-origin referer, so every structural check passed while the
emblem was visibly broken. All images are now imported into the local media
library and both pages have zero external image references.
See [D42](DECISIONS.md#d42).

**Theme code changed once today:** `scroll-padding-top` added to `chrome.css`, so
the sticky header stops covering in-page anchor targets.

**The deployment procedure was written down**, in
[DEPLOY-TO-STAGING.md](DEPLOY-TO-STAGING.md). Code ships through the existing
release pipeline; Elementor pages, media, menus, Customizer settings and page
meta move as named items by hand. The database is never copied, because local
and staging share slugs and a plain import would either overwrite live content
or create `-2` duplicates. The step most likely to fail is images: a page
exported from local carries `http://v2` URLs and local attachment IDs, both
meaningless on staging, so the exported JSON needs its image URLs rewritten
before import.

---

## 2026-08-08: v1.10.0 built, homepage and custom site chrome

Built the homepage and a custom header/footer on branch `feat/home-chrome`, from
the owner-approved design. All seven planned steps done in one pass, each still
independently revertible.

**Homepage** is a Page Template (`page-templates/template-homepage.php`,
[D37](DECISIONS.md#d37)), assigned to Page 156 and set as the front page. Nine
section partials in `template-parts/home/`, styled by `assets/css/home.css`,
behaviour in `assets/js/home.js`, with the view test, three `add_image_size`
sizes, the data helpers and a "Homepage" Customizer section (four stats plus the
About image) in `inc/homepage.php`. The design was mapped onto `theme.json`
tokens with no new fonts: Geist bold for headings, Instrument Serif for numerals
only. Every image is a real `<img>` with explicit dimensions; missing images
render sector-tinted empty states from the existing pattern token, so there are
no broken images and no CSS background images. First hero image is
`fetchpriority="high"`; everything else is lazy.

**Site chrome** ships behind a Customizer toggle, `met_hello_child_chrome_enabled`,
default off ([D38](DECISIONS.md#d38)). Header (`header.php`), footer
(`footer.php`) and their parts fall through to the parent theme when off, so the
switch is a one-click rollback; confirmed byte-identical revert on the homepage,
an Elementor Page and a Post. The mega/dropdown menu is built structurally from
the assigned menu by `Met_Hello_Child_Nav_Walker`, with no hardcoded titles:
against the live menu it yields Business = mega (three columns), About Us and
Media = dropdowns, the rest flat. The drawer renders the same menu again through
`Met_Hello_Child_Drawer_Walker` as `<details>` accordions that work with no JS.
Yoast output in `wp_head()` is untouched; view-source shows one doctype, one
`</head>`, one `wp_footer`, and exactly one scroll-to-top button (still owned by
`inc/scroll-top.php`).

**Hero slides** are `met_hero_slide`, a non-public CPT ([D39](DECISIONS.md#d39));
**sector** is `_met_sector` Page meta ([D40](DECISIONS.md#d40)), and all nine
`/business/` Pages were backfilled from their eyebrow. The **Page Hero** variant
control became an explicit None / Standard / Business radio, default None, with
no change to how existing heroes render.

`phpcs` is clean across all 24 new and changed files. Novamira `check-design`
returns one warn only, the two sector colours, which are canonical `theme.json`
palette tokens the design snapshot did not capture. Nothing committed or tagged.
The chrome toggle is left on locally so the owner can review the full design.

**Owner review, and the bug that took four rounds.** The owner reviewed and
listed twelve items. Most were straightforward: the Site Identity logo and a new
25th Anniversary logo with its own link in the header, drawer and footer;
optional background images for the Tenders, Careers and About bands; a
per-slide vertical focal point for hero images with the recommended size noted
in the editor; and spacing and colour corrections.

One item resisted three attempted fixes. The mobile hamburger stayed visible at
full laptop width. The media query was correct, the file was served correctly,
the braces balanced, the query was not nested, and the stylesheet was linked
once. Lowering the breakpoint twice changed nothing. A temporary on-screen probe
settled it: the query was matching (`true`) while the button still computed
`display: block`. The parent theme's `reset.css` was printing **after** the
child's `chrome.css`, and its
`[type=button], button { border: 1px solid #c36; display: inline-block }` ties
with `.met-menu-btn` on specificity, so it won on source order. The owner's
screenshot is what cracked it: the pink `#c36` borders on exactly the `<button>`
elements pointed at a stylesheet styling bare elements.

The same cause was behind the earlier run of "this text is the wrong colour"
fixes, since that reset also sets `a { color: #c36 }`. Those had been patched one
selector at a time. The real fix is one line per enqueue: declare
`array( 'hello-elementor', 'hello-elementor-theme-style' )` as dependencies, as
`inc/assets.php` already did for `theme.css`. Recorded in STATE.md so the next
person reads load order before reaching for specificity.

A related hero bug had the same shape: `.met-home img { height: auto }` (0,1,1)
outranked `.met-hero-home__bg { height: 100% }` (0,1,0), so the hero image sized
itself and left a gap as the window narrowed. Fixed by scoping all fill images
under `.met-home` in one block.

**Deferred by the owner:** Elementor requests Inter and Roboto from Google on
every page, from its Kit typography, which contradicts D15 and D28. Not changed,
because the four built Elementor Pages use those fonts and switching them needs a
visual review. See STATE.md.

---

## 2026-08-08: direction change back to Elementor, Novamira MCP, 42 pages, and a token bug that broke every hero

**The day's headline: the block-system migration was abandoned for page bodies.**
Owner reviewed the block-built `/whistleblowing/` and chose to rebuild it in
Elementor instead, using the design system rather than fighting it. Pages are
now built with Elementor free plus Essential Addons and UAE, governed by
`theme.json` tokens. This does not revive D27's failed approach: the tokens are
applied per widget at build time, not pushed at Elementor from a stylesheet.
See [D35](DECISIONS.md#d35).

**Novamira installed** (`wp-content/plugins/novamira`, v1.11.2), an MCP server
giving the agent PHP execution and filesystem access on local. Dev only, never
staging. Two real blockers were found and fixed: Local's nginx omits
`HTTP_AUTHORIZATION` from its `fastcgi_param` list, so bearer tokens never
reached PHP (fixed in `conf/nginx/site.conf.hbs`, backup kept); and the MCP
client had to use the `mcp/novamira-oauth` endpoint, not the
Application-Password-only `mcp/novamira`. Connected via an Application Password
in a gitignored `.mcp.json`. This retires the curl-and-nonce scripting and
satisfies D29's condition for deleting `inc/migration-tools.php` eventually.

**A design system was captured and activated** in Novamira as
`iium-holdings-corporate`, synthesised from `theme.json` rather than invented,
so `check-design` pre-flight now runs against the real tokens. Every page below
passed it with zero violations.

**Four pages built in Elementor**, all verified rendering, all pre-flight clean:
`/whistleblowing/` (rebuilt), `/iium-holdings-group-of-companies/`,
`/contact-us/` (WPForms 197 embedded via the native shortcode widget, form
confirmed rendering 5 fields), and `/sitemap/`.

**42 pages now exist**, matching the live staging sitemap. 40 were created in
two batches with title, Page Hero eyebrow and subtitle, WordPress excerpt and
three Yoast fields each; `/careers/`, `/tenders/` and `/events/` carry the
MetCPT shortcodes. Parent/child hierarchy verified: 9 under `/business/`, 9
under `/board-of-directors/`. `naaimah-backup` skipped as a duplicate.

**The main menu was restructured**, 28 placeholder custom links converted to
real `post_type` page links. Only 4 remain custom, and correctly so: About Us,
Education, Infrastructure and Healthcare have no page behind them and act as
non-linking headers.

**The bug worth remembering.** `tokens.css` referenced
`--wp--preset--font-size--2xl` and `--3xl`. WordPress does not generate those
names: a slug starting with a digit gets a hyphen inserted, so the real
properties are `--2-xl` and `--3-xl`. Both aliases resolved to nothing, so
`font-size` was invalid and the browser fell back to its default. That silently
shrank **every Page Hero title on all 16 pages** and **every `h2` in every blog
post**. The owner caught it by eye; no structural check could, because a broken
`var()` still renders text, just at the wrong size. Fixed in `tokens.css` and
then found again in `patterns.css` (3 more rules). Both now carry literal
fallbacks so a future slug rename degrades visibly instead of silently.
See [D36](DECISIONS.md#d36).

**Two documentation errors corrected.** `patterns.css` claimed in a comment that
headings are bold sans; the file contains no such rule, and `theme.json` sets
headings to serif. That wrong comment is what drove an earlier "fix" to the
whistleblowing page. And `PRD-block-system.md` §3.5.2 says there is no header
and footer builder installed, but Header Footer Elementor is active (currently
with no templates assigned, so the parent theme renders the chrome).

**Planned, not built: v1.10.0 homepage and site chrome.** Full plan agreed with
the owner. See STATE.md "Open items" for the decisions and scope.

---

## 2026-08-07: v1.9.0 code, canonical design system and page.php, phase 0 of the block-system migration

Direction changed from the 2026-08-04 plan. Option A (govern Elementor from a
token layer, shipped as 1.8.0's code) hit its ceiling: D27's same-day
correction found Elementor bakes its Kit defaults into per-widget CSS that
always outranks a generic stylesheet rule, so typography and colour on
Elementor pages could not be reached from `elementor-base.css` at all.
Superseded by [PLAN/PRD-block-system.md](../PLAN/PRD-block-system.md): move
page content into the block editor, governed by `theme.json`, migrated in
batches, Elementor retired last. Full reasoning and the four rounds of scope
discussion (MetCPT shortcodes, staging's real plugin list, Page Hero and
Scroll to Top conflicts, the design-file corpus) are in that PRD and
[PLAN/PRD-design-system.md](../PLAN/PRD-design-system.md).

Found while reading the 40 design files for the token reconciliation: they are
not 21 different systems as first estimated, but two, System A (32 files) and
System B (8 files, a later revision), with 11 tokens conflicting between them
and identical file membership on every one. Full findings, the contrast audit,
and every locked value in
[PLAN/DESIGN-SYSTEM-DECISIONS.md](../PLAN/DESIGN-SYSTEM-DECISIONS.md). Reviewed
by the owner as a live, interactive gallery,
[PLAN/DESIGN-SYSTEM-GALLERY.html](../PLAN/DESIGN-SYSTEM-GALLERY.html), approved
2026-08-07 with no exceptions raised.

Shipped in code, verified on local `v2`, phpcs clean (20 files, 0 errors):

- `theme.json`: the canonical token set, colour (with a display/text split on
  the accent and each sector colour, since the brighter display shade fails
  WCAG AA as text), type scale, spacing, section rhythm, layout widths,
  elevation, radius, z-index, motion, focus. `color.custom`,
  `defaultPalette`, `customFontSize` and `customSpacingSize` all off, so the
  block editor offers only this system.
- Geist and Instrument Serif self-hosted (`assets/fonts/`), declared as
  `theme.json` font faces. Downloaded from Google's own CDN response (the
  `latin` subset, a single variable file for Geist covering all six weights)
  and verified as valid WOFF2 before committing. Closes the D15 TODO. The
  Google Fonts enqueue and its preconnect hints are removed from
  `inc/assets.php`. `dropins/maintenance.php`, `error-403.php` and
  `inc/maintenance.php` still load from the CDN, unchanged: they run outside
  a booted WordPress (D7) and cannot read `theme.json`. Known, deliberate,
  same pattern as the D27 Inter-vs-Geist gap.
- `tokens.css` rewritten as an alias layer: every existing flat name now
  points at the matching `theme.json` custom property, so `theme.css`,
  `scroll-top.css` and `elementor-base.css` needed no rewrite.
- `page.php` added. Hello Elementor ships none, so every Page fell through to
  the template written for blog posts: a duplicate `<h1>` next to Page Hero's
  own, an open comment form on corporate pages (Site Health confirms comments
  are open by default), and a page that would have been squeezed into the
  parent's 1140px blog-post width the moment it stopped being an Elementor
  page. One file fixes all three: `id="content"` kept for the skip link,
  `class="site-main"` dropped so the parent's width selector cannot match,
  Page Hero called directly (its existing static guard already prevents a
  double print), and a visible fallback `<h1>` on any Page with no hero
  variant set, so a page is never left with zero headings either.
- Contrast sweep of `theme.css`. Two confirmed bugs from the design-system PRD
  (the eyebrow label, in-body post links, both display gold used as small
  text) plus every other matching case found while checking: card title
  hover, pagination hover and current-page state, the search field focus
  ring, post-back hover, the 404 ghost button. The global focus-visible ring
  needed a genuine split, not a blanket swap: the darker accent-text value
  that fixes the light-background cases computes to 2.19:1 on the dark hero
  bands, worse than the original. Fixed with a base rule plus a dark-context
  override, verified by computing both directions rather than assuming one
  fix covers both. The post-body link hover reused the darker text colour,
  which would have collapsed hover and default to the same shade, so it
  reuses the existing, already-approved `--petrol` token instead of inventing
  an unreviewed new colour.
- `style.css`, `functions.php`, `readme.txt` version bumped to 1.9.0.

Phase 0a done the same day. The local site turned out to be reachable at
`http://v2` (found via the hosts file; `localhost` and `github-test.local`,
tried first, were both wrong). With owner-supplied admin credentials, logged
in via `wp-login.php` and installed and activated Essential Addons for
Elementor and Ultimate Addons for Elementor (UAE) through the ordinary
nonce-protected wp-admin screens, scripted with curl, no WP-CLI. Versions
6.7.2 and 2.9.2, an exact match to staging. Confirmed no fatal error on the
homepage, a sample Page, or the plugins screen afterward.

Phase 1 started the same day: `/whistle-blowing/` (post 86) migrated end to
end and confirmed live on local `v2`. Built `inc/migration-tools.php`, a
temporary `manage_options`-gated tool (admin-post actions, no WP-CLI) to flip
the meta a migration needs, and `assets/css/patterns.css`, the first
block-content component styles scoped under `.met-page`. Content built from
`whistleblowing.html` (structure) and the live staging page (copy, already
read in an earlier session, matched what the design file had). Pushed to the
Page via the REST API, authenticated with the owner-supplied admin
credentials.

Two real bugs found and fixed by reading the actual rendered response after
each change, not by assuming the code was correct:

1. The PRD's migration mechanism (section 4.5) was incomplete. Clearing
   `_elementor_edit_mode` alone was not enough: that meta only governs
   Elementor's own admin UI, not front-end template selection, which reads
   the separate `_wp_page_template` post meta. Left at
   `elementor_header_footer`, the Page fell through past `page.php` to
   `index.php`'s generic fallback, rendering with the wrong `<main>` class
   and the exact width bug page.php exists to prevent. Fixed:
   `inc/migration-tools.php` now clears both keys and backs up the original
   template value under `_met_migration_original_template` for a clean
   rollback.
2. `page.php` never called `get_header()` or `get_footer()`, so the page
   rendered as a bare fragment: no `<head>`, no enqueued styles, no site
   chrome, confirmed by an empty grep for every stylesheet handle that should
   have been there. Both calls added.

Verified after the fix: full valid document, all five stylesheet handles
present including the new `patterns.css`, `theme.json` global styles present
(`--wp--preset--color--accent` found), `<main id="content" class="met-page">`
exactly once, one `<h1>` ("Whistle Blowing", from Page Hero), the hero
subtitle "Speak up. We're listening.", every body section present (contact
channel, checklist, assurance band, three process steps), zero Elementor
markup, zero comment form, zero fatal errors, Scroll to Top still present.
All checked structurally via curl and grep.

**Then the owner opened it in a browser, and it was wrong twice.** This is
the part of the day worth keeping.

**Round 2, real body-content bugs.** All shipped as "verified" by the
structural checks above, all visibly wrong:

- Section headings used Instrument Serif. This design system sets headings in
  **bold sans**, and reserves the serif for two specific accents (a step
  number, a blockquote). Backwards.
- The "What you can report" two-column grid (text left, checklist right) had
  been collapsed to one column.
- The phone and email icon tiles in the contact cards were missing entirely.
- `.eyebrow` labels had **no styling at all**. `theme.css` scopes that rule
  under `.met-view`, and a block-authored Page carries `.met-page`, so the
  rule never reached them. Fixed by an unscoped copy in `patterns.css`.
- Several content groups carried WordPress `layout` types
  (`is-layout-constrained`, `is-layout-flex`), which generate their own width
  and display CSS, competing with the explicit grid rules in `patterns.css`.
  Removed from the block markup; `blockGap` turned off in `theme.json` for
  the same reason.

Also learned, and worth remembering for every remaining page: **WordPress
strips raw `<svg>` from `post_content` on save** (`wp_kses_post`), so an icon
authored into page content silently disappears. Icons are now painted as CSS
`background-image` data URIs in `patterns.css`, which sidesteps it entirely.
The first cut had used emoji as placeholders, which rendered in full colour
and looked worse than nothing.

**Round 3, the design misjudgement, not a bug.** The top of the page had been
rebuilt as a plain light intro, faithfully reproducing `whistleblowing.html`'s
own opening section, in place of Page Hero's petrol band. Owner correction:
Page Hero is the site's fixed header, already deployed on 16 Pages precisely
so no page's header differs from any other's. A design file is a full-page
mockup and carries its own top-of-page treatment; reproducing each one would
hand the site 30 different headers, which is the inconsistency this project
exists to remove. Reverted, and recorded as [D30](DECISIONS.md#d30): **design
files supply body content only, Page Hero is never redesigned per page.**

Net: `/whistleblowing/` is live and correct on local `v2`. It took three
rounds and two owner screenshots. The other 9 pages in batch 1, phase 0b, and
phases 2-4 remain undone.

**The lesson, stated plainly because it will apply to all 29 remaining pages:
structural verification is not visual verification.** curl and grep confirmed
every round above as correct, including both wrong ones. Only a browser
caught them. Pages should not be built in a batch without a screenshot
between each one.

### Documentation and repo housekeeping, same day

Docs brought in line with what was actually built and decided:

- **[D28](DECISIONS.md#d28), [D29](DECISIONS.md#d29), [D30](DECISIONS.md#d30)**
  added. D30 is the owner rule above: Page Hero is the fixed header, design
  files supply body content only.
- **[PRD-block-system.md](../PLAN/PRD-block-system.md) corrected** where the
  plan itself was wrong: section 4.5's one-meta-key migration, and section
  3.7.6's `page.php` sketch with no `get_header()`. Both wrong versions left
  visible as corrections rather than quietly rewritten, since each is the
  plausible assumption the next person would also make.
- **Superseded markers** on `PRD-design-tokens.md`,
  `PROPOSAL-frontend-revamp.md` and `STAGING-CHECKLIST-1.8.0.md`, each saying
  what in it is still valid rather than just "outdated".
- **Five broken internal links fixed.** `#d2`, `#d4`, `#d7`, `#d15` and `#d23`
  were linked to but had no anchors. Every `#dNN` reference across all docs
  now resolves.
- **`PROJECT_LOG.md` archived**, 712 lines to 209. See D23 below.
- **`STATE.md` condensed** from a growing narrative back to a short status
  file, which is what it is for. Detail moved here and to DECISIONS.

Two real repo problems found while checking the archive would not ship in the
release zip:

1. **`PLAN/` was shipping to every site that installs the theme.** 228 KB of
   internal PRDs and the design gallery, in the distributed package. Against
   [D21](DECISIONS.md), which says dev files stay out of the zip via
   `.gitattributes` and the `release.yml` exclude list; `PLAN/` was simply
   missing from both, and had been since it was created in 1.6.0. Added to
   both, along with `composer.lock`, which was in one list but not the other.
2. **Three empty junk files in the repo root**, named `C:UsersIIUM`,
   `Holdings.claudeclaude-notify-signalspermission` and
   `...signalsstop`. A notify hook is writing to a Windows path with the
   backslashes stripped, so the whole path becomes one filename. All three
   were empty and untracked; removed, and `.gitignore` patterns added so a
   `git add -A` cannot commit the next ones. **The root cause is in the hook
   config, outside this repo, and is not fixed.** Worth fixing there, or they
   keep appearing.

The `.gitignore` pattern for the first one needed care: Windows forbids `:` in
filenames, so the character in that name is U+F03A, a colon lookalike. A
pattern with an ASCII colon does not match it, and the obvious broad
alternative (`/C*`) would have swallowed `CLAUDE.md`. The committed pattern
uses the real U+F03A byte, verified against a decoy file and confirmed not to
catch `CLAUDE.md`.

---

## Older entries

Everything before v1.9.0, which is v1.0.0 through v1.8.0 and covers 2026-07-07
to 2026-08-04, is in
[archive/PROJECT_LOG-2026.md](archive/PROJECT_LOG-2026.md). Twenty entries,
moved unedited on 2026-08-07.

Quick index of what is in there, so you can tell whether you need to open it:

| Entry | What it covers |
|---|---|
| 2026-08-04 | v1.8.0 sitewide token layer, Elementor `<main>` landmark fix, and the same-day correction that Elementor's generated CSS beats a generic base layer ([D27](DECISIONS.md#d27)) |
| 2026-08-03 | v1.6.0 Page Hero, v1.7.0 Scroll to Top, v1.7.1 and v1.7.2 fixes, first phpcs run |
| 2026-08-01 | v1.5.0 restructure to the standard theme layout, project docs added |
| 2026-07-29 | Cross-check against the MetCPT plugin |
| 2026-07-07 | v1.4.0 GitHub auto-updates and first public release, v1.4.1, v1.4.2 |
| v1.0.0 to v1.3.0 | Original build: scaffold, single post, archives, search, author, 404 |
