<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Vote\View\VoteConst;

$formatDay = trans('vote::view.format_day');
?>
@extends('layouts.default')

@section('title', trans('vote::view.confirm_join_vote'))

@section('css')
<link rel="stylesheet" href="{{ CoreUrl::asset('vote/css/front.css') }}">
@endsection

@section('content')

<div class="box box-info">
    <div class="box-header with-border">
        <h4>{{ $vote->title }}</h4>
        <div class="vote-info mgb-10">
            @if ($vote->nominate_start_at)
            <div><strong>{{ trans('vote::view.nominate_start_at') }}</strong>: <span>{{ $vote->nominate_start_at->format('H\hi '. $formatDay .' d/m/Y') }}</span></div>
            @endif
            @if ($vote->nominate_end_at)
            <div><strong>{{ trans('vote::view.nominate_end_at') }}</strong>: <span>{{ $vote->nominate_end_at->format('H\hi '. $formatDay .' d/m/Y') }}</span></div>
            @endif
            <div><strong>{{ trans('vote::view.you_can_nominate_max') }}</strong>: 
                @if ($vote->nominee_max)
                <span>{{ $vote->nominee_max }}</span> {{ trans('vote::view.person') }}
                @else
                <span>{{ trans('vote::view.unlimited_person') }}</span>
                @endif
            </div>
        </div>
        <div class="vote-desc">
            <strong>{{ trans('vote::view.content') }}:</strong>
            <div class="vote-content">
                {!! $vote->content !!}
            </div>
            <div class="more-link text-center hidden">
                <a href="#" class="btn btn-info" data-text-more="{{ trans('vote::view.read_more') }}" data-text-less="{{ trans('vote::view.show_less') }}">{{ trans('vote::view.read_more') }}</a>
            </div>
        </div>
    </div>
    
    <div class="box-body">
        @if (isset($errorMess))
            <div class="alert alert-error">
                {{ $errorMess }}
            </div>
        @else
            @if ($voteNominee->confirm == VoteConst::CONFIRM_YES)
                <div class="alert alert-success">
                {!! trans('vote::message.you_had_confirmed_yes_join_vote') !!}
                </div>
            @else
                <div class="alert alert-warning">
                    {!! trans('vote::message.you_had_confirmed_no_join_vote') !!}
                </div>
            @endif
        @endif
    </div>

</div>

@endsection

@section('script')
<script>
    var textValidRequired = '<?php echo trans('vote::message.this_field_is_required') ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('vote/js/front.js') }}"></script>
@endsection