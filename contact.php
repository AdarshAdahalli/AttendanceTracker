<?php
  session_start();
  $isTeacher = isset($_SESSION['teacher_id']);
  $isStudent = isset($_SESSION['student_usn']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact &amp; Support - Online Attendance Tracker</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
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
          ONLINE ATTENDANCE <span class="badge-tracker">SUPPORT</span>
        </a>
      </div>
      <div id="app-nav" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
          <?php if($isTeacher): ?>
            <li><a href="teacher.php">Dashboard</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="class.php">Class</a></li>
            <li><a href="statistics.php">Statistics</a></li>
            <li class="active"><a href="contact.php">Contact</a></li>
            <li class="logout-btn"><a href="logout.php">Logout</a></li>
          <?php elseif($isStudent): ?>
            <li><a href="student.php">My Attendance</a></li>
            <li class="active"><a href="contact.php">Contact</a></li>
            <li class="logout-btn"><a href="php/student_logout.php">Logout</a></li>
          <?php else: ?>
            <li><a href="index.php">Home</a></li>
            <li><a href="student.php">Student Portal</a></li>
            <li class="active"><a href="contact.php">Contact</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="main-wrapper">
    <div class="app-container">

      <div class="welcome-hero text-center" style="padding:40px 20px;">
        <h1>Contact &amp; Technical Support</h1>
        <p>Need assistance or have queries regarding your attendance?</p>
      </div>

      <div class="row">
        <div class="col-md-8 col-md-offset-2">
          <div class="custom-card">
            <div class="custom-card-header">
              <h2 class="custom-card-title">Send a Query</h2>
            </div>
            
            <form onsubmit="alert('Thank you! Your message has been received.'); this.reset(); return false;">
              <div class="form-group">
                <label>Your Name</label>
                <input class="form-control" placeholder="Enter your full name" required>
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <input class="form-control" type="email" placeholder="Enter your email" required>
              </div>
              <div class="form-group">
                <label>USN / Faculty ID (Optional)</label>
                <input class="form-control" placeholder="e.g. 1RV21CS001">
              </div>
              <div class="form-group">
                <label>Message / Issue Description</label>
                <textarea class="form-control" rows="5" placeholder="Describe the problem or query..." required></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-block">Submit Query</button>
            </form>
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
