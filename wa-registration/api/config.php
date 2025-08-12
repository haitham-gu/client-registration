<?php
// Database configuration for XAMPP (MySQL)
// Adjust credentials if you changed defaults in phpMyAdmin.
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP default is empty
define('DB_NAME', 'wa_registration');
define('DB_CHARSET', 'utf8mb4');

function db() {
	static $pdo = null;
	if ($pdo) return $pdo;
	$charset = DB_CHARSET;
	try {
		// Ensure database exists
		$dsnNoDb = 'mysql:host=' . DB_HOST . ';charset=' . $charset;
		$pdoTmp = new PDO($dsnNoDb, DB_USER, DB_PASS, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
		$pdoTmp->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET ' . DB_CHARSET . ' COLLATE utf8mb4_unicode_ci');

		// Connect to target DB
		$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . $charset;
		$pdo = new PDO($dsn, DB_USER, DB_PASS, [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		]);
		ensure_tables($pdo);
		return $pdo;
	} catch (Throwable $e) {
		// Propagate to caller; they may choose to fallback to CSV only
		throw $e;
	}
}

function ensure_tables(PDO $pdo): void {
	$pdo->exec('CREATE TABLE IF NOT EXISTS clients (
		id INT UNSIGNED NOT NULL AUTO_INCREMENT,
		full_name VARCHAR(150) NOT NULL,
		phone_dz VARCHAR(32) NOT NULL,
		phone_fr VARCHAR(32) NOT NULL,
		wilaya VARCHAR(120) NOT NULL,
		city_fr VARCHAR(120) NOT NULL,
		client_code VARCHAR(40) NULL UNIQUE,
		created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		INDEX idx_phone_dz (phone_dz),
		INDEX idx_phone_fr (phone_fr)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;');
}

function make_client_code_from_id(int $id): string {
	// Example: CL-2508-00001D (base36 id tail for compactness)
	$ym = date('ym');
	$base36 = strtoupper(base_convert((string)$id, 10, 36));
	return 'CL-' . $ym . '-' . str_pad($base36, 6, '0', STR_PAD_LEFT);
}
?>