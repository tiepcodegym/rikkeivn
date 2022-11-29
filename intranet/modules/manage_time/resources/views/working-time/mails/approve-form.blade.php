<?php
    use Rikkei\ManageTime\View\WorkingTime;

    $layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
    extract($data);
    $objWorkingTIme = new WorkingTime();
    $permissRegister = $objWorkingTIme->getPermissByRoute();
?>
@extends($layout)

@section('content')

<p>Xin chào <strong>{{ $dearName }}</strong>,</p>

<p>&nbsp;</p>
<p>{!! $content !!}</p>
<p>&nbsp;</p>

@if ($permissRegister)
    @if (isset($detailLink))
        <p><a href="{{ $detailLink }}" style="color: #15c">Xem chi tiết</a></p>
    @endif
@else
    <p><a href="{{route('manage_time::wktime.index')}}" style="color: #15c">Xem danh sách</a></p>
@endif
@endsection
