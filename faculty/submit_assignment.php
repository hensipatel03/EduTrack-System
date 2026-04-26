<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$facultyId = (int)$user['id'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id === 0) {
    die('Invalid assignment ID.');
}

// Ensure assignment belongs to faculty
$sql = "SELECT a.*, s.name AS subject_name
        FROM assignments a
        JOIN subjects s ON s.id = a.subject_id
        WHERE a.id = :id AND a.faculty_user_id = :fid
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id, ':fid' => $facultyId]);
$assignment = $stmt->fetch();
if (!$assignment) {
    die('Assignment not found or access denied.');
}
$assignmentId = $id;


$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['marks_obtained']) && is_array($_POST['marks_obtained'])) {
        foreach ($_POST['marks_obtained'] as $subId => $marks) {
            $subId = (int)$subId;
            $marks = $marks !== '' ? (float)$marks : null;
            $feedback = trim($_POST['feedback'][$subId] ?? '');

            if ($subId > 0) {
                try {
                    $sqlUp = "UPDATE assignment_submissions
                              SET marks_obtained = :marks,
                                  feedback       = :feedback,
                                  status         = 'evaluated'
                              WHERE id = :id AND assignment_id = :aid";
                    $stmtUp = $pdo->prepare($sqlUp);
                    $stmtUp->execute([
                        ':marks'    => $marks,
                        ':feedback' => $feedback !== '' ? $feedback : null,
                        ':id'       => $subId,
                        ':aid'      => $assignmentId,
                    ]);
                } catch (Throwable $e) {
                    $error = 'Error updating submission: ' . $e->getMessage();
                }
            }
        }
        if (!isset($error)) {
            $success = 'Marks and feedback updated successfully.';
        }
    }
}

$sqlSub = "SELECT sub.*, u.first_name, u.last_name, sp.enrollment_no
           FROM assignment_submissions sub
           JOIN users u ON u.id = sub.student_user_id
           JOIN student_profiles sp ON sp.user_id = u.id
           WHERE sub.assignment_id = :aid
           ORDER BY sub.submitted_at";
$stmtSub = $pdo->prepare($sqlSub);
$stmtSub->execute([':aid' => $assignmentId]);
$submissions = $stmtSub->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-2">Submissions - <?php echo htmlspecialchars($assignment['title']); ?></h3>
<p class="text-muted">
    Subject: <?php echo htmlspecialchars($assignment['subject_name']); ?>
</p>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Enrollment</th>
        <th>Student</th>
        <th>Submitted At</th>
        <th>File</th>
        <th>Marks</th>
        <th>Feedback</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($submissions): ?>
        <?php foreach ($submissions as $i => $s): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($s['enrollment_no']); ?></td>
                <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                <td><?php echo htmlspecialchars($s['submitted_at']); ?></td>
                <td>
                    <a href="<?php echo BASE_URL; ?>/uploads/submissions/<?php echo rawurlencode($s['file_name']); ?>" target="_blank">
                        <?php echo htmlspecialchars($s['original_name'] ?? $s['file_name']); ?>
                    </a>
                </td>
                <td style="width: 90px;">
                    <input type="number" step="0.01" name="marks_obtained[<?php echo $s['id']; ?>]"
                           value="<?php echo htmlspecialchars((string)$s['marks_obtained']); ?>"
                           class="form-control form-control-sm">
                </td>
                <td style="width: 200px;">
                    <textarea name="feedback[<?php echo $s['id']; ?>]" class="form-control form-control-sm" rows="1"><?php
                        echo htmlspecialchars($s['feedback'] ?? '');
                    ?></textarea>
                </td>
                <td style="width: 90px;">
                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="8" class="text-center">No submissions yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</form>

