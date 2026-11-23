<?php
session_start();
require_once('defines.php');
require_once('node_class.php');

header('Content-Type: application/json');

$usn = isset($_POST['usn']) ? strtoupper(trim($_POST['usn'])) : '';
$pass = isset($_POST['password']) ? trim($_POST['password']) : '';

if (empty($usn)) {
    die(json_encode(array('status' => 'error', 'message' => 'Please enter your USN / Roll Number.')));
}

$con = connectTo();
$usnEsc = $con->real_escape_string($usn);

// 1. Check if student exists in students table
$res = $con->query("SELECT * FROM students WHERE UPPER(usn) = '$usnEsc'");
$studentRow = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;

// 2. Check if student exists in any class object in objects table
$foundInClasses = false;
$detectedName = '';
$objQuery = $con->query("SELECT uid, object FROM objects");
if ($objQuery && $objQuery->num_rows > 0) {
    while ($row = $objQuery->fetch_assoc()) {
        $node = @unserialize($row['object']);
        if ($node instanceof Node) {
            $records = $node->getRecords();
            foreach ($records as $rRoll => $rData) {
                if (strtoupper($rRoll) === $usn) {
                    $foundInClasses = true;
                    if (!empty($rData['name'])) {
                        $detectedName = $rData['name'];
                        break 2;
                    }
                }
            }
        }
    }
}

// If student doesn't exist in students table yet, but is in classes: auto-register with default pass = USN
if (!$studentRow && $foundInClasses) {
    $studentName = !empty($detectedName) ? $detectedName : $usn;
    $hashedPass = hashPass($usn);
    $con->query("INSERT INTO students (usn, name, password) VALUES ('$usnEsc', '".$con->real_escape_string($studentName)."', '$hashedPass')");
    $res = $con->query("SELECT * FROM students WHERE UPPER(usn) = '$usnEsc'");
    $studentRow = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
}

// Password verification:
// Allowed if:
// 1) Password entered is equal to USN (as requested: "login with their usn and password also usn")
// 2) Password matches hashed password in students table
$isAuth = false;
if (strtoupper($pass) === $usn || $pass === $usn) {
    $isAuth = true;
} else if ($studentRow && verifyPass($pass, $studentRow['password'])) {
    $isAuth = true;
}

if ($isAuth) {
    $displayName = $studentRow ? $studentRow['name'] : (!empty($detectedName) ? $detectedName : $usn);
    $_SESSION['student_usn'] = $usn;
    $_SESSION['student_name'] = $displayName;
    $con->close();
    die(json_encode(array(
        'status' => 'success',
        'name' => $displayName,
        'usn' => $usn,
        'redirect' => 'student.php'
    )));
} else {
    $con->close();
    if (!$foundInClasses && !$studentRow) {
        die(json_encode(array('status' => 'error', 'message' => 'USN not found in any class records. Please verify your USN.')));
    }
    die(json_encode(array('status' => 'error', 'message' => 'Invalid password. (Tip: Default password is your USN).')));
}
?>
