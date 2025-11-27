<?php
header('Content-Type: text/plain');

echo "Testing MariaDB connection...\n\n";

$host = '127.0.0.1';
$db = 'quit_smoking_app';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "✓ SUCCESS! Connected to database.\n";
    echo "✓ Database: $db\n";
    echo "✓ User: $user\n";
    echo "✓ Password: " . (empty($pass) ? 'EMPTY' : 'SET') . "\n";
} catch (PDOException $e) {
    echo "✗ FAILED! Error: " . $e->getMessage() . "\n";
    echo "  Host: $host\n";
    echo "  Database: $db\n";
    echo "  User: $user\n";
}
?>
