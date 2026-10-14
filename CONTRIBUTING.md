# Contributing to Wikimedia Maroc

Start with the [English team guide](docs/TEAM_GUIDE_EN.md) or [الدليل العربي](docs/TEAM_GUIDE_AR.md), then complete the [onboarding checklist](docs/ONBOARDING_CHECKLIST.md).

1. Accept the repository invitation and choose an Issue with the Team Lead. Use your own GitHub account and your own website account; these are separate systems.
2. Create a personal task branch from updated `main`. Follow [GIT_WORKFLOW.md](docs/GIT_WORKFLOW.md). Never force push to `main` or merge without approval.
3. Make a small scoped change using [CODING_STYLE.md](docs/CODING_STYLE.md). Coordinate shared layout/CSS/schema files.
4. Run PHP/JS syntax checks and relevant integration/UI tests. Integration tests require a separate `_test` database and a server using that same database. Follow the exact two-terminal commands in the team guide.
5. If CSS changed, rebuild production CSS and commit source plus output. Do not commit dependencies/runtime files.
6. Review `git status`, the staged file list, and `git diff --cached`. Never include `.env`, credentials, tokens, passwords/hashes, real database dumps, session/cookie files, logs, or private uploads.
7. Push your feature branch and open a PR targeting `main`. Fill in the template with actual tests, limitations, and sanitized screenshots for UI changes. Request Team Lead review; push review fixes to the same branch.

Use the bug/feature Issue templates. Report security-sensitive details privately to the repository owner rather than posting credentials or a live exploit against a shared environment in a public Issue. Do not fabricate team identities or create real accounts for other people without their agreed details.

Before requesting review, confirm: existing functionality still works, backend role/CSRF checks remain, FR/AR and mobile are checked when relevant, test results are truthful, and the PR contains no unrelated project files.
