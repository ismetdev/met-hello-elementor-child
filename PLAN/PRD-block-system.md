# PRD: block system, and retiring Elementor

Status: for approval, not approved
Author: Ismet, with Claude
Date: 2026-08-07
Supersedes the direction in [PROPOSAL-frontend-revamp.md](PROPOSAL-frontend-revamp.md)
section 4, which recommended Option A now and Option C later. Option A shipped as
v1.8.0 and hit its ceiling. This document is Option C.

## 1. Goal

Move the site's design system out of Elementor widgets and into `theme.json`, so
one token change reaches every page, staff can edit and build pages without
breaking the design, and Elementor can be removed.

Priority order, unchanged from the proposal:

1. Performance
2. Beauty of the UI design
3. Design consistency
4. Responsiveness
5. Accessibility and polish

## 2. Why Option A was not enough

Recorded in [DECISIONS D27](../DOCS/DECISIONS.md#d27), found by testing, not by
guessing. Elementor generates a CSS file per page and bakes the Kit's typography
default into a rule scoped to that exact widget's element ID, three class
selectors deep. That beats any generic stylesheet rule on plain specificity,
regardless of load order, whether or not the widget was customised.

So `elementor-base.css` can reach focus outlines, reduced motion, box-sizing and
overflow safety. It cannot reach typography or colour, which is the whole visible
problem. The fallback was Elementor Global Fonts, and that hit a second wall:
Geist is not in Elementor Free's font list, so the site settled for Inter.

A stylesheet cannot govern Elementor content. That is the finding. The only way
to govern the content is to own it.

## 3. Decisions taken, 2026-08-07

| Question | Answer |
|---|---|
| Architecture | Block system: `theme.json` plus block patterns, content in `post_content` |
| Who edits after launch | Ismet, staff, and Claude. Staff edit content, so typed HTML editing is ruled out |
| Rollout | Batches of 10 pages. Ismet picks each batch |
| Elementor exit | Page bodies first, header and footer last |
| Rejected | HTML blob in wp-admin, WPCode snippets, and the section-library-with-meta-fields middle step. All three either require staff to edit HTML or build a field system `theme.json` already provides |

## 3.5 Environment, read from Site Health on 2026-08-07

Owner-supplied. This section is the factual base for the risks and steps below.

### 3.5.1 Staging is behind local, and missing two plugins local never had

| | Local `v2` | Live staging | Note |
|---|---|---|---|
| Theme | 1.8.0 | **1.7.2** | The token layer and the Elementor `<main>` fix never reached staging |
| Elementor | 4.1.4 | 4.2.1 | |
| Essential Addons | **not installed** | 6.7.2 | |
| Ultimate Addons (UAE) | **not installed** | 2.9.2 | |
| WordPress | 7.0.2 | 7.0.2 | 7.0.3 available |
| PHP | to check | 8.1.34 | |

**This breaks the migration test.** Step 9 says compare a migrated page against
the Elementor original at four widths. A page built with Essential Addons or UAE
widgets will not render on local, because neither plugin is there. Fix before
phase 1: install both free plugins on local so it mirrors staging. Comparing
against live staging instead is not good enough, since the point is to check the
new page before it goes live.

### 3.5.2 The header and footer: cheaper than assumed, one thing to confirm

**Corrected 2026-08-08 by checking the running site.** The original text below
was wrong on the facts and is kept visible because the conclusion it reached
happened to be right for the wrong reason.

> ~~The plugin list contains **no header and footer builder**. Elementor's Theme
> Builder is a Pro feature and staging runs Elementor free. So the chrome is almost
> certainly **Hello Elementor 3.4.9's own header and footer**, configured in the
> Customizer, which is parent theme PHP, not an Elementor design.~~

**Header Footer Elementor (HFE) IS installed and active.** It is a free plugin
and does not need Elementor Pro. What saves phase 4 is that **no HFE template is
currently assigned**: `get_hfe_header_id()` and `get_hfe_footer_id()` both return
false, so `hfe_header_enabled()` is false and HFE hooks nothing. The parent Hello
Elementor templates render the chrome today, so the conclusion stands.

The risk this hid: if an HFE template is ever assigned,
`HFE_Default_Compat::override_header()` runs `remove_all_actions('wp_head')` and
output-buffers-then-discards `locate_template('header.php')`, so a child
`header.php` would silently render nothing. v1.10.0 therefore adds
`hfe_header_enabled` / `hfe_footer_enabled` filters as insurance. See
[D38](../DOCS/DECISIONS.md#d38).

Phase 4 is being brought forward as v1.10.0, ahead of the remaining page work.

### 3.5.3 WPForms Lite survives Elementor removal

Contact forms run on WPForms Lite 2.0.0.2, with WP Mail SMTP for delivery.
Neither depends on Elementor and WPForms ships a native block. Forms carry over
with no work. This was an open question in section 7 of the proposal and is now
closed.

### 3.5.4 A host-level performance bug, unrelated to this project

Site Health reports the PHP opcode cache is **full**: 128 MB of 128 MB used, 100%
of the 8 MB interned strings buffer used, 0 bytes free, and a hit rate of
**58.91%**.

A healthy opcache runs above 95%. At 58.91% PHP is recompiling source on most
requests because it cannot cache it. That is server time added to every single
page load, before WordPress renders anything, and it will not be fixed by any
change in this repo.

Ask the host to raise `opcache.memory_consumption` and
`opcache.interned_strings_buffer`. This is separate from the PRD and worth doing
this week, because it may account for part of the measured mobile loss that the
2026-08-04 baseline attributed to CSS and images.

### 3.5.5 Other configuration to fix on staging

| Setting | Now | Should be | Why |
|---|---|---|---|
| `max input variables` | 1000 | 3000+ | The block editor and Elementor both post large forms. At 1000 a big page saves silently truncated |
| `WP_MEMORY_LIMIT` | 40M | 128M | The floor for front-end requests. The server allows 256M |
| `WP_DEBUG` | Enabled | Disabled | Debug logging on production writes to `debug.log` on every notice. Display is already off, so nothing leaks, but it costs disk and time |
| `WP_ENVIRONMENT_TYPE` | Undefined | `staging` | Undefined means WordPress assumes production. Setting it correctly changes how plugins behave and is one line in `wp-config.php` |
| `PHP time limit` | 30s | 120s for admin | Long saves and imports can hit it |
| WordPress | 7.0.2 | 7.0.3 | |
| LiteSpeed Cache | 7.8.1 | 7.9 | |

The Font Library directory not existing is not a problem for this plan. Fonts
ship inside the theme at `assets/fonts/` and are declared in `theme.json`, so the
`uploads/fonts` directory is never used.

### 3.5.6 MetTranslate is active but not yet in use, and that is a deadline

MetTranslate 0.9.1 is active on staging with no translations entered.

**This is the cheapest moment this migration will ever have.** Translations
attach to page content. Migrate the 30 pages now and nothing is lost, because
nothing exists. Enter translations first and every migrated page orphans its
translation, turning a one meta field rollback into a re-translation job.

Do not start using MetTranslate on the ~30 Pages until phase 3 is done.

## 3.6 The design files, read on 2026-08-07

Located at `C:\Users\IIUM Holdings\OneDrive - IIUM HOLDINGS SDN BHD\ANNUAL ACTIVITY\2026\PROJECT\WEB REVAMP\CLAUDE DESIGN\CLAUDE`. 36 HTML files.

### 3.6.1 There is no single design system in them. There are 21

Every one of the 36 files declares its own `:root` block. Hashing those blocks
gives **21 distinct token sets**. The largest group covers only 8 files.

Real conflicts, same token name, different value:

| Token | Values found |
|---|---|
| `--container` | `1200px` and `1280px` |
| `--paper` | `#F4EFE7` and `#F7F3EC` |
| `--gold-soft` | `#C99A3A` and `#D4A547` |
| `--petrol-deep` | `#0A0A0A` and `#082226` |

The files declare **69 distinct token names**. `tokens.css` has **43**. 27 names
exist in the design files and never reached the theme, including
`--primary`, `--secondary`, `--dark`, `--on-dark`, `--ghost`, `--section-y`,
`--space-1`, `--space-9`, `--space-10`, `--text-4xl`, `--radius-sm`,
`--radius-xl`, `--t-slow`, `--ease-out`, `--container-narrow`, and
`--div-education` / `--div-healthcare` / `--div-infrastructure`, which are a
second set of names for the same three sector colours `tokens.css` already calls
`--edu` / `--infra` / `--health`.

### 3.6.2 What this corrects

[D27](../DOCS/DECISIONS.md#d27) records that `tokens.css` was merged from the
design files with "no value changed, no name collision found". That holds for the
subset of files that merge read. Across all 36 it does not: the four conflicts
above are real, and 27 names were dropped.

**More importantly, it moves the root cause.** The proposal's section 2.3 said
the inconsistency began when the design files were rebuilt by hand in Elementor.
That is only half true. The inconsistency was already in the design files.
Elementor reproduced 21 design systems faithfully.

This does not weaken the case for the migration. It strengthens it. Elementor
took an inconsistency that lived in 36 source files, where it was fixable by
editing text, and baked it into hundreds of per-widget values, where it is not.
The migration is still the fix. But it changes what phase 1 must do first.

### 3.6.3 Consequence: reconcile the tokens before building anything

A canonical token set has to be agreed before `theme.json` is written, not after.
Otherwise `theme.json` inherits the same 21 systems and the whole project buys
nothing. This becomes step 1a of phase 0, and it needs owner decisions on the
four conflicts above.

### 3.6.4 The page families, confirmed by structure

Class fingerprints, not guesses.

| Family | Count | Shared structure |
|---|---|---|
| Director and officer profiles | **9** | Identical: `hero`, `about`, `creds`, `feature`, `next`. Highest reuse on the site |
| Subsidiary pages | **9** | `hero`, `figures` on 8 of 9, then varied |
| Corporate and about | 6 | `about-the-group`, `corporate-profile`, `our-businesses`, `our-subsidiaries`, `rise2030`, `management-team` |
| Governance | 3 | `board-charter`, `code-of-business-conduct`, `whistleblowing` |
| Standalone | 2 | `homepage`, `25th-anniversary`. Both carry MetCPT shortcodes |
| Contact | **4 variants of one page** | `contact-us`, `contact-us-v2`, `contact-us-redesigned`, `contact-directory`. Owner must say which is approved |
| Not pages | 3 | `footer.html` is phase 4 input. `hero-poster-ideas.html` is exploration. `single-post-template.html` already shipped as `single.php` |

`hero` appears in 23 of 36 files and is the one genuinely shared component today.
Everything else is named per page, not per component. So the pattern library is
an abstraction job, not a mechanical conversion: the same layout appears under
different class names on different pages and has to be recognised as one pattern.
Budget for that in phase 1.

## 3.7 What breaks when a Page stops using Elementor

Raised by the owner on 2026-08-07. Checked by reading the code, not assumed.
Four real breakages, one non-issue, and one fix that resolves all four.

### 3.7.1 Page Hero disappears. Confirmed

[inc/page-hero.php:216-217](../inc/page-hero.php#L216-L217) binds the hero to
`elementor/page_templates/header-footer/before_content` and the Canvas
equivalent. Those actions are fired by Elementor's own page templates. Clear
`_elementor_edit_mode` and neither template runs, so neither action fires, and
the hero silently vanishes.

This hits batch 1 hard. Both `/business/` Pages in it carry the business
variant, and several others carry the standard variant, per
[STATE](../DOCS/STATE.md).

### 3.7.2 The page collapses to 1140px. Confirmed

Hello Elementor's `assets/css/theme.css` contains:

```css
body:not([class*=elementor-page-]) .site-main { max-width: 1140px }
```

Elementor adds the `elementor-page-{id}` body class. Remove Elementor from a
Page and the class goes, the `:not()` stops excluding, and the constraint
applies. Full-bleed sections would be trapped in a 1140px column.
`.alignwide` is worse: the parent defines it as `margin-inline:-80px`, a fixed
offset, not a real wide width.

This is the same parent rule [D5](../DOCS/DECISIONS.md) already documents.

### 3.7.3 Two H1s on every migrated Page. Confirmed

There is **no `page.php`** in either theme. Checked both. A Page falls through
`index.php` to `hello-elementor/template-parts/single.php`, which prints
`<div class="page-header"><h1 class="entry-title">` before the content. Our hero
pattern carries the title too. Result: a duplicate title and two H1s, which is
also an accessibility failure on a project targeting 100.

### 3.7.4 Comment forms appear on corporate pages. Confirmed

The same parent template calls `comments_template()`. Site Health reports
**Default comment status: Open**. Elementor Pages never ran that template, so
this has been invisible. Migrated Pages would run it.

### 3.7.5 Scroll to Top is fine. No change needed

[inc/scroll-top.php](../inc/scroll-top.php) renders on `wp_footer`, sitewide,
with no Elementor dependency, and the button re-parents itself to `<body>` at
runtime ([D26](../DOCS/DECISIONS.md#d26)). It works identically before and after
migration. Verified by reading the hook, not by assuming.

### 3.7.6 The fix: the child theme gets its own `page.php`

All four are the same root cause. Pages have no template of their own, so they
inherit one written for blog posts. One file fixes all of it:

```
get_header();                          <- see the correction below
<main id="content" class="met-page">   <- id kept, class dropped
    Page Hero (called directly)
    the_content()
</main>
get_footer();
```

**Corrected 2026-08-07.** The first sketch of this template, and the first
implementation of it, omitted `get_header()` and `get_footer()`. A template
that is only a `while ( have_posts() )` loop renders a bare HTML fragment: no
`<head>`, no enqueued stylesheets, no site chrome. It was caught by fetching
the real page and grepping for stylesheet handles that were not in it, not by
reading the code back. Both calls are required and are now in the file.

Four details that matter:

1. **Keep `id="content"`, drop `class="site-main"`.** The parent's max-width
   selector keys on the class. The skip link keys on the id. Dropping the class
   removes the constraint with no `!important` and no specificity fight, and
   keeps the skip link target working. Same philosophy as
   [D26](../DOCS/DECISIONS.md#d26): win by structure, not by force.
2. **Call the hero directly.** Extract the body of
   `met_hello_child_render_page_hero()` so it can be called from `page.php` as
   well as from the two Elementor actions. Its existing `static` guard already
   prevents a double print. Both paths coexist during the migration, which is
   what makes batches safe: a migrated Page and an Elementor Page both render
   their hero.
3. **No title, no comments.** The hero prints the title. Comments have no place
   on a corporate Page.
4. **The `<main>` shim in [inc/setup.php](../inc/setup.php) stays** until phase 4,
   because unmigrated Elementor Pages still need it. It is removed with Elementor.

This work moves into **phase 0**, not phase 1. It has to exist before the first
Page is migrated.

### 3.7.7 The content source is live staging, not the design folder

Owner instruction, 2026-08-07: live staging holds the accurate, current content.
The design folder holds the intended design.

So each migration reads from two places:

| From | What |
|---|---|
| `CLAUDE DESIGN` file | Structure, layout, section order, tokens |
| Live staging page | Copy, headings, figures, images, links, contact details |

Where the two disagree on wording or numbers, **staging wins**. Where they
disagree on layout, the design file wins. Any third case is an owner decision,
not a developer one.

**Staging is readable from this environment.** Confirmed 2026-08-07 by fetching
`/whistleblowing/` and reading back its full section structure. This corrects
[PROPOSAL-frontend-revamp.md](PROPOSAL-frontend-revamp.md) section 2.6, which
recorded that a TLS connection to staging could not be opened. Content can now
be read directly rather than retyped, which removes a transcription risk from
every page in every batch.

## 4. Approach

### 4.1 The split that makes this incremental

The block **system** and the block **theme** are separate. WordPress supports a
classic theme carrying a full `theme.json`, block patterns and block-authored
page content. Many production themes ship exactly this and stay there.

That means:

- Phases 0 to 3 run inside the current classic child theme. Elementor's header
  and footer keep working. The site is never visibly half-built.
- Phase 4 moves the header and footer into the theme and removes Elementor.
- Phase 5, block theme conversion, is optional and deferred. It buys one thing:
  staff editing the header and footer visually in the Site Editor. Do it when
  that is worth the conversion, not before.

Nothing written in phases 0 to 3 is discarded at phase 4 or 5.

### 4.2 Why `theme.json` solves the actual problem

The problem is not that the values are wrong. It is that nothing stops a person
picking a different one. `theme.json` is the only layer on this site that can
remove the choice:

| Setting | Effect on staff |
|---|---|
| `color.palette` set, `color.custom: false`, `color.defaultPalette: false` | The colour picker offers your brand colours and nothing else. No hex field |
| `typography.fontSizes` set, `customFontSize: false` | Font size is a named scale, not a number box |
| `typography.fontFamilies` set to Geist and Instrument Serif only | Two fonts, no picker of 1,000 |
| `spacing.spacingSizes` set, `customSpacingSize: false` | Padding and margin come from your scale |
| `layout.contentSize` and `wideSize` | Container width is not a per-page decision |

This is the difference between a design system and a document telling people to
follow one. Consistency stops being something you police.

### 4.3 Fonts: self-hosted, through `theme.json`

`theme.json` `fontFace` entries self-host Geist and Instrument Serif from
`assets/fonts/`. This closes three open items at once:

- The `met_hello_child_fonts_url()` TODO and [D15](../DOCS/DECISIONS.md), the
  Google Fonts CDN dependency.
- The [D27](../DOCS/DECISIONS.md#d27) gap where Elementor pages ran Inter while
  the theme's own views ran Geist.
- Render-blocking third-party requests, which the 2026-08-04 baseline named as
  part of the mobile loss.

### 4.4 Token naming, so nothing existing breaks

`theme.json` emits its own custom properties, for example
`--wp--preset--color--gold`. The existing `theme.css`, `scroll-top.css` and
`elementor-base.css` all read `--gold`, `--petrol` and so on.

`theme.json` becomes the single source. `tokens.css` shrinks to an alias file:

```css
:root { --gold: var(--wp--preset--color--gold); }
```

No rewrite of `theme.css`, no duplicated values, one place to change a token.
`tokens.css` keeps its current full form only until Elementor is gone, because
Elementor pages need the raw values during phases 1 to 3.

### 4.45 MetCPT shortcodes carry over unchanged

Confirmed by the owner on 2026-08-07: MetCPT shortcodes are used on the homepage,
the 25th anniversary Page, and the Events, Careers, Tenders and News Pages.

Shortcodes work in the block editor with no change. Each one becomes a Shortcode
block, or is placed inside a pattern that includes it. Nothing in MetCPT is
touched, which keeps [D17](../DOCS/DECISIONS.md#d17) intact.

Two consequences for the plan:

- Those six Pages carry live plugin output, not static markup, so they need a
  functional check after migration, not just a visual one. Confirm each listing
  still queries and renders.
- Migrating them is lower risk than the rest, not higher. The listing was never
  Elementor's to begin with. Elementor was only holding the wrapper around it.

MetCPT shipping real blocks instead of shortcodes would be nicer. It is a
separate repo with its own release cycle, so it is out of scope here and is worth
a future PRD in that repo, not this one.

### 4.5 How a page moves off Elementor, and how it moves back

**Corrected 2026-08-07, by testing.** The first version of this section said
one meta key was enough. It is not. Recorded as written and then corrected,
rather than quietly rewritten, because the wrong version is exactly the
plausible-sounding assumption the next person would also make.

An Elementor page stores its content in `_elementor_data` post meta, sets
`_elementor_edit_mode` to `builder`, and sets `_wp_page_template` to one of
Elementor's own templates (`elementor_header_footer` or `elementor_canvas`).

**Two keys control the move, not one:**

| Meta key | What it actually controls |
|---|---|
| `_elementor_edit_mode` | Whether Elementor's **editor** treats the Page as builder content |
| `_wp_page_template` | Which **front-end template file** WordPress loads. This is the Page Attributes "Template" dropdown |

Clearing only `_elementor_edit_mode` leaves `_wp_page_template` pointed at a
template Elementor no longer claims, and WordPress falls straight past
`page.php` to `index.php`'s generic singular fallback, which reintroduces the
exact `<main class="site-main">` width bug `page.php` exists to prevent. The
page looks migrated in the admin and is still wrong on the front end.

**Migration per page.** Build the content in the block editor, then clear
`_elementor_edit_mode` **and** set `_wp_page_template` to `default`, backing
up its previous value first.

**Rollback per page.** Restore both. `_elementor_data` is never touched, so
the original Elementor page returns intact. The original template value is
stored under `_met_migration_original_template`, so rollback restores the real
previous value rather than guessing at one.

Both directions are handled by `inc/migration-tools.php`
(`manage_options` gated, nonce checked, temporary: see [D29](../DOCS/DECISIONS.md#d29)).
Still reversible in seconds, still why batches of 10 are safe.

### 4.6 What a design file supplies, and what it does not

**Owner instruction, 2026-08-07. This is the rule for every page in every
batch.** Full reasoning in [D30](../DOCS/DECISIONS.md#d30).

Page Hero ([D25](../DOCS/DECISIONS.md#d25)) is the site's fixed header. It is
already on all 16 target Pages, and it exists so that no page's header differs
from any other's. It is **never** redesigned, replaced or removed to match an
individual design file.

Each design file is a full-page mockup that carries its own top-of-page
treatment, and those treatments differ from file to file. Reproducing each one
would hand the site 30 different headers, which is the inconsistency this
project exists to remove.

| From the design file | Taken |
|---|---|
| Body content sections, below the top of the page | Yes, this is the deliverable |
| Its own intro band, hero, or display headline | No. Header is Page Hero's |
| A display headline like "Speak up. We're listening." | As the hero **subtitle** in meta, or dropped. Never a second heading |

The `<h1>` is always `get_the_title()`, printed by Page Hero. The `met-page__intro`
fallback in `page.php` and `patterns.css` applies only to a Page with no hero
variant set, which no migrated Page in this project should have.

## 5. Scope

**In.**

- `theme.json` for the child theme, generated from `tokens.css`.
- Self-hosted Geist and Instrument Serif.
- A block pattern library built from the `CLAUDE DESIGN` reference files.
- Migration of about 30 Page bodies from Elementor to blocks, in batches of 10.
- Header, main menu, mobile menu and footer moved into the theme, at phase 4.
- Removal of Elementor and its addon plugins, at phase 4.
- The corporate-site gaps listed in section 7.

**Out.**

- The theme's own editorial views: `single.php`, `archive.php`, `search.php`,
  `author.php`, `404.php`. They work, they are already token-driven, and they
  are not the problem. They stay classic PHP.
- MetCPT's Events, Tenders and Careers. Separate repo, separate release cycle,
  [D17](../DOCS/DECISIONS.md#d17).
- MetTranslate. Separate repo. It gains from this work, since block content
  lives in `post_content` where it can read it, but nothing in MetTranslate
  changes here.
- Block theme conversion and the Site Editor. Phase 5, deferred, section 6.
- SEO, by the same instruction as the proposal.

## 6. Steps

Each phase is a tagged release, testable on local `v2` before staging.

### Phase 0a: match the environments. No release

0a. Install Essential Addons 6.7.2 and Ultimate Addons (UAE) 2.9.2 on local `v2`,
    and update Elementor to 4.2.1, so local renders what staging renders. Without
    this the phase 1 comparison test cannot run.
0b. Ship the untagged v1.8.0 work to staging, which is still on 1.7.2. Staging has
    never received the token layer or the Elementor `<main>` landmark fix. Do this
    before phase 0 so there is only one unreleased change in flight at a time.
0c. Send the host the opcache request in section 3.5.4 and apply the
    `wp-config.php` and PHP settings in section 3.5.5. None of it is theme code
    and all of it helps every phase after.

### Phase 0: foundation. No page changes. Target v1.9.0

1a. **Reconcile the tokens first.** Section 3.6. Produce one canonical set from the
    21 found in the design files: settle the four value conflicts, decide which of
    the 27 missing names are real tokens and which are page-local noise, and
    collapse the duplicate sector-colour naming. Output is a short decision table
    the owner signs off. Nothing else in phase 0 starts until this is done.
1. Write `theme.json` from the reconciled set. Palette, font sizes with the existing
   `clamp()` values as fluid sizes, spacing scale, layout widths, root padding
   from `--gutter`. Set the four "custom" switches to `false` so the scale is
   the only option.
2. Self-host Geist and Instrument Serif in `assets/fonts/`, declare them as
   `fontFace` entries. Point `met_hello_child_fonts_url()` at the local sheet
   or retire it. Remove the Google Fonts preconnect hints.
3. Rewrite `tokens.css` as the alias layer in section 4.4. Confirm `theme.css`,
   `scroll-top.css` and `elementor-base.css` render identically.
4. Turn off core's remote pattern directory and the default palette, so the
   inserter shows your patterns and no one else's.
4a. **Add `page.php` to the child theme**, per section 3.7.6. Refactor
    `met_hello_child_render_page_hero()` so the hero can be called from both
    `page.php` and the existing Elementor actions. This must ship before any Page
    is migrated.
5. **Test:** every existing page, Elementor and native, looks unchanged. Blog,
   archive, search, author, 404 unchanged. MetCPT pages unchanged. `phpcs`
   clean.

### Phase 1: pattern library and the first 10 pages. Target v2.0.0

6. Build the block pattern set from the `CLAUDE DESIGN` files: hero, section
   band, stat row, card grid, leadership grid, split feature, call to action,
   logo strip, contact block. Final list comes from reading those 36 files.
7. Register the patterns with block types and categories so they appear where
   staff expect them. Lock the parts that must not be edited.
8. Add block style variations for the sector colours (`--edu`, `--infra`,
   `--health`), so a subsidiary page is a variation, not a rebuild.
9. Migrate batch 1, chosen by the owner on 2026-08-07:

   | # | Page | Design file | Notes |
   |---|---|---|---|
   | 1 | `/` Homepage | `homepage.html` | Baseline page, mobile 61. MetCPT shortcode |
   | 2 | `/iium-holdings-25th-anniversary/` | `25th-anniversary.html` | MetCPT shortcode |
   | 3 | `/iium-holdings-group-of-companies/` | `about-the-group.html` | Owner confirmed 2026-08-07, over `our-businesses` and `our-subsidiaries` |
   | 4 | `/board-of-directors/` | **none exists** | Baseline page, mobile 61, and the 154 KiB unused-CSS outlier. The folder has 9 individual director pages but no index. Design needed |
   | 5 | `/management-team/` | `management-team.html` | |
   | 6 | `/rise2030-strategy-blueprint/` | `rise2030.html` | |
   | 7 | `/whistleblowing/` | `whistleblowing.html` | Structure read from staging, 2026-08-07 |
   | 8 | `/contact-us/` | `contact-us-v2.html` | Owner picked 2026-08-07 from 4 variants. Carries a WPForms form |
   | 9 | `/business/iium-medical-specialist-centre-sdn-bhd/` | `iium-medical-specialist-centre.html` | Business Page Hero variant |
   | 10 | `/business/daya-bersih-sdn-bhd/` | `daya-bersih.html` | Business Page Hero variant |

   Per page: read structure from the design file and content from live staging
   (section 3.7.7), build the **body only** in blocks (section 4.6, Page Hero
   stays as the header), verify against the live original at 390, 768, 1280 and
   1920, confirm the Page Hero still renders, then run the two-key migration in
   section 4.5. Where a Page carries a MetCPT shortcode (section 4.45), also
   confirm the listing still queries and renders, not only that it looks right.

   **Status, 2026-08-07. Page 7 of 10 done, the other nine not started.**
   `/whistleblowing/` (post 86, note the slug is `whistleblowing`, not
   `whistle-blowing`) is migrated and live on local `v2`: Page Hero standard
   variant, eyebrow "Governance", subtitle "Speak up. We're listening.", body
   content built from `whistleblowing.html`, `<main id="content"
   class="met-page">`, one `<h1>`, no comment form, no Elementor markup, no
   fatal errors, Scroll to Top intact.

   It took **three rounds** and two owner screenshots to get right, which is
   the useful part of this status note. Round 1 shipped structurally correct
   and visually wrong. Round 2 fixed real body-content bugs: serif on
   headings that the design sets in bold sans, a two-column grid collapsed to
   one, missing contact icons, `.eyebrow` labels with no styling at all
   (`theme.css` scopes that rule under `.met-view`, which a `.met-page` never
   carries), and WordPress `is-layout-*` classes generating width and display
   CSS that competed with the file's own grid rules. Round 3 was the
   [D30](../DOCS/DECISIONS.md#d30) correction: the header had been rebuilt to
   match the design file and had to go back to Page Hero.

   **The lesson, for the remaining 29 pages: structural verification is not
   visual verification.** curl and grep confirmed every one of those rounds as
   "correct". Only a browser caught what was wrong. Do not batch-build pages
   without a screenshot between each one.

9a. **Reuse risk, stated once.** This batch spans six page families and shares
    almost no structure, so it is the weakest available test of pattern reuse:
    roughly 10 patterns for 10 pages proves nothing about whether the library
    scales. Mitigation, not a reason to change the batch: build the hero, section
    band, figures row and CTA as generic patterns from the start, not as
    page-specific ones, so batches 2 and 3 inherit them. The batch is also well
    chosen for two other reasons, which is why it stands: it contains 2 of the 3
    measured baseline pages, and Board of Directors plus Management Team are the
    index pages for the 9 director profiles, which makes those 9 the natural
    batch 2 with high reuse.
10. **Test:** the 10 pages match or beat the Elementor originals visually. Re-run
    PageSpeed on any of the three baseline pages that are in this batch and
    compare to the 2026-08-04 numbers.

### Phase 2: pages 11 to 20. Target v2.1.0

11. Same process. New patterns only where a page genuinely needs a shape the
    library does not have. Every new pattern is reusable by later batches, so
    this phase should be faster than phase 1.
12. **Test:** same checks. Pattern count grows slower than page count. If it
    does not, the library is wrong and we stop and fix it.

### Phase 3: pages 21 to 30 and the rest. Target v2.2.0

13. Same process, to completion. No Page on the site still renders from
    `_elementor_data`.
14. **Test:** confirm zero pages have `_elementor_edit_mode` set. Full responsive
    walk at 390, 768, 1280, 1920. Full PageSpeed re-measure on all three
    baseline pages.

### Phase 4: chrome, and Elementor out. Target v3.0.0

15. Confirm the header and footer source, per section 3.5.2. Expected answer:
    Hello Elementor's own Customizer header and footer, since no header and footer
    builder plugin is installed and Elementor's Theme Builder is Pro only. If so,
    steps 16 and 17 are overrides of two parent template parts, not a rebuild.
16. Rebuild the header in the child theme: logo, main menu via `wp_nav_menu`,
    accessible mobile menu, sticky behaviour, skip link target. Keep the
    existing design.
17. Rebuild the footer: columns, social links with accessible names, which also
    closes the outstanding accessibility finding in
    [STAGING-CHECKLIST-1.8.0.md](STAGING-CHECKLIST-1.8.0.md), legal links.
18. Delete `elementor-base.css`, and the Elementor `<main>` landmark shim in
    `inc/setup.php`, since a theme-rendered page prints its own `<main>`. Update
    [D27](../DOCS/DECISIONS.md#d27) to mark both superseded.
19. Deactivate Elementor and its addons. Leave them installed and inactive for
    one full release cycle, then uninstall.
20. **Test:** whole-site walk. Header, menu, mobile menu, footer, all templates,
    all four widths. Confirm MetCPT and MetTranslate unaffected. Final PageSpeed
    against the 2026-08-04 baseline.

### Phase 5: block theme conversion. Deferred, not scheduled

21. Convert `templates/` and `parts/` to block templates so staff edit the header
    and footer in the Site Editor.

Do this only when staff editing chrome visually is worth the conversion. The
trigger is a real request, not a date. The theme also stops being a Hello
Elementor child at that point, so it needs a slug decision and a one-time
reinstall on both sites. Everything in phases 0 to 4 carries over unchanged.

## 7. Corporate-site gaps to close along the way

You asked what a corporate site should have that you may not know about. These
are cheap and fold into the phases above rather than needing their own project.

| Item | Phase | Note |
|---|---|---|
| Breadcrumbs on inner pages | 1 | Yoast 28.2 is installed and can output them |
| Organisation structured data | 1 | Legal name, logo, address, contact. Drives the Google knowledge panel |
| Search covering Pages and CPTs | 3 | `search.php` exists but is scoped to blog |
| Human sitemap page | 3 | Separate from Yoast's XML sitemap |
| Accessibility statement page | 3 | Standard for a government-linked holding company |
| Privacy, terms, cookie notice | 3 | Cookie consent is a legal question. Flag it, do not decide it here |
| Language switcher placement | 4 | MetTranslate exists. It needs a fixed home in the chrome |
| Print styles | 4 | Board and annual-report pages get printed |
| Favicon and app icon set | 4 | |

## 8. Prerequisites

1. **Located 2026-08-07, but still not in the repo.** The 36 design files are in
   OneDrive at `ANNUAL ACTIVITY\2026\PROJECT\WEB REVAMP\CLAUDE DESIGN\CLAUDE`,
   read and inventoried in section 3.6. They are still outside git, so the home
   machine and every future session cannot rely on them. Commit them to this repo
   under `design/`, in line with [D18](../DOCS/DECISIONS.md).
1b. **Blocking phase 0: the four token conflicts in section 3.6.1 need owner
   decisions.** Step 1a produces the table. Nothing is written until it is signed.
1c. **Blocking batch 1, three page-mapping gaps.** Step 9. Which of the four
   contact variants is approved. Which design file `iium-holdings-group-of-companies`
   maps to. And Board of Directors has no design file at all, so it needs one
   designed before it can be built. None of these block phase 0.
2. **Blocking phase 1: local does not mirror staging.** Section 3.5.1. Install
   Essential Addons and Ultimate Addons on local, phase 0a.
3. **Blocking phase 3: do not enter MetTranslate translations for the ~30 Pages
   until phase 3 is done.** Section 3.5.6.
4. **Not blocking, do this week: the host opcache fix.** Section 3.5.4.
5. Staging's plugin list was the second blocker in the first draft of this
   document. Owner supplied it on 2026-08-07 and it is now section 3.5. Closed.

## 9. Risks

| Risk | What to do |
|---|---|
| A migrated page looks worse than the Elementor original | Compare side by side at 4 widths before clearing `_elementor_edit_mode`. The old page is one meta field away until then |
| Batches of 10 leave the site mixed for weeks | Both systems read the same tokens after phase 0, so a block page and an Elementor page look alike. This is the reason phase 0 comes first |
| LiteSpeed Cache breaks the new CSS delivery | Known on this site, [D26](../DOCS/DECISIONS.md#d26). Purge after every release and verify on staging, never only on local |
| Staff find the block editor harder than Elementor | Lock patterns so the parts that must not move cannot move. Budget one training session at phase 1, not phase 4 |
| The pattern library grows one pattern per page | That means the library is wrong. Phase 2's test is written to catch it. Stop and consolidate rather than continue |
| Scope creep from section 7 | Those items are small and scheduled. Anything not on that list waits for its own PRD |
| Two machines diverge mid-project | `git pull --ff-only origin main` before editing, [D18](../DOCS/DECISIONS.md). Push `main` before the tag |
| MetCPT or MetTranslate breaks | Both are checked at the end of every phase, not only at the end of the project |
| A big page save is silently truncated by `max input variables` 1000 | Raise it to 3000 in phase 0a, before any large page is built. This fails quietly, so it will not announce itself |
| Translations entered before phase 3 are orphaned by migration | Section 3.5.6. Hold MetTranslate content entry for the ~30 Pages until phase 3 closes |
| Staging is two versions behind local and gets three changes at once | Phase 0b ships 1.8.0 to staging on its own, before phase 0 |
| A MetCPT listing looks fine but stops querying after migration | Step 9 checks function, not only appearance, on the six shortcode Pages |
| The opcache fix is refused or ignored by the host | It is host config, outside this repo. If refused, record it and stop attributing its cost to theme code, the same mistake PROJECT_LOG corrected on 2026-08-04 |
| Page Hero vanishes from a migrated Page | Section 3.7.1. Fixed in phase 0 step 4a, before any Page moves. Verify the hero on every migrated Page as part of step 9, not at the end |
| Batch 1 produces one pattern per page and proves nothing about reuse | Step 9a. Build the shared patterns generically from the start. If batch 2, the 9 director profiles, does not reuse them heavily, stop and consolidate before batch 3 |
| Design file and staging content disagree | Section 3.7.7. Staging wins on copy, the design file wins on layout, anything else is an owner decision |

## 10. Done when

Measured against the 2026-08-04 baseline in
[PROPOSAL-frontend-revamp.md](PROPOSAL-frontend-revamp.md) section 2.5.

| Check | Now | Target |
|---|---|---|
| Homepage mobile | 61 | 90+ |
| Board of Directors mobile | 61 | 90+ |
| IKOP Pharma mobile | 74 | 95+ |
| Desktop, all three | 89 to 96 | No regression |
| Worst mobile LCP | 11.9s | Under 2.5s |
| Accessibility | 90 | 100 |
| Pages rendering from `_elementor_data` | About 30 | 0 |
| Elementor and addon plugins active | Yes | No. Elementor, Essential Addons and Ultimate Addons all off |
| Active plugin count on staging | 11 | 7. WPForms, WP Mail SMTP, LiteSpeed, Yoast, MetCPT, MetTranslate, Imunify |
| Opcache hit rate | 58.91% | 95%+ |
| Changing one token in `theme.json` | Changes nothing site-wide | Visibly changes every page |
| A staff member picking an off-brand colour | Possible | Not offered by the editor |
| Responsive walk at 390, 768, 1280, 1920 | Not done | Clean on every page |
