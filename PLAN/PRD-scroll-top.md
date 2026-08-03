# PRD: Scroll to Top button

Status: draft, awaiting approval
Author: Ismet, with Claude
Date: 2026-08-03
Target version: 1.7.0
Depends on: nothing. Independent of the Page Hero work in
[PRD-page-hero.md](PRD-page-hero.md).

## 1. Goal

Give readers a one-click way back to the top of any page on the site.

The corporate pages are long. After the Page Hero work the site reads
consistently, but a reader who scrolls to the bottom of `/board-of-directors/`
or `/rise2030-strategy-blueprint/` has no way back except a long scroll or the
browser chrome. This closes that gap.

### Success measures

| Measure | Now | Target |
|---|---|---|
| Pages with a way back to the top | 0 | Every page |
| Site owner can change the button without a code release | No | Yes, colour and side, from the Customizer |
| Front-end JavaScript shipped by this theme | 0 bytes | Under 1KB, deferred |

## 2. Scope

### In

- A floating button on **every page of the site**: homepage, all Elementor
  Pages, blog Posts, archives, search, author, 404. This is the first sitewide
  feature this theme has shipped.
- Hidden at the top of the page. Fades in once the reader scrolls past roughly
  one screen. Returns to the top on click.
- Outlined style: paper fill, hairline border, coloured arrow, fills solid on
  hover.
- A **Customizer section** with three settings:
  - On / off.
  - Position: bottom right (default) or bottom left.
  - Colour: a colour picker driving the border, the arrow, and the hover fill.
- Keyboard, screen reader, touch target and reduced-motion support.
- A performance budget, same discipline as the Page Hero release.

### Out

- Any scroll progress indicator, ring or percentage. Plain arrow only.
- A separate "hide on mobile" toggle. The button shows on all screen sizes.
  Add it later only if it proves to be in the way.
- Customizer control over size, offset from the edge, icon shape or fade speed.
  Those stay in CSS. Three settings is the useful set; more is clutter.
- Smooth-scroll behaviour for anchor links elsewhere on the site.
- Any change to the Page Hero, the styled views, the Elementor header and
  footer, or the MetCPT plugin.

## 3. Approach

### 3.1 Its own stylesheet, because `theme.css` is not sitewide

This is the constraint that shapes the whole feature.
[assets/css/theme.css](../assets/css/theme.css) is gated by
`met_hello_child_is_styled_view()` and `met_hello_child_page_has_hero()`, so on
the homepage and most Elementor Pages it is not loaded at all. Its `:root`
tokens are therefore unavailable sitewide.

So the button ships **self-contained**:

| File | Loads | Size target |
|---|---|---|
| `assets/css/scroll-top.css` | Sitewide | Under 1KB |
| `assets/js/scroll-top.js` | Sitewide, deferred | Under 1KB |
| `assets/js/scroll-top-customizer.js` | Customizer preview only, never front end | Not counted |

`scroll-top.css` hardcodes its own values and reads the Customizer colour from
three CSS custom properties printed inline. It never references a token defined
in `theme.css`. The two stylesheets stay independent, and either can load
without the other.

### 3.2 Why this earns an exception to D2

[DECISIONS.md D2](../DOCS/DECISIONS.md) says Elementor keeps the chrome and the
theme keeps the content. A floating button is chrome, so this is an exception,
and the PRD records why:

- Elementor free has no Back to Top widget. It is a Pro-only feature.
- Hello Elementor 3.4.9 does not provide one either. Checked 2026-08-03.
- Adding it per page in Elementor would mean repeating it on every page and
  keeping them in sync by hand, which is the exact problem the Page Hero work
  just solved.

A new decision entry, D26, will record this. `met_hello_child_is_styled_view()`
is not touched. The button gets its own trivial gate, one Customizer boolean,
so the existing scope boundary stays exactly as it is.

### 3.3 Rendering

The markup prints on `wp_footer`, which fires on Elementor Canvas
(`canvas.php:52`) and on Full Width via `get_footer()`. Verified against the
installed plugin on 2026-08-03. One hook covers the whole site.

Markup is a real `<button type="button">`, not a link, since it performs an
action rather than navigating:

```html
<button type="button" class="met-to-top" aria-label="Scroll to top">
  <svg aria-hidden="true" focusable="false">…</svg>
</button>
```

