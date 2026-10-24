# 🚀 DevLoop — Production Deployment & Launch Playbook

> **Your Complete Step-by-Step Manual for $0/Month Cloud Hosting & Real User Growth.**
> 
> Follow the tracks below in order. All steps are optimized for 100% free-tier services (Oracle Cloud Always Free, DuckDNS, Let's Encrypt, Resend, UptimeRobot).
> 
> Check off items as you complete them, and notify Antigravity when you finish Track 1–5 so we can verify the live deployment!

---

## 🗺️ Execution Tracks Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                       DEVLOOP PRODUCTION PLAYBOOK                           │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │
     ┌───────────────────┬─────────────┴───────┬───────────────────┐
     ▼                   ▼                     ▼                   ▼
┌──────────────┐   ┌──────────────┐      ┌──────────────┐    ┌──────────────┐
│   TRACK 1    │   │   TRACK 2    │      │   TRACK 3    │    │   TRACK 4    │
│ Oracle Cloud │   │ DuckDNS & IP │      │ Server Stack │    │   Free SSL   │
│  Instance    │   │ Subdomain    │      │ Provisioning │    │  & Security  │
└──────────────┘   └──────────────┘      └──────────────┘    └──────────────┘
     │                   │                     │                   │
     └───────────────────┼─────────────────────┴───────────────────┘
                         ▼
     ┌───────────────────┴─────────────────────┐
     ▼                                         ▼
┌──────────────┐                         ┌──────────────┐
│   TRACK 5    │                         │   TRACK 6    │
│ CI/CD Deploy │                         │ Free Email & │
│  Automation  │                         │  Monitoring  │
└──────────────┘                         └──────────────┘
                         │
                         ▼
                 ┌──────────────┐
                 │   TRACK 7    │
                 │ Launch Blitz │
                 │ & Promotion  │
                 └──────────────┘
```

---

## 📌 Track 1: Oracle Cloud Always Free VM Instance

### 1.1 Sign Up / Log In
- [ ] Go to [cloud.oracle.com](https://cloud.oracle.com/) and log in (or sign up for an Always Free tier account).
  > **Note**: A credit card is required for identity verification, but Oracle will not charge for Always Free resources.

### 1.2 Create the Compute Instance
- [ ] In the Oracle Cloud Console, navigate to:  
  **Navigation Menu (☰)** → **Compute** → **Instances** → Click **Create Instance**.
- [ ] Configure the fields exactly as follows:
  - **Name**: `devloop-prod`
  - **Compartment**: Keep default (root compartment).
  - **Placement**: Keep default Availability Domain.
  - **Image**: Click **Change Image** → Select **Ubuntu 24.04 LTS Minimal** (or Ubuntu 22.04 LTS).
  - **Shape**: Click **Change Shape**:
    - Select **Ampere (ARM-based Processor)**.
    - Select `VM.Standard.A1.Flex`.
    - Set **OCPU count**: `2` (Always Free tier includes up to 4 OCPUs).
    - Set **Memory (GB)**: `12` (Always Free tier includes up to 24 GB RAM).
  - **Networking**:
    - Primary VNIC: Keep default Virtual Cloud Network (VCN) and Subnet.
    - **Assign a public IPv4 address**: Select **Yes**.
  - **Add SSH Keys**:
    - Select **Generate a key pair for me** → Click **Save private key** (save as `devloop-key.key` to your local computer).
    - *Alternatively, paste your existing `id_rsa.pub` or `id_ed25519.pub` if you have one.*
  - **Boot Volume**: Keep default (50 GB).
- [ ] Click **Create**.
- [ ] Wait 1–2 minutes until the instance status changes from **PROVISIONING** (orange) to **RUNNING** (green).
- [ ] **Note down your Public IP Address**:
  - `Public IP`: `___________________` (e.g., `129.153.xxx.xxx`)

### 1.3 Open Firewall Ports in Oracle Cloud (Crucial!)
> Oracle instances block incoming HTTP/HTTPS traffic by default in the Virtual Cloud Network.

- [ ] In the instance details page, under **Instance Information**, find **Virtual Cloud Network** and click your VCN name.
- [ ] Under **Resources** on the left, click **Security Lists**.
- [ ] Click the **Default Security List for...**.
- [ ] Click **Add Ingress Rules** and add the following rule:
  - **Source Type**: `CIDR`
  - **Source CIDR**: `0.0.0.0/0`
  - **IP Protocol**: `TCP`
  - **Source Port Range**: Leave empty (`All`)
  - **Destination Port Range**: `80, 443`
  - **Description**: `Allow HTTP and HTTPS inbound`
- [ ] Click **Add Ingress Rules**.

---

## 📌 Track 2: Free Domain Setup (DuckDNS)

- [ ] Go to [duckdns.org](https://www.duckdns.org/) and sign in using your **GitHub** account.
- [ ] Under the **domains** section:
  - In the **sub domain** field, enter: `devloop` (or your preferred subdomain).
  - In the **current ip** field, paste your **Oracle Cloud Public IP** from Track 1.
  - Click **add domain**.
- [ ] Your domain is now: `devloop.duckdns.org` pointing to your Oracle instance!
- [ ] Copy your **DuckDNS Token** shown at the top of the DuckDNS page:
  - `DUCKDNS_TOKEN`: `___________________________________`

---

## 📌 Track 3: Automated Server Provisioning

### 3.1 Connect to the Server via SSH
Open **PowerShell** or **Git Bash** on your local machine:

```powershell
# If using Windows PowerShell and your key is in Downloads:
ssh -i "$HOME\Downloads\devloop-key.key" ubuntu@<YOUR_ORACLE_PUBLIC_IP>
```

> **Permission Tip**: If Windows complains about key permissions, run:
> ```powershell
> icacls "$HOME\Downloads\devloop-key.key" /inheritance:r
> icacls "$HOME\Downloads\devloop-key.key" /grant:r "$($env:USERNAME):(R)"
> ```

### 3.2 Run the 1-Line Provisioning Script
Once connected to the server (`ubuntu@devloop-prod`), execute the automated provisioning script:

```bash
curl -sSL https://raw.githubusercontent.com/dipambarman/devloop/main/scripts/setup-oracle.sh | bash
```

*This automated script will:*
- Update all Ubuntu packages.
- Reset and configure `iptables` and `ufw` firewall (opening ports 22, 80, 443).
- Install Docker Engine & Docker Compose plugin.
- Install Certbot for Let's Encrypt SSL.
- Add user `ubuntu` to the `docker` group.
- Create `/var/www/devloop` and `/backups` directories with proper permissions.

### 3.3 Apply User Permissions
Log out and log back in so the Docker group takes effect:
```bash
exit
```
Reconnect:
```powershell
ssh -i "$HOME\Downloads\devloop-key.key" ubuntu@<YOUR_ORACLE_PUBLIC_IP>
```

---

## 📌 Track 4: Clone, Configure & Launch Docker Stack

### 4.1 Clone the DevLoop Repository
```bash
git clone https://github.com/dipambarman/devloop.git /var/www/devloop
cd /var/www/devloop
```

### 4.2 Configure Production Environment
Copy the production environment template:
```bash
cp .env.production .env
```

Edit `.env` using nano:
```bash
nano .env
```

Check and fill in these key variables:
```env
APP_NAME=DevLoop
APP_ENV=production
APP_DEBUG=false
APP_URL=https://devloop.duckdns.org

# Generate a strong password for MySQL:
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=devloop
DB_USERNAME=devloop_user
DB_PASSWORD=YOUR_STRONG_PASSWORD_HERE
DB_ROOT_PASSWORD=YOUR_STRONG_ROOT_PASSWORD_HERE

# Redis Cache & Sessions (handled by redis container):
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379
```
*(Press `Ctrl + O` then `Enter` to save, `Ctrl + X` to exit nano).*

### 4.3 Launch the Multi-Container Docker Stack
```bash
cd /var/www/devloop
docker compose up -d --build
```

Verify that all 5 services are running:
```bash
docker compose ps
```
You should see:
- `devloop-app` (PHP 8.3-FPM) — Up
- `devloop-nginx` (Nginx 1.27) — Up (Ports 80, 443)
- `devloop-mysql` (MySQL 8.4) — Up (healthy)
- `devloop-redis` (Redis 7) — Up (healthy)
- `devloop-queue` (Laravel Queue Worker) — Up

### 4.4 Initialize Database & Encryption Key
Inside the running container:
```bash
# 1. Generate Application Key
docker compose exec -T app php artisan key:generate --force

# 2. Run Database Migrations
docker compose exec -T app php artisan migrate --force

# 3. Create Storage Symlink
docker compose exec -T app php artisan storage:link

# 4. Cache production configurations
docker compose exec -T app php artisan config:cache
docker compose exec -T app php artisan route:cache
docker compose exec -T app php artisan view:cache
docker compose exec -T app php artisan event:cache
```

### 4.5 Obtain Free SSL Certificate (HTTPS via Let's Encrypt)
Temporarily stop Nginx container to bind port 80 for Certbot:
```bash
docker compose stop nginx
sudo certbot certonly --standalone -d devloop.duckdns.org --agree-tos -m your-email@example.com --non-interactive
```

Mount certificates and start Nginx with SSL:
```bash
# Copy certificates to project SSL folder
mkdir -p /var/www/devloop/docker/nginx/ssl
sudo cp /etc/letsencrypt/live/devloop.duckdns.org/fullchain.pem /var/www/devloop/docker/nginx/ssl/
sudo cp /etc/letsencrypt/live/devloop.duckdns.org/privkey.pem /var/www/devloop/docker/nginx/ssl/
sudo chmod -R 755 /var/www/devloop/docker/nginx/ssl

# Restart Nginx
docker compose up -d nginx
```

### 4.6 Set Up Automated Cron Jobs (Backups & DuckDNS IP Renewal)
Edit crontab on the server:
```bash
crontab -e
```
Add the following two lines at the bottom:
```bash
# 1. Update DuckDNS IP every 5 minutes (replace YOUR_DUCKDNS_TOKEN)
*/5 * * * * curl -s "https://www.duckdns.org/update?domains=devloop&token=YOUR_DUCKDNS_TOKEN&ip=" > /dev/null

# 2. Automated Daily Database Backup at 2:00 AM (14-day rotation)
0 2 * * * /var/www/devloop/scripts/backup.sh >> /var/log/devloop-backup.log 2>&1
```

---

## 📌 Track 5: Setup Automated CI/CD (Push-to-Deploy)

When you push code to GitHub (`main` branch), GitHub Actions will automatically run all tests and deploy the updates to your Oracle Cloud server via SSH.

- [ ] On GitHub, go to your repository: `https://github.com/dipambarman/devloop`.
- [ ] Click **Settings** → **Secrets and variables** → **Actions**.
- [ ] Click **New repository secret** and add the following 3 secrets:

| Secret Name | Value | Description |
|---|---|---|
| `SERVER_HOST` | `<YOUR_ORACLE_PUBLIC_IP>` | The public IP of your Oracle VM |
| `SERVER_USER` | `ubuntu` | The SSH username |
| `SSH_PRIVATE_KEY` | Content of `devloop-key.key` | Open `devloop-key.key` in Notepad, copy everything (including `-----BEGIN RSA PRIVATE KEY-----`) |

Now, whenever you or Antigravity push changes to `main`, GitHub Actions will test and deploy automatically!

---

## 📌 Track 6: Free Email Delivery & 24/7 Monitoring

### 6.1 Transactional Email (Resend — 3,000 emails/month free)
- [ ] Go to [resend.com](https://resend.com/) and sign up with GitHub.
- [ ] Navigate to **API Keys** → Click **Create API Key**.
- [ ] In `/var/www/devloop/.env`, set:
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.resend.com
  MAIL_PORT=465
  MAIL_USERNAME=resend
  MAIL_PASSWORD=re_YOUR_API_KEY
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS="notifications@devloop.duckdns.org"
  MAIL_FROM_NAME="DevLoop"
  ```
- [ ] Run `docker compose exec -T app php artisan config:cache`

### 6.2 24/7 Uptime Monitoring (UptimeRobot — 100% Free)
- [ ] Sign up at [uptimerobot.com](https://uptimerobot.com/).
- [ ] Click **Add New Monitor**:
  - **Monitor Type**: `HTTPS`
  - **Friendly Name**: `DevLoop Production`
  - **URL**: `https://devloop.duckdns.org`
  - **Monitoring Interval**: `5 minutes`
  - **Alert Contacts**: Your email / Telegram.
- [ ] Click **Create Monitor**. You now have instant alerts if the server ever goes down!

---

## 📌 Track 7: User Acquisition Launch Blitz (0 → 100+ Users)

Once the site is live at `https://devloop.duckdns.org`:

### Day 1: Soft Launch (Social Proof)
- [ ] **GitHub**: Ensure repository is public. Add topics: `laravel`, `developer-tools`, `kanban`, `project-management`, `docker`, `tailwindcss`.
- [ ] **Twitter / X Thread**:
  > *"I built DevLoop: an open-source developer workspace + Kanban platform built with Laravel 12 & Docker. Hosted 100% free on Oracle Cloud Ampere. Live demo: https://devloop.duckdns.org. Here's what I learned building it 🧵👇"*
- [ ] **LinkedIn Post**: Share the technical stack, architecture diagrams, and the cosmic dark design.

### Day 3: Developer Community Blitz
- [ ] **Hacker News (Show HN)**:
  - Title: `Show HN: DevLoop – Open-source developer collaboration platform built with Laravel 12`
  - Body: Explain why you built it, the architecture (rate limiting, multi-stage Docker, SortableJS Kanban), and invite feedback.
- [ ] **Reddit**:
  - Post in `r/laravel`: Share the repo and lessons learned with Laravel 12 + Docker.
  - Post in `r/SideProject` & `r/selfhosted`: Share the $0/month Always Free deployment story.
- [ ] **Dev.to / Hashnode**:
  - Publish: *"How I Built and Deployed a Full-Stack Developer Workspace with Laravel 12 on Oracle Cloud Free Tier"*.

---

## 📋 What to Report Back to Antigravity

Once you finish setting up Tracks 1–4, notify me with:
1. **Your Domain/IP** (e.g., `devloop.duckdns.org`).
2. **Whether you added the GitHub Secrets** (`SERVER_HOST`, `SERVER_USER`, `SSH_PRIVATE_KEY`).
3. **Any error or roadblock encountered** during provisioning.

I will then run live end-to-end browser tests against your production URL and verify all features (Auth, Onboarding, Kanban, Notes, Snippets)! 🚀
