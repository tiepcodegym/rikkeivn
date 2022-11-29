@if (isset($errors) && count($errors) > 0)
    <div class="flash-message">
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{!! $error !!} </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif