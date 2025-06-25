FROM php:8.2-fpm-bullseye

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y \
        libzip-dev zip unzip git curl libpng-dev libjpeg-dev libfreetype6-dev pkg-config libonig-dev libxml2-dev && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install zip pdo pdo_mysql mbstring gd && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto al contenedor
COPY . .

# Instalar Composer manualmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Crear carpetas necesarias para Laravel
RUN mkdir -p bootstrap/cache storage && \
    chmod -R 775 bootstrap/cache storage && \
    chown -R www-data:www-data bootstrap/cache storage

# OJO: Deja que Jenkins instale dependencias, no lo hagas aquí
# RUN composer install --no-interaction --prefer-dist --optimize-autoloader

CMD ["php-fpm"]