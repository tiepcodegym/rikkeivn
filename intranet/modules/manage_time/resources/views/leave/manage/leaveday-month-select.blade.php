<div class="pager-select-filter{{ isset($class) ? ' ' . $class : '' }}">
    <ul class="pagination" title="{{ trans('manage_time::view.View by month') }}" data-toggle="tooltip">
        <li class="paginate-button previous">
            <a href="{{ $urlIndex }}?month={{ $arrayMonths['prev'] }}">
                <i class="fa fa-chevron-left"></i> 
                {{ $arrayMonths['prev'] }}
            </a>
        </li>
        <li class="paginate-button input-page">
            <input type="text" class="form-control input-datepicker" data-format="YYYY-MM" value="{{ $arrayMonths['current'] }}"
                   data-options="{{ json_encode(['maxDate' => $monthNow, 'useCurrent' => false]) }}">
        </li>
        @if ($arrayMonths['next'])
        <li class="paginate-button next">
            <a href="{{ $urlIndex }}?month={{ $arrayMonths['next'] }}">
                {{ $arrayMonths['next'] }} 
                <i class="fa fa-chevron-right"></i>
            </a>
        </li>
        @endif
    </ul>
</div>

