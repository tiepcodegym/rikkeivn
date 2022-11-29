<?php

namespace Rikkei\Api\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use Log;
use Rikkei\Api\Helper\HrmBo as HrmBoHelper;
use Rikkei\Core\Http\Controllers\Controller;
use Validator;

class HrmBoController extends Controller
{
    /**
     * API - Get all branches
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getTeamBo(Request $request)
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

            $response = HrmBoHelper::getInstance()->getTeamBo($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmBoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API - HR statistics for each branch in BO page
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getBoEachBranch(Request $request)
    {
        try {
            $response = HrmBoHelper::getInstance()->getBoEachBranch($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmBoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API - HR statistics of each division by branch in BO page
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getBoDivisionEachBranch(Request $request)
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

            $response = HrmBoHelper::getInstance()->getBoDivisionEachBranch($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmBoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function getStatisticalEmployeeInOut(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
                'year' => 'required|date_format:Y'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmBoHelper::getInstance()->getStatisticalEmployeeInOut($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmBoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API - list of employees quitting in a month
     *
     * @param Request $request is param
     * @return array employees
     */
    public function getListLeaveCompanyInMonth(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'month' => 'required|date|date_format:Y-m',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmBoHelper::getInstance()->getListLeaveCompanyInMonth($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmBoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    public function getListBirthdayInMonth(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'month' => 'required|date_format:Y-m',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmBoHelper::getInstance()->getListBirthdayInMonth($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmBoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get list employees expiration of the contract in month
     *
     * @param Request $request for attributes
     * @return array json
     */
    public function getExpiredContractInMonth(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'month' => 'required|date_format:Y-m',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            $response = HrmBoHelper::getInstance()->getExpiredContractInMonth($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmBoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API - list of new employees join in month
     *
     * @param Request $request is param
     * @return array employees
     */
    public function getNewEmployeesInMonth(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'month' => 'required|date|date_format:Y-m',
                'status' => 'required|array',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmBoHelper::getInstance()->getNewEmployeesInMonth($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmBoHelper::getInstance()->errorMessage($ex)
            ];
        }
    }
}