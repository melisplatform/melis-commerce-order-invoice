-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Feb 14, 2019 at 03:37 PM
-- Server version: 5.5.62
-- PHP Version: 7.1.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `melisdev.local`
--

-- --------------------------------------------------------

--
-- Table structure for table `melis_ecom_order_invoice`
--

CREATE TABLE IF NOT EXISTS `melis_ecom_order_invoice` (
  `ordin_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Primary',
  `ordin_user_id` int(11) NOT NULL COMMENT 'User Id',
  `ordin_order_id` int(11) NOT NULL COMMENT 'Order Id',
  `ordin_date_generated` datetime NOT NULL COMMENT 'Date when PDF was generated',
  `ordin_invoice_pdf` longblob NOT NULL COMMENT 'The PDF file',
  PRIMARY KEY (`ordin_id`)
) ENGINE=InnoDB;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
