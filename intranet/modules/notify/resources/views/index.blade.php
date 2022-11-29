@extends('layouts.default')

@section('title', trans('notify::view.All notifications'))

<?php
use Rikkei\Notify\View\NotifyView;

$nextPage = $collection->currentPage();
if ($nextPage) {
    $nextPage += 1;
}
?>

@section('content')

<div class="box box-rikkei notify-page">
    <div class="box-body">
        <div class="row">
            <div class="col-sm-6 col-sm-offset-3">
                <p class="check-readall{{ $collection->isEmpty() ? ' hidden' : '' }}">
                    <a href="#">{{ trans('notify::view.Check read all') }}</a>
                </p>
                <ul class="list-unstyled noti-contain">
                    <li class="noti-body">
                        <ul class="notify-list list-unstyled" id="notify_list_page"
                            data-url="{{ route('notify::load_data', ['per_page' => $perPage, 'page' => $nextPage]) }}">
                            @if (!$collection->isEmpty())
                                @foreach ($collection as $notify)
                                    @include('notify::template.notify-item')
                                @endforeach
                            @endif
                        </ul>
                    </li>
                    @if ($collection->isEmpty())
                    <li class="none-item text-center">
                        <a href="#">{{ trans('notify::view.None notify') }}</a>
                    </li>
                    @endif
                    <li class="text-center noti-loading hidden">
                        <a href="#"><i class="fa fa-refresh fa-spin"></i></a>
                    </li>
                    @if ($collection->hasMorePages())
                    <li class="footer load-more">
                        <h4 class="text-center"><a href="#">{{ trans('notify::view.Load more') }}</a></h4>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>

@stop

@section('script')
<script>
    (function ($) {
        $('.notify-content').shortedContent({showChars: 500,});
    })(jQuery);
</script>
@stop
