# Wikimedia Maroc Website and Administration Dashboard

Native PHP 8.2 and MySQL implementation for a public Wikimedia Maroc website plus an administration dashboard. The project is intentionally framework-free and uses PDO, PHP sessions, reusable PHP views, CSRF protection, secure uploads, role-based administration, and a compiled CSS asset.

## What Is Included

- Public website with homepage, projects, events, news, team, resources, contact, join page, language switcher, sitemap, and robots.txt.
- Admin dashboard with metrics, event management, projects, news, team, resources, media library, contact messages, users, and website settings.
- Event CRUD with upcoming cards, searchable table, filters, image upload, archive action, draft and publish states.
- Secure login with `password_hash()`, `password_verify()`, session ID regeneration, CSRF tokens, and login attempt rate limiting.
- MySQL schema with foreign keys, unique slugs, indexes, timestamps, and activity logs.
- French and Arabic public/admin interfaces, translated labels, Arabic content fields, and RTL layout switching. Content without an Arabic translation falls back to French.
- Tailwind source file plus compiled production CSS in `public/assets/css/app.css`.

## Requirements

- PHP 8.2 or newer with PDO MySQL, mbstring, and fileinfo enabled. The integration tests also use curl.
- MySQL 8 or MariaDB 10.6 or newer.
- Node.js 18 or newer only if you want to rebuild the CSS.
- Apache with mod_rewrite for production, or PHP built-in server for local development.

## Installation

Copy the environment file:

```bash
cp .env.example .env
```

Create the database:

```sql
CREATE DATABASE wikimedia_maroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update `.env` with your database credentials, then run:

```bash
php scripts/migrate.php
php scripts/seed.php
php scripts/create-admin.php
```

The seed script inserts demonstration content only. Replace demo records before production.

## Local Development

Start the PHP development server from the project folder:

```bash
php -d upload_max_filesize=4M -d post_max_size=6M -S 127.0.0.1:8080 -t public scripts/serve.php
```

Open:

- Public site: `http://127.0.0.1:8080`
- Admin login: `http://127.0.0.1:8080/admin/login`

## CSS Build

The compiled CSS file is already committed at `public/assets/css/app.css`.

To rebuild it:

```bash
npm ci
npm run build:css
```

Do not use Tailwind from a CDN in production.

## Project Structure

```text
wikimedia-maroc/
  app/
    Controllers/
    Core/
    Helpers/
    Lang/
    Models/
    Views/
  database/
    migrations/schema.sql
    seeds/demo.sql
  public/
    assets/css/app.css
    assets/js/app.js
    uploads/
    index.php
    robots.txt
  resources/css/app.css
  routes/web.php
  scripts/
  .env.example
  package.json
  tailwind.config.js
```

## Security Notes

- All database writes use prepared statements.
- State-changing requests require CSRF tokens.
- Admin routes require authentication.
- Super Administrator is required for user and settings management.
- Uploaded files are renamed with random filenames.
- Upload MIME type, extension, and size are validated.
- Upload execution is disabled with `public/uploads/.htaccess`.
- Destructive actions use POST forms with confirmation.
- Plain text output is escaped with `htmlspecialchars()`.

Rich HTML editing is intentionally not enabled. If you add it later, sanitize with a strict allowlist before rendering.

## Image and Logo Sources

The default logo and demonstration images are sourced from Wikimedia Commons:

- Wikimedia MA User Group logo: https://commons.wikimedia.org/wiki/File:Wikimedia_MA_User_Group.svg
- Hassan II Mosque image: https://commons.wikimedia.org/wiki/File:Maroc,_Mosqu%C3%A9e_Hassan_2,_Grand_Casablanca.jpg
- Ait Benhaddou image: https://commons.wikimedia.org/wiki/File:Ait_Benhaddou.jpg
- Moroccan carpet image: https://commons.wikimedia.org/wiki/File:Moroccan_carpet.jpg
- Atlas Mountains image: https://commons.wikimedia.org/wiki/File:Atlas_Mountains,_Fez,_Morocco.jpg

