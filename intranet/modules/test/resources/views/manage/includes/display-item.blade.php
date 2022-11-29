<?php
use Rikkei\Test\View\ViewTest;
?>

<div class="form-group row display-option-row {{ isset($htmlId) ? 'hidden' : '' }}" @if(isset($htmlId)) id="display_option_tpl" @endif>
    @foreach (array_keys(ViewTest::ARR_CATS) as $key)
    <div class="col-opt-20 col-type">
        <select class="category_{{ $key }} select-cat form-control ignore" data-cat="{{ $key }}"
                @if(isset($option)) name="display_option[{{ $index }}][{{ $key }}]" @endif>
                <option value="">&nbsp;</option>
            @if (isset($collectCats) && $collectCats && isset($collectCats[$key]))
                @foreach($collectCats[$key] as $cat)
                <option value="{{ $cat->id }}" {{ (isset($option[$key]) && $option[$key] == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            @endif
        </select>
    </div>
    @endforeach
    <div class="col-opt-10">
        <input type="number" min="1" class="total-option form-control" disabled>
    </div>
    <div class="col-opt-20">
        <input type="number" min="1" class="input-option form-control ignore"
               @if (isset($option)) name="display_option[{{ $index }}][value]" value="{{ $option['value'] }}" @else value="1" @endif>
    </div>
    <div class="col-opt-10">
        <button type="button" class="btn btn-danger btn-del-row"><i class="fa fa-close"></i></button>
    </div>
</div>
