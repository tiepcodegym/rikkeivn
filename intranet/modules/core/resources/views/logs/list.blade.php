<h3>Danh sách file logs</h3>

@foreach ($files as $file)
<p><a href="{{ route('core::log.download', (string)basename($file)) }}"> {{ (string)basename($file) }} </a></p>
@endforeach


