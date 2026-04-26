<?php
// faculty/subjects.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$facultyId = (int)$user['id'];

$sql = "SELECT fs.id AS fac_sub_id, s.*, 
               c.name AS course_name, b.name AS branch_name, sem.name AS semester_name
        FROM faculty_subjects fs
        JOIN subjects s ON s.id = fs.subject_id
        JOIN courses c ON c.id = s.course_id
        JOIN branches b ON b.id = s.branch_id
        JOIN semesters sem ON sem.id = s.semester_id
        WHERE fs.faculty_user_id = :fid
        ORDER BY c.name, b.name, sem.number, s.name";
$stmt = $pdo->prepare($sql);
$stmt->execute([':fid' => $facultyId]);
$subjects = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">My Subjects</h3>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Course</th>
        <th>Branch</th>
        <th>Semester</th>
        <th>Subject</th>
        <th>Code</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($subjects): ?>
        <?php foreach ($subjects as $i => $row): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                <td><?php echo htmlspecialchars($row['semester_name']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['code']); ?></td>
                <td>
                    <a href="<?php echo BASE_URL; ?>/faculty/students.php?subject_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">
                        View Students
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7" class="text-center">No assigned subjects.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

