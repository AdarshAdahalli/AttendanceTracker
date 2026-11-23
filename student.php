<?php
session_start();
include_once 'php/defines.php';
include_once 'php/node_class.php';

$isStudentLoggedIn = isset($_SESSION['student_usn']) && !empty($_SESSION['student_usn']);
$studentUsn = $isStudentLoggedIn ? $_SESSION['student_usn'] : '';
$studentName = $isStudentLoggedIn ? (isset($_SESSION['student_name']) ? $_SESSION['student_name'] : $studentUsn) : '';

$studentClasses = array();
$totalConductedAll = 0;
$totalAttendedAll = 0;

if ($isStudentLoggedIn) {
    $con = connectTo();
    $objQuery = $con->query("SELECT uid, teacher_uid, code, year, section, object FROM objects");
    if ($objQuery && $objQuery->num_rows > 0) {
        while ($row = $objQuery->fetch_assoc()) {
            $node = @unserialize($row['object']);
            if ($node instanceof Node) {
                $records = $node->getRecords();
                foreach ($records as $rRoll => $rData) {
                    if (strtoupper($rRoll) === strtoupper($studentUsn)) {
                        $presents = isset($rData['present']) ? (int)$rData['present'] : 0;
                        $days = $node->getDays();
                        $pct = ($days > 0) ? round(($presents / $days) * 100, 1) : 0;
                        $timeline = isset($rData['timeline']) ? $rData['timeline'] : array();
                        
                        // Update student display name if node has it and session was fallback
                        if (!empty($rData['name']) && $studentName === $studentUsn) {
                            $studentName = $rData['name'];
                            $_SESSION['student_name'] = $studentName;
                        }

                        $studentClasses[] = array(
                            'class_id' => $row['uid'],
                            'code'     => $node->getCode(),
                            'section'  => $node->getSection(),
                            'year'     => $node->getYear(),
                            'semester' => $node->getSemester(),
                            'teacher'  => $node->getTeacherName(),
                            'days'     => $days,
                            'present'  => $presents,
                            'percent'  => $pct,
                            'timeline' => $timeline
                        );

                        $totalConductedAll += $days;
                        $totalAttendedAll += $presents;
                        break;
                    }
                }
            }
        }
    }
    $con->close();
}

