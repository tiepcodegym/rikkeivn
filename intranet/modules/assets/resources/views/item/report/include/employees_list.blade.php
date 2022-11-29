<?php
    use Rikkei\Assets\View\AssetConst;
    use Rikkei\Core\View\CoreUrl;
?>
<label class="report">
    {{ trans('asset::view.Print details by each employee') }}
</label>
<br>
<label class="hidden" id="no_employee-error" style="color: red">{{ trans('asset::message.Please select an employee') }}</label>
<div class="input-box">
    <table class="table table-striped dataTable table-bordered table-hover table-grid-data asset-emp-table-report asset-table-disabled-checkbox" style="width: 100%;">
        <thead>
            <tr>
                <th class="width-20 text-center">
                    @if(isset($employees) && count($employees))
                        <input type="checkbox" class="checkbox-all-report" name="" value="">
                    @endif
                </th>
                <th class="width-100">{{ trans('asset::view.Employee code') }}</th>
                <th class="width-120">{{ trans('asset::view.Employee name') }}</th>
                <th class="width-180">{{ trans('asset::view.Position') }}</th>
            </tr>
        </thead>
        <tbody class="table-body">
            @if(isset($employees) && count($employees))
                @foreach($employees as $item)
                    <tr>
                        <td>
                            {{ $item->employee_id }}
                        </td>
                        <td>{{ $item->employee_code }}</td>
                        <td>{{ $item->employee_name }}</td>
                        <td>{{ $item->role_name }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="hidden"></td>
                    <td class="hidden"></td>
                    <td class="hidden"></td>
                    <td colspan="4" class="text-center">
                        <h2 class="no-result-grid">{{ trans('asset::view.No results data') }}</h2>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
