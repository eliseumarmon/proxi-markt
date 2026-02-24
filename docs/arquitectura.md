# Arquitectura del proyecto

 Este documento detalla la arquitectura de alto nivel de Proxi Markt, incluyendo los distintos componentes de software, la forma en que se comunican, y la infraestructura que los soporta.

## 1. Visión general

 El proyecto **Proxi Markt** está diseñado siguiendo una arquitectura cliente-servidor de tipo **Single Page Application (SPA)**, completamente desacoplada de su API y basada en los principios REST.

 El código está dividido en dos grandes bloques (Monorepo):

 1. **Frontend:** Desarrollado con Vue 3 y Vite.
 2. **Backend:** Desarrollado con Laravel 11 y PHP 8.3.
 3. **Base de Datos:** MySQL 8.

 ---

## 2. Diagrama lógico de arquitectura (software)

 El siguiente esquema representa cómo interactifica el usuario final con las capas lógicas del sistema:

 ```mermaid
 sequenceDiagram
     autonumber
     actor Usuario
     participant Navegador (Vue SPA)
     participant Servidor Web (Axios / Axios Interceptors)
     participant API REST (Laravel Sanctum)
     participant Base de Datos (MySQL)
 
     Usuario->>Navegador (Vue SPA): Visita la web / Accede
     Navegador (Vue SPA)->>Servidor Web (Axios / Axios Interceptors): Solicita login
     Servidor Web (Axios / Axios Interceptors)->>API REST (Laravel Sanctum): POST /api/login
     API REST (Laravel Sanctum)->>Base de Datos (MySQL): Verifica Credenciales
     Base de Datos (MySQL)-->>API REST (Laravel Sanctum): OK
     API REST (Laravel Sanctum)-->>Servidor Web (Axios / Axios Interceptors): Retorna Token Bearer
     Servidor Web (Axios / Axios Interceptors)-->>Navegador (Vue SPA): Guarda en Local Storage y actualiza UI global
     
     Usuario->>Navegador (Vue SPA): Solicita ver puntos cercanos (Mapa)
     Navegador (Vue SPA)->>Servidor Web (Axios / Axios Interceptors): Inyecta Bearer automáticamente
     Servidor Web (Axios / Axios Interceptors)->>API REST (Laravel Sanctum): GET /api/puntos_radio/5
     API REST (Laravel Sanctum)->>Base de Datos (MySQL): Consulta GIS/Coordenadas
     Base de Datos (MySQL)-->>API REST (Laravel Sanctum): Retorna Puntos
     API REST (Laravel Sanctum)-->>Navegador (Vue SPA): JSON con puntos
 ```

 ---

## 3. Diagrama de infraestructura cloud native (AWS)

 En entornos de producción (Producción AWS Free Tier), el sistema abandona el tradicional monolito para apostar por un despliegue de microservicios segregados usando AWS:

 ```mermaid
 graph TD
     %% Estilos
     classDef user fill:#f9f,stroke:#333,stroke-width:4px;
     classDef cloudfront fill:#e1aaba,stroke:#bc4b51,stroke-width:2px;
     classDef s3 fill:#f4bc94,stroke:#e76f51,stroke-width:2px;
     classDef ec2 fill:#c1e2b3,stroke:#2a9d8f,stroke-width:2px;
     classDef rds fill:#b5d8f6,stroke:#457b9d,stroke-width:2px;
     classDef internet fill:#eee,stroke:#999,stroke-width:1px,stroke-dasharray: 5, 5;
 
     Usuario((👩‍🌾 Usuario Final)):::user
 
     subgraph AWS_Cloud [Nube AWS - Entorno Seguro]
         
         CF[🌍 Amazon CloudFront<br/>CDN & Proxy Inverso + SSL]:::cloudfront
         S3[(🪣 Amazon S3<br/>ProxiMarkt Frontend)]:::s3
         
         subgraph VPC [Amazon VPC Privada]
             EC2[💻 Amazon EC2 / Ubuntu<br/>Laravel API REST + Storage]:::ec2
             RDS[(🗄️ Amazon RDS<br/>Base de Datos MySQL)]:::rds
         end
         
         %% Conexiones
         Usuario -- "HTTPS: Visitando web" --> CF
         Usuario -- "HTTPS: Descarga Fotos y Peticiones API" --> CF
         
         CF -- "Reglas (/*) (OAC Privado)" --> S3
         CF -- "Reglas (/api/*, /storage/*)" --> EC2
         
         EC2 -- "Puerto 3306 Interno" --> RDS
         
     end
 
     class AWS_Cloud internet;
 ```

### Explicación del flujo de infraestructura

 1. **Amazon S3 (Almacenamiento Estático):** Custodia los archivos HTML, CSS y JS ya compilados por Vite. Está configurado de forma hermética (`Block public access`) y solo autoriza las subcargas desde herramientas del desarrollador, y las descargas a CloudFront.
 2. **Amazon CloudFront (Edge Network):** Es el corazón del despliegue. Todos los usuarios entran mediante un certificado SSL autogestionado en el "borde" de red. CloudFront hace dos cosas vitales:
     - Entrega la SPA estática instantáneamente cacheadola en servidores de todo el mundo.
     - Actúa como un **Reverse Proxy**: Todas las peticiones que empiecen por `/api` o `/storage` son interceptadas y transferidas con los Host Headers correctos al servidor de EC2 mediante HTTP asíncrono. Esto elude los odiados errores *Mixed Content* del navegador.
 3. **Amazon EC2 (Cómputo Backend):** Ejecuta Ubuntu 24 con Apache + PHP 8.3. Solo expone puertos estándar y procesa el enrutamiento API de Laravel. Las fotos enviadas (`FormData`) se guardan aquí bajo disco (Stateful, temporal).
 4. **Amazon RDS (Persistencia de Datos):** Servidor maestro de Bases de Datos `db.t3.micro`. Carece de IP pública, conectado internamente mediante Security Groups únicamente a la máquina EC2 que lo invoque.
