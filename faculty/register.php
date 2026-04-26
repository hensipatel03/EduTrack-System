<?php
// faculty/register.php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = Database::getInstance();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if ($firstName === '' || $lastName === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Generate unique employee ID
            $employeeId = generateEmployeeId($pdo);
            
            // uniqueness checks
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                throw new RuntimeException('Email already exists.');
            }

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $employeeId]);
            if ($stmt->fetch()) {
                throw new RuntimeException('Employee ID already exists. Please try again.');
            }

            // Check if employee ID already exists (though unlikely)
            $stmt = $pdo->prepare("SELECT id FROM faculty_profiles WHERE employee_code = :employee_code LIMIT 1");
            $stmt->execute([':employee_code' => $employeeId]);
            if ($stmt->fetch()) {
                throw new RuntimeException('Employee ID generation conflict. Please try again.');
            }
            
            // Get faculty role id
            $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'faculty' LIMIT 1");
            $stmt->execute();
            $role = $stmt->fetch();
            if (!$role) {
                throw new RuntimeException('Faculty role not found.');
            }
            $roleId = (int)$role['id'];

            $pdo->beginTransaction();

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            error_log('Attempting to insert faculty: email=' . $email . ', employee=' . $employeeId . ', role_id=' . $roleId);
            
            $insert = $pdo->prepare("INSERT INTO users (role_id, username, email, password_hash, full_name, first_name, last_name, status, created_at, updated_at)
                VALUES (:role_id, :username, :email, :password_hash, :full_name, :first_name, :last_name, 'pending', NOW(), NOW())");
            $insert->execute([
                ':role_id' => $roleId,
                ':username' => $employeeId,
                ':email' => $email,
                ':password_hash' => $passwordHash,
                ':full_name' => $firstName . ' ' . $lastName,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
            ]);
            
            error_log('Faculty user insert executed, checking affected rows...');

            $userId = (int)$pdo->lastInsertId();
            
            if (!$userId) {
                throw new RuntimeException('Failed to get user ID from insert.');
            }

            $insertProf = $pdo->prepare("INSERT INTO faculty_profiles (user_id, employee_code, designation, created_at, updated_at)
                VALUES (:user_id, :employee_code, NULL, NOW(), NOW())");
            $insertProf->execute([
                ':user_id' => $userId,
                ':employee_code' => $employeeId,
            ]);

            $pdo->commit();
            error_log('Faculty registered successfully: User ID=' . $userId . ', Employee ID=' . $employeeId);
            $success = 'Registration successful. Your Employee ID is: <strong>' . $employeeId . '</strong>. Your account is pending admin approval. You will be able to login once approved.';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Registration failed: ' . $e->getMessage();
            // Log error for debugging
            error_log('Faculty Registration Error: ' . $e->getMessage() . ' | Code: ' . $e->getCode());
            error_log('Stack trace: ' . $e->getTraceAsString());
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculty Registration</title>
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
                    <h4 class="card-title mb-3">Faculty Registration</h4>

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
                            <button type="submit" class="btn btn-primary">Register</button>
                            <a href="../faculty/login.php" class="btn btn-outline-secondary">Back to Login</a>
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
</script>
</body>
</html>
