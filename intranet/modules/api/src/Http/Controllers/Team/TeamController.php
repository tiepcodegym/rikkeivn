<?php
namespace Rikkei\Api\Http\Controllers\Team;

use Rikkei\Api\Helper\Revenue;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Api\Helper\Team as TeamHelper;
use Rikkei\Api\Helper\Role as RoleHelper;
use Illuminate\Http\Request;
use Log;
use Rikkei\Api\Helper\Project as ProjectHelper;
use Validator;

class TeamController extends Controller
{
    /*
     * get teams list
     */
    public function getList(Request $request)
    {
        if (isset($request->branch_code)) {
            $condition = [
                'field' => 'branch_code',
                'compare' => '=',
                'value' => $request->branch_code,
            ];
            $where = (array) $request->where;
            $where[] = $condition;
            $request->merge(['where' => $where]);
        }
        try {
            $response = TeamHelper::getInstance()->getList($request->all());
            return [
                'success' => 1,
                'teams' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => TeamHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function getOwnerList(Request $request)
    {
        $rules = [
            'email' => 'required|email|exists:employees,email',
            'team_id' => 'exists:teams,id'
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
                'data' => Revenue::getInstance()->getOwnerList($request),
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
     * get roles list
     */
    public function getRolesList(Request $request)
    {
        try {
            $response = RoleHelper::getInstance()->getList($request->all());
            return [
                'success' => 1,
                'roles' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => TeamHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /*
     * get total employees (current and onstie + leave day)
     */
    public function getTotalEmployee(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'month' => 'required|date_format:Y-m',
                'team_id' => 'exists:teams,id'
            ]);
            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = TeamHelper::getInstance()->getTotalEmployee($request->all());
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => TeamHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /*
     * get Holidays
     */
    public function getHolidays(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'branch_codes' => 'required|array',
                'branch_codes.*' => 'string',
            ]);
            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = TeamHelper::getInstance()->getHolidays($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => TeamHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /*
     * get point employees
     */
    public function getPointEmployees(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'month_from' => 'required|date_format:Y-m',
                'month_to' => 'required|date_format:Y-m',
                'team_id' => 'required|array'
            ]);
            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = TeamHelper::getInstance()->getPointEmployees($request->all());
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => TeamHelper::getInstance()->errorMessage($ex)
            ];
        }
    }
}
