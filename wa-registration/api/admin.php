<?php
// Admin API endpoint for dashboard operations
// Works under XAMPP: http://localhost/wa-registration/api/admin.php

require_once __DIR__ . '/config.php';

// Start session for authentication check
session_start();

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([ 'ok' => false, 'error' => 'Method not allowed' ]);
    exit;
}

// Check authentication
if (!isAuthenticated()) {
    http_response_code(401);
    echo json_encode([ 'ok' => false, 'error' => 'Unauthorized', 'redirect' => 'login.html' ]);
    exit;
}

$action = $_GET['action'] ?? 'list';

try {
    $pdo = db();
    
    switch ($action) {
        case 'list':
            handleListClients($pdo);
            break;
        case 'export':
            handleExportClients($pdo);
            break;
        case 'stats':
            handleGetStats($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode([ 'ok' => false, 'error' => 'Invalid action' ]);
    }
} catch (Throwable $e) {
    http_response_code(500);
    $detail = 'Server error';
    if (!class_exists('PDO')) {
        $detail = 'PDO extension not available in PHP. Enable pdo_mysql in XAMPP.';
    } else if (stripos($e->getMessage(), 'SQLSTATE') !== false) {
        $detail = 'Database connection or query failed. Check credentials and MySQL status.';
    }
    echo json_encode([ 'ok' => false, 'error' => $detail ]);
}

function handleListClients(PDO $pdo): void {
    // Get all clients
    $stmt = $pdo->query('SELECT * FROM clients ORDER BY created_at DESC');
    $clients = $stmt->fetchAll();
    
    // Get statistics
    $stats = getStats($pdo);
    
    echo json_encode([
        'ok' => true,
        'clients' => $clients,
        'stats' => $stats
    ]);
}

function handleExportClients(PDO $pdo): void {
    $stmt = $pdo->query('SELECT * FROM clients ORDER BY created_at DESC');
    $clients = $stmt->fetchAll();
    
    // Set CSV headers
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="clients_' . date('Y-m-d') . '.csv"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    // Output CSV
    $output = fopen('php://output', 'w');
    
    // Add BOM for proper UTF-8 encoding in Excel
    fputs($output, "\xEF\xBB\xBF");
    
    // CSV headers
    fputcsv($output, [
        'كود العميل',
        'الاسم الكامل',
        'هاتف الجزائر',
        'هاتف فرنسا',
        'الولاية',
        'المدينة الفرنسية',
        'تاريخ التسجيل'
    ]);
    
    // CSV data
    foreach ($clients as $client) {
        fputcsv($output, [
            $client['client_code'],
            $client['full_name'],
            $client['phone_dz'],
            $client['phone_fr'],
            $client['wilaya'],
            $client['city_fr'],
            $client['created_at']
        ]);
    }
    
    fclose($output);
}

function handleGetStats(PDO $pdo): void {
    $stats = getStats($pdo);
    echo json_encode([ 'ok' => true, 'stats' => $stats ]);
}

function getStats(PDO $pdo): array {
    $stats = [];
    
    // Total clients
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM clients');
    $stats['total'] = (int)$stmt->fetchColumn();
    
    // Today's registrations
    $stmt = $pdo->query('SELECT COUNT(*) as today FROM clients WHERE DATE(created_at) = CURDATE()');
    $stats['today'] = (int)$stmt->fetchColumn();
    
    // This week's registrations
    $stmt = $pdo->query('SELECT COUNT(*) as thisWeek FROM clients WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)');
    $stats['thisWeek'] = (int)$stmt->fetchColumn();
    
    // Top wilaya
    $stmt = $pdo->query('SELECT wilaya, COUNT(*) as count FROM clients GROUP BY wilaya ORDER BY count DESC LIMIT 1');
    $topWilaya = $stmt->fetch();
    $stats['topWilaya'] = $topWilaya ? $topWilaya['wilaya'] : '-';
    
    // Additional stats for potential future use
    $stmt = $pdo->query('SELECT city_fr, COUNT(*) as count FROM clients GROUP BY city_fr ORDER BY count DESC LIMIT 1');
    $topCity = $stmt->fetch();
    $stats['topCityFR'] = $topCity ? $topCity['city_fr'] : '-';
    
    return $stats;
}

function isAuthenticated(): bool {
    // Check session
    if (isset($_SESSION['admin_user']) && isset($_SESSION['login_time'])) {
        $timeElapsed = time() - $_SESSION['login_time'];
        if ($timeElapsed < 3600) { // 1 hour session timeout
            // Refresh session time
            $_SESSION['login_time'] = time();
            return true;
        } else {
            // Session expired
            session_destroy();
        }
    }
    
    // Check remember-me cookie
    if (isset($_COOKIE['admin_remember'])) {
        $parts = explode(':', $_COOKIE['admin_remember'], 2);
        if (count($parts) === 2) {
            [$token, $username] = $parts;
            $tokenFile = sys_get_temp_dir() . "/admin_token_$token";
            
            if (file_exists($tokenFile)) {
                $tokenData = json_decode(file_get_contents($tokenFile), true);
                if ($tokenData && $tokenData['expires'] > time()) {
                    // Valid remember token - refresh session
                    $_SESSION['admin_user'] = ['username' => $username];
                    $_SESSION['login_time'] = time();
                    return true;
                }
            }
        }
    }
    
    return false;
}
?>
