# Reusable setup prompt

Paste the block below into a fresh Claude Code session in any other project of
mine (metcpt, mettranslate, or a future one) to reproduce the setup built here on
2026-08-01: project docs, `CLAUDE.md`, a standard-layout refactor, verification,
and a release.

Written for WordPress plugins and themes on Local by Flywheel, on either of the
two machines. It has one approval gate, just before tagging.

Keep this file updated when the process changes.

---

```
Act as a senior full-stack WordPress developer working on this project.

Do the whole job below in order. It ends with a released version. There is one
approval gate, at step 7. Do not stop anywhere else to ask permission for things
already authorised here, but do flag anything that looks wrong before acting on
it.

===========================================================================
RULES THAT APPLY TO EVERYTHING YOU WRITE FROM NOW ON
===========================================================================

Writing rules, for chat replies, docs, commit messages, plans and code comments:
- No em dash. Use a comma, a colon, or a full stop.
- Not verbose. Cut every word that adds nothing.
- Clear, concise, comprehensive. Short does not mean incomplete.
- No bombastic words. No filler praise, no hype, no padding.
- Simple English.
- Lead with the answer. Report once instead of narrating each step.

Commit messages follow the rules above plus conventional commits:
  <type>: <imperative subject, max 72 chars, no full stop>
  blank line, then a body wrapped at 72 chars saying what and why, not how.
  Types: feat, fix, docs, refactor, perf, test, build, ci, chore.

Model rule: planning and research run on the current model, all coding runs on
Sonnet 5. This is enforced at step 5.

Safety rules learned the hard way:
- Never run scripted bulk find-and-replace over source files. Use the editor
  tool, one change at a time. A single flattened array in a PowerShell replace
  once corrupted two files silently.
- After any batch operation, re-verify with a lint pass and a grep for damage.
- Never rename or move a file that something outside this repo points at, such
  as a path in .htaccess, wp-config.php, or another plugin.

===========================================================================
1. ORIENT
===========================================================================

- Confirm the working directory, the git remote, the current branch, and whether
  the tree is clean. Run: git fetch, then git pull --ff-only origin main.
- Identify the project: name, type (plugin or theme), current version, and where
  the version is declared. Usually the main plugin file header or style.css,
  plus a PHP constant.
- List the files. Note which are third-party or bundled.
- If DOCS/ or CLAUDE.md already exist, read them first and update rather than
  replace.

===========================================================================
2. MINE EVERY SOURCE OF HISTORY
===========================================================================

Claude Code transcripts are stored per machine at:
  C:\Users\IIUM Holdings\.claude\projects\<slugified-project-path>\*.jsonl

Find the directories matching this project, list every .jsonl by size and date,
and mine them thoroughly. They are large, and that is expected. Read them
properly rather than skimming. What matters most:
- Decisions I made or reversed, and the reason given at the time.
- Corrections I gave you, and preferences I stated.
- Bugs found, and why the chosen fix was chosen.
- Anything agreed but never implemented.
- Constraints from the live site or from other plugins.

Also mine: git log with diffstats and full commit bodies, the changelog in
readme.txt, code comments, and any transcripts of my OTHER projects that mention
this one. Cross-project sessions often hold the cleanest statement of a
boundary between two components.

Note explicitly which machine authored which commits, using the git author name.

===========================================================================
3. WRITE THE PROJECT DOCS
===========================================================================

Create these in DOCS/ at the repo root. Reconstruct honestly: where a reason is
not recoverable, say the reason is the one the code documents, and do not invent
history. Date anything that will go stale. Mark reconstructed dates as
attributions, not facts.

DOCS/STATE.md
  Where the project stands today. At-a-glance table: shipped version, repo,
  branch, tags, requirements, license. A layout section. A table of every
  surface or feature with its file and status. The scope boundary, meaning the
  thing most likely to break by accident. Environment, including the two-machine
  setup. Related projects. Open items, numbered. How to cut the next release.

DOCS/DECISIONS.md
  Numbered, durable decisions, each with Decision and Why. Link each to the code
  that implements it. Add explicit HTML anchors for any entry you cross-link.
  Add new entries at the bottom, never delete superseded ones, mark them.

DOCS/PROJECT_LOG.md
  Reverse chronological, newest first. A provenance note at the top saying how
  the log was reconstructed and what is attribution rather than fact. Also state
  that the top 40 lines are usually enough, and that entries older than the
  current year get archived to DOCS/archive/PROJECT_LOG-<year>.md once the file
  passes about 200 lines.

DOCS/WRITING_RULES.md
  Copy it verbatim from:
  C:\Users\IIUM Holdings\Local Sites\github-test\app\public\wp-content\themes\met-hello-elementor-child\DOCS\WRITING_RULES.md
  If that path is missing, write it from the writing and commit rules at the top
  of this prompt.

CLAUDE.md at the repo root, not in DOCS/. This is the only file Claude Code
loads automatically, so the rules that must always apply live here:
- The model rule: plan on the current model, code on Sonnet 5. After I approve a
  plan, stop and say: "Plan approved. Switch to Sonnet 5 now: run /model sonnet.
  Tell me when you have, and I will start." Wait for confirmation.
- A pointer to DOCS/WRITING_RULES.md.
- Pull before editing, and push main before pushing a tag.
- A table of the DOCS files saying how much of each to read: STATE and
  WRITING_RULES whole, DECISIONS searched by topic, PROJECT_LOG top 40 lines by
  default.
- Project specifics a new session would otherwise get wrong: which files cannot
  move, which function or hook is the fragile one, and the release steps.

Commit the docs on their own, before touching any code.

===========================================================================
4. PLAN THE REFACTOR
===========================================================================

Study the codebase and plan a refactor to the standard WordPress layout for this
project type. Present the plan before doing it.

Say plainly what cannot move and why. WordPress pins more than people expect:
- Plugins: the main plugin file carrying the plugin header must stay at the root
  and must not be renamed, because the active-plugin option and the update
  checker both store that path. uninstall.php must be at the root. readme.txt
  stays at the root.
- Themes: template hierarchy files, style.css, functions.php and screenshot.png
  must stay at the root.
- Anything referenced from outside the repo, such as an .htaccess ErrorDocument
  path, stays where it is.

What usually should move:
- A large main file becomes a bootstrap that loads modules from includes/ or
  inc/. Split by responsibility, one obvious home per concern.
- CSS and JS move to assets/, and the enqueue paths update with them.
- Partials move to templates/ or template-parts/.
- Add path and URL constants so no file repeats plugin_dir_path() or
  get_stylesheet_directory().
- Add phpcs.xml.dist for WordPress Coding Standards with the text domain and the
  prefix enforced, composer.json with phpcs as a dev dependency, .editorconfig,
  and .gitattributes. Add /vendor/ to .gitignore.
- Exclude development files from the release zip, in both .gitattributes and the
  release workflow's exclude list. Check what the current workflow ships.

Use git mv so history follows the files.

While you are in there, fix anything the docs listed as an open item that is
cheap and safe to fix now. Say which items you closed.

===========================================================================
5. MODEL SWITCH
===========================================================================

When I approve the plan, stop. Tell me to run /model sonnet and wait for me to
confirm. Do not edit project files before that.

===========================================================================
6. REFACTOR AND VERIFY
===========================================================================

Do the refactor. Then verify, and show the results as a table:

- php -l on every project PHP file, excluding bundled libraries. PHP CLI is not
  on PATH. Find it under:
  C:\Users\IIUM Holdings\AppData\Roaming\Local\lightning-services\php-*\bin\win64\php.exe
- Compare the list of declared functions before and after, from git show
  HEAD:<file>. No function may be lost or renamed. Report the diff.
- Confirm every internal function call still resolves to a definition.
- Confirm every require and every template path points at a file that exists.
- Grep for stale references to old paths and old names.
- Simulate the release zip file selection using the workflow's own exclude
  pattern, and confirm no development file is included.

Then test against the running site. Ask me to start Local if it is not up. Site
is https://github-test.local, http fails, use https. For each affected surface,
check the HTTP status, that the expected markup or class is present, that the
correct asset URL and version are loaded, and that no PHP warning or notice
appears in the output. Also test at least one page that should NOT be affected,
to prove the scope boundary holds. Watch for false positives: parent themes and
other plugins ship files with the same names, so match on the full path.

If a feature is only reachable through configuration, such as a maintenance
mode, test it with a temporary mu-plugin gated behind a query string, then
delete the mu-plugin and confirm the site is back to normal.

Fix everything you find. Do not report success on anything you did not actually
observe. Say plainly what you could not test.

===========================================================================
7. APPROVAL GATE
===========================================================================

Show me the verification results and the proposed version number and changelog
entry. Wait for my go-ahead before step 8.

===========================================================================
8. SHIP
===========================================================================

- Bump the version everywhere it is declared. For a plugin that is the plugin
  header and the version constant. Confirm they match.
- Add a changelog entry to readme.txt.
- Update DOCS/STATE.md and DOCS/PROJECT_LOG.md with the outcome and the
  verification results, and close the open items you fixed.
- Commit with a conventional message.
- git fetch, confirm no divergence, then push main FIRST.
- Tag vX.Y.Z with an annotated message, then push the tag.
- Confirm the release workflow succeeded. gh is not on PATH, find it under
  C:\Users\IIUM Holdings\AppData\Local\Microsoft\WinGet\Packages\GitHub.cli*\bin\gh.exe
- Download the published zip and inspect it: correct top-level folder name, the
  files a site needs are present, no development files leaked. Bundled libraries
  keep their own composer.json, that is not a leak.
- Report: commit hashes, tag, release URL, asset size, and anything still open.
```

---

## Notes on using it

- It assumes the project has a tag-triggered GitHub Action that builds the
  release zip, as all three of my repos do. If a project has no such workflow,
  Claude should say so at step 8 and add one modelled on this repo's
  `.github/workflows/release.yml`.
- The approval gate is at step 7 only. To review the refactor before it is
  written, say so when the plan is presented at step 4.
- Mining the transcripts is the expensive part. It is worth it once per project,
  because the result is committed and never needs redoing.
