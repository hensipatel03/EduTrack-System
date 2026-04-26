<?php
// admin/index.php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

requireLogin('admin');
$pdo = Database::getInstance();

// Counters
$totalUsers    = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalFaculty  = (int)$pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'faculty'")->fetchColumn();
$totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id = u.role_id WHERE r.name = 'student'")->fetchColumn();
$totalCourses  = (int)$pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalSubjects = (int)$pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="mb-4">
        <h1 class="display-5 fw-bold text-dark">
            <i class="fas fa-tachometer-alt text-primary me-3"></i>Admin Dashboard
        </h1>
        <p class="text-muted">Welcome back! Here's an overview of your system.</p>
    </div>

    <!-- Dashboard Stats Row -->
    <div class="row g-4 mb-5">
        <!-- Total Users Card -->
        <div class="col-lg-3 col-md-6">
            <a href="<?php echo BASE_URL; ?>/admin/users/faculty_list.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 transition-transform" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Users</p>
                                <h3 class="mb-0 fw-bold"><?php echo $totalUsers; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-users text-primary fa-2x"></i>
                            </div>
                        </div>
                        <small class="text-primary fw-bold mt-2">View All Users →</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Faculty Card -->
        <div class="col-lg-3 col-md-6">
            <a href="<?php echo BASE_URL; ?>/admin/users/faculty_list.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 transition-transform" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Faculty Members</p>
                                <h3 class="mb-0 fw-bold"><?php echo $totalFaculty; ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-chalkboard-user text-success fa-2x"></i>
                            </div>
                        </div>
                        <small class="text-success fw-bold mt-2">Manage Faculty →</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Students Card -->
        <div class="col-lg-3 col-md-6">
            <a href="<?php echo BASE_URL; ?>/admin/users/student_list.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 transition-transform" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Students</p>
                                <h3 class="mb-0 fw-bold"><?php echo $totalStudents; ?></h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-graduation-cap text-info fa-2x"></i>
                            </div>
                        </div>
                        <small class="text-info fw-bold mt-2">View Students →</small>
                    </div>
                </div>
            </a>
        </div>

        <!-- Academics Card -->
        <div class="col-lg-3 col-md-6">
            <a href="<?php echo BASE_URL; ?>/admin/academics/courses.php" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 transition-transform" style="cursor: pointer;">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Courses & Subjects</p>
                                <h3 class="mb-0 fw-bold"><?php echo $totalCourses + $totalSubjects; ?></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                <i class="fas fa-book text-warning fa-2x"></i>
                            </div>
                        </div>
                        <small class="text-warning fw-bold mt-2">Manage Academics →</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <h5 class="fw-bold text-dark mb-3">
                <i class="fas fa-lightning-bolt text-warning me-2"></i>Quick Actions
            </h5>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">User Management</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?php echo BASE_URL; ?>/admin/users/faculty_form.php" class="list-group-item list-group-item-action border-0 ps-0 pe-0">
                            <i class="fas fa-user-plus text-success me-2"></i>Add New Faculty
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/users/student_form.php" class="list-group-item list-group-item-action border-0 ps-0 pe-0">
                            <i class="fas fa-user-plus text-info me-2"></i>Add New Student
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/users/faculty_list.php" class="list-group-item list-group-item-action border-0 ps-0 pe-0">
                            <i class="fas fa-list text-primary me-2"></i>View All Faculty
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/users/student_list.php" class="list-group-item list-group-item-action border-0 ps-0 pe-0">
                            <i class="fas fa-list text-primary me-2"></i>View All Students
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h6 class="mb-0 fw-bold">Academic Management</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="<?php echo BASE_URL; ?>/admin/academics/subjects.php" class="list-group-item list-group-item-action border-0 ps-0 pe-0">
                            <i class="fas fa-book text-warning me-2"></i>Manage Subjects
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/academics/topics.php" class="list-group-item list-group-item-action border-0 ps-0 pe-0">
                            <i class="fas fa-list text-warning me-2"></i>Manage Topics
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/academics/branches.php" class="list-group-item list-group-item-action border-0 ps-0 pe-0">
                            <i class="fas fa-sitemap text-danger me-2"></i>Manage Branches
                        </a>
                        <a href="<?php echo BASE_URL; ?>/admin/academics/semesters.php" class="list-group-item list-group-item-action border-0 ps-0 pe-0">
                            <i class="fas fa-calendar text-info me-2"></i>Manage Semesters
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Section -->
    <div class="row g-4 mb-5">
        <div class="col-12">
            <h5 class="fw-bold text-dark mb-3">
                <i class="fas fa-chart-bar text-success me-2"></i>Reports
            </h5>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">System Usage</h6>
                    <a href="<?php echo BASE_URL; ?>/admin/reports/usage_report.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Track system usage statistics and user activity patterns.</p>
                </div>
            </div>
        </div>

        </div>
    </div>


</div>

<style>
    .transition-transform {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .transition-transform:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .sidebar {
        min-height: calc(100vh - 140px);
    }

    .list-group-item:hover {
        background-color: rgba(0, 0, 0, 0.03);
        padding-left: 5px;
        transition: all 0.2s ease;
    }
</style>

