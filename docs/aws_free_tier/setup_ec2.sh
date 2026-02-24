#!/bin/bash
# Script de inicialización para Ubuntu 24.04 / 22.04 en AWS EC2 (Free Tier)
# Instala Apache, PHP, Composer y prepara el entorno para Laravel.

# Actualizando el sistema
sudo apt update
sudo apt upgrade -y

# Instalando Apache y utilidades
sudo apt install apache2 curl git unzip -y

# Añadiendo repositorio para PHP (Ondrej Sury)
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

PHP_VERSION="8.3"

# Instalando PHP y extensiones para Laravel
sudo apt install php$PHP_VERSION php$PHP_VERSION-cli php$PHP_VERSION-common php$PHP_VERSION-mysql php$PHP_VERSION-zip php$PHP_VERSION-gd php$PHP_VERSION-mbstring php$PHP_VERSION-curl php$PHP_VERSION-xml php$PHP_VERSION-bcmath libapache2-mod-php$PHP_VERSION -y

# Instalando Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
php -r "unlink('composer-setup.php');"

# Configurando permisos de Apache
# Habilitar mod_rewrite para Laravel
sudo a2enmod rewrite
sudo systemctl restart apache2
