FROM node:24.14.1-alpine AS frontend-build

WORKDIR /frontend

COPY rms-frontend/package.json rms-frontend/package-lock.json ./
RUN npm ci

COPY rms-frontend/ ./
ENV VITE_API_BASE_URL=/api
RUN npm run build

FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    ca-certificates \
    mariadb-server \
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

COPY rms-api/ .
COPY --from=frontend-build /frontend/dist/ ./public/

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

COPY docker/render-entrypoint.sh /usr/local/bin/render-entrypoint.sh
RUN chmod +x /usr/local/bin/render-entrypoint.sh

CMD ["/usr/local/bin/render-entrypoint.sh"]
