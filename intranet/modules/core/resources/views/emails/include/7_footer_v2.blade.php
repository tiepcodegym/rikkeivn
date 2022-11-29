<?php
use Carbon\Carbon;

$currentYear = Carbon::now()->format('Y');
?>
<table border="0" cellpadding="0" cellspacing="0" width="800" id="template_footer" style="color: white; background: url({{ asset('common/images/email/bg-header-footer.png') }}); background-size: cover;">
    <tr>
        <td>
            <table border="0" cellpadding="0" cellspacing="0" style="padding: 30px 0;">
                <tr>
                    <td width="500" style="text-align: left; padding-left: 45px;">
                        <h1 style="font-size: 16px; color: white;">株式会社リッケイ</h1>
                        <div style="font-size: 13px; margin-top: 14px; color: white;">
                            <p><strong>東京本社:</strong> 〒108-0014 東京都港区芝4-13-4 田町第16藤島ビル3階</p>
                            <p><strong>大阪支社:</strong> 〒530-0047 大阪市北区西天満3-5-10オフィスポート大阪 2階</p>
                            <p style="margin-block-end: 0;"><strong>名古屋支社:</strong> 〒451-6040 愛知県名古屋市西区牛島町6-1名古屋ルーセントタワー40階</p>
                        </div>
                    </td>

                    <td width="210" style="text-align: right; padding-right: 45px;">
                        <div>
                            <table>
                                <tr>
                                    <td width="160">&nbsp;</td>
                                    <td>
                                        <a href="https://rikkeisoft.com/" style="text-decoration: none;" target="_blank">
                                            <img src="{{ URL::asset('/common/images/email/web.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                        </a>
                                    </td>
                                    <td width="10">&nbsp;</td>
                                    <td>
                                        <a href="https://www.facebook.com/rikkeijapan" style="text-decoration: none;display: inline-block;" target="_blank">
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
                                Phone: 030-6435-0754
                            </p>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <p style="font-size: 9px; color: white; text-align: right; padding-right: 45px; margin-block-start: 0; margin-block-end: 0;">Copyright @ {{ $currentYear }} Rikkei Inc. All right reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
