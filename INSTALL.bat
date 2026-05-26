@echo off
REM StudyShare Portal - Quick Installation Script (Windows)
REM Run this after downloading the files

setlocal enabledelayedexpansion

echo.
echo 🚀 StudyShare Portal - Installation Setup
echo ==========================================
echo.

:: Create uploads directory
echo 📁 Creating upload directories...
if not exist "uploads" mkdir uploads
if not exist "uploads\notes" mkdir uploads\notes
echo ✅ Upload directories created

echo.
echo 📚 Database Setup Required:
echo ==========================
echo.
echo You have TWO options to import the database:
echo.
echo Option 1 - Using phpMyAdmin:
echo   1. Open http://localhost/phpmyadmin in your browser
echo   2. Click the "Import" tab
echo   3. Select file: database/studyshare.sql
echo   4. Click "Go" button
echo.
echo Option 2 - Using MySQL Command Line:
echo   1. Go to Start Menu and search for "Command Prompt"
echo   2. Navigate to: cd "c:\xampp\mysql\bin"
echo   3. Run: mysql -u root studyshare ^< c:\xampp\htdocs\STUDYSHARE-\database\studyshare.sql
echo.

echo ⚙️  Check PHP Settings (php.ini):
echo ==================================
echo The following settings must be set:
echo   upload_max_filesize = 50M
echo   post_max_size = 50M
echo.
echo Location: C:\xampp\php\php.ini
echo If changed, restart Apache from XAMPP Control Panel
echo.

echo ✨ Setup Complete! Next Steps:
echo ==============================
echo.
echo 1. IMPORT THE DATABASE (see above)
echo.
echo 2. Open in browser:
echo    Quick Access: http://localhost/STUDYSHARE-/index-test.html
echo.
echo 3. Or access directly:
echo    Student Portal:  http://localhost/STUDYSHARE-/user/login.php
echo    Lecturer Portal: http://localhost/STUDYSHARE-/lecture/login.php
echo.
echo 4. Test with these credentials:
echo    Student:
echo      Roll Number: BCA25060
echo      Date of Birth: 25-10-2007
echo.
echo    Lecturer:
echo      Username: lecturer
echo      Password: lecturer123
echo.

echo 📖 Documentation:
echo ==================
echo   - Read IMPLEMENTATION_SUMMARY.md for complete details
echo   - Read TESTING_CHECKLIST.md for verification steps
echo   - Read SETUP_GUIDE.md for usage instructions
echo.

echo 🎉 Everything is ready! Start with the database import.
echo.

pause
