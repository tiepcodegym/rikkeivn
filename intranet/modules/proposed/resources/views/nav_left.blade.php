<?php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\URL;
    $auth = Auth::user()->getEmployee();

    $active = '';
    if (request()->is('proposed/manage-proposed')
        || request()->is('proposed/manage-proposed/edit*')) {
        $active = 'active';
    }
?>
<style>
    .breadcrumb {
        display: none;
    }
</style>
<!-- MENU_MYOT-->
<div class="box box-solid">
    <div class="box-header with-border">
        <div class="pull-left ot-menu-title">
            <h3 class="box-title">{{ trans('proposed::view.Manage proposed') }}</h3>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ot">
            <li class="{{ $active }}">
                <a href="{{URL::route('proposed::manage-proposed.index')}}" class="menu-title">
                    <i class="fa fa-list"></i> {{ trans('proposed::view.List proposed') }}
                </a>
            </li>
{{--            <li class="{{ (request()->is('proposed/manage-proposed/category')) ? 'active' : '' }}">--}}
{{--                <a href="{{URL::route('proposed::manage-proposed.category.index')}}" class="menu-title">--}}
{{--                    <i class="fa fa-list"></i> {{ trans('proposed::view.List proposed category') }}--}}

{{--                </a>--}}
{{--            </li>--}}
        </ul>
    </div>
    <!-- /.box-body -->
</div>
<!-- /. box -->
