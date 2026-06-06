

CREATE DATABASE IF NOT EXISTS `online-libraty`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `online-libraty`;

CREATE TABLE IF NOT EXISTS `register` (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    email      VARCHAR(100) NOT NULL UNIQUE,
    phone      VARCHAR(20)  NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
