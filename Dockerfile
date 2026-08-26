# syntax=docker/dockerfile:1

# ---------- Stage 1: build frontend assets ----------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources

RUN npm run build

# ---------- Stage 2: All-in-One Runtime (PHP + MariaDB + Nginx + Websockify + Supervisord) ----------
FROM php:8.4-cli-alpine

RUN apk add --no-cache \
        mariadb \
        mariadb-client \
        nginx \
        supervisor \
        openssl \
        icu-dev \
        libxml2-dev \
        linux-headers \
        unzip \
        python3 \
        py3-pip \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        opcache \
        dom \
        xml \
        simplexml \
        xmlwriter \
        xmlreader \
    && pip3 install --break-system-packages --no-cache-dir websockify==0.13.0

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first (cached layer)
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

# Copy application source & built assets
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

# Configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 443 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
