<?php
require_once __DIR__ . '/../common/db.php';

$pdo = db();

$logs = [
    ['admin', 'admin', 'upload', '101', 'Admin uploaded DBMS Unit 4 Notes', '-2 minutes'],
    ['admin', 'admin', 'edit', '101', 'Admin edited note details: Previous Title: DBMS Notes -> Updated Title: DBMS Unit 4 Notes', '-10 minutes'],
    ['admin', 'admin', 'delete', '102', 'Admin deleted multiple notes (Spam content)', '-1 hour'],
    ['BCA25060', 'student', 'register', 'BCA25060', 'Student registered successfully', '-1 day'],
    ['BCA25060', 'student', 'login', 'BCA25060', 'Student logged in from new IP', '-5 hours'],
    ['BCA25060', 'student', 'download', '101', 'Student downloaded DBMS Unit 4 Notes', '-10 minutes'],
    ['system', 'system', 'system', 'sys', 'System notification: Daily backup completed', '-1 day'],
    ['admin', 'admin', 'upload', '103', 'Failed upload attempt - invalid file format', '-3 hours'],
    ['BCA25008', 'student', 'login', 'BCA25008', 'Multiple failed login attempts detected', '-2 days'],
    ['admin', 'admin', 'delete', '104', 'Suspended account: BCA25008 for violating terms', '-2 days'],
];

$stmt = $pdo->prepare("INSERT INTO activity_log (user_id, user_type, action, target_id, details, created_at) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($logs as $log) {
    $date = date('Y-m-d H:i:s', strtotime($log[5]));
    $stmt->execute([$log[0], $log[1], $log[2], $log[3], $log[4], $date]);
}

echo "Seeded successfully!";
