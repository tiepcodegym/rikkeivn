<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
?>
@extends($layout)

@section('content')

<?php
extract($data);
$link = route('project::dashboard');
?>

<p>Xin chào Anh/Chị <strong>{{ $pm_name }}</strong>,</p> 
<p>Thời gian kết thúc (end date) của (các) dự án sau: <strong>{{ $project_names }}</strong>, đã quá 30 ngày, vui lòng đóng (các) dự án lại!</p>
<p><a href="{{ $link }}">Project dashboard</a></p>
<p>Trân trọng cảm ơn.</p>
<br />
-----------------------------------------

@endsection
