<?php
// login.php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['user_id'])) {
    redirectToRoleHome();
}

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
                WHERE (u.username = :username OR u.email = :username)
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
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

                redirectToRoleHome();
            } else {
                $error = 'Invalid credentials.';
            }
        } else {
            $error = 'Invalid credentials.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-4 col-md-5">
        <div class="card shadow-lg border-0 login-card">
            <div class="card-header text-center py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h4 class="mb-0 text-white fw-bold">
                    <i class="fas fa-sign-in-alt me-2"></i>General Login
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
                        <label class="form-label fw-bold">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="Enter username or email" required>
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
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Select your portal from the <a href="<?php echo BASE_URL; ?>/index.php" class="text-decoration-none">home page</a>
                </small>
            </div>
        </div>
    </div>
</div>

