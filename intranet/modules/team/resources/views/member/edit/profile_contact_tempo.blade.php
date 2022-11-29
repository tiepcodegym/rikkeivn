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
            <label class="col-md-4 control-label">{{ trans($moduleTrans .'.Tempo nation') }}</label>
            <div class="input-box col-md-8">
                <select name="{{ $contactField }}[tempo_country]" class="form-control select-search has-search"
                    {!!$disabledInput!!}>
                    <option value="">&nbsp;</option>
                    @foreach ($libCountry as $key => $label)
                        <option value="{{ $key }}"<?php 
                            if ($employeeContact && (isset($notCheckNative) || $employeeContact->native_country != null) &&
                                    $key == $employeeContact->tempo_country): ?> selected<?php 
                            endif; ?>>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans($moduleTrans .'.Tempo city') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="{{ $contactField }}[tempo_province]" class="form-control" 
                    placeholder="{{ trans($moduleTrans .'.Tempo city') }}" value="{{ $employeeContact ? $employeeContact->tempo_province : null }}" 
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans($moduleTrans .'.Tempo district') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="{{ $contactField }}[tempo_district]" class="form-control" 
                    placeholder="{{ trans($moduleTrans .'.Tempo district') }}" value="{{ $employeeContact ? $employeeContact->tempo_district : null }}" 
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="form-group row">
            <label class="col-md-4 control-label">{{ trans($moduleTrans .'.Tempo ward') }}</label>
            <div class="input-box col-md-8">
                <input type="text" name="{{ $contactField }}[tempo_ward]" class="form-control" 
                    placeholder="{{ trans($moduleTrans .'.Tempo ward') }}" value="{{ $employeeContact ? $employeeContact->tempo_ward : null }}" 
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 form-horizontal">
        <div class="form-group row">
            <label class="col-md-2 control-label">{{ trans($moduleTrans .'.Tempo addr') }}</label>
            <div class="input-box col-md-10">
                <input type="text" name="{{ $contactField }}[tempo_addr]" class="form-control" 
                    placeholder="{{ trans($moduleTrans .'.Tempo addr') }}"
                    value="{{ $employeeContact ? $employeeContact->tempo_addr : null }}"
                    {!!$disabledInput!!} />
            </div>
        </div>
    </div>
</div>