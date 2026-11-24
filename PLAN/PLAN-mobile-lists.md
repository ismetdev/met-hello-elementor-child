# Plan: tenders and careers lists on phone (stage 1 of 3)

Status: plan and mockup only. No theme or plugin change. Mockup: [MOCKUP-mobile-lists.html](MOCKUP-mobile-lists.html).

## Problem

The MetCPT shortcodes `[tenders_list]` and `[careers_list]` print a 6-column table. Below 768px MetCPT sets `min-width: 640px` and `overflow-x: auto`, so a 313 to 353px phone scrolls sideways inside the table. Plan: on phone and iPad portrait, show each row as a card. Desktop keeps the table.

## 1. Card fields and order

Only fields that already exist in the table markup are used, so no plugin change is needed.

**Tender card**

| Row | Content | Why |
|---|---|---|
| 1 | Ref no. (small, mono), status badge on the right | Identifies the tender, status is the first thing people check |
| 2 | Title, 17px semibold, 4 lines at most | The main content. 200 characters fits in 4 lines at 375px, with "..." after that. Full title is on the detail page |
| 3 | Excerpt, 2 lines, only if one exists | Same text as the table shows |
| 4 | Issuer and Location, two columns with small labels | Secondary facts |
| 5 | "Closes 10 Aug 2026" (gold text) and the document button | The deadline and the action sit together |

Not shown: tender category. It is not in the table markup, so adding it needs a plugin change.

**Career card**

| Row | Content |
|---|---|
| 1 | Status badge, "Closes 10 Aug 2026" on the right |
| 2 | Position title, 17px semibold, 3 lines at most |
| 3 | Company, and the type chip (Full Time, Contract, Internship) on the right |
| 4 | Department and Location, two columns with small labels |

The whole card is one tap target (the title link stretches over the card). On tender cards the document button is a separate link above it.

## 2. Breakpoints

| Width | Layout |
|---|---|
| 768px and below | Cards. This is the same point where MetCPT starts its sideways scroll today |
| 600 to 768px (iPad portrait) | Cards in 2 columns |
| Below 600px (phone) | Cards in 1 column |
| Below 480px | Filter tabs wrap to 2 x 2 so each stays 44px tall |
| 769px and above (iPad landscape, desktop) | Table stays as it is |

To check on a real iPad: 820px and 1024px wide. The 6-column table is tight at 769px. If it looks cramped, move the card breakpoint to 900px. This is an open decision.

## 3. States

- **Open:** green badge with dot and the word "Open". Date reads "Closes ...".
- **Closing soon** (7 days or less, the MetCPT rule): gold badge. Same card.
- **Closed:** grey badge, muted title and date, date reads "Closed ...", grey button. Card background a little lighter.
- **Empty:** "No tenders found." or "No positions found." in a dashed card, with a "Clear search and filter" button (proposed, owner can drop it). Today this message only prints when there are no records at all. After a search or tab with no match, the list is just blank. A small script in the theme will show the message, on desktop too.
- **Long title (200 characters):** clamped to 4 lines on tenders and 3 on careers, wraps on long words, never makes the card wider.
- **No document:** the button says "Details", as MetCPT does now.

Status is never shown by colour alone: each badge has a word and a dot.

## 4. Filters and buttons on phone

- Search: full width, 48px high, 16px text (stops iOS zoom on focus).
- Tabs (All, Open, Closing Soon, Closed): a segmented control, 4 across from 480px, 2 x 2 below that. Each tab is 44px high.
- "Showing 7 tenders": under the tabs, announced to screen readers when it changes.
- The second "Showing ..." line at the bottom is hidden on cards, it repeats the first.

## 5. Accessibility

- Touch targets 44px or more: tabs, search, document button, whole card.
- Contrast (checked): text #0F1419 on white 18.5:1; secondary #5A6168 on #F7F3EC 5.7:1; accent text #85621E on white 5.6:1; badges 5.6 to 6.9:1; white on #85621E button 5.6:1. All pass 4.5:1.
- Focus: 2px #85621E outline, 2px offset, on search, tabs, buttons, and on the whole card when its title link has focus.
- Screen readers: some browsers drop table meaning when CSS turns rows into blocks. The theme script sets `role="list"` and `role="listitem"` while the card layout is active, and removes them on desktop.
- No animation. Reduced motion is respected.

## 6. Technical approach (theme only, plugin untouched)

- New file `assets/css/mobile-lists.css`, written as `@media (max-width: 768px)` rules over the existing MetCPT classes (`.tdl-*`, `.crl-*`). The mockup uses the same rules as container queries.
- New file `assets/js/mobile-lists.js`, about 40 lines: empty message (watches the count text), the roles above, the "Clear" button.
- Load both only on pages that hold `[tenders_list]` or `[careers_list]`. `inc/listing.php` already has a helper that finds a shortcode in page content or Elementor data. Not gated by `met_hello_child_is_styled_view()`.
- New file `inc/mobile-lists.php` for the enqueue. Theme version bump to 1.12.6 (or 1.13.0) at stage 2.
- Row labels ("Issuer", "Closes", "Closed") are CSS `::before` text. If the site is translated later, these need a filter.

## 7. Things to confirm

1. **Template B.** The main-pilot copy has `metcpt_tenders_template = b`. Template B is a different layout (`.tdp-*`) and is already responsive. This plan only touches the table (Template A). If main uses B, the tenders page may not have the problem. Careers has no template switch, so it does.
2. Card breakpoint: 768px (proposed) or 900px.
3. Keep the "Clear search and filter" button and the empty message on desktop?
4. Excerpt on cards: keep 2 lines (proposed) or hide on phone?
5. The data in the mockup is from the local V2 copy (sample tenders and jobs), not the live pages, which could not be reached.
