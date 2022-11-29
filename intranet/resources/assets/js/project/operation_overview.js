$(document).ready(function () {
    loadingIcon(true);
    loadingIconStart(true);
    $('.input-select-team-member').val(gloabalTeamId);
    $('#activity_month_to_overview').trigger('change');
});

var globalMonthFrom, globalMonthto, globalEmployeePoints = null;
var globalEmployeeMaternity = {};

const TYPE_OSDC = 1;
const TYPE_BASE = 2;
const TYPE_ONSITE = 5;

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content'),
    },
});

$('#activity_month_from_overview').datepicker({
    format: 'yyyy-mm',
    viewMode: "months",
    minViewMode: "months",
    // endDate: '0y',
    autoclose: true,
});

$('#activity_month_to_overview').datepicker({
    format: 'yyyy-mm',
    viewMode: "months",
    minViewMode: "months",
    // endDate: '0y',
    autoclose: true,
});

$("#activity_month_from_overview").datepicker("setDate", gloabalStartDateFilter);

$("#activity_month_to_overview").datepicker("setDate", gloabalEndDateFilter);

// change date select event
$('#activity_month_from_overview, #activity_month_to_overview').change(function () {
    if (globalMonthFrom !== $('#activity_month_from_overview').val() || globalMonthto !== $('#activity_month_to_overview').val()) {
        globalMonthFrom = $('#activity_month_from_overview').val();
        globalMonthto = $('#activity_month_to_overview').val();
        if (globalMonthFrom > globalMonthto) {
            errorDataOverview(globalMessage['Montherror']);
        } else {
            loadDataOverview();
        }
    }
});

// permission event
$('.input-select-team-member').change(function () {
    loadDataOverview();
});

function checkValidContractDateEmployee(comparedMonth, employeeData) {
    var months = Object.keys(employeeData);
    var flag = false;
    for (var i = 0; i < months.length; i++) {
        if (comparedMonth >= months[i]) flag = true;
    }
    return flag;
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

//get string calculated month
function getStringCalculatedMonth(stringMonth, monthIndex) {
    var dateFormatMonth = moment(stringMonth);

    return moment(dateFormatMonth).add(monthIndex, 'months').format('Y-MM');
}

function getPointForMaternity(currentMonth, employeeId) {
    if (!globalEmployeeMaternity.hasOwnProperty(employeeId)) {
        return 0;
    }

    for (var index in globalEmployeeMaternity[employeeId]) {
        var currentData = globalEmployeeMaternity[employeeId][index];
        if (currentMonth === currentData['leave_start']) {
            return currentData['percent_not_working_from_leave_start']
        } else if (currentMonth === currentData['leave_end']) {
            return currentData['percent_not_working_until_leave_end']
        } else if (currentMonth > currentData['leave_start'] && currentMonth < currentData['leave_end']) {
            return 1;
        }
    }

    return 0;
}

function getTotalMemberPointEachMonth(responseData) {
    globalEmployeePoints = responseData.member_points;
    globalEmployeeMaternity = responseData.maternity_data;
    var scopeMonthFrom = globalMonthFrom;
    var result = {};
    while (scopeMonthFrom <= $('#activity_month_to_overview').val()) {
        var totalPoint = 0;
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
                        if (employeeInforMappingMonth['join_date'] === scopeMonthFrom) {
                            point = employeeInforMappingMonth['actual_point_first_month'];
                        }
                        if (employeeInforMappingMonth['leave_date'] === scopeMonthFrom) {
                            point = employeeInforMappingMonth['actual_point_last_month'];
                        }
                        var detailEmployeeId = employeeId.split('-')[1];
                        var percentNotWorking = getPointForMaternity(scopeMonthFrom, detailEmployeeId);
                        point = point - percentNotWorking*point;

                        totalPoint += parseFloat(point >= 0 ? point : 0);
                    }
                }

            }
        }
        result[scopeMonthFrom] = totalPoint;
        scopeMonthFrom = getStringCalculatedMonth(scopeMonthFrom, 1);
    }

    return result;
}

