<?php
/**
 * JWT Helper for Authentication
 * Stateless token-based authentication
 */

// Use Firebase JWT library (install via: composer require firebase/php-jwt)
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

define('JWT_SECRET', 'your-secret-key-change-this-in-production-use-env-file');
define('JWT_ALGORITHM', 'HS256');
define('JWT_EXPIRATION', 86400 * 7); // 7 days

class JWTHelper {
    
    /**
     * Generate JWT token for user
     */
    public static function encode($userId, $username) {
        $issuedAt = time();
        $expire = $issuedAt + JWT_EXPIRATION;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => $_SERVER['HTTP_HOST'] ?? 'quit-smoking-app',
            'user_id' => $userId,
            'username' => $username
        ];

        return JWT::encode($payload, JWT_SECRET, JWT_ALGORITHM);
    }

    /**
     * Decode and validate JWT token
     * Returns user data or false on failure
     */
    public static function decode($token) {
        try {
            $decoded = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGORITHM));
            return [
                'user_id' => $decoded->user_id,
                'username' => $decoded->username
            ];
        } catch (Exception $e) {
            error_log("JWT Decode Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract token from Authorization header
     */
    public static function getBearerToken() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $matches = [];
            if (preg_match('/Bearer\s+(.+)/', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }

    /**
     * Middleware: Require valid JWT or send 401
     */
    public static function requireAuth() {
        $token = self::getBearerToken();
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'No token provided']);
            exit;
        }

        $userData = self::decode($token);
        
        if (!$userData) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        return $userData;
    }
}
?>
