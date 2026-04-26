<?php
// admin/academics/branches.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$action = $_GET['action'] ?? null;
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$branch = [
    'course_id'   => '',
    'name'        => '',
    'code'        => '',
    'description' => '',
    'status'      => 'active',
];

$error   = '';
$success = '';

$courses = $pdo->query("SELECT id, name, code FROM courses WHERE status = 'active' ORDER BY name")->fetchAll();
$branches_list = $pdo->query("SELECT DISTINCT b.name FROM branches b WHERE b.status = 'active' ORDER BY b.name")->fetchAll();

if ($action === 'delete' && $id > 0) {
    $del = $pdo->prepare("DELETE FROM branches WHERE id = :id");
    $del->execute([':id' => $id]);
    header('Location: branches.php');
    exit;
}

if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM branches WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $branch['course_id']   = $row['course_id'];
        $branch['name']        = $row['name'];
        $branch['code']        = $row['code'];
        $branch['description'] = $row['description'];
        $branch['status']      = $row['status'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid              = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $branch['course_id']   = $_POST['course_id'] !== '' ? (int)$_POST['course_id'] : null;
    $branch['name']        = trim($_POST['name'] ?? '');
    $branch['code']        = trim($_POST['code'] ?? '');
    $branch['description'] = trim($_POST['description'] ?? '');
    $branch['status']      = $_POST['status'] ?? 'active';

    if (!$branch['course_id'] || $branch['name'] === '' || $branch['code'] === '') {
        $error = 'Course, name and code are required.';
    } else {
        try {
            if ($bid > 0) {
                $sql = "UPDATE branches SET
                            course_id   = :course_id,
                            name        = :name,
                            code        = :code,
                            description = :description,
                            status      = :status
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':course_id'   => $branch['course_id'],
                    ':name'        => $branch['name'],
                    ':code'        => $branch['code'],
                    ':description' => $branch['description'] !== '' ? $branch['description'] : null,
                    ':status'      => $branch['status'],
                    ':id'          => $bid,
                ]);
                $success = 'Branch updated successfully.';
            } else {
                $sql = "INSERT INTO branches (course_id, name, code, description, status)
                        VALUES (:course_id, :name, :code, :description, :status)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':course_id'   => $branch['course_id'],
                    ':name'        => $branch['name'],
                    ':code'        => $branch['code'],
                    ':description' => $branch['description'] !== '' ? $branch['description'] : null,
                    ':status'      => $branch['status'],
                ]);
                $success = 'Branch added successfully.';
            }
        } catch (Throwable $e) {
            $error = 'Error saving branch. Possibly duplicate code for this course.';
        }
    }
}

$filter_branch = isset($_GET['filter_branch']) && $_GET['filter_branch'] !== ''
    ? $_GET['filter_branch']
    : null;

$sql = "SELECT b.*, c.name AS course_name, c.code AS course_code
        FROM branches b
        JOIN courses c ON c.id = b.course_id";
