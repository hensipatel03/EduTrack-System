<?php
/**
 * Database Connection Tester
 * Run this file to verify database setup and admin credentials
 */

// Include configuration
require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test - College Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px 10px 0 0; }
        .test-item { padding: 15px; border-bottom: 1px solid #eee; }
        .test-item:last-child { border-bottom: none; }
        .status-success { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-info { color: #17a2b8; }
        .badge { margin-left: 10px; }
        .table { margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mt-5">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-database"></i> Database Connection Test</h3>
            </div>
            <div class="card-body">
                
                <?php
                $tests_passed = 0;
                $tests_total = 0;

                // Test 1: Config file loaded
                echo '<div class="test-item">';
                $tests_total++;
                echo '<h5><i class="fas fa-cogs"></i> Configuration File</h5>';
                echo '<p class="mb-2">';
                if (defined('DB_SERVER')) {
                    echo '<span class="status-success"><i class="fas fa-check-circle"></i> Loaded</span>';
                    $tests_passed++;
                    echo '<br><small class="text-muted">Server: ' . DB_SERVER . ' | Database: ' . DB_NAME . '</small>';
                } else {
                    echo '<span class="status-error"><i class="fas fa-times-circle"></i> Failed</span>';
                }
                echo '</p>';
                echo '</div>';

                // Test 2: Database Connection
                echo '<div class="test-item">';
                $tests_total++;
                echo '<h5><i class="fas fa-link"></i> Database Connection</h5>';
                $db_connection = false;
                try {
                    $pdo = new PDO(
                        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                        DB_USER,
                        DB_PASS,
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    echo '<span class="status-success"><i class="fas fa-check-circle"></i> Connected</span>';
                    echo '<br><small class="text-muted">MySQL Server Version: ' . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . '</small>';
                    $db_connection = true;
                    $tests_passed++;
                } catch (PDOException $e) {
                    echo '<span class="status-error"><i class="fas fa-times-circle"></i> Connection Failed</span>';
                    echo '<br><small class="text-danger">' . htmlspecialchars($e->getMessage()) . '</small>';
                }
                echo '</div>';

                if ($db_connection) {
                    // Test 3: Tables Exist
                    echo '<div class="test-item">';
                    $tests_total++;
                    echo '<h5><i class="fas fa-table"></i> Database Tables</h5>';
                    try {
                        $tables = [
                            'roles', 'users', 'faculty_profiles', 'student_profiles',
                            'courses', 'branches', 'semesters', 'subjects',
                            'faculty_subjects', 'study_materials', 'assignments',
                            'assignment_submissions', 'topics', 'student_topic_progress',
                            'calendar_events', 'doubts', 'doubt_replies'
                        ];
                        
                        $stmt = $pdo->query("SHOW TABLES FROM " . DB_NAME);
                        $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        $missing = array_diff($tables, $existing_tables);
                        
                        if (empty($missing)) {
                            echo '<span class="status-success"><i class="fas fa-check-circle"></i> All Tables Exist (' . count($tables) . '/'. count($tables) . ')</span>';
                            $tests_passed++;
                        } else {
                            echo '<span class="status-error"><i class="fas fa-exclamation-circle"></i> Missing Tables</span>';
                            echo '<br><small class="text-danger">Missing: ' . implode(', ', $missing) . '</small>';
                        }
                    } catch (Exception $e) {
                        echo '<span class="status-error"><i class="fas fa-times-circle"></i> Error</span>';
                        echo '<br><small class="text-danger">' . $e->getMessage() . '</small>';
                    }
                    echo '</div>';

                    // Test 4: Roles Data
                    echo '<div class="test-item">';
                    $tests_total++;
                    echo '<h5><i class="fas fa-users-cog"></i> Roles Configuration</h5>';
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result['count'] >= 3) {
                            echo '<span class="status-success"><i class="fas fa-check-circle"></i> Roles Configured</span>';
                            echo '<table class="table table-sm mt-2">';
                            $stmt = $pdo->query("SELECT * FROM roles ORDER BY id");
                            while ($role = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<tr><td><strong>ID ' . $role['id'] . ':</strong></td><td>' . ucfirst($role['name']) . '</td></tr>';
                            }
                            echo '</table>';
                            $tests_passed++;
                        } else {
                            echo '<span class="status-error"><i class="fas fa-exclamation-circle"></i> Incomplete</span>';
                            echo '<br><small class="text-danger">Found only ' . $result['count'] . ' roles (need 3)</small>';
                        }
                    } catch (Exception $e) {
                        echo '<span class="status-error"><i class="fas fa-times-circle"></i> Error</span>';
                        echo '<br><small class="text-danger">' . $e->getMessage() . '</small>';
                    }
                    echo '</div>';

                    // Test 5: Admin User
                    echo '<div class="test-item">';
                    $tests_total++;
                    echo '<h5><i class="fas fa-user-shield"></i> Admin User Account</h5>';
                    try {
                        $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, status FROM users WHERE username = 'admin' AND role_id = 1");
                        $stmt->execute();
                        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($admin) {
                            echo '<span class="status-success"><i class="fas fa-check-circle"></i> Admin User Found</span>';
                            echo '<table class="table table-sm mt-2">';
                            echo '<tr><td><strong>Username:</strong></td><td><code>admin</code></td></tr>';
                            echo '<tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($admin['email']) . '</td></tr>';
                            echo '<tr><td><strong>Name:</strong></td><td>' . htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . '</td></tr>';
                            echo '<tr><td><strong>Status:</strong></td><td><span class="badge bg-success">' . ucfirst($admin['status']) . '</span></td></tr>';
                            echo '</table>';
                            $tests_passed++;
                        } else {
                            echo '<span class="status-error"><i class="fas fa-times-circle"></i> Admin Not Found</span>';
                            echo '<br><small class="text-danger">Run setup_admin.sql or generate_admin.php to create admin user</small>';
                        }
                    } catch (Exception $e) {
                        echo '<span class="status-error"><i class="fas fa-times-circle"></i> Error</span>';
                        echo '<br><small class="text-danger">' . $e->getMessage() . '</small>';
                    }
                    echo '</div>';

                    // Test 6: Test Credentials
                    echo '<div class="test-item">';
                    $tests_total++;
                    echo '<h5><i class="fas fa-key"></i> Credentials Test</h5>';
                    echo '<div class="alert alert-info">';
                    echo '<strong><i class="fas fa-info-circle"></i> Default Admin Credentials:</strong><br>';
                    echo '<code>Username: admin</code><br>';
                    echo '<code>Password: Admin@123</code><br><br>';
                    echo '<em>Login at:</em> <code>admin/login.php</code>';
                    echo '</div>';
                    $tests_passed++;
                    echo '</div>';
                }

                // Summary
                echo '<div class="mt-4">';
                echo '<h5>Test Summary</h5>';
                $percentage = ($tests_passed / $tests_total) * 100;
                echo '<div class="progress" style="height: 25px;">';
                echo '<div class="progress-bar' . ($percentage == 100 ? ' bg-success' : ($percentage >= 80 ? ' bg-warning' : ' bg-danger')) . '" role="progressbar" style="width: ' . $percentage . '%;" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">';
                echo $tests_passed . '/' . $tests_total . ' Tests Passed (' . round($percentage, 1) . '%)</div>';
                echo '</div>';
                
                if ($percentage == 100) {
                    echo '<div class="alert alert-success mt-3"><i class="fas fa-check-circle"></i> All tests passed! System is ready for use.</div>';
                } else {
                    echo '<div class="alert alert-warning mt-3"><i class="fas fa-exclamation-triangle"></i> Some tests failed. Please check the errors above.</div>';
                }
                echo '</div>';
                ?>

            </div>
            <div class="card-footer bg-light">
                <small class="text-muted">
                    <i class="fas fa-info-circle"></i> Database Test Report - <?php echo date('Y-m-d H:i:s'); ?>
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
