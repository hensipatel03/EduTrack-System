<?php
// faculty/calendar.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$facultyId = (int)$user['id'];

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$startDate = sprintf('%04d-%02d-01', $year, $month);
$endDate   = date('Y-m-t', strtotime($startDate));

$sql = "SELECT * FROM calendar_events
        WHERE (user_id IS NULL OR user_id = :uid)
          AND start_date BETWEEN :start AND :end
        ORDER BY start_date";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':uid'   => $facultyId,
    ':start' => $startDate,
    ':end'   => $endDate,
]);
$events = $stmt->fetchAll();

$sqlAssignments = "SELECT a.id, a.title, a.description, a.due_date AS start_date, 'assignment' AS type, s.name AS subject_name
                   FROM assignments a
                   JOIN subjects s ON s.id = a.subject_id
                   WHERE a.due_date BETWEEN :start AND :end
                     AND a.faculty_user_id = :fid
                   ORDER BY a.due_date";
$stmtA = $pdo->prepare($sqlAssignments);
$stmtA->execute([':start' => $startDate, ':end' => $endDate, ':fid' => $facultyId]);
$assignments = $stmtA->fetchAll();

$planner = [];

$allEvents = array_merge($events, $assignments, $planner);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Calendar (<?php echo $month . '/' . $year; ?>)</h3>

<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <select name="month" class="form-select">
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo $m === $month ? 'selected' : ''; ?>>
                    <?php echo $m; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="year" class="form-select">
            <?php for ($y = $year - 1; $y <= $year + 1; $y++): ?>
                <option value="<?php echo $y; ?>" <?php echo $y === $year ? 'selected' : ''; ?>>
                    <?php echo $y; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Go</button>
    </div>
</form>

<table class="table table-bordered table-sm align-middle">
    <thead class="table-light">
    <tr>
        <th>Date</th>
        <th>Title</th>
        <th>Type</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($allEvents): ?>
        <?php foreach ($allEvents as $e): ?>
            <tr>
                <td><?php echo htmlspecialchars($e['start_date']); ?></td>
                <td><?php echo htmlspecialchars($e['title']); ?></td>
                <td><?php echo htmlspecialchars($e['type']); ?></td>
                <td><?php echo htmlspecialchars($e['description'] ?? $e['subject_name'] ?? ''); ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="4" class="text-center">No events this month.</td></tr>
    <?php endif; ?>
    </tbody>
</table>


