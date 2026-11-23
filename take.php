<?php
  session_start();
  if(!(array_key_exists('teacher_id',$_SESSION) && isset($_SESSION['teacher_id']))) {
    session_destroy();
    header('Location: index.php');
    exit;
  }
  include_once 'php/defines.php';
  include_once 'php/node_class.php';

  $teacher_id = $_SESSION['teacher_id'];
  $classes = $_SESSION['classes'];
  $name = $_SESSION['name'];
  
  if(!isset($_GET['cN']) || empty($_GET['cN'])) {
    die('<div style="text-align:center; padding:50px;"><h2>Invalid Request</h2><a href="teacher.php">Back to Dashboard</a></div>');
  }
  $class_id = intval($_GET['cN']);
  
  if(!in_array($class_id, $classes)) {
    die('<div style="text-align:center; padding:50px;"><h2>Class Not Found</h2><a href="teacher.php">Back to Dashboard</a></div>');
  }

  $classNode = new Node;
  $node = $classNode->retrieveObjecti($class_id, $teacher_id) or die("No such record");
  $records = $node->getRecords();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Take Attendance - <?php echo htmlspecialchars($node->getCode().' ('.$node->getSection().')'); ?></title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script>
    var numberOfDays = <?php echo $node->getDays(); ?>;
    var class_id = <?php echo $class_id; ?>;
    var teacher_id = <?php echo $teacher_id; ?>;
  </script>
  <script src="js/take.js"></script>
