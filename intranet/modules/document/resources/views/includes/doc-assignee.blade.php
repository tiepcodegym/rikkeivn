<?php
use Rikkei\Document\View\DocConst;
?>

<div class="form-group">
</div>


<div class="form-group">
    <?php
    $editors = old('employee_id') ? DocConst::getOldEmployee(explode(',', old('employee_id'))) : null;
    if (!$editors || $editors->isEmpty()) {
        $editors = $item->editors;
    }
    ?>
    <label>{{ trans('doc::view.Editor') }}: </label>
    @if ($item->isAuthor() && $item->status != DocConst::STT_PUBLISH)
        <table class="table">
            <tbody>
                <tr class="tr-toggle" data-toggle="editor">
                    <td>
                        @if ($editors)
                            <?php
                            $editorsAcc = $editors->map(function ($editor) {
                                $editor->account = DocConst::getAccount($editor->email);
                                return $editor;
                            });
                            ?>
                            {{ $editorsAcc->implode('account', ', ') }}
                        @endif
                    </td>
                    <td></td>
                    <td class="text-right">
                        <button type="button" class="btn btn-xs btn-primary btn-toggle"><i class="fa fa-edit"></i></button>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="tr-add-form tr-toggle hidden" data-toggle="editor">
                    <td>
                        <select class="form-control select-search" multiple
                                data-remote-url="{{ route('team::employee.list.search.ajax') }}">
                            @if ($editors && !$editors->isEmpty())
                                @foreach ($editors as $emp)
                                <option value="{{ $emp->id }}" selected>{{ $emp->getNickName() }}</option>
                                @endforeach
                            @endif
                        </select>
                    </td>
                    <td>
                        <i class="fa fa-spin fa-refresh hidden add-loading"></i>
                        <button type="button" class="btn-add-assignee btn btn-xs btn-success"
                                data-url="{{ route('doc::admin.add_assignee', $item->id) }}"
                                data-type="{{ DocConst::TYPE_ASSIGNE_EDITOR }}"><i class="fa fa-save"></i></button>
                    </td>
                    <td class="text-right">
                        <button type="button" class="btn btn-xs btn-danger btn-toggle"><i class="fa fa-close"></i></button>
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <?php
        $editorNames = [];
        if ($editors && !$editors->isEmpty()) {
            foreach ($editors as $emp) {
                $editorNames[] = $emp->getNickName();
            }
        }
        ?>
        <span>{{ implode(', ', $editorNames) }}</span>
    @endif
</div>

