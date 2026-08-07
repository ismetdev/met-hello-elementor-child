# PROPOSAL: front-end revamp of v2.iiumholdings.com.my

Status: options for review, not approved
Author: Ismet, with Claude
Date: 2026-08-04
Decision needed by: 2026-08-04, because the GMD demo is 2026-08-07

This is the options document. Once you pick, it becomes a full PRD that Sonnet 5
can build from.

## 1. Goal

Make the 30 or so Elementor pages on live staging load fast, look like one
designed system, and hold up on every screen size, in time to show the GMD on
2026-08-07, without breaking anything before the migration to the live site.

Your priority order, used throughout this document:

1. Performance
2. Beauty of the UI design
3. Design consistency
4. Responsiveness
5. Accessibility and polish

SEO is out of scope, by your instruction.

## 2. What I found

### 2.1 The site

Confirmed by visiting the live staging site: about 30 pages across About Us,
Business (9 subsidiaries in 3 divisions), Media, Tender, Careers, Whistleblowing
and Contact Us. Header, footer and all page bodies are Elementor.

### 2.2 The design system already exists, in the wrong place

The 36 files in `CLAUDE DESIGN\CLAUDE` are a real design system. `homepage.html`
defines the full token set on `:root`:

| Group | Tokens |
|---|---|
| Colour | `--ink`, `--ink-soft`, `--ink-faint`, `--paper`, `--paper-cool`, `--petrol`, `--petrol-soft`, `--gold`, `--gold-soft`, `--hairline` |
| Sector | `--edu` `#4A7BA8`, `--infra` `#B98A2E`, `--health` `#B5566F` |
| Type | `--font-sans` Geist, `--font-serif` Instrument Serif, scale `--text-xs` 12px to `--text-hero` `clamp(44px, 7vw, 92px)` |
| Layout | `--container` 1200px, `--gutter` `clamp(20px, 4vw, 40px)`, `--radius` 10px, `--radius-lg` 16px |
| Motion | `--ease`, `--t-fast` 180ms, `--t-med` 280ms |

Note `--text-hero` and `--gutter` already use `clamp()`. That is fluid
responsive design done correctly. It is in the reference files and nowhere on the
live site.

### 2.3 The root cause, in one line

When those files were rebuilt by hand in Elementor, every token became a
hardcoded value inside a widget, so the site has around 30 private design
systems instead of one shared one.

That single fact explains all three complaints:

- **Inconsistency.** Nothing governs the values, so they drifted page to page.
- **Bad responsiveness.** The `clamp()` scales were lost. Elementor replaces them
  with per-widget values at 3 fixed breakpoints, tuned by eye, on hundreds of
  widgets. Sizes between and beyond those breakpoints were never designed.
- **Bad performance.** Every hardcoded value is CSS Elementor must generate and
  ship, on top of the full asset set of two addon plugins.

### 2.4 The theme is not the problem, and is also not helping

`met-hello-elementor-child` v1.7.2 is in good shape and deliberately does not
touch Elementor pages. `met_hello_child_is_styled_view()` in
[inc/setup.php](../inc/setup.php) gates it to native Posts and their archives
(DECISIONS D3, D4). Only Scroll to Top is sitewide (D26).

So the theme is clean, and it is also not where the site's visible problems live.
Any fix has to reach the Elementor pages, which means changing that boundary on
purpose, with the reasoning written down.

### 2.5 Measured baseline

PageSpeed Insights, 2026-08-04, owner-supplied.

| Page | Desktop | Mobile | Mobile LCP | Mobile FCP | TBT |
|---|---|---|---|---|---|
| Homepage | 90 | **61** | **11.9s** | 4.0s | 0ms |
| /board-of-directors/ | 89 | **61** | **10.9s** | 4.4s | 30ms |
| /business/ikop-pharma-sdn-bhd/ | 96 | 74 | 4.8s | 3.6s | 0ms |

Accessibility 90, Best Practices 100, SEO 92 on all three, desktop and mobile.

**Desktop is not the problem. Mobile is, and it is bad.** An 11.9 second LCP is
not a tuning issue, it is a page that a phone user on a normal connection will
abandon.

