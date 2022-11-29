<?php
use Rikkei\Core\View\Menu;
?>
<!-- Collect the nav links, forms, and other content for toggling -->
<div class="collapse navbar-collapse pull-left" id="navbar-collapse">
    <ul class="nav navbar-nav" data-menu-main="left">
        {!! Menu::get() !!}
    </ul>
    <?php /*
    <form class="navbar-form navbar-left" role="search" type="get" action="">
        <div class="form-group">
            <input type="text" class="form-control" id="navbar-search-input" placeholder="Search">
        </div>
    </form> */ ?>
</div><!-- /.navbar-collapse -->

