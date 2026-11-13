-- GreenDash: add demonstration samples to an EXISTING MariaDB database.
-- No existing rows, tables, account roles, product settings, or IDs are replaced.
-- Select the intended database before sourcing this file. Requires MariaDB 10.1+ and InnoDB.
-- New parent IDs are looked up; the database allocates new row IDs automatically.
-- Re-running skips matching sample records. An SQL error rolls back this batch.
-- Existing reviews and edited sample records are preserved.
-- Full fresh-database export: CCAPDEV-N24-5-GreenDash.sql (do not import it over existing tables).
SET NAMES utf8mb4;
DELIMITER //
BEGIN NOT ATOMIC
    DECLARE seed_vendor_id INT DEFAULT NULL;
    DECLARE seed_customer_id INT DEFAULT NULL;
    DECLARE seed_admin_id INT DEFAULT NULL;
    DECLARE seed_fries_stall_id INT DEFAULT NULL;
    DECLARE seed_fries_category_id INT DEFAULT NULL;
    DECLARE seed_bowls_stall_id INT DEFAULT NULL;
    DECLARE seed_bowls_category_id INT DEFAULT NULL;
    DECLARE seed_fries_product_id INT DEFAULT NULL;
    DECLARE seed_chicken_product_id INT DEFAULT NULL;
    DECLARE seed_vegetable_product_id INT DEFAULT NULL;
    DECLARE seed_fries_group_id INT DEFAULT NULL;
    DECLARE seed_fries_regular_id INT DEFAULT NULL;
    DECLARE seed_fries_large_id INT DEFAULT NULL;
    DECLARE seed_fries_mega_id INT DEFAULT NULL;
    DECLARE seed_chicken_group_id INT DEFAULT NULL;
    DECLARE seed_chicken_classic_id INT DEFAULT NULL;
    DECLARE seed_chicken_garlic_id INT DEFAULT NULL;
    DECLARE seed_chicken_spicy_id INT DEFAULT NULL;
    DECLARE seed_vegetable_group_id INT DEFAULT NULL;
    DECLARE seed_vegetable_tofu_id INT DEFAULT NULL;
    DECLARE seed_vegetable_egg_id INT DEFAULT NULL;
    DECLARE seed_base_price DECIMAL(10,2) DEFAULT NULL;
    DECLARE seed_extra_price DECIMAL(10,2) DEFAULT NULL;
    DECLARE seed_unit_price DECIMAL(10,2) DEFAULT NULL;
    DECLARE seed_order_id INT DEFAULT NULL;
    DECLARE seed_order_is_new INT DEFAULT NULL;
    DECLARE seed_item_id INT DEFAULT NULL;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    IF DATABASE() IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Select the intended database before running this script.'; END IF;
    IF (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('users', 'stalls', 'categories', 'products', 'product_option_groups', 'product_option_choices', 'orders', 'order_items', 'order_item_options', 'reviews', 'stall_operating_hours', 'store_requests') AND ENGINE = 'InnoDB') <> 12 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'All twelve existing project tables must use InnoDB; no data was changed.';
    END IF;
    START TRANSACTION;
    SET seed_vendor_id = (SELECT u.user_id FROM users u WHERE u.user_role = 'vendor' AND u.acc_status = 'active' ORDER BY EXISTS (SELECT 1 FROM stalls s WHERE s.vendor_id = u.user_id) DESC, u.user_id LIMIT 1);
    SET seed_customer_id = (SELECT MIN(user_id) FROM users WHERE user_role IN ('student','faculty','staff') AND acc_status = 'active');
    SET seed_admin_id = (SELECT MIN(user_id) FROM users WHERE user_role = 'admin' AND acc_status = 'active');
    IF NOT (seed_vendor_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'An active vendor, customer, and admin are required. Existing data was not changed.'; END IF;
    IF NOT (seed_customer_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'An active vendor, customer, and admin are required. Existing data was not changed.'; END IF;
    IF NOT (seed_admin_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'An active vendor, customer, and admin are required. Existing data was not changed.'; END IF;
    INSERT INTO `stalls` (`vendor_id`, `name`, `location`, `description`, `tags`, `stall_type`, `is_open`, `image_path`, `banner_path`)
    SELECT seed_vendor_id, 'Demo Potato Corner', 'Bloemen', 'Sample fries counter for the Phase 2 demonstration.', 'fries cheese snacks', 'food', 1, '../assets/stalls/potcor.png', NULL FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stalls` WHERE vendor_id = seed_vendor_id AND name = 'Demo Potato Corner' AND location = 'Bloemen');
    SET seed_fries_stall_id = (SELECT MIN(`id`) FROM `stalls` WHERE vendor_id = seed_vendor_id AND name = 'Demo Potato Corner' AND location = 'Bloemen');
    IF NOT (seed_fries_stall_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_fries_stall_id, 0, '09:00:00', '20:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_fries_stall_id AND day_of_week = 0);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_fries_stall_id, 1, '09:00:00', '20:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_fries_stall_id AND day_of_week = 1);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_fries_stall_id, 2, '09:00:00', '20:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_fries_stall_id AND day_of_week = 2);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_fries_stall_id, 3, '09:00:00', '20:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_fries_stall_id AND day_of_week = 3);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_fries_stall_id, 4, '09:00:00', '20:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_fries_stall_id AND day_of_week = 4);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_fries_stall_id, 5, '09:00:00', '20:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_fries_stall_id AND day_of_week = 5);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_fries_stall_id, 6, '09:00:00', '20:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_fries_stall_id AND day_of_week = 6);
    INSERT INTO `categories` (`stall_id`, `name`)
    SELECT seed_fries_stall_id, 'Fries' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE stall_id = seed_fries_stall_id AND name = 'Fries');
    SET seed_fries_category_id = (SELECT MIN(`id`) FROM `categories` WHERE stall_id = seed_fries_stall_id AND name = 'Fries');
    IF NOT (seed_fries_category_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `stalls` (`vendor_id`, `name`, `location`, `description`, `tags`, `stall_type`, `is_open`, `image_path`, `banner_path`)
    SELECT seed_vendor_id, 'Demo Rice Bowls', 'Gokongwei Hall', 'Sample rice meals for the Phase 2 demonstration.', 'rice chicken lunch', 'food', 1, '../assets/main/defaultAvatar.png', NULL FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stalls` WHERE vendor_id = seed_vendor_id AND name = 'Demo Rice Bowls' AND location = 'Gokongwei Hall');
    SET seed_bowls_stall_id = (SELECT MIN(`id`) FROM `stalls` WHERE vendor_id = seed_vendor_id AND name = 'Demo Rice Bowls' AND location = 'Gokongwei Hall');
    IF NOT (seed_bowls_stall_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_bowls_stall_id, 0, '10:00:00', '19:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_bowls_stall_id AND day_of_week = 0);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_bowls_stall_id, 1, '10:00:00', '19:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_bowls_stall_id AND day_of_week = 1);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_bowls_stall_id, 2, '10:00:00', '19:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_bowls_stall_id AND day_of_week = 2);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_bowls_stall_id, 3, '10:00:00', '19:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_bowls_stall_id AND day_of_week = 3);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_bowls_stall_id, 4, '10:00:00', '19:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_bowls_stall_id AND day_of_week = 4);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_bowls_stall_id, 5, '10:00:00', '19:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_bowls_stall_id AND day_of_week = 5);
    INSERT INTO `stall_operating_hours` (`stall_id`, `day_of_week`, `opens_at`, `closes_at`)
    SELECT seed_bowls_stall_id, 6, '10:00:00', '19:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `stall_operating_hours` WHERE stall_id = seed_bowls_stall_id AND day_of_week = 6);
    INSERT INTO `categories` (`stall_id`, `name`)
    SELECT seed_bowls_stall_id, 'Rice Bowls' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `categories` WHERE stall_id = seed_bowls_stall_id AND name = 'Rice Bowls');
    SET seed_bowls_category_id = (SELECT MIN(`id`) FROM `categories` WHERE stall_id = seed_bowls_stall_id AND name = 'Rice Bowls');
    IF NOT (seed_bowls_category_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `products` (`stall_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_available`, `banner_path`)
    SELECT seed_fries_stall_id, seed_fries_category_id, 'Cheese Fries', 'Fries with cheese seasoning and a selectable portion.', '75.00', NULL, 1, NULL FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE stall_id = seed_fries_stall_id AND name = 'Cheese Fries');
    SET seed_fries_product_id = (SELECT MIN(`id`) FROM `products` WHERE stall_id = seed_fries_stall_id AND name = 'Cheese Fries');
    IF NOT (seed_fries_product_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `products` (`stall_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_available`, `banner_path`)
    SELECT seed_bowls_stall_id, seed_bowls_category_id, 'Chicken Rice Bowl', 'Chicken and rice with a choice of sauce.', '120.00', NULL, 1, NULL FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE stall_id = seed_bowls_stall_id AND name = 'Chicken Rice Bowl');
    SET seed_chicken_product_id = (SELECT MIN(`id`) FROM `products` WHERE stall_id = seed_bowls_stall_id AND name = 'Chicken Rice Bowl');
    IF NOT (seed_chicken_product_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `products` (`stall_id`, `category_id`, `name`, `description`, `price`, `image_path`, `is_available`, `banner_path`)
    SELECT seed_bowls_stall_id, seed_bowls_category_id, 'Vegetable Rice Bowl', 'Vegetables and rice with an optional add-on.', '105.00', NULL, 1, NULL FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE stall_id = seed_bowls_stall_id AND name = 'Vegetable Rice Bowl');
    SET seed_vegetable_product_id = (SELECT MIN(`id`) FROM `products` WHERE stall_id = seed_bowls_stall_id AND name = 'Vegetable Rice Bowl');
    IF NOT (seed_vegetable_product_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_groups` (`product_id`, `group_name`, `is_required`, `display_order`)
    SELECT seed_fries_product_id, 'Portion', 1, 0 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_groups` WHERE product_id = seed_fries_product_id AND group_name = 'Portion');
    SET seed_fries_group_id = (SELECT MIN(`group_id`) FROM `product_option_groups` WHERE product_id = seed_fries_product_id AND group_name = 'Portion');
    IF NOT (seed_fries_group_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_choices` (`group_id`, `option_name`, `extra_price`, `is_available`, `display_order`)
    SELECT seed_fries_group_id, 'Regular', '0.00', 1, 0 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_choices` WHERE group_id = seed_fries_group_id AND option_name = 'Regular');
    SET seed_fries_regular_id = (SELECT MIN(`option_id`) FROM `product_option_choices` WHERE group_id = seed_fries_group_id AND option_name = 'Regular');
    IF NOT (seed_fries_regular_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_choices` (`group_id`, `option_name`, `extra_price`, `is_available`, `display_order`)
    SELECT seed_fries_group_id, 'Large', '25.00', 1, 1 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_choices` WHERE group_id = seed_fries_group_id AND option_name = 'Large');
    SET seed_fries_large_id = (SELECT MIN(`option_id`) FROM `product_option_choices` WHERE group_id = seed_fries_group_id AND option_name = 'Large');
    IF NOT (seed_fries_large_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_choices` (`group_id`, `option_name`, `extra_price`, `is_available`, `display_order`)
    SELECT seed_fries_group_id, 'Mega', '50.00', 1, 2 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_choices` WHERE group_id = seed_fries_group_id AND option_name = 'Mega');
    SET seed_fries_mega_id = (SELECT MIN(`option_id`) FROM `product_option_choices` WHERE group_id = seed_fries_group_id AND option_name = 'Mega');
    IF NOT (seed_fries_mega_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_groups` (`product_id`, `group_name`, `is_required`, `display_order`)
    SELECT seed_chicken_product_id, 'Sauce', 0, 0 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_groups` WHERE product_id = seed_chicken_product_id AND group_name = 'Sauce');
    SET seed_chicken_group_id = (SELECT MIN(`group_id`) FROM `product_option_groups` WHERE product_id = seed_chicken_product_id AND group_name = 'Sauce');
    IF NOT (seed_chicken_group_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_choices` (`group_id`, `option_name`, `extra_price`, `is_available`, `display_order`)
    SELECT seed_chicken_group_id, 'Classic Soy', '0.00', 1, 0 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_choices` WHERE group_id = seed_chicken_group_id AND option_name = 'Classic Soy');
    SET seed_chicken_classic_id = (SELECT MIN(`option_id`) FROM `product_option_choices` WHERE group_id = seed_chicken_group_id AND option_name = 'Classic Soy');
    IF NOT (seed_chicken_classic_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_choices` (`group_id`, `option_name`, `extra_price`, `is_available`, `display_order`)
    SELECT seed_chicken_group_id, 'Extra Garlic', '10.00', 1, 1 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_choices` WHERE group_id = seed_chicken_group_id AND option_name = 'Extra Garlic');
    SET seed_chicken_garlic_id = (SELECT MIN(`option_id`) FROM `product_option_choices` WHERE group_id = seed_chicken_group_id AND option_name = 'Extra Garlic');
    IF NOT (seed_chicken_garlic_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_choices` (`group_id`, `option_name`, `extra_price`, `is_available`, `display_order`)
    SELECT seed_chicken_group_id, 'Spicy', '0.00', 0, 2 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_choices` WHERE group_id = seed_chicken_group_id AND option_name = 'Spicy');
    SET seed_chicken_spicy_id = (SELECT MIN(`option_id`) FROM `product_option_choices` WHERE group_id = seed_chicken_group_id AND option_name = 'Spicy');
    IF NOT (seed_chicken_spicy_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_groups` (`product_id`, `group_name`, `is_required`, `display_order`)
    SELECT seed_vegetable_product_id, 'Add-on', 0, 0 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_groups` WHERE product_id = seed_vegetable_product_id AND group_name = 'Add-on');
    SET seed_vegetable_group_id = (SELECT MIN(`group_id`) FROM `product_option_groups` WHERE product_id = seed_vegetable_product_id AND group_name = 'Add-on');
    IF NOT (seed_vegetable_group_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_choices` (`group_id`, `option_name`, `extra_price`, `is_available`, `display_order`)
    SELECT seed_vegetable_group_id, 'Tofu', '0.00', 1, 0 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_choices` WHERE group_id = seed_vegetable_group_id AND option_name = 'Tofu');
    SET seed_vegetable_tofu_id = (SELECT MIN(`option_id`) FROM `product_option_choices` WHERE group_id = seed_vegetable_group_id AND option_name = 'Tofu');
    IF NOT (seed_vegetable_tofu_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    INSERT INTO `product_option_choices` (`group_id`, `option_name`, `extra_price`, `is_available`, `display_order`)
    SELECT seed_vegetable_group_id, 'Egg', '15.00', 1, 1 FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `product_option_choices` WHERE group_id = seed_vegetable_group_id AND option_name = 'Egg');
    SET seed_vegetable_egg_id = (SELECT MIN(`option_id`) FROM `product_option_choices` WHERE group_id = seed_vegetable_group_id AND option_name = 'Egg');
    IF NOT (seed_vegetable_egg_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    SET seed_base_price = (SELECT price FROM products WHERE id = seed_fries_product_id);
    SET seed_extra_price = (SELECT extra_price FROM product_option_choices WHERE option_id = seed_fries_large_id);
    SET seed_unit_price = seed_base_price + seed_extra_price;
    INSERT INTO orders (`order_number`, `user_id`, `stall_id`, `stall_name`, `stall_location`, `status`, `payment_method`, `payment_status`, `payment_reference`, `pickup_type`, `pickup_at`, `total_amount`, `created_at`)
    SELECT 'GDDEMO202611130001', seed_customer_id, seed_fries_stall_id, (SELECT name FROM stalls WHERE id = seed_fries_stall_id), (SELECT location FROM stalls WHERE id = seed_fries_stall_id), 'completed', 'cash', 'paid', NULL, 'now', NULL, seed_unit_price * 1, '2026-11-12 11:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM orders WHERE order_number = 'GDDEMO202611130001');
    SET seed_order_is_new = ROW_COUNT();
    SET seed_order_id = (SELECT MIN(`order_id`) FROM `orders` WHERE order_number = 'GDDEMO202611130001' AND user_id = seed_customer_id AND stall_id = seed_fries_stall_id);
    IF NOT (seed_order_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    IF seed_order_is_new = 1 THEN
        INSERT INTO order_items (order_id, menu_item_id, item_name, category_name, special_instructions, quantity, base_price, unit_price, total_price)
        SELECT seed_order_id, p.id, p.name, c.name, NULL, 1, seed_base_price, seed_unit_price, seed_unit_price * 1
        FROM products p JOIN categories c ON c.id = p.category_id AND c.stall_id = p.stall_id WHERE p.id = seed_fries_product_id;
        SET seed_item_id = LAST_INSERT_ID();
        INSERT INTO order_item_options (order_item_id, option_id, option_group_name, option_name, quantity, extra_price)
        SELECT seed_item_id, c.option_id, g.group_name, c.option_name, 1, c.extra_price
        FROM product_option_choices c JOIN product_option_groups g ON g.group_id = c.group_id WHERE c.option_id = seed_fries_large_id AND g.product_id = seed_fries_product_id;
    END IF;
    INSERT INTO reviews (order_id, rating, `comment`, created_at, updated_at)
    SELECT o.order_id, 5, 'Quick pickup and the fries were still warm.', '2026-11-13 14:00:00', '2026-11-13 14:00:00' FROM orders o
    WHERE o.order_id = seed_order_id AND o.status = 'completed' AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.order_id = o.order_id);
    SET seed_base_price = (SELECT price FROM products WHERE id = seed_chicken_product_id);
    SET seed_extra_price = (SELECT extra_price FROM product_option_choices WHERE option_id = seed_chicken_garlic_id);
    SET seed_unit_price = seed_base_price + seed_extra_price;
    INSERT INTO orders (`order_number`, `user_id`, `stall_id`, `stall_name`, `stall_location`, `status`, `payment_method`, `payment_status`, `payment_reference`, `pickup_type`, `pickup_at`, `total_amount`, `created_at`)
    SELECT 'GDDEMO202611130002', seed_customer_id, seed_bowls_stall_id, (SELECT name FROM stalls WHERE id = seed_bowls_stall_id), (SELECT location FROM stalls WHERE id = seed_bowls_stall_id), 'completed', 'online', 'paid', 'DEMO-PAYMENT-0002', 'now', NULL, seed_unit_price * 2, '2026-11-12 12:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM orders WHERE order_number = 'GDDEMO202611130002');
    SET seed_order_is_new = ROW_COUNT();
    SET seed_order_id = (SELECT MIN(`order_id`) FROM `orders` WHERE order_number = 'GDDEMO202611130002' AND user_id = seed_customer_id AND stall_id = seed_bowls_stall_id);
    IF NOT (seed_order_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    IF seed_order_is_new = 1 THEN
        INSERT INTO order_items (order_id, menu_item_id, item_name, category_name, special_instructions, quantity, base_price, unit_price, total_price)
        SELECT seed_order_id, p.id, p.name, c.name, NULL, 2, seed_base_price, seed_unit_price, seed_unit_price * 2
        FROM products p JOIN categories c ON c.id = p.category_id AND c.stall_id = p.stall_id WHERE p.id = seed_chicken_product_id;
        SET seed_item_id = LAST_INSERT_ID();
        INSERT INTO order_item_options (order_item_id, option_id, option_group_name, option_name, quantity, extra_price)
        SELECT seed_item_id, c.option_id, g.group_name, c.option_name, 1, c.extra_price
        FROM product_option_choices c JOIN product_option_groups g ON g.group_id = c.group_id WHERE c.option_id = seed_chicken_garlic_id AND g.product_id = seed_chicken_product_id;
    END IF;
    INSERT INTO reviews (order_id, rating, `comment`, created_at, updated_at)
    SELECT o.order_id, 4, 'The sauce choice was correct and the serving was generous.', '2026-11-13 14:00:00', '2026-11-13 14:00:00' FROM orders o
    WHERE o.order_id = seed_order_id AND o.status = 'completed' AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.order_id = o.order_id);
    SET seed_base_price = (SELECT price FROM products WHERE id = seed_vegetable_product_id);
    SET seed_extra_price = (SELECT extra_price FROM product_option_choices WHERE option_id = seed_vegetable_egg_id);
    SET seed_unit_price = seed_base_price + seed_extra_price;
    INSERT INTO orders (`order_number`, `user_id`, `stall_id`, `stall_name`, `stall_location`, `status`, `payment_method`, `payment_status`, `payment_reference`, `pickup_type`, `pickup_at`, `total_amount`, `created_at`)
    SELECT 'GDDEMO202611130003', seed_customer_id, seed_bowls_stall_id, (SELECT name FROM stalls WHERE id = seed_bowls_stall_id), (SELECT location FROM stalls WHERE id = seed_bowls_stall_id), 'completed', 'cash', 'paid', NULL, 'scheduled', '2026-11-13 11:30:00', seed_unit_price * 1, '2026-11-13 11:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM orders WHERE order_number = 'GDDEMO202611130003');
    SET seed_order_is_new = ROW_COUNT();
    SET seed_order_id = (SELECT MIN(`order_id`) FROM `orders` WHERE order_number = 'GDDEMO202611130003' AND user_id = seed_customer_id AND stall_id = seed_bowls_stall_id);
    IF NOT (seed_order_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    IF seed_order_is_new = 1 THEN
        INSERT INTO order_items (order_id, menu_item_id, item_name, category_name, special_instructions, quantity, base_price, unit_price, total_price)
        SELECT seed_order_id, p.id, p.name, c.name, NULL, 1, seed_base_price, seed_unit_price, seed_unit_price * 1
        FROM products p JOIN categories c ON c.id = p.category_id AND c.stall_id = p.stall_id WHERE p.id = seed_vegetable_product_id;
        SET seed_item_id = LAST_INSERT_ID();
        INSERT INTO order_item_options (order_item_id, option_id, option_group_name, option_name, quantity, extra_price)
        SELECT seed_item_id, c.option_id, g.group_name, c.option_name, 1, c.extra_price
        FROM product_option_choices c JOIN product_option_groups g ON g.group_id = c.group_id WHERE c.option_id = seed_vegetable_egg_id AND g.product_id = seed_vegetable_product_id;
    END IF;
    INSERT INTO reviews (order_id, rating, `comment`, created_at, updated_at)
    SELECT o.order_id, 5, NULL, '2026-11-13 14:00:00', '2026-11-13 14:00:00' FROM orders o
    WHERE o.order_id = seed_order_id AND o.status = 'completed' AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.order_id = o.order_id);
    SET seed_base_price = (SELECT price FROM products WHERE id = seed_fries_product_id);
    SET seed_extra_price = (SELECT extra_price FROM product_option_choices WHERE option_id = seed_fries_mega_id);
    SET seed_unit_price = seed_base_price + seed_extra_price;
    INSERT INTO orders (`order_number`, `user_id`, `stall_id`, `stall_name`, `stall_location`, `status`, `payment_method`, `payment_status`, `payment_reference`, `pickup_type`, `pickup_at`, `total_amount`, `created_at`)
    SELECT 'GDDEMO202611130004', seed_customer_id, seed_fries_stall_id, (SELECT name FROM stalls WHERE id = seed_fries_stall_id), (SELECT location FROM stalls WHERE id = seed_fries_stall_id), 'completed', 'cash', 'paid', NULL, 'now', NULL, seed_unit_price * 2, '2026-11-13 12:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM orders WHERE order_number = 'GDDEMO202611130004');
    SET seed_order_is_new = ROW_COUNT();
    SET seed_order_id = (SELECT MIN(`order_id`) FROM `orders` WHERE order_number = 'GDDEMO202611130004' AND user_id = seed_customer_id AND stall_id = seed_fries_stall_id);
    IF NOT (seed_order_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    IF seed_order_is_new = 1 THEN
        INSERT INTO order_items (order_id, menu_item_id, item_name, category_name, special_instructions, quantity, base_price, unit_price, total_price)
        SELECT seed_order_id, p.id, p.name, c.name, NULL, 2, seed_base_price, seed_unit_price, seed_unit_price * 2
        FROM products p JOIN categories c ON c.id = p.category_id AND c.stall_id = p.stall_id WHERE p.id = seed_fries_product_id;
        SET seed_item_id = LAST_INSERT_ID();
        INSERT INTO order_item_options (order_item_id, option_id, option_group_name, option_name, quantity, extra_price)
        SELECT seed_item_id, c.option_id, g.group_name, c.option_name, 1, c.extra_price
        FROM product_option_choices c JOIN product_option_groups g ON g.group_id = c.group_id WHERE c.option_id = seed_fries_mega_id AND g.product_id = seed_fries_product_id;
    END IF;
    INSERT INTO reviews (order_id, rating, `comment`, created_at, updated_at)
    SELECT o.order_id, 3, 'The food tasted good, but pickup took longer than expected.', '2026-11-13 14:00:00', '2026-11-13 14:00:00' FROM orders o
    WHERE o.order_id = seed_order_id AND o.status = 'completed' AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.order_id = o.order_id);
    SET seed_base_price = (SELECT price FROM products WHERE id = seed_chicken_product_id);
    SET seed_extra_price = (SELECT extra_price FROM product_option_choices WHERE option_id = seed_chicken_classic_id);
    SET seed_unit_price = seed_base_price + seed_extra_price;
    INSERT INTO orders (`order_number`, `user_id`, `stall_id`, `stall_name`, `stall_location`, `status`, `payment_method`, `payment_status`, `payment_reference`, `pickup_type`, `pickup_at`, `total_amount`, `created_at`)
    SELECT 'GDDEMO202611130005', seed_customer_id, seed_bowls_stall_id, (SELECT name FROM stalls WHERE id = seed_bowls_stall_id), (SELECT location FROM stalls WHERE id = seed_bowls_stall_id), 'completed', 'online', 'paid', 'DEMO-PAYMENT-0005', 'now', NULL, seed_unit_price * 1, '2026-11-13 12:30:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM orders WHERE order_number = 'GDDEMO202611130005');
    SET seed_order_is_new = ROW_COUNT();
    SET seed_order_id = (SELECT MIN(`order_id`) FROM `orders` WHERE order_number = 'GDDEMO202611130005' AND user_id = seed_customer_id AND stall_id = seed_bowls_stall_id);
    IF NOT (seed_order_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    IF seed_order_is_new = 1 THEN
        INSERT INTO order_items (order_id, menu_item_id, item_name, category_name, special_instructions, quantity, base_price, unit_price, total_price)
        SELECT seed_order_id, p.id, p.name, c.name, NULL, 1, seed_base_price, seed_unit_price, seed_unit_price * 1
        FROM products p JOIN categories c ON c.id = p.category_id AND c.stall_id = p.stall_id WHERE p.id = seed_chicken_product_id;
        SET seed_item_id = LAST_INSERT_ID();
        INSERT INTO order_item_options (order_item_id, option_id, option_group_name, option_name, quantity, extra_price)
        SELECT seed_item_id, c.option_id, g.group_name, c.option_name, 1, c.extra_price
        FROM product_option_choices c JOIN product_option_groups g ON g.group_id = c.group_id WHERE c.option_id = seed_chicken_classic_id AND g.product_id = seed_chicken_product_id;
    END IF;
    INSERT INTO reviews (order_id, rating, `comment`, created_at, updated_at)
    SELECT o.order_id, 4, 'The order details and pickup number were checked carefully.', '2026-11-13 14:00:00', '2026-11-13 14:00:00' FROM orders o
    WHERE o.order_id = seed_order_id AND o.status = 'completed' AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.order_id = o.order_id);
    SET seed_base_price = (SELECT price FROM products WHERE id = seed_chicken_product_id);
    SET seed_extra_price = (SELECT extra_price FROM product_option_choices WHERE option_id = seed_chicken_garlic_id);
    SET seed_unit_price = seed_base_price + seed_extra_price;
    INSERT INTO orders (`order_number`, `user_id`, `stall_id`, `stall_name`, `stall_location`, `status`, `payment_method`, `payment_status`, `payment_reference`, `pickup_type`, `pickup_at`, `total_amount`, `created_at`)
    SELECT 'GDDEMO202611130006', seed_customer_id, seed_bowls_stall_id, (SELECT name FROM stalls WHERE id = seed_bowls_stall_id), (SELECT location FROM stalls WHERE id = seed_bowls_stall_id), 'preparing', 'cash', 'pending', NULL, 'now', NULL, seed_unit_price * 1, '2026-11-13 13:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM orders WHERE order_number = 'GDDEMO202611130006');
    SET seed_order_is_new = ROW_COUNT();
    SET seed_order_id = (SELECT MIN(`order_id`) FROM `orders` WHERE order_number = 'GDDEMO202611130006' AND user_id = seed_customer_id AND stall_id = seed_bowls_stall_id);
    IF NOT (seed_order_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    IF seed_order_is_new = 1 THEN
        INSERT INTO order_items (order_id, menu_item_id, item_name, category_name, special_instructions, quantity, base_price, unit_price, total_price)
        SELECT seed_order_id, p.id, p.name, c.name, NULL, 1, seed_base_price, seed_unit_price, seed_unit_price * 1
        FROM products p JOIN categories c ON c.id = p.category_id AND c.stall_id = p.stall_id WHERE p.id = seed_chicken_product_id;
        SET seed_item_id = LAST_INSERT_ID();
        INSERT INTO order_item_options (order_item_id, option_id, option_group_name, option_name, quantity, extra_price)
        SELECT seed_item_id, c.option_id, g.group_name, c.option_name, 1, c.extra_price
        FROM product_option_choices c JOIN product_option_groups g ON g.group_id = c.group_id WHERE c.option_id = seed_chicken_garlic_id AND g.product_id = seed_chicken_product_id;
    END IF;
    SET seed_base_price = (SELECT price FROM products WHERE id = seed_fries_product_id);
    SET seed_extra_price = (SELECT extra_price FROM product_option_choices WHERE option_id = seed_fries_regular_id);
    SET seed_unit_price = seed_base_price + seed_extra_price;
    INSERT INTO orders (`order_number`, `user_id`, `stall_id`, `stall_name`, `stall_location`, `status`, `payment_method`, `payment_status`, `payment_reference`, `pickup_type`, `pickup_at`, `total_amount`, `created_at`)
    SELECT 'GDDEMO202611130007', seed_customer_id, seed_fries_stall_id, (SELECT name FROM stalls WHERE id = seed_fries_stall_id), (SELECT location FROM stalls WHERE id = seed_fries_stall_id), 'ready', 'online', 'pending', 'DEMO-PAYMENT-0007', 'now', NULL, seed_unit_price * 1, '2026-11-13 13:10:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM orders WHERE order_number = 'GDDEMO202611130007');
    SET seed_order_is_new = ROW_COUNT();
    SET seed_order_id = (SELECT MIN(`order_id`) FROM `orders` WHERE order_number = 'GDDEMO202611130007' AND user_id = seed_customer_id AND stall_id = seed_fries_stall_id);
    IF NOT (seed_order_id IS NOT NULL) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Unable to locate a required sample record; no samples were committed.'; END IF;
    IF seed_order_is_new = 1 THEN
        INSERT INTO order_items (order_id, menu_item_id, item_name, category_name, special_instructions, quantity, base_price, unit_price, total_price)
        SELECT seed_order_id, p.id, p.name, c.name, NULL, 1, seed_base_price, seed_unit_price, seed_unit_price * 1
        FROM products p JOIN categories c ON c.id = p.category_id AND c.stall_id = p.stall_id WHERE p.id = seed_fries_product_id;
        SET seed_item_id = LAST_INSERT_ID();
        INSERT INTO order_item_options (order_item_id, option_id, option_group_name, option_name, quantity, extra_price)
        SELECT seed_item_id, c.option_id, g.group_name, c.option_name, 1, c.extra_price
        FROM product_option_choices c JOIN product_option_groups g ON g.group_id = c.group_id WHERE c.option_id = seed_fries_regular_id AND g.product_id = seed_fries_product_id;
    END IF;
    INSERT INTO `store_requests` (`vendor_id`, `type`, `status`, `proposed_name`, `proposed_location`, `proposed_hours`, `proposed_description`, `stall_id`, `resolved_by`, `created_at`, `resolved_at`)
    SELECT seed_vendor_id, 'add', 'pending', 'Demo Fruit Cups', 'Agno', 'Mon-Fri 09:00-17:00', '{"store_type": "Snacks", "note": "Fruit cup counter proposal."}', NULL, NULL, '2026-11-13 10:00:00', NULL FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `store_requests` WHERE vendor_id = seed_vendor_id AND type = 'add' AND proposed_name = 'Demo Fruit Cups');
    INSERT INTO `store_requests` (`vendor_id`, `type`, `status`, `proposed_name`, `proposed_location`, `proposed_hours`, `proposed_description`, `stall_id`, `resolved_by`, `created_at`, `resolved_at`)
    SELECT seed_vendor_id, 'add', 'pending', 'Demo Sandwich Counter', 'Green Court', 'Mon-Sat 09:00-18:00', '{"store_type": "Food stall", "note": "Sandwich counter proposal."}', NULL, NULL, '2026-11-13 10:30:00', NULL FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `store_requests` WHERE vendor_id = seed_vendor_id AND type = 'add' AND proposed_name = 'Demo Sandwich Counter');
    INSERT INTO `store_requests` (`vendor_id`, `type`, `status`, `proposed_name`, `proposed_location`, `proposed_hours`, `proposed_description`, `stall_id`, `resolved_by`, `created_at`, `resolved_at`)
    SELECT seed_vendor_id, 'add', 'denied', 'Demo Evening Snacks', 'Agno', 'Fri-Sat 20:00-23:00', '{"store_type": "Snacks", "note": "The proposed location was unavailable."}', NULL, seed_admin_id, '2026-11-12 10:00:00', '2026-11-12 11:00:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `store_requests` WHERE vendor_id = seed_vendor_id AND type = 'add' AND proposed_name = 'Demo Evening Snacks');
    INSERT INTO `store_requests` (`vendor_id`, `type`, `status`, `proposed_name`, `proposed_location`, `proposed_hours`, `proposed_description`, `stall_id`, `resolved_by`, `created_at`, `resolved_at`)
    SELECT seed_vendor_id, 'add', 'approved', 'Demo Potato Corner', 'Bloemen', 'Mon-Sun 09:00-20:00', '{"store_type": "Snacks", "note": "Approved demonstration stall."}', seed_fries_stall_id, seed_admin_id, '2026-11-11 10:30:00', '2026-11-11 11:30:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `store_requests` WHERE vendor_id = seed_vendor_id AND type = 'add' AND proposed_name = 'Demo Potato Corner');
    INSERT INTO `store_requests` (`vendor_id`, `type`, `status`, `proposed_name`, `proposed_location`, `proposed_hours`, `proposed_description`, `stall_id`, `resolved_by`, `created_at`, `resolved_at`)
    SELECT seed_vendor_id, 'add', 'approved', 'Demo Rice Bowls', 'Gokongwei Hall', 'Mon-Sun 10:00-19:00', '{"store_type": "Restaurant", "note": "Approved demonstration stall."}', seed_bowls_stall_id, seed_admin_id, '2026-11-11 10:30:00', '2026-11-11 11:30:00' FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM `store_requests` WHERE vendor_id = seed_vendor_id AND type = 'add' AND proposed_name = 'Demo Rice Bowls');
    IF NOT ((SELECT COUNT(*) FROM `users`) >= 5 AND (SELECT COUNT(*) FROM `stalls`) >= 5 AND (SELECT COUNT(*) FROM `categories`) >= 5 AND (SELECT COUNT(*) FROM `products`) >= 5 AND (SELECT COUNT(*) FROM `product_option_groups`) >= 5 AND (SELECT COUNT(*) FROM `product_option_choices`) >= 5 AND (SELECT COUNT(*) FROM `orders`) >= 5 AND (SELECT COUNT(*) FROM `order_items`) >= 5 AND (SELECT COUNT(*) FROM `order_item_options`) >= 5 AND (SELECT COUNT(*) FROM `reviews`) >= 5 AND (SELECT COUNT(*) FROM `stall_operating_hours`) >= 5 AND (SELECT COUNT(*) FROM `store_requests`) >= 5) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Some tables still contain fewer than five records. No additions were committed; inspect your current data.'; END IF;
    COMMIT;
