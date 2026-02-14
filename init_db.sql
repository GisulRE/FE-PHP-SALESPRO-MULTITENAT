-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 06, 2022 at 08:38 PM
-- Server version: 5.7.24
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erp_pos`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_balance` double DEFAULT NULL,
  `total_balance` double NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `account_no`, `name`, `initial_balance`, `total_balance`, `note`, `is_default`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '1', 'Caja Roja', 6040, 6040, 'Caja 1', 1, 1, '2018-12-18 02:58:02', '2022-01-11 20:49:04'),
(2, 'BCO', 'Banco', 0, 0, NULL, 0, 0, '2021-08-16 06:28:34', '2021-11-29 16:01:14'),
(3, 'CC', 'CAJA CHICA', 0, 0, NULL, NULL, 0, '2021-11-29 15:44:34', '2021-12-07 00:43:08'),
(4, 'CJ', 'CAJA GENERAL', 0, 0, NULL, 0, 0, '2021-11-29 15:45:08', '2021-12-13 16:52:33'),
(5, 'BCO', 'BANCO', 2657.5, 2657.5, NULL, NULL, 1, '2021-11-29 16:01:41', '2021-12-13 16:31:04'),
(6, 'VP', 'Caja Vapes', 620, 620, NULL, NULL, 1, '2021-12-13 16:32:23', '2021-12-22 02:56:28'),
(7, 'SC', 'Santa Chill', NULL, 0, NULL, NULL, 0, '2021-12-13 16:33:32', '2021-12-16 01:41:41'),
(8, 'LC', 'La Creme', NULL, 0, NULL, NULL, 0, '2021-12-13 16:33:46', '2021-12-16 01:41:34'),
(9, '$', 'Dolares', 1380, 1380, '$200', NULL, 1, '2021-12-13 16:49:09', '2021-12-13 16:50:23'),
(10, '125', 'test', 1000, 1000, 'test', NULL, 0, '2021-12-22 20:10:05', '2021-12-22 20:11:16'),
(11, 'Paypal', 'test', 1500, 1500, NULL, NULL, 0, '2021-12-22 20:12:14', '2021-12-22 20:34:54'),
(12, '2', 'Caja Juan', 1000, 1000, NULL, NULL, 1, '2022-01-06 00:33:26', '2022-01-06 00:33:26'),
(13, 'BCO M', 'BANCO Mercantil', 1000, 1000, NULL, NULL, 1, '2022-01-10 21:33:09', '2022-01-10 21:33:09'),
(14, 'BCO G', 'BANCO Ganadero', 1000, 1000, NULL, NULL, 1, '2022-01-10 21:33:44', '2022-01-10 21:33:44'),
(15, 'CXC', 'Cuenta por Cobrar', 0, 0, 'test para metodo de pago por cobrar', NULL, 1, '2022-02-24 21:54:18', '2022-02-24 21:54:43');

-- --------------------------------------------------------

--
-- Table structure for table `account_method_pay`
--

CREATE TABLE `account_method_pay` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `methodpay_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `account_method_pay`
--

INSERT INTO `account_method_pay` (`id`, `account_id`, `methodpay_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 5, 4, 1, '2021-12-22 16:20:11', '2021-12-22 16:20:11'),
(2, 5, 6, 1, '2021-12-22 16:20:11', '2021-12-22 16:20:11'),
(5, 6, 7, 1, '2021-12-22 16:29:19', '2021-12-22 16:29:19'),
(6, 6, 5, 1, '2021-12-22 16:29:19', '2021-12-22 16:29:19'),
(7, 11, 8, 0, '2021-12-22 20:35:08', '2021-12-22 20:35:08');

-- --------------------------------------------------------

--
-- Table structure for table `adjustments`
--

CREATE TABLE `adjustments` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_qty` double NOT NULL,
  `item` int(11) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `adjustments`
--

INSERT INTO `adjustments` (`id`, `reference_no`, `warehouse_id`, `document`, `total_qty`, `item`, `note`, `created_at`, `updated_at`) VALUES
(5, 'adr-20220426-050320', 1, NULL, 7, 2, 'test with variant and no variant update', '2022-04-26 21:03:20', '2022-04-26 22:00:23'),
(8, 'adr-20220426-101104', 1, NULL, 0, 1, NULL, '2022-04-27 02:11:04', '2022-04-27 02:11:04');

-- --------------------------------------------------------

--
-- Table structure for table `adjustment_accounts`
--

CREATE TABLE `adjustment_accounts` (
  `id` int(11) NOT NULL,
  `reference_no` varchar(20) NOT NULL,
  `account_id` int(11) NOT NULL,
  `note` varchar(254) NOT NULL,
  `amount` double NOT NULL DEFAULT '0',
  `type_adjustment` varchar(10) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `adjustment_accounts`
--

INSERT INTO `adjustment_accounts` (`id`, `reference_no`, `account_id`, `note`, `amount`, `type_adjustment`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ADJ-00000001', 1, 'cambio sobrante test 1', 3.5, 'ING', 1, '2021-12-28 21:44:57', '2021-12-28 21:41:18'),
(2, 'ADJ-00000002', 1, 'Error de cambios', 5, 'EGR', 1, '2021-12-28 21:55:21', '2021-12-28 21:55:21'),
(3, 'ADJ-00000003', 1, '', 5000, '', 0, '2021-12-30 04:10:34', '2021-12-30 04:10:34'),
(4, 'ADJ-00000004', 1, '', 2500, '', 0, '2021-12-30 04:32:58', '2021-12-30 04:32:58'),
(5, 'ADJ-00000005', 1, '', 2500, '', 0, '2021-12-30 04:32:58', '2021-12-30 04:32:58'),
(6, 'ADJ-00000006', 1, '', 2500, '', 0, '2021-12-30 04:32:44', '2021-12-30 04:32:44'),
(7, 'ADJ-00000007', 1, 'test con ajax en form normal', 1, 'ING', 1, '2021-12-30 16:08:43', '2021-12-30 16:08:43'),
(8, 'ADJ-00000008', 1, 'test con ajax en form normal', 1, 'ING', 0, '2021-12-30 16:10:26', '2021-12-30 16:10:26'),
(9, 'ADJ-00000009', 1, 'perdida de cambio reposicion test ajax', 2, 'EGR', 1, '2021-12-30 19:28:30', '2021-12-30 19:28:30'),
(10, 'ADJ-00000010', 1, 'test ajax false', 2, 'ING', 1, '2021-12-30 19:30:27', '2021-12-30 19:30:27'),
(11, 'ADJ-00000011', 1, 'test ajax false', 2, 'EGR', 1, '2021-12-30 19:31:30', '2021-12-30 19:31:30'),
(12, 'ADJ-00000012', 1, 'test ajax true', 2, 'ING', 1, '2021-12-30 19:32:30', '2021-12-30 19:32:30'),
(13, 'ADJ-00000013', 1, 'test ajax true', 5, 'ING', 1, '2021-12-30 19:36:01', '2021-12-30 19:36:01'),
(14, 'ADJ-00000014', 1, 'test ajax true', 10, 'ING', 1, '2021-12-30 19:38:51', '2021-12-30 19:38:51'),
(15, 'ADJ-00000015', 1, 'test ajax true', 15, 'ING', 1, '2021-12-30 19:40:17', '2021-12-30 19:40:17'),
(16, 'ADJ-00000016', 1, 'test', 0.5, 'EGR', 0, '2022-01-05 04:27:42', '2022-01-05 04:27:42'),
(17, 'ADJ-00000017', 1, 'test', 50, 'ING', 0, '2022-01-05 04:51:23', '2022-01-05 04:51:23'),
(18, 'ADJ-00000018', 9, 'test', 50, 'EGR', 0, '2022-01-05 04:51:23', '2022-01-05 04:51:23'),
(19, 'ADJ-00000019', 1, 'test', 0, 'EGR', 0, '2022-01-05 04:51:41', '2022-01-05 04:51:41'),
(20, 'ADJ-00000020', 1, 'test', 0.5, 'EGR', 0, '2022-01-05 04:55:27', '2022-01-05 04:55:27'),
(21, 'ADJ-00000021', 1, 'perdida de cambio', 0.5, 'EGR', 1, '2022-01-05 14:46:24', '2022-01-05 14:46:24');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `checkin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkout` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `date`, `employee_id`, `user_id`, `checkin`, `checkout`, `status`, `note`, `created_at`, `updated_at`) VALUES
(2, '2021-12-07', 2, 1, '9:00am', '7:00pm', 1, NULL, '2021-12-08 01:28:27', '2021-12-08 01:28:27'),
(3, '2021-12-07', 3, 1, '9:00am', '7:00pm', 1, NULL, '2021-12-08 01:28:27', '2021-12-08 01:28:27'),
(4, '2021-12-07', 4, 1, '9:00am', '7:00pm', 1, NULL, '2021-12-08 01:28:27', '2021-12-08 01:28:27'),
(5, '2021-12-07', 5, 1, '9:00am', '7:00pm', 1, NULL, '2021-12-08 01:28:27', '2021-12-08 01:28:27');

-- --------------------------------------------------------

--
-- Table structure for table `billers`
--

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
  `account_id_qr` int(11) DEFAULT NULL,
  `account_id_deposito` int(11) DEFAULT NULL,
  `account_id_cheque` int(11) DEFAULT NULL,
  `account_id_receivable` int(11) NOT NULL,
  `account_id_giftcard` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `punto_venta_siat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `billers`
--

INSERT INTO `billers` (`id`, `name`, `image`, `company_name`, `vat_number`, `email`, `phone_number`, `address`, `city`, `state`, `postal_code`, `country`, `account_id`, `account_id_tarjeta`, `account_id_qr`, `account_id_deposito`, `account_id_cheque`, `account_id_receivable`, `account_id_giftcard`, `warehouse_id`, `customer_id`, `is_active`, `created_at`, `updated_at`, `punto_venta_siat`) VALUES
(1, 'Rebeca', 'aks.jpg', 'LC*', '31123', 'yousuf@kds.com', '442343324', 'halishahar', 'chittagong', NULL, NULL, 'Bolivia', 1, 5, 5, 13, 13, 15, 1, 1, 18, 1, '2018-05-12 21:49:30', '2022-02-24 21:55:10', NULL),
(2, 'FERNANDO', NULL, 'FV1', NULL, 'fv@gmail.com', '1', '1', '1', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '2021-08-16 07:14:11', '2021-11-29 15:47:44', NULL),
(3, 'lisbeth', NULL, 'fv3', NULL, 'fv3@gmail.com', '3434', '3434', '3434', '334', NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '2021-08-16 07:16:34', '2021-11-29 15:47:38', NULL),
(4, 'raul', NULL, 'FV4', NULL, 'raul@gmail.com', '1', '1', '1', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '2021-08-17 00:16:58', '2021-11-29 15:47:54', NULL),
(5, 'teresa', NULL, 'FV5', NULL, 'teresa@gmail.com', '11', '11', '1', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '2021-08-17 00:17:41', '2021-11-29 15:48:00', NULL),
(6, 'Rolando', NULL, 'La Creme', '1212', 'rolandoescobar78@gmail.com', '343434', 'asdfasd', 'asdfas', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, '2021-12-08 01:46:25', '2021-12-12 20:26:49', NULL),
(7, 'Juan', NULL, 'GIsul', NULL, 'admin@admin.com', '78553485', 'Cbba', 'Cbba', 'BO', NULL, 'Bolivia', 12, 14, 14, 14, 9, 15, 12, 1, 18, 1, '2022-01-06 00:32:15', '2022-02-24 21:55:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `title`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sin Marca', NULL, 0, '2021-08-01 23:28:03', '2021-11-30 16:40:52'),
(2, 'null', NULL, 0, '2021-08-16 05:51:08', '2021-11-30 16:40:52'),
(3, 'Kumbaya', NULL, 1, '2021-11-30 16:40:39', '2021-11-30 16:57:53'),
(4, 'Camel', NULL, 1, '2021-11-30 16:41:15', '2021-11-30 16:41:15'),
(5, 'Chill Pins', NULL, 1, '2021-11-30 16:41:38', '2021-11-30 16:41:38'),
(6, 'Blacksheep', NULL, 1, '2021-11-30 16:42:53', '2021-11-30 16:42:53'),
(7, 'OCB', NULL, 1, '2021-11-30 16:43:31', '2021-11-30 16:43:31'),
(8, 'Lion Rolling Circus', NULL, 1, '2021-11-30 16:43:56', '2021-11-30 16:43:56'),
(9, 'Phillies', NULL, 1, '2021-11-30 16:44:37', '2021-11-30 16:44:37'),
(10, 'Sprite', NULL, 1, '2021-11-30 16:45:25', '2021-11-30 16:45:25'),
(11, 'Coca Cola', NULL, 1, '2021-11-30 16:45:35', '2021-11-30 16:45:35'),
(12, 'Bendita', NULL, 1, '2021-11-30 16:46:06', '2021-11-30 16:46:06'),
(13, 'Bulldog', NULL, 1, '2021-11-30 16:46:39', '2021-11-30 16:46:39'),
(14, 'Raw', NULL, 1, '2021-11-30 16:46:46', '2021-11-30 16:46:46'),
(15, 'Silver Screen', NULL, 1, '2021-11-30 16:47:45', '2021-12-04 19:15:44'),
(16, 'Republica', NULL, 1, '2021-11-30 16:48:16', '2021-11-30 16:48:16'),
(17, 'Cocalero', NULL, 1, '2021-11-30 16:48:26', '2021-11-30 16:48:26'),
(18, 'Yerbasanta', NULL, 1, '2021-11-30 16:49:06', '2021-11-30 16:49:06'),
(19, 'Pueblo', NULL, 1, '2021-11-30 16:50:18', '2021-11-30 16:50:18'),
(20, 'Apache', NULL, 1, '2021-11-30 16:50:26', '2021-11-30 16:50:26'),
(21, 'Arlequin', NULL, 0, '2021-11-30 16:50:56', '2021-12-03 21:53:18'),
(22, 'Mandala', NULL, 1, '2021-11-30 16:51:23', '2021-11-30 16:51:23'),
(23, 'Big Ben', NULL, 1, '2021-11-30 16:51:44', '2021-12-04 19:15:30'),
(24, 'Café Créme', NULL, 1, '2021-11-30 16:52:21', '2021-12-04 19:16:15'),
(25, 'Jer & Rosh', NULL, 1, '2021-11-30 16:53:36', '2021-11-30 16:53:36'),
(26, 'vggg', NULL, 0, '2021-11-30 16:57:22', '2021-11-30 16:57:37'),
(27, 'Harlequin', NULL, 1, '2021-12-04 20:33:39', '2021-12-04 20:33:39'),
(28, 'Coffee Roaster', NULL, 1, '2021-12-06 17:35:39', '2021-12-06 17:35:39'),
(29, 'SM', NULL, 1, '2021-12-12 16:40:20', '2021-12-12 16:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `cashier`
--

CREATE TABLE `cashier` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `note` varchar(200) DEFAULT NULL,
  `amount_start` double NOT NULL DEFAULT '0',
  `amount_end` double DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `cashier`
--

INSERT INTO `cashier` (`id`, `account_id`, `note`, `amount_start`, `amount_end`, `is_active`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'test', 1486.5, 1606.5, 0, '2022-01-03 15:56:53', '2022-01-04 17:30:15', '2022-01-04 21:37:39', '2022-01-04 21:30:15'),
(7, 1, 'Apertura Caja en POS', 1606, NULL, 1, '2022-01-05 10:46:27', NULL, '2022-01-05 14:46:27', '2022-01-05 14:46:27'),
(8, 12, 'Apertura Caja en POS', 1000, NULL, 1, '2022-01-05 21:46:25', NULL, '2022-01-06 01:46:25', '2022-01-06 01:46:25');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image`, `parent_id`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PRODUCTOS', NULL, NULL, 0, '2021-08-02 13:49:59', '2021-11-25 18:33:18'),
(2, 'SERVICIOS BARBERIA', NULL, NULL, 0, '2021-08-02 14:31:18', '2021-11-30 16:10:23'),
(3, 'BAR', NULL, NULL, 0, '2021-08-16 05:53:46', '2021-11-30 16:10:14'),
(4, 'La Creme barberia', NULL, NULL, 1, '2021-11-30 16:11:11', '2021-11-30 16:11:11'),
(5, 'Santa Chill U', NULL, NULL, 1, '2021-11-30 16:13:02', '2022-03-23 00:36:27'),
(6, 'Foods', NULL, NULL, 1, '2022-02-21 21:47:02', '2022-02-21 21:47:02');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

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
-- Table structure for table `customers`
--

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
  `credit` double NOT NULL DEFAULT '0',
  `is_credit` tinyint(1) NOT NULL DEFAULT '0',
  `price_type` int(11) NOT NULL DEFAULT '0' COMMENT '0 = default, 1 = price a, 2 = price b, 3 = price c',
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `customer_group_id`, `name`, `company_name`, `email`, `phone_number`, `tax_no`, `address`, `city`, `state`, `postal_code`, `country`, `deposit`, `expense`, `credit`, `is_credit`, `price_type`, `is_active`, `created_at`, `updated_at`) VALUES
(18, 1, 'Clientes Varios', NULL, 'cliente@gmail.com', '650222222', NULL, 'barrio', 'Santa Cruz', NULL, NULL, NULL, NULL, 40, 0, 0, 0, 1, '2021-04-19 17:27:01', '2022-02-24 21:31:18'),
(19, 1, 'FARMACIA CLAUDIA', 'FARMACIA CLAUDIA', '', '', NULL, 'KM 6 DVG', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(20, 1, 'FARMACIA BETHESDA', 'FARMACIA BETHESDA', '', '', NULL, 'KM 6', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(21, 1, 'FARMACIA NAIDI', 'FARMACIA NAIDI', '', '', NULL, 'KM 6', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(22, 1, 'FARMACIA CACERES', 'FARMACIA CACERES', '', '', NULL, '', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(23, 1, 'FARMACIA MARLY', 'FARMACIA MARLY', '', '', NULL, 'KM 6 DOBLE VIA A LA GUARDIA', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(24, 1, 'FARMACIA FRANCO', 'FARMACIA FRANCO', '', '', NULL, '3 ANILLO FRENTE AL ONCOLOGICO', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(25, 1, 'FARMACIA PRO VIDA', 'FARMACIA PRO VIDA', '', '', NULL, 'VILLA 1RO DE MAYO', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(26, 1, 'FARMACIA VIRGEN DE LORETO', 'FARMACIA VIRGEN DE LORETO', '', '1', NULL, 'VILLA', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(27, 1, 'FARMACIA YESSICA', 'FARMACIA YESSICA', '', '1', NULL, '4 DE NOVIEMBRE', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:22'),
(28, 1, 'FARMACIA ZEBALLOS', 'FARMACIA ZEBALLOS', '', '1', NULL, '3 ANILLO ZOO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:29:34', '2021-11-25 18:17:47'),
(29, 1, 'FARMACIA NINO JESUS WARNES', 'FARMACIA NINO JESUS WARNES', '', '', NULL, 'WARNES', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:55', '2021-11-25 18:17:47'),
(30, 1, 'FARMACIA CRISTO REY WARNES', 'FARMACIA CRISTO REY WARNES', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:55', '2021-11-25 18:17:47'),
(31, 1, 'FARMACIA NIRVANA WARNES', 'FARMACIA NIRVANA WARNES', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:55', '2021-08-16 08:05:31'),
(32, 1, 'FARMACIA SENOR DE MAYO WARNES', 'FARMACIA SENOR DE MAYO WARNES', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:55', '2021-08-16 08:05:46'),
(33, 1, 'FARMACIA VIRGEN DEL ROSARIO WARNES', 'FARMACIA VIRGEN DEL ROSARIO WARNES', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:55', '2021-11-25 18:17:47'),
(34, 1, 'FARMACIA SANTA CRUZ MONTERO', 'FARMACIA SANTA CRUZ MONTERO', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:55', '2021-11-25 18:17:47'),
(35, 1, 'FARMACIA SAN JORGE MONTERO', 'FARMACIA SAN JORGE MONTERO', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:55', '2021-11-25 18:17:47'),
(36, 1, 'FARMACIA VICTORIA MONTERO', 'FARMACIA VICTORIA MONTERO', '', '9221093', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:55', '2021-11-25 18:17:47'),
(37, 1, 'FARMACIA UNIMAX MONTERO', 'FARMACIA UNIMAX MONTERO', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:56', '2021-11-25 18:17:47'),
(38, 1, 'FARMACIA SANTA TERESITA MONTERO', 'FARMACIA SANTA TERESITA MONTERO', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:56', '2021-11-25 18:17:47'),
(39, 1, 'FARMACIA YULIANA MONTERO', 'FARMACIA YULIANA MONTERO', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:37:56', '2021-11-25 18:17:47'),
(40, 1, 'FARMACIA MAGE', 'FARMACIA MAGE', '', '1', NULL, 'AV EL PALMAR PARADA 14', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:01', '2021-11-25 18:17:47'),
(41, 1, 'FARMACIA GARCIA', 'FARMACIA GARCIA', '', '1', NULL, 'LOS LOTES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:01', '2021-11-25 18:17:47'),
(42, 1, 'FARMACIA H2O VIDA', 'FARMACIA H2O VIDA', '', '71071026', NULL, 'a', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:01', '2021-11-25 18:17:48'),
(43, 1, 'FARMACIA PENIEL', 'FARMACIA PENIEL', '', '1', NULL, 'a', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:34', '2021-11-25 18:17:48'),
(44, 1, 'FARMACIA MEDICAL', 'FARMACIA MEDICAL', '', '1', NULL, 'a', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:34', '2021-11-25 18:17:48'),
(45, 1, 'FARMACIA FARMA- MED', 'FARMACIA FARMA- MED', '', '1', NULL, 'a', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:34', '2021-11-25 18:17:48'),
(46, 1, 'FARMACIA JOSE MARIA', 'FARMACIA JOSE MARIA', '', '1', NULL, 'RAMADA', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:34', '2021-11-25 18:17:48'),
(47, 1, 'FARMACIA AGUA VIVA AV CENTENARIO 4 ANILLO', 'FARMACIA AGUA VIVA AV CENTENARIO 4 ANILLO', '', '1', NULL, 'ABASTO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:34', '2021-11-25 18:17:48'),
(48, 1, 'FARMACIA MARINA', 'FARMACIA MARINA', '', '1', NULL, 'CALLE 6 DE AGOSTO 645', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:34', '2021-11-25 18:17:48'),
(49, 1, 'FARMACIA AG', 'FARMACIA AG', '', '1', NULL, 'CALLE BARRON NO 500', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:35', '2021-11-25 18:17:48'),
(50, 1, 'FARMACIA LORENA', 'FARMACIA LORENA', '', '1', NULL, 'HOSPITAL JAPONES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:35', '2021-11-25 18:17:48'),
(51, 1, 'FARMACIA SAN PEDRO', 'FARMACIA SAN PEDRO', '', '1', NULL, 'AL FRENTE DEL HOSPITAL JAPONES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:39:35', '2021-11-25 18:17:48'),
(52, 1, 'FARMACIA AGUA VIVA ', 'FARMACIA AGUA VIVA', '', '1', NULL, 'ABASTO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:51:54', '2021-11-25 18:17:48'),
(53, 1, 'FARMACIA FARMA-MED', 'FARMACIA FARMA-MED', '', '1', NULL, '1', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:52:38', '2021-11-25 18:17:48'),
(54, 1, 'FARMACIA 4 DE MARZO', 'FARMACIA 4 DE MARZO', '', '1', NULL, 'CHACARILLA', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:52:38', '2021-11-25 18:17:48'),
(55, 1, 'DOCTORA SELINA', 'DOCTORA SELINA', '', '1', NULL, '3 ANILLLO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:56:17', '2021-11-25 18:17:48'),
(56, 1, 'FARMACIA VIRGEN MARIA', 'FARMACIA VIRGEN MARIA', '', '1', NULL, '1', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:56:17', '2021-11-25 18:17:48'),
(57, 1, 'FARMACIA JESSY', 'FARMACIA JESSY', '', '1', NULL, '1', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(58, 1, 'FARMACIA TATA MALTA', 'FARMACIA TATA MALTA', '', '1', NULL, 'AV. 2 DE AGOSTO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(59, 1, 'FARMACIA ABRIL', 'FARMACIA ABRIL', '', '1', NULL, 'AV. MUTUALISTA', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(60, 1, 'FARMACIA D.H.I', 'FARMACIA D.H.I', '', '1', NULL, 'LOS POZOS', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(61, 1, 'FARMACIA VIRGEN MARIA WARNES', 'FARMACIA VIRGEN MARIA WARNES', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(62, 1, 'CENTRO MEDICO MENDEZ', 'CENTRO MEDICO MENDEZ', '', '1', NULL, '1', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(63, 1, 'CENTRO MEDICO EL QUIOR', 'CENTRO MEDICO EL QUIOR', '', '1', NULL, 'EL QUIOR', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(64, 1, 'FARMACIA NUEVO MUNDO', 'FARMACIA NUEVO MUNDO', '', '1', NULL, 'EL QUIOR', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(65, 1, 'FARMACIA PRIMICIA', 'FARMACIA PRIMICIA', '', '1', NULL, 'EL QUIOR', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:48'),
(66, 1, 'FARMACIA PAURITO', 'FARMACIA PAURITO', '', '1', NULL, '1', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:49'),
(67, 1, 'FARMACIA CIEL', 'FARMACIA CIEL', '', '1', NULL, '1', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 07:57:17', '2021-11-25 18:17:49'),
(68, 1, 'FARMACIA NINO JESUS (WARNES)', 'FARMACIA NINO JESUS (WARNES)', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:28', '2021-11-25 18:17:49'),
(69, 1, 'FARMACIA CRISTO REY (WARNES)', 'FARMACIA CRISTO REY (WARNES)', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-11-25 18:17:49'),
(70, 1, 'FARMACIA NIRVANA (WARNES)', 'FARMACIA NIRVANA (WARNES)', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-11-25 18:17:49'),
(71, 1, 'FARMACIA SENOR DE MAYO (WARNES)', 'FARMACIA SENOR DE MAYO (WARNES)', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-11-25 18:17:49'),
(72, 1, 'FARMACIA VIRGEN DEL ROSARIO (WARNES)', 'FARMACIA VIRGEN DEL ROSARIO (WARNES)', '', '1', NULL, 'WARNES', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-08-16 08:06:06'),
(73, 1, 'FARMACIA SANTA CRUZ (MONTERO)', 'FARMACIA SANTA CRUZ (MONTERO)', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-11-25 18:17:49'),
(74, 1, 'FARMACIA SAN JORGE (MONTERO)', 'FARMACIA SAN JORGE (MONTERO)', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-11-25 18:17:49'),
(75, 1, 'FARMACIA VICTORIA (MONTERO)', 'FARMACIA VICTORIA (MONTERO)', '', '9221093', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-11-25 18:17:49'),
(76, 1, 'FARMACIA UNIMAX (MONTERO)', 'FARMACIA UNIMAX (MONTERO)', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-11-25 18:17:49'),
(77, 1, 'FARMACIA SANTA TERESITA (MONTERO)', 'FARMACIA SANTA TERESITA (MONTERO)', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-08-16 08:08:50'),
(78, 1, 'FARMACIA YULIANA (MONTERO)', 'FARMACIA YULIANA (MONTERO)', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:00:59', '2021-11-25 18:17:49'),
(79, 1, 'FARMACIA H2O - VIDA', 'FARMACIA H2O - VIDA', '', '71071026', NULL, 'a', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:03:07', '2021-11-25 18:17:49'),
(80, 1, 'FARMACIA ERNESTO', 'FARMACIA ERNESTO', '', '1', NULL, 'PLAM 3000', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:07:03', '2021-11-25 18:17:49'),
(81, 1, 'FARMACIA BAUTISTA', 'FARMACIA BAUTISTA', '', '1', NULL, 'AVALEMANA 4TO ANILLO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:07:03', '2021-11-25 18:17:49'),
(82, 1, 'FARMACIA SHEKINAH', 'FARMACIA SHEKINAH', '', '1', NULL, 'AV SAN SILVESTRE', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:07:04', '2021-11-25 18:17:49'),
(83, 1, 'FARMACIA MABEL', 'FARMACIA MABEL', '', '1', NULL, 'ZONA JUAN PABLO 2 6TO ANILLO', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:07:04', '2021-11-25 18:17:49'),
(84, 1, 'FARMACIA FARMA-STORE', 'FARMACIA FARMA-STORE', '', '1', NULL, 'RADIAL 26', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:07:04', '2021-11-25 18:17:49'),
(85, 1, 'FARMACIA VIRGEN MORENA MONTERO', 'FARMACIA VIRGEN MORENA MONTERO', '', '1', NULL, 'MONTERO', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:49'),
(86, 1, 'FARMACIA R Y I', 'FARMACIA R Y I', '', '1', NULL, 'VIRGEN DE LUJAN', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:49'),
(87, 1, 'FARMACIA LLENE', 'FARMACIA LLENE', '', '1', NULL, '1', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:49'),
(88, 1, 'FARMACIA FARMASIL', 'FARMACIA FARMASIL', '', '1', NULL, 'ABASTO', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:49'),
(89, 1, 'FARAMCIA 28 DE SEPTIEMBRE', 'FARAMCIA 28 DE SEPTIEMBRE', '', '1', NULL, '5TO ANILLO CAMBODROMO', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:49'),
(90, 1, 'FARMACIA SAN MARCOS', 'FARMACIA SAN MARCOS', '', '1', NULL, 'VILLA', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:49'),
(91, 1, 'FARAMCIA ROSSY', 'FARAMCIA ROSSY', '', '1', NULL, 'KM 6', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:49'),
(92, 1, 'FARMACIA IVERAZ CAMIRI', 'FARMACIA IVERAZ CAMIRI', '', '1', NULL, 'CAMIRI', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:49'),
(93, 1, 'FARMACIA LISBETH YOBANA CAMIRI', 'FARMACIA LISBETH YOBANA CAMIRI', '', '1', NULL, 'CAMIRI', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:50'),
(94, 1, 'SHIRLEY', 'SHIRLEY', '', '1', NULL, '1', 'SANTA CRUZ', 'BO', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:50'),
(95, 1, 'FARMACIA VICTORIA', 'FARMACIA VICTORIA', '', '1', NULL, 'BARRIO COLORADA 5 ANILLO', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:50'),
(96, 1, 'FARMACIA SA CAYETANO', 'FARMACIA SA CAYETANO', '', '1', NULL, 'YACUIBA', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:50'),
(97, 1, 'FARMACIA FARMA SUR 2 YACUIBA', 'FARMACIA FARMA SUR 2 YACUIBA', '', '1', NULL, 'YACUIBA', 'SANTA CRUZ', '', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:09:22', '2021-11-25 18:17:50'),
(98, 1, 'FARMACIA RENASOL', 'FARMACIA RENASOL', '', '1', NULL, 'LOS CHACOS CALLE 11', 'SANTA CRUZ', '1', '1', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:07', '2021-11-25 18:17:50'),
(99, 1, 'FARMACIA MARIA DEL CARMEN WARNES', 'FARMACIA MARIA DEL CARMEN WARNES', '', '1', NULL, 'WARNES', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:07', '2021-11-25 18:17:50'),
(100, 1, 'FARMACIA SANTA RITA YACUIBA', 'FARMACIA SANTA RITA YACUIBA', '', '76806589', NULL, 'YACUIBA', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:07', '2021-11-25 18:17:50'),
(101, 1, 'FARMACIA SAN ANDRES YACUIBA', 'FARMACIA SAN ANDRES YACUIBA', '', '76821133', NULL, 'YACUIBA', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:07', '2021-11-25 18:17:50'),
(102, 1, 'FARMACIA PADRE CELESTIAL YACUIBA', 'FARMACIA PADRE CELESTIAL YACUIBA', '', '67112790', NULL, 'YACUIBA', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:07', '2021-11-25 18:17:50'),
(103, 1, 'FARMACIA LA AUXILIADORA YACUIBA', 'FARMACIA LA AUXILIADORA YACUIBA', '', '1', NULL, 'YACUIBA', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:08', '2021-11-25 18:17:50'),
(104, 1, 'FARMACIA DIVINO NINO POCITO', 'FARMACIA DIVINO NINO POCITO', '', '70875265', NULL, 'POCITO', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:08', '2021-11-25 18:17:50'),
(105, 1, 'FARMACIA KARINA POCITO', 'FARMACIA KARINA POCITO', '', '1', NULL, 'POCITO', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:08', '2021-11-25 18:17:50'),
(106, 1, 'FARMACIA GUADALUPE', 'FARMACIA GUADALUPE', '', '72941112', NULL, 'ALTO SAN PEDRO 3 ANILLO EXTERNO', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:08', '2021-11-25 18:17:50'),
(107, 1, 'FARMACIA MARIA DEL PILAR YACUIBA', 'FARMACIA MARIA DEL PILAR YACUIBA', '', '72909300', NULL, 'YACUIBA', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:08', '2021-11-25 18:17:50'),
(108, 1, 'FARMACIA CUELLAR', 'FARMACIA CUELLAR', '', '77810801', NULL, 'EL PARAISO PASANDO EL SEGUNDO ANILLO', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:08', '2021-11-25 18:17:50'),
(109, 1, 'FARMACIA SAN JORGE CAMIRI', 'FARMACIA SAN JORGE CAMIRI', '', '1', NULL, 'CAMIRI', 'SANTA CRUZ', '1', '', 'bd', NULL, NULL, 0, 0, 0, 0, '2021-08-16 08:13:08', '2021-11-25 18:17:50'),
(110, 1, 'CENTRO MEDICO BARTIMEO', 'CENTRO MEDICO BARTIMEO', NULL, '222222', NULL, 'KM/9 LA GUARDIA', 'SC', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-17 00:20:59', '2021-11-25 18:17:50'),
(111, 1, 'test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-17 15:51:14', '2021-11-25 18:17:50'),
(112, 1, 'prueba 2', NULL, NULL, '3433333', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-23 23:47:31', '2021-11-25 18:17:50'),
(113, 1, 'test1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-23 23:56:13', '2021-11-25 18:17:50'),
(114, 1, 'test3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-23 23:58:26', '2021-11-25 18:17:50'),
(115, 1, 'FARMACIA LLANTEN', NULL, NULL, NULL, NULL, 'AV/ 2 DE AGOSTO 6TO ANILLO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:01:12', '2021-11-25 18:17:51'),
(116, 1, 'FARMACIA BAUTISTA', NULL, NULL, NULL, NULL, 'AV/ ALEMANA 4TO ANILLO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:05:45', '2021-11-25 18:17:51'),
(117, 1, 'FARMACIA NATURA', NULL, NULL, NULL, NULL, 'AV/ BENI 4TO ANILLO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:12:26', '2021-11-25 18:17:51'),
(118, 1, 'FARMACIA TERRAZA', NULL, NULL, NULL, NULL, 'AV/ SAN SIVETRE 6TO ANILLO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:15:57', '2021-11-25 18:17:51'),
(119, 1, 'FARMACIA 21 SEPTIEMBRE', NULL, NULL, NULL, NULL, 'AV/ CUMABI 8 ANILLO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:20:33', '2021-11-25 18:17:51'),
(120, 1, 'FARMACIA GLORIA QUIROGA', NULL, NULL, NULL, NULL, 'HOSPITAL PALO VERDE', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:28:31', '2021-11-25 18:17:51'),
(121, 1, 'FARMACIA BIO CENTER', NULL, NULL, NULL, NULL, 'CAMBODROMO 6TO ANILLO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:31:20', '2021-11-25 18:17:51'),
(122, 1, 'FARMACIA ZECIA C.S.', NULL, NULL, NULL, NULL, 'MERCADO NUEVO LOS POSO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:34:48', '2021-11-25 18:17:51'),
(123, 1, 'FARMACIA PHARMACY', NULL, NULL, NULL, NULL, 'AV/ LUJAN 6TO ANILLO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:38:10', '2021-11-25 18:17:51'),
(124, 1, 'FARMACIA NACIONAL', NULL, NULL, NULL, NULL, 'MERCADO LOS POSO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:41:57', '2021-11-25 18:17:51'),
(125, 1, 'FARMACIA PIRAI', NULL, NULL, NULL, NULL, 'MERCADO LOS POSO', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:44:21', '2021-11-25 18:17:51'),
(126, 1, 'FARMACIA BUENA VISTA', NULL, NULL, NULL, NULL, 'PLAN 3MIL', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:47:06', '2021-11-25 18:17:51'),
(127, 1, 'FARMACIA 24 SEPTIEMBRE', NULL, NULL, NULL, NULL, 'AV/ SAN SILVESTRE', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:51:03', '2021-11-25 18:17:51'),
(128, 1, 'FARMACIA MINIONS FARMA', NULL, NULL, NULL, NULL, 'KM 9 DOBLE VIA ALA GUARDIA', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:54:52', '2021-11-25 18:17:51'),
(129, 1, 'FARMACIA SANTIAGO APOSTOL', NULL, NULL, NULL, NULL, 'KM 9 DOBLE VIA ALA GUARDIA', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-08-24 01:57:12', '2021-11-25 18:17:59'),
(130, 1, 'FARMACIA MISTER AHORRO', NULL, NULL, NULL, NULL, 'MERCADO LA CAMPANA', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, '2021-09-02 00:38:39', '2021-11-25 18:18:00'),
(131, 1, 'La Creme', NULL, NULL, '54848484848', NULL, 'SCZ', 'santa cruz', NULL, NULL, NULL, NULL, NULL, 0, 0, 2, 1, '2021-12-13 23:54:49', '2022-03-15 20:05:24'),
(132, 1, 'test cliente', 'SESS', 'calatayudjuan17@gmail.com', '63969924', NULL, 'S/N', 'Cbba', 'BO', NULL, 'Bolivia', NULL, NULL, 500, 1, 2, 1, '2022-03-15 20:02:28', '2022-03-28 03:45:46'),
(133, 1, 'Pedidos Ya', 'Pedidos Ya', 'general@gmail.com', '5484848484', NULL, 'SCZ', 'santa cruz', NULL, NULL, 'Bolivia', NULL, NULL, 500, 1, 0, 1, '2022-03-15 20:06:28', '2022-03-25 22:04:38'),
(134, 1, 'Yaigo Delivery', 'test', NULL, '3454545', NULL, 'S/N', 'SCZ', NULL, NULL, 'Bolivia', NULL, NULL, 100, 0, 0, 1, '2022-03-25 21:48:31', '2022-03-25 22:01:20'),
(135, 1, 'Casera Maria', 'Maria', NULL, '65551515', NULL, 'S/N', 'santa cruz', NULL, NULL, 'Bolivia', NULL, NULL, 200, 1, 0, 1, '2022-03-25 22:06:17', '2022-03-25 22:06:17');

-- --------------------------------------------------------

--
-- Table structure for table `customer_groups`
--

CREATE TABLE `customer_groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_groups`
--

INSERT INTO `customer_groups` (`id`, `name`, `percentage`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'general', '0', 1, '2018-05-12 08:09:36', '2019-03-02 06:01:35');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

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

CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ventas', 1, '2018-12-27 05:16:47', '2018-12-27 10:40:23'),
(2, 'Servicio', 1, '2021-11-18 05:13:22', '2021-12-01 16:35:15');

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` int(10) UNSIGNED NOT NULL,
  `amount` double NOT NULL,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

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
  `percentage` double NOT NULL DEFAULT '0',
  `pay_commission` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FALSE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `phone_number`, `department_id`, `user_id`, `image`, `address`, `city`, `country`, `is_active`, `contract_type`, `percentage`, `pay_commission`, `created_at`, `updated_at`) VALUES
