#!/usr/bin/env bash
# ==============================================================================
# DevLoop — Oracle Cloud Always Free Provisioning Script
# Target OS: Ubuntu 24.04 LTS (ARM64 / Ampere A1 or x86_64)
# ==============================================================================

set -euo pipefail

echo "=================================================="
echo "  🚀 Starting DevLoop Server Provisioning"
echo "=================================================="

# 1. Update system packages
echo "==> Updating system packages..."
sudo apt-get update && sudo apt-get upgrade -y

# 2. Reset and configure firewall for Oracle Cloud
echo "==> Configuring firewall..."
sudo iptables -F
sudo apt-get install -y iptables-persistent netfilter-persistent ufw
sudo netfilter-persistent save

sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp comment 'SSH'
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'
sudo ufw --force enable

# 3. Install core dependencies
echo "==> Installing Git, Curl, Unzip, and Build tools..."
sudo apt-get install -y git curl wget unzip zip software-properties-common ca-certificates gnupg

# 4. Install Docker & Docker Compose Plugin
echo "==> Installing Docker Engine & Docker Compose..."
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Allow current user to run Docker without sudo
sudo usermod -aG docker "$USER"

# 5. Install Certbot for Let's Encrypt SSL
echo "==> Installing Certbot..."
sudo apt-get install -y certbot

# 6. Prepare application directory
echo "==> Creating application directory at /var/www/devloop..."
sudo mkdir -p /var/www/devloop
sudo chown -R "$USER":"$USER" /var/www/devloop

# 7. Create backup directory
sudo mkdir -p /backups
sudo chown -R "$USER":"$USER" /backups

echo "=================================================="
echo "  ✅ Server Provisioning Complete!"
echo "=================================================="
echo ""
echo "Next Steps:"
echo "1. Log out and log back in to apply Docker group permissions:"
echo "   exit"
echo "2. Clone your repository:"
echo "   git clone https://github.com/dipambarman/devloop.git /var/www/devloop"
echo "3. Copy .env.production to .env:"
echo "   cd /var/www/devloop && cp .env.production .env"
echo "4. Generate app key and configure credentials in .env:"
echo "   nano .env"
echo "5. Launch the multi-container stack:"
echo "   docker compose up -d --build"
echo ""
