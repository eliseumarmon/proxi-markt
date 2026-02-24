# Despliegue de ProxiMarkt en AWS

Este documento registra todas las fases, decisiones arquitectónicas, problemas encontrados y sus soluciones detalladas durante el despliegue a producción de ProxiMarkt (Vue + Laravel + MySQL) en Amazon Web Services (AWS) utilizando un modelo distribuido.

---

## Arquitectura desplegada

Adoptamos una arquitectura orientada a microservicios distribuidos aprovechando estrictamente componentes elegibles para la **AWS Free Tier (Capa Gratuita)**:

1. **Frontend (Amazon S3 + CloudFront):** Single Page Application en Vue generada mediante Vite. Alojada de forma estática y privada en S3, expuesta mediante CloudFront (CDN) para proveer entrega rápida global, caché eficiente y HTTPS gratuito.
2. **Backend (Amazon EC2):** Servidor API REST Laravel corriendo sobre Ubuntu 24.04 con Apache2 y PHP 8.3 (`t2.micro` o `t3.micro`).
3. **Base de Datos (Amazon RDS):** Instancia gestionada MySQL 8.x (`db.t3.micro`) aislada en red interna VPC y sin acceso público mediante configuraciones estrictas de *Security Groups*.

---

## Pros y contras de las decisiones arquitectónicas

### Frontends en s3 + CloudFront (SPA distribuido)

