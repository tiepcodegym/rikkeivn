<?php 
use Rikkei\Project\Model\ProjectLog;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as ViewCore;

$projectLogTable = ProjectLog::getTableName();
$collectionModel = isset($projectLogs) ? $projectLogs : $dashboardLogs;
?>
<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive">
            <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
                <thead>
                    <tr>
                        <th class="width-10-per">{{ trans('project::view.STT') }}</th>
                        <th class="col-created_at">{{ trans('project::view.Time') }}</th>
                        <th class="col-author">{{ trans('project::view.Author') }}</th>
                        <th class="col-content">{{ trans('project::view.Content') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($collectionModel) && count($collectionModel))
                        <?php $i = ViewCore::getNoStartGrid($collectionModel); ?>
                        @foreach($collectionModel as $item)
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $item->created_at }}</td>
                                <td>{{ $item->author }}</td>
                                <td class="white-space-pre">{{ $item->content }}</td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="10" class="text-center">
                                <h2 class="no-result-grid">{{trans('project::view.No results found')}}</h2>
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