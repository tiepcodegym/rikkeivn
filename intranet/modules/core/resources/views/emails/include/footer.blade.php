<?php
use Carbon\Carbon;

$currentYear = Carbon::now()->format('Y');
?>
<table border="0" cellpadding="0" cellspacing="0" width="600" id="template_footer" style="background: #bd1e2c; color: white;">
    <tr>
        <td width="30">&nbsp;</td>
        <td>
            <table border="0" cellpadding="0" cellspacing="0" width="540">
                <tr width="540" height="10">
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td width="380" style="text-align: left;">
                        <h1 style="font-size: 15px; color: white;">Rikkeisoft Corporation</h1>
                        <div style="font-size: 9px;margin-top: 14px;margin-bottom: 22px; color: white;">
                            <p>Head Office: 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi, Vietnam</p>
                            <p>Da Nang Office: 11F, VietNam News Agency Building, 81 Quang Trung St., Hai Chau Dist., Da Nang, Vietnam</p>
                            <p>Ho Chi Minh City Office: 7F, Maritime Safety South Building, 42 Tu Cuong St., Ward 4, Tan Binh Dist., Ho Chi Minh City, Vietnam</p>
                            <p>Japan Office: 3F, Tamachi 16th Fujishima Building, 4-13-4 Shiba, Minato-ku, Tokyo, Japan </p>
                        </div>
                        <address style="font-size: 9px; color: white;">Copyright &copy; {{ $currentYear }} RikkeiSoft. All rights reserved.</address>
                    </td>

                    <td width="160" style="text-align: right;">
                        <div>
                            <table>
                                <tr>
                                    <td width="40">&nbsp;</td>
                                    <td>
                                        <a href="https://rikkeisoft.com/" style="text-decoration: none;" target="_blank">
                                            <img src="{{ URL::asset('/common/images/email/web.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                        </a>
                                    </td>
                                    <td width="10">&nbsp;</td>
                                    <td>
                                        <a href="https://www.facebook.com/rikkeisoft/" style="text-decoration: none;display: inline-block;" target="_blank">
                                            <img src="{{ URL::asset('/common/images/email/face.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                        </a>
                                    </td>
                                    <td width="10">&nbsp;</td>
                                    <td>
                                        <a href="https://www.youtube.com/channel/UCg4sqAGemXn5basWdzxEbVg" style="text-decoration: none;display: inline-block;" target="_blank">
                                            <img src="{{ URL::asset('/common/images/email/youtube.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div style="font-size: 9px;color: white;">
                            <p>
                                <a href="mailto:contact@rikkeisoft.com" style="color: white;text-decoration: none;">contact@rikkeisoft.com</a>
                            </p>
                            <p style="color: white;">
                                Phone: (+81)3-6435-0754
                            </p>
<!--                            <p style="color: white;">
                                Fax: (04) 3-623-1686
                            </p>-->
                            <span style="background-color: #bd1e2c; color: #bd1e2c">{{ date('Y-m-d H:i:s') }}</span>
                        </div>
                    </td>
                </tr>
                <tr width="540" height="15">
                    <td>&nbsp;</td>
                </tr>
            </table>
        </td>
        <td width="30">&nbsp;</td>
    </tr>
</table>
