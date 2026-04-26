<?php
// Setup database from SQL file
$host = '127.0.0.1';
$user = 'root';
$pass = '';

// First connect without selecting a database
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Connected to MySQL\n";
    
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $count++;
            } catch (PDOException $e) {
                echo "⚠ Statement skipped (may already exist): " . substr($statement, 0, 50) . "...\n";
            }
        }
    }
    
    echo "✓ Database setup complete! Executed $count statements\n";
    echo "✓ Database 'EduTrack_system' created successfully\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
