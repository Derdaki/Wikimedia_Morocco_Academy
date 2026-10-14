# Contributor Onboarding Checklist / قائمة الانضمام

Contributor: fill in your name privately or in the team planning board. Never include passwords, tokens, `.env`, session contents, or password hashes in the checklist evidence.

- [ ] Git installed: `git --version` works. / Git مثبت ويعمل.
- [ ] Own GitHub account configured; collaborator invitation accepted. / تم قبول دعوة المستودع.
- [ ] Repository cloned, not just downloaded as a ZIP; `git remote -v` points to the expected repository.
- [ ] VS Code opens this repository only; the PHP executable is configured.
- [ ] PHP 8.2+ works; `php --ini` identifies the loaded configuration.
- [ ] `pdo_mysql`, `mbstring`, `fileinfo` enabled; `curl` available for tests.
- [ ] Actual database version/port checked; personal database created with utf8mb4.
- [ ] Private `.env` configured; `git check-ignore .env` confirms it is ignored.
- [ ] `php scripts/migrate.php` succeeds; demo seeds used only when appropriate.
- [ ] `php scripts/doctor.php` passes, including actual upload/session write checks.
- [ ] PHP server stays running on 8085 (or a documented unused port matching `APP_URL`).
- [ ] Homepage, projects, events, news, CSS/JS/images load without server/console errors.
- [ ] French and Arabic language switching works; RTL/mobile layouts checked.
- [ ] Own named dashboard account works on the correct local/staging database.
- [ ] Editor can manage intended content/media, but cannot access Users or Settings.
- [ ] Temporary initial password changed under My account; sign-in works afterwards.
- [ ] No website account is being confused with GitHub or public team biographies.
- [ ] Disposable `_test` database configured in both terminals; integration tests run and actual results recorded.
- [ ] CSS rebuilt if source CSS changed; compiled output included without `node_modules`.
- [ ] Personal branch created for an assigned Issue; no direct/force push to `main`.
- [ ] Staged diff reviewed; no credentials, logs, private uploads, or database dumps included.
- [ ] First feature branch pushed and first Pull Request submitted to `main`.
- [ ] Review requested from the Team Lead; any requested changes addressed.

Record versions, commands, pass/fail results and unresolved issues in the PR, not secrets. A teammate can help with installation, but must not ask for your password. / سجل النسخ والنتائج والمشاكل، وليس كلمات المرور. يمكن لزميل مساعدتك بدون معرفة كلمة مرورك.

References: [English setup](TEAM_GUIDE_EN.md), [الدليل العربي](TEAM_GUIDE_AR.md), [accounts](ADMIN_GUIDE.md), [Git workflow](GIT_WORKFLOW.md).
