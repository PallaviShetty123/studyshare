<?php
require_once __DIR__ . '/../common/auth_admin.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$admin = getCurrentAdmin();
$pdo = db();

$errors = [];
$success = '';

// Handle CSV import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['csv_file']['name'])) {
    if ($_FILES['csv_file']['type'] !== 'text/csv' && $_FILES['csv_file']['type'] !== 'application/vnd.ms-excel') {
        $errors[] = 'Please upload a valid CSV file.';
    } else {
        $file = $_FILES['csv_file']['tmp_name'];
        $row = 0;
        $imported = 0;
        
        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                if ($row === 1) continue; // Skip header
                
                if (count($data) >= 5) {
                    $roll_no = trim($data[0]);
                    $name = trim($data[1]);
                    $dob = trim($data[2]);
                    $department = trim($data[3]);
                    $semester = intval(trim($data[4]));

                    if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $dob, $matches)) {
                        $dob = sprintf('%s-%s-%s', $matches[3], $matches[2], $matches[1]);
                    }

                    if (!empty($roll_no) && !empty($name) && !empty($dob)) {
                        $stmt = $pdo->prepare('INSERT IGNORE INTO students (roll_no, name, dob, department, semester) VALUES (?, ?, ?, ?, ?)');
                        if ($stmt->execute([$roll_no, $name, $dob, $department, $semester])) {
                            $imported++;
                        }
                    }
                }
            }
            fclose($handle);
            $success = "Imported $imported students successfully!";
        }
    }
}

// Fetch filter values dynamically from DB
$departments = $pdo->query('SELECT DISTINCT department FROM students ORDER BY department')->fetchAll(PDO::FETCH_COLUMN);
$semesters = $pdo->query('SELECT DISTINCT semester FROM students ORDER BY semester')->fetchAll(PDO::FETCH_COLUMN);

// Handle filtering
$filter_dept = $_GET['department'] ?? '';
$filter_sem = $_GET['semester'] ?? '';

$query = 'SELECT * FROM students';
$params = [];
$conditions = [];

if ($filter_dept) {
    $conditions[] = 'department = ?';
    $params[] = $filter_dept;
}
if ($filter_sem) {
    $conditions[] = 'semester = ?';
    $params[] = $filter_sem;
}

if ($conditions) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY roll_no';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | StudyShare Admin</title>
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
                <a href="manage_students.php" class="nav-item active">
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
                <h1>Manage Students</h1>
                <p>Import and manage student accounts</p>
            </header>

            <div class="content-body">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <span class="icon">✅</span>
                        <p><?= $success ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($errors): ?>
                    <div class="alert alert-error">
                        <span class="icon">⚠️</span>
                        <div style="flex: 1;">
                            <?php foreach ($errors as $error): ?>
                                <p style="margin-bottom: 0.25rem;">• <?= $error ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-card" style="margin-bottom: 3rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem;">Import Student Dataset</h2>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                        Upload a CSV file with student data. <br>
                        <strong>Format:</strong> roll_no, name, dob (DD-MM-YYYY), department, semester
                    </p>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="csv_file">Select CSV File *</label>
                            <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv" required style="padding: 0.6rem;">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon">📥</span> Import Students
                        </button>
                    </form>
                </div>

                <div class="section-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; gap: 2rem; flex-wrap: wrap;">
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 700;">All Students (<?= count($students) ?>)</h2>
                        <p style="color: var(--text-muted); font-size: 0.9rem;">View and manage registered student accounts</p>
                    </div>

                    <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; background: #f8fafc; padding: 1rem; border-radius: 1rem; border: 1px solid var(--border);">
                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Department</label>
                            <select name="department" class="form-control" style="padding: 0.5rem 1rem; min-width: 150px;" onchange="this.form.submit()">
                                <option value="">All Departments</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= sanitize($dept) ?>" <?= $filter_dept === $dept ? 'selected' : '' ?>><?= sanitize($dept) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            <label style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Semester</label>
                            <select name="semester" class="form-control" style="padding: 0.5rem 1rem; min-width: 150px;" onchange="this.form.submit()">
                                <option value="">All Semesters</option>
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?= $sem ?>" <?= intval($filter_sem) === intval($sem) ? 'selected' : '' ?>>Semester <?= $sem ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <?php if ($filter_dept || $filter_sem): ?>
                            <a href="manage_students.php" class="btn btn-danger" style="padding: 0.6rem 1rem; margin-top: auto;">
                                <span class="icon">🔄</span> Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Semester</th>
                                <th>DOB</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($students): ?>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td style="font-weight: 600;"><?= sanitize($student['roll_no']) ?></td>
                                        <td><?= sanitize($student['name']) ?></td>
                                        <td><span class="badge badge-info"><?= sanitize($student['department']) ?></span></td>
                                        <td>Sem <?= $student['semester'] ?></td>
                                        <td><?= date('d-m-Y', strtotime($student['dob'])) ?></td>
                                        <td>
                                            <a href="delete_student.php?roll_no=<?= urlencode($student['roll_no']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this student? This action cannot be undone.')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                                        <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                                        <p>No students found. Import some to get started!</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
