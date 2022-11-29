<?php
use Rikkei\Test\View\ViewTest;

$countQuestion = $questions->count();
$totalPage = ceil($countQuestion / ViewTest::PER_PAGE);
$currentPage = 1;
?>

@if ($totalPage > 1)
<div class="test-paginate">
    <a href="#" data-page="{{ $currentPage - 1 }}" class="prev-page hidden"><i class="fa fa-angle-double-left"></i></a>
    @for ($page = 1; $page <= $totalPage; $page++)
        <a href="#" data-page="{{ $page }}" {!! $page == $currentPage ? 'class="active"' : '' !!}>{{ $page }}</a>
    @endfor
    <a href="#" data-page="{{ $currentPage + 1 }}" class="next-page hidden"><i class="fa fa-angle-double-right"></i></a>
</div>
@endif
