<p>Chào {{ $data['emp_name'] }}</p>
<p>Dưới đây là danh sách các CSS đã quá thời gian mong muốn trả lời nhưng chưa được khách hàng đánh giá.</p>

@if (count($data['css']))
	<?php $key = 0; ?>
    @foreach ($data['css'] as $item)
		<?php $key++; ?>
        <p>{{ $key }}. {{ $item }}</p>
    @endforeach
@endif

<br>
<p>{{trans('sales::view.Email respect')}}</p>
<p>{{trans('sales::view.Product team')}}</p>