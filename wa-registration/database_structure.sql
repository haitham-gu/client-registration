-- Database structure for Client Registration System
-- Run this SQL to create the required tables

CREATE DATABASE IF NOT EXISTS wa_registration 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE wa_registration;

-- Clients table to store registration data
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_code VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone1 VARCHAR(20) NOT NULL,
    phone2 VARCHAR(20),
    wilaya VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes for better performance
    INDEX idx_phone1 (phone1),
    INDEX idx_phone2 (phone2),
    INDEX idx_client_code (client_code),
    INDEX idx_created_at (created_at),
    INDEX idx_wilaya (wilaya),
    INDEX idx_city (city)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login attempts table for rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempts INT DEFAULT 1,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_ip_address (ip_address),
    INDEX idx_last_attempt (last_attempt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data (optional)
-- INSERT INTO clients (client_code, first_name, last_name, phone1, phone2, wilaya, city) VALUES
-- ('CL-2508-000001', 'أحمد', 'بن علي', '0555123456', '0777654321', 'الجزائر', 'الجزائر العاصمة'),
-- ('CL-2508-000002', 'فاطمة', 'بن محمد', '0666789012', NULL, 'وهران', 'وهران'),
-- ('CL-2508-000003', 'خالد', 'بن أحمد', '0777345678', '0555987654', 'قسنطينة', 'قسنطينة');
