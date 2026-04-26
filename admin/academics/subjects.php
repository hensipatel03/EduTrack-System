<?php
// admin/academics/subjects.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$action = $_GET['action'] ?? null;
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$subject = [
    'course_id'   => '',
    'branch_id'   => '',
    'semester_id' => '',
    'name'        => '',
    'code'        => '',
    'credits'     => '',
    'description' => '',
    'status'      => 'active',
];

$error   = '';
$success = '';

$courses = $pdo->query("SELECT id, name, code FROM courses WHERE status = 'active' ORDER BY name")->fetchAll();
$branches = $pdo->query("SELECT b.id, b.name, b.code, c.name AS course_name 
                         FROM branches b
                         JOIN courses c ON c.id = b.course_id
                         WHERE b.status = 'active'
                         ORDER BY c.name, b.name")->fetchAll();
$semesters = $pdo->query("SELECT s.id, s.name, s.number, c.name AS course_name
                          FROM semesters s
                          JOIN courses c ON c.id = s.course_id
                          WHERE s.status = 'active'
                          ORDER BY c.name, s.number")->fetchAll();

if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE FROM subjects WHERE id = :id");
    $del->execute([':id' => $id]);
    header('Location: subjects.php');
    exit;
}

if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $subject['course_id']   = $row['course_id'];
        $subject['branch_id']   = $row['branch_id'];
        $subject['semester_id'] = $row['semester_id'];
        $subject['name']        = $row['name'];
        $subject['code']        = $row['code'];
        $subject['credits']     = $row['credits'];
        $subject['description'] = $row['description'] ?? '';
        $subject['status']      = $row['status'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid                  = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $subject['course_id']   = $_POST['course_id'] !== '' ? (int)$_POST['course_id'] : null;
    $subject['branch_id']   = $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;
    $subject['semester_id'] = $_POST['semester_id'] !== '' ? (int)$_POST['semester_id'] : null;
    $subject['name']        = trim($_POST['name'] ?? '');
    $subject['code']        = trim($_POST['code'] ?? '');
    $subject['credits']     = $_POST['credits'] !== '' ? (int)$_POST['credits'] : null;
    $subject['description'] = trim($_POST['description'] ?? '');
    $subject['status']      = $_POST['status'] ?? 'active';

    if (!$subject['course_id'] || !$subject['branch_id'] || !$subject['semester_id'] ||
        $subject['name'] === '' || $subject['code'] === '') {
        $error = 'Course, branch, semester, name, and code are required.';
    } else {
        try {
            if ($sid > 0) {
                $sql = "UPDATE subjects SET
                            course_id   = :course_id,
                            branch_id   = :branch_id,
                            semester_id = :semester_id,
                            name        = :name,
                            code        = :code,
                            credits     = :credits,
                            description = :description,
                            status      = :status
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':course_id'   => $subject['course_id'],
                    ':branch_id'   => $subject['branch_id'],
                    ':semester_id' => $subject['semester_id'],
                    ':name'        => $subject['name'],
                    ':code'        => $subject['code'],
                    ':credits'     => $subject['credits'],
                    ':description' => $subject['description'] !== '' ? $subject['description'] : null,
                    ':status'      => $subject['status'],
                    ':id'          => $sid,
                ]);
                $success = 'Subject updated successfully.';
            } else {
                $sql = "INSERT INTO subjects
                        (course_id, branch_id, semester_id, name, code, credits, description, status)
                        VALUES (:course_id, :branch_id, :semester_id, :name, :code, :credits, :description, :status)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':course_id'   => $subject['course_id'],
                    ':branch_id'   => $subject['branch_id'],
                    ':semester_id' => $subject['semester_id'],
                    ':name'        => $subject['name'],
                    ':code'        => $subject['code'],
                    ':credits'     => $subject['credits'],
                    ':description' => $subject['description'] !== '' ? $subject['description'] : null,
                    ':status'      => $subject['status'],
                ]);
                $success = 'Subject added successfully.';
            }
        } catch (Throwable $e) {
            $error = 'Error saving subject. Possibly duplicate subject code.';
        }
    }
}

