<?php

namespace Rikkei\Education\Http\Requests;

use Rikkei\Education\Http\Requests\Request;

class StoreRegisterTeachingRequests extends Request
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
        $rules['title'] = 'required|max:255';
        $rules['tranning_hour'] = 'required|regex:/^\d+(\.\d{1,2})?$/';
        $rules['content'] = 'required';

        return $rules;
    }

    public function messages()
    {
        return [
            'title.required' => trans('education::message.Title is required'),
            'title.max' => trans('education::message.This title education max length 255'),
            'tranning_hour.required' => trans('education::message.Tranning hour is required'),
            'tranning_hour.regex' => trans('education::message.Tranning hour is number'),
            'content.required' => trans('education::message.Content is required')
        ];
    }
}
