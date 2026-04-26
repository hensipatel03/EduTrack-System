<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getInstance();

echo "Next Employee ID: " . generateEmployeeId($pdo) . "\n";
echo "Next Enrollment ID: " . generateEnrollmentId($pdo) . "\n";
?>