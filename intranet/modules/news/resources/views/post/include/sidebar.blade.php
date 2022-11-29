<?php
use Rikkei\News\View\ViewNews;
?>
<div class="blog-sidebar-wrapper col-md-3">
    <div class="box box-primary box-solid collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title">
                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                    <i class="fa fa-bars"></i>
                </button>
            </h3>
        </div>
        <div class="box-body">
            <div class="blog-sidebar">
                <div class="blog-search">
                    <form method="get" action="{{ (isset($isYume) && $isYume) ? Request::url() : URL::route('news::post.index') }}">
                        <div class="search-box">
                            <input class="search-input" name="search" value="{{ $searchParams }}" placeholder="Tìm kiếm ..." />
                            <button type="submit" class="search-btn">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
                @if (isset($activeCategories) && count($activeCategories))
                    <div class="blog-category">
                        <div class="bc-header">
                            <strong>Danh mục</strong>
                        </div>
                        <div class="bc-content">
                            <ul>
                                @foreach ($activeCategories as $category)
                                    <?php
                                    if (!isset($category['title']) || !isset($category['slug']) ||
                                        !$category['title'] || !$category['slug']) {
                                        continue;
                                    }
                                    ?>
                                    <li>
                                        <a href="{{ ViewNews::getCategoryUrl($category['slug']) }}">
                                            <i class="fa fa-chevron-right bc-icon bc-element"></i> &nbsp;&nbsp;
                                            <span class="bc-text bc-element">{{ $category['title'] }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
<?php /*

<div class="col-md-4 blog-sidebar-wrapper">
    <div class="blog-sidebar">
        <div class="blog-search">
            <form method="get" action="{{ URL::route('news::post.index') }}">
                <div class="search-box">
                    <input class="search-input" name="search" value="{{ $searchParams }}" placeholder="Tìm kiếm ..." />
                    <button type="submit" class="search-btn">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
        @if (isset($activeCategories) && count($activeCategories))
            <div class="blog-category">
                <div class="bc-header">
                    <strong>Danh mục</strong>
                </div>
                <div class="bc-content">
                    <ul>
                        @foreach ($activeCategories as $category)
                            <?php
                            if (!isset($category['title']) || !isset($category['slug']) ||
                                !$category['title'] || !$category['slug']) {
                                continue;
                            }
                            ?>
                            <li>
                                <a href="{{ ViewNews::getCategoryUrl($category['slug']) }}">
                                    <i class="fa fa-chevron-right bc-icon bc-element"></i> &nbsp;&nbsp;
                                    <span class="bc-text bc-element">{{ $category['title'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
*/ ?>