<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\Task;
use Rikkei\Project\View\ValidatorExtend;

class AddSkillRequest extends Request
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
    public static function rules($input)
    {
        $arrayRules = [];
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) { 
            $arrayRules['skill_' . $i] =  'required';
            $arrayRules['category_' . $i] =  'required';
            $arrayRules['course_name_' . $i] =  'required';
            $arrayRules['mode_' . $i] = 'required';
            $arrayRules['provider_' .$i] = 'required';
            $arrayRules['required_role_' .$i] = 'required';
            $arrayRules['hours_' .$i] = 'required|min:0|numeric';
            $arrayRules['level_' .$i] = 'required';
            $arrayRules['remark_' .$i] = 'required';
        }
        return $arrayRules;
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates($input)
    {
        $arrayMessages = [];
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) { 
            $arrayMessages['skill_' . $i . '.required'] =  trans('project::message.The skill field is required');
            $arrayMessages['category_' . '_' . $i . '.required'] =  trans('project::message.The category field is required');
            $arrayMessages['course_name_' . $i . '.required'] =  trans('project::message.The course name field is required');
            $arrayMessages['mode_' . $i . '.required'] =  trans('project::message.The mode field is required');
            $arrayMessages['provider_' . $i . '.required'] =  trans('project::message.The provider field is required');
            $arrayMessages['required_role_' . $i . '.required'] =  trans('project::message.The required for role field is required');
            $arrayMessages['hours_' . $i . '.required'] =  trans('project::message.The hours field is required');
            $arrayMessages['hours_' . $i . '.numeric'] =  trans('project::message.The hours field is integer');
            $arrayMessages['hours_' . $i . '.min'] =  trans('project::message.The hours field must be greater than 0');
            $arrayMessages['level_' . $i . '.required'] =  trans('project::message.The skill level assessment method field is required');
            $arrayMessages['remark_' . $i . '.required'] =  trans('project::message.The remark field is required');
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
        ValidatorExtend::addWO();
        $rules = self::rules($data);
        return Validator::make($data, $rules, self::messagesValidates($data));
    }
}
