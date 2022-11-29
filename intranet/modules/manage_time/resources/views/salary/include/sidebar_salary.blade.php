<div class="box box-solid" style="overflow: hidden;">
    <div class="box-header with-border bg-aqua-active">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title"><i class="fa fa-user-circle-o"></i> {{ trans('manage_time::view.Information salary') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <style type="text/css">
            .salary-menu .active {
                font-weight: bold;
            }
        </style>
        <ul class="nav nav-pills nav-stacked salary-menu">
            <?php
                $classSalaryThisPeriod = '';
                $classSalaryList = '';
                if (isset($isSalaryThisPeriod) && $isSalaryThisPeriod) {
                    $classSalaryThisPeriod = 'active';
                } else {
                    $classSalaryList = 'active';
                }
            ?>
            <li>
                <a href="{{ route('manage_time::profile.salary.salary-detail') }}" class="{{ $classSalaryThisPeriod }}">
                    <i class="fa fa-circle-o"></i> {{ trans('manage_time::view.This salary') }}
                </a>
            </li>
            <li>
                <a href="{{ route('manage_time::profile.salary.salary-list') }}" class="{{ $classSalaryList }}">
                    <i class="fa fa-circle-o"></i> {{ trans('manage_time::view.Period salary ago') }}
                </a>
            </li>
        </ul>
    </div>
</div>
<!-- /. box -->