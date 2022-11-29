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
<p>Đã đến ngày tạo thưởng dự án, vui lòng click vào những đường link dưới đây để tạo thưởng dự án!</p>
@foreach($project_data as $key=>$value)
    <p><a href="{{route('project::reward', ['id' => $value['proj_id'], 'taskID'=> $value['task_id']])}}">{{$value["proj_name"]}}</a></p>
@endforeach
<p>Trân trọng cảm ơn.</p>
<br />
-----------------------------------------

@endsection
