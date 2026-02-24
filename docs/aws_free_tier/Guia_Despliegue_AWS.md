# Guía paso a paso para desplegar ProxiMarkt en AWS free tier

Esta guía está diseñada para que tú mismo configures todos los servicios en la consola de AWS sin gastar dinero usando el **Free Tier**.

## 1. Crear la base de datos (amazon RDS)

La base de datos debe ser lo primero, ya que el backend la necesitará para conectarse.

1. Ve a la consola de AWS y busca **RDS**.
2. Haz clic en **Crear base de datos**.
3. Selecciona **Creación estándar**.
4. Motor: **MySQL** o **MariaDB** (según uses).
5. Plantilla: **Capa gratuita (Free Tier)**. Esto es vital.
6. Configuraciones:
   - Identificador de la base de datos: `proximarkt-db`
   - Nombre de usuario principal: `root` o el que prefieras.
   - Contraseña maestra: Escribe una contraseña segura (la necesitarás en tu `.env`).
7. Configuración de instancia: Debería estar marcado automáticamente `db.t3.micro` o `db.t2.micro` (Apto para capa gratuita).
8. Almacenamiento: **20 GB** (lo máximo gratis). ¡Desactiva el escalado automático de almacenamiento si quieres fijarlo!
9. Conectividad:
   - Acceso público: **No** (Por seguridad, solo la EC2 se conectará a ella).
   - Crea un nuevo grupo de seguridad VPC llamado `rds-sg-proximarkt`.
10. Autenticación de base de datos: Autenticación con contraseña.
11. **Crear base de datos**. *Tardará unos minutos en aprovisionarse. Cópia el "Punto de enlace" (Endpoint) que aparece cuando termine.*

---

## 2. Crear servidor Backend (amazon ec2)

Aquí ejecutaremos tu proyecto Laravel.

1. Ve a **EC2** y haz clic en **Lanzar instancia**.
2. Nombre: `ProxiMarkt-Backend`
3. AMI (Sistema Operativo): **Ubuntu Server 24.04 LTS** (o 22.04) que diga *Apto para la capa gratuita*.
4. Tipo de instancia: **t2.micro** o **t3.micro** (Apto para la capa gratuita).
5. Par de claves (Inicio de sesión): Haz clic en **Crear nuevo par de claves**.
   - Nombre: `proximarkt-key`
   - Tipo: RSA
   - Formato: `.pem` (para Mac/Linux o PowerShell) o `.ppk` (si usas PuTTY). Descarga y guarda este archivo.
6. Configuraciones de red:
   - Permitir tráfico SSH desde: **Mi IP**.
   - Permitir tráfico HTTP de Internet.
   - Permitir tráfico HTTPS de Internet.
7. Almacenamiento: 20 GB (gp3). (Tienes hasta 30GB gratis combinados).
8. Haz clic en **Lanzar instancia**.

### 2.1 Conectar ec2 y RDS

1. Ve a **Grupos de Seguridad (Security Groups)** en la consola EC2.
2. Selecciona tu grupo RDS (`rds-sg-proximarkt`) y dale a Editar reglas de entrada.
3. Añade una regla: Tipo **MySQL/Aurora (3306)**, Origen: **Personalizado**, y en el buscador selecciona el ID del **Grupo de Seguridad de tu EC2**. Esto permitirá a tu EC2 conectarse a tu base de datos de forma segura.

### 2.2 Preparar el código en ec2 (vía User data)

¡Excelente idea! Para automatizar todo usando el script `setup_ec2.sh` como **User Data** durante la creación de la instancia:

1. Al crear la instancia (Paso 2), ve a **Detalles avanzados (Advanced details)** en la parte inferior.
2. Baja hasta la sección **Datos de usuario (User data)**.
3. Copia todo el contenido del archivo `setup_ec2.sh` y pégalo ahí.
4. Lanza la instancia. AWS ejecutará este script automáticamente como `root` en el primer arranque, instalando Apache, PHP y Composer por ti.

### 2.3 Configurar Laravel

1. Conéctate a tu máquina por SSH: `ssh -i "proximarkt-key.pem" ubuntu@TU_IP_PÚBLICA`
2. Ve al directorio que el script creó: `cd /var/www/proxi-markt`
3. Clona tu repositorio, copia el `.env.example` a `.env` y sustituye estas variables:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=El_endpoint_de_tu_RDS
   DB_PORT=3306
   DB_DATABASE=proximarkt
   DB_USERNAME=root
   DB_PASSWORD=tu_contraseña_secreta
   ```

4. Copia el contenido de `apache-vhost.conf` en `/etc/apache2/sites-available/proximarkt.conf`, activa el sitio (`sudo a2ensite proximarkt.conf`) y reinicia apache (`sudo systemctl restart apache2`).

---

## 3. Desplegar el Frontend (amazon s3 + CloudFront)

1. En la consola, ve a **S3** y haz clic en **Crear bucket**.
2. Nombre: `proximarkt-frontend-bucket` (debe ser un nombre único globalmente).
3. Región: la misma que tu EC2/RDS.
4. **Desbloquear todo el acceso público**: Déjalo BLOQUEADO (Usaremos CloudFront para asegurar la lectura segura).
5. Crea el Bucket.

### 3.1 Construir y subir tu Frontend

En tu máquina local:

1. Asegúrate de que las URLs de la API en tu frontend apunten a la IP pública o dominio de tu instancia EC2. Por ejemplo, en tu `.env.production` (o fichero de Config/Axios en Vue):

   ```env
   VITE_API_URL=http://<EC2-IP-PUBLICA>/api
   ```

2. Ejecuta:

   ```bash
   cd frontend
   npm run build
   ```

3. Sube el contenido de la carpeta `/dist/` alucket S3 copiando y pegando desde la consola, o mediante AWS CLI:
   `aws s3 sync dist/ s3://proximarkt-frontend-bucket`

### 3.2 Crear la red de distribución (CloudFront)

1. Ve a **CloudFront** y dale a **Crear distribución**.
2. **Origen:** Selecciona tu bucket S3 (`proximarkt-frontend-bucket.s3.eu-west-...`).
3. Aparecerá una opción para OAC (Origin access control). Selecciónala y dale a crear un nuevo control. Esto te dará una política JSON.
4. Política de visor (Viewer): **Redirect HTTP to HTTPS**.
5. WAF (Firewall): **Do not enable security protections** (para no pagar extra).
6. Haz clic en **Crear distribución**.
7. Arriba amarillo aparecerá un aviso: *"La política de bucket de S3 debe actualizarse..."*, dale a "Copiar política" y haz clic en el enlace para ir a S3 a pegarla en los permisos de tu bucket.
8. **MUY IMPORTANTE (Para que Vue Router funcione):**
   - Ve a la pestaña de **Páginas de Error (Error pages)** de tu Distribución de CloudFront recién creada.
   - Crea una respuesta de error personalizada.
   - Código de error HTTP: `404 Not Found`.
   - Respuesta HTTP: `200 OK`.
   - Ruta de la respuesta: `/index.html`.
   - (Repite esto mismo creando otra para el código `403 Forbidden`).

---

**¡Listo!** El dominio gratuito que te da CloudFront (`d.......cloudfront.net`) cargará tu aplicación Frontend Vue, el cual se comunicará con tu instancia EC2 Laravel en la nube.
