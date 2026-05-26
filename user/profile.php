<?php
require_once __DIR__ . '/../common/auth_user.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$student = getCurrentStudent();
$pdo = db();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['profile_image']['name'])) {
    $file = $_FILES['profile_image'];
    
    if (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/jpg'])) {
        $errors[] = 'Only JPG and PNG images are allowed.';
    } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB
        $errors[] = 'Image size must be less than 5MB.';
    } else {
        // Create directory if it doesn't exist
        if (!file_exists(PROFILE_DIR)) {
            mkdir(PROFILE_DIR, 0755, true);
        }
        
        // Delete old image if exists
        if (!empty($student['profile_image']) && file_exists(PROFILE_DIR . $student['profile_image'])) {
            unlink(PROFILE_DIR . $student['profile_image']);
        }
        
        // Save new image
        $filename = uniqid() . '_' . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], PROFILE_DIR . $filename)) {
            $stmt = $pdo->prepare('UPDATE students SET profile_image = ? WHERE roll_no = ?');
            if ($stmt->execute([$filename, $student['roll_no']])) {
                $success = 'Profile picture updated successfully!';
                $_SESSION['student_profile_image'] = $filename;
                $student['profile_image'] = $filename;
            } else {
                $errors[] = 'Failed to save image to database.';
                @unlink(PROFILE_DIR . $filename);
            }
        } else {
            $errors[] = 'Failed to upload image.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
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
                <a href="notes.php" class="nav-item">
                    <span class="icon">📚</span> Browse Notes
                </a>
                <a href="profile.php" class="nav-item active">
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
                    <h1>My Profile</h1>
                    <p>View and manage your account</p>
                </div>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <p><?= $success ?></p>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p>• <?= $error ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <section class="profile-container">
                <div class="profile-picture-section">
                    <div class="profile-picture">
                        <?php 
                        if (!empty($student['profile_image']) && file_exists(PROFILE_DIR . $student['profile_image'])): ?>
                            <img src="<?= getProfileImage($student['profile_image']) ?>" alt="Profile Picture">
                        <?php else: ?>
                            <div class="default-avatar">👤</div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="upload-picture-form">
                        <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/jpg" required>
                        <label for="profile_image" class="upload-label">Choose Picture</label>
                        <button type="submit" class="btn-primary">Upload</button>
                    </form>
                    <p class="help-text">JPG or PNG, max 5MB</p>
                </div>

                <div class="profile-details-section">
                    <h2>Student Information</h2>
                    <div class="details-grid">
                        <div class="detail-item">
                            <label>Full Name</label>
                            <p><?= sanitize($student['name']) ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Roll Number</label>
                            <p><?= sanitize($student['roll_no']) ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Date of Birth</label>
                            <p><?= date('d-m-Y', strtotime($student['dob'])) ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Department</label>
                            <p><?= sanitize($student['department']) ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Semester</label>
                            <p><?= $student['semester'] ?></p>
                        </div>
                        <div class="detail-item">
                            <label>Member Since</label>
                            <p><?= date('M d, Y', strtotime($student['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
</body>
</html>
