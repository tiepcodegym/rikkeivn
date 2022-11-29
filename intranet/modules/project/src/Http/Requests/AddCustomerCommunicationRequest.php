<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Project\View\ValidatorExtend;

class AddCustomerCommunicationRequest extends Request
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
    public static function rules($type)
    {
        $arrayRules = [];
        $allNameTab = Task::getAllNameTabWorkorder();
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) {
            $arrayRules['role_' . $allNameTab[$type] . '_' . $i] =  'required';
            $arrayRules['customer_' . $i] =  'required';
        }
        return $arrayRules;
    }

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates($type)
    {
        $arrayMessages = [];
        $numberRecord = Input::get('number_record');
        $allNameTab = Task::getAllNameTabWorkorder();
        for ($i = 1; $i <= $numberRecord; $i++) {
            $arrayMessages['description_' . $allNameTab[$type] . '_' . $i . '.required'] =  trans('project::message.The role field is required');
            $arrayMessages['customer_' . $i . '.required'] =  trans('project::message.The customer field is required');
        }
        return $arrayMessages;
    }

    /**
     * validate data
     * @param array
     * @return validator
     */
    public static function validateData($data)
    {
        $allNameTab = Task::getAllNameTabWorkorder();
        $data['role_'. $allNameTab[$data['type']] . '_' . 1] = $data['role_1'];
        $data['customer_' . 1] = $data['employee_1'];
        ValidatorExtend::addWO();
        $rules = self::rules($data['type']);
        return Validator::make($data, $rules, self::messagesValidates($data['type']));
    }
}
