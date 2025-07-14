-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 
-- Версия на сървъра: 10.1.21-MariaDB
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `car_rental`
--

DELIMITER $$
--
-- Процедури
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `FilterCars` (IN `p_brand_id` INT, IN `p_model_id` INT, IN `p_gearbox_id` INT, IN `p_type_id` INT, IN `p_year_from` INT, IN `p_year_to` INT, IN `p_mileage_max` INT, IN `p_price_max` DECIMAL(10,2), IN `p_car_status_id` INT)  BEGIN
    SELECT 
        c.car_id, m.model_name, b.brand_name, g.gearbox_name, 
        c.year_manufacture, c.mileage, t.type_name, 
        c.price_per_day, c.image_url, cs.car_status_name
    FROM cars c
    JOIN models m ON c.model_id = m.model_id
    JOIN brands b ON m.brand_id = b.brand_id
    JOIN gearboxes g ON c.gearbox_id = g.gearbox_id
    JOIN types t ON c.type_id = t.type_id
    JOIN car_status cs ON c.car_status_id = cs.car_status_id      WHERE 
        (p_brand_id IS NULL OR b.brand_id = p_brand_id) AND
        (p_model_id IS NULL OR m.model_id = p_model_id) AND
        (p_gearbox_id IS NULL OR g.gearbox_id = p_gearbox_id) AND
        (p_type_id IS NULL OR t.type_id = p_type_id) AND
        (p_year_from IS NULL OR c.year_manufacture >= p_year_from) AND
        (p_year_to IS NULL OR c.year_manufacture <= p_year_to) AND
        (p_mileage_max IS NULL OR c.mileage <= p_mileage_max) AND
        (p_price_max IS NULL OR c.price_per_day <= p_price_max) AND
        (p_car_status_id IS NULL OR c.car_status_id = p_car_status_id)      ORDER BY b.brand_name, m.model_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllCars` (IN `p_car_status_id` INT)  BEGIN
    SELECT c.car_id, 
           m.model_name, 
           b.brand_name, 
           g.gearbox_name, 
           c.year_manufacture, 
           c.mileage, 
           t.type_name, 
           c.price_per_day, 
           c.image_url
    FROM cars c
    JOIN models m ON c.model_id = m.model_id
    JOIN brands b ON m.brand_id = b.brand_id
    JOIN gearboxes g ON c.gearbox_id = g.gearbox_id
    JOIN types t ON c.type_id = t.type_id
    JOIN car_status cs ON c.car_status_id = cs.car_status_id
    WHERE (p_car_status_id IS NULL OR c.car_status_id = p_car_status_id)
    ORDER BY b.brand_name ASC, m.model_name ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetCarList` ()  BEGIN
    SELECT 
        c.car_id, 
        b.brand_name, 
        m.model_name, 
        c.year_manufacture 
    FROM cars c
    JOIN models m ON c.model_id = m.model_id
    JOIN brands b ON m.brand_id = b.brand_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetVipCars` ()  BEGIN
    SELECT c.car_id, m.model_name, b.brand_name, g.gearbox_name,
           c.year_manufacture, c.mileage, t.type_name,
           c.price_per_day, c.image_url
    FROM cars c
    JOIN models m ON c.model_id = m.model_id
    JOIN brands b ON m.brand_id = b.brand_id
    JOIN gearboxes g ON c.gearbox_id = g.gearbox_id
    JOIN types t ON c.type_id = t.type_id
    JOIN car_status cs ON c.car_status_id = cs.car_status_id
    WHERE cs.car_status_id = 3
    ORDER BY c.price_per_day DESC;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура на таблица `brands`
--

CREATE TABLE `brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `brands`
--

INSERT INTO `brands` (`brand_id`, `brand_name`) VALUES
(1, 'Toyota'),
(2, 'BMW'),
(3, 'Volkswagen'),
(4, 'Audi'),
(5, 'Ford'),
(6, 'Lamborghini'),
(7, 'Ferrari'),
(8, 'Rolls-Royce'),
(9, 'Bentley'),
(10, 'Aston Martin'),
(11, 'Bugatti'),
(12, 'Hyundai'),
(13, 'Mercedes'),
(14, 'Renault'),
(15, 'Nissan'),
(16, 'Tesla'),
(17, 'Lada');

--
-- Тригери `brands`
--
DELIMITER $$
CREATE TRIGGER `before_brand_delete` BEFORE DELETE ON `brands` FOR EACH ROW BEGIN
    DECLARE brand_used INT;

    SELECT COUNT(*) INTO brand_used
    FROM cars
    WHERE brand_id = OLD.brand_id;

    IF brand_used > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete this brand. It is used in one or more cars.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура на таблица `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `gearbox_id` int(11) NOT NULL,
  `year_manufacture` int(11) NOT NULL,
  `mileage` int(11) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `car_status_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `cars`
--

