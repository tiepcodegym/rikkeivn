<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
if (!isset($dearName)) {
    $dearName = 'all';
}
?>
@extends($layout)

@section('content')

<p>Xin chào <strong>{{ $dearName }}</strong>, </p>
<p>&nbsp;</p>
<p>Có một số <strong>Opportunities</strong> sắp tới deadline, vui lòng xem xét và cập nhật:</p>
@if ($oppors)
<ul>
    @foreach ($oppors as $opp)
    <li><a href="{{ route('sales::req.apply.oppor.view', $opp['id']) }}">{{ $opp['duedate'] }}: {{ $opp['name'] }}</a></li>
    @endforeach
</ul>
@endif
<p>&nbsp;</p>
<p>Thank you!</p>

@endsection
