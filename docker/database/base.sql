USE proxi_markt;

CREATE TABLE USUARIOS(
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    contrasenya VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) UNIQUE NOT NULL, 
    direccion VARCHAR(255),
    longitud DECIMAL(10,8),
    latitud DECIMAL(10,8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    puntuacio DOUBLE DEFAULT 0
);

CREATE TABLE CATEGORIAS(
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(255) NOT NULL 
);

CREATE TABLE PRODUCTOS(
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    nombre_producto VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock_total INT NOT NULL DEFAULT 0,
    stock_reserva INT NOT NULL DEFAULT 0,
    stock_real INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    imagen VARCHAR(255),
    id_categoria INT,
    estado ENUM('agotado', 'reservado', 'disponible'), 
    FOREIGN KEY (id_categoria) REFERENCES CATEGORIAS(id_categoria)
);

CREATE TABLE CHATS(
    id_chat INT AUTO_INCREMENT PRIMARY KEY,
    id_comprador INT,
    id_vendedor INT,
    id_producto INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_comprador) REFERENCES USUARIOS(id_usuario),
    FOREIGN KEY (id_vendedor) REFERENCES USUARIOS(id_usuario),
    FOREIGN KEY (id_producto) REFERENCES PRODUCTOS(id_producto),
    UNIQUE (id_comprador, id_vendedor, id_producto)
);

CREATE TABLE MENSAJES(
    id_mensaje INT AUTO_INCREMENT PRIMARY KEY,
    id_chat INT,
    id_envio INT,
    contenido TEXT NOT NULL,
    FOREIGN KEY (id_chat) REFERENCES CHAT(id_chat),
    FOREIGN KEY (id_envio) REFERENCES USUARIOS(id_usuario)
);

CREATE TABLE PUNTOS_ENTREGA(
    id_punto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    longitud DECIMAL(10,8) NOT NULL,
    latitud DECIMAL(10,8) NOT NULL,
    nombre_punto VARCHAR(255) NOT NULL,
    direccion_punto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES USUARIOS(id_usuario)
);

CREATE TABLE COMPRAVENTAS(
    id_compraventa INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT,
    id_comprador INT,
    id_vendedor INT,
    cantidad_total INT NOT NULL,
    id_punto INT,
    estado ENUM('pendiente', 'en curso', 'completado', 'cancelado'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES PRODUCTOS(id_producto),
    FOREIGN KEY (id_vendedor) REFERENCES USUARIOS(id_usuario),
    FOREIGN KEY (id_comprador) REFERENCES USUARIOS(id_usuario),
    FOREIGN KEY (id_punto) REFERENCES PUNTOS_ENTREGA(id_punto)
);

CREATE TABLE VALORACIONES(
    id_valoracion INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT,
    id_resenyador INT,
    id_resenyado INT,
    valoracion ENUM('1','2','3','4','5') NOT NULL,
    comentario TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (id_venta) REFERENCES COMPRAVENTAS(id_compraventa),
    FOREIGN KEY (id_resenyador) REFERENCES USUARIOS(id_usuario),
    FOREIGN KEY (id_resenyado) REFERENCES USUARIOS(id_usuario),
    UNIQUE (id_venta, id_resenyador, id_resenyado)
);