<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$pdo = db();
$actions = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['note_id'])) {
    $noteId = (int) $_POST['note_id'];
    if ($_POST['action'] === 'approve') {
        $stmt = $pdo->prepare('UPDATE notes SET status = ? WHERE id = ?');
        $stmt->execute(['approved', $noteId]);
        $actions[] = 'Note approved successfully.';
    }
    if ($_POST['action'] === 'delete') {
        $stmt = $pdo->prepare('SELECT filename FROM notes WHERE id = ?');
        $stmt->execute([$noteId]);
        $note = $stmt->fetch();
        if ($note) {
            @unlink(__DIR__ . '/../uploads/' . $note['filename']);
            $stmt = $pdo->prepare('DELETE FROM notes WHERE id = ?');
            $stmt->execute([$noteId]);
            $actions[] = 'Note deleted successfully.';
        }
    }
}

$notes = $pdo->query('SELECT notes.*, users.full_name FROM notes JOIN users ON users.id = notes.user_id ORDER BY notes.uploaded_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notes | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">StudyShare</div>
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a class="active" href="notes.php">Manage Notes</a>
                <a href="../user/logout.php">Logout</a>
            </nav>
        </aside>
        <main>
            <header class="topbar">
                <h1>Manage Notes</h1>
                <div class="topbar-actions">
                    <a class="button-small" href="dashboard.php">Back to dashboard</a>
                </div>
            </header>

            <?php if ($actions): ?>
                <div class="alert alert-success">
                    <ul>
                        <?php foreach ($actions as $message): ?>
                            <li><?= htmlspecialchars($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <section class="panel">
                <?php if (empty($notes)): ?>
                    <p class="empty-state">No notes available.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Semester</th>
                                <th>Uploaded By</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notes as $note): ?>
                                <tr>
                                    <td><?= $note['id'] ?></td>
                                    <td><?= htmlspecialchars($note['subject_name']) ?></td>
                                    <td><?= htmlspecialchars($note['class']) ?></td>
                                    <td><?= htmlspecialchars($note['semester']) ?></td>
                                    <td><?= htmlspecialchars($note['full_name']) ?></td>
                                    <td><?= ucfirst($note['status']) ?></td>
                                    <td class="actions-cell">
                                        <?php if ($note['status'] === 'pending'): ?>
                                            <form method="post" class="inline-form">
                                                <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                                <button type="submit" name="action" value="approve" class="button button-small">Approve</button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="post" class="inline-form" onsubmit="return confirm('Delete this note permanently?');">
                                            <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                            <button type="submit" name="action" value="delete" class="button button-danger button-small">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
