-- Hospital ITA Document System Database
-- Created for comprehensive document management system

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Create database
CREATE DATABASE IF NOT EXISTS `ita_hospital_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ita_hospital_db`;

-- --------------------------------------------------------
-- Table structure for table `roles`
-- --------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default roles
INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'admin', 'System Administrator - Full access'),
(2, 'staff', 'Staff Member - Document management'),
(3, 'approver', 'Document Approver - Approval workflow'),
(4, 'visitor', 'Public Visitor - Read-only access');

-- --------------------------------------------------------
-- Table structure for table `permissions`
-- --------------------------------------------------------

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions
INSERT INTO `permissions` (`name`, `description`, `module`) VALUES
('user.create', 'Create new users', 'users'),
('user.read', 'View users', 'users'),
('user.update', 'Update user information', 'users'),
('user.delete', 'Delete users', 'users'),
('document.create', 'Create documents', 'documents'),
('document.read', 'View documents', 'documents'),
('document.update', 'Update documents', 'documents'),
('document.delete', 'Delete documents', 'documents'),
('document.approve', 'Approve documents', 'documents'),
('category.create', 'Create categories', 'categories'),
('category.read', 'View categories', 'categories'),
('category.update', 'Update categories', 'categories'),
('category.delete', 'Delete categories', 'categories'),
('backup.create', 'Create backups', 'backups'),
('backup.restore', 'Restore backups', 'backups'),
('settings.update', 'Update system settings', 'settings'),
('reports.view', 'View reports', 'reports');

-- --------------------------------------------------------
-- Table structure for table `role_permissions`
-- --------------------------------------------------------

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Assign permissions to roles
-- Admin: All permissions
INSERT INTO `role_permissions` SELECT 1, id FROM `permissions`;

-- Staff: Document and category management
INSERT INTO `role_permissions` SELECT 2, id FROM `permissions` WHERE name LIKE 'document.%' OR name LIKE 'category.read';

-- Approver: Document approval and read
INSERT INTO `role_permissions` SELECT 3, id FROM `permissions` WHERE name IN ('document.read', 'document.approve', 'category.read');

-- Visitor: Read-only access
INSERT INTO `role_permissions` SELECT 4, id FROM `permissions` WHERE name IN ('document.read', 'category.read');

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `role_id` int(11) NOT NULL,
  `status` enum('active', 'inactive', 'locked') NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin)
INSERT INTO `users` (`username`, `password`, `email`, `first_name`, `last_name`, `role_id`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@hospital.com', 'System', 'Administrator', 1);

-- --------------------------------------------------------
-- Table structure for table `categories`
-- --------------------------------------------------------

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `parent_id` int(11) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  INDEX `idx_parent_level` (`parent_id`, `level`),
  INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO `categories` (`name`, `description`, `parent_id`, `level`, `sort_order`, `created_by`) VALUES
('นโยบายและแนวทาง', 'เอกสารนโยบายและแนวทางการดำเนินงาน', NULL, 1, 1, 1),
('คู่มือและขั้นตอน', 'คู่มือการปปฏิบัติงานและขั้นตอนต่างๆ', NULL, 1, 2, 1),
('รายงานและสถิติ', 'รายงานประจำงวดและสถิติต่างๆ', NULL, 1, 3, 1),
('แบบฟอร์มและเอกสาร', 'แบบฟอร์มและเอกสารที่ใช้ในการดำเนินงาน', NULL, 1, 4, 1);

-- --------------------------------------------------------
-- Table structure for table `fiscal_years`
-- --------------------------------------------------------

CREATE TABLE `fiscal_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert fiscal years
INSERT INTO `fiscal_years` (`year`, `name`, `start_date`, `end_date`) VALUES
(2024, 'ปีงบประมาณ 2567', '2023-10-01', '2024-09-30'),
(2025, 'ปีงบประมาณ 2568', '2024-10-01', '2025-09-30');

-- --------------------------------------------------------
-- Table structure for table `quarters`
-- --------------------------------------------------------

CREATE TABLE `quarters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fiscal_year_id` int(11) NOT NULL,
  `quarter` int(1) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `fiscal_quarter` (`fiscal_year_id`, `quarter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert quarters for fiscal year 2024
INSERT INTO `quarters` (`fiscal_year_id`, `quarter`, `name`, `start_date`, `end_date`) VALUES
(1, 1, 'ไตรมาส 1', '2023-10-01', '2023-12-31'),
(1, 2, 'ไตรมาส 2', '2024-01-01', '2024-03-31'),
(1, 3, 'ไตรมาส 3', '2024-04-01', '2024-06-30'),
(1, 4, 'ไตรมาส 4', '2024-07-01', '2024-09-30');

-- --------------------------------------------------------
-- Table structure for table `documents`
-- --------------------------------------------------------

CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `description` text,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `version` varchar(20) NOT NULL DEFAULT '1.0',
  `status` enum('draft', 'pending', 'approved', 'rejected', 'archived') NOT NULL DEFAULT 'draft',
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `download_count` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_comment` text,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`),
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_category` (`category_id`),
  INDEX `idx_public` (`is_public`),
  FULLTEXT KEY `search_content` (`title`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `document_fiscal_years`
-- --------------------------------------------------------

CREATE TABLE `document_fiscal_years` (
  `document_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  PRIMARY KEY (`document_id`, `fiscal_year_id`),
  FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `document_quarters`
