# DevLoop — Engineering Decisions

This document records the key technical decisions made during DevLoop's development, the alternatives that were considered, and the reasoning behind each choice.

---

## Decision 1: Server-Rendered Blade over SPA Framework

**Context:** Choosing between a single-page application framework (React, Vue, Inertia.js) and traditional server-rendered Blade templates.

**Decision:** Use Laravel Blade with Alpine.js for interactivity.

**Reasoning:**
- Blade eliminates the need for a separate API layer, reducing overall complexity.
- Alpine.js provides lightweight reactivity (dropdowns, modals, toggles) without a full JavaScript build pipeline for components.
- Server-rendered pages are SEO-friendly by default and do not require hydration.
- The team does not need real-time collaborative editing, which would justify a heavier frontend framework.
- Blade components (`x-modal`, `x-stat-card`, etc.) provide reusability comparable to React/Vue components for this use case.

**Trade-offs accepted:**
- Page transitions require full HTTP round-trips rather than client-side navigation.
- Complex interactivity (like the Kanban board) requires explicit JavaScript (SortableJS) rather than a reactive state manager.

---

## Decision 2: MySQL over SQLite for Development

**Context:** Choosing the default development database.

**Decision:** Use MySQL 8.x as the primary database for both development and production.

**Reasoning:**
- MySQL supports the full range of column types, indexing strategies, and JSON operations that DevLoop uses.
- Using the same database engine in development and production eliminates an entire category of "works on my machine" issues.
- The `activity_logs` table uses polymorphic relationships with JSON metadata columns, which benefit from MySQL's native JSON functions.
- SQLite is supported as a deployment alternative (documented in the Oracle deployment guide) for lightweight setups.

---

## Decision 3: Database-Backed Sessions over File Sessions

**Context:** Choosing a session storage driver.

**Decision:** Use the `database` session driver with encryption enabled.

**Reasoning:**
- Database sessions allow the application to query and invalidate specific user sessions — essential for the `Auth::logoutOtherDevices()` feature used after password changes and resets.
- Session encryption (`SESSION_ENCRYPT=true`) protects session payloads at rest.
- File-based sessions do not support per-user session invalidation without scanning the filesystem.
- Redis was not chosen because it adds an infrastructure dependency that is unnecessary for the current scale.

---

## Decision 4: Policy-Based Authorization over Middleware Gates

**Context:** How to enforce access control across the application.

**Decision:** Use Laravel Policies mapped to Eloquent models, invoked via `Gate::authorize()` in controllers.

**Reasoning:**
- Policies co-locate authorization logic with the model it protects, making it easy to find and audit.
- The `Gate::authorize()` pattern throws `AuthorizationException` automatically, returning a 403 response without manual error handling in controllers.
- Earlier versions of Notes and Snippets used inline `if ($model->user_id !== auth()->id()) abort(403)` checks — these were refactored to use `NotePolicy` and `SnippetPolicy` for consistency.
- Policies are automatically discovered by Laravel's naming convention (`NotePolicy` maps to `Note` model), requiring no manual registration.

---

## Decision 5: Tiered Rate Limiting Architecture

**Context:** How to protect the application against abuse without degrading the experience for legitimate users.

**Decision:** Define five named rate limiters with different thresholds, applied to route groups by operation type.

**Reasoning:**
- A single global limit would either be too restrictive for read-heavy workflows or too permissive for write-heavy abuse.
- Tiered limits (60/min reads, 30/min writes, 10/min creates, 5/min deletes) match the actual usage patterns of a project management tool — users read far more often than they create or delete.
- Named limiters (`throttle:global`, `throttle:write`, etc.) in `AppServiceProvider` can be adjusted in one place rather than scattered across route files.
- The login and password-reset flows have their own `throttle:5,1` middleware for additional protection, independent of the application rate limiters.

---

## Decision 6: Event-Driven Activity Logging

**Context:** How to track project activity (task created, status changed, comment added, member added) without coupling side effects to controllers.

**Decision:** Use Laravel's event system with a centralized `LogActivityListener`.

**Reasoning:**
- Events decouple the "what happened" (controller action) from the "what should we record" (activity log entry).
- A single listener handles all activity event types, writing to the `activity_logs` table with polymorphic subject references and JSON metadata.
- Adding a new tracked action requires only dispatching a new event — no changes to existing controllers or the listener.
- This pattern also makes it straightforward to add notification dispatch, webhook triggers, or analytics tracking in the future by attaching additional listeners.

---

## Decision 7: Tailwind CSS Custom Design Tokens over Default Palette

**Context:** Whether to use Tailwind's built-in color palette (e.g., `bg-gray-800`) or define custom semantic tokens.

**Decision:** Define custom tokens (`background`, `surface`, `primary-text`, `primary`, `accent`, `teal`) in `tailwind.config.js`.

**Reasoning:**
- Semantic tokens express intent (`bg-surface`) rather than implementation (`bg-gray-900`), making the design system easier to understand and modify.
- Changing the entire color scheme requires editing only the token definitions in `tailwind.config.js`, not hundreds of class references across templates.
- The custom palette creates a cohesive visual identity (the "cosmic dark" theme) that would be harder to achieve with Tailwind's generic utility colors.
- The Inter font family was chosen over browser defaults for its excellent readability at small sizes, which matters for information-dense developer interfaces.

---

## Decision 8: Soft Deletes for Projects and Tasks

**Context:** Whether deleting a project or task should permanently remove the database row.

**Decision:** Use Laravel's `SoftDeletes` trait on `Project` and `Task` models.

**Reasoning:**
- Accidental deletion is costly when a project contains dozens of tasks, comments, and activity history.
- Soft deletes preserve data integrity for foreign key references from `activity_logs`, which use polymorphic relationships that would break with hard deletes.
- Recovery can be implemented in the future without data loss.
- Notes, snippets, comments, and discussions use hard deletes because they are lower-value, user-owned content that does not have downstream references.

---

## Decision 9: Bcrypt with 12 Rounds over Argon2

**Context:** Choosing a password hashing algorithm and cost factor.

**Decision:** Use Bcrypt with 12 rounds (configured via `BCRYPT_ROUNDS=12` in `.env`).

**Reasoning:**
- Bcrypt is the Laravel default and is widely supported across all PHP installations without additional extensions.
- 12 rounds provides a good balance between security and performance — hashing takes approximately 250ms, which is acceptable for login/registration endpoints.
- The `Password::defaults()` configuration in `AppServiceProvider` enforces complexity rules (mixed case, numbers, symbols, breach check) that reduce reliance on hash strength alone.
- Argon2id would be a marginal improvement but requires the `sodium` PHP extension, adding a deployment dependency.
