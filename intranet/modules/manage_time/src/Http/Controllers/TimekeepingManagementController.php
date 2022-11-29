<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Auth;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Log;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\ManageTime\Model\ManageTimeSettingUser;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Core\Model\CoreConfigData;
use Illuminate\Support\Facades\Mail;

class TimekeepingManagementController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add(Lang::get('manage_time::view.Timekeeping management'));
    }

    /**
    * index 
    */ 
    public function index() 
    {
        $hanoi = ManageTimeSettingUser::where('branch', Team::CODE_PREFIX_HN)->get();
        $danang = ManageTimeSettingUser::where('branch', Team::CODE_PREFIX_DN)->get();
        $hochiminh = ManageTimeSettingUser::where('branch', Team::CODE_PREFIX_HCM)->get();

        $viewData = [
            'hanoi' => $hanoi,
            'danang' => $danang,
            'hochiminh' => $hochiminh,
        ];
        return view('manage_time::setting.index', $viewData);
    }

    /**
     * Update
     *
     * @return Response
     */ 
    public function update(Request $request) 
    {
        DB::beginTransaction();
        try {
            $ids = ManageTimeSettingUser::all()->pluck('id')->toArray();
            $hanoi = $request->get('hanoi');
            $danang = $request->get('danang');
            $hochiminh = $request->get('hochiminh');

            $data = [];
            if ($hanoi) {
                foreach ($hanoi as $item) {
                    $value = [];
                    $value['branch'] = Team::CODE_PREFIX_HN;
                    $value['employee_id'] = $item;
                    $employee = Employee::where('id', $item)->first();
                    if ($employee) {
                        $value['employee_name'] = $employee->name;
                        $value['employee_email'] = $employee->email;
                        $data[] = $value;
                    }
                }
            }
            if ($danang) {
                foreach ($danang as $item) {
                    $value = [];
                    $value['branch'] = Team::CODE_PREFIX_DN;
                    $value['employee_id'] = $item;
                    $employee = Employee::where('id', $item)->first();
                    if ($employee) {
                        $value['employee_name'] = $employee->name;
                        $value['employee_email'] = $employee->email;
                        $data[] = $value;
                    }
                }
            }
            if ($hochiminh) {
                foreach ($hochiminh as $item) {
                    $value = [];
                    $value['branch'] = Team::CODE_PREFIX_HCM;
                    $value['employee_id'] = $item;
                    $employee = Employee::where('id', $item)->first();
                    if ($employee) {
                        $value['employee_name'] = $employee->name;
                        $value['employee_email'] = $employee->email;
                        $data[] = $value;
                    }
                }
            }
            $result = ManageTimeSettingUser::insert($data);
            if ($result) {
                ManageTimeSettingUser::whereIn('id', $ids)->delete();
            }
            DB::commit();
            $response['success'] = 1;
            $response['message'] = Lang::get('manage_time::message.Update success');
            return response()->json($response);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => trans('manage_time::message.An error occurred'),
            ]);
        }
    }
}