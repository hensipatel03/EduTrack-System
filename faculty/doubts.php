<?php
// faculty/doubts.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$facultyId = (int)$user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doubtId = isset($_POST['doubt_id']) ? (int)$_POST['doubt_id'] : 0;
    $message = trim($_POST['message'] ?? '');
    $action  = $_POST['action'] ?? '';

    if ($doubtId > 0 && $message !== '' && $action === 'reply') {
        $sql = "INSERT INTO doubt_replies (doubt_id, user_id, message)
                VALUES (:doubt_id, :uid, :message)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':doubt_id' => $doubtId,
            ':uid'      => $facultyId,
            ':message'  => $message,
        ]);
    } elseif ($doubtId > 0 && $action === 'resolve') {
        $sql = "UPDATE doubts d
                JOIN subjects s ON s.id = d.subject_id
                JOIN faculty_subjects fs ON fs.subject_id = s.id
                SET d.status = 'resolved', d.resolved_at = NOW()
                WHERE d.id = :id AND fs.faculty_user_id = :fid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id'  => $doubtId,
            ':fid' => $facultyId,
        ]);
    }

    header('Location: doubts.php');
    exit;
}

$sql = "SELECT d.*, u.first_name, u.last_name, s.name AS subject_name
        FROM doubts d
        JOIN users u ON u.id = d.student_user_id
        JOIN subjects s ON s.id = d.subject_id
        JOIN faculty_subjects fs ON fs.subject_id = s.id
        WHERE fs.faculty_user_id = :fid
        ORDER BY d.status ASC, d.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':fid' => $facultyId]);
$doubts = $stmt->fetchAll();

// Replies grouped by doubt
$replySql = "SELECT dr.*, u.first_name, u.last_name
             FROM doubt_replies dr
             JOIN users u ON u.id = dr.user_id
             WHERE dr.doubt_id = :did
             ORDER BY dr.created_at ASC";
$stmtReply = $pdo->prepare($replySql);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<h3 class="mb-3">Student Doubts</h3>

<?php if ($doubts): ?>
    <?php foreach ($doubts as $d): ?>
        <div class="card mb-3 <?php echo $d['status'] === 'resolved' ? 'border-success' : ''; ?>">
            <div class="card-header d-flex justify-content-between">
                <div>
                    <strong><?php echo htmlspecialchars($d['title']); ?></strong>
                    <div class="small text-muted">
                        Subject: <?php echo htmlspecialchars($d['subject_name']); ?> |
                        By: <?php echo htmlspecialchars($d['first_name'] . ' ' . $d['last_name']); ?> |
                        <?php echo htmlspecialchars($d['created_at']); ?>
                    </div>
                </div>
                <span class="badge bg-<?php echo $d['status'] === 'resolved' ? 'success' : 'warning'; ?>">
                    <?php echo htmlspecialchars(ucfirst($d['status'])); ?>
                </span>
            </div>
            <div class="card-body">
                <p><?php echo nl2br(htmlspecialchars($d['question'])); ?></p>

                <hr>
                <h6>Discussion</h6>
                <div class="mb-2" style="max-height: 200px; overflow-y: auto;">
                    <?php
                    $stmtReply->execute([':did' => $d['id']]);
                    $replies = $stmtReply->fetchAll();
                    if ($replies):
                        foreach ($replies as $r):
                            ?>
                            <div class="mb-2">
                                <strong><?php echo htmlspecialchars($r['first_name'] . ' ' . $r['last_name']); ?></strong>
                                <span class="text-muted small"><?php echo htmlspecialchars($r['created_at']); ?></span>
                                <div><?php echo nl2br(htmlspecialchars($r['message'])); ?></div>
                            </div>
                        <?php
                        endforeach;
                    else:
                        ?>
                        <div class="text-muted">No replies yet.</div>
                    <?php endif; ?>
                </div>

                <?php if ($d['status'] !== 'resolved'): ?>
                    <form method="post" class="mt-2">
                        <input type="hidden" name="doubt_id" value="<?php echo $d['id']; ?>">
                        <div class="mb-2">
                            <label class="form-label">Your Reply</label>
                            <textarea name="message" class="form-control" rows="2" required></textarea>
                        </div>
                        <button type="submit" name="action" value="reply" class="btn btn-primary btn-sm">
                            Reply
                        </button>
                        <button type="submit" name="action" value="resolve" class="btn btn-success btn-sm ms-2"
                                onclick="return confirm('Mark this doubt as resolved?');">
                            Mark Resolved
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-info">No doubts raised for your subjects yet.</div>
<?php endif; ?>

