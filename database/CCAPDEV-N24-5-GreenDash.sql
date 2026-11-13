-- CCAPDEV-N24-5-GreenDash: Phase 2 schema and sample data
-- Prepared for the project demonstration on 2026-11-14.
-- Intended for an empty database. Existing tables are not dropped or overwritten.
-- Foreign-key checks stay enabled; parent tables and rows precede their dependants.
-- Original account records and order headers are retained. New sample accounts use reserved .invalid addresses.
-- Historical order snapshots may differ from current catalogue names, prices, or availability.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

-- Table: users
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: stalls
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: categories
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stall_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stall_id` (`stall_id`,`name`),
  UNIQUE KEY `id` (`id`,`stall_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: products
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: product_option_groups
CREATE TABLE `product_option_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`),
  KEY `product_option_groups_product` (`product_id`),
  CONSTRAINT `product_option_groups_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: product_option_choices
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: orders
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: order_items
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: order_item_options
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: reviews
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

-- Table: stall_operating_hours
CREATE TABLE `stall_operating_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stall_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '0=Sunday, 1=Monday ... 6=Saturday',
  `opens_at` time NOT NULL,
  `closes_at` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `stall_id` (`stall_id`,`day_of_week`),
  CONSTRAINT `stall_operating_hours_ibfk_1` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table: store_requests
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

-- Sample data (all tables contain at least five distinct records).
START TRANSACTION;

-- users: 9 rows
INSERT INTO `users` (`user_id`, `email_add`, `password_hash`, `first_name`, `last_name`, `contact_num`, `profile_image`, `user_role`, `acc_status`, `created_at`, `updated_at`) VALUES
  (1, 'zoe_magaling@dlsu.edu.ph', NULL, 'Zoe', 'Magaling', NULL, 'https://lh3.googleusercontent.com/a/ACg8ocJbfEOB7un7lRtQvN8q7EN1aOWJevwiDVsgPYah3ue_1jmEhDGi=s96-c', 'faculty', 'active', '2026-11-10 16:20:01', '2026-11-12 11:06:47'),
  (2, 'chelsea_dimaunahan@dlsu.edu.ph', NULL, 'Chelsea', 'Jei Dimaunahan', NULL, NULL, 'student', 'active', '2026-11-11 01:29:52', '2026-11-12 13:04:02'),
  (3, 'raphael_gamboa@dlsu.edu.ph', NULL, 'Raphael', 'Gamboa', NULL, 'https://lh3.googleusercontent.com/a/ACg8ocJG8eZaZ8P1pUCS0rI5r41w6js7N1yt6pJxr-9xB-7KZd8IVQ=s96-c', 'vendor', 'active', '2026-11-11 04:28:45', '2026-11-12 14:59:26'),
  (4, 'admin@dlsu.edu.ph', NULL, 'System', 'Admin', NULL, NULL, 'admin', 'active', '2026-11-11 10:19:51', '2026-11-11 10:19:51'),
  (5, 'rinoa_mariel_sadey@dlsu.edu.ph', NULL, 'Rinoa Mariel', 'Sadey', NULL, 'https://lh3.googleusercontent.com/a/ACg8ocIe9HHOTZybD0v3-RuLbOji12Lk9b5NzhnxzfEMyOjNrf_gL5A=s96-c', NULL, 'active', '2026-11-11 15:20:23', '2026-11-12 08:55:54'),
  (6, 'sample.staff@example.invalid', NULL, 'Alex', 'Rivera', NULL, NULL, 'staff', 'active', '2026-11-01 08:00:00', '2026-11-13 08:00:00'),
  (7, 'sample.vendor@example.invalid', NULL, 'Mika', 'Santos', NULL, NULL, 'vendor', 'active', '2026-11-01 08:00:00', '2026-11-13 08:00:00'),
  (8, 'sample.student@example.invalid', NULL, 'Sam', 'Cruz', NULL, NULL, 'student', 'active', '2026-11-01 08:00:00', '2026-11-13 08:00:00'),
  (9, 'sample.inactive@example.invalid', NULL, 'Jamie', 'Torres', NULL, NULL, 'faculty', 'inactive', '2026-11-01 08:00:00', '2026-11-13 08:00:00');

