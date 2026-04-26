<?php
// student/index.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('student');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$studentId = (int)$user['id'];

// Get student's enrollment details
$enrollment = getStudentEnrollment($pdo, $studentId);
if (!$enrollment) {
    die('Student enrollment not found.');
}

// Upcoming assignments (next 7 days or active)
$sqlAssignments = "SELECT a.*, s.name AS subject_name
                   FROM assignments a
                   JOIN subjects s ON s.id = a.subject_id
                   WHERE s.course_id = :course_id 
                   AND s.branch_id = :branch_id 
                   AND s.semester_id = :semester_id
                   ORDER BY a.due_date ASC
                   LIMIT 5";
$stmtAssignments = $pdo->prepare($sqlAssignments);
$stmtAssignments->execute([
    ':course_id' => $enrollment['course_id'],
    ':branch_id' => $enrollment['branch_id'],
    ':semester_id' => $enrollment['semester_id']
]);
$upcoming = $stmtAssignments->fetchAll();

// Planner stats
$sqlStats = "SELECT 
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
                COUNT(*) AS total
             FROM student_topic_progress
             WHERE student_user_id = :sid";
$stmtStats = $pdo->prepare($sqlStats);
$stmtStats->execute([':sid' => $studentId]);
$stats = $stmtStats->fetch();
$total      = (int)($stats['total'] ?? 0);
$completed  = (int)($stats['completed'] ?? 0);
$progressPc = $total > 0 ? round(($completed / $total) * 100) : 0;

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Dashboard</h3>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5 class="card-title">Study Progress</h5>
                <p class="card-text mb-1"><?php echo $progressPc; ?>% Completed</p>
                <div class="progress">
                    <div class="progress-bar bg-dark"
                         style="width: <?php echo $progressPc; ?>%;"></div>
                </div>
                <small class="text-light">
                    <?php echo $completed; ?> of <?php echo $total; ?> topics completed
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Upcoming Assignments
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Title</th>
                        <th>Due</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($upcoming): ?>
                        <?php foreach ($upcoming as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a['subject_name']); ?></td>
                                <td><?php echo htmlspecialchars($a['title']); ?></td>
                                <td><?php echo htmlspecialchars($a['due_date']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center">No assignments scheduled.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

