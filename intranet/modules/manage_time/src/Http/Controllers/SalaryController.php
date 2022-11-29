<?php

namespace Rikkei\ManageTime\Http\Controllers;

use App;
use Auth;
use DB;
use Lang;
use Log;
use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Http\Controllers\AuthController;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\View\TimekeepingPermission;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\Model\Salary;
use Rikkei\ManageTime\Model\SalaryTable;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\Model\TimekeepingAggregate;

class SalaryController extends Controller
{
    /**
     * [salaryTableList: get salary table list for manager]
     * @return [type]
     */
    public function salaryTableList()
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Manage salary');
        Menu::setActive('HR');

        if (!TimekeepingPermission::isScopeOfTeam() && !TimekeepingPermission::isScopeOfCompany()) {
            View::viewErrorPermission();
        }
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $teamIdAllowCreate = TimekeepingPermission::getTeamIdAllowCreate();
        $collectionModel = SalaryTable::getSalaryTableList($teamIdAllowCreate, $dataFilter);
        $timekeepingTablesList = TimekeepingTable::getTimekeepingTablesList($teamIdAllowCreate);

        $params = [
            'teamIdAllowCreate' => $teamIdAllowCreate,
            'collectionModel' => $collectionModel,
            'timekeepingTablesList' => $timekeepingTablesList
        ];
        return view('manage_time::salary.salary_table_list')->with($params);
    }

    /**
     * [salaryTableDetail: get salary table detail for manager]
     * @param  [int] $salaryTableId
     * @return [type]
     */
    public function salaryTableDetail($salaryTableId)
    {
        Breadcrumb::add('HR');
        Breadcrumb::add('Manage salary');
        Menu::setActive('HR');

        if (!TimekeepingPermission::isScopeOfTeam() && !TimekeepingPermission::isScopeOfCompany()) {
            View::viewErrorPermission();
        }
        $salaryTable = SalaryTable::find($salaryTableId);
        if (!$salaryTable) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
            return redirect()->route('manage_time::timekeeping.salary.salary-table-list')->with('messages', $messages);
        }
        $collectionModel = Salary::getSalaryBySalaryTableId($salaryTableId);
        $params = [
            'collectionModel' => $collectionModel,
            'salaryTable' => $salaryTable
        ];
        return view('manage_time::salary.salary_table_detail')->with($params);
    }

    /**
     * [salaryList: get salary list for employee]
     * @return [type]
     */
    public function salaryList()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Salary');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $yearFilter = Carbon::now()->year;
        if ($dataFilter) {
            $yearFilter = $dataFilter['year'];
        }
        $collectionModel = Salary::getSalaryCollection($userCurrent->id, $yearFilter);
        $params = [
            'collectionModel' => $collectionModel,
            'yearFilter' => $yearFilter
        ];
        return view('manage_time::salary.salary_list')->with($params);
    }

    /**
     * [salaryDetail: get salary detail for employee]
     * @param  [int] $salaryTableId
     * @return [type]
     */
    public function salaryDetail($salaryTableId = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Salary');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $tblSalary = Salary::getTableName();
        $tblSalaryTable = SalaryTable::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblTimekeepingAggregate = TimekeepingAggregate::getTableName();
        $salaryThisPeriod = Salary::getSalaryThisPeriod($userCurrent->id);
        if (!$salaryTableId) {
            if ($salaryThisPeriod) {
                return redirect()->route('manage_time::profile.salary.salary-detail', ['salaryTableId' => $salaryThisPeriod->salary_table_id]);
            } else {
                $messages = [
                    'errors'=> [
                        Lang::get('team::messages.Not found item.'),
                    ]
                ];
                return redirect()->route('manage_time::profile.salary.salary-list')->with('messages', $messages);
            }
        }
        $isSalaryThisPeriod = false;
        if ($salaryThisPeriod && $salaryTableId == $salaryThisPeriod->salary_table_id) {
            $salary = clone $salaryThisPeriod;
            $isSalaryThisPeriod = true;
        } else {
            $salary = Salary::getSalaryByEmployee($salaryTableId, $userCurrent->id);
            if (!$salary) {
                $messages = [
                    'errors'=> [
                        Lang::get('team::messages.Not found item.'),
                    ]
                ];
                return redirect()->route('manage_time::profile.salary.salary-list')->with('messages', $messages);
            }
        }
        $timekeepingAggregate = Timekeeping::getTimekeepingAggregateByEmp($salary->timekeeping_table_id, $userCurrent->id, Carbon::parse($salary->start_date), Carbon::parse($salary->end_date));
        if (!$timekeepingAggregate) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::messages.Not exist timekeeping table'),
                ]
            ];
            return redirect()->route('manage_time::profile.salary.salary-list')->with('messages', $messages);
        }
        $params = [
            'salary' => $salary,
            'isSalaryThisPeriod' => $isSalaryThisPeriod,
            'timekeepingAggregate' => $timekeepingAggregate
        ];
        return view('manage_time::salary.salary_detail')->with($params);
    }

    /**
     * [timekeepingDetail: get timekeeping detail for employee]
     * @param  [int] $salaryTableId
     * @return [type]
     */
    public function timekeepingDetail($salaryTableId)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Salary');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $salaryThisPeriod = Salary::getSalaryThisPeriod($userCurrent->id);
        $isSalaryThisPeriod = false;
        if ($salaryThisPeriod && $salaryTableId == $salaryThisPeriod->salary_table_id) {
            $isSalaryThisPeriod = true;
            $salary = clone $salaryThisPeriod;
        } else {
            $salary = Salary::getSalaryByEmployee($salaryTableId, $userCurrent->id);
        }
        if (!$salary) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
            return redirect()->route('manage_time::profile.salary.salary-list')->with('messages', $messages);
        }
        $timekeepingAggregate = Timekeeping::getTimekeepingAggregateByEmp($salary->timekeeping_table_id, $userCurrent->id, Carbon::parse($salary->start_date), Carbon::parse($salary->end_date));
        if (!$timekeepingAggregate) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::message.Not exist timekeeping aggregate'),
                ]
            ];
            return redirect()->route('manage_time::profile.salary.salary-list')->with('messages', $messages);
        }
        $timekeepingTable = TimekeepingTable::select('id', 'timekeeping_table_name', 'team_id', 'month', 'year', 'start_date', 'end_date')
            ->where('id', $salary->timekeeping_table_id)
            ->first();
        if (!$timekeepingTable) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::message.Not exist timekeeping table'),
                ]
            ];
            return redirect()->route('manage_time::profile.salary.salary-list')->with('messages', $messages);
        }
        $params = [
            'salary' => $salary,
            'isSalaryThisPeriod' => $isSalaryThisPeriod,
            'timekeepingAggregate' => $timekeepingAggregate,
            'timekeepingTable' => $timekeepingTable
        ];
        return view('manage_time::salary.timekeeping_detail')->with($params);
    }

    /**
     * [saveSalaryTable: store salary table]
     * @param  Request $request
     * @return [type]
     */
    public function saveSalaryTable(Request $request)
    {
        $dataInsert = $request->all();
        $rules = [
            'salary_table_name' => 'required',
            'team_id' => 'required',
            'month' => 'required',
            'year' => 'required',
            'start_date' => 'required',
            'end_date' => 'required|after:start_date',
            'timekeeping_table_id' => 'required'
        ];
        $messages = [
            'salary_table_name.required' => Lang::get('manage_time::message.Salary table name is required'),
            'team_id.required' => Lang::get('manage_time::message.Team id is required'),
            'month.required' => Lang::get('manage_time::message.Month is required'),
            'year.required'  => Lang::get('manage_time::message.Year is required'),
            'start_date.required'  => Lang::get('manage_time::message.Start date timekeeping is required'),
            'end_date.required' => Lang::get('manage_time::message.End date timekeeping is required'),
            'end_date.after' => Lang::get('manage_time::message.The end date timekeeping at must be after start date timekeeping'),
            'timekeeping_table_id.required' => Lang::get('manage_time::message.Timekeeping table is required')
        ];
        $validator = Validator::make($dataInsert, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        DB::beginTransaction();
        try {
            $userCurrent = Permission::getInstance()->getEmployee();
            $startDate = Carbon::createFromFormat('d-m-Y', $dataInsert['start_date']);
            $endDate = Carbon::createFromFormat('d-m-Y', $dataInsert['end_date']);
            $teamId = $dataInsert['team_id'];
            $salaryTable = new SalaryTable();
            $salaryTable->creator_id = $userCurrent->id;
            $salaryTable->salary_table_name = $dataInsert['salary_table_name'];
            $salaryTable->team_id = $teamId;
            $salaryTable->month = $dataInsert['month'];
            $salaryTable->year = $dataInsert['year'];
            $salaryTable->start_date = $startDate->toDateString();
            $salaryTable->end_date = $endDate->toDateString();
            $salaryTable->timekeeping_table_id = $dataInsert['timekeeping_table_id'];
            $totalDays = 0;
            $totalDaysWeekend = 0;
            while (strtotime($startDate) <= strtotime($endDate)) {
                $totalDays++;
                $date = Carbon::parse($startDate);
                if ($date->isWeekend()) {
                    $totalDaysWeekend++;
                }
                $startDate = date ("Y-m-d", strtotime("+1 day", strtotime($startDate)));
            }
            $salaryTable->number_working_days = $totalDays - $totalDaysWeekend;
            if ($salaryTable->save()) {
                $dataSalary = [];
                $dataInsertSalary = [];
                $employeesSalary = SalaryTable::getEmployeeSalaryByTeam($teamId);
                if ($employeesSalary) {
                    $dataSalary['salary_table_id'] = $salaryTable->id;
                    foreach ($employeesSalary as $emp) {
                        $now = Carbon::now();
                        $dataSalary['employee_id'] = $emp->employee_id;
                        $dataSalary['created_at'] = $now;
                        $dataSalary['updated_at'] = $now;
                        $dataInsertSalary[] = $dataSalary;
                    }
                }
                if ($dataInsertSalary) {
                    Salary::insert($dataInsertSalary);
                }
            }
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Create salary table success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch(Exception $ex) {
            DB::rollBack();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [$ex->getMessage()]]);
        }
    }

    /**
     * [uploadFileSalaryTable: upload file salary table]
     * @param  Request $request
     * @return [type]
     */
    public function uploadFileSalaryTable(Request $request)
    {
        $rules = [
            'file' => 'required',
        ];
        $messages = [
            'file.required' => Lang::get('manage_time::view.The file is required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        if (!in_array($extension, ['csv', 'xls', 'xlsx'])) {
            return redirect()->back()->withErrors(Lang::get('manage_time::view.Only allow file csv, xls, xlsx'));
        }
        DB::beginTransaction();
        try {
            $data = Excel::selectSheetsByIndex(0)->noHeading()->load($file->getRealPath(), 'UTF-8', function ($reader) {
                $reader->ignoreEmpty();
            })->get()->toArray(); 
            $rowErr = 0;
            $data = array_slice($data, 2); 
            if (!count($data)) {
                $messages = [
                    'errors' => [
                        Lang::get('manage_time::view.Invalid file')
                    ]
                ];
                return redirect()->back()->with('messages', $messages);
            }
            $salaryTableId = $request->salary_table_id;
            $salaryTable = SalaryTable::find($salaryTableId);
            // Check if has not salary table
            if (!$salaryTable) {
                $messages = [
                    'errors' => [
                        Lang::get('manage_time::message.Not exist salary table')
                    ]
                ];
                return redirect()->back()->with('messages', $messages);
            }

            $dataResetSalary = [
                'basic_salary' => 0,
                'official_salary' => 0,
                'trial_salary' => 0,
                'overtime_salary' => 0,
                'gasoline_allowance' => 0,
                'telephone_allowance' => 0,
                'certificate_allowance' => 0,
                'bonus_and_other_allowance' => 0,
                'other_income' => 0,
                'premium_and_union' => 0,
                'advance_payment' => 0,
                'personal_income_tax' => 0,
            ];
            Salary::where('salary_table_id', $salaryTableId)->update($dataResetSalary);
            $errors = [];
            $datasInsertSalary = [];
            $titleIndex = self::getHeadingIndexSalaryTable();
            foreach ($data as $itemRow) {
                if (!array_key_exists($titleIndex['ma_nv'], $itemRow) || 
                    !array_key_exists($titleIndex['ten_nv'], $itemRow) || 
                    !array_key_exists($titleIndex['luong_co_ban'], $itemRow) || 
                    !array_key_exists($titleIndex['luong_chinh_thuc'], $itemRow) || 
                    !array_key_exists($titleIndex['luong_thu_viec'], $itemRow) || 
                    !array_key_exists($titleIndex['luong_lam_them_gio'], $itemRow) || 
                    !array_key_exists($titleIndex['xang_xe'], $itemRow) || 
                    !array_key_exists($titleIndex['dien_thoai'], $itemRow) || 
                    !array_key_exists($titleIndex['phu_cap_chung_chi'], $itemRow) || 
                    !array_key_exists($titleIndex['thuong_phu_cap'], $itemRow) || 
                    !array_key_exists($titleIndex['thu_nhap_khac'], $itemRow) || 
                    !array_key_exists($titleIndex['bao_hiem_cong_doan'], $itemRow) || 
                    !array_key_exists($titleIndex['tam_ung_luong'], $itemRow) || 
                    !array_key_exists($titleIndex['thue_thu_nhap_ca_nhan'], $itemRow)
                ) {
                    $messages = [
                        'errors' => [
                            Lang::get('manage_time::view.Invalid file')
                        ]
                    ];
                    return redirect()->back()->with('messages', $messages);
                }
                $rowErr++;
                $dataInsert = [];
                $dataInsert['salary_table_id'] = $salaryTableId;
                $employeeCode = $itemRow[$titleIndex['ma_nv']];
                if (!$employeeCode) {
                    $errors[] = Lang::get('manage_time::view.Employee code in line :line is invalid!',  ['line' => $rowErr]);
                    continue;
                }
                if (!$itemRow[$titleIndex['luong_co_ban']]) {
                    $errors[] = Lang::get('manage_time::view.Basic salary in line :line is invalid!',  ['line' => $rowErr]);
                    continue;
                }
                $employee = Employee::select('id')
                    ->where('employee_code', $employeeCode)
                    ->first();
                if (!$employee) {
                    continue;
                }
                $dataInsert['employee_id'] = $employee->id;
                $dataInsert['basic_salary'] = $itemRow[$titleIndex['luong_co_ban']];
                if ($itemRow[$titleIndex['luong_chinh_thuc']]) {
                    $dataInsert['official_salary'] = $itemRow[$titleIndex['luong_chinh_thuc']];
                }
                if ($itemRow[$titleIndex['luong_thu_viec']]) {
                    $dataInsert['trial_salary'] = $itemRow[$titleIndex['luong_thu_viec']];
                }
                if ($itemRow[$titleIndex['luong_lam_them_gio']]) {
                    $dataInsert['overtime_salary'] = $itemRow[$titleIndex['luong_lam_them_gio']];
                }
                if ($itemRow[$titleIndex['xang_xe']]) {
                    $dataInsert['gasoline_allowance'] = $itemRow[$titleIndex['xang_xe']];
                }
                if ($itemRow[$titleIndex['dien_thoai']]) {
                    $dataInsert['telephone_allowance'] = $itemRow[$titleIndex['dien_thoai']];
                }
                if ($itemRow[$titleIndex['phu_cap_chung_chi']]) {
                    $dataInsert['certificate_allowance'] = $itemRow[$titleIndex['phu_cap_chung_chi']];
                }
                if ($itemRow[$titleIndex['thuong_phu_cap']]) {
                    $dataInsert['bonus_and_other_allowance'] = $itemRow[$titleIndex['thuong_phu_cap']];
                }
                if ($itemRow[$titleIndex['thu_nhap_khac']]) {
                    $dataInsert['other_income'] = $itemRow[$titleIndex['thu_nhap_khac']];
                }
                if ($itemRow[$titleIndex['bao_hiem_cong_doan']]) {
                    $dataInsert['premium_and_union'] = $itemRow[$titleIndex['bao_hiem_cong_doan']];
                }
                if ($itemRow[$titleIndex['tam_ung_luong']]) {
                    $dataInsert['advance_payment'] = $itemRow[$titleIndex['tam_ung_luong']];
                }
                if ($itemRow[$titleIndex['thue_thu_nhap_ca_nhan']]) {
                    $dataInsert['personal_income_tax'] = $itemRow[$titleIndex['thue_thu_nhap_ca_nhan']];
                }
                $datasInsertSalary[] = $dataInsert;
            }
            if (count($errors)) {
                DB::rollback();
                $messages = [
                    'errors' => $errors
                ];
            } else {
                foreach ($datasInsertSalary as $emp) {
                    Salary::where('salary_table_id', $emp['salary_table_id'])
                        ->where('employee_id', $emp['employee_id'])
                        ->update($emp);
                }
                DB::commit();
                $messages = [
                    'success' => [
                        Lang::get('manage_time::view.Import success')
                    ]
                ];
            }
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [$ex->getMessage()]]);
        }
    }

    /**
     * [getTimekeepingData: update salary table by timekeeping data]
     * @param  Request $request
     * @return [type]
     */
    public function getTimekeepingData(Request $request)
    {
        $rules = [
            'timekeeping_table_id' => 'required',
        ];
        $messages = [
            'timekeeping_table_id.required' => Lang::get('manage_time::view.The timekeeping table is required'),
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $salaryTableId = $request->salary_table_id;
            $salaryTable = SalaryTable::find($salaryTableId);
            // Check if has not salary table
            if (!$salaryTable) {
                $messages = [
                    'errors' => [
                        Lang::get('manage_time::message.Not exist salary table')
                    ]
                ];
                return redirect()->back()->with('messages', $messages);
            }
            $timekeepingTableId = $request->timekeeping_table_id;
            $timekeepingTable = TimekeepingTable::find($timekeepingTableId);
            if (!$timekeepingTable) {
                $messages = [
                    'errors' => [
                        Lang::get('manage_time::message.Not exist timekeeping table')
                    ]
                ];
                return redirect()->back()->with('messages', $messages);
            }
            $salaryTable->timekeeping_table_id = $timekeepingTableId;
            $salaryTable->save();

            $employeesListToSalary = Salary::where('salary_table_id', $salaryTableId)->get();
            $datasUpdateSalary = [];
            if (count($employeesListToSalary)) {
                $startDateToSalary = Carbon::parse($salaryTable->start_date);
                $endDateToSalary = Carbon::parse($salaryTable->end_date);
                foreach ($employeesListToSalary as $item) {
                    $dataInsert = [];
                    $dataInsert['salary_table_id'] = $salaryTableId;
                    $dataInsert['employee_id'] = $item->employee_id;
                    if ($item->basic_salary) {
                        $timekeepingAggregateOfEmp = Timekeeping::getTimekeepingAggregateByEmp($timekeepingTableId, $item->employee_id, $startDateToSalary, $endDateToSalary);
                        if ($timekeepingAggregateOfEmp) {
                            // Get time OT official and trial
                            $totalOTOfficial = $timekeepingAggregateOfEmp->total_official_ot_weekdays + $timekeepingAggregateOfEmp->total_official_ot_weekends + $timekeepingAggregateOfEmp->total_official_ot_holidays;
                            $totalOTTrial = $timekeepingAggregateOfEmp->total_trial_ot_weekdays + $timekeepingAggregateOfEmp->total_trial_ot_weekends + $timekeepingAggregateOfEmp->total_trial_ot_holidays;
                            // Get time working official and trial
                            $totalWorkingOfficialToSalary = $timekeepingAggregateOfEmp->total_official_working_days + $timekeepingAggregateOfEmp->total_official_business_trip + $timekeepingAggregateOfEmp->total_official_leave_day_has_salary + $timekeepingAggregateOfEmp->total_official_supplement + $timekeepingAggregateOfEmp->total_official_holiay;
                            $totalWorkingTrialToSalary = $timekeepingAggregateOfEmp->total_trial_working_days + $timekeepingAggregateOfEmp->total_trial_business_trip + $timekeepingAggregateOfEmp->total_trial_leave_day_has_salary + $timekeepingAggregateOfEmp->total_trial_supplement + $timekeepingAggregateOfEmp->total_trial_holiay;
                            // Official salary per day and per hour
                            $officialSalaryPerDay = $item->basic_salary / $salaryTable->number_working_days;
                            $officialSalaryPerHour = $officialSalaryPerDay / ManageTimeConst::TIME_WORKING_PER_DAY;
                            // Trial salary per day and per hour
                            $trialSalaryPerDay = $item->basic_trial_salary / $salaryTable->number_working_days;
                            $trialSalaryPerHour = $trialSalaryPerDay / ManageTimeConst::TIME_WORKING_PER_DAY;

                            $workingOfficialSalary = 0;
                            $workingTrialSalary = 0;
                            $otOfficialSalary = 0;
                            $otTrialSalary = 0;
                            if ($totalWorkingOfficialToSalary) {
                                $workingOfficialSalary = $totalWorkingOfficialToSalary * $officialSalaryPerDay;
                                $dataInsert['official_salary'] = round($workingOfficialSalary, 2);
                            }
                            if ($totalWorkingTrialToSalary) {
                                $workingTrialSalary = $totalWorkingTrialToSalary * $trialSalaryPerDay;
                                $dataInsert['trial_salary'] = round($workingTrialSalary, 2);
                            }
                            if ($totalOTOfficial) {
                                $otOfficialSalary = $totalOTOfficial * $officialSalaryPerHour * ManageTimeConst::SALARY_OT_RATE;
                            }
                            if ($totalOTTrial) {
                                $otTrialSalary = $totalOTTrial * $trialSalaryPerHour * ManageTimeConst::SALARY_OT_RATE;
                            }
                            $dataInsert['overtime_salary'] = round(($otOfficialSalary + $otTrialSalary), 2);
                        } else {
                            $dataInsert['official_salary'] = 0;
                            $dataInsert['trial_salary'] = 0;
                            $dataInsert['overtime_salary'] = 0;
                        }
                    } else {
                        $dataInsert['official_salary'] = 0;
                        $dataInsert['trial_salary'] = 0;
                        $dataInsert['overtime_salary'] = 0;
                    }
                    $datasUpdateSalary[] = $dataInsert;
                }
            }
            if (count($datasUpdateSalary)) {
                foreach ($datasUpdateSalary as $emp) {
                    Salary::where('salary_table_id', $emp['salary_table_id'])
                        ->where('employee_id', $emp['employee_id'])
                        ->update($emp);
                }
            }
            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Update salary table success')
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [$ex->getMessage()]]);
        }
    }

    /**
     * [getHeadingIndexSalaryTable: get index of file salary table]
     * @return [type]
     */
    public function getHeadingIndexSalaryTable()
    {
        return [
            'ma_nv' => 0,
            'ten_nv' => 1,
            'luong_co_ban' => 2,
            'luong_chinh_thuc' => 3,
            'luong_thu_viec' => 4,
            'luong_lam_them_gio' => 5,
            'xang_xe' => 6,
            'dien_thoai' => 7,
            'phu_cap_chung_chi' => 8,
            'thuong_phu_cap' => 9,
            'thu_nhap_khac' => 10,
            'bao_hiem_cong_doan' => 11,
            'tam_ung_luong' => 12,
            'thue_thu_nhap_ca_nhan' => 13,
        ];
    }
}