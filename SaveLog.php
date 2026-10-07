<?php
// SaveLog.php - receives logbookForm and saves it to visitor, visitor_log, location
require 'CapstoneSample_db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: logbookForm.html');
    exit;
}

$allowedTypes     = ['Student', 'Faculty', 'Parent', 'Guest'];
$allowedBuildings = ['Admin Building', 'FDT', 'Anatomy Building', 'Arch', 'PGT Building',
                     'GPL Building', 'LRC Building', 'Purple Owl Complex', 'Centennial Gymnasium'];

$fullName    = trim($_POST['fullName'] ?? '');
$contactNo   = trim($_POST['contactNo'] ?? '');
$birthdate   = trim($_POST['birthdate'] ?? '');
$visitorType = $_POST['visitorType'] ?? '';
$building    = $_POST['building'] ?? '';

if ($fullName === '' || $contactNo === '' || $visitorType === '' || $building === '') {
    die('Please fill in all required fields. <a href="logbookForm.html">Go back</a>');
}
if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $contactNo)) {
    die('Invalid contact number. <a href="logbookForm.html">Go back</a>');
}
if (!in_array($visitorType, $allowedTypes, true) || !in_array($building, $allowedBuildings, true)) {
    die('Invalid visitor type or building. <a href="logbookForm.html">Go back</a>');
}

// birthdate is optional
$birthValue = null;
if ($birthdate !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $birthdate);
    if (!$d || $d->format('Y-m-d') !== $birthdate || $d > new DateTime('today')) {
        die('Invalid birthdate. <a href="logbookForm.html">Go back</a>');
    }
    $birthValue = $birthdate;
}

// Split "Juan Dela Cruz" into first / middle / last
$parts     = preg_split('/\s+/', $fullName);
$firstname = array_shift($parts);
$lastname  = count($parts) ? array_pop($parts) : '';
$middle    = count($parts) ? implode(' ', $parts) : null;

try {
    // TEMP: uses the first active guard account until you build a staff login
    $guardId = $pdo->query("SELECT user_id FROM `user` WHERE role = 'guard' AND status = 'active' ORDER BY user_id LIMIT 1")->fetchColumn();
    if (!$guardId) {
        die('No guard account found. Run capstonesample_db.sql first (it creates the sample users).');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO visitor (firstname, middlename, lastname, contact_number, birthdate, visitor_type)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$firstname, $middle, $lastname, $contactNo, $birthValue, $visitorType]);
    $visitorId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO visitor_log (visitor_id, user_id) VALUES (?, ?)");
    $stmt->execute([$visitorId, $guardId]);
    $logId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO location (log_id, building) VALUES (?, ?)");
    $stmt->execute([$logId, $building]);

    $pdo->commit();
    header('Location: visitorDashboard.php?log_id=' . $logId);
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die('Save failed: ' . e($e->getMessage()));
}