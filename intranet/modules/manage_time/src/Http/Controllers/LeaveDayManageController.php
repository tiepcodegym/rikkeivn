<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Auth;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Log;
use Maatwebsite\Excel\Collections\SheetCollection;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\ManageTime\Model\LeaveDay;
use Rikkei\ManageTime\Model\LeaveDayBaseline;
use Rikkei\ManageTime\Model\LeaveDayHistories;
use Rikkei\ManageTime\View\LeaveDayPermission;
use Rikkei\ManageTime\View\ManageLeaveDay;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Permission;
use Session;

class LeaveDayManageController extends Controller
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
    public function index(Request $request) 
    {
        Breadcrumb::add(Lang::get('manage_time::view.Day manage'));
        if (!Permission::getInstance()->isScopeCompany()
                && !Permission::getInstance()->isScopeTeam()) {
            return view('core::errors.permission_denied');
        }
        $month = $request->get('month');
        if ($month && !preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])$/', $month)) {
            return redirect()->route('manage_time::admin.manage-day-of-leave.index');
        }
        $monthNow = Carbon::now()->format('Y-m');
        $isBaseline = false;
        if (!$month || $month == $monthNow) {
            $month = null;
        } else {
            $isBaseline = true;
        }

        $collectionModel = LeaveDay::getGridData(null, $month);

        $params = [
            'month' => $month,
            'monthNow' => $monthNow,
            'isBaseline' => $isBaseline,
            'leaveDayTbl' => $isBaseline ? LeaveDayBaseline::getTableName() : LeaveDay::getTableName(),
            'collectionModel' => $collectionModel
        ];

        return view('manage_time::leave.manage.leave_day_list', $params);
    }

    /**
     * Save leave day of employee
     *
     * @return Response redirect    redirect to Leave Day Management page
     */ 
    public function edit() 
    {
        $data = Input::get();
        $id = $data['id'];
        if($id) {
            $day = LeaveDay::find($id);
            if(!$day){
                return redirect()->route('manage_time::admin.manage-day-of-leave.index')->with('flash_error',Lang::get('manage_time::view.Error input data!'));
            }
        }

        DB::beginTransaction();
        try {
            $dayData['day_last_year'] = (float)$data['day_last_year'];
            $dayData['day_last_transfer'] = (float)$data['day_last_transfer'];
            $dayData['day_current_year'] = (float)$data['day_current_year'];
            $dayData['day_seniority'] = (float)$data['day_seniority'];
            $dayData['day_ot'] = (float)$data['day_OT'];
            $dayData['day_used'] = (float)$data['day_used'];
            $dayData['note'] = $data['note'];
            $dayData['updated_at'] = Carbon::now();

            $totalDay = ManageLeaveDay::totalDay($dayData['day_last_transfer'], $dayData['day_current_year'], $dayData['day_seniority'], $dayData['day_ot']);

            $validator = Validator::make($dayData, [
                'day_last_year' =>'numeric|min: 0',
                'day_last_transfer' => 'numeric|min:0',
                'day_current_year' => 'numeric|min:0',
                'day_seniority' => 'numeric|min:0',
                'day_ot' => 'numeric|min:0',
                'day_used' => 'numeric|min:0',
            ]);

            if ($validator->fails()) {
                return redirect()->route('manage_time::admin.manage-day-of-leave.index')->with('flash_error',Lang::get('manage_time::view.Error input data!'));
            }

            //Save history
            $leaveDayPermission = new LeaveDayPermission();
            $changes = $leaveDayPermission->findChanges($day, $dayData);
            if (count($changes)) {
                $leaveDayPermission->saveHistory($day->employee_id, $changes, LeaveDayHistories::TYPE_EDIT);
            }

            $day->setData($dayData);
            $day->save();

            DB::commit();
            return redirect()->route('manage_time::admin.manage-day-of-leave.index')->with('flash_success',Lang::get('manage_time::view.Save success message'));
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex);
            return redirect()->route('manage_time::admin.manage-day-of-leave.index')->with('flash_error',Lang::get('manage_time::view.Error input data!'));
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $listLeaveDayIds = explode(',', $request->leaveDayIds);

            $values = [
                'day_last_year' => 0,
                'day_last_transfer' => 0,
                'day_current_year' => 0,
                'day_seniority' => 0,
                'day_ot' => 0,
                'day_used' => 0,
                'note' => null,
                'updated_at' => Carbon::now()
            ];
            $leaveDays = LeaveDay::whereIn('id', $listLeaveDayIds);
            $leaveDays->update($values);
            $leaveDays->delete();
            
            DB::commit();

            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Delete data success'),
                ]
            ];

            $request->session()->flash('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
            $messages = [
                'errors'=> [
                    $ex->getMessage(),
                ]
            ];
            $request->session()->flash('messages', $messages);
        }

        return;
    }

    /**
    * Import day
    */ 
    public function importFile(Request $request) 
    {
    	$valid = Validator::make($request->all(), [
           'file' => 'required|max:8192'
        ]);
        if ($valid->fails()) {
        	Session::flash('messages', ['errors' => [Lang::get('leave::view.Overload Data')]]);
            return response()->json($valid->errors()->first('file'), 422);
        }
    	$file = $request->file('file'); 
    	$extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['xlsx', 'xls'])) {
        	Session::flash('messages', ['errors' => [Lang::get('validation.mimes', ['attribute' => 'import file', 'values' => 'xlsx, xls']), 422]]);
            return response()->json(trans('validation.mimes', ['attribute' => 'import file', 'values' => 'xlsx, xls, csv']), 422);
        }
        $data = Excel::load($file->getRealPath(), 'UTF-8', function ($reader) {
        	$reader->ignoreEmpty();
            $reader->formatDates(false);
        })->get();
        if ($data instanceof SheetCollection) {
            $data = $data->toArray();
            array_shift($data[0]);
            array_filter($data[0]);
            $data = $data[0];
        } else {
            $data = $data->toArray();
            $data_shift = array_shift($data);
        }
        DB::beginTransaction();
        try {
            $errors = [];
            $leaveDayPermis = new LeaveDayPermission();
            $dataInsertHistories = [];
            $currentLogId = Auth::id();
            foreach ($data as $index => $data_item) {
                if (!array_key_exists('ma_nhan_vien', $data_item) ||
                        !array_key_exists('so_ngay_phep_nam_truoc_con_lai', $data_item) ||
                        !array_key_exists('so_ngay_phep_nam_truoc_chuyen_sang', $data_item) ||
                        !array_key_exists('so_ngay_phep_duoc_nghi_trong_nam', $data_item) ||
                        !array_key_exists('so_ngay_phep_theo_tham_nien', $data_item) ||
                        !array_key_exists('so_ngay_phep_duoc_cong_do_ot', $data_item) ||
                        !array_key_exists('so_ngay_phep_da_nghi', $data_item) ||
                        !array_key_exists('ghi_chu', $data_item)) {
                    Session::flash('messages', ['errors' => [Lang::get('manage_time::view.Invalid file')]]);
                    return response()->json(Lang::get('manage_time::view.Invalid file'));
                }
                if (array_filter($data_item)) {
                    $employeeCode = $data_item['ma_nhan_vien'];
                    $employee = Employee::where('employee_code', $employeeCode)->first();

                    $rowErr = $index + 1;
                    if (!$employee) {
                        $errors[] = Lang::get('manage_time::view.Employee code error') . $rowErr . Lang::get('manage_time::view.Invalid');
                        continue;
                    }
                    $day = LeaveDay::where('employee_id', '=', $employee->id)->withTrashed()->first();
                    $currentTime = Carbon::now();
                    if (!$day) {
                        $day = new LeaveDay();
                        $day->employee_id = $employee->id;
                        $day->created_at = $currentTime;
                    }
                    $day->updated_at = $currentTime;
                    $day->deleted_at = null;

                    $totalDay = ManageLeaveDay::totalDay($data_item['so_ngay_phep_nam_truoc_chuyen_sang'], $data_item['so_ngay_phep_duoc_nghi_trong_nam'], $data_item['so_ngay_phep_theo_tham_nien'], $data_item['so_ngay_phep_duoc_cong_do_ot']);

                    $dayData = [];
                    if (!$data_item['so_ngay_phep_nam_truoc_con_lai']) {
                        $dayData['day_last_year'] = 0;
                    } else {
                        $dayData['day_last_year'] = $data_item['so_ngay_phep_nam_truoc_con_lai'];
                    }

                    if (!$data_item['so_ngay_phep_nam_truoc_chuyen_sang']) {
                        $dayData['day_last_transfer'] = 0;
                    } else {
                        $dayData['day_last_transfer'] = $data_item['so_ngay_phep_nam_truoc_chuyen_sang'];
                    }

                    if (!$data_item['so_ngay_phep_duoc_nghi_trong_nam']) {
                        $dayData['day_current_year'] = 0;
                    } else {
                        $dayData['day_current_year'] = $data_item['so_ngay_phep_duoc_nghi_trong_nam'];
                    }

                    if (!$data_item['so_ngay_phep_theo_tham_nien']) {
                        $dayData['day_seniority'] = 0;
                    } else {
                        $dayData['day_seniority'] = $data_item['so_ngay_phep_theo_tham_nien'];
                    }

                    if (!$data_item['so_ngay_phep_duoc_cong_do_ot']) {
                        $dayData['day_ot'] = 0;
                    } else {
                        $dayData['day_ot'] = $data_item['so_ngay_phep_duoc_cong_do_ot'];
                    }

                    if (!$data_item['so_ngay_phep_da_nghi']) {
                        $dayData['day_used'] = 0;
                    } else {
                        $dayData['day_used'] = $data_item['so_ngay_phep_da_nghi'];
                    }

                    $dayData['note'] = $data_item['ghi_chu'];

                    //Save histories
                    $change = $leaveDayPermis->findChanges($day, $dayData);
                    if (count($change)) {
                        $leaveDayHistory = new LeaveDayHistories();
                        $dataInsertHistories[] = [
                            'id' => $leaveDayHistory->id,
                            'employee_id' => $day->employee_id,
                            'content' => json_encode($change),
                            'type' => LeaveDayHistories::TYPE_IMPORT,
                            'created_by' => $currentLogId,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }

                    $day->setData($dayData);
                    $day->save();
                }
            }
            if (count($dataInsertHistories)) {
                LeaveDayHistories::insert($dataInsertHistories);
            }
            if (count($errors)) {
                DB::rollback();
                $messages = [
                    'errors' => $errors
                ];
            } else {
                DB::commit();
                $messages = [
                    'success' => [
                        Lang::get('manage_time::view.Import success')
                    ]
                ];
            }
            Session::flash('messages', $messages);
            return;
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return response()->json($ex->getMessage(), 500);
        }
    }

    /**
    * Export file
    */ 
    public function exportFile(Request $request)
    {
        $month = $request->get('month');
        $collectionModel = LeaveDay::getGridData('export', $month)->get()->toArray();
        if(empty($collectionModel)) {
            return redirect()->route('manage_time::admin.manage-day-of-leave.index')->with('flash_error',Lang::get('manage_time::view.No data to export'));
        }
        Excel::create('Export data', function($excel) use($collectionModel) {
            $excel->sheet('Sheet 1', function($sheet) use($collectionModel) {
                $data = [];
                $data[0] = [Lang::get('manage_time::view.Employee code'),
                            Lang::get('manage_time::view.Employee fullname'), 
                            Lang::get('manage_time::view.Number day last year'), 
                            Lang::get('manage_time::view.Number day last year use'), 
                            Lang::get('manage_time::view.Number day current year'), 
                            Lang::get('manage_time::view.Number day seniority'), 
                            Lang::get('manage_time::view.Number day OT'), 
                            Lang::get('manage_time::view.Total number day'), 
                            Lang::get('manage_time::view.Number days of used'), 
                            Lang::get('manage_time::view.Number day remain'), 
                            Lang::get('manage_time::view.Number day note')
                        ];
                $data[1] = ['a', 'b', '1', '2', '3', '4', '5', '6=2+3+4+5', '7', '8=6-7', 'c'];
                foreach ($collectionModel as $key => $item) {
                    $data[] = [
                        $item['employee_code'],
                        $item['name'],
                        (float)number_format($item['day_last_year'], 2),
                        (float)number_format($item['day_last_transfer'], 2),
                        (float)number_format($item['day_current_year'], 2),
                        (float)number_format($item['day_seniority'], 2),
                        (float)number_format($item['day_ot'], 2),
                        (float)number_format($item['total_day'], 2),
                        (float)number_format($item['day_used'], 2),
                        (float)number_format($item['remain_day'], 2),
                        $item['note']
                    ];
                }
                $sheet->fromArray($data, null, 'A1', true, false);
                $sheet->cells('A1:K2', function($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $countData = count($data);
                $sheet->cells("C3:J{$countData}", function($cells) {
                    $cells->setAlignment('right');
                    $cells->setValignment('center');
                });
                $sheet->cells("A3:B{$countData}", function($cells) {
                    $cells->setAlignment('left');
                    $cells->setValignment('center');
                });

                $sheet->setHeight([
                    1     =>  50,
                    2     =>  25
                ]);

                $sheet->setBorder('A1:K1', 'thin');
            });
            $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true); 
        })->download('xlsx');
    }

    /**
     * History list page
     *
     * @return Response view
     */
    public function viewHistory()
    {
        Breadcrumb::add(Lang::get('manage_time::view.Day manage'), route('manage_time::admin.manage-day-of-leave.index'));
        Breadcrumb::add(Lang::get('manage_time::view.Leave day histories changes'));
        if (!LeaveDayPermission::isAllowViewHistories()) {
            return view('core::errors.permission_denied');
        }
        $leaveDayHistories = new LeaveDayHistories();
        return view('manage_time::leave.manage.histories_list', [
            'collectionModel' => $leaveDayHistories->histories(),
            'typeLabels' => LeaveDayHistories::typesLabel(),
        ]);
    }

    /**
     * History detail page
     *
     * @param string $id
     *
     * @return Response view
     */
    public function viewHistoryDetail($id)
    {
        Breadcrumb::add(Lang::get('manage_time::view.Day manage'), route('manage_time::admin.manage-day-of-leave.index'));
        Breadcrumb::add(Lang::get('manage_time::view.Leave day histories changes'));
        if (!LeaveDayPermission::isAllowViewHistories()) {
            return view('core::errors.permission_denied');
        }
        $historyModel = new LeaveDayHistories();
        $history = $historyModel->historyInfo($id);
        if (!$history) {
            return view('core::errors.404');
        }
        return view('manage_time::leave.manage.histories_detail', [
            'history' => $history,
        ]);
    }
    

    /**
     * list leave days
     *
     * @param  Request $request
     * @return array
     */
    public function listDayOfLeave(Request $request)
    {
        Breadcrumb::reset();
        Breadcrumb::add('Home', URL::to('/'), '<i class="fa fa-dashboard"></i>');
        Breadcrumb::add(Lang::get('manage_time::view.Team'));
        Breadcrumb::add(Lang::get('manage_time::view.List leave days'));

        if (!Permission::getInstance()->isAllow()) {
            return view('core::errors.permission_denied');
        }
        $month = Carbon::now()->format('Y-m');
        $objLeaveday = new LeaveDay();
        $userCurrent = Permission::getInstance()->getEmployee();
        $url = route('manage_time::division.list-day-of-leave') . '/';

        if (Permission::getInstance()->isScopeCompany()) {
            $collectionModel = $objLeaveday->getGridDataWithProject(null, $url);
        } else {
            $empIds = [];
            $objProject = new Project();
            $date =  $month . '-01';
            $employeeProj = $objProject->getEmployeeByPM($userCurrent->id, $date);
            if (count($employeeProj)) {
                $empIds = array_unique($employeeProj->pluck('employee_id')->toArray());
            }
            if (Permission::getInstance()->isScopeTeam()) {
                $teamsOfEmp = Permission::getInstance()->isScopeTeam(null, 'manage_time::division.list-day-of-leave');
                $objTeamMember = new TeamMember();
                $listEmployee = $objTeamMember->getEmployeesByTeam($teamsOfEmp);
                if (count($listEmployee)) {
                    $listEmpId = $listEmployee->lists('id')->toArray();
                    $empIds = array_merge($empIds, array_unique($listEmpId));
                }
            }
            if (count($empIds)) {
                $collectionModel = $objLeaveday->getGridDataWithProject(null, $url, $empIds);
            } else {
                $collectionModel = [];
            }
        }

        $params = [
            'isBaseline' => false,
            'leaveDayTbl' => LeaveDay::getTableName(),
            'collectionModel' => $collectionModel
        ];

        return view('team::leave.leave_day_list', $params);
    }
}