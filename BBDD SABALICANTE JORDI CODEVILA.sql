-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql206.infinityfree.com
-- Tiempo de generación: 04-02-2025 a las 13:40:43
-- Versión del servidor: 10.6.19-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_37973440_sabalic`
--
CREATE DATABASE IF NOT EXISTS `if0_37973440_sabalic` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `if0_37973440_sabalic`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carritos`
--

CREATE TABLE `carritos` (
  `id_linea_carrito` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `codigo_producto` varchar(8) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `familias`
--

CREATE TABLE `familias` (
  `id_familia` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `familias`
--

INSERT INTO `familias` (`id_familia`, `nombre`, `descripcion`, `activo`) VALUES
(1, 'MENÃšS PARA EVENTOS', 'MenÃºs para todo tipo de eventos y edades.', 1),
(2, 'PRODUCTOS SALADOS', 'Todo tipo de productos salados, bocadillos, encurtidos, cocas, torreznos.', 1),
(3, 'EMBUTIDOS Y QUESOS', 'Variedades de embutidos y quesos.', 1),
(4, 'PRODUCTOS DULCES', 'Variedades dulces', 1),
(5, 'BEBIDAS', 'Varios tipos de bebidas.', 1),
(6, 'Ultima prueba', 'prueba crear familiaa', 0),
(7, 'prueba', 'ssss', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas_pedido`
--

CREATE TABLE `lineas_pedido` (
  `id_linea` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `codigo_producto` varchar(8) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` float NOT NULL,
  `subtotal` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `lineas_pedido`
--

INSERT INTO `lineas_pedido` (`id_linea`, `id_pedido`, `codigo_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 17, 'MNC02122', 1, 0.3, 0.3),
(2, 17, 'PDC88255', 1, 0.55, 0.55),
(3, 18, 'CCS00802', 1, 0.5, 0.5),
(4, 18, 'MEA00001', 1, 110, 110),
(5, 19, 'MEI00001', 1, 38, 38),
(6, 20, 'PDS56825', 2, 0.55, 1.1),
(7, 20, 'BEE59856', 2, 17.64, 35.28),
(8, 20, 'SPX00005', 12, 1.288, 15.456),
(9, 20, 'MNC02122', 1, 0.3, 0.3),
(10, 20, 'DOT23698', 1, 0.637, 0.637),
(11, 21, 'BEQ36982', 1, 14, 14),
(12, 21, 'BEE59856', 1, 17.64, 17.64),
(13, 22, 'CCM56546', 2, 2.6675, 5.335),
(14, 22, 'CMC01222', 3, 13, 39),
(15, 23, 'CCS00802', 5, 0.5, 2.5),
(16, 23, 'BEQ36982', 1, 14, 14),
(17, 23, 'BSS56456', 3, 24.5, 73.5),
(18, 23, 'BEE59856', 1, 17.64, 17.64),
(19, 24, 'MNC02122', 1, 0.3, 0.3),
(20, 24, 'PDC88255', 1, 0.55, 0.55),
(21, 24, 'MRR10001', 1, 2, 2),
(22, 24, 'MMN00001', 20, 0.75, 15);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `estado` enum('Pendiente','Pagado','Enviado','Cancelado','Entregado') NOT NULL DEFAULT 'Pendiente',
  `forma_pago` enum('Tarjeta','Efectivo','Transferencia') NOT NULL,
  `total` float NOT NULL DEFAULT 0,
  `recogida_local` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `fecha`, `estado`, `forma_pago`, `total`, `recogida_local`) VALUES
(17, 11, '2025-02-03 13:48:29', 'Pagado', 'Transferencia', 5.85, 0),
(18, 11, '2025-02-03 13:49:13', 'Enviado', 'Tarjeta', 115.5, 0),
(19, 11, '2025-02-03 13:51:30', 'Entregado', 'Efectivo', 38, 1),
(20, 10, '2025-02-03 13:55:16', 'Pendiente', 'Transferencia', 57.773, 0),
(21, 10, '2025-02-03 13:55:40', 'Cancelado', 'Transferencia', 31.64, 1),
(22, 12, '2025-02-04 04:34:28', 'Pagado', 'Tarjeta', 49.335, 0),
(23, 9, '2025-02-04 10:27:58', 'Pendiente', 'Transferencia', 107.64, 1),
(24, 9, '2025-02-04 10:29:10', 'Pendiente', 'Tarjeta', 22.85, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `codigo` varchar(8) NOT NULL,
  `nombre` varchar(125) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_subfamilia` int(11) NOT NULL,
  `precio` float NOT NULL,
  `imagen` varchar(255) DEFAULT 'sin-imagen.jpg',
  `descuento` float DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `stock` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`codigo`, `nombre`, `descripcion`, `id_subfamilia`, `precio`, `imagen`, `descuento`, `activo`, `stock`) VALUES
('BEE59856', 'Bandeja de Embutidos', 'MorcÃ³n, Butifarra Negra, Longaniza IbÃ©rica, Fuet Extra. Para 6 personas.', 10, 18, 'Captura de pantalla 2025-01-25 202436_398700999.png', 2, 1, 35),
('BEQ36982', 'Bandeja Embutidos y Queso', 'Lomo, Chorizo IbÃ©rico, SalchichÃ³n PayÃ©s, JamÃ³n Serrano, Queso Semicurado. Para 5 personas', 10, 14, 'Captura de pantalla 2025-01-25 200225_450393565.png', 0, 1, 70),
('BQV52651', 'Bandeja Quesos Variados', 'Queso Semicurado Oveja Vega SotuÃ©llamos, Queso de Bola Semi Castillo, Queso Curado La Cueva del Abuelo (D.O). Para 6 personas.', 10, 20, 'Captura de pantalla 2025-01-25 202341_653922267.png', 0, 1, 0),
('BSI69522', 'Bandeja Surtidos IbÃ©ricos', 'Lomo IbÃ©rico de Cebo, JamÃ³n IbÃ©rico Cebo de Campo, Chorizo IbÃ©rico de Bellota, SalchichÃ³n IbÃ©rico de Bellota. Para 6 personas.', 10, 22.5, 'Captura de pantalla 2025-01-25 200549_976740536.png', 12, 1, 78),
('BSS56456', 'Bandeja Surtido Salazones', 'Hueva de Merluza Roja, Mojama Centro Extra, Bonito Seco. Para 6 personas.', 10, 25, 'Captura de pantalla 2025-01-25 202029_140105707.png', 2, 1, 35),
('CAC00022', 'Coca Tomate, AtÃºn, Cebolla y Olivas Negras', 'Son grandes, para 4 personas', 8, 17.9, 'Captura de pantalla 2025-01-25 194926_438114869.png', 14, 1, 100),
('CCB55555', 'Coca Carbonara', 'Son grandes, para 4 personas', 8, 17.9, 'Captura de pantalla 2025-01-25 195237_493038405.png', 0, 1, 44),
('CCM56546', 'Tarrina Coca de mollitas', 'Coca de mollitas crujiente', 8, 2.75, 'Captura de pantalla 2025-01-25 203602_320765489.png', 3, 1, 5443),
('CCS00802', 'Croissant Salado', 'Variedades: JamÃ³n York y Queso, Chistorra, Tortilla de Patata, Salchicha Frankfurt', 6, 0.5, 'Captura de pantalla 2025-01-25 191214_541465013.png', 0, 1, 93),
('CJQ00001', 'Coca Tomate, JamÃ³n York y Queso', 'Es una coca grande 4 personas', 8, 17.9, 'sin-imagen.jpg', 12, 1, 50),
('CMC01222', 'Coca Mollitas Salada con Chocolate', 'Deliciosa coca de chocolate (grande)', 8, 13, 'Captura de pantalla 2025-01-25 195354_142501009.png', 0, 1, 997),
('CMS55555', 'Coca Mollitas Salada', 'Son grandes, para 4 personas', 8, 12, '', 3, 1, 1200),
('CST00211', 'Coca Salchichas, Tomate y Pimiento', 'Para 4 personas', 8, 17.9, 'sin-imagen.jpg', 0, 1, 24),
('DOT23698', 'Mini Donuts Variados', 'Deliciosos, van por unidades.', 12, 0.65, 'Captura de pantalla 2025-01-25 202933_608857478.png', 2, 1, 1189),
('EAT12001', 'Empanada de AtÃºn y Tomate', 'Empanada de AtÃºn y Tomate 3 Und', 6, 1.95, 'Captura de pantalla 2025-01-25 191600_778344263.png', 6, 1, 122),
('JIC00021', 'Plato JamÃ³n IbÃ©rico de Cebo al Corte', '100g aprox', 11, 15, '', 0, 0, 28),
('MEA00001', 'MenÃº Adultos (10 Personas)', '20 Montaditos: 4 Sobrasada Mallorquina 4 Mojama de AtÃºn 4 SalchichÃ³n IbÃ©rico 4 Queso IbÃ©rico 4 AtÃºn Tortilla de Patata (Con o sin cebolla) Ensalada de Tomate Trinchado con SalazÃ³n Bandeja con Diferentes Tipos de Saladitos: 5 Porciones Coca Mollitas Salada 6 Mini Croissant Salado Surtido 4 Mini Rejitas Surtidas 4 Mini Quiches Surtidos Coca Entera (2 Bandejas - Ingredientes a elegir) 2 Bandejas de Embutidos con Queso', 2, 110, 'Captura de pantalla 2025-01-25 195548_367704094.png', 0, 1, 47),
('MEI00001', 'MenÃº para 10 niÃ±os', '20 Montaditos: 5 JamÃ³n York 4 Queso 3 SalchichÃ³n 3 Chorizo 5 Nocilla Bandeja Saladitos Salados Variados: 8 TriÃ¡ngulos JamÃ³n York y Queso 6 Mini Empanada Tomate y AtÃºn 6 Mini Croissant Salado 10 Porciones Coca Mollitas Salada Bandeja de Pizza JamÃ³n York y Queso (12 Mini Porciones)', 1, 40, 'Captura de pantalla 2025-01-25 195601_429664319.png', 5, 1, 18),
('MMN00001', 'Montaditos NiÃ±os', 'Variedades: JamÃ³n York, Queso, Chorizo, SalchichÃ³n, Nocilla (precio por unidad)', 5, 0.75, 'Captura de pantalla 2025-01-25 185318_197819211.png', 0, 1, 1948),
('MNC02122', 'Mini Napolitana de Chocolate', 'Bocadito para endulzar el dÃ­a. Va por unidades.', 12, 0.3, '', 0, 1, 590),
('MQQ12000', 'Mini Quiches', 'Variedades: Espinacas, Pollo y JamÃ³n y Queso', 6, 0.65, 'Captura de pantalla 2025-01-25 192106_819859390.png', 0, 1, 5000),
('MRR10001', 'Montaditos RÃºsticos', 'Variedades: JamÃ³n Serrano de Reserva, SalchichÃ³n IbÃ©rico de Bellota, Chorizo IbÃ©rico de Bellota, Lomo IbÃ©rico de Cebo, Queso Semicurado, Queso Curado, Sobrasada Mallorquina, AtÃºn, Butifarra, MorcÃ³n, Mojama Extra de AtÃºn, Hueva de Merluza, Anchoas del CantÃ¡brico y SalmÃ³n Ahumado', 5, 2, 'Captura de pantalla 2025-01-25 190211_871724318.png', 0, 1, 998),
('PDC88255', 'POPS DOTS TRIPLE CHOCOLATE', 'BOLITA DE DONUTS RELLENA DE CACAO INTENSO CON CRUJIENTE COBERTURA DE BOMBON CON SUS PERLAS DE CHOCO BLANCO, NEGRO Y CASTAÃ‘O. Va por unidades.', 12, 0.55, 'Captura de pantalla 2025-01-25 203047_820614667.png', 0, 1, 6664),
('PDS56825', 'POPS DOTS STRAWBERRY', 'BOLITA DE DONUTS RELLENA CON MERMELADA DE FRESA Y UNA COBERTURA DE BOMBÃ“N ROSA Y BOMBÃ“N BLANCO. Va por unidades.', 12, 0.55, 'Captura de pantalla 2025-01-25 203140_143395147.png', 0, 1, 5533),
('PRU12312', 'PRUEBAAA', 'ASDDASDASD', 15, 12, '', 4, 0, 4),
('PTI12502', 'Plato de Torreznos IbÃ©ricos', 'Deliciosos torreznos crujientes', 7, 2.5, 'Captura de pantalla 2025-01-25 193037_203741315.png', 0, 1, 57),
('PYQ00016', 'Pizza JamÃ³n York y Queso', 'Pizza JamÃ³n York y Queso redonda', 6, 2.3, 'Captura de pantalla 2025-01-25 191738_366327859.png', 0, 1, 100),
('RJH99999', 'Rejitas de Hojaldre', 'Variedades: Tomate y AtÃºn con Aceitunas, Pollo y Bacon con Bechamel, JamÃ³n con Queso Curado', 7, 0.55, 'Captura de pantalla 2025-01-25 193948_739705946.png', 0, 1, 0),
('SPX00005', 'Saquitos de Pollo', 'Saquitos de Pollo', 6, 1.4, 'Captura de pantalla 2025-01-25 192454_870784918.png', 8, 1, 88),
('TCC04511', 'Tarrina coca de mollitas chocolate', 'La mejor coca de mollitas crujiente de chocolate', 8, 2.85, 'Captura de pantalla 2025-01-25 203550_203392408.png', 3, 1, 12222),
('TJQ02401', 'TriÃ¡ngulo JamÃ³n York y Queso', 'TriÃ¡ngulo JamÃ³n York y Queso 4 Und', 6, 2.95, 'Captura de pantalla 2025-01-25 191347_875402767.png', 6, 1, 29),
('TTP00012', 'Tortilla de Patatas', 'Tortilla de Patatas', 6, 7.95, 'Captura de pantalla 2025-01-25 192731_153059114.png', 8, 1, 98);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subfamilias`
--

CREATE TABLE `subfamilias` (
  `id_subfamilia` int(11) NOT NULL,
  `id_familia` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `subfamilias`
--

INSERT INTO `subfamilias` (`id_subfamilia`, `id_familia`, `nombre`, `descripcion`, `activo`) VALUES
(1, 1, 'MenÃº Evento NiÃ±os', 'MenÃºs para eventos infantiles', 1),
(2, 1, 'MenÃº Evento Adultos', 'MenÃºs para eventos para adultos', 1),
(5, 2, 'Montaditos', 'Montaditos Variados', 1),
(6, 2, 'Aperitivos y Tapas', 'Variedades de aperitivos y tapas', 1),
(7, 2, 'Otros Aperitivos Salados', 'MÃ¡s aperitivos salados', 1),
(8, 2, 'Cocas', 'Cocas de todo tipo (incluidas las dulces)', 1),
(10, 3, 'Bandejas de Embutidos, Quesos y Salazones', 'Variedades de embutidos quesos y salazones', 1),
(11, 3, 'JamÃ³n IbÃ©rico', 'Variedad de jamones serranos', 1),
(12, 4, 'Dulces Individuales', 'Variedades de  dulces', 1),
(13, 6, 'subfamilia pruebaa', 'ultima prueba subfamilia', 0),
(14, 5, 'Prueba', 'Ppp', 0),
(15, 7, 'pruebas', 'asdasd', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(125) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `localidad` varchar(50) NOT NULL,
  `provincia` varchar(50) NOT NULL,
  `cp` char(5) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(125) NOT NULL,
  `rol` enum('Usuario','Empleado','Administrador','Contable') NOT NULL DEFAULT 'Usuario',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `clave`, `dni`, `nombre`, `apellidos`, `direccion`, `localidad`, `provincia`, `cp`, `telefono`, `email`, `rol`, `activo`) VALUES
(1, '$2y$10$Qmes.47FORmVnlxURAp18e6PjlziryW.gk2DH5OHJ4UMtV3v1DRGy', '74234076N', 'Jordi', 'Codevila de LeÃ³n', 'Presbitero Aracil n4', 'San Vicente del Raspeig', 'Alicante', '03690', '610057136', 'Jordicodeviladl@gmail.com', 'Administrador', 1),
(2, '$2y$10$Miev9rCGA98oMgpWHioJVesQY.HSR6pah6zgCcZxiDR.gXD2sJWny', '88460038Z', 'Paloma', 'Moratalla', 'Calle de la administradora', 'San Vicente del Raspeig', 'Alicante', '03690', '610057136', 'admi@sabalicante.com', 'Administrador', 1),
(3, '$2y$10$t.wHVFBy5IyG/3EIp1p5FeeUMokhm9HGPmrl21oiMshDQAzNaWoUO', '28249793C', 'Pepito', 'El Contable', 'Casa del contable', 'San Vicente del Raspeig', 'Alicante', '03690', '612257135', 'contable@sabalicante.com', 'Contable', 1),
(4, '$2y$10$4xvKYbqM/NDBe8aEX5mcp.nzQyzCOAd7qlTIaGCxBLMy/GEH9saTK', '87905154M', 'Martin', 'Velez', 'La torre 12', 'Elche', 'Alicante', '03204', '610257136', 'martin@sabalicante.com', 'Empleado', 1),
(5, '$2y$10$Z4YEsgOQpDx9A9pILYkfBe.p4auYYC16fHh9.H1I1TmfG2geCTz/G', '05303239Z', 'Vera', 'GarcÃ­a', 'Lacasitos', 'Alicante', 'Alicante', '03210', '688736154', 'vera@sabalicante.com', 'Empleado', 1),
(6, '$2y$10$BPH3BsMQGGdm8zuHHnmSWeV4TD4seNbTuPKUUEilIG01TVBv2J3sC', '13874014T', 'Lucas', 'Satorre', 'Menganito 12', 'Alicante', 'Alicante', '03122', '655542123', 'lucas@sabalicante.com', 'Empleado', 0),
(7, '$2y$10$vKV2kGZVvSFhOcHBEq.LGus7EmSsebllNVWh7wvIu9PYhlB3bsBby', '46239199E', 'Josefina', 'Sanchez LaTorres', 'Tomas Garcia n12', 'Torrevieja', 'Alicante', '03201', '632245234', 'Josefina09@gmail.com', 'Usuario', 0),
(8, '$2y$10$TDRsuGmqOiMqrU1J.iVIw.C.l9UNMclDbXi/XLjtxOOGjZUyKJ.uq', '73821080G', 'Manolo', 'Pies de gato', 'AlcÃ¡ntara', 'Madrid', 'Madrids', '28001', '632287452', 'manolopiesgato@yahoo.es', 'Usuario', 0),
(9, '$2y$10$c8sLJUV7WIrldiQt8CyhpOTWzNgL/eBNBUHIDkkYRxER1lMZsv4n6', '53301253H', 'Paquit', 'Ramirez', 'asdasdasd', 'asdasdas', 'dasdasd', '03204', '615557136', 'ramirez@gmail.com', 'Usuario', 1),
(10, '$2y$10$oPJNkGPVpM5YGHX8hpTE8uyJbh1Sg4xr.f.oi5MkI.QdHTbcpiROC', '48673889r', 'Ana', 'Vega CalderÃ³n', 'Calle Las Rosas 13', 'San Vicente del Raspeig', 'Alicante', '03690', '600878766', 'anavegaca@hotmail.com', 'Usuario', 1),
(11, '$2y$10$PpPPj7LcHGTiSRby2L6.suWBZz2Rb8RBgGyeVYEysWx5cAB2c7dxK', '13076641Z', 'JosÃ©', 'RamÃ­rez', 'Presbitero Aracil  4', 'San Vicente Del Raspeig', 'Alicante', '03690', '622451398', 'jramirez@hotmail.es', 'Usuario', 1),
(12, '$2y$10$Wo3kzI6Hbnh/ricaco66dufOxpKJf828JQyb9uwuAFW92awzkRGL6', '74241484Z', 'Raul', 'Garcia Lopez', 'Calle Espronceda, 139 4 6', 'Elche', 'Alicante', '03204', '636222513', 'paixop@hotmail.com', 'Usuario', 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD PRIMARY KEY (`id_linea_carrito`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `codigo_producto` (`codigo_producto`);

--
-- Indices de la tabla `familias`
--
ALTER TABLE `familias`
  ADD PRIMARY KEY (`id_familia`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `lineas_pedido`
--
ALTER TABLE `lineas_pedido`
  ADD PRIMARY KEY (`id_linea`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `codigo_producto` (`codigo_producto`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`codigo`),
  ADD KEY `id_subfamilia` (`id_subfamilia`);

--
-- Indices de la tabla `subfamilias`
--
ALTER TABLE `subfamilias`
  ADD PRIMARY KEY (`id_subfamilia`),
  ADD UNIQUE KEY `id_familia` (`id_familia`,`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carritos`
--
ALTER TABLE `carritos`
  MODIFY `id_linea_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT de la tabla `familias`
--
ALTER TABLE `familias`
  MODIFY `id_familia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `lineas_pedido`
--
ALTER TABLE `lineas_pedido`
  MODIFY `id_linea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `subfamilias`
--
ALTER TABLE `subfamilias`
  MODIFY `id_subfamilia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carritos`
--
ALTER TABLE `carritos`
  ADD CONSTRAINT `carritos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carritos_ibfk_2` FOREIGN KEY (`codigo_producto`) REFERENCES `productos` (`codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `lineas_pedido`
--
ALTER TABLE `lineas_pedido`
  ADD CONSTRAINT `lineas_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `lineas_pedido_ibfk_2` FOREIGN KEY (`codigo_producto`) REFERENCES `productos` (`codigo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_subfamilia`) REFERENCES `subfamilias` (`id_subfamilia`) ON DELETE CASCADE;

--
-- Filtros para la tabla `subfamilias`
--
ALTER TABLE `subfamilias`
  ADD CONSTRAINT `subfamilias_ibfk_1` FOREIGN KEY (`id_familia`) REFERENCES `familias` (`id_familia`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
