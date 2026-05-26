<?php
session_start();
require_once __DIR__ . '/../common/auth_lecturer.php';
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

$lecturer = getCurrentLecturer();
$pdo = db();
$errors = [];
$success = '';

$columnExists = false;
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM lecturers LIKE 'profile_image'");
    $columnExists = (bool) $stmt->fetch();
} catch (Exception $e) {
    $columnExists = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['profile_image']['name'])) {
    if (!$columnExists) {
        $errors[] = 'Profile image upload is not enabled. Please add the lecturers.profile_image field in your database.';
    } else {
        $file = $_FILES['profile_image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($file['type'], $allowedTypes)) {
            $errors[] = 'Only JPG and PNG images are allowed.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image size must be less than 5MB.';
        } else {
            if (!file_exists(PROFILE_DIR)) {
                mkdir(PROFILE_DIR, 0755, true);
            }

            if (!empty($lecturer['profile_image']) && file_exists(PROFILE_DIR . $lecturer['profile_image'])) {
                unlink(PROFILE_DIR . $lecturer['profile_image']);
            }

            $filename = uniqid() . '_' . basename($file['name']);
            if (move_uploaded_file($file['tmp_name'], PROFILE_DIR . $filename)) {
                $stmt = $pdo->prepare('UPDATE lecturers SET profile_image = ? WHERE id = ?');
                if ($stmt->execute([$filename, $lecturer['id']])) {
                    $success = 'Profile picture updated successfully!';
                    $lecturer['profile_image'] = $filename;
                } else {
                    $errors[] = 'Failed to save image to database.';
                    @unlink(PROFILE_DIR . $filename);
                }
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Profile | StudyShare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/lecturer.css">
    <style>
        .profile-card {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 3rem;
            background: white;
            padding: 3rem;
            border-radius: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .avatar-section {
            text-align: center;
        }

        .avatar-wrapper {
            width: 180px;
            height: 180px;
            margin: 0 auto 2rem;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
        }

        .avatar-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .info-section h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 2rem;
            color: var(--text-dark);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .info-item label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .info-item p {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        @media (max-width: 900px) {
            .profile-card {
                grid-template-columns: 1fr;
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="lecturer-layout">
        <aside class="lecturer-sidebar">
            <div class="sidebar-brand">
                <h2>StudyShare</h2>
                <p>Lecturer Portal</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="profile.php" class="nav-item active">
                    <span class="icon">👤</span> Profile
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="lecturer-info">
                    <p><?= sanitize($lecturer['name']) ?></p>
                    <small><?= sanitize($lecturer['department']) ?></small>
                </div>
                <a href="logout.php" class="logout-btn">
                    <span class="icon">🚪</span> Logout
                </a>
            </div>
        </aside>

        <main class="lecturer-main">
            <header class="lecturer-header">
                <h1>My Profile</h1>
                <p>Manage your personal information and profile settings.</p>
            </header>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="icon">✅</span> <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <span class="icon">⚠️</span>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p>• <?= sanitize($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="profile-card">
                <div class="avatar-section">
                    <div class="avatar-wrapper">
                        <?php if (!empty($lecturer['profile_image']) && file_exists(PROFILE_DIR . $lecturer['profile_image'])): ?>
                            <img src="<?= getProfileImage($lecturer['profile_image']) ?>" alt="Profile">
                        <?php else: ?>
                            👤
                        <?php endif; ?>
                    </div>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/jpg" required style="display: none;" onchange="this.form.submit()">
                            <label for="profile_image" class="btn btn-primary" style="width: auto; padding: 0.75rem 1.5rem; cursor: pointer;">
                                Update Picture
                            </label>
                        </div>
                        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 1rem;">JPG, PNG or JPEG. Max 5MB.</p>
                    </form>
                </div>

                <div class="info-section">
                    <h2>Lecturer Details</h2>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Full Name</label>
                            <p><?= sanitize($lecturer['name']) ?></p>
                        </div>
                        <div class="info-item">
                            <label>Email Address</label>
                            <p><?= sanitize($lecturer['email'] ?? 'Not provided') ?></p>
                        </div>
                        <div class="info-item">
                            <label>Department</label>
                            <p><?= sanitize($lecturer['department']) ?></p>
                        </div>
                        <div class="info-item">
                            <label>Subject</label>
                            <p><?= sanitize($lecturer['subject'] ?? 'Not assigned') ?></p>
                        </div>
                        <div class="info-item">
                            <label>Member Since</label>
                            <p><?= date('M d, Y', strtotime($lecturer['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