The class prefix `met-to-top` is new and appears nowhere in the theme,
Elementor, or Hello Elementor, so there is nothing to collide with.

### 3.4 Show and hide

A `scroll` listener registered `{ passive: true }` and throttled with
`requestAnimationFrame`, toggling one class at a threshold of `window.innerHeight`.

`IntersectionObserver` on a sentinel element would avoid scroll work on the main
thread entirely and was the first choice. It was dropped because it needs a
sentinel node injected at the top of `<body>`, and this theme does not own the
top of the body on Elementor pages. A passive, rAF-throttled listener toggling a
single class is a known-cheap pattern, and it costs nothing on browsers that
never scroll the page.

Visibility is handled with `opacity` plus `visibility`, not `display`. That
gives a fade, and `visibility: hidden` removes the button from the tab order and
the accessibility tree while it is hidden, with no ARIA bookkeeping in JS.

### 3.5 Customizer

One section, `met_hello_child_scroll_top`, titled "Scroll to Top". Three
settings, each with a sanitiser, following WordPress practice:

| Setting | Control | Default | Sanitiser |
|---|---|---|---|
| `met_hello_child_scroll_top_enabled` | Checkbox | On | Cast to bool |
| `met_hello_child_scroll_top_position` | Radio: right / left | `right` | Whitelist check |
| `met_hello_child_scroll_top_colour` | `WP_Customize_Color_Control` | `#0E3B40` (petrol) | `sanitize_hex_color` |

All three use `transport => postMessage` with
`assets/js/scroll-top-customizer.js` updating the preview live, so changing the
colour does not reload the page. That file is enqueued on
`customize_preview_init` only and never reaches a visitor.

The colour drives three CSS custom properties printed inline in `wp_head` via
`wp_add_inline_style`, about 80 bytes:

```css
.met-to-top{--met-tt-accent:#0E3B40;--met-tt-fill:#FFFFFF;--met-tt-on-accent:#FFFFFF;}
```

Border and arrow use `--met-tt-accent`. On hover the background becomes
`--met-tt-accent` and the arrow becomes `--met-tt-on-accent`. One picker, a
coherent result, no way to configure an unreadable combination.

Position is a body-level class rather than a fourth property, so the CSS stays
static and cacheable.

### 3.6 Accessibility

| Concern | Handling |
|---|---|
| Screen readers | Translatable `aria-label`, `met-hello-child` text domain. The SVG is `aria-hidden`. |
| Keyboard | A real `<button>`, so it is focusable and activates on Enter and Space with no JS. Visible `:focus-visible` ring, matching the gold ring used elsewhere. |
| Hidden state | `visibility: hidden` keeps it out of the tab order until it appears. |
| Touch target | 48px, above the 44px minimum. |
| Motion | `prefers-reduced-motion: reduce` switches the scroll from `smooth` to instant and removes the fade transition. |
| Contrast | The default petrol on paper passes AA. A custom colour is the owner's choice; step 7 notes this in the Customizer description rather than trying to police it. |

### 3.7 Performance

| Metric | Rule |
|---|---|
| New JavaScript | Under 1KB uncompressed, vanilla, no jQuery, loaded with `defer`. |
| New CSS | Under 1KB uncompressed, plus about 80 bytes of inline custom properties. |
| New HTTP requests | 2 sitewide. Both are static and cacheable. |
| Scroll cost | One passive listener, rAF-throttled, doing one `classList.toggle`. No layout reads in the handler beyond a cached `innerHeight`. |
| LCP / CLS | No change expected. The button is `position: fixed`, so it is outside normal flow and cannot shift content. It is never the LCP element. |
| When disabled | The Customizer toggle is checked before enqueueing. Off means zero CSS, zero JS, zero markup. |

The honest cost: this adds two requests to **every** page on the site, including
pages that load nothing from this theme today. That is the price of a sitewide
feature and it is why both files have a hard 1KB budget. Step 8 measures it.

### 3.8 Files

| File | Change |
|---|---|
| `inc/scroll-top.php` | New. Customizer registration, sanitisers, inline CSS, enqueue, `wp_footer` render. |
| `assets/css/scroll-top.css` | New. Self-contained button styles. |
| `assets/js/scroll-top.js` | New. Show, hide, scroll to top. |
| `assets/js/scroll-top-customizer.js` | New. Live preview, Customizer only. |
| `functions.php` | One `require_once`. |

