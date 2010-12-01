-- phpMyAdmin SQL Dump
-- version 3.3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 01, 2010 at 02:09 PM
-- Server version: 5.1.48
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `weploy`
--

-- --------------------------------------------------------

--
-- Table structure for table `ploys`
--

CREATE TABLE IF NOT EXISTS `ploys` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `log` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `repository` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `revision` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
