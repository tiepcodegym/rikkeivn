<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Project\View\ValidatorExtend;

class AddSecurityRequest extends Request
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
            $arrayRules['content_' . $i] =  'required';
            $arrayRules['description_security_' . $i] =  'required';
            $arrayRules['member_' . $i] =  'required';
            $arrayRules['period_security_' . $i] = 'required';
            $arrayRules['procedure_security_' .$i] = 'required';
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
            $arrayMessages['content_' . $i . '.required'] =  trans('project::message.The content field is required');
            $arrayMessages['description_security_' . '_' . $i . '.required'] =  trans('project::message.The description field is required');
            $arrayMessages['member_' . $i . '.required'] =  trans('project::message.The member field is required');
            $arrayMessages['period_security_' . $i . '.required'] =  trans('project::message.The period field is required');
            $arrayMessages['procedure_security_' . $i . '.required'] =  trans('project::message.The procedure field is required');
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
        $data['description_security' . '_' . 1] = $data['description_1'];
        $data['member_security' . '_' . 1] = $data['member_1'];
        $data['period_security' . '_' . 1] = $data['period_1'];
        $data['procedure_security' . '_' . 1] = $data['procedure_1'];
        $rules = self::rules($data, $project);
        return Validator::make($data, $rules, self::messagesValidates($data, $project));
    }
}
