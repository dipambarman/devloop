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

Preparing the application for production hosting.

- [ ] Oracle Cloud Always Free deployment (documented in `oracle-deployment-roadmap.md`)
- [ ] Nginx reverse proxy configuration
- [ ] Free SSL via Let's Encrypt / Certbot
- [ ] DuckDNS domain setup
- [ ] Laravel production caching (config, routes, views)
- [ ] Queue worker setup for background jobs
- [ ] Automated database backups

### Phase 6: Communication & Notifications 📋

Expanding the notification system beyond in-app alerts.

- [ ] Email notifications for task assignments
- [ ] Email notifications for discussion replies and mentions
- [ ] Configurable notification preferences per user
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

Current version: **v1.3.0**
