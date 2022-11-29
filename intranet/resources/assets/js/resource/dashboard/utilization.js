$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
    }
});
var token = $('meta[name="csrf-token"]').attr('content');

/*
 * Datepicker set
 */
var $startMonth = $('#start-month');
var $endMonth = $('#end-month');
var $buttonNext = $('.paginate_button.next');
var $buttonPrev = $('.paginate_button.previous');
var $buttonLast = $('.paginate_button.lastpage-page');
var $buttonFirst = $('.paginate_button.first-page');
var $inputPager = $('input[name=page]');
var $buttonReset = $('.btn-reset');
var $buttonFilter = $('.btn-filter');
$(document).ready(function(){
    $buttonNext.find('a').attr('href', 'javascript:void(0)');
    $buttonPrev.find('a').attr('href', 'javascript:void(0)');
    $buttonLast.find('a').attr('href', 'javascript:void(0)');
    $buttonFirst.find('a').attr('href', 'javascript:void(0)');
    $inputPager.parent().removeClass('form-pager');
    $startMonth.datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        endDate: $endMonth.val(),
        weekStart: 1,
        calendarWeeks: true,
    }).on('changeDate', function () {
        $endMonth.datepicker('setStartDate', $startMonth.val());
        loadData(1);
    });

    $endMonth.datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        startDate: $startMonth.val(),
        weekStart: 1,
        calendarWeeks: true,
    }).on('changeDate', function () {
        $startMonth.datepicker('setEndDate', $endMonth.val());
        loadData(1);
    });
    setEffort();
    setAverage();
    fixLeftCol();
    $('#program').multiselect();
    selectSearchReload();
    $('#proj_filter').select2();
});

/**
 * Resize window event
 */
jQuery(window).resize(function() {
    fixLeftCol();
});

/*
 * show popup display daily effort by week
 * @param {int} week
 * @param {int} year
 * @param {element} elem
 */
function showDetail(week, year, elem, viewMode) {
    var empId = $(elem).data('empid'); 
    var empName = $(elem).data('empname'); 
    var leaveDate = $(elem).data('leave'); 
    var joinDate = $(elem).data('join'); 
    $(".apply-click-modal").show();
    $.ajax({
        url: urlDetail,
        type: 'post',
        dataType: 'HTML',
        data: {
            week:week, 
            year:year, 
            empId: empId, 
            empName: empName, 
            leaveDate: leaveDate,
            joinDate: joinDate,
            viewMode: viewMode,
        },
        success: function (data) {
            $('#modal-days').modal('show');
            $('#modal-days .modal-body').html(data);
            fixLeftColWeekDetail();
        },
        error: function () {
            alert('ajax fail to fetch data');
        },
        complete: function () {
            $(".apply-click-modal").hide();
        },
    });
}

/*
 * Set effort
 */
function setEffort() {
    $('table.effortTable td.effort-container ').each(function () {
        var total = 0;
        $(this).find('.effort').each(function () {
            var effort = $.trim($(this).text());
            effort = effort.substring(0, effort.length - 1);
            total += parseFloat(effort);
        });
        if(isNaN(total)) {
            //$(this).prepend("<p>Σ <span class='total-effort'>0</span>%</p>");
        } else {
            $(this).prepend("<div><span class='total-effort'>" + rounding(total, 2) + "</span>%</div>");
        }
        //Set bgColor
        setBg($(this), total);

        //Remove effort is 0%
        if (total == 0 || isNaN(total)) {
            if (total == 0) {
                $(this).html("<div><span class='total-effort'>0</span>%</div>");
            }
            $(this).removeAttr('onclick');
            $(this).css('cursor', 'auto');
        } else {
            $(this).find('.effort').each(function () {
                var effort = $.trim($(this).text());
                effort = effort.substring(0, effort.length - 1);
            });
        }
        
        //merge effort same project
        var stt = 0;
        var arrIds = [];
        var arrIdsWithKey= [];
        $(this).find('.pj-info').each(function () { 
            $(this).attr('row', stt);
            var dataId = $(this).attr('data-id');
            if ($.inArray(dataId, arrIds) == -1) {
                arrIds.push(dataId);
                arrIdsWithKey[dataId] = stt;
            } else {
                var effortTemp = $(this).find('.effort').text();
                effortTemp = effortTemp.substring(0, effortTemp.length - 1);
                var row = arrIdsWithKey[dataId];
                var EffortElem = $(this).parent('.effort-container').find('.pj-info[data-id='+dataId+'][row='+row+'] .effort');
                var effort = EffortElem.text();
                effort = effort.substring(0, effort.length - 1);
                effort = parseFloat(effort) + parseFloat(effortTemp);
                EffortElem.text(rounding(effort) + '%', 2);
                $(this).next('br').remove();
                $(this).remove();
            }
            stt++;
        });
        
        $(this).find('.pj-info').each(function () { 
            $(this).closest('td').find('.tooltip').append('<p>'+$(this).find('.effort').text()+ ' ' + $(this).find('.pj-name').text() +'</p>');
        });
    });
}

