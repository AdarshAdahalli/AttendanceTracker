function verify(type,input) {
  var regexes = {
    'email': /^([\S]+)@([\S]+)\.([\S]+)$/,
    'phone': /^[0-9]{10}$/,
    'code': /^[a-zA-Z0-9\-_]{2,20}$/,
    'roll': /^[a-zA-Z0-9\/\-_]{2,25}$/,
    'name': /^[a-zA-Z0-9\s\'\.\-_]{2,50}$/,
    'num': /^[0-9]+$/
  };
  if (!regexes[type]) return true;
  return ((input.match(regexes[type]) == null) ? false : true);
}

$(document).ready(function () {
  processLogin();
  processSignup();
  processStudentLogin();

  // Tab switching
  $('.tab-btn').on('click', function() {
    var target = $(this).data('target');
    $('.tab-btn').removeClass('active');
    $(this).addClass('active');
    $('.portal-tab-content').hide();
    $('#' + target).fadeIn(250);
    $('.alert').addClass('hidden');
  });
});

function showAlert(message, type) {
  var alertEl = $('.alert');
  alertEl.removeClass('hidden alert-warning alert-danger alert-success alert-info');
  alertEl.addClass('alert-' + (type || 'warning'));
  alertEl.find('span').html(message);
}

function processStudentLogin() {
  $('#student-portal-form').submit(function(e) {
    e.preventDefault();
    var usn = $(this).find('input[name=usn]').val().trim();
    var pass = $(this).find('input[name=password]').val();
    var btn = $('#btn-student-submit');

    if (!usn || !pass) {
      showAlert('Please enter both your USN and password.', 'warning');
      return;
    }

    btn.prop('disabled', true).text('Signing in...');

    $.ajax({
      url: 'php/process_student_login.php',
      type: 'POST',
      data: { usn: usn, password: pass },
      dataType: 'json',
      success: function(r) {
        btn.prop('disabled', false).text('Login to Student Portal');
        if (r.status === 'success') {
          showAlert('<strong>Welcome, ' + r.name + '!</strong> Redirecting to your dashboard...', 'success');
          setTimeout(function() {
            window.location = 'student.php';
          }, 800);
        } else {
          showAlert(r.message || 'Invalid credentials.', 'danger');
        }
      },
      error: function() {
        btn.prop('disabled', false).text('Login to Student Portal');
        showAlert('Could not connect to the server. Please check your network.', 'danger');
      }
    });
  });
}

function processLogin() {
  $("#login").submit(function(e) {
    e.preventDefault();
    var data = {};
    var empty = false;
    $("#login input").each(function(k,v) {
      if(!$(v).val().length) {
        showAlert('Please enter <strong>' + $(v).attr('name') + '</strong>!', 'warning');
        empty = true;
        return false;
      }
      data[$(v).attr('name')] = $(v).val();
    });
    if (empty) return;

    var btn = $('#btn-faculty-login');
    btn.prop('disabled', true).text('Logging in...');

    $.ajax({
      url : 'php/process_login.php',
      type : 'post',
      data : data,
      dataType : 'json',
      success : function(r) {
        btn.prop('disabled', false).text('Login as Faculty');
        switch(r.error) {
          case 'empty' : 
            showAlert('Please fill in all credentials.', 'warning');
            break;
          case 'not_found' :
            showAlert('No faculty account found with this email. Please sign up.', 'danger');
            $('.tab-btn[data-target=faculty-signup-tab]').click();
            $("form#signup input[name=email]").val(data.email).focus();
            break;
          case 'incorrect' :
            showAlert('Incorrect Password! Please try again.', 'danger');
            break;
          case 'none' :
            showAlert('<strong>Welcome back!</strong> Redirecting to faculty dashboard...', 'success');
            setTimeout(function() {
              window.location = "teacher.php";
            }, 800);
            break;
        }
      },
      error: function() {
        btn.prop('disabled', false).text('Login as Faculty');
        showAlert('Server communication error. Please try again.', 'danger');
      }
    });
  });
}

function processSignup() {
  $("#signup").submit(function(e) {
    e.preventDefault();
    var data = {};
    var isEmpty = false;
    $("#signup input").each(function(k,v) {
      if(!$(v).val().length) {
        showAlert('Please enter <strong>' + $(v).attr('name') + '</strong>!', 'warning');
        isEmpty = true;
        return false;
      }
      data[$(v).attr('name')] = $(v).val();
    });
    if(isEmpty) return;

    if($("#signup input[name=password]").val() != $("#signup input[name=password2]").val()) {
      showAlert('Passwords do not match!', 'warning');
      return;
    }
    if($("#signup input[name=password]").val().length < 6) {
      showAlert('Password must be at least 6 characters long!', 'warning');
      return;
    }
    if(!verify('phone', data.phone)) {
      showAlert('Please enter a valid 10-digit phone number.', 'warning');
      return;
    }
    if(!verify('email', data.email)) {
      showAlert('Please enter a valid email address.', 'warning');
      return;
    }
    if(!verify('name', data.name)) {
      showAlert('Please enter a valid full name.', 'warning');
      return;
    }

    var btn = $('#btn-faculty-signup');
    btn.prop('disabled', true).text('Creating Account...');

    $.ajax({
      url : 'php/process_signup.php',
      type : 'post',
      data : data,
      dataType : 'json',
      success : function(r) {
        btn.prop('disabled', false).text('Create Faculty Account');
        switch(r.error) {
          case 'email' : showAlert('Invalid Email format.', 'warning'); break;
          case 'phone' : showAlert('Invalid Phone Number.', 'warning'); break;
          case 'name' : showAlert('Invalid Name format.', 'warning'); break;
          case 'empty' : showAlert('Please fill in all details.', 'warning'); break;
          case 'mismatch' : showAlert('Passwords do not match.', 'warning'); break;
          case 'small' : showAlert('Password should be at least 6 characters.', 'warning'); break;
          case 'exists' : 
            showAlert('An account with this email already exists. Please log in.', 'info');
            $('.tab-btn[data-target=faculty-login-tab]').click();
            $("form#login input[name=email]").val(data.email).focus();
            break;
          case 'db_error' : 
            showAlert('Server database issue. Please try again later.', 'danger');
            break;
          case 'none' : 
            showAlert('<strong>Account created successfully!</strong> Please log in with your credentials.', 'success');
            $('.tab-btn[data-target=faculty-login-tab]').click();
            $("form#login input[name=email]").val(data.email);
            $("form#login input[name=password]").focus();
            break;
        }
      },
      error: function() {
        btn.prop('disabled', false).text('Create Faculty Account');
        showAlert('Server connection error. Please try again.', 'danger');
      }
    });
  });
}
