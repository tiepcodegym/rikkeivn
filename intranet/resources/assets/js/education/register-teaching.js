$.ajaxSetup({
   headers: {
      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
   },
});

var globalTotal;
var globalIndex = 1;
var globalDateToday = new Date();

$(document).ready(function () {
   hide();
});

$(document).on('blur', '.tblDetailInput input', function () {
   if ($(this).val()) {
      $(this).removeClass('error-input');
   }

   if (!$('#tblOperationBody').find('.error-input').length) {
      $('.error-input-mess').hide();
   } else {
      $('.error-input-mess').show();
   }
});

// $(document).on('change', '.register-type', function()
// {
//    var value = $(this).val();
//    if (value == 1) {
//       show();
//       $('#course_id').addClass('hidden');
//       $('#class_id').addClass('hidden');
//       $('.detail-class').addClass('hidden');
//       $('.tranning_hour').val('');
//       $('.content').val(globalContent ? globalContent : '').prop('readonly', true);
//    } else {
//       hide();
//       $('.content').val(globalContent ? globalContent : '').prop('readonly', false);
//    }
//    $('#radio-error').html('');
//    hiddenError()
// });

$(document).on('blur', '#title, #content', function()
{
   var value = $(this).val();
   if (value) {
      $('#title-error').addClass('hidden');
   }
});

// $(document).on('change', '#course_type_id', function()
// {
//    var selectedVal = $("#course_type_id option:selected").val();
//    if ($("#register-type1").is(':checked')) {
//       getCourseByCourseById(selectedVal);
//    }
// });

// $(document).on('change', '#course_id', function()
// {
//    var selectedVal = $("#course_id option:selected").val();
//    getClassesByCourseId(selectedVal);
// });

// $(document).on('change', '#class_id', function()
// {
//    var selectedVal = $("#class_id option:selected").val();
//    getClassDetailById(selectedVal);
// });

$(document).on('click', '.add-new', function()
{
   globalIndex++;
   $('.tblDetailInput').append(renderHtmlDeitalShift(globalIndex));
   Datetimepicker();
});

$(document).on('click', '.btn-remove', function()
{
   var $this  = $(this);
   var trCtr = $this.closest('tr');
   $('.tranning_hour').val('');
   trCtr.remove();
   totalTime();
   if ($('#tblOperationBody tbody').find('tr').length == 0) {
      $('.add-new').click();
   }
});

$(document).on('click', '.submitBtn', function()
{
   var mode_tyoe = $(this).attr('data-mode')
   setErrorInput();
   if(mode_tyoe === 'mode_update') {
      if ($('#tblOperationBody').find('.error-input').length == 0) {
          if ($('#title').val() != '' && $('#content').val() != '') {
              formSubmit();
          }
      }
   } else {
      if ($('#tblOperationBody').find('.error-input').length == 0) {
          if ($('#title').val() != '' && $('#content').val() != '') {
              formSubmit();
          }
      }
   }
});

function setErrorInput() {
   var $tbodyCtr = $('#tblOperationBody tbody');

   var valueTitle = $('#title').val();
   var valueContent = $('#content').val();
   var valueTarget = $('.target').val();

   if ($.trim(valueTitle) == '') {
      $('.dv-title').find('.error').remove();
      $('#title').val('');
      $('#title').after('<label id="title-error" class="error" for="title">'+ globalTitle +'</label>');
   }

   if ($.trim(valueContent) == '') {
      $('.dv-content').find('.error').remove();
      $('#content').val('');
      $('#content').after('<label id="content-error" class="error" for="title">'+ globalConent +'</label>');
   }

   if ($.trim(valueTarget) == '') {
      $('.tg-content').find('.error').remove();
      $('.target').val('');
      $('.target').after('<label id="tg-error" class="error" for="title">'+ globalConent +'</label>');
   }

   $tbodyCtr.each(function () {
      $(this).find('input, select').each(function () {
         if (!$(this).val()) {
            $('.error-input-mess').html(globalMessage['Input all']);
            $('.error-input-mess').addClass('error');
            $(this).addClass('error-input');
         } else {
            $(this).removeClass('error-input');
         }
      });

      if (!$('#tblOperationBody').find('.error-input').length) {
         $('.error-input-mess').hide();
      } else {
         $('.error-input-mess').show();
      }
   });
}

/**
 * Form submit
 */
function formSubmit() {

   $('#form-create-update').submit();
}

/**
 * get Course, class, class details
 */
