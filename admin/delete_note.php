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
    // Delete from Google Drive
    try {
        $drive = new DriveHelper();
        $drive->deleteFile($note['file_path']);
    } catch (Exception $e) {
        // Continue even if Drive delete fails
    }

    // Delete the local file if it exists (legacy)
    if (file_exists(NOTES_DIR . $note['file_path'])) {
        unlink(NOTES_DIR . $note['file_path']);
    }
    
    // Log the activity
    logActivity($admin['id'], 'admin', 'delete', $note_id, "Deleted notes for " . ($note['subject_name'] ?? 'Unknown'));

    // Delete from database
    $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
    $stmt->execute([$note_id]);
}

redirect('manage_notes.php');
