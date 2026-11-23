<?php
DEFINE('DB_USER','root');
DEFINE('DB_PASS','');
DEFINE('DB_HOST','localhost');
DEFINE('DB_DB','attendance');
DEFINE('EMAIL',1);
DEFINE('PHONE',2);
DEFINE('ROLL',3);
DEFINE('CODE',4);
DEFINE('NAME',5);
DEFINE('NUMBER',6);

function connectTo() {
  $con = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_DB);
  return $con;   
}

function sqlReady($input) {
  $con = connectTo();
  $string = mysqli_real_escape_string($con,$input);
  $con->close();
  return $string; 
}

function hashPass($pass,$rounds = 9) {
  if (function_exists('password_hash')) {
    return password_hash($pass, PASSWORD_BCRYPT);
  }
  $salt = "";
  $i = -1;
  $saltChars = array_merge(range(0,9),range('a','z'),range('A','Z'));
  while(++$i < 22)
    $salt .= $saltChars[array_rand($saltChars)];
  return crypt($pass, sprintf('$2y$%02d$', $rounds) . $salt);
}

function verifyPass($input,$pass) {
  if (function_exists('password_verify')) {
    if (@password_verify($input, $pass)) return true;
  }
  return (@crypt($input,$pass) == $pass || $input === $pass);
}

function respond($as,$what) {
  die(json_encode(array($as=>$what)));
}

function updateSession($email) {
  $con = connectTo();
  $exists = $con->query("select * from `teacher` where email = '$email'");
  if ($exists && $exists->num_rows) {
    $exists = $exists->fetch_assoc();
    $_SESSION['name'] = $exists['name'];
    $_SESSION['email'] = $exists['email'];
    $_SESSION['phone'] = $exists['phone'];
    $_SESSION['teacher_id'] = $exists['uid'];
    $_SESSION['classes'] = 0;
    $classes = $con->query('select uid from `objects` where teacher_uid = '.$_SESSION['teacher_id']);
    if($classes && $classes->num_rows) {
      $cls = array();
      while($a = $classes->fetch_array()) {
        $cls[] = $a[0];
      } 
      $_SESSION['classes'] = $cls;
    }
  }
  $con->close();
}

function verify($type,$input) {
  $reEmail = '/^([\S]+)@([\S]+)\.([\S]+)$/';
  $rePhone = '/^[0-9]{10}$/';
  $reCode  = '/^[a-zA-Z0-9\-_]{2,20}$/';
  $reRoll  = '/^[a-zA-Z0-9\/\-_]{2,25}$/';
  $reName  = '/^[a-zA-Z0-9\s\'\.\-_]{2,50}$/';
  $reNum   = '/^[0-9]+$/';
  $m = array();
  switch($type) {
    case EMAIL : 
      preg_match($reEmail,$input,$m);
    break;
    case PHONE : 
      preg_match($rePhone,$input,$m);
    break;
    case CODE : 
      preg_match($reCode,$input,$m);
    break;
    case ROLL : 
      preg_match($reRoll,$input,$m);
    break;
    case NAME : 
      preg_match($reName,$input,$m);
    break;
    case NUMBER : 
      preg_match($reNum,$input,$m);
    break;
  }
  return count($m) == 0 ? false : true;
}
?>
