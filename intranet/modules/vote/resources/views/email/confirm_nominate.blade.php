<?php
use Rikkei\Core\Model\EmailQueue;

$layout = EmailQueue::getLayoutConfig();

?>
@extends($layout)

@section('content')
<?php
extract($data);
?>

<p>Xin chào Anh/Chị <strong>{{ $employee_name }}</strong></p>
<p style="line-height: 22px;">Anh/Chị đã được đề cử trong cuộc ứng cử/đề cử: <strong>{{ $vote_title }}</strong></p>
<p>Chi tiết: <a href="{{ $detail_link }}">tại đây</a></p>
<p>Xác nhận tham gia?: <a href="{{ route('vote::nominee_confirm', ['key' => $confirm_key, 'ans' => 'yes']) }}">Có</a>, 
    <a href="{{ route('vote::nominee_confirm', ['key' => $confirm_key, 'ans' => 'no']) }}">Không</a>.</p>

@endsection

