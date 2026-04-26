<?php
// admin/users/student_form.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

// Get student role_id
$stmtRole = $pdo->prepare("SELECT id FROM roles WHERE name = 'student' LIMIT 1");
$stmtRole->execute();
$role = $stmtRole->fetch();
if (!$role) {
    die('Student role not found. Please check roles table.');
}
$studentRoleId = (int)$role['id'];

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$error   = '';
$success = '';

$student = [
    'first_name'    => '',
    'last_name'     => '',
    'username'      => '',
    'email'         => '',
    'mobile'        => '',
    'gender'        => '',
    'dob'           => '',
    'address'       => '',
    'status'        => 'active',
    'enrollment_no' => '',
    'course_id'     => '',
    'branch_id'     => '',
    'semester_id'   => '',
];

if ($isEdit) {
    $sql = "SELECT u.*, sp.enrollment_no, sp.course_id, sp.branch_id, sp.semester_id
            FROM users u
            JOIN roles r ON r.id = u.role_id
            LEFT JOIN student_profiles sp ON sp.user_id = u.id
            WHERE u.id = :id AND r.name = 'student'
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        die('Student not found.');
    }

    $student = [
        'first_name'    => $row['first_name'],
        'last_name'     => $row['last_name'],
        'username'      => $row['username'],
        'email'         => $row['email'],
        'mobile'        => $row['mobile'] ?? '',
        'gender'        => $row['gender'] ?? '',
        'dob'           => $row['dob'] ?? '',
        'address'       => $row['address'] ?? '',
        'status'        => $row['status'] ?? 'active',
        'enrollment_no' => $row['enrollment_no'] ?? '',
        'course_id'     => $row['course_id'] ?? '',
        'branch_id'     => $row['branch_id'] ?? '',
        'semester_id'   => $row['semester_id'] ?? '',
    ];
}