INSERT INTO `cars` (`car_id`, `model_id`, `color_id`, `type_id`, `gearbox_id`, `year_manufacture`, `mileage`, `price_per_day`, `car_status_id`, `image_url`) VALUES
(1, 1, 2, 1, 2, 2020, 35000, '45.00', 2, 'images/toyota_corolla.png'),
(2, 2, 1, 3, 2, 2021, 22000, '85.00', 4, 'images/bmw_x51.png'),
(3, 3, 3, 2, 1, 2019, 55000, '40.00', 1, 'images/vw_golf.png'),
(4, 4, 5, 1, 2, 2022, 18000, '75.00', 4, 'images/audi_a4.png'),
(5, 5, 4, 2, 1, 2018, 70000, '35.00', 1, 'images/ford_focus.png'),
(6, 6, 2, 2, 1, 2022, 15000, '15.00', 1, 'images/toyota_yaris.png'),
(7, 7, 3, 2, 2, 2023, 7000, '60.00', 1, 'images/vw_golf2.png'),
(8, 8, 1, 5, 2, 2023, 5000, '1750.00', 3, 'images/revuelto.png'),
(9, 9, 2, 5, 2, 2024, 3000, '2720.00', 3, 'images/812_superfast.png'),
(10, 10, 3, 6, 2, 2023, 4000, '2850.00', 3, 'images/phantom.png'),
(11, 11, 4, 5, 2, 2022, 7000, '1680.00', 3, 'images/continental_gt.png'),
(12, 12, 5, 5, 2, 2024, 2500, '1700.00', 3, 'images/valor.png'),
(13, 13, 1, 5, 2, 2023, 6000, '2000.00', 3, 'images/chiron.png'),
(20, 14, 3, 3, 2, 2023, 12132, '55.00', 2, 'images/6803b70edcc78_kona.png'),
(21, 15, 2, 1, 2, 2015, 50000, '60.00', 4, 'images/6803bb96e8fec_cla.png'),
(24, 16, 2, 3, 1, 2018, 100000, '35.00', 2, 'images/680699fcd8284_tucson_2018.png'),
(25, 17, 2, 2, 1, 2021, 36000, '30.00', 1, 'images/6806a5562b9f3_clio.png'),
(26, 18, 2, 3, 2, 2022, 22000, '45.00', 4, 'images/6806a82e7c14d_x-trail.png'),
(41, 19, 1, 10, 1, 2018, 200000, '40.00', 4, 'images/680d22896a2dc_vito.png'),
(43, 21, 6, 1, 2, 2019, 100000, '40.00', 2, 'images/680e958287897_passat.png'),
(44, 22, 4, 1, 2, 2023, 23108, '45.00', 2, 'images/680e966830e8c_tesla.png');

