-- Critical Section Fixes Migration
-- This file contains database schema changes to prevent race conditions and data inconsistencies

USE fortune_db;

-- Fix 1: Add composite index to fortune_history for better performance
-- This helps prevent duplicate queries within a short time frame
ALTER TABLE fortune_history
ADD INDEX idx_user_time (user_name, fortune_type, created_at);

-- Fix 2: Add UNIQUE constraint to zodiac_compatibility table
-- Ensures that each zodiac pair combination is unique
-- First check if the constraint already exists and drop it if needed
ALTER TABLE zodiac_compatibility
ADD UNIQUE KEY unique_zodiac_pair (zodiac1, zodiac2);

-- Fix 3: Add UNIQUE constraint to star_sign_compatibility table (if it exists)
-- Ensures that each star sign pair combination is unique
-- Note: This table may not exist yet based on compatibility_data.sql
-- Uncomment the following lines if the table exists:
-- ALTER TABLE star_sign_compatibility
-- ADD UNIQUE KEY unique_star_sign_pair (sign1, sign2);

-- Fix 4: Update fortune_history table to support VARCHAR for user_birthdate
-- This was already done in a previous migration but ensuring it's correct
-- The column should support various date formats including combined birthdates like "1990-01-01 & 1992-05-15"
ALTER TABLE fortune_history
MODIFY COLUMN user_birthdate VARCHAR(200);

-- Fix 5: Ensure fortune_type ENUM includes 'compatibility'
-- This was already done in a previous migration
ALTER TABLE fortune_history
MODIFY COLUMN fortune_type ENUM('daily', 'tarot', 'saju', 'omikuji', 'compatibility') NOT NULL;

-- Fix 6: Add index for better query performance on zodiac_compatibility
ALTER TABLE zodiac_compatibility
ADD INDEX idx_zodiac1 (zodiac1),
ADD INDEX idx_zodiac2 (zodiac2);

-- Optimization: Convert to InnoDB if not already
ALTER TABLE zodiac_compatibility ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: For star_sign_compatibility table, add similar indexes if the table exists
-- Uncomment the following lines if needed:
-- ALTER TABLE star_sign_compatibility
-- ADD INDEX idx_sign1 (sign1),
-- ADD INDEX idx_sign2 (sign2);
-- ALTER TABLE star_sign_compatibility ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
