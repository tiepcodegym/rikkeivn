<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Vote\View\VoteConst;

$formatDay = trans('vote::view.format_day');
?>
@extends('layouts.default')

@section('title', $vote->title)

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" />
<link rel="stylesheet" href="{{ CoreUrl::asset('vote/css/front.css') }}">
@endsection

@section('content')

<div class="box box-info">
    <div class="box-header with-border">
        <div class="vote-info mgb-10">
            <div><strong>{{ trans('vote::view.vote_start_at') }}</strong>: <span>{{ $vote->vote_start_at->format('H\hi '. $formatDay .' d/m/Y') }}</span></div>
            <div><strong>{{ trans('vote::view.vote_end_at') }}</strong>: <span>{{ $vote->vote_end_at->format('H\hi '. $formatDay .' d/m/Y') }}</span></div>
            <div><strong>{{ trans('vote::view.you_can_vote_max') }}</strong>: 
                @if ($vote->vote_max)
                <span>{{ $vote->vote_max }}</span> {{ trans('vote::view.person') }}, {{ trans('vote::view.left_over_count') }}: <span class="num_left_over">{{ $remainVote }}</span>
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
        @if (!$errorMess)
            <h4 class="mgb-30">{{ trans('vote::view.nominee_list') }}</h4>
            @if (!$nominees->isEmpty())
            <div class="nominee-list row text-center">
                @foreach ($nominees as $nominee)
                <div class="nominee {{ $nominee->had_voted ? 'had_voted' : '' }}">
                    <div class="inner">
                        <?php
                        $avatarUrl = $nominee->avatar_url;
                        if (!$avatarUrl) {
                            $avatarUrl = URL::asset('common/images/noavatar.png');
                        } else {
                            $arrAvatarUrl = explode('=', $nominee->avatar_url);
                            $arrLenght = count($arrAvatarUrl);
                            if ($arrLenght > 1) {
                                $arrAvatarUrl[$arrLenght - 1] = '300';
                                $avatarUrl = implode('=', $arrAvatarUrl);
                            }
                        }
                        ?>
                        <div class="nominee-name text-uppercase text-center"><i class="voted fa fa-check"></i><span>{{ $nominee->name }}</span></div>
                        <div class="thumb-box text-center">
                            <img class="img-responsive nominee-thumb" src="{{ $avatarUrl }}" alt="{{ $nominee->name }}">
                        </div>
                        <div class="vote-box text-center" data-vote-nominee="{{ $nominee->vote_nominee_id }}">
                            <a data-type="vote" href="{{ route('vote::vote_nominee', ['vote_nominee_id' => $nominee->vote_nominee_id]) }}" class="btn btn-block btn-success vote {{ $nominee->had_voted ? 'hidden' : '' }}">{{ trans('vote::view.vote') }}</a>
                            <a data-type="cancel" href="{{ route('vote::vote_nominee', ['vote_nominee_id' => $nominee->vote_nominee_id]) }}" class="btn btn-block btn-danger cancel {{ $nominee->had_voted ? '' : 'hidden' }}"><i class="fa fa-close"></i> {{ trans('vote::view.cancel_vote') }}</a>
                            <button class="btn btn-block btn-default v_loading hidden"><i class="fa fa-spin fa-refresh"></i></button>
                        </div>
                        <div class="nominee-desc text-left">
                            @if ($nominee->description)
                            <div class="white-space-pre content-show"></div>
                            <div class="white-space-pre content-more hidden">{{ $nominee->description }}</div>
                            @else
                            <div class="text-center no-desc"><i>*{{ trans('vote::message.no_description') }}*</i></div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            @else
            <h5>{{ trans('vote::message.not_found_item') }}</h5>
            @endif
        @else
            <div class="alert alert-danger">{{ $errorMess }}</div>
        @endif
    </div>
    
    <div class="modal fade" id="vote_desc_more" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-center">{{ trans('vote::view.description_of') }} <strong class="nominee-name"></strong></h4>
                </div>
                <div class="modal-body white-space-pre">
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('vote::view.close') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

</div>

@endsection

@section('script')
<script>
    var textValidRequired = '<?php echo trans('vote::message.this_field_is_required') ?>';
    var textViewMore = '<?php echo trans('vote::view.view_more') ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="{{ CoreUrl::asset('vote/js/front.js') }}"></script>
@endsection
