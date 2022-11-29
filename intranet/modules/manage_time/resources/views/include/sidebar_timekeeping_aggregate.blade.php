<?php
    use Carbon\Carbon;
    use Rikkei\ManageTime\Model\TimekeepingTable;
    use Rikkei\ManageTime\View\TimekeepingPermission;

    $teamIdAllowCreate = TimekeepingPermission::getTeamIdAllowCreate();
    $listMenuTimekeeping = TimekeepingTable::getTimekeepingTablesList($teamIdAllowCreate, Carbon::now()->year);
?>

<div class="box box-solid" id="box_register">
    <div class="box-header with-border">
        <div class="pull-left managetime-menu-title">
            <h3 class="box-title">{{ trans('manage_time::view.Timekeeping aggregate from time to time') }}</h3>
        </div>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked managetime-menu">
            @if (count($listMenuTimekeeping))
                @foreach ($listMenuTimekeeping as $item)
                    <?php
                        $classActive = '';
                        if ($item->timekeeping_table_id == $timeKeepingTable->id) {
                            $classActive = 'active';
                        }
                    ?>
                    <li class="timekeeping-time-to-time">
                        <a class="{{ $classActive }}" href="{{ route('manage_time::timekeeping.timekeeping-aggregate', ['timekeepingTableId' => $item->timekeeping_table_id]) }}" style="cursor: pointer;">
                            <i>{{ $item->timekeeping_table_name }}</i>
                        </a>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>
</div>
<!-- /. box -->