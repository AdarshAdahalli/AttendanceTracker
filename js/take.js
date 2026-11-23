$(document).ready(function() {
  // Toggle Presence (P / A)
  $(document).on('click', '.btn-toggle-presence', function() {
    togglePresence($(this));
  });

  // Mark All Present
  $('#btn-mark-all-p').on('click', function() {
    $('.student-row:visible').each(function() {
      var btn = $(this).find('.btn-toggle-presence');
      if (btn.hasClass('is-absent')) {
        togglePresence(btn);
      }
    });
  });

  // Mark All Absent
  $('#btn-mark-all-a').on('click', function() {
    $('.student-row:visible').each(function() {
      var btn = $(this).find('.btn-toggle-presence');
      if (btn.hasClass('is-present')) {
        togglePresence(btn);
      }
    });
  });

  // Search filter
  $('#roster-search').on('keyup', function() {
    var query = $(this).val().toLowerCase();
    $('.student-row').each(function() {
      var name = $(this).find('.student-name').text().toLowerCase();
      var roll = $(this).find('.student-usn-pill').text().toLowerCase();
      if (name.indexOf(query) !== -1 || roll.indexOf(query) !== -1) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
  });

  // Save Attendance
  $('#submit').on('click', function() {
    submitAttendance();
  });

  // Delete Roll Modal Trigger
  $(document).on('click', '.delete-roll', function() {
    var roll = $(this).closest('.student-row').find('.student-usn-pill').text();
    $('.warning-roll').text(roll);
    $('.delete-warning').modal('show');
  });

  // Confirm Delete Roll
  $('.delete-rollnumber').on('click', function() {
    var roll = $('.warning-roll').text();
    deleteRoll(roll);
  });

  // Edit Name Modal Trigger
  $(document).on('click', '.edit-name-btn', function() {
    var row = $(this).closest('.student-row');
    var roll = row.find('.student-usn-pill').text();
    var currentName = row.find('.student-name').text();
    if (currentName === '(No Name Set)') currentName = '';

    $('#edit-name-roll').val(roll);
    $('#edit-name-input').val(currentName);
    $('#editNameModal').modal('show');
  });

  // Save Edited Name
  $('#save-student-name-btn').on('click', function() {
    var roll = $('#edit-name-roll').val();
    var newName = $('#edit-name-input').val().trim();
    if (!newName) {
      alert('Please enter a student name.');
      return;
    }

    var btn = $(this);
    btn.prop('disabled', true).text('Saving...');

    $.ajax({
      url: 'php/update_student_name.php',
      type: 'POST',
      data: { class_id: class_id, roll: roll, name: newName },
      dataType: 'json',
      success: function(resp) {
        btn.prop('disabled', false).text('Save Name');
        if (resp.status === 'success') {
          $('.student-row').each(function() {
            if ($(this).find('.student-usn-pill').text() === roll) {
              $(this).find('.student-name').text(newName).css('color', 'var(--dark)');
              var initials = newName.split(' ').map(function(s){return s[0];}).join('').substring(0,2).toUpperCase();
              $(this).find('.student-avatar').text(initials || roll.substring(0,2));
            }
          });
          $('#editNameModal').modal('hide');
        } else {
          alert(resp.message || 'Error updating name.');
        }
      },
      error: function() {
        btn.prop('disabled', false).text('Save Name');
        alert('Server communication error.');
      }
    });
  });

  // Add Single Student Form
  $('#add-student-form').on('submit', function(e) {
    e.preventDefault();
    var roll = $('#new-student-roll').val().trim();
    var name = $('#new-student-name').val().trim();

    if (!roll) {
      alert('Please enter a Roll Number / USN.');
      return;
    }

    var btn = $('#btn-submit-add-student');
    btn.prop('disabled', true).text('Adding...');

    $.ajax({
      url: 'php/add_single_student.php',
      type: 'POST',
      data: { class_id: class_id, roll: roll, name: name },
      dataType: 'json',
      success: function(resp) {
        btn.prop('disabled', false).text('Add Student');
        if (resp.status === 'success') {
          $('#addStudentModal').modal('hide');
          $('#new-student-roll').val('');
          $('#new-student-name').val('');
          window.location.reload();
        } else {
          alert(resp.message || 'Error adding student.');
        }
      },
      error: function() {
        btn.prop('disabled', false).text('Add Student');
        alert('Server error while adding student.');
      }
    });
  });
});

function togglePresence(btn) {
  var row = btn.closest('.student-row');
  var countBadge = row.find('.presence-val');
  var currentCount = parseInt(countBadge.text()) || 0;

  if (btn.hasClass('is-absent')) {
    btn.removeClass('is-absent').addClass('is-present').html('&#10003; Present');
    countBadge.text(currentCount + 1);
  } else {
    btn.removeClass('is-present').addClass('is-absent').html('&times; Absent');
    countBadge.text(Math.max(0, currentCount - 1));
  }
}

function deleteRoll(roll) {
  $.ajax({
    url: 'php/delete_roll.php',
    type: 'POST',
    data: { roll: roll, class_id: class_id },
    dataType: 'json',
    success: function(r) {
      if (r.error === 'none') {
        $('.student-row').each(function() {
          if ($(this).find('.student-usn-pill').text() === roll) {
            $(this).fadeOut(400, function() {
              $(this).remove();
            });
          }
        });
        $('.delete-warning').modal('hide');
      } else {
        alert('Error deleting student record.');
      }
    }
  });
}

function submitAttendance() {
  var data = [];
  var time = Math.round((new Date).getTime() / 1000);

  $('.student-row').each(function() {
    var roll = $(this).find('.student-usn-pill').text();
    var count = $(this).find('.presence-val').text();
    data.push({
      roll: roll,
      newpresent: count,
      timestamp: time
    });
  });

  var submitBtn = $('#submit');
  submitBtn.prop('disabled', true).html('Saving Attendance...');

  $.ajax({
    url: 'php/mark_attendance.php',
    type: 'POST',
    data: { content: data, class_id: class_id, teacher_id: teacher_id },
    dataType: 'json',
    success: function(r) {
      if (r.error === 'none') {
        submitBtn.html('&#10003; Saved Successfully!');
        setTimeout(function() {
          window.location = "teacher.php";
        }, 1200);
      } else {
        submitBtn.prop('disabled', false).html('Save Attendance');
        alert('Error saving attendance: ' + (r.error || 'Unknown error'));
      }
    },
    error: function() {
      submitBtn.prop('disabled', false).html('Save Attendance');
      alert('Server error occurred while saving.');
    }
  });
}
