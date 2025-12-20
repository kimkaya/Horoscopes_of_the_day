-- Add phone_number field to fortune_history table
USE fortune_db;

ALTER TABLE fortune_history
ADD COLUMN user_phone VARCHAR(20) AFTER user_gender;