function getDetailTotalPointEachMonth(responseData) {
    var totalMemeberPointEachMonth = getTotalMemberPointEachMonth(responseData);
    var totalProjectPointEachMonth = responseData.project_points;
    var result = [];
    var osdc, base, onsite;
    for (var month in totalMemeberPointEachMonth) {
        osdc = totalProjectPointEachMonth.hasOwnProperty(month) && totalProjectPointEachMonth[month].hasOwnProperty(TYPE_OSDC) ? totalProjectPointEachMonth[month][TYPE_OSDC]['cost'] : 0;
        base = totalProjectPointEachMonth.hasOwnProperty(month) && totalProjectPointEachMonth[month].hasOwnProperty(TYPE_BASE) ? totalProjectPointEachMonth[month][TYPE_BASE]['cost'] : 0;
        onsite = totalProjectPointEachMonth.hasOwnProperty(month) && totalProjectPointEachMonth[month].hasOwnProperty(TYPE_ONSITE) ? totalProjectPointEachMonth[month][TYPE_ONSITE]['cost'] : 0;
        result.push({
            'month' : month,
            'members' : totalMemeberPointEachMonth[month],
            'osdc' : osdc,
            'base' : base,
            'onsite' : onsite,
            'project' : parseFloat(osdc) + parseFloat(base) + parseFloat(onsite)
        })
    }

    return result;
}

