<?php
/**
 * POST /api/relapse
 * Log a relapse (user smoked)
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/jwt_helper.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = JWTHelper::requireAuth();
$userId = $auth['user_id'];

$input = json_decode(file_get_contents('php://input'), true);
$logDate = $input['date'] ?? date('Y-m-d');
$cigarettesSmoked = (int)($input['cigarettes_smoked'] ?? 1);
$note = $input['note'] ?? null;

try {
    $db = getDB();
    $db->beginTransaction();
    
    // Log the relapse
    $stmt = $db->prepare("
        INSERT INTO daily_logs (user_id, log_date, status, cigarettes_smoked, note)
        VALUES (?, ?, 'relapse', ?, ?)
        ON DUPLICATE KEY UPDATE 
            status = 'relapse', 
            cigarettes_smoked = ?, 
            note = ?,
            logged_at = NOW()
    ");
    $stmt->execute([
        $userId, $logDate, $cigarettesSmoked, $note,
        $cigarettesSmoked, $note
    ]);
    
    // PUNISHMENTS:
    
    // 1. Reset streak to 0
    $stmt = $db->prepare("UPDATE users SET current_streak = 0 WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // 2. Deduct coins
    awardCoins($userId, COINS_RELAPSE_PENALTY, 'relapse_penalty', $logDate);
    
    // Get current user stats for response
    $stmt = $db->prepare("SELECT total_coins, best_streak FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userStats = $stmt->fetch();
    
    $db->commit();
    
    sendJSON([
        'success' => true,
        'message' => 'Relapse logged. Your streak has been reset.',
        'punishment' => [
            'streak_reset' => true,
            'coins_deducted' => abs(COINS_RELAPSE_PENALTY),
            'new_total_coins' => (int)$userStats['total_coins']
        ],
        'encouragement' => 'Don\'t give up! Every day is a new chance to quit. Your best streak was ' . $userStats['best_streak'] . ' days - you can beat it!'
    ]);
    
} catch (Exception $e) {
    $db->rollBack();
    error_log("Relapse log error: " . $e->getMessage());
    sendJSON(['error' => 'Failed to log relapse'], 500);
}
?>
