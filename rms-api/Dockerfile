FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    ca-certificates \
    git \
    unzip \
    libzip-dev \
    libonig-dev \
    libicu-dev \
    libcurl4-openssl-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        bcmath \
        intl \
        zip \
        curl \
        gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

CMD ["sh", "-c", "set -e; php artisan config:clear; php artisan storage:link || true; attempt=0; until php artisan migrate --database=central --path=database/migrations/central --force; do attempt=$((attempt + 1)); if [ "$attempt" -ge 30 ]; then exit 1; fi; sleep 2; done; exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]