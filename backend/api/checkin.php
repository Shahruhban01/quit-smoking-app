<?php
/**
 * POST /api/checkin.php
 * Daily check-in: "No cigarettes today"
 * FIXED: Better transaction handling
 */

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

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../includes/jwt_helper.php';
    require_once __DIR__ . '/../includes/functions.php';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ob_clean();
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    // Authenticate user
    $auth = JWTHelper::requireAuth();
    $userId = $auth['user_id'];

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        ob_clean();
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }
    
    $logDate = isset($input['date']) ? $input['date'] : date('Y-m-d');
    $note = isset($input['note']) ? $input['note'] : null;

    $db = getDB();
    
    // START TRANSACTION ONCE
    $db->beginTransaction();
    
    try {
        // 1. Insert or update daily log
        $stmt = $db->prepare("
            INSERT INTO daily_logs (user_id, log_date, status, note)
            VALUES (?, ?, 'no_smoke', ?)
            ON DUPLICATE KEY UPDATE status = 'no_smoke', note = ?, logged_at = NOW()
        ");
        $stmt->execute([$userId, $logDate, $note, $note]);
        
        // 2. Recalculate streak
        $currentStreak = calculateCurrentStreak($userId);
        updateUserStreak($userId, $currentStreak);
        
        // 3. Award daily coins
        awardCoins($userId, COINS_DAILY_CHECKIN, 'daily_checkin', $logDate);
        $coinsEarned = COINS_DAILY_CHECKIN;
        
        // 4. Check for streak milestone bonuses
        global $STREAK_MILESTONES;
        if (isset($STREAK_MILESTONES[$currentStreak])) {
            $milestoneBonus = $STREAK_MILESTONES[$currentStreak];
            awardCoins($userId, $milestoneBonus, 'streak_milestone', "streak_$currentStreak");
            $coinsEarned += $milestoneBonus;
        }
        
        // 5. Check for badge unlocks
        $newBadges = checkAndUnlockBadges($userId, $currentStreak);
        
        // 6. Calculate total coins earned (including badge rewards)
        foreach ($newBadges as $badge) {
            $coinsEarned += $badge['coin_reward'];
        }
        
        // COMMIT TRANSACTION
        $db->commit();
        
        ob_end_clean();
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Check-in successful!',
            'current_streak' => $currentStreak,
            'coins_earned' => $coinsEarned,
            'new_badges' => $newBadges
        ]);
        
    } catch (Exception $innerException) {
        // Rollback on any error
        $db->rollBack();
        throw $innerException;
    }
    
} catch (PDOException $e) {
    ob_clean();
    error_log("Check-in PDO error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    
} catch (Exception $e) {
    ob_clean();
    error_log("Check-in error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Check-in failed: ' . $e->getMessage()]);
}

exit;
?>
