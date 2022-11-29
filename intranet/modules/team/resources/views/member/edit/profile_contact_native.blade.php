<?php
if (!isset($moduleTrans)) {
    $moduleTrans = 'team::profile';
}
if (!isset($contactField)) {
    $contactField = 'employeeContact';
}
?>


<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans($moduleTrans. '.Native nation') }}</label>
            <div class="input-box col-md-8">
                <select name="{{ $contactField }}[native_country]" class="form-control select-search has-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($libCountry as $key => $label)
                        <option value="{{ $key }}"<?php 
                            if ($employeeContact && $key == $employeeContact->native_country): ?> selected<?php 
                            endif; ?>>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans($moduleTrans. '.Native city') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="{{ $contactField }}[native_province]" class="form-control" 
                       placeholder="{{ trans($moduleTrans. '.Native city') }}" value="{{ $employeeContact ? $employeeContact->native_province : null }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans($moduleTrans. '.Native district') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="{{ $contactField }}[native_district]" class="form-control" 
                       placeholder="{{ trans($moduleTrans. '.Native district') }}" value="{{ $employeeContact ? $employeeContact->native_district : null }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans($moduleTrans. '.Native ward') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="{{ $contactField }}[native_ward]" class="form-control" 
                       placeholder="{{ trans($moduleTrans. '.Native ward') }}" value="{{ $employeeContact ? $employeeContact->native_ward : null }}" 
                       {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label {{ !empty($requireClass) ? $requireClass : '' }}" data-label="native_addr">{{ trans($moduleTrans. '.Native addr') }}</label>
            <div class="input-box col-md-10">
                <input type="text" name="{{ $contactField }}[native_addr]" class="form-control" 
                    placeholder="{{ trans($moduleTrans. '.Native addr') }}"
                    value="{{ $employeeContact ? $employeeContact->native_addr : null }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>