<?php
namespace Rikkei\Api\Http\Controllers\Operations;

use Illuminate\Http\Request;
use Log, Validator;
use Rikkei\Api\Helper\Operation as OperationHelper;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjectKind;

class OperationController extends Controller
{
    /** Get Operation Reports view manin [overview, member, project] */
    public function getOperationReports(Request $request)
    {
        try {
            $result = OperationHelper::swichViewManin($request);

            return [
                'success' => 1,
                'data' => $result
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => OperationHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /** Delete project Addition */
    public function deleteProjectAddition(Request $request)
    {
        try {
            $result = OperationHelper::deleteProjectAddition($request);

            return [
                'success' => 1,
                'data' => 'success'
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => OperationHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /** Create project */
    public function createProjectAddition(Request $request)
    {
        $tableProject = Project::getTableName();
        $rules = [
            'name' => "required|max:255|unique:{$tableProject},name",
            'type' => "required",
            'kindId' => "required",
            'detail' => "required|array",
            'detail.*.costApprovedProduction' => 'required',
            'detail.*.month' => 'required',
            'detail.*.teamId' => 'required',
            'detail.*.price' => 'required',
            'detail.*.unitPriceSelected' => 'required',
            'detail.*.projectChild' => 'array',
            'detail.*.projectChild.*.costApprovedProduction' => 'required',
            'detail.*.projectChild.*.teamId' => 'required|numeric',
            'detail.*.projectChild.*.price' => 'required'
        ];

        $messages = [
            'name.required' => trans('project::message.The name field is required'),
            'name.max' => trans('project::message.Please enter name a value between 1 and 255 characters long'),
            'name.unique' => trans('project::message.The value of name field must be unique'),
            'detail.*.teamId' => trans('project::message.The group field is required'),
            'detail.*.projectChild.*.teamId' => trans('project::message.The group field is required'),
            'detail.*.costApprovedProduction' => trans('project::message.The cost_approved_production field is required'),
            'detail.*.projectChild.*.costApprovedProduction' => trans('project::message.The cost_approved_production field is required')
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'data' => $validator->errors(),
            ]);
        }

        try {
            return [
                'success' => 1,
                'data' => OperationHelper::createProject($request, true),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => OperationHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    public function getProjectFuture(Request $request) {
        try {
            return [
                'success' => 1,
                'data' => OperationHelper::getPorjectFuture($request),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => OperationHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    public function projectCostUpdate(Request $request) {
        try {
            OperationHelper::updateProjectCost($request);
            return [
                'success' => 1,
                'data' => 'success',
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => OperationHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    public function getProjectKind() {
        try {
            return [
                'success' => 1,
                'data' => ProjectKind::orderBy('is_other_type')->orderBy('id')->pluck('kind_name','id')
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => OperationHelper::getInstance()->errorMessage($ex),
            ];
        }
    }
    
    public function getOperationReportsTeam(Request $request)
    {
        $rules = [
            'month_start' => 'required|date_format:Y-m',
            'month_end' => 'required|date_format:Y-m',
            'team_id' => 'numeric'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'data' => $validator->errors(),
            ]);
        }

        $monthStart = $request->month_start;
        $monthEnd = $request->month_end;
        $teamId = $request->team_id ? $request->team_id : '';

        try {
            return [
                'success' => 1,
                'data' => OperationHelper::getInstance()->getOperationReportsTeam($monthStart, $monthEnd, $teamId)
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => OperationHelper::getInstance()->errorMessage($ex),
            ];
        }
        return ;
    }
}
