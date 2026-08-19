-- Inserción de Roles Corregida
INSERT INTO Rol (nombre, descripcion) VALUES ('Usuario', 'Rol con permisos totales');
INSERT INTO Rol (nombre, descripcion) VALUES ('Anunciante', 'Rol para publicar lugares');
INSERT INTO Rol (nombre, descripcion) VALUES ('Administrador', 'Rol con permisos para controlar contenido');

INSERT INTO Categoria_Lugar (id_categoria, nombre, descripcion, icono) VALUES
(1, 'Restaurantes', 'Establecimientos donde se ofrecen una variedad de comidas y bebidas, ideales para disfrutar de una experiencia culinaria en un ambiente confortable y acogedor.', 'fork_spoon'),
(2, 'Parques', 'Áreas verdes públicas diseñadas para el esparcimiento y la recreación de los visitantes, que pueden incluir jardines, juegos infantiles, áreas deportivas y espacios para actividades al aire libre.', 'park'),
(3, 'Iglesias', 'Lugares sagrados dedicados al culto y a la espiritualidad, donde se llevan a cabo ceremonias religiosas, actividades comunitarias y momentos de reflexión.', 'church'),
(4, 'Plazas', 'Espacios públicos urbanos que fomentan la convivencia social, a menudo rodeados de comercios y lugares de interés, y que pueden albergar eventos culturales y actividades comunitarias.', 'local_mall'),
(5, 'Antros', 'Establecimientos nocturnos dedicados al entretenimiento y la diversión, donde se disfrutan de bailar, música y socializar en un ambiente vibrante y animado.', 'nightlife'),
(6, 'Mercados', 'Lugares de compra donde los consumidores pueden adquirir una variedad de productos, desde alimentos frescos hasta artesanías, promoviendo la interacción comunitarias y el comercio local.', 'local_convenience_store'),
(7, 'Super Mercados', 'Establecimientos comerciales de gran tamaño que ofrecen una amplia gama de alimentos y productos de consumo diario, facilitando las compras para el hogar en un solo lugar.', 'shopping_cart'),
(8, 'Tiendas', 'Establecimientos comerciales minoristas que ofrecen productos específicos, desde ropa y calzado hasta artículos de uso cotidiano, y donde el cliente puede encontrar variedad y atención personalizada.', 'shop'),
(9, 'Puestos locales', 'Pequeños establecimientos de comida o venta que ofrecen productos frescos y regionales, ideales para disfrutar de antojos locales y experimentar sabores auténticos en un ambiente informal.', 'storefront'),
(10, 'Servicios de Ayuda', 'Lugares que brindan asistencia y apoyo a quienes lo necesitan, ofreciendo servicios como asesoría, ayuda humanitaria o programas comunitarios para mejorar la calidad de vida.', 'help');

-- Inserción de Categorías de Lugar
INSERT INTO Categoria_Lugar (id_categoria, nombre, descripcion, icono) VALUES
(11, 'Patrimonios y Sitios Historicos', 'Lugares de gran valor histórico y cultural que representan la herencia y el legado de la región, ideales para aprender sobre el pasado y apreciar la arquitectura antigua.', 'castle'),
(12, 'Arte y Cultura', 'Espacios dedicados a la expresión artística y cultural como museos, galerías y centros culturales donde se promueve la creatividad y el conocimiento.', 'draw_collage'),
(13, 'Jardines', 'Áreas naturales cuidadosamente diseñadas con vegetación ornamental, ideales para paseos tranquilos, descanso y apreciación del entorno natural.', 'nature'),
(14, 'Bebidas', 'Establecimientos especializados en la preparación y venta de bebidas, desde cafés y teterías hasta bares y coctelerías, ideales para socializar y relajarse.', 'water_full'),
(15, 'Tours', 'Servicios organizados que ofrecen recorridos guiados por distintos puntos de interés, permitiendo a los visitantes conocer a fondo la historia, cultura y atractivos del lugar.', 'airport_shuttle');

-- Inserción de Métodos de Pago
INSERT INTO Metodo_Pago (nombre) VALUES ('Tarjeta de Debito');
INSERT INTO Metodo_Pago (nombre) VALUES ('Tarjeta de Credito');