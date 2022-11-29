<li class="dropdown noti-menu no-hover" id="notify_menu">
    <a href="#" class="dropdown-toggle notify-toggle" data-toggle="dropdown"
       data-reset-url="{{ route('notify::reset_noti_num') }}">
        <i class="fa fa-bell-o"></i>
        <?php
        $notifyNum = auth()->user()->notify_num;
        ?>
        <span class="label label-warning notify-num">{{ !$notifyNum ? '' : ($notifyNum > 99 ? '99+' : $notifyNum) }}</span>
    </a>
    <ul class="dropdown-menu noti-contain">
        <li class="noti-header text-center">
            <a href="#" class="refresh">{{ trans('notify::view.Refresh') }} <i class="fa fa-refresh"></i></a>
        </li>
        <li class="noti-body">
            <ul class="notify-list list-unstyled" id="notify_list" data-url="{{ route('notify::load_data') }}"></ul>
        </li>
        <li class="text-center noti-loading hidden">
            <a href="#"><i class="fa fa-refresh fa-spin"></i></a>
        </li>
        <li class="none-item text-center">
            <a href="#">{{ trans('notify::view.None notify') }}</a>
        </li>
        <li class="footer view-all hidden">
            <a href="{{ route('notify::index') }}">{{ trans('notify::view.View all') }}</a>
        </li>
    </ul>
</li>

<div class="modal" tabindex="-1" role="dialog" id="modal-popup-notify" style="padding-top: 5%;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title">{!! trans('core::view.Notification') !!}</h3>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <p class="content content-release-notes">#</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
