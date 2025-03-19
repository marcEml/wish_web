FROM php:8.2-fpm

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql pgsql

WORKDIR /var/www/html