/**
 * Set average every column
 */
function setAverage() {
    $('tr.result td.average-effort').each(function() { 
        var total = 0;
        var n = $(this).data('child');
        var count = 0;
         $("td[data-child="+n+"]").each(function(){
             $(this).find('.total-effort').each(function(){
                count++;
                var effort = $.trim($(this).text());
                total += parseFloat(effort);
            });
         });
         var tdAvg = $("tr.result td[data-child="+n+"]");
         var tdTotal = $("tr.result-total td[data-child="+n+"]");
         var average = rounding(total/count, 2);
         var total = rounding(total, 2);
         tdAvg.html(average + '%');
         tdTotal.html(total + '%');
         setBg(tdAvg, average);
    });
}

/**
 * Set background by effort
 * @param element elem
 * @param int effort
 */
function setBg(elem, effort) {
    if (effort == 0) {
        elem.css('background', bgWhite);
    } else if (effort <= effortGreenMin && effort > 0) {
        elem.css('background', bgYellow);
    } else if (effort > effortGreenMin && effort <= effortGreenMax) {
        elem.css('background', bgGreen);
    } else if (effort > effortGreenMax) {
        elem.css('background', bgRed);
    } 
}

// Wait for window load
$(window).load(function() {
    // Animate loader off screen
    $(".se-pre-con").fadeOut("slow");;
});

/**
 * Fix height column Account
 */
function fixLeftCol() {
    var effortHeightHead = $('.effortTable thead').height();
    $('.accountTable thead tr').height(effortHeightHead);
    $('.accountTable tbody tr').each(function() {
        var row = $(this).attr('row');
        var accountHeight = $(this).height();
        var effortHeight = $('.effortTable tbody tr[row='+row+']').height();
        if (effortHeight > accountHeight) {
            $(this).height(rounding(effortHeight, 0));
            $('.effortTable tbody tr[row='+row+']').height(rounding(effortHeight, 0))
        } else {
            $(this).height(rounding(accountHeight, 0));
            $('.effortTable tbody tr[row='+row+']').height(rounding(accountHeight, 0))
        }
    });
}

function fixLeftColWeekDetail() {
    var effortHeightHead = $('.tbl-effort thead').height();
    $('.tbl-proj-name thead tr').height(effortHeightHead);
    $('.tbl-effort tbody tr').each(function() {
        var row = $(this).attr('row');
        var accountHeight = $(this).height();
        var effortHeight = $('.tbl-proj-name tbody tr[row='+row+']').height();
        if (effortHeight > accountHeight) {
            $(this).height(rounding(effortHeight, 0));
            $('.tbl-proj-name tbody tr[row='+row+']').height(rounding(effortHeight, 0))
        } else {
            $(this).height(rounding(accountHeight, 0));
            $('.tbl-proj-name tbody tr[row='+row+']').height(rounding(accountHeight, 0))
        }
    });
}

var xhr = null;

/**
 * load data ajax
 * @param {int} page
 * @returns html
 */   
