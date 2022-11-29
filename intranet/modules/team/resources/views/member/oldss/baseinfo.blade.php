<?php
use Carbon\Carbon; ?>
<table class="table dataTable tbl-cv">
    <colgroup>
        <col style="width: 10%; max-width: 110px;">
        <col style="width: 25%; min-width: 300px;">
        <col style="width: 10%; min-width: 120px;">
        <col style="width: 25%;">
        <col style="width: 10%;">
        <col style="width: 20%;">
    </colgroup>
    <tbody>
        <tr>
            <td data-lang-r="full name"></td>
            <td>{{ $employeeModelItem->name }}</td>
            <td>
                <span data-lang-r="kana name" data-lang-show="ja"></span>
            </td>
            <td>
                <div data-lang-show="ja">
                    <a href="#" data-name="employee[japanese_name]" data-placement="bottom" class="xeditor-label"
                        data-type="text"
                        data-edit-dom="submit"
                        data-edit-type="normal"
                        data-valid-type='{"maxlength":255}'>{{ $employeeModelItem->japanese_name }}</a>
                </div>
            </td>
            <td data-lang-r="sex"></td>
            <td>
                <a href="#" data-name="employee[gender]" 
                    data-placement="bottom" 
                    data-type="select"
                    data-edit-type="normal"
                    class="xeditor-label"
                    data-edit-dom="submit"
                    data-valid-type='{"maxlength":255}'
                    data-edit-label="select-lang"
                    data-value="{{$employeeModelItem->gender}}"
                    data-trans-values='{"1": "gender_1", "0": "gender_0"}'
                    data-inputclass="xeditor-select"
                    ></a>
            </td>
        </tr>
        <tr>
            <td data-lang-r="birthday"></td>
            <td>
                <a href="#"data-name="employee[birthday]"
                    data-placement="bottom"
                    data-type="date"
                    data-edit-dom="submit"
                    class="xeditor-label"
                    data-edit-type="normal"
                    data-valid-type='{"date":true}'
                    data-mode="popup">{{ $employeeModelItem->birthday }}</a>
            </td>
            <td data-lang-r="old"></td>
            <td>
                <?php if ($employeeModelItem->birthday):
                    $old = Carbon::now()->year - Carbon::parse($employeeModelItem->birthday)->year; ?>
                    <span>{!! $old > 0 ? $old : '' !!}</span>
                    <span data-lang-r="year old"></span>
                <?php endif; ?>
            </td>
            <td data-lang-r="address home"></td>
            <td>
                <a href="#" data-name="eav[address_home]" 
                    data-placement="bottom" 
                    data-type="text"
                    data-edit-dom="submit"
                    data-edit-type="normal"
                    class="xeditor-label"
                    data-valid-type='{"maxlength":255}'
                    data-db-lang="address_home"
                    >{{ $employeeModelItem->address_home }}</a>
            </td>
        </tr>
        <tr>
            <td data-lang-r="address"></td>
            <td colspan="5">
                <a href="#" data-name="eav[address]"
                    data-placement="bottom" 
                    data-type="text"
                    data-edit-type="normal"
                    class="xeditor-label"
                    data-edit-dom="submit"
                    data-valid-type='{"maxlength":255}'
                    data-db-lang="address"
                    ></a>
            </td>
        </tr>
        <tr>
            <td data-lang-r="school graduation"></td>
            <td>
                <a href="#" data-name="eav[school_graduation]" 
                    data-placement="top" 
                    data-type="text"
                    data-edit-type="normal"
                    class="xeditor-label"
                    data-edit-dom="submit"
                    data-valid-type='{"maxlength":255}'
                    data-db-lang="school_graduation"
                    ></a>
            </td>
            <td data-lang-r="field develop"></td>
            <td>
                <a href="#" data-name="eav_text[field_dev]" 
                    data-placement="right" 
                    data-type="textarea"
                    data-edit-type="normal"
                    class="xeditor-label"
                    data-edit-dom="submit"
                    data-valid-type='{"maxlength":5000}'
                    data-db-lang="field_dev"
                    ></a>
            </td>
            <td data-lang-r="level japanese"></td>
            <td>
                <a href="#" data-name="eav[lang_ja]" 
                    data-placement="top" 
                    data-type="text"
                    data-edit-type="normal"
                    data-edit-dom="submit"
                    class="xeditor-label"
                    data-valid-type='{"maxlength":255}'
                    data-db-lang="lang_ja"
                    ></a>
            </td>
        </tr>
        <tr>
            <td data-lang-r="experience year"></td>
            <td>
                <a href="#" data-name="eav[exper_year]" 
                    data-placement="top" 
                    data-type="text"
                    data-edit-dom="submit"
                    data-edit-type="normal"
                    class="xeditor-label"
                    data-valid-type='{"number": true,"min":0, "max":100}'
                    data-db-lang="exper_year"
                    ></a>
            </td>
            <td data-lang-r="scope develop"></td>
            <td>
                <a href="#" data-name="eav_text[scope_dev]" 
                    data-placement="top" 
                    data-type="textarea"
                    data-edit-dom="submit"
                    data-edit-type="normal"
                    class="xeditor-label"
                    data-valid-type='{"maxlength":5000}'
                    data-db-lang="scope_dev"
                    ></a>
            </td>
            <td data-lang-r="level english"></td>
            <td>
                <a href="#" data-name="eav[lang_en]" 
                    data-placement="top" 
                    data-type="text"
                    data-edit-dom="submit"
                    data-edit-type="normal"
                    class="xeditor-label"
                    data-valid-type='{"maxlength":255}'
                    data-db-lang="lang_en"
                    ></a>
            </td>
        </tr>
    </tbody>
</table>
