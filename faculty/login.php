<?php
// faculty/login.php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $pdo = Database::getInstance();

        $sql = "SELECT u.*, r.name AS role_name
                FROM users u
                JOIN roles r ON r.id = u.role_id
                JOIN faculty_profiles fp ON fp.user_id = u.id
                WHERE fp.employee_code = :employee_id
                  AND r.name = 'faculty'
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':employee_id' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['status'] === 'pending') {
                $error = 'Your account is pending admin approval. Please wait for approval to login.';
            } elseif ($user['status'] === 'inactive') {
                $error = 'Your account is inactive. Please contact admin.';
            } elseif (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['role']    = $user['role_name'];

                $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")
                    ->execute([':id' => $user['id']]);

                header('Location: ' . BASE_URL . '/faculty/index.php');
                exit;
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Login - College Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-header text-center py-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                    <h4 class="mb-0 text-white fw-bold">
                        <i class="fas fa-chalkboard-user me-2"></i>Faculty Login
                    </h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    <form method="post" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Employee ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Enter your Employee ID" required autofocus>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control password-field" placeholder="Enter password" required>
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold">
                            <i class="fas fa-sign-in-alt me-2"></i>Login as Faculty
                        </button>
                    </form>
                    <div class="mt-3 text-center">
                        <a href="<?php echo BASE_URL; ?>/faculty/register.php" class="btn btn-outline-primary">Register as Faculty</a>
                    </div>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">
                        <i class="fas fa-arrow-left me-1"></i>
                        <a href="<?php echo BASE_URL; ?>/index.php" class="text-decoration-none">Back to Home</a>
                    </small>
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