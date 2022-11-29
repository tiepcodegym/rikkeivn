<?php

namespace Rikkei\Contract\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Team\Model\Team;
use Carbon\Carbon;
use Rikkei\Resource\View\getOptions;

/**
 * Description of CreateContractRequest
 *
 * @author HuongPV - Pro
 */
class EditContractRequest extends FormRequest
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

    public function rules()
    {
        $allTypeContract = ContractModel::getAllTypeContract();
        $allTypeContract = array_keys($allTypeContract);
        $employeeTable = Employee::getTableName();
        $contractTable = ContractModel::getTableName();
        $rules = [
            'id' => "required|exists:{$contractTable},id",
            'sel_employee_id' => "required|exists:{$employeeTable},id",
            'sel_contract_type' => ['required', 'in:' . implode(',', $allTypeContract)],
            'txt_start_at' => [
                'required',
                'date_format:d-m-Y',
            ]
        ];
        $typeContract = Input::get('sel_contract_type');
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

    public function validator($factory)
    {
        $validator = $factory->make(
                $this->all(), $this->container->call([$this, 'rules']), $this->messages(), $this->attributes()
        );
        $validator->after(function($validator) {
            $id = Input::get('id');
            $contractInfo = ContractModel::getContractById($id);
            if ($contractInfo && !$contractInfo->isContractLast()) {
                $validator->errors()->add('id', trans('contract::message.Old records are not allowed to edit'));
            } else {
                $empId = Input::get('sel_employee_id');
                $startAt = Input::get('txt_start_at') ? Input::get('txt_start_at') : null;
                if ($startAt && $empId) {
                    $startAt = Carbon::parse($startAt);
                    $messError = $this->validateSatrtAt($empId, $startAt);
                    if ($messError != '') {
                        $validator->errors()->add('txt_start_at', $messError);
                    }
                }

                $endAt = Input::get('txt_end_at') ? Input::get('txt_end_at') : null;
                if ($empId && $endAt) {
                    $endAt = Carbon::parse($endAt);
                    $messError = $this->validateEndAt($empId, $endAt);
                    if ($messError != '') {
                        $validator->errors()->add('txt_end_at', $messError);
                    }
                }

                $contractType = Input::get('sel_contract_type');
                if ($contractType && $startAt && $endAt == null) {
                    $startAt = Carbon::parse($startAt);
                    $messError = $this->validateContractType($contractType, $startAt);
                    if ($messError != '') {
                        $validator->errors()->add('sel_contract_type', $messError);
                    }
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
        $id = Input::get('id');
        $isBusy = ContractModel::checkTimeIsBusy($empId, $startAt, $id);
        if ($isBusy) {
            return trans('contract::message.The contract creation time is invalid');
        }
        return '';
    }

    public function validateEndAt($empId, $endAt)
    {
        $id = Input::get('id');
        $isBusy = ContractModel::checkTimeIsBusy($empId, $endAt, $id);
        if ($isBusy) {
            return trans('contract::message.Thoi_gian_ket_thuc_bi_trung');
        }
        return '';
    }

    function validateContractType($contractType, $startAt)
    {
        $empId = Input::get('sel_employee_id');
        //Khong cho phep tao hop dong khong thoi han ve qua khứ
        if (intval($contractType) === getOptions::WORKING_UNLIMIT) {
            $id = Input::get('id');
            $count = ContractModel::where('start_at', '>=', $startAt)->where('employee_id',$empId)->where('id', '<>', $id)->count();
            if ($count > 0) {
                return trans('contract::message.Chi_duoc_phep_tao_hop_dong_khong_thoi_han_khi_la_hop_dong_tao_lan_cuoi');
            }
        }
        return '';
    }

}
