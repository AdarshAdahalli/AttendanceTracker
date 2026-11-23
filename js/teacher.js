function verify(type,input) {
  var regexes = {
    'email': /^([\S]+)@([\S]+)\.([\S]+)$/,
    'phone': /^[0-9]{10}$/,
    'code': /^[a-zA-Z0-9\-_]{2,20}$/,
    'roll': /^[a-zA-Z0-9\/\-_]{2,25}$/,
    'name': /^[a-zA-Z0-9\s\'\.\-_]{2,50}$/,
    'num': /^[0-9]+$/,
    'number': /^[0-9]+$/
  };
  if(!regexes[type]) return true;
  return ((input.match(regexes[type]) == null) ? false : true);
}

$(document).ready(function() {
  $('#cancel').click(function() {
    $('.modal').modal('hide');
  });

  $('#add').click(function() {
    addTheClass();
  });

  $(document).on('click', '.delete-class-warning', function() {
    deleteWarning($(this).closest('.class-card, .class'));
  });

  $('.delete-class-code').click(function() {
    deleteClass($(this).parent());
  });
});

function deleteWarning(handle) {
  var code = handle.find('.code').text();
  var section = handle.find('.section').text();
  var year = handle.find('.year').text();
  $('.warning-class').html('<span class="warning-code">'+code+'</span> ( <span class="warning-section">'+section+'</span> ) , <span class="warning-year">'+year+'</span>');
}

function deleteClass(handle) {
  var code = handle.find('.warning-code').text();
  var section = handle.find('.warning-section').text();
  var year = handle.find('.warning-year').text();
  $.ajax({
    url : 'php/delete_class.php',
    type : 'post',
    data : {code:code,year:year,section:section},
    dataType : 'json',
    success : function(r) {
      switch(r.error) {
        case 'not_found' :
          alert('No such class found! Logging you out for security.');
          window.location = "logout.php";
        break;
        case 'illegal' :
          alert('You did not create this class! Logging you out for security.');
          window.location = "logout.php";
        break;
        case 'failure' :
          alert('Error processing request.');
          window.location = "logout.php";
        break;
        case 'none' :
          $('.delete-warning').modal('hide');
          $('.class-card, .class').each(function(k,v) {
            if($(this).find('.code').text() == code && 
               $(this).find('.year').text() == year && 
               $(this).find('.section').text() == section) {
                $(this).fadeOut(400, function() {
                  $(this).remove();
                });
                return;
            }
          });
        break;
      }
    }
  });
}

function addTheClass() {
  var fields = $('#add_class_form input,#add_class_form select');
  var data = {};
  for(var i = 0; i < fields.length; i++) {
    data[jQuery(fields[i]).attr('name')] = jQuery(fields[i]).val();
  }
  
  if(!verify('code', data.code)) {
    alert('Please enter a valid Subject Code (e.g. BCA-101 or CS101)');
    $('input[name=code]').focus();
    return;
  }
  if(!verify('number', data.year)) {
    alert('Invalid Year!');
    return;
  }
  if(!verify('number', data.section) || data.section == "-1") {
    alert('Please choose a valid Section!');
    return;
  }
  if(!verify('number', data.semester) || data.semester == "-1") {
    alert('Please choose a valid Semester!');
    return;
  }
  if(!verify('roll', data.start)) {
    alert('Invalid Starting Roll / USN (e.g. 101/BC/25 or 1RV21CS001)');
    $('input[name=start]').focus();
    return;
  }
  if(data.end && !verify('roll', data.end)) {
    alert('Invalid Ending Roll / USN');
    $('input[name=end]').focus();
    return;
  }

  $('#add').prop('disabled', true).text('Creating...');

  $.ajax({
    url : 'php/add_class.php',
    type : 'post',
    data : data,
    dataType : 'json',
    success : function(r) {
      $('#add').prop('disabled', false).text('Add Class');
      if(r.error) {
        if(r.error == 'exists') {
          alert('A class with this Code, Section, and Year already exists!');
        } else {
          alert('Error: ' + r.error);
        }
        return;
      }
      $('.bs-example-modal-lg').modal('hide');
      window.location.reload();
    },
    error : function() {
      $('#add').prop('disabled', false).text('Add Class');
      alert('Network or server error while adding class.');
    }
  });
}
