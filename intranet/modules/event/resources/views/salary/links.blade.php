<div class="text-right">
    @if (isset($btnSendPass))
    <button class="btn btn-success margin-bottom-5" id="send_pass_mail_exists_btn" data-html="true"
            data-toggle="tooltip" title="{{ trans('event::view.send_mail_exists_password_tooltip') }}"
            data-url="{{ route('event::send.email.employees.salary.send_exists_pass') }}">
        <i class="fa fa-send"></i> 
        {{ trans('event::view.Send exists mail password') }}
        <i class="fa fa-spin fa-refresh loading hidden"></i>
    </button>
    <button class="btn btn-info margin-bottom-5" id="send_pass_mail_btn" data-html="true"
            data-toggle="tooltip" title="{{ trans('event::view.sent_mail_password_tooltip') }}"
            data-url="{{ route('event::send.email.employees.salary.send_pass') }}">
        <i class="fa fa-send"></i> 
        {{ trans('event::view.Send mail password') }} 
        <i class=" fa fa-spin fa-refresh loading hidden"></i>
    </button>
    @endif
    <a target="_blank" href="{{ route('event::send.email.employees.salary.list_files') }}" class="btn btn-primary margin-bottom-5">
        <i class="fa fa-history"></i>
        {{ trans('event::view.View history send mail') }}
    </a>
</div>

