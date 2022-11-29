<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\SourceServer;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\View\ValidatorExtend;

class CreateProjectRequest extends Request
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
    public function rules()
    {
        ValidatorExtend::addWO();
        $tableProject = Project::getTableName();
        $tableProjectMeta = ProjectMeta::getTableName();
        $tableSourceServer = SourceServer::getTableName();
        $locBaseline = Input::get('lineofcode_baseline');
        $locCurrent = Input::get('lineofcode_current');
        if (!is_numeric($locBaseline) || strlen($locBaseline) > 255) {
            $rules['lineofcode_baseline'] = 'integer';
        }
        if (!is_numeric($locCurrent) || strlen($locCurrent) > 255) {
            $rules['lineofcode_current'] = 'integer';
        }
        $rules['billable_effort'] = 'required|numeric|greater_than:0';
        $rules['plan_effort'] = 'required|numeric|greater_than:0';
        $rules['cost_approved_production'] = 'required';
        $rules['kind_id'] = 'required';
        if($id = Input::get('project_id')) {
            $idProjectMeta = Input::get('project_meta_id');
            $rules = [
                'name' => "required|max:255|unique:{$tableProject},name,".(int)$id.",id,deleted_at,NULL",
                'team_id' => 'required',
                'start_at' => 'required',
                'project_code' => "required|unique:{$tableProject},project_code,".(int)$id.",id,deleted_at,NULL",
            ];
            if (Input::has('is_check_redmine')) {
                $rules['id_redmine'] = 'required|max:100|source_servers:' .$id;
            } else {
                $rules['id_redmine'] = 'max:100|source_servers:' .$id;
            }
            if (Input::has('is_check_git')) {
                $rules['id_git'] = 'required|max:100|source_servers:' .$id;
            } else {
                $rules['id_git'] = 'max:100|source_servers:' .$id;
            }
            if (Input::has('is_check_svn')) {
                $rules['id_svn'] = 'required|max:100|source_servers:' .$id;
            } else {
                $rules['id_svn'] = 'max:100|source_servers:' .$id;
            }
        } elseif (Input::get('clone_id')) {
            $rules = [
                'name' => "required|max:255|unique:{$tableProject},name,NULL,id,deleted_at,NULL",
                'billable_effort' => "nullable",
                'plan_effort' => "nullable",
                'cost_approved_production' => "nullable",
                'kind_id' => "nullable",
            ];
        } else {
            $id = null;
            $rules = [
                'name' => "required|max:255|unique:{$tableProject},name,NULL,id,deleted_at,NULL",
                'team_id' => 'required',
                'start_at' => 'required|date_format:Y-m-d|before:end_at',
                'end_at' => 'required|date_format:Y-m-d',
                'manager_id' => 'required',
            ];
            if (Input::has('is_check_redmine')) {
                $rules['id_redmine'] = 'required|max:100|source_servers:' .$id;
            } else {
                $rules['id_redmine'] = 'max:100|source_servers:' .$id;
            }
            if (Input::has('is_check_git')) {
                $rules['id_git'] = 'required|max:100|source_servers:' .$id;
            } else {
                $rules['id_git'] = 'max:100|source_servers:' .$id;
            }
            if (Input::has('is_check_svn')) {
                $rules['id_svn'] = 'required|max:100|source_servers:' .$id;
            } else {
                $rules['id_svn'] = 'max:100|source_servers:' .$id;
            }
            if (Input::has('team')) {
                $pqa = Team::getTeamTypePqa();
                $check = true;
                foreach (Input::get('team') as $item) {
                    if (!in_array($item, $pqa)) {
                        $check= false;
                        break;
                    }
                }
            }
        }

        return $rules;
    }

    public function messages()
    {
    	return [
            'name.required' => trans('project::message.The name field is required'),
            'name.max' => trans('project::message.Please enter name a value between 1 and 255 characters long'),
            'name.unique' => trans('project::message.The value of name field must be unique'),
            'team_id.required' => trans('project::message.The team field is required'),
    		'sale_id.required' => trans('project::message.The sale field is required'),
            'start_at.required' => trans('project::message.The start at field is required'),
            'start_at.date' => trans('project::message.The start at must be format YY-MM-DD'),
            'start_at.before' => trans('project::message.The start at must be before end at'),
            'end_at.required' => trans('project::message.The end at field is required'),
            'end_at.date' => trans('project::message.The end at must be format YY-MM-DD'),
            'project_code.required' => trans('project::message.The short project name field is required'),
            'project_code.unique' => trans('project::message.The value of short project name field must be unique'),
            'schedule_link.required' => trans('project::message.The plan - schedule link field is required'),
            'schedule_link.url' => trans('project::message.Please enter plan - schedule link a valid URL'),
            'schedule_link.unique' => trans('project::message.The value of plan - schedule link field must be unique'),
            'lineofcode_baseline.integer' => trans('project::message.Please enter line of code baseline a valid number'),
            'lineofcode_current.integer' => trans('project::message.Please enter line of code current a valid number'),
            'id_redmine.max' => trans('project::message.Please enter id redmine a value between 1 and 100 characters long.'),
            'id_redmine.source_servers' => trans('project::message.The value of id redmine field must be unique'),
            'id_git.max' => trans('project::message.Please enter id git a value between 1 and 100 characters long.'),
            'id_git.source_servers' => trans('project::message.The value of it git field must be unique'),
            'id_svn.max' => trans('project::message.Please enter id svn a value between 1 and 100 characters long.'),
            'id_svn.source_servers' => trans('project::message.The value of id svn field must be unique'),
            'billable_effort.required' => trans('project::message.The billable effort field is required'),
            'billable_effort.numeric' => trans('project::message.The billable effort must be numberic'),
            'billable_effort.greater_than' => trans('project::message.The value must be greater than zero'),
            'plan_effort.required' => trans('project::message.The plan effort field is required'),
            'plan_effort.numeric' => trans('project::message.The plan effort must be numberic'),
            'plan_effort.greater_than' => trans('project::message.The value must be greater than zero'),
            'cost_approved_production' => trans('project::message.The cost_approved_production field is required'),
            'kind_id' => trans('project::message.The project kind field is required'),
    	];
    }
}
