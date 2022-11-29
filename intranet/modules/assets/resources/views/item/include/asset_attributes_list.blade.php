<?php
    $disabled = '';
    if (isset($allowEdit) && !$allowEdit) {
        $disabled = 'disabled';
    }
?>
@if (count($assetAttributesList))
    <div class="form-group">
        <p><b>{{ trans('asset::view.Asset attribute') }}</b></p>
        <div class="row">
            @foreach ($assetAttributesList as $item)
                <div class="col-md-3">
                    <label>
                        <input type="checkbox" class="minimal" name="attribute[{{ $item->id }}]" value="{{ $item->id }}" <?php if (isset($assetItemAttributes) && in_array($item->id, $assetItemAttributes)): ?> checked<?php endif; ?> {{ $disabled }} />
                        {{ $item->name }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
@endif