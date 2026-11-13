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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (5,1,'Brewed Coffee'),(2,1,'Featured Drinks'),(3,1,'Featured Food'),(1,1,'Popular'),(4,1,'Refreshers');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_item_options`
--

LOCK TABLES `order_item_options` WRITE;
/*!40000 ALTER TABLE `order_item_options` DISABLE KEYS */;
INSERT INTO `order_item_options` VALUES (1,1,NULL,'Choice of milk','Non-fat',1,0.00),(2,1,NULL,'Choice of size','Grande',1,20.00),(3,2,NULL,'Choice of milk','Non-fat',1,0.00),(4,2,NULL,'Choice of size','Tall',1,10.00),(5,3,1,'Size','Tall',1,10.00),(6,3,4,'Choice of Milk','Whole',1,0.00),(7,4,1,'Size','Tall',1,10.00),(8,4,4,'Choice of Milk','Whole',1,0.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,'Java Chip Frappuccino',NULL,NULL,1,210.00,230.00,230.00),(2,2,3,'Caramel Macchiato',NULL,NULL,1,210.00,220.00,220.00),(3,3,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(4,4,2,'Java Chip Frappuccino',NULL,NULL,1,210.00,220.00,220.00),(5,6,3,'Caramel Macchiato',NULL,NULL,2,210.00,210.00,420.00);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'GD77310EDACB484B59',5,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,230.00,'2026-11-12 08:30:58'),(2,'GDC1E5B60C927C5EF9',2,1,'Starbucks','Green Court','cancelled','cash','not_charged',NULL,'now',NULL,220.00,'2026-11-12 09:47:30'),(3,'GD58866C45BD3DE637',1,1,'Starbucks','Green Court','pending','cash','pending',NULL,'now',NULL,220.00,'2026-11-12 12:27:49'),(4,'GD95CE1B5D37A26945',1,1,'Starbucks','Green Court','pending','cash','pending',NULL,'now',NULL,220.00,'2026-11-12 13:56:36'),(5,'TEST001',1,1,'Starbucks','Green Court','completed','cash','paid',NULL,'now','2026-11-12 14:07:56',250.00,'2026-11-12 14:07:56'),(6,'GD705AEBCFDBDFCEE3',1,1,'Starbucks','Green Court','pending','cash','pending',NULL,'scheduled','2026-11-12 22:15:00',420.00,'2026-11-12 14:09:22');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_option_choices`
--

LOCK TABLES `product_option_choices` WRITE;
/*!40000 ALTER TABLE `product_option_choices` DISABLE KEYS */;
INSERT INTO `product_option_choices` VALUES (1,1,'Tall',10.00,1,0),(2,1,'Grande',20.00,1,1),(3,1,'Venti',45.00,1,2),(4,2,'Whole',0.00,1,0),(5,2,'Non-fat',0.00,1,1),(6,2,'Sub Oatmilk',50.00,1,2);
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_option_groups`
--

LOCK TABLES `product_option_groups` WRITE;
/*!40000 ALTER TABLE `product_option_groups` DISABLE KEYS */;
INSERT INTO `product_option_groups` VALUES (1,2,'Size',1,0),(2,2,'Choice of Milk',1,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (2,1,1,'Java Chip Frappuccino','Java Chip Frappuccino',210.00,'../assets/starbucksMenu/javachip.png',1,NULL),(3,1,2,'Caramel Macchiato','Caramel Macchiato',210.00,'../assets/starbucksMenu/caramel.png',1,NULL);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
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
  `day_of_week` tinyint(4) NOT NULL COMMENT '1=Monday ... 7=Sunday',
  `opens_at` time NOT NULL,
  `closes_at` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stall_id` (`stall_id`,`day_of_week`),
  CONSTRAINT `stall_operating_hours_ibfk_1` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stall_operating_hours`
--

LOCK TABLES `stall_operating_hours` WRITE;
/*!40000 ALTER TABLE `stall_operating_hours` DISABLE KEYS */;
INSERT INTO `stall_operating_hours` VALUES (9,1,0,'06:00:00','22:30:00'),(10,1,1,'06:00:00','22:30:00'),(11,1,2,'06:00:00','22:30:00'),(12,1,3,'06:00:00','22:30:00'),(13,1,4,'06:00:00','22:30:00'),(14,1,5,'06:00:00','22:30:00'),(15,1,6,'06:00:00','22:30:00'),(16,3,0,'08:00:00','17:00:00'),(17,3,1,'08:00:00','17:00:00'),(18,3,2,'08:00:00','17:00:00'),(19,3,3,'08:00:00','17:00:00'),(20,3,4,'08:00:00','17:00:00'),(21,3,5,'08:00:00','17:00:00'),(22,3,6,'08:00:00','17:00:00'),(23,4,0,'08:00:00','17:00:00'),(24,4,1,'08:00:00','17:00:00'),(25,4,2,'08:00:00','17:00:00'),(26,4,3,'08:00:00','17:00:00'),(27,4,4,'08:00:00','17:00:00'),(28,4,5,'08:00:00','17:00:00'),(29,4,6,'08:00:00','17:00:00');
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stalls`
--

