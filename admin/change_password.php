<?php
// admin/change_password.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('admin');
$pdo  = Database::getInstance();
$user = getCurrentUser();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password === '' || $new_password === '' || $confirm_password === '') {
        $error = 'All fields are required.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute([':id' => $user['id']]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($current_password, $row['password_hash'])) {
            $error = 'Current password is incorrect.';
        } else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmtUp = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $stmtUp->execute([
                ':hash' => $new_hash,
                ':id'   => $user['id'],
            ]);
            $success = 'Password changed successfully.';
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Change Password</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label">Current Password</label>
        <input type="password" name="current_password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="new_password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Change Password</button>
</form>

