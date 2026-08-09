# CLAUDE.md

Rules for Claude Code in this repository. Read `DOCS/` for the full context.

## Model: plan on the default model, code on Sonnet 5

All coding runs on **Sonnet 5**. Planning, research and review run on whatever
model the session starts with.

After the user approves a plan, **stop before writing any code** and say:

> Plan approved. Switch to Sonnet 5 now: run `/model sonnet`. Tell me when you
> have, and I will start.

Do not edit project files until the user confirms the switch. If the user says to
proceed anyway, proceed and note which model is in use.

"Coding" means writing or editing project files: PHP, CSS, JS, config, templates.
It does not include doc edits in `DOCS/` that are part of planning.

Full reasoning: [DOCS/DECISIONS.md](DOCS/DECISIONS.md#d22).

## Writing

All output follows [DOCS/WRITING_RULES.md](DOCS/WRITING_RULES.md): no em dash, not
verbose, clear and complete, no bombastic words, simple English. This covers chat
replies, docs, commit messages, PRDs and plans. Commit messages also follow
conventional commit format.

## Before editing

This repo is worked on from two machines. Run `git pull --ff-only origin main`
first. When releasing, push `main` before pushing the `vX.Y.Z` tag.

## Project context, and how much of it to read

Read only what the task needs. None of these load automatically.

| File | What it is | How to read it |
|---|---|---|
| [DOCS/STATE.md](DOCS/STATE.md) | Current version, layout, scope boundary, open items, release steps | Whole file. It is short and stays short. Start here |
| [DOCS/DECISIONS.md](DOCS/DECISIONS.md) | Why the code is the way it is | Search for the topic, then read that entry. Read it whole only when reviewing the design |
| [DOCS/PROJECT_LOG.md](DOCS/PROJECT_LOG.md) | What happened and when | **The newest entry whole, then stop.** Older entries are archived, with an index at the bottom of the file. Read further only when the task is about older history |
| [DOCS/WRITING_RULES.md](DOCS/WRITING_RULES.md) | Writing standard | Whole file, it is small |
| [DOCS/DEPLOY-TO-STAGING.md](DOCS/DEPLOY-TO-STAGING.md) | Moving local work to live staging | Only when deploying. The database is never copied |

When PROJECT_LOG.md passes about 200 lines, move everything older than the
current version era into `DOCS/archive/PROJECT_LOG-<year>.md`, append to that
year's file rather than making a second one, and leave an index table at the
bottom of the live log saying what each archived period covers. The newest
entries stay cheap to reach. Amended 2026-08-07, see
[DECISIONS D23](DOCS/DECISIONS.md#d23): the old "older than the current year"
trigger could never fire on a project this fast.

## Theme specifics

- WordPress requires the template hierarchy files, `style.css`, `functions.php`
  and `screenshot.png` at the theme root. Do not move them. `error-403.php`
  stays too, because deployed `.htaccess` files reference its path.
- `style.css` holds the theme header only and is not enqueued. Design CSS lives
  in `assets/css/theme.css`.
- `functions.php` is a bootstrap. Behaviour goes in `inc/`.
- `met_hello_child_is_styled_view()` in `inc/setup.php` gates the stylesheet, the
  font preconnect hints, and the full-width body class. Widening it widens all
  three, and risks colliding with the MetCPT plugin.
- Bump the version in both `style.css` and `functions.php`, and add a
  `readme.txt` changelog entry, before tagging a release.
