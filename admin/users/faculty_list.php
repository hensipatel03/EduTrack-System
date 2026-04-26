<?php
// admin/users/faculty_list.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$action = $_GET['action'] ?? null;
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action && $id > 0) {
    if ($action === 'toggle') {
        $stmt = $pdo->prepare("SELECT status FROM users u 
                               JOIN roles r ON r.id = u.role_id 
                               WHERE u.id = :id AND r.name = 'faculty'");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if ($row) {
            $newStatus = $row['status'] === 'active' ? 'inactive' : 'active';
            $upd = $pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
            $upd->execute([':status' => $newStatus, ':id' => $id]);
        }
    } elseif ($action === 'approve') {
        $upd = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = :id");
        $upd->execute([':id' => $id]);
    } elseif ($action === 'reject') {
        $upd = $pdo->prepare("UPDATE users SET status = 'inactive' WHERE id = :id");
        $upd->execute([':id' => $id]);
    } elseif ($action === 'delete') {
        // Cascades via foreign keys (faculty_profiles, faculty_subjects, etc.)
        $del = $pdo->prepare("DELETE u FROM users u 
                              JOIN roles r ON r.id = u.role_id 
                              WHERE u.id = :id AND r.name = 'faculty'");
        $del->execute([':id' => $id]);
    }

    header('Location: faculty_list.php');
    exit;
}

$sql = "SELECT u.*, fp.employee_code, fp.designation
        FROM users u
        JOIN roles r ON r.id = u.role_id
        LEFT JOIN faculty_profiles fp ON fp.user_id = u.id
        WHERE r.name = 'faculty'
        ORDER BY u.created_at DESC";

// Filters (from GET)
$search = trim((string)($_GET['search'] ?? ''));
$status = $_GET['status'] ?? '';
$subjectId = isset($_GET['subject_id']) && $_GET['subject_id'] !== '' ? (int)$_GET['subject_id'] : 0;

// Load distinct subjects that are assigned to faculty for the filter dropdown
try {
    $subjects = $pdo->query("SELECT DISTINCT s.id, s.name FROM faculty_subjects fs JOIN subjects s ON s.id = fs.subject_id ORDER BY s.name")->fetchAll();
} catch (Throwable $e) {
    $subjects = [];
}

$where = ["r.name = ?"];
$params = ['faculty'];

if ($status !== '' && in_array($status, ['active','inactive','pending'], true)) {
    $where[] = 'u.status = ?';
    $params[] = $status;
}

if ($subjectId > 0) {
    $where[] = 'EXISTS (SELECT 1 FROM faculty_subjects fs WHERE fs.faculty_user_id = u.id AND fs.subject_id = ?)';
    $params[] = $subjectId;
}

if ($search !== '') {
    $where[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql = "SELECT u.*, fp.employee_code, fp.designation
        FROM users u
        JOIN roles r ON r.id = u.role_id
        LEFT JOIN faculty_profiles fp ON fp.user_id = u.id
        WHERE " . implode(' AND ', $where) . " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Faculty List</h3>
    <a href="faculty_form.php" class="btn btn-primary btn-sm">Add Faculty</a>
</div>

<form method="get" class="mb-3">
    <div class="row g-2">
        <div class="col-md-4">
            <input type="search" name="search" class="form-control" placeholder="Search name, email or username"
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All Status</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="subject_id" class="form-select">
                <option value="">All Subjects</option>
                <?php foreach ($subjects as $sub): ?>
                    <option value="<?php echo (int)$sub['id']; ?>" <?php echo $subjectId === (int)$sub['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($sub['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-secondary">Filter</button>
            <a href="faculty_list.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<table class="table table-striped table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($rows): ?>
        <?php foreach ($rows as $index => $row): ?>
            <tr>
                <td><?php echo $index + 1; ?></td>
                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <span class="badge bg-<?php 
                        echo $row['status'] === 'active' ? 'success' : 
                             ($row['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                        <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                    </span>
                </td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <a href="faculty_list.php?action=approve&id=<?php echo $row['id']; ?>"
                           class="btn btn-sm btn-success"
                           onclick="return confirm('Approve this faculty?');">
                            Approve
                        </a>
                        <a href="faculty_list.php?action=reject&id=<?php echo $row['id']; ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Reject this faculty?');">
                            Reject
                        </a>
                    <?php else: ?>
                        <a href="faculty_form.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                        <a href="faculty_subjects.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Subjects</a>
                        <a href="faculty_list.php?action=toggle&id=<?php echo $row['id']; ?>"
                           class="btn btn-sm btn-warning"
                           onclick="return confirm('Are you sure to change status?');">
                            <?php echo $row['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                        </a>
                        <a href="faculty_list.php?action=delete&id=<?php echo $row['id']; ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure to delete this faculty?');">
                            Delete
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="8" class="text-center">No faculty found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

