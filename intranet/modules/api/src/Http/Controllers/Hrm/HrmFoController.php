<?php

namespace Rikkei\Api\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use Log;
use Rikkei\Api\Helper\HrmFo as HrmFoHelper;
use Rikkei\Core\Http\Controllers\Controller;
use Validator;

class HrmFoController extends Controller
{
    /**
     * API - Get all branches
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getTeamFo(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'branch_code' => 'required|exists:teams,branch_code',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmFoHelper::getInstance()->getTeamFo($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmFoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get FO Overall
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getFoOverall(Request $request)
    {
        try {
            ini_set('max_execution_time', '300');
            $valid = validator()->make($request->all(), [
                'year' => 'required|date_format:Y',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $finalFoOverAllResponse = HrmFoHelper::getInstance()->getFoOverall($request);

            return [
                'success' => 1,
                'data' =>  $finalFoOverAllResponse
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmFoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get FO Allocated
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getFoAllocation(Request $request)
    {
        try {
            ini_set('max_execution_time', '300');
            $valid = validator()->make($request->all(), [
                'year' => 'required|date_format:Y',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            $response = HrmFoHelper::getInstance()->getFoAllocationOptimized($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmFoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }


    /**
     * API get Effort for each kind of project
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getFoEffortProject(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'year' => 'required|date_format:Y',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            $response = HrmFoHelper::getInstance()->getFoEffortProject($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmFoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get percentage effort for each role
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getFoEffortRole(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'month_from' => 'required|date_format:Y-m',
                'month_to' => 'date_format:Y-m',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            $response = HrmFoHelper::getInstance()->getFoEffortRole($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmFoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get percentage effort for each role
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getFoEffortRoleForDay(Request $request)
    {
        try {

            $rules = [
                'date' => 'required|date_format:d/m/Y',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id'
            ];

            $valid = validator()->make($request->all(), $rules);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            $response = HrmFoHelper::getInstance()->getFoEffortRoleForDay($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmFoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get percentage effort for employee
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getFoEffortEmployee(Request $request)
    {
        try {

            $rules = [
                'date' => 'required|date_format:d/m/Y',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id'
            ];

            $valid = validator()->make($request->all(), $rules);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            $response = HrmFoHelper::getInstance()->getFoEffortRoleEmployee($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmFoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }
}