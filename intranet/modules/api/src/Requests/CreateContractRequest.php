<?php

namespace Rikkei\Api\Requests;

use Rikkei\Contract\Model\ContractModel;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Validator;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

/**
 * Description of CreateContractRequest
 *
 * @author HuongPV - Pro
 */
class CreateContractRequest
{
    public static function validate($params, $empId)
    {
        $allTypeContract = ContractModel::getAllTypeContract();
        $allTypeContract = array_keys($allTypeContract);
        $employeeTable = Employee::getTableName();
        $rules = [
            'employee_email' => "required|email|exists:{$employeeTable},email",
            'type' => 'required|in:'.implode(',', $allTypeContract),
            'from_date' => 'required|date_format:Y-m-d',
            'creator' => "required|email|exists:{$employeeTable},email",
            'hrm_contract_id' => "required|string",
        ];
        $typeContract = $params['type'];
        $fromDate = $params['from_date'];
        $toDate = $params['to_date'] ? $params['to_date'] : null;
        if ($typeContract != getOptions::WORKING_UNLIMIT) {
            //Loại Có xác định thời hạn
            $rules['to_date'] = 'required|date_format:Y-m-d|after:from_date';
        }
        $validator = Validator::make($params, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'message' => trans('api::message.Error input data!'),
                'errors' => $validator->errors(),
            ]);
        }

        $startAt = Carbon::parse($fromDate);
        if ($startAt && $empId) {
            $messError = self::validateStartAt($empId, $startAt);
            if ($messError != '') {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) use ($messError) {
                    $vld->errors()->add('from_date', $messError);
                });
                return response()->json([ 'success' => 0, 'message' => $validator->messages() ]);
            }
        }
        if ($empId && $toDate) {
            $endAt = Carbon::parse($toDate);
            $messError = self::validateEndAt($empId, $endAt);
            if ($messError != '') {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) use ($messError) {
                    $vld->errors()->add('to_date', $messError);
                });
                return response()->json([ 'success' => 0, 'message' => $validator->messages() ]);
            }
            $messError = self::validateNotWrapperContract($startAt, $endAt, $empId);
            if ($messError != '') {                
                $validator = validator()->make([], []);
                $validator->after(function ($vld) use ($messError) {
                    $vld->errors()->add('to_date', $messError);
                });
                return response()->json([ 'success' => 0, 'message' => $validator->messages() ]);
            }
        }
        if ($typeContract && $startAt && empty($toDate)) {
            $messError = self::validateContractType($typeContract, $startAt, $empId);
            if ($messError != '') {
                $validator = validator()->make([], []);
                $validator->after(function ($vld) use ($messError) {
                    $vld->errors()->add('type', $messError);
                });
                return response()->json([ 'success' => 0, 'message' => $validator->messages() ]);
            }
        }

        return null;
    }

    public static function validateStartAt($empId, $startAt)
    {
        $joinDate = ContractModel::getJonInDate($empId);
        if (!$joinDate) {
            return trans('contract::message.Employee not config join date company');
        }
        if (strtotime($startAt) < strtotime($joinDate)) {
            return trans('contract::message.The start time must not be less than the time of joining the company');
        }
        $contractLast = ContractModel::getLastContract($empId);
        if ($contractLast && trim($contractLast->end_at) == '') {
            if (strtotime($contractLast->start_at) <= strtotime($startAt)) {
                return trans('contract::message.A contract has not been updated yet');
            }
        }
        $isBusy = ContractModel::checkTimeIsBusy($empId, $startAt);
        if ($isBusy) {
            return trans('contract::message.The contract creation time is invalid');
        }
        return '';
    }

    public static function validateEndAt($empId, $endAt)
    {
        $isBusy = ContractModel::checkTimeIsBusy($empId, $endAt, null);
        if ($isBusy) {
            return trans('contract::message.Thoi_gian_ket_thuc_bi_trung');
        }
        return '';
    }

    public static function validateContractType($contractType, $startAt, $empId)
    {
        //Khong cho phep tao hop dong khong thoi han ve qua khứ
        if (intval($contractType) === getOptions::WORKING_UNLIMIT) {
            $count = ContractModel::where('start_at', '>=', $startAt)->where('employee_id', $empId)->count();
            if ($count > 0) {
                return trans('contract::message.Chi_duoc_phep_tao_hop_dong_khong_thoi_han_khi_la_hop_dong_tao_lan_cuoi');
            }
        }
        return '';
    }

    /**
     * Kiểm tra nếu tồn tại hợp đồng nằm trong khoảng đang xét => failed
     * @return string '' is pass or message detail error
     */
    public static function validateNotWrapperContract($startAt, $endAt, $empId)
    {
        $r = ContractModel::where([
            ['start_at', '>=', $startAt],
            ['end_at', '<=', $endAt],
        ])
        ->where('employee_id', $empId)
        ->count();
        if ($r > 0) {
            return trans('contract::message.Timestime conflict');
        }
        return '';
    }
}