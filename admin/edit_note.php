<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/drive_helper.php';

$admin = getCurrentAdmin();
$pdo = db();

// Get all subjects for dropdown
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY subject_name')->fetchAll();
$note_id = intval($_GET['id'] ?? 0);

if ($note_id === 0) {
    redirect('manage_notes.php');
}

$stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
$stmt->execute([$note_id]);
$note = $stmt->fetch();

if (!$note) {
    redirect('manage_notes.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $semester = intval($_POST['semester'] ?? 0);
    
    if ($subject_id === 0 || empty($description) || empty($department) || $semester === 0) {
        $errors[] = 'Please fill all required fields.';
    } else {
        $file_path = $note['file_path'];
        
        // If new file uploaded
        if (!empty($_FILES['file']['name'])) {
            if (!isValidFileUpload($_FILES['file']['name'])) {
                $errors[] = 'Invalid file type. Only PDF and DOC files are allowed.';
            } elseif ($_FILES['file']['size'] > MAX_FILE_SIZE) {
                $errors[] = 'File size exceeds 10MB limit.';
            } else {
                $new_filename = uploadFile($_FILES['file'], NOTES_DIR);
                if ($new_filename) {
                    $filepath = NOTES_DIR . $new_filename;
                    
                    // Create scanned copy as well
                    $scanned_filepath = __DIR__ . '/../uploads/scanned/' . $new_filename;
                    if (!is_dir(__DIR__ . '/../uploads/scanned/')) {
                        mkdir(__DIR__ . '/../uploads/scanned/', 0755, true);
                    }
                    copy($filepath, $scanned_filepath);
                    
                    try {
                        // Upload to Google Drive
                        $drive = new DriveHelper();
                        $driveFileId = $drive->uploadFile($filepath, $_FILES['file']['name'], $_FILES['file']['type']);
                        $drive->makePublic($driveFileId);

                        $scannedDriveId = $drive->uploadFile($scanned_filepath, $_FILES['file']['name'], $_FILES['file']['type']);
                        $drive->makePublic($scannedDriveId);
                        
                        // Delete old files from Drive if they were Drive IDs
                        if (strlen($file_path) > 20 && strpos($file_path, '.') === false) { // Likely a Drive ID
                            try { $drive->deleteFile($file_path); } catch(Exception $e) {}
                        }
                        if (!empty($note['scanned_file_path']) && strlen($note['scanned_file_path']) > 20 && strpos($note['scanned_file_path'], '.') === false) { // Likely a Drive ID
                            try { $drive->deleteFile($note['scanned_file_path']); } catch(Exception $e) {}
                        }

                        $file_path = $driveFileId;
                        $scanned_file_path = $scannedDriveId;

                        // Delete local files after successful upload to Drive
                        if (file_exists($filepath)) @unlink($filepath);
                        if (file_exists($scanned_filepath)) @unlink($scanned_filepath);
                    } catch (Exception $e) {
                        $errors[] = 'Google Drive Error: ' . $e->getMessage();
                        if (file_exists($filepath)) @unlink($filepath);
                        if (file_exists($scanned_filepath)) @unlink($scanned_filepath);
                    }
                } else {
                    $errors[] = 'Failed to upload new file.';
                }
            }
        }
        
        if (empty($errors)) {
            // Ensure scanned_file_path column exists
            try { $pdo->exec("ALTER TABLE notes ADD COLUMN scanned_file_path VARCHAR(255) NULL"); } catch (Exception $e) { }

            if (!empty($_FILES['file']['name'])) {
                $stmt = $pdo->prepare('UPDATE notes SET subject_id = ?, description = ?, file_path = ?, scanned_file_path = ?, department = ?, semester = ? WHERE id = ?');
                $success_db = $stmt->execute([$subject_id, $description, $file_path, $scanned_file_path, $department, $semester, $note_id]);
            } else {
                $stmt = $pdo->prepare('UPDATE notes SET subject_id = ?, description = ?, department = ?, semester = ? WHERE id = ?');
                $success_db = $stmt->execute([$subject_id, $description, $department, $semester, $note_id]);
            }

            if ($success_db) {
                $success = 'Note updated successfully!';
                // Refresh note data
                $stmt = $pdo->prepare('SELECT * FROM notes WHERE id = ?');
                $stmt->execute([$note_id]);
                $note = $stmt->fetch();
            } else {
                $errors[] = 'Failed to update note.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Note | StudyShare Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <h2>StudyShare</h2>
                <p>Admin Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="upload_notes.php" class="nav-item">
                    <span class="icon">📤</span> Upload Notes
                </a>
                <a href="manage_notes.php" class="nav-item active">
                    <span class="icon">📋</span> Manage Notes
                </a>
                <a href="manage_students.php" class="nav-item">
                    <span class="icon">👥</span> Manage Students
                </a>
                <a href="assign_subjects.php" class="nav-item">
                    <span class="icon">📘</span> Assign Subjects
                </a>
                <a href="activity_log.php" class="nav-item">
                    <span class="icon">📜</span> Activity Log
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-info">
                    <p><?= sanitize($admin['username']) ?></p>
                    <small>Administrator</small>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <span class="icon">🚪</span> Logout
                </a>
            </div>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1>Edit Note</h1>
                        <p>Modify existing study materials</p>
                    </div>
                    <a href="manage_notes.php" class="btn" style="background: var(--border); color: var(--text-dark);">← Back</a>
                </div>
            </header>

            <div class="content-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <span class="icon">✅</span>
                        <p><?= $success ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-error">
                        <span class="icon">⚠️</span>
                        <div style="flex: 1;">
                            <?php foreach ($errors as $error): ?>
                                <p style="margin-bottom: 0.25rem;">• <?= $error ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="subject_id">Subject *</label>
                            <select id="subject_id" name="subject_id" class="form-control" required>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>" <?= intval($note['subject_id']) === intval($subject['id']) ? 'selected' : '' ?>>
                                        <?= sanitize($subject['subject_name']) ?> (<?= sanitize($subject['subject_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" class="form-control" required rows="4"><?= sanitize($note['description']) ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="department">Department *</label>
                                <select id="department" name="department" class="form-control" required>
                                    <?php 
                                    $depts = ['BCA', 'BBA', 'B.COM', 'ENGLISH', 'KANNADA', 'HINDI'];
                                    foreach ($depts as $dept): ?>
                                        <option value="<?= $dept ?>" <?= $note['department'] === $dept ? 'selected' : '' ?>><?= $dept ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="semester">Semester *</label>
                                <select id="semester" name="semester" class="form-control" required>
                                    <?php for($i=1; $i<=8; $i++): ?>
                                        <option value="<?= $i ?>" <?= intval($note['semester']) === $i ? 'selected' : '' ?>><?= $i ?>st Semester</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="file">Replace File (PDF or DOC) - Optional</label>
                            <input type="file" id="file" name="file" class="form-control" accept=".pdf,.doc,.docx" style="padding: 0.6rem;">
                            <small style="display: block; margin-top: 0.5rem; color: var(--text-muted);">Current file ID: <?= sanitize($note['file_path']) ?></small>
                        </div>

                        <div style="margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                <span class="icon">💾</span> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
