<?php

namespace Rikkei\SlideShow\Http\Requests;

use Rikkei\SlideShow\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;

class BirthdayRequest extends Request
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
            'birthday_company' => 'required|date_format:Y-m-d H:i A',
        ];
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        return [
            'birthday_company.date_format' => trans('slide_show::message.The birthday company does not match the format Y-m-d H:i A'),
            'birthday_company.required' => trans('slide_show::message.The birthday company is required'),
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
