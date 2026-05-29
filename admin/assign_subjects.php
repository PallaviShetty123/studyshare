<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

// Handle Form Submission — supports MULTIPLE subjects at once
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_subject'])) {
    $lecturer_id = intval($_POST['lecturer_id']);
    $subject_ids = $_POST['subject_ids'] ?? [];
    $year = sanitize($_POST['year']);
    $semester = intval($_POST['semester']);
    $academic_year = sanitize($_POST['academic_year']);
    $status = isset($_POST['status']) ? 1 : 0;

    if ($lecturer_id && !empty($subject_ids) && $academic_year) {
        $stmt = $pdo->prepare("SELECT department FROM lecturers WHERE id = ?");
        $stmt->execute([$lecturer_id]);
        $department = $stmt->fetchColumn();

        $insertStmt = $pdo->prepare("
            INSERT INTO lecturer_subject_assignments 
            (lecturer_id, subject_id, subject_name, department, year, semester, academic_year, assigned_by, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // Check for duplicates
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM lecturer_subject_assignments WHERE lecturer_id = ? AND subject_id = ? AND semester = ? AND academic_year = ?");

        $assigned_count = 0;
        $skipped = 0;
        foreach ($subject_ids as $sid) {
            $sid = intval($sid);
            // Check duplicate
            $checkStmt->execute([$lecturer_id, $sid, $semester, $academic_year]);
            if ($checkStmt->fetchColumn() > 0) {
                $skipped++;
                continue;
            }

            $subStmt = $pdo->prepare("SELECT subject_name FROM subjects WHERE id = ?");
            $subStmt->execute([$sid]);
            $subject_name = $subStmt->fetchColumn();

            if ($subject_name && $department) {
                $insertStmt->execute([
                    $lecturer_id, $sid, $subject_name, $department,
                    $year, $semester, $academic_year, $admin['id'], $status
                ]);
                $assigned_count++;
                logActivity($admin['id'], 'admin', 'assign_subject', $lecturer_id, "Assigned subject $subject_name to lecturer ID $lecturer_id");
            }
        }

        if ($assigned_count > 0 && $skipped > 0) {
            flash('success', "$assigned_count subject(s) assigned successfully! $skipped already assigned (skipped).");
        } elseif ($assigned_count > 0) {
            flash('success', "$assigned_count subject(s) assigned successfully!");
        } else {
            flash('success', "All selected subjects were already assigned (skipped).");
        }
        redirect('assign_subjects.php');
    }
}

// Handle Action (Toggle/Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'toggle') {
        $pdo->prepare("UPDATE lecturer_subject_assignments SET status = NOT status WHERE id = ?")->execute([$id]);
        flash('success', 'Assignment status updated.');
    } elseif ($_GET['action'] === 'delete') {
        $pdo->prepare("DELETE FROM lecturer_subject_assignments WHERE id = ?")->execute([$id]);
        flash('success', 'Assignment removed.');
    }
    redirect('assign_subjects.php');
}

// Fetch Data for Dropdowns
$lecturers = $pdo->query("SELECT id, name, department FROM lecturers ORDER BY name")->fetchAll();
$subjects = $pdo->query("SELECT id, subject_name FROM subjects ORDER BY subject_name")->fetchAll();