// Load dropdown data
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student['first_name']    = trim($_POST['first_name'] ?? '');
    $student['last_name']     = trim($_POST['last_name'] ?? '');
    $student['username']      = trim($_POST['username'] ?? '');
    $student['email']         = trim($_POST['email'] ?? '');
    $student['mobile']        = trim($_POST['mobile'] ?? '');
    $student['gender']        = $_POST['gender'] ?? '';
    $student['dob']           = $_POST['dob'] ?? '';
    $student['address']       = trim($_POST['address'] ?? '');
    $student['status']        = $_POST['status'] ?? 'active';
    $student['enrollment_no'] = trim($_POST['enrollment_no'] ?? '');
    $student['course_id']     = $_POST['course_id'] !== '' ? (int)$_POST['course_id'] : null;
    $student['branch_id']     = $_POST['branch_id'] !== '' ? (int)$_POST['branch_id'] : null;
    $student['semester_id']   = $_POST['semester_id'] !== '' ? (int)$_POST['semester_id'] : null;

    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($student['first_name'] === '' || $student['last_name'] === '' ||
        $student['username'] === '' || $student['email'] === '' || $student['enrollment_no'] === '') {
        $error = 'First name, last name, username, email and enrollment no are required.';
    } elseif (!$isEdit && ($password === '' || $confirmPassword === '')) {
        $error = 'Password and confirm password are required for new student.';
    } elseif (!$isEdit && $password !== $confirmPassword) {
        $error = 'Password and confirm password do not match.';
    } else {
        try {
            if ($isEdit) {
                $pdo->beginTransaction();

                $sqlUser = "UPDATE users SET
                                first_name = :first_name,
                                last_name  = :last_name,
                                username   = :username,
                                email      = :email,
                                mobile     = :mobile,
                                gender     = :gender,
                                dob        = :dob,
                                address    = :address,
                                status     = :status
                            WHERE id = :id";
                $stmtUser = $pdo->prepare($sqlUser);
                $stmtUser->execute([
                    ':first_name' => $student['first_name'],
                    ':last_name'  => $student['last_name'],
                    ':username'   => $student['username'],
                    ':email'      => $student['email'],
                    ':mobile'     => $student['mobile'] !== '' ? $student['mobile'] : null,
                    ':gender'     => $student['gender'] !== '' ? $student['gender'] : null,
                    ':dob'        => $student['dob'] !== '' ? $student['dob'] : null,
                    ':address'    => $student['address'] !== '' ? $student['address'] : null,
                    ':status'     => $student['status'],
                    ':id'         => $id,
                ]);

                $sqlProf = "INSERT INTO student_profiles
                            (user_id, enrollment_no, course_id, branch_id, semester_id)
                            VALUES (:user_id, :enrollment_no, :course_id, :branch_id, :semester_id)
                            ON DUPLICATE KEY UPDATE
                                enrollment_no = VALUES(enrollment_no),
                                course_id     = VALUES(course_id),
                                branch_id     = VALUES(branch_id),
                                semester_id   = VALUES(semester_id)";
                $stmtProf = $pdo->prepare($sqlProf);
                $stmtProf->execute([
                    ':user_id'       => $id,
                    ':enrollment_no' => $student['enrollment_no'],
                    ':course_id'     => $student['course_id'],
                    ':branch_id'     => $student['branch_id'],
                    ':semester_id'   => $student['semester_id'],
                ]);

                $pdo->commit();
                $success = 'Student updated successfully.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $pdo->beginTransaction();

                $sqlUser = "INSERT INTO users
                            (role_id, username, email, password_hash, full_name, first_name, last_name,
                             mobile, gender, dob, address, status)
                            VALUES
                            (:role_id, :username, :email, :password_hash, :full_name, :first_name, :last_name,
                             :mobile, :gender, :dob, :address, :status)";
                $stmtUser = $pdo->prepare($sqlUser);
                $stmtUser->execute([
                    ':role_id'       => $studentRoleId,
                    ':username'      => $student['username'],
                    ':email'         => $student['email'],
                    ':password_hash' => $passwordHash,
                    ':full_name'     => $student['first_name'] . ' ' . $student['last_name'],
                    ':first_name'    => $student['first_name'],
                    ':last_name'     => $student['last_name'],
                    ':mobile'        => $student['mobile'] !== '' ? $student['mobile'] : null,
                    ':gender'        => $student['gender'] !== '' ? $student['gender'] : null,
                    ':dob'           => $student['dob'] !== '' ? $student['dob'] : null,
                    ':address'       => $student['address'] !== '' ? $student['address'] : null,
                    ':status'        => $student['status'],
                ]);
                $newUserId = (int)$pdo->lastInsertId();

                $sqlProf = "INSERT INTO student_profiles
                            (user_id, enrollment_no, course_id, branch_id, semester_id)
                            VALUES (:user_id, :enrollment_no, :course_id, :branch_id, :semester_id)";
                $stmtProf = $pdo->prepare($sqlProf);
                $stmtProf->execute([
                    ':user_id'       => $newUserId,
                    ':enrollment_no' => $student['enrollment_no'],
                    ':course_id'     => $student['course_id'],
                    ':branch_id'     => $student['branch_id'],
                    ':semester_id'   => $student['semester_id'],
                ]);

                $pdo->commit();
                $success = 'Student created successfully.';
                $isEdit  = true;
                $id      = $newUserId;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Error saving student. Possibly duplicate username, email, or enrollment no.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3"><?php echo $isEdit ? 'Edit Student' : 'Add Student'; ?></h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" autocomplete="off">
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control"
                   value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control"
                   value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Enrollment No</label>
            <input type="text" name="enrollment_no" class="form-control"
                   value="<?php echo htmlspecialchars($student['enrollment_no']); ?>" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control"
                   value="<?php echo htmlspecialchars($student['username']); ?>" required <?php echo $isEdit ? 'readonly' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile" class="form-control"
                   value="<?php echo htmlspecialchars($student['mobile']); ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">Select</option>
                <option value="male" <?php echo $student['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo $student['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo $student['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control"
                   value="<?php echo htmlspecialchars($student['dob']); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php echo $student['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $student['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>

    <?php if (!$isEdit): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
        </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Course</label>
            <select name="course_id" class="form-select">
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>"
                        <?php echo (string)$student['course_id'] === (string)$c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Branch</label>
            <select name="branch_id" class="form-select">
                <option value="">-- Select Branch --</option>
                <?php foreach ($branches as $b): ?>
                    <option value="<?php echo $b['id']; ?>"
                        <?php echo (string)$student['branch_id'] === (string)$b['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($b['course_name'] . ' - ' . $b['name'] . ' (' . $b['code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Semester</label>
            <select name="semester_id" class="form-select">
                <option value="">-- Select Semester --</option>
                <?php foreach ($semesters as $s): ?>
                    <option value="<?php echo $s['id']; ?>"
                        <?php echo (string)$student['semester_id'] === (string)$s['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['course_name'] . ' - ' . $s['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="3"><?php
            echo htmlspecialchars($student['address']);
        ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update' : 'Create'; ?></button>
    <a href="student_list.php" class="btn btn-secondary ms-2">Back to List</a>
</form>

