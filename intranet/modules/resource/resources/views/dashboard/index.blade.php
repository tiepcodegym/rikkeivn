@extends('layouts.default')
@section('title')
{{ trans('resource::view.Dashboard.Resource dashboard') }}
@endsection

<?php

use Illuminate\Support\Facades\URL;
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\CoreUrl;

$date = date('Y-m-d');
$week = date('W');
$filter = CookieCore::getRaw('filter.resource.dashboard');
?>

@section('content')
<div class="row resource-dashboard">
    <div class="col-xs-12">
        @include('resource::dashboard.include.tab_dashboard')
    </div>
    <!-- /.col -->
    <div class="modal apply-click-modal"><img class="loading-img" src="{{ asset('sales/images/loading.gif') }}" /></div>
</div>
<!-- /.row -->

@endsection
<!-- Styles -->
@section('css')
<meta name="_token" content="{{ csrf_token() }}"/>
<link href="{{ asset('common/css/style.css') }}" rel="stylesheet" type="text/css" >
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="{{ URL::asset('resource/css/resource.css') }}" />
@endsection

<!-- Script -->
@section('script')
<script src="{{ CoreUrl::asset('lib/js/Chart.bundle.js') }}"></script>
<script src="{{ CoreUrl::asset('lib/js/chartjs-plugin-annotation.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('resource/js/dashboard/dashboard.js') }}"></script>
<script>
/** DECLARE ALL CHART */
var dates = [];
@foreach ($twelveMonth as $month)
dates.push("{{$month[0] . '/' . $month[1]}}");
@endforeach

//Total man month
var ctx = document.getElementById('totalEffort').getContext('2d');
var myChart = new Chart.Line(ctx, {
    data: {
        labels: dates,
        datasets: 
            [{
                label: '{{trans("resource::view.MM")}}',
                data: [{{implode(',', $totalMmNoBorrow)}}],
                backgroundColor: "rgba(153,255,51,0.4)"
            },
            //if choose team then show line borrow
            <?php if ($teamId) : ?>
            {
                label: '{{trans("resource::view.MM with borrow")}}',
                data: [{{implode(',', $totalManDay)}}],
                borderDash: [10,5]
            },
            //end line borrow
            <?php endif; ?>
            {
                label: '{{trans("resource::view.Employee MM")}}',
                data: [{{implode(',', $totalEmp)}}],
                backgroundColor: "rgba(255,153,0,0.4)"
            }]
    },
    options: {
        legend: {display: true},
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true 
                }
            }],
        }, // scales,
        annotation: {
            annotations: [
              {
                type: "line",
                mode: "vertical",
                scaleID: "x-axis-0",
                value: "{{$currentMonth}}",
                borderColor: "red",
                label: {
                  content: "Current",
                  enabled: true,
                  position: "top"
                }
              }
            ]
        },
        tooltips: {
            mode: 'single',
            callbacks: {
                label: function (tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    var total = 0;
                    for (var i = 1; i < data.datasets.length; i++) {
                        if (data.datasets[i].label == '{{trans("resource::view.Employee MM")}}') {
                            total += data.datasets[i].data[tooltipItem.index];
                        }
                    }
                    var currentValue = dataset.data[tooltipItem.index];
                    var precentage = rounding((currentValue/total) * 100,1);
                    if (dataset.label == '{{trans("resource::view.Employee MM")}}') {
                        return dataset.label + ": " + tooltipItem.yLabel;
                    } else {
                        return dataset.label + ": " + numberWithCommas(tooltipItem.yLabel) + " (" + precentage + "%)";
                    }

                }
            }
        },
    } 
});