// Fetch Assignments for Table
$assignments = $pdo->query("
    SELECT a.*, l.name as lecturer_name 
    FROM lecturer_subject_assignments a 
    JOIN lecturers l ON a.lecturer_id = l.id 
    ORDER BY l.name ASC, a.created_at DESC
")->fetchAll();

$message = flash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Subjects | StudyShare Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .subject-checkbox-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            max-height: 260px;
            overflow-y: auto;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
        }
        .subject-checkbox-grid::-webkit-scrollbar { width: 5px; }
        .subject-checkbox-grid::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .subject-checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.6rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.15s;
            font-size: 0.85rem;
        }
        .subject-checkbox-item:hover {
            border-color: #a78bfa;
            background: #f5f3ff;
        }
        .subject-checkbox-item input[type="checkbox"] {
            accent-color: #6366f1;
            width: 1rem;
            height: 1rem;
            cursor: pointer;
        }
        .subject-checkbox-item.checked {
            border-color: #6366f1;
            background: #eef2ff;
        }
        .select-actions {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .select-actions button {
            padding: 0.25rem 0.6rem;
            font-size: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            background: white;
            cursor: pointer;
            color: #6366f1;
            font-weight: 600;
        }
        .select-actions button:hover { background: #eef2ff; }
        .selected-count {
            font-size: 0.8rem;
            color: #6366f1;
            font-weight: 600;
            margin-left: auto;
        }
    </style>
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
                <a href="assign_subjects.php" class="nav-item active">
                    <span class="icon">📘</span> Assign Subjects
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
                <h1>Assign Subjects</h1>
                <p>Assign multiple subjects to lecturers dynamically</p>
            </header>

            <?php if ($message): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem; background: #ecfdf5; color: #065f46; padding: 1rem; border-radius: 8px;">
                    <span class="icon">✅</span> <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="content-body" style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                <!-- Form Section -->
                <div class="form-container" style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">New Assignment</h2>
                    <form method="POST" action="assign_subjects.php">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Select Lecturer *</label>
                            <select name="lecturer_id" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <option value="">-- Select Lecturer --</option>
                                <?php foreach ($lecturers as $l): ?>
                                    <option value="<?= $l['id'] ?>"><?= sanitize($l['name']) ?> (<?= sanitize($l['department']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Select Subjects * <span class="selected-count" id="selectedCount">(0 selected)</span></label>
                            <div class="select-actions">
                                <button type="button" onclick="toggleAll(true)">Select All</button>
                                <button type="button" onclick="toggleAll(false)">Deselect All</button>
                            </div>
                            <div class="subject-checkbox-grid" id="subjectGrid">
                                <?php foreach ($subjects as $s): ?>
                                    <label class="subject-checkbox-item" id="item-<?= $s['id'] ?>">
                                        <input type="checkbox" name="subject_ids[]" value="<?= $s['id'] ?>" onchange="updateCount(); toggleHighlight(this)">
                                        <?= sanitize($s['subject_name']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Year *</label>
                            <select name="year" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Semester *</label>
                            <select name="semester" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                                <?php for($i=1; $i<=8; $i++): ?>
                                    <option value="<?= $i ?>">Semester <?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Academic Year *</label>
                            <input type="text" name="academic_year" placeholder="e.g. 2025-2026" class="form-control" required style="width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 6px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 500; cursor: pointer;">
                                <input type="checkbox" name="status" checked style="width: 1.25rem; height: 1.25rem; accent-color: #6366f1;">
                                Active Assignment
                            </label>
                        </div>

                        <button type="submit" name="assign_subject" class="btn btn-primary" style="width: 100%; padding: 0.75rem; background: #6366f1; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Assign Subjects</button>
                    </form>
                </div>

                <!-- Table Section -->
                <div class="table-container" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);">
                    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Assignment Management <span style="font-weight: 400; color: #94a3b8; font-size: 0.9rem;">(<?= count($assignments) ?> total)</span></h2>
                    <?php if ($assignments): ?>
                        <div style="overflow-x: auto;">
                        <table class="admin-table" style="width: 100%; text-align: left;">
                            <thead>
                                <tr>
                                    <th style="padding-bottom: 1rem; border-bottom: 2px solid #f1f5f9;">Lecturer</th>
                                    <th style="padding-bottom: 1rem; border-bottom: 2px solid #f1f5f9;">Subject Details</th>
                                    <th style="padding-bottom: 1rem; border-bottom: 2px solid #f1f5f9;">Academic Year</th>
                                    <th style="padding-bottom: 1rem; border-bottom: 2px solid #f1f5f9;">Status</th>
                                    <th style="padding-bottom: 1rem; border-bottom: 2px solid #f1f5f9;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($assignments as $a): ?>
                                    <tr>
                                        <td style="padding: 1rem 0; border-bottom: 1px solid #f1f5f9;">
                                            <div style="font-weight: 600;"><?= sanitize($a['lecturer_name']) ?></div>
                                            <span class="badge badge-info" style="font-size: 0.7rem;"><?= sanitize($a['department']) ?></span>
                                        </td>
                                        <td style="padding: 1rem 0; border-bottom: 1px solid #f1f5f9;">
                                            <div style="font-weight: 600; color: #4338ca;"><?= sanitize($a['subject_name']) ?></div>
                                            <div style="font-size: 0.8rem; color: #64748b;">Sem <?= $a['semester'] ?> • <?= sanitize($a['year']) ?></div>
                                        </td>
                                        <td style="padding: 1rem 0; border-bottom: 1px solid #f1f5f9;"><?= sanitize($a['academic_year']) ?></td>
                                        <td style="padding: 1rem 0; border-bottom: 1px solid #f1f5f9;">
                                            <?php if ($a['status']): ?>
                                                <span class="badge badge-success" style="background: #ecfdf5; color: #059669;">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger" style="background: #fef2f2; color: #dc2626;">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 1rem 0; border-bottom: 1px solid #f1f5f9;">
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="?action=toggle&id=<?= $a['id'] ?>" class="btn btn-sm" style="background: #f1f5f9; color: #475569; padding: 0.25rem 0.5rem; border-radius: 4px; text-decoration: none; font-size: 0.8rem;"><?= $a['status'] ? 'Disable' : 'Enable' ?></a>
                                                <a href="?action=delete&id=<?= $a['id'] ?>" class="btn btn-sm" style="background: #fee2e2; color: #dc2626; padding: 0.25rem 0.5rem; border-radius: 4px; text-decoration: none; font-size: 0.8rem;" onclick="return confirm('Remove this assignment?')">Remove</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 3rem; color: #94a3b8;">
                            <div style="font-size: 3rem; margin-bottom: 1rem;">📘</div>
                            <p>No subjects assigned yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function updateCount() {
            const checked = document.querySelectorAll('#subjectGrid input[type="checkbox"]:checked').length;
            document.getElementById('selectedCount').textContent = '(' + checked + ' selected)';
        }

        function toggleHighlight(cb) {
            const item = cb.closest('.subject-checkbox-item');
            if (cb.checked) {
                item.classList.add('checked');
            } else {
                item.classList.remove('checked');
            }
        }

        function toggleAll(state) {
            document.querySelectorAll('#subjectGrid input[type="checkbox"]').forEach(cb => {
                cb.checked = state;
                toggleHighlight(cb);
            });
            updateCount();
        }
    </script>
</body>
</html>
