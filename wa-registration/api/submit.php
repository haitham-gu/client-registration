<?php
// Simple JSON API endpoint for form submission with MySQL storage and client code
// Works under XAMPP: http://localhost/wa-registration/api/submit.php

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode([ 'ok' => false, 'error' => 'Method not allowed' ]);
	exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
	http_response_code(400);
	echo json_encode([ 'ok' => false, 'error' => 'Invalid JSON' ]);
	exit;
}

$fullName = trim($data['fullName'] ?? '');
$phoneDZ  = trim($data['phoneDZ'] ?? '');
$phoneFR  = trim($data['phoneFR'] ?? '');
$wilaya   = trim($data['wilaya'] ?? '');
$cityFR   = trim($data['cityFR'] ?? '');

if ($fullName === '' || $phoneDZ === '' || $phoneFR === '' || $wilaya === '' || $cityFR === '') {
	http_response_code(400);
	echo json_encode([ 'ok' => false, 'error' => 'Missing fields' ]);
	exit;
}

if (!preg_match('/^\d+$/', $phoneDZ) || !preg_match('/^\d+$/', $phoneFR)) {
	http_response_code(400);
	echo json_encode([ 'ok' => false, 'error' => 'Phone must be digits' ]);
	exit;
}

// Append to backup/submissions.csv (best-effort)
try {
	$csvDir = __DIR__ . '/../backup';
	if (!is_dir($csvDir)) {
		@mkdir($csvDir, 0777, true);
	}
	$csvPath = $csvDir . '/submissions.csv';
	$isNew = !file_exists($csvPath);
	if ($fp = fopen($csvPath, 'a')) {
		if ($isNew) {
			fputcsv($fp, ['timestamp', 'fullName', 'phoneDZ', 'phoneFR', 'wilaya', 'cityFR']);
		}
		fputcsv($fp, [date('c'), $fullName, $phoneDZ, $phoneFR, $wilaya, $cityFR]);
		fclose($fp);
	}
} catch (Throwable $e) {
	// Ignore CSV failures; DB is the source of truth
}

// Check if already registered (both phone numbers must match)
try {
	$pdo = db();
	$existingStmt = $pdo->prepare('SELECT client_code, full_name FROM clients WHERE phone_dz = ? AND phone_fr = ? LIMIT 1');
	$existingStmt->execute([$phoneDZ, $phoneFR]);
	$existing = $existingStmt->fetch();
	
	if ($existing) {
		// Already registered - return existing code
		echo json_encode([ 
			'ok' => true, 
			'clientCode' => $existing['client_code'],
			'isExisting' => true,
			'existingName' => $existing['full_name']
		]);
		exit;
	}
	
	// Insert new registration and generate client code
	$stmt = $pdo->prepare('INSERT INTO clients (full_name, phone_dz, phone_fr, wilaya, city_fr) VALUES (?, ?, ?, ?, ?)');
	$stmt->execute([$fullName, $phoneDZ, $phoneFR, $wilaya, $cityFR]);
	$id = (int)$pdo->lastInsertId();
	$code = make_client_code_from_id($id);
	$upd = $pdo->prepare('UPDATE clients SET client_code = ? WHERE id = ?');
	$upd->execute([$code, $id]);

	echo json_encode([ 'ok' => true, 'clientCode' => $code, 'isExisting' => false ]);
	exit;
} catch (Throwable $e) {
	http_response_code(500);
	$detail = 'DB error';
	if (!class_exists('PDO')) {
		$detail = 'PDO extension not available in PHP. Enable pdo_mysql in XAMPP.';
	} else if (stripos($e->getMessage(), 'SQLSTATE') !== false) {
		$detail = 'Database connection or query failed. Check credentials and MySQL status.';
	}
	echo json_encode([ 'ok' => false, 'error' => $detail ]);
	exit;
}
?>