(1, 'Jorge Ramos', 'jhoelpicks.soccer@gmail.com', '23232', 1, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 'FALSE', '2021-11-10 21:00:14', '2021-12-01 16:35:54'),
(2, 'Rebeca', 'rebeca_m.sh@hotmail.com', '75637869', 1, 15, NULL, NULL, NULL, NULL, 1, NULL, 0, 'FALSE', '2021-12-01 16:38:02', '2021-12-01 16:38:02'),
(3, 'Hector', 'elchamobarber@gmail.com', '62010824', 2, 16, NULL, NULL, NULL, NULL, 1, 'COMISION_UNICA', 60, 'TRUE', '2021-12-01 16:50:43', '2022-01-05 15:02:58'),
(4, 'David Augusto', 'davidaugustolopez94@gmail.com', '76848229', 2, 17, NULL, NULL, NULL, NULL, 1, 'COMISION_UNICA', 0, 'TRUE', '2021-12-06 18:25:42', '2022-01-12 05:40:52'),
(5, 'Jesus Rangel', 'lacremebarberclub@gmail.com', '62010825', 2, 18, NULL, NULL, NULL, NULL, 1, NULL, 0, 'FALSE', '2021-12-06 18:43:15', '2021-12-06 18:43:15'),
(6, 'Rafael', '19rafa.rasta@gmail.com', '62010822', 2, NULL, NULL, NULL, NULL, NULL, 1, NULL, 0, 'FALSE', '2021-12-13 16:21:15', '2021-12-13 16:21:15');

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_category_id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `reference_no`, `expense_category_id`, `warehouse_id`, `account_id`, `user_id`, `amount`, `note`, `created_at`, `updated_at`) VALUES
(1, 'er-20220131-041252', 1, 1, 5, 1, 100, NULL, '2022-01-31 20:12:52', '2022-01-31 20:12:52'),
(2, 'er-20220131-041311', 2, 1, 5, 1, 250, 'Tigo', '2022-01-31 20:13:11', '2022-01-31 20:13:11'),
(3, 'er-20220131-041333', 1, 1, 5, 1, 150, 'Agua', '2022-01-31 20:13:33', '2022-01-31 20:13:33'),
(4, 'er-20220330-043528', 2, 1, 1, 1, 143, 'Internet Entel Mes Marzo', '2022-03-30 20:35:28', '2022-03-30 20:35:28'),
(5, 'er-20220519-034606', 1, 1, 1, 1, 100, 'test', '2022-05-19 19:46:06', '2022-05-19 19:46:06'),
(6, 'er-20220519-034735', 1, 1, 1, 1, 50, NULL, '2022-05-18 04:00:00', '2022-05-19 19:47:35'),
(8, 'er-20220519-035217', 2, 1, 1, 1, 20, 'test update', '2022-05-19 07:52:26', '2022-05-20 19:03:03'),
(9, 'er-20220519-035338', 2, 1, 1, 1, 25, 'test old date', '2022-05-16 07:04:00', '2022-05-20 19:04:07');

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `code`, `name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '17301979', 'Servicios Basicos', 1, '2021-11-18 17:33:50', '2021-11-18 17:33:50'),
(2, '22719709', 'Internet', 1, '2022-01-31 20:12:30', '2022-01-31 20:12:30');

-- --------------------------------------------------------

--
-- Table structure for table `garantes`
--

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

CREATE TABLE `general_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `site_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_logo` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staff_access` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_format` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alert_expiration` int(10) NOT NULL DEFAULT '30',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `currency_position` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `general_settings`
--

INSERT INTO `general_settings` (`id`, `site_title`, `site_logo`, `currency`, `staff_access`, `date_format`, `theme`, `alert_expiration`, `created_at`, `updated_at`, `currency_position`) VALUES
(1, 'Test Dev', '240674860_214978100553527_7001149762023573475_n.png', 'BOB', 'own', 'd/m/Y', 'default.css', 30, '2018-07-06 06:13:11', '2022-03-24 20:45:42', 'suffix');

-- --------------------------------------------------------

--
-- Table structure for table `gift_cards`
--

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

--
-- Dumping data for table `gift_cards`
--

INSERT INTO `gift_cards` (`id`, `card_no`, `amount`, `expense`, `customer_id`, `user_id`, `expired_date`, `created_by`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '4330187632810922', 1500, 0, 18, NULL, '2021-12-11', 1, 1, '2021-12-11 20:28:58', '2021-12-11 20:29:36');

-- --------------------------------------------------------

--
-- Table structure for table `gift_card_recharges`
--

CREATE TABLE `gift_card_recharges` (
  `id` int(10) UNSIGNED NOT NULL,
  `gift_card_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gift_card_recharges`
--

INSERT INTO `gift_card_recharges` (`id`, `gift_card_id`, `amount`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 500, 1, '2021-12-11 20:29:36', '2021-12-11 20:29:36');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hrm_settings`
--

CREATE TABLE `hrm_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `checkin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkout` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hrm_settings`
--

INSERT INTO `hrm_settings` (`id`, `checkin`, `checkout`, `created_at`, `updated_at`) VALUES
(1, '9:00am', '7:00pm', '2021-12-01 17:00:30', '2021-12-06 18:15:37');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `code`, `created_at`, `updated_at`) VALUES
(1, 'es', '2018-07-07 22:59:17', '2019-12-24 17:34:20');

-- --------------------------------------------------------

--
-- Table structure for table `lote_sales`
--

CREATE TABLE `lote_sales` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `qty` double NOT NULL,
  `data` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `lote_sales`
--

INSERT INTO `lote_sales` (`id`, `sale_id`, `lote_id`, `qty`, `data`, `created_at`, `updated_at`) VALUES
(2, 94, 15, 3, NULL, '2022-02-15 00:34:49', '2022-02-15 04:34:49'),
(3, 94, 16, 3, NULL, '2022-02-15 00:38:25', '2022-02-15 04:38:25'),
(4, 95, 11, 2, NULL, '2022-02-15 00:43:14', '2022-02-15 04:43:14'),
(5, 95, 11, 2, NULL, '2022-02-15 00:43:14', '2022-02-15 04:43:14'),
(8, 101, 17, 5, NULL, '2022-02-15 01:00:33', '2022-02-15 05:00:33'),
(9, 101, 18, 2, NULL, '2022-02-15 01:00:40', '2022-02-15 05:00:40'),
(12, 169, 13, 1, NULL, '2022-03-16 23:50:50', '2022-03-17 03:50:50'),
(13, 174, 22, 2, NULL, '2022-03-24 01:31:05', '2022-03-24 05:31:05'),
(14, 175, 27, 1, NULL, '2022-03-28 00:19:22', '2022-03-28 04:19:22'),
(15, 175, 26, 1, NULL, '2022-03-28 00:19:22', '2022-03-28 04:19:22'),
(16, 176, 27, 1, NULL, '2022-03-28 00:42:00', '2022-03-28 04:42:00'),
(17, 176, 26, 1, NULL, '2022-03-28 00:42:01', '2022-03-28 04:42:01'),
(18, 177, 27, 1, NULL, '2022-03-28 14:39:15', '2022-03-28 18:39:15'),
(19, 177, 26, 1, NULL, '2022-03-28 14:39:15', '2022-03-28 18:39:15'),
(20, 178, 27, 1, NULL, '2022-03-28 14:40:55', '2022-03-28 18:40:55'),
(21, 178, 26, 2, NULL, '2022-03-28 14:40:56', '2022-03-28 18:40:56'),
(22, 179, 27, 1, NULL, '2022-03-28 14:47:35', '2022-03-28 18:47:35'),
(23, 179, 26, 1, NULL, '2022-03-28 14:47:35', '2022-03-28 18:47:35'),
(24, 183, 27, 1, NULL, '2022-03-28 16:29:56', '2022-03-28 20:29:56'),
(25, 183, 26, 1, NULL, '2022-03-28 16:29:57', '2022-03-28 20:29:57'),
(26, 185, 27, 2, NULL, '2022-03-29 17:33:36', '2022-03-29 21:33:36'),
(27, 185, 26, 2, NULL, '2022-03-29 17:33:37', '2022-03-29 21:33:37'),
(28, 185, 27, 2, NULL, '2022-03-29 17:33:37', '2022-03-29 21:33:37'),
(29, 185, 26, 4, NULL, '2022-03-29 17:33:38', '2022-03-29 21:33:38'),
(30, 186, 27, 1, NULL, '2022-03-29 17:37:28', '2022-03-29 21:37:28'),
(31, 186, 26, 1, NULL, '2022-03-29 17:37:29', '2022-03-29 21:37:29');

-- --------------------------------------------------------

--
-- Table structure for table `method_payments`
--

CREATE TABLE `method_payments` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `apply` tinyint(1) NOT NULL DEFAULT '0',
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `cbx` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `method_payments`
--

INSERT INTO `method_payments` (`id`, `name`, `description`, `apply`, `used`, `cbx`, `created_at`, `updated_at`) VALUES
(1, 'Efectivo', 'Efectivo', 0, 0, 1, '2021-12-21 00:26:35', '2021-12-21 00:32:37'),
(2, 'Guardar Mas Tarde', 'Guardar Mas Tarde', 0, 0, 0, '2021-12-21 00:26:35', '2021-12-21 00:32:37'),
(3, 'Tarjeta Regalo', 'Tarjeta Regalo', 1, 0, 1, '2021-12-21 00:33:48', '2021-12-21 00:36:22'),
(4, 'Tarjeta Credito/Debito', 'Tarjeta Credito Debito', 1, 1, 1, '2021-12-21 00:33:48', '2021-12-22 16:20:11'),
(5, 'Cheque', 'Cheque', 1, 1, 1, '2021-12-21 00:38:42', '2021-12-21 00:37:32'),
(6, 'Qr Simple', 'Qr Simple', 1, 1, 1, '2021-12-21 00:36:44', '2021-12-22 16:20:11'),
(7, 'Deposito', 'Deposito', 1, 1, 1, '2021-12-21 00:37:40', '2021-12-22 16:29:19'),
(8, 'Paypal', 'Paypal', 1, 0, 0, '2021-12-21 00:37:40', '2021-12-22 20:35:27'),
(9, 'Por Cobrar', 'Por Cobrar', 1, 1, 0, '2022-02-24 04:07:56', '2022-02-24 04:08:25');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2018_02_17_060412_create_categories_table', 1),
(4, '2018_02_20_035727_create_brands_table', 1),
(5, '2018_02_25_100635_create_suppliers_table', 1),
(6, '2018_02_27_101619_create_warehouse_table', 1),
(7, '2018_03_03_040448_create_units_table', 1),
(8, '2018_03_04_041317_create_taxes_table', 1),
(9, '2018_03_10_061915_create_customer_groups_table', 1),
(10, '2018_03_10_090534_create_customers_table', 1),
(11, '2018_03_11_095547_create_billers_table', 1),
(12, '2018_04_05_054401_create_products_table', 1),
(13, '2018_04_06_133606_create_purchases_table', 1),
(14, '2018_04_06_154600_create_product_purchases_table', 1),
(15, '2018_04_06_154915_create_product_warhouse_table', 1),
(16, '2018_04_10_085927_create_sales_table', 1),
(17, '2018_04_10_090133_create_product_sales_table', 1),
(18, '2018_04_10_090254_create_payments_table', 1),
(19, '2018_04_10_090341_create_payment_with_cheque_table', 1),
(20, '2018_04_10_090509_create_payment_with_credit_card_table', 1),
(21, '2018_04_13_121436_create_quotation_table', 1),
(22, '2018_04_13_122324_create_product_quotation_table', 1),
(23, '2018_04_14_121802_create_transfers_table', 1),
(24, '2018_04_14_121913_create_product_transfer_table', 1),
(25, '2018_05_13_082847_add_payment_id_and_change_sale_id_to_payments_table', 2),
(26, '2018_05_13_090906_change_customer_id_to_payment_with_credit_card_table', 3),
(27, '2018_05_20_054532_create_adjustments_table', 4),
(28, '2018_05_20_054859_create_product_adjustments_table', 4),
(29, '2018_05_21_163419_create_returns_table', 5),
(30, '2018_05_21_163443_create_product_returns_table', 5),
(32, '2018_06_02_073430_add_columns_to_users_table', 7),
(36, '2018_06_21_063736_create_pos_setting_table', 9),
(37, '2018_06_21_094155_add_user_id_to_sales_table', 10),
(38, '2018_06_21_101529_add_user_id_to_purchases_table', 11),
(39, '2018_06_21_103512_add_user_id_to_transfers_table', 12),
(40, '2018_06_23_061058_add_user_id_to_quotations_table', 13),
(41, '2018_06_23_082427_add_is_deleted_to_users_table', 14),
(42, '2018_06_25_043308_change_email_to_users_table', 15),
(43, '2018_07_06_115449_create_general_settings_table', 16),
(44, '2018_07_08_043944_create_languages_table', 17),
(45, '2018_07_11_102144_add_user_id_to_returns_table', 18),
(46, '2018_07_11_102334_add_user_id_to_payments_table', 18),
(47, '2018_07_22_130541_add_digital_to_products_table', 19),
(49, '2018_07_24_154250_create_deliveries_table', 20),
(50, '2018_08_16_053336_create_expense_categories_table', 21),
(51, '2018_08_17_115415_create_expenses_table', 22),
(55, '2018_08_18_050418_create_gift_cards_table', 23),
(56, '2018_08_19_063119_create_payment_with_gift_card_table', 24),
(57, '2018_08_25_042333_create_gift_card_recharges_table', 25),
(58, '2018_08_25_101354_add_deposit_expense_to_customers_table', 26),
(59, '2018_08_26_043801_create_deposits_table', 27),
(60, '2018_09_02_044042_add_keybord_active_to_pos_setting_table', 28),
(61, '2018_09_09_092713_create_payment_with_paypal_table', 29),
(62, '2018_09_10_051254_add_currency_to_general_settings_table', 30),
(63, '2018_10_22_084118_add_biller_and_store_id_to_users_table', 31),
(65, '2018_10_26_034927_create_coupons_table', 32),
(66, '2018_10_27_090857_add_coupon_to_sales_table', 33),
(67, '2018_11_07_070155_add_currency_position_to_general_settings_table', 34),
(68, '2018_11_19_094650_add_combo_to_products_table', 35),
(69, '2018_12_09_043712_create_accounts_table', 36),
(70, '2018_12_17_112253_add_is_default_to_accounts_table', 37),
(71, '2018_12_19_103941_add_account_id_to_payments_table', 38),
(72, '2018_12_20_065900_add_account_id_to_expenses_table', 39),
(73, '2018_12_20_082753_add_account_id_to_returns_table', 40),
(74, '2018_12_26_064330_create_return_purchases_table', 41),
(75, '2018_12_26_144210_create_purchase_product_return_table', 42),
(76, '2018_12_26_144708_create_purchase_product_return_table', 43),
(77, '2018_12_27_110018_create_departments_table', 44),
(78, '2018_12_30_054844_create_employees_table', 45),
(79, '2018_12_31_125210_create_payrolls_table', 46),
(80, '2018_12_31_150446_add_department_id_to_employees_table', 47),
(81, '2019_01_01_062708_add_user_id_to_expenses_table', 48),
(82, '2019_01_02_075644_create_hrm_settings_table', 49),
(83, '2019_01_02_090334_create_attendances_table', 50),
(84, '2019_01_27_160956_add_three_columns_to_general_settings_table', 51),
(85, '2019_02_15_183303_create_stock_counts_table', 52),
(86, '2019_02_17_101604_add_is_adjusted_to_stock_counts_table', 53),
(87, '2019_04_13_101707_add_tax_no_to_customers_table', 54),
(89, '2019_10_14_111455_create_holidays_table', 55),
(90, '2019_11_13_145619_add_is_variant_to_products_table', 56),
(91, '2019_11_13_150206_create_product_variants_table', 57),
(92, '2019_11_13_153828_create_variants_table', 57),
(93, '2019_11_25_134041_add_qty_to_product_variants_table', 58),
(94, '2019_11_25_134922_add_variant_id_to_product_purchases_table', 58),
(95, '2019_11_25_145341_add_variant_id_to_product_warehouse_table', 58),
(96, '2019_11_29_182201_add_variant_id_to_product_sales_table', 59),
(97, '2019_12_04_121311_add_variant_id_to_product_quotation_table', 60),
(98, '2019_12_05_123802_add_variant_id_to_product_transfer_table', 61),
(100, '2019_12_08_114954_add_variant_id_to_product_returns_table', 62),
(101, '2019_12_08_203146_add_variant_id_to_purchase_product_return_table', 63),
(102, '2020_02_28_103340_create_money_transfers_table', 64),
(103, '2020_07_01_193151_add_image_to_categories_table', 65);

