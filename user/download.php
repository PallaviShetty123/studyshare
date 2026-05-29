<?php
session_start();
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/auth_lecturer.php';
require_once __DIR__ . '/../common/drive_helper.php';

// Check if student, lecturer, or admin is logged in
$is_student = !empty($_SESSION['student_roll_no']);
$is_lecturer = isLecturerLoggedIn();
$is_admin = !empty($_SESSION['admin_id']);

if (!$is_student && !$is_lecturer && !$is_admin) {
    redirect('../user/login.php');
}

$note_id = intval($_GET['id'] ?? 0);

if ($note_id === 0) {
    redirect('notes.php');
}

$pdo = db();

// Fetch the note details
if ($is_student) {
    $student = getCurrentStudent();
    $stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ? AND department = ? AND semester = ?');
    $stmt->execute([$note_id, $student['department'], $student['semester']]);
} else {
    // Lecturers and admins can access any note
    $stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
    $stmt->execute([$note_id]);
}
$note = $stmt->fetch();

if (!$note) {
    redirect('notes.php');
}

// Record download only for students
if ($is_student) {
    try {
        $stmt = $pdo->prepare('INSERT INTO downloads (roll_no, note_id, download_date) VALUES (?, ?, NOW())');
        $stmt->execute([$student['roll_no'], $note_id]);
        
        $stmt = $pdo->prepare('UPDATE notes SET likes = IFNULL(likes, 0) + 1 WHERE id = ?');
        $stmt->execute([$note_id]);
    } catch (Exception $e) {
        // Continue anyway
    }
}

// Determine which file path or Google Drive ID to use based on role
// Lecturers see the scanned one; students and admins see the uploaded one
$fileId = $note['file_path']; // default original/uploaded file
if ($is_lecturer) {
    if (!empty($note['scanned_file_path'])) {
        $fileId = $note['scanned_file_path'];
    }
}

// Detect if it is a Google Drive ID or a local filename
// Google Drive IDs do not contain a dot (extension) and are generally long strings
$isDriveId = (strpos($fileId, '.') === false && strlen($fileId) > 15);

if ($isDriveId) {
    // Try to get Google Drive view link
    try {
        $drive = new DriveHelper();
        $viewLink = $drive->getViewLink($fileId);
        if ($viewLink) {
            header('Location: ' . $viewLink);
            exit;
        }
    } catch (Exception $e) {
        // If Google Drive fails, fall back to local file
    }
}

// Fallback: Determine local file path on the server
if ($is_lecturer) {
    // Lecturer sees the scanned note
    $local_filename = !empty($note['scanned_file_path']) ? $note['scanned_file_path'] : $note['file_path'];
    $file_path = SCANNED_DIR . $local_filename;
    
    // Fallback to original notes directory if scanned file is missing
    if (!file_exists($file_path)) {
        $file_path = NOTES_DIR . $note['file_path'];
    }
} else {
    // Students and Admins see the uploaded note
    $file_path = NOTES_DIR . $note['file_path'];
}

if (!file_exists($file_path)) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found on server.');
}

// Send file headers
$filename = basename($file_path);
// Extract original file name if it has unique ID prefix
if (preg_match('/^\w{13}_(.*)$/', $filename, $matches)) {
    $filename = $matches[1];
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($file_path));
header('Pragma: no-cache');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Expires: 0');

// Send the file
if (!readfile($file_path)) {
    header('HTTP/1.0 500 Internal Server Error');
    exit('Error reading file.');
}
exit;
