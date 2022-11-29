<?php 
    use Carbon\Carbon;
    use Rikkei\Core\Model\EmailQueue;
    $layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
    @if ($data['error'] !== null)
    <p>File: {{ $data['fileName'] }}</p>
    <p>{{ $data['error'] }}</p>
    @endif
@endsection