<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Event\View\ViewEvent;
use Rikkei\Core\Model\CoreConfigData;

$layout = EmailQueue::getLayoutConfig();
$titleIndex = ViewEvent::getHeadingIndexForgotTurnOff();
/* team code */
$keysEmail = ViewEvent::getKeysEmailBranch($data['branch'], 'turnoff');
$content = CoreConfigData::getValueDb($keysEmail['content']);
if (isset($data['reg_replace']) && $data['reg_replace'] &&
    isset($data['reg_replace']['patterns']) && $data['reg_replace']['patterns'] &&
    isset($data['reg_replace']['replaces']) && $data['reg_replace']['replaces']
) {
    $content = preg_replace(
        $data['reg_replace']['patterns'],
        $data['reg_replace']['replaces'],
        $content
    );
}
$tableBorder = 'border-collapse: collapse; border: 2px solid #767676; width: 100%;';
$tablePaddingCell = 'padding: 10px;';
$tableAlignRightCell = 'text-align: right;';
$tableAlignLeftCell = 'text-align: left;';
$tableCellBorder = 'border: 1px solid #767676;';
$lineHeight = 'line-height: 1.5;';
$dvtIcon = '<div style="position: relative;font-size: 16px;background: black;color: white;display: inline-block;border-radius: 50%;width: 20px;height: 20px;">
    <div style="position: absolute;font-size: 15px;top: 50%;left: 50%;line-height: 0;margin-left: -5px;">VND</div>
</div>';
$dvtIcon = '<span style="font-size: 12px;color: gray;">VND</span>';
?>
@extends($layout)

@section('content')
    <?php
    extract($data);
    ?>
    <div style="line-height: 17px;">
        {!! $content !!}
    </div>

    <p>&nbsp;</p>
    <table style="{{ $tableBorder }}">
        <tbody>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Account</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['account']]))
                    {{ $data['employee']['account'] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">IP Address</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee']['ip_address']))
                    {{ $data['employee']['ip_address'] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Computer Name</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee']['computername']))
                    {{ $data['employee']['computername'] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Area</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee']['area']))
                    {{ $data['employee']['area'] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Date</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee']['date']))
                    {{ $data['employee']['date'] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Month</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee']['month']))
                    {{ $data['employee']['month'] }}
                @endif
            </td>
        </tr>
        </tbody>
    </table>

    @if (isset($columnsHeading) && count($columnsHeading))
        <table class="sabatical_tbl" style="border-collapse: collapse; border: 2px solid #767676; width: 100%;">
            <tbody>
            @foreach($columnsHeading as $index => $colName)
                <?php
                $value = isset($data[$index]) ? $data[$index] : null;
                ?>
                <tr>
                    <td style="padding: 8px 10px; border: 1px solid #767676;">{{ $colName }}</td>
                    <td style="padding: 8px 10px; border: 1px solid #767676; text-align: right;">{{ $value }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
