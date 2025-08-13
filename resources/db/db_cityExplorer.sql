-- Tabla Rol
CREATE TABLE Rol (
    id_rol INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT
);

-- Tabla Usuario
CREATE TABLE Usuario (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    apellidoP VARCHAR(50) NOT NULL,
    apellidoM VARCHAR(50),
    correo VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_bloqueo TIMESTAMP NULL,
    motivo_bloqueo TEXT NULL,
    bloqueado_por INT NULL,
    fecha_desbloqueo TIMESTAMP NULL,
    desbloqueado_por INT NULL,
    last_login TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    bloqueado BOOLEAN DEFAULT FALSE,
    
    -- Claves foráneas
    FOREIGN KEY (id_rol) REFERENCES Rol(id_rol) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (bloqueado_por) REFERENCES Usuario(id_usuario),
    FOREIGN KEY (desbloqueado_por) REFERENCES Usuario(id_usuario),
    
    -- Índices
    INDEX idx_usuarios_rol (id_rol),
    INDEX idx_usuarios_activo (activo)
);

-- Tabla Dirección
CREATE TABLE Direccion (
    id_direccion INT PRIMARY KEY AUTO_INCREMENT,
    calle VARCHAR(100) NOT NULL,
    numero_int VARCHAR(10) DEFAULT 'S/N',
    numero_ext VARCHAR(10) NOT NULL,
    colonia VARCHAR(100) NOT NULL,
    codigo_postal CHAR(5) NOT NULL
);

-- Tabla Método de Pago
CREATE TABLE Metodo_Pago (
    id_metodo_pago INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL
);

-- Tabla Categoría de Lugar
CREATE TABLE Categoria_Lugar (
    id_categoria INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(255)
);

-- Tabla Lugar con Relación a Dirección
CREATE TABLE Lugar (
    id_lugar INT PRIMARY KEY AUTO_INCREMENT,
    paginaWeb VARCHAR(255),
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    dias_servicio JSON, 
    num_telefonico VARCHAR(15),
    horario_apertura TIME,
    horario_cierre TIME,
    id_categoria INT NOT NULL,
    id_usuario INT NOT NULL,
    id_direccion INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_bloqueo TIMESTAMP NULL,
    motivo_bloqueo TEXT NULL,
    bloqueado_por INT NULL,
    fecha_desbloqueo TIMESTAMP NULL,
    desbloqueado_por INT NULL,
    last_login TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    bloqueado BOOLEAN DEFAULT FALSE,
    
    -- Claves foráneas
    FOREIGN KEY (id_categoria) REFERENCES Categoria_Lugar(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_direccion) REFERENCES Direccion(id_direccion) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (bloqueado_por) REFERENCES Usuario(id_usuario),
    FOREIGN KEY (desbloqueado_por) REFERENCES Usuario(id_usuario)
);

CREATE TABLE Imagenes (
    id_imagen INT PRIMARY KEY AUTO_INCREMENT,
    id_lugar INT NOT NULL,
    url VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_lugar) REFERENCES Lugar(id_lugar) ON DELETE CASCADE
);

-- Tabla Comentario
CREATE TABLE Comentario (
    id_comentario INT PRIMARY KEY AUTO_INCREMENT,
    contenido TEXT NOT NULL,
    valoracion INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_usuario INT NOT NULL,
    id_lugar INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_lugar) REFERENCES Lugar(id_lugar) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla Favorito
CREATE TABLE Favorito (
    id_favorito INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_lugar INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_lugar) REFERENCES Lugar(id_lugar) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (id_usuario, id_lugar)
);

-- Tabla Pago
CREATE TABLE Pago (
    id_pago INT PRIMARY KEY AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_lugar INT NOT NULL,
    monto DECIMAL(10, 2) NOT NULL,
    id_metodo_pago INT NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_lugar) REFERENCES Lugar(id_lugar) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_metodo_pago) REFERENCES Metodo_Pago(id_metodo_pago) ON DELETE RESTRICT ON UPDATE CASCADE
);

CREATE TABLE estadisticas_visitas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_lugar INT NOT NULL,
  id_usuario INT DEFAULT NULL,
  tiempo_visita INT NOT NULL,
  fecha DATETIME NOT NULL,
  fecha_dia DATE NOT NULL,
  FOREIGN KEY (id_lugar) REFERENCES Lugar(id_lugar) ON DELETE CASCADE,
  FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE SET NULL
);