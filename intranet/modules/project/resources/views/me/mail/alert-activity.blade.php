<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
?>

<p>Xin chào anh/chị@if (isset($dearName)) <strong>{{ $dearName }}</strong> @endif,</p>
&nbsp;
<p>Đề nghị anh/chị điền hoạt động ME trong tháng {{ $month }} theo đường dẫn sau:</p>
<p><a href="{{ $detailLink }}">Hoạt động ME</a></p>
<p>Deadline là 17h30, thứ 6 ngày {{ $blWeek[1] . '-' . $month }}</p>
&nbsp;
<p>Trân trọng cảm ơn!</p>
<br />
-----------------------------------------

@stop
