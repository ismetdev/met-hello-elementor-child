# PROJECT LOG

What was built and when. Newest first.

**Provenance.** Reconstructed on 2026-08-01. Versions 1.0.0 to 1.4.2 were built on
the home machine, and Claude Code transcripts do not sync between machines, so the
office laptop has none of those sessions. The entries below come from the
`readme.txt` changelog, git history, the code itself, and one cross-reference in
the MetCPT session of 2026-07-29. Dates for 1.0.0 to 1.3.0 are phase attributions,
not commit dates: all three commits in this repo were made on 2026-07-07, when the
finished theme was first pushed to GitHub. From here on, log as work happens.

---

## 2026-08-01: project docs added (office laptop)

Added `DOCS/STATE.md`, `DOCS/DECISIONS.md`, `DOCS/PROJECT_LOG.md` and
`DOCS/WRITING_RULES.md`. Triggered by losing access to the earlier chat sessions:
the design reasoning existed only in transcripts on the home machine, so it was
rebuilt from the artifacts and committed where both machines can see it.

Searched every Claude Code transcript on this laptop (`~/.claude/projects/`): no
theme sessions, only today's. The MetCPT session `d3a31ece` held seven useful
references to this theme, which is where the scope-boundary check and the "Haraka"
comment issue came from.

`WRITING_RULES.md` sets the writing standard for replies, docs, commits and plans.

No code changed.

---

## 2026-07-29: cross-checked against MetCPT (from the plugin session)

While auditing the MetCPT plugin, the theme was read to settle which component
owns which archive page. Findings:

- The theme reads no MetCPT options. The two are fully decoupled.
- Ownership is clean. The plugin owns `/events/`, `/tenders/`, `/careers/` and the
  raw CPT archive fallback. The theme owns news and blog category, tag and date
  archives, scoped by `is_category() || is_tag() || is_date()`.
- Found: about 4 comments still say "Haraka", the plugin's former name, in
  `functions.php`, `README.md`, `readme.txt`, `style.css`. Cosmetic only.
- Decided: do not fix it from the plugin session. Separate repo, separate release
  cycle. See [DECISIONS.md](DECISIONS.md#d17). Still open.

No code changed in this repo.

---

## 2026-07-07: v1.4.2, single post share, back and author links (`6c3dcef`)

- Share row extended to X, Facebook, LinkedIn, WhatsApp, Telegram and Threads,
  built from a reusable `met_hello_child_get_share_links()` helper and an extended
  inline SVG icon set.
- Back button now targets the post's own category archive, falling back to the
  Newsroom URL only when the post has no category.
- Author name now links to the author archive page.

`functions.php`, `single.php`, `style.css`, `readme.txt`. +89 / -12.

---

## 2026-07-07: v1.4.1, real branded screenshot (`f5de4f8`)

Replaced the 1x1 placeholder `screenshot.png` (70 bytes) with a rendered 1200x900
branded thumbnail (83 KB). Small change, real purpose: it ran the whole release
pipeline end to end (tag, Action, zip, Release, WordPress update screen) on a
change where failure cost nothing.

---

## 2026-07-07: v1.4.0, GitHub auto-updates and first public release (`b6d6583`)

First commit in the repo. The accumulated 1.0.0 to 1.4.0 work published as a
public repo: 133 files, about 12,987 lines, most of it the bundled update library.

- Bundled YahnisElsts Plugin Update Checker v5 in theme mode, pointed at
  `ismetdev/met-hello-elementor-child`, branch `main`, with
  `enableReleaseAssets()` so updates come from the workflow-built zip instead of
  GitHub's auto-generated source archive.
- Optional private-repo auth via `MET_HELLO_CHILD_GITHUB_TOKEN`, read from
  `wp-config.php` and never committed.
- Added `.github/workflows/release.yml`: fires on `v*` tags, builds the zip on
  Linux inside a correctly named folder, checks `style.css`, `functions.php` and
  the update library are present, publishes the Release with the zip attached.
- Theme author set to ismetdev.

---

## v1.3.0: search, author, 404, standalone pages, shared design system

The largest step. Three new views plus the refactor needed to support them.

- **New views:** `search.php`, `author.php`, `404.php`, all reusing one design.
- **Refactor:** CSS reorganised into a shared `.met-view` scope with a reusable
  `.met-hero` band and `.met-listing` / `.met-card` grid. The card moved to
  `template-parts/met-card.php` and is shared by archive, search and author.
  `single.php` and `archive.php` migrated to the shared classes with no intended
  visual change.
- **Scope plumbing:** enqueue scope, preconnect hints and the full-width body
  class unified behind `met_hello_child_is_styled_view()`. Per-view body classes
  collapsed into one `met-hello-child-fullwidth`.
- **Author header:** avatar, name, post count, biography, website and social links
  (Yoast aware), degrading cleanly when fields are empty.
- **Standalone pages:** maintenance toggle (503 plus cache bypass, admins exempt),
  a `wp-content/maintenance.php` update drop-in, an `ErrorDocument` 403 file, and
  a styled `wp_die()` handler for application-level 403s, all with inlined CSS.

---

## v1.2.1: phase 4 hardening

Escaping audit, conditional-asset check, i18n, cross-plugin and accessibility
review. One real bug found and fixed: double-escaped featured image `alt` text in
`single.php` and `archive.php`, because `the_post_thumbnail()` already escapes
attributes. Everything else passed with no changes.

---

## v1.2.0: phase 3, archives

`archive.php` for category, tag and date archives. Compact petrol header band plus
a uniform responsive card grid (`auto-fill minmax(320px, 1fr)`, single column on
mobile). Cards show a featured image, with a petrol pattern fallback when absent
so the grid stays even, a primary-category eyebrow, linked title, date, reading
time and a trimmed excerpt. Styled pagination with gold accents. CSS scoped under
`.met-archive`. Enqueue scope widened to these archives only.

---

## v1.1.0: phase 2, the single post

`single.php` in the new editorial design: petrol hero, feature image frame,
article body, share and back row. Geist and Instrument Serif loaded from the
Google Fonts CDN behind one function, ready to self-host. Design CSS enqueued and
full width forced ("Option A") on single Posts only. Added the reading-time,
primary-term and filterable back-link helpers. Header and footer come from
Elementor via `get_header()` and `get_footer()`, and the CSS is scoped to the
article region so it never affects them.

---

## v1.0.0: phase 1, scaffold

Child theme scaffold. Enqueues parent then child stylesheet, defines the version
constant, loads the text domain. Renders identically to plain Hello Elementor: no
custom templates, no design CSS. A deliberate no-op baseline to prove the child
theme was wired correctly before any design landed.