END//
DELIMITER ;

-- Verify exact counts, rather than relying on SHOW TABLES.
SELECT 'users' AS table_name, COUNT(*) AS sample_rows FROM `users`
UNION ALL
SELECT 'stalls' AS table_name, COUNT(*) AS sample_rows FROM `stalls`
UNION ALL
SELECT 'categories' AS table_name, COUNT(*) AS sample_rows FROM `categories`
UNION ALL
SELECT 'products' AS table_name, COUNT(*) AS sample_rows FROM `products`
UNION ALL
SELECT 'product_option_groups' AS table_name, COUNT(*) AS sample_rows FROM `product_option_groups`
UNION ALL
SELECT 'product_option_choices' AS table_name, COUNT(*) AS sample_rows FROM `product_option_choices`
UNION ALL
SELECT 'orders' AS table_name, COUNT(*) AS sample_rows FROM `orders`
UNION ALL
SELECT 'order_items' AS table_name, COUNT(*) AS sample_rows FROM `order_items`
UNION ALL
SELECT 'order_item_options' AS table_name, COUNT(*) AS sample_rows FROM `order_item_options`
UNION ALL
SELECT 'reviews' AS table_name, COUNT(*) AS sample_rows FROM `reviews`
UNION ALL
SELECT 'stall_operating_hours' AS table_name, COUNT(*) AS sample_rows FROM `stall_operating_hours`
UNION ALL
SELECT 'store_requests' AS table_name, COUNT(*) AS sample_rows FROM `store_requests`
ORDER BY table_name;
