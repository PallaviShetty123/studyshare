<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

// Ensure the activity_log table exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(50) NOT NULL,
        user_type VARCHAR(20) NOT NULL,
        action VARCHAR(50) NOT NULL,
        target_id VARCHAR(255),
        details TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Fetch activity logs with user names
$logs = $pdo->query("
    SELECT 
        l.*, 
        CASE 
            WHEN l.user_type = 'admin' THEN a.username 
            WHEN l.user_type = 'lecturer' THEN lec.name 
            WHEN l.user_type = 'student' THEN s.name
            ELSE 'System'
        END as actor_name
    FROM activity_log l
    LEFT JOIN admin a ON l.user_id = a.id AND l.user_type = 'admin'
    LEFT JOIN lecturers lec ON l.user_id = lec.id AND l.user_type = 'lecturer'
    LEFT JOIN students s ON l.user_id = s.roll_no AND l.user_type = 'student'
    ORDER BY l.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Fetch stats for the dashboard
$uploadsToday = $pdo->query("SELECT COUNT(*) FROM notes WHERE DATE(upload_date) = CURDATE()")->fetchColumn();
$newStudentsToday = $pdo->query("SELECT COUNT(*) FROM students WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$downloadsToday = $pdo->query("SELECT COUNT(*) FROM downloads WHERE DATE(download_date) = CURDATE()")->fetchColumn();
$deletedNotes = $pdo->query("SELECT COUNT(*) FROM activity_log WHERE action = 'DELETE' AND details LIKE '%note%'")->fetchColumn();
$activeStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

$stats = [
    'uploads_today' => (int)$uploadsToday,
    'new_students' => (int)$newStudentsToday,
    'downloads_today' => (int)$downloadsToday,
    'deleted_notes' => (int)$deletedNotes,
    'active_students' => (int)$activeStudents
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Log | StudyShare Admin</title>
    
    <!-- Fonts & Existing CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: { preflight: false },
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                            950: '#2e1065',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        /* Base overrides for React section */
        #react-activity-root * {
            box-sizing: border-box;
        }
        #react-activity-root input, 
        #react-activity-root select, 
        #react-activity-root button {
            font-family: 'Inter', sans-serif;
            outline: none;
        }
        
        /* Smooth scrolling inside feed */
        .feed-container::-webkit-scrollbar {
            width: 6px;
        }
        .feed-container::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 4px;
        }
        .feed-container::-webkit-scrollbar-track {
            background-color: transparent;
        }
    </style>

    <!-- React, ReactDOM, and Babel CDNs -->
    <script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>

    <!-- Initial Data Injection -->
    <script>
        window.INITIAL_LOGS = <?= json_encode($logs) ?>;
        window.INITIAL_STATS = <?= json_encode($stats) ?>;
    </script>
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
                <a href="assign_subjects.php" class="nav-item">
                    <span class="icon">📘</span> Assign Subjects
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

        <main class="admin-content" style="padding: 0; background: #f8fafc; height: 100vh; overflow: hidden;">
            <div id="react-activity-root"></div>
        </main>
    </div>

    <!-- React Application Logic -->
    <script type="text/babel" src="components/activityLogApp.jsx"></script>
</body>
</html>
