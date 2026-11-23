<?php
  require_once('defines.php');

  class Node {
    /************* 
      PROPERTIES 
    **************/
    const rollNumberRegex = '/([0-9]+)(\/[a-zA-Z]+\/[0-9]{2})/';
    private $teacher;      // teacher_id
    private $subjectCode; 
    private $section;    
    private $year;       
    private $semester;    
    private $numberOfDays = 0; 
    private $records = array(); // array of key value paired array for each student
   
    /********** 
      METHODS 
    ***********/
    
    # CONSTRUCTORS
     
    function __construct() {  
      $a = func_get_args();
      $i = func_num_args();
      if (method_exists($this,$f='__construct'.$i)) {
        call_user_func_array(array($this,$f),$a); 
      }
    }

    function __construct7($code,$teacher_uid,$year,$semester,$section,$start,$end) {
      $this->setCode($code);
      $this->setTeacher($teacher_uid);
      $this->setYear($year);
      $this->setSemester($semester);
      $this->setSection($section);
      $this->setDays(0);
      $this->initRecords($start,$end);
      if($this->saveNode() === false) {
        echo false;
      }
    }

    # HELPER FUNCTIONS
    
    public function retrieveObject($code,$section,$year) {
      $con = connectTo();
      $code = sqlReady($code);
      $section = sqlReady($section);
      $year = sqlReady($year);
      
      if($con->connect_errno) {
      	return false;
      } else {
        $obj = $con->query('select object from objects where code = "'.$code.'" and section = "'.$section.'" and year = "'.$year.'"');
        if($con->errno) {
          $con->close();
          return false;
        } else {
            if($obj && $obj->num_rows == 1) {
              $serialized = $obj->fetch_assoc()['object'];
              $con->close();
              return @unserialize($serialized);							
            } else { 
              $con->close();
              return false;
            }
        }
      }
    }

    public function retrieveObjecti($class_id,$teacher_uid) {
      $con = connectTo();
      $class_id = intval($class_id);
      $teacher_uid = intval($teacher_uid);
      
      if($con->connect_errno) {
      	return false;
      } else {
        $obj = $con->query('select object from objects where uid = "'.$class_id.'" and teacher_uid = "'.$teacher_uid.'"');
        if($con->errno) {
          $con->close();
          return false;
        } else {
            if($obj && $obj->num_rows == 1) {
              $serialized = $obj->fetch_array()['object'];
              $con->close();
              return @unserialize($serialized);							
            } else { 
              $con->close();
              return false;
            }
        }
      }
    }

    public function initRecords($start,$end) { 
      $start = trim($start);
      $end = trim($end);
      if(empty($start)) return false;

      if(preg_match(self::rollNumberRegex, $start) && preg_match(self::rollNumberRegex, $end)) {
        $s = (int)preg_replace(self::rollNumberRegex,"$1",$start);
        $e = (int)preg_replace(self::rollNumberRegex,"$1",$end);
        $type = preg_replace(self::rollNumberRegex,"$2",$start);
        if ($s <= $e) {
          foreach(range($s,$e) as $d) {
            $roll = $d.$type;
            $this->records[$roll] = array('name' => '', 'present' => 0, 'timeline' => array()); 
          }
        } else {
          $this->records[$start] = array('name' => '', 'present' => 0, 'timeline' => array());
        }
      } else if (is_numeric($start) && is_numeric($end)) {
        $s = (int)$start;
        $e = (int)$end;
        if ($s <= $e) {
          foreach(range($s,$e) as $d) {
            $this->records[(string)$d] = array('name' => '', 'present' => 0, 'timeline' => array());
          }
        } else {
          $this->records[$start] = array('name' => '', 'present' => 0, 'timeline' => array());
        }
      } else {
        $this->records[$start] = array('name' => '', 'present' => 0, 'timeline' => array());
        if (!empty($end) && $end !== $start) {
          $this->records[$end] = array('name' => '', 'present' => 0, 'timeline' => array());
        }
      }
      return true;
    }

    public function deleteNode() {
      $con = connectTo();
      if($con->connect_errno) {
      	return false;
      } else {
        $teacher_uid = $this->getTeacherID();
        $code = $this->getCode();
        $section = $this->getSection();
        $year = $this->getYear();
        $obj = $con->query('delete from objects where teacher_uid = "'.$teacher_uid.'" and code = "'.$code.'" and section = "'.$section.'" and year = "'.$year.'"');
        $success = ($obj && $con->affected_rows) ? true : false;
        $con->close();
        return $success;
      }
    }

    public function saveNode() {
      $con = connectTo();
      if($con->connect_errno) {
      	return false;
      } else {
        $teacher_uid = $this->getTeacherID();
        $code = $this->getCode();
        $section = $this->getSection();
        $year = $this->getYear();
        $serialized = $con->real_escape_string(serialize($this));

        $obj = $con->query('select uid from objects where teacher_uid = "'.$teacher_uid.'" and code = "'.$code.'" and section = "'.$section.'" and year = "'.$year.'"');
        if($obj && $obj->num_rows) {
          $res = $con->query('update objects set object = "'.$serialized.'" where teacher_uid = "'.$teacher_uid.'" and code = "'.$code.'" and section = "'.$section.'" and year = "'.$year.'"');
        } else {
          $res = $con->query('insert into `objects`(`teacher_uid`, `code`, `year`, `section`, `object`) VALUES ("'.$teacher_uid.'","'.$code.'","'.$year.'","'.$section.'","'.$serialized.'")');
        }
        $err = $con->errno;
        $con->close();
        return ($err === 0);
      }
    }

    public function saveNodei($class_id) {
      $con = connectTo();
      if($con->connect_errno) {
      	return false;
      } else {
        $class_id = intval($class_id);
        $teacher_uid = $this->getTeacherID();
        $code = $this->getCode();
        $section = $this->getSection();
        $year = $this->getYear();
        $serialized = $con->real_escape_string(serialize($this));

        $selectedNode = $con->query('select uid from objects where uid = '.$class_id.' and teacher_uid = '.$teacher_uid);
        if($selectedNode && $selectedNode->num_rows) {
          $obj = $con->query('update objects set code = "'.$code.'", year = "'.$year.'", section = "'.$section.'", object = "'.$serialized.'" where teacher_uid = "'.$teacher_uid.'" and uid = "'.$class_id.'"');
          $con->close();
          return ($obj !== false);
        }
        $con->close();
        return false;
      }
    }

    public function isPresent($rollNumber,$newPresents) {
      if(isset($this->records[$rollNumber]))
        return ( $this->records[$rollNumber]['present'] < $newPresents )? 1 : 0;
      return false;
    }

    public function deleteRoll($rollNumber) {
      if(isset($this->records[$rollNumber])) {
        unset($this->records[$rollNumber]);
        return true;
      }
      return false;
    }

    public function setStudentName($rollNumber, $name) {
      $name = trim($name);
      if(isset($this->records[$rollNumber])) {
        $this->records[$rollNumber]['name'] = $name;
        // Sync to students table in database
        $con = connectTo();
        if(!$con->connect_errno) {
          $rollEsc = $con->real_escape_string($rollNumber);
          $nameEsc = $con->real_escape_string($name);
          $check = $con->query("SELECT uid FROM students WHERE usn = '$rollEsc'");
          if($check && $check->num_rows > 0) {
            $con->query("UPDATE students SET name = '$nameEsc' WHERE usn = '$rollEsc'");
          } else {
            $defaultPass = hashPass($rollNumber);
            $con->query("INSERT INTO students (usn, name, password) VALUES ('$rollEsc', '$nameEsc', '$defaultPass')");
          }
          $con->close();
        }
        return true;
      }
      return false;
    }

    public function getStudentName($rollNumber) {
      if(isset($this->records[$rollNumber]) && !empty($this->records[$rollNumber]['name'])) {
        return $this->records[$rollNumber]['name'];
      }
      $con = connectTo();
      if(!$con->connect_errno) {
        $rollEsc = $con->real_escape_string($rollNumber);
        $res = $con->query("SELECT name FROM students WHERE usn = '$rollEsc'");
        if($res && $row = $res->fetch_assoc()) {
          $con->close();
          return !empty($row['name']) ? $row['name'] : '';
        }
        $con->close();
      }
      return '';
    }

    public function addStudent($rollNumber, $name = '') {
      $rollNumber = trim($rollNumber);
      $name = trim($name);
      if(empty($rollNumber)) return false;

      if(!isset($this->records[$rollNumber])) {
        $this->records[$rollNumber] = array(
          'name' => $name,
          'present' => 0,
          'timeline' => array()
        );
      } else {
        if(!empty($name)) {
          $this->records[$rollNumber]['name'] = $name;
        }
      }
      if(!empty($name)) {
        $this->setStudentName($rollNumber, $name);
      }
      return true;
    }
    
    /**********
      GETTERS
    **********/
    
    public function getTeacherID() {
      return $this->teacher;
    }

    public function getTeacherName() {
      $con = connectTo();
      $s = $con->query("select name from teacher where uid = ".intval($this->getTeacherID()));
      if($s && $s->num_rows) {
        $name = $s->fetch_assoc()['name'];
      } else {
        $name = 'Faculty';
      }
      $con->close();
      return $name;
    }

    public function getCode() {
      return $this->subjectCode;
    }

    public function getYear() {
      return $this->year;
    }

    public function getSemester() {
      return $this->semester;
    }

    public function getSection() {
      return $this->section;
    }

    public function getDays() {
      return (int)$this->numberOfDays;
    }

    public function getPercent($rollNumber) {
      if(!isset($this->records[$rollNumber])) return false;
      $days = $this->getDays();
      if($days == 0) return 0;
      $present = isset($this->records[$rollNumber]['present']) ? $this->records[$rollNumber]['present'] : 0;
      return (100 * ($present / $days));
    }

    public function getTimeline($rollNumber) {
      return isset($this->records[$rollNumber]) ? $this->records[$rollNumber]['timeline'] : false;
    }

    public function getRecords() {
      // Ensure backwards compatibility with records created without 'name'
      foreach($this->records as $roll => $data) {
        if(!isset($this->records[$roll]['name'])) {
          $this->records[$roll]['name'] = $this->getStudentName($roll);
        }
      }
      return $this->records;
    }

    /**************
        SETTERS 
    **************/
    public function setTeacher($val) {
      $this->teacher = $val;
    }

    public function setCode($val) {
      $this->subjectCode = $val;
    }

    public function setYear($val) {
      $this->year = $val;
    }

    public function setSemester($val) {
      $this->semester = $val;
    }

    public function setSection($val) {
      $this->section = $val;
    }

    public function setDays($val) {
      $this->numberOfDays = (int)$val;
    }

    public function setPresence($rollNumber,$newPresents,$timestamp) {
      if(isset($this->records[$rollNumber])) {
        $this->records[$rollNumber]['timeline'][$timestamp] = $this->isPresent($rollNumber,$newPresents);
        $this->records[$rollNumber]['present'] = $newPresents;
        return true;
      }
      return false;
    }
  }
?>
