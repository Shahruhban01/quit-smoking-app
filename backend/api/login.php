<?php
/**
 * POST /api/login.php
 * FIXED VERSION - Authenticate user and return JWT
 */

// === ERROR HANDLING SETUP ===
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => "PHP Error: $errstr"]);
    exit;
});

set_exception_handler(function($exception) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $exception->getMessage()]);
    exit;
});

// === MAIN LOGIC ===
try {
    require_once __DIR__ . '/../config/db.php';
    
    // Check if JWT library exists, if not use simple token
    $useJWT = file_exists(__DIR__ . '/../includes/jwt_helper.php');
    if ($useJWT) {
        require_once __DIR__ . '/../includes/jwt_helper.php';
    }
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    $email = isset($input['email']) ? filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL) : false;
    $password = isset($input['password']) ? $input['password'] : '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password required']);
        exit;
    }

    // Fetch user by email
    $db = getDB();
    $stmt = $db->prepare("
        SELECT user_id, username, email, password_hash, last_login
        FROM users 
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }
    
    // Generate token
    if ($useJWT) {
        // Use JWT if available
        $token = JWTHelper::encode($user['user_id'], $user['username']);
    } else {
        // Fallback to simple session token
        $token = bin2hex(random_bytes(32));
        // Store token in session or database if needed
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
    }
    
    // Update last login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
    $stmt->execute([$user['user_id']]);
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'user_id' => (int)$user['user_id'],
            'username' => $user['username'],
            'email' => $user['email']
        ]
    ]);
    
} catch (PDOException $e) {
    ob_clean();
    error_log("Login PDO error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    
} catch (Exception $e) {
    ob_clean();
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Login failed: ' . $e->getMessage()]);
}

exit;
?>
