<?php
/**
 * Utility Functions - FIXED TRANSACTION HANDLING
 */

require_once __DIR__ . '/../config/db.php';

// COIN EARNING CONSTANTS (must match frontend)
define('COINS_DAILY_CHECKIN', 10);
define('COINS_RELAPSE_PENALTY', -50);

$STREAK_MILESTONES = [
    7 => 100,
    14 => 150,
    30 => 500,
    60 => 800,
    90 => 1500,
    180 => 3000,
    365 => 10000
];

/**
 * Calculate current streak from daily logs
 */
function calculateCurrentStreak($userId) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            SELECT log_date, status 
            FROM daily_logs 
            WHERE user_id = ? 
            ORDER BY log_date DESC
        ");
        $stmt->execute([$userId]);
        $logs = $stmt->fetchAll();

        $streak = 0;
        $previousDate = null;

        foreach ($logs as $log) {
            $currentDate = new DateTime($log['log_date']);
            
            if ($log['status'] === 'no_smoke') {
                if ($previousDate === null) {
                    $streak++;
                    $previousDate = $currentDate;
                } elseif ($currentDate->diff($previousDate)->days === 1) {
                    $streak++;
                    $previousDate = $currentDate;
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        return $streak;
        
    } catch (Exception $e) {
        error_log("calculateCurrentStreak error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Award coins to user and log transaction
 * FIXED: No longer manages transactions - caller must handle
 */
function awardCoins($userId, $amount, $reason, $referenceId = null) {
    try {
        $db = getDB();
        
        // Update user's total coins (no transaction - caller handles it)
        $stmt = $db->prepare("
            UPDATE users 
            SET total_coins = GREATEST(0, total_coins + ?) 
            WHERE user_id = ?
        ");
        $stmt->execute([$amount, $userId]);
        
        // Log transaction
        $stmt = $db->prepare("
            INSERT INTO coin_transactions (user_id, amount, reason, reference_id)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $amount, $reason, $referenceId]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Coin award error: " . $e->getMessage());
        throw $e; // Re-throw so caller can handle
    }
}

/**
 * Check and unlock badges for user
 * FIXED: No longer manages transactions - caller must handle
 */
function checkAndUnlockBadges($userId, $currentStreak) {
    try {
        $db = getDB();
        $newBadges = [];
        
        // Get badges user doesn't have yet
        $stmt = $db->prepare("
            SELECT b.* 
            FROM badges b
            WHERE b.requirement_type = 'streak'
            AND b.requirement_value <= ?
            AND b.badge_id NOT IN (
                SELECT badge_id FROM user_badges WHERE user_id = ?
            )
            ORDER BY b.requirement_value ASC
        ");
        $stmt->execute([$currentStreak, $userId]);
        $eligibleBadges = $stmt->fetchAll();
        
        foreach ($eligibleBadges as $badge) {
            // Unlock badge (no nested transaction)
            $stmt = $db->prepare("
                INSERT INTO user_badges (user_id, badge_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $badge['badge_id']]);
            
            // Award badge coins (no nested transaction)
            if ($badge['coin_reward'] > 0) {
                awardCoins(
                    $userId, 
                    $badge['coin_reward'], 
                    'badge_unlock', 
                    $badge['badge_key']
                );
            }
            
            $newBadges[] = $badge;
        }
        
        return $newBadges;
        
    } catch (Exception $e) {
        error_log("checkAndUnlockBadges error: " . $e->getMessage());
        throw $e; // Re-throw so caller can handle
    }
}

/**
 * Update user's streak in database
 */
function updateUserStreak($userId, $newStreak) {
    try {
        $db = getDB();
        
        $stmt = $db->prepare("
            UPDATE users 
            SET current_streak = ?,
                best_streak = GREATEST(best_streak, ?)
            WHERE user_id = ?
        ");
        $stmt->execute([$newStreak, $newStreak, $userId]);
        return true;
        
    } catch (Exception $e) {
        error_log("updateUserStreak error: " . $e->getMessage());
        throw $e; // Re-throw so caller can handle
    }
}

/**
 * Send JSON response
 */
function sendJSON($data, $statusCode = 200) {
    ob_clean();
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
