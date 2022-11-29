<?php 
use Rikkei\Project\Model\ProjectLog;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as ViewCore;

$projectLogTable = ProjectLog::getTableName();
$collectionModel = $projectLogs;
?>

<h4 class="box-title padding-left-15">{{trans('project::view.Activity')}}</h4>
@include('team::include.filter', ['idProject' => $project->id])
<div class="table-responsive">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data">
        <thead>
            <tr>
                <th class="width-10-per sorting {{ Config::getDirClass('id') }}" data-order="id" data-dir="{{ Config::getDirOrder('id') }}">{{ trans('project::view.STT') }}</th>
                <th class="col-created_at sorting {{ Config::getDirClass('created_at') }}" data-order="created_at" data-dir="{{ Config::getDirOrder('created_at') }}">{{ trans('project::view.Time') }}</th>
                <th class="col-author sorting {{ Config::getDirClass('author') }}" data-order="author" data-dir="{{ Config::getDirOrder('author') }}">{{ trans('project::view.Author') }}</th>
                <th class="col-content sorting {{ Config::getDirClass('content') }}" data-order="content" data-dir="{{ Config::getDirOrder('content') }}">{{ trans('project::view.Content') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr class="filter-input-grid">
                <td>&nbsp;</td>
                <td>
                    <div class="rows">
                        <input type="text" name="filter[{{ $projectLogTable }}.created_at]" value="{{ Form::getFilterData("{$projectLogTable}.created_at") }}" placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control width-100" />
                    </div>
                </td>
                <td>
                    <div class="rows">
                        <input type="text" name="filter[{{ $projectLogTable }}.author]" value="{{ Form::getFilterData("{$projectLogTable}.author") }}" placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control width-100" />
                    </div>
                </td>
                <td>
                    <div class="rows">
                        <input type="text" name="filter[{{ $projectLogTable }}.content]" value="{{ Form::getFilterData("{$projectLogTable}.content") }}" placeholder="{{ trans('project::view.Search') }}..." class="filter-grid form-control width-100" />
                    </div>
                </td>
            </tr>
            @if(isset($collectionModel) && count($collectionModel))
                <?php $i = ViewCore::getNoStartGrid($collectionModel); ?>
                @foreach($collectionModel as $item)
                    <tr>
                        <td>{{ $i }}</td>
                        <td>{{ $item->created_at }}</td>
                        <td>{{ $item->author}}</td>
                        <td>{{ $item->content }}</td>
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
@include('team::include.pager')
     
