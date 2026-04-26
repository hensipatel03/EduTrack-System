<?php
// student/register.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = Database::getInstance();
$error = '';
$success = '';

// Load active courses for dropdown
try {
    $courses = $pdo->query("SELECT id, name FROM courses WHERE status = 'active' ORDER BY name")->fetchAll();
    $semesters = $pdo->query("SELECT DISTINCT s.id, s.name, s.course_id FROM semesters s JOIN courses c ON c.id = s.course_id WHERE c.status = 'active' ORDER BY s.course_id, s.name")->fetchAll();
} catch (Throwable $e) {
    $courses = [];
    $semesters = [];
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $course_id = isset($_POST['course_id']) && $_POST['course_id'] !== '' ? (int)$_POST['course_id'] : null;
    $semester_id = isset($_POST['semester_id']) && $_POST['semester_id'] !== '' ? (int)$_POST['semester_id'] : null;

    if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Generate unique enrollment ID
            $enrollmentId = generateEnrollmentId($pdo);
            
            // uniqueness checks
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1");
            $stmt->execute([':username' => $enrollmentId, ':email' => $email]);
            if ($stmt->fetch()) {
                throw new RuntimeException('Email already exists.');
            }

            // Check if enrollment ID already exists (though unlikely)
            $stmt = $pdo->prepare("SELECT id FROM student_profiles WHERE enrollment_no = :enrollment_no LIMIT 1");
            $stmt->execute([':enrollment_no' => $enrollmentId]);
            if ($stmt->fetch()) {
                throw new RuntimeException('Enrollment ID generation conflict. Please try again.');
            }

            // Get student role id
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'student' LIMIT 1");
            $stmt->execute();
            $role = $stmt->fetch();
            if (!$role) {
                throw new RuntimeException('Student role not found.');
            }
            $roleId = (int)$role['id'];

            $pdo->beginTransaction();

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            error_log('Attempting to insert student: email=' . $email . ', enrollment=' . $enrollmentId . ', role_id=' . $roleId);
            
            $insert = $pdo->prepare("INSERT INTO users (role_id, username, email, password_hash, full_name, first_name, last_name, status, created_at, updated_at)
                VALUES (:role_id, :username, :email, :password_hash, :full_name, :first_name, :last_name, 'pending', NOW(), NOW())");
            $insert->execute([
                ':role_id' => $roleId,
                ':username' => $enrollmentId,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':full_name' => $firstName . ' ' . $lastName,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
            ]);
            
            error_log('User insert executed, checking affected rows...');

            $userId = (int)$pdo->lastInsertId();
            
            if (!$userId) {
                throw new RuntimeException('Failed to get user ID from insert.');
            }

            $insertProf = $pdo->prepare("INSERT INTO student_profiles (user_id, enrollment_no, course_id, semester_id, created_at, updated_at)
                VALUES (:user_id, :enrollment_no, :course_id, :semester_id, NOW(), NOW())");
            $insertProf->execute([
                ':user_id' => $userId,
                ':enrollment_no' => $enrollmentId,
                ':course_id' => $course_id,
                ':semester_id' => $semester_id,
            ]);

            $pdo->commit();
            error_log('Student registered successfully: User ID=' . $userId . ', Enrollment ID=' . $enrollmentId);
            $success = 'Registration successful. Your Enrollment ID is: <strong>' . $enrollmentId . '</strong>. Your account is pending admin approval. You will be able to login once approved.';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Registration failed: ' . $e->getMessage();
            // Log error for debugging
            error_log('Student Registration Error: ' . $e->getMessage() . ' | Code: ' . $e->getCode());
            error_log('Stack trace: ' . $e->getTraceAsString());
        }
    }
}

// Load simple form
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Registration</title>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Student Registration</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post" autocomplete="off">
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Course</label>
                            <select name="course_id" id="course_id" class="form-select">
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo (int)$c['id']; ?>" <?php echo (isset($_POST['course_id']) && (int)$_POST['course_id'] === (int)$c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" id="semester_id" class="form-select">
                                <option value="">-- Select Semester --</option>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" class="form-control password-field" required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" class="form-control password-field" required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Register</button>
                            <a href="../student/login.php" class="btn btn-outline-secondary">Back to Login</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const targetName = this.getAttribute('data-target');
            const input = document.querySelector(`input[name="${targetName}"]`);
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Dynamic semester loading based on course selection
    const semestrData = <?php echo json_encode($semesters); ?>;
    const courseSelect = document.getElementById('course_id');
    const semesterSelect = document.getElementById('semester_id');

    function updateSemesters() {
        const selectedCourseId = parseInt(courseSelect.value);
        semesterSelect.innerHTML = '<option value="">-- Select Semester --</option>';

        if (selectedCourseId > 0) {
            const courseSemesters = semestrData.filter(s => s.course_id === selectedCourseId);
            courseSemesters.forEach(sem => {
                const option = document.createElement('option');
                option.value = sem.id;
                option.textContent = sem.name;
                semesterSelect.appendChild(option);
            });
        }
    }

    courseSelect.addEventListener('change', updateSemesters);

    // Initialize semesters if course is already selected
    if (courseSelect.value) {
        updateSemesters();
    }
</script>
</body>
</html>
