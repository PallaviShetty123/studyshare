<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$student = getCurrentStudent();
$note_id = intval($_POST['note_id'] ?? 0);

if ($note_id === 0) {
    echo json_encode(['error' => 'Invalid note']);
    exit;
}

$pdo = db();

// Check if the note exists and belongs to student's department/semester
$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ? AND department = ? AND semester = ?');
$stmt->execute([$note_id, $student['department'], $student['semester']]);
$note = $stmt->fetch();

if (!$note) {
    echo json_encode(['error' => 'Note not found']);
    exit;
}

// Check if already liked
$stmt = $pdo->prepare('SELECT id FROM likes WHERE roll_no = ? AND note_id = ?');
$stmt->execute([$student['roll_no'], $note_id]);
$existing_like = $stmt->fetch();

if ($existing_like) {
    // Unlike
    $stmt = $pdo->prepare('DELETE FROM likes WHERE roll_no = ? AND note_id = ?');
    $stmt->execute([$student['roll_no'], $note_id]);
    
    // Update likes count
    $stmt = $pdo->prepare('UPDATE notes SET likes = likes - 1 WHERE id = ?');
    $stmt->execute([$note_id]);
    
    echo json_encode(['success' => true, 'liked' => false, 'likes' => $note['likes'] - 1]);
} else {
    // Like
    $stmt = $pdo->prepare('INSERT INTO likes (roll_no, note_id) VALUES (?, ?)');
    $stmt->execute([$student['roll_no'], $note_id]);
    
    // Update likes count
    $stmt = $pdo->prepare('UPDATE notes SET likes = likes + 1 WHERE id = ?');
    $stmt->execute([$note_id]);
    
    echo json_encode(['success' => true, 'liked' => true, 'likes' => $note['likes'] + 1]);
}