Where the loss actually comes from:

| Cause | Homepage mobile | Board mobile | IKOP mobile |
|---|---|---|---|
| Render-blocking requests | 2,400ms | **5,420ms** | 2,240ms |
| Image delivery | 1,380 KiB | 802 KiB | 145 KiB |
| Cache lifetimes | 233 KiB | 264 KiB | 45 KiB |
| Unused CSS | 12 KiB | **154 KiB** | 12 KiB |

Desktop total page weight: 3,400 KiB homepage, 2,740 KiB board of directors.

Four readings that matter:

1. **JavaScript is not the problem.** Total Blocking Time is 0ms, 30ms, 0ms.
   Near perfect. Whatever Elementor, Essential Addons and Ultimate Addons ship in
   JS, the phone is not choking on it.
2. **Render-blocking CSS and images are the problem.** Together they account for
   almost the whole mobile loss. Both are fixable by configuration and media
   work. Neither needs a page rebuilt.
3. **Board of Directors is the outlier.** 154 KiB unused CSS and 5,420ms
   render-blocking, against 12 KiB and 2,240ms elsewhere. One widget or one
   plugin asset set is loading there and nowhere else. Worth finding, it is
   probably the single worst page on the site.
4. **CLS 0.096 on desktop, on all three pages.** Identical number, so it is the
   shared chrome, the header or the hero, not per-page content. One fix, whole
   site.

Accessibility 90 fails the same 4 checks on every page: colour contrast (this is
the known [D26](../DOCS/DECISIONS.md) contrast fail, now measured by Google),
links without a discernible name, skip links not focusable, and no `main`
landmark. The last three are child theme fixes and are cheap.

This corrects PROJECT_LOG 2026-08-03, where the Lighthouse pass was dropped
because the score measured the Elementor build rather than the theme. That was
right then. This project is about the Elementor build, so the score is now the
correct measure.

### 2.6 One thing I still could not check

I read page text, not pixels. My environment cannot open a TLS connection to the
staging site, so I have not seen the responsive break at 390px with my own eyes.
The responsiveness work needs a manual walk on real widths.

## 3. The options

Three ways to get one design system onto the pages. Judged on your five
priorities plus effort and risk.

### Option A: govern Elementor from a token layer

Keep every page as it is. Add two things:

1. A small sitewide stylesheet from the child theme that declares the `:root`
   tokens and sets the base rules: type scale, container width, section rhythm,
   button styles, link and focus states.
2. Elementor Site Settings (Global Colours, Global Fonts, custom breakpoints)
   pointed at the same values, so new work inherits them.

Then, page by page, clear hardcoded widget values so the layer shows through.
That last part is manual Elementor work and is where the real hours are.

| | |
|---|---|
| Industry standing | Standard practice for an Elementor site. Elementor Globals are the builder's own token system, and this is what they are for |
| Performance ceiling | Higher than I first thought, now the baseline is measured. The mobile loss is render-blocking CSS and images, not Elementor's JS. Both are fixable without touching a page. Mobile 85+ is realistic |
| Beauty | Unchanged, then improved as tokens land. Your design, not AI design |
| Consistency | Good. Not perfect, since one missed hardcoded value stays wrong until found |
| Responsiveness | Good. `clamp()` in the layer works at every width, not just 3 breakpoints |
| Editability | Unchanged. Your team keeps Elementor |
| Effort | 2 to 3 days for the layer and Site Settings. 2 to 3 weeks for the full page-by-page pass |
| Risk | Low. Reversible by removing one stylesheet |
| Ready for 2026-08-07 | Yes, the layer and the quick wins |

### Option B: rebuild the pages in the theme, edit via the Customizer

Replace Elementor page bodies with PHP templates built from the design folder
HTML. Content edited through Customizer fields or post meta. This is your
stated leaning.

