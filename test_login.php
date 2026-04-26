<?php
// Test login process
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

echo "=== LOGIN PROCESS TEST ===\n\n";

try {
    $pdo = Database::getInstance();
    echo "✓ Database connection successful\n\n";
    
    // Test credentials
    $testCreds = [
        ['username' => 'admin', 'password' => 'Admin@123', 'role' => 'admin'],
        ['username' => 'faculty1', 'password' => 'Faculty@123', 'role' => 'faculty'],
        ['username' => 'student1', 'password' => 'Student@123', 'role' => 'student'],
    ];
    
    foreach ($testCreds as $cred) {
        echo "Testing {$cred['role']} login: {$cred['username']}\n";
        
        $sql = "SELECT u.*, r.name AS role_name
                FROM users u
                JOIN roles r ON r.id = u.role_id
                WHERE (u.username = :username1 OR u.email = :username2) 
                  AND u.status = 'active'
                  AND r.name = :role
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username1' => $cred['username'],
            ':username2' => $cred['username'],
            ':role' => $cred['role']
        ]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "  ✓ User found: {$user['full_name']}\n";
            
            if (password_verify($cred['password'], $user['password_hash'])) {
                echo "  ✓ Password verified\n";
                echo "  ✓ Session would be set: user_id={$user['id']}, role={$user['role_name']}\n";
            } else {
                echo "  ✗ Password verification FAILED\n";
            }
        } else {
            echo "  ✗ User not found\n";
        }
        echo "\n";
    }
    
    echo "=== LOGIN URLS ===\n";
    echo "Admin:   " . BASE_URL . "/admin/login.php\n";
    echo "Faculty: " . BASE_URL . "/faculty/login.php\n";
    echo "Student: " . BASE_URL . "/student/login.php\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
