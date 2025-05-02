-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 02, 2025 at 01:30 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jira_clone`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel_cache_spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:7:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:12:\"manage users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"manage projects\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:19:\"manage own projects\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:11:\"create task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:9:\"edit task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"delete task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:13:\"change status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;}}}s:5:\"roles\";a:4:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:15:\"project_manager\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:9:\"developer\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:6:\"tester\";s:1:\"c\";s:3:\"web\";}}}', 1746271910);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comments_task_id_foreign` (`task_id`),
  KEY `comments_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `content`, `task_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'First comment on this', 1, 2, '2025-05-02 05:42:34', '2025-05-02 05:42:34'),
(2, 'Second comment on this', 1, 2, '2025-05-02 05:42:49', '2025-05-02 05:42:49'),
(3, 'Manager\'s comment to this', 1, 1, '2025-05-02 07:13:42', '2025-05-02 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labels`
--

DROP TABLE IF EXISTS `labels`;
CREATE TABLE IF NOT EXISTS `labels` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `labels_project_id_foreign` (`project_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `label_task`
--

DROP TABLE IF EXISTS `label_task`;
CREATE TABLE IF NOT EXISTS `label_task` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `label_id` bigint UNSIGNED NOT NULL,
  `task_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `label_task_label_id_foreign` (`label_id`),
  KEY `label_task_task_id_foreign` (`task_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_04_29_155553_create_projects_table', 1),
(5, '2025_04_29_155554_create_task_statuses_table', 1),
(6, '2025_04_29_155554_create_tasks_table', 1),
(7, '2025_04_29_155555_create_comments_table', 1),
(8, '2025_04_29_155556_create_sprints_table', 1),
(9, '2025_04_29_155557_create_labels_table', 1),
(10, '2025_04_29_155558_create_priorities_table', 1),
(11, '2025_04_29_155558_create_task_types_table', 1),
(12, '2025_04_29_162807_create_label_task_table', 1),
(13, '2025_04_29_162840_create_project_user_table', 1),
(14, '2025_04_29_165018_create_permission_tables', 1),
(15, '2025_04_30_063654_create_personal_access_tokens_table', 2),
(16, '2025_04_30_170000_create_project_invitations_table', 3),
(17, '2025_05_02_105332_add_is_active_to_users_table', 4),
(18, '2025_05_02_111027_add_avatar_to_users_table', 5),
(19, '2025_05_02_111143_create_user_activities_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage users', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(2, 'manage projects', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(3, 'manage own projects', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(4, 'create task', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(5, 'edit task', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(6, 'delete task', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(7, 'change status', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `priorities`
--

DROP TABLE IF EXISTS `priorities`;
CREATE TABLE IF NOT EXISTS `priorities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `priorities`
--

INSERT INTO `priorities` (`id`, `name`, `icon`, `color`, `order`, `created_at`, `updated_at`) VALUES
(1, 'Highest', 'arrow-up', '#e5493a', 1, '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(2, 'High', 'arrow-up', '#e97f33', 2, '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(3, 'Medium', 'minus', '#e2b203', 3, '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(4, 'Low', 'arrow-down', '#79e2f2', 4, '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(5, 'Lowest', 'arrow-down', '#c8c8c8', 5, '2025-04-29 15:05:57', '2025-04-29 15:05:57');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `lead_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `projects_key_unique` (`key`),
  KEY `projects_lead_id_foreign` (`lead_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `key`, `description`, `lead_id`, `created_at`, `updated_at`) VALUES
(1, 'IQ Link', 'IQLK', NULL, 1, '2025-04-30 13:07:32', '2025-04-30 13:07:32'),
(2, 'LipoDoc', 'LPDC', NULL, 1, '2025-04-30 15:21:15', '2025-04-30 15:21:15'),
(3, 'LipoDoc Firmware', 'LPDF', NULL, 1, '2025-04-30 15:25:54', '2025-04-30 15:25:54'),
(4, 'Another project', 'ANTP', NULL, 1, '2025-04-30 15:33:19', '2025-04-30 15:33:19'),
(5, 'Fixing coffee machine in lobby', 'FCMIL', NULL, 1, '2025-05-02 10:48:13', '2025-05-02 10:48:13');

-- --------------------------------------------------------

--
-- Table structure for table `project_invitations`
--

DROP TABLE IF EXISTS `project_invitations`;
CREATE TABLE IF NOT EXISTS `project_invitations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `inviter_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_invitations_token_unique` (`token`),
  KEY `project_invitations_project_id_foreign` (`project_id`),
  KEY `project_invitations_inviter_id_foreign` (`inviter_id`),
  KEY `project_invitations_user_id_foreign` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_user`
--

DROP TABLE IF EXISTS `project_user`;
CREATE TABLE IF NOT EXISTS `project_user` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_user_project_id_foreign` (`project_id`),
  KEY `project_user_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_user`
--

INSERT INTO `project_user` (`id`, `project_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 1, 2, NULL, NULL),
(3, 2, 2, NULL, NULL),
(4, 3, 1, NULL, NULL),
(5, 4, 1, NULL, NULL),
(6, 5, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(2, 'project_manager', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(3, 'developer', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(4, 'tester', 'web', '2025-04-29 15:05:57', '2025-04-29 15:05:57');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(3, 2),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(5, 1),
(5, 2),
(5, 3),
(5, 4),
(6, 1),
(6, 2),
(7, 1),
(7, 2),
(7, 3),
(7, 4);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('WfLw4h2KX99Bi2hcU8my4KZwfYk8xZqzwdB3i6BP', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiNm90djRpRWpINlZkcEtkeXhVQllZWHpWeldHM0l3OGkxc09MVFFFOCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjk6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9wcm9maWxlIjt9czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czo0OiJhdXRoIjthOjE6e3M6MjE6InBhc3N3b3JkX2NvbmZpcm1lZF9hdCI7aToxNzQ2MTkyNDk0O319', 1746192557);

-- --------------------------------------------------------

--
-- Table structure for table `sprints`
--

DROP TABLE IF EXISTS `sprints`;
CREATE TABLE IF NOT EXISTS `sprints` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` enum('planning','active','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planning',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sprints_project_id_foreign` (`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sprints`
--

INSERT INTO `sprints` (`id`, `name`, `project_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sprint 001', 1, '2025-05-02 09:11:38', '2025-05-12 00:00:00', 'active', '2025-05-02 07:11:17', '2025-05-02 07:11:38');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `task_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `reporter_id` bigint UNSIGNED NOT NULL,
  `assignee_id` bigint UNSIGNED DEFAULT NULL,
  `task_status_id` bigint UNSIGNED NOT NULL,
  `task_type_id` bigint UNSIGNED NOT NULL,
  `priority_id` bigint UNSIGNED NOT NULL,
  `sprint_id` bigint UNSIGNED DEFAULT NULL,
  `story_points` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_project_id_foreign` (`project_id`),
  KEY `tasks_reporter_id_foreign` (`reporter_id`),
  KEY `tasks_assignee_id_foreign` (`assignee_id`),
  KEY `tasks_task_status_id_foreign` (`task_status_id`),
  KEY `tasks_task_type_id_foreign` (`task_type_id`),
  KEY `tasks_priority_id_foreign` (`priority_id`),
  KEY `tasks_sprint_id_foreign` (`sprint_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `task_number`, `project_id`, `reporter_id`, `assignee_id`, `task_status_id`, `task_type_id`, `priority_id`, `sprint_id`, `story_points`, `created_at`, `updated_at`) VALUES
(1, 'Initial project setup', NULL, 'IQLINK-1', 1, 1, 2, 2, 2, 3, 1, NULL, '2025-04-30 13:08:22', '2025-05-02 11:28:31'),
(2, 'Documentation review and project setup', NULL, 'LPDC-1', 2, 2, 2, 13, 3, 1, NULL, 8, '2025-05-02 06:55:32', '2025-05-02 06:55:47'),
(3, 'Define a project team', NULL, 'IQLK-2', 1, 1, 1, 1, 3, 1, 1, NULL, '2025-05-02 06:58:56', '2025-05-02 11:28:31'),
(4, 'Find service for fixing broken parts', NULL, 'FCMIL-1', 5, 1, 1, 18, 3, 2, NULL, NULL, '2025-05-02 10:48:58', '2025-05-02 10:48:58');

-- --------------------------------------------------------

--
-- Table structure for table `task_statuses`
--

DROP TABLE IF EXISTS `task_statuses`;
CREATE TABLE IF NOT EXISTS `task_statuses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL,
  `project_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_statuses_project_id_foreign` (`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_statuses`
--

INSERT INTO `task_statuses` (`id`, `name`, `slug`, `order`, `project_id`, `created_at`, `updated_at`) VALUES
(1, 'To Do', 'to-do', 1, 1, '2025-04-30 13:07:32', '2025-04-30 13:07:32'),
(2, 'In Progress', 'in-progress', 2, 1, '2025-04-30 13:07:32', '2025-04-30 13:07:32'),
(3, 'In Review', 'in-review', 3, 1, '2025-04-30 13:07:32', '2025-04-30 13:07:32'),
(4, 'Done', 'done', 4, 1, '2025-04-30 13:07:32', '2025-04-30 13:07:32'),
(5, 'To Do', 'to-do', 1, 3, '2025-04-30 15:25:54', '2025-04-30 15:25:54'),
(6, 'In Progress', 'in-progress', 2, 3, '2025-04-30 15:25:54', '2025-04-30 15:25:54'),
(7, 'In Review', 'in-review', 3, 3, '2025-04-30 15:25:54', '2025-04-30 15:25:54'),
(8, 'Done', 'done', 4, 3, '2025-04-30 15:25:54', '2025-04-30 15:25:54'),
(9, 'To Do', 'to-do', 1, 4, '2025-04-30 15:33:19', '2025-05-02 07:26:45'),
(10, 'In Progress', 'in-progress', 2, 4, '2025-04-30 15:33:19', '2025-05-02 07:26:45'),
(11, 'In Review', 'in-review', 4, 4, '2025-04-30 15:33:19', '2025-05-02 07:26:45'),
(12, 'Done', 'done', 5, 4, '2025-04-30 15:33:19', '2025-05-02 07:26:45'),
(13, 'To Do', 'to-do', 1, 2, '2025-05-02 06:54:59', '2025-05-02 06:54:59'),
(14, 'In Progress', 'in-progress', 2, 2, '2025-05-02 06:54:59', '2025-05-02 06:54:59'),
(15, 'In Review', 'in-review', 3, 2, '2025-05-02 06:54:59', '2025-05-02 06:54:59'),
(16, 'Done', 'done', 4, 2, '2025-05-02 06:54:59', '2025-05-02 06:54:59'),
(17, 'On Hold', 'on-hold', 3, 4, '2025-05-02 07:26:38', '2025-05-02 07:26:56'),
(18, 'To Do', 'to-do', 1, 5, '2025-05-02 10:48:13', '2025-05-02 10:48:13'),
(19, 'In Progress', 'in-progress', 2, 5, '2025-05-02 10:48:13', '2025-05-02 10:48:13'),
(20, 'In Review', 'in-review', 3, 5, '2025-05-02 10:48:13', '2025-05-02 10:48:13'),
(21, 'Done', 'done', 4, 5, '2025-05-02 10:48:13', '2025-05-02 10:48:13');

-- --------------------------------------------------------

--
-- Table structure for table `task_types`
--

DROP TABLE IF EXISTS `task_types`;
CREATE TABLE IF NOT EXISTS `task_types` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_types`
--

INSERT INTO `task_types` (`id`, `name`, `icon`, `color`, `created_at`, `updated_at`) VALUES
(1, 'Epic', 'lightning', '#6554c0', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(2, 'Story', 'book', '#36b37e', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(3, 'Task', 'check-square', '#4fade6', '2025-04-29 15:05:57', '2025-04-29 15:05:57'),
(4, 'Bug', 'alert-triangle', '#e5493a', '2025-04-29 15:05:57', '2025-04-29 15:05:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `is_active`, `avatar`, `created_at`, `updated_at`) VALUES
(1, 'Markovic Jelena', 'admin@example.com', '2025-04-30 13:06:41', '$2y$12$3XRTJOGtzHCC7wW3jlBXse3ZtRcriQWyagXWJM0KEEgiWIYkYmS3O', 'dFkWMEpIhg94ZmkKmh3sJSZHnJBdFvKA1cIqyw4LzY8UUzJK14iUl5k683k4', 1, 'avatars/DHFR0MYG1icoqIdNR6nvlTcu7sbmMlb7fmMcL5PC.png', '2025-04-30 13:06:41', '2025-05-02 09:46:08'),
(2, 'Gacik Sasa', 'gacik@example.com', NULL, '$2y$12$3XRTJOGtzHCC7wW3jlBXse3ZtRcriQWyagXWJM0KEEgiWIYkYmS3O', '7blYr1eqkUdVq9VmbAW8pohKLEYYm6fXpLQSBTTF9hnARvzCCF5DasCpBrSw', 1, NULL, '2025-04-30 13:10:49', '2025-05-02 09:46:32');

-- --------------------------------------------------------

--
-- Table structure for table `user_activities`
--

DROP TABLE IF EXISTS `user_activities`;
CREATE TABLE IF NOT EXISTS `user_activities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_activities_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activities`
--

INSERT INTO `user_activities` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'Created project: Fixing coffee machine in lobby', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-02 10:48:13', '2025-05-02 10:48:13'),
(2, 1, 'Created task: FCMIL-1 - Find service for fixing broken parts', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-02 10:48:58', '2025-05-02 10:48:58'),
(3, 2, 'Logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-02 11:12:04', '2025-05-02 11:12:04'),
(4, 1, 'Logged in', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', '2025-05-02 11:28:14', '2025-05-02 11:28:14');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
