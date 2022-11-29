@extends('layouts.default')
<?php
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\CoreUrl;
use Illuminate\Support\Facades\Config;
use Rikkei\Tag\View\TagConst;

$tabActive = CookieCore::get('tab-keep-status-tag-field');
if (!$tabActive) {
    $tabActive = 'project';
}
$versionAsset = Config::get('view.assets_verson');
?>

@section('title')
{{ trans('tag::view.Field Manage') }}
@endsection

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.1/css/bootstrap-colorpicker.min.css" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/jquery.tagit.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('lib/tag-it/css/tagit.ui-zendesk.min.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_tag/css/styles.css') }}" />
@endsection

@section('content')
<div class="row" ng-app="RkTagApp">
    <div class="col-sm-12" ng-controller="fieldManageController">
        <div class="nav-tabs-custom tab-keep-status tag-field-manage" data-type="tag-field">
            <ul class="nav nav-tabs">
                <li<?php if($tabActive == 'project'): ?> class="active"<?php endif; ?>>
                    <a href="#project" data-toggle="tab" data-type="project"><strong>{{ trans('tag::view.Project') }}</strong>
                        <i class="fa fa-spin fa-refresh hidden project"></i>
                    </a>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-tag-wrapper tab-pane<?php if($tabActive == 'project'): ?> active<?php endif; ?>" 
                    id="project">
                    <field-list-tree data-type-id="{{ TagConst::SET_TAG_PROJECT }}"
                        ng-bind-html-compile="funcGetHtmlFieldManage" 
                        ng-init="funcSetRootTree({{ TagConst::SET_TAG_PROJECT }})"></field-list-tree>
                </div>
            </div>
        </div>
    </div>
    
</div>

<div class="field-manage-modal-wrapper"></div>
<div class='response-notifications top-right'></div>
@endsection


@section('script')
@include ('tag::include.translate')
<script>
    var RKVarGlobalTag = {
        pathAssetTemplate: '{{ URL::asset('asset_tag/template') }}',
        assetVersion: {{ $versionAsset }},
        fieldPath: JSON.parse('{!! json_encode($fieldsPath) !!}'),
        urlFieldSave: '{{ URL::route('tag::field.manage.save') }}',
        urlFieldDelete: '{{ URL::route('tag::field.manage.delete') }}',
        urlFieldGetItem: '{{ URL::route('tag::field.manage.get.item') }}',
        urlFieldGetTagItem: '{{ URL::route('tag::field.manage.get.tag.item') }}',
        urlFieldDeleteTagItem: '{{ URL::route('tag::field.manage.tag.delete') }}',
        urlFieldAddTagItem: '{{ URL::route('tag::field.manage.tag.add') }}',
        urlFieldApproveTagItem: '{{ URL::route('tag::field.manage.tag.approve') }}',
        urlFieldCountTag: '{{ URL::route('tag::field.manage.tag.count') }}',
        urlTagSave: '{{ URL::route('tag::field.manage.tag.save') }}',
        urlTagReviewLinkSubmit: '{{ URL::route('tag::field.manage.tag.review.link') }}',
        fieldStatus: JSON.parse('{!! json_encode(TagConst::fieldStatus()) !!}'),
        fieldTypes: JSON.parse('{!! json_encode(TagConst::fieldTypes()) !!}'),
        tagStatusApprove: {{ TagConst::TAG_STATUS_APPROVE }},
        tagStatusReview: {{ TagConst::TAG_STATUS_REVIEW }},
        fieldTypeTag: {{ TagConst::FIELD_TYPE_TAG }},
        fieldTypeInfo: {{ TagConst::FIELD_TYPE_INFO }}
    };
</script>
@include('tag::include.ng-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/mouse0270-bootstrap-notify/3.1.7/bootstrap-notify.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.1/js/bootstrap-colorpicker.min.js"></script>
<script src="{{ URL::asset('lib/tag-it/js/tag-it.min.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/general.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/app.js') }}"></script>
<script src="{{ CoreUrl::asset('asset_tag/js/field-manage.js') }}"></script>
@endsection