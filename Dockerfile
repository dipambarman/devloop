# Production Dockerfile for DevLoop (Laravel 12 + PHP 8.3)
FROM php:8.3-fpm-alpine

# Set working directory
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
    zip

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
    pdo_sqlite

# Copy Composer binary from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files into container
COPY . .

# Set proper permissions for Laravel directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