function loadData(page) {
    if(xhr != null) {
        xhr.abort();
    }
    var projId = $('#proj_filter').val();
    var projStatus = $('#status_filter').val();
    var programs = $('#program').val(); 
    if ($('#team_id').length > 0) {
        teamId = $('#team_id').val();
    } else {
        teamId = "";
    }
    if ($('#emp_id').val()) {
        empId = $('#emp_id').val();
        empText = $('#employee-autocomplete').val().trim();
    } else {
        empId = "";
        empText = "";
    }

    var startDate = $('#start-month').val();
    var endDate = $('#end-month').val();

    var limit = $('select[name=limit] option:selected').data('value');
    var $refresh = $buttonFilter.find('.fa-refresh');
    $refresh.removeClass('hidden');
    var $dataContainer = $('.table-data-container');
    var $loaderContainer = $('.loader-container');
    var $loader = $('.loader');
    $loaderContainer.removeClass('hidden');
    $loader.removeClass('hidden');
    $('html, body').animate({
        scrollTop: $('body').offset().top
    }, 'slow');
    xhr = $.ajax({
        url: urlFilter,
        type: 'post',
        dataType: 'html',
        timeout: 30000,
        data: {
            _token: token,
            projId : projId, 
            projStatus: projStatus,
            programs: programs,
            teamId: teamId,
            startDate: startDate,
            endDate: endDate,
            empId: empId,
            empText: empText,
            limit: limit,
            page: page,
            viewMode: $('.btn-viewmode').find('.btn[data-selected=true]').data('value'),
            effort: $('#effort_filter').val(),
        },
        beforeSend : function() {  
            
        },
        success: function (html) {
            $dataContainer.html(html);
            setEffort();
            setAverage();
            fixLeftCol();
            $buttonLast.find('a').attr('data-page', totalPage );
            $inputPager.val(page);
            var nextPage = parseInt(page) + 1;
            var prevPage = parseInt(page) - 1;
            $buttonPrev.removeClass('disabled');
            $buttonNext.removeClass('disabled');
            $buttonLast.removeClass('disabled');
            $buttonFirst.removeClass('disabled');
            $buttonNext.find('a').attr('data-page', nextPage );
            $buttonPrev.find('a').attr('data-page', prevPage );
            if (totalPage < nextPage) {
                $buttonNext.addClass('disabled');
            } 
            if (prevPage == 0) { 
                $buttonPrev.addClass('disabled');
            } 
            if (page == 1) {
                $buttonFirst.addClass('disabled');
            }
            if (page == totalPage) {
                $buttonLast.addClass('disabled');
            }
            $('.data-pager-info span').html(pagerInfo);
            setHeightBody('.content-wrapper', 50);
        },
        error: function (x, t, m) {
            if (t == "timeout") {
                alert("got timeout");
            }
        },
        complete: function () {
            $refresh.addClass('hidden');
            $loaderContainer.addClass('hidden');
            $loader.addClass('hidden');
        },
    });
}

$buttonFilter.on('click', function(e) {
    loadData(1);
});

$('select[name=limit]').on('change', function(e) {
    e.stopPropagation();
    $buttonFilter.trigger('click');
});

$buttonNext.on('click', function(e) {
    e.stopPropagation();
    var page = $(this).find('a').attr('data-page');
    if (parseInt(page) <= parseInt(totalPage)) {
        loadData(page);
    }
});

$buttonPrev.on('click', function(e) {
    e.stopPropagation();
    var page = $(this).find('a').attr('data-page');
    if (parseInt(page) > 0) {
        loadData(page);
    } 
});

$buttonLast.on('click', function(e) {
    e.stopPropagation();
    var page = $(this).find('a').attr('data-page');
    var nextPage = $buttonNext.find('a').attr('data-page');
    if (parseInt(page) >= parseInt(nextPage)) {
        loadData(page);
    }
});

$buttonFirst.on('click', function(e) {
    e.stopPropagation();
    var page = $(this).find('a').attr('data-page');
    var PrevPage = $buttonPrev.find('a').attr('data-page');
    if (parseInt(page) <= parseInt(PrevPage)) {
        loadData(page);
    }
});

$inputPager.on('keyup', function(e) {
    e.stopPropagation();
    if (e.keyCode == 13) {
        var page = $(this).val().trim();
        if (parseInt(page) > parseInt(totalPage) || parseInt(page) < 1) {
            page = 1;
        }
        loadData(page);
    }
});

/**
 * Reset filter
 */