-- --------------------------------------------------------

--
-- Table structure for table `money_transfers`
--

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

--
-- Dumping data for table `money_transfers`
--

INSERT INTO `money_transfers` (`id`, `reference_no`, `from_account_id`, `to_account_id`, `amount`, `note`, `created_at`, `updated_at`) VALUES
(1, 'mtr-20211213-043026', 1, 5, 6433, NULL, '2021-12-13 22:30:26', '2021-12-13 22:30:26'),
(2, 'mtr-20220218-025625', 1, 12, 10, 'test', '2022-02-18 18:56:25', '2022-02-18 18:56:25');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

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
  `payment_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `payment_reference`, `user_id`, `purchase_id`, `sale_id`, `account_id`, `employee_id`, `amount`, `change`, `paying_method`, `payment_note`, `created_at`, `updated_at`) VALUES
(30, 'spr-20211213-103907', 15, NULL, 31, 1, NULL, 55, 0, 'Efectivo', NULL, '2021-12-13 16:39:07', '2021-12-13 16:39:07'),
(31, 'spr-20211213-104624', 15, NULL, 32, 1, NULL, 55, 45, 'Efectivo', NULL, '2021-12-13 16:46:24', '2021-12-13 16:46:24'),
(32, 'spr-20211213-114252', 15, NULL, 33, 1, NULL, 320, 0, 'Qr_simple', 'PQR', '2021-12-13 17:42:52', '2021-12-13 17:42:52'),
(33, 'spr-20211213-114348', 15, NULL, 34, 1, NULL, 55, 0, 'Efectivo', NULL, '2021-12-13 17:43:48', '2021-12-13 17:43:48'),
(34, 'spr-20211213-041043', 15, NULL, 35, 1, NULL, 120, 80, 'Efectivo', NULL, '2021-12-13 22:10:43', '2021-12-13 22:10:43'),
(35, 'spr-20211213-042006', 15, NULL, 36, 1, NULL, 120, 119.22, 'Efectivo', NULL, '2021-12-13 22:20:06', '2021-12-13 22:20:06'),
(36, 'spr-20211213-043603', 15, NULL, 37, 1, NULL, 170, 0, 'Qr_simple', 'PQR', '2021-12-13 22:36:03', '2021-12-13 22:36:03'),
(37, 'spr-20211213-044543', 15, NULL, 38, 1, NULL, 50, 150, 'Efectivo', NULL, '2021-12-13 22:45:43', '2021-12-13 22:45:43'),
(38, 'spr-20211213-053313', 15, NULL, 39, 1, NULL, 280, 0, 'Qr_simple', 'PQR', '2021-12-13 23:33:13', '2021-12-13 23:33:13'),
(39, 'spr-20211213-081159', 15, NULL, 40, 1, NULL, 55, 0, 'Efectivo', NULL, '2021-12-14 02:11:59', '2021-12-14 02:11:59'),
(40, 'spr-20211213-081232', 15, NULL, 41, 1, NULL, 55, 0, 'Efectivo', NULL, '2021-12-14 02:12:32', '2021-12-14 02:12:32'),
(41, 'spr-20211213-081300', 15, NULL, 42, 1, NULL, 30, 0, 'Efectivo', NULL, '2021-12-14 02:13:00', '2021-12-14 02:13:00'),
(42, 'spr-20211213-081342', 15, NULL, 43, 1, NULL, 55, 0, 'Qr_simple', 'PQR', '2021-12-14 02:13:42', '2021-12-14 02:13:42'),
(43, 'spr-20211213-081434', 15, NULL, 44, 1, NULL, 110, 0, 'Efectivo', NULL, '2021-12-14 02:14:34', '2021-12-14 02:14:34'),
(44, 'spr-20211213-081500', 15, NULL, 45, 1, NULL, 55, 50, 'Efectivo', NULL, '2021-12-14 02:15:00', '2021-12-14 02:15:00'),
(45, 'spr-20211213-081539', 15, NULL, 46, 1, NULL, 55, 45, 'Efectivo', NULL, '2021-12-14 02:15:39', '2021-12-14 02:15:39'),
(46, 'spr-20211213-081540', 15, NULL, 47, 1, NULL, 55, 45, 'Efectivo', NULL, '2021-12-14 02:15:40', '2021-12-14 02:15:40'),
(47, 'spr-20211213-081613', 15, NULL, 48, 1, NULL, 55, 15, 'Efectivo', NULL, '2021-12-14 02:16:13', '2021-12-14 02:16:13'),
(48, 'spr-20211221-104657', 1, NULL, 49, 1, NULL, 10, 0, 'Cheque', 'test', '2021-12-21 14:46:57', '2021-12-21 14:46:57'),
(49, 'spr-20211221-104749', 1, NULL, 50, 1, NULL, 75, 0, 'Efectivo', NULL, '2021-12-21 14:47:49', '2021-12-21 14:47:49'),
(50, 'spr-20211222-055907', 1, NULL, 52, 1, NULL, 15, 0, 'Efectivo', 'test medio pago 1', '2021-12-22 21:59:20', '2021-12-22 21:59:20'),
(51, 'spr-20211222-060101', 1, NULL, 53, 6, NULL, 90, 0, 'Cheque', 'test medio de pago 2', '2021-12-22 22:01:06', '2021-12-22 22:01:06'),
(52, 'spr-20211222-060456', 1, NULL, 54, 6, NULL, 40, 0, 'Deposito', 'test medio de pago 3', '2021-12-22 22:04:56', '2021-12-22 22:04:56'),
(53, 'spr-20220104-050909', 1, NULL, 55, 1, NULL, 120, 0, 'Efectivo', 'test con cortesia', '2022-01-04 21:09:09', '2022-01-04 21:09:09'),
(54, 'spr-20220105-104712', 1, NULL, 56, 1, NULL, 120, 0, 'Efectivo', NULL, '2022-01-05 14:47:12', '2022-01-05 14:47:12'),
(55, 'spr-20220105-100435', 18, NULL, 57, 12, NULL, 10, 0, 'Efectivo', NULL, '2022-01-06 02:04:35', '2022-01-06 02:04:35'),
(56, 'spr-20220105-101934', 1, NULL, 58, 1, NULL, 75, 0, 'Efectivo', NULL, '2022-01-06 02:19:34', '2022-01-06 02:19:34'),
(57, 'spr-20220107-042715', 1, NULL, 67, 1, NULL, 130, 0, 'Efectivo', 'test employee', '2022-01-07 08:27:15', '2022-01-07 08:27:15'),
(58, 'spr-20220107-042821', 1, NULL, 68, 1, NULL, 130, 0, 'Efectivo', NULL, '2022-01-07 08:28:21', '2022-01-07 08:28:21'),
(59, 'spr-20220107-042930', 1, NULL, 69, 1, NULL, 195, 0, 'Efectivo', NULL, '2022-01-07 08:29:30', '2022-01-07 08:29:30'),
(60, 'spr-20220111-122631', 1, NULL, 70, 5, NULL, 10, 0, 'Qr_simple', 'test', '2022-01-11 04:26:31', '2022-01-11 04:26:31'),
(61, 'spr-20220111-123128', 1, NULL, 72, 5, NULL, 15, 0, 'Qr_simple', NULL, '2022-01-11 04:31:28', '2022-01-11 04:31:28'),
(62, 'spr-20220111-124331', 1, NULL, 73, 1, NULL, 170, 0, 'Efectivo', NULL, '2022-01-11 04:43:31', '2022-01-11 04:43:31'),
(63, 'spr-20220111-124625', 1, NULL, 74, 5, NULL, 100, 0, 'Tarjeta_Credito_Debito', 'test card', '2022-01-11 04:46:25', '2022-01-11 04:46:25'),
(64, 'spr-20220112-014119', 1, NULL, 75, 1, NULL, 150, 0, 'Efectivo', NULL, '2022-01-12 05:41:19', '2022-01-12 05:41:19'),
(65, 'spr-20220112-014230', 1, NULL, 76, 5, NULL, 120, 0, 'Tarjeta_Credito_Debito', 'test', '2022-01-12 05:42:30', '2022-01-12 05:42:30'),
(66, 'spr-20220112-014813', 18, NULL, 78, 12, NULL, 245, 0, 'Efectivo', NULL, '2022-01-12 05:48:13', '2022-01-12 05:48:13'),
(67, 'spr-20220112-015631', 18, NULL, 79, 12, NULL, 10, 0, 'Efectivo', NULL, '2022-01-12 05:56:31', '2022-01-12 05:56:31'),
(68, 'spr-20220115-032221', 1, NULL, 80, 1, NULL, 150, 0, 'Efectivo', NULL, '2022-01-15 19:22:21', '2022-01-15 19:22:21'),
(69, 'ppr-20220125-124512', 1, 39, NULL, 1, NULL, 633.6, 0, 'Efectivo', 'test 1 pago efectivo', '2022-01-25 04:45:12', '2022-01-25 04:45:12'),
(70, 'ppr-20220125-124829', 1, 38, NULL, 14, NULL, 266, 0, 'Cheque', 'test 2 pago tarjeta', '2022-01-25 04:48:29', '2022-01-25 04:48:29'),
(71, 'ppr-20220125-124904', 1, 37, NULL, 13, NULL, 42, 0, 'Efectivo', NULL, '2022-01-25 04:49:04', '2022-01-25 04:49:04'),
(72, 'spr-20220125-125440', 1, NULL, 81, 1, NULL, 130, 0, 'Efectivo', NULL, '2022-01-25 04:54:40', '2022-01-25 04:54:40'),
(73, 'spr-20220125-012107', 1, NULL, 82, 1, NULL, 280, 0, 'Efectivo', 'update pago', '2022-01-25 05:21:07', '2022-01-26 05:44:59'),
(74, 'spr-20220126-100317', 1, NULL, 83, 1, NULL, 10, 0, 'Efectivo', NULL, '2022-01-26 14:03:17', '2022-01-26 14:03:17'),
(75, 'spr-20220204-040747', 1, NULL, 84, 1, NULL, 50, 60, 'Efectivo', 'debe 60bs', '2022-02-04 20:07:47', '2022-02-04 20:07:47'),
(76, 'spr-20220204-040834', 1, NULL, 84, 1, NULL, 60, 0, 'Qr_Simple', 'Completo con QR Nro 48184811515', '2022-02-04 20:08:34', '2022-02-04 20:08:34'),
(77, 'spr-20220205-123720', 1, NULL, 85, 1, NULL, 75, 0, 'Efectivo', NULL, '2022-02-05 16:37:20', '2022-02-05 16:37:20'),
(78, 'spr-20220205-123742', 1, NULL, 86, 1, NULL, 120, 0, 'Efectivo', NULL, '2022-02-05 16:37:42', '2022-02-05 16:37:42'),
(79, 'spr-20220205-123815', 1, NULL, 87, 1, NULL, 150, 0, 'Efectivo', NULL, '2022-02-05 16:38:15', '2022-02-05 16:38:15'),
(80, 'spr-20220205-021832', 1, NULL, 88, 5, NULL, 150, 0, 'Tarjeta_Credito_Debito', NULL, '2022-02-05 18:18:32', '2022-02-05 18:18:32'),
(81, 'spr-20220208-040943', 1, NULL, 89, 5, NULL, 200, 0, 'Tarjeta_Credito_Debito', NULL, '2022-02-08 20:09:43', '2022-02-08 20:09:43'),
(82, 'ppr-20220208-045356', 1, 40, NULL, 1, NULL, 5, 0, 'Efectivo', 'pago test', '2022-02-08 20:53:57', '2022-02-08 20:53:57'),
(83, 'ppr-20220208-055824', 1, 41, NULL, 1, NULL, 13.2, 0, 'Efectivo', NULL, '2022-02-08 21:58:24', '2022-02-08 21:58:24'),
(84, 'ppr-20220208-055846', 1, 42, NULL, 1, NULL, 13.2, 0, 'Cheque', 'test cheque', '2022-02-08 21:58:46', '2022-02-08 21:58:46'),
(85, 'ppr-20220208-055858', 1, 43, NULL, 1, NULL, 13.2, 0, 'Efectivo', NULL, '2022-02-08 21:58:58', '2022-02-08 21:58:58'),
(86, 'ppr-20220210-091044', 1, 46, NULL, 1, NULL, 29.58, 0, 'Efectivo', NULL, '2022-02-11 01:10:44', '2022-02-11 01:10:44'),
(87, 'ppr-20220212-121959', 1, 48, NULL, 1, NULL, 10, 0, 'Efectivo', NULL, '2022-02-12 04:19:59', '2022-02-12 04:19:59'),
(88, 'ppr-20220212-122012', 1, 49, NULL, 1, NULL, 100, 0, 'Efectivo', NULL, '2022-02-12 04:20:12', '2022-02-12 04:20:12'),
(89, 'ppr-20220212-010147', 1, 55, NULL, 1, NULL, 42.34, 0, 'Efectivo', NULL, '2022-02-12 05:01:47', '2022-02-12 05:01:47'),
(90, 'ppr-20220214-024219', 1, 56, NULL, 1, NULL, 23, 0, 'Efectivo', NULL, '2022-02-14 18:42:19', '2022-02-14 18:42:19'),
(91, 'ppr-20220214-024241', 1, 57, NULL, 13, NULL, 50, 0, 'Cheque', NULL, '2022-02-14 18:42:41', '2022-02-14 18:42:41'),
(94, 'spr-20220215-123835', 1, NULL, 94, 1, NULL, 465, 0, 'Efectivo', NULL, '2022-02-15 04:38:35', '2022-02-15 04:38:35'),
(95, 'spr-20220215-124314', 1, NULL, 95, 1, NULL, 300, 0, 'Efectivo', NULL, '2022-02-15 04:43:14', '2022-02-15 04:43:14'),
(98, 'spr-20220215-010107', 1, NULL, 101, 5, NULL, 70, 0, 'Qr_simple', NULL, '2022-02-15 05:01:07', '2022-02-15 05:01:07'),
(99, 'ppr-20220215-010858', 1, 58, NULL, 1, NULL, 168, 0, 'Efectivo', NULL, '2022-02-15 05:08:58', '2022-02-15 05:08:58'),
(100, 'ppr-20220215-010942', 1, 59, NULL, 1, NULL, 240, 0, 'Efectivo', NULL, '2022-02-15 05:09:42', '2022-02-15 05:09:42'),
(101, 'spr-20220215-054257', 1, NULL, 102, 1, NULL, 24, 0, 'Efectivo', NULL, '2022-02-15 21:42:57', '2022-02-15 21:42:57'),
(105, 'spr-20220215-065640', 1, NULL, 106, 1, NULL, 24, 0, 'Efectivo', NULL, '2022-02-15 22:56:40', '2022-02-15 22:56:40'),
(108, 'spr-20220215-071352', 1, NULL, 109, 1, NULL, 24, 0, 'Efectivo', NULL, '2022-02-15 23:13:52', '2022-02-15 23:13:52'),
(109, 'spr-20220215-073317', 1, NULL, 110, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-15 23:33:17', '2022-02-15 23:33:17'),
(110, 'spr-20220215-073648', 1, NULL, 111, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-15 23:36:48', '2022-02-15 23:36:48'),
(111, 'spr-20220216-044455', 1, NULL, 112, 1, NULL, 40, 0, 'Efectivo', NULL, '2022-02-16 20:44:55', '2022-02-16 20:44:55'),
(112, 'spr-20220216-045118', 1, NULL, 113, 1, NULL, 40, 0, 'Efectivo', NULL, '2022-02-16 20:51:18', '2022-02-16 20:51:18'),
(113, 'spr-20220216-050117', 1, NULL, 114, 1, NULL, 40, 0, 'Efectivo', NULL, '2022-02-16 21:01:17', '2022-02-16 21:01:17'),
(114, 'spr-20220216-050228', 1, NULL, 115, 1, NULL, 40, 0, 'Efectivo', NULL, '2022-02-16 21:02:28', '2022-02-16 21:02:28'),
(115, 'spr-20220216-050431', 1, NULL, 116, 1, NULL, 40, 0, 'Efectivo', NULL, '2022-02-16 21:04:31', '2022-02-16 21:04:31'),
(116, 'spr-20220216-050957', 1, NULL, 117, 1, NULL, 40, 0, 'Efectivo', NULL, '2022-02-16 21:09:57', '2022-02-16 21:09:57'),
(117, 'spr-20220216-051227', 1, NULL, 118, 1, NULL, 40, 0, 'Efectivo', NULL, '2022-02-16 21:12:27', '2022-02-16 21:12:27'),
(118, 'spr-20220216-051308', 1, NULL, 119, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-16 21:13:08', '2022-02-16 21:13:08'),
(119, 'spr-20220216-053040', 1, NULL, 120, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-16 21:30:40', '2022-02-16 21:30:40'),
(120, 'spr-20220216-062105', 1, NULL, 121, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-16 22:21:05', '2022-02-16 22:21:05'),
(121, 'spr-20220216-062140', 1, NULL, 122, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-16 22:21:40', '2022-02-16 22:21:40'),
(122, 'spr-20220216-062251', 1, NULL, 123, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-16 22:22:51', '2022-02-16 22:22:51'),
(123, 'spr-20220216-062303', 1, NULL, 124, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-16 22:23:03', '2022-02-16 22:23:03'),
(124, 'spr-20220216-062410', 1, NULL, 125, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-02-16 22:24:10', '2022-02-16 22:24:10'),
(125, 'spr-20220223-112037', 1, NULL, 126, 1, NULL, 10, 0, 'Efectivo', NULL, '2022-02-24 03:20:37', '2022-02-24 03:20:37'),
(126, 'spr-20220223-113054', 1, NULL, 127, 5, NULL, 15, 0, 'Qr_simple', NULL, '2022-02-24 03:30:54', '2022-02-24 03:30:54'),
(130, 'spr-20220224-060838', 1, NULL, 138, 1, NULL, 10, 0, 'Efectivo', NULL, '2022-02-24 22:08:38', '2022-02-24 22:08:38'),
(131, 'spr-20220224-060914', 1, NULL, 139, 5, NULL, 15, 0, 'Qr_simple', NULL, '2022-02-24 22:09:14', '2022-02-24 22:09:14'),
(132, 'spr-20220303-040408', 1, NULL, 142, 1, NULL, 10, 0, 'Efectivo', NULL, '2022-03-03 20:04:08', '2022-03-03 20:04:08'),
(133, 'spr-20220303-043245', 1, NULL, 144, 1, NULL, 15, 0, 'Efectivo', NULL, '2022-03-03 20:32:45', '2022-03-03 20:32:45'),
(135, 'cpc-20220304-051600', 1, NULL, 134, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-04 21:16:03', '2022-03-04 21:16:03'),
(136, 'cpc-20220304-051618', 1, NULL, 133, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-04 21:16:18', '2022-03-04 21:16:18'),
(137, 'cpc-20220304-052347', 1, NULL, 137, 15, NULL, 20, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-04 21:23:47', '2022-03-04 21:23:47'),
(138, 'cpc-20220304-052347', 1, NULL, 135, 15, NULL, 30, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-04 21:23:47', '2022-03-04 21:23:47'),
(139, 'cpc-20220304-052516', 1, NULL, 140, 15, NULL, 25, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-04 21:25:16', '2022-03-04 21:25:16'),
(141, 'cpc-20220304-053551', 1, NULL, 132, 15, NULL, 15, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-04 21:35:51', '2022-03-04 21:35:51'),
(142, 'spr-20220306-101153', 1, NULL, 145, 1, NULL, 55, 0, 'Efectivo', NULL, '2022-03-07 02:11:53', '2022-03-07 02:11:53'),
(143, 'cpc-20220308-052254', 1, NULL, 141, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-08 21:22:54', '2022-03-08 21:22:54'),
(144, 'cpc-20220308-052341', 1, NULL, 143, 15, NULL, 15, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-08 21:23:41', '2022-03-08 21:23:41'),
(145, 'cpc-20220308-055328', 1, NULL, 148, 15, NULL, 15, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-08 21:53:28', '2022-03-08 21:53:28'),
(146, 'cpc-20220308-094101', 1, NULL, 147, 15, NULL, 30, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 01:41:07', '2022-03-09 01:41:07'),
(147, 'cpc-20220308-094126', 1, NULL, 146, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 01:41:26', '2022-03-09 01:41:26'),
(148, 'cpc-20220308-094635', 1, NULL, 150, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 01:46:35', '2022-03-09 01:46:35'),
(149, 'spr-20220308-100146', 1, NULL, 152, 1, NULL, 24, 0, 'Efectivo', NULL, '2022-03-09 02:01:46', '2022-03-09 02:01:46'),
(150, 'cpc-20220309-120812', 1, NULL, 149, 15, NULL, 50, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 04:08:12', '2022-03-09 04:08:12'),
(151, 'cpc-20220309-120816', 1, NULL, 151, 15, NULL, 20, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 04:08:16', '2022-03-09 04:08:16'),
(152, 'cpc-20220309-125431', 1, NULL, 149, 15, NULL, 35, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 04:54:31', '2022-03-09 04:54:31'),
(153, 'cpc-20220309-060448', 1, NULL, 153, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 22:04:48', '2022-03-09 22:04:48'),
(154, 'cpc-20220309-060449', 1, NULL, 154, 15, NULL, 20, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 22:04:49', '2022-03-09 22:04:49'),
(155, 'cpc-20220309-060818', 1, NULL, 155, 15, NULL, 20, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 22:08:18', '2022-03-09 22:08:18'),
(156, 'cpc-20220309-061524', 1, NULL, 157, 15, NULL, 80, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-09 22:15:24', '2022-03-09 22:15:24'),
(157, 'ppr-20220309-104503', 1, 64, NULL, 1, NULL, 60, 0, 'Efectivo', NULL, '2022-03-10 02:45:03', '2022-03-10 02:45:03'),
(158, 'ppr-20220309-104936', 1, 63, NULL, 1, NULL, 120, 0, 'Efectivo', NULL, '2022-03-10 02:49:36', '2022-03-10 02:49:36'),
(159, 'ppr-20220309-104950', 1, 60, NULL, 1, NULL, 200, 0, 'Efectivo', NULL, '2022-03-10 02:49:50', '2022-03-10 02:49:50'),
(160, 'ppr-20220309-105007', 1, 61, NULL, 1, NULL, 450, 0, 'Cheque', NULL, '2022-03-10 02:50:07', '2022-03-10 02:50:07'),
(161, 'ppr-20220309-105023', 1, 62, NULL, 1, NULL, 530, 0, 'Efectivo', NULL, '2022-03-10 02:50:23', '2022-03-10 02:50:23'),
(162, 'cpc-20220310-033731', 1, NULL, 156, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-10 19:37:31', '2022-03-10 19:37:31'),
(164, 'spr-20220316-060151', 1, NULL, 158, 1, NULL, 170, 0, 'Efectivo', NULL, '2022-03-16 22:01:51', '2022-03-16 22:01:51'),
(165, 'spr-20220316-060219', 1, NULL, 159, 1, NULL, 40, 0, 'Efectivo', NULL, '2022-03-16 22:02:19', '2022-03-16 22:02:19'),
(166, 'spr-20220316-060549', 1, NULL, 161, 5, NULL, 40, 0, 'Qr_simple', NULL, '2022-03-16 22:05:49', '2022-03-16 22:05:49'),
(167, 'spr-20220316-111833', 1, NULL, 162, 1, NULL, 10, 0, 'Efectivo', NULL, '2022-03-17 03:18:33', '2022-03-17 03:18:33'),
(168, 'spr-20220316-112927', 1, NULL, 166, 1, NULL, 10, 0, 'Efectivo', NULL, '2022-03-17 03:29:27', '2022-03-17 03:29:27'),
(170, 'spr-20220316-115050', 1, NULL, 169, 5, NULL, 10, 0, 'Qr_simple', NULL, '2022-03-17 03:50:50', '2022-03-17 03:50:50'),
(171, 'spr-20220316-115224', 1, NULL, 170, 1, NULL, 15, 0, 'Efectivo', NULL, '2022-03-17 03:52:24', '2022-03-17 03:52:24'),
(172, 'spr-20220316-115340', 1, NULL, 171, 5, NULL, 10, 0, 'Qr_simple', NULL, '2022-03-17 03:53:40', '2022-03-17 03:53:40'),
(174, 'cpc-20220322-043825', 1, NULL, 163, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-22 20:38:25', '2022-03-22 20:38:25'),
(175, 'cpc-20220322-043826', 1, NULL, 160, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-22 20:38:26', '2022-03-22 20:38:26'),
(176, 'spr-20220322-082844', 1, NULL, 173, 1, NULL, 10, 0, 'Efectivo', NULL, '2022-03-23 00:28:44', '2022-03-23 00:28:44'),
(177, 'spr-20220324-013115', 1, NULL, 174, 1, NULL, 20, 0, 'Efectivo', NULL, '2022-03-24 05:31:15', '2022-03-24 05:31:15'),
(178, 'cpc-20220324-065056', 1, NULL, 165, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-24 22:50:56', '2022-03-24 22:50:56'),
(179, 'cpc-20220324-065106', 1, NULL, 164, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-24 22:51:06', '2022-03-24 22:51:06'),
(181, 'spr-20220328-042601', 1, NULL, 180, 5, NULL, 10, 0, 'Qr_simple', NULL, '2022-03-28 20:26:01', '2022-03-28 20:26:01'),
(182, 'cpc-20220329-043312', 1, NULL, 177, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: test cliente', '2022-03-29 20:33:21', '2022-03-29 20:33:21'),
(183, 'cpc-20220329-043553', 1, NULL, 178, 15, NULL, 15, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: test cliente', '2022-03-29 20:35:57', '2022-03-29 20:35:57'),
(184, 'cpc-20220329-044608', 1, NULL, 182, 15, NULL, 50, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: Casera Maria', '2022-03-29 20:46:23', '2022-03-29 20:46:23'),
(185, 'cpc-20220329-052824', 1, NULL, 182, 15, NULL, 35, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: Casera Maria', '2022-03-29 21:28:24', '2022-03-29 21:28:24'),
(186, 'cpc-20220329-053910', 1, NULL, 183, 15, NULL, 20, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: Casera Maria', '2022-03-29 21:39:10', '2022-03-29 21:39:10'),
(187, 'cpc-20220329-054020', 1, NULL, 184, 15, NULL, 50, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: Casera Maria', '2022-03-29 21:40:20', '2022-03-29 21:40:20'),
(188, 'cpc-20220330-033200', 1, NULL, 175, 15, NULL, 30, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: Clientes Varios', '2022-03-30 19:32:00', '2022-03-30 19:32:00'),
(189, 'cpc-20220330-033201', 1, NULL, 176, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: Clientes Varios', '2022-03-30 19:32:01', '2022-03-30 19:32:01'),
(190, 'cpc-20220330-033201', 1, NULL, 179, 15, NULL, 10, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: Clientes Varios', '2022-03-30 19:32:01', '2022-03-30 19:32:01'),
(191, 'cpc-20220330-033201', 1, NULL, 181, 15, NULL, 40, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar del cliente: Clientes Varios', '2022-03-30 19:32:01', '2022-03-30 19:32:01'),
(192, 'cpc-20220330-043027', 1, NULL, 181, 15, NULL, 45, 0, 'Efectivo', 'Pago procesado con cuentas por cobrar', '2022-03-30 20:30:27', '2022-03-30 20:30:27'),
(193, 'ppr-20220330-043402', 1, 69, NULL, 1, NULL, 101.5, 0, 'Efectivo', NULL, '2022-03-30 20:34:02', '2022-03-30 20:34:02'),
(194, 'ppr-20220330-043417', 1, 70, NULL, 1, NULL, 813.4, 0, 'Efectivo', NULL, '2022-03-30 20:34:17', '2022-03-30 20:34:17'),
(195, 'spr-20220425-061739', 1, NULL, 187, 1, NULL, 120, 0, 'Efectivo', NULL, '2022-04-25 22:17:39', '2022-04-25 22:17:39'),
(196, 'spr-20220425-061818', 1, NULL, 188, 5, NULL, 150, 0, 'Qr_simple', NULL, '2022-04-25 22:18:18', '2022-04-25 22:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `payment_with_cheque`
--

CREATE TABLE `payment_with_cheque` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(11) NOT NULL,
  `cheque_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_with_cheque`
--

INSERT INTO `payment_with_cheque` (`id`, `payment_id`, `cheque_no`, `created_at`, `updated_at`) VALUES
(1, 32, '12323446', '2021-12-13 17:42:52', '2021-12-13 17:42:52'),
(2, 36, '234546564', '2021-12-13 22:36:03', '2021-12-13 22:36:03'),
(3, 38, '545346546', '2021-12-13 23:33:13', '2021-12-13 23:33:13'),
(4, 42, '45665535', '2021-12-14 02:13:42', '2021-12-14 02:13:42'),
(5, 48, '451545', '2021-12-21 14:46:57', '2021-12-21 14:46:57'),
(6, 51, '44514848', '2021-12-22 22:01:14', '2021-12-22 22:01:14'),
(7, 70, '4555545454', '2022-01-25 04:48:29', '2022-01-25 04:48:29'),
(8, 84, '25151510515', '2022-02-08 21:58:46', '2022-02-08 21:58:46'),
(9, 91, '941949', '2022-02-14 18:42:41', '2022-02-14 18:42:41'),
(10, 160, '85854757', '2022-03-10 02:50:07', '2022-03-10 02:50:07');

-- --------------------------------------------------------

--
-- Table structure for table `payment_with_credit_card`
--

CREATE TABLE `payment_with_credit_card` (
  `id` int(10) UNSIGNED NOT NULL,
  `payment_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_stripe_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `charge_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_with_credit_card`
