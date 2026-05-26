<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$student = getCurrentStudent();
$pdo = db();

$search = sanitize($_GET['search'] ?? '');
$subject = sanitize($_GET['subject'] ?? '');
$semester = sanitize($_GET['semester'] ?? '');
$file_type = sanitize($_GET['file_type'] ?? '');

$query = 'SELECT n.*, s.subject_name, s.subject_code, l.name AS lecturer_name
          FROM notes n
          LEFT JOIN subjects s ON n.subject_id = s.id
          LEFT JOIN lecturers l ON n.lecturer_id = l.id
          WHERE n.department = ?';
$params = [$student['department']];

if ($semester) {
    $query .= ' AND n.semester = ?';
    $params[] = $semester;
}

if ($subject) {
    $query .= ' AND s.subject_name = ?';
    $params[] = $subject;
}

if ($search) {
    $query .= ' AND (n.description LIKE ? OR s.subject_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($file_type) {
    $query .= ' AND n.file_path LIKE ?';
    $params[] = "%.$file_type";
}

$query .= ' ORDER BY n.upload_date DESC';

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $notes = $stmt->fetchAll();

    $html = '';
    if ($notes) {
        foreach ($notes as $note) {
            // Check if student liked this note
            $likeStmt = $pdo->prepare('SELECT id FROM likes WHERE roll_no = ? AND note_id = ?');
            $likeStmt->execute([$student['roll_no'], $note['id']]);
            $liked = $likeStmt->fetch() !== false;

            $html .= '<div class="note-item">';
            $html .= '<div class="note-content">';
            $html .= '<h3>' . sanitize($note['subject_name'] ?? 'Unknown Subject') . '</h3>';
            $html .= '<p class="note-description">' . sanitize($note['description']) . '</p>';
            $html .= '<div class="note-meta">';
            $html .= '<span class="meta-item">📅 ' . date('M d, Y', strtotime($note['upload_date'])) . '</span>';
            $html .= '<span class="meta-item">📂 ' . sanitize($note['department']) . '</span>';
            $html .= '</div></div>';
            
            $html .= '<div class="note-actions">';
            $likedClass = $liked ? 'liked' : '';
            $html .= '<button class="like-btn ' . $likedClass . '" data-note-id="' . $note['id'] . '" onclick="toggleLike(this, ' . $note['id'] . ')">';
            $html .= '<span class="heart">❤️</span>';
            $html .= '<span class="like-count">' . $note['likes'] . '</span></button>';
            
            $html .= '<button class="ai-btn smart-note-btn" onclick="generateSmartNote(' . $note['id'] . ', \'' . htmlspecialchars($note['subject_name'], ENT_QUOTES) . '\', \'' . htmlspecialchars($note['description'], ENT_QUOTES) . '\')">✨ Smart Notes</button>';
            $html .= '<button class="ai-btn quiz-btn" onclick="generateQuiz(' . $note['id'] . ', \'' . htmlspecialchars($note['subject_name'], ENT_QUOTES) . '\', \'' . htmlspecialchars($note['description'], ENT_QUOTES) . '\')">🧠 Take Quiz</button>';
            $html .= '<a href="download.php?id=' . $note['id'] . '" class="download-btn">⬇️ Download</a>';
            $html .= '</div></div>';
        }
    } else {
        $html = '<p class="no-data">No notes match your filters.</p>';
    }

    echo json_encode([
        'success' => true,
        'count' => count($notes),
        'html' => $html
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
