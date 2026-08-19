-- ============================================================
-- SEED DATA: San Miguel de Allende, Guanajuato
-- Ejecutar DESPUÉS de inserts.sql
-- ============================================================

-- ============================================================
-- USUARIOS
-- ============================================================
INSERT INTO Usuario (nombre, apellidoP, correo, password, id_rol, activo, bloqueado, foto_perfil)
VALUES
('Admin', 'SMA', 'admin@sma.com', '$2y$12$dIKZweG/gQW4iT4k8Dgzq.M5uu11hjPXjYbPx4ubFjilt4.VmYWjy', 3, TRUE, FALSE, 'default-avatar.png'),
('Anunciante', 'SMA', 'anunciante@sma.com', '$2y$12$dIKZweG/gQW4iT4k8Dgzq.M5uu11hjPXjYbPx4ubFjilt4.VmYWjy', 2, TRUE, FALSE, 'default-avatar.png');

-- ============================================================
-- DIRECCIONES Y LUGARES
-- ============================================================

-- ==================== CATEGORÍA 1: RESTAURANTES ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Cuna de Allende', 'S/N', '3', 'Centro', '37700'),
('Aldama', 'S/N', '7', 'Centro', '37700'),
('Cuna de Allende', 'S/N', '11', 'Centro', '37700'),
('Órganos', 'S/N', '19', 'Centro', '37700'),
('Hidalgo', 'S/N', '12', 'Centro', '37700'),
('Zacateros', 'S/N', '38', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('El Pegaso', 'Restaurante y bar emblemático de San Miguel de Allende con terraza en el techo, música en vivo y cocina mexicana contemporánea. Ofrece vistas espectaculares del Jardín Principal y la Parroquia.', 'https://elpegaso.mx', '4151523351', '08:00:00', '23:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 1, 2, 1, TRUE),
('Moxi', 'Restaurante de alta cocina ubicado en el Hotel Matilda. Ofrece una fusión de sabores mexicanos e internacionales con ingredientes locales y una presentación artística.', 'https://hotelmatilda.com/moxi', '4151523352', '07:00:00', '22:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 1, 2, 2, TRUE),
('La Parada', 'Restaurante de fusión peruana-mexicana con un ambiente moderno y vibrante. Sus ceviches y platos de autor son famosos entre locales y turistas.', 'https://laparada.mx', '4151523353', '13:00:00', '23:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 1, 2, 3, TRUE),
('Casa de la Noche', 'Acogedor restaurante en un patio colonial con iluminación de velas. Especializado en cocina mexicana tradicional con un toque contemporáneo. Ambiente romántico e íntimo.', 'https://casadelanoche.com', '4151523354', '08:00:00', '22:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 1, 2, 4, TRUE),
('Hecho en México', 'Restaurante de cocina mexicana auténtica con recetas tradicionales de diferentes regiones del país. Sus tacos, moles y enchiladas son preparados con ingredientes frescos y locales.', 'https://hechoenmexico.mx', '4151523355', '08:00:00', '23:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 1, 2, 5, TRUE),
('Bovino Steakhouse', 'Steakhouse argentino-mexicano que ofrece cortes de res premium, vinos seleccionados y un ambiente elegante pero relajado. Ideal para cenas especiales.', 'https://bovinosteaakhouse.com', '4151523356', '13:00:00', '23:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 1, 2, 6, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(1, 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&h=600&fit=crop'),
(2, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&h=600&fit=crop'),
(3, 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&h=600&fit=crop'),
(4, 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=800&h=600&fit=crop'),
(5, 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=800&h=600&fit=crop'),
(6, 'https://images.unsplash.com/photo-1550966871-3ed3cdb51f3a?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 2: PARQUES ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Plaza Principal', 'S/N', 'S/N', 'Centro', '37700'),
('Calzada de la Presa', 'S/N', 'S/N', 'Centro', '37700'),
('Av. Independencia', 'S/N', 'S/N', 'Centro', '37700'),
('Salida a Celaya', 'S/N', 'S/N', 'Guadalupe', '37700'),
('Palmita', 'S/N', 'S/N', 'Los Picachos', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Jardín Principal', 'El corazón de San Miguel de Allende. Plaza principal rodeada de portales, bancas de hierro forjado y laureles indios perfectamente podados. Punto de encuentro de locales y turistas.', NULL, '4151523401', '06:00:00', '22:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 2, 2, 7, TRUE),
('Parque Juárez', 'Hermoso parque público con áreas verdes, fuente central, juegos infantiles, canchas deportivas y un pequeño lago artificial. Ideal para paseos familiares y actividades al aire libre.', NULL, '4151523402', '06:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 2, 2, 8, TRUE),
('Parque Benito Juárez', 'Parque lineal ubicado sobre la antigua vía del ferrocarril. Cuenta con áreas verdes, pistas para correr, ejercitadores al aire libre y un ambiente tranquilo para caminar.', NULL, '4151523403', '06:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 2, 2, 9, TRUE),
('Parque Zeferino Gutiérrez', 'Parque vecinal con áreas verdes, juegos infantiles y canchas deportivas. Espacio recreativo importante para la colonia Guadalupe y zonas aledañas.', NULL, '4151523404', '06:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 2, 2, 10, TRUE),
('Ecoparque El Charco del Ingenio', 'Jardín botánico y área natural protegida de 65 hectáreas. Alberga una impresionante colección de cactáceas y plantas suculentas, un antiguo acueducto colonial y miradores espectaculares.', 'https://elcharco.org.mx', '4151523405', '09:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 2, 2, 11, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(7, 'https://images.unsplash.com/photo-1584382869730-e1b1e0c5d5b0?w=800&h=600&fit=crop'),
(8, 'https://images.unsplash.com/photo-1447072632859-3285df2c3a8c?w=800&h=600&fit=crop'),
(9, 'https://images.unsplash.com/photo-1509515837298-2c67a3933321?w=800&h=600&fit=crop'),
(10, 'https://images.unsplash.com/photo-1587502536575-6dfba0a6a7d2?w=800&h=600&fit=crop'),
(11, 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 3: IGLESIAS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Plaza Principal', 'S/N', 'S/N', 'Centro', '37700'),
('Canal', 'S/N', '4', 'Centro', '37700'),
('Juárez', 'S/N', 'S/N', 'Centro', '37700'),
('Santo Domingo', 'S/N', '1', 'Centro', '37700'),
('Insurgentes', 'S/N', '25', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Parroquia de San Miguel Arcángel', 'Icono arquitectónico de la ciudad con su fachada neogótica única en México. Construida en el siglo XVII, su fachada fue diseñada por el arquitecto autodidacta Zeferino Gutiérrez.', NULL, '4151523451', '06:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 3, 2, 12, TRUE),
('Templo de la Inmaculada Concepción (Las Monjas)', 'Conocido como Las Monjas, este templo del siglo XIX perteneció al convento de las Concepcionistas. Destaca por su gran cúpula y su imponente fachada neoclásica.', NULL, '4151523452', '07:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 3, 2, 13, TRUE),
('Templo de San Francisco', 'Templo franciscano del siglo XVIII con una fachada barroca ricamente ornamentada. Su interior alberga importantes obras de arte sacro y retablos dorados.', NULL, '4151523453', '07:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 3, 2, 14, TRUE),
('Templo de Santo Domingo', 'Iglesia de estilo barroco con una hermosa fachada de cantera rosa. Construida en el siglo XVIII, forma parte del exconvento dominico y es un importante punto de referencia en la ciudad.', NULL, '4151523454', '07:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 3, 2, 15, TRUE),
('Oratorio de San Felipe Neri', 'Templo del siglo XVIII conocido por su hermosa fachada churrigueresca y su interior decorado con pinturas al óleo. Alberga la imagen del Señor de la Conquista.', NULL, '4151523455', '07:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 3, 2, 16, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(12, 'https://images.unsplash.com/photo-1584300484645-30d4bb4a7fe2?w=800&h=600&fit=crop'),
(13, 'https://images.unsplash.com/photo-1605092677722-1c798f5af344?w=800&h=600&fit=crop'),
(14, 'https://images.unsplash.com/photo-1578145979246-50d9aaa1ad02?w=800&h=600&fit=crop'),
(15, 'https://images.unsplash.com/photo-1599059813005-11265ba4b4ce?w=800&h=600&fit=crop'),
(16, 'https://images.unsplash.com/photo-1590626852659-31b6b44b1fe8?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 4: PLAZAS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Plaza Principal', 'S/N', 'S/N', 'Centro', '37700'),
('Calle de Allende', 'S/N', 'S/N', 'Centro', '37700'),
('Hidalgo', 'S/N', 'S/N', 'Centro', '37700'),
('Soledad', 'S/N', 'S/N', 'Centro', '37700'),
('Salida a Querétaro', 'S/N', 'S/N', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Plaza Principal (Jardín Principal)', 'Corazón de San Miguel de Allende, rodeada de portales coloniales, restaurantes y tiendas. Punto de encuentro por excelencia con vista a la Parroquia.', NULL, NULL, '00:00:00', '23:59:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 4, 2, 17, TRUE),
('Plaza Allende', 'Encantadora plaza ubicada frente a la Casa de Allende. Con una fuente central y jardines bien cuidados, es un espacio tranquilo ideal para descansar y disfrutar del ambiente colonial.', NULL, NULL, '00:00:00', '23:59:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 4, 2, 18, TRUE),
('Plaza Cívica', 'Espacio público frente a la Presidencia Municipal donde se realizan eventos cívicos y culturales. Cuenta con una estatua de Ignacio Allende y amplias áreas de descanso.', NULL, NULL, '00:00:00', '23:59:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 4, 2, 19, TRUE),
('Plaza de la Soledad', 'Plaza tranquila y pintoresca ubicada en el antiguo barrio de la Soledad. Rodeada de casas coloniales y conocida por su ambiente sereno y su pequeña capilla.', NULL, NULL, '00:00:00', '23:59:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 4, 2, 20, TRUE),
('Plaza de la Luciérnaga', 'Moderna plaza comercial y gastronómica con fuentes danzantes, áreas verdes y una variedad de restaurantes y tiendas. Popular entre familias y jóvenes.', NULL, NULL, '06:00:00', '23:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 4, 2, 21, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(17, 'https://images.unsplash.com/photo-1605106250963-2f5ec64f6b10?w=800&h=600&fit=crop'),
(18, 'https://images.unsplash.com/photo-1578898887932-dce23a595ad4?w=800&h=600&fit=crop'),
(19, 'https://images.unsplash.com/photo-1551817959-ee0d9c38786d?w=800&h=600&fit=crop'),
(20, 'https://images.unsplash.com/photo-1578235296215-1e9e55b0f6b9?w=800&h=600&fit=crop'),
(21, 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 5: ANTROS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Umarán', 'S/N', '6', 'Centro', '37700'),
('Cantarranas', 'S/N', '8', 'Centro', '37700'),
('Zacateros', 'S/N', '39', 'Centro', '37700'),
('Hidalgo', 'S/N', '14', 'Centro', '37700'),
('Reloj', 'S/N', '20', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('La Azotea', 'Exclusivo bar en azotea con vistas panorámicas de la Parroquia y el centro histórico. Ambiente sofisticado con coctelería de autor, música lounge y一双 hermosa iluminación.', 'https://laazotea.mx', '4151523501', '18:00:00', '02:00:00', '["Jueves","Viernes","Sabado","Domingo"]', 5, 2, 22, TRUE),
('El Ring', 'Antro y bar deportivo con pantallas gigantes, mesa de billar y una pista de baile. Ambiente animado con música variada que va desde reguetón hasta rock en español.', NULL, '4151523502', '20:00:00', '03:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 5, 2, 23, TRUE),
('Mr. Black', 'Bar clandestino de coctelería de autor con un ambiente íntimo y elegante. Especializado en whiskies añejos y cocteles clásicos con un toque mexicano.', NULL, '4151523503', '19:00:00', '02:00:00', '["Jueves","Viernes","Sabado","Domingo"]', 5, 2, 24, TRUE),
('Garufa', 'Antro y lounge con música electrónica, reguetón y cumbia. Cuenta con una pista de baile amplia, iluminación láser y un ambiente festivo que atrae a jóvenes y no tan jóvenes.', NULL, '4151523504', '20:00:00', '03:00:00', '["Viernes","Sabado","Domingo"]', 5, 2, 25, TRUE),
('La Bibelot', 'Bar y discoteca con un concepto único de arte y música. Su decoración ecléctica combina obras de arte locales con un sistema de sonido de primer nivel.', NULL, '4151523505', '21:00:00', '03:00:00', '["Viernes","Sabado","Domingo"]', 5, 2, 26, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(22, 'https://images.unsplash.com/photo-1566417713940-fe7c737a9ef2?w=800&h=600&fit=crop'),
(23, 'https://images.unsplash.com/photo-1514528753872-db12e8a280d8?w=800&h=600&fit=crop'),
(24, 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=800&h=600&fit=crop'),
(25, 'https://images.unsplash.com/photo-1571266028243-e4733b0f0bb0?w=800&h=600&fit=crop'),
(26, 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 6: MERCADOS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Lucas Balderas', 'S/N', '2', 'Centro', '37700'),
('Colegio', 'S/N', '1', 'Centro', '37700'),
('Mesones', 'S/N', '50', 'Centro', '37700'),
('Av. Independencia', 'S/N', 'S/N', 'Allende', '37700'),
('San Francisco', 'S/N', 'S/N', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Mercado de Artesanías', 'Mercado tradicional donde artesanos locales venden sus creaciones: cerámica, textiles, joyería de plata, alebrijes, vidrio soplado y más. Ideal para encontrar recuerdos únicos.', NULL, '4151523551', '09:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 6, 2, 27, TRUE),
('Mercado Ignacio Ramírez', 'Mercado municipal donde se concentran puestos de frutas, verduras, carnes, flores y comida preparada. Experiencia auténtica de la vida cotidiana sanmiguelense.', NULL, '4151523552', '07:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 6, 2, 28, TRUE),
('Mercado Sano', 'Mercado orgánico y de productos naturales que ofrece frutas, verduras, pan artesanal, miel, quesos y productos gourmet. Se enfoca en alimentos saludables y sustentables.', NULL, '4151523553', '08:00:00', '17:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"]', 6, 2, 29, TRUE),
('Tianguis de los Martes', 'Tradicional tianguis semanal que se instala cada martes en la avenida Independencia. Ropa, accesorios, frutas, verduras y productos del hogar a precios populares.', NULL, NULL, '06:00:00', '15:00:00', '["Martes"]', 6, 2, 30, TRUE),
('Mercado de la Calle San Francisco', 'Conjunto de puestos artesanales a lo largo de la calle San Francisco. Joyería, ropa de algodón, bolsas de cuero, sombreros y artículos decorativos.', NULL, NULL, '09:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 6, 2, 31, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(27, 'https://images.unsplash.com/photo-1555939653-1f6b5e6e3d4c?w=800&h=600&fit=crop'),
(28, 'https://images.unsplash.com/photo-1566385101042-1a0b5e6e8e3f?w=800&h=600&fit=crop'),
(29, 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop'),
(30, 'https://images.unsplash.com/photo-1534723452862-4c87418d4c1c?w=800&h=600&fit=crop'),
(31, 'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 7: SUPER MERCADOS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Salida a Celaya', 'S/N', '1', 'Guadalupe', '37700'),
('Av. Independencia', 'S/N', '500', 'Centro', '37700'),
('Salida a Celaya', 'S/N', '100', 'Los Picachos', '37700'),
('Libramiento Manuel Zavala', 'S/N', '200', 'San Luis Rey', '37700'),
('Calzada de la Presa', 'S/N', '45', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('La Comer', 'Supermercado de gran formato con amplia variedad de productos nacionales e internacionales. Incluye panadería, carnicería, frutería y sección de vinos.', NULL, '4151523601', '07:00:00', '22:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 7, 2, 32, TRUE),
('Soriana', 'Cadena de supermercados con una amplia gama de productos alimenticios, de limpieza, cuidado personal y electrónicos. Cuenta con estacionamiento amplio.', NULL, '4151523602', '07:00:00', '22:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 7, 2, 33, TRUE),
('Bodega Aurrerá', 'Tienda de autoservicio con precios bajos.Cuenta con secciones de abarrotes, perecederos, ropa y artículos para el hogar.', NULL, '4151523603', '08:00:00', '22:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 7, 2, 34, TRUE),
('Walmart', 'Hipermercado con la más amplia variedad de productos: alimentos, electrónica, ropa, juguetes, jardinería y farmacia. Abierto todos los días.', NULL, '4151523604', '07:00:00', '23:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 7, 2, 35, TRUE),
('Super Q', 'Supermercado de conveniencia con productos de calidad a buen precio. Ideal para compras rápidas con buena variedad de frutas, verduras y abarrotes.', NULL, '4151523605', '07:00:00', '21:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 7, 2, 36, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(32, 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop'),
(33, 'https://images.unsplash.com/photo-1578916173728-00d8a3f7c6a0?w=800&h=600&fit=crop'),
(34, 'https://images.unsplash.com/photo-1534723452862-4c87418d4c1c?w=800&h=600&fit=crop'),
(35, 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&h=600&fit=crop'),
(36, 'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 8: TIENDAS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Reloj', 'S/N', '12', 'Centro', '37700'),
('Canal', 'S/N', '12', 'Centro', '37700'),
('San Francisco', 'S/N', '32', 'Centro', '37700'),
('Plaza Principal', 'S/N', '8', 'Centro', '37700'),
('Mesones', 'S/N', '93', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Mixta', 'Tienda de diseño de interiores y decoración con piezas únicas de artistas y artesanos mexicanos. Muebles, textiles, cerámica y objetos decorativos contemporáneos.', 'https://mixta.mx', '4151523651', '10:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 8, 2, 37, TRUE),
('Casa Dragón', 'Concept store que ofrece ropa, accesorios, arte y objetos de diseño con influencia mexicana y oriental. Un espacio ecléctico y lleno de personalidad.', NULL, '4151523652', '10:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"]', 8, 2, 38, TRUE),
('Ángeles de México', 'Joyería especializada en plata mexicana con diseños tradicionales y contemporáneos. Ofrece aretes, collares, pulseras y anillos elaborados por artesanos locales.', NULL, '4151523653', '10:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 8, 2, 39, TRUE),
('Fábrica de la Plaza', 'Tienda de artesanías y regalos ubicada en el portal principal. Sombreros, rebozos, alebrijes, talavera, vidrio soplado y una gran variedad de productos mexicanos.', NULL, '4151523654', '10:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 8, 2, 40, TRUE),
('La Calaca', 'Tienda de arte popular mexicano especializada en calaveras de catrinas, esqueletos artesanales y arte relacionado con el Día de Muertos. Piezas únicas hechas a mano.', NULL, '4151523655', '10:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"]', 8, 2, 41, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(37, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&h=600&fit=crop'),
(38, 'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?w=800&h=600&fit=crop'),
(39, 'https://images.unsplash.com/photo-1601121145789-63b73b5f3a08?w=800&h=600&fit=crop'),
(40, 'https://images.unsplash.com/photo-1606326608606-aa0b62935f2b?w=800&h=600&fit=crop'),
(41, 'https://images.unsplash.com/photo-1571066811602-716837d681de?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 9: PUESTOS LOCALES ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('San Francisco', 'S/N', '10', 'Centro', '37700'),
('Colegio', 'S/N', '20', 'Centro', '37700'),
('Plaza Principal', 'S/N', 'S/N', 'Centro', '37700'),
('Mercado Ignacio Ramírez', 'S/N', 'S/N', 'Centro', '37700'),
('Jardín Principal', 'S/N', 'S/N', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Tacos El Papi', 'Puesto de tacos al pastor y de bistec con tortillas hechas a mano. Famoso entre los locales por su salsa verde y su ambiente nocturno. Imperdible después de una noche de fiesta.', NULL, '4151523701', '19:00:00', '01:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 9, 2, 42, TRUE),
('Gorditas Doña Chely', 'Puesto tradicional de gorditas rellenas de chicharrón, tinga, picadillo y nopales. Masa hecha a mano y frita al momento. Sabor casero que conquista a todos.', NULL, '4151523702', '08:00:00', '16:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"]', 9, 2, 43, TRUE),
('Elotes Doña Mary', 'Carrito de elotes y esquites con todo el sabor de México. Elotes asados o hervidos con mayonesa, queso, crema, chile en polvo y limón. Un clásico de la plaza.', NULL, '4151523703', '10:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 9, 2, 44, TRUE),
('Tamales Doña Tere', 'Puesto de tamales tradicionales de hoja de maíz y hoja de plátano. Variedades: verde, rojo, mole, dulce y rajas con queso. Acompañados de atole de distintos sabores.', NULL, '4151523704', '07:00:00', '14:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 9, 2, 45, TRUE),
('Esquites El Güero', 'Puesto especializado en esquites: granos de elote cocidos en epazote con chile, limón, crema y queso. También ofrece tostadas de pata y cueritos.', NULL, '4151523705', '11:00:00', '21:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 9, 2, 46, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(42, 'https://images.unsplash.com/photo-1551504734-5ee1c4a1479b?w=800&h=600&fit=crop'),
(43, 'https://images.unsplash.com/photo-1625943553852-781c6dd46faa?w=800&h=600&fit=crop'),
(44, 'https://images.unsplash.com/photo-1613514785940-daed07799d9b?w=800&h=600&fit=crop'),
(45, 'https://images.unsplash.com/photo-1657300147021-f19f25a29e32?w=800&h=600&fit=crop'),
(46, 'https://images.unsplash.com/photo-1625225233840-695456021cde?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 10: SERVICIOS DE AYUDA ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Dr. Ignacio Hernández Macías', 'S/N', '75', 'Centro', '37700'),
('Salida a Celaya', 'S/N', '200', 'Guadalupe', '37700'),
('Hidalgo', 'S/N', '8', 'Centro', '37700'),
('Calzada de la Presa', 'S/N', '100', 'Centro', '37700'),
('Salida a Celaya', 'S/N', '5', 'Guadalupe', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('DIF San Miguel de Allende', 'Sistema para el Desarrollo Integral de la Familia. Brinda asistencia social, apoyos alimentarios, atención psicológica y programas de bienestar para la comunidad.', NULL, '4151523751', '09:00:00', '16:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes"]', 10, 2, 47, TRUE),
('Cruz Roja Mexicana', 'Institución de asistencia médica de emergencia. Servicio de ambulancias, atención prehospitalaria, paramédicos las 24 horas y cursos de primeros auxilios.', NULL, '4151523752', '00:00:00', '23:59:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 10, 2, 48, TRUE),
('Protección Civil Municipal', 'Dependencia encargada de la prevención y atención de emergencias y desastres naturales. Realiza evaluaciones de riesgo y coordina operativos de seguridad.', NULL, '4151523753', '09:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes"]', 10, 2, 49, TRUE),
('Estación de Bomberos', 'Cuerpo de bomberos municipal que atiende incendios, rescates y emergencias. Cuenta con personal capacitado y equipo especializado para proteger a la comunidad.', NULL, '4151523754', '00:00:00', '23:59:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 10, 2, 50, TRUE),
('Hospital General', 'Hospital público que ofrece servicios de urgencias, consulta externa, hospitalización, cirugía y maternidad. Cuenta con especialidades básicas y personal médico calificado.', NULL, '4151523755', '00:00:00', '23:59:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 10, 2, 51, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(47, 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=800&h=600&fit=crop'),
(48, 'https://images.unsplash.com/photo-1584515933487-779824d29309?w=800&h=600&fit=crop'),
(49, 'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=800&h=600&fit=crop'),
(50, 'https://images.unsplash.com/photo-1590626852659-31b6b44b1fe8?w=800&h=600&fit=crop'),
(51, 'https://images.unsplash.com/photo-1631217868264-e5b90bb7e133?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 11: PATRIMONIOS Y SITIOS HISTÓRICOS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Cuna de Allende', 'S/N', '1', 'Centro', '37700'),
('Plaza Principal', 'S/N', 'S/N', 'Centro', '37700'),
('Juárez', 'S/N', 'S/N', 'Centro', '37700'),
('Plaza Principal', 'S/N', '2', 'Centro', '37700'),
('Atotonilco', 'S/N', 'S/N', 'Atotonilco', '37760');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Casa de Allende (Museo Histórico)', 'Casa natal de Ignacio Allende, héroe de la Independencia de México. Actualmente es un museo que exhibe muebles, documentos y objetos de la época colonial y de la Independencia.', NULL, '4151523801', '09:00:00', '17:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 11, 2, 52, TRUE),
('Parroquia de San Miguel Arcángel', 'Monumento histórico y símbolo de la ciudad. Su fachada neogótica diseñada por Zeferino Gutiérrez la convierte en una de las iglesias más fotografiadas de México. Patrimonio de la humanidad.', NULL, '4151523802', '06:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 11, 2, 53, TRUE),
('Templo de San Francisco', 'Construcción franciscana del siglo XVIII con una impresionante fachada barroca. Forma parte del conjunto arquitectónico que le valió a San Miguel el título de Patrimonio Mundial de la UNESCO.', NULL, '4151523803', '07:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 11, 2, 54, TRUE),
('Antigua Presidencia Municipal', 'Edificio colonial del siglo XVIII que albergó el ayuntamiento de la ciudad. Su fachada de cantera rosa y sus arcos son un ejemplo de la arquitectura civil de la época.', NULL, '4151523804', '09:00:00', '17:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes"]', 11, 2, 55, TRUE),
('Santuario de Atotonilco', 'Templo del siglo XVIII conocido como la Capilla Sixtina de México por sus impresionantes murales y frescos religiosos. Patrimonio Mundial de la UNESCO, ubicado a 14 km de SMA.', NULL, '4151523805', '08:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 11, 2, 56, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(52, 'https://images.unsplash.com/photo-1592805144716-feeccccef718?w=800&h=600&fit=crop'),
(53, 'https://images.unsplash.com/photo-1584300484645-30d4bb4a7fe2?w=800&h=600&fit=crop'),
(54, 'https://images.unsplash.com/photo-1605092677722-1c798f5af344?w=800&h=600&fit=crop'),
(55, 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=600&fit=crop'),
(56, 'https://images.unsplash.com/photo-1602539162782-7b252b1c75ba?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 12: ARTE Y CULTURA ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Hernández Macías', 'S/N', '75', 'Centro', '37700'),
('Cuna de Allende', 'S/N', '11', 'Centro', '37700'),
('Calzada de la Aurora', 'S/N', 'S/N', 'Aurora', '37700'),
('Mesones', 'S/N', '86', 'Centro', '37700'),
('Ancha de San Antonio', 'S/N', '20', 'San Antonio', '37700'),
('Mesones', 'S/N', '82', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Centro Cultural El Nigromante', 'Antiguo convento del siglo XVIII convertido en centro cultural del INBA. Alberga exposiciones de arte, talleres, conciertos y eventos culturales. Su arquitectura es impresionante.', NULL, '4151523851', '10:00:00', '18:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 12, 2, 57, TRUE),
('Museo de la Máscara', 'Museo único en su tipo que alberga una colección de más de 200 máscaras tradicionales mexicanas de diversas regiones del país. Muestra la riqueza del arte popular mexicano.', NULL, '4151523852', '10:00:00', '18:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 12, 2, 58, TRUE),
('Fábrica la Aurora', 'Antigua fábrica textil convertida en galería de arte y centro creativo. Alberga estudios de artistas, galerías de arte contemporáneo, tiendas de diseño y restaurantes.', 'https://fabricalaaurora.com', '4151523853', '10:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 12, 2, 59, TRUE),
('Galería San Miguel', 'Galería de arte que representa a artistas locales e internacionales. Exposiciones rotativas de pintura, escultura, fotografía y arte contemporáneo en un espacio colonial restaurado.', NULL, '4151523854', '10:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"]', 12, 2, 60, TRUE),
('Instituto Allende', 'Institución educativa y cultural fundada en 1950. Ofrece cursos de arte, idiomas y cultura mexicana. Su campus colonial con jardines es un atractivo en sí mismo.', 'https://institutoallende.edu.mx', '4151523855', '09:00:00', '17:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes"]', 12, 2, 61, TRUE),
('Teatro Ángela Peralta', 'Teatro histórico del siglo XIX restaurado, con una acústica excepcional y una decoración neoclásica. Alberga conciertos, obras de teatro, ópera y eventos culturales.', NULL, '4151523856', '10:00:00', '21:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 12, 2, 62, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(57, 'https://images.unsplash.com/photo-1561214115-f2f134cc4912?w=800&h=600&fit=crop'),
(58, 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?w=800&h=600&fit=crop'),
(59, 'https://images.unsplash.com/photo-1501084817091-a4f3d1d19e07?w=800&h=600&fit=crop'),
(60, 'https://images.unsplash.com/photo-1596495577886-d920f1fb7238?w=800&h=600&fit=crop'),
(61, 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800&h=600&fit=crop'),
(62, 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 13: JARDINES ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Palmita', 'S/N', 'S/N', 'Los Picachos', '37700'),
('Plaza Principal', 'S/N', 'S/N', 'Centro', '37700'),
('Calzada de la Presa', 'S/N', 'S/N', 'Centro', '37700'),
('Salida a Celaya', 'S/N', 'S/N', 'Los Picachos', '37700'),
('Santa Casa', 'S/N', 'S/N', 'Santa Julia', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('El Charco del Ingenio', 'Jardín botánico y reserva natural de 65 hectáreas. Alberga más de 2,500 especies de cactáceas y plantas suculentas, un acueducto colonial y senderos ecológicos con vistas panorámicas.', 'https://elcharco.org.mx', '4151523901', '09:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 13, 2, 63, TRUE),
('Jardín Principal', 'Jardín público más emblemático de la ciudad, con laureles indios podados en formas geométricas, bancas de hierro forjado y una fuente central. Ideal para pasear y descansar.', NULL, '4151523902', '06:00:00', '22:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 13, 2, 64, TRUE),
('Parque Juárez', 'Extenso parque con jardines bien cuidados, árboles centenarios, un lago artificial con patos, juegos infantiles y senderos para caminar. Pulmón verde de la ciudad.', NULL, '4151523903', '06:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 13, 2, 65, TRUE),
('Jardín Botánico Cactáceas', 'Jardín especializado en cactáceas y suculentas de la región del Bajío. Cuenta con invernaderos, colecciones científicas y áreas de exhibición de plantas del desierto.', NULL, '4151523904', '09:00:00', '17:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 13, 2, 66, TRUE),
('Huerto de la Santa Casa', 'Huerto orgánico y jardín comunitario que promueve la agricultura sustentable. Espacio verde donde se cultivan hortalizas, hierbas aromáticas y flores comestibles.', NULL, '4151523905', '08:00:00', '17:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"]', 13, 2, 67, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(63, 'https://images.unsplash.com/photo-1558618666-fcd25c85f82e?w=800&h=600&fit=crop'),
(64, 'https://images.unsplash.com/photo-1584382869730-e1b1e0c5d5b0?w=800&h=600&fit=crop'),
(65, 'https://images.unsplash.com/photo-1447072632859-3285df2c3a8c?w=800&h=600&fit=crop'),
(66, 'https://images.unsplash.com/photo-1466785902778-6aa89da9b7ef?w=800&h=600&fit=crop'),
(67, 'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 14: BEBIDAS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Cuna de Allende', 'S/N', '12', 'Centro', '37700'),
('Reloj', 'S/N', '5', 'Centro', '37700'),
('Diez de Sollano', 'S/N', '24', 'Centro', '37700'),
('Umarán', 'S/N', '6', 'Centro', '37700'),
('Mesones', 'S/N', '100', 'Centro', '37700'),
('Hidalgo', 'S/N', '12', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Café de la Parroquia', 'Cafetería tradicional con más de 30 años de historia. Ofrece café de especialidad mexicano, capuchinos, lattes y repostería artesanal en un ambiente colonial acogedor.', NULL, '4151523951', '07:00:00', '22:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 14, 2, 68, TRUE),
('La Ventana', 'Cafetería de especialidad con granos de origen mexicano tostados localmente. Ambiente moderno y relajado, ideal para trabajar o leer mientras disfrutas de un buen café.', NULL, '4151523952', '07:00:00', '21:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 14, 2, 69, TRUE),
('Antonia Coffee Roasters', 'Tostadores de café artesanal que ofrecen café de especialidad de fincas mexicanas. Catas, café recién tostado y un espacio minimalista para disfrutar de la bebida.', 'https://antoniacoffee.com', '4151523953', '07:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 14, 2, 70, TRUE),
('La Azotea', 'Bar en azotea con coctelería de autor, vinos selectos y vistas espectaculares. Especialidad en margaritas artesanales y cocteles con infusión de sabores mexicanos.', 'https://laazotea.mx', '4151523954', '18:00:00', '02:00:00', '["Jueves","Viernes","Sabado","Domingo"]', 14, 2, 71, TRUE),
('El Circo', 'Bar y lounge con ambiente ecléctico y coctelería creativa. Decorado con arte circense, ofrece espectáculos en vivo los fines de semana y una carta de cocteles única.', NULL, '4151523955', '17:00:00', '01:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 14, 2, 72, TRUE),
('Hank New Orleans Oyster Bar', 'Bar de ostras y coctelería con ambiente inspirado en Nueva Orleans. Especialidad en martinis, micheladas preparadas y una selección de whiskies y tequilas.', NULL, '4151523956', '12:00:00', '23:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 14, 2, 73, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(68, 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=800&h=600&fit=crop'),
(69, 'https://images.unsplash.com/photo-1559496417-e7f25cb247f3?w=800&h=600&fit=crop'),
(70, 'https://images.unsplash.com/photo-1497935586351-b67a49e012bf?w=800&h=600&fit=crop'),
(71, 'https://images.unsplash.com/photo-1566417713940-fe7c737a9ef2?w=800&h=600&fit=crop'),
(72, 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=800&h=600&fit=crop'),
(73, 'https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=800&h=600&fit=crop');

-- ==================== CATEGORÍA 15: TOURS ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Centro', 'S/N', 'S/N', 'Centro', '37700'),
('Carretera a Dolores Hidalgo', 'S/N', 'KM 15', 'Zona Rural', '37800'),
('Zona Rural', 'S/N', 'S/N', 'San Miguel', '37700'),
('Centro', 'S/N', 'S/N', 'Centro', '37700'),
('Centro', 'S/N', 'S/N', 'Centro', '37700'),
('San Antonio', 'S/N', '30', 'San Antonio', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Walking Tours San Miguel', 'Recorridos guiados a pie por el centro histórico. Guías expertos comparten la historia, arquitectura y leyendas de San Miguel. Tours en español e inglés.', 'https://walkingtourssma.com', '4151523991', '09:00:00', '17:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 15, 2, 74, TRUE),
('Viñedos San Miguel', 'Recorrido por viñedos locales con cata de vinos mexicanos. Incluye visita a la bodega, explicación del proceso de elaboración y degustación de vinos premiados.', 'https://viniedossma.com', '4151523992', '10:00:00', '18:00:00', '["Jueves","Viernes","Sabado","Domingo"]', 15, 2, 75, TRUE),
('Globos Aerostáticos San Miguel', 'Vuelo en globo aerostático sobre San Miguel de Allende y sus alrededores. Experiencia única al amanecer con vistas panorámicas del centro histórico y el campo.', 'https://globossma.com', '4151523993', '05:00:00', '11:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 15, 2, 76, TRUE),
('Tour Guanajuato', 'Excursión de día completo a la ciudad de Guanajuato (a 1 hora). Incluye transporte, visita al Museo de las Momias, el Callejón del Beso y el Teatro Juárez.', NULL, '4151523994', '08:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 15, 2, 77, TRUE),
('Santuario de Atotonilco Tour', 'Tour guiado al Santuario de Atotonilco, Patrimonio Mundial. Incluye transporte, entrada guiada y explicación de los murales y frescos del siglo XVIII.', NULL, '4151523995', '08:00:00', '14:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 15, 2, 78, TRUE),
('Clases de Cocina Sazón', 'Clases de cocina mexicana tradicional. Aprende a preparar moles, salsas, tamales y platillos típicos con ingredientes frescos del mercado local.', 'https://sazoncocina.com', '4151523996', '10:00:00', '14:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"]', 15, 2, 79, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(74, 'https://images.unsplash.com/photo-1551069613-1904dbdcda11?w=800&h=600&fit=crop'),
(75, 'https://images.unsplash.com/photo-1551105502-0f5c6a0f2a0c?w=800&h=600&fit=crop'),
(76, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop'),
(77, 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?w=800&h=600&fit=crop'),
(78, 'https://images.unsplash.com/photo-1602539162782-7b252b1c75ba?w=800&h=600&fit=crop'),
(79, 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?w=800&h=600&fit=crop');

-- ============================================================
-- VALORACIONES / COMENTARIOS
-- ============================================================
INSERT INTO Comentario (contenido, valoracion, id_usuario, id_lugar) VALUES
('Excelente restaurante, la comida es espectacular y el servicio de primera. Las vistas desde la terraza son increíbles.', 5, 1, 1),
('Muy rica comida pero un poco caro. El ambiente es muy agradable y el personal muy atento.', 4, 1, 1),
('La mejor pizza de San Miguel, masa delgada y crujiente. El lugar es pequeño pero acogedor.', 5, 1, 3),
('Ambiente romántico y cena a la luz de velas inolvidable. El mole es el mejor que he probado.', 5, 1, 4),
('Buena comida mexicana tradicional, los tacos de carnitas son imperdibles. Precios accesibles.', 4, 1, 5),
('El corte de carne es excelente, el punto de cocción perfecto. Un poco caro pero vale la pena.', 5, 1, 6),
('El jardín principal es hermoso, muy bien cuidado. Perfecto para sentarse a descansar y ver el movimiento de la ciudad.', 5, 1, 7),
('Parque grande con muchas áreas verdes. Ideal para llevar a los niños. Le falta mantenimiento.', 3, 1, 8),
('La parroquia es impresionante, tanto de día como de noche iluminada. No te puedes ir de SMA sin visitarla.', 5, 1, 12),
('Iglesia muy bonita y tranquila. La cúpula es espectacular por dentro.', 4, 1, 13),
('La plaza principal es el corazón de la ciudad. Muy bonita y siempre hay algo que hacer.', 5, 1, 17),
('Excelente ambiente nocturno, la vista de la parroquia iluminada desde la azotea es imperdible.', 5, 1, 22),
('Buena música y ambiente, pero los precios son elevados. Llegar temprano para evitar fila.', 4, 1, 23),
('El mercado de artesanías tiene muy buenos recuerdos. Los precios son justos y hay mucha variedad.', 5, 1, 27),
('Mercado tradicional con frutas y verduras frescas. La experiencia más auténtica de SMA.', 4, 1, 28),
('Buena variedad de productos pero los fines de semana hay mucha gente. Precios competitivos.', 3, 1, 32),
('Tienda con diseños únicos, los muebles son preciosos. Un poco caro pero la calidad lo vale.', 4, 1, 37),
('Los tacos al pastor son los mejores de SMA. La salsa verde pica rico. Precios muy accesibles.', 5, 1, 42),
('Las gorditas de Doña Chely son deliciosas, sobre todo las de chicharrón. Muy recomendadas.', 5, 1, 43),
('Elote cremoso y bien servido. La señora Mary siempre atiende con una sonrisa.', 4, 1, 44),
('Los tamales de mole son increíbles. El atole de fresa también es muy bueno. Llegar temprano porque se acaban.', 5, 1, 45),
('Esquites bien preparados, con mucho queso y crema. El chile en polvo le da el toque perfecto.', 4, 1, 46),
('El DIF hace una gran labor en la comunidad. Siempre atentos a quienes más lo necesitan.', 5, 1, 47),
('La Casa de Allende es un museo muy interesante. La historia de la Independencia está muy bien presentada.', 5, 1, 52),
('El Santuario de Atotonilco es una joya del arte barroco mexicano. Los frescos son impresionantes.', 5, 1, 56),
('La Fábrica la Aurora es un imperdible para los amantes del arte. Galerías increíbles y un café muy bonito.', 5, 1, 59),
('El teatro es hermoso, la acústica es excelente. Asistir a un concierto aquí es una experiencia única.', 5, 1, 62),
('Jardín botánico espectacular. Las cactáceas son impresionantes y los senderos muy bonitos.', 5, 1, 63),
('El café es delicioso, el ambiente muy acogedor. Perfecto para empezar el día.', 5, 1, 68),
('Café de especialidad de muy buena calidad. El latte art es hermoso y el sabor excepcional.', 5, 1, 69),
('El tour caminando por el centro es muy informativo. El guía sabía mucho de historia y contaba anécdotas interesantes.', 5, 1, 74),
('Los viñedos son preciosos y el vino es de buena calidad. La degustación está bien organizada.', 4, 1, 75),
('Volar en globo sobre SMA es una experiencia mágica. Las vistas del amanecer son inolvidables.', 5, 1, 76),
('Guanajuato es una ciudad hermosa y el tour está muy bien organizado. El tiempo justo para ver lo principal.', 4, 1, 77),
('El Santuario de Atotonilco es impresionante. El guía explicó muy bien la historia de los murales.', 5, 1, 78),
('Las clases de cocina son divertidas y aprendes mucho. Los moles que preparamos estaban deliciosos.', 5, 1, 79),
('El jardín botánico de cactáceas tiene una colección impresionante. Muy recomendado para los amantes de la naturaleza.', 4, 1, 66),
('La azotea del bar tiene las mejores vistas de la ciudad. Los cocteles son originales y bien preparados.', 5, 1, 71),
('Museo pequeño pero muy interesante. Las máscaras son increíbles y hay mucha variedad de todo México.', 4, 1, 58),
('El Parque Juárez es un buen lugar para correr por la mañana. Está bien cuidado y es seguro.', 4, 1, 65);

-- ============================================================
-- LUGARES ADICIONALES (10+)
-- ============================================================

-- ==================== PATRIMONIOS Y SITIOS HISTÓRICOS (CAT. 11) ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Cañada de la Virgen', 'S/N', 'S/N', 'Zona Rural', '37850'),
('Canal', 'S/N', '6', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Cañada de la Virgen', 'Zona arqueológica prehispánica a 20 km de San Miguel. Conjunto de pirámides y plazas de la cultura otomí construidas en el siglo IX. Cuenta con centro de interpretación y visitas guiadas por especialistas.', 'https://cañadadelavirgen.mx', '4151523806', '10:00:00', '16:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 11, 2, 80, TRUE),
('Casa del Mayorazgo de la Canal', 'Casa señorial del siglo XVIII considerada uno de los edificios civiles más importantes de la ciudad. Conserva su fachada original, un espléndido patio interior y salones con mobiliario de época.', NULL, '4151523807', '10:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 11, 2, 81, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(80, 'https://images.unsplash.com/photo-1598977123118-4e30ba3c4f5b?w=800&h=600&fit=crop'),
(81, 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914?w=800&h=600&fit=crop');

-- ==================== ARTE Y CULTURA (CAT. 12) ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Cuna de Allende', 'S/N', '9', 'Centro', '37700'),
('Insurgentes', 'S/N', '25', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Museo de la Esmeralda', 'Museo dedicado a la esmeralda y la joyería mexicana. Alberga una colección de gemas, minerales y piezas de orfebrería con talleres interactivos para visitantes de todas las edades.', NULL, '4151523857', '11:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 12, 2, 82, TRUE),
('Biblioteca Pública de San Miguel', 'Institución cultural fundada por voluntarios que ofrece más de 40,000 libros en español e inglés. Organiza eventos, talleres, conciertos y exposiciones, además de contar con un patio encantador.', 'https://bibliotecasma.com', '4151523858', '09:00:00', '18:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado"]', 12, 2, 83, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(82, 'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?w=800&h=600&fit=crop'),
(83, 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=800&h=600&fit=crop');

-- ==================== RESTAURANTES (CAT. 1) ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Mesones', 'S/N', '70', 'Centro', '37700'),
('Sollano', 'S/N', '17', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('La Grotta', 'Restaurante italiano con más de 40 años de tradición en San Miguel. Especialidad en pastas frescas, pizzas al horno de leña y vinos italianos, servidos en un patio colonial con chimenea.', 'https://lagrotta.mx', '4151523357', '13:00:00', '23:00:00', '["Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 1, 2, 84, TRUE),
('Panio', 'Panadería artesanal de inspiración europea famosa por sus croissants, panes de masa madre y repostería. Su café de especialidad y su ambiente acogedor lo convierten en parada obligada.', 'https://panio.mx', '4151523358', '08:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 1, 2, 85, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(84, 'https://images.unsplash.com/photo-1544148103-0773bf10d330?w=800&h=600&fit=crop'),
(85, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=800&h=600&fit=crop');

-- ==================== PARQUES (CAT. 2) ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Zona Rural', 'S/N', 'KM 9', 'Taboada', '37800'),
('Camino a la Presa', 'S/N', 'S/N', 'Zona Rural', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('La Gruta Spa', 'Balneario de aguas termales al aire libre rodeado de jardines y vegetación. Cuenta con albercas de temperatura natural, áreas de descanso, restaurante y servicio de masajes.', 'https://lagrutaspa.mx', '4151523406', '09:00:00', '19:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 2, 2, 86, TRUE),
('El Mirador de la Cañada', 'Mirador natural con vistas panorámicas del centro histórico, la presa y el cañón del río Laja. Lugar ideal para ver el atardecer y tomar fotografías del paisaje sanmiguelense.', NULL, '4151523407', '07:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 2, 2, 87, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(86, 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?w=800&h=600&fit=crop'),
(87, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop');

-- ==================== BEBIDAS (CAT. 14) ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Calle de Allende', 'S/N', '12', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Casa Dragones Tasting Room', 'Sala de degustación de tequila de la prestigiosa marca Casa Dragones. Catas guiadas, maridaje con chocolate gourmet y una experiencia sensorial en un espacio colonial restaurado.', 'https://casadragones.com', '4151523957', '11:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 14, 2, 88, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(88, 'https://images.unsplash.com/photo-1569529465841-dfecdab7503b?w=800&h=600&fit=crop');

-- ==================== MERCADOS (CAT. 6) ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Plaza de la Soledad', 'S/N', 'S/N', 'Centro', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Mercado Orgánico del Sábado', 'Mercado semanal de productos orgánicos y de comercio justo. Frutas y verduras de temporada, pan artesanal, quesos, miel, café de altura y alimentos preparados por productores locales.', NULL, NULL, '08:00:00', '14:00:00', '["Sabado"]', 6, 2, 89, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(89, 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&h=600&fit=crop');

-- ==================== TIENDAS (CAT. 8) ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Ancha de San Antonio', 'S/N', '123', 'San Antonio', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('La Ciudadela', 'Plaza comercial boutique con una cuidada selección de tiendas de diseño, moda, joyería y galerías de arte. Ideal para comprar piezas únicas hechas por artistas y artesanos de la región.', NULL, '4151523656', '10:00:00', '20:00:00', '["Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo"]', 8, 2, 90, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(90, 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=600&fit=crop');

-- ==================== TOURS (CAT. 15) ====================

INSERT INTO Direccion (calle, numero_int, numero_ext, colonia, codigo_postal) VALUES
('Carretera a Querétaro', 'S/N', 'KM 5', 'Zona Rural', '37700');

INSERT INTO Lugar (nombre, descripcion, paginaWeb, num_telefonico, horario_apertura, horario_cierre, dias_servicio, id_categoria, id_usuario, id_direccion, activo)
VALUES
('Rancho Los Labradores', 'Hacienda del siglo XVII convertida en centro de eventos y turismo rural. Ofrece recorridos por sus patios históricos, peñas de música mexicana, gastronomía regional y campamentos.', 'https://rancholoslabradores.mx', '4151523997', '10:00:00', '22:00:00', '["Jueves","Viernes","Sabado","Domingo"]', 15, 2, 91, TRUE);

INSERT INTO Imagenes (id_lugar, url) VALUES
(91, 'https://images.unsplash.com/photo-1540541338287-41700207dee6?w=800&h=600&fit=crop');

-- ============================================================
-- FIN DE SEED DATA: SAN MIGUEL DE ALLENDE
-- ============================================================
