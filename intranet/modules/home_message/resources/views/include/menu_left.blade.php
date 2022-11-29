<?php

use Illuminate\Support\Facades\URL;
use Rikkei\Core\View\Menu;

$auth = \Rikkei\Team\View\Permission::getInstance()->getEmployee();
$menuActive = Menu::getFlagActive();
?>

<!-- MENU_MYOT-->
<div class="box box-solid">
    <div class="box-header with-border">
        <div class="pull-left ot-menu-title">
            <h3 class="box-title">{{ trans('HomeMessage::view.Manage home message') }}</h3>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ot">
            <li class="{{$menuActive=='message'?'active':''}}">
                <a href="{{ Url::route('HomeMessage::home_message.all-home-message') }}" class="menu-title"
                   id="register">
                    <i class="fa fa-list"></i> {{ trans('HomeMessage::view.List home message') }}
                </a>
            </li>
{{--            <li class="{{$menuActive=='group'?'active':''}}">--}}
{{--                <a href="{{ Url::route('HomeMessage::home_message.all-group') }}" class="menu-title" id="unapproved">--}}
{{--                    <i class="fa fa-list"></i> {{ trans('HomeMessage::view.List group') }}--}}

{{--                </a>--}}
{{--            </li>--}}
            <li class="{{$menuActive=='banner'?'active':''}}">
                <a href="{{ Url::route('HomeMessage::home_message.all-banner') }}" class="menu-title" id="unapproved">
                    <i class="fa fa-list"></i> {{ trans('HomeMessage::view.Home banner') }}

                </a>
            </li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->
