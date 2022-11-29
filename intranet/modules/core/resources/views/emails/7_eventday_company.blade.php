<!DOCTYPE html>
<html dir="ltr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        @yield('css')
    </head>
    <body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="color: #000; height:100%;
            margin:0;
            line-height: 1.5;
            padding-left: 0 ;
            padding-right: 0;
            background: #E8E8E8;
            font-size: 18px;">
        <div id="wrapper" dir="ltr" style="word-wrap: break-word;">
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="0" cellspacing="0" width="650" id="template_container" style="background: #fff;">
                            <td>
                                <!-- Header -->
                                @yield('header')
                                <!-- End Header -->
                            </td>
                            <tr>
                                <td align="center" >
                                    <!-- Body -->
                                    <table border="0" cellpadding="0" cellspacing="0" width="650" id="template_body">
                                        <tr>
                                            <td id="body_content">
                                                <!-- Content -->
                                                <table border="0" cellpadding="0" cellspacing="0" width="650">
                                                    <tr>
                                                        <td>
                                                            <div id="body_content_inner" 
                                                                 style="
                                                                 min-height: 105px; padding: 0;">
                                                                @yield('content')
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
