<?php

namespace Rikkei\SlideShow\Http\Requests;

use Rikkei\SlideShow\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;

class SliderPasswordRequest extends Request
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public static function rules($data)
    {
        return  [
            'password' => 'required|min:8',
        ];
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        return [
            'password.required' => trans('slide_show::message.The password field is required'),
            'password.min' => trans('slide_show::message.The password must be great or equal 8 character'),
        ];
    }

    /**
     * validate data 
     * @param array
     * @return validator
     */
    public static function validateData($data = array())
    {
        $rules = self::rules($data);
        return Validator::make($data, $rules, self::messagesValidates());
    }
}
