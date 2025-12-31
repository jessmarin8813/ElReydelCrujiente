-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-12-2025 a las 10:50:53
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `elreydelcrujiente`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `combos`
--

CREATE TABLE `combos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `combos`
--

INSERT INTO `combos` (`id`, `nombre`, `precio`, `estado`) VALUES
(7, 'Uno', 8.75, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `combo_productos`
--

CREATE TABLE `combo_productos` (
  `id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `combo_productos`
--

INSERT INTO `combo_productos` (`id`, `combo_id`, `producto_id`, `cantidad`) VALUES
(28, 7, 1, 0.15),
(29, 7, 7, 0.10),
(30, 7, 9, 1.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) DEFAULT NULL,
  `estado` enum('libre','ocupada') DEFAULT 'libre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `nombre`, `estado`) VALUES
(1, '1', 'libre'),
(2, '2', 'libre'),
(3, '3', 'libre'),
(4, '4', 'libre');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `metodo` enum('efectivo','tarjeta','divisas','pagomovil') DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `moneda` varchar(3) NOT NULL DEFAULT 'bs',
  `monto_usd` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tasa_usada` decimal(12,6) NOT NULL DEFAULT 0.000000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `pedido_id`, `metodo`, `monto`, `fecha`, `moneda`, `monto_usd`, `tasa_usada`) VALUES
(1, 1, 'tarjeta', 5890.00, '2025-11-14 12:20:21', 'usd', 5890.00, 1.000000),
(2, 3, 'pagomovil', 3478.00, '2025-11-14 12:51:53', 'bs', 14.81, 234.870000),
(3, 4, 'pagomovil', 3112.03, '2025-11-14 13:07:38', 'bs', 13.25, 234.870000),
(4, 2, 'pagomovil', 4700.92, '2025-11-14 13:07:46', 'bs', 20.01, 234.870000),
(5, 5, 'tarjeta', 6354.86, '2025-11-15 12:44:57', 'usd', 6354.86, 1.000000),
(6, 6, 'pagomovil', 11552.00, '2025-11-15 12:45:13', 'bs', 48.85, 236.460000),
(7, 8, 'tarjeta', 4442.00, '2025-11-15 13:14:53', 'usd', 4442.00, 1.000000),
(8, 7, 'tarjeta', 4960.00, '2025-11-15 13:41:57', 'usd', 4960.00, 1.000000),
(9, 9, 'tarjeta', 12695.00, '2025-11-16 13:38:14', 'usd', 12695.00, 1.000000),
(10, 10, 'efectivo', 950.00, '2025-11-16 13:38:27', 'bs', 4.02, 236.460000),
(11, 11, 'pagomovil', 6567.00, '2025-11-22 12:04:45', 'bs', 27.01, 243.110000),
(12, 13, 'pagomovil', 4498.00, '2025-11-22 12:05:01', 'bs', 18.50, 243.110000),
(13, 14, 'pagomovil', 633.00, '2025-11-22 13:16:47', 'bs', 2.60, 243.110000),
(14, 19, 'pagomovil', 6564.00, '2025-11-22 15:16:30', 'bs', 27.00, 243.110000),
(15, 15, 'pagomovil', 1824.00, '2025-11-22 15:16:44', 'bs', 7.50, 243.110000),
(16, 18, 'pagomovil', 4255.00, '2025-11-22 15:17:26', 'bs', 17.50, 243.110000),
(17, 20, 'tarjeta', 2254.00, '2025-11-22 17:00:59', 'usd', 2254.00, 1.000000),
(18, 21, 'pagomovil', 365.00, '2025-11-22 17:13:22', 'bs', 1.50, 243.110000),
(19, 16, 'pagomovil', 6000.00, '2025-11-22 17:58:48', 'bs', 24.68, 243.110000),
(20, 16, 'efectivo', 2607.00, '2025-11-22 19:48:07', 'bs', 10.72, 243.110000),
(21, 22, 'tarjeta', 2539.00, '2025-11-23 13:46:11', 'usd', 2539.00, 1.000000),
(22, 23, 'tarjeta', 8175.00, '2025-11-23 14:05:49', 'usd', 8175.00, 1.000000),
(23, 25, 'tarjeta', 1505.00, '2025-11-28 14:25:59', 'usd', 1505.00, 1.000000),
(24, 26, 'efectivo', 492.00, '2025-11-28 14:42:11', 'bs', 2.00, 245.660000),
(25, 29, 'pagomovil', 2184.00, '2025-11-29 13:04:52', 'bs', 8.83, 247.300000),
(26, 30, 'pagomovil', 7438.00, '2025-11-29 17:25:01', 'bs', 30.08, 247.300000),
(27, 28, 'pagomovil', 642.98, '2025-11-29 19:18:08', 'bs', 2.60, 247.300000),
(28, 31, 'tarjeta', 6950.00, '2025-11-30 15:27:02', 'usd', 6950.00, 1.000000),
(29, 32, 'efectivo', 500.00, '2025-11-30 15:27:26', 'bs', 2.02, 247.300000),
(30, 33, 'pagomovil', 1236.50, '2025-12-05 13:04:16', 'bs', 5.00, 247.300000),
(31, 34, 'pagomovil', 7024.22, '2025-12-05 13:29:01', 'bs', 27.56, 254.870000),
(32, 35, 'pagomovil', 1275.00, '2025-12-05 13:29:50', 'bs', 5.00, 254.870000),
(33, 36, 'pagomovil', 4962.00, '2025-12-05 14:52:40', 'bs', 19.47, 254.870000),
(34, 37, 'pagomovil', 510.00, '2025-12-05 19:01:55', 'bs', 2.00, 254.870000),
(35, 38, 'pagomovil', 3869.00, '2025-12-06 11:29:51', 'bs', 15.00, 257.920000),
(36, 39, 'efectivo', 258.00, '2025-12-06 11:31:07', 'bs', 1.00, 257.920000),
(37, 41, 'pagomovil', 5808.00, '2025-12-06 15:09:53', 'bs', 22.52, 257.920000),
(38, 42, 'tarjeta', 4675.00, '2025-12-06 15:10:31', 'usd', 4675.00, 1.000000),
(39, 40, 'pagomovil', 3018.00, '2025-12-06 15:10:55', 'bs', 11.70, 257.920000),
(40, 43, 'tarjeta', 6384.00, '2025-12-06 20:19:22', 'usd', 6384.00, 1.000000),
(41, 44, 'efectivo', 645.00, '2025-12-07 12:24:53', 'bs', 2.50, 257.920000),
(42, 45, 'pagomovil', 3224.00, '2025-12-07 15:28:43', 'bs', 12.50, 257.920000),
(43, 46, 'pagomovil', 2876.00, '2025-12-07 15:29:07', 'bs', 11.15, 257.920000),
(44, 47, 'efectivo', 1400.00, '2025-12-12 12:28:45', 'bs', 5.23, 267.740000),
(45, 48, 'tarjeta', 1875.00, '2025-12-12 15:49:07', 'usd', 1875.00, 1.000000),
(46, 49, 'pagomovil', 4124.00, '2025-12-12 16:10:38', 'bs', 15.40, 267.740000),
(47, 50, 'tarjeta', 1005.00, '2025-12-12 16:21:03', 'usd', 1005.00, 1.000000),
(48, 51, 'pagomovil', 4177.00, '2025-12-13 13:41:10', 'bs', 15.60, 267.740000),
(49, 52, 'tarjeta', 4954.00, '2025-12-13 14:29:16', 'usd', 4954.00, 1.000000),
(50, 53, 'pagomovil', 900.00, '2025-12-14 07:30:03', 'bs', 3.36, 267.740000),
(51, 29, 'tarjeta', 2585.73, '2025-12-26 18:36:51', 'usd', 2585.73, 1.000000),
(52, 34, 'pagomovil', 56.00, '2025-12-26 18:37:08', 'bs', 0.19, 291.350000),
(53, 36, 'pagomovil', 54.00, '2025-12-26 18:37:17', 'bs', 0.19, 291.350000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `mesa_id` int(11) DEFAULT NULL,
  `tipo` enum('en_sitio','para_llevar') DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `metodo_pago` text DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'pendiente',
  `total` decimal(10,2) DEFAULT 0.00,
  `total_usd` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tasa_pedido` decimal(12,6) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `estado_cocina` varchar(20) DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `mesa_id`, `tipo`, `nombre`, `fecha`, `metodo_pago`, `estado`, `total`, `total_usd`, `tasa_pedido`, `fecha_creacion`, `estado_cocina`) VALUES