* **PROS:** Rendimiento máximo gracias a la distribución global de edge locations (CDN). Seguridad extra (el bucket de origen S3 es privado, el acceso se realiza por Origin Access Control). Coste cercano a $0 por volumen bajo y tráfico TLS/SSL gratuito provisto por AWS CloudFront de forma nativa (ahorrándonos la gestión y el mantenimiento de renovar certificados vía *Let's Encrypt* en el propio servidor).
* **CONTRAS:** Mayor complejidad en enrutamientos y gestión de cachés (invalidaciones obligatorias). SPA como Vue requiere reglas especializadas de *Error Pages* (forzar 403 y 404 a `/index.html`) para procesar las rutas del lado de cliente.

### Ec2 con apache (stateless API)

* **PROS:** Control absoluto sobre módulos del servidor de aplicaciones (PHP-FPM, mod_rewrite, cronjobs de Laravel) sin pagar la cuota de Elastic Beanstalk y asumiendo todo el control SysAdmin.
* **CONTRAS:** Mantenimiento (parches de SO) dependientes de nosotros. Como las fotos (Storage) actualmente se alojan aquí, hemos bloqueado la capacidad de *Load Balancing*, haciéndola temporalmente *Stateful* (la pérdida de esta EC2 implicaría la pérdida del disco).

<div style="page-break-before:always";/>

### RDS managed

* **PROS:** Backups automáticos diarios, parches automáticos y separación de responsabilidades que evita ahogar los recursos de la máquina EC2 en consultas complejas.
* **CONTRAS:** Es el elemento de AWS que más velozmente consume el Free Tier (750h se consumen el mismo mes si está 24/7 vivo).

### Proxy inverso en CloudFront (la solución maestra)

En lugar de exponer directamente la API EC2 de manera insegura, o generar certificados locales para la API, optamos por usar CloudFront como proxy TLS.

* **PROS:** Evitamos el bloqueo estricto de navegadores modernos (`blocked:mixed-content`) que impide enviar peticiones POST de un HTTPS seguro a una conexión HTTP desnuda. Permite llamadas relativas `/api/*`.
* **CONTRAS:** Requirió configurar minuciosamente comportamientos temporales estrictos en CloudFront (Políticas `AllViewer` origin request, deshabilitar Cachés en este Path) y ajustes en Apache (`ServerAlias *`) para ignorar los *Host headers* inyectados por la Content Delivery Network.

---

## Dificultades encontradas y soluciones aplicadas

El despliegue requirió resolver en tiempo real numerosos bloqueos técnicos inherentes a la comunicación entre sistemas independientes en la nube:

1. **Fallos de Conexión Base de Datos (Seguridad / Setup):**
    * *Problema:* Laravel devolvió *Connection Refused* o error de Base de Datos Inexistente.
    * *Causa:* SGs mal configurados en RDS o BBDD no importada. Laravel 11 asume SQLite local por defecto en caso de fallo u omisión en variables.
    * *Solución:* Autorizar SG a la máquina EC2, vaciar caché de configuración de Laravel (`config:cache`) y volcar SQL manual indicando flag de host externo (`-h [endpoint-rds]`).

2. **Pérdida de Configuración en el Build de Vue (Vite + Entorno Windows):**
    * *Problema:* Al subir a S3, Axios fallaba redirigiendo erróneamente porque `VITE_API_URL` estaba indefinida (*undefined*).
    * *Causa:* Conflictos en la ocultación de archivos `.env.production` por Windows y mala inyección durante `npm run build` en el intérprete de terminal local.
    * *Solución:* Forzamos primero URLs absolutas crudas en `axios.js` y posteriormente, implementamos proxy inverso permitiendo recular al modelo relativo seguro y estático (`/api/...`), aislando a la SPA de la IP.

3. **Respuestas Engañosas (200 OK HTML devueltas por API /register):**
    * *Problema:* Petición POST finalizaba sin fallo pero no registraba usuarios, devolviendo la vista genérica HTML de Front.
    * *Causa:* Para eludir falsos 404s en S3 vinculados a la SPA, la regla Custom Error Page empaquetaba los 404 a la API (Apache rechazando por Host Mismatch) enmascarándolos.
    * *Solución:* Edición del VirtualHost configurado por el Bash Script inicial, inyectándole directiva comodín (`ServerAlias *`), y apagando site por defecto de Ubuntu.

4. **Permisos de Escritura de Servidor Web (Permission Denied Laravel.log / Storage):**
    * *Problema:* HTTP 500 Subiendo catálogo o fotos.
    * *Causa:* El usuario CLI (`ubuntu`) tomó dominio recursivo de la estructura, privando al pool del Server Web (`www-data`).
    * *Solución:* Cesión de propiedad usando `chown -R www-data:www-data` únicamente en `storage` y `bootstrap/cache`.

5. **Reconstrucciones Relativas en Storage de Vue (Mixed Content Persistent):**
    * *Problema:* Al intentar mostrar imágenes de CloudFront, el CDN caía en bloqueos Mixtos porque las imágenes renderizaban la IP explícita proveniente del helper `storage.js`.
    * *Solución:* Se restauró el helper de modo que devuelva prefijos vacíos forzados (`const api = "/api"`) permitiendo a los visualizadores resolver como `/storage/productos/...` heredando el Origin SSL general.

---

## Guía paso a paso del despliegue realizado

Para futuras referencias, este fue el orden cronológico y las acciones exactas realizadas en la consola de AWS y en el código:

### 1. Preparación de la base de datos (RDS)

1. Creamos una base de datos MySQL 8 (`db.t3.micro`) en la capa gratuita de Amazon RDS.
2. La configuramos como privada (sin acceso público) y creamos un Security Group (`rds-sg-proximarkt`).
3. Para inicializar la estructura, nos conectamos vía SSH a la EC2 y usamos el cliente `mysql`. Se descubrió que la base de datos no existía por lo que se tuvo que crear y volcar las tablas manualmente:

   ```bash
   mysql -h <rds-endpoint>.rds.amazonaws.com -u root -p
   mysql> CREATE DATABASE proximarkt_db;
   mysql> exit;
   mysql -h <rds-endpoint>.rds.amazonaws.com -u root -p proximarkt_db < base.sql
   ```

<div style="page-break-before:always";/>

### 2. Preparación del servidor Backend (ec2)

1. Lanzamos una instancia Ubuntu 24.04 (`t2/t3.micro`) abriendo los puertos 22 (SSH), 80 (HTTP) y 443 (HTTPS), con entrada permitida al SG de la RDS (p. 3306).
2. Tuvimos problemas con el script de **User Data** (`setup_ec2.sh`) ya que no se ejecutó, y descargarlo vía `wget` desde GitHub trajo un archivo vacío en blanco. Por ende, ejecutamos los comandos de instalación de Apache, PHP 8.3 y Composer de manera 100% manual por SSH:

   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install apache2 curl git unzip -y
   sudo apt install software-properties-common -y
   sudo add-apt-repository ppa:ondrej/php -y
   sudo apt update
   sudo apt install php8.3 php8.3-cli php8.3-common php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath libapache2-mod-php8.3 -y
   
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   sudo mv composer.phar /usr/local/bin/composer
   php -r "unlink('composer-setup.php');"
   
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. Clonamos el repositorio, generamos el entorno de producción e instalamos las dependencias:

   ```bash
   cd /var/www
   git clone <URL_REPO>
   cd backend
   cp .env.example .env
   # Editamos .env introduciendo credenciales del RDS y DB_CONNECTION=mysql
   nano .env
   composer install --optimize-autoloader --no-dev
   php artisan config:cache
   ```

4. Poblamos los datos iniciales requeridos mediante Seeders:

   ```bash
   php artisan db:seed --class=CategoriaSeeder --force
   ```

<div style="page-break-before:always";/>

### 3. Ajustes de permisos y reparación de apache

1. Creamos el archivo host virtual manualmente (`sudo nano /etc/apache2/sites-available/proximarkt.conf`) con el siguiente bloque de configuración. Cabe destacar la adición del comodín `ServerAlias *` en lugar de sólo el `ServerName`, un paso vital para esquivar las mutaciones de Host Headers que provocaba el Proxy Inverso de CloudFront:

   ```apache
   <VirtualHost *:80>
       ServerName api.proximarkt.com
       ServerAlias *
       DocumentRoot /var/www/proxi-markt/backend/public
   
       <Directory /var/www/proxi-markt/backend/public>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   
       ErrorLog ${APACHE_LOG_DIR}/proximarkt_error.log
       CustomLog ${APACHE_LOG_DIR}/proximarkt_access.log combined
   </VirtualHost>
   ```

2. Deshabilitamos el sitio por defecto de Ubuntu, habilitamos el nuestro y aplicamos permisos finales:

   ```bash
   sudo a2dissite 000-default.conf
   sudo a2ensite proximarkt.conf
   
   sudo chown -R www-data:www-data /var/www/proxi-markt/backend/storage /var/www/proxi-markt/backend/bootstrap/cache
   php artisan storage:link
   sudo systemctl restart apache2
   ```

<div style="page-break-before:always";/>

### 4. Preparación del Frontend (s3)

1. Creamos un cubo (Bucket) en Amazon S3 (`proximarkt-frontend-bucket`) configurando todos sus accesos como bloqueados al público.
2. Forzamos URLs relativas estáticas (`/api` y `/storage`) en `api/axios.js` y `utils/storage.js` esquivando variables del `.env`.
3. Compilamos en local el código optimizado:

   ```bash
   cd frontend
   npm install
   npm run build
   ```

4. Subimos manualmente el contenido de la carpeta `/dist` devuelta al S3 (o vía AWS CLI `aws s3 sync dist/ s3://proximarkt-frontend-bucket --delete`).

### 5. La red de distribución segura (CloudFront)

1. Creamos una distribución apuntando hacia el Bucket S3 originado.
2. Aplicamos políticas OAC (Origin Access Control) devolviendo a S3 una policy en JSON que autorizó únicamente a CloudFront para leer sus archivos (antes esto se hacía manual ahora se encarga CloudFront de todo).
3. Configuramos la obligatoriedad de **Redirección de HTTP hacia HTTPS**.
4. **Configuración para Vue Router (El "Cerebro" de la SPA):** Editamos las *Error Pages* forzando respuestas a los errores 403 y 404 para que emitieran código `200 OK` devolviendo `/index.html`.
   * *¿Por qué es necesario esto?* En una aplicación SPA (Single Page Application) como Vue, toda la web es un único archivo `index.html`. Si un usuario recarga directamente la página en la URL `/auth`, CloudFront buscará una carpeta física llamada "auth" en Amazon S3. Como esa carpeta no existe, S3 devolverá un `404 Not Found` (o `403 Access Denied`). Al forzar que esos errores devuelvan siempre el `index.html` madre con un estado `200`, logramos que el "cerebro" Javascript de Vue tome el control en el navegador del usuario y monte la vista correcta de `/auth` dinámicamente.
5. **Implementación de Proxy Inverso (Backend + Storage):**
   * Añadimos el EC2 como Origen secundario, esquivando la restricción de IP de CloudFront al utilizar el **DNS Público de IPv4** (`ec2-x-x-x-x...compute.amazonaws.com`) gratuito de la propia instancia Amazon EC2.
   * Creamos Behavior para `/api/*` (`AllViewer` origin request, Caché Deshabilitada, TODOS los métodos HTTP permitidos).
   * Creamos Behavior para `/storage/*` (Caché Optimizada `CachingOptimized`, métodos GET).

### Finalización

Tras vaciar la caché de CloudFront (`/* Invalidations`) varias veces para refrescar el código del CDN, la arquitectura Cloud Native entró en funcionamiento global de forma óptima a coste casi nulo.
