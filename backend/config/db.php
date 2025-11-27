<?php
/**
 * Database Connection - PRODUCTION READY
 */

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1'); // localhost
define('DB_NAME', getenv('DB_NAME') ?: 'quit_smoking_app'); // develope_quit_smoking_app
define('DB_USER', getenv('DB_USER') ?: 'root'); // develope_quit_smoking_app
define('DB_PASS', getenv('DB_PASS') ?: ''); // KhEt8wHJunwAsBeJfs2R
define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            
            // Return JSON error instead of throwing
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'error' => 'Database connection failed',
                'details' => $e->getMessage() // Remove in production
            ]);
            exit;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

function getDB() {
    try {
        return Database::getInstance()->getConnection();
    } catch (Exception $e) {
        error_log("getDB() failed: " . $e->getMessage());
        throw $e;
    }
}
?>
