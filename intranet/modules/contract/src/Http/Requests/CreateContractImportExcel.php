<?php

namespace Rikkei\Contract\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Team\Model\Employee;
use Carbon\Carbon;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\View\getOptions;

/**
 * Description of CreateContractRequest
 *
 * @author HuongPV - Pro
 */
class CreateContractImportExcel
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

    public function rules($params)
    {
        $allTypeContract = ContractModel::getAllTypeContract();
        $allTypeContract = array_keys($allTypeContract);
        $employeeTable = Employee::getTableName();
        $rules = [
            'sel_employee_id' => "required|exists:{$employeeTable},id",
            'sel_contract_type' => ['required', 'in:' . implode(',', $allTypeContract)],
            'txt_start_at' => [
                'required',
                'date_format:d-m-Y',
            ]
        ];



        $typeContract = $params['sel_contract_type'];
        if ($typeContract != getOptions::WORKING_UNLIMIT) {
            //Loại Có xác định thời hạn
            $rules['txt_end_at'] = ['required', 'date_format:d-m-Y'];
            $rules['txt_start_at'] = ['before:txt_end_at'];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'sel_employee_id.exists' => trans('contract::message.The selected employee id is invalid.'),
            'sel_employee_id.required' => trans('contract::message.The selected employee id is invalid.'),
            'sel_contract_type.required' => trans('contract::message.The selected contract type is invalid.'),
            'sel_contract_type.in' => trans('contract::message.The selected contract type is invalid.'),
            'txt_start_at.required' => trans('contract::message.Start time cannot be left blank.'),
            'txt_end_at.required' => trans('contract::message.End time cannot be left blank.'),
            'txt_start_at.before' => trans('contract::message.The txt start at must be a date before txt end at.'),
        ];
    }

    public function extendValidator(&$validator, $params)
    {
        $validator->after(function ($validator) use ($params) {
            $empId = $params['sel_employee_id'];
            $startAt = Carbon::parse($params['txt_start_at']);
            if ($startAt && $empId) {
                $startAt = Carbon::parse($startAt);
                $messError = $this->validateSatrtAt($empId, $startAt);
                if ($messError != '') {
                    $validator->errors()->add('txt_start_at', $messError);
                }
            }
            $endAt = $params['txt_end_at'] ? $params['txt_end_at'] : null;
            if ($empId && $endAt) {
                $endAt = Carbon::parse($endAt);
                $messError = $this->validateEndAt($empId, $endAt);
                if ($messError != '') {
                    $validator->errors()->add('txt_end_at', $messError);
                }
                $messError = $this->validateNotWrapperContract($startAt, $endAt,$empId);
                if ($messError != '') {
                    $validator->errors()->add('txt_end_at', $messError);
                }
            }

            $contractType = $params['sel_contract_type'];
            if ($contractType && $startAt && $endAt == null) {
                $startAt = Carbon::parse($startAt);
                $messError = $this->validateContractType($contractType, $startAt, $empId);
                if ($messError != '') {
                    $validator->errors()->add('sel_contract_type', $messError);
                }
            }
        });
        return $validator;
    }

    public function validateSatrtAt($empId, $startAt)
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

    public function validateEndAt($empId, $endAt)
    {
        $isBusy = ContractModel::checkTimeIsBusy($empId, $endAt, 0);
        if ($isBusy) {
            return trans('contract::message.Thoi_gian_ket_thuc_bi_trung');
        }
        return '';
    }

    public function validateContractType($contractType, $startAt, $empId)
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
    public function validateNotWrapperContract($startAt, $endAt,$empId)
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
