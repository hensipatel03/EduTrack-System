<?php
// student/submit_assignment.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('student');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$studentId = (int)$user['id'];

$uploadDir = __DIR__ . '/../uploads/submissions';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$assignmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($assignmentId <= 0) {
    // Redirect back to assignments list if no specific assignment selected
    header('Location: ' . BASE_URL . '/student/assignments.php');
    exit;
}

$sql = "SELECT a.*, s.name AS subject_name
        FROM assignments a
        JOIN subjects s ON s.id = a.subject_id
        WHERE a.id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $assignmentId]);
$assignment = $stmt->fetch();
if (!$assignment) {
    die('Assignment not found.');
}

$error   = '';
$success = '';

// Check if the assignment submission is closed (past due date)
$is_closed = false;
if (!empty($assignment['due_date'])) {
    $due_ts = strtotime($assignment['due_date']);
    if ($due_ts !== false && time() > $due_ts) {
        $is_closed = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_closed) {
        $error = 'Submition Close.';
    } else {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please choose a file.';
        } else {
        $file = $_FILES['file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $storedName   = uniqid('sub_', true) . '.' . $ext;
        $dest = $uploadDir . '/' . $storedName;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $sqlUp = "INSERT INTO assignment_submissions
                      (assignment_id, student_user_id, file_name, original_name, status)
                      VALUES (:aid, :sid, :file_name, :original_name, 'submitted')
                      ON DUPLICATE KEY UPDATE
                        file_name    = VALUES(file_name),
                        original_name= VALUES(original_name),
                        submitted_at = NOW(),
                        status       = 'submitted'";
            $stmtUp = $pdo->prepare($sqlUp);
            $stmtUp->execute([
                ':aid'         => $assignmentId,
                ':sid'         => $studentId,
                ':file_name'   => $storedName,
                ':original_name'=> $file['name'],
            ]);
            $success = 'Assignment submitted successfully.';
        } else {
            $error = 'Failed to upload file.';
        }
    }
}

}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Submit Assignment</h3>

<div class="card mb-3">
    <div class="card-body">
        <h5><?php echo htmlspecialchars($assignment['title']); ?></h5>
        <p class="mb-1"><strong>Subject:</strong> <?php echo htmlspecialchars($assignment['subject_name']); ?></p>
        <p class="mb-1"><strong>Due:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($is_closed): ?>
    <div class="alert alert-warning">Submition Close.</div>
    <a href="assignments.php" class="btn btn-secondary">Back</a>
<?php else: ?>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Choose File</label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Submit</button>
        <a href="assignments.php" class="btn btn-secondary ms-2">Back</a>
    </form>
<?php endif; ?>
