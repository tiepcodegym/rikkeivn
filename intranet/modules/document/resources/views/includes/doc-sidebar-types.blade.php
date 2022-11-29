<?php
use Rikkei\Document\View\DocConst;
?>

<div class="box-body">
    <form class="doc-search-form" method="get" action="{{ $routeSearch }}">
        <input type="text" class="form-control" name="search" placeholder="{{ trans('doc::view.Search') }}"
               value="{{ request()->get('search') }}">
        <button class="btn btn-default"><i class="fa fa-search"></i></button>
    </form>
    <ul class="list-unstyled list-types-bar">
        <li class="depth-0{{ $typeDoc ? '' : ' active' }}">
            <div class="inner-list">
                <i class="fa fa-folder"></i> <a href="{{ route('doc::list') }}">{{ trans('doc::view.All documnets') }}</a>
            </div>
        </li>
        {!! DocConst::toNestedList($listTypes, null, 0, $typeDoc ? $typeDoc->id : null) !!}
    </ul>
</div>