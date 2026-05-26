<?php
require_once __DIR__ . '/../common/functions.php';

if (isStudentLoggedIn()) {
    redirect('../user/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal | StudyShare</title>
    <style>
        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%);
            color: #111827;
        }
        .page-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .landing-card {
            width: min(100%, 1080px);
            background: rgba(255,255,255,0.96);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.15);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 0;
        }
        .hero-panel {
            padding: 3rem 3rem 2.5rem;
        }
        .hero-panel h1 {
            font-size: clamp(2.5rem, 3vw, 4rem);
            margin-bottom: 1rem;
            line-height: 1.05;
        }
        .hero-panel p {
            color: #4b5563;
            font-size: 1.05rem;
            max-width: 38rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }
        .hero-panel .cta-group {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1.4rem;
            border-radius: 999px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .btn-primary {
            background: #4f46e5;
            color: white;
            box-shadow: 0 18px 30px rgba(79, 70, 229, 0.18);
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #111827;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .feature-grid {
            display: grid;
            gap: 1rem;
            margin-top: 2.5rem;
        }
        .feature-card {
            padding: 1.25rem 1.4rem;
            border-radius: 18px;
            background: #f8fafc;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.04);
        }
        .feature-card span {
            font-size: 1.5rem;
            line-height: 1;
        }
        .feature-card strong {
            display: block;
            margin-bottom: 0.35rem;
        }
        .illustration-panel {
            background: linear-gradient(180deg, #eef2ff 0%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .illustration-panel .panel-content {
            text-align: center;
        }
        .illustration-panel h3 {
            margin-bottom: 1rem;
            font-size: 1.4rem;
        }
        .illustration-panel p {
            color: #4b5563;
            line-height: 1.8;
        }
        @media (max-width: 900px) {
            .landing-card {
                grid-template-columns: 1fr;
            }
            .hero-panel {
                padding: 2rem;
            }
            .illustration-panel {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-shell">
        <article class="landing-card">
            <section class="hero-panel">
                <p style="color:#4f46e5;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;font-size:0.85rem;">Student Experience</p>
                <h1>Join your course, launch smarter study sessions.</h1>
                <p>StudyShare brings your syllabus, notes, and lecture resources into one polished student workspace. Access only your department subjects, download study files, and keep pace with semester learning.</p>
                <div class="cta-group">
                    <a href="../user/login.php" class="btn btn-primary">Student Login</a>
                    <a href="../lecture/login.php" class="btn btn-secondary">Lecturer Portal</a>
                </div>
                <div class="feature-grid">
                    <div class="feature-card">
                        <span>✅</span>
                        <div><strong>Course-focused notes</strong> Personalized subject access for your semester and language path.</div>
                    </div>
                    <div class="feature-card">
                        <span>✅</span>
                        <div><strong>Secure roll number login</strong> Enter your roll number and DOB to sign in.</div>
                    </div>
                    <div class="feature-card">
                        <span>✅</span>
                        <div><strong>Smart revision flow</strong> Quickly jump to available notes and downloads.</div>
                    </div>
                </div>
            </section>

            <aside class="illustration-panel">
                <div class="panel-content">
                    <h3>Already have access?</h3>
                    <p>Sign in to your dedicated student portal and stay organized with department-aligned resources.</p>
                </div>
            </aside>
        </article>
    </div>
</body>
</html>
