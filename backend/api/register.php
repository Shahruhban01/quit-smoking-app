<?php
/**
 * POST /api/register.php
 * FIXED VERSION with comprehensive error handling
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

// Custom error/exception handlers
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => "PHP Error: $errstr in " . basename($errfile) . " line $errline"]);
    exit;
});

set_exception_handler(function($exception) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => 'Exception: ' . $exception->getMessage()]);
    exit;
});

// === MAIN LOGIC ===
try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/functions.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON: ' . json_last_error_msg()]);
        exit;
    }

    // Validate required fields
    $required = ['username', 'email', 'password', 'quit_date', 'cigarettes_per_day'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            exit;
        }
    }

    // Sanitize inputs
    $username = trim($input['username']);
    $email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
    $password = $input['password'];
    $quitDate = $input['quit_date'];
    $cigsPerDay = (int)$input['cigarettes_per_day'];
    $costPerPack = isset($input['cost_per_pack']) ? (float)$input['cost_per_pack'] : 10.00;
    $country = isset($input['country']) ? $input['country'] : 'US';
    $timezone = isset($input['timezone']) ? $input['timezone'] : 'UTC';

    // Validation
    if (!$email) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email format']);
        exit;
    }

    if (strlen($password) < 8) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 8 characters']);
        exit;
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        http_response_code(400);
        echo json_encode(['error' => 'Username must be 3-50 characters']);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $db = getDB();
    
    $stmt = $db->prepare("
        INSERT INTO users (
            username, email, password_hash, quit_date, 
            cigarettes_per_day_before, cost_per_pack, country, timezone
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $success = $stmt->execute([
        $username, $email, $passwordHash, $quitDate,
        $cigsPerDay, $costPerPack, $country, $timezone
    ]);
    
    if (!$success) {
        throw new Exception('Database insert failed');
    }
    
    $userId = $db->lastInsertId();
    
    ob_end_clean(); // Clear buffer
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user_id' => (int)$userId
    ]);
    
} catch (PDOException $e) {
    ob_clean();
    
    if ($e->getCode() == 23000) { // Duplicate entry
        http_response_code(409);
        echo json_encode(['error' => 'Username or email already exists']);
    } else {
        error_log("Registration PDO error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
    
} catch (Exception $e) {
    ob_clean();
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed: ' . $e->getMessage()]);
}

exit;
?>
