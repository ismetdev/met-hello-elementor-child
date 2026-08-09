# Staging checklist: v1.8.0

> **Still open, and now applies to 1.9.0.** 1.8.0 was never released. Staging
> is still on 1.7.2 and will receive 1.8.0 and 1.9.0 together whenever the
> next release ships. Every item below still needs doing.
>
> **Two items are now moot for migrated pages.** Setting Elementor's Global
> Colours and Global Fonts only affects pages Elementor still renders. Pages
> migrated under [PRD-block-system.md](PRD-block-system.md) get their
> typography and colour from `theme.json` instead, so do those two steps only
> for pages still on Elementor, and skip them entirely once phase 4 removes
> Elementor. See [DECISIONS D28](../DOCS/DECISIONS.md#d28).

What to do on live staging after the update lands, in order. This is the
part only you can do, since it needs the live site and DevTools.

## 1. Update and purge

1. Dashboard, Updates: update the theme to 1.8.0.
2. Purge the LiteSpeed cache, including Critical CSS. Do this every time
   after an update: see [D26](../DOCS/DECISIONS.md), the 1.7.1 bug was exactly
   a stale cached stylesheet.
3. Confirm on staging with a hard refresh (Ctrl+Shift+R), not from memory of
   local `v2`.

## 2. Footer social links: give them a name

**What's wrong:** Lighthouse flags "Links do not have a discernible name" on
every page. This is almost certainly the footer's social media icons, plain
icon links with no text a screen reader can announce.

**How to find it:**

1. Open any page, right-click a footer social icon (Facebook, LinkedIn,
   whichever), Inspect.
2. Look at the `<a>` tag. If it has no text inside and no `aria-label`, that's
   the problem.

**How to fix it, in Elementor:**

- If it is Elementor's own Social Icons widget: each icon has a "Screen
  Reader Text" or "Label" field per platform. Fill it in: "Facebook",
  "LinkedIn", and so on.
- If it is an Essential Addons or Ultimate Addons social widget: check its
  settings panel for the same kind of field, usually called "Accessible
  Text" or "ARIA Label".
- If neither offers a field, the icon needs a text label added, even a
  visually hidden one. Tell me which widget it is and I will write the CSS to
  hide it visually while a screen reader still reads it.

**Test:** Lighthouse Accessibility, "Links do not have a discernible name"
audit passes.

## 3. Desktop layout shift (CLS 0.096)

**What's wrong:** All three pages tested (homepage, Board of Directors, IKOP
Pharma) show the identical CLS value, 0.096, on desktop. Identical across
different page content means the cause is something all three pages share:
the header, the logo, or a hero image, not page-specific content.

**How to find it:**

1. Open the homepage in Chrome. DevTools, Performance tab.
2. Click record, reload the page, stop recording after it settles.
3. Look for a red "Layout Shift" entry near the top of the timeline. Click it.
4. DevTools names the specific element that moved.

Two likely causes to check first, since they are the most common source of
this exact symptom:

- **The logo.** If it is an SVG or a `<img>` without a `width` and `height`
  attribute, the browser does not know its size until it downloads, so the
  header height changes after paint and everything below jumps. Check in
  Elementor: Site Settings, or the header template's logo widget, whether a
  fixed width/height is set.
- **A hero image on the homepage** that loads without reserved space.

**Report back:** paste me the element DevTools names, and I will write the
CSS or point you to the Elementor setting that reserves its space.

**Test:** desktop CLS under 0.05 on all three pages.

## 4. Elementor Global Colours and Fonts (PRD step 10)

**Not optional. This is the only thing that reaches Elementor-authored
typography and colour.** Found while testing 1.8.0 locally: Elementor bakes
its Kit defaults into CSS scoped to each widget, which always beats the
theme's own stylesheet on specificity. So until this step is done, none of
the token colours or fonts show up on any Elementor page, no matter how
correct the code is. Do this after 1.8.0 is confirmed working, before
calling the demo ready.

1. Elementor, Site Settings, Global Colors: set Primary `#0E3B40`, Secondary
   `#B98A2E`, Text `#0F1419`, Accent `#C99A3A`. Add three custom colours:
   Education `#4A7BA8`, Infrastructure `#B98A2E`, Healthcare `#B5566F`.
2. Global Fonts: Primary **Inter**, not Geist. Geist is not in Elementor
   Free's font picker, and custom font upload is Pro only. Inter is already
   in the list and visually close; this is the accepted compromise for the
   demo. Secondary Instrument Serif, if it's in the list.
3. Layout, content width: 1200px.
4. Do not re-point existing widgets at these yet. That is later work. New
   widgets, and any widget still set to "Default", pick these up
   automatically, which covers most of what exists today.

**Test:** add a new heading widget to a scratch page. It should show Inter
and the petrol/gold colours with no manual setting. If it still shows Roboto
or the default green/blue, the widget likely has an explicit local style set,
not "Default", check its own Typography/Style panel.

## 5. Responsive walk (PRD step 12)

Every page, at these four widths in Chrome DevTools device toolbar: 390px,
768px, 1280px, 1920px. Fix only what is clearly broken: horizontal scroll,
overlapping text, text too small to read, an image cut off, a grid that
collapses wrong. Log anything else, do not fix it now.

Send me a list of what you find. Some of it I can fix from the theme side (a
base rule in `elementor-base.css`), some needs an Elementor edit on the
specific page, and I will tell you which is which as you report them.

## 6. Re-measure

Run [pagespeed.web.dev](https://pagespeed.web.dev) again on the homepage,
`/board-of-directors/`, and `/business/ikop-pharma-sdn-bhd/`, mobile and
desktop. Paste me the results next to the baseline in
[PRD-design-tokens.md](PRD-design-tokens.md) section 6.
