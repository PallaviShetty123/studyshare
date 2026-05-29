<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/drive_helper.php';

$note_id = intval($_GET['id'] ?? 0);
$admin = getCurrentAdmin();

if ($note_id === 0) {
    redirect('manage_notes.php');
}

$pdo = db();

// Get the note details for logging and file deletion
$stmt = $pdo->prepare('
    SELECT n.*, s.subject_name 
    FROM notes n 
    LEFT JOIN subjects s ON n.subject_id = s.id 
    WHERE n.id = ?
');
$stmt->execute([$note_id]);
$note = $stmt->fetch();

if ($note) {
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
    if (!empty($note['file_path']) && file_exists(NOTES_DIR . $note['file_path'])) {
        @unlink(NOTES_DIR . $note['file_path']);
    }
    
    // Delete local scanned file if it exists
    if (!empty($note['scanned_file_path']) && file_exists(SCANNED_DIR . $note['scanned_file_path'])) {
        @unlink(SCANNED_DIR . $note['scanned_file_path']);
    }
    
    // Log the activity
    logActivity($admin['id'], 'admin', 'delete', $note_id, "Deleted notes for " . ($note['subject_name'] ?? 'Unknown'));

    // Delete from database
    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
    $stmt->execute([$note_id]);
}

redirect('manage_notes.php');
