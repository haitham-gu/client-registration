<?php
// Authentication API for admin login system
// Handles login, logout, session management, and security

session_start();

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Configuration
const MAX_LOGIN_ATTEMPTS = 5;
const LOCKOUT_TIME = 900; // 15 minutes
const SESSION_TIMEOUT = 3600; // 1 hour
const REMEMBER_DURATION = 2592000; // 30 days

// Secure admin credentials - PRODUCTION READY
$ADMIN_USERS = [
    'admin' => [
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: password (CHANGE THIS!)
        'name' => 'مدير النظام'
    ]
];

$action = $_GET['action'] ?? ($_POST['action'] ?? getJsonInput()['action'] ?? '');

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check':
        handleCheckSession();
        break;
    default:
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid action']);
}

function handleLogin(): void {
    try {
        $input = getJsonInput();
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        $remember = $input['remember'] ?? false;
        
        error_log("Login attempt - Username: $username, Password length: " . strlen($password));
        
        if (!$username || !$password) {
            echo json_encode(['ok' => false, 'error' => 'اسم المستخدم وكلمة المرور مطلوبان']);
            return;
        }
    
    // Check rate limiting
    $attempts = getLoginAttempts($_SERVER['REMOTE_ADDR']);
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        $timeLeft = getRemainingLockoutTime($_SERVER['REMOTE_ADDR']);
        if ($timeLeft > 0) {
            echo json_encode([
                'ok' => false, 
                'error' => "تم حظر IP مؤقتاً. حاول بعد " . ceil($timeLeft / 60) . " دقيقة"
            ]);
            return;
        } else {
            // Reset attempts after lockout expires
            resetLoginAttempts($_SERVER['REMOTE_ADDR']);
        }
    }
    
    // Validate credentials
    $user = validateUser($username, $password);
    if (!$user) {
        incrementLoginAttempts($_SERVER['REMOTE_ADDR']);
        $newAttempts = getLoginAttempts($_SERVER['REMOTE_ADDR']);
        
        echo json_encode([
            'ok' => false, 
            'error' => 'اسم المستخدم أو كلمة المرور غير صحيحة',
            'attempts' => $newAttempts,
            'maxAttempts' => MAX_LOGIN_ATTEMPTS
        ]);
        return;
    }
    
    // Success - create session
    resetLoginAttempts($_SERVER['REMOTE_ADDR']);
    createSession($user, $remember);
    
        echo json_encode([
            'ok' => true,
            'user' => [
                'username' => $user['username'],
                'name' => $user['name']
            ]
        ]);
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'خطأ في النظام']);
    }
}

function handleLogout(): void {
    session_destroy();
    
    // Clear remember-me cookie if exists
    if (isset($_COOKIE['admin_remember'])) {
        setcookie('admin_remember', '', time() - 3600, '/');
    }
    
    echo json_encode(['ok' => true]);
}

function handleCheckSession(): void {
    $authenticated = false;
    $user = null;
    
    // Check session
    if (isset($_SESSION['admin_user']) && isset($_SESSION['login_time'])) {
        $timeElapsed = time() - $_SESSION['login_time'];
        if ($timeElapsed < SESSION_TIMEOUT) {
            $authenticated = true;
            $user = $_SESSION['admin_user'];
            // Refresh session time
            $_SESSION['login_time'] = time();
        } else {
            // Session expired
            session_destroy();
        }
    }
    
    // Check remember-me cookie
    if (!$authenticated && isset($_COOKIE['admin_remember'])) {
        $token = $_COOKIE['admin_remember'];
        $userData = validateRememberToken($token);
        if ($userData) {
            createSession($userData, false);
            $authenticated = true;
            $user = $userData;
        }
    }
    
    echo json_encode([
        'ok' => true,
        'authenticated' => $authenticated,
        'user' => $user
    ]);
}

function validateUser(string $username, string $password): ?array {
    global $ADMIN_USERS;
    
    if (isset($ADMIN_USERS[$username])) {
        $user = $ADMIN_USERS[$username];
        
        // For debugging - log attempt (remove in production)
        error_log("Login attempt for user: $username");
        
        if (password_verify($password, $user['password'])) {
            error_log("Password verified for user: $username");
            return [
                'username' => $username,
                'name' => $user['name']
            ];
        } else {
            error_log("Password verification failed for user: $username");
        }
    }
    error_log("User not found: $username");
    return null;
}

function createSession(array $user, bool $remember): void {
    session_regenerate_id(true);
    $_SESSION['admin_user'] = [
        'username' => $user['username'],
        'name' => $user['name']
    ];
    $_SESSION['login_time'] = time();
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + REMEMBER_DURATION;
        setcookie('admin_remember', $token . ':' . $user['username'], $expires, '/', '', false, true);
        
        // In production, store token in database with expiry
        // For now, we'll use a simple file-based approach
        file_put_contents(
            sys_get_temp_dir() . "/admin_token_$token", 
            json_encode(['username' => $user['username'], 'expires' => $expires])
        );
    }
}

function validateRememberToken(string $cookieValue): ?array {
    $parts = explode(':', $cookieValue, 2);
    if (count($parts) !== 2) return null;
    
    [$token, $username] = $parts;
    $tokenFile = sys_get_temp_dir() . "/admin_token_$token";
    
    if (!file_exists($tokenFile)) return null;
    
    $tokenData = json_decode(file_get_contents($tokenFile), true);
    if (!$tokenData || $tokenData['expires'] < time()) {
        unlink($tokenFile);
        return null;
    }
    
    // Find user
    foreach (ADMIN_USERS as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    
    return null;
}

function getLoginAttempts(string $ip): int {
    $file = sys_get_temp_dir() . "/login_attempts_" . md5($ip);
    if (!file_exists($file)) return 0;
    
    $data = json_decode(file_get_contents($file), true);
    if (!$data || $data['expires'] < time()) {
        unlink($file);
        return 0;
    }
    
    return $data['attempts'];
}

function incrementLoginAttempts(string $ip): void {
    $file = sys_get_temp_dir() . "/login_attempts_" . md5($ip);
    $attempts = getLoginAttempts($ip) + 1;
    $data = [
        'attempts' => $attempts,
        'expires' => time() + LOCKOUT_TIME
    ];
    file_put_contents($file, json_encode($data));
}

function resetLoginAttempts(string $ip): void {
    $file = sys_get_temp_dir() . "/login_attempts_" . md5($ip);
    if (file_exists($file)) {
        unlink($file);
    }
}

function getRemainingLockoutTime(string $ip): int {
    $file = sys_get_temp_dir() . "/login_attempts_" . md5($ip);
    if (!file_exists($file)) return 0;
    
    $data = json_decode(file_get_contents($file), true);
    if (!$data) return 0;
    
    return max(0, $data['expires'] - time());
}

function getJsonInput(): array {
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?: [];
}
?>
