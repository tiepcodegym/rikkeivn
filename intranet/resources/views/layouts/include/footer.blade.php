<?php
use Carbon\Carbon;
use Rikkei\Notes\Model\ReleaseNotes;

$currentYear = Carbon::now()->format('Y');
$version = (new ReleaseNotes())->getLastVersion();
$version = !$version ? '1.0.0' : $version->version;
?>
<footer class="main-footer">
    <div class="container-fluid">
        <div class="pull-right hidden-xs">
            <a href="{{ route('notes::notes.index') }}" style="color: #444; ">
                <b>{{ trans('view.footer Version') }}</b> {!!$version!!}
            </a> 
        </div>
        <strong>{{ trans('view.Copyright') }} {!! $currentYear !!} <a href="https://rikkeisoft.com/">{{ trans('view.RikkeiSoft') }}</a>.</strong> {{ trans('view.All rights reserved') }}
    </div><!-- /.container -->
</footer>
