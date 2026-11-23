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
  $phone = $_SESSION['phone'];
  $email = $_SESSION['email'];
  $classes = isset($_SESSION['classes']) && is_array($_SESSION['classes']) ? $_SESSION['classes'] : array();
  $teacher_id = $_SESSION['teacher_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Profile - Online Attendance Tracker</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/profile.js"></script>
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
          <li class="active"><a href="profile.php">Profile</a></li>
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

      <div class="welcome-hero">
        <h1>Faculty Profile</h1>
        <p>Manage your account credentials and contact details.</p>
      </div>

      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div class="custom-card">
            <div class="custom-card-header">
              <h2 class="custom-card-title">Profile Settings</h2>
            </div>

            <div class="form-group">
              <label>Full Name</label>
              <input class="form-control" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>">
            </div>

            <div class="form-group">
              <label>Phone Number</label>
              <input class="form-control" name="phone" placeholder="Enter your phone" value="<?php echo htmlspecialchars($phone); ?>">
            </div>

            <div class="form-group">
              <label>Email Address</label>
              <input class="form-control" name="email" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <div class="form-group">
              <label>Active Assigned Classes</label>
              <div style="font-size:1.1rem; font-weight:700; color:var(--dark);">
                <?php echo count($classes); ?> class(es)
              </div>
            </div>

            <div class="text-right" style="margin-top:25px;">
              <button class="btn btn-primary update-profile">Save Changes</button>
            </div>
          </div>
        </div>
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
