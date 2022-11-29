<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
?>

<p>Thông báo lịch ME & xét thưởng tháng {{ $month }}</p>
<p>&nbsp;</p>
<ol>
    @foreach ($deadLines as $key => $arrValues)
    <li>17h30 thứ {{ $arrValues[0] }} ({{ $arrValues[1] }}): {{ $arrValues[2] }}</li>
    @endforeach
</ol>
<p>&nbsp;</p>
<p>Trân trọng cảm ơn!</p>
<br />
-----------------------------------------

@stop
