@if(Session::has('flash_success'))
    <div class="alert alert-success">
        <ul>
            <li>
                {{ Session::get('flash_success') }}
            </li>
        </ul>
    </div>
@endif
@if(Session::has('flash_error'))
    <div class="alert alert-danger not-found">
        <ul>
            <li>
                {{ Session::get('flash_error') }}
            </li>
        </ul>
    </div>
@endif