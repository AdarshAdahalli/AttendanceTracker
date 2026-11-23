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
  <title>Class Management - Online Attendance Tracker</title>
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
          <li><a href="teacher.php">Dashboard</a></li>
          <li><a href="profile.php">Profile</a></li>
          <li class="active"><a href="class.php">Class Management</a></li>
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
        <h1>Class Management</h1>
        <p>Edit course settings, manage student rosters, and assign student names.</p>
      </div>

      <?php if(empty($classes)): ?>
        <div class="custom-card text-center" style="padding: 50px;">
          <h3>You haven't created any classes yet!</h3>
          <p style="color:var(--muted); margin-bottom:20px;">Create a class from the faculty dashboard to get started.</p>
          <a href="teacher.php" class="btn btn-primary">Go to Dashboard</a>
        </div>
      <?php else: ?>
        <?php 
          $n = new Node;
          foreach($classes as $class_id):
            $node = $n->retrieveObjecti($class_id, $teacher_id);
            if(!$node) continue;
            $code = $node->getCode();
            $section = $node->getSection();
            $year = $node->getYear();
            $semester = $node->getSemester();
            $records = $node->getRecords();
        ?>
          <div class="custom-card" id="_<?php echo $class_id; ?>_">
            <div class="custom-card-header">
              <div>
                <span class="class-code-badge"><?php echo htmlspecialchars($code); ?></span>
                <span style="font-size:1.1rem; font-weight:700; margin-left:10px; color:var(--dark);">
                  Section <?php echo htmlspecialchars($section); ?> &bull; Year <?php echo htmlspecialchars($year); ?> (Semester <?php echo htmlspecialchars($semester); ?>)
                </span>
              </div>
              <div>
                <a href="take.php?cN=<?php echo $class_id; ?>" class="btn btn-success btn-sm">Take Attendance</a>
              </div>
            </div>

            <!-- Edit Class Details Form -->
            <div class="row" style="margin-bottom: 25px;">
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label>Subject Code</label>
                  <input class="form-control" name="code" value="<?php echo htmlspecialchars($code); ?>" placeholder="e.g. BCA-101">
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label>Academic Year</label>
                  <input class="form-control" name="year" value="<?php echo htmlspecialchars($year); ?>" placeholder="e.g. 2026">
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label>Section</label>
                  <input class="form-control" name="section" value="<?php echo htmlspecialchars($section); ?>" placeholder="e.g. 1 or A">
                </div>
              </div>
              <div class="col-md-3 col-sm-6">
                <div class="form-group">
                  <label>Semester</label>
                  <input class="form-control" name="semester" value="<?php echo htmlspecialchars($semester); ?>" placeholder="e.g. 3">
                </div>
              </div>
              <div class="col-md-12 text-right">
                <button class="btn btn-primary update" data-classid="<?php echo $class_id; ?>">Update Class Info</button>
              </div>
            </div>

            <!-- Student Roster for this Class -->
            <hr style="border-color:var(--border-color); margin: 25px 0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
              <h4 style="font-weight:700; color:var(--dark); margin:0;">
                Enrolled Students (<?php echo count($records); ?>)
              </h4>
              <button class="btn btn-sm btn-outline open-add-modal" data-classid="<?php echo $class_id; ?>">
                + Add Student to Class
              </button>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-striped" style="margin:0; background:white;">
                <thead>
                  <tr style="background:#f8fafc; color:var(--dark);">
                    <th style="width: 50px;">#</th>
                    <th style="width: 200px;">Roll / USN</th>
                    <th>Student Name</th>
                    <th style="width: 140px; text-align:center;">Attended</th>
                    <th style="width: 120px; text-align:center;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sIndex = 1;
                    foreach($records as $roll => $rData): 
                      $sName = isset($rData['name']) && !empty($rData['name']) ? $rData['name'] : '';
                  ?>
                    <tr>
                      <td><?php echo $sIndex++; ?></td>
                      <td>
                        <strong class="student-usn-pill" style="font-size:0.92rem;"><?php echo htmlspecialchars($roll); ?></strong>
                      </td>
                      <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                          <input type="text" class="form-control input-sm student-inline-name" 
                                 value="<?php echo htmlspecialchars($sName); ?>" 
                                 placeholder="Enter Student Name" 
                                 data-roll="<?php echo htmlspecialchars($roll); ?>"
                                 data-classid="<?php echo $class_id; ?>"
                                 style="max-width:320px;">
                          <button class="btn btn-sm btn-outline save-inline-name-btn" title="Save Name">&#10003; Save</button>
                        </div>
                      </td>
                      <td style="text-align:center; vertical-align:middle;">
                        <span class="presence-count-badge"><?php echo (int)$rData['present']; ?> / <?php echo $node->getDays(); ?></span>
                      </td>
                      <td style="text-align:center; vertical-align:middle;">
                        <button class="btn btn-sm btn-outline delete-roster-roll" 
                                data-classid="<?php echo $class_id; ?>" 
                                data-roll="<?php echo htmlspecialchars($roll); ?>"
                                style="color:var(--danger); border-color:#fca5a5;">
                          Remove
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          </div>
        <?php endforeach; ?>
      <?php endif; ?>

    </div>
  </div>

  <!-- Add Student Modal -->
  <div class="modal fade" id="rosterAddStudentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
      <div class="modal-content">
        <form id="roster-add-student-form">
          <input type="hidden" id="roster-add-classid">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Add Student</h4>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Roll Number / USN</label>
              <input type="text" class="form-control" id="roster-add-roll" placeholder="e.g. 1RV21CS001" required>
            </div>
            <div class="form-group">
              <label>Student Name</label>
              <input type="text" class="form-control" id="roster-add-name" placeholder="e.g. Adarsh" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Student</button>
          </div>
        </form>
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
    // Update class params
    $('.update').on('click', function() {
      var parent = $(this).closest('.custom-card');
      var class_id = $(this).data('classid') || parent.attr('id').replace(/_/g, '');
      var code = parent.find('input[name=code]').val().trim();
      var year = parent.find('input[name=year]').val().trim();
      var section = parent.find('input[name=section]').val().trim();
      var semester = parent.find('input[name=semester]').val().trim();

      var btn = $(this);
      btn.prop('disabled', true).text('Updating...');

      $.ajax({
        url: 'php/update_class.php',
        type: 'POST',
        data: { class_id: class_id, code: code, year: year, section: section, semester: semester },
        dataType: 'json',
        success: function(r) {
          btn.prop('disabled', false).text('Update Class Info');
          if (r.error === 'none' || r.status === 'success') {
            alert('Class details updated successfully!');
            window.location.reload();
          } else {
            alert('Update error: ' + (r.error || 'Please check inputs'));
          }
        },
        error: function() {
          btn.prop('disabled', false).text('Update Class Info');
          alert('Server communication error.');
        }
      });
    });

    // Save inline student name
    $('.save-inline-name-btn').on('click', function() {
      var input = $(this).siblings('.student-inline-name');
      var roll = input.data('roll');
      var class_id = input.data('classid');
      var name = input.val().trim();
      var btn = $(this);

      btn.prop('disabled', true).text('...');

      $.ajax({
        url: 'php/update_student_name.php',
        type: 'POST',
        data: { class_id: class_id, roll: roll, name: name },
        dataType: 'json',
        success: function(resp) {
          btn.prop('disabled', false).text('Saved!');
          setTimeout(function(){ btn.text('Save'); }, 1500);
        },
        error: function() {
          btn.prop('disabled', false).text('Error');
        }
      });
    });

    // Open add student modal
    $('.open-add-modal').on('click', function() {
      var cid = $(this).data('classid');
      $('#roster-add-classid').val(cid);
      $('#rosterAddStudentModal').modal('show');
    });

    // Add student submit
    $('#roster-add-student-form').on('submit', function(e) {
      e.preventDefault();
      var cid = $('#roster-add-classid').val();
      var roll = $('#roster-add-roll').val().trim();
      var name = $('#roster-add-name').val().trim();

      $.ajax({
        url: 'php/add_single_student.php',
        type: 'POST',
        data: { class_id: cid, roll: roll, name: name },
        dataType: 'json',
        success: function(resp) {
          if (resp.status === 'success') {
            window.location.reload();
          } else {
            alert(resp.message || 'Error adding student');
          }
        }
      });
    });

    // Delete roll
    $('.delete-roster-roll').on('click', function() {
      var roll = $(this).data('roll');
      var cid = $(this).data('classid');
      if (confirm('Are you sure you want to remove ' + roll + ' from this class?')) {
        $.ajax({
          url: 'php/delete_roll.php',
          type: 'POST',
          data: { roll: roll, class_id: cid },
          dataType: 'json',
          success: function(r) {
            window.location.reload();
          }
        });
      }
    });
  });
  </script>
</body>
</html>
