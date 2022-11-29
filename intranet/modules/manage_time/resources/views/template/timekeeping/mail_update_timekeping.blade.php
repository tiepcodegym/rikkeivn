<?php 
    use Carbon\Carbon;
    use Rikkei\Core\Model\EmailQueue;
    $layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
    <span class="managetime-span-black">{{ trans('manage_time::view.Dear') }} {{ $data['receiver_name'] }}</span> <br>

    <span class="managetime-span-black">{{ $data['content'] }}</span> <br>
    
    @if (!empty($data['errors']))
    <ul>
        @foreach ($data['errors'] as $error)
        <li>{!! $error !!}</li>
        @endforeach
    </ul>
    @endif

    <span class="managetime-span-black">{{ trans('manage_time::view.Detailed timekeeping table:') }} </span><br>

    <ul class="managetime-span-black">
        <li class="managetime-span-black">{{ trans('manage_time::view.Timekeeping table name:') }} {{ $data['timekeeping_table_name'] }}</li>
        <li class="managetime-span-black">{{ trans('manage_time::view.Month:') }} {{ $data['month'] }}</li>
        <li class="managetime-span-black">{{ trans('manage_time::view.Year:') }} {{ $data['year'] }}</li>
    </ul>

    <span class="managetime-span-black">{{ trans('manage_time::view.You can click on the following link for more details:') }} <a href="{{ $data['link'] }}">{{ trans('manage_time::view.See details') }}</a></span> <br>

    <span class="managetime-span-black">{{ trans('manage_time::view.Thanks!') }}</span> <br>
    
    <span class="managetime-span-black">{{ trans('manage_time::view.Intranet.') }}</span>

    <style type="text/css">
        .managetime-span-black {
            color: #000;
        }
    </style>
@endsection