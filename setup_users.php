<?php
// Insert test users for Admin, Faculty, and Student
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

try {
    $pdo = Database::getInstance();
    
    // First, insert roles if they don't exist
    $roles = ['admin', 'faculty', 'student'];
    foreach ($roles as $role) {
        $pdo->prepare("INSERT IGNORE INTO roles (name, description) VALUES (:name, :desc)")
            ->execute([
                ':name' => $role,
                ':desc' => ucfirst($role) . ' role'
            ]);
    }
    echo "✓ Roles created/verified\n";
    
    // Get role IDs
    $adminRole = $pdo->query("SELECT id FROM roles WHERE name = 'admin'")->fetch();
    $facultyRole = $pdo->query("SELECT id FROM roles WHERE name = 'faculty'")->fetch();
    $studentRole = $pdo->query("SELECT id FROM roles WHERE name = 'student'")->fetch();
    
    // Insert test users
    $users = [
        [
            'role_id' => $adminRole['id'],
            'username' => 'admin',
            'email' => 'admin@college.com',
            'password' => 'Admin@123',
            'full_name' => 'System Administrator',
            'status' => 'active'
        ],
        [
            'role_id' => $facultyRole['id'],
            'username' => 'faculty1',
            'email' => 'faculty@college.com',
            'password' => 'Faculty@123',
            'full_name' => 'Dr. John Faculty',
            'status' => 'active'
        ],
        [
            'role_id' => $studentRole['id'],
            'username' => 'student1',
            'email' => 'student@college.com',
            'password' => 'Student@123',
            'full_name' => 'Jane Student',
            'status' => 'active'
        ]
    ];
    
    foreach ($users as $userData) {
        try {
            $pdo->prepare("
                INSERT INTO users (role_id, username, email, password_hash, full_name, status)
                VALUES (:role_id, :username, :email, :password_hash, :full_name, :status)
            ")->execute([
                ':role_id' => $userData['role_id'],
                ':username' => $userData['username'],
                ':email' => $userData['email'],
                ':password_hash' => password_hash($userData['password'], PASSWORD_BCRYPT),
                ':full_name' => $userData['full_name'],
                ':status' => $userData['status']
            ]);
            echo "✓ User created: {$userData['username']} ({$userData['email']})\n";
            echo "  Password: {$userData['password']}\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "⚠ User already exists: {$userData['username']}\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "\n✓ Test users setup complete!\n";
    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "ADMIN:\n  Username: admin\n  Password: Admin@123\n\n";
    echo "FACULTY:\n  Username: faculty1\n  Password: Faculty@123\n\n";
    echo "STUDENT:\n  Username: student1\n  Password: Student@123\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
