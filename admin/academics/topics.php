<?php
// admin/academics/topics.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$subjectId = isset($_GET['subject_id']) ? (int)$_GET['subject_id'] : 0;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? null;

$topic = [
    'title' => '',
    'description' => '',
    'order_no' => 1,
];

$error = '';
$success = '';

if ($action === 'delete' && $id > 0) {
    $pdo->prepare("DELETE FROM topics WHERE id = :id")->execute([':id' => $id]);
    header('Location: topics.php?subject_id=' . $subjectId);
    exit;
}

if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM topics WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $topic = [
            'title' => $row['title'],
            'description' => $row['description'] ?? '',
            'order_no' => $row['order_no'] ?? 1,
        ];
        $subjectId = (int)$row['subject_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $topic['title'] = trim($_POST['title'] ?? '');
    $topic['description'] = trim($_POST['description'] ?? '');
    $topic['order_no'] = (int)($_POST['order_no'] ?? 1);

    if ($topic['title'] === '') {
        $error = 'Title is required.';
    } elseif ($subjectId <= 0) {
        $error = 'Subject is required.';
    } else {
        try {
            if ($id > 0) {
                $sql = "UPDATE topics SET title = :title, description = :description, order_no = :order_no WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $topic['title'],
                    ':description' => $topic['description'] !== '' ? $topic['description'] : null,
                    ':order_no' => $topic['order_no'],
                    ':id' => $id,
                ]);
                $success = 'Topic updated successfully.';
            } else {
                $sql = "INSERT INTO topics (subject_id, title, description, order_no) VALUES (:subject_id, :title, :description, :order_no)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':subject_id' => $subjectId,
                    ':title' => $topic['title'],
                    ':description' => $topic['description'] !== '' ? $topic['description'] : null,
                    ':order_no' => $topic['order_no'],
                ]);
                $success = 'Topic created successfully.';
                $id = (int)$pdo->lastInsertId();
            }
        } catch (Throwable $e) {
            $error = 'Error saving topic.';
        }
    }
}

// Get subjects for dropdown
$subjects = $pdo->query("SELECT id, name, code FROM subjects WHERE status = 'active' ORDER BY name")->fetchAll();

// Get current subject
$currentSubject = null;
if ($subjectId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = :id");
    $stmt->execute([':id' => $subjectId]);
    $currentSubject = $stmt->fetch();
}

// Get topics for current subject
$topics = [];
if ($subjectId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM topics WHERE subject_id = :sid ORDER BY order_no, id");
    $stmt->execute([':sid' => $subjectId]);
    $topics = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3">Manage Topics</h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="get" class="mb-3">
    <label class="form-label">Select Subject</label>
    <select name="subject_id" class="form-select" onchange="this.form.submit()">
        <option value="">-- Select Subject --</option>
        <?php foreach ($subjects as $s): ?>
            <option value="<?php echo $s['id']; ?>" <?php echo $subjectId == $s['id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($s['name'] . ' (' . $s['code'] . ')'); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($currentSubject): ?>
    <h4>Topics for: <?php echo htmlspecialchars($currentSubject['name']); ?></h4>

    <form method="post" class="mb-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($topic['title']); ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Order</label>
                <input type="number" name="order_no" class="form-control" value="<?php echo $topic['order_no']; ?>" min="1">
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($topic['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><?php echo $id > 0 ? 'Update' : 'Add'; ?> Topic</button>
        <?php if ($id > 0): ?>
            <a href="topics.php?subject_id=<?php echo $subjectId; ?>" class="btn btn-secondary">New Topic</a>
        <?php endif; ?>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Order</th>
                <th>Title</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($topics as $t): ?>
                <tr>
                    <td><?php echo $t['order_no']; ?></td>
                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                    <td><?php echo htmlspecialchars($t['description'] ?? ''); ?></td>
                    <td>
                        <a href="?subject_id=<?php echo $subjectId; ?>&id=<?php echo $t['id']; ?>&action=edit" class="btn btn-sm btn-warning">Edit</a>
                        <a href="?subject_id=<?php echo $subjectId; ?>&id=<?php echo $t['id']; ?>&action=delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this topic?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

