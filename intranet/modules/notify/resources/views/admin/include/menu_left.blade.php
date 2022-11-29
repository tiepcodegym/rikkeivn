<?php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\URL;
    $auth = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
?>

<!-- MENU_MYOT-->
<div class="box box-solid">
    <div class="box-header with-border">
        <div class="pull-left ot-menu-title">
            <h3 class="box-title">{{ trans('notify::view.notify_title') }}</h3>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ot">
            <li>
                <a href="{{ Url::route('notify::admin.notify.index') }}" class="menu-title" id="register">
                    <i class="fa fa-list"></i> {{ trans('notify::view.notify_list') }}
                </a>
            </li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->
