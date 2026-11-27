/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.23-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: greenDash
-- ------------------------------------------------------
-- Server version	10.6.23-MariaDB-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stall_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stall_id` (`stall_id`,`name`),
  UNIQUE KEY `id` (`id`,`stall_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (5,1,'Brewed Coffee'),(10,1,'Christmas Specials'),(2,1,'Featured Drinks'),(3,1,'Featured Food'),(1,1,'Popular'),(4,1,'Refreshers'),(6,3,'Classic Blizzard'),(7,3,'Special Blizzard'),(8,5,'Fries'),(9,6,'Rice Bowls');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_item_options`
--

DROP TABLE IF EXISTS `order_item_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_item_options` (
  `order_item_option_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_item_id` int(11) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `option_group_name` varchar(100) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `extra_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`order_item_option_id`),
  KEY `idx_order_item_options_item_id` (`order_item_id`),
  CONSTRAINT `order_item_options_ibfk_1` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_options`
--

LOCK TABLES `order_item_options` WRITE;
/*!40000 ALTER TABLE `order_item_options` DISABLE KEYS */;
INSERT INTO `order_item_options` VALUES (1,1,NULL,'Choice of milk','Non-fat',1,0.00),(2,1,NULL,'Choice of size','Grande',1,20.00),(3,2,NULL,'Choice of milk','Non-fat',1,0.00),(4,2,NULL,'Choice of size','Tall',1,10.00),(5,3,1,'Size','Tall',1,10.00),(6,3,4,'Choice of Milk','Whole',1,0.00),(7,4,1,'Size','Tall',1,10.00),(8,4,4,'Choice of Milk','Whole',1,0.00),(9,6,8,'Portion','Large',1,25.00),(10,7,11,'Sauce','Extra Garlic',1,10.00),(11,8,14,'Add-on','Egg',1,15.00),(12,9,9,'Portion','Mega',1,50.00),(13,10,10,'Sauce','Classic Soy',1,0.00),(14,11,11,'Sauce','Extra Garlic',1,10.00),(15,12,7,'Portion','Regular',1,0.00),(16,13,2,'Size','Grande',1,20.00),(17,13,6,'Choice of Milk','Sub Oatmilk',1,50.00),(18,16,1,'Size','Tall',1,10.00),(19,16,4,'Choice of Milk','Whole',1,0.00),(20,17,1,'Size','Tall',1,10.00),(21,17,4,'Choice of Milk','Whole',1,0.00),(22,18,2,'Size','Grande',1,20.00),(23,18,5,'Choice of Milk','Non-fat',1,0.00),(24,19,1,'Size','Tall',1,10.00),(25,19,4,'Choice of Milk','Whole',1,0.00),(26,20,1,'Size','Tall',1,10.00),(27,20,6,'Choice of Milk','Sub Oatmilk',1,50.00),(28,21,1,'Size','Tall',1,10.00),(29,21,6,'Choice of Milk','Sub Oatmilk',1,50.00),(30,22,1,'Size','Tall',1,10.00),(31,22,4,'Choice of Milk','Whole',1,0.00),(32,23,1,'Size','Tall',1,10.00),(33,23,4,'Choice of Milk','Whole',1,0.00),(34,24,1,'Size','Tall',1,10.00),(35,24,4,'Choice of Milk','Whole',1,0.00),(36,27,1,'Size','Tall',1,10.00),(37,27,5,'Choice of Milk','Non-fat',1,0.00),(38,28,1,'Size','Tall',1,10.00),(39,28,4,'Choice of Milk','Whole',1,0.00),(40,29,1,'Size','Tall',1,10.00),(41,29,4,'Choice of Milk','Whole',1,0.00),(42,30,1,'Size','Tall',1,10.00),(43,30,4,'Choice of Milk','Whole',1,0.00);
/*!40000 ALTER TABLE `order_item_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `item_name` varchar(150) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL,
  `special_instructions` varchar(255) DEFAULT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `base_price` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_items` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,'Java Chip Frappuccino',NULL,NULL,1,210.00,230.00,230.00),(2,2,3,'Caramel Macchiato',NULL,NULL,1,210.00,220.00,220.00),(3,3,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(4,4,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(5,6,3,'Caramel Macchiato',NULL,NULL,2,210.00,210.00,420.00),(6,7,8,'Cheese Fries','Fries',NULL,1,75.00,100.00,100.00),(7,8,9,'Chicken Rice Bowl','Rice Bowls',NULL,2,120.00,130.00,260.00),(8,9,10,'Vegetable Rice Bowl','Rice Bowls',NULL,1,105.00,120.00,120.00),(9,10,8,'Cheese Fries','Fries',NULL,2,75.00,125.00,250.00),(10,11,9,'Chicken Rice Bowl','Rice Bowls',NULL,1,120.00,120.00,120.00),(11,12,9,'Chicken Rice Bowl','Rice Bowls',NULL,1,120.00,130.00,130.00),(12,13,8,'Cheese Fries','Fries',NULL,1,75.00,75.00,75.00),(13,14,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,280.00,280.00),(14,15,3,'Caramel Macchiato',NULL,NULL,1,210.00,210.00,210.00),(15,16,3,'Caramel Macchiato',NULL,NULL,1,210.00,210.00,210.00),(16,17,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(17,18,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(18,19,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,230.00,230.00),(19,20,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(20,21,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,270.00,270.00),(21,22,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,270.00,270.00),(22,23,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(23,24,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(24,26,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(25,26,3,'Caramel Macchiato',NULL,NULL,1,210.00,210.00,210.00),(26,27,7,'KitKat Blizzard',NULL,NULL,1,119.00,119.00,119.00),(27,28,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(28,29,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(29,30,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(30,31,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stall_id` int(11) DEFAULT NULL,
  `stall_name` varchar(150) NOT NULL,
  `stall_location` varchar(150) NOT NULL,
  `status` enum('pending','preparing','ready','completed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_method` enum('cash','online') NOT NULL,
  `payment_status` enum('pending','paid','not_charged') NOT NULL DEFAULT 'pending',
  `payment_reference` varchar(40) DEFAULT NULL,
  `pickup_type` enum('now','scheduled') NOT NULL,
  `pickup_at` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `orders_user_created` (`user_id`,`created_at`),
  KEY `orders_stall_status` (`stall_id`,`status`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'GD77310EDACB484B59',5,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,230.00,'2026-11-12 08:30:58'),(2,'GDC1E5B60C927C5EF9',2,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,220.00,'2026-11-12 09:47:30'),(3,'GD58866C45BD3DE637',1,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,220.00,'2026-11-12 12:27:49'),(4,'GD95CE1B5D37A26945',1,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,220.00,'2026-11-12 13:56:36'),(5,'TEST001',1,1,'Starbucks','Green Court','completed','cash','paid',NULL,'now','2026-11-12 14:07:56',250.00,'2026-11-12 14:07:56'),(6,'GD705AEBCFDBDFCEE3',1,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'scheduled','2026-11-12 22:15:00',420.00,'2026-11-12 14:09:22'),(7,'GDDEMO202611130001',5,5,'Demo Potato Corner','Bloemen','completed','cash','paid',NULL,'now',NULL,100.00,'2026-11-12 11:00:00'),(8,'GDDEMO202611130002',5,6,'Demo Rice Bowls','Gokongwei Hall','completed','online','paid','DEMO-PAYMENT-0002','now',NULL,260.00,'2026-11-12 12:00:00'),(9,'GDDEMO202611130003',5,6,'Demo Rice Bowls','Gokongwei Hall','completed','cash','paid',NULL,'scheduled','2026-11-13 11:30:00',120.00,'2026-11-13 11:00:00'),(10,'GDDEMO202611130004',5,5,'Demo Potato Corner','Bloemen','completed','cash','paid',NULL,'now',NULL,250.00,'2026-11-13 12:00:00'),(11,'GDDEMO202611130005',5,6,'Demo Rice Bowls','Gokongwei Hall','completed','online','paid','DEMO-PAYMENT-0005','now',NULL,120.00,'2026-11-13 12:30:00'),(12,'GDDEMO202611130006',5,6,'Demo Rice Bowls','Gokongwei Hall','preparing','cash','pending',NULL,'now',NULL,130.00,'2026-11-13 13:00:00'),(13,'GDDEMO202611130007',5,5,'Demo Potato Corner','Bloemen','ready','online','pending','DEMO-PAYMENT-0007','now',NULL,75.00,'2026-11-13 13:10:00'),(14,'GDA6B30CE5DF57F4EF',2,1,'Starbucks','Green Court','cancelled','online','not_charged','111111','now',NULL,280.00,'2026-11-13 01:07:31'),(15,'GD545D5E8E511F6051',5,1,'Starbucks','Green Court','pending','cash','pending',NULL,'now',NULL,210.00,'2026-11-13 01:08:04'),(16,'GDA5B2AC570FC63309',5,1,'Starbucks','Green Court','pending','cash','pending',NULL,'scheduled','2026-11-13 09:15:00',210.00,'2026-11-13 01:13:46'),(17,'GDAEF0C6E785A098A3',2,1,'Starbucks','Green Court','cancelled','online','not_charged','ANSNDAJHS6710','scheduled','2026-11-13 14:15:00',220.00,'2026-11-13 01:14:32'),(18,'GD2512B608F9401A4C',2,1,'Starbucks','Green Court','cancelled','online','not_charged','WWJJGSASCGGH','scheduled','2026-11-13 09:45:00',220.00,'2026-11-13 01:17:39'),(19,'GDD1B451652BA8D1E6',2,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'scheduled','2026-11-13 09:30:00',230.00,'2026-11-13 01:24:19'),(20,'GDFC4D9ABBD3A15518',2,1,'Starbucks','Green Court','cancelled','online','not_charged','ADHSASBDSJH!11','now',NULL,220.00,'2026-11-13 01:33:12'),(21,'GDDA2F89111211C095',2,1,'Starbucks','Green Court','cancelled','online','not_charged','NDSAASNJS1777189','scheduled','2026-11-13 11:15:00',270.00,'2026-11-13 01:39:38'),(22,'GD54243711D98BE8B9',2,1,'Starbucks','Green Court','cancelled','online','not_charged','ffghfav7754g','scheduled','2026-11-13 11:00:00',270.00,'2026-11-13 01:52:42'),(23,'GD7592EBF8B553671B',2,1,'Starbucks','Green Court','pending','online','pending','1122dGAGDAD','scheduled','2026-11-20 11:30:00',220.00,'2026-11-20 02:02:51'),(24,'GD64448581F2E7ADE8',2,1,'Starbucks','Green Court','pending','cash','pending',NULL,'now',NULL,220.00,'2026-11-20 02:18:07'),(25,'TEST004',1,1,'Starbucks','Green Court','preparing','cash','pending',NULL,'now',NULL,100.00,'2026-11-25 15:35:42'),(26,'GD8EBC3FD4E0B5746D',1,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,430.00,'2026-11-26 06:44:46'),(27,'GD9660E5667921D205',1,3,'Dairy Queen','Agno','cancelled','cash','not_charged',NULL,'now',NULL,119.00,'2026-11-26 06:46:55'),(28,'GD2C1D0ACCCC9F1548',5,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,220.00,'2026-11-26 10:54:40'),(29,'GD54D8CF8820768693',1,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,220.00,'2026-11-26 22:08:49'),(30,'GD1162F19684F9EE31',1,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,220.00,'2026-11-26 22:34:30'),(31,'GDBD1BFA649C8A2333',1,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,220.00,'2026-11-26 22:50:34');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `product_categories_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_categories`
--

LOCK TABLES `product_categories` WRITE;
/*!40000 ALTER TABLE `product_categories` DISABLE KEYS */;
INSERT INTO `product_categories` VALUES (2,1),(3,2),(4,6),(5,6),(6,7),(7,7),(8,8),(9,9),(10,9),(11,1),(11,2),(11,10),(12,1),(12,10);
/*!40000 ALTER TABLE `product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_option_choices`
--

DROP TABLE IF EXISTS `product_option_choices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_option_choices` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `extra_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`option_id`),
  KEY `product_option_choices_group` (`group_id`),
  CONSTRAINT `product_option_choices_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `product_option_groups` (`group_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_option_choices`
--

LOCK TABLES `product_option_choices` WRITE;
/*!40000 ALTER TABLE `product_option_choices` DISABLE KEYS */;
INSERT INTO `product_option_choices` VALUES (1,1,'Tall',10.00,1,0),(2,1,'Grande',20.00,1,1),(3,1,'Venti',45.00,1,2),(4,2,'Whole',0.00,1,0),(5,2,'Non-fat',0.00,1,1),(6,2,'Sub Oatmilk',50.00,1,2),(7,3,'Regular',0.00,1,0),(8,3,'Large',25.00,1,1),(9,3,'Mega',50.00,1,2),(10,4,'Classic Soy',0.00,1,0),(11,4,'Extra Garlic',10.00,1,1),(12,4,'Spicy',0.00,0,2),(13,5,'Tofu',0.00,1,0),(14,5,'Egg',15.00,1,1);
/*!40000 ALTER TABLE `product_option_choices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_option_groups`
--

DROP TABLE IF EXISTS `product_option_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_option_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`),
  KEY `product_option_groups_product` (`product_id`),
  CONSTRAINT `product_option_groups_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_option_groups`
--

LOCK TABLES `product_option_groups` WRITE;
/*!40000 ALTER TABLE `product_option_groups` DISABLE KEYS */;
INSERT INTO `product_option_groups` VALUES (1,2,'Size',1,0),(2,2,'Choice of Milk',1,1),(3,8,'Portion',1,0),(4,9,'Sauce',0,0),(5,10,'Add-on',0,0);
/*!40000 ALTER TABLE `product_option_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stall_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `banner_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stall_id` (`stall_id`),
  KEY `category_id` (`category_id`,`stall_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`),
  CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`, `stall_id`) REFERENCES `categories` (`id`, `stall_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (2,1,1,'Java Chip Frappuccino','Java Chip Frappuccino',210.00,'../assets/starbucksMenu/javachip.png',1,NULL),(3,1,2,'Caramel Macchiato','Caramel Macchiato',210.00,'../assets/starbucksMenu/caramel.png',1,NULL),(4,3,6,'Oreo Blizzard',NULL,99.00,NULL,1,NULL),(5,3,6,'Chocolate Chip Blizzard',NULL,99.00,NULL,1,NULL),(6,3,7,'Rocky Road Blizzard',NULL,119.00,NULL,1,NULL),(7,3,7,'KitKat Blizzard',NULL,119.00,NULL,1,NULL),(8,5,8,'Cheese Fries','Fries with cheese seasoning and a selectable portion.',75.00,NULL,1,NULL),(9,6,9,'Chicken Rice Bowl','Chicken and rice with a choice of sauce.',120.00,NULL,1,NULL),(10,6,9,'Vegetable Rice Bowl','Vegetables and rice with an optional add-on.',105.00,NULL,1,NULL),(11,1,1,'Peppermint Mocha','Espresso, steamed milk, mocha sauce, and peppermint syrup, topped with whipped cream and festive peppermint or dark chocolate toppings—available hot, iced, or blended as a Frappuccino.',220.00,'assets/main/no-image-set.png',1,NULL),(12,1,1,'Free Food',NULL,233.00,'assets/main/no-image-set.png',1,NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `uq_reviews_order` (`order_id`),
  CONSTRAINT `fk_reviews_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_reviews_rating` CHECK (`rating` between 1 and 5)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (1,7,5,'Quick pickup and the fries were still warm.','2026-11-13 14:00:00','2026-11-13 14:00:00'),(2,8,4,'The sauce choice was correct and the serving was generous.','2026-11-13 14:00:00','2026-11-13 14:00:00'),(3,9,5,NULL,'2026-11-13 14:00:00','2026-11-13 14:00:00'),(4,10,3,'The food tasted good, but pickup took longer than expected.','2026-11-13 14:00:00','2026-11-13 14:00:00'),(5,11,3,'A&B \"Special\" <Large>','2026-11-13 14:00:00','2026-11-26 11:40:10');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stall_operating_hours`
--

DROP TABLE IF EXISTS `stall_operating_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stall_operating_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stall_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '0=Sunday ... 6=Saturday',
  `opens_at` time NOT NULL,
  `closes_at` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stall_id` (`stall_id`,`day_of_week`),
  CONSTRAINT `stall_operating_hours_ibfk_1` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stall_operating_hours`
--

LOCK TABLES `stall_operating_hours` WRITE;
/*!40000 ALTER TABLE `stall_operating_hours` DISABLE KEYS */;
INSERT INTO `stall_operating_hours` VALUES (9,1,0,'06:00:00','22:30:00'),(10,1,1,'06:00:00','22:30:00'),(11,1,2,'06:00:00','22:30:00'),(12,1,3,'06:00:00','22:30:00'),(13,1,4,'06:00:00','22:30:00'),(14,1,5,'06:00:00','22:30:00'),(15,1,6,'06:00:00','22:30:00'),(30,3,0,'08:00:00','17:00:00'),(31,3,1,'08:00:00','17:00:00'),(32,3,2,'08:00:00','17:00:00'),(33,3,3,'08:00:00','17:00:00'),(34,3,4,'08:00:00','17:00:00'),(35,3,5,'08:00:00','17:00:00'),(36,3,6,'08:00:00','17:00:00'),(37,4,0,'08:00:00','17:00:00'),(38,4,1,'08:00:00','17:00:00'),(39,4,2,'08:00:00','17:00:00'),(40,4,3,'08:00:00','17:00:00'),(41,4,4,'08:00:00','17:00:00'),(42,4,5,'08:00:00','17:00:00'),(43,4,6,'08:00:00','17:00:00'),(44,5,0,'09:00:00','20:00:00'),(45,5,1,'09:00:00','20:00:00'),(46,5,2,'09:00:00','20:00:00'),(47,5,3,'09:00:00','20:00:00'),(48,5,4,'09:00:00','20:00:00'),(49,5,5,'09:00:00','20:00:00'),(50,5,6,'09:00:00','20:00:00'),(51,6,0,'10:00:00','19:00:00'),(52,6,1,'10:00:00','19:00:00'),(53,6,2,'10:00:00','19:00:00'),(54,6,3,'10:00:00','19:00:00'),(55,6,4,'10:00:00','19:00:00'),(56,6,5,'10:00:00','19:00:00'),(57,6,6,'10:00:00','19:00:00');
/*!40000 ALTER TABLE `stall_operating_hours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stalls`
--

DROP TABLE IF EXISTS `stalls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stalls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `stall_type` varchar(100) DEFAULT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `image_path` varchar(255) DEFAULT NULL,
  `banner_path` varchar(255) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stalls`
--

LOCK TABLES `stalls` WRITE;
/*!40000 ALTER TABLE `stalls` DISABLE KEYS */;
INSERT INTO `stalls` VALUES (1,13,'Starbucks','Green Court','Enjoy your handcrafted coffee, refreshing beverages, delicious pastries, and savory bites.','coffee pastries drinks','drinks food',1,'../assets/stalls/starbucks.png','../assets/stalls/starbucks-banner.png','../assets/payment-qr/starbucks.png'),(3,3,'Dairy Queen','Agno','Treat yourself to creamy soft-serve ice cream, irresistible Blizzard treats, and delightful frozen desserts.','icecream blizzard freeze','dessert',1,'../assets/stalls/dq.png',NULL,NULL),(4,3,'The Famous Belgian Waffles','Bloemen','Indulge in freshly baked Belgian-style waffles filled with delicious sweet or savory flavors.','waffles hot snacks','food dessert',0,'../assets/stalls/belgian.png',NULL,NULL),(5,2,'Demo Potato Corner','Bloemen','Sample fries counter for the Phase 2 demonstration.','fries cheese snacks','food',1,'../assets/stalls/potcor.png',NULL,NULL),(6,2,'Demo Rice Bowls','Gokongwei Hall','Sample rice meals for the Phase 2 demonstration.','rice chicken lunch','food',1,'../assets/main/defaultAvatar.png',NULL,NULL);
/*!40000 ALTER TABLE `stalls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store_requests`
--

DROP TABLE IF EXISTS `store_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `store_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `type` enum('add','remove') NOT NULL,
  `status` enum('pending','approved','denied') NOT NULL DEFAULT 'pending',
  `proposed_name` varchar(150) DEFAULT NULL,
  `proposed_location` varchar(100) DEFAULT NULL,
  `proposed_stall_type` varchar(100) DEFAULT NULL,
  `proposed_hours` text DEFAULT NULL,
  `proposed_description` text DEFAULT NULL,
  `proposed_image_path` varchar(255) DEFAULT NULL,
  `proposed_qr_code_path` varchar(255) DEFAULT NULL,
  `stall_id` int(11) DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `stall_id` (`stall_id`),
  KEY `resolved_by` (`resolved_by`),
  CONSTRAINT `store_requests_ibfk_1` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `store_requests_ibfk_2` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`) ON DELETE SET NULL,
  CONSTRAINT `store_requests_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_requests`
--

LOCK TABLES `store_requests` WRITE;
/*!40000 ALTER TABLE `store_requests` DISABLE KEYS */;
INSERT INTO `store_requests` VALUES (1,2,'add','pending','Demo Fruit Cups','Agno',NULL,'Mon-Fri 09:00-17:00','{\"store_type\": \"Snacks\", \"note\": \"Fruit cup counter proposal.\"}',NULL,NULL,NULL,NULL,'2026-11-13 10:00:00',NULL),(2,2,'add','pending','Demo Sandwich Counter','Green Court',NULL,'Mon-Sat 09:00-18:00','{\"store_type\": \"Food stall\", \"note\": \"Sandwich counter proposal.\"}',NULL,NULL,NULL,NULL,'2026-11-13 10:30:00',NULL),(3,2,'add','denied','Demo Evening Snacks','Agno',NULL,'Fri-Sat 20:00-23:00','{\"store_type\": \"Snacks\", \"note\": \"The proposed location was unavailable.\"}',NULL,NULL,NULL,4,'2026-11-12 10:00:00','2026-11-12 11:00:00'),(4,2,'add','approved','Demo Potato Corner','Bloemen',NULL,'Mon-Sun 09:00-20:00','{\"store_type\": \"Snacks\", \"note\": \"Approved demonstration stall.\"}',NULL,NULL,5,4,'2026-11-11 10:30:00','2026-11-11 11:30:00'),(5,2,'add','approved','Demo Rice Bowls','Gokongwei Hall',NULL,'Mon-Sun 10:00-19:00','{\"store_type\": \"Restaurant\", \"note\": \"Approved demonstration stall.\"}',NULL,NULL,6,4,'2026-11-11 10:30:00','2026-11-11 11:30:00');
/*!40000 ALTER TABLE `store_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_carts`
--

DROP TABLE IF EXISTS `user_carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_carts` (
  `user_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items_json`)),
  `version` bigint(20) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`,`stall_id`),
  KEY `user_carts_stall_fk` (`stall_id`),
  CONSTRAINT `user_carts_stall_fk` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_carts_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_carts`
--

LOCK TABLES `user_carts` WRITE;
/*!40000 ALTER TABLE `user_carts` DISABLE KEYS */;
INSERT INTO `user_carts` VALUES (1,1,'[]',9),(1,3,'[]',2),(2,1,'[]',2),(5,1,'[{\"productId\":2,\"quantity\":1,\"specialInstructions\":\"\",\"options\":[{\"groupId\":1,\"optionId\":1},{\"groupId\":2,\"optionId\":4}]}]',3);
/*!40000 ALTER TABLE `user_carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_order_cancellations`
--

DROP TABLE IF EXISTS `user_order_cancellations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_order_cancellations` (
  `cancellation_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `cancelled_at` datetime NOT NULL,
  PRIMARY KEY (`cancellation_id`),
  UNIQUE KEY `unique_cancelled_order` (`order_id`),
  KEY `idx_user_cancelled_at` (`user_id`,`cancelled_at`),
  CONSTRAINT `fk_cancellation_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `fk_cancellation_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_order_cancellations`
--

LOCK TABLES `user_order_cancellations` WRITE;
/*!40000 ALTER TABLE `user_order_cancellations` DISABLE KEYS */;
INSERT INTO `user_order_cancellations` VALUES (1,1,6,'2026-11-25 23:28:06'),(2,1,4,'2026-11-25 23:30:38'),(3,1,3,'2026-11-25 23:30:58');
/*!40000 ALTER TABLE `user_order_cancellations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_add` varchar(150) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `contact_num` varchar(30) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `user_role` enum('admin','student','faculty','vendor','staff') DEFAULT NULL,
  `acc_status` enum('active','inactive','pending') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email_add`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'zoe_magaling@dlsu.edu.ph',NULL,'Zoe','Magaling',NULL,'https://lh3.googleusercontent.com/a/ACg8ocJbfEOB7un7lRtQvN8q7EN1aOWJevwiDVsgPYah3ue_1jmEhDGi=s96-c','student','active','2026-11-10 16:20:01','2026-11-26 22:32:13'),(2,'chelsea_dimaunahan@dlsu.edu.ph',NULL,'Chelsea','Jei Dimaunahan',NULL,NULL,'student','active','2026-11-11 01:29:52','2026-11-13 01:03:34'),(3,'raphael_gamboa@dlsu.edu.ph',NULL,'Raphael','Gamboa',NULL,'https://lh3.googleusercontent.com/a/ACg8ocJG8eZaZ8P1pUCS0rI5r41w6js7N1yt6pJxr-9xB-7KZd8IVQ=s96-c','vendor','active','2026-11-11 04:28:45','2026-11-27 00:20:18'),(4,'admin@dlsu.edu.ph',NULL,'System','Admin',NULL,NULL,'admin','active','2026-11-11 10:19:51','2026-11-12 17:50:20'),(5,'rinoa_mariel_sadey@dlsu.edu.ph',NULL,'Rinoa','Mariel Sadey',NULL,'https://lh3.googleusercontent.com/a/ACg8ocIe9HHOTZybD0v3-RuLbOji12Lk9b5NzhnxzfEMyOjNrf_gL5A=s96-c','student','active','2026-11-11 15:20:23','2026-11-27 00:13:55'),(7,'chelseajei0804@gmail.com','$2y$10$f/pOsH2/CfPcXTEFh5d17eM1ynpW9guOC4CdkGnt6AI1ZW6NyY.ti','Test','Wow',NULL,NULL,'vendor','inactive','2026-11-26 16:15:59','2026-11-26 23:20:52'),(8,'raphaelrgamboa@gmail.com','$2y$10$j4stv4oT5xnNPrdbipOZd.1KVX2QG/FrOEEqhmDoMZgqmAMo/.gBu','Raphael','Gamboa',NULL,NULL,'admin','active','2026-11-26 18:35:04','2026-11-26 18:35:04'),(9,'test1@email.com','$2y$10$rymMG8gU2Rt09HMKhsb9euo0/lJRSpBLyLRF5WSkphB9sXV0xTMai','Raf','Sandou',NULL,NULL,'vendor','pending','2026-11-26 22:32:53','2026-11-26 22:32:53'),(10,'ayasjpgs@gmail.com','$2y$10$8yie4dmB0xygzb4t/UtUyOuo8/8SKadTs4aWADhnedoA.mVbe8Kpy','Chelsea','Dimaunahan',NULL,NULL,'admin','active','2026-11-26 22:59:13','2026-11-26 22:59:13'),(11,'rinoa_sadey@dlsu.edu.ph',NULL,'Rinoa Mariel','Sadey',NULL,'https://lh3.googleusercontent.com/a/ACg8ocJRM5Bpz_XqMxY0RmZPgyrzykf2Vg-6tS05eLNNEniISmAdqv8=s96-c',NULL,NULL,'2026-11-26 23:54:44','2026-11-26 23:54:44'),(12,'test@mail.com','$2y$10$K5WHGZIiTPiBB.9LeChyP.EQ7R9bR9/tz.jc4Wg2ZQkZ5CAiRA4I.','Raph','Gamb',NULL,NULL,'vendor','active','2026-11-27 00:15:53','2026-11-27 00:18:36'),(13,'dimaunahan.chelseajei@gmail.com','$2y$10$sIFb0dAQ9K3uDEjw8v0db.Pm7kGd9nuDwz9hFuAsplUraymHhIuy.','Chelsea Jei','Dimaunahan',NULL,NULL,'vendor','active','2026-11-27 00:20:30','2026-11-27 00:27:17');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendor_order_cancellations`
--

DROP TABLE IF EXISTS `vendor_order_cancellations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendor_order_cancellations` (
  `order_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `reason` enum('standard','out_of_stock','utility_outage','emergency_closure') NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `cancelled_at` datetime NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `vendor_order_cancellations_week` (`vendor_id`,`reason`,`cancelled_at`),
  CONSTRAINT `vendor_order_cancellations_order_fk` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `vendor_order_cancellations_vendor_fk` FOREIGN KEY (`vendor_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendor_order_cancellations`
--

LOCK TABLES `vendor_order_cancellations` WRITE;
/*!40000 ALTER TABLE `vendor_order_cancellations` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendor_order_cancellations` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed
