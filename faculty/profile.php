<?php
// faculty/profile.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $gender     = $_POST['gender'] ?? null;
    $dob        = $_POST['dob'] ?? null;
    $address    = trim($_POST['address'] ?? '');

    if ($first_name === '' || $last_name === '' || $email === '') {
        $error = 'First name, last name, and email are required.';
    } else {
        try {
            $sql = "UPDATE users 
                    SET first_name = :first_name,
                        last_name  = :last_name,
                        email      = :email,
                        mobile     = :mobile,
                        gender     = :gender,
                        dob        = :dob,
                        address    = :address
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':first_name' => $first_name,
                ':last_name'  => $last_name,
                ':email'      => $email,
                ':mobile'     => $mobile !== '' ? $mobile : null,
                ':gender'     => $gender !== '' ? $gender : null,
                ':dob'        => $dob !== '' ? $dob : null,
                ':address'    => $address !== '' ? $address : null,
                ':id'         => $user['id'],
            ]);

            $success = 'Profile updated successfully.';
            $user = getCurrentUser();
        } catch (Throwable $e) {
            $error = 'Failed to update profile.';
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">My Profile</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control"
                   value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control"
                   value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile" class="form-control"
                   value="<?php echo htmlspecialchars($user['mobile'] ?? ''); ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">Select</option>
                <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control"
                   value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="3"><?php
            echo htmlspecialchars($user['address'] ?? '');
        ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Update Profile</button>
</form>

