# Wikimedia Maroc: Team Guide

For five hackathon contributors using Windows, VS Code, and native PHP. Start here, then read [your tasks](HACKATHON_TASKS.md), [account management](ADMIN_GUIDE.md), and [Git workflow](GIT_WORKFLOW.md). [Arabic version](TEAM_GUIDE_AR.md).

## 1. Understand the application

The public website presents Moroccan Wikimedia projects, events, news, team information, resources, and a contact form. The private dashboard manages that content. This is **not Laravel or React**; there is no Composer install, framework command, or JavaScript application server.

**Frontend** means what the browser displays: HTML, CSS, images, forms, and the small Vanilla JavaScript file. PHP renders the HTML on the server. Tailwind is a development tool for compiling CSS, not a requirement for running the installed site.

**Backend** means the PHP code that handles requests, validates data, checks permissions, reads/writes MySQL, and returns pages or redirects. The database stores content and accounts, not the source code.

An actual request follows this path:

```text
Browser -> scripts/serve.php (development) -> public/index.php
        -> app/bootstrap.php -> routes/web.php -> controller
        -> model -> PDO -> MySQL/MariaDB
        -> PHP view + layout -> HTML -> browser
```

Apache uses `public/.htaccess` instead of the development router. `bootstrap.php` loads `.env`, the App autoloader, helpers, sessions, and authentication. `Database::pdo()` connects with PDO and native prepared statements. A model performs a query; a controller passes its results to a reusable view. No REST API or build system is required to render pages.

For example, an authenticated Editor submits an event form. PHP checks the session and CSRF token, validates the fields and optional image, then saves through `Event`. The public controller queries the **same database**. A published, eligible event appears on the next public page request; drafts and archived events stay hidden. News scheduled for the future stays hidden until its publication time. Updating dashboard content does not require a Git commit or CSS rebuild.

### Folder map

| Path | Actual purpose |
| --- | --- |
| `public/index.php` | HTTP entry point, security headers, error handling |
| `public/.htaccess` | Apache clean URLs; serve only `public/` |
| `routes/web.php` | GET/POST routes and their controller methods |
| `app/Controllers/PublicController.php` | Public lists, details, language switching, contact submission |
| `app/Controllers/Admin/` | Dashboard, content, events, media, users, settings, messages |
| `app/Controllers/AuthController.php` | Login and logout |
| `app/Core/` | PDO connection, router, views, sessions/auth, CSRF, validation, uploads, CLI input |
| `app/Models/` | Database queries for content, accounts, settings, media |
| `app/Views/public/`, `app/Views/admin/` | Page templates |
| `app/Views/layouts/` | Shared public, admin, and login layouts |
| `app/Helpers/functions.php` | Escaping, links, translation, formatting, CSRF helpers |
| `app/Lang/` | French/Arabic labels and interface translations |
| `resources/css/app.css` | Tailwind directives AND the site's semantic styles |
| `public/assets/` | Compiled CSS, JavaScript, bundled Commons images |
| `public/uploads/` | Local uploaded files; never commit user content |
| `database/migrations/schema.sql` | Idempotent table-creation schema, not a private dump |
| `database/seeds/demo.sql` | Optional demonstration content; no admin passwords |
| `scripts/` | Migration, seed, account setup/reset, diagnostics, development router |
| `storage/` | Private sessions/logs and ignored local runtime files |
| `tests/` | Real HTTP/PDO integration suite and verification fixtures |
| `docs/`, `.github/` | Team guides and collaboration templates |
| `.env.example` | Safe configuration template; `.env` is private |

## 2. Install tools once

