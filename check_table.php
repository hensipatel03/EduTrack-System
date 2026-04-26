<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$pdo = Database::getInstance();

try {
    $stmt = $pdo->query("DESCRIBE student_profiles");
    $columns = $stmt->fetchAll();
    echo "student_profiles columns:\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . "\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>