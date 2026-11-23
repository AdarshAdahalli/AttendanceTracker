<?php
session_start();
include_once 'defines.php';
include_once 'node_class.php';

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    die(json_encode(array("error" => "auth")));
}

$teacher_id = $_SESSION['teacher_id'];
$code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
$year = isset($_POST['year']) ? trim($_POST['year']) : '';
$semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';
$section = isset($_POST['section']) ? trim($_POST['section']) : '';
$startRoll = isset($_POST['start']) ? strtoupper(trim($_POST['start'])) : '';
$endRoll = isset($_POST['end']) ? strtoupper(trim($_POST['end'])) : '';
if (empty($endRoll)) {
    $endRoll = $startRoll;
}

$classes = (!isset($_SESSION['classes']) || $_SESSION['classes'] == 0) ? array() : $_SESSION['classes'];

if(verify(CODE,$code) === false) respond("error","code");
if(verify(ROLL,$startRoll) === false) respond("error","roll");
if(verify(ROLL,$endRoll) === false) respond("error","roll");
if(verify(NUMBER,$semester) === false) respond("error","semester");
if(verify(NUMBER,$section) === false) respond("error","section");
if(verify(NUMBER,$year) === false) respond("error","year");

$n = new Node($code, $teacher_id, $year, $semester, $section, $startRoll, $endRoll);
updateSession($_SESSION['email']);
$classes2 = $_SESSION['classes'];
$class_id = null;
if (is_array($classes2)) {
    foreach($classes2 as $c) {
        if(!in_array($c,$classes)) $class_id = $c;
    }
}
if(!isset($class_id) && is_array($classes2) && count($classes2) > 0) {
    $class_id = end($classes2);
}

if(!isset($class_id)) respond("error","exists");
echo json_encode(array("code"=>$code,"section"=>$section,"year"=>$year,"class_id"=>$class_id));
?>
