@extends('layouts.default')

@section('title')
    {{ $titleHeadPage }}
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
    <link rel="stylesheet" href="{{ URL::asset('asset_news/css/news.css') }}" />
@endsection
@section('content')
<?php
use Rikkei\Team\View\Config as TeamConfig;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\Form as CoreForm;
?>
<div class="row">
    <div class="col-md-6">
        <div class="table-responsive">
            <table>
                <tr>
                    <td>{{trans('welfare::view.Name event')}}</td>
                    <td>{{$eventItem->name}}</td>
                </tr>
                <tr>
                    <td>{{trans('welfare::view.Group event')}}</td>
                    <td>{{$eventItem->groupName}}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="table-responsive">
            <table>
                <tr>
                    <td>label</td>
                    <td>content</td>
                </tr>
            </table>
        </div>
    </div>
</div>
    <?php ?>
@endsection