-- stalls: 5 rows
INSERT INTO `stalls` (`id`, `vendor_id`, `name`, `location`, `description`, `tags`, `stall_type`, `is_open`, `image_path`, `banner_path`) VALUES
  (1, 3, 'Starbucks', 'Green Court', 'Enjoy your handcrafted coffee, refreshing beverages, delicious pastries, and savory bites.', 'coffee pastries drinks', 'drinks food', 1, '../assets/stalls/starbucks.png', '../assets/stalls/starbucks-banner.png'),
  (3, 3, 'Dairy Queen', 'Agno', 'Treat yourself to creamy soft-serve ice cream, irresistible Blizzard treats, and delightful frozen desserts.', 'icecream blizzard freeze', 'dessert', 0, '../assets/stalls/dq.png', NULL),
  (4, 7, 'The Famous Belgian Waffles', 'Bloemen', 'Indulge in freshly baked Belgian-style waffles filled with delicious sweet or savory flavors.', 'waffles hot snacks', 'food dessert', 0, '../assets/stalls/belgian.png', NULL),
  (5, 3, 'Potato Corner', 'Bloemen', 'Freshly cooked fries with a choice of flavors and portions.', 'fries snacks cheese', 'food', 1, '../assets/stalls/potcor.png', NULL),
  (6, 7, 'Campus Rice Bowls', 'Gokongwei Hall', 'Rice meals and vegetable sides prepared for campus pickup.', 'rice chicken lunch', 'food', 1, '../assets/main/defaultAvatar.png', NULL);

-- categories: 10 rows
INSERT INTO `categories` (`id`, `stall_id`, `name`) VALUES
  (1, 1, 'Popular'),
  (2, 1, 'Featured Drinks'),
  (3, 1, 'Featured Food'),
  (4, 1, 'Refreshers'),
  (5, 1, 'Brewed Coffee'),
  (6, 3, 'Frozen Desserts'),
  (7, 4, 'Waffles'),
  (8, 5, 'Fries'),
  (9, 6, 'Rice Bowls'),
  (10, 6, 'Side Dishes');

-- products: 10 rows
INSERT INTO `products` (`id`, `stall_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_available`, `banner_path`) VALUES
  (2, 1, 1, 'Java Chip Frappuccino', 'Java Chip Frappuccino', 210.00, '../assets/starbucksMenu/javachip.png', 1, NULL),
  (3, 1, 2, 'Caramel Macchiato', 'Caramel Macchiato', 210.00, '../assets/starbucksMenu/caramel.png', 1, NULL),
  (4, 1, 5, 'Caffe Americano', 'Espresso with hot water, available hot or iced.', 150.00, 'assets/starbucksMenu/americano.png', 1, NULL),
  (5, 1, 2, 'Pure Matcha Latte', 'Matcha with the selected milk.', 190.00, 'assets/starbucksMenu/matchalatte.png', 1, NULL),
  (6, 1, 3, 'Butter Croissant', 'A flaky pastry served plain.', 95.00, NULL, 1, NULL),
  (7, 3, 6, 'Chocolate Sundae', 'Soft-serve ice cream with chocolate topping.', 85.00, NULL, 1, NULL),
  (8, 4, 7, 'Classic Belgian Waffle', 'A freshly prepared waffle with a sweet filling.', 90.00, NULL, 1, NULL),
  (9, 5, 8, 'Cheese Fries', 'Crispy fries with cheese seasoning.', 75.00, NULL, 1, NULL),
  (10, 6, 9, 'Chicken Rice Bowl', 'Chicken and steamed rice with a choice of sauce.', 120.00, NULL, 1, NULL),
  (11, 6, 10, 'Vegetable Spring Rolls', 'Vegetable rolls with dipping sauce; currently unavailable.', 55.00, NULL, 0, NULL);

-- product_option_groups: 7 rows
INSERT INTO `product_option_groups` (`group_id`, `product_id`, `group_name`, `is_required`, `display_order`) VALUES
  (1, 2, 'Size', 1, 0),
  (2, 2, 'Choice of Milk', 1, 1),
  (3, 3, 'Size', 1, 0),
  (4, 4, 'Temperature', 1, 0),
  (5, 5, 'Choice of Milk', 1, 0),
  (6, 9, 'Portion', 1, 0),
  (7, 10, 'Sauce', 0, 0);

