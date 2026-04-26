<?php
// admin/academics/semesters.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$action = $_GET['action'] ?? null;
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$semester = [
    'course_id' => '',
    'number'    => '',
    'status'    => 'active',
];

$error   = '';
$success = '';

$courses = $pdo->query("SELECT id, name, code FROM courses WHERE status = 'active' ORDER BY name")->fetchAll();

if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE FROM semesters WHERE id = :id");
    $del->execute([':id' => $id]);
    header('Location: semesters.php');
    exit;
}

if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM semesters WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $semester['course_id'] = $row['course_id'];
        $semester['number']    = $row['number'];
        $semester['status']    = $row['status'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid              = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $semester['course_id'] = $_POST['course_id'] !== '' ? (int)$_POST['course_id'] : null;
    $semester['number']    = (int)($_POST['number'] ?? 0);
    $semester['status']    = $_POST['status'] ?? 'active';

    if (!$semester['course_id'] || $semester['number'] <= 0) {
        $error = 'Course and number are required.';
    } else {
        $semester['name'] = 'Semester ' . $semester['number'];
        try {
            if ($sid > 0) {
                $sql = "UPDATE semesters SET
                            course_id = :course_id,
                            name      = :name,
                            number    = :number,
                            status    = :status
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':course_id' => $semester['course_id'],
                    ':name'      => $semester['name'],
                    ':number'    => $semester['number'],
                    ':status'    => $semester['status'],
                    ':id'        => $sid,
                ]);
                $success = 'Semester updated successfully.';
            } else {
                $sql = "INSERT INTO semesters (course_id, name, number, status)
                        VALUES (:course_id, :name, :number, :status)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':course_id' => $semester['course_id'],
                    ':name'      => $semester['name'],
                    ':number'    => $semester['number'],
                    ':status'    => $semester['status'],
                ]);
                $success = 'Semester added successfully.';
            }
        } catch (Throwable $e) {
            $error = 'Error saving semester. Possibly duplicate number for this course.';
        }
    }
}

$filter_course = isset($_GET['filter_course']) ? (int)$_GET['filter_course'] : 0;

$sql = "SELECT s.*, c.name AS course_name, c.code AS course_code
    FROM semesters s
    JOIN courses c ON c.id = s.course_id";
$params = [];
if ($filter_course > 0) {
    $sql .= " WHERE s.course_id = :course_id";
    $params[':course_id'] = $filter_course;
}
$sql .= " ORDER BY c.name ASC, CAST(s.number AS UNSIGNED) ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3">Semesters</h3>

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
            <label class="form-label">Course</label>
            <select name="course_id" class="form-select" required>
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>"
                        <?php echo (string)$semester['course_id'] === (string)$c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Semester Number</label>
            <input type="number" name="number" class="form-control" min="1" max="12"
                   value="<?php echo htmlspecialchars((string)$semester['number']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php echo $semester['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $semester['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary"><?php echo $id > 0 ? 'Update' : 'Add'; ?> Semester</button>
    <?php if ($id > 0): ?>
        <a href="semesters.php" class="btn btn-secondary ms-2">Cancel Edit</a>
    <?php endif; ?>
</form>

<form method="get" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label">Filter by Course</label>
            <select name="filter_course" class="form-select">
                <option value="0">-- All Courses --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $filter_course === (int)$c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-secondary">Apply</button>
            <a href="semesters.php" class="btn btn-outline-secondary ms-1">Reset</a>
        </div>
    </div>
</form>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Course</th>
        <th>Name</th>
        <th>No.</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($rows): ?>
        <?php foreach ($rows as $i => $row): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($row['course_name'] . ' (' . $row['course_code'] . ')'); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars((string)$row['number']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                <td>
                    <a href="semesters.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="semesters.php?action=delete&id=<?php echo $row['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this semester? This may affect subjects and students.');">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6" class="text-center">No semesters found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

