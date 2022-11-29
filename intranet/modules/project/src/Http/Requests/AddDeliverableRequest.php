<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\Model\Project;
use Rikkei\Project\View\ValidatorExtend;

class AddDeliverableRequest extends Request
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
        $projectId = Input::get('project_id');
        $isAddNew = 0;
        if (Input::has('isAddNew')) {
            $isAddNew = 1;
        }
        $numberRecord = Input::get('number_record');
        for ($i = 1; $i <= $numberRecord; $i++) { 
            $arrayRules['title_' . $i] =  'required';
            $arrayRules['committed_date_' . $i] =  'required|date|betwwen_time_project:' 
                . $time['startAt']. ','. $time['endAt'];
            $arrayRules['re_commited_date_' . $i] =  'date';
            $arrayRules['actual_date_' . $i] =  'date';
            $arrayRules['change_request_by_' . $i] =  'required_with:re_commited_date_' . $i;
            $arrayRules['stage_' . $i] =  'required|exits_stage:'. $projectId. ','. $isAddNew;
            $arrayRules['note_' . $i] =  'required_with:re_commited_date_' . $i;
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
            $arrayMessages['title_' . $i . '.required'] =  trans('project::message.The title field is required');
            $arrayMessages['committed_date_' . $i . '.required'] =  trans('project::message.The committed date field is required');
            $arrayMessages['committed_date_' . $i . '.date'] =  trans('project::message.The committed date must be format YY-MM-DD');
            $arrayMessages['committed_date_' . $i . '.betwwen_time_project'] =  trans('project::message.Committed date of delivery must be in time of project');
            $arrayMessages['re_commited_date_' . $i . '.required'] =  trans('project::message.The committed date field is required');
            $arrayMessages['re_commited_date_' . $i . '.date'] =  trans('project::message.The committed date must be format YY-MM-DD');
            $arrayMessages['re_commited_date_' . $i . '.betwwen_time_project'] =  trans('project::message.Re-plan date of delivery must be in time of project');
            $arrayMessages['actual_date_' . $i . '.date'] =  trans('project::message.The actual date must be format YY-MM-DD');
            $arrayMessages['change_request_by_' . $i . '.required_with'] =  trans('project::message.The Change request by field is required');
            $arrayMessages['stage_' . $i . '.required'] =  trans('project::message.The stage does not exists');
            $arrayMessages['stage_' . $i . '.exits_stage'] =  trans('project::message.The stage does not exists');
            $arrayMessages['note_' . $i . '.required_with'] =  trans('project::message.The Note field is required');
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
