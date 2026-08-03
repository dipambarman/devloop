# DevLoop — Database Schema & Relationships

This document describes every table in the DevLoop database, their columns, relationships, and design rationale.

---

## Entity-Relationship Overview

```
┌─────────┐       owns        ┌──────────┐      has many     ┌─────────┐
│  User   │──────────────────▶│ Project  │────────────────▶│  Task   │
│         │                   │          │                  │         │
│         │◀──member of──────▶│          │                  │         │
│         │  (project_user)   │          │                  │         │
└────┬────┘                   └────┬─────┘                  └────┬────┘
     │                             │                              │
     │ has many                    │ has many                     │ has many
     ▼                             ▼                              ▼
┌─────────┐               ┌──────────────┐               ┌───────────┐
│  Note   │               │  Discussion  │               │  Comment  │
│ Snippet │               │              │               │           │
└─────────┘               └──────┬───────┘               └───────────┘
                                 │ has many
                                 ▼
                          ┌──────────────────┐
                          │ DiscussionReply  │
                          └──────────────────┘
```

---

## Tables

### `users`

The core authentication table. Extended with developer profile fields.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `name` | string | Display name |
| `email` | string (unique) | Login email, must be verified |
| `email_verified_at` | timestamp (nullable) | When email was verified |
| `password` | string (hidden) | Bcrypt-hashed password (12 rounds) |
| `bio` | text (nullable) | Short developer biography |
| `skills` | json (nullable) | Array of skill tags, e.g. `["PHP", "Laravel", "Vue"]` |
| `github_url` | string (nullable) | GitHub profile URL |
| `portfolio_url` | string (nullable) | Personal portfolio URL |
| `linkedin_url` | string (nullable) | LinkedIn profile URL |
| `remember_token` | string (hidden) | "Remember me" session token |
| `created_at` / `updated_at` | timestamps | Standard Laravel timestamps |

**Relationships:**
- `hasMany(Project)` via `owner_id`
- `belongsToMany(Project)` via `project_user` pivot
- `hasMany(Task)` via `assignee_id` and `creator_id`
- `hasMany(Note)`, `hasMany(Snippet)`

---

### `projects`

Represents a development project that contains tasks, discussions, and team members.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `name` | string | Project name |
| `slug` | string (unique) | URL-friendly slug derived from name |
| `description` | text (nullable) | Project description |
| `status` | string | `active` (default), can be changed |
| `color` | string | Hex color for UI display, default `#6366F1` |
| `github_repo` | string (nullable) | Linked GitHub repository URL |
| `owner_id` | foreign → users | Project creator/owner |
| `created_at` / `updated_at` | timestamps | Standard timestamps |
| `deleted_at` | timestamp (nullable) | Soft delete marker |

**Relationships:**
- `belongsTo(User)` as owner
- `belongsToMany(User)` as members via `project_user`
- `hasMany(Task)`, `hasMany(Discussion)`, `hasMany(ActivityLog)`

---

### `project_user` (pivot)

Many-to-many relationship between users and projects for team membership.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `project_id` | foreign → projects | The project |
| `user_id` | foreign → users | The team member |
| `role` | string | `member` (default) or `viewer` |
| `created_at` / `updated_at` | timestamps | When membership was created |

**Constraint:** Unique composite on `(project_id, user_id)` — a user can only be a member once per project.

---

### `tasks`

Work items within a project. Supports status workflow, priority levels, and assignment.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `project_id` | foreign → projects | Parent project (cascades on delete) |
| `title` | string | Task title |
| `description` | text (nullable) | Detailed task description |
| `status` | string | `todo` → `in_progress` → `review` → `done` |
| `priority` | string | `low`, `medium` (default), `high`, `urgent` |
| `assignee_id` | foreign → users (nullable) | Assigned developer (null on user delete) |
| `creator_id` | foreign → users | Who created the task |
| `due_date` | date (nullable) | Task deadline |
| `order_column` | integer (nullable) | Position for Kanban board ordering |
| `created_at` / `updated_at` | timestamps | Standard timestamps |
| `deleted_at` | timestamp (nullable) | Soft delete marker |

**Relationships:**
- `belongsTo(Project)`, `belongsTo(User)` as assignee, `belongsTo(User)` as creator
- `hasMany(Comment)`, `belongsToMany(Tag)` via `tag_task`

---

### `comments`

