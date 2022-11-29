<!DOCTYPE html>
<html dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        @yield('css')
        <style type="text/css">
            .email-content * {
                font-size: 13px !important;
                line-height: 2.0;
            }
        </style>
    </head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="color: #000">
        <div id="wrapper" dir="ltr" style="word-wrap: break-word;">
            <div class="emailBody" style="width: 600px; max-width: 100%; margin: 0 auto; background: url({{ URL::asset('common/images/email/summer-background.png') }}) no-repeat top; background-size: 100% auto;">
                <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_container">
                                <tr width="600">
                                    <td align="center">
                                        <!-- Body -->
                                        <table border="0" cellpadding="0" cellspacing="0" width="600" id="template_body">
                                            <tr width="600" height="170">
                                                <td align="center">&nbsp;
                                                    @yield('before_content')
                                                </td>
                                            </tr>
                                            <tr width="600">
                                                <td align="center" id="body_content">
                                                    <!-- Content -->
                                                    </center>
                                                    <table border="0" cellpadding="0" cellspacing="0" width="360">
                                                        <tr width="360">
                                                            <td style="font-size: 13px !important; line-height: 2.0; text-align: left; margin-top: 10px ; min-height: 350px !important;">
                                                                @yield('content')
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    </center>
                                                    <!-- End Content -->
                                                </td>
                                            </tr>
                                            <tr width="600" height="60">
                                                <td>&nbsp;</td>
                                            </tr>
                                        </table>
                                        <!-- End Body -->
                                    </td>
                                </tr>
                                <tr width="600">
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
        </div>
    </body>
</html>
