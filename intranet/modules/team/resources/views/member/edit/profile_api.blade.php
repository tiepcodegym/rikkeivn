<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Team\Model\EmployeeContact;
?>

@extends('team::member.profile_normal')
@section('content_profile')
<div class="row">
    <div class="col-md-4">
        @include('call_api::connect.account')
    </div>
    <div class="col-md-4">
        @include('call_api::connect.redmine_user')
    </div>
    <div class="col-md-4">
        @include('call_api::connect.gitlab_user')
    </div>
</div>
<div class="row" id="setting_app_container">
    <div class="col-md-6">
        <div class="box-header with-border bold-label">
            <h2 class="box-title">{{ trans('call_api::message.Mail Application Password') }}</h2>
        </div>
        <div class="box-body">
            <form method="post" class="no-validate" data-form-submit="ajax" data-cb-success="formEmployeeInfoSuccess" novalidate="novalidate"
                  action="{{ route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => 'api']) }}">
                {!! csrf_field() !!}
                <div class="form-group row">
                    <div class="col-lg-8 col-xs-7">
                        <input type="text" name="app_password" class="form-control app-password" value="{{ $employeeModelItem->app_password }}"
                               autocomplete="off" disabled>
                    </div>
                    @if (!$disabledInput)
                    <div class="col-lg-4 col-xs-5 text-right form-action-btns">
                        <button class="btn btn-success btn-save-form hidden" type="submit">
                            {!!trans('team::view.Save')!!}
                            <i class="fa fa-spin fa-refresh loading-submit hidden"></i>
                        </button>
                        <button class="btn btn-primary btn-edit-form" type="button"><i class="fa fa-edit"></i></button>
                        <button class="btn btn-danger btn-cancel-form hidden" type="button"><i class="fa fa-close"></i></button>
                    </div>
                    @endif
                </div>
            </form>
            <button class="btn btn-danger btn-check-pass">
                {{ trans('team::view.Check pass') }}
                <i class="fa fa-spin fa-refresh hidden loading-check-pass"></i>
            </button>
        </div>
    </div>
    <div class="col-md-6">
        @include('team::member.setting.fields')
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box-header with-border">
            <h2 class="box-title">{{ trans('team::view.Contact display options') }}</h2>
        </div>
        <div class="box-body">
            <form method="post" class="no-validate" data-form-submit="ajax" data-cb-success="formEmployeeInfoSuccess" novalidate="novalidate"
                  action="{{ route('team::member.profile.save', ['employeeId' => $employeeModelItem->id, 'type' => 'setting']) }}">
                {!! csrf_field() !!}
                <div class="row">
                    <div class="col-md-4">
                        <label><strong>{{ trans('team::view.Birthday') }}</strong></label>
                        <div class="form-group">
                            <label>
                                <input type="radio"
                                       name="can_show_birthday"
                                       value="{!! EmployeeContact::SHOW_BIRTHDAY !!}"
                                       @if ($contactOption['can_show_birthday'] === EmployeeContact::SHOW_BIRTHDAY) checked @endif>
                                {{ trans('team::view.Display') }}
                            </label><br />
                            <label>
                                <input type="radio"
                                       name="can_show_birthday"
                                       value="{!! EmployeeContact::NOT_SHOW_BIRTHDAY !!}"
                                       @if ($contactOption['can_show_birthday'] === EmployeeContact::NOT_SHOW_BIRTHDAY) checked @endif>
                                {{ trans('team::view.Not display') }}
                            </label><br />
                            <label>
                                <input type="radio"
                                       name="can_show_birthday"
                                       value="{!! EmployeeContact::SHOW_ONLY_YEAR !!}"
                                       @if ($contactOption['can_show_birthday'] === EmployeeContact::SHOW_ONLY_YEAR) checked @endif>
                                {{ trans('team::view.Display only year') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label><strong>{{ trans('team::view.Phone') }}</strong></label>
                        <div class="form-group">
                            <label>
                                <input type="radio"
                                       name="can_show_phone"
                                       value="{!! EmployeeContact::SHOW_PHONE !!}"
                                       @if ($contactOption['can_show_phone'] === EmployeeContact::SHOW_PHONE) checked @endif>
                                {{ trans('team::view.Display') }}
                            </label><br/>
                            <label>
                                <input type="radio"
                                       name="can_show_phone"
                                       value="{!! EmployeeContact::NOT_SHOW_PHONE !!}"
                                       @if ($contactOption['can_show_phone'] === EmployeeContact::NOT_SHOW_PHONE) checked @endif>
                                {{ trans('team::view.Not display') }}
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label><strong>{{ trans('team::view.Mail') }}</strong></label>
                        <div class="form-group">
                            <label>
                                <input type="radio"
                                       name="dont_receive_system_mail"
                                       value="{!! EmployeeContact::DONT_RECEIVE_SYSTEM_MAIL !!}"
                                       @if ($contactOption['dont_receive_system_mail'] === EmployeeContact::DONT_RECEIVE_SYSTEM_MAIL) checked @endif>
                                {{ trans("team::view.Don't receive") }}
                            </label><br/>
                            <label>
                                <input type="radio"
                                       name="dont_receive_system_mail"
                                       value="{!! EmployeeContact::RECEIVE_SYSTEM_MAIL !!}"
                                       @if ($contactOption['dont_receive_system_mail'] === EmployeeContact::RECEIVE_SYSTEM_MAIL) checked @endif>
                                {{ trans("team::view.Receive") }}
                            </label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary btn-save-form" type="submit">
                    {!! trans('team::view.Save') !!}
                    <i class="fa fa-spin fa-refresh loading-submit hidden"></i>
                </button>
            </form>
        </div>
    </div>
</div>
<div class="modal" id="modal-confirm-change-password">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h3 class="text-center">Are your sure to change your Password!</h3>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn pull-left" data-dismiss="modal">Cancel</button>
                <button type="button" id="comment-analysis-css" class="btn btn-danger post-ajax" data-url-ajax="">
                    Confirm
                    <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i>
                </button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/4.4.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.full.min.js"></script>
<script>
    var urlcheckExistSetting = '{{ route("team::member.profile.check_exists", ["type" => "setting", "employeeId" => $employeeModelItem->id]) }}';
    var urlcheckAppPass = '{{ route("team::check-app-pass") }}';
    var textEnterPasswordToShow = '<?php echo trans('team::profile.Enter password to show') ?>';
    var textPasswordErrorTryAgain = '<?php echo trans('team::messages.Input password error, please try again') ?>';
    var checkAppPassSuccess = '<?php echo trans('team::messages.Check appPass successfully!') ?>';

    $('.btn-change-password').on('click', function (e) {
        e.preventDefault();
        let url_ajax = $(this).data('url-ajax');
        $('#modal-confirm-change-password .post-ajax').attr('data-url-ajax', url_ajax);
        $('#modal-confirm-change-password').modal('show');
    })
</script>
<script src="{{ CoreUrl::asset('team/js/view.js') }}"></script>
@stop
