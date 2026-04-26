<?php
// update_existing_users.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$pdo = Database::getInstance();

try {
    $pdo->beginTransaction();

    // Update existing faculty
    $stmt = $pdo->query("SELECT u.id, fp.id as profile_id FROM users u JOIN faculty_profiles fp ON fp.user_id = u.id WHERE fp.employee_code IS NULL");
    $faculty = $stmt->fetchAll();

    foreach ($faculty as $fac) {
        $employeeId = generateEmployeeId($pdo);
        $pdo->prepare("UPDATE faculty_profiles SET employee_code = :code WHERE id = :id")
             ->execute([':code' => $employeeId, ':id' => $fac['profile_id']]);
        $pdo->prepare("UPDATE users SET username = :username WHERE id = :id")
             ->execute([':username' => $employeeId, ':id' => $fac['id']]);
    }

    // Update existing students
    $stmt = $pdo->query("SELECT u.id, sp.enrollment_no FROM users u JOIN student_profiles sp ON sp.user_id = u.id WHERE u.username != sp.enrollment_no");
    $students = $stmt->fetchAll();

    foreach ($students as $stu) {
        $pdo->prepare("UPDATE users SET username = :username WHERE id = :id")
             ->execute([':username' => $stu['enrollment_no'], ':id' => $stu['id']]);
    }

    $pdo->commit();
    echo "Existing users updated successfully.\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
?>