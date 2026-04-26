<?php
// admin/academics/courses.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$action = $_GET['action'] ?? null;
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$course = [
    'name'   => '',
    'code'   => '',
    'description' => '',
    'status' => 'active',
];

$error   = '';
$success = '';

if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE FROM courses WHERE id = :id");
    $del->execute([':id' => $id]);
    header('Location: courses.php');
    exit;
}

if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $course['name']        = $row['name'];
        $course['code']        = $row['code'];
        $course['description'] = $row['description'] ?? '';
        $course['status']      = $row['status'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cid         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $course['name']   = trim($_POST['name'] ?? '');
    $course['code']   = trim($_POST['code'] ?? '');
    $course['status'] = $_POST['status'] ?? 'active';
    $course['description'] = trim($_POST['description'] ?? '');

    if ($course['name'] === '' || $course['code'] === '') {
        $error = 'Name and code are required.';
    } else {
        try {
            if ($cid > 0) {
                $sql = "UPDATE courses SET
                            name = :name,
                            code = :code,
                            description = :description,
                            status = :status
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name'        => $course['name'],
                    ':code'        => $course['code'],
                    ':description' => $course['description'] !== '' ? $course['description'] : null,
                    ':status'      => $course['status'],
                    ':id'          => $cid,
                ]);
                $success = 'Course updated successfully.';
            } else {
                $sql = "INSERT INTO courses (name, code, description, status)
                        VALUES (:name, :code, :description, :status)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name'        => $course['name'],
                    ':code'        => $course['code'],
                    ':description' => $course['description'] !== '' ? $course['description'] : null,
                    ':status'      => $course['status'],
                ]);
                $success = 'Course added successfully.';
            }
        } catch (Throwable $e) {
            $error = 'Error saving course. Possibly duplicate code.';
        }
    }
}

$rows = $pdo->query("SELECT * FROM courses ORDER BY name")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3">Courses</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" class="mb-4">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Course Name</label>
            <input type="text" name="name" class="form-control"
                   value="<?php echo htmlspecialchars($course['name']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Course Code</label>
            <input type="text" name="code" class="form-control"
                   value="<?php echo htmlspecialchars($course['code']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php echo $course['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $course['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php
            echo htmlspecialchars($course['description'] ?? '');
        ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><?php echo $id > 0 ? 'Update' : 'Add'; ?> Course</button>
    <?php if ($id > 0): ?>
        <a href="courses.php" class="btn btn-secondary ms-2">Cancel Edit</a>
    <?php endif; ?>
</form>
<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Name</th>
        <th>Code</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($rows): ?>
        <?php foreach ($rows as $i => $row): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['code']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                <td><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
                <td>
                    <a href="courses.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="courses.php?action=delete&id=<?php echo $row['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this course? This may affect branches, semesters, and subjects.');">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6" class="text-center">No courses found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

