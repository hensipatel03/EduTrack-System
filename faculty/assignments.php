<?php
// faculty/assignments.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$facultyId = (int)$user['id'];

$uploadDir = __DIR__ . '/../uploads/assignments';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$error   = '';
$success = '';

// Delete assignment (and optionally file; submissions kept)
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT file_name FROM assignments
                           WHERE id = :id AND faculty_user_id = :fid");
    $stmt->execute([':id' => $id, ':fid' => $facultyId]);
    $row = $stmt->fetch();
    if ($row) {
        if ($row['file_name']) {
            $path = $uploadDir . '/' . $row['file_name'];
            if (is_file($path)) {
                @unlink($path);
            }
        }
        $del = $pdo->prepare("DELETE FROM assignments WHERE id = :id");
        $del->execute([':id' => $id]);
    }
    header('Location: assignments.php');
    exit;
}

// Assigned subjects
$sqlSub = "SELECT s.id, s.name, s.code
           FROM faculty_subjects fs
           JOIN subjects s ON s.id = fs.subject_id
           WHERE fs.faculty_user_id = :fid
           ORDER BY s.name";
$stmtSub = $pdo->prepare($sqlSub);
$stmtSub->execute([':fid' => $facultyId]);
$subjects = $stmtSub->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectId   = isset($_POST['subject_id']) ? (int)$_POST['subject_id'] : 0;
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $dueDate     = $_POST['due_date'] ?? '';

    if ($subjectId <= 0 || $title === '' || $dueDate === '') {
        $error = 'Subject, title, and due date are required.';
    } else {
        $storedName   = null;
        $originalName = null;

        if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $storedName   = uniqid('ass_', true) . '.' . $ext;
            $originalName = $file['name'];

            $dest = $uploadDir . '/' . $storedName;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $error = 'Failed to upload assignment file.';
            }
        }

        if ($error === '') {
            $sql = "INSERT INTO assignments
                    (faculty_user_id, subject_id, title, description, file_name, original_name, due_date)
                    VALUES
                    (:fid, :sid, :title, :description, :file_name, :original_name, :due_date)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':fid'          => $facultyId,
                ':sid'          => $subjectId,
                ':title'        => $title,
                ':description'  => $description !== '' ? $description : null,
                ':file_name'    => $storedName,
                ':original_name'=> $originalName,
                ':due_date'     => $dueDate,
            ]);
            $success = 'Assignment created successfully.';
        }
    }
}

$sqlList = "SELECT a.*, s.name AS subject_name, s.code AS subject_code
            FROM assignments a
            JOIN subjects s ON s.id = a.subject_id
            WHERE a.faculty_user_id = :fid
            ORDER BY a.created_at DESC";
$stmtList = $pdo->prepare($sqlList);
$stmtList->execute([':fid' => $facultyId]);
$assignments = $stmtList->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Assignments</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="mb-4">
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
        <label class="form-label">Description (optional)</label>
        <textarea name="description" class="form-control" rows="2"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Attachment (optional)</label>
        <input type="file" name="file" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Due Date</label>
        <input type="datetime-local" name="due_date" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Create Assignment</button>
</form>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Subject</th>
        <th>Title</th>
        <th>Due Date</th>
        <th>File</th>
        <th>Status</th>
        <th>Submissions</th>
        <th>Actions</th>
    </tr>
    </thead>
    </thead>
    <tbody>
    <?php if ($assignments): ?>
        <?php foreach ($assignments as $i => $a): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($a['subject_name'] . ' (' . $a['subject_code'] . ')'); ?></td>
                <td><?php echo htmlspecialchars($a['title']); ?></td>
                <td><?php echo htmlspecialchars($a['due_date']); ?></td>
                <td>
                    <?php if ($a['file_name']): ?>
                        <a href="<?php echo BASE_URL; ?>/uploads/assignments/<?php echo rawurlencode($a['file_name']); ?>" target="_blank">
                            <?php echo htmlspecialchars($a['original_name'] ?? $a['file_name']); ?>
                        </a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars(ucfirst($a['status'])); ?></td>
                <td>
                    <a href="<?php echo BASE_URL; ?>/faculty/submit_assignment.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                </td>
                <td>
                    <a href="assignments.php?action=delete&id=<?php echo $a['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this assignment?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="8" class="text-center">No assignments yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

