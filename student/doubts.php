<?php
// student/doubts.php
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

$error   = '';
$success = '';

// Subjects list - only for student's course, branch, and semester
$sqlSub = "SELECT DISTINCT s.id, s.name, s.code
           FROM subjects s
           WHERE s.course_id = :course_id 
           AND s.branch_id = :branch_id 
           AND s.semester_id = :semester_id
           ORDER BY s.name";
$stmtSub = $pdo->prepare($sqlSub);
$stmtSub->execute([
    ':course_id' => $enrollment['course_id'],
    ':branch_id' => $enrollment['branch_id'],
    ':semester_id' => $enrollment['semester_id']
]);
$subjects = $stmtSub->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
    $title     = trim($_POST['title'] ?? '');
    $question  = trim($_POST['question'] ?? '');

    if ($subjectId <= 0 || $title === '' || $question === '') {
        $error = 'Subject, title, and question are required.';
    } else {
        $sql = "INSERT INTO doubts (student_user_id, subject_id, title, question)
                VALUES (:sid, :subid, :title, :question)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sid'     => $studentId,
            ':subid'   => $subjectId,
            ':title'   => $title,
            ':question'=> $question,
        ]);
        $success = 'Doubt submitted.';
    }
}

$sql = "SELECT d.*, s.name AS subject_name
        FROM doubts d
        JOIN subjects s ON s.id = d.subject_id
        WHERE d.student_user_id = :sid
        ORDER BY d.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':sid' => $studentId]);
$doubts = $stmt->fetchAll();

// Replies per doubt
$replySql = "SELECT dr.*, u.first_name, u.last_name
             FROM doubt_replies dr
             JOIN users u ON u.id = dr.user_id
             WHERE dr.doubt_id = :did
             ORDER BY dr.created_at ASC";
$stmtReply = $pdo->prepare($replySql);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">My Doubts</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">Ask a New Doubt</div>
    <div class="card-body">
        <form method="post">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Subject</label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">-- Select Subject --</option>
                        <?php foreach ($subjects as $s): ?>
                            <option value="<?php echo $s['id']; ?>">
                                <?php echo htmlspecialchars($s['name'] . ' (' . $s['code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Question</label>
                <textarea name="question" class="form-control" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-success">Submit Doubt</button>
        </form>
    </div>
</div>

<?php if ($doubts): ?>
    <?php foreach ($doubts as $d): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <div>
                    <strong><?php echo htmlspecialchars($d['title']); ?></strong>
                    <div class="small text-muted">
                        Subject: <?php echo htmlspecialchars($d['subject_name']); ?> |
                        <?php echo htmlspecialchars($d['created_at']); ?>
                    </div>
                </div>
                <span class="badge bg-<?php echo $d['status'] === 'resolved' ? 'success' : 'warning'; ?>">
                    <?php echo htmlspecialchars(ucfirst($d['status'])); ?>
                </span>
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($d['question'])); ?></p>
                <hr>
                <h6>Replies</h6>
                <div style="max-height: 200px; overflow-y:auto;">
                    <?php
                    $stmtReply->execute([':did' => $d['id']]);
                    $replies = $stmtReply->fetchAll();
                    if ($replies):
                        foreach ($replies as $r):
                            ?>
                            <div class="mb-2">
                                <strong><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></strong>
                                <span class="text-muted small"><?php echo htmlspecialchars($r['created_at']); ?></span>
                                <div><?php echo nl2br(htmlspecialchars($r['message'])); ?></div>
                            </div>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <div class="text-muted">No replies yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-info">You have not asked any doubts yet.</div>
<?php endif; ?>