The bundled assets are served locally. The `/credits` page includes authors, source links, and licenses. The collage also uses [Hassan II Mosque Plaza](https://commons.wikimedia.org/wiki/File:Hassan_II_Mosque_Plaza.jpg) by FuriousYogi (CC BY-SA 4.0) and [Moroccan tribal carpet](https://commons.wikimedia.org/wiki/File:Moroccan_Carpet_(4804578240).jpg) by Mike Prince (CC BY 2.0).

## Production Deployment on Apache

Upload the `wikimedia-maroc` folder outside the web root when possible, and point the domain document root to `wikimedia-maroc/public`.

Recommended permissions:

```bash
chmod -R 755 public
chmod -R 775 public/uploads
chmod -R 770 storage/sessions storage/logs
```

Keep these files outside public access:

- `.env`
- `app/`
- `database/`
- `scripts/`
- `resources/`

The included `public/.htaccess` routes clean URLs to `public/index.php`. Enable `mod_rewrite` and allow `.htaccess` overrides for the public directory. `mod_headers` enables additional upload response headers. The upload directory blocks script execution; its PHP directive is conditional so it can also be used with PHP-FPM.

The sitemap is at `/sitemap.xml`; configure `APP_URL` so its entries are absolute. Add the deployed absolute sitemap URL to `robots.txt` when the production domain is known, or submit it directly to your search console.

Set `APP_ENV=production`, `APP_URL` to the HTTPS domain root, and valid database credentials in `.env`. Serve only `public/`. Use a dedicated database account, HTTPS, and PHP settings `upload_max_filesize=4M`, `post_max_size=6M`, `display_errors=Off`. An authenticated session expires after one hour of inactivity. Do not rely on the PHP development server for production.

## Windows Installation

From PowerShell:

```powershell
Set-Location C:\Users\ilyas\Documents\Wikimedia-Project-Morocco\wikimedia-maroc
Copy-Item .env.example .env
```

Create `wikimedia_maroc` in MySQL with `utf8mb4` encoding and configure `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`. The example credentials must match an account you provisioned. No administrator account is seeded.

Then run:

```powershell
php scripts/migrate.php
php scripts/seed.php
php scripts/create-admin.php
php scripts/doctor.php
php -d upload_max_filesize=4M -d post_max_size=6M -S 127.0.0.1:8080 -t public scripts/serve.php
```

If port 8080 is occupied, use another port and update `APP_URL` in `.env` to match. The website is at that URL and administration is at `/admin/login`.

On this machine, native PHP and Node tools were unable to write inside the Documents checkout. `doctor.php` exposes this through its directory write checks. Uploads passed from a writable copy in the Windows temporary directory. Use a writable checkout outside the protected folder, or explicitly allow PHP and your build tools through Windows folder protection. No Windows protection settings were changed during verification. Session storage has a temporary-directory fallback; uploads require a writable `public/uploads` directory.

## Repeatable Tests

Use a separate MySQL/MariaDB database whose name ends in `_test`. Never run the integration suite against production. Migrate and seed that database, and start a server pointing at it. In each PowerShell terminal set:

```powershell
$env:DB_DATABASE = 'wikimedia_maroc_test'
$env:DB_PORT = '3306'
$env:APP_URL = 'http://127.0.0.1:8085'
```

Credentials are read from `.env` or process environment variables. Process environment overrides `.env`. Run the migration and seed in one terminal, then start the server:

```powershell
php scripts/migrate.php
php scripts/seed.php
php -d upload_max_filesize=4M -d post_max_size=6M -S 127.0.0.1:8085 -t public scripts/serve.php
```

In the other configured terminal:

```powershell
$env:TEST_BASE_URL = 'http://127.0.0.1:8085'
php tests/run.php
```

The suite creates temporary users and content, performs real HTTP requests and multipart uploads, and removes its fixtures. A failure produces a nonzero exit status. `tests/apache.conf` is the isolated XAMPP configuration used on this machine, not a production vhost template. See `VERIFICATION.md` for executed checks and remaining deployment limits.

## First Administrator

Create the first account only through:

```bash
php scripts/create-admin.php
```

The application does not ship with hardcoded admin credentials.

## Verification

Useful checks:

```bash
php -l public/index.php
php -l app/bootstrap.php
php scripts/migrate.php
php scripts/seed.php
```

For a complete local smoke test, configure MySQL, create an admin user, then create and edit an event through `/admin/events`.
