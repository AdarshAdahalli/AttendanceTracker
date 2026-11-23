<?php
  session_start();
  if(isset($_SESSION['teacher_id']) && !empty($_SESSION['teacher_id'])) {
    header('Location: teacher.php');
    exit;
  }
  if(isset($_SESSION['student_usn']) && !empty($_SESSION['student_usn'])) {
    header('Location: student.php');
    exit;
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Attendance Tracker - Student &amp; Faculty Portal</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/login.js"></script>
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
          ONLINE ATTENDANCE <span class="badge-tracker">SYSTEM</span>
        </a>
      </div>
      <div id="app-nav" class="navbar-collapse collapse">
        <ul class="nav navbar-nav navbar-right">
          <li class="active"><a href="index.php">Home</a></li>
          <li><a href="student.php">Student Portal</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="main-wrapper">
    <div class="app-container">

      <!-- Hero Section -->
      <div class="welcome-hero text-center" style="padding: 45px 30px;">
        <span class="class-code-badge" style="background:rgba(255,255,255,0.15); color:white; margin-bottom:12px; display:inline-block;">
          INSTITUTIONAL ATTENDANCE PLATFORM
        </span>
        <h1 style="font-size:2.4rem; margin-bottom:12px;">Smart &amp; Seamless Attendance Tracking</h1>
        <p style="font-size:1.1rem; max-width:650px; margin:0 auto; color:#cbd5e1;">
          A unified portal for students to track real-time attendance percentages and faculty to record daily sessions effortlessly.
        </p>
      </div>

      <!-- Main Login Container with Tabs -->
      <div class="row">
        <div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
          <div class="custom-card" style="box-shadow: var(--shadow-lg);">

            <!-- Global Alert -->
            <div class="alert alert-warning hidden" style="border-radius:var(--radius-sm); margin-bottom:20px;">
              <span></span>
              <button type="button" class="close" onclick="$('.alert').addClass('hidden');">&times;</button>
            </div>

            <!-- Tab Switcher -->
            <div class="custom-tabs" style="justify-content:center;">
              <button class="custom-tab-btn tab-btn active" data-target="student-login-tab">
                &#127891; Student Portal
              </button>
              <button class="custom-tab-btn tab-btn" data-target="faculty-login-tab">
                &#128188; Faculty Login
              </button>
              <button class="custom-tab-btn tab-btn" data-target="faculty-signup-tab">
                &#10010; Faculty Sign Up
              </button>
            </div>

            <!-- Tab 1: Student Login -->
            <div id="student-login-tab" class="portal-tab-content">
              <div class="text-center" style="margin-bottom:24px;">
                <h3 style="font-weight:800; color:var(--dark); margin:0 0 6px 0;">Student Portal Login</h3>
                <p style="color:var(--muted); font-size:0.95rem;">Check your enrolled courses, attendance percentage, and eligibility.</p>
              </div>

              <form id="student-portal-form">
                <div class="form-group">
                  <label>University Seat Number (USN) / Roll No.</label>
                  <input class="form-control input-lg" type="text" name="usn" placeholder="e.g. 1RV21CS001 or 101/BC/25" required>
                </div>
                <div class="form-group">
                  <label>Password</label>
                  <input class="form-control input-lg" type="password" name="password" placeholder="Enter password" required>
                  <span class="help-block">&#128161; <strong>Tip:</strong> Default password is your USN.</span>
                </div>
                <button type="submit" id="btn-student-submit" class="btn btn-primary btn-lg btn-block" style="margin-top:20px;">
                  Login to Student Portal
                </button>
              </form>
            </div>

            <!-- Tab 2: Faculty Login -->
            <div id="faculty-login-tab" class="portal-tab-content" style="display:none;">
              <div class="text-center" style="margin-bottom:24px;">
                <h3 style="font-weight:800; color:var(--dark); margin:0 0 6px 0;">Faculty Login</h3>
                <p style="color:var(--muted); font-size:0.95rem;">Access your dashboard to conduct attendance and manage class rosters.</p>
              </div>

              <form id="login">
                <div class="form-group">
                  <label>Faculty Email Address</label>
                  <input class="form-control input-lg" type="email" name="email" placeholder="e.g. professor@college.edu" required>
                </div>
                <div class="form-group">
                  <label>Password</label>
                  <input class="form-control input-lg" type="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" id="btn-faculty-login" class="btn btn-primary btn-lg btn-block" style="margin-top:20px;">
                  Login as Faculty
                </button>
              </form>
            </div>

            <!-- Tab 3: Faculty Sign Up -->
            <div id="faculty-signup-tab" class="portal-tab-content" style="display:none;">
              <div class="text-center" style="margin-bottom:24px;">
                <h3 style="font-weight:800; color:var(--dark); margin:0 0 6px 0;">Faculty Registration</h3>
                <p style="color:var(--muted); font-size:0.95rem;">Create a new faculty account to start creating classes.</p>
              </div>

              <form id="signup">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Full Name</label>
                      <input class="form-control" type="text" name="name" placeholder="Prof. / Dr. Full Name" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>10-Digit Mobile Phone</label>
                      <input class="form-control" type="text" name="phone" placeholder="9876543210" required>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label>Official Email Address</label>
                  <input class="form-control" type="email" name="email" placeholder="faculty@college.edu" required>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Password</label>
                      <input class="form-control" type="password" name="password" placeholder="At least 6 characters" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label>Confirm Password</label>
                      <input class="form-control" type="password" name="password2" placeholder="Re-type password" required>
                    </div>
                  </div>
                </div>

                <button type="submit" id="btn-faculty-signup" class="btn btn-primary btn-lg btn-block" style="margin-top:15px;">
                  Create Faculty Account
                </button>
              </form>
            </div>

          </div>
        </div>
      </div>

      <!-- Feature Grid -->
      <div class="row" style="margin-top:20px;">
        <div class="col-md-4">
          <div class="stat-card text-center" style="padding:28px 20px;">
            <div style="font-size:2rem; margin-bottom:10px;">&#127891;</div>
            <h4 style="font-weight:700; color:var(--dark); margin-bottom:8px;">Student Dashboard</h4>
            <p style="color:var(--muted); font-size:0.9rem; margin:0;">
              Students can login using their USN to monitor course-by-course attendance percentages and shortage alerts.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card text-center" style="padding:28px 20px;">
            <div style="font-size:2rem; margin-bottom:10px;">&#9989;</div>
            <h4 style="font-weight:700; color:var(--dark); margin-bottom:8px;">Fast Session Recording</h4>
            <p style="color:var(--muted); font-size:0.9rem; margin:0;">
              Faculty can mark attendance in seconds with student names, USN pills, and one-click 'Mark All Present' shortcuts.
            </p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card text-center" style="padding:28px 20px;">
            <div style="font-size:2rem; margin-bottom:10px;">&#128202;</div>
            <h4 style="font-weight:700; color:var(--dark); margin-bottom:8px;">Interactive Analytics</h4>
            <p style="color:var(--muted); font-size:0.9rem; margin:0;">
              Visual column charts and detailed timelines show historical attendance trends session by session.
            </p>
          </div>
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
