@echo off
title DevLoop — Cloudflare Public Tunnel
echo ==========================================================
echo   Launching DevLoop Cloudflare Public Tunnel...
echo ==========================================================
echo.

if not exist cloudflared.exe (
    echo [ERROR] cloudflared.exe not found!
    echo Download from: https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe
    pause
    exit /b 1
)

echo Starting tunnel for http://localhost:8000 ...
echo [TIP] Keep this window open to keep the public URL online!
echo.

cloudflared.exe tunnel --url http://127.0.0.1:8000
pause
