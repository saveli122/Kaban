FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev libicu-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip gd intl opcache \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Удобные UID/GID для Linux/WSL (на Windows можно опустить)
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
USER www-data
