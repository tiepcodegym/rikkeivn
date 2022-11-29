<?php
if (!isset($isTpl)) {
    $isTpl = true;
}
?>

<button type="button" title="{{ trans('team::view.Edit') }}" class="btn btn-success btn-busi-edit {{ $isTpl ? 'hidden' : '' }}">
    <i class="fa fa-edit"></i>
</button>
<button type="button" class="btn btn-primary btn-busi-save {{ $isTpl ? '' : 'hidden' }}"
        data-url="{{ $urlEdit }}">
    <i class="fa fa-save"></i>
</button>
<button type="button" title="{{ trans('team::view.Delete') }}" class="btn btn-danger btn-busi-delete {{ $isTpl ? 'new-del' : '' }}"
        data-noti="{{ trans('team::messages.Are you sure want to delete?') }}"
        data-url="{{ $urlDelete }}">
    <i class="fa fa-trash"></i>
</button>
<button type="button" title="{{ trans('team::view.Cancel') }}" class="btn btn-danger btn-busi-cancel hidden">
    <i class="fa fa-close"></i>
</button>