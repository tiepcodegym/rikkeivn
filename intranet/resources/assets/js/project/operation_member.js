var globalEmployeePoints = {};
var globalEmployeeMaternity = {};
var globalMonthFrom = null;
var globalMonthTo = null;

$(document).ready(function () {
    loadingIcon(true);
    loadingIconStart(true);

    $('.input-select-team-member').val(gloabalTeamId);
    $('#activity_month_to_members').trigger('change');
});


$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
    },
});

$('#activity_month_from_members').datepicker({
    format: 'yyyy-mm',
    viewMode: "months",
    minViewMode: "months",
    // endDate: '0y',
    autoclose: true,
});

$('#activity_month_to_members').datepicker({
    format: 'yyyy-mm',
    viewMode: "months",
    minViewMode: "months",
    // endDate: '0y',
    autoclose: true,
});

$("#activity_month_from_members").datepicker("setDate", gloabalStartDateFilter);

$("#activity_month_to_members").datepicker("setDate", gloabalEndDateFilter);

// change date select event
$('#activity_month_from_members, #activity_month_to_members').change(function () {
    globalMonthFrom = $('#activity_month_from_members').val();
    globalMonthTo = $('#activity_month_to_members').val();
    if (globalMonthFrom > globalMonthTo) {
        errorData(globalMessage['Montherror']);
    } else {
        loadData();
    }
});

// permission event
$('.input-select-team-member').change(function () {
    loadData();
});

//get string calculated month
function getStringCalculatedMonth(stringMonth, monthIndex) {
    var dateFormatMonth = moment(stringMonth);

    return moment(dateFormatMonth).add(monthIndex, 'months').format('Y-MM');
}

//get data mapping month
function getDataMappingMonth(employeeId, startTeam,  month) {
    if (!globalEmployeePoints[employeeId][startTeam][month]) {
        if (month > '2012-01') {
            var arrayKeyContractStartDates = Object.keys(globalEmployeePoints[employeeId][startTeam]);
            arrayKeyContractStartDates = arrayKeyContractStartDates.sort();
            arrayKeyContractStartDates = arrayKeyContractStartDates.reverse();

            for (var indexContractStartDate in arrayKeyContractStartDates) {
                if (month > arrayKeyContractStartDates[indexContractStartDate]) {
                    var result = globalEmployeePoints[employeeId][startTeam][arrayKeyContractStartDates[indexContractStartDate]];
                    if (result && result['leave_date'] && month > result['leave_date']) {
                        return null;
                    }

                    return result;
                }
            }
        }
    }

    return globalEmployeePoints[employeeId][startTeam][month];
}

// check valid data
function checkValidContractDateEmployee(comparedMonth, employeeData) {
    var months = Object.keys(employeeData);
    var flag = false;
    for (var i = 0; i < months.length; i++) {
        if (comparedMonth >= months[i]) flag = true;
    }
    return flag;
}

function getPointForMaternity(currentMonth, employeeId) {
    if (!globalEmployeeMaternity.hasOwnProperty(employeeId)) {
        return 0;
    }

    var percent = 0;
    for (var index in globalEmployeeMaternity[employeeId]) {
        var currentData = globalEmployeeMaternity[employeeId][index];
        if (currentMonth === currentData['leave_start']) {
            percent += currentData['percent_not_working_from_leave_start'];
        } else if (currentMonth === currentData['leave_end']) {
            percent += currentData['percent_not_working_until_leave_end'];
        } else if (currentMonth > currentData['leave_start'] && currentMonth < currentData['leave_end']) {
            return 1;
        }
    }

    return percent > 1 ? 1 : percent;
}

