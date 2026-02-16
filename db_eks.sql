/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : db_eks

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2026-02-16 13:09:23
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
INSERT INTO `tbl_assets` VALUES ('1', '1', 'ek_first_floor', 'first floor', '1', '2026-02-16 10:13:28', '2026-02-16 10:13:28', '3.00', 'Active');
INSERT INTO `tbl_assets` VALUES ('2', '2', 'ek_two_bedrooms', 'two bedrooms', '1', '2026-02-16 10:13:53', '2026-02-16 10:13:53', '3.00', 'Active');
INSERT INTO `tbl_assets` VALUES ('3', '3', 'ek_single_room', 'single room', '1', '2026-02-16 10:14:11', '2026-02-16 10:14:11', '3.00', 'Active');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_bank_details
-- ----------------------------
INSERT INTO `tbl_bank_details` VALUES ('3', 'icci', null, '45', null, null, '', '1', '2026-02-16 11:09:10');

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_bookings
-- ----------------------------
INSERT INTO `tbl_bookings` VALUES ('10', '2', '1', '2026-02-16', '2026-02-17', null, null, '4', 'Booked', null, '2026-02-16 11:05:41');
INSERT INTO `tbl_bookings` VALUES ('11', '1', '2', '2026-02-17', '2026-02-18', null, null, '5', 'Booked', null, '2026-02-16 11:08:24');
INSERT INTO `tbl_bookings` VALUES ('12', '1', '1', '2026-02-20', '2026-02-26', null, null, '4', 'Booked', null, '2026-02-16 11:16:00');

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
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `aadhaar_no` (`aadhaar_no`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_customers
-- ----------------------------
INSERT INTO `tbl_customers` VALUES ('1', 'test1', 'Indian', '123456789123', null, '1234567890', 'test1@gmil.com', 'andromeda galaxy', '2026-02-16 10:15:49');
INSERT INTO `tbl_customers` VALUES ('2', 'test2', 'Foreign', null, '789456123078', '7894561230', 'test2@gmil.com', 'Coma Berenices galaxy', '2026-02-16 10:17:51');

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
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `invoice_no` (`invoice_no`),
  KEY `idx_tbl_invoice_booking` (`booking_id`),
  CONSTRAINT `tbl_invoices_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `tbl_bookings` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_invoices
-- ----------------------------
INSERT INTO `tbl_invoices` VALUES ('8', '10', 'INV202602160010', '2026-02-16', '5000.00', '0.00', '5000.00', '2026-02-16 11:05:41');
INSERT INTO `tbl_invoices` VALUES ('9', '11', 'INV202602160011', '2026-02-16', '8000.00', '0.00', '8000.00', '2026-02-16 11:08:24');
INSERT INTO `tbl_invoices` VALUES ('10', '12', 'INV202602160012', '2026-02-16', '48000.00', '0.00', '48000.00', '2026-02-16 11:16:00');

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_payments
-- ----------------------------
INSERT INTO `tbl_payments` VALUES ('3', '8', '2026-02-16 11:07:44', 'Cash', null, '5000.00', '', '2026-02-16 11:07:44');
INSERT INTO `tbl_payments` VALUES ('4', '8', '2026-02-16 11:07:52', 'Cash', null, '5000.00', '', '2026-02-16 11:07:52');
INSERT INTO `tbl_payments` VALUES ('5', '9', '2026-02-16 11:08:57', 'Cash', null, '6000.00', '', '2026-02-16 11:08:57');
INSERT INTO `tbl_payments` VALUES ('6', '9', '2026-02-16 11:09:10', '', '3', '2000.00', '', '2026-02-16 11:09:10');
INSERT INTO `tbl_payments` VALUES ('7', '10', '2026-02-16 13:08:53', 'Cash', null, '5000.00', '', '2026-02-16 13:08:53');

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
  PRIMARY KEY (`rate_id`),
  KEY `fk_tbl_rates_asset` (`asset_id`),
  CONSTRAINT `tbl_rates_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `tbl_assets` (`asset_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_rates
-- ----------------------------
INSERT INTO `tbl_rates` VALUES ('1', '1', '8000.00', '2026-02-16', '2036-12-31');
INSERT INTO `tbl_rates` VALUES ('2', '2', '5000.00', '2026-02-16', '2036-12-31');
INSERT INTO `tbl_rates` VALUES ('3', '3', '3000.00', '2026-02-16', '2026-02-16');
INSERT INTO `tbl_rates` VALUES ('4', '3', '30001.00', '2026-02-17', '2026-02-17');
INSERT INTO `tbl_rates` VALUES ('5', '3', '3001.00', '2026-02-18', '2036-12-31');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of tbl_users
-- ----------------------------
INSERT INTO `tbl_users` VALUES ('1', 'admin', 'admin@gmail.com', '$2y$10$Yctjsat9zr0ltcTjpw6s0uGzDaEqE.RpKc4D6h006PKEAhK41CXui', 'Admin', '1', '2026-02-16 00:00:00', null);
INSERT INTO `tbl_users` VALUES ('2', 'Manager', 'manager@gmail.com', '$2y$10$KfLXcOQkV25/.n7WEMnhB.FbctBD7dPd8sHEFfFy5amMGqbipsiS.', 'Admin', '1', '2026-02-16 10:11:29', null);
INSERT INTO `tbl_users` VALUES ('3', 'staff', 'staff@gmail.com', '$2y$10$QKg4.RDm2RWFUmbl3NRCtuoQlD7s.jM3JE153HFecgNnzCBN7bYWG', 'Staff', '1', '2026-02-16 10:12:03', null);