--

INSERT INTO `payment_with_credit_card` (`id`, `payment_id`, `customer_id`, `customer_stripe_id`, `charge_id`, `created_at`, `updated_at`) VALUES
(1, 63, 18, 'POSEXT-61dd0ba1af2e8', '61dd0ba1af2f9', '2022-01-11 04:46:25', '2022-01-11 04:46:25'),
(2, 65, 18, 'POSEXT-61dd0ba1af2e8', '61de6a46853b7', '2022-01-12 05:42:30', '2022-01-12 05:42:30'),
(3, 80, 18, 'POSEXT-61dd0ba1af2e8', '61febf78ec414', '2022-02-05 18:18:32', '2022-02-05 18:18:32'),
(4, 81, 18, 'POSEXT-61dd0ba1af2e8', '6202ce072e0a2', '2022-02-08 20:09:43', '2022-02-08 20:09:43');

-- --------------------------------------------------------

--
-- Table structure for table `payment_with_gift_card`
--

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

CREATE TABLE `payment_with_receivable` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `sales` text NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payment_with_receivable`
--

INSERT INTO `payment_with_receivable` (`id`, `account_id`, `user_id`, `amount`, `sales`, `status`, `created_at`, `updated_at`) VALUES
(1, 15, 1, 20, '134,133', 1, '2022-03-04 17:16:18', '2022-03-04 21:16:18'),
(2, 15, 1, 40, '137,135', 1, '2022-03-04 17:23:47', '2022-03-04 21:23:47'),
(3, 15, 1, 25, '140', 1, '2022-03-04 17:25:16', '2022-03-04 21:25:16'),
(5, 15, 1, 15, '132', 1, '2022-03-04 17:35:51', '2022-03-04 21:35:51'),
(6, 15, 1, 10, '141', 1, '2022-03-08 17:22:55', '2022-03-08 21:22:55'),
(7, 15, 1, 15, '143', 1, '2022-03-08 17:23:41', '2022-03-08 21:23:41'),
(8, 15, 1, 15, '148', 1, '2022-03-08 17:53:28', '2022-03-08 21:53:28'),
(9, 15, 1, 40, '147,146', 1, '2022-03-08 21:41:26', '2022-03-09 01:41:26'),
(10, 15, 1, 10, '150', 1, '2022-03-08 21:46:35', '2022-03-09 01:46:35'),
(11, 15, 1, 70, '149,151', 1, '2022-03-09 00:08:16', '2022-03-09 04:08:16'),
(12, 15, 1, 35, '149', 1, '2022-03-09 00:54:32', '2022-03-09 04:54:32'),
(13, 15, 1, 30, '153,154', 1, '2022-03-09 18:04:49', '2022-03-09 22:04:49'),
(14, 15, 1, 20, '155', 1, '2022-03-09 18:08:18', '2022-03-09 22:08:18'),
(15, 15, 1, 80, '157', 1, '2022-03-09 18:15:24', '2022-03-09 22:15:24'),
(16, 15, 1, 10, '156', 1, '2022-03-10 15:37:32', '2022-03-10 19:37:32'),
(17, 15, 1, 20, '163,160', 1, '2022-03-22 16:38:26', '2022-03-22 20:38:26'),
(18, 15, 1, 20, '165,164', 1, '2022-03-24 18:51:15', '2022-03-24 22:51:15'),
(19, 15, 1, 25, '177,178', 1, '2022-03-29 16:36:12', '2022-03-29 20:36:12'),
(20, 15, 1, 35, '182', 1, '2022-03-29 17:28:50', '2022-03-29 21:28:50'),
(21, 15, 1, 70, '183,184', 1, '2022-03-29 17:40:42', '2022-03-29 21:40:42'),
(22, 15, 1, 60, '175,176,179,181', 1, '2022-03-30 15:32:01', '2022-03-30 19:32:01'),
(23, 15, 1, 45, '181', 1, '2022-03-30 16:30:27', '2022-03-30 20:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `paying_method` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payrolls`
--

