# syntax=docker/dockerfile:1

FROM php:8.4-apache AS php-base

ENV DEBIAN_FRONTEND=noninteractive
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN apt-get \
        -o Acquire::ForceIPv4=true \
        -o Acquire::Retries=5 \
        update \
    && apt-get \
        -o Acquire::ForceIPv4=true \
        -o Acquire::Retries=5 \
        install -y --no-install-recommends \
        curl \
        unzip \
        libcurl4-openssl-dev \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        libonig-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libxml2-dev \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j1 \
        pdo_mysql \
        pdo_pgsql \
        bcmath \
        curl \
        intl \
        zip \
        mbstring \
        gd \
        exif \
        dom \
        xml \
        opcache \
        pcntl \
    && a2enmod rewrite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN sed -ri \
    -e "s!/var/www/html!/var/www/html/public!g" \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf \
    && sed -ri \
    "s/AllowOverride None/AllowOverride All/g" \
    /etc/apache2/apache2.conf

WORKDIR /var/www/html


FROM php-base AS composer-dependencies

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

COPY . .

RUN composer dump-autoload \
    --no-dev \
    --optimize \
    --no-interaction


FROM node:22-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci \
    --no-audit \
    --no-fund

COPY . .

RUN npm run build


FROM php-base AS production

WORKDIR /var/www/html

COPY --from=composer-dependencies /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build

RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && rm -rf public/storage \
    && ln -s ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
