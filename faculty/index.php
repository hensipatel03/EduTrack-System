<?php
// faculty/index.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('faculty');
$pdo  = Database::getInstance();
$user = getCurrentUser();
$facultyId = (int)$user['id'];

// Correctly fetch counters using prepared statements and fetchColumn
$stmt = $pdo->prepare("SELECT COUNT(*) FROM faculty_subjects WHERE faculty_user_id = :fid");
$stmt->execute([':fid' => $facultyId]);
$totalSubjects = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM study_materials WHERE faculty_user_id = :fid");
$stmt->execute([':fid' => $facultyId]);
$totalMaterials = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE faculty_user_id = :fid");
$stmt->execute([':fid' => $facultyId]);
$totalAssignments = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*)
                       FROM doubts d
                       JOIN subjects s ON s.id = d.subject_id
                       JOIN faculty_subjects fs ON fs.subject_id = s.id
                       WHERE fs.faculty_user_id = :fid AND d.status = 'open'");
$stmt->execute([':fid' => $facultyId]);
$pendingDoubts = (int)$stmt->fetchColumn();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0 fw-bold"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Faculty Dashboard</h1>
            <p class="text-muted small mb-0">Welcome, <?php echo htmlspecialchars($user['first_name'] ?? $user['full_name'] ?? ''); ?> — overview of your courses and tasks.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <a class="text-decoration-none" href="<?php echo BASE_URL; ?>/faculty/subjects.php">
                <div class="card border-0 shadow-sm h-100 transition-transform">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Assigned Subjects</p>
                            <h3 class="mb-0 fw-bold"><?php echo $totalSubjects; ?></h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-book-reader text-info fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <small class="text-info">Manage Subjects →</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a class="text-decoration-none" href="<?php echo BASE_URL; ?>/faculty/materials.php">
                <div class="card border-0 shadow-sm h-100 transition-transform">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Study Materials</p>
                            <h3 class="mb-0 fw-bold"><?php echo $totalMaterials; ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-folder-open text-success fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <small class="text-success">Upload & Manage →</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a class="text-decoration-none" href="<?php echo BASE_URL; ?>/faculty/assignments.php">
                <div class="card border-0 shadow-sm h-100 transition-transform">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Assignments</p>
                            <h3 class="mb-0 fw-bold"><?php echo $totalAssignments; ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-file-alt text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <small class="text-primary">Create & Review →</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a class="text-decoration-none" href="<?php echo BASE_URL; ?>/faculty/doubts.php">
                <div class="card border-0 shadow-sm h-100 transition-transform">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1">Pending Doubts</p>
                            <h3 class="mb-0 fw-bold"><?php echo $pendingDoubts; ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-question-circle text-warning fa-2x"></i>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <small class="text-warning">View & Respond →</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">Quick Actions</div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?php echo BASE_URL; ?>/faculty/materials.php" class="btn btn-outline-primary">Upload Material</a>
                        <a href="<?php echo BASE_URL; ?>/faculty/assignments.php" class="btn btn-outline-success">Create Assignment</a>
                        <a href="<?php echo BASE_URL; ?>/faculty/doubts.php" class="btn btn-outline-warning">View Doubts</a>
                        <a href="<?php echo BASE_URL; ?>/faculty/assignments.php" class="btn btn-outline-info">View Submissions</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header">Profile</div>
                <div class="card-body">
                    <p class="mb-1"><strong><?php echo htmlspecialchars($user['full_name'] ?? ($user['first_name'] . ' ' . $user['last_name'] ?? '')); ?></strong></p>
                    <p class="text-muted small mb-0">Email: <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    <p class="text-muted small mb-0">Role: <?php echo htmlspecialchars($user['role_name'] ?? 'faculty'); ?></p>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .transition-transform { transition: transform .18s ease, box-shadow .18s ease; }
    .transition-transform:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
</style>