//get data
function getDataOverview($data) {
    $('#messageBoxOverview').empty();
    $('.message-overview').html('');
    $('.table-overview-responsive').empty();
    $data = getDetailTotalPointEachMonth($data);
    var strHtml = '';
    var strHtmlFirst = '';
    strHtmlFirst += '<table class="dataTable table-bordered table-hover table-grid-data not-padding-th dataTable-project table-overview-first">';
    strHtmlFirst += '<thead>';
    strHtmlFirst += '<tr>';
    strHtmlFirst += '<th class="head-month-first scrips-overview" colspan="3">' + globalHeader['Month'] + '</th>';
    strHtmlFirst += '</tr>';
    strHtmlFirst += '<tr>';
    strHtmlFirst += '<th class="head-month-first" colspan="3">' + globalHeader['Number of human actual'] + '</th>';
    strHtmlFirst += '</tr>';
    strHtmlFirst += '<tr>';
    strHtmlFirst += '<th class="head-month-first" colspan="3">' + globalHeader['Work effort'] + '</th>';
    strHtmlFirst += '</tr>';
    strHtmlFirst += '<tr>';
    strHtmlFirst += '<th class="head-month-first" colspan="3">' + globalHeader['OSDC'] + '</th>';
    strHtmlFirst += '</tr>';
    strHtmlFirst += '<tr>';
    strHtmlFirst += '<th class="head-month-first" colspan="3">' + globalHeader['Project Base'] + '</th>';
    strHtmlFirst += '</tr>';
    strHtmlFirst += '<tr>';
    strHtmlFirst += '<th class="head-month-first" colspan="3">' + globalHeader['Onsite'] + '</th>';
    strHtmlFirst += '</tr>';
    strHtmlFirst += '<tr>';
    strHtmlFirst += '<th class="head-month-first" colspan="3">' + globalHeader['Busy rate'] + '</th>';
    strHtmlFirst += '</tr>';
    strHtmlFirst += '</thead>';
    strHtmlFirst += '</table>';
    $('#dataOverview').append(strHtmlFirst);

    if ($data.length > 0) {
        var dataMonthForChart = [];
        var dataMembersForChart = [];
        var dataProjectForChart = [];
        var dataBusiRateForChart = [];
        for (var indexOfTotal = 0; indexOfTotal < $data.length; indexOfTotal++) {
            var classMonth = '';
            var tableNow = '';
            if ($data[indexOfTotal]['month'] === globalCurrentMonth) {
                classMonth = 'class="month-now"';
                tableNow = 'table-now';
            }
            dataMonthForChart.push($data[indexOfTotal]['month']);
            dataMembersForChart.push($data[indexOfTotal]['members']);
            dataProjectForChart.push($data[indexOfTotal]['project']);
            if (!$data[indexOfTotal]['members']) {
                var dataRate = 0;
            } else {
                var dataRate = Math.round($data[indexOfTotal]['project'] / $data[indexOfTotal]['members'] * 100);
            }
            dataBusiRateForChart.push(dataRate);
            dataRate += '%';
            strHtml = '<table class="dataTable table-bordered table-hover table-grid-data not-padding-th dataTable-project table-overview ' + tableNow + '">';
            strHtml += '<thead>';
            strHtml += '<tr ' + classMonth + '>';
            strHtml += '<th class="head-month scrips-overview" colspan="3">' + $data[indexOfTotal]['month'] + '</th>';
            strHtml += '</tr>';
            strHtml += '<tr>';
            strHtml += '<th class="head-month number-format" colspan="3">' + $data[indexOfTotal]['members'].toFixed(2) + '</th>';
            strHtml += '</tr>';
            strHtml += '<tr>';
            strHtml += '<th class="head-month number-format" colspan="3">' + (+$data[indexOfTotal]['project']).toFixed(2) + '</th>';
            strHtml += '</tr>';
            strHtml += '<tr>';
            strHtml += '<th class="head-month number-format" colspan="3">' + (+$data[indexOfTotal]['osdc']).toFixed(2) + '</th>';
            strHtml += '</tr>';
            strHtml += '<tr>';
            strHtml += '<th class="head-month number-format" colspan="3">' + (+$data[indexOfTotal]['base']).toFixed(2) + '</th>';
            strHtml += '</tr>';
            strHtml += '<tr>';
            strHtml += '<th class="head-month number-format" colspan="3">' + (+$data[indexOfTotal]['onsite']).toFixed(2) + '</th>';
            strHtml += '</tr>';
            strHtml += '<tr>';
            strHtml += '<th class="head-month number-format" colspan="3">' + dataRate + '</th>';
            strHtml += '</tr>';
            strHtml += '</thead>';
            strHtml += '</table>';
            $('#dataOverview').append(strHtml);
        }
        var ultilWidth = $('.tab-content').width();
        var numItems = parseInt($('.table-overview').css('width').replace("px", "")) + 2;
        var totalWidth = (numItems * $('.table-overview').length) + parseInt($('.table-overview-first').css('width').replace("px", "")) + 2;
        if (totalWidth > ultilWidth) {
            $('.total-responsive-overview').css({'width': ultilWidth, 'overflow-x': 'scroll'});
        } else {
            $('.total-responsive-overview').css({'width': totalWidth, 'overflow-x': 'hidden'});
        }
        $('#dataOverview').css('width', totalWidth);
        // set chart
        resetCanvas();
        accessCanvas(dataMembersForChart, dataProjectForChart, dataBusiRateForChart, dataMonthForChart);
        formatNumber();
    }
    loadingIcon(false);
    loadingIconStart(false);
}

// destroy chart
function resetCanvas() {
    $('#graph-container').empty();
    $('#graph-container').append('<hr><canvas id="results-graph"><canvas>');
    var canvas = document.querySelector('#results-graph');
    canvas.getContext('2d');
}

