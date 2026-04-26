<?php
// student/materials.php
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

$subId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;

$sqlSub = "SELECT DISTINCT s.id, s.name, s.code
           FROM subjects s
           JOIN study_materials m ON m.subject_id = s.id
           WHERE s.course_id = :course_id 
           AND s.branch_id = :branch_id 
           AND s.semester_id = :semester_id
           ORDER BY s.name";
$stmtSub = $pdo->prepare($sqlSub);
$stmtSub->execute([
    ':course_id' => $enrollment['course_id'],
    ':branch_id' => $enrollment['branch_id'],
    ':semester_id' => $enrollment['semester_id']
]);
$subjects = $stmtSub->fetchAll();

$params = [];
$where  = "s.course_id = :course_id AND s.branch_id = :branch_id AND s.semester_id = :semester_id";
$params[':course_id'] = $enrollment['course_id'];
$params[':branch_id'] = $enrollment['branch_id'];
$params[':semester_id'] = $enrollment['semester_id'];

if ($subId > 0) {
    $where .= " AND m.subject_id = :sid";
    $params[':sid'] = $subId;
}

$sql = "SELECT m.*, s.name AS subject_name, s.code AS subject_code,
    u.full_name AS uploaded_by_name
    FROM study_materials m
    JOIN subjects s ON s.id = m.subject_id
    LEFT JOIN users u ON u.id = m.faculty_user_id
    WHERE $where
    ORDER BY m.uploaded_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materials = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Study Materials</h3>

<form method="get" class="mb-3">
    <label class="form-label">Filter by Subject</label>
    <select name="subject_id" class="form-select" onchange="this.form.submit()">
        <option value="">All Subjects</option>
        <?php foreach ($subjects as $s): ?>
            <option value="<?php echo $s['id']; ?>"
                <?php echo (string)$subId === (string)$s['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($s['name'] . ' (' . $s['code'] . ')'); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Subject</th>
        <th>Title</th>
        <th>File</th>
        <th>Uploaded By</th>
        <th>Uploaded At</th>
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
                <td><?php echo htmlspecialchars($m['uploaded_by_name'] ?? $m['faculty_user_id']); ?></td>
                <td><?php echo htmlspecialchars($m['uploaded_at']); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="6" class="text-center">No materials found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>