function getCourseByCourseById(courseId)
{
   globalTotal = 0;
   loadding();
   $.ajax({
      url: '/manager/courses/type/' + courseId,
      type: 'get',
      dataType: 'json',
      success: function (data) {
         if (data.data) {
            $('#course_id').empty();
            $('#class_id').empty();
            $('#course_id').append(renderHtmlOption(data.data.itemCourse, ''));
            $('#class_id').append(renderHtmlOption(data.data.itemsClass, ''));
            if($("#register-type2").is(':checked')) {
               $('.content').val(globalContent ? globalContent : '');
            } else {
               $('.content').val(data.data.contentCours.description);
            }
            renderHtmlTime(data.data.classDetail);
         }
      },
      error: function (x, t, m) {
         if (t === 'timeout') {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorTimeoutText);
         } else {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorText);
         }
      },
      complete: function () {
         hiddenLoading();
         if($("#register-type2").is(':checked')) {
            $('.content').prop('readonly', false);
         } else {
            $('.content').prop('readonly', true);
         }
      },
   });
}

/**
 * get class by Course_id
 */
function getClassesByCourseId(courseId)
{
   $('#class_id').addClass('hidden');
   $('.detail-class').addClass('hidden');
   $('#update_class_loading').removeClass('hidden');
   $('#update_detail_loading').removeClass('hidden');
   $.ajax({
      url: '/manager/classes/' + courseId,
      type: 'get',
      dataType: 'json',
      success: function (data) {
         if (data.data) {
            $('#class_id').empty();
            $('#class_id').append(renderHtmlOption(data.data.itemsClass, ''));
            renderHtmlTime(data.data.classDetail);
            $('.content').val(data.data.contentCours.description);
         }
      },
      error: function (x, t, m) {
         if (t === 'timeout') {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorTimeoutText);
         } else {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorText);
         }
      },
      complete: function () {
         $('#update_class_loading').addClass('hidden');
         $('#update_detail_loading').addClass('hidden');
         $('#class_id').removeClass('hidden');
         $('.detail-class').removeClass('hidden');
         if($("#register-type2").is(':checked')) {
            $('.content').val('').prop('readonly', false);
         } else {
            $('.content').prop('readonly', true);
         }
      },
   });
}

function getCourse(courseId)
{
   $.ajax({
      url: '/manager/courses/' + courseId,
      type: 'get',
      dataType: 'json',
      success: function (data) {
         if (data.data) {
            if($("#register-type2").is(':checked')) {
               $('.content').val(globalContent ? globalContent : '').prop('readonly', false);
            } else {
               $('.content').val(data.data.description).prop('readonly', true);
            }
         }
      },
      error: function (x, t, m) {
         if (t === 'timeout') {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorTimeoutText);
         } else {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorText);
         }
      }
   });
}

/**
 * get class by Course_id
 */
function getClassDetailById(class_id)
{
   $('#update_detail_loading').removeClass('hidden');
   $('.detail-class').addClass('hidden');
   globalTotal = 0;
   $.ajax({
      url: '/manager/class_detail/' + class_id,
      type: 'get',
      dataType: 'json',
      success: function (data) {
         renderHtmlTime(data.data);
      },
      error: function (x, t, m) {
         if (t === 'timeout') {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorTimeoutText);
         } else {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorText);
         }
      },
      complete: function () {
         $('#update_detail_loading').addClass('hidden');
         $('.detail-class').removeClass('hidden');
      },
   });
}

/**
 * get info register teaching details
 */
function getRegisterTeachingDetails(course_type_id, course_id, class_id)
{
   globalTotal = 0;
   loadding();
   $.ajax({
      url: '/manager/register-teaching/detail/' + course_type_id + '/' + course_id + '/' + class_id,
      type: 'get',
      dataType: 'json',
      success: function (data) {
         if (data.data) {
            $('#course_id').empty();
            $('#class_id').empty();
            $('#course_id').append(renderHtmlOption(data.data.itemCourse, course_id));
            $('#class_id').append(renderHtmlOption(data.data.itemsClass, class_id));
            if($("#register-type2").is(':checked')) {
               $('.content').val(globalContent ? globalContent : '').prop('readonly', false);
            } else {
               $('.content').val(data.data.contentCours.description).prop('readonly', true);
            }
            renderHtmlTime(data.data.classDetail);
         }
      },
      error: function (x, t, m) {
         if (t === 'timeout') {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorTimeoutText);
         } else {
            $('#modal-warning-notification .modal-body p.text-default').text(globalErrorText);
         }
      },
      complete: function () {
         hiddenLoading();
      },
   });
}

/**
 * render Html
 */
