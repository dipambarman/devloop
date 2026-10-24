# ─────────────────────────────────────────────────────────
# DevLoop Production Dockerfile
# Laravel 12 + PHP 8.3-FPM + Node 20 (multi-stage build)
# ─────────────────────────────────────────────────────────

# ── Stage 1: Build frontend assets ──────────────────────
FROM node:20-alpine AS frontend
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources/ resources/
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# ── Stage 2: Install PHP dependencies ──────────────────
FROM composer:latest AS composer
WORKDIR /build
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
COPY . .
RUN composer dump-autoload --optimize

# ── Stage 3: Production image ──────────────────────────
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    curl \
    git \
    icu-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    oniguruma-dev \
    sqlite-dev \
    unzip \
    zip \
    linux-headers

# Install PHP extensions required by Laravel
RUN docker-php-ext-install \
    bcmath \
    exif \
    gd \
    intl \
    mbstring \
    pcntl \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    opcache \
    zip

# Copy PHP production configuration
COPY docker/php/php-production.ini /usr/local/etc/php/conf.d/custom.ini

# Copy application files
COPY --from=composer /build/vendor vendor/
COPY . .

# Copy compiled frontend assets from build stage
COPY --from=frontend /build/public/build public/build/

# Set proper permissions for Laravel directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create log directory for PHP
RUN mkdir -p /var/log/php && chown www-data:www-data /var/log/php

EXPOSE 9000

CMD ["php-fpm"]
