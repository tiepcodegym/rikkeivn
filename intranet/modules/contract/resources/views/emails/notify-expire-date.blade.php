<?php
$layout = \Rikkei\Core\Model\EmailQueue::getLayoutConfig();
extract($data);

$expireDate = explode(' ',$expireDate);
?>
@extends($layout)

@section('content')
@if (isset($employee))
<p>{{trans('contract::view.Dear Mr./Ms')}}</p>
@endif

<p>&nbsp;</p>
    <p>
    {{trans("contract::view.Rikkeisoft system announces the expiration of the employee's contract.")}}<br/><br/>
    {{trans('contract::view.Employee profile')}}:<br/>
    {{trans('contract::view.Full name')}}: <b>{{ isset($employee['name']) && $employee['name'] ? $employee['name'] :'' }}</b><br/>
    Email: <b> {{ isset($employee['email']) && $employee['email'] ? $employee['email'] :'' }}</b><br/>
    {{trans('contract::view.Expire date')}}:  <b>{{$expireDate[0]}}</b><br/>
</p>
<p>&nbsp;</p>

<p>
    @if (isset($link))
        <a href="{{ $link }}" style="color: #15c">Thông tin chi tiết hợp đồng</a>
    @else
        <a href="{{ route('contract::manage.contract.show', ['id' => $contract['id']]) }}" style="color: #15c">Thông tin chi tiết hợp đồng</a>
    @endif
</p>
@endsection