var $modalChangeColor = $('#modal-change-color');
var $bodyModal = $modalChangeColor.find('.modal-body');
var $form = $bodyModal.children('form');
var $inputColor = $form.find('input[name="color"]');
var $btnSave = $modalChangeColor.find('.btn-save');
var _token = $form.find('input[name="_token"]').val();
var $channelId = $form.find('input[name="channelId"]');

$('.column-choice').select2();
$inputColor.colorpicker({
    format: 'hex',
}).focusin(function () {
    $bodyModal.css('min-height', '220px');
}).focusout(function () {
    setTimeout(function () {
        $bodyModal.css('min-height', '');
    }, 100);
});
RKfuncion.general.initDateTimePicker();
$('.input-datepicker').on('dp.change', function () {
    $('.btn-search-filter').click();
});

$('input[name="display_type"]').on('click', function () {
    var tabDataTable = $('.tab-data-table'),
        tabChart = $('.tab-chart');
    if ($(this).val() === 'chart') {
        tabDataTable.addClass('hidden');
        tabChart.removeClass('hidden');
    } else {
        tabDataTable.removeClass('hidden');
        tabChart.addClass('hidden');
    }
});

if (!Array.isArray(recruiters)) {
    recruiters = Object.keys(recruiters).map(function (key) {
        return recruiters[key];
    });
}
var recruiterNames = recruiters.map(function (recruiter) {
    return recruiter.name;
});

var dataChartInterviewFail = [],
    dataChartInterviewPass = [],
    dataChartInterviewOffer = [],
    bgColorDefault = '#ffff00';
Object.keys(groupChannels).map(function (key, index) {
    dataChartInterviewFail[index] = {
        label: groupChannels[key].name,
        data: [],
        backgroundColor: groupChannels[key].color || bgColorDefault,
    };
    dataChartInterviewPass[index] = {
        label: groupChannels[key].name,
        data: [],
        backgroundColor: groupChannels[key].color || bgColorDefault,
    };
    dataChartInterviewOffer[index] = {
        label: groupChannels[key].name,
        data: [],
        backgroundColor: groupChannels[key].color || bgColorDefault,
    };
    recruiters.map(function (recruit, idx) {
        var total = groupChannels[key].recruiters[recruit.email].total,
            pass = groupChannels[key].recruiters[recruit.email].pass,
            fail = groupChannels[key].recruiters[recruit.email].fail,
            offer = groupChannels[key].recruiters[recruit.email].offer;
        total = total ? total : 1;
        dataChartInterviewPass[index].data[idx] = (pass / total * 100).toFixed(2);
        dataChartInterviewFail[index].data[idx] = (fail / total * 100).toFixed(2);
        dataChartInterviewOffer[index].data[idx] = (offer / total * 100).toFixed(2);
    });
});

var ctxFail = document.getElementById('candidateFail').getContext('2d'),
    ctxPass = document.getElementById('candidatePass').getContext('2d'),
    ctxOffer = document.getElementById('candidateOffer').getContext('2d');

var chartCandidateFail = new Chart(ctxFail, {
    type: 'bar',
    data: {
        labels: recruiterNames,
        datasets: dataChartInterviewFail,
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    min: 0,
                    max: 100,
                    callback: function (value) {
                        return value.toFixed(0) + '%';
                    },
                },
            }],
            xAxes: [{
                ticks: {
                    autoSkip: false,
                }
            }],
        },
        title: {
            display: true,
            text: txtQuantityCandidateFailInterview,
            fontSize: 16
        },
        maintainAspectRatio: false,
        legend: {
            onClick: function (event, legendItem) {
                handleClickChart(legendItem);
            }
        },
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    return dataset.label + ": " + tooltipItem.yLabel + "%";
                }
            }
        }
    }
});
var chartCandidatePass = new Chart(ctxPass, {
    type: 'bar',
    data: {
        labels: recruiterNames,
        datasets: dataChartInterviewPass,
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    min: 0,
                    max: 100,
                    callback: function (value) {
                        return value.toFixed(0) + '%';
                    },
                },
            }],
            xAxes: [{
                ticks: {
                    autoSkip: false,
                }
            }],
        },
        title: {
            display: true,
            text: txtQuantityCandidatePassInterview,
            fontSize: 16
        },
        maintainAspectRatio: false,
        legend: {
            onClick: function (event, legendItem) {
                handleClickChart(legendItem);
            }
        },
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    return dataset.label + ": " + tooltipItem.yLabel + "%";
                }
            }
        }
    }
});
var chartCandidateOffer = new Chart(ctxOffer, {
    type: 'bar',
    data: {
        labels: recruiterNames,
        datasets: dataChartInterviewOffer,
    },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    min: 0,
                    max: 100,
                    callback: function (value) {
                        return value.toFixed(0) + '%';
                    },
                },
            }],
            xAxes: [{
                ticks: {
                    autoSkip: false,
                }
            }],
        },
        title: {
            display: true,
            text: txtQuantityCandidatePassOffer,
            fontSize: 16
        },
        maintainAspectRatio: false,
        legend: {
            onClick: function (event, legendItem) {
                handleClickChart(legendItem);
            }
        },
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    return dataset.label + ": " + tooltipItem.yLabel + "%";
                }
            }
        }
    }
});

var handleClickChart = function (legendItem) {
    var index = legendItem.datasetIndex;
    $modalChangeColor.modal('show');
    $inputColor.val(legendItem.fillStyle);
    $form.find('input[name="channel"]').val(legendItem.text);
    $channelId.val(selectedChannels[index].id);
    $btnSave.attr('data-index', index);
};

$btnSave.click(function (event) {
    event.preventDefault();
    var index = $btnSave.attr('data-index');
    $btnSave.children('i').removeClass('hidden');
    $.ajax({
        url: $form.attr('action'),
        method: 'post',
        data: {
            _token: _token,
            channelId: $channelId.val(),
            color: $inputColor.val(),
        },
    }).done(function (data) {
        if (data && data.status === 1) {
            var colorCode = $inputColor.val();
            chartCandidateFail.data.datasets[index].backgroundColor = colorCode;
            chartCandidateFail.update();
            chartCandidatePass.data.datasets[index].backgroundColor = colorCode;
            chartCandidatePass.update();
            chartCandidateOffer.data.datasets[index].backgroundColor = colorCode;
            chartCandidateOffer.update();
            $modalChangeColor.modal('hide');
            $btnSave.children('i').addClass('hidden');
        }
    });
});
