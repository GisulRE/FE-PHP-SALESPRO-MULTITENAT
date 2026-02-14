-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 13, 2026 at 09:40 PM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 7.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_balance` double DEFAULT NULL,
  `total_balance` double NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_method_pay`
--

DROP TABLE IF EXISTS `account_method_pay`;
CREATE TABLE `account_method_pay` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `methodpay_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `adjustments`
--

DROP TABLE IF EXISTS `adjustments`;
CREATE TABLE `adjustments` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_qty` double NOT NULL,
  `item` int(11) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `adjustment_accounts`
--

DROP TABLE IF EXISTS `adjustment_accounts`;
CREATE TABLE `adjustment_accounts` (
  `id` int(11) NOT NULL,
  `reference_no` varchar(20) NOT NULL,
  `account_id` int(11) NOT NULL,
  `note` varchar(254) NOT NULL,
  `amount` double NOT NULL DEFAULT 0,
  `type_adjustment` varchar(10) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
CREATE TABLE `attendances` (
  `id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checkin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkout` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attention_shift`
--

DROP TABLE IF EXISTS `attention_shift`;
CREATE TABLE `attention_shift` (
  `id` int(11) NOT NULL,
  `reference_nro` varchar(50) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `autorizacion_facturacion`
--

DROP TABLE IF EXISTS `autorizacion_facturacion`;
CREATE TABLE `autorizacion_facturacion` (
  `id` int(10) UNSIGNED NOT NULL,
  `ambiente` int(10) UNSIGNED NOT NULL,
  `codigo_sistema` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1,
  `fecha_solicitud` date NOT NULL,
  `fecha_vencimiento_token` datetime NOT NULL,
  `token` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_modalidad` int(10) UNSIGNED NOT NULL,
  `tipo_sistema` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `id_url_produccion_obtencion_codigos` int(10) UNSIGNED DEFAULT NULL,
  `id_url_produccion_operaciones` int(10) UNSIGNED DEFAULT NULL,
  `id_url_produccion_recepcion_compras` int(10) UNSIGNED DEFAULT NULL,
  `id_url_produccion_sincronizacion_datos` int(10) UNSIGNED DEFAULT NULL,
  `id_url_pruebas_obtencion_codigos` int(10) UNSIGNED DEFAULT NULL,
  `id_url_pruebas_operaciones` int(10) UNSIGNED DEFAULT NULL,
  `id_url_pruebas_recepcion_compras` int(10) UNSIGNED DEFAULT NULL,
  `id_url_pruebas_sincronizacion_datos` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billers`
--

DROP TABLE IF EXISTS `billers`;
CREATE TABLE `billers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vat_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_id` int(11) NOT NULL,
  `account_id_tarjeta` int(11) DEFAULT NULL,
  `account_id_cheque` int(11) DEFAULT NULL,
  `account_id_vale` int(11) DEFAULT NULL,
  `account_id_otros` int(11) DEFAULT NULL,
  `account_id_pagoposterior` int(11) DEFAULT NULL,
  `account_id_transferenciabancaria` int(11) DEFAULT NULL,
  `account_id_deposito` int(11) DEFAULT NULL,
  `account_id_swift` int(11) DEFAULT NULL,
  `account_id_giftcard` int(11) DEFAULT NULL,
  `account_id_qr` int(11) DEFAULT NULL,
  `account_id_receivable` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `punto_venta_siat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `biller_warehouses`
--

DROP TABLE IF EXISTS `biller_warehouses`;
CREATE TABLE `biller_warehouses` (
  `id` int(11) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE `brands` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashier`
--

DROP TABLE IF EXISTS `cashier`;
CREATE TABLE `cashier` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `note` varchar(200) DEFAULT NULL,
  `amount_start` double NOT NULL DEFAULT 0,
  `amount_end` double DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `codigo_actividad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_producto_servicio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `control_contingencia`
--

DROP TABLE IF EXISTS `control_contingencia`;
CREATE TABLE `control_contingencia` (
  `id` int(10) UNSIGNED NOT NULL,
  `cuis` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sucursal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_punto_venta` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cufd_valido` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_factura` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '[1=compra-venta, 2=alquiler, 13=servicios]',
  `codigo_documento_sector` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_evento` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_inicio_evento` timestamp NULL DEFAULT NULL,
  `fecha_fin_evento` timestamp NULL DEFAULT NULL,
  `cufd_evento` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_registro_evento` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usuario_modificacion` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cantidad_paquetes` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `control_contingencia_paquetes`
--

DROP TABLE IF EXISTS `control_contingencia_paquetes`;
CREATE TABLE `control_contingencia_paquetes` (
  `id` int(10) UNSIGNED NOT NULL,
  `control_contingencia_id` int(10) UNSIGNED NOT NULL,
  `cantidad_ventas` int(10) UNSIGNED DEFAULT NULL,
  `fecha_de_envio` timestamp NULL DEFAULT NULL,
  `glosa_nro_factura_inicio_a_fin` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arreglo_ventas` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_recepcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `respuesta_servicio` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_errores` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
CREATE TABLE `coupons` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `minimum_amount` double DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `used` int(11) NOT NULL,
  `expired_date` date NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `credenciales_cafc`
--

DROP TABLE IF EXISTS `credenciales_cafc`;
CREATE TABLE `credenciales_cafc` (
  `id` int(10) UNSIGNED NOT NULL,
  `año` int(10) UNSIGNED DEFAULT NULL,
  `tipo_factura` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '[‘compra-venta’,‘alquiler’, ‘servicio-básico’]',
  `codigo_documento_sector` tinyint(3) UNSIGNED DEFAULT NULL,
  `codigo_cafc` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sucursal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_punto_venta` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_emision` timestamp NULL DEFAULT NULL,
  `fecha_vigencia` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `nro_min` int(10) UNSIGNED DEFAULT NULL,
  `nro_max` int(10) UNSIGNED DEFAULT NULL,
  `correlativo_factura` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_group_id` int(11) NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_no` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deposit` double DEFAULT NULL,
  `expense` double DEFAULT NULL,
  `credit` double NOT NULL DEFAULT 0,
  `is_credit` tinyint(1) NOT NULL DEFAULT 0,
  `price_type` int(11) NOT NULL DEFAULT 0 COMMENT '0 = default, 1 = price a, 2 = price b, 3 = price c',
  `is_tasadignidad` tinyint(1) NOT NULL DEFAULT 0,
  `is_ley1886` tinyint(1) NOT NULL DEFAULT 0,
  `porcentaje_tasadignidad` double NOT NULL DEFAULT 0,
  `porcentaje_ley1886` double NOT NULL DEFAULT 0,
  `codigofijo` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nro_medidor` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_documento` tinyint(3) UNSIGNED DEFAULT NULL,
  `valor_documento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento_documento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razon_social` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_birh` date DEFAULT NULL,
  `sucursal_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_company`
--

DROP TABLE IF EXISTS `customer_company`;
CREATE TABLE `customer_company` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `fullname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telephone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_custom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_groups`
--

DROP TABLE IF EXISTS `customer_groups`;
CREATE TABLE `customer_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_nit`
--

DROP TABLE IF EXISTS `customer_nit`;
CREATE TABLE `customer_nit` (
  `id` int(10) UNSIGNED NOT NULL,
  `tipo_documento` tinyint(3) UNSIGNED DEFAULT NULL,
  `valor_documento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento_documento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_caso_especial` tinyint(3) UNSIGNED DEFAULT 1,
  `razon_social` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_sales`
--

DROP TABLE IF EXISTS `customer_sales`;
CREATE TABLE `customer_sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `codigofijo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razon_social` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_documento` tinyint(3) UNSIGNED DEFAULT NULL,
  `valor_documento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `complemento_documento` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_tarjeta_credito_debito` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_caso_especial` tinyint(3) UNSIGNED DEFAULT 1,
  `tipo_metodo_pago` smallint(5) UNSIGNED DEFAULT NULL,
  `nro_factura` bigint(20) UNSIGNED DEFAULT NULL,
  `codigo_recepcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuf` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_cufd` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `xml` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sucursal` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado_factura` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nro_factura_manual` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_manual` timestamp NULL DEFAULT NULL,
  `codigo_excepcion` tinyint(4) DEFAULT NULL,
  `codigo_documento_sector` tinyint(3) UNSIGNED DEFAULT NULL,
  `glosa_periodo_facturado` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categoria` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_medidor` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lectura_medidor_anterior` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lectura_medidor_actual` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gestion` int(10) UNSIGNED DEFAULT NULL,
  `mes` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ciudad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zona` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domicilio_cliente` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consumo_periodo` double UNSIGNED DEFAULT NULL,
  `beneficiario_ley_1886` int(10) UNSIGNED DEFAULT NULL,
  `monto_descuento_ley_1886` double UNSIGNED DEFAULT NULL,
  `monto_descuento_tarifa_dignidad` double UNSIGNED DEFAULT NULL,
  `tasa_aseo` double UNSIGNED DEFAULT NULL,
  `tasa_alumbrado` double UNSIGNED DEFAULT NULL,
  `otras_tasas` double UNSIGNED DEFAULT NULL,
  `ajuste_no_sujeto_iva` double UNSIGNED DEFAULT NULL,
  `detalle_ajuste_no_sujeto_iva` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ajuste_sujeto_iva` double UNSIGNED DEFAULT NULL,
  `detalle_ajuste_sujeto_iva` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `otros_pagos_no_sujeto_iva` double UNSIGNED DEFAULT NULL,
  `detalle_otros_pagos_no_sujeto_iva` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
CREATE TABLE `deliveries` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sale_id` int(11) NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivered_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recieved_by` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

DROP TABLE IF EXISTS `deposits`;
CREATE TABLE `deposits` (
  `id` int(10) UNSIGNED NOT NULL,
  `amount` double NOT NULL,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `contract_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `percentage` double NOT NULL DEFAULT 0,
  `pay_commission` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FALSE',
  `pre_sale` tinyint(1) NOT NULL DEFAULT 1,
  `warehouse_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
CREATE TABLE `expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_category_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
CREATE TABLE `expense_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `factura_masiva`
--

DROP TABLE IF EXISTS `factura_masiva`;
CREATE TABLE `factura_masiva` (
  `id` int(10) UNSIGNED NOT NULL,
  `glosa` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cuis` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sucursal` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_punto_venta` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_documento_sector` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `fecha_inicio` timestamp NULL DEFAULT NULL,
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `tipo_factura` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '[‘compra-venta’, ‘servicio-básico’]',
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cantidad_paquetes` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `factura_masiva_paquetes`
--

DROP TABLE IF EXISTS `factura_masiva_paquetes`;
CREATE TABLE `factura_masiva_paquetes` (
  `id` int(10) UNSIGNED NOT NULL,
  `factura_masiva_id` int(10) UNSIGNED NOT NULL,
  `cantidad_ventas` int(10) UNSIGNED DEFAULT NULL,
  `fecha_de_envio` timestamp NULL DEFAULT NULL,
  `glosa_nro_factura_inicio_a_fin` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `arreglo_ventas` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_recepcion` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `respuesta_servicio` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `log_errores` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `garantes`
--

DROP TABLE IF EXISTS `garantes`;
CREATE TABLE `garantes` (
  `id_garante` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `fecha_garantia` date NOT NULL,
  `ci` varchar(20) NOT NULL,
  `tel_cel` varchar(50) NOT NULL,
  `direccion` varchar(250) NOT NULL,
  `zona` varchar(200) NOT NULL,
  `lugar_trabajo` varchar(200) NOT NULL,
  `telefono` int(11) NOT NULL,
  `estado` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `general_settings`
--

DROP TABLE IF EXISTS `general_settings`;
CREATE TABLE `general_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `site_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staff_access` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_format` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_expiration` int(11) NOT NULL DEFAULT 30,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `currency_position` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gift_cards`
--

DROP TABLE IF EXISTS `gift_cards`;
CREATE TABLE `gift_cards` (
  `id` int(10) UNSIGNED NOT NULL,
  `card_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `expense` double NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gift_card_recharges`
--

DROP TABLE IF EXISTS `gift_card_recharges`;
CREATE TABLE `gift_card_recharges` (
  `id` int(10) UNSIGNED NOT NULL,
  `gift_card_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
CREATE TABLE `holidays` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_approved` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrm_settings`
--

DROP TABLE IF EXISTS `hrm_settings`;
CREATE TABLE `hrm_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `checkin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkout` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `kardex`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `kardex`;
CREATE TABLE `kardex` (
`transaction_id` int(10) unsigned
,`product_id` int(11)
,`product` varchar(191)
,`transaction_type` varchar(13)
,`warehouse_id` int(10) unsigned
,`warehouse` varchar(191)
,`warehouse_qty_before` int(11)
,`warehouse_qty_after` int(11)
,`entrada` bigint(12)
,`salida` bigint(12)
,`qty` double
,`cost` varchar(191)
,`total_cost` varchar(417)
,`from_to` int(11)
,`date` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lote_sales`
--

DROP TABLE IF EXISTS `lote_sales`;
CREATE TABLE `lote_sales` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `qty` double NOT NULL,
  `data` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `method_payments`
--

DROP TABLE IF EXISTS `method_payments`;
CREATE TABLE `method_payments` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `apply` tinyint(1) NOT NULL DEFAULT 0,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `cbx` tinyint(1) NOT NULL DEFAULT 1,
  `codigo_clasificador_siat` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `money_transfers`
--

DROP TABLE IF EXISTS `money_transfers`;
CREATE TABLE `money_transfers` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_account_id` int(11) NOT NULL,
  `to_account_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `note` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

DROP TABLE IF EXISTS `oauth_access_tokens`;
CREATE TABLE `oauth_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

DROP TABLE IF EXISTS `oauth_auth_codes`;
CREATE TABLE `oauth_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

DROP TABLE IF EXISTS `oauth_clients`;
CREATE TABLE `oauth_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

DROP TABLE IF EXISTS `oauth_personal_access_clients`;
CREATE TABLE `oauth_personal_access_clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `oauth_refresh_tokens`;
CREATE TABLE `oauth_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_reference` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `purchase_id` int(11) DEFAULT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `account_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `amount` double NOT NULL,
  `change` double NOT NULL,
  `paying_method` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_with_cheque`
--

DROP TABLE IF EXISTS `payment_with_cheque`;
CREATE TABLE `payment_with_cheque` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(11) NOT NULL,
  `cheque_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_with_credit_card`
--

DROP TABLE IF EXISTS `payment_with_credit_card`;
CREATE TABLE `payment_with_credit_card` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `charge_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number_card` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_with_gift_card`
--

DROP TABLE IF EXISTS `payment_with_gift_card`;
CREATE TABLE `payment_with_gift_card` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(11) NOT NULL,
  `gift_card_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_with_paypal`
--

DROP TABLE IF EXISTS `payment_with_paypal`;
CREATE TABLE `payment_with_paypal` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(11) NOT NULL,
  `transaction_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_with_receivable`
--

DROP TABLE IF EXISTS `payment_with_receivable`;
CREATE TABLE `payment_with_receivable` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `sales` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

DROP TABLE IF EXISTS `payrolls`;
CREATE TABLE `payrolls` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `paying_method` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personas`
--

DROP TABLE IF EXISTS `personas`;
CREATE TABLE `personas` (
  `id_persona` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) NOT NULL,
  `ci` varchar(10) NOT NULL,
  `fecha_nac` date NOT NULL,
  `tel_cel` varchar(10) NOT NULL,
  `direccion` varchar(250) NOT NULL,
  `zona` varchar(200) NOT NULL,
  `contextura` varchar(200) NOT NULL,
  `estatura` varchar(10) NOT NULL,
  `nro_seguro` varchar(20) NOT NULL,
  `seg_vida` varchar(20) NOT NULL,
  `id_garante1` int(11) NOT NULL,
  `id_garante2` int(11) NOT NULL,
  `foto` varchar(250) DEFAULT NULL,
  `estado_civil` varchar(50) NOT NULL,
  `afp` varchar(100) NOT NULL,
  `estado` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pos_setting`
--

DROP TABLE IF EXISTS `pos_setting`;
CREATE TABLE `pos_setting` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `product_number` int(11) NOT NULL,
  `keybord_active` tinyint(1) NOT NULL,
  `stripe_public_key` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_secret_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `t_c` varchar(12) COLLATE utf8mb4_unicode_ci DEFAULT '6.96',
  `print` tinyint(1) NOT NULL DEFAULT 1,
  `type_print` int(11) DEFAULT NULL,
  `date_sell` tinyint(1) NOT NULL DEFAULT 0,
  `print_order` int(11) NOT NULL DEFAULT 0,
  `print_presale` tinyint(1) NOT NULL DEFAULT 0,
  `keybord_presale` tinyint(1) NOT NULL DEFAULT 0,
  `facturacion_id` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_moneda_siat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `nit_emisor` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social_emisor` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion_emisor` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_siat` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pass_siat` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_siat` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_operaciones` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_optimo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_cobranza` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_session_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_session_last_started_at` timestamp NULL DEFAULT NULL,
  `require_transfer_authorization` tinyint(1) NOT NULL DEFAULT 1,
  `modo_contingencia` tinyint(1) NOT NULL DEFAULT 0,
  `codigo_emision` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '[1=>online, 3=>masivo]',
  `qr_commission` double DEFAULT 0,
  `hour_resetshift` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cant_max_contingencia` smallint(5) UNSIGNED DEFAULT NULL,
  `cant_max_masiva` smallint(5) UNSIGNED DEFAULT NULL,
  `quotation_printer` int(11) DEFAULT NULL,
  `customer_sucursal` tinyint(1) NOT NULL DEFAULT 0,
  `user_category` tinyint(1) NOT NULL DEFAULT 0,
  `cant_decimal` int(11) NOT NULL DEFAULT 2,
  `cufd_centralizado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pre_sale`
--

DROP TABLE IF EXISTS `pre_sale`;
CREATE TABLE `pre_sale` (
  `id` int(11) NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `attentionshift_id` int(11) DEFAULT NULL,
  `item` int(11) NOT NULL,
  `total_qty` double NOT NULL,
  `grand_total` double NOT NULL,
  `order_discount` double DEFAULT 0,
  `total_discount` double DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `tips` double DEFAULT 0,
  `status` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

DROP TABLE IF EXISTS `printers`;
CREATE TABLE `printers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `printer` varchar(200) NOT NULL,
  `host_address` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'TYPE = TCP/IP - SHARED',
  `category_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode_symbology` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `purchase_unit_id` int(11) NOT NULL,
  `sale_unit_id` int(11) NOT NULL,
  `cost` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_a` double NOT NULL DEFAULT 0,
  `price_b` double NOT NULL DEFAULT 0,
  `price_c` double NOT NULL DEFAULT 0,
  `qty` double DEFAULT NULL,
  `alert_quantity` double DEFAULT NULL,
  `promotion` tinyint(4) DEFAULT NULL,
  `promotion_price` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starting_date` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_date` date DEFAULT NULL,
  `tax_id` int(11) DEFAULT NULL,
  `tax_method` int(11) DEFAULT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_variant` tinyint(1) DEFAULT NULL,
  `featured` tinyint(4) DEFAULT NULL,
  `product_list` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_list` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_list` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_pricelist` tinyint(1) NOT NULL DEFAULT 0,
  `product_details` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `courtesy` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FALSE',
  `permanent` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TRUE',
  `starting_date_courtesy` date DEFAULT NULL,
  `ending_date_courtesy` date DEFAULT NULL,
  `courtesy_clearance_price` double DEFAULT 0,
  `commission_percentage` float NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `codigo_actividad` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_producto_servicio` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_basicservice` tinyint(1) NOT NULL DEFAULT 0,
  `account_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_adjustments`
--

DROP TABLE IF EXISTS `product_adjustments`;
CREATE TABLE `product_adjustments` (
  `id` int(10) UNSIGNED NOT NULL,
  `adjustment_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `code` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` double NOT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `product_adjustments`
--
DROP TRIGGER IF EXISTS `after_product_adjustments_trigger`;
DELIMITER $$
CREATE TRIGGER `after_product_adjustments_trigger` BEFORE INSERT ON `product_adjustments` FOR EACH ROW BEGIN 
DECLARE
	trans_id INT;
DECLARE
	warehouse_id_val INT;
DECLARE
	product_qty_before INT;
DECLARE
	warehouse_qty_before INT;
DECLARE
	purchase_ref_no VARCHAR(50);
DECLARE
	product_type VARCHAR(50);
	-- Obtener el warehouse_id y el reference_no de la tabla sales
	SELECT
	    id,
	    warehouse_id,
	    reference_no INTO trans_id,
	    warehouse_id_val,
	    purchase_ref_no
	FROM adjustments
	WHERE
	    id = NEW.adjustment_id;
	-- FOR UPDATE;
	-- Bloquear la fila en la tabla return
	-- Obtener la cantidad de productos antes de la actualización y el tipo de producto
	SELECT
	    qty,
	    type INTO product_qty_before,
	    product_type
	FROM products
	WHERE
	    id = NEW.product_id;
	-- FOR UPDATE Bloquear la fila en la tabla products
	-- Obtener la cantidad en el almacén antes de la actualización
	SELECT qty INTO warehouse_qty_before
	FROM product_warehouse
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_val;
	-- Registrar los cambios en la tabla Record
	IF NEW.action = "+" THEN
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 5, (product_qty_before - NEW.qty), product_qty_before, warehouse_qty_before - NEW.qty, warehouse_qty_before
	    );
	ELSE
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 5, (product_qty_before + NEW.qty), product_qty_before, (
	            warehouse_qty_before + NEW.qty
	        ), warehouse_qty_before
	    );
END
	IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_associated`
--

DROP TABLE IF EXISTS `product_associated`;
CREATE TABLE `product_associated` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_courtesy_id` int(10) UNSIGNED NOT NULL,
  `product_associated_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_lot`
--

DROP TABLE IF EXISTS `product_lot`;
CREATE TABLE `product_lot` (
  `id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `idwarehouse` int(11) NOT NULL,
  `idproduct` int(11) NOT NULL,
  `qty` double NOT NULL,
  `stock` double NOT NULL,
  `expiration` date DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `supplier` int(11) NOT NULL,
  `fabrication_date` date NOT NULL,
  `status` varchar(10) COLLATE utf8_spanish_ci NOT NULL COMMENT '0 = down, 1 = up, 2 = update, 3 = other',
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `low_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_pre_sale`
--

DROP TABLE IF EXISTS `product_pre_sale`;
CREATE TABLE `product_pre_sale` (
  `id` int(11) NOT NULL,
  `presale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `qty` double NOT NULL,
  `sale_unit_id` int(11) NOT NULL,
  `net_unit_price` double NOT NULL,
  `discount` double NOT NULL DEFAULT 0,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `product_purchases`
--

DROP TABLE IF EXISTS `product_purchases`;
CREATE TABLE `product_purchases` (
  `id` int(10) UNSIGNED NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` double NOT NULL,
  `recieved` double NOT NULL,
  `purchase_unit_id` int(11) NOT NULL,
  `net_unit_cost` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `product_purchases`
--
DROP TRIGGER IF EXISTS `AFTER_PRODUCT_PURCHASES_TRIGGER`;
DELIMITER $$
CREATE TRIGGER `AFTER_PRODUCT_PURCHASES_TRIGGER` AFTER INSERT ON `product_purchases` FOR EACH ROW BEGIN 
DECLARE
	trans_id INT;
DECLARE
	warehouse_id_val INT;
DECLARE
	product_qty_before INT;
DECLARE
	warehouse_qty_before INT;
DECLARE
	purchase_ref_no VARCHAR(50);
DECLARE
	product_type VARCHAR(50);
DECLARE
	p_status INT;
declare prod_warehouse_id int;
	-- Obtener el warehouse_id y el reference_no de la tabla sales
	SELECT
	    id,
	    warehouse_id,
	    reference_no,
	    status INTO trans_id,
	    warehouse_id_val,
	    purchase_ref_no,
	    p_status
	FROM purchases
	WHERE
	    id = NEW.purchase_id FOR
	UPDATE;
	-- add product_warehouse if not exists
	Select id INTO prod_warehouse_id
	FROM product_warehouse
	WHERE
	    product_warehouse.product_id = NEW.product_id
	    AND product_warehouse.warehouse_id = warehouse_id_val;
	IF prod_warehouse_id IS NULL THEN
	INSERT INTO
	    product_warehouse (product_id, warehouse_id, qty)
	VALUES (
	        NEW.product_id, warehouse_id_val, 0
	    );
END
	IF;
	-- validar  purchase status
	IF p_status = 1
	OR p_status = 2 THEN
	-- Obtener la cantidad de productos antes de la actualización y el tipo de producto
	SELECT
	    qty,
	    type INTO product_qty_before,
	    product_type
	FROM products
	WHERE
	    id = NEW.product_id FOR
	UPDATE;
	-- Bloquear la fila en la tabla products
	-- Obtener la cantidad en el almacén antes de la actualización
	SELECT qty INTO warehouse_qty_before
	FROM product_warehouse
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_val FOR
	UPDATE;
	-- Bloquear la fila en la tabla product_warehouse
	IF NEW.variant_id IS NOT NULL THEN
	SELECT qty INTO product_qty_before
	FROM product_variants
	WHERE
	    id = NEW.variant_id FOR
	UPDATE;
	-- Bloquear la fila en la tabla product_variants
	-- Verificar si el producto es de tipo digital
	IF product_type <> 'digital' THEN
	-- Actualizar la cantidad en product_variants
	UPDATE product_variants
	SET
	    qty = qty + NEW.recieved
	WHERE
	    id = NEW.variant_id;
END
	IF;
	ELSE
	-- Verificar si el tipo de producto es diferente de 'digital' para actualizar inventario
	IF product_type <> 'digital' THEN
	-- Actualizar la cantidad en la tabla products
	UPDATE products
	SET
	    qty = qty + NEW.recieved
	WHERE
	    id = NEW.product_id;
END
	IF;
	-- Actualizar la cantidad en la tabla product_warehouse
	UPDATE product_warehouse
	SET
	    qty = qty + NEW.recieved
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_val;
END
	IF;
	-- Registrar los cambios en la tabla Record
	IF product_type = 'digital' THEN
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 2, product_qty_before, product_qty_before, warehouse_qty_before, warehouse_qty_before
	    );
	ELSE
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 2, product_qty_before, (
	            product_qty_before + NEW.recieved
	        ), warehouse_qty_before, (
	            warehouse_qty_before + NEW.recieved
	        )
	    );
END
	IF;
END
	IF;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `AFTER_PRODUCT_PURCHASES_UPDATE_TRIGGER`;
DELIMITER $$
CREATE TRIGGER `AFTER_PRODUCT_PURCHASES_UPDATE_TRIGGER` AFTER UPDATE ON `product_purchases` FOR EACH ROW BEGIN 
DECLARE
	trans_id INT;
DECLARE
	warehouse_id_val INT;
DECLARE
	product_qty_before INT;
DECLARE
	warehouse_qty_before INT;
DECLARE
	purchase_ref_no VARCHAR(50);
DECLARE
	product_type VARCHAR(50);
DECLARE
	p_status INT;
declare prod_warehouse_id int;
	-- Obtener el warehouse_id y el reference_no de la tabla sales
	SELECT
	    id,
	    warehouse_id,
	    reference_no,
	    status INTO trans_id,
	    warehouse_id_val,
	    purchase_ref_no,
	    p_status
	FROM purchases
	WHERE
	    id = NEW.purchase_id FOR
	UPDATE;
	-- add product if not exists on product_warehose
	Select id INTO prod_warehouse_id
	FROM product_warehouse
	WHERE
	    product_warehouse.product_id = NEW.product_id
	    AND product_warehouse.warehouse_id = warehouse_id_val;
	IF prod_warehouse_id IS NULL THEN
	INSERT INTO
	    product_warehouse (product_id, warehouse_id, qty)
	VALUES (
	        NEW.product_id, warehouse_id_val, 0
	    );
END
	IF;
	-- validar  purchase status
	IF ABS(NEW.recieved - OLD.recieved) > 0 THEN
	-- Obtener la cantidad de productos antes de la actualización y el tipo de producto
	SELECT
	    qty,
	    type INTO product_qty_before,
	    product_type
	FROM products
	WHERE
	    id = NEW.product_id FOR
	UPDATE;
	-- Bloquear la fila en la tabla products
	-- Obtener la cantidad en el almacén antes de la actualización
	SELECT qty INTO warehouse_qty_before
	FROM product_warehouse
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_val FOR
	UPDATE;
	-- Bloquear la fila en la tabla product_warehouse
	IF NEW.variant_id IS NOT NULL THEN
	SELECT qty INTO product_qty_before
	FROM product_variants
	WHERE
	    id = NEW.variant_id FOR
	UPDATE;
	-- Bloquear la fila en la tabla product_variants
	-- Verificar si el producto es de tipo digital
	IF product_type <> 'digital' THEN
	-- Actualizar la cantidad en product_variants
	UPDATE product_variants
	SET
	    qty = qty + ABS(NEW.recieved - OLD.recieved)
	WHERE
	    id = NEW.variant_id;
END
	IF;
	ELSE
	-- Verificar si el tipo de producto es diferente de 'digital' para actualizar inventario
	IF product_type <> 'digital' THEN
	-- Actualizar la cantidad en la tabla products
	UPDATE products
	SET
	    qty = qty + ABS(NEW.recieved - OLD.recieved)
	WHERE
	    id = NEW.product_id;
END
	IF;
	-- Actualizar la cantidad en la tabla product_warehouse
	UPDATE product_warehouse
	SET
	    qty = qty + ABS(NEW.recieved - OLD.recieved)
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_val;
END
	IF;
	-- Registrar los cambios en la tabla record
	IF product_type = 'digital' THEN
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 2, product_qty_before, product_qty_before, warehouse_qty_before, warehouse_qty_before
	    );
	ELSE
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 2, product_qty_before, (
	            product_qty_before + ABS(NEW.recieved - OLD.recieved)
	        ), warehouse_qty_before, (
	            warehouse_qty_before + ABS(NEW.recieved - OLD.recieved)
	        )
	    );
END
	IF;
END
	IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_quotation`
--

DROP TABLE IF EXISTS `product_quotation`;
CREATE TABLE `product_quotation` (
  `id` int(10) UNSIGNED NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` double NOT NULL,
  `sale_unit_id` int(11) NOT NULL,
  `net_unit_price` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_returns`
--

DROP TABLE IF EXISTS `product_returns`;
CREATE TABLE `product_returns` (
  `id` int(10) UNSIGNED NOT NULL,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` double NOT NULL,
  `sale_unit_id` int(11) NOT NULL,
  `net_unit_price` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `product_returns`
--
DROP TRIGGER IF EXISTS `BEFORE_PRODUCT_RETURNS_TRIGGER`;
DELIMITER $$
CREATE TRIGGER `BEFORE_PRODUCT_RETURNS_TRIGGER` BEFORE INSERT ON `product_returns` FOR EACH ROW BEGIN 
DECLARE
	trans_id INT;
DECLARE
	warehouse_id_val INT;
DECLARE
	product_qty_before INT;
DECLARE
	warehouse_qty_before INT;
DECLARE
	purchase_ref_no VARCHAR(50);
DECLARE
	product_type VARCHAR(50);
	-- Obtener el warehouse_id y el reference_no de la tabla sales
	SELECT
	    id,
	    warehouse_id,
	    reference_no INTO trans_id,
	    warehouse_id_val,
	    purchase_ref_no
	FROM returns
	WHERE
	    id = NEW.return_id;
	-- FOR UPDATE;
	-- Bloquear la fila en la tabla return
	-- Obtener la cantidad de productos antes de la actualización y el tipo de producto
	SELECT
	    qty,
	    type INTO product_qty_before,
	    product_type
	FROM products
	WHERE
	    id = NEW.product_id;
	-- FOR UPDATE Bloquear la fila en la tabla products
	-- Obtener la cantidad en el almacén antes de la actualización
	SELECT qty INTO warehouse_qty_before
	FROM product_warehouse
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_val;
	-- FOR UPDATE; -- Bloquear la fila en la tabla product_warehouse
	IF NEW.variant_id IS NOT NULL THEN
	SELECT qty INTO product_qty_before
	FROM product_variants
	WHERE
	    id = NEW.variant_id;
	-- FOR UPDATE; -- Bloquear la fila en la tabla product_variants
END
	IF;
	-- Registrar los cambios en la tabla Record
	IF product_type = 'digital' THEN
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 3, product_qty_before, product_qty_before, warehouse_qty_before, warehouse_qty_before
	    );
	ELSE
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 3, (product_qty_before - NEW.qty), product_qty_before, (
	            warehouse_qty_before - NEW.qty
	        ), warehouse_qty_before
	    );
END
	IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_sales`
--

DROP TABLE IF EXISTS `product_sales`;
CREATE TABLE `product_sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `qty` double NOT NULL,
  `cost` double DEFAULT 0,
  `sale_unit_id` int(11) NOT NULL,
  `net_unit_price` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `product_sales`
--
DROP TRIGGER IF EXISTS `after_product_sale_trigger`;
DELIMITER $$
CREATE TRIGGER `after_product_sale_trigger` AFTER INSERT ON `product_sales` FOR EACH ROW BEGIN 
DECLARE
	trans_id INT;
DECLARE
	warehouse_id_val INT;
DECLARE
	product_qty_before INT;
DECLARE
	warehouse_qty_before INT;
DECLARE
	sales_ref_no VARCHAR(50);
DECLARE
	product_type VARCHAR(50);
	-- Obtener el warehouse_id, reference_no y variant_id de la tabla sales
	SELECT
	    id,
	    warehouse_id,
	    reference_no INTO trans_id,
	    warehouse_id_val,
	    sales_ref_no
	FROM sales
	WHERE
	    id = NEW.sale_id FOR
	UPDATE;
	-- Bloquear la fila en la tabla sales
	-- Obtener la cantidad de productos antes de la actualización y el tipo de producto
	SELECT
	    qty,
	    type INTO product_qty_before,
	    product_type
	FROM products
	WHERE
	    id = NEW.product_id FOR
	UPDATE;
	-- Bloquear la fila en la tabla products
	-- Si variant_id es diferente de NULL, obtener la cantidad de product_variants y actualizar
	IF NEW.variant_id IS NOT NULL THEN
	SELECT qty INTO product_qty_before
	FROM product_variants
	WHERE
	    id = NEW.variant_id FOR
	UPDATE;
	-- Bloquear la fila en la tabla product_variants
	-- Verificar si el producto es de tipo digital
	IF product_type NOT IN('digital', 'combo') THEN
	-- Actualizar la cantidad en product_variants
	UPDATE product_variants
	SET
	    qty = qty - NEW.qty
	WHERE
	    id = NEW.variant_id;
END
	IF;
	ELSE
	-- Verificar si el producto es de tipo digital
	IF product_type NOT IN('digital', 'combo') THEN
	-- Actualizar qty en products
	UPDATE products
	SET
	    qty = qty - NEW.qty
	WHERE
	    id = NEW.product_id;
END
	IF;
END
	IF;
	-- Obtener la cantidad en el almacén antes de la actualización
	SELECT qty INTO warehouse_qty_before
	FROM product_warehouse
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_val FOR
	UPDATE;
	-- Bloquear la fila en la tabla product_warehouse
	-- Actualizar la cantidad en la tabla product_warehouse si no es un producto digital
	IF product_type NOT IN('digital', 'combo') THEN
	UPDATE product_warehouse
	SET
	    qty = qty - NEW.qty
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_val;
END
	IF;
	-- Registrar los cambios en la tabla Record
	IF product_type IN ('digital', 'combo') THEN
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, sales_ref_no, 1, product_qty_before, product_qty_before, warehouse_qty_before, warehouse_qty_before
	    );
	ELSE
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, sales_ref_no, 1, product_qty_before, (product_qty_before - NEW.qty), warehouse_qty_before, (
	            warehouse_qty_before - NEW.qty
	        )
	    );
END
	IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_transfer`
--

DROP TABLE IF EXISTS `product_transfer`;
CREATE TABLE `product_transfer` (
  `id` int(10) UNSIGNED NOT NULL,
  `transfer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` double NOT NULL,
  `purchase_unit_id` int(11) NOT NULL,
  `net_unit_cost` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `product_transfer`
--
DROP TRIGGER IF EXISTS `AFTER_PRODUCT_TRANSFER_TRIGGER`;
DELIMITER $$
CREATE TRIGGER `AFTER_PRODUCT_TRANSFER_TRIGGER` AFTER INSERT ON `product_transfer` FOR EACH ROW BEGIN 
DECLARE
	trans_id INT;
DECLARE
	warehouse_id_f INT;
DECLARE
	warehouse_id_t INT;
DECLARE
	product_qty_before INT;
DECLARE
	warehouse_qty_before_f INT;
DECLARE
	warehouse_qty_before_t INT;
DECLARE
	reference_no_v VARCHAR(50);
DECLARE
	product_type VARCHAR(50);
	-- Obtener el warehouse_id y el reference_no de la tabla sales
	SELECT
	    id,
	    from_warehouse_id,
	    to_warehouse_id,
	    reference_no INTO trans_id,
	    warehouse_id_f,
	    warehouse_id_t,
	    reference_no_v
	FROM transfers
	WHERE
	    id = NEW.transfer_id;
	-- FOR UPDATE;
	-- Bloquear la fila en la tabla return
	-- Obtener la cantidad de productos antes de la actualización y el tipo de producto
	SELECT
	    qty,
	    type INTO product_qty_before,
	    product_type
	FROM products
	WHERE
	    id = NEW.product_id;
	-- FOR UPDATE Bloquear la fila en la tabla products
	-- Obtener la cantidad en el almacén antes de la actualización
	SELECT qty INTO warehouse_qty_before_f
	FROM product_warehouse
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_f;
	-- FOR UPDATE; -- Bloquear la fila en la tabla product_warehouse
	SELECT qty INTO warehouse_qty_before_t
	FROM product_warehouse
	WHERE
	    product_id = NEW.product_id
	    AND warehouse_id = warehouse_id_t;
	-- FOR UPDATE; -- Bloquear la fila en la tabla product_warehouse
	IF NEW.variant_id IS NOT NULL THEN
	SELECT qty INTO product_qty_before
	FROM product_variants
	WHERE
	    id = NEW.variant_id;
	-- FOR UPDATE; -- Bloquear la fila en la tabla product_variants
END
	IF;
	-- Registrar los cambios en la tabla Record
	IF product_type = 'digital' THEN
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, NEW.product_id, purchase_ref_no, 4, product_qty_before, product_qty_before, warehouse_qty_before, warehouse_qty_before
	    );
	ELSE
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, product_id, reference_no, transaction_type, product_qty_before, product_qty_after, warehouse_qty_before, warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_f, NEW.product_id, reference_no_v, 4, product_qty_before, product_qty_before, (
	            warehouse_qty_before_f + NEW.qty
	        ), warehouse_qty_before_f
	    ),
	    (
	        trans_id, warehouse_id_t, NEW.product_id, reference_no_v, 4, product_qty_before, product_qty_before, (
	            warehouse_qty_before_t - NEW.qty
	        ), warehouse_qty_before_t
	    );
END
	IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE `product_variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `item_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `additional_price` double DEFAULT NULL,
  `qty` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_warehouse`
--

DROP TABLE IF EXISTS `product_warehouse`;
CREATE TABLE `product_warehouse` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `qty` double NOT NULL,
  `blocked_qty` double DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `puntos_venta`
--

DROP TABLE IF EXISTS `puntos_venta`;
CREATE TABLE `puntos_venta` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_punto_venta` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_punto_venta` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_cuis` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_vigencia_cuis` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `sucursal` int(10) UNSIGNED DEFAULT NULL,
  `correlativo_factura` bigint(20) UNSIGNED DEFAULT NULL,
  `correlativo_alquiler` bigint(20) UNSIGNED DEFAULT NULL,
  `correlativo_servicios_basicos` bigint(20) UNSIGNED DEFAULT NULL,
  `correlativo_nota_debcred` bigint(20) UNSIGNED DEFAULT NULL,
  `modo_contingencia` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `nit_comisionista` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numero_contrato` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_siat` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE `purchases` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `item` int(11) NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_cost` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `order_discount` double DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `paid_amount` double NOT NULL,
  `status` int(11) NOT NULL,
  `payment_status` int(11) NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_product_return`
--

DROP TABLE IF EXISTS `purchase_product_return`;
CREATE TABLE `purchase_product_return` (
  `id` int(10) UNSIGNED NOT NULL,
  `return_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `qty` double NOT NULL,
  `purchase_unit_id` int(11) NOT NULL,
  `net_unit_cost` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `purchase_product_return`
--
DROP TRIGGER IF EXISTS `BEFORE_purchase_product_return_TRIGGER`;
DELIMITER $$
CREATE TRIGGER `BEFORE_purchase_product_return_TRIGGER` BEFORE INSERT ON `purchase_product_return` FOR EACH ROW BEGIN 
	DECLARE
		trans_id INT;
	DECLARE
		warehouse_id_val INT;
	DECLARE
		product_qty_before INT;
	DECLARE
		warehouse_qty_before INT;
	DECLARE
		r_purchase_ref_no VARCHAR(50);
	DECLARE
		product_type VARCHAR(50);
		-- Obtener el warehouse_id y el reference_no de la tabla
		SELECT
	    id,
	    warehouse_id,
	    reference_no INTO trans_id,
	    warehouse_id_val,
	    r_purchase_ref_no
	FROM return_purchases
	WHERE
	    id = NEW.return_id;
	-- FOR UPDATE;
	-- Bloquear la fila en la tabla return
	-- Obtener la cantidad de productos antes de la actualización y el tipo de producto
	SELECT
	    qty,
	    type INTO product_qty_before,
	    product_type
	 FROM products
	 WHERE id = NEW.product_id;
		-- FOR UPDATE Bloquear la fila en la tabla products
		-- Obtener la cantidad en el almacén antes de la actualización
	 SELECT qty INTO warehouse_qty_before
		FROM product_warehouse
		WHERE
			product_id = NEW.product_id
			AND warehouse_id = warehouse_id_val;
		-- FOR UPDATE; -- Bloquear la fila en la tabla product_warehouse
		IF NEW.variant_id IS NOT NULL THEN
		SELECT qty INTO product_qty_before
		FROM product_variants
		WHERE
			id = NEW.variant_id;
	-- FOR UPDATE; -- Bloquear la fila en la tabla product_variants
END
	IF;
	-- Registrar los cambios en la tabla Record
	INSERT INTO
	    record (
	        transaction_id, warehouse_id, 
            product_id, reference_no, 
            transaction_type, 
            product_qty_before, 
            product_qty_after, 
            warehouse_qty_before, 
            warehouse_qty_after
	    )
	VALUES (
	        trans_id, warehouse_id_val, 
            NEW.product_id, r_purchase_ref_no, 
            6,  (product_qty_before + NEW.qty),
            product_qty_before, 
            (warehouse_qty_before + NEW.qty), 
            warehouse_qty_before
	    );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
CREATE TABLE `quotations` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_price` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `order_discount` double DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `quotation_status` int(11) NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `record`
--

DROP TABLE IF EXISTS `record`;
CREATE TABLE `record` (
  `id` int(11) NOT NULL,
  `transaction_id` int(10) UNSIGNED NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `reference_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_type` smallint(6) NOT NULL,
  `product_qty_before` int(11) DEFAULT NULL,
  `product_qty_after` int(11) DEFAULT NULL,
  `warehouse_qty_before` int(11) DEFAULT NULL,
  `warehouse_qty_after` int(11) DEFAULT NULL,
  `cb_cost` decimal(10,0) NOT NULL DEFAULT 0,
  `action_taken_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registros_sincronizacion_siat`
--

DROP TABLE IF EXISTS `registros_sincronizacion_siat`;
CREATE TABLE `registros_sincronizacion_siat` (
  `id` int(10) UNSIGNED NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operacion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 0,
  `usuario_alta` int(10) UNSIGNED DEFAULT NULL,
  `usuario_modificacion` int(10) UNSIGNED DEFAULT NULL,
  `orden` int(10) UNSIGNED DEFAULT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

DROP TABLE IF EXISTS `returns`;
CREATE TABLE `returns` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_sale_id` int(11) DEFAULT NULL,
  `cuf` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_price` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staff_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_purchases`
--

DROP TABLE IF EXISTS `return_purchases`;
CREATE TABLE `return_purchases` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_cost` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staff_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `blocked_modules` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_no` int(11) DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `biller_id` int(11) DEFAULT NULL,
  `item` int(11) NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_price` double NOT NULL,
  `grand_total` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `order_discount` double DEFAULT 0,
  `total_tips` double DEFAULT 0,
  `coupon_id` int(11) DEFAULT NULL,
  `coupon_discount` double DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `sale_status` int(11) NOT NULL,
  `payment_status` int(11) NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_amount` double DEFAULT NULL,
  `sale_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staff_note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_sell` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_import_temp`
--

DROP TABLE IF EXISTS `sales_import_temp`;
CREATE TABLE `sales_import_temp` (
  `id` int(11) NOT NULL,
  `facturamasiva_id` int(11) NOT NULL,
  `NRO_FACT` int(11) NOT NULL,
  `codigoSucursal` int(11) NOT NULL,
  `nombreRazonSocial` varchar(255) NOT NULL,
  `codigoTipoDocumentoIdentidad` int(11) NOT NULL,
  `numeroDocumento` varchar(100) NOT NULL,
  `complemento` varchar(50) DEFAULT NULL,
  `codigoCliente` varchar(50) NOT NULL,
  `mes` varchar(50) NOT NULL,
  `gestion` int(11) NOT NULL,
  `ciudad` varchar(250) NOT NULL,
  `zona` varchar(250) NOT NULL,
  `numero_medidor` varchar(100) NOT NULL,
  `domicilio_cliente` varchar(255) DEFAULT NULL,
  `codigoMetodoPago` int(11) NOT NULL,
  `numeroTarjeta` varchar(100) DEFAULT NULL,
  `montoTotal` double NOT NULL,
  `montoTotalSujetoIva` double NOT NULL,
  `consumoPeriodo` int(11) NOT NULL,
  `beneficiarioLey1886` int(11) DEFAULT NULL,
  `montoDescuentoLey1886` double NOT NULL,
  `montoDescuentoTarifaDignidad` double NOT NULL,
  `tasaAseo` double NOT NULL,
  `tasaAlumbrado` double NOT NULL,
  `otrasTasas` double NOT NULL,
  `ajusteNoSujetoIva` double NOT NULL,
  `detalleAjusteNoSujetoIva` varchar(255) DEFAULT NULL,
  `ajusteSujetoIva` double NOT NULL,
  `detalleAjusteSujetoIva` varchar(255) DEFAULT NULL,
  `otrosPagosNoSujetoIva` double NOT NULL,
  `descuentoAdicional` double NOT NULL,
  `codigoExcepcion` int(11) NOT NULL,
  `cafc` varchar(255) DEFAULT NULL,
  `usuario` varchar(100) NOT NULL,
  `codigoProducto` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `cantidad` double NOT NULL,
  `precioUnitario` double NOT NULL,
  `montoDescuento` double NOT NULL,
  `subTotal` double NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `lectant` varchar(100) DEFAULT NULL,
  `f_lectAnt` varchar(100) DEFAULT NULL,
  `lectact` varchar(100) DEFAULT NULL,
  `f_lectAct` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shift_employee`
--

DROP TABLE IF EXISTS `shift_employee`;
CREATE TABLE `shift_employee` (
  `id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `siat_actividades_economicas`
--

DROP TABLE IF EXISTS `siat_actividades_economicas`;
CREATE TABLE `siat_actividades_economicas` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_caeb` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_actividad` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siat_cufd`
--

DROP TABLE IF EXISTS `siat_cufd`;
CREATE TABLE `siat_cufd` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_cufd` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_control` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `fecha_vigencia` datetime NOT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED DEFAULT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siat_documento_sector`
--

DROP TABLE IF EXISTS `siat_documento_sector`;
CREATE TABLE `siat_documento_sector` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_actividad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_documento_sector` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_documento_sector` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siat_leyendas_facturas`
--

DROP TABLE IF EXISTS `siat_leyendas_facturas`;
CREATE TABLE `siat_leyendas_facturas` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_actividad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_leyenda` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siat_parametricas_varios`
--

DROP TABLE IF EXISTS `siat_parametricas_varios`;
CREATE TABLE `siat_parametricas_varios` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_clasificador` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_clasificador` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siat_producto_servicios`
--

DROP TABLE IF EXISTS `siat_producto_servicios`;
CREATE TABLE `siat_producto_servicios` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_actividad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_producto` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_producto` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_counts`
--

DROP TABLE IF EXISTS `stock_counts`;
CREATE TABLE `stock_counts` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `category_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_adjusted` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sucursal_siat`
--

DROP TABLE IF EXISTS `sucursal_siat`;
CREATE TABLE `sucursal_siat` (
  `id` int(10) UNSIGNED NOT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_sucursal` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domicilio_tributario` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad_municipio` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_autorizacion_facturacion` int(10) UNSIGNED DEFAULT NULL,
  `departamento` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE `suppliers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vat_number` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

DROP TABLE IF EXISTS `taxes`;
CREATE TABLE `taxes` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tip`
--

DROP TABLE IF EXISTS `tip`;
CREATE TABLE `tip` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `presale_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `amount` double NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

DROP TABLE IF EXISTS `transfers`;
CREATE TABLE `transfers` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `from_warehouse_id` int(11) NOT NULL,
  `to_warehouse_id` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `total_qty` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_cost` double NOT NULL,
  `shipping_cost` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_request_logs`
--

DROP TABLE IF EXISTS `transfer_request_logs`;
CREATE TABLE `transfer_request_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transfer_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
CREATE TABLE `units` (
  `id` int(10) UNSIGNED NOT NULL,
  `unit_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_unit` int(11) DEFAULT NULL,
  `operator` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operation_value` double DEFAULT NULL,
  `codigo_clasificador_siat` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `url_ws`
--

DROP TABLE IF EXISTS `url_ws`;
CREATE TABLE `url_ws` (
  `id` int(10) UNSIGNED NOT NULL,
  `ambiente` int(10) UNSIGNED NOT NULL,
  `nombre_servicio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ruta_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_url` int(10) UNSIGNED NOT NULL,
  `uso_modalidad` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `biller_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_category`
--

DROP TABLE IF EXISTS `user_category`;
CREATE TABLE `user_category` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `variants`
--

DROP TABLE IF EXISTS `variants`;
CREATE TABLE `variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
CREATE TABLE `warehouses` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sucursal_id` int(10) UNSIGNED DEFAULT NULL,
  `sucursal_siat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `websockets_statistics_entries`
--

DROP TABLE IF EXISTS `websockets_statistics_entries`;
CREATE TABLE `websockets_statistics_entries` (
  `id` int(10) UNSIGNED NOT NULL,
  `app_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `peak_connection_count` int(11) NOT NULL,
  `websocket_message_count` int(11) NOT NULL,
  `api_message_count` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure for view `kardex`
--
DROP TABLE IF EXISTS `kardex`;

DROP VIEW IF EXISTS `kardex`;
CREATE ALGORITHM=UNDEFINED DEFINER=`admingisulsrl`@`localhost` SQL SECURITY DEFINER VIEW `kardex`  AS SELECT `record`.`transaction_id` AS `transaction_id`, `record`.`product_id` AS `product_id`, `products`.`name` AS `product`, CASE WHEN `record`.`transaction_type` = 0 THEN 'INIT' WHEN `record`.`transaction_type` = 1 THEN 'VENTA' WHEN `record`.`transaction_type` = 2 THEN 'COMPRA' WHEN `record`.`transaction_type` = 3 THEN 'RETURN' WHEN `record`.`transaction_type` = 4 THEN 'TRANSFER' WHEN `record`.`transaction_type` = 5 THEN 'AJUSTE' WHEN `record`.`transaction_type` = 6 THEN 'COMPRA RETURN' ELSE 'Otro' END AS `transaction_type`, `warehouses`.`id` AS `warehouse_id`, `warehouses`.`name` AS `warehouse`, `record`.`warehouse_qty_before` AS `warehouse_qty_before`, `record`.`warehouse_qty_after` AS `warehouse_qty_after`, CASE WHEN `record`.`warehouse_qty_after` - `record`.`warehouse_qty_before` > 0 THEN `record`.`warehouse_qty_after`- `record`.`warehouse_qty_before` ELSE 0 END AS `entrada`, CASE WHEN `record`.`warehouse_qty_before` - `record`.`warehouse_qty_after` > 0 THEN `record`.`warehouse_qty_before`- `record`.`warehouse_qty_after` ELSE 0 END AS `salida`, CASE WHEN `record`.`transaction_type` = 0 THEN `record`.`warehouse_qty_after` WHEN `record`.`transaction_type` = 1 THEN `product_sales`.`qty` WHEN `record`.`transaction_type` = 2 THEN `product_purchases`.`qty` WHEN `record`.`transaction_type` = 3 THEN `product_returns`.`qty` WHEN `record`.`transaction_type` = 4 THEN `product_transfer`.`qty` WHEN `record`.`transaction_type` = 5 THEN `product_adjustments`.`qty` WHEN `record`.`transaction_type` = 6 THEN `purchase_product_return`.`qty` ELSE NULL END AS `qty`, CASE WHEN `record`.`transaction_type` = 0 THEN `products`.`cost` WHEN `record`.`transaction_type` = 1 THEN if(`record`.`cb_cost` > 0,`record`.`cb_cost`,`product_sales`.`cost`) WHEN `record`.`transaction_type` = 2 THEN `product_purchases`.`net_unit_cost` WHEN `record`.`transaction_type` = 3 THEN `products`.`cost` WHEN `record`.`transaction_type` = 4 THEN `product_transfer`.`net_unit_cost` WHEN `record`.`transaction_type` = 5 THEN `products`.`cost` WHEN `record`.`transaction_type` = 6 THEN `purchase_product_return`.`net_unit_cost` ELSE NULL END AS `cost`, CASE WHEN `record`.`transaction_type` = 0 THEN format(`record`.`warehouse_qty_after` * `products`.`cost`,2) WHEN `record`.`transaction_type` = 1 THEN format(`product_sales`.`qty` * `product_sales`.`cost`,2) WHEN `record`.`transaction_type` = 2 THEN format(`product_purchases`.`qty` * `product_purchases`.`net_unit_cost`,2) WHEN `record`.`transaction_type` = 3 THEN format(`products`.`cost` * `product_returns`.`qty`,2) WHEN `record`.`transaction_type` = 4 THEN format(`product_transfer`.`qty` * `product_transfer`.`net_unit_cost`,2) WHEN `record`.`transaction_type` = 5 THEN format(`products`.`cost` * `product_adjustments`.`qty`,2) WHEN `record`.`transaction_type` = 6 THEN format(`purchase_product_return`.`qty` * `purchase_product_return`.`net_unit_cost`,2) ELSE NULL END AS `total_cost`, CASE WHEN `record`.`transaction_type` = 4 AND `record`.`warehouse_qty_before` < `record`.`warehouse_qty_after` THEN `transfers`.`from_warehouse_id` WHEN `record`.`transaction_type` = 4 AND `record`.`warehouse_qty_before` > `record`.`warehouse_qty_after` THEN `transfers`.`to_warehouse_id` ELSE NULL END AS `from_to`, `record`.`action_taken_at` AS `date` FROM ((((((((((((((`record` join `products` on(`products`.`id` = `record`.`product_id`)) join `warehouses` on(`warehouses`.`id` = `record`.`warehouse_id`)) left join `product_sales` on(`record`.`transaction_type` = 1 and `record`.`transaction_id` = `product_sales`.`sale_id` and `record`.`product_id` = `product_sales`.`product_id`)) left join `product_purchases` on(`record`.`transaction_type` = 2 and `record`.`transaction_id` = `product_purchases`.`purchase_id` and `record`.`product_id` = `product_purchases`.`product_id`)) left join `product_returns` on(`record`.`transaction_type` = 3 and `record`.`product_id` = `product_returns`.`product_id` and `record`.`transaction_id` = `product_returns`.`return_id`)) left join `product_transfer` on(`record`.`transaction_type` = 4 and `record`.`transaction_id` = `product_transfer`.`transfer_id` and `record`.`product_id` = `product_transfer`.`product_id`)) left join `product_adjustments` on(`record`.`transaction_type` = 5 and `record`.`transaction_id` = `product_adjustments`.`adjustment_id` and `record`.`product_id` = `product_adjustments`.`product_id`)) left join `purchase_product_return` on(`record`.`transaction_type` = 6 and `record`.`transaction_id` = `purchase_product_return`.`return_id` and `record`.`product_id` = `purchase_product_return`.`product_id`)) left join `sales` on(`record`.`transaction_type` = 1 and `record`.`transaction_id` = `sales`.`id`)) left join `purchases` on(`record`.`transaction_type` = 2 and `record`.`transaction_id` = `purchases`.`id`)) left join `returns` on(`record`.`transaction_type` = 3 and `record`.`transaction_id` = `returns`.`id`)) left join `transfers` on(`record`.`transaction_type` = 4 and `record`.`transaction_id` = `transfers`.`id`)) left join `adjustments` on(`record`.`transaction_type` = 5 and `record`.`transaction_id` = `adjustments`.`id`)) left join `return_purchases` on(`record`.`transaction_type` = 6 and `record`.`transaction_id` = `return_purchases`.`id`))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account_method_pay`
--
ALTER TABLE `account_method_pay`
  ADD PRIMARY KEY (`id`),
  ADD KEY `union` (`account_id`,`methodpay_id`);

--
-- Indexes for table `adjustments`
--
ALTER TABLE `adjustments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `adjustment_accounts`
--
ALTER TABLE `adjustment_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attention_shift`
--
ALTER TABLE `attention_shift`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `autorizacion_facturacion`
--
ALTER TABLE `autorizacion_facturacion`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billers`
--
ALTER TABLE `billers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `biller_warehouses`
--
ALTER TABLE `biller_warehouses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cashier`
--
ALTER TABLE `cashier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `control_contingencia`
--
ALTER TABLE `control_contingencia`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `control_contingencia_paquetes`
--
ALTER TABLE `control_contingencia_paquetes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credenciales_cafc`
--
ALTER TABLE `credenciales_cafc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_company`
--
ALTER TABLE `customer_company`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer` (`customer_id`);

--
-- Indexes for table `customer_groups`
--
ALTER TABLE `customer_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_nit`
--
ALTER TABLE `customer_nit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_sales`
--
ALTER TABLE `customer_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `factura_masiva`
--
ALTER TABLE `factura_masiva`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `factura_masiva_paquetes`
--
ALTER TABLE `factura_masiva_paquetes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `garantes`
--
ALTER TABLE `garantes`
  ADD PRIMARY KEY (`id_garante`);

--
-- Indexes for table `general_settings`
--
ALTER TABLE `general_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gift_cards`
--
ALTER TABLE `gift_cards`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gift_card_recharges`
--
ALTER TABLE `gift_card_recharges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hrm_settings`
--
ALTER TABLE `hrm_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lote_sales`
--
ALTER TABLE `lote_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `method_payments`
--
ALTER TABLE `method_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `money_transfers`
--
ALTER TABLE `money_transfers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_access_tokens`
--
ALTER TABLE `oauth_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_access_tokens_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_auth_codes`
--
ALTER TABLE `oauth_auth_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_auth_codes_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_clients_user_id_index` (`user_id`);

--
-- Indexes for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oauth_refresh_tokens`
--
ALTER TABLE `oauth_refresh_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_with_cheque`
--
ALTER TABLE `payment_with_cheque`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_with_credit_card`
--
ALTER TABLE `payment_with_credit_card`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_with_gift_card`
--
ALTER TABLE `payment_with_gift_card`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_with_paypal`
--
ALTER TABLE `payment_with_paypal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_with_receivable`
--
ALTER TABLE `payment_with_receivable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id_persona`);

--
-- Indexes for table `pos_setting`
--
ALTER TABLE `pos_setting`
  ADD UNIQUE KEY `pos_setting_id_unique` (`id`);

--
-- Indexes for table `pre_sale`
--
ALTER TABLE `pre_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `printers`
--
ALTER TABLE `printers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_adjustments`
--
ALTER TABLE `product_adjustments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_associated`
--
ALTER TABLE `product_associated`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_courtesy_id` (`product_courtesy_id`),
  ADD KEY `product_associated_id` (`product_associated_id`);

--
-- Indexes for table `product_lot`
--
ALTER TABLE `product_lot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compras` (`purchase_id`);

--
-- Indexes for table `product_pre_sale`
--
ALTER TABLE `product_pre_sale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_purchases`
--
ALTER TABLE `product_purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_quotation`
--
ALTER TABLE `product_quotation`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_returns`
--
ALTER TABLE `product_returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_sales`
--
ALTER TABLE `product_sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_transfer`
--
ALTER TABLE `product_transfer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_warehouse`
--
ALTER TABLE `product_warehouse`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `puntos_venta`
--
ALTER TABLE `puntos_venta`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_product_return`
--
ALTER TABLE `purchase_product_return`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `record`
--
ALTER TABLE `record`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registros_sincronizacion_siat`
--
ALTER TABLE `registros_sincronizacion_siat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `return_purchases`
--
ALTER TABLE `return_purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_import_temp`
--
ALTER TABLE `sales_import_temp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facturamasiva_id` (`facturamasiva_id`);

--
-- Indexes for table `shift_employee`
--
ALTER TABLE `shift_employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siat_actividades_economicas`
--
ALTER TABLE `siat_actividades_economicas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siat_cufd`
--
ALTER TABLE `siat_cufd`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siat_documento_sector`
--
ALTER TABLE `siat_documento_sector`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siat_leyendas_facturas`
--
ALTER TABLE `siat_leyendas_facturas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siat_parametricas_varios`
--
ALTER TABLE `siat_parametricas_varios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siat_producto_servicios`
--
ALTER TABLE `siat_producto_servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_counts`
--
ALTER TABLE `stock_counts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sucursal_siat`
--
ALTER TABLE `sucursal_siat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tip`
--
ALTER TABLE `tip`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transfer_request_logs`
--
ALTER TABLE `transfer_request_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_transfer` (`transfer_id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `url_ws`
--
ALTER TABLE `url_ws`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_category`
--
ALTER TABLE `user_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `variants`
--
ALTER TABLE `variants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sucursal_id` (`sucursal_id`);

--
-- Indexes for table `websockets_statistics_entries`
--
ALTER TABLE `websockets_statistics_entries`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_method_pay`
--
ALTER TABLE `account_method_pay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `adjustments`
--
ALTER TABLE `adjustments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `adjustment_accounts`
--
ALTER TABLE `adjustment_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attention_shift`
--
ALTER TABLE `attention_shift`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `autorizacion_facturacion`
--
ALTER TABLE `autorizacion_facturacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `billers`
--
ALTER TABLE `billers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `biller_warehouses`
--
ALTER TABLE `biller_warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cashier`
--
ALTER TABLE `cashier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `control_contingencia`
--
ALTER TABLE `control_contingencia`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `control_contingencia_paquetes`
--
ALTER TABLE `control_contingencia_paquetes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `credenciales_cafc`
--
ALTER TABLE `credenciales_cafc`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_company`
--
ALTER TABLE `customer_company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_groups`
--
ALTER TABLE `customer_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_nit`
--
ALTER TABLE `customer_nit`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_sales`
--
ALTER TABLE `customer_sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `factura_masiva`
--
ALTER TABLE `factura_masiva`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `factura_masiva_paquetes`
--
ALTER TABLE `factura_masiva_paquetes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `garantes`
--
ALTER TABLE `garantes`
  MODIFY `id_garante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_settings`
--
ALTER TABLE `general_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gift_cards`
--
ALTER TABLE `gift_cards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gift_card_recharges`
--
ALTER TABLE `gift_card_recharges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrm_settings`
--
ALTER TABLE `hrm_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lote_sales`
--
ALTER TABLE `lote_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `method_payments`
--
ALTER TABLE `method_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `money_transfers`
--
ALTER TABLE `money_transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_clients`
--
ALTER TABLE `oauth_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oauth_personal_access_clients`
--
ALTER TABLE `oauth_personal_access_clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_with_cheque`
--
ALTER TABLE `payment_with_cheque`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_with_credit_card`
--
ALTER TABLE `payment_with_credit_card`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_with_gift_card`
--
ALTER TABLE `payment_with_gift_card`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_with_paypal`
--
ALTER TABLE `payment_with_paypal`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_with_receivable`
--
ALTER TABLE `payment_with_receivable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personas`
--
ALTER TABLE `personas`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pre_sale`
--
ALTER TABLE `pre_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printers`
--
ALTER TABLE `printers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_adjustments`
--
ALTER TABLE `product_adjustments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_associated`
--
ALTER TABLE `product_associated`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_lot`
--
ALTER TABLE `product_lot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_pre_sale`
--
ALTER TABLE `product_pre_sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_purchases`
--
ALTER TABLE `product_purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_quotation`
--
ALTER TABLE `product_quotation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_returns`
--
ALTER TABLE `product_returns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sales`
--
ALTER TABLE `product_sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_transfer`
--
ALTER TABLE `product_transfer`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_warehouse`
--
ALTER TABLE `product_warehouse`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `puntos_venta`
--
ALTER TABLE `puntos_venta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_product_return`
--
ALTER TABLE `purchase_product_return`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `record`
--
ALTER TABLE `record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `registros_sincronizacion_siat`
--
ALTER TABLE `registros_sincronizacion_siat`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_purchases`
--
ALTER TABLE `return_purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_import_temp`
--
ALTER TABLE `sales_import_temp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shift_employee`
--
ALTER TABLE `shift_employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siat_actividades_economicas`
--
ALTER TABLE `siat_actividades_economicas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siat_cufd`
--
ALTER TABLE `siat_cufd`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siat_documento_sector`
--
ALTER TABLE `siat_documento_sector`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siat_leyendas_facturas`
--
ALTER TABLE `siat_leyendas_facturas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siat_parametricas_varios`
--
ALTER TABLE `siat_parametricas_varios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siat_producto_servicios`
--
ALTER TABLE `siat_producto_servicios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_counts`
--
ALTER TABLE `stock_counts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sucursal_siat`
--
ALTER TABLE `sucursal_siat`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tip`
--
ALTER TABLE `tip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_request_logs`
--
ALTER TABLE `transfer_request_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `url_ws`
--
ALTER TABLE `url_ws`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_category`
--
ALTER TABLE `user_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `variants`
--
ALTER TABLE `variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websockets_statistics_entries`
--
ALTER TABLE `websockets_statistics_entries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transfer_request_logs`
--
ALTER TABLE `transfer_request_logs`
  ADD CONSTRAINT `fk_transfer` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
