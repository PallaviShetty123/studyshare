<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$student = getCurrentStudent();
$pdo = db();

// Get filter parameters
$subject_filter = sanitize($_GET['subject'] ?? '');

// Build query
$query = 'SELECT n.*, s.subject_name, s.subject_code, l.name AS lecturer_name
          FROM notes n
          LEFT JOIN subjects s ON n.subject_id = s.id
          LEFT JOIN lecturers l ON n.lecturer_id = l.id
          WHERE n.department = ? AND n.semester = ?';
$params = [$student['department'], $student['semester']];

if ($subject_filter) {
    $query .= ' AND s.subject_name = ?';
    $params[] = $subject_filter;
}

$query .= ' ORDER BY n.upload_date DESC';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$notes = $stmt->fetchAll();

// Get unique subjects
$stmt = $pdo->prepare('SELECT DISTINCT s.subject_name AS subject FROM notes n
                       LEFT JOIN subjects s ON n.subject_id = s.id
                       WHERE n.department = ? AND n.semester = ?
                       ORDER BY s.subject_name');
$stmt->execute([$student['department'], $student['semester']]);
$subjects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Notes | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/notes.css">
    <link rel="stylesheet" href="../assets/css/ai-features.css">
</head>
<body>
    <div class="student-layout">
        <aside class="student-sidebar">
            <div class="sidebar-brand">
                <h2>StudyShare</h2>
                <p>Student Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="notes.php" class="nav-item active">
                    <span class="icon">📚</span> Browse Notes
                </a>
                <a href="profile.php" class="nav-item">
                    <span class="icon">👤</span> Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="student-info">
                    <p><?= sanitize($student['name']) ?></p>
                    <small><?= sanitize($student['roll_no']) ?></small>
                </div>
                <a href="../logout.php" class="logout-btn">Logout</a>
            </div>
        </aside>

        <main class="student-content">
            <header class="student-header">
                <div>
                    <h1>Browse Notes</h1>
                    <p>View and download study materials</p>
                </div>
            </header>

            <section class="filter-section advanced-search-container">
                <div class="search-bar-wrapper">
                    <input type="text" id="searchInput" placeholder="Search by description or title..." class="search-input">
                    <span class="search-icon">🔍</span>
                </div>
                <div class="filter-dropdowns">
                    <select id="subjectFilter" class="filter-select">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= htmlspecialchars($subject['subject']) ?>" <?= $subject_filter === $subject['subject'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($subject['subject']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select id="semesterFilter" class="filter-select">
                        <option value="">All Semesters</option>
                        <option value="1">Semester 1</option>
                        <option value="2" selected>Semester 2</option>
                        <option value="3">Semester 3</option>
                        <option value="4">Semester 4</option>
                        <option value="5">Semester 5</option>
                        <option value="6">Semester 6</option>
                    </select>

                    <select id="fileTypeFilter" class="filter-select">
                        <option value="">All File Types</option>
                        <option value="pdf">PDF</option>
                        <option value="ppt">Presentation</option>
                        <option value="doc">Document</option>
                    </select>
                </div>
            </section>

            <section class="notes-list-section">
                <h2 id="notes-title"><?= $subject_filter ? sanitize($subject_filter) . ' Notes' : 'All Notes' ?> (<span id="notes-count"><?= count($notes) ?></span>)</h2>
                
                <div class="notes-list" id="notes-container">
                    <?php if ($notes): ?>
                        <?php foreach ($notes as $note): 
                            // Check if student liked this note
                            $stmt = $pdo->prepare('SELECT id FROM likes WHERE roll_no = ? AND note_id = ?');
                            $stmt->execute([$student['roll_no'], $note['id']]);
                            $liked = $stmt->fetch() !== false;
                        ?>
                            <div class="note-item">
                                <div class="note-content">
                                    <h3><?= sanitize($note['subject_name'] ?? 'Unknown Subject') ?></h3>
                                    <p class="note-description"><?= sanitize($note['description']) ?></p>
                                    <div class="note-meta">
                                        <span class="meta-item">📅 <?= date('M d, Y', strtotime($note['upload_date'])) ?></span>
                                        <span class="meta-item">📂 <?= sanitize($note['department']) ?></span>
                                    </div>
                                </div>
                                <div class="note-actions">
                                    <div class="view-btn">
                                        <span class="icon">👁️</span>
                                        <span class="view-count"><?= $note['likes'] ?></span>
                                    </div>
                                    <button class="ai-btn smart-note-btn" onclick="generateSmartNote(<?= $note['id'] ?>, '<?= htmlspecialchars($note['subject_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($note['description'], ENT_QUOTES) ?>')">✨ Smart Notes</button>
                                    <button class="ai-btn quiz-btn" onclick="generateQuiz(<?= $note['id'] ?>, '<?= htmlspecialchars($note['subject_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($note['description'], ENT_QUOTES) ?>')">🧠 Take Quiz</button>
                                    <a href="download.php?id=<?= $note['id'] ?>" class="download-btn">⬇️ Download</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-data">No notes available for this subject.</p>
                    <?php endif; ?>
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
</body>
</html>
