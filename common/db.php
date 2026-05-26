<?php
require_once __DIR__ . '/config.php';

$pdo = null;

function db() {
    global $pdo;
    
    // Check if connection exists and is still alive
    if ($pdo) {
        try {
            $pdo->query("SELECT 1");
            return $pdo;
        } catch (PDOException $e) {
            $pdo = null; // Connection lost, force reconnect
        }
    }

    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,
            ]
        );
        
        // Only run table creation if we haven't checked it in this session
        if (session_status() !== PHP_SESSION_NONE && !isset($_SESSION['tables_checked'])) {
            $pdo->exec("
            CREATE TABLE IF NOT EXISTS smart_notes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                note_id INT,
                summary TEXT,
                key_points TEXT,
                flashcards TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
            );
            ");
            
            $pdo->exec("
            CREATE TABLE IF NOT EXISTS quiz_sets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                note_id INT,
                questions TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
            );
            ");
            $_SESSION['tables_checked'] = true;
        }
        
        return $pdo;
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'gone away') !== false) {
            die("Database Error: MySQL server has gone away. Please restart MySQL in XAMPP Control Panel.");
        }
        die("Connection failed: " . $e->getMessage());
    }
}
