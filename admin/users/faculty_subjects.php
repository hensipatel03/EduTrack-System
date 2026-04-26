<?php
// admin/users/faculty_subjects.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$facultyId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($facultyId <= 0) {
    die('Invalid faculty ID.');
}

// Verify faculty exists
$stmt = $pdo->prepare("SELECT u.id, u.first_name, u.last_name 
                       FROM users u 
                       JOIN roles r ON r.id = u.role_id 
                       WHERE u.id = :id AND r.name = 'faculty'");
$stmt->execute([':id' => $facultyId]);
$faculty = $stmt->fetch();

if (!$faculty) {
    die('Faculty not found.');
}

$error   = '';
$success = '';

// Handle subject assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subjectIds = isset($_POST['subject_ids']) && is_array($_POST['subject_ids']) ? $_POST['subject_ids'] : [];
    $subjectIds = array_filter(array_map('intval', $subjectIds), fn($id) => $id > 0);

    try {
        $pdo->beginTransaction();

        // Delete existing assignments for this faculty
        $del = $pdo->prepare("DELETE FROM faculty_subjects WHERE faculty_user_id = :fid");
        $del->execute([':fid' => $facultyId]);

        // Insert new assignments using named placeholders
        if (!empty($subjectIds)) {
            $params = [':fid' => $facultyId];
            $values = [];
            foreach ($subjectIds as $i => $sid) {
                $key = ':s' . $i;
                $values[] = "(:fid, $key)";
                $params[$key] = $sid;
            }

            $sql = "INSERT INTO faculty_subjects (faculty_user_id, subject_id) VALUES " . implode(', ', $values);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        $pdo->commit();
        $success = 'Subjects assigned successfully.';
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = 'Error assigning subjects: ' . $e->getMessage();
    }
}

// Get all subjects grouped by course/branch/semester
$sql = "SELECT s.id, s.name, s.code, c.name AS course_name, b.name AS branch_name, sem.number AS semester_number
        FROM subjects s
        JOIN courses c ON c.id = s.course_id
        JOIN branches b ON b.id = s.branch_id
        JOIN semesters sem ON sem.id = s.semester_id
        WHERE s.status = 'active'
        ORDER BY c.name, b.name, sem.number, s.name";
$allSubjects = $pdo->query($sql)->fetchAll();

// Get currently assigned subjects for this faculty
$stmt = $pdo->prepare("SELECT subject_id FROM faculty_subjects WHERE faculty_user_id = :fid");
$stmt->execute([':fid' => $facultyId]);
$assignedSubjects = array_column($stmt->fetchAll(), 'subject_id');

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="mb-3">
    <a href="faculty_list.php" class="btn btn-secondary btn-sm">← Back to Faculty</a>
</div>

<h3 class="mb-3">
    Assign Subjects to 
    <strong><?php echo htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']); ?></strong>
</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" class="card p-4">
    <div class="form-group mb-3">
        <label class="form-label fw-bold">Select Subjects</label>
        <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 10px;">
            <?php if (!empty($allSubjects)): ?>
                <?php
                $lastCourse = null;
                $lastBranch = null;
                $lastSem = null;
                ?>
                <?php foreach ($allSubjects as $subject): ?>
                    <?php
                    $courseChanged = $lastCourse !== $subject['course_name'];
                    $branchChanged = $lastBranch !== $subject['branch_name'];
                    $semChanged = $lastSem !== $subject['semester_number'];
                    ?>
                    
                    <?php if ($courseChanged): ?>
                        <?php if ($lastCourse !== null): ?>
                            </div></div>
                        <?php endif; ?>
                        <div class="mt-3">
                            <strong style="color: #dc3545;">📚 <?php echo htmlspecialchars($subject['course_name']); ?></strong>
                            <div style="margin-left: 15px;">
                    <?php endif; ?>
                    
                    <?php if ($branchChanged): ?>
                        <?php if ($lastBranch !== null): ?>
                            </div></div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <em style="color: #0d6efd;">🏢 <?php echo htmlspecialchars($subject['branch_name']); ?></em>
                            <div style="margin-left: 15px;">
                    <?php endif; ?>
                    
                    <?php if ($semChanged): ?>
                        <?php if ($lastSem !== null): ?>
                            </div>
                        <?php endif; ?>
                        <div class="mt-1">
                            <span style="font-weight: 500; color: #666;">Semester <?php echo (int)$subject['semester_number']; ?></span>
                            <div style="margin-left: 10px;">
                    <?php endif; ?>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="subject_ids[]" 
                               value="<?php echo $subject['id']; ?>" 
                               id="subject_<?php echo $subject['id']; ?>"
                               <?php echo in_array($subject['id'], $assignedSubjects) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="subject_<?php echo $subject['id']; ?>">
                            <?php echo htmlspecialchars($subject['name'] . ' (' . $subject['code'] . ')'); ?>
                        </label>
                    </div>
                    
                    <?php
                    $lastCourse = $subject['course_name'];
                    $lastBranch = $subject['branch_name'];
                    $lastSem = $subject['semester_number'];
                    ?>
                <?php endforeach; ?>
                
                <?php if ($lastCourse !== null): ?>
                    </div></div></div></div>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-muted">No active subjects available.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="faculty_list.php" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>


