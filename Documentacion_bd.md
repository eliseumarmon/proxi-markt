## Documentación BBDD

### Tablas

**Tabla usuarios**

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

**Tabla categorias**

- id_categoria int AUTO_INCREMENT PRIMARY KEY,
- nombre_categoria varchar(255)

**Tabla productos**

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

**Tabla chat**

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

**Tabla mensajes**

- id_mensaje int AUTO_INCREMENT PRIMARY KEY,
- id_chat int,
- id_envio int,
- contenido TEXT,
- FOREIGN KEY (id_chat) REFERENCES CHAT(id_chat),
- FOREIGN KEY (id_envio) REFERENCES USUARIOS(id_usuario)

**Tabla puntos entrega**

- id_usuario int,
- longitud DECIMAL(10,8),
- latitud DECIMAL(10,8),
- nombre_punto VARCHAR(255),
- direccion_punto VARCHAR(255),
- created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
- modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON 
- UPDATE CURRENT_TIMESTAMP,
- FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario)

**Tabla compraventas**

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

**Tabla valoraciones**

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
