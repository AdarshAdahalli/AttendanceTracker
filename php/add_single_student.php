<?php
session_start();
require_once('defines.php');
require_once('node_class.php');

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_id'])) {
    die(json_encode(array('status' => 'error', 'message' => 'Unauthorized')));
}

$class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
$roll = isset($_POST['roll']) ? strtoupper(trim($_POST['roll'])) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (empty($roll)) {
    die(json_encode(array('status' => 'error', 'message' => 'Please enter a valid Roll Number / USN')));
}

$teacher_id = $_SESSION['teacher_id'];
$n = new Node();
$node = $n->retrieveObjecti($class_id, $teacher_id);
if (!$node) {
    die(json_encode(array('status' => 'error', 'message' => 'Class not found')));
}

$node->addStudent($roll, $name);
$node->saveNodei($class_id);

die(json_encode(array(
    'status' => 'success',
    'roll' => $roll,
    'name' => $name,
    'present' => 0
)));
?>
