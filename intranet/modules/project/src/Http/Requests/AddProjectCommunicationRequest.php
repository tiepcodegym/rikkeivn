<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Project\View\ValidatorExtend;

class AddProjectCommunicationRequest extends Request
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
    public static function rules($input, $project = null)
    {
        $arrayRules = [];
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) { 
            $arrayRules['type_' . $i] =  'required';
            $arrayRules['method_' . $i] =  'required';
        }
        return $arrayRules;
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates($input, $project = null)
    {
        $arrayMessages = [];
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) { 
            $arrayMessages['type_' . $i . '.required'] =  trans('project::message.The type field is required');
            $arrayMessages['method_' . $i . '.required'] =  trans('project::message.The method field is required');
        }
        return $arrayMessages;
    }

    /**
     * validate data 
     * @param array
     * @return validator
     */
    public static function validateData($data = array(), $project = null)
    {
        ValidatorExtend::addWO();
        $rules = self::rules($data, $project);
        return Validator::make($data, $rules, self::messagesValidates($data, $project));
    }
}
