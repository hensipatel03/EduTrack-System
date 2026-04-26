<?php
// includes/functions.php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config.php';

function redirectToRoleHome(): void
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: ' . BASE_URL . '/admin/index.php');
            break;
        case 'faculty':
            header('Location: ' . BASE_URL . '/faculty/index.php');
            break;
        case 'student':
            header('Location: ' . BASE_URL . '/student/index.php');
            break;
        default:
            header('Location: ' . BASE_URL . '/login.php');
            break;
    }
    exit;
}

function generateEmployeeId(PDO $pdo): string
{
    // Get the maximum employee ID as number from users.username where role is faculty
    $stmt = $pdo->prepare("SELECT MAX(CAST(u.username AS UNSIGNED)) as max_id 
                          FROM users u 
                          JOIN roles r ON r.id = u.role_id 
                          WHERE r.name = 'faculty' AND u.username REGEXP '^[0-9]+$'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    $nextId = ($result['max_id'] ?? 1000) + 1;
    return (string)$nextId;
}

function generateEnrollmentId(PDO $pdo): string
{
    // Get the maximum enrollment ID as number
    $stmt = $pdo->prepare("SELECT MAX(CAST(enrollment_no AS UNSIGNED)) as max_id 
                          FROM student_profiles 
                          WHERE enrollment_no REGEXP '^[0-9]+$'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    $nextId = ($result['max_id'] ?? 2000) + 1;
    return (string)$nextId;
}

/**
 * Get student's enrollment details (course_id, branch_id, semester_id)
 * @param PDO $pdo Database connection
 * @param int $userId User ID of the student
 * @return array|null Enrollment details or null if not found
 */
function getStudentEnrollment(PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("SELECT course_id, branch_id, semester_id 
                          FROM student_profiles 
                          WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $userId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ?: null;
}