FROM php:8.2-fpm-bullseye


RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y \
        libzip-dev zip unzip git curl \
    && docker-php-ext-install zip pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN mkdir -p bootstrap/cache && chmod -R 775 bootstrap/cache


RUN composer install --no-interaction --prefer-dist --optimize-autoloader

CMD ["php-fpm"]
