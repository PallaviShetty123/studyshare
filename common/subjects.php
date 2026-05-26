<?php
require_once __DIR__ . '/db.php';

/**
 * Get all subjects for a student (compulsory + language-specific)
 */
function getStudentSubjects($roll_no) {
    $pdo = db();
    
    // Get compulsory subjects
    $stmt = $pdo->prepare('SELECT * FROM subjects WHERE is_compulsory = 1 AND language_specific IS NULL ORDER BY subject_name');
    $stmt->execute();
    $compulsory_subjects = $stmt->fetchAll();
    
    // Get student's language assignment
    $stmt = $pdo->prepare('SELECT language FROM language_assignments WHERE roll_no = ?');
    $stmt->execute([$roll_no]);
    $language_assignment = $stmt->fetch();
    
    $language = $language_assignment ? $language_assignment['language'] : 'KANNADA';
    
    // Get language-specific subject
    $stmt = $pdo->prepare('SELECT * FROM subjects WHERE language_specific = ?');
    $stmt->execute([$language]);
    $language_subject = $stmt->fetch();
    
    // Combine all subjects
    $all_subjects = $compulsory_subjects;
    if ($language_subject) {
        $all_subjects[] = $language_subject;
    }
    
    return $all_subjects;
}

/**
 * Get notes for a specific subject
 */
function getSubjectNotes($subject_id) {
    $pdo = db();
    $stmt = $pdo->prepare('
        SELECT n.*, l.name as lecturer_name, s.subject_name 
        FROM notes n 
        LEFT JOIN lecturers l ON n.lecturer_id = l.id 
        LEFT JOIN subjects s ON n.subject_id = s.id 
        WHERE n.subject_id = ? 
        ORDER BY n.upload_date DESC
    ');
    $stmt->execute([$subject_id]);
    return $stmt->fetchAll();
}

/**
 * Get student's language
 */
function getStudentLanguage($roll_no) {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT language FROM language_assignments WHERE roll_no = ?');
    $stmt->execute([$roll_no]);
    $result = $stmt->fetch();
    return $result ? $result['language'] : 'KANNADA';
}

/**
 * Check if a roll number has Hindi language
 */
function isHindiStudent($roll_no) {
    return getStudentLanguage($roll_no) === 'HINDI';
}

/**
 * Insert students from the dataset into database
 */
function insertStudentsData() {
    $pdo = db();
    
    $students = [
        ['BCA25060', 'VIVEK', '2007-10-25', 'BCA', 2],
        ['BCA25008', 'BHARATH', '2007-05-29', 'BCA', 2],
        ['BCA25050', 'SINDHURA', '2007-05-02', 'BCA', 2],
        ['BCA25032', 'PRANAVI', '2007-08-21', 'BCA', 2],
        ['BCA25031', 'PRAMEELA', '2007-06-15', 'BCA', 2],
        ['BCA25019', 'MEGHANA', '2006-10-06', 'BCA', 2],
        ['BCA35044', 'SHIVANI', '2007-12-10', 'BCA', 2],
        ['BCA25027', 'POOJA', '2007-10-09', 'BCA', 2],
        ['BCA25013', 'DEEPIKA', '2007-09-25', 'BCA', 2],
        ['BCA25043', 'SANJANA', '2007-01-23', 'BCA', 2],
        ['BCA25041', 'SANIHA', '2007-12-27', 'BCA', 2],
        ['BCA25005', 'ANKITHA', '2007-08-13', 'BCA', 2],
        ['BCA25004', 'ANISHA', '2007-09-11', 'BCA', 2],
        ['BCA25042', 'SANJANA', '2007-06-09', 'BCA', 2],
        ['BCA25018', 'MANJUSHREE', '2007-11-02', 'BCA', 2],
        ['BCA25046', 'SHREENIDHI', '2007-11-13', 'BCA', 2],
        ['BCA25054', 'TASVI', '2007-08-27', 'BCA', 2],
        ['BCA25026', 'PALLAVI', '2008-02-05', 'BCA', 2],
        ['BCA25010', 'CHANDINI', '2008-02-12', 'BCA', 2],
        ['BCA25055', 'VARHSA', '2008-04-09', 'BCA', 2],
        ['BCA25059', 'VINAYA', '2007-12-29', 'BCA', 2],
        ['BCA25049', 'SINCHANA', '2007-08-25', 'BCA', 2],
        ['BCA25037', 'SAHANA', '2007-11-24', 'BCA', 2],
        ['BCA25025', 'NISHMITHA', '2007-01-09', 'BCA', 2],
        ['BCA25024', 'NISHMITHA', '2007-11-07', 'BCA', 2],
        ['BCA25023', 'NISHMITHA', '2007-08-13', 'BCA', 2],
        ['BCA25015', 'DIVIJA', '2008-01-04', 'BCA', 2],
        ['BCA25009', 'BHOOMIKA', '2006-10-07', 'BCA', 2],
        ['BCA25011', 'CHAYA', '2007-09-21', 'BCA', 2],
        ['BCA25039', 'SAMIKSHA', '2008-05-26', 'BCA', 2],
        ['BCA25028', 'PRAJNA', '2007-06-11', 'BCA', 2],
        ['BCA25007', 'ASHRITHA', '2008-03-31', 'BCA', 2],
        ['BCA25034', 'RASHMITHA', '2007-05-18', 'BCA', 2],
        ['BCA25048', 'SINCHANA', '2007-09-01', 'BCA', 2],
        ['BCA25022', 'NIRATH', '2007-08-06', 'BCA', 2],
        ['BCA25001', 'ADITYA', '2007-11-24', 'BCA', 2],
        ['BCA25003', 'AKSHATH', '2007-09-24', 'BCA', 2],
        ['BCA25012', 'CHIRAYU', '2008-04-11', 'BCA', 2],
        ['BCA25014', 'DISHAN', '2007-09-17', 'BCA', 2],
        ['BCA25033', 'PREETHAM', '2007-10-25', 'BCA', 2],
        ['BCA25038', 'SAMARTH', '2007-07-07', 'BCA', 2],
        ['BCA25017', 'MANIKANTA', '2007-02-11', 'BCA', 2],
        ['BCA25053', 'SUSHANTH', '2008-04-04', 'BCA', 2],
        ['BCA25045', 'SHRAVANTH', '2008-01-05', 'BCA', 2],
        ['BCA25057', 'VARUN', '2008-03-14', 'BCA', 2],
        ['BCA25006', 'ASHMITH', '2007-03-01', 'BCA', 2],
        ['BCA25036', 'SACHIN', '2007-03-28', 'BCA', 2],
        ['BCA25002', 'AKASH', '2007-10-26', 'BCA', 2],
        ['BCA25040', 'SANDEEP', '2007-07-18', 'BCA', 2],
        ['BCA25030', 'PRAJWAL', '2007-09-04', 'BCA', 2],
        ['BCA25052', 'SUBHIKSH', '2007-06-07', 'BCA', 2],
        ['BCA25029', 'PRAJWAL', '2008-01-02', 'BCA', 2],
        ['BCA25051', 'SRAJAN', '2007-03-04', 'BCA', 2],
    ];
    
    // Check if students already exist
    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM students');
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        foreach ($students as $student) {
            try {
                $stmt = $pdo->prepare('INSERT INTO students (roll_no, name, dob, department, semester) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$student[0], $student[1], $student[2], $student[3], $student[4]]);
            } catch (Exception $e) {
                // Skip if already exists
            }
        }
    }
}
?>
