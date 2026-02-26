-- ============================================================
-- Simple Scooter Showroom - Complete Database Schema
-- Compatible: MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS `simple_scooter` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `simple_scooter`;

-- ============================================================
-- USERS & ROLES
-- ============================================================
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20),
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin','admin','operator') NOT NULL DEFAULT 'operator',
  `avatar` VARCHAR(255),
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login` DATETIME,
  `login_attempts` TINYINT DEFAULT 0,
  `locked_until` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- MARKETING SOURCES
-- ============================================================
CREATE TABLE `marketing_sources` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `type` ENUM('facebook','instagram','whatsapp','walk_in','referral','google','other') NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SCOOTER INVENTORY
-- ============================================================
CREATE TABLE `inventory` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `model_name` VARCHAR(150) NOT NULL,
  `brand` VARCHAR(100),
  `color` VARCHAR(50),
  `sku` VARCHAR(50) UNIQUE,
  `purchase_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `selling_price` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `stock_quantity` INT DEFAULT 0,
  `low_stock_alert` INT DEFAULT 2,
  `dealer_name` VARCHAR(150),
  `dealer_contact` VARCHAR(50),
  `battery_capacity` VARCHAR(50),
  `range_km` INT,
  `top_speed` INT,
  `warranty_months` INT DEFAULT 12,
  `image` VARCHAR(255),
  `description` TEXT,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- VISITORS
-- ============================================================
CREATE TABLE `visitors` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150),
  `address` TEXT,
  `source_id` INT UNSIGNED,
  `interested_model_id` INT UNSIGNED,
  `visit_date` DATE NOT NULL,
  `assigned_operator_id` INT UNSIGNED,
  `status` ENUM('cold','warm','hot') DEFAULT 'cold',
  `notes` TEXT,
  `created_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`source_id`) REFERENCES `marketing_sources`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`interested_model_id`) REFERENCES `inventory`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`assigned_operator_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- LEADS / CRM
-- ============================================================
CREATE TABLE `leads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `visitor_id` INT UNSIGNED,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150),
  `source_id` INT UNSIGNED,
  `interested_model_id` INT UNSIGNED,
  `budget` DECIMAL(12,2),
  `status` ENUM('new','contacted','negotiating','converted','lost') DEFAULT 'new',
  `priority` ENUM('low','medium','high') DEFAULT 'medium',
  `assigned_to` INT UNSIGNED,
  `next_followup_date` DATE,
  `notes` TEXT,
  `facebook_lead_id` VARCHAR(100),
  `created_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`visitor_id`) REFERENCES `visitors`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`source_id`) REFERENCES `marketing_sources`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`interested_model_id`) REFERENCES `inventory`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- FOLLOW-UPS
-- ============================================================
CREATE TABLE `followups` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lead_id` INT UNSIGNED NOT NULL,
  `followup_date` DATE NOT NULL,
  `followup_time` TIME,
  `type` ENUM('call','whatsapp','email','visit','sms') DEFAULT 'call',
  `status` ENUM('pending','completed','cancelled','rescheduled') DEFAULT 'pending',
  `notes` TEXT,
  `result` TEXT,
  `next_followup_date` DATE,
  `conducted_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`conducted_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- CUSTOMERS (Converted Leads)
-- ============================================================
CREATE TABLE `customers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `lead_id` INT UNSIGNED,
  `name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(150),
  `address` TEXT,
  `city` VARCHAR(100),
  `state` VARCHAR(100),
  `pincode` VARCHAR(10),
  `aadhar_number` VARCHAR(20),
  `pan_number` VARCHAR(20),
  `date_of_birth` DATE,
  `kyc_verified` TINYINT(1) DEFAULT 0,
  `document_aadhar` VARCHAR(255),
  `document_pan` VARCHAR(255),
  `document_photo` VARCHAR(255),
  `notes` TEXT,
  `created_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SALES
-- ============================================================
CREATE TABLE `sales` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(50) UNIQUE NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `inventory_id` INT UNSIGNED NOT NULL,
  `sale_date` DATE NOT NULL,
  `quantity` INT DEFAULT 1,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `discount` DECIMAL(12,2) DEFAULT 0,
  `tax_percent` DECIMAL(5,2) DEFAULT 0,
  `tax_amount` DECIMAL(12,2) DEFAULT 0,
  `total_amount` DECIMAL(12,2) NOT NULL,
  `payment_mode` ENUM('cash','upi','card','bank_transfer','emi') DEFAULT 'cash',
  `payment_status` ENUM('paid','partial','pending') DEFAULT 'pending',
  `amount_paid` DECIMAL(12,2) DEFAULT 0,
  `balance_due` DECIMAL(12,2) DEFAULT 0,
  `is_emi` TINYINT(1) DEFAULT 0,
  `emi_months` INT,
  `emi_amount` DECIMAL(12,2),
  `warranty_expiry` DATE,
  `operator_id` INT UNSIGNED,
  `notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  FOREIGN KEY (`inventory_id`) REFERENCES `inventory`(`id`),
  FOREIGN KEY (`operator_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- PAYMENTS