$overallPercent = ($totalConductedAll > 0) ? round(($totalAttendedAll / $totalConductedAll) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Portal - Online Attendance Tracker</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/highcharts.js"></script>
  <script src="js/highcharts-exporting.js"></script>
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
        <a class="navbar-brand" href="index.php">
          ONLINE ATTENDANCE <span class="badge-tracker">STUDENT PORTAL</span>
        </a>
      </div>
      <div id="app-nav" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
          <?php if ($isStudentLoggedIn): ?>
            <li class="active"><a href="student.php">My Attendance</a></li>
            <li><a href="contact.php">Help / Contact</a></li>
            <li class="logout-btn"><a href="php/student_logout.php">Logout</a></li>
          <?php else: ?>
            <li><a href="index.php">Faculty Portal</a></li>
            <li><a href="contact.php">Contact</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="main-wrapper">
    <div class="app-container">

      <?php if ($isStudentLoggedIn): ?>
        <!-- Logged in Student Dashboard -->
        
        <div class="welcome-hero">
          <h1>Welcome, <?php echo htmlspecialchars($studentName); ?></h1>
          <p>USN: <span class="student-usn-pill" style="color:#a5b4fc; font-size:1.1rem;"><?php echo htmlspecialchars($studentUsn); ?></span> | Real-time Academic Attendance Dashboard</p>
        </div>

        <!-- Aggregate KPI Stats -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">Enrolled Courses</div>
            <div class="stat-value"><?php echo count($studentClasses); ?></div>
            <div class="stat-sub">Active enrolled subjects</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Overall Attendance</div>
            <div class="stat-value" style="color: <?php echo ($overallPercent >= 75) ? '#10b981' : (($overallPercent >= 65) ? '#f59e0b' : '#ef4444'); ?>;">
              <?php echo $overallPercent; ?>%
            </div>
            <div class="stat-sub">Across all courses</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Classes Attended</div>
            <div class="stat-value"><?php echo $totalAttendedAll; ?> <span style="font-size:1rem; color:var(--muted); font-weight:500;">/ <?php echo $totalConductedAll; ?></span></div>
            <div class="stat-sub">Total sessions held</div>
          </div>
          <div class="stat-card">
            <div class="stat-label">Eligibility Status</div>
            <div style="margin-top: 10px;">
              <?php if ($overallPercent >= 75): ?>
                <span class="status-badge status-good">Good Standing &ge; 75%</span>
              <?php elseif ($overallPercent >= 65): ?>
                <span class="status-badge status-warning">Warning: 65% - 74%</span>
              <?php else: ?>
                <span class="status-badge status-danger">Shortage: &lt; 65%</span>
              <?php endif; ?>
            </div>
            <div class="stat-sub">75% minimum required for exams</div>
          </div>
        </div>

        <!-- Enrolled Courses List -->
        <div class="custom-card">
          <div class="custom-card-header">
            <h2 class="custom-card-title">Enrolled Courses &amp; Attendance Breakdown</h2>
          </div>

          <?php if (empty($studentClasses)): ?>
            <div class="alert alert-info text-center" style="padding: 30px; border-radius: var(--radius-md);">
              <h4>No enrolled courses found for USN: <strong><?php echo htmlspecialchars($studentUsn); ?></strong></h4>
              <p>Your faculty may not have added your USN to their class records yet. Please verify your USN or check with your teacher.</p>
            </div>
          <?php else: ?>
            <div class="row">
              <?php foreach ($studentClasses as $idx => $cls): 
                $pct = $cls['percent'];
                $barColor = ($pct >= 75) ? '#10b981' : (($pct >= 65) ? '#f59e0b' : '#ef4444');
                $targetCalc = '';
                if ($cls['days'] > 0) {
                    if ($pct >= 75) {
                        $canBunk = floor(($cls['present'] - 0.75 * $cls['days']) / 0.75);
                        $targetCalc = ($canBunk > 0) 
                            ? "You can afford to miss <strong>{$canBunk}</strong> upcoming class(es) while staying &ge; 75%."
                            : "Attendance is on track. Attend next classes to maintain safe margin.";
                    } else {
                        $needed = ceil((0.75 * $cls['days'] - $cls['present']) / 0.25);
                        $targetCalc = "<span style='color:#ef4444;'>Need to attend <strong>{$needed}</strong> consecutive class(es) to reach 75%.</span>";
                    }
                }
              ?>
                <div class="col-md-6 col-sm-12">
                  <div class="course-card">
                    <div class="course-card-header">
                      <div>
                        <span class="class-code-badge"><?php echo htmlspecialchars($cls['code']); ?></span>
                        <div style="font-weight:700; font-size:1.15rem; color:var(--dark); margin-top:8px;">
                          Section <?php echo htmlspecialchars($cls['section']); ?> | Semester <?php echo htmlspecialchars($cls['semester']); ?>
                        </div>
                        <div style="font-size:0.88rem; color:var(--muted); margin-top:2px;">
                          Faculty: <strong><?php echo htmlspecialchars($cls['teacher']); ?></strong> | Year: <?php echo htmlspecialchars($cls['year']); ?>
                        </div>
                      </div>
                      <div class="text-right">
                        <div style="font-size:1.6rem; font-weight:800; color:<?php echo $barColor; ?>;">
                          <?php echo $pct; ?>%
                        </div>
                        <div style="font-size:0.82rem; color:var(--muted);">
                          <?php echo $cls['present']; ?> / <?php echo $cls['days']; ?> Attended
                        </div>
                      </div>
                    </div>

                    <div class="progress-container">
                      <div class="progress-bar-bg">
                        <div class="progress-bar-fill" style="width: <?php echo min(100, $pct); ?>%; background: <?php echo $barColor; ?>;"></div>
                      </div>
                    </div>

                    <div style="font-size:0.85rem; color:var(--slate); margin-bottom:16px;">
                      <?php echo $targetCalc; ?>
                    </div>

                    <div class="text-right">
                      <button class="btn btn-outline btn-sm view-timeline-btn" 
                              data-code="<?php echo htmlspecialchars($cls['code']); ?>"
                              data-teacher="<?php echo htmlspecialchars($cls['teacher']); ?>"
                              data-timeline='<?php echo json_encode($cls['timeline']); ?>'>
                        View Attendance History
                      </button>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <!-- Student Login View -->

        <div class="row">
          <div class="col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="custom-card" style="margin-top: 20px;">
              <div class="text-center" style="margin-bottom: 25px;">
                <div style="width:64px; height:64px; border-radius:50%; background:var(--primary-light); color:var(--primary); font-size:2rem; display:inline-flex; align-items:center; justify-content:center; margin-bottom:12px;">
                  &#127891;
                </div>
                <h2 style="font-weight:800; color:var(--dark); margin:0 0 8px 0;">Student Portal Login</h2>
                <p style="color:var(--muted); font-size:0.95rem;">Enter your USN and password to access your real-time attendance report.</p>
              </div>

              <div id="login-alert" class="alert alert-danger" style="display:none; border-radius:var(--radius-sm);"></div>

              <form id="student-login-form">
                <div class="form-group">
                  <label for="student-usn">University Seat Number (USN) / Roll No.</label>
                  <input type="text" class="form-control input-lg" id="student-usn" name="usn" placeholder="e.g. 1RV21CS001 or 101/BC/25" required autofocus>
                </div>
                <div class="form-group">
                  <label for="student-pass">Password</label>
                  <input type="password" class="form-control input-lg" id="student-pass" name="password" placeholder="Enter password" required>
                  <span class="help-block">&#128161; <strong>Tip:</strong> Default password is your USN.</span>
                </div>
                <button type="submit" id="btn-student-login" class="btn btn-primary btn-lg btn-block" style="margin-top:24px;">
                  Sign In to Student Portal
                </button>
              </form>

              <hr style="margin: 25px 0; border-color: var(--border-color);">

              <div class="text-center">
                <p style="color:var(--muted); font-size:0.9rem; margin-bottom:10px;">Are you a faculty member?</p>
                <a href="index.php" class="btn btn-outline btn-sm">Faculty Portal Login / Sign Up</a>
              </div>
            </div>
          </div>
        </div>

      <?php endif; ?>

    </div>
  </div>

  <!-- Detailed Timeline Modal -->
  <div class="modal fade" id="timelineModal" tabindex="-1" role="dialog" aria-labelledby="timelineModalLabel">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
          <h4 class="modal-title" id="timelineModalLabel">Attendance History</h4>
        </div>
        <div class="modal-body">
          <div id="timelineChart" style="height: 300px; margin-bottom: 25px;"></div>
          <h4 style="font-weight:700; color:var(--dark); margin-bottom:15px;">Date-wise Attendance Record</h4>
          <div id="timelineList" class="table-responsive"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" data-dismiss="modal">Close</button>
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

  <script>
  $(document).ready(function() {
    // Student Login AJAX
    $('#student-login-form').on('submit', function(e) {
      e.preventDefault();
      var usn = $('#student-usn').val().trim();
      var pass = $('#student-pass').val();
      var alertBox = $('#login-alert');
      var btn = $('#btn-student-login');

      if (!usn || !pass) {
        alertBox.text('Please enter both USN and password.').slideDown();
        return;
      }

      btn.prop('disabled', true).text('Signing in...');
      alertBox.slideUp();

      $.ajax({
        url: 'php/process_student_login.php',
        type: 'POST',
        data: { usn: usn, password: pass },
        dataType: 'json',
        success: function(resp) {
          btn.prop('disabled', false).text('Sign In to Student Portal');
          if (resp.status === 'success') {
            window.location.reload();
          } else {
            alertBox.text(resp.message || 'Login failed.').slideDown();
          }
        },
        error: function() {
          btn.prop('disabled', false).text('Sign In to Student Portal');
          alertBox.text('Server connection error. Please try again.').slideDown();
        }
      });
    });

    // View Detailed Timeline Modal
    $('.view-timeline-btn').on('click', function() {
      var code = $(this).data('code');
      var teacher = $(this).data('teacher');
      var timeline = $(this).data('timeline');

      $('#timelineModalLabel').text(code + ' Attendance History (Faculty: ' + teacher + ')');

      var dates = [];
      var seriesData = [];
      var rowsHtml = '<table class="table table-bordered table-striped"><thead><tr><th>#</th><th>Date &amp; Time</th><th>Attendance Status</th></tr></thead><tbody>';

      var count = 1;
      var hasData = false;

      $.each(timeline, function(timestamp, isPresent) {
        hasData = true;
        var dateObj = new Date(parseInt(timestamp) * 1000);
        var dateStr = dateObj.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) + ' ' + dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        dates.push(dateObj.toLocaleDateString());
        seriesData.push(isPresent ? 1 : 0);

        var statusBadge = isPresent 
          ? '<span class="status-badge status-good">Present (P)</span>' 
          : '<span class="status-badge status-danger">Absent (A)</span>';

        rowsHtml += '<tr><td>' + count + '</td><td>' + dateStr + '</td><td>' + statusBadge + '</td></tr>';
        count++;
      });

      rowsHtml += '</tbody></table>';

      if (!hasData) {
        $('#timelineList').html('<div class="alert alert-info">No individual session records found yet for this class.</div>');
        $('#timelineChart').hide();
      } else {
        $('#timelineList').html(rowsHtml);
        $('#timelineChart').show();

        // Render Highcharts
        $('#timelineChart').highcharts({
          chart: { type: 'column' },
          title: { text: 'Class-by-Class Attendance Timeline' },
          xAxis: { categories: dates, title: { text: 'Class Sessions' } },
          yAxis: {
            min: 0,
            max: 1,
            tickInterval: 1,
            title: { text: 'Attendance' },
            labels: {
              formatter: function() { return this.value === 1 ? 'Present' : 'Absent'; }
            }
          },
          legend: { enabled: false },
          series: [{
            name: 'Status',
            data: seriesData,
            color: '#4f46e5'
          }]
        });
      }

      $('#timelineModal').modal('show');
    });
  });
  </script>
</body>
</html>
