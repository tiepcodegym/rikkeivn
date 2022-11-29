<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Illuminate\Support\Facades\Validator;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\View\ValidatorExtend;
use Carbon\Carbon;
use Rikkei\Core\View\View as CoreView;

class AddProjectMemberRequest extends Request
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
        if (!$project) {
            $project = Project::find($input['project_id']);
        }
        $projectDraft = $project->projectChild;
        if (count($projectDraft)) {
            $project = $projectDraft;
        }
        $now = Carbon::now();
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
        $startAt->modify('-1 days');
        $endAt->modify('+1 days');
        $employeeIdInput = CoreView::getValueArray($input, ['employee_id']);
        $typeInput = CoreView::getValueArray($input, ['type']);
        $effortInput = CoreView::getValueArray($input, ['effort']);
        $maxEffort = Project::MAX_EFFORT;
        $startAtInput = CoreView::getValueArray($input, ['start_at']);
        $endAtInput = CoreView::getValueArray($input, ['end_at']);
        $idMember = CoreView::getValueArray($input, ['id']);
        $rules = [
            'start_at' => 'required|date_format:Y-m-d|before_or_equal:end_at,' .$endAtInput. '|after:' .$startAt->format('Y-m-d'),
            'end_at' => 'required|date_format:Y-m-d|before:' .$endAt->format('Y-m-d'),
            'type' => 'required',
        ];
        if (!$idMember) {
            $rules['employee_id'] = 'required|team_allocation:' .$employeeIdInput. ',' .$input['project_id']. ',' .$startAtInput. ',' .$endAtInput. ',' .$typeInput;
            $rules['effort'] = 'required|numeric|between:0,' .$maxEffort. '|effort_value:' .$effortInput. ','  .$employeeIdInput. ',' .$input['project_id']. ',' .$startAtInput. ',' .$endAtInput;
        } else {
            $projectMember = ProjectMember::find($idMember);
            if ($projectMember && $projectMember->parent_id) {
                $rules['employee_id'] = 'required|team_allocation:' .$employeeIdInput. ',' .$input['project_id']. ',' .$startAtInput. ',' .$endAtInput . ',' .$typeInput. ',' .$idMember. ',' .$projectMember->parent_id;
            } else {
                $rules['employee_id'] = 'required|team_allocation:' .$employeeIdInput. ',' .$input['project_id']. ',' .$startAtInput. ',' .$endAtInput. ',' .$typeInput. ',' .$idMember;
            }
            
            $rules['effort'] = 'required|numeric|between:0,' .$maxEffort. '|effort_value:' .$effortInput. ',' .$employeeIdInput. ',' .$input['project_id']. ',' .$startAtInput. ',' .$endAtInput. ',' .$idMember;
        }
        return $rules;
    }    

    /**
     * message validate
     * @return array
     */
    public static function messagesValidates()
    {
        $maxEffort = Project::MAX_EFFORT;
        return [
            'employee_id.required' => trans('project::message.The employee field is required'),
            'employee_id.unique' => trans('project::message.The employee field must be unique'),
            'employee_id.team_allocation' => trans('project::message.The employee field must be unique (time)'),
            'start_at.required' => trans('project::message.The start date field is required'),
            'end_at.required' => trans('project::message.The end date field is required'),
            'start_at.date' => trans('project::message.The start at must be format YY-MM-DD'),
            'end_at.date' => trans('project::message.The end at must be format YY-MM-DD'),
            'start_at.before_or_equal' => trans('project::message.The start at must be before end at'),
            'type.required' => trans('project::message.The type field is required'),
            'effort.required' => trans('project::message.The effort field is required'),
            'effort.between' => trans('project::message.The effort musbe between 0 - :max', ['max' => $maxEffort]),
            'effort.numeric' => trans('project::message.The effort must be an integer'),
            'effort.effort_value' => trans('project::message.Effort of a person must be less than or equal :max', ['max' => $maxEffort]),
            'type.type_project_member' => trans('project::message.PM already exists'),
        ];
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
        return Validator::make($data, $rules, self::messagesValidates());
    }
}
