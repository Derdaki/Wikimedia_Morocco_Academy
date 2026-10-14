# Small, Consistent Changes

- Follow the surrounding native PHP code. Do not introduce Laravel, React, Composer requirements, or a second account system for a hackathon task.
- Use four spaces in PHP, descriptive names, `declare(strict_types=1)` in new PHP classes/scripts, and the existing `App\` namespaces/autoloader. Views stay under `app/Views`.
- Controllers handle requests, validation, authorization, and redirects. Models own SQL. Reuse `Controller`, `Validator`, `Auth`, `Csrf`, `UploadService`, and helpers.
- Check permissions on the backend. Mutations use POST plus CSRF; a hidden button is not authorization. Test an Editor and an unauthenticated client.
- Use PDO prepared statements with bound values. Native prepares need distinct named placeholders when the same value appears more than once. Never put a request value into a table/column identifier without a fixed allowlist.
- Escape rendered text/attributes with `e()`. Use `url()`/`asset()` and existing safe-URL validation. Do not introduce arbitrary rich HTML or executable uploads.
- Passwords use `password_hash()`/`password_verify()`, never reversible storage or literals. Do not put passwords in logs, sessions, form old-input storage, fixtures committed with real identities, or command arguments.
- Use `ui()`/`trans()` and both French/Arabic translations for interface changes. Prefer logical CSS properties such as `margin-inline` and test `dir=rtl`.
- Edit `resources/css/app.css`, keep semantic styles, run `npm ci` then `npm run build:css`, and commit compiled CSS with the source. Do not hand-edit only generated output or add Tailwind CDN.
- Use existing Vanilla JavaScript and semantic HTML controls. Give fields labels and icon buttons accessible names; retain visible keyboard focus and responsive constraints.
- SQL changes must be reviewable, documented, and safe for existing data. The current migration uses `CREATE TABLE IF NOT EXISTS`; it does not automatically apply arbitrary alterations to existing tables. Never commit real database dumps/accounts.
- Add focused tests for behavior changes, especially permissions, account changes, forms, and database queries. Run integration tests only on a disposable `_test` database.
- Keep comments short and useful. Avoid unrelated formatting/renaming; a small PR is easier to review and less likely to conflict.

Use [CONTRIBUTING.md](../CONTRIBUTING.md) for the PR checklist and [GIT_WORKFLOW.md](GIT_WORKFLOW.md) for shared-file coordination.
