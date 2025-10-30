-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-10-2025 a las 02:07:21
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cooperativa`
--
CREATE DATABASE IF NOT EXISTS `cooperativa` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `cooperativa`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `aportes`
--

CREATE TABLE `aportes` (
  `id` int(11) NOT NULL,
  `socio_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `aportes`
--

INSERT INTO `aportes` (`id`, `socio_id`, `monto`, `fecha`) VALUES
(1, 1, 100000.00, '2024-07-16'),
(4, 4, 20000.00, '2024-07-04'),
(5, 4, 10000.00, '2024-09-01'),
(6, 1, 100.00, '2025-04-07'),
(7, 1, 1.00, '2025-04-07'),
(10, 4, -10000.00, '2025-04-09'),
(11, 4, -5000.00, '2025-01-30'),
(12, 4, -5000.00, '2025-01-14'),
(13, 4, 20000.00, '2024-12-16'),
(14, 4, -20000.00, '2025-04-23'),
(15, 1, -20000.00, '2025-04-23'),
(16, 6, 50000.00, '2023-12-11'),
(17, 7, 500.00, '2022-07-01'),
(18, 7, 20000.00, '2024-05-15'),
(19, 8, 10000.00, '2024-01-23'),
(20, 8, 40000.00, '2024-04-10'),
(21, 8, -30000.00, '2024-06-12'),
(22, 8, 10000.00, '2024-06-13'),
(23, 9, 10000.00, '2024-01-08'),
(24, 9, 90000.00, '2024-04-27'),
(25, 10, 20000.00, '2024-07-16'),
(26, 13, 5000.00, '2024-01-25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL,
  `socio_id` int(11) DEFAULT NULL,
  `accion` varchar(50) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario` varchar(50) DEFAULT NULL,
  `detalles` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `socio_id`, `accion`, `fecha`, `usuario`, `detalles`) VALUES
(1, 1, 'modificacion', '2025-04-23 20:25:09', 'admin', 'Modificado: nombre=Nicolax Luciano, email=nicolax@gmail.com, cedula=40236184913, direccion=Calle Saona #5, telefono=8094080416'),
(2, 1, 'modificacion', '2025-04-23 20:25:17', 'admin', 'Modificado: nombre=Nicolax Luciano, email=nicolaxblanco@gmail.com, cedula=40236184913, direccion=Calle Saona #5, telefono=8094080416'),
(3, 6, 'modificacion', '2025-04-23 21:26:43', 'admin', 'Modificado: nombre=Miledys Blanco, email=yamilex@gmail.com, cedula=22300000003, direccion=Saona #5, telefono=8099860416'),
(4, 7, 'modificacion', '2025-04-23 21:28:53', 'admin', 'Modificado: nombre=Leandro Aracena, email=leandro.aracena@gmail.com, cedula=00118082700, direccion=Tiradentes #33, telefono=8096717092'),
(5, 7, 'modificacion', '2025-04-23 21:28:58', 'admin', 'Modificado: nombre=Leandro Aracena, email=leandro.aracena@gmail.com, cedula=00118082700, direccion=Tiradentes #33, telefono=8096717092'),
(6, 1, 'pago_registrado', '2025-04-23 21:59:33', 'admin', 'Pago registrado para préstamo #7: Monto=157500, Fecha=2025-05-01'),
(7, 8, 'modificacion', '2025-04-23 22:02:13', 'admin', 'Modificado: nombre=Erick Peña, email=erick@gmail.com, cedula=22300000007, direccion=Tiradentes #33, telefono=8294080416'),
(8, 8, 'modificacion', '2025-04-23 22:05:15', 'admin', 'Modificado: nombre=Erick Peña, email=erickpena@gmail.com, cedula=22300000007, direccion=Tiradentes #33, telefono=8294080416'),
(9, 8, 'modificacion', '2025-04-23 22:05:26', 'admin', 'Modificado: nombre=Erick Peña, email=erick@gmail.com, cedula=22300000007, direccion=Tiradentes #33, telefono=8294080416'),
(10, 8, 'prestamo_aprobado', '2025-04-23 22:27:39', 'admin', 'Préstamo #9 aprobado'),
(11, 7, 'pago_registrado', '2025-04-23 22:28:00', 'admin', 'Pago registrado para préstamo #6: Monto=63000, Fecha=2025-08-14'),
(12, 8, 'pago_registrado', '2025-04-23 22:28:22', 'admin', 'Pago registrado para préstamo #9: Monto=10000, Fecha=2025-05-01'),
(13, 8, 'pago_registrado', '2025-04-23 22:28:49', 'admin', 'Pago registrado para préstamo #9: Monto=53000, Fecha=2025-08-06'),
(14, 7, 'pago_registrado', '2025-04-23 22:30:40', 'admin', 'Pago registrado para préstamo #6: Monto=43401, Fecha=2025-08-21'),
(15, 9, 'modificacion', '2025-04-23 22:49:25', 'admin', 'Modificado: nombre=Ariel Pena Valerio, email=ariel@gmail.com, cedula=40236100000, direccion=Calle Saona #7, telefono=8494080416'),
(16, 9, 'prestamo_aprobado', '2025-04-23 22:50:23', 'admin', 'Préstamo #10 aprobado'),
(17, 9, 'pago_registrado', '2025-04-23 22:50:48', 'admin', 'Pago registrado para préstamo #10: Monto=100000, Fecha=2025-06-30'),
(18, 9, 'pago_registrado', '2025-04-23 22:51:03', 'admin', 'Pago registrado para préstamo #10: Monto=10000, Fecha=2025-08-12'),
(19, 9, 'pago_registrado', '2025-04-23 22:51:30', 'admin', 'Pago registrado para préstamo #10: Monto=100000, Fecha=2025-12-30'),
(20, 9, 'retiro_saldo', '2025-04-23 22:53:31', 'admin', 'Retiro de saldo: Monto=20000, Fecha=2025-08-04, Nuevo Saldo=80000'),
(21, 10, 'registro', '2025-04-30 16:54:02', 'cajera', 'Socio registrado: Daniel Jimenez, Saldo inicial: 40000'),
(22, 10, 'modificacion', '2025-04-30 16:54:19', 'cajera', 'Modificado: nombre=Daniel Jimenez Melo, email=daniel@gmial.com, cedula=22336184913, direccion=Azua, telefono=8290000416'),
(23, 10, 'modificacion', '2025-04-30 16:55:03', 'cajera', 'Modificado: nombre=Daniel Jimenez Melo, email=daniel@gmial.com, cedula=22336184913, direccion=Azua, telefono=8290000416'),
(24, 10, 'modificacion', '2025-04-30 16:59:42', 'cajera', 'Modificado: nombre=Daniel Jimenez Melo, email=daniel@gmail.com, cedula=22336184913, direccion=Azua, telefono=8290000416'),
(25, 10, 'modificacion', '2025-04-30 17:16:01', 'cajera', 'Modificado: nombre=Daniel Jimenez Melo, email=daniel@gmail.com, cedula=22336184914, direccion=Azua, telefono=8290000416'),
(26, 10, 'modificacion', '2025-04-30 17:57:25', 'cajera', 'Modificado: nombre=Daniel Jimenez Melo, email=daniel@gmail.com, cedula=22336184914, direccion=Azua #69, telefono=8290000416'),
(27, 10, 'retiro_saldo', '2025-04-30 17:58:16', 'admin', 'Retiro de saldo: Monto=5000, Fecha=2024-11-06, Nuevo Saldo=55000'),
(28, 10, 'retiro', '2025-04-30 18:17:31', 'cajera', 'Retiro de saldo: 5000, Nuevo saldo: 50000'),
(29, 10, 'solicitud_prestamo', '2025-04-30 18:20:20', 'gerente', 'Préstamo solicitado: Monto=25000, Plazo=12 meses'),
(30, 10, 'solicitud_prestamo', '2025-04-30 18:42:30', 'gerente', 'Préstamo solicitado: Monto=25000, Plazo=12 meses'),
(31, 10, 'solicitud_prestamo', '2025-04-30 18:51:21', 'gerente', 'Préstamo solicitado: Monto=25000, Plazo=12 meses'),
(32, 10, 'aprobado', '2025-04-30 19:16:11', 'gerente', 'Préstamo #13 aprobado'),
(33, 10, 'solicitud_prestamo', '2025-04-30 19:32:26', 'gerente', 'Préstamo solicitado: Monto=25000, Plazo=12 meses'),
(34, 10, 'rechazado', '2025-04-30 19:32:35', 'gerente', 'Préstamo #14 rechazado'),
(35, 10, 'rechazado', '2025-04-30 19:47:09', 'gerente', 'Préstamo #15 rechazado'),
(36, 10, 'rechazado', '2025-04-30 19:47:12', 'gerente', 'Préstamo #16 rechazado'),
(37, 10, 'solicitud_prestamo', '2025-04-30 19:48:44', 'gerente', 'Préstamo solicitado: Monto=100000, Plazo=12 meses'),
(38, 10, 'aprobado', '2025-04-30 19:48:52', 'gerente', 'Préstamo #18 aprobado'),
(39, 10, 'pago_registrado', '2025-04-30 23:17:17', 'cobro', 'Pago registrado para préstamo #18: Monto=12000, Fecha=2024-07-08'),
(40, 11, 'registro', '2025-05-01 12:07:12', 'cajera', 'Socio registrado: Hector Martinez, Saldo inicial: 70000'),
(41, 11, 'solicitud_prestamo', '2025-05-01 12:08:48', 'gerente', 'Préstamo solicitado: Monto=140000, Plazo=24 meses'),
(42, 11, 'aprobado', '2025-05-01 12:09:19', 'gerente', 'Préstamo #19 aprobado'),
(43, 11, 'pago_registrado', '2025-05-01 12:11:18', 'cobro', 'Pago registrado para préstamo #19: Monto=6533.33, Fecha=2024-10-01'),
(44, 11, 'pago_registrado', '2025-05-06 20:40:27', 'cobro', 'Pago registrado para préstamo #19: Monto=50000, Fecha=2025-05-06'),
(45, 11, 'pago_registrado', '2025-05-06 20:40:52', 'cobro', 'Pago registrado para préstamo #19: Monto=80000, Fecha=2025-06-18'),
(46, 11, 'pago_registrado', '2025-05-06 20:41:14', 'cobro', 'Pago registrado para préstamo #19: Monto=15266.67, Fecha=2025-07-17'),
(47, 11, 'pago_registrado', '2025-05-06 20:41:28', 'cobro', 'Pago registrado para préstamo #19: Monto=5000, Fecha=2025-08-14'),
(48, 11, 'prestamo_pagado', '2025-05-06 20:41:28', 'cobro', 'Préstamo #19 pagado completamente.'),
(49, 12, 'registro', '2025-05-07 22:43:19', 'cajera', 'Socio registrado: Jose Amado, Saldo inicial: 5000'),
(50, 13, 'registro', '2025-06-13 12:33:03', 'cajera', 'Socio registrado: Luis Abinader, Saldo inicial: 1000'),
(51, 13, 'modificacion', '2025-06-13 12:33:24', 'cajera', 'Modificado: nombre=Luis Abinaders, email=luis.abinader@cnss.gob.do, cedula=40236184999, direccion=Tiradentes #33, telefono=8094089999'),
(52, 13, 'solicitud_prestamo', '2025-06-13 12:34:49', 'gerente', 'Préstamo solicitado: Monto=12000, Plazo=12 meses'),
(53, 13, 'aprobado', '2025-06-13 12:35:01', 'gerente', 'Préstamo #20 aprobado'),
(54, 13, 'pago_registrado', '2025-06-13 12:36:15', 'cobro', 'Pago registrado para préstamo #20: Monto=1050, Fecha=2025-07-13'),
(55, 13, 'pago_registrado', '2025-06-13 12:36:32', 'cobro', 'Pago registrado para préstamo #20: Monto=1050, Fecha=2025-08-13'),
(56, 13, 'pago_registrado', '2025-06-13 12:36:43', 'cobro', 'Pago registrado para préstamo #20: Monto=1050, Fecha=2025-08-13'),
(57, 13, 'pago_registrado', '2025-06-13 12:36:56', 'cobro', 'Pago registrado para préstamo #20: Monto=1050, Fecha=2025-09-13'),
(58, 13, 'pago_registrado', '2025-06-13 12:37:30', 'cobro', 'Pago registrado para préstamo #20: Monto=8400, Fecha=2025-10-13'),
(59, 13, 'prestamo_pagado', '2025-06-13 12:37:30', 'cobro', 'Préstamo #20 pagado completamente.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `prestamo_id` int(11) NOT NULL,
  `monto_pago` decimal(10,2) NOT NULL,
  `fecha_pago` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `prestamo_id`, `monto_pago`, `fecha_pago`) VALUES
(1, 1, 100000.00, '2025-05-07'),
(2, 1, 10000.00, '2025-06-07'),
(3, 1, 50000.00, '2025-07-07'),
(4, 1, 25000.00, '2025-08-07'),
(5, 1, 15000.00, '2025-09-07'),
(6, 1, 10000.00, '2025-10-07'),
(16, 7, 157500.00, '2025-05-01'),
(18, 9, 10000.00, '2025-05-01'),
(19, 9, 53000.00, '2025-08-06'),
(20, 6, 43401.00, '2025-08-21'),
(21, 10, 100000.00, '2025-06-30'),
(22, 10, 10000.00, '2025-08-12'),
(23, 10, 100000.00, '2025-12-30'),
(24, 18, 12000.00, '2024-07-08'),
(25, 19, 6533.33, '2024-10-01'),
(26, 19, 50000.00, '2025-05-06'),
(27, 19, 80000.00, '2025-06-18'),
(28, 19, 15266.67, '2025-07-17'),
(29, 19, 5000.00, '2025-08-14'),
(30, 20, 1050.00, '2025-07-13'),
(31, 20, 1050.00, '2025-08-13'),
(32, 20, 1050.00, '2025-08-13'),
(33, 20, 1050.00, '2025-09-13'),
(34, 20, 8400.00, '2025-10-13');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `socio_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `tasa_interes` decimal(5,2) NOT NULL,
  `fecha_solicitud` date NOT NULL,
  `fecha_inicio` date NOT NULL DEFAULT '2025-01-01',
  `fecha_fin` date NOT NULL DEFAULT '2025-01-01',
  `plazo_meses` int(11) NOT NULL,
  `estado` enum('pendiente','aprobado','pagado','rechazado') DEFAULT 'pendiente',
  `cuota_mensual` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cheque_pdf` varchar(255) DEFAULT NULL,
  `mora` decimal(10,2) DEFAULT 0.00,
  `motivo_cancelacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prestamos`
--

INSERT INTO `prestamos` (`id`, `socio_id`, `monto`, `tasa_interes`, `fecha_solicitud`, `fecha_inicio`, `fecha_fin`, `plazo_meses`, `estado`, `cuota_mensual`, `cheque_pdf`, `mora`, `motivo_cancelacion`) VALUES
(1, 1, 200000.00, 5.00, '0000-00-00', '2025-04-07', '2026-04-07', 12, 'pagado', 17121.50, 'cheque_prestamo_1_1744058577.pdf', 0.00, NULL),
(5, 4, 10000.00, 5.00, '0000-00-00', '2025-04-23', '2025-10-23', 6, 'rechazado', 1691.06, 'cheque_prestamo_4_1745411927.pdf', 0.00, NULL),
(6, 7, 41000.00, 5.00, '0000-00-00', '2025-04-23', '2026-04-23', 12, 'pagado', 3509.91, 'cheque_prestamo_7_1745443799.pdf', 351.00, NULL),
(7, 1, 150000.00, 5.00, '0000-00-00', '2025-04-23', '2026-04-23', 12, 'pagado', 12841.12, 'cheque_prestamo_1_1745445028.pdf', 0.00, NULL),
(9, 8, 60000.00, 5.00, '0000-00-00', '2025-04-24', '2026-04-24', 12, 'pagado', 5136.45, 'cheque_prestamo_8_1745446598.pdf', 0.00, NULL),
(10, 9, 200000.00, 5.00, '0000-00-00', '2025-04-24', '2026-04-24', 12, 'pagado', 17121.50, 'cheque_prestamo_9_1745448604.pdf', 0.00, NULL),
(18, 10, 100000.00, 12.00, '0000-00-00', '2025-04-30', '2026-04-30', 12, 'aprobado', 9333.33, 'cheque_prestamo_18_1746042532.pdf', 0.00, NULL),
(19, 11, 140000.00, 12.00, '0000-00-00', '2025-05-01', '2027-05-01', 24, 'pagado', 6533.33, 'cheque_prestamo_19_1746101359.pdf', 0.00, NULL),
(20, 13, 12000.00, 5.00, '0000-00-00', '2025-06-13', '2026-06-13', 12, 'pagado', 1050.00, 'cheque_prestamo_20_1749818101.pdf', 0.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `retiros`
--

CREATE TABLE `retiros` (
  `id` int(11) NOT NULL,
  `socio_id` int(11) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `cheque_pdf` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `retiros`
--

INSERT INTO `retiros` (`id`, `socio_id`, `monto`, `fecha`, `cheque_pdf`) VALUES
(1, 10, 5000.00, '2024-08-01', 'cheque_retiro_10_1746037051.pdf');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fecha_ingreso` date NOT NULL,
  `saldo` decimal(10,2) DEFAULT 0.00,
  `saldo_6_meses` decimal(10,2) DEFAULT 0.00,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `cedula` varchar(13) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `socios`
--

INSERT INTO `socios` (`id`, `nombre`, `email`, `fecha_ingreso`, `saldo`, `saldo_6_meses`, `estado`, `cedula`, `direccion`, `telefono`) VALUES
(1, 'Nicolax Luciano', 'nicolaxblanco@gmail.com', '2024-07-16', 80101.00, 100000.00, 'activo', '40236184913', 'Calle Saona #5', '8094080416'),
(4, 'Victor Luciano', 'victor@gmail.com', '2024-07-04', 10000.00, 30000.00, 'activo', '00000000001', 'Calle Saona #5', '8090000001'),
(6, 'Miledys Blanco', 'yamilex@gmail.com', '2023-12-11', 50000.00, 50000.00, 'activo', '22300000003', 'Saona #5', '8099860416'),
(7, 'Leandro Aracena', 'leandro.aracena@gmail.com', '2022-07-01', 20500.00, 20500.00, 'activo', '00118082700', 'Tiradentes #33', '8096717092'),
(8, 'Erick Peña', 'erick@gmail.com', '2024-01-23', 30000.00, 30000.00, 'activo', '22300000007', 'Tiradentes #33', '8294080416'),
(9, 'Ariel Pena Valerio', 'ariel@gmail.com', '2024-01-08', 80000.00, 100000.00, 'activo', '40236100000', 'Calle Saona #7', '8494080416'),
(10, 'Daniel Jimenez Melo', 'daniel@gmail.com', '2023-08-31', 50000.00, 20000.00, 'activo', '22336184914', 'Azua #69', '8290000416'),
(11, 'Hector Martinez', 'hector@gmail.com', '2024-06-02', 70000.00, 0.00, 'activo', '22336184913', 'grieta #1', '8294020000'),
(12, 'Jose Amado', 'jose@gmail.com', '2024-05-08', 5000.00, 0.00, 'activo', '40200000011', 'Tiradentes #33', '8090001111'),
(13, 'Luis Abinaders', 'luis.abinader@cnss.gob.do', '2023-12-21', 6000.00, 5000.00, 'activo', '40236184999', 'Tiradentes #33', '8094089999');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cajera','gerente','cobro') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `role`) VALUES
(1, 'admin', 'admin123', 'admin'),
(2, 'cajera', 'cajera123', 'cajera'),
(3, 'gerente', 'gerente123', 'gerente'),
(4, 'cobro', 'cobro123', 'cobro');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `aportes`
--
ALTER TABLE `aportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `socio_id` (`socio_id`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prestamo_id` (`prestamo_id`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `socio_id` (`socio_id`);

--
-- Indices de la tabla `retiros`
--
ALTER TABLE `retiros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `socio_id` (`socio_id`);

--
-- Indices de la tabla `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `aportes`
--
ALTER TABLE `aportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `retiros`
--
ALTER TABLE `retiros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `socios`
--
ALTER TABLE `socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `aportes`
--
ALTER TABLE `aportes`
  ADD CONSTRAINT `aportes_ibfk_1` FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`);

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`prestamo_id`) REFERENCES `prestamos` (`id`);

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`);

--
-- Filtros para la tabla `retiros`
--
ALTER TABLE `retiros`
  ADD CONSTRAINT `retiros_ibfk_1` FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
