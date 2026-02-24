# Base de datos

## Descripción general

La base de datos permite guardar y organizar toda la información que necesita la plataforma para su correcto funcionamiento. Su objetivo principal es mantener y relacionar la información de usuarios y productos principalmente. Que son el corazón de la aplicación. Permite guardar chats y mensajes entre usuarios.

- Motor: Mysql
- Versión: 8

## Diagrama entidad - relación con atributos

```mermaid
---
config:
  layout: elk
  theme: neutral
---
erDiagram
 direction LR
 USUARIOS {
  int id PK ""  
  string nombre_usuario  "NN"  
  string email  "NN, U"  
  string contrasenya  "NN"  
  string telefono  "NN, U"  
  string direccion  ""  
  decimal longitud  ""  
  decimal latitud  ""  
  timestamp created_at  "NN"  
  timestamp updated_at  ""  
 }

 PUNTOS_ENTREGA {
  int id PK ""  
  int id_usuario FK ""  
  decimal longitud  "NN"  
  decimal latitud  "NN"  
  string nombre_punto  "NN"  
  string direccion_punto  ""  
  timestamp created_at  "NN"  
  timestamp updated_at  ""  
 }

 PRODUCTOS {
  int id PK ""  
  int id_categoria FK ""  
  int id_usuario FK ""  
  int id_puntoentrega FK ""  
  string nombre_producto  "NN"  
  text descripcion  ""  
  decimal precio  "NN"  
  int stock_total  "NN"  
  int stock_reserva  "NN"  
  int stock_real  "VIRTUAL"  
  string imagen  ""  
  enum estado  "default 'disponible'"  
  timestamp created_at  "NN"  
  timestamp updated_at  ""  
 }

 CHATS {
  int id PK ""  
  int id_comprador FK "NN, U1"  
  int id_vendedor FK "NN, U1"  
  int id_producto FK "NN, U1"  
  timestamp created_at  "NN"  
  timestamp updated_at  ""  
 }

 MENSAJES {
  int id PK ""  
  int id_chat FK "NN"  
  int id_envio FK "NN"  
  text contenido  "NN"  
  boolean leido "default false"
  timestamp created_at  "NN"  
  timestamp updated_at  ""  
 }

 COMPRAVENTAS {
  int id PK ""  
  int id_producto FK "NN"  
  int id_comprador FK "NN"  
  int id_vendedor FK "NN"  
  int id_punto FK ""  
  int cantidad  "NN"  
  decimal precio  "NN"  
  decimal precio_total  "VIRTUAL"  
  date fecha_prevista  ""  
  enum estado  "default 'pendiente'"  
  timestamp created_at  "NN"  
  timestamp updated_at  ""  
 }

 VALORACIONES {
  int id PK ""  
  int id_venta FK "NN, U1"  
  int id_valorador FK "NN, U1"  
  int id_valorado FK "NN, U1"  
  tinyint valoracion  "NN"  
  text comentario  ""  
  timestamp created_at  "NN"  
  timestamp updated_at  ""  
 }

 CATEGORIAS {
  int id PK ""  
  string nombre_categoria  "NN"  
  timestamp created_at  "NN"  
  timestamp updated_at  ""  
 }

 USUARIOS||--o{PUNTOS_ENTREGA:"registra"
 USUARIOS||--o{PRODUCTOS:"vende"
 USUARIOS||--o{CHATS:"participa"
 USUARIOS||--o{MENSAJES:"envía"
 USUARIOS||--o{COMPRAVENTAS:"participa"
 USUARIOS||--o{VALORACIONES:"evalúa"
 CATEGORIAS||--o{PRODUCTOS:"clasifica"
 PUNTOS_ENTREGA||--o{PRODUCTOS:"entrega_en"
 PUNTOS_ENTREGA||--o{COMPRAVENTAS:"punto_encuentro"
 PRODUCTOS||--o{CHATS:"referencia"
 PRODUCTOS||--o{COMPRAVENTAS:"se_vende"
 CHATS||--o{MENSAJES:"contiene"
 COMPRAVENTAS||--o{VALORACIONES:"calificada_por"
```

### Relaciones

#### Chats - mensajes

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[CHATS] <-- (1:M) --> id2[MENSAJES]
```

Cada mensaje pertenece a un único chat; un chat puede contener varios mensajes.

#### Mensajes - usuarios

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[MENSAJES] <-- (1:M) --> id2[USUARIOS]
```

Cada mensaje es enviado por un único usuario; un usuario puede enviar muchos mensajes.

#### Usuarios - puntos_entrega

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[USUARIOS] <-- (1:M) --> id2[PUNTOS_ENTREGA]
```

Cada punto de entrega es asignado por un usuario; un usuario puede asignarse varios puntos de entrega.

#### Usuarios - productos

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[USUARIOS] <-- (1:M) --> id2[PRODUCTOS]
```

Un usuario puede publicar varios productos; cada producto pertenece a un único usuario.

