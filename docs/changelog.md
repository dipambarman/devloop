# DevLoop — Changelog

All notable changes to DevLoop are documented here, organized by version and date.

---

## v1.3.0 — 2026-08-03

### Security Hardening

- **Tiered rate limiting** — Added five named rate limiters (`global` 60/min, `write` 30/min, `search` 20/min, `upload` 10/min, `critical` 5/min) applied to route groups by operation type.
- **Security headers middleware** — Created `SecurityHeadersMiddleware` injecting `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`, `Permissions-Policy`, and `Strict-Transport-Security` (production only) on every response.
- **Route restructuring** — Reorganized `web.php` from a flat auth group into tiered READ/WRITE/UPLOAD/CRITICAL groups with escalating throttle limits.
- **NotePolicy and SnippetPolicy** — Replaced manual `abort(403)` checks in NoteController and SnippetController with proper Laravel Policies via `Gate::authorize()`.
- **Discussion content limits** — Added `max:10000` validation on discussion and reply content fields (previously unlimited).
- **AuthenticateSession middleware** — Registered in the web middleware group to support `Auth::logoutOtherDevices()`.

### Auth Hardening

- **Password strength policy** — Configured `Password::defaults()` in AppServiceProvider requiring mixed case, letters, numbers, symbols, and HaveIBeenPwned breach check.
- **Route-level throttling** — Added `throttle:5,1` to `POST /login`, `POST /forgot-password`, `POST /reset-password`, `POST /confirm-password`, and `PUT /password`.
- **Session invalidation on password change** — Added `Auth::logoutOtherDevices()` to `PasswordController` so stolen sessions are revoked after a password change.
- **Session destruction on password reset** — Added direct session table cleanup in `NewPasswordController` to destroy all existing sessions after a password reset.

---

## v1.2.0 — 2026-07-XX

### Team Collaboration

- **Project discussions** — Added threaded discussion boards within projects with create, reply, pin/unpin, and delete functionality.
- **Discussion replies** — Nested reply system with author-or-owner delete permissions.
- **Member management** — Invite users to projects by email with `member` or `viewer` roles.
- **Member removal** — Project owners can remove members (cannot remove themselves).
- **Activity feed** — Event-driven activity logging with polymorphic subject tracking.
- **In-app notifications** — Mark-as-read and mark-all-as-read for notifications.

### Events System

- Added domain events: `DiscussionCreated`, `DiscussionReplyAdded`, `MemberAdded`, `MemberRemoved`, `TaskAssigneeChanged`, `TaskPriorityChanged`.
- Added `LogActivityListener` for centralized activity log recording.

---

## v1.1.0 — 2026-06-27

### Developer Workspace

- **Notes** — Personal markdown notes with optional project linking and pin-to-top.
- **Code snippets** — Saved code blocks with language metadata for syntax highlighting.
- **Developer profiles** — Extended user model with bio, skills (JSON array), GitHub URL, portfolio URL, and LinkedIn URL.

### Task Enhancements

- **Kanban board** — Drag-and-drop board view using SortableJS with column-based status workflow.
- **Inline status updates** — Change task status, priority, and assignee without navigating to the edit page.
- **Task comments** — Threaded comments on tasks with author-or-owner delete permissions.
- **Tags** — Reusable labels attachable to tasks via many-to-many relationship.
- **Task ordering** — `order_column` for Kanban board card positioning.

### Dashboard

- **Statistics dashboard** — Stat cards showing project count, task count, completion rate, and overdue tasks.
- **Task distribution chart** — Chart.js visualization of tasks by status.
- **Recent projects and tasks** — Quick-access lists on the dashboard.
- **DashboardService** — Extracted query logic into a dedicated service class.

---

## v1.0.0 — 2026-06-26

### Foundation

- **Authentication** — Laravel Breeze with login, registration, email verification, password reset, and password confirmation.
- **Project CRUD** — Create, read, update, and delete projects with name, description, color, GitHub repo link, and soft deletes.
- **Task CRUD** — Create, read, update, and delete tasks with title, description, status workflow (todo → in_progress → review → done), priority levels (low, medium, high, urgent), assignee, creator, due date, and soft deletes.
- **Authorization** — ProjectPolicy and TaskPolicy enforcing ownership and membership rules.
- **Form validation** — StoreProjectRequest, UpdateProjectRequest, StoreTaskRequest, UpdateTaskRequest with strict validation rules.
- **Dark theme UI** — Custom Tailwind CSS design system with cosmic dark palette, Inter typography, and responsive layout.
