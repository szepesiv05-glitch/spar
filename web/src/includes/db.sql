-- 1. Felhasználók tábla
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Nyitvatartás tábla
CREATE TABLE `openhours` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `day_of_week` TINYINT NOT NULL COMMENT '1: Hétfő, 7: Vasárnap',
    `opening_time` TIME NULL,
    `closing_time` TIME NULL,
    `is_closed` BOOLEAN DEFAULT FALSE,
    UNIQUE(`day_of_week`)
) ENGINE=InnoDB;

-- 3. Ünnepnapok tábla
CREATE TABLE `holidays` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `holiday_date` DATE NOT NULL UNIQUE,
    `description` VARCHAR(255),
    `is_workday` BOOLEAN DEFAULT FALSE COMMENT 'Ha esetleg ünnepnapi pótlékkal dolgoznak'
) ENGINE=InnoDB;

-- 4. Műszakok tábla
CREATE TABLE `shifts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected', 'finished') DEFAULT 'pending',
    `admin_comment` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Alapadatok az openhours táblához
INSERT INTO `openhours` (`day_of_week`, `opening_time`, `closing_time`, `is_closed`) VALUES
(1, '06:30:00', '21:00:00', FALSE),
(2, '06:30:00', '21:00:00', FALSE),
(3, '06:30:00', '21:00:00', FALSE),
(4, '06:30:00', '21:00:00', FALSE),
(5, '06:30:00', '21:00:00', FALSE),
(6, '06:30:00', '21:00:00', FALSE),
(7, '07:00:00', '20:00:00', FALSE);