# docker/php/Dockerfile
FROM php:8.3-fpm

# Install necessary PHP extensions and other dependencies
RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Expose the port
EXPOSE 9000

CMD ["php-fpm"]
