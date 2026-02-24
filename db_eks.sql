/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : db_eks

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2026-02-24 10:28:19
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `tbl_assets`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_assets`;
CREATE TABLE `tbl_assets` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(50) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `description` text,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `margin_percent` decimal(5,2) DEFAULT '3.00',
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`asset_id`),
  UNIQUE KEY `asset_code` (`asset_code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_assets
-- ----------------------------
INSERT INTO `tbl_assets` VALUES ('1', '1', 'kalyan_full', 'kalyan_full', '1', '2026-02-16 10:13:28', '2026-02-23 17:54:23', '3.00', 'Active');
INSERT INTO `tbl_assets` VALUES ('2', '2', 'kalyan_master_bedroom', 'kalyan_master_bedroom', '1', '2026-02-16 10:13:53', '2026-02-23 17:54:30', '3.00', 'Active');
INSERT INTO `tbl_assets` VALUES ('3', '3', 'kalyan_single_room', 'kalyan_single_room', '1', '2026-02-16 10:14:11', '2026-02-23 17:54:46', '3.00', 'Active');

-- ----------------------------
-- Table structure for `tbl_bank_details`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_bank_details`;
CREATE TABLE `tbl_bank_details` (
  `bank_id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(150) NOT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `account_no` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) DEFAULT NULL,
  `branch` varchar(150) DEFAULT NULL,
  `upi_id` varchar(150) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`bank_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_bank_details
-- ----------------------------
INSERT INTO `tbl_bank_details` VALUES ('3', 'icci', null, '45', null, null, '', '1', '2026-02-16 11:09:10');
INSERT INTO `tbl_bank_details` VALUES ('4', 'sdfg', null, '74', null, null, '', '1', '2026-02-16 14:13:23');
INSERT INTO `tbl_bank_details` VALUES ('5', 'jjijijij', null, '258963', null, null, '369*8', '1', '2026-02-16 14:14:58');
INSERT INTO `tbl_bank_details` VALUES ('6', 'fffffff', null, '1515545', null, null, '555555', '1', '2026-02-16 14:15:25');
INSERT INTO `tbl_bank_details` VALUES ('7', 'BJHBHJFE', null, '1545', null, null, '5215', '1', '2026-02-16 14:17:50');
INSERT INTO `tbl_bank_details` VALUES ('8', 'vdsr', null, 'rgs', null, null, 'grs', '1', '2026-02-18 11:22:03');
INSERT INTO `tbl_bank_details` VALUES ('9', 'das', null, 'ads', null, null, 'das', '1', '2026-02-18 11:22:15');

-- ----------------------------
-- Table structure for `tbl_bookings`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_bookings`;
CREATE TABLE `tbl_bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `booking_from` date NOT NULL,
  `booking_to` date NOT NULL,
  `check_in` date DEFAULT NULL,
  `check_out` date DEFAULT NULL,
  `no_of_persons` int(11) DEFAULT '1',
  `status` enum('Booked','CheckedIn','CheckedOut','Cancelled') DEFAULT 'Booked',
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  KEY `fk_tbl_booking_customer` (`customer_id`),
  KEY `idx_tbl_booking_asset` (`asset_id`),
  KEY `idx_tbl_booking_dates` (`booking_from`,`booking_to`),
  CONSTRAINT `tbl_bookings_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `tbl_assets` (`asset_id`),
  CONSTRAINT `tbl_bookings_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `tbl_customers` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_bookings
-- ----------------------------
INSERT INTO `tbl_bookings` VALUES ('10', '2', '1', '2026-02-16', '2026-02-17', null, null, '4', 'Booked', null, '2026-02-16 11:05:41');
INSERT INTO `tbl_bookings` VALUES ('11', '1', '2', '2026-02-17', '2026-02-18', null, null, '5', 'Booked', null, '2026-02-16 11:08:24');
INSERT INTO `tbl_bookings` VALUES ('12', '1', '1', '2026-02-20', '2026-02-26', null, null, '4', 'Booked', null, '2026-02-16 11:16:00');
INSERT INTO `tbl_bookings` VALUES ('13', '1', '1', '2026-03-16', '2026-03-18', null, null, '5', 'Booked', null, '2026-02-16 14:16:43');
INSERT INTO `tbl_bookings` VALUES ('14', '1', '1', '2026-04-21', '2026-04-23', null, null, '2', 'Booked', null, '2026-02-19 17:31:10');
INSERT INTO `tbl_bookings` VALUES ('15', '1', '1', '2026-04-14', '2026-04-15', null, null, '5', 'Booked', null, '2026-02-20 11:11:23');
INSERT INTO `tbl_bookings` VALUES ('16', '1', '1', '2025-12-29', '2026-01-30', null, null, '5', 'Booked', null, '2026-02-20 11:12:50');
INSERT INTO `tbl_bookings` VALUES ('17', '2', '1', '2026-02-26', '2026-02-27', null, null, '5', 'Booked', null, '2026-02-20 13:00:51');
INSERT INTO `tbl_bookings` VALUES ('18', '2', '1', '2026-02-21', '2026-02-25', null, null, '5', 'Booked', null, '2026-02-20 13:10:03');
INSERT INTO `tbl_bookings` VALUES ('19', '2', '1', '2026-04-21', '2026-05-22', null, null, '8', 'Booked', null, '2026-02-20 13:16:36');
INSERT INTO `tbl_bookings` VALUES ('20', '1', '1', '2026-05-21', '2026-05-23', null, null, '12', 'Booked', null, '2026-02-20 16:33:14');
INSERT INTO `tbl_bookings` VALUES ('21', '1', '1', '2026-05-30', '2026-05-31', null, null, '2', 'Booked', null, '2026-02-20 17:42:08');
INSERT INTO `tbl_bookings` VALUES ('22', '1', '1', '2026-04-01', '2026-04-02', null, null, '2', 'Booked', null, '2026-02-20 17:48:59');

-- ----------------------------
-- Table structure for `tbl_customers`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_customers`;
CREATE TABLE `tbl_customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `aadhaar_no` varchar(12) DEFAULT NULL,
  `passport_no` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `country_of_origin` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `aadhaar_no` (`aadhaar_no`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_customers
-- ----------------------------
INSERT INTO `tbl_customers` VALUES ('1', 'test1', 'Indian', '123456789123', null, '1234567890', 'test1@gmil.com', 'andromeda galaxy', '2026-02-16 10:15:49', null);
INSERT INTO `tbl_customers` VALUES ('2', 'test2', 'Foreign', null, '789456123078', '7894561230', 'test2@gmil.com', 'Coma Berenices galaxy', '2026-02-16 10:17:51', null);
INSERT INTO `tbl_customers` VALUES ('3', 'sophia', 'Foreign', null, '456987123014', '2102112132', 'sophia@greece.com', '23, Î’Î¿ÏÎµÎ¯Î¿Ï… Î—Ï€ÎµÎ¯ÏÎ¿Ï… , Regional Unit of North Athens, Attica , greece', '2026-02-24 09:58:49', 'greece');

-- ----------------------------
-- Table structure for `tbl_invoices`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_invoices`;
CREATE TABLE `tbl_invoices` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `invoice_no` varchar(30) NOT NULL,
  `invoice_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `tax_amount` decimal(12,2) DEFAULT '0.00',
  `total_amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tarrif_per_day` decimal(10,2) DEFAULT NULL,
  `no_of_days` int(11) DEFAULT NULL,
  `gross_tariff` decimal(10,2) DEFAULT NULL,
  `cleaning_charges` decimal(10,2) DEFAULT NULL,
  `total_tariff` decimal(10,2) DEFAULT NULL,
  `deduction_1` decimal(10,2) DEFAULT NULL,
  `deduction_2` decimal(10,2) DEFAULT NULL,
  `net_amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `idx_tbl_invoice_booking` (`booking_id`),
  CONSTRAINT `tbl_invoices_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `tbl_bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_invoices