-- product_option_choices: 20 rows
INSERT INTO `product_option_choices` (`option_id`, `group_id`, `option_name`, `extra_price`, `is_available`, `display_order`) VALUES
  (1, 1, 'Tall', 10.00, 1, 0),
  (2, 1, 'Grande', 20.00, 1, 1),
  (3, 1, 'Venti', 45.00, 1, 2),
  (4, 2, 'Whole', 0.00, 1, 0),
  (5, 2, 'Non-fat', 0.00, 1, 1),
  (6, 2, 'Sub Oatmilk', 50.00, 1, 2),
  (7, 3, 'Tall', 10.00, 1, 0),
  (8, 3, 'Grande', 20.00, 1, 1),
  (9, 3, 'Venti', 45.00, 1, 2),
  (10, 4, 'Hot', 0.00, 1, 0),
  (11, 4, 'Iced', 0.00, 1, 1),
  (12, 5, 'Whole', 0.00, 1, 0),
  (13, 5, 'Non-fat', 0.00, 1, 1),
  (14, 5, 'Oatmilk', 50.00, 1, 2),
  (15, 6, 'Regular', 0.00, 1, 0),
  (16, 6, 'Large', 25.00, 1, 1),
  (17, 6, 'Mega', 50.00, 1, 2),
  (18, 7, 'Classic Soy', 0.00, 1, 0),
  (19, 7, 'Spicy', 0.00, 0, 1),
  (20, 7, 'Extra Garlic', 10.00, 1, 2);

-- orders: 13 rows
INSERT INTO `orders` (`order_id`, `order_number`, `user_id`, `stall_id`, `stall_name`, `stall_location`, `status`, `payment_method`, `payment_status`, `payment_reference`, `pickup_type`, `pickup_at`, `total_amount`, `created_at`) VALUES
  (1, 'GD77310EDACB484B59', 5, 1, 'Starbucks', 'Green Court', 'cancelled', 'cash', 'not_charged', NULL, 'now', NULL, 230.00, '2026-11-12 08:30:58'),
  (2, 'GDC1E5B60C927C5EF9', 2, 1, 'Starbucks', 'Green Court', 'cancelled', 'cash', 'not_charged', NULL, 'now', NULL, 220.00, '2026-11-12 09:47:30'),
  (3, 'GD58866C45BD3DE637', 1, 1, 'Starbucks', 'Green Court', 'pending', 'cash', 'pending', NULL, 'now', NULL, 220.00, '2026-11-12 12:27:49'),
  (4, 'GD95CE1B5D37A26945', 1, 1, 'Starbucks', 'Green Court', 'pending', 'cash', 'pending', NULL, 'now', NULL, 220.00, '2026-11-12 13:56:36'),
  (5, 'TEST001', 1, 1, 'Starbucks', 'Green Court', 'completed', 'cash', 'paid', NULL, 'now', '2026-11-12 14:07:56', 250.00, '2026-11-12 14:07:56'),
  (6, 'GD705AEBCFDBDFCEE3', 1, 1, 'Starbucks', 'Green Court', 'pending', 'cash', 'pending', NULL, 'scheduled', '2026-11-12 22:15:00', 420.00, '2026-11-12 14:09:22'),
  (7, 'GDSEED202611000007', 1, 1, 'Starbucks', 'Green Court', 'completed', 'cash', 'paid', NULL, 'now', NULL, 245.00, '2026-11-11 10:15:00'),
  (8, 'GDSEED202611000008', 2, 5, 'Potato Corner', 'Bloemen', 'completed', 'online', 'paid', 'DEMO-ONLINE-000008', 'scheduled', '2026-11-11 12:30:00', 200.00, '2026-11-11 12:10:00'),
  (9, 'GDSEED202611000009', 6, 1, 'Starbucks', 'Green Court', 'completed', 'online', 'paid', 'DEMO-ONLINE-000009', 'now', NULL, 240.00, '2026-11-12 09:45:00'),
  (10, 'GDSEED202611000010', 8, 6, 'Campus Rice Bowls', 'Gokongwei Hall', 'completed', 'cash', 'paid', NULL, 'now', NULL, 185.00, '2026-11-12 13:15:00'),
  (11, 'GDSEED202611000011', 1, 1, 'Starbucks', 'Green Court', 'preparing', 'cash', 'pending', NULL, 'now', NULL, 230.00, '2026-11-13 09:10:00'),
  (12, 'GDSEED202611000012', 2, 5, 'Potato Corner', 'Bloemen', 'ready', 'online', 'pending', 'DEMO-ONLINE-000012', 'now', NULL, 125.00, '2026-11-13 09:30:00'),
  (13, 'GDSEED202611000013', 6, 6, 'Campus Rice Bowls', 'Gokongwei Hall', 'pending', 'cash', 'pending', NULL, 'scheduled', '2026-11-13 16:00:00', 120.00, '2026-11-13 14:00:00');

