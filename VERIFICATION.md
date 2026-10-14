# Verification Record

Date: 2026-10-14. Scope: the existing `wikimedia-maroc/` application only.

## Executed Successfully

- All 68 PHP files passed `php -n -l`.
- JavaScript application and Tailwind configuration passed `node --check`.
- 94 integration assertions passed through the PHP development server.
- The same 94 assertions passed through Apache 2.4.58 with PHP 8.2.12.
- Database tests used an isolated MariaDB 10.4.32 instance on loopback port 3307, with native PDO prepares. The user's existing MySQL 8 service was not modified.
- Schema migration ran twice against a fresh test database. Seeds ran repeatedly without duplicate content, and a custom website setting survived reseeding.
- Real HTTP tests covered administrator login/logout, rate limiting, CSRF rejection, role restrictions, revoked sessions, last-administrator protection, event CRUD/archive/search/filters/pagination, project CRUD, news publishing/scheduling, team/resource management, contact storage/read/delete, valid/invalid settings, and public visibility.
- Real multipart uploads covered images, event covers, PDFs, size limits, MIME spoofing, executable extensions, media search/deletion, and media referenced in content or JSON homepage settings.
- An executable fixture placed directly in the Apache upload directory returned 403 and was not executed. Clean URL rewriting and static assets returned successful responses.
- Playwright/Chrome checked 17 public/admin pages at 1366px and 390px widths: all returned 200, no horizontal document overflow, and no unnamed visible buttons or links. Additional checks covered French/Arabic RTL, mobile sidebar open/close, and the event list toggle. No JavaScript page errors were observed.
- Commons assets were bundled locally and observed rendering successfully in the browser.
- `npm ci --offline` installed the locked dependency tree in a writable verification copy. `npm run build:css` compiled the production stylesheet successfully. The stylesheet contains the Tailwind output and existing semantic styles.

## Bugs Fixed

- Repeated PDO named placeholders, ambiguous joined-column ordering/filtering, and missing admin pagination.
- Disabled accounts retaining authenticated sessions; missing idle expiry and session hardening; last active administrator lockout.
- Unvalidated asset/link URLs, malformed form inputs, invalid dates/categories/statuses, and missing validation feedback.
- Apache turned the nonstandard CSRF status 419 into 500. CSRF rejection now uses standard 403 on both servers.
- Scheduled news appearing before publication; draft preview links leading to unavailable public content.
- Media referenced in JSON settings could be deleted because JSON escaped the path's slashes. Reference checks now parse JSON.
- Upload type/extension validation, image-only cover validation, orphan cleanup after database failures, and upload headers.
- Tailwind rebuilding would erase the site's custom styles. The complete source now builds reproducibly with a lockfile.
- Missing Arabic translations/RTL layouts, inactive list controls, sidebar keyboard behavior, and inaccessible icon controls.
- Broken external-only imagery, incomplete collage composition, seed settings being overwritten, and unreadable full contact messages.

## Environment Limits

The successful database/upload tests ran against a writable temporary copy of the current source. In the original Documents checkout, native PHP and Node processes failed directory/file write probes. An earlier integration run there passed 74 checks but failed image upload and media search because no file could be stored. Later additions expanded the suite to 94 checks. This is not a passing upload result for the Documents folder.

Use a writable checkout, or configure Windows folder protection to allow PHP/build tools. `php scripts/doctor.php` checks the actual upload/session directories, database/schema, extensions, and controller bindings. The session fallback permits login testing, but does not make the upload directory writable.

The persistent `.env` is not configured with the user's MySQL credentials. MySQL 8, MariaDB 10.6+, PHP-FPM, HTTPS/reverse-proxy deployment, production permissions, concurrency/load, email delivery, and other browser engines were not exercised. Contact messages are stored in the administration area; no SMTP delivery is configured.

This record does not certify production readiness. Configure and verify the intended deployment before publishing.

## Launch

Follow the Windows installation and repeatable-test commands in `README.md`. Start with a MySQL database, a configured `.env`, a created administrator, and writable runtime directories. The normal development command is:

```powershell
php -d upload_max_filesize=4M -d post_max_size=6M -S 127.0.0.1:8080 -t public scripts/serve.php
```
