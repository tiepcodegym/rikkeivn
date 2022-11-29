@if($teamName)
	<?php
		$i = 1;
	?>
	@foreach($teamName as $value)
		<input type="hidden" name="" value="{{ $value['id'] }}"/>
		<label>{{ $i++ }} -</label>
		<label>{{ $value['name'] }}</label></br>
	@endforeach
@endif
