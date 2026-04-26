<?php
// student/view_submission.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin('student');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$studentId = (int)$user['id'];

// Get submission ID from URL
$submissionId = isset($_GET['sid']) ? (int)$_GET['sid'] : 0;
if ($submissionId === 0) {
    die('Invalid submission ID.');
}

// Get submission details with proper access control
$sql = "SELECT sub.*, a.title AS assignment_title, s.name AS subject_name, a.max_marks
        FROM assignment_submissions sub
        JOIN assignments a ON a.id = sub.assignment_id
        JOIN subjects s ON s.id = a.subject_id
        WHERE sub.id = :sid AND sub.student_user_id = :student_id
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':sid' => $submissionId, ':student_id' => $studentId]);
$submission = $stmt->fetch();

if (!$submission) {
    die('Submission not found or access denied.');
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h3 class="mb-3"><?php echo htmlspecialchars($submission['assignment_title']); ?></h3>
            <p class="text-muted">Subject: <?php echo htmlspecialchars($submission['subject_name']); ?></p>

            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Submission Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Submitted At:</label>
                            <p><?php echo htmlspecialchars($submission['submitted_at']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status:</label>
                            <p>
                                <?php if ($submission['status'] === 'submitted'): ?>
                                    <span class="badge bg-warning">Submitted</span>
                                <?php elseif ($submission['status'] === 'evaluated'): ?>
                                    <span class="badge bg-success">Evaluated</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><?php echo htmlspecialchars(ucfirst($submission['status'])); ?></span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Submission File:</label>
                            <p>
                                <a href="<?php echo BASE_URL; ?>/uploads/submissions/<?php echo rawurlencode($submission['file_name']); ?>" 
                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                    Download: <?php echo htmlspecialchars($submission['original_name'] ?? $submission['file_name']); ?>
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($submission['status'] === 'evaluated'): ?>
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Marks & Feedback</h5>
                </div>
                <div class="card-body">
                    <?php if ($submission['marks_obtained'] !== null): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Marks Obtained:</label>
                        <div class="alert alert-info" role="alert">
                            <h5 class="alert-heading">
                                <?php echo htmlspecialchars((string)$submission['marks_obtained']); ?>
                                <?php if ($submission['max_marks']): ?>
                                    / <?php echo htmlspecialchars((string)$submission['max_marks']); ?>
                                <?php endif; ?>
                                <span class="float-end">
                                    <?php 
                                    $percentage = $submission['max_marks'] ? ($submission['marks_obtained'] / $submission['max_marks']) * 100 : 0;
                                    echo round($percentage, 2) . '%'; 
                                    ?>
                                </span>
                            </h5>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($submission['feedback']): ?>
                    <div>
                        <label class="form-label fw-bold">Feedback from Faculty:</label>
                        <div class="alert alert-light border" role="alert">
                            <?php echo nl2br(htmlspecialchars($submission['feedback'])); ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No feedback provided yet.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning" role="alert">
                <strong>Awaiting Evaluation</strong><br>
                Your submission has been received but has not been evaluated by the faculty yet.
            </div>
            <?php endif; ?>

            <div class="mt-3">
                <a href="assignments.php" class="btn btn-secondary">Back to Assignments</a>
            </div>
        </div>
    </div>
</div>


