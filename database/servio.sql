-- Servio — Book Borrowing Management System
-- MySQL 8 / MariaDB 10.6+
-- Equivalent to the Laravel migration + BookSeeder.
-- Use this script OR `php artisan migrate --seed`, not both.

CREATE DATABASE IF NOT EXISTS `servio`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `servio`;

DROP TABLE IF EXISTS `books`;

CREATE TABLE `books` (
    `book_id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`      VARCHAR(255) NOT NULL,
    `author`     VARCHAR(255) NOT NULL,
    `status`     ENUM('Available', 'Borrowed') NOT NULL DEFAULT 'Available',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`book_id`),
    KEY `books_title_index` (`title`),
    KEY `books_author_index` (`author`),
    KEY `books_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `books` (`title`, `author`, `status`, `created_at`, `updated_at`) VALUES
    ('It Ends With Us',        'Colleen Hoover',   'Available', NOW(), NOW()),
    ('Fourth Wing',            'Rebecca Yarros',   'Available', NOW(), NOW()),
    ('The Silent Patient',     'Alex Michaelides', 'Available', NOW(), NOW()),
    ('The Housemaid',          'Freida McFadden',  'Available', NOW(), NOW()),
    ('Project Hail Mary',      'Andy Weir',        'Available', NOW(), NOW()),
    ('Better Than The Movies', 'Lynn Painter',     'Available', NOW(), NOW());