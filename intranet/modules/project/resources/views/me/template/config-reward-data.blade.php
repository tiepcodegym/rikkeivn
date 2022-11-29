<div class="form-group row form-label-left">
    <div class="col-md-11">
        @foreach ($contributes as $key => $label)
        <?php
        $itemValue = 0;
        if (isset($configRewards[$key]) && $configRewards[$key]) {
            $itemValue = $configRewards[$key];
        }
        ?>
        <div class="row">
            <label class="col-md-2 col-sm-3">{{ $label }}</label>
            <p class="col-md-10 col-sm-9">
                <input type="number" min="0" step="1" name="item[{{ $keyDb }}][{{ $key }}]"
                       value="{{ $itemValue }}"
                       class="form-control input-field input-number">
            </p>
        </div>
        @endforeach
    </div>
    <div class="col-md-1">
        <input type="hidden" name="key_config_cache" value="{{ $keyDb }}">
        <button class="btn-add" type="submit">{{trans('core::view.Save')}} <i class="fa fa-spin fa-refresh hidden submit-ajax-refresh"></i></button>
    </div>
</div>
