<?php
// faculty/materials.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$facultyId = (int)$user['id'];

$uploadDir = __DIR__ . '/../uploads/materials'; // adjust if your root differs

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$error   = '';
$success = '';

if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT file_name FROM study_materials 
                           WHERE id = :id AND faculty_user_id = :fid");
    $stmt->execute([':id' => $id, ':fid' => $facultyId]);
    $row = $stmt->fetch();
    if ($row) {
        $filePath = $uploadDir . '/' . $row['file_name'];
        if (is_file($filePath)) {
            @unlink($filePath);
        }
        $del = $pdo->prepare("DELETE FROM study_materials WHERE id = :id");
        $del->execute([':id' => $id]);
    }

    header('Location: materials.php');
    exit;
}

// Load assigned subjects
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

    if ($subjectId <= 0 || $title === '') {
        $error = 'Subject and title are required.';
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please upload a valid file.';
    } else {
        $file = $_FILES['file'];
        $allowedExt = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $error = 'Invalid file type. Allowed: ' . implode(', ', $allowedExt);
        } else {
            $storedName = uniqid('mat_', true) . '.' . $ext;
            $dest = $uploadDir . '/' . $storedName;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $sql = "INSERT INTO study_materials
                        (faculty_user_id, subject_id, title, description,
                         file_name, original_name, file_type, file_size)
                        VALUES
                        (:fid, :sid, :title, :description, :file_name, :original_name, :file_type, :file_size)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':fid'          => $facultyId,
                    ':sid'          => $subjectId,
                    ':title'        => $title,
                    ':description'  => $description !== '' ? $description : null,
                    ':file_name'    => $storedName,
                    ':original_name'=> $file['name'],
                    ':file_type'    => $file['type'],
                    ':file_size'    => $file['size'],
                ]);
                $success = 'Material uploaded successfully.';
            } else {
                $error = 'Failed to move uploaded file.';
            }
        }
    }
}

$sqlList = "SELECT m.*, s.name AS subject_name, s.code AS subject_code
            FROM study_materials m
            JOIN subjects s ON s.id = m.subject_id
            WHERE m.faculty_user_id = :fid
            ORDER BY m.uploaded_at DESC";
$stmtList = $pdo->prepare($sqlList);
$stmtList->execute([':fid' => $facultyId]);
$materials = $stmtList->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Study Materials</h3>

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
        <label class="form-label">File (PDF/PPT/DOC)</label>
        <input type="file" name="file" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Upload Material</button>
</form>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Subject</th>
        <th>Title</th>
        <th>File</th>
        <th>Uploaded At</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($materials): ?>
        <?php foreach ($materials as $i => $m): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($m['subject_name'] . ' (' . $m['subject_code'] . ')'); ?></td>
                <td><?php echo htmlspecialchars($m['title']); ?></td>
                <td>
                    <a href="<?php echo BASE_URL; ?>/uploads/materials/<?php echo rawurlencode($m['file_name']); ?>" target="_blank">
                        <?php echo htmlspecialchars($m['original_name'] ?? $m['file_name']); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($m['uploaded_at']); ?></td>
                <td>
                    <a href="materials.php?action=delete&id=<?php echo $m['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this material?');">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6" class="text-center">No materials uploaded yet.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

