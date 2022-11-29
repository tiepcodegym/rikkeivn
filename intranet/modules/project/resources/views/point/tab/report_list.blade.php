<?php
use Carbon\Carbon;
use Rikkei\Project\Model\ProjPointReport;
use Rikkei\Project\View\View;
use Rikkei\Core\View\View as ViewCore;

$reportLabel = ProjPointReport::pointLabel();
?>

<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th class="col-id" style="width: 20px;">{{ trans('project::view.No.') }}</th>
                        <th class="">{{ trans('project::view.Week') }}</th>
                        <th class="">{{ trans('project::view.First report') }}</th>
                        <th class="">{{ trans('project::view.Last report') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php $i = ViewCore::getNoStartGrid($collectionModel); ?>
                        @foreach($collectionModel as $item)
                            <?php $dayOfWeek = View::getFirstLastDayOfWeek(Carbon::parse($item->created_at)); ?>
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $dayOfWeek[1]->format('Y-m-d') . ' => ' . $dayOfWeek[2]->format('Y-m-d') }}</td>
                                <td>{{ $item->changed_at ? Carbon::parse($item->changed_at)->format('Y-m-d H:i:s') : '' }}</td>
                                <td>{{ $item->last_report ? Carbon::parse($item->last_report)->format('Y-m-d H:i:s') : '' }}</td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">
                                <h2 class="no-result-grid">{{ trans('project::view.No results found') }}</h2>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="box-body">
            @include('team::include.pager', ['domainTrans' => 'project'])
        </div>
    </div>
</div>