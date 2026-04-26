<?php
// student/assignments.php
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

$sql = "SELECT a.*, s.name AS subject_name, u.full_name AS faculty_name,
               sub.id AS submission_id,
               sub.status AS submission_status,
               sub.submitted_at,
               sub.marks_obtained,
               sub.feedback
        FROM assignments a
        JOIN subjects s ON s.id = a.subject_id
        LEFT JOIN users u ON u.id = a.faculty_user_id
        LEFT JOIN assignment_submissions sub 
            ON sub.assignment_id = a.id AND sub.student_user_id = :sid
        WHERE s.course_id = :course_id 
        AND s.branch_id = :branch_id 
        AND s.semester_id = :semester_id
        ORDER BY a.due_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':sid' => $studentId,
    ':course_id' => $enrollment['course_id'],
    ':branch_id' => $enrollment['branch_id'],
    ':semester_id' => $enrollment['semester_id']
]);
$assignments = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Assignments</h3>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Subject</th>
        <th>File</th>
        <th>Title</th>
        <th>Due Date</th>
        <th>Status</th>
        <th>Submission</th>
        <th>Marks</th>
        <th>Feedback</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($assignments): ?>
        <?php foreach ($assignments as $i => $a): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($a['subject_name']); ?></td>
                <td>
                    <?php if ($a['file_name']): ?>
                        <a href="<?php echo BASE_URL; ?>/uploads/assignments/<?php echo rawurlencode($a['file_name']); ?>" target="_blank">
                            <?php echo htmlspecialchars($a['original_name'] ?? $a['file_name']); ?>
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($a['title']); ?></td>
                <td><?php echo htmlspecialchars($a['due_date']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($a['status'])); ?></td>
                <td>
                    <?php if ($a['submission_id']): ?>
                        <?php echo htmlspecialchars($a['submission_status']); ?>
                        <br><small><?php echo htmlspecialchars($a['submitted_at']); ?></small>
                    <?php else: ?>
                        Not submitted
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($a['submission_id'] && $a['marks_obtained'] !== null): ?>
                        <strong><?php echo htmlspecialchars((string)$a['marks_obtained']); ?></strong>
                    <?php elseif ($a['submission_id']): ?>
                        <span class="badge bg-warning">Pending</span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($a['submission_id'] && $a['feedback']): ?>
                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                data-bs-target="#feedbackModal<?php echo $a['submission_id']; ?>">
                            View
                        </button>
                        <!-- Feedback Modal -->
                        <div class="modal fade" id="feedbackModal<?php echo $a['submission_id']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Feedback - <?php echo htmlspecialchars($a['title']); ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p><?php echo nl2br(htmlspecialchars($a['feedback'])); ?></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($a['submission_id']): ?>
                        <span class="text-muted">-</span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td style="white-space: nowrap;">
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="submit_assignment.php?id=<?php echo $a['id']; ?>" class="btn btn-success" style="font-size: 0.8rem;">
                            <?php echo $a['submission_id'] ? 'Resubmit' : 'Submit'; ?>
                        </a>
                        <?php if ($a['submission_id']): ?>
                            <a href="view_submission.php?sid=<?php echo $a['submission_id']; ?>" class="btn btn-info" style="font-size: 0.8rem;">
                                View Details
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="9" class="text-center">No assignments.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
