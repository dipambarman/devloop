<p align="center">
  <img src="public/favicon.ico" alt="DevLoop Logo" width="100">
</p>
<h1 align="center">DevLoop</h1>

<p align="center">
  <strong>A premium, developer-focused project management and collaboration tool.</strong><br>
  Built with Laravel, TailwindCSS, and AlpineJS.
</p>

<p align="center">
  <a href="#features">Features</a> •
  <a href="#tech-stack">Tech Stack</a> •
  <a href="#installation">Installation</a> •
  <a href="#screenshots">Screenshots</a>
</p>

---

## 🚀 About DevLoop

DevLoop is a modern task and project management system designed specifically for developers. It features a stunning dark-mode UI ("Cosmic Indigo"), interactive Kanban boards, robust markdown note-taking, and real-time-like collaboration features. 

Whether you're organizing a solo side-hustle or collaborating with a team, DevLoop keeps you in the loop.
ke 
## ✨ Features

- **Organize with Projects**: Group your tasks, team members, and milestones into dedicated workspaces.
- **Interactive Kanban Boards**: Drag-and-drop tasks between columns (To Do, In Progress, Review, Done) effortlessly.
- **Developer Profiles**: Showcase your skills, GitHub, Portfolio, and LinkedIn natively.
- **Team Collaboration**: Invite users to your projects with role-based access control (Owner, Member, Viewer).
- **Markdown Notes & Snippets**: Keep track of documentation and code snippets with full GitHub-flavored markdown support.
- **Real-time Notifications**: Get alerted when tasks are assigned to you or when project states change.
- **Beautiful Dark UI**: Carefully crafted design system using TailwindCSS, featuring glassmorphism, gradients, and micro-animations.
- **100% Responsive**: Fully functional mobile layout with an off-canvas drawer navigation.

## 🛠️ Tech Stack

- **Framework**: Laravel 12 (PHP 8.3)
- **Frontend**: Blade Templates, TailwindCSS (v3), Alpine.js
- **Database**: MySQL 8.4
- **Icons**: Heroicons
- **Drag & Drop**: SortableJS

## 📦 Installation (Local Development)

To run DevLoop locally, follow these steps:

### Prerequisites
- PHP 8.2 or higher (8.3 recommended)
- Composer
- Node.js & NPM
- MySQL Database

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/devloop.git
   cd devloop
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install NPM Dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   Copy the `.env.example` file and configure your database settings:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Make sure to update `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in your `.env`.*

5. **Run Migrations & Seeders**
   This will set up the database schema and populate it with rich demo data:
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Start the Development Servers**
   You'll need two terminal windows.
   
   Terminal 1 (Laravel Server):
   ```bash
   php artisan serve
   ```
   
   Terminal 2 (Vite Asset Bundler):
   ```bash
   npm run dev
   ```

7. **Visit the App**
   Open your browser and navigate to `http://localhost:8000`. You can log in using the demo account:
   - **Email**: test@example.com
   - **Password**: password

## 📸 Screenshots

*(Add screenshots of your application here once deployed!)*

### Dashboard
<!-- <img src="docs/dashboard.png" alt="DevLoop Dashboard"> -->

### Kanban Board
<!-- <img src="docs/kanban.png" alt="DevLoop Kanban Board"> -->

### Developer Profile
<!-- <img src="docs/profile.png" alt="DevLoop Profile"> -->

---
<p align="center">
  Built with ❤️ by <a href="https://github.com/yourusername">Dipam Barman</a>
</p>
