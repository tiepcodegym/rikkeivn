<?php
if(!Session::has('messages')) {
    return;
}
$messages = Session::get('messages');
$render = Session::get('is_render');
?>

@if (isset($messages['success']) && count($messages['success']))
    <div class="flash-message">
        <div class="alert alert-success">
            <ul>
                @foreach($messages['success'] as $message)
                    <li>{{ $message }}</li>   
                @endforeach
            </ul>
        </div>
    </div>
@endif
@if (isset($messages['errors']) && count($messages['errors']))
    <div class="flash-message">
        <div class="alert alert-danger">
            <ul>
                @foreach($messages['errors'] as $message)
                    @if ($render)
                    <li>{!! $message !!}</li>
                    @else
                    <li>{{ $message }}</li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>
@endif

<?php
Session::forget('is_render');
?>
