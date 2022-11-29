<?php

namespace Rikkei\Project\Http\Requests;

use Rikkei\Project\Http\Requests\Request;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\SourceServer;
use Illuminate\Support\Facades\Input;
use Illuminate\Contracts\Validation\Validator;
use Rikkei\Project\View\ValidatorExtend;

class CreateOperationRequest extends Request
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
        return $rules = [
            'name' => "required|max:255|unique:{$tableProject},name",
            'datadetai.*.teamId' => 'required',
            'datadetai.*.approved_production_cost' => 'required',
            'datadetai.*.year' => 'required',
            'datadetai.*.detail.*.teamId' => 'required',
            'datadetai.*.detail.*.approved_production_cost' => 'required',
            'datadetai.*.detail.*.price' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => trans('project::message.The name field is required'),
            'name.max' => trans('project::message.Please enter name a value between 1 and 255 characters long'),
            'name.unique' => trans('project::message.The value of name field must be unique'),
            'datadetai.teamId' => trans('project::message.The group field is required'),
            'datadetai.approved_production_cost' => trans('project::message.The cost_approved_production field is required'),
            'datadetai.price' => trans('project::message.The price field is required')
        ];
    }
}