LOCK TABLES `stalls` WRITE;
/*!40000 ALTER TABLE `stalls` DISABLE KEYS */;
INSERT INTO `stalls` VALUES (1,2,'Starbucks','Green Court','Enjoy your handcrafted coffee, refreshing beverages, delicious pastries, and savory bites.','coffee pastries drinks','drinks food',1,'../assets/stalls/starbucks.png','../assets/stalls/starbucks-banner.png'),(3,NULL,'Dairy Queen','Agno','Treat yourself to creamy soft-serve ice cream, irresistible Blizzard treats, and delightful frozen desserts.','icecream blizzard freeze','dessert',0,'../assets/stalls/dq.png',NULL),(4,NULL,'The Famous Belgian Waffles','Bloemen','Indulge in freshly baked Belgian-style waffles filled with delicious sweet or savory flavors.','waffles hot snacks','food dessert',0,'../assets/stalls/belgian.png',NULL);
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
  `proposed_hours` text DEFAULT NULL,
  `proposed_description` text DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store_requests`
--

LOCK TABLES `store_requests` WRITE;
/*!40000 ALTER TABLE `store_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `store_requests` ENABLE KEYS */;
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
  `acc_status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email_add`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'zoe_magaling@dlsu.edu.ph',NULL,'Zoe','Magaling',NULL,'https://lh3.googleusercontent.com/a/ACg8ocJbfEOB7un7lRtQvN8q7EN1aOWJevwiDVsgPYah3ue_1jmEhDGi=s96-c','faculty','active','2026-11-10 16:20:01','2026-11-12 11:06:47'),(2,'chelsea_dimaunahan@dlsu.edu.ph',NULL,'Chelsea','Jei Dimaunahan',NULL,NULL,'student','active','2026-11-11 01:29:52','2026-11-12 13:04:02'),(3,'raphael_gamboa@dlsu.edu.ph',NULL,'Raphael','Gamboa',NULL,'https://lh3.googleusercontent.com/a/ACg8ocJG8eZaZ8P1pUCS0rI5r41w6js7N1yt6pJxr-9xB-7KZd8IVQ=s96-c','vendor','active','2026-11-11 04:28:45','2026-11-12 14:59:26'),(4,'admin@dlsu.edu.ph',NULL,'System','Admin',NULL,NULL,'admin','active','2026-11-11 10:19:51','2026-11-11 10:19:51'),(5,'rinoa_mariel_sadey@dlsu.edu.ph',NULL,'Rinoa Mariel','Sadey',NULL,'https://lh3.googleusercontent.com/a/ACg8ocIe9HHOTZybD0v3-RuLbOji12Lk9b5NzhnxzfEMyOjNrf_gL5A=s96-c',NULL,'active','2026-11-11 15:20:23','2026-11-12 08:55:54');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'greenDash'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-12 16:10:42
