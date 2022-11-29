<?php
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Event\View\ViewEvent;
use Rikkei\Core\Model\CoreConfigData;

$layout = EmailQueue::getLayoutConfig();
$titleIndex = ViewEvent::getHeadingIndexFines();
/* team code */
$keysEmail = ViewEvent::getKeysEmailBranch($data['branch'], 'fines');
$content = CoreConfigData::getValueDb($keysEmail['content']);
if(isset($data['reg_replace']) && $data['reg_replace'] &&
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
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">{{ trans('event::view.Account') }}</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['email']]))
                    {{ $data['employee'][$titleIndex['email']] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">{{ trans('event::view.Employee name') }}</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['ho_ten']]))
                    {{ $data['employee'][$titleIndex['ho_ten']] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">{{ trans('event::view.Employee code') }}</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['id']]))
                    {{ $data['employee'][$titleIndex['id']] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Phút đi muộn</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['phut_di_muon']]))
                    {{ $data['employee'][$titleIndex['phut_di_muon']] }}
                @else
                    0
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Tiền đi muộn</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['tien_di_muon']]))
                    <?php 
                        $number = preg_replace('/\,|\s/', '', $data['employee'][$titleIndex['tien_di_muon']]); 
                        $number = number_format($number);
                    ?>
                    {{ $number }}
                @else
                    0
                @endif
                {!! $dvtIcon !!}
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Số lần quên chấm công</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['lan_quen_cham_cong']]))
                    {{ $data['employee'][$titleIndex['lan_quen_cham_cong']] }}
                @else
                    0
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Tiền quên chấm công</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['tien_quen_cham_cong']]))
                    <?php 
                        $number = preg_replace('/\,|\s/', '', $data['employee'][$titleIndex['tien_quen_cham_cong']]); 
                        $number = number_format($number);
                    ?>
                    {{ $number }}
                @else
                    0
                @endif
                {!! $dvtIcon !!}
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Số lần không mặc đồng phục</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['lan_dong_phuc']]))
                    {{ $data['employee'][$titleIndex['lan_dong_phuc']] }}
                @else
                    0
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Tiền không mặc đồng phục</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['tien_dong_phuc']]))
                    <?php 
                        $number = preg_replace('/\,|\s/', '', $data['employee'][$titleIndex['tien_dong_phuc']]); 
                        $number = number_format($number);
                    ?>
                    {{ $number }}
                @else
                    0
                @endif
                {!! $dvtIcon !!}
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Số lần để máy qua đêm</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['lan_quen_tat_may']]))
                    {{ $data['employee'][$titleIndex['lan_quen_tat_may']] }}
                @else
                    0
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Tiền để máy qua đêm </td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['tien_quen_tat_may']]))
                    <?php
                    $number = preg_replace('/\,|\s/', '', $data['employee'][$titleIndex['tien_quen_tat_may']]);
                    $number = number_format($number);
                    ?>
                    {{ $number }}
                @else
                    0
                @endif
                {!! $dvtIcon !!}
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">
                <strong>Tổng</strong>
            </td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                <strong>
                    @if (isset($data['employee'][$titleIndex['tong']]))
                        <?php 
                            $number = preg_replace('/\,|\s/', '', $data['employee'][$titleIndex['tong']]); 
                            $number = number_format($number);
                        ?>
                        {{ $number }}
                    @else
                        0
                    @endif
                </strong>
                {!! $dvtIcon !!}
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