-- order_items: 15 rows
INSERT INTO `order_items` (`order_item_id`, `order_id`, `menu_item_id`, `item_name`, `category_name`, `special_instructions`, `quantity`, `base_price`, `unit_price`, `total_price`) VALUES
  (1, 1, NULL, 'Java Chip Frappuccino', NULL, NULL, 1, 210.00, 230.00, 230.00),
  (2, 2, 3, 'Caramel Macchiato', NULL, NULL, 1, 210.00, 220.00, 220.00),
  (3, 3, 2, 'Java Chip Frappuccino', NULL, NULL, 1, 210.00, 220.00, 220.00),
  (4, 4, 2, 'Java Chip Frappuccino', NULL, NULL, 1, 210.00, 220.00, 220.00),
  (5, 6, 3, 'Caramel Macchiato', NULL, NULL, 2, 210.00, 210.00, 420.00),
  (6, 5, NULL, 'Coffee and Pastry Combo', 'Featured Food', NULL, 1, 250.00, 250.00, 250.00),
  (7, 7, 4, 'Caffe Americano', 'Brewed Coffee', 'No added sugar.', 1, 150.00, 150.00, 150.00),
  (8, 7, 6, 'Butter Croissant', 'Featured Food', NULL, 1, 95.00, 95.00, 95.00),
  (9, 8, 9, 'Cheese Fries', 'Fries', 'Extra napkins, please.', 2, 75.00, 100.00, 200.00),
  (10, 9, 5, 'Pure Matcha Latte', 'Featured Drinks', 'Less ice.', 1, 190.00, 240.00, 240.00),
  (11, 10, 10, 'Chicken Rice Bowl', 'Rice Bowls', NULL, 1, 120.00, 130.00, 130.00),
  (12, 10, 11, 'Vegetable Spring Rolls', 'Side Dishes', NULL, 1, 55.00, 55.00, 55.00),
  (13, 11, 3, 'Caramel Macchiato', 'Featured Drinks', NULL, 1, 210.00, 230.00, 230.00),
  (14, 12, 9, 'Cheese Fries', 'Fries', NULL, 1, 75.00, 125.00, 125.00),
  (15, 13, 10, 'Chicken Rice Bowl', 'Rice Bowls', 'Keep the sauce separate.', 1, 120.00, 120.00, 120.00);

-- order_item_options: 15 rows
INSERT INTO `order_item_options` (`order_item_option_id`, `order_item_id`, `option_id`, `option_group_name`, `option_name`, `quantity`, `extra_price`) VALUES
  (1, 1, NULL, 'Choice of milk', 'Non-fat', 1, 0.00),
  (2, 1, NULL, 'Choice of size', 'Grande', 1, 20.00),
  (3, 2, NULL, 'Choice of milk', 'Non-fat', 1, 0.00),
  (4, 2, NULL, 'Choice of size', 'Tall', 1, 10.00),
  (5, 3, 1, 'Size', 'Tall', 1, 10.00),
  (6, 3, 4, 'Choice of Milk', 'Whole', 1, 0.00),
  (7, 4, 1, 'Size', 'Tall', 1, 10.00),
  (8, 4, 4, 'Choice of Milk', 'Whole', 1, 0.00),
  (9, 7, 10, 'Temperature', 'Hot', 1, 0.00),
  (10, 9, 16, 'Portion', 'Large', 1, 25.00),
  (11, 10, 14, 'Choice of Milk', 'Oatmilk', 1, 50.00),
  (12, 11, 20, 'Sauce', 'Extra Garlic', 1, 10.00),
  (13, 13, 8, 'Size', 'Grande', 1, 20.00),
  (14, 14, 17, 'Portion', 'Mega', 1, 50.00),
  (15, 15, 18, 'Sauce', 'Classic Soy', 1, 0.00);

