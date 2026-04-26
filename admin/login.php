<?php
// admin/login.php
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
                WHERE (u.username = :username1 OR u.email = :username2) 
                  AND u.status = 'active'
                  AND r.name = 'admin'
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username1' => $username, ':username2' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Auth success
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role']    = $user['role_name'];

            // Update last_login_at
            $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id")
                ->execute([':id' => $user['id']]);

            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - College Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-4 col-md-5">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-header text-center py-4" style="background: linear-gradient(135deg,#c11b37ff 0%, #c11b37ff 100%);">
                    <h4 class="mb-0 text-white fw-bold">
                        <i class="fas fa-shield-alt me-2"></i>Admin Login
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
                            <label class="form-label fw-bold">Admin Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Enter username or email" required autofocus>
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
                        <button type="submit" class="btn w-100 btn-lg fw-bold" style="background-color: #c11b37ff; border-color: #c11b37ff; color: #ffffff;">
                            <i class="fas fa-sign-in-alt me-2"></i>Login as Admin
                        </button>
                    </form>
                </div>
                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">
                        <i class="fas fa-arrow-left me-1"></i>
                        <a href="../index.php" class="text-decoration-none">Back to Home</a>
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