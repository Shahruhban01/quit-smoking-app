<?php
/**
 * GET /api/coins/history
 * Get coin transaction history
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/jwt_helper.php';
require_once __DIR__ . '/../includes/functions.php';

$auth = JWTHelper::requireAuth();
$userId = $auth['user_id'];

$limit = (int)($_GET['limit'] ?? 50);
$limit = min($limit, 100); // Max 100

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT 
            transaction_id,
            amount,
            reason,
            reference_id,
            created_at
        FROM coin_transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$userId, $limit]);
    $transactions = $stmt->fetchAll();
    
    sendJSON([
        'transactions' => $transactions
    ]);
    
} catch (Exception $e) {
    error_log("Coin history error: " . $e->getMessage());
    sendJSON(['error' => 'Failed to load coin history'], 500);
}
?>
