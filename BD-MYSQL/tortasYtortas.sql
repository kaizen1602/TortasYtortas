-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 10-06-2025 a las 22:08:10
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
(1, 1, 'mani', 5000.00, 10000.00, 100);

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
(1, 'kevin', '1108252740', 'limonar', '11313131313', 1),
(2, 'Manuela penilla', '131313113', 'Villa real 2', '32131231213', 1);

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
(1, 1, 2, 'Torta de banano', 1, 55000.00, 800000.00, 0.00, 0.00, 800000.00, 798669.00, 745000.00, '2025-06-10 01:04:36'),
(2, 2, 1, 'Torta de maracuya', 100, 80000.00, 100000.00, 0.00, 5000.00, 10005000.00, 5000.00, 2005000.00, '2025-06-10 01:10:20'),
(3, 3, 1, 'Torta de maracuya', 899, 80000.00, 100000.00, 0.00, 0.00, 89900000.00, 0.00, 17980000.00, '2025-06-10 01:19:29'),
(4, 4, 1, 'Torta de maracuya', 1, 80000.00, 100000.00, 0.00, 0.00, 100000.00, 0.00, 20000.00, '2025-06-10 01:21:03'),
(5, 5, 2, 'Torta de banano', 20, 55000.00, 800000.00, 0.00, 0.00, 16000000.00, 15100000.00, 14900000.00, '2025-06-10 01:21:23'),
(6, 6, 2, 'Torta de banano', 10, 55000.00, 800000.00, 0.00, 0.00, 8000000.00, 5676667.00, 7450000.00, '2025-06-10 01:25:23'),
(7, 7, 2, 'Torta de banano', 2, 55000.00, 800000.00, 0.00, 0.00, 1600000.00, 1367577.00, 1490000.00, '2025-06-10 01:25:39'),
(8, 8, 2, 'Torta de banano', 1, 55000.00, 800000.00, 0.00, 0.00, 800000.00, 710010.00, 745000.00, '2025-06-10 01:33:36'),
(9, 9, 2, 'Torta de banano', 3, 55000.00, 800000.00, 0.00, 0.00, 2400000.00, 2398669.00, 2235000.00, '2025-06-10 01:34:13'),
(10, 10, 2, 'Torta de banano', 2, 55000.00, 800000.00, 0.00, 0.00, 1600000.00, 1468669.00, 1490000.00, '2025-06-10 01:39:39'),
(11, 11, 2, 'Torta de banano', 20, 55000.00, 800000.00, 0.00, 0.00, 16000000.00, 15910000.00, 14900000.00, '2025-06-10 01:43:12'),
(12, 12, 2, 'Torta de banano', 10, 55000.00, 800000.00, 0.00, 0.00, 8000000.00, 7900000.00, 7450000.00, '2025-06-10 01:43:43');

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
(1, 1, 1, 800000.00, '2025-06-10 11:04:00', 798.67, 1331.00),
(2, 1, 1, 11000000.00, '2025-06-10 11:09:00', 0.00, 10000000.00),
(3, 1, 1, 89900000.00, '2025-06-10 11:19:00', 0.00, 89900000.00),
(4, 1, 1, 100000.00, '2025-06-10 11:20:00', 0.00, 100000.00),
(5, 1, 1, 16000000.00, '2025-06-10 11:21:00', 0.00, 900000.00),
(6, 1, 1, 8000000.00, '2025-06-10 11:25:00', 0.00, 2323333.00),
(7, 1, 1, 1600000.00, '2025-06-10 11:25:00', 0.00, 232423.00),
(8, 1, 1, 800000.00, '2025-06-10 11:33:00', 710.01, 89990.00),
(9, 1, 1, 2400000.00, '2025-06-10 11:34:00', 0.00, 1331.00),
(10, 1, 1, 1600000.00, '2025-06-10 11:39:00', 0.00, 131331.00),
(11, 1, 1, 16000000.00, '2025-06-10 11:42:00', 0.00, 90000.00),
(12, 1, 1, 19000000.00, '2025-06-11 02:43:00', 0.00, 10000000.00);

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
(1, 2, 1);

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
(1, 1, 2, 1, 800000.00, 0.00, 800000.00),
(2, 2, 1, 100, 100000.00, 0.00, 10500000.00),
(3, 3, 1, 899, 100000.00, 0.00, 89900000.00),
(4, 4, 1, 1, 100000.00, 0.00, 100000.00),
(5, 5, 2, 20, 800000.00, 0.00, 16000000.00),
(6, 6, 2, 10, 800000.00, 0.00, 8000000.00),
(7, 7, 2, 2, 800000.00, 0.00, 1600000.00),
(8, 8, 2, 1, 800000.00, 0.00, 800000.00),
(9, 9, 2, 3, 800000.00, 0.00, 2400000.00),
(10, 10, 2, 2, 800000.00, 0.00, 1600000.00),
(11, 11, 2, 20, 800000.00, 0.00, 16000000.00),
(17, 12, 2, 20, 800000.00, 0.00, 16000000.00),
(18, 12, 1, 30, 100000.00, 0.00, 3000000.00);

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
(1, 'Torta de maracuyagood', 80000.00, 100000.00, 0.00, 90, 1),
(2, 'Torta de banano', 55000.00, 800000.00, 0.00, 3090, 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `log_ventas`
--
ALTER TABLE `log_ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `pedido_adicionales`
--
ALTER TABLE `pedido_adicionales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pedido_productos`
--
ALTER TABLE `pedido_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
