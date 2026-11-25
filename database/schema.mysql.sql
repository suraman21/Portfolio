-- ============================================================================
-- Aarav Singh — Software & Web Developer PHP Portfolio & CMS
-- MySQL / MariaDB Schema Dump (UTF8MB4) — Matches includes/db.php 100%
-- Note: On first page load, includes/db.php also auto-creates & seeds all
-- tables automatically when DB_DRIVER is set to 'mysql' in includes/config.php.
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `portfolio_cms`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `portfolio_cms`;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(80) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `display_name` VARCHAR(120) NOT NULL DEFAULT 'Administrator',
  `updated_at` VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `site_settings` (
  `setting_key` VARCHAR(100) PRIMARY KEY,
  `setting_value` LONGTEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `title` VARCHAR(140) NOT NULL,
  `tile_class` VARCHAR(40) NOT NULL DEFAULT 'tile-violet',
  `icon_key` VARCHAR(40) NOT NULL DEFAULT 'browser_code',
  `short_desc` TEXT NOT NULL,
  `tagline` TEXT NOT NULL,
  `overview` LONGTEXT NOT NULL,
  `deliverables_json` LONGTEXT NOT NULL,
  `metrics` VARCHAR(200) NOT NULL DEFAULT '',
  `stack_json` LONGTEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(140) NOT NULL UNIQUE,
  `title` VARCHAR(180) NOT NULL,
  `short_category` VARCHAR(160) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `year` VARCHAR(20) NOT NULL DEFAULT '2026',
  `client` VARCHAR(160) NOT NULL DEFAULT '',
  `role` VARCHAR(160) NOT NULL DEFAULT '',
  `status_badge` VARCHAR(160) NOT NULL DEFAULT '',
  `image_path` VARCHAR(255) NOT NULL DEFAULT '/assets/work-finova.png',
  `live_url` VARCHAR(255) NOT NULL DEFAULT '',
  `repo_url` VARCHAR(255) NOT NULL DEFAULT '',
  `summary` TEXT NOT NULL,
  `challenge` LONGTEXT NOT NULL,
  `solution` LONGTEXT NOT NULL,
  `stack_json` LONGTEXT NOT NULL,
  `metrics_json` LONGTEXT NOT NULL,
  `arch_json` LONGTEXT NOT NULL,
  `code_file` VARCHAR(180) NOT NULL DEFAULT 'src/index.ts',
  `code_snippet` LONGTEXT NOT NULL,
  `benchmarks_json` LONGTEXT NOT NULL,
  `is_featured` INT NOT NULL DEFAULT 0,
  `is_active` INT NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(140) NOT NULL,
  `client_role` VARCHAR(160) NOT NULL,
  `quote_text` TEXT NOT NULL,
  `avatar_path` VARCHAR(255) NOT NULL DEFAULT '/assets/avatar-rohit.png',
  `quote_color` VARCHAR(30) NOT NULL DEFAULT '#b4aaf8',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_name` VARCHAR(140) NOT NULL,
  `sender_email` VARCHAR(180) NOT NULL,
  `project_scope` VARCHAR(160) NOT NULL DEFAULT '',
  `timeline` VARCHAR(100) NOT NULL DEFAULT '',
  `message_body` LONGTEXT NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'unread',
  `ip_address` VARCHAR(64) NOT NULL DEFAULT '',
  `created_at` VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tech_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL,
  `icon_key` VARCHAR(40) NOT NULL DEFAULT 'code',
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `skill_bars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(120) NOT NULL,
  `skill_name` VARCHAR(160) NOT NULL,
  `proficiency` INT NOT NULL DEFAULT 90,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `experiences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `entry_type` VARCHAR(30) NOT NULL DEFAULT 'work',
  `period` VARCHAR(80) NOT NULL,
  `title` VARCHAR(180) NOT NULL,
  `organization` VARCHAR(180) NOT NULL,
  `summary` TEXT NOT NULL,
  `highlights_json` LONGTEXT NOT NULL,
  `stack_json` LONGTEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `process_steps` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `step_number` VARCHAR(10) NOT NULL,
  `title` VARCHAR(120) NOT NULL,
  `description` TEXT NOT NULL,
  `icon_key` VARCHAR(40) NOT NULL DEFAULT 'arch',
  `is_highlighted` INT NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `faqs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question` TEXT NOT NULL,
  `answer` TEXT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_active` INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(160) NOT NULL UNIQUE,
  `title` VARCHAR(220) NOT NULL,
  `category` VARCHAR(80) NOT NULL,
  `published_date` VARCHAR(40) NOT NULL,
  `read_time` VARCHAR(40) NOT NULL DEFAULT '5 min read',
  `excerpt` TEXT NOT NULL,
  `tags_json` LONGTEXT NOT NULL,
  `sections_json` LONGTEXT NOT NULL,
  `is_published` INT NOT NULL DEFAULT 1,
  `sort_order` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
