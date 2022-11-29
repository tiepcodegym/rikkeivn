<?php

namespace Rikkei\Ot\Http\Controllers;

use Auth;
use DB;
use Lang;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\View;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Http\Controllers\AuthController;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Ot\Model\OtEmployee;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Input;

class OtEmployeeController extends Controller 
{
    use SoftDeletes;
    
    protected $table = 'ot_employees';
    protected $dates = ['deleted_at'];
    
    /**
     * add bread crumb
     */
    protected function _construct() 
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('OT');
        Menu::setActive('Profile');
    }

    /**
     * save overtime register
     * @param int $regId register id
     * @param array $empArr employees ids
     */
    public static function saveEmployees($regId, $empArr) 
    {
        foreach($empArr as $emp) {
            $otEmp = new OtEmployee();
            
            $otEmp->ot_register_id = $regId;
            $otEmp->employee_id = $emp->empId;
            $otEmp->start_at = Carbon::createFromFormat('d-m-Y H:i', $emp->startAt)->format('Y-m-d H:i:s');
            $otEmp->end_at = Carbon::createFromFormat('d-m-Y H:i', $emp->endAt)->format('Y-m-d H:i:s');
            $otEmp->is_paid = $emp->isPaid;
            $otEmp->time_break = $emp->break;
            $otEmp->save();
        }
    }
  
    /**
     * get employee info ajax
     * @param Request $request
     * @return json employee's info
     */
    public function getEmployeeForSearch(Request $request) 
    {
        $data = array();
        $search = $request->queryStr;
        $searchremove = $request->registerId;
        $tmp = OtEmployee::getDataSearch($search, $searchremove);
        foreach ($tmp as $key) {
            $data[] = ['id' => $key->id, 'text' => $key->name, 'code' => $key->employee_code];
        }

        return \Response::json($data);
    }

    /**
     * Search employee by ajax
     */
    public function ajaxSearchEmployee()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            OtEmployee::searchEmployeeAjax(trim(Input::get('q')), [
                'page' => Input::get('page'),
            ])
        );
    }

    /*
     * Search employee can approve
     */
    public function searchEmployeeCanApproveAjax()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        return response()->json(
            OtRegister::searchEmployeesCanApprove(Input::get('q'), $userCurrent->id)
        );
    }

    /**
     * get members' list of a project
     * @param Request $request
     * @return string $html
     */
    public function getProjectMember(Request $request) 
    {
        $idProject = $request->idProject;
        $projsMembers = OtEmployee::getProjectMember($idProject);  
        $memberArr = [];
        if (count($projsMembers)) {
            foreach ($projsMembers as $member) {
                $memberArr[$member->employee_id] = ["code" => $member->employee_code, "name" => $member->name, "email" => $member->email];
            }
        }
        return $memberArr;
    }
    
    /**
     * get members of requested team
     * @param Request $request
     * @return array $empArr
     */
    public function getTeamEmployee(Request $request) 
    {
        $idTeam = $request->idTeam;
        $teamEmps= OtEmployee::getTeamEmployee($idTeam);  
        $empArr = [];
        if (count($teamEmps)) {
            foreach ($teamEmps as $emp) {
                $empArr[$emp->employee_id] = ["code" => $emp->employee_code, "name" => $emp->name, "email" => $emp->email];
            }
        }
        return $empArr;
    }
    
    /**
     * check if request time is registered
     * @param Request $request
     * @return boolean
     */
    public function checkOccupiedTimeSlot(Request $request) 
    {
        $registEmpTable = OtEmployee::getTableName();
        $registTable = OtRegister::getTableName();
        $employee_id = $request->id;
        $register_id = $request->regId;
        $time = Carbon::createFromFormat('d-m-Y H:i', $request->time)->toDateTimeString();
        $checkFreeSlot = DB::table("{$registEmpTable}");
        if ($register_id && OtRegister::find($register_id)) {
            $checkFreeSlot = $checkFreeSlot->where("ot_register_id", "!=", $register_id);
        }
        $checkFreeSlot = $checkFreeSlot->where("{$registEmpTable}.start_at", '<=', $time)
                            ->where("{$registEmpTable}.end_at", '>=', $time)
                            ->where("{$registEmpTable}.employee_id", "=", $employee_id)
                            ->join("{$registTable}", "{$registTable}.id", "=", "{$registEmpTable}.ot_register_id")
                            ->where("{$registTable}.status", "<>", OtRegister::REMOVE)
                            ->whereNull("{$registTable}.deleted_at")
                            ->whereNull("{$registEmpTable}.deleted_at")
                            ->count('ot_register_id');
           
        return $checkFreeSlot;
    }

    public function ajaxCheckOccupiedTimeSlot(Request $request)
    {
        $startDate = Carbon::createFromFormat('d-m-Y H:i', $request->startDate)->toDateTimeString();
        $endDate = Carbon::createFromFormat('d-m-Y H:i', $request->endDate)->toDateTimeString();

        return response()->json(OtEmployee::checkRegisterExist($request->employeeId, $startDate, $endDate, $request->registerId));
    }

    /*
     * Check exist time lot for all employee register OT before submit
     */
    public function ajaxCheckExistTimeSlotByEmployees(Request $request)
    {
        $hasErrorExist = false;
        $dataEmployees = json_decode($request->dataEmployees);
        if (!$dataEmployees) {
            return response()->json($hasErrorExist);
        }
        $hasOtDisallow = [];
        $errorsExist = [];

        foreach ($dataEmployees as $item) {
            $employeeIds[] = $item->empId;
            if (!$item->startAt || !$item->endAt) {
                continue;
            }
                $getDisableOtExist = OtEmployee::getDisableOtExist($item->empId);
                if($item->isPaid){
                    if($getDisableOtExist){
                        $hasErrorExist = true;
                        $hasOtDisallow[] = Lang::get('ot::message.Employee name: :employee_name, employee code: :employee_code', ['employee_name' => $item->empName, 'employee_code' => $item->empCode]);
                 }
                }

             $registerExist = OtEmployee::getRegisterExist($item->empId, Carbon::createFromFormat('d-m-Y H:i', $item->startAt)->format('Y-m-d H:i:s'), Carbon::createFromFormat('d-m-Y H:i', $item->endAt)->format('Y-m-d H:i:s'), $request->registerId);
             if ($registerExist) {
                 $hasErrorExist = true;
                 $errorsExist[] = Lang::get('ot::message.Employee name: :employee_name, employee code: :employee_code', ['employee_name' => $registerExist->employee_name, 'employee_code' => $registerExist->employee_code]);
             }
        }
        $html = view('ot::include.error_time_lot_exist')->with(['hasOtDisallow' => $hasOtDisallow])->with(['errorsExist' => $errorsExist])->render();

        return response()->json(['hasErrorExist' => $hasErrorExist, 'html' => $html]);
    }
    
    /**
     * get project approvers list ajax
     * @param Request $request
     * @return array
     */
    public function getProjectApprovers (Request $request)
    {
        $userCurrent = Permission::getInstance()->getEmployee();
        return OtEmployee::getProjectApprovers($request->idProject, $userCurrent->id);
    }
}
