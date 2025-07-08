/*
Navicat MySQL Data Transfer

Source Server         : XAMPP
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : ita_hospital_db

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2025-07-08 14:52:06
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for activity_logs
-- ----------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_table_record` (`table_name`,`record_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of activity_logs
-- ----------------------------
INSERT INTO `activity_logs` VALUES ('1', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1', '2025-07-07 14:33:40');
INSERT INTO `activity_logs` VALUES ('2', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 14:55:09');
INSERT INTO `activity_logs` VALUES ('3', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 15:39:32');
INSERT INTO `activity_logs` VALUES ('4', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 15:39:54');
INSERT INTO `activity_logs` VALUES ('5', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 15:40:03');
INSERT INTO `activity_logs` VALUES ('6', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 15:54:00');
INSERT INTO `activity_logs` VALUES ('7', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 16:43:06');
INSERT INTO `activity_logs` VALUES ('8', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 16:43:33');
INSERT INTO `activity_logs` VALUES ('9', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 16:53:48');
INSERT INTO `activity_logs` VALUES ('10', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 16:53:58');
INSERT INTO `activity_logs` VALUES ('11', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 16:54:23');
INSERT INTO `activity_logs` VALUES ('12', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 20:30:26');
INSERT INTO `activity_logs` VALUES ('13', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 20:30:36');
INSERT INTO `activity_logs` VALUES ('14', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 20:30:58');
INSERT INTO `activity_logs` VALUES ('15', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 20:31:06');
INSERT INTO `activity_logs` VALUES ('16', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 20:33:22');
INSERT INTO `activity_logs` VALUES ('17', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 21:53:27');
INSERT INTO `activity_logs` VALUES ('18', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 21:53:38');
INSERT INTO `activity_logs` VALUES ('19', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 22:17:36');
INSERT INTO `activity_logs` VALUES ('20', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-07 22:17:48');
INSERT INTO `activity_logs` VALUES ('21', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-08 08:40:21');
INSERT INTO `activity_logs` VALUES ('22', '1', 'logout', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-08 08:54:13');
INSERT INTO `activity_logs` VALUES ('23', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-08 08:54:21');
INSERT INTO `activity_logs` VALUES ('24', '1', 'login', null, null, null, null, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-08 08:58:53');

-- ----------------------------
-- Table structure for approval_logs
-- ----------------------------
DROP TABLE IF EXISTS `approval_logs`;
CREATE TABLE `approval_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `action` enum('approve','reject','request_changes') NOT NULL,
  `comments` text DEFAULT NULL,
  `previous_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_document` (`document_id`),
  KEY `idx_approver` (`approver_id`),
  KEY `idx_action` (`action`),
  CONSTRAINT `approval_logs_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `approval_logs_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of approval_logs
-- ----------------------------

-- ----------------------------
-- Table structure for backups
-- ----------------------------
DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('database','files','full') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','failed') DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_automated` tinyint(1) DEFAULT 0,
  `retention_days` int(11) DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of backups
-- ----------------------------

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_level` (`level`),
  KEY `idx_active` (`is_active`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of categories
-- ----------------------------
INSERT INTO `categories` VALUES ('1', 'เอกสารบริหาร', 'เอกสารเกี่ยวกับการบริหารจัดการ', null, '1', '1', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('2', 'เอกสารการเงิน', 'เอกสารเกี่ยวกับการเงินและบัญชี', null, '1', '2', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('3', 'เอกสารทางการแพทย์', 'เอกสารเกี่ยวกับการแพทย์และการรักษา', null, '1', '3', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('4', 'เอกสารทรัพยากรบุคคล', 'เอกสารเกี่ยวกับการจัดการทรัพยากรบุคคล', null, '1', '4', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('5', 'เอกสารคุณภาพ', 'เอกสารเกี่ยวกับการควบคุมคุณภาพ', null, '1', '5', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('6', 'นโยบายและระเบียบ', 'นโยบายและระเบียบของโรงพยาบาล', '1', '2', '1', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('7', 'คำสั่งและประกาศ', 'คำสั่งและประกาศต่างๆ', '1', '2', '2', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('8', 'รายงานประจำปี', 'รายงานประจำปีของโรงพยาบาล', '1', '2', '3', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('9', 'งบประมาณ', 'เอกสารเกี่ยวกับงบประมาณ', '2', '2', '1', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('10', 'การจัดซื้อจัดจ้าง', 'เอกสารการจัดซื้อจัดจ้าง', '2', '2', '2', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);
INSERT INTO `categories` VALUES ('11', 'รายงานการเงิน', 'รายงานทางการเงิน', '2', '2', '3', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44', null, null);

-- ----------------------------
-- Table structure for display_settings
-- ----------------------------
DROP TABLE IF EXISTS `display_settings`;
CREATE TABLE `display_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` enum('document','category') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `quarter_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `display_order` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_entity_quarter_year` (`entity_type`,`entity_id`,`quarter_id`,`fiscal_year_id`),
  KEY `fiscal_year_id` (`fiscal_year_id`),
  KEY `created_by` (`created_by`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  KEY `idx_quarter_year` (`quarter_id`,`fiscal_year_id`),
  KEY `idx_visible` (`is_visible`),
  CONSTRAINT `display_settings_ibfk_1` FOREIGN KEY (`quarter_id`) REFERENCES `quarters` (`id`) ON DELETE CASCADE,
  CONSTRAINT `display_settings_ibfk_2` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `display_settings_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of display_settings
-- ----------------------------

-- ----------------------------
-- Table structure for documents
-- ----------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `version` varchar(20) DEFAULT '1.0',
  `status` enum('draft','pending','approved','rejected','archived') DEFAULT 'draft',
  `visibility` enum('public','private','restricted') DEFAULT 'public',
  `responsible_person` varchar(200) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `checksum` varchar(64) DEFAULT NULL,
  `virus_scan_status` enum('pending','clean','infected','error') DEFAULT 'pending',
  `virus_scan_date` timestamp NULL DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approval_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_visibility` (`visibility`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_approved_by` (`approved_by`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_documents_status_created` (`status`,`created_at`),
  FULLTEXT KEY `idx_title_desc` (`title`,`description`),
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`),
  CONSTRAINT `documents_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of documents
-- ----------------------------

-- ----------------------------
-- Table structure for document_fiscal_years
-- ----------------------------
DROP TABLE IF EXISTS `document_fiscal_years`;
CREATE TABLE `document_fiscal_years` (
  `document_id` int(11) NOT NULL,
  `fiscal_year_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`document_id`,`fiscal_year_id`),
  KEY `fiscal_year_id` (`fiscal_year_id`),
  CONSTRAINT `document_fiscal_years_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_fiscal_years_ibfk_2` FOREIGN KEY (`fiscal_year_id`) REFERENCES `fiscal_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of document_fiscal_years
-- ----------------------------

-- ----------------------------
-- Table structure for document_permissions
-- ----------------------------
DROP TABLE IF EXISTS `document_permissions`;
CREATE TABLE `document_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_type` enum('read','write','delete','approve') NOT NULL,
  `granted_by` int(11) NOT NULL,
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `granted_by` (`granted_by`),
  KEY `idx_document_user` (`document_id`,`user_id`),
  KEY `idx_permission_type` (`permission_type`),
  CONSTRAINT `document_permissions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_permissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_permissions_ibfk_3` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of document_permissions
-- ----------------------------

-- ----------------------------
-- Table structure for document_quarters
-- ----------------------------
DROP TABLE IF EXISTS `document_quarters`;
CREATE TABLE `document_quarters` (
  `document_id` int(11) NOT NULL,
  `quarter_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`document_id`,`quarter_id`),
  KEY `quarter_id` (`quarter_id`),
  CONSTRAINT `document_quarters_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_quarters_ibfk_2` FOREIGN KEY (`quarter_id`) REFERENCES `quarters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of document_quarters
-- ----------------------------

-- ----------------------------
-- Table structure for document_versions
-- ----------------------------
DROP TABLE IF EXISTS `document_versions`;
CREATE TABLE `document_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `version` varchar(20) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `changes_description` text DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_document` (`document_id`),
  KEY `idx_version` (`document_id`,`version`),
  KEY `idx_current` (`document_id`,`is_current`),
  CONSTRAINT `document_versions_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_versions_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of document_versions
-- ----------------------------

-- ----------------------------
-- Table structure for downloads
-- ----------------------------
DROP TABLE IF EXISTS `downloads`;
CREATE TABLE `downloads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `download_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_size` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_document` (`document_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_download_at` (`download_at`),
  CONSTRAINT `downloads_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `downloads_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of downloads
-- ----------------------------

-- ----------------------------
-- Table structure for fiscal_years
-- ----------------------------
DROP TABLE IF EXISTS `fiscal_years`;
CREATE TABLE `fiscal_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `year` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of fiscal_years
-- ----------------------------
INSERT INTO `fiscal_years` VALUES ('1', '2024', 'ปีงบประมาณ 2567', '2023-10-01', '2024-09-30', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44');
INSERT INTO `fiscal_years` VALUES ('2', '2025', 'ปีงบประมาณ 2568', '2024-10-01', '2025-09-30', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44');
INSERT INTO `fiscal_years` VALUES ('3', '2026', 'ปีงบประมาณ 2569', '2025-10-01', '2026-09-30', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44');

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('document_uploaded','document_approved','document_rejected','document_expiring','system_alert') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_read` (`is_read`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of notifications
-- ----------------------------

-- ----------------------------
-- Table structure for page_views
-- ----------------------------
DROP TABLE IF EXISTS `page_views`;
CREATE TABLE `page_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_type` enum('document','category','dashboard','other') NOT NULL,
  `page_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page` (`page_type`,`page_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_viewed_at` (`viewed_at`),
  CONSTRAINT `page_views_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of page_views
-- ----------------------------

-- ----------------------------
-- Table structure for permissions
-- ----------------------------
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of permissions
-- ----------------------------
INSERT INTO `permissions` VALUES ('1', 'users.view', 'ดูข้อมูลผู้ใช้', 'สามารถดูรายชื่อผู้ใช้', 'users', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('2', 'users.create', 'เพิ่มผู้ใช้', 'สามารถเพิ่มผู้ใช้ใหม่', 'users', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('3', 'users.edit', 'แก้ไขผู้ใช้', 'สามารถแก้ไขข้อมูลผู้ใช้', 'users', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('4', 'users.delete', 'ลบผู้ใช้', 'สามารถลบผู้ใช้', 'users', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('5', 'documents.view', 'ดูเอกสาร', 'สามารถดูเอกสาร', 'documents', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('6', 'documents.create', 'เพิ่มเอกสาร', 'สามารถเพิ่มเอกสารใหม่', 'documents', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('7', 'documents.edit', 'แก้ไขเอกสาร', 'สามารถแก้ไขเอกสาร', 'documents', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('8', 'documents.delete', 'ลบเอกสาร', 'สามารถลบเอกสาร', 'documents', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('9', 'documents.approve', 'อนุมัติเอกสาร', 'สามารถอนุมัติเอกสาร', 'documents', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('10', 'categories.view', 'ดูหมวดหมู่', 'สามารถดูหมวดหมู่', 'categories', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('11', 'categories.create', 'เพิ่มหมวดหมู่', 'สามารถเพิ่มหมวดหมู่ใหม่', 'categories', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('12', 'categories.edit', 'แก้ไขหมวดหมู่', 'สามารถแก้ไขหมวดหมู่', 'categories', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('13', 'categories.delete', 'ลบหมวดหมู่', 'สามารถลบหมวดหมู่', 'categories', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('14', 'reports.view', 'ดูรายงาน', 'สามารถดูรายงาน', 'reports', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('15', 'reports.export', 'ส่งออกรายงาน', 'สามารถส่งออกรายงาน', 'reports', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('16', 'settings.view', 'ดูการตั้งค่า', 'สามารถดูการตั้งค่าระบบ', 'settings', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('17', 'settings.edit', 'แก้ไขการตั้งค่า', 'สามารถแก้ไขการตั้งค่าระบบ', 'settings', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('18', 'backups.view', 'ดูการสำรองข้อมูล', 'สามารถดูการสำรองข้อมูล', 'backups', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('19', 'backups.create', 'สร้างการสำรองข้อมูล', 'สามารถสร้างการสำรองข้อมูล', 'backups', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('20', 'display_settings.view', 'ดูการตั้งค่าการแสดงผล', 'สามารถดูการตั้งค่าการแสดงผล', 'display_settings', '2025-07-07 14:01:44');
INSERT INTO `permissions` VALUES ('21', 'display_settings.edit', 'แก้ไขการตั้งค่าการแสดงผล', 'สามารถแก้ไขการตั้งค่าการแสดงผล', 'display_settings', '2025-07-07 14:01:44');

-- ----------------------------
-- Table structure for quarters
-- ----------------------------
DROP TABLE IF EXISTS `quarters`;
CREATE TABLE `quarters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `quarter_number` int(11) NOT NULL,
  `start_month` int(11) NOT NULL,
  `end_month` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of quarters
-- ----------------------------
INSERT INTO `quarters` VALUES ('1', 'ไตรมาสที่ 1', '1', '10', '12', '1', '2025-07-07 14:01:44');
INSERT INTO `quarters` VALUES ('2', 'ไตรมาสที่ 2', '2', '1', '3', '1', '2025-07-07 14:01:44');
INSERT INTO `quarters` VALUES ('3', 'ไตรมาสที่ 3', '3', '4', '6', '1', '2025-07-07 14:01:44');
INSERT INTO `quarters` VALUES ('4', 'ไตรมาสที่ 4', '4', '7', '9', '1', '2025-07-07 14:01:44');

-- ----------------------------
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES ('1', 'admin', 'ผู้ดูแลระบบ', 'มีสิทธิ์ในการจัดการระบบทั้งหมด', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44');
INSERT INTO `roles` VALUES ('2', 'staff', 'เจ้าหน้าที่', 'เจ้าหน้าที่ประจำหมวด', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44');
INSERT INTO `roles` VALUES ('3', 'approver', 'ผู้อนุมัติ', 'ผู้อนุมัติเอกสาร', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44');
INSERT INTO `roles` VALUES ('4', 'visitor', 'ผู้เยี่ยมชม', 'บุคคลทั่วไป', '1', '2025-07-07 14:01:44', '2025-07-07 14:01:44');

-- ----------------------------
-- Table structure for role_permissions
-- ----------------------------
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of role_permissions
-- ----------------------------
INSERT INTO `role_permissions` VALUES ('1', '1', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '2', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '3', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '4', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '5', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '6', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '7', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '8', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '9', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '10', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '11', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '12', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '13', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '14', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '15', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '16', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '17', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '18', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '19', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '20', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('1', '21', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('2', '5', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('2', '6', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('2', '7', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('2', '10', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('2', '14', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('3', '5', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('3', '9', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('3', '10', '2025-07-07 14:01:44');
INSERT INTO `role_permissions` VALUES ('3', '14', '2025-07-07 14:01:44');

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_last_activity` (`last_activity`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of sessions
-- ----------------------------

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `data_type` enum('string','integer','boolean','text') DEFAULT 'string',
  `category` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`),
  KEY `updated_by` (`updated_by`),
  KEY `idx_category` (`category`),
  KEY `idx_public` (`is_public`),
  CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of settings
-- ----------------------------
INSERT INTO `settings` VALUES ('1', 'site_name', 'ระบบจัดเก็บและเผยแพร่เอกสาร ITA โรงพยาบาล', 'string', 'general', 'ชื่อเว็บไซต์', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('2', 'max_file_size', '52428800', 'integer', 'upload', 'ขนาดไฟล์สูงสุด (bytes)', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('3', 'allowed_file_types', 'pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png', 'string', 'upload', 'ประเภทไฟล์ที่อนุญาต', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('4', 'items_per_page', '20', 'integer', 'display', 'จำนวนรายการต่อหน้า', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('5', 'session_timeout', '3600', 'integer', 'security', 'เวลาหมดอายุ session (วินาที)', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('6', 'backup_retention_days', '30', 'integer', 'backup', 'จำนวนวันเก็บไฟล์สำรอง', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('7', 'enable_notifications', '1', 'boolean', 'notification', 'เปิดใช้งานการแจ้งเตือน', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('8', 'default_document_status', 'draft', 'string', 'document', 'สถานะเริ่มต้นของเอกสาร', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('9', 'require_approval', '1', 'boolean', 'document', 'ต้องการการอนุมัติเอกสาร', '0', null, '2025-07-07 14:01:44');
INSERT INTO `settings` VALUES ('10', 'enable_version_control', '1', 'boolean', 'document', 'เปิดใช้งานการควบคุมเวอร์ชัน', '0', null, '2025-07-07 14:01:44');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('1', 'admin', 'admin@hospital.local', '$2y$10$UtjFw0WdYfrfAukCYTp3fu2Cn2uyYDmJ/rMdL1alkw./ily0GqBju', 'ผู้ดูแล', 'ระบบ', null, 'IT', 'ผู้ดูแลระบบ', '1', '2025-07-08 08:58:53', null, '0', null, '0', null, '2025-07-07 14:01:44', '2025-07-08 08:58:53', null, null);

-- ----------------------------
-- Table structure for user_roles
-- ----------------------------
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of user_roles
-- ----------------------------
INSERT INTO `user_roles` VALUES ('1', '1', '2025-07-07 14:01:44', null);
