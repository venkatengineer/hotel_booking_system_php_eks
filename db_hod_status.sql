/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : db_hod_status

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2026-02-18 15:34:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `stenographers`
-- ----------------------------
DROP TABLE IF EXISTS `stenographers`;
CREATE TABLE `stenographers` (
  `stenographer_id` int(11) NOT NULL AUTO_INCREMENT,
  `hod_id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`stenographer_id`),
  UNIQUE KEY `hod_id` (`hod_id`),
  UNIQUE KEY `username` (`username`),
  CONSTRAINT `stenographers_ibfk_1` FOREIGN KEY (`hod_id`) REFERENCES `tbl_hod_status` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of stenographers
-- ----------------------------
INSERT INTO `stenographers` VALUES ('1', '1', '690726', 'SIVAPRAKASAM.P', '$2y$10$5Dhy8KFUAdAhmF0akbpyte1u4r1jFzNfEq1s7jsbUx7fVnp15Drd.');
INSERT INTO `stenographers` VALUES ('2', '2', '773644', 'SUBRAMANIAN.R', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('3', '3', '784909', 'RAGHUPATHY.K', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('4', '4', '836115', 'AUSTIN VIJILAN.A', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('5', '5', '857872', 'ANBARASI DYNA CHELLADURAI', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('6', '6', '859878', 'SANAL KUMAR.M', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('7', '7', '675308', 'CHITRA', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('8', '8', '699632', 'NAGARAJ.AV', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('9', '9', '678276', 'VARADHARAJAN.B', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('10', '10', '860211', 'SAJASH PANICKAR.V.S.', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('11', '11', '695228', null, '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('12', '12', '675340', 'VENKATESAN.K', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('13', '13', '717829', 'SUBRAMANI.M', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('14', '14', '692123', 'SASHIKUMAR.P K', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('15', '15', '705887', 'KALAVATHI.K M', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('16', '16', '648915', 'RAMESH.R', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('17', '17', '818785', 'KOMALA.G', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('18', '18', '833029', 'KAMALA BAI.S', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('19', '19', '697590', 'SURESH KUMAR.KR', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('20', '20', '842996', 'MOHANA LAKSHMI.C', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('21', '21', '812949', 'VASUMATHY.V', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('22', '22', '692406', 'REVATHY MURALIDHARAN', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('23', '23', '705895', 'MALLIKA.S', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('24', '24', '863711', 'RAJESWARI.R', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('25', '25', '675307', 'CHITRA', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('26', '26', '668406', 'RAJASHREE ANANDAN', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('27', '27', '840940', 'LEENA.K.T', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('28', '28', '785371', 'BALACHANDRA BABU.M R', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('29', '29', '707946', 'SATHYA BAMA.K', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('30', '30', '675358', 'PADMAVATHI.J', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('31', '31', 'POST V', null, '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');
INSERT INTO `stenographers` VALUES ('32', '32', '826970', 'SREELATHA.P K', '$2y$10$2EsiiaWm7qaix2NIH6SWpe1TbMuuEh4nuJIbC47NAsXbjqELcSFD.');

-- ----------------------------
-- Table structure for `tbl_hod_status`
-- ----------------------------
DROP TABLE IF EXISTS `tbl_hod_status`;
CREATE TABLE `tbl_hod_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department` varchar(50) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `update_by` varchar(50) DEFAULT NULL,
  `last_updt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `remarks` varchar(254) DEFAULT NULL,
  `empno` varchar(6) DEFAULT NULL,
  `order_by` int(3) DEFAULT NULL,
  `status` enum('FREE','BUSY','NOT_AVAILABLE') NOT NULL DEFAULT 'FREE',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tbl_hod_status
-- ----------------------------
INSERT INTO `tbl_hod_status` VALUES ('1', 'ADMINISTRATION', 'GENERAL MANAGER', 'SIVAPRAKASAM.P', '2026-02-18 14:43:27', 'dfsfsgrgr', '690726', '1', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('2', 'ADMINISTRATION', 'CVO', 'SUBRAMANIAN.R', null, null, '773644', '2', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('3', 'ADMINISTRATION', 'SECY.TO GM', 'RAGHUPATHY.K', '2026-02-18 14:45:38', 'Gone On-duty to Delhi.', '784909', '3', 'NOT_AVAILABLE');
INSERT INTO `tbl_hod_status` VALUES ('4', 'ACCOUNTS', 'PFA', 'AUSTIN VIJILAN.A', '2026-02-18 14:36:34', 'sadfhjkl;', '836115', '4', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('5', 'ACCOUNTS', 'FA & CAO /Finance & General', 'ANBARASI DYNA CHELLADURAI', null, null, '857872', '5', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('6', 'ACCOUNTS', 'FA & CAO/Production & Stores', 'SANAL KUMAR.M', null, null, '859878', '6', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('7', 'CIVIL ENGINEERING', 'PCE', 'CHITRA', '2026-02-18 10:00:38', null, '675308', '7', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('8', 'ELECTRICAL', 'PCEE', 'NAGARAJ.AV', '2026-02-18 15:31:35', 'he is in meeting', '699632', '8', 'BUSY');
INSERT INTO `tbl_hod_status` VALUES ('9', 'ELECTRICAL', 'CEE/QA', 'VARADHARAJAN.B', null, null, '678276', '9', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('10', 'ELECTRICAL', 'CEGE', 'SAJASH PANICKAR.V.S.', null, null, '860211', '10', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('11', 'ELECTRICAL', 'CDE/ELEC', null, null, null, '695228', '11', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('12', 'MEDICAL', 'PCMO', 'VENKATESAN.K', null, null, '675340', '12', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('13', 'MECHANICAL', 'PCME', 'SUBRAMANI.M', null, null, '717829', '13', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('14', 'MECHANICAL', 'CAO/ICF', 'SASHIKUMAR.P K', null, null, '692123', '14', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('15', 'MECHANICAL', 'CPLE', 'KALAVATHI.K M', null, null, '705887', '15', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('16', 'MECHANICAL', 'CWE/LHB', 'RAMESH.R', null, null, '648915', '16', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('17', 'MECHANICAL', 'CWE/FUR', 'KOMALA.G', null, null, '818785', '17', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('18', 'MECHANICAL', 'CWE/SHELL', 'KAMALA BAI.S', null, null, '833029', '18', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('19', 'MECHANICAL', 'CWE/SPECIAL STOCKS', 'SURESH KUMAR.KR', null, null, '697590', '19', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('20', 'MECHANICAL', 'CME/QA', 'MOHANA LAKSHMI.C', null, null, '842996', '20', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('21', 'MECHANICAL', 'CME/MATERIALS', 'VASUMATHY.V', null, null, '812949', '21', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('22', 'MECHANICAL', 'CDE/MECH', 'REVATHY MURALIDHARAN', null, null, '692406', '22', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('23', 'PERSONNEL', 'PCPO', 'MALLIKA.S', null, null, '705895', '23', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('24', 'PERSONNEL', 'CPO/ADMIN', 'RAJESWARI.R', null, null, '863711', '24', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('25', 'STORES', 'PCMM', 'CHITRA', null, null, '675307', '25', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('26', 'STORES', 'CMM/ELEC', 'RAJASHREE ANANDAN', '2026-02-18 10:15:20', 'he is busy\r\n', '668406', '26', 'BUSY');
INSERT INTO `tbl_hod_status` VALUES ('27', 'STORES', 'CMM/FUR', 'LEENA.K.T', null, null, '840940', '27', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('28', 'STORES', 'CMM/PROJECT', 'BALACHANDRA BABU.M R', null, null, '785371', '28', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('29', 'STORES', 'CMM/SHELL', 'SATHYA BAMA.K', null, null, '707946', '29', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('30', 'STORES', 'CMM/G', 'PADMAVATHI.J', null, null, '675358', '30', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('31', 'STORES', 'CMM/ADMIN', null, null, '', 'POST V', '31', 'FREE');
INSERT INTO `tbl_hod_status` VALUES ('32', 'SECURITY', 'IG-CUM-PCSC', 'SREELATHA.P K', null, null, '826970', '32', 'FREE');