Nothing existing is modified apart from that single line in `functions.php`.

## 4. Steps

Each step is checkable on the local `v2` site.

1. Add `inc/scroll-top.php` with the three Customizer settings, their
   sanitisers and the section. Wire into `functions.php`.
   *Check: the "Scroll to Top" section appears in the Customizer, the settings
   save, and nothing renders on the front end yet.*
2. Add `assets/css/scroll-top.css` and the sitewide enqueue, gated on the
   enabled setting.
   *Check: the stylesheet loads on the homepage, an Elementor Page, and a blog
   Post. Unchecking the toggle removes it everywhere.*
3. Add the `wp_footer` markup and the inline custom properties.
   *Check: the button appears bottom right on every page type including
   Elementor Canvas. Changing the Customizer colour changes it after a save.*
4. Add `assets/js/scroll-top.js`: the passive rAF-throttled listener and the
   click handler.
   *Check: hidden at the top, fades in after about one screen, returns to the
   top on click. Works on the homepage, a hero Page, a blog Post and a MetCPT
   listing page.*
5. Add `assets/js/scroll-top-customizer.js` and switch the settings to
   `postMessage`.
   *Check: colour and position update live in the Customizer preview with no
   reload. Confirm the file does not load for a logged-out visitor.*
6. Accessibility pass.
   *Check: reachable by Tab only once visible, activates on Enter and Space,
   visible focus ring, `aria-label` announced, 48px target, reduced-motion
   setting removes the animation.*
7. Responsive and collision pass at 320, 768, 1024 and 1920px, on both
   positions.
   *Check: never covers page content, footer links or the MetCPT listings. Sits
   above Elementor content but below any Elementor menu or popup. No horizontal
   overflow.*
8. Performance check against section 3.7.
   *Check: both files under 1KB, Lighthouse mobile on the homepage before and
   after shows no LCP or CLS regression. Record the numbers.*
9. Run `composer install` then `phpcs`.
   *Check: clean, no new findings.*
10. Version bump in `style.css` and `functions.php`, `readme.txt` changelog
    entry, DOCS update: STATE, a D26 entry in DECISIONS for the sitewide
    exception, a PROJECT_LOG entry.
11. Tag `v1.7.0`, let the release pipeline build, update the theme on v2
    staging, then verify on the live site.

## 5. Risks

| Risk | What to do |
|---|---|
| This is the theme's first sitewide asset, so a mistake here affects every page rather than a gated subset | Both files are tiny and self-contained, and the Customizer toggle is a kill switch that needs no release. Step 2 explicitly tests that unchecking it removes everything. |
| `z-index` collision with an Elementor menu, popup or sticky header | Step 7 checks it. The button starts at `z-index: 9990`, below Elementor's own overlay range. Tune down, not up, if anything conflicts. |
| A custom Customizer colour produces an unreadable button | Out of scope to police. The control description states the default is the brand petrol. The fill and arrow colours are derived, so only the accent can be changed. |
| Inline CSS is cached by LiteSpeed, so a colour change looks like it did not apply | Same as the existing maintenance-toggle note in `readme.txt`. Add one line to the changelog telling the owner to purge all after changing the setting. |
| The scroll listener costs main-thread time on long pages | Passive plus rAF-throttled, doing one class toggle. Measured in step 8. |
| Adding chrome to the theme sets a precedent that erodes D2 | D26 records the exception and its three specific reasons. It is an exception, not a new rule. |

## 6. Done when

1. The button appears on every page of the live staging site, hidden at the top
   and appearing after about one screen of scrolling.
2. Colour, side and on/off are all changeable from the Customizer with no code
   release, and preview live.
3. Turning it off leaves no CSS, JS or markup on any page.
4. Keyboard, screen reader and reduced-motion checks in step 6 all pass.
5. Both files are under 1KB and the homepage shows no LCP or CLS regression,
   with numbers recorded in PROJECT_LOG.
6. `phpcs` is clean and 1.7.0 is tagged and installed on staging.
7. No existing page, template or the Page Hero behaves any differently.
