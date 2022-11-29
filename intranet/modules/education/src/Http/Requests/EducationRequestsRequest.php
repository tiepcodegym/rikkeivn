<?php

namespace Rikkei\Education\Http\Requests;

use Rikkei\Education\Http\Requests\Request;
use Rikkei\Education\Model\EducationRequest;

class EducationRequestsRequest extends Request
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

    public static function rules()
    {
        $statusArr          = implode(',', array_keys(EducationRequest::getInstance()->getStatus()));
        $scopeTotalArr      = implode(',', array_keys(EducationRequest::getInstance()->getScopeTotal()));
        $statusNotAllowArr  = implode(',', array_keys(EducationRequest::getInstance()->getStatusNotAllow()));
        return [
            'title'         => 'required|max:100',
            'description'   => 'required',
            'status'        => 'required|in:' . $statusArr,
            'team_id'       => 'required|array',
            'type_id'       => 'required|integer',
            'object'        => 'required|array',
            'tag'           => 'required|array',
            'assign_id'     => 'integer',
            'scope_total'   => 'integer|in:' . $scopeTotalArr,
            'teacher_id'    => 'integer',
            'reason'        => 'required_if:status,' . $statusNotAllowArr,
        ];
    }

    public function messages()
    {
        return [
            'title.required' => trans('education::view.message.The title is required'),
            'title.min' => trans('education::view.message.The title min 5 characters'),
            'title.max' => trans('education::view.message.The title max 100 characters'),
            'object.required' => trans('education::view.message.The object is required'),
            'scope_total.required' => trans('education::view.message.The scope is required'),
            'scope_total.required' => trans('education::view.message.The scope is required'),
            'description.required' => trans('education::view.message.The description is required'),
            'status.required' => trans('education::view.message.The status is required'),
            'team_id.required' => trans('education::view.message.The division is required'),
            'tag.required' => trans('education::view.message.The tag is required'),
            'reason.required_if' => trans('education::view.message.The reason is required'),
            'type_id.required' => trans('education::view.message.The course field is required'),
        ];
    }
}
