# DEPLOY TO STAGING

How work done on local `v2` reaches https://v2.iiumholdings.com.my.

Last updated: 2026-08-10

**This procedure was executed in full for the first time on 2026-08-10** (the
1.7.2 to 1.11.1 deploy) and held up. The two things it did not originally warn
about, now folded in below: purge before judging a moved page empty, and
hardcoded page IDs like the `/business/` parent do not survive the move (fixed in
v1.11.1). Treat the walkthrough at the end as the tested version.

## The rule

**Never copy the database.** Staging already holds the real content: 42 pages,
the menu, Yoast fields, MetCPT posts, media. Local is a workshop holding only
the pages being built. Importing local over staging would either overwrite live
content or create duplicate slugs (`/sitemap-2/`), because both sites use the
same slugs.

Everything moves as **named items**, one at a time, each verified after it
lands.

## What moves, and how

| Thing | How it travels | Automatic? |
|---|---|---|
| Theme code (PHP, CSS, JS, `theme.json`) | git tag, then the release pipeline | Yes, after the tag |
| Elementor page bodies | Elementor template export and import, one page at a time | No |
| Images used by those pages | Uploaded to staging media first, then referenced | No |
| Menus and menu locations | Rebuilt by hand in Appearance, Menus | No |
| Customizer settings | Retyped by hand. They are `theme_mod` rows, not code | No |
| Page meta (Page Hero, `_met_sector`) | Retyped, or the backfill action in `inc/migration-tools.php` | Partly |
| Front page setting | Set by hand in Settings, Reading | No |

Only the first row is automated. Everything else is a deliberate manual step,
and that is the point.

---

## Part A: theme code

This is the safe, well-tested path. It has shipped eight times.

1. `git pull --ff-only origin main` on this machine.
2. Merge `feat/home-chrome` into `main`.
3. Bump the version in **both** `style.css` (the `Version:` header) and
   `MET_HELLO_CHILD_VERSION` in `functions.php`.
4. Add a `= X.Y.Z =` block to the changelog in `readme.txt`.
5. `vendor/bin/phpcs` clean.
6. Commit. **Push `main` first**, then `git tag vX.Y.Z && git push origin vX.Y.Z`.
7. `release.yml` builds the zip and publishes the GitHub Release.
8. On staging: Dashboard, Updates. The theme offers the new version. Update.
9. Purge LiteSpeed.

**Staging is on 1.7.2 and the next tag is 1.10.0.** That carries three versions
of change at once, including `theme.json`, `page.php` and the whole chrome. So:

- **Leave the chrome toggle OFF** on the first staging deploy. Appearance,
  Customize, Site Header & Footer. Off is the default, so a fresh install is
  already correct. Check the site looks exactly as it did before the update.
  Only then flip it on, check again, and untick if anything is wrong.
- Check `/whistleblowing/`, one `/business/` page and one blog post before and
  after. Those cover Page Hero, the business hero variant and the styled views.

**Rollback:** reinstall the previous release zip from GitHub, or untick the
chrome toggle if the problem is chrome only.

---

## Part B: an Elementor page

Do one page. Verify it. Then do the next. Do not batch.

### B1. Get the images onto staging first

This is the step that breaks if you skip it.

Most images on the built pages came from staging in the first place, so they
already sit in staging's media library under the same filename. Any image that
did not, upload to staging's media library by hand before importing.

To list what a local page references, in Novamira `execute-php`:

```php
$data = get_post_meta( 152, '_elementor_data', true );
preg_match_all( '#http://v2/wp-content/uploads/[^"\\\\]+#', $data, $m );
print_r( array_unique( $m[0] ) );
```

Check each filename exists in staging's media library. Upload the missing ones.

### B2. Export from local

1. Edit the page in Elementor.
2. Top-left hamburger, **Save as Template**. Name it after the page.
3. Templates, Saved Templates, **Export** the row. You get a `.json` file.

### B3. Rewrite the image URLs in the JSON

Open the `.json` in a text editor and replace every

