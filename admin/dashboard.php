<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

// Get statistics
$stats = [
    'total_students' => $pdo->query('SELECT COUNT(*) as count FROM students')->fetch()['count'],
    'total_notes' => $pdo->query('SELECT COUNT(*) as count FROM notes')->fetch()['count'],
    'total_likes' => $pdo->query('SELECT SUM(likes) as count FROM notes')->fetch()['count'] ?? 0,
];

// Get recent notes
$recent_notes = $pdo->query('
    SELECT n.*, s.subject_name 
    FROM notes n 
    LEFT JOIN subjects s ON n.subject_id = s.id 
    ORDER BY n.upload_date DESC 
    LIMIT 10
')->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | StudyShare Admin</title>
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
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="upload_notes.php" class="nav-item">
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
                <h1>Dashboard</h1>
                <p>Welcome back, <?= sanitize($admin['username']) ?>!</p>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>Total Students</h3>
                        <p class="stat-value"><?= $stats['total_students'] ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-info">
                        <h3>Total Notes</h3>
                        <p class="stat-value"><?= $stats['total_notes'] ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-info">
                        <h3>Total Views</h3>
                        <p class="stat-value"><?= $stats['total_likes'] ?></p>
                    </div>
                </div>
            </div>

            <section class="recent-section">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700;">Recent Uploads</h2>
                    <a href="manage_notes.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Department</th>
                                <th>Semester</th>
                                <th>Upload Date</th>
                                <th>Views</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_notes): ?>
                                <?php foreach ($recent_notes as $note): ?>
                                    <tr>
                                        <td style="font-weight: 600;"><?= sanitize($note['subject_name'] ?? 'Unknown') ?></td>
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
                                                <a href="delete_note.php?id=<?= $note['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                                        No notes uploaded yet. <a href="upload_notes.php" style="color: var(--primary); font-weight: 600;">Upload now</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
