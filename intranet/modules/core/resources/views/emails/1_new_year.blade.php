<!DOCTYPE html>
<html dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        @yield('css')
    </head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="color: #000">
        <div id="wrapper" dir="ltr" style="word-wrap: break-word;">
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
                            <tr>
                                <td align="center" valign="top">
                                    <!-- Header -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_header">
                                        <tr>
                                            <td id="header_wrapper" style="">
                                                <div style="max-height: 0; max-width: 0">
                                                    <div style="width: 600px; height: 100px; background-color: #bd1e2c;"></div>
                                                </div>
                                                <table>
                                                    <tr width="300" height="20">
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                    <tr>
                                                        <td width="30">&nbsp;</td>
                                                        <td>
                                                            <div style="display: inline-block; float: left;">
                                                                <a href="{{ URL::to('/') }}" style="display: inline-block;">
                                                                    <img src="{{ URL::asset('/common/images/email/logo-email.png') }}" 
                                                                        alt="Rikkeisoft Intranet" class="img-responsive" style="max-width: 102px; max-height: 60px;"/>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr width="300" height="20">
                                                        <td>&nbsp;</td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Header -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="background: url({{ URL::asset('common/images/email/dao-mo.png') }}) no-repeat top right;">
                                    <!-- Body -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                        <tr width="600" height="40">
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td id="body_content">
                                                <!-- Content -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="600">
                                                    <tr>
                                                        <td>
                                                            <div id="body_content_inner" style="font-size: 15px; min-height: 105px;">
                                                                @yield('content')
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                        <tr width="600" height="40">
                                            <td>&nbsp;</td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <!-- Footer -->
                                    @include('core::emails.include.footer')
                                    <!-- End Footer -->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>