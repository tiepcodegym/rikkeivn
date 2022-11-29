<div class="timekeeping-personal-sidebar">
    <div class="box box-solid timekeeping-detail-sidebar">
        <div class="box-header with-border bg-aqua-active">
            <div class="pull-left managetime-menu-title">
                <h3 class="box-title"><i class="fa fa-user-circle-o"></i> {{ trans('manage_time::view.Information timekeeping') }}</h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-stacked salary-menu">
                <?php
                    $classTimekeepingThisPeriod = '';
                    $classTimekeepingList = '';
                    if (isset($isTimekeepingThisPeriod) && $isTimekeepingThisPeriod) {
                        $classTimekeepingThisPeriod = 'active';
                    } else {
                        $classTimekeepingList = 'active';
                    }
                ?>
                <li>
                    <a href="{{ route('manage_time::profile.timekeeping', ['id' => $idTimeKeepingMax]) }}" class="{{ $classTimekeepingThisPeriod }}">
                        <i class="fa fa-circle-o"></i> {{ trans('manage_time::view.This timekeeping') }}
                    </a>
                </li>
                <li>
                    <a href="{{ route('manage_time::profile.timekeeping-list') }}" class="{{ $classTimekeepingList }}">
                        <i class="fa fa-circle-o"></i> {{ trans('manage_time::view.Period timekeeping ago') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <!-- /. box -->
    
    <!-- Sidebar note  -->
    <div class="box box-solid timekeeping-note-sidebar">
        <div class="box-header with-border bg-aqua-active">
            <div class="pull-left managetime-menu-title">
                <h3 class="box-title"><i class="fa  fa-sticky-note-o"></i> {{ trans('manage_time::view.Note') }}</h3>
            </div>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="box-body no-padding">
            <ul class="nav nav-pills nav-note">
                <li><a><b>-</b>: {{ trans('manage_time::view.The date is greater than the max date in the import file') }}</a></li>
                <li><a><b>X</b>: {{ trans('manage_time::view.Work all day') }}</a></li>
                <li><a><b>X: 0.75</b>: {{ trans('manage_time::view.Work 3/4 day') }}</a></li>
                <li><a><b>X/2</b>: {{ trans('manage_time::view.Work half day') }}</a></li>
                <li><a><b>X: 0.25</b>: {{ trans('manage_time::view.Work 1/4 day') }}</a></li>
                <li><a><b>V</b>: {{ trans('manage_time::view.Off allday') }}</a></li>
                <li><a><b>V: 0.75</b>: {{ trans('manage_time::view.Off 3/4 day') }}</a></li>
                <li><a><b>V/2</b>: {{ trans('manage_time::view.Off half day') }}</a></li>
                <li><a><b>V: 0.25</b>: {{ trans('manage_time::view.Off 1/4 day') }}</a></li>
                <li><a><b>P</b>: {{ trans('manage_time::view.Paid leave all day') }}</a></li>
                <li><a><b>P: 0.75</b>: {{ trans('manage_time::view.Paid leave 3/4 day') }}</a></li>
                <li><a><b>P/2</b>: {{ trans('manage_time::view.Paid leave half day') }}</a></li>
                <li><a><b>P: 0.25</b>: {{ trans('manage_time::view.Paid leave 1/4 day') }}</a></li>
                <li><a><b>KL</b>:{{ trans('manage_time::view.Unpaid leave all day') }}</a></li>
                <li><a><b>KL: 0.75</b>: {{ trans('manage_time::view.Unpaid leave 3/4 day') }}</a></li>
                <li><a><b>KL/2</b>: {{ trans('manage_time::view.Unpaid leave half day') }}</a></li>
                <li><a><b>KL: 0.25</b>: {{ trans('manage_time::view.Unpaid leave 1/4 day') }}</a></li>
                <li><a><b>BS</b>: {{ trans('manage_time::view.Add attendance for all day') }}</a></li>
                <li><a><b>BS/2</b>: {{ trans('manage_time::view.Add attendance for half day') }}</a></li>
                <li><a><b>L</b>: {{ trans('manage_time::view.Holiday leave') }}</a></li>
                <li><a><b>M1</b>: {{ trans('manage_time::view.Late arrival in the morning') }}</a></li>
                <li><a><b>M2</b>: {{ trans('manage_time::view.Late arrival in the afternoon') }}</a></li>
                <li><a><b>S1</b>: {{ trans('manage_time::view.Early checking out at mid-day') }} </a></li>
                <li><a><b>S2</b>: {{ trans('manage_time::view.Early checking out at the end of the day') }}</a></li>
                <li><a><b>OT</b>: {{ trans('manage_time::view.Paid OT hours') }}</a></li>
                <li><a><b>OTKL</b>: {{ trans('manage_time::view.Unpaid OT hours') }}</a></li>
                <li><a><b>CT</b>: {{ trans('manage_time::view.Business trip all day') }}</a></li>
                <li><a><b>CT/2</b>: {{ trans('manage_time::view.Business trip half day') }}</a></li>
            </ul>
        </div>
    </div>
</div>
