<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Rikkei\Project\Model\Project;
use Illuminate\Support\Facades\Validator;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\View\ValidatorExtend;
use Rikkei\Team\Model\Team;

class EditBasicInforProjectRequest extends Request
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
    public static function rules($data)
    {
        $tableProject = Project::getTableName();
        $tableProjectMeta = ProjectMeta::getTableName();
        $rules = [];
        if(!isset($data['name'])) {
            $rules['team_id'] = 'required';
        } else {
            if ($data['name'] == 'name') {
                $rules['name'] = "required|max:255|unique:{$tableProject},name,".(int)$data['project_id'].",id,deleted_at,NULL";
            }
            if ($data['name'] == 'start_at') {
                $rules['start_at'] = "required|date_format:Y-m-d|before:end_at";
            }
            if ($data['name'] == 'end_at') {
                $rules['end_at'] = "required|date_format:Y-m-d|after:start_at";
            }
            if ($data['name'] == 'billable_effort') {
                $rules['billable_effort'] = 'required|numeric|greater_than:0';
            }
            if ($data['name'] == 'plan_effort') {
                $rules['plan_effort'] = 'required|numeric|greater_than:0';
            }
            if ($data['name'] == 'cost_approved_production') {
                $rules['cost_approved_production'] = 'required|numeric|greater_than:0';
            }
            if ($data['name'] == 'lineofcode_baseline') {
                $rules['lineofcode_baseline'] = 'integer';
            }
            if ($data['name'] == 'lineofcode_current') {
                $rules['lineofcode_current'] = 'integer';
            }
            if ($data['name'] == 'id_git_external') {
                $rules['id_git_external'] = 'url';
            }
            if ($data['name'] == 'id_redmine_external') {
                $rules['id_redmine_external'] = 'url';
            }
            if ($data['name'] == 'schedule_link') {
                $idProjectMeta = ProjectMeta::where('project_id', $data['project_id'])->first()->id;
                $rules['schedule_link'] = "url|unique:{$tableProjectMeta},schedule_link,".(int)$idProjectMeta.",id,deleted_at,NULL";
            }
            if ($data['name'] == 'company_id') {
                $rules['company_id'] = 'required';
            }
            if ($data['name'] == 'cust_contact_id') {
                $rules['cust_contact_id'] = 'required';
            }
            if ($data['name'] == 'kind_id') {
                $rules['kind_id'] = 'required';
            }
            if ($data['name'] == 'cus_email') {
                $rules['cus_email'] = 'email';
            }
            if ($data['name'] == 'Classification') {
                $rules['Classification'] = 'required';
            }
            $pqa = Team::getTeamTypePqa();
            if (isset($data['team']) && !empty($data['team'])) {
                $check = true;
                foreach ($data['team'] as $item) {
                    if (!in_array($item, $pqa)) {
                        $check= false;
                        break;
                    }
                }
            }
        }
        return $rules;
    }

    public static function messagesValidates()
    {
    	return [
            'name.required' => trans('project::message.The name field is required'),
            'name.max' => trans('project::message.Please enter name a value between 1 and 255 characters long'),
            'name.unique' => trans('project::message.The value of name field must be unique'),
            'team_id.required' => trans('project::message.The team field is required'),
            'start_at.required' => trans('project::message.The start at field is required'),
            'start_at.date' => trans('project::message.The start at must be format YY-MM-DD'),
            'start_at.before' => trans('project::message.The start at must be before end at'),
            'end_at.required' => trans('project::message.The end at field is required'),
            'end_at.date' => trans('project::message.The end at must be format YY-MM-DD'),
            'end_at.after' => trans('project::message.The end at must be after start at'),
            'project_code.required' => trans('project::message.The short project name field is required'),
            'project_code.unique' => trans('project::message.The value of short project name field must be unique'),
            'billable_effort.required' => trans('project::message.The billable effort field is required'),
            'billable_effort.numeric' => trans('project::message.The billable effort must be numberic'),
            'plan_effort.required' => trans('project::message.The plan effort field is required'),
            'plan_effort.numeric' => trans('project::message.The plan effort must be numberic'),
            'lineofcode_baseline.integer' => trans('project::message.Please enter line of code baseline a valid number'),
            'lineofcode_current.integer' => trans('project::message.Please enter line of code current a valid number'),
            'id_git_external.url' => trans('project::message.Please enter external repository link a valid URL'),
            'id_redmine_external.url' => trans('project::message.Please enter issue tracker link a valid URL'),
            'schedule_link.url' => trans('project::message.Please enter schedule link a valid URL'),
            'schedule_link.unique' => trans('project::message.The value of schedule link field must be unique'),
            'plan_effort.greater_than' => trans('project::message.The value must be greater than zero'),
            'billable_effort.greater_than' => trans('project::message.The value must be greater than zero'),
            'cost_approved_production.greater_than' => trans('project::message.The value must be greater than zero'),
            'prog_langs.required' => trans('project::message.The programming language field is required'),
            'company_id.required' => trans('project::message.The company field is required'),
            'cus_email.email' => trans('project::message.Incorrect email format'),
    	];
    }

    /**
     * validate data
     * @param array
     * @return validator
     */
    public static function validateData($data = array())
    {
        $rules = self::rules($data);
        if(!isset($data['name'])) {
            $name = 'team_id';
            $value = $data['value'];
        } else {
            $name = $data['name'];
            $value = $data['value'];
        }
        $dataValidate[$name] = $value;
        if(isset($data['end_at'])) {
            $dataValidate['end_at'] = $data['end_at'];
        }
        if(isset($data['start_at'])) {
            $dataValidate['start_at'] = $data['start_at'];
        }
        ValidatorExtend::addWO();
        return Validator::make($dataValidate, $rules, self::messagesValidates());
    }
}
