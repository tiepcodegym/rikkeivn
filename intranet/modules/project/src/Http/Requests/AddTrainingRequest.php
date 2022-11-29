<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\ValidatorExtend;

class AddTrainingRequest extends Request
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
        $time = self::getTime($input, $project = null);
        $arrayRules = [];
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) { 
            $arrayRules['topic_' . $i] =  'required';
            $arrayRules['description_' . $i] =  'required';
            $arrayRules['member_' . $i] =  'required';
            $arrayRules['start_at_' . $i] = 'required|date_format:Y-m-d|before_or_equal:end_at_'. $i .',' .$input['end_at_'.$i]. '|after:'.$time['startAt'];
            $arrayRules['end_at_' .$i] = 'required|date_format:Y-m-d|before:'.$time['endAt'];
        }
        return $arrayRules;
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates($input, $project = null)
    {   
        $time = self::getTime($input, $project = null);
        $arrayMessages = [];
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) { 
            $arrayMessages['topic_' . $i . '.required'] =  trans('project::message.The topic field is required');
            $arrayMessages['description_' . $i . '.required'] =  trans('project::message.The description field is required');
            $arrayMessages['member_' . $i . '.required'] =  trans('project::message.The participants field is required');

            $arrayMessages['start_at_' . $i . '.required'] =  trans('project::message.The start date field is required');
            $arrayMessages['start_at_' . $i . '.date'] =  trans('project::message.The start at must be format YY-MM-DD');
            $arrayMessages['start_at_' . $i . '.before_or_equal'] =  trans('project::message.The start date must be before or equal and date');
            $arrayMessages['start_at_' . $i . '.after'] =  trans('project::message.The start at must be after ') . $time['startAt'];
            $arrayMessages['end_at_' . $i . '.required'] =  trans('project::message.The end date field is required');
            $arrayMessages['end_at_' . $i . '.date'] =  trans('project::message.The end at must be format YY-MM-DD');
            $arrayMessages['end_at_' . $i . '.before'] =  trans('project::message.The end at must be before ') . $time['endAt'];
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

    /**
     * get time running of project
     * @param array
     * @param array
     * @return array
     */
    public static function getTime($data = array(), $project = null)
    {
        if (!$project) {
            $project = Project::find($data['project_id']);
        }
        $projectDraft = $project->projectChild;
        if (count($projectDraft)) {
            $project = $projectDraft;
        }
        $now = \Carbon\Carbon::now();
        if (!$project->end_at) {
            $endAt = $now;
        } else {
            $endAt = $project->end_at;
        }
        if (!$project->start_at) {
            $startAt = $now;
        } else {
            $startAt = $project->start_at;
        }

        $result = [];
        $result['startAt'] = $startAt->modify('-1 days')->format('Y-m-d');
        $result['endAt'] = $endAt->modify('+1 days')->format('Y-m-d');
        return $result;
    }
}
