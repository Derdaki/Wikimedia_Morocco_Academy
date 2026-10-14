# Administrator Onboarding and Accounts

This guide describes the actual native PHP account system. It does not create real team accounts or contain passwords. Account operations apply to the database configured on the server where they run.

## Two identities, two kinds of team records

| Identity | Purpose | Managed where |
| --- | --- | --- |
| GitHub account | Clone, commit, push, review PRs | GitHub; repository owner invites collaborators |
| Website account (`users`) | Sign into the PHP administration dashboard | Initial trusted CLI setup, then Super Administrator's Users page |
| Public team biography (`team_members`) | Show a person's name/bio/photo on the public site | Dashboard Team section; no login is created |

Each developer's local database is independent. Do not export accounts between machines in Git. For a shared team dashboard, configure a staging server with its own private `.env`, database, HTTPS, backups, and five individual accounts. A local setup Super Administrator has no authority on another machine or staging.

## Actual permissions

| Action | Super Administrator | Editor |
| --- | --- | --- |
| Sign in, dashboard, change own password | Yes | Yes |
| Create/edit/publish/archive/delete events | Yes | Yes |
| Manage projects, news, resources, public team biographies | Yes | Yes |
| Upload/manage permitted media | Yes | Yes |
| Read/manage contact messages | Yes | Yes |
| Create accounts, activate/deactivate users, change roles | Yes | No |
| Manage website settings | Yes | No |

Editors currently share content-management access, including deletion and contact messages. There is no per-author ownership rule or separate reviewer role. Do not promise narrower privileges than the implemented model. File type/size validation and in-use media checks still apply to both roles.

`Controller::requireAuth()` protects dashboard routes; `requireRole('super_admin')` protects Users, role changes, deactivation, and Settings on the backend. Hiding sidebar links is only a usability measure, not the security control. State-changing HTTP forms also require CSRF tokens. There is **no public administrator registration**.

## 1. Team Lead creates the first Super Administrator

First finish `.env`, database migration, and `doctor.php` using [TEAM_GUIDE_EN.md](TEAM_GUIDE_EN.md). From the repository root:

```powershell
php scripts/create-admin.php
```

Enter your own name/email, choose `super_admin` (the default), and choose a unique password. On interactive Windows consoles the script delegates to the hidden, confirmation-based PowerShell prompt. If console detection is not available, use the helper explicitly:

```powershell
powershell -NoProfile -File scripts/admin-account.ps1 -Action create
```

Passwords are 10 to 72 UTF-8 **bytes**, with no null characters; non-ASCII characters can occupy multiple bytes. The CLI hashes the password with `password_hash()`. Login uses `password_verify()`. Never put a real password in command arguments or a committed file. The helper passes it through stdin, not a disk file or command-line argument. Input must not contain line breaks because the CLI protocol is line-based.

Only a trusted operator with access to the server's private configuration should run the CLI. CLI access is an OS-level administrative capability, not a public password reset service. No default/shared admin password is shipped. Rerunning creation with an existing email fails rather than overwriting the account.

## 2. Create the four contributors in the existing dashboard

1. Start PHP and the database. Open `http://127.0.0.1:8085/admin/login` locally, or the configured HTTPS staging login URL.
2. Sign in as the Team Lead's Super Administrator.
3. Open **Users / Utilisateurs** (`/admin/users`).
4. In **Create administrator**, enter the first contributor's actual name and unique email. Keep **Editor** selected.
5. Arrange a unique initial password privately with that contributor. Prefer letting them type it themselves in a supervised HTTPS/local session, or deliver a unique temporary password through an approved secure channel. Do not use Issues, PRs, screenshots, or group chat.
6. Click Create and confirm a new row appears with Editor role and active status.
7. Repeat for the other three contributors. Do not reuse names/emails or give everyone Super Administrator access.
8. Each contributor opens the correct server's login page, signs in with their own credentials, and opens **My account / Mon compte** (`/admin/account`) to change any temporary password immediately.
9. After changing a password, sign in again and verify content access. An Editor must not be able to open `/admin/users` or `/admin/settings`; the expected response is HTTP 403.

