<?php

namespace Rikkei\Project\Http\Requests;

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
class CreateOsdcImportExcel
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
        $employeeTable = Employee::getTableName();
        $rules = [
            'sel_employee_id' => "required|exists:{$employeeTable},id",
            'sel_employee_email' => "required",
            'sel_employee_me' => "required|numeric|min:1|not_in:0",

        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'sel_employee_id.exists' => "Nhân viên đã có trong hệ thống.",
            'sel_employee_id.required' => "Nhân viên chưa được khai báo.",
            'sel_employee_email.required' => "Địa chỉ email nhân viên không hợp lệ.",
            'sel_employee_me.required' => "ME phải không được để chống",
            'sel_employee_me.numeric' => "ME phải là chữ số",
            'sel_employee_me.min' => 'ME phải lớn hơn 0',
            'sel_employee_me.not_in' => 'ME phải lớn hơn 0',


        ];
    }

}
