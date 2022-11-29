<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);
?>
@extends($layout)

@section('content')

<p>&nbsp;</p>
<p>{!! $content !!}</p>
<p>&nbsp;</p>

@endsection
