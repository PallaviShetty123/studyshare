<?php
session_start();
require_once __DIR__ . '/../common/auth_lecturer.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/drive_helper.php';

$lecturer = getCurrentLecturer();
$pdo = db();

$note_id = intval($_GET['note_id'] ?? 0);

if ($note_id <= 0) {
    redirect('dashboard.php');
}

// Get note details with subject name
$stmt = $pdo->prepare('
    SELECT n.*, s.subject_name 
    FROM notes n 
    LEFT JOIN subjects s ON n.subject_id = s.id 
    WHERE n.id = ? AND n.lecturer_id = ?
');
$stmt->execute([$note_id, $lecturer['id']]);
$note = $stmt->fetch();

if (!$note) {
    redirect('dashboard.php');
}

// Log the activity
logActivity($lecturer['id'], 'lecturer', 'delete', $note_id, "Deleted notes for " . ($note['subject_name'] ?? 'Unknown'));

// Delete from Google Drive if they are Drive IDs
try {
    $drive = new DriveHelper();
    if (!empty($note['file_path']) && strlen($note['file_path']) > 20 && strpos($note['file_path'], '.') === false) {
        $drive->deleteFile($note['file_path']);
    }
    if (!empty($note['scanned_file_path']) && strlen($note['scanned_file_path']) > 20 && strpos($note['scanned_file_path'], '.') === false) {
        $drive->deleteFile($note['scanned_file_path']);
    }
} catch (Exception $e) {
    // Continue even if Drive delete fails
}

// Delete local original file if it exists
$file_path = __DIR__ . '/../uploads/notes/' . $note['file_path'];
if (!empty($note['file_path']) && file_exists($file_path)) {
    @unlink($file_path);
}

// Delete local scanned file if it exists
$scanned_file_path = __DIR__ . '/../uploads/scanned/' . $note['scanned_file_path'];
if (!empty($note['scanned_file_path']) && file_exists($scanned_file_path)) {
    @unlink($scanned_file_path);
}

// Delete from database
$stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
$stmt->execute([$note_id]);

// Also delete related likes
$stmt = $pdo->prepare('DELETE FROM likes WHERE note_id = ?');
$stmt->execute([$note_id]);

// Also delete related downloads
$stmt = $pdo->prepare('DELETE FROM downloads WHERE note_id = ?');
$stmt->execute([$note_id]);

redirect('dashboard.php?deleted=1');
?>