1. Install [Git for Windows](https://git-scm.com/install/windows). Include Git in PATH, then reopen PowerShell and run `git --version`.
2. Create your own [GitHub account](https://github.com/signup), enable two-factor authentication, and send only your GitHub username to the Team Lead. Accept the repository invitation. Never share your GitHub password or token.
3. Install [VS Code](https://code.visualstudio.com/download). Open the repository folder, not its parent containing unrelated projects. Use Terminal > New Terminal and select PowerShell.
4. Install [XAMPP](https://www.apachefriends.org/download.html) with PHP 8.2 or newer, normally under `C:\xampp`. The [XAMPP Windows FAQ](https://www.apachefriends.org/faq_windows.html) explains its control panel and bundled MariaDB. Start the database from the control panel; Apache is optional when using PHP's built-in server. Do not stop or overwrite an existing MySQL installation.
5. Check the actual database version. The documented target is MySQL 8 or MariaDB 10.6+. Some XAMPP packages contain older MariaDB; local verification on this machine passed with 10.4.32, but this does not establish supported production deployment. Agree a compatible database version with the lead before upgrading anything.
6. Install a current supported [Node.js LTS](https://nodejs.org/en/download) **only if editing/rebuilding CSS**. The compiled CSS already exists. Check `node --version` and `npm --version`. On Windows, `npm.cmd` can be used if PowerShell blocks `npm.ps1`.

For this PowerShell session, make XAMPP's executables available:

```powershell
$env:Path = 'C:\xampp\php;C:\xampp\mysql\bin;' + $env:Path
php -v
php --ini
php -m
```

PHP needs `pdo_mysql`, `mbstring`, and `fileinfo`; tests also need `curl`. Enable missing extensions in the **loaded** `php.ini`, not an unrelated PHP installation, then restart PHP. See the [PHP Windows extension manual](https://www.php.net/manual/en/install.pecl.windows.php).

In VS Code's user `settings.json`, configure the installed executable if necessary:

```json
{
  "php.validate.executablePath": "C:/xampp/php/php.exe",
  "php.validate.run": "onSave"
}
```

VS Code uses `php -l` for [PHP validation](https://code.visualstudio.com/docs/languages/php). Optional editor extensions must not replace the actual PHP runtime.

## 3. Clone into a writable folder

Use a developer folder outside protected Documents/Desktop/OneDrive. For example:

```powershell
New-Item -ItemType Directory -Force "$env:LOCALAPPDATA\WikimediaMarocDev" | Out-Null
Set-Location "$env:LOCALAPPDATA\WikimediaMarocDev"
git clone https://github.com/Derdaki/Wikimedia_Morocco_Academy.git
cd Wikimedia_Morocco_Academy
code .
```

If the onboarding PR has not yet been merged, review these changes on its branch before following the new account workflow:

```powershell
git fetch origin
git switch --track origin/codex/hackathon-onboarding
```

After the lead merges the PR, normal contributors start task branches from updated `main` as described in the Git guide. Do not merge this preview yourself or start dependent work without coordinating with the lead.

Cloning includes Git history, branches, and the `origin` remote. A ZIP is a source snapshot without the Git history/remotes needed for normal collaboration. In this clone, `app/`, `public/`, and `database/` are at the repository root; do not add another `wikimedia-maroc/` wrapper.

Configure your commit identity using your own details in VS Code or `git config user.name` / `git config user.email`. A GitHub no-reply email can keep your personal email private.

## 4. Configure your own local database

```powershell
if (!(Test-Path .env)) { Copy-Item .env.example .env }
code .env
```

Set `APP_URL=http://127.0.0.1:8085`, `DB_DATABASE=wikimedia_maroc`, `DB_HOST=127.0.0.1`, and the actual database port, username, and password **locally**. Do not assume a blank root password. The example's `3306` is a default, not a guarantee; this lead's existing setup uses `3307`. Never copy the lead's `.env` or machine-specific runtime scripts to another computer.

Open the database client with your actual account name; `-p` prompts without putting the password in the command:

```powershell
mysql -h 127.0.0.1 -P 3306 -u YOUR_LOCAL_DB_USER -p
```

Replace the port and `YOUR_LOCAL_DB_USER` first. In the SQL prompt:

```sql
SELECT VERSION();
CREATE DATABASE IF NOT EXISTS wikimedia_maroc
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

If your account cannot create a database, ask the local database operator. Use phpMyAdmin as an alternative if it is already configured securely. Each developer needs their own database; do not connect your development server to shared staging accidentally.

Back in the repository:

```powershell
php scripts/migrate.php
# Optional: only for a fresh/disposable local development database.
php scripts/seed.php
# Create the initial LOCAL Super Administrator with your own credentials.
php scripts/create-admin.php
php scripts/doctor.php
```

The migration creates missing tables; it does not implement a general schema-version migration framework. Back up valuable data before schema changes. Seeds insert demos and avoid duplicate slugs, but are not a production-data synchronization tool. No administrator is seeded. Skip seeds when using existing valuable content.

Interactive account setup on Windows delegates to `scripts/admin-account.ps1`, which hides and confirms password entry. If automatic console detection is unavailable, run `powershell -NoProfile -File scripts/admin-account.ps1 -Action create` directly. Use a unique password of 10 to 72 **UTF-8 bytes**; Arabic characters can occupy multiple bytes. Do not put passwords in command arguments, screenshots, issue reports, or chat. Only run reviewed scripts; if script policy blocks the helper, ask the lead to review the process-scoped policy rather than disabling system security.

`doctor.php` must report the extensions, environment, database/schema, route bindings, and actual upload/session write probes as OK. `public/uploads` and `storage/sessions` must be writable by the PHP process; `storage/logs` should also be writable. A session fallback in Temp does **not** fix blocked uploads. Windows Controlled Folder Access was confirmed blocking PHP in the original Documents checkout. Use the writable clone above; do not disable Defender or grant Everyone full control.

## 5. Start the real website

Keep this terminal open:

```powershell
php -d upload_max_filesize=4M -d post_max_size=6M -S 127.0.0.1:8085 -t public scripts/serve.php
```

- Public: [http://127.0.0.1:8085](http://127.0.0.1:8085)
- Login: [http://127.0.0.1:8085/admin/login](http://127.0.0.1:8085/admin/login)
- Dashboard after login: `/admin`; personal password: `/admin/account`.

`localhost` / `127.0.0.1` means **your computer**, not the lead's. All five members can use port 8085 on five separate computers. The database AND PHP server must stay running. Stop your development server with Ctrl+C. If 8085 is occupied on your computer, choose an unused port and update `.env`'s `APP_URL` to match. Serve only `public/`, never the repository root.

## 6. Accounts and shared staging

GitHub accounts let you push source code. Website accounts are rows in this application's `users` table and let you use the PHP dashboard. They are unrelated to public `team_members` biographies.

For **shared staging**, the Team Lead is Super Administrator and creates four named **Editor** accounts under Users. Editors manage dashboard content/media but cannot manage Users or sensitive Settings. See the exact workflow and permission table in [ADMIN_GUIDE.md](ADMIN_GUIDE.md). Each developer bootstraps their own isolated local database; having a local setup Super Administrator does not grant that person Super Administrator access on shared staging. Test normal contributor work locally with an Editor account as well.

Creating an account on one local database does not create it elsewhere. A Git push does not send MySQL data, `.env`, sessions, or uploaded files. Never synchronize real users/password hashes with SQL dumps in Git. Shared staging needs separately managed hosting, HTTPS, a shared database, backups, and individual accounts; it has not been provisioned by these guides.

## 7. Develop, build, and test

Before a task, follow [GIT_WORKFLOW.md](GIT_WORKFLOW.md). Agree file ownership to avoid five people editing the same layout/CSS file. Edit CSS in `resources/css/app.css`, then commit source and compiled output together:

```powershell
npm ci
npm run build:css
node --check public/assets/js/app.js
node --check tailwind.config.js
Get-ChildItem app,public,routes,scripts,tests -Filter *.php -Recurse -File |
  ForEach-Object { php -l $_.FullName; if ($LASTEXITCODE -ne 0) { throw 'PHP syntax failed' } }
```

Do not edit only the compiled CSS; the next build replaces it. Do not commit `node_modules`. Check desktop/mobile, FR/AR and RTL, visible labels, images, and browser console errors.

### Integration tests: separate database and two terminals

Create a **disposable** database named `wikimedia_maroc_hackathon_test` with utf8mb4 using the same secure SQL-client procedure. The suffix `_test` is required. Never run tests on `wikimedia_maroc` or staging: tests create/delete records and temporarily change settings.

In **both** PowerShell terminals, from the same clone, set:

```powershell
$env:DB_DATABASE = 'wikimedia_maroc_hackathon_test'
$env:APP_URL = 'http://127.0.0.1:8086'
$env:SESSION_NAME = 'wmma_hackathon_test'
# DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD come from your private .env.
```

In terminal A:

```powershell
php scripts/migrate.php
php scripts/seed.php
php -d upload_max_filesize=4M -d post_max_size=6M -S 127.0.0.1:8086 -t public scripts/serve.php
```

In terminal B:

```powershell
$env:TEST_BASE_URL = 'http://127.0.0.1:8086'
php tests/run.php
```

On Windows, also test the secure account helper from terminal B:

```powershell
powershell -NoProfile -File tests/admin-console.ps1
```

This helper test uses mocked secure input and temporary accounts to check UTF-8 transport and password creation/reset without showing passwords. It does not replace checking actual hidden keyboard input on your terminal.

Both terminals and the HTTP server **must use that same disposable database**. The suite creates temporary accounts, tests real login/permissions/CRUD/uploads, then cleans its fixtures. Stop terminal A afterwards. Close both test terminals before restarting the normal site; environment variables override `.env`, so reusing a test terminal can point your normal server at the wrong database. Only claim the results actually printed by your run. Record limitations in your PR.

## 8. Troubleshooting

| Problem | Check and recovery |
| --- | --- |
| PDO connection/503 | Start MySQL/MariaDB; check actual port, database name, user privileges and private `.env`; run `doctor.php`. Do not paste credentials into an Issue. |
| Missing extension / PHP not found | Use `Get-Command php`, `php --ini`, `php -m`; add XAMPP PHP to PATH and enable extensions in that loaded configuration. Restart the server. |
| Port already used | `Get-NetTCPConnection -LocalPort 8085 -State Listen`; identify the owner, choose another port, change `APP_URL`. Do not terminate unknown processes. |
| Upload/session write failure | Check actual write probes in `doctor.php`. Clone outside protected Documents/OneDrive; confirm PHP can write the folders. Never broadly disable Windows protection. |
| Git authentication / permission denied | Accept the collaborator invitation; check `git remote -v`; use Git Credential Manager's browser authentication. Do not put a token in a remote URL or chat. |
| Merge conflict | Stop and follow the conflict example in [GIT_WORKFLOW.md](GIT_WORKFLOW.md); do not discard another person's work. |
| Dashboard login fails | Check the correct local/staging URL, active account, correct DB and unique email. Wait 15 minutes after rate limiting; sessions expire after one hour idle. A reset requires a trusted operator. See [ADMIN_GUIDE.md](ADMIN_GUIDE.md). |
| Missing CSS or images | Serve `public/` with `scripts/serve.php`, check `APP_URL`, check asset files, rebuild CSS only when needed, reload the browser. |
| CLI PowerShell prompt fails | Run the reviewed `scripts/admin-account.ps1` directly; check PHP PATH and script policy. Never replace hidden input with a password in command history. |

Finish the [onboarding checklist](ONBOARDING_CHECKLIST.md) and open your first small PR. The repository does not synchronize databases or automatically deploy code, and the guide is not a production-readiness certificate.
