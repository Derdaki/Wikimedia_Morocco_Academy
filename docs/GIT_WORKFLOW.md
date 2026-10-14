# Five-Person Git and GitHub Workflow

Git tracks source-code history locally. GitHub hosts the shared repository and reviews. Neither synchronizes your local MySQL database, `.env`, session files, or uploads.

## 1. Repository access

The Team Lead opens the repository on GitHub -> **Settings -> Collaborators -> Add people**, selects each contributor's actual GitHub username, and sends an invitation. Each person accepts the invitation while signed into their own account. These are documented [GitHub collaborator invitation steps](https://docs.github.com/en/repositories/managing-your-repositorys-settings-and-features/repository-access-and-collaboration/inviting-collaborators-to-a-personal-repository). Adding a collaborator does not create a website administrator.

This is currently a personal-account repository: do not confuse GitHub collaborator access with the application's Editor/Super Administrator roles. The lead should configure available branch protection/rules for `main` to require PR review and disallow force pushes; the guides do not automatically change repository settings. Do not share GitHub credentials. HTTPS pushes should use Git Credential Manager's browser authentication, not a token embedded in a URL.

## 2. Branch ownership

| Branch | Purpose / owner |
| --- | --- |
| `main` | Reviewed, stable integration; Team Lead coordinates merges |
| `feature/homepage` | Member 2: public layout and responsive work |
| `feature/events` | Member 3: event/project interface work |
| `feature/dashboard` | Member 4: dashboard forms and tables |
| `feature/translations` | Member 5: French/Arabic review |
| `feature/backend-task-name` | Member 1: a specific backend/database task |

Use one branch per task/person, not one shared branch that everyone rewrites. Later tasks can use unique suffixes, for example `feature/events-filter-feedback`. Agent-generated work may use the `codex/` prefix. Agree who owns shared files such as `resources/css/app.css`, `routes/web.php`, and layouts before editing them.

## 3. Start a task

Open PowerShell inside **this repository only**, with no unfinished changes:

```powershell
git status
git switch main
git pull origin main
git switch -c feature/my-task
```

`switch main` selects the stable branch, `pull` downloads and integrates the latest work, and `switch -c` creates your task branch. Replace `feature/my-task` with your agreed name. If the branch already exists, use `git switch feature/my-task`, without `-c`.

If `git status` shows unfinished work, do not switch blindly. Commit the intended work on its current branch, or ask the lead for help. Do not stash secrets: Git stash also stores content in Git objects. Never use `git reset --hard` or discard changes just to make a command succeed.

Make a small change, test it, and compare the diff:

```powershell
git diff
git status
git add .
git diff --cached --name-only
git diff --cached
git commit -m "Describe the completed task"
git push -u origin feature/my-task
```

`add .` stages changes below the current directory; **only use it from the isolated project root after reviewing `status`**. Explicit paths are safer when other changes are present, e.g. `git add app/Views/public/home.php`. Check the staged list and content before committing. To unstage an unintended file without deleting it: `git restore --staged -- path/to/file`.

A commit is a local snapshot with a message. Push uploads your branch's commits; `-u` remembers its remote tracking branch for later `git push`. A push is not a merge, deployment, database migration, or synchronization of dashboard content.

Never stage `.env`, credentials, tokens, database dumps with real data, password hashes, uploads, session/cookie files, runtime logs/cache, or `node_modules`. `.env.example` must contain placeholders only. `git check-ignore .env` should confirm local configuration is ignored. If a secret was already committed, removing it in a later commit is not enough: tell the lead privately and rotate it.

## 4. Open and review a Pull Request

1. On GitHub, use the pushed branch's **Compare & pull request** action or Pull requests -> New pull request.
2. Choose **base `main`** and **compare your feature branch**. Verify the diff contains only your task.
3. Fill in the PR template: purpose, linked Issue, changes, tests actually run, screenshots for UI changes, and limitations. Never include private credentials/data.
4. Request review from the Team Lead and, where useful, the owner of the affected files. Do not invent reviewer usernames; ask the team.
5. Respond to review comments. Commit fixes to the **same branch** and run `git push`; the PR updates automatically.
6. The lead merges only after approval and passing agreed checks. Do not self-merge into `main` without approval. No automatic merging is configured by this guide.
7. After merging, synchronize locally:

```powershell
git switch main
git pull origin main
```

If the merged change includes SQL schema changes, read its migration notes and apply the migration to your own database. Git pull does not execute SQL. Existing dashboard data remains local. Start a new branch for the next task.

## 5. Resolve a merge conflict without force pushing

To bring updated `main` into your feature branch, first commit your current intended work:

```powershell
git switch feature/my-task
git fetch origin
git merge origin/main
```

If Git reports a conflict:

1. Run `git status` to identify conflicted files. Open them in VS Code's merge editor.
2. Read both changes with the teammate responsible for the other code. Keep the combined correct behavior; do not click Accept Current/Incoming without understanding it.
3. Remove markers such as `<<<<<<<`, `=======`, and `>>>>>>>` from the resolved source. For generated CSS, merge `resources/css/app.css` first, then run the CSS build instead of guessing between generated outputs.
4. Run syntax/integration/UI checks relevant to the conflict.
5. Stage only resolved files and complete the merge:

```powershell
git add path/to/resolved-file
git commit -m "Merge main and resolve agreed changes"
git push
```

If you cannot safely resolve it, `git merge --abort` returns to the pre-merge state when the merge began from a clean working tree. Ask the lead before discarding anything. This merge-based workflow does not require rewriting shared history. **Never force push to `main`.** A rejected push means fetch/coordinate/merge, not force.

## 6. Daily five-person routine

- The lead opens/assigns Issues with clear acceptance criteria and reviews the integration queue.
- Each member posts their branch/Issue and planned files before editing shared code.
- Keep PRs focused enough for a beginner teammate to understand; avoid unrelated formatting/refactors.
- Report blockers early with sanitized error output and reproduction steps.
- Review FR/AR, mobile behavior, and backend permissions when relevant.
- End the day with pushed feature commits or a draft PR, a short status note, and an updated local `main` when safe.

See [CONTRIBUTING.md](../CONTRIBUTING.md), [coding style](CODING_STYLE.md), and [GitHub's PR documentation](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests).
