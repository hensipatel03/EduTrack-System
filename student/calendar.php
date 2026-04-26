<?php
// student/calendar.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('student');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$studentId = (int)$user['id'];

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate   = date('Y-m-t', strtotime($startDate));

$sql = "SELECT * FROM calendar_events
        WHERE (user_id IS NULL OR user_id = :uid)
          AND start_date BETWEEN :start AND :end
        ORDER BY start_date";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':uid'   => $studentId,
    ':start' => $startDate,
    ':end'   => $endDate,
]);
$events = $stmt->fetchAll();

// Also get assignments
$sqlAssignments = "SELECT a.id, a.title, a.description, a.due_date AS start_date, 'assignment' AS type, s.name AS subject_name
                   FROM assignments a
                   JOIN subjects s ON s.id = a.subject_id
                   WHERE a.due_date BETWEEN :start AND :end
                   ORDER BY a.due_date";
$stmtA = $pdo->prepare($sqlAssignments);
$enrollment = getStudentEnrollment($pdo, $studentId);

if ($enrollment) {
    // Filter assignments by student's course/branch/semester
    $sqlAssignments = "SELECT a.id, a.title, a.description, a.due_date AS start_date, 'assignment' AS type, s.name AS subject_name
                   FROM assignments a
                   JOIN subjects s ON s.id = a.subject_id
                   WHERE s.course_id = :course_id AND s.branch_id = :branch_id AND s.semester_id = :semester_id
                     AND a.due_date BETWEEN :start AND :end
                   ORDER BY a.due_date";
    $stmtA = $pdo->prepare($sqlAssignments);
    $stmtA->execute([
        ':course_id' => $enrollment['course_id'],
        ':branch_id' => $enrollment['branch_id'],
        ':semester_id' => $enrollment['semester_id'],
        ':start' => $startDate,
        ':end' => $endDate,
    ]);
    $assignments = $stmtA->fetchAll();
} else {
    // Fallback: no enrollment info, keep original (show all assignments in date range)
    $stmtA->execute([':start' => $startDate, ':end' => $endDate]);
    $assignments = $stmtA->fetchAll();
}

// Get planner items
$planner = [];
if ($enrollment) {
    $sqlPlanner = "SELECT p.id, t.title, p.target_date AS start_date, 'planner' AS type, s.name AS subject_name, p.status
               FROM student_topic_progress p
               JOIN topics t ON t.id = p.topic_id
               JOIN subjects s ON s.id = t.subject_id
               WHERE p.student_user_id = :uid
                 AND s.course_id = :course_id AND s.branch_id = :branch_id AND s.semester_id = :semester_id
                 AND p.target_date BETWEEN :start AND :end
               ORDER BY p.target_date";
    $stmtP = $pdo->prepare($sqlPlanner);
    $stmtP->execute([
        ':uid' => $studentId,
        ':course_id' => $enrollment['course_id'],
        ':branch_id' => $enrollment['branch_id'],
        ':semester_id' => $enrollment['semester_id'],
        ':start' => $startDate,
        ':end' => $endDate,
    ]);
    $planner = $stmtP->fetchAll();
} else {
    // Fallback: no enrollment info, keep original planner query
    $sqlPlanner = "SELECT p.id, t.title, p.target_date AS start_date, 'planner' AS type, s.name AS subject_name, p.status
               FROM student_topic_progress p
               JOIN topics t ON t.id = p.topic_id
               JOIN subjects s ON s.id = t.subject_id
               WHERE p.student_user_id = :uid AND p.target_date BETWEEN :start AND :end
               ORDER BY p.target_date";
    $stmtP = $pdo->prepare($sqlPlanner);
    $stmtP->execute([':uid' => $studentId, ':start' => $startDate, ':end' => $endDate]);
    $planner = $stmtP->fetchAll();
}

// Combine all
$allEvents = array_merge($events, $assignments, $planner);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Calendar (<?php echo $month . '/' . $year; ?>)</h3>

<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="month" class="form-select">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo $m === $month ? 'selected' : ''; ?>>
                    <?php echo $m; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="year" class="form-select">
            <?php for ($y = $year - 1; $y <= $year + 1; $y++): ?>
                <option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Go</button>
    </div>
</form>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Date</th>
        <th>Title</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($allEvents): ?>
        <?php foreach ($allEvents as $e): ?>
            <tr>
                <td><?php echo htmlspecialchars($e['start_date']); ?></td>
                <td><?php echo htmlspecialchars($e['title']); ?></td>
                <td><?php echo htmlspecialchars($e['type']); ?></td>
                <td><?php echo htmlspecialchars($e['description'] ?? $e['subject_name'] ?? ''); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4" class="text-center">No events this month.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