-- --------------------------------------------------------

CREATE TABLE `document_quarters` (
  `document_id` int(11) NOT NULL,
  `quarter_id` int(11) NOT NULL,
  PRIMARY KEY (`document_id`, `quarter_id`),
  FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`quarter_id`) REFERENCES `quarters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `document_versions`
-- --------------------------------------------------------

CREATE TABLE `document_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `version` varchar(20) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `change_notes` text,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  UNIQUE KEY `document_version` (`document_id`, `version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `activity_logs`
-- --------------------------------------------------------

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  INDEX `idx_user_action` (`user_id`, `action`),
  INDEX `idx_table_record` (`table_name`, `record_id`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notifications`
-- --------------------------------------------------------

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_user_read` (`user_id`, `is_read`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `backups`
-- --------------------------------------------------------

CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `backup_type` enum('manual', 'scheduled') NOT NULL DEFAULT 'manual',
  `includes_files` tinyint(1) NOT NULL DEFAULT 0,
  `status` enum('creating', 'completed', 'failed') NOT NULL DEFAULT 'creating',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  INDEX `idx_status` (`status`),
  INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text,
  `type` enum('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
  `description` text,
  `category` varchar(50) NOT NULL DEFAULT 'general',
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `description`, `category`) VALUES
('site_name', 'ระบบจัดเก็บเอกสาร ITA โรงพยาบาล', 'string', 'ชื่อเว็บไซต์', 'general'),
('site_description', 'ระบบจัดการเอกสารสำหรับโรงพยาบาล', 'string', 'คำอธิบายเว็บไซต์', 'general'),
('max_file_size', '52428800', 'integer', 'ขนาดไฟล์สูงสุด (bytes) - 50MB', 'upload'),
('allowed_file_types', '["pdf","doc","docx","xls","xlsx","jpg","jpeg","png"]', 'json', 'ประเภทไฟล์ที่อนุญาต', 'upload'),
('items_per_page', '20', 'integer', 'จำนวนรายการต่อหน้า', 'display'),
('session_timeout', '3600', 'integer', 'Session timeout (seconds)', 'security'),
('max_login_attempts', '5', 'integer', 'จำนวนครั้งการล็อกอินสูงสุด', 'security'),
('lockout_duration', '900', 'integer', 'ระยะเวลาล็อค (seconds)', 'security'),
('backup_retention_days', '30', 'integer', 'จำนวนวันเก็บ backup', 'backup'),
('email_notifications', '1', 'boolean', 'เปิดการแจ้งเตือนทาง email', 'notifications');

-- --------------------------------------------------------
-- Create indexes for better performance
-- --------------------------------------------------------

-- Additional indexes for common queries
CREATE INDEX `idx_documents_status_public` ON `documents` (`status`, `is_public`);
CREATE INDEX `idx_documents_created_at` ON `documents` (`created_at`);
CREATE INDEX `idx_users_role_status` ON `users` (`role_id`, `status`);
CREATE INDEX `idx_categories_active_parent` ON `categories` (`is_active`, `parent_id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;