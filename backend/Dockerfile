# Cambiamos a 8.4 para soportar Symfony 8
FROM php:8.4-fpm-alpine

# Instalar dependencias del sistema para PHP y extensiones
RUN apk add --no-cache \
    libpng-dev \
    libzip-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo pdo_mysql gd zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar el código
COPY . .

# Ejecutar la instalación de dependencias
RUN composer install --no-dev --optimize-autoloader

# Permisos correctos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]