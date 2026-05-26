#!/bin/bash
# StudyShare Portal - Quick Installation Script
# Run this after downloading the files

echo "🚀 StudyShare Portal - Installation Setup"
echo "=========================================="

# Check if XAMPP is running
echo ""
echo "📋 Checking requirements..."
if ! command -v mysql &> /dev/null; then
    echo "⚠️  MySQL not found. Please start XAMPP first!"
    exit 1
fi

echo "✅ MySQL found"

# Create uploads directory
echo ""
echo "📁 Creating upload directories..."
mkdir -p "uploads/notes"
chmod 755 "uploads/notes"
echo "✅ Upload directories created"

# Database import instructions
echo ""
echo "📚 Database Setup Required:"
echo "=========================="
echo ""
echo "Import the database using one of these methods:"
echo ""
echo "Method 1 - Using phpMyAdmin:"
echo "1. Go to http://localhost/phpmyadmin"
echo "2. Click 'Import' tab"
echo "3. Select /database/studyshare.sql"
echo "4. Click 'Go'"
echo ""
echo "Method 2 - Using MySQL Command Line:"
echo "mysql -u root studyshare < database/studyshare.sql"
echo ""

# Configuration check
echo ""
echo "⚙️  Required PHP Settings:"
echo "=========================="
echo "Check php.ini has:"
echo "  upload_max_filesize = 50M"
echo "  post_max_size = 50M"
echo ""

# Ready to test
echo ""
echo "✨ Setup Complete! Next Steps:"
echo "=============================="
echo ""
echo "1. Open in browser:"
echo "   http://localhost/STUDYSHARE-/index-test.html"
echo ""
echo "2. Or access directly:"
echo "   Student:  http://localhost/STUDYSHARE-/user/login.php"
echo "   Lecturer: http://localhost/STUDYSHARE-/lecture/login.php"
echo ""
echo "3. Test credentials:"
echo "   Student - Roll: BCA25060, DOB: 25-10-2007"
echo "   Lecturer - User: lecturer, Pass: lecturer123"
echo ""
echo "📖 Read IMPLEMENTATION_SUMMARY.md for complete details"
echo "📋 Read TESTING_CHECKLIST.md for verification steps"
echo ""
echo "🎉 Ready to go!"
