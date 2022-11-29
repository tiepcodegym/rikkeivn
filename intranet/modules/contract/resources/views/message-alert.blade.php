@if (session('error'))
<div class="alert alert-error">
    {{ session('error') }}
</div>
@endif

@if (session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@if (session('warning'))
<div class="alert alert-warning">
    {!! session('warning') !!}
</div>
@endif