
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
    FOREIGN KEY (id_rol) REFERENCES Rol(id_rol) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla Dirección (Nueva)
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
    FOREIGN KEY (id_categoria) REFERENCES Categoria_Lugar(id_categoria) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (id_direccion) REFERENCES Direccion(id_direccion) ON DELETE CASCADE ON UPDATE CASCADE
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
    FOREIGN KEY ( id_metodo_pago) REFERENCES Metodo_Pago( id_metodo_pago) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Tabla Lista
CREATE TABLE Lista (
    id_lista INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    id_usuario INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES Usuario(id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Tabla Lista_Lugar
CREATE TABLE Lista_Lugar (
    id_lista_lugar INT PRIMARY KEY AUTO_INCREMENT,
    id_lista INT NOT NULL,
    id_lugar INT NOT NULL,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_lista) REFERENCES Lista(id_lista) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_lugar) REFERENCES Lugar(id_lugar) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE (id_lista, id_lugar)
);

-- Inserción de Roles Corregida
INSERT INTO Rol (nombre, descripcion) VALUES ('Usuario', 'Rol con permisos totales');
INSERT INTO Rol (nombre, descripcion) VALUES ('Anunciante', 'Rol para publicar lugares');
INSERT INTO Rol (nombre, descripcion) VALUES ('Administrador', 'Rol con permisos para controlar contenido');

INSERT INTO Categoria_Lugar (id_categoria, nombre, descripcion) VALUES
(1, 'Restaurantes', 'Establecimientos donde se ofrecen una variedad de comidas y bebidas, ideales para disfrutar de una experiencia culinaria en un ambiente confortable y acogedor.'),
(2, 'Parques', 'Áreas verdes públicas diseñadas para el esparcimiento y la recreación de los visitantes, que pueden incluir jardines, juegos infantiles, áreas deportivas y espacios para actividades al aire libre.'),
(3, 'Iglesias', 'Lugares sagrados dedicados al culto y a la espiritualidad, donde se llevan a cabo ceremonias religiosas, actividades comunitarias y momentos de reflexión.'),
(4, 'Plazas', 'Espacios públicos urbanos que fomentan la convivencia social, a menudo rodeados de comercios y lugares de interés, y que pueden albergar eventos culturales y actividades comunitarias.'),
(5, 'Antros', 'Establecimientos nocturnos dedicados al entretenimiento y la diversión, donde se disfrutan de bailar, música y socializar en un ambiente vibrante y animado.'),
(6, 'Mercados', 'Lugares de compra donde los consumidores pueden adquirir una variedad de productos, desde alimentos frescos hasta artesanías, promoviendo la interacción comunitaria y el comercio local.'),
(7, 'Super Mercados', 'Establecimientos comerciales de gran tamaño que ofrecen una amplia gama de alimentos y productos de consumo diario, facilitando las compras para el hogar en un solo lugar.'),
(8, 'Tiendas', 'Establecimientos comerciales minoristas que ofrecen productos específicos, desde ropa y calzado hasta artículos de uso cotidiano, y donde el cliente puede encontrar variedad y atención personalizada.'),
(9, 'Puestos locales', 'Pequeños establecimientos de comida o venta que ofrecen productos frescos y regionales, ideales para disfrutar de antojos locales y experimentar sabores auténticos en un ambiente informal.'),
(10, 'Servicios de Ayuda', 'Lugares que brindan asistencia y apoyo a quienes lo necesitan, ofreciendo servicios como asesoría, ayuda humanitaria o programas comunitarios para mejorar la calidad de vida.');
-- Inserción de Categorías de Lugar
INSERT INTO Categoria_Lugar (id_categoria, nombre, descripcion) VALUES
(11, 'Patrimonios y Sitios Historicos', 'Lugares de gran valor histórico y cultural que representan la herencia y el legado de la región, ideales para aprender sobre el pasado y apreciar la arquitectura antigua.'),
(12, 'Arte y Cultura', 'Espacios dedicados a la expresión artística y cultural como museos, galerías y centros culturales donde se promueve la creatividad y el conocimiento.'),
(13, 'Jardines', 'Áreas naturales cuidadosamente diseñadas con vegetación ornamental, ideales para paseos tranquilos, descanso y apreciación del entorno natural.'),
(14, 'Bebidas', 'Establecimientos especializados en la preparación y venta de bebidas, desde cafés y teterías hasta bares y coctelerías, ideales para socializar y relajarse.'),
(15, 'Tours', 'Servicios organizados que ofrecen recorridos guiados por distintos puntos de interés, permitiendo a los visitantes conocer a fondo la historia, cultura y atractivos del lugar.');


-- Inserción de Métodos de Pago
INSERT INTO Metodo_Pago (nombre) VALUES ('Tarjeta de Debito');
INSERT INTO Metodo_Pago (nombre) VALUES ('Tarjeta de Credito');
