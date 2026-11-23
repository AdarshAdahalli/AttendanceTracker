<?php
  session_start();
  if(!(array_key_exists('teacher_id',$_SESSION) && isset($_SESSION['teacher_id']))) {
    session_destroy();
    header('Location: index.php');
    exit;
  }
  include_once 'php/defines.php';
  include_once 'php/node_class.php';

  $name = $_SESSION['name'];
  $classes = isset($_SESSION['classes']) && is_array($_SESSION['classes']) ? $_SESSION['classes'] : array();
  $teacher_id = $_SESSION['teacher_id'];

  $totalSessions = 0;
  $totalStudents = 0;
  $classList = array();

  if(!empty($classes)) {
    $n = new Node;
    foreach($classes as $class_id) {
      $node = $n->retrieveObjecti($class_id, $teacher_id);
      if($node) {
        $recs = $node->getRecords();
        $days = $node->getDays();
        $totalSessions += $days;
        $totalStudents += count($recs);
        $classList[] = array(
          'id'       => $class_id,
          'code'     => $node->getCode(),
          'section'  => $node->getSection(),
          'year'     => $node->getYear(),
          'semester' => $node->getSemester(),
          'days'     => $days,
          'students' => count($recs)
        );
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Dashboard - Online Attendance Tracker</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/teacher.js"></script>
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

      <!-- Hero Banner -->
      <div class="welcome-hero">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
          <div>
            <h1>Welcome, Prof. <?php echo htmlspecialchars($name); ?></h1>
            <p>Faculty Attendance Portal &bull; Take attendance, manage student rosters, and view real-time statistics.</p>
          </div>
          <div>
            <button class="btn btn-primary btn-lg" data-toggle="modal" data-target=".bs-example-modal-lg">
              + Add New Class
            </button>
          </div>
        </div>
      </div>

      <!-- KPI Summary -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">Active Classes</div>
          <div class="stat-value"><?php echo count($classList); ?></div>
          <div class="stat-sub">Managed courses</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Sessions Conducted</div>
          <div class="stat-value"><?php echo $totalSessions; ?></div>
          <div class="stat-sub">Across all classes</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Total Enrolled Students</div>
          <div class="stat-value"><?php echo $totalStudents; ?></div>
          <div class="stat-sub">Students under your instruction</div>
        </div>
      </div>

      <!-- Class Cards Grid -->
      <div class="custom-card">
        <div class="custom-card-header">
          <h2 class="custom-card-title">My Classes &amp; Attendance Sheets</h2>
          <span style="font-size:0.85rem; color:var(--muted);">Select a class to record student attendance</span>
        </div>

        <div class="class-grid">
          <?php foreach($classList as $c): ?>
            <div class="class-card">
              <div class="class-card-top">
                <span class="class-code-badge"><?php echo htmlspecialchars($c['code']); ?></span>
                <button class="btn btn-outline btn-sm delete-class-warning" 
                        data-toggle="modal" data-target=".delete-warning"
                        title="Delete this class"
                        style="color:var(--danger); border-color:#fca5a5;">
                  &times;
                </button>
              </div>

              <div class="class-meta">
                <span class="meta-pill">Section: <strong class="section"><?php echo htmlspecialchars($c['section']); ?></strong></span>
                <span class="meta-pill">Year: <strong class="year"><?php echo htmlspecialchars($c['year']); ?></strong></span>
                <span class="meta-pill">Semester: <?php echo htmlspecialchars($c['semester']); ?></span>
                <span class="code" style="display:none;"><?php echo htmlspecialchars($c['code']); ?></span>
              </div>

              <div class="class-sessions">
                <div>Sessions Conducted: <strong><?php echo $c['days']; ?></strong></div>
                <div>Enrolled Students: <strong><?php echo $c['students']; ?></strong></div>
              </div>

              <div style="display:flex; gap:10px; margin-top:10px;">
                <a href="take.php?cN=<?php echo $c['id']; ?>" class="btn btn-success btn-sm btn-block">
                  Take Attendance
                </a>
                <a href="class.php#_<?php echo $c['id']; ?>_" class="btn btn-outline btn-sm" title="Manage Roster & Names">
                  Roster
                </a>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- Add Class Card -->
          <div class="add-class-card" data-toggle="modal" data-target=".bs-example-modal-lg">
            <div class="add-class-icon">+</div>
            <div style="font-weight:700; font-size:1.1rem; color:var(--dark);">Add New Class</div>
            <div style="font-size:0.82rem; color:var(--muted); margin-top:4px;">Configure course &amp; student roll range</div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- Add Class Modal -->
  <div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Create New Class</h4>
        </div>
        <div class="modal-body">
          <div id="add_class_form">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Subject Code</label>
                  <input class="form-control" name="code" placeholder="e.g. BCA-101 or CS101" required>
                  <span class="help-block">Alphanumeric course code</span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Academic Year</label>
                  <select class="form-control" name="year">
                    <?php foreach(range(date('Y'), 2018) as $r) echo '<option>'.$r.'</option>'; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Section</label>
                  <select class="form-control" name="section">
                    <option value="-1">Choose Section</option>
                    <?php foreach(range(1, 6) as $r) echo '<option value="'.$r.'">Section '.$r.'</option>'; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Semester</label>
                  <select class="form-control" name="semester">
                    <option value="-1">Choose Semester</option>
                    <?php foreach(range(1, 8) as $r) echo '<option value="'.$r.'">Semester '.$r.'</option>'; ?>
                  </select>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Starting Roll / USN</label>
                  <input class="form-control" name="start" placeholder="e.g. 101/BC/25 or 101" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Ending Roll / USN</label>
                  <input class="form-control" name="end" placeholder="e.g. 150/BC/25 or 150">
                  <span class="help-block">Leave blank or same as start for single student</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="add">Create Class</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Class Modal -->
  <div class="modal fade delete-warning" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title text-danger">Delete Class?</h4>
        </div>
        <div class="modal-body text-center">
          <p>Are you sure you want to delete <br><strong class="warning-class" style="color:var(--dark);"></strong>?</p>
          <p style="font-size:0.8rem; color:var(--muted);">All attendance history for this class will be permanently removed.</p>
        </div>
        <div class="modal-footer" style="display:flex; justify-content:center; gap:10px;">
          <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger delete-class-code">Delete</button>
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
