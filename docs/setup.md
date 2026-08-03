# DevLoop — Local Development Setup

This guide walks through setting up DevLoop on a local machine from scratch.

---

## Prerequisites

Ensure the following are installed before proceeding:

| Software | Minimum Version | Purpose |
|----------|----------------|---------|
| PHP | 8.3+ | Backend runtime |
| Composer | 2.x | PHP dependency manager |
| Node.js | 20+ | Frontend tooling |
| npm | 9+ | Node package manager |
| MySQL | 8.0+ | Database server |
| Git | 2.x | Version control |

---

## Step 1 — Clone the Repository

```bash
git clone https://github.com/dipambarman/devloop.git
cd devloop
```

---

## Step 2 — Install PHP Dependencies

```bash
composer install
```

This installs Laravel, Breeze, Pest, and all backend packages defined in `composer.json`.

---

## Step 3 — Install Frontend Dependencies

```bash
npm install
```

This installs Tailwind CSS, Alpine.js, Vite, SortableJS, and supporting build tools.

---

## Step 4 — Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate the application encryption key:

```bash
php artisan key:generate
```

Open `.env` and configure your database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devloop
DB_USERNAME=root
DB_PASSWORD=your_password_here
```

Create the MySQL database if it does not already exist:

```sql
CREATE DATABASE devloop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Step 5 — Run Database Migrations

```bash
php artisan migrate
```

This creates all 17 tables: users, projects, tasks, comments, tags, notes, snippets, discussions, activity_logs, notifications, sessions, cache, jobs, and supporting pivot tables.

To seed with sample data (if seeders are available):

```bash
php artisan migrate --seed
```

---

## Step 6 — Create Storage Symlink

```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`, allowing uploaded files to be served via the web.

---

## Step 7 — Start Development Servers

You need two terminal sessions running simultaneously:

**Terminal 1 — Laravel Development Server:**

```bash
php artisan serve
```

**Terminal 2 — Vite Asset Compiler:**

```bash
npm run dev
```

Alternatively, use the single-command setup from `composer.json`:

```bash
composer dev
```

This starts the PHP server, queue worker, log watcher, and Vite all at once using `concurrently`.

---

## Step 8 — Access the Application

Open your browser and navigate to:

```
http://localhost:8000
```

Register a new account to get started. Email verification is configured (uses `log` driver locally — check `storage/logs/laravel.log` for verification links).

---

## Common Issues

### "SQLSTATE[HY000] [2002] Connection refused"

MySQL is not running. Start it with:

```bash
# Windows (XAMPP)
Start MySQL from XAMPP Control Panel

# macOS (Homebrew)
brew services start mysql

# Linux
sudo systemctl start mysql
```

### "Vite manifest not found"

The Vite development server is not running. Open a second terminal and run `npm run dev`.

### "Permission denied on storage/"

Fix directory permissions:

```bash
chmod -R 775 storage bootstrap/cache
```

### Email verification links not arriving

In local development, the mail driver is set to `log`. Check `storage/logs/laravel.log` for the verification URL.

---

## Environment Variables Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_ENV` | `production` | Set to `local` for development |
| `APP_DEBUG` | `false` | Set to `true` for detailed error pages |
| `APP_URL` | `http://localhost:8000` | Base URL for the application |
| `DB_CONNECTION` | `mysql` | Database driver |
| `SESSION_DRIVER` | `database` | Where sessions are stored |
| `SESSION_ENCRYPT` | `true` | Whether session data is encrypted |
| `BCRYPT_ROUNDS` | `12` | Password hashing cost factor |
| `MAIL_MAILER` | `log` | Mail driver (use `smtp` for production) |
| `QUEUE_CONNECTION` | `database` | Queue backend |

---

## Production Build

To compile assets for production deployment:

```bash
npm run build
```

This generates optimized, minified CSS and JavaScript in the `public/build/` directory.
