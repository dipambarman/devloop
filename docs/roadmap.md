# DevLoop — Development Roadmap

This roadmap outlines the planned features and improvements for DevLoop, organized by development phase.

---

## Completed Phases

### Phase 1: Foundation ✅

The core application skeleton and primary functionality.

- [x] Laravel 12 project setup with PHP 8.3
- [x] Authentication system (Breeze) — login, register, email verification, password reset
- [x] Custom dark theme design system with Tailwind CSS
- [x] Project CRUD with soft deletes
- [x] Task CRUD with status workflow and priority levels
- [x] Authorization policies for Projects and Tasks
- [x] Form request validation on all endpoints

### Phase 2: Developer Workspace ✅

Personal productivity tools for individual developers.

- [x] Markdown notes with project linking and pin-to-top
- [x] Code snippet storage with language-based syntax highlighting
- [x] Developer profile pages — bio, skills, GitHub, portfolio, LinkedIn
- [x] Statistics dashboard with Chart.js visualizations
- [x] DashboardService for aggregated metrics

### Phase 3: Collaboration ✅

Team features that turn DevLoop from a personal tool into a collaboration platform.

- [x] Kanban board with drag-and-drop (SortableJS)
- [x] Inline task status, priority, and assignee updates
- [x] Task comments with author/owner delete permissions
- [x] Tag system for task categorization
- [x] Project member invitations by email (member/viewer roles)
- [x] Project discussion boards with threaded replies and pinning
- [x] Event-driven activity logging with polymorphic subjects
- [x] In-app notification system with mark-as-read

### Phase 4: Security Hardening ✅

Production-grade security for a developer platform.

- [x] Strong password policy (mixed case, numbers, symbols, breach check)
- [x] Tiered rate limiting (global, write, search, upload, critical)
- [x] Security headers middleware (XSS, clickjacking, MIME-sniff, HSTS)
- [x] Session invalidation on password change and reset
- [x] Policy-based authorization for Notes and Snippets
- [x] Route restructuring into throttled operation tiers
- [x] Content length limits on user-submitted text

---

## Upcoming Phases

### Phase 5: Deployment & Infrastructure 🚧

Preparing the application for production hosting and public launch.

- [x] Multi-stage production Dockerfile (Node 20 + Composer 2 + PHP 8.3-FPM)
- [x] Docker Compose 5-service production stack (App, Nginx, MySQL 8.4, Redis 7, Queue worker)
- [x] Nginx reverse proxy configuration (`docker/nginx/default.conf`) with gzip & cache headers
- [x] PHP 8.3 production configuration (`docker/php/php-production.ini`) with OPcache & JIT
- [x] MySQL 8.4 production tuning configuration (`docker/mysql/my.cnf`)
- [x] GitHub Actions CI test pipeline (`.github/workflows/test.yml`)
- [x] GitHub Actions CD automated deploy pipeline (`.github/workflows/deploy.yml`)
- [x] Production environment template (`.env.production`)
- [x] Public marketing landing page (`resources/views/welcome.blade.php`) with cosmic dark theme
- [x] Crawler & SEO optimization (`public/robots.txt`)
- [x] Automated daily database backup command (`php artisan db:backup`) with gzip compression and retention rotation
- [x] Production backup runner script (`scripts/backup.sh`)
- [x] Deep application health check probe (`/health` & `/api/health`) covering database latency, cache, disk, and queue status
- [x] Queue worker and failed job monitoring (`php artisan queue:monitor-health`)
- [x] Automated daily backup and hourly queue monitoring schedule in `routes/console.php`
- [ ] Oracle Cloud Always Free VM instance setup (Ubuntu 24.04 on Ampere A1)
- [ ] DuckDNS dynamic DNS setup (`devloop.duckdns.org`)
- [ ] Free SSL certificate via Let's Encrypt / Certbot

### Phase 6: Communication & Notifications 🚧

Expanding the notification system with event-driven alerts and user preferences.

- [x] Multi-channel notifications for task assignments (`TaskAssignedNotification`)
- [x] Multi-channel notifications for discussion replies (`DiscussionRepliedNotification`)
- [x] Multi-channel notifications for task comments (`CommentAddedNotification`)
- [x] Multi-channel notifications for project invitations (`MemberAddedNotification`)
- [x] Configurable notification preferences per user (in-app vs email toggles in profile)
- [x] Interactive notification dropdown with deep links to tasks, discussions & projects
- [ ] Digest emails (daily/weekly summary of project activity)
- [ ] Webhook support for external integrations

### Phase 7: Advanced Project Management 📋

Features for teams managing larger projects.

- [ ] Calendar view for tasks with due dates
- [ ] Sprint planning — group tasks into time-boxed sprints
- [ ] Milestone tracking — group tasks into release milestones
- [ ] Task dependencies — mark tasks as blocked by other tasks
- [ ] Time tracking — log hours spent on tasks
- [ ] Recurring tasks — auto-create tasks on a schedule
- [ ] Bulk task operations — multi-select and batch update

### Phase 8: Integrations 📋

Connecting DevLoop with external developer tools.

- [ ] GitHub integration — link commits and pull requests to tasks
- [ ] GitHub webhook receiver — auto-update task status on PR merge
- [ ] Import projects from GitHub repositories
- [ ] Slack/Discord notifications for project activity
- [ ] REST API with Sanctum token authentication

### Phase 9: Analytics & Insights 📋

Data-driven features for project managers and team leads.

- [ ] Burndown charts — track sprint progress over time
- [ ] Velocity tracking — measure team throughput across sprints
- [ ] Member contribution reports — tasks completed, comments posted per user
- [ ] Project health scores — overdue ratio, completion trends
- [ ] Custom dashboard widgets — let users configure their dashboard layout

### Phase 10: Advanced Platform Features 📋

Longer-term features that expand DevLoop's scope.

- [ ] Real-time collaboration — live updates via WebSockets (Laravel Reverb)
- [ ] AI task assistant — suggest task breakdowns, auto-assign priorities
- [ ] File attachments on tasks and discussions
- [ ] Custom fields — user-defined metadata on tasks
- [ ] Team workspaces — multi-tenant organization support
- [ ] Audit log — detailed security audit trail of all user actions
- [ ] Two-factor authentication (2FA)
- [ ] OAuth login (GitHub, Google)

---

## Versioning

DevLoop follows semantic versioning:

- **Major** — Breaking changes or significant architectural shifts
- **Minor** — New features and non-breaking enhancements
- **Patch** — Bug fixes and security patches

Current version: **v1.4.0 (Infrastructure & Launch Prep)**
