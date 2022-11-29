<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('css')
<style>
    ._data_tbl{
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    ._data_tbl thead th{
        text-align: left;
    }
    ._data_tbl th, ._data_tbl td{
        padding: 8px 10px;
        border: 1px solid #ddd;
    }
    p{
        line-height: 18px;
    }
</style>
@stop

@section('content')

<?php
extract($data);
$to_name = '';
$me_content = '';
$accept_link = '';
$me_data = '';
$self_content = '';
$reson_content = 'khách hàng làm CSS';
if ($is_coo_update) {
    $reson_content = 'COO cập nhật';
}
switch ($to_type) {
    case 1: 
        $to_name = $employee_name;
        $self_content = 'của Anh/Chị';
        break;
    case 2:
        $to_name = $pm_name;
        break;
    case 3:
        $to_name = $leader_name;
        break;
}
if (in_array($to_type, [2, 3])) {
    $me_data .= '<table class="_data_tbl">'
            . '<thead>'
                . '<tr>'
                    . '<th>Nhân viên</th>'
                    . '<th>Điểm tổng ME cũ</th>'
                    . '<th>Điểm tổng ME mới</th>'
                . '</tr>'
            . '</thead>';
    foreach ($members as $member) {
        $me_data .= '<tr>'
                    . '<td>'. $member['employee_name'] .'</td>'
                    . '<td style="text-align: center;">'. $member['me_point_old'] .'</td>'
                    . '<td style="text-align: center;">'. $member['me_point_new'] .'</td>'
                . '</tr>';
    }
    $me_data .= '</table>';
    $me_content = '<p>Danh sách ME thay đổi chi tiết:</p>';
} else {
    $me_content = '<p>Điểm tổng ME cũ: <strong>'. $me_point_old .'</strong>, điểm tổng ME mới: <strong>'. $me_point_new .'</strong></p>';
}
?>

<p>Xin chào Anh/Chị <strong>{{ $to_name }}</strong>,</p> 
<p>Điểm ME của tháng {{ $time->format('Y-m') }}, dự án <strong>{{ $project_name }}</strong> {{ $self_content }} có thay đổi do {{ $reson_content }}.</p>
<ul>
    <li><p>Điểm CSS cũ: <strong>{{ $css_point_old }}</strong>, điểm CSS mới: <strong>{{ $css_point_new }}</strong></p></li>
    <li><p>Project point cũ: <strong>{{ $project_point_old }}</strong>, project point mới: <strong>{{ $project_point_new }}</strong></p></li>
    <li>{!! $me_content !!}</li>
</ul>
{!! $me_data !!}
<!--<p><a href="{{ $accept_link }}">chi tiết</a></p>-->
<p>Trân trọng cảm ơn.</p>
<br />
-----------------------------------------

@stop
