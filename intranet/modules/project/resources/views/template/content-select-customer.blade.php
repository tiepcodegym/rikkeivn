<?php
use Rikkei\Sales\Model\Customer;
?>

@if(isset($permissionEdit) && $permissionEdit)
    <select name="cust_contact_id" class="select-search {{ isset($project->id) ? 'input-basic-info' : '' }}" id="cust_contact_id">
        @if (isset($customers) && !$customers->isEmpty())
            @foreach ($customers as $cust)
            <option value="{{ $cust->id }}" {{ $customer->id == $cust->id ? 'selected' : '' }}>
                {{ $cust->name . ($cust->name_ja ? ' (' . $cust->name_ja . ')' : '') }} @if(!empty($cust->email)) - {{ $cust->email }} @endif
            </option>
            @endforeach
        @else
            @if ($customer)
                <option value="{{ $customer->id }}" selected>{{ $customer->name }} @if(isset($customer->email))- {{ $customer->email }} @endif</option>
            @else
                <option value=""></option>
            @endif
        @endif
    </select>
    @if($errors->has('cust_contact_id'))
        <label id="cust_contact_id-error" class="error" for="cust_contact_id">{{$errors->first('cust_contact_id')}}</label>
    @endif
@else
    <p class="form-control-static">{{ $customer->name }}</p>
@endif
