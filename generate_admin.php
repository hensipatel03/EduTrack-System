<?php
/**
 * Admin User Creation Script
 * Run this once to create the admin user
 */

// Database configuration
$host = 'localhost';
$db = 'EduTrack_system';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if admin user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo "Admin user already exists!<br>";
        exit;
    }
    
    // Create admin user
    $username = 'admin';
    $email = 'admin@college.edu';
    $password = 'Admin@123'; // Change this password after first login
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $first_name = 'Admin';
    $last_name = 'User';
    $role_id = 1; // Admin role
    
    $stmt = $pdo->prepare("
        INSERT INTO users (role_id, username, email, password_hash, first_name, last_name, status)
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    ");
    
    $stmt->execute([$role_id, $username, $email, $password_hash, $first_name, $last_name]);
    
    $admin_id = $pdo->lastInsertId();
    
    echo "✓ Admin user created successfully!<br>";
    echo "========================================<br>";
    echo "Admin Login Credentials:<br>";
    echo "========================================<br>";
    echo "Username: " . $username . "<br>";
    echo "Email: " . $email . "<br>";
    echo "Password: " . $password . "<br>";
    echo "========================================<br>";
    echo "User ID: " . $admin_id . "<br>";
    echo "Role: Admin<br>";
    echo "Status: Active<br>";
    echo "<br><strong>⚠️ Important:</strong> Change the password immediately after first login!<br>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
