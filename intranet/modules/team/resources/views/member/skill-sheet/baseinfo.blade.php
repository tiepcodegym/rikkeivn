<?php
use Carbon\Carbon;
use Rikkei\Core\View\View;
use Illuminate\Support\Str;

$avatar = $userItem->avatar_url;
$avatar = View::getLinkImage($avatar);
$avatar = preg_replace('/\?(sz=)(\d+)/i', '', $avatar);
?>

<div class="row">
    <div class="col-md-6 form-horizontal">
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="full name"></label>
            <div class="input-box col-md-8">
                <p class="form-control-static" data-excel-render="user-name" data-slug="{{Str::slug($employeeModelItem->name, '_')}}">{{ $employeeModelItem->name }}</p>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="kana name"></label>
            <div class="input-box col-md-8">
                <input name="employee[japanese_name]" class="form-control" {!!$disabledInput!!}
                    value="{{ $employeeModelItem->japanese_name }}" data-input-cv
                    data-valid-type='{"maxlength":255}' />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="birthday"></label>
            <div class="input-box col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-box">
                            <input name="employee[birthday]" data-flag-type="date" class="form-control"
                                value="{{ $employeeModelItem->birthday }}" {!!$isCompanyDisableInput!!} data-input-cv />
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <p class="form-control-static"><span data-lang-r="old"></span>: 
                            <span data-fg-dom="old" data-excel-render="user-old">{!! $employeeModelItem->getOld() !!}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="address home"></label>
            <div class="input-box col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <input name="eav[address_home]" class="form-control" {!!$disabledInput!!}
                        value="" data-db-lang="address_home" data-input-cv
                        data-valid-type='{"maxlength":255}' data-lang-placeholder="placeholder address home" />
                    </div>
                    <div class="col-md-6 text-right form-inline">
                        <label class="control-label" data-lang-r="sex"></label>
                        <select name="employee[gender]" class="form-control" {!!$isCompanyDisableInput!!} data-input-cv>
                            <option data-lang-r="gender_1" value="1"{!!$employeeModelItem->gender == 1 ? ' selected' : ''!!}></option>
                            <option data-lang-r="gender_0" value="0"{!!$employeeModelItem->gender == 0 ? ' selected' : ''!!}></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="address"></label>
            <div class="input-box col-md-8">
                <input name="eav[address]" class="form-control" {!!$disabledInput!!}
                    value="" data-db-lang="address" data-input-cv
                    data-valid-type='{"maxlength":255}' data-lang-placeholder="placeholder address" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="school graduation"></label>
            <div class="input-box col-md-8">
                <input name="eav[school_graduation]" class="form-control" {!!$disabledInput!!}
                    value="" data-db-lang="school_graduation" data-input-cv
                    data-valid-type='{"maxlength":255}' data-lang-placeholder="placeholder school graduation" />
            </div>
        </div>
    </div>
    <div class="col-md-6 form-horizontal">
        <div class="row">
            <div class="col-md-4">
                <div class="margin-bottom-15">
                    <p class="control-label" data-lang-r="level japanese"></p>
                </div>
                <div class="margin-bottom-15">
                    <p class="control-label" data-lang-r="level english"></p>
                </div>
            </div>
            <div class="col-md-5 form-group-select2">
                <div class="margin-bottom-15 form-input-loading">
                    <select name="eav_s[lang_ja_level]" class="form-control" {!!$disabledInput!!}
                            data-select2-dom="1" data-select2-search="1" data-input-cv >
                            <option value="">&nbsp;</option>
                            <?php $langSelected = $employeeCvEav->getVal('lang_ja_level'); ?>
                            @foreach ($langsLevel['ja'] as $lang)
                                <option value="{!!$lang!!}"{!!$langSelected == $lang ? ' selected' : ''!!}>{!!$lang!!}</option>
                            @endforeach
                        </select>
                </div>
                <div class="margin-bottom-15 form-input-loading">
                    <select name="eav_s[lang_en_level]" class="form-control" {!!$disabledInput!!}
                            data-select2-dom="1" data-select2-search="1" data-input-cv >
                            <option value="">&nbsp;</option>
                            <?php
                            $langSelected = $employeeCvEav->getVal('lang_en_level');
                            if (!in_array($langSelected, $langsLevel['en'])) {
                                array_unshift($langsLevel['en'], $langSelected);
                            }
                            ?>
                            @foreach ($langsLevel['en'] as $lang)
                                <option value="{!!$lang!!}"{!!$langSelected == $lang ? ' selected' : ''!!}>{!!$lang!!}</option>
                            @endforeach
                        </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <img src="{{ $avatar }}" class="img-responsive cv-avatar" />
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="field develop"></label>
            <div class="input-box col-md-8">
                <input name="eav[field_dev]" class="form-control" {!!$disabledInput!!}
                    value="" data-db-lang="field_dev" data-input-cv
                    data-valid-type='{"maxlength":255}' data-lang-placeholder="placeholder field develop" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="experience year"></label>
            <div class="input-box col-md-8">
                <input name="eav_s[exper_year]" class="form-control" {!!$disabledInput!!}
                    value="{!!$employeeCvEav->getVal('exper_year')!!}" data-input-cv
                    data-valid-type='{"number": true,"min":0, "max":100}' data-lang-placeholder="placeholder experience year" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="experience japan"></label>
            <div class="input-box col-md-8">
                <input name="eav[exper_japan]" class="form-control" {!!$disabledInput!!}
                    value="" data-input-cv
                    data-db-lang="exper_japan"
                    data-valid-type='{"maxlength": 255}'
                    data-lang-placeholder="placeholder experience japan" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-4 control-label" data-lang-r="responsible"></label>
            <div class="input-box col-md-8">
                <?php
                $roleSelected = $employeeCvEav->getVal('role');
                if (is_numeric($roleSelected)) {
                    $roleSelected = [$roleSelected];
                } else {
                    $roleSelected = $roleSelected ? json_decode($roleSelected, true) : [];
                }
                ?>
                <select name="eav_s[role][]" class="form-control" multiple {!!$disabledInput!!}
                    data-select2-dom="1" data-select2-search="1" data-input-cv>
                    <option value="">&nbsp;</option>
                    @foreach ($roles as $roleId => $roleLabel)
                    <option value="{{ $roleId }}" {{ in_array($roleId, $roleSelected) ? 'selected' : '' }}>{{ $roleLabel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>
<div class="row form-horizontal">
    <div class="col-md-12">
        <div class="form-group">
            <label class="col-md-2 control-label" data-lang-r="personal summary" d-excel-text="statement"></label>
            <div class="input-box col-md-10">
                <textarea name="eav_t[statement]" class="form-control" {!!$disabledInput!!}
                    data-input-cv data-valid-type='{"maxlength": 5000}'
                    data-db-lang="statement" rows="6" d-excel-br="statement"
                    data-lang-placeholder="placeholder Personal Statement"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="col-md-2 control-label" data-lang-r="reference" d-excel-text="reference"></label>
            <div class="input-box col-md-10">
                <textarea name="eav_t[reference]" class="form-control" {!! $disabledInput !!}
                          data-input-cv data-valid-type='{"maxlength": 5000}'
                          data-db-lang="reference" rows="6" d-excel-br="reference"
                          data-lang-placeholder="reference"></textarea>
            </div>
        </div>
    </div>
</div>
