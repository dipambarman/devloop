# 🚀 Oracle Cloud Always Free Deployment Roadmap for DevLoop

This guide provides a step-by-step roadmap to deploy **DevLoop** (Laravel 12 + Nginx + PHP 8.3 + SQLite/MySQL + Tailwind/Vite + Let's Encrypt SSL) on **Oracle Cloud Infrastructure (OCI) Always Free Tier**.

---

## 📌 Deployment Overview

```
Client Browser
      │ (HTTPS Port 443)
      ▼
DuckDNS Domain (e.g., devloop.duckdns.org)
      │
      ▼
Oracle Cloud Security List & Ubuntu iptables (Port 80 / 443)
      │
      ▼
Nginx Reverse Proxy
      │
      ▼
PHP 8.3-FPM ──► Laravel 12 App (/var/www/devloop) ──► SQLite / MySQL
```

---

## 🎯 Phase 1: Oracle Cloud Instance Setup

### 1.1 Create Instance on OCI
1. Log in to [Oracle Cloud Console](https://cloud.oracle.com/).
2. Go to **Compute** -> **Instances** -> **Create Instance**.
3. **Name**: `devloop-server`
4. **Image & Shape**:
   - Image: **Ubuntu 24.04 LTS** (or 22.04 LTS).
   - Shape: Click **Change Shape** -> Select **Ampere (ARM)** -> `VM.Standard.A1.Flex` with **2 OCPUs and 12 GB RAM** (100% Always Free!).
5. **Networking**: Keep default Virtual Cloud Network (VCN) and assign a **Public IP**.
6. **SSH Key**: Save/Download the Private Key (`.key` or `.pem`) to your local computer.
7. Click **Create**.

---

### 1.2 Open Firewall Ports in Oracle Console
Oracle instances block inbound web traffic by default.

1. In the OCI Console, go to **Networking** -> **Virtual Cloud Networks**.
2. Click your VCN -> **Security Lists** -> Click **Default Security List for...**.
3. Click **Add Ingress Rules**:
   - **Source CIDR**: `0.0.0.0/0`
   - **IP Protocol**: `TCP`
   - **Destination Port Range**: `80, 443`
4. Save the rule.

---

## 🔐 Phase 2: Free Domain Setup (DuckDNS)

1. Go to [DuckDNS.org](https://www.duckdns.org/) and sign in with GitHub or Google.
2. Enter your desired domain name (e.g., `devloop`).
3. Set the IP address to your **Oracle Cloud Public IP**.
4. Click **Update IP**.
5. Your domain is now: `http://devloop.duckdns.org`

---

## 🖥️ Phase 3: Server Provisioning (SSH Script)

### 3.1 SSH into your Oracle Instance
From Windows Terminal / PowerShell:
```powershell
ssh -i "path/to/your-key.key" ubuntu@<YOUR-ORACLE-PUBLIC-IP>
```

### 3.2 Run One-Line Automated Server Setup
Once logged into your server, run the following commands to install Nginx, PHP 8.3, Node 20, Composer, and Certbot:

```bash
# 1. Update system & firewall rules
sudo apt update && sudo apt upgrade -y
sudo iptables -F
sudo netfilter-persistent save

# 2. Install Nginx, Git, Curl, Unzip
sudo apt install -y nginx git curl unzip ufw

# 3. Add PHP 8.3 repository & install PHP + extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-common php8.3-sqlite3 \
    php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip \
    php8.3-gd php8.3-bcmath php8.3-intl composer

# 4. Install Node.js 20 & NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# 5. Install Certbot for Free SSL
sudo apt install -y certbot python3-certbot-nginx
```

---

## ⚙️ Phase 4: Laravel Project Deployment

### 4.1 Clone Project Repository
```bash
sudo mkdir -p /var/www/devloop
sudo chown -R ubuntu:ubuntu /var/www/devloop
git clone https://github.com/dipambarman/devloop.git /var/www/devloop
cd /var/www/devloop
```

### 4.2 Install Dependencies & Build Frontend
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies and compile CSS/Vite assets
npm ci
npm run build
```

### 4.3 Configure Environment & Database
```bash
cp .env.example .env

# Generate encryption key
php artisan key:generate

# Set up SQLite database
touch database/database.sqlite

# Run database migrations
php artisan migrate --force

# Create storage symlink
php artisan storage:link

# Set production permissions
sudo chown -R www-data:www-data /var/www/devloop
sudo chmod -R 775 /var/www/devloop/storage /var/www/devloop/bootstrap/cache /var/www/devloop/database
```

Update your `.env` file (`nano .env`):
```env
APP_NAME=DevLoop
APP_ENV=production
APP_DEBUG=false
APP_URL=https://devloop.duckdns.org
DB_CONNECTION=sqlite
```

---

## 🌐 Phase 5: Nginx & Free SSL Configuration

### 5.1 Configure Nginx Server Block
Create Nginx configuration (`sudo nano /etc/nginx/sites-available/devloop`):

```nginx
server {
    listen 80;
    server_name devloop.duckdns.org;
    root /var/www/devloop/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/devloop /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

### 5.2 Obtain Free SSL Certificate (HTTPS)
Run Certbot:
```bash
sudo certbot --nginx -d devloop.duckdns.org
```

Follow the prompts. Certbot will automatically configure SSL and renew certificates automatically!

---

## ⚡ Phase 6: Laravel Production Optimization

Run these caching commands on your server:
```bash
cd /var/www/devloop
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ✅ Deployment Checklist

- [ ] Oracle Ampere Instance Created (Ubuntu 24.04)
- [ ] OCI Ingress Rule Added (Port 80 & 443)
- [ ] DuckDNS Domain pointing to Oracle Public IP
- [ ] SSH connected & setup script executed
- [ ] Code cloned & `.env` configured
- [ ] `composer install` & `npm run build` completed
- [ ] `php artisan migrate --force` executed
- [ ] Nginx configured & test successful
- [ ] Certbot SSL HTTPS enabled
- [ ] Production caching applied (`php artisan config:cache`)
