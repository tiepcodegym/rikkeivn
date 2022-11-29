<?php

namespace Rikkei\Education\Http\Requests;

use Rikkei\Education\Http\Requests\Request;
use Rikkei\Education\Model\SettingEducation;

class SettingEducationRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $table = SettingEducation::getTableName();
        $rules['code'] = 'required|max:2|unique:'. $table .'|regex:/^[\D]*$/';
        $rules['name'] = 'required|max:255';
        return $rules;
    }

    public function messages()
    {
        return [
            'code.required' => trans('education::message.Code education is required'),
            'code.unique' => trans('education::message.This code education is exist'),
            'code.max' => trans('education::message.This code education max length 2'),
            'code.regex' => trans('education::message.This code education not numberic'),
            'name.required' => trans('education::message.The name is required'),
            'name.max' => trans('education::message.This name education max length 255')
        ];
    }
}
