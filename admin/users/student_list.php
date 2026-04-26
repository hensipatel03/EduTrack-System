<?php
// admin/users/student_list.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$action = $_GET['action'] ?? null;
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action && $id > 0) {
    if ($action === 'toggle') {
        $stmt = $pdo->prepare("SELECT u.status FROM users u
                               JOIN roles r ON r.id = u.role_id
                               WHERE u.id = :id AND r.name = 'student'");
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
        $del = $pdo->prepare("DELETE u FROM users u
                              JOIN roles r ON r.id = u.role_id
                              WHERE u.id = :id AND r.name = 'student'");
        $del->execute([':id' => $id]);
    }

    header('Location: student_list.php');
    exit;
}

$sql = "SELECT u.*, sp.enrollment_no, c.name AS course_name, b.name AS branch_name, s.name AS semester_name
        FROM users u
        JOIN roles r ON r.id = u.role_id
        LEFT JOIN student_profiles sp ON sp.user_id = u.id
        LEFT JOIN courses c ON c.id = sp.course_id
        LEFT JOIN branches b ON b.id = sp.branch_id
        LEFT JOIN semesters s ON s.id = sp.semester_id
        WHERE r.name = 'student'
        ORDER BY u.created_at DESC";

// Filters (from GET)
$search = trim((string)($_GET['search'] ?? ''));
$status = $_GET['status'] ?? '';
$courseId = isset($_GET['course_id']) && $_GET['course_id'] !== '' ? (int)$_GET['course_id'] : 0;
$branchId = isset($_GET['branch_id']) && $_GET['branch_id'] !== '' ? (int)$_GET['branch_id'] : 0;
$semesterId = isset($_GET['semester_id']) && $_GET['semester_id'] !== '' ? (int)$_GET['semester_id'] : 0;

// Load lists for filters
try {
    $courses = $pdo->query("SELECT id, name FROM courses WHERE status = 'active' ORDER BY name")->fetchAll();
    $branches = $pdo->query("SELECT id, name FROM branches WHERE status = 'active' ORDER BY name")->fetchAll();
    $semesters = $pdo->query("SELECT id, name FROM semesters WHERE status = 'active' ORDER BY name")->fetchAll();
} catch (Throwable $e) {
    $courses = $branches = $semesters = [];
}

$conditions = ["r.name = ?"];
$params = ['student'];

if ($status !== '' && in_array($status, ['active','inactive','pending'], true)) {
    $conditions[] = 'u.status = ?';
    $params[] = $status;
}

if ($courseId > 0) {
    $conditions[] = 'sp.course_id = ?';
    $params[] = $courseId;
}

if ($branchId > 0) {
    $conditions[] = 'sp.branch_id = ?';
    $params[] = $branchId;
}

if ($semesterId > 0) {
    $conditions[] = 'sp.semester_id = ?';
    $params[] = $semesterId;
}

if ($search !== '') {
    $conditions[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ? OR sp.enrollment_no LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = implode(' AND ', $conditions);
$sql = "SELECT u.*, sp.enrollment_no, c.name AS course_name, b.name AS branch_name, s.name AS semester_name
        FROM users u
        JOIN roles r ON r.id = u.role_id
        LEFT JOIN student_profiles sp ON sp.user_id = u.id
        LEFT JOIN courses c ON c.id = sp.course_id
        LEFT JOIN branches b ON b.id = sp.branch_id
        LEFT JOIN semesters s ON s.id = sp.semester_id
        WHERE " . $whereClause . " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Student List</h3>
    <a href="student_form.php" class="btn btn-primary btn-sm">Add Student</a>
</div>

<style>
    .filter-form-section {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
    }
    
    .filter-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: flex-end;
        margin-bottom: 12px;
    }
    
    .filter-controls .form-control,
    .filter-controls .form-select {
        flex: 1;
        min-width: 150px;
    }
    
    .search-input-wrapper {
        flex: 1 1 100%;
        min-width: 200px;
    }
    
    .filters-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }
    
    .button-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    
    .button-group .btn {
        white-space: nowrap;
        padding: 6px 14px;
        font-size: 0.9rem;
    }
    
    .table-responsive-wrapper {
        overflow-x: auto;
        margin-bottom: 20px;
        border: 1px solid #dee2e6;
        border-radius: 6px;
    }
    
    .table-responsive-wrapper table {
        min-width: 100%;
    }
    
    @media (max-width: 768px) {
        .filter-form-section {
            padding: 12px;
        }
        
        .filters-row {
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        }
        
        .button-group {
            width: 100%;
            justify-content: stretch;
        }
        
        .button-group .btn {
            flex: 1;
            min-width: auto;
        }
        
        .table-responsive-wrapper {
            font-size: 0.9rem;
        }
        
        .table-responsive-wrapper table th,
        .table-responsive-wrapper table td {
            padding: 0.5rem;
        }
    }
</style>

<div class="filter-form-section">
        <form method="get">
        <div class="search-input-wrapper">
            <input type="search" name="search" class="form-control form-control-sm" 
                   placeholder="Search by name, email, username or enrollment number"
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="filters-row">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
            </select>
            
            <select name="course_id" class="form-select form-select-sm">
                <option value="">All Courses</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo (int)$c['id']; ?>" <?php echo $courseId === (int)$c['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="branch_id" class="form-select form-select-sm">
                <option value="">All Branches</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?php echo (int)$b['id']; ?>" <?php echo $branchId === (int)$b['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['name']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="semester_id" class="form-select form-select-sm">
                <option value="">All Semesters</option>
                <?php foreach ($semesters as $s): ?>
                    <option value="<?php echo (int)$s['id']; ?>" <?php echo $semesterId === (int)$s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($s['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="button-group">
            <button type="submit" class="btn btn-secondary">Filter</button>
            <a href="student_list.php" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="table-responsive-wrapper">

<table class="table table-striped table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>No</th>
        <th>Name</th>
        <th>Enrollment No</th>
        <th>Username</th>
        <th>Email</th>
        <th>Mobile</th>
        <th>Course</th>
        <th>Branch</th>
        <th>Semester</th>
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
                <td><?php echo htmlspecialchars($row['enrollment_no'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['mobile'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['course_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['branch_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($row['semester_name'] ?? ''); ?></td>
                <td>
                    <span class="badge bg-<?php 
                        echo $row['status'] === 'active' ? 'success' : 
                             ($row['status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                        <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-1 flex-nowrap align-items-center" style="white-space:nowrap;">
                        <?php if ($row['status'] === 'pending'): ?>
                            <a href="student_list.php?action=approve&id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-success small"
                               onclick="return confirm('Approve this student?');" style="font-size:0.78rem;">
                                Approve
                            </a>
                            <a href="student_list.php?action=reject&id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-danger small"
                               onclick="return confirm('Reject this student?');" style="font-size:0.78rem;">
                                Reject
                            </a>
                        <?php else: ?>
                            <a href="student_form.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info small" style="font-size:0.78rem;">Edit</a>
                            <a href="student_list.php?action=toggle&id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-warning small"
                               onclick="return confirm('Are you sure to change status?');" style="font-size:0.78rem;">
                                <?php echo $row['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <a href="student_list.php?action=delete&id=<?php echo $row['id']; ?>"
                               class="btn btn-sm btn-danger small"
                               onclick="return confirm('Are you sure to delete this student?');" style="font-size:0.78rem;">
                                Delete
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="11" class="text-center">No students found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
</div>