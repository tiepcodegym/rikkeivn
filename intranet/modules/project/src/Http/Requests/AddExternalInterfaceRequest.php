<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;

class AddExternalInterfaceRequest extends Request
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
            $arrayRules['name_' . $i] =  'required';
            $arrayRules['position_' . $i] =  'required';
            $arrayRules['responsibilities_' . $i] =  'required';
            $arrayRules['contact_' . $i] =  'required';
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
            $arrayMessages['name_' . $i . '.required'] =  trans('project::message.The name field is required');
            $arrayMessages['position_' . $i . '.required'] =  trans('project::message.The position field is required');
            $arrayMessages['responsibilities_' . $i . '.required'] =  trans('project::message.The responsibilities field is required');
            $arrayMessages['contact_' . $i . '.required'] =  trans('project::message.The contact field is required');
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
