<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';
require_once __DIR__ . '/../common/subjects.php';

$student = getCurrentStudent();
$pdo = db();

$subject_id = intval($_GET['subject_id'] ?? 0);

if ($subject_id <= 0) {
    redirect('dashboard.php');
}

// Get subject details
$stmt = $pdo->prepare('SELECT * FROM subjects WHERE id = ?');
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

if (!$subject) {
    redirect('dashboard.php');
}

// Verify student has access to this subject
$student_subjects = getStudentSubjects($student['roll_no']);
$subject_ids = array_column($student_subjects, 'id');

if (!in_array($subject_id, $subject_ids)) {
    redirect('dashboard.php');
}

// Get all notes for this subject
$stmt = $pdo->prepare('
    SELECT n.*, l.name as lecturer_name 
    FROM notes n 
    LEFT JOIN lecturers l ON n.lecturer_id = l.id 
    WHERE n.subject_id = ? AND n.semester = ?
    ORDER BY n.upload_date DESC
');
$stmt->execute([$subject_id, $student['semester']]);
$notes = $stmt->fetchAll();

// Check if student liked each note
$liked_notes = [];
foreach ($notes as $note) {
    $stmt = $pdo->prepare('SELECT id FROM likes WHERE roll_no = ? AND note_id = ?');
    $stmt->execute([$student['roll_no'], $note['id']]);
    $liked_notes[$note['id']] = $stmt->fetch() !== false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($subject['subject_name']) ?> | StudyShare</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/subjects.css">
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
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="notes.php" class="nav-item active">
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
            <!-- Back Button -->
            <div class="back-button">
                <a href="dashboard.php">← Back to Dashboard</a>
            </div>

            <!-- Subject Header -->
            <div class="subject-header" style="background: linear-gradient(135deg, <?= $subject['color_code'] ?> 0%, <?= adjustBrightness($subject['color_code'], -20) ?> 100%);">
                <h1><?= sanitize($subject['subject_name']) ?></h1>
                <p><?= $notes ? count($notes) . ' notes available' : 'No notes yet' ?></p>
            </div>

            <!-- Notes Grid -->
            <div class="subject-notes-container">
                <div class="notes-grid">
                    <?php if ($notes): ?>
                        <?php foreach ($notes as $note): ?>
                            <div class="note-card">
                                <div class="note-title"><?= sanitize($note['description']) ?: 'Untitled Note' ?></div>
                                <div class="note-lecturer">By <?= $note['lecturer_name'] ?? 'Admin' ?></div>
                                <div class="note-date"><?= date('M d, Y', strtotime($note['upload_date'])) ?></div>
                                
                                <div class="note-actions">
                                    <a href="download.php?id=<?= $note['id'] ?>" class="note-btn note-download">
                                        📥 Download PDF
                                    </a>
                                    <div class="note-views">
                                        <span class="icon">👁️</span>
                                        <span class="count"><?= $note['likes'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data-message">
                            <p>📚 No notes available for this subject yet.</p>
                            <p>Check back soon for updates!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        function adjustBrightness(hex, percent) {
            hex = hex.replace('#', '');
            var rgb = parseInt(hex, 16);
            var r = (rgb >> 16) & 255;
            var g = (rgb >> 8) & 255;
            var b = rgb & 255;
            
            r = Math.max(0, Math.min(255, r + r * percent / 100));
            g = Math.max(0, Math.min(255, g + g * percent / 100));
            b = Math.max(0, Math.min(255, b + b * percent / 100));
            
            return '#' + [r, g, b].map(x => {
                const hex = Math.round(x).toString(16);
                return hex.length === 1 ? '0' + hex : hex;
            }).join('');
        }

        function toggleLike(button, noteId) {
            fetch('../user/like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'note_id=' + noteId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.classList.toggle('liked');
                    button.querySelector('.like-count').textContent = data.likes;
                    button.querySelector('.heart').textContent = data.liked ? '❤️' : '🤍';
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>

<?php
function adjustBrightness($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    $rgb = array_map('hexdec', str_split($hex, 2));
    
    foreach ($rgb as &$value) {
        $value = max(0, min(255, $value + ($value * $percent / 100)));
    }
    
    return '#' . implode('', array_map(function($v) { return str_pad(dechex($v), 2, '0', STR_PAD_LEFT); }, $rgb));
}
?>
