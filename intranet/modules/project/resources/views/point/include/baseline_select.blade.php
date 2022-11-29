<div class="filter-panel-left pager-filter">
    <ul class="pagination" title="{{ trans('project::view.View baseline') }}" data-toggle="tooltip">
        @if(isset($weekList['list'][$weekList['choose']-1]))
            <li class="paginate_button previous">
                <a href="{{ $weekList['list'][$weekList['choose']-1]['url'] }}">
                    <i class="fa fa-chevron-left"></i>
                    {{ isset($weekList['list'][$weekList['choose']-1]['woy']) && $weekList['list'][$weekList['choose']-1]['woy'] ? 'w.'.$weekList['list'][$weekList['choose']-1]['woy'] : '' }}
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
        @if(isset($weekList['list'][$weekList['choose']+1]))
            <li class="paginate_button next">
                <a href="{{ $weekList['list'][$weekList['choose']+1]['url'] }}">
                    {{ isset($weekList['list'][$weekList['choose']+1]['woy']) && $weekList['list'][$weekList['choose']+1]['woy'] ? 'w.'.$weekList['list'][$weekList['choose']+1]['woy'] : '' }}
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
            <a href="#">
            @foreach ($weekList['list'] as $weekInYear => $week)
                @if($weekList['choose'] == $weekInYear)
                    {{ $week['label'] }}
                    @break
                @endif
            @endforeach
            </a>
        </li>
    </ul>
</div>
