$('#add-folk,.edit-folk').on('click', function(e) {
    e.preventDefault();
    $('#lib-folk-create').show(800);
    $('#lib-folk-create').removeClass('hidden');
    $('#lib-folk-list').hide(800);
    var id = parseInt($(this).attr('data-id'));
    setData(id);
});

var rules = {
    'name': {
        required: true,
        rangelength: [0, 255],
    },
};
var messages = {
    'name': {
        required: requiredText,
        rangelength: rangeText,
    },  
};

$('#form-create-folk').validate({
    'rules': rules,
    'messages': messages,
});

$('#form-create-folk').on('submit', function(e){
   e.preventDefault();
   if ($('#form-create-folk').valid()) {
       $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
       $.ajax({
           url: $(this).attr('action'),
           type: 'POST',
           data: $(this).serialize(),
           dataType: 'JSON',
           success : function(rs) {
               if(rs.success) {
                   setTimeout(function() {
                       window.location.href = urlList;
                   }, 1000);
               } else {
                   setErrorMessage(rs.messages);
               }
           },
           error : function(rs) {
               alert(rs.statusText);
           }
       });
   }
});

function setErrorMessage($errors) {
   var attrs = Object.keys($errors);
   $.each(attrs, function($i, $key){
       $('input[name="'+$key+'"]').addClass('error');
       var label = $('#'+$key+ '-error');
       if(!label.length) {
           $('input[name="'+$key+'"]').after('<label id="'+$key+'-error" class="error" for="'+$key+'"></label>');
       }
       label = $('#'+$key+ '-error');
       label.text(''+$errors[$key][0]);
   });
}

/**
 * setData by id
 * @param {type} $id
 * @returns {undefined}         */
function setData($id) {
    var trId = $('#folk_' + $id);
    if (($id != 0) && trId.length) {
        // change text btn submit
        $('#form-create-folk').find('button[type="submit"]').text(updateText);
        var name = trId.find('td[data-col="name"]').text();
        var form = $('#form-create-folk');
        $('#form-create-folk input[name="name"]').val(name);
        $('#form-create-folk input[name="id"]').val($id);
    }
}