$conds = [];
$params = [];
if ($filter_branch !== null) {
    $conds[] = "b.name = :branch_name";
    $params[':branch_name'] = $filter_branch;
}
if ($conds) {
    $sql .= ' WHERE ' . implode(' AND ', $conds);
}
$sql .= " ORDER BY c.name, b.name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3">Branches</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post" class="mb-4">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Course</label>
            <select name="course_id" id="course_id" class="form-select" required>
                <option value="">-- Select Course --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?php echo $c['id']; ?>"
                        <?php echo (string)$branch['course_id'] === (string)$c['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name'] . ' (' . $c['code'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Branch Name</label>
            <div id="branch_input_container">
                <select name="name" id="branch_name" class="form-select" required>
                    <option value="">-- Select Branch --</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Branch Code</label>
            <input type="text" name="code" id="branch_code" class="form-control"
                   value="<?php echo htmlspecialchars($branch['code']); ?>" required>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4">
            <button type="button" id="toggle_create_mode" class="btn btn-outline-primary">Create New Branch</button>
            <button type="button" id="toggle_select_mode" class="btn btn-outline-secondary" style="display:none;">Select Existing Branch</button>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="active" <?php echo $branch['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $branch['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="2"><?php
            echo htmlspecialchars($branch['description'] ?? '');
        ?></textarea>
    </div>
    <div class="mb-3">
    <button type="submit" class="btn btn-primary"><?php echo $id > 0 ? 'Update' : 'Add'; ?> Branch</button>
    <?php if ($id > 0): ?>
        <a href="branches.php" class="btn btn-secondary ms-2">Cancel Edit</a>
    <?php endif; ?>
    </div>
</form>

<script>
    const courseSelect = document.getElementById('course_id');
    const branchNameSelect = document.getElementById('branch_name');
    const branchCodeInput = document.getElementById('branch_code');
    const branchInputContainer = document.getElementById('branch_input_container');
    const toggleCreateBtn = document.getElementById('toggle_create_mode');
    const toggleSelectBtn = document.getElementById('toggle_select_mode');
    
    const allBranches = <?php echo json_encode($pdo->query("SELECT id, name, code, course_id FROM branches WHERE status = 'active' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC)); ?>;
    
    let isCreateMode = false;
    
    // Toggle to Create Mode
    toggleCreateBtn.addEventListener('click', function() {
        isCreateMode = true;
        toggleCreateBtn.style.display = 'none';
        toggleSelectBtn.style.display = 'inline-block';
        
        branchInputContainer.innerHTML = '<input type="text" name="name" id="branch_name" class="form-control" placeholder="Enter new branch name" required>';
        branchCodeInput.value = '';
        branchCodeInput.removeAttribute('readonly');
    });
    
    // Toggle to Select Mode
    toggleSelectBtn.addEventListener('click', function() {
        isCreateMode = false;
        toggleSelectBtn.style.display = 'none';
        toggleCreateBtn.style.display = 'inline-block';
        
        branchInputContainer.innerHTML = '<select name="name" id="branch_name" class="form-select" required><option value="">-- Select Branch --</option></select>';
        branchCodeInput.value = '';
        
        // Re-initialize dropdown after creating new element
        setTimeout(function() {
            const newBranchSelect = document.getElementById('branch_name');
            newBranchSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const code = selectedOption.getAttribute('data-code');
                if (code) {
                    branchCodeInput.value = code;
                } else {
                    branchCodeInput.value = '';
                }
            });
            
            // Populate dropdown if course is selected
            if (courseSelect.value) {
                courseSelect.dispatchEvent(new Event('change'));
            }
        }, 0);
    });
    
    courseSelect.addEventListener('change', function() {
        if (isCreateMode) {
            return; // Don't populate dropdown in create mode
        }
        
        const branchSelect = document.getElementById('branch_name');
        if (!branchSelect) return;
        
        branchSelect.innerHTML = '<option value="">-- Select Branch --</option>';
        branchCodeInput.value = '';
        
        // Show all unique branches regardless of course selection
        const addedBranches = new Set();
        
        allBranches.forEach(branch => {
            if (!addedBranches.has(branch.name)) {
                const option = document.createElement('option');
                option.value = branch.name;
                option.setAttribute('data-code', branch.code);
                option.setAttribute('data-id', branch.id);
                option.textContent = branch.name;  // Show only branch name
                branchSelect.appendChild(option);
                addedBranches.add(branch.name);
            }
        });
    });
    
    // Event for branch selection in dropdown (select mode)
    document.addEventListener('change', function(e) {
        if (e.target.id === 'branch_name' && !isCreateMode) {
            const selectedOption = e.target.options[e.target.selectedIndex];
            const code = selectedOption.getAttribute('data-code');
            if (code) {
                branchCodeInput.value = code;
            } else {
                branchCodeInput.value = '';
            }
        }
    });
    
    // Initialize on page load if course is selected
    if (courseSelect.value && !isCreateMode) {
        courseSelect.dispatchEvent(new Event('change'));
    }
</script>

<form method="get" class="mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-auto">
            <label class="form-label">Filter Branch</label>
            <select name="filter_branch" class="form-select">
                <option value="">All Branches</option>
                <?php foreach ($branches_list as $br): ?>
                    <option value="<?php echo htmlspecialchars($br['name']); ?>" <?php echo $filter_branch === $br['name'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($br['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-secondary">Apply</button>
            <a href="branches.php" class="btn btn-outline-secondary ms-1">Reset</a>
        </div>
    </div>
</form>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Sr No</th>
        <th>Course</th>
        <th>Branch</th>
        <th>Code</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($rows): ?>
        <?php foreach ($rows as $i => $row): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo htmlspecialchars($row['course_name'] . ' (' . $row['course_code'] . ')'); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['code']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                <td>
                    <a href="branches.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="branches.php?action=delete&id=<?php echo $row['id']; ?>"
                       class="btn btn-sm btn-danger"
                       onclick="return confirm('Delete this branch? This may affect subjects.');">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7" class="text-center">No branches found.</td></tr>
    <?php endif; ?>
    </tbody>
</table>