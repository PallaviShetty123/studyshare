<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

// Fetch activity logs with user names
$logs = $pdo->query("
    SELECT 
        l.*, 
        CASE 
            WHEN l.user_type = 'admin' THEN a.username 
            WHEN l.user_type = 'lecturer' THEN lec.name 
        END as actor_name
    FROM activity_log l
    LEFT JOIN admin a ON l.user_id = a.id AND l.user_type = 'admin'
    LEFT JOIN lecturers lec ON l.user_id = lec.id AND l.user_type = 'lecturer'
    ORDER BY l.created_at DESC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log | StudyShare Admin</title>
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
                <a href="manage_notes.php" class="nav-item">
                    <span class="icon">📋</span> Manage Notes
                </a>
                <a href="manage_students.php" class="nav-item">
                    <span class="icon">👥</span> Manage Students
                </a>
                <a href="activity_log.php" class="nav-item active">
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
                <h1>Activity Log</h1>
                <p>Track all administrative actions</p>
            </header>

            <div class="content-body">
                <div class="table-container">
                    <?php if ($logs): ?>
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 500;"><?= date('M d, Y', strtotime($log['created_at'])) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted);"><?= date('H:i', strtotime($log['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600;"><?= sanitize($log['actor_name'] ?? 'Unknown') ?></div>
                                            <span class="badge badge-info" style="font-size: 0.65rem;"><?= $log['user_type'] ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                                $action = strtoupper($log['action']);
                                                $badgeClass = ($action === 'DELETE') ? 'danger' : (($action === 'UPLOAD') ? 'success' : 'info');
                                            ?>
                                            <span class="badge badge-<?= $badgeClass ?>">
                                                <?= $action ?>
                                            </span>
                                        </td>
                                        <td style="color: var(--text-muted); font-size: 0.9rem;"><?= sanitize($log['details']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 4rem; color: var(--text-muted);">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📜</div>
                            <p>No activity logs found yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
