<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\Task;
use Rikkei\Project\View\ValidatorExtend;

class AddAssumptionConstrainRequest extends Request
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
            $arrayRules['content_' . $i] =  'required';
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
            $arrayMessages['content_' . $i . '.required'] =  trans('project::message.The content field is required');
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

    public static function rulesAssumptionCons($type)
    {
        $arrayRules = [];
        $allNameTab = Task::getAllNameTabWorkorder();
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) {
            $arrayRules['description_' . $allNameTab[$type] . '_' . $i] =  'required';
        }
        return $arrayRules;
    }

    public static function messagesValidatesAssumptionCons($type)
    {
        $arrayMessages = [];
        $allNameTab = Task::getAllNameTabWorkorder();
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) {
            $arrayMessages['description_' . $allNameTab[$type] . '_' . $i . '.required'] =  trans('project::message.The description field is required');
        }
        return $arrayMessages;
    }

    public static function validateDataAssCons($data)
    {
        $allNameTab = Task::getAllNameTabWorkorder();
        $data['description_'. $allNameTab[$data['type']] . '_' . 1] = $data['description_1'];
        ValidatorExtend::addWO();
        $rules = self::rulesAssumptionCons($data['type']);
        return Validator::make($data, $rules, self::messagesValidatesAssumptionCons($data['type']));
    }
}
