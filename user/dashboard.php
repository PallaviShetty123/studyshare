<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/subjects.php';

// Insert student data if not already in database
insertStudentsData();

$student = getCurrentStudent();
$pdo = db();

// Get all subjects for this student
$student_subjects = getStudentSubjects($student['roll_no']);

// Calculate notes count for each subject
$subject_notes_count = [];
foreach ($student_subjects as $subject) {
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM notes WHERE subject_id = ? AND semester = ?');
    $stmt->execute([$subject['id'], $student['semester']]);
    $count = $stmt->fetch()['count'];
    $subject_notes_count[$subject['id']] = $count;
}

/**
 * Helper function to adjust color brightness
 */
function adjustBrightness($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    $rgb = array_map('hexdec', str_split($hex, 2));
    
    foreach ($rgb as &$value) {
        $value = max(0, min(255, $value + ($value * $percent / 100)));
    }
    
    return '#' . implode('', array_map(function($v) { return str_pad(dechex($v), 2, '0', STR_PAD_LEFT); }, $rgb));
}

/**
 * Helper function to show time ago
 */
function time_ago($timestamp) {
    if (is_string($timestamp)) {
        $timestamp = strtotime($timestamp);
    }
    
    $time_ago = time() - $timestamp;
    
    if ($time_ago < 60) {
        return "just now";
    } elseif ($time_ago < 3600) {
        $mins = floor($time_ago / 60);
        return $mins . " minute" . ($mins > 1 ? "s" : "") . " ago";
    } elseif ($time_ago < 86400) {
        $hours = floor($time_ago / 3600);
        return $hours . " hour" . ($hours > 1 ? "s" : "") . " ago";
    } else {
        $days = floor($time_ago / 86400);
        return $days . " day" . ($days > 1 ? "s" : "") . " ago";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/subjects.css">
    <link rel="stylesheet" href="../assets/css/ai-features.css">
</head>
<body>
    <div class="student-layout">
        <!-- Sidebar -->
        <aside class="student-sidebar">
            <div class="sidebar-brand">
                <h2>📚 StudyShare</h2>
                <p>Student Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="notes.php" class="nav-item">
                    <span class="icon">📝</span> Browse Notes
                </a>
                <a href="profile.php" class="nav-item">
                    <span class="icon">👤</span> Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="student-info">
                    <p><strong><?= sanitize($student['name']) ?></strong></p>
                    <p class="student-roll"><?= sanitize($student['roll_no']) ?></p>
                </div>
                <a href="logout.php" class="btn-logout">Logout</a>
            </div>
        </aside>


        <!-- Main Content -->
        <main class="student-main">
            <!-- Header -->
            <div class="student-header">
                <div class="header-content">
                    <div class="header-text">
                        <h1>Welcome back, <?= sanitize($student['name']) ?>! 👋</h1>
                        <p>Ready to continue your learning journey?</p>
                    </div>
                    <div class="header-actions">
                        <div class="search-container">
                            <input type="text" id="subject-search" placeholder="Search subjects..." class="search-input">
                            <button class="search-btn">🔍</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <span class="stat-icon">📚</span>
                    </div>
                    <div class="stat-content">
                        <p class="stat-value"><?= count($student_subjects) ?></p>
                        <p class="stat-label">Active Subjects</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <span class="stat-icon">📄</span>
                    </div>
                    <div class="stat-content">
                        <p class="stat-value"><?= array_sum($subject_notes_count) ?></p>
                        <p class="stat-label">Available Notes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <span class="stat-icon">🌐</span>
                    </div>
                    <div class="stat-content">
                        <p class="stat-value"><?= isHindiStudent($student['roll_no']) ? 'Hindi' : 'Kannada' ?></p>
                        <p class="stat-label">Language Subject</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <span class="stat-icon">🎯</span>
                    </div>
                    <div class="stat-content">
                        <p class="stat-value">Semester <?= $student['semester'] ?></p>
                        <p class="stat-label">Current Semester</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="actions-grid">
                    <a href="notes.php" class="action-card">
                        <span class="action-icon">🔍</span>
                        <span class="action-text">Browse All Notes</span>
                    </a>
                    <a href="profile.php" class="action-card">
                        <span class="action-icon">👤</span>
                        <span class="action-text">Update Profile</span>
                    </a>
                    <a href="#" class="action-card">
                        <span class="action-icon">📊</span>
                        <span class="action-text">View Progress</span>
                    </a>
                    <a href="#" class="action-card">
                        <span class="action-icon">📅</span>
                        <span class="action-text">Academic Calendar</span>
                    </a>
                </div>
            </div>

            <!-- Subjects Section -->
            <section class="subjects-section">
                <div class="section-header">
                    <h2 class="section-title">📖 Your Subjects</h2>
                    <p class="section-subtitle">Explore your curriculum and access study materials</p>
                </div>
                
                <div class="subjects-filters">
                    <button class="filter-btn active" data-filter="all">All Subjects</button>
                    <button class="filter-btn" data-filter="compulsory">Compulsory</button>
                    <button class="filter-btn" data-filter="language">Language</button>
                </div>
                
                <div class="subjects-container">
                    <?php foreach ($student_subjects as $subject): ?>
                        <div class="subject-card-wrapper" data-subject-type="<?= $subject['language_specific'] ? 'language' : 'compulsory' ?>">
                            <a href="subject-notes.php?subject_id=<?= $subject['id'] ?>" class="subject-card">
                                <div class="subject-card-header" style="background: linear-gradient(135deg, <?= $subject['color_code'] ?> 0%, <?= adjustBrightness($subject['color_code'], -15) ?> 100%);">
                                    <div class="subject-icon">📘</div>
                                    <div class="subject-type-badge">
                                        <?= $subject['language_specific'] ? 'Language' : 'Core' ?>
                                    </div>
                                </div>
                                <div class="subject-card-body">
                                    <h3 class="subject-title"><?= sanitize($subject['subject_name']) ?></h3>
                                    <p class="subject-code"><?= sanitize($subject['subject_code'] ?? 'CODE') ?></p>
                                    <div class="subject-stats">
                                        <span class="stat-item">
                                            <span class="stat-icon">📄</span>
                                            <?= $subject_notes_count[$subject['id']] ?? 0 ?> notes
                                        </span>
                                        <span class="stat-item">
                                            <span class="stat-icon">👨‍🏫</span>
                                            Faculty Assigned
                                        </span>
                                    </div>
                                </div>
                                <div class="subject-card-footer">
                                    <div class="subject-actions">
                                        <button class="action-btn primary">View Notes</button>
                                        <button class="action-btn secondary">Resources</button>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Recent Notes Section -->
            <section class="recent-notes-section">
                <h2 class="section-title">🔥 Recently Uploaded</h2>
                <div class="recent-notes-list">
                    <?php
                    $subject_ids = array_column($student_subjects, 'id');
                    if (!empty($subject_ids)) {
                        $placeholders = implode(',', array_fill(0, count($subject_ids), '?'));
                        $stmt = $pdo->prepare("
                            SELECT n.*, s.subject_name, s.color_code, l.name as lecturer_name 
                            FROM notes n 
                            LEFT JOIN subjects s ON n.subject_id = s.id 
                            LEFT JOIN lecturers l ON n.lecturer_id = l.id 
                            WHERE n.subject_id IN ($placeholders) AND n.semester = ?
                            ORDER BY n.upload_date DESC 
                            LIMIT 5
                        ");
                        $stmt->execute(array_merge($subject_ids, [$student['semester']]));
                        $recent_notes = $stmt->fetchAll();
                        
                        if ($recent_notes) {
                            foreach ($recent_notes as $note) {
                                ?>
                                <div class="recent-note-card">
                                    <div class="note-color-bar" style="background-color: <?= $note['color_code'] ?>"></div>
                                    <div class="note-content">
                                        <h4><?= sanitize($note['subject_name']) ?></h4>
                                        <p class="note-title-text"><?= sanitize($note['description']) ?></p>
                                        <p class="note-meta">Uploaded by <?= $note['lecturer_name'] ?? 'Admin' ?> • <?= time_ago($note['upload_date']) ?> • 👁️ <?= $note['likes'] ?> views</p>
                                    </div>
                                    <div class="note-actions">
                                        <button class="ai-btn smart-note-btn" onclick="generateSmartNote(<?= $note['id'] ?>, '<?= htmlspecialchars($note['subject_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($note['description'], ENT_QUOTES) ?>')">✨ Smart Notes</button>
                                        <button class="ai-btn quiz-btn" onclick="generateQuiz(<?= $note['id'] ?>, '<?= htmlspecialchars($note['subject_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($note['description'], ENT_QUOTES) ?>')">🧠 Take Quiz</button>
                                        <a href="download.php?id=<?= $note['id'] ?>" class="btn-small btn-download">Download</a>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="no-data">No notes available yet. Check back soon!</p>';
                        }
                    }
                    ?>
                </div>
            </section>
        </main>
    </div>

    <!-- AI Modal Container -->
    <div id="aiModal" class="ai-modal">
        <div class="ai-modal-content">
            <span class="ai-close-btn">&times;</span>
            <div id="aiModalBody"></div>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/ai-features.js"></script>
    <script>
        // Search functionality
        document.getElementById('subject-search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const subjectCards = document.querySelectorAll('.subject-card-wrapper');
            
            subjectCards.forEach(card => {
                const title = card.querySelector('.subject-title').textContent.toLowerCase();
                const code = card.querySelector('.subject-code').textContent.toLowerCase();
                const isVisible = title.includes(searchTerm) || code.includes(searchTerm);
                card.style.display = isVisible ? 'block' : 'none';
            });
        });

        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                const subjectCards = document.querySelectorAll('.subject-card-wrapper');
                
                subjectCards.forEach(card => {
                    const type = card.dataset.subjectType;
                    if (filter === 'all' || type === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Add hover effects
        document.querySelectorAll('.subject-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
            });
        });
    </script>
</body>
</html>
