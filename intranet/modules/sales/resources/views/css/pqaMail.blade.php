<p>{{ trans('sales::view.CSS.Preview.Hello name', ['name' => $data['emp']['name']]) }}</p>
<p>{{ trans('sales::view.CSS.Preview.List CSS not make') }}</p>

@if (count($data['cssMail']))
    @foreach ($data['cssMail'] as $item)
        <br>
        <p>{{ $item['no'] }}. {{ $item['url'] }}</p>

        @if (count($item['notMakePerson']))
            <p>{{ trans('sales::view.CSS.Preview.List not make person') }}</p>
            @foreach ($item['notMakePerson'] as $person)
                <p>{{ trans('sales::view.CSS.Preview.Customer not make' ,['name' => $person['name'], 'email' => $person['email']]) }}</p>
            @endforeach
        @endif
    @endforeach
@endif

<br>

<p>{{trans('sales::view.Email respect')}}</p>
<p>{{trans('sales::view.Product team')}}</p>