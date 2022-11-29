<?php
use Rikkei\Test\View\ViewTest;

$statuses = ViewTest::listStatusLabel();
$labelsQuestion = $answersCorrect = $answersFalse = $notAnswers = [];
$totalCorrect = $totalFalse = $totalAnswer = 0;
?>
<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-sm-8">
                <div class="row">
                    <div class="col-sm-3 col-md-4">
                        <label>{{ trans('test::test.question_status') }}</label>
                    </div>
                    <div class="col-sm-9 col-md-8">
                        <select class="form-control select-search" name="filter[status]">
                            <option value="">All</option>
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" {{ $status == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="margin-bottom-5">
                    @include('team::include.filter')
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped dataTable table-bordered">
                <thead>
                <tr>
                    <th>{{ trans('test::test.question') }}</th>
                    <th class="col-name" data-order="sum_correct">{{ trans('test::test.num_answer_correct') }}</th>
                    <th class="col-name" data-order="sum_not_correct">{{ trans('test::test.num_answer_not_correct') }}</th>
                    <th class="col-name" data-order="total_answer">{{ trans('test::test.num_answer_total') }}</th>
                </tr>
                </thead>

                <tbody>
                @if (!$questionAnalytic->isEmpty())
                    <?php
                    $totalQuestion = $questionAnalytic->count();
                    ?>
                    @foreach ($questionAnalytic as $item)
                        <?php
                        $answersCorrect[] = $item->sum_correct;
                        $answersFalse[] = $item->sum_not_correct;
                        $notAnswers[] = $totalResult - ($item->sum_correct + $item->sum_not_correct);
                        $labelsQuestion[] = $item->pivot->order + 1;
                        $totalCorrect += $item->sum_correct;
                        $totalFalse += $item->sum_not_correct;
                        $totalAnswer += $item->total_answer;
                        ?>
                        <tr>
                            <td>
                                <strong class="minw-15">{{ $item->pivot->order + 1 }}</strong>
                                <button type="button" class="btn-show-content btn btn-primary" title="{{ trans('test::test.view') }}">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <div class="hidden q_content {{ $item->is_editor ? 'editor' : '' }}">{!! $item->content !!}</div>
                            </td>
                            <td>{{ $item->sum_correct }} &nbsp;&nbsp;({{ $totalResult ? number_format($item->sum_correct / $totalResult * 100) : 0 }}%)</td>
                            <td>{{ $item->sum_not_correct }} &nbsp;&nbsp;({{ $totalResult ? number_format($item->sum_not_correct / $totalResult * 100) : 0 }}%)</td>
                            <td>{{ $item->total_answer }}/{{ $totalResult }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>{{ trans('test::test.average') }}</th>
                        <th>{{ number_format($totalCorrect / $totalQuestion, 2) }}</th>
                        <th>{{ number_format($totalFalse / $totalQuestion, 2) }}</th>
                        <th>{{ number_format($totalAnswer / $totalQuestion, 2) }}</th>
                    </tr>
                @else
                    <tr>
                        <td colspan="4" class="text-center">
                            <h4>{{ trans('test::test.no_questions') }}</h4>
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6 margin-top-50">
        <canvas id="answers_char" height="{{ 20 * ($questionAnalytic->count() + 1) }}"></canvas>
    </div>
</div>

<script src="{{ asset('lib/chartjs/Chart_2.5_.min.js') }}"></script>
<script>
    var testId = '{{ $test->id }}';
    var type = '{{ $testerType }}';
    var urlGetAnalytics = '{{ route('test::admin.test.analytics') }}';

    (function ($) {
        var answersCorrect = '{{ json_encode($answersCorrect) }}';
        answersCorrect = JSON.parse(answersCorrect.replace(/&quot;/g, '"'));
        var answersFalse = '{{ json_encode($answersFalse) }}';
        answersFalse = JSON.parse(answersFalse.replace(/&quot;/g, '"'));
        var notAnswers = '{{ json_encode($notAnswers) }}';
        notAnswers = JSON.parse(notAnswers.replace(/&quot;/g, '"'));
        var labelsQuestion = '{{ json_encode($labelsQuestion) }}';
        labelsQuestion = JSON.parse(labelsQuestion.replace(/&quot;/g, '"'));
        if (!Array.isArray(notAnswers) || notAnswers.length === 0) {
            notAnswers = [0];
        }
        var ctx = document.getElementById("answers_char");
        var myChart = new Chart(ctx, {
            type: 'horizontalBar',
            data: {
                labels: labelsQuestion,
                datasets: [{
                    label: '<?php echo trans("test::test.num_answer_correct") ?>',
                    backgroundColor: 'rgb(51, 102, 204)',
                    data: answersCorrect
                }, {
                    label: '<?php echo trans("test::test.num_answer_not_correct") ?>',
                    backgroundColor: 'rgb(220, 57, 18)',
                    data: answersFalse
                }, {
                    label: '<?php echo trans("test::test.num_not_answer") ?>',
                    backgroundColor: '#F29220',
                    data: notAnswers
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        stacked: true,
                        scaleLabel: {
                            display: true,
                            labelString: '<?php echo trans("test::test.question") ?>'
                        }
                    }],
                    xAxes: [{
                        stacked: true,
                        ticks: {
                            beginAtZero: true
                        },
                        scaleLabel: {
                            display: true,
                            labelString: '<?php echo trans("test::test.number") ?>'
                        }
                    }]
                }
            }
        });
    })(jQuery);

    $('.select-search').on("change", function () {
        $.ajax({
            method: 'GET',
            url: urlGetAnalytics,
            data: {
                'test_id': testId,
                'testerType': type,
                'status': $(this).val()
            },
            success: function (res) {
                $('#analytic_tab').html(res);
            },
            error: function () {
                bootbox.alert({
                    message: 'Error system',
                    backdrop: true,
                    className: 'modal-default',
                });
            },
        });
    });
</script>
@include('test::template.script')