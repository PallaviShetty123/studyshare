<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$roll_no = $_GET['roll_no'] ?? '';

if (empty($roll_no)) {
    redirect('manage_students.php');
}

$pdo = db();

// Delete the student
$stmt = $pdo->prepare('DELETE FROM students WHERE roll_no = ?');
$stmt->execute([$roll_no]);

redirect('manage_students.php');
