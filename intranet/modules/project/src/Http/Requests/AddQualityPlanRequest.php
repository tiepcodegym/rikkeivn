<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;

class AddQualityPlanRequest extends Request
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
            // 'content' => 'required'
        ];
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        return [
            'content.required' => trans('project::message.The content field is required')
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
