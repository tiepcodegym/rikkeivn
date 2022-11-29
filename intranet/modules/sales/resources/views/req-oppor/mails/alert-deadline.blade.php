<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
if (!isset($dearName)) {
    $dearName = 'Mr/Mrs';
}
?>
@extends($layout)

@section('content')

<p>Xin chào <strong>{{ $dearName }}</strong>, </p>
<p>&nbsp;</p>
<p>Opportunity sau có deadline là ngày hôm nay, vui lòng xem xét và cập nhật:</p>
@if ($oppors)
<ul>
    @foreach ($oppors as $opp)
    <li><a href="{{ route('sales::req.oppor.edit', $opp['id']) }}">{{ $opp['name'] }}</a></li>
    @endforeach
</ul>
@endif
<p>&nbsp;</p>

@endsection
