FROM php:8.2-fpm

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
      libzip-dev zip unzip \
      sqlite3 libsqlite3-dev \
 && docker-php-ext-configure zip \
 && docker-php-ext-install zip pdo pdo_sqlite \
 && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
