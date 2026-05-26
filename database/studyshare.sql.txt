-- StudyShare Database Schema
-- Create Database
CREATE DATABASE IF NOT EXISTS studyshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studyshare;

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Students Table
CREATE TABLE IF NOT EXISTS students (
    roll_no VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    department VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes Table
CREATE TABLE IF NOT EXISTS notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    department VARCHAR(50) NOT NULL,
    semester INT NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    likes INT DEFAULT 0,
    uploaded_by INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Likes Table
CREATE TABLE IF NOT EXISTS likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    roll_no VARCHAR(50) NOT NULL,
    note_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (roll_no, note_id),
    FOREIGN KEY (roll_no) REFERENCES students(roll_no) ON DELETE CASCADE,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Downloads History Table
CREATE TABLE IF NOT EXISTS downloads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    roll_no VARCHAR(50) NOT NULL,
    note_id INT NOT NULL,
    download_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (roll_no) REFERENCES students(roll_no) ON DELETE CASCADE,
    FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lecturer/Faculty Table
CREATE TABLE IF NOT EXISTS lecturers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    department VARCHAR(50) NOT NULL,
    subject VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    subject_code VARCHAR(20) UNIQUE,
    description TEXT,
    is_compulsory TINYINT DEFAULT 1,
    language_specific VARCHAR(20),
    color_code VARCHAR(7) DEFAULT '#3498db',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Language Assignment Table
CREATE TABLE IF NOT EXISTS language_assignments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    roll_no VARCHAR(50) NOT NULL,
    language VARCHAR(50) NOT NULL,
    FOREIGN KEY (roll_no) REFERENCES students(roll_no) ON DELETE CASCADE,
    UNIQUE KEY unique_student_language (roll_no, language)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update Notes Table to add lecturer reference
ALTER TABLE notes ADD COLUMN lecturer_id INT AFTER uploaded_by;
ALTER TABLE notes ADD COLUMN subject_id INT AFTER subject;
ALTER TABLE notes ADD FOREIGN KEY (lecturer_id) REFERENCES lecturers(id) ON DELETE SET NULL;
ALTER TABLE notes ADD FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL;

-- Insert Default Admin
INSERT INTO admin (username, password) VALUES ('admin', MD5('admin123'));

-- Insert Default Lecturer
INSERT INTO lecturers (username, password, name, email, department, subject) VALUES 
('lecturer', MD5('lecturer123'), 'Prof. Teacher', 'lecturer@studyshare.com', 'BCA', NULL);

-- Insert Lecturer Dataset with Hashed Passwords
INSERT INTO lecturers (username, password, name, email, department, subject) VALUES
('LEC001', MD5('LEC001'), 'Vijayashree', 'lec001@studyshare.com', 'BCA', 'Object Oriented Programming using Java'),
('LEC002', MD5('LEC002'), 'Shreekanth', 'lec002@studyshare.com', 'BCA', 'Data Structure'),
('LEC003', MD5('LEC003'), 'Swathi', 'lec003@studyshare.com', 'ENGLISH', 'English'),
('LEC004', MD5('LEC004'), 'Praveen Mogaveer', 'lec004@studyshare.com', 'BBA', 'Environmental Studies'),
('LEC005', MD5('LEC005'), 'Nirmala', 'lec005@studyshare.com', 'BCA', 'Computational Mathematics'),
('LEC006', MD5('LEC006'), 'Megha', 'lec006@studyshare.com', 'BCA', 'Computing and Mathematics for AI'),
('LEC007', MD5('LEC007'), 'Dr chethan shetty', 'lec007@studyshare.com', 'KANNADA', 'Kannada'),
('LEC008', MD5('LEC008'), 'Deepa mam', 'lec008@studyshare.com', 'HINDI', 'Hindi')
ON DUPLICATE KEY UPDATE
    password = VALUES(password),
    name = VALUES(name),
    email = VALUES(email),
    department = VALUES(department),
    subject = VALUES(subject);

-- Insert Compulsory Subjects (same for all students)
INSERT INTO subjects (subject_name, subject_code, is_compulsory, language_specific, color_code) VALUES
('Object Oriented Programming using Java', 'OOP101', 1, NULL, '#FF6B6B'),
('English', 'ENG101', 1, NULL, '#4ECDC4'),
('Data Structure Theory', 'DST101', 1, NULL, '#45B7D1'),
('Computing and Mathematics for AI', 'CMAI101', 1, NULL, '#FFA07A'),
('Computational Mathematics', 'CMATH101', 1, NULL, '#98D8C8'),
('Environmental Studies', 'ENV101', 1, NULL, '#F7DC6F');

-- Insert Language Specific Subjects
INSERT INTO subjects (subject_name, subject_code, is_compulsory, language_specific, color_code) VALUES
('Hindi', 'HINDI101', 1, 'HINDI', '#DDA15E'),
('Kannada', 'KANNADA101', 1, 'KANNADA', '#BC6C25');

-- Assign Hindi to specified students
INSERT INTO language_assignments (roll_no, language) VALUES
('BCA25017', 'HINDI'),
('BCA25051', 'HINDI'),
('BCA25040', 'HINDI'),
('BCA25057', 'HINDI'),
('BCA25060', 'HINDI'),
('BCA25029', 'HINDI'),
('BCA25023', 'HINDI');

-- Add student dataset
INSERT INTO students (roll_no, name, dob, department, semester) VALUES
('BCA25060','VIVEK','2007-10-25','BCA',2),
('BCA25008','BHARATH','2007-05-29','BCA',2),
('BCA25050','SINDHURA','2007-05-02','BCA',2),
('BCA25032','PRANAVI','2007-08-21','BCA',2),
('BCA25031','PRAMEELA','2007-06-15','BCA',2),
('BCA25019','MEGHANA','2006-10-06','BCA',2),
('BCA35044','SHIVANI','2007-12-10','BCA',2),
('BCA25027','POOJA','2007-10-09','BCA',2),
('BCA25013','DEEPIKA','2007-09-25','BCA',2),
('BCA25043','SANJANA','2007-01-23','BCA',2),
('BCA25041','SANIHA','2007-12-27','BCA',2),
('BCA25005','ANKITHA','2007-08-13','BCA',2),
('BCA25004','ANISHA','2007-09-11','BCA',2),
('BCA25042','SANJANA','2007-06-09','BCA',2),
('BCA25018','MANJUSHREE','2007-11-02','BCA',2),
('BCA25046','SHREENIDHI','2007-11-13','BCA',2),
('BCA25054','TASVI','2007-08-27','BCA',2),
('BCA25026','PALLAVI','2008-02-05','BCA',2),
('BCA25010','CHANDINI','2008-02-12','BCA',2),
('BCA25055','VARHSA','2008-04-09','BCA',2),
('BCA25059','VINAYA','2007-12-29','BCA',2),
('BCA25049','SINCHANA','2007-08-25','BCA',2),
('BCA25037','SAHANA','2007-11-24','BCA',2),
('BCA25025','NISHMITHA','2007-01-09','BCA',2),
('BCA25024','NISHMITHA','2007-11-07','BCA',2),
('BCA25023','NISHMITHA','2007-08-13','BCA',2),
('BCA25015','DIVIJA','2008-01-04','BCA',2),
('BCA25009','BHOOMIKA','2006-10-07','BCA',2),
('BCA25011','CHAYA','2007-09-21','BCA',2),
('BCA25039','SAMIKSHA','2008-05-26','BCA',2),
('BCA25028','PRAJNA','2007-06-11','BCA',2),
('BCA25007','ASHRITHA','2008-03-31','BCA',2),
('BCA25034','RASHMITHA','2007-05-18','BCA',2),
('BCA25048','SINCHANA','2007-09-01','BCA',2),
('BCA25022','NIRATH','2007-08-06','BCA',2),
('BCA25001','ADITYA','2007-11-24','BCA',2),
('BCA25003','AKSHATH','2007-09-24','BCA',2),
('BCA25012','CHIRAYU','2008-04-11','BCA',2),
('BCA25014','DISHAN','2007-09-17','BCA',2),
('BCA25033','PREETHAM','2007-10-25','BCA',2),
('BCA25038','SAMARTH','2007-07-07','BCA',2),
('BCA25017','MANIKANTA','2007-02-11','BCA',2),
('BCA25053','SUSHANTH','2008-04-04','BCA',2),
('BCA25045','SHRAVANTH','2008-01-05','BCA',2),
('BCA25057','VARUN','2008-03-14','BCA',2),
('BCA25006','ASHMITH','2007-03-01','BCA',2),
('BCA25036','SACHIN','2007-03-28','BCA',2),
('BCA25002','AKASH','2007-10-26','BCA',2),
('BCA25040','SANDEEP','2007-07-18','BCA',2),
('BCA25030','PRAJWAL','2007-09-04','BCA',2),
('BCA25052','SUBHIKSH','2007-06-07','BCA',2),
('BCA25029','PRAJWAL','2008-01-02','BCA',2),
('BCA25051','SRAJAN','2007-03-04','BCA',2);

-- Indexes for better performance
CREATE INDEX idx_department_semester ON notes(department, semester);
CREATE INDEX idx_roll_no ON students(roll_no);
CREATE INDEX idx_note_likes ON likes(note_id);
