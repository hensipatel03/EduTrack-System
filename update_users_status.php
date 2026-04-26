<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$pdo = Database::getInstance();

try {
    $pdo->exec("ALTER TABLE `users` MODIFY COLUMN `status` ENUM('active','inactive','pending') DEFAULT 'pending'");
    echo "Users table status enum updated successfully.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>