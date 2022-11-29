 <?php 
    use Carbon\Carbon;
    use Rikkei\Core\Model\EmailQueue;
    $layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)
@section('content')
    <span class="managetime-span-black">{{ trans('ot::view.Dear') }} {{ $data['receiver_name'] }},</span> <br> <br> 
        
    <span class="managetimemanagetime-span-black">{{ trans('ot::view.Have employee :registrant_name register overtime as below and register for you.', ['registrant_name' => $data['register_name']]) }}</span> <br> <br>
        
    <span class="managetime-span-black">{{ trans('ot::view.Detailed registration information is:') }} </span><br>

    <ul class="managetime-span-black">
        <li class="managetime-span-black">{{ trans('ot::view.Registrant:') }} {{ $data['register_name'] }}</li>
        <li class="managetime-span-black">{{ trans('ot::view.Position:') }} {{ $data['team_name'] }}</li>
        <li class="managetime-span-black">{{ trans('ot::view.Overtime start shift:') }} {{ $data['start_at'] }}
        <li class="managetime-span-black">{{ trans('ot::view.Overtime end shift:') }} {{ $data['end_at'] }}
        <li class="managetime-span-black">{{ trans('ot::view.Reason:') }} {!! $data['reason'] !!}</li>
    </ul>
        
    <span class="managetime-span-black">{{ trans('ot::view.You can click on the following link for more details:') }} <a href="{{ $data['link'] }}">{{ trans('ot::view.See details') }}</a></span> <br> <br>
        
    <span class="managetime-span-black">{{ trans('ot::view.Thanks!') }}</span> <br>
    
    <span class="managetime-span-black">{{ trans('ot::view.Intranet.') }}</span>

    <style type="text/css">
        .managetime-span-black {
            color: #000;
        }
    </style>
@endsection
