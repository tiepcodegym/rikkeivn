<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class AddPerformanceRequest extends Request
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
    public static function rules()
    {
        return [
            'end_at' => 'required|date',
        ];
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        return [
            'end_at.required' => trans('project::message.The end at field is required'),
            'end_at.date_format' => trans('project::message.The end at does not match the format Y-M-d'),
        ];
    }

    /**
     * validate data 
     * @param array
     * @return validator
     */
    public static function validateData($data = array())
    {
        $rules = self::rules();
        return Validator::make($data, $rules, self::messagesValidates());
    }
}
