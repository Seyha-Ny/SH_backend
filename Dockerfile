# ============================================================
# Stage 1: Composer dependencies
# ============================================================
FROM composer:2.8 AS vendor

WORKDIR /app

COPY ecommerce_backend/composer.json ecommerce_backend/composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-suggest \
    --optimize-autoloader \
    --prefer-dist

# ============================================================
# Stage 2: Node frontend (build static assets)
# ============================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY ecommerce_frontend/package.json ecommerce_frontend/package-lock.json ./
RUN npm ci --omit=dev

COPY ecommerce_frontend/ .
RUN npm run build

# ============================================================
# Stage 3: PHP-FPM runtime
# ============================================================
FROM php:8.3-fpm-alpine AS backend

RUN apk add --no-cache \
    postgresql-libs \
    && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    postgresql-dev \
    && docker-php-ext-install \
    pdo_mysql \
    pdo_pgsql \
    bcmath \
    opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Copy production PHP config
COPY ecommerce_backend/docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY ecommerce_backend/docker/php-opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# Copy app from project root (only ecommerce_backend contents)
COPY ecommerce_backend/composer.json ecommerce_backend/composer.lock ./
COPY --from=vendor /app/vendor ./vendor
COPY ecommerce_backend/ .

# Copy frontend build output
COPY --from=frontend /app/dist ./public/dist

# Storage setup
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Optimize for production
RUN php artisan optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 9000

COPY ecommerce_backend/docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]

# ============================================================
# Stage 4: Nginx serving static + proxying PHP
# ============================================================
FROM nginx:1.27-alpine AS nginx

RUN apk add --no-cache tzdata

COPY ecommerce_backend/docker/nginx.conf /etc/nginx/nginx.conf
COPY ecommerce_backend/docker/nginx-default.conf /etc/nginx/conf.d/default.conf

COPY --from=backend /var/www/html /var/www/html

EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]
