<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')

<div>
    <p>
        <div>Chào {{ $data['name'] }}.</div>
        <div>
            {{ $data['name'] }} さん<br/>
            お疲れ様です。
        </div>
    </p>
    <p>
        <div>
            Team Intranet xin thông báo.<br/>
            Từ ngày {!! $data['dateFrom'] !!} đến ngày {!! $data['dateTo1Year'] !!} bạn mới chỉ nghỉ phép {!! $data['totalLeaveDay'] !!} ngày.
        </div>
        <div>{!! $data['dateFrom'] !!}から{!! $data['dateTo1Year'] !!} までに、{!! $data['totalLeaveDay'] !!}日間有給休暇を取得したことをお知らせいたします。</div>
    </p>
    <p>
        <div>Theo qui định, trong vòng 1 năm (từ ngày {!! $data['dateFrom'] !!} tới ngày {!! $data['dateTo1Year'] !!}) bạn phải nghỉ phép tối thiếu {!! $data['leaveDayMin'] !!} ngày. Mong bạn lưu ý.</div>
        <div>法改正により、1年間（{!! $data['dateFrom'] !!} から {!! $data['dateTo1Year'] !!} まで）で最低{!! $data['leaveDayMin'] !!}日間は有給休暇を取得しなければいけませんので、ご注意ください。</div>
    </p>
    <p>
        <div>Trân trọng.</div>
        <div>よろしくお願いいたします。</div>
    </p>
</div>

@endsection
