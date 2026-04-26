<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$pdo = Database::getInstance();

try {
    $pdo->exec("ALTER TABLE faculty_profiles 
                ADD COLUMN employee_code VARCHAR(50) DEFAULT NULL AFTER user_id,
                ADD COLUMN designation VARCHAR(100) DEFAULT NULL AFTER employee_code,
                ADD COLUMN joining_date DATE DEFAULT NULL AFTER department,
                ADD COLUMN qualifications TEXT DEFAULT NULL AFTER joining_date,
                ADD COLUMN specialization VARCHAR(150) DEFAULT NULL AFTER qualifications");

    echo "faculty_profiles table updated successfully.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>