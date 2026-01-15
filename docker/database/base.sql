USE proxi_markt;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    contrasenya VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    direccion VARCHAR(255),
    longitud DECIMAL(10, 8),
    latitud DECIMAL(10, 8),
    puntuacio DOUBLE DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(255) NOT NULL
);

CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_categoria INT,
    nombre_producto VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) NOT NULL,
    stock_total INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255),
    estado ENUM(
        'agotado',
        'reservado',
        'disponible'
    ),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias (id)
);

CREATE TABLE chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_comprador INT,
    id_vendedor INT,
    id_producto INT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_comprador) REFERENCES usuarios (id),
    FOREIGN KEY (id_vendedor) REFERENCES usuarios (id),
    FOREIGN KEY (id_producto) REFERENCES usuarios (id),
    UNIQUE (
        id_comprador,
        id_vendedor,
        id_producto
    )
);

CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_chat INT,
    id_envio INT,
    contenido TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_chat) REFERENCES chats (id),
    FOREIGN KEY (id_envio) REFERENCES usuarios (id)
);

CREATE TABLE puntos_entrega (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    longitud DECIMAL(10, 8) NOT NULL,
    latitud DECIMAL(10, 8) NOT NULL,
    nombre_punto VARCHAR(255) NOT NULL,
    direccion_punto VARCHAR(255),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
);

CREATE TABLE compraventas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT,
    id_comprador INT,
    id_vendedor INT,
    id_punto INT,
    cantidad_total INT NOT NULL,
    estado ENUM(
        'pendiente',
        'en curso',
        'completado',
        'cancelado'
    ),
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_producto) REFERENCES productos (id),
    FOREIGN KEY (id_comprador) REFERENCES usuarios (id),
    FOREIGN KEY (id_vendedor) REFERENCES usuarios (id),
    FOREIGN KEY (id_punto) REFERENCES puntos_entrega (id)
);

CREATE TABLE valoraciones (
    id_valoracion INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT,
    id_resenyador INT,
    id_resenyado INT,
    valoracion ENUM('1', '2', '3', '4', '5') NOT NULL,
    comentario TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_venta) REFERENCES compraventas (id),
    FOREIGN KEY (id_resenyador) REFERENCES usuarios (id),
    FOREIGN KEY (id_resenyado) REFERENCES usuarios (id),
    UNIQUE (
        id_venta,
        id_resenyador,
        id_resenyado
    )
);