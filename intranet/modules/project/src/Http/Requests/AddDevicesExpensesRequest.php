<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class AddDevicesExpensesRequest extends Request
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
        $arrayRules = [];
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) {
            $arrayRules['time_' . $i] =  'required';
            $arrayRules['amount_' . $i] =  'required|numeric|min:0';
            $arrayRules['description_' . $i] =  'required';
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
            $arrayMessages['time_' . $i . '.required'] =  trans('project::message.Time');
            $arrayMessages['amount_' . $i . '.required'] =  trans('project::message.The amount field is required');
            $arrayMessages['description_' . $i . '.required'] =  trans('project::message.The descriptione field is required');
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
