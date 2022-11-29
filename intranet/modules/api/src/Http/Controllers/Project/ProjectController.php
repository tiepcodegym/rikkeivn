<?php

namespace Rikkei\Api\Http\Controllers\Project;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Lang;
use Rikkei\Api\Helper\Project as ProjectHelper;
use Rikkei\Api\Helper\Revenue;
use Rikkei\Api\Helper\Timekeeping;
use Rikkei\Core\Http\Controllers\Controller;

class ProjectController extends Controller
{
    /*
     * get project information in Work order and Report
     */
    public function getInfo(Request $request)
    {
        try {
            $info = ProjectHelper::getInstance()->getInfo($request->project_id);
            return [
                'success' => 1,
                'data' => $info,
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => ProjectHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    /*
     * get projects list
     * @params division, state, pm, name, type
     */
    public function getList(Request $request)
    {
        $rules = [
            'name' => 'string',
            'pm' => 'string',
            'team_id' => 'array',
            'state' => 'array',
            'type' => 'array',
            'id' => 'array', //project_id
            'crm_account_id' => 'array',
            'month_from' => 'date_format:Y-m',
            'month_to' => 'date_format:Y-m'
        ];
        $messages = [
            'name.string' => Lang::get('api::message.Name must be a string!'),
            'pm.string' => Lang::get('api::message.Pm must be a string!'),
            'team_id.array' => Lang::get('api::message.Team_id must be an array!'),
            'state.array' => Lang::get('api::message.State must be an array!'),
            'type.array' => Lang::get('api::message.Type must be an array!'),
            'id.array' => Lang::get('api::message.Project id must be an array!'),
            'crm_account_id.array' => Lang::get('api::message.CRM account id must be an array!'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            return [
                'success' => 1,
                'data' => ProjectHelper::getInstance()->getList($request->all()),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => ProjectHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    public function getMember(Request $request)
    {
        try {
            if ( !isset($request['time']) || empty($request['time']) ) {
                return [
                    'success' => 0,
                    'message' => "Input 'time' is require",
                ];
            }
            if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])$/",$request['time'])) {
                return [
                    'success' => 0,
                    'message' => "Input 'time' must be 'Y-m' ",
                ];
            } 
            $time = $request['time'];
            return [
                'success' => 1,
                'data' => ProjectHelper::getInstance()->getMember($time),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => ProjectHelper::getInstance()->errorMessage($ex),
            ];
        }
    }
    /**
     * Get list project in month
     */
    public function getListInMonth(Request $request)
    {
        try {
            $time = $request['time'];
            return [
                'success' => 1,
                'data' => ProjectHelper::getInstance()->getListInMonth($time),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => ProjectHelper::getInstance()->errorMessage($ex),
            ];
        }
    }
    /*
* get projects list
* @params division, state, pm, name, type
*/
    public function getRevenueList(Request $request)
    {
        ini_set('max_execution_time', 600);
        $rules = [
            'email' => 'required|email|exists:employees,email',
            'team_id' => 'array',
            'team_id.*' => 'exists:teams,id',
            'project_id' => 'array',
            'project_id.*' => 'exists:projs,id',
            'month_from' => 'required|date_format:Y-m',
            'month_to' => 'required|date_format:Y-m'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            return [
                'success' => 1,
                'data' => Revenue::getInstance()->getRevenueList($request->all()),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => ProjectHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getBillableEffortByProjectIds(Request $request)
    {
        $rules = [
            'project_ids' => 'array',
            'date_start' => 'date_format:Y-m-d',
            'date_end' => 'date_format:Y-m-d'
        ];
        $messages = [
            'project_ids.array' => Lang::get('api::message.Project_ids must be an array!'),
            'date_start.date_format:Y-m-d' => Lang::get('api::message.Start Date is incorrect'),
            'date_end.date_format:Y-m-d' => Lang::get('api::message.End Date is incorrect'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            return [
                'success' => 1,
                'data' => ProjectHelper::getInstance()->getBillableEffortByProjectIds($request->all()),
            ];
        } catch (\Exception $ex) {
            \Log::info($ex);
            return [
                'success' => 0,
                'message' => ProjectHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    /**
     * get timekeeping by projects
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getProjTimekeeping(Request $request)
    {
        $rules = [
            'proj_ids' => 'required|array',
            'proj_ids.*' => 'integer',
            'date_start' => 'required|date_format:Y-m-d',
            'date_end' => 'required|date_format:Y-m-d',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $proIds = $request->proj_ids;
            $dateStart = $request->date_start;
            $dateEnd = $request->date_end;
            $collection = Timekeeping::getInstance()->getTkWithProject($proIds, $dateStart, $dateEnd);
            $empIds = array_values(array_unique($collection->lists('employee_id')->toArray()));
            $registerOt =  Timekeeping::getInstance()->getRegisterOTByProjectKeyProj($empIds, $proIds, $dateStart, $dateEnd);
            $registerLeaveDays = Timekeeping::getInstance()->getRegisterLeaveDayKeyEmp($empIds, $dateStart, $dateEnd);
            $registerBusiness = Timekeeping::getInstance()->getRegisterBusinessKeyEmp($empIds, $dateStart, $dateEnd);
            return [
               'success' => 1,
               'data' => Timekeeping::getInstance()->getJsonTkProj($collection, $registerOt, $registerBusiness, $registerLeaveDays),
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => ProjectHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    /**
     * Báo cáo chấm công theo project
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function reportProjTimekeeping(Request $request)
    {
        $rules = [
            'proj_ids' => 'required|array',
            'proj_ids.*' => 'integer',
            'date_start' => 'required|date_format:Y-m-d',
            'date_end' => 'required|date_format:Y-m-d',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }
        try {
            $proIds = $request->proj_ids;
            $dateStart = $request->date_start;
            $dateEnd = $request->date_end;
            $collection = Timekeeping::getInstance()->getTkWithProject($proIds, $dateStart, $dateEnd);
            $empIds = array_values(array_unique($collection->lists('employee_id')->toArray()));
            $registerOt =  Timekeeping::getInstance()->getRegisterOTByProjectKeyProj($empIds, $proIds, $dateStart, $dateEnd);
            return [
               'success' => 1,
               'data' => Timekeeping::getInstance()->getJsonReportTkProj($collection, $registerOt),
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => ProjectHelper::getInstance()->errorMessage($ex),
            ];
        }
    }

    /**
     * getProjectCssResultByProjIds
     *
     * @param  Request $request
     * @return json
     */
    public function getProjectCssResultByProjIds(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'proj_ids' => 'required|array|min:1',
            'proj_ids.*' => 'required|min:1',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'success' => 0,
                'messages' => $errors
            ]);
        }
        try {
            $dataResult = [];
            $projCssResult = ProjectHelper::getInstance()->getProjectCssResultByProjIds($data['proj_ids']);
            if (count($projCssResult)) {
                foreach($projCssResult as $item) {
                    $dataResult[$item->id][] = [
                        'css_id' => $item->css_id,
                        'customer_name' => $item->css_result_name,
                        'point' => $item->css_result_point,
                        'created_at' => $item->css_result_created_at,
                    ];
                }
            }
            return [
                'success' => 1,
                'data' => $dataResult,
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'messages' => [$ex->getMessage()]
            ];
        }
    }
}
