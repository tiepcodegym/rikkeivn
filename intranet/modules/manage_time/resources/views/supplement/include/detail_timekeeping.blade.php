<?php
    use Carbon\Carbon;
    use Rikkei\ManageTime\View\ManageTimeConst;
    use Rikkei\ManageTime\View\View as ManageTimeView;
?>

<div class="table-responsive tk_detail" style="margin-left:0px; margin-right:0px">
    <table class="table table-striped table-bordered table-hover table-grid-data">
        <thead style="background-color: #d9edf7;">
            <tr>
                <th class="managetime-col-60">{{ trans('manage_time::view.Day of month') }}</th>
                <th class="managetime-col-60">{{ trans('manage_time::view.Day of week') }}</th>
                <th class="managetime-col-60">{{ trans('manage_time::view.Time in morning') }}</th>
                <th class="managetime-col-60">{{ trans('manage_time::view.Time out morning') }}</th>
                <th class="managetime-col-60">{{ trans('manage_time::view.Time in afternoon') }}</th>
                <th class="managetime-col-60">{{ trans('manage_time::view.Time out afternoon') }}</th>
                <th class="managetime-col-200 text-center">{{ trans('manage_time::view.Timekeeping sign') }}</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $days = ManageTimeConst::days();
            ?>
            @if (isset($timekeeping) && count($timekeeping))
                @foreach ($timekeeping as $date => $item)
                    <?php
                        $timeInOut = ManageTimeView::displayTimeInOut($item);
                        $date = Carbon::parse($date)
                    ?>
                    <tr data-id_timekeeping={{$item->timekeeping_table_id}}>
                        <td>{{ $date->format('d/m/Y') }}</td>
                        <td style="min-width: 90px;">{{ $days[$date->dayOfWeek] }}</td>
                        <td>{{ $timeInOut['timeInMor'] }}</td>
                        <td>{{ $timeInOut['timeOutMor'] }}</td>
                        <td>{{ $timeInOut['timeInAfter'] }}</td>
                        <td>{{ $timeInOut['timeOutAfter'] }}</td>
                        <td>{{ $item->sign_fines }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>