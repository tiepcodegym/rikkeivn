<div class="box box-primary">
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked menu-ot">
            <li class="{{ (request()->is('admin/staff-are-late')) ? 'active' : '' }}">
                <a href="{{ route('manage_time::admin.staff-late.show') }}">
                    <i class="fa fa-list"></i> {{ trans('manage_time::view.Employee no late time period') }}
                </a>
            </li>
            <li class="{{ (request()->is('admin/staff-are-late/not-late')) ? 'active' : '' }}">
                <a href="{{ route('manage_time::admin.staff-late.not-late.show') }}">
                    <i class="fa fa-list"> </i>{{ trans('manage_time::view.Employee no late') }}
                </a>
            </li>
        </ul>
    </div>
</div>
