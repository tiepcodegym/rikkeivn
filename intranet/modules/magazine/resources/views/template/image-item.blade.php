<?php
$selected = '';
if ($pivot = $image->pivot) {
    if ($pivot->is_background) {
        $selected = 'selected';
    }
}
?>
<div class="imgPreviewWrap {{ $selected }}" data-id="{{ $image->id }}">
    <img src="{{ $image->thumb_src }}" data-full="{{ $image->getSrc('full') }}" alt="No image">
    <div class="actions">
        <button type="button" class="action-delete" title="Delete image"><span>x</span></button>
    </div>
    <input type="hidden" class="image_val" value="{{ $image->id }}">
</div>