// get data
function getData() {
    $('#messageBox').empty();
    $('.message').html('');
    $('.table-responsive').empty();
    var strHtml = '';
    var scopeMonthFrom = globalMonthFrom;
    var dataScroll = 'hidden';
    while (scopeMonthFrom <= globalMonthTo) {
        var classMonth = '';
        var tableNow = '';
        var textNow = '';
        if (scopeMonthFrom === globalCurrentMonth) {
            classMonth = 'class="month-now"';
            tableNow = 'table-now';
            textNow = 'text-now';
        }
        strHtml = '<table class="dataTable table-bordered table-hover table-grid-data not-padding-th dataTable-project table-members table ' + tableNow + '">';
        strHtml += '<thead class="table-header">';
        strHtml += '<tr ' + classMonth + '>';
        strHtml += '<th class="head-month-first" colspan="4" data-order="name">' + scopeMonthFrom;
        strHtml += '</tr>';
        strHtml += '<tr>';
        strHtml += '<th class="head-month ' + textNow + '" colspan="4">' + globalHeader['Reward Total'] + ': ' + '$$totalPoint$$';
        strHtml += '</tr>';
        strHtml += '<tr>';
        strHtml += '<th class="header__item col-no scrips-no ' + textNow + '"><a class="order filter__link filter__link--number" href="#">' + globalHeader['No'] + '</a>';
        strHtml += '<th class="header__item col-name scrips-name ' + textNow + '"><a  class="employee_name filter__link" href="#">' + globalHeader['Member'] + '</a>';
        strHtml += '<th class="header__item col-account-name scrips-account-name' + textNow + '"><a  class="account_name filter__link" href="#">' + globalHeader['Account Name'] + '</a>';
        strHtml += '<th class="header__item col-point scrips-point ' + textNow + '"><a class="point filter__link filter__link--number" href="#">' + globalHeader['Point'] + '</a>';
        strHtml += '</tr></thead><tbody class="table-content">';
        var totalPoint = 0;
        var indexOfMember = 1;

        for (var employeeId in globalEmployeePoints) {
            if (globalEmployeePoints.hasOwnProperty(employeeId)) {
                for (var startTeam in globalEmployeePoints[employeeId]) {
                    var listTimeline = globalEmployeePoints[employeeId][startTeam];
                    if (!checkValidContractDateEmployee(scopeMonthFrom, listTimeline)) {
                        continue;
                    }

                    var employeeInforMappingMonth = getDataMappingMonth(employeeId, startTeam, scopeMonthFrom);
                    if (employeeInforMappingMonth) {
                        var point = employeeInforMappingMonth['point'];
                        if (employeeInforMappingMonth['join_date'] > scopeMonthFrom) continue;
                        if (employeeInforMappingMonth['leave_date'] < scopeMonthFrom) continue;
                        if (employeeInforMappingMonth['join_date'] === scopeMonthFrom) {
                            point = employeeInforMappingMonth['actual_point_first_month'];
                        }
                        if (employeeInforMappingMonth['leave_date'] === scopeMonthFrom) {
                            point = employeeInforMappingMonth['actual_point_last_month'];
                        }
                        var detailEmployeeId = employeeId.split('-')[1];
                        var detailTeamId = employeeId.split('-')[0];
                        var percentNotWorking = getPointForMaternity(scopeMonthFrom, detailEmployeeId);
                        point = Math.round((point - percentNotWorking * point) * 100) / 100;

                        totalPoint += parseFloat(point >= 0 ? point : 0);

                        strHtml += '<tr class="table-row operation_member_detail" data-toggle="tooltip" ' +
                            'data-container="body" ' +
                            'data-html="true"' +
                            'data-original-title="' + "<ul class='operation_member_tooltip'>" +
                                '<li>' + gloabalLocale['employee_code'] + ': ' + employeeInforMappingMonth['employee_code'] + '</li>' +
                                '<li>' + gloabalLocale['email'] + ': ' + employeeInforMappingMonth['email'] + '</li>' +
                                '<li>' + gloabalLocale['team'] + ': ' + employeeInforMappingMonth['team_name'] + '</li>' +
                            '</ul>' + '">';
                        strHtml += '<td class="table-data col-no" >' + indexOfMember++;
                        strHtml += '<td class="table-data col-name"' +
                            'data-member-id="' + detailEmployeeId + '"' +
                            'data-team-id="' + detailTeamId + '"' +

                            'data-month="' + scopeMonthFrom + '" >' +
                            employeeInforMappingMonth['name'];
                        strHtml += '<td class="table-data col-account-name" >' + employeeInforMappingMonth['account_name'];
                        strHtml += '<td class="table-data col-point">' + point;
                        strHtml += '</tr>';
                    }
                    if (indexOfMember > 15) {
                        dataScroll = 'scroll';
                    }
                }

            }
        }
        strHtml = strHtml.replace('$$totalPoint$$', parseFloat(totalPoint).toFixed(2));
        strHtml += '</tbody>';
        strHtml += '</table>';
        $('#countResponsive').append(strHtml);
        scopeMonthFrom = getStringCalculatedMonth(scopeMonthFrom, 1);
    }
    var ultilWidth = $('.tab-content').width();
    var numItems = parseInt($('.table-members').css('width').replace("px", "")) + 2;
    var totalWidth = numItems * $('.table-members').length;
    var scrollTop = $(window).scrollTop(),
        elementOffset = $('.total-responsive').offset().top,
        distance = (elementOffset - scrollTop);
    if (totalWidth > ultilWidth) {
        $('.total-responsive').css({
            'width': ultilWidth,
            'max-height': 'calc(100vh - ' + distance + 'px)',
            'overflow-x': 'scroll',
            // 'overflow-y': dataScroll,
        });
    } else {
        $('.total-responsive').css({
            'width': totalWidth + 16,
            'max-height': 'calc(100vh - ' + distance + 'px)',
            'overflow-x': 'hidden',
            // 'overflow-y': dataScroll,
        });
    }
    $('#countResponsive').css('width', totalWidth);
    loadingIcon(false);
    loadingIconStart(false);
    initActionSort();
}

function getParameterByName(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, '\\$&');
    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';

    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

