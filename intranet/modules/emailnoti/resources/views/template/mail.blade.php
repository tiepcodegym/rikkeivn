<?php 
	use Rikkei\Core\Model\EmailQueue;
	$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
{!! $data['content'] !!}
@endsection