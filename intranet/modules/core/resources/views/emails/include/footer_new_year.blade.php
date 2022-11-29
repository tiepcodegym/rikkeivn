<?php
use Rikkei\Core\Model\CoreConfigData;
use Carbon\Carbon;

$currentYear = Carbon::now()->format('Y');
$address = CoreConfigData::getValueDb('address_company');
?>
    <table border="0" cellpadding="0" cellspacing="0" width="1024" id="template_footer" style="background: #ffffff;">
        <tr>
            <td width="30">&nbsp;</td>
            <td>
                <table border="0" cellpadding="0" cellspacing="0" width="1000">
                    <tr width="540" height="10">
                        <td>&nbsp;</td>
                    </tr>
                    <tr>
                        <td width="924" style="text-align: left;">
                            <h1 style="font-size: 22px; color: #bf1e2e; margin-top: -1px;">Rikkeisoft Corporation</h1>
                            <div style="font-size: 10px;margin-top: 14px;margin-bottom: 15px; color: #bf1e2e;">
                                <?= '<p style="line-height: 14px">'.nl2br($address).'</p>' ?>
                            </div>
                            <address style="font-size: 10px; color: #bf1e2e;">Copyright &copy; {{ $currentYear }} RikkeiSoft. All rights reserved.</address>
                        </td>

                        <td width="100" style="text-align: right; vertical-align: top;">
                            <div style="height: 15px"></div>
                            <div style="padding-right: 0">
                                <table width="110px" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="30">&nbsp;</td>
                                        <td>
                                            <a href="{{ CoreConfigData::getValueDb('web_url') }}" style="text-decoration: none;" target="_blank">
                                                <img src="{{ asset('common/images/email_newyear/08.-Icon1.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                            </a>
                                        </td>
                                        <td width="40">&nbsp;</td>
                                        <td>
                                            <a href="{{ CoreConfigData::getValueDb('face_url') }}" style="text-decoration: none;display: inline-block;" target="_blank">
                                                <img src="{{ asset('common/images/email_newyear/09.-Icon2.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                            </a>
                                        </td>
                                        <td width="40">&nbsp;</td>
                                        <td>
                                            <a href="{{ CoreConfigData::getValueDb('youtube_url') }}" style="text-decoration: none;display: inline-block;" target="_blank">
                                                <img src="{{ asset('common/images/email_newyear/10.-Icon3.png') }}" style="max-width: 25px;" alt="RikkeiSoft" />
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div style="font-size: 10px;color: #bf1e2e; padding-top: 6px;line-height: 4px">
                                <p>
                                    <a href="'mailto:contact@rikkeisoft.com" style="color: #bf1e2e;text-decoration: none;">{{ CoreConfigData::getValueDb('email_contact') }}</a>
                                </p>
                                <p style="color: #bf1e2e;">
                                    Phone: (04) 3-623-1685
                                </p>
                                <p style="color: #bf1e2e;">
                                    Fax: (04) 3-623-1686
                                </p>
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