-- reviews: 5 rows
INSERT INTO `reviews` (`review_id`, `order_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
  (1, 5, 5, 'The cashier checked my order''s pickup number quickly.', '2026-11-12 15:30:00', '2026-11-12 15:30:00'),
  (2, 7, 4, 'The coffee was hot and the pastry was fresh.', '2026-11-11 11:00:00', '2026-11-11 11:00:00'),
  (3, 8, 3, 'The fries tasted good, but pickup took a little longer.', '2026-11-11 13:10:00', '2026-11-12 08:05:00'),
  (4, 9, 5, NULL, '2026-11-12 10:30:00', '2026-11-12 10:30:00'),
  (5, 10, 4, 'Generous serving and clear pickup instructions.', '2026-11-12 14:00:00', '2026-11-12 14:00:00');

-- stall_operating_hours: 35 rows
INSERT INTO `stall_operating_hours` (`id`, `stall_id`, `day_of_week`, `opens_at`, `closes_at`) VALUES
  (9, 1, 0, '06:00:00', '22:30:00'),
  (10, 1, 1, '06:00:00', '22:30:00'),
  (11, 1, 2, '06:00:00', '22:30:00'),
  (12, 1, 3, '06:00:00', '22:30:00'),
  (13, 1, 4, '06:00:00', '22:30:00'),
  (14, 1, 5, '06:00:00', '22:30:00'),
  (15, 1, 6, '06:00:00', '22:30:00'),
  (16, 3, 0, '08:00:00', '17:00:00'),
  (17, 3, 1, '08:00:00', '17:00:00'),
  (18, 3, 2, '08:00:00', '17:00:00'),
  (19, 3, 3, '08:00:00', '17:00:00'),
  (20, 3, 4, '08:00:00', '17:00:00'),
  (21, 3, 5, '08:00:00', '17:00:00'),
  (22, 3, 6, '08:00:00', '17:00:00'),
  (23, 4, 0, '08:00:00', '17:00:00'),
  (24, 4, 1, '08:00:00', '17:00:00'),
  (25, 4, 2, '08:00:00', '17:00:00'),
  (26, 4, 3, '08:00:00', '17:00:00'),
  (27, 4, 4, '08:00:00', '17:00:00'),
  (28, 4, 5, '08:00:00', '17:00:00'),
  (29, 4, 6, '08:00:00', '17:00:00'),
  (30, 5, 0, '09:00:00', '20:00:00'),
  (31, 5, 1, '09:00:00', '20:00:00'),
  (32, 5, 2, '09:00:00', '20:00:00'),
  (33, 5, 3, '09:00:00', '20:00:00'),
  (34, 5, 4, '09:00:00', '20:00:00'),
  (35, 5, 5, '09:00:00', '20:00:00'),
  (36, 5, 6, '09:00:00', '20:00:00'),
  (37, 6, 0, '10:00:00', '19:00:00'),
  (38, 6, 1, '10:00:00', '19:00:00'),
  (39, 6, 2, '10:00:00', '19:00:00'),
  (40, 6, 3, '10:00:00', '19:00:00'),
  (41, 6, 4, '10:00:00', '19:00:00'),
  (42, 6, 5, '10:00:00', '19:00:00'),
  (43, 6, 6, '10:00:00', '19:00:00');

-- store_requests: 5 rows
INSERT INTO `store_requests` (`id`, `vendor_id`, `type`, `status`, `proposed_name`, `proposed_location`, `proposed_hours`, `proposed_description`, `stall_id`, `resolved_by`, `created_at`, `resolved_at`) VALUES
  (1, 3, 'add', 'pending', 'Campus Fruit Cups', 'Agno', 'Mon-Fri 09:00-17:00', '{"store_type": "Snacks", "note": "Fresh fruit cup counter proposal."}', NULL, NULL, '2026-11-13 10:00:00', NULL),
  (2, 7, 'remove', 'pending', NULL, NULL, NULL, 'The vendor requested removal of this closed waffle stall.', 4, NULL, '2026-11-13 10:30:00', NULL),
  (3, 3, 'add', 'denied', 'Night Owl Snacks', 'Agno', 'Fri-Sat 22:00-23:30', '{"store_type": "Snacks", "note": "Location was unavailable for the proposed schedule."}', NULL, 4, '2026-11-12 10:00:00', '2026-11-12 15:00:00'),
  (4, 3, 'add', 'approved', 'Potato Corner', 'Bloemen', 'Mon-Sun 09:00-20:00', '{"store_type": "Snacks"}', 5, 4, '2026-11-11 10:30:00', '2026-11-11 11:30:00'),
  (5, 7, 'add', 'approved', 'Campus Rice Bowls', 'Gokongwei Hall', 'Mon-Sun 10:00-19:00', '{"store_type": "Restaurant"}', 6, 4, '2026-11-11 11:00:00', '2026-11-11 11:45:00');

COMMIT;
