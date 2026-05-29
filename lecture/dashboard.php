<?php
session_start();
require_once __DIR__ . '/../common/auth_lecturer.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/drive_helper.php';

$lecturer = getCurrentLecturer();
$pdo = db();

// Handle file upload
$upload_message = '';
$upload_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['note_file'])) {
    $subject_id = intval($_POST['subject_id'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    $semester = intval($_POST['semester'] ?? 2);
    
    if ($subject_id <= 0) {
        $upload_error = 'Unable to determine your assigned subject. Please contact admin.';
    } elseif (empty($description)) {
        $upload_error = 'Please enter a description.';
    } elseif ($_FILES['note_file']['error'] !== UPLOAD_ERR_OK) {
        $upload_error = 'File upload failed. Please try again.';
    } else {
        $file = $_FILES['note_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($file_ext !== 'pdf') {
            $upload_error = 'Only PDF files are allowed.';
        } elseif ($file['size'] > 50 * 1024 * 1024) { // 50MB limit
            $upload_error = 'File size exceeds 50MB limit.';
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = __DIR__ . '/../uploads/notes/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $filename = md5($lecturer['id'] . time() . $file['name']) . '.pdf';
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Determine file type and perform OCR if scanned
                require_once __DIR__ . '/../common/ocr_helper.php';
                $isScanned = false;
                $ocrText = null;

                $extractedText = extractTextFromPdf($filepath);
                if (strlen(trim($extractedText)) < 100) {
                    $isScanned = true;
                    // Perform OCR and get text
                    $scanned_dir = __DIR__ . '/../uploads/scanned/';
                    if (!is_dir($scanned_dir)) mkdir($scanned_dir, 0755, true);
                    $ocrText = performOcr($filepath, $scanned_dir);
                }

                // Upload to Google Drive based on file type
                try {
                    $drive = new DriveHelper();
                    $normalDriveLink = null;
                    $scannedDriveLink = null;

                    if ($isScanned) {
                        $scannedDriveId = $drive->uploadFile($filepath, $file['name'], $file['type'], DRIVE_FOLDER_SCANNED);
                        $drive->makePublic($scannedDriveId);
                        $scannedDriveLink = $drive->getViewLink($scannedDriveId);
                    } else {
                        $normalDriveId = $drive->uploadFile($filepath, $file['name'], $file['type'], DRIVE_FOLDER_NORMAL);
                        $drive->makePublic($normalDriveId);
                        $normalDriveLink = $drive->getViewLink($normalDriveId);
                    }

                    // Delete local file after successful upload to Drive
                    if (file_exists($filepath)) @unlink($filepath);

                    // Ensure database columns exist
                    try { $pdo->exec("ALTER TABLE notes ADD COLUMN normal_file_path VARCHAR(255) NULL"); } catch (Exception $e) {}
                    try { $pdo->exec("ALTER TABLE notes ADD COLUMN scanned_file_path VARCHAR(255) NULL"); } catch (Exception $e) {}
                    try { $pdo->exec("ALTER TABLE notes ADD COLUMN ocr_text LONGTEXT NULL"); } catch (Exception $e) {}
                    try { $pdo->exec("ALTER TABLE notes ADD COLUMN is_scanned BOOLEAN DEFAULT 0"); } catch (Exception $e) {}

                    // Insert note into database with Google Drive links
                    $stmt = $pdo->prepare('INSERT INTO notes (subject_id, description, normal_file_path, scanned_file_path, ocr_text, is_scanned, lecturer_id, upload_date, department, semester) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)');
                    $isScannedInt = $isScanned ? 1 : 0;
                    $stmt->execute([
                        $subject_id,
                        $description,
                        $normalDriveLink,
                        $scannedDriveLink,
                        $ocrText,
                        $isScannedInt,
                        $lecturer['id'],
                        $lecturer['department'],
                        $semester
                    ]);
                    $note_id = $pdo->lastInsertId();

                    // Log activity
                    $subj_stmt = $pdo->prepare('SELECT subject_name FROM subjects WHERE id = ?');
                    $subj_stmt->execute([$subject_id]);
                    $subj_name = $subj_stmt->fetchColumn();

                    logActivity($lecturer['id'], 'lecturer', 'upload', $note_id, "Uploaded notes for $subj_name");
                    flash('success', 'Note uploaded and scanned successfully!');
                    redirect('dashboard.php');
                } catch (Exception $e) {
                    $upload_error = 'Google Drive Error: ' . $e->getMessage();
                    if (file_exists($filepath)) @unlink($filepath);
                    if (isset($scanned_filepath) && file_exists($scanned_filepath)) @unlink($scanned_filepath);
                }
            } else {
                $upload_error = 'Failed to save file temporarily. Please try again.';
            }
        }
    }
}

// Get messages
$upload_message = flash('success');

// Get lecturer subject details by assigned subject name (legacy fallback)
$stmt = $pdo->prepare('SELECT * FROM subjects WHERE subject_name = ? LIMIT 1');
$stmt->execute([$lecturer['subject']]);
$lecturer_subject_legacy = $stmt->fetch();

// Fetch newly assigned subjects
$stmt = $pdo->prepare('
    SELECT * FROM lecturer_subject_assignments 
    WHERE lecturer_id = ? AND status = 1 
    ORDER BY created_at DESC
');
$stmt->execute([$lecturer['id']]);
$assigned_subjects = $stmt->fetchAll();

// Get lecturer's notes
$stmt = $pdo->prepare('
    SELECT n.*, s.subject_name, s.color_code 
    FROM notes n 
    LEFT JOIN subjects s ON n.subject_id = s.id 
    WHERE n.lecturer_id = ? 
    ORDER BY n.upload_date DESC
');
$stmt->execute([$lecturer['id']]);
$lecturer_notes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard | StudyShare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/lecturer.css">
</head>
<body>
    <div class="lecturer-layout">
        <aside class="lecturer-sidebar">
            <div class="sidebar-brand">
                <h2>StudyShare</h2>
                <p>Lecturer Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="profile.php" class="nav-item">
                    <span class="icon">👤</span> Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="lecturer-info">
                    <p><?= sanitize($lecturer['name']) ?></p>
                    <small><?= sanitize($lecturer['department']) ?></small>
                </div>
                <a href="logout.php" class="logout-btn">
                    <span class="icon">🚪</span> Logout
                </a>
            </div>
        </aside>

        <main class="lecturer-main">
            <header class="lecturer-header">
                <h1>Welcome back, <?= explode(' ', sanitize($lecturer['name']))[0] ?>! 👋</h1>
                <p>Manage your course materials and track student engagement.</p>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📘</div>
                    <div class="stat-info">
                        <h3>Assigned Subjects</h3>
                        <?php if (!empty($assigned_subjects)): ?>
                            <p class="stat-value" style="font-size: 1.2rem;"><?= count($assigned_subjects) ?> Subject<?= count($assigned_subjects) > 1 ? 's' : '' ?></p>
                        <?php else: ?>
                            <p class="stat-value" style="font-size: 1.2rem;"><?= sanitize($lecturer_subject_legacy['subject_name'] ?? $lecturer['subject'] ?? 'Not assigned') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-info">
                        <h3>Total Notes</h3>
                        <p class="stat-value"><?= count($lecturer_notes) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3>Latest Upload</h3>
                        <p class="stat-value" style="font-size: 1.1rem;">
                            <?= !empty($lecturer_notes) ? date('M d, Y', strtotime($lecturer_notes[0]['upload_date'])) : 'N/A' ?>
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!empty($assigned_subjects)): ?>
            <div style="margin-bottom: 2rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">My Assigned Subjects</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                    <?php foreach ($assigned_subjects as $asub): ?>
                        <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="font-size: 1.5rem;">📘</div>
                                <h3 style="font-size: 1.1rem; font-weight: 600; color: #334155; margin: 0; line-height: 1.2;"><?= sanitize($asub['subject_name']) ?></h3>
                            </div>
                            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.25rem;">Semester: Sem <?= $asub['semester'] ?></div>
                            <div style="font-size: 0.85rem; color: #64748b;">Year: <?= sanitize($asub['year']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="dashboard-grid">
                <section class="section-card">
                    <h2><span>📤</span> Upload New Note</h2>

                    <?php if ($upload_message): ?>
                        <div class="alert alert-success">
                            <span class="icon">✅</span> <?= $upload_message ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($upload_error): ?>
                        <div class="alert alert-error">
                            <span class="icon">⚠️</span> <?= $upload_error ?>
                        </div>
                    <?php endif; ?>

                    <form id="uploadForm" method="POST" enctype="multipart/form-data">
                        
                        <?php if (!empty($assigned_subjects)): ?>
                            <div class="form-group">
                                <label>Assigned Subject *</label>
                                <select name="subject_id" class="form-control" required style="padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                                    <?php foreach ($assigned_subjects as $asub): ?>
                                        <option value="<?= $asub['subject_id'] ?>">
                                            <?= sanitize($asub['subject_name']) ?> (Sem <?= $asub['semester'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="subject_id" value="<?= intval($lecturer_subject_legacy['id'] ?? 0) ?>">
                            <div class="form-group">
                                <label>Subject (Auto-assigned)</label>
                                <input type="text" class="form-control" value="<?= sanitize($lecturer_subject_legacy['subject_name'] ?? $lecturer['subject']) ?>" readonly style="background: #f1f5f9; color: #64748b;">
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="note_file">Select PDF File *</label>
                            <input type="file" id="note_file" name="note_file" class="form-control" accept=".pdf" required style="padding: 0.6rem;">
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" class="form-control" required placeholder="What is this note about?" rows="3"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="semester">Target Semester *</label>
                            <select id="semester" name="semester" class="form-control" required>
                                <?php for($i=1; $i<=8; $i++): ?>
                                    <option value="<?= $i ?>" <?= (intval($student['semester'] ?? 2) == $i) ? 'selected' : '' ?>>Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <button type="submit" id="submitBtn" class="btn btn-primary">
                            <span id="btnText">Upload Note</span>
                        </button>
                    </form>
                </section>

                <section class="section-card">
                    <h2><span>📚</span> Your Uploaded Notes (<?= count($lecturer_notes) ?>)</h2>

                    <div class="table-container">
                        <?php if ($lecturer_notes): ?>
                            <table class="modern-table">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Details</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lecturer_notes as $note): ?>
                                        <tr>
                                            <td>
                                                <div style="font-weight: 600; margin-bottom: 0.25rem;"><?= sanitize(substr($note['description'], 0, 40)) ?><?= strlen($note['description']) > 40 ? '...' : '' ?></div>
                                                <div class="date-text">📅 <?= date('M d, Y', strtotime($note['upload_date'])) ?></div>
                                            </td>
                                            <td>
                                                <span class="subject-badge" style="background-color: <?= $note['color_code'] ?>; font-size: 0.65rem;">
                                                    Sem <?= $note['semester'] ?>
                                                </span>
                                                <div style="font-size: 0.8rem; margin-top: 0.4rem; color: var(--text-muted);">
                                                    👁️ <?= $note['likes'] ?? 0 ?> views
                                                </div>
                                            </td>
                                            <td>
                                                <a href="../user/download.php?id=<?= $note['id'] ?>" target="_blank" class="btn btn-sm" style="background-color: #6366f1; color: white; margin-right: 0.25rem; font-size: 0.8rem; border-radius: 4px; padding: 0.25rem 0.5rem; text-decoration: none; display: inline-block;">View Scanned</a>
                                                <a href="delete-note.php?note_id=<?= $note['id'] ?>" 
                                                   class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('Are you sure you want to delete this note?')">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                <div style="font-size: 3rem; margin-bottom: 1rem;">📝</div>
                                <p>No notes uploaded yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
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
            btnText.textContent = 'Uploading to Drive...';
        });
    </script>
</body>
</html>
