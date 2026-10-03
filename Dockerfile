# ==========================================
# Stage 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy dependency definitions
COPY package*.json ./

# Install npm dependencies (including devDependencies required for Vite & Tailwind)
RUN npm ci --prefer-offline --no-audit || npm install

# Copy application source code for building assets
COPY . .

# Compile production assets to /app/public/build
RUN npm run build

# ==========================================
# Stage 2: Production PHP Application
# ==========================================
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and web server
RUN apk add --no-cache \
    nginx \
    curl \
    bash \
    sqlite \
    sqlite-dev \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev

# Install official PHP extension installer helper
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install essential PHP extensions for Laravel 11/12
RUN install-php-extensions \
    pdo \
    pdo_sqlite \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    ctype \
    curl \
    dom \
    fileinfo \
    json \
    mbstring \
    openssl \
    pcre \
    tokenizer \
    xml \
    zip \
    opcache \
    intl \
    pcntl \
    gd

# Install Composer from official image
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Copy composer files and install production dependencies
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copy application code
COPY . .

# Copy compiled frontend assets from the node-builder stage
COPY --from=node-builder /app/public/build ./public/build

# Generate optimized autoload files
RUN composer dump-autoload --optimize --no-dev --no-interaction

# Copy custom Nginx and PHP configurations
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Setup entrypoint startup script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

# Create required directories and set proper permissions
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database \
    /run/nginx \
    /var/log/nginx \
    && chown -R www-data:www-data /var/www/html /run/nginx /var/log/nginx \
    && chmod -R 775 storage bootstrap/cache

# Render default internal port
EXPOSE 10000

# Container entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