function renderHtmlTime(data)
{
   var strHtml = '';
   if (data.length > 0) {
      $('.detail-class').empty();
      for(var i = 0; i < data.length; i++) {
         var countHour = ((Date.parse(data[i].end_date_time) - Date.parse(data[i].start_date_time)) / 1000 ) / 3600
         globalTotal = globalTotal + countHour;
         strHtml += '<input type="hidden" name="class_detai['+ i +'][class_id]" value="'+ data[i].class_id +'" />';
         strHtml += '<input type="hidden" name="class_detai['+ i +'][name]" value="'+ data[i].name +'" />';
         strHtml += '<input type="hidden" name="class_detai['+ i +'][start_date]" value="'+ data[i].start_date_time +'" />';
         strHtml += '<input type="hidden" name="class_detai['+ i +'][end_date]" value="'+ data[i].end_date_time +'" />';
         strHtml += '<p>'+ globalCa + ' ' + data[i].name +' : '+ moment(data[i].start_date_time).format("YYYY-MM-DD H:mm") + ' - ' + moment(data[i].end_date_time).format("YYYY-MM-DD H:mm")  + '</p>';
      }
      if ($("#register-type1").is(":checked")) {
         $('.tranning_hour').val(globalTotal);
         $('#tranning_hour-error').addClass('hidden');
      }
      $('.detail-class').append(strHtml);
   } else {
      $('.detail-class').empty();
      $('.tranning_hour').val('');
   }
}

/**
 * Show input
 */
// function show()
// {
//    if (varGlobalPassModule.dataRegisterTime.length > 0) {
//       getRegisterTeachingDetails(globalCourseType, globalCourseId, globalClassId);
//    } else {
//       getCourseByCourseById($("#course_type_id option:selected").val());
//    }
//    $('.tranning_hour').val(globalTotal);
//    $('#course').removeClass('hidden');
//    $('#class').removeClass('hidden');
//    $('#detail-class').removeClass('hidden');
//    $('.lbl-required .required').addClass('hidden');
//    $('.detail_class_choose').addClass('hidden');
// }

/**
 * hide
 */
function hide()
{
   var total = globalTotalTime ? globalTotalTime : '';
   $('.tranning_hour').val(total);
   $('#course').addClass('hidden');
   $('#class').addClass('hidden');
   $('#detail-class').addClass('hidden');
   $('.lbl-required .required').removeClass('hidden');
   $('.detail_class_choose').removeClass('hidden');

   $('.tblDetailInput').empty();
   if (varGlobalPassModule.dataRegisterTime.length > 0) {
      // if (globalType == 2) {
         for(var i = 0; i < varGlobalPassModule.dataRegisterTime.length; i++){
            $('.tblDetailInput').append(renderHtmlDeitalShift((i+1), varGlobalPassModule.dataRegisterTime[i]));
            globalIndex = globalIndex + 1;
         }
         $('.content').val(globalContent);
      // }
      // else {
      //    $('.tblDetailInput').append(renderHtmlDeitalShift(globalIndex, null));
      //    $('.content').val('');
      //    $('.tranning_hour').val('');
      // }
   } else {
      $('.tblDetailInput').append(renderHtmlDeitalShift(globalIndex, null));
   }

   Datetimepicker();
}

function Datetimepicker()
{
   // init datetimepicker
   $('.start_date').datetimepicker({
      allowInputToggle: true,
      format: 'YYYY-MM-DD H:mm',
      useCurrent: false,
      sideBySide: true
   }).on('dp.hide', function (e) {
      var minDay = moment(e.date).format("YYYY-MM-DD H:mm");
      var maxDay = moment(e.date).format("YYYY-MM-DD 23:59");
      if ($(this).val()) {
         $(this).parents('tr').find('.end_date').data('DateTimePicker').minDate(minDay);
         $(this).parents('tr').find('.end_date').data('DateTimePicker').maxDate(maxDay);
      }
      totalTime();
   }).on('dp.show', function () {
      $(this).parents('tr').find('.start_date').data('DateTimePicker').minDate(globalDateToday)
   });

   $('.end_date').datetimepicker({
      allowInputToggle: true,
      format: 'YYYY-MM-DD H:mm',
      useCurrent: false,
      sideBySide: true
   }).on('dp.hide', function (e) {
      var minDay = moment(e.date).format("YYYY-MM-DD 00:00");
      var maxDay = moment(e.date).format("YYYY-MM-DD H:mm");
      var startDayValue  = $(this).parents('tr').find('.start_date').val();
      if(!startDayValue) {
         $(this).parents('tr').find('.start_date').data('DateTimePicker').maxDate(maxDay);
         $(this).parents('tr').find('.start_date').data('DateTimePicker').minDate(minDay);
      }
      totalTime();
   }).on('dp.show', function () {
      $(this).parents('tr').find('.end_date').data('DateTimePicker').minDate(globalDateToday);
   });
}

function hiddenError()
{
   $('#title-error').remove();
   $('#content-error').remove();
   $('.error-input-mess').hide();
   $('#tg-error').remove();
   $('#team-error').remove();
}
$(document).ready(function(){
   $('#team_id_add').multiselect({
      nonSelectedText: nonSelectedText,
      allSelectedText: allSelectedText,
      numberDisplayed: 3,
   });
   $('.caret').addClass('hidden');
})
