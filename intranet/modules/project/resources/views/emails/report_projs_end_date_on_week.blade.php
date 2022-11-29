<?php
use Rikkei\Core\Model\EmailQueue;
use Carbon\Carbon;
use Rikkei\Project\View\CheckEndDateProjsOnWeek;
use Rikkei\Team\Model\Employee;

extract($data);
?>
@extends(EmailQueue::getLayoutConfig())

@section('content')
<p>Dear {{ Employee::getNameByEmail($email)->name }},</p>
<p><strong>Thông báo tới bạn dự án trong tuần này đến ngày deliver hoặc end date.</strong></p>
@foreach($dataEmail as $key => $data)
    <p></p>
    <p>&emsp; {{ $key + 1 }}. <a href="{{ URL::route('project::project.edit', ['id' => $data['id']]) }}" target="_blank">{{ $data['name'] }}</a></p>
    <p>&emsp; Project manager: {{ $data['manager_name'] . " (" . preg_replace('/@.*/', '', $data['manager_email']) .")" }}</p>
    @if (CheckEndDateProjsOnWeek::checkDayOnWeek(Carbon::parse($data['end_at'])))
        <p>&emsp; End date: {{ Carbon::parse($data['end_at'])->format('d/m/Y') }}</p>
    @endif

    <?php
        $delivers = explode(",", $data['committed_date']);

    ?>
    @foreach ($delivers as $deliver)
        @if ($deliver == null)
            @continue
        @endif
        @if (CheckEndDateProjsOnWeek::checkDayOnWeek(Carbon::parse($deliver)))
            <p>&emsp; Deliver date: {{ Carbon::parse($deliver)->format('d/m/Y') }}</p>
        @endif
    @endforeach

@endforeach
@endsection