#### Chats - productos

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[CHATS] <-- (1:M) --> id2[PRODUCTOS]
```

Cada chat está asociado a un único producto; un producto puede tener varios chats simuláneos (mismo vendedor, diferentes compradores).

#### Categorías - productos

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[CATEGORIAS] <-- (1:M) --> id2[PRODUCTOS]
```

Cada producto pertenece a una categoría; una categoría puede tener muchos productos.

En un futuro se espera implementar que un producto pertenezca a varias categorías. E incluso que una categoría pueda tener subcategorías.

#### Productos - compraventas

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[PRODUCTOS] <-- (1:M) --> id2[COMPRAVENTAS]
```

Cada producto puede generar varias compraventas; cada compraventa corresponde a un único producto.

#### Compraventas - reseñas

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[COMPRAVENTAS] <-- (1:M) --> id2[RESEÑAS]
```

Una compraventa puede generar un máximo de 2 reseñas (comprador y vendedor); cada reseña pertenece a una única compraventa.

#### Puntos_entrega - compraventas

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[PUNTOS_ENTREGA] <-- (1:M) --> id2[COMPRAVENTAS]
```

Una compraventa puede tener lugar en un único punto de entrega. En un punto de entrega ocurren muchas operaciones de compraventa.

#### Usuarios - compraventas

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[USUARIOS] <-- (1:M) --> id2[COMPRAVENTAS]
    id1[USUARIOS] <-- (1:M) --> id2[COMPRAVENTAS]
```

Un único usuario con rol de comprador participa en la compraventa.
Un único usuario con rol de vendedor participa en la compraventa.
Cada usuario puede realizar muchas compraventas.

#### Usuarios - reseñas

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[USUARIOS] <-- (1:M) --> id2[RESEÑAS]
    id1[USUARIOS] <-- (1:M) --> id2[RESEÑAS]
```

En cada reseña participa un único usuario reseñador.
En cada reseña participa un único usuario reseñado.
Un usuario puede tener muchas reseñas.

#### Usuarios - chats

```mermaid
---
config:
    theme: neutral
---

flowchart LR
    id1[USUARIOS] <-- (1:M) --> id2[CHATS]
    id1[USUARIOS] <-- (1:M) --> id2[CHATS]
```

Un chat tiene dos únicos participantes (comprador - vendedor).
Los usuarios pueden tener muchos chats.

### Tablas y campos

#### **Tabla usuarios**

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    contrasenya VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    direccion VARCHAR(255),
    longitud DECIMAL(12, 8),
    latitud DECIMAL(12, 8),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

```

#### **Tabla categorias**

```sql
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **Tabla puntos de entrega**

```sql
CREATE TABLE puntos_entrega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    longitud DECIMAL(12, 8) NOT NULL,
    latitud DECIMAL(12, 8) NOT NULL,
    nombre_punto VARCHAR(255) NOT NULL,
    direccion_punto VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
);
```

#### **Tabla productos**

```sql
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT,
    id_usuario INT,
    id_puntoentrega INT,
    nombre_producto VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock_total INT NOT NULL DEFAULT 0,
    stock_reserva INT NOT NULL DEFAULT 0,
    stock_real INT AS (stock_total - stock_reserva) STORED,
    imagen VARCHAR(255),
    estado ENUM('agotado', 'disponible') DEFAULT 'disponible',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias (id),
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id),
    FOREIGN KEY (id_puntoentrega) REFERENCES puntos_entrega (id)
);
```

#### **Tabla chats**

```sql
CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_comprador INT,
    id_vendedor INT,
    id_producto INT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_comprador) REFERENCES usuarios (id),
    FOREIGN KEY (id_vendedor) REFERENCES usuarios (id),
    FOREIGN KEY (id_producto) REFERENCES productos (id),
    UNIQUE (
        id_comprador,
        id_vendedor,
        id_producto
    )
);
```

#### **Tabla mensajes**

```sql
CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_chat INT,
    id_envio INT,
    contenido TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_chat) REFERENCES chats (id),
    FOREIGN KEY (id_envio) REFERENCES usuarios (id)
);
```

#### **Tabla compraventas**

```sql
CREATE TABLE compraventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT,
    id_comprador INT,
    id_vendedor INT,
    id_punto INT,
    cantidad INT NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    precio_total DECIMAL(10, 2) AS (precio * cantidad) STORED,
    fecha_prevista DATE,
    estado ENUM(
        'pendiente',
        'en curso',
        'completado',
        'cancelado',
        'valorado'
    ) DEFAULT 'pendiente',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos (id),
    FOREIGN KEY (id_comprador) REFERENCES usuarios (id),
    FOREIGN KEY (id_vendedor) REFERENCES usuarios (id),
    FOREIGN KEY (id_punto) REFERENCES puntos_entrega (id)
);
```

#### **Tabla valoraciones**

```sql
CREATE TABLE valoraciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT,
    id_valorador INT,
    id_valorado INT,
    valoracion TINYINT NOT NULL,
    comentario TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venta) REFERENCES compraventas (id),
    FOREIGN KEY (id_valorador) REFERENCES usuarios (id),
    FOREIGN KEY (id_valorado) REFERENCES usuarios (id),
    UNIQUE (id_venta, id_valorador)
);
```
