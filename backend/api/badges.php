<?php
/**
 * GET /api/badges
 * Get all badges (locked and unlocked)
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/jwt_helper.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = JWTHelper::requireAuth();
$userId = $auth['user_id'];

try {
    $db = getDB();
    
    // Get all badges with unlock status
    $stmt = $db->prepare("
        SELECT 
            b.*,
            ub.unlocked_at,
            CASE WHEN ub.user_badge_id IS NOT NULL THEN 1 ELSE 0 END as unlocked
        FROM badges b
        LEFT JOIN user_badges ub ON b.badge_id = ub.badge_id AND ub.user_id = ?
        ORDER BY b.sort_order ASC
    ");
    $stmt->execute([$userId]);
    $badges = $stmt->fetchAll();
    
    sendJSON([
        'badges' => $badges
    ]);
    
} catch (Exception $e) {
    error_log("Badges fetch error: " . $e->getMessage());
    sendJSON(['error' => 'Failed to load badges'], 500);
}
?>
