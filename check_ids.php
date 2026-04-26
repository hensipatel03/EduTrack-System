<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$pdo = Database::getInstance();

try {
    echo "Faculty users:\n";
    $stmt = $pdo->query("SELECT u.username, u.email, fp.employee_code FROM users u JOIN faculty_profiles fp ON fp.user_id = u.id WHERE u.role_id = 2");
    while ($row = $stmt->fetch()) {
        echo "- Username: {$row['username']}, Email: {$row['email']}, Employee ID: {$row['employee_code']}\n";
    }

    echo "\nStudent users:\n";
    $stmt = $pdo->query("SELECT u.username, u.email, sp.enrollment_no FROM users u JOIN student_profiles sp ON sp.user_id = u.id WHERE u.role_id = 3");
    while ($row = $stmt->fetch()) {
        echo "- Username: {$row['username']}, Email: {$row['email']}, Enrollment ID: {$row['enrollment_no']}\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>