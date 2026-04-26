<?php
// register.php - Combined registration for Faculty & Student
declare(strict_types=1);

session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

$pdo = Database::getInstance();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roleName = $_POST['role'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!in_array($roleName, ['faculty', 'student'], true)) {
        $error = 'Invalid role selected.';
    } elseif ($firstName === '' || $lastName === '' || $username === '' || $email === '') {
        $error = 'Please fill required fields.';
    } elseif ($password === '' || $confirm === '') {
        $error = 'Password fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Find role id
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = :name LIMIT 1");
            $stmt->execute([':name' => $roleName]);
            $r = $stmt->fetch();
            if (!$r) {
                $error = 'Role not found. Please contact admin.';
            } else {
                $roleId = (int)$r['id'];

                // Additional checks
                if ($roleName === 'student') {
                    $enrollment = trim($_POST['enrollment_no'] ?? '');
                    if ($enrollment === '') {
                        throw new RuntimeException('Enrollment number is required for students.');
                    }
                } else {
                    $employeeCode = trim($_POST['employee_code'] ?? '');
                }

                $pdo->beginTransaction();

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $insertUser = $pdo->prepare("INSERT INTO users (role_id, username, email, password_hash, full_name, first_name, last_name, status, created_at, updated_at)
                    VALUES (:role_id, :username, :email, :password_hash, :full_name, :first_name, :last_name, 'pending', NOW(), NOW())");
                $insertUser->execute([
                    ':role_id' => $roleId,
                    ':username' => $username,
                    ':email' => $email,
                    ':password_hash' => $passwordHash,
                    ':full_name' => $firstName . ' ' . $lastName,
                    ':first_name' => $firstName,
                    ':last_name' => $lastName,
                ]);

                $newUserId = (int)$pdo->lastInsertId();

                if ($roleName === 'student') {
                    $stmtProf = $pdo->prepare("INSERT INTO student_profiles (user_id, enrollment_no, created_at, updated_at)
                        VALUES (:user_id, :enrollment_no, NOW(), NOW())");
                    $stmtProf->execute([
                        ':user_id' => $newUserId,
                        ':enrollment_no' => $enrollment,
                    ]);
                } else {
                    $stmtProf = $pdo->prepare("INSERT INTO faculty_profiles (user_id, employee_code, created_at, updated_at)
                        VALUES (:user_id, :employee_code, NOW(), NOW())");
                    $stmtProf->execute([
                        ':user_id' => $newUserId,
                        ':employee_code' => $employeeCode !== '' ? $employeeCode : null,
                    ]);
                }

                $pdo->commit();
                $success = 'Registration successful. Your account is pending admin approval. You will be able to login once approved.';
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Registration failed. ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - Edu Track System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: #f7f7fb; padding: 40px;">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Register (Faculty / Student)</h4>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="post" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="faculty">Faculty</option>
                                <option value="student">Student</option>
                            </select>
                        </div>

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

                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Enrollment No (Students)</label>
                            <input type="text" name="enrollment_no" class="form-control" placeholder="Only for students">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Employee Code (Faculty)</label>
                            <input type="text" name="employee_code" class="form-control" placeholder="Only for faculty">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Register</button>
                            <a href="login.php" class="btn btn-outline-secondary">Back to Login</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
