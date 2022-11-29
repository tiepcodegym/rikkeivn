<?php

namespace Rikkei\Api\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use Log;
use Rikkei\Api\Helper\HrmTotal as HrmTotalHelper;
use Rikkei\Core\Http\Controllers\Controller;
use Validator;

class HrmTotalController extends Controller
{
    /**
     * API get Total Employees Each Branch
     *
     * @return array json
     */
    public function getTotalEmployeesEachBranch()
    {
        try {
            $response = HrmTotalHelper::getInstance()->getTotalEmployeesEachBranch();
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Employees Each Branch
     *
     * @return array json
     */
    public function getAllTeams(Request $request)
    {
        try {
            $response = HrmTotalHelper::getInstance()->getAllTeams($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Employees + Total Employees FO + Total Employees BO
     *
     * @return array json
     */
    public function getTotalEmployees()
    {
        try {
            $totalEmpResponse = HrmTotalHelper::getInstance()->getTotalEmployees();
            $totalEmpFOAndBO = HrmTotalHelper::getInstance()->getTotalEmpFOAndBO();
            return [
                'success' => 1,
                'data' => [
                    'total_employees' => $totalEmpResponse,
                    'total_employees_fo_bo' => $totalEmpFOAndBO
                  ]
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Employees Division
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getTotalEmployeesDivision(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'branch_code' => 'required|exists:teams,branch_code'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getTotalEmployeesDivision($request);

            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total type of contract
     *
     * @return array json
     */
    public function getContractType(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getContractType($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total age and gender
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getAgeGenders(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'date' => 'required',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getAgeGenders($request);

            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total seniority
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getSeniorities(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'date' => 'required',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getSeniorities($request);

            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total educations
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getEducations(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getEducations($request);

            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Certificates
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getCertificates(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'date' => 'required|date_format:Y-m-d',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getCertificates($request);

            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Division Popup
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getTotalDivisionPopup(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'date' => 'required|date_format:Y',
                'team_id' => 'exists:teams,id'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getTotalDivisionPopup($request);

            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get human resource and onsiter of branch
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getHRByBranch(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'year' => 'required',
                'branch_code' => 'exists:teams,branch_code'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getHRByBranch($request);

            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Employees Each Branch
     *
     * @param Request $request
     * @return array json
     */
    public function getTotalRequest(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'updated_from' => 'date',
                'updated_to' => 'date'
            ]);
            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            $updated_from = $request->updated_from;
            $updated_to = $request->updated_to;
            if ($updated_from && $updated_to && ($updated_to < $updated_from)) {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) {
                    $vld->errors()->add('updated_to', 'The updated to must be a date after or equal updated from.');
                });
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()
                ]);
            }
            
            $response = HrmTotalHelper::getInstance()->getTotalRequest($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Candidates
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getTotalCandidates(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'updated_from' => 'date',
                'updated_to' => 'date'
            ]);
            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }
            $updated_from = $request->updated_from;
            $updated_to = $request->updated_to;
            if ($updated_from && $updated_to && ($updated_to < $updated_from)) {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) {
                    $vld->errors()->add('updated_to', 'The updated to must be a date after or equal updated from.');
                });
                return response()->json([
                    'success' => 0,
                    'message' => $validator->messages()
                ]);
            }

            $response = HrmTotalHelper::getInstance()->getTotalCandidates($request);

            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmTotalHelper::getInstance()->errorMessage($ex)
            ];
        }
    }
}