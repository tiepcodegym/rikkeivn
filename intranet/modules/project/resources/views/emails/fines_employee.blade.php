<?php
use Rikkei\Event\View\TimekeepingHelper;
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
$titleIndex = TimekeepingHelper::getHeadingIndexFines();

$tableBorder = 'border-collapse: collapse; border: 2px solid #767676; width: 100%;';
$tablePaddingCell = 'padding: 10px;';
$tableAlignRightCell = 'text-align: right;';
$tableAlignLeftCell = 'text-align: left;';
$tableCellBorder = 'border: 1px solid #767676;';
$lineHeight = 'line-height: 1.5;';
$dvtIcon = '<div style="position: relative;font-size: 16px;background: black;color: white;display: inline-block;border-radius: 50%;width: 20px;height: 20px;">
    <div style="position: absolute;font-size: 15px;top: 50%;left: 50%;line-height: 0;margin-left: -5px;">đ</div>
</div>';
$dvtIcon = '<span style="font-size: 12px;color: gray;">đ</span>';

$emailContent = \Rikkei\Core\Model\CoreConfigData::getValueDb('hr.email_content.fines');
$emailContent = trim($emailContent);
?>
@extends($layout)

@section('content')
<div style="{{ $lineHeight }}">
<p>Dear
    @if (isset($data['employee'][$titleIndex['ho_ten']]))
        {{ $data['employee'][$titleIndex['ho_ten']] }},
    @endif
</p>
@if ($emailContent)
    {!! $emailContent !!}
@else
<p>
    Công đoàn gửi bạn bảng tiền phạt vi phạm nội quy
    @if (isset($data['time']) && $data['time'])
        tháng {{ $data['time'] }}.
    @endif
     Hạn cuối nộp tiền phạt là
    @if (isset($data['time_limit_dow']) && $data['time_limit_dow'])
        thứ {{ $data['time_limit_dow'] }},
    @endif
    @if (isset($data['time_limit_date']) && $data['time_limit_date'])
        ngày {{ $data['time_limit_date'] }},
    @endif
</p>

<p>
    <strong style="color: red">LƯU Ý</strong>: Hiện tại Công ty thực hiện bổ sung công, nghỉ phép trên AMIS nên dữ liệu trên máy chấm công sẽ không được cập nhật.
</p>

<p>
    <strong>Vì vậy tất cả mọi người check lại thời gian đi muộn tại bảng Chấm công chi tiết giờ vào ra 
        @if (isset($data['time']) && $data['time'])
            tháng {{ $data['time'] }}.
        @endif
    </strong>
    Nếu có thông tin cần thay đổi vui lòng liên hệ Công đoàn ( NhungLTH ) để được cập nhật chính xác.
</p>

<p>
    Rất mong mọi người chấp hành việc đóng phạt nhanh chóng và đầy đủ.
</p>
@endif
</div>
<p>Đơn vị tiền: 1.000VND</p>
<table style="{{ $tableBorder }}">
    <tbody>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Mã nhân viên</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['ma_nv']]))
                    {{ $data['employee'][$titleIndex['ma_nv']] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Họ tên</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['ho_ten']]))
                    {{ $data['employee'][$titleIndex['ho_ten']] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">ID</td>
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
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Tiền đi muộn</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['tien_di_muon']]))
                    {{ $data['employee'][$titleIndex['tien_di_muon']] }}
                @endif
                {!! $dvtIcon !!}
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Số lần quên chấm công</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['lan_quen_cham_cong']]))
                    {{ $data['employee'][$titleIndex['lan_quen_cham_cong']] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Tiền quên chấm công</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['tien_quen_cham_cong']]))
                    {{ $data['employee'][$titleIndex['tien_quen_cham_cong']] }}
                @endif
                {!! $dvtIcon !!}
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Số lần không mặc đồng phục</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['lan_dong_phuc']]))
                    {{ $data['employee'][$titleIndex['lan_dong_phuc']] }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $tablePaddingCell . $tableAlignLeftCell . $tableCellBorder }}">Tiền không mặc đồng phục</td>
            <td style="{{ $tablePaddingCell . $tableAlignRightCell . $tableCellBorder }}">
                @if (isset($data['employee'][$titleIndex['tien_dong_phuc']]))
                    {{ $data['employee'][$titleIndex['tien_dong_phuc']] }}
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
                        {{ $data['employee'][$titleIndex['tong']] }}
                    @endif
                </strong>
                {!! $dvtIcon !!}
            </td>
        </tr>
    </tbody>
</table>
<br/>
<p>Thanks!</p>
@endsection
