<?php
// Database Configuration Template
// Copy this file to config.php and update with your database credentials

// ⚠️ IMPORTANT: Change these values before deployment!
const DB_HOST = 'localhost';
const DB_NAME = 'your_database_name';
const DB_USER = 'your_database_user';
const DB_PASS = 'your_database_password';
const DB_CHARSET = 'utf8mb4';

// Database connection function
function getDatabaseConnection(): PDO {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection failed");
    }
}

// Create tables if they don't exist
function initializeDatabase(): void {
    try {
        $pdo = getDatabaseConnection();
        
        // Create clients table
        $sql = "CREATE TABLE IF NOT EXISTS clients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_code VARCHAR(20) UNIQUE NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            phone1 VARCHAR(20) NOT NULL,
            phone2 VARCHAR(20),
            wilaya VARCHAR(100) NOT NULL,
            city VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_phone1 (phone1),
            INDEX idx_phone2 (phone2),
            INDEX idx_client_code (client_code),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        
        // Create login_attempts table for rate limiting
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            attempts INT DEFAULT 1,
            last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_ip_address (ip_address)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($sql);
        
    } catch (Exception $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        throw $e;
    }
}

// Generate unique client code
function generateClientCode(): string {
    $year = date('y');
    $month = date('m');
    
    // Get next sequence number
    $pdo = getDatabaseConnection();
    $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING(client_code, 9) AS UNSIGNED)) as max_num FROM clients WHERE client_code LIKE ?");
    $prefix = "CL-{$year}{$month}-%";
    $stmt->execute([$prefix]);
    $result = $stmt->fetch();
    
    $nextNum = ($result['max_num'] ?? 0) + 1;
    $sequence = str_pad($nextNum, 6, '0', STR_PAD_LEFT);
    
    return "CL-{$year}{$month}-{$sequence}";
}

// Initialize database on first include
initializeDatabase();
?>
