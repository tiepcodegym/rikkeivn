<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class AddQualityRequest extends Request
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
        $rules = [];
        if (isset($data['billable_effort'])) {
            $rules['billable_effort'] = 'required|numeric';
        }
        if (isset($data['plan_effort'])) {
            $rules['plan_effort'] = 'required|numeric';
        }
        return $rules;
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        return [
            'billable_effort.required' => trans('project::message.The billable effort field is required'),
            'billable_effort.numeric' => trans('project::message.The billable effort must be numberic'),
            'plan_effort.required' => trans('project::message.The plan effort field is required'),
            'plan_effort.numeric' => trans('project::message.The plan effort must be numberic'),
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
