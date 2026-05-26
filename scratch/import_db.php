<?php
try {
    $host = '127.0.0.1';
    $user = 'root';
    $pass = '';
    
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to MySQL successfully.\n";
    
    // Read SQL file
    $sqlFile = __DIR__ . '/../database/studyshare.sql';
    if (!file_exists($sqlFile)) {
        die("SQL file not found at: $sqlFile\n");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "Read SQL file: " . strlen($sql) . " bytes.\n";
    
    echo "Importing database and tables...\n";
    // Disable foreign key checks to handle insert order
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec($sql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Import completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
