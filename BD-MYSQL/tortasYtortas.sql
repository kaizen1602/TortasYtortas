-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 22-05-2025 a las 16:13:28
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tortasYtortas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adicionales`
--

CREATE TABLE `adicionales` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `adicionales`
--

INSERT INTO `adicionales` (`id`, `producto_id`, `nombre`, `precio`, `precio_venta`, `stock`) VALUES
(1, 1, 'Durazno', 2500.00, 30000.00, 1),
(2, 1, 'Mani', 25000.00, 30000.00, 31),
(3, 1, 'Zanahoria', 20000.00, 5000.00, 30);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono` varchar(250) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `cedula`, `direccion`, `telefono`, `estado`) VALUES
(1, 'kevin', '1108252740', 'manzana 3 limonar', '3046847849', 1),
(2, 'Manuela Penilla', '12345', 'Santa maria', '12312', 1),
(3, 'Esteban', '13123', 'San jeronimo', '12312', 1),
(5, 'Derly', '123456', 'tin ', '1312', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_ventas`
--

CREATE TABLE `log_ventas` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `nombre_producto` varchar(100) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `costo_unitario` decimal(10,2) DEFAULT NULL,
  `precio_venta_unitario` decimal(10,2) DEFAULT NULL,
  `descuento` decimal(10,2) DEFAULT 0.00,
  `precio_adicionales` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT NULL,
  `diferencia` decimal(10,2) DEFAULT 0.00,
  `ganancia` decimal(10,2) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `log_ventas`
--

INSERT INTO `log_ventas` (`id`, `pedido_id`, `producto_id`, `nombre_producto`, `cantidad`, `costo_unitario`, `precio_venta_unitario`, `descuento`, `precio_adicionales`, `total`, `diferencia`, `ganancia`, `fecha`) VALUES
(1, 1, 2, 'Torta de maracuya', 1, 20000.00, 40000.00, 0.00, 2500.00, 42500.00, 0.00, 22500.00, '2025-05-17 00:41:06'),
(2, 1, 1, 'Torta de mango', 1, 25000.00, 50000.00, 0.00, 2000.00, 52000.00, 0.00, 27000.00, '2025-05-17 00:41:06'),
(3, 2, 2, 'Torta de maracuya', 1, 20000.00, 40000.00, 0.00, 2500.00, 42500.00, 0.00, 22500.00, '2025-05-17 00:50:49'),
(4, 2, 1, 'Torta de mango', 2, 25000.00, 50000.00, 0.00, 2000.00, 102000.00, 0.00, 52000.00, '2025-05-17 00:50:49'),
(5, 3, 1, 'Torta de mango', 4, 25000.00, 50000.00, 0.00, 2000.00, 202000.00, 0.00, 102000.00, '2025-05-17 10:33:04'),
(6, 3, 2, 'Torta de maracuya', 80, 20000.00, 40000.00, 0.00, 0.00, 3200000.00, 0.00, 1600000.00, '2025-05-17 10:33:04'),
(7, 4, 1, 'Torta de mango', 1, 25000.00, 50000.00, 0.00, 2000.00, 52000.00, 0.00, 27000.00, '2025-05-17 10:45:46'),
(8, 5, 1, 'Torta de mango', 1, 25000.00, 50000.00, 0.00, 2500.00, 52500.00, 0.00, 27500.00, '2025-05-17 12:45:52'),
(9, 6, 2, 'Torta de maracuya', 8, 20000.00, 40000.00, 0.00, 2000.00, 322000.00, 0.00, 162000.00, '2025-05-18 13:14:07'),
(10, 7, 1, 'Torta de mango', 10, 25000.00, 50000.00, 0.00, 0.00, 500000.00, 0.00, 250000.00, '2025-05-19 17:38:46'),
(11, 7, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 0.00, 80000.00, 0.00, 40000.00, '2025-05-19 17:38:46'),
(12, 8, 2, 'Torta de maracuya', 1, 20000.00, 40000.00, 0.00, 0.00, 40000.00, 0.00, 20000.00, '2025-05-20 15:42:29'),
(13, 9, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 0.00, 80000.00, 0.00, 40000.00, '2025-05-20 15:43:26'),
(14, 10, 1, 'Torta de mango', 1, 25000.00, 50000.00, 0.00, 0.00, 50000.00, 0.00, 25000.00, '2025-05-20 16:10:34'),
(15, 11, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 0.00, 80000.00, 5000.00, 40000.00, '2025-05-20 16:19:00'),
(16, 12, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 0.00, 80000.00, 5000.00, 40000.00, '2025-05-20 16:19:46'),
(17, 13, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 0.00, 80000.00, 80000.00, 40000.00, '2025-05-20 17:15:03'),
(18, 14, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 0.00, 80000.00, 80000.00, 40000.00, '2025-05-20 21:15:53'),
(19, 15, 1, 'Torta de mango', 78, 25000.00, 50000.00, 0.00, 0.00, 3900000.00, 3900000.00, 1950000.00, '2025-05-20 21:18:47'),
(20, 16, 2, 'Torta de maracuya', 8, 20000.00, 40000.00, 0.00, 0.00, 320000.00, 320000.00, 160000.00, '2025-05-20 23:45:32'),
(21, 17, 2, 'Torta de maracuya', 3, 20000.00, 40000.00, 0.00, 0.00, 120000.00, 90000.00, 60000.00, '2025-05-20 23:52:18'),
(22, 18, 2, 'Torta de maracuya', 3, 20000.00, 40000.00, 0.00, 0.00, 120000.00, -80000.00, 60000.00, '2025-05-20 23:53:17'),
(23, 18, 1, 'Torta de mango', 2, 25000.00, 50000.00, 0.00, 0.00, 100000.00, -100000.00, 50000.00, '2025-05-20 23:53:17'),
(24, 19, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 0.00, 80000.00, 5000.00, 40000.00, '2025-05-20 23:55:29'),
(25, 20, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 2500.00, 82500.00, -2500.00, 42500.00, '2025-05-21 00:01:12'),
(26, 21, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 20000.00, 100000.00, 10000.00, 60000.00, '2025-05-21 00:46:45'),
(27, 22, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 20000.00, 100000.00, 15000.00, 60000.00, '2025-05-21 00:51:52'),
(28, 23, 1, 'Torta de mango', 2, 25000.00, 50000.00, 0.00, 20000.00, 120000.00, 10000.00, 70000.00, '2025-05-21 00:59:00'),
(29, 24, 2, 'Torta de maracuya', 2, 20000.00, 40000.00, 0.00, 20000.00, 100000.00, 12000.00, 60000.00, '2025-05-21 23:57:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `descuento` decimal(10,2) DEFAULT 0.00,
  `total_pagado` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `cliente_id`, `estado`, `total`, `fecha`, `descuento`, `total_pagado`) VALUES
(1, 2, 1, 94500.00, '2025-05-17 17:41:00', 0.00, 0.00),
(2, 1, 1, 144500.00, '2025-05-17 17:50:00', 0.00, 0.00),
(3, 2, 1, 3402000.00, '2025-05-18 03:33:00', 0.00, 0.00),
(4, 2, 1, 52000.00, '2025-05-17 22:45:46', 0.00, 0.00),
(5, 3, 1, 52500.00, '2025-05-18 00:45:52', 0.00, 0.00),
(6, 3, 1, 322000.00, '2025-05-19 01:14:07', 0.00, 0.00),
(7, 5, 1, 580000.00, '2025-05-20 05:38:46', 0.00, 0.00),
(8, 3, 1, 37975.00, '2025-05-21 03:42:29', 0.00, 0.00),
(9, 1, 1, 77975.00, '2025-05-21 03:43:26', 0.00, 0.00),
(10, 5, 1, 50000.00, '2025-05-21 02:10:00', 0.00, 45000.00),
(11, 5, 1, 80000.00, '2025-05-21 02:18:00', 0.00, 75000.00),
(12, 5, 1, 80000.00, '2025-05-21 02:19:00', 0.00, 75000.00),
(13, 5, 1, 80000.00, '2025-05-21 03:14:00', 5.00, 0.00),
(14, 2, 1, 80000.00, '2025-05-21 07:14:00', 2.00, 0.00),
(15, 2, 1, 3900000.00, '2025-05-21 07:18:00', 0.00, 0.00),
(16, 5, 1, 140000.00, '2025-05-22 14:45:00', 20.00, 0.00),
(17, 3, 1, 120000.00, '2025-05-21 09:52:00', 90.00, 30000.00),
(18, 5, 1, 220000.00, '2025-05-21 09:52:00', 20.00, 200000.00),
(19, 5, 1, 80000.00, '2025-05-21 14:55:00', 5.00, 80000.00),
(20, 3, 1, 320000.00, '2025-05-22 01:00:00', 0.00, 315000.00),
(21, 5, 1, 80000.00, '2025-05-21 10:46:00', 0.00, 90000.00),
(22, 1, 1, 140000.00, '2025-05-22 01:51:00', 5.00, 120000.00),
(23, 3, 1, 110000.00, '2025-05-21 10:58:00', 0.00, 110000.00),
(24, 3, 1, 90000.00, '2025-05-22 09:57:00', 2.00, 88000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_adicionales`
--

CREATE TABLE `pedido_adicionales` (
  `id` int(11) NOT NULL,
  `pedido_producto_id` int(11) DEFAULT NULL,
  `adicional_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_adicionales`
--

INSERT INTO `pedido_adicionales` (`id`, `pedido_producto_id`, `adicional_id`) VALUES
(5, 5, 1),
(6, 6, 2),
(7, 7, 2),
(9, 10, 2),
(10, 12, 2),
(11, 13, 1),
(12, 14, 2),
(14, 34, 3),
(17, 37, 3),
(19, 39, 3),
(27, 45, 1),
(28, 45, 2),
(29, 45, 3),
(30, 46, 3),
(31, 47, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_productos`
--

CREATE TABLE `pedido_productos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(5,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_productos`
--

INSERT INTO `pedido_productos` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `descuento`, `subtotal`) VALUES
(5, 2, 2, 1, 40000.00, 0.00, 42500.00),
(6, 2, 1, 2, 50000.00, 0.00, 104000.00),
(7, 1, 1, 1, 50000.00, 0.00, 52000.00),
(10, 3, 1, 1, 50000.00, 0.00, 52000.00),
(11, 3, 2, 80, 40000.00, 0.00, 3200000.00),
(12, 4, 1, 1, 50000.00, 0.00, 52000.00),
(13, 5, 1, 1, 50000.00, 0.00, 52500.00),
(14, 6, 2, 8, 40000.00, 0.00, 336000.00),
(15, 7, 1, 10, 50000.00, 0.00, 500000.00),
(16, 7, 2, 2, 40000.00, 0.00, 80000.00),
(17, 8, 2, 1, 40000.00, 0.00, 40000.00),
(18, 9, 2, 2, 40000.00, 0.00, 80000.00),
(19, 10, 1, 1, 50000.00, 0.00, 50000.00),
(20, 11, 2, 2, 40000.00, 0.00, 80000.00),
(21, 12, 2, 2, 40000.00, 0.00, 80000.00),
(22, 13, 2, 2, 40000.00, 0.00, 80000.00),
(23, 14, 2, 2, 40000.00, 0.00, 80000.00),
(24, 15, 1, 78, 50000.00, 0.00, 3900000.00),
(26, 16, 2, 1, 40000.00, 0.00, 40000.00),
(27, 16, 1, 2, 50000.00, 0.00, 100000.00),
(28, 17, 2, 3, 40000.00, 0.00, 120000.00),
(29, 18, 2, 3, 40000.00, 0.00, 120000.00),
(30, 18, 1, 2, 50000.00, 0.00, 100000.00),
(32, 19, 2, 2, 40000.00, 0.00, 80000.00),
(34, 21, 2, 2, 40000.00, 0.00, 120000.00),
(37, 23, 1, 2, 50000.00, 0.00, 140000.00),
(39, 22, 2, 2, 40000.00, 0.00, 120000.00),
(40, 22, 1, 1, 50000.00, 0.00, 50000.00),
(45, 20, 2, 2, 40000.00, 0.00, 175000.00),
(46, 20, 1, 2, 50000.00, 0.00, 140000.00),
(47, 24, 2, 2, 40000.00, 0.00, 120000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(5,2) DEFAULT 0.00,
  `stock` int(11) DEFAULT 0,
  `estado` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio_base`, `precio_venta`, `descuento`, `stock`, `estado`) VALUES
(1, 'Torta de mango', 25000.00, 50000.00, 0.00, 91, 1),
(2, 'Torta de maracuya', 20000.00, 40000.00, 0.00, 780, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `created_at`) VALUES
(1, 'Kevin david ocampo', 'kevin@gmail.com', '$2y$10$A1Umc25nBc/a9RtJosPGaui7Ye3v6Qig9ziW2FgRxQyDo0kYqN6rK', '2025-05-22 04:06:41'),
(2, 'esteban', 'esteban@gmail.com', '$2y$10$lTq1dK.GfmYPHlu/VYROuu.Oew1A3YaUvgkNxcgNhvmvfPrFO.sO6', '2025-05-22 04:41:36');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adicionales`
--
ALTER TABLE `adicionales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`);

--
-- Indices de la tabla `log_ventas`
--
ALTER TABLE `log_ventas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `pedido_adicionales`
--
ALTER TABLE `pedido_adicionales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_producto_id` (`pedido_producto_id`),
  ADD KEY `adicional_id` (`adicional_id`);

--
-- Indices de la tabla `pedido_productos`
--
ALTER TABLE `pedido_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `adicionales`
--
ALTER TABLE `adicionales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `log_ventas`
--
ALTER TABLE `log_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `pedido_adicionales`
--
ALTER TABLE `pedido_adicionales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `pedido_productos`
--
ALTER TABLE `pedido_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `adicionales`
--
ALTER TABLE `adicionales`
  ADD CONSTRAINT `adicionales_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `pedido_adicionales`
--
ALTER TABLE `pedido_adicionales`
  ADD CONSTRAINT `pedido_adicionales_ibfk_1` FOREIGN KEY (`pedido_producto_id`) REFERENCES `pedido_productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_adicionales_ibfk_2` FOREIGN KEY (`adicional_id`) REFERENCES `adicionales` (`id`);

--
-- Filtros para la tabla `pedido_productos`
--
ALTER TABLE `pedido_productos`
  ADD CONSTRAINT `pedido_productos_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_productos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
