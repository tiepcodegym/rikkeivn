<?php

namespace Rikkei\Education\Http\Requests;

use Rikkei\Education\Http\Requests\Request;

class TemplateMailRequest extends Request
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
        $rules['description'] = 'required';

        return $rules;
    }

    public function messages()
    {
        return [
            'description.required' => trans('education::message.The description is required')
        ];
    }
}
