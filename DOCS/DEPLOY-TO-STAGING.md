# DEPLOY TO STAGING

How work done on local `v2` reaches https://v2.iiumholdings.com.my.

Last updated: 2026-08-09

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