// load data response
function loadData() {

    // process loading icon
    loadingIcon(true);

    var currentPage = getParameterByName('page') ? getParameterByName('page') : 1;
    var currentUrl = [location.protocol, '//', location.host, location.pathname].join('');
    var selectValue;
    if ($('.input-select-team-member').length) {
        selectValue = $('.input-select-team-member').val();
    } else {
        selectValue = $('#selected-team').data('id');
    }
    var parameter = {
        monthFrom: globalMonthFrom,
        monthTo: globalMonthTo,
        page: currentPage,
        url: currentUrl,
        teamId: selectValue,
        typeViewMain: globalTypeViewMain,
    };

    // get data
    $.ajax({
        url: globalGetOperationUrl,
        type: 'post',
        dataType: 'json',
        data: parameter,
        success: function (data) {
            if (data) {
                globalEmployeePoints = data['employee_points'];
                globalEmployeeMaternity = data['maternity_data'];
                getData();
                $('#countResponsive table tbody').css({
                    'overflow': 'auto',
                    'display': 'block',
                    'height': ($('.total-responsive').outerHeight() - $('thead').outerHeight() - 20) + 'px'
                });


            } else {
                errorData(globalMessage['No results found']);
            }
        },
    });
}

// process while data error
function errorData(htmlContent) {
    $('#messageBox').empty();
    var strHtml = '';
    strHtml += '<div class="alert alert-error alert-dismissible">';
    strHtml += '<button type="button" class="close" data-dismiss="alert">×</button>';
    strHtml += '<strong class="message"></strong>';
    strHtml += '</div>';
    $('#messageBox').append(strHtml);
    $('.message').html(htmlContent);
    $('.total-responsive').css({'width': '0px', 'overflow-x': 'hidden'});
    $('.table-responsive').empty();
}

// process icon loading
function loadingIcon($check) {
    if ($check) {
        $('.loading-icon').removeClass('hidden');
        $('.table-responsive').addClass('hidden');
        $('.total-responsive').css({
            'width': '100%',
            'max-height': '700px',
            'overflow-x': 'hidden',
            'overflow-y': 'hidden',
        });
    } else {
        $('.loading-icon').addClass('hidden');
        $('.table-responsive').removeClass('hidden');
    }
}

// process icon loading start page
function loadingIconStart($check) {
    if ($check) {
        $('.se-pre-con').show();
    } else {
        $('.se-pre-con').hide();
    }
}

function replaceTVKhongDau(alias) {
    var str = alias;
    str = str.toLowerCase();
    str = str.replace(/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ/g,"a");
    str = str.replace(/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ/g,"e");
    str = str.replace(/ì|í|ị|ỉ|ĩ/g,"i");
    str = str.replace(/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ/g,"o");
    str = str.replace(/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ/g,"u");
    str = str.replace(/ỳ|ý|ỵ|ỷ|ỹ/g,"y");
    str = str.replace(/đ/g,"d");
    str = str.replace(/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'|\"|\&|\#|\[|\]|~|\$|_|`|-|{|}|\||\\/g," ");
    str = str.replace(/ + /g," ");
    str = str.trim();

    return str;
}

function initActionSort() {
    var properties = [
        'order',
        'employee_name',
        'point',
        'account_name'
    ];

    $.each( properties, function( i, val ) {

        var orderClass = '';

        $('body').on('click', "." + val, (function(e) {
            e.preventDefault();
            var currentTable = $(e.target).closest('.table');

            $(currentTable).find('.filter__link.filter__link--active').not(this).removeClass('filter__link--active');
            $(this).toggleClass('filter__link--active');
            $(currentTable).find('.filter__link').removeClass('asc desc');

            if(orderClass === 'asc' || orderClass === '') {
                $(this).addClass('desc');
                orderClass = 'desc';
            } else {
                $(this).addClass('asc');
                orderClass = 'asc';
            }

            var parent = $(this).closest('.header__item');
            var index = $(currentTable).find(".header__item").index(parent);
            var $table = $(currentTable).find('.table-content');
            var rows = $table.find('.table-row').get();
            var isSelected = $(this).hasClass('filter__link--active');
            var isNumber = $(this).hasClass('filter__link--number');

            rows.sort(function(a, b){

                var x = ($(a).find('.table-data').eq(index).text().toLowerCase());
                var y = ($(b).find('.table-data').eq(index).text().toLowerCase());

                if(isNumber) {
                    if(isSelected) {
                        return (+y) - (+x);
                    } else {
                        return (+x) - (+y);
                    }

                } else {
                    x = replaceTVKhongDau(x);
                    y = replaceTVKhongDau(y);
                    if(isSelected) {
                        if(x > y) return -1;
                        if(x < y) return 1;
                        return 0;
                    } else {
                        if(x < y) return -1;
                        if(x > y) return 1;
                        return 0;
                    }
                }
            });

            $.each(rows, function(index,row) {
                $table.append(row);
            });

            return false;
        }));

    });
}