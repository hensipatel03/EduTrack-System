<?php
// index.php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirectToRoleHome();
}

include __DIR__ . '/includes/header.php';
?>

<div class="hero-section text-center mb-5 py-4">
    <h1 class="display-4 fw-bold mb-3">
        <i class="fas fa-book-reader text-primary me-2"></i>Edu Track System
    </h1>
    <p class="lead text-muted">Streamline your academic journey with our comprehensive platform</p>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-3 col-md-6">
        <div class="card portal-card shadow-sm h-100 border-0 transition-transform">
            <div class="card-body text-center">
                <div class="portal-icon mb-3">
                    <i class="fas fa-lock fa-3x text-danger"></i>
                </div>
                <h5 class="card-title fw-bold">Admin Portal</h5>
                <p class="card-text small text-muted">Manage users, academics, reports, and system configuration.</p>
                <a href="<?php echo BASE_URL; ?>/admin/login.php" class="btn btn-danger btn-sm mt-3">
                    <i class="fas fa-sign-in-alt me-1"></i>Admin Login
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card portal-card shadow-sm h-100 border-0 transition-transform">
            <div class="card-body text-center">
                <div class="portal-icon mb-3">
                    <i class="fas fa-chalkboard-user fa-3x text-primary"></i>
                </div>
                <h5 class="card-title fw-bold">Faculty Portal</h5>
                <p class="card-text small text-muted">Manage subjects, materials, assignments, and student doubts.</p>
                <a href="<?php echo BASE_URL; ?>/faculty/login.php" class="btn btn-primary btn-sm mt-3">
                    <i class="fas fa-sign-in-alt me-1"></i>Faculty Login
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="card portal-card shadow-sm h-100 border-0 transition-transform">
            <div class="card-body text-center">
                <div class="portal-icon mb-3">
                    <i class="fas fa-graduation-cap fa-3x text-success"></i>
                </div>
                <h5 class="card-title fw-bold">Student Portal</h5>
                <p class="card-text small text-muted">Access planner, calendar, resources, and submit assignments.</p>
                <a href="<?php echo BASE_URL; ?>/student/login.php" class="btn btn-success btn-sm mt-3">
                    <i class="fas fa-sign-in-alt me-1"></i>Student Login
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>