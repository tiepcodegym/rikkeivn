<div class="form-group">
    <?php
    $approvers = $item ? $item->approvers : null;
    ?>
    <label>Approver: </label>

    <table class="table">
        <tbody>
        @if ($approvers && !$approvers->isEmpty())
            @foreach ($approvers as $approver)
                @include('doc::includes.assignee-item', [
                    'typeAssignee' => DocConst::TYPE_ASSIGNE_APPROVE,
                    'emp' => $approver,
                    'permiss' => $coorApprover,
                    'collect' => $approvers
                ])
            @endforeach
        @elseif (!$coorApprover)
        <tr>
            <td class="text-red">{{ trans('doc::view.Not assigne') }}</td>
        </tr>
        @endif
        </tbody>
        @if ($coorApprover)
        <tfoot>
            <tr class="tr-add-form">
                <td>
                    <select class="form-control select-search-employee select-search" id="select_approver"
                            data-remote-url="{{ route('team::employee.list.search.ajax') }}"
                            style="max-width: 300px;">
                        @if (old('employee_id') && $oldEmp = DocConst::getOldEmployee(old('employee_id')))
                        <option value="{{ $oldEmp->id }}" selected>{{ DocConst::getAccount($oldEmp->email) }}</option>
                        @endif
                    </select>
                </td>
                <td></td>
                <td class="text-right">
                    <button type="button" class="btn btn-xs btn-success btn-add-assignee"
                            data-url="{{ route('doc::admin.add_assignee', $item->id) }}"
                            data-type="{{ DocConst::TYPE_ASSIGNE_APPROVE }}"><i class="fa fa-plus"></i></button>
                </td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
