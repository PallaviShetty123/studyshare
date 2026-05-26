# ✅ StudyShare Setup Checklist

## Pre-Flight Checks

- [ ] XAMPP is running (Apache + MySQL)
- [ ] PHP version is 7.4 or higher
- [ ] MySQL database exists: `studyshare`
- [ ] Can access `http://localhost/phpmyadmin`

---

## Database Setup

- [ ] Run updated `database/studyshare.sql` to create new tables:
  - [ ] `lecturers` table created
  - [ ] `subjects` table created with 8 subjects
  - [ ] `language_assignments` table created with 7 Hindi, 43 Kannada
  - [ ] `notes` table updated with subject_id and lecturer_id
  
### Verify in phpMyAdmin:
```sql
SELECT COUNT(*) FROM subjects;  -- Should return 8
SELECT COUNT(*) FROM language_assignments WHERE language='HINDI';  -- Should return 7
SELECT * FROM lecturers LIMIT 1;  -- Should show lecturer account
```

---

## File Structure Verification

### New Files Created ✨

- [ ] `/common/auth_lecturer.php` - Lecturer authentication
- [ ] `/common/subjects.php` - Subject & language functions
- [ ] `/lecture/login.php` - Lecturer login page
- [ ] `/lecture/logout.php` - Lecturer logout
- [ ] `/lecture/dashboard.php` - Lecturer upload interface
- [ ] `/lecture/delete-note.php` - Delete notes
- [ ] `/user/subject-notes.php` - Subject notes view
- [ ] `/assets/css/subjects.css` - Subject cards styling
- [ ] `/uploads/notes/` directory (auto-created on first upload)

### Updated Files 📝

- [ ] `/user/dashboard.php` - New beautiful subject cards UI
- [ ] `/assets/css/dashboard.css` - New layout styles
- [ ] `/database/studyshare.sql` - New schema

### Documentation 📖

- [ ] `/SETUP_GUIDE.md` - Comprehensive setup guide
- [ ] `/index-test.html` - Quick access portal
- [ ] `/TESTING_CHECKLIST.md` - This file

---

## Configure PHP Settings

Edit `php.ini`:

```ini
; Increase upload limits for PDF files
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

Restart Apache after changes.

---

## Permissions & Directories

- [ ] Create `/uploads/` directory (if doesn't exist)
- [ ] Create `/uploads/notes/` directory
- [ ] Set directory permissions: `chmod 755 uploads/notes/`
- [ ] Verify Apache user can write to these directories

### Windows (if needed):
```cmd
mkdir "c:\xampp\htdocs\STUDYSHARE-\uploads"
mkdir "c:\xampp\htdocs\STUDYSHARE-\uploads\notes"
```

---

## Test Student Logins

### Student #1 - Hindi
- [ ] Roll No: `BCA25060`
- [ ] DOB: `25-10-2007`  
- [ ] Expected: Should see 7 subjects (6 compulsory + Hindi)
- [ ] Language: Hindi ✅

### Student #2 - Kannada  
- [ ] Roll No: `BCA25001`
- [ ] DOB: `24-11-2007`
- [ ] Expected: Should see 7 subjects (6 compulsory + Kannada)
- [ ] Language: Kannada ✅

---

## Test Lecturer Functions

### Lecturer Account
- [ ] Username: `lecturer`
- [ ] Password: `lecturer123`
- [ ] Can access: `http://localhost/STUDYSHARE-/lecture/login.php`

### Upload a Note ✅
- [ ] Select any subject
- [ ] Enter title: "Test Note"
- [ ] Upload a PDF file (< 50MB)
- [ ] Verify: File appears in lecturer dashboard
- [ ] Verify: Note appears in student's subject page

### View in Student Portal ✅
- [ ] Login as student
- [ ] Navigate to related subject
- [ ] See uploaded note appears
- [ ] Download PDF works

### Delete a Note ✅
- [ ] From lecturer dashboard, click delete
- [ ] Confirm deletion
- [ ] Note disappears from both lecturer & student views

---

## Test Student Features

### Subject Dashboard
- [ ] [ ] Subject cards display with gradient colors
- [ ] [ ] Floating animation visible on cards
- [ ] [ ] Stats show correct numbers (subjects, notes, language)
- [ ] [ ] Grid is responsive on mobile

### Subject Page
- [ ] [ ] Click on subject card navigates to subject page
- [ ] [ ] Subject header shows color coded
- [ ] [ ] Lists all available notes
- [ ] [ ] Download button works
- [ ] [ ] Like button functionality works

### Language Assignment
- [ ] [ ] Hindi students (BCA25017, etc.) see Hindi subject
- [ ] [ ] Kannada students see Kannada subject
- [ ] [ ] Language selector shows correct assignment
- [ ] [ ] Cannot access restricted language subject

---

## Test UI/UX

### Design Elements
- [ ] Sidebar navigation visible and functional
- [ ] Subject cards use gradient backgrounds
- [ ] Color scheme consistent (purple/teal theme)
- [ ] Animations smooth and not jarring
- [ ] Text readable on all backgrounds

### Responsive Design  
- [ ] [ ] Desktop (1920px): Full layout looks good
- [ ] [ ] Tablet (768px): Single column, cards stack
- [ ] [ ] Mobile (375px): All elements accessible

### Accessibility
- [ ] Buttons are clickable (min 44x44px)
- [ ] Text contrast is sufficient
- [ ] Form labels are present
- [ ] Error messages clear and visible

---

## Troubleshooting

### Database Connection Error
```
Error: Could not connect to database
Solution: Check config.php, verify MySQL is running
```

### File Upload Limit Error
```
Error: File too large or format not allowed
Solution: Check php.ini upload_max_filesize, verify .pdf validation
```

### Subject Not Appearing
```
Error: Student doesn't see expected subjects
Solution: Verify language_assignments table, check getStudentSubjects()
```

### Lecturer Portal Blank
```
Error: Can't upload or view notes
Solution: Check /uploads/notes/ directory permissions
```

---

## Performance Check

- [ ] Page load time < 2 seconds
- [ ] Subject cards render smoothly
- [ ] No JavaScript errors in console
- [ ] Database queries optimized (see slow_query_log)

---

## Security Verification

- [ ] Passwords are MD5 hashed (already implemented)
- [ ] Session tokens used correctly
- [ ] File permissions prevent direct access
- [ ] Input sanitization on all forms
- [ ] SQL injection protection via prepared statements

---

## Final Deployment

Before going live:

- [ ] All test cases pass
- [ ] Documentation updated
- [ ] Backup database created
- [ ] Error logging configured
- [ ] Admin notified of new portals
- [ ] Users given access credentials

---

## Quick Links

- **Student Portal**: `http://localhost/STUDYSHARE-/user/login.php`
- **Lecturer Portal**: `http://localhost/STUDYSHARE-/lecture/login.php`
- **Test Access Hub**: `http://localhost/STUDYSHARE-/index-test.html`
- **Setup Guide**: `SETUP_GUIDE.md`

---

## Sign-Off

- [ ] Development Complete
- [ ] Testing Complete  
- [ ] Documentation Complete
- [ ] Ready for Production

**Completed by:** _________________  
**Date:** _________________  
**Notes:** _____________________________________

---

**Great job! 🎉 Your StudyShare portal is ready to use!**
