<!--Check Ot disallow -->
<ul>
    @if (count($hasOtDisallow))
        <li>{{ trans('ot::message.The following employees can`t be registered') }}</li>
        @foreach ($hasOtDisallow as $key => $value)
            <li>{{ $value }}</li>
        @endforeach
    @endif
</ul>
<!--Check If Exist -->

<ul>
    @if (count($errorsExist))
        <li>{{ trans('ot::message.The following employees have registered the same registration') }}</li>
        @foreach ($errorsExist as $key => $value)
            <li>{{ $value }}</li>
        @endforeach
    @endif

</ul>