/** ROLE */
var ctx = document.getElementById("role").getContext('2d');
//labels
var labels = [];
@foreach ($totalRole['labels'] as $labels)
labels.push("{{$labels}}");
@endforeach
//colors
var roleColors = [];
@foreach ($totalRole['colors'] as $color)
roleColors.push("{{$color}}");
@endforeach
var myChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: labels,
        datasets: [{
                backgroundColor: roleColors,
                data: [{{implode(',', $totalRole['count'])}}]
            }]
    },
    options: {
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                        return previousValue + currentValue;
                    });
                    var currentValue = dataset.data[tooltipItem.index];
                    var precentage = Math.floor(((currentValue/total) * 100)+0.5);   
                    var tooltipLabel = data.labels[tooltipItem.index];
                    return tooltipLabel + ': ' + currentValue + ' (' + precentage + "%)";
                }
            }
        },
        legend: {
            onClick: null
        }
    }
});

/** PROGRAMMING LANGUAGE */
var ctx = document.getElementById("program").getContext('2d');
//labels
var programs = [];
@foreach ($totalProgLang['labels'] as $label)
programs.push("{{$label}}");
@endforeach
//colors
var colorPro = [];
@foreach ($totalProgLang['colors'] as $color)
colorPro.push("{{$color}}");
@endforeach
var myChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: programs,
        datasets: [{
                backgroundColor: colorPro,
                data: [{{implode(',', $totalProgLang['count'])}}]
            }]
    },
    options: {
        tooltips: {
            callbacks: {
                label: function(tooltipItem, data) {
                    var dataset = data.datasets[tooltipItem.datasetIndex];
                    var total = dataset.data.reduce(function(previousValue, currentValue, currentIndex, array) {
                        return previousValue + currentValue;
                    });
                    var currentValue = dataset.data[tooltipItem.index];
                    var precentage = Math.floor(((currentValue/total) * 100)+0.5); 
                    var tooltipLabel = data.labels[tooltipItem.index];
                    return tooltipLabel + ': ' + currentValue + ' (' + precentage + "%)";
                }
            }
        },
        legend: {
            onClick: null
        }
    }
});

    
//Count employee by effort and count plan
    var numberWithCommas = function (x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    var dataPack1 = [{{implode(',', $empWithEffort[0])}}];
    var dataPack2 = [{{implode(',', $empWithEffort[1])}}];
    var dataPack3 = [{{implode(',', $empWithEffort[2])}}];
    var dataPack4 = [{{implode(',', $empWithEffort[3])}}];
    
    var bar_ctx = document.getElementById('bar-chart');
    var bar_chart = new Chart(bar_ctx, {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [
                {
                    type: 'line',
                    lineTension: 0,       
                    label: '{{ trans("resource::view.Count plan") }}',
                    data: [{{implode(',', $empPlan)}}],
                    backgroundColor: "#3498db",
                    borderColor: "#3498db",
                    hoverBorderWidth: 2,
                    hoverBorderColor: 'lightgrey',
                    fill: false,
                },
                {
                    label: '{{ trans("resource::view.Unallocated") }}',
                    data: dataPack1,
                    backgroundColor: "{{Dashboard::BG_EFFORT_WHITE}}",
                    hoverBackgroundColor: "{{Dashboard::BG_EFFORT_WHITE}}",
                    hoverBorderWidth: 2,
                    hoverBorderColor: 'lightgrey'
                },
                {
                    label: '{{ trans("resource::view.Warning") }}',
                    data: dataPack2,
                    backgroundColor: "{{Dashboard::BG_EFFORT_YELLOW}}",
                    hoverBackgroundColor: "{{Dashboard::BG_EFFORT_YELLOW}}",
                    hoverBorderWidth: 2,
                    hoverBorderColor: 'lightgrey'
                },
                {
                    label: '{{ trans("resource::view.Normal") }}',
                    data: dataPack3,
                    backgroundColor: "{{Dashboard::BG_EFFORT_GREEN}}",
                    hoverBackgroundColor: "{{Dashboard::BG_EFFORT_GREEN}}",
                    hoverBorderWidth: 2,
                    hoverBorderColor: 'lightgrey'
                },
                {
                    label: '{{ trans("resource::view.Overload") }}',
                    data: dataPack4,
                    backgroundColor: "{{Dashboard::BG_EFFORT_RED}}",
                    hoverBackgroundColor: "{{Dashboard::BG_EFFORT_RED}}",
                    hoverBorderWidth: 2,
                    hoverBorderColor: 'lightgrey'
                }
            ]
        },
        options: {
            annotation: {
                annotations: [
                  {
                    type: "line",
                    mode: "vertical",
                    scaleID: "x-axis-0",
                    value: "{{$currentMonth}}",
                    borderColor: "red",
                    label: {
                      content: "Current",
                      enabled: true,
                      position: "top"
                    }
                  }
                ]
            },
            tooltips: {
                mode: 'label',
                callbacks: {
                    label: function (tooltipItem, data) {
                        var dataset = data.datasets[tooltipItem.datasetIndex];
                        var total = 0;
                        for (var i = 1; i < data.datasets.length; i++)
                            total += data.datasets[i].data[tooltipItem.index];
                        var currentValue = dataset.data[tooltipItem.index];
                        var precentage = rounding((currentValue/total) * 100,1);
                        if (dataset.label == '{{ trans("resource::view.Count plan") }}') {
                            return dataset.label + ": " + tooltipItem.yLabel;
                        } else {
                            return dataset.label + ": " + numberWithCommas(tooltipItem.yLabel) + " (" + precentage + "%)";
                        }
                        
                    }
                }
            },
            scales: {
                xAxes: [{
                        stacked: true,
                        gridLines: {display: false},
                    }],
                yAxes: [{
                        stacked: true,
                        ticks: {
                            callback: function (value) {
                                return numberWithCommas(value);
                            },
                            beginAtZero: true 
                        }
                    }],
            }, // scales,
            legend: {
                onClick: null
            }
        } // options
    }
);