-- ============================================================
CREATE TABLE `payments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `sale_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `payment_mode` ENUM('cash','upi','card','bank_transfer') DEFAULT 'cash',
  `transaction_id` VARCHAR(100),
  `is_emi` TINYINT(1) DEFAULT 0,
  `emi_installment_number` INT,
  `notes` TEXT,
  `received_by` INT UNSIGNED,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  FOREIGN KEY (`received_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- ACTIVITY LOGS / AUDIT TRAIL
-- ============================================================
CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(50),
  `record_id` INT UNSIGNED,
  `old_values` JSON,
  `new_values` JSON,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SYSTEM SETTINGS
-- ============================================================
CREATE TABLE `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT,
  `group` VARCHAR(50) DEFAULT 'general',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- EMAIL TEMPLATES
-- ============================================================
CREATE TABLE `email_templates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `variables` TEXT,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SERVICE REMINDERS
-- ============================================================
CREATE TABLE `service_reminders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT UNSIGNED NOT NULL,
  `sale_id` INT UNSIGNED,
  `reminder_date` DATE NOT NULL,
  `type` ENUM('service','warranty_expiry','emi_due','birthday','custom') DEFAULT 'service',
  `message` TEXT,
  `status` ENUM('pending','sent','cancelled') DEFAULT 'pending',
  `sent_at` DATETIME,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- DEFAULT DATA
-- ============================================================

-- Default Super Admin (password: Admin@123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`) VALUES
('Super Admin', 'superadmin@simple.com', '9999999999', '$2y$12$LcYpJB8pOBRoMnFCDXtfFuVW9hX3jnPZs5UZ9u5cBfvJZGNXhJWuS', 'super_admin'),
('Admin User', 'admin@simple.com', '9888888888', '$2y$12$LcYpJB8pOBRoMnFCDXtfFuVW9hX3jnPZs5UZ9u5cBfvJZGNXhJWuS', 'admin'),
('Operator One', 'operator@simple.com', '9777777777', '$2y$12$LcYpJB8pOBRoMnFCDXtfFuVW9hX3jnPZs5UZ9u5cBfvJZGNXhJWuS', 'operator');

-- Marketing Sources
INSERT INTO `marketing_sources` (`name`, `type`) VALUES
('Facebook Ads', 'facebook'),
('Instagram', 'instagram'),
('WhatsApp', 'whatsapp'),
('Walk-in', 'walk_in'),
('Referral', 'referral'),
('Google Ads', 'google'),
('Direct', 'other');

-- Default Settings
INSERT INTO `settings` (`key`, `value`, `group`) VALUES
('company_name', 'Simple Scooters', 'general'),
('company_phone', '+91 99999 99999', 'general'),
('company_email', 'info@simple.com', 'general'),
('company_address', '123 EV Street, City, State', 'general'),
('company_gstin', '', 'general'),
('currency_symbol', '₹', 'general'),
('invoice_prefix', 'SMP', 'general'),
('invoice_counter', '1000', 'general'),
('smtp_host', '', 'email'),
('smtp_port', '587', 'email'),
('smtp_user', '', 'email'),
('smtp_pass', '', 'email'),
('smtp_from_name', 'Simple Scooters', 'email'),
('facebook_pixel_id', '', 'marketing'),
('facebook_access_token', '', 'marketing'),
('whatsapp_api_key', '', 'marketing'),
('whatsapp_phone_number', '', 'marketing'),
('low_stock_alert_enabled', '1', 'notifications'),
('emi_reminder_days', '3', 'notifications'),
('dark_mode_default', '0', 'ui');

-- Sample Inventory
INSERT INTO `inventory` (`model_name`, `brand`, `color`, `sku`, `purchase_price`, `selling_price`, `stock_quantity`, `battery_capacity`, `range_km`, `top_speed`, `warranty_months`) VALUES
('Simple One', 'Simple Energy', 'Brazen Black', 'SE-ONE-BLK', 95000, 115000, 5, '4.8 kWh', 203, 80, 24),
('Simple One', 'Simple Energy', 'Cherry Red', 'SE-ONE-RED', 95000, 115000, 3, '4.8 kWh', 203, 80, 24),
('Simple One Pro', 'Simple Energy', 'Ocean Blue', 'SE-ONEP-BLU', 110000, 134999, 2, '5.0 kWh', 236, 90, 24),
('Simple Dot', 'Simple Energy', 'Ivory White', 'SE-DOT-WHT', 55000, 69999, 8, '2.4 kWh', 130, 60, 12);

-- Email Templates
INSERT INTO `email_templates` (`name`, `subject`, `body`, `variables`) VALUES
('New Lead', 'Thank you for your interest - Simple Scooters', '<h2>Hello {{name}},</h2><p>Thank you for your interest in our electric scooters. Our team will contact you shortly.</p>', '{{name}},{{phone}}'),
('Sale Confirmation', 'Congratulations on your purchase! - Simple Scooters', '<h2>Dear {{name}},</h2><p>Congratulations! Your purchase of <strong>{{model}}</strong> has been confirmed. Invoice: {{invoice_number}}</p>', '{{name}},{{model}},{{invoice_number}},{{amount}}'),
('Follow-up Reminder', 'Your appointment reminder - Simple Scooters', '<h2>Hello {{name}},</h2><p>This is a reminder for your follow-up scheduled on {{date}}.</p>', '{{name}},{{date}}');
