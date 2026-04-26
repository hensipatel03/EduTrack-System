<?php
// admin/users/faculty_form.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

// Get faculty role_id
$stmtRole = $pdo->prepare("SELECT id FROM roles WHERE name = 'faculty' LIMIT 1");
$stmtRole->execute();
$role = $stmtRole->fetch();
if (!$role) {
    die('Faculty role not found. Please check roles table.');
}
$facultyRoleId = (int)$role['id'];

$id    = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$error   = '';
$success = '';

$faculty = [
    'first_name'    => '',
    'last_name'     => '',
    'username'      => '',
    'email'         => '',
    'mobile'        => '',
    'gender'        => '',
    'dob'           => '',
    'address'       => '',
    'status'        => 'active',
];

if ($isEdit) {
    $sql = "SELECT u.*, fp.employee_code, fp.designation 
            FROM users u
            JOIN roles r ON r.id = u.role_id
            LEFT JOIN faculty_profiles fp ON fp.user_id = u.id
            WHERE u.id = :id AND r.name = 'faculty'
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        die('Faculty not found.');
    }

    $faculty = [
        'first_name'    => $row['first_name'],
        'last_name'     => $row['last_name'],
        'username'      => $row['username'],
        'email'         => $row['email'],
        'mobile'        => $row['mobile'] ?? '',
        'gender'        => $row['gender'] ?? '',
        'dob'           => $row['dob'] ?? '',
        'address'       => $row['address'] ?? '',
        'status'        => $row['status'] ?? 'active',
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faculty['first_name']    = trim($_POST['first_name'] ?? '');
    $faculty['last_name']     = trim($_POST['last_name'] ?? '');
    $faculty['username']      = trim($_POST['username'] ?? '');
    $faculty['email']         = trim($_POST['email'] ?? '');
    $faculty['mobile']        = trim($_POST['mobile'] ?? '');
    $faculty['gender']        = $_POST['gender'] ?? '';
    $faculty['dob']           = $_POST['dob'] ?? '';
    $faculty['address']       = trim($_POST['address'] ?? '');
    $faculty['employee_code'] = trim($_POST['employee_code'] ?? '');
    $faculty['designation']   = trim($_POST['designation'] ?? '');
    $faculty['status']        = $_POST['status'] ?? 'active';

    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($faculty['first_name'] === '' || $faculty['last_name'] === '' || 
        $faculty['username'] === '' || $faculty['email'] === '') {
        $error = 'First name, last name, username, and email are required.';
    } elseif (!$isEdit && ($password === '' || $confirmPassword === '')) {
        $error = 'Password and confirm password are required for new faculty.';
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
                    ':first_name' => $faculty['first_name'],
                    ':last_name'  => $faculty['last_name'],
                    ':username'   => $faculty['username'],
                    ':email'      => $faculty['email'],
                    ':mobile'     => $faculty['mobile'] !== '' ? $faculty['mobile'] : null,
                    ':gender'     => $faculty['gender'] !== '' ? $faculty['gender'] : null,
                    ':dob'        => $faculty['dob'] !== '' ? $faculty['dob'] : null,
                    ':address'    => $faculty['address'] !== '' ? $faculty['address'] : null,
                    ':status'     => $faculty['status'],
                    ':id'         => $id,
                ]);

                $sqlProf = "INSERT INTO faculty_profiles (user_id, employee_code, designation)
                            VALUES (:user_id, :employee_code, :designation)
                            ON DUPLICATE KEY UPDATE
                                employee_code = VALUES(employee_code),
                                designation   = VALUES(designation)";
                $stmtProf = $pdo->prepare($sqlProf);
                $stmtProf->execute([
                    ':user_id'       => $id,
                    ':employee_code' => $faculty['employee_code'] !== '' ? $faculty['employee_code'] : null,
                    ':designation'   => $faculty['designation'] !== '' ? $faculty['designation'] : null,
                ]);

                $pdo->commit();
                $success = 'Faculty updated successfully.';
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
                    ':role_id'       => $facultyRoleId,
                    ':username'      => $faculty['username'],
                    ':email'         => $faculty['email'],
                    ':password_hash' => $passwordHash,
                    ':full_name'     => $faculty['first_name'] . ' ' . $faculty['last_name'],
                    ':first_name'    => $faculty['first_name'],
                    ':last_name'     => $faculty['last_name'],
                    ':mobile'        => $faculty['mobile'] !== '' ? $faculty['mobile'] : null,
                    ':gender'        => $faculty['gender'] !== '' ? $faculty['gender'] : null,
                    ':dob'           => $faculty['dob'] !== '' ? $faculty['dob'] : null,
                    ':address'       => $faculty['address'] !== '' ? $faculty['address'] : null,
                    ':status'        => $faculty['status'],
                ]);
                $newUserId = (int)$pdo->lastInsertId();

                $sqlProf = "INSERT INTO faculty_profiles (user_id, employee_code, designation)
                            VALUES (:user_id, :employee_code, :designation)";
                $stmtProf = $pdo->prepare($sqlProf);
                $stmtProf->execute([
                    ':user_id'       => $newUserId,
                    ':employee_code' => $faculty['employee_code'] !== '' ? $faculty['employee_code'] : null,
                    ':designation'   => $faculty['designation'] !== '' ? $faculty['designation'] : null,
                ]);

                $pdo->commit();
                $success = 'Faculty created successfully.';
                $isEdit  = true;
                $id      = $newUserId;
            }
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Error saving faculty. Possibly duplicate username or email.';
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3"><?php echo $isEdit ? 'Edit Faculty' : 'Add Faculty'; ?></h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" autocomplete="off">
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control"
                   value="<?php echo htmlspecialchars($faculty['first_name']); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control"
                   value="<?php echo htmlspecialchars($faculty['last_name']); ?>" required>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control"
                   value="<?php echo htmlspecialchars($faculty['username']); ?>" required <?php echo $isEdit ? 'readonly' : ''; ?>>
        </div>
        <div class="col-md-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?php echo htmlspecialchars($faculty['email']); ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Mobile</label>
            <input type="text" name="mobile" class="form-control"
                   value="<?php echo htmlspecialchars($faculty['mobile']); ?>">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-select">
                <option value="">Select</option>
                <option value="male" <?php echo $faculty['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo $faculty['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo $faculty['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control"
                   value="<?php echo htmlspecialchars($faculty['dob']); ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php echo $faculty['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $faculty['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
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

        <div class="mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="3"><?php
            echo htmlspecialchars($faculty['address']);
        ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update' : 'Create'; ?></button>
    <a href="faculty_list.php" class="btn btn-secondary ms-2">Back to List</a>
</form>

