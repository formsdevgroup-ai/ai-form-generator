# Stage 0: Composer deps (for Ziggy - needed during Vite build)
FROM composer:2 AS composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --ignore-platform-reqs

# Stage 1: Build frontend assets (Vite + Vue)
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm install --no-audit --legacy-peer-deps

COPY . .
# Copy Ziggy from composer stage (app.js imports from vendor/tightenco/ziggy)
COPY --from=composer /app/vendor ./vendor

RUN npm run build

# Stage 2: PHP app with nginx
FROM richarvey/nginx-php-fpm:3.1.6

WORKDIR /var/www/html

COPY . .

# Copy built Vite assets from frontend stage
COPY --from=frontend /app/public/build ./public/build

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
# Route all requests (e.g. /login, /register) to Laravel's index.php
ENV PHP_CATCHALL 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1

# Deploy script runs on container start
COPY docker/render-start.sh /render-start.sh
RUN chmod +x /render-start.sh

CMD ["/render-start.sh"]
