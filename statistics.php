<?php
  session_start();
  if(!(array_key_exists('teacher_id',$_SESSION) && isset($_SESSION['teacher_id']))) {
    session_destroy();
    header('Location: index.php');
    exit;
  }
  include_once 'php/defines.php';
  include_once 'php/node_class.php';

  $classes = isset($_SESSION['classes']) && is_array($_SESSION['classes']) ? $_SESSION['classes'] : array();
  $teacher_id = $_SESSION['teacher_id'];
  $name = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Analytics - Online Attendance Tracker</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/highcharts.js"></script>
  <script src="js/statistics.js"></script>
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
          <li><a href="teacher.php">Dashboard</a></li>
          <li><a href="profile.php">Profile</a></li>
          <li><a href="class.php">Class Management</a></li>
          <li class="active"><a href="statistics.php">Statistics</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li class="logout-btn"><a href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="main-wrapper">
    <div class="app-container">

      <div class="welcome-hero">
        <h1>Attendance Statistics &amp; Analytics</h1>
        <p>Review class averages, detention risks, and attendance trends.</p>
      </div>

      <div class="custom-card">
        <?php
          $n = new Node;
          if(!empty($classes)) {
            $data = array();
            foreach($classes as $c) {
              $node = $n->retrieveObjecti($c, $teacher_id);
              if(!$node) continue;
              $key = $node->getCode().' (Sec '.$node->getSection().'), '.$node->getYear();
              $total_days = $node->getDays();
              $data[$key] = array("total"=>$total_days, "average"=>0, "detained"=>array());

              if($total_days > 0) {
                $detained = array();
                $sum = 0;
                $count = 0;
                foreach($node->getRecords() as $roll => $rec) {
                  $pres = isset($rec['present']) ? (int)$rec['present'] : 0;
                  $sum += $pres;
                  $count++;
                  if(($pres / $total_days) < 0.75) {
                    $sName = !empty($rec['name']) ? $rec['name'] : $roll;
                    $detained[$roll] = array(
                      "name" => $sName,
                      "pct"  => round(100 * ($pres / $total_days), 1),
                      "attended" => $pres
                    );
                  }
                }
                if ($count > 0) {
                  $data[$key]['average'] = round($sum / $count, 1);
                }
                $data[$key]['detained'] = $detained;
              }
            }

            echo '<script>var data = '.json_encode($data).';</script>';
        ?>
          <div class="custom-tabs">
            <button class="custom-tab-btn active" onclick="$('.tab-pane').hide(); $('#graph-pane').show(); $('.custom-tab-btn').removeClass('active'); $(this).addClass('active');">
              Average Attendance Chart
            </button>
            <button class="custom-tab-btn" onclick="$('.tab-pane').hide(); $('#detained-pane').show(); $('.custom-tab-btn').removeClass('active'); $(this).addClass('active');">
              Shortage / Low Attendance Warnings (&lt; 75%)
            </button>
          </div>

          <div id="graph-pane" class="tab-pane" style="padding:20px 0;">
            <div id="graph" style="min-height: 380px;"></div>
          </div>

          <div id="detained-pane" class="tab-pane" style="display:none; padding:20px 0;">
            <?php foreach($data as $class => $d): ?>
              <div class="custom-card" style="margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                  <h4 style="font-weight:700; margin:0; color:var(--dark);"><?php echo htmlspecialchars($class); ?></h4>
                  <span class="status-badge <?php echo count($d['detained']) > 0 ? 'status-danger' : 'status-good'; ?>">
                    <?php echo count($d['detained']); ?> Student(s) with Shortage
                  </span>
                </div>

                <?php if(empty($d['detained'])): ?>
                  <p style="color:var(--success); font-weight:600; margin:0;">All students are in good standing (&ge; 75%).</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-bordered table-striped" style="margin:0; background:white;">
                      <thead>
                        <tr style="background:#f8fafc;">
                          <th>Roll / USN</th>
                          <th>Student Name</th>
                          <th>Attended</th>
                          <th>Percentage</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach($d['detained'] as $roll => $s): ?>
                          <tr>
                            <td><span class="student-usn-pill"><?php echo htmlspecialchars($roll); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($s['name']); ?></strong></td>
                            <td><?php echo $s['attended']; ?> / <?php echo $d['total']; ?></td>
                            <td><span style="color:#ef4444; font-weight:700;"><?php echo $s['pct']; ?>%</span></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

        <?php } else { ?>
          <div class="text-center" style="padding: 40px; color:var(--muted);">
            <h3>No classes found</h3>
            <p>Create classes to start viewing attendance statistics.</p>
          </div>
        <?php } ?>
      </div>

    </div>
  </div>

  <footer class="app-footer">
    <div class="app-container">
      <p>&copy; <?php echo date('Y'); ?> Online Attendance Tracker. Built for Students &amp; Faculty.</p>
    </div>
  </footer>

</body>
</html>
