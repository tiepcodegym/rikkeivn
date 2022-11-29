<?php
use Rikkei\Team\View\Permission;
?>
<div class="filter-panel-left panel-left-link">
    @if($reportSubmitAvai)
        <button data-url-ajax="{{ URL::route('project::point.report', ['id' => $project->id]) }}" 
            class="btn-add post-ajax btn-add-task hidden is-submit-report" type="button" id="submit-report">{{ trans('project::view.Report') }}
        </button>
        <button data-url-ajax="{{ URL::route('project::point.report', ['id' => $project->id]) }}" 
            class="btn-add btn-add-task is-report" type="button" data-noti="{{trans('project::message.Input not change')}}:" data-title="{{trans('project::message.Are you sure report?')}}">{{ trans('project::view.Report') }} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh-btn"></i></button>
    @endif
     <a href="{{ URL::route('project::plan.comment', ['projectId' => $project->id]) }}" target="_blank" class="btn-add-task">{{ trans('project::view.Project Plan') }}</a>
    <a href="{{ URL::route('project::project.edit', ['id' => $project->id]) }}" target="_blank" class="btn-add-task">{{ trans('project::view.View workorder') }}</a>
    <a href="{{ URL::route('project::point.dashboard.help') }}" target="_blank" class="btn-add-task">{{ trans('project::view.Help') }}</a>
    @if ($weekList)
        &nbsp;&nbsp;
        <ul class="pagination" title="{{ trans('project::view.View baseline') }}" data-toggle="tooltip">
            @if ($weekList[0])
                <li class="paginate_button previous">
                    <a href="{{ $weekList[0]['url'] }}">
                        <i class="fa fa-chevron-left"></i>
                        <span>{{ isset($weekList[0]['woy']) && $weekList[0]['woy'] ? 'w.'.$weekList[0]['woy'] : '' }}</span>
                    </a>
                </li>
            {{--
            @else
                <li class="paginate_button previous disabled">
                    <a href="#">
                        <i class="fa fa-chevron-left"></i>
                    </a>
                </li>
            --}}
            @endif

            @if ($weekList[2])
                <li class="paginate_button next">
                    <a href="{{ $weekList[2]['url'] }}">
                        {{ isset($weekList[2]['woy']) && $weekList[2]['woy'] ? 'w.'.$weekList[2]['woy'] : '' }}
                        <i class="fa fa-chevron-right"></i>
                        
                    </a>
                </li>
            {{--
            @else
                <li class="paginate_button next disabled">
                    <a href="#">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </li>
            --}}
            @endif

            <li class="paginate_button active">
                <a href="#">{{ $weekList[1]['label'] }}</a>
            </li>
        </ul>
    @endif
</div>
