<?php
use Rikkei\Document\View\DocConst;
?>

<div class="form-group">
    <?php
    $reviewers = $item ? $item->reviewers : null;
    ?>
    <label>Reviewer <em class="required">*</em>: @if ($docPermiss['edit_reviewer']) <i>({{ trans('doc::view.Choose reviewer and click save') }})</i> @endif</label>
    <table class="table table-list-assignee" id="tbl_reviewers">
        <tbody>
        @if ($reviewers && !$reviewers->isEmpty())
            @foreach ($reviewers as $reviewer)
                @include('doc::includes.assignee-item', [
                    'typeAssignee' => DocConst::TYPE_ASSIGNE_REVIEW,
                    'emp' => $reviewer,
                    'permiss' => $docPermiss['edit_reviewer'],
                    'collect' => $reviewers
                ])
            @endforeach
        @elseif (!$docPermiss['edit_reviewer'])
        <tr class="item-none">
            <td class="text-red">{{ trans('doc::view.Not assigne') }}</td>
        </tr>
        @endif
        </tbody>
        @if ($docPermiss['edit_reviewer'])
        <tfoot>
            <tr class="tr-add-form">
                <td colspan="2">
                    <select class="form-control select-remote-assignee" id="select_reviewer"
                            data-remote-url="{{ route('doc::admin.search_assignees') }}"
                            data-type="{{ DocConst::TYPE_ASSIGNE_REVIEW }}">
                        @if (old('employee_id') && $oldEmp = DocConst::getOldEmployee(old('employee_id')))
                        <option value="{{ $oldEmp->id }}" selected>{{ DocConst::getAccount($oldEmp->email) }}</option>
                        @endif
                    </select>
                </td>
                <td><i class="fa fa-spin fa-refresh hidden add-loading"></i></td>
            </tr>
            
            @if ($item && $item->status == DocConst::STT_SUBMITED)
            <tr>
                <td>
                    <button type="submit" class="btn-success btn-sm btn" id="btn_save_doc"><i class="fa fa-save"></i> {{ trans('doc::view.Save') }}</button>
                </td>
            </tr>
            @endif
        </tfoot>
        @endif
    </table>
</div>

