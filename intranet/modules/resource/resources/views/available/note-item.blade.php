<?php
$content = '';
$name = '';
$noteClass = ' note-current';
$noteEmail = '';
$hasEdit = true;

if (isset($noteItem)) {
    $content = $noteItem->note;
    $name = ucfirst(preg_replace('/@.*/', '', $noteItem->email)) . ': ';
    $noteClass = '';
    $noteEmail = $noteItem->email;
    $hasEdit = ($noteItem->email == $currentUser->email);
}
?>

<div class="note-item media{{ $noteClass }}" data-email="{{ $noteEmail }}">
    <div class="media-left pull-left">
        <strong class="note-name">{{ $name }}</strong>
        <span class="loading hidden"><br /><i class="fa fa-spin fa-refresh"></i></span>
    </div>
    <div class="media-body">
        <div class="note-show hidden">{{ $content }}</div>
        @if ($hasEdit)
        <textarea class="note-edit form-control hidden">{{ $content }}</textarea>
        <div class="error note-error hidden"></div>
        @endif
    </div>
    @if ($hasEdit)
    <button type="button" class="note-edit-btn btn btn-primary btn-sm">
        <i class="fa fa-edit"></i>
    </button>
    @endif
</div>
