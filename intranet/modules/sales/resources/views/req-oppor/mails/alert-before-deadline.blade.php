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
<p>Opportunity sau sắp đến deadline, vui lòng xem xét và cập nhật:</p>
@if ($oppors)
<ul>
    @foreach ($oppors as $opp)
    <?php
    $detailLink = route('sales::req.apply.oppor.view', $opp['id']);
    ?>
    <li><a href="{{ $detailLink }}" style="text-decoration: none;"><strong>{{ $opp['duedate'] }}</strong>: {{ $opp['name'] }}</a></li>
    @endforeach
</ul>
@endif
<p>&nbsp;</p>

@endsection