The intended shared roster is one Team Lead Super Administrator plus four named Editors. Names/emails/passwords must come from the actual people; no demonstration roster is installed automatically. An existing extra administrator is not silently demoted or deleted by this workflow.

## 3. Change roles and deactivate accounts

On Users, choose **Editor** or **Super Administrator** in that person's row and click the save icon (accessible name: Save role + person's name). Promotion is exceptional and must be agreed with the lead. Demotion removes privileged access on the next request because the account/role is reloaded from the database.

Use **Deactivate** to disable a departing member and **Activate** to re-enable them. Deactivation revokes their authenticated sessions on the next request. The interface/backend blocks self-deactivation. Transactional locking also prevents deactivating or demoting the last active Super Administrator, including concurrent changes. If you demote yourself while another active Super Administrator exists, you lose Users/Settings access immediately; coordinate first.

Do not edit SQL role values or remove checks to work around a denial. The accepted roles are exactly `super_admin` and `editor`.

## 4. Change or recover a password

### Contributor knows the current password

Sign in -> My account -> enter the current password, new password, and confirmation -> Change password. The backend verifies the current password, enforces the byte limits, and changes only the authenticated user's account. A submitted `user_id` cannot target somebody else. All old sessions, including the current session, are invalidated; sign in again.

Temporary-password rotation is an onboarding requirement, **not an enforced first-login flag**. The lead should confirm rotation before shared staging is used for real content.

### Contributor forgot the password

A trusted server operator verifies the person's identity outside the application and runs:

```powershell
php scripts/reset-admin-password.php
# Explicit hidden Windows helper, if needed:
powershell -NoProfile -File scripts/admin-account.ps1 -Action reset
```

Enter the existing email and a new unique password privately. The reset changes the hash, not the role or activation status. Existing sessions are revoked on their next request. A disabled account remains disabled. The CLI does not print the password or create an unknown account. It has no email delivery, public reset link, reset token, or automatic identity verification.

On non-Windows interactive consoles, PHP uses `stty` to suppress echo and restores it afterwards; if echo cannot be suppressed, setup fails closed. Windows interactive setup uses `Read-Host -AsSecureString`. The integration tests exercise the non-echoing stdin path; `tests/admin-console.ps1` additionally tests the Windows helper with mocked secure input and UTF-8. Interactive terminal behavior must also be checked on each developer's environment.

### Existing sessions after upgrading this branch

Sessions now contain a fingerprint of the account's password hash. Password changes/resets invalidate fingerprints; the raw password is never stored in the session. Sessions created before this update lack the fingerprint and are signed out on their next request. No database schema change is needed. Sessions also expire after one hour idle.

## 5. Troubleshoot sign-in without leaking secrets

- Confirm the correct server URL and database. Another person's localhost is not your localhost.
- Ask the lead to confirm your email and active status; do not send your password.
- Check PHP sessions/uploads with `php scripts/doctor.php`. A protected Windows folder can block writes.
- After five failed attempts matching the email **or IP** within 15 minutes, wait 15 minutes. Teammates sharing an IP can affect this limit. Do not disable the limiter in order to onboard.
- A role/password change or account deactivation can end existing access; sign in again if appropriate.
- The CLI reset does not reactivate an account or clear the rate limiter. The Super Administrator must handle activation separately.
- Inspect private server logs locally for database errors. Share only sanitized excerpts, never `.env`, password hashes, cookies, or session files.

## Verification expected before a hackathon

Use only a disposable `_test` database and the two-terminal commands in the team guide. `tests/run.php` covers four separate Editor logins, dashboard account creation, role changes, last-admin protection, Editor denials, CSRF, password changes, session revocation, CLI creation/reset, and the existing content/upload workflows. Record the actual run result in the PR and [VERIFICATION.md](../VERIFICATION.md).

Do not test account administration by modifying the real five-person roster or production data. HTTPS staging, email-based recovery, multifactor authentication for website users, fine-grained Editor permissions, and production deployment remain separate work.
