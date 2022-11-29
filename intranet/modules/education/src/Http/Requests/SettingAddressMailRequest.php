<?php

namespace Rikkei\Education\Http\Requests;

use Rikkei\Education\Http\Requests\Request;
use Rikkei\Education\Model\SettingAddressMail;

class SettingAddressMailRequest extends Request
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
        $rules['email'] = 'email|regex:/(.*)@rikkeisoft\.com/i';

        return $rules;
    }

    public function messages()
    {
        return [
            'email.regex' => trans('education::message.Unlike the format like mail@rikkeisoft.com')
        ];
    }
}
