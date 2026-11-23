<?php
  include_once 'defines.php';
  include_once 'node_class.php';
  header('Content-Type: application/json');

  $roll = isset($_POST['roll']) ? trim($_POST['roll']) : die(json_encode(array("error"=>"MISSING_PARAM")));
  $section = isset($_POST['section']) ? trim($_POST['section']) : die(json_encode(array("error"=>"MISSING_PARAM")));
  $code = isset($_POST['code']) ? trim($_POST['code']) : die(json_encode(array("error"=>"MISSING_PARAM")));
  $year = isset($_POST['year']) ? trim($_POST['year']) : die(json_encode(array("error"=>"MISSING_PARAM")));

  $o = new Node;
  if($o = $o->retrieveObject($code,$section,$year)) {
    $timeline = $o->getTimeline($roll);
    if($timeline !== false) {
      $name = $o->getStudentName($roll);
      $percent = $o->getPercent($roll);
      die(json_encode(array(
        "timeline" => $timeline,
        "percent"  => $percent !== false ? $percent : 0,
        "teacher"  => $o->getTeacherName(),
        "count"    => $o->getDays(),
        "name"     => !empty($name) ? $name : $roll
      )));
    }
    die(json_encode(array("error"=>"NOT_IN_RECORDS")));
  }
  die(json_encode(array("error"=>"NO_RECORD")));	
?>
