# Base de Dades: ProxiMarkt 🛒🍅

## Descripció general

La base de dades de ProxiMarkt servix per a guardar i organitzar tota la informació que necessita la plataforma per a funcionar bé. El seu objectiu és fer possible la comunicació entre compradors i venedors i gestionar coses com els productes, les reserves o els punts de lliurament.

- Motor: Mysql
- Versió: 8

## Diagrama Entitat - Relació amb atributs

![Diagrama Entitat Relació amb atributs](img/diagrama.jpeg)

### Relacions

**Productos <---> Usuarios (1:N)**

    Un Usuario puede realizar uno o varios Pedidos, y cada Pedido pertenece a un solo Usuario.

**Mensajes <---> Chats (N:1)**

    Cada mensaje pertenece a un único chat; un chat puede contener varios mensajes.

**Mensajes <---> usuarios (N:1)**

    Cada mensaje es enviado por un único usuario; un usuario puede enviar muchos mensajes.

**Puntos_entrega <---> Usuarios (N:1)**

    Cada punto de entrega pertenece a un usuario; un usuario puede tener varios puntos de entrega.

**Usuarios <---> Productos (1:N)**

    Un usuario puede publicar varios productos; cada producto pertenece a un único usuario.

**Chats <---> Productos (N:1)**

    Cada chat está asociado a un producto; un producto puede tener varios chats.

**Productos <---> Categorias (N:1)**

    Cada producto pertenece a una categoría; una categoría puede tener muchos productos.

**Prodcutos <---> Compraventas (1:N)**

    Un producto puede estar en varias compraventas; cada compraventa corresponde a un producto.

**Compraventas <---> Reseñas (1:N)**

    Una compraventa puede generar varias reseñas; cada reseña pertenece a una compraventa.

**Puntos_entrega <---> Compraventas (1:N)**

    Un punto de entrega puede usarse en varias compraventas; cada compraventa usa un único punto de entrega.

**Usuarios <---> Compraventas (Comprar)  (1:N)**

    Un usuario puede realizar varias compras; cada compraventa tiene un comprador

**Usuarios <---> Compraventas (Vender)  (1:N)**

    Un usuario puede realizar varias ventas; cada compraventa tiene un vendedor.

**Usuarios <---> Reseñas (Obtener) (1:N)**

    Un usuario puede recibir varias reseñas; cada reseña valora a un usuario.

**Usuarios <---> Reseñas (Dejar) (1:N)**

    Un usuario puede dejar varias reseñas; cada reseña es creada por un usuario.

**Usuarios <---> Chats (Escribir) (1:N)**

    Un usuario puede escribir en varios chats; cada chat tiene mensajes escritos por usuarios.

**Usuarios <---> Chats (Recibir) (1:N)**

    Un usuario puede recibir varios chats; cada chat está dirigido a un usuario.

### Atributs

#### **Tabla usuarios**

- id_usuario int AUTO_INCREMENT PRIMARY KEY,
- nombre_usuario VARCHAR(255) UNIQUE,
- email varchar(255) UNIQUE,
- contraseña varchar(255),
- telefono VARCHAR(20) UNIQUE,
- direccion varchar(255) UNIQUE,
- longitud DECIMAL(10,8),
- latitud DECIMAL(10,8),
- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
- modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
- UPDATE CURRENT_TIMESTAMP,
- puntuacio DOUBLE DEFAULT 0

#### **Tabla categorias**

- id_categoria int AUTO_INCREMENT PRIMARY KEY,
- nombre_categoria varchar(255)

#### **Tabla productos**

- id_producto int AUTO_INCREMENT PRIMARY KEY,
- nombre_producto VARCHAR(255),
- descripcion TEXT,
- precio DECIMAL(10,2),
- stock_total int,
- stock_reserva int,
- stock_real int,
- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
- imagen VARCHAR(255),
- id_categoria int,
- estado ENUM('agotado', 'reservado', 'disponible'),
- FOREIGN KEY (id_categoria) REFERENCES CATEGORIAS(id_categoria)

#### **Tabla chat**

- id_chat int AUTO_INCREMENT PRIMARY KEY,
- id_comprador int,
- id_vendedor int,
- id_producto int,
- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
- modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
- UPDATE CURRENT_TIMESTAMP,
- FOREIGN KEY (id_comprador) REFERENCES USUARIOS(id_usuario),
- FOREIGN KEY (id_vendedor) REFERENCES USUARIOS(id_usuario),
- FOREIGN KEY (id_producto) REFERENCES PRODUCTOS(id_producto),
- UNIQUE (id_comprador, id_vendedor, id_producto)

#### **Tabla mensajes**

- id_mensaje int AUTO_INCREMENT PRIMARY KEY,
- id_chat int,
- id_envio int,
- contenido TEXT,
- FOREIGN KEY (id_chat) REFERENCES CHAT(id_chat),
- FOREIGN KEY (id_envio) REFERENCES USUARIOS(id_usuario)

#### **Tabla puntos entrega**

- id_usuario int,
- longitud DECIMAL(10,8),
- latitud DECIMAL(10,8),
- nombre_punto VARCHAR(255),
- direccion_punto VARCHAR(255),
- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
- modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
- UPDATE CURRENT_TIMESTAMP,
- FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario)

#### **Tabla compraventas**

- id_compraventa int AUTO_INCREMENT PRIMARY KEY,
- id_producto int,
- id_comprador int,
- id_vendedor int,
- cantidad_total int,
- id_punto int,
- estado ENUM('pendiente', 'en curso', 'completado', 'cancelado'),
- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
- FOREIGN KEY (id_producto) REFERENCES PRODUCTOS(id_producto),
- FOREIGN KEY (id_vendedor) REFERENCES USUARIOS(id_usuario),
- FOREIGN KEY (id_comprador) REFERENCES USUARIOS(id_usuario),
- FOREIGN KEY (id_punto) REFERENCES PUNTOS_ENTREGA(id_punto)

---
#### **Tabla valoraciones**

- id_valoracion int AUTO_INCREMENT PRIMARY KEY,
- id_venta INT,
- id_reseñador int,
- id_reseñado int,
- valoracion ENUM('1','2','3','4','5'),
- comentario TEXT,
- fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
- FOREIGN KEY (id_venta) REFERENCES COMPRAVENTAS(id_compraventa),
- FOREIGN KEY (id_reseñador) REFERENCES USUARIOS(id_usuario),
- FOREIGN KEY (id_reseñado) REFERENCES USUARIOS(id_usuario),
- UNIQUE (id_venta, id_reseñador, id_reseñado)