-- ----------------------------
INSERT INTO `tbl_invoices` VALUES ('8', '10', 'INV202602160010', '2026-02-16', '5000.00', '0.00', '5000.00', '2026-02-16 11:05:41', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('9', '11', 'INV202602160011', '2026-02-16', '8000.00', '0.00', '8000.00', '2026-02-16 11:08:24', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('10', '12', 'INV202602160012', '2026-02-16', '48000.00', '0.00', '48000.00', '2026-02-16 11:16:00', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('11', '13', 'INV202602160013', '2026-02-16', '16000.00', '0.00', '16000.00', '2026-02-16 14:16:43', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('12', '14', 'INV202602190014', '2026-02-19', '80000.00', '0.00', '80000.00', '2026-02-19 17:31:10', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('13', '15', 'INV202602200015', '2026-02-20', '9000.00', '0.00', '9000.00', '2026-02-20 11:11:23', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('14', '16', 'INV202602200016', '2026-02-20', '256000.00', '0.00', '256000.00', '2026-02-20 11:12:50', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('15', '17', 'INV202602200017', '2026-02-20', '7418.00', '0.00', '7418.00', '2026-02-20 13:00:51', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('16', '18', 'INV202602200018', '2026-02-20', '21816.00', '0.00', '21816.00', '2026-02-20 13:10:03', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('17', '19', 'INV202602200019', '2026-02-20', '248000.00', '0.00', '248000.00', '2026-02-20 13:16:36', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('18', '20', 'INV202602200020', '2026-02-20', '22.00', '0.00', '22.00', '2026-02-20 16:33:14', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('19', '21', 'INV202602200021', '2026-02-20', '11.00', '0.00', '11.00', '2026-02-20 17:42:08', null, null, null, null, null, null, null, null);
INSERT INTO `tbl_invoices` VALUES ('20', '22', 'INV202602200022', '2026-02-20', '1.00', '0.00', '1.00', '2026-02-20 17:48:59', null, null, null, null, null, null, null, null);

-- ----------------------------
-- Table structure for `tbl_payments`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_payments`;
CREATE TABLE `tbl_payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_mode` enum('Cash','UPI','Card','BankTransfer') NOT NULL,
  `bank_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `fk_tbl_payment_invoice` (`invoice_id`),
  CONSTRAINT `fk_payment_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_invoices` (`invoice_id`) ON DELETE CASCADE,
  CONSTRAINT `tbl_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_invoices` (`invoice_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_payments
-- ----------------------------
INSERT INTO `tbl_payments` VALUES ('3', '8', '2026-02-16 11:07:44', 'Cash', null, '5000.00', '', '2026-02-16 11:07:44');
INSERT INTO `tbl_payments` VALUES ('4', '8', '2026-02-16 11:07:52', 'Cash', null, '5000.00', '', '2026-02-16 11:07:52');
INSERT INTO `tbl_payments` VALUES ('5', '9', '2026-02-16 11:08:57', 'Cash', null, '6000.00', '', '2026-02-16 11:08:57');
INSERT INTO `tbl_payments` VALUES ('6', '9', '2026-02-16 11:09:10', '', '3', '2000.00', '', '2026-02-16 11:09:10');
INSERT INTO `tbl_payments` VALUES ('7', '10', '2026-02-16 13:08:53', 'Cash', null, '5000.00', '', '2026-02-16 13:08:53');
INSERT INTO `tbl_payments` VALUES ('8', '10', '2026-02-16 14:13:23', '', '4', '4.00', '47', '2026-02-16 14:13:23');
INSERT INTO `tbl_payments` VALUES ('9', '10', '2026-02-16 14:14:58', '', '5', '5.00', '255', '2026-02-16 14:14:58');
INSERT INTO `tbl_payments` VALUES ('10', '8', '2026-02-16 14:15:25', '', '6', '555.00', '5858585', '2026-02-16 14:15:25');
INSERT INTO `tbl_payments` VALUES ('11', '11', '2026-02-16 14:17:50', '', '7', '15000.00', '5151', '2026-02-16 14:17:50');
INSERT INTO `tbl_payments` VALUES ('12', '11', '2026-02-17 09:45:31', 'Cash', null, '1000.00', '', '2026-02-17 09:45:31');
INSERT INTO `tbl_payments` VALUES ('13', '10', '2026-02-17 09:46:23', 'Cash', null, '500.00', '', '2026-02-17 09:46:23');
INSERT INTO `tbl_payments` VALUES ('14', '11', '2026-02-17 09:46:59', 'Cash', null, '500.00', '', '2026-02-17 09:46:59');
INSERT INTO `tbl_payments` VALUES ('15', '11', '2026-02-17 09:47:08', 'Cash', null, '1000.00', '', '2026-02-17 09:47:08');
INSERT INTO `tbl_payments` VALUES ('16', '11', '2026-02-18 11:22:03', '', '8', '5000.00', 'dgs', '2026-02-18 11:22:03');
INSERT INTO `tbl_payments` VALUES ('17', '9', '2026-02-18 11:22:15', '', '9', '1.00', 'das', '2026-02-18 11:22:15');

-- ----------------------------
-- Table structure for `tbl_rates`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_rates`;
CREATE TABLE `tbl_rates` (
  `rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `rate_per_day` decimal(10,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rate_weekend` decimal(10,2) DEFAULT NULL,
  `rate_weekday` decimal(10,2) DEFAULT NULL,
  `rate_consession` decimal(10,2) DEFAULT NULL,
  `rate_long_stay` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `fk_tbl_rates_asset` (`asset_id`),
  CONSTRAINT `tbl_rates_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `tbl_assets` (`asset_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_rates
-- ----------------------------
INSERT INTO `tbl_rates` VALUES ('1', '1', '8000.00', '2025-01-01', '2025-12-31', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('2', '2', '5000.00', '2025-01-01', '2025-12-31', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('3', '3', '4000.00', '2026-01-01', '2026-12-31', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('4', '3', '3500.00', '2025-01-01', '2025-12-31', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('5', '3', '3000.00', '2024-01-01', '2024-12-31', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('6', '1', '9000.00', '2026-01-01', '2026-02-19', '2026-02-20 16:49:26', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('15', '3', '4500.00', '2027-01-01', '2026-12-31', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('16', '2', '6000.00', '2026-01-01', '2026-02-19', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('17', '1', '1.00', '2026-02-20', '2026-05-20', '2026-02-20 16:49:26', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('18', '2', '5454.00', '2026-02-20', '2026-04-19', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('19', '2', '8000.00', '2026-04-20', '2036-12-31', '2026-02-20 16:24:28', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('20', '1', '11.00', '2026-05-21', '2026-05-24', '2026-02-20 17:43:13', null, null, null, null);
INSERT INTO `tbl_rates` VALUES ('21', '1', '800.00', '2026-05-25', '2036-10-07', '2026-02-20 17:43:13', null, null, null, null);

-- ----------------------------
-- Table structure for `tbl_users`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Manager','Staff') DEFAULT 'Staff',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_users
-- ----------------------------
INSERT INTO `tbl_users` VALUES ('1', 'admin', 'admin@gmail.com', '$2y$10$Yctjsat9zr0ltcTjpw6s0uGzDaEqE.RpKc4D6h006PKEAhK41CXui', 'Admin', '1', '2026-02-16 00:00:00', null);
INSERT INTO `tbl_users` VALUES ('2', 'Manager', 'manager@gmail.com', '$2y$10$KfLXcOQkV25/.n7WEMnhB.FbctBD7dPd8sHEFfFy5amMGqbipsiS.', 'Staff', '1', '2026-02-16 10:11:29', null);
INSERT INTO `tbl_users` VALUES ('3', 'staff', 'staff@gmail.com', '$2y$10$QKg4.RDm2RWFUmbl3NRCtuoQlD7s.jM3JE153HFecgNnzCBN7bYWG', 'Staff', '1', '2026-02-16 10:12:03', null);
INSERT INTO `tbl_users` VALUES ('4', 'staff2', 'saff@gmail.com', '$2y$10$Qsl0qNNbkITYfUJPvF4GSOO0H2lbeRHEVgVvWwyz9hpE3lO7A0xea', 'Staff', '1', '2026-02-20 17:56:12', null);
