FROM php:8.4-fpm

# 1. Configuración de Permisos

ARG UID=1000
ARG GID=1000

# Cambiamos el ID del usuario y del grupo de www-data
RUN usermod -u ${UID} www-data && \
    groupmod -g ${GID} www-data || true && \
    chown -R www-data:www-data /var/www/html

# 2. Instalación de Dependencias del Sistema

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libxml2-dev \
    libonig-dev \
    libssl-dev \
    && rm -rf /var/lib/apt/lists/*

# 3. Instalación de Extensiones de PHP

RUN docker-php-ext-install pdo_mysql mbstring xml ctype zip 

# Configuramos GD antes de instalarla
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install gd

# 4. Instalamos Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 5. Instalamos Xdebug
RUN pecl install xdebug || true \
    && docker-php-ext-enable xdebug

# 6. Configuración de PHP (Usar el de producción es a menudo más seguro)
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

# 7. Establece el directorio de trabajo y el usuario
WORKDIR /var/www/html
USER www-data