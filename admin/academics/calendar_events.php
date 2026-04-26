<?php
// admin/academics/calendar_events.php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$error = '';
$success = '';

$event = [
    'title' => '',
    'description' => '',
    'start_date' => '',
    'end_date' => '',
    'type' => 'custom',
];

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM calendar_events WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    if ($row) {
        $event = [
            'title' => $row['title'],
            'description' => $row['description'] ?? '',
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'] ?? '',
            'type' => $row['type'] ?? 'custom',
        ];
    } else {
        die('Event not found.');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event['title'] = trim($_POST['title'] ?? '');
    $event['description'] = trim($_POST['description'] ?? '');
    $event['start_date'] = $_POST['start_date'] ?? '';
    $event['end_date'] = $_POST['end_date'] ?? '';
    $event['type'] = $_POST['type'] ?? 'custom';

    if ($event['title'] === '' || $event['start_date'] === '') {
        $error = 'Title and start date are required.';
    } else {
        try {
            if ($isEdit) {
                $sql = "UPDATE calendar_events SET
                            title = :title,
                            description = :description,
                            start_date = :start_date,
                            end_date = :end_date,
                            type = :type
                        WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $event['title'],
                    ':description' => $event['description'] !== '' ? $event['description'] : null,
                    ':start_date' => $event['start_date'],
                    ':end_date' => $event['end_date'] !== '' ? $event['end_date'] : null,
                    ':type' => $event['type'],
                    ':id' => $id,
                ]);
                $success = 'Event updated successfully.';
            } else {
                $sql = "INSERT INTO calendar_events (title, description, start_date, end_date, type)
                        VALUES (:title, :description, :start_date, :end_date, :type)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $event['title'],
                    ':description' => $event['description'] !== '' ? $event['description'] : null,
                    ':start_date' => $event['start_date'],
                    ':end_date' => $event['end_date'] !== '' ? $event['end_date'] : null,
                    ':type' => $event['type'],
                ]);
                $success = 'Event created successfully.';
                $isEdit = true;
                $id = (int)$pdo->lastInsertId();
            }
        } catch (Throwable $e) {
            $error = 'Error saving event.';
        }
    }
}

// Handle delete action
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM calendar_events WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
        $success = 'Event deleted successfully.';
        // Reset form if deleting the current event
        if ($deleteId === $id) {
            $isEdit = false;
            $id = 0;
            $event = [
                'title' => '',
                'description' => '',
                'start_date' => '',
                'end_date' => '',
                'type' => 'custom',
            ];
        }
    } catch (Throwable $e) {
        $error = 'Error deleting event.';
    }
}

// List all events
$events = $pdo->query("SELECT * FROM calendar_events ORDER BY start_date DESC")->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<h3 class="mb-3"><?php echo $isEdit ? 'Edit Event' : 'Add Event'; ?></h3>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form method="post">
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($event['title']); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
                <option value="custom" <?php echo $event['type'] === 'custom' ? 'selected' : ''; ?>>Custom</option>
                <option value="holiday" <?php echo $event['type'] === 'holiday' ? 'selected' : ''; ?>>Holiday</option>
                <option value="exam" <?php echo $event['type'] === 'exam' ? 'selected' : ''; ?>>Exam</option>
            </select>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($event['start_date']); ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">End Date (optional)</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($event['end_date']); ?>">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($event['description']); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><?php echo $isEdit ? 'Update' : 'Create'; ?> Event</button>
    <a href="?id=0" class="btn btn-secondary">New Event</a>
</form>

<hr>

<h4>All Events</h4>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Title</th>
            <th>Type</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($events as $e): ?>
            <tr>
                <td><?php echo htmlspecialchars($e['title']); ?></td>
                <td><?php echo htmlspecialchars($e['type']); ?></td>
                <td><?php echo htmlspecialchars($e['start_date']); ?></td>
                <td><?php echo htmlspecialchars($e['end_date'] ?? ''); ?></td>
                <td>
                    <a href="?id=<?php echo $e['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?delete=<?php echo $e['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