--
-- Тригери `cars`
--
DELIMITER $$
CREATE TRIGGER `log_deleted_car` BEFORE DELETE ON `cars` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1
        FROM Rentals
        WHERE car_id = OLD.car_id
          AND (
                (rental_date <= NOW() AND return_date >= NOW())
                OR rental_date > NOW()
          )
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete car: It has ongoing or future rentals.';
    ELSE
        INSERT INTO Deleted_Cars_Log (
            car_id,
            model_id,
            color_id,
            type_id,
            gearbox_id,
            year_manufacture,
            mileage,
            price_per_day,
            car_status_id,
            image_url,
            deleted_at
        )
        VALUES (
            OLD.car_id,
            OLD.model_id,
            OLD.color_id,
            OLD.type_id,
            OLD.gearbox_id,
            OLD.year_manufacture,
            OLD.mileage,
            OLD.price_per_day,
            OLD.car_status_id,
            OLD.image_url,
            NOW()
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура на таблица `car_status`
--

CREATE TABLE `car_status` (
  `car_status_id` int(11) NOT NULL,
  `car_status_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `car_status`
--

INSERT INTO `car_status` (`car_status_id`, `car_status_name`) VALUES
(1, 'Budget'),
(2, 'Standard'),
(3, 'VIP'),
(4, 'Luxury');

--
-- Тригери `car_status`
--
DELIMITER $$
CREATE TRIGGER `before_car_status_delete` BEFORE DELETE ON `car_status` FOR EACH ROW BEGIN
    DECLARE status_used INT;

    SELECT COUNT(*) INTO status_used
    FROM cars
    WHERE car_status_id = OLD.car_status_id;

    IF status_used > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete this car status. It is used in one or more cars.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура на таблица `colors`
--

CREATE TABLE `colors` (
  `color_id` int(11) NOT NULL,
  `color_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `colors`
--

INSERT INTO `colors` (`color_id`, `color_name`) VALUES
(1, 'Black'),
(2, 'White'),
(3, 'Blue'),
(4, 'Red'),
(5, 'Silver'),
(6, 'Gray'),
(7, 'Green'),
(8, 'Yellow'),
(9, 'Orange'),
(10, 'Brown'),
(11, 'Beige'),
(12, 'Gold'),
(13, 'Purple'),
(14, 'Pink'),
(15, 'Bronze'),
(16, 'Maroon'),
(17, 'Turquoise'),
(18, 'Navy');

--
-- Тригери `colors`
--
DELIMITER $$
CREATE TRIGGER `before_color_delete` BEFORE DELETE ON `colors` FOR EACH ROW BEGIN
        DECLARE color_used INT;
    SELECT COUNT(*) INTO color_used FROM cars WHERE color_id = OLD.color_id;

        IF color_used > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete this color. It is used in one or more cars.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура на таблица `deleted_cars_log`
--

CREATE TABLE `deleted_cars_log` (
  `log_id` int(11) NOT NULL,
  `car_id` int(11) DEFAULT NULL,
  `mileage` int(11) DEFAULT NULL,
  `price_per_day` decimal(10,2) DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `model_id` int(11) NOT NULL,
  `color_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `gearbox_id` int(11) NOT NULL,
  `year_manufacture` int(11) NOT NULL,
  `car_status_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `deleted_cars_log`
--

INSERT INTO `deleted_cars_log` (`log_id`, `car_id`, `mileage`, `price_per_day`, `deleted_at`, `model_id`, `color_id`, `type_id`, `gearbox_id`, `year_manufacture`, `car_status_id`, `image_url`) VALUES
(3, 31, 100000, '40.00', '2025-04-26 16:32:12', 19, 1, 10, 2, 2020, 1, 'images/680d07f720762_vito.png'),
(4, 32, 15555555, '40.00', '2025-04-26 16:33:32', 19, 1, 10, 1, 2020, 1, 'images/680d0abc0fe7e_vito.png'),
(5, 33, 213216, '40.00', '2025-04-26 16:41:40', 19, 1, 10, 1, 2023, 1, 'images/680d0cb15eb86_vito.png'),
(6, 34, 213216, '40.00', '2025-04-26 16:46:37', 19, 1, 10, 1, 2020, 1, 'images/680d0de4ace8d_vito.png'),
(7, 35, 213216, '40.00', '2025-04-26 16:50:03', 19, 1, 10, 1, 2020, 1, 'images/680d0eacd2e1b_vito.png'),
(8, 36, 213216, '40.00', '2025-04-26 16:53:49', 19, 2, 3, 2, 2020, 1, 'images/680d0f9713ccc_vito.png'),
(9, 37, 213216, '40.00', '2025-04-26 17:00:26', 19, 1, 1, 1, 2020, 1, 'images/680d111d07a36_vito.png'),
(10, 38, 213216, '40.00', '2025-04-26 17:05:14', 19, 2, 3, 1, 2020, 1, 'images/680d1242e1fa0_vito.png'),
(11, 39, 155000, '40.00', '2025-04-26 17:06:31', 19, 2, 14, 2, 2020, 1, 'images/680d1279504cb_vito.png'),
(12, 40, 200000, '40.00', '2025-04-26 18:14:08', 19, 1, 10, 1, 2020, 1, 'images/680d2268681e0_vito.png'),
(13, 42, 40000, '50.00', '2025-04-27 20:34:29', 20, 3, 4, 1, 1997, 2, 'images/680e9419f1874_rs2.png'),
(14, 45, 22222222, '100.00', '2025-05-09 18:05:37', 23, 2, 3, 2, 2020, 2, 'images/681e43b8be8a0_tesla.png'),
(15, 46, 11121313, '105.00', '2025-05-09 18:10:26', 23, 4, 3, 2, 2021, 2, 'images/681e44e475e0d_tesla.png'),
(16, 47, 150000, '30.00', '2025-05-09 18:26:22', 24, 4, 3, 1, 2000, 1, 'images/681e472283139_x-trail.png'),
(17, 48, 150000, '30.00', '2025-05-09 18:26:25', 24, 1, 3, 1, 2000, 1, 'images/681e48a5c8479_images.jpg'),
(18, 49, 2121323, '25.00', '2025-05-09 18:30:45', 24, 1, 3, 1, 2000, 2, 'images/681e4985851e3_images.jpg'),
(19, 46, 696969, '6969.00', '2025-05-13 16:21:21', 26, 1, 3, 1, 2003, 1, 'images/68236ffb3391e_494690129_700631459077247_1580527691592605021_n.jpg'),
(20, 45, 100000, '50.00', '2025-05-13 16:21:24', 25, 1, 1, 2, 2018, 4, 'images/68236f1e426b5_487836779_1345040550039610_841832303038017350_n.jpg');

-- --------------------------------------------------------

--
-- Структура на таблица `gearboxes`
--

CREATE TABLE `gearboxes` (
  `gearbox_id` int(11) NOT NULL,
  `gearbox_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `gearboxes`
--

INSERT INTO `gearboxes` (`gearbox_id`, `gearbox_name`) VALUES
(1, 'Manual'),
(2, 'Automatic');

--
-- Тригери `gearboxes`
--
DELIMITER $$
CREATE TRIGGER `before_gearbox_delete` BEFORE DELETE ON `gearboxes` FOR EACH ROW BEGIN
    DECLARE gearbox_used INT;

    SELECT COUNT(*) INTO gearbox_used
    FROM cars
    WHERE gearbox_id = OLD.gearbox_id;

    IF gearbox_used > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete this gearbox. It is used in one or more cars.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура на таблица `models`
--

CREATE TABLE `models` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(50) NOT NULL,
  `brand_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `models`
--

INSERT INTO `models` (`model_id`, `model_name`, `brand_id`) VALUES
(1, 'Corolla', 1),
(2, 'X5', 2),
(3, 'Golf 7', 3),
(4, 'A4', 4),
(5, 'Focus', 5),
(6, 'Yaris', 1),
(7, 'Golf 8', 3),
(8, 'Revuelto', 6),
(9, '812 Superfast', 7),
(10, 'Phantom', 8),
(11, 'Continental GT', 9),
(12, 'Valor', 10),
(13, 'Chiron', 11),
(14, 'Kona', 12),
(15, 'Cla', 13),
(16, 'Tucson', 12),
(17, 'Clio', 14),
(18, 'X-Trail', 15),
(19, 'Vito', 13),
(20, 'RS2', 4),
(21, 'Passat B8', 3),
(22, 'Model S', 16),
(23, 'Y', 16),
(24, 'Niva', 17),
(25, 'C250d', 13),
(26, 'Moro', 13);

--
-- Тригери `models`
--
DELIMITER $$
CREATE TRIGGER `before_model_delete` BEFORE DELETE ON `models` FOR EACH ROW BEGIN
    DECLARE model_used INT;

    SELECT COUNT(*) INTO model_used
    FROM cars
    WHERE model_id = OLD.model_id;

    IF model_used > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot delete this model. It is used in one or more cars.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура на таблица `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `payments`
--

INSERT INTO `payments` (`payment_id`, `rental_id`, `amount`, `payment_date`, `payment_method_id`) VALUES
(2, 23, '170.00', '2025-04-14', 1),
(3, 24, '70.00', '2025-04-14', 2),
(4, 25, '70.00', '2025-04-14', 2),
(5, 26, '70.00', '2025-04-14', 2),
(6, 27, '170.00', '2025-04-14', 2),
(7, 28, '170.00', '2025-04-14', 2),
(9, 30, '40.00', '2025-04-14', 2),
(10, 31, '30.00', '2025-04-14', 2),
(11, 32, '15.00', '2025-04-14', 1),
(12, 33, '135.00', '2025-04-14', 2),
(13, 34, '80.00', '2025-04-14', 2),
(14, 35, '85.00', '2025-04-14', 2),
(15, 36, '85.00', '2025-04-14', 2),
(16, 37, '85.00', '2025-04-14', 1),
(17, 38, '150.00', '2025-04-14', 2),
(18, 39, '120.00', '2025-04-14', 2),
(19, 40, '90.00', '2025-04-14', 2),
(20, 41, '90.00', '2025-04-14', 2),
(21, 42, '90.00', '2025-04-14', 2),
(22, 43, '15.00', '2025-04-14', 2),
(23, 44, '45.00', '2025-04-14', 2),
(24, 45, '170.00', '2025-04-14', 2),
(25, 46, '150.00', '2025-04-14', 2),
(26, 47, '70.00', '2025-04-14', 2),
(27, 48, '70.00', '2025-04-14', 1),
(28, 49, '35.00', '2025-04-14', 2),
(29, 50, '150.00', '2025-04-14', 2),
(30, 51, '35.00', '2025-04-14', 1),
(31, 52, '120.00', '2025-04-14', 2),
(32, 53, '150.00', '2025-04-14', 2),
(33, 54, '120.00', '2025-04-14', 1),
(34, 55, '105.00', '2025-04-14', 2),
(35, 56, '450.00', '2025-04-14', 2),
(36, 57, '15.00', '2025-04-14', 2),
(37, 58, '1280.00', '2025-04-15', 1),
(38, 59, '1360.00', '2025-04-15', 2),
(39, 60, '116960.00', '2025-04-19', 2),
(40, 61, '21000.00', '2025-04-19', 2),
(41, 62, '595.00', '2025-04-26', 2),
(42, 63, '70.00', '2025-04-26', 2),
(43, 64, '45.00', '2025-04-26', 2),
(44, 65, '1700.00', '2025-04-26', 1),
(45, 66, '15.00', '2025-04-26', 2),
(46, 67, '150.00', '2025-04-26', 2),
(47, 68, '40.00', '2025-04-26', 2),
(48, 69, '29550.00', '2025-04-27', 2),
(49, 70, '1680.00', '2025-04-27', 1),
(50, 71, '21252000.00', '2025-04-27', 2),
(51, 72, '73014000.00', '2025-04-27', 2),
(52, 73, '18000.00', '2025-04-27', 2),
(53, 74, '18000.00', '2025-04-27', 2),
(54, 75, '450.00', '2025-04-27', 2),
(55, 76, '150.00', '2025-04-27', 2),
(56, 77, '45.00', '2025-04-27', 2),
(57, 78, '165.00', '2025-04-27', 2),
(58, 79, '525.00', '2025-04-27', 1),
(59, 80, '120.00', '2025-04-27', 1),
(60, 81, '180.00', '2025-04-27', 2),
(61, 82, '105.00', '2025-04-28', 2),
(62, 83, '150.00', '2025-05-08', 2),
(63, 84, '5440.00', '2025-05-09', 1),
(64, 85, '150.00', '2025-05-09', 1),
(65, 86, '6000.00', '2025-05-09', 1),
(66, 87, '165.00', '2025-05-09', 1),
(67, 88, '135.00', '2025-05-09', 1),
(68, 89, '160.00', '2025-05-09', 1);

-- --------------------------------------------------------

--
-- Структура на таблица `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_method_id` int(11) NOT NULL,
  `payment_method_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `payment_methods`
--

INSERT INTO `payment_methods` (`payment_method_id`, `payment_method_name`) VALUES
(1, 'Credit Card'),
(2, 'Cash');

-- --------------------------------------------------------

--
-- Структура на таблица `rentals`
--

CREATE TABLE `rentals` (
  `rental_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rental_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `rental_status_id` int(11) NOT NULL,
  `pickup_address` varchar(255) NOT NULL,
  `pickup_time` time NOT NULL,
  `dropoff_time` time NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `rentals`
--

INSERT INTO `rentals` (`rental_id`, `car_id`, `user_id`, `rental_date`, `return_date`, `total_price`, `rental_status_id`, `pickup_address`, `pickup_time`, `dropoff_time`, `created_at`) VALUES
(23, 2, 1, '2025-04-15', '2025-04-17', '170.00', 4, 'Varna Grand Mall', '11:50:00', '11:50:00', NULL),
(24, 5, 1, '2025-04-15', '2025-04-17', '70.00', 4, 'Varna Grand Mall', '15:17:00', '13:17:00', NULL),
(25, 5, 1, '2025-04-15', '2025-04-17', '70.00', 4, 'Varna Grand Mall', '15:17:00', '13:17:00', NULL),
(26, 5, 1, '2025-04-15', '2025-04-17', '70.00', 4, 'Varna Grand Mall', '15:17:00', '13:17:00', NULL),
(27, 2, 1, '2025-04-21', '2025-04-23', '170.00', 4, 'Varna Grand Mall', '12:26:00', '12:26:00', NULL),
(28, 2, 1, '2025-04-21', '2025-04-23', '170.00', 4, 'Varna Grand Mall', '12:26:00', '12:26:00', NULL),
(30, 3, 1, '2025-04-15', '2025-04-16', '40.00', 4, 'Varna Grand Mall', '12:43:00', '12:43:00', NULL),
(31, 6, 1, '2025-04-21', '2025-04-23', '30.00', 4, 'Varna Grand Mall', '15:40:00', '15:40:00', NULL),
(32, 6, 1, '2025-04-29', '2025-04-30', '15.00', 4, 'Varna Grand Mall', '14:40:00', '14:40:00', NULL),
(33, 1, 1, '2025-04-21', '2025-04-24', '135.00', 4, 'Varna Grand Mall', '15:47:00', '16:48:00', NULL),
(34, 3, 1, '2025-04-22', '2025-04-24', '80.00', 4, 'Varna Grand Mall', '17:51:00', '17:51:00', NULL),
(35, 2, 1, '2025-05-19', '2025-05-20', '85.00', 2, 'Varna Grand Mall', '16:57:00', '15:57:00', NULL),
(36, 2, 1, '2025-04-29', '2025-04-30', '85.00', 4, 'Varna Grand Mall', '13:00:00', '13:00:00', NULL),
(37, 2, 1, '2025-04-27', '2025-04-28', '85.00', 4, 'Varna Grand Mall', '13:01:00', '13:01:00', NULL),
(38, 4, 1, '2025-04-21', '2025-04-23', '150.00', 4, 'Varna Grand Mall', '14:04:00', '14:04:00', NULL),
(39, 7, 1, '2025-04-28', '2025-04-30', '120.00', 4, 'Varna Grand Mall', '18:05:00', '18:05:00', NULL),
(40, 1, 1, '2025-04-28', '2025-04-30', '90.00', 4, 'Varna Grand Mall', '14:09:00', '14:09:00', NULL),
(41, 1, 1, '2025-04-14', '2025-04-16', '90.00', 4, 'Varna Grand Mall', '17:16:00', '16:16:00', NULL),
(42, 1, 1, '2025-06-10', '2025-06-12', '90.00', 2, 'Varna Grand Mall', '16:17:00', '16:17:00', NULL),
(43, 6, 1, '2025-05-07', '2025-05-08', '15.00', 4, 'Varna Grand Mall', '14:25:00', '14:25:00', NULL),
(44, 6, 1, '2025-06-10', '2025-06-13', '45.00', 2, 'Varna Grand Mall', '16:24:00', '16:24:00', NULL),
(45, 2, 1, '2025-06-10', '2025-06-12', '170.00', 2, 'Varna Grand Mall', '05:31:00', '06:31:00', NULL),
(46, 4, 1, '2025-05-13', '2025-05-15', '150.00', 2, 'Varna Grand Mall', '15:33:00', '15:33:00', NULL),
(47, 5, 1, '2025-06-16', '2025-06-18', '70.00', 3, 'Varna Grand Mall', '14:42:00', '14:41:00', NULL),
(48, 5, 1, '2025-04-21', '2025-04-23', '70.00', 4, 'Varna Grand Mall', '14:43:00', '14:42:00', NULL),
(49, 5, 1, '2025-04-28', '2025-04-29', '35.00', 4, 'Varna Grand Mall', '18:55:00', '19:55:00', NULL),
(50, 4, 1, '2025-07-08', '2025-07-10', '150.00', 3, 'Varna Grand Mall', '20:29:00', '20:29:00', NULL),
(51, 5, 1, '2025-04-24', '2025-04-25', '35.00', 4, 'Varna Grand Mall', '17:41:00', '16:41:00', NULL),
(52, 7, 1, '2025-04-15', '2025-04-17', '120.00', 4, 'Varna Grand Mall', '17:50:00', '17:50:00', NULL),
(53, 4, 1, '2025-12-29', '2025-12-31', '150.00', 3, 'Varna Grand Mall', '17:01:00', '17:01:00', NULL),
(54, 7, 2, '2025-04-22', '2025-04-24', '120.00', 2, 'Varna Grand Mall', '20:13:00', '18:13:00', NULL),
(55, 5, 1, '2025-04-30', '2025-05-03', '105.00', 4, 'Varna Airport', '03:48:00', '00:48:00', NULL),
(56, 4, 1, '2025-04-24', '2025-04-30', '450.00', 4, 'Varna Airport', '19:22:00', '17:24:00', NULL),
(57, 6, 1, '2025-04-14', '2025-04-15', '15.00', 4, 'Varna Airport', '18:08:00', '18:08:00', NULL),
(58, 13, 1, '2025-04-21', '2025-04-23', '1280.00', 4, 'Varna Airport', '01:29:00', '01:29:00', NULL),
(59, 11, 1, '2025-04-28', '2025-04-30', '1360.00', 4, 'Varna Airport', '19:33:00', '21:33:00', NULL),
(60, 9, 3, '2025-07-09', '2025-08-21', '116960.00', 3, 'Dobri Voinikov 8', '19:27:00', '19:27:00', NULL),
(61, 8, 3, '2025-06-18', '2025-06-30', '21000.00', 3, 'Dobri Voinikov 8', '07:00:00', '07:00:00', NULL),
(62, 2, 2, '2025-05-01', '2025-05-08', '595.00', 2, 'Varna Center', '19:38:00', '19:38:00', NULL),
(63, 5, 2, '2025-05-13', '2025-05-15', '70.00', 2, 'Varna Center', '01:01:00', '01:01:00', '2025-04-26 20:15:50'),
(64, 1, 1, '2025-04-26', '2025-04-27', '45.00', 4, 'Varna Center', '21:38:00', '21:38:00', '2025-04-26 20:39:19'),
(65, 12, 1, '2025-04-26', '2025-04-27', '1700.00', 4, 'Varna Center', '20:41:00', '20:41:00', '2025-04-26 20:40:29'),
(66, 6, 1, '2025-04-26', '2025-04-27', '15.00', 4, 'Varna Center', '21:07:00', '21:07:00', '2025-04-26 21:06:23'),
(67, 4, 1, '2025-05-21', '2025-05-23', '150.00', 3, 'Varna Center', '11:11:00', '11:11:00', '2025-04-26 21:13:10'),
(68, 41, 3, '2025-04-26', '2025-04-27', '40.00', 4, 'Varna Center', '11:11:00', '11:11:00', '2025-04-26 21:14:45'),
(69, 4, 5, '2025-05-01', '2026-05-30', '29550.00', 3, 'Varna', '01:00:00', '13:00:00', '2025-04-27 22:01:03'),
(70, 11, 4, '2025-06-11', '2025-06-12', '1680.00', 2, 'Hamburg', '06:59:00', '18:59:00', '2025-04-27 22:02:12'),
(71, 13, 4, '2025-04-27', '2054-05-31', '21252000.00', 3, 'Hamburg', '00:06:00', '03:09:00', '2025-04-27 22:03:18'),
(72, 13, 5, '2025-04-30', '2125-04-13', '73014000.00', 3, 'Varna', '02:02:00', '02:02:00', '2025-04-27 22:05:27'),
(73, 13, 4, '2025-04-27', '2025-05-06', '18000.00', 3, 'Hamburg', '00:08:00', '02:11:00', '2025-04-27 22:06:42'),
(74, 13, 4, '2025-04-27', '2025-05-06', '18000.00', 3, 'Hamburg', '00:08:00', '02:11:00', '2025-04-27 22:07:11'),
(75, 4, 4, '2025-06-01', '2025-06-07', '450.00', 2, 'Hamburg', '00:15:00', '23:14:00', '2025-04-27 22:14:01'),
(76, 4, 1, '2025-12-25', '2025-12-27', '150.00', 2, 'Varna Center', '06:59:00', '06:59:00', '2025-04-27 22:28:25'),
(77, 26, 1, '2025-04-29', '2025-04-30', '45.00', 3, 'Varna Center', '23:32:00', '23:32:00', '2025-04-27 22:32:28'),
(78, 20, 1, '2025-04-27', '2025-04-30', '165.00', 4, 'Varna bl18', '04:44:00', '04:44:00', '2025-04-27 22:51:37'),
(79, 4, 1, '2025-05-01', '2025-05-08', '525.00', 4, 'Varna bl18', '13:13:00', '13:13:00', '2025-04-27 22:53:06'),
(80, 3, 3, '2025-04-27', '2025-04-30', '120.00', 1, 'Varna bl18', '03:33:00', '03:33:00', '2025-04-27 23:02:24'),
(81, 25, 6, '2025-04-30', '2025-05-06', '180.00', 2, 'Varna bl18', '22:22:00', '22:22:00', '2025-04-27 23:05:52'),
(82, 5, 3, '2025-05-04', '2025-05-07', '105.00', 3, 'Varna bl18', '12:12:00', '12:12:00', '2025-04-28 15:07:49'),
(83, 4, 1, '2025-05-19', '2025-05-21', '150.00', 2, 'Varna Center', '11:11:00', '12:12:00', '2025-05-08 23:04:14'),
(84, 9, 1, '2025-05-20', '2025-05-22', '5440.00', 3, 'Varna bl18', '21:58:00', '21:58:00', '2025-05-09 20:58:46'),
(85, 4, 1, '2025-05-22', '2025-05-24', '150.00', 3, 'Varna bl18', '11:11:00', '11:11:00', '2025-05-09 21:02:02'),
(86, 13, 1, '2025-05-09', '2025-05-12', '6000.00', 3, 'Varna Center', '23:07:00', '23:07:00', '2025-05-09 21:07:37'),
(87, 20, 1, '2025-05-09', '2025-05-12', '165.00', 3, 'Varna Center', '22:17:00', '12:12:00', '2025-05-09 21:17:33'),
(88, 26, 1, '2025-05-09', '2025-05-12', '135.00', 3, 'Varna bl18', '23:23:00', '23:23:00', '2025-05-09 21:23:32'),
(89, 41, 1, '2025-05-09', '2025-05-13', '160.00', 3, 'Varna Center', '23:27:00', '23:27:00', '2025-05-09 21:27:49');

--
-- Тригери `rentals`
--
DELIMITER $$
CREATE TRIGGER `after_return_date` AFTER UPDATE ON `rentals` FOR EACH ROW BEGIN
    IF NEW.return_date <= NOW() AND NEW.rental_status_id != 4 THEN
                UPDATE rentals 
        SET rental_status_id = 4 
        WHERE rental_id = NEW.rental_id;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_rental` BEFORE INSERT ON `rentals` FOR EACH ROW BEGIN
    SET NEW.created_at = NOW();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура на таблица `rental_status`
--

CREATE TABLE `rental_status` (
  `rental_status_id` int(11) NOT NULL,
  `rental_status_name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `rental_status`
--

INSERT INTO `rental_status` (`rental_status_id`, `rental_status_name`) VALUES
(1, 'Active'),
(2, 'Approved'),
(3, 'Cancelled'),
(4, 'Returned');

-- --------------------------------------------------------

--
-- Структура на таблица `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'user'),
(2, 'admin');

-- --------------------------------------------------------

--
-- Структура на таблица `types`
--

CREATE TABLE `types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `types`
--

INSERT INTO `types` (`type_id`, `type_name`) VALUES
(1, 'Sedan'),
(2, 'Hatchback'),
(3, 'SUV'),
(4, 'Wagon'),
(5, 'Coupe'),
(6, 'Limousine'),
(7, 'Convertible'),
(8, 'Pickup'),
(9, 'Crossover'),
(10, 'Minivan'),
(11, 'Roadster'),
(12, 'Van'),
(13, 'Microcar'),
(14, 'Fastback'),
(15, 'Targa'),
(16, 'Cabriolet');

-- --------------------------------------------------------

--
-- Структура на таблица `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role_id` int(11) NOT NULL,
  `keyword` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Схема на данните от таблица `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `role_id`, `keyword`) VALUES
(1, 'Milen Bogdanov', '$2y$10$nXk4WgU4lsM9uAfpL2ogj.psWPpq.APnvHveSG.vFGF42zWo7xZSW', 'test@gmail.com', 1, 'test'),
(2, 'Ivan Ivanov', '$2y$10$lDgrg.5fk9HNytQ4Iri55OW0y60finfFsCPhXX2AaYrN6O/LRZs0m', 'ivan@gmail.com', 2, 'ivan'),
(3, 'Admin', '$2y$10$mjnu/JKXhLMI0FCFu.BMEufr.lARCowk0YLzuHBmb4esnWyXybGKq', 'admin@gmail.com', 2, 'admin'),
(4, 'Petya2000', '$2y$10$9V39g9ofQaTuXysLeeMmw.N/R1h/nJhGZnJdo0AFV1OAmgfCUAcWi', 'petyaatanasova@gmail.com', 1, 'kak se kazvam + godinata'),
(5, 'stelko', '$2y$10$5o8Hdo1OoK7r229SbPxhEebociDfnTiBl4x.Pi/P4U0MtVPHB.Cvm', 'stelko@stelko.com', 1, '1.8t'),
(6, 'ProjectTest', '$2y$10$ewGITcxBrInfgDGWdpqmNOjwuLZHP.ZhSDntQUN4kIaKCFUNVPvAu', 'projecttest@gmail.com', 1, 'projecttest');

--
-- Тригери `users`
--
DELIMITER $$
CREATE TRIGGER `before_user_delete` BEFORE DELETE ON `users` FOR EACH ROW BEGIN
        DECLARE user_has_rentals INT;
    SELECT COUNT(*) INTO user_has_rentals FROM rentals WHERE user_id = OLD.user_id;

        IF user_has_rentals > 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete this user. They have active rentals.';
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `color_id` (`color_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `gearbox_id` (`gearbox_id`),
  ADD KEY `car_status_id` (`car_status_id`);

--
-- Indexes for table `car_status`
--
ALTER TABLE `car_status`
  ADD PRIMARY KEY (`car_status_id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`color_id`);

--
-- Indexes for table `deleted_cars_log`
--
ALTER TABLE `deleted_cars_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `gearboxes`
--
ALTER TABLE `gearboxes`
  ADD PRIMARY KEY (`gearbox_id`);

--
-- Indexes for table `models`
--
ALTER TABLE `models`
  ADD PRIMARY KEY (`model_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `rental_id` (`rental_id`),
  ADD KEY `payment_method_id` (`payment_method_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_method_id`);

--
-- Indexes for table `rentals`
--
ALTER TABLE `rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `rental_status_id` (`rental_status_id`);

--
-- Indexes for table `rental_status`
--
ALTER TABLE `rental_status`
  ADD PRIMARY KEY (`rental_status_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
--
-- AUTO_INCREMENT for table `car_status`
--
ALTER TABLE `car_status`
  MODIFY `car_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `color_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `deleted_cars_log`
--
ALTER TABLE `deleted_cars_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `gearboxes`
--
ALTER TABLE `gearboxes`
  MODIFY `gearbox_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `models`
--
ALTER TABLE `models`
  MODIFY `model_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;
--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `rentals`
--
ALTER TABLE `rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;
--
-- AUTO_INCREMENT for table `rental_status`
--
ALTER TABLE `rental_status`
  MODIFY `rental_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- Ограничения за дъмпнати таблици
--

--
-- Ограничения за таблица `cars`
--
ALTER TABLE `cars`
  ADD CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `models` (`model_id`),
  ADD CONSTRAINT `cars_ibfk_2` FOREIGN KEY (`color_id`) REFERENCES `colors` (`color_id`),
  ADD CONSTRAINT `cars_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `types` (`type_id`),
  ADD CONSTRAINT `cars_ibfk_4` FOREIGN KEY (`gearbox_id`) REFERENCES `gearboxes` (`gearbox_id`),
  ADD CONSTRAINT `cars_ibfk_5` FOREIGN KEY (`car_status_id`) REFERENCES `car_status` (`car_status_id`);

--
-- Ограничения за таблица `models`
--
ALTER TABLE `models`
  ADD CONSTRAINT `models_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`);

--
-- Ограничения за таблица `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`payment_method_id`);

--
-- Ограничения за таблица `rentals`
--
ALTER TABLE `rentals`
  ADD CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`),
  ADD CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `rentals_ibfk_3` FOREIGN KEY (`rental_status_id`) REFERENCES `rental_status` (`rental_status_id`);

--
-- Ограничения за таблица `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
