<?php
use Rikkei\Core\View\CoreUrl;
use Rikkei\Core\View\Menu as CoreMenu;
?><meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>
    @yield('title')
    Rikkeisoft Intranet
</title>
<script>
    var baseUrl = '{{ url('/') }}/';
    var currentUrl = '{{ app('request')->url() }}/';
    var siteConfigGlobal = {
        token: '{{ csrf_token() }}',
        base_url: '{{ url('/') }}/',
        menu_active: '{{CoreMenu::getFlagActive()}}',
    };
</script>
<!-- Tell the browser to be responsive to screen width -->
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<link rel="icon" href="{{ URL::asset('favicon.ico') }}" type="image/x-icon">
<link rel="Shortcut Icon" type="image/x-icon" href="{{ URL::asset('favicon.ico') }}">

<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/css/AdminLTE.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.3.8/css/skins/_all-skins.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
<?php /*
<!-- Bootstrap 3.3.6 -->
<link rel="stylesheet" href="{{ URL::asset('adminlte/bootstrap/css/bootstrap.min.css') }}" />
<!-- Font Awesome -->
<link rel="stylesheet" href="{{ URL::asset('lib/font-awesome/css/font-awesome.min.css') }}">
<!-- Ionicons -->
<!--<link rel="stylesheet" href="{{ URL::asset('lib/ionicons-2.0.1/css/ionicons.min.css') }}">-->
<!-- Theme style -->
<link rel="stylesheet" href="{{ URL::asset('adminlte/dist/css/AdminLTE.min.css') }}" />
<link rel="stylesheet" href="{{ URL::asset('adminlte/dist/css/skins/_all-skins.min.css') }}" />
 */ ?>
<!-- common style -->
<link rel="stylesheet" href="{{ CoreUrl::asset('common/css/style.css') }}" />
<link rel="stylesheet" href="{{ CoreUrl::asset('asset_notify/css/notify.css') }}" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.1.0/css/flag-icon.min.css" rel="stylesheet">
