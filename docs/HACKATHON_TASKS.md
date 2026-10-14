# Five-Member Hackathon Plan

Replace Member 1-5 with actual names in your planning board, not with shared login accounts. Each task gets an Issue, an owner, a personal feature branch, and a PR reviewed by the lead. No real accounts are created by this document.

## Member 1: Team Lead

- **Responsibilities:** backend supervision, database integration, code review, GitHub collaborator invitations, staging account onboarding, final integration.
- **Suggested files:** `routes/web.php`, `app/Core/`, `app/Models/`, `app/Controllers/`, `database/migrations/`, `scripts/`.
- **Branch:** `feature/backend-task-name`.
- **Example Issue:** "Verify a published event moves from the admin form to the public homepage."
- **Deliverables:** one documented end-to-end check, a small scoped fix if needed, reviewed PRs, a five-person staging roster with one lead and four Editors, and an integration checklist.
- **Acceptance:** prepared queries and server-side authorization preserved; migrations tested on a disposable database; all relevant tests pass; no secrets/data dumps in Git; `main` changed only after review. Creating local users does not count as configuring shared staging.
- **Difficulty:** moderate; owns decisions and pairs with beginners on backend work.

## Member 2: Frontend

- **Responsibilities:** homepage, header, footer, image presentation, responsive layouts, visible focus states.
- **Suggested files:** `app/Views/public/home.php`, `app/Views/layouts/public.php`, `app/Views/public/partials-pagination.php`, `resources/css/app.css`, `public/assets/js/app.js` when needed.
- **Branch:** `feature/homepage`.
- **Example Issue:** "Improve homepage card spacing at 390px without changing desktop alignment."
- **Deliverables:** a focused template/style change, rebuilt `public/assets/css/app.css`, desktop/mobile screenshots, and FR/AR checks.
- **Acceptance:** no horizontal page overflow at 390px/1366px; all image paths load; buttons/links have accessible names; keyboard focus remains visible; source and compiled CSS are both committed; no auth or database refactor.
- **Difficulty:** easy to moderate; pair with Member 5 for Arabic review.

## Member 3: Events and Projects

- **Responsibilities:** event/project lists and detail pages, filter/search usability, draft/published state feedback.
- **Suggested files:** `app/Views/public/events.php`, `app/Views/public/event-show.php`, `app/Views/public/projects.php`, `app/Views/public/project-show.php`, `app/Views/admin/events/`, `app/Controllers/PublicController.php`, `app/Controllers/Admin/EventController.php`, `app/Models/Event.php`, `app/Models/Content.php`. Discuss controller/model changes with the lead.
- **Branch:** `feature/events`.
- **Example Issue:** "Check combined event search, category/location filters, and empty results in French and Arabic."
- **Deliverables:** a small interface improvement, reproducible filter scenarios, and any focused regression test.
- **Acceptance:** search and pagination preserve selected filters; drafts/archived events remain hidden publicly; published content is visible from the same database; no SQL concatenation of user input; existing CRUD tests stay green.
- **Difficulty:** moderate; start with template-only work, then pair on PHP queries.

## Member 4: Admin Dashboard

- **Responsibilities:** dashboard spacing, labelled forms, searchable management tables, role/user interface usability.
- **Suggested files:** `app/Views/layouts/admin.php`, `app/Views/admin/dashboard.php`, `app/Views/admin/content/`, `app/Views/admin/events/`, `app/Views/admin/users/`, `resources/css/app.css` in coordination with Member 2.
- **Branch:** `feature/dashboard`.
- **Example Issue:** "Improve the Users table so named Editor accounts and role-saving controls are clear on mobile."
- **Deliverables:** focused form/table changes, keyboard/mobile screenshots, and manual Super Administrator/Editor check notes.
- **Acceptance:** dark sidebar/white workspace conventions preserved; tables scroll within their wrapper rather than the whole page; fields have labels; POST forms retain CSRF; Editors still get HTTP 403 for Users/Settings even if they request the URL directly; do not weaken backend checks.
- **Difficulty:** easy to moderate for styling; ask the lead to review permission changes.

## Member 5: Testing and Documentation

- **Responsibilities:** onboarding checks, reproducible bug reports, French/Arabic wording and RTL review, documentation accuracy.
- **Suggested files:** `tests/run.php`, `app/Lang/fr.php`, `app/Lang/ar.php`, `app/Lang/interface.php`, `docs/`, `README.md`, `VERIFICATION.md`.
- **Branch:** `feature/translations` (use a separate `feature/testing-task-name` for an unrelated test task).
- **Example Issue:** "Run onboarding on a second Windows computer and record missing steps without exposing credentials."
- **Deliverables:** completed onboarding checklist, a truthful test report, translation improvements, and Issues with exact reproduction steps.
- **Acceptance:** all commands checked against real scripts; tests run only against `_test`; actual counts/versions recorded; FR and Arabic RTL checked at mobile/desktop; no password, cookie, session, or private screenshot in Issues/PRs; no unsupported claim of production readiness.
- **Difficulty:** easy to start; moderate for automated test additions, with lead support.

## Coordination and finish line

1. **Kickoff:** everyone clones and completes [ONBOARDING_CHECKLIST.md](ONBOARDING_CHECKLIST.md); lead confirms individual accounts and branch ownership.
2. **First small PR:** each member makes one modest change and requests review before taking a larger task.
3. **Shared-file rule:** Member 2 coordinates CSS/public layout edits; the lead coordinates routes/schema; others discuss overlapping edits before starting them.
4. **Integration:** merge approved small PRs, then each member pulls `main`. Apply reviewed schema changes locally only when needed; do not reseed valuable databases.
5. **Demo:** check public content, four Editor logins, Super Administrator boundaries, uploads, French/Arabic, and mobile. Record remaining limitations honestly.

Use the repository's Issue and PR templates. The task list suggests work; it does not claim these tasks are already implemented or assign unknown GitHub usernames.
