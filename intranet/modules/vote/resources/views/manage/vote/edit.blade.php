<?php
use Rikkei\Vote\View\VoteConst;
use Rikkei\Core\View\CoreUrl;
use Carbon\Carbon;

$timeNow = Carbon::now();
?>

@extends('layouts.default')

@section('title', trans('vote::view.vote_detail'))

@section('css')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/css/bootstrap-multiselect.css" />
@include('vote::include.css')

@stop

@section('content')

<div class="nav-tabs-custom tab-info tab-keep-status">
    <ul class="nav nav-tabs" role="tablist" id="vote_tabs">
        <li role="presentation" class="active">
            <a href="#vote_info" data-url="" aria-controls="vote_info" role="tab" data-toggle="tab">
                <strong>{{ trans('vote::view.vote_info') }}</strong>
            </a>
        </li>
        <li role="presentation">
            <a href="#nominate_list" data-url="{{ route('vote::manage.nominee.load_data', ['vote_id' => $vote->id]) }}" aria-controls="nominate_list" role="tab" data-toggle="tab">
                <strong>{{ trans('vote::view.nominate_list') }}</strong>
            </a>
        </li>
        <li role="presentation">
            <a href="#candidate_list" data-url="{{ route('vote::manage.vote_nominee.load_data', ['vote_id' => $vote->id]) }}" aria-controls="voter_list" role="tab" data-toggle="tab">
                <strong>{{ trans('vote::view.voter_list_and_result') }}</strong>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active loaded" id="vote_info">
            <div class="row">
                <div class="col-lg-10 col-lg-offset-1">
                    <div class="box-body">
                        {!! Form::open(['method' => 'put', 'route' => ['vote::manage.vote.update', $vote->id], 'id' => 'vote_form']) !!}

                        @include('vote::manage.include.basic_info')

                        @if ($permissEdit)
                        <div class="col-md-12">
                            <div class="form-group text-center">
                                <input type="hidden" name="change_data" id="change_data" value="0">
                                <button type="submit" id="btn_save_vote" class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('vote::view.save') }}</button>
                                @if ($vote->status != VoteConst::STT_DISABLE && 
                                    $timeNow->lte($vote->vote_end_at) && 
                                    (!$vote->nominate_end_at || $timeNow->lte($vote->nominate_end_at)) &&
                                    $timeNow->lte($vote->vote_start_at))
                                <button type="button" id="btn_sendmail" class="btn btn-info"><i class="fa fa-envelope"></i> {{ trans('vote::view.send_mail_notify') }}</button>
                                @endif
                            </div>
                        </div>
                        @endif

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="nominate_list">
            <div class="grid-data-query" data-url="{{ route('vote::manage.nominee.load_data', ['vote_id' => $vote->id]) }}">
                <h4 class="box-title padding-left-15"><i class="block-loading-icon fa fa-spin fa-refresh hidden"></i></h4>
                @if ($vote->status != VoteConst::STT_DISABLE && 
                    $timeNow->lte($vote->vote_start_at) && 
                    (!$vote->nominate_start_at || $timeNow->gte($vote->nominate_start_at)) && 
                    (!$vote->nominate_end_at || $timeNow->lte($vote->nominate_end_at)))
                <p>
                    <a class="btn btn-primary" href="{{ route('vote::show_nominate', ['slug' => $vote->slug]) }}" target="_blank">{{ trans('vote::view.nominate_link') }}</a>
                    <a class="btn btn-primary" href="{{ route('vote::show_self_nominate', ['slug' => $vote->slug]) }}" target="_blank">{{ trans('vote::view.self_nominate_link') }}</a>
                </p>
                @endif
                <div class="grid-data-query-table">
                    @include('vote::manage.include.nominee_list')
                </div>
            </div>
        </div>
        <?php
        $canAddNominee = $timeNow->lte($vote->vote_start_at);
        ?>
        <div role="tabpanel" class="tab-pane" id="candidate_list">
            @if ($permissEdit)
            <div class="actions-box">
                @if ($canAddNominee)
                <button type="button" class="btn-add" data-toggle="modal" data-target="#modal_add_nominee"><i class="fa fa-plus"></i> {{ trans('vote::view.add_nominee') }}</button>
                @endif
                @if ($vote->status != VoteConst::STT_DISABLE && 
                    $timeNow->lte($vote->vote_end_at) && 
                    (!$vote->nominate_end_at || $timeNow->gte($vote->nominate_end_at)))
                <button type="button" class="btn btn-info" id="btn_send_mail_vote"><i class="fa fa-envelope"></i> {{ trans('vote::view.send_vote_mail') }}</button>
                @endif
                @if ($vote->status != VoteConst::STT_DISABLE && 
                    $timeNow->gte($vote->vote_start_at) && 
                    $timeNow->lte($vote->vote_end_at))
                <a class="mgl-10 btn btn-primary" href="{{ route('vote::show_vote', ['slug' => $vote->slug]) }}" target="_blank">{{ trans('vote::view.vote_link') }}</a>
                @endif
            </div>
            @endif
            <div class="grid-data-query" data-url="{{ route('vote::manage.vote_nominee.load_data', ['vote_id' => $vote->id]) }}">
                <h4 class="box-title padding-left-15"><i class="block-loading-icon fa fa-spin fa-refresh hidden"></i></h4>
                <div class="grid-data-query-table">
                    @include('vote::manage.include.candidate_list')
                </div>
            </div>
        </div>
    </div>
</div>

    
    @include('vote::manage.include.nominator_modal')
    @include('vote::manage.include.voter_modal')
    
    @if ($canAddNominee)
        @include('vote::manage.include.add_nominee')
    @endif


@if ($vote->status != VoteConst::STT_DISABLE)
    @if (!$vote->nominate_end_at || $timeNow->lte($vote->nominate_end_at))
        @include('vote::email.modal_sendmail')
    @endif

    @if (!$vote->nominate_end_at || $timeNow->gte($vote->nominate_end_at))
        @include('vote::email.modal_sendmail_vote')
    @endif
@endif

@include('vote::email.team_modal')

@stop

@section('script')

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/0.9.13/js/bootstrap-multiselect.min.js"></script>
<script src="{{ CoreUrl::asset('lib/ckeditor/ckeditor.js') }}"></script>
<script>
    
    var hasMultiSelect = true;
    
    @if (Session::has('mail-error') && Session::get('mail-error'))
        $('#sendmail_modal').modal('show');
        $('.flash-message').appendTo($('#sendmail_modal .message_box'));
    @endif
    
    @if (Session::has('mail-vote-error') && Session::get('mail-vote-error'))
        $('#modal_send_mail_vote').modal('show');
        $('.flash-message').appendTo($('#modal_send_mail_vote .message_box'));
    @endif
    
    @if (Session::has('nominee-error') && Session::get('nominee-error'))
        $('#modal_add_nominee').modal('show');
        $('.flash-message').appendTo($('#modal_add_nominee .message_box'));
    @endif
    
</script>
@include('vote::include.script')

@stop