</head>
<body>

  <!-- Navigation Bar -->
  <nav class="navbar navbar-custom navbar-fixed-top">
    <div class="container-fluid app-container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-nav" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar" style="background:white;"></span>
          <span class="icon-bar" style="background:white;"></span>
          <span class="icon-bar" style="background:white;"></span>
        </button>
        <a class="navbar-brand" href="teacher.php">
          ONLINE ATTENDANCE <span class="badge-tracker">FACULTY</span>
        </a>
      </div>
      <div id="app-nav" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
          <li class="active"><a href="teacher.php">Dashboard</a></li>
          <li><a href="profile.php">Profile</a></li>  
          <li><a href="class.php">Class Management</a></li>
          <li><a href="statistics.php">Statistics</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li class="logout-btn"><a href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="main-wrapper">
    <div class="app-container">

      <!-- Header Hero -->
      <div class="welcome-hero">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
          <div>
            <span class="class-code-badge" style="background:rgba(255,255,255,0.15); color:white; margin-bottom:8px; display:inline-block;">
              <?php echo htmlspecialchars($node->getCode()); ?>
            </span>
            <h1 style="margin:5px 0;">Attendance Session: Section <?php echo htmlspecialchars($node->getSection()); ?></h1>
            <p>Academic Year: <strong><?php echo htmlspecialchars($node->getYear()); ?></strong> | Classes Conducted So Far: <strong><?php echo $node->getDays(); ?></strong></p>
          </div>
          <div>
            <button id="submit" class="btn btn-success btn-lg">
              &#10003; Save Attendance
            </button>
          </div>
        </div>
      </div>

      <!-- Toolbar -->
      <div class="roster-toolbar">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
          <button id="btn-mark-all-p" class="btn btn-sm btn-success">
            &#10003; Mark All Present
          </button>
          <button id="btn-mark-all-a" class="btn btn-sm btn-outline">
            &times; Mark All Absent
          </button>
          <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addStudentModal">
            + Add Student
          </button>
          <button class="btn btn-sm btn-outline" data-toggle="modal" data-target="#instructionsModal">
            &#9432; Help
          </button>
        </div>

        <div style="min-width: 260px;">
          <input type="text" id="roster-search" class="form-control" placeholder="Search by name or USN...">
        </div>
      </div>

      <!-- Student Records List -->
      <div class="custom-card">
        <div class="custom-card-header">
          <h2 class="custom-card-title">
            Student Roster (<?php echo count($records); ?> enrolled)
          </h2>
          <span style="font-size:0.85rem; color:var(--muted);">
            Click <strong>[A] Absent</strong> to toggle to <strong>[P] Present</strong>
          </span>
        </div>

        <div id="studentRecords" class="roster-list">
          <?php if(empty($records)): ?>
            <div class="text-center" style="padding: 40px; color:var(--muted);">
              <h4>No students in this class yet.</h4>
              <p>Click the <strong>+ Add Student</strong> button above to enroll students.</p>
            </div>
          <?php else: ?>
            <?php foreach($records as $roll => $data): 
              $sName = isset($data['name']) && !empty($data['name']) ? $data['name'] : '(No Name Set)';
              $initials = '';
              if (!empty($data['name'])) {
                  $parts = explode(' ', trim($data['name']));
                  foreach($parts as $p) { if(!empty($p)) $initials .= strtoupper($p[0]); }
                  $initials = substr($initials, 0, 2);
              } else {
                  $initials = substr(str_replace(['/', '-'], '', $roll), 0, 2);
              }
            ?>
              <div class="student-row">
                <div class="student-identity">
                  <div class="student-avatar"><?php echo htmlspecialchars($initials); ?></div>
                  <div class="student-details">
                    <div class="student-name-box">
                      <span class="student-name" style="<?php echo (empty($data['name'])) ? 'color:var(--muted); font-weight:normal; font-style:italic;' : ''; ?>">
                        <?php echo htmlspecialchars($sName); ?>
                      </span>
                      <button class="edit-name-btn" title="Edit Student Name">&#9998; Edit Name</button>
                    </div>
                    <div style="margin-top:2px;">
                      USN: <span class="student-usn-pill"><?php echo htmlspecialchars($roll); ?></span>
                    </div>
                  </div>
                </div>

                <div class="student-actions">
                  <div class="presence-count-badge" title="Total sessions attended so far">
                    Attended: <strong class="presence-val"><?php echo (int)$data['present']; ?></strong>
                  </div>

                  <button class="btn btn-toggle-presence is-absent" title="Toggle Present / Absent">
                    &times; Absent
                  </button>

                  <button class="btn btn-outline btn-sm delete-roll" title="Delete Student from this class" style="color:var(--danger); border-color:#fca5a5;">
                    &times;
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

  <!-- Edit Name Modal -->
  <div class="modal fade" id="editNameModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Edit Student Name</h4>
        </div>
        <div class="modal-body">
          <input type="hidden" id="edit-name-roll">
          <div class="form-group">
            <label>Student Full Name</label>
            <input type="text" class="form-control" id="edit-name-input" placeholder="e.g. John Doe" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
          <button type="button" id="save-student-name-btn" class="btn btn-primary">Save Name</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Student Modal -->
  <div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <form id="add-student-form">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Add Student to Class</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Roll Number / USN</label>
              <input type="text" class="form-control" id="new-student-roll" placeholder="e.g. 1RV21CS001 or 101/BC/25" required>
            </div>
            <div class="form-group">
              <label>Student Name</label>
              <input type="text" class="form-control" id="new-student-name" placeholder="e.g. Adarsh" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
            <button type="submit" id="btn-submit-add-student" class="btn btn-primary">Add Student</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Instructions Modal -->
  <div class="modal fade" id="instructionsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Taking Attendance Instructions</h4>
        </div>
        <div class="modal-body">
          <ol style="line-height:2; padding-left:20px;">
            <li>All students start marked as <strong>Absent</strong> for this new session.</li>
            <li>Click the <strong>[A] Absent</strong> button next to a student to mark them <strong>[P] Present</strong>.</li>
            <li>Use <strong>Mark All Present</strong> if almost everyone is present, then toggle the few absentees.</li>
            <li>Click <strong>&#9998; Edit Name</strong> to assign or change a student's full name.</li>
            <li>Click <strong>+ Add Student</strong> to add an individual student with their USN &amp; Name to this class.</li>
            <li>When done, click <strong>Save Attendance</strong> at the top right to record the session.</li>
          </ol>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Got it</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Student Confirmation Modal -->
  <div class="modal fade delete-warning" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title text-danger">Remove Student?</h4>
        </div>
        <div class="modal-body text-center">
          <p>Are you sure you want to remove student <br><strong class="warning-roll" style="color:var(--dark);"></strong> from this class?</p>
          <p style="font-size:0.8rem; color:var(--muted);">This will delete their attendance records for this class.</p>
        </div>
        <div class="modal-footer" style="display:flex; justify-content:center; gap:10px;">
          <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger delete-rollnumber">Remove</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="app-footer">
    <div class="app-container">
      <p>&copy; <?php echo date('Y'); ?> Online Attendance Tracker. Built for Students &amp; Faculty.</p>
    </div>
  </footer>

</body>
</html>