$buttonReset.on('click', function(e) {
    e.stopPropagation();
    $('#proj_filter').select2("val", "0");
    $('#status_filter').select2("val", "0");
    $('#emp_id').val("");
    $('#employee-autocomplete').val('');
    $("#program option:selected").prop('selected', false);
    $("#program").multiselect("refresh");
    $("#team_id option:selected").prop('selected', false);
    $("#team_id").multiselect("refresh");
    $('#start-month').datepicker("setDate", new Date(startDefault) );
    $('#end-month').datepicker("setDate", new Date(endDefault) );
    $('.btn-viewmode .btn').attr('data-selected', 'false').removeClass('bg-aqua');
    $('.btn-viewmode .btn[data-value=week]').attr('data-selected', 'true').addClass('bg-aqua');
    $('#effort_filter').select2("val", "0");
    var page = 1;
    loadData(page);
});

//set min height body
function setHeightBody(elem, height){
    $(elem).css('min-height', $(window).height() - $('.main-footer').outerHeight() - height);
}

$('#proj_filter, #status_filter, #effort_filter').change(function() {
   loadData(1); 
});

/**
 * onmouseover every td effort
 * 
 * @param {element} elem
 */
function over(elem) {
    var offset = $(elem).offset();
    var bottomOfVisibleWindow = $('.bottom-height').offset().top;
    var distanceToBottom = bottomOfVisibleWindow - offset.top;
    var tooltip = $(elem).find('.tooltip');
    var tooltipHeight = tooltip.height();
    if (tooltipHeight > distanceToBottom) {
        tooltip.css({'bottom' : distanceToBottom - 120, 'left' : offset.left, 'display' : 'block'});
    } else {
        tooltip.css({'left' : offset.left, 'display' : 'block'});
    }
}

/**
 * onmouseout every td effort
 * 
 * @param {element} elem
 */
function out(elem) {
    $(elem).find('.tooltip').css({'display' : 'none'});
}

/**
  * Show suggest employee filter
  */
 $("#employee-autocomplete").autocomplete({
    source: function (request, response) {
        $.ajax({
            url: urlSearchEmp,
            dataType: "json",
            data: {email: $("#employee-autocomplete").val()},
            success: function (data) {
                response( data );
            }
        });
    },
    minLength: 3,
    select: function( event, ui ) {
        $('#emp_id').val(parseInt(ui.item.data));
        $('#employee-autocomplete').val(ui.item.value);
        loadData(1);
    },
    focus: function( event, ui ) {
        $('#emp_id').val(ui.item.data);
    }
});

/**
 * Search employee by text 
 */
$('#employee-autocomplete').bind('keyup', function(e) {
    var code = e.keyCode || e.which;
    if (code != 38 && code != 40 && code != 13) {
        $('#emp_id').val($(this).val().trim());
    }
    if(code == 13) { 
        $(this).blur();
        loadData(1);
    }
});

$('.btn-viewmode .btn').on('click', function() {
    $(this).parent().find('.btn').removeClass('bg-aqua');
    $(this).parent().find('.btn').attr('data-selected', 'false');
    $(this).addClass('bg-aqua');
    $(this).attr('data-selected', 'true');
    loadData(1);
});

$(document).on('mouseup', 'li.checkbox-item', function () {
    var domInput = $(this).find('input');
    var id = domInput.val();
    var isChecked = !domInput.is(':checked');
    if (teamPath[id] && typeof teamPath[id].child !== "undefined") {
        var teamChild = teamPath[id].child;
        $('li.checkbox-item input').map(function (i, el) {
            if (teamChild.indexOf(parseInt($(el).val())) !== -1 && $(el).is(':checked') === !isChecked) {
                $(el).click();
            }
        });
    }
    setTimeout(function () {
        changeLabelSelected();
        changeTitle()
    }, 0);
});

$(document).ready(function () {
    changeLabelSelected();
});

function changeLabelSelected() {
    var checkedValue = $(".team-select-box option:selected");
    if (checkedValue.length === 1) {
        var string = $.trim(checkedValue.text());
        $(".team-select-box .multiselect-selected-text").text(string);
    }
}

function changeTitle() {
    var str = '';
    $(".team-select-box option:selected").each(function () {
        str += ', ' + $.trim($(this).text());
    });
    if (str.charAt(0) === ',')
        str = str.slice(2);
    $(".team-select-box .multiselect").prop('title', str)
}