```
http:\/\/v2\/wp-content\/uploads\/
```

with

```
https:\/\/v2.iiumholdings.com.my\/wp-content\/uploads\/
```

Note the escaped slashes. Elementor stores its data as JSON inside JSON, so the
slashes are backslash-escaped. Search for the escaped form, not the plain one.

Why: the JSON also carries local **attachment IDs**, which mean nothing on
staging. Elementor resolves an image by its URL when the ID does not match, so a
correct staging URL saves it. If you leave `http://v2/...` in there, staging
cannot reach it and every image renders broken.

### B4. Import on staging

1. Templates, Saved Templates, **Import Templates**, upload the `.json`.
2. Edit the **existing** staging page. Do not create a new one, or you get a
   duplicate slug.
3. In the widget panel, the folder icon, My Templates, **Insert** the template.
4. When asked **"Import documents settings?"** answer **Yes**. That carries the
   page-level settings: padding, page layout, background.
5. Set Page Hero to **None** on that page if the design carries its own hero.
   The 25th Anniversary and Group of Companies designs both do.
6. Update. Purge LiteSpeed. Clear Elementor CSS: Elementor, Tools, Regenerate
   CSS & Data.

### B5. Verify before moving on

Not curl. A browser, or the headless Chrome script (D43).

- Every image loads. This is the one that fails.
- No horizontal scroll at 390, 768, 1366, 1920.
- Exactly one `h1`.
- The page hero is not doubled (theme hero plus design hero).

**Rollback:** WordPress revisions. The page's previous content is one revision
back, and Elementor keeps its own revision history in the editor panel.

### Pages waiting to move

`/sitemap/` is built on local and needs moving, not building.
`/iium-holdings-25th-anniversary/` and `/iium-holdings-group-of-companies/`
are rebuilds of pages that already have content on staging, so those two
overwrite rather than fill.

---

## Part C: menus

The footer needs three new menus, for the locations `met-footer-company`,
`met-footer-governance` and `met-footer-business`. Menus do not travel with
code.

Build them by hand on staging in Appearance, Menus, then assign each to its
location in **Manage Locations**. The main menu (`menu-1`) already exists on
staging and is untouched.

Assigning a menu never switches the chrome on or off. That is deliberate
(D38).

---

## Part D: Customizer settings

These are `theme_mod` rows in the database. Nothing carries them. Retype them on
staging after the code lands.

| Section | Settings |
|---|---|
| Site Header & Footer | The chrome toggle. Leave off until Part A is verified |
| Site Identity | Site logo |
| Homepage | Four stat number and label pairs, plus three image IDs |
| Anniversary logo | Image and link, in the chrome section |
| Scroll to Top | Already set on staging, do not touch |

Media control values are **attachment IDs**, and staging's IDs differ from
local's. Pick the image again from staging's own library. Do not copy the
number.

---

## Part E: page meta and the front page

1. **Page Hero variant.** Any page whose design carries its own hero needs the
   variant set to **None**. Existing staging pages keep whatever they have.
2. **`_met_sector`.** The nine `/business/` pages need it. Run the one-time
   backfill in `inc/migration-tools.php` on staging, then spot-check three
   pages. The eyebrow fallback means they render correctly even before the
   backfill, so this is tidying, not a blocker.
3. **Front page.** Assign the **Homepage** template to the staging "Home" page
   in Page Attributes, then Settings, Reading, set it as the static front page.
   Do this **last**, after the chrome is on and verified. It is the single most
   visible change on the site.

---

## Order of operations

1. Part A, code, chrome off. Verify nothing changed.
2. Part C, footer menus.
3. Part D, Customizer, chrome still off.
4. Flip the chrome on. Verify header, footer, menu, drawer, keyboard.
5. Part B, one page at a time, verifying each.
6. Part E3, the front page. Verify.
7. Purge LiteSpeed after every step, not at the end.

## Housekeeping on staging, unrelated to any deploy

Two pages should be trashed or set to noindex. Both are in the sitemap and
neither exists on local:

