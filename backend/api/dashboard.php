<?php
/**
 * GET /api/dashboard.php
 * Get user's dashboard data (requires auth)
 */

ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => "Error: $errstr"]);
    exit;
});

set_exception_handler(function($exception) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $exception->getMessage()]);
    exit;
});

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/jwt_helper.php';
    require_once __DIR__ . '/../includes/functions.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        ob_clean();
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    // Authenticate user
    $auth = JWTHelper::requireAuth();
    $userId = $auth['user_id'];

    $db = getDB();
    
    // Get user profile and stats
    $stmt = $db->prepare("
        SELECT 
            username, email, quit_date, cigarettes_per_day_before,
            cost_per_pack, cigarettes_per_pack, country, timezone,
            total_coins, current_streak, best_streak, sound_enabled,
            DATEDIFF(CURDATE(), quit_date) as total_days_quit
        FROM users 
        WHERE user_id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        ob_clean();
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }
    
    // Calculate money saved
    $dailyCost = ($user['cost_per_pack'] / $user['cigarettes_per_pack']) * $user['cigarettes_per_day_before'];
    $moneySaved = $dailyCost * max(0, $user['total_days_quit']);
    
    // Get recent daily logs (last 30 days)
    $stmt = $db->prepare("
        SELECT log_date, status, cigarettes_smoked, note
        FROM daily_logs
        WHERE user_id = ?
        ORDER BY log_date DESC
        LIMIT 30
    ");
    $stmt->execute([$userId]);
    $recentLogs = $stmt->fetchAll();
    
    // Check if user has checked in today
    $today = date('Y-m-d');
    $checkedInToday = false;
    foreach ($recentLogs as $log) {
        if ($log['log_date'] === $today) {
            $checkedInToday = true;
            break;
        }
    }
    
    // Get unlocked badges count
    $stmt = $db->prepare("SELECT COUNT(*) as badge_count FROM user_badges WHERE user_id = ?");
    $stmt->execute([$userId]);
    $badgeCount = $stmt->fetch()['badge_count'];
    
    // Get next badge to unlock
    $stmt = $db->prepare("
        SELECT b.name, b.requirement_value, b.coin_reward
        FROM badges b
        WHERE b.requirement_type = 'streak'
        AND b.requirement_value > ?
        AND b.badge_id NOT IN (SELECT badge_id FROM user_badges WHERE user_id = ?)
        ORDER BY b.requirement_value ASC
        LIMIT 1
    ");
    $stmt->execute([$user['current_streak'], $userId]);
    $nextBadge = $stmt->fetch();
    
    ob_end_clean();
    http_response_code(200);
    echo json_encode([
        'user' => [
            'username' => $user['username'],
            'quit_date' => $user['quit_date'],
            'total_days_quit' => (int)$user['total_days_quit'],
            'current_streak' => (int)$user['current_streak'],
            'best_streak' => (int)$user['best_streak'],
            'total_coins' => (int)$user['total_coins'],
            'badges_unlocked' => (int)$badgeCount,
            'money_saved' => round($moneySaved, 2),
            'sound_enabled' => (bool)$user['sound_enabled'],
            'checked_in_today' => $checkedInToday
        ],
        'next_badge' => $nextBadge,
        'recent_logs' => $recentLogs
    ]);
    
} catch (Exception $e) {
    ob_clean();
    error_log("Dashboard error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load dashboard: ' . $e->getMessage()]);
}

exit;
?>
