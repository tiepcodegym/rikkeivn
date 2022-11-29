<?php
use Rikkei\News\View\ViewNews;
use Rikkei\Core\View\Form as FormView;
?>
@if(isset($activeCategories) && count($activeCategories))
<div class="nav-news-categories">
    <div class="container">
        <div class="row" id="myNavbar">
            <div class="col-sm-10">
                <nav id="cssmenu">
                    <div id="head-mobile"></div>
                    <div class="button"></div>
                    <ul>
                        @foreach ($activeCategories as $category)
                            <?php
                            if (!isset($category->title) || !isset($category->slug) ||
                                !$category->title || !$category->slug) {
                                continue;
                            }
                            ?>
                                @if( $category->parent_id == 0 )
                                <li class="{{ $activeMenu == $category->slug ? 'active' : '' }} {{ url()->current() }}">
                                    <a href="{{ ViewNews::getCategoryUrl($category->slug) }}">
                                        <span class="bc-text bc-element">{{ $category->title }}</span>
                                    </a>
                                @endif
                                @if( !$category->children->isEmpty() )
                                    <ul>
                                        @foreach($category->children as $subMenuItem)
                                            <li><a href="{{ ViewNews::getCategoryUrl($subMenuItem->slug) }}">{{ $subMenuItem->title }}</a></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                            @endforeach
                    </ul>
                </nav>
            </div>
            <div class="col-sm-2">
                <form class="form-search" action="{{ (isset($isYume) && $isYume) ? Request::url() : URL::route('news::post.index') }}">
                    <input type="text" name="search" class="input-search" placeholder="Search..">
                </form>
            </div>
        </div>
    </div>
</div>
@endif
