<p>Chào {{ $data['data']['emp_name'] }}</p>
<p>Dưới đây là danh sách các NC đã quá hạn nhưng chưa được xử lý.</p>

@if (count($data['nc']))
    @php $key = 0; @endphp
    @foreach ($data['nc'] as $ncId => $item)
        @php $key++; @endphp
        <p>{{ $key }}. <a href="{{ route('project::nc.detail', ['id' => $ncId]) }}">{{ $item }}</a></p>
    @endforeach
@endif

<br>

<p>Trân trọng,</p>
<p>Product team.</p>