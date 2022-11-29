<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class AddToolAndInfrastructureRequest extends Request
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
        Validator::extend('after_or_equal', function ($attribute, $value, $parameters, $validator) {
            return strtotime($validator->getData()[$parameters[0]]) <= strtotime($value);
        }, trans('project::message.End date must be greater than start date'));

        $arrayRules = [];
        $numberRecord = Input::get('number_record');

        for ($i = 1; $i <= $numberRecord; $i++) {
            $arrayRules['soft_hard_ware_' . $i] =  'required';
            $arrayRules['purpose_' . $i] =  'required';
            $arrayRules['start_date_' . $i] =  'required|date';
            $arrayRules['end_date_' . $i] =  'required|date|after_or_equal:start_date_'. $i;
        }

        return $arrayRules;
    }

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        $arrayMessages = [];
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) {
            $arrayMessages['soft_hard_ware_' . $i . '.required'] =  trans('project::message.The soft hard ware field is required');
            $arrayMessages['purpose_' . $i . '.required'] =  trans('project::message.The purpose field is required');
            $arrayMessages['start_date_' . $i . '.required'] =  trans('project::message.The start date field is required');
            $arrayMessages['end_date_' . $i . '.required'] =  trans('project::message.The end date field is required');
            $arrayMessages['end_date_' . $i . '.after_or_equal:start_date_'. $i ] =  trans('project::message.End date must be greater than start date');
        }

        return $arrayMessages;
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
