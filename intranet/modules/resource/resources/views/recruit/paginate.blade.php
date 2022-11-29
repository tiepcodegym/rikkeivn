<?php
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;

if (!isset($domainTrans) || !$domainTrans) {
    $domainTrans = 'team';
}
?>

<div class="grid-pager-redirect">
    <div class="data-pager-info grid-pager-box" role="status" aria-live="polite">
        <span>{!! trans($domainTrans . '::view.Total :itemTotal entries / :pagerTotal page', [
            'itemTotal' => $collectionModel->total(),
            'pagerTotal' => ceil($collectionModel->total() / $collectionModel->perpage()),
            ]) !!}</span>
    </div>

    <div class="grid-pager-box-right">
        <div class="dataTables_paginate paging_simple_numbers grid-pager-box pagination-wrapper">
            <div class="paging_simple_numbers grid-pager-box pagination-wrapper">
                <ul class="pagination">
                    <li class="paginate_button first-page<?php if($collectionModel->currentPage() == 1): ?> disabled<?php endif; ?>">
                        <a href="<?php if($collectionModel->currentPage() != 1): ?>{{ Config::urlParams(['page' => 1]) }}<?php else: ?>#<?php endif; ?>" data-page="1">
                            <i class="fa fa-angle-double-left"></i>
                        </a>
                    </li>
                    <li class="paginate_button previous<?php if($collectionModel->currentPage() == 1): ?> disabled<?php endif; ?>">
                        <a href="<?php 
                            if($collectionModel->currentPage() != 1): ?>{{ $collectionModel->previousPageUrl() }}<?php 
                            else: ?>#<?php endif; ?>" data-page="{{ $collectionModel->currentPage()-1 }}">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                    </li>
                    <li class="paginate_button">
                        <div action="{{ Config::urlParams(['page' => null]) }}" method="get" class="form-pager">
                            <span class="page_num" style="display: inline-block; border: 1px solid #ddd; padding-top: 4px;">{{ $collectionModel->currentPage() }}</span>
                        </div>
                    </li>
                    <li class="paginate_button next<?php if(!$collectionModel->hasMorePages()): ?> disabled<?php endif; ?>">
                        <a href="<?php 
                            if($collectionModel->hasMorePages()): ?>{{ $collectionModel->nextPageUrl() }}<?php
                            else: ?>#<?php endif; ?>" data-page="{{ $collectionModel->currentPage()+1 }}">
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </li>
                    <li class="paginate_button lastpage-page<?php if($collectionModel->lastPage() == $collectionModel->currentPage()): ?> disabled<?php endif; ?>">
                        <a href="<?php 
                            if($collectionModel->lastPage() != $collectionModel->currentPage()): ?>{{ Config::urlParams(['page' => $collectionModel->lastPage()]) }}<?php
                            else: ?>#<?php endif; ?>" data-page="{{ $collectionModel->lastPage() }}">
                            <i class="fa fa-angle-double-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>
