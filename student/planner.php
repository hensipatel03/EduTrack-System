<?php
// student/planner.php
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

$subjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topicId    = isset($_POST['topic_id']) ? (int)$_POST['topic_id'] : 0;
    $status     = $_POST['status'] ?? 'not_started';
    $targetDate = $_POST['target_date'] ?? '';
    $strength   = $_POST['strength'] ?? null;

    if ($topicId > 0) {
        $sql = "INSERT INTO student_topic_progress
                (student_user_id, topic_id, target_date, status, strength)
                VALUES (:sid, :tid, :target_date, :status, :strength)
                ON DUPLICATE KEY UPDATE
                    target_date = VALUES(target_date),
                    status      = VALUES(status),
                    strength    = VALUES(strength)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sid'         => $studentId,
            ':tid'         => $topicId,
            ':target_date' => $targetDate !== '' ? $targetDate : null,
            ':status'      => $status,
            ':strength'    => $strength !== '' ? $strength : null,
        ]);
    }

    $subjectId = isset($_POST['current_subject_id']) ? (int)$_POST['current_subject_id'] : $subjectId;
}

$sqlSubjects = "SELECT DISTINCT s.id, s.name, s.code
                FROM subjects s
                WHERE s.course_id = :course_id 
                AND s.branch_id = :branch_id 
                AND s.semester_id = :semester_id
                ORDER BY s.name";
$stmtSubjects = $pdo->prepare($sqlSubjects);
$stmtSubjects->execute([
    ':course_id' => $enrollment['course_id'],
    ':branch_id' => $enrollment['branch_id'],
    ':semester_id' => $enrollment['semester_id']
]);
$subjects = $stmtSubjects->fetchAll();

$topics = [];
if ($subjectId > 0) {
    $sqlTopics = "SELECT t.*, p.status, p.target_date, p.strength
                  FROM topics t
                  LEFT JOIN student_topic_progress p 
                    ON p.topic_id = t.id AND p.student_user_id = :sid
                  WHERE t.subject_id = :subid
                  ORDER BY t.order_no, t.id";
    $stmtT = $pdo->prepare($sqlTopics);
    $stmtT->execute([':sid' => $studentId, ':subid' => $subjectId]);
    $topics = $stmtT->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Study Planner</h3>

<form method="get" class="mb-3">
    <label class="form-label">Subject</label>
    <select name="subject_id" class="form-select" onchange="this.form.submit()">
        <option value="">-- Select Subject --</option>
        <?php foreach ($subjects as $s): ?>
            <option value="<?php echo $s['id']; ?>"
                <?php echo (string)$subjectId === (string)$s['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($s['name'] . ' (' . $s['code'] . ')'); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($subjectId && $topics): ?>
    <table class="table table-bordered table-sm align-middle">
        <thead class="table-light">
        <tr>
            <th>Sr No</th>
            <th>Topic</th>
            <th>Target Date</th>
            <th>Status</th>
            <th>Strength</th>
            <th>Save</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($topics as $i => $t): ?>
            <tr>
                <form method="post">
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                    <td style="width: 160px;">
                        <input type="date" name="target_date" class="form-control form-control-sm"
                               value="<?php echo htmlspecialchars($t['target_date'] ?? ''); ?>">
                    </td>
                    <td style="width: 160px;">
                        <select name="status" class="form-select form-select-sm">
                            <option value="not_started" <?php echo ($t['status'] ?? 'not_started') === 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                            <option value="in_progress" <?php echo ($t['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo ($t['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </td>
                    <td style="width: 160px;">
                        <select name="strength" class="form-select form-select-sm">
                            <option value="">-</option>
                            <option value="weak" <?php echo ($t['strength'] ?? '') === 'weak' ? 'selected' : ''; ?>>Weak</option>
                            <option value="average" <?php echo ($t['strength'] ?? '') === 'average' ? 'selected' : ''; ?>>Average</option>
                            <option value="strong" <?php echo ($t['strength'] ?? '') === 'strong' ? 'selected' : ''; ?>>Strong</option>
                        </select>
                    </td>
                    <td style="width: 80px;">
                        <input type="hidden" name="topic_id" value="<?php echo $t['id']; ?>">
                        <input type="hidden" name="current_subject_id" value="<?php echo $subjectId; ?>">
                        <button type="submit" class="btn btn-sm btn-success">Save</button>
                    </td>
                </form>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif ($subjectId): ?>
    <div class="alert alert-info">No topics defined for this subject.</div>
<?php endif; ?>
