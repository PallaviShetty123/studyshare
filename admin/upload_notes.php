<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/drive_helper.php';

$admin = getCurrentAdmin();
$pdo = db();
$errors = [];
$success = '';

// Get all subjects for the dropdown
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY subject_name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $department = sanitize($_POST['department'] ?? '');
    $semester = intval($_POST['semester'] ?? 0);
    
    if ($subject_id === 0 || empty($description) || empty($department) || $semester === 0) {
        $errors[] = 'Please fill all required fields.';
    } elseif (empty($_FILES['file']['name'])) {
        $errors[] = 'Please upload a file.';
    } elseif (!isValidFileUpload($_FILES['file']['name'])) {
        $errors[] = 'Invalid file type. Only PDF and DOC files are allowed.';
    } elseif ($_FILES['file']['size'] > MAX_FILE_SIZE) {
        $errors[] = 'File size exceeds 10MB limit.';
    } else {
        // Upload original file locally first
        $filename = uploadFile($_FILES['file'], NOTES_DIR);
        if ($filename) {
            $filepath = NOTES_DIR . $filename;
            
            // Scan the file and store a copy in the scanned directory
            $scanned_filepath = __DIR__ . '/../uploads/scanned/' . $filename;
            if (!is_dir(__DIR__ . '/../uploads/scanned/')) {
                mkdir(__DIR__ . '/../uploads/scanned/', 0755, true);
            }
            copy($filepath, $scanned_filepath);
            
            try {
                // Upload original file to Google Drive
                $drive = new DriveHelper();
                $driveFileId = $drive->uploadFile($filepath, $_FILES['file']['name'], $_FILES['file']['type']);
                $drive->makePublic($driveFileId);
                
                // Upload scanned file to Google Drive
                $scannedDriveId = $drive->uploadFile($scanned_filepath, $_FILES['file']['name'], $_FILES['file']['type']);
                $drive->makePublic($scannedDriveId);
                
                // Clean up local copies
                if (file_exists($filepath)) { unlink($filepath); }
                if (file_exists($scanned_filepath)) { unlink($scanned_filepath); }
                
                // Ensure scanned_file_path column exists (attempt alter, ignore errors)
                try { $pdo->exec("ALTER TABLE notes ADD COLUMN scanned_file_path VARCHAR(255) NULL"); } catch (Exception $e) { }
                
                // Insert note with both original and scanned Drive IDs
                $stmt = $pdo->prepare('INSERT INTO notes (subject_id, description, file_path, scanned_file_path, department, semester, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
                if ($stmt->execute([$subject_id, $description, $driveFileId, $scannedDriveId, $department, $semester, $admin['id']])) {
                    $note_id = $pdo->lastInsertId();
                    
                    // Get subject name for logging
                    $subj_stmt = $pdo->prepare('SELECT subject_name FROM subjects WHERE id = ?');
                    $subj_stmt->execute([$subject_id]);
                    $subj_name = $subj_stmt->fetchColumn();
                    
                    logActivity($admin['id'], 'admin', 'upload', $note_id, "Uploaded notes for $subj_name");
                    
                    flash('success', 'Note uploaded to Google Drive successfully!');
                    redirect('upload_notes.php');
                } else {
                    $errors[] = 'Failed to save note to database.';
                }
            } catch (Exception $e) {
                $errors[] = 'Google Drive Error: ' . $e->getMessage();
                // Cleanup any leftover local files
                if (file_exists($filepath)) { unlink($filepath); }
                if (file_exists($scanned_filepath)) { unlink($scanned_filepath); }
            }
        } else {
            $errors[] = 'Failed to upload file.';
        }
    }
}

// Get flash messages
$success = flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Notes | StudyShare Admin</title>
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
                <a href="upload_notes.php" class="nav-item active">
                    <span class="icon">📤</span> Upload Notes
                </a>
                <a href="manage_notes.php" class="nav-item">
                    <span class="icon">📋</span> Manage Notes
                </a>
                <a href="manage_students.php" class="nav-item">
                    <span class="icon">👥</span> Manage Students
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
                <a href="logout.php" class="logout-btn">
                    <span class="icon">🚪</span> Logout
                </a>
            </div>
        </aside>

        <main class="admin-content">
            <header class="admin-header">
                <h1>Upload Notes</h1>
                <p>Add new study materials to the platform</p>
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
                    <form id="uploadForm" action="upload_notes.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="subject_id">Subject *</label>
                            <select id="subject_id" name="subject_id" class="form-control" required>
                                <option value="">Select Subject</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= $subject['id'] ?>" <?= (intval($_POST['subject_id'] ?? 0)) === intval($subject['id']) ? 'selected' : '' ?>>
                                        <?= sanitize($subject['subject_name']) ?> (<?= sanitize($subject['subject_code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" class="form-control" required placeholder="Brief description of the notes" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="department">Department *</label>
                                <select id="department" name="department" class="form-control" required>
                                    <option value="">Select Department</option>
                                    <?php 
                                    $depts = ['BCA', 'BBA', 'B.COM', 'ENGLISH', 'KANNADA', 'HINDI'];
                                    foreach ($depts as $dept): ?>
                                        <option value="<?= $dept ?>" <?= ($_POST['department'] ?? '') === $dept ? 'selected' : '' ?>><?= $dept ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="semester">Semester *</label>
                                <select id="semester" name="semester" class="form-control" required>
                                    <option value="">Select Semester</option>
                                    <?php for($i=1; $i<=8; $i++): ?>
                                        <option value="<?= $i ?>" <?= (intval($_POST['semester'] ?? 0)) === $i ? 'selected' : '' ?>><?= $i ?>st Semester</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="file">Upload File (PDF or DOC) *</label>
                            <input type="file" id="file" name="file" class="form-control" required accept=".pdf,.doc,.docx" style="padding: 0.6rem;">
                            <small style="display: block; margin-top: 0.5rem; color: var(--text-muted);">Max file size: 10MB. Allowed formats: PDF, DOC, DOCX</small>
                        </div>

                        <div style="margin-top: 2rem;">
                            <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%; justify-content: center;">
                                <span class="icon">📤</span> <span id="btnText">Start Upload</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
            btnText.textContent = 'Uploading to Google Drive...';
            
            // Add a loader icon
            const icon = btn.querySelector('.icon');
            icon.innerHTML = '⏳';
            icon.style.animation = 'spin 1s linear infinite';
        });

        // Add spinning animation
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
