<?php
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

$devTypes = getOptions::getInstance()->getDevTypeOptions();
$textContract = 'Tất cả loại hợp đồng';
if ($contractTypes) {
    $textContract = 'Hợp đồng: ' . implode(', ', getOptions::listWorkingTypeInternal('vi'));
}
?>

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="{{ asset('resource/css/export-stats.css') }}" />
    </head>
    <body>
        <table>
            <tbody>
                <tr>
                    <td></td>
                    <td colspan="10" class="sheet-title">
                        {{ trans('resource::view.Recruitment statistics')
                            . ' ' . ($month ? trans('resource::view.Month') . ' ' . $month : '')
                            . ' ' . trans('resource::view.Year') . ' ' . $year }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="10" align="center">({{ $textContract }})</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="10" class="part-title">1. Nguồn vào</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="head-row">
                    <td></td>
                    <td class="sl-bd">STT</td>
                    <td class="sl-bd">Tên</td>
                    <td class="sl-bd">Team</td>
                    <td class="sl-bd">Nguồn vào</td>
                    <td class="sl-bd">Thời gian vào</td>
                    <td class="sl-bd">Người giới thiệu</td>
                    <td class="sl-bd">Người tuyển dụng</td>
                    <td class="sl-bd">Hợp đồng</td>
                    <td class="sl-bd">Thời hạn hợp đồng</td>
                    <td class="sl-bd">Level</td>
                </tr>
                @if (!$collectionIn->isEmpty())
                    @foreach ($collectionIn as $key => $item)
                    <tr>
                        <td></td>
                        <td class="sl-bd">{{ $key + 1 }}</td>
                        <td class="sl-bd">{{ $item->name }}</td>
                        <td class="sl-bd">{{ $item->team_names }}</td>
                        <td class="sl-bd">{{ $item->cnname }}</td>
                        <td class="sl-bd">{{ Carbon::parse($item->join_date)->toDateString() }}</td>
                        <td class="sl-bd">{{ $item->empname }}</td>
                        <td class="sl-bd">{{ $item->recruiter }}</td>
                        <td class="sl-bd">{{ getOptions::getWorkingTypeLabel($item->working_type) }}</td>
                        <td class="sl-bd">{{ $item->contract_length }}</td>
                        <td class="sl-bd">{{ isset($devTypes[$item->level_type]) ? $devTypes[$item->level_type] : null }}</td>
                    </tr>
                    @endforeach
                @endif
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td colspan="9" class="part-title">2. Nguồn ra</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="head-row">
                    <td></td>
                    <td class="sl-bd">STT</td>
                    <td class="sl-bd">Tên</td>
                    <td class="sl-bd">Team</td>
                    <td class="sl-bd">Thời gian nghỉ</td>
                    <td class="sl-bd">Lý do nghỉ</td>
                    <td class="sl-bd">Level</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @if (!$collectionOut->isEmpty())
                    @foreach ($collectionOut as $key => $item)
                    <tr>
                        <td></td>
                        <td class="sl-bd">{{ $key + 1 }}</td>
                        <td class="sl-bd">{{ $item->name }}</td>
                        <td class="sl-bd">{{ $item->team_names }}</td>
                        <td class="sl-bd">{{ Carbon::parse($item->leave_date)->toDateString() }}</td>
                        <td class="sl-bd">{{ $item->leave_reason }}</td>
                        <td class="sl-bd">{{ isset($devTypes[$item->level_type]) ? $devTypes[$item->level_type] : null }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </body>
</html>