| | |
|---|---|
| Industry standing | **Below standard, and I have to say so plainly.** Two reasons. First, the Customizer is legacy: WordPress froze it in favour of the Site Editor years ago, and it was designed for theme options, not page content. Second, 30 pages of content behind developer-defined fields means every content change is a developer ticket. That is the pattern the whole industry spent a decade moving away from |
| Performance ceiling | Highest of the three. Elementor removed, hand-written CSS, mobile 90+ realistic |
| Beauty | Exactly your design, no builder in the way. Best of the three |
| Consistency | Perfect. One stylesheet, no way to drift |
| Responsiveness | Perfect. The `clamp()` scales survive intact |
| Editability | **Worst of the three.** Your team loses the visual editor |
| Effort | 6 to 10 weeks for 30 pages, plus content re-entry |
| Risk | High. Every page rebuilt, every URL and layout re-verified |
| Ready for 2026-08-07 | **No. Not one page.** |

To be fair to it: the *performance and design* half of your instinct is right.
Hand-built beats a builder every time. It is the *Customizer as CMS* half that
does not hold, and it is the half your content team pays for daily, forever.

### Option C: block theme, `theme.json`, block patterns

Convert to a WordPress block theme. Design tokens live in `theme.json`, which is
the WordPress-native token standard. Pages become block patterns built from the
design folder. Elementor is retired.

| | |
|---|---|
| Industry standing | **The current WordPress standard.** `theme.json` is the platform's own answer to this exact problem, and it is where WordPress core investment goes |
| Performance ceiling | Near Option B. Mobile 85 to 95 realistic |
| Beauty | Your design, same as B |
| Consistency | Enforced by the platform. A token changed in `theme.json` changes everywhere |
| Responsiveness | Full fluid scales, same as B |
| Editability | **Best of the three.** Editors get the Site Editor: visual, no developer needed, and it cannot drift outside the tokens |
| Effort | 8 to 12 weeks. Also a real change for whoever edits the site |
| Risk | High if rushed. Low if phased |
| Ready for 2026-08-07 | No |

## 4. Recommendation

**Do Option A now. Commit to Option C as the target. Reject Option B.**

Reasoning:

- **The demo is in 3 days.** That is not a preference, it is arithmetic. B and C
  cannot show anything by 2026-08-07. A can, and A is not throwaway work: the
  token layer written now is the same token set that becomes `theme.json` later.
- **Option B is the one to drop.** It costs almost as much as C and gives your
  content team a worse tool than they have today. C reaches the same performance
  and the same design with a better editing story. If you want off Elementor, and
  that is a reasonable thing to want, go to C, not B.
- **Beauty is not at risk in any option.** You said your other AI-built theme
  looked generic. That is not a danger here. The design already exists in
  `CLAUDE DESIGN\CLAUDE` and it is yours. My job is to reproduce it faithfully,
  not invent one.
- **Performance is your top priority, and the measured data now supports A on
  it.** I originally wrote that A was weakest here. The baseline in section 2.5
  disproves that. Total Blocking Time is 0ms, so the builder and its addons are
  not what is slow. The loss is render-blocking CSS and unoptimised images, and
  neither one needs a page rebuilt. A can get most of the available win.

## 5. Plugins: my recommendation

Accepted by you on 2026-08-04. **Keep Essential Addons and Ultimate Addons for
now, configure them properly, and plan their removal as part of Option C.**

The measured baseline strengthens this, for a better reason than the one I first
gave. I assumed the addons were a performance cost. Total Blocking Time of 0ms
says their JavaScript costs almost nothing. They still add CSS to the
render-blocking chain, which is worth trimming, but removing them would not have
bought the mobile score. Rebuilding every widget they power, 3 days before a
demo, would have been a large risk for a small gain.

What to do instead, this week, no page edits:

- Turn on Essential Addons "Asset Generation" so only widgets actually used on a
  page load their CSS and JS.
- Disable every unused element in both plugins' settings screens.
- In Elementor Settings: enable Improved Asset Loading, Improved CSS Loading and
  Inline Font Icons. Disable Elementor's Google Fonts loading, since the theme
  already loads Geist and Instrument Serif (DECISIONS D15).