$filter_course = isset($_GET['filter_course']) ? (int)$_GET['filter_course'] : 0;
$filter_branch = isset($_GET['filter_branch']) ? (int)$_GET['filter_branch'] : 0;
$filter_semester = isset($_GET['filter_semester']) ? (int)$_GET['filter_semester'] : 0;

$sql = "SELECT sub.*, 
               c.name AS course_name, c.code AS course_code,
               b.name AS branch_name, b.code AS branch_code,
               s.name AS semester_name, s.number AS semester_number
        FROM subjects sub
        JOIN courses c ON c.id = sub.course_id
        JOIN branches b ON b.id = sub.branch_id
        JOIN semesters s ON s.id = sub.semester_id";
$conds = [];
$params = [];
if ($filter_course > 0) {
    $conds[] = 'sub.course_id = :course_id';
    $params[':course_id'] = $filter_course;
}
if ($filter_branch > 0) {
    $conds[] = 'sub.branch_id = :branch_id';
    $params[':branch_id'] = $filter_branch;
}
if ($filter_semester > 0) {
    $conds[] = 'sub.semester_id = :semester_id';
    $params[':semester_id'] = $filter_semester;
}
if ($conds) {
    $sql .= ' WHERE ' . implode(' AND ', $conds);
}
$sql .= " ORDER BY c.name, b.name, s.number, sub.name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3">Subjects</h3>

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
                        <?php echo (string)$subject['course_id'] === (string)$c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select" required>
                <option value="">-- Select Branch --</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b['id']; ?>"
                        <?php echo (string)$subject['branch_id'] === (string)$b['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($b['course_name'] . ' - ' . $b['name'] . ' (' . $b['code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Semester</label>
            <select name="semester_id" class="form-select" required>
                <option value="">-- Select Semester --</option>
                <?php foreach ($semesters as $s): ?>
                    <option value="<?php echo $s['id']; ?>"
                        <?php echo (string)$subject['semester_id'] === (string)$s['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['course_name'] . ' - ' . $s['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Subject Name</label>
            <input type="text" name="name" class="form-control"
                   value="<?php echo htmlspecialchars($subject['name']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Subject Code</label>
            <input type="text" name="code" class="form-control"
                   value="<?php echo htmlspecialchars($subject['code']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Credits</label>
            <input type="number" name="credits" class="form-control" min="0" max="10"
                   value="<?php echo htmlspecialchars((string)$subject['credits']); ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php echo $subject['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $subject['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php
            echo htmlspecialchars($subject['description'] ?? '');
        ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary"><?php echo $id > 0 ? 'Update' : 'Add'; ?> Subject</button>
    <?php if ($id > 0): ?>
        <a href="subjects.php" class="btn btn-secondary ms-2">Cancel Edit</a>
    <?php endif; ?>
</form>

<form method="get" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Course</label>
            <select name="filter_course" class="form-select">
                <option value="0">-- All Courses --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $filter_course === (int)$c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
       
        <div class="col-md-3">
            <label class="form-label">Semester</label>
            <select name="filter_semester" class="form-select">
                <option value="0">-- All Semesters --</option>
                <?php foreach ($semesters as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php echo $filter_semester === (int)$s['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['course_name'] . ' - ' . $s['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-secondary">Apply</button>
            <a href="subjects.php" class="btn btn-outline-secondary ms-1">Reset</a>
        </div>
    </div>
</form>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Course</th>
        <th>Branch</th>
        <th>Semester</th>
        <th>Name</th>
        <th>Code</th>
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
                <td><?php echo htmlspecialchars($row['branch_name'] . ' (' . $row['branch_code'] . ')'); ?></td>
                <td><?php echo htmlspecialchars($row['semester_name']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['code']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                <td>
                    <a href="subjects.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="subjects.php?action=delete&id=<?php echo $row['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this subject? This may affect faculty assignments and materials.');">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="8" class="text-center">No subjects found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
