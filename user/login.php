<?php
require_once __DIR__ . '/../common/db.php';
require_once __DIR__ . '/../common/functions.php';

if (isStudentLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roll_no = sanitize($_POST['roll_no'] ?? '');
    $dob = trim($_POST['dob'] ?? '');

    if (empty($roll_no) || empty($dob)) {
        $errors[] = 'Please enter roll number and date of birth.';
    } else {
        $dob = str_replace(['/', '.'], '-', $dob);

        if (preg_match('/^(\d{2})-(\d{2})-(\d{4})$/', $dob, $matches)) {
            $dob = sprintf('%s-%s-%s', $matches[3], $matches[2], $matches[1]);
        } elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dob, $matches)) {
            $dob = $dob;
        } else {
            $errors[] = 'Date of birth must be in DD-MM-YYYY format (e.g. 29-05-2007).';
        }
    }

    if (empty($errors)) {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM students WHERE roll_no = ? AND dob = ?');
        $stmt->execute([$roll_no, $dob]);
        $student = $stmt->fetch();
        
        if (!$student) {
            $errors[] = 'Invalid roll number or date of birth.';
            logActivity($roll_no, 'student', 'login', $roll_no, 'Failed login attempt');
        } else {
            setStudentSession($student);
            logActivity($student['roll_no'], 'student', 'login', $student['roll_no'], 'Student logged in successfully');
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | StudyShare</title>
    <link rel="stylesheet" href="../assets/css/login.css?v=<?= time() ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="particles" id="particles"></div>

    <div class="login-card">
        <div class="logo-section">
            <div class="logo-icon">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 12C8 12 16 4 24 4C32 4 40 12 40 12V36C40 36 32 44 24 44C16 44 8 36 8 36V12Z" stroke="currentColor" stroke-width="2.5" fill="rgba(255,255,255,0.1)"/>
                    <path d="M16 18H32M16 26H28M16 34H24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
            <h1 class="logo-text">StudyShare</h1>
            <p class="subtitle">Student Portal Login</p>
        </div>

        <?php if ($errors): ?>
            <div class="alert-error">
                <svg width="20" height="20" class="error-icon" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <div class="error-messages">
                    <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm" autocomplete="on">
            <div class="input-group">
                <input type="text" id="roll_no" name="roll_no" required autocomplete="username" spellcheck="false" placeholder="e.g. 21CS001" value="<?= sanitize($_POST['roll_no'] ?? '') ?>">
                <label for="roll_no">Roll Number</label>
                <span class="input-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <path d="M22 6l-10 7L2 6"/>
                    </svg>
                </span>
            </div>

            <div class="input-group">
                <input type="text" id="dob" name="dob" required inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" autocomplete="bday" placeholder="DD-MM-YYYY" value="<?= sanitize($_POST['dob'] ?? '') ?>">
                <label for="dob">Date of Birth</label>
                <span class="input-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </span>
                <button type="button" class="date-picker-toggle" onclick="document.getElementById('dob').type='date'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </button>
            </div>

            <button type="submit" class="btn-login" id="loginBtn">
                <span class="btn-text">Login</span>
                <span class="btn-loader"></span>
                <svg class="btn-arrow" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </form>

        <div class="footer-link">
            <p>Are you an admin? <a href="../admin/login.php">Go to Admin Portal</a></p>
        </div>

        <a href="../index.php" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Home
        </a>
    </div>

    <script>
        (function() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 40;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.cssText = `
                    left: ${Math.random() * 100}%;
                    top: ${Math.random() * 100}%;
                    animation-duration: ${15 + Math.random() * 20}s;
                    animation-delay: -${Math.random() * 20}s;
                    width: ${3 + Math.random() * 5}px;
                    height: ${3 + Math.random() * 5}px;
                    opacity: ${0.1 + Math.random() * 0.4};
                `;
                particlesContainer.appendChild(particle);
            }

            document.querySelectorAll('.input-group input').forEach(input => {
                const group = input.closest('.input-group');
                input.addEventListener('focus', () => group.classList.add('focused'));
                input.addEventListener('blur', () => {
                    if (!input.value) group.classList.remove('focused');
                });
                if (input.value) group.classList.add('focused');
            });

            const form = document.getElementById('loginForm');
            const btn = document.getElementById('loginBtn');
            
            form.addEventListener('submit', function(e) {
                if (btn.classList.contains('loading')) {
                    e.preventDefault();
                    return;
                }
                btn.classList.add('loading');
            });

            const inputs = document.querySelectorAll('#roll_no, #dob');
            inputs.forEach(input => {
                input.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    this.closest('.input-group').classList.add('error');
                });
                input.addEventListener('input', function() {
                    this.closest('.input-group').classList.remove('error');
                });
            });
        })();
    </script>
</body>
</html>