- Find what makes `/board-of-directors/` load 154 KiB of unused CSS when other
  pages load 12 KiB. That is one widget or one plugin asset set, and it is the
  worst offender on the site.
- Audit which of their widgets are genuinely used, and put the list in the PRD.
  That list is the removal plan for later.

## 6. What Option A does before 2026-08-07

Three working days: Tuesday 4th, Wednesday 5th, Thursday 6th. Friday is the
demo.

Ordered by measured impact, biggest first. Baseline is done, so Tuesday starts on
the real work.

| Day | Work | Touches | Expected |
|---|---|---|---|
| Tue | **Images.** Convert to WebP, resize to the size actually displayed, set explicit width and height, lazy load below the fold, preload the LCP image. Up to 2,752 KiB back on the homepage | Media library and Elementor image settings. No code | The single largest win. Most of the mobile LCP |
| Tue | **Render-blocking CSS.** LiteSpeed critical CSS, defer non-critical CSS, cache lifetimes. Plugin config from section 5. Find the Board of Directors 154 KiB outlier | Plugin settings only | 2,240ms to 5,420ms of the mobile delay |
| Wed | **Token layer.** Ship the sitewide stylesheet from the child theme. Set Elementor Global Colours, Global Fonts and breakpoints to match | Child theme, one new stylesheet, new release version | Consistency and responsiveness |
| Wed | **Cheap accessibility fixes.** `main` landmark, focusable skip link, discernible link names, `font-display: swap` | Child theme | Accessibility 90 to about 96 |
| Thu | Walk every page at 390px, 768px, 1280px, 1920px. Fix the worst breaks. Re-measure all 3 pages. Freeze | Page-level Elementor edits, only where needed | No broken layout on demo day |

Deliberately not attempted this week: the full page-by-page consistency pass, any
plugin removal, any rebuild, the D26 contrast fix.

Note that images and CSS delivery are content and configuration work, not code.
They do not compete with the token layer for time, and they carry no risk to the
design. That is why they go first.

## 7. Risks

| Risk | What to do |
|---|---|
| A sitewide stylesheet changes the boundary D3 and D4 set, and could collide with MetCPT pages | Load it as its own small file, like `scroll-top.css` (D26), not by widening `met_hello_child_is_styled_view()`. Check MetCPT pages before release |
| The token layer overrides a hardcoded value someone wanted, and a page looks wrong on demo day | Do it Wednesday, not Thursday. Walk all 30 pages Thursday morning |
| Hardcoded Elementor values beat the layer on specificity | Learn from D26: qualify selectors with the element type, do not reach for `!important` first |
| LiteSpeed CSS Combine reorders or drops the new file | Known problem on this site, see D26. Purge cache after release and re-check on staging, not local |
| Changes are made straight on staging and lost | Build on local `v2`, release by tag, pull to staging. Follow the D18 pull-before-edit rule |
| The demo is 3 days away and this doc is asking for a decision today | Decide today. If nothing is decided by end of Tuesday, do Tuesday's configuration work only and show the site as it is |

## 8. Done when

Measured against the 2026-08-04 baseline in section 2.5.

| Check | Now | Target by 2026-08-07 |
|---|---|---|
| Homepage mobile | 61 | 85+ |
| Board of Directors mobile | 61 | 85+ |
| IKOP Pharma mobile | 74 | 90+ |
| Desktop, all three | 89 to 96 | No regression |
| Worst mobile LCP | 11.9s | Under 2.5s |
| Accessibility | 90 | 96+ |
| Token layer | Does not exist | Changing one token visibly changes every page |
| Responsive walk | Not done | All pages checked at 390, 768, 1280, 1920 |

## 9. Decisions

All settled 2026-08-04.

1. **Option A now, Option C as the target.** Approved.
2. **Plugins: keep, configure, remove later under C.** Approved.
3. **PageSpeed baseline.** Received, recorded in section 2.5.
4. **Responsive walk: all pages, fast pass.** Approved. Broad coverage, shallow
   fixes, on the basis that the GMD could land on any page.

The build plan is [PRD-design-tokens.md](PRD-design-tokens.md), target version
1.8.0.
