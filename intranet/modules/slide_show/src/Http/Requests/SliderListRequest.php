<?php

namespace Rikkei\SlideShow\Http\Requests;

use Rikkei\SlideShow\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;

class SliderListRequest extends Request
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
            'date' => 'date_format:Y-m-d',
        ];
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        return [
            'date.date_format' => trans('slide_show::message.The date must be the format Y-m-d'),
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
