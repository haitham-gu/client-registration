<?php
// Production Database Configuration Template
// IMPORTANT: Update these values for your hosting provider

// For hosting providers (update these values):
define('DB_HOST', 'localhost'); // or your host's database server
define('DB_USER', 'your_db_username'); // from hosting panel
define('DB_PASS', 'your_db_password'); // from hosting panel
define('DB_NAME', 'your_db_name'); // from hosting panel
define('DB_CHARSET', 'utf8mb4');

// Environment detection
define('IS_PRODUCTION', !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1']));

// Error reporting (hide in production)
if (IS_PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

function db() {
    static $pdo = null;
    if ($pdo) return $pdo;
    
    $charset = DB_CHARSET;
    try {
        // For production, database should already exist
        if (IS_PRODUCTION) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . $charset;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true,
            ]);
        } else {
            // Development: create database if it doesn't exist
            $dsnNoDb = 'mysql:host=' . DB_HOST . ';charset=' . $charset;
            $pdoTmp = new PDO($dsnNoDb, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $pdoTmp->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET ' . DB_CHARSET . ' COLLATE utf8mb4_unicode_ci');

            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . $charset;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        
        ensure_tables($pdo);
        return $pdo;
    } catch (PDOException $e) {
        if (IS_PRODUCTION) {
            // Log error securely in production
            error_log("Database connection failed: " . $e->getMessage());
            die(json_encode(['error' => 'Database connection failed']));
        } else {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}

function ensure_tables($pdo) {
    $createTable = "
    CREATE TABLE IF NOT EXISTS clients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_code VARCHAR(20) UNIQUE NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        second_phone VARCHAR(20),
        wilaya VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_client_code (client_code),
        INDEX idx_phone (phone),
        INDEX idx_created_at (created_at)
    ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ";
    
    try {
        $pdo->exec($createTable);
    } catch (PDOException $e) {
        if (!IS_PRODUCTION) {
            die("Table creation failed: " . $e->getMessage());
        }
        error_log("Table creation failed: " . $e->getMessage());
    }
}

function generateClientCode() {
    $year = date('y');
    $month = date('m');
    $random = str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
    return "CL-{$year}{$month}-{$random}";
}
?>
