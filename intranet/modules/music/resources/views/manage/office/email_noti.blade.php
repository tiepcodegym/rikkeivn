<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
use Rikkei\Core\View\View as CoreView;
?>
@extends($layout)

@section('content')

<?php
?>

<p>Dear <strong>{{$name}}</strong>,</p>
<p>Đến {{$time}} là giờ phát nhạc, vui lòng phát nhạc bằng cách click vào đường link dưới đây: </p>

<p><a href="{{URL::route('music::order.office',$officeId)}}">{{URL::route('music::order.office',$officeId)}}</a></p>

<p>Trân trọng cảm ơn.</p>
<br />
-----------------------------------------

@endsection