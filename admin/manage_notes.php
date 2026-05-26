<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

// Get all notes
$notes = $pdo->query('
    SELECT n.*, s.subject_name, s.subject_code 
    FROM notes n 
    LEFT JOIN subjects s ON n.subject_id = s.id 
    ORDER BY n.upload_date DESC
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notes | StudyShare Admin</title>
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
                <h1>Manage Notes</h1>
                <p>View, edit, and delete uploaded notes</p>
            </header>

            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Upload Date</th>
                            <th>Views</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($notes): ?>
                            <?php foreach ($notes as $note): ?>
                                <tr>
                                    <td style="font-weight: 600;">
                                        <?= sanitize($note['subject_name'] ?? 'Unknown') ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= sanitize($note['subject_code'] ?? '') ?></div>
                                    </td>
                                    <td style="max-width: 200px; font-size: 0.9rem; color: var(--text-muted);">
                                        <?= sanitize(substr($note['description'], 0, 50)) ?><?= strlen($note['description']) > 50 ? '...' : '' ?>
                                    </td>
                                    <td><span class="badge badge-info"><?= sanitize($note['department']) ?></span></td>
                                    <td>Sem <?= $note['semester'] ?></td>
                                    <td><?= date('M d, Y', strtotime($note['upload_date'])) ?></td>
                                    <td>
                                        <span style="display: inline-flex; align-items: center; gap: 0.25rem;">
                                            👁️ <?= $note['likes'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.5rem;">
                                            <a href="edit_note.php?id=<?= $note['id'] ?>" class="btn btn-sm" style="background: #eef2ff; color: #6366f1;">Edit</a>
                                            <a href="delete_note.php?id=<?= $note['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this note? This action cannot be undone.')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                                    <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                                    <p>No notes found. <a href="upload_notes.php" style="color: var(--primary); font-weight: 600;">Upload one?</a></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
