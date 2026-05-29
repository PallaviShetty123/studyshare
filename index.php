<?php
require_once __DIR__ . '/common/functions.php';

// Redirect if already logged in
if (isStudentLoggedIn()) {
    redirect('user/dashboard.php');
}
if (isAdminLoggedIn()) {
    redirect('admin/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyShare - Home</title>
    <link rel="stylesheet" href="<?=BASE_URL?>/assets/css/index.css">
</head>
<body>
    <header class="site-header">
        <div class="brand">
            <div class="logo">StudyShare</div>
        </div>
        <nav class="site-nav">
            <a href="#">Home</a>
            <a href="user/login.php">Student</a>
            <a href="lecture/login.php">Lecturer</a>
        </nav>
        <a href="user/register.html" class="button">Get Started</a>
    </header>

    <main class="hero-header">
        <div class="hero-content">
            <span class="hero-eyebrow">Premium study platform</span>
            <h1>Share and Learn Smarter</h1>
            <p>Upload notes, access study materials, and collaborate with students using a modern, polished landing page experience.</p>
            <div class="hero-actions">
                <a href="user/login.php" class="button">Login</a>
                <a href="#notes" class="button-secondary">Browse Notes</a>
            </div>
        </div>
    </main>

    <section id="notes" class="feature-grid">
        <article class="feature-card">
            <div class="feature-icon">📄</div>
            <h3>Notes</h3>
            <p>Browse and download subject notes from peers and lecturers.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon">📚</div>
            <h3>Books</h3>
            <p>Find curated textbooks and reference materials for every course.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon">🧠</div>
            <h3>Previous Papers</h3>
            <p>Study with past exam papers and practice tests for better preparation.</p>
        </article>
        <article class="feature-card">
            <div class="feature-icon">📝</div>
            <h3>Assignments</h3>
            <p>Access shared assignments, project guides, and study resources instantly.</p>
        </article>
    </section>
</body>
</html>
