<?php
use Rikkei\Core\Model\EmailQueue;
$layout = EmailQueue::getLayoutConfig();
?>

@extends($layout)

@section('content')

<div>
    <p>Dear {{ $data['emp_name'] }}!</p>
    <p>Đây là email được gửi từ website <a href="{{config('app.url')}}">rikkei.vn</a>.</p>
    <p>Rikkei.vn là một website được thiết kế để hỗ trợ việc giao tiếp, hợp tác và xử lý công việc giữa các nhân viên trong nội bộ Rikkeisoft. </p>
    Các tính năng chính:<br/>
    - Trang tin tức<br/>
    - Quản lý chấm công<br/>
    - Quản lý dự án<br/>
    ...<br/>

    <p style="font-size:16px"><strong>Về việc bảo mật file đính kèm được gửi qua hệ thống mail của <a href="{{ config('app.url') }}">rikkei.vn</a></strong></p>
    <p>Trong quá trình làm việc tại Rikkeisoft, bạn sẽ nhận được rất nhiều mail được gửi từ hệ thống rikkei.vn. Các file đính kèm trong mail (thông tin lương, thuế...) sẽ được set mật khẩu, nhằm tăng tính bảo mật.</p>
    <p>Mật khẩu hiện tại của bạn là: <b>{{ $data['pass'] }}</b></p>
    <p>Bạn có thể thay đổi mật khẩu tại <a href="{{ config('app.url') }}/profile/{{ $data['employee_id'] }}/api">đây</a></a></p>
    
    <p>Trân trọng</p>
    <p>Product team</p>
</div>
@endsection