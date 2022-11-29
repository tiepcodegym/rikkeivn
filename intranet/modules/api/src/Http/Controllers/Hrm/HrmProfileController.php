<?php

namespace Rikkei\Api\Http\Controllers\Hrm;

use Illuminate\Http\Request;
use Log;
use Rikkei\Api\Helper\HrmProfile;
use Rikkei\Core\Http\Controllers\Controller;
use Validator;

/**
 * Class HrmProfileController
 * @package Rikkei\Api\Http\Controllers\Hrm
 */
class HrmProfileController extends Controller
{
    /**
     * @return array
     */
    public function getEmployeeIds(Request $request)
    {
        $params = $request->all();
        $rules = [
            'updated_from' => 'date',
            'updated_to' => 'date',
        ];
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
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
        return [
            'success' => 1,
            'data' => HrmProfile::getInstance()->getEmployeeIds($request)
        ];
    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getProfileEmployees(Request $request, $type)
    {
        try {
            ini_set('memory_limit', '-1');
            ini_set('max_execution_time', '300');
            $valid = validator()->make($request->all(), [
                'employee_ids' => 'required|array',
            ]);

            if ($valid->fails()) {
                return response()->json([
                    'success' => 0,
                    'message' => $valid->messages(),
                    'data' => []
                ]);
            }


            return [
                'success' => 1,
                'message' => 'success',
                'data' => HrmProfile::getInstance()->getProfileEmployees($request, $type)
            ];
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error($ex->getMessage());

            return [
                'success' => 0,
                'data' => [],
                'message' => HrmProfile::getInstance()->errorMessage($ex)
            ];
        }

    }

    /**
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function setProfileInitial(Request $request, $type)
    {
        try {
            ini_set('max_execution_time', '300');

            return [
                'success' => 1,
                'message' => 'success',
                'data' => HrmProfile::getInstance()->setInitial($type)
            ];
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error($ex->getMessage());

            return [
                'success' => 0,
                'data' => [],
                'message' => HrmProfile::getInstance()->errorMessage($ex)
            ];
        }

    }
}