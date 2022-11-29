<?php
use Rikkei\Test\View\ViewTest;
use Rikkei\Team\Model\Team;

$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')
<p>{{ trans('resource::view.Hello :name,', ['name' => $data['name']]) }}</p>
<p>{!! trans('resource::view.The candidate <b>:name</b> has just been updated information:', ['name' => $data['candidateName']]) !!}</p> 
@foreach ($data['changes'] as $field => $value)
@if($field == 'contract_team_id')
    <?php
        $teamOldInfo  = Team::where('id',$value['old'])->first();
        if($teamOldInfo)
        {
            $value['old'] =  $teamOldInfo->name ."({$teamOldInfo->code})";
        }
        $teamNewInfo  = Team::where('id',$value['new'])->first();
        if($teamNewInfo)
        {
            $value['new'] = $teamNewInfo->name . "({$teamNewInfo->code})";
        }
        
    ?>
    <p style="text-align: left;">{{ trans('resource::view.' . $field )}}: {!! $value['old'] ? $value['old'] : nl2br(ViewTest::trimWords($value['old'])) !!} -> {!! $value['new'] ? $value['new'] : nl2br(ViewTest::trimWords($value['new'])) !!}</p>
@else
    <p style="text-align: left;">{{ trans('resource::view.' . $field )}}: {!! $value['old'] ? $value['old'] : nl2br(ViewTest::trimWords($value['old'])) !!} -> {!! $value['new'] ? $value['new'] : nl2br(ViewTest::trimWords($value['new'])) !!}</p>
@endif


@endforeach
<p>{{ trans('resource::view.Please visit the link below to view detail candidates') }}</p>
<p><a href="{{ $data['urlToCandidate'] }}" target="_blank">{{ $data['urlToCandidate'] }}</a></p>
@endsection
