<div id="main-heaer-padding-top">
    <header class="main-header"  id="main-heaer-top">
        <!-- Logo -->
        <a href="{{ URL::to('/') }}" class="logo" id="logo">
            <span class="logo-lg">
                <img src="{{ asset('/common/images/rikkei_logo.png') }}" class="img-logo-desk" />
            </span>
        </a>
        <nav class="navbar navbar-static-top">
            <div class="container-fluid">
                <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                    <span class="sr-only">Toggle navigation</span>
                </a>
                @include("include.menu_main")
                @include("include.menu_right")
            </div><!-- /.container-fluid -->
        </nav>
        <div class="clearfix"></div>
    </header>
</div>
