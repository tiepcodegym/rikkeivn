<?php

use Rikkei\Core\Model\User;
use Rikkei\Core\View\Menu;
use Rikkei\Core\Model\Menu as MenuModel;

$menuSetting = MenuModel::getMenuSetting();
if (Menu::getActive() == 'setting') {
    $userSetting = ' active';
} else {
    $userSetting = '';
}
?>
<!-- Navbar Right Menu -->
@if(Auth::user())
    <style>
        .tooltip-inner {
            white-space: nowrap;
        }
    </style>
<div class="navbar-custom-menu">
    <ul class="nav navbar-nav" data-menu-main="right">
        <li class="dropdown menu-language">
            <?php
            $lang = Session::get('locale');
            switch ($lang) {
                case 'vi':
                    $icon = '<span class="ion-ios-world-outline"></span><span class="text"> VI </span><span class="fa fa-sort-desc"></span>';
                    break;
                case 'en':
                    $icon = '<span class="ion-ios-world-outline"></span><span class="text"> EN </span><span class="fa fa-sort-desc"></span>';
                    break;
                case 'jp':
                    $icon = '<span class="ion-ios-world-outline"></span><span class="text"> JA </span><span class="fa fa-sort-desc"></span>';
                    break;
                default:
                    $icon = '<span class="ion-ios-world-outline"></span><span class="text"> VI </span><span class="fa fa-sort-desc"></span>';
                    break;
            }
            ?>
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo $icon ?></a>
            <ul class="dropdown-menu">
                <li><a class="item-language" href="#" data-lang="vi"><span class=""> Tiếng Việt - VI</span></a></li>
                <li><a class="item-language" href="#" data-lang="en"><span class=""> English - EN</span></a></li>
                <li><a class="item-language" href="#" data-lang="jp"><span class=""> 日本語 - JA</span></a></li>
            </ul>
        </li>
        @include('notify::template.notify-menu')

        @if ($menuSetting && $menuSetting->id)
            <?php $menuSettingHtml = Menu::get($menuSetting->id, 1); ?>
            @if (e($menuSettingHtml))
                <li class="setting dropdown{{ $userSetting }}">
                    <a href="{{ URL::route('team::setting.team.index') }}" class="menu-setting-dropdow dropdown-toggle" data-toggle="dropdown" data-menu-slug="setting">
                        <i class="fa fa-gears"></i>
                    </a>
                    <a href="#" class="menu-setting-sidebar" data-toggle="control-sidebar">
                        <i class="fa fa-gears"></i>
                    </a>
                    <ul class="dropdown-menu">
                        {!! $menuSettingHtml !!}
                    </ul>
                </li>
            @endif
        @endif
        <!-- User Account Menu -->
        <li class="user user-menu">
            <a href="#">
                <span data-dom-flag="profile-avatar">
                    <?php $avatar = User::getAvatar(); ?>
                    @if($avatar)
                        <img src="{{ $avatar }}" class="user-image" alt="User Image">
                    @else
                        <i class="fa fa-user"></i>
                    @endif
                </span>
                <span class="hidden-xs">{{ User::getNickName() }}</span>
            </a>
        </li>
        <!-- Help View -->
        <li class="setting dropdown{{ $userSetting }}">
            <a href="{{ URL::route('help::display.help.view') }}">
                <i class="fa fa-fw fa-question-circle"></i>
            </a>           
        </li>

        <li class="logout">
            <a href="{{ URL::to('logout') }}" title="{{ trans('view.Logout') }}" data-toggle="tooltip" data-placement="bottom">
                <i class="fa fa-sign-out"></i>
            </a>
        </li>
    </ul>
</div><!-- /.navbar-custom-menu -->
@endif