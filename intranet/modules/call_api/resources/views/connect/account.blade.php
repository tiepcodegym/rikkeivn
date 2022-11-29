<div class="box-header with-border">
    <h2 class="box-title">{{ trans('call_api::message.Sonar') }}</h2>
</div>
<div class="box-body">
    <p>
        <button class="btn-add post-ajax" data-url-ajax="{!!route('call_api::sonar.user.create')!!}"
                type="button">{!!trans('call_api::message.Create account')!!}
            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i>
        </button>
    </p>
    <p>
        <button class="btn-add btn-change-password" data-url-ajax="{!!route('call_api::sonar.user.change.password')!!}"
                type="button">{!!trans('call_api::message.Change password')!!}
            <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i>
        </button>
    </p>
</div>
