{{--{!! $paginator->fragment($type)->links() !!}--}}
<?php
use Rikkei\Team\View\Config;

$link_limit = $paginator->perPage(); // maximum number of links (a little bit inaccurate, but will be ok for now)
?>

@if ($paginator->lastPage() >= 1)
    <div class="data-pager-info grid-pager-box">
         <span>{!! trans('project::view.Total :itemTotal entries / :pagerTotal page', [
                'itemTotal' => $paginator->total(),
                'pagerTotal' => $paginator->lastPage(),
                ]) !!}</span>
    </div>
    <div class="grid-pager-box-right">
        <div class="dataTables_length grid-pager-box">
            <label>{{ trans('project::view.Show') }}
                <select class="form-control input-sm" autocomplete="off">
                    @foreach(Config::toOptionLimit() as $option)
                        <option value="{{ $option['value'] }}" <?php
                            if ($option['value'] == $link_limit): ?> selected <?php endif; ?>
                            data-value="{{ $option['value'] }}"
                        >
                        {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="dataTables_paginate paging_simple_numbers grid-pager-box pagination-wrapper">
            <ul class="pagination">
                <li class="{{ ($paginator->currentPage() == 1) ? ' disabled' : '' }}">
                    <a href="{{ $paginator->url(1) }}"><span>&laquo;</span></a>
                </li>
                @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                    <?php
                    $half_total_links = ceil($link_limit / 2);
                    $from = $paginator->currentPage() - $half_total_links;
                    $to = $paginator->currentPage() + $half_total_links;
                    if ($paginator->currentPage() < $half_total_links) {
                        $to += $half_total_links - $paginator->currentPage();
                    }
                    if ($paginator->lastPage() - $paginator->currentPage() < $half_total_links) {
                        $from -= $half_total_links - ($paginator->lastPage() - $paginator->currentPage()) - 1;
                    }
                    ?>
                    @if ($from < $i && $i < $to)
                        <li class="{{ ($paginator->currentPage() == $i) ? ' active' : '' }}">
                            <a href="{{ $paginator->url($i) }}">{{ $i }}</a>
                        </li>
                    @endif
                @endfor
                <li class="{{ ($paginator->currentPage() == $paginator->lastPage()) ? ' disabled' : '' }}">
                    <a href="{{ $paginator->url($paginator->lastPage()) }}"><span>&raquo;</span></a>
                </li>
            </ul>
        </div>
    </div>
    <div class="clearfix"></div>
@endif
