# ==============================================================================
# DevLoop — Cloudflare Quick Tunnel Launcher
# Exposes your local DevLoop (Laragon / localhost) to the public internet
# with instant HTTPS, zero credit card, and zero bandwidth limits.
# ==============================================================================

Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "  🚀 Launching DevLoop Cloudflare Public Tunnel" -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan

$cloudflaredPath = Join-Path $PSScriptRoot "..\cloudflared.exe"

if (-not (Test-Path $cloudflaredPath)) {
    Write-Host "❌ cloudflared.exe not found in project root." -ForegroundColor Red
    Write-Host "Please download it from: https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe" -ForegroundColor Yellow
    exit 1
}

Write-Host "==> Checking if DevLoop is running on Laragon / localhost..." -ForegroundColor Yellow

# Start Cloudflare Tunnel pointing to localhost (Laragon Apache on Port 80)
# or http://127.0.0.1:8000 if using php artisan serve
& $cloudflaredPath tunnel --url http://localhost