// generate chart
function accessCanvas(dataMembersForChart, dataProjectForChart, dataBusiRateForChart, dataMonthForChart) {
    var ctx = document.getElementById('results-graph').getContext('2d');
    return new Chart(ctx, {
        type: 'bar',
        data: {
            datasets: [{
                label: 'Members',
                data: dataMembersForChart,
                order: 1,
                backgroundColor: 'rgba(255, 159, 64, 0.2)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1,
                yAxisID: 'y-axis-1',
                pointStyle: 'rect'
            }, {
                label: 'Projects',
                data: dataProjectForChart,
                order: 1,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1,
                yAxisID: 'y-axis-1',
                pointStyle: 'rect'
            }, {
                label: 'Busy Rate',
                data: dataBusiRateForChart,
                type: 'line',
                order: 2,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                yAxisID: 'y-axis-2',
                pointStyle: 'line'
            }],
            labels: dataMonthForChart,
        },
        options: {
            legend: {
                labels: {
                    usePointStyle: true
                }
            },
            showAllTooltips: true,
            responsive: true,
            maintainAspectRatio: false,
            tooltips: {
                mode: 'label',
            },
            elements: {
                line: {
                    fill: false,
                },
            },
            scales: {
                yAxes: [
                    {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        id: 'y-axis-1',
                        gridLines: {
                            display: true,
                        },
                        labels: {
                            show: true,
                        },
                        ticks: {
                            beginAtZero: true,  // minimum value will be 0.
                            callback: function(value, index, values) {
                                return value + " MM";
                            },
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Total MM (MM)',
                            padding: 15,
                            fontFamily: 'Arial'
                        }
                    },
                    {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        id: 'y-axis-2',
                        gridLines: {
                            display: true,
                        },
                        labels: {
                            show: true,
                        },
                        ticks: {
                            beginAtZero: true,
                            callback: function(value, index, values) {
                                return value + " %";
                            },
                            suggestedMax: 100// minimum value will be 0.
                        },
                        scaleLabel: {
                            display: true,
                            padding: 15,
                            labelString: 'Busy Rate (%)',
                            fontFamily: 'Arial'
                        }
                    },
                ],
            },
        },
    });
}

// load data response
function loadDataOverview() {

    // process loading icon
    loadingIcon(true);

    var monthFrom = $('#activity_month_from_overview').val();
    var monthTo = $('#activity_month_to_overview').val();
    var selectValue;
    if ($('.input-select-team-member').length) {
        selectValue = $('.input-select-team-member').val();
    } else {
        selectValue = $('#selected-team').data('id');
    }
    // get data
    $.ajax({
        url: globalGetOperationUrl,
        type: 'post',
        dataType: 'json',
        data: {
            monthFrom: monthFrom,
            monthTo: monthTo,
            teamId: selectValue,
            typeViewMain: globalTypeViewMain,
        },
        success: function (data) {
            if (data.data) {
                getDataOverview(data.data);
            } else {
                errorDataOverview(globalMessage['No results found']);
            }
        },
    });
}

// process while data error
function errorDataOverview(htmlContent) {
    $('#messageBoxOverview').empty();
    var strHtml = '';
    strHtml += '<div class="alert alert-error alert-dismissible">';
    strHtml += '<button type="button" class="close" data-dismiss="alert">×</button>';
    strHtml += '<strong class="message-overview"></strong>';
    strHtml += '</div>';
    $('#messageBoxOverview').append(strHtml);
    $('.message-overview').html(htmlContent);
    $('.total-responsive-overview').css({'width': '0px', 'overflow-x': 'hidden'});
    $('.table-overview-responsive').empty();
    resetCanvas();
}

// process icon loading
function loadingIcon($check) {
    if ($check) {
        $('.loading-icon-overview').removeClass('hidden');
        $('.table-overview-responsive, .graph-container').addClass('hidden');
        $('.total-responsive-overview').css({'width': '0px', 'height': '683px', 'overflow-x': 'hidden'});
    } else {
        $('.loading-icon-overview').addClass('hidden');
        $('.table-overview-responsive, .graph-container').removeClass('hidden');
        $('.total-responsive-overview').css({'height': 'auto'});
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

function formatNumber() {
    $('body .number-format').each(function ($index) {
        var num = $(this).text().trim();
        num = num.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
        $(this).text(num);
    })
}