<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\StageAndMilestone;
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\ValidatorExtend;

class StageAndMilestoneRequest extends Request
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
        $tableStage = StageAndMilestone::getTableName();
        $arrayRules = [];
        $numberRecord = Input::get('number_record');
        $projectId = Input::get('project_id');
        for ($i = 1; $i <= $numberRecord; $i++) { 
            if (Input::has('id')) {
                $stageId = (int) Input::get('id');
                $stage = StageAndMilestone::find($stageId);
                    $arrayRules['stage_' . $i] =  'required|';
            } else {
                $arrayRules['stage_' . $i] =  'required|';
            }
            $arrayRules['description_' . $i] =  'required';
            $arrayRules['milestone_' . $i] =  'required';
            $arrayRules['qua_gate_plan_' . $i] =  'required|date_format:Y-m-d|betwwen_time_project:' .$time['startAt']. ','. $time['endAt'];
            $arrayRules['qua_gate_actual_' . $i] =  'date_format:Y-m-d';
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
            $arrayMessages['stage_' . $i . '.required'] =  trans('project::message.The stage field is required');
            $arrayMessages['stage_' . $i . '.unique'] =  trans('project::message.The stage field must be unique');
            $arrayMessages['stage_' . $i . '.add_stage_milestone'] =  trans('project::message.The stage field must be unique');
            $arrayMessages['stage_' . $i . '.stage_milestone'] =  trans('project::message.The stage field must be unique');
            $arrayMessages['description_' . $i . '.required'] =  trans('project::message.The description field is required');
            $arrayMessages['milestone_' . $i . '.required'] =  trans('project::message.The milestone field is required');
            $arrayMessages['qua_gate_plan_' . $i . '.required'] =  trans('project::message.The quality gate plan date field is required');
            $arrayMessages['qua_gate_actual_' . $i . '.required'] =  trans('project::message.The quality gate actual date field is required');
            $arrayMessages['qua_gate_plan_' . $i . '.date'] =  trans('project::message.The quality gate plan date must be format YY-MM-DD');
            $arrayMessages['qua_gate_plan_' . $i . '.betwwen_time_project'] =  trans('project::message.The quality gate plan date must be in time of project');
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
        $rules = self::rules($data, $project);
        ValidatorExtend::addWO();
        return Validator::make($data, $rules, self::messagesValidates());
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
        $result['startAt'] = $startAt->format('Y-m-d');
        $result['endAt'] = $endAt->format('Y-m-d');
        return $result;
    }
}