- `/sample-page/`, the WordPress default.
- `/board-of-directors/naaimah-backup/`, a published duplicate of
  `puan-naaimah-binti-mat-ahmad-radzi/`.

## When WP-CLI arrives

If the host ever gives shell access, Parts B, D and E collapse into scripted
steps, and `inc/migration-tools.php` can be deleted (see D29). Until then this
is the procedure.

---

# Comprehensive walkthrough for the 2026-08-09 deploy (steps 6 to 8)

Written after v1.11.0 was released and the owner finished the theme update,
footer menus, Customizer, chrome toggle, categories and housekeeping. What
remains is the content: the posts, the Elementor page bodies, and the front page.

The one fact that shapes all of it: **staging cannot reach the local site.**
`http://v2` is not on the internet, so any importer that tries to pull an
attachment from a local URL fails. Media therefore moves by hand.

## Step 6: move the content posts

Eight posts were authored on local and are not on staging. Recreate each on
staging by hand. Eight posts is small enough that hand-entry is more reliable
than any importer, and it sidesteps the attachment problem entirely.

| Local ID | Title | Category | Featured image file | Facebook album |
|---|---|---|---|---|
| 329 | IIUM Holdings Expands Healthcare Collaboration to Support Pharmaceutical and Medical Services | press-releases | ikop-radiant-scaled.jpg | - |
| 327 | IIUM Medical Specialist Centre Strengthens Integrity Commitment Through Corruption-Free Pledge Initiative | press-releases | IMSC-terajui-budaya-bebas-rasuah.webp | - |
| 324 | IIUM Holdings Conducts Integrity Awareness Programme to Promote Ethical Workplace Practices Across the Group | press-releases | ikrar-bebas-rasuah-iium-holdings-scaled.jpg | - |
| 339 | IIUM Gombak Iftar Pack: IIUM Holdings Group Sponsors SHAS Mosque | csr | IIUM-Gombak-Iftar-Pack.jpg | - |
| 336 | Kasih Ramadan: IIUM Holdings Distributes Iftar Packs to Students and Community | csr | Pek-berbuka-puasa-IIUM-Holdings.jpg | - |
| 333 | Sumbangan Duit Raya: IIUM Holdings Santuni Pelajar Asnaf UIAM dan IIC | csr | sumbangan-duit-raya.webp | - |
| 321 | Integrity Day 1.0 at IMSC Gallery | gallery | IMSC-terajui-budaya-bebas-rasuah.webp | facebook.com/share/p/19AKmLXSRv/ |
| 319 | Kenduri Durian at IMSC Gallery | gallery | makan-durian.webp | facebook.com/share/p/19KqGNcXnA/ |

For each post:

1. On **local** `http://v2`, open the post in the editor. Copy the body text.
   From the Media Library, download its featured image (note the filename in the
   table). Post 321 shares 327's image, so download it once.
2. On **staging**, Posts, Add New. Paste the title and the body.
3. Set the **Category** in the sidebar to the one in the table. The category must
   already exist (done in step 5).
4. **Featured image**, Set featured image, upload the file you downloaded.
5. For the two **gallery** posts only, paste the Facebook link into the
   **External album** box (the field the theme adds on the Post edit screen).
6. Publish.

Also check the News and Announcement content. That page lists every category, so
it shows whatever posts staging already holds. If staging already has the
announcement and activity posts, nothing to do. Recreate on staging, the same
way, any that exist only on local.

## Step 7: move the Elementor page bodies

Same export and import mechanism for every page, but the pages split into two
groups by whether they carry baked images.

### The mechanism, one page at a time

1. On **local**, open the page with Elementor. Top-left menu (the three lines),
   **Save as Template**, name it after the page.
2. Templates, Saved Templates, **Export** that row. You get a `.json` file.
3. On **staging**, Templates, Saved Templates, **Import Templates**, upload the
   `.json`.
4. Edit the **existing** staging page with Elementor. Do not create a new page,
   or you get a duplicate slug.
