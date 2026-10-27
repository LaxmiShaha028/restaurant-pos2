-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: restaurant
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `restaurant`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `restaurant` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;

USE `restaurant`;

--
-- Table structure for table `add_ons`
--

DROP TABLE IF EXISTS `add_ons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_ons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `add_on_name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `add_on_name_unique` (`add_on_name`),
  KEY `add_ons_is_available_index` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `add_ons`
--

LOCK TABLES `add_ons` WRITE;
/*!40000 ALTER TABLE `add_ons` DISABLE KEYS */;
/*!40000 ALTER TABLE `add_ons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_breaks`
--

DROP TABLE IF EXISTS `attendance_breaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_breaks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` bigint(20) unsigned NOT NULL,
  `break_start_at` datetime NOT NULL,
  `break_end_at` datetime DEFAULT NULL,
  `duration_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_breaks_created_by_foreign` (`created_by`),
  KEY `attendance_breaks_updated_by_foreign` (`updated_by`),
  KEY `attendance_breaks_attendance_id_break_start_at_index` (`attendance_id`,`break_start_at`),
  KEY `attendance_breaks_attendance_id_break_end_at_index` (`attendance_id`,`break_end_at`),
  CONSTRAINT `attendance_breaks_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `attendance_breaks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_breaks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_breaks`
--

LOCK TABLES `attendance_breaks` WRITE;
/*!40000 ALTER TABLE `attendance_breaks` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_breaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `shift_schedule_id` bigint(20) unsigned DEFAULT NULL,
  `shift_schedule_override_id` bigint(20) unsigned DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `scheduled_start_at` datetime NOT NULL,
  `scheduled_end_at` datetime NOT NULL,
  `grace_minutes` smallint(5) unsigned NOT NULL DEFAULT 0,
  `scheduled_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `check_in_at` datetime DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `auto_checked_out` tinyint(1) NOT NULL DEFAULT 0,
  `auto_checkout_reason` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'scheduled',
  `late_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `worked_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `break_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `overtime_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `early_leave_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `is_manual` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_employee_date_unique` (`employee_id`,`attendance_date`),
  KEY `attendances_shift_schedule_id_foreign` (`shift_schedule_id`),
  KEY `attendances_shift_schedule_override_id_foreign` (`shift_schedule_override_id`),
  KEY `attendances_created_by_foreign` (`created_by`),
  KEY `attendances_updated_by_foreign` (`updated_by`),
  KEY `attendances_attendance_date_status_index` (`attendance_date`,`status`),
  KEY `attendances_scheduled_start_at_scheduled_end_at_index` (`scheduled_start_at`,`scheduled_end_at`),
  CONSTRAINT `attendances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `attendances_shift_schedule_id_foreign` FOREIGN KEY (`shift_schedule_id`) REFERENCES `shift_schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_shift_schedule_override_id_foreign` FOREIGN KEY (`shift_schedule_override_id`) REFERENCES `shift_schedule_overrides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `module` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_module_action_index` (`module`,`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `last_visit_at` datetime DEFAULT NULL,
  `total_orders` int(10) unsigned NOT NULL DEFAULT 0,
  `total_spent` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_phone_unique` (`phone`),
  UNIQUE KEY `customers_email_unique` (`email`),
  KEY `customers_name_index` (`name`),
  KEY `customers_phone_index` (`phone`),
  KEY `customers_email_index` (`email`),
  KEY `customers_last_visit_at_index` (`last_visit_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `domains` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint(20) unsigned NOT NULL,
  `domain` varchar(255) NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'subdomain',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_token` varchar(150) DEFAULT NULL,
  `ssl_status` varchar(30) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domains_domain_unique` (`domain`),
  KEY `domains_restaurant_id_is_primary_index` (`restaurant_id`,`is_primary`),
  KEY `domains_is_verified_type_index` (`is_verified`,`type`),
  CONSTRAINT `domains_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
INSERT INTO `domains` VALUES (1,1,'sopno-restaurant.local','subdomain',1,1,NULL,'pending','2026-10-06 13:10:42','2026-10-06 13:10:42'),(2,3,'bongo-sylhet.local','subdomain',1,1,NULL,'pending','2026-10-08 02:21:42','2026-10-08 02:21:42');
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `joining_date` date NOT NULL,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_status` varchar(30) NOT NULL DEFAULT 'none',
  `status_updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_user_id_unique` (`user_id`),
  KEY `employees_created_by_foreign` (`created_by`),
  KEY `employees_updated_by_foreign` (`updated_by`),
  KEY `employees_joining_date_current_status_index` (`joining_date`,`current_status`),
  KEY `employees_current_status_status_updated_at_index` (`current_status`,`status_updated_at`),
  KEY `employees_current_status_index` (`current_status`),
  KEY `employees_phone_index` (`phone`),
  CONSTRAINT `employees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employees_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expense_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_categories_name_unique` (`name`),
  KEY `expense_categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES (1,'Raw Materials','Food ingredients and kitchen raw materials.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(2,'Salary','Employee salary and wages.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(3,'Utility Bills','Electricity, water, gas and internet bills.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(4,'Maintenance','Repair and maintenance expenses.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(5,'Rent','Restaurant rent and property expenses.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(6,'Transport','Delivery, transport and travel expenses.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(7,'Marketing','Advertising and promotional expenses.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(8,'Equipment','Kitchen and restaurant equipment purchase.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(9,'Other','Other miscellaneous expenses.',1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL);
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_category_id` bigint(20) unsigned NOT NULL,
  `expense_date` datetime NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_expense_category_id_foreign` (`expense_category_id`),
  KEY `expenses_updated_by_foreign` (`updated_by`),
  KEY `expenses_expense_date_expense_category_id_index` (`expense_date`,`expense_category_id`),
  KEY `expenses_expense_date_payment_method_index` (`expense_date`,`payment_method`),
  KEY `expenses_created_by_expense_date_index` (`created_by`,`expense_date`),
  KEY `expenses_expense_date_index` (`expense_date`),
  KEY `expenses_payment_method_index` (`payment_method`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `expenses_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_categories`
--

DROP TABLE IF EXISTS `menu_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_categories_category_name_unique` (`category_name`),
  KEY `menu_categories_is_available_index` (`is_available`),
  KEY `menu_categories_display_order_index` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_categories`
--

LOCK TABLES `menu_categories` WRITE;
/*!40000 ALTER TABLE `menu_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_add_on`
--

DROP TABLE IF EXISTS `menu_item_add_on`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_item_add_on` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned NOT NULL,
  `add_on_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_item_add_on_unique` (`menu_item_id`,`add_on_id`),
  KEY `menu_item_add_on_menu_item_id_index` (`menu_item_id`),
  KEY `menu_item_add_on_add_on_id_index` (`add_on_id`),
  CONSTRAINT `menu_item_add_on_add_on_id_foreign` FOREIGN KEY (`add_on_id`) REFERENCES `add_ons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `menu_item_add_on_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_add_on`
--

LOCK TABLES `menu_item_add_on` WRITE;
/*!40000 ALTER TABLE `menu_item_add_on` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_item_add_on` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_variants`
--

DROP TABLE IF EXISTS `menu_item_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_item_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned NOT NULL,
  `variant_name` varchar(120) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_item_variant_unique` (`menu_item_id`,`variant_name`),
  KEY `menu_item_variants_menu_item_id_is_available_index` (`menu_item_id`,`is_available`),
  KEY `menu_item_variants_is_available_index` (`is_available`),
  CONSTRAINT `menu_item_variants_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_variants`
--

LOCK TABLES `menu_item_variants` WRITE;
/*!40000 ALTER TABLE `menu_item_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_item_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_category_id` bigint(20) unsigned NOT NULL,
  `menu_name` varchar(180) NOT NULL,
  `item_type` enum('regular','combo','set_meal') NOT NULL DEFAULT 'regular',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ingredients` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `preparation_time` int(10) unsigned DEFAULT NULL COMMENT 'Preparation time in minutes',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_menu_category_id_is_available_index` (`menu_category_id`,`is_available`),
  KEY `menu_items_item_type_is_available_index` (`item_type`,`is_available`),
  KEY `menu_items_is_available_index` (`is_available`),
  KEY `menu_items_is_featured_index` (`is_featured`),
  CONSTRAINT `menu_items_menu_category_id_foreign` FOREIGN KEY (`menu_category_id`) REFERENCES `menu_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_07_20_033412_create_personal_access_tokens_table',1),(5,'2026_07_20_035937_create_roles_table',1),(6,'2026_07_20_040033_create_permissions_table',1),(7,'2026_07_20_040100_create_permission_role_table',1),(8,'2026_07_20_040212_add_rms_fields_to_users_table',1),(9,'2026_07_20_065031_create_menu_categories_table',1),(10,'2026_07_20_065032_create_menu_items_table',1),(11,'2026_07_20_065033_create_menu_item_variants_table',1),(12,'2026_07_20_065034_create_add_on_categories_table',1),(13,'2026_07_20_065035_create_add_ons_table',1),(14,'2026_07_20_171940_update_menu_foreign_keys_for_cascade_delete',1),(15,'2026_07_20_194627_update_menu_item_variants_foreign_key_for_cascade',1),(16,'2026_07_21_054031_make_add_ons_standalone',1),(17,'2026_07_21_054155_drop_add_on_categories_table',1),(18,'2026_07_21_062929_create_restaurant_tables_table',1),(19,'2026_07_22_045344_add_reserved_at_to_restaurant_tables_table',1),(20,'2026_07_22_053216_add_reservation_period_to_restaurant_tables_table',1),(21,'2026_07_23_103404_create_or_update_customers_table',1),(22,'2026_07_23_103405_create_order_tables_table',1),(23,'2026_07_23_103405_create_orders_table',1),(24,'2026_07_23_103406_create_order_items_table',1),(25,'2026_07_23_103407_create_order_item_addons_table',1),(26,'2026_07_23_145700_create_menu_item_add_on_table',1),(27,'2026_07_26_170218_create_order_payments_table',1),(28,'2026_07_27_150104_add_chef_id_to_orders_table',1),(29,'2026_08_02_100124_create_expense_categories_table',1),(30,'2026_08_02_100148_create_expenses_table',1),(31,'2026_08_02_124831_create_suppliers_table',1),(32,'2026_08_02_124858_create_purchase_orders_table',1),(33,'2026_08_02_124923_create_purchase_order_items_table',1),(34,'2026_08_03_120328_create_employees_table',1),(35,'2026_08_03_121619_add_phone_to_employees_table',1),(36,'2026_08_04_100656_create_shift_schedules_table',1),(37,'2026_08_04_104007_update_shift_schedules_for_recurring_ranges',1),(38,'2026_08_04_104044_create_shift_schedule_overrides_table',1),(39,'2026_08_04_112021_create_attendances_table',1),(40,'2026_08_04_112053_create_attendance_breaks_table',1),(41,'2026_08_04_120917_add_auto_checkout_fields_to_attendances_table',1),(42,'2026_08_04_143700_create_salary_payrolls_table',1),(43,'2026_08_04_143744_create_salary_details_table',1),(44,'2026_08_06_105327_create_raw_materials_table',1),(45,'2026_08_06_105328_create_warehouse_stocks_table',1),(46,'2026_08_06_105329_create_stock_movements_table',1),(47,'2026_08_06_105331_add_raw_material_id_to_purchase_order_items_table',1),(48,'2026_08_10_114652_create_purchase_order_payments_table',1),(49,'2026_08_10_151641_alter_purchase_order_payments_table',1),(50,'2026_08_10_161817_create_purchase_order_receipts_table',1),(51,'2026_08_10_161819_create_purchase_order_receipt_items_table',1),(52,'2026_08_10_165955_create_restaurant_stocks_table',1),(53,'2026_08_10_165956_create_stock_transfers_table',1),(54,'2026_08_10_165957_create_stock_transfer_items_table',1),(55,'2026_08_11_105245_alter_purchase_order_receipts_for_grn_integrity',1),(56,'2026_08_11_105743_alter_purchase_order_receipt_items_for_inventory_precision',1),(57,'2026_08_11_214918_create_recipe_mappings_table',1),(58,'2026_08_12_094003_create_order_recipe_consumptions_table',1),(59,'2026_08_12_114059_extend_recipe_mappings_for_add_ons',1),(60,'2026_08_12_160031_create_order_kitchen_batches_table',1),(61,'2026_08_12_160225_add_order_kitchen_batch_id_to_order_items_table',1),(62,'2026_08_12_161501_add_kitchen_batch_to_order_recipe_consumptions_table',1),(63,'2026_08_20_151719_add_variant_id_to_recipe_mappings_table',1),(64,'2026_08_22_123642_create_reservations_table',1),(65,'2026_08_22_123909_create_reservation_tables_table',1),(66,'2026_08_22_134752_add_is_primary_to_reservation_tables_table',1),(67,'2026_08_22_140106_add_unique_index_to_reservation_tables_table',1),(68,'2026_08_22_223759_add_reservation_id_to_orders_table',1),(69,'2026_08_23_151327_add_profile_image_to_employees_table',1),(70,'2026_08_23_165652_add_deleted_at_to_users_table',1),(71,'2026_08_24_093914_create_audit_logs_table',1),(72,'2026_08_24_131452_create_system_settings_table',1),(73,'2026_08_24_133336_add_charge_types_to_system_settings_table',1),(74,'2026_08_30_165212_create_super_admins_table',2),(75,'2026_08_30_165234_create_restaurants_table',2),(76,'2026_08_30_165303_create_subscription_plans_table',2),(77,'2026_08_30_165321_create_subscriptions_table',2),(78,'2026_08_30_165338_create_payments_table',2),(79,'2026_08_30_165356_create_tenant_databases_table',2),(80,'2026_08_30_165413_create_domains_table',2),(81,'2026_08_30_165436_create_payment_webhooks_table',2),(82,'2026_08_30_165456_create_platform_audit_logs_table',2),(83,'2026_09_02_123813_create_payment_methods_table',3);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item_addons`
--

DROP TABLE IF EXISTS `order_item_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_item_addons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `menu_addon_id` bigint(20) unsigned DEFAULT NULL,
  `addon_name` varchar(150) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_item_addons_order_item_id_foreign` (`order_item_id`),
  KEY `order_item_addons_menu_addon_id_index` (`menu_addon_id`),
  CONSTRAINT `order_item_addons_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_addons`
--

LOCK TABLES `order_item_addons` WRITE;
/*!40000 ALTER TABLE `order_item_addons` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_item_addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `order_kitchen_batch_id` bigint(20) unsigned NOT NULL,
  `menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `menu_item_variant_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(180) NOT NULL,
  `variant_name` varchar(150) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `addon_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `kitchen_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_menu_item_id_foreign` (`menu_item_id`),
  KEY `order_items_menu_item_variant_id_foreign` (`menu_item_variant_id`),
  KEY `order_items_status_index` (`status`),
  KEY `order_items_order_id_menu_item_id_index` (`order_id`,`menu_item_id`),
  KEY `order_items_batch_status_index` (`order_kitchen_batch_id`,`status`),
  CONSTRAINT `order_items_kitchen_batch_fk` FOREIGN KEY (`order_kitchen_batch_id`) REFERENCES `order_kitchen_batches` (`id`),
  CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_menu_item_variant_id_foreign` FOREIGN KEY (`menu_item_variant_id`) REFERENCES `menu_item_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_kitchen_batches`
--

DROP TABLE IF EXISTS `order_kitchen_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_kitchen_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `batch_no` int(10) unsigned NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `chef_id` bigint(20) unsigned DEFAULT NULL,
  `sent_to_kitchen_at` timestamp NULL DEFAULT NULL,
  `preparing_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_kitchen_batches_order_batch_unique` (`order_id`,`batch_no`),
  KEY `order_kitchen_batches_chef_id_foreign` (`chef_id`),
  KEY `order_kitchen_batches_created_by_foreign` (`created_by`),
  KEY `order_kitchen_batches_status_chef_index` (`status`,`chef_id`),
  CONSTRAINT `order_kitchen_batches_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_kitchen_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_kitchen_batches_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_kitchen_batches`
--

LOCK TABLES `order_kitchen_batches` WRITE;
/*!40000 ALTER TABLE `order_kitchen_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_kitchen_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_payments`
--

DROP TABLE IF EXISTS `order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_payments_received_by_foreign` (`received_by`),
  KEY `order_payments_order_id_created_at_index` (`order_id`,`created_at`),
  KEY `order_payments_payment_method_index` (`payment_method`),
  CONSTRAINT `order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_payments`
--

LOCK TABLES `order_payments` WRITE;
/*!40000 ALTER TABLE `order_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_recipe_consumption_items`
--

DROP TABLE IF EXISTS `order_recipe_consumption_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_recipe_consumption_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_recipe_consumption_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `restaurant_quantity_before` decimal(14,4) NOT NULL,
  `restaurant_quantity_after` decimal(14,4) NOT NULL,
  `source_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`source_breakdown`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orci_consumption_material_unique` (`order_recipe_consumption_id`,`raw_material_id`),
  KEY `orci_material_idx` (`raw_material_id`),
  CONSTRAINT `orci_consumption_fk` FOREIGN KEY (`order_recipe_consumption_id`) REFERENCES `order_recipe_consumptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orci_material_fk` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_recipe_consumption_items`
--

LOCK TABLES `order_recipe_consumption_items` WRITE;
/*!40000 ALTER TABLE `order_recipe_consumption_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_recipe_consumption_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_recipe_consumptions`
--

DROP TABLE IF EXISTS `order_recipe_consumptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_recipe_consumptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `order_kitchen_batch_id` bigint(20) unsigned NOT NULL,
  `order_number` varchar(100) NOT NULL,
  `trigger` varchar(50) NOT NULL DEFAULT 'start_preparing',
  `order_status_snapshot` varchar(30) NOT NULL,
  `consumed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_recipe_consumptions_order_batch_unique` (`order_id`,`order_kitchen_batch_id`),
  KEY `orc_created_by_fk` (`created_by`),
  KEY `orc_consumed_at_idx` (`consumed_at`),
  KEY `order_recipe_consumptions_batch_index` (`order_kitchen_batch_id`),
  CONSTRAINT `orc_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orc_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_recipe_consumptions_batch_fk` FOREIGN KEY (`order_kitchen_batch_id`) REFERENCES `order_kitchen_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_recipe_consumptions`
--

LOCK TABLES `order_recipe_consumptions` WRITE;
/*!40000 ALTER TABLE `order_recipe_consumptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_recipe_consumptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_tables`
--

DROP TABLE IF EXISTS `order_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `restaurant_table_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_tables_order_id_restaurant_table_id_unique` (`order_id`,`restaurant_table_id`),
  KEY `order_tables_restaurant_table_id_order_id_index` (`restaurant_table_id`,`order_id`),
  CONSTRAINT `order_tables_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_tables_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_tables`
--

LOCK TABLES `order_tables` WRITE;
/*!40000 ALTER TABLE `order_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `restaurant_table_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(30) NOT NULL DEFAULT 'due',
  `payment_method` varchar(30) DEFAULT NULL,
  `payment_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_breakdown`)),
  `payment_reference` varchar(150) DEFAULT NULL,
  `order_note` text DEFAULT NULL,
  `kitchen_note` text DEFAULT NULL,
  `sent_to_kitchen_at` datetime DEFAULT NULL,
  `preparing_at` datetime DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL,
  `served_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `is_customer_spend_recorded` tinyint(1) NOT NULL DEFAULT 0,
  `customer_spend_recorded_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `chef_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_created_by_foreign` (`created_by`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_payment_method_index` (`payment_method`),
  KEY `orders_created_at_index` (`created_at`),
  KEY `orders_restaurant_table_id_status_index` (`restaurant_table_id`,`status`),
  KEY `orders_customer_id_completed_at_index` (`customer_id`,`completed_at`),
  KEY `orders_chef_status_index` (`chef_id`,`status`),
  KEY `orders_reservation_id_index` (`reservation_id`),
  CONSTRAINT `orders_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_methods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_methods_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,'bKash','bkash',NULL,NULL,1,'2026-10-06 13:08:06','2026-10-06 13:08:06');
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_webhooks`
--

DROP TABLE IF EXISTS `payment_webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_webhooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned DEFAULT NULL,
  `gateway` varchar(50) NOT NULL,
  `event_id` varchar(180) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `signature_verified` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_webhooks_gateway_event_id_unique` (`gateway`,`event_id`),
  KEY `payment_webhooks_payment_id_foreign` (`payment_id`),
  KEY `payment_webhooks_gateway_event_type_index` (`gateway`,`event_type`),
  KEY `payment_webhooks_status_index` (`status`),
  CONSTRAINT `payment_webhooks_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_webhooks`
--

LOCK TABLES `payment_webhooks` WRITE;
/*!40000 ALTER TABLE `payment_webhooks` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_webhooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint(20) unsigned NOT NULL,
  `subscription_id` bigint(20) unsigned DEFAULT NULL,
  `gateway` varchar(50) NOT NULL,
  `transaction_id` varchar(150) DEFAULT NULL,
  `reference` varchar(150) DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'BDT',
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_subscription_id_foreign` (`subscription_id`),
  KEY `payments_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `payments_transaction_id_index` (`transaction_id`),
  KEY `payments_gateway_index` (`gateway`),
  CONSTRAINT `payments_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `payments_subscription_id_foreign` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,1,'manual','9N7K2P1L8M','LOCAL-TEST-SOPNO-001',100.00,'BDT','bkash','paid','2026-10-06 13:10:20','{\"type\":\"manual\",\"payment_type\":\"initial\",\"payment_method_name\":\"bKash\",\"notes\":null,\"recorded_by\":1}','2026-10-06 13:10:20','2026-10-06 13:10:20'),(2,3,3,'manual','e3546y46','LOCAL-TEST-SOPNO-001',100.00,'BDT','bkash','paid','2026-10-08 02:21:28','{\"type\":\"manual\",\"payment_type\":\"initial\",\"payment_method_name\":\"bKash\",\"notes\":null,\"recorded_by\":1}','2026-10-08 02:21:28','2026-10-08 02:21:28');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_role_unique` (`role_id`,`permission_id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`),
  KEY `permissions_module_index` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\Central\\SuperAdmin',1,'central-api-token','cb732b774b37a20f0ba98b6d1dbf5c2f6c579ed9aeae021b93dca282fce9001f','\"[\\\"*\\\"]\"','2026-10-26 05:56:28',NULL,'2026-10-06 12:50:00','2026-10-26 05:56:28');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `platform_audit_logs`
--

DROP TABLE IF EXISTS `platform_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `platform_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `super_admin_id` bigint(20) unsigned DEFAULT NULL,
  `restaurant_id` bigint(20) unsigned DEFAULT NULL,
  `module` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `platform_audit_logs_super_admin_id_foreign` (`super_admin_id`),
  KEY `platform_audit_logs_module_action_index` (`module`,`action`),
  KEY `platform_audit_logs_restaurant_id_index` (`restaurant_id`),
  KEY `platform_audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `platform_audit_logs_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `platform_audit_logs_super_admin_id_foreign` FOREIGN KEY (`super_admin_id`) REFERENCES `super_admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `platform_audit_logs`
--

LOCK TABLES `platform_audit_logs` WRITE;
/*!40000 ALTER TABLE `platform_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `platform_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `received_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_items_material_order_index` (`raw_material_id`,`purchase_order_id`),
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_payments`
--

DROP TABLE IF EXISTS `purchase_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_payments_created_by_foreign` (`created_by`),
  KEY `purchase_order_payments_updated_by_foreign` (`updated_by`),
  KEY `purchase_order_payments_purchase_order_id_payment_date_index` (`purchase_order_id`,`payment_date`),
  CONSTRAINT `purchase_order_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_payments_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_payments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_payments`
--

LOCK TABLES `purchase_order_payments` WRITE;
/*!40000 ALTER TABLE `purchase_order_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_receipt_items`
--

DROP TABLE IF EXISTS `purchase_order_receipt_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_receipt_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_receipt_id` bigint(20) unsigned NOT NULL,
  `purchase_order_item_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL,
  `total_cost` decimal(18,4) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_receipt_item_unique` (`purchase_order_receipt_id`,`purchase_order_item_id`),
  KEY `purchase_order_receipt_items_purchase_order_item_id_foreign` (`purchase_order_item_id`),
  KEY `purchase_order_receipt_items_raw_material_id_index` (`raw_material_id`),
  CONSTRAINT `purchase_order_receipt_items_purchase_order_item_id_foreign` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`),
  CONSTRAINT `purchase_order_receipt_items_purchase_order_receipt_id_foreign` FOREIGN KEY (`purchase_order_receipt_id`) REFERENCES `purchase_order_receipts` (`id`),
  CONSTRAINT `purchase_order_receipt_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_receipt_items`
--

LOCK TABLES `purchase_order_receipt_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_receipt_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_receipt_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_receipts`
--

DROP TABLE IF EXISTS `purchase_order_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `receipt_no` varchar(80) NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_order_receipts_receipt_no_unique` (`receipt_no`),
  KEY `purchase_order_receipts_received_by_foreign` (`received_by`),
  KEY `purchase_order_receipts_created_by_foreign` (`created_by`),
  KEY `purchase_order_receipts_updated_by_foreign` (`updated_by`),
  KEY `purchase_order_receipts_purchase_order_id_received_at_index` (`purchase_order_id`,`received_at`),
  CONSTRAINT `purchase_order_receipts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_receipts_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  CONSTRAINT `purchase_order_receipts_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_receipts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_receipts`
--

LOCK TABLES `purchase_order_receipts` WRITE;
/*!40000 ALTER TABLE `purchase_order_receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `order_date` datetime NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'ordered',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `ordered_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_orders_ordered_by_foreign` (`ordered_by`),
  KEY `purchase_orders_created_by_foreign` (`created_by`),
  KEY `purchase_orders_updated_by_foreign` (`updated_by`),
  KEY `purchase_orders_supplier_id_order_date_index` (`supplier_id`,`order_date`),
  KEY `purchase_orders_status_index` (`status`),
  CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_ordered_by_foreign` FOREIGN KEY (`ordered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchase_orders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raw_materials`
--

DROP TABLE IF EXISTS `raw_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raw_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_name` varchar(180) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `base_unit` varchar(30) NOT NULL,
  `warehouse_minimum_quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `restaurant_minimum_quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `raw_materials_created_by_foreign` (`created_by`),
  KEY `raw_materials_updated_by_foreign` (`updated_by`),
  KEY `raw_materials_material_name_base_unit_is_active_index` (`material_name`,`base_unit`,`is_active`),
  KEY `raw_materials_material_name_index` (`material_name`),
  KEY `raw_materials_category_index` (`category`),
  KEY `raw_materials_is_active_index` (`is_active`),
  CONSTRAINT `raw_materials_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `raw_materials_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raw_materials`
--

LOCK TABLES `raw_materials` WRITE;
/*!40000 ALTER TABLE `raw_materials` DISABLE KEYS */;
/*!40000 ALTER TABLE `raw_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_mappings`
--

DROP TABLE IF EXISTS `recipe_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recipe_mappings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `variant_id` bigint(20) unsigned DEFAULT NULL,
  `add_on_id` bigint(20) unsigned DEFAULT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recipe_mapping_addon_material_unique` (`add_on_id`,`raw_material_id`),
  UNIQUE KEY `recipe_mapping_menu_variant_material_unique` (`menu_item_id`,`variant_id`,`raw_material_id`),
  KEY `recipe_mappings_created_by_foreign` (`created_by`),
  KEY `recipe_mappings_updated_by_foreign` (`updated_by`),
  KEY `recipe_mapping_raw_material_index` (`raw_material_id`),
  KEY `recipe_mapping_menu_item_index` (`menu_item_id`),
  KEY `recipe_mapping_variant_index` (`variant_id`),
  CONSTRAINT `recipe_mapping_addon_fk` FOREIGN KEY (`add_on_id`) REFERENCES `add_ons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recipe_mapping_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `menu_item_variants` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `recipe_mappings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recipe_mappings_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recipe_mappings_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `recipe_mappings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recipe_mapping_target_check` CHECK (`menu_item_id` is not null and `add_on_id` is null or `menu_item_id` is null and `add_on_id` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_mappings`
--

LOCK TABLES `recipe_mappings` WRITE;
/*!40000 ALTER TABLE `recipe_mappings` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipe_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_tables`
--

DROP TABLE IF EXISTS `reservation_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `restaurant_table_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_tables_reservation_id_restaurant_table_id_unique` (`reservation_id`,`restaurant_table_id`),
  KEY `reservation_tables_restaurant_table_id_foreign` (`restaurant_table_id`),
  KEY `reservation_tables_reservation_id_is_primary_index` (`reservation_id`,`is_primary`),
  CONSTRAINT `reservation_tables_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_tables_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_tables`
--

LOCK TABLES `reservation_tables` WRITE;
/*!40000 ALTER TABLE `reservation_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_no` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `guest_count` int(10) unsigned NOT NULL,
  `advance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('reserved','confirmed','seated','completed','cancelled') NOT NULL DEFAULT 'reserved',
  `note` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservations_reservation_no_unique` (`reservation_no`),
  KEY `reservations_customer_id_foreign` (`customer_id`),
  CONSTRAINT `reservations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_stocks`
--

DROP TABLE IF EXISTS `restaurant_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `average_unit_cost` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `last_received_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_stocks_raw_material_unique` (`raw_material_id`),
  KEY `restaurant_stocks_created_by_foreign` (`created_by`),
  KEY `restaurant_stocks_updated_by_foreign` (`updated_by`),
  CONSTRAINT `restaurant_stocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_stocks_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `restaurant_stocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_stocks`
--

LOCK TABLES `restaurant_stocks` WRITE;
/*!40000 ALTER TABLE `restaurant_stocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_tables`
--

DROP TABLE IF EXISTS `restaurant_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `capacity` int(10) unsigned NOT NULL DEFAULT 1,
  `section` enum('ac','non_ac','outdoor') NOT NULL,
  `status` enum('available','occupied','reserved','cleaning') NOT NULL DEFAULT 'available',
  `reservation_start_at` datetime DEFAULT NULL,
  `reservation_end_at` datetime DEFAULT NULL,
  `reserved_at` datetime DEFAULT NULL,
  `merged_with_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_tables_name_deleted_unique` (`table_name`,`deleted_at`),
  KEY `restaurant_tables_merged_with_id_foreign` (`merged_with_id`),
  KEY `restaurant_tables_section_status_index` (`section`,`status`),
  KEY `restaurant_tables_section_index` (`section`),
  KEY `restaurant_tables_status_index` (`status`),
  CONSTRAINT `restaurant_tables_merged_with_id_foreign` FOREIGN KEY (`merged_with_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_tables`
--

LOCK TABLES `restaurant_tables` WRITE;
/*!40000 ALTER TABLE `restaurant_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurants`
--

DROP TABLE IF EXISTS `restaurants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(180) NOT NULL,
  `slug` varchar(180) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `owner_name` varchar(150) NOT NULL,
  `owner_email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `tenant_key` varchar(100) NOT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurants_slug_unique` (`slug`),
  UNIQUE KEY `restaurants_tenant_key_unique` (`tenant_key`),
  KEY `restaurants_status_index` (`status`),
  KEY `restaurants_owner_email_status_index` (`owner_email`,`status`),
  KEY `restaurants_owner_email_index` (`owner_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurants`
--

LOCK TABLES `restaurants` WRITE;
/*!40000 ALTER TABLE `restaurants` DISABLE KEYS */;
INSERT INTO `restaurants` VALUES (1,'Sopno Restaurant','sopno-restaurant',NULL,'Tisha','shraboniakter554@gmail.com','+1 (919) 283-8996','974 Green Cowley Freeway','Sylhet','Bangladesh','sopno-restaurant-ouvof0uc','active','2026-10-06 12:58:21','2026-10-06 13:10:42',NULL),(2,'Bongo','bongo',NULL,'Sadia','shraboniakter554@gmail.com','+1 (875) 736-5902','Doloi para','Sylhet','Bangladesh','bongo-fydrkq5x','inactive','2026-10-08 02:10:47','2026-10-08 02:18:58',NULL),(3,'Bongo Sylhet','bongo-sylhet',NULL,'Sadia','shraboniakter554@gmail.com','+1 (919) 283-8996','974 Green Cowley Freeway','Et at porro molestia','Bangladesh','bongo-sylhet-dggiajt4','inactive','2026-10-08 02:20:57','2026-10-12 03:40:13',NULL);
/*!40000 ALTER TABLE `restaurants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_details`
--

DROP TABLE IF EXISTS `salary_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `salary_payroll_id` bigint(20) unsigned NOT NULL,
  `attendance_id` bigint(20) unsigned DEFAULT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `salary_date` date NOT NULL,
  `scheduled_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `worked_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `late_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `break_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `overtime_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `salary_type` varchar(50) NOT NULL DEFAULT 'no_salary',
  `regular_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `overtime_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `calculation_source` varchar(20) NOT NULL DEFAULT 'automatic',
  `notes` text DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_details_attendance_id_foreign` (`attendance_id`),
  KEY `salary_details_updated_by_foreign` (`updated_by`),
  KEY `salary_detail_payroll_date_index` (`salary_payroll_id`,`salary_date`),
  KEY `salary_detail_employee_date_index` (`employee_id`,`salary_date`),
  KEY `salary_detail_type_date_index` (`salary_type`,`salary_date`),
  CONSTRAINT `salary_details_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `salary_details_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `salary_details_salary_payroll_id_foreign` FOREIGN KEY (`salary_payroll_id`) REFERENCES `salary_payrolls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `salary_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_details`
--

LOCK TABLES `salary_details` WRITE;
/*!40000 ALTER TABLE `salary_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_payrolls`
--

DROP TABLE IF EXISTS `salary_payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_payrolls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `employee_name` varchar(255) NOT NULL,
  `employee_phone` varchar(255) DEFAULT NULL,
  `employee_email` varchar(255) DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `regular_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `overtime_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `adjustment_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `paid_at` datetime DEFAULT NULL,
  `paid_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_payrolls_paid_by_foreign` (`paid_by`),
  KEY `salary_payrolls_created_by_foreign` (`created_by`),
  KEY `salary_payrolls_updated_by_foreign` (`updated_by`),
  KEY `salary_payroll_employee_period_index` (`employee_id`,`period_start`,`period_end`),
  KEY `salary_payroll_status_period_index` (`payment_status`,`period_start`,`period_end`),
  CONSTRAINT `salary_payrolls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `salary_payrolls_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_payrolls_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_payrolls`
--

LOCK TABLES `salary_payrolls` WRITE;
/*!40000 ALTER TABLE `salary_payrolls` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_payrolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_schedule_overrides`
--

DROP TABLE IF EXISTS `shift_schedule_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_schedule_overrides` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `shift_schedule_id` bigint(20) unsigned NOT NULL,
  `override_date` date NOT NULL,
  `override_type` varchar(30) NOT NULL DEFAULT 'modified',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `grace_minutes` smallint(5) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_schedule_overrides_created_by_foreign` (`created_by`),
  KEY `shift_schedule_overrides_updated_by_foreign` (`updated_by`),
  KEY `shift_schedule_overrides_shift_schedule_id_override_date_index` (`shift_schedule_id`,`override_date`),
  KEY `shift_schedule_overrides_override_date_override_type_index` (`override_date`,`override_type`),
  CONSTRAINT `shift_schedule_overrides_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shift_schedule_overrides_shift_schedule_id_foreign` FOREIGN KEY (`shift_schedule_id`) REFERENCES `shift_schedules` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shift_schedule_overrides_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_schedule_overrides`
--

LOCK TABLES `shift_schedule_overrides` WRITE;
/*!40000 ALTER TABLE `shift_schedule_overrides` DISABLE KEYS */;
/*!40000 ALTER TABLE `shift_schedule_overrides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_schedules`
--

DROP TABLE IF EXISTS `shift_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `working_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_days`)),
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_minutes` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_schedules_created_by_foreign` (`created_by`),
  KEY `shift_schedules_updated_by_foreign` (`updated_by`),
  KEY `shift_schedules_employee_date_range_index` (`employee_id`,`start_date`,`end_date`),
  KEY `shift_schedules_date_range_status_index` (`start_date`,`end_date`,`is_active`),
  CONSTRAINT `shift_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shift_schedules_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shift_schedules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_schedules`
--

LOCK TABLES `shift_schedules` WRITE;
/*!40000 ALTER TABLE `shift_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `shift_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `location` varchar(30) NOT NULL,
  `movement_type` varchar(50) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `quantity_before` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `quantity_after` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `unit_cost` decimal(18,4) unsigned DEFAULT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `unit` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_created_by_foreign` (`created_by`),
  KEY `stock_movements_material_location_type_index` (`raw_material_id`,`location`,`movement_type`),
  KEY `stock_movements_reference_index` (`reference_type`,`reference_id`),
  KEY `stock_movements_material_date_index` (`raw_material_id`,`created_at`),
  KEY `stock_movements_location_index` (`location`),
  KEY `stock_movements_movement_type_index` (`movement_type`),
  KEY `stock_movements_reference_type_index` (`reference_type`),
  KEY `stock_movements_reference_id_index` (`reference_id`),
  CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_items`
--

DROP TABLE IF EXISTS `stock_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_transfer_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL,
  `warehouse_quantity_before` decimal(14,4) NOT NULL,
  `warehouse_quantity_after` decimal(14,4) NOT NULL,
  `restaurant_quantity_before` decimal(14,4) NOT NULL,
  `restaurant_quantity_after` decimal(14,4) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfer_material_unique` (`stock_transfer_id`,`raw_material_id`),
  KEY `stock_transfer_items_raw_material_id_index` (`raw_material_id`),
  CONSTRAINT `stock_transfer_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `stock_transfer_items_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_items`
--

LOCK TABLES `stock_transfer_items` WRITE;
/*!40000 ALTER TABLE `stock_transfer_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_no` varchar(80) DEFAULT NULL,
  `transferred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `transferred_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfers_transfer_no_unique` (`transfer_no`),
  KEY `stock_transfers_transferred_by_foreign` (`transferred_by`),
  KEY `stock_transfers_created_by_foreign` (`created_by`),
  KEY `stock_transfers_updated_by_foreign` (`updated_by`),
  KEY `stock_transfers_transferred_at_index` (`transferred_at`),
  CONSTRAINT `stock_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_transferred_by_foreign` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfers`
--

LOCK TABLES `stock_transfers` WRITE;
/*!40000 ALTER TABLE `stock_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscription_plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) NOT NULL DEFAULT 'BDT',
  `billing_interval` varchar(20) NOT NULL DEFAULT 'month',
  `billing_interval_count` int(10) unsigned NOT NULL DEFAULT 1,
  `trial_days` int(10) unsigned NOT NULL DEFAULT 0,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_plans_slug_unique` (`slug`),
  KEY `subscription_plans_is_active_index` (`is_active`),
  KEY `subscription_plans_is_active_sort_order_index` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscription_plans`
--

LOCK TABLES `subscription_plans` WRITE;
/*!40000 ALTER TABLE `subscription_plans` DISABLE KEYS */;
INSERT INTO `subscription_plans` VALUES (1,'Professional','professional',NULL,100.00,'BDT','month',1,15,NULL,1,0,'2026-10-06 12:56:50','2026-10-06 12:56:50',NULL),(2,'Subcription','subcription',NULL,150.00,'BDT','month',1,15,NULL,1,0,'2026-10-06 21:46:57','2026-10-06 21:47:48',NULL),(3,'Trial','trial','Free trial plan for new restaurants.',0.00,'BDT','month',1,14,'[\"Dashboard\",\"Menu Management\",\"Order Management\",\"Kitchen Management\",\"Basic Reports\"]',1,1,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL),(4,'Enterprise','enterprise','Full restaurant management system with advanced features.',5000.00,'BDT','month',1,0,'[\"All Modules\",\"Inventory\",\"Warehouse\",\"Salary Management\",\"Advanced Reports\",\"Kitchen Management\",\"Billing\",\"Staff Management\",\"Priority Support\"]',1,2,'2026-10-26 04:14:49','2026-10-26 04:14:49',NULL);
/*!40000 ALTER TABLE `subscription_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint(20) unsigned NOT NULL,
  `subscription_plan_id` bigint(20) unsigned NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `start_at` timestamp NULL DEFAULT NULL,
  `end_at` timestamp NULL DEFAULT NULL,
  `trial_start_at` timestamp NULL DEFAULT NULL,
  `trial_end_at` timestamp NULL DEFAULT NULL,
  `auto_renew` tinyint(1) NOT NULL DEFAULT 0,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_subscription_plan_id_foreign` (`subscription_plan_id`),
  KEY `subscriptions_restaurant_id_status_index` (`restaurant_id`,`status`),
  KEY `subscriptions_status_end_at_index` (`status`,`end_at`),
  CONSTRAINT `subscriptions_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `subscriptions_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,1,1,'active','2026-10-06 13:10:42','2026-11-06 13:10:42',NULL,NULL,0,NULL,NULL,'2026-10-06 12:58:21','2026-10-06 13:10:42'),(2,2,2,'active',NULL,NULL,NULL,NULL,0,NULL,NULL,'2026-10-08 02:10:47','2026-10-08 02:11:36'),(3,3,1,'active','2026-10-08 02:21:43','2026-11-08 02:21:43',NULL,NULL,0,NULL,NULL,'2026-10-08 02:20:57','2026-10-08 02:21:43');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `super_admins`
--

DROP TABLE IF EXISTS `super_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `super_admins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_logout_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `blocked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `super_admins_username_unique` (`username`),
  UNIQUE KEY `super_admins_email_unique` (`email`),
  KEY `super_admins_is_active_index` (`is_active`),
  KEY `super_admins_blocked_at_index` (`blocked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `super_admins`
--

LOCK TABLES `super_admins` WRITE;
/*!40000 ALTER TABLE `super_admins` DISABLE KEYS */;
INSERT INTO `super_admins` VALUES (1,'Platform Admin','admin','admin123@gmail.com',NULL,'$2y$12$DmqdBtpLLN7.367eIlWVve5lcSixIE1jo0Zx8J26XpKdof3uR5s.a',NULL,1,'2026-10-06 12:50:00',NULL,0,NULL,'2026-10-06 12:48:44','2026-10-06 12:50:00',NULL);
/*!40000 ALTER TABLE `super_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text DEFAULT NULL,
  `gstin` varchar(50) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_created_by_foreign` (`created_by`),
  KEY `suppliers_updated_by_foreign` (`updated_by`),
  KEY `suppliers_supplier_name_index` (`supplier_name`),
  KEY `suppliers_phone_index` (`phone`),
  CONSTRAINT `suppliers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `suppliers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_name` varchar(255) DEFAULT NULL,
  `restaurant_logo` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `branch_address` varchar(255) DEFAULT NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vat_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `service_charge_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `service_charge_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `owner_nid` varchar(255) DEFAULT NULL,
  `owner_trade_license` varchar(255) DEFAULT NULL,
  `invoice_subtitle` varchar(255) DEFAULT NULL,
  `bin_number` varchar(255) DEFAULT NULL,
  `invoice_show_logo` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_company_name` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_branch_address` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_token_number` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_table_number` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_currency` varchar(255) NOT NULL DEFAULT 'BDT',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_databases`
--

DROP TABLE IF EXISTS `tenant_databases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tenant_databases` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint(20) unsigned NOT NULL,
  `connection_name` varchar(100) NOT NULL DEFAULT 'tenant',
  `database_host` varchar(255) NOT NULL,
  `database_port` smallint(5) unsigned NOT NULL DEFAULT 3306,
  `database_name` varchar(180) NOT NULL,
  `database_username` varchar(150) NOT NULL,
  `database_password` text DEFAULT NULL,
  `status` varchar(40) NOT NULL DEFAULT 'pending',
  `migrated_at` timestamp NULL DEFAULT NULL,
  `provisioned_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_databases_restaurant_id_unique` (`restaurant_id`),
  UNIQUE KEY `tenant_databases_database_name_unique` (`database_name`),
  KEY `tenant_databases_status_index` (`status`),
  CONSTRAINT `tenant_databases_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_databases`
--

LOCK TABLES `tenant_databases` WRITE;
/*!40000 ALTER TABLE `tenant_databases` DISABLE KEYS */;
INSERT INTO `tenant_databases` VALUES (1,1,'tenant','127.0.0.1',3306,'tenant_sopno_restaurant','root','eyJpdiI6IkJMYVBiTzVkV3QvT1FScXh3ellIUUE9PSIsInZhbHVlIjoiSStESENrdmdDSmMyMXlaSTRpSmdNdz09IiwibWFjIjoiY2I5ZWZiZTU1MzhjZWM4NjA5YjBhOWFmY2E2YTNlMDUwODJhZDg1OGZjYzIzZTYzZDc2YTA3NzVlY2ZmMDBhYSIsInRhZyI6IiJ9','active','2026-10-25 03:22:08','2026-10-06 13:10:42',NULL,'2026-10-06 13:10:21','2026-10-25 03:22:08'),(2,3,'tenant','127.0.0.1',3306,'tenant_bongo_sylhet','root','eyJpdiI6IlV3WUFjOFl6b3JkSEdqdnMxcStoUmc9PSIsInZhbHVlIjoib0NjbDRFdHhmRmRpSThncGpJTUtmdz09IiwibWFjIjoiNzliYmZiN2E0ZGI3MGJjM2I3NDQwNDI0NDFjMThiNjJhODdkNGMyYjIzY2QxY2IyZjlmY2U5NTQ5ZjA3NWNmNyIsInRhZyI6IiJ9','active','2026-10-25 03:22:08','2026-10-08 02:21:42',NULL,'2026-10-08 02:21:28','2026-10-25 03:22:08');
/*!40000 ALTER TABLE `tenant_databases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_logout_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `blocked_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_id_foreign` (`role_id`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stocks`
--

DROP TABLE IF EXISTS `warehouse_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `average_unit_cost` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `last_received_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouse_stocks_raw_material_unique` (`raw_material_id`),
  KEY `warehouse_stocks_created_by_foreign` (`created_by`),
  KEY `warehouse_stocks_updated_by_foreign` (`updated_by`),
  KEY `warehouse_stocks_quantity_last_received_at_index` (`quantity`,`last_received_at`),
  CONSTRAINT `warehouse_stocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stocks_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `warehouse_stocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stocks`
--

LOCK TABLES `warehouse_stocks` WRITE;
/*!40000 ALTER TABLE `warehouse_stocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `warehouse_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'restaurant'
--

--
-- Dumping routines for database 'restaurant'
--

--
-- Current Database: `tenant_sopno_restaurant`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `tenant_sopno_restaurant` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `tenant_sopno_restaurant`;

--
-- Table structure for table `accounting_payment_mappings`
--

DROP TABLE IF EXISTS `accounting_payment_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_payment_mappings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_method` varchar(50) NOT NULL,
  `ledger_account_id` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_payment_mappings_payment_method_unique` (`payment_method`),
  KEY `accounting_payment_ledger_idx` (`ledger_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_payment_mappings`
--

LOCK TABLES `accounting_payment_mappings` WRITE;
/*!40000 ALTER TABLE `accounting_payment_mappings` DISABLE KEYS */;
INSERT INTO `accounting_payment_mappings` VALUES (1,'cash',2,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(2,'bank_transfer',3,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(3,'card',4,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(4,'bkash',5,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(5,'nagad',6,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(6,'mixed',7,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(7,'other',8,1,'2026-10-06 13:10:41','2026-10-07 01:17:34');
/*!40000 ALTER TABLE `accounting_payment_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `add_ons`
--

DROP TABLE IF EXISTS `add_ons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_ons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `add_on_name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `add_on_name_unique` (`add_on_name`),
  KEY `add_ons_add_on_name_index` (`add_on_name`),
  KEY `add_ons_is_available_index` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `add_ons`
--

LOCK TABLES `add_ons` WRITE;
/*!40000 ALTER TABLE `add_ons` DISABLE KEYS */;
INSERT INTO `add_ons` VALUES (1,'Extra Cheese',2.00,NULL,1,'2026-10-06 23:42:02','2026-10-06 23:42:02',NULL),(2,'Tomato sauce',0.00,NULL,1,'2026-10-06 23:42:24','2026-10-06 23:42:24',NULL);
/*!40000 ALTER TABLE `add_ons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_breaks`
--

DROP TABLE IF EXISTS `attendance_breaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_breaks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` bigint(20) unsigned NOT NULL,
  `break_start_at` datetime NOT NULL,
  `break_end_at` datetime DEFAULT NULL,
  `duration_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_breaks_created_by_foreign` (`created_by`),
  KEY `attendance_breaks_updated_by_foreign` (`updated_by`),
  KEY `attendance_breaks_attendance_id_break_start_at_index` (`attendance_id`,`break_start_at`),
  KEY `attendance_breaks_attendance_id_break_end_at_index` (`attendance_id`,`break_end_at`),
  CONSTRAINT `attendance_breaks_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `attendance_breaks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_breaks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_breaks`
--

LOCK TABLES `attendance_breaks` WRITE;
/*!40000 ALTER TABLE `attendance_breaks` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_breaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `shift_schedule_id` bigint(20) unsigned DEFAULT NULL,
  `shift_schedule_override_id` bigint(20) unsigned DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `scheduled_start_at` datetime NOT NULL,
  `scheduled_end_at` datetime NOT NULL,
  `grace_minutes` smallint(5) unsigned NOT NULL DEFAULT 0,
  `scheduled_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `check_in_at` datetime DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `auto_checked_out` tinyint(1) NOT NULL DEFAULT 0,
  `auto_checkout_reason` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'scheduled',
  `late_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `worked_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `break_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `overtime_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `early_leave_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `is_manual` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_employee_date_unique` (`employee_id`,`attendance_date`),
  KEY `attendances_shift_schedule_id_foreign` (`shift_schedule_id`),
  KEY `attendances_shift_schedule_override_id_foreign` (`shift_schedule_override_id`),
  KEY `attendances_created_by_foreign` (`created_by`),
  KEY `attendances_updated_by_foreign` (`updated_by`),
  KEY `attendances_attendance_date_status_index` (`attendance_date`,`status`),
  KEY `attendances_scheduled_start_at_scheduled_end_at_index` (`scheduled_start_at`,`scheduled_end_at`),
  CONSTRAINT `attendances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `attendances_shift_schedule_id_foreign` FOREIGN KEY (`shift_schedule_id`) REFERENCES `shift_schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_shift_schedule_override_id_foreign` FOREIGN KEY (`shift_schedule_override_id`) REFERENCES `shift_schedule_overrides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
INSERT INTO `attendances` VALUES (7,1,1,1,'2026-10-07','2026-10-07 13:00:00','2026-10-07 17:00:00',60,240,NULL,NULL,0,NULL,'scheduled',0,0,0,0,0,0,NULL,1,1,'2026-10-07 00:59:57','2026-10-07 00:59:57'),(8,2,2,NULL,'2026-10-07','2026-10-07 09:00:00','2026-10-07 17:00:00',60,480,NULL,NULL,0,NULL,'scheduled',0,0,0,0,0,0,NULL,1,1,'2026-10-07 00:59:57','2026-10-07 00:59:57'),(9,1,1,NULL,'2026-10-08','2026-10-08 09:00:00','2026-10-08 17:00:00',60,480,NULL,NULL,0,NULL,'scheduled',0,0,0,0,0,0,NULL,1,1,'2026-10-07 21:55:13','2026-10-07 21:55:13'),(10,2,2,NULL,'2026-10-08','2026-10-08 09:00:00','2026-10-08 17:00:00',60,480,NULL,NULL,0,NULL,'scheduled',0,0,0,0,0,0,NULL,1,1,'2026-10-07 21:55:13','2026-10-07 21:55:13'),(11,1,1,NULL,'2026-10-11','2026-10-11 09:00:00','2026-10-11 17:00:00',60,480,NULL,NULL,0,NULL,'absent',0,0,0,0,0,0,NULL,1,1,'2026-10-11 01:35:14','2026-10-11 04:06:59'),(12,2,2,NULL,'2026-10-11','2026-10-11 09:00:00','2026-10-11 17:00:00',60,480,NULL,NULL,0,NULL,'absent',0,0,0,0,0,0,NULL,1,1,'2026-10-11 01:35:14','2026-10-11 04:06:59'),(13,1,1,NULL,'2026-10-13','2026-10-13 09:00:00','2026-10-13 17:00:00',60,480,NULL,NULL,0,NULL,'absent',0,0,0,0,0,0,NULL,1,1,'2026-10-13 05:43:15','2026-10-13 05:43:15'),(14,2,2,NULL,'2026-10-13','2026-10-13 09:00:00','2026-10-13 17:00:00',60,480,NULL,NULL,0,NULL,'absent',0,0,0,0,0,0,NULL,1,1,'2026-10-13 05:43:15','2026-10-13 05:43:15'),(15,3,NULL,NULL,'2026-10-25','2026-10-25 09:00:00','2026-10-25 17:00:00',10,480,'2026-10-25 09:01:00','2026-10-25 17:00:00',0,NULL,'completed',0,479,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(16,4,NULL,NULL,'2026-10-24','2026-10-24 09:00:00','2026-10-24 17:00:00',10,480,'2026-10-24 09:02:00','2026-10-24 17:00:00',0,NULL,'completed',0,478,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(17,5,NULL,NULL,'2026-10-23','2026-10-23 09:00:00','2026-10-23 17:00:00',10,480,'2026-10-23 09:03:00','2026-10-23 17:00:00',0,NULL,'completed',0,477,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(18,6,NULL,NULL,'2026-10-22','2026-10-22 09:00:00','2026-10-22 17:00:00',10,480,'2026-10-22 09:04:00','2026-10-22 17:00:00',0,NULL,'completed',0,476,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(19,7,NULL,NULL,'2026-10-21','2026-10-21 09:00:00','2026-10-21 17:00:00',10,480,'2026-10-21 09:05:00','2026-10-21 17:00:00',0,NULL,'completed',0,475,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(20,8,NULL,NULL,'2026-10-20','2026-10-20 09:00:00','2026-10-20 17:00:00',10,480,'2026-10-20 09:06:00','2026-10-20 17:00:00',0,NULL,'completed',0,474,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(21,9,NULL,NULL,'2026-10-19','2026-10-19 09:00:00','2026-10-19 17:00:00',10,480,'2026-10-19 09:07:00','2026-10-19 17:00:00',0,NULL,'completed',0,473,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(22,10,NULL,NULL,'2026-10-18','2026-10-18 09:00:00','2026-10-18 17:00:00',10,480,'2026-10-18 09:08:00','2026-10-18 17:00:00',0,NULL,'completed',0,472,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(23,11,NULL,NULL,'2026-10-17','2026-10-17 09:00:00','2026-10-17 17:00:00',10,480,'2026-10-17 09:09:00','2026-10-17 17:00:00',0,NULL,'completed',0,471,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(24,12,NULL,NULL,'2026-10-16','2026-10-16 09:00:00','2026-10-16 17:00:00',10,480,'2026-10-16 09:10:00','2026-10-16 17:00:00',0,NULL,'completed',0,470,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(25,13,NULL,NULL,'2026-10-15','2026-10-15 09:00:00','2026-10-15 17:00:00',10,480,'2026-10-15 09:11:00','2026-10-15 17:00:00',0,NULL,'completed',1,469,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(26,14,NULL,NULL,'2026-10-14','2026-10-14 09:00:00','2026-10-14 17:00:00',10,480,'2026-10-14 09:12:00','2026-10-14 17:00:00',0,NULL,'completed',2,468,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(27,15,NULL,NULL,'2026-10-13','2026-10-13 09:00:00','2026-10-13 17:00:00',10,480,'2026-10-13 09:13:00','2026-10-13 17:00:00',0,NULL,'completed',3,467,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(28,16,NULL,NULL,'2026-10-12','2026-10-12 09:00:00','2026-10-12 17:00:00',10,480,'2026-10-12 09:14:00','2026-10-12 17:00:00',0,NULL,'completed',4,466,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(29,17,NULL,NULL,'2026-10-11','2026-10-11 09:00:00','2026-10-11 17:00:00',10,480,'2026-10-11 09:15:00','2026-10-11 17:00:00',0,NULL,'completed',5,465,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(30,18,NULL,NULL,'2026-10-10','2026-10-10 09:00:00','2026-10-10 17:00:00',10,480,'2026-10-10 09:00:00','2026-10-10 17:00:00',0,NULL,'completed',0,480,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(31,19,NULL,NULL,'2026-10-09','2026-10-09 09:00:00','2026-10-09 17:00:00',10,480,'2026-10-09 09:01:00','2026-10-09 17:00:00',0,NULL,'completed',0,479,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(32,20,NULL,NULL,'2026-10-08','2026-10-08 09:00:00','2026-10-08 17:00:00',10,480,'2026-10-08 09:02:00','2026-10-08 17:00:00',0,NULL,'completed',0,478,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(33,21,NULL,NULL,'2026-10-07','2026-10-07 09:00:00','2026-10-07 17:00:00',10,480,'2026-10-07 09:03:00','2026-10-07 17:00:00',0,NULL,'completed',0,477,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(34,22,NULL,NULL,'2026-10-06','2026-10-06 09:00:00','2026-10-06 17:00:00',10,480,'2026-10-06 09:04:00','2026-10-06 17:00:00',0,NULL,'completed',0,476,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(35,23,NULL,NULL,'2026-10-05','2026-10-05 09:00:00','2026-10-05 17:00:00',10,480,'2026-10-05 09:05:00','2026-10-05 17:00:00',0,NULL,'completed',0,475,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(36,24,NULL,NULL,'2026-10-04','2026-10-04 09:00:00','2026-10-04 17:00:00',10,480,'2026-10-04 09:06:00','2026-10-04 17:00:00',0,NULL,'completed',0,474,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(37,25,NULL,NULL,'2026-10-03','2026-10-03 09:00:00','2026-10-03 17:00:00',10,480,'2026-10-03 09:07:00','2026-10-03 17:00:00',0,NULL,'completed',0,473,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(38,26,NULL,NULL,'2026-10-02','2026-10-02 09:00:00','2026-10-02 17:00:00',10,480,'2026-10-02 09:08:00','2026-10-02 17:00:00',0,NULL,'completed',0,472,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(39,27,NULL,NULL,'2026-10-01','2026-10-01 09:00:00','2026-10-01 17:00:00',10,480,'2026-10-01 09:09:00','2026-10-01 17:00:00',0,NULL,'completed',0,471,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(40,28,NULL,NULL,'2026-09-30','2026-09-30 09:00:00','2026-09-30 17:00:00',10,480,'2026-09-30 09:10:00','2026-09-30 17:00:00',0,NULL,'completed',0,470,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(41,29,NULL,NULL,'2026-09-29','2026-09-29 09:00:00','2026-09-29 17:00:00',10,480,'2026-09-29 09:11:00','2026-09-29 17:00:00',0,NULL,'completed',1,469,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(42,30,NULL,NULL,'2026-09-28','2026-09-28 09:00:00','2026-09-28 17:00:00',10,480,'2026-09-28 09:12:00','2026-09-28 17:00:00',0,NULL,'completed',2,468,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(43,31,NULL,NULL,'2026-09-27','2026-09-27 09:00:00','2026-09-27 17:00:00',10,480,'2026-09-27 09:13:00','2026-09-27 17:00:00',0,NULL,'completed',3,467,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(44,32,NULL,NULL,'2026-10-26','2026-10-26 09:00:00','2026-10-26 17:00:00',10,480,'2026-10-26 09:14:00','2026-10-26 17:00:00',0,NULL,'completed',4,466,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(45,33,NULL,NULL,'2026-10-25','2026-10-25 09:00:00','2026-10-25 17:00:00',10,480,'2026-10-25 09:15:00','2026-10-25 17:00:00',0,NULL,'completed',5,465,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(46,34,NULL,NULL,'2026-10-24','2026-10-24 09:00:00','2026-10-24 17:00:00',10,480,'2026-10-24 09:00:00','2026-10-24 17:00:00',0,NULL,'completed',0,480,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(47,35,NULL,NULL,'2026-10-23','2026-10-23 09:00:00','2026-10-23 17:00:00',10,480,'2026-10-23 09:01:00','2026-10-23 17:00:00',0,NULL,'completed',0,479,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(48,36,NULL,NULL,'2026-10-22','2026-10-22 09:00:00','2026-10-22 17:00:00',10,480,'2026-10-22 09:02:00','2026-10-22 17:00:00',0,NULL,'completed',0,478,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(49,37,NULL,NULL,'2026-10-21','2026-10-21 09:00:00','2026-10-21 17:00:00',10,480,'2026-10-21 09:03:00','2026-10-21 17:00:00',0,NULL,'completed',0,477,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(50,38,NULL,NULL,'2026-10-20','2026-10-20 09:00:00','2026-10-20 17:00:00',10,480,'2026-10-20 09:04:00','2026-10-20 17:00:00',0,NULL,'completed',0,476,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(51,39,NULL,NULL,'2026-10-19','2026-10-19 09:00:00','2026-10-19 17:00:00',10,480,'2026-10-19 09:05:00','2026-10-19 17:00:00',0,NULL,'completed',0,475,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(52,40,NULL,NULL,'2026-10-18','2026-10-18 09:00:00','2026-10-18 17:00:00',10,480,'2026-10-18 09:06:00','2026-10-18 17:00:00',0,NULL,'completed',0,474,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(53,41,NULL,NULL,'2026-10-17','2026-10-17 09:00:00','2026-10-17 17:00:00',10,480,'2026-10-17 09:07:00','2026-10-17 17:00:00',0,NULL,'completed',0,473,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(54,42,NULL,NULL,'2026-10-16','2026-10-16 09:00:00','2026-10-16 17:00:00',10,480,'2026-10-16 09:08:00','2026-10-16 17:00:00',0,NULL,'completed',0,472,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(55,43,NULL,NULL,'2026-10-15','2026-10-15 09:00:00','2026-10-15 17:00:00',10,480,'2026-10-15 09:09:00','2026-10-15 17:00:00',0,NULL,'completed',0,471,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(56,44,NULL,NULL,'2026-10-14','2026-10-14 09:00:00','2026-10-14 17:00:00',10,480,'2026-10-14 09:10:00','2026-10-14 17:00:00',0,NULL,'completed',0,470,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(57,45,NULL,NULL,'2026-10-13','2026-10-13 09:00:00','2026-10-13 17:00:00',10,480,'2026-10-13 09:11:00','2026-10-13 17:00:00',0,NULL,'completed',1,469,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(58,46,NULL,NULL,'2026-10-12','2026-10-12 09:00:00','2026-10-12 17:00:00',10,480,'2026-10-12 09:12:00','2026-10-12 17:00:00',0,NULL,'completed',2,468,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(59,47,NULL,NULL,'2026-10-11','2026-10-11 09:00:00','2026-10-11 17:00:00',10,480,'2026-10-11 09:13:00','2026-10-11 17:00:00',0,NULL,'completed',3,467,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(60,48,NULL,NULL,'2026-10-10','2026-10-10 09:00:00','2026-10-10 17:00:00',10,480,'2026-10-10 09:14:00','2026-10-10 17:00:00',0,NULL,'completed',4,466,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(61,49,NULL,NULL,'2026-10-09','2026-10-09 09:00:00','2026-10-09 17:00:00',10,480,'2026-10-09 09:15:00','2026-10-09 17:00:00',0,NULL,'completed',5,465,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(62,50,NULL,NULL,'2026-10-08','2026-10-08 09:00:00','2026-10-08 17:00:00',10,480,'2026-10-08 09:00:00','2026-10-08 17:00:00',0,NULL,'completed',0,480,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(63,51,NULL,NULL,'2026-10-07','2026-10-07 09:00:00','2026-10-07 17:00:00',10,480,'2026-10-07 09:01:00','2026-10-07 17:00:00',0,NULL,'completed',0,479,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(64,52,NULL,NULL,'2026-10-06','2026-10-06 09:00:00','2026-10-06 17:00:00',10,480,'2026-10-06 09:02:00','2026-10-06 17:00:00',0,NULL,'completed',0,478,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(65,53,NULL,NULL,'2026-10-05','2026-10-05 09:00:00','2026-10-05 17:00:00',10,480,'2026-10-05 09:03:00','2026-10-05 17:00:00',0,NULL,'completed',0,477,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(66,54,NULL,NULL,'2026-10-04','2026-10-04 09:00:00','2026-10-04 17:00:00',10,480,'2026-10-04 09:04:00','2026-10-04 17:00:00',0,NULL,'completed',0,476,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(67,55,NULL,NULL,'2026-10-03','2026-10-03 09:00:00','2026-10-03 17:00:00',10,480,'2026-10-03 09:05:00','2026-10-03 17:00:00',0,NULL,'completed',0,475,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(68,56,NULL,NULL,'2026-10-02','2026-10-02 09:00:00','2026-10-02 17:00:00',10,480,'2026-10-02 09:06:00','2026-10-02 17:00:00',0,NULL,'completed',0,474,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(69,57,NULL,NULL,'2026-10-01','2026-10-01 09:00:00','2026-10-01 17:00:00',10,480,'2026-10-01 09:07:00','2026-10-01 17:00:00',0,NULL,'completed',0,473,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(70,58,NULL,NULL,'2026-09-30','2026-09-30 09:00:00','2026-09-30 17:00:00',10,480,'2026-09-30 09:08:00','2026-09-30 17:00:00',0,NULL,'completed',0,472,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(71,59,NULL,NULL,'2026-09-29','2026-09-29 09:00:00','2026-09-29 17:00:00',10,480,'2026-09-29 09:09:00','2026-09-29 17:00:00',0,NULL,'completed',0,471,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(72,60,NULL,NULL,'2026-09-28','2026-09-28 09:00:00','2026-09-28 17:00:00',10,480,'2026-09-28 09:10:00','2026-09-28 17:00:00',0,NULL,'completed',0,470,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(73,61,NULL,NULL,'2026-09-27','2026-09-27 09:00:00','2026-09-27 17:00:00',10,480,'2026-09-27 09:11:00','2026-09-27 17:00:00',0,NULL,'completed',1,469,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(74,62,NULL,NULL,'2026-10-26','2026-10-26 09:00:00','2026-10-26 17:00:00',10,480,'2026-10-26 09:12:00','2026-10-26 17:00:00',0,NULL,'completed',2,468,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(75,63,NULL,NULL,'2026-10-25','2026-10-25 09:00:00','2026-10-25 17:00:00',10,480,'2026-10-25 09:13:00','2026-10-25 17:00:00',0,NULL,'completed',3,467,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(76,64,NULL,NULL,'2026-10-24','2026-10-24 09:00:00','2026-10-24 17:00:00',10,480,'2026-10-24 09:14:00','2026-10-24 17:00:00',0,NULL,'completed',4,466,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(77,65,NULL,NULL,'2026-10-23','2026-10-23 09:00:00','2026-10-23 17:00:00',10,480,'2026-10-23 09:15:00','2026-10-23 17:00:00',0,NULL,'completed',5,465,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(78,66,NULL,NULL,'2026-10-22','2026-10-22 09:00:00','2026-10-22 17:00:00',10,480,'2026-10-22 09:00:00','2026-10-22 17:00:00',0,NULL,'completed',0,480,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(79,67,NULL,NULL,'2026-10-21','2026-10-21 09:00:00','2026-10-21 17:00:00',10,480,'2026-10-21 09:01:00','2026-10-21 17:00:00',0,NULL,'completed',0,479,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(80,68,NULL,NULL,'2026-10-20','2026-10-20 09:00:00','2026-10-20 17:00:00',10,480,'2026-10-20 09:02:00','2026-10-20 17:00:00',0,NULL,'completed',0,478,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(81,69,NULL,NULL,'2026-10-19','2026-10-19 09:00:00','2026-10-19 17:00:00',10,480,'2026-10-19 09:03:00','2026-10-19 17:00:00',0,NULL,'completed',0,477,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(82,70,NULL,NULL,'2026-10-18','2026-10-18 09:00:00','2026-10-18 17:00:00',10,480,'2026-10-18 09:04:00','2026-10-18 17:00:00',0,NULL,'completed',0,476,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(83,71,NULL,NULL,'2026-10-17','2026-10-17 09:00:00','2026-10-17 17:00:00',10,480,'2026-10-17 09:05:00','2026-10-17 17:00:00',0,NULL,'completed',0,475,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(84,72,NULL,NULL,'2026-10-16','2026-10-16 09:00:00','2026-10-16 17:00:00',10,480,'2026-10-16 09:06:00','2026-10-16 17:00:00',0,NULL,'completed',0,474,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(85,73,NULL,NULL,'2026-10-15','2026-10-15 09:00:00','2026-10-15 17:00:00',10,480,'2026-10-15 09:07:00','2026-10-15 17:00:00',0,NULL,'completed',0,473,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(86,74,NULL,NULL,'2026-10-14','2026-10-14 09:00:00','2026-10-14 17:00:00',10,480,'2026-10-14 09:08:00','2026-10-14 17:00:00',0,NULL,'completed',0,472,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(87,75,NULL,NULL,'2026-10-13','2026-10-13 09:00:00','2026-10-13 17:00:00',10,480,'2026-10-13 09:09:00','2026-10-13 17:00:00',0,NULL,'completed',0,471,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(88,76,NULL,NULL,'2026-10-12','2026-10-12 09:00:00','2026-10-12 17:00:00',10,480,'2026-10-12 09:10:00','2026-10-12 17:00:00',0,NULL,'completed',0,470,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(89,77,NULL,NULL,'2026-10-11','2026-10-11 09:00:00','2026-10-11 17:00:00',10,480,'2026-10-11 09:11:00','2026-10-11 17:00:00',0,NULL,'completed',1,469,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(90,78,NULL,NULL,'2026-10-10','2026-10-10 09:00:00','2026-10-10 17:00:00',10,480,'2026-10-10 09:12:00','2026-10-10 17:00:00',0,NULL,'completed',2,468,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(91,79,NULL,NULL,'2026-10-09','2026-10-09 09:00:00','2026-10-09 17:00:00',10,480,'2026-10-09 09:13:00','2026-10-09 17:00:00',0,NULL,'completed',3,467,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(92,80,NULL,NULL,'2026-10-08','2026-10-08 09:00:00','2026-10-08 17:00:00',10,480,'2026-10-08 09:14:00','2026-10-08 17:00:00',0,NULL,'completed',4,466,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(93,81,NULL,NULL,'2026-10-07','2026-10-07 09:00:00','2026-10-07 17:00:00',10,480,'2026-10-07 09:15:00','2026-10-07 17:00:00',0,NULL,'completed',5,465,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(94,82,NULL,NULL,'2026-10-06','2026-10-06 09:00:00','2026-10-06 17:00:00',10,480,'2026-10-06 09:00:00','2026-10-06 17:00:00',0,NULL,'completed',0,480,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(95,83,NULL,NULL,'2026-10-05','2026-10-05 09:00:00','2026-10-05 17:00:00',10,480,'2026-10-05 09:01:00','2026-10-05 17:00:00',0,NULL,'completed',0,479,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(96,84,NULL,NULL,'2026-10-04','2026-10-04 09:00:00','2026-10-04 17:00:00',10,480,'2026-10-04 09:02:00','2026-10-04 17:00:00',0,NULL,'completed',0,478,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(97,85,NULL,NULL,'2026-10-03','2026-10-03 09:00:00','2026-10-03 17:00:00',10,480,'2026-10-03 09:03:00','2026-10-03 17:00:00',0,NULL,'completed',0,477,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(98,86,NULL,NULL,'2026-10-02','2026-10-02 09:00:00','2026-10-02 17:00:00',10,480,'2026-10-02 09:04:00','2026-10-02 17:00:00',0,NULL,'completed',0,476,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(99,87,NULL,NULL,'2026-10-01','2026-10-01 09:00:00','2026-10-01 17:00:00',10,480,'2026-10-01 09:05:00','2026-10-01 17:00:00',0,NULL,'completed',0,475,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(100,88,NULL,NULL,'2026-09-30','2026-09-30 09:00:00','2026-09-30 17:00:00',10,480,'2026-09-30 09:06:00','2026-09-30 17:00:00',0,NULL,'completed',0,474,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(101,89,NULL,NULL,'2026-09-29','2026-09-29 09:00:00','2026-09-29 17:00:00',10,480,'2026-09-29 09:07:00','2026-09-29 17:00:00',0,NULL,'completed',0,473,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(102,90,NULL,NULL,'2026-09-28','2026-09-28 09:00:00','2026-09-28 17:00:00',10,480,'2026-09-28 09:08:00','2026-09-28 17:00:00',0,NULL,'completed',0,472,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(103,91,NULL,NULL,'2026-09-27','2026-09-27 09:00:00','2026-09-27 17:00:00',10,480,'2026-09-27 09:09:00','2026-09-27 17:00:00',0,NULL,'completed',0,471,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(104,92,NULL,NULL,'2026-10-26','2026-10-26 09:00:00','2026-10-26 17:00:00',10,480,'2026-10-26 09:10:00','2026-10-26 17:00:00',0,NULL,'completed',0,470,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(105,93,NULL,NULL,'2026-10-25','2026-10-25 09:00:00','2026-10-25 17:00:00',10,480,'2026-10-25 09:11:00','2026-10-25 17:00:00',0,NULL,'completed',1,469,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(106,94,NULL,NULL,'2026-10-24','2026-10-24 09:00:00','2026-10-24 17:00:00',10,480,'2026-10-24 09:12:00','2026-10-24 17:00:00',0,NULL,'completed',2,468,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(107,95,NULL,NULL,'2026-10-23','2026-10-23 09:00:00','2026-10-23 17:00:00',10,480,'2026-10-23 09:13:00','2026-10-23 17:00:00',0,NULL,'completed',3,467,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(108,96,NULL,NULL,'2026-10-22','2026-10-22 09:00:00','2026-10-22 17:00:00',10,480,'2026-10-22 09:14:00','2026-10-22 17:00:00',0,NULL,'completed',4,466,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(109,97,NULL,NULL,'2026-10-21','2026-10-21 09:00:00','2026-10-21 17:00:00',10,480,'2026-10-21 09:15:00','2026-10-21 17:00:00',0,NULL,'completed',5,465,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(110,98,NULL,NULL,'2026-10-20','2026-10-20 09:00:00','2026-10-20 17:00:00',10,480,'2026-10-20 09:00:00','2026-10-20 17:00:00',0,NULL,'completed',0,480,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(111,99,NULL,NULL,'2026-10-19','2026-10-19 09:00:00','2026-10-19 17:00:00',10,480,'2026-10-19 09:01:00','2026-10-19 17:00:00',0,NULL,'completed',0,479,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(112,100,NULL,NULL,'2026-10-18','2026-10-18 09:00:00','2026-10-18 17:00:00',10,480,'2026-10-18 09:02:00','2026-10-18 17:00:00',0,NULL,'completed',0,478,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(113,101,NULL,NULL,'2026-10-17','2026-10-17 09:00:00','2026-10-17 17:00:00',10,480,'2026-10-17 09:03:00','2026-10-17 17:00:00',0,NULL,'completed',0,477,30,0,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(114,102,NULL,NULL,'2026-10-16','2026-10-16 09:00:00','2026-10-16 17:00:00',10,480,'2026-10-16 09:04:00','2026-10-16 17:00:00',0,NULL,'completed',0,476,30,30,0,0,'DEMO DATA',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58');
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `module` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_module_action_index` (`module`,`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'menu_categories','created','Created menu category: Burgers','App\\Models\\MenuCategory',1,NULL,'{\"category_name\":\"Burgers\",\"is_available\":true,\"updated_at\":\"2026-10-07T00:57:19.000000Z\",\"created_at\":\"2026-10-07T00:57:19.000000Z\",\"id\":1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 18:57:19','2026-10-06 18:57:19'),(2,1,'menu_items','created','Created menu item: Classic Test Burger','App\\Models\\MenuItem',1,NULL,'{\"menu_category_id\":1,\"menu_name\":\"Classic Test Burger\",\"item_type\":\"regular\",\"price\":\"250.00\",\"ingredients\":\"Beef patty, lettuce, cheese, sauce\",\"description\":\"Local development test item\",\"preparation_time\":null,\"is_available\":true,\"is_featured\":false,\"image_path\":null,\"updated_at\":\"2026-10-07T01:00:26.000000Z\",\"created_at\":\"2026-10-07T01:00:26.000000Z\",\"id\":1,\"image_url\":null,\"item_type_label\":\"Regular\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 19:00:26','2026-10-06 19:00:26'),(3,1,'orders','created','Created order #1','App\\Models\\Order',1,NULL,'{\"id\":1,\"order_number\":\"ORD-20261007-HNDBAG\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"bkash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":\"POS Guest Count: 1\",\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T01:03:48.000000Z\",\"deleted_at\":null,\"customer\":null,\"reservation\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":1,\"order_id\":1,\"order_kitchen_batch_id\":1,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Test Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T01:03:48.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":1,\"order_id\":1,\"amount\":\"250.00\",\"payment_method\":\"bkash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T01:03:48.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T00:53:02.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T00:53:02.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":1,\"order_id\":1,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-07T01:03:48.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T01:03:48.000000Z\"},\"kitchen_batches\":[{\"id\":1,\"order_id\":1,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-07T01:03:48.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T01:03:48.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T00:53:02.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T00:53:02.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 19:03:48','2026-10-06 19:03:48'),(4,1,'employees','created','Created employee: ','App\\Models\\Employee',1,NULL,'{\"id\":1,\"user_id\":2,\"phone\":\"0138776655442\",\"profile_image\":null,\"joining_date\":\"2026-10-07T00:00:00.000000Z\",\"hourly_rate\":\"100.00\",\"current_status\":\"none\",\"status_updated_at\":\"2026-10-07T01:11:32.000000Z\",\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-10-07T01:11:32.000000Z\",\"updated_at\":\"2026-10-07T01:11:32.000000Z\",\"deleted_at\":null,\"user\":{\"id\":2,\"role_id\":3,\"name\":\"Chef\",\"username\":\"chef.sopno\",\"email\":\"chef.sopno@example.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":null,\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-07T01:11:32.000000Z\",\"updated_at\":\"2026-10-07T01:11:32.000000Z\",\"deleted_at\":null,\"role\":{\"id\":3,\"name\":\"chef\",\"display_name\":\"Chef\",\"description\":\"Kitchen and food preparation management access.\",\"is_active\":true,\"created_at\":\"2026-10-06T19:10:41.000000Z\",\"updated_at\":\"2026-10-06T19:10:41.000000Z\"}},\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T00:53:02.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T00:53:02.000000Z\",\"deleted_at\":null},\"updater\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T00:53:02.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T00:53:02.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 19:11:32','2026-10-06 19:11:32'),(5,1,'employees','updated','Updated employee: ','App\\Models\\Employee',1,'{\"id\":1,\"user_id\":2,\"phone\":\"0138776655442\",\"profile_image\":null,\"joining_date\":\"2026-10-07T00:00:00.000000Z\",\"hourly_rate\":\"100.00\",\"current_status\":\"none\",\"status_updated_at\":\"2026-10-07T01:11:32.000000Z\",\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-10-07T01:11:32.000000Z\",\"updated_at\":\"2026-10-07T01:11:32.000000Z\",\"deleted_at\":null,\"user\":{\"id\":2,\"role_id\":3,\"name\":\"Chef\",\"username\":\"chef.sopno\",\"email\":\"chef.sopno@example.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":null,\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-07T01:11:32.000000Z\",\"updated_at\":\"2026-10-07T01:11:32.000000Z\",\"deleted_at\":null,\"role\":{\"id\":3,\"name\":\"chef\",\"display_name\":\"Chef\",\"description\":\"Kitchen and food preparation management access.\",\"is_active\":true,\"created_at\":\"2026-10-06T19:10:41.000000Z\",\"updated_at\":\"2026-10-06T19:10:41.000000Z\"}},\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T01:15:32.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T01:15:32.000000Z\",\"deleted_at\":null},\"updater\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T01:15:32.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T01:15:32.000000Z\",\"deleted_at\":null}}','{\"id\":1,\"user_id\":2,\"phone\":\"0138776655442\",\"profile_image\":null,\"joining_date\":\"2026-10-07T00:00:00.000000Z\",\"hourly_rate\":\"100.00\",\"current_status\":\"none\",\"status_updated_at\":\"2026-10-07T01:11:32.000000Z\",\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-10-07T01:11:32.000000Z\",\"updated_at\":\"2026-10-07T01:11:32.000000Z\",\"deleted_at\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 19:16:05','2026-10-06 19:16:05'),(6,1,'orders','status_changed','Changed order status #1','App\\Models\\Order',1,'{\"status\":\"ready\"}','{\"status\":\"served\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 21:02:37','2026-10-06 21:02:37'),(7,1,'orders','completed','Completed order #1','App\\Models\\Order',1,NULL,'{\"id\":1,\"order_number\":\"ORD-20261007-HNDBAG\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"status\":\"completed\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"bkash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":\"POS Guest Count: 1\",\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-07T01:03:48.000000Z\",\"preparing_at\":\"2026-10-07T02:19:48.000000Z\",\"ready_at\":\"2026-10-07T02:20:01.000000Z\",\"served_at\":\"2026-10-07T03:02:37.000000Z\",\"completed_at\":\"2026-10-07T03:02:48.000000Z\",\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T03:02:48.000000Z\",\"deleted_at\":null,\"reservation\":null,\"customer\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":1,\"order_id\":1,\"order_kitchen_batch_id\":1,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Test Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T03:02:37.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":1,\"order_id\":1,\"amount\":\"250.00\",\"payment_method\":\"bkash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T01:03:48.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T01:15:32.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T01:15:32.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":1,\"order_id\":1,\"order_kitchen_batch_id\":1,\"order_number\":\"ORD-20261007-HNDBAG\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-07T02:19:48.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-07T02:19:48.000000Z\",\"updated_at\":\"2026-10-07T02:19:48.000000Z\"}],\"latest_kitchen_batch\":{\"id\":1,\"order_id\":1,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-07T01:03:48.000000Z\",\"preparing_at\":\"2026-10-07T02:19:48.000000Z\",\"ready_at\":\"2026-10-07T02:20:01.000000Z\",\"served_at\":\"2026-10-07T03:02:37.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T03:02:37.000000Z\"},\"kitchen_batches\":[{\"id\":1,\"order_id\":1,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-07T01:03:48.000000Z\",\"preparing_at\":\"2026-10-07T02:19:48.000000Z\",\"ready_at\":\"2026-10-07T02:20:01.000000Z\",\"served_at\":\"2026-10-07T03:02:37.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-07T01:03:48.000000Z\",\"updated_at\":\"2026-10-07T03:02:37.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T01:15:32.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T01:15:32.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 21:02:48','2026-10-06 21:02:48'),(8,1,'employees','created','Created employee: ','App\\Models\\Employee',2,NULL,'{\"id\":2,\"user_id\":3,\"phone\":\"0138776655443\",\"profile_image\":null,\"joining_date\":\"2026-10-07T00:00:00.000000Z\",\"hourly_rate\":\"60.00\",\"current_status\":\"none\",\"status_updated_at\":\"2026-10-07T03:16:16.000000Z\",\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-10-07T03:16:16.000000Z\",\"updated_at\":\"2026-10-07T03:16:16.000000Z\",\"deleted_at\":null,\"user\":{\"id\":3,\"role_id\":4,\"name\":\"Waiter\",\"username\":\"waiter12\",\"email\":\"waiter@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":null,\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-07T03:16:16.000000Z\",\"updated_at\":\"2026-10-07T03:16:16.000000Z\",\"deleted_at\":null,\"role\":{\"id\":4,\"name\":\"waiter\",\"display_name\":\"Waiter\",\"description\":\"Order taking and front desk operational access.\",\"is_active\":true,\"created_at\":\"2026-10-06T19:10:41.000000Z\",\"updated_at\":\"2026-10-06T19:10:41.000000Z\"}},\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T01:15:32.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T01:15:32.000000Z\",\"deleted_at\":null},\"updater\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T01:15:32.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T01:15:32.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 21:16:16','2026-10-06 21:16:16'),(9,1,'orders','created','Created order #2','App\\Models\\Order',2,NULL,'{\"id\":2,\"order_number\":\"ORD-20261007-F8NBBO\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":1,\"reservation_id\":null,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"250.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":1,\"name\":\"Tasnima Akther Tisha\",\"phone\":\"+1 (844) 536-1838\",\"email\":\"shraboniakter554@gmail.com\",\"last_visit_at\":null,\"total_orders\":0,\"total_spent\":\"0.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\",\"deleted_at\":null},\"reservation\":null,\"primary_table\":{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":2,\"restaurant_table_id\":1,\"is_primary\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\"}}],\"items\":[{\"id\":2,\"order_id\":2,\"order_kitchen_batch_id\":2,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Test Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\",\"addons\":[]}],\"payments\":[],\"latest_kitchen_batch\":{\"id\":2,\"order_id\":2,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\"},\"kitchen_batches\":[{\"id\":2,\"order_id\":2,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T03:49:09.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T03:49:09.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 22:19:01','2026-10-06 22:19:01'),(10,1,'expense_categories','created','Created expense category: Inventory','App\\Models\\ExpenseCategory',1,NULL,'{\"id\":1,\"name\":\"Inventory\",\"description\":null,\"is_active\":true,\"ledger_account_id\":null,\"created_at\":\"2026-10-07T04:55:15.000000Z\",\"updated_at\":\"2026-10-07T04:55:15.000000Z\",\"deleted_at\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 22:55:15','2026-10-06 22:55:15'),(11,1,'expenses','created','Created expense: 1','App\\Models\\Expense',1,NULL,'{\"expense_category_id\":1,\"expense_date\":\"2026-10-07T00:00:00.000000Z\",\"amount\":\"10.00\",\"payment_method\":\"cash\",\"notes\":null,\"created_by\":1,\"updated_by\":1,\"updated_at\":\"2026-10-07T04:55:39.000000Z\",\"created_at\":\"2026-10-07T04:55:39.000000Z\",\"id\":1,\"category\":{\"id\":1,\"name\":\"Inventory\",\"description\":null,\"is_active\":true,\"ledger_account_id\":37,\"created_at\":\"2026-10-07T04:55:15.000000Z\",\"updated_at\":\"2026-10-07T04:55:15.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 22:55:39','2026-10-06 22:55:39'),(12,1,'orders','status_changed','Changed order status #2','App\\Models\\Order',2,'{\"status\":\"ready\"}','{\"status\":\"served\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:24:07','2026-10-06 23:24:07'),(13,1,'orders','updated','Updated order #2','App\\Models\\Order',2,'{\"id\":2,\"order_number\":\"ORD-20261007-F8NBBO\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":1,\"reservation_id\":null,\"status\":\"served\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"250.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":\"2026-10-07T05:11:38.000000Z\",\"ready_at\":\"2026-10-07T05:23:53.000000Z\",\"served_at\":\"2026-10-07T05:24:07.000000Z\",\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:24:07.000000Z\",\"deleted_at\":null}','{\"id\":2,\"order_number\":\"ORD-20261007-F8NBBO\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":1,\"reservation_id\":null,\"status\":\"served\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":\"2026-10-07T05:11:38.000000Z\",\"ready_at\":\"2026-10-07T05:23:53.000000Z\",\"served_at\":\"2026-10-07T05:24:07.000000Z\",\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:26:02.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":1,\"name\":\"Tasnima Akther Tisha\",\"phone\":\"+1 (844) 536-1838\",\"email\":\"shraboniakter554@gmail.com\",\"last_visit_at\":null,\"total_orders\":0,\"total_spent\":\"0.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\",\"deleted_at\":null},\"reservation\":null,\"primary_table\":{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-08T00:31:00.000000Z\",\"reservation_end_at\":\"2026-10-08T13:32:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T04:30:14.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-08T00:31:00.000000Z\",\"reservation_end_at\":\"2026-10-08T13:32:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T04:30:14.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":2,\"restaurant_table_id\":1,\"is_primary\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\"}}],\"items\":[{\"id\":2,\"order_id\":2,\"order_kitchen_batch_id\":2,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Test Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:24:07.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":2,\"order_id\":2,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Additional payment received during served order update.\",\"received_by\":1,\"created_at\":\"2026-10-07T05:26:02.000000Z\",\"updated_at\":\"2026-10-07T05:26:02.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T04:31:37.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T04:31:37.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":2,\"order_id\":2,\"order_kitchen_batch_id\":2,\"order_number\":\"ORD-20261007-F8NBBO\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-07T05:11:38.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-07T05:11:38.000000Z\",\"updated_at\":\"2026-10-07T05:11:38.000000Z\"}],\"latest_kitchen_batch\":{\"id\":2,\"order_id\":2,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":\"2026-10-07T05:11:38.000000Z\",\"ready_at\":\"2026-10-07T05:23:53.000000Z\",\"served_at\":\"2026-10-07T05:24:07.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:24:07.000000Z\"},\"kitchen_batches\":[{\"id\":2,\"order_id\":2,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":\"2026-10-07T05:11:38.000000Z\",\"ready_at\":\"2026-10-07T05:23:53.000000Z\",\"served_at\":\"2026-10-07T05:24:07.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:24:07.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T04:31:37.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T04:31:37.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:26:02','2026-10-06 23:26:02'),(14,1,'orders','completed','Completed order #2','App\\Models\\Order',2,NULL,'{\"id\":2,\"order_number\":\"ORD-20261007-F8NBBO\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":1,\"reservation_id\":null,\"status\":\"completed\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":\"2026-10-07T05:11:38.000000Z\",\"ready_at\":\"2026-10-07T05:23:53.000000Z\",\"served_at\":\"2026-10-07T05:24:07.000000Z\",\"completed_at\":\"2026-10-07T05:26:02.000000Z\",\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":true,\"customer_spend_recorded_at\":\"2026-10-07T05:26:03.000000Z\",\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:26:03.000000Z\",\"deleted_at\":null,\"reservation\":null,\"customer\":{\"id\":1,\"name\":\"Tasnima Akther Tisha\",\"phone\":\"+1 (844) 536-1838\",\"email\":\"shraboniakter554@gmail.com\",\"last_visit_at\":\"2026-10-07T05:26:03.000000Z\",\"total_orders\":0,\"total_spent\":\"250.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:26:03.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T05:26:03.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T05:26:03.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":2,\"restaurant_table_id\":1,\"is_primary\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T04:19:01.000000Z\"}}],\"items\":[{\"id\":2,\"order_id\":2,\"order_kitchen_batch_id\":2,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Test Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:24:07.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":2,\"order_id\":2,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Additional payment received during served order update.\",\"received_by\":1,\"created_at\":\"2026-10-07T05:26:02.000000Z\",\"updated_at\":\"2026-10-07T05:26:02.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T04:31:37.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T04:31:37.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":2,\"order_id\":2,\"order_kitchen_batch_id\":2,\"order_number\":\"ORD-20261007-F8NBBO\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-07T05:11:38.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-07T05:11:38.000000Z\",\"updated_at\":\"2026-10-07T05:11:38.000000Z\"}],\"latest_kitchen_batch\":{\"id\":2,\"order_id\":2,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":\"2026-10-07T05:11:38.000000Z\",\"ready_at\":\"2026-10-07T05:23:53.000000Z\",\"served_at\":\"2026-10-07T05:24:07.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:24:07.000000Z\"},\"kitchen_batches\":[{\"id\":2,\"order_id\":2,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-07T04:19:01.000000Z\",\"preparing_at\":\"2026-10-07T05:11:38.000000Z\",\"ready_at\":\"2026-10-07T05:23:53.000000Z\",\"served_at\":\"2026-10-07T05:24:07.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:24:07.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T04:31:37.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T04:31:37.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:26:03','2026-10-06 23:26:03'),(15,1,'orders','created','Created order #3','App\\Models\\Order',3,NULL,'{\"id\":3,\"order_number\":\"ORD-20261007-JLONTH\",\"customer_id\":3,\"customer_name\":\"Tasnima\",\"customer_phone\":\"+1 (646) 633-6464\",\"customer_email\":\"shraboniakter@gmail.com\",\"restaurant_table_id\":1,\"reservation_id\":null,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"250.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":3,\"name\":\"Tasnima\",\"phone\":\"+1 (646) 633-6464\",\"email\":\"shraboniakter@gmail.com\",\"last_visit_at\":null,\"total_orders\":0,\"total_spent\":\"0.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\",\"deleted_at\":null},\"reservation\":null,\"primary_table\":{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":3,\"restaurant_table_id\":1,\"is_primary\":1,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\"}}],\"items\":[{\"id\":3,\"order_id\":3,\"order_kitchen_batch_id\":3,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Test Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\",\"addons\":[]}],\"payments\":[],\"latest_kitchen_batch\":{\"id\":3,\"order_id\":3,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-07T05:28:12.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\"},\"kitchen_batches\":[{\"id\":3,\"order_id\":3,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-07T05:28:12.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T04:31:37.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T04:31:37.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:28:12','2026-10-06 23:28:12'),(16,1,'menu_categories','created','Created menu category: Coffee','App\\Models\\MenuCategory',2,NULL,'{\"category_name\":\"Coffee\",\"is_available\":true,\"updated_at\":\"2026-10-07T05:32:14.000000Z\",\"created_at\":\"2026-10-07T05:32:14.000000Z\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:32:14','2026-10-06 23:32:14'),(17,1,'menu_categories','updated','Updated menu category: Coffee','App\\Models\\MenuCategory',2,'{\"id\":2,\"category_name\":\"Coffee\",\"description\":null,\"is_available\":true,\"display_order\":0,\"created_at\":\"2026-10-07T05:32:14.000000Z\",\"updated_at\":\"2026-10-07T05:32:14.000000Z\",\"deleted_at\":null}','{\"id\":2,\"category_name\":\"Coffee\",\"description\":null,\"is_available\":true,\"display_order\":0,\"created_at\":\"2026-10-07T05:32:14.000000Z\",\"updated_at\":\"2026-10-07T05:32:14.000000Z\",\"deleted_at\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:35:46','2026-10-06 23:35:46'),(18,1,'menu_items','updated','Updated menu item: Classic Burger','App\\Models\\MenuItem',1,'{\"id\":1,\"menu_category_id\":1,\"menu_name\":\"Classic Test Burger\",\"item_type\":\"regular\",\"price\":\"250.00\",\"ingredients\":\"Beef patty, lettuce, cheese, sauce\",\"description\":\"Local development test item\",\"image_path\":null,\"preparation_time\":null,\"is_available\":true,\"is_featured\":false,\"created_at\":\"2026-10-07T01:00:26.000000Z\",\"updated_at\":\"2026-10-07T01:00:26.000000Z\",\"deleted_at\":null,\"image_url\":null,\"item_type_label\":\"Regular\"}','{\"id\":1,\"menu_category_id\":1,\"menu_name\":\"Classic Burger\",\"item_type\":\"regular\",\"price\":\"250.00\",\"ingredients\":\"Beef patty, lettuce, cheese, sauce\",\"description\":\"Local development test item\",\"image_path\":null,\"preparation_time\":null,\"is_available\":true,\"is_featured\":false,\"created_at\":\"2026-10-07T01:00:26.000000Z\",\"updated_at\":\"2026-10-07T05:36:48.000000Z\",\"deleted_at\":null,\"image_url\":null,\"item_type_label\":\"Regular\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:36:48','2026-10-06 23:36:48'),(19,1,'menu_variants','created','Created menu variant: Large','App\\Models\\MenuItemVariant',1,NULL,'{\"menu_item_id\":1,\"variant_name\":\"Large\",\"price\":\"350.00\",\"is_available\":true,\"updated_at\":\"2026-10-07T05:38:51.000000Z\",\"created_at\":\"2026-10-07T05:38:51.000000Z\",\"id\":1,\"menu_item\":{\"id\":1,\"menu_category_id\":1,\"menu_name\":\"Classic Burger\",\"item_type\":\"regular\",\"price\":\"250.00\",\"ingredients\":\"Beef patty, lettuce, cheese, sauce\",\"description\":\"Local development test item\",\"image_path\":null,\"preparation_time\":null,\"is_available\":true,\"is_featured\":false,\"created_at\":\"2026-10-07T01:00:26.000000Z\",\"updated_at\":\"2026-10-07T05:36:48.000000Z\",\"deleted_at\":null,\"image_url\":null,\"item_type_label\":\"Regular\"}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:38:51','2026-10-06 23:38:51'),(20,1,'menu_variants','created','Created menu variant: Small','App\\Models\\MenuItemVariant',2,NULL,'{\"menu_item_id\":1,\"variant_name\":\"Small\",\"price\":\"250.00\",\"is_available\":true,\"updated_at\":\"2026-10-07T05:39:13.000000Z\",\"created_at\":\"2026-10-07T05:39:13.000000Z\",\"id\":2,\"menu_item\":{\"id\":1,\"menu_category_id\":1,\"menu_name\":\"Classic Burger\",\"item_type\":\"regular\",\"price\":\"250.00\",\"ingredients\":\"Beef patty, lettuce, cheese, sauce\",\"description\":\"Local development test item\",\"image_path\":null,\"preparation_time\":null,\"is_available\":true,\"is_featured\":false,\"created_at\":\"2026-10-07T01:00:26.000000Z\",\"updated_at\":\"2026-10-07T05:36:48.000000Z\",\"deleted_at\":null,\"image_url\":null,\"item_type_label\":\"Regular\"}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:39:13','2026-10-06 23:39:13'),(21,1,'addons','created','Created add-on: Extra Cheese','App\\Models\\AddOn',1,NULL,'{\"add_on_name\":\"Extra Cheese\",\"price\":\"2.00\",\"description\":null,\"is_available\":true,\"updated_at\":\"2026-10-07T05:42:02.000000Z\",\"created_at\":\"2026-10-07T05:42:02.000000Z\",\"id\":1}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:42:02','2026-10-06 23:42:02'),(22,1,'addons','created','Created add-on: Tomato sauce','App\\Models\\AddOn',2,NULL,'{\"add_on_name\":\"Tomato sauce\",\"price\":\"0.00\",\"description\":null,\"is_available\":true,\"updated_at\":\"2026-10-07T05:42:24.000000Z\",\"created_at\":\"2026-10-07T05:42:24.000000Z\",\"id\":2}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:42:24','2026-10-06 23:42:24'),(23,1,'addons','updated','Updated add-on: Extra Cheese','App\\Models\\AddOn',1,'{\"id\":1,\"add_on_name\":\"Extra Cheese\",\"price\":\"2.00\",\"description\":null,\"is_available\":true,\"created_at\":\"2026-10-07T05:42:02.000000Z\",\"updated_at\":\"2026-10-07T05:42:02.000000Z\",\"deleted_at\":null}','{\"id\":1,\"add_on_name\":\"Extra Cheese\",\"price\":\"2.00\",\"description\":null,\"is_available\":true,\"created_at\":\"2026-10-07T05:42:02.000000Z\",\"updated_at\":\"2026-10-07T05:42:02.000000Z\",\"deleted_at\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:42:34','2026-10-06 23:42:34'),(24,1,'expenses','updated','Updated expense: 1','App\\Models\\Expense',1,'{\"id\":1,\"expense_category_id\":1,\"expense_date\":\"2026-10-07T00:00:00.000000Z\",\"amount\":\"10.00\",\"payment_method\":\"cash\",\"notes\":null,\"source_type\":null,\"source_id\":null,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-10-07T04:55:39.000000Z\",\"updated_at\":\"2026-10-07T04:55:39.000000Z\",\"deleted_at\":null}','{\"id\":1,\"expense_category_id\":1,\"expense_date\":\"2026-10-07T00:00:00.000000Z\",\"amount\":\"9.00\",\"payment_method\":\"cash\",\"notes\":null,\"source_type\":null,\"source_id\":null,\"created_by\":1,\"updated_by\":1,\"created_at\":\"2026-10-07T04:55:39.000000Z\",\"updated_at\":\"2026-10-07T05:46:27.000000Z\",\"deleted_at\":null}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-06 23:46:27','2026-10-06 23:46:27'),(25,1,'employees','blocked','Blocked employee: ','App\\Models\\Employee',2,'{\"is_active\":true}','{\"is_active\":false}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-07 00:49:23','2026-10-07 00:49:23'),(26,1,'employees','unblocked','Unblocked employee: ','App\\Models\\Employee',2,'{\"is_active\":false}','{\"is_active\":true}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-07 00:49:27','2026-10-07 00:49:27'),(27,1,'users','blocked','Blocked user: Waiter','App\\Models\\User',3,'{\"is_active\":true,\"status\":\"Active\"}','{\"is_active\":false,\"status\":\"Blocked\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-07 01:28:42','2026-10-07 01:28:42'),(28,1,'users','unblocked','Unblocked user: Waiter','App\\Models\\User',3,'{\"is_active\":false,\"status\":\"Blocked\"}','{\"is_active\":true,\"status\":\"Active\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-07 01:28:46','2026-10-07 01:28:46'),(29,1,'orders','cancelled','Cancelled order #3','App\\Models\\Order',3,'{\"id\":3,\"order_number\":\"ORD-20261007-JLONTH\",\"customer_id\":3,\"customer_name\":\"Tasnima\",\"customer_phone\":\"+1 (646) 633-6464\",\"customer_email\":\"shraboniakter@gmail.com\",\"restaurant_table_id\":1,\"reservation_id\":null,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"250.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-07T05:28:12.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T07:32:45.000000Z\",\"deleted_at\":null}','{\"id\":3,\"order_number\":\"ORD-20261007-JLONTH\",\"customer_id\":3,\"customer_name\":\"Tasnima\",\"customer_phone\":\"+1 (646) 633-6464\",\"customer_email\":\"shraboniakter@gmail.com\",\"restaurant_table_id\":1,\"reservation_id\":null,\"status\":\"canceled\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"0.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-07T05:28:12.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":\"2026-10-07T08:23:19.000000Z\",\"cancellation_reason\":\"Wrong order\",\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T08:23:19.000000Z\",\"deleted_at\":null,\"reservation\":null,\"customer\":{\"id\":3,\"name\":\"Tasnima\",\"phone\":\"+1 (646) 633-6464\",\"email\":\"shraboniakter@gmail.com\",\"last_visit_at\":null,\"total_orders\":0,\"total_spent\":\"0.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T08:23:19.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-07T08:23:19.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":3,\"restaurant_table_id\":1,\"is_primary\":1,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T05:28:12.000000Z\"}}],\"items\":[{\"id\":3,\"order_id\":3,\"order_kitchen_batch_id\":3,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Test Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"canceled\",\"kitchen_note\":null,\"created_at\":\"2026-10-07T05:28:12.000000Z\",\"updated_at\":\"2026-10-07T08:23:19.000000Z\",\"addons\":[]}],\"payments\":[],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-07T04:31:37.000000Z\",\"last_logout_at\":null,\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-07T04:31:37.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-07 02:23:19','2026-10-07 02:23:19'),(30,1,'orders','created','Created order #4','App\\Models\\Order',4,NULL,'{\"id\":4,\"order_number\":\"ORD-20261008-WGV5O2\",\"customer_id\":2,\"customer_name\":\"Tisha\",\"customer_phone\":\"07364466764\",\"customer_email\":null,\"restaurant_table_id\":1,\"reservation_id\":2,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"250.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":2,\"name\":\"Tisha\",\"phone\":\"07364466764\",\"email\":null,\"last_visit_at\":null,\"total_orders\":0,\"total_spent\":\"0.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:30:14.000000Z\",\"updated_at\":\"2026-10-07T04:30:14.000000Z\",\"deleted_at\":null},\"reservation\":{\"id\":2,\"reservation_no\":\"RES-20261007-J9QK7P3O\",\"customer_id\":2,\"reservation_date\":\"2026-10-08T00:00:00.000000Z\",\"start_time\":\"2026-10-08T00:31:00.000000Z\",\"end_time\":\"2026-10-08T13:32:00.000000Z\",\"guest_count\":1,\"advance_amount\":\"0.00\",\"status\":\"seated\",\"note\":null,\"created_by\":1,\"created_at\":\"2026-10-07T04:30:14.000000Z\",\"updated_at\":\"2026-10-07T08:23:35.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-08T00:31:00.000000Z\",\"reservation_end_at\":\"2026-10-08T13:32:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-08T00:31:00.000000Z\",\"reservation_end_at\":\"2026-10-08T13:32:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":4,\"restaurant_table_id\":1,\"is_primary\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\"}}],\"items\":[{\"id\":4,\"order_id\":4,\"order_kitchen_batch_id\":4,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\",\"addons\":[]}],\"payments\":[],\"latest_kitchen_batch\":{\"id\":4,\"order_id\":4,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\"},\"kitchen_batches\":[{\"id\":4,\"order_id\":4,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-08T08:55:31.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-08T08:55:31.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-08 03:54:05','2026-10-08 03:54:05'),(31,1,'orders','status_changed','Changed order status #4','App\\Models\\Order',4,'{\"status\":\"ready\"}','{\"status\":\"served\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-08 03:56:01','2026-10-08 03:56:01'),(32,1,'orders','updated','Updated order #4','App\\Models\\Order',4,'{\"id\":4,\"order_number\":\"ORD-20261008-WGV5O2\",\"customer_id\":2,\"customer_name\":\"Tisha\",\"customer_phone\":\"07364466764\",\"customer_email\":null,\"restaurant_table_id\":1,\"reservation_id\":2,\"status\":\"served\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"250.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":\"2026-10-08T09:55:40.000000Z\",\"ready_at\":\"2026-10-08T09:55:46.000000Z\",\"served_at\":\"2026-10-08T09:56:01.000000Z\",\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:01.000000Z\",\"deleted_at\":null}','{\"id\":4,\"order_number\":\"ORD-20261008-WGV5O2\",\"customer_id\":2,\"customer_name\":\"Tisha\",\"customer_phone\":\"07364466764\",\"customer_email\":null,\"restaurant_table_id\":1,\"reservation_id\":2,\"status\":\"served\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":\"2026-10-08T09:55:40.000000Z\",\"ready_at\":\"2026-10-08T09:55:46.000000Z\",\"served_at\":\"2026-10-08T09:56:01.000000Z\",\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:29.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":2,\"name\":\"Tisha\",\"phone\":\"07364466764\",\"email\":null,\"last_visit_at\":null,\"total_orders\":0,\"total_spent\":\"0.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:30:14.000000Z\",\"updated_at\":\"2026-10-07T04:30:14.000000Z\",\"deleted_at\":null},\"reservation\":{\"id\":2,\"reservation_no\":\"RES-20261007-J9QK7P3O\",\"customer_id\":2,\"reservation_date\":\"2026-10-08T00:00:00.000000Z\",\"start_time\":\"2026-10-08T00:31:00.000000Z\",\"end_time\":\"2026-10-08T13:32:00.000000Z\",\"guest_count\":1,\"advance_amount\":\"0.00\",\"status\":\"seated\",\"note\":null,\"created_by\":1,\"created_at\":\"2026-10-07T04:30:14.000000Z\",\"updated_at\":\"2026-10-07T08:23:35.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-08T00:31:00.000000Z\",\"reservation_end_at\":\"2026-10-08T13:32:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-08T00:31:00.000000Z\",\"reservation_end_at\":\"2026-10-08T13:32:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":4,\"restaurant_table_id\":1,\"is_primary\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\"}}],\"items\":[{\"id\":4,\"order_id\":4,\"order_kitchen_batch_id\":4,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:01.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":3,\"order_id\":4,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Additional payment received during served order update.\",\"received_by\":1,\"created_at\":\"2026-10-08T09:56:29.000000Z\",\"updated_at\":\"2026-10-08T09:56:29.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-08T08:55:31.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-08T08:55:31.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":3,\"order_id\":4,\"order_kitchen_batch_id\":4,\"order_number\":\"ORD-20261008-WGV5O2\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-08T09:55:40.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-08T09:55:40.000000Z\",\"updated_at\":\"2026-10-08T09:55:40.000000Z\"}],\"latest_kitchen_batch\":{\"id\":4,\"order_id\":4,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":\"2026-10-08T09:55:40.000000Z\",\"ready_at\":\"2026-10-08T09:55:46.000000Z\",\"served_at\":\"2026-10-08T09:56:01.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:01.000000Z\"},\"kitchen_batches\":[{\"id\":4,\"order_id\":4,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":\"2026-10-08T09:55:40.000000Z\",\"ready_at\":\"2026-10-08T09:55:46.000000Z\",\"served_at\":\"2026-10-08T09:56:01.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:01.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-08T08:55:31.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-08T08:55:31.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-08 03:56:29','2026-10-08 03:56:29'),(33,1,'orders','completed','Completed order #4','App\\Models\\Order',4,NULL,'{\"id\":4,\"order_number\":\"ORD-20261008-WGV5O2\",\"customer_id\":2,\"customer_name\":\"Tisha\",\"customer_phone\":\"07364466764\",\"customer_email\":null,\"restaurant_table_id\":1,\"reservation_id\":2,\"status\":\"completed\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":\"2026-10-08T09:55:40.000000Z\",\"ready_at\":\"2026-10-08T09:55:46.000000Z\",\"served_at\":\"2026-10-08T09:56:01.000000Z\",\"completed_at\":\"2026-10-08T09:56:30.000000Z\",\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":true,\"customer_spend_recorded_at\":\"2026-10-08T09:56:30.000000Z\",\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:30.000000Z\",\"deleted_at\":null,\"reservation\":{\"id\":2,\"reservation_no\":\"RES-20261007-J9QK7P3O\",\"customer_id\":2,\"reservation_date\":\"2026-10-08T00:00:00.000000Z\",\"start_time\":\"2026-10-08T00:31:00.000000Z\",\"end_time\":\"2026-10-08T13:32:00.000000Z\",\"guest_count\":1,\"advance_amount\":\"0.00\",\"status\":\"completed\",\"note\":null,\"created_by\":1,\"created_at\":\"2026-10-07T04:30:14.000000Z\",\"updated_at\":\"2026-10-08T09:56:30.000000Z\",\"deleted_at\":null},\"customer\":{\"id\":2,\"name\":\"Tisha\",\"phone\":\"07364466764\",\"email\":null,\"last_visit_at\":\"2026-10-08T09:56:30.000000Z\",\"total_orders\":0,\"total_spent\":\"250.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:30:14.000000Z\",\"updated_at\":\"2026-10-08T09:56:30.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-08T09:56:30.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":1,\"table_name\":\"Table 01\",\"capacity\":20,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-07T04:11:34.000000Z\",\"updated_at\":\"2026-10-08T09:56:30.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":4,\"restaurant_table_id\":1,\"is_primary\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:54:05.000000Z\"}}],\"items\":[{\"id\":4,\"order_id\":4,\"order_kitchen_batch_id\":4,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:01.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":3,\"order_id\":4,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Additional payment received during served order update.\",\"received_by\":1,\"created_at\":\"2026-10-08T09:56:29.000000Z\",\"updated_at\":\"2026-10-08T09:56:29.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-08T08:55:31.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-08T08:55:31.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":3,\"order_id\":4,\"order_kitchen_batch_id\":4,\"order_number\":\"ORD-20261008-WGV5O2\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-08T09:55:40.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-08T09:55:40.000000Z\",\"updated_at\":\"2026-10-08T09:55:40.000000Z\"}],\"latest_kitchen_batch\":{\"id\":4,\"order_id\":4,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":\"2026-10-08T09:55:40.000000Z\",\"ready_at\":\"2026-10-08T09:55:46.000000Z\",\"served_at\":\"2026-10-08T09:56:01.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:01.000000Z\"},\"kitchen_batches\":[{\"id\":4,\"order_id\":4,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-08T09:54:05.000000Z\",\"preparing_at\":\"2026-10-08T09:55:40.000000Z\",\"ready_at\":\"2026-10-08T09:55:46.000000Z\",\"served_at\":\"2026-10-08T09:56:01.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-08T09:54:05.000000Z\",\"updated_at\":\"2026-10-08T09:56:01.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-08T08:55:31.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-08T08:55:31.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','2026-10-08 03:56:30','2026-10-08 03:56:30'),(34,1,'users','blocked','Blocked user: Waiter','App\\Models\\User',3,'{\"is_active\":true,\"status\":\"Active\"}','{\"is_active\":false,\"status\":\"Blocked\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-11 03:54:53','2026-10-11 03:54:53'),(35,1,'users','unblocked','Unblocked user: Waiter','App\\Models\\User',3,'{\"is_active\":false,\"status\":\"Blocked\"}','{\"is_active\":true,\"status\":\"Active\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-11 03:55:03','2026-10-11 03:55:03'),(36,1,'users','blocked','Blocked user: Waiter','App\\Models\\User',3,'{\"is_active\":true,\"status\":\"Active\"}','{\"is_active\":false,\"status\":\"Blocked\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 09:00:01','2026-10-13 09:00:01'),(37,1,'users','unblocked','Unblocked user: Waiter','App\\Models\\User',3,'{\"is_active\":false,\"status\":\"Blocked\"}','{\"is_active\":true,\"status\":\"Active\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 09:00:04','2026-10-13 09:00:04'),(38,1,'orders','created','Created order #5','App\\Models\\Order',5,NULL,'{\"id\":5,\"order_number\":\"ORD-20261013-RPJBSG\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":4,\"reservation_id\":6,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":1,\"name\":\"Tasnima Akther Tisha\",\"phone\":\"+1 (844) 536-1838\",\"email\":\"shraboniakter554@gmail.com\",\"last_visit_at\":\"2026-10-07T05:26:03.000000Z\",\"total_orders\":0,\"total_spent\":\"250.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-07T05:26:36.000000Z\",\"deleted_at\":null},\"reservation\":{\"id\":6,\"reservation_no\":\"RES-20261013-IABBV6CB\",\"customer_id\":1,\"reservation_date\":\"2026-10-13T00:00:00.000000Z\",\"start_time\":\"2026-10-13T22:09:00.000000Z\",\"end_time\":\"2026-10-13T23:10:00.000000Z\",\"guest_count\":3,\"advance_amount\":\"0.00\",\"status\":\"seated\",\"note\":null,\"created_by\":1,\"created_at\":\"2026-10-13T15:08:44.000000Z\",\"updated_at\":\"2026-10-13T15:09:24.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":4,\"table_name\":\"table 4\",\"capacity\":7,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-13T22:09:00.000000Z\",\"reservation_end_at\":\"2026-10-13T23:10:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":4,\"table_name\":\"table 4\",\"capacity\":7,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-13T22:09:00.000000Z\",\"reservation_end_at\":\"2026-10-13T23:10:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":5,\"restaurant_table_id\":4,\"is_primary\":1,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\"}}],\"items\":[{\"id\":5,\"order_id\":5,\"order_kitchen_batch_id\":5,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":4,\"order_id\":5,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-13T14:04:55.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-13T14:04:55.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":5,\"order_id\":5,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-13T15:10:09.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\"},\"kitchen_batches\":[{\"id\":5,\"order_id\":5,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-13T15:10:09.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-13T14:04:55.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-13T14:04:55.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 09:10:09','2026-10-13 09:10:09'),(39,1,'orders','status_changed','Changed order status #5','App\\Models\\Order',5,'{\"status\":\"ready\"}','{\"status\":\"served\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 09:11:48','2026-10-13 09:11:48'),(40,1,'orders','completed','Completed order #5','App\\Models\\Order',5,NULL,'{\"id\":5,\"order_number\":\"ORD-20261013-RPJBSG\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":4,\"reservation_id\":6,\"status\":\"completed\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-13T15:10:09.000000Z\",\"preparing_at\":\"2026-10-13T15:11:32.000000Z\",\"ready_at\":\"2026-10-13T15:11:35.000000Z\",\"served_at\":\"2026-10-13T15:11:48.000000Z\",\"completed_at\":\"2026-10-13T15:11:59.000000Z\",\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":true,\"customer_spend_recorded_at\":\"2026-10-13T15:11:59.000000Z\",\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:11:59.000000Z\",\"deleted_at\":null,\"reservation\":{\"id\":6,\"reservation_no\":\"RES-20261013-IABBV6CB\",\"customer_id\":1,\"reservation_date\":\"2026-10-13T00:00:00.000000Z\",\"start_time\":\"2026-10-13T22:09:00.000000Z\",\"end_time\":\"2026-10-13T23:10:00.000000Z\",\"guest_count\":3,\"advance_amount\":\"0.00\",\"status\":\"completed\",\"note\":null,\"created_by\":1,\"created_at\":\"2026-10-13T15:08:44.000000Z\",\"updated_at\":\"2026-10-13T15:11:59.000000Z\",\"deleted_at\":null},\"customer\":{\"id\":1,\"name\":\"Tasnima Akther Tisha\",\"phone\":\"+1 (844) 536-1838\",\"email\":\"shraboniakter554@gmail.com\",\"last_visit_at\":\"2026-10-13T15:11:59.000000Z\",\"total_orders\":0,\"total_spent\":\"500.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-13T15:11:59.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":4,\"table_name\":\"table 4\",\"capacity\":7,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-13T15:11:59.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":4,\"table_name\":\"table 4\",\"capacity\":7,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-13T15:11:59.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":5,\"restaurant_table_id\":4,\"is_primary\":1,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\"}}],\"items\":[{\"id\":5,\"order_id\":5,\"order_kitchen_batch_id\":5,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:11:48.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":4,\"order_id\":5,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:10:09.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-13T14:04:55.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-13T14:04:55.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":4,\"order_id\":5,\"order_kitchen_batch_id\":5,\"order_number\":\"ORD-20261013-RPJBSG\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-13T15:11:32.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-13T15:11:32.000000Z\",\"updated_at\":\"2026-10-13T15:11:32.000000Z\"}],\"latest_kitchen_batch\":{\"id\":5,\"order_id\":5,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-13T15:10:09.000000Z\",\"preparing_at\":\"2026-10-13T15:11:32.000000Z\",\"ready_at\":\"2026-10-13T15:11:35.000000Z\",\"served_at\":\"2026-10-13T15:11:48.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:11:48.000000Z\"},\"kitchen_batches\":[{\"id\":5,\"order_id\":5,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-13T15:10:09.000000Z\",\"preparing_at\":\"2026-10-13T15:11:32.000000Z\",\"ready_at\":\"2026-10-13T15:11:35.000000Z\",\"served_at\":\"2026-10-13T15:11:48.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-13T15:10:09.000000Z\",\"updated_at\":\"2026-10-13T15:11:48.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-13T14:04:55.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-13T14:04:55.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-13 09:12:00','2026-10-13 09:12:00'),(41,1,'orders','created','Created order #6','App\\Models\\Order',6,NULL,'{\"id\":6,\"order_number\":\"ORD-20261015-VZ5STH\",\"customer_id\":6,\"customer_name\":\"Vaughan Roberson\",\"customer_phone\":\"+1 (528) 232-8797\",\"customer_email\":\"jujo@mailinator.com\",\"restaurant_table_id\":4,\"reservation_id\":5,\"status\":\"pending\",\"subtotal\":\"750.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"750.00\",\"paid_amount\":\"750.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":6,\"name\":\"Vaughan Roberson\",\"phone\":\"+1 (528) 232-8797\",\"email\":\"jujo@mailinator.com\",\"last_visit_at\":null,\"total_orders\":0,\"total_spent\":\"0.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-11T10:38:17.000000Z\",\"updated_at\":\"2026-10-11T10:38:17.000000Z\",\"deleted_at\":null},\"reservation\":{\"id\":5,\"reservation_no\":\"RES-20261011-OL0OTW6P\",\"customer_id\":6,\"reservation_date\":\"2026-10-11T00:00:00.000000Z\",\"start_time\":\"2026-10-11T18:17:00.000000Z\",\"end_time\":\"2026-10-11T19:03:00.000000Z\",\"guest_count\":3,\"advance_amount\":\"3.00\",\"status\":\"seated\",\"note\":\"Possimus et perspiciatis duis tempora beatae\",\"created_by\":1,\"created_at\":\"2026-10-11T10:38:17.000000Z\",\"updated_at\":\"2026-10-14T04:54:53.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":4,\"table_name\":\"table 4\",\"capacity\":7,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-11T18:17:00.000000Z\",\"reservation_end_at\":\"2026-10-11T19:03:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":4,\"table_name\":\"table 4\",\"capacity\":7,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-11T18:17:00.000000Z\",\"reservation_end_at\":\"2026-10-11T19:03:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":6,\"restaurant_table_id\":4,\"is_primary\":1,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\"}}],\"items\":[{\"id\":6,\"order_id\":6,\"order_kitchen_batch_id\":6,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":3,\"addon_total\":\"0.00\",\"line_total\":\"750.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":5,\"order_id\":6,\"amount\":\"750.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-15T03:16:44.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-15T03:16:44.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":6,\"order_id\":6,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-15T03:45:48.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\"},\"kitchen_batches\":[{\"id\":6,\"order_id\":6,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-15T03:45:48.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-15T03:16:44.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-15T03:16:44.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-14 21:45:48','2026-10-14 21:45:48'),(42,1,'orders','status_changed','Changed order status #6','App\\Models\\Order',6,'{\"status\":\"ready\"}','{\"status\":\"served\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-14 21:48:19','2026-10-14 21:48:19'),(43,1,'orders','completed','Completed order #6','App\\Models\\Order',6,NULL,'{\"id\":6,\"order_number\":\"ORD-20261015-VZ5STH\",\"customer_id\":6,\"customer_name\":\"Vaughan Roberson\",\"customer_phone\":\"+1 (528) 232-8797\",\"customer_email\":\"jujo@mailinator.com\",\"restaurant_table_id\":4,\"reservation_id\":5,\"status\":\"completed\",\"subtotal\":\"750.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"750.00\",\"paid_amount\":\"750.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-15T03:45:48.000000Z\",\"preparing_at\":\"2026-10-15T03:47:57.000000Z\",\"ready_at\":\"2026-10-15T03:48:01.000000Z\",\"served_at\":\"2026-10-15T03:48:19.000000Z\",\"completed_at\":\"2026-10-15T03:48:27.000000Z\",\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":true,\"customer_spend_recorded_at\":\"2026-10-15T03:48:27.000000Z\",\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:48:27.000000Z\",\"deleted_at\":null,\"reservation\":{\"id\":5,\"reservation_no\":\"RES-20261011-OL0OTW6P\",\"customer_id\":6,\"reservation_date\":\"2026-10-11T00:00:00.000000Z\",\"start_time\":\"2026-10-11T18:17:00.000000Z\",\"end_time\":\"2026-10-11T19:03:00.000000Z\",\"guest_count\":3,\"advance_amount\":\"3.00\",\"status\":\"completed\",\"note\":\"Possimus et perspiciatis duis tempora beatae\",\"created_by\":1,\"created_at\":\"2026-10-11T10:38:17.000000Z\",\"updated_at\":\"2026-10-15T03:48:27.000000Z\",\"deleted_at\":null},\"customer\":{\"id\":6,\"name\":\"Vaughan Roberson\",\"phone\":\"+1 (528) 232-8797\",\"email\":\"jujo@mailinator.com\",\"last_visit_at\":\"2026-10-15T03:48:27.000000Z\",\"total_orders\":0,\"total_spent\":\"750.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-11T10:38:17.000000Z\",\"updated_at\":\"2026-10-15T03:48:27.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":4,\"table_name\":\"table 4\",\"capacity\":7,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-15T03:48:27.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":4,\"table_name\":\"table 4\",\"capacity\":7,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-15T03:48:27.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":6,\"restaurant_table_id\":4,\"is_primary\":1,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\"}}],\"items\":[{\"id\":6,\"order_id\":6,\"order_kitchen_batch_id\":6,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":3,\"addon_total\":\"0.00\",\"line_total\":\"750.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:48:19.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":5,\"order_id\":6,\"amount\":\"750.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:45:48.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-15T03:16:44.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-15T03:16:44.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":5,\"order_id\":6,\"order_kitchen_batch_id\":6,\"order_number\":\"ORD-20261015-VZ5STH\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-15T03:47:57.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-15T03:47:57.000000Z\",\"updated_at\":\"2026-10-15T03:47:57.000000Z\"}],\"latest_kitchen_batch\":{\"id\":6,\"order_id\":6,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-15T03:45:48.000000Z\",\"preparing_at\":\"2026-10-15T03:47:57.000000Z\",\"ready_at\":\"2026-10-15T03:48:01.000000Z\",\"served_at\":\"2026-10-15T03:48:19.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:48:19.000000Z\"},\"kitchen_batches\":[{\"id\":6,\"order_id\":6,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-15T03:45:48.000000Z\",\"preparing_at\":\"2026-10-15T03:47:57.000000Z\",\"ready_at\":\"2026-10-15T03:48:01.000000Z\",\"served_at\":\"2026-10-15T03:48:19.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-15T03:45:48.000000Z\",\"updated_at\":\"2026-10-15T03:48:19.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-15T03:16:44.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-15T03:16:44.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36','2026-10-14 21:48:27','2026-10-14 21:48:27'),(44,1,'orders','created','Created order #7','App\\Models\\Order',7,NULL,'{\"id\":7,\"order_number\":\"ORD-20261025-LLNHH1\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":4,\"reservation_id\":7,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"250.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":1,\"name\":\"Tasnima Akther Tisha\",\"phone\":\"+1 (844) 536-1838\",\"email\":\"shraboniakter554@gmail.com\",\"last_visit_at\":\"2026-10-13T15:11:59.000000Z\",\"total_orders\":0,\"total_spent\":\"500.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-13T15:11:59.000000Z\",\"deleted_at\":null},\"reservation\":{\"id\":7,\"reservation_no\":\"RES-20261014-DIH80WSX\",\"customer_id\":1,\"reservation_date\":\"2026-10-14T00:00:00.000000Z\",\"start_time\":\"2026-10-14T11:29:00.000000Z\",\"end_time\":\"2026-10-14T11:59:00.000000Z\",\"guest_count\":4,\"advance_amount\":\"0.00\",\"status\":\"seated\",\"note\":null,\"created_by\":1,\"created_at\":\"2026-10-14T05:19:10.000000Z\",\"updated_at\":\"2026-10-25T04:07:19.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":4,\"table_name\":\"table 4\",\"capacity\":8,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-14T11:29:00.000000Z\",\"reservation_end_at\":\"2026-10-14T11:59:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":4,\"table_name\":\"table 4\",\"capacity\":8,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-14T11:29:00.000000Z\",\"reservation_end_at\":\"2026-10-14T11:59:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":7,\"restaurant_table_id\":4,\"is_primary\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\"}}],\"items\":[{\"id\":7,\"order_id\":7,\"order_kitchen_batch_id\":7,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\",\"addons\":[]}],\"payments\":[],\"latest_kitchen_batch\":{\"id\":7,\"order_id\":7,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\"},\"kitchen_batches\":[{\"id\":7,\"order_id\":7,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-25T03:10:51.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-25T03:10:51.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-24 22:08:16','2026-10-24 22:08:16'),(45,1,'orders','status_changed','Changed order status #7','App\\Models\\Order',7,'{\"status\":\"ready\"}','{\"status\":\"served\"}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-24 22:36:15','2026-10-24 22:36:15'),(46,1,'orders','updated','Updated order #7','App\\Models\\Order',7,'{\"id\":7,\"order_number\":\"ORD-20261025-LLNHH1\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":4,\"reservation_id\":7,\"status\":\"served\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"250.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":\"2026-10-25T04:34:26.000000Z\",\"ready_at\":\"2026-10-25T04:34:33.000000Z\",\"served_at\":\"2026-10-25T04:36:15.000000Z\",\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:36:15.000000Z\",\"deleted_at\":null}','{\"id\":7,\"order_number\":\"ORD-20261025-LLNHH1\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":4,\"reservation_id\":7,\"status\":\"served\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":\"2026-10-25T04:34:26.000000Z\",\"ready_at\":\"2026-10-25T04:34:33.000000Z\",\"served_at\":\"2026-10-25T04:36:15.000000Z\",\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T05:05:17.000000Z\",\"deleted_at\":null,\"customer\":{\"id\":1,\"name\":\"Tasnima Akther Tisha\",\"phone\":\"+1 (844) 536-1838\",\"email\":\"shraboniakter554@gmail.com\",\"last_visit_at\":\"2026-10-13T15:11:59.000000Z\",\"total_orders\":0,\"total_spent\":\"500.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-13T15:11:59.000000Z\",\"deleted_at\":null},\"reservation\":{\"id\":7,\"reservation_no\":\"RES-20261014-DIH80WSX\",\"customer_id\":1,\"reservation_date\":\"2026-10-14T00:00:00.000000Z\",\"start_time\":\"2026-10-14T11:29:00.000000Z\",\"end_time\":\"2026-10-14T11:59:00.000000Z\",\"guest_count\":4,\"advance_amount\":\"0.00\",\"status\":\"seated\",\"note\":null,\"created_by\":1,\"created_at\":\"2026-10-14T05:19:10.000000Z\",\"updated_at\":\"2026-10-25T04:07:19.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":4,\"table_name\":\"table 4\",\"capacity\":8,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-14T11:29:00.000000Z\",\"reservation_end_at\":\"2026-10-14T11:59:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":4,\"table_name\":\"table 4\",\"capacity\":8,\"section\":\"ac\",\"status\":\"occupied\",\"reservation_start_at\":\"2026-10-14T11:29:00.000000Z\",\"reservation_end_at\":\"2026-10-14T11:59:00.000000Z\",\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":7,\"restaurant_table_id\":4,\"is_primary\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\"}}],\"items\":[{\"id\":7,\"order_id\":7,\"order_kitchen_batch_id\":7,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:36:15.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":6,\"order_id\":7,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Additional payment received during served order update.\",\"received_by\":1,\"created_at\":\"2026-10-25T05:05:17.000000Z\",\"updated_at\":\"2026-10-25T05:05:17.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-25T03:10:51.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-25T03:10:51.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":6,\"order_id\":7,\"order_kitchen_batch_id\":7,\"order_number\":\"ORD-20261025-LLNHH1\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-25T04:34:26.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-25T04:34:26.000000Z\",\"updated_at\":\"2026-10-25T04:34:26.000000Z\"}],\"latest_kitchen_batch\":{\"id\":7,\"order_id\":7,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":\"2026-10-25T04:34:26.000000Z\",\"ready_at\":\"2026-10-25T04:34:33.000000Z\",\"served_at\":\"2026-10-25T04:36:15.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:36:15.000000Z\"},\"kitchen_batches\":[{\"id\":7,\"order_id\":7,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":\"2026-10-25T04:34:26.000000Z\",\"ready_at\":\"2026-10-25T04:34:33.000000Z\",\"served_at\":\"2026-10-25T04:36:15.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:36:15.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-25T03:10:51.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-25T03:10:51.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-24 23:05:17','2026-10-24 23:05:17'),(47,1,'orders','completed','Completed order #7','App\\Models\\Order',7,NULL,'{\"id\":7,\"order_number\":\"ORD-20261025-LLNHH1\",\"customer_id\":1,\"customer_name\":\"Tasnima Akther Tisha\",\"customer_phone\":\"+1 (844) 536-1838\",\"customer_email\":\"shraboniakter554@gmail.com\",\"restaurant_table_id\":4,\"reservation_id\":7,\"status\":\"completed\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":\"2026-10-25T04:34:26.000000Z\",\"ready_at\":\"2026-10-25T04:34:33.000000Z\",\"served_at\":\"2026-10-25T04:36:15.000000Z\",\"completed_at\":\"2026-10-25T05:05:18.000000Z\",\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":true,\"customer_spend_recorded_at\":\"2026-10-25T05:05:18.000000Z\",\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T05:05:18.000000Z\",\"deleted_at\":null,\"reservation\":{\"id\":7,\"reservation_no\":\"RES-20261014-DIH80WSX\",\"customer_id\":1,\"reservation_date\":\"2026-10-14T00:00:00.000000Z\",\"start_time\":\"2026-10-14T11:29:00.000000Z\",\"end_time\":\"2026-10-14T11:59:00.000000Z\",\"guest_count\":4,\"advance_amount\":\"0.00\",\"status\":\"completed\",\"note\":null,\"created_by\":1,\"created_at\":\"2026-10-14T05:19:10.000000Z\",\"updated_at\":\"2026-10-25T05:05:18.000000Z\",\"deleted_at\":null},\"customer\":{\"id\":1,\"name\":\"Tasnima Akther Tisha\",\"phone\":\"+1 (844) 536-1838\",\"email\":\"shraboniakter554@gmail.com\",\"last_visit_at\":\"2026-10-25T05:05:18.000000Z\",\"total_orders\":0,\"total_spent\":\"750.00\",\"is_active\":true,\"notes\":null,\"created_at\":\"2026-10-07T04:19:01.000000Z\",\"updated_at\":\"2026-10-25T05:05:18.000000Z\",\"deleted_at\":null},\"primary_table\":{\"id\":4,\"table_name\":\"table 4\",\"capacity\":8,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-25T05:05:18.000000Z\",\"deleted_at\":null},\"tables\":[{\"id\":4,\"table_name\":\"table 4\",\"capacity\":8,\"section\":\"ac\",\"status\":\"available\",\"reservation_start_at\":null,\"reservation_end_at\":null,\"merged_with_id\":null,\"notes\":null,\"created_at\":\"2026-10-08T07:40:04.000000Z\",\"updated_at\":\"2026-10-25T05:05:18.000000Z\",\"deleted_at\":null,\"pivot\":{\"order_id\":7,\"restaurant_table_id\":4,\"is_primary\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:08:16.000000Z\"}}],\"items\":[{\"id\":7,\"order_id\":7,\"order_kitchen_batch_id\":7,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"served\",\"kitchen_note\":null,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:36:15.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":6,\"order_id\":7,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Additional payment received during served order update.\",\"received_by\":1,\"created_at\":\"2026-10-25T05:05:17.000000Z\",\"updated_at\":\"2026-10-25T05:05:17.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-25T03:10:51.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-25T03:10:51.000000Z\",\"deleted_at\":null}}],\"recipe_consumptions\":[{\"id\":6,\"order_id\":7,\"order_kitchen_batch_id\":7,\"order_number\":\"ORD-20261025-LLNHH1\",\"trigger\":\"start_preparing\",\"order_status_snapshot\":\"preparing\",\"consumed_at\":\"2026-10-25T04:34:26.000000Z\",\"created_by\":2,\"created_at\":\"2026-10-25T04:34:26.000000Z\",\"updated_at\":\"2026-10-25T04:34:26.000000Z\"}],\"latest_kitchen_batch\":{\"id\":7,\"order_id\":7,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":\"2026-10-25T04:34:26.000000Z\",\"ready_at\":\"2026-10-25T04:34:33.000000Z\",\"served_at\":\"2026-10-25T04:36:15.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:36:15.000000Z\"},\"kitchen_batches\":[{\"id\":7,\"order_id\":7,\"batch_no\":1,\"status\":\"served\",\"chef_id\":2,\"sent_to_kitchen_at\":\"2026-10-25T04:08:16.000000Z\",\"preparing_at\":\"2026-10-25T04:34:26.000000Z\",\"ready_at\":\"2026-10-25T04:34:33.000000Z\",\"served_at\":\"2026-10-25T04:36:15.000000Z\",\"created_by\":1,\"created_at\":\"2026-10-25T04:08:16.000000Z\",\"updated_at\":\"2026-10-25T04:36:15.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-25T03:10:51.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-25T03:10:51.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-24 23:05:18','2026-10-24 23:05:18'),(48,1,'orders','created','Created order #8','App\\Models\\Order',8,NULL,'{\"id\":8,\"order_number\":\"ORD-20261025-QKLF8J\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00008\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-25T09:23:52.000000Z\",\"updated_at\":\"2026-10-25T09:23:52.000000Z\",\"deleted_at\":null,\"customer\":null,\"reservation\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":8,\"order_id\":8,\"order_kitchen_batch_id\":8,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-25T09:23:52.000000Z\",\"updated_at\":\"2026-10-25T09:23:52.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":7,\"order_id\":8,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-25T09:23:52.000000Z\",\"updated_at\":\"2026-10-25T09:23:52.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-25T03:10:51.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-25T03:10:51.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":8,\"order_id\":8,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-25T09:23:52.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-25T09:23:52.000000Z\",\"updated_at\":\"2026-10-25T09:23:52.000000Z\"},\"kitchen_batches\":[{\"id\":8,\"order_id\":8,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-25T09:23:52.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-25T09:23:52.000000Z\",\"updated_at\":\"2026-10-25T09:23:52.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-25T03:10:51.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-25T03:10:51.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-25 03:23:52','2026-10-25 03:23:52'),(49,1,'orders','created','Created order #9','App\\Models\\Order',9,NULL,'{\"id\":9,\"order_number\":\"ORD-20261026-OW2JNX\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00009\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"pending\",\"subtotal\":\"500.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"500.00\",\"paid_amount\":\"500.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:22:42.000000Z\",\"deleted_at\":null,\"customer\":null,\"reservation\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":9,\"order_id\":9,\"order_kitchen_batch_id\":9,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":2,\"addon_total\":\"0.00\",\"line_total\":\"500.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:22:42.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":8,\"order_id\":9,\"amount\":\"500.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:22:42.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":9,\"order_id\":9,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T08:22:42.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:22:42.000000Z\"},\"kitchen_batches\":[{\"id\":9,\"order_id\":9,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T08:22:42.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:22:42.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 02:22:43','2026-10-26 02:22:43'),(50,1,'orders','cancelled','Cancelled order #9','App\\Models\\Order',9,'{\"id\":9,\"order_number\":\"ORD-20261026-OW2JNX\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00009\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"pending\",\"subtotal\":\"500.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"500.00\",\"paid_amount\":\"500.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-26T08:22:42.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:23:05.000000Z\",\"deleted_at\":null}','{\"id\":9,\"order_number\":\"ORD-20261026-OW2JNX\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00009\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"canceled\",\"subtotal\":\"500.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"500.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"0.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-26T08:22:42.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":\"2026-10-26T08:23:49.000000Z\",\"cancellation_reason\":\"Item unavailable\",\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:23:49.000000Z\",\"deleted_at\":null,\"reservation\":null,\"customer\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":9,\"order_id\":9,\"order_kitchen_batch_id\":9,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":2,\"addon_total\":\"0.00\",\"line_total\":\"500.00\",\"status\":\"canceled\",\"kitchen_note\":null,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:23:49.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":8,\"order_id\":9,\"amount\":\"500.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-26T08:22:42.000000Z\",\"updated_at\":\"2026-10-26T08:22:42.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 02:23:49','2026-10-26 02:23:49'),(51,1,'orders','created','Created order #10','App\\Models\\Order',10,NULL,'{\"id\":10,\"order_number\":\"ORD-20261026-SXGNQX\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00010\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"pending\",\"subtotal\":\"250.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"250.00\",\"paid_amount\":\"250.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-26T08:24:12.000000Z\",\"updated_at\":\"2026-10-26T08:24:13.000000Z\",\"deleted_at\":null,\"customer\":null,\"reservation\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":10,\"order_id\":10,\"order_kitchen_batch_id\":10,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"250.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-26T08:24:13.000000Z\",\"updated_at\":\"2026-10-26T08:24:13.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":9,\"order_id\":10,\"amount\":\"250.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-26T08:24:12.000000Z\",\"updated_at\":\"2026-10-26T08:24:12.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":10,\"order_id\":10,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T08:24:13.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T08:24:13.000000Z\",\"updated_at\":\"2026-10-26T08:24:13.000000Z\"},\"kitchen_batches\":[{\"id\":10,\"order_id\":10,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T08:24:13.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T08:24:13.000000Z\",\"updated_at\":\"2026-10-26T08:24:13.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 02:24:13','2026-10-26 02:24:13'),(52,1,'orders','created','Created order #111','App\\Models\\Order',111,NULL,'{\"id\":111,\"order_number\":\"ORD-20261026-F1E5LC\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00111\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"pending\",\"subtotal\":\"750.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"750.00\",\"paid_amount\":\"750.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:57:39.000000Z\",\"deleted_at\":null,\"customer\":null,\"reservation\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":111,\"order_id\":111,\"order_kitchen_batch_id\":111,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":3,\"addon_total\":\"0.00\",\"line_total\":\"750.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:57:39.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":110,\"order_id\":111,\"amount\":\"750.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:57:39.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":111,\"order_id\":111,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T10:57:39.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:57:39.000000Z\"},\"kitchen_batches\":[{\"id\":111,\"order_id\":111,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T10:57:39.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:57:39.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 04:57:39','2026-10-26 04:57:39'),(53,1,'orders','cancelled','Cancelled order #111','App\\Models\\Order',111,'{\"id\":111,\"order_number\":\"ORD-20261026-F1E5LC\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00111\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"pending\",\"subtotal\":\"750.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"750.00\",\"paid_amount\":\"750.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-26T10:57:39.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:57:58.000000Z\",\"deleted_at\":null}','{\"id\":111,\"order_number\":\"ORD-20261026-F1E5LC\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00111\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"canceled\",\"subtotal\":\"750.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"750.00\",\"paid_amount\":\"0.00\",\"due_amount\":\"0.00\",\"payment_status\":\"due\",\"payment_method\":null,\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":\"2026-10-26T10:57:39.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":\"2026-10-26T10:58:36.000000Z\",\"cancellation_reason\":\"Item unavailable\",\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":2,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:58:36.000000Z\",\"deleted_at\":null,\"reservation\":null,\"customer\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":111,\"order_id\":111,\"order_kitchen_batch_id\":111,\"menu_item_id\":1,\"menu_item_variant_id\":null,\"item_name\":\"Classic Burger\",\"variant_name\":null,\"unit_price\":\"250.00\",\"quantity\":3,\"addon_total\":\"0.00\",\"line_total\":\"750.00\",\"status\":\"canceled\",\"kitchen_note\":null,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:58:36.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":110,\"order_id\":111,\"amount\":\"750.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-26T10:57:39.000000Z\",\"updated_at\":\"2026-10-26T10:57:39.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 04:58:36','2026-10-26 04:58:36'),(54,1,'orders','created','Created order #112','App\\Models\\Order',112,NULL,'{\"id\":112,\"order_number\":\"ORD-20261026-GGCQ4L\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00112\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"pending\",\"subtotal\":\"205.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"205.00\",\"paid_amount\":\"205.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-26T10:59:27.000000Z\",\"updated_at\":\"2026-10-26T10:59:27.000000Z\",\"deleted_at\":null,\"customer\":null,\"reservation\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":112,\"order_id\":112,\"order_kitchen_batch_id\":112,\"menu_item_id\":2,\"menu_item_variant_id\":null,\"item_name\":\"DEMO100-Menu Item 001\",\"variant_name\":null,\"unit_price\":\"205.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"205.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-26T10:59:27.000000Z\",\"updated_at\":\"2026-10-26T10:59:27.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":111,\"order_id\":112,\"amount\":\"205.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-26T10:59:27.000000Z\",\"updated_at\":\"2026-10-26T10:59:27.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":112,\"order_id\":112,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T10:59:27.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T10:59:27.000000Z\",\"updated_at\":\"2026-10-26T10:59:27.000000Z\"},\"kitchen_batches\":[{\"id\":112,\"order_id\":112,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T10:59:27.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T10:59:27.000000Z\",\"updated_at\":\"2026-10-26T10:59:27.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 04:59:27','2026-10-26 04:59:27'),(55,1,'orders','created','Created order #113','App\\Models\\Order',113,NULL,'{\"id\":113,\"order_number\":\"ORD-20261026-LA4AAV\",\"customer_id\":null,\"customer_name\":null,\"customer_phone\":null,\"customer_email\":null,\"restaurant_table_id\":null,\"reservation_id\":null,\"fulfillment_type\":\"takeaway\",\"pickup_token\":\"T-00113\",\"pickup_status\":\"uncollected\",\"picked_up_at\":null,\"status\":\"pending\",\"subtotal\":\"205.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"service_charge\":\"0.00\",\"total_amount\":\"205.00\",\"paid_amount\":\"205.00\",\"due_amount\":\"0.00\",\"payment_status\":\"paid\",\"payment_method\":\"cash\",\"payment_breakdown\":null,\"payment_reference\":null,\"order_note\":null,\"kitchen_note\":null,\"sent_to_kitchen_at\":null,\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"completed_at\":null,\"canceled_at\":null,\"cancellation_reason\":null,\"is_customer_spend_recorded\":false,\"customer_spend_recorded_at\":null,\"created_by\":1,\"chef_id\":null,\"created_at\":\"2026-10-26T11:01:30.000000Z\",\"updated_at\":\"2026-10-26T11:01:30.000000Z\",\"deleted_at\":null,\"customer\":null,\"reservation\":null,\"primary_table\":null,\"tables\":[],\"items\":[{\"id\":113,\"order_id\":113,\"order_kitchen_batch_id\":113,\"menu_item_id\":2,\"menu_item_variant_id\":null,\"item_name\":\"DEMO100-Menu Item 001\",\"variant_name\":null,\"unit_price\":\"205.00\",\"quantity\":1,\"addon_total\":\"0.00\",\"line_total\":\"205.00\",\"status\":\"pending\",\"kitchen_note\":null,\"created_at\":\"2026-10-26T11:01:30.000000Z\",\"updated_at\":\"2026-10-26T11:01:30.000000Z\",\"addons\":[]}],\"payments\":[{\"id\":112,\"order_id\":113,\"amount\":\"205.00\",\"payment_method\":\"cash\",\"reference\":null,\"note\":\"Initial payment during order creation.\",\"received_by\":1,\"created_at\":\"2026-10-26T11:01:30.000000Z\",\"updated_at\":\"2026-10-26T11:01:30.000000Z\",\"receiver\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}],\"latest_kitchen_batch\":{\"id\":113,\"order_id\":113,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T11:01:30.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T11:01:30.000000Z\",\"updated_at\":\"2026-10-26T11:01:30.000000Z\"},\"kitchen_batches\":[{\"id\":113,\"order_id\":113,\"batch_no\":1,\"status\":\"pending\",\"chef_id\":null,\"sent_to_kitchen_at\":\"2026-10-26T11:01:30.000000Z\",\"preparing_at\":null,\"ready_at\":null,\"served_at\":null,\"created_by\":1,\"created_at\":\"2026-10-26T11:01:30.000000Z\",\"updated_at\":\"2026-10-26T11:01:30.000000Z\"}],\"creator\":{\"id\":1,\"role_id\":1,\"name\":\"Tisha\",\"username\":\"shraboniakter554\",\"email\":\"shraboniakter554@gmail.com\",\"email_verified_at\":null,\"is_active\":true,\"last_login_at\":\"2026-10-26T03:07:28.000000Z\",\"last_logout_at\":\"2026-10-08T08:11:00.000000Z\",\"failed_login_attempts\":0,\"blocked_at\":null,\"must_change_password\":0,\"password_changed_at\":null,\"created_at\":\"2026-10-06T19:10:42.000000Z\",\"updated_at\":\"2026-10-26T03:07:28.000000Z\",\"deleted_at\":null}}','127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36','2026-10-26 05:01:30','2026-10-26 05:01:30');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart_of_accounts`
--

DROP TABLE IF EXISTS `chart_of_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_of_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `system_key` varchar(80) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `normal_balance` varchar(10) NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `allow_manual_posting` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chart_of_accounts_code_unique` (`code`),
  UNIQUE KEY `chart_of_accounts_system_key_unique` (`system_key`),
  KEY `coa_account_type_idx` (`account_type`),
  KEY `coa_parent_idx` (`parent_id`),
  KEY `coa_active_idx` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_of_accounts`
--

LOCK TABLES `chart_of_accounts` WRITE;
/*!40000 ALTER TABLE `chart_of_accounts` DISABLE KEYS */;
INSERT INTO `chart_of_accounts` VALUES (1,'1000','assets','Assets','asset','debit',NULL,1,1,0,'Root asset account.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(2,'1010','cash','Cash in Hand','asset','debit',1,1,1,1,'Physical cash held by the restaurant.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(3,'1020','bank','Bank Account','asset','debit',1,1,1,1,'Primary bank account / bank balance.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(4,'1030','card_settlement','Card Settlement Account','asset','debit',1,1,1,1,'Card/POS settlement receivable or clearing balance.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(5,'1040','bkash_wallet','bKash Wallet','asset','debit',1,1,1,1,'bKash merchant/business wallet balance.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(6,'1050','nagad_wallet','Nagad Wallet','asset','debit',1,1,1,1,'Nagad merchant/business wallet balance.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(7,'1060','mixed_payment_clearing','Mixed Payment Clearing','asset','debit',1,1,1,1,'Temporary asset account for mixed payments until split posting is available.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(8,'1070','other_payment_clearing','Other Payment Clearing','asset','debit',1,1,1,1,'Temporary asset account for other payment methods.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(9,'1100','accounts_receivable','Accounts Receivable','asset','debit',1,1,1,1,'Amounts owed by customers.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(10,'1200','inventory','Inventory','asset','debit',1,1,1,1,'Food ingredients, raw materials and stock value.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(11,'2000','liabilities','Liabilities','liability','credit',NULL,1,1,0,'Root liability account.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(12,'2010','accounts_payable','Accounts Payable','liability','credit',11,1,1,1,'Amounts owed to suppliers and vendors.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(13,'2100','tax_payable','Tax Payable','liability','credit',11,1,1,1,'Taxes collected or accrued and payable.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(14,'3000','equity','Equity','equity','credit',NULL,1,1,0,'Root equity account.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(15,'3010','owners_equity','Owner\'s Equity','equity','credit',14,1,1,1,'Owner capital and equity contributions.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(16,'3020','retained_earnings','Retained Earnings','equity','credit',14,1,1,0,'Accumulated retained profit/loss.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(17,'4000','revenue','Revenue','revenue','credit',NULL,1,1,0,'Root revenue account.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(18,'4010','restaurant_sales_revenue','Restaurant Sales Revenue','revenue','credit',17,1,1,1,'Normal restaurant/POS food and beverage sales.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(19,'4100','other_income','Other Income','revenue','credit',17,1,1,0,'Parent account for non-POS other income.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(20,'4110','bottle_crate_income','Bottle / Crate Income','revenue','credit',19,1,1,1,'Income from bottle and crate sales.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(21,'4120','waste_paper_income','Waste Paper Income','revenue','credit',19,1,1,1,'Income from waste paper sales.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(22,'4130','carton_income','Carton Income','revenue','credit',19,1,1,1,'Income from carton sales.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(23,'4140','scrap_income','Scrap Income','revenue','credit',19,1,1,1,'Income from scrap sales.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(24,'4150','used_oil_income','Used Cooking Oil Income','revenue','credit',19,1,1,1,'Income from selling used cooking oil.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(25,'4160','recyclable_waste_income','Recyclable Waste Income','revenue','credit',19,1,1,1,'Income from other recyclable waste.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(26,'4190','misc_other_income','Miscellaneous Other Income','revenue','credit',19,1,1,1,'Miscellaneous non-POS income.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(27,'5000','cogs','Cost of Goods Sold','cogs','debit',NULL,1,1,0,'Root cost of goods sold account.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(28,'5010','food_ingredient_cogs','Food & Ingredient COGS','cogs','debit',27,1,1,1,'Consumed food ingredient and recipe cost.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(29,'6000','operating_expenses','Operating Expenses','expense','debit',NULL,1,1,0,'Root operating expense account.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(30,'6100','salary_wages_expense','Salary & Wages Expense','expense','debit',29,1,1,1,'Employee salary and wage expense.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(31,'6200','rent_expense','Rent Expense','expense','debit',29,1,1,1,'Restaurant premises rent expense.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(32,'6300','utilities_expense','Utilities Expense','expense','debit',29,1,1,1,'Electricity, gas, water and utility expenses.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(33,'6400','maintenance_repairs_expense','Maintenance & Repairs Expense','expense','debit',29,1,1,1,'Maintenance and repair costs.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(34,'6500','marketing_expense','Marketing Expense','expense','debit',29,1,1,1,'Advertising and marketing expense.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(35,'6600','delivery_transport_expense','Delivery / Transport Expense','expense','debit',29,1,1,1,'Delivery, courier and transport expense.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(36,'6700','cleaning_supplies_expense','Cleaning & Supplies Expense','expense','debit',29,1,1,1,'Cleaning materials and operating supplies.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(37,'6900','misc_operating_expense','Miscellaneous Operating Expense','expense','debit',29,1,1,1,'Other operating expenses not mapped elsewhere.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(38,'1250','purchase_return_clearing','Purchase Return Clearing','asset','debit',1,1,1,1,'Temporary supplier recovery asset created when inventory is physically returned, before the return is fully settled.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(39,'1260','supplier_credit_receivable','Supplier Credit Receivable','asset','debit',1,1,1,1,'Reusable supplier credit available for application against another eligible purchase order.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(40,'1270','supplier_refund_receivable','Supplier Refund Receivable','asset','debit',1,1,1,1,'Cash/bank refund due from a supplier but not yet collected.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(41,'4200','purchase_return_valuation_gain','Purchase Return Valuation Gain','revenue','credit',17,1,1,1,'Gain when supplier return credit exceeds the inventory carrying value removed.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(42,'6800','purchase_return_valuation_loss','Purchase Return Valuation Loss','expense','debit',29,1,1,1,'Loss when inventory carrying value removed exceeds the supplier return credit.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(43,'6050','sales_discount','Sales Discount','expense','debit',29,1,1,1,'Discount given to customers on restaurant sales.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(44,'4020','service_charge_income','Service Charge Income','revenue','credit',17,1,1,1,'Service charge collected from restaurant customers.',NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(45,'Save',NULL,'Save','revenue','credit',17,0,1,1,NULL,1,1,'2026-10-07 01:08:27','2026-10-07 01:08:27',NULL);
/*!40000 ALTER TABLE `chart_of_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `last_visit_at` datetime DEFAULT NULL,
  `total_orders` int(10) unsigned NOT NULL DEFAULT 0,
  `total_spent` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_phone_unique` (`phone`),
  UNIQUE KEY `customers_email_unique` (`email`),
  KEY `customers_name_index` (`name`),
  KEY `customers_last_visit_at_index` (`last_visit_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,'Tasnima Akther Tisha','+1 (844) 536-1838','shraboniakter554@gmail.com','2026-10-25 05:05:18',0,750.00,1,NULL,'2026-10-06 22:19:01','2026-10-24 23:05:18',NULL),(2,'Tisha','07364466764',NULL,'2026-10-08 09:56:30',0,250.00,1,NULL,'2026-10-06 22:30:14','2026-10-08 03:56:30',NULL),(3,'Tasnima','+1 (646) 633-6464','shraboniakter@gmail.com',NULL,0,0.00,1,NULL,'2026-10-06 23:28:12','2026-10-06 23:28:12',NULL),(4,'Emerson Ballard','+1 (557) 252-9368','vyvygi@mailinator.com',NULL,0,0.00,1,NULL,'2026-10-08 01:15:13','2026-10-08 01:15:13',NULL),(5,'Jennifer Lester','+1 (512) 339-3656','hypi@mailinator.com',NULL,0,0.00,1,NULL,'2026-10-08 01:32:35','2026-10-08 01:32:35',NULL),(6,'Vaughan Roberson','+1 (528) 232-8797','jujo@mailinator.com','2026-10-15 03:48:27',0,750.00,1,NULL,'2026-10-11 04:38:17','2026-10-14 21:48:27',NULL),(7,'Demo Customer 001','8801901000001','demo-customer-001@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,'Demo Customer 002','8801901000002','demo-customer-002@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,'Demo Customer 003','8801901000003','demo-customer-003@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,'Demo Customer 004','8801901000004','demo-customer-004@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,'Demo Customer 005','8801901000005','demo-customer-005@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,'Demo Customer 006','8801901000006','demo-customer-006@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,'Demo Customer 007','8801901000007','demo-customer-007@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,'Demo Customer 008','8801901000008','demo-customer-008@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,'Demo Customer 009','8801901000009','demo-customer-009@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,'Demo Customer 010','8801901000010','demo-customer-010@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,'Demo Customer 011','8801901000011','demo-customer-011@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,'Demo Customer 012','8801901000012','demo-customer-012@local.test',NULL,0,0.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,'Demo Customer 013','8801901000013','demo-customer-013@local.test','2026-10-26 11:01:58',1,505.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,'Demo Customer 014','8801901000014','demo-customer-014@local.test','2026-10-26 11:00:58',1,530.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,'Demo Customer 015','8801901000015','demo-customer-015@local.test','2026-10-26 10:59:58',1,180.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,'Demo Customer 016','8801901000016','demo-customer-016@local.test','2026-10-26 10:58:58',1,205.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,'Demo Customer 017','8801901000017','demo-customer-017@local.test','2026-10-26 10:57:58',1,230.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,'Demo Customer 018','8801901000018','demo-customer-018@local.test','2026-10-26 10:56:58',1,255.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,'Demo Customer 019','8801901000019','demo-customer-019@local.test','2026-10-26 10:55:58',1,280.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,'Demo Customer 020','8801901000020','demo-customer-020@local.test','2026-10-26 10:54:58',1,305.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,'Demo Customer 021','8801901000021','demo-customer-021@local.test','2026-10-26 10:53:58',1,330.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,'Demo Customer 022','8801901000022','demo-customer-022@local.test','2026-10-26 10:52:58',1,355.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,'Demo Customer 023','8801901000023','demo-customer-023@local.test','2026-10-26 10:51:58',1,380.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,'Demo Customer 024','8801901000024','demo-customer-024@local.test','2026-10-26 10:50:58',1,405.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,'Demo Customer 025','8801901000025','demo-customer-025@local.test','2026-10-26 10:49:58',1,430.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,'Demo Customer 026','8801901000026','demo-customer-026@local.test','2026-10-26 10:48:58',1,455.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,'Demo Customer 027','8801901000027','demo-customer-027@local.test','2026-10-26 10:47:58',1,480.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,'Demo Customer 028','8801901000028','demo-customer-028@local.test','2026-10-26 10:46:58',1,505.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,'Demo Customer 029','8801901000029','demo-customer-029@local.test','2026-10-26 10:45:58',1,530.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,'Demo Customer 030','8801901000030','demo-customer-030@local.test','2026-10-26 10:44:58',1,180.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,'Demo Customer 031','8801901000031','demo-customer-031@local.test','2026-10-26 10:43:58',1,205.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,'Demo Customer 032','8801901000032','demo-customer-032@local.test','2026-10-26 10:42:58',1,230.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,'Demo Customer 033','8801901000033','demo-customer-033@local.test','2026-10-26 10:41:58',1,255.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,'Demo Customer 034','8801901000034','demo-customer-034@local.test','2026-10-26 10:40:58',1,280.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,'Demo Customer 035','8801901000035','demo-customer-035@local.test','2026-10-26 10:39:58',1,305.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,'Demo Customer 036','8801901000036','demo-customer-036@local.test','2026-10-26 11:13:58',1,330.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,'Demo Customer 037','8801901000037','demo-customer-037@local.test','2026-10-26 11:12:58',1,355.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,'Demo Customer 038','8801901000038','demo-customer-038@local.test','2026-10-26 11:11:58',1,380.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,'Demo Customer 039','8801901000039','demo-customer-039@local.test','2026-10-26 11:10:58',1,405.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,'Demo Customer 040','8801901000040','demo-customer-040@local.test','2026-10-26 11:09:58',1,430.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,'Demo Customer 041','8801901000041','demo-customer-041@local.test','2026-10-26 11:08:58',1,455.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,'Demo Customer 042','8801901000042','demo-customer-042@local.test','2026-10-26 11:07:58',1,480.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,'Demo Customer 043','8801901000043','demo-customer-043@local.test','2026-10-26 11:06:58',1,505.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,'Demo Customer 044','8801901000044','demo-customer-044@local.test','2026-10-26 11:05:58',1,530.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,'Demo Customer 045','8801901000045','demo-customer-045@local.test','2026-10-26 11:04:58',1,180.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,'Demo Customer 046','8801901000046','demo-customer-046@local.test','2026-10-26 11:03:58',1,205.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,'Demo Customer 047','8801901000047','demo-customer-047@local.test','2026-10-26 11:02:58',1,230.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,'Demo Customer 048','8801901000048','demo-customer-048@local.test','2026-10-26 11:01:58',1,255.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,'Demo Customer 049','8801901000049','demo-customer-049@local.test','2026-10-26 11:00:58',1,280.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,'Demo Customer 050','8801901000050','demo-customer-050@local.test','2026-10-26 10:59:58',1,305.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,'Demo Customer 051','8801901000051','demo-customer-051@local.test','2026-10-26 10:58:58',1,330.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,'Demo Customer 052','8801901000052','demo-customer-052@local.test','2026-10-26 10:57:58',1,355.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,'Demo Customer 053','8801901000053','demo-customer-053@local.test','2026-10-26 10:56:58',1,380.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,'Demo Customer 054','8801901000054','demo-customer-054@local.test','2026-10-26 10:55:58',1,405.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,'Demo Customer 055','8801901000055','demo-customer-055@local.test','2026-10-26 10:54:58',1,430.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,'Demo Customer 056','8801901000056','demo-customer-056@local.test','2026-10-26 10:53:58',1,455.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,'Demo Customer 057','8801901000057','demo-customer-057@local.test','2026-10-26 10:52:58',1,480.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,'Demo Customer 058','8801901000058','demo-customer-058@local.test','2026-10-26 10:51:58',1,505.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,'Demo Customer 059','8801901000059','demo-customer-059@local.test','2026-10-26 10:50:58',1,530.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,'Demo Customer 060','8801901000060','demo-customer-060@local.test','2026-10-26 10:49:58',1,180.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,'Demo Customer 061','8801901000061','demo-customer-061@local.test','2026-10-26 10:48:58',1,205.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,'Demo Customer 062','8801901000062','demo-customer-062@local.test','2026-10-26 10:47:58',1,230.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,'Demo Customer 063','8801901000063','demo-customer-063@local.test','2026-10-26 10:46:58',1,255.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,'Demo Customer 064','8801901000064','demo-customer-064@local.test','2026-10-26 10:45:58',1,280.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,'Demo Customer 065','8801901000065','demo-customer-065@local.test','2026-10-26 10:44:58',1,305.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,'Demo Customer 066','8801901000066','demo-customer-066@local.test','2026-10-26 10:43:58',1,330.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,'Demo Customer 067','8801901000067','demo-customer-067@local.test','2026-10-26 10:42:58',1,355.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,'Demo Customer 068','8801901000068','demo-customer-068@local.test','2026-10-26 10:41:58',1,380.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,'Demo Customer 069','8801901000069','demo-customer-069@local.test','2026-10-26 10:40:58',1,405.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,'Demo Customer 070','8801901000070','demo-customer-070@local.test','2026-10-26 10:39:58',1,430.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,'Demo Customer 071','8801901000071','demo-customer-071@local.test','2026-10-26 11:13:58',1,455.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,'Demo Customer 072','8801901000072','demo-customer-072@local.test','2026-10-26 11:12:58',1,480.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,'Demo Customer 073','8801901000073','demo-customer-073@local.test','2026-10-26 11:11:58',1,505.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,'Demo Customer 074','8801901000074','demo-customer-074@local.test','2026-10-26 11:10:58',1,530.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,'Demo Customer 075','8801901000075','demo-customer-075@local.test','2026-10-26 11:09:58',1,180.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,'Demo Customer 076','8801901000076','demo-customer-076@local.test','2026-10-26 11:08:58',1,205.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,'Demo Customer 077','8801901000077','demo-customer-077@local.test','2026-10-26 11:07:58',1,230.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,'Demo Customer 078','8801901000078','demo-customer-078@local.test','2026-10-26 11:06:58',1,255.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,'Demo Customer 079','8801901000079','demo-customer-079@local.test','2026-10-26 11:05:58',1,280.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,'Demo Customer 080','8801901000080','demo-customer-080@local.test','2026-10-26 11:04:58',1,305.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,'Demo Customer 081','8801901000081','demo-customer-081@local.test','2026-10-26 11:03:58',1,330.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,'Demo Customer 082','8801901000082','demo-customer-082@local.test','2026-10-26 11:02:58',1,355.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,'Demo Customer 083','8801901000083','demo-customer-083@local.test','2026-10-26 11:01:58',1,380.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,'Demo Customer 084','8801901000084','demo-customer-084@local.test','2026-10-26 11:00:58',1,405.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,'Demo Customer 085','8801901000085','demo-customer-085@local.test','2026-10-26 10:59:58',1,430.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,'Demo Customer 086','8801901000086','demo-customer-086@local.test','2026-10-26 10:58:58',1,455.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,'Demo Customer 087','8801901000087','demo-customer-087@local.test','2026-10-26 10:57:58',1,480.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,'Demo Customer 088','8801901000088','demo-customer-088@local.test','2026-10-26 10:56:58',1,505.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,'Demo Customer 089','8801901000089','demo-customer-089@local.test','2026-10-26 10:55:58',1,530.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,'Demo Customer 090','8801901000090','demo-customer-090@local.test','2026-10-26 10:54:58',1,180.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,'Demo Customer 091','8801901000091','demo-customer-091@local.test','2026-10-26 10:53:58',1,205.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,'Demo Customer 092','8801901000092','demo-customer-092@local.test','2026-10-26 10:52:58',1,230.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,'Demo Customer 093','8801901000093','demo-customer-093@local.test','2026-10-26 10:51:58',1,255.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,'Demo Customer 094','8801901000094','demo-customer-094@local.test','2026-10-26 10:50:58',1,280.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,'Demo Customer 095','8801901000095','demo-customer-095@local.test','2026-10-26 10:49:58',1,305.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,'Demo Customer 096','8801901000096','demo-customer-096@local.test','2026-10-26 10:48:58',1,330.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(103,'Demo Customer 097','8801901000097','demo-customer-097@local.test','2026-10-26 10:47:58',1,355.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(104,'Demo Customer 098','8801901000098','demo-customer-098@local.test','2026-10-26 10:46:58',1,380.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(105,'Demo Customer 099','8801901000099','demo-customer-099@local.test','2026-10-26 10:45:58',1,405.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(106,'Demo Customer 100','8801901000100','demo-customer-100@local.test','2026-10-26 10:44:58',1,430.00,1,'DEMO DATA: generated for local module preview.','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `joining_date` date NOT NULL,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_status` varchar(30) NOT NULL DEFAULT 'none',
  `status_updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_user_id_unique` (`user_id`),
  KEY `employees_created_by_foreign` (`created_by`),
  KEY `employees_updated_by_foreign` (`updated_by`),
  KEY `employees_joining_date_current_status_index` (`joining_date`,`current_status`),
  KEY `employees_current_status_status_updated_at_index` (`current_status`,`status_updated_at`),
  KEY `employees_phone_index` (`phone`),
  KEY `employees_current_status_index` (`current_status`),
  CONSTRAINT `employees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employees_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,2,'0138776655442',NULL,'2026-10-07',100.00,'absent','2026-10-11 10:06:59',1,1,'2026-10-06 19:11:32','2026-10-11 04:06:59',NULL),(2,3,'0138776655443',NULL,'2026-10-07',60.00,'absent','2026-10-11 10:06:59',1,1,'2026-10-06 21:16:16','2026-10-11 04:06:59',NULL),(3,4,'8801801000001',NULL,'2026-09-25',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(4,5,'8801801000002',NULL,'2026-09-24',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(5,6,'8801801000003',NULL,'2026-09-23',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(6,7,'8801801000004',NULL,'2026-09-22',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(7,8,'8801801000005',NULL,'2026-09-21',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,9,'8801801000006',NULL,'2026-09-20',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,10,'8801801000007',NULL,'2026-09-19',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,11,'8801801000008',NULL,'2026-09-18',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,12,'8801801000009',NULL,'2026-09-17',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,13,'8801801000010',NULL,'2026-09-16',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,14,'8801801000011',NULL,'2026-09-15',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,15,'8801801000012',NULL,'2026-09-14',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,16,'8801801000013',NULL,'2026-09-13',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,17,'8801801000014',NULL,'2026-09-12',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,18,'8801801000015',NULL,'2026-09-11',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,19,'8801801000016',NULL,'2026-09-10',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,20,'8801801000017',NULL,'2026-09-09',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,21,'8801801000018',NULL,'2026-09-08',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,22,'8801801000019',NULL,'2026-09-07',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,23,'8801801000020',NULL,'2026-09-06',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,24,'8801801000021',NULL,'2026-09-05',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,25,'8801801000022',NULL,'2026-09-04',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,26,'8801801000023',NULL,'2026-09-03',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,27,'8801801000024',NULL,'2026-09-02',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,28,'8801801000025',NULL,'2026-09-01',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,29,'8801801000026',NULL,'2026-08-31',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,30,'8801801000027',NULL,'2026-08-30',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,31,'8801801000028',NULL,'2026-08-29',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,32,'8801801000029',NULL,'2026-08-28',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,33,'8801801000030',NULL,'2026-08-27',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,34,'8801801000031',NULL,'2026-08-26',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,35,'8801801000032',NULL,'2026-08-25',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,36,'8801801000033',NULL,'2026-08-24',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,37,'8801801000034',NULL,'2026-08-23',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,38,'8801801000035',NULL,'2026-08-22',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,39,'8801801000036',NULL,'2026-08-21',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,40,'8801801000037',NULL,'2026-08-20',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,41,'8801801000038',NULL,'2026-08-19',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,42,'8801801000039',NULL,'2026-08-18',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,43,'8801801000040',NULL,'2026-08-17',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,44,'8801801000041',NULL,'2026-08-16',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,45,'8801801000042',NULL,'2026-08-15',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,46,'8801801000043',NULL,'2026-08-14',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,47,'8801801000044',NULL,'2026-08-13',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,48,'8801801000045',NULL,'2026-08-12',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,49,'8801801000046',NULL,'2026-08-11',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,50,'8801801000047',NULL,'2026-08-10',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,51,'8801801000048',NULL,'2026-08-09',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,52,'8801801000049',NULL,'2026-08-08',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,53,'8801801000050',NULL,'2026-08-07',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,54,'8801801000051',NULL,'2026-08-06',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,55,'8801801000052',NULL,'2026-08-05',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,56,'8801801000053',NULL,'2026-08-04',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,57,'8801801000054',NULL,'2026-08-03',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,58,'8801801000055',NULL,'2026-08-02',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,59,'8801801000056',NULL,'2026-08-01',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,60,'8801801000057',NULL,'2026-07-31',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,61,'8801801000058',NULL,'2026-07-30',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,62,'8801801000059',NULL,'2026-07-29',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,63,'8801801000060',NULL,'2026-07-28',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,64,'8801801000061',NULL,'2026-07-27',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,65,'8801801000062',NULL,'2026-07-26',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,66,'8801801000063',NULL,'2026-07-25',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,67,'8801801000064',NULL,'2026-07-24',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,68,'8801801000065',NULL,'2026-07-23',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,69,'8801801000066',NULL,'2026-07-22',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,70,'8801801000067',NULL,'2026-07-21',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,71,'8801801000068',NULL,'2026-07-20',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,72,'8801801000069',NULL,'2026-07-19',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,73,'8801801000070',NULL,'2026-07-18',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,74,'8801801000071',NULL,'2026-07-17',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,75,'8801801000072',NULL,'2026-07-16',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,76,'8801801000073',NULL,'2026-07-15',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,77,'8801801000074',NULL,'2026-07-14',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,78,'8801801000075',NULL,'2026-07-13',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,79,'8801801000076',NULL,'2026-07-12',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,80,'8801801000077',NULL,'2026-07-11',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,81,'8801801000078',NULL,'2026-07-10',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,82,'8801801000079',NULL,'2026-07-09',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,83,'8801801000080',NULL,'2026-07-08',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,84,'8801801000081',NULL,'2026-07-07',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,85,'8801801000082',NULL,'2026-07-06',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,86,'8801801000083',NULL,'2026-07-05',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,87,'8801801000084',NULL,'2026-07-04',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,88,'8801801000085',NULL,'2026-07-03',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,89,'8801801000086',NULL,'2026-07-02',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,90,'8801801000087',NULL,'2026-07-01',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,91,'8801801000088',NULL,'2026-06-30',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,92,'8801801000089',NULL,'2026-06-29',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,93,'8801801000090',NULL,'2026-06-28',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,94,'8801801000091',NULL,'2026-06-27',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,95,'8801801000092',NULL,'2026-06-26',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,96,'8801801000093',NULL,'2026-06-25',170.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,97,'8801801000094',NULL,'2026-06-24',180.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,98,'8801801000095',NULL,'2026-06-23',190.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,99,'8801801000096',NULL,'2026-06-22',120.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,101,'8801801000097',NULL,'2026-06-21',130.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,102,'8801801000098',NULL,'2026-06-20',140.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,103,'8801801000099',NULL,'2026-06-19',150.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,104,'8801801000100',NULL,'2026-06-18',160.00,'none',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expense_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `ledger_account_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_categories_name_unique` (`name`),
  KEY `expense_categories_is_active_index` (`is_active`),
  KEY `expense_categories_ledger_idx` (`ledger_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES (1,'Inventory',NULL,1,37,'2026-10-06 22:55:15','2026-10-06 22:55:39',NULL);
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_category_id` bigint(20) unsigned NOT NULL,
  `expense_date` datetime NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_source_unique` (`source_type`,`source_id`),
  KEY `expenses_expense_category_id_foreign` (`expense_category_id`),
  KEY `expenses_updated_by_foreign` (`updated_by`),
  KEY `expenses_expense_date_expense_category_id_index` (`expense_date`,`expense_category_id`),
  KEY `expenses_expense_date_payment_method_index` (`expense_date`,`payment_method`),
  KEY `expenses_created_by_expense_date_index` (`created_by`,`expense_date`),
  KEY `expenses_expense_date_index` (`expense_date`),
  KEY `expenses_payment_method_index` (`payment_method`),
  KEY `expenses_source_type_idx` (`source_type`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `expenses_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,1,'2026-10-07 00:00:00',9.00,'cash',NULL,NULL,NULL,1,1,'2026-10-06 22:55:39','2026-10-06 23:46:27',NULL),(2,1,'2026-10-25 10:43:58',300.00,'bank_transfer','DEMO100-Expense 001',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(3,1,'2026-10-24 10:43:58',350.00,'card','DEMO100-Expense 002',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(4,1,'2026-10-23 10:43:58',400.00,'cash','DEMO100-Expense 003',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(5,1,'2026-10-22 10:43:58',450.00,'bank_transfer','DEMO100-Expense 004',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(6,1,'2026-10-21 10:43:58',500.00,'card','DEMO100-Expense 005',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(7,1,'2026-10-20 10:43:58',550.00,'cash','DEMO100-Expense 006',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,1,'2026-10-19 10:43:58',600.00,'bank_transfer','DEMO100-Expense 007',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,1,'2026-10-18 10:43:58',650.00,'card','DEMO100-Expense 008',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,1,'2026-10-17 10:43:58',700.00,'cash','DEMO100-Expense 009',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,1,'2026-10-16 10:43:58',750.00,'bank_transfer','DEMO100-Expense 010',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,1,'2026-10-15 10:43:58',800.00,'card','DEMO100-Expense 011',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,1,'2026-10-14 10:43:58',850.00,'cash','DEMO100-Expense 012',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,1,'2026-10-13 10:43:58',900.00,'bank_transfer','DEMO100-Expense 013',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,1,'2026-10-12 10:43:58',950.00,'card','DEMO100-Expense 014',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,1,'2026-10-11 10:43:58',1000.00,'cash','DEMO100-Expense 015',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,1,'2026-10-10 10:43:58',1050.00,'bank_transfer','DEMO100-Expense 016',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,1,'2026-10-09 10:43:58',1100.00,'card','DEMO100-Expense 017',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,1,'2026-10-08 10:43:58',1150.00,'cash','DEMO100-Expense 018',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,1,'2026-10-07 10:43:58',1200.00,'bank_transfer','DEMO100-Expense 019',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,1,'2026-10-06 10:43:58',250.00,'card','DEMO100-Expense 020',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,1,'2026-10-05 10:43:58',300.00,'cash','DEMO100-Expense 021',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,1,'2026-10-04 10:43:58',350.00,'bank_transfer','DEMO100-Expense 022',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,1,'2026-10-03 10:43:58',400.00,'card','DEMO100-Expense 023',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,1,'2026-10-02 10:43:58',450.00,'cash','DEMO100-Expense 024',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,1,'2026-10-01 10:43:58',500.00,'bank_transfer','DEMO100-Expense 025',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,1,'2026-09-30 10:43:58',550.00,'card','DEMO100-Expense 026',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,1,'2026-09-29 10:43:58',600.00,'cash','DEMO100-Expense 027',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,1,'2026-09-28 10:43:58',650.00,'bank_transfer','DEMO100-Expense 028',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,1,'2026-09-27 10:43:58',700.00,'card','DEMO100-Expense 029',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,1,'2026-09-26 10:43:58',750.00,'cash','DEMO100-Expense 030',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,1,'2026-09-25 10:43:58',800.00,'bank_transfer','DEMO100-Expense 031',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,1,'2026-09-24 10:43:58',850.00,'card','DEMO100-Expense 032',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,1,'2026-09-23 10:43:58',900.00,'cash','DEMO100-Expense 033',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,1,'2026-09-22 10:43:58',950.00,'bank_transfer','DEMO100-Expense 034',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,1,'2026-09-21 10:43:58',1000.00,'card','DEMO100-Expense 035',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,1,'2026-09-20 10:43:58',1050.00,'cash','DEMO100-Expense 036',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,1,'2026-09-19 10:43:58',1100.00,'bank_transfer','DEMO100-Expense 037',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,1,'2026-09-18 10:43:58',1150.00,'card','DEMO100-Expense 038',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,1,'2026-09-17 10:43:58',1200.00,'cash','DEMO100-Expense 039',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,1,'2026-09-16 10:43:58',250.00,'bank_transfer','DEMO100-Expense 040',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,1,'2026-09-15 10:43:58',300.00,'card','DEMO100-Expense 041',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,1,'2026-09-14 10:43:58',350.00,'cash','DEMO100-Expense 042',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,1,'2026-09-13 10:43:58',400.00,'bank_transfer','DEMO100-Expense 043',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,1,'2026-09-12 10:43:58',450.00,'card','DEMO100-Expense 044',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,1,'2026-09-11 10:43:58',500.00,'cash','DEMO100-Expense 045',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,1,'2026-09-10 10:43:58',550.00,'bank_transfer','DEMO100-Expense 046',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,1,'2026-09-09 10:43:58',600.00,'card','DEMO100-Expense 047',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,1,'2026-09-08 10:43:58',650.00,'cash','DEMO100-Expense 048',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,1,'2026-09-07 10:43:58',700.00,'bank_transfer','DEMO100-Expense 049',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,1,'2026-09-06 10:43:58',750.00,'card','DEMO100-Expense 050',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,1,'2026-09-05 10:43:58',800.00,'cash','DEMO100-Expense 051',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,1,'2026-09-04 10:43:58',850.00,'bank_transfer','DEMO100-Expense 052',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,1,'2026-09-03 10:43:58',900.00,'card','DEMO100-Expense 053',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,1,'2026-09-02 10:43:58',950.00,'cash','DEMO100-Expense 054',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,1,'2026-09-01 10:43:58',1000.00,'bank_transfer','DEMO100-Expense 055',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,1,'2026-08-31 10:43:58',1050.00,'card','DEMO100-Expense 056',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,1,'2026-08-30 10:43:58',1100.00,'cash','DEMO100-Expense 057',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,1,'2026-08-29 10:43:58',1150.00,'bank_transfer','DEMO100-Expense 058',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,1,'2026-08-28 10:43:58',1200.00,'card','DEMO100-Expense 059',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,1,'2026-10-26 10:43:58',250.00,'cash','DEMO100-Expense 060',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,1,'2026-10-25 10:43:58',300.00,'bank_transfer','DEMO100-Expense 061',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,1,'2026-10-24 10:43:58',350.00,'card','DEMO100-Expense 062',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,1,'2026-10-23 10:43:58',400.00,'cash','DEMO100-Expense 063',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,1,'2026-10-22 10:43:58',450.00,'bank_transfer','DEMO100-Expense 064',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,1,'2026-10-21 10:43:58',500.00,'card','DEMO100-Expense 065',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,1,'2026-10-20 10:43:58',550.00,'cash','DEMO100-Expense 066',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,1,'2026-10-19 10:43:58',600.00,'bank_transfer','DEMO100-Expense 067',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,1,'2026-10-18 10:43:58',650.00,'card','DEMO100-Expense 068',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,1,'2026-10-17 10:43:58',700.00,'cash','DEMO100-Expense 069',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,1,'2026-10-16 10:43:58',750.00,'bank_transfer','DEMO100-Expense 070',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,1,'2026-10-15 10:43:58',800.00,'card','DEMO100-Expense 071',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,1,'2026-10-14 10:43:58',850.00,'cash','DEMO100-Expense 072',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,1,'2026-10-13 10:43:58',900.00,'bank_transfer','DEMO100-Expense 073',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,1,'2026-10-12 10:43:58',950.00,'card','DEMO100-Expense 074',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,1,'2026-10-11 10:43:58',1000.00,'cash','DEMO100-Expense 075',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,1,'2026-10-10 10:43:58',1050.00,'bank_transfer','DEMO100-Expense 076',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,1,'2026-10-09 10:43:58',1100.00,'card','DEMO100-Expense 077',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,1,'2026-10-08 10:43:58',1150.00,'cash','DEMO100-Expense 078',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,1,'2026-10-07 10:43:58',1200.00,'bank_transfer','DEMO100-Expense 079',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,1,'2026-10-06 10:43:58',250.00,'card','DEMO100-Expense 080',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,1,'2026-10-05 10:43:58',300.00,'cash','DEMO100-Expense 081',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,1,'2026-10-04 10:43:58',350.00,'bank_transfer','DEMO100-Expense 082',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,1,'2026-10-03 10:43:58',400.00,'card','DEMO100-Expense 083',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,1,'2026-10-02 10:43:58',450.00,'cash','DEMO100-Expense 084',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,1,'2026-10-01 10:43:58',500.00,'bank_transfer','DEMO100-Expense 085',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,1,'2026-09-30 10:43:58',550.00,'card','DEMO100-Expense 086',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,1,'2026-09-29 10:43:58',600.00,'cash','DEMO100-Expense 087',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,1,'2026-09-28 10:43:58',650.00,'bank_transfer','DEMO100-Expense 088',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,1,'2026-09-27 10:43:58',700.00,'card','DEMO100-Expense 089',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,1,'2026-09-26 10:43:58',750.00,'cash','DEMO100-Expense 090',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,1,'2026-09-25 10:43:58',800.00,'bank_transfer','DEMO100-Expense 091',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,1,'2026-09-24 10:43:58',850.00,'card','DEMO100-Expense 092',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,1,'2026-09-23 10:43:58',900.00,'cash','DEMO100-Expense 093',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,1,'2026-09-22 10:43:58',950.00,'bank_transfer','DEMO100-Expense 094',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,1,'2026-09-21 10:43:58',1000.00,'card','DEMO100-Expense 095',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,1,'2026-09-20 10:43:58',1050.00,'cash','DEMO100-Expense 096',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,1,'2026-09-19 10:43:58',1100.00,'bank_transfer','DEMO100-Expense 097',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,1,'2026-09-18 10:43:58',1150.00,'card','DEMO100-Expense 098',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,1,'2026-09-17 10:43:58',1200.00,'cash','DEMO100-Expense 099',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,1,'2026-09-16 10:43:58',250.00,'bank_transfer','DEMO100-Expense 100',NULL,NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income_categories`
--

DROP TABLE IF EXISTS `income_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `ledger_account_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `income_categories_active_idx` (`is_active`),
  KEY `income_categories_ledger_idx` (`ledger_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income_categories`
--

LOCK TABLES `income_categories` WRITE;
/*!40000 ALTER TABLE `income_categories` DISABLE KEYS */;
INSERT INTO `income_categories` VALUES (1,'Bottle / Crate Sale','Income from selling accumulated bottles, empty crates or similar reusable containers.',1,20,NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(2,'Waste Paper Sale','Income from selling accumulated waste paper.',1,21,NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(3,'Carton Sale','Income from selling accumulated cartons or packaging materials.',1,22,NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(4,'Scrap Sale','Income from selling scrap metal, plastic or other scrap materials.',1,23,NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(5,'Used Cooking Oil Sale','Income from selling used cooking oil where legally and operationally applicable.',1,24,NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(6,'Recyclable Waste Sale','Income from selling other recyclable restaurant waste.',1,25,NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL),(7,'Miscellaneous Other Income','Other non-POS restaurant income that does not belong to another category.',1,26,NULL,NULL,'2026-10-06 13:10:41','2026-10-06 13:10:41',NULL);
/*!40000 ALTER TABLE `income_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incomes`
--

DROP TABLE IF EXISTS `incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incomes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `income_category_id` bigint(20) unsigned NOT NULL,
  `category_name_snapshot` varchar(150) NOT NULL,
  `income_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `incomes_source_unique` (`source_type`,`source_id`),
  KEY `incomes_category_idx` (`income_category_id`),
  KEY `incomes_date_idx` (`income_date`),
  KEY `incomes_payment_method_idx` (`payment_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incomes`
--

LOCK TABLES `incomes` WRITE;
/*!40000 ALTER TABLE `incomes` DISABLE KEYS */;
INSERT INTO `incomes` VALUES (1,4,'Scrap Sale','2026-10-07',130.00,'nagad',NULL,NULL,NULL,NULL,1,1,'2026-10-07 01:04:10','2026-10-07 01:04:10',NULL);
/*!40000 ALTER TABLE `incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_wastage_items`
--

DROP TABLE IF EXISTS `inventory_wastage_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_wastage_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_wastage_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `unit_cost` decimal(18,4) unsigned NOT NULL,
  `total_loss` decimal(18,4) unsigned NOT NULL,
  `restaurant_quantity_before` decimal(18,4) unsigned NOT NULL,
  `restaurant_quantity_after` decimal(18,4) unsigned NOT NULL,
  `reason` varchar(60) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_wastage_items_raw_material_id_index` (`raw_material_id`),
  KEY `inventory_wastage_items_reason_index` (`reason`),
  KEY `wastage_item_header_material_index` (`inventory_wastage_id`,`raw_material_id`),
  CONSTRAINT `inventory_wastage_items_inventory_wastage_id_foreign` FOREIGN KEY (`inventory_wastage_id`) REFERENCES `inventory_wastages` (`id`),
  CONSTRAINT `inventory_wastage_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_wastage_items`
--

LOCK TABLES `inventory_wastage_items` WRITE;
/*!40000 ALTER TABLE `inventory_wastage_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_wastage_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_wastages`
--

DROP TABLE IF EXISTS `inventory_wastages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_wastages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wastage_no` varchar(80) NOT NULL,
  `wasted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_loss` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `notes` text DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_wastages_wastage_no_unique` (`wastage_no`),
  KEY `inventory_wastages_created_by_foreign` (`created_by`),
  KEY `inventory_wastages_wasted_at_index` (`wasted_at`),
  KEY `inventory_wastages_recorded_by_index` (`recorded_by`),
  CONSTRAINT `inventory_wastages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_wastages_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_wastages`
--

LOCK TABLES `inventory_wastages` WRITE;
/*!40000 ALTER TABLE `inventory_wastages` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_wastages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `journal_number` varchar(40) DEFAULT NULL,
  `journal_date` date NOT NULL,
  `entry_type` varchar(20) NOT NULL DEFAULT 'manual',
  `status` varchar(20) NOT NULL DEFAULT 'posted',
  `description` text NOT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `source_revision` int(10) unsigned DEFAULT NULL,
  `source_hash` varchar(64) DEFAULT NULL,
  `reversal_of_id` bigint(20) unsigned DEFAULT NULL,
  `total_debit` decimal(16,2) NOT NULL,
  `total_credit` decimal(16,2) NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `posted_by` bigint(20) unsigned NOT NULL,
  `reversed_at` timestamp NULL DEFAULT NULL,
  `reversed_by` bigint(20) unsigned DEFAULT NULL,
  `reversal_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_source_revision_unique` (`source_type`,`source_id`,`source_revision`),
  UNIQUE KEY `journal_entries_journal_number_unique` (`journal_number`),
  UNIQUE KEY `journal_entries_reversal_of_id_unique` (`reversal_of_id`),
  KEY `journal_entries_date_idx` (`journal_date`),
  KEY `journal_entries_status_idx` (`status`),
  KEY `journal_entries_type_idx` (`entry_type`),
  KEY `journal_entries_source_idx` (`source_type`,`source_id`),
  KEY `journal_entries_source_hash_idx` (`source_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
INSERT INTO `journal_entries` VALUES (1,'JV-202610-000001','2026-10-07','source','posted','Recipe Consumption — ORD-20261007-HNDBAG — Batch #1','recipe_consumption',1,1,'0e4ca5c4e3f6bc71181ca0641c42cc386a618a01478af010f88b7bf750343a7c',NULL,1.00,1.00,'2026-10-07 02:19:49',2,NULL,NULL,NULL,'2026-10-06 20:19:49','2026-10-06 20:19:49'),(2,'JV-202610-000002','2026-10-07','source','posted','Restaurant Sale — ORD-20261007-HNDBAG','order_sale',1,1,'2abfef2ec3519e96f45f3ac8e107087eeb146fb783264b1ad560277b0cbf94a4',NULL,250.00,250.00,'2026-10-07 03:02:48',1,NULL,NULL,NULL,'2026-10-06 21:02:48','2026-10-06 21:02:48'),(3,'JV-202610-000003','2026-10-07','source','reversed','Inventory','expense',1,1,'26bc757d196250f2b1d4b08c8405076cfe42cdde1795f4e1925fd9914c4489e9',NULL,10.00,10.00,'2026-10-07 05:46:27',1,'2026-10-06 23:46:27',1,'Expense transaction was edited.','2026-10-06 22:55:39','2026-10-06 23:46:27'),(4,'JV-202610-000004','2026-10-07','source','posted','Recipe Consumption — ORD-20261007-F8NBBO — Batch #1','recipe_consumption',2,1,'2de20a1b6e90f0a315c357b98ecece7705aa44076aa34acb6e196d73dce06bd6',NULL,1.00,1.00,'2026-10-07 05:11:38',2,NULL,NULL,NULL,'2026-10-06 23:11:38','2026-10-06 23:11:38'),(5,'JV-202610-000005','2026-10-07','source','posted','Restaurant Sale — ORD-20261007-F8NBBO','order_sale',2,1,'244e83b97dfdca8c12a1fb5331150b3290c8d7a416c29e6ee889640732f7f0c7',NULL,250.00,250.00,'2026-10-07 05:26:02',1,NULL,NULL,NULL,'2026-10-06 23:26:02','2026-10-06 23:26:02'),(6,'JV-202610-000006','2026-10-07','reversal','posted','REVERSAL: JV-202610-000003 — Expense transaction was edited.',NULL,NULL,NULL,NULL,3,10.00,10.00,'2026-10-07 05:46:27',1,NULL,NULL,NULL,'2026-10-06 23:46:27','2026-10-06 23:46:27'),(7,'JV-202610-000007','2026-10-07','source','posted','Inventory','expense',1,2,'12c404ae4129a1d52149ae3bf9f9f3fdeebd896e2c3b5e93f5670652f7d3b969',NULL,9.00,9.00,'2026-10-07 05:46:27',1,NULL,NULL,NULL,'2026-10-06 23:46:27','2026-10-06 23:46:27'),(8,'JV-202610-000008','2026-10-07','source','posted','Supplier Payment — PO #1','purchase_payment',1,1,'56cace40b3264547aae851f73804f1ed7ce093bb202f3fe9f93be09dbccbe12c',NULL,1.00,1.00,'2026-10-07 06:42:58',1,NULL,NULL,NULL,'2026-10-07 00:42:58','2026-10-07 00:42:58'),(9,'JV-202610-000009','2026-10-07','source','posted','Other Income — Scrap Sale','income',1,1,'2bfa5c7b14a4d76ab105fdbf3c0e8efefc340b72eb8f08a2bd9a00ffb6bbd0d5',NULL,130.00,130.00,'2026-10-07 07:04:10',1,NULL,NULL,NULL,'2026-10-07 01:04:10','2026-10-07 01:04:10'),(10,'JV-202610-000010','2026-10-08','source','posted','Supplier Payment — PO #2','purchase_payment',2,1,'210d9be9e2645eb15bfc662be723ed1c55d2fed3603491d9f3421e1058f7795d',NULL,1.00,1.00,'2026-10-08 07:46:11',1,NULL,NULL,NULL,'2026-10-08 01:46:11','2026-10-08 01:46:11'),(11,'JV-202610-000011','2026-10-08','source','posted','Supplier Payment — PO #3','purchase_payment',3,1,'2c891d82cbb1f71124fe7ea2ac7241d1bf974b945b8830849c62e78d833ff130',NULL,5.00,5.00,'2026-10-08 09:01:03',1,NULL,NULL,NULL,'2026-10-08 03:01:03','2026-10-08 03:01:03'),(12,'JV-202610-000012','2026-10-08','source','posted','Supplier Payment — PO #3','purchase_payment',4,1,'52480292a87644cc4008d68e6ede34e210ca5108f4293edfe132b1a17b5dded4',NULL,15.00,15.00,'2026-10-08 09:01:58',1,NULL,NULL,NULL,'2026-10-08 03:01:58','2026-10-08 03:01:58'),(13,'JV-202610-000013','2026-10-08','source','posted','GRN GRN-20261008-000008 — PO #3','purchase_receipt',8,1,'2c246e75f6386e7276799876e97897dae7bfb3dcb7c037cd90667488d2ed3e36',NULL,20.00,20.00,'2026-10-08 09:01:58',1,NULL,NULL,NULL,'2026-10-08 03:01:58','2026-10-08 03:01:58'),(14,'JV-202610-000014','2026-10-08','source','posted','Recipe Consumption — ORD-20261008-WGV5O2 — Batch #1','recipe_consumption',3,1,'28959e5f9a998a79d7bd3eb361ce5c21bbadcb3b69adbd2bde7efed8450d34f8',NULL,1.00,1.00,'2026-10-08 09:55:40',2,NULL,NULL,NULL,'2026-10-08 03:55:40','2026-10-08 03:55:40'),(15,'JV-202610-000015','2026-10-08','source','posted','Restaurant Sale — ORD-20261008-WGV5O2','order_sale',4,1,'7c2b37b1fd43fcf7b50e6a0f370a31b0270d14be17d793f2c67ee7fd2bd5fcdc',NULL,250.00,250.00,'2026-10-08 09:56:30',1,NULL,NULL,NULL,'2026-10-08 03:56:30','2026-10-08 03:56:30'),(16,'JV-202610-000016','2026-10-11','source','posted','GRN GRN-20261011-000009 — PO #4','purchase_receipt',9,1,'a6acb0a0680bfbd35bae2693d6ba50c3e7a1c56fa3f5f88c75537cac52eb7efc',NULL,100.00,100.00,'2026-10-11 06:23:36',1,NULL,NULL,NULL,'2026-10-11 00:23:36','2026-10-11 00:23:36'),(17,'JV-202610-000017','2026-10-11','source','posted','GRN GRN-20261011-000013 — PO #7','purchase_receipt',13,1,'d4a33b65b08281eeab0ad702208f80443348c6a65f0be8746585a25deea1fca3',NULL,100.00,100.00,'2026-10-11 06:26:02',1,NULL,NULL,NULL,'2026-10-11 00:26:02','2026-10-11 00:26:02'),(18,'JV-202610-000018','2026-10-13','source','posted','GRN GRN-20261013-000015 — PO #8','purchase_receipt',15,1,'0d756de73d7d7fe48d433f651f5444b050db41d984e71a2d9f8bd800ea7831d5',NULL,100.00,100.00,'2026-10-13 07:27:44',1,NULL,NULL,NULL,'2026-10-13 01:27:44','2026-10-13 01:27:44'),(19,'JV-202610-000019','2026-10-13','source','posted','Recipe Consumption — ORD-20261013-RPJBSG — Batch #1','recipe_consumption',4,1,'4c1e2cb5571d7562b37440ba95896c121920f4014ff2d87407412af7e81d567d',NULL,1.00,1.00,'2026-10-13 15:11:32',2,NULL,NULL,NULL,'2026-10-13 09:11:32','2026-10-13 09:11:32'),(20,'JV-202610-000020','2026-10-13','source','posted','Restaurant Sale — ORD-20261013-RPJBSG','order_sale',5,1,'bfac7ddf6137fe050533e126864f9b33004475476ca8954c79a9695cb9016496',NULL,250.00,250.00,'2026-10-13 15:11:59',1,NULL,NULL,NULL,'2026-10-13 09:11:59','2026-10-13 09:11:59'),(21,'JV-202610-000021','2026-10-14','source','posted','GRN GRN-20261014-000016 — PO #9','purchase_receipt',16,1,'c84f471b1174d6e0ba88b08776e4555368fc017876e15aed0a436785fafd1925',NULL,100.00,100.00,'2026-10-14 08:25:49',1,NULL,NULL,NULL,'2026-10-14 02:25:49','2026-10-14 02:25:49'),(22,'JV-202610-000022','2026-10-15','source','posted','Recipe Consumption — ORD-20261015-VZ5STH — Batch #1','recipe_consumption',5,1,'bbcc0be90349d365342f129b7ae5ecbc0370f668fd6b1117167ebfbd8894454b',NULL,3.00,3.00,'2026-10-15 03:47:57',2,NULL,NULL,NULL,'2026-10-14 21:47:57','2026-10-14 21:47:57'),(23,'JV-202610-000023','2026-10-15','source','posted','Restaurant Sale — ORD-20261015-VZ5STH','order_sale',6,1,'3cb0c52f84c90c6e43fdf1b40dd5d4365f56a9e6d5eebe771752dd10affd34c8',NULL,750.00,750.00,'2026-10-15 03:48:27',1,NULL,NULL,NULL,'2026-10-14 21:48:27','2026-10-14 21:48:27'),(24,'JV-202610-000024','2026-10-25','source','posted','Recipe Consumption — ORD-20261025-LLNHH1 — Batch #1','recipe_consumption',6,1,'877848d321ce0247aba5a639514c9a9f29655120ddcfac4d20ea6c87b586b3f5',NULL,1.00,1.00,'2026-10-25 04:34:27',2,NULL,NULL,NULL,'2026-10-24 22:34:27','2026-10-24 22:34:27'),(25,'JV-202610-000025','2026-10-25','source','posted','Restaurant Sale — ORD-20261025-LLNHH1','order_sale',7,1,'50fb2dbd0366756a3ae3f7ffcac0131b5d09512ea47fa52c2ec8b4ab7e10f262',NULL,250.00,250.00,'2026-10-25 05:05:18',1,NULL,NULL,NULL,'2026-10-24 23:05:18','2026-10-24 23:05:18'),(26,'JV-202610-000026','2026-10-26','source','posted','Recipe Consumption — ORD-20261025-QKLF8J — Batch #1','recipe_consumption',7,1,'aec7c0ad1fb6707c3e41e6d49cbb89cc54d2a11286a25f31773d9d1348ee61de',NULL,1.00,1.00,'2026-10-26 08:05:19',2,NULL,NULL,NULL,'2026-10-26 02:05:19','2026-10-26 02:05:19'),(27,'JV-202610-000027','2026-10-26','source','posted','GRN GRN-20261026-000017 — PO #10','purchase_receipt',17,1,'1a6bf7040f93433113c3d0770103d1c139eda37a33ba6e799f9a0734ea76a0c4',NULL,10.00,10.00,'2026-10-26 08:14:10',1,NULL,NULL,NULL,'2026-10-26 02:14:10','2026-10-26 02:14:10'),(28,'JV-202610-000028','2026-10-26','source','posted','Recipe Consumption — ORD-20261026-SXGNQX — Batch #1','recipe_consumption',8,1,'669aaf579762d1977f581f49775fa60114bd7c8551e5588a144fafbc008b96c1',NULL,1.00,1.00,'2026-10-26 08:24:29',2,NULL,NULL,NULL,'2026-10-26 02:24:29','2026-10-26 02:24:29'),(29,'JV-202610-000029','2026-10-26','source','posted','GRN GRN-20261026-000018 — PO #111','purchase_receipt',18,1,'e09697db86cb1aef14b4d179610b57b9af9858f3bb57237cc49c5d090f601032',NULL,100.00,100.00,'2026-10-26 10:52:32',1,NULL,NULL,NULL,'2026-10-26 04:52:32','2026-10-26 04:52:32'),(30,'JV-202610-000030','2026-10-26','source','posted','Recipe Consumption — ORD-20261026-GGCQ4L — Batch #1','recipe_consumption',9,1,'448a16e1ddf548065e3cdde1fbd95fb4781fb7fda0728462684868e52ccae955',NULL,100.00,100.00,'2026-10-26 11:02:22',2,NULL,NULL,NULL,'2026-10-26 05:02:22','2026-10-26 05:02:22');
/*!40000 ALTER TABLE `journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_lines`
--

DROP TABLE IF EXISTS `journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint(20) unsigned NOT NULL,
  `ledger_account_id` bigint(20) unsigned NOT NULL,
  `account_code_snapshot` varchar(20) NOT NULL,
  `account_name_snapshot` varchar(150) NOT NULL,
  `account_type_snapshot` varchar(20) NOT NULL,
  `normal_balance_snapshot` varchar(10) NOT NULL,
  `debit` decimal(16,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(16,2) NOT NULL DEFAULT 0.00,
  `memo` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_lines_entry_idx` (`journal_entry_id`),
  KEY `journal_lines_account_idx` (`ledger_account_id`),
  KEY `journal_lines_account_entry_idx` (`ledger_account_id`,`journal_entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_lines`
--

LOCK TABLES `journal_lines` WRITE;
/*!40000 ALTER TABLE `journal_lines` DISABLE KEYS */;
INSERT INTO `journal_lines` VALUES (1,1,28,'5010','Food & Ingredient COGS','cogs','debit',1.00,0.00,'Food cost: Burger Test Batch 1 pcs','2026-10-06 20:19:50','2026-10-06 20:19:50'),(2,1,10,'1200','Inventory','asset','debit',0.00,1.00,'Inventory consumed by kitchen recipe production.','2026-10-06 20:19:50','2026-10-06 20:19:50'),(3,2,5,'1040','bKash Wallet','asset','debit',250.00,0.00,'Customer payment received via bKash','2026-10-06 21:02:48','2026-10-06 21:02:48'),(4,2,18,'4010','Restaurant Sales Revenue','revenue','credit',0.00,250.00,'Restaurant food sales revenue for ORD-20261007-HNDBAG','2026-10-06 21:02:48','2026-10-06 21:02:48'),(5,3,37,'6900','Miscellaneous Operating Expense','expense','debit',10.00,0.00,'Inventory','2026-10-06 22:55:39','2026-10-06 22:55:39'),(6,3,2,'1010','Cash in Hand','asset','debit',0.00,10.00,'Paid via Cash','2026-10-06 22:55:39','2026-10-06 22:55:39'),(7,4,28,'5010','Food & Ingredient COGS','cogs','debit',1.00,0.00,'Food cost: Burger Test Batch 1 pcs','2026-10-06 23:11:38','2026-10-06 23:11:38'),(8,4,10,'1200','Inventory','asset','debit',0.00,1.00,'Inventory consumed by kitchen recipe production.','2026-10-06 23:11:38','2026-10-06 23:11:38'),(9,5,2,'1010','Cash in Hand','asset','debit',250.00,0.00,'Customer payment received via Cash','2026-10-06 23:26:03','2026-10-06 23:26:03'),(10,5,18,'4010','Restaurant Sales Revenue','revenue','credit',0.00,250.00,'Restaurant food sales revenue for ORD-20261007-F8NBBO','2026-10-06 23:26:03','2026-10-06 23:26:03'),(11,6,37,'6900','Miscellaneous Operating Expense','expense','debit',0.00,10.00,'Reversal of line #5 — Inventory','2026-10-06 23:46:27','2026-10-06 23:46:27'),(12,6,2,'1010','Cash in Hand','asset','debit',10.00,0.00,'Reversal of line #6 — Paid via Cash','2026-10-06 23:46:27','2026-10-06 23:46:27'),(13,7,37,'6900','Miscellaneous Operating Expense','expense','debit',9.00,0.00,'Inventory','2026-10-06 23:46:27','2026-10-06 23:46:27'),(14,7,2,'1010','Cash in Hand','asset','debit',0.00,9.00,'Paid via Cash','2026-10-06 23:46:27','2026-10-06 23:46:27'),(15,8,12,'2010','Accounts Payable','liability','credit',1.00,0.00,'Supplier payable reduced','2026-10-07 00:42:58','2026-10-07 00:42:58'),(16,8,2,'1010','Cash in Hand','asset','debit',0.00,1.00,'Paid via Cash','2026-10-07 00:42:58','2026-10-07 00:42:58'),(17,9,6,'1050','Nagad Wallet','asset','debit',130.00,0.00,'Amount received via Nagad','2026-10-07 01:04:10','2026-10-07 01:04:10'),(18,9,23,'4140','Scrap Income','revenue','credit',0.00,130.00,'Scrap Sale','2026-10-07 01:04:10','2026-10-07 01:04:10'),(19,10,12,'2010','Accounts Payable','liability','credit',1.00,0.00,'Supplier payable reduced','2026-10-08 01:46:12','2026-10-08 01:46:12'),(20,10,2,'1010','Cash in Hand','asset','debit',0.00,1.00,'Paid via Cash','2026-10-08 01:46:12','2026-10-08 01:46:12'),(21,11,12,'2010','Accounts Payable','liability','credit',5.00,0.00,'Supplier payable reduced','2026-10-08 03:01:03','2026-10-08 03:01:03'),(22,11,4,'1030','Card Settlement Account','asset','debit',0.00,5.00,'Paid via Card','2026-10-08 03:01:03','2026-10-08 03:01:03'),(23,12,12,'2010','Accounts Payable','liability','credit',15.00,0.00,'Supplier payable reduced','2026-10-08 03:01:58','2026-10-08 03:01:58'),(24,12,4,'1030','Card Settlement Account','asset','debit',0.00,15.00,'Paid via Card','2026-10-08 03:01:58','2026-10-08 03:01:58'),(25,13,10,'1200','Inventory','asset','debit',20.00,0.00,'Inventory received via GRN-20261008-000008','2026-10-08 03:01:58','2026-10-08 03:01:58'),(26,13,12,'2010','Accounts Payable','liability','credit',0.00,20.00,'Supplier liability created from GRN-20261008-000008','2026-10-08 03:01:58','2026-10-08 03:01:58'),(27,14,28,'5010','Food & Ingredient COGS','cogs','debit',1.00,0.00,'Food cost: Burger Test Batch 1 pcs','2026-10-08 03:55:40','2026-10-08 03:55:40'),(28,14,10,'1200','Inventory','asset','debit',0.00,1.00,'Inventory consumed by kitchen recipe production.','2026-10-08 03:55:40','2026-10-08 03:55:40'),(29,15,2,'1010','Cash in Hand','asset','debit',250.00,0.00,'Customer payment received via Cash','2026-10-08 03:56:30','2026-10-08 03:56:30'),(30,15,18,'4010','Restaurant Sales Revenue','revenue','credit',0.00,250.00,'Restaurant food sales revenue for ORD-20261008-WGV5O2','2026-10-08 03:56:30','2026-10-08 03:56:30'),(31,16,10,'1200','Inventory','asset','debit',100.00,0.00,'Inventory received via GRN-20261011-000009','2026-10-11 00:23:37','2026-10-11 00:23:37'),(32,16,12,'2010','Accounts Payable','liability','credit',0.00,100.00,'Supplier liability created from GRN-20261011-000009','2026-10-11 00:23:37','2026-10-11 00:23:37'),(33,17,10,'1200','Inventory','asset','debit',100.00,0.00,'Inventory received via GRN-20261011-000013','2026-10-11 00:26:02','2026-10-11 00:26:02'),(34,17,12,'2010','Accounts Payable','liability','credit',0.00,100.00,'Supplier liability created from GRN-20261011-000013','2026-10-11 00:26:02','2026-10-11 00:26:02'),(35,18,10,'1200','Inventory','asset','debit',100.00,0.00,'Inventory received via GRN-20261013-000015','2026-10-13 01:27:45','2026-10-13 01:27:45'),(36,18,12,'2010','Accounts Payable','liability','credit',0.00,100.00,'Supplier liability created from GRN-20261013-000015','2026-10-13 01:27:45','2026-10-13 01:27:45'),(37,19,28,'5010','Food & Ingredient COGS','cogs','debit',1.00,0.00,'Food cost: Burger Test Batch 1 pcs','2026-10-13 09:11:32','2026-10-13 09:11:32'),(38,19,10,'1200','Inventory','asset','debit',0.00,1.00,'Inventory consumed by kitchen recipe production.','2026-10-13 09:11:32','2026-10-13 09:11:32'),(39,20,2,'1010','Cash in Hand','asset','debit',250.00,0.00,'Customer payment received via Cash','2026-10-13 09:11:59','2026-10-13 09:11:59'),(40,20,18,'4010','Restaurant Sales Revenue','revenue','credit',0.00,250.00,'Restaurant food sales revenue for ORD-20261013-RPJBSG','2026-10-13 09:11:59','2026-10-13 09:11:59'),(41,21,10,'1200','Inventory','asset','debit',100.00,0.00,'Inventory received via GRN-20261014-000016','2026-10-14 02:25:49','2026-10-14 02:25:49'),(42,21,12,'2010','Accounts Payable','liability','credit',0.00,100.00,'Supplier liability created from GRN-20261014-000016','2026-10-14 02:25:49','2026-10-14 02:25:49'),(43,22,28,'5010','Food & Ingredient COGS','cogs','debit',3.00,0.00,'Food cost: Burger Test Batch 3 pcs','2026-10-14 21:47:58','2026-10-14 21:47:58'),(44,22,10,'1200','Inventory','asset','debit',0.00,3.00,'Inventory consumed by kitchen recipe production.','2026-10-14 21:47:58','2026-10-14 21:47:58'),(45,23,2,'1010','Cash in Hand','asset','debit',750.00,0.00,'Customer payment received via Cash','2026-10-14 21:48:27','2026-10-14 21:48:27'),(46,23,18,'4010','Restaurant Sales Revenue','revenue','credit',0.00,750.00,'Restaurant food sales revenue for ORD-20261015-VZ5STH','2026-10-14 21:48:27','2026-10-14 21:48:27'),(47,24,28,'5010','Food & Ingredient COGS','cogs','debit',1.00,0.00,'Food cost: Burger Test Batch 1 pcs','2026-10-24 22:34:28','2026-10-24 22:34:28'),(48,24,10,'1200','Inventory','asset','debit',0.00,1.00,'Inventory consumed by kitchen recipe production.','2026-10-24 22:34:28','2026-10-24 22:34:28'),(49,25,2,'1010','Cash in Hand','asset','debit',250.00,0.00,'Customer payment received via Cash','2026-10-24 23:05:18','2026-10-24 23:05:18'),(50,25,18,'4010','Restaurant Sales Revenue','revenue','credit',0.00,250.00,'Restaurant food sales revenue for ORD-20261025-LLNHH1','2026-10-24 23:05:18','2026-10-24 23:05:18'),(51,26,28,'5010','Food & Ingredient COGS','cogs','debit',1.00,0.00,'Food cost: Burger Test Batch 1 pcs','2026-10-26 02:05:20','2026-10-26 02:05:20'),(52,26,10,'1200','Inventory','asset','debit',0.00,1.00,'Inventory consumed by kitchen recipe production.','2026-10-26 02:05:20','2026-10-26 02:05:20'),(53,27,10,'1200','Inventory','asset','debit',10.00,0.00,'Inventory received via GRN-20261026-000017','2026-10-26 02:14:10','2026-10-26 02:14:10'),(54,27,12,'2010','Accounts Payable','liability','credit',0.00,10.00,'Supplier liability created from GRN-20261026-000017','2026-10-26 02:14:10','2026-10-26 02:14:10'),(55,28,28,'5010','Food & Ingredient COGS','cogs','debit',1.00,0.00,'Food cost: Burger Test Batch 1 pcs','2026-10-26 02:24:29','2026-10-26 02:24:29'),(56,28,10,'1200','Inventory','asset','debit',0.00,1.00,'Inventory consumed by kitchen recipe production.','2026-10-26 02:24:29','2026-10-26 02:24:29'),(57,29,10,'1200','Inventory','asset','debit',100.00,0.00,'Inventory received via GRN-20261026-000018','2026-10-26 04:52:33','2026-10-26 04:52:33'),(58,29,12,'2010','Accounts Payable','liability','credit',0.00,100.00,'Supplier liability created from GRN-20261026-000018','2026-10-26 04:52:33','2026-10-26 04:52:33'),(59,30,28,'5010','Food & Ingredient COGS','cogs','debit',100.00,0.00,'Food cost: Chicken 100 kg','2026-10-26 05:02:22','2026-10-26 05:02:22'),(60,30,10,'1200','Inventory','asset','debit',0.00,100.00,'Inventory consumed by kitchen recipe production.','2026-10-26 05:02:22','2026-10-26 05:02:22');
/*!40000 ALTER TABLE `journal_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_categories`
--

DROP TABLE IF EXISTS `menu_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_categories_category_name_unique` (`category_name`),
  KEY `menu_categories_is_available_index` (`is_available`),
  KEY `menu_categories_display_order_index` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_categories`
--

LOCK TABLES `menu_categories` WRITE;
/*!40000 ALTER TABLE `menu_categories` DISABLE KEYS */;
INSERT INTO `menu_categories` VALUES (1,'Burgers',NULL,1,0,'2026-10-06 18:57:19','2026-10-06 18:57:19',NULL),(2,'Coffee',NULL,1,0,'2026-10-06 23:32:14','2026-10-06 23:32:14',NULL);
/*!40000 ALTER TABLE `menu_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_add_on`
--

DROP TABLE IF EXISTS `menu_item_add_on`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_item_add_on` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned NOT NULL,
  `add_on_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_item_add_on_unique` (`menu_item_id`,`add_on_id`),
  KEY `menu_item_add_on_menu_item_id_index` (`menu_item_id`),
  KEY `menu_item_add_on_add_on_id_index` (`add_on_id`),
  CONSTRAINT `menu_item_add_on_add_on_id_foreign` FOREIGN KEY (`add_on_id`) REFERENCES `add_ons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `menu_item_add_on_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_add_on`
--

LOCK TABLES `menu_item_add_on` WRITE;
/*!40000 ALTER TABLE `menu_item_add_on` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_item_add_on` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_variants`
--

DROP TABLE IF EXISTS `menu_item_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_item_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned NOT NULL,
  `variant_name` varchar(120) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_item_variant_unique` (`menu_item_id`,`variant_name`),
  KEY `menu_item_variants_menu_item_id_is_available_index` (`menu_item_id`,`is_available`),
  KEY `menu_item_variants_is_available_index` (`is_available`),
  CONSTRAINT `menu_item_variants_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_variants`
--

LOCK TABLES `menu_item_variants` WRITE;
/*!40000 ALTER TABLE `menu_item_variants` DISABLE KEYS */;
INSERT INTO `menu_item_variants` VALUES (1,1,'Large',350.00,1,'2026-10-06 23:38:51','2026-10-06 23:38:51',NULL),(2,1,'Small',250.00,1,'2026-10-06 23:39:13','2026-10-06 23:39:13',NULL);
/*!40000 ALTER TABLE `menu_item_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_category_id` bigint(20) unsigned NOT NULL,
  `menu_name` varchar(180) NOT NULL,
  `item_type` enum('regular','combo','set_meal') NOT NULL DEFAULT 'regular',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ingredients` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `preparation_time` int(10) unsigned DEFAULT NULL COMMENT 'Preparation time in minutes',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_menu_category_id_is_available_index` (`menu_category_id`,`is_available`),
  KEY `menu_items_item_type_is_available_index` (`item_type`,`is_available`),
  KEY `menu_items_is_available_index` (`is_available`),
  KEY `menu_items_is_featured_index` (`is_featured`),
  CONSTRAINT `menu_items_menu_category_id_foreign` FOREIGN KEY (`menu_category_id`) REFERENCES `menu_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
INSERT INTO `menu_items` VALUES (1,1,'Classic Burger','regular',250.00,'Beef patty, lettuce, cheese, sauce','Local development test item',NULL,NULL,1,0,'2026-10-06 19:00:26','2026-10-06 23:36:48',NULL),(2,1,'DEMO100-Menu Item 001','regular',205.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,9,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(3,2,'DEMO100-Menu Item 002','regular',230.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,10,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(4,1,'DEMO100-Menu Item 003','regular',255.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,11,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(5,2,'DEMO100-Menu Item 004','regular',280.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,12,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(6,1,'DEMO100-Menu Item 005','regular',305.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,13,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(7,2,'DEMO100-Menu Item 006','regular',330.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,14,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,1,'DEMO100-Menu Item 007','regular',355.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,15,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,2,'DEMO100-Menu Item 008','regular',380.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,16,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,1,'DEMO100-Menu Item 009','regular',405.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,17,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,2,'DEMO100-Menu Item 010','regular',430.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,18,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,1,'DEMO100-Menu Item 011','regular',455.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,19,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,2,'DEMO100-Menu Item 012','regular',480.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,20,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,1,'DEMO100-Menu Item 013','regular',505.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,21,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,2,'DEMO100-Menu Item 014','regular',530.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,22,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,1,'DEMO100-Menu Item 015','regular',180.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,23,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,2,'DEMO100-Menu Item 016','regular',205.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,24,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,1,'DEMO100-Menu Item 017','regular',230.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,25,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,2,'DEMO100-Menu Item 018','regular',255.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,26,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,1,'DEMO100-Menu Item 019','regular',280.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,27,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,2,'DEMO100-Menu Item 020','regular',305.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,8,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,1,'DEMO100-Menu Item 021','regular',330.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,9,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,2,'DEMO100-Menu Item 022','regular',355.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,10,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,1,'DEMO100-Menu Item 023','regular',380.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,11,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,2,'DEMO100-Menu Item 024','regular',405.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,12,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,1,'DEMO100-Menu Item 025','regular',430.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,13,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,2,'DEMO100-Menu Item 026','regular',455.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,14,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,1,'DEMO100-Menu Item 027','regular',480.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,15,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,2,'DEMO100-Menu Item 028','regular',505.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,16,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,1,'DEMO100-Menu Item 029','regular',530.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,17,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,2,'DEMO100-Menu Item 030','regular',180.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,18,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,1,'DEMO100-Menu Item 031','regular',205.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,19,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,2,'DEMO100-Menu Item 032','regular',230.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,20,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,1,'DEMO100-Menu Item 033','regular',255.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,21,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,2,'DEMO100-Menu Item 034','regular',280.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,22,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,1,'DEMO100-Menu Item 035','regular',305.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,23,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,2,'DEMO100-Menu Item 036','regular',330.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,24,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,1,'DEMO100-Menu Item 037','regular',355.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,25,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,2,'DEMO100-Menu Item 038','regular',380.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,26,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,1,'DEMO100-Menu Item 039','regular',405.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,27,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,2,'DEMO100-Menu Item 040','regular',430.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,8,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,1,'DEMO100-Menu Item 041','regular',455.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,9,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,2,'DEMO100-Menu Item 042','regular',480.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,10,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,1,'DEMO100-Menu Item 043','regular',505.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,11,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,2,'DEMO100-Menu Item 044','regular',530.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,12,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,1,'DEMO100-Menu Item 045','regular',180.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,13,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,2,'DEMO100-Menu Item 046','regular',205.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,14,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,1,'DEMO100-Menu Item 047','regular',230.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,15,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,2,'DEMO100-Menu Item 048','regular',255.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,16,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,1,'DEMO100-Menu Item 049','regular',280.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,17,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,2,'DEMO100-Menu Item 050','regular',305.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,18,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,1,'DEMO100-Menu Item 051','regular',330.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,19,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,2,'DEMO100-Menu Item 052','regular',355.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,20,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,1,'DEMO100-Menu Item 053','regular',380.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,21,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,2,'DEMO100-Menu Item 054','regular',405.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,22,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,1,'DEMO100-Menu Item 055','regular',430.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,23,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,2,'DEMO100-Menu Item 056','regular',455.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,24,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,1,'DEMO100-Menu Item 057','regular',480.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,25,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,2,'DEMO100-Menu Item 058','regular',505.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,26,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,1,'DEMO100-Menu Item 059','regular',530.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,27,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,2,'DEMO100-Menu Item 060','regular',180.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,8,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,1,'DEMO100-Menu Item 061','regular',205.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,9,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,2,'DEMO100-Menu Item 062','regular',230.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,10,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,1,'DEMO100-Menu Item 063','regular',255.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,11,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,2,'DEMO100-Menu Item 064','regular',280.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,12,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,1,'DEMO100-Menu Item 065','regular',305.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,13,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,2,'DEMO100-Menu Item 066','regular',330.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,14,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,1,'DEMO100-Menu Item 067','regular',355.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,15,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,2,'DEMO100-Menu Item 068','regular',380.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,16,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,1,'DEMO100-Menu Item 069','regular',405.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,17,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,2,'DEMO100-Menu Item 070','regular',430.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,18,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,1,'DEMO100-Menu Item 071','regular',455.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,19,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,2,'DEMO100-Menu Item 072','regular',480.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,20,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,1,'DEMO100-Menu Item 073','regular',505.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,21,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,2,'DEMO100-Menu Item 074','regular',530.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,22,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,1,'DEMO100-Menu Item 075','regular',180.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,23,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,2,'DEMO100-Menu Item 076','regular',205.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,24,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,1,'DEMO100-Menu Item 077','regular',230.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,25,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,2,'DEMO100-Menu Item 078','regular',255.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,26,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,1,'DEMO100-Menu Item 079','regular',280.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,27,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,2,'DEMO100-Menu Item 080','regular',305.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,8,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,1,'DEMO100-Menu Item 081','regular',330.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,9,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,2,'DEMO100-Menu Item 082','regular',355.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,10,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,1,'DEMO100-Menu Item 083','regular',380.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,11,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,2,'DEMO100-Menu Item 084','regular',405.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,12,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,1,'DEMO100-Menu Item 085','regular',430.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,13,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,2,'DEMO100-Menu Item 086','regular',455.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,14,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,1,'DEMO100-Menu Item 087','regular',480.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,15,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,2,'DEMO100-Menu Item 088','regular',505.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,16,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,1,'DEMO100-Menu Item 089','regular',530.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,17,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,2,'DEMO100-Menu Item 090','regular',180.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,18,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,1,'DEMO100-Menu Item 091','regular',205.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,19,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,2,'DEMO100-Menu Item 092','regular',230.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,20,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,1,'DEMO100-Menu Item 093','regular',255.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,21,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,2,'DEMO100-Menu Item 094','regular',280.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,22,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,1,'DEMO100-Menu Item 095','regular',305.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,23,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,2,'DEMO100-Menu Item 096','regular',330.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,24,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,1,'DEMO100-Menu Item 097','regular',355.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,25,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,2,'DEMO100-Menu Item 098','regular',380.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,26,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,1,'DEMO100-Menu Item 099','regular',405.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,27,1,0,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,2,'DEMO100-Menu Item 100','regular',430.00,'Demo ingredients','DEMO DATA: menu item for local module preview.',NULL,8,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_08_30_171641_create_tenant_roles_table',1),(2,'2026_08_30_171703_create_tenant_permissions_table',1),(3,'2026_08_30_171718_create_tenant_permission_role_table',1),(4,'2026_08_30_171735_create_tenant_users_table',1),(5,'2026_08_30_171809_create_tenant_password_reset_tokens_table',1),(6,'2026_08_30_171824_create_tenant_personal_access_tokens_table',1),(7,'2026_08_30_171838_create_tenant_menu_categories_table',1),(8,'2026_08_30_171853_create_tenant_menu_items_table',1),(9,'2026_08_30_171908_create_tenant_menu_item_variants_table',1),(10,'2026_08_30_171929_create_tenant_add_ons_table',1),(11,'2026_08_30_171945_create_tenant_menu_item_add_on_table',1),(12,'2026_08_30_172004_create_tenant_customers_table',1),(13,'2026_08_30_172021_create_tenant_restaurant_tables_table',1),(14,'2026_08_30_172414_create_tenant_employees_table',1),(15,'2026_08_30_172433_create_tenant_shift_schedules_table',1),(16,'2026_08_30_172523_create_tenant_shift_schedule_overrides_table',1),(17,'2026_08_30_172538_create_tenant_attendances_table',1),(18,'2026_08_30_172555_create_tenant_attendance_breaks_table',1),(19,'2026_08_30_172611_create_tenant_raw_materials_table',1),(20,'2026_08_30_172628_create_tenant_warehouse_stocks_table',1),(21,'2026_08_30_172644_create_tenant_restaurant_stocks_table',1),(22,'2026_08_30_172658_create_tenant_suppliers_table',1),(23,'2026_08_30_172712_create_tenant_purchase_orders_table',1),(24,'2026_08_30_172728_create_tenant_purchase_order_items_table',1),(25,'2026_08_30_172746_create_tenant_purchase_order_payments_table',1),(26,'2026_08_30_172801_create_tenant_purchase_order_receipts_table',1),(27,'2026_08_30_172817_create_tenant_purchase_order_receipt_items_table',1),(28,'2026_08_30_173005_create_tenant_stock_movements_table',1),(29,'2026_08_30_173024_create_tenant_stock_transfers_table',1),(30,'2026_08_30_173038_create_tenant_stock_transfer_items_table',1),(31,'2026_08_30_173111_create_tenant_recipe_mappings_table',1),(32,'2026_08_30_173131_create_tenant_reservations_table',1),(33,'2026_08_30_173148_create_tenant_reservation_tables_table',1),(34,'2026_08_30_173203_create_tenant_orders_table',1),(35,'2026_08_30_173228_create_tenant_order_tables_table',1),(36,'2026_08_30_173241_create_tenant_order_kitchen_batches_table',1),(37,'2026_08_30_173259_create_tenant_order_items_table',1),(38,'2026_08_30_173314_create_tenant_order_item_addons_table',1),(39,'2026_08_30_173329_create_tenant_order_payments_table',1),(40,'2026_08_30_173454_create_tenant_order_recipe_consumptions_table',1),(41,'2026_08_30_173509_create_tenant_order_recipe_consumption_items_table',1),(42,'2026_08_30_173527_create_tenant_salary_payrolls_table',1),(43,'2026_08_30_173545_create_tenant_salary_details_table',1),(44,'2026_08_30_173601_create_tenant_expense_categories_table',1),(45,'2026_08_30_173618_create_tenant_expenses_table',1),(46,'2026_08_30_173635_create_tenant_system_settings_table',1),(47,'2026_08_30_173653_create_tenant_audit_logs_table',1),(48,'2026_09_06_212714_create_inventory_wastages_table',1),(49,'2026_09_06_212812_create_inventory_wastage_items_table',1),(50,'2026_09_06_223000_create_stock_transfer_templates_table',1),(51,'2026_09_06_223100_create_stock_transfer_template_items_table',1),(52,'2026_09_07_142000_create_warehouse_stock_lots_table',1),(53,'2026_09_07_142100_create_warehouse_stock_lot_movements_table',1),(54,'2026_09_07_142200_backfill_existing_warehouse_stock_lots',1),(55,'2026_09_07_154500_add_lot_snapshot_fields_to_purchase_order_receipt_items_table',1),(56,'2026_09_07_211500_add_manual_notes_to_warehouse_stock_lots_table',1),(57,'2026_09_07_211600_create_warehouse_stock_lot_metadata_changes_table',1),(58,'2026_09_07_223000_create_purchase_returns_table',1),(59,'2026_09_07_223100_create_purchase_return_items_table',1),(60,'2026_09_07_231000_add_return_credit_applied_to_purchase_orders_table',1),(61,'2026_09_07_231100_create_purchase_return_settlements_table',1),(62,'2026_09_07_235000_add_supplier_credit_applied_to_purchase_orders_table',1),(63,'2026_09_07_235100_create_supplier_credit_applications_table',1),(64,'2026_09_08_120000_make_customer_phone_nullable_for_reservations',1),(65,'2026_09_08_170000_sync_reservation_datetime_with_reservation_date',1),(66,'2026_09_09_000001_add_source_tracking_to_expenses_table',1),(67,'2026_09_09_000002_add_payment_tracking_to_salary_payrolls_table',1),(68,'2026_09_09_000003_create_other_income_tables',1),(69,'2026_09_09_000004_create_chart_of_accounts',1),(70,'2026_09_09_000005_create_journal_engine',1),(71,'2026_09_09_000006_add_source_hash_to_journal_entries',1),(72,'2026_09_09_000007_add_purchase_return_accounting_accounts',1),(73,'2026_09_10_111517_add_sales_discount_account_to_chart_of_accounts',1),(74,'2026_09_10_124104_add_service_charge_income_account_to_chart_of_accounts',1),(75,'2026_10_25_000001_add_takeaway_pickup_fields_to_orders',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item_addons`
--

DROP TABLE IF EXISTS `order_item_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_item_addons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `menu_addon_id` bigint(20) unsigned DEFAULT NULL,
  `addon_name` varchar(150) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_item_addons_order_item_id_foreign` (`order_item_id`),
  KEY `order_item_addons_menu_addon_id_index` (`menu_addon_id`),
  CONSTRAINT `order_item_addons_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_addons`
--

LOCK TABLES `order_item_addons` WRITE;
/*!40000 ALTER TABLE `order_item_addons` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_item_addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `order_kitchen_batch_id` bigint(20) unsigned NOT NULL,
  `menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `menu_item_variant_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(180) NOT NULL,
  `variant_name` varchar(150) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `addon_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `kitchen_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_menu_item_id_foreign` (`menu_item_id`),
  KEY `order_items_menu_item_variant_id_foreign` (`menu_item_variant_id`),
  KEY `order_items_status_index` (`status`),
  KEY `order_items_order_id_menu_item_id_index` (`order_id`,`menu_item_id`),
  KEY `order_items_batch_status_index` (`order_kitchen_batch_id`,`status`),
  CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_menu_item_variant_id_foreign` FOREIGN KEY (`menu_item_variant_id`) REFERENCES `menu_item_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_order_kitchen_batch_id_foreign` FOREIGN KEY (`order_kitchen_batch_id`) REFERENCES `order_kitchen_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,1,NULL,'Classic Test Burger',NULL,250.00,1,0.00,250.00,'served',NULL,'2026-10-06 19:03:48','2026-10-06 21:02:37'),(2,2,2,1,NULL,'Classic Test Burger',NULL,250.00,1,0.00,250.00,'served',NULL,'2026-10-06 22:19:01','2026-10-06 23:24:07'),(3,3,3,1,NULL,'Classic Test Burger',NULL,250.00,1,0.00,250.00,'canceled',NULL,'2026-10-06 23:28:12','2026-10-07 02:23:19'),(4,4,4,1,NULL,'Classic Burger',NULL,250.00,1,0.00,250.00,'served',NULL,'2026-10-08 03:54:05','2026-10-08 03:56:01'),(5,5,5,1,NULL,'Classic Burger',NULL,250.00,1,0.00,250.00,'served',NULL,'2026-10-13 09:10:09','2026-10-13 09:11:48'),(6,6,6,1,NULL,'Classic Burger',NULL,250.00,3,0.00,750.00,'served',NULL,'2026-10-14 21:45:48','2026-10-14 21:48:19'),(7,7,7,1,NULL,'Classic Burger',NULL,250.00,1,0.00,250.00,'served',NULL,'2026-10-24 22:08:16','2026-10-24 22:36:15'),(8,8,8,1,NULL,'Classic Burger',NULL,250.00,1,0.00,250.00,'ready',NULL,'2026-10-25 03:23:52','2026-10-26 02:05:34'),(9,9,9,1,NULL,'Classic Burger',NULL,250.00,2,0.00,500.00,'canceled',NULL,'2026-10-26 02:22:42','2026-10-26 02:23:49'),(10,10,10,1,NULL,'Classic Burger',NULL,250.00,1,0.00,250.00,'ready',NULL,'2026-10-26 02:24:13','2026-10-26 02:24:39'),(11,11,11,2,NULL,'DEMO100-Menu Item 001',NULL,205.00,1,0.00,205.00,'preparing','DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58'),(12,12,12,3,NULL,'DEMO100-Menu Item 002',NULL,230.00,1,0.00,230.00,'preparing','DEMO DATA','2026-10-26 04:42:58','2026-10-26 04:43:58'),(13,13,13,4,NULL,'DEMO100-Menu Item 003',NULL,255.00,1,0.00,255.00,'preparing','DEMO DATA','2026-10-26 04:41:58','2026-10-26 04:43:58'),(14,14,14,5,NULL,'DEMO100-Menu Item 004',NULL,280.00,1,0.00,280.00,'preparing','DEMO DATA','2026-10-26 04:40:58','2026-10-26 04:43:58'),(15,15,15,6,NULL,'DEMO100-Menu Item 005',NULL,305.00,1,0.00,305.00,'preparing','DEMO DATA','2026-10-26 04:39:58','2026-10-26 04:43:58'),(16,16,16,7,NULL,'DEMO100-Menu Item 006',NULL,330.00,1,0.00,330.00,'preparing','DEMO DATA','2026-10-26 04:38:58','2026-10-26 04:43:58'),(17,17,17,8,NULL,'DEMO100-Menu Item 007',NULL,355.00,1,0.00,355.00,'ready','DEMO DATA','2026-10-26 04:37:58','2026-10-26 04:43:58'),(18,18,18,9,NULL,'DEMO100-Menu Item 008',NULL,380.00,1,0.00,380.00,'ready','DEMO DATA','2026-10-26 04:36:58','2026-10-26 04:43:58'),(19,19,19,10,NULL,'DEMO100-Menu Item 009',NULL,405.00,1,0.00,405.00,'ready','DEMO DATA','2026-10-26 04:35:58','2026-10-26 04:43:58'),(20,20,20,11,NULL,'DEMO100-Menu Item 010',NULL,430.00,1,0.00,430.00,'ready','DEMO DATA','2026-10-26 04:34:58','2026-10-26 04:43:58'),(21,21,21,12,NULL,'DEMO100-Menu Item 011',NULL,455.00,1,0.00,455.00,'ready','DEMO DATA','2026-10-26 04:33:58','2026-10-26 04:43:58'),(22,22,22,13,NULL,'DEMO100-Menu Item 012',NULL,480.00,1,0.00,480.00,'ready','DEMO DATA','2026-10-26 04:32:58','2026-10-26 04:43:58'),(23,23,23,14,NULL,'DEMO100-Menu Item 013',NULL,505.00,1,0.00,505.00,'served','DEMO DATA','2026-10-26 04:31:58','2026-10-26 04:43:58'),(24,24,24,15,NULL,'DEMO100-Menu Item 014',NULL,530.00,1,0.00,530.00,'served','DEMO DATA','2026-10-26 04:30:58','2026-10-26 04:43:58'),(25,25,25,16,NULL,'DEMO100-Menu Item 015',NULL,180.00,1,0.00,180.00,'served','DEMO DATA','2026-10-26 04:29:58','2026-10-26 04:43:58'),(26,26,26,17,NULL,'DEMO100-Menu Item 016',NULL,205.00,1,0.00,205.00,'served','DEMO DATA','2026-10-26 04:28:58','2026-10-26 04:43:58'),(27,27,27,18,NULL,'DEMO100-Menu Item 017',NULL,230.00,1,0.00,230.00,'served','DEMO DATA','2026-10-26 04:27:58','2026-10-26 04:43:58'),(28,28,28,19,NULL,'DEMO100-Menu Item 018',NULL,255.00,1,0.00,255.00,'served','DEMO DATA','2026-10-26 04:26:58','2026-10-26 04:43:58'),(29,29,29,20,NULL,'DEMO100-Menu Item 019',NULL,280.00,1,0.00,280.00,'served','DEMO DATA','2026-10-26 04:25:58','2026-10-26 04:43:58'),(30,30,30,21,NULL,'DEMO100-Menu Item 020',NULL,305.00,1,0.00,305.00,'served','DEMO DATA','2026-10-26 04:24:58','2026-10-26 04:43:58'),(31,31,31,22,NULL,'DEMO100-Menu Item 021',NULL,330.00,1,0.00,330.00,'served','DEMO DATA','2026-10-26 04:23:58','2026-10-26 04:43:58'),(32,32,32,23,NULL,'DEMO100-Menu Item 022',NULL,355.00,1,0.00,355.00,'served','DEMO DATA','2026-10-26 04:22:58','2026-10-26 04:43:58'),(33,33,33,24,NULL,'DEMO100-Menu Item 023',NULL,380.00,1,0.00,380.00,'served','DEMO DATA','2026-10-26 04:21:58','2026-10-26 04:43:58'),(34,34,34,25,NULL,'DEMO100-Menu Item 024',NULL,405.00,1,0.00,405.00,'served','DEMO DATA','2026-10-26 04:20:58','2026-10-26 04:43:58'),(35,35,35,26,NULL,'DEMO100-Menu Item 025',NULL,430.00,1,0.00,430.00,'served','DEMO DATA','2026-10-26 04:19:58','2026-10-26 04:43:58'),(36,36,36,27,NULL,'DEMO100-Menu Item 026',NULL,455.00,1,0.00,455.00,'served','DEMO DATA','2026-10-26 04:18:58','2026-10-26 04:43:58'),(37,37,37,28,NULL,'DEMO100-Menu Item 027',NULL,480.00,1,0.00,480.00,'served','DEMO DATA','2026-10-26 04:17:58','2026-10-26 04:43:58'),(38,38,38,29,NULL,'DEMO100-Menu Item 028',NULL,505.00,1,0.00,505.00,'served','DEMO DATA','2026-10-26 04:16:58','2026-10-26 04:43:58'),(39,39,39,30,NULL,'DEMO100-Menu Item 029',NULL,530.00,1,0.00,530.00,'served','DEMO DATA','2026-10-26 04:15:58','2026-10-26 04:43:58'),(40,40,40,31,NULL,'DEMO100-Menu Item 030',NULL,180.00,1,0.00,180.00,'served','DEMO DATA','2026-10-26 04:14:58','2026-10-26 04:43:58'),(41,41,41,32,NULL,'DEMO100-Menu Item 031',NULL,205.00,1,0.00,205.00,'served','DEMO DATA','2026-10-26 04:13:58','2026-10-26 04:43:58'),(42,42,42,33,NULL,'DEMO100-Menu Item 032',NULL,230.00,1,0.00,230.00,'served','DEMO DATA','2026-10-26 04:12:58','2026-10-26 04:43:58'),(43,43,43,34,NULL,'DEMO100-Menu Item 033',NULL,255.00,1,0.00,255.00,'served','DEMO DATA','2026-10-26 04:11:58','2026-10-26 04:43:58'),(44,44,44,35,NULL,'DEMO100-Menu Item 034',NULL,280.00,1,0.00,280.00,'served','DEMO DATA','2026-10-26 04:10:58','2026-10-26 04:43:58'),(45,45,45,36,NULL,'DEMO100-Menu Item 035',NULL,305.00,1,0.00,305.00,'served','DEMO DATA','2026-10-26 04:09:58','2026-10-26 04:43:58'),(46,46,46,37,NULL,'DEMO100-Menu Item 036',NULL,330.00,1,0.00,330.00,'served','DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58'),(47,47,47,38,NULL,'DEMO100-Menu Item 037',NULL,355.00,1,0.00,355.00,'served','DEMO DATA','2026-10-26 04:42:58','2026-10-26 04:43:58'),(48,48,48,39,NULL,'DEMO100-Menu Item 038',NULL,380.00,1,0.00,380.00,'served','DEMO DATA','2026-10-26 04:41:58','2026-10-26 04:43:58'),(49,49,49,40,NULL,'DEMO100-Menu Item 039',NULL,405.00,1,0.00,405.00,'served','DEMO DATA','2026-10-26 04:40:58','2026-10-26 04:43:58'),(50,50,50,41,NULL,'DEMO100-Menu Item 040',NULL,430.00,1,0.00,430.00,'served','DEMO DATA','2026-10-26 04:39:58','2026-10-26 04:43:58'),(51,51,51,42,NULL,'DEMO100-Menu Item 041',NULL,455.00,1,0.00,455.00,'served','DEMO DATA','2026-10-26 04:38:58','2026-10-26 04:43:58'),(52,52,52,43,NULL,'DEMO100-Menu Item 042',NULL,480.00,1,0.00,480.00,'served','DEMO DATA','2026-10-26 04:37:58','2026-10-26 04:43:58'),(53,53,53,44,NULL,'DEMO100-Menu Item 043',NULL,505.00,1,0.00,505.00,'served','DEMO DATA','2026-10-26 04:36:58','2026-10-26 04:43:58'),(54,54,54,45,NULL,'DEMO100-Menu Item 044',NULL,530.00,1,0.00,530.00,'served','DEMO DATA','2026-10-26 04:35:58','2026-10-26 04:43:58'),(55,55,55,46,NULL,'DEMO100-Menu Item 045',NULL,180.00,1,0.00,180.00,'served','DEMO DATA','2026-10-26 04:34:58','2026-10-26 04:43:58'),(56,56,56,47,NULL,'DEMO100-Menu Item 046',NULL,205.00,1,0.00,205.00,'served','DEMO DATA','2026-10-26 04:33:58','2026-10-26 04:43:58'),(57,57,57,48,NULL,'DEMO100-Menu Item 047',NULL,230.00,1,0.00,230.00,'served','DEMO DATA','2026-10-26 04:32:58','2026-10-26 04:43:58'),(58,58,58,49,NULL,'DEMO100-Menu Item 048',NULL,255.00,1,0.00,255.00,'served','DEMO DATA','2026-10-26 04:31:58','2026-10-26 04:43:58'),(59,59,59,50,NULL,'DEMO100-Menu Item 049',NULL,280.00,1,0.00,280.00,'served','DEMO DATA','2026-10-26 04:30:58','2026-10-26 04:43:58'),(60,60,60,51,NULL,'DEMO100-Menu Item 050',NULL,305.00,1,0.00,305.00,'served','DEMO DATA','2026-10-26 04:29:58','2026-10-26 04:43:58'),(61,61,61,52,NULL,'DEMO100-Menu Item 051',NULL,330.00,1,0.00,330.00,'served','DEMO DATA','2026-10-26 04:28:58','2026-10-26 04:43:58'),(62,62,62,53,NULL,'DEMO100-Menu Item 052',NULL,355.00,1,0.00,355.00,'served','DEMO DATA','2026-10-26 04:27:58','2026-10-26 04:43:58'),(63,63,63,54,NULL,'DEMO100-Menu Item 053',NULL,380.00,1,0.00,380.00,'served','DEMO DATA','2026-10-26 04:26:58','2026-10-26 04:43:58'),(64,64,64,55,NULL,'DEMO100-Menu Item 054',NULL,405.00,1,0.00,405.00,'served','DEMO DATA','2026-10-26 04:25:58','2026-10-26 04:43:58'),(65,65,65,56,NULL,'DEMO100-Menu Item 055',NULL,430.00,1,0.00,430.00,'served','DEMO DATA','2026-10-26 04:24:58','2026-10-26 04:43:58'),(66,66,66,57,NULL,'DEMO100-Menu Item 056',NULL,455.00,1,0.00,455.00,'served','DEMO DATA','2026-10-26 04:23:58','2026-10-26 04:43:58'),(67,67,67,58,NULL,'DEMO100-Menu Item 057',NULL,480.00,1,0.00,480.00,'served','DEMO DATA','2026-10-26 04:22:58','2026-10-26 04:43:58'),(68,68,68,59,NULL,'DEMO100-Menu Item 058',NULL,505.00,1,0.00,505.00,'served','DEMO DATA','2026-10-26 04:21:58','2026-10-26 04:43:58'),(69,69,69,60,NULL,'DEMO100-Menu Item 059',NULL,530.00,1,0.00,530.00,'served','DEMO DATA','2026-10-26 04:20:58','2026-10-26 04:43:58'),(70,70,70,61,NULL,'DEMO100-Menu Item 060',NULL,180.00,1,0.00,180.00,'served','DEMO DATA','2026-10-26 04:19:58','2026-10-26 04:43:58'),(71,71,71,62,NULL,'DEMO100-Menu Item 061',NULL,205.00,1,0.00,205.00,'served','DEMO DATA','2026-10-26 04:18:58','2026-10-26 04:43:58'),(72,72,72,63,NULL,'DEMO100-Menu Item 062',NULL,230.00,1,0.00,230.00,'served','DEMO DATA','2026-10-26 04:17:58','2026-10-26 04:43:58'),(73,73,73,64,NULL,'DEMO100-Menu Item 063',NULL,255.00,1,0.00,255.00,'served','DEMO DATA','2026-10-26 04:16:58','2026-10-26 04:43:58'),(74,74,74,65,NULL,'DEMO100-Menu Item 064',NULL,280.00,1,0.00,280.00,'served','DEMO DATA','2026-10-26 04:15:58','2026-10-26 04:43:58'),(75,75,75,66,NULL,'DEMO100-Menu Item 065',NULL,305.00,1,0.00,305.00,'served','DEMO DATA','2026-10-26 04:14:58','2026-10-26 04:43:58'),(76,76,76,67,NULL,'DEMO100-Menu Item 066',NULL,330.00,1,0.00,330.00,'served','DEMO DATA','2026-10-26 04:13:58','2026-10-26 04:43:58'),(77,77,77,68,NULL,'DEMO100-Menu Item 067',NULL,355.00,1,0.00,355.00,'served','DEMO DATA','2026-10-26 04:12:58','2026-10-26 04:43:58'),(78,78,78,69,NULL,'DEMO100-Menu Item 068',NULL,380.00,1,0.00,380.00,'served','DEMO DATA','2026-10-26 04:11:58','2026-10-26 04:43:58'),(79,79,79,70,NULL,'DEMO100-Menu Item 069',NULL,405.00,1,0.00,405.00,'served','DEMO DATA','2026-10-26 04:10:58','2026-10-26 04:43:58'),(80,80,80,71,NULL,'DEMO100-Menu Item 070',NULL,430.00,1,0.00,430.00,'served','DEMO DATA','2026-10-26 04:09:58','2026-10-26 04:43:58'),(81,81,81,72,NULL,'DEMO100-Menu Item 071',NULL,455.00,1,0.00,455.00,'served','DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58'),(82,82,82,73,NULL,'DEMO100-Menu Item 072',NULL,480.00,1,0.00,480.00,'served','DEMO DATA','2026-10-26 04:42:58','2026-10-26 04:43:58'),(83,83,83,74,NULL,'DEMO100-Menu Item 073',NULL,505.00,1,0.00,505.00,'served','DEMO DATA','2026-10-26 04:41:58','2026-10-26 04:43:58'),(84,84,84,75,NULL,'DEMO100-Menu Item 074',NULL,530.00,1,0.00,530.00,'served','DEMO DATA','2026-10-26 04:40:58','2026-10-26 04:43:58'),(85,85,85,76,NULL,'DEMO100-Menu Item 075',NULL,180.00,1,0.00,180.00,'served','DEMO DATA','2026-10-26 04:39:58','2026-10-26 04:43:58'),(86,86,86,77,NULL,'DEMO100-Menu Item 076',NULL,205.00,1,0.00,205.00,'served','DEMO DATA','2026-10-26 04:38:58','2026-10-26 04:43:58'),(87,87,87,78,NULL,'DEMO100-Menu Item 077',NULL,230.00,1,0.00,230.00,'served','DEMO DATA','2026-10-26 04:37:58','2026-10-26 04:43:58'),(88,88,88,79,NULL,'DEMO100-Menu Item 078',NULL,255.00,1,0.00,255.00,'served','DEMO DATA','2026-10-26 04:36:58','2026-10-26 04:43:58'),(89,89,89,80,NULL,'DEMO100-Menu Item 079',NULL,280.00,1,0.00,280.00,'served','DEMO DATA','2026-10-26 04:35:58','2026-10-26 04:43:58'),(90,90,90,81,NULL,'DEMO100-Menu Item 080',NULL,305.00,1,0.00,305.00,'served','DEMO DATA','2026-10-26 04:34:58','2026-10-26 04:43:58'),(91,91,91,82,NULL,'DEMO100-Menu Item 081',NULL,330.00,1,0.00,330.00,'served','DEMO DATA','2026-10-26 04:33:58','2026-10-26 04:43:58'),(92,92,92,83,NULL,'DEMO100-Menu Item 082',NULL,355.00,1,0.00,355.00,'served','DEMO DATA','2026-10-26 04:32:58','2026-10-26 04:43:58'),(93,93,93,84,NULL,'DEMO100-Menu Item 083',NULL,380.00,1,0.00,380.00,'served','DEMO DATA','2026-10-26 04:31:58','2026-10-26 04:43:58'),(94,94,94,85,NULL,'DEMO100-Menu Item 084',NULL,405.00,1,0.00,405.00,'served','DEMO DATA','2026-10-26 04:30:58','2026-10-26 04:43:58'),(95,95,95,86,NULL,'DEMO100-Menu Item 085',NULL,430.00,1,0.00,430.00,'served','DEMO DATA','2026-10-26 04:29:58','2026-10-26 04:43:58'),(96,96,96,87,NULL,'DEMO100-Menu Item 086',NULL,455.00,1,0.00,455.00,'served','DEMO DATA','2026-10-26 04:28:58','2026-10-26 04:43:58'),(97,97,97,88,NULL,'DEMO100-Menu Item 087',NULL,480.00,1,0.00,480.00,'served','DEMO DATA','2026-10-26 04:27:58','2026-10-26 04:43:58'),(98,98,98,89,NULL,'DEMO100-Menu Item 088',NULL,505.00,1,0.00,505.00,'served','DEMO DATA','2026-10-26 04:26:58','2026-10-26 04:43:58'),(99,99,99,90,NULL,'DEMO100-Menu Item 089',NULL,530.00,1,0.00,530.00,'served','DEMO DATA','2026-10-26 04:25:58','2026-10-26 04:43:58'),(100,100,100,91,NULL,'DEMO100-Menu Item 090',NULL,180.00,1,0.00,180.00,'served','DEMO DATA','2026-10-26 04:24:58','2026-10-26 04:43:58'),(101,101,101,92,NULL,'DEMO100-Menu Item 091',NULL,205.00,1,0.00,205.00,'served','DEMO DATA','2026-10-26 04:23:58','2026-10-26 04:43:58'),(102,102,102,93,NULL,'DEMO100-Menu Item 092',NULL,230.00,1,0.00,230.00,'served','DEMO DATA','2026-10-26 04:22:58','2026-10-26 04:43:58'),(103,103,103,94,NULL,'DEMO100-Menu Item 093',NULL,255.00,1,0.00,255.00,'served','DEMO DATA','2026-10-26 04:21:58','2026-10-26 04:43:58'),(104,104,104,95,NULL,'DEMO100-Menu Item 094',NULL,280.00,1,0.00,280.00,'served','DEMO DATA','2026-10-26 04:20:58','2026-10-26 04:43:58'),(105,105,105,96,NULL,'DEMO100-Menu Item 095',NULL,305.00,1,0.00,305.00,'served','DEMO DATA','2026-10-26 04:19:58','2026-10-26 04:43:58'),(106,106,106,97,NULL,'DEMO100-Menu Item 096',NULL,330.00,1,0.00,330.00,'served','DEMO DATA','2026-10-26 04:18:58','2026-10-26 04:43:58'),(107,107,107,98,NULL,'DEMO100-Menu Item 097',NULL,355.00,1,0.00,355.00,'served','DEMO DATA','2026-10-26 04:17:58','2026-10-26 04:43:58'),(108,108,108,99,NULL,'DEMO100-Menu Item 098',NULL,380.00,1,0.00,380.00,'served','DEMO DATA','2026-10-26 04:16:58','2026-10-26 04:43:58'),(109,109,109,100,NULL,'DEMO100-Menu Item 099',NULL,405.00,1,0.00,405.00,'served','DEMO DATA','2026-10-26 04:15:58','2026-10-26 04:43:58'),(110,110,110,101,NULL,'DEMO100-Menu Item 100',NULL,430.00,1,0.00,430.00,'served','DEMO DATA','2026-10-26 04:14:58','2026-10-26 04:43:58'),(111,111,111,1,NULL,'Classic Burger',NULL,250.00,3,0.00,750.00,'canceled',NULL,'2026-10-26 04:57:39','2026-10-26 04:58:36'),(112,112,112,2,NULL,'DEMO100-Menu Item 001',NULL,205.00,1,0.00,205.00,'preparing',NULL,'2026-10-26 04:59:27','2026-10-26 05:02:22'),(113,113,113,2,NULL,'DEMO100-Menu Item 001',NULL,205.00,1,0.00,205.00,'pending',NULL,'2026-10-26 05:01:30','2026-10-26 05:01:40');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_kitchen_batches`
--

DROP TABLE IF EXISTS `order_kitchen_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_kitchen_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `batch_no` int(10) unsigned NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `chef_id` bigint(20) unsigned DEFAULT NULL,
  `sent_to_kitchen_at` timestamp NULL DEFAULT NULL,
  `preparing_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_kitchen_batches_order_batch_unique` (`order_id`,`batch_no`),
  KEY `order_kitchen_batches_chef_id_foreign` (`chef_id`),
  KEY `order_kitchen_batches_created_by_foreign` (`created_by`),
  KEY `order_kitchen_batches_status_chef_index` (`status`,`chef_id`),
  CONSTRAINT `order_kitchen_batches_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_kitchen_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_kitchen_batches_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_kitchen_batches`
--

LOCK TABLES `order_kitchen_batches` WRITE;
/*!40000 ALTER TABLE `order_kitchen_batches` DISABLE KEYS */;
INSERT INTO `order_kitchen_batches` VALUES (1,1,1,'served',2,'2026-10-06 19:03:48','2026-10-06 20:19:48','2026-10-06 20:20:01','2026-10-06 21:02:37',1,'2026-10-06 19:03:48','2026-10-06 21:02:37'),(2,2,1,'served',2,'2026-10-06 22:19:01','2026-10-06 23:11:38','2026-10-06 23:23:53','2026-10-06 23:24:07',1,'2026-10-06 22:19:01','2026-10-06 23:24:07'),(3,3,1,'pending',2,'2026-10-06 23:28:12',NULL,NULL,NULL,1,'2026-10-06 23:28:12','2026-10-07 01:32:45'),(4,4,1,'served',2,'2026-10-08 03:54:05','2026-10-08 03:55:40','2026-10-08 03:55:46','2026-10-08 03:56:01',1,'2026-10-08 03:54:05','2026-10-08 03:56:01'),(5,5,1,'served',2,'2026-10-13 09:10:09','2026-10-13 09:11:32','2026-10-13 09:11:35','2026-10-13 09:11:48',1,'2026-10-13 09:10:09','2026-10-13 09:11:48'),(6,6,1,'served',2,'2026-10-14 21:45:48','2026-10-14 21:47:57','2026-10-14 21:48:01','2026-10-14 21:48:19',1,'2026-10-14 21:45:48','2026-10-14 21:48:19'),(7,7,1,'served',2,'2026-10-24 22:08:16','2026-10-24 22:34:26','2026-10-24 22:34:33','2026-10-24 22:36:15',1,'2026-10-24 22:08:16','2026-10-24 22:36:15'),(8,8,1,'ready',2,'2026-10-25 03:23:52','2026-10-26 02:05:18','2026-10-26 02:05:34',NULL,1,'2026-10-25 03:23:52','2026-10-26 02:05:34'),(9,9,1,'pending',2,'2026-10-26 02:22:42',NULL,NULL,NULL,1,'2026-10-26 02:22:42','2026-10-26 02:23:05'),(10,10,1,'ready',2,'2026-10-26 02:24:13','2026-10-26 02:24:29','2026-10-26 02:24:39',NULL,1,'2026-10-26 02:24:13','2026-10-26 02:24:39'),(11,11,1,'preparing',4,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL,NULL,4,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(12,12,1,'preparing',4,'2026-10-26 04:42:58','2026-10-26 04:42:58',NULL,NULL,5,'2026-10-26 04:42:58','2026-10-26 04:43:58'),(13,13,1,'preparing',4,'2026-10-26 04:41:58','2026-10-26 04:41:58',NULL,NULL,6,'2026-10-26 04:41:58','2026-10-26 04:43:58'),(14,14,1,'preparing',4,'2026-10-26 04:40:58','2026-10-26 04:40:58',NULL,NULL,7,'2026-10-26 04:40:58','2026-10-26 04:43:58'),(15,15,1,'preparing',4,'2026-10-26 04:39:58','2026-10-26 04:39:58',NULL,NULL,8,'2026-10-26 04:39:58','2026-10-26 04:43:58'),(16,16,1,'preparing',4,'2026-10-26 04:38:58','2026-10-26 04:38:58',NULL,NULL,9,'2026-10-26 04:38:58','2026-10-26 04:43:58'),(17,17,1,'ready',4,'2026-10-26 04:37:58','2026-10-26 04:37:58','2026-10-26 04:49:58',NULL,10,'2026-10-26 04:37:58','2026-10-26 04:43:58'),(18,18,1,'ready',4,'2026-10-26 04:36:58','2026-10-26 04:36:58','2026-10-26 04:48:58',NULL,11,'2026-10-26 04:36:58','2026-10-26 04:43:58'),(19,19,1,'ready',4,'2026-10-26 04:35:58','2026-10-26 04:35:58','2026-10-26 04:47:58',NULL,12,'2026-10-26 04:35:58','2026-10-26 04:43:58'),(20,20,1,'ready',4,'2026-10-26 04:34:58','2026-10-26 04:34:58','2026-10-26 04:46:58',NULL,13,'2026-10-26 04:34:58','2026-10-26 04:43:58'),(21,21,1,'ready',4,'2026-10-26 04:33:58','2026-10-26 04:33:58','2026-10-26 04:45:58',NULL,14,'2026-10-26 04:33:58','2026-10-26 04:43:58'),(22,22,1,'ready',4,'2026-10-26 04:32:58','2026-10-26 04:32:58','2026-10-26 04:44:58',NULL,15,'2026-10-26 04:32:58','2026-10-26 04:43:58'),(23,23,1,'served',4,'2026-10-26 04:31:58','2026-10-26 04:31:58','2026-10-26 04:43:58','2026-10-26 05:01:58',16,'2026-10-26 04:31:58','2026-10-26 04:43:58'),(24,24,1,'served',4,'2026-10-26 04:30:58','2026-10-26 04:30:58','2026-10-26 04:42:58','2026-10-26 05:00:58',17,'2026-10-26 04:30:58','2026-10-26 04:43:58'),(25,25,1,'served',4,'2026-10-26 04:29:58','2026-10-26 04:29:58','2026-10-26 04:41:58','2026-10-26 04:59:58',18,'2026-10-26 04:29:58','2026-10-26 04:43:58'),(26,26,1,'served',4,'2026-10-26 04:28:58','2026-10-26 04:28:58','2026-10-26 04:40:58','2026-10-26 04:58:58',19,'2026-10-26 04:28:58','2026-10-26 04:43:58'),(27,27,1,'served',4,'2026-10-26 04:27:58','2026-10-26 04:27:58','2026-10-26 04:39:58','2026-10-26 04:57:58',20,'2026-10-26 04:27:58','2026-10-26 04:43:58'),(28,28,1,'served',4,'2026-10-26 04:26:58','2026-10-26 04:26:58','2026-10-26 04:38:58','2026-10-26 04:56:58',21,'2026-10-26 04:26:58','2026-10-26 04:43:58'),(29,29,1,'served',4,'2026-10-26 04:25:58','2026-10-26 04:25:58','2026-10-26 04:37:58','2026-10-26 04:55:58',22,'2026-10-26 04:25:58','2026-10-26 04:43:58'),(30,30,1,'served',4,'2026-10-26 04:24:58','2026-10-26 04:24:58','2026-10-26 04:36:58','2026-10-26 04:54:58',23,'2026-10-26 04:24:58','2026-10-26 04:43:58'),(31,31,1,'served',4,'2026-10-26 04:23:58','2026-10-26 04:23:58','2026-10-26 04:35:58','2026-10-26 04:53:58',24,'2026-10-26 04:23:58','2026-10-26 04:43:58'),(32,32,1,'served',4,'2026-10-26 04:22:58','2026-10-26 04:22:58','2026-10-26 04:34:58','2026-10-26 04:52:58',25,'2026-10-26 04:22:58','2026-10-26 04:43:58'),(33,33,1,'served',4,'2026-10-26 04:21:58','2026-10-26 04:21:58','2026-10-26 04:33:58','2026-10-26 04:51:58',26,'2026-10-26 04:21:58','2026-10-26 04:43:58'),(34,34,1,'served',4,'2026-10-26 04:20:58','2026-10-26 04:20:58','2026-10-26 04:32:58','2026-10-26 04:50:58',27,'2026-10-26 04:20:58','2026-10-26 04:43:58'),(35,35,1,'served',4,'2026-10-26 04:19:58','2026-10-26 04:19:58','2026-10-26 04:31:58','2026-10-26 04:49:58',28,'2026-10-26 04:19:58','2026-10-26 04:43:58'),(36,36,1,'served',4,'2026-10-26 04:18:58','2026-10-26 04:18:58','2026-10-26 04:30:58','2026-10-26 04:48:58',29,'2026-10-26 04:18:58','2026-10-26 04:43:58'),(37,37,1,'served',4,'2026-10-26 04:17:58','2026-10-26 04:17:58','2026-10-26 04:29:58','2026-10-26 04:47:58',30,'2026-10-26 04:17:58','2026-10-26 04:43:58'),(38,38,1,'served',4,'2026-10-26 04:16:58','2026-10-26 04:16:58','2026-10-26 04:28:58','2026-10-26 04:46:58',31,'2026-10-26 04:16:58','2026-10-26 04:43:58'),(39,39,1,'served',4,'2026-10-26 04:15:58','2026-10-26 04:15:58','2026-10-26 04:27:58','2026-10-26 04:45:58',32,'2026-10-26 04:15:58','2026-10-26 04:43:58'),(40,40,1,'served',4,'2026-10-26 04:14:58','2026-10-26 04:14:58','2026-10-26 04:26:58','2026-10-26 04:44:58',33,'2026-10-26 04:14:58','2026-10-26 04:43:58'),(41,41,1,'served',4,'2026-10-26 04:13:58','2026-10-26 04:13:58','2026-10-26 04:25:58','2026-10-26 04:43:58',34,'2026-10-26 04:13:58','2026-10-26 04:43:58'),(42,42,1,'served',4,'2026-10-26 04:12:58','2026-10-26 04:12:58','2026-10-26 04:24:58','2026-10-26 04:42:58',35,'2026-10-26 04:12:58','2026-10-26 04:43:58'),(43,43,1,'served',4,'2026-10-26 04:11:58','2026-10-26 04:11:58','2026-10-26 04:23:58','2026-10-26 04:41:58',36,'2026-10-26 04:11:58','2026-10-26 04:43:58'),(44,44,1,'served',4,'2026-10-26 04:10:58','2026-10-26 04:10:58','2026-10-26 04:22:58','2026-10-26 04:40:58',37,'2026-10-26 04:10:58','2026-10-26 04:43:58'),(45,45,1,'served',4,'2026-10-26 04:09:58','2026-10-26 04:09:58','2026-10-26 04:21:58','2026-10-26 04:39:58',38,'2026-10-26 04:09:58','2026-10-26 04:43:58'),(46,46,1,'served',4,'2026-10-26 04:43:58','2026-10-26 04:43:58','2026-10-26 04:55:58','2026-10-26 05:13:58',39,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(47,47,1,'served',4,'2026-10-26 04:42:58','2026-10-26 04:42:58','2026-10-26 04:54:58','2026-10-26 05:12:58',40,'2026-10-26 04:42:58','2026-10-26 04:43:58'),(48,48,1,'served',4,'2026-10-26 04:41:58','2026-10-26 04:41:58','2026-10-26 04:53:58','2026-10-26 05:11:58',41,'2026-10-26 04:41:58','2026-10-26 04:43:58'),(49,49,1,'served',4,'2026-10-26 04:40:58','2026-10-26 04:40:58','2026-10-26 04:52:58','2026-10-26 05:10:58',42,'2026-10-26 04:40:58','2026-10-26 04:43:58'),(50,50,1,'served',4,'2026-10-26 04:39:58','2026-10-26 04:39:58','2026-10-26 04:51:58','2026-10-26 05:09:58',43,'2026-10-26 04:39:58','2026-10-26 04:43:58'),(51,51,1,'served',4,'2026-10-26 04:38:58','2026-10-26 04:38:58','2026-10-26 04:50:58','2026-10-26 05:08:58',44,'2026-10-26 04:38:58','2026-10-26 04:43:58'),(52,52,1,'served',4,'2026-10-26 04:37:58','2026-10-26 04:37:58','2026-10-26 04:49:58','2026-10-26 05:07:58',45,'2026-10-26 04:37:58','2026-10-26 04:43:58'),(53,53,1,'served',4,'2026-10-26 04:36:58','2026-10-26 04:36:58','2026-10-26 04:48:58','2026-10-26 05:06:58',46,'2026-10-26 04:36:58','2026-10-26 04:43:58'),(54,54,1,'served',4,'2026-10-26 04:35:58','2026-10-26 04:35:58','2026-10-26 04:47:58','2026-10-26 05:05:58',47,'2026-10-26 04:35:58','2026-10-26 04:43:58'),(55,55,1,'served',4,'2026-10-26 04:34:58','2026-10-26 04:34:58','2026-10-26 04:46:58','2026-10-26 05:04:58',48,'2026-10-26 04:34:58','2026-10-26 04:43:58'),(56,56,1,'served',4,'2026-10-26 04:33:58','2026-10-26 04:33:58','2026-10-26 04:45:58','2026-10-26 05:03:58',49,'2026-10-26 04:33:58','2026-10-26 04:43:58'),(57,57,1,'served',4,'2026-10-26 04:32:58','2026-10-26 04:32:58','2026-10-26 04:44:58','2026-10-26 05:02:58',50,'2026-10-26 04:32:58','2026-10-26 04:43:58'),(58,58,1,'served',4,'2026-10-26 04:31:58','2026-10-26 04:31:58','2026-10-26 04:43:58','2026-10-26 05:01:58',51,'2026-10-26 04:31:58','2026-10-26 04:43:58'),(59,59,1,'served',4,'2026-10-26 04:30:58','2026-10-26 04:30:58','2026-10-26 04:42:58','2026-10-26 05:00:58',52,'2026-10-26 04:30:58','2026-10-26 04:43:58'),(60,60,1,'served',4,'2026-10-26 04:29:58','2026-10-26 04:29:58','2026-10-26 04:41:58','2026-10-26 04:59:58',53,'2026-10-26 04:29:58','2026-10-26 04:43:58'),(61,61,1,'served',4,'2026-10-26 04:28:58','2026-10-26 04:28:58','2026-10-26 04:40:58','2026-10-26 04:58:58',54,'2026-10-26 04:28:58','2026-10-26 04:43:58'),(62,62,1,'served',4,'2026-10-26 04:27:58','2026-10-26 04:27:58','2026-10-26 04:39:58','2026-10-26 04:57:58',55,'2026-10-26 04:27:58','2026-10-26 04:43:58'),(63,63,1,'served',4,'2026-10-26 04:26:58','2026-10-26 04:26:58','2026-10-26 04:38:58','2026-10-26 04:56:58',56,'2026-10-26 04:26:58','2026-10-26 04:43:58'),(64,64,1,'served',4,'2026-10-26 04:25:58','2026-10-26 04:25:58','2026-10-26 04:37:58','2026-10-26 04:55:58',57,'2026-10-26 04:25:58','2026-10-26 04:43:58'),(65,65,1,'served',4,'2026-10-26 04:24:58','2026-10-26 04:24:58','2026-10-26 04:36:58','2026-10-26 04:54:58',58,'2026-10-26 04:24:58','2026-10-26 04:43:58'),(66,66,1,'served',4,'2026-10-26 04:23:58','2026-10-26 04:23:58','2026-10-26 04:35:58','2026-10-26 04:53:58',59,'2026-10-26 04:23:58','2026-10-26 04:43:58'),(67,67,1,'served',4,'2026-10-26 04:22:58','2026-10-26 04:22:58','2026-10-26 04:34:58','2026-10-26 04:52:58',60,'2026-10-26 04:22:58','2026-10-26 04:43:58'),(68,68,1,'served',4,'2026-10-26 04:21:58','2026-10-26 04:21:58','2026-10-26 04:33:58','2026-10-26 04:51:58',61,'2026-10-26 04:21:58','2026-10-26 04:43:58'),(69,69,1,'served',4,'2026-10-26 04:20:58','2026-10-26 04:20:58','2026-10-26 04:32:58','2026-10-26 04:50:58',62,'2026-10-26 04:20:58','2026-10-26 04:43:58'),(70,70,1,'served',4,'2026-10-26 04:19:58','2026-10-26 04:19:58','2026-10-26 04:31:58','2026-10-26 04:49:58',63,'2026-10-26 04:19:58','2026-10-26 04:43:58'),(71,71,1,'served',4,'2026-10-26 04:18:58','2026-10-26 04:18:58','2026-10-26 04:30:58','2026-10-26 04:48:58',64,'2026-10-26 04:18:58','2026-10-26 04:43:58'),(72,72,1,'served',4,'2026-10-26 04:17:58','2026-10-26 04:17:58','2026-10-26 04:29:58','2026-10-26 04:47:58',65,'2026-10-26 04:17:58','2026-10-26 04:43:58'),(73,73,1,'served',4,'2026-10-26 04:16:58','2026-10-26 04:16:58','2026-10-26 04:28:58','2026-10-26 04:46:58',66,'2026-10-26 04:16:58','2026-10-26 04:43:58'),(74,74,1,'served',4,'2026-10-26 04:15:58','2026-10-26 04:15:58','2026-10-26 04:27:58','2026-10-26 04:45:58',67,'2026-10-26 04:15:58','2026-10-26 04:43:58'),(75,75,1,'served',4,'2026-10-26 04:14:58','2026-10-26 04:14:58','2026-10-26 04:26:58','2026-10-26 04:44:58',68,'2026-10-26 04:14:58','2026-10-26 04:43:58'),(76,76,1,'served',4,'2026-10-26 04:13:58','2026-10-26 04:13:58','2026-10-26 04:25:58','2026-10-26 04:43:58',69,'2026-10-26 04:13:58','2026-10-26 04:43:58'),(77,77,1,'served',4,'2026-10-26 04:12:58','2026-10-26 04:12:58','2026-10-26 04:24:58','2026-10-26 04:42:58',70,'2026-10-26 04:12:58','2026-10-26 04:43:58'),(78,78,1,'served',4,'2026-10-26 04:11:58','2026-10-26 04:11:58','2026-10-26 04:23:58','2026-10-26 04:41:58',71,'2026-10-26 04:11:58','2026-10-26 04:43:58'),(79,79,1,'served',4,'2026-10-26 04:10:58','2026-10-26 04:10:58','2026-10-26 04:22:58','2026-10-26 04:40:58',72,'2026-10-26 04:10:58','2026-10-26 04:43:58'),(80,80,1,'served',4,'2026-10-26 04:09:58','2026-10-26 04:09:58','2026-10-26 04:21:58','2026-10-26 04:39:58',73,'2026-10-26 04:09:58','2026-10-26 04:43:58'),(81,81,1,'served',4,'2026-10-26 04:43:58','2026-10-26 04:43:58','2026-10-26 04:55:58','2026-10-26 05:13:58',74,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(82,82,1,'served',4,'2026-10-26 04:42:58','2026-10-26 04:42:58','2026-10-26 04:54:58','2026-10-26 05:12:58',75,'2026-10-26 04:42:58','2026-10-26 04:43:58'),(83,83,1,'served',4,'2026-10-26 04:41:58','2026-10-26 04:41:58','2026-10-26 04:53:58','2026-10-26 05:11:58',76,'2026-10-26 04:41:58','2026-10-26 04:43:58'),(84,84,1,'served',4,'2026-10-26 04:40:58','2026-10-26 04:40:58','2026-10-26 04:52:58','2026-10-26 05:10:58',77,'2026-10-26 04:40:58','2026-10-26 04:43:58'),(85,85,1,'served',4,'2026-10-26 04:39:58','2026-10-26 04:39:58','2026-10-26 04:51:58','2026-10-26 05:09:58',78,'2026-10-26 04:39:58','2026-10-26 04:43:58'),(86,86,1,'served',4,'2026-10-26 04:38:58','2026-10-26 04:38:58','2026-10-26 04:50:58','2026-10-26 05:08:58',79,'2026-10-26 04:38:58','2026-10-26 04:43:58'),(87,87,1,'served',4,'2026-10-26 04:37:58','2026-10-26 04:37:58','2026-10-26 04:49:58','2026-10-26 05:07:58',80,'2026-10-26 04:37:58','2026-10-26 04:43:58'),(88,88,1,'served',4,'2026-10-26 04:36:58','2026-10-26 04:36:58','2026-10-26 04:48:58','2026-10-26 05:06:58',81,'2026-10-26 04:36:58','2026-10-26 04:43:58'),(89,89,1,'served',4,'2026-10-26 04:35:58','2026-10-26 04:35:58','2026-10-26 04:47:58','2026-10-26 05:05:58',82,'2026-10-26 04:35:58','2026-10-26 04:43:58'),(90,90,1,'served',4,'2026-10-26 04:34:58','2026-10-26 04:34:58','2026-10-26 04:46:58','2026-10-26 05:04:58',83,'2026-10-26 04:34:58','2026-10-26 04:43:58'),(91,91,1,'served',4,'2026-10-26 04:33:58','2026-10-26 04:33:58','2026-10-26 04:45:58','2026-10-26 05:03:58',84,'2026-10-26 04:33:58','2026-10-26 04:43:58'),(92,92,1,'served',4,'2026-10-26 04:32:58','2026-10-26 04:32:58','2026-10-26 04:44:58','2026-10-26 05:02:58',85,'2026-10-26 04:32:58','2026-10-26 04:43:58'),(93,93,1,'served',4,'2026-10-26 04:31:58','2026-10-26 04:31:58','2026-10-26 04:43:58','2026-10-26 05:01:58',86,'2026-10-26 04:31:58','2026-10-26 04:43:58'),(94,94,1,'served',4,'2026-10-26 04:30:58','2026-10-26 04:30:58','2026-10-26 04:42:58','2026-10-26 05:00:58',87,'2026-10-26 04:30:58','2026-10-26 04:43:58'),(95,95,1,'served',4,'2026-10-26 04:29:58','2026-10-26 04:29:58','2026-10-26 04:41:58','2026-10-26 04:59:58',88,'2026-10-26 04:29:58','2026-10-26 04:43:58'),(96,96,1,'served',4,'2026-10-26 04:28:58','2026-10-26 04:28:58','2026-10-26 04:40:58','2026-10-26 04:58:58',89,'2026-10-26 04:28:58','2026-10-26 04:43:58'),(97,97,1,'served',4,'2026-10-26 04:27:58','2026-10-26 04:27:58','2026-10-26 04:39:58','2026-10-26 04:57:58',90,'2026-10-26 04:27:58','2026-10-26 04:43:58'),(98,98,1,'served',4,'2026-10-26 04:26:58','2026-10-26 04:26:58','2026-10-26 04:38:58','2026-10-26 04:56:58',91,'2026-10-26 04:26:58','2026-10-26 04:43:58'),(99,99,1,'served',4,'2026-10-26 04:25:58','2026-10-26 04:25:58','2026-10-26 04:37:58','2026-10-26 04:55:58',92,'2026-10-26 04:25:58','2026-10-26 04:43:58'),(100,100,1,'served',4,'2026-10-26 04:24:58','2026-10-26 04:24:58','2026-10-26 04:36:58','2026-10-26 04:54:58',93,'2026-10-26 04:24:58','2026-10-26 04:43:58'),(101,101,1,'served',4,'2026-10-26 04:23:58','2026-10-26 04:23:58','2026-10-26 04:35:58','2026-10-26 04:53:58',94,'2026-10-26 04:23:58','2026-10-26 04:43:58'),(102,102,1,'served',4,'2026-10-26 04:22:58','2026-10-26 04:22:58','2026-10-26 04:34:58','2026-10-26 04:52:58',95,'2026-10-26 04:22:58','2026-10-26 04:43:58'),(103,103,1,'served',4,'2026-10-26 04:21:58','2026-10-26 04:21:58','2026-10-26 04:33:58','2026-10-26 04:51:58',96,'2026-10-26 04:21:58','2026-10-26 04:43:58'),(104,104,1,'served',4,'2026-10-26 04:20:58','2026-10-26 04:20:58','2026-10-26 04:32:58','2026-10-26 04:50:58',97,'2026-10-26 04:20:58','2026-10-26 04:43:58'),(105,105,1,'served',4,'2026-10-26 04:19:58','2026-10-26 04:19:58','2026-10-26 04:31:58','2026-10-26 04:49:58',98,'2026-10-26 04:19:58','2026-10-26 04:43:58'),(106,106,1,'served',4,'2026-10-26 04:18:58','2026-10-26 04:18:58','2026-10-26 04:30:58','2026-10-26 04:48:58',99,'2026-10-26 04:18:58','2026-10-26 04:43:58'),(107,107,1,'served',4,'2026-10-26 04:17:58','2026-10-26 04:17:58','2026-10-26 04:29:58','2026-10-26 04:47:58',101,'2026-10-26 04:17:58','2026-10-26 04:43:58'),(108,108,1,'served',4,'2026-10-26 04:16:58','2026-10-26 04:16:58','2026-10-26 04:28:58','2026-10-26 04:46:58',102,'2026-10-26 04:16:58','2026-10-26 04:43:58'),(109,109,1,'served',4,'2026-10-26 04:15:58','2026-10-26 04:15:58','2026-10-26 04:27:58','2026-10-26 04:45:58',103,'2026-10-26 04:15:58','2026-10-26 04:43:58'),(110,110,1,'served',4,'2026-10-26 04:14:58','2026-10-26 04:14:58','2026-10-26 04:26:58','2026-10-26 04:44:58',104,'2026-10-26 04:14:58','2026-10-26 04:43:58'),(111,111,1,'pending',2,'2026-10-26 04:57:39',NULL,NULL,NULL,1,'2026-10-26 04:57:39','2026-10-26 04:57:58'),(112,112,1,'preparing',2,'2026-10-26 04:59:27','2026-10-26 05:02:22',NULL,NULL,1,'2026-10-26 04:59:27','2026-10-26 05:02:22'),(113,113,1,'pending',2,'2026-10-26 05:01:30',NULL,NULL,NULL,1,'2026-10-26 05:01:30','2026-10-26 05:01:40');
/*!40000 ALTER TABLE `order_kitchen_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_payments`
--

DROP TABLE IF EXISTS `order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_payments_received_by_foreign` (`received_by`),
  KEY `order_payments_order_id_created_at_index` (`order_id`,`created_at`),
  KEY `order_payments_payment_method_index` (`payment_method`),
  CONSTRAINT `order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_payments`
--

LOCK TABLES `order_payments` WRITE;
/*!40000 ALTER TABLE `order_payments` DISABLE KEYS */;
INSERT INTO `order_payments` VALUES (1,1,250.00,'bkash',NULL,'Initial payment during order creation.',1,'2026-10-06 19:03:48','2026-10-06 19:03:48'),(2,2,250.00,'cash',NULL,'Additional payment received during served order update.',1,'2026-10-06 23:26:02','2026-10-06 23:26:02'),(3,4,250.00,'cash',NULL,'Additional payment received during served order update.',1,'2026-10-08 03:56:29','2026-10-08 03:56:29'),(4,5,250.00,'cash',NULL,'Initial payment during order creation.',1,'2026-10-13 09:10:09','2026-10-13 09:10:09'),(5,6,750.00,'cash',NULL,'Initial payment during order creation.',1,'2026-10-14 21:45:48','2026-10-14 21:45:48'),(6,7,250.00,'cash',NULL,'Additional payment received during served order update.',1,'2026-10-24 23:05:17','2026-10-24 23:05:17'),(7,8,250.00,'cash',NULL,'Initial payment during order creation.',1,'2026-10-25 03:23:52','2026-10-25 03:23:52'),(8,9,500.00,'cash',NULL,'Initial payment during order creation.',1,'2026-10-26 02:22:42','2026-10-26 02:22:42'),(9,10,250.00,'cash',NULL,'Initial payment during order creation.',1,'2026-10-26 02:24:12','2026-10-26 02:24:12'),(10,11,205.00,'cash','DEMO100-PAY-001','DEMO DATA payment',4,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(11,12,230.00,'cash','DEMO100-PAY-002','DEMO DATA payment',5,'2026-10-26 04:42:58','2026-10-26 04:43:58'),(12,13,255.00,'cash','DEMO100-PAY-003','DEMO DATA payment',6,'2026-10-26 04:41:58','2026-10-26 04:43:58'),(13,14,280.00,'cash','DEMO100-PAY-004','DEMO DATA payment',7,'2026-10-26 04:40:58','2026-10-26 04:43:58'),(14,15,305.00,'cash','DEMO100-PAY-005','DEMO DATA payment',8,'2026-10-26 04:39:58','2026-10-26 04:43:58'),(15,16,330.00,'cash','DEMO100-PAY-006','DEMO DATA payment',9,'2026-10-26 04:38:58','2026-10-26 04:43:58'),(16,17,355.00,'cash','DEMO100-PAY-007','DEMO DATA payment',10,'2026-10-26 04:37:58','2026-10-26 04:43:58'),(17,18,380.00,'cash','DEMO100-PAY-008','DEMO DATA payment',11,'2026-10-26 04:36:58','2026-10-26 04:43:58'),(18,19,405.00,'cash','DEMO100-PAY-009','DEMO DATA payment',12,'2026-10-26 04:35:58','2026-10-26 04:43:58'),(19,20,430.00,'cash','DEMO100-PAY-010','DEMO DATA payment',13,'2026-10-26 04:34:58','2026-10-26 04:43:58'),(20,21,455.00,'cash','DEMO100-PAY-011','DEMO DATA payment',14,'2026-10-26 04:33:58','2026-10-26 04:43:58'),(21,22,480.00,'cash','DEMO100-PAY-012','DEMO DATA payment',15,'2026-10-26 04:32:58','2026-10-26 04:43:58'),(22,23,505.00,'cash','DEMO100-PAY-013','DEMO DATA payment',16,'2026-10-26 04:31:58','2026-10-26 04:43:58'),(23,24,530.00,'cash','DEMO100-PAY-014','DEMO DATA payment',17,'2026-10-26 04:30:58','2026-10-26 04:43:58'),(24,25,180.00,'cash','DEMO100-PAY-015','DEMO DATA payment',18,'2026-10-26 04:29:58','2026-10-26 04:43:58'),(25,26,205.00,'cash','DEMO100-PAY-016','DEMO DATA payment',19,'2026-10-26 04:28:58','2026-10-26 04:43:58'),(26,27,230.00,'cash','DEMO100-PAY-017','DEMO DATA payment',20,'2026-10-26 04:27:58','2026-10-26 04:43:58'),(27,28,255.00,'cash','DEMO100-PAY-018','DEMO DATA payment',21,'2026-10-26 04:26:58','2026-10-26 04:43:58'),(28,29,280.00,'cash','DEMO100-PAY-019','DEMO DATA payment',22,'2026-10-26 04:25:58','2026-10-26 04:43:58'),(29,30,305.00,'cash','DEMO100-PAY-020','DEMO DATA payment',23,'2026-10-26 04:24:58','2026-10-26 04:43:58'),(30,31,330.00,'cash','DEMO100-PAY-021','DEMO DATA payment',24,'2026-10-26 04:23:58','2026-10-26 04:43:58'),(31,32,355.00,'cash','DEMO100-PAY-022','DEMO DATA payment',25,'2026-10-26 04:22:58','2026-10-26 04:43:58'),(32,33,380.00,'cash','DEMO100-PAY-023','DEMO DATA payment',26,'2026-10-26 04:21:58','2026-10-26 04:43:58'),(33,34,405.00,'cash','DEMO100-PAY-024','DEMO DATA payment',27,'2026-10-26 04:20:58','2026-10-26 04:43:58'),(34,35,430.00,'cash','DEMO100-PAY-025','DEMO DATA payment',28,'2026-10-26 04:19:58','2026-10-26 04:43:58'),(35,36,455.00,'cash','DEMO100-PAY-026','DEMO DATA payment',29,'2026-10-26 04:18:58','2026-10-26 04:43:58'),(36,37,480.00,'cash','DEMO100-PAY-027','DEMO DATA payment',30,'2026-10-26 04:17:58','2026-10-26 04:43:58'),(37,38,505.00,'cash','DEMO100-PAY-028','DEMO DATA payment',31,'2026-10-26 04:16:58','2026-10-26 04:43:58'),(38,39,530.00,'cash','DEMO100-PAY-029','DEMO DATA payment',32,'2026-10-26 04:15:58','2026-10-26 04:43:58'),(39,40,180.00,'cash','DEMO100-PAY-030','DEMO DATA payment',33,'2026-10-26 04:14:58','2026-10-26 04:43:58'),(40,41,205.00,'cash','DEMO100-PAY-031','DEMO DATA payment',34,'2026-10-26 04:13:58','2026-10-26 04:43:58'),(41,42,230.00,'cash','DEMO100-PAY-032','DEMO DATA payment',35,'2026-10-26 04:12:58','2026-10-26 04:43:58'),(42,43,255.00,'cash','DEMO100-PAY-033','DEMO DATA payment',36,'2026-10-26 04:11:58','2026-10-26 04:43:58'),(43,44,280.00,'cash','DEMO100-PAY-034','DEMO DATA payment',37,'2026-10-26 04:10:58','2026-10-26 04:43:58'),(44,45,305.00,'cash','DEMO100-PAY-035','DEMO DATA payment',38,'2026-10-26 04:09:58','2026-10-26 04:43:58'),(45,46,330.00,'cash','DEMO100-PAY-036','DEMO DATA payment',39,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(46,47,355.00,'cash','DEMO100-PAY-037','DEMO DATA payment',40,'2026-10-26 04:42:58','2026-10-26 04:43:58'),(47,48,380.00,'cash','DEMO100-PAY-038','DEMO DATA payment',41,'2026-10-26 04:41:58','2026-10-26 04:43:58'),(48,49,405.00,'cash','DEMO100-PAY-039','DEMO DATA payment',42,'2026-10-26 04:40:58','2026-10-26 04:43:58'),(49,50,430.00,'cash','DEMO100-PAY-040','DEMO DATA payment',43,'2026-10-26 04:39:58','2026-10-26 04:43:58'),(50,51,455.00,'cash','DEMO100-PAY-041','DEMO DATA payment',44,'2026-10-26 04:38:58','2026-10-26 04:43:58'),(51,52,480.00,'cash','DEMO100-PAY-042','DEMO DATA payment',45,'2026-10-26 04:37:58','2026-10-26 04:43:58'),(52,53,505.00,'cash','DEMO100-PAY-043','DEMO DATA payment',46,'2026-10-26 04:36:58','2026-10-26 04:43:58'),(53,54,530.00,'cash','DEMO100-PAY-044','DEMO DATA payment',47,'2026-10-26 04:35:58','2026-10-26 04:43:58'),(54,55,180.00,'cash','DEMO100-PAY-045','DEMO DATA payment',48,'2026-10-26 04:34:58','2026-10-26 04:43:58'),(55,56,205.00,'cash','DEMO100-PAY-046','DEMO DATA payment',49,'2026-10-26 04:33:58','2026-10-26 04:43:58'),(56,57,230.00,'cash','DEMO100-PAY-047','DEMO DATA payment',50,'2026-10-26 04:32:58','2026-10-26 04:43:58'),(57,58,255.00,'cash','DEMO100-PAY-048','DEMO DATA payment',51,'2026-10-26 04:31:58','2026-10-26 04:43:58'),(58,59,280.00,'cash','DEMO100-PAY-049','DEMO DATA payment',52,'2026-10-26 04:30:58','2026-10-26 04:43:58'),(59,60,305.00,'cash','DEMO100-PAY-050','DEMO DATA payment',53,'2026-10-26 04:29:58','2026-10-26 04:43:58'),(60,61,330.00,'cash','DEMO100-PAY-051','DEMO DATA payment',54,'2026-10-26 04:28:58','2026-10-26 04:43:58'),(61,62,355.00,'cash','DEMO100-PAY-052','DEMO DATA payment',55,'2026-10-26 04:27:58','2026-10-26 04:43:58'),(62,63,380.00,'cash','DEMO100-PAY-053','DEMO DATA payment',56,'2026-10-26 04:26:58','2026-10-26 04:43:58'),(63,64,405.00,'cash','DEMO100-PAY-054','DEMO DATA payment',57,'2026-10-26 04:25:58','2026-10-26 04:43:58'),(64,65,430.00,'cash','DEMO100-PAY-055','DEMO DATA payment',58,'2026-10-26 04:24:58','2026-10-26 04:43:58'),(65,66,455.00,'cash','DEMO100-PAY-056','DEMO DATA payment',59,'2026-10-26 04:23:58','2026-10-26 04:43:58'),(66,67,480.00,'cash','DEMO100-PAY-057','DEMO DATA payment',60,'2026-10-26 04:22:58','2026-10-26 04:43:58'),(67,68,505.00,'cash','DEMO100-PAY-058','DEMO DATA payment',61,'2026-10-26 04:21:58','2026-10-26 04:43:58'),(68,69,530.00,'cash','DEMO100-PAY-059','DEMO DATA payment',62,'2026-10-26 04:20:58','2026-10-26 04:43:58'),(69,70,180.00,'cash','DEMO100-PAY-060','DEMO DATA payment',63,'2026-10-26 04:19:58','2026-10-26 04:43:58'),(70,71,205.00,'cash','DEMO100-PAY-061','DEMO DATA payment',64,'2026-10-26 04:18:58','2026-10-26 04:43:58'),(71,72,230.00,'cash','DEMO100-PAY-062','DEMO DATA payment',65,'2026-10-26 04:17:58','2026-10-26 04:43:58'),(72,73,255.00,'cash','DEMO100-PAY-063','DEMO DATA payment',66,'2026-10-26 04:16:58','2026-10-26 04:43:58'),(73,74,280.00,'cash','DEMO100-PAY-064','DEMO DATA payment',67,'2026-10-26 04:15:58','2026-10-26 04:43:58'),(74,75,305.00,'cash','DEMO100-PAY-065','DEMO DATA payment',68,'2026-10-26 04:14:58','2026-10-26 04:43:58'),(75,76,330.00,'cash','DEMO100-PAY-066','DEMO DATA payment',69,'2026-10-26 04:13:58','2026-10-26 04:43:58'),(76,77,355.00,'cash','DEMO100-PAY-067','DEMO DATA payment',70,'2026-10-26 04:12:58','2026-10-26 04:43:58'),(77,78,380.00,'cash','DEMO100-PAY-068','DEMO DATA payment',71,'2026-10-26 04:11:58','2026-10-26 04:43:58'),(78,79,405.00,'cash','DEMO100-PAY-069','DEMO DATA payment',72,'2026-10-26 04:10:58','2026-10-26 04:43:58'),(79,80,430.00,'cash','DEMO100-PAY-070','DEMO DATA payment',73,'2026-10-26 04:09:58','2026-10-26 04:43:58'),(80,81,455.00,'cash','DEMO100-PAY-071','DEMO DATA payment',74,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(81,82,480.00,'cash','DEMO100-PAY-072','DEMO DATA payment',75,'2026-10-26 04:42:58','2026-10-26 04:43:58'),(82,83,505.00,'cash','DEMO100-PAY-073','DEMO DATA payment',76,'2026-10-26 04:41:58','2026-10-26 04:43:58'),(83,84,530.00,'cash','DEMO100-PAY-074','DEMO DATA payment',77,'2026-10-26 04:40:58','2026-10-26 04:43:58'),(84,85,180.00,'cash','DEMO100-PAY-075','DEMO DATA payment',78,'2026-10-26 04:39:58','2026-10-26 04:43:58'),(85,86,205.00,'cash','DEMO100-PAY-076','DEMO DATA payment',79,'2026-10-26 04:38:58','2026-10-26 04:43:58'),(86,87,230.00,'cash','DEMO100-PAY-077','DEMO DATA payment',80,'2026-10-26 04:37:58','2026-10-26 04:43:58'),(87,88,255.00,'cash','DEMO100-PAY-078','DEMO DATA payment',81,'2026-10-26 04:36:58','2026-10-26 04:43:58'),(88,89,280.00,'cash','DEMO100-PAY-079','DEMO DATA payment',82,'2026-10-26 04:35:58','2026-10-26 04:43:58'),(89,90,305.00,'cash','DEMO100-PAY-080','DEMO DATA payment',83,'2026-10-26 04:34:58','2026-10-26 04:43:58'),(90,91,330.00,'cash','DEMO100-PAY-081','DEMO DATA payment',84,'2026-10-26 04:33:58','2026-10-26 04:43:58'),(91,92,355.00,'cash','DEMO100-PAY-082','DEMO DATA payment',85,'2026-10-26 04:32:58','2026-10-26 04:43:58'),(92,93,380.00,'cash','DEMO100-PAY-083','DEMO DATA payment',86,'2026-10-26 04:31:58','2026-10-26 04:43:58'),(93,94,405.00,'cash','DEMO100-PAY-084','DEMO DATA payment',87,'2026-10-26 04:30:58','2026-10-26 04:43:58'),(94,95,430.00,'cash','DEMO100-PAY-085','DEMO DATA payment',88,'2026-10-26 04:29:58','2026-10-26 04:43:58'),(95,96,455.00,'cash','DEMO100-PAY-086','DEMO DATA payment',89,'2026-10-26 04:28:58','2026-10-26 04:43:58'),(96,97,480.00,'cash','DEMO100-PAY-087','DEMO DATA payment',90,'2026-10-26 04:27:58','2026-10-26 04:43:58'),(97,98,505.00,'cash','DEMO100-PAY-088','DEMO DATA payment',91,'2026-10-26 04:26:58','2026-10-26 04:43:58'),(98,99,530.00,'cash','DEMO100-PAY-089','DEMO DATA payment',92,'2026-10-26 04:25:58','2026-10-26 04:43:58'),(99,100,180.00,'cash','DEMO100-PAY-090','DEMO DATA payment',93,'2026-10-26 04:24:58','2026-10-26 04:43:58'),(100,101,205.00,'cash','DEMO100-PAY-091','DEMO DATA payment',94,'2026-10-26 04:23:58','2026-10-26 04:43:58'),(101,102,230.00,'cash','DEMO100-PAY-092','DEMO DATA payment',95,'2026-10-26 04:22:58','2026-10-26 04:43:58'),(102,103,255.00,'cash','DEMO100-PAY-093','DEMO DATA payment',96,'2026-10-26 04:21:58','2026-10-26 04:43:58'),(103,104,280.00,'cash','DEMO100-PAY-094','DEMO DATA payment',97,'2026-10-26 04:20:58','2026-10-26 04:43:58'),(104,105,305.00,'cash','DEMO100-PAY-095','DEMO DATA payment',98,'2026-10-26 04:19:58','2026-10-26 04:43:58'),(105,106,330.00,'cash','DEMO100-PAY-096','DEMO DATA payment',99,'2026-10-26 04:18:58','2026-10-26 04:43:58'),(106,107,355.00,'cash','DEMO100-PAY-097','DEMO DATA payment',101,'2026-10-26 04:17:58','2026-10-26 04:43:58'),(107,108,380.00,'cash','DEMO100-PAY-098','DEMO DATA payment',102,'2026-10-26 04:16:58','2026-10-26 04:43:58'),(108,109,405.00,'cash','DEMO100-PAY-099','DEMO DATA payment',103,'2026-10-26 04:15:58','2026-10-26 04:43:58'),(109,110,430.00,'cash','DEMO100-PAY-100','DEMO DATA payment',104,'2026-10-26 04:14:58','2026-10-26 04:43:58'),(110,111,750.00,'cash',NULL,'Initial payment during order creation.',1,'2026-10-26 04:57:39','2026-10-26 04:57:39'),(111,112,205.00,'cash',NULL,'Initial payment during order creation.',1,'2026-10-26 04:59:27','2026-10-26 04:59:27'),(112,113,205.00,'cash',NULL,'Initial payment during order creation.',1,'2026-10-26 05:01:30','2026-10-26 05:01:30');
/*!40000 ALTER TABLE `order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_recipe_consumption_items`
--

DROP TABLE IF EXISTS `order_recipe_consumption_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_recipe_consumption_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_recipe_consumption_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `restaurant_quantity_before` decimal(14,4) NOT NULL,
  `restaurant_quantity_after` decimal(14,4) NOT NULL,
  `source_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`source_breakdown`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orci_consumption_material_unique` (`order_recipe_consumption_id`,`raw_material_id`),
  KEY `orci_material_idx` (`raw_material_id`),
  CONSTRAINT `orci_consumption_fk` FOREIGN KEY (`order_recipe_consumption_id`) REFERENCES `order_recipe_consumptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orci_material_fk` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_recipe_consumption_items`
--

LOCK TABLES `order_recipe_consumption_items` WRITE;
/*!40000 ALTER TABLE `order_recipe_consumption_items` DISABLE KEYS */;
INSERT INTO `order_recipe_consumption_items` VALUES (1,1,2,'Burger Test Batch','pcs',1.0000,1.0000,10.0000,9.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":1,\"menu_item_id\":1,\"menu_item_name\":\"Classic Test Burger\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":1,\"mapping_variant_id\":null,\"order_quantity\":1,\"recipe_quantity\":1,\"consumed_quantity\":1}]','Recipe consumption for order ORD-20261007-HNDBAG, kitchen batch #1.','2026-10-06 20:19:48','2026-10-06 20:19:48'),(2,2,2,'Burger Test Batch','pcs',1.0000,1.0000,9.0000,8.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":2,\"menu_item_id\":1,\"menu_item_name\":\"Classic Test Burger\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":1,\"mapping_variant_id\":null,\"order_quantity\":1,\"recipe_quantity\":1,\"consumed_quantity\":1}]','Recipe consumption for order ORD-20261007-F8NBBO, kitchen batch #1.','2026-10-06 23:11:38','2026-10-06 23:11:38'),(3,3,2,'Burger Test Batch','pcs',1.0000,1.0000,8.0000,7.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":4,\"menu_item_id\":1,\"menu_item_name\":\"Classic Burger\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":1,\"mapping_variant_id\":null,\"order_quantity\":1,\"recipe_quantity\":1,\"consumed_quantity\":1}]','Recipe consumption for order ORD-20261008-WGV5O2, kitchen batch #1.','2026-10-08 03:55:40','2026-10-08 03:55:40'),(4,4,2,'Burger Test Batch','pcs',1.0000,1.0000,7.0000,6.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":5,\"menu_item_id\":1,\"menu_item_name\":\"Classic Burger\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":1,\"mapping_variant_id\":null,\"order_quantity\":1,\"recipe_quantity\":1,\"consumed_quantity\":1}]','Recipe consumption for order ORD-20261013-RPJBSG, kitchen batch #1.','2026-10-13 09:11:32','2026-10-13 09:11:32'),(5,5,2,'Burger Test Batch','pcs',3.0000,1.0000,6.0000,3.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":6,\"menu_item_id\":1,\"menu_item_name\":\"Classic Burger\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":1,\"mapping_variant_id\":null,\"order_quantity\":3,\"recipe_quantity\":1,\"consumed_quantity\":3}]','Recipe consumption for order ORD-20261015-VZ5STH, kitchen batch #1.','2026-10-14 21:47:57','2026-10-14 21:47:57'),(6,6,2,'Burger Test Batch','pcs',1.0000,1.0000,3.0000,2.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":7,\"menu_item_id\":1,\"menu_item_name\":\"Classic Burger\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":1,\"mapping_variant_id\":null,\"order_quantity\":1,\"recipe_quantity\":1,\"consumed_quantity\":1}]','Recipe consumption for order ORD-20261025-LLNHH1, kitchen batch #1.','2026-10-24 22:34:26','2026-10-24 22:34:26'),(7,7,2,'Burger Test Batch','pcs',1.0000,1.0000,2.0000,1.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":8,\"menu_item_id\":1,\"menu_item_name\":\"Classic Burger\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":1,\"mapping_variant_id\":null,\"order_quantity\":1,\"recipe_quantity\":1,\"consumed_quantity\":1}]','Recipe consumption for order ORD-20261025-QKLF8J, kitchen batch #1.','2026-10-26 02:05:19','2026-10-26 02:05:19'),(8,8,2,'Burger Test Batch','pcs',1.0000,1.0000,1.0000,0.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":10,\"menu_item_id\":1,\"menu_item_name\":\"Classic Burger\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":1,\"mapping_variant_id\":null,\"order_quantity\":1,\"recipe_quantity\":1,\"consumed_quantity\":1}]','Recipe consumption for order ORD-20261026-SXGNQX, kitchen batch #1.','2026-10-26 02:24:29','2026-10-26 02:24:29'),(9,9,7,'Chicken','kg',100.0000,1.0000,210.0000,110.0000,'[{\"source_type\":\"menu_item\",\"order_item_id\":112,\"menu_item_id\":2,\"menu_item_name\":\"DEMO100-Menu Item 001\",\"menu_item_variant_id\":null,\"variant_name\":null,\"recipe_mapping_id\":3,\"mapping_variant_id\":null,\"order_quantity\":1,\"recipe_quantity\":100,\"consumed_quantity\":100}]','Recipe consumption for order ORD-20261026-GGCQ4L, kitchen batch #1.','2026-10-26 05:02:22','2026-10-26 05:02:22');
/*!40000 ALTER TABLE `order_recipe_consumption_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_recipe_consumptions`
--

DROP TABLE IF EXISTS `order_recipe_consumptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_recipe_consumptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `order_kitchen_batch_id` bigint(20) unsigned NOT NULL,
  `order_number` varchar(100) NOT NULL,
  `trigger` varchar(50) NOT NULL DEFAULT 'start_preparing',
  `order_status_snapshot` varchar(30) NOT NULL,
  `consumed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_recipe_consumptions_order_batch_unique` (`order_id`,`order_kitchen_batch_id`),
  KEY `order_recipe_consumptions_created_by_foreign` (`created_by`),
  KEY `order_recipe_consumptions_batch_index` (`order_kitchen_batch_id`),
  KEY `orc_consumed_at_idx` (`consumed_at`),
  CONSTRAINT `order_recipe_consumptions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_recipe_consumptions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_recipe_consumptions_order_kitchen_batch_id_foreign` FOREIGN KEY (`order_kitchen_batch_id`) REFERENCES `order_kitchen_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_recipe_consumptions`
--

LOCK TABLES `order_recipe_consumptions` WRITE;
/*!40000 ALTER TABLE `order_recipe_consumptions` DISABLE KEYS */;
INSERT INTO `order_recipe_consumptions` VALUES (1,1,1,'ORD-20261007-HNDBAG','start_preparing','preparing','2026-10-06 20:19:48',2,'2026-10-06 20:19:48','2026-10-06 20:19:48'),(2,2,2,'ORD-20261007-F8NBBO','start_preparing','preparing','2026-10-06 23:11:38',2,'2026-10-06 23:11:38','2026-10-06 23:11:38'),(3,4,4,'ORD-20261008-WGV5O2','start_preparing','preparing','2026-10-08 03:55:40',2,'2026-10-08 03:55:40','2026-10-08 03:55:40'),(4,5,5,'ORD-20261013-RPJBSG','start_preparing','preparing','2026-10-13 09:11:32',2,'2026-10-13 09:11:32','2026-10-13 09:11:32'),(5,6,6,'ORD-20261015-VZ5STH','start_preparing','preparing','2026-10-14 21:47:57',2,'2026-10-14 21:47:57','2026-10-14 21:47:57'),(6,7,7,'ORD-20261025-LLNHH1','start_preparing','preparing','2026-10-24 22:34:26',2,'2026-10-24 22:34:26','2026-10-24 22:34:26'),(7,8,8,'ORD-20261025-QKLF8J','start_preparing','preparing','2026-10-26 02:05:19',2,'2026-10-26 02:05:19','2026-10-26 02:05:19'),(8,10,10,'ORD-20261026-SXGNQX','start_preparing','preparing','2026-10-26 02:24:29',2,'2026-10-26 02:24:29','2026-10-26 02:24:29'),(9,112,112,'ORD-20261026-GGCQ4L','start_preparing','preparing','2026-10-26 05:02:22',2,'2026-10-26 05:02:22','2026-10-26 05:02:22');
/*!40000 ALTER TABLE `order_recipe_consumptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_tables`
--

DROP TABLE IF EXISTS `order_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `restaurant_table_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_tables_order_id_restaurant_table_id_unique` (`order_id`,`restaurant_table_id`),
  KEY `order_tables_restaurant_table_id_order_id_index` (`restaurant_table_id`,`order_id`),
  CONSTRAINT `order_tables_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_tables_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_tables`
--

LOCK TABLES `order_tables` WRITE;
/*!40000 ALTER TABLE `order_tables` DISABLE KEYS */;
INSERT INTO `order_tables` VALUES (1,2,1,1,'2026-10-06 22:19:01','2026-10-06 22:19:01'),(2,3,1,1,'2026-10-06 23:28:12','2026-10-06 23:28:12'),(3,4,1,1,'2026-10-08 03:54:05','2026-10-08 03:54:05'),(4,5,4,1,'2026-10-13 09:10:09','2026-10-13 09:10:09'),(5,6,4,1,'2026-10-14 21:45:48','2026-10-14 21:45:48'),(6,7,4,1,'2026-10-24 22:08:16','2026-10-24 22:08:16');
/*!40000 ALTER TABLE `order_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `restaurant_table_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `fulfillment_type` varchar(20) NOT NULL DEFAULT 'dine_in',
  `pickup_token` varchar(20) DEFAULT NULL,
  `pickup_status` varchar(20) DEFAULT NULL,
  `picked_up_at` datetime DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(30) NOT NULL DEFAULT 'due',
  `payment_method` varchar(30) DEFAULT NULL,
  `payment_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_breakdown`)),
  `payment_reference` varchar(150) DEFAULT NULL,
  `order_note` text DEFAULT NULL,
  `kitchen_note` text DEFAULT NULL,
  `sent_to_kitchen_at` datetime DEFAULT NULL,
  `preparing_at` datetime DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL,
  `served_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `is_customer_spend_recorded` tinyint(1) NOT NULL DEFAULT 0,
  `customer_spend_recorded_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `chef_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_pickup_token_unique` (`pickup_token`),
  KEY `orders_created_by_foreign` (`created_by`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_payment_method_index` (`payment_method`),
  KEY `orders_created_at_index` (`created_at`),
  KEY `orders_restaurant_table_id_status_index` (`restaurant_table_id`,`status`),
  KEY `orders_customer_id_completed_at_index` (`customer_id`,`completed_at`),
  KEY `orders_reservation_id_index` (`reservation_id`),
  KEY `orders_chef_status_index` (`chef_id`,`status`),
  KEY `orders_pickup_board_index` (`fulfillment_type`,`status`,`pickup_status`),
  CONSTRAINT `orders_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'ORD-20261007-HNDBAG',NULL,NULL,NULL,NULL,NULL,NULL,'dine_in',NULL,NULL,NULL,'completed',250.00,0.00,0.00,0.00,250.00,250.00,0.00,'paid','bkash',NULL,NULL,'POS Guest Count: 1',NULL,'2026-10-07 01:03:48','2026-10-07 02:19:48','2026-10-07 02:20:01','2026-10-07 03:02:37','2026-10-07 03:02:48',NULL,NULL,0,NULL,1,2,'2026-10-06 19:03:48','2026-10-06 21:02:48',NULL),(2,'ORD-20261007-F8NBBO',1,'Tasnima Akther Tisha','+1 (844) 536-1838','shraboniakter554@gmail.com',1,NULL,'dine_in',NULL,NULL,NULL,'completed',250.00,0.00,0.00,0.00,250.00,250.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-07 04:19:01','2026-10-07 05:11:38','2026-10-07 05:23:53','2026-10-07 05:24:07','2026-10-07 05:26:02',NULL,NULL,1,'2026-10-07 05:26:03',1,2,'2026-10-06 22:19:01','2026-10-06 23:26:03',NULL),(3,'ORD-20261007-JLONTH',3,'Tasnima','+1 (646) 633-6464','shraboniakter@gmail.com',1,NULL,'dine_in',NULL,NULL,NULL,'canceled',250.00,0.00,0.00,0.00,250.00,0.00,0.00,'due',NULL,NULL,NULL,NULL,NULL,'2026-10-07 05:28:12',NULL,NULL,NULL,NULL,'2026-10-07 08:23:19','Wrong order',0,NULL,1,2,'2026-10-06 23:28:12','2026-10-07 02:23:19',NULL),(4,'ORD-20261008-WGV5O2',2,'Tisha','07364466764',NULL,1,2,'dine_in',NULL,NULL,NULL,'completed',250.00,0.00,0.00,0.00,250.00,250.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-08 09:54:05','2026-10-08 09:55:40','2026-10-08 09:55:46','2026-10-08 09:56:01','2026-10-08 09:56:30',NULL,NULL,1,'2026-10-08 09:56:30',1,2,'2026-10-08 03:54:05','2026-10-08 03:56:30',NULL),(5,'ORD-20261013-RPJBSG',1,'Tasnima Akther Tisha','+1 (844) 536-1838','shraboniakter554@gmail.com',4,6,'dine_in',NULL,NULL,NULL,'completed',250.00,0.00,0.00,0.00,250.00,250.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-13 15:10:09','2026-10-13 15:11:32','2026-10-13 15:11:35','2026-10-13 15:11:48','2026-10-13 15:11:59',NULL,NULL,1,'2026-10-13 15:11:59',1,2,'2026-10-13 09:10:09','2026-10-13 09:11:59',NULL),(6,'ORD-20261015-VZ5STH',6,'Vaughan Roberson','+1 (528) 232-8797','jujo@mailinator.com',4,5,'dine_in',NULL,NULL,NULL,'completed',750.00,0.00,0.00,0.00,750.00,750.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-15 03:45:48','2026-10-15 03:47:57','2026-10-15 03:48:01','2026-10-15 03:48:19','2026-10-15 03:48:27',NULL,NULL,1,'2026-10-15 03:48:27',1,2,'2026-10-14 21:45:48','2026-10-14 21:48:27',NULL),(7,'ORD-20261025-LLNHH1',1,'Tasnima Akther Tisha','+1 (844) 536-1838','shraboniakter554@gmail.com',4,7,'dine_in',NULL,NULL,NULL,'completed',250.00,0.00,0.00,0.00,250.00,250.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-25 04:08:16','2026-10-25 04:34:26','2026-10-25 04:34:33','2026-10-25 04:36:15','2026-10-25 05:05:18',NULL,NULL,1,'2026-10-25 05:05:18',1,2,'2026-10-24 22:08:16','2026-10-24 23:05:18',NULL),(8,'ORD-20261025-QKLF8J',NULL,NULL,NULL,NULL,NULL,NULL,'takeaway','T-00008','uncollected',NULL,'ready',250.00,0.00,0.00,0.00,250.00,250.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-25 09:23:52','2026-10-26 08:05:18','2026-10-26 08:05:34',NULL,NULL,NULL,NULL,0,NULL,1,2,'2026-10-25 03:23:52','2026-10-26 02:05:34',NULL),(9,'ORD-20261026-OW2JNX',NULL,NULL,NULL,NULL,NULL,NULL,'takeaway','T-00009','uncollected',NULL,'canceled',500.00,0.00,0.00,0.00,500.00,0.00,0.00,'due',NULL,NULL,NULL,NULL,NULL,'2026-10-26 08:22:42',NULL,NULL,NULL,NULL,'2026-10-26 08:23:49','Item unavailable',0,NULL,1,2,'2026-10-26 02:22:42','2026-10-26 02:23:49',NULL),(10,'ORD-20261026-SXGNQX',NULL,NULL,NULL,NULL,NULL,NULL,'takeaway','T-00010','uncollected',NULL,'ready',250.00,0.00,0.00,0.00,250.00,250.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-26 08:24:13','2026-10-26 08:24:29','2026-10-26 08:24:39',NULL,NULL,NULL,NULL,0,NULL,1,2,'2026-10-26 02:24:12','2026-10-26 02:24:39',NULL),(11,'DEMO100-ORD-001',7,'Demo Customer 001','8801901000001',NULL,NULL,NULL,'takeaway','T-00011','uncollected',NULL,'preparing',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:43:58','2026-10-26 10:43:58',NULL,NULL,NULL,NULL,NULL,0,NULL,4,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,'DEMO100-ORD-002',8,'Demo Customer 002','8801901000002',NULL,NULL,NULL,'takeaway','T-00012','uncollected',NULL,'preparing',230.00,0.00,0.00,0.00,230.00,230.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:42:58','2026-10-26 10:42:58',NULL,NULL,NULL,NULL,NULL,0,NULL,5,NULL,'2026-10-26 04:42:58','2026-10-26 04:43:58',NULL),(13,'DEMO100-ORD-003',9,'Demo Customer 003','8801901000003',NULL,NULL,NULL,'takeaway','T-00013','uncollected',NULL,'preparing',255.00,0.00,0.00,0.00,255.00,255.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:41:58','2026-10-26 10:41:58',NULL,NULL,NULL,NULL,NULL,0,NULL,6,NULL,'2026-10-26 04:41:58','2026-10-26 04:43:58',NULL),(14,'DEMO100-ORD-004',10,'Demo Customer 004','8801901000004',NULL,NULL,NULL,'takeaway','T-00014','uncollected',NULL,'preparing',280.00,0.00,0.00,0.00,280.00,280.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:40:58','2026-10-26 10:40:58',NULL,NULL,NULL,NULL,NULL,0,NULL,7,NULL,'2026-10-26 04:40:58','2026-10-26 04:43:58',NULL),(15,'DEMO100-ORD-005',11,'Demo Customer 005','8801901000005',NULL,NULL,NULL,'takeaway','T-00015','uncollected',NULL,'preparing',305.00,0.00,0.00,0.00,305.00,305.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:39:58','2026-10-26 10:39:58',NULL,NULL,NULL,NULL,NULL,0,NULL,8,NULL,'2026-10-26 04:39:58','2026-10-26 04:43:58',NULL),(16,'DEMO100-ORD-006',12,'Demo Customer 006','8801901000006',NULL,NULL,NULL,'takeaway','T-00016','uncollected',NULL,'preparing',330.00,0.00,0.00,0.00,330.00,330.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:38:58','2026-10-26 10:38:58',NULL,NULL,NULL,NULL,NULL,0,NULL,9,NULL,'2026-10-26 04:38:58','2026-10-26 04:43:58',NULL),(17,'DEMO100-ORD-007',13,'Demo Customer 007','8801901000007',NULL,NULL,NULL,'takeaway','T-00017','uncollected',NULL,'ready',355.00,0.00,0.00,0.00,355.00,355.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:37:58','2026-10-26 10:37:58','2026-10-26 10:49:58',NULL,NULL,NULL,NULL,0,NULL,10,NULL,'2026-10-26 04:37:58','2026-10-26 04:43:58',NULL),(18,'DEMO100-ORD-008',14,'Demo Customer 008','8801901000008',NULL,NULL,NULL,'takeaway','T-00018','uncollected',NULL,'ready',380.00,0.00,0.00,0.00,380.00,380.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:36:58','2026-10-26 10:36:58','2026-10-26 10:48:58',NULL,NULL,NULL,NULL,0,NULL,11,NULL,'2026-10-26 04:36:58','2026-10-26 04:43:58',NULL),(19,'DEMO100-ORD-009',15,'Demo Customer 009','8801901000009',NULL,NULL,NULL,'takeaway','T-00019','uncollected',NULL,'ready',405.00,0.00,0.00,0.00,405.00,405.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:35:58','2026-10-26 10:35:58','2026-10-26 10:47:58',NULL,NULL,NULL,NULL,0,NULL,12,NULL,'2026-10-26 04:35:58','2026-10-26 04:43:58',NULL),(20,'DEMO100-ORD-010',16,'Demo Customer 010','8801901000010',NULL,NULL,NULL,'takeaway','T-00020','uncollected',NULL,'ready',430.00,0.00,0.00,0.00,430.00,430.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:34:58','2026-10-26 10:34:58','2026-10-26 10:46:58',NULL,NULL,NULL,NULL,0,NULL,13,NULL,'2026-10-26 04:34:58','2026-10-26 04:43:58',NULL),(21,'DEMO100-ORD-011',17,'Demo Customer 011','8801901000011',NULL,NULL,NULL,'takeaway','T-00021','uncollected',NULL,'ready',455.00,0.00,0.00,0.00,455.00,455.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:33:58','2026-10-26 10:33:58','2026-10-26 10:45:58',NULL,NULL,NULL,NULL,0,NULL,14,NULL,'2026-10-26 04:33:58','2026-10-26 04:43:58',NULL),(22,'DEMO100-ORD-012',18,'Demo Customer 012','8801901000012',NULL,NULL,NULL,'takeaway','T-00022','uncollected',NULL,'ready',480.00,0.00,0.00,0.00,480.00,480.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:32:58','2026-10-26 10:32:58','2026-10-26 10:44:58',NULL,NULL,NULL,NULL,0,NULL,15,NULL,'2026-10-26 04:32:58','2026-10-26 04:43:58',NULL),(23,'DEMO100-ORD-013',19,'Demo Customer 013','8801901000013',NULL,NULL,NULL,'takeaway','T-00023','collected','2026-10-26 11:01:58','completed',505.00,0.00,0.00,0.00,505.00,505.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:31:58','2026-10-26 10:31:58','2026-10-26 10:43:58','2026-10-26 11:01:58','2026-10-26 11:01:58',NULL,NULL,1,'2026-10-26 11:01:58',16,NULL,'2026-10-26 04:31:58','2026-10-26 04:43:58',NULL),(24,'DEMO100-ORD-014',20,'Demo Customer 014','8801901000014',NULL,NULL,NULL,'takeaway','T-00024','collected','2026-10-26 11:00:58','completed',530.00,0.00,0.00,0.00,530.00,530.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:30:58','2026-10-26 10:30:58','2026-10-26 10:42:58','2026-10-26 11:00:58','2026-10-26 11:00:58',NULL,NULL,1,'2026-10-26 11:00:58',17,NULL,'2026-10-26 04:30:58','2026-10-26 04:43:58',NULL),(25,'DEMO100-ORD-015',21,'Demo Customer 015','8801901000015',NULL,NULL,NULL,'takeaway','T-00025','collected','2026-10-26 10:59:58','completed',180.00,0.00,0.00,0.00,180.00,180.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:29:58','2026-10-26 10:29:58','2026-10-26 10:41:58','2026-10-26 10:59:58','2026-10-26 10:59:58',NULL,NULL,1,'2026-10-26 10:59:58',18,NULL,'2026-10-26 04:29:58','2026-10-26 04:43:58',NULL),(26,'DEMO100-ORD-016',22,'Demo Customer 016','8801901000016',NULL,NULL,NULL,'takeaway','T-00026','collected','2026-10-26 10:58:58','completed',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:28:58','2026-10-26 10:28:58','2026-10-26 10:40:58','2026-10-26 10:58:58','2026-10-26 10:58:58',NULL,NULL,1,'2026-10-26 10:58:58',19,NULL,'2026-10-26 04:28:58','2026-10-26 04:43:58',NULL),(27,'DEMO100-ORD-017',23,'Demo Customer 017','8801901000017',NULL,NULL,NULL,'takeaway','T-00027','collected','2026-10-26 10:57:58','completed',230.00,0.00,0.00,0.00,230.00,230.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:27:58','2026-10-26 10:27:58','2026-10-26 10:39:58','2026-10-26 10:57:58','2026-10-26 10:57:58',NULL,NULL,1,'2026-10-26 10:57:58',20,NULL,'2026-10-26 04:27:58','2026-10-26 04:43:58',NULL),(28,'DEMO100-ORD-018',24,'Demo Customer 018','8801901000018',NULL,NULL,NULL,'takeaway','T-00028','collected','2026-10-26 10:56:58','completed',255.00,0.00,0.00,0.00,255.00,255.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:26:58','2026-10-26 10:26:58','2026-10-26 10:38:58','2026-10-26 10:56:58','2026-10-26 10:56:58',NULL,NULL,1,'2026-10-26 10:56:58',21,NULL,'2026-10-26 04:26:58','2026-10-26 04:43:58',NULL),(29,'DEMO100-ORD-019',25,'Demo Customer 019','8801901000019',NULL,NULL,NULL,'takeaway','T-00029','collected','2026-10-26 10:55:58','completed',280.00,0.00,0.00,0.00,280.00,280.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:25:58','2026-10-26 10:25:58','2026-10-26 10:37:58','2026-10-26 10:55:58','2026-10-26 10:55:58',NULL,NULL,1,'2026-10-26 10:55:58',22,NULL,'2026-10-26 04:25:58','2026-10-26 04:43:58',NULL),(30,'DEMO100-ORD-020',26,'Demo Customer 020','8801901000020',NULL,NULL,NULL,'takeaway','T-00030','collected','2026-10-26 10:54:58','completed',305.00,0.00,0.00,0.00,305.00,305.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:24:58','2026-10-26 10:24:58','2026-10-26 10:36:58','2026-10-26 10:54:58','2026-10-26 10:54:58',NULL,NULL,1,'2026-10-26 10:54:58',23,NULL,'2026-10-26 04:24:58','2026-10-26 04:43:58',NULL),(31,'DEMO100-ORD-021',27,'Demo Customer 021','8801901000021',NULL,NULL,NULL,'takeaway','T-00031','collected','2026-10-26 10:53:58','completed',330.00,0.00,0.00,0.00,330.00,330.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:23:58','2026-10-26 10:23:58','2026-10-26 10:35:58','2026-10-26 10:53:58','2026-10-26 10:53:58',NULL,NULL,1,'2026-10-26 10:53:58',24,NULL,'2026-10-26 04:23:58','2026-10-26 04:43:58',NULL),(32,'DEMO100-ORD-022',28,'Demo Customer 022','8801901000022',NULL,NULL,NULL,'takeaway','T-00032','collected','2026-10-26 10:52:58','completed',355.00,0.00,0.00,0.00,355.00,355.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:22:58','2026-10-26 10:22:58','2026-10-26 10:34:58','2026-10-26 10:52:58','2026-10-26 10:52:58',NULL,NULL,1,'2026-10-26 10:52:58',25,NULL,'2026-10-26 04:22:58','2026-10-26 04:43:58',NULL),(33,'DEMO100-ORD-023',29,'Demo Customer 023','8801901000023',NULL,NULL,NULL,'takeaway','T-00033','collected','2026-10-26 10:51:58','completed',380.00,0.00,0.00,0.00,380.00,380.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:21:58','2026-10-26 10:21:58','2026-10-26 10:33:58','2026-10-26 10:51:58','2026-10-26 10:51:58',NULL,NULL,1,'2026-10-26 10:51:58',26,NULL,'2026-10-26 04:21:58','2026-10-26 04:43:58',NULL),(34,'DEMO100-ORD-024',30,'Demo Customer 024','8801901000024',NULL,NULL,NULL,'takeaway','T-00034','collected','2026-10-26 10:50:58','completed',405.00,0.00,0.00,0.00,405.00,405.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:20:58','2026-10-26 10:20:58','2026-10-26 10:32:58','2026-10-26 10:50:58','2026-10-26 10:50:58',NULL,NULL,1,'2026-10-26 10:50:58',27,NULL,'2026-10-26 04:20:58','2026-10-26 04:43:58',NULL),(35,'DEMO100-ORD-025',31,'Demo Customer 025','8801901000025',NULL,NULL,NULL,'takeaway','T-00035','collected','2026-10-26 10:49:58','completed',430.00,0.00,0.00,0.00,430.00,430.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:19:58','2026-10-26 10:19:58','2026-10-26 10:31:58','2026-10-26 10:49:58','2026-10-26 10:49:58',NULL,NULL,1,'2026-10-26 10:49:58',28,NULL,'2026-10-26 04:19:58','2026-10-26 04:43:58',NULL),(36,'DEMO100-ORD-026',32,'Demo Customer 026','8801901000026',NULL,NULL,NULL,'takeaway','T-00036','collected','2026-10-26 10:48:58','completed',455.00,0.00,0.00,0.00,455.00,455.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:18:58','2026-10-26 10:18:58','2026-10-26 10:30:58','2026-10-26 10:48:58','2026-10-26 10:48:58',NULL,NULL,1,'2026-10-26 10:48:58',29,NULL,'2026-10-26 04:18:58','2026-10-26 04:43:58',NULL),(37,'DEMO100-ORD-027',33,'Demo Customer 027','8801901000027',NULL,NULL,NULL,'takeaway','T-00037','collected','2026-10-26 10:47:58','completed',480.00,0.00,0.00,0.00,480.00,480.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:17:58','2026-10-26 10:17:58','2026-10-26 10:29:58','2026-10-26 10:47:58','2026-10-26 10:47:58',NULL,NULL,1,'2026-10-26 10:47:58',30,NULL,'2026-10-26 04:17:58','2026-10-26 04:43:58',NULL),(38,'DEMO100-ORD-028',34,'Demo Customer 028','8801901000028',NULL,NULL,NULL,'takeaway','T-00038','collected','2026-10-26 10:46:58','completed',505.00,0.00,0.00,0.00,505.00,505.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:16:58','2026-10-26 10:16:58','2026-10-26 10:28:58','2026-10-26 10:46:58','2026-10-26 10:46:58',NULL,NULL,1,'2026-10-26 10:46:58',31,NULL,'2026-10-26 04:16:58','2026-10-26 04:43:58',NULL),(39,'DEMO100-ORD-029',35,'Demo Customer 029','8801901000029',NULL,NULL,NULL,'takeaway','T-00039','collected','2026-10-26 10:45:58','completed',530.00,0.00,0.00,0.00,530.00,530.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:15:58','2026-10-26 10:15:58','2026-10-26 10:27:58','2026-10-26 10:45:58','2026-10-26 10:45:58',NULL,NULL,1,'2026-10-26 10:45:58',32,NULL,'2026-10-26 04:15:58','2026-10-26 04:43:58',NULL),(40,'DEMO100-ORD-030',36,'Demo Customer 030','8801901000030',NULL,NULL,NULL,'takeaway','T-00040','collected','2026-10-26 10:44:58','completed',180.00,0.00,0.00,0.00,180.00,180.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:14:58','2026-10-26 10:14:58','2026-10-26 10:26:58','2026-10-26 10:44:58','2026-10-26 10:44:58',NULL,NULL,1,'2026-10-26 10:44:58',33,NULL,'2026-10-26 04:14:58','2026-10-26 04:43:58',NULL),(41,'DEMO100-ORD-031',37,'Demo Customer 031','8801901000031',NULL,NULL,NULL,'takeaway','T-00041','collected','2026-10-26 10:43:58','completed',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:13:58','2026-10-26 10:13:58','2026-10-26 10:25:58','2026-10-26 10:43:58','2026-10-26 10:43:58',NULL,NULL,1,'2026-10-26 10:43:58',34,NULL,'2026-10-26 04:13:58','2026-10-26 04:43:58',NULL),(42,'DEMO100-ORD-032',38,'Demo Customer 032','8801901000032',NULL,NULL,NULL,'takeaway','T-00042','collected','2026-10-26 10:42:58','completed',230.00,0.00,0.00,0.00,230.00,230.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:12:58','2026-10-26 10:12:58','2026-10-26 10:24:58','2026-10-26 10:42:58','2026-10-26 10:42:58',NULL,NULL,1,'2026-10-26 10:42:58',35,NULL,'2026-10-26 04:12:58','2026-10-26 04:43:58',NULL),(43,'DEMO100-ORD-033',39,'Demo Customer 033','8801901000033',NULL,NULL,NULL,'takeaway','T-00043','collected','2026-10-26 10:41:58','completed',255.00,0.00,0.00,0.00,255.00,255.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:11:58','2026-10-26 10:11:58','2026-10-26 10:23:58','2026-10-26 10:41:58','2026-10-26 10:41:58',NULL,NULL,1,'2026-10-26 10:41:58',36,NULL,'2026-10-26 04:11:58','2026-10-26 04:43:58',NULL),(44,'DEMO100-ORD-034',40,'Demo Customer 034','8801901000034',NULL,NULL,NULL,'takeaway','T-00044','collected','2026-10-26 10:40:58','completed',280.00,0.00,0.00,0.00,280.00,280.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:10:58','2026-10-26 10:10:58','2026-10-26 10:22:58','2026-10-26 10:40:58','2026-10-26 10:40:58',NULL,NULL,1,'2026-10-26 10:40:58',37,NULL,'2026-10-26 04:10:58','2026-10-26 04:43:58',NULL),(45,'DEMO100-ORD-035',41,'Demo Customer 035','8801901000035',NULL,NULL,NULL,'takeaway','T-00045','collected','2026-10-26 10:39:58','completed',305.00,0.00,0.00,0.00,305.00,305.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:09:58','2026-10-26 10:09:58','2026-10-26 10:21:58','2026-10-26 10:39:58','2026-10-26 10:39:58',NULL,NULL,1,'2026-10-26 10:39:58',38,NULL,'2026-10-26 04:09:58','2026-10-26 04:43:58',NULL),(46,'DEMO100-ORD-036',42,'Demo Customer 036','8801901000036',NULL,NULL,NULL,'takeaway','T-00046','collected','2026-10-26 11:13:58','completed',330.00,0.00,0.00,0.00,330.00,330.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:43:58','2026-10-26 10:43:58','2026-10-26 10:55:58','2026-10-26 11:13:58','2026-10-26 11:13:58',NULL,NULL,1,'2026-10-26 11:13:58',39,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,'DEMO100-ORD-037',43,'Demo Customer 037','8801901000037',NULL,NULL,NULL,'takeaway','T-00047','collected','2026-10-26 11:12:58','completed',355.00,0.00,0.00,0.00,355.00,355.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:42:58','2026-10-26 10:42:58','2026-10-26 10:54:58','2026-10-26 11:12:58','2026-10-26 11:12:58',NULL,NULL,1,'2026-10-26 11:12:58',40,NULL,'2026-10-26 04:42:58','2026-10-26 04:43:58',NULL),(48,'DEMO100-ORD-038',44,'Demo Customer 038','8801901000038',NULL,NULL,NULL,'takeaway','T-00048','collected','2026-10-26 11:11:58','completed',380.00,0.00,0.00,0.00,380.00,380.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:41:58','2026-10-26 10:41:58','2026-10-26 10:53:58','2026-10-26 11:11:58','2026-10-26 11:11:58',NULL,NULL,1,'2026-10-26 11:11:58',41,NULL,'2026-10-26 04:41:58','2026-10-26 04:43:58',NULL),(49,'DEMO100-ORD-039',45,'Demo Customer 039','8801901000039',NULL,NULL,NULL,'takeaway','T-00049','collected','2026-10-26 11:10:58','completed',405.00,0.00,0.00,0.00,405.00,405.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:40:58','2026-10-26 10:40:58','2026-10-26 10:52:58','2026-10-26 11:10:58','2026-10-26 11:10:58',NULL,NULL,1,'2026-10-26 11:10:58',42,NULL,'2026-10-26 04:40:58','2026-10-26 04:43:58',NULL),(50,'DEMO100-ORD-040',46,'Demo Customer 040','8801901000040',NULL,NULL,NULL,'takeaway','T-00050','collected','2026-10-26 11:09:58','completed',430.00,0.00,0.00,0.00,430.00,430.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:39:58','2026-10-26 10:39:58','2026-10-26 10:51:58','2026-10-26 11:09:58','2026-10-26 11:09:58',NULL,NULL,1,'2026-10-26 11:09:58',43,NULL,'2026-10-26 04:39:58','2026-10-26 04:43:58',NULL),(51,'DEMO100-ORD-041',47,'Demo Customer 041','8801901000041',NULL,NULL,NULL,'takeaway','T-00051','collected','2026-10-26 11:08:58','completed',455.00,0.00,0.00,0.00,455.00,455.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:38:58','2026-10-26 10:38:58','2026-10-26 10:50:58','2026-10-26 11:08:58','2026-10-26 11:08:58',NULL,NULL,1,'2026-10-26 11:08:58',44,NULL,'2026-10-26 04:38:58','2026-10-26 04:43:58',NULL),(52,'DEMO100-ORD-042',48,'Demo Customer 042','8801901000042',NULL,NULL,NULL,'takeaway','T-00052','collected','2026-10-26 11:07:58','completed',480.00,0.00,0.00,0.00,480.00,480.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:37:58','2026-10-26 10:37:58','2026-10-26 10:49:58','2026-10-26 11:07:58','2026-10-26 11:07:58',NULL,NULL,1,'2026-10-26 11:07:58',45,NULL,'2026-10-26 04:37:58','2026-10-26 04:43:58',NULL),(53,'DEMO100-ORD-043',49,'Demo Customer 043','8801901000043',NULL,NULL,NULL,'takeaway','T-00053','collected','2026-10-26 11:06:58','completed',505.00,0.00,0.00,0.00,505.00,505.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:36:58','2026-10-26 10:36:58','2026-10-26 10:48:58','2026-10-26 11:06:58','2026-10-26 11:06:58',NULL,NULL,1,'2026-10-26 11:06:58',46,NULL,'2026-10-26 04:36:58','2026-10-26 04:43:58',NULL),(54,'DEMO100-ORD-044',50,'Demo Customer 044','8801901000044',NULL,NULL,NULL,'takeaway','T-00054','collected','2026-10-26 11:05:58','completed',530.00,0.00,0.00,0.00,530.00,530.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:35:58','2026-10-26 10:35:58','2026-10-26 10:47:58','2026-10-26 11:05:58','2026-10-26 11:05:58',NULL,NULL,1,'2026-10-26 11:05:58',47,NULL,'2026-10-26 04:35:58','2026-10-26 04:43:58',NULL),(55,'DEMO100-ORD-045',51,'Demo Customer 045','8801901000045',NULL,NULL,NULL,'takeaway','T-00055','collected','2026-10-26 11:04:58','completed',180.00,0.00,0.00,0.00,180.00,180.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:34:58','2026-10-26 10:34:58','2026-10-26 10:46:58','2026-10-26 11:04:58','2026-10-26 11:04:58',NULL,NULL,1,'2026-10-26 11:04:58',48,NULL,'2026-10-26 04:34:58','2026-10-26 04:43:58',NULL),(56,'DEMO100-ORD-046',52,'Demo Customer 046','8801901000046',NULL,NULL,NULL,'takeaway','T-00056','collected','2026-10-26 11:03:58','completed',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:33:58','2026-10-26 10:33:58','2026-10-26 10:45:58','2026-10-26 11:03:58','2026-10-26 11:03:58',NULL,NULL,1,'2026-10-26 11:03:58',49,NULL,'2026-10-26 04:33:58','2026-10-26 04:43:58',NULL),(57,'DEMO100-ORD-047',53,'Demo Customer 047','8801901000047',NULL,NULL,NULL,'takeaway','T-00057','collected','2026-10-26 11:02:58','completed',230.00,0.00,0.00,0.00,230.00,230.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:32:58','2026-10-26 10:32:58','2026-10-26 10:44:58','2026-10-26 11:02:58','2026-10-26 11:02:58',NULL,NULL,1,'2026-10-26 11:02:58',50,NULL,'2026-10-26 04:32:58','2026-10-26 04:43:58',NULL),(58,'DEMO100-ORD-048',54,'Demo Customer 048','8801901000048',NULL,NULL,NULL,'takeaway','T-00058','collected','2026-10-26 11:01:58','completed',255.00,0.00,0.00,0.00,255.00,255.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:31:58','2026-10-26 10:31:58','2026-10-26 10:43:58','2026-10-26 11:01:58','2026-10-26 11:01:58',NULL,NULL,1,'2026-10-26 11:01:58',51,NULL,'2026-10-26 04:31:58','2026-10-26 04:43:58',NULL),(59,'DEMO100-ORD-049',55,'Demo Customer 049','8801901000049',NULL,NULL,NULL,'takeaway','T-00059','collected','2026-10-26 11:00:58','completed',280.00,0.00,0.00,0.00,280.00,280.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:30:58','2026-10-26 10:30:58','2026-10-26 10:42:58','2026-10-26 11:00:58','2026-10-26 11:00:58',NULL,NULL,1,'2026-10-26 11:00:58',52,NULL,'2026-10-26 04:30:58','2026-10-26 04:43:58',NULL),(60,'DEMO100-ORD-050',56,'Demo Customer 050','8801901000050',NULL,NULL,NULL,'takeaway','T-00060','collected','2026-10-26 10:59:58','completed',305.00,0.00,0.00,0.00,305.00,305.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:29:58','2026-10-26 10:29:58','2026-10-26 10:41:58','2026-10-26 10:59:58','2026-10-26 10:59:58',NULL,NULL,1,'2026-10-26 10:59:58',53,NULL,'2026-10-26 04:29:58','2026-10-26 04:43:58',NULL),(61,'DEMO100-ORD-051',57,'Demo Customer 051','8801901000051',NULL,NULL,NULL,'takeaway','T-00061','collected','2026-10-26 10:58:58','completed',330.00,0.00,0.00,0.00,330.00,330.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:28:58','2026-10-26 10:28:58','2026-10-26 10:40:58','2026-10-26 10:58:58','2026-10-26 10:58:58',NULL,NULL,1,'2026-10-26 10:58:58',54,NULL,'2026-10-26 04:28:58','2026-10-26 04:43:58',NULL),(62,'DEMO100-ORD-052',58,'Demo Customer 052','8801901000052',NULL,NULL,NULL,'takeaway','T-00062','collected','2026-10-26 10:57:58','completed',355.00,0.00,0.00,0.00,355.00,355.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:27:58','2026-10-26 10:27:58','2026-10-26 10:39:58','2026-10-26 10:57:58','2026-10-26 10:57:58',NULL,NULL,1,'2026-10-26 10:57:58',55,NULL,'2026-10-26 04:27:58','2026-10-26 04:43:58',NULL),(63,'DEMO100-ORD-053',59,'Demo Customer 053','8801901000053',NULL,NULL,NULL,'takeaway','T-00063','collected','2026-10-26 10:56:58','completed',380.00,0.00,0.00,0.00,380.00,380.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:26:58','2026-10-26 10:26:58','2026-10-26 10:38:58','2026-10-26 10:56:58','2026-10-26 10:56:58',NULL,NULL,1,'2026-10-26 10:56:58',56,NULL,'2026-10-26 04:26:58','2026-10-26 04:43:58',NULL),(64,'DEMO100-ORD-054',60,'Demo Customer 054','8801901000054',NULL,NULL,NULL,'takeaway','T-00064','collected','2026-10-26 10:55:58','completed',405.00,0.00,0.00,0.00,405.00,405.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:25:58','2026-10-26 10:25:58','2026-10-26 10:37:58','2026-10-26 10:55:58','2026-10-26 10:55:58',NULL,NULL,1,'2026-10-26 10:55:58',57,NULL,'2026-10-26 04:25:58','2026-10-26 04:43:58',NULL),(65,'DEMO100-ORD-055',61,'Demo Customer 055','8801901000055',NULL,NULL,NULL,'takeaway','T-00065','collected','2026-10-26 10:54:58','completed',430.00,0.00,0.00,0.00,430.00,430.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:24:58','2026-10-26 10:24:58','2026-10-26 10:36:58','2026-10-26 10:54:58','2026-10-26 10:54:58',NULL,NULL,1,'2026-10-26 10:54:58',58,NULL,'2026-10-26 04:24:58','2026-10-26 04:43:58',NULL),(66,'DEMO100-ORD-056',62,'Demo Customer 056','8801901000056',NULL,NULL,NULL,'takeaway','T-00066','collected','2026-10-26 10:53:58','completed',455.00,0.00,0.00,0.00,455.00,455.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:23:58','2026-10-26 10:23:58','2026-10-26 10:35:58','2026-10-26 10:53:58','2026-10-26 10:53:58',NULL,NULL,1,'2026-10-26 10:53:58',59,NULL,'2026-10-26 04:23:58','2026-10-26 04:43:58',NULL),(67,'DEMO100-ORD-057',63,'Demo Customer 057','8801901000057',NULL,NULL,NULL,'takeaway','T-00067','collected','2026-10-26 10:52:58','completed',480.00,0.00,0.00,0.00,480.00,480.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:22:58','2026-10-26 10:22:58','2026-10-26 10:34:58','2026-10-26 10:52:58','2026-10-26 10:52:58',NULL,NULL,1,'2026-10-26 10:52:58',60,NULL,'2026-10-26 04:22:58','2026-10-26 04:43:58',NULL),(68,'DEMO100-ORD-058',64,'Demo Customer 058','8801901000058',NULL,NULL,NULL,'takeaway','T-00068','collected','2026-10-26 10:51:58','completed',505.00,0.00,0.00,0.00,505.00,505.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:21:58','2026-10-26 10:21:58','2026-10-26 10:33:58','2026-10-26 10:51:58','2026-10-26 10:51:58',NULL,NULL,1,'2026-10-26 10:51:58',61,NULL,'2026-10-26 04:21:58','2026-10-26 04:43:58',NULL),(69,'DEMO100-ORD-059',65,'Demo Customer 059','8801901000059',NULL,NULL,NULL,'takeaway','T-00069','collected','2026-10-26 10:50:58','completed',530.00,0.00,0.00,0.00,530.00,530.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:20:58','2026-10-26 10:20:58','2026-10-26 10:32:58','2026-10-26 10:50:58','2026-10-26 10:50:58',NULL,NULL,1,'2026-10-26 10:50:58',62,NULL,'2026-10-26 04:20:58','2026-10-26 04:43:58',NULL),(70,'DEMO100-ORD-060',66,'Demo Customer 060','8801901000060',NULL,NULL,NULL,'takeaway','T-00070','collected','2026-10-26 10:49:58','completed',180.00,0.00,0.00,0.00,180.00,180.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:19:58','2026-10-26 10:19:58','2026-10-26 10:31:58','2026-10-26 10:49:58','2026-10-26 10:49:58',NULL,NULL,1,'2026-10-26 10:49:58',63,NULL,'2026-10-26 04:19:58','2026-10-26 04:43:58',NULL),(71,'DEMO100-ORD-061',67,'Demo Customer 061','8801901000061',NULL,NULL,NULL,'takeaway','T-00071','collected','2026-10-26 10:48:58','completed',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:18:58','2026-10-26 10:18:58','2026-10-26 10:30:58','2026-10-26 10:48:58','2026-10-26 10:48:58',NULL,NULL,1,'2026-10-26 10:48:58',64,NULL,'2026-10-26 04:18:58','2026-10-26 04:43:58',NULL),(72,'DEMO100-ORD-062',68,'Demo Customer 062','8801901000062',NULL,NULL,NULL,'takeaway','T-00072','collected','2026-10-26 10:47:58','completed',230.00,0.00,0.00,0.00,230.00,230.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:17:58','2026-10-26 10:17:58','2026-10-26 10:29:58','2026-10-26 10:47:58','2026-10-26 10:47:58',NULL,NULL,1,'2026-10-26 10:47:58',65,NULL,'2026-10-26 04:17:58','2026-10-26 04:43:58',NULL),(73,'DEMO100-ORD-063',69,'Demo Customer 063','8801901000063',NULL,NULL,NULL,'takeaway','T-00073','collected','2026-10-26 10:46:58','completed',255.00,0.00,0.00,0.00,255.00,255.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:16:58','2026-10-26 10:16:58','2026-10-26 10:28:58','2026-10-26 10:46:58','2026-10-26 10:46:58',NULL,NULL,1,'2026-10-26 10:46:58',66,NULL,'2026-10-26 04:16:58','2026-10-26 04:43:58',NULL),(74,'DEMO100-ORD-064',70,'Demo Customer 064','8801901000064',NULL,NULL,NULL,'takeaway','T-00074','collected','2026-10-26 10:45:58','completed',280.00,0.00,0.00,0.00,280.00,280.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:15:58','2026-10-26 10:15:58','2026-10-26 10:27:58','2026-10-26 10:45:58','2026-10-26 10:45:58',NULL,NULL,1,'2026-10-26 10:45:58',67,NULL,'2026-10-26 04:15:58','2026-10-26 04:43:58',NULL),(75,'DEMO100-ORD-065',71,'Demo Customer 065','8801901000065',NULL,NULL,NULL,'takeaway','T-00075','collected','2026-10-26 10:44:58','completed',305.00,0.00,0.00,0.00,305.00,305.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:14:58','2026-10-26 10:14:58','2026-10-26 10:26:58','2026-10-26 10:44:58','2026-10-26 10:44:58',NULL,NULL,1,'2026-10-26 10:44:58',68,NULL,'2026-10-26 04:14:58','2026-10-26 04:43:58',NULL),(76,'DEMO100-ORD-066',72,'Demo Customer 066','8801901000066',NULL,NULL,NULL,'takeaway','T-00076','collected','2026-10-26 10:43:58','completed',330.00,0.00,0.00,0.00,330.00,330.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:13:58','2026-10-26 10:13:58','2026-10-26 10:25:58','2026-10-26 10:43:58','2026-10-26 10:43:58',NULL,NULL,1,'2026-10-26 10:43:58',69,NULL,'2026-10-26 04:13:58','2026-10-26 04:43:58',NULL),(77,'DEMO100-ORD-067',73,'Demo Customer 067','8801901000067',NULL,NULL,NULL,'takeaway','T-00077','collected','2026-10-26 10:42:58','completed',355.00,0.00,0.00,0.00,355.00,355.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:12:58','2026-10-26 10:12:58','2026-10-26 10:24:58','2026-10-26 10:42:58','2026-10-26 10:42:58',NULL,NULL,1,'2026-10-26 10:42:58',70,NULL,'2026-10-26 04:12:58','2026-10-26 04:43:58',NULL),(78,'DEMO100-ORD-068',74,'Demo Customer 068','8801901000068',NULL,NULL,NULL,'takeaway','T-00078','collected','2026-10-26 10:41:58','completed',380.00,0.00,0.00,0.00,380.00,380.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:11:58','2026-10-26 10:11:58','2026-10-26 10:23:58','2026-10-26 10:41:58','2026-10-26 10:41:58',NULL,NULL,1,'2026-10-26 10:41:58',71,NULL,'2026-10-26 04:11:58','2026-10-26 04:43:58',NULL),(79,'DEMO100-ORD-069',75,'Demo Customer 069','8801901000069',NULL,NULL,NULL,'takeaway','T-00079','collected','2026-10-26 10:40:58','completed',405.00,0.00,0.00,0.00,405.00,405.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:10:58','2026-10-26 10:10:58','2026-10-26 10:22:58','2026-10-26 10:40:58','2026-10-26 10:40:58',NULL,NULL,1,'2026-10-26 10:40:58',72,NULL,'2026-10-26 04:10:58','2026-10-26 04:43:58',NULL),(80,'DEMO100-ORD-070',76,'Demo Customer 070','8801901000070',NULL,NULL,NULL,'takeaway','T-00080','collected','2026-10-26 10:39:58','completed',430.00,0.00,0.00,0.00,430.00,430.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:09:58','2026-10-26 10:09:58','2026-10-26 10:21:58','2026-10-26 10:39:58','2026-10-26 10:39:58',NULL,NULL,1,'2026-10-26 10:39:58',73,NULL,'2026-10-26 04:09:58','2026-10-26 04:43:58',NULL),(81,'DEMO100-ORD-071',77,'Demo Customer 071','8801901000071',NULL,NULL,NULL,'takeaway','T-00081','collected','2026-10-26 11:13:58','completed',455.00,0.00,0.00,0.00,455.00,455.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:43:58','2026-10-26 10:43:58','2026-10-26 10:55:58','2026-10-26 11:13:58','2026-10-26 11:13:58',NULL,NULL,1,'2026-10-26 11:13:58',74,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,'DEMO100-ORD-072',78,'Demo Customer 072','8801901000072',NULL,NULL,NULL,'takeaway','T-00082','collected','2026-10-26 11:12:58','completed',480.00,0.00,0.00,0.00,480.00,480.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:42:58','2026-10-26 10:42:58','2026-10-26 10:54:58','2026-10-26 11:12:58','2026-10-26 11:12:58',NULL,NULL,1,'2026-10-26 11:12:58',75,NULL,'2026-10-26 04:42:58','2026-10-26 04:43:58',NULL),(83,'DEMO100-ORD-073',79,'Demo Customer 073','8801901000073',NULL,NULL,NULL,'takeaway','T-00083','collected','2026-10-26 11:11:58','completed',505.00,0.00,0.00,0.00,505.00,505.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:41:58','2026-10-26 10:41:58','2026-10-26 10:53:58','2026-10-26 11:11:58','2026-10-26 11:11:58',NULL,NULL,1,'2026-10-26 11:11:58',76,NULL,'2026-10-26 04:41:58','2026-10-26 04:43:58',NULL),(84,'DEMO100-ORD-074',80,'Demo Customer 074','8801901000074',NULL,NULL,NULL,'takeaway','T-00084','collected','2026-10-26 11:10:58','completed',530.00,0.00,0.00,0.00,530.00,530.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:40:58','2026-10-26 10:40:58','2026-10-26 10:52:58','2026-10-26 11:10:58','2026-10-26 11:10:58',NULL,NULL,1,'2026-10-26 11:10:58',77,NULL,'2026-10-26 04:40:58','2026-10-26 04:43:58',NULL),(85,'DEMO100-ORD-075',81,'Demo Customer 075','8801901000075',NULL,NULL,NULL,'takeaway','T-00085','collected','2026-10-26 11:09:58','completed',180.00,0.00,0.00,0.00,180.00,180.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:39:58','2026-10-26 10:39:58','2026-10-26 10:51:58','2026-10-26 11:09:58','2026-10-26 11:09:58',NULL,NULL,1,'2026-10-26 11:09:58',78,NULL,'2026-10-26 04:39:58','2026-10-26 04:43:58',NULL),(86,'DEMO100-ORD-076',82,'Demo Customer 076','8801901000076',NULL,NULL,NULL,'takeaway','T-00086','collected','2026-10-26 11:08:58','completed',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:38:58','2026-10-26 10:38:58','2026-10-26 10:50:58','2026-10-26 11:08:58','2026-10-26 11:08:58',NULL,NULL,1,'2026-10-26 11:08:58',79,NULL,'2026-10-26 04:38:58','2026-10-26 04:43:58',NULL),(87,'DEMO100-ORD-077',83,'Demo Customer 077','8801901000077',NULL,NULL,NULL,'takeaway','T-00087','collected','2026-10-26 11:07:58','completed',230.00,0.00,0.00,0.00,230.00,230.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:37:58','2026-10-26 10:37:58','2026-10-26 10:49:58','2026-10-26 11:07:58','2026-10-26 11:07:58',NULL,NULL,1,'2026-10-26 11:07:58',80,NULL,'2026-10-26 04:37:58','2026-10-26 04:43:58',NULL),(88,'DEMO100-ORD-078',84,'Demo Customer 078','8801901000078',NULL,NULL,NULL,'takeaway','T-00088','collected','2026-10-26 11:06:58','completed',255.00,0.00,0.00,0.00,255.00,255.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:36:58','2026-10-26 10:36:58','2026-10-26 10:48:58','2026-10-26 11:06:58','2026-10-26 11:06:58',NULL,NULL,1,'2026-10-26 11:06:58',81,NULL,'2026-10-26 04:36:58','2026-10-26 04:43:58',NULL),(89,'DEMO100-ORD-079',85,'Demo Customer 079','8801901000079',NULL,NULL,NULL,'takeaway','T-00089','collected','2026-10-26 11:05:58','completed',280.00,0.00,0.00,0.00,280.00,280.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:35:58','2026-10-26 10:35:58','2026-10-26 10:47:58','2026-10-26 11:05:58','2026-10-26 11:05:58',NULL,NULL,1,'2026-10-26 11:05:58',82,NULL,'2026-10-26 04:35:58','2026-10-26 04:43:58',NULL),(90,'DEMO100-ORD-080',86,'Demo Customer 080','8801901000080',NULL,NULL,NULL,'takeaway','T-00090','collected','2026-10-26 11:04:58','completed',305.00,0.00,0.00,0.00,305.00,305.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:34:58','2026-10-26 10:34:58','2026-10-26 10:46:58','2026-10-26 11:04:58','2026-10-26 11:04:58',NULL,NULL,1,'2026-10-26 11:04:58',83,NULL,'2026-10-26 04:34:58','2026-10-26 04:43:58',NULL),(91,'DEMO100-ORD-081',87,'Demo Customer 081','8801901000081',NULL,NULL,NULL,'takeaway','T-00091','collected','2026-10-26 11:03:58','completed',330.00,0.00,0.00,0.00,330.00,330.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:33:58','2026-10-26 10:33:58','2026-10-26 10:45:58','2026-10-26 11:03:58','2026-10-26 11:03:58',NULL,NULL,1,'2026-10-26 11:03:58',84,NULL,'2026-10-26 04:33:58','2026-10-26 04:43:58',NULL),(92,'DEMO100-ORD-082',88,'Demo Customer 082','8801901000082',NULL,NULL,NULL,'takeaway','T-00092','collected','2026-10-26 11:02:58','completed',355.00,0.00,0.00,0.00,355.00,355.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:32:58','2026-10-26 10:32:58','2026-10-26 10:44:58','2026-10-26 11:02:58','2026-10-26 11:02:58',NULL,NULL,1,'2026-10-26 11:02:58',85,NULL,'2026-10-26 04:32:58','2026-10-26 04:43:58',NULL),(93,'DEMO100-ORD-083',89,'Demo Customer 083','8801901000083',NULL,NULL,NULL,'takeaway','T-00093','collected','2026-10-26 11:01:58','completed',380.00,0.00,0.00,0.00,380.00,380.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:31:58','2026-10-26 10:31:58','2026-10-26 10:43:58','2026-10-26 11:01:58','2026-10-26 11:01:58',NULL,NULL,1,'2026-10-26 11:01:58',86,NULL,'2026-10-26 04:31:58','2026-10-26 04:43:58',NULL),(94,'DEMO100-ORD-084',90,'Demo Customer 084','8801901000084',NULL,NULL,NULL,'takeaway','T-00094','collected','2026-10-26 11:00:58','completed',405.00,0.00,0.00,0.00,405.00,405.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:30:58','2026-10-26 10:30:58','2026-10-26 10:42:58','2026-10-26 11:00:58','2026-10-26 11:00:58',NULL,NULL,1,'2026-10-26 11:00:58',87,NULL,'2026-10-26 04:30:58','2026-10-26 04:43:58',NULL),(95,'DEMO100-ORD-085',91,'Demo Customer 085','8801901000085',NULL,NULL,NULL,'takeaway','T-00095','collected','2026-10-26 10:59:58','completed',430.00,0.00,0.00,0.00,430.00,430.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:29:58','2026-10-26 10:29:58','2026-10-26 10:41:58','2026-10-26 10:59:58','2026-10-26 10:59:58',NULL,NULL,1,'2026-10-26 10:59:58',88,NULL,'2026-10-26 04:29:58','2026-10-26 04:43:58',NULL),(96,'DEMO100-ORD-086',92,'Demo Customer 086','8801901000086',NULL,NULL,NULL,'takeaway','T-00096','collected','2026-10-26 10:58:58','completed',455.00,0.00,0.00,0.00,455.00,455.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:28:58','2026-10-26 10:28:58','2026-10-26 10:40:58','2026-10-26 10:58:58','2026-10-26 10:58:58',NULL,NULL,1,'2026-10-26 10:58:58',89,NULL,'2026-10-26 04:28:58','2026-10-26 04:43:58',NULL),(97,'DEMO100-ORD-087',93,'Demo Customer 087','8801901000087',NULL,NULL,NULL,'takeaway','T-00097','collected','2026-10-26 10:57:58','completed',480.00,0.00,0.00,0.00,480.00,480.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:27:58','2026-10-26 10:27:58','2026-10-26 10:39:58','2026-10-26 10:57:58','2026-10-26 10:57:58',NULL,NULL,1,'2026-10-26 10:57:58',90,NULL,'2026-10-26 04:27:58','2026-10-26 04:43:58',NULL),(98,'DEMO100-ORD-088',94,'Demo Customer 088','8801901000088',NULL,NULL,NULL,'takeaway','T-00098','collected','2026-10-26 10:56:58','completed',505.00,0.00,0.00,0.00,505.00,505.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:26:58','2026-10-26 10:26:58','2026-10-26 10:38:58','2026-10-26 10:56:58','2026-10-26 10:56:58',NULL,NULL,1,'2026-10-26 10:56:58',91,NULL,'2026-10-26 04:26:58','2026-10-26 04:43:58',NULL),(99,'DEMO100-ORD-089',95,'Demo Customer 089','8801901000089',NULL,NULL,NULL,'takeaway','T-00099','collected','2026-10-26 10:55:58','completed',530.00,0.00,0.00,0.00,530.00,530.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:25:58','2026-10-26 10:25:58','2026-10-26 10:37:58','2026-10-26 10:55:58','2026-10-26 10:55:58',NULL,NULL,1,'2026-10-26 10:55:58',92,NULL,'2026-10-26 04:25:58','2026-10-26 04:43:58',NULL),(100,'DEMO100-ORD-090',96,'Demo Customer 090','8801901000090',NULL,NULL,NULL,'takeaway','T-00100','collected','2026-10-26 10:54:58','completed',180.00,0.00,0.00,0.00,180.00,180.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:24:58','2026-10-26 10:24:58','2026-10-26 10:36:58','2026-10-26 10:54:58','2026-10-26 10:54:58',NULL,NULL,1,'2026-10-26 10:54:58',93,NULL,'2026-10-26 04:24:58','2026-10-26 04:43:58',NULL),(101,'DEMO100-ORD-091',97,'Demo Customer 091','8801901000091',NULL,NULL,NULL,'takeaway','T-00101','collected','2026-10-26 10:53:58','completed',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:23:58','2026-10-26 10:23:58','2026-10-26 10:35:58','2026-10-26 10:53:58','2026-10-26 10:53:58',NULL,NULL,1,'2026-10-26 10:53:58',94,NULL,'2026-10-26 04:23:58','2026-10-26 04:43:58',NULL),(102,'DEMO100-ORD-092',98,'Demo Customer 092','8801901000092',NULL,NULL,NULL,'takeaway','T-00102','collected','2026-10-26 10:52:58','completed',230.00,0.00,0.00,0.00,230.00,230.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:22:58','2026-10-26 10:22:58','2026-10-26 10:34:58','2026-10-26 10:52:58','2026-10-26 10:52:58',NULL,NULL,1,'2026-10-26 10:52:58',95,NULL,'2026-10-26 04:22:58','2026-10-26 04:43:58',NULL),(103,'DEMO100-ORD-093',99,'Demo Customer 093','8801901000093',NULL,NULL,NULL,'takeaway','T-00103','collected','2026-10-26 10:51:58','completed',255.00,0.00,0.00,0.00,255.00,255.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:21:58','2026-10-26 10:21:58','2026-10-26 10:33:58','2026-10-26 10:51:58','2026-10-26 10:51:58',NULL,NULL,1,'2026-10-26 10:51:58',96,NULL,'2026-10-26 04:21:58','2026-10-26 04:43:58',NULL),(104,'DEMO100-ORD-094',100,'Demo Customer 094','8801901000094',NULL,NULL,NULL,'takeaway','T-00104','collected','2026-10-26 10:50:58','completed',280.00,0.00,0.00,0.00,280.00,280.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:20:58','2026-10-26 10:20:58','2026-10-26 10:32:58','2026-10-26 10:50:58','2026-10-26 10:50:58',NULL,NULL,1,'2026-10-26 10:50:58',97,NULL,'2026-10-26 04:20:58','2026-10-26 04:43:58',NULL),(105,'DEMO100-ORD-095',101,'Demo Customer 095','8801901000095',NULL,NULL,NULL,'takeaway','T-00105','collected','2026-10-26 10:49:58','completed',305.00,0.00,0.00,0.00,305.00,305.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:19:58','2026-10-26 10:19:58','2026-10-26 10:31:58','2026-10-26 10:49:58','2026-10-26 10:49:58',NULL,NULL,1,'2026-10-26 10:49:58',98,NULL,'2026-10-26 04:19:58','2026-10-26 04:43:58',NULL),(106,'DEMO100-ORD-096',102,'Demo Customer 096','8801901000096',NULL,NULL,NULL,'takeaway','T-00106','collected','2026-10-26 10:48:58','completed',330.00,0.00,0.00,0.00,330.00,330.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:18:58','2026-10-26 10:18:58','2026-10-26 10:30:58','2026-10-26 10:48:58','2026-10-26 10:48:58',NULL,NULL,1,'2026-10-26 10:48:58',99,NULL,'2026-10-26 04:18:58','2026-10-26 04:43:58',NULL),(107,'DEMO100-ORD-097',103,'Demo Customer 097','8801901000097',NULL,NULL,NULL,'takeaway','T-00107','collected','2026-10-26 10:47:58','completed',355.00,0.00,0.00,0.00,355.00,355.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:17:58','2026-10-26 10:17:58','2026-10-26 10:29:58','2026-10-26 10:47:58','2026-10-26 10:47:58',NULL,NULL,1,'2026-10-26 10:47:58',101,NULL,'2026-10-26 04:17:58','2026-10-26 04:43:58',NULL),(108,'DEMO100-ORD-098',104,'Demo Customer 098','8801901000098',NULL,NULL,NULL,'takeaway','T-00108','collected','2026-10-26 10:46:58','completed',380.00,0.00,0.00,0.00,380.00,380.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:16:58','2026-10-26 10:16:58','2026-10-26 10:28:58','2026-10-26 10:46:58','2026-10-26 10:46:58',NULL,NULL,1,'2026-10-26 10:46:58',102,NULL,'2026-10-26 04:16:58','2026-10-26 04:43:58',NULL),(109,'DEMO100-ORD-099',105,'Demo Customer 099','8801901000099',NULL,NULL,NULL,'takeaway','T-00109','collected','2026-10-26 10:45:58','completed',405.00,0.00,0.00,0.00,405.00,405.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:15:58','2026-10-26 10:15:58','2026-10-26 10:27:58','2026-10-26 10:45:58','2026-10-26 10:45:58',NULL,NULL,1,'2026-10-26 10:45:58',103,NULL,'2026-10-26 04:15:58','2026-10-26 04:43:58',NULL),(110,'DEMO100-ORD-100',106,'Demo Customer 100','8801901000100',NULL,NULL,NULL,'takeaway','T-00110','collected','2026-10-26 10:44:58','completed',430.00,0.00,0.00,0.00,430.00,430.00,0.00,'paid','cash',NULL,NULL,'DEMO DATA order for local module preview.','Demo kitchen note.','2026-10-26 10:14:58','2026-10-26 10:14:58','2026-10-26 10:26:58','2026-10-26 10:44:58','2026-10-26 10:44:58',NULL,NULL,1,'2026-10-26 10:44:58',104,NULL,'2026-10-26 04:14:58','2026-10-26 04:43:58',NULL),(111,'ORD-20261026-F1E5LC',NULL,NULL,NULL,NULL,NULL,NULL,'takeaway','T-00111','uncollected',NULL,'canceled',750.00,0.00,0.00,0.00,750.00,0.00,0.00,'due',NULL,NULL,NULL,NULL,NULL,'2026-10-26 10:57:39',NULL,NULL,NULL,NULL,'2026-10-26 10:58:36','Item unavailable',0,NULL,1,2,'2026-10-26 04:57:39','2026-10-26 04:58:36',NULL),(112,'ORD-20261026-GGCQ4L',NULL,NULL,NULL,NULL,NULL,NULL,'takeaway','T-00112','uncollected',NULL,'preparing',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-26 10:59:27','2026-10-26 11:02:22',NULL,NULL,NULL,NULL,NULL,0,NULL,1,2,'2026-10-26 04:59:27','2026-10-26 05:02:22',NULL),(113,'ORD-20261026-LA4AAV',NULL,NULL,NULL,NULL,NULL,NULL,'takeaway','T-00113','uncollected',NULL,'pending',205.00,0.00,0.00,0.00,205.00,205.00,0.00,'paid','cash',NULL,NULL,NULL,NULL,'2026-10-26 11:01:30',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,1,2,'2026-10-26 05:01:30','2026-10-26 05:01:40',NULL);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_role_unique` (`role_id`,`permission_id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES (1,1,19,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(2,1,9,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(3,1,4,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(4,1,1,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(5,1,17,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(6,1,13,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(7,1,12,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(8,1,7,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(9,1,11,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(10,1,5,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(11,1,6,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(12,1,18,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(13,1,10,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(14,1,16,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(15,1,3,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(16,1,15,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(17,1,14,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(18,1,8,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(19,1,2,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(20,2,1,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(21,2,4,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(22,2,5,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(23,2,6,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(24,2,7,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(25,2,8,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(26,2,9,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(27,2,10,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(28,2,11,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(29,2,12,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(30,2,13,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(31,2,14,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(32,2,15,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(33,2,16,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(34,2,17,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(35,2,18,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(36,3,1,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(37,3,6,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(38,3,7,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(39,3,8,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(40,4,1,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(41,4,4,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(42,4,5,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(43,4,6,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(44,4,8,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(45,4,10,'2026-10-06 13:10:41','2026-10-06 13:10:41');
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`),
  KEY `permissions_module_index` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.view','View Dashboard','dashboard','Access restaurant dashboard.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(2,'users.manage','Manage Users','users','Manage restaurant users.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(3,'settings.manage','Manage Settings','settings','Manage restaurant settings and branding.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(4,'customers.manage','Manage Customers','customers','Manage restaurant customers.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(5,'orders.create','Create Orders','orders','Create restaurant orders.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(6,'orders.view','View Orders','orders','View restaurant orders.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(7,'kitchen.view','View Kitchen','kitchen','Access kitchen operations.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(8,'tables.manage','Manage Tables','tables','Manage restaurant tables and table availability.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(9,'billing.view','View Billing','billing','Access billing and settlement information.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(10,'reservations.manage','Manage Reservations','reservations','Manage restaurant reservations.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(11,'menu.manage','Manage Menu','menu','Manage restaurant menu items and categories.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(12,'inventory.view','View Inventory','inventory','View warehouse and restaurant inventory.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(13,'inventory.manage','Manage Inventory','inventory','Manage stock and inventory transactions.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(14,'suppliers.manage','Manage Suppliers','suppliers','Manage restaurant suppliers.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(15,'staff.manage','Manage Staff','staff','Manage restaurant staff.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(16,'salary.manage','Manage Salary','salary','Manage employee salary and payroll.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(17,'expenses.manage','Manage Expenses','expenses','Manage restaurant expenses.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(18,'reports.view','View Reports','reports','View restaurant reports.','2026-10-06 13:10:41','2026-10-06 13:10:41'),(19,'audit_logs.view','View Audit Log','audit_logs','View restaurant audit logs.','2026-10-06 13:10:41','2026-10-06 13:10:41');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (22,'App\\Models\\User',3,'tenant-api-token','d7d705c1be884bb9a43747d66cc0756b823f55a9eb742bf6a0b49e876864a5fa','[\"*\"]','2026-10-12 05:51:02',NULL,'2026-10-12 03:01:35','2026-10-12 05:51:02'),(36,'App\\Models\\User',1,'tenant-api-token','1636e2bc1e76a5c0b8e97b2ace5501d2e85741925e69a6217f10eb421301c963','[\"*\"]','2026-10-26 05:56:30',NULL,'2026-10-25 21:07:28','2026-10-26 05:56:30'),(37,'App\\Models\\User',2,'tenant-api-token','436602b5fbcfec9efc9a095270c8d5648736682616a7813bc3968f04f72e823f','[\"*\"]','2026-10-26 05:56:27',NULL,'2026-10-26 02:04:51','2026-10-26 05:56:27');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `received_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_order_items_raw_material_id_purchase_order_id_index` (`raw_material_id`,`purchase_order_id`),
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
INSERT INTO `purchase_order_items` VALUES (1,1,3,'Suger','kg',5.00,0.00,1.40,7.00,'2026-10-07 00:42:58','2026-10-07 00:42:58'),(4,2,1,'Burger','pcs',5.00,0.00,1.00,5.00,'2026-10-08 01:51:03','2026-10-08 01:51:03'),(5,3,4,'Chili','kg',20.00,20.00,1.00,20.00,'2026-10-08 03:01:03','2026-10-08 03:01:58'),(6,4,4,'Chili','kg',100.00,100.00,1.00,100.00,'2026-10-11 00:23:26','2026-10-11 00:23:36'),(7,5,3,'Suger','kg',100.00,0.00,1.00,100.00,'2026-10-11 00:23:53','2026-10-11 00:23:53'),(8,6,1,'Burger','pcs',1.00,0.00,0.00,0.00,'2026-10-11 00:24:42','2026-10-11 00:24:42'),(9,7,5,'Salt','kg',100.00,100.00,0.00,0.00,'2026-10-11 00:25:42','2026-10-11 00:26:02'),(10,8,6,'Zira','kg',100.00,100.00,0.00,0.00,'2026-10-13 01:27:18','2026-10-13 01:27:44'),(11,9,7,'Chicken','kg',100.00,100.00,1.00,100.00,'2026-10-14 02:25:27','2026-10-14 02:25:48'),(12,10,7,'Chicken','kg',10.00,10.00,1.00,10.00,'2026-10-26 02:13:59','2026-10-26 02:14:10'),(13,11,8,'DEMO100-Raw Material 001','litre',11.00,0.00,27.27,300.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(14,12,9,'DEMO100-Raw Material 002','piece',12.00,0.00,29.17,350.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(15,13,10,'DEMO100-Raw Material 003','kg',13.00,0.00,30.77,400.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(16,14,11,'DEMO100-Raw Material 004','litre',14.00,0.00,32.14,450.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(17,15,12,'DEMO100-Raw Material 005','piece',15.00,0.00,33.33,500.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(18,16,13,'DEMO100-Raw Material 006','kg',16.00,0.00,34.38,550.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(19,17,14,'DEMO100-Raw Material 007','litre',17.00,0.00,35.29,600.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(20,18,15,'DEMO100-Raw Material 008','piece',18.00,0.00,36.11,650.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(21,19,16,'DEMO100-Raw Material 009','kg',19.00,0.00,36.84,700.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(22,20,17,'DEMO100-Raw Material 010','litre',20.00,0.00,37.50,750.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(23,21,18,'DEMO100-Raw Material 011','piece',21.00,0.00,38.10,800.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(24,22,19,'DEMO100-Raw Material 012','kg',22.00,0.00,38.64,850.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(25,23,20,'DEMO100-Raw Material 013','litre',23.00,0.00,39.13,900.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(26,24,21,'DEMO100-Raw Material 014','piece',24.00,0.00,39.58,950.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(27,25,22,'DEMO100-Raw Material 015','kg',25.00,0.00,40.00,1000.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(28,26,23,'DEMO100-Raw Material 016','litre',26.00,0.00,40.38,1050.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(29,27,24,'DEMO100-Raw Material 017','piece',27.00,0.00,40.74,1100.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(30,28,25,'DEMO100-Raw Material 018','kg',28.00,0.00,41.07,1150.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(31,29,26,'DEMO100-Raw Material 019','litre',29.00,0.00,41.38,1200.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(32,30,27,'DEMO100-Raw Material 020','piece',10.00,0.00,25.00,250.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(33,31,28,'DEMO100-Raw Material 021','kg',11.00,0.00,27.27,300.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(34,32,29,'DEMO100-Raw Material 022','litre',12.00,0.00,29.17,350.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(35,33,30,'DEMO100-Raw Material 023','piece',13.00,0.00,30.77,400.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(36,34,31,'DEMO100-Raw Material 024','kg',14.00,0.00,32.14,450.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(37,35,32,'DEMO100-Raw Material 025','litre',15.00,0.00,33.33,500.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(38,36,33,'DEMO100-Raw Material 026','piece',16.00,0.00,34.38,550.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(39,37,34,'DEMO100-Raw Material 027','kg',17.00,0.00,35.29,600.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(40,38,35,'DEMO100-Raw Material 028','litre',18.00,0.00,36.11,650.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(41,39,36,'DEMO100-Raw Material 029','piece',19.00,0.00,36.84,700.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(42,40,37,'DEMO100-Raw Material 030','kg',20.00,0.00,37.50,750.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(43,41,38,'DEMO100-Raw Material 031','litre',21.00,0.00,38.10,800.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(44,42,39,'DEMO100-Raw Material 032','piece',22.00,0.00,38.64,850.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(45,43,40,'DEMO100-Raw Material 033','kg',23.00,0.00,39.13,900.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(46,44,41,'DEMO100-Raw Material 034','litre',24.00,0.00,39.58,950.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(47,45,42,'DEMO100-Raw Material 035','piece',25.00,0.00,40.00,1000.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(48,46,43,'DEMO100-Raw Material 036','kg',26.00,0.00,40.38,1050.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(49,47,44,'DEMO100-Raw Material 037','litre',27.00,0.00,40.74,1100.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(50,48,45,'DEMO100-Raw Material 038','piece',28.00,0.00,41.07,1150.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(51,49,46,'DEMO100-Raw Material 039','kg',29.00,0.00,41.38,1200.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(52,50,47,'DEMO100-Raw Material 040','litre',10.00,0.00,25.00,250.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(53,51,48,'DEMO100-Raw Material 041','piece',11.00,0.00,27.27,300.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(54,52,49,'DEMO100-Raw Material 042','kg',12.00,0.00,29.17,350.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(55,53,50,'DEMO100-Raw Material 043','litre',13.00,0.00,30.77,400.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(56,54,51,'DEMO100-Raw Material 044','piece',14.00,0.00,32.14,450.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(57,55,52,'DEMO100-Raw Material 045','kg',15.00,0.00,33.33,500.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(58,56,53,'DEMO100-Raw Material 046','litre',16.00,0.00,34.38,550.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(59,57,54,'DEMO100-Raw Material 047','piece',17.00,0.00,35.29,600.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(60,58,55,'DEMO100-Raw Material 048','kg',18.00,0.00,36.11,650.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(61,59,56,'DEMO100-Raw Material 049','litre',19.00,0.00,36.84,700.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(62,60,57,'DEMO100-Raw Material 050','piece',20.00,0.00,37.50,750.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(63,61,58,'DEMO100-Raw Material 051','kg',21.00,0.00,38.10,800.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(64,62,59,'DEMO100-Raw Material 052','litre',22.00,0.00,38.64,850.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(65,63,60,'DEMO100-Raw Material 053','piece',23.00,0.00,39.13,900.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(66,64,61,'DEMO100-Raw Material 054','kg',24.00,0.00,39.58,950.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(67,65,62,'DEMO100-Raw Material 055','litre',25.00,0.00,40.00,1000.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(68,66,63,'DEMO100-Raw Material 056','piece',26.00,0.00,40.38,1050.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(69,67,64,'DEMO100-Raw Material 057','kg',27.00,0.00,40.74,1100.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(70,68,65,'DEMO100-Raw Material 058','litre',28.00,0.00,41.07,1150.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(71,69,66,'DEMO100-Raw Material 059','piece',29.00,0.00,41.38,1200.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(72,70,67,'DEMO100-Raw Material 060','kg',10.00,0.00,25.00,250.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(73,71,68,'DEMO100-Raw Material 061','litre',11.00,0.00,27.27,300.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(74,72,69,'DEMO100-Raw Material 062','piece',12.00,0.00,29.17,350.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(75,73,70,'DEMO100-Raw Material 063','kg',13.00,0.00,30.77,400.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(76,74,71,'DEMO100-Raw Material 064','litre',14.00,0.00,32.14,450.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(77,75,72,'DEMO100-Raw Material 065','piece',15.00,0.00,33.33,500.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(78,76,73,'DEMO100-Raw Material 066','kg',16.00,0.00,34.38,550.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(79,77,74,'DEMO100-Raw Material 067','litre',17.00,0.00,35.29,600.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(80,78,75,'DEMO100-Raw Material 068','piece',18.00,0.00,36.11,650.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(81,79,76,'DEMO100-Raw Material 069','kg',19.00,0.00,36.84,700.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(82,80,77,'DEMO100-Raw Material 070','litre',20.00,0.00,37.50,750.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(83,81,78,'DEMO100-Raw Material 071','piece',21.00,0.00,38.10,800.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(84,82,79,'DEMO100-Raw Material 072','kg',22.00,0.00,38.64,850.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(85,83,80,'DEMO100-Raw Material 073','litre',23.00,0.00,39.13,900.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(86,84,81,'DEMO100-Raw Material 074','piece',24.00,0.00,39.58,950.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(87,85,82,'DEMO100-Raw Material 075','kg',25.00,0.00,40.00,1000.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(88,86,83,'DEMO100-Raw Material 076','litre',26.00,0.00,40.38,1050.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(89,87,84,'DEMO100-Raw Material 077','piece',27.00,0.00,40.74,1100.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(90,88,85,'DEMO100-Raw Material 078','kg',28.00,0.00,41.07,1150.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(91,89,86,'DEMO100-Raw Material 079','litre',29.00,0.00,41.38,1200.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(92,90,87,'DEMO100-Raw Material 080','piece',10.00,0.00,25.00,250.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(93,91,88,'DEMO100-Raw Material 081','kg',11.00,0.00,27.27,300.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(94,92,89,'DEMO100-Raw Material 082','litre',12.00,0.00,29.17,350.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(95,93,90,'DEMO100-Raw Material 083','piece',13.00,0.00,30.77,400.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(96,94,91,'DEMO100-Raw Material 084','kg',14.00,0.00,32.14,450.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(97,95,92,'DEMO100-Raw Material 085','litre',15.00,0.00,33.33,500.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(98,96,93,'DEMO100-Raw Material 086','piece',16.00,0.00,34.38,550.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(99,97,94,'DEMO100-Raw Material 087','kg',17.00,0.00,35.29,600.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(100,98,95,'DEMO100-Raw Material 088','litre',18.00,0.00,36.11,650.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(101,99,96,'DEMO100-Raw Material 089','piece',19.00,0.00,36.84,700.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(102,100,97,'DEMO100-Raw Material 090','kg',20.00,0.00,37.50,750.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(103,101,98,'DEMO100-Raw Material 091','litre',21.00,0.00,38.10,800.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(104,102,99,'DEMO100-Raw Material 092','piece',22.00,0.00,38.64,850.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(105,103,100,'DEMO100-Raw Material 093','kg',23.00,0.00,39.13,900.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(106,104,101,'DEMO100-Raw Material 094','litre',24.00,0.00,39.58,950.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(107,105,102,'DEMO100-Raw Material 095','piece',25.00,0.00,40.00,1000.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(108,106,103,'DEMO100-Raw Material 096','kg',26.00,0.00,40.38,1050.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(109,107,104,'DEMO100-Raw Material 097','litre',27.00,0.00,40.74,1100.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(110,108,105,'DEMO100-Raw Material 098','piece',28.00,0.00,41.07,1150.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(111,109,106,'DEMO100-Raw Material 099','kg',29.00,0.00,41.38,1200.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(112,110,107,'DEMO100-Raw Material 100','litre',10.00,0.00,25.00,250.00,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(113,111,7,'Chicken','kg',100.00,100.00,1.00,100.00,'2026-10-26 04:51:08','2026-10-26 04:52:32');
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_payments`
--

DROP TABLE IF EXISTS `purchase_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_payments_created_by_foreign` (`created_by`),
  KEY `purchase_order_payments_updated_by_foreign` (`updated_by`),
  KEY `purchase_order_payments_purchase_order_id_payment_date_index` (`purchase_order_id`,`payment_date`),
  CONSTRAINT `purchase_order_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_payments_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_payments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_payments`
--

LOCK TABLES `purchase_order_payments` WRITE;
/*!40000 ALTER TABLE `purchase_order_payments` DISABLE KEYS */;
INSERT INTO `purchase_order_payments` VALUES (1,1,'2026-10-07',1.00,'cash',NULL,'Initial payment recorded when purchase order was created.',1,1,'2026-10-07 00:42:58','2026-10-07 00:42:58'),(2,2,'2026-10-08',1.00,'cash',NULL,'Initial payment recorded when purchase order was created.',1,1,'2026-10-08 01:46:11','2026-10-08 01:46:11'),(3,3,'2026-10-08',5.00,'card',NULL,'Initial payment recorded when purchase order was created.',1,1,'2026-10-08 03:01:03','2026-10-08 03:01:03'),(4,3,'2026-10-08',15.00,'card',NULL,NULL,1,1,'2026-10-08 03:01:58','2026-10-08 03:01:58');
/*!40000 ALTER TABLE `purchase_order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_receipt_items`
--

DROP TABLE IF EXISTS `purchase_order_receipt_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_receipt_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_receipt_id` bigint(20) unsigned NOT NULL,
  `purchase_order_item_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL,
  `total_cost` decimal(18,4) NOT NULL,
  `notes` text DEFAULT NULL,
  `supplier_batch_no` varchar(120) DEFAULT NULL,
  `manufactured_at` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_receipt_item_unique` (`purchase_order_receipt_id`,`purchase_order_item_id`),
  KEY `purchase_order_receipt_items_purchase_order_item_id_foreign` (`purchase_order_item_id`),
  KEY `purchase_order_receipt_items_raw_material_id_index` (`raw_material_id`),
  KEY `po_receipt_item_material_expiry_index` (`raw_material_id`,`expiry_date`),
  CONSTRAINT `purchase_order_receipt_items_purchase_order_item_id_foreign` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`),
  CONSTRAINT `purchase_order_receipt_items_purchase_order_receipt_id_foreign` FOREIGN KEY (`purchase_order_receipt_id`) REFERENCES `purchase_order_receipts` (`id`),
  CONSTRAINT `purchase_order_receipt_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_receipt_items`
--

LOCK TABLES `purchase_order_receipt_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_receipt_items` DISABLE KEYS */;
INSERT INTO `purchase_order_receipt_items` VALUES (8,8,5,4,'Chili','kg',20.0000,1.0000,20.0000,NULL,NULL,NULL,NULL,'2026-10-08 03:01:58','2026-10-08 03:01:58'),(9,9,6,4,'Chili','kg',100.0000,1.0000,100.0000,NULL,NULL,NULL,NULL,'2026-10-11 00:23:36','2026-10-11 00:23:36'),(13,13,9,5,'Salt','kg',100.0000,1.0000,100.0000,NULL,NULL,NULL,NULL,'2026-10-11 00:26:02','2026-10-11 00:26:02'),(15,15,10,6,'Zira','kg',100.0000,1.0000,100.0000,NULL,NULL,NULL,NULL,'2026-10-13 01:27:44','2026-10-13 01:27:44'),(16,16,11,7,'Chicken','kg',100.0000,1.0000,100.0000,NULL,NULL,NULL,NULL,'2026-10-14 02:25:48','2026-10-14 02:25:48'),(17,17,12,7,'Chicken','kg',10.0000,1.0000,10.0000,NULL,NULL,NULL,NULL,'2026-10-26 02:14:10','2026-10-26 02:14:10'),(18,18,113,7,'Chicken','kg',100.0000,1.0000,100.0000,NULL,NULL,NULL,NULL,'2026-10-26 04:52:32','2026-10-26 04:52:32');
/*!40000 ALTER TABLE `purchase_order_receipt_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_receipts`
--

DROP TABLE IF EXISTS `purchase_order_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `receipt_no` varchar(80) NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_order_receipts_receipt_no_unique` (`receipt_no`),
  KEY `purchase_order_receipts_received_by_foreign` (`received_by`),
  KEY `purchase_order_receipts_created_by_foreign` (`created_by`),
  KEY `purchase_order_receipts_updated_by_foreign` (`updated_by`),
  KEY `purchase_order_receipts_purchase_order_id_received_at_index` (`purchase_order_id`,`received_at`),
  CONSTRAINT `purchase_order_receipts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_receipts_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  CONSTRAINT `purchase_order_receipts_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_receipts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_receipts`
--

LOCK TABLES `purchase_order_receipts` WRITE;
/*!40000 ALTER TABLE `purchase_order_receipts` DISABLE KEYS */;
INSERT INTO `purchase_order_receipts` VALUES (8,3,'GRN-20261008-000008','2026-10-08 09:01:58',NULL,1,1,1,'2026-10-08 03:01:58','2026-10-08 03:01:58'),(9,4,'GRN-20261011-000009','2026-10-11 06:23:36',NULL,1,1,1,'2026-10-11 00:23:36','2026-10-11 00:23:36'),(13,7,'GRN-20261011-000013','2026-10-11 06:26:02',NULL,1,1,1,'2026-10-11 00:26:02','2026-10-11 00:26:02'),(15,8,'GRN-20261013-000015','2026-10-13 07:27:44',NULL,1,1,1,'2026-10-13 01:27:44','2026-10-13 01:27:44'),(16,9,'GRN-20261014-000016','2026-10-14 08:25:48',NULL,1,1,1,'2026-10-14 02:25:48','2026-10-14 02:25:48'),(17,10,'GRN-20261026-000017','2026-10-26 08:14:10',NULL,1,1,1,'2026-10-26 02:14:10','2026-10-26 02:14:10'),(18,111,'GRN-20261026-000018','2026-10-26 10:52:32',NULL,1,1,1,'2026-10-26 04:52:32','2026-10-26 04:52:32');
/*!40000 ALTER TABLE `purchase_order_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `order_date` datetime NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'ordered',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `return_credit_applied` decimal(18,2) NOT NULL DEFAULT 0.00,
  `supplier_credit_applied` decimal(18,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `ordered_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_orders_ordered_by_foreign` (`ordered_by`),
  KEY `purchase_orders_created_by_foreign` (`created_by`),
  KEY `purchase_orders_updated_by_foreign` (`updated_by`),
  KEY `purchase_orders_supplier_id_order_date_index` (`supplier_id`,`order_date`),
  KEY `purchase_orders_status_index` (`status`),
  CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_ordered_by_foreign` FOREIGN KEY (`ordered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchase_orders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
INSERT INTO `purchase_orders` VALUES (1,1,'2026-10-07 00:00:00','2026-10-07','ordered',7.00,0.20,0.70,7.90,1.00,0.00,0.00,6.90,'cash',1,NULL,1,1,'2026-10-07 00:42:58','2026-10-07 00:42:58',NULL),(2,1,'2026-10-08 00:00:00','2026-10-08','ordered',5.00,0.20,0.30,5.50,1.00,0.00,0.00,4.50,'cash',1,'order',1,1,'2026-10-08 01:46:11','2026-10-08 01:51:03',NULL),(3,1,'2026-10-08 00:00:00','2026-10-08','received',20.00,0.00,0.00,20.00,20.00,0.00,0.00,0.00,'card',1,NULL,1,1,'2026-10-08 03:01:03','2026-10-08 03:01:58',NULL),(4,1,'2026-10-11 00:00:00','2026-10-11','received',100.00,0.00,0.00,100.00,0.00,0.00,0.00,100.00,NULL,1,NULL,1,1,'2026-10-11 00:23:26','2026-10-11 00:23:36',NULL),(5,1,'2026-10-11 00:00:00','2026-10-11','ordered',100.00,0.00,0.00,100.00,0.00,0.00,0.00,100.00,NULL,1,NULL,1,1,'2026-10-11 00:23:53','2026-10-11 00:23:53',NULL),(6,1,'2026-10-11 00:00:00','2026-10-11','ordered',0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,1,NULL,1,1,'2026-10-11 00:24:42','2026-10-11 00:24:42',NULL),(7,1,'2026-10-11 00:00:00','2026-10-11','received',0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,1,NULL,1,1,'2026-10-11 00:25:42','2026-10-11 00:26:02',NULL),(8,1,'2026-10-13 00:00:00','2026-10-13','received',0.00,0.00,0.00,0.00,0.00,0.00,0.00,0.00,NULL,1,NULL,1,1,'2026-10-13 01:27:18','2026-10-13 01:27:44',NULL),(9,1,'2026-10-14 00:00:00','2026-10-14','received',100.00,0.00,0.00,100.00,0.00,0.00,0.00,100.00,NULL,1,NULL,1,1,'2026-10-14 02:25:27','2026-10-14 02:25:48',NULL),(10,1,'2026-10-26 00:00:00','2026-10-26','received',10.00,0.00,0.00,10.00,0.00,0.00,0.00,10.00,NULL,1,NULL,1,1,'2026-10-26 02:13:59','2026-10-26 02:14:10',NULL),(11,2,'2026-10-25 10:43:58','2026-10-28','ordered',300.00,0.00,0.00,300.00,0.00,0.00,0.00,300.00,NULL,4,'DEMO100-Purchase Order 001',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,3,'2026-10-24 10:43:58','2026-10-29','ordered',350.00,0.00,0.00,350.00,0.00,0.00,0.00,350.00,NULL,5,'DEMO100-Purchase Order 002',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,4,'2026-10-23 10:43:58','2026-10-30','ordered',400.00,0.00,0.00,400.00,0.00,0.00,0.00,400.00,NULL,6,'DEMO100-Purchase Order 003',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,5,'2026-10-22 10:43:58','2026-10-31','ordered',450.00,0.00,0.00,450.00,0.00,0.00,0.00,450.00,NULL,7,'DEMO100-Purchase Order 004',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,6,'2026-10-21 10:43:58','2026-11-01','ordered',500.00,0.00,0.00,500.00,0.00,0.00,0.00,500.00,NULL,8,'DEMO100-Purchase Order 005',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,7,'2026-10-20 10:43:58','2026-11-02','ordered',550.00,0.00,0.00,550.00,0.00,0.00,0.00,550.00,NULL,9,'DEMO100-Purchase Order 006',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,8,'2026-10-19 10:43:58','2026-11-03','ordered',600.00,0.00,0.00,600.00,0.00,0.00,0.00,600.00,NULL,10,'DEMO100-Purchase Order 007',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,9,'2026-10-18 10:43:58','2026-11-04','ordered',650.00,0.00,0.00,650.00,0.00,0.00,0.00,650.00,NULL,11,'DEMO100-Purchase Order 008',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,10,'2026-10-17 10:43:58','2026-11-05','ordered',700.00,0.00,0.00,700.00,0.00,0.00,0.00,700.00,NULL,12,'DEMO100-Purchase Order 009',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,11,'2026-10-16 10:43:58','2026-10-27','ordered',750.00,0.00,0.00,750.00,0.00,0.00,0.00,750.00,NULL,13,'DEMO100-Purchase Order 010',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,12,'2026-10-15 10:43:58','2026-10-28','ordered',800.00,0.00,0.00,800.00,0.00,0.00,0.00,800.00,NULL,14,'DEMO100-Purchase Order 011',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,13,'2026-10-14 10:43:58','2026-10-29','ordered',850.00,0.00,0.00,850.00,0.00,0.00,0.00,850.00,NULL,15,'DEMO100-Purchase Order 012',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,14,'2026-10-13 10:43:58','2026-10-30','ordered',900.00,0.00,0.00,900.00,0.00,0.00,0.00,900.00,NULL,16,'DEMO100-Purchase Order 013',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,15,'2026-10-12 10:43:58','2026-10-31','ordered',950.00,0.00,0.00,950.00,0.00,0.00,0.00,950.00,NULL,17,'DEMO100-Purchase Order 014',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,16,'2026-10-11 10:43:58','2026-11-01','ordered',1000.00,0.00,0.00,1000.00,0.00,0.00,0.00,1000.00,NULL,18,'DEMO100-Purchase Order 015',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,17,'2026-10-10 10:43:58','2026-11-02','ordered',1050.00,0.00,0.00,1050.00,0.00,0.00,0.00,1050.00,NULL,19,'DEMO100-Purchase Order 016',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,18,'2026-10-09 10:43:58','2026-11-03','ordered',1100.00,0.00,0.00,1100.00,0.00,0.00,0.00,1100.00,NULL,20,'DEMO100-Purchase Order 017',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,19,'2026-10-08 10:43:58','2026-11-04','ordered',1150.00,0.00,0.00,1150.00,0.00,0.00,0.00,1150.00,NULL,21,'DEMO100-Purchase Order 018',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,20,'2026-10-07 10:43:58','2026-11-05','ordered',1200.00,0.00,0.00,1200.00,0.00,0.00,0.00,1200.00,NULL,22,'DEMO100-Purchase Order 019',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,21,'2026-10-06 10:43:58','2026-10-27','ordered',250.00,0.00,0.00,250.00,0.00,0.00,0.00,250.00,NULL,23,'DEMO100-Purchase Order 020',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,22,'2026-10-05 10:43:58','2026-10-28','ordered',300.00,0.00,0.00,300.00,0.00,0.00,0.00,300.00,NULL,24,'DEMO100-Purchase Order 021',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,23,'2026-10-04 10:43:58','2026-10-29','ordered',350.00,0.00,0.00,350.00,0.00,0.00,0.00,350.00,NULL,25,'DEMO100-Purchase Order 022',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,24,'2026-10-03 10:43:58','2026-10-30','ordered',400.00,0.00,0.00,400.00,0.00,0.00,0.00,400.00,NULL,26,'DEMO100-Purchase Order 023',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,25,'2026-10-02 10:43:58','2026-10-31','ordered',450.00,0.00,0.00,450.00,0.00,0.00,0.00,450.00,NULL,27,'DEMO100-Purchase Order 024',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,26,'2026-10-01 10:43:58','2026-11-01','ordered',500.00,0.00,0.00,500.00,0.00,0.00,0.00,500.00,NULL,28,'DEMO100-Purchase Order 025',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,27,'2026-09-30 10:43:58','2026-11-02','ordered',550.00,0.00,0.00,550.00,0.00,0.00,0.00,550.00,NULL,29,'DEMO100-Purchase Order 026',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,28,'2026-09-29 10:43:58','2026-11-03','ordered',600.00,0.00,0.00,600.00,0.00,0.00,0.00,600.00,NULL,30,'DEMO100-Purchase Order 027',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,29,'2026-09-28 10:43:58','2026-11-04','ordered',650.00,0.00,0.00,650.00,0.00,0.00,0.00,650.00,NULL,31,'DEMO100-Purchase Order 028',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,30,'2026-09-27 10:43:58','2026-11-05','ordered',700.00,0.00,0.00,700.00,0.00,0.00,0.00,700.00,NULL,32,'DEMO100-Purchase Order 029',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,31,'2026-09-26 10:43:58','2026-10-27','ordered',750.00,0.00,0.00,750.00,0.00,0.00,0.00,750.00,NULL,33,'DEMO100-Purchase Order 030',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,32,'2026-09-25 10:43:58','2026-10-28','ordered',800.00,0.00,0.00,800.00,0.00,0.00,0.00,800.00,NULL,34,'DEMO100-Purchase Order 031',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,33,'2026-09-24 10:43:58','2026-10-29','ordered',850.00,0.00,0.00,850.00,0.00,0.00,0.00,850.00,NULL,35,'DEMO100-Purchase Order 032',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,34,'2026-09-23 10:43:58','2026-10-30','ordered',900.00,0.00,0.00,900.00,0.00,0.00,0.00,900.00,NULL,36,'DEMO100-Purchase Order 033',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,35,'2026-09-22 10:43:58','2026-10-31','ordered',950.00,0.00,0.00,950.00,0.00,0.00,0.00,950.00,NULL,37,'DEMO100-Purchase Order 034',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,36,'2026-09-21 10:43:58','2026-11-01','ordered',1000.00,0.00,0.00,1000.00,0.00,0.00,0.00,1000.00,NULL,38,'DEMO100-Purchase Order 035',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,37,'2026-09-20 10:43:58','2026-11-02','ordered',1050.00,0.00,0.00,1050.00,0.00,0.00,0.00,1050.00,NULL,39,'DEMO100-Purchase Order 036',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,38,'2026-09-19 10:43:58','2026-11-03','ordered',1100.00,0.00,0.00,1100.00,0.00,0.00,0.00,1100.00,NULL,40,'DEMO100-Purchase Order 037',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,39,'2026-09-18 10:43:58','2026-11-04','ordered',1150.00,0.00,0.00,1150.00,0.00,0.00,0.00,1150.00,NULL,41,'DEMO100-Purchase Order 038',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,40,'2026-09-17 10:43:58','2026-11-05','ordered',1200.00,0.00,0.00,1200.00,0.00,0.00,0.00,1200.00,NULL,42,'DEMO100-Purchase Order 039',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,41,'2026-09-16 10:43:58','2026-10-27','ordered',250.00,0.00,0.00,250.00,0.00,0.00,0.00,250.00,NULL,43,'DEMO100-Purchase Order 040',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,42,'2026-09-15 10:43:58','2026-10-28','ordered',300.00,0.00,0.00,300.00,0.00,0.00,0.00,300.00,NULL,44,'DEMO100-Purchase Order 041',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,43,'2026-09-14 10:43:58','2026-10-29','ordered',350.00,0.00,0.00,350.00,0.00,0.00,0.00,350.00,NULL,45,'DEMO100-Purchase Order 042',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,44,'2026-09-13 10:43:58','2026-10-30','ordered',400.00,0.00,0.00,400.00,0.00,0.00,0.00,400.00,NULL,46,'DEMO100-Purchase Order 043',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,45,'2026-09-12 10:43:58','2026-10-31','ordered',450.00,0.00,0.00,450.00,0.00,0.00,0.00,450.00,NULL,47,'DEMO100-Purchase Order 044',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,46,'2026-10-26 10:43:58','2026-11-01','ordered',500.00,0.00,0.00,500.00,0.00,0.00,0.00,500.00,NULL,48,'DEMO100-Purchase Order 045',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,47,'2026-10-25 10:43:58','2026-11-02','ordered',550.00,0.00,0.00,550.00,0.00,0.00,0.00,550.00,NULL,49,'DEMO100-Purchase Order 046',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,48,'2026-10-24 10:43:58','2026-11-03','ordered',600.00,0.00,0.00,600.00,0.00,0.00,0.00,600.00,NULL,50,'DEMO100-Purchase Order 047',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,49,'2026-10-23 10:43:58','2026-11-04','ordered',650.00,0.00,0.00,650.00,0.00,0.00,0.00,650.00,NULL,51,'DEMO100-Purchase Order 048',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,50,'2026-10-22 10:43:58','2026-11-05','ordered',700.00,0.00,0.00,700.00,0.00,0.00,0.00,700.00,NULL,52,'DEMO100-Purchase Order 049',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,51,'2026-10-21 10:43:58','2026-10-27','ordered',750.00,0.00,0.00,750.00,0.00,0.00,0.00,750.00,NULL,53,'DEMO100-Purchase Order 050',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,52,'2026-10-20 10:43:58','2026-10-28','ordered',800.00,0.00,0.00,800.00,0.00,0.00,0.00,800.00,NULL,54,'DEMO100-Purchase Order 051',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,53,'2026-10-19 10:43:58','2026-10-29','ordered',850.00,0.00,0.00,850.00,0.00,0.00,0.00,850.00,NULL,55,'DEMO100-Purchase Order 052',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,54,'2026-10-18 10:43:58','2026-10-30','ordered',900.00,0.00,0.00,900.00,0.00,0.00,0.00,900.00,NULL,56,'DEMO100-Purchase Order 053',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,55,'2026-10-17 10:43:58','2026-10-31','ordered',950.00,0.00,0.00,950.00,0.00,0.00,0.00,950.00,NULL,57,'DEMO100-Purchase Order 054',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,56,'2026-10-16 10:43:58','2026-11-01','ordered',1000.00,0.00,0.00,1000.00,0.00,0.00,0.00,1000.00,NULL,58,'DEMO100-Purchase Order 055',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,57,'2026-10-15 10:43:58','2026-11-02','ordered',1050.00,0.00,0.00,1050.00,0.00,0.00,0.00,1050.00,NULL,59,'DEMO100-Purchase Order 056',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,58,'2026-10-14 10:43:58','2026-11-03','ordered',1100.00,0.00,0.00,1100.00,0.00,0.00,0.00,1100.00,NULL,60,'DEMO100-Purchase Order 057',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,59,'2026-10-13 10:43:58','2026-11-04','ordered',1150.00,0.00,0.00,1150.00,0.00,0.00,0.00,1150.00,NULL,61,'DEMO100-Purchase Order 058',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,60,'2026-10-12 10:43:58','2026-11-05','ordered',1200.00,0.00,0.00,1200.00,0.00,0.00,0.00,1200.00,NULL,62,'DEMO100-Purchase Order 059',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,61,'2026-10-11 10:43:58','2026-10-27','ordered',250.00,0.00,0.00,250.00,0.00,0.00,0.00,250.00,NULL,63,'DEMO100-Purchase Order 060',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,62,'2026-10-10 10:43:58','2026-10-28','ordered',300.00,0.00,0.00,300.00,0.00,0.00,0.00,300.00,NULL,64,'DEMO100-Purchase Order 061',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,63,'2026-10-09 10:43:58','2026-10-29','ordered',350.00,0.00,0.00,350.00,0.00,0.00,0.00,350.00,NULL,65,'DEMO100-Purchase Order 062',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,64,'2026-10-08 10:43:58','2026-10-30','ordered',400.00,0.00,0.00,400.00,0.00,0.00,0.00,400.00,NULL,66,'DEMO100-Purchase Order 063',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,65,'2026-10-07 10:43:58','2026-10-31','ordered',450.00,0.00,0.00,450.00,0.00,0.00,0.00,450.00,NULL,67,'DEMO100-Purchase Order 064',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,66,'2026-10-06 10:43:58','2026-11-01','ordered',500.00,0.00,0.00,500.00,0.00,0.00,0.00,500.00,NULL,68,'DEMO100-Purchase Order 065',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,67,'2026-10-05 10:43:58','2026-11-02','ordered',550.00,0.00,0.00,550.00,0.00,0.00,0.00,550.00,NULL,69,'DEMO100-Purchase Order 066',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,68,'2026-10-04 10:43:58','2026-11-03','ordered',600.00,0.00,0.00,600.00,0.00,0.00,0.00,600.00,NULL,70,'DEMO100-Purchase Order 067',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,69,'2026-10-03 10:43:58','2026-11-04','ordered',650.00,0.00,0.00,650.00,0.00,0.00,0.00,650.00,NULL,71,'DEMO100-Purchase Order 068',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,70,'2026-10-02 10:43:58','2026-11-05','ordered',700.00,0.00,0.00,700.00,0.00,0.00,0.00,700.00,NULL,72,'DEMO100-Purchase Order 069',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,71,'2026-10-01 10:43:58','2026-10-27','ordered',750.00,0.00,0.00,750.00,0.00,0.00,0.00,750.00,NULL,73,'DEMO100-Purchase Order 070',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,72,'2026-09-30 10:43:58','2026-10-28','ordered',800.00,0.00,0.00,800.00,0.00,0.00,0.00,800.00,NULL,74,'DEMO100-Purchase Order 071',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,73,'2026-09-29 10:43:58','2026-10-29','ordered',850.00,0.00,0.00,850.00,0.00,0.00,0.00,850.00,NULL,75,'DEMO100-Purchase Order 072',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,74,'2026-09-28 10:43:58','2026-10-30','ordered',900.00,0.00,0.00,900.00,0.00,0.00,0.00,900.00,NULL,76,'DEMO100-Purchase Order 073',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,75,'2026-09-27 10:43:58','2026-10-31','ordered',950.00,0.00,0.00,950.00,0.00,0.00,0.00,950.00,NULL,77,'DEMO100-Purchase Order 074',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,76,'2026-09-26 10:43:58','2026-11-01','ordered',1000.00,0.00,0.00,1000.00,0.00,0.00,0.00,1000.00,NULL,78,'DEMO100-Purchase Order 075',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,77,'2026-09-25 10:43:58','2026-11-02','ordered',1050.00,0.00,0.00,1050.00,0.00,0.00,0.00,1050.00,NULL,79,'DEMO100-Purchase Order 076',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,78,'2026-09-24 10:43:58','2026-11-03','ordered',1100.00,0.00,0.00,1100.00,0.00,0.00,0.00,1100.00,NULL,80,'DEMO100-Purchase Order 077',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,79,'2026-09-23 10:43:58','2026-11-04','ordered',1150.00,0.00,0.00,1150.00,0.00,0.00,0.00,1150.00,NULL,81,'DEMO100-Purchase Order 078',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,80,'2026-09-22 10:43:58','2026-11-05','ordered',1200.00,0.00,0.00,1200.00,0.00,0.00,0.00,1200.00,NULL,82,'DEMO100-Purchase Order 079',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,81,'2026-09-21 10:43:58','2026-10-27','ordered',250.00,0.00,0.00,250.00,0.00,0.00,0.00,250.00,NULL,83,'DEMO100-Purchase Order 080',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,82,'2026-09-20 10:43:58','2026-10-28','ordered',300.00,0.00,0.00,300.00,0.00,0.00,0.00,300.00,NULL,84,'DEMO100-Purchase Order 081',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,83,'2026-09-19 10:43:58','2026-10-29','ordered',350.00,0.00,0.00,350.00,0.00,0.00,0.00,350.00,NULL,85,'DEMO100-Purchase Order 082',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,84,'2026-09-18 10:43:58','2026-10-30','ordered',400.00,0.00,0.00,400.00,0.00,0.00,0.00,400.00,NULL,86,'DEMO100-Purchase Order 083',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,85,'2026-09-17 10:43:58','2026-10-31','ordered',450.00,0.00,0.00,450.00,0.00,0.00,0.00,450.00,NULL,87,'DEMO100-Purchase Order 084',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,86,'2026-09-16 10:43:58','2026-11-01','ordered',500.00,0.00,0.00,500.00,0.00,0.00,0.00,500.00,NULL,88,'DEMO100-Purchase Order 085',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,87,'2026-09-15 10:43:58','2026-11-02','ordered',550.00,0.00,0.00,550.00,0.00,0.00,0.00,550.00,NULL,89,'DEMO100-Purchase Order 086',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,88,'2026-09-14 10:43:58','2026-11-03','ordered',600.00,0.00,0.00,600.00,0.00,0.00,0.00,600.00,NULL,90,'DEMO100-Purchase Order 087',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,89,'2026-09-13 10:43:58','2026-11-04','ordered',650.00,0.00,0.00,650.00,0.00,0.00,0.00,650.00,NULL,91,'DEMO100-Purchase Order 088',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,90,'2026-09-12 10:43:58','2026-11-05','ordered',700.00,0.00,0.00,700.00,0.00,0.00,0.00,700.00,NULL,92,'DEMO100-Purchase Order 089',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,91,'2026-10-26 10:43:58','2026-10-27','ordered',750.00,0.00,0.00,750.00,0.00,0.00,0.00,750.00,NULL,93,'DEMO100-Purchase Order 090',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,92,'2026-10-25 10:43:58','2026-10-28','ordered',800.00,0.00,0.00,800.00,0.00,0.00,0.00,800.00,NULL,94,'DEMO100-Purchase Order 091',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,93,'2026-10-24 10:43:58','2026-10-29','ordered',850.00,0.00,0.00,850.00,0.00,0.00,0.00,850.00,NULL,95,'DEMO100-Purchase Order 092',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(103,94,'2026-10-23 10:43:58','2026-10-30','ordered',900.00,0.00,0.00,900.00,0.00,0.00,0.00,900.00,NULL,96,'DEMO100-Purchase Order 093',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(104,95,'2026-10-22 10:43:58','2026-10-31','ordered',950.00,0.00,0.00,950.00,0.00,0.00,0.00,950.00,NULL,97,'DEMO100-Purchase Order 094',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(105,96,'2026-10-21 10:43:58','2026-11-01','ordered',1000.00,0.00,0.00,1000.00,0.00,0.00,0.00,1000.00,NULL,98,'DEMO100-Purchase Order 095',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(106,97,'2026-10-20 10:43:58','2026-11-02','ordered',1050.00,0.00,0.00,1050.00,0.00,0.00,0.00,1050.00,NULL,99,'DEMO100-Purchase Order 096',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(107,98,'2026-10-19 10:43:58','2026-11-03','ordered',1100.00,0.00,0.00,1100.00,0.00,0.00,0.00,1100.00,NULL,101,'DEMO100-Purchase Order 097',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(108,99,'2026-10-18 10:43:58','2026-11-04','ordered',1150.00,0.00,0.00,1150.00,0.00,0.00,0.00,1150.00,NULL,102,'DEMO100-Purchase Order 098',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(109,100,'2026-10-17 10:43:58','2026-11-05','ordered',1200.00,0.00,0.00,1200.00,0.00,0.00,0.00,1200.00,NULL,103,'DEMO100-Purchase Order 099',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(110,101,'2026-10-16 10:43:58','2026-10-27','ordered',250.00,0.00,0.00,250.00,0.00,0.00,0.00,250.00,NULL,104,'DEMO100-Purchase Order 100',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(111,101,'2026-10-26 00:00:00','2026-10-26','received',100.00,0.00,0.00,100.00,0.00,0.00,0.00,100.00,NULL,1,NULL,1,1,'2026-10-26 04:51:08','2026-10-26 04:52:32',NULL);
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_return_items`
--

DROP TABLE IF EXISTS `purchase_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_return_id` bigint(20) unsigned NOT NULL,
  `warehouse_stock_lot_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_receipt_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(180) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `lot_no` varchar(80) NOT NULL,
  `supplier_batch_no` varchar(120) DEFAULT NULL,
  `manufactured_at` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `lot_quantity_before` decimal(18,4) unsigned NOT NULL,
  `lot_quantity_after` decimal(18,4) unsigned NOT NULL,
  `warehouse_quantity_before` decimal(18,4) unsigned NOT NULL,
  `warehouse_quantity_after` decimal(18,4) unsigned NOT NULL,
  `return_unit_cost` decimal(18,4) unsigned NOT NULL,
  `expected_supplier_credit` decimal(18,4) unsigned NOT NULL,
  `inventory_average_unit_cost` decimal(18,4) unsigned NOT NULL,
  `inventory_value_reduction` decimal(18,4) unsigned NOT NULL,
  `valuation_variance` decimal(18,4) NOT NULL,
  `reason` varchar(60) NOT NULL,
  `reason_notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pri_return_fk` (`purchase_return_id`),
  KEY `pri_po_fk` (`purchase_order_id`),
  KEY `pri_grn_fk` (`purchase_order_receipt_id`),
  KEY `pri_lot_date_idx` (`warehouse_stock_lot_id`,`created_at`),
  KEY `pri_material_date_idx` (`raw_material_id`,`created_at`),
  KEY `pri_reason_idx` (`reason`),
  CONSTRAINT `pri_grn_fk` FOREIGN KEY (`purchase_order_receipt_id`) REFERENCES `purchase_order_receipts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pri_lot_fk` FOREIGN KEY (`warehouse_stock_lot_id`) REFERENCES `warehouse_stock_lots` (`id`),
  CONSTRAINT `pri_material_fk` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `pri_po_fk` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pri_return_fk` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_return_items`
--

LOCK TABLES `purchase_return_items` WRITE;
/*!40000 ALTER TABLE `purchase_return_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_return_settlements`
--

DROP TABLE IF EXISTS `purchase_return_settlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_return_settlements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `settlement_no` varchar(80) NOT NULL,
  `purchase_return_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `settlement_type` varchar(60) NOT NULL,
  `amount` decimal(18,2) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `parent_settlement_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `settled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `allocation_effect` tinyint(1) NOT NULL DEFAULT 1,
  `cash_effect` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_return_settlements_settlement_no_unique` (`settlement_no`),
  KEY `prs_po_fk` (`purchase_order_id`),
  KEY `prs_parent_fk` (`parent_settlement_id`),
  KEY `prs_user_fk` (`created_by`),
  KEY `prs_return_date_idx` (`purchase_return_id`,`settled_at`),
  KEY `prs_supplier_date_idx` (`supplier_id`,`settled_at`),
  KEY `prs_type_idx` (`settlement_type`),
  CONSTRAINT `prs_parent_fk` FOREIGN KEY (`parent_settlement_id`) REFERENCES `purchase_return_settlements` (`id`),
  CONSTRAINT `prs_po_fk` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prs_return_fk` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`),
  CONSTRAINT `prs_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `prs_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_return_settlements`
--

LOCK TABLES `purchase_return_settlements` WRITE;
/*!40000 ALTER TABLE `purchase_return_settlements` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_return_settlements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_returns`
--

DROP TABLE IF EXISTS `purchase_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_no` varchar(80) NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `supplier_name` varchar(180) NOT NULL,
  `supplier_phone` varchar(80) DEFAULT NULL,
  `supplier_email` varchar(180) DEFAULT NULL,
  `supplier_address` text DEFAULT NULL,
  `returned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_items` int(10) unsigned NOT NULL DEFAULT 0,
  `expected_supplier_credit` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `inventory_value_reduction` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `valuation_variance` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_returns_return_no_unique` (`return_no`),
  KEY `pr_creator_fk` (`created_by`),
  KEY `pr_returned_at_idx` (`returned_at`),
  KEY `pr_supplier_date_idx` (`supplier_id`,`returned_at`),
  CONSTRAINT `pr_creator_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pr_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_returns`
--

LOCK TABLES `purchase_returns` WRITE;
/*!40000 ALTER TABLE `purchase_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raw_materials`
--

DROP TABLE IF EXISTS `raw_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raw_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_name` varchar(180) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `base_unit` varchar(30) NOT NULL,
  `warehouse_minimum_quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `restaurant_minimum_quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `raw_materials_created_by_foreign` (`created_by`),
  KEY `raw_materials_updated_by_foreign` (`updated_by`),
  KEY `raw_materials_material_name_base_unit_is_active_index` (`material_name`,`base_unit`,`is_active`),
  KEY `raw_materials_material_name_index` (`material_name`),
  KEY `raw_materials_category_index` (`category`),
  KEY `raw_materials_is_active_index` (`is_active`),
  CONSTRAINT `raw_materials_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `raw_materials_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raw_materials`
--

LOCK TABLES `raw_materials` WRITE;
/*!40000 ALTER TABLE `raw_materials` DISABLE KEYS */;
INSERT INTO `raw_materials` VALUES (1,'Burger','Junk Food','pcs',0.0000,0.0000,1,1,1,'2026-10-06 19:26:35','2026-10-06 19:26:35',NULL),(2,'Burger Test Batch',NULL,'pcs',0.0000,0.0000,1,1,1,'2026-10-06 20:01:36','2026-10-06 20:01:36',NULL),(3,'Suger','Grocery','kg',40.0000,40.0000,1,1,1,'2026-10-06 22:53:10','2026-10-06 22:53:10',NULL),(4,'Chili','Grocery','kg',0.0000,0.0000,1,1,1,'2026-10-08 02:56:05','2026-10-08 02:56:05',NULL),(5,'Salt','Grocery','kg',0.0000,0.0000,1,1,1,'2026-10-11 00:25:20','2026-10-11 00:25:20',NULL),(6,'Zira','Grocery','kg',0.0000,0.0000,1,1,1,'2026-10-13 01:22:09','2026-10-13 01:22:09',NULL),(7,'Chicken','Grocery','kg',0.0000,0.0000,1,1,1,'2026-10-14 02:24:55','2026-10-14 02:24:55',NULL),(8,'DEMO100-Raw Material 001','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,'DEMO100-Raw Material 002','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,'DEMO100-Raw Material 003','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,'DEMO100-Raw Material 004','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,'DEMO100-Raw Material 005','Dairy','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,'DEMO100-Raw Material 006','Dry Goods','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,'DEMO100-Raw Material 007','Meat','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,'DEMO100-Raw Material 008','Produce','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,'DEMO100-Raw Material 009','Dairy','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,'DEMO100-Raw Material 010','Dry Goods','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,'DEMO100-Raw Material 011','Meat','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,'DEMO100-Raw Material 012','Produce','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,'DEMO100-Raw Material 013','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,'DEMO100-Raw Material 014','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,'DEMO100-Raw Material 015','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,'DEMO100-Raw Material 016','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,'DEMO100-Raw Material 017','Dairy','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,'DEMO100-Raw Material 018','Dry Goods','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,'DEMO100-Raw Material 019','Meat','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,'DEMO100-Raw Material 020','Produce','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,'DEMO100-Raw Material 021','Dairy','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,'DEMO100-Raw Material 022','Dry Goods','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,'DEMO100-Raw Material 023','Meat','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,'DEMO100-Raw Material 024','Produce','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,'DEMO100-Raw Material 025','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,'DEMO100-Raw Material 026','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,'DEMO100-Raw Material 027','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,'DEMO100-Raw Material 028','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,'DEMO100-Raw Material 029','Dairy','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,'DEMO100-Raw Material 030','Dry Goods','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,'DEMO100-Raw Material 031','Meat','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,'DEMO100-Raw Material 032','Produce','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,'DEMO100-Raw Material 033','Dairy','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,'DEMO100-Raw Material 034','Dry Goods','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,'DEMO100-Raw Material 035','Meat','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,'DEMO100-Raw Material 036','Produce','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,'DEMO100-Raw Material 037','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,'DEMO100-Raw Material 038','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,'DEMO100-Raw Material 039','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,'DEMO100-Raw Material 040','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,'DEMO100-Raw Material 041','Dairy','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,'DEMO100-Raw Material 042','Dry Goods','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,'DEMO100-Raw Material 043','Meat','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,'DEMO100-Raw Material 044','Produce','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,'DEMO100-Raw Material 045','Dairy','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,'DEMO100-Raw Material 046','Dry Goods','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,'DEMO100-Raw Material 047','Meat','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,'DEMO100-Raw Material 048','Produce','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,'DEMO100-Raw Material 049','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,'DEMO100-Raw Material 050','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,'DEMO100-Raw Material 051','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,'DEMO100-Raw Material 052','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,'DEMO100-Raw Material 053','Dairy','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,'DEMO100-Raw Material 054','Dry Goods','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,'DEMO100-Raw Material 055','Meat','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,'DEMO100-Raw Material 056','Produce','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,'DEMO100-Raw Material 057','Dairy','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,'DEMO100-Raw Material 058','Dry Goods','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,'DEMO100-Raw Material 059','Meat','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,'DEMO100-Raw Material 060','Produce','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,'DEMO100-Raw Material 061','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,'DEMO100-Raw Material 062','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,'DEMO100-Raw Material 063','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,'DEMO100-Raw Material 064','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,'DEMO100-Raw Material 065','Dairy','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,'DEMO100-Raw Material 066','Dry Goods','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,'DEMO100-Raw Material 067','Meat','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,'DEMO100-Raw Material 068','Produce','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,'DEMO100-Raw Material 069','Dairy','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,'DEMO100-Raw Material 070','Dry Goods','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,'DEMO100-Raw Material 071','Meat','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,'DEMO100-Raw Material 072','Produce','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,'DEMO100-Raw Material 073','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,'DEMO100-Raw Material 074','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,'DEMO100-Raw Material 075','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,'DEMO100-Raw Material 076','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,'DEMO100-Raw Material 077','Dairy','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,'DEMO100-Raw Material 078','Dry Goods','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,'DEMO100-Raw Material 079','Meat','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,'DEMO100-Raw Material 080','Produce','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,'DEMO100-Raw Material 081','Dairy','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,'DEMO100-Raw Material 082','Dry Goods','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,'DEMO100-Raw Material 083','Meat','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,'DEMO100-Raw Material 084','Produce','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,'DEMO100-Raw Material 085','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,'DEMO100-Raw Material 086','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,'DEMO100-Raw Material 087','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,'DEMO100-Raw Material 088','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,'DEMO100-Raw Material 089','Dairy','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,'DEMO100-Raw Material 090','Dry Goods','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,'DEMO100-Raw Material 091','Meat','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,'DEMO100-Raw Material 092','Produce','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,'DEMO100-Raw Material 093','Dairy','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,'DEMO100-Raw Material 094','Dry Goods','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,'DEMO100-Raw Material 095','Meat','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(103,'DEMO100-Raw Material 096','Produce','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(104,'DEMO100-Raw Material 097','Dairy','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(105,'DEMO100-Raw Material 098','Dry Goods','piece',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(106,'DEMO100-Raw Material 099','Meat','kg',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(107,'DEMO100-Raw Material 100','Produce','litre',5.0000,2.0000,1,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(108,'Milk','Dairy','litre',0.0000,0.0000,0,1,1,'2026-10-26 05:16:38','2026-10-26 05:22:19','2026-10-26 05:22:19'),(109,'Milk','Dairy','litre',0.0000,0.0000,1,1,1,'2026-10-26 05:22:48','2026-10-26 05:44:43',NULL),(110,'Meat','Meat','kg',3.0000,3.0000,0,1,1,'2026-10-26 05:28:50','2026-10-26 05:36:56','2026-10-26 05:36:56'),(111,'Lolipop','Dairy','kg',0.0000,0.0000,1,1,1,'2026-10-26 05:45:24','2026-10-26 05:45:24',NULL);
/*!40000 ALTER TABLE `raw_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_mappings`
--

DROP TABLE IF EXISTS `recipe_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recipe_mappings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `variant_id` bigint(20) unsigned DEFAULT NULL,
  `add_on_id` bigint(20) unsigned DEFAULT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recipe_mapping_menu_variant_material_unique` (`menu_item_id`,`variant_id`,`raw_material_id`),
  UNIQUE KEY `recipe_mapping_addon_material_unique` (`add_on_id`,`raw_material_id`),
  KEY `recipe_mappings_created_by_foreign` (`created_by`),
  KEY `recipe_mappings_updated_by_foreign` (`updated_by`),
  KEY `recipe_mapping_menu_item_index` (`menu_item_id`),
  KEY `recipe_mapping_variant_index` (`variant_id`),
  KEY `recipe_mapping_addon_index` (`add_on_id`),
  KEY `recipe_mapping_raw_material_index` (`raw_material_id`),
  CONSTRAINT `recipe_mapping_addon_fk` FOREIGN KEY (`add_on_id`) REFERENCES `add_ons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recipe_mapping_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `menu_item_variants` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `recipe_mappings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recipe_mappings_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recipe_mappings_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `recipe_mappings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recipe_mapping_target_check` CHECK (`menu_item_id` is not null and `add_on_id` is null or `menu_item_id` is null and `add_on_id` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_mappings`
--

LOCK TABLES `recipe_mappings` WRITE;
/*!40000 ALTER TABLE `recipe_mappings` DISABLE KEYS */;
INSERT INTO `recipe_mappings` VALUES (1,1,NULL,NULL,2,1.0000,'pcs',NULL,1,1,'2026-10-06 20:08:54','2026-10-06 20:08:54'),(3,2,NULL,NULL,7,100.0000,'kg',NULL,1,1,'2026-10-26 05:01:10','2026-10-26 05:01:10');
/*!40000 ALTER TABLE `recipe_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_tables`
--

DROP TABLE IF EXISTS `reservation_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `restaurant_table_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_tables_reservation_id_restaurant_table_id_unique` (`reservation_id`,`restaurant_table_id`),
  KEY `reservation_tables_reservation_id_is_primary_index` (`reservation_id`,`is_primary`),
  KEY `reservation_tables_restaurant_table_id_index` (`restaurant_table_id`),
  CONSTRAINT `reservation_tables_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_tables_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_tables`
--

LOCK TABLES `reservation_tables` WRITE;
/*!40000 ALTER TABLE `reservation_tables` DISABLE KEYS */;
INSERT INTO `reservation_tables` VALUES (2,2,1,0,'2026-10-06 22:30:14','2026-10-06 22:30:14'),(3,3,2,0,'2026-10-08 01:15:13','2026-10-08 01:15:13'),(5,4,3,0,'2026-10-08 01:32:35','2026-10-08 01:32:35'),(6,5,4,0,'2026-10-11 04:38:17','2026-10-11 04:38:17'),(9,6,4,0,'2026-10-13 09:09:21','2026-10-13 09:09:21'),(10,7,4,0,'2026-10-13 23:19:10','2026-10-13 23:19:10'),(11,8,6,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(12,9,7,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(13,10,8,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(14,11,9,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(15,12,10,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(16,13,11,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(17,14,12,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(18,15,13,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(19,16,14,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(20,17,15,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(21,18,16,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(22,19,17,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(23,20,18,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(24,21,19,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(25,22,20,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(26,23,21,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(27,24,22,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(28,25,23,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(29,26,24,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(30,27,25,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(31,28,26,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(32,29,27,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(33,30,28,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(34,31,29,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(35,32,30,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(36,33,31,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(37,34,32,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(38,35,33,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(39,36,34,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(40,37,35,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(41,38,36,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(42,39,37,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(43,40,38,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(44,41,39,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(45,42,40,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(46,43,41,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(47,44,42,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(48,45,43,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(49,46,44,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(50,47,45,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(51,48,46,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(52,49,47,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(53,50,48,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(54,51,49,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(55,52,50,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(56,53,51,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(57,54,52,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(58,55,53,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(59,56,54,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(60,57,55,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(61,58,56,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(62,59,57,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(63,60,58,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(64,61,59,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(65,62,60,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(66,63,61,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(67,64,62,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(68,65,63,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(69,66,64,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(70,67,65,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(71,68,66,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(72,69,67,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(73,70,68,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(74,71,69,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(75,72,70,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(76,73,71,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(77,74,72,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(78,75,73,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(79,76,74,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(80,77,75,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(81,78,76,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(82,79,77,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(83,80,78,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(84,81,79,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(85,82,80,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(86,83,81,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(87,84,82,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(88,85,83,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(89,86,84,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(90,87,85,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(91,88,86,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(92,89,87,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(93,90,88,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(94,91,89,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(95,92,90,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(96,93,91,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(97,94,92,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(98,95,93,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(99,96,94,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(100,97,95,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(101,98,96,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(102,99,97,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(103,100,98,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(104,101,99,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(105,102,100,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(106,103,101,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(107,104,102,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(108,105,103,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(109,106,104,1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(110,107,105,1,'2026-10-26 04:43:58','2026-10-26 04:43:58');
/*!40000 ALTER TABLE `reservation_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_no` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `guest_count` int(10) unsigned NOT NULL,
  `advance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('reserved','confirmed','seated','completed','cancelled') NOT NULL DEFAULT 'reserved',
  `note` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservations_reservation_no_unique` (`reservation_no`),
  KEY `reservations_customer_id_foreign` (`customer_id`),
  KEY `reservations_created_by_foreign` (`created_by`),
  KEY `reservations_reservation_date_status_index` (`reservation_date`,`status`),
  KEY `reservations_start_time_end_time_index` (`start_time`,`end_time`),
  CONSTRAINT `reservations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
INSERT INTO `reservations` VALUES (1,'RES-20261007-B5Z3ZD9O',1,'2026-10-08','2026-10-08 11:30:00','2026-10-08 13:29:00',2,40.00,'cancelled',NULL,1,'2026-10-06 22:27:09','2026-10-06 22:29:22',NULL),(2,'RES-20261007-J9QK7P3O',2,'2026-10-08','2026-10-08 00:31:00','2026-10-08 13:32:00',1,0.00,'completed',NULL,1,'2026-10-06 22:30:14','2026-10-08 03:56:30',NULL),(3,'RES-20261008-LL0MPJAO',4,'2026-10-08','2026-10-08 18:38:00','2026-10-08 20:26:00',3,82.00,'seated','Tempora consequuntur ea eum qui excepturi in voluptatem Qui aliquid sequi asperiores distinctio',1,'2026-10-08 01:15:13','2026-10-08 01:19:07',NULL),(4,'RES-20261008-V8QLY8CR',5,'2026-10-08','2026-10-08 13:32:00','2026-10-08 15:24:00',3,9.00,'seated','Magna do omnis voluptas non quos quos',1,'2026-10-08 01:32:35','2026-10-08 01:32:47',NULL),(5,'RES-20261011-OL0OTW6P',6,'2026-10-11','2026-10-11 18:17:00','2026-10-11 19:03:00',3,3.00,'completed','Possimus et perspiciatis duis tempora beatae',1,'2026-10-11 04:38:17','2026-10-14 21:48:27',NULL),(6,'RES-20261013-IABBV6CB',1,'2026-10-13','2026-10-13 22:09:00','2026-10-13 23:10:00',3,0.00,'completed',NULL,1,'2026-10-13 09:08:44','2026-10-13 09:11:59',NULL),(7,'RES-20261014-DIH80WSX',1,'2026-10-14','2026-10-14 11:29:00','2026-10-14 11:59:00',4,0.00,'completed',NULL,1,'2026-10-13 23:19:10','2026-10-24 23:05:18',NULL),(8,'DEMO100-RES-001',7,'2026-10-26','2026-10-26 13:30:00','2026-10-26 15:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,'DEMO100-RES-002',8,'2026-10-27','2026-10-27 14:00:00','2026-10-27 16:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,'DEMO100-RES-003',9,'2026-10-28','2026-10-28 15:30:00','2026-10-28 17:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,'DEMO100-RES-004',10,'2026-10-29','2026-10-29 16:00:00','2026-10-29 18:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,'DEMO100-RES-005',11,'2026-10-30','2026-10-30 17:30:00','2026-10-30 19:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,'DEMO100-RES-006',12,'2026-10-31','2026-10-31 18:00:00','2026-10-31 20:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,'DEMO100-RES-007',13,'2026-11-01','2026-11-01 19:30:00','2026-11-01 21:30:00',3,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,'DEMO100-RES-008',14,'2026-11-02','2026-11-02 12:00:00','2026-11-02 14:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,'DEMO100-RES-009',15,'2026-11-03','2026-11-03 13:30:00','2026-11-03 15:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,'DEMO100-RES-010',16,'2026-11-04','2026-11-04 14:00:00','2026-11-04 16:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,'DEMO100-RES-011',17,'2026-11-05','2026-11-05 15:30:00','2026-11-05 17:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,'DEMO100-RES-012',18,'2026-11-06','2026-11-06 16:00:00','2026-11-06 18:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,'DEMO100-RES-013',19,'2026-11-07','2026-11-07 17:30:00','2026-11-07 19:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,'DEMO100-RES-014',20,'2026-11-08','2026-11-08 18:00:00','2026-11-08 20:00:00',4,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,'DEMO100-RES-015',21,'2026-11-09','2026-11-09 19:30:00','2026-11-09 21:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,'DEMO100-RES-016',22,'2026-11-10','2026-11-10 12:00:00','2026-11-10 14:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,'DEMO100-RES-017',23,'2026-11-11','2026-11-11 13:30:00','2026-11-11 15:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,'DEMO100-RES-018',24,'2026-11-12','2026-11-12 14:00:00','2026-11-12 16:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,'DEMO100-RES-019',25,'2026-11-13','2026-11-13 15:30:00','2026-11-13 17:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,'DEMO100-RES-020',26,'2026-11-14','2026-11-14 16:00:00','2026-11-14 18:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,'DEMO100-RES-021',27,'2026-11-15','2026-11-15 17:30:00','2026-11-15 19:30:00',5,500.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,'DEMO100-RES-022',28,'2026-11-16','2026-11-16 18:00:00','2026-11-16 20:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,'DEMO100-RES-023',29,'2026-11-17','2026-11-17 19:30:00','2026-11-17 21:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,'DEMO100-RES-024',30,'2026-11-18','2026-11-18 12:00:00','2026-11-18 14:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,'DEMO100-RES-025',31,'2026-11-19','2026-11-19 13:30:00','2026-11-19 15:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,'DEMO100-RES-026',32,'2026-11-20','2026-11-20 14:00:00','2026-11-20 16:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,'DEMO100-RES-027',33,'2026-11-21','2026-11-21 15:30:00','2026-11-21 17:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,'DEMO100-RES-028',34,'2026-11-22','2026-11-22 16:00:00','2026-11-22 18:00:00',6,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,'DEMO100-RES-029',35,'2026-11-23','2026-11-23 17:30:00','2026-11-23 19:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,'DEMO100-RES-030',36,'2026-11-24','2026-11-24 18:00:00','2026-11-24 20:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,'DEMO100-RES-031',37,'2026-10-26','2026-10-26 19:30:00','2026-10-26 21:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,'DEMO100-RES-032',38,'2026-10-27','2026-10-27 12:00:00','2026-10-27 14:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,'DEMO100-RES-033',39,'2026-10-28','2026-10-28 13:30:00','2026-10-28 15:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,'DEMO100-RES-034',40,'2026-10-29','2026-10-29 14:00:00','2026-10-29 16:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,'DEMO100-RES-035',41,'2026-10-30','2026-10-30 15:30:00','2026-10-30 17:30:00',7,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,'DEMO100-RES-036',42,'2026-10-31','2026-10-31 16:00:00','2026-10-31 18:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,'DEMO100-RES-037',43,'2026-11-01','2026-11-01 17:30:00','2026-11-01 19:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,'DEMO100-RES-038',44,'2026-11-02','2026-11-02 18:00:00','2026-11-02 20:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,'DEMO100-RES-039',45,'2026-11-03','2026-11-03 19:30:00','2026-11-03 21:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,'DEMO100-RES-040',46,'2026-11-04','2026-11-04 12:00:00','2026-11-04 14:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,'DEMO100-RES-041',47,'2026-11-05','2026-11-05 13:30:00','2026-11-05 15:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,'DEMO100-RES-042',48,'2026-11-06','2026-11-06 14:00:00','2026-11-06 16:00:00',2,500.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,'DEMO100-RES-043',49,'2026-11-07','2026-11-07 15:30:00','2026-11-07 17:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,'DEMO100-RES-044',50,'2026-11-08','2026-11-08 16:00:00','2026-11-08 18:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,'DEMO100-RES-045',51,'2026-11-09','2026-11-09 17:30:00','2026-11-09 19:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,'DEMO100-RES-046',52,'2026-11-10','2026-11-10 18:00:00','2026-11-10 20:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,'DEMO100-RES-047',53,'2026-11-11','2026-11-11 19:30:00','2026-11-11 21:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,'DEMO100-RES-048',54,'2026-11-12','2026-11-12 12:00:00','2026-11-12 14:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,'DEMO100-RES-049',55,'2026-11-13','2026-11-13 13:30:00','2026-11-13 15:30:00',3,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,'DEMO100-RES-050',56,'2026-11-14','2026-11-14 14:00:00','2026-11-14 16:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,'DEMO100-RES-051',57,'2026-11-15','2026-11-15 15:30:00','2026-11-15 17:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,'DEMO100-RES-052',58,'2026-11-16','2026-11-16 16:00:00','2026-11-16 18:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,'DEMO100-RES-053',59,'2026-11-17','2026-11-17 17:30:00','2026-11-17 19:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,'DEMO100-RES-054',60,'2026-11-18','2026-11-18 18:00:00','2026-11-18 20:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,'DEMO100-RES-055',61,'2026-11-19','2026-11-19 19:30:00','2026-11-19 21:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,'DEMO100-RES-056',62,'2026-11-20','2026-11-20 12:00:00','2026-11-20 14:00:00',4,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,'DEMO100-RES-057',63,'2026-11-21','2026-11-21 13:30:00','2026-11-21 15:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,'DEMO100-RES-058',64,'2026-11-22','2026-11-22 14:00:00','2026-11-22 16:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,'DEMO100-RES-059',65,'2026-11-23','2026-11-23 15:30:00','2026-11-23 17:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,'DEMO100-RES-060',66,'2026-11-24','2026-11-24 16:00:00','2026-11-24 18:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,'DEMO100-RES-061',67,'2026-10-26','2026-10-26 17:30:00','2026-10-26 19:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,'DEMO100-RES-062',68,'2026-10-27','2026-10-27 18:00:00','2026-10-27 20:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,'DEMO100-RES-063',69,'2026-10-28','2026-10-28 19:30:00','2026-10-28 21:30:00',5,500.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,'DEMO100-RES-064',70,'2026-10-29','2026-10-29 12:00:00','2026-10-29 14:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,'DEMO100-RES-065',71,'2026-10-30','2026-10-30 13:30:00','2026-10-30 15:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,'DEMO100-RES-066',72,'2026-10-31','2026-10-31 14:00:00','2026-10-31 16:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,'DEMO100-RES-067',73,'2026-11-01','2026-11-01 15:30:00','2026-11-01 17:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,'DEMO100-RES-068',74,'2026-11-02','2026-11-02 16:00:00','2026-11-02 18:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,'DEMO100-RES-069',75,'2026-11-03','2026-11-03 17:30:00','2026-11-03 19:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,'DEMO100-RES-070',76,'2026-11-04','2026-11-04 18:00:00','2026-11-04 20:00:00',6,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,'DEMO100-RES-071',77,'2026-11-05','2026-11-05 19:30:00','2026-11-05 21:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,'DEMO100-RES-072',78,'2026-11-06','2026-11-06 12:00:00','2026-11-06 14:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,'DEMO100-RES-073',79,'2026-11-07','2026-11-07 13:30:00','2026-11-07 15:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,'DEMO100-RES-074',80,'2026-11-08','2026-11-08 14:00:00','2026-11-08 16:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,'DEMO100-RES-075',81,'2026-11-09','2026-11-09 15:30:00','2026-11-09 17:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,'DEMO100-RES-076',82,'2026-11-10','2026-11-10 16:00:00','2026-11-10 18:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,'DEMO100-RES-077',83,'2026-11-11','2026-11-11 17:30:00','2026-11-11 19:30:00',7,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,'DEMO100-RES-078',84,'2026-11-12','2026-11-12 18:00:00','2026-11-12 20:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,'DEMO100-RES-079',85,'2026-11-13','2026-11-13 19:30:00','2026-11-13 21:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,'DEMO100-RES-080',86,'2026-11-14','2026-11-14 12:00:00','2026-11-14 14:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,'DEMO100-RES-081',87,'2026-11-15','2026-11-15 13:30:00','2026-11-15 15:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,'DEMO100-RES-082',88,'2026-11-16','2026-11-16 14:00:00','2026-11-16 16:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,'DEMO100-RES-083',89,'2026-11-17','2026-11-17 15:30:00','2026-11-17 17:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,'DEMO100-RES-084',90,'2026-11-18','2026-11-18 16:00:00','2026-11-18 18:00:00',2,500.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,'DEMO100-RES-085',91,'2026-11-19','2026-11-19 17:30:00','2026-11-19 19:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,'DEMO100-RES-086',92,'2026-11-20','2026-11-20 18:00:00','2026-11-20 20:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,'DEMO100-RES-087',93,'2026-11-21','2026-11-21 19:30:00','2026-11-21 21:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,'DEMO100-RES-088',94,'2026-11-22','2026-11-22 12:00:00','2026-11-22 14:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,'DEMO100-RES-089',95,'2026-11-23','2026-11-23 13:30:00','2026-11-23 15:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,'DEMO100-RES-090',96,'2026-11-24','2026-11-24 14:00:00','2026-11-24 16:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,'DEMO100-RES-091',97,'2026-10-26','2026-10-26 15:30:00','2026-10-26 17:30:00',3,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,'DEMO100-RES-092',98,'2026-10-27','2026-10-27 16:00:00','2026-10-27 18:00:00',4,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,'DEMO100-RES-093',99,'2026-10-28','2026-10-28 17:30:00','2026-10-28 19:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,'DEMO100-RES-094',100,'2026-10-29','2026-10-29 18:00:00','2026-10-29 20:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,'DEMO100-RES-095',101,'2026-10-30','2026-10-30 19:30:00','2026-10-30 21:30:00',7,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(103,'DEMO100-RES-096',102,'2026-10-31','2026-10-31 12:00:00','2026-10-31 14:00:00',2,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(104,'DEMO100-RES-097',103,'2026-11-01','2026-11-01 13:30:00','2026-11-01 15:30:00',3,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(105,'DEMO100-RES-098',104,'2026-11-02','2026-11-02 14:00:00','2026-11-02 16:00:00',4,0.00,'confirmed','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(106,'DEMO100-RES-099',105,'2026-11-03','2026-11-03 15:30:00','2026-11-03 17:30:00',5,500.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(107,'DEMO100-RES-100',106,'2026-11-04','2026-11-04 16:00:00','2026-11-04 18:00:00',6,0.00,'reserved','DEMO DATA: test reservation.',1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_stocks`
--

DROP TABLE IF EXISTS `restaurant_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `average_unit_cost` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `last_received_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_stocks_raw_material_unique` (`raw_material_id`),
  KEY `restaurant_stocks_created_by_foreign` (`created_by`),
  KEY `restaurant_stocks_updated_by_foreign` (`updated_by`),
  CONSTRAINT `restaurant_stocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_stocks_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `restaurant_stocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_stocks`
--

LOCK TABLES `restaurant_stocks` WRITE;
/*!40000 ALTER TABLE `restaurant_stocks` DISABLE KEYS */;
INSERT INTO `restaurant_stocks` VALUES (1,2,0.0000,1.0000,'2026-10-06 20:05:32',1,2,'2026-10-06 20:05:32','2026-10-26 02:24:29',NULL),(2,5,100.0000,1.0000,'2026-10-11 00:26:57',1,1,'2026-10-11 00:26:57','2026-10-11 00:26:57',NULL),(3,4,120.0000,1.0000,'2026-10-11 00:26:57',1,1,'2026-10-11 00:26:58','2026-10-11 00:26:58',NULL),(4,6,100.0000,1.0000,'2026-10-13 01:28:05',1,1,'2026-10-13 01:28:05','2026-10-13 01:28:05',NULL),(5,7,110.0000,1.0000,'2026-10-26 04:52:56',1,2,'2026-10-26 02:14:44','2026-10-26 05:02:22',NULL),(6,8,4.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(7,9,5.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,10,6.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,11,7.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,12,8.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,13,9.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,14,10.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,15,11.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,16,12.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,17,13.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,18,14.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,19,15.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,20,16.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,21,17.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,22,18.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,23,19.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,24,20.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,25,21.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,26,22.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,27,23.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,28,24.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,29,25.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,30,26.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,31,27.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,32,28.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,33,4.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,34,5.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,35,6.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,36,7.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,37,8.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,38,9.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,39,10.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,40,11.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,41,12.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,42,13.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,43,14.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,44,15.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,45,16.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,46,17.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,47,18.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,48,19.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,49,20.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,50,21.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,51,22.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,52,23.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,53,24.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,54,25.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,55,26.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,56,27.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,57,28.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,58,4.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,59,5.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,60,6.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,61,7.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,62,8.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,63,9.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,64,10.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,65,11.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,66,12.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,67,13.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,68,14.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,69,15.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,70,16.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,71,17.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,72,18.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,73,19.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,74,20.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,75,21.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,76,22.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,77,23.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,78,24.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,79,25.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,80,26.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,81,27.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,82,28.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,83,4.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,84,5.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,85,6.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,86,7.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,87,8.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,88,9.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,89,10.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,90,11.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,91,12.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,92,13.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,93,14.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,94,15.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,95,16.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,96,17.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,97,18.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,98,19.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,99,20.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,100,21.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,101,22.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,102,23.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,103,24.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,104,25.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(103,105,26.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(104,106,27.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(105,107,28.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `restaurant_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_tables`
--

DROP TABLE IF EXISTS `restaurant_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `capacity` int(10) unsigned NOT NULL DEFAULT 1,
  `section` enum('ac','non_ac','outdoor') NOT NULL,
  `status` enum('available','occupied','reserved','cleaning') NOT NULL DEFAULT 'available',
  `reservation_start_at` datetime DEFAULT NULL,
  `reservation_end_at` datetime DEFAULT NULL,
  `merged_with_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_tables_name_deleted_unique` (`table_name`,`deleted_at`),
  KEY `restaurant_tables_merged_with_id_foreign` (`merged_with_id`),
  KEY `restaurant_tables_section_status_index` (`section`,`status`),
  KEY `restaurant_tables_section_index` (`section`),
  KEY `restaurant_tables_status_index` (`status`),
  CONSTRAINT `restaurant_tables_merged_with_id_foreign` FOREIGN KEY (`merged_with_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_tables`
--

LOCK TABLES `restaurant_tables` WRITE;
/*!40000 ALTER TABLE `restaurant_tables` DISABLE KEYS */;
INSERT INTO `restaurant_tables` VALUES (1,'Table 01',10,'ac','occupied',NULL,NULL,4,NULL,'2026-10-06 22:11:34','2026-10-24 23:16:58',NULL),(2,'Table 02',6,'outdoor','occupied',NULL,NULL,NULL,NULL,'2026-10-06 22:11:55','2026-10-14 22:09:05',NULL),(3,'table 3',6,'non_ac','occupied','2026-10-08 13:32:00','2026-10-08 15:24:00',NULL,NULL,'2026-10-08 01:23:06','2026-10-13 09:09:21',NULL),(4,'table 4',8,'ac','occupied',NULL,NULL,NULL,NULL,'2026-10-08 01:40:04','2026-10-24 23:16:58',NULL),(5,'table 6',8,'outdoor','available',NULL,NULL,NULL,NULL,'2026-10-14 21:42:51','2026-10-24 23:11:02',NULL),(6,'DEMO100-TABLE-001',3,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(7,'DEMO100-TABLE-002',4,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,'DEMO100-TABLE-003',5,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,'DEMO100-TABLE-004',6,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,'DEMO100-TABLE-005',7,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,'DEMO100-TABLE-006',8,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,'DEMO100-TABLE-007',2,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,'DEMO100-TABLE-008',3,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,'DEMO100-TABLE-009',4,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,'DEMO100-TABLE-010',5,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,'DEMO100-TABLE-011',6,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,'DEMO100-TABLE-012',7,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,'DEMO100-TABLE-013',8,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,'DEMO100-TABLE-014',2,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,'DEMO100-TABLE-015',3,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,'DEMO100-TABLE-016',4,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,'DEMO100-TABLE-017',5,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,'DEMO100-TABLE-018',6,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,'DEMO100-TABLE-019',7,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,'DEMO100-TABLE-020',8,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,'DEMO100-TABLE-021',2,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,'DEMO100-TABLE-022',3,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,'DEMO100-TABLE-023',4,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,'DEMO100-TABLE-024',5,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,'DEMO100-TABLE-025',6,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,'DEMO100-TABLE-026',7,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,'DEMO100-TABLE-027',8,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,'DEMO100-TABLE-028',2,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,'DEMO100-TABLE-029',3,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,'DEMO100-TABLE-030',4,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,'DEMO100-TABLE-031',5,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,'DEMO100-TABLE-032',6,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,'DEMO100-TABLE-033',7,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,'DEMO100-TABLE-034',8,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,'DEMO100-TABLE-035',2,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,'DEMO100-TABLE-036',3,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,'DEMO100-TABLE-037',4,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,'DEMO100-TABLE-038',5,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,'DEMO100-TABLE-039',6,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,'DEMO100-TABLE-040',7,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,'DEMO100-TABLE-041',8,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,'DEMO100-TABLE-042',2,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,'DEMO100-TABLE-043',3,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,'DEMO100-TABLE-044',4,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,'DEMO100-TABLE-045',5,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,'DEMO100-TABLE-046',6,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,'DEMO100-TABLE-047',7,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,'DEMO100-TABLE-048',8,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,'DEMO100-TABLE-049',2,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,'DEMO100-TABLE-050',3,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,'DEMO100-TABLE-051',4,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,'DEMO100-TABLE-052',5,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,'DEMO100-TABLE-053',6,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,'DEMO100-TABLE-054',7,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,'DEMO100-TABLE-055',8,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,'DEMO100-TABLE-056',2,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,'DEMO100-TABLE-057',3,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,'DEMO100-TABLE-058',4,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,'DEMO100-TABLE-059',5,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,'DEMO100-TABLE-060',6,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,'DEMO100-TABLE-061',7,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,'DEMO100-TABLE-062',8,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,'DEMO100-TABLE-063',2,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,'DEMO100-TABLE-064',3,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,'DEMO100-TABLE-065',4,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,'DEMO100-TABLE-066',5,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,'DEMO100-TABLE-067',6,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,'DEMO100-TABLE-068',7,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,'DEMO100-TABLE-069',8,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,'DEMO100-TABLE-070',2,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,'DEMO100-TABLE-071',3,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,'DEMO100-TABLE-072',4,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,'DEMO100-TABLE-073',5,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,'DEMO100-TABLE-074',6,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,'DEMO100-TABLE-075',7,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,'DEMO100-TABLE-076',8,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,'DEMO100-TABLE-077',2,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,'DEMO100-TABLE-078',3,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,'DEMO100-TABLE-079',4,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,'DEMO100-TABLE-080',5,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,'DEMO100-TABLE-081',6,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,'DEMO100-TABLE-082',7,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,'DEMO100-TABLE-083',8,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,'DEMO100-TABLE-084',2,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,'DEMO100-TABLE-085',3,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,'DEMO100-TABLE-086',4,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,'DEMO100-TABLE-087',5,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,'DEMO100-TABLE-088',6,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,'DEMO100-TABLE-089',7,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,'DEMO100-TABLE-090',8,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,'DEMO100-TABLE-091',2,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,'DEMO100-TABLE-092',3,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,'DEMO100-TABLE-093',4,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,'DEMO100-TABLE-094',5,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,'DEMO100-TABLE-095',6,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,'DEMO100-TABLE-096',7,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,'DEMO100-TABLE-097',8,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(103,'DEMO100-TABLE-098',2,'outdoor','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(104,'DEMO100-TABLE-099',3,'ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(105,'DEMO100-TABLE-100',4,'non_ac','available',NULL,NULL,NULL,'DEMO DATA','2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `restaurant_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  KEY `roles_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Administrator','Full access to the restaurant management system.',1,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(2,'manager','Manager','Manage restaurant operations, staff, inventory and reports.',1,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(3,'chef','Chef','Kitchen and food preparation management access.',1,'2026-10-06 13:10:41','2026-10-06 13:10:41'),(4,'waiter','Waiter','Order taking and front desk operational access.',1,'2026-10-06 13:10:41','2026-10-06 13:10:41');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_details`
--

DROP TABLE IF EXISTS `salary_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `salary_payroll_id` bigint(20) unsigned NOT NULL,
  `attendance_id` bigint(20) unsigned DEFAULT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `salary_date` date NOT NULL,
  `scheduled_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `worked_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `late_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `break_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `overtime_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `salary_type` varchar(50) NOT NULL DEFAULT 'no_salary',
  `regular_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `overtime_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `calculation_source` varchar(20) NOT NULL DEFAULT 'automatic',
  `notes` text DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_details_attendance_id_foreign` (`attendance_id`),
  KEY `salary_details_updated_by_foreign` (`updated_by`),
  KEY `salary_detail_payroll_date_index` (`salary_payroll_id`,`salary_date`),
  KEY `salary_detail_employee_date_index` (`employee_id`,`salary_date`),
  KEY `salary_detail_type_date_index` (`salary_type`,`salary_date`),
  CONSTRAINT `salary_details_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `salary_details_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `salary_details_salary_payroll_id_foreign` FOREIGN KEY (`salary_payroll_id`) REFERENCES `salary_payrolls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `salary_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_details`
--

LOCK TABLES `salary_details` WRITE;
/*!40000 ALTER TABLE `salary_details` DISABLE KEYS */;
INSERT INTO `salary_details` VALUES (1,1,7,1,'2026-10-07',240,0,0,0,0,100.00,'no_salary',0.00,0.00,0.00,'automatic',NULL,1,'2026-10-07 01:02:00','2026-10-07 01:02:00'),(2,2,8,2,'2026-10-07',480,0,0,0,0,60.00,'full_salary',0.00,0.00,0.00,'manual',NULL,1,'2026-10-07 01:02:00','2026-10-07 01:02:24'),(3,3,15,3,'2026-10-25',480,479,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 001',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(4,4,16,4,'2026-10-24',480,478,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 002',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(5,5,17,5,'2026-10-23',480,477,0,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 003',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(6,6,18,6,'2026-10-22',480,476,0,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 004',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(7,7,19,7,'2026-10-21',480,475,0,30,30,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 005',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(8,8,20,8,'2026-10-20',480,474,0,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 006',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(9,9,21,9,'2026-10-19',480,473,0,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 007',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(10,10,22,10,'2026-10-18',480,472,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 008',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(11,11,23,11,'2026-10-17',480,471,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 009',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(12,12,24,12,'2026-10-16',480,470,0,30,30,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 010',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(13,13,25,13,'2026-10-15',480,469,1,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 011',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(14,14,26,14,'2026-10-14',480,468,2,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 012',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(15,15,27,15,'2026-10-13',480,467,3,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 013',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(16,16,28,16,'2026-10-12',480,466,4,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 014',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(17,17,29,17,'2026-10-11',480,465,5,30,30,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 015',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(18,18,30,18,'2026-10-10',480,480,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 016',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(19,19,31,19,'2026-10-09',480,479,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 017',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(20,20,32,20,'2026-10-08',480,478,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 018',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(21,21,33,21,'2026-10-07',480,477,0,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 019',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(22,22,34,22,'2026-10-06',480,476,0,30,30,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 020',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(23,23,35,23,'2026-10-05',480,475,0,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 021',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(24,24,36,24,'2026-10-04',480,474,0,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 022',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(25,25,37,25,'2026-10-03',480,473,0,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 023',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(26,26,38,26,'2026-10-02',480,472,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 024',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(27,27,39,27,'2026-10-01',480,471,0,30,30,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 025',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(28,28,40,28,'2026-09-30',480,470,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 026',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(29,29,41,29,'2026-09-29',480,469,1,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 027',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(30,30,42,30,'2026-09-28',480,468,2,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 028',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(31,31,43,31,'2026-09-27',480,467,3,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 029',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(32,32,44,32,'2026-10-26',480,466,4,30,30,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 030',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(33,33,45,33,'2026-10-25',480,465,5,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 031',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(34,34,46,34,'2026-10-24',480,480,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 032',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(35,35,47,35,'2026-10-23',480,479,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 033',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(36,36,48,36,'2026-10-22',480,478,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 034',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(37,37,49,37,'2026-10-21',480,477,0,30,30,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 035',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(38,38,50,38,'2026-10-20',480,476,0,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 036',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(39,39,51,39,'2026-10-19',480,475,0,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 037',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(40,40,52,40,'2026-10-18',480,474,0,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 038',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(41,41,53,41,'2026-10-17',480,473,0,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 039',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(42,42,54,42,'2026-10-16',480,472,0,30,30,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 040',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(43,43,55,43,'2026-10-15',480,471,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 041',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(44,44,56,44,'2026-10-14',480,470,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 042',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(45,45,57,45,'2026-10-13',480,469,1,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 043',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(46,46,58,46,'2026-10-12',480,468,2,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 044',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(47,47,59,47,'2026-10-11',480,467,3,30,30,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 045',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(48,48,60,48,'2026-10-10',480,466,4,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 046',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(49,49,61,49,'2026-10-09',480,465,5,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 047',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(50,50,62,50,'2026-10-08',480,480,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 048',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(51,51,63,51,'2026-10-07',480,479,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 049',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(52,52,64,52,'2026-10-06',480,478,0,30,30,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 050',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(53,53,65,53,'2026-10-05',480,477,0,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 051',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(54,54,66,54,'2026-10-04',480,476,0,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 052',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(55,55,67,55,'2026-10-03',480,475,0,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 053',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(56,56,68,56,'2026-10-02',480,474,0,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 054',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(57,57,69,57,'2026-10-01',480,473,0,30,30,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 055',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(58,58,70,58,'2026-09-30',480,472,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 056',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(59,59,71,59,'2026-09-29',480,471,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 057',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(60,60,72,60,'2026-09-28',480,470,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 058',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(61,61,73,61,'2026-09-27',480,469,1,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 059',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(62,62,74,62,'2026-10-26',480,468,2,30,30,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 060',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(63,63,75,63,'2026-10-25',480,467,3,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 061',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(64,64,76,64,'2026-10-24',480,466,4,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 062',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(65,65,77,65,'2026-10-23',480,465,5,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 063',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(66,66,78,66,'2026-10-22',480,480,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 064',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(67,67,79,67,'2026-10-21',480,479,0,30,30,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 065',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(68,68,80,68,'2026-10-20',480,478,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 066',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(69,69,81,69,'2026-10-19',480,477,0,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 067',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(70,70,82,70,'2026-10-18',480,476,0,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 068',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(71,71,83,71,'2026-10-17',480,475,0,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 069',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(72,72,84,72,'2026-10-16',480,474,0,30,30,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 070',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(73,73,85,73,'2026-10-15',480,473,0,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 071',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(74,74,86,74,'2026-10-14',480,472,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 072',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(75,75,87,75,'2026-10-13',480,471,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 073',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(76,76,88,76,'2026-10-12',480,470,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 074',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(77,77,89,77,'2026-10-11',480,469,1,30,30,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 075',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(78,78,90,78,'2026-10-10',480,468,2,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 076',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(79,79,91,79,'2026-10-09',480,467,3,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 077',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(80,80,92,80,'2026-10-08',480,466,4,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 078',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(81,81,93,81,'2026-10-07',480,465,5,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 079',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(82,82,94,82,'2026-10-06',480,480,0,30,30,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 080',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(83,83,95,83,'2026-10-05',480,479,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 081',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(84,84,96,84,'2026-10-04',480,478,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 082',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(85,85,97,85,'2026-10-03',480,477,0,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 083',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(86,86,98,86,'2026-10-02',480,476,0,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 084',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(87,87,99,87,'2026-10-01',480,475,0,30,30,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 085',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(88,88,100,88,'2026-09-30',480,474,0,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 086',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(89,89,101,89,'2026-09-29',480,473,0,30,0,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 087',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(90,90,102,90,'2026-09-28',480,472,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 088',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(91,91,103,91,'2026-09-27',480,471,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 089',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(92,92,104,92,'2026-10-26',480,470,0,30,30,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 090',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(93,93,105,93,'2026-10-25',480,469,1,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 091',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(94,94,106,94,'2026-10-24',480,468,2,30,0,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 092',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(95,95,107,95,'2026-10-23',480,467,3,30,0,170.00,'hourly',1360.00,0.00,1360.00,'attendance','DEMO DATA salary detail 093',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(96,96,108,96,'2026-10-22',480,466,4,30,0,180.00,'hourly',1440.00,0.00,1440.00,'attendance','DEMO DATA salary detail 094',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(97,97,109,97,'2026-10-21',480,465,5,30,30,190.00,'hourly',1520.00,0.00,1520.00,'attendance','DEMO DATA salary detail 095',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(98,98,110,98,'2026-10-20',480,480,0,30,0,120.00,'hourly',960.00,0.00,960.00,'attendance','DEMO DATA salary detail 096',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(99,99,111,99,'2026-10-19',480,479,0,30,0,130.00,'hourly',1040.00,0.00,1040.00,'attendance','DEMO DATA salary detail 097',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(100,100,112,100,'2026-10-18',480,478,0,30,0,140.00,'hourly',1120.00,0.00,1120.00,'attendance','DEMO DATA salary detail 098',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(101,101,113,101,'2026-10-17',480,477,0,30,0,150.00,'hourly',1200.00,0.00,1200.00,'attendance','DEMO DATA salary detail 099',1,'2026-10-26 04:43:58','2026-10-26 04:43:58'),(102,102,114,102,'2026-10-16',480,476,0,30,30,160.00,'hourly',1280.00,0.00,1280.00,'attendance','DEMO DATA salary detail 100',1,'2026-10-26 04:43:58','2026-10-26 04:43:58');
/*!40000 ALTER TABLE `salary_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_payrolls`
--

DROP TABLE IF EXISTS `salary_payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_payrolls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `employee_name` varchar(255) NOT NULL,
  `employee_phone` varchar(255) DEFAULT NULL,
  `employee_email` varchar(255) DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `regular_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `overtime_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `adjustment_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `paid_at` datetime DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(150) DEFAULT NULL,
  `paid_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_payrolls_paid_by_foreign` (`paid_by`),
  KEY `salary_payrolls_created_by_foreign` (`created_by`),
  KEY `salary_payrolls_updated_by_foreign` (`updated_by`),
  KEY `salary_payroll_employee_period_index` (`employee_id`,`period_start`,`period_end`),
  KEY `salary_payroll_status_period_index` (`payment_status`,`period_start`,`period_end`),
  KEY `salary_payroll_employee_period_idx` (`employee_id`,`period_start`),
  CONSTRAINT `salary_payrolls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `salary_payrolls_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_payrolls_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_payrolls`
--

LOCK TABLES `salary_payrolls` WRITE;
/*!40000 ALTER TABLE `salary_payrolls` DISABLE KEYS */;
INSERT INTO `salary_payrolls` VALUES (1,1,'Chef','0138776655442','chef.sopno@example.com','2026-10-01','2026-10-07',100.00,0.00,0.00,0.00,0.00,'unpaid',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2026-10-07 01:02:00','2026-10-07 01:02:00',NULL),(2,2,'Waiter','0138776655443','waiter@gmail.com','2026-10-01','2026-10-07',60.00,0.00,0.00,0.00,0.00,'unpaid',NULL,NULL,NULL,NULL,NULL,NULL,1,1,'2026-10-07 01:02:00','2026-10-07 01:02:00',NULL),(3,3,'Demo Staff 001','8801801000001','demo-staff-001@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(4,4,'Demo Staff 002','8801801000002','demo-staff-002@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(5,5,'Demo Staff 003','8801801000003','demo-staff-003@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(6,6,'Demo Staff 004','8801801000004','demo-staff-004@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(7,7,'Demo Staff 005','8801801000005','demo-staff-005@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,8,'Demo Staff 006','8801801000006','demo-staff-006@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,9,'Demo Staff 007','8801801000007','demo-staff-007@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,10,'Demo Staff 008','8801801000008','demo-staff-008@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,11,'Demo Staff 009','8801801000009','demo-staff-009@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,12,'Demo Staff 010','8801801000010','demo-staff-010@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,13,'Demo Staff 011','8801801000011','demo-staff-011@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,14,'Demo Staff 012','8801801000012','demo-staff-012@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,15,'Demo Staff 013','8801801000013','demo-staff-013@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,16,'Demo Staff 014','8801801000014','demo-staff-014@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,17,'Demo Staff 015','8801801000015','demo-staff-015@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,18,'Demo Staff 016','8801801000016','demo-staff-016@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,19,'Demo Staff 017','8801801000017','demo-staff-017@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,20,'Demo Staff 018','8801801000018','demo-staff-018@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,21,'Demo Staff 019','8801801000019','demo-staff-019@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,22,'Demo Staff 020','8801801000020','demo-staff-020@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,23,'Demo Staff 021','8801801000021','demo-staff-021@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,24,'Demo Staff 022','8801801000022','demo-staff-022@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,25,'Demo Staff 023','8801801000023','demo-staff-023@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,26,'Demo Staff 024','8801801000024','demo-staff-024@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,27,'Demo Staff 025','8801801000025','demo-staff-025@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,28,'Demo Staff 026','8801801000026','demo-staff-026@local.test','2026-09-01','2026-09-30',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,29,'Demo Staff 027','8801801000027','demo-staff-027@local.test','2026-09-01','2026-09-30',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,30,'Demo Staff 028','8801801000028','demo-staff-028@local.test','2026-09-01','2026-09-30',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,31,'Demo Staff 029','8801801000029','demo-staff-029@local.test','2026-09-01','2026-09-30',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,32,'Demo Staff 030','8801801000030','demo-staff-030@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,33,'Demo Staff 031','8801801000031','demo-staff-031@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,34,'Demo Staff 032','8801801000032','demo-staff-032@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,35,'Demo Staff 033','8801801000033','demo-staff-033@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,36,'Demo Staff 034','8801801000034','demo-staff-034@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,37,'Demo Staff 035','8801801000035','demo-staff-035@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,38,'Demo Staff 036','8801801000036','demo-staff-036@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,39,'Demo Staff 037','8801801000037','demo-staff-037@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,40,'Demo Staff 038','8801801000038','demo-staff-038@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,41,'Demo Staff 039','8801801000039','demo-staff-039@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,42,'Demo Staff 040','8801801000040','demo-staff-040@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,43,'Demo Staff 041','8801801000041','demo-staff-041@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,44,'Demo Staff 042','8801801000042','demo-staff-042@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,45,'Demo Staff 043','8801801000043','demo-staff-043@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,46,'Demo Staff 044','8801801000044','demo-staff-044@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,47,'Demo Staff 045','8801801000045','demo-staff-045@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,48,'Demo Staff 046','8801801000046','demo-staff-046@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,49,'Demo Staff 047','8801801000047','demo-staff-047@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,50,'Demo Staff 048','8801801000048','demo-staff-048@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,51,'Demo Staff 049','8801801000049','demo-staff-049@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,52,'Demo Staff 050','8801801000050','demo-staff-050@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,53,'Demo Staff 051','8801801000051','demo-staff-051@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,54,'Demo Staff 052','8801801000052','demo-staff-052@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,55,'Demo Staff 053','8801801000053','demo-staff-053@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,56,'Demo Staff 054','8801801000054','demo-staff-054@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,57,'Demo Staff 055','8801801000055','demo-staff-055@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,58,'Demo Staff 056','8801801000056','demo-staff-056@local.test','2026-09-01','2026-09-30',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,59,'Demo Staff 057','8801801000057','demo-staff-057@local.test','2026-09-01','2026-09-30',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,60,'Demo Staff 058','8801801000058','demo-staff-058@local.test','2026-09-01','2026-09-30',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,61,'Demo Staff 059','8801801000059','demo-staff-059@local.test','2026-09-01','2026-09-30',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,62,'Demo Staff 060','8801801000060','demo-staff-060@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,63,'Demo Staff 061','8801801000061','demo-staff-061@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,64,'Demo Staff 062','8801801000062','demo-staff-062@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,65,'Demo Staff 063','8801801000063','demo-staff-063@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,66,'Demo Staff 064','8801801000064','demo-staff-064@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,67,'Demo Staff 065','8801801000065','demo-staff-065@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,68,'Demo Staff 066','8801801000066','demo-staff-066@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,69,'Demo Staff 067','8801801000067','demo-staff-067@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,70,'Demo Staff 068','8801801000068','demo-staff-068@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,71,'Demo Staff 069','8801801000069','demo-staff-069@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,72,'Demo Staff 070','8801801000070','demo-staff-070@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,73,'Demo Staff 071','8801801000071','demo-staff-071@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,74,'Demo Staff 072','8801801000072','demo-staff-072@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,75,'Demo Staff 073','8801801000073','demo-staff-073@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,76,'Demo Staff 074','8801801000074','demo-staff-074@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,77,'Demo Staff 075','8801801000075','demo-staff-075@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,78,'Demo Staff 076','8801801000076','demo-staff-076@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,79,'Demo Staff 077','8801801000077','demo-staff-077@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,80,'Demo Staff 078','8801801000078','demo-staff-078@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,81,'Demo Staff 079','8801801000079','demo-staff-079@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,82,'Demo Staff 080','8801801000080','demo-staff-080@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,83,'Demo Staff 081','8801801000081','demo-staff-081@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,84,'Demo Staff 082','8801801000082','demo-staff-082@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,85,'Demo Staff 083','8801801000083','demo-staff-083@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,86,'Demo Staff 084','8801801000084','demo-staff-084@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,87,'Demo Staff 085','8801801000085','demo-staff-085@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,88,'Demo Staff 086','8801801000086','demo-staff-086@local.test','2026-09-01','2026-09-30',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,89,'Demo Staff 087','8801801000087','demo-staff-087@local.test','2026-09-01','2026-09-30',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,90,'Demo Staff 088','8801801000088','demo-staff-088@local.test','2026-09-01','2026-09-30',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,91,'Demo Staff 089','8801801000089','demo-staff-089@local.test','2026-09-01','2026-09-30',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,92,'Demo Staff 090','8801801000090','demo-staff-090@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,93,'Demo Staff 091','8801801000091','demo-staff-091@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,94,'Demo Staff 092','8801801000092','demo-staff-092@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,95,'Demo Staff 093','8801801000093','demo-staff-093@local.test','2026-10-01','2026-10-31',170.00,1360.00,0.00,0.00,1360.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,96,'Demo Staff 094','8801801000094','demo-staff-094@local.test','2026-10-01','2026-10-31',180.00,1440.00,0.00,0.00,1440.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,97,'Demo Staff 095','8801801000095','demo-staff-095@local.test','2026-10-01','2026-10-31',190.00,1520.00,0.00,0.00,1520.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,98,'Demo Staff 096','8801801000096','demo-staff-096@local.test','2026-10-01','2026-10-31',120.00,960.00,0.00,0.00,960.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,99,'Demo Staff 097','8801801000097','demo-staff-097@local.test','2026-10-01','2026-10-31',130.00,1040.00,0.00,0.00,1040.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,100,'Demo Staff 098','8801801000098','demo-staff-098@local.test','2026-10-01','2026-10-31',140.00,1120.00,0.00,0.00,1120.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,101,'Demo Staff 099','8801801000099','demo-staff-099@local.test','2026-10-01','2026-10-31',150.00,1200.00,0.00,0.00,1200.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,102,'Demo Staff 100','8801801000100','demo-staff-100@local.test','2026-10-01','2026-10-31',160.00,1280.00,0.00,0.00,1280.00,'unpaid',NULL,NULL,NULL,NULL,NULL,'DEMO DATA payroll',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `salary_payrolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_schedule_overrides`
--

DROP TABLE IF EXISTS `shift_schedule_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_schedule_overrides` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `shift_schedule_id` bigint(20) unsigned NOT NULL,
  `override_date` date NOT NULL,
  `override_type` varchar(30) NOT NULL DEFAULT 'modified',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `grace_minutes` smallint(5) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_schedule_overrides_created_by_foreign` (`created_by`),
  KEY `shift_schedule_overrides_updated_by_foreign` (`updated_by`),
  KEY `shift_schedule_overrides_shift_schedule_id_override_date_index` (`shift_schedule_id`,`override_date`),
  KEY `shift_schedule_overrides_override_date_override_type_index` (`override_date`,`override_type`),
  CONSTRAINT `shift_schedule_overrides_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shift_schedule_overrides_shift_schedule_id_foreign` FOREIGN KEY (`shift_schedule_id`) REFERENCES `shift_schedules` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shift_schedule_overrides_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_schedule_overrides`
--

LOCK TABLES `shift_schedule_overrides` WRITE;
/*!40000 ALTER TABLE `shift_schedule_overrides` DISABLE KEYS */;
INSERT INTO `shift_schedule_overrides` VALUES (1,1,'2026-10-07','modified','13:00:00','17:00:00',60,NULL,1,1,'2026-10-07 00:58:34','2026-10-07 00:58:34',NULL);
/*!40000 ALTER TABLE `shift_schedule_overrides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_schedules`
--

DROP TABLE IF EXISTS `shift_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `working_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_days`)),
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_minutes` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_schedules_created_by_foreign` (`created_by`),
  KEY `shift_schedules_updated_by_foreign` (`updated_by`),
  KEY `shift_schedules_employee_id_start_date_end_date_index` (`employee_id`,`start_date`,`end_date`),
  KEY `shift_schedules_start_date_end_date_is_active_index` (`start_date`,`end_date`,`is_active`),
  CONSTRAINT `shift_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shift_schedules_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shift_schedules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_schedules`
--

LOCK TABLES `shift_schedules` WRITE;
/*!40000 ALTER TABLE `shift_schedules` DISABLE KEYS */;
INSERT INTO `shift_schedules` VALUES (1,1,'2026-10-07','2026-11-06','[\"sunday\",\"monday\",\"tuesday\",\"wednesday\",\"thursday\"]','09:00:00','17:00:00',60,1,NULL,1,1,'2026-10-07 00:55:51','2026-10-07 00:55:51',NULL),(2,2,'2026-10-07','2026-11-06','[\"sunday\",\"monday\",\"tuesday\",\"wednesday\",\"thursday\"]','09:00:00','17:00:00',60,1,NULL,1,1,'2026-10-07 00:56:12','2026-10-07 00:56:12',NULL);
/*!40000 ALTER TABLE `shift_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `location` varchar(30) NOT NULL,
  `movement_type` varchar(50) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `quantity_before` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `quantity_after` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `unit_cost` decimal(18,4) unsigned DEFAULT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `unit` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_created_by_foreign` (`created_by`),
  KEY `stock_movements_material_location_type_index` (`raw_material_id`,`location`,`movement_type`),
  KEY `stock_movements_reference_index` (`reference_type`,`reference_id`),
  KEY `stock_movements_material_date_index` (`raw_material_id`,`created_at`),
  KEY `stock_movements_location_index` (`location`),
  KEY `stock_movements_movement_type_index` (`movement_type`),
  KEY `stock_movements_reference_type_index` (`reference_type`),
  KEY `stock_movements_reference_id_index` (`reference_id`),
  CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,'warehouse','opening_stock',10.0000,0.0000,10.0000,1.0000,'App\\Models\\RawMaterial',1,'pcs','Initial warehouse opening stock.',1,'2026-10-06 19:26:35','2026-10-06 19:26:35'),(3,2,'warehouse','warehouse_adjustment_in',10.0000,0.0000,10.0000,1.0000,'App\\Models\\WarehouseStock',2,'pcs','Local test stock',1,'2026-10-06 20:02:30','2026-10-06 20:02:30'),(4,2,'warehouse','transfer_out',10.0000,10.0000,0.0000,1.0000,'App\\Models\\StockTransfer',2,'pcs','Transfer TRF-20261007-000002 | Warehouse to Restaurant | Item: Burger Test Batch | Item note: Local test recipe stock transfer',1,'2026-10-06 20:05:32','2026-10-06 20:05:32'),(5,2,'restaurant','transfer_in',10.0000,0.0000,10.0000,1.0000,'App\\Models\\StockTransfer',2,'pcs','Transfer TRF-20261007-000002 | Restaurant received from Warehouse | Item: Burger Test Batch | Item note: Local test recipe stock transfer',1,'2026-10-06 20:05:32','2026-10-06 20:05:32'),(6,2,'restaurant','recipe_consumption',1.0000,10.0000,9.0000,1.0000,'App\\Models\\OrderRecipeConsumption',1,'pcs','Recipe consumption | Order: ORD-20261007-HNDBAG | Kitchen Batch: #1 | Material: Burger Test Batch | Quantity: 1.0000 pcs',2,'2026-10-06 20:19:48','2026-10-06 20:19:48'),(8,3,'warehouse','opening_stock',50.0000,0.0000,50.0000,1.4000,'App\\Models\\RawMaterial',3,'kg','Initial warehouse opening stock.',1,'2026-10-06 22:53:10','2026-10-06 22:53:10'),(9,2,'restaurant','recipe_consumption',1.0000,9.0000,8.0000,1.0000,'App\\Models\\OrderRecipeConsumption',2,'pcs','Recipe consumption | Order: ORD-20261007-F8NBBO | Kitchen Batch: #1 | Material: Burger Test Batch | Quantity: 1.0000 pcs',2,'2026-10-06 23:11:38','2026-10-06 23:11:38'),(13,4,'warehouse','purchase_receive',20.0000,0.0000,20.0000,1.0000,'App\\Models\\PurchaseOrderReceipt',8,'kg','GRN: GRN-20261008-000008 | Purchase order #3 | Item: Chili | Warehouse lot: LOT-20261008-000013',1,'2026-10-08 03:01:58','2026-10-08 03:01:58'),(14,2,'restaurant','recipe_consumption',1.0000,8.0000,7.0000,1.0000,'App\\Models\\OrderRecipeConsumption',3,'pcs','Recipe consumption | Order: ORD-20261008-WGV5O2 | Kitchen Batch: #1 | Material: Burger Test Batch | Quantity: 1.0000 pcs',2,'2026-10-08 03:55:40','2026-10-08 03:55:40'),(15,4,'warehouse','purchase_receive',100.0000,20.0000,120.0000,1.0000,'App\\Models\\PurchaseOrderReceipt',9,'kg','GRN: GRN-20261011-000009 | Purchase order #4 | Item: Chili | Warehouse lot: LOT-20261011-000014',1,'2026-10-11 00:23:36','2026-10-11 00:23:36'),(17,5,'warehouse','purchase_receive',100.0000,0.0000,100.0000,1.0000,'App\\Models\\PurchaseOrderReceipt',13,'kg','GRN: GRN-20261011-000013 | Purchase order #7 | Item: Salt | Warehouse lot: LOT-20261011-000018',1,'2026-10-11 00:26:02','2026-10-11 00:26:02'),(18,5,'warehouse','transfer_out',100.0000,100.0000,0.0000,1.0000,'App\\Models\\StockTransfer',5,'kg','Transfer TRF-20261011-000005 | Warehouse to Restaurant | Item: Salt',1,'2026-10-11 00:26:57','2026-10-11 00:26:57'),(19,5,'restaurant','transfer_in',100.0000,0.0000,100.0000,1.0000,'App\\Models\\StockTransfer',5,'kg','Transfer TRF-20261011-000005 | Restaurant received from Warehouse | Item: Salt',1,'2026-10-11 00:26:57','2026-10-11 00:26:57'),(20,4,'warehouse','transfer_out',120.0000,120.0000,0.0000,1.0000,'App\\Models\\StockTransfer',5,'kg','Transfer TRF-20261011-000005 | Warehouse to Restaurant | Item: Chili',1,'2026-10-11 00:26:58','2026-10-11 00:26:58'),(21,4,'restaurant','transfer_in',120.0000,0.0000,120.0000,1.0000,'App\\Models\\StockTransfer',5,'kg','Transfer TRF-20261011-000005 | Restaurant received from Warehouse | Item: Chili',1,'2026-10-11 00:26:58','2026-10-11 00:26:58'),(23,6,'warehouse','purchase_receive',100.0000,0.0000,100.0000,1.0000,'App\\Models\\PurchaseOrderReceipt',15,'kg','GRN: GRN-20261013-000015 | Purchase order #8 | Item: Zira | Warehouse lot: LOT-20261013-000020',1,'2026-10-13 01:27:44','2026-10-13 01:27:44'),(24,6,'warehouse','transfer_out',100.0000,100.0000,0.0000,1.0000,'App\\Models\\StockTransfer',6,'kg','Transfer TRF-20261013-000006 | Warehouse to Restaurant | Item: Zira',1,'2026-10-13 01:28:05','2026-10-13 01:28:05'),(25,6,'restaurant','transfer_in',100.0000,0.0000,100.0000,1.0000,'App\\Models\\StockTransfer',6,'kg','Transfer TRF-20261013-000006 | Restaurant received from Warehouse | Item: Zira',1,'2026-10-13 01:28:05','2026-10-13 01:28:05'),(26,2,'restaurant','recipe_consumption',1.0000,7.0000,6.0000,1.0000,'App\\Models\\OrderRecipeConsumption',4,'pcs','Recipe consumption | Order: ORD-20261013-RPJBSG | Kitchen Batch: #1 | Material: Burger Test Batch | Quantity: 1.0000 pcs',2,'2026-10-13 09:11:32','2026-10-13 09:11:32'),(27,7,'warehouse','purchase_receive',100.0000,0.0000,100.0000,1.0000,'App\\Models\\PurchaseOrderReceipt',16,'kg','GRN: GRN-20261014-000016 | Purchase order #9 | Item: Chicken | Warehouse lot: LOT-20261014-000021',1,'2026-10-14 02:25:48','2026-10-14 02:25:48'),(28,2,'restaurant','recipe_consumption',3.0000,6.0000,3.0000,1.0000,'App\\Models\\OrderRecipeConsumption',5,'pcs','Recipe consumption | Order: ORD-20261015-VZ5STH | Kitchen Batch: #1 | Material: Burger Test Batch | Quantity: 3.0000 pcs',2,'2026-10-14 21:47:57','2026-10-14 21:47:57'),(29,2,'restaurant','recipe_consumption',1.0000,3.0000,2.0000,1.0000,'App\\Models\\OrderRecipeConsumption',6,'pcs','Recipe consumption | Order: ORD-20261025-LLNHH1 | Kitchen Batch: #1 | Material: Burger Test Batch | Quantity: 1.0000 pcs',2,'2026-10-24 22:34:26','2026-10-24 22:34:26'),(30,2,'restaurant','recipe_consumption',1.0000,2.0000,1.0000,1.0000,'App\\Models\\OrderRecipeConsumption',7,'pcs','Recipe consumption | Order: ORD-20261025-QKLF8J | Kitchen Batch: #1 | Material: Burger Test Batch | Quantity: 1.0000 pcs',2,'2026-10-26 02:05:19','2026-10-26 02:05:19'),(31,7,'warehouse','purchase_receive',10.0000,100.0000,110.0000,1.0000,'App\\Models\\PurchaseOrderReceipt',17,'kg','GRN: GRN-20261026-000017 | Purchase order #10 | Item: Chicken | Warehouse lot: LOT-20261026-000022',1,'2026-10-26 02:14:10','2026-10-26 02:14:10'),(32,7,'warehouse','transfer_out',110.0000,110.0000,0.0000,1.0000,'App\\Models\\StockTransfer',7,'kg','Transfer TRF-20261026-000007 | Warehouse to Restaurant | Item: Chicken',1,'2026-10-26 02:14:44','2026-10-26 02:14:44'),(33,7,'restaurant','transfer_in',110.0000,0.0000,110.0000,1.0000,'App\\Models\\StockTransfer',7,'kg','Transfer TRF-20261026-000007 | Restaurant received from Warehouse | Item: Chicken',1,'2026-10-26 02:14:44','2026-10-26 02:14:44'),(34,2,'restaurant','recipe_consumption',1.0000,1.0000,0.0000,1.0000,'App\\Models\\OrderRecipeConsumption',8,'pcs','Recipe consumption | Order: ORD-20261026-SXGNQX | Kitchen Batch: #1 | Material: Burger Test Batch | Quantity: 1.0000 pcs',2,'2026-10-26 02:24:29','2026-10-26 02:24:29'),(35,7,'warehouse','purchase_receive',100.0000,0.0000,100.0000,1.0000,'App\\Models\\PurchaseOrderReceipt',18,'kg','GRN: GRN-20261026-000018 | Purchase order #111 | Item: Chicken | Warehouse lot: LOT-20261026-000023',1,'2026-10-26 04:52:32','2026-10-26 04:52:32'),(36,7,'warehouse','transfer_out',100.0000,100.0000,0.0000,1.0000,'App\\Models\\StockTransfer',9,'kg','Transfer TRF-20261026-000009 | Warehouse to Restaurant | Item: Chicken',1,'2026-10-26 04:52:56','2026-10-26 04:52:56'),(37,7,'restaurant','transfer_in',100.0000,110.0000,210.0000,1.0000,'App\\Models\\StockTransfer',9,'kg','Transfer TRF-20261026-000009 | Restaurant received from Warehouse | Item: Chicken',1,'2026-10-26 04:52:56','2026-10-26 04:52:56'),(38,7,'restaurant','recipe_consumption',100.0000,210.0000,110.0000,1.0000,'App\\Models\\OrderRecipeConsumption',9,'kg','Recipe consumption | Order: ORD-20261026-GGCQ4L | Kitchen Batch: #1 | Material: Chicken | Quantity: 100.0000 kg',2,'2026-10-26 05:02:22','2026-10-26 05:02:22'),(39,109,'warehouse','opening_stock',400.0000,0.0000,400.0000,1.0000,'App\\Models\\RawMaterial',109,'litre','Initial warehouse opening stock.',1,'2026-10-26 05:22:48','2026-10-26 05:22:48');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_items`
--

DROP TABLE IF EXISTS `stock_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_transfer_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL,
  `warehouse_quantity_before` decimal(14,4) NOT NULL,
  `warehouse_quantity_after` decimal(14,4) NOT NULL,
  `restaurant_quantity_before` decimal(14,4) NOT NULL,
  `restaurant_quantity_after` decimal(14,4) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfer_material_unique` (`stock_transfer_id`,`raw_material_id`),
  KEY `stock_transfer_items_raw_material_id_index` (`raw_material_id`),
  CONSTRAINT `stock_transfer_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `stock_transfer_items_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_items`
--

LOCK TABLES `stock_transfer_items` WRITE;
/*!40000 ALTER TABLE `stock_transfer_items` DISABLE KEYS */;
INSERT INTO `stock_transfer_items` VALUES (1,2,2,'Burger Test Batch','pcs',10.0000,1.0000,10.0000,0.0000,0.0000,10.0000,'Local test recipe stock transfer','2026-10-06 20:05:32','2026-10-06 20:05:32'),(2,5,5,'Salt','kg',100.0000,1.0000,100.0000,0.0000,0.0000,100.0000,NULL,'2026-10-11 00:26:57','2026-10-11 00:26:57'),(3,5,4,'Chili','kg',120.0000,1.0000,120.0000,0.0000,0.0000,120.0000,NULL,'2026-10-11 00:26:58','2026-10-11 00:26:58'),(4,6,6,'Zira','kg',100.0000,1.0000,100.0000,0.0000,0.0000,100.0000,NULL,'2026-10-13 01:28:05','2026-10-13 01:28:05'),(5,7,7,'Chicken','kg',110.0000,1.0000,110.0000,0.0000,0.0000,110.0000,NULL,'2026-10-26 02:14:44','2026-10-26 02:14:44'),(6,9,7,'Chicken','kg',100.0000,1.0000,100.0000,0.0000,110.0000,210.0000,NULL,'2026-10-26 04:52:56','2026-10-26 04:52:56');
/*!40000 ALTER TABLE `stock_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_template_items`
--

DROP TABLE IF EXISTS `stock_transfer_template_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_template_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_transfer_template_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transfer_template_material_unique` (`stock_transfer_template_id`,`raw_material_id`),
  KEY `stock_transfer_template_items_raw_material_id_foreign` (`raw_material_id`),
  KEY `transfer_template_sort_index` (`stock_transfer_template_id`,`sort_order`),
  CONSTRAINT `stock_transfer_template_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `stock_transfer_template_items_stock_transfer_template_id_foreign` FOREIGN KEY (`stock_transfer_template_id`) REFERENCES `stock_transfer_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_template_items`
--

LOCK TABLES `stock_transfer_template_items` WRITE;
/*!40000 ALTER TABLE `stock_transfer_template_items` DISABLE KEYS */;
INSERT INTO `stock_transfer_template_items` VALUES (1,1,3,'Suger','kg',2.0000,NULL,0,'2026-10-07 00:32:44','2026-10-07 00:32:44'),(2,1,1,'Burger','pcs',2.0000,NULL,1,'2026-10-07 00:32:44','2026-10-07 00:32:44');
/*!40000 ALTER TABLE `stock_transfer_template_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_templates`
--

DROP TABLE IF EXISTS `stock_transfer_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_transfer_templates_created_by_foreign` (`created_by`),
  KEY `stock_transfer_templates_updated_by_foreign` (`updated_by`),
  KEY `stock_transfer_templates_name_index` (`name`),
  KEY `stock_transfer_templates_is_active_index` (`is_active`),
  CONSTRAINT `stock_transfer_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfer_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_templates`
--

LOCK TABLES `stock_transfer_templates` WRITE;
/*!40000 ALTER TABLE `stock_transfer_templates` DISABLE KEYS */;
INSERT INTO `stock_transfer_templates` VALUES (1,'Morning is stock',NULL,1,1,1,'2026-10-07 00:32:44','2026-10-07 00:32:44',NULL);
/*!40000 ALTER TABLE `stock_transfer_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_no` varchar(80) DEFAULT NULL,
  `transferred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `transferred_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfers_transfer_no_unique` (`transfer_no`),
  KEY `stock_transfers_transferred_by_foreign` (`transferred_by`),
  KEY `stock_transfers_created_by_foreign` (`created_by`),
  KEY `stock_transfers_updated_by_foreign` (`updated_by`),
  KEY `stock_transfers_transferred_at_index` (`transferred_at`),
  CONSTRAINT `stock_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_transferred_by_foreign` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfers`
--

LOCK TABLES `stock_transfers` WRITE;
/*!40000 ALTER TABLE `stock_transfers` DISABLE KEYS */;
INSERT INTO `stock_transfers` VALUES (2,'TRF-20261007-000002','2026-10-07 02:05:32',NULL,1,1,1,'2026-10-06 20:05:32','2026-10-06 20:05:32'),(5,'TRF-20261011-000005','2026-10-11 06:26:57',NULL,1,1,1,'2026-10-11 00:26:57','2026-10-11 00:26:57'),(6,'TRF-20261013-000006','2026-10-13 07:28:05',NULL,1,1,1,'2026-10-13 01:28:05','2026-10-13 01:28:05'),(7,'TRF-20261026-000007','2026-10-26 08:14:44',NULL,1,1,1,'2026-10-26 02:14:44','2026-10-26 02:14:44'),(9,'TRF-20261026-000009','2026-10-26 10:52:56',NULL,1,1,1,'2026-10-26 04:52:56','2026-10-26 04:52:56');
/*!40000 ALTER TABLE `stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_credit_applications`
--

DROP TABLE IF EXISTS `supplier_credit_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_credit_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_no` varchar(80) NOT NULL,
  `source_settlement_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(18,2) unsigned NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_credit_applications_application_no_unique` (`application_no`),
  KEY `sca_user_fk` (`created_by`),
  KEY `sca_source_date_idx` (`source_settlement_id`,`applied_at`),
  KEY `sca_supplier_date_idx` (`supplier_id`,`applied_at`),
  KEY `sca_po_date_idx` (`purchase_order_id`,`applied_at`),
  CONSTRAINT `sca_po_fk` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  CONSTRAINT `sca_source_fk` FOREIGN KEY (`source_settlement_id`) REFERENCES `purchase_return_settlements` (`id`),
  CONSTRAINT `sca_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `sca_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_credit_applications`
--

LOCK TABLES `supplier_credit_applications` WRITE;
/*!40000 ALTER TABLE `supplier_credit_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_credit_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text DEFAULT NULL,
  `gstin` varchar(50) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_created_by_foreign` (`created_by`),
  KEY `suppliers_updated_by_foreign` (`updated_by`),
  KEY `suppliers_supplier_name_index` (`supplier_name`),
  KEY `suppliers_phone_index` (`phone`),
  CONSTRAINT `suppliers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `suppliers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Rukshana','Rukshana','shraboniakter554@gmail.com','01576757878','974 Green Cowley Freeway','22AAAAA0000A1Z5',1,1,'2026-10-07 00:39:05','2026-10-07 00:39:05',NULL),(2,'DEMO100-Supplier 001','Contact 001','demo-supplier-001@local.test','8801701000001','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(3,'DEMO100-Supplier 002','Contact 002','demo-supplier-002@local.test','8801701000002','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(4,'DEMO100-Supplier 003','Contact 003','demo-supplier-003@local.test','8801701000003','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(5,'DEMO100-Supplier 004','Contact 004','demo-supplier-004@local.test','8801701000004','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(6,'DEMO100-Supplier 005','Contact 005','demo-supplier-005@local.test','8801701000005','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(7,'DEMO100-Supplier 006','Contact 006','demo-supplier-006@local.test','8801701000006','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,'DEMO100-Supplier 007','Contact 007','demo-supplier-007@local.test','8801701000007','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,'DEMO100-Supplier 008','Contact 008','demo-supplier-008@local.test','8801701000008','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,'DEMO100-Supplier 009','Contact 009','demo-supplier-009@local.test','8801701000009','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,'DEMO100-Supplier 010','Contact 010','demo-supplier-010@local.test','8801701000010','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,'DEMO100-Supplier 011','Contact 011','demo-supplier-011@local.test','8801701000011','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,'DEMO100-Supplier 012','Contact 012','demo-supplier-012@local.test','8801701000012','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,'DEMO100-Supplier 013','Contact 013','demo-supplier-013@local.test','8801701000013','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,'DEMO100-Supplier 014','Contact 014','demo-supplier-014@local.test','8801701000014','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,'DEMO100-Supplier 015','Contact 015','demo-supplier-015@local.test','8801701000015','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,'DEMO100-Supplier 016','Contact 016','demo-supplier-016@local.test','8801701000016','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,'DEMO100-Supplier 017','Contact 017','demo-supplier-017@local.test','8801701000017','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,'DEMO100-Supplier 018','Contact 018','demo-supplier-018@local.test','8801701000018','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,'DEMO100-Supplier 019','Contact 019','demo-supplier-019@local.test','8801701000019','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,'DEMO100-Supplier 020','Contact 020','demo-supplier-020@local.test','8801701000020','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,'DEMO100-Supplier 021','Contact 021','demo-supplier-021@local.test','8801701000021','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,'DEMO100-Supplier 022','Contact 022','demo-supplier-022@local.test','8801701000022','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,'DEMO100-Supplier 023','Contact 023','demo-supplier-023@local.test','8801701000023','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,'DEMO100-Supplier 024','Contact 024','demo-supplier-024@local.test','8801701000024','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,'DEMO100-Supplier 025','Contact 025','demo-supplier-025@local.test','8801701000025','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,'DEMO100-Supplier 026','Contact 026','demo-supplier-026@local.test','8801701000026','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,'DEMO100-Supplier 027','Contact 027','demo-supplier-027@local.test','8801701000027','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,'DEMO100-Supplier 028','Contact 028','demo-supplier-028@local.test','8801701000028','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,'DEMO100-Supplier 029','Contact 029','demo-supplier-029@local.test','8801701000029','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,'DEMO100-Supplier 030','Contact 030','demo-supplier-030@local.test','8801701000030','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,'DEMO100-Supplier 031','Contact 031','demo-supplier-031@local.test','8801701000031','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,'DEMO100-Supplier 032','Contact 032','demo-supplier-032@local.test','8801701000032','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,'DEMO100-Supplier 033','Contact 033','demo-supplier-033@local.test','8801701000033','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,'DEMO100-Supplier 034','Contact 034','demo-supplier-034@local.test','8801701000034','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,'DEMO100-Supplier 035','Contact 035','demo-supplier-035@local.test','8801701000035','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,'DEMO100-Supplier 036','Contact 036','demo-supplier-036@local.test','8801701000036','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,'DEMO100-Supplier 037','Contact 037','demo-supplier-037@local.test','8801701000037','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,'DEMO100-Supplier 038','Contact 038','demo-supplier-038@local.test','8801701000038','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,'DEMO100-Supplier 039','Contact 039','demo-supplier-039@local.test','8801701000039','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,'DEMO100-Supplier 040','Contact 040','demo-supplier-040@local.test','8801701000040','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,'DEMO100-Supplier 041','Contact 041','demo-supplier-041@local.test','8801701000041','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,'DEMO100-Supplier 042','Contact 042','demo-supplier-042@local.test','8801701000042','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,'DEMO100-Supplier 043','Contact 043','demo-supplier-043@local.test','8801701000043','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,'DEMO100-Supplier 044','Contact 044','demo-supplier-044@local.test','8801701000044','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,'DEMO100-Supplier 045','Contact 045','demo-supplier-045@local.test','8801701000045','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,'DEMO100-Supplier 046','Contact 046','demo-supplier-046@local.test','8801701000046','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,'DEMO100-Supplier 047','Contact 047','demo-supplier-047@local.test','8801701000047','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,'DEMO100-Supplier 048','Contact 048','demo-supplier-048@local.test','8801701000048','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,'DEMO100-Supplier 049','Contact 049','demo-supplier-049@local.test','8801701000049','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,'DEMO100-Supplier 050','Contact 050','demo-supplier-050@local.test','8801701000050','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,'DEMO100-Supplier 051','Contact 051','demo-supplier-051@local.test','8801701000051','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,'DEMO100-Supplier 052','Contact 052','demo-supplier-052@local.test','8801701000052','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,'DEMO100-Supplier 053','Contact 053','demo-supplier-053@local.test','8801701000053','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,'DEMO100-Supplier 054','Contact 054','demo-supplier-054@local.test','8801701000054','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,'DEMO100-Supplier 055','Contact 055','demo-supplier-055@local.test','8801701000055','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,'DEMO100-Supplier 056','Contact 056','demo-supplier-056@local.test','8801701000056','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,'DEMO100-Supplier 057','Contact 057','demo-supplier-057@local.test','8801701000057','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,'DEMO100-Supplier 058','Contact 058','demo-supplier-058@local.test','8801701000058','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,'DEMO100-Supplier 059','Contact 059','demo-supplier-059@local.test','8801701000059','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,'DEMO100-Supplier 060','Contact 060','demo-supplier-060@local.test','8801701000060','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,'DEMO100-Supplier 061','Contact 061','demo-supplier-061@local.test','8801701000061','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,'DEMO100-Supplier 062','Contact 062','demo-supplier-062@local.test','8801701000062','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,'DEMO100-Supplier 063','Contact 063','demo-supplier-063@local.test','8801701000063','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,'DEMO100-Supplier 064','Contact 064','demo-supplier-064@local.test','8801701000064','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,'DEMO100-Supplier 065','Contact 065','demo-supplier-065@local.test','8801701000065','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,'DEMO100-Supplier 066','Contact 066','demo-supplier-066@local.test','8801701000066','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,'DEMO100-Supplier 067','Contact 067','demo-supplier-067@local.test','8801701000067','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,'DEMO100-Supplier 068','Contact 068','demo-supplier-068@local.test','8801701000068','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,'DEMO100-Supplier 069','Contact 069','demo-supplier-069@local.test','8801701000069','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,'DEMO100-Supplier 070','Contact 070','demo-supplier-070@local.test','8801701000070','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,'DEMO100-Supplier 071','Contact 071','demo-supplier-071@local.test','8801701000071','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,'DEMO100-Supplier 072','Contact 072','demo-supplier-072@local.test','8801701000072','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,'DEMO100-Supplier 073','Contact 073','demo-supplier-073@local.test','8801701000073','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,'DEMO100-Supplier 074','Contact 074','demo-supplier-074@local.test','8801701000074','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,'DEMO100-Supplier 075','Contact 075','demo-supplier-075@local.test','8801701000075','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,'DEMO100-Supplier 076','Contact 076','demo-supplier-076@local.test','8801701000076','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,'DEMO100-Supplier 077','Contact 077','demo-supplier-077@local.test','8801701000077','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,'DEMO100-Supplier 078','Contact 078','demo-supplier-078@local.test','8801701000078','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,'DEMO100-Supplier 079','Contact 079','demo-supplier-079@local.test','8801701000079','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,'DEMO100-Supplier 080','Contact 080','demo-supplier-080@local.test','8801701000080','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,'DEMO100-Supplier 081','Contact 081','demo-supplier-081@local.test','8801701000081','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,'DEMO100-Supplier 082','Contact 082','demo-supplier-082@local.test','8801701000082','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,'DEMO100-Supplier 083','Contact 083','demo-supplier-083@local.test','8801701000083','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,'DEMO100-Supplier 084','Contact 084','demo-supplier-084@local.test','8801701000084','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,'DEMO100-Supplier 085','Contact 085','demo-supplier-085@local.test','8801701000085','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,'DEMO100-Supplier 086','Contact 086','demo-supplier-086@local.test','8801701000086','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,'DEMO100-Supplier 087','Contact 087','demo-supplier-087@local.test','8801701000087','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,'DEMO100-Supplier 088','Contact 088','demo-supplier-088@local.test','8801701000088','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,'DEMO100-Supplier 089','Contact 089','demo-supplier-089@local.test','8801701000089','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,'DEMO100-Supplier 090','Contact 090','demo-supplier-090@local.test','8801701000090','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,'DEMO100-Supplier 091','Contact 091','demo-supplier-091@local.test','8801701000091','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,'DEMO100-Supplier 092','Contact 092','demo-supplier-092@local.test','8801701000092','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,'DEMO100-Supplier 093','Contact 093','demo-supplier-093@local.test','8801701000093','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,'DEMO100-Supplier 094','Contact 094','demo-supplier-094@local.test','8801701000094','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,'DEMO100-Supplier 095','Contact 095','demo-supplier-095@local.test','8801701000095','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,'DEMO100-Supplier 096','Contact 096','demo-supplier-096@local.test','8801701000096','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,'DEMO100-Supplier 097','Contact 097','demo-supplier-097@local.test','8801701000097','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,'DEMO100-Supplier 098','Contact 098','demo-supplier-098@local.test','8801701000098','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,'DEMO100-Supplier 099','Contact 099','demo-supplier-099@local.test','8801701000099','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,'DEMO100-Supplier 100','Contact 100','demo-supplier-100@local.test','8801701000100','Local demo address',NULL,1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,'Sadia','Tasnima Akther Tisha','shraboniakter554@gmail.com','98265352','974 Green Cowley Freeway','g3tgt',1,1,'2026-10-26 05:47:53','2026-10-26 05:47:53',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_name` varchar(255) DEFAULT NULL,
  `restaurant_logo` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `branch_address` varchar(255) DEFAULT NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vat_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `service_charge_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `service_charge_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `owner_nid` varchar(255) DEFAULT NULL,
  `owner_trade_license` varchar(255) DEFAULT NULL,
  `invoice_subtitle` varchar(255) DEFAULT NULL,
  `bin_number` varchar(255) DEFAULT NULL,
  `invoice_show_logo` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_company_name` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_branch_address` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_token_number` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_table_number` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_currency` varchar(255) NOT NULL DEFAULT 'BDT',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'Sopno Restaurant','system-settings/logo/AWNwvnkEaR9d9nl724Y6arhZxRpAKOk3mtzssIng.jpg','Bangladesh','974 Green Cowley Freeway',0.00,'percentage',0.00,'percentage',NULL,'system-settings/documents/trade-license/qo19P05dgAA8mwSPJSQnpek8EAVGMBABq7tSQVlQ.jpg',NULL,NULL,0,0,0,0,0,'BDT','2026-10-06 13:10:42','2026-10-15 01:55:46');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_logout_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `blocked_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_is_active_index` (`is_active`),
  KEY `users_blocked_at_index` (`blocked_at`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Tisha','shraboniakter554','shraboniakter554@gmail.com',NULL,'$2y$12$0rIsMfLryxPL95j0dc1DKOnteghYb3FMrzit8DqASW5A5ueHWqZpC',1,'2026-10-25 21:07:28','2026-10-08 02:11:00',0,NULL,NULL,0,NULL,'2026-10-06 13:10:42','2026-10-25 21:07:28',NULL),(2,3,'Chef','chef.sopno','chef.sopno@example.com',NULL,'$2y$12$cCuv79az1qgwY89PUwR82eIotwtHuNM0YbdJ5sEIKkbhldmOrDIvS',1,'2026-10-26 02:04:51',NULL,0,NULL,NULL,0,NULL,'2026-10-06 19:11:32','2026-10-26 02:04:51',NULL),(3,4,'Waiter','waiter12','waiter@gmail.com',NULL,'$2y$12$sluylYifNuc9hhtQv4Gm5ecJ4UaKHFgW6bebmJ7CuvgWAhk.ugeSW',1,'2026-10-12 03:01:35',NULL,0,NULL,NULL,0,NULL,'2026-10-06 21:16:16','2026-10-13 09:00:04',NULL),(4,3,'Demo Staff 001','demo-staff-001','demo-staff-001@local.test',NULL,'$2y$12$wEAA6LZUnNFYcTnqEBKLseVDTwrQGInCD1/16NaABb/iJa4tYSYBG',1,NULL,NULL,0,NULL,NULL,0,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(5,4,'Demo Staff 002','demo-staff-002','demo-staff-002@local.test',NULL,'$2y$12$uzLUgIyVHIMGK4XoMnHjXOHoQWqNy.82eVajKTb1ocfHoPKql6pGa',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(6,4,'Demo Staff 003','demo-staff-003','demo-staff-003@local.test',NULL,'$2y$12$i./SfIvXXLp/ovGfkopCzOKlEFD4BFl9JWVsR50A1uFp1DWCV4KGO',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(7,4,'Demo Staff 004','demo-staff-004','demo-staff-004@local.test',NULL,'$2y$12$toPwwcto1wbWxg9oAUAsyeIaZTbseOr7Pt6fYXaYYTOqycwuxfJEi',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(8,4,'Demo Staff 005','demo-staff-005','demo-staff-005@local.test',NULL,'$2y$12$37BEZmTQtaArbrH35DZBseqGZRdC3yFVrPdJWCmoog/lnSmHCFoXO',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,4,'Demo Staff 006','demo-staff-006','demo-staff-006@local.test',NULL,'$2y$12$VPLRhymDovEu8H2Mccb1RegP4izJ7R6D2TRx/hDoufGsvG7P3tlqW',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,4,'Demo Staff 007','demo-staff-007','demo-staff-007@local.test',NULL,'$2y$12$p9.PB2fDU/3DpCRplwPdVOYs71eu4TA93MKCyz6Yd11mvqTgqoHaa',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,4,'Demo Staff 008','demo-staff-008','demo-staff-008@local.test',NULL,'$2y$12$1dIsCmmTE23zokCwhZb2SONsNfEK2.oAHQfCScm5/lhQ9D/Y1G8Ba',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,4,'Demo Staff 009','demo-staff-009','demo-staff-009@local.test',NULL,'$2y$12$PDL5.CoP6Ju5Jqgzo42hJO5bueOiufuzASYpiWWEBjRbSEm98RR6u',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,4,'Demo Staff 010','demo-staff-010','demo-staff-010@local.test',NULL,'$2y$12$bXNW4xGHe7wT0aTlme.oreFGKjyuMGxqzAPXws0s9IRE2I9RuSXd2',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,4,'Demo Staff 011','demo-staff-011','demo-staff-011@local.test',NULL,'$2y$12$Ls3MrQqHNFs9yCoSN9V/cOloyPcJ67XmZ/5BQLoG405YAvrzVV9pS',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,4,'Demo Staff 012','demo-staff-012','demo-staff-012@local.test',NULL,'$2y$12$XzsoWEwoSko0CZ.JqreQUeaxD3qvww8bSD0KfKZbPbRWoC.mND7Du',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,4,'Demo Staff 013','demo-staff-013','demo-staff-013@local.test',NULL,'$2y$12$L1X9pQzlyrh5UuXajAvYruAfcWJTCK3Om6s0uSrYLnj2oGx1AIRGa',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,4,'Demo Staff 014','demo-staff-014','demo-staff-014@local.test',NULL,'$2y$12$f.TOZCkSnyZ6lr61cnQK0ONJskkYz9UmxAjsBIIya4j0hn6v3C/x.',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,4,'Demo Staff 015','demo-staff-015','demo-staff-015@local.test',NULL,'$2y$12$jsd2DmfUSx/ZyQ430lcj4eXfdbX7muRwfhje9gWL.tJoNJCvq.URe',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,4,'Demo Staff 016','demo-staff-016','demo-staff-016@local.test',NULL,'$2y$12$jlZcQdhG3r6uPIvGOz9BnOTN1Ca0KrLTEI3qEI8w5TOIHwDo3JcVe',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,4,'Demo Staff 017','demo-staff-017','demo-staff-017@local.test',NULL,'$2y$12$gGmz.fyPbBWVJI5xlsKz7eZ9OjyxmkjOuZCYBnLAF79che3SKfHQ6',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,4,'Demo Staff 018','demo-staff-018','demo-staff-018@local.test',NULL,'$2y$12$JuyOL0c.QnEgTBSldZ4nuOiM9x.vylCA5dxzQAAB15RftOC.dk.7W',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,4,'Demo Staff 019','demo-staff-019','demo-staff-019@local.test',NULL,'$2y$12$v6UZWDGwqlavHWs4AYj4t.sfMtM9/M1LBXXaI9salaS6sPNc/QeXa',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,4,'Demo Staff 020','demo-staff-020','demo-staff-020@local.test',NULL,'$2y$12$cN/qbTWFfhzcRRhyCljP8e3eWWfgNQBSXoeN3mW4Ct3ZnUX7Yfidm',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,4,'Demo Staff 021','demo-staff-021','demo-staff-021@local.test',NULL,'$2y$12$QRSBZGiI.mePJTqV9NLGA.cMmC/oaSFhAbrdAnmHT.YYMzum2AOZe',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,4,'Demo Staff 022','demo-staff-022','demo-staff-022@local.test',NULL,'$2y$12$fBDpNmfpH56mtweDqG2wdeAN5mXlXIEQH2IdjYHE0PSYRJlc1mSue',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,4,'Demo Staff 023','demo-staff-023','demo-staff-023@local.test',NULL,'$2y$12$kGhEQtEqqWlYh/GOU1vizu4F/tsZz6IwUVPQkYUyTXEkskadPzLLi',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,4,'Demo Staff 024','demo-staff-024','demo-staff-024@local.test',NULL,'$2y$12$l3noee37K5n00u5MokQIX.7sOoUhlyVQhpq9OSY5yxCE/BG.G5hVq',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,4,'Demo Staff 025','demo-staff-025','demo-staff-025@local.test',NULL,'$2y$12$imiL7L451fpbvJoEZATwCu5R99DV4EMmK2yQ84pI6OfFC3bLJGUzO',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,4,'Demo Staff 026','demo-staff-026','demo-staff-026@local.test',NULL,'$2y$12$z1liOk0JlJnkl2W8Diq8KeBlqGyMKJxzdBqEhQaTDoc.WCZ8tzc8a',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,4,'Demo Staff 027','demo-staff-027','demo-staff-027@local.test',NULL,'$2y$12$L3hzwrrGOxdXSJfZPfM9MuFntVHGtwbnmSOJjJVjf.fF2ICP0NKo6',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,4,'Demo Staff 028','demo-staff-028','demo-staff-028@local.test',NULL,'$2y$12$vz5t9AfNUJ9SRZ584dNZXewM1SCIhCpQXeZ0FgzsMqOqngXsqdBwy',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,4,'Demo Staff 029','demo-staff-029','demo-staff-029@local.test',NULL,'$2y$12$zdLtIKqw3QKuZN.UHSVUcO5aGW68/AOnuvUA6X2g5XmmKeOy3maDC',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,4,'Demo Staff 030','demo-staff-030','demo-staff-030@local.test',NULL,'$2y$12$QdZE6CfL3WMRJU8wcCqffeqX8PWWLZQU9PgdMdk0pPA7GwRtHfou6',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,4,'Demo Staff 031','demo-staff-031','demo-staff-031@local.test',NULL,'$2y$12$bsZ/BzqN0H8Ecpp0iWRJG.xnZt99XBe4/OiqO9PUXAycW9KxZXlHG',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,4,'Demo Staff 032','demo-staff-032','demo-staff-032@local.test',NULL,'$2y$12$5ZUJyY6edl05JOcTHqlreet9lnYVXc0O9bvpfBcbh4mlXCIMxWCvW',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,4,'Demo Staff 033','demo-staff-033','demo-staff-033@local.test',NULL,'$2y$12$vqwebXy.Yz1GItgdHR1ZwekVha0jgkre80CJZZdIvNeYJcbzQfugy',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,4,'Demo Staff 034','demo-staff-034','demo-staff-034@local.test',NULL,'$2y$12$cu48MY8LwlKD/5o7EBbMmeRGNJBLIsOQj0m89Twq.adwii7KUeGx2',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,4,'Demo Staff 035','demo-staff-035','demo-staff-035@local.test',NULL,'$2y$12$j5DlJ7aA.p7pcWmX0C3HGuvQ/VAXJOiTUTe4sA0zhokRpYZgSD/sy',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,4,'Demo Staff 036','demo-staff-036','demo-staff-036@local.test',NULL,'$2y$12$3/CYlydesIC.h31vjgvjderNkC4aXHotav4PITng9PXMk/b4b8hc6',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,4,'Demo Staff 037','demo-staff-037','demo-staff-037@local.test',NULL,'$2y$12$E0fv5fOvWgwZKmPzZTggHuBMq5V4BpfW5K7XsODRpFbDozkrbv.5C',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,4,'Demo Staff 038','demo-staff-038','demo-staff-038@local.test',NULL,'$2y$12$7tnvzp.MfprSUehMXRvaCugIvyCCDXGHrvSaClw16ltM4.4PUMu7S',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,4,'Demo Staff 039','demo-staff-039','demo-staff-039@local.test',NULL,'$2y$12$RAt20xtK7fj/f3d7RFZXGuMpl8AoTaNjGpAUphvPKCAeF2iDk3isu',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,4,'Demo Staff 040','demo-staff-040','demo-staff-040@local.test',NULL,'$2y$12$YJOnVCfxr.6dvpft0xQOQOy3qK7GkF1E4Wr/7PNo3Xql8LR/q3cH2',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,4,'Demo Staff 041','demo-staff-041','demo-staff-041@local.test',NULL,'$2y$12$Tms8Cx3.LZZ4q5Q.597HQOZTP2JnKX7gYAM8b.09SvWYCsvFs6YQq',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,4,'Demo Staff 042','demo-staff-042','demo-staff-042@local.test',NULL,'$2y$12$rD3Ge3snVPbWbvJa/mBda.E5CmDrVrF6QaS4lMVi4ObvZCTtgx7mG',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,4,'Demo Staff 043','demo-staff-043','demo-staff-043@local.test',NULL,'$2y$12$dcSkk8SGjUmNBhBByY9X4udWhJiHQsO8eOPh.5YrBzlfLMYaK0N.m',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,4,'Demo Staff 044','demo-staff-044','demo-staff-044@local.test',NULL,'$2y$12$9g9mWSs0EXes/roXNtcvqeTGMhIT3eEpjq37ev5dC3I70TqAIjHuW',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,4,'Demo Staff 045','demo-staff-045','demo-staff-045@local.test',NULL,'$2y$12$KK9SDTP08wUwYJPlVN9UTu8TD3olfkOudiTbUmptoOSKd/L1Yqaxy',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,4,'Demo Staff 046','demo-staff-046','demo-staff-046@local.test',NULL,'$2y$12$BH8PkCUaSKWGCurroRu17uAbT6OpMmkypWA6L4F1oXGyUvqIJT2.e',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,4,'Demo Staff 047','demo-staff-047','demo-staff-047@local.test',NULL,'$2y$12$6W9moBc5mBCiSErFLhVei.6KEC5TkYmbTaUhB94Gh1gm1JoUjSS42',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,4,'Demo Staff 048','demo-staff-048','demo-staff-048@local.test',NULL,'$2y$12$nBGjLF6tc5CtK0a2qk5kge//IftFuKlf5OdolgPmxyQX1TcvtQJJm',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,4,'Demo Staff 049','demo-staff-049','demo-staff-049@local.test',NULL,'$2y$12$AG6F5uz4oGkpovntXW3vkecMQfVceSbIecw2XihZZSMX1/Fz2Xxf6',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,4,'Demo Staff 050','demo-staff-050','demo-staff-050@local.test',NULL,'$2y$12$A/gt/uRz7lBSHKXs2FpKb.CWx9XIF6uc3iq9hAPAJj1qz2Nb9669e',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,4,'Demo Staff 051','demo-staff-051','demo-staff-051@local.test',NULL,'$2y$12$IKZhOKiM2zubchWZPcEqROqCb0MRwTaV5iRLqc0E7RaWAu8Tmb8Nq',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,4,'Demo Staff 052','demo-staff-052','demo-staff-052@local.test',NULL,'$2y$12$yNlf2QztqxxzxldoUuIxm.VF1nagEP5LPvd8HfutIz7.oXQlydKLS',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,4,'Demo Staff 053','demo-staff-053','demo-staff-053@local.test',NULL,'$2y$12$NnYhww3WvppRt3May8jY6uCNGZcsVglSbuYIl.jvnAp4QASUIXHfq',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,4,'Demo Staff 054','demo-staff-054','demo-staff-054@local.test',NULL,'$2y$12$lHE2j6XrtTIwPaF/jeBKse4skPWsqFfdmrEp5Ml0cVa75utAFqRJq',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,4,'Demo Staff 055','demo-staff-055','demo-staff-055@local.test',NULL,'$2y$12$8fAw3ckT.3nU.vPWGkW.MOHGix.oTMPWb4D6Wp1OLqnaKpR9TvAUS',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,4,'Demo Staff 056','demo-staff-056','demo-staff-056@local.test',NULL,'$2y$12$jFIQgRCaGNaJv3lzhmXYKeEV6dzxg5p9B4fMvFYA7ZN3vmpBGRpza',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,4,'Demo Staff 057','demo-staff-057','demo-staff-057@local.test',NULL,'$2y$12$33kQtfoadfXo8fLIetc7Ou7INtLNK9Sfmzt3mBRYSu.jFBHFVd9Ey',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,4,'Demo Staff 058','demo-staff-058','demo-staff-058@local.test',NULL,'$2y$12$.OKYdgJ5Ai.bvYVAiXnH0uOjyAO5s94CLbMIjkLGjqHJv3N1HEspe',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,4,'Demo Staff 059','demo-staff-059','demo-staff-059@local.test',NULL,'$2y$12$vQqWxuZCVNvdKnHEOaTR/uEm4tV2Bk.3EMZeDL9U4tb7V/vuzQske',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,4,'Demo Staff 060','demo-staff-060','demo-staff-060@local.test',NULL,'$2y$12$zpZVDLSoNlqkRhBnzsDDHuOfE5GWcxrSlO8wlxR9EcCXIws9xMPGC',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,4,'Demo Staff 061','demo-staff-061','demo-staff-061@local.test',NULL,'$2y$12$VzWQK3SEaZsq7oD3I/3hfe3cV0p77K6nyFBsWjfWdvMzcOy.21HNm',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,4,'Demo Staff 062','demo-staff-062','demo-staff-062@local.test',NULL,'$2y$12$iqTZ3Xn8dAalw./L5YXERepqTuXCUliDds0u743FTOtOzx81Zl746',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,4,'Demo Staff 063','demo-staff-063','demo-staff-063@local.test',NULL,'$2y$12$HZ5MjDIyEv.3b2k69R1sE.jG6NaiQbamg9QlMHPCyr7zYwgfx8V6S',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,4,'Demo Staff 064','demo-staff-064','demo-staff-064@local.test',NULL,'$2y$12$xR40gX9RPq7oROkbDRUvWuT1AUxuymxHmzYIbZ1wHZS0fdO5vP8Ee',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,4,'Demo Staff 065','demo-staff-065','demo-staff-065@local.test',NULL,'$2y$12$/bSLlJbkwKh48MmbzsIYP.MYhpVZSIwHQ6hsv3o/3C6OGnSjj3Y3C',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,4,'Demo Staff 066','demo-staff-066','demo-staff-066@local.test',NULL,'$2y$12$X8x.oI8jLR1G3FJ9AlPAO.EVVScl5dvykNu7/8OwVcxTMDu41vf/6',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,4,'Demo Staff 067','demo-staff-067','demo-staff-067@local.test',NULL,'$2y$12$5BV53KMmgG88F4y5T0eQ/.mCeT.juRvOsw97tBu.2HceKVP78EpQC',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,4,'Demo Staff 068','demo-staff-068','demo-staff-068@local.test',NULL,'$2y$12$/FjxP8IOW1V4NT74B.nHi.DKbvM7dFmAwYrSb16UUhQbVUOkiikLu',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,4,'Demo Staff 069','demo-staff-069','demo-staff-069@local.test',NULL,'$2y$12$nj/9vX0YOlNA83gqcqk1B.OZrhcgTUA9CPCZUZiyXHk5BTGhbLMHy',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,4,'Demo Staff 070','demo-staff-070','demo-staff-070@local.test',NULL,'$2y$12$Shc6BUwXruWF92N2J0TRkenUlUbN5FCIe/ZuZVjvbUwX0JP1/zSea',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,4,'Demo Staff 071','demo-staff-071','demo-staff-071@local.test',NULL,'$2y$12$CKZTjWzZE1C2Lg4ECQNuduKKtz5EqirYVTenh6UFE1zYZ8UvsgFbS',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,4,'Demo Staff 072','demo-staff-072','demo-staff-072@local.test',NULL,'$2y$12$bhB.4fk8tPERGTyrK.OpouX7vVSJPM671I24P68YOOI4PGhBZOvPe',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,4,'Demo Staff 073','demo-staff-073','demo-staff-073@local.test',NULL,'$2y$12$i0yhEi48NiRcZ6L5CmhJ8uDf4c5Y6sFYIogjxea.lnAz8s1KCvRzu',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,4,'Demo Staff 074','demo-staff-074','demo-staff-074@local.test',NULL,'$2y$12$LPp4LiZbRQQ580YaEdYO1O6UoaJ8AeIbNd8qyCqGbHiy1GFMn/u8O',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,4,'Demo Staff 075','demo-staff-075','demo-staff-075@local.test',NULL,'$2y$12$9Iecpl8N5.vWEPugC5KM4.6XIDLsrMnlMlAR7O2q/XhyHXvDpUHJS',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,4,'Demo Staff 076','demo-staff-076','demo-staff-076@local.test',NULL,'$2y$12$a7GwIS5NldcqCObf3j6Xhu/v4gXC4Ud3PaFgpIrU0AsFoRciWu5c.',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,4,'Demo Staff 077','demo-staff-077','demo-staff-077@local.test',NULL,'$2y$12$iGNNX4N6kRL2mGKhGgtFI.xrZY21bSttbvk2yMXEkCWQhRie0UYfS',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,4,'Demo Staff 078','demo-staff-078','demo-staff-078@local.test',NULL,'$2y$12$FwzEQhM.y.aMMViRluLfy.pHqnWkdkIutqB0l2vhmzlyfZ.w5XNeW',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,4,'Demo Staff 079','demo-staff-079','demo-staff-079@local.test',NULL,'$2y$12$NIioENDfVX0QYiBgHCLamueDixlF9XRHZZVVk2m8DEClmxQeY.CGa',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,4,'Demo Staff 080','demo-staff-080','demo-staff-080@local.test',NULL,'$2y$12$ePcxlrP5kn2OfQzn8xmqI.WLHPrlQprsLslJsgfB3z9SWvIBnySGu',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,4,'Demo Staff 081','demo-staff-081','demo-staff-081@local.test',NULL,'$2y$12$5UDkoo7zytPBiP2qtAPUEOCdecjFd99sMeh1FZef6Z0FoWFq.hvB6',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,4,'Demo Staff 082','demo-staff-082','demo-staff-082@local.test',NULL,'$2y$12$xmmcR6olL1TlbaadyvZ3wuJNBxrVGJ8oWBXWCphabJD761ing7yjO',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,4,'Demo Staff 083','demo-staff-083','demo-staff-083@local.test',NULL,'$2y$12$/xdYPBU6cuTJSCew6VLBje7ALYpYz1Cge0bSxMLxe0MdFw5SNw..C',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,4,'Demo Staff 084','demo-staff-084','demo-staff-084@local.test',NULL,'$2y$12$s5M.v4Mj9E51OOF7g/ZFcuuLwXZRF/aqjjVxWbXwNsRHgzFdhtzHW',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,4,'Demo Staff 085','demo-staff-085','demo-staff-085@local.test',NULL,'$2y$12$naokSi3YJCSv6TIfGIRnJOvBmTFtipT6Yf6moRNVDS4zTt2ZVZB8O',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,4,'Demo Staff 086','demo-staff-086','demo-staff-086@local.test',NULL,'$2y$12$aChAT/UkuFeWWFBkiSgPiOZ7wRNYkMCI6emhAxE/mnaU4vzl3E.32',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,4,'Demo Staff 087','demo-staff-087','demo-staff-087@local.test',NULL,'$2y$12$kODq1.LkQovfySL2UAcGo.HTZuBiXU3d.i5XiDI7pyRK2ztdbjmAi',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,4,'Demo Staff 088','demo-staff-088','demo-staff-088@local.test',NULL,'$2y$12$lnpkiznsLTM1hoy03JuMz.ne9sDskMeMiij4JwTLJnb5Mr9ENjNbm',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,4,'Demo Staff 089','demo-staff-089','demo-staff-089@local.test',NULL,'$2y$12$MvkFecAzAQAPQItkcK45x.5EbQF3uniwIO3Y543Ww5wdBIT3WIgRS',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,4,'Demo Staff 090','demo-staff-090','demo-staff-090@local.test',NULL,'$2y$12$X3uD1.NI7NZI2Enb13RXAuLKNMk9lkEMJSVJhbJbMxcm/exwBAm8e',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,4,'Demo Staff 091','demo-staff-091','demo-staff-091@local.test',NULL,'$2y$12$BHMfPpy9OO4bhI88JD4IYueBh2vZL7oCRmcD4i9SizJS1EfI/9nQK',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,4,'Demo Staff 092','demo-staff-092','demo-staff-092@local.test',NULL,'$2y$12$iHVjbvU27QZSbGBrTkpayOOXwejQ.PS.d636xmdUMcFyrvILYm./.',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,4,'Demo Staff 093','demo-staff-093','demo-staff-093@local.test',NULL,'$2y$12$ZbBcMtyq2ly8R5J5KBmoaOFJiGUifNM4lTPPtyKtmYhcjA.QazC4m',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,4,'Demo Staff 094','demo-staff-094','demo-staff-094@local.test',NULL,'$2y$12$EFIiVfMvAAnEb.Shh.gYSOyqNMw6.Eamzj0ICPLuwfTcz/KNgIXSa',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,4,'Demo Staff 095','demo-staff-095','demo-staff-095@local.test',NULL,'$2y$12$Tbo93wt.C83gSgppH0Tbl.1deCKb8D9OIsL3Oe9auwpICBJx3Igui',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,4,'Demo Staff 096','demo-staff-096','demo-staff-096@local.test',NULL,'$2y$12$19KCQHHpCXkxi9y.3892PuMJoYtvgOxDUJSlKGT7x.qFjMMOWskZK',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,4,'Demo Staff 097','demo-staff-097','demo-staff-097@local.test',NULL,'$2y$12$SoipxCcLSeF6LCtpFUeCxOb9caRoibNn6V9znEoYomUv/yY7Kew9e',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,4,'Demo Staff 098','demo-staff-098','demo-staff-098@local.test',NULL,'$2y$12$6H22X800M9sAJoSbhdLqY.w6Nm6GS/V/nSMnqOYc7DJp0EHr/0L7.',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(103,4,'Demo Staff 099','demo-staff-099','demo-staff-099@local.test',NULL,'$2y$12$XFHjNbR.CuYc5M6GWALopOyd45M6CJrD9hljsU3gGdxkfM9uEy8A.',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(104,4,'Demo Staff 100','demo-staff-100','demo-staff-100@local.test',NULL,'$2y$12$moRe4lcfNonH66ZCHWaip.Nv1yWCYY8gvXMWmlfK.bYxVtiPMDrU.',1,NULL,NULL,0,NULL,NULL,1,NULL,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stock_lot_metadata_changes`
--

DROP TABLE IF EXISTS `warehouse_stock_lot_metadata_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stock_lot_metadata_changes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_stock_lot_id` bigint(20) unsigned NOT NULL,
  `changed_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`changed_fields`)),
  `before_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`before_snapshot`)),
  `after_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`after_snapshot`)),
  `change_reason` varchar(1000) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wsl_meta_lot_date_idx` (`warehouse_stock_lot_id`,`created_at`),
  KEY `wsl_meta_user_fk` (`created_by`),
  CONSTRAINT `wsl_meta_lot_fk` FOREIGN KEY (`warehouse_stock_lot_id`) REFERENCES `warehouse_stock_lots` (`id`),
  CONSTRAINT `wsl_meta_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stock_lot_metadata_changes`
--

LOCK TABLES `warehouse_stock_lot_metadata_changes` WRITE;
/*!40000 ALTER TABLE `warehouse_stock_lot_metadata_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `warehouse_stock_lot_metadata_changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stock_lot_movements`
--

DROP TABLE IF EXISTS `warehouse_stock_lot_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stock_lot_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_stock_lot_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `movement_type` varchar(60) NOT NULL,
  `direction` varchar(10) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `quantity_before` decimal(18,4) unsigned NOT NULL,
  `quantity_after` decimal(18,4) unsigned NOT NULL,
  `unit_cost` decimal(18,4) unsigned NOT NULL,
  `unit` varchar(30) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `warehouse_stock_lot_movements_created_by_foreign` (`created_by`),
  KEY `warehouse_lot_movement_lot_date_index` (`warehouse_stock_lot_id`,`occurred_at`),
  KEY `warehouse_lot_movement_material_date_index` (`raw_material_id`,`occurred_at`),
  KEY `warehouse_stock_lot_movements_movement_type_index` (`movement_type`),
  KEY `warehouse_lot_movement_reference_index` (`reference_type`,`reference_id`),
  CONSTRAINT `warehouse_stock_lot_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lot_movements_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `warehouse_stock_lot_movements_warehouse_stock_lot_id_foreign` FOREIGN KEY (`warehouse_stock_lot_id`) REFERENCES `warehouse_stock_lots` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stock_lot_movements`
--

LOCK TABLES `warehouse_stock_lot_movements` WRITE;
/*!40000 ALTER TABLE `warehouse_stock_lot_movements` DISABLE KEYS */;
INSERT INTO `warehouse_stock_lot_movements` VALUES (2,2,2,'warehouse_adjustment_in','in',10.0000,0.0000,10.0000,1.0000,'pcs','App\\Models\\StockMovement',3,'2026-10-06 20:02:30','Warehouse adjustment IN created LOT-ADJ-20261007-000002. | Local test stock',1,'2026-10-06 20:02:30','2026-10-06 20:02:30'),(3,2,2,'transfer_out','out',10.0000,10.0000,0.0000,1.0000,'pcs','App\\Models\\StockTransfer',2,'2026-10-06 20:05:32','FEFO lot allocation: LOT-ADJ-20261007-000002 | Expiry: unknown | Local test recipe stock transfer',1,'2026-10-06 20:05:32','2026-10-06 20:05:32'),(14,13,4,'purchase_receive','in',20.0000,0.0000,20.0000,1.0000,'kg','App\\Models\\PurchaseOrderReceipt',8,'2026-10-08 09:01:58','Lot created from GRN-20261008-000008 | Lot: LOT-20261008-000013',1,'2026-10-08 03:01:58','2026-10-08 03:01:58'),(15,14,4,'purchase_receive','in',100.0000,0.0000,100.0000,1.0000,'kg','App\\Models\\PurchaseOrderReceipt',9,'2026-10-11 06:23:36','Lot created from GRN-20261011-000009 | Lot: LOT-20261011-000014',1,'2026-10-11 00:23:36','2026-10-11 00:23:36'),(19,18,5,'purchase_receive','in',100.0000,0.0000,100.0000,1.0000,'kg','App\\Models\\PurchaseOrderReceipt',13,'2026-10-11 06:26:02','Lot created from GRN-20261011-000013 | Lot: LOT-20261011-000018',1,'2026-10-11 00:26:02','2026-10-11 00:26:02'),(20,18,5,'transfer_out','out',100.0000,100.0000,0.0000,1.0000,'kg','App\\Models\\StockTransfer',5,'2026-10-11 00:26:57','FEFO lot allocation: LOT-20261011-000018 | Expiry: unknown',1,'2026-10-11 00:26:57','2026-10-11 00:26:57'),(21,13,4,'transfer_out','out',20.0000,20.0000,0.0000,1.0000,'kg','App\\Models\\StockTransfer',5,'2026-10-11 00:26:57','FEFO lot allocation: LOT-20261008-000013 | Expiry: unknown',1,'2026-10-11 00:26:58','2026-10-11 00:26:58'),(22,14,4,'transfer_out','out',100.0000,100.0000,0.0000,1.0000,'kg','App\\Models\\StockTransfer',5,'2026-10-11 00:26:57','FEFO lot allocation: LOT-20261011-000014 | Expiry: unknown',1,'2026-10-11 00:26:58','2026-10-11 00:26:58'),(24,20,6,'purchase_receive','in',100.0000,0.0000,100.0000,1.0000,'kg','App\\Models\\PurchaseOrderReceipt',15,'2026-10-13 07:27:44','Lot created from GRN-20261013-000015 | Lot: LOT-20261013-000020',1,'2026-10-13 01:27:44','2026-10-13 01:27:44'),(25,20,6,'transfer_out','out',100.0000,100.0000,0.0000,1.0000,'kg','App\\Models\\StockTransfer',6,'2026-10-13 01:28:05','FEFO lot allocation: LOT-20261013-000020 | Expiry: unknown',1,'2026-10-13 01:28:05','2026-10-13 01:28:05'),(26,21,7,'purchase_receive','in',100.0000,0.0000,100.0000,1.0000,'kg','App\\Models\\PurchaseOrderReceipt',16,'2026-10-14 08:25:48','Lot created from GRN-20261014-000016 | Lot: LOT-20261014-000021',1,'2026-10-14 02:25:48','2026-10-14 02:25:48'),(27,22,7,'purchase_receive','in',10.0000,0.0000,10.0000,1.0000,'kg','App\\Models\\PurchaseOrderReceipt',17,'2026-10-26 08:14:10','Lot created from GRN-20261026-000017 | Lot: LOT-20261026-000022',1,'2026-10-26 02:14:10','2026-10-26 02:14:10'),(28,21,7,'transfer_out','out',100.0000,100.0000,0.0000,1.0000,'kg','App\\Models\\StockTransfer',7,'2026-10-26 02:14:44','FEFO lot allocation: LOT-20261014-000021 | Expiry: unknown',1,'2026-10-26 02:14:44','2026-10-26 02:14:44'),(29,22,7,'transfer_out','out',10.0000,10.0000,0.0000,1.0000,'kg','App\\Models\\StockTransfer',7,'2026-10-26 02:14:44','FEFO lot allocation: LOT-20261026-000022 | Expiry: unknown',1,'2026-10-26 02:14:44','2026-10-26 02:14:44'),(30,23,7,'purchase_receive','in',100.0000,0.0000,100.0000,1.0000,'kg','App\\Models\\PurchaseOrderReceipt',18,'2026-10-26 10:52:32','Lot created from GRN-20261026-000018 | Lot: LOT-20261026-000023',1,'2026-10-26 04:52:32','2026-10-26 04:52:32'),(31,23,7,'transfer_out','out',100.0000,100.0000,0.0000,1.0000,'kg','App\\Models\\StockTransfer',9,'2026-10-26 04:52:56','FEFO lot allocation: LOT-20261026-000023 | Expiry: unknown',1,'2026-10-26 04:52:56','2026-10-26 04:52:56');
/*!40000 ALTER TABLE `warehouse_stock_lot_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stock_lots`
--

DROP TABLE IF EXISTS `warehouse_stock_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stock_lots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lot_no` varchar(80) NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `source_type` varchar(60) NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_receipt_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_receipt_item_id` bigint(20) unsigned DEFAULT NULL,
  `supplier_batch_no` varchar(120) DEFAULT NULL,
  `manufactured_at` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `received_quantity` decimal(18,4) unsigned NOT NULL,
  `remaining_quantity` decimal(18,4) unsigned NOT NULL,
  `unit_cost` decimal(18,4) unsigned NOT NULL,
  `unit` varchar(30) NOT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `manual_notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouse_stock_lots_lot_no_unique` (`lot_no`),
  KEY `warehouse_stock_lots_created_by_foreign` (`created_by`),
  KEY `warehouse_stock_lots_updated_by_foreign` (`updated_by`),
  KEY `warehouse_lot_material_expiry_index` (`raw_material_id`,`expiry_date`),
  KEY `warehouse_lot_material_remaining_index` (`raw_material_id`,`remaining_quantity`),
  KEY `warehouse_lot_supplier_material_index` (`supplier_id`,`raw_material_id`),
  KEY `warehouse_stock_lots_purchase_order_id_index` (`purchase_order_id`),
  KEY `warehouse_stock_lots_purchase_order_receipt_id_index` (`purchase_order_receipt_id`),
  KEY `warehouse_stock_lots_purchase_order_receipt_item_id_index` (`purchase_order_receipt_item_id`),
  KEY `warehouse_stock_lots_source_type_index` (`source_type`),
  CONSTRAINT `warehouse_stock_lots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_purchase_order_receipt_id_foreign` FOREIGN KEY (`purchase_order_receipt_id`) REFERENCES `purchase_order_receipts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_purchase_order_receipt_item_id_foreign` FOREIGN KEY (`purchase_order_receipt_item_id`) REFERENCES `purchase_order_receipt_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `warehouse_stock_lots_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stock_lots`
--

LOCK TABLES `warehouse_stock_lots` WRITE;
/*!40000 ALTER TABLE `warehouse_stock_lots` DISABLE KEYS */;
INSERT INTO `warehouse_stock_lots` VALUES (2,'LOT-ADJ-20261007-000002',2,'warehouse_adjustment_in',NULL,NULL,NULL,NULL,NULL,NULL,NULL,10.0000,0.0000,1.0000,'pcs','2026-10-06 20:02:30','Created from warehouse adjustment IN. | Local test stock',NULL,1,1,'2026-10-06 20:02:30','2026-10-06 20:05:32'),(13,'LOT-20261008-000013',4,'purchase_receive',1,3,8,8,NULL,NULL,NULL,20.0000,0.0000,1.0000,'kg','2026-10-08 09:01:58','Created from purchase receive GRN-20261008-000008 | Purchase order #3 | Receipt item #8',NULL,1,1,'2026-10-08 03:01:58','2026-10-11 00:26:58'),(14,'LOT-20261011-000014',4,'purchase_receive',1,4,9,9,NULL,NULL,NULL,100.0000,0.0000,1.0000,'kg','2026-10-11 06:23:36','Created from purchase receive GRN-20261011-000009 | Purchase order #4 | Receipt item #9',NULL,1,1,'2026-10-11 00:23:36','2026-10-11 00:26:58'),(18,'LOT-20261011-000018',5,'purchase_receive',1,7,13,13,NULL,NULL,NULL,100.0000,0.0000,1.0000,'kg','2026-10-11 06:26:02','Created from purchase receive GRN-20261011-000013 | Purchase order #7 | Receipt item #13',NULL,1,1,'2026-10-11 00:26:02','2026-10-11 00:26:57'),(20,'LOT-20261013-000020',6,'purchase_receive',1,8,15,15,NULL,NULL,NULL,100.0000,0.0000,1.0000,'kg','2026-10-13 07:27:44','Created from purchase receive GRN-20261013-000015 | Purchase order #8 | Receipt item #15',NULL,1,1,'2026-10-13 01:27:44','2026-10-13 01:28:05'),(21,'LOT-20261014-000021',7,'purchase_receive',1,9,16,16,NULL,NULL,NULL,100.0000,0.0000,1.0000,'kg','2026-10-14 08:25:48','Created from purchase receive GRN-20261014-000016 | Purchase order #9 | Receipt item #16',NULL,1,1,'2026-10-14 02:25:48','2026-10-26 02:14:44'),(22,'LOT-20261026-000022',7,'purchase_receive',1,10,17,17,NULL,NULL,NULL,10.0000,0.0000,1.0000,'kg','2026-10-26 08:14:10','Created from purchase receive GRN-20261026-000017 | Purchase order #10 | Receipt item #17',NULL,1,1,'2026-10-26 02:14:10','2026-10-26 02:14:44'),(23,'LOT-20261026-000023',7,'purchase_receive',101,111,18,18,NULL,NULL,NULL,100.0000,0.0000,1.0000,'kg','2026-10-26 10:52:32','Created from purchase receive GRN-20261026-000018 | Purchase order #111 | Receipt item #18',NULL,1,1,'2026-10-26 04:52:32','2026-10-26 04:52:56');
/*!40000 ALTER TABLE `warehouse_stock_lots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stocks`
--

DROP TABLE IF EXISTS `warehouse_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `average_unit_cost` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `last_received_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouse_stocks_raw_material_unique` (`raw_material_id`),
  KEY `warehouse_stocks_created_by_foreign` (`created_by`),
  KEY `warehouse_stocks_updated_by_foreign` (`updated_by`),
  KEY `warehouse_stocks_quantity_last_received_at_index` (`quantity`,`last_received_at`),
  CONSTRAINT `warehouse_stocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stocks_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `warehouse_stocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stocks`
--

LOCK TABLES `warehouse_stocks` WRITE;
/*!40000 ALTER TABLE `warehouse_stocks` DISABLE KEYS */;
INSERT INTO `warehouse_stocks` VALUES (1,1,10.0000,1.0000,'2026-10-06 19:26:35',1,1,'2026-10-06 19:26:35','2026-10-06 19:26:35',NULL),(2,2,0.0000,1.0000,'2026-10-06 20:02:30',1,1,'2026-10-06 20:01:36','2026-10-06 20:05:32',NULL),(3,3,50.0000,1.4000,'2026-10-06 22:53:10',1,1,'2026-10-06 22:53:10','2026-10-06 22:53:10',NULL),(4,4,0.0000,1.0000,'2026-10-11 06:23:36',1,1,'2026-10-08 02:56:05','2026-10-11 00:26:58',NULL),(5,5,0.0000,1.0000,'2026-10-11 06:26:02',1,1,'2026-10-11 00:25:20','2026-10-11 00:26:57',NULL),(6,6,0.0000,1.0000,'2026-10-13 07:27:44',1,1,'2026-10-13 01:22:09','2026-10-13 01:28:05',NULL),(7,7,0.0000,1.0000,'2026-10-26 10:52:32',1,1,'2026-10-14 02:24:55','2026-10-26 04:52:56',NULL),(8,8,30.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(9,9,31.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(10,10,32.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(11,11,33.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(12,12,34.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(13,13,35.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(14,14,36.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(15,15,37.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(16,16,38.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(17,17,39.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(18,18,40.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(19,19,41.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(20,20,42.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(21,21,43.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(22,22,44.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(23,23,45.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(24,24,46.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(25,25,47.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(26,26,48.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(27,27,49.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(28,28,50.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(29,29,51.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(30,30,52.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(31,31,53.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(32,32,54.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(33,33,55.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(34,34,56.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(35,35,57.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(36,36,58.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(37,37,59.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(38,38,60.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(39,39,61.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(40,40,62.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(41,41,63.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(42,42,64.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(43,43,65.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(44,44,66.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(45,45,67.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(46,46,68.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(47,47,69.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(48,48,70.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(49,49,71.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(50,50,72.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(51,51,73.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(52,52,74.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(53,53,75.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(54,54,76.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(55,55,77.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(56,56,78.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(57,57,79.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(58,58,80.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(59,59,81.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(60,60,82.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(61,61,83.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(62,62,84.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(63,63,85.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(64,64,86.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(65,65,87.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(66,66,88.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(67,67,89.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(68,68,90.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(69,69,91.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(70,70,92.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(71,71,93.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(72,72,94.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(73,73,95.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(74,74,96.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(75,75,97.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(76,76,98.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(77,77,99.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(78,78,30.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(79,79,31.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(80,80,32.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(81,81,33.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(82,82,34.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(83,83,35.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(84,84,36.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(85,85,37.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(86,86,38.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(87,87,39.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(88,88,40.0000,12.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(89,89,41.0000,13.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(90,90,42.0000,14.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(91,91,43.0000,15.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(92,92,44.0000,16.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(93,93,45.0000,17.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(94,94,46.0000,18.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(95,95,47.0000,19.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(96,96,48.0000,20.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(97,97,49.0000,21.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(98,98,50.0000,22.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(99,99,51.0000,23.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(100,100,52.0000,24.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(101,101,53.0000,25.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(102,102,54.0000,26.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(103,103,55.0000,27.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(104,104,56.0000,28.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(105,105,57.0000,29.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(106,106,58.0000,30.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(107,107,59.0000,31.0000,'2026-10-26 04:43:58',1,1,'2026-10-26 04:43:58','2026-10-26 04:43:58',NULL),(108,108,0.0000,0.0000,NULL,1,1,'2026-10-26 05:16:38','2026-10-26 05:22:19','2026-10-26 05:22:19'),(109,109,400.0000,1.0000,'2026-10-26 05:22:48',1,1,'2026-10-26 05:22:48','2026-10-26 05:22:48',NULL),(110,110,0.0000,0.0000,NULL,1,1,'2026-10-26 05:28:50','2026-10-26 05:36:56','2026-10-26 05:36:56'),(111,111,0.0000,0.0000,NULL,1,1,'2026-10-26 05:45:24','2026-10-26 05:45:24',NULL);
/*!40000 ALTER TABLE `warehouse_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'tenant_sopno_restaurant'
--

--
-- Dumping routines for database 'tenant_sopno_restaurant'
--

--
-- Current Database: `tenant_bongo_sylhet`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `tenant_bongo_sylhet` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `tenant_bongo_sylhet`;

--
-- Table structure for table `accounting_payment_mappings`
--

DROP TABLE IF EXISTS `accounting_payment_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_payment_mappings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_method` varchar(50) NOT NULL,
  `ledger_account_id` bigint(20) unsigned NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounting_payment_mappings_payment_method_unique` (`payment_method`),
  KEY `accounting_payment_ledger_idx` (`ledger_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounting_payment_mappings`
--

LOCK TABLES `accounting_payment_mappings` WRITE;
/*!40000 ALTER TABLE `accounting_payment_mappings` DISABLE KEYS */;
INSERT INTO `accounting_payment_mappings` VALUES (1,'cash',2,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41'),(2,'bank_transfer',3,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41'),(3,'card',4,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41'),(4,'bkash',5,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41'),(5,'nagad',6,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41'),(6,'mixed',7,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41'),(7,'other',8,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41');
/*!40000 ALTER TABLE `accounting_payment_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `add_ons`
--

DROP TABLE IF EXISTS `add_ons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `add_ons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `add_on_name` varchar(150) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `add_on_name_unique` (`add_on_name`),
  KEY `add_ons_add_on_name_index` (`add_on_name`),
  KEY `add_ons_is_available_index` (`is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `add_ons`
--

LOCK TABLES `add_ons` WRITE;
/*!40000 ALTER TABLE `add_ons` DISABLE KEYS */;
/*!40000 ALTER TABLE `add_ons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_breaks`
--

DROP TABLE IF EXISTS `attendance_breaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendance_breaks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_id` bigint(20) unsigned NOT NULL,
  `break_start_at` datetime NOT NULL,
  `break_end_at` datetime DEFAULT NULL,
  `duration_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_breaks_created_by_foreign` (`created_by`),
  KEY `attendance_breaks_updated_by_foreign` (`updated_by`),
  KEY `attendance_breaks_attendance_id_break_start_at_index` (`attendance_id`,`break_start_at`),
  KEY `attendance_breaks_attendance_id_break_end_at_index` (`attendance_id`,`break_end_at`),
  CONSTRAINT `attendance_breaks_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `attendance_breaks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendance_breaks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_breaks`
--

LOCK TABLES `attendance_breaks` WRITE;
/*!40000 ALTER TABLE `attendance_breaks` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendance_breaks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `shift_schedule_id` bigint(20) unsigned DEFAULT NULL,
  `shift_schedule_override_id` bigint(20) unsigned DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `scheduled_start_at` datetime NOT NULL,
  `scheduled_end_at` datetime NOT NULL,
  `grace_minutes` smallint(5) unsigned NOT NULL DEFAULT 0,
  `scheduled_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `check_in_at` datetime DEFAULT NULL,
  `check_out_at` datetime DEFAULT NULL,
  `auto_checked_out` tinyint(1) NOT NULL DEFAULT 0,
  `auto_checkout_reason` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'scheduled',
  `late_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `worked_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `break_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `overtime_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `early_leave_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `is_manual` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_employee_date_unique` (`employee_id`,`attendance_date`),
  KEY `attendances_shift_schedule_id_foreign` (`shift_schedule_id`),
  KEY `attendances_shift_schedule_override_id_foreign` (`shift_schedule_override_id`),
  KEY `attendances_created_by_foreign` (`created_by`),
  KEY `attendances_updated_by_foreign` (`updated_by`),
  KEY `attendances_attendance_date_status_index` (`attendance_date`,`status`),
  KEY `attendances_scheduled_start_at_scheduled_end_at_index` (`scheduled_start_at`,`scheduled_end_at`),
  CONSTRAINT `attendances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `attendances_shift_schedule_id_foreign` FOREIGN KEY (`shift_schedule_id`) REFERENCES `shift_schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_shift_schedule_override_id_foreign` FOREIGN KEY (`shift_schedule_override_id`) REFERENCES `shift_schedule_overrides` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `attendances_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `module` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) unsigned DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_module_action_index` (`module`,`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `audit_logs_model_type_model_id_index` (`model_type`,`model_id`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chart_of_accounts`
--

DROP TABLE IF EXISTS `chart_of_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chart_of_accounts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `system_key` varchar(80) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `normal_balance` varchar(10) NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `allow_manual_posting` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chart_of_accounts_code_unique` (`code`),
  UNIQUE KEY `chart_of_accounts_system_key_unique` (`system_key`),
  KEY `coa_account_type_idx` (`account_type`),
  KEY `coa_parent_idx` (`parent_id`),
  KEY `coa_active_idx` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chart_of_accounts`
--

LOCK TABLES `chart_of_accounts` WRITE;
/*!40000 ALTER TABLE `chart_of_accounts` DISABLE KEYS */;
INSERT INTO `chart_of_accounts` VALUES (1,'1000','assets','Assets','asset','debit',NULL,1,1,0,'Root asset account.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(2,'1010','cash','Cash in Hand','asset','debit',1,1,1,1,'Physical cash held by the restaurant.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(3,'1020','bank','Bank Account','asset','debit',1,1,1,1,'Primary bank account / bank balance.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(4,'1030','card_settlement','Card Settlement Account','asset','debit',1,1,1,1,'Card/POS settlement receivable or clearing balance.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(5,'1040','bkash_wallet','bKash Wallet','asset','debit',1,1,1,1,'bKash merchant/business wallet balance.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(6,'1050','nagad_wallet','Nagad Wallet','asset','debit',1,1,1,1,'Nagad merchant/business wallet balance.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(7,'1060','mixed_payment_clearing','Mixed Payment Clearing','asset','debit',1,1,1,1,'Temporary asset account for mixed payments until split posting is available.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(8,'1070','other_payment_clearing','Other Payment Clearing','asset','debit',1,1,1,1,'Temporary asset account for other payment methods.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(9,'1100','accounts_receivable','Accounts Receivable','asset','debit',1,1,1,1,'Amounts owed by customers.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(10,'1200','inventory','Inventory','asset','debit',1,1,1,1,'Food ingredients, raw materials and stock value.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(11,'2000','liabilities','Liabilities','liability','credit',NULL,1,1,0,'Root liability account.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(12,'2010','accounts_payable','Accounts Payable','liability','credit',11,1,1,1,'Amounts owed to suppliers and vendors.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(13,'2100','tax_payable','Tax Payable','liability','credit',11,1,1,1,'Taxes collected or accrued and payable.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(14,'3000','equity','Equity','equity','credit',NULL,1,1,0,'Root equity account.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(15,'3010','owners_equity','Owner\'s Equity','equity','credit',14,1,1,1,'Owner capital and equity contributions.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(16,'3020','retained_earnings','Retained Earnings','equity','credit',14,1,1,0,'Accumulated retained profit/loss.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(17,'4000','revenue','Revenue','revenue','credit',NULL,1,1,0,'Root revenue account.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(18,'4010','restaurant_sales_revenue','Restaurant Sales Revenue','revenue','credit',17,1,1,1,'Normal restaurant/POS food and beverage sales.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(19,'4100','other_income','Other Income','revenue','credit',17,1,1,0,'Parent account for non-POS other income.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(20,'4110','bottle_crate_income','Bottle / Crate Income','revenue','credit',19,1,1,1,'Income from bottle and crate sales.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(21,'4120','waste_paper_income','Waste Paper Income','revenue','credit',19,1,1,1,'Income from waste paper sales.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(22,'4130','carton_income','Carton Income','revenue','credit',19,1,1,1,'Income from carton sales.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(23,'4140','scrap_income','Scrap Income','revenue','credit',19,1,1,1,'Income from scrap sales.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(24,'4150','used_oil_income','Used Cooking Oil Income','revenue','credit',19,1,1,1,'Income from selling used cooking oil.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(25,'4160','recyclable_waste_income','Recyclable Waste Income','revenue','credit',19,1,1,1,'Income from other recyclable waste.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(26,'4190','misc_other_income','Miscellaneous Other Income','revenue','credit',19,1,1,1,'Miscellaneous non-POS income.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(27,'5000','cogs','Cost of Goods Sold','cogs','debit',NULL,1,1,0,'Root cost of goods sold account.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(28,'5010','food_ingredient_cogs','Food & Ingredient COGS','cogs','debit',27,1,1,1,'Consumed food ingredient and recipe cost.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(29,'6000','operating_expenses','Operating Expenses','expense','debit',NULL,1,1,0,'Root operating expense account.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(30,'6100','salary_wages_expense','Salary & Wages Expense','expense','debit',29,1,1,1,'Employee salary and wage expense.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(31,'6200','rent_expense','Rent Expense','expense','debit',29,1,1,1,'Restaurant premises rent expense.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(32,'6300','utilities_expense','Utilities Expense','expense','debit',29,1,1,1,'Electricity, gas, water and utility expenses.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(33,'6400','maintenance_repairs_expense','Maintenance & Repairs Expense','expense','debit',29,1,1,1,'Maintenance and repair costs.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(34,'6500','marketing_expense','Marketing Expense','expense','debit',29,1,1,1,'Advertising and marketing expense.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(35,'6600','delivery_transport_expense','Delivery / Transport Expense','expense','debit',29,1,1,1,'Delivery, courier and transport expense.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(36,'6700','cleaning_supplies_expense','Cleaning & Supplies Expense','expense','debit',29,1,1,1,'Cleaning materials and operating supplies.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(37,'6900','misc_operating_expense','Miscellaneous Operating Expense','expense','debit',29,1,1,1,'Other operating expenses not mapped elsewhere.',NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(38,'1250','purchase_return_clearing','Purchase Return Clearing','asset','debit',1,1,1,1,'Temporary supplier recovery asset created when inventory is physically returned, before the return is fully settled.',NULL,NULL,'2026-10-08 02:21:42','2026-10-08 02:21:42',NULL),(39,'1260','supplier_credit_receivable','Supplier Credit Receivable','asset','debit',1,1,1,1,'Reusable supplier credit available for application against another eligible purchase order.',NULL,NULL,'2026-10-08 02:21:42','2026-10-08 02:21:42',NULL),(40,'1270','supplier_refund_receivable','Supplier Refund Receivable','asset','debit',1,1,1,1,'Cash/bank refund due from a supplier but not yet collected.',NULL,NULL,'2026-10-08 02:21:42','2026-10-08 02:21:42',NULL),(41,'4200','purchase_return_valuation_gain','Purchase Return Valuation Gain','revenue','credit',17,1,1,1,'Gain when supplier return credit exceeds the inventory carrying value removed.',NULL,NULL,'2026-10-08 02:21:42','2026-10-08 02:21:42',NULL),(42,'6800','purchase_return_valuation_loss','Purchase Return Valuation Loss','expense','debit',29,1,1,1,'Loss when inventory carrying value removed exceeds the supplier return credit.',NULL,NULL,'2026-10-08 02:21:42','2026-10-08 02:21:42',NULL),(43,'6050','sales_discount','Sales Discount','expense','debit',29,1,1,1,'Discount given to customers on restaurant sales.',NULL,NULL,'2026-10-08 02:21:42','2026-10-08 02:21:42',NULL),(44,'4020','service_charge_income','Service Charge Income','revenue','credit',17,1,1,1,'Service charge collected from restaurant customers.',NULL,NULL,'2026-10-08 02:21:42','2026-10-08 02:21:42',NULL);
/*!40000 ALTER TABLE `chart_of_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `last_visit_at` datetime DEFAULT NULL,
  `total_orders` int(10) unsigned NOT NULL DEFAULT 0,
  `total_spent` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customers_phone_unique` (`phone`),
  UNIQUE KEY `customers_email_unique` (`email`),
  KEY `customers_name_index` (`name`),
  KEY `customers_last_visit_at_index` (`last_visit_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `joining_date` date NOT NULL,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `current_status` varchar(30) NOT NULL DEFAULT 'none',
  `status_updated_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employees_user_id_unique` (`user_id`),
  KEY `employees_created_by_foreign` (`created_by`),
  KEY `employees_updated_by_foreign` (`updated_by`),
  KEY `employees_joining_date_current_status_index` (`joining_date`,`current_status`),
  KEY `employees_current_status_status_updated_at_index` (`current_status`,`status_updated_at`),
  KEY `employees_phone_index` (`phone`),
  KEY `employees_current_status_index` (`current_status`),
  CONSTRAINT `employees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employees_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expense_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `ledger_account_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expense_categories_name_unique` (`name`),
  KEY `expense_categories_is_active_index` (`is_active`),
  KEY `expense_categories_ledger_idx` (`ledger_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `expense_category_id` bigint(20) unsigned NOT NULL,
  `expense_date` datetime NOT NULL,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_source_unique` (`source_type`,`source_id`),
  KEY `expenses_expense_category_id_foreign` (`expense_category_id`),
  KEY `expenses_updated_by_foreign` (`updated_by`),
  KEY `expenses_expense_date_expense_category_id_index` (`expense_date`,`expense_category_id`),
  KEY `expenses_expense_date_payment_method_index` (`expense_date`,`payment_method`),
  KEY `expenses_created_by_expense_date_index` (`created_by`,`expense_date`),
  KEY `expenses_expense_date_index` (`expense_date`),
  KEY `expenses_payment_method_index` (`payment_method`),
  KEY `expenses_source_type_idx` (`source_type`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `expenses_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income_categories`
--

DROP TABLE IF EXISTS `income_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `income_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `ledger_account_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `income_categories_active_idx` (`is_active`),
  KEY `income_categories_ledger_idx` (`ledger_account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income_categories`
--

LOCK TABLES `income_categories` WRITE;
/*!40000 ALTER TABLE `income_categories` DISABLE KEYS */;
INSERT INTO `income_categories` VALUES (1,'Bottle / Crate Sale','Income from selling accumulated bottles, empty crates or similar reusable containers.',1,20,NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(2,'Waste Paper Sale','Income from selling accumulated waste paper.',1,21,NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(3,'Carton Sale','Income from selling accumulated cartons or packaging materials.',1,22,NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(4,'Scrap Sale','Income from selling scrap metal, plastic or other scrap materials.',1,23,NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(5,'Used Cooking Oil Sale','Income from selling used cooking oil where legally and operationally applicable.',1,24,NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(6,'Recyclable Waste Sale','Income from selling other recyclable restaurant waste.',1,25,NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL),(7,'Miscellaneous Other Income','Other non-POS restaurant income that does not belong to another category.',1,26,NULL,NULL,'2026-10-08 02:21:41','2026-10-08 02:21:41',NULL);
/*!40000 ALTER TABLE `income_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incomes`
--

DROP TABLE IF EXISTS `incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `incomes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `income_category_id` bigint(20) unsigned NOT NULL,
  `category_name_snapshot` varchar(150) NOT NULL,
  `income_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference` varchar(150) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `incomes_source_unique` (`source_type`,`source_id`),
  KEY `incomes_category_idx` (`income_category_id`),
  KEY `incomes_date_idx` (`income_date`),
  KEY `incomes_payment_method_idx` (`payment_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incomes`
--

LOCK TABLES `incomes` WRITE;
/*!40000 ALTER TABLE `incomes` DISABLE KEYS */;
/*!40000 ALTER TABLE `incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_wastage_items`
--

DROP TABLE IF EXISTS `inventory_wastage_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_wastage_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `inventory_wastage_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `unit_cost` decimal(18,4) unsigned NOT NULL,
  `total_loss` decimal(18,4) unsigned NOT NULL,
  `restaurant_quantity_before` decimal(18,4) unsigned NOT NULL,
  `restaurant_quantity_after` decimal(18,4) unsigned NOT NULL,
  `reason` varchar(60) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_wastage_items_raw_material_id_index` (`raw_material_id`),
  KEY `inventory_wastage_items_reason_index` (`reason`),
  KEY `wastage_item_header_material_index` (`inventory_wastage_id`,`raw_material_id`),
  CONSTRAINT `inventory_wastage_items_inventory_wastage_id_foreign` FOREIGN KEY (`inventory_wastage_id`) REFERENCES `inventory_wastages` (`id`),
  CONSTRAINT `inventory_wastage_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_wastage_items`
--

LOCK TABLES `inventory_wastage_items` WRITE;
/*!40000 ALTER TABLE `inventory_wastage_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_wastage_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_wastages`
--

DROP TABLE IF EXISTS `inventory_wastages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_wastages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `wastage_no` varchar(80) NOT NULL,
  `wasted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_loss` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `notes` text DEFAULT NULL,
  `recorded_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_wastages_wastage_no_unique` (`wastage_no`),
  KEY `inventory_wastages_created_by_foreign` (`created_by`),
  KEY `inventory_wastages_wasted_at_index` (`wasted_at`),
  KEY `inventory_wastages_recorded_by_index` (`recorded_by`),
  CONSTRAINT `inventory_wastages_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_wastages_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_wastages`
--

LOCK TABLES `inventory_wastages` WRITE;
/*!40000 ALTER TABLE `inventory_wastages` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_wastages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_entries`
--

DROP TABLE IF EXISTS `journal_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `journal_number` varchar(40) DEFAULT NULL,
  `journal_date` date NOT NULL,
  `entry_type` varchar(20) NOT NULL DEFAULT 'manual',
  `status` varchar(20) NOT NULL DEFAULT 'posted',
  `description` text NOT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `source_revision` int(10) unsigned DEFAULT NULL,
  `source_hash` varchar(64) DEFAULT NULL,
  `reversal_of_id` bigint(20) unsigned DEFAULT NULL,
  `total_debit` decimal(16,2) NOT NULL,
  `total_credit` decimal(16,2) NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `posted_by` bigint(20) unsigned NOT NULL,
  `reversed_at` timestamp NULL DEFAULT NULL,
  `reversed_by` bigint(20) unsigned DEFAULT NULL,
  `reversal_reason` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `journal_source_revision_unique` (`source_type`,`source_id`,`source_revision`),
  UNIQUE KEY `journal_entries_journal_number_unique` (`journal_number`),
  UNIQUE KEY `journal_entries_reversal_of_id_unique` (`reversal_of_id`),
  KEY `journal_entries_date_idx` (`journal_date`),
  KEY `journal_entries_status_idx` (`status`),
  KEY `journal_entries_type_idx` (`entry_type`),
  KEY `journal_entries_source_idx` (`source_type`,`source_id`),
  KEY `journal_entries_source_hash_idx` (`source_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_entries`
--

LOCK TABLES `journal_entries` WRITE;
/*!40000 ALTER TABLE `journal_entries` DISABLE KEYS */;
INSERT INTO `journal_entries` VALUES (1,'JV-202610-000001','2026-10-08','source','posted','Supplier Payment — PO #1','purchase_payment',1,1,'ad7f2329cee5a426ec445a80f67291575ffb5db233cfaa057ea16a410006d1fe',NULL,1.00,1.00,'2026-10-08 08:33:55',1,NULL,NULL,NULL,'2026-10-08 02:33:55','2026-10-08 02:33:55'),(2,'JV-202610-000002','2026-10-08','source','posted','Supplier Payment — PO #1','purchase_payment',2,1,'181d5085e4ace415fe53adc6500460698a3d9df30e94e95e289e5780b8aedbf2',NULL,5.00,5.00,'2026-10-08 08:34:52',1,NULL,NULL,NULL,'2026-10-08 02:34:52','2026-10-08 02:34:52'),(3,'JV-202610-000003','2026-10-08','source','posted','GRN GRN-20261008-000001 — PO #1','purchase_receipt',1,1,'d5cc3f7a00f86a458af933c6150dab1ba1eb9881a9aed9cc1a9769f8f67aa72e',NULL,10.00,10.00,'2026-10-08 08:34:52',1,NULL,NULL,NULL,'2026-10-08 02:34:52','2026-10-08 02:34:52');
/*!40000 ALTER TABLE `journal_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_lines`
--

DROP TABLE IF EXISTS `journal_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `journal_lines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `journal_entry_id` bigint(20) unsigned NOT NULL,
  `ledger_account_id` bigint(20) unsigned NOT NULL,
  `account_code_snapshot` varchar(20) NOT NULL,
  `account_name_snapshot` varchar(150) NOT NULL,
  `account_type_snapshot` varchar(20) NOT NULL,
  `normal_balance_snapshot` varchar(10) NOT NULL,
  `debit` decimal(16,2) NOT NULL DEFAULT 0.00,
  `credit` decimal(16,2) NOT NULL DEFAULT 0.00,
  `memo` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_lines_entry_idx` (`journal_entry_id`),
  KEY `journal_lines_account_idx` (`ledger_account_id`),
  KEY `journal_lines_account_entry_idx` (`ledger_account_id`,`journal_entry_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_lines`
--

LOCK TABLES `journal_lines` WRITE;
/*!40000 ALTER TABLE `journal_lines` DISABLE KEYS */;
INSERT INTO `journal_lines` VALUES (1,1,12,'2010','Accounts Payable','liability','credit',1.00,0.00,'Supplier payable reduced','2026-10-08 02:33:55','2026-10-08 02:33:55'),(2,1,2,'1010','Cash in Hand','asset','debit',0.00,1.00,'Paid via Cash','2026-10-08 02:33:55','2026-10-08 02:33:55'),(3,2,12,'2010','Accounts Payable','liability','credit',5.00,0.00,'Supplier payable reduced','2026-10-08 02:34:52','2026-10-08 02:34:52'),(4,2,2,'1010','Cash in Hand','asset','debit',0.00,5.00,'Paid via Cash','2026-10-08 02:34:52','2026-10-08 02:34:52'),(5,3,10,'1200','Inventory','asset','debit',10.00,0.00,'Inventory received via GRN-20261008-000001','2026-10-08 02:34:52','2026-10-08 02:34:52'),(6,3,12,'2010','Accounts Payable','liability','credit',0.00,10.00,'Supplier liability created from GRN-20261008-000001','2026-10-08 02:34:52','2026-10-08 02:34:52');
/*!40000 ALTER TABLE `journal_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_categories`
--

DROP TABLE IF EXISTS `menu_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_categories_category_name_unique` (`category_name`),
  KEY `menu_categories_is_available_index` (`is_available`),
  KEY `menu_categories_display_order_index` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_categories`
--

LOCK TABLES `menu_categories` WRITE;
/*!40000 ALTER TABLE `menu_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_add_on`
--

DROP TABLE IF EXISTS `menu_item_add_on`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_item_add_on` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned NOT NULL,
  `add_on_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_item_add_on_unique` (`menu_item_id`,`add_on_id`),
  KEY `menu_item_add_on_menu_item_id_index` (`menu_item_id`),
  KEY `menu_item_add_on_add_on_id_index` (`add_on_id`),
  CONSTRAINT `menu_item_add_on_add_on_id_foreign` FOREIGN KEY (`add_on_id`) REFERENCES `add_ons` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `menu_item_add_on_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_add_on`
--

LOCK TABLES `menu_item_add_on` WRITE;
/*!40000 ALTER TABLE `menu_item_add_on` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_item_add_on` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_item_variants`
--

DROP TABLE IF EXISTS `menu_item_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_item_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned NOT NULL,
  `variant_name` varchar(120) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `menu_item_variant_unique` (`menu_item_id`,`variant_name`),
  KEY `menu_item_variants_menu_item_id_is_available_index` (`menu_item_id`,`is_available`),
  KEY `menu_item_variants_is_available_index` (`is_available`),
  CONSTRAINT `menu_item_variants_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_item_variants`
--

LOCK TABLES `menu_item_variants` WRITE;
/*!40000 ALTER TABLE `menu_item_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_item_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_category_id` bigint(20) unsigned NOT NULL,
  `menu_name` varchar(180) NOT NULL,
  `item_type` enum('regular','combo','set_meal') NOT NULL DEFAULT 'regular',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `ingredients` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `preparation_time` int(10) unsigned DEFAULT NULL COMMENT 'Preparation time in minutes',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_menu_category_id_is_available_index` (`menu_category_id`,`is_available`),
  KEY `menu_items_item_type_is_available_index` (`item_type`,`is_available`),
  KEY `menu_items_is_available_index` (`is_available`),
  KEY `menu_items_is_featured_index` (`is_featured`),
  CONSTRAINT `menu_items_menu_category_id_foreign` FOREIGN KEY (`menu_category_id`) REFERENCES `menu_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_08_30_171641_create_tenant_roles_table',1),(2,'2026_08_30_171703_create_tenant_permissions_table',1),(3,'2026_08_30_171718_create_tenant_permission_role_table',1),(4,'2026_08_30_171735_create_tenant_users_table',1),(5,'2026_08_30_171809_create_tenant_password_reset_tokens_table',1),(6,'2026_08_30_171824_create_tenant_personal_access_tokens_table',1),(7,'2026_08_30_171838_create_tenant_menu_categories_table',1),(8,'2026_08_30_171853_create_tenant_menu_items_table',1),(9,'2026_08_30_171908_create_tenant_menu_item_variants_table',1),(10,'2026_08_30_171929_create_tenant_add_ons_table',1),(11,'2026_08_30_171945_create_tenant_menu_item_add_on_table',1),(12,'2026_08_30_172004_create_tenant_customers_table',1),(13,'2026_08_30_172021_create_tenant_restaurant_tables_table',1),(14,'2026_08_30_172414_create_tenant_employees_table',1),(15,'2026_08_30_172433_create_tenant_shift_schedules_table',1),(16,'2026_08_30_172523_create_tenant_shift_schedule_overrides_table',1),(17,'2026_08_30_172538_create_tenant_attendances_table',1),(18,'2026_08_30_172555_create_tenant_attendance_breaks_table',1),(19,'2026_08_30_172611_create_tenant_raw_materials_table',1),(20,'2026_08_30_172628_create_tenant_warehouse_stocks_table',1),(21,'2026_08_30_172644_create_tenant_restaurant_stocks_table',1),(22,'2026_08_30_172658_create_tenant_suppliers_table',1),(23,'2026_08_30_172712_create_tenant_purchase_orders_table',1),(24,'2026_08_30_172728_create_tenant_purchase_order_items_table',1),(25,'2026_08_30_172746_create_tenant_purchase_order_payments_table',1),(26,'2026_08_30_172801_create_tenant_purchase_order_receipts_table',1),(27,'2026_08_30_172817_create_tenant_purchase_order_receipt_items_table',1),(28,'2026_08_30_173005_create_tenant_stock_movements_table',1),(29,'2026_08_30_173024_create_tenant_stock_transfers_table',1),(30,'2026_08_30_173038_create_tenant_stock_transfer_items_table',1),(31,'2026_08_30_173111_create_tenant_recipe_mappings_table',1),(32,'2026_08_30_173131_create_tenant_reservations_table',1),(33,'2026_08_30_173148_create_tenant_reservation_tables_table',1),(34,'2026_08_30_173203_create_tenant_orders_table',1),(35,'2026_08_30_173228_create_tenant_order_tables_table',1),(36,'2026_08_30_173241_create_tenant_order_kitchen_batches_table',1),(37,'2026_08_30_173259_create_tenant_order_items_table',1),(38,'2026_08_30_173314_create_tenant_order_item_addons_table',1),(39,'2026_08_30_173329_create_tenant_order_payments_table',1),(40,'2026_08_30_173454_create_tenant_order_recipe_consumptions_table',1),(41,'2026_08_30_173509_create_tenant_order_recipe_consumption_items_table',1),(42,'2026_08_30_173527_create_tenant_salary_payrolls_table',1),(43,'2026_08_30_173545_create_tenant_salary_details_table',1),(44,'2026_08_30_173601_create_tenant_expense_categories_table',1),(45,'2026_08_30_173618_create_tenant_expenses_table',1),(46,'2026_08_30_173635_create_tenant_system_settings_table',1),(47,'2026_08_30_173653_create_tenant_audit_logs_table',1),(48,'2026_09_06_212714_create_inventory_wastages_table',1),(49,'2026_09_06_212812_create_inventory_wastage_items_table',1),(50,'2026_09_06_223000_create_stock_transfer_templates_table',1),(51,'2026_09_06_223100_create_stock_transfer_template_items_table',1),(52,'2026_09_07_142000_create_warehouse_stock_lots_table',1),(53,'2026_09_07_142100_create_warehouse_stock_lot_movements_table',1),(54,'2026_09_07_142200_backfill_existing_warehouse_stock_lots',1),(55,'2026_09_07_154500_add_lot_snapshot_fields_to_purchase_order_receipt_items_table',1),(56,'2026_09_07_211500_add_manual_notes_to_warehouse_stock_lots_table',1),(57,'2026_09_07_211600_create_warehouse_stock_lot_metadata_changes_table',1),(58,'2026_09_07_223000_create_purchase_returns_table',1),(59,'2026_09_07_223100_create_purchase_return_items_table',1),(60,'2026_09_07_231000_add_return_credit_applied_to_purchase_orders_table',1),(61,'2026_09_07_231100_create_purchase_return_settlements_table',1),(62,'2026_09_07_235000_add_supplier_credit_applied_to_purchase_orders_table',1),(63,'2026_09_07_235100_create_supplier_credit_applications_table',1),(64,'2026_09_08_120000_make_customer_phone_nullable_for_reservations',1),(65,'2026_09_08_170000_sync_reservation_datetime_with_reservation_date',1),(66,'2026_09_09_000001_add_source_tracking_to_expenses_table',1),(67,'2026_09_09_000002_add_payment_tracking_to_salary_payrolls_table',1),(68,'2026_09_09_000003_create_other_income_tables',1),(69,'2026_09_09_000004_create_chart_of_accounts',1),(70,'2026_09_09_000005_create_journal_engine',1),(71,'2026_09_09_000006_add_source_hash_to_journal_entries',1),(72,'2026_09_09_000007_add_purchase_return_accounting_accounts',1),(73,'2026_09_10_111517_add_sales_discount_account_to_chart_of_accounts',1),(74,'2026_09_10_124104_add_service_charge_income_account_to_chart_of_accounts',1),(75,'2026_10_25_000001_add_takeaway_pickup_fields_to_orders',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item_addons`
--

DROP TABLE IF EXISTS `order_item_addons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_item_addons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint(20) unsigned NOT NULL,
  `menu_addon_id` bigint(20) unsigned DEFAULT NULL,
  `addon_name` varchar(150) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_item_addons_order_item_id_foreign` (`order_item_id`),
  KEY `order_item_addons_menu_addon_id_index` (`menu_addon_id`),
  CONSTRAINT `order_item_addons_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_addons`
--

LOCK TABLES `order_item_addons` WRITE;
/*!40000 ALTER TABLE `order_item_addons` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_item_addons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `order_kitchen_batch_id` bigint(20) unsigned NOT NULL,
  `menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `menu_item_variant_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(180) NOT NULL,
  `variant_name` varchar(150) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `addon_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `kitchen_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_menu_item_id_foreign` (`menu_item_id`),
  KEY `order_items_menu_item_variant_id_foreign` (`menu_item_variant_id`),
  KEY `order_items_status_index` (`status`),
  KEY `order_items_order_id_menu_item_id_index` (`order_id`,`menu_item_id`),
  KEY `order_items_batch_status_index` (`order_kitchen_batch_id`,`status`),
  CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_menu_item_variant_id_foreign` FOREIGN KEY (`menu_item_variant_id`) REFERENCES `menu_item_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_order_kitchen_batch_id_foreign` FOREIGN KEY (`order_kitchen_batch_id`) REFERENCES `order_kitchen_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_kitchen_batches`
--

DROP TABLE IF EXISTS `order_kitchen_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_kitchen_batches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `batch_no` int(10) unsigned NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `chef_id` bigint(20) unsigned DEFAULT NULL,
  `sent_to_kitchen_at` timestamp NULL DEFAULT NULL,
  `preparing_at` timestamp NULL DEFAULT NULL,
  `ready_at` timestamp NULL DEFAULT NULL,
  `served_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_kitchen_batches_order_batch_unique` (`order_id`,`batch_no`),
  KEY `order_kitchen_batches_chef_id_foreign` (`chef_id`),
  KEY `order_kitchen_batches_created_by_foreign` (`created_by`),
  KEY `order_kitchen_batches_status_chef_index` (`status`,`chef_id`),
  CONSTRAINT `order_kitchen_batches_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_kitchen_batches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_kitchen_batches_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_kitchen_batches`
--

LOCK TABLES `order_kitchen_batches` WRITE;
/*!40000 ALTER TABLE `order_kitchen_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_kitchen_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_payments`
--

DROP TABLE IF EXISTS `order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_payments_received_by_foreign` (`received_by`),
  KEY `order_payments_order_id_created_at_index` (`order_id`,`created_at`),
  KEY `order_payments_payment_method_index` (`payment_method`),
  CONSTRAINT `order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_payments`
--

LOCK TABLES `order_payments` WRITE;
/*!40000 ALTER TABLE `order_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_recipe_consumption_items`
--

DROP TABLE IF EXISTS `order_recipe_consumption_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_recipe_consumption_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_recipe_consumption_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `restaurant_quantity_before` decimal(14,4) NOT NULL,
  `restaurant_quantity_after` decimal(14,4) NOT NULL,
  `source_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`source_breakdown`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orci_consumption_material_unique` (`order_recipe_consumption_id`,`raw_material_id`),
  KEY `orci_material_idx` (`raw_material_id`),
  CONSTRAINT `orci_consumption_fk` FOREIGN KEY (`order_recipe_consumption_id`) REFERENCES `order_recipe_consumptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orci_material_fk` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_recipe_consumption_items`
--

LOCK TABLES `order_recipe_consumption_items` WRITE;
/*!40000 ALTER TABLE `order_recipe_consumption_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_recipe_consumption_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_recipe_consumptions`
--

DROP TABLE IF EXISTS `order_recipe_consumptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_recipe_consumptions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `order_kitchen_batch_id` bigint(20) unsigned NOT NULL,
  `order_number` varchar(100) NOT NULL,
  `trigger` varchar(50) NOT NULL DEFAULT 'start_preparing',
  `order_status_snapshot` varchar(30) NOT NULL,
  `consumed_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_recipe_consumptions_order_batch_unique` (`order_id`,`order_kitchen_batch_id`),
  KEY `order_recipe_consumptions_created_by_foreign` (`created_by`),
  KEY `order_recipe_consumptions_batch_index` (`order_kitchen_batch_id`),
  KEY `orc_consumed_at_idx` (`consumed_at`),
  CONSTRAINT `order_recipe_consumptions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_recipe_consumptions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_recipe_consumptions_order_kitchen_batch_id_foreign` FOREIGN KEY (`order_kitchen_batch_id`) REFERENCES `order_kitchen_batches` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_recipe_consumptions`
--

LOCK TABLES `order_recipe_consumptions` WRITE;
/*!40000 ALTER TABLE `order_recipe_consumptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_recipe_consumptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_tables`
--

DROP TABLE IF EXISTS `order_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `restaurant_table_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_tables_order_id_restaurant_table_id_unique` (`order_id`,`restaurant_table_id`),
  KEY `order_tables_restaurant_table_id_order_id_index` (`restaurant_table_id`,`order_id`),
  CONSTRAINT `order_tables_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_tables_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_tables`
--

LOCK TABLES `order_tables` WRITE;
/*!40000 ALTER TABLE `order_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `restaurant_table_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_id` bigint(20) unsigned DEFAULT NULL,
  `fulfillment_type` varchar(20) NOT NULL DEFAULT 'dine_in',
  `pickup_token` varchar(20) DEFAULT NULL,
  `pickup_status` varchar(20) DEFAULT NULL,
  `picked_up_at` datetime DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(30) NOT NULL DEFAULT 'due',
  `payment_method` varchar(30) DEFAULT NULL,
  `payment_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payment_breakdown`)),
  `payment_reference` varchar(150) DEFAULT NULL,
  `order_note` text DEFAULT NULL,
  `kitchen_note` text DEFAULT NULL,
  `sent_to_kitchen_at` datetime DEFAULT NULL,
  `preparing_at` datetime DEFAULT NULL,
  `ready_at` datetime DEFAULT NULL,
  `served_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `is_customer_spend_recorded` tinyint(1) NOT NULL DEFAULT 0,
  `customer_spend_recorded_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `chef_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  UNIQUE KEY `orders_pickup_token_unique` (`pickup_token`),
  KEY `orders_created_by_foreign` (`created_by`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_payment_method_index` (`payment_method`),
  KEY `orders_created_at_index` (`created_at`),
  KEY `orders_restaurant_table_id_status_index` (`restaurant_table_id`,`status`),
  KEY `orders_customer_id_completed_at_index` (`customer_id`,`completed_at`),
  KEY `orders_reservation_id_index` (`reservation_id`),
  KEY `orders_chef_status_index` (`chef_id`,`status`),
  KEY `orders_pickup_board_index` (`fulfillment_type`,`status`,`pickup_status`),
  CONSTRAINT `orders_chef_id_foreign` FOREIGN KEY (`chef_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permission_role` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permission_role_unique` (`role_id`,`permission_id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES (1,1,19,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(2,1,9,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(3,1,4,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(4,1,1,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(5,1,17,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(6,1,13,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(7,1,12,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(8,1,7,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(9,1,11,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(10,1,5,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(11,1,6,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(12,1,18,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(13,1,10,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(14,1,16,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(15,1,3,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(16,1,15,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(17,1,14,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(18,1,8,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(19,1,2,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(20,2,1,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(21,2,4,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(22,2,5,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(23,2,6,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(24,2,7,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(25,2,8,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(26,2,9,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(27,2,10,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(28,2,11,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(29,2,12,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(30,2,13,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(31,2,14,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(32,2,15,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(33,2,16,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(34,2,17,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(35,2,18,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(36,3,1,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(37,3,6,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(38,3,7,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(39,3,8,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(40,4,1,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(41,4,4,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(42,4,5,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(43,4,6,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(44,4,8,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(45,4,10,'2026-10-08 02:21:42','2026-10-08 02:21:42');
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `display_name` varchar(150) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`),
  KEY `permissions_module_index` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'dashboard.view','View Dashboard','dashboard','Access restaurant dashboard.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(2,'users.manage','Manage Users','users','Manage restaurant users.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(3,'settings.manage','Manage Settings','settings','Manage restaurant settings and branding.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(4,'customers.manage','Manage Customers','customers','Manage restaurant customers.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(5,'orders.create','Create Orders','orders','Create restaurant orders.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(6,'orders.view','View Orders','orders','View restaurant orders.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(7,'kitchen.view','View Kitchen','kitchen','Access kitchen operations.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(8,'tables.manage','Manage Tables','tables','Manage restaurant tables and table availability.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(9,'billing.view','View Billing','billing','Access billing and settlement information.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(10,'reservations.manage','Manage Reservations','reservations','Manage restaurant reservations.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(11,'menu.manage','Manage Menu','menu','Manage restaurant menu items and categories.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(12,'inventory.view','View Inventory','inventory','View warehouse and restaurant inventory.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(13,'inventory.manage','Manage Inventory','inventory','Manage stock and inventory transactions.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(14,'suppliers.manage','Manage Suppliers','suppliers','Manage restaurant suppliers.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(15,'staff.manage','Manage Staff','staff','Manage restaurant staff.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(16,'salary.manage','Manage Salary','salary','Manage employee salary and payroll.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(17,'expenses.manage','Manage Expenses','expenses','Manage restaurant expenses.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(18,'reports.view','View Reports','reports','View restaurant reports.','2026-10-08 02:21:42','2026-10-08 02:21:42'),(19,'audit_logs.view','View Audit Log','audit_logs','View restaurant audit logs.','2026-10-08 02:21:42','2026-10-08 02:21:42');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (2,'App\\Models\\User',1,'tenant-api-token','0846c2de705d44e08f87674e4e3bda925a3c676963e8fcbd7ccc7c9a1f52f4a3','[\"*\"]','2026-10-08 02:54:54',NULL,'2026-10-08 02:53:55','2026-10-08 02:54:54');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `received_quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_items_purchase_order_id_foreign` (`purchase_order_id`),
  KEY `purchase_order_items_raw_material_id_purchase_order_id_index` (`raw_material_id`,`purchase_order_id`),
  CONSTRAINT `purchase_order_items_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
INSERT INTO `purchase_order_items` VALUES (1,1,1,'Suger','kg',10.00,10.00,1.00,10.00,'2026-10-08 02:33:55','2026-10-08 02:34:51');
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_payments`
--

DROP TABLE IF EXISTS `purchase_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_order_payments_created_by_foreign` (`created_by`),
  KEY `purchase_order_payments_updated_by_foreign` (`updated_by`),
  KEY `purchase_order_payments_purchase_order_id_payment_date_index` (`purchase_order_id`,`payment_date`),
  CONSTRAINT `purchase_order_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_payments_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `purchase_order_payments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_payments`
--

LOCK TABLES `purchase_order_payments` WRITE;
/*!40000 ALTER TABLE `purchase_order_payments` DISABLE KEYS */;
INSERT INTO `purchase_order_payments` VALUES (1,1,'2026-10-08',1.00,'cash',NULL,'Initial payment recorded when purchase order was created.',1,1,'2026-10-08 02:33:55','2026-10-08 02:33:55'),(2,1,'2026-10-08',5.00,'cash',NULL,NULL,1,1,'2026-10-08 02:34:52','2026-10-08 02:34:52');
/*!40000 ALTER TABLE `purchase_order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_receipt_items`
--

DROP TABLE IF EXISTS `purchase_order_receipt_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_receipt_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_receipt_id` bigint(20) unsigned NOT NULL,
  `purchase_order_item_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL,
  `total_cost` decimal(18,4) NOT NULL,
  `notes` text DEFAULT NULL,
  `supplier_batch_no` varchar(120) DEFAULT NULL,
  `manufactured_at` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_receipt_item_unique` (`purchase_order_receipt_id`,`purchase_order_item_id`),
  KEY `purchase_order_receipt_items_purchase_order_item_id_foreign` (`purchase_order_item_id`),
  KEY `purchase_order_receipt_items_raw_material_id_index` (`raw_material_id`),
  KEY `po_receipt_item_material_expiry_index` (`raw_material_id`,`expiry_date`),
  CONSTRAINT `purchase_order_receipt_items_purchase_order_item_id_foreign` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`),
  CONSTRAINT `purchase_order_receipt_items_purchase_order_receipt_id_foreign` FOREIGN KEY (`purchase_order_receipt_id`) REFERENCES `purchase_order_receipts` (`id`),
  CONSTRAINT `purchase_order_receipt_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_receipt_items`
--

LOCK TABLES `purchase_order_receipt_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_receipt_items` DISABLE KEYS */;
INSERT INTO `purchase_order_receipt_items` VALUES (1,1,1,1,'Suger','kg',10.0000,1.0000,10.0000,NULL,'RICE-2A','2026-10-01','2026-10-17','2026-10-08 02:34:51','2026-10-08 02:34:51');
/*!40000 ALTER TABLE `purchase_order_receipt_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_receipts`
--

DROP TABLE IF EXISTS `purchase_order_receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `receipt_no` varchar(80) NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `received_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_order_receipts_receipt_no_unique` (`receipt_no`),
  KEY `purchase_order_receipts_received_by_foreign` (`received_by`),
  KEY `purchase_order_receipts_created_by_foreign` (`created_by`),
  KEY `purchase_order_receipts_updated_by_foreign` (`updated_by`),
  KEY `purchase_order_receipts_purchase_order_id_received_at_index` (`purchase_order_id`,`received_at`),
  CONSTRAINT `purchase_order_receipts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_receipts_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  CONSTRAINT `purchase_order_receipts_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_order_receipts_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_receipts`
--

LOCK TABLES `purchase_order_receipts` WRITE;
/*!40000 ALTER TABLE `purchase_order_receipts` DISABLE KEYS */;
INSERT INTO `purchase_order_receipts` VALUES (1,1,'GRN-20261008-000001','2026-10-08 08:34:51',NULL,1,1,1,'2026-10-08 02:34:51','2026-10-08 02:34:51');
/*!40000 ALTER TABLE `purchase_order_receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `order_date` datetime NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'ordered',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `service_charge` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `return_credit_applied` decimal(18,2) NOT NULL DEFAULT 0.00,
  `supplier_credit_applied` decimal(18,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `ordered_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_orders_ordered_by_foreign` (`ordered_by`),
  KEY `purchase_orders_created_by_foreign` (`created_by`),
  KEY `purchase_orders_updated_by_foreign` (`updated_by`),
  KEY `purchase_orders_supplier_id_order_date_index` (`supplier_id`,`order_date`),
  KEY `purchase_orders_status_index` (`status`),
  CONSTRAINT `purchase_orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_ordered_by_foreign` FOREIGN KEY (`ordered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `purchase_orders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
INSERT INTO `purchase_orders` VALUES (1,1,'2026-10-08 00:00:00','2026-10-08','received',10.00,0.20,0.40,10.60,6.00,0.00,0.00,4.60,'cash',1,NULL,1,1,'2026-10-08 02:33:55','2026-10-08 02:34:52',NULL);
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_return_items`
--

DROP TABLE IF EXISTS `purchase_return_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_return_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_return_id` bigint(20) unsigned NOT NULL,
  `warehouse_stock_lot_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_receipt_id` bigint(20) unsigned DEFAULT NULL,
  `item_name` varchar(180) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `lot_no` varchar(80) NOT NULL,
  `supplier_batch_no` varchar(120) DEFAULT NULL,
  `manufactured_at` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `lot_quantity_before` decimal(18,4) unsigned NOT NULL,
  `lot_quantity_after` decimal(18,4) unsigned NOT NULL,
  `warehouse_quantity_before` decimal(18,4) unsigned NOT NULL,
  `warehouse_quantity_after` decimal(18,4) unsigned NOT NULL,
  `return_unit_cost` decimal(18,4) unsigned NOT NULL,
  `expected_supplier_credit` decimal(18,4) unsigned NOT NULL,
  `inventory_average_unit_cost` decimal(18,4) unsigned NOT NULL,
  `inventory_value_reduction` decimal(18,4) unsigned NOT NULL,
  `valuation_variance` decimal(18,4) NOT NULL,
  `reason` varchar(60) NOT NULL,
  `reason_notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pri_return_fk` (`purchase_return_id`),
  KEY `pri_po_fk` (`purchase_order_id`),
  KEY `pri_grn_fk` (`purchase_order_receipt_id`),
  KEY `pri_lot_date_idx` (`warehouse_stock_lot_id`,`created_at`),
  KEY `pri_material_date_idx` (`raw_material_id`,`created_at`),
  KEY `pri_reason_idx` (`reason`),
  CONSTRAINT `pri_grn_fk` FOREIGN KEY (`purchase_order_receipt_id`) REFERENCES `purchase_order_receipts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pri_lot_fk` FOREIGN KEY (`warehouse_stock_lot_id`) REFERENCES `warehouse_stock_lots` (`id`),
  CONSTRAINT `pri_material_fk` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `pri_po_fk` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pri_return_fk` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_return_items`
--

LOCK TABLES `purchase_return_items` WRITE;
/*!40000 ALTER TABLE `purchase_return_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_return_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_return_settlements`
--

DROP TABLE IF EXISTS `purchase_return_settlements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_return_settlements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `settlement_no` varchar(80) NOT NULL,
  `purchase_return_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `settlement_type` varchar(60) NOT NULL,
  `amount` decimal(18,2) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `parent_settlement_id` bigint(20) unsigned DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_reference` varchar(255) DEFAULT NULL,
  `settled_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `allocation_effect` tinyint(1) NOT NULL DEFAULT 1,
  `cash_effect` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_return_settlements_settlement_no_unique` (`settlement_no`),
  KEY `prs_po_fk` (`purchase_order_id`),
  KEY `prs_parent_fk` (`parent_settlement_id`),
  KEY `prs_user_fk` (`created_by`),
  KEY `prs_return_date_idx` (`purchase_return_id`,`settled_at`),
  KEY `prs_supplier_date_idx` (`supplier_id`,`settled_at`),
  KEY `prs_type_idx` (`settlement_type`),
  CONSTRAINT `prs_parent_fk` FOREIGN KEY (`parent_settlement_id`) REFERENCES `purchase_return_settlements` (`id`),
  CONSTRAINT `prs_po_fk` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prs_return_fk` FOREIGN KEY (`purchase_return_id`) REFERENCES `purchase_returns` (`id`),
  CONSTRAINT `prs_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `prs_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_return_settlements`
--

LOCK TABLES `purchase_return_settlements` WRITE;
/*!40000 ALTER TABLE `purchase_return_settlements` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_return_settlements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_returns`
--

DROP TABLE IF EXISTS `purchase_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_returns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `return_no` varchar(80) NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `supplier_name` varchar(180) NOT NULL,
  `supplier_phone` varchar(80) DEFAULT NULL,
  `supplier_email` varchar(180) DEFAULT NULL,
  `supplier_address` text DEFAULT NULL,
  `returned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_items` int(10) unsigned NOT NULL DEFAULT 0,
  `expected_supplier_credit` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `inventory_value_reduction` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `valuation_variance` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_returns_return_no_unique` (`return_no`),
  KEY `pr_creator_fk` (`created_by`),
  KEY `pr_returned_at_idx` (`returned_at`),
  KEY `pr_supplier_date_idx` (`supplier_id`,`returned_at`),
  CONSTRAINT `pr_creator_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pr_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_returns`
--

LOCK TABLES `purchase_returns` WRITE;
/*!40000 ALTER TABLE `purchase_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `raw_materials`
--

DROP TABLE IF EXISTS `raw_materials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `raw_materials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `material_name` varchar(180) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `base_unit` varchar(30) NOT NULL,
  `warehouse_minimum_quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `restaurant_minimum_quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `raw_materials_created_by_foreign` (`created_by`),
  KEY `raw_materials_updated_by_foreign` (`updated_by`),
  KEY `raw_materials_material_name_base_unit_is_active_index` (`material_name`,`base_unit`,`is_active`),
  KEY `raw_materials_material_name_index` (`material_name`),
  KEY `raw_materials_category_index` (`category`),
  KEY `raw_materials_is_active_index` (`is_active`),
  CONSTRAINT `raw_materials_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `raw_materials_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `raw_materials`
--

LOCK TABLES `raw_materials` WRITE;
/*!40000 ALTER TABLE `raw_materials` DISABLE KEYS */;
INSERT INTO `raw_materials` VALUES (1,'Suger','Grocery','kg',0.0000,2.0000,1,1,1,'2026-10-08 02:32:42','2026-10-08 02:32:42',NULL);
/*!40000 ALTER TABLE `raw_materials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_mappings`
--

DROP TABLE IF EXISTS `recipe_mappings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recipe_mappings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `menu_item_id` bigint(20) unsigned DEFAULT NULL,
  `variant_id` bigint(20) unsigned DEFAULT NULL,
  `add_on_id` bigint(20) unsigned DEFAULT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recipe_mapping_menu_variant_material_unique` (`menu_item_id`,`variant_id`,`raw_material_id`),
  UNIQUE KEY `recipe_mapping_addon_material_unique` (`add_on_id`,`raw_material_id`),
  KEY `recipe_mappings_created_by_foreign` (`created_by`),
  KEY `recipe_mappings_updated_by_foreign` (`updated_by`),
  KEY `recipe_mapping_menu_item_index` (`menu_item_id`),
  KEY `recipe_mapping_variant_index` (`variant_id`),
  KEY `recipe_mapping_addon_index` (`add_on_id`),
  KEY `recipe_mapping_raw_material_index` (`raw_material_id`),
  CONSTRAINT `recipe_mapping_addon_fk` FOREIGN KEY (`add_on_id`) REFERENCES `add_ons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recipe_mapping_variant_fk` FOREIGN KEY (`variant_id`) REFERENCES `menu_item_variants` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `recipe_mappings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recipe_mappings_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `recipe_mappings_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `recipe_mappings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `recipe_mapping_target_check` CHECK (`menu_item_id` is not null and `add_on_id` is null or `menu_item_id` is null and `add_on_id` is not null)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_mappings`
--

LOCK TABLES `recipe_mappings` WRITE;
/*!40000 ALTER TABLE `recipe_mappings` DISABLE KEYS */;
/*!40000 ALTER TABLE `recipe_mappings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation_tables`
--

DROP TABLE IF EXISTS `reservation_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservation_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_id` bigint(20) unsigned NOT NULL,
  `restaurant_table_id` bigint(20) unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservation_tables_reservation_id_restaurant_table_id_unique` (`reservation_id`,`restaurant_table_id`),
  KEY `reservation_tables_reservation_id_is_primary_index` (`reservation_id`,`is_primary`),
  KEY `reservation_tables_restaurant_table_id_index` (`restaurant_table_id`),
  CONSTRAINT `reservation_tables_reservation_id_foreign` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reservation_tables_restaurant_table_id_foreign` FOREIGN KEY (`restaurant_table_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation_tables`
--

LOCK TABLES `reservation_tables` WRITE;
/*!40000 ALTER TABLE `reservation_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservation_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reservations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reservation_no` varchar(255) NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `reservation_date` date NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `guest_count` int(10) unsigned NOT NULL,
  `advance_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('reserved','confirmed','seated','completed','cancelled') NOT NULL DEFAULT 'reserved',
  `note` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reservations_reservation_no_unique` (`reservation_no`),
  KEY `reservations_customer_id_foreign` (`customer_id`),
  KEY `reservations_created_by_foreign` (`created_by`),
  KEY `reservations_reservation_date_status_index` (`reservation_date`,`status`),
  KEY `reservations_start_time_end_time_index` (`start_time`,`end_time`),
  CONSTRAINT `reservations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reservations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_stocks`
--

DROP TABLE IF EXISTS `restaurant_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `average_unit_cost` decimal(14,4) NOT NULL DEFAULT 0.0000,
  `last_received_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_stocks_raw_material_unique` (`raw_material_id`),
  KEY `restaurant_stocks_created_by_foreign` (`created_by`),
  KEY `restaurant_stocks_updated_by_foreign` (`updated_by`),
  CONSTRAINT `restaurant_stocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `restaurant_stocks_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `restaurant_stocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_stocks`
--

LOCK TABLES `restaurant_stocks` WRITE;
/*!40000 ALTER TABLE `restaurant_stocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_tables`
--

DROP TABLE IF EXISTS `restaurant_tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_tables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `capacity` int(10) unsigned NOT NULL DEFAULT 1,
  `section` enum('ac','non_ac','outdoor') NOT NULL,
  `status` enum('available','occupied','reserved','cleaning') NOT NULL DEFAULT 'available',
  `reservation_start_at` datetime DEFAULT NULL,
  `reservation_end_at` datetime DEFAULT NULL,
  `merged_with_id` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `restaurant_tables_name_deleted_unique` (`table_name`,`deleted_at`),
  KEY `restaurant_tables_merged_with_id_foreign` (`merged_with_id`),
  KEY `restaurant_tables_section_status_index` (`section`,`status`),
  KEY `restaurant_tables_section_index` (`section`),
  KEY `restaurant_tables_status_index` (`status`),
  CONSTRAINT `restaurant_tables_merged_with_id_foreign` FOREIGN KEY (`merged_with_id`) REFERENCES `restaurant_tables` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_tables`
--

LOCK TABLES `restaurant_tables` WRITE;
/*!40000 ALTER TABLE `restaurant_tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `restaurant_tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`),
  KEY `roles_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','Administrator','Full access to the restaurant management system.',1,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(2,'manager','Manager','Manage restaurant operations, staff, inventory and reports.',1,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(3,'chef','Chef','Kitchen and food preparation management access.',1,'2026-10-08 02:21:42','2026-10-08 02:21:42'),(4,'waiter','Waiter','Order taking and front desk operational access.',1,'2026-10-08 02:21:42','2026-10-08 02:21:42');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_details`
--

DROP TABLE IF EXISTS `salary_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `salary_payroll_id` bigint(20) unsigned NOT NULL,
  `attendance_id` bigint(20) unsigned DEFAULT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `salary_date` date NOT NULL,
  `scheduled_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `worked_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `late_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `break_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `overtime_minutes` int(10) unsigned NOT NULL DEFAULT 0,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `salary_type` varchar(50) NOT NULL DEFAULT 'no_salary',
  `regular_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `overtime_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `calculation_source` varchar(20) NOT NULL DEFAULT 'automatic',
  `notes` text DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_details_attendance_id_foreign` (`attendance_id`),
  KEY `salary_details_updated_by_foreign` (`updated_by`),
  KEY `salary_detail_payroll_date_index` (`salary_payroll_id`,`salary_date`),
  KEY `salary_detail_employee_date_index` (`employee_id`,`salary_date`),
  KEY `salary_detail_type_date_index` (`salary_type`,`salary_date`),
  CONSTRAINT `salary_details_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `salary_details_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `salary_details_salary_payroll_id_foreign` FOREIGN KEY (`salary_payroll_id`) REFERENCES `salary_payrolls` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `salary_details_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_details`
--

LOCK TABLES `salary_details` WRITE;
/*!40000 ALTER TABLE `salary_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_payrolls`
--

DROP TABLE IF EXISTS `salary_payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `salary_payrolls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `employee_name` varchar(255) NOT NULL,
  `employee_phone` varchar(255) DEFAULT NULL,
  `employee_email` varchar(255) DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `hourly_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `regular_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `overtime_salary` decimal(14,2) NOT NULL DEFAULT 0.00,
  `adjustment_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `paid_at` datetime DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(150) DEFAULT NULL,
  `paid_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_payrolls_paid_by_foreign` (`paid_by`),
  KEY `salary_payrolls_created_by_foreign` (`created_by`),
  KEY `salary_payrolls_updated_by_foreign` (`updated_by`),
  KEY `salary_payroll_employee_period_index` (`employee_id`,`period_start`,`period_end`),
  KEY `salary_payroll_status_period_index` (`payment_status`,`period_start`,`period_end`),
  KEY `salary_payroll_employee_period_idx` (`employee_id`,`period_start`),
  CONSTRAINT `salary_payrolls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `salary_payrolls_paid_by_foreign` FOREIGN KEY (`paid_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `salary_payrolls_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_payrolls`
--

LOCK TABLES `salary_payrolls` WRITE;
/*!40000 ALTER TABLE `salary_payrolls` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_payrolls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_schedule_overrides`
--

DROP TABLE IF EXISTS `shift_schedule_overrides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_schedule_overrides` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `shift_schedule_id` bigint(20) unsigned NOT NULL,
  `override_date` date NOT NULL,
  `override_type` varchar(30) NOT NULL DEFAULT 'modified',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `grace_minutes` smallint(5) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_schedule_overrides_created_by_foreign` (`created_by`),
  KEY `shift_schedule_overrides_updated_by_foreign` (`updated_by`),
  KEY `shift_schedule_overrides_shift_schedule_id_override_date_index` (`shift_schedule_id`,`override_date`),
  KEY `shift_schedule_overrides_override_date_override_type_index` (`override_date`,`override_type`),
  CONSTRAINT `shift_schedule_overrides_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shift_schedule_overrides_shift_schedule_id_foreign` FOREIGN KEY (`shift_schedule_id`) REFERENCES `shift_schedules` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shift_schedule_overrides_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_schedule_overrides`
--

LOCK TABLES `shift_schedule_overrides` WRITE;
/*!40000 ALTER TABLE `shift_schedule_overrides` DISABLE KEYS */;
/*!40000 ALTER TABLE `shift_schedule_overrides` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shift_schedules`
--

DROP TABLE IF EXISTS `shift_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shift_schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `working_days` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`working_days`)),
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_minutes` smallint(5) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shift_schedules_created_by_foreign` (`created_by`),
  KEY `shift_schedules_updated_by_foreign` (`updated_by`),
  KEY `shift_schedules_employee_id_start_date_end_date_index` (`employee_id`,`start_date`,`end_date`),
  KEY `shift_schedules_start_date_end_date_is_active_index` (`start_date`,`end_date`,`is_active`),
  CONSTRAINT `shift_schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `shift_schedules_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `shift_schedules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shift_schedules`
--

LOCK TABLES `shift_schedules` WRITE;
/*!40000 ALTER TABLE `shift_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `shift_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `location` varchar(30) NOT NULL,
  `movement_type` varchar(50) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `quantity_before` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `quantity_after` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `unit_cost` decimal(18,4) unsigned DEFAULT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `unit` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_movements_created_by_foreign` (`created_by`),
  KEY `stock_movements_material_location_type_index` (`raw_material_id`,`location`,`movement_type`),
  KEY `stock_movements_reference_index` (`reference_type`,`reference_id`),
  KEY `stock_movements_material_date_index` (`raw_material_id`,`created_at`),
  KEY `stock_movements_location_index` (`location`),
  KEY `stock_movements_movement_type_index` (`movement_type`),
  KEY `stock_movements_reference_type_index` (`reference_type`),
  KEY `stock_movements_reference_id_index` (`reference_id`),
  CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_movements_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES (1,1,'warehouse','purchase_receive',10.0000,0.0000,10.0000,1.0000,'App\\Models\\PurchaseOrderReceipt',1,'kg','GRN: GRN-20261008-000001 | Purchase order #1 | Item: Suger | Warehouse lot: LOT-20261008-000001 | Supplier batch: RICE-2A | Expiry: 2026-10-17',1,'2026-10-08 02:34:52','2026-10-08 02:34:52');
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_items`
--

DROP TABLE IF EXISTS `stock_transfer_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_transfer_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(14,4) NOT NULL,
  `unit_cost` decimal(14,4) NOT NULL,
  `warehouse_quantity_before` decimal(14,4) NOT NULL,
  `warehouse_quantity_after` decimal(14,4) NOT NULL,
  `restaurant_quantity_before` decimal(14,4) NOT NULL,
  `restaurant_quantity_after` decimal(14,4) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfer_material_unique` (`stock_transfer_id`,`raw_material_id`),
  KEY `stock_transfer_items_raw_material_id_index` (`raw_material_id`),
  CONSTRAINT `stock_transfer_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `stock_transfer_items_stock_transfer_id_foreign` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_items`
--

LOCK TABLES `stock_transfer_items` WRITE;
/*!40000 ALTER TABLE `stock_transfer_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfer_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_template_items`
--

DROP TABLE IF EXISTS `stock_transfer_template_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_template_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `stock_transfer_template_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit` varchar(30) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transfer_template_material_unique` (`stock_transfer_template_id`,`raw_material_id`),
  KEY `stock_transfer_template_items_raw_material_id_foreign` (`raw_material_id`),
  KEY `transfer_template_sort_index` (`stock_transfer_template_id`,`sort_order`),
  CONSTRAINT `stock_transfer_template_items_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `stock_transfer_template_items_stock_transfer_template_id_foreign` FOREIGN KEY (`stock_transfer_template_id`) REFERENCES `stock_transfer_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_template_items`
--

LOCK TABLES `stock_transfer_template_items` WRITE;
/*!40000 ALTER TABLE `stock_transfer_template_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfer_template_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfer_templates`
--

DROP TABLE IF EXISTS `stock_transfer_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfer_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_transfer_templates_created_by_foreign` (`created_by`),
  KEY `stock_transfer_templates_updated_by_foreign` (`updated_by`),
  KEY `stock_transfer_templates_name_index` (`name`),
  KEY `stock_transfer_templates_is_active_index` (`is_active`),
  CONSTRAINT `stock_transfer_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfer_templates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfer_templates`
--

LOCK TABLES `stock_transfer_templates` WRITE;
/*!40000 ALTER TABLE `stock_transfer_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfer_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_transfers`
--

DROP TABLE IF EXISTS `stock_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_transfers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transfer_no` varchar(80) DEFAULT NULL,
  `transferred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `transferred_by` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stock_transfers_transfer_no_unique` (`transfer_no`),
  KEY `stock_transfers_transferred_by_foreign` (`transferred_by`),
  KEY `stock_transfers_created_by_foreign` (`created_by`),
  KEY `stock_transfers_updated_by_foreign` (`updated_by`),
  KEY `stock_transfers_transferred_at_index` (`transferred_at`),
  CONSTRAINT `stock_transfers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_transferred_by_foreign` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `stock_transfers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_transfers`
--

LOCK TABLES `stock_transfers` WRITE;
/*!40000 ALTER TABLE `stock_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier_credit_applications`
--

DROP TABLE IF EXISTS `supplier_credit_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier_credit_applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_no` varchar(80) NOT NULL,
  `source_settlement_id` bigint(20) unsigned NOT NULL,
  `supplier_id` bigint(20) unsigned NOT NULL,
  `purchase_order_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(18,2) unsigned NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplier_credit_applications_application_no_unique` (`application_no`),
  KEY `sca_user_fk` (`created_by`),
  KEY `sca_source_date_idx` (`source_settlement_id`,`applied_at`),
  KEY `sca_supplier_date_idx` (`supplier_id`,`applied_at`),
  KEY `sca_po_date_idx` (`purchase_order_id`,`applied_at`),
  CONSTRAINT `sca_po_fk` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`),
  CONSTRAINT `sca_source_fk` FOREIGN KEY (`source_settlement_id`) REFERENCES `purchase_return_settlements` (`id`),
  CONSTRAINT `sca_supplier_fk` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  CONSTRAINT `sca_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier_credit_applications`
--

LOCK TABLES `supplier_credit_applications` WRITE;
/*!40000 ALTER TABLE `supplier_credit_applications` DISABLE KEYS */;
/*!40000 ALTER TABLE `supplier_credit_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `suppliers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text DEFAULT NULL,
  `gstin` varchar(50) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_created_by_foreign` (`created_by`),
  KEY `suppliers_updated_by_foreign` (`updated_by`),
  KEY `suppliers_supplier_name_index` (`supplier_name`),
  KEY `suppliers_phone_index` (`phone`),
  CONSTRAINT `suppliers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `suppliers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'Rukshana','Tasnima Akther Tisha','shraboniakter554@gmail.com','0588787841','974 Green Cowley Freeway','yg236t263',1,1,'2026-10-08 02:24:10','2026-10-08 02:24:10',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_name` varchar(255) DEFAULT NULL,
  `restaurant_logo` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `branch_address` varchar(255) DEFAULT NULL,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vat_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `service_charge_rate` decimal(5,2) NOT NULL DEFAULT 0.00,
  `service_charge_type` varchar(255) NOT NULL DEFAULT 'percentage',
  `owner_nid` varchar(255) DEFAULT NULL,
  `owner_trade_license` varchar(255) DEFAULT NULL,
  `invoice_subtitle` varchar(255) DEFAULT NULL,
  `bin_number` varchar(255) DEFAULT NULL,
  `invoice_show_logo` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_company_name` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_branch_address` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_token_number` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_show_table_number` tinyint(1) NOT NULL DEFAULT 1,
  `invoice_currency` varchar(255) NOT NULL DEFAULT 'BDT',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'Bongo Sylhet',NULL,'Bangladesh','974 Green Cowley Freeway',0.00,'percentage',0.00,'percentage',NULL,NULL,NULL,NULL,1,1,1,1,1,'BDT','2026-10-08 02:21:42','2026-10-08 02:21:42');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_logout_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` smallint(5) unsigned NOT NULL DEFAULT 0,
  `blocked_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT 0,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_is_active_index` (`is_active`),
  KEY `users_blocked_at_index` (`blocked_at`),
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1,'Sadia','shraboniakter554','shraboniakter554@gmail.com',NULL,'$2y$12$NejdUkc.tDBV7276M3Kve.pJAYcdNFDFuwES1Fxj/2nodhVX8NHzO',1,'2026-10-08 02:53:55','2026-10-08 02:52:48',0,NULL,NULL,0,NULL,'2026-10-08 02:21:42','2026-10-08 02:53:55',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stock_lot_metadata_changes`
--

DROP TABLE IF EXISTS `warehouse_stock_lot_metadata_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stock_lot_metadata_changes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_stock_lot_id` bigint(20) unsigned NOT NULL,
  `changed_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`changed_fields`)),
  `before_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`before_snapshot`)),
  `after_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`after_snapshot`)),
  `change_reason` varchar(1000) NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wsl_meta_lot_date_idx` (`warehouse_stock_lot_id`,`created_at`),
  KEY `wsl_meta_user_fk` (`created_by`),
  CONSTRAINT `wsl_meta_lot_fk` FOREIGN KEY (`warehouse_stock_lot_id`) REFERENCES `warehouse_stock_lots` (`id`),
  CONSTRAINT `wsl_meta_user_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stock_lot_metadata_changes`
--

LOCK TABLES `warehouse_stock_lot_metadata_changes` WRITE;
/*!40000 ALTER TABLE `warehouse_stock_lot_metadata_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `warehouse_stock_lot_metadata_changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stock_lot_movements`
--

DROP TABLE IF EXISTS `warehouse_stock_lot_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stock_lot_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `warehouse_stock_lot_id` bigint(20) unsigned NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `movement_type` varchar(60) NOT NULL,
  `direction` varchar(10) NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL,
  `quantity_before` decimal(18,4) unsigned NOT NULL,
  `quantity_after` decimal(18,4) unsigned NOT NULL,
  `unit_cost` decimal(18,4) unsigned NOT NULL,
  `unit` varchar(30) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `occurred_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `warehouse_stock_lot_movements_created_by_foreign` (`created_by`),
  KEY `warehouse_lot_movement_lot_date_index` (`warehouse_stock_lot_id`,`occurred_at`),
  KEY `warehouse_lot_movement_material_date_index` (`raw_material_id`,`occurred_at`),
  KEY `warehouse_stock_lot_movements_movement_type_index` (`movement_type`),
  KEY `warehouse_lot_movement_reference_index` (`reference_type`,`reference_id`),
  CONSTRAINT `warehouse_stock_lot_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lot_movements_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `warehouse_stock_lot_movements_warehouse_stock_lot_id_foreign` FOREIGN KEY (`warehouse_stock_lot_id`) REFERENCES `warehouse_stock_lots` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stock_lot_movements`
--

LOCK TABLES `warehouse_stock_lot_movements` WRITE;
/*!40000 ALTER TABLE `warehouse_stock_lot_movements` DISABLE KEYS */;
INSERT INTO `warehouse_stock_lot_movements` VALUES (1,1,1,'purchase_receive','in',10.0000,0.0000,10.0000,1.0000,'kg','App\\Models\\PurchaseOrderReceipt',1,'2026-10-08 08:34:51','Lot created from GRN-20261008-000001 | Lot: LOT-20261008-000001 | Supplier batch: RICE-2A | Expiry: 2026-10-17',1,'2026-10-08 02:34:52','2026-10-08 02:34:52');
/*!40000 ALTER TABLE `warehouse_stock_lot_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stock_lots`
--

DROP TABLE IF EXISTS `warehouse_stock_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stock_lots` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `lot_no` varchar(80) NOT NULL,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `source_type` varchar(60) NOT NULL,
  `supplier_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_receipt_id` bigint(20) unsigned DEFAULT NULL,
  `purchase_order_receipt_item_id` bigint(20) unsigned DEFAULT NULL,
  `supplier_batch_no` varchar(120) DEFAULT NULL,
  `manufactured_at` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `received_quantity` decimal(18,4) unsigned NOT NULL,
  `remaining_quantity` decimal(18,4) unsigned NOT NULL,
  `unit_cost` decimal(18,4) unsigned NOT NULL,
  `unit` varchar(30) NOT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `manual_notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouse_stock_lots_lot_no_unique` (`lot_no`),
  KEY `warehouse_stock_lots_created_by_foreign` (`created_by`),
  KEY `warehouse_stock_lots_updated_by_foreign` (`updated_by`),
  KEY `warehouse_lot_material_expiry_index` (`raw_material_id`,`expiry_date`),
  KEY `warehouse_lot_material_remaining_index` (`raw_material_id`,`remaining_quantity`),
  KEY `warehouse_lot_supplier_material_index` (`supplier_id`,`raw_material_id`),
  KEY `warehouse_stock_lots_purchase_order_id_index` (`purchase_order_id`),
  KEY `warehouse_stock_lots_purchase_order_receipt_id_index` (`purchase_order_receipt_id`),
  KEY `warehouse_stock_lots_purchase_order_receipt_item_id_index` (`purchase_order_receipt_item_id`),
  KEY `warehouse_stock_lots_source_type_index` (`source_type`),
  CONSTRAINT `warehouse_stock_lots_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_purchase_order_receipt_id_foreign` FOREIGN KEY (`purchase_order_receipt_id`) REFERENCES `purchase_order_receipts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_purchase_order_receipt_item_id_foreign` FOREIGN KEY (`purchase_order_receipt_item_id`) REFERENCES `purchase_order_receipt_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `warehouse_stock_lots_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stock_lots_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stock_lots`
--

LOCK TABLES `warehouse_stock_lots` WRITE;
/*!40000 ALTER TABLE `warehouse_stock_lots` DISABLE KEYS */;
INSERT INTO `warehouse_stock_lots` VALUES (1,'LOT-20261008-000001',1,'purchase_receive',1,1,1,1,'RICE-2A','2026-10-01','2026-10-17',10.0000,10.0000,1.0000,'kg','2026-10-08 08:34:51','Created from purchase receive GRN-20261008-000001 | Purchase order #1 | Receipt item #1 | Supplier batch: RICE-2A | Expiry: 2026-10-17',NULL,1,1,'2026-10-08 02:34:52','2026-10-08 02:34:52');
/*!40000 ALTER TABLE `warehouse_stock_lots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouse_stocks`
--

DROP TABLE IF EXISTS `warehouse_stocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `warehouse_stocks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `raw_material_id` bigint(20) unsigned NOT NULL,
  `quantity` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `average_unit_cost` decimal(18,4) unsigned NOT NULL DEFAULT 0.0000,
  `last_received_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `warehouse_stocks_raw_material_unique` (`raw_material_id`),
  KEY `warehouse_stocks_created_by_foreign` (`created_by`),
  KEY `warehouse_stocks_updated_by_foreign` (`updated_by`),
  KEY `warehouse_stocks_quantity_last_received_at_index` (`quantity`,`last_received_at`),
  CONSTRAINT `warehouse_stocks_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `warehouse_stocks_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`),
  CONSTRAINT `warehouse_stocks_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouse_stocks`
--

LOCK TABLES `warehouse_stocks` WRITE;
/*!40000 ALTER TABLE `warehouse_stocks` DISABLE KEYS */;
INSERT INTO `warehouse_stocks` VALUES (1,1,10.0000,1.0000,'2026-10-08 08:34:51',1,1,'2026-10-08 02:32:42','2026-10-08 02:34:51',NULL);
/*!40000 ALTER TABLE `warehouse_stocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'tenant_bongo_sylhet'
--

--
-- Dumping routines for database 'tenant_bongo_sylhet'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-27  9:31:31
