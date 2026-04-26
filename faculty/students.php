<?php
// faculty/students.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$facultyId = (int)$user['id'];

// Accept either `subject_id` or `id` (some links use `id`) for compatibility
$subjectId = 0;
if (isset($_GET['subject_id'])) {
    $subjectId = (int)$_GET['subject_id'];
} elseif (isset($_GET['id'])) {
    $subjectId = (int)$_GET['id'];
}
if ($subjectId <= 0) {
    die('Invalid subject.');
}

// Ensure subject belongs to this faculty
$sql = "SELECT s.*, c.name AS course_name, b.name AS branch_name, sem.name AS semester_name
        FROM faculty_subjects fs
        JOIN subjects s ON s.id = fs.subject_id
        JOIN courses c ON c.id = s.course_id
        JOIN branches b ON b.id = s.branch_id
        JOIN semesters sem ON sem.id = s.semester_id
        WHERE fs.faculty_user_id = :fid AND s.id = :sid
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':fid' => $facultyId, ':sid' => $subjectId]);
$subject = $stmt->fetch();

if (!$subject) {
    die('You are not assigned to this subject.');
}

$sqlStudents = "SELECT u.*, sp.enrollment_no
                FROM users u
                JOIN roles r ON r.id = u.role_id
                JOIN student_profiles sp ON sp.user_id = u.id
                WHERE r.name = 'student'
                  AND sp.course_id   = :cid
                  AND sp.branch_id   = :bid
                  AND sp.semester_id = :semid
                ORDER BY sp.enrollment_no";
$stmtStu = $pdo->prepare($sqlStudents);
$stmtStu->execute([
    ':cid'   => $subject['course_id'],
    ':bid'   => $subject['branch_id'],
    ':semid' => $subject['semester_id'],
]);
$students = $stmtStu->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-2">Students - <?php echo htmlspecialchars($subject['name']); ?></h3>
<p class="text-muted">
    <?php echo htmlspecialchars($subject['course_name'] . ' / ' . $subject['branch_name'] . ' / ' . $subject['semester_name']); ?>
</p>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Enrollment</th>
        <th>Name</th>
        <th>Email</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($students): ?>
        <?php foreach ($students as $i => $st): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($st['enrollment_no']); ?></td>
                <td><?php echo htmlspecialchars($st['first_name'] . ' ' . $st['last_name']); ?></td>
                <td><?php echo htmlspecialchars($st['email']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4" class="text-center">No students found for this subject.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