User comments attached to tasks.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `task_id` | foreign → tasks | Parent task |
| `user_id` | foreign → users | Comment author |
| `content` | text | Comment body (max 2000 chars enforced at validation) |
| `created_at` / `updated_at` | timestamps | Standard timestamps |

---

### `tags`

Reusable labels that can be attached to tasks.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `name` | string | Tag label |
| `created_at` / `updated_at` | timestamps | Standard timestamps |

### `tag_task` (pivot)

Many-to-many between tags and tasks.

| Column | Type | Description |
|--------|------|-------------|
| `tag_id` | foreign → tags | The tag |
| `task_id` | foreign → tasks | The task |

---

### `notes`

Private markdown notes belonging to individual users, optionally linked to a project.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `user_id` | foreign → users | Note owner (cascades on delete) |
| `project_id` | foreign → projects (nullable) | Optional project association |
| `title` | string | Note title |
| `content` | text (nullable) | Note body |
| `is_pinned` | boolean | Whether note is pinned to the top |
| `created_at` / `updated_at` | timestamps | Standard timestamps |

---

### `snippets`

Private code snippets with syntax highlighting metadata, belonging to individual users.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `user_id` | foreign → users | Snippet owner (cascades on delete) |
| `project_id` | foreign → projects (nullable) | Optional project association |
| `title` | string | Snippet title |
| `code` | text | The code content |
| `language` | string | Programming language for syntax highlighting (default: `plaintext`) |
| `created_at` / `updated_at` | timestamps | Standard timestamps |

---

### `discussions`

Project-scoped discussion threads for team communication.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `project_id` | foreign → projects | Parent project (cascades on delete) |
| `user_id` | foreign → users | Discussion author (cascades on delete) |
| `title` | string | Discussion title |
| `content` | text | Discussion body (max 10000 chars at validation) |
| `is_pinned` | boolean | Whether pinned to the top of the list |
| `created_at` / `updated_at` | timestamps | Timestamps; `updated_at` bumps on new reply |

---

### `discussion_replies`

Replies within a discussion thread.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `discussion_id` | foreign → discussions | Parent discussion |
| `user_id` | foreign → users | Reply author |
| `content` | text | Reply body (max 10000 chars at validation) |
| `created_at` / `updated_at` | timestamps | Standard timestamps |

---

### `activity_logs`

Polymorphic activity tracking for project timelines. Records every meaningful action.

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint (PK) | Auto-increment primary key |
| `project_id` | foreign → projects | Which project this activity belongs to |
| `user_id` | foreign → users | Who performed the action |
| `event` | string | Event key: `task_created`, `status_changed`, `comment_added`, etc. |
| `subject_type` | string (nullable) | Polymorphic model class (e.g., `App\Models\Task`) |
| `subject_id` | bigint (nullable) | Polymorphic model ID |
| `description` | string | Human-readable: "created task 'Fix login bug'" |
| `meta` | json (nullable) | Structured data: `{"old_status": "todo", "new_status": "done"}` |
| `created_at` / `updated_at` | timestamps | Standard timestamps |

---

### `notifications`

Laravel's built-in notification system table for in-app notifications.

| Column | Type | Description |
|--------|------|-------------|
| `id` | uuid (PK) | UUID primary key |
| `type` | string | Notification class name |
| `notifiable_type` | string | Polymorphic (usually `App\Models\User`) |
| `notifiable_id` | bigint | User ID |
| `data` | text | JSON notification payload |
| `read_at` | timestamp (nullable) | When the notification was read |
| `created_at` / `updated_at` | timestamps | Standard timestamps |

---

### Infrastructure Tables

| Table | Purpose |
|-------|---------|
| `sessions` | Database-backed session storage (encrypted) |
| `cache` / `cache_locks` | Database-backed cache storage |
| `jobs` / `job_batches` / `failed_jobs` | Queue worker tables |
| `password_reset_tokens` | Password reset token storage (60-min expiry) |

---

## Cascade Rules

All foreign keys use `cascadeOnDelete` to maintain referential integrity:

- Deleting a **User** removes their projects, tasks (as creator), notes, snippets, discussions, and replies.
- Deleting a **Project** removes its tasks, comments, discussions, replies, activity logs, and member associations.
- Deleting a **Task** removes its comments and tag associations.
- Deleting a **Discussion** removes its replies.
- Task `assignee_id` uses `nullOnDelete` — if the assigned user is deleted, the task remains but becomes unassigned.
