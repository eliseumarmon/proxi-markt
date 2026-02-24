# Iniciar proyecto

## Backend

1. Asegurar que el directorio esté así:

    ```text
    .
    ├── backend
    ├── docker
    │   ├── database
    │   │   ├── base.sql
    │   │   └── .env.database.example
    │   ├── .env.app.example
    │   ├── nginx
    │   │   └── default.conf
    │   └── php
    │       ├── conf.d
    │       │    └── 99-xdebug.ini
    │       └── Dockerfile
    └── docker-compose.yaml
    ```

2. Renombrar y rellenar ***.env.database.example*** y ***.env.app.example***

3. Desde la carpeta raíz ejecutar `docker compose up --build`

4. Entrar al contenedor de php con: `docker compose exec -it php bash`

5. Iniciar un nuevo proyecto de Laravel con: `composer create-project laravel/laravel .`

6. Instalar las rutas api: `php artisan install:api` incluye librería **sanctum**.

7. Modificar ***backend/.env*** para conectarse a la base de datos.

8. Crear todas las tablas necesarias para el funcionamiento de Laravel: `php artisan migrate`

## Frontend

1. Comprobar si node está instalado: `node -v`
    1. Si no está instalado:

        ```bash
        # Descarga e instala nvm:
        curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
        # En lugar de reiniciar la shell
        \. "$HOME/.nvm/nvm.sh"
        # Descarga e instala Node.js:
        nvm install 24
        # Verifica la versión de Node.js:
        node -v # Debería mostrar "v24.13.0".
        # Verifica versión de npm:
        npm -v # Debería mostrar "11.6.2".
        ```

2. Iniciar el proyecto Vue:
    1. Desde la raíz del proyecto: `npm create vue@latest`
    2. El nombre del proyecto es ***frontend***
    3. Indicar que se utiliza ***Router (SPA)***
    4. Entrar en la carpeta frontend: `cd frontend`
    5. Instalar dependencias de Vue: `npm install`

3. Instalar dependencias adicionales:
    1. Axios: `npm install axios`
    2. Leaflet: `npm install leaflet`

4. Iniciar servidor para frontend (desarrollo):
    1. Ejecutar el comando: `npm run dev`

## Documentación

1. Instalar libreria ***venv*** para generar entorno virtual de python: `sudo apt install python3-venv`

2. Crear el entorno virtual: `python3 -m venv .venv`

3. Usar el entorno virtual: `source .venv/bin/activate`

4. Instalar MkDocs: `pip install mkdocs-material`

5. Instalar plugin mermaid para MkDocs: `pip install mkdocs-mermaid2-plugin`

6. Configurar `mkdocs.yml` añadiendo las extensiones necesarias:

    ```yaml
    site_name: Proxi Markt
    theme:
      name: material
    markdown_extensions: 
      - pymdownx.superfences:
          custom_fences:
            - name: mermaid
              class: mermaid
              format: !!python/name:pymdownx.superfences.fence_div_format
    plugins:
      - search
      - mermaid2
    extra:
      mermaid:
        theme: 'neutral'
    ```

7. Desplegar archivos de la carpeta ***docs/*** con: `mkdocs gh-deploy`
