<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$pdo = Database::getInstance();

try {
    // Update existing users to 'active' status
    $stmt = $pdo->prepare("UPDATE users SET status = 'active'");
    $stmt->execute();
    echo "All existing users updated to 'active' status.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>