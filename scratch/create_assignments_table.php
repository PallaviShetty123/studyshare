<?php
require_once __DIR__ . '/../common/db.php';

$pdo = db();

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lecturer_subject_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            lecturer_id INT NOT NULL,
            subject_id INT NOT NULL,
            subject_name VARCHAR(100),
            department VARCHAR(50),
            year VARCHAR(20),
            semester INT,
            academic_year VARCHAR(20),
            assigned_by INT,
            status TINYINT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE CASCADE,
            FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Table lecturer_subject_assignments created successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