(1, 1, 'en_sitio', '', '2025-11-14 11:54:32', NULL, 'pagado', 3393.87, 14.45, 234.870000, '2025-11-14 15:54:32', 'pendiente'),
(2, 2, 'en_sitio', '', '2025-11-14 12:17:46', NULL, 'pagado', 4348.62, 18.52, 234.870000, '2025-11-14 16:17:46', 'pendiente'),
(3, 3, 'en_sitio', 'Juan Tamoy', '2025-11-14 12:25:33', NULL, 'pagado', 2866.59, 12.21, 234.870000, '2025-11-14 16:25:33', 'pendiente'),
(4, NULL, 'para_llevar', 'Nene Mecánico', '2025-11-14 12:37:09', NULL, 'pagado', 3112.03, 13.25, 234.870000, '2025-11-14 16:37:09', 'pendiente'),
(5, 1, 'en_sitio', '', '2025-11-15 11:20:04', NULL, 'pagado', 4936.10, 20.88, 236.460000, '2025-11-15 15:20:04', 'pendiente'),
(6, NULL, 'para_llevar', '', '2025-11-15 11:51:24', NULL, 'pagado', 9753.98, 41.25, 236.460000, '2025-11-15 15:51:24', 'pendiente'),
(7, NULL, 'para_llevar', '', '2025-11-15 12:57:17', NULL, 'pagado', 4510.47, 19.08, 236.460000, '2025-11-15 16:57:17', 'pendiente'),
(8, NULL, 'para_llevar', '', '2025-11-15 13:08:26', NULL, 'pagado', 4441.90, 18.79, 236.460000, '2025-11-15 17:08:26', 'pendiente'),
(9, NULL, 'para_llevar', '', '2025-11-16 13:01:13', NULL, 'pagado', 2975.85, 12.58, 236.460000, '2025-11-16 17:01:13', 'pendiente'),
(10, NULL, 'para_llevar', '', '2025-11-16 13:31:23', NULL, 'pagado', 945.84, 4.00, 236.460000, '2025-11-16 17:31:23', 'pendiente'),
(11, NULL, 'para_llevar', '', '2025-11-21 12:34:50', NULL, 'pagado', 6524.81, 27.01, 241.570000, '2025-11-21 16:34:50', 'pendiente'),
(12, NULL, 'para_llevar', 'Yonnier', '2025-11-21 13:19:39', NULL, 'deudor', 362.36, 1.50, 241.570000, '2025-11-21 17:19:39', 'pendiente'),
(13, 3, 'en_sitio', '', '2025-11-22 10:45:39', NULL, 'pagado', 4375.98, 18.00, 243.110000, '2025-11-22 14:45:39', 'pendiente'),
(14, NULL, 'para_llevar', 'Sra. Cachapa', '2025-11-22 12:39:06', NULL, 'pagado', 632.09, 2.60, 243.110000, '2025-11-22 16:39:06', 'pendiente'),
(15, NULL, 'para_llevar', '', '2025-11-22 12:45:26', NULL, 'pagado', 1823.33, 7.50, 243.110000, '2025-11-22 16:45:26', 'pendiente'),
(16, NULL, 'para_llevar', 'Renan', '2025-11-22 13:11:34', NULL, 'pagado', 8119.87, 33.40, 243.110000, '2025-11-22 17:11:34', 'pendiente'),
(17, NULL, 'para_llevar', 'Tío grúa', '2025-11-22 14:44:41', NULL, 'deudor', 5115.03, 21.04, 243.110000, '2025-11-22 18:44:41', 'pendiente'),
(18, NULL, 'para_llevar', 'Seg', '2025-11-22 14:48:36', NULL, 'pagado', 3768.21, 15.50, 243.110000, '2025-11-22 18:48:36', 'pendiente'),
(19, NULL, 'para_llevar', 'Chica fiesta', '2025-11-22 14:49:23', NULL, 'pagado', 6077.75, 25.00, 243.110000, '2025-11-22 18:49:23', 'pendiente'),
(20, NULL, 'para_llevar', 'Ismael', '2025-11-22 16:10:19', NULL, 'pagado', 2253.63, 9.27, 243.110000, '2025-11-22 20:10:19', 'pendiente'),
(21, NULL, 'para_llevar', 'Javier', '2025-11-22 17:12:51', NULL, 'pagado', 364.67, 1.50, 243.110000, '2025-11-22 21:12:51', 'pendiente'),
(22, NULL, 'para_llevar', '', '2025-11-23 13:44:13', NULL, 'pagado', 2370.32, 9.75, 243.110000, '2025-11-23 17:44:13', 'pendiente'),
(23, NULL, 'para_llevar', 'Cruz', '2025-11-23 13:50:18', NULL, 'pagado', 8174.57, 33.63, 243.110000, '2025-11-23 17:50:18', 'pendiente'),
(24, NULL, 'para_llevar', 'Javier-Rene', '2025-11-28 12:59:13', NULL, 'deudor', 368.49, 1.50, 245.660000, '2025-11-28 16:59:13', 'pendiente'),
(25, NULL, 'para_llevar', '', '2025-11-28 14:11:14', NULL, 'pagado', 1504.67, 6.13, 245.660000, '2025-11-28 18:11:14', 'pendiente'),
(26, NULL, 'para_llevar', '', '2025-11-28 14:41:56', NULL, 'pagado', 491.32, 2.00, 245.660000, '2025-11-28 18:41:56', 'pendiente'),
(27, NULL, 'para_llevar', 'Carlos Clima', '2025-11-28 17:56:28', NULL, 'deudor', 5619.47, 22.88, 245.660000, '2025-11-28 21:56:28', 'pendiente'),
(28, NULL, 'para_llevar', '', '2025-11-29 10:33:10', NULL, 'pagado', 638.72, 2.60, 245.660000, '2025-11-29 14:33:10', 'pendiente'),
(29, NULL, 'para_llevar', 'Brasilero', '2025-11-29 12:35:51', NULL, 'pagado', 2183.04, 8.83, 247.300000, '2025-11-29 16:35:51', 'pendiente'),
(30, NULL, 'para_llevar', '', '2025-11-29 17:16:13', NULL, 'pagado', 5830.10, 23.58, 247.300000, '2025-11-29 21:16:13', 'pendiente'),
(31, NULL, 'para_llevar', '', '2025-11-30 14:37:50', NULL, 'pagado', 6021.76, 24.35, 247.300000, '2025-11-30 18:37:50', 'pendiente'),
(32, NULL, 'para_llevar', '', '2025-11-30 15:27:13', NULL, 'pagado', 494.60, 2.00, 247.300000, '2025-11-30 19:27:13', 'pendiente'),
(33, NULL, 'para_llevar', '', '2025-12-05 12:54:13', NULL, 'pagado', 1236.50, 5.00, 247.300000, '2025-12-05 16:54:13', 'pendiente'),
(34, NULL, 'para_llevar', '', '2025-12-05 12:57:48', NULL, 'pagado', 6815.59, 27.56, 247.300000, '2025-12-05 16:57:48', 'pendiente'),
(35, NULL, 'para_llevar', '', '2025-12-05 13:20:27', NULL, 'pagado', 1274.35, 5.00, 254.870000, '2025-12-05 17:20:27', 'pendiente'),
(36, NULL, 'para_llevar', '', '2025-12-05 13:29:28', NULL, 'pagado', 4069.00, 15.97, 254.870000, '2025-12-05 17:29:28', 'pendiente'),
(37, NULL, 'para_llevar', '', '2025-12-05 13:47:41', NULL, 'pagado', 509.74, 2.00, 254.870000, '2025-12-05 17:47:41', 'pendiente'),
(38, NULL, 'para_llevar', '', '2025-12-06 11:29:34', NULL, 'pagado', 3868.80, 15.00, 257.920000, '2025-12-06 15:29:34', 'pendiente'),
(39, NULL, 'para_llevar', '', '2025-12-06 11:30:50', NULL, 'pagado', 257.92, 1.00, 257.920000, '2025-12-06 15:30:50', 'pendiente'),
(40, NULL, 'para_llevar', '', '2025-12-06 12:03:16', NULL, 'pagado', 3017.66, 11.70, 257.920000, '2025-12-06 16:03:16', 'pendiente'),
(41, NULL, 'para_llevar', 'Guaro', '2025-12-06 13:44:32', NULL, 'pagado', 4388.51, 17.02, 257.920000, '2025-12-06 17:44:32', 'pendiente'),
(42, NULL, 'para_llevar', 'Oscar grúa', '2025-12-06 13:52:52', NULL, 'pagado', 2385.76, 9.25, 257.920000, '2025-12-06 17:52:52', 'pendiente'),
(43, NULL, 'para_llevar', 'Gianfranco', '2025-12-06 14:42:43', NULL, 'pagado', 6383.52, 24.75, 257.920000, '2025-12-06 18:42:43', 'pendiente'),
(44, NULL, 'para_llevar', '', '2025-12-07 11:27:55', NULL, 'pagado', 386.88, 1.50, 257.920000, '2025-12-07 15:27:55', 'pendiente'),
(45, NULL, 'para_llevar', 'Chica fiesta', '2025-12-07 13:23:53', NULL, 'pagado', 3224.00, 12.50, 257.920000, '2025-12-07 17:23:53', 'pendiente'),
(46, NULL, 'para_llevar', '', '2025-12-07 15:11:50', NULL, 'pagado', 2488.93, 9.65, 257.920000, '2025-12-07 19:11:50', 'pendiente'),
(47, NULL, 'para_llevar', 'Cintia', '2025-12-12 12:11:54', NULL, 'pagado', 1392.25, 5.20, 267.740000, '2025-12-12 16:11:54', 'pendiente'),
(48, NULL, 'para_llevar', '', '2025-12-12 13:16:30', NULL, 'pagado', 1606.44, 6.00, 267.740000, '2025-12-12 17:16:30', 'pendiente'),
(49, NULL, 'para_llevar', '', '2025-12-12 15:48:32', NULL, 'pagado', 3587.72, 13.40, 267.740000, '2025-12-12 19:48:32', 'pendiente'),
(50, NULL, 'para_llevar', '', '2025-12-12 16:20:51', NULL, 'pagado', 1004.03, 3.75, 267.740000, '2025-12-12 20:20:51', 'pendiente'),
(51, NULL, 'para_llevar', '', '2025-12-13 13:25:10', NULL, 'pagado', 2409.66, 9.00, 267.740000, '2025-12-13 17:25:10', 'pendiente'),
(52, NULL, 'para_llevar', '', '2025-12-13 13:40:29', NULL, 'pagado', 4953.19, 18.50, 267.740000, '2025-12-13 17:40:29', 'pendiente'),
(53, NULL, 'para_llevar', '', '2025-12-13 14:07:35', NULL, 'deudor', 2409.66, 9.00, 267.740000, '2025-12-13 18:07:35', 'pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalles`
--

CREATE TABLE `pedido_detalles` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` decimal(10,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_detalles`
--

INSERT INTO `pedido_detalles` (`id`, `pedido_id`, `producto_id`, `cantidad`) VALUES
(1, 1, 1, 0.515),
(2, 1, 6, 0.200),
(3, 1, 11, 1.000),
(4, 1, 9, 1.000),
(5, 1, 2, 3.000),
(6, 2, 1, 0.205),
(7, 2, 6, 0.210),
(8, 2, 9, 2.000),
(9, 2, 8, 0.130),
(10, 2, 7, 0.100),
(11, 2, 10, 1.000),
(12, 3, 7, 0.080),
(13, 3, 1, 0.245),
(14, 3, 6, 0.160),
(15, 3, 11, 1.000),
(16, 4, 1, 0.310),
(17, 4, 9, 2.000),
(18, 4, 10, 1.000),
(19, 5, 1, 0.515),
(20, 5, 7, 0.200),
(21, 5, 9, 4.000),
(22, 6, 6, 0.510),
(23, 6, 5, 0.260),
(24, 6, 9, 4.000),
(25, 6, 2, 4.000),
(26, 6, 1, 0.500),
(27, 6, 8, 0.250),
(28, 6, 11, 1.000),
(29, 7, 1, 0.475),
(30, 7, 2, 2.000),
(31, 7, 11, 1.000),
(32, 8, 7, 0.095),
(33, 8, 8, 0.275),
(34, 8, 6, 0.250),
(35, 8, 5, 0.255),
(36, 7, 4, 1.000),
(37, 9, 6, 0.145),
(38, 9, 1, 1.290),
(39, 9, 9, 1.000),
(40, 9, 11, 1.000),
(41, 9, 4, 3.000),
(42, 10, 9, 2.000),
(43, 11, 1, 0.500),
(44, 11, 3, 1.000),
(45, 11, 7, 0.190),
(46, 12, 10, 1.000),
(47, 13, 1, 0.520),
(48, 13, 2, 2.000),
(49, 13, 9, 1.000),
(50, 13, 10, 1.000),
(51, 14, 11, 1.000),
(52, 15, 1, 0.300),
(53, 16, 1, 1.000),
(54, 16, 2, 4.000),
(55, 16, 7, 0.080),
(56, 16, 12, 1.000),
(57, 16, 9, 1.000),
(58, 17, 1, 0.410),
(59, 17, 6, 0.230),
(60, 17, 9, 2.000),
(61, 17, 10, 1.000),
(62, 18, 1, 0.500),
(63, 18, 2, 3.000),
(64, 18, 12, 1.000),
(65, 19, 1, 1.000),
(66, 19, 2, 2.000),
(67, 20, 7, 0.130),
(68, 20, 6, 0.190),
(69, 20, 2, 1.000),
(70, 21, 10, 1.000),
(71, 22, 6, 0.280),
(72, 22, 9, 2.000),
(73, 23, 5, 0.500),
(74, 23, 1, 0.645),
(75, 23, 9, 2.000),
(76, 24, 10, 1.000),
(77, 25, 2, 1.000),
(78, 25, 1, 0.165),
(79, 25, 13, 1.000),
(80, 26, 2, 2.000),
(81, 27, 1, 0.675),
(82, 27, 2, 6.000),
(83, 27, 11, 1.000),
(84, 28, 11, 1.000),
(85, 29, 1, 0.265),
(86, 29, 3, 0.250),
(87, 30, 5, 0.725),
(88, 30, 2, 4.000),
(89, 30, 1, 0.260),
(90, 31, 5, 0.300),
(91, 31, 1, 0.370),
(92, 31, 12, 1.000),
(93, 31, 2, 3.000),
(94, 31, 9, 1.000),
(95, 31, 7, 0.125),
(96, 32, 2, 2.000),
(97, 33, 1, 0.200),
(98, 34, 5, 0.500),
(99, 34, 3, 1.000),
(100, 34, 1, 0.150),
(101, 34, 10, 1.000),
(102, 35, 1, 0.200),
(103, 36, 5, 0.265),
(104, 36, 3, 1.000),
(105, 36, 2, 2.000),
(106, 36, 10, 1.000),
(107, 37, 12, 1.000),
(108, 38, 1, 0.400),
(109, 38, 2, 1.000),
(110, 38, 9, 2.000),
(111, 39, 2, 1.000),
(112, 40, 1, 0.200),
(113, 40, 8, 0.050),
(114, 40, 7, 0.050),
(115, 40, 9, 1.000),
(116, 40, 11, 1.000),
(117, 41, 5, 0.200),
(118, 41, 1, 0.305),
(119, 41, 8, 0.070),
(120, 41, 7, 0.055),
(121, 41, 10, 2.000),
(122, 41, 9, 1.000),
(123, 41, 2, 2.000),
(124, 42, 1, 0.505),
(125, 42, 2, 2.000),
(126, 42, 10, 1.000),
(127, 42, 9, 1.000),
(128, 43, 1, 0.770),
(129, 43, 9, 2.000),
(130, 43, 10, 1.000),
(131, 44, 10, 1.000),
(132, 44, 2, 1.000),
(133, 45, 1, 0.500),
(134, 46, 7, 0.055),
(135, 46, 1, 0.240),
(136, 46, 2, 2.000),
(137, 46, 10, 1.000),
(138, 47, 5, 0.100),
(139, 47, 1, 0.100),
(140, 48, 1, 0.200),
(141, 48, 2, 1.000),
(142, 48, 13, 1.000),
(143, 49, 1, 0.200),
(144, 49, 5, 0.200),
(145, 49, 2, 3.000),
(146, 49, 12, 1.000),
(147, 50, 1, 0.150),
(148, 51, 1, 0.200),
(149, 51, 2, 4.000),
(150, 51, 5, 0.200),
(151, 51, 8, 0.100),
(152, 52, 1, 0.500),
(153, 52, 9, 3.000),
(154, 53, 1, 0.125),
(155, 53, 5, 0.125),
(156, 53, 2, 1.000),
(157, 53, 10, 1.000);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `disponible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`, `disponible`) VALUES
(1, 'Chicharrón carnudo', 25.00, 1),
(2, 'Bollos de chicharrón', 1.00, 1),
(3, 'Cachapa con Queso', 9.00, 1),
(4, 'Cachapa sola', 4.50, 1),
(5, 'Lomo de cerdo', 27.00, 1),
(6, 'Costilla de cerdo', 23.00, 1),
(7, 'Chorizo', 30.00, 1),
(8, 'Morcilla', 12.00, 1),
(9, 'Arepas Frita (und)', 2.00, 1),
(10, 'Refresco 1ltr', 1.50, 1),
(11, 'Refresco 2lt', 2.50, 1),
(12, 'Refresco 1.5lts', 2.00, 1),
(13, 'Refresco Lata', 1.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_cambio`
--

CREATE TABLE `tipo_cambio` (
  `id` int(11) NOT NULL,
  `tasa` decimal(10,2) DEFAULT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_cambio`
--

INSERT INTO `tipo_cambio` (`id`, `tasa`, `fecha`) VALUES
(1, 218.17, '2025-10-27 19:15:14'),
(2, 150.00, '2025-10-28 18:05:48'),
(3, 221.74, '2025-10-29 18:43:22'),
(4, 223.64, '2025-10-31 09:48:34'),
(5, 223.96, '2025-11-01 16:25:50'),
(6, 228.47, '2025-11-07 10:34:03'),
(7, 231.04, '2025-11-08 16:57:34'),
(8, 240.00, '2025-11-11 10:22:41'),
(9, 231.04, '2025-11-11 10:25:01'),
(10, 240.00, '2025-11-11 11:20:52'),
(11, 231.04, '2025-11-11 11:21:49'),
(12, 234.87, '2025-11-14 10:18:39'),
(13, 236.46, '2025-11-15 10:33:17'),
(14, 237.75, '2025-11-19 13:06:45'),
(15, 241.57, '2025-11-21 09:05:39'),
(16, 243.11, '2025-11-22 10:44:27'),
(17, 245.66, '2025-11-27 18:39:35'),
(18, 245.66, '2025-11-27 18:39:37'),
(19, 245.66, '2025-11-28 10:20:02'),
(20, 247.30, '2025-11-29 10:33:32'),
(21, 254.87, '2025-12-05 13:02:34'),
(22, 247.30, '2025-12-05 13:04:05'),
(23, 254.87, '2025-12-05 13:04:53'),
(24, 257.92, '2025-12-06 11:04:31'),
(25, 265.00, '2025-12-10 19:58:46'),
(26, 267.74, '2025-12-12 11:19:43'),
(27, 291.35, '2025-12-26 15:49:26');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `combos`
--
ALTER TABLE `combos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `combo_productos`
--
ALTER TABLE `combo_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `combo_id` (`combo_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mesa_id` (`mesa_id`);

--
-- Indices de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `pedido_detalles_ibfk_1` (`pedido_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipo_cambio`
--
ALTER TABLE `tipo_cambio`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `combos`
--
ALTER TABLE `combos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `combo_productos`
--
ALTER TABLE `combo_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `tipo_cambio`
--
ALTER TABLE `tipo_cambio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `combo_productos`
--
ALTER TABLE `combo_productos`
  ADD CONSTRAINT `combo_productos_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`),
  ADD CONSTRAINT `combo_productos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`mesa_id`) REFERENCES `mesas` (`id`);

--
-- Filtros para la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD CONSTRAINT `pedido_detalles_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_detalles_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
