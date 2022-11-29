@extends('layouts.default-ng')
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\Model\CoreConfigData;

$versionAsset = CoreConfigData::get('view.assets_verson');
?>
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.15/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_qa/css/styles.css') }}" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
@endsection

@section('ng-controller', 'ng-controller="QAController"')

@section('content')
<div class="row">
    <div class="col-lg-2 col-md-3" ng-include="qaMenuLeft"></div>
    <div class="col-lg-10 col-md-9">
        <div class="box box-primary">
            <div class="box-body qa-list-cate" data-pager="page" 
                data-pager-url="{{ URL::route('qa::category.get.list') }}">
                <div ng-include="qaCateList"></div>
                <div ng-include="corePager"></div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('script')
<ng-include src="'{{ CoreUrl::asset('asset_qa/template/general.html') }}'" onload="loadedQATemplate = true"></ng-include>
<ng-include src="'{{ CoreUrl::asset('common/template/general.html') }}'" onload="loadedCoreTemplate = true"></ng-include>
<div ng-include="qaCateEdit"></div>
<script>
    var RKTransQA = JSON.parse('{!! json_encode(trans('qa::view')) !!}');
    var RKVarGlobalQA = {
        pathAssetTemplate: '{{ URL::asset('asset_qa/template') }}',
        assetVersion: {{ $versionAsset }},
        titlePage: 'Category list',
        pageActive: 'cate',
        
        urlQaCateList: '{{ URL::route('qa::category.get.list') }}',
        urlQaCateSave: '{{ URL::route('qa::category.save') }}',
        urlQaCateGetItem: '{{ URL::route('qa::category.get.item') }}'
    };
</script>
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.4/angular.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script src="{{ CoreUrl::asset('common/js/angular/app.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_qa/js/scripts.js') }}"></script>
@endsection

