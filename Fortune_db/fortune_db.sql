-- Fortune Database Schema
CREATE DATABASE IF NOT EXISTS fortune_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fortune_db;

-- Fortune History Table (stores all fortune readings)
CREATE TABLE IF NOT EXISTS fortune_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fortune_type ENUM('daily', 'tarot', 'saju', 'omikuji', 'compatibility') NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_birthdate DATE,
    user_gender ENUM('male', 'female', 'other'),
    result_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fortune_type (fortune_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daily Fortune Messages
CREATE TABLE IF NOT EXISTS daily_fortunes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fortune_text TEXT NOT NULL,
    lucky_item VARCHAR(100),
    lucky_color VARCHAR(50),
    lucky_number INT,
    fortune_level ENUM('excellent', 'good', 'normal', 'caution', 'bad') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tarot Cards
CREATE TABLE IF NOT EXISTS tarot_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    card_name VARCHAR(100) NOT NULL,
    card_meaning TEXT NOT NULL,
    reversed_meaning TEXT NOT NULL,
    card_category ENUM('major', 'minor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Saju Fortune Results
CREATE TABLE IF NOT EXISTS saju_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zodiac_sign VARCHAR(50) NOT NULL,
    element VARCHAR(50) NOT NULL,
    fortune_text TEXT NOT NULL,
    personality_text TEXT NOT NULL,
    career_text TEXT NOT NULL,
    love_text TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Omikuji Results (Japanese Fortune Slips)
CREATE TABLE IF NOT EXISTS omikuji_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fortune_level VARCHAR(50) NOT NULL,
    fortune_text TEXT NOT NULL,
    wish_text VARCHAR(200),
    health_text VARCHAR(200),
    study_text VARCHAR(200),
    business_text VARCHAR(200),
    travel_text VARCHAR(200)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
