# DevLoop — System Architecture

This document describes the internal architecture of DevLoop, detailing how each layer communicates, how data flows through the system, and the reasoning behind structural choices.

---

## High-Level Overview

DevLoop follows a server-rendered MVC architecture where every page is composed on the server and delivered as HTML. There is no separate API backend or single-page application framework — the browser receives fully rendered Blade templates on each request.

```
┌──────────────────────────────────────────────────────┐
│                    Client Browser                     │
│            (HTML, Tailwind CSS, Alpine.js)            │
└──────────────────────┬───────────────────────────────┘
                       │  HTTP Request
                       ▼
┌──────────────────────────────────────────────────────┐
│                  Routing Layer                        │
│          routes/web.php  •  routes/auth.php           │
│       (Middleware: auth, throttle, verified)          │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│              Form Request Validation                  │
│   StoreProjectRequest, StoreTaskRequest, etc.         │
│       (Input sanitization before controllers)         │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│                   Controllers                         │
│  ProjectController, TaskController, NoteController    │
│       (Thin — delegates to services/models)           │
└─────────┬──────────────────────────┬─────────────────┘
          │                          │
          ▼                          ▼
┌─────────────────┐    ┌────────────────────────┐
│  Service Layer  │    │  Authorization Layer   │
│ DashboardService│    │ ProjectPolicy          │
│                 │    │ TaskPolicy             │
│                 │    │ NotePolicy             │
│                 │    │ SnippetPolicy          │
└────────┬────────┘    └────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────────────────┐
│               Eloquent ORM (Models)                   │
│   User, Project, Task, Note, Snippet, Comment,        │
│   Discussion, DiscussionReply, Tag, ActivityLog       │
└──────────────────────┬───────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────┐
│                   MySQL 8.4                           │
│          (Database Sessions, Cache, Queue)            │
└──────────────────────────────────────────────────────┘
```

---

## Layer Breakdown

### 1. Routing Layer

Routes are split into two files for separation of concerns:

- **`routes/web.php`** — All authenticated application routes, organized into tiered throttle groups (read, write, upload, critical).
- **`routes/auth.php`** — Authentication flows (login, register, password reset, email verification).

Every route group applies middleware in layers. The global `throttle:global` limiter runs at 60 requests per minute for baseline protection. Write operations narrow to 30/min, creation to 10/min, and destructive operations to 5/min.

### 2. Form Request Validation

Validation rules live in dedicated `FormRequest` classes rather than inline in controllers. This keeps controllers focused on orchestration:

- `StoreProjectRequest` — Validates project name, description, color, github_repo.
- `StoreTaskRequest` — Validates project ownership via `authorize()`, checks title, status, priority, assignee, due_date, and tags.
- `UpdateProjectRequest` / `UpdateTaskRequest` — Handle update-specific rules.
- `ProfileUpdateRequest` — Validates profile fields including bio, skills, and social URLs.
- `LoginRequest` — Handles email/password validation and rate-limited authentication.

### 3. Controllers

Controllers follow the thin-controller pattern. They receive validated input from FormRequests, delegate authorization to Policies, and call Eloquent models or services for data operations. Controllers never contain raw SQL, business rules, or validation logic.

Resource controllers (`ProjectController`, `TaskController`, `NoteController`, `SnippetController`) use Laravel's RESTful resource routing, mapping HTTP verbs to standard CRUD methods.

### 4. Service Layer

Complex query logic that crosses multiple models is extracted into dedicated services. The `DashboardService` aggregates statistics, recent projects, recent tasks, and task distribution data for the dashboard — keeping the `DashboardController` thin.

### 5. Authorization Layer

Authorization uses Laravel's Policy system. Each policy maps to a model and defines what actions a user can perform:

- **ProjectPolicy** — Owner can update, delete, manage members. Members and owners can view.
- **TaskPolicy** — Project members can view and update. Only project owners can delete.
- **NotePolicy** — Only the note author can view, update, or delete (private resources).
- **SnippetPolicy** — Only the snippet author can view, update, or delete (private resources).
- **CommentPolicy** — Comment author or project owner can delete.

### 6. Event System

Domain events decouple side effects from core operations. When a task status changes, a comment is posted, or a member is added, the corresponding event is dispatched. The `LogActivityListener` captures these events and writes structured entries to the `activity_logs` table.

Events include: `TaskCreated`, `TaskStatusChanged`, `TaskPriorityChanged`, `TaskAssigneeChanged`, `CommentAdded`, `DiscussionCreated`, `DiscussionReplyAdded`, `MemberAdded`, `MemberRemoved`.

### 7. Frontend Architecture

The frontend uses server-rendered Blade templates enhanced with Alpine.js for interactivity (dropdowns, modals, toggles) and Tailwind CSS for styling. There is no frontend build framework like React or Vue — this is intentional to minimize complexity and keep the codebase approachable.

Reusable UI is built through Blade components (`x-modal`, `x-danger-button`, `x-text-input`, `x-stat-card`, etc.) which encapsulate both markup and styling.

SortableJS powers the Kanban board drag-and-drop functionality. Chart.js renders dashboard statistics. Toastify handles notification toasts.

---

## Security Architecture

| Layer | Mechanism |
|-------|-----------|
| Authentication | Laravel Breeze with session-based auth, email verification, password reset |
| Session Security | Database-backed sessions, encrypted, HttpOnly, SameSite=Lax |
| Password Policy | Min 8 chars, mixed case, numbers, symbols, HaveIBeenPwned breach check |
| CSRF Protection | Automatic via `@csrf` in Blade forms |
| Rate Limiting | Tiered: 60/min global, 30/min writes, 10/min creates, 5/min deletes |
| Security Headers | X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy, HSTS |
| Authorization | Policy-based access control per model |
| Input Validation | FormRequest classes with strict rules on every endpoint |

---

## Directory Structure

```
app/
├── Events/            # Domain events (TaskCreated, CommentAdded, etc.)
├── Http/
│   ├── Controllers/   # Thin controllers organized by domain
│   │   └── Auth/      # Authentication controllers (login, register, etc.)
│   ├── Middleware/     # SecurityHeadersMiddleware
│   └── Requests/      # FormRequest validation classes
│       └── Auth/      # LoginRequest
├── Listeners/         # Event listeners (LogActivityListener)
├── Models/            # Eloquent models (User, Project, Task, etc.)
├── Policies/          # Authorization policies per model
├── Providers/         # AppServiceProvider (rate limiters, password policy)
└── Services/          # Business logic services (DashboardService)

bootstrap/
└── app.php            # Application bootstrap, middleware registration

config/
├── auth.php           # Guards, providers, password reset config
└── session.php        # Session driver, encryption, cookie settings

database/
├── factories/         # Model factories for testing
├── migrations/        # Schema definitions (17 migration files)
└── seeders/           # Database seeders

resources/
└── views/
    ├── auth/          # Login, register, password reset views
    ├── components/    # Reusable Blade components
    ├── discussions/   # Discussion thread views
    ├── layouts/       # Application layout templates
    ├── notes/         # Note CRUD views
    ├── profile/       # User profile views
    ├── projects/      # Project CRUD + Kanban board views
    ├── snippets/      # Code snippet views
    └── tasks/         # Task CRUD views

routes/
├── auth.php           # Authentication routes
└── web.php            # Application routes (tiered throttle groups)
```
