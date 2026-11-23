<?php
session_start();
unset($_SESSION['student_usn']);
unset($_SESSION['student_name']);
header('Location: ../index.php');
exit;
?>