/** PROJECT CHART */
var ctx = document.getElementById("project").getContext('2d');
//labels
var labels = [];
@foreach ($projTypeMm['labels'] as $labels)
labels.push("{{$labels}}");
@endforeach
var countProj = [];
@foreach ($projTypeMm['countProj'] as $label => $value)
countProj["{{$label}}"] = {{$value}};
@endforeach

var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [
        {
            type: 'bar',
            label: '{{trans("resource::view.Man month")}}',
            backgroundColor: '#2ecc71',
            data: [{{implode(',', $projTypeMm['count'])}}]
        },
        {
            type: 'bar',
            lineTension: 0,       
            label: '{{trans("resource::view.Count project")}}',
            borderColor: '#3498db',
            backgroundColor: '#3498db',
            data: [{{implode(',', $projTypeMm['countProj'])}}],
            fill: false,
        }
    ]
  },
    options: {
        legend: {
            onClick: null
        },
        scales: {
            yAxes: [{
                display: true,
                ticks: {
                    beginAtZero: true   // minimum value will be 0.
                }
            }]
        }
    } 
});
</script>
<script type="text/javascript">
    var labelLPs = [];
    var numberLPs = [];
    @foreach ($dataLP as $data)
    labelLPs.push("{{ $data->name }}");
    numberLPs.push("{{ $data->number }}");
    @endforeach

    new Chart(document.getElementById("doughnut-chart"), {
        type: 'doughnut',
        data: {
          labels: labelLPs,
          datasets: [
            {
              backgroundColor: ["#FFFF00", "#DA70D6", "#FFA500", "#EEE8AA", "#98FB98", "#AFEEEE", "#D87093", "#CD853F", "#FFC0CB", "#800080", "#FF0000", "#6B8E23", "#808000", "#000080", "#BC8F8F", "#4169E1", "#8B4513", "#FA8072", "#F4A460", "#2E8B57", "#6A5ACD", "#708090"],
              data: numberLPs
            }
          ]
        },
    });
</script>
@endsection
