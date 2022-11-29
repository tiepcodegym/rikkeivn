<!DOCTYPE html>
<html>
<head>
@include('layouts.include.css_default')
@yield('css')
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition skin-blue layout-top-nav">
<?php
if(!isset($langDomain) || !$langDomain) {
    $langDomain = 'core::view.';
}
?>
<div class="home-wrapper" id="homeWrapper">
@include('layouts.include.header')
@yield('after_header')
<!-- Full Width Column -->
    @yield('content')
    @yield('footer')
    @include("include.menu_mobile")
</div><!-- ./wrapper -->
<a href="#" class="top-up">
    <i class="fa fa-arrow-circle-up"></i>
</a>
<div id="popup-wrapper">
</div>
@include('layouts.include.js_default')
<!-- Add custom script follow page -->
@yield('script')
</body>
</html>