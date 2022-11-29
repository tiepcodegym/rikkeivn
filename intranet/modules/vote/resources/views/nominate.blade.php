<?php
use Rikkei\Core\View\CoreUrl;

$title = trans('vote::view.nominate');
$route = ['vote::add_nominate', $vote->id];
if ($isSelf) {
    $title = trans('vote::view.self_nominate');
    $route = ['vote::add_self_nominate', $vote->id];
}
$formatDay = trans('vote::view.format_day');

?>
@extends('layouts.default')

@section('title', $title)

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
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
            
            @if (!$isSelf)
            <div><strong>{{ trans('vote::view.you_can_nominate_max') }}</strong>: 
                @if ($vote->nominee_max)
                <span>{{ $vote->nominee_max }}</span> {{ trans('vote::view.person') }}
                    @if (isset($remaniNominee))
                    , {{ trans('vote::view.left_over_count') }}: <span class="num_left_over">{{ $remaniNominee }}</span>
                    @endif
                @else
                <span>{{ trans('vote::view.unlimited_person') }}</span>
                @endif
            </div>
            @endif
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
        <?php
        $dupError = false;
        if (Session::has('dup-error')) {
            $dupError = Session::get('dup-error');
        }
        ?>
        @if (!$errorMess)
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                {!! Form::open(['method' => 'post', 'route' => $route, 'id' => 'nominee_form']) !!}
                
                @if (!$isSelf)
                <div class="form-group">
                    <label class="required">{{ trans('vote::view.who_do_you_wanto_nominate') }} <em>*</em></label>
                    <select name="nominee_id" class="form-control multi-select" data-url="{{ route('vote::list_employee', ['vote_id' => $vote->id, 'excerpt_current' => !$isSelf, 'has_nominator' => true]) }}">
                        <option value="">{{ trans('vote::view.select_nominate') }}</option>
                    </select>
                </div>
                @endif
                
                <div class="form-group">
                    <label class="required">{{ trans('vote::view.reason') }} <em>*</em></label>
                    <textarea name="reason" class="form-control" rows="4">{{ old('reason') }}</textarea>
                </div>
                
                <div class="form-group text-center">
                    <button type="submit" class="btn-add">{{ $title }}</button>
                </div>
                
                {!! Form::close() !!}
            </div>
        </div>
        @else
            @if (!$dupError && !Session::get('messages'))
            <div class="alert alert-danger">{{ $errorMess }}</div>
            @endif
        @endif
    </div>
    
</div>

@endsection

@section('script')
<script>
    var textValidRequired = '<?php echo trans('vote::message.this_field_is_required') ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('vote/js/front.js') }}"></script>
@endsection
