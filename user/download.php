<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/auth_lecturer.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/drive_helper.php';

$student = getCurrentStudent();
$note_id = intval($_GET['id'] ?? 0);

if ($note_id === 0) {
    redirect('notes.php');
}

$pdo = db();

// Get the note
$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ? AND department = ? AND semester = ?');
$stmt->execute([$note_id, $student['department'], $student['semester']]);
$note = $stmt->fetch();

if (!$note) {
    redirect('notes.php');
}

// Record download with timestamp
try {
    $stmt = $pdo->prepare('INSERT INTO downloads (roll_no, note_id, download_date) VALUES (?, ?, NOW())');
    $stmt->execute([$student['roll_no'], $note_id]);
    
    $stmt = $pdo->prepare('UPDATE notes SET likes = IFNULL(likes, 0) + 1 WHERE id = ?');
    $stmt->execute([$note_id]);
} catch (Exception $e) {
    // Continue anyway
}
// Determine which file ID to use based on user role
$fileId = $note['file_path']; // default original
if (function_exists('isLecturerLoggedIn') && isLecturerLoggedIn()) {
    if (!empty($note['scanned_file_path'])) {
        $fileId = $note['scanned_file_path'];
    }
}

// Try to get Google Drive view link for the selected file ID
try {
    $drive = new DriveHelper();
    $viewLink = $drive->getViewLink($fileId);
    if ($viewLink) {
        header('Location: ' . $viewLink);
        exit;
    }
} catch (Exception $e) {
    // If it fails, fallback to local file
}

// Determine local file path (original or scanned) if needed
$file_path = NOTES_DIR . $note['file_path'];
if (function_exists('isLecturerLoggedIn') && isLecturerLoggedIn() && !empty($note['scanned_file_path'])) {
    $scanned_local = SCANNED_DIR . $note['file_path'];
    if (file_exists($scanned_local)) {
        $file_path = $scanned_local;
    }
}

if (!file_exists($file_path)) {
    header('HTTP/1.0 404 Not Found');
    exit('File not found.');
}

// Prepare file for download
$filename = basename($note['file_path']);
if (empty($filename) || strpos($filename, '.') === false) {
    $filename = 'note_' . $note_id . '.pdf';
}

// Send file headers
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
