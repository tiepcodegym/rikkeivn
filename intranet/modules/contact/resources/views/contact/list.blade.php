@extends('layouts.default')

@section('title')
{{ trans('contact::view.Contacts') }}
@endsection

@section('content')
<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\EmployeeContact;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-info">
            <div class="box-body">
                <div data-page-list="search" data-page-url="{!!route('contact::get.list')!!}"
                     data-page-more="append" data-page-load="btn">
                    <form class="form-inline" autocomplete="off"
                        action="{!!route('contact::get.list')!!}"
                        method="get" id="form-contact" autocomplete="off">
                        <div class="form-group margin-left-10">
                            <label for="search" class="required">{{trans('contact::view.Search')}}</label>
                            <input class="form-control input-field" type="text" id="search"
                                data-page-search="search" name="s" value=""
                                placeholder='{{ trans('contact::view.Name email') }}'/>
                        </div>

                        <button type="submit" class="btn btn-primary" data-page-search-btn="search" disabled id="submitBtn">
                            <i class="fa fa-search" data-page-loading-org="search"></i>
                            <i class="fa fa-spin fa-refresh hidden" data-page-loading="search"></i>
                        </button>

                        <span id="error-search" style="margin-left:10%">&#160;</span>
                    </form>
                    <h5 class="margin-left-10 text-italic" style="color:red">
                        {!! trans('contact::view.suggest_contact_title', ['url' => route('team::member.profile.index', ['employeeId' => $authId, 'type' => 'api'])]) !!}
                    </h5>
                    <div class="row margin-top-20">
                        <div class="col-md-12">
                            <h3 class="hidden" data-page-noresult="search">{!!trans('contact::view.no result')!!}</h3>
                            <div class="hidden" data-page-result="search">
                                <div data-page-item-wrapper="search" class="row">
                                    <div data-id="{id}" class="col-md-4">
                                        <div class="ct-item ct-item-wrap">
                                            <div class="ct-image">
                                                <p>
                                                    <a href="{profile_url}" target="">
                                                        <img src="{avatar_url}" alt="avatar" class="img-responsive ct-img"/>
                                                    </a>
                                                </p>
                                                <p>
                                                    <strong>{employee_code}</strong>
                                                </p>
                                            </div>
                                            <div class="ct-info">
                                                <a href="{profile_url}" target="">
                                                    <strong style="color: #0a0a0a">{name}</strong>
                                                </a><br/>
                                                <i class="fa fa-envelope-o color-mail"></i> <a href="mailto:{email}">{email}</a><br/>
                                                <i class="fa fa-users color-team"></i> <span data-text-line="1">{teamName}</span><br/>
                                                <i class="fa fa-skype color-skype"></i> {skype}<br/>
                                                <i class="fa fa-university color-bank"></i> {bank}<br/>
                                                @if (Permission::getInstance()->isAllow('contact::get.list'))
                                                    <i class="fa fa-phone color-phone"></i> <a href="tel:{mobile_phone}">{mobile_phone}</a><br/>
                                                    <i class="fa fa-birthday-cake color-birth"></i> {birthday}<br/>
                                                    <i class="fa fa-calendar-times-o" style="color: red"></i> {trial_date}<br/>
                                                    <i class="fa fa-calendar-check-o" style="color: green"></i> {offcial_date}<br/>
                                                    <i class="fa fa-sticky-note"></i> {contract}<br/>
                                                @else
                                                    <i class="fa fa-phone color-phone" data-can-show-phone="{can_show_phone}"></i> <a href="tel:{mobile_phone}">{mobile_phone}</a><br/>
                                                    <i class="fa fa-birthday-cake color-birth" data-can-show-birthday="{can_show_birthday}"></i> {birthday}<br/>
                                                @endif
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-left margin-top-20">
                                    <button type="button" class="btn btn-primary"
                                        data-page-more-btn="search">
                                        {!!trans('contact::view.load more')!!}
                                        <i class="fa fa-spin fa-refresh hidden" data-page-loading="search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link rel="stylesheet" href="{!!CoreUrl::asset('assets/contact/css/contact.css')!!}" />
@endsection

@section('script')
<script src="{!! CoreUrl::asset('asset_help/js/help.js') !!}"></script>
<script>
    var contactGetListUrl = '{{ route("contact::get.list") }}';
    const NOT_SHOW_PHONE = '{!! EmployeeContact::NOT_SHOW_PHONE !!}';
    const NOT_SHOW_BIRTHDAY = '{!! EmployeeContact::NOT_SHOW_BIRTHDAY !!}';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script>
    jQuery(document).ready(function ($) {
        let formSubmit = $("#form-contact").validate({
            errorPlacement: function(error, element) {
                if (element.attr('name') == 's') {
                    error.insertAfter('#error-search');
                } else{
                    error.insertAfter(element);
                }
            },
            rules: {
                s: {
                    required: true,
                    minlength: 3,
                }
            },
            messages: {
                s: {
                    required: "Bắt buộc nhập",
                    minlength: "Độ dài tối thiểu 3 kí tự"
                }
            }
        });
        formSubmit.form();
    });
</script>
<script src="{!!CoreUrl::asset('assets/contact/js/contact.js')!!}"></script>

@endsection
