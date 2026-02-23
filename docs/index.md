# Documentación de Proxi Markt

**Proxi Markt** es una plataforma web desarrollada para conectar directamente a productores locales (sector primario) con los consumidores, fomentando el comercio de proximidad y eliminando plataformas intermediarias.

Esta documentación técnica está dirigida a desarrolladores que deseen contribuir, comprender o desplegar el proyecto. Reúne toda la información necesaria sobre la arquitectura, la base de datos, la API backend y el cliente frontend de la aplicación.

## Características principales

* **Compraventa P2P:** Flujo de transacciones directas gestionadas mediante estados (Pendiente, En curso, Cancelado, Completado).
* **Chat en Tiempo Real:** Sistema de mensajería asíncrona integrado para la negociación directa sobre los artículos en venta.
* **Valoraciones Bidireccionales:** Sistema de reseñas (1 a 5 estrellas) entre usuarios una vez que la compraventa se ha completado.
* **Historial de Pedidos:** Registro persistente de compras, ventas y estados.
* **Geoposicionamiento:** Integración de mapas para descubrir puntos de entrega e instalaciones de productores cercanos calculados mediante distancia real.

---

## Mapa de documentación

Te recomendamos seguir el siguiente orden de lectura para comprender el proyecto de forma progresiva:

1. **[Arquitectura del Proyecto](arquitectura.md):** Visión general del ecosistema. Flujo entre el cliente Vue, la API Laravel y el esquema de despliegue sobre Amazon Web Services (AWS).
2. **[Modelo de Datos (Entidad-Relación)](documentacio_bd.md):** Esquema de la base de datos relacional MySQL y diagrama de todas las entidades involucradas.
3. **[Backend - API y Lógica (Laravel)](documentacion_backend.md):** Detalle de la arquitectura REST Stateless, rutas protegidas por Sanctum, validaciones cruzadas y explicación de flujos complejos (Reserva de stock, Haversine, etc).
4. **[Frontend - Interfaz SPA (Vue.js)](documentacion_componentes.md):** Desglose del ciclo de los componentes reactivos, gestión del estado de sesión (`useAuth`), interceptores de Axios y diagramas de flujos.
5. **[Infraestructura Cloud (AWS Free Tier)](aws_free_tier/Despliegue%20de%20ProxiMarkt%20en%20AWS.md):** Manuales paso a paso y documentos *Post-Mortem* con las decisiones y resoluciones de problemas implementando el despliegue del proyecto mediante S3, CloudFront, EC2 y RDS.
6. **[Guía de Instalación Local](documentacion_iniciar_proyecto.md):** Instrucciones paso a paso para desplegar e iniciar el proyecto localmente utilizando Docker Compose y Vite para desarrollo.

## Inicio rápido (desarrollo local)

Si ya estás familiarizado con la estructura global y solo deseas levantar el entorno de desarrollo local, sigue estos comandos:

1. Clona el repositorio e inicia los contenedores de Docker (Base de datos y Backend):

    ```bash
    docker compose up --build -d
    ```

2. Accede a la instancia de PHP para instalar dependencias y configurar la base de datos:

    ```bash
    docker compose exec -it php bash
    composer install
    php artisan migrate
    php artisan storage:link
    # Opcional (Poblar la base de datos con información de prueba): php artisan db:seed
    ```

3. En una nueva terminal, abre la carpeta `frontend/`, instala los paquetes de Node e inicia el servidor de desarrollo Vite:

    ```bash
    cd frontend
    npm install
    npm run dev
    ```
