// render option select
function renderHtmlOption(object_data, id)
{
    var strHtml = '';
    $.each(object_data,function(key,value){
        var selected = id == key ? 'selected' : '';
        strHtml += '<option value="'+key+'"' + selected + '>'+value+'</option>';
    });

    return strHtml;
}

// render register teachings (time)
function renderHtmlDeitalShift(tabindex, object)
{
    var name = '';
    var start_date = '';
    var end_date = '';
    if (object) {
        name = object.name;
        start_date = object.start_date;
        end_date = object.end_date;
    }

    var strHtml = '';
    strHtml += '<tr tabindex="'+ tabindex +'">';
    strHtml += '   <td>';
    strHtml += '      <input type="number" min="1" id="name_'+ tabindex +'" placeholder="' + globalCa + '" name="detail_class_choose['+ tabindex +'][name]" class="form-control" value="'+ name +'">';
    strHtml += '   </td>';
    strHtml += '   <td>';
    strHtml += '      <input type="text" autocomplete="off" class="form-control date start_date" id="start_date_'+ tabindex +'"  value="'+ start_date +'" name="detail_class_choose['+ tabindex +'][start_date]" data-provide="datepicker" placeholder="' + globalStartTime + '" />';
    strHtml += '   </td>';
    strHtml += '   <td>'
    strHtml += '      <input type="text" autocomplete="off" class="form-control date end_date" id="end_date_'+ tabindex +'" value="'+ end_date +'" name="detail_class_choose['+ tabindex +'][end_date]" data-provide="datepicker" placeholder="' + globalEndTime + '" />';
    strHtml += '   </td>';
    if (globalIsShow) {
        strHtml += '   <td></td>';
    } else {
        strHtml += '   <td>';
        strHtml += '      <a class="btn btn-danger btn-remove" id="btn-remove_'+ tabindex +'">Remove <i class="glyphicon glyphicon-remove"></i></a>';
        strHtml += '   </td>';
    }
    strHtml += '</tr>';

    return strHtml;
}

// total time
function totalTime()
{
    var total = 0;
    var tblCtr = $('.tblDetailInput');
    tblCtr.find('tr td input.end_date').each(function () {
        var startHour = $(this).parents('tr').find('.start_date').val() == '' ? 0 :  moment($(this).parents('tr').find('.start_date').val(), "YYYY-MM-DD H:mm");
        var endHour = $(this).val() == '' ? 0 : moment($(this).val(), "YYYY-MM-DD H:mm");
        if (endHour != 0 && startHour != 0) {
            var duration = moment.duration(endHour.diff(startHour));
            var hours = duration.asHours();
            total = total + hours;
        }
    });

    if (total > 0){
        $('.tranning_hour').val(Math.ceil(total));
        $('#tranning_hour-error').addClass('hidden');
    }
}

// show loading
function loadding()
{
    $('#course_id').addClass('hidden');
    $('#update_cate_loading').removeClass('hidden');
    $('#class_id').addClass('hidden');
    $('#update_class_loading').removeClass('hidden');
    $('#update_detail_loading').removeClass('hidden');
    $('.detail-class').addClass('hidden');
}

// hidden loading
function hiddenLoading()
{
    $('#update_cate_loading').addClass('hidden');
    $('#course_id').removeClass('hidden');
    $('#update_class_loading').addClass('hidden');
    $('#class_id').removeClass('hidden');
    $('#update_detail_loading').addClass('hidden');
    $('.detail-class').removeClass('hidden');
}
