<?php
// Reset admin password
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = Database::getInstance();
    
    $newPassword = 'Admin@123';
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $pdo->prepare("UPDATE users SET password_hash = :hash WHERE username = 'admin'")
        ->execute([':hash' => $hashedPassword]);
    
    echo "✓ Admin password reset successfully\n";
    echo "Admin username: admin\n";
    echo "Admin password: Admin@123\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