INSERT INTO `payrolls` (`id`, `reference_no`, `employee_id`, `account_id`, `user_id`, `amount`, `paying_method`, `note`, `created_at`, `updated_at`) VALUES
(1, 'payroll-20220131-053025', 2, 1, 1, 100, '0', 'test1', '2022-01-31 21:30:25', '2022-01-31 21:30:25'),
(2, 'payroll-20220131-053049', 3, 12, 1, 1500, '0', NULL, '2022-01-31 21:30:49', '2022-01-31 21:30:49'),
(3, 'payroll-20220131-053109', 6, 12, 1, 500, '0', NULL, '2022-01-31 21:31:09', '2022-01-31 21:31:09'),
(4, 'payroll-20220131-053125', 2, 1, 1, 1000, '0', NULL, '2022-01-31 21:31:25', '2022-01-31 21:31:25');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(4, 'products-edit', 'web', '2018-06-03 01:00:09', '2018-06-03 01:00:09'),
(5, 'products-delete', 'web', '2018-06-03 22:54:22', '2018-06-03 22:54:22'),
(6, 'products-add', 'web', '2018-06-04 00:34:14', '2018-06-04 00:34:14'),
(7, 'products-index', 'web', '2018-06-04 03:34:27', '2018-06-04 03:34:27'),
(8, 'purchases-index', 'web', '2018-06-04 08:03:19', '2018-06-04 08:03:19'),
(9, 'purchases-add', 'web', '2018-06-04 08:12:25', '2018-06-04 08:12:25'),
(10, 'purchases-edit', 'web', '2018-06-04 09:47:36', '2018-06-04 09:47:36'),
(11, 'purchases-delete', 'web', '2018-06-04 09:47:36', '2018-06-04 09:47:36'),
(12, 'sales-index', 'web', '2018-06-04 10:49:08', '2018-06-04 10:49:08'),
(13, 'sales-add', 'web', '2018-06-04 10:49:52', '2018-06-04 10:49:52'),
(14, 'sales-edit', 'web', '2018-06-04 10:49:52', '2018-06-04 10:49:52'),
(15, 'sales-delete', 'web', '2018-06-04 10:49:53', '2018-06-04 10:49:53'),
(16, 'quotes-index', 'web', '2018-06-04 22:05:10', '2018-06-04 22:05:10'),
(17, 'quotes-add', 'web', '2018-06-04 22:05:10', '2018-06-04 22:05:10'),
(18, 'quotes-edit', 'web', '2018-06-04 22:05:10', '2018-06-04 22:05:10'),
(19, 'quotes-delete', 'web', '2018-06-04 22:05:10', '2018-06-04 22:05:10'),
(20, 'transfers-index', 'web', '2018-06-04 22:30:03', '2018-06-04 22:30:03'),
(21, 'transfers-add', 'web', '2018-06-04 22:30:03', '2018-06-04 22:30:03'),
(22, 'transfers-edit', 'web', '2018-06-04 22:30:03', '2018-06-04 22:30:03'),
(23, 'transfers-delete', 'web', '2018-06-04 22:30:03', '2018-06-04 22:30:03'),
(24, 'returns-index', 'web', '2018-06-04 22:50:24', '2018-06-04 22:50:24'),
(25, 'returns-add', 'web', '2018-06-04 22:50:24', '2018-06-04 22:50:24'),
(26, 'returns-edit', 'web', '2018-06-04 22:50:25', '2018-06-04 22:50:25'),
(27, 'returns-delete', 'web', '2018-06-04 22:50:25', '2018-06-04 22:50:25'),
(28, 'customers-index', 'web', '2018-06-04 23:15:54', '2018-06-04 23:15:54'),
(29, 'customers-add', 'web', '2018-06-04 23:15:55', '2018-06-04 23:15:55'),
(30, 'customers-edit', 'web', '2018-06-04 23:15:55', '2018-06-04 23:15:55'),
(31, 'customers-delete', 'web', '2018-06-04 23:15:55', '2018-06-04 23:15:55'),
(32, 'suppliers-index', 'web', '2018-06-04 23:40:12', '2018-06-04 23:40:12'),
(33, 'suppliers-add', 'web', '2018-06-04 23:40:12', '2018-06-04 23:40:12'),
(34, 'suppliers-edit', 'web', '2018-06-04 23:40:12', '2018-06-04 23:40:12'),
(35, 'suppliers-delete', 'web', '2018-06-04 23:40:12', '2018-06-04 23:40:12'),
(36, 'product-report', 'web', '2018-06-24 23:05:33', '2018-06-24 23:05:33'),
(37, 'purchase-report', 'web', '2018-06-24 23:24:56', '2018-06-24 23:24:56'),
(38, 'sale-report', 'web', '2018-06-24 23:33:13', '2018-06-24 23:33:13'),
(39, 'customer-report', 'web', '2018-06-24 23:36:51', '2018-06-24 23:36:51'),
(40, 'due-report', 'web', '2018-06-24 23:39:52', '2018-06-24 23:39:52'),
(41, 'users-index', 'web', '2018-06-25 00:00:10', '2018-06-25 00:00:10'),
(42, 'users-add', 'web', '2018-06-25 00:00:10', '2018-06-25 00:00:10'),
(43, 'users-edit', 'web', '2018-06-25 00:01:30', '2018-06-25 00:01:30'),
(44, 'users-delete', 'web', '2018-06-25 00:01:30', '2018-06-25 00:01:30'),
(45, 'profit-loss', 'web', '2018-07-14 21:50:05', '2018-07-14 21:50:05'),
(46, 'best-seller', 'web', '2018-07-14 22:01:38', '2018-07-14 22:01:38'),
(47, 'daily-sale', 'web', '2018-07-14 22:24:21', '2018-07-14 22:24:21'),
(48, 'monthly-sale', 'web', '2018-07-14 22:30:41', '2018-07-14 22:30:41'),
(49, 'daily-purchase', 'web', '2018-07-14 22:36:46', '2018-07-14 22:36:46'),
(50, 'monthly-purchase', 'web', '2018-07-14 22:48:17', '2018-07-14 22:48:17'),
(51, 'payment-report', 'web', '2018-07-14 23:10:41', '2018-07-14 23:10:41'),
(52, 'warehouse-stock-report', 'web', '2018-07-14 23:16:55', '2018-07-14 23:16:55'),
(53, 'product-qty-alert', 'web', '2018-07-14 23:33:21', '2018-07-14 23:33:21'),
(54, 'supplier-report', 'web', '2018-07-30 03:00:01', '2018-07-30 03:00:01'),
(55, 'expenses-index', 'web', '2018-09-05 01:07:10', '2018-09-05 01:07:10'),
(56, 'expenses-add', 'web', '2018-09-05 01:07:10', '2018-09-05 01:07:10'),
(57, 'expenses-edit', 'web', '2018-09-05 01:07:10', '2018-09-05 01:07:10'),
(58, 'expenses-delete', 'web', '2018-09-05 01:07:11', '2018-09-05 01:07:11'),
(59, 'general_setting', 'web', '2018-10-19 23:10:04', '2018-10-19 23:10:04'),
(60, 'mail_setting', 'web', '2018-10-19 23:10:04', '2018-10-19 23:10:04'),
(61, 'pos_setting', 'web', '2018-10-19 23:10:04', '2018-10-19 23:10:04'),
(62, 'hrm_setting', 'web', '2019-01-02 10:30:23', '2019-01-02 10:30:23'),
(63, 'purchase-return-index', 'web', '2019-01-02 21:45:14', '2019-01-02 21:45:14'),
(64, 'purchase-return-add', 'web', '2019-01-02 21:45:14', '2019-01-02 21:45:14'),
(65, 'purchase-return-edit', 'web', '2019-01-02 21:45:14', '2019-01-02 21:45:14'),
(66, 'purchase-return-delete', 'web', '2019-01-02 21:45:14', '2019-01-02 21:45:14'),
(67, 'account-index', 'web', '2019-01-02 22:06:13', '2019-01-02 22:06:13'),
(68, 'balance-sheet', 'web', '2019-01-02 22:06:14', '2019-01-02 22:06:14'),
(69, 'account-statement', 'web', '2019-01-02 22:06:14', '2019-01-02 22:06:14'),
(70, 'department', 'web', '2019-01-02 22:30:01', '2019-01-02 22:30:01'),
(71, 'attendance', 'web', '2019-01-02 22:30:01', '2019-01-02 22:30:01'),
(72, 'payroll', 'web', '2019-01-02 22:30:01', '2019-01-02 22:30:01'),
(73, 'employees-index', 'web', '2019-01-02 22:52:19', '2019-01-02 22:52:19'),
(74, 'employees-add', 'web', '2019-01-02 22:52:19', '2019-01-02 22:52:19'),
(75, 'employees-edit', 'web', '2019-01-02 22:52:19', '2019-01-02 22:52:19'),
(76, 'employees-delete', 'web', '2019-01-02 22:52:19', '2019-01-02 22:52:19'),
(77, 'user-report', 'web', '2019-01-16 06:48:18', '2019-01-16 06:48:18'),
(78, 'stock_count', 'web', '2019-02-17 10:32:01', '2019-02-17 10:32:01'),
(79, 'adjustment', 'web', '2019-02-17 10:32:02', '2019-02-17 10:32:02'),
(80, 'sms_setting', 'web', '2019-02-22 05:18:03', '2019-02-22 05:18:03'),
(81, 'create_sms', 'web', '2019-02-22 05:18:03', '2019-02-22 05:18:03'),
(82, 'print_barcode', 'web', '2019-03-07 05:02:19', '2019-03-07 05:02:19'),
(83, 'empty_database', 'web', '2019-03-07 05:02:19', '2019-03-07 05:02:19'),
(84, 'customer_group', 'web', '2019-03-07 05:37:15', '2019-03-07 05:37:15'),
(85, 'unit', 'web', '2019-03-07 05:37:15', '2019-03-07 05:37:15'),
(86, 'tax', 'web', '2019-03-07 05:37:15', '2019-03-07 05:37:15'),
(87, 'gift_card', 'web', '2019-03-07 06:29:38', '2019-03-07 06:29:38'),
(88, 'coupon', 'web', '2019-03-07 06:29:38', '2019-03-07 06:29:38'),
(89, 'holiday', 'web', '2019-10-19 08:57:15', '2019-10-19 08:57:15'),
(90, 'warehouse-report', 'web', '2019-10-22 06:00:23', '2019-10-22 06:00:23'),
(91, 'warehouse', 'web', '2020-02-26 06:47:32', '2020-02-26 06:47:32'),
(92, 'brand', 'web', '2020-02-26 06:59:59', '2020-02-26 06:59:59'),
(93, 'billers-index', 'web', '2020-02-26 07:11:15', '2020-02-26 07:11:15'),
(94, 'billers-add', 'web', '2020-02-26 07:11:15', '2020-02-26 07:11:15'),
(95, 'billers-edit', 'web', '2020-02-26 07:11:15', '2020-02-26 07:11:15'),
(96, 'billers-delete', 'web', '2020-02-26 07:11:15', '2020-02-26 07:11:15'),
(97, 'money-transfer', 'web', '2020-03-02 05:41:48', '2020-03-02 05:41:48'),
(98, 'category', 'web', '2020-07-13 12:13:16', '2020-07-13 12:13:16'),
(99, 'delivery', 'web', '2020-07-13 12:13:16', '2020-07-13 12:13:16'),
(101, 'module_qr', 'web', '2021-07-09 03:17:47', '2021-07-09 03:17:47'),
(102, 'salebiller-report', 'web', '2021-12-03 22:42:19', NULL),
(103, 'salecustomer-report', 'web', '2021-12-03 22:42:30', '2021-12-28 21:05:29'),
(104, 'adjustment-account-index', 'web', '2021-12-28 20:49:46', '2021-12-28 21:05:35'),
(105, 'module_siat', 'web', '2022-07-06 22:09:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personas`
--

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
  `id_garante1` int(10) NOT NULL,
  `id_garante2` int(10) NOT NULL,
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
  `print` tinyint(1) NOT NULL DEFAULT '1',
  `type_print` int(11) DEFAULT NULL,
  `date_sell` tinyint(1) NOT NULL DEFAULT '0',
  `print_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos_setting`
--

INSERT INTO `pos_setting` (`id`, `customer_id`, `warehouse_id`, `biller_id`, `product_number`, `keybord_active`, `stripe_public_key`, `stripe_secret_key`, `t_c`, `print`, `type_print`, `date_sell`, `print_order`, `created_at`, `updated_at`) VALUES
(1, 18, 1, 1, 4, 0, 'aaaadfasdfa', 'adfasdfasd', '6.96', 0, 2, 0, 0, '2018-09-02 03:17:04', '2022-03-23 00:31:31');

-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

CREATE TABLE `printers` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `printer` varchar(200) NOT NULL,
  `host_address` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'TYPE = TCP/IP - SHARED',
  `category_id` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `printers`
--

INSERT INTO `printers` (`id`, `name`, `printer`, `host_address`, `type`, `category_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PDF', 'Microsoft Print to PDF', 'localhost', 'tcp/ip', 4, 1, '2022-02-15 17:40:12', '2022-02-19 04:15:25'),
(2, 'Virtual Test Printer', 'VirtualPrintTest', 'JCCM-17', 'shared', 0, 1, '2022-02-15 18:49:05', '2022-02-15 22:49:42');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

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
  `price_a` double NOT NULL DEFAULT '0',
  `price_b` double NOT NULL DEFAULT '0',
  `price_c` double NOT NULL DEFAULT '0',
  `qty` double DEFAULT NULL,
  `alert_quantity` double DEFAULT NULL,
  `promotion` tinyint(4) DEFAULT NULL,
  `promotion_price` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starting_date` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_date` date DEFAULT NULL,
  `tax_id` int(11) DEFAULT NULL,
  `tax_method` int(11) DEFAULT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci,
  `file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_variant` tinyint(1) DEFAULT NULL,
  `featured` tinyint(4) DEFAULT NULL,
  `product_list` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_list` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_list` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_details` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT NULL,
  `courtesy` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FALSE',
  `permanent` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TRUE',
  `starting_date_courtesy` date DEFAULT NULL,
  `ending_date_courtesy` date DEFAULT NULL,
  `courtesy_clearance_price` double DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `code`, `type`, `barcode_symbology`, `brand_id`, `category_id`, `unit_id`, `purchase_unit_id`, `sale_unit_id`, `cost`, `price`, `price_a`, `price_b`, `price_c`, `qty`, `alert_quantity`, `promotion`, `promotion_price`, `starting_date`, `last_date`, `tax_id`, `tax_method`, `image`, `file`, `is_variant`, `featured`, `product_list`, `qty_list`, `price_list`, `product_details`, `is_active`, `courtesy`, `permanent`, `starting_date_courtesy`, `ending_date_courtesy`, `courtesy_clearance_price`, `created_at`, `updated_at`) VALUES
(476, 'Tabaco kumbaya*', '55470330', 'standard', 'C128', 3, 5, 22, 22, 22, '45', '58', 0, 0, 0, 0, 6, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, 1, 0, NULL, NULL, NULL, '', 0, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-11-30 17:09:03', '2021-12-12 19:58:06'),
(477, 'Vapeador', '80696015', 'standard', 'C128', 6, 5, 1, 1, 1, '42.00', '55', 0, 0, 0, 10, 10, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, 1, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 17:23:10', '2022-04-26 22:18:54'),
(478, 'Peinado', '13413521', 'digital', 'C128', NULL, 4, 0, 0, 0, '0', '30', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 17:48:47', '2021-12-01 17:48:47'),
(479, 'Barba c/ Maquina', '03013758', 'digital', 'C128', NULL, 4, 0, 0, 0, '0', '50', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 22:21:50', '2021-12-01 22:21:50'),
(480, 'Degradado', '16520683', 'digital', 'C128', NULL, 4, 0, 0, 0, '0', '70', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 22:37:22', '2021-12-01 22:37:22'),
(481, 'Barba c/ navaja y toalla caliente', '72153239', 'digital', 'C128', NULL, 4, 0, 0, 0, '0', '90', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 22:39:31', '2021-12-01 22:39:31'),
(482, 'Afeitado tradicional con toalla caliente', '84295002', 'digital', 'C128', NULL, 4, 0, 0, 0, '0', '100', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 22:55:53', '2022-03-23 00:36:53'),
(483, 'Corte de cabello', '01490893', 'digital', 'C128', NULL, 4, 0, 0, 0, '0', '120', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 22:57:59', '2021-12-01 22:57:59'),
(484, 'Corte de cabello y barba c/ maquina', '54779684', 'digital', 'C128', NULL, 4, 0, 0, 0, '0', '150', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 23:06:18', '2021-12-01 23:06:18'),
(485, 'Corte de cabello y barba c/ navaja y toalla caliente', '17206957', 'digital', 'C128', NULL, 4, 0, 0, 0, '0', '170', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 23:08:47', '2021-12-01 23:08:47'),
(486, 'Cerveza', '56912100', 'standard', 'C128', 12, 5, 29, 29, 29, '9.17', '20', 0, 0, 0, 22, 6, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 23:43:36', '2022-03-25 22:10:29'),
(487, 'Mandala Té frío frambuesa', '15029440', 'standard', 'C128', 22, 5, 30, 30, 30, '5.62', '10', 0, 0, 0, 14, 6, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 23:57:00', '2022-02-09 20:51:34'),
(488, 'Mandala Té frío durazno', '11501299', 'standard', 'C128', 22, 5, 30, 30, 30, '5.62', '10', 0, 0, 0, 18, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-01 23:58:06', '2022-03-18 19:14:45'),
(489, 'Tabacco', '48621517', 'standard', 'C128', 19, 5, 31, 31, 31, '45', '75', 0, 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-04 20:17:20', '2021-12-12 19:56:01'),
(490, 'Tabacco Apache', '32928720', 'standard', 'C128', 20, 5, 31, 31, 31, '42.00', '75', 0, 0, 0, 9, 3, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-04 20:23:34', '2022-03-25 22:10:30'),
(491, 'Tabacco Harlequin', '32120587', 'standard', 'C128', 27, 5, 31, 31, 31, '40', '75', 0, 0, 0, 5, 2, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-04 20:34:39', '2022-02-14 22:09:29'),
(492, 'Gin tonic', '60350296', 'standard', 'C128', 16, 5, 26, 26, 26, '10', '20', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-04 22:57:30', '2021-12-04 22:57:30'),
(493, 'coco', '41236320', 'standard', 'C128', NULL, 5, 1, 1, 1, '8', '15', 0, 0, 0, 7, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'TRUE', 'TRUE', NULL, NULL, 8, '2021-12-06 17:13:33', '2022-04-26 22:00:23'),
(494, 'Café', '23001941', 'standard', 'C128', 28, 5, 32, 32, 32, '3.00', '10', 12, 9.5, 0, 14, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-06 17:46:41', '2022-04-26 21:16:21'),
(495, 'Coca Cola', '90820471', 'standard', 'C128', 11, 5, 33, 33, 33, '5.00', '10', 0, 0, 0, -12, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-06 17:52:26', '2022-03-17 03:04:05'),
(496, 'Coca cola zero', '98584202', 'standard', 'C128', 11, 5, 33, 33, 33, '5', '10', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-06 17:53:33', '2022-03-23 00:28:31'),
(497, 'Sprite', '49521831', 'standard', 'C128', 11, 5, 33, 33, 33, '5', '10', 0, 0, 0, 19, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-06 17:54:27', '2022-02-11 01:22:44'),
(498, 'Blunts Lion Rolling Circus', '50493715', 'standard', 'C128', 8, 5, 1, 1, 1, '11.20', '20', 0, 0, 0, 13, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-07 17:31:55', '2021-12-12 20:42:19'),
(499, 'Filtros de cartón LRC', '16765449', 'standard', 'C128', 8, 5, 25, 25, 25, '25', '50', 0, 0, 0, 4, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-07 17:37:32', '2022-01-11 04:46:10'),
(500, 'Bandeja Raw', '03429178', 'standard', 'C128', 14, 5, 1, 1, 1, '50', '80', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-07 18:17:03', '2022-03-14 22:12:12'),
(501, 'Phillies Blunts', '49093238', 'standard', 'C128', 9, 5, 1, 1, 1, '14', '20', 0, 0, 0, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-07 18:34:06', '2022-02-16 21:12:27'),
(502, 'Enroladora acrílica', '50819398', 'standard', 'C128', NULL, 5, 1, 1, 1, '50', '70', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-07 18:42:55', '2022-02-04 20:07:46'),
(503, 'Papelillo LRC', '70351927', 'standard', 'C128', 8, 5, 1, 1, 1, '6.6', '12', 0, 0, 0, 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-07 19:02:38', '2022-02-08 21:58:10'),
(504, 'Cafe Creme', '99062703', 'standard', 'C128', 29, 5, 25, 25, 25, '48', '80', 0, 0, 0, 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 16:44:38', '2022-02-15 04:33:11'),
(505, 'Cafe Creme c/filtro', '65912364', 'standard', 'C128', 24, 5, 25, 25, 25, '48', '90', 0, 0, 0, 11, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 16:55:03', '2022-02-15 04:47:24'),
(506, 'Papelillo OCB', '11298466', 'standard', 'C128', 7, 5, 1, 1, 1, '7.2', '12', 0, 0, 0, 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 17:03:58', '2022-02-16 19:22:21'),
(507, 'Filtro OCB (carton)', '38297144', 'standard', 'C128', 7, 5, 1, 1, 1, '9', '15', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 17:06:52', '2021-12-12 20:08:36'),
(508, 'Filtro OCB (goma)', '86945467', 'standard', 'C128', 7, 5, 34, 34, 34, '18', '30', 0, 0, 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 17:09:32', '2021-12-12 20:08:36'),
(509, 'Cenicero OCB', '71890163', 'standard', 'C128', 7, 5, 1, 1, 1, '20', '35', 0, 0, 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 17:11:53', '2021-12-12 20:08:36'),
(510, 'Cenicero Bulldog', '05139014', 'standard', 'C128', 29, 5, 1, 1, 1, '35', '50', 0, 0, 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 17:13:17', '2021-12-12 20:08:36'),
(511, 'Rejilla', '25241843', 'standard', 'C128', 29, 5, 1, 1, 1, '4', '8', 0, 0, 0, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 19:10:23', '2021-12-12 20:42:20'),
(512, 'Limpia pipa', '16902357', 'standard', 'C128', 29, 5, 35, 35, 35, '1', '1.25', 0, 0, 0, 97, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 19:12:52', '2022-05-20 21:19:25'),
(513, 'Bebida Isotonica (Sante)', '44391732', 'standard', 'C128', 29, 5, 33, 33, 33, '4.76', '10', 0, 0, 0, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 19:19:48', '2021-12-12 20:42:21'),
(514, 'Santa Maria', '94671310', 'standard', 'C128', 29, 5, 33, 33, 33, '5.8', '10', 0, 0, 0, 17, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 19:49:26', '2022-05-20 21:20:11'),
(515, 'Agua embotellada', '73640021', 'standard', 'C128', 29, 5, 33, 33, 33, '2.5', '10', 10, 9, 9.5, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 19:50:23', '2022-04-26 21:16:03'),
(516, 'Camisas selvatica', '57192937', 'standard', 'C128', 29, 5, 1, 1, 1, '85', '100', 0, 0, 0, 6, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 19:52:27', '2021-12-12 20:42:21'),
(517, 'Chill pins', '67119582', 'standard', 'C128', 29, 5, 1, 1, 1, '0', '0', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 19:53:13', '2021-12-12 19:53:13'),
(518, 'Tabacco pueblo', '73965392', 'standard', 'C128', 29, 5, 31, 31, 31, '45', '75', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 19:59:45', '2021-12-12 20:08:35'),
(519, 'Tabacco Kumbaya', '45091513', 'standard', 'C128', 3, 5, 22, 22, 22, '45', '58', 0, 0, 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-12 20:00:50', '2021-12-12 20:08:35'),
(520, 'Suavecito Crema p/cabello', '13204671', 'standard', 'C128', 29, 4, 1, 1, 1, '103.5', '200', 0, 0, 0, 8, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 16:56:52', '2021-12-13 17:42:52'),
(521, 'Pin Sol', '21447651', 'standard', 'C128', 29, 5, 1, 1, 1, '23', '30', 0, 0, 0, 2, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:23:06', '2022-02-11 01:12:34'),
(522, 'Pin cerveza', '01180423', 'standard', 'C128', 29, 5, 1, 1, 1, '23', '30', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:23:47', '2021-12-13 17:34:54'),
(523, 'Pin Bekind', '91521020', 'standard', 'C128', 29, 5, 1, 1, 1, '23', '30', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:24:31', '2021-12-13 17:34:54'),
(524, 'Pin Looking Good', '21953940', 'standard', 'C128', 29, 5, 1, 1, 1, '23', '30', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:25:13', '2021-12-13 17:34:54'),
(525, 'Pin Buzzin', '35024190', 'standard', 'C128', NULL, 5, 37, 37, 37, '87', '115', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:26:55', '2021-12-13 17:34:54'),
(526, 'Pin Anime', '27375435', 'standard', 'C128', 29, 5, 1, 1, 1, '23', '30', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:27:35', '2021-12-13 17:34:55'),
(527, 'Pin Guitarra', '00152933', 'standard', 'C128', 29, 5, 1, 1, 1, '23', '30', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:29:02', '2021-12-13 17:34:55'),
(528, 'Pin Cool stuff', '60439103', 'standard', 'C128', 29, 5, 1, 1, 1, '23', '30', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:30:05', '2021-12-13 17:34:55'),
(529, 'Pin Rosa', '36138486', 'standard', 'C128', 29, 5, 1, 1, 1, '18', '25', 0, 0, 0, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2021-12-13 17:31:54', '2021-12-13 17:34:55'),
(530, 'Barberia', '62412959', 'combo', 'C128', 3, 5, 0, 0, 0, '0', '320', 0, 0, 0, 0, NULL, 1, '310', '2022-02-07', '2022-02-12', NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, '491,490,495,491,490,495', '1,1,1,1,1,1', '75,75,10,75,75,10', '', 0, 'FALSE', 'TRUE', NULL, NULL, NULL, '2022-02-07 20:24:56', '2022-02-07 20:29:08'),
(531, 'Barberia', '99380600', 'combo', 'C128', 3, 5, 0, 0, 0, '0', '160', 0, 0, 0, 0, NULL, 1, '150', '2022-02-07', '2022-02-12', NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, '490,491,495', '1,1,1', '75,75,10', '<p>test combo</p>', 1, 'FALSE', 'TRUE', NULL, NULL, NULL, '2022-02-07 20:51:44', '2022-02-07 20:51:44'),
(532, 'Paracetamol 500mg', '05160304', 'standard', 'C128', 4, 5, 38, 39, 38, '10', '1.50', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '<p>testt </p>', 0, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-02-09 22:09:39', '2022-02-09 22:14:40'),
(533, 'Paracetamol 500mg', '69336019', 'standard', 'C128', 4, 5, 39, 39, 38, '1', '1.50', 0, 0, 0, 10, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, NULL, NULL, NULL, '<p>test</p>', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-02-09 22:16:38', '2022-02-09 22:17:57'),
(534, 'Pan Mediano', '90135100', 'insumo', 'C128', NULL, 6, 1, 1, 1, '2.50', '2.50', 0, 0, 0, 4, 10, NULL, NULL, NULL, NULL, NULL, 1, '1645480142092como_hacer_pan_de_hamburguesa_32638_600.jpg', NULL, NULL, NULL, NULL, NULL, NULL, '<p>producto insumo</p>', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-02-21 21:49:58', '2022-04-26 22:18:53'),
(535, 'Carne Mediana', '78731919', 'insumo', 'C128', NULL, 6, 40, 40, 40, '3.20', '5', 0, 0, 0, 0, 10, NULL, NULL, NULL, NULL, NULL, 1, '1645480450363hamburguesa-casera_700x465.jpg', NULL, NULL, NULL, NULL, NULL, NULL, '<p>Producto Insumo</p>', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-02-21 21:56:24', '2022-04-26 22:18:53'),
(536, 'Hamburguesa Clasica', '05461293', 'producto_terminado', 'C128', NULL, 6, 0, 0, 0, '6.00', '10', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, '1645672654531depositphotos_15890699-stock-photo-big-hamburger.jpg', NULL, NULL, 1, '534,535', '1,1', '2,4', '<p>test producto terminado</p>', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-02-24 03:18:00', '2022-03-07 02:11:06'),
(537, 'Hamburguesa Mediana', '98503776', 'producto_terminado', 'C128', NULL, 6, 0, 0, 0, '11.20', '15', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, '164567308200403efcc56e44728357b07399e44c0121c.png', NULL, NULL, 1, '534,535,541', '1,2,1', '2,4,1.2', '<p>test update</p>', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-02-24 03:24:53', '2022-03-03 20:31:25'),
(538, 'test', '28010473', 'producto_terminado', 'C128', NULL, 6, 0, 0, 0, '4', '5', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, '534', '2', '2', '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-03-03 15:48:05', '2022-03-03 15:48:05'),
(539, 'wdwd', '21917508', 'producto_terminado', 'C128', NULL, 6, 0, 0, 0, '10', '15', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, NULL, '534,535', '1,2', '2,4', '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-03-03 16:09:43', '2022-03-03 16:09:43'),
(540, 'test 5', '87876336', 'producto_terminado', 'C128', NULL, 6, 0, 0, 0, '0', '25.00', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, '534,535', '3,2', '2,4', '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-03-03 16:13:33', '2022-03-03 16:20:19'),
(541, 'Muzzarella', '94105973', 'insumo', 'C128', 13, 6, 41, 41, 41, '1.2', '3', 0, 0, 0, 42, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 0, NULL, NULL, NULL, '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-03-03 20:30:13', '2022-03-29 21:33:38'),
(542, 'combo', '15320461', 'combo', 'C128', NULL, 4, 0, 0, 0, '0', '105.00', 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'zummXD2dvAtI.png', NULL, NULL, 1, '486,495,490', '1,1,1', '20,10,75', '', 1, 'FALSE', 'TRUE', NULL, NULL, 0, '2022-03-07 04:23:35', '2022-03-07 04:23:35');

-- --------------------------------------------------------

--
-- Table structure for table `product_adjustments`
--

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
-- Dumping data for table `product_adjustments`
--

INSERT INTO `product_adjustments` (`id`, `adjustment_id`, `product_id`, `code`, `qty`, `action`, `created_at`, `updated_at`) VALUES
(4, 5, 477, 'Tabacco-80696015', 2, '-', '2022-04-26 21:03:20', '2022-04-26 22:00:23'),
(5, 5, 493, '41236320', 5, '+', '2022-04-26 21:03:20', '2022-04-26 22:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `product_associated`
--

CREATE TABLE `product_associated` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_courtesy_id` int(10) UNSIGNED NOT NULL,
  `product_associated_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `product_associated`
--

INSERT INTO `product_associated` (`id`, `product_courtesy_id`, `product_associated_id`) VALUES
(3, 493, 483),
(4, 493, 484);

-- --------------------------------------------------------

--
-- Table structure for table `product_lot`
--

CREATE TABLE `product_lot` (
  `id` int(10) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `idwarehouse` int(11) NOT NULL,
  `idproduct` int(10) NOT NULL,
  `qty` double NOT NULL,
  `stock` double NOT NULL,
  `expiration` date DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_spanish_ci DEFAULT NULL,
  `supplier` int(10) NOT NULL,
  `fabrication_date` date NOT NULL,
  `status` varchar(10) COLLATE utf8_spanish_ci NOT NULL COMMENT '0 = down, 1 = up, 2 = update, 3 = other',
  `created_at` datetime DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `low_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Dumping data for table `product_lot`
--

INSERT INTO `product_lot` (`id`, `purchase_id`, `idwarehouse`, `idproduct`, `qty`, `stock`, `expiration`, `name`, `supplier`, `fabrication_date`, `status`, `created_at`, `updated_at`, `low_date`) VALUES
(8, 55, 1, 494, 5, 5, '2022-02-28', 'L-10022022', 1, '2022-02-10', '0', '2022-02-10 21:08:22', '2022-02-11 01:08:22', '2022-03-29 17:33:52'),
(9, 55, 1, 494, 3, 3, '2022-03-12', 'L-10022022', 1, '2022-02-10', '0', '2022-02-10 21:08:35', '2022-02-11 01:08:35', '2022-03-29 17:33:52'),
(10, 57, 1, 497, 10, 10, '2022-02-28', 'L-10022022', 1, '2022-02-10', '0', '2022-02-10 21:22:45', '2022-02-11 01:22:45', '2022-03-29 17:33:52'),
(11, 58, 1, 490, 5, 3, '2022-03-06', 'L-10022022', 1, '2022-02-10', '0', '2022-02-10 21:26:10', '2022-03-18 19:26:52', '2022-03-29 17:33:52'),
(13, 59, 1, 494, 10, 7, '2022-02-23', 'L-12022022', 1, '2022-02-12', '0', '2022-02-12 01:01:07', '2022-03-17 03:50:50', '2022-03-29 17:33:52'),
(14, 60, 1, 491, 5, 5, '2022-02-28', 'L-14022022', 1, '2022-02-14', '0', '2022-02-14 14:42:03', '2022-02-14 18:42:03', '2022-03-29 17:33:52'),
(15, 61, 1, 504, 5, 2, '2022-02-28', 'L-15022022', 1, '2022-02-15', '0', '2022-02-15 00:31:24', '2022-02-15 04:35:17', '2022-03-29 17:33:52'),
(16, 61, 1, 490, 5, 0, '2022-02-28', 'L-15022022', 1, '2022-02-15', '0', '2022-02-15 00:31:24', '2022-02-15 04:43:14', '2022-03-29 17:33:52'),
(17, 62, 1, 495, 5, 0, '2022-02-28', 'L-15022022', 1, '2022-02-15', '0', '2022-02-15 00:47:23', '2022-02-15 05:00:34', '2022-03-29 17:33:52'),
(18, 62, 1, 495, 5, 3, '2022-03-12', 'L-15022022', 1, '2022-02-15', '0', '2022-02-15 00:47:23', '2022-02-15 05:01:07', '2022-03-29 17:33:52'),
(19, 62, 1, 505, 10, 10, '2022-03-12', 'L-15022022', 1, '2022-02-15', '0', '2022-02-15 00:47:24', '2022-02-15 04:47:24', '2022-03-29 17:33:52'),
(20, 63, 1, 534, 20, 20, '2022-02-26', 'L-21022022', 3, '2022-02-21', '0', '2022-02-21 18:44:35', '2022-03-18 19:16:13', '2022-03-29 17:33:52'),
(21, 63, 1, 535, 20, 20, '2022-02-23', 'L-21022022', 3, '2022-02-21', '0', '2022-02-21 18:44:35', '2022-03-18 19:16:13', '2022-03-29 17:33:52'),
(22, 66, 1, 494, 5, 3, '2022-03-31', 'L-16032022', 1, '2022-03-16', '0', '2022-03-16 22:24:46', '2022-03-24 05:31:05', NULL),
(23, 67, 1, 495, 5, 5, '2022-03-30', 'L-16032022', 1, '2022-03-16', '0', '2022-03-16 23:02:48', '2022-03-17 03:04:05', NULL),
(24, 68, 1, 535, 5, 5, '2022-04-14', 'L-16032022', 3, '2022-03-16', '1', '2022-03-16 23:06:28', '2022-03-17 03:07:43', NULL),
(26, 69, 1, 535, 20, 6, '2022-04-09', 'L-25032022', 3, '2022-03-25', '2', '2022-03-25 18:08:39', '2022-03-29 21:37:29', NULL),
(27, 69, 1, 534, 15, 4, '2022-04-09', 'L-25032022', 3, '2022-03-25', '2', '2022-03-25 18:08:39', '2022-03-29 21:37:28', NULL),
(28, 70, 1, 486, 20, 20, '2022-07-30', 'L-25032022', 1, '2022-03-25', '1', '2022-03-25 18:10:30', '2022-03-25 22:10:30', NULL),
(29, 70, 1, 490, 10, 10, '2022-08-31', 'L-25032022', 1, '2022-03-25', '1', '2022-03-25 18:10:30', '2022-03-25 22:10:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_purchases`
--

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
  `status` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_purchases`
--

INSERT INTO `product_purchases` (`id`, `purchase_id`, `product_id`, `variant_id`, `qty`, `recieved`, `purchase_unit_id`, `net_unit_cost`, `discount`, `tax_rate`, `tax`, `total`, `status`, `created_at`, `updated_at`) VALUES
(186, 36, 491, NULL, 1, 1, 31, 40, 0, 0, 0, 40, 1, '2021-12-13 16:19:44', '2021-12-13 16:19:44'),
(187, 36, 504, NULL, 9, 9, 10, 48, 0, 0, 0, 432, 1, '2021-12-13 16:19:44', '2021-12-13 16:19:44'),
(188, 36, 505, NULL, 1, 1, 10, 48, 0, 0, 0, 48, 1, '2021-12-13 16:19:45', '2021-12-13 16:19:45'),
(189, 36, 477, 7, 9, 9, 1, 42, 0, 0, 0, 378, 1, '2021-12-13 16:19:45', '2021-12-13 16:19:45'),
(190, 36, 477, 9, 4, 4, 1, 42, 0, 0, 0, 168, 1, '2021-12-13 16:19:45', '2021-12-13 16:19:45'),
(191, 36, 512, 9, 46, 46, 35, 30, 0, 0, 0, 1380, 1, '2021-12-13 16:19:45', '2021-12-13 16:19:45'),
(192, 37, 477, 9, 1, 1, 1, 42, 0, 0, 0, 42, 1, '2021-12-13 16:38:01', '2021-12-13 16:38:01'),
(193, 38, 521, NULL, 1, 1, 1, 23, 0, 0, 0, 23, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(194, 38, 522, NULL, 1, 1, 1, 23, 0, 0, 0, 23, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(195, 38, 523, NULL, 1, 1, 1, 23, 0, 0, 0, 23, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(196, 38, 524, NULL, 1, 1, 1, 23, 0, 0, 0, 23, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(197, 38, 525, NULL, 1, 1, 37, 87, 0, 0, 0, 87, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(198, 38, 526, NULL, 1, 1, 1, 23, 0, 0, 0, 23, 1, '2021-12-13 17:34:55', '2021-12-13 17:34:55'),
(199, 38, 527, NULL, 1, 1, 1, 23, 0, 0, 0, 23, 1, '2021-12-13 17:34:55', '2021-12-13 17:34:55'),
(200, 38, 528, NULL, 1, 1, 1, 23, 0, 0, 0, 23, 1, '2021-12-13 17:34:55', '2021-12-13 17:34:55'),
(201, 38, 529, NULL, 1, 1, 1, 18, 0, 0, 0, 18, 1, '2021-12-13 17:34:55', '2021-12-13 17:34:55'),
(202, 39, 520, NULL, 9, 9, 1, 70.4, 0, 0, 0, 633.6, 1, '2021-12-13 17:41:17', '2021-12-13 17:41:17'),
(203, 40, 515, NULL, 2, 2, 33, 2.5, 0, 0, 0, 5, 1, '2022-02-08 20:52:40', '2022-02-08 20:52:40'),
(204, 41, 503, NULL, 2, 2, 1, 6.6, 0, 0, 0, 13.2, 1, '2022-02-08 21:56:20', '2022-02-08 21:56:20'),
(205, 42, 503, NULL, 2, 2, 1, 6.6, 0, 0, 0, 13.2, 1, '2022-02-08 21:57:13', '2022-02-08 21:57:13'),
(206, 43, 503, NULL, 2, 2, 1, 6.6, 0, 0, 0, 13.2, 1, '2022-02-08 21:58:10', '2022-02-08 21:58:10'),
(207, 46, 486, NULL, 2, 2, 29, 9.17, 0, 0, 0, 18.34, 1, '2022-02-09 20:51:12', '2022-02-09 20:51:12'),
(208, 46, 487, NULL, 2, 2, 30, 5.62, 0, 0, 0, 11.24, 1, '2022-02-09 20:51:34', '2022-02-09 20:51:34'),
(210, 48, 497, NULL, 2, 2, 33, 5, 0, 0, 0, 10, 1, '2022-02-09 21:58:42', '2022-02-09 21:58:42'),
(211, 49, 533, NULL, 10, 10, 19, 10, 0, 0, 0, 100, 1, '2022-02-09 22:17:57', '2022-02-09 22:17:57'),
(217, 55, 486, NULL, 2, 2, 29, 9.17, 0, 0, 0, 18.34, 1, '2022-02-11 01:07:23', '2022-02-11 01:07:23'),
(218, 55, 494, NULL, 8, 8, 27, 3, 0, 0, 0, 24, 1, '2022-02-11 01:07:33', '2022-02-11 01:07:33'),
(219, 56, 521, NULL, 1, 1, 1, 23, 0, 0, 0, 23, 1, '2022-02-11 01:12:34', '2022-02-11 01:12:34'),
(220, 57, 497, NULL, 10, 10, 33, 5, 0, 0, 0, 50, 1, '2022-02-11 01:22:44', '2022-02-11 01:22:44'),
(246, 58, 490, NULL, 5, 5, 31, 33.6, 42, 0, 0, 168, 1, '2022-02-12 04:56:08', '2022-02-12 04:56:08'),
(253, 59, 494, NULL, 10, 10, 27, 3, 0, 0, 0, 30, 1, '2022-02-12 05:54:20', '2022-02-12 05:54:20'),
(254, 59, 477, 8, 5, 5, 1, 42, 0, 0, 0, 210, 1, '2022-02-12 05:54:20', '2022-02-12 05:54:20'),
(255, 60, 491, NULL, 5, 5, 31, 40, 0, 0, 0, 200, 1, '2022-02-14 18:42:03', '2022-02-14 18:42:03'),
(256, 61, 504, NULL, 5, 5, 10, 48, 0, 0, 0, 240, 1, '2022-02-15 04:31:24', '2022-02-15 04:31:24'),
(257, 61, 490, NULL, 5, 5, 31, 42, 0, 0, 0, 210, 1, '2022-02-15 04:31:24', '2022-02-15 04:31:24'),
(258, 62, 495, NULL, 10, 10, 33, 5, 0, 0, 0, 50, 1, '2022-02-15 04:47:23', '2022-02-15 04:47:23'),
(259, 62, 505, NULL, 10, 10, 10, 48, 0, 0, 0, 480, 1, '2022-02-15 04:47:24', '2022-02-15 04:47:24'),
(260, 63, 534, NULL, 20, 20, 1, 2, 0, 0, 0, 40, 1, '2022-02-21 22:44:35', '2022-02-21 22:44:35'),
(261, 63, 535, NULL, 20, 20, 17, 4, 0, 0, 0, 80, 1, '2022-02-21 22:44:35', '2022-02-21 22:44:35'),
(262, 64, 541, NULL, 50, 50, 41, 1.2, 0, 0, 0, 60, 1, '2022-03-03 20:32:18', '2022-03-03 20:32:18'),
(272, 69, 535, NULL, 20, 20, 17, 3.2, 0, 0, 0, 64, 1, '2022-03-25 22:08:39', '2022-03-25 22:08:39'),
(273, 69, 534, NULL, 15, 15, 1, 2.5, 0, 0, 0, 37.5, 1, '2022-03-25 22:08:39', '2022-03-25 22:08:39'),
(274, 70, 486, NULL, 20, 20, 29, 9.17, 0, 0, 0, 183.4, 1, '2022-03-25 22:10:30', '2022-03-25 22:10:30'),
(275, 70, 490, NULL, 10, 10, 31, 42, 0, 0, 0, 420, 1, '2022-03-25 22:10:30', '2022-03-25 22:10:30'),
(276, 70, 477, 8, 5, 5, 1, 42, 0, 0, 0, 210, 1, '2022-03-25 22:10:31', '2022-03-25 22:10:31'),
(279, 71, 512, NULL, 5, 5, 35, 1, 0, 0, 0, 5, 1, '2022-05-20 21:19:25', '2022-05-20 21:19:25'),
(280, 72, 514, NULL, 5, 5, 33, 5.8, 0, 0, 0, 29, 1, '2022-05-20 21:20:11', '2022-05-20 21:20:11');

-- --------------------------------------------------------

--
-- Table structure for table `product_quotation`
--

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_sales`
--

CREATE TABLE `product_sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
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

--
-- Dumping data for table `product_sales`
--

INSERT INTO `product_sales` (`id`, `sale_id`, `product_id`, `category_id`, `variant_id`, `employee_id`, `qty`, `sale_unit_id`, `net_unit_price`, `discount`, `tax_rate`, `tax`, `total`, `created_at`, `updated_at`) VALUES
(73, 31, 477, 5, 9, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-13 16:39:07', '2021-12-13 16:39:07'),
(74, 32, 477, 5, 9, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-13 16:46:24', '2021-12-13 16:46:24'),
(75, 33, 520, 4, NULL, NULL, 1, 1, 200, 0, 0, 0, 200, '2021-12-13 17:42:52', '2021-12-13 17:42:52'),
(76, 33, 483, 4, NULL, NULL, 1, 0, 120, 0, 0, 0, 120, '2021-12-13 17:42:52', '2021-12-13 17:42:52'),
(77, 34, 477, 5, 9, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-13 17:43:48', '2021-12-13 17:43:48'),
(78, 35, 483, 4, NULL, NULL, 1, 0, 120, 0, 0, 0, 120, '2021-12-13 22:10:43', '2021-12-13 22:10:43'),
(79, 36, 483, 4, NULL, NULL, 1, 0, 120, 0, 0, 0, 120, '2021-12-13 22:20:06', '2021-12-13 22:20:06'),
(80, 37, 485, 4, NULL, NULL, 1, 0, 170, 0, 0, 0, 170, '2021-12-13 22:36:03', '2021-12-13 22:36:03'),
(81, 38, 479, 4, NULL, NULL, 1, 0, 50, 0, 0, 0, 50, '2021-12-13 22:45:43', '2021-12-13 22:45:43'),
(82, 39, 477, 5, 7, NULL, 2, 1, 55, 0, 0, 0, 110, '2021-12-13 23:33:13', '2021-12-13 23:33:13'),
(83, 39, 485, 4, NULL, NULL, 1, 0, 170, 0, 0, 0, 170, '2021-12-13 23:33:13', '2021-12-13 23:33:13'),
(84, 40, 477, 5, 9, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:11:55', '2021-12-14 02:11:55'),
(85, 41, 477, 5, 7, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:12:32', '2021-12-14 02:12:32'),
(86, 42, 493, 5, NULL, NULL, 2, 1, 15, 0, 0, 0, 30, '2021-12-14 02:13:00', '2021-12-14 02:13:00'),
(87, 43, 477, 5, 7, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:13:42', '2021-12-14 02:13:42'),
(88, 44, 477, 5, 7, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:14:34', '2021-12-14 02:14:34'),
(89, 44, 477, 5, 9, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:14:34', '2021-12-14 02:14:34'),
(90, 45, 477, 5, 7, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:15:00', '2021-12-14 02:15:00'),
(91, 46, 477, 5, 7, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:15:39', '2021-12-14 02:15:39'),
(92, 47, 477, 5, 7, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:15:40', '2021-12-14 02:15:40'),
(93, 48, 477, 5, 7, NULL, 1, 1, 55, 0, 0, 0, 55, '2021-12-14 02:16:13', '2021-12-14 02:16:13'),
(94, 49, 497, 5, NULL, NULL, 1, 33, 10, 0, 0, 0, 10, '2021-12-21 14:46:57', '2021-12-21 14:46:57'),
(95, 50, 490, 5, NULL, NULL, 1, 31, 75, 0, 0, 0, 75, '2021-12-21 14:47:49', '2021-12-21 14:47:49'),
(96, 51, 493, 5, NULL, NULL, 1, 1, 15, 0, 0, 0, 15, '2021-12-22 21:57:41', '2021-12-22 21:57:41'),
(97, 52, 493, 5, NULL, NULL, 1, 1, 15, 0, 0, 0, 15, '2021-12-22 21:58:29', '2021-12-22 21:58:29'),
(98, 53, 488, 5, NULL, NULL, 1, 30, 10, 0, 0, 0, 10, '2021-12-22 22:00:37', '2021-12-22 22:00:37'),
(99, 53, 500, 5, NULL, NULL, 1, 1, 80, 0, 0, 0, 80, '2021-12-22 22:00:38', '2021-12-22 22:00:38'),
(100, 54, 501, 5, NULL, NULL, 2, 1, 20, 0, 0, 0, 40, '2021-12-22 22:04:37', '2021-12-22 22:04:37'),
(101, 55, 483, 4, NULL, NULL, 1, 0, 120, 0, 0, 0, 120, '2022-01-04 21:09:09', '2022-01-04 21:09:09'),
(102, 55, 493, 5, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, '2022-01-04 21:09:09', '2022-01-04 21:09:09'),
(103, 56, 483, 4, NULL, NULL, 1, 0, 120, 0, 0, 0, 120, '2022-01-05 14:47:11', '2022-01-05 14:47:11'),
(104, 56, 493, 5, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, '2022-01-05 14:47:11', '2022-01-05 14:47:11'),
(105, 57, 497, 5, NULL, NULL, 1, 33, 10, 0, 0, 0, 10, '2022-01-06 02:04:20', '2022-01-06 02:04:20'),
(106, 58, 491, 5, NULL, NULL, 1, 31, 75, 0, 0, 0, 75, '2022-01-06 02:19:24', '2022-01-06 02:19:24'),
(108, 67, 496, 5, NULL, NULL, 1, 33, 10, 0, 0, 0, 10, '2022-01-07 08:27:05', '2022-01-07 08:27:05'),
(109, 67, 483, 4, NULL, 3, 1, 0, 120, 0, 0, 0, 120, '2022-01-07 08:27:15', '2022-01-07 08:27:15'),
(110, 68, 497, 5, NULL, NULL, 1, 33, 10, 0, 0, 0, 10, '2022-01-07 08:28:19', '2022-01-07 08:28:19'),
(111, 68, 483, 4, NULL, NULL, 1, 0, 120, 0, 0, 0, 120, '2022-01-07 08:28:20', '2022-01-07 08:28:20'),
(112, 68, 493, 5, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, '2022-01-07 08:28:21', '2022-01-07 08:28:21'),
(113, 69, 490, 5, NULL, NULL, 1, 31, 75, 0, 0, 0, 75, '2022-01-07 08:29:21', '2022-01-07 08:29:21'),
(114, 69, 483, 4, NULL, 3, 1, 0, 120, 0, 0, 0, 120, '2022-01-07 08:29:29', '2022-01-07 08:29:29'),
(115, 69, 493, 5, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, '2022-01-07 08:29:30', '2022-01-07 08:29:30'),
(116, 70, 496, 5, NULL, NULL, 1, 33, 10, 0, 0, 0, 10, '2022-01-11 04:25:32', '2022-01-11 04:25:32'),
(118, 72, 493, 5, NULL, NULL, 1, 1, 15, 0, 0, 0, 15, '2022-01-11 04:30:17', '2022-01-11 04:30:17'),
(119, 73, 486, 5, NULL, NULL, 1, 29, 20, 0, 0, 0, 20, '2022-01-11 04:43:15', '2022-01-11 04:43:15'),
(120, 73, 484, 4, NULL, 3, 1, 0, 150, 0, 0, 0, 150, '2022-01-11 04:43:15', '2022-01-11 04:43:15'),
(121, 74, 499, 5, NULL, NULL, 2, 10, 50, 0, 0, 0, 100, '2022-01-11 04:46:10', '2022-01-11 04:46:10'),
(122, 75, 484, 4, NULL, 4, 1, 0, 150, 0, 0, 0, 150, '2022-01-12 05:41:19', '2022-01-12 05:41:19'),
(123, 76, 483, 4, NULL, 3, 1, 0, 120, 0, 0, 0, 120, '2022-01-12 05:42:30', '2022-01-12 05:42:30'),
(125, 78, 485, 4, NULL, 4, 1, 0, 170, 0, 0, 0, 170, '2022-01-12 05:48:11', '2022-01-12 05:48:11'),
(126, 78, 491, 5, NULL, NULL, 1, 31, 75, 0, 0, 0, 75, '2022-01-12 05:48:12', '2022-01-12 05:48:12'),
(127, 79, 487, 5, NULL, NULL, 1, 30, 10, 0, 0, 0, 10, '2022-01-12 05:56:31', '2022-01-12 05:56:31'),
(128, 80, 484, 4, NULL, 3, 1, 0, 150, 0, 0, 0, 150, '2022-01-15 19:22:20', '2022-01-18 04:58:08'),
(129, 80, 493, 5, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, '2022-01-15 19:22:20', '2022-01-17 01:28:57'),
(130, 81, 496, 5, NULL, NULL, 1, 33, 10, 0, 0, 0, 10, '2022-01-25 04:54:40', '2022-01-26 05:28:55'),
(131, 81, 483, 4, NULL, 4, 1, 0, 120, 0, 0, 0, 120, '2022-01-25 04:54:40', '2022-01-26 05:28:55'),
(132, 82, 496, 5, NULL, NULL, 1, 33, 10, 0, 0, 0, 10, '2022-01-25 05:21:07', '2022-01-26 05:08:39'),
(133, 82, 483, 4, NULL, 3, 1, 0, 120, 0, 0, 0, 120, '2022-01-25 05:21:07', '2022-01-26 05:08:40'),
(134, 82, 484, 4, NULL, 4, 1, 0, 150, 0, 0, 0, 150, '2022-01-26 05:04:20', '2022-01-26 05:08:41'),
(136, 83, 488, 5, NULL, NULL, 1, 30, 10, 0, 0, 0, 10, '2022-01-26 14:03:17', '2022-01-26 14:03:17'),
(137, 84, 486, 5, NULL, NULL, 2, 29, 20, 0, 0, 0, 40, '2022-02-04 20:07:46', '2022-02-04 20:07:46'),
(138, 84, 502, 5, NULL, NULL, 1, 1, 70, 0, 0, 0, 70, '2022-02-04 20:07:46', '2022-02-04 20:07:46'),
(139, 85, 490, 5, NULL, NULL, 1, 31, 75, 0, 0, 0, 75, '2022-02-05 16:37:20', '2022-02-05 16:37:20'),
(140, 86, 483, 4, NULL, 3, 1, 0, 120, 0, 0, 0, 120, '2022-02-05 16:37:42', '2022-02-05 16:37:42'),
(141, 87, 484, 4, NULL, 3, 1, 0, 150, 0, 0, 0, 150, '2022-02-05 16:38:14', '2022-02-05 16:38:14'),
(142, 87, 493, 5, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, '2022-02-05 16:38:14', '2022-02-05 16:38:14'),
(143, 88, 484, 4, NULL, 4, 1, 0, 150, 0, 0, 0, 150, '2022-02-05 18:18:32', '2022-02-05 18:18:32'),
(144, 89, 483, 4, NULL, 3, 1, 0, 120, 0, 0, 0, 120, '2022-02-08 20:09:41', '2022-02-08 20:09:41'),
(145, 89, 504, 5, NULL, NULL, 1, 10, 80, 0, 0, 0, 80, '2022-02-08 20:09:42', '2022-02-08 20:09:42'),
(150, 94, 504, 5, NULL, NULL, 3, 10, 80, 0, 0, 0, 240, '2022-02-15 04:35:33', '2022-02-15 04:35:33'),
(151, 94, 490, 5, NULL, NULL, 3, 31, 75, 0, 0, 0, 225, '2022-02-15 04:38:35', '2022-02-15 04:38:35'),
(152, 95, 490, 5, NULL, NULL, 4, 31, 75, 0, 0, 0, 300, '2022-02-15 04:43:14', '2022-02-15 04:43:14'),
(156, 101, 495, 5, NULL, NULL, 7, 33, 10, 0, 0, 0, 70, '2022-02-15 05:01:07', '2022-02-15 05:01:07'),
(157, 102, 506, 5, NULL, NULL, 2, 1, 12, 0, 0, 0, 24, '2022-02-15 21:42:56', '2022-02-15 21:42:56'),
(161, 106, 506, 5, NULL, NULL, 2, 1, 12, 0, 0, 0, 24, '2022-02-15 22:56:39', '2022-02-15 22:56:39'),
(164, 109, 506, 5, NULL, NULL, 2, 1, 12, 0, 0, 0, 24, '2022-02-15 23:13:52', '2022-02-15 23:13:52'),
(165, 110, 501, 5, NULL, NULL, 1, 1, 20, 0, 0, 0, 20, '2022-02-15 23:33:17', '2022-02-15 23:33:17'),
(166, 111, 501, 5, NULL, NULL, 1, 1, 20, 0, 0, 0, 20, '2022-02-15 23:36:48', '2022-02-15 23:36:48'),
(167, 112, 501, 5, NULL, NULL, 2, 1, 20, 0, 0, 0, 40, '2022-02-16 20:44:36', '2022-02-16 20:44:36'),
(168, 113, 501, 5, NULL, NULL, 2, 1, 20, 0, 0, 0, 40, '2022-02-16 20:51:08', '2022-02-16 20:51:08'),
(169, 114, 501, 5, NULL, NULL, 2, 1, 20, 0, 0, 0, 40, '2022-02-16 21:01:17', '2022-02-16 21:01:17'),
(170, 115, 501, 5, NULL, NULL, 2, 1, 20, 0, 0, 0, 40, '2022-02-16 21:02:27', '2022-02-16 21:02:27'),
(171, 116, 501, 5, NULL, NULL, 2, 1, 20, 0, 0, 0, 40, '2022-02-16 21:04:31', '2022-02-16 21:04:31'),
(172, 117, 501, 5, NULL, NULL, 2, 1, 20, 0, 0, 0, 40, '2022-02-16 21:09:56', '2022-02-16 21:09:56'),
(173, 118, 501, 5, NULL, NULL, 2, 1, 20, 0, 0, 0, 40, '2022-02-16 21:12:27', '2022-02-16 21:12:27'),
(174, 119, 486, 5, NULL, NULL, 1, 29, 20, 0, 0, 0, 20, '2022-02-16 21:13:08', '2022-02-16 21:13:08'),
(175, 120, 486, 5, NULL, NULL, 1, 29, 20, 0, 0, 0, 20, '2022-02-16 21:30:40', '2022-02-16 21:30:40'),
(176, 121, 486, 5, NULL, NULL, 1, 29, 20, 0, 0, 0, 20, '2022-02-16 22:21:05', '2022-02-16 22:21:05'),
(177, 122, 486, 5, NULL, NULL, 1, 29, 20, 0, 0, 0, 20, '2022-02-16 22:21:39', '2022-02-16 22:21:39'),
(178, 123, 486, 5, NULL, NULL, 1, 29, 20, 0, 0, 0, 20, '2022-02-16 22:22:51', '2022-02-16 22:22:51'),
(179, 124, 486, 5, NULL, NULL, 1, 29, 20, 0, 0, 0, 20, '2022-02-16 22:23:03', '2022-02-16 22:23:03'),
(180, 125, 486, 5, NULL, NULL, 1, 29, 20, 0, 0, 0, 20, '2022-02-16 22:24:10', '2022-02-16 22:24:10'),
(181, 126, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-02-24 03:20:31', '2022-02-24 03:20:31'),
(182, 127, 537, 6, NULL, NULL, 1, 0, 15, 0, 0, 0, 15, '2022-02-24 03:30:51', '2022-02-24 03:30:51'),
(187, 132, 537, 6, NULL, NULL, 1, 0, 15, 0, 0, 0, 15, '2022-02-24 21:30:17', '2022-02-24 21:30:17'),
(188, 133, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-02-24 21:59:53', '2022-02-24 21:59:53'),
(189, 134, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-02-24 22:04:25', '2022-02-24 22:04:25'),
(190, 135, 537, 6, NULL, NULL, 2, 0, 15, 0, 0, 0, 30, '2022-02-24 22:06:20', '2022-02-24 22:06:20'),
(192, 137, 536, 6, NULL, NULL, 2, 0, 10, 0, 0, 0, 20, '2022-02-24 22:07:50', '2022-02-24 22:07:50'),
(193, 138, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-02-24 22:08:37', '2022-02-24 22:08:37'),
(194, 139, 537, 6, NULL, NULL, 1, 0, 15, 0, 0, 0, 15, '2022-02-24 22:09:14', '2022-02-24 22:09:14'),
(195, 140, 537, 6, NULL, NULL, 1, 0, 15, 0, 0, 0, 15, '2022-02-24 22:09:27', '2022-02-24 22:09:27'),
(196, 140, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-02-24 22:09:27', '2022-02-24 22:09:27'),
(197, 141, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-02 21:14:52', '2022-03-02 21:14:52'),
(198, 142, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-03 20:04:07', '2022-03-03 20:04:07'),
(199, 143, 537, 6, NULL, NULL, 1, 0, 15, 0, 0, 0, 15, '2022-03-03 20:20:38', '2022-03-03 20:20:38'),
(200, 144, 537, 6, NULL, NULL, 1, 0, 15, 0, 0, 0, 15, '2022-03-03 20:32:45', '2022-03-03 20:32:45'),
(201, 145, 537, 6, NULL, NULL, 3, 0, 15, 0, 0, 0, 45, '2022-03-07 02:11:52', '2022-03-07 02:11:52'),
(202, 145, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-07 02:11:52', '2022-03-07 02:11:52'),
(203, 146, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-08 21:51:57', '2022-03-08 21:51:57'),
(204, 147, 493, 5, NULL, NULL, 2, 1, 15, 0, 0, 0, 30, '2022-03-08 21:52:18', '2022-03-08 21:52:18'),
(205, 148, 537, 6, NULL, NULL, 1, 0, 15, 0, 0, 0, 15, '2022-03-08 21:52:33', '2022-03-08 21:52:33'),
(206, 149, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-09 01:45:10', '2022-03-09 01:45:10'),
(207, 149, 490, 5, NULL, NULL, 1, 31, 75, 0, 0, 0, 75, '2022-03-09 01:45:10', '2022-03-09 01:45:10'),
(208, 150, 487, 5, NULL, NULL, 1, 30, 10, 0, 0, 0, 10, '2022-03-09 01:46:20', '2022-03-09 01:46:20'),
(209, 151, 498, 5, NULL, NULL, 1, 1, 20, 0, 0, 0, 20, '2022-03-09 02:01:29', '2022-03-09 02:01:29'),
(210, 152, 503, 5, NULL, NULL, 2, 1, 12, 0, 0, 0, 24, '2022-03-09 02:01:46', '2022-03-09 02:01:46'),
(211, 153, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-09 04:20:51', '2022-03-09 04:20:51'),
(212, 154, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-09 22:01:42', '2022-03-09 22:01:42'),
(213, 154, 494, 5, NULL, NULL, 1, 27, 10, 0, 0, 0, 10, '2022-03-09 22:01:42', '2022-03-09 22:01:42'),
(214, 155, 498, 5, NULL, NULL, 1, 1, 20, 0, 0, 0, 20, '2022-03-09 22:01:52', '2022-03-09 22:01:52'),
(215, 156, 488, 5, NULL, NULL, 1, 30, 10, 0, 0, 0, 10, '2022-03-09 22:14:54', '2022-03-09 22:14:54'),
(216, 157, 500, 5, NULL, NULL, 1, 1, 80, 0, 0, 0, 80, '2022-03-09 22:15:04', '2022-03-09 22:15:04'),
(217, 158, 485, 4, NULL, 3, 1, 0, 170, 0, 0, 0, 170, '2022-03-16 22:01:47', '2022-03-16 22:01:47'),
(218, 159, 536, 6, NULL, NULL, 1, 0, 40, 0, 0, 0, 40, '2022-03-16 22:02:19', '2022-03-16 22:02:19'),
(219, 160, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-16 22:02:31', '2022-03-16 22:02:31'),
(220, 161, 542, 4, NULL, NULL, 1, 0, 40, 0, 0, 0, 40, '2022-03-16 22:05:49', '2022-03-16 22:05:49'),
(221, 162, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-17 03:18:33', '2022-03-17 03:18:33'),
(222, 163, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-17 03:24:26', '2022-03-17 03:24:26'),
(223, 164, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-17 03:26:09', '2022-03-17 03:26:09'),
(224, 165, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-17 03:27:37', '2022-03-17 03:27:37'),
(225, 166, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-17 03:29:27', '2022-03-17 03:29:27'),
(228, 169, 494, 5, NULL, NULL, 1, 27, 10, 0, 0, 0, 10, '2022-03-17 03:50:50', '2022-03-17 03:50:50'),
(229, 170, 493, 5, NULL, NULL, 1, 1, 15, 0, 0, 0, 15, '2022-03-17 03:52:24', '2022-03-17 03:52:24'),
(230, 171, 488, 5, NULL, NULL, 1, 30, 10, 0, 0, 0, 10, '2022-03-17 03:53:40', '2022-03-17 03:53:40'),
(232, 173, 496, 5, NULL, NULL, 1, 33, 10, 0, 0, 0, 10, '2022-03-23 00:28:32', '2022-03-23 00:28:32'),
(233, 174, 494, 5, NULL, NULL, 2, 27, 10, 0, 0, 0, 20, '2022-03-24 05:31:05', '2022-03-24 05:31:05'),
(234, 175, 536, 6, NULL, NULL, 1, 0, 30, 0, 0, 0, 30, '2022-03-28 04:19:22', '2022-03-28 04:19:22'),
(235, 176, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-28 04:42:01', '2022-03-30 20:31:48'),
(236, 177, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-28 18:39:15', '2022-03-28 18:39:15'),
(237, 178, 537, 6, NULL, NULL, 1, 0, 15, 0, 0, 0, 15, '2022-03-28 18:40:56', '2022-03-28 18:40:56'),
(238, 179, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-28 18:47:35', '2022-03-28 18:47:35'),
(239, 180, 494, 5, NULL, NULL, 1, 27, 10, 0, 0, 0, 10, '2022-03-28 20:26:01', '2022-03-30 20:32:25'),
(240, 181, 494, 5, NULL, NULL, 1, 27, 10, 0, 0, 0, 10, '2022-03-28 20:27:45', '2022-03-28 20:27:45'),
(241, 181, 490, 5, NULL, NULL, 1, 31, 75, 0, 0, 0, 75, '2022-03-28 20:27:45', '2022-03-28 20:27:45'),
(242, 182, 494, 5, NULL, NULL, 1, 27, 10, 0, 0, 0, 10, '2022-03-28 20:28:27', '2022-03-28 20:28:27'),
(243, 182, 490, 5, NULL, NULL, 1, 31, 75, 0, 0, 0, 75, '2022-03-28 20:28:27', '2022-03-28 20:28:27'),
(244, 183, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-28 20:29:57', '2022-03-28 20:29:57'),
(245, 183, 494, 5, NULL, NULL, 1, 27, 10, 0, 0, 0, 10, '2022-03-28 20:29:57', '2022-03-28 20:29:57'),
(246, 184, 498, 5, NULL, NULL, 3, 1, 20, 0, 0, 0, 60, '2022-03-28 20:37:54', '2022-03-28 20:37:54'),
(247, 184, 494, 5, NULL, NULL, 2, 27, 10, 0, 0, 0, 20, '2022-03-28 20:37:54', '2022-03-28 20:37:54'),
(248, 185, 536, 6, NULL, NULL, 2, 0, 10, 0, 0, 0, 20, '2022-03-29 21:33:37', '2022-03-29 21:33:37'),
(249, 185, 537, 6, NULL, NULL, 2, 0, 15, 0, 0, 0, 30, '2022-03-29 21:33:38', '2022-03-29 21:33:38'),
(250, 185, 494, 5, NULL, NULL, 1, 27, 10, 0, 0, 0, 10, '2022-03-29 21:33:38', '2022-03-29 21:33:38'),
(251, 186, 536, 6, NULL, NULL, 1, 0, 10, 0, 0, 0, 10, '2022-03-29 21:37:29', '2022-03-29 21:37:29'),
(252, 187, 483, 4, NULL, 3, 1, 0, 120, 0, 0, 0, 120, '2022-04-25 22:17:37', '2022-04-25 22:17:37'),
(253, 187, 493, 5, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, '2022-04-25 22:17:38', '2022-04-25 22:17:38'),
(254, 188, 484, 4, NULL, 4, 1, 0, 150, 0, 0, 0, 150, '2022-04-25 22:18:17', '2022-04-26 00:43:25'),
(255, 188, 493, 5, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, '2022-04-26 00:43:26', '2022-04-26 00:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `product_transfer`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

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

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `variant_id`, `position`, `item_code`, `additional_price`, `qty`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 1, 'Producto2-qwer', 35, 0, '2021-08-02 14:25:45', '2021-08-02 14:25:45'),
(2, 477, 7, 1, 'Strawberry-80696015', 0, 1, '2021-12-01 17:29:24', '2022-04-26 20:55:34'),
(3, 477, 8, 2, 'Tabacco-80696015', 0, 8, '2021-12-01 17:29:25', '2022-04-26 22:00:23'),
(4, 477, 9, 3, 'Frosted-Berries-80696015', 0, 0, '2021-12-01 17:29:25', '2022-04-26 22:18:54'),
(5, 476, 10, 1, 'Apache-55470330', 75, 0, '2021-12-04 19:19:15', '2021-12-04 19:21:55'),
(6, 476, 11, 2, 'Harlequin-55470330', 75, 0, '2021-12-04 19:19:15', '2021-12-04 19:21:55'),
(7, 476, 12, 3, 'Pueblo-55470330', 75, 0, '2021-12-04 19:19:15', '2021-12-04 19:21:55');

-- --------------------------------------------------------

--
-- Table structure for table `product_warehouse`
--

CREATE TABLE `product_warehouse` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `warehouse_id` int(11) NOT NULL,
  `qty` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_warehouse`
--

INSERT INTO `product_warehouse` (`id`, `product_id`, `variant_id`, `warehouse_id`, `qty`, `created_at`, `updated_at`) VALUES
(123, '477', 7, 1, 0, '2021-12-01 17:30:56', '2022-04-26 21:16:21'),
(124, '477', 9, 1, 0, '2021-12-06 18:55:39', '2022-04-26 22:18:54'),
(125, '494', NULL, 1, 14, '2021-12-06 18:56:06', '2022-04-26 21:16:21'),
(126, '495', NULL, 1, -5, '2021-12-06 18:56:32', '2022-03-17 03:04:05'),
(127, '496', NULL, 1, 1, '2021-12-06 18:57:04', '2022-03-23 00:28:32'),
(129, '493', NULL, 1, 9, '2021-12-07 00:14:17', '2022-04-26 22:00:23'),
(130, '489', NULL, 1, 0, '2021-12-07 16:15:48', '2021-12-12 15:37:23'),
(131, '490', NULL, 1, 11, '2021-12-07 16:18:10', '2022-03-25 22:10:30'),
(132, '491', NULL, 1, 5, '2021-12-07 16:19:57', '2022-02-14 22:09:29'),
(133, '487', NULL, 1, 14, '2021-12-07 16:33:19', '2022-02-09 20:51:34'),
(134, '488', NULL, 1, 18, '2021-12-07 16:35:00', '2022-03-18 19:14:45'),
(135, '486', NULL, 1, 22, '2021-12-07 16:45:25', '2022-03-25 22:10:29'),
(136, '498', NULL, 1, 13, '2021-12-07 17:33:07', '2021-12-12 20:42:19'),
(137, '499', NULL, 1, 4, '2021-12-07 17:39:09', '2022-01-11 04:46:10'),
(138, '500', NULL, 1, 1, '2021-12-07 18:17:48', '2021-12-22 22:00:37'),
(139, '501', NULL, 1, 8, '2021-12-07 18:37:07', '2022-02-16 21:12:27'),
(140, '502', NULL, 1, 0, '2021-12-07 18:43:22', '2022-02-04 20:07:46'),
(141, '503', NULL, 1, 10, '2021-12-07 19:03:23', '2022-02-08 21:58:10'),
(142, '519', NULL, 1, 2, '2021-12-12 20:08:35', '2021-12-12 20:08:35'),
(143, '518', NULL, 1, 1, '2021-12-12 20:08:35', '2021-12-12 20:08:35'),
(144, '506', NULL, 1, 10, '2021-12-12 20:08:35', '2022-02-16 19:22:21'),
(145, '507', NULL, 1, 1, '2021-12-12 20:08:36', '2021-12-12 20:08:36'),
(146, '508', NULL, 1, 2, '2021-12-12 20:08:36', '2021-12-12 20:08:36'),
(147, '509', NULL, 1, 2, '2021-12-12 20:08:36', '2021-12-12 20:08:36'),
(148, '510', NULL, 1, 2, '2021-12-12 20:08:36', '2021-12-12 20:08:36'),
(149, '511', NULL, 1, 6, '2021-12-12 20:33:03', '2021-12-12 20:42:19'),
(150, '512', NULL, 1, 97, '2021-12-12 20:33:03', '2022-05-20 21:19:25'),
(151, '513', NULL, 1, 8, '2021-12-12 20:42:21', '2021-12-12 20:42:21'),
(152, '514', NULL, 1, 17, '2021-12-12 20:42:21', '2022-05-20 21:20:11'),
(153, '515', NULL, 1, 8, '2021-12-12 20:42:21', '2022-04-26 21:16:03'),
(154, '516', NULL, 1, 6, '2021-12-12 20:42:21', '2021-12-12 20:42:21'),
(155, '504', NULL, 1, 10, '2021-12-13 16:19:44', '2022-02-15 04:33:20'),
(156, '505', NULL, 1, 11, '2021-12-13 16:19:44', '2022-02-15 04:47:24'),
(157, '521', NULL, 1, 2, '2021-12-13 17:34:54', '2022-02-11 01:12:34'),
(158, '522', NULL, 1, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(159, '523', NULL, 1, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(160, '524', NULL, 1, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(161, '525', NULL, 1, 1, '2021-12-13 17:34:54', '2021-12-13 17:34:54'),
(162, '526', NULL, 1, 1, '2021-12-13 17:34:55', '2021-12-13 17:34:55'),
(163, '527', NULL, 1, 1, '2021-12-13 17:34:55', '2021-12-13 17:34:55'),
(164, '528', NULL, 1, 1, '2021-12-13 17:34:55', '2021-12-13 17:34:55'),
(165, '529', NULL, 1, 1, '2021-12-13 17:34:55', '2021-12-13 17:34:55'),
(166, '520', NULL, 1, 8, '2021-12-13 17:41:17', '2021-12-13 17:42:52'),
(167, '497', NULL, 1, 12, '2022-02-09 21:58:42', '2022-02-11 01:22:44'),
(168, '533', NULL, 1, 10, '2022-02-09 22:17:57', '2022-02-09 22:17:57'),
(169, '477', 8, 1, 10, '2022-02-12 05:01:07', '2022-04-26 22:00:23'),
(170, '534', NULL, 1, 4, '2022-02-21 22:44:35', '2022-04-26 22:18:53'),
(171, '535', NULL, 1, 0, '2022-02-21 22:44:35', '2022-04-26 22:18:53'),
(172, '541', NULL, 1, 42, '2022-03-03 20:32:18', '2022-03-29 21:33:38');

-- --------------------------------------------------------

--
-- Table structure for table `puntos_venta`
--

CREATE TABLE `puntos_venta` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_punto_venta` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_punto_venta` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_punto_venta` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo_cuis` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_vigencia_cuis` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

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
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `reference_no`, `user_id`, `warehouse_id`, `supplier_id`, `item`, `total_qty`, `total_discount`, `total_tax`, `total_cost`, `order_tax_rate`, `order_tax`, `order_discount`, `shipping_cost`, `grand_total`, `paid_amount`, `status`, `payment_status`, `document`, `note`, `created_at`, `updated_at`) VALUES
(17, 'pr-20211206-061416', 1, 1, 1, 11, 41, 0, 0, 787.6, 0, 0, 0, 0, 787.6, 0, 1, 1, NULL, NULL, '2021-12-07 00:14:16', '2021-12-12 20:08:36'),
(35, 'pr-20211212-023302', 1, 1, 1, 16, 198, 0, 0, 1816.89, 0, 0, 0, 0, 1816.89, 0, 1, 1, NULL, NULL, '2021-12-12 20:33:02', '2021-12-12 20:42:21'),
(36, 'pr-20211213-101944', 1, 1, 1, 6, 70, 0, 0, 2446, 0, 0, NULL, NULL, 2446, 0, 1, 1, NULL, NULL, '2021-12-13 16:19:44', '2021-12-13 16:19:44'),
(37, 'pr-20211213-103801', 1, 1, 1, 1, 1, 0, 0, 42, 0, 0, NULL, NULL, 42, 42, 1, 2, NULL, NULL, '2021-12-13 16:38:01', '2022-01-25 04:49:04'),
(38, 'pr-20211213-113454', 15, 1, 1, 9, 9, 0, 0, 266, 0, 0, NULL, NULL, 266, 266, 1, 2, NULL, NULL, '2021-12-13 17:34:54', '2022-01-25 04:48:28'),
(39, 'pr-20211213-114116', 15, 1, 1, 1, 9, 0, 0, 633.6, 0, 0, NULL, NULL, 633.6, 633.6, 1, 2, NULL, NULL, '2021-12-13 17:41:16', '2022-01-25 04:45:12'),
(40, 'pr-20220208-045157', 1, 1, 1, 1, 2, 0, 0, 5, 0, 0, NULL, NULL, 5, 5, 1, 2, NULL, 'test', '2022-02-08 20:51:57', '2022-02-08 20:53:56'),
(41, 'pr-20220208-055617', 1, 1, 1, 1, 2, 0, 0, 13.2, 0, 0, NULL, NULL, 13.2, 13.2, 1, 2, NULL, 'test lote not expiration date', '2022-02-08 21:56:17', '2022-02-08 21:58:23'),
(42, 'pr-20220208-055713', 1, 1, 1, 1, 2, 0, 0, 13.2, 0, 0, NULL, NULL, 13.2, 13.2, 1, 2, NULL, 'test lote not expiration date', '2022-02-08 21:57:13', '2022-02-08 21:58:45'),
(43, 'pr-20220208-055809', 1, 1, 1, 1, 2, 0, 0, 13.2, 0, 0, NULL, NULL, 13.2, 13.2, 1, 2, NULL, 'test lote not expiration date', '2022-02-08 21:58:09', '2022-02-08 21:58:57'),
(46, 'pr-20220209-045049', 1, 1, 1, 2, 4, 0, 0, 29.58, 0, 0, NULL, NULL, 29.58, 29.58, 1, 2, NULL, 'test with lote', '2022-02-09 20:50:49', '2022-02-11 01:10:44'),
(48, 'pr-20220209-055842', 1, 1, 1, 1, 2, 0, 0, 10, 0, 0, NULL, NULL, 10, 10, 1, 2, NULL, 'test lote', '2022-02-09 21:58:42', '2022-02-12 04:19:59'),
(49, 'pr-20220209-061757', 1, 1, 1, 1, 10, 0, 0, 100, 0, 0, NULL, NULL, 100, 100, 1, 2, NULL, 'test con tabletas cada blister tiene 10', '2022-02-09 22:17:57', '2022-02-12 04:20:11'),
(55, 'pr-20220210-090723', 1, 1, 1, 2, 10, 0, 0, 42.34, 0, 0, NULL, NULL, 42.34, 42.34, 1, 2, NULL, 'test', '2022-02-11 01:07:23', '2022-02-12 05:01:47'),
(56, 'pr-20220210-091234', 1, 1, 1, 1, 1, 0, 0, 23, 0, 0, NULL, NULL, 23, 23, 1, 2, NULL, 'test delete local lote', '2022-02-11 01:12:34', '2022-02-14 18:42:18'),
(57, 'pr-20220210-092244', 1, 1, 1, 1, 10, 0, 0, 50, 0, 0, NULL, NULL, 50, 50, 1, 2, NULL, 'test with lote', '2022-02-11 01:22:44', '2022-02-14 18:42:41'),
(58, 'pr-20220210-092609', 1, 1, 1, 1, 5, 42, 0, 168, 0, 0, 0, 0, 168, 168, 1, 2, NULL, 'test update add lote second item', '2022-02-11 01:26:09', '2022-02-15 05:08:58'),
(59, 'pr-20220212-010105', 1, 1, 1, 2, 15, 0, 0, 240, 0, 0, 0, 0, 240, 240, 1, 2, NULL, 'test item with lote', '2022-02-12 05:01:05', '2022-02-15 05:09:42'),
(60, 'pr-20220214-024202', 1, 1, 1, 1, 5, 0, 0, 200, 0, 0, NULL, NULL, 200, 200, 1, 2, NULL, NULL, '2022-02-14 18:42:02', '2022-03-10 02:49:50'),
(61, 'pr-20220215-123123', 1, 1, 1, 2, 10, 0, 0, 450, 0, 0, NULL, NULL, 450, 450, 1, 2, NULL, NULL, '2022-02-15 04:31:23', '2022-03-10 02:50:07'),
(62, 'pr-20220215-124723', 1, 1, 1, 2, 20, 0, 0, 530, 0, 0, NULL, NULL, 530, 530, 1, 2, NULL, NULL, '2022-02-15 04:47:23', '2022-03-10 02:50:23'),
(63, 'pr-20220221-064435', 1, 1, 3, 2, 40, 0, 0, 120, 0, 0, NULL, NULL, 120, 120, 1, 2, NULL, 'test stock insumos', '2022-02-21 22:44:35', '2022-03-10 02:49:35'),
(64, 'pr-20220303-043218', 1, 1, 3, 1, 50, 0, 0, 60, 0, 0, NULL, NULL, 60, 60, 1, 2, NULL, NULL, '2022-03-03 20:32:18', '2022-03-10 02:45:03'),
(69, 'pr-20220325-060838', 1, 1, 3, 2, 35, 0, 0, 101.5, 0, 0, NULL, NULL, 101.5, 101.5, 1, 2, NULL, NULL, '2022-03-25 22:08:38', '2022-03-30 20:34:02'),
(70, 'pr-20220325-061029', 1, 1, 1, 3, 35, 0, 0, 813.4, 0, 0, NULL, NULL, 813.4, 813.4, 1, 2, NULL, NULL, '2022-03-25 22:10:29', '2022-03-30 20:34:17'),
(71, 'pr-20220519-040826', 1, 1, 1, 1, 5, 0, 0, 5, 0, 0, 0, 0, 5, 0, 1, 1, NULL, 'test', '2022-05-18 08:08:26', '2022-05-20 21:19:26'),
(72, 'pr-20220519-040939', 1, 1, 1, 1, 5, 0, 0, 29, 0, 0, 0, 0, 29, 0, 1, 1, NULL, 'test with date default', '2022-05-20 09:20:11', '2022-05-20 21:20:11');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_product_return`
--

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

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

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
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(10) UNSIGNED NOT NULL,
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
  `return_note` text COLLATE utf8mb4_unicode_ci,
  `staff_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_purchases`
--

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
  `return_note` text COLLATE utf8mb4_unicode_ci,
  `staff_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `guard_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `guard_name`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'El administrador puede acceder a todos los datos ...', 'web', 1, '2018-06-01 23:46:44', '2018-06-02 23:13:05'),
(2, 'Tienda', 'Propietario de la tienda ...', 'web', 1, '2018-10-22 02:38:13', '2018-10-22 02:38:13'),
(4, 'Usuario', 'El personal tiene acceso específico ...', 'web', 1, '2018-06-02 00:05:27', '2018-06-02 00:05:27');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(13, 2),
(14, 2),
(15, 2),
(16, 2),
(17, 2),
(18, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(45, 2),
(46, 2),
(47, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(63, 2),
(64, 2),
(65, 2),
(66, 2),
(69, 2),
(77, 2),
(78, 2),
(79, 2),
(84, 2),
(85, 2),
(86, 2),
(87, 2),
(88, 2),
(92, 2),
(104, 2),
(6, 4),
(7, 4),
(8, 4),
(9, 4),
(12, 4),
(13, 4),
(20, 4),
(21, 4),
(24, 4),
(25, 4),
(28, 4),
(29, 4),
(63, 4),
(64, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `order_discount` double DEFAULT '0',
  `coupon_id` int(11) DEFAULT NULL,
  `coupon_discount` double DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `sale_status` int(11) NOT NULL,
  `payment_status` int(11) NOT NULL,
  `document` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_amount` double DEFAULT NULL,
  `sale_note` text COLLATE utf8mb4_unicode_ci,
  `staff_note` text COLLATE utf8mb4_unicode_ci,
  `date_sell` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `reference_no`, `user_id`, `customer_id`, `warehouse_id`, `biller_id`, `item`, `total_qty`, `total_discount`, `total_tax`, `total_price`, `grand_total`, `order_tax_rate`, `order_tax`, `order_discount`, `coupon_id`, `coupon_discount`, `shipping_cost`, `sale_status`, `payment_status`, `document`, `paid_amount`, `sale_note`, `staff_note`, `date_sell`, `created_at`, `updated_at`) VALUES
(31, 'NRV-00000001', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 10:39:07', '2021-12-13 16:39:07', '2021-12-13 16:39:07'),
(32, 'NRV-00000002', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 10:46:24', '2021-12-13 16:46:24', '2021-12-13 16:46:24'),
(33, 'NRV-00000003', 15, 18, 1, 1, 2, 2, 0, 0, 320, 320, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 320, NULL, NULL, '2021-12-13 11:42:52', '2021-12-13 17:42:52', '2021-12-13 17:42:52'),
(34, 'NRV-00000004', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 11:43:47', '2021-12-13 17:43:47', '2021-12-13 17:43:47'),
(35, 'NRV-00000005', 15, 18, 1, 1, 1, 1, 0, 0, 120, 120, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 120, NULL, NULL, '2021-12-13 16:10:43', '2021-12-13 22:10:43', '2021-12-13 22:10:43'),
(36, 'NRV-00000006', 15, 18, 1, 1, 1, 1, 0, 0, 120, 120, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 120, NULL, NULL, '2021-12-13 16:20:06', '2021-12-13 22:20:06', '2021-12-13 22:20:06'),
(37, 'NRV-00000007', 15, 18, 1, 1, 1, 1, 0, 0, 170, 170, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 170, NULL, NULL, '2021-12-13 16:36:03', '2021-12-13 22:36:03', '2021-12-13 22:36:03'),
(38, 'NRV-00000008', 15, 18, 1, 1, 1, 1, 0, 0, 50, 50, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 50, NULL, NULL, '2021-12-13 16:45:43', '2021-12-13 22:45:43', '2021-12-13 22:45:43'),
(39, 'NRV-00000009', 15, 18, 1, 1, 2, 3, 0, 0, 280, 280, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 280, NULL, NULL, '2021-12-13 17:33:13', '2021-12-13 23:33:13', '2021-12-13 23:33:13'),
(40, 'NRV-00000010', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 20:11:54', '2021-12-14 02:11:54', '2021-12-14 02:11:54'),
(41, 'NRV-00000011', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 20:12:32', '2021-12-14 02:12:32', '2021-12-14 02:12:32'),
(42, 'NRV-00000012', 15, 18, 1, 1, 1, 2, 0, 0, 30, 30, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 30, NULL, NULL, '2021-12-13 20:13:00', '2021-12-14 02:13:00', '2021-12-14 02:13:00'),
(43, 'NRV-00000013', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 20:13:42', '2021-12-14 02:13:42', '2021-12-14 02:13:42'),
(44, 'NRV-00000014', 15, 18, 1, 1, 2, 2, 0, 0, 110, 110, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 110, NULL, NULL, '2021-12-13 20:14:33', '2021-12-14 02:14:33', '2021-12-14 02:14:33'),
(45, 'NRV-00000015', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 20:14:59', '2021-12-14 02:14:59', '2021-12-14 02:14:59'),
(46, 'NRV-00000016', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 20:15:39', '2021-12-14 02:15:39', '2021-12-14 02:15:39'),
(47, 'NRV-00000017', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 20:15:40', '2021-12-14 02:15:40', '2021-12-14 02:15:40'),
(48, 'NRV-00000018', 15, 18, 1, 1, 1, 1, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2021-12-13 20:16:12', '2021-12-14 02:16:12', '2021-12-14 02:16:12'),
(49, 'NRV-00000019', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2021-12-20 10:46:56', '2021-12-21 14:46:56', '2021-12-21 14:46:56'),
(50, 'NRV-00000020', 1, 18, 1, 1, 1, 1, 0, 0, 75, 75, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 75, NULL, NULL, '2021-12-20 10:47:48', '2021-12-21 14:47:48', '2021-12-21 14:47:48'),
(51, 'NRV-00000021', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2021-12-21 17:57:22', '2021-12-22 21:57:22', '2021-12-22 21:57:22'),
(52, 'NRV-00000022', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2021-12-21 17:58:20', '2021-12-22 21:58:21', '2021-12-22 21:58:21'),
(53, 'NRV-00000023', 1, 18, 1, 1, 2, 2, 0, 0, 90, 90, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 90, NULL, NULL, '2021-12-21 18:00:37', '2021-12-22 22:00:37', '2021-12-22 22:00:37'),
(54, 'NRV-00000024', 1, 18, 1, 1, 1, 2, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2021-12-21 18:04:37', '2021-12-22 22:04:37', '2021-12-22 22:04:37'),
(55, 'NRV-00000025', 1, 18, 1, 1, 2, 2, 0, 0, 120, 120, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 120, NULL, NULL, '2022-01-04 17:09:09', '2022-01-04 21:09:09', '2022-01-04 21:09:09'),
(56, 'NRV-00000026', 1, 18, 1, 1, 2, 2, 0, 0, 120, 120, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 120, NULL, NULL, '2022-01-04 10:47:11', '2022-01-05 14:47:11', '2022-01-05 14:47:11'),
(57, 'NRV-00000027', 18, 18, 1, 7, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-01-05 22:04:20', '2022-01-06 02:04:20', '2022-01-06 02:04:20'),
(58, 'NRV-00000028', 1, 18, 1, 1, 1, 1, 0, 0, 75, 75, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 75, NULL, NULL, '2022-01-05 22:19:23', '2022-01-06 02:19:23', '2022-01-06 02:19:23'),
(67, 'NRV-00000037', 1, 18, 1, 1, 2, 2, 0, 0, 130, 130, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 130, NULL, NULL, '2022-01-07 04:25:17', '2022-01-07 08:25:17', '2022-01-07 08:25:17'),
(68, 'NRV-00000038', 1, 18, 1, 1, 3, 3, 0, 0, 130, 130, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 130, NULL, NULL, '2022-01-07 04:27:56', '2022-01-07 08:27:56', '2022-01-07 08:27:56'),
(69, 'NRV-00000039', 1, 18, 1, 1, 3, 3, 0, 0, 195, 195, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 195, NULL, NULL, '2022-01-07 04:29:00', '2022-01-07 08:29:00', '2022-01-07 08:29:00'),
(70, 'NRV-00000040', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-01-11 00:25:32', '2022-01-11 04:25:32', '2022-01-11 04:25:32'),
(72, 'NRV-00000042', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-01-11 00:30:16', '2022-01-11 04:30:16', '2022-01-11 04:30:16'),
(73, 'NRV-00000043', 1, 18, 1, 1, 2, 2, 0, 0, 170, 170, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 170, NULL, NULL, '2022-01-11 00:43:15', '2022-01-11 04:43:15', '2022-01-11 04:43:15'),
(74, 'NRV-00000044', 1, 18, 1, 1, 1, 2, 0, 0, 100, 100, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 100, NULL, NULL, '2022-01-11 00:46:10', '2022-01-11 04:46:10', '2022-01-11 04:46:10'),
(75, 'NRV-00000045', 1, 18, 1, 1, 1, 1, 0, 0, 150, 150, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 150, NULL, NULL, '2022-01-11 01:41:19', '2022-01-12 05:41:19', '2022-01-12 05:41:19'),
(76, 'NRV-00000046', 1, 18, 1, 1, 1, 1, 0, 0, 120, 120, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 120, NULL, NULL, '2022-01-11 01:42:30', '2022-01-12 05:42:30', '2022-01-12 05:42:30'),
(78, 'NRV-00000048', 18, 18, 1, 7, 2, 2, 0, 0, 245, 245, 0, 0, NULL, NULL, 11, NULL, 1, 4, NULL, 245, NULL, NULL, '2022-01-11 01:48:10', '2022-01-12 05:48:10', '2022-01-12 05:48:10'),
(79, 'NRV-00000049', 18, 18, 1, 7, 1, 1, 0, 0, 10, 10, 0, 0, NULL, 11, 11, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-01-11 01:56:31', '2022-01-12 05:56:31', '2022-01-12 05:56:31'),
(80, 'NRV-00000050', 1, 18, 1, 1, 2, 2, 0, 0, 150, 150, 0, 0, 0, NULL, NULL, 0, 1, 4, NULL, 150, NULL, NULL, '2022-01-11 15:22:19', '2022-01-15 19:22:19', '2022-01-17 01:28:57'),
(81, 'NRV-00000051', 1, 18, 1, 1, 2, 2, 0, 0, 130, 130, 0, 0, 0, NULL, NULL, 0, 1, 4, NULL, 130, NULL, NULL, '2022-01-25 00:54:40', '2022-01-25 04:54:40', '2022-01-26 05:28:55'),
(82, 'NRV-00000052', 1, 18, 1, 1, 3, 3, 0, 0, 280, 280, 0, 0, 0, NULL, NULL, 0, 1, 4, NULL, 280, NULL, NULL, '2022-01-25 01:21:07', '2022-01-25 05:21:07', '2022-01-26 05:44:59'),
(83, 'NRV-00000053', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-01-26 10:03:17', '2022-01-26 14:03:17', '2022-01-26 14:03:17'),
(84, 'NRV-00000054', 1, 18, 1, 1, 2, 3, 0, 0, 110, 110, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 110, NULL, NULL, '2022-02-04 16:07:46', '2022-02-04 20:07:46', '2022-02-04 20:08:34'),
(85, 'NRV-00000055', 1, 18, 1, 1, 1, 1, 0, 0, 75, 75, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 75, NULL, NULL, '2022-02-05 12:37:19', '2022-02-05 16:37:19', '2022-02-05 16:37:19'),
(86, 'NRV-00000056', 1, 18, 1, 1, 1, 1, 0, 0, 120, 120, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 120, NULL, NULL, '2022-02-05 12:37:42', '2022-02-05 16:37:42', '2022-02-05 16:37:42'),
(87, 'NRV-00000057', 1, 18, 1, 1, 2, 2, 0, 0, 150, 150, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 150, NULL, NULL, '2022-02-05 12:38:14', '2022-02-05 16:38:14', '2022-02-05 16:38:14'),
(88, 'NRV-00000058', 1, 18, 1, 1, 1, 1, 0, 0, 150, 150, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 150, 'test', NULL, '2022-02-05 14:18:32', '2022-02-05 18:18:32', '2022-02-05 18:18:32'),
(89, 'NRV-00000059', 1, 18, 1, 1, 2, 2, 0, 0, 200, 200, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 200, 'test validation item services deletes', NULL, '2022-02-08 16:09:41', '2022-02-08 20:09:41', '2022-02-08 20:09:41'),
(94, 'NRV-00000064', 1, 18, 1, 1, 2, 6, 0, 0, 465, 465, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 465, 'test with lote both products', NULL, '2022-02-15 00:33:11', '2022-02-15 04:33:11', '2022-02-15 04:33:11'),
(95, 'NRV-00000065', 1, 18, 1, 1, 1, 4, 0, 0, 300, 300, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 300, NULL, NULL, '2022-02-15 00:43:13', '2022-02-15 04:43:14', '2022-02-15 04:43:14'),
(101, 'NRV-00000066', 1, 18, 1, 1, 1, 7, 0, 0, 70, 70, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 70, NULL, NULL, '2022-02-15 00:59:48', '2022-02-15 04:59:48', '2022-02-15 04:59:48'),
(102, 'NRV-00000067', 1, 18, 1, 1, 1, 2, 0, 0, 24, 24, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 24, 'test para pre-cuenta', NULL, '2022-02-15 17:42:55', '2022-02-15 21:42:55', '2022-02-15 21:42:55'),
(106, 'NRV-00000071', 1, 18, 1, 1, 1, 2, 0, 0, 24, 24, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 24, 'test para pre-cuenta', NULL, '2022-02-15 18:56:39', '2022-02-15 22:56:39', '2022-02-15 22:56:39'),
(109, 'NRV-00000074', 1, 18, 1, 1, 1, 2, 0, 0, 24, 24, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 24, 'test para pre-cuenta', NULL, '2022-02-15 19:13:51', '2022-02-15 23:13:51', '2022-02-15 23:13:51'),
(110, 'NRV-00000075', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-15 19:33:16', '2022-02-15 23:33:16', '2022-02-15 23:33:16'),
(111, 'NRV-00000076', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-15 19:36:47', '2022-02-15 23:36:47', '2022-02-15 23:36:47'),
(112, 'NRV-00000077', 1, 18, 1, 1, 1, 2, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-02-16 16:43:52', '2022-02-16 20:43:52', '2022-02-16 20:43:52'),
(113, 'NRV-00000078', 1, 18, 1, 1, 1, 2, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-02-16 16:50:58', '2022-02-16 20:50:58', '2022-02-16 20:50:58'),
(114, 'NRV-00000079', 1, 18, 1, 1, 1, 2, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-02-16 17:01:17', '2022-02-16 21:01:17', '2022-02-16 21:01:17'),
(115, 'NRV-00000080', 1, 18, 1, 1, 1, 2, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-02-16 17:02:27', '2022-02-16 21:02:27', '2022-02-16 21:02:27'),
(116, 'NRV-00000081', 1, 18, 1, 1, 1, 2, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-02-16 17:04:30', '2022-02-16 21:04:30', '2022-02-16 21:04:30'),
(117, 'NRV-00000082', 1, 18, 1, 1, 1, 2, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-02-16 17:09:56', '2022-02-16 21:09:56', '2022-02-16 21:09:56'),
(118, 'NRV-00000083', 1, 18, 1, 1, 1, 2, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-02-16 17:12:27', '2022-02-16 21:12:27', '2022-02-16 21:12:27'),
(119, 'NRV-00000084', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-16 17:13:08', '2022-02-16 21:13:08', '2022-02-16 21:13:08'),
(120, 'NRV-00000085', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-16 17:30:40', '2022-02-16 21:30:40', '2022-02-16 21:30:40'),
(121, 'NRV-00000086', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-16 18:21:04', '2022-02-16 22:21:04', '2022-02-16 22:21:04'),
(122, 'NRV-00000087', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-16 18:21:39', '2022-02-16 22:21:39', '2022-02-16 22:21:39'),
(123, 'NRV-00000088', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-16 18:22:50', '2022-02-16 22:22:50', '2022-02-16 22:22:50'),
(124, 'NRV-00000089', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-16 18:23:03', '2022-02-16 22:23:03', '2022-02-16 22:23:03'),
(125, 'NRV-00000090', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-16 18:24:09', '2022-02-16 22:24:09', '2022-02-16 22:24:09'),
(126, 'NRV-00000091', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, 'test producto terminado', NULL, '2022-02-23 23:20:30', '2022-02-24 03:20:30', '2022-02-24 03:20:30'),
(127, 'NRV-00000092', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-02-23 23:30:48', '2022-02-24 03:30:48', '2022-02-24 03:30:48'),
(132, 'NRV-00000097', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-02-24 17:30:17', '2022-02-24 21:30:17', '2022-03-04 21:35:51'),
(133, 'NRV-00000098', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-02-24 17:59:51', '2022-02-24 21:59:51', '2022-03-04 21:16:18'),
(134, 'NRV-00000099', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-02-24 18:04:25', '2022-02-24 22:04:25', '2022-03-04 21:15:55'),
(135, 'NRV-00000100', 1, 18, 1, 1, 1, 2, 0, 0, 30, 30, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 30, NULL, NULL, '2022-02-24 18:06:20', '2022-02-24 22:06:20', '2022-03-04 21:23:47'),
(137, 'NRV-00000102', 1, 18, 1, 1, 1, 2, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-02-24 18:07:50', '2022-02-24 22:07:50', '2022-03-04 21:23:47'),
(138, 'NRV-00000103', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-02-24 18:08:37', '2022-02-24 22:08:37', '2022-02-24 22:08:37'),
(139, 'NRV-00000104', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-02-24 18:09:14', '2022-02-24 22:09:14', '2022-02-24 22:09:14'),
(140, 'NRV-00000105', 1, 18, 1, 1, 2, 2, 0, 0, 25, 25, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 25, NULL, NULL, '2022-02-24 18:09:26', '2022-02-24 22:09:26', '2022-03-04 21:25:16'),
(141, 'NRV-00000106', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-02 17:14:45', '2022-03-02 21:14:45', '2022-03-08 21:22:53'),
(142, 'NRV-00000107', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, 'test', NULL, '2022-03-03 16:02:17', '2022-03-03 20:02:17', '2022-03-03 20:02:17'),
(143, 'NRV-00000108', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-03-03 16:20:23', '2022-03-03 20:20:23', '2022-03-08 21:23:41'),
(144, 'NRV-00000109', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-03-03 16:32:45', '2022-03-03 20:32:45', '2022-03-03 20:32:45'),
(145, 'NRV-00000110', 1, 18, 1, 1, 2, 4, 0, 0, 55, 55, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 55, NULL, NULL, '2022-03-06 22:11:51', '2022-03-07 02:11:51', '2022-03-07 02:11:51'),
(146, 'NRV-00000111', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-08 17:51:56', '2022-03-08 21:51:56', '2022-03-09 01:41:26'),
(147, 'NRV-00000112', 1, 18, 1, 1, 1, 2, 0, 0, 30, 30, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 30, NULL, NULL, '2022-03-08 17:52:18', '2022-03-08 21:52:18', '2022-03-09 01:40:47'),
(148, 'NRV-00000113', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-03-08 17:52:32', '2022-03-08 21:52:32', '2022-03-08 21:53:28'),
(149, 'NRV-00000114', 1, 18, 1, 1, 2, 2, 0, 0, 85, 85, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 85, NULL, NULL, '2022-03-08 21:45:09', '2022-03-09 01:45:09', '2022-03-09 04:54:31'),
(150, 'NRV-00000115', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-08 21:46:20', '2022-03-09 01:46:20', '2022-03-09 01:46:35'),
(151, 'NRV-00000116', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-03-08 22:01:29', '2022-03-09 02:01:29', '2022-03-09 04:08:15'),
(152, 'NRV-00000117', 1, 18, 1, 1, 1, 2, 0, 0, 24, 24, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 24, NULL, NULL, '2022-03-08 22:01:46', '2022-03-09 02:01:46', '2022-03-09 02:01:46'),
(153, 'NRV-00000118', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-08 00:20:51', '2022-03-09 04:20:51', '2022-03-09 22:04:48'),
(154, 'NRV-00000119', 1, 18, 1, 1, 2, 2, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-03-09 18:01:42', '2022-03-09 22:01:42', '2022-03-09 22:04:49'),
(155, 'NRV-00000120', 1, 18, 1, 1, 1, 1, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-03-09 18:01:52', '2022-03-09 22:01:52', '2022-03-09 22:08:18'),
(156, 'NRV-00000121', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-09 18:14:54', '2022-03-09 22:14:54', '2022-03-10 19:37:31'),
(157, 'NRV-00000122', 1, 18, 1, 1, 1, 1, 0, 0, 80, 80, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 80, NULL, NULL, '2022-03-09 18:15:03', '2022-03-09 22:15:03', '2022-03-09 22:15:24'),
(158, 'NRV-00000123', 1, 18, 1, 1, 1, 1, 0, 0, 170, 170, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 170, NULL, NULL, '2022-03-16 18:01:44', '2022-03-16 22:01:44', '2022-03-16 22:01:44'),
(159, 'NRV-00000124', 1, 132, 1, 1, 1, 1, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-03-16 18:02:18', '2022-03-16 22:02:18', '2022-03-16 22:02:18'),
(160, 'NRV-00000125', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-16 18:02:31', '2022-03-16 22:02:31', '2022-03-22 20:38:26'),
(161, 'NRV-00000126', 1, 132, 1, 1, 1, 1, 0, 0, 40, 40, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 40, NULL, NULL, '2022-03-16 18:05:48', '2022-03-16 22:05:48', '2022-03-16 22:05:48'),
(162, 'NRV-00000127', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-16 23:18:32', '2022-03-17 03:18:32', '2022-03-17 03:18:32'),
(163, 'NRV-00000128', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-16 23:24:26', '2022-03-17 03:24:26', '2022-03-22 20:38:25'),
(164, 'NRV-00000129', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-16 23:26:08', '2022-03-17 03:26:08', '2022-03-24 22:51:06'),
(165, 'NRV-00000130', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-16 23:26:21', '2022-03-17 03:26:21', '2022-03-24 22:50:56'),
(166, 'NRV-00000131', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-16 23:27:50', '2022-03-17 03:27:50', '2022-03-17 03:27:50'),
(169, 'NRV-00000133', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-16 23:50:49', '2022-03-17 03:50:49', '2022-03-17 03:50:49'),
(170, 'NRV-00000134', 1, 18, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-03-16 23:52:23', '2022-03-17 03:52:23', '2022-03-17 03:52:23'),
(171, 'NRV-00000135', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-16 23:53:40', '2022-03-17 03:53:40', '2022-03-17 03:53:40'),
(173, 'NRV-00000136', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-22 20:28:31', '2022-03-23 00:28:31', '2022-03-23 00:28:31'),
(174, 'NRV-00000137', 1, 18, 1, 1, 1, 2, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-03-24 01:31:04', '2022-03-24 05:31:04', '2022-03-24 05:31:04'),
(175, 'NRV-00000138', 1, 18, 1, 1, 1, 1, 0, 0, 30, 30, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 30, NULL, NULL, '2022-03-28 00:19:21', '2022-03-28 04:19:21', '2022-03-30 19:32:00'),
(176, 'NRV-00000139', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, 0, NULL, NULL, 0, 1, 4, NULL, 10, NULL, NULL, '2022-03-28 00:42:00', '2022-03-28 04:42:00', '2022-03-30 20:31:48'),
(177, 'NRV-00000140', 1, 132, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-28 14:39:14', '2022-03-28 18:39:14', '2022-03-29 20:33:01'),
(178, 'NRV-00000141', 1, 132, 1, 1, 1, 1, 0, 0, 15, 15, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 15, NULL, NULL, '2022-03-28 14:40:55', '2022-03-28 18:40:55', '2022-03-29 20:35:30'),
(179, 'NRV-00000142', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 10, NULL, NULL, '2022-03-28 14:47:34', '2022-03-28 18:47:34', '2022-03-30 19:32:01'),
(180, 'NRV-00000143', 1, 18, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, 0, NULL, NULL, 0, 1, 4, NULL, 10, NULL, NULL, '2022-03-28 16:26:01', '2022-03-28 20:26:01', '2022-03-30 20:32:25'),
(181, 'NRV-00000144', 1, 18, 1, 1, 2, 2, 0, 0, 85, 85, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 85, NULL, NULL, '2022-03-28 16:27:45', '2022-03-28 20:27:45', '2022-03-30 20:30:27'),
(182, 'NRV-00000145', 1, 135, 1, 1, 2, 2, 0, 0, 85, 85, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 85, NULL, NULL, '2022-03-28 16:28:27', '2022-03-28 20:28:27', '2022-03-29 21:28:12'),
(183, 'NRV-00000146', 1, 135, 1, 1, 2, 2, 0, 0, 20, 20, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 20, NULL, NULL, '2022-03-28 16:29:55', '2022-03-28 20:29:55', '2022-03-29 21:39:09'),
(184, 'NRV-00000147', 1, 135, 1, 1, 2, 5, 0, 0, 80, 80, 0, 0, NULL, NULL, NULL, NULL, 4, 3, NULL, 50, NULL, NULL, '2022-03-28 16:37:53', '2022-03-28 20:37:53', '2022-03-29 21:40:20'),
(185, 'NRV-00000148', 1, 135, 1, 1, 3, 5, 0, 0, 60, 60, 0, 0, NULL, NULL, NULL, NULL, 4, 2, NULL, NULL, NULL, NULL, '2022-03-29 17:33:36', '2022-03-29 21:33:36', '2022-03-29 21:33:36'),
(186, 'NRV-00000149', 1, 135, 1, 1, 1, 1, 0, 0, 10, 10, 0, 0, NULL, NULL, NULL, NULL, 4, 2, NULL, NULL, NULL, NULL, '2022-03-29 17:37:28', '2022-03-29 21:37:28', '2022-03-29 21:37:28'),
(187, 'NRV-00000150', 1, 18, 1, 1, 2, 2, 0, 0, 120, 120, 0, 0, NULL, NULL, NULL, NULL, 1, 4, NULL, 120, NULL, NULL, '2022-04-25 18:17:37', '2022-04-25 22:17:37', '2022-04-25 22:17:37'),
(188, 'NRV-00000151', 1, 18, 1, 1, 2, 2, 0, 0, 150, 150, 0, 0, 0, NULL, NULL, 0, 1, 4, NULL, 150, 'update with cortesy', NULL, '2022-04-25 18:18:17', '2022-04-25 22:18:17', '2022-04-26 00:43:26');

-- --------------------------------------------------------

--
-- Table structure for table `siat_actividades_economicas`
--

CREATE TABLE `siat_actividades_economicas` (
  `id` int(10) UNSIGNED NOT NULL,
  `codigo_caeb` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_actividad` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `usuario_alta` int(10) UNSIGNED NOT NULL,
  `usuario_modificacion` int(10) UNSIGNED NOT NULL,
  `id_empresa` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siat_documento_sector`
--

CREATE TABLE `siat_documento_sector` (
  `id` int(10) UNSIGNED NOT NULL,
  `actividad_id` int(10) UNSIGNED NOT NULL,
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

CREATE TABLE `siat_leyendas_facturas` (
  `id` int(10) UNSIGNED NOT NULL,
  `actividad_id` int(10) UNSIGNED NOT NULL,
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

CREATE TABLE `siat_producto_servicios` (
  `id` int(10) UNSIGNED NOT NULL,
  `actividad_id` int(10) UNSIGNED NOT NULL,
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
  `note` text COLLATE utf8mb4_unicode_ci,
  `is_adjusted` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_counts`
--

INSERT INTO `stock_counts` (`id`, `reference_no`, `warehouse_id`, `category_id`, `brand_id`, `user_id`, `type`, `initial_file`, `final_file`, `note`, `is_adjusted`, `created_at`, `updated_at`) VALUES
(1, 'scr-20220426-081847', 1, NULL, NULL, 1, 'full', '20220426-081847.csv', '20220426-082331.csv', 'update', 0, '2022-04-27 00:18:47', '2022-04-27 00:23:56');

-- --------------------------------------------------------

--
-- Table structure for table `sucursal_siat`
--

CREATE TABLE `sucursal_siat` (
  `id` int(10) UNSIGNED NOT NULL,
  `sucursal` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion_sucursal` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domicilio_tributario` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ciudad_municipio` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_autorizacion_facturacion` int(4) UNSIGNED NOT NULL,
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

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `image`, `company_name`, `vat_number`, `email`, `phone_number`, `address`, `city`, `state`, `postal_code`, `country`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Proveedor', NULL, 'FV', NULL, 'prueba@gmail.com', '355555', 'CENTRO', 'SANTA CRUZ', NULL, NULL, NULL, 1, '2021-08-02 15:27:13', '2021-11-25 18:18:43'),
(2, 'MEDICAL PHARMA', NULL, 'MEDICAL PHARMA', NULL, 'mf@gmail.com', '336-7170', 'CALLE SEOANE Y VELASCO', 'SC', NULL, NULL, NULL, 0, '2021-09-02 00:45:35', '2021-11-25 18:18:25'),
(3, 'Foods & Products', NULL, 'Test foods', NULL, 'fernanda.rodriguezdasilva@gmail.com', '54848484848', 'test', 'santa cruz', 'SC', NULL, 'Bolivia', 1, '2022-02-21 21:58:26', '2022-02-21 21:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `taxes`
--

INSERT INTO `taxes` (`id`, `name`, `rate`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'IVA', 13, 1, '2018-05-12 09:58:30', '2021-04-19 18:44:25');

-- --------------------------------------------------------

--
-- Table structure for table `transfers`
--

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
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(10) UNSIGNED NOT NULL,
  `unit_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_unit` int(11) DEFAULT NULL,
  `operator` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operation_value` double DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `unit_code`, `unit_name`, `base_unit`, `operator`, `operation_value`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'PZA', 'Pieza', NULL, '*', 1, 1, '2018-05-12 02:27:46', '2021-08-02 14:29:02'),
(2, 'c/12', 'Docena de caja ', 1, '*', 12, 0, '2018-05-12 09:57:05', '2021-11-30 16:22:33'),
(3, 'carton', 'Caja Carton', 1, '*', 24, 0, '2018-05-12 09:57:45', '2021-11-30 16:22:33'),
(4, 'm', 'Metro', NULL, '*', 1, 0, '2018-05-12 09:58:07', '2021-11-30 16:22:13'),
(7, 'kg', 'kilogramo', NULL, '*', 1, 0, '2018-06-25 00:49:26', '2021-11-30 16:22:33'),
(9, 'gr', 'Gramo', 7, '/', 1000, 0, '2018-09-01 00:06:28', '2021-11-30 16:22:33'),
(10, 'CAJA', 'CAJA', NULL, '*', 1, 0, '2021-08-02 14:11:42', '2021-11-30 16:22:33'),
(11, 'FRASCO', 'FRASCO', NULL, '*', 1, 0, '2021-08-02 14:12:03', '2021-11-30 16:22:33'),
(12, 'TUBO', 'TUBO', NULL, '*', 1, 0, '2021-08-02 14:12:18', '2021-11-30 16:22:33'),
(13, 'JARABE', 'JARABE', NULL, '*', 1, 0, '2021-08-02 14:12:26', '2021-11-30 16:22:33'),
(14, 'AMPOLLA', 'AMPOLLA', NULL, '*', 1, 0, '2021-08-02 14:12:39', '2021-11-30 16:22:33'),
(15, 'ROLLO', 'ROLLO', NULL, '*', 1, 0, '2021-08-02 14:12:50', '2021-11-30 16:22:54'),
(16, 'CREMA', 'CREMA', NULL, '*', 1, 0, '2021-08-02 14:13:06', '2021-11-30 16:22:54'),
(17, 'UNI', 'UNIDAD', NULL, '*', 1, 0, '2021-08-02 14:28:51', '2021-11-30 16:22:54'),
(18, 'GOTERO', 'GOTERO', NULL, '*', 1, 0, '2021-08-07 03:04:27', '2021-11-30 16:22:54'),
(19, 'BLISTER', 'BLISTER', NULL, '*', 1, 0, '2021-08-07 03:07:26', '2021-11-30 16:22:54'),
(20, 'BOTE', 'BOTE', NULL, '*', 1, 0, '2021-08-07 15:17:01', '2021-11-30 16:22:54'),
(21, 'EMPAQUES', 'EMPAQUES', NULL, '*', 1, 0, '2021-08-07 15:19:10', '2021-11-30 16:22:54'),
(22, 'Bolsa grande 40 gr', 'Bolsa grande', NULL, '*', 1, 1, '2021-11-30 16:22:00', '2021-12-04 19:51:44'),
(23, 'Bolsa pequeña', 'Bolsa pequeña', 24, '*', 1, 1, '2021-11-30 16:23:27', '2021-11-30 16:33:57'),
(24, 'Unidad', 'Unidad', NULL, '*', 1, 0, '2021-11-30 16:25:49', '2021-11-30 16:39:06'),
(25, 'Caja', 'Caja', NULL, '*', 1, 1, '2021-11-30 16:26:03', '2021-11-30 16:26:03'),
(26, 'Vaso', 'Vaso', NULL, '*', 1, 1, '2021-11-30 16:27:18', '2021-11-30 16:27:18'),
(27, 'Taza', 'Taza', 24, '*', 1, 0, '2021-11-30 16:27:45', '2021-12-06 17:21:44'),
(28, 'Bolsa mediana', 'Bolsa mediana de 30 gr', NULL, '*', 1, 0, '2021-11-30 16:29:17', '2021-12-04 19:20:20'),
(29, 'Lata', 'Lata', NULL, '*', 1, 1, '2021-11-30 16:36:47', '2021-11-30 16:58:29'),
(30, 'Te', 'Latas de Te', NULL, '*', 1, 1, '2021-11-30 16:37:14', '2021-11-30 17:00:43'),
(31, 'Bolsa mediana 30 gr', 'Bolsa mediana', NULL, '*', 1, 1, '2021-12-04 19:52:11', '2021-12-04 19:52:11'),
(32, 'Taza', 'Taza', NULL, '*', 1, 1, '2021-12-06 17:22:00', '2021-12-06 17:22:00'),
(33, 'Botella', 'Botella', NULL, '*', 1, 1, '2021-12-06 17:50:04', '2021-12-06 17:50:04'),
(34, 'BOLSA', 'BOLSA', NULL, '*', 1, 1, '2021-12-12 17:08:58', '2021-12-12 17:08:58'),
(35, 'KIT', 'KIT', NULL, '*', 1, 1, '2021-12-12 19:12:32', '2021-12-12 19:12:32'),
(36, 'PRENDA', 'PRENDA', NULL, '*', 1, 1, '2021-12-12 19:52:12', '2021-12-12 19:52:12'),
(37, 'Pack', 'Pack', NULL, '*', 1, 1, '2021-12-13 17:22:30', '2021-12-13 17:22:30'),
(38, 'tab', 'Tableta', NULL, NULL, NULL, 1, '2022-02-09 22:07:11', '2022-02-09 22:19:59'),
(39, 'Bl', 'Blister', NULL, '*', 10, 1, '2022-02-09 22:08:00', '2022-02-09 22:11:43'),
(40, 'C/U', 'Unidad', NULL, '*', 1, 1, '2022-02-21 21:52:24', '2022-02-21 21:52:24'),
(41, 'Gr', 'Gramos', NULL, '*', 1, 1, '2022-03-03 20:26:19', '2022-03-03 20:26:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

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

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `remember_token`, `phone`, `company_name`, `role_id`, `biller_id`, `warehouse_id`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$DWAHTfjcvwCpOCXaJg11MOhsqns03uvlwiSUOQwkHL2YYrtrXPcL6', '7jz24lksl9fbhZIXXQOIVGRbR2iimvIG0rESNRnzLyDPvMXT13VECtW91Yn5', '12112', 'Gisul', 1, 1, NULL, 1, 0, '2018-06-02 03:24:15', '2021-04-19 17:24:39'),
(3, 'dhiman da', 'dhiman@gmail.com', '$2y$10$Fef6vu5E67nm11hX7V5a2u1ThNCQ6n9DRCvRF9TD7stk.Pmt2R6O.', '5ehQM6JIfiQfROgTbB5let0Z93vjLHS7rd9QD5RPNgOxli3xdo7fykU7vtTt', '212', 'lioncoders', 1, NULL, NULL, 1, 0, '2018-06-13 22:00:31', '2018-12-25 03:47:07'),
(6, 'test', 'test@gmail.com', '$2y$10$TDAeHcVqHyCmurki0wjLZeIl1SngKX3WLOhyTiCoZG3souQfqv.LS', 'KpW1gYYlOFacumklO2IcRfSsbC3KcWUZzOI37gqoqM388Xie6KdhaOHIFEYm', '1234', '212312', 4, NULL, NULL, 0, 1, '2018-06-23 03:05:33', '2018-06-23 03:13:45'),
(8, 'test', 'test@yahoo.com', '$2y$10$hlMigidZV0j2/IPkgE/xsOSb8WM2IRlsMv.1hg1NM7kfyd6bGX3hC', NULL, '31231', NULL, 4, NULL, NULL, 0, 1, '2018-06-24 22:35:49', '2018-07-02 01:07:39'),
(9, 'tienda', 'anda@gmail.com', '$2y$10$kxDbnynB6mB1e1w3pmtbSOlSxy/WwbLPY5TJpMi0Opao5ezfuQjQm', 'dNAe8iSjJbGZNeEypYH80C4kX2wKowej6QADpbzL6PBoKDFQMMzmwgFUOsYl', '3123', NULL, 4, 5, 1, 1, 0, '2018-07-02 01:08:08', '2021-11-25 18:34:22'),
(10, 'fvelez', 'fv@gmail.com', '$2y$10$ihiXLc8oUXrS/1J1CN.WYuuLBW/apjkVoD1tPdwFlwPM5KMiebiu6', NULL, '72100719', 'FV', 1, NULL, NULL, 0, 1, '2021-08-06 03:39:50', '2021-11-25 18:34:13'),
(11, 'alejandra', 'alejandra@gmail.com', '$2y$10$YLb3TVpeUixVP9XMm25mKevcTxC/JUJ6dyUG9HLOaOkfSjlJ3Y4e2', '36R6WK2SR2W3QPhHKClyq7a0NghhTMcRktkvjDNG6vXCuCMzX0Q3nKHFdrf9', '799939393', 'FV', 2, NULL, NULL, 0, 1, '2021-08-06 03:43:26', '2021-11-25 18:34:13'),
(12, 'lisbeth', 'lisbet@gmail.com', '$2y$10$IiT.H41j1BVxy2UHm39Dwe6FqikYRPDu8223prH68gJUJnFiggdbu', NULL, '76666666', 'FV', 1, NULL, NULL, 0, 1, '2021-08-06 03:44:07', '2021-11-25 18:34:13'),
(13, 'lvelez', 'lvelez@gmail.com', '$2y$10$WUYE6CW4uiMV0p/2yPbF0eT5HBMd.NtG/GWhkW3FzFFFE7QuPYZrO', NULL, '785558888', 'FV', 1, NULL, NULL, 0, 1, '2021-08-06 03:44:43', '2021-11-25 18:34:13'),
(14, 'rebeca', 'rebeca@gmail.com', '$2y$10$4kSCJgxOtFNkhdQ1s8VFqewdJd.ToS7hRNYE/DKPiMe4yNaR4PpW2', NULL, '688888888', 'La Creme', 2, 1, NULL, 1, 0, '2021-11-25 21:13:48', '2022-01-06 00:46:08'),
(15, 'Rebel', 'rebeca_m.sh@hotmail.com', '$2y$10$DE/nb4VrPy.Mdnxc6JLlkuOiZAs3wqPK.w.O3hZwhgOyqH6XKTPs.', NULL, '75637869', NULL, 1, NULL, NULL, 1, 0, '2021-12-01 16:38:02', '2021-12-01 16:38:02'),
(16, 'Chamo', 'elchamobarber@gmail.com', '$2y$10$N1fzEMm.X51z3ZlbLtt7CuzmCcWEE.oW0H7A8Gv3m5SABd2RD.vq.', NULL, '62010824', NULL, 1, NULL, NULL, 1, 0, '2021-12-01 16:50:43', '2021-12-01 16:50:43'),
(17, 'test1', 'claudiocalatayud88@gmail.com', '$2y$10$FvZww2/TrDJyW1mae1tffOPSxYJ1OPvU4/jBwrYRFkWhQiv3n1wnC', 'blZQBBqrtqCCnS7H6qzwJ5i7HMxK4jWdHJXGyiOjYO7MN9TUatTcxTX6A6cW', '+59178553485', 'SESS', 4, 6, 2, 1, 0, '2021-12-10 22:13:10', '2021-12-10 22:13:10'),
(18, 'vendedor', 'admin@demo.com', '$2y$10$gHnn5dZ6wxy7PSlqqZ0cu.3D4iYY32EZFGoKVki1O339ZGvvrrMYy', 'gZNWDA9YkZKwdNxjv2jvaitmU8xFOvZ6Vl7M9z5NvndtZSEGBDWwhRHBVJFt', '78553485', 'Gisul', 4, 7, NULL, 1, 0, '2022-01-06 00:45:20', '2022-01-06 00:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `variants`
--

CREATE TABLE `variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variants`
--

INSERT INTO `variants` (`id`, `name`, `created_at`, `updated_at`) VALUES
(2, 'Mediano', '2019-11-21 07:03:04', '2019-11-24 08:43:52'),
(3, 'Pequeño', '2019-11-21 07:03:04', '2019-11-24 08:43:52'),
(5, 'Largo', '2019-11-24 06:07:20', '2019-11-24 08:44:56'),
(6, 'Producto2', '2021-08-02 14:25:45', '2021-08-02 14:25:45'),
(7, 'Strawberry', '2021-12-01 17:29:24', '2021-12-01 17:29:24'),
(8, 'Tabacco', '2021-12-01 17:29:25', '2021-12-01 17:29:25'),
(9, 'Frosted Berries', '2021-12-01 17:29:25', '2021-12-01 17:29:25'),
(10, 'Apache', '2021-12-04 19:19:15', '2021-12-04 19:19:15'),
(11, 'Harlequin', '2021-12-04 19:19:15', '2021-12-04 19:19:15'),
(12, 'Pueblo', '2021-12-04 19:19:15', '2021-12-04 19:19:15');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `sucursal_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `phone`, `email`, `address`, `is_active`, `created_at`, `updated_at`, `sucursal_id`) VALUES
(1, 'Principal', '788848484', 'test@gmail.com', 'av test', 1, NULL, NULL, NULL);

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
-- Indexes for table `billers`
--
ALTER TABLE `billers`
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
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_groups`
--
ALTER TABLE `customer_groups`
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
-- Indexes for table `siat_actividades_economicas`
--
ALTER TABLE `siat_actividades_economicas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siat_documento_sector`
--
ALTER TABLE `siat_documento_sector`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actividad_id` (`actividad_id`);

--
-- Indexes for table `siat_leyendas_facturas`
--
ALTER TABLE `siat_leyendas_facturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actividad_id` (`actividad_id`);

--
-- Indexes for table `siat_parametricas_varios`
--
ALTER TABLE `siat_parametricas_varios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siat_producto_servicios`
--
ALTER TABLE `siat_producto_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actividad_id` (`actividad_id`);

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
-- Indexes for table `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `account_method_pay`
--
ALTER TABLE `account_method_pay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `adjustments`
--
ALTER TABLE `adjustments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `adjustment_accounts`
--
ALTER TABLE `adjustment_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `billers`
--
ALTER TABLE `billers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `cashier`
--
ALTER TABLE `cashier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `customer_groups`
--
ALTER TABLE `customer_groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `garantes`
--
ALTER TABLE `garantes`
  MODIFY `id_garante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_settings`
--
ALTER TABLE `general_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gift_cards`
--
ALTER TABLE `gift_cards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `gift_card_recharges`
--
ALTER TABLE `gift_card_recharges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hrm_settings`
--
ALTER TABLE `hrm_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lote_sales`
--
ALTER TABLE `lote_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `method_payments`
--
ALTER TABLE `method_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `money_transfers`
--
ALTER TABLE `money_transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=197;

--
-- AUTO_INCREMENT for table `payment_with_cheque`
--
ALTER TABLE `payment_with_cheque`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payment_with_credit_card`
--
ALTER TABLE `payment_with_credit_card`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `personas`
--
ALTER TABLE `personas`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `printers`
--
ALTER TABLE `printers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=543;

--
-- AUTO_INCREMENT for table `product_adjustments`
--
ALTER TABLE `product_adjustments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_associated`
--
ALTER TABLE `product_associated`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_lot`
--
ALTER TABLE `product_lot`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `product_purchases`
--
ALTER TABLE `product_purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=281;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=256;

--
-- AUTO_INCREMENT for table `product_transfer`
--
ALTER TABLE `product_transfer`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_warehouse`
--
ALTER TABLE `product_warehouse`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `puntos_venta`
--
ALTER TABLE `puntos_venta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `siat_actividades_economicas`
--
ALTER TABLE `siat_actividades_economicas`
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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sucursal_siat`
--
ALTER TABLE `sucursal_siat`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `variants`
--
ALTER TABLE `variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product_associated`
--
ALTER TABLE `product_associated`
  ADD CONSTRAINT `product_associated_ibfk_1` FOREIGN KEY (`product_courtesy_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_associated_ibfk_2` FOREIGN KEY (`product_associated_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `siat_documento_sector`
--
ALTER TABLE `siat_documento_sector`
  ADD CONSTRAINT `fk_documento_serctor_actividades_economicas` FOREIGN KEY (`actividad_id`) REFERENCES `siat_actividades_economicas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `siat_leyendas_facturas`
--
ALTER TABLE `siat_leyendas_facturas`
  ADD CONSTRAINT `fk_leyendas_facturas_actividades_economicas` FOREIGN KEY (`actividad_id`) REFERENCES `siat_actividades_economicas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `siat_producto_servicios`
--
ALTER TABLE `siat_producto_servicios`
  ADD CONSTRAINT `fk_producto_servicios_actividades_economicas` FOREIGN KEY (`actividad_id`) REFERENCES `siat_actividades_economicas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `fk_almacen_sucursales` FOREIGN KEY (`sucursal_id`) REFERENCES `sucursal_siat` (`id`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `updateVencimiento` ON SCHEDULE EVERY 1 DAY STARTS '2022-03-17 17:33:51' ON COMPLETION NOT PRESERVE ENABLE COMMENT 'checking expire lote for down' DO UPDATE product_lot
SET low_date = now(), status = 0
WHERE CURDATE() >= expiration
/*DATEDIFF(CURDATE(),expiration) >= expiration*/$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