5. In the widget panel, the folder icon (My Templates), **Insert** the template.
   When asked **"Import documents settings?"**, answer **Yes**.
6. Update. Purge LiteSpeed. Elementor, Tools, **Regenerate CSS & Data**.
7. Open the page in a browser and check it.

### Group A: no baked images, import and done

These pages carry no images in their layout. Their pictures come from the posts
at render time, or they are text and icons only. Nothing to rewrite.

`/news-announcement/`, `/media/`, `/gallery/`, `/press-releases/`,
`/csr-initiatives/`, `/sitemap/`.

Two conditions for these to fill correctly, both already handled if steps 5 and 6
are done first: the category must exist, and the posts must exist. The
`[met_posts]` shortcode filters by slug, so it just works once the content is on
staging. **Leave Page Hero as it is (standard)** on all six; these keep the
theme hero.

### Group B: baked images, import then fix the images

`/iium-holdings-25th-anniversary/`, `/iium-holdings-group-of-companies/`,
`/rise2030-strategy-blueprint/`. The first three moved in the 2026-08-10 deploy.

**Still to move, built 2026-08-11 and never deployed:** `/business/` (the
division landing page) and all nine subsidiary child pages (IIUM Higher
Education, IIUM Schools, IIUM Educare, IIUM Consultancy and Innovation, Daya
Bersih, IIUM Advanced Technologies, IIUM Properties, IIUM Medical Specialist
Centre, IKOP Pharma). Two extra things for these ten:

- **The nine subsidiary pages need Page Hero set to None**, same as step 1
  below. Their design hero is the first Elementor section and prints the `h1`.
  Leaving Page Hero on gives the page two `h1` tags and two stacked headers.
- **The `/business/` landing page has nine placeholder `#` links.** Point each
  `Explore` button at the matching subsidiary page after the children are in.

Do the import mechanism above, then:

1. **Set Page Hero to None** on each. These three designs carry their own hero,
   so the theme hero must be switched off in the Page Hero meta box.
2. **Fix the images.** After inserting the template, some images will be broken,
   because the local filenames do not always match staging. Walk the page in
   Elementor, and for each broken image or background, click it and pick the
   image from staging's Media Library. If it is not there, download it from the
   local Media Library and upload it to staging first.
3. **RISE2030 needs two uploads.** Its hero and closing backgrounds
   (`rise2030-hero.jpg`, `rise2030-close.jpg`) exist only on local. Upload both
   to staging, then set them as the section backgrounds on the hero and the
   closing band.
4. Group of Companies also uses `met-vision-pattern.svg` at the uploads root as a
   faint background pattern. If it is missing on staging, upload it; if the
   pattern does not show, the page is still fine.

Image counts, so you know what to expect: anniversary about 14, group about 10
(nine company logos plus the pattern), RISE2030 two.

## Step 8: set the front page

The homepage is a theme Page Template, not an Elementor page, so it does not move
through Elementor at all. On staging:

1. Pick or create the Page that will be home (a plain Page, no Elementor).
2. Page Attributes, **Template**, choose **Homepage**.
3. Set that page's **Page Hero to None** (the homepage template renders its own
   hero).
4. Settings, **Reading**, "A static page", set **Front page** to that page.
5. Purge LiteSpeed. Open the site root and check.

The homepage pulls its content from things already on staging: the announcements
category, recent posts, the nine `/business/` child pages for the companies grid,
and the stats and images from the Customizer (step 3). No hero slides exist yet,
so the hero shows its static fallback, which is expected.

The companies grid colour-codes each company from its `_met_sector` meta and
falls back to reading the Page Hero eyebrow, so the nine pages show the right
colour even before any backfill. Running the one-time sector backfill in
`inc/migration-tools.php` on staging is optional tidying, not required.

## Do it in this order

1. Step 6, the posts, first. The listing pages need them.
2. Step 7 Group A, the shortcode pages.
3. Step 7 Group B, the image pages.
4. Step 8, the front page, last. It is the most visible change.
5. Purge LiteSpeed after each page, and do a browser check on each.
