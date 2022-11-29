<?php
use Carbon\Carbon;

$currentYear = Carbon::now()->format('Y');
?>
<table border="0" cellpadding="0" cellspacing="0" width="800" id="template_footer" style="color: white; background: url({{ asset('common/images/email/bg-header-footer.png') }}); background-size: cover;">
    <tr>
        <td>
            <table border="0" cellpadding="0" cellspacing="0" style="padding: 30px 0;">
                <tr>
                    <td width="480" style="text-align: left; padding-left: 45px;">
                        <h1 style="font-size: 16px; color: white;">Rikkeisoft Corporation</h1>
                        <div style="font-size: 13px; margin-top: 14px; color: white;">
                            <p><strong>Head office:</strong> 21st Floor, Handico Tower, Pham Hung St., Nam Tu Liem District, Hanoi, Vietnam</p>
                            <p><strong>Da Nang office:</strong> 11F, VietNam News Agency Building, 81 Quang Trung St., Hai Chau Dist., Da Nang, Vietnam</p>
                            <p><strong>本社:</strong> 21st Floor Handico Tower Pham Hung St., Nam Tu Liem District, Hanoi, Vietnam</p>
                            <p><strong>Ho Chi Minh office:</strong> 7F, Maritime Safety South Building, 42 Tu Cuong St., Ward 4, Tan Binh Dist., Ho Chi Minh City, Vietnam</p>
                            <p><strong>Tokyo office:</strong> 3rd Floor, Fujishima Building, Tamachi 16 Street, 4-13-4 Shiba, Minato-ku, Tokyo, Japan</p>
                            <p style="margin-block-end: 0;"><strong>Osaka office:</strong> 2nd Floor, Office’ Port Osaka, 3-5-10 Nishitenma, Kita-ku, Osaka, Japan</p>
                        </div>
                    </td>

                    <td width="230" style="text-align: right; padding-right: 45px;">
                        <div>
                            <table>
                                <tr>
                                    <td width="160">&nbsp;</td>
                                    <td>
                                        <a href="https://rikkeisoft.com/en" style="text-decoration: none;" target="_blank">
                                            <img src="{{ URL::asset('/common/images/email/web.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                        </a>
                                    </td>
                                    <td width="10">&nbsp;</td>
                                    <td>
                                        <a href="https://www.facebook.com/rikkeisoftglobal" style="text-decoration: none;display: inline-block;" target="_blank">
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
                        <div style="font-size: 12px; color: white;">
                            <p>
                                <a href="mailto:contact@rikkeisoft.com" style="color: white;text-decoration: none;">contact@rikkeisoft.com</a>
                            </p>
                            <p style="color: white;">
                                Phone: (+81)3-6435-0754
                            </p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <p style="font-size: 9px; color: white; text-align: right; padding-right: 45px; margin-block-start: 0; margin-block-end: 0;">Copyright &copy; {{ $currentYear }} RikkeiSoft. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
