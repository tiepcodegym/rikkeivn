<?php

namespace Rikkei\Education\Http\Requests;

use Rikkei\Education\Http\Requests\Request;
use Rikkei\Education\Model\SettingEducation;

class UpdateSettingEducationRequest extends Request
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

    public static function rules()
    {
        $table = SettingEducation::getTableName();
        $rules = [];
        $rules['code'] = 'required|max:2|unique:'.$table.',code,'.request()->id.'|regex:/^[A-Za-z\s-_]+$/|';
        $rules['name'] = 'required';
        return $rules;
    }

    public function messages()
    {
        return [
            'code.required' => trans('education::message.Code education is required'),
            'code.unique' => trans('education::message.This code education is exist'),
            'code.max' => trans('education::message.This code education max length 2'),
            'code.regex' => trans('education::message.This code education not numberic'),
            'name.required' => trans('education::message.The name is required')
        ];
    }
}
