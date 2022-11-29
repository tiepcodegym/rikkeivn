<?php

namespace Rikkei\Api\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use Log;
use Rikkei\Api\Helper\HrmCandidate;
use Rikkei\Api\Helper\HrmRecruitmentChart;
use Rikkei\Core\Http\Controllers\Controller;
use Validator;
use Rikkei\Resource\View\getOptions;

class HrmRecruitmentController extends Controller
{
    /**
     * API get Total Employees Each Branch
     *
     * @return array json
     */
    public function getReport(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'date_from' => 'required|date|date_format:Y-m-d',
                'date_to' => 'required|date|date_format:Y-m-d',
                'branch_code' => 'exists:teams,branch_code',
                'team_id' => 'exists:teams,id',
                'campaign_id' => 'exists:requests,id'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmRecruitmentChart::getInstance()->getReport($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmRecruitmentChart::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Employees Each Branch
     *
     * @return array json
     */
    public function getCampaign(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'date_from' => 'required|date|date_format:Y-m-d',
                'date_to' => 'required|date|date_format:Y-m-d',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $response = HrmRecruitmentChart::getInstance()->getCampaign($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmRecruitmentChart::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Total Employees Each Branch
     *
     * @return array json
     */
    public function getTarget(Request $request)
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

            $response = HrmRecruitmentChart::getInstance()->getTarget($request);
            return [
                'success' => 1,
                'data' => $response
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmRecruitmentChart::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Candidates
     *
     * @return array json
     */
    public function getCandidates(Request $request)
    {
        try {
            $valid = validator()->make($request->all(), [
                'candidate_name' => 'string',
                'branch_code' => 'string',
                'team_id' => 'exists:teams,id',
                'position_apply' => 'integer',
                'date_from' => 'date_format:Y-m-d',
                'date_to' => 'date_format:Y-m-d',
                'status' => 'integer',
                'campaign_id' => 'exists:requests,id'
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages()
                ]);
            }

            $hrmCandidateInstance = HrmCandidate::getInstance();
            $response = $hrmCandidateInstance->getCandidates($request);

            return [
                'success' => 1,
                'data' => $response,
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmRecruitmentChart::getInstance()->errorMessage($ex)
            ];
        }
    }

    /**
     * API get Candidates
     *
     * @return array json
     */
    public function getPositionApplyAndCandidateStatus(Request $request)
    {
        try {
            $hrmCandidateInstance = HrmCandidate::getInstance();

            return [
                'success' => 1,
                'data' => [
                    'position_apply' => getOptions::getInstance()->getRoles(),
                    'status' => $hrmCandidateInstance->getStatus()
                ]
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmRecruitmentChart::getInstance()->errorMessage($ex)
            ];
        }
    }


    /**
     * API get Candidates Requests
     *
     * @return array json
     */
    public function getCandidateRequests(Request $request)
    {
        try {
            $hrmCandidateInstance = HrmCandidate::getInstance();
            $response = $hrmCandidateInstance->getCandidateRequests($request);

            return [
                'success' => 1,
                'data' => $response,
            ];
        } catch (\Exception $ex) {
            Log::info($ex);
            return [
                'success' => 0,
                'message' => HrmRecruitmentChart::getInstance()->errorMessage($ex)
            ];
        }
    }
}