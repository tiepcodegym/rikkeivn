<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Carbon\Carbon;
use DB;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request as RequestUrl;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Lang;
use Log;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\FinesMoney\Model\FinesMoney;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\Model\LeaveDay;
use Rikkei\ManageTime\Model\LeaveDayHistory;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\SupplementRegister;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
use Rikkei\ManageTime\Model\TimekeepingLock;
use Rikkei\ManageTime\Model\TimekeepingLockHistories;
use Rikkei\ManageTime\Model\TimekeepingNotLateTime;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\ManageTime\Model\TimekeepingWorkingTime;
use Rikkei\ManageTime\Model\WktLog;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\TimekeepingPermission;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\ManageTime\View\ViewTimeKeeping;
use Rikkei\ManageTime\View\WorkingTime;
use Rikkei\Ot\Model\OtBreakTime;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Project\Model\Project;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\View\TeamList;
use Rikkei\ManageTime\View\WorkingTime as WorkingTimeView;
use Rikkei\Team\Model\EmployeeRelationship;

class TimekeepingController extends Controller
{
    const LIMIT = 50; // Records on a page
    const FOLDER_APP = 'app/';

    /**
     * Get timekeeping list by employee
     * @return [type]
     */
    public function getTimekeepingListByEmployee()
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Timekeeping');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        $timekeepingThisPeriod = TimekeepingTable::getTimekeepingThisPeriod($userCurrent->id);
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $yearFilter = TimekeepingTable::getMaxYear();
        if (!$yearFilter) {
            $yearFilter = Carbon::now()->year;
        }
        if ($dataFilter) {
            $yearFilter = $dataFilter['year'];
        }
        $collectionModel = TimekeepingTable::getCollectionTimekeepingByEmp($userCurrent->id, $yearFilter);
        $teamCodePrefix = Team::getTeamCodePrefixOfEmployee($userCurrent);
        $params = [
            'collectionModel' => $collectionModel,
            'yearFilter' => $yearFilter,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePrefix),
            'idTimeKeepingMax' => $timekeepingThisPeriod ? $timekeepingThisPeriod->timekeeping_table_id : '',
        ];
        return view('manage_time::timekeeping.personal.timekeeping_list')->with($params);
    }

    /**
     * Get timekeeping detail by employee
     * @param  [int|null] $timekeepingId
     * @return [type]
     */
    public function getTimekeepingDetailByEmployee($timekeepingId = null)
    {
        Breadcrumb::add('Profile');
        Breadcrumb::add('Timekeeping');
        Menu::setActive('Profile');

        $userCurrent = Permission::getInstance()->getEmployee();
        //check date
        if (!$timekeepingId) {
            $timekeepingId = with(new TimekeepingTable())->checkDateGetTimekeeping($userCurrent->id);
        }

        $timekeepingThisPeriod = TimekeepingTable::getTimekeepingThisPeriod($userCurrent->id);
        if (!$timekeepingId) {
            if ($timekeepingThisPeriod) {
                return redirect()->route('manage_time::profile.timekeeping', ['id' => $timekeepingThisPeriod->timekeeping_table_id]);
            } else {
                $messages = [
                    'errors'=> [
                        Lang::get('team::messages.Not found item.'),
                    ]
                ];
                return redirect()->route('manage_time::profile.timekeeping-list')->with('messages', $messages);
            }
        }
        $isTimekeepingThisPeriod = false;
        if ($timekeepingThisPeriod && $timekeepingId == $timekeepingThisPeriod->timekeeping_table_id) {
            $timekeepingTable = clone $timekeepingThisPeriod;
            $isTimekeepingThisPeriod = true;
        } else {
            $timekeepingTable = TimekeepingTable::getTimekeepingDetailByEmp($timekeepingId, $userCurrent->id);
            if (!$timekeepingTable) {
                $messages = [
                    'errors'=> [
                        Lang::get('team::messages.Not found item.'),
                    ]
                ];
                return redirect()->route('manage_time::profile.timekeeping-list')->with('messages', $messages);
            }
        }
        ViewTimeKeeping::cronRelatedPerson($userCurrent->id);
        $timekeepingAggregate = TimekeepingAggregate::getTimekeepingAggregateByEmp($timekeepingTable->timekeeping_table_id, $userCurrent->id);
        $timeKeepingList = Timekeeping::join('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
                ->join('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
                ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
                ->leftJoin('employee_works', 'employee_works.employee_id', '=', 'employees.id')
                ->where('timekeeping_table_id', $timekeepingId)
                ->where('manage_time_timekeepings.employee_id', $userCurrent->id)
                ->select(
                    'manage_time_timekeepings.*',
                    'teams.code',
                    'manage_time_timekeeping_tables.type as contract_type',
                    'manage_time_timekeeping_tables.date_max_import',
                    DB::raw('date(employees.join_date) as join_date'),
                    DB::raw('date(employees.trial_date) as trial_date'),
                    DB::raw('date(employees.leave_date) as leave_date'),
                    DB::raw('date(employees.offcial_date) as offcial_date')
                )
                ->get();
        $dataKeeping = [];
        foreach ($timeKeepingList as $keepingItem) {
            $dataKeeping[$keepingItem->timekeeping_date] = $keepingItem;
        }
        $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
        $arrHolidays[$teamCodePrefix] = CoreConfigData::getHolidayTeam($teamCodePrefix);

        $objNotLate = new TimekeepingNotLateTime();
        $objWorkingTime = new WorkingTime();
        $workingTimdDate = $objWorkingTime->getStrWorkingTime([$userCurrent->id], $timekeepingTable, $teamCodePrefix);
        $params = [
            'timekeepingTable' => $timekeepingTable,
            'timekeepingAggregate' => $timekeepingAggregate,
            'isTimekeepingThisPeriod' => $isTimekeepingThisPeriod,
            'dataKeeping' => $dataKeeping,
            'daysOffInTimeBusiness' => ManageTimeView::daysOffInTimeBusiness($timekeepingAggregate, $timekeepingTable),
            'teamCodePrefix' => $teamCodePrefix,
            'compensationDays' => $compensationDays,
            'arrHolidays' => $arrHolidays,
            'idTimeKeepingMax' =>  $timekeepingThisPeriod ? $timekeepingThisPeriod->timekeeping_table_id : '',
            'dataNotLate' => $objNotLate->getNotLateTimeByEmpId($userCurrent->id),
            'workingTimdDate' => $workingTimdDate[$userCurrent->id],
        ];
        return view('manage_time::timekeeping.personal.timekeeping_detail')->with($params);
    }

    /**
     * [manageTimekeepingTable: get timekeeping table list]
     * @return [view]
     */
    public function manageTimekeepingTable()
    {
        Breadcrumb::add('Admin');
        Breadcrumb::add('Timekeeping', route('manage_time::timekeeping.manage-timekeeping-table'));

        if (!TimekeepingPermission::isScopeOfTeam() && !TimekeepingPermission::isScopeOfCompany() && !TimekeepingPermission::isPermissionView()) {
            View::viewErrorPermission();
        }
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        $teamIdAllowCreate = TimekeepingPermission::getTeamIdAllowCreateView();
        ViewTimeKeeping::cronRelatedPerson();
        $param = [
            'collectionModel' => TimekeepingTable::getTimekeepingTableCollection($teamIdAllowCreate, $dataFilter),
            'teamIdAllowCreate' => $teamIdAllowCreate,
            'typesOffcial' => getOptions::typeEmployeeOfficial(),
        ];
        return view('manage_time::timekeeping.manage.list_table_timekeeping')->with($param);
    }

    /**
     * [saveTimeKeepingTable: store timekeeping table]
     * @param  Request $request
     * @return [type]
     */
    public function saveTimeKeepingTableOld(Request $request, $teamCodePrefix)
    {
        if (!TimekeepingPermission::isPermission()) {
            return redirect()->back()->with('messages', ['errors' => [Lang::get('core::message.You don\'t have access')]]);
        }
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 300);
        $dataInsert = $request->all();
        $rules = [
            'timekeeping_table_name' => 'required',
            'team_id' => 'required',
            'month' => 'required',
            'year' => 'required',
            'start_date' => 'required',
            'end_date'=> 'required|after:start_date'
        ];
        $messages = [
            'timekeeping_table_name.required' => Lang::get('manage_time::message.Timekeeping table name is required'),
            'team_id.required' => Lang::get('manage_time::message.Team id is required'),
            'month.required' => Lang::get('manage_time::message.Month is required'),
            'year.required'  => Lang::get('manage_time::message.Year is required'),
            'start_date.required'  => Lang::get('manage_time::message.Start date timekeeping is required'),
            'end_date.required' => Lang::get('manage_time::message.End date timekeeping is required'),
            'end_date.after' => Lang::get('manage_time::message.The end date timekeeping at must be after start date timekeeping')
        ];
        $validator = Validator::make($dataInsert, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }
        try {
            DB::beginTransaction();
            $userCurrent = Permission::getInstance()->getEmployee();
            $startDate = Carbon::createFromFormat('d-m-Y', $dataInsert['start_date']);
            $endDate = Carbon::createFromFormat('d-m-Y', $dataInsert['end_date']);
            $teamId = $dataInsert['team_id'];
            $timekeepingTable = new TimekeepingTable();
            $timekeepingTable->creator_id = $userCurrent->id;
            $timekeepingTable->timekeeping_table_name = $dataInsert['timekeeping_table_name'];
            $timekeepingTable->team_id = $teamId;
            $timekeepingTable->month = $dataInsert['month'];
            $timekeepingTable->year = $dataInsert['year'];
            $timekeepingTable->start_date = $startDate->toDateString();
            $timekeepingTable->end_date = $endDate->toDateString();
            $timekeepingTable->type = $request->contract_type;

           /* $teamOfTimekeeping = Team::find($timekeepingTable->team_id);
            $teamCodePrefix = Team::getTeamCodePrefix($teamOfTimekeeping->code);
            $teamCodePrefix = Team::changeTeam($teamCodePrefix);*/

            $annualHolidays = CoreConfigData::getAnnualHolidays(2);
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
            $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);

            if ($timekeepingTable->save()) {
                $dataTimekeeping = new Timekeeping();
                $dataInsertTimekeeping = [];
                $dataTimekeepingAggregate = [];
                $dataInsertTimekeepingAggregate = [];
                if ($request->contract_type == getOptions::WORKING_OFFICIAL) {
                    $contractType = [getOptions::WORKING_OFFICIAL, getOptions::WORKING_UNLIMIT, getOptions::WORKING_PROBATION];
                } else {
                    $contractType = [getOptions::WORKING_PARTTIME];
                    if ($teamCodePrefix === \Rikkei\Team\View\TeamConst::CODE_DANANG) {
                        $contractType[] = getOptions::WORKING_INTERNSHIP;
                    }
                }
                $employeeTimekeeping = TimekeepingTable::getEmployeeTimekeepingByTeam($timekeepingTable, $contractType);
                if (count($employeeTimekeeping)) {
                    $dates = [];
                    while (strtotime($startDate) <= strtotime($endDate)) {
                        $dates[] = $startDate->toDateString();
                        $startDate->addDay();
                    }
                    $dataTimekeeping->timekeeping_table_id = $timekeepingTable->id;
                    $dataTimekeepingAggregate['timekeeping_table_id'] = $timekeepingTable->id;
                    $now = Carbon::now();
                    $manageTimeView = new ManageTimeView();
                    foreach ($employeeTimekeeping as $emp) {
                        $dataTimekeeping->employee_id = $emp->employee_id;
                        $dataTimekeeping->created_at = $now;
                        $dataTimekeeping->updated_at = $now;
                        $dataTimekeepingAggregate['employee_id'] = $emp->employee_id;
                        $dataTimekeepingAggregate['created_at'] = $now;
                        $dataTimekeepingAggregate['updated_at'] = $now;

                        $empOffcialDate = $emp->offcial_date;
                        $empTrialDate = $emp->trial_date;
                        $empOffcialDateCarbon = Carbon::parse($empOffcialDate)->format('Y-m-d');
                        foreach ($dates as $date) {
                            $dateCarbon = Carbon::createFromFormat('Y-m-d', $date);
                            $isWeekend = ManageTimeCommon::isWeekend($dateCarbon, $compensationDays);
                            $isHoliday = ManageTimeCommon::isHolidays($dateCarbon, [$annualHolidays, $specialHolidays]);
                            $dataTimekeeping->timekeeping_date = $date;

                            $dataTimekeeping->is_official =  0;
                            if ($empOffcialDate && strtotime($dateCarbon->format('Y-m-d')) >= strtotime($empOffcialDateCarbon)) {
                                $dataTimekeeping->is_official =  1;
                            }

                            if (empty($emp->leave_date) || Carbon::parse($emp->leave_date)->gte(Carbon::parse($date))) {
                                $timekeepingResult = $manageTimeView->timekeepingResult($dataTimekeeping, $isWeekend, $isHoliday, $empOffcialDate, $empTrialDate, $timekeepingTable->contract_type, null, null, $timekeepingTable->type);
                                $dataTimekeeping->timekeeping = $timekeepingResult[0];
                                $dataTimekeeping->timekeeping_number = $timekeepingResult[1];
                            } else {
                                $dataTimekeeping->timekeeping = 0;
                                $dataTimekeeping->timekeeping_number = 0;
                            }
                            $dataInsertTimekeeping[] = $dataTimekeeping->toArray();
                        }
                        $dataInsertTimekeepingAggregate[] = $dataTimekeepingAggregate;
                    }
                    unset($employeeTimekeeping);
                    foreach (collect($dataInsertTimekeeping)->chunk(1000) as $chunk) {
                        Timekeeping::insert($chunk->toArray());
                    }
                    unset($dataInsertTimekeeping);
                    foreach (collect($dataInsertTimekeepingAggregate)->chunk(1000) as $chunk) {
                        TimekeepingAggregate::insert($chunk->toArray());
                    }
                    unset($dataInsertTimekeepingAggregate);
                }
            }
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Create timekeeping table success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [Lang::get('manage_time::message.Error. Please try again later.')]]);
        }
    }
    /**
     * getLeaveRegister: modal leave register
     * @param  Request $request
     * @return Response json
     */
    public function getLeaveRegister(Request $request)
    {
        dd(1111);
//        $userCurrent = Permission::getInstance()->getEmployee();
//        $teams = Team::getTeamOfEmployee($userCurrent->id);
//        $registrantInformation = ManageTimeCommon::getRegistrantInformation($userCurrent->id);
//        $teamCodePre = Team::getOnlyOneTeamCodePrefixChange($userCurrent, $teams);
//        $listLeaveDayReasons = LeaveDayReason::getLeaveDayReasons($teamCodePre);
//
//        // Get working time this month of employee logged
//        $objWTView = new WorkingTimeView();
//        $workingTime = $objWTView->getWorkingTimeByEmployeeBetween($userCurrent->id, $teamCodePre);
//        $timeSetting = $workingTime['timeSetting'];
//        $timeWorkingQuater = $workingTime['timeWorkingQuater'];
//        $groupEmail = CoreConfigData::getGroupEmailRegisterLeave();
//        $registerBranch = CoreConfigData::checkBranchRegister(false, $teamCodePre);
//        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
//        $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePre);
//        $leaveDay = LeaveDay::getLeaveDayById($userCurrent->id);
//        $getRelation = EmployeeRelationship::getListRelationEmpNotDie($userCurrent->id);
//        $creator = Employee::getEmpById($userCurrent->id);
//        // if employee type japan
//        if ($teamCodePre == Team::CODE_PREFIX_JP) {
//            $grantDate = LeaveDayController::setGrantDateEmployeeJp($creator);
//        } else {
//            $grantDate = [
//                'last_grant_date' => '',
//                'next_grant_date' => ''
//            ];
//        }
//        $params = [
//            'registrantInformation' => $registrantInformation,
//            'listLeaveDayReasons' => $listLeaveDayReasons,
//            'specialHolidays' => $specialHolidays,
//            'annualHolidays' => $annualHolidays,
//            'suggestApprover' => ManageTimeCommon::suggestApprover(ManageTimeConst::TYPE_LEAVE_DAY, $userCurrent),
//            'curEmp' => $userCurrent,
//            'regsUnapporve' => ManageTimeView::dayOffUnapprove($userCurrent->id),
//            'timeSetting' => $timeSetting,
//            'keyDateInit' => date('Y-m-d'),
//            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
//            'teamCodePreOfEmp' => $teamCodePre,
//            'groupEmail' => $groupEmail,
//            'registerBranch' => $registerBranch,
//            'weekends' => ManageTimeCommon::getAllWeekend(),
//            'timeWorkingQuater' => $timeWorkingQuater,
//            'leaveDay' => $leaveDay,
//            'getRelation' => $getRelation,
//            'grantDate' => $grantDate,
//            'dateStart' => date('d-m-Y 8:00', strtotime($request->date)),
//            'dateEnd' => date('d-m-Y 17:30', strtotime($request->date)),
//        ];
//
//        $getLeaveRegister = view('manage_time::timekeeping.personal.modal_leave_register', $params)->render();
//        return \Response::json([
//            'getLeaveRegister' => $getLeaveRegister,
//        ]);
    }

    /**
     * [getTimekeepingDetail: view timekeeping detail]
     * @param  int|null $timekeepingTableId
     * @return [view]
     */
    public function getTimekeepingDetail($timekeepingTableId = null)
    {
        Breadcrumb::add('Admin');
        Breadcrumb::add('Timekeeping', route('manage_time::timekeeping.manage-timekeeping-table'));

        if (!TimekeepingPermission::isScopeOfTeam() && !TimekeepingPermission::isScopeOfCompany() && !TimekeepingPermission::isPermissionView()) {
            View::viewErrorPermission();
        }
        $teamIdAllowCreate = TimekeepingPermission::getTeamIdAllowCreateView();
        $yearCurrent = Carbon::now()->year;
        if (!$timekeepingTableId) {
            $timeKeepingTableDefault = TimekeepingTable::select('id', 'timekeeping_table_name', 'team_id', 'start_date', 'end_date')
                ->where('year', $yearCurrent)
                ->whereIn('team_id', $teamIdAllowCreate)
                ->orderBy('month', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();
            if ($timeKeepingTableDefault) {
                return redirect()->route('manage_time::timekeeping.timekeeping-detail', ['timekeepingTableId' => $timeKeepingTableDefault->id]);
            } else {
                $messages = [
                    'errors'=> [
                        Lang::get('manage_time::message.You have not created timekeeping table in :year', ['year' => $yearCurrent]),
                    ]
                ];
                return redirect()->route('manage_time::timekeeping.manage-timekeeping-table')->with('messages', $messages);
            }
        }
        $timeKeepingTable = TimekeepingTable::getTimekeepingTable($timekeepingTableId);

        if (!$timeKeepingTable) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
            return redirect()->route('manage_time::timekeeping.manage-timekeeping-table')->with('messages', $messages);
        }
        if (!in_array($timeKeepingTable->team_id, $teamIdAllowCreate)) {
            View::viewErrorPermission();
        }

        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $startDate = Carbon::parse($timeKeepingTable->start_date);
        $endDate = Carbon::parse($timeKeepingTable->end_date);
        $yearFilter = $timeKeepingTable->year;
        if (!$yearFilter) {
            $yearFilter = Carbon::now()->format('Y');
        }

        $teamIdAllowCreate = TimekeepingPermission::getTeamIdAllowCreate();
        $timekeepingTablesList = TimekeepingTable::getTimekeepingTablesList($teamIdAllowCreate, $yearFilter);
        $collectionModel = Timekeeping::getEmployeesToTimekeeping($timekeepingTableId, $dataFilter);
        $datesTimekeeping = ManageTimeCommon::getDateRange($startDate, $endDate);

        $empIdInList = $collectionModel->lists('employee_id')->toArray();
        $timeKeepingList = Timekeeping::join('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
            ->join('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
            ->join('teams', 'teams.id', '=', 'manage_time_timekeeping_tables.team_id')
            ->leftJoin('employee_works', 'employee_works.employee_id', '=', 'employees.id')
            ->where('timekeeping_table_id', $timekeepingTableId)
            ->whereIn('manage_time_timekeepings.employee_id', $empIdInList)
            ->select(
                'manage_time_timekeepings.*',
                DB::raw('date(employees.join_date) as join_date'),
                DB::raw('date(employees.trial_date) as trial_date'),
                DB::raw('date(employees.offcial_date) as offcial_date'),
                DB::raw('date(employees.leave_date) as leave_date'),
                'manage_time_timekeeping_tables.type as contract_type',
                'manage_time_timekeeping_tables.date_max_import',
                'teams.code'
            )
            ->get();
        $dataKeeping = [];

        $teamOfTimekeeping = Team::getTeamById($timeKeepingTable->team_id);
        $teamCodePre = Team::getTeamCodePrefix($teamOfTimekeeping->code);

        $arrTeamPrefix = [];
        $arrCompensationDays = [];
        $arrHolidays = [];
        foreach ($timeKeepingList as $keepingItem) {
            $dataKeeping[$keepingItem->employee_id][$keepingItem->timekeeping_date] = $keepingItem;
            if (!array_key_exists($keepingItem->employee_id, $arrTeamPrefix)) {
                $teamCodePrefix = Team::getTeamCodePrefix($keepingItem->code);
                $arrTeamPrefix[$keepingItem->employee_id] = $teamCodePrefix;
            }
            if (!array_key_exists($teamCodePrefix, $arrCompensationDays)) {
                $arrCompensationDays[$teamCodePrefix] = CoreConfigData::getCompensatoryDays($teamCodePrefix);
            }
            if (!array_key_exists($teamCodePrefix, $arrHolidays)) {
                $arrHolidays[$teamCodePrefix] = CoreConfigData::getHolidayTeam($teamCodePrefix);
            }
        }
        $params = [
            'collectionModel'  => $collectionModel,
            'timeKeepingTable' => $timeKeepingTable,
            'datesTimekeeping' => $datesTimekeeping,
            'yearFilter' => $yearFilter,
            'timekeepingTablesList' => $timekeepingTablesList,
            'empIdInList' => $empIdInList,
            'dataKeeping' => $dataKeeping,
            'compensationDays' => CoreConfigData::getCompensatoryDays($teamCodePre),
            'arrTeamPrefix' => $arrTeamPrefix,
            'arrCompensationDays' => $arrCompensationDays,
            'arrHolidays' => $arrHolidays,
        ];

        return view('manage_time::timekeeping.timekeeping_detail', $params);
    }

    /**
     * Export late minutes of every employees in time keeping
     *
     * @param int $timekeepingTableId
     *
     * @return Response download xlsx file
     */
    public function exportLateMinutes($timekeepingTableId)
    {
        $timeKeepingTable = TimekeepingTable::getTimekeepingTable($timekeepingTableId);
        if (!$timeKeepingTable) {
            return redirect()->back->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $team = Team::getTeamById($timeKeepingTable->team_id);
        $teamCodePre = Team::getTeamCodePrefix($team->code);

        $objTimeCons = new ManageTimeConst();
        $moneyBlock = $objTimeCons->getFinesBlockBranch($teamCodePre);

        $dataExcel = [];
        // Add header
        $dataExcel[0] = [
            Lang::get('manage_time::view.Employee code'),
            Lang::get('manage_time::view.Employee fullname'),
            Lang::get('manage_time::view.Late minutes'),
        ];
        $isTeamCodeJapan = ManageTimeCommon::isTeamCodeJapan($teamCodePre);
        if ($isTeamCodeJapan) {
            $dataExcel[0][] = Lang::get('manage_time::view.Early minutes');
            $headCells = 'A1:D1';
            $itemCells = 'A2:D2';
            $autoSize = ['A', 'B', 'C', 'D'];
        } else {
            $dataExcel[0][] = Lang::get('manage_time::view.Fines late in');
            $headCells = 'A1:D1';
            $itemCells = 'A2:D2';
            $autoSize = ['A', 'B', 'C', 'D'];
        }

        $lateMinutesData = Timekeeping::getLateMinutes($timekeepingTableId);
        foreach ($lateMinutesData as $index => $itemLate) {
            $dataExcel[$index + 1] = [
                $itemLate->employee_code,
                $itemLate->employee_name,
            ];
            if ($isTeamCodeJapan) {
                $dataExcel[$index + 1][] = $itemLate->total_late_shift;
                $dataExcel[$index + 1][] = $itemLate->total_early_shift;
            } else {
                $dataExcel[$index + 1][] = $itemLate->total_late_start_shift;
                $dataExcel[$index + 1][] = number_format((int)($itemLate->total_late_start_shift / ManageTimeConst::TIME_LATE_IN_PER_BLOCK) * $moneyBlock);
            }
            if ($dataExcel[$index + 1][count($dataExcel[$index + 1]) - 1] == 0) {
                unset($dataExcel[$index + 1]);
            }
        }

        if ($isTeamCodeJapan) {
            $headCells = 'A1:D1';
            $itemCells = 'A2:D2';
            $autoSize = ['A', 'B', 'C', 'D'];
        } else {
            $headCells = 'A1:D1';
            $itemCells = 'A2:D2';
            $autoSize = ['A', 'B', 'C', 'D'];
        }

        Excel::create(Lang::get('manage_time::view.Timekeeping late minutes month :month', ['month' => $timeKeepingTable->month]), function($excel) use($dataExcel, $headCells, $itemCells, $autoSize) {
            $excel->sheet('Sheet 1', function($sheet) use($dataExcel, $headCells, $itemCells, $autoSize) {
                $sheet->fromArray($dataExcel, null, 'A1', true, false);
                $sheet->cells($headCells, function($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $countData = count($dataExcel);
                $sheet->cells("{$itemCells}{$countData}", function($cells) {
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $sheet->setHeight([
                    1 => 40,
                ]);
                $sheet->setAutoSize($autoSize);

                $sheet->setBorder($headCells, 'thin');
            });
            $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
        })->download('xlsx');
    }

    /**
     * Export time keeping detail
     *
     * @param int $timekeepingTableId
     *
     * @return file download xlsx
     */
    public function exportTimekeepingDetail($timekeepingTableId)
    {
        $timeKeepingTable = TimekeepingTable::getTimekeepingTable($timekeepingTableId);
        if (!$timeKeepingTable) {
            return redirect()->back->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $startDate = Carbon::parse($timeKeepingTable->start_date);
        $endDate = Carbon::parse($timeKeepingTable->end_date);
        $datesTimekeeping = ManageTimeCommon::getDateRange($startDate, $endDate);

        $filter = Form::getFilterData(null, null, route('manage_time::timekeeping.timekeeping-detail', $timekeepingTableId) . '/');
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $collectionModel = Timekeeping::getEmployeesToTimekeeping($timekeepingTableId, $dataFilter, true);

        $empIdInList = $collectionModel->lists('employee_id')->toArray();
        $timeKeepingList = Timekeeping::join('employees', 'employees.id', '=', 'manage_time_timekeepings.employee_id')
            ->join('employee_works', 'employee_works.employee_id', '=', 'employees.id')
            ->join('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
            ->where('timekeeping_table_id', $timekeepingTableId)
            ->whereIn('manage_time_timekeepings.employee_id', $empIdInList)
            ->select(
                'manage_time_timekeepings.*',
                DB::raw('date(employees.trial_date) as trial_date'),
                DB::raw('date(employees.offcial_date) as offcial_date'),
                'manage_time_timekeeping_tables.type as contract_type',
                'manage_time_timekeeping_tables.date_max_import'
            )
            ->get();

        $dataKeeping = [];
        $arrTeamPrefix = [];
        $arrCompensationDays =[];
        $arrHolidays = [];
        foreach ($timeKeepingList as $keepingItem) {
            $dataKeeping[$keepingItem->employee_id][$keepingItem->timekeeping_date] = $keepingItem;

            if (!array_key_exists($keepingItem->employee_id, $arrTeamPrefix)) {
                $teamCodePrefix = Team::getTeamCodePrefix($keepingItem->code);
                $arrTeamPrefix[$keepingItem->employee_id] = $teamCodePrefix;
            }
            if (!array_key_exists($teamCodePrefix, $arrCompensationDays)) {
                $arrCompensationDays[$teamCodePrefix] = CoreConfigData::getCompensatoryDays($teamCodePrefix);
            }
            if (!array_key_exists($teamCodePrefix, $arrHolidays)) {
                $arrHolidays[$teamCodePrefix] = CoreConfigData::getHolidayTeam($teamCodePrefix);
            }
        }

        $dataExcel = [];
        // Add header
        $dataExcel[0] = [
            Lang::get('manage_time::view.Employee code'),
            Lang::get('manage_time::view.Employee fullname'),
            Lang::get('manage_time::view.Department'),
        ];
        foreach ($datesTimekeeping as $date) {
            $dataExcel[0][] = $date->format('d/m');
        }
        // Add body
        foreach ($collectionModel as $index => $item) {
            $dataExcel[$index + 1] = [
                $item->employee_code,
                $item->employee_name,
                $item->role_name,
            ];
            $teamCodePrefix = $arrTeamPrefix[$item->employee_id];
            $compensationDays = $arrCompensationDays[$teamCodePrefix];
            foreach ($datesTimekeeping as $date) {
                if (isset($dataKeeping[$item->employee_id])) {
                    $dataItem = $dataKeeping[$item->employee_id][date('Y-m-d', strtotime($date))];
                    $timekeepingSign = ManageTimeCommon::getTimekeepingSign($dataItem, $teamCodePrefix, $compensationDays, $arrHolidays[$teamCodePrefix]);
                    $dataExcel[$index + 1][] = $timekeepingSign[0];
                }
            }
        }
        Excel::create(Lang::get('manage_time::view.Timekeeping detail month :month', ['month' => $timeKeepingTable->month]), function($excel) use($dataExcel) {
            $excel->sheet('Sheet 1', function($sheet) use($dataExcel) {
                $sheet->fromArray($dataExcel, null, 'A1', true, false);
                $sheet->cells('A1:AH1', function($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $countData = count($dataExcel);
                $sheet->cells("A2:AH{$countData}", function($cells) {
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $sheet->setHeight([
                    1 => 40,
                ]);
                $sheet->setAutoSize([
                    'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'Q', 'R', 'S', 'T'
                ]);

                $sheet->setBorder('A1:AH1', 'thin');
            });
            $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
        })->download('xlsx');
    }

    /**
     * [getTimekeepingAggregate: view timekeeping aggregate]
     * @param  int|null $timekeepingTableId
     * @return [view]
     */
    public static function getTimekeepingAggregate($timekeepingTableId = null)
    {
        Breadcrumb::add('Admin');
        Breadcrumb::add('Timekeeping', route('manage_time::timekeeping.manage-timekeeping-table'));

        if (!TimekeepingPermission::isScopeOfTeam() && !TimekeepingPermission::isScopeOfCompany() && !TimekeepingPermission::isPermissionView()) {
            View::viewErrorPermission();
        }
        $teamIdAllowCreate = TimekeepingPermission::getTeamIdAllowCreateView();
        $yearCurrent = Carbon::now()->year;
        if (!$timekeepingTableId) {
            $timeKeepingTableDefault = TimekeepingTable::select('id', 'timekeeping_table_name', 'team_id', 'start_date', 'end_date')
                ->where('year', $yearCurrent)
                ->whereIn('team_id', $teamIdAllowCreate)
                ->orderBy('month', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();
            if ($timeKeepingTableDefault) {
                return redirect()->route('manage_time::timekeeping.timekeeping-aggregate', ['timekeepingTableId' => $timeKeepingTableDefault->id]);
            } else {
                $messages = [
                    'errors'=> [
                        Lang::get('manage_time::message.You have not created timekeeping table in :year', ['year' => $yearCurrent]),
                    ]
                ];
                return redirect()->route('manage_time::timekeeping.manage-timekeeping-table')->with('messages', $messages);
            }
        }
        $timeKeepingTable = TimekeepingTable::getTimekeepingTable($timekeepingTableId);

        if (!$timeKeepingTable) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
            return redirect()->route('manage_time::timekeeping.manage-timekeeping-table')->with('messages', $messages);
        }
        if (!in_array($timeKeepingTable->team_id, $teamIdAllowCreate)) {
            View::viewErrorPermission();
        }
        $yearFilter = $timeKeepingTable->year;
        if (!$yearFilter) {
            $yearFilter = Carbon::now()->format('Y');
        }
        ViewTimeKeeping::cronRelatedPerson();
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $collectionModel = TimekeepingAggregate::getTimekeepingAggregateCol($timekeepingTableId, false, $dataFilter);
        $officialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_official_salary");
        $trialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_trial_salary");
        $route = route('manage_time::timekeeping.timekeeping-aggregate', $timekeepingTableId) . '/';
        $collectionModel = TimekeepingAggregate::filterTotalSalary($route, $collectionModel, $timeKeepingTable, $officialSalaryFilter, $trialSalaryFilter);

        $timekeepingTablesList = TimekeepingTable::getTimekeepingTablesList($teamIdAllowCreate, $yearFilter);
        $isTeamCodeJapan = ManageTimeCommon::isTeamCodeJapan(Team::getTeamCodePrefix($timeKeepingTable->team_code));
        $keyFilter = !$isTeamCodeJapan ? ManageTimeCommon::keysFilter() : ManageTimeCommon::keysFilterJapan();

        $params = [
            'collectionModel'  => $collectionModel,
            'timeKeepingTable' => $timeKeepingTable,
            'yearFilter' => $yearFilter,
            'timekeepingTablesList' => $timekeepingTablesList,
            'empIdInList' => $collectionModel->lists('employee_id')->toArray(),
            'optionsCompare' => ManageTimeCommon::optionsCompare(),
            'isTeamCodeJapan' => $isTeamCodeJapan,
            'keyFilter' => $keyFilter,
        ];

        return view('manage_time::timekeeping.timekeeping_aggregate', $params);
    }

    /**
     * [postUploadTimekeeping: upload file timekeeping]
     * @param  Request $request
     * @return [notification]
     */
    public function postUploadTimekeeping(Request $request)
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
        if (!in_array($extension, ['csv'])) {
            return redirect()->back()->withErrors(Lang::get('manage_time::view.Only allow file csv'));
        }
        try {
            $timekeepingTableId = $request->timekeeping_table_id;
            $timekeepingTable = TimekeepingTable::find($timekeepingTableId);
            // Check if has not timekeeping table
            if (!$timekeepingTable) {
                redirect()->back()->with('messages', ['errors' => [Lang::get('manage_time::message.Not exist timekeeping table')]]);
            }
            $fileName = 'Bang_cham_cong_thang_' . $timekeepingTable->month . '_nam_' . $timekeepingTable->year . '_' . $timekeepingTable->team_id . '_' . $timekeepingTableId;
            if (ManageTimeView::storageTimekeepingFile($file, $timekeepingTableId, $fileName)) {
                $messages = [
                    'success' => [
                        Lang::get('manage_time::message.Upload successful, system will update data within minutes', ['minutes' => 10]),
                    ]
                ];
            } else {
                $messages = [
                    'errors' => [
                        Lang::get('manage_time::message.Processing upload file, please try again'),
                    ]
                ];
            }
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            $message = $ex->getMessage();
            if ($ex->getCode() != 422) {
                $message = trans('project::me.Save error, please try again laster');
            }
            return redirect()->back()->with('messages', [
                'errors' => [$message]
            ]);
        }
    }

    /**
     * Create a file with name is id of timekeeping table updated
     *
     * @param Request $request
     *
     * @return Response redirect
     */
    public static function getDataFromRelatedModules(Request $request)
    {
        try {
            $dataRequest = $request->only('timekeeping_table_id', 'empids', 'start_date', 'end_date');
            if (empty($dataRequest['start_date']) || empty($dataRequest['end_date'])) {
                return redirect()->back()->with('messages', [
                    'errors' => ['không để trống ngày bắt đầu và kết thúc']
                ]);
            }
            if ($dataRequest['start_date'] > $dataRequest['end_date']) {
                return redirect()->back()->with('messages', [
                    'errors' => ['Ngày bắt đầu phải nhỏ hơn ngày kết thúc']
                ]);
            }
            if ($dataRequest['empids'] == null) {
                $dataRequest['empids'] = Timekeeping::getEmployeesIdOfTimekeeping($dataRequest['timekeeping_table_id']);
            }
            if (!Storage::exists(ManageTimeView::FOLDER_UPLOAD_RELATED)) {
                Storage::makeDirectory(ManageTimeView::FOLDER_UPLOAD_RELATED, ManageTimeView::ACCESS_FOLDER);
            }
            @chmod(storage_path('app/' . ManageTimeView::FOLDER_UPLOAD_RELATED), ManageTimeView::ACCESS_FOLDER);
            if (!Storage::exists('process')) {
                Storage::makeDirectory('process');
            }

            @chmod(storage_path('app/process'), ManageTimeView::ACCESS_FOLDER);
            $dataTmp = array_chunk($dataRequest['empids'], Timekeeping::CHUNK_NUMBER);
            foreach ($dataTmp as $empIdsTk) {
                ViewTimeKeeping::createFileRelateTk($dataRequest, $empIdsTk);
            }
            

            $messages = [
                'success' => [
                    Lang::get('manage_time::view.Data will be updated within 5 minutes'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            return redirect()->back()->with('messages', [
                'errors' => ['Lỗi. Vui lòng liên hệ với người quản trị để được hỗ trợ.']
            ]);
        }
    }

    public function updateDayOff(Request $request)
    {
        set_time_limit(240);
        $dataRequest = $request->only('timekeeping_table_id');

        DB::beginTransaction();
        try {
            $timeKeepingTable = TimekeepingTable::select('id', 'timekeeping_table_name', 'team_id', 'start_date', 'end_date', 'year', 'month', 'type')
                ->where('id', $dataRequest['timekeeping_table_id'])
                ->first();
            if (!$timeKeepingTable) {
                $messages = [
                    'errors'=> [
                        Lang::get('team::messages.Not found item.'),
                    ]
                ];
                return redirect()->back()->with('messages', $messages);
            }
            $typesOffcial = \Rikkei\Resource\View\getOptions::typeEmployeeOfficial();
            if (!in_array($timeKeepingTable->type, $typesOffcial)) {
                $messages = [
                    'errors'=> [
                        Lang::get('team::messages.Not found item.'),
                    ]
                ];
                return redirect()->back()->with('messages', $messages);
            }

            $tkTableId = $timeKeepingTable->id;
            $arrLeavedays = [];
            $dataInsertOrUpdate = [];
            $now = Carbon::now();

            $objTKAggregate = new TimekeepingAggregate();
            $tkAggregates = $objTKAggregate->getOTTimekeepingAggregatesById($tkTableId);
            if (!count($tkAggregates)) {
                $messages = [
                    'success'=> [
                        'Trong tháng không có nhân viên nào OT'
                    ]
                ];
                return redirect()->back()->with('messages', $messages);
            }
            $arrTkAggregates = $tkAggregates->keyBy('employee_id')->toArray();
            $empId = $tkAggregates->lists('employee_id')->toArray();
            $leaveDays = LeaveDay::select('employee_id', 'day_ot')->whereIn('employee_id', $empId)->withTrashed()->get();
            if (count($leaveDays)) {
                $arrLeavedays = $leaveDays->keyBy('employee_id')->toArray();
            }

            foreach($arrTkAggregates as $empId => $item) {
                $otWeekend = $item['total_official_ot_weekends'] +  $item['total_trial_ot_weekends'];
                $otHoliday = $item['total_official_ot_holidays'] +  $item['total_trial_ot_holidays'];

                $otWeekend = ($otWeekend * 50) / (100 * 8);
                $otHoliday = ($otHoliday * 150) / (100 * 8);
                $total = round($otWeekend + $otHoliday, 2);
                $timeAddLeaveDay = round($total, 2); //sẽ lệch so với phần trên một chút vì phần trên làm tròn từng ngày ot
                if ($timeAddLeaveDay < 0) {
                    $timeAddLeaveDay = 0;
                }

                if (isset($arrLeavedays[$empId])) {
                    $dataInsertOrUpdate['update'][$empId] = [
                        'employee_id' => $empId,
                        'day_ot' => $timeAddLeaveDay,
                        'leave_day_ot' => $arrLeavedays[$empId]['day_ot'],
                    ];
                } else {
                    $dataInsertOrUpdate['insert'][$empId] = [
                        'employee_id' => $empId,
                        'day_ot' => $timeAddLeaveDay,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if (count($dataInsertOrUpdate) && !empty($request->update_day_off)) {
                ManageTimeView::updateDayOff($tkTableId, $dataInsertOrUpdate);
            }

            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Update day off success')
                ]
            ];

            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => ['Error. Please try again.']]);
        }
    }

    /**
     * [updateTimekeepingAggregate: update timekeeping aggregate table]
     * @param  Request $request
     * @return [type]
     */
    public static function updateTimekeepingAggregate(Request $request, $empIds = [])
    {
        $dataRequest = $request->only('timekeeping_table_id');;
        DB::beginTransaction();
        try {
            $timeKeepingTable = TimekeepingTable::select('id', 'timekeeping_table_name', 'team_id', 'start_date', 'end_date', 'type')
                ->where('id', $dataRequest['timekeeping_table_id'])
                ->first();
            if (!$timeKeepingTable) {
                $messages = [
                    'errors'=> [
                        Lang::get('team::messages.Not found item.'),
                    ]
                ];
                return redirect()->back()->with('messages', $messages);
            }
            if (!empty($request->team_code)) {
                $teamCodePre = $request->team_code;
            } else {
                $team = Team::getTeamById($timeKeepingTable->team_id);
                $teamCodePre = Team::getTeamCodePrefix($team->code);
                $teamCodePre = Team::changeTeam($teamCodePre);
            }
            $timekeepingAggregate = Timekeeping::getTimekeepingAggregate($timeKeepingTable->id, $teamCodePre, $empIds);
            if (count($timekeepingAggregate)) {
                $now = Carbon::now();
                $employeeIds = [];
                $dataUpdate = [];
                if ($timeKeepingTable->start_date && $timeKeepingTable->end_date) {
                    $timeKeepingTable->start_date = Carbon::parse($timeKeepingTable->start_date);
                    $timeKeepingTable->end_date = Carbon::parse($timeKeepingTable->end_date);
                } else {
                    $timeKeepingTable->start_date = null;
                    $timeKeepingTable->end_date = null;
                }
                $compensation = CoreConfigData::getComAndLeaveDays($teamCodePre);
                $compInTime = ManageTimeCommon::getCompInTime(
                    $timeKeepingTable,
                    $compensation,
                    'com'
                );
                $leavComInTime = ManageTimeCommon::getCompInTime(
                    $timeKeepingTable,
                    $compensation,
                    'lea'
                );

                $empIds = [];
                foreach ($timekeepingAggregate as $item) {
                    $arrayWork = [];
                    $cpCompenstaion = $compensation;
                    $cpCompInTime = $compInTime;
                    $cpleavComInTime = $leavComInTime;
                    $empIds[] = $item['employee_id'];

                    $item["join_date"] = Carbon::parse($item["join_date"])->format("Y-m-d");
                    if ($timeKeepingTable->type == TimekeepingTable::OFFICIAL) {
                        foreach ($cpCompenstaion['com'] as $key => $compen) {
                            if (strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["join_date"])
                                || ((($item["trial_date"] && strtotime($compen) <= strtotime($item["trial_date"]))
                                || (!$item["trial_date"] && strtotime($compen) <= strtotime($item["offcial_date"]))
                                || (($item["trial_date"] && strtotime($compen) >= strtotime($item["trial_date"]) && strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["trial_date"]))
                                    || (!$item["trial_date"] && strtotime($compen) >= strtotime($item["offcial_date"]) && strtotime($cpCompenstaion['lea'][$key]) < strtotime($item["offcial_date"]))
                                    && strtotime($cpCompenstaion['lea'][$key]) >= strtotime($item["join_date"])))
                                && strtotime($compen) >= strtotime($timeKeepingTable->start_date)
                                && strtotime($compen) <= strtotime($timeKeepingTable->end_date))) {
                                unset($cpCompenstaion['com'][$key]);
                                unset($cpCompenstaion['lea'][$key]);
                                $cpCompInTime = ManageTimeCommon::getCompInTime($timeKeepingTable, $cpCompenstaion, 'com');
                                $cpleavComInTime = ManageTimeCommon::getCompInTime($timeKeepingTable, $cpCompenstaion, 'lea');
                            }
                        }
                    } elseif ($timeKeepingTable->type == TimekeepingTable::TRIAL) {
                        $cpCompInTime = ['check' => [], 'big' => []];
                        $cpleavComInTime = ['check' => [], 'big' => []];
                    } else {
                        //do not some thing
                    }

                    $itemCom = ManageTimeCommon::calComDayEmpInTime($timeKeepingTable, $item['offcial_date'], $item['join_date'], $item['leave_date'], $cpCompInTime, true);
                    $itemLea = ManageTimeCommon::calComDayEmpInTime($timeKeepingTable, $item['offcial_date'], $item['join_date'], $item['leave_date'], $cpleavComInTime);
                    $itemCom['number_com_tri'] = $itemCom['number_com_tri'] - $itemLea['number_com_tri'];
                    $itemCom['number_com_off'] = $itemCom['number_com_off'] - $itemLea['number_com_off'];
                    $item['updated_at'] = $now;
                    $item = array_merge($itemCom, $item);

                    $item = (object) $item;
                    $daysOffInTimeBusiness = ManageTimeView::daysOffInTimeBusiness($item, $timeKeepingTable, $teamCodePre);
                    $totalWorkingToSalary = ManageTimeView::totalWorkingDayObject($item, $daysOffInTimeBusiness);
                    $item = (array) $item;

                    $arrayWork= [
                        'total_working_officail' => $totalWorkingToSalary['offcial'],
                        'total_working_trial' => $totalWorkingToSalary['trial'],
                    ];
                    $item = array_merge($arrayWork, $item);
                    unset($item['offcial_date']);
                    unset($item['join_date']);
                    unset($item['leave_date']);
                    unset($item['trial_date']);

                    TimekeepingAggregate::where('timekeeping_table_id', $timeKeepingTable->id)
                        ->where('employee_id', $item['employee_id'])
                        ->update($item);
                }
                // fined to work late
                FinesMoney::insertFinesWorkLate($timeKeepingTable->start_date, $empIds);
            }
            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Update timekeeping aggregate success')
                ]
            ];
            return;
            // return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [$ex->getMessage()]]);
        }
    }

    /**
     * Export timekeeping aggregate to excel
     * @param  [int] $timekeepingTableId
     * @return [file]
     */
    public static function exportTimkeepingAggregate($timekeepingTableId)
    {
        $timeKeepingTable = TimekeepingTable::getTimekeepingTable($timekeepingTableId);

        if (!$timeKeepingTable) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
            return redirect()->route('manage_time::timekeeping.manage-timekeeping-table')->with('messages', $messages);
        }
        $team = Team::getTeamById($timeKeepingTable->team_id);
        $teamCodePre = Team::getTeamCodePrefix($team->code);
        $collectionModel = TimekeepingAggregate::getTimekeepingAggregateCol($timekeepingTableId, true);
        $route = route('manage_time::timekeeping.timekeeping-aggregate', $timekeepingTableId) . '/';
        $officialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_official_salary", $route);
        $trialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_trial_salary", $route);

        $collectionModel = TimekeepingAggregate::filterTotalSalary($route, $collectionModel, $timeKeepingTable, $officialSalaryFilter, $trialSalaryFilter, true);

        if(!count($collectionModel)) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::view.No data to export'),
                ]
            ];
            return redirect()->route('manage_time::timekeeping.timekeeping-aggregate', ['id' => $timeKeepingTable->id])->with('messages', $messages);
        }

        $data = [];
        $data[0] = [
            Lang::get('manage_time::view.Employee code'),
            Lang::get('manage_time::view.Employee fullname'),
            Lang::get('manage_time::view.The total of official working days'),
            Lang::get('manage_time::view.The total of trial working days'),
            Lang::get('manage_time::view.Overtime on weekdays'),
            Lang::get('manage_time::view.Overtime on weekends'),
            Lang::get('manage_time::view.Overtime on holidays'),
            Lang::get('manage_time::view.Total number of late in'),
            Lang::get('manage_time::view.Total number of early out'),
            Lang::get('manage_time::view.CT'),
            Lang::get('manage_time::view.P'),
            Lang::get('manage_time::view.KL'),
            Lang::get('manage_time::view.BS'),
            Lang::get('manage_time::view.L'),
        ];

        $isTeamCodeJapan = ManageTimeCommon::isTeamCodeJapan($teamCodePre);
        if ($isTeamCodeJapan) {
            $data[0][] = Lang::get('manage_time::view.M');
            $data[0][] = Lang::get('manage_time::view.S');
            $headCells = 'A1:V1';
            $itemCells = 'C2:V';
            $autoSize = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'Q', 'R', 'S', 'T', 'U', 'V'];
        } else {
            $data[0][] = Lang::get('manage_time::view.M1');
            $headCells = 'A1:V1';
            $itemCells = 'C2:V';
            $autoSize = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'Q', 'R', 'S', 'T', 'U', 'V'];
        }

        $data[0] = array_merge($data[0], [
            Lang::get('manage_time::view.OTKL'),
            Lang::get('manage_time::view.number days compensation'),
            Lang::get('manage_time::view.OT of official working'),
            Lang::get('manage_time::view.OT of trial working'),
            Lang::get('manage_time::view.The total of official working days to salary'),
            Lang::get('manage_time::view.The total of trial working days to salary'),
            Lang::get('manage_time::view.Total basic salary'),
        ]);

        foreach ($collectionModel as $key => $item) {
            $arrayTemp = [
                $item->employee_code,
                $item->employee_name,
                $item->total_official_working_days,
                $item->total_trial_working_days,
                $item->totalOTWeekdays,
                $item->totalOTWeekends,
                $item->totalOTHolidays,
                $item->total_number_late_in,
                $item->total_number_early_out,
                $item->totalRegisterBusinessTrip,
                $item->totalLeaveDayHasSalary,
                $item->total_leave_day_no_salary,
                $item->totalRegisterSupplement,
                $item->totalHoliday,
            ];

            if ($isTeamCodeJapan) {
                $arrayTemp[] = $item->total_late_start_shift + $item->total_late_mid_shift;
                $arrayTemp[] = $item->total_early_mid_shift + $item->total_early_end_shift;
            } else {
                $arrayTemp[] = $item->total_late_start_shift;
            }
            $arrayTemp = array_merge($arrayTemp, [
                $item->total_ot_no_salary,
                $item->total_num_com,
                $item->totalOTOfficial,
                $item->totalOTTrial,
                $item->total_working_officail,
                $item->total_working_trial,
                $item->total_official_leave_basic_salary,
            ]);
            $data[] = $arrayTemp;
        }

        Excel::create(Lang::get('manage_time::view.Timekeeping aggregate month :month', ['month' => $timeKeepingTable->month]), function($excel) use($data, $headCells, $itemCells, $autoSize) {
            $excel->sheet('Sheet 1', function($sheet) use($data, $headCells, $itemCells, $autoSize) {
                $sheet->fromArray($data, null, 'A1', true, false);
                $sheet->cells($headCells, function($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $countData = count($data);
                $sheet->cells("{$itemCells}{$countData}", function($cells) {
                    $cells->setAlignment('right');
                    $cells->setValignment('center');
                });
                $sheet->cells("A2:B{$countData}", function($cells) {
                    $cells->setAlignment('left');
                    $cells->setValignment('center');
                });

                $sheet->setHeight([
                    1 => 40,
                ]);
                $sheet->setAutoSize($autoSize);
                $sheet->setWidth([
                    'J' => 10,
                    'K' => 10,
                    'L' => 10,
                    'M' => 10,
                    'N' => 10,
                    'O' => 10,
                    'P' => 10,
                ]);

                $sheet->setBorder($headCells, 'thin');
            });
            $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
        })->download('xlsx');
    }

    /*
     * Delete timekeeping table
     */
    public static function deleteTimekeepingTable()
    {
        DB::beginTransaction();
        try {
            $timekeepingTableId = Input::get('id');
            $timekeepingTable = TimekeepingTable::find($timekeepingTableId);
            if (!$timekeepingTable) {
                return redirect()->back()->withErrors(Lang::get('core::message.Not found item'));
            }
            LeaveDayHistory::where('timekeeping_table_id', $timekeepingTableId)->delete();
            $timekeepingTable->delete();
            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('core::message.Delete data success')
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [$ex->getMessage()]]);
        }
    }

    public static function getHour($time)
    {
        $arrayTime = explode(':', $time);
        return [
            'hour' => (int)$arrayTime[0],
            'minute' => (int)$arrayTime[1],
        ];
    }

    /**
     * Get diff time of time keeping
     *
     * @param Carbon $dateStart date start of register record
     * @param Carbon $dateEnd date end of register record
     * @param Carbon $dateTimekeeping date of time keeping is calculating
     * @param string $teamCodePrefix team code of
     * @param Collection $timeSettingOfEmp
     *
     * @return float|int
     */
    public static function getDiffTimesOfTimeKeeping($dateStart, $dateEnd, $dateTimekeeping, $teamCodePrefix, $timeSettingOfEmp)
    {
        if ($dateStart->format('Y-m-d') < $dateTimekeeping->format('Y-m-d') && $dateTimekeeping->format('Y-m-d') < $dateEnd->format('Y-m-d')) {
            return [
                'diff' => 1,
                'session' => ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY,
            ];
        } elseif ($dateStart->format('Y-m-d') < $dateTimekeeping->format('Y-m-d') && $dateTimekeeping->format('Y-m-d') == $dateEnd->format('Y-m-d')) {
            if (!ManageTimeView::isMorningTime($dateEnd->hour, false)) {
                return [
                    'diff' => 1,
                    'session' => ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY,
                ];
            } else {
                return [
                    'diff' =>static::getDiffTimes($timeSettingOfEmp['morningInSetting']->hour, $timeSettingOfEmp['morningOutSetting']->hour, $timeSettingOfEmp['morningInSetting']->minute, $timeSettingOfEmp['morningOutSetting']->minute, $teamCodePrefix, $timeSettingOfEmp),
                    'session' => ManageTimeConst::HAS_LEAVE_DAY_MORNING,
                ];
            }
        } elseif ($dateStart->format('Y-m-d') == $dateTimekeeping->format('Y-m-d') && $dateTimekeeping->format('Y-m-d') < $dateEnd->format('Y-m-d')) {
            if (ManageTimeView::isMorningTime($dateStart->hour)) {
                return [
                    'diff' => 1,
                    'session' => ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY,
                ];
            } else {
                return [
                    'diff' => static::getDiffTimes($timeSettingOfEmp['afternoonInSetting']->hour, $timeSettingOfEmp['afternoonOutSetting']->hour, $timeSettingOfEmp['afternoonInSetting']->minute, $timeSettingOfEmp['afternoonOutSetting']->minute, $teamCodePrefix, $timeSettingOfEmp),
                    'session' => ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON,
                ];
            }
        } else {
            if (ManageTimeView::isMorningTime($dateStart->hour)) {
                $dateStart->hour($timeSettingOfEmp['morningInSetting']->hour);
                $dateStart->minute($timeSettingOfEmp['morningInSetting']->minute);
            } else {
                $dateStart->hour($timeSettingOfEmp['afternoonInSetting']->hour);
                $dateStart->minute($timeSettingOfEmp['afternoonInSetting']->minute);
            }
            if (ManageTimeView::isMorningTime($dateEnd->hour, false)) {
                $dateEnd->hour($timeSettingOfEmp['morningOutSetting']->hour);
                $dateEnd->minute($timeSettingOfEmp['morningOutSetting']->minute);
            } else {
                $dateEnd->hour($timeSettingOfEmp['afternoonOutSetting']->hour);
                $dateEnd->minute($timeSettingOfEmp['afternoonOutSetting']->minute);
            }

            $diff = static::getDiffTimes($dateStart->hour, $dateEnd->hour, $dateStart->minute, $dateEnd->minute, $teamCodePrefix, $timeSettingOfEmp);
            if ($diff >= 1) {
                $session = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
            } elseif (ManageTimeView::isMorningTime($dateEnd->hour, false)) {
                $session = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
            } else {
                $session = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
            }
            return [
                'diff' => $diff,
                'session' => $session,
            ];
        }
    }

    /**
     * [getDiffTimes: get timekeeping time on timekeeping date]
     * @param  [int] $hourStart
     * @param  [int] $hourEnd
     * @param  [int] $minuteStart
     * @param  [int] $minuteEnd
     * @return [float]
     */
    public static function getDiffTimes($hourStart, $hourEnd, $minuteStart, $minuteEnd, $teamCodePrefix, $workingTime)
    {
        $startDate = new DateTime();
        $startDate->setTime($hourStart, $minuteStart);
        $endDate = new DateTime();
        $endDate->setTime($hourEnd, $minuteEnd);
        $diffTime = $startDate->diff($endDate);

        $lunchBreak = ManageTimeView::getLunchBreak($hourStart, $hourEnd, $workingTime);

        return round(($diffTime->h * 60 + $diffTime->i - $lunchBreak) / ManageTimeView::getHoursWork($workingTime), 2);
    }

    /*
     * Ajax get timekeeping table
     */
    public function ajaxGetTimekepingTables(Request $request)
    {
        $year = $request->year;
        CookieCore::setRaw('filter_year_timekeeping_table', $year);
        $teamIdAllowCreate = TimekeepingPermission::getTeamIdAllowCreateView();
        $params = [
            'timekeepingTablesList' => TimekeepingTable::getTimekeepingTablesList($teamIdAllowCreate, $year),
            'timekeepingTableId' => $request->timekeepingTableId,
        ];
        if ($request->type == ManageTimeConst::TYPE_AJAX_GET_TIMEKEEPING_TABLE) {
            $view = 'manage_time::timekeeping.include.option_filter_timekeeping_table';
        } else {
            $view = 'manage_time::timekeeping.include.option_filter_timekeeping_aggregate';
        }
        $html = view($view)->with($params)->render();

        return response()->json(['html' => $html]);
    }

    /**
     * Add employee into timekeeping
     *
     * @param Request $request
     */
    public function addEmpToTimekeeping(Request $request)
    {
        if (!TimekeepingPermission::isPermission()) {
            return redirect()->back()->with('messages', ['errors' => [Lang::get('core::message.You don\'t have access')]]);
        }
        $employeeIds = $request->employee;
        $timekeepingTableId = $request->timekeeping_table_id;

        $timekeepingTable = TimekeepingTable::find($timekeepingTableId);
        if (!$timekeepingTable) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        }
        $teamOfTimekeeping = Team::find($timekeepingTable->team_id);
        $teamCodePrefix = Team::getTeamCodePrefix($teamOfTimekeeping->code);

        $employeeTimekeeping = Employee::getEmpByIds($employeeIds);

        $dataExist = ManageTimeView::findEmpExistInTimekeeping($timekeepingTableId, $employeeTimekeeping);

        if (count($dataExist)) {
            return redirect()->back()->with('messages', ['errors' => $dataExist]);
        }

        DB::beginTransaction();
        try {
            $typesOffcial = getOptions::typeEmployeeOfficial();
            $dataInsertTimekeeping = [];
            $dataTimekeepingAggregate = [];
            $dataInsertTimekeepingAggregate = [];
            $dataTimekeeping['timekeeping_table_id'] = $timekeepingTable->id;
            $dataTimekeepingAggregate['timekeeping_table_id'] = $timekeepingTable->id;

            $dates = [];
            $startDate = $timekeepingTable->start_date;
            $endDate = $timekeepingTable->end_date;
            while (strtotime($startDate) <= strtotime($endDate)) {
                $dates[] = Carbon::parse($startDate)->toDateString();
                $startDate = date ("Y-m-d", strtotime("+1 day", strtotime($startDate)));
            }
            foreach ($employeeTimekeeping as $emp) {
                $trialDate = $emp->trial_date;
                $offcialDate = $emp->offcial_date;
                $now = Carbon::now();
                $dataTimekeeping['employee_id'] = $emp->id;
                $dataTimekeeping['created_at'] = $now->format('Y-m-d H:i:s');
                $dataTimekeeping['updated_at'] = $now->format('Y-m-d H:i:s');
                $dataTimekeepingAggregate['employee_id'] = $emp->id;
                $dataTimekeepingAggregate['created_at'] = $now->format('Y-m-d H:i:s');
                $dataTimekeepingAggregate['updated_at'] = $now->format('Y-m-d H:i:s');
                foreach ($dates as $value) {
                    $dataTimekeeping['timekeeping_date'] = $value;
                    if ($offcialDate) {
                        if (strtotime(Carbon::parse($value)->format('Y-m-d')) < strtotime(Carbon::parse($offcialDate)->format('Y-m-d'))) {
                            $dataTimekeeping['is_official'] =  0;
                        } else {
                            $dataTimekeeping['is_official'] =  1;
                        }
                    } else {
                        $dataTimekeeping['is_official'] =  0;
                    }
                    //Check holiday
                    $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
                    $isWeekend = ManageTimeCommon::isWeekend(Carbon::parse($value), $compensationDays);
                    $isHoliday = ManageTimeCommon::isHoliday(Carbon::parse($value), null, null, $teamCodePrefix);
                    if (Carbon::parse($emp->leave_date)->lt(Carbon::parse($value))) {
                        $dataTimekeeping['timekeeping'] = ManageTimeConst::NOT_WORKING;
                    } else {
                        if (!$isWeekend && $isHoliday && in_array($timekeepingTable->type, $typesOffcial)) {
                            if (((empty($trialDate) && (empty($offcialDate) || strtotime($value) < strtotime($offcialDate)))
                                || (!empty($trialDate) && strtotime($value) < strtotime($trialDate)))) {
                                $dataTimekeeping['timekeeping'] = ManageTimeConst::NOT_WORKING;
                            } else {
                                $dataTimekeeping['timekeeping'] = ManageTimeConst::HOLIDAY_TIME;
                            }

                        } else {
                            $dataTimekeeping['timekeeping'] = ManageTimeConst::NOT_WORKING;
                        }
                    }
                    $dataInsertTimekeeping[] = $dataTimekeeping;
                }
                $dataInsertTimekeepingAggregate[] = $dataTimekeepingAggregate;
            }
            if (count($dataInsertTimekeepingAggregate)) {
                TimekeepingAggregate::insert($dataInsertTimekeepingAggregate);
            }
            if (count($dataInsertTimekeeping)) {
                Timekeeping::insert($dataInsertTimekeeping);
            }
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Add employee success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [Lang::get('manage_titme::message.Error. Please try again later.')]]);
        }

    }

    public function removeEmpFromTimekeeping(Request $request)
    {
        if (!TimekeepingPermission::isPermission()) {
            return redirect()->back()->with('messages', ['errors' => [Lang::get('core::message.You don\'t have access')]]);
        }
        $employeeIds = $request->employee;
        $timekeepingTableId = $request->timekeeping_table_id;

        DB::beginTransaction();
        try {
            TimekeepingAggregate::whereIn('employee_id', $employeeIds)
                ->where('timekeeping_table_id', $timekeepingTableId)
                ->forceDelete();
            Timekeeping::whereIn('employee_id', $employeeIds)
                ->where('timekeeping_table_id', $timekeepingTableId)
                ->forceDelete();

            DB::commit();

            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Remove employee success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [Lang::get('manage_titme::message.Error. Please try again later.')]]);
        }
    }

    /**
     * Save time keeping row
     *
     * @param Request $request
     * @return Response json
     */
    public function saveRowKeeping(Request $request)
    {
        if (!TimekeepingPermission::isPermission()) {
             return Response()->json([
                'error' => 1,
                'message' => Lang::get('core::message.You don\'t have access'),
            ]);
        }
        $data = $request->data;
        $timekeepingTableId = $request->tableId;
        $empId = $request->empId;

       $timekeepingAggregate = TimekeepingAggregate::where("timekeeping_table_id", $timekeepingTableId)
            ->where("employee_id", $empId)
            ->first();

       if (!$timekeepingAggregate) {
            return Response()->json([
                'error' => 1,
                'message' => Lang::get('team::messages.Not found item.'),
            ]);
        }

        DB::beginTransaction();
        try {
            TimekeepingAggregate::where('timekeeping_table_id', $timekeepingTableId)
                ->where('employee_id', $empId)
                ->update($data);
            DB::commit();
            return Response()->json([
                'success' => 1,
            ]);
        } catch (Exception $ex) {
            Log::info($ex);
            DB::rollBack();
            return Response()->json([
                'error' => 1,
                'message' => Lang::get('manage_time::message.Error. Please try again later.'),
            ]);
        }
    }

    public function isMorningInShift($hour)
    {
        return in_array($hour, [7, 8, 9, 10, 11, 12]);
    }

    public function isMorningOutInShift($hour)
    {
        return in_array($hour, [7, 8, 9, 10, 11, 12, 13]);
    }

    /**
     * Import time in, time out of employee into table timekeeping
     *
     * @param Request $request
     *
     * @return Response redirect to timekeeping list page
     */
    public function importTimeInOut(Request $request)
    {
        try {
            $timekeepingTableId = $request->timekeeping_table_id;
            $timeKeepingTable = TimekeepingTable::getTimekeepingTable($timekeepingTableId);
            if (!$timeKeepingTable) {
                return redirect()->back->withErrors(Lang::get('team::messages.Not found item.'));
            }

            $employeesOfTable = Timekeeping::getEmployeesOfTable($timekeepingTableId);
            $month = $timeKeepingTable->month . '-' .  $timeKeepingTable->year;
            $wktLogs = WktLog::listByMonth($month, $employeesOfTable, false);
            $dataExcel = [];
            // Add header
            $dataExcel[0] = [
                'ID chấm công' ,
                'Mã N.Viên',
                'Họ tên',
                'Ngày',
                'Ca làm việc',
                'Vào lúc',
                'Ra lúc',
            ];

            foreach ($wktLogs as $key => $dates) {
                $employeesEmail = Employee::find($key)->email;
                foreach ($dates as $date => $log) {
                    $monthOfTimeKeeping = date('Y-m-01', strtotime($date));

                    $emp = Employee::getEmpByEmailsWithContracts([$employeesEmail], $monthOfTimeKeeping, [
                        'employees.email',
                        'employees.id',
                        'offcial_date',
                        'trial_date',
                        'join_date',
                        'contract_type',
                        'start_time1',
                        'end_time1',
                        'start_time2',
                        'end_time2',
                        'code',
                    ])->first();
                    if ($emp && $emp->end_time1 == null) {
                        $emp->end_time1 = '13:00';
                        $emp->start_time2 = '14:00';
                    }
                    $hourIn = $log['time_in'];
                    $hourOut = $log['time_out'];
                    $dataExcel[] = [
                        "employee_code" => $log['employee_code'],
                        "nickname" => $log['nickname'],
                        "name" => $log['name'],
                        'date' => str_replace('-', '/', $date),
                        "shift" => 'Sáng',
                        'time_in' => $hourIn <= $emp->end_time1 ? $log['time_in'] : null,
                        'time_out' => $hourOut <= $emp->start_time2 ? $log['time_out'] : null,
                    ];
                    $dataExcel[] = [
                        "employee_code" => $log['employee_code'],
                        "nickname" => $log['nickname'],
                        "name" => $log['name'],
                        'date' => str_replace('-', '/', $date),
                        "shift" => 'Chiều',
                        'time_in' => $hourIn > $emp->end_time1 ? $log['time_in'] : null,
                        'time_out' => $hourOut > $emp->start_time2 ? $log['time_out'] : null,
                    ];
                }
            }

            Excel::create($timekeepingTableId, function($excel) use($dataExcel) {
                $excel->sheet('Sheet 1', function($sheet) use($dataExcel) {
                    $sheet->fromArray($dataExcel, null, 'A1', true, false);
                });
                $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
            })->store('csv', storage_path('app/' . ManageTimeView::FOLDER_UPLOAD));

            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Import successful, system will update data within minutes', ['minutes' => 5]),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            $messages = [
                'errors' => [
                    Lang::get('manage_time::message.Import fail, please contact to admin and try again.'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        }

    }

    /**
     * Get diff time of time keeping when regsite time 1/4
     *
     * @param Carbon $dateStart date start of register record
     * @param Carbon $dateEnd date end of register record
     * @param Carbon $dateTimekeeping date of time keeping is calculating
     * @param Collection $timeSettingOfEmp
     *
     * @return array
     */
    public static function getDiffTimesOfTimeKeepingResiger($dateStart, $dateEnd, $dateTimekeeping, $timeSettingOfEmp, $timeKeepingTableId, $employeeId)
    {
        if ($dateStart->format('Y-m-d') < $dateTimekeeping->format('Y-m-d') && $dateTimekeeping->format('Y-m-d') < $dateEnd->format('Y-m-d')) {
            return [
                'diff' => 1,
                'session' => ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY,
                'timekeeping_number_register' => 0,
            ];
        } elseif ($dateStart->format('Y-m-d') < $dateTimekeeping->format('Y-m-d') && $dateTimekeeping->format('Y-m-d') == $dateEnd->format('Y-m-d')) {
            if ($dateEnd->hour == $timeSettingOfEmp['afternoonOutSetting']->hour) {
                return [
                    'diff' => 1,
                    'session' => ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY,
                    'timekeeping_number_register' => 0,
                ];
            } elseif ($dateEnd->hour == $timeSettingOfEmp['morningOutSetting']->hour) {
                return [
                    'diff' =>static::getDiffTimesRegister($timeSettingOfEmp['morningInSetting']->hour, $timeSettingOfEmp['morningOutSetting']->hour, $timeSettingOfEmp['morningInSetting']->minute, $timeSettingOfEmp['morningOutSetting']->minute, $timeSettingOfEmp),
                    'session' => ManageTimeConst::HAS_LEAVE_DAY_MORNING,
                    'timekeeping_number_register' => 0,
                ];
            } else {
                if ($dateEnd->hour == $timeSettingOfEmp['morningOutSetting']->hour ||
                    $dateEnd->hour == $timeSettingOfEmp['morningOutSetting']->hour - 2) {
                    $dateEnd = self::getTimeEndMor($dateEnd, $timeSettingOfEmp);
                } else {
                    $dateEnd = self::getTimeEndAfter($dateEnd, $timeSettingOfEmp);
                }
                $diff = static::getDiffTimesRegister($timeSettingOfEmp['morningInSetting']->hour, $dateEnd->hour, $timeSettingOfEmp['morningInSetting']->minute, $dateEnd->minute, $timeSettingOfEmp);
                $timeReset = self::getTimeResetEndEqual($timeKeepingTableId, $employeeId, $dateTimekeeping->format('Y-m-d'), $dateEnd, $timeSettingOfEmp);
                return [
                    'diff' => $diff,
                    'session' => $diff,
                    'timekeeping_number_register' => $timeReset['time'],
                    'timeLateStart' => $timeReset['timeLateStart'],
                    'timeLateMid' => $timeReset['timeLateMid'],
                ];
            }
        } elseif ($dateStart->format('Y-m-d') == $dateTimekeeping->format('Y-m-d') && $dateTimekeeping->format('Y-m-d') < $dateEnd->format('Y-m-d')) {
            if ($dateStart->hour == $timeSettingOfEmp['morningInSetting']->hour) {
                return [
                    'diff' => 1,
                    'session' => ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY,
                    'timekeeping_number_register' => 0
                ];
            } elseif ($dateStart->hour == $timeSettingOfEmp['afternoonInSetting']->hour) {
                return [
                    'diff' => static::getDiffTimesRegister($timeSettingOfEmp['afternoonInSetting']->hour, $timeSettingOfEmp['afternoonOutSetting']->hour, $timeSettingOfEmp['afternoonInSetting']->minute, $timeSettingOfEmp['afternoonOutSetting']->minute, $timeSettingOfEmp),
                    'session' => ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON,
                    'timekeeping_number_register' => 0
                ];
            } else {
                if (ManageTimeView::isMorningTime($dateStart->hour)) {
                    $dateStart = self::getTimeStartMor($dateStart, $timeSettingOfEmp);
                } else {
                    $dateStart = self::getTimeStartAfter($dateStart, $timeSettingOfEmp);
                }
                $diff = static::getDiffTimesRegister($dateStart->hour, $timeSettingOfEmp['afternoonOutSetting']->hour, $dateStart->minute, $timeSettingOfEmp['afternoonOutSetting']->minute, $timeSettingOfEmp);
                $timeReset = self::getTimeResetStartEqual($timeKeepingTableId, $employeeId, $dateTimekeeping->format('Y-m-d'), $dateStart, $timeSettingOfEmp);
                return [
                    'diff' => $diff,
                    'session' => $diff,
                    'timekeeping_number_register' => $timeReset['time'],
                    'timeLateStart' => $timeReset['timeLateStart'],
                    'timeEarlyMid' => $timeReset['timeEarlyMid'],
                    'timeEarlyEnd' => $timeReset['timeEarlyEnd']
                ];
            }
        } else {
            if (ManageTimeView::isMorningTime($dateStart->hour)) {
                $dateStart = self::getTimeStartMor($dateStart, $timeSettingOfEmp);
            } else {
                $dateStart = self::getTimeStartAfter($dateStart, $timeSettingOfEmp);
            }

            if ($dateEnd->hour == $timeSettingOfEmp['morningOutSetting']->hour ||
                $dateEnd->hour == $timeSettingOfEmp['morningOutSetting']->hour - 2) {
                $dateEnd = self::getTimeEndMor($dateEnd, $timeSettingOfEmp);
            } else {
                $dateEnd = self::getTimeEndAfter($dateEnd, $timeSettingOfEmp);
            }

            $diff = static::getDiffTimesRegister($dateStart->hour, $dateEnd->hour, $dateStart->minute, $dateEnd->minute, $timeSettingOfEmp);
            if ($diff >= 1) {
                $session = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
                $timekeeping = 0;
            } elseif ($dateStart->hour == $timeSettingOfEmp['morningInSetting']->hour &&
                    $dateEnd->hour == $timeSettingOfEmp['morningOutSetting']->hour) {
                $session = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
                $timekeeping = 0;
            } elseif ($dateStart->hour == $timeSettingOfEmp['afternoonInSetting']->hour &&
                    $dateEnd->hour == $timeSettingOfEmp['afternoonOutSetting']->hour) {
                $session = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
                $timekeeping = 0;
            } else {
                $timeReset = self::getTimeReset($timeKeepingTableId, $employeeId, $dateTimekeeping->format('Y-m-d'), $dateStart, $dateEnd, $timeSettingOfEmp);
                return [
                    'diff' => $diff,
                    'session' => $diff,
                    'timekeeping_number_register' => $timeReset['time'],
                    'timeLateStart' => $timeReset['timeLateStart'],
                    'timeLateMid' => $timeReset['timeLateMid'],
                    'timeEarlyMid' => $timeReset['timeEarlyMid'],
                    'timeEarlyEnd' => $timeReset['timeEarlyEnd']
                ];
            }
            return [
                'diff' => $diff,
                'session' => $session,
                'timekeeping_number_register' => $timekeeping,
            ];
        }
    }

    /**
     * [getDiffTimes: get timekeeping time on timekeeping date]
     * @param  [int] $hourStart, $hourEnd, $minuteStart, $minuteEnd
     * @return [float]
     */
    public static function getDiffTimesRegister($hourStart, $hourEnd, $minuteStart, $minuteEnd, $workingTime)
    {
        $startDate = new DateTime();
        $startDate->setTime($hourStart, $minuteStart);
        $endDate = new DateTime();
        $endDate->setTime($hourEnd, $minuteEnd);
        $diffTime = $startDate->diff($endDate);

        if ($hourEnd > $workingTime['morningOutSetting']->hour) {
            $lunchBreak = ManageTimeView::getLunchBreak($hourStart, $hourEnd, $workingTime);
            return round(($diffTime->h * 60 + $diffTime->i - $lunchBreak) / ManageTimeView::getHoursWork($workingTime), 2);
        }
        return round(($diffTime->h * 60 + $diffTime->i) / ManageTimeView::getHoursWork($workingTime), 2);
    }

    /**
     * [getDiffTimesRegisterCarbon description]
     * @param  [carbon] $startDate
     * @param  [carbon] $endDate
     * @param  [array] $workingTime
     * @return [float]
     */
    public function getDiffTimesRegisterCarbon($startDate, $endDate, $workingTime)
    {
        $diffTime = $startDate->diff($endDate);
        if ($endDate->hour > $workingTime['morningOutSetting']->hour) {
            $lunchBreak = ManageTimeView::getLunchBreak($startDate->hour, $endDate->hour, $workingTime);
            return round(($diffTime->h * 60 + $diffTime->i - $lunchBreak) / ManageTimeView::getHoursWork($workingTime), 2);
        }
        return round(($diffTime->h * 60 + $diffTime->i) / ManageTimeView::getHoursWork($workingTime), 2);
    }

    /**
    * set time variable session
    * @param [date] $startHour, endHour
    * @param [datetime] $timeSettingOfEmp
    * @return [float]
    */
    public static function setTimeSession($startDate, $endDate, $timeSettingOfEmp)
    {
        if ($startDate->hour == $timeSettingOfEmp['morningInSetting']->hour) {
            if ($endDate->hour == $timeSettingOfEmp['morningOutSetting']->hour) {
                return ManageTimeConst::HAS_LEAVE_DAY_MORNING;
            }
        } elseif ($startDate->hour == $timeSettingOfEmp['afternoonInSetting']->hour) {
            if ($endDate->hour == $timeSettingOfEmp['morningOutSetting']->hour) {
                return ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
            }
        }
        $session = round((($endDate->hour - $startDate->hour) * 60 + ($endDate->minute - $startDate->minute)) / 480, 2);
        return $session;
    }

    /**
    * reset time date start in morning
    * @param [datetime] $dateStart, $timeSettingOfEmp
    * @return [datetime]
    */
    public static function getTimeStartMor($dateStart, $timeSettingOfEmp)
    {
        if ($dateStart->hour == ($timeSettingOfEmp['morningOutSetting']->hour - 2)) {
            $hour = $dateStart->hour;
            $minute = $dateStart->minute;
        } else {
            $hour = $timeSettingOfEmp['morningInSetting']->hour;
            $minute = $timeSettingOfEmp['morningInSetting']->minute;
        }
        $dateStart->hour($hour);
        $dateStart->minute($minute);
        return $dateStart;
    }

    /**
    * reset time date start in afternoon
    * @param [datetime] $dateStart, $timeSettingOfEmp
    * @return [datetime]
    */
    public static function getTimeStartAfter($dateStart, $timeSettingOfEmp)
    {
        if ($dateStart->hour == ($timeSettingOfEmp['afternoonOutSetting']->hour - 2)) {
            $hour = $dateStart->hour;
            $minute = $dateStart->minute;
        } else {
            $hour = $timeSettingOfEmp['afternoonInSetting']->hour;
            $minute = $timeSettingOfEmp['afternoonInSetting']->minute;
        }
        $dateStart->hour($hour);
        $dateStart->minute($minute);
        return $dateStart;
    }

    /**
    * reset time date end in morning
    * @param [datetime] $dateEnd, $timeSettingOfEmp
    * @return [datetime]
    */
    public static function getTimeEndMor($dateEnd, $timeSettingOfEmp)
    {
        if ($dateEnd->hour == ($timeSettingOfEmp['morningInSetting']->hour + 2)) {
            $hour = $dateEnd->hour;
            $minute = $dateEnd->minute;
        } else {
            $hour = $timeSettingOfEmp['morningOutSetting']->hour;
            $minute = $timeSettingOfEmp['morningOutSetting']->minute;
        }
        $dateEnd->hour($hour);
        $dateEnd->minute($minute);
        return $dateEnd;
    }

    /**
    * reset time date end in afternoon
    * @param [datetime] $dateEnd, $timeSettingOfEmp
    * @return [datetime]
    */
    public static function getTimeEndAfter($dateEnd, $timeSettingOfEmp)
    {
        if ($dateEnd->hour == ($timeSettingOfEmp['afternoonInSetting']->hour + 2)) {
            $hour = $dateEnd->hour;
            $minute = $dateEnd->minute;
        } else {
            $hour = $timeSettingOfEmp['afternoonOutSetting']->hour;
            $minute = $timeSettingOfEmp['afternoonOutSetting']->minute;
        }
        $dateEnd->hour($hour);
        $dateEnd->minute($minute);
        return $dateEnd;
    }

    /**
     * calculate working time, time go late, time leave when date leave register same day
     * @param [int] $timeKeepingTableId, $employeeId
     * @param [date] $dateTimekeeping
     * @param [datetime] $dateStart, $dateEnd, $timeSettingOfEmp
     * @return [array]
     */
    public static function getTimeReset($timeKeepingTableId, $employeeId, $dateTimekeeping, $dateStart, $dateEnd, $timeSettingOfEmp)
    {
        $timeKeeping = Timekeeping::getTimekeeping($timeKeepingTableId, $employeeId, $dateTimekeeping);
        $timeLateStart = 0;
        $timeLateMid = 0;
        $timeEarlyMid = 0;
        $timeEarlyEnd = 0;

        $timeInMor = $timeSettingOfEmp['morningInSetting']->hour;
        $timeOurAfter = $timeSettingOfEmp['afternoonOutSetting']->hour;
        $timeOurMor = $timeSettingOfEmp['morningOutSetting']->hour;
        $timeInAfter = $timeSettingOfEmp['afternoonInSetting']->hour;
        if (!$timeKeeping ||
            ($timeKeeping->end_time_morning_shift == null &&
            $timeKeeping->end_time_afternoon_shift == null)) {
            return [
                'time' => 0,
                'timeLateStart' => $timeLateStart,
                'timeLateMid' => $timeLateMid,
                'timeEarlyMid' => $timeEarlyMid,
                'timeEarlyEnd' => $timeEarlyEnd,
            ];
        }
        $timeStartLeave = $dateStart->hour * 60 + $dateStart->minute;
        $timeEndLeave = $dateEnd->hour * 60 + $dateEnd->minute;
        switch ($dateStart->hour) {
            case $timeOurMor - 2:
                if (empty($timeKeeping->end_time_morning_shift) &&
                    empty($timeKeeping->start_time_afternoon_shift) &&
                    empty($timeKeeping->end_time_afternoon_shift)) {
                    $time = 0;
                    break;
                }
                if ($dateEnd->hour == $timeOurMor) {
                    if (!empty($timeKeeping->start_time_morning_shift)) {
                        $timeStartMor = explode(':', $timeKeeping->start_time_morning_shift);
                        $timeStartMor = (int)$timeStartMor[0] * 60 + (int)$timeStartMor[1];
                        $timeEndRegiser = ($timeOurMor - 2) * 60 + $timeSettingOfEmp['morningOutSetting']->minute;

                        if ($timeStartMor > $timeEndRegiser) {
                            $time = 0;
                        } else {
                            $time = ($dateStart->hour - $timeInMor) * 60 + $dateStart->minute - $timeSettingOfEmp['morningInSetting']->minute;
                        }

                        $setTimes = self::getTimeEarlyMid($timeKeeping, $timeStartLeave);
                        $timeEarlyMid = $setTimes['lateEarly'];
                        $timeEarlyMid = $timeEarlyMid ? $timeEarlyMid : ManageTimeConst::RESET;
                        $time = $time * $setTimes['time'];
                    } else {
                        $time = 0;
                    }
                } elseif ($dateEnd->hour == $timeOurAfter) {
                    if (!empty($timeKeeping->start_time_morning_shift)) {
                        $timeStart = explode(':', $timeKeeping->start_time_morning_shift);
                        $timeStartMor = (int)$timeStart[0] * 60 + (int)$timeStart[1];
                    }
                    $timeStrartRegister = ($timeOurMor - 2) * 60 + $timeSettingOfEmp['morningInSetting']->minute;
                    $timeMor = 0;
                    if (!empty($timeKeeping->start_time_morning_shift) && $timeStartMor < $timeStrartRegister) {
                        $timeMor = ($dateStart->hour - $timeInMor) * 60 + $dateStart->minute - $timeSettingOfEmp['morningInSetting']->minute;

                        $setTimes = self::getTimeEarlyMid($timeKeeping, $timeStartLeave);
                        $timeEarlyMid = $setTimes['lateEarly'];
                        $timeEarlyMid = $timeEarlyMid ? $timeEarlyMid : ManageTimeConst::RESET;
                    }
                    if ($timeMor > 0 && $timeKeeping->timekeeping_number > 0) {
                        $time = - $timeKeeping->timekeeping_number * 480 + $timeMor;
                        $timeEarlyEnd = ManageTimeConst::RESET;
                    } elseif ($timeMor > 0 && $timeKeeping->timekeeping_number == 0
                            || $timeMor < 0 && empty($timeKeeping->end_time_afternoon_shift)) {
                        $time = $timeMor;
                    } elseif ($timeMor < 0 && !empty($timeKeeping->end_time_afternoon_shift)) {
                        if ($timeKeeping->timekeeping_number == 1) {
                            $time = - 480 - $timeMor;
                        } else {
                            $time = $timeMor;
                        }
                    } elseif ($timeMor == 0) {
                        $time = - $timeKeeping->timekeeping_number * 480;
                    } else {
                        $time = 0;
                    }
                } else {
                    if (!empty($timeKeeping->end_time_afternoon_shift)) {
                        $hourAfter = explode(":", $timeKeeping->end_time_afternoon_shift);
                        $timeHourAfter = (int)$hourAfter[0] * 60 + (int)$hourAfter[1];
                        if ($timeHourAfter > ($timeOurAfter - 2) * 60 + $timeSettingOfEmp['afternoonOutSetting']->minute) {
                            $timeAfter = ($timeOurAfter - $dateEnd->hour) * 60 + $timeSettingOfEmp['afternoonOutSetting']->minute - $dateEnd->minute;

                            $setTimes = self::getTimeLateMid($timeKeeping, $timeEndLeave);
                            $timeLateMid = $setTimes['lateEarly'];
                            $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                        } else {
                            $timeAfter = 0;
                        }
                    } else {
                        $timeAfter = 0;
                    }
                    if (!empty($timeKeeping->start_time_morning_shift)) {
                        $timeMor = ($dateStart->hour - $timeInMor) * 60 + $dateStart->minute - $timeSettingOfEmp['morningInSetting']->minute;
                    } else {
                        $timeMor = 0;
                    }
                    if ($timeAfter) {
                        $time = - ($timeMor + $timeAfter);
                    } else {
                        $time = - $timeMor;
                    }
                }
                break;
            case $timeInAfter:
                if (!empty($timeKeeping->end_time_afternoon_shift) && $dateEnd->hour == $timeOurAfter - 2) {
                    $time = ($timeOurAfter - $dateEnd->hour) * 60 + $timeSettingOfEmp['afternoonOutSetting']->minute - $dateEnd->minute;
                } else {
                    $time = 0;
                }
                $setTimes = self::getTimeLateMid($timeKeeping, $timeEndLeave);
                $timeLateMid = $setTimes['lateEarly'];
                $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                $time = $time * $setTimes['time'];
                break;
            case $timeOurAfter - 2:
                if (!empty($timeKeeping->end_time_afternoon_shift) &&
                    (!empty($timeKeeping->start_time_morning_shift) || !empty($timeKeeping->start_time_afternoon_shift))) {
                    $timeStartAfter = 0;
                    if (!empty($timeKeeping->start_time_afternoon_shift)) {
                        $timeStartAfter = explode(':', $timeKeeping->start_time_afternoon_shift);
                        $timeStartAfter = (int)$timeStartAfter[0] * 60 + (int)$timeStartAfter[1];
                    }
                    if ($timeStartAfter >= $timeEndLeave) {
                        $time = 0;
                    } else {
                        $time = ($dateStart->hour - $timeInAfter) * 60 + $timeSettingOfEmp['afternoonInSetting']->minute - $dateStart->minute;
                    }
                } else {
                    $time = 0;
                }
                $setTimes = self::getTimeEarlyEnd($timeKeeping, $timeStartLeave);
                $timeEarlyEnd = $setTimes['lateEarly'];
                $timeEarlyEnd = $timeEarlyEnd ? $timeEarlyEnd : ManageTimeConst::RESET;
                $time = $time * $setTimes['time'];
                break;
            default:
                if (empty($timeKeeping->end_time_morning_shift) &&
                    empty($timeKeeping->start_time_afternoon_shift) &&
                    empty($timeKeeping->end_time_afternoon_shift)) {
                    $time = 0;
                    break;
                }
                if ($dateEnd->hour == $timeOurMor - 2 && !empty($timeKeeping->start_time_morning_shift)) {
                    $timeStartMor = explode(':', $timeKeeping->start_time_morning_shift);
                    $timeStartMor = (int)$timeStartMor[0] * 60 + (int)$timeStartMor[1];

                    if (!empty($timeKeeping->end_time_morning_shift)) {
                        $timeEndMor = explode(':', $timeKeeping->end_time_morning_shift);
                        $timeEndMor = (int)$timeEndMor[0] * 60 + (int)$timeEndMor[1];
                    }
                    if (isset($timeEndMor) && $timeEndMor <= $timeEndLeave) {
                        $time = 0;
                    } elseif ($timeStartMor < ($timeOurMor * 60 + $timeSettingOfEmp['morningOutSetting']->minute)) {
                        $time = ($timeOurMor - $dateEnd->hour) * 60 + $timeSettingOfEmp['morningOutSetting']->minute - $dateEnd->minute;
                        $setTimes = self::getTimeLateStart($timeKeeping, $timeEndLeave);
                        $timeLateStart = $setTimes['lateEarly'];
                        $timeLateStart = $timeLateStart ? $timeLateStart : ManageTimeConst::RESET;
                        $time = $time * $setTimes['time'];
                    } else {
                        $time = 0;
                    }
                } elseif ($dateEnd->hour == $timeOurAfter - 2 && !empty($timeKeeping->end_time_afternoon_shift)) {
                    $timeEndAfter = explode(':', $timeKeeping->end_time_afternoon_shift);
                    $timeEnd = (int)$timeEndAfter[0] * 60 + (int)$timeEndAfter[1];
                    $timeEndRegiser = ($timeOurAfter - 2) * 60 + $timeSettingOfEmp['afternoonOutSetting']->minute;

                    if (!empty($timeKeeping->start_time_morning_shift) && $timeEnd <= $timeEndRegiser) {
                        $time = - $timeKeeping->timekeeping_number * 480;
                        $timeLateStart = ManageTimeConst::RESET;
                        $timeEarlyEnd = ManageTimeConst::RESET;
                    } elseif (!empty($timeKeeping->start_time_morning_shift) && $timeEnd > $timeEndRegiser) {
                        $tempolary = ($timeOurAfter - $dateEnd->hour) * 60 + $timeSettingOfEmp['afternoonOutSetting']->minute - $dateEnd->minute;
                        $time = - 480 + $tempolary;

                        $setTimes = self::getTimeLateMid($timeKeeping, $timeEndLeave);
                        $timeLateMid = $setTimes['lateEarly'];
                        $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                    } else {
                        $time = ($timeOurAfter - $dateEnd->hour) * 60 + $timeSettingOfEmp['afternoonOutSetting']->minute - $dateEnd->minute;

                        $setTimes = self::getTimeLateMid($timeKeeping, $timeEndLeave);
                        $timeLateMid = $setTimes['lateEarly'];
                        $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                        $time = $time * $setTimes['time'];
                    }
                } else {
                    $time = 0;
                }
                break;
            }
        return [
            'time' => $time / 480,
            'timeLateStart' => $timeLateStart,
            'timeLateMid' => $timeLateMid,
            'timeEarlyMid' => $timeEarlyMid,
            'timeEarlyEnd' => $timeEarlyEnd,
        ];
    }

    /**
     * calculate working time, time go late, time leave: morning when date leave register < dateTimekeeping
     * @param [int] $timeKeepingTableId, $employeeId
     * @param [date] $dateTimekeeping
     * @param [datetime] $dateStart, $timeSettingOfEmp
     * @return [array]
     */
    public static function getTimeResetStartEqual($timeKeepingTableId, $employeeId, $dateTimekeeping, $dateStart, $timeSettingOfEmp)
    {
        $timeKeeping = Timekeeping::getTimekeeping($timeKeepingTableId, $employeeId, $dateTimekeeping);
        $timeLateStart = 0;
        $timeEarlyMid = 0;
        $timeEarlyEnd = 0;

        $timeInMor = $timeSettingOfEmp['morningInSetting']->hour;
        $timeOurAfter = $timeSettingOfEmp['afternoonOutSetting']->hour;

        $timeStartLeave = $dateStart->hour * 60 + $dateStart->minute;

        if ($dateStart->hour == $timeSettingOfEmp['morningOutSetting']->hour - 2) {
            if (!empty($timeKeeping->start_time_morning_shift)) {
                $time = (($dateStart->hour - $timeSettingOfEmp['morningInSetting']->hour) * 60 +
                $dateStart->minute - $timeSettingOfEmp['morningInSetting']->minute) / 480;
                $timeEndLeave = $dateStart->hour * 60 + $dateStart->minute;

                $setTimes = self::getTimeEarlyMid($timeKeeping, $timeEndLeave);
                if ($setTimes['time'] > 0 && empty($timeKeeping->end_time_afternoon_shift) &&
                    (float)$timeKeeping->timekeeping_number > 0) {
                    $time = - (($timeSettingOfEmp['morningOutSetting']->hour - $dateStart->hour) * 60 +
                    $dateStart->minute - $timeSettingOfEmp['morningOutSetting']->minute) / 480;
                }
                $timeEarlyMid = $setTimes['lateEarly'];
                $timeEarlyMid = $timeEarlyMid ? $timeEarlyMid : ManageTimeConst::RESET;
                $time = $time * $setTimes['time'];
            } else {
                $time = 0;
            }
        } else {
            if (!empty($timeKeeping->end_time_afternoon_shift)) {
                $cpAfternoonOutSetting = clone $timeSettingOfEmp['afternoonOutSetting'];
                $time120End = clone $cpAfternoonOutSetting->subHours(2);
                $timeEndLeave = $time120End->hour * 60 + $time120End->minute;
                $setTimes = self::getTimeEarlyEnd($timeKeeping, $timeEndLeave);
                if ($setTimes['time'] > 0) {
                    $time = (($dateStart->hour - $timeSettingOfEmp['afternoonInSetting']->hour) * 60 +
                    $dateStart->minute - $timeSettingOfEmp['afternoonInSetting']->minute) / 480;
                } else {
                    $time = (($timeSettingOfEmp['afternoonOutSetting']->hour - $dateStart->hour) * 60 +
                    $timeSettingOfEmp['afternoonOutSetting']->minute - $dateStart->minute) / 480;
                }
                $timeEarlyEnd = $setTimes['lateEarly'];
                $timeEarlyEnd = $timeEarlyEnd ? $timeEarlyEnd : ManageTimeConst::RESET;
                $time = $time * $setTimes['time'];
            } else {
                $time = 0;
            }
        }
        return [
            'time' => $time,
            'timeLateStart' => $timeLateStart,
            'timeEarlyMid' => $timeEarlyMid,
            'timeEarlyEnd' => $timeEarlyEnd,
        ];
    }

    /**
     * calculate working time, time go late, time leave: afternoon when date leave register > dateTimekeeping
     * @param [int] $timeKeepingTableId, $employeeId
     * @param [date] $dateTimekeeping
     * @param [datetime] $dateEnd, $timeSettingOfEmp
     * @return [array]
     */
    public static function getTimeResetEndEqual($timeKeepingTableId, $employeeId, $dateTimekeeping, $dateEnd, $timeSettingOfEmp)
    {
        $timeKeeping = Timekeeping::getTimekeeping($timeKeepingTableId, $employeeId, $dateTimekeeping);
        $timeLateStart = 0;
        $timeLateMid = 0;

        $timeOurMor = $timeSettingOfEmp['morningOutSetting'];
        $timeInAfter = $timeSettingOfEmp['afternoonInSetting'];
        $timeOurAfter = $timeSettingOfEmp['afternoonOutSetting'];
        $timeMorIn = $timeSettingOfEmp['morningInSetting'];

        $timeStartLeave = $timeEndLeave = $dateEnd->hour * 60 + $dateEnd->minute;
        if ($dateEnd->hour == $timeOurMor->hour - 2) {
            if (!empty($timeKeeping->start_time_morning_shift)) {
                $time = (($dateEnd->hour - $timeMorIn->hour) * 60 + $dateEnd->minute - $timeMorIn->minute) / 480;

                $time120 = $timeMorIn->hour * 60 + $timeMorIn->minute;
                $setTimes = self::getTimeLateStart($timeKeeping, $timeStartLeave, $time120);
                $timeLateStart = $setTimes['lateEarly'];
                $timeLateStart = $timeLateStart ? $timeLateStart : ManageTimeConst::RESET;
                $time = $time * $setTimes['time'];
            } else {
                $time = 0;
            }
        } else {
            if (!empty($timeKeeping->end_time_afternoon_shift)) {
                $timeEnd = explode(':', $timeKeeping->end_time_afternoon_shift);
                $timeEnd = (int)$timeEnd[0] * 60 + (int)$timeEnd[1];
                if ($timeEnd <= $timeEndLeave) {
                    $time = -$timeKeeping->timekeeping_number;
                } else {
                    $time = (($timeOurAfter->hour - $dateEnd->hour) * 60 + $timeSettingOfEmp['afternoonOutSetting']->minute - $dateEnd->minute) / 480;
                    $setTimes = self::getTimeLateMid($timeKeeping, $timeStartLeave);
                    if ($setTimes['time'] < 0) {
                        $time = (($dateEnd->hour - $timeMorIn->hour) * 60 + $dateEnd->minute - $timeMorIn->minute) / 480;
                        $time -= (($timeInAfter->hour - $timeOurMor->hour) * 60 + $timeInAfter->minute - $timeOurMor->minute) / 480;
                    }
                    $timeLateMid = $setTimes['lateEarly'];
                    $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                    $time = $time * $setTimes['time'];
                }
            }else{
                $time = 0;
            }
        }

        return [
            'time' => $time,
            'timeLateStart' => $timeLateStart,
            'timeLateMid' => $timeLateMid,
        ];
    }

    /**
    * Calculate the time go company late start
    * @param [colletion] $timeKeeping
    * @param int $timeStartLeave
    * @return [float]
    */
    public static function getTimeLateStart($timeKeeping, $timeStartLeave, $time120 = null)
    {
        $time = 1;
        $lateEarly = 0;
        if (!empty($timeKeeping->start_time_morning_shift)) {
            $timeStartMor = explode(':', $timeKeeping->start_time_morning_shift);
            $lateStart = (int)$timeStartMor[0] * 60 + (int)$timeStartMor[1];
            if ($lateStart - $timeStartLeave > 0) {
                $lateEarly = $lateStart - $timeStartLeave;
            }
            if ($time120) {
                if ($time120 < $timeStartLeave) {
                    $time = -1;
                }
            } elseif ($lateStart < $timeStartLeave) {
                $time = -1;
            } else {
            }
        }
        return [
            'time' => $time,
            'lateEarly' => $lateEarly
        ];
    }

    /**
    * Calculate the time leave company early mid
    * @param [colletion] $timeKeeping
    * @param [int] $timeStartLeave
    * @return [float]
    */
    public static function getTimeEarlyMid($timeKeeping, $timeEndLeave)
    {
        $time = 1;
        $lateEarly = 0;
        if (!empty($timeKeeping->end_time_morning_shift)) {
            $timeEndMor = explode(':', $timeKeeping->end_time_morning_shift);
            $earlyMid = (int)$timeEndMor[0] * 60 + (int)$timeEndMor[1];
            if ($timeEndLeave - $earlyMid > 0) {
                $lateEarly = $timeEndLeave - $earlyMid;
            }
        }
        if (!empty($timeKeeping->start_time_morning_shift)) {
            $timeStartMor = explode(':', $timeKeeping->start_time_morning_shift);
            $timeStartMor = (int)$timeStartMor[0] * 60 + (int)$timeStartMor[1];
            if ($timeKeeping->timeKeeping == 1 ||
                (!empty($timeKeeping->end_time_afternoon_shift) && $timeStartMor < $timeEndLeave)) {
                $time = -1;
            }
        }
        return [
            'time' => $time,
            'lateEarly' => $lateEarly
        ];
    }

    /**
    * Calculate the time go company late mid
    * @param [colletion] $timeKeeping
    * @param int $timeStartLeave
    * @return [float]
    */
    public static function getTimeLateMid($timeKeeping, $timeStartLeave)
    {
        $time = 1;
        $lateEarly = 0;
        if (!empty($timeKeeping->start_time_afternoon_shift)) {
            $timeStartAfter = explode(':', $timeKeeping->start_time_afternoon_shift);
            $lateMid = (int)$timeStartAfter[0] * 60 + (int)$timeStartAfter[1];
            if ($lateMid - $timeStartLeave > 0) {
                $lateEarly = $lateMid - $timeStartLeave;
            }

            if ($lateMid > $timeStartLeave && !empty((float)$timeKeeping->timekeeping_number)) {
                $time = -1;
            }

            if ($timeKeeping->timekeeping_number == 1 && $time > 0) {
                $time = -1;
            }
        } else {
            if ($timeKeeping->timekeeping_number == 1) {
                $time = -1;
            }
            if ($time > 0 && !empty($timeKeeping->end_time_afternoon_shift)) {
                $timeEndtAfter = explode(':', $timeKeeping->end_time_afternoon_shift);
                $endTime = (int)$timeEndtAfter[0] * 60 + (int)$timeEndtAfter[1];
                if ($endTime > $timeStartLeave + 120) {
                    $time = -1;
                }
            }
        }
        return [
            'time' => $time,
            'lateEarly' => $lateEarly
        ];
    }

    /**
    * Calculate the time leave company early end
    * @param [colletion] $timeKeeping
    * @param int $timeStartLeave
    * @return [float]
    */
    public static function getTimeEarlyEnd($timeKeeping, $timeStartLeave, $time120End = null)
    {
        $time = 1;
        $lateEarly = 0;
        if (!empty($timeKeeping->end_time_afternoon_shift)) {
            $timeEndAfter = explode(':', $timeKeeping->end_time_afternoon_shift);
            $earlyMid = (int)$timeEndAfter[0] * 60 + (int)$timeEndAfter[1];
            if ($timeStartLeave - $earlyMid > 0) {
                $lateEarly = $timeStartLeave - $earlyMid;
            }

            if ($time120End) {
                if ($earlyMid > $time120End) {
                    $time = -1;
                }
            } else {
                if ($timeStartLeave < $earlyMid) {
                    $time = -1;
                }
            }
        }
        return [
            'time' => $time,
            'lateEarly' => $lateEarly
        ];
    }

    /**
     * merge data array $data into array $datatotal
     * @param  [array] $data      [two dimensional]
     * @param  [array] $dataTotal [two dimensional]
     * @return [array]
     */
    public static function mergeDataTotalTimeKeeping($data, $dataTotal, $merge = true)
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $dataTotal) && $merge) {
                $dataTotal[$key] = array_merge($data[$key], $dataTotal[$key]);
            } else {
                $dataTotal[$key] = $data[$key];
            }
        }
        return $dataTotal;
    }

    /**
     * [update timekeeping: update timekeeping table]
     * @param  Request $request
     * @return [type]
     */
    public function updateTimeTableTimekeeping(Request $request)
    {
        if (!TimekeepingPermission::isPermission()) {
            return redirect()->back()->with('messages', ['errors' => [Lang::get('core::message.You don\'t have access')]]);
        }

        $dataUpdate = $request->all();
        $rules = [
            'id_table' => 'required|integer',
            'start_date' => 'required',
            'end_date'=> 'required|after:start_date'
        ];
        $messages = [
            'id_table.required' => Lang::get('manage_time::message.invalid_input_data'),
            'start_date.required'  => Lang::get('manage_time::message.Start date timekeeping is required'),
            'end_date.required' => Lang::get('manage_time::message.End date timekeeping is required'),
            'end_date.after' => Lang::get('manage_time::message.The end date timekeeping at must be after start date timekeeping')
        ];
        $validator = Validator::make($dataUpdate, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $userCurrent = Permission::getInstance()->getEmployee();
        DB::beginTransaction();
        try {
            $timekeepingTable = TimekeepingTable::find($dataUpdate['id_table']);
            $endDateOld = $timekeepingTable->end_date;
            if (!$timekeepingTable) {
                return redirect()->back()->with('messages', ['errors' => [Lang::get('manage_time::message.Not exist timekeeping table')]]);
            }
            if ($timekeepingTable->creator_id != $userCurrent->id) {
                return redirect()->back()->with('messages', ['errors' => [Lang::get('manage_time::message.You do not not have permission because not employee create')]]);
            }
            $startDate = Carbon::createFromFormat('d-m-Y', $dataUpdate['start_date']);
            $date = clone $startDate;
            $dateEnd = $date->endOfMonth()->format('d-m-Y');
            if ($dateEnd < $dataUpdate['end_date']) {
                $dataUpdate['end_date'] = $dateEnd;
            }
            $endDate = Carbon::createFromFormat('d-m-Y', $dataUpdate['end_date']);

            $timekeepingTable->timekeeping_table_name = $dataUpdate['timekeeping_table_name'];
            $timekeepingTable->start_date = $startDate->toDateString();
            $timekeepingTable->end_date = $endDate->toDateString();


            if ($timekeepingTable->update()) {
                $viewTime = new ViewTimeKeeping();
                if ($endDateOld >= $endDate->format('Y-m-d')) {
                    //== xóa khỏi bảng công
                    $viewTime->deleteDateTimeKeeping($endDate->format('Y-m-d'), $endDateOld, $timekeepingTable->id);
                } else {
                    //=== thêm vào bảng công
                    $viewTime->insertDateTimeKeeping($timekeepingTable, $startDate, $endDate);
                }
            }
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Create timekeeping table success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch(Exception $ex) {
            DB::rollBack();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [Lang::get('manage_time::message.Error. Please try again later.')]]);
        }
    }

    /**
     * Tạo bảng công theo hướng mới
     * @param  Request $request
     * @return [type]
     */
    public function storeTimeKeeping($timekeepingTable)
    {
        $teamOfTimekeeping = Team::find($timekeepingTable->team_id);
        $teamCodePrefix = Team::getTeamCodePrefix($teamOfTimekeeping->code);
        $teamCodePrefix = Team::changeTeam($teamCodePrefix);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
        $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
        $teamIds = $this->getTeamTimekeeping($timekeepingTable->team_id);

        if ($timekeepingTable->type == getOptions::WORKING_OFFICIAL) {
            $employee = new Employee();
            $employeeTimekeeping = $employee->getEmpTrialOrOffcial($timekeepingTable->end_date, $teamIds);
        } else {
            $contractType = [getOptions::WORKING_PARTTIME];
            if ($teamCodePrefix === \Rikkei\Team\View\TeamConst::CODE_DANANG) {
                $contractType[] = getOptions::WORKING_INTERNSHIP;
            }
            $contact = new ContractModel();
            $employeeTimekeeping = $contact->getEmpByContractType($timekeepingTable, $contractType);
        }

        $startDate = Carbon::parse($timekeepingTable->start_date);
        $endDate = Carbon::parse($timekeepingTable->end_date);
        $dataTimekeeping = new Timekeeping();
        $dataInsertTimekeeping = [];
        $dataTimekeepingAggregate = [];
        $dataInsertTimekeepingAggregate = [];

        if (count($employeeTimekeeping)) {
            $dates = [];
            while (strtotime($startDate) <= strtotime($endDate)) {
                $dates[] = $startDate->toDateString();
                $startDate->addDay();
            }
            $dataTimekeeping->timekeeping_table_id = $timekeepingTable->id;
            $dataTimekeepingAggregate['timekeeping_table_id'] = $timekeepingTable->id;
            $now = Carbon::now();
            $manageTimeView = new ManageTimeView();
            foreach ($employeeTimekeeping as $emp) {
                $dataTimekeeping->employee_id = $emp->employee_id;
                $dataTimekeeping->created_at = $now;
                $dataTimekeeping->updated_at = $now;
                $dataTimekeepingAggregate['employee_id'] = $emp->employee_id;
                $dataTimekeepingAggregate['created_at'] = $now;
                $dataTimekeepingAggregate['updated_at'] = $now;

                $empOffcialDate = $emp->offcial_date;
                $empTrialDate = $emp->trial_date;
                $empOffcialDateCarbon = Carbon::parse($empOffcialDate)->format('Y-m-d');
                foreach ($dates as $date) {
                    $dateCarbon = Carbon::createFromFormat('Y-m-d', $date);
                    $isWeekend = ManageTimeCommon::isWeekend($dateCarbon, $compensationDays);
                    $isHoliday = ManageTimeCommon::isHolidays($dateCarbon, [$annualHolidays, $specialHolidays]);
                    $dataTimekeeping->timekeeping_date = $date;

                    $dataTimekeeping->is_official =  0;
                    if ($empOffcialDate && strtotime($dateCarbon->format('Y-m-d')) >= strtotime($empOffcialDateCarbon)) {
                        $dataTimekeeping->is_official =  1;
                    }

                    if (empty($emp->leave_date) || Carbon::parse($emp->leave_date)->gte(Carbon::parse($date))) {
                        $timekeepingResult = $manageTimeView->timekeepingResult($dataTimekeeping, $isWeekend, $isHoliday, $empOffcialDate, $empTrialDate, $timekeepingTable->contract_type, null, null, $timekeepingTable->type);
                        $dataTimekeeping->timekeeping = $timekeepingResult[0];
                        $dataTimekeeping->timekeeping_number = $timekeepingResult[1];
                    } else {
                        $dataTimekeeping->timekeeping = 0;
                        $dataTimekeeping->timekeeping_number = 0;
                    }
                    $dataInsertTimekeeping[] = $dataTimekeeping->toArray();
                }
                $dataInsertTimekeepingAggregate[] = $dataTimekeepingAggregate;
            }
            unset($employeeTimekeeping);
            foreach (collect($dataInsertTimekeeping)->chunk(1000) as $chunk) {
                Timekeeping::insert($chunk->toArray());
            }
            unset($dataInsertTimekeeping);
            foreach (collect($dataInsertTimekeepingAggregate)->chunk(1000) as $chunk) {
                TimekeepingAggregate::insert($chunk->toArray());
            }
            unset($dataInsertTimekeepingAggregate);
        }
    }

    /**
     * check validate timekeeping
     * @param  [array] $dataInsert
     * @return [type]
     */
    public function validateTimeKeeping($dataInsert)
    {
        $rules = [
            'timekeeping_table_name' => 'required',
            'team_id' => 'required',
            'month' => 'required',
            'year' => 'required',
            'start_date' => 'required',
            'end_date'=> 'required|after:start_date'
        ];
        $messages = [
            'timekeeping_table_name.required' => Lang::get('manage_time::message.Timekeeping table name is required'),
            'team_id.required' => Lang::get('manage_time::message.Team id is required'),
            'month.required' => Lang::get('manage_time::message.Month is required'),
            'year.required'  => Lang::get('manage_time::message.Year is required'),
            'start_date.required'  => Lang::get('manage_time::message.Start date timekeeping is required'),
            'end_date.required' => Lang::get('manage_time::message.End date timekeeping is required'),
            'end_date.after' => Lang::get('manage_time::message.The end date timekeeping at must be after start date timekeeping')
        ];
        return Validator::make($dataInsert, $rules, $messages);
    }

    /**
     * get list team
     * @param  [collection] $timekeepingTable
     * @return [type]
     */
    public function getTeamTimekeeping($teamId)
    {
        $teamIds = [];
        $teamIds[] = (int) $teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId);
        $teamHN = Team::select('id')->where('code', TeamConst::CODE_HANOI)->first();

        if ($teamHN && $teamHN->id == $teamId) {
            // Add team BOD and PQA
            $team = new Team();
            $teamIds = array_unique(array_merge($teamIds, $team->getTeamBODPQA()));
        }
        return array_values($teamIds);
    }

    /**
     * saveTimeKeepingTable new
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function saveTimeKeepingTableNew(Request $request)
    {
        if (!TimekeepingPermission::isPermission()) {
            return redirect()->back()->with('messages', ['errors' => [Lang::get('core::message.You don\'t have access')]]);
        }
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 300);
        $dataInsert = $request->all();
        $validator = $this->validateTimeKeeping($dataInsert);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        DB::beginTransaction();
        try {
            $userCurrent = Permission::getInstance()->getEmployee();
            $startDate = Carbon::createFromFormat('d-m-Y', $dataInsert['start_date']);
            $endDate = Carbon::createFromFormat('d-m-Y', $dataInsert['end_date']);
            $teamId = $dataInsert['team_id'];
            $timekeepingTable = new TimekeepingTable();
            $timekeepingTable->creator_id = $userCurrent->id;
            $timekeepingTable->timekeeping_table_name = $dataInsert['timekeeping_table_name'];
            $timekeepingTable->team_id = $teamId;
            $timekeepingTable->month = $dataInsert['month'];
            $timekeepingTable->year = $dataInsert['year'];
            $timekeepingTable->start_date = $startDate->toDateString();
            $timekeepingTable->end_date = $endDate->toDateString();
            $timekeepingTable->type = $request->contract_type;
            if ($timekeepingTable->save()) {
                $this->storeTimeKeeping($timekeepingTable);
            }
            DB::commit();
            $messages = [
                'success'=> [
                    Lang::get('manage_time::message.Create timekeeping table success'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => [Lang::get('manage_time::message.Error. Please try again later.')]]);
        }
    }

    /**
     * Tạo bảng châm công
     * @param  Request $request
     * @return [type]
     */
    public function saveTimeKeepingTable(Request $request)
    {
        $teamOfTimekeeping = Team::find($request->team_id);
        $teamCodePrefix = Team::getTeamCodePrefix($teamOfTimekeeping->code);
        $teamCodePrefix = Team::changeTeam($teamCodePrefix);
        if (!$teamCodePrefix || $teamCodePrefix == Team::CODE_PREFIX_HN) {
            return $this->saveTimeKeepingTableNew($request);
        } else {
            return $this->saveTimeKeepingTableOld($request, $teamCodePrefix);
        }
    }

    /**
     * Cron job get related data into timekeeping
     *
     * @param  Request $request
     * @param  int|null $checkLock
     *
     * @return void
     */
    public static function setDataRelatedCron($file, $checkLock = null)
    {
        $dataRelate = [];
        set_time_limit(3600);
        $resultMore = '';
        $result = preg_match('/[0-9]+.*$/', $file, $resultMore);
        if (!$result || !$resultMore) {
            return true;
        }
        $resultMore = $resultMore[0];
        $dataRequest['timekeeping_table_id'] = explode('_', substr($resultMore, 0, strrpos($resultMore, '.')))[0];
        $excel = Excel::selectSheetsByIndex(0)->load(storage_path(self::FOLDER_APP . $file), function ($reader) {
        })->get()->toArray();
        if (count($excel)) {
            $dataRelate = $excel[0];
            if ($dataRelate['emp_ids'] != '') {
                $dataRelate['emp_ids'] = explode('_', $dataRelate['emp_ids']);
            } else {
                $dataRelate['emp_ids'] = [];
            }
            $dataRelate['timekeeping_table_id'] = $dataRequest['timekeeping_table_id'];
        } else {
            Log::info('Thiếu thông tin cập nhật dữ liệu liên quan ' . $file);
            return true;
        }
        $timeKeepingTable = TimekeepingTable::select(
                'id',
                'creator_id',
                'timekeeping_table_name',
                'team_id',
                'start_date',
                'end_date',
                'year',
                'month',
                'lock_up',
                'type'
            )
            ->where('id', $dataRequest['timekeeping_table_id']);
        if ($checkLock) {
            $timeKeepingTable->where('lock_up', TimekeepingTable::OPEN_LOCK_UP);
        }
        $timeKeepingTable = $timeKeepingTable->first();
        if (!$timeKeepingTable) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        }
        Log::info('file: ' . $file);
        return with(new TimekeepingController())->updateDataRelated($timeKeepingTable, $dataRelate);
    }

    /**
     * [setDataRelated description]
     *
     * @param int $tkTableId
     * @param array $empIds
     * @return \Illuminate\Http\RedirectResponse
     */
    public static function setDataRelated($tkTableId, $empIds = [])
    {
        $timeKeepingTable = TimekeepingTable::find($tkTableId);
        if (!$timeKeepingTable) {
            $messages = [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ];
            Log::info('không tìm thấy bảng công');
            return redirect()->back()->with('messages', $messages);
        }
        $dataRelate = [
            'start_date' => $timeKeepingTable->start_date,
            'end_date' => $timeKeepingTable->end_date,
            'emp_ids' => $empIds,
        ];
        return with(new TimekeepingController())->updateDataRelated($timeKeepingTable, $dataRelate);
    }

    /**
     * [updateDataRelated description]
     *
     * @param collection $timeKeepingTable
     * @param array $dataRelate [date: start_date, end_date; array emp_ids: []]
     * @param boolean $sendEmail
     * @return mixed
     * @throws Exception
     */
    public function updateDataRelated($timeKeepingTable, $dataRelate, $sendEmail = true)
    {
        Log::useFiles(storage_path() . '/logs/timekeeping.log');
        DB::beginTransaction();
        try {
            $timekeepingTableStartDate = Carbon::parse($dataRelate['start_date'])->format('Y-m-d');
            $timekeepingTableEndDate = Carbon::parse($dataRelate['end_date'])->format('Y-m-d');

            $timekeepingTableId = $timeKeepingTable->id;
            $dataBussinessTrip = [];
            $dataLeaveDay = [];
            $dataSupplement= [];
            $dataOT = [];
            $dataJoinCompany = [];
            $dataTotal = [];
            $numberEmployeeAllow = 5; // số nv cho phép function chạy luôn

            $empsIdOfTimeKeeping = Timekeeping::selectRaw('distinct(employee_id)')
                ->where('timekeeping_table_id', $timekeepingTableId);
            if (count($dataRelate['emp_ids'])) {
                $empsIdOfTimeKeeping->whereIn('employee_id', $dataRelate['emp_ids']);
            }
            $empsIdOfTimeKeeping = $empsIdOfTimeKeeping->lists('employee_id')->toArray();
            if (!$sendEmail && !$empsIdOfTimeKeeping) {
                Log::info('======= khong tim thay nhan vien khi cap nhap DLLQ ========');
                return;
            }

            //Get holidays of time keeping table
            $team = Team::getTeamById($timeKeepingTable->team_id);
            $teamCodePrefix = Team::getTeamCodePrefix($team->code);
            $teamCodePrefix = Team::changeTeam($teamCodePrefix);
            $annualHolidays = CoreConfigData::getAnnualHolidays(2);
            $specialHolidays = CoreConfigData::getSpecialHolidays(2, $teamCodePrefix);
            $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
            $rangeTimes = CoreConfigData::getValueDb(ManageTimeConst::KEY_RANGE_WKTIME);
            $arrHolidays = [$annualHolidays, $specialHolidays];
            $arrDate = [$timekeepingTableStartDate, $timekeepingTableEndDate];

            $dataResetTimekeeping = [
                'has_business_trip' => ManageTimeConst::HAS_NOT_BUSINESS_TRIP,
                'register_business_trip_number' => 0,
                'has_leave_day' => ManageTimeConst::HAS_NOT_LEAVE_DAY,
                'has_leave_day_no_salary' => ManageTimeConst::HAS_NOT_LEAVE_DAY,
                'register_leave_has_salary' => 0,
                'register_leave_no_salary' => 0,
                'has_supplement' => ManageTimeConst::HAS_NOT_SUPPLEMENT,
                'register_supplement_number' => 0,
                'leave_day_added' => 0,
                'register_leave_basic_salary' => 0,
                'no_salary_holiday' => 0,
                'timekeeping_number_register' => 0,
                'register_leave_basic_salary' => 0,
            ];

            if ($teamCodePrefix != Team::CODE_PREFIX_JP) {
                $data = [
                    'register_ot' => 0,
                    'register_ot_has_salary' => 0,
                    'register_ot_no_salary' => 0
                ];
            }
            if (count($dataRelate['emp_ids']) || $empsIdOfTimeKeeping < $numberEmployeeAllow) {
                // xóa các đơn vì có thể đơn cũ đã bị hủy
                // không chạy cả công ty vì quá lớn
                // tối ưu phân này ở chỗ hủy đơn
                $dataResetTimekeeping = array_merge($dataResetTimekeeping, $data);
                Timekeeping::where('timekeeping_table_id', $timekeepingTableId)
                ->where('timekeeping_date', '>=', $timekeepingTableStartDate)
                ->where('timekeeping_date', '<=', $timekeepingTableEndDate)
                ->whereIn('employee_id', $dataRelate['emp_ids'])
                ->where(function($query) {
                    $query->orWhere('register_business_trip_number', '<>', 0)
                        ->orWhere('register_leave_has_salary', '<>', 0)
                        ->orWhere('register_leave_no_salary', '<>', 0)
                        ->orWhere('register_supplement_number','<>', 0)
                        ->orWhere('register_leave_basic_salary', '<>', 0)
                        ->orWhere('timekeeping_number_register', '<>', 0)
                        ->orWhere('no_salary_holiday', '<>', 0)
                        ->orWhere('register_ot', '<>', 0);
                })
                ->update($dataResetTimekeeping);
            }

            $monthOfTimeKeeping = $timeKeepingTable->year . '-' . sprintf('%02d', $timeKeepingTable->month) . '-01';

            // get time join company
            $nameColumnTable = DB::getSchemaBuilder()->getColumnListing(Timekeeping::getTableName());
            $array = ["id", "timekeeping_table_id", "employee_id", "timekeeping_date", "created_at", "updated_at", "deleted_at"];
            foreach ($array as $value) {
                unset($nameColumnTable[array_search($value, $nameColumnTable)]);
            }

            $getTimeJoinCompany = Employee::getTimeJoinCompany($monthOfTimeKeeping, $empsIdOfTimeKeeping);
            $employeesEmail = Employee::select('email')->whereIn('id', $empsIdOfTimeKeeping)->lists('email')->toArray();

            $businessTripRegister = BusinessTripRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate, $timeKeepingTable);
            $leaveDayRegister = LeaveDayRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate);
            $supplementRegister = SupplementRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate);
            $overtimeRegister = OtRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate);
            $supplementRegisterOT = SupplementRegister::getRegisterOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate, true);
            $objTK = new Timekeeping();
            $idLeaveReasonNoSalaries = $objTK->getIdLeaveReasonNoSalaryHolidays();
            //separate branch when have busineess trip register
            if (count($leaveDayRegister) && count($businessTripRegister)) {
                $leaveDayRegister = ManageTimeView::separateBranchBusiness($leaveDayRegister, $businessTripRegister, $teamCodePrefix);
            }
            if (count($supplementRegister) && count($businessTripRegister)) {
                $supplementRegister = ManageTimeView::separateBranchBusiness($supplementRegister, $businessTripRegister, $teamCodePrefix);
            }
            if (count($overtimeRegister) && count($businessTripRegister)) {
                $overtimeRegister = ManageTimeView::separateBranchBusiness($overtimeRegister, $businessTripRegister, $teamCodePrefix);
            }
            if (count($supplementRegisterOT) && count($businessTripRegister)) {
                $supplementRegisterOT = ManageTimeView::separateBranchBusiness($supplementRegisterOT, $businessTripRegister, $teamCodePrefix);
            }

            $supplementOTGroup = [];
            if (count($supplementRegisterOT)) {
                $supplementOTGroup = $supplementRegisterOT->groupBy('employee_id');
            }
            $dataNotLate = with(new ManageTimeView())->getDataNotLate($empsIdOfTimeKeeping, Carbon::parse($dataRelate['start_date']), Carbon::parse($dataRelate['end_date']));
            //=== working time register ===
            $empLists = Employee::getEmpByEmailsWithContracts($employeesEmail, $timekeepingTableStartDate, $timekeepingTableEndDate, [
                'employees.email',
                'employees.id',
                'offcial_date',
                'trial_date',
                'join_date',
                'leave_date',
                'contract_type',
                'start_time1',
                'end_time1',
                'start_time2',
                'end_time2',
                'code',
            ]);
            $workingTimeDate = [];
            $workingTimeQuaterDate = [];
            $objView = new ManageTimeView();
            $workingTimeDefault = ManageTimeView::findTimeSetting([], $teamCodePrefix, $rangeTimes);
            foreach ($empLists as $empIdKey => $itemEmpList) {
                foreach ($itemEmpList as $itemEmp) {
                    $startDateWT = Carbon::parse($itemEmp->wtk_from_date);
                    $endDateWT = Carbon::parse($itemEmp->wtk_to_date);
                    while (strtotime($startDateWT) <= strtotime($endDateWT)) {
                        $dateKey = $startDateWT->format('Y-m-d') . ' ';
                        if ($itemEmp->start_time1) {
                            $workingTimeDate[$itemEmp->id][$startDateWT->format('Y-m-d')] = [
                               'morningInSetting' => Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->start_time1),
                               'morningOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->end_time1),
                               'afternoonInSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->start_time2),
                               'afternoonOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $itemEmp->end_time2),
                            ];
                        } else {
                            $workingTimeDate[$itemEmp->id][$startDateWT->format('Y-m-d')] = [
                                'morningInSetting' => Carbon::createFromFormat('Y-m-d H:i', $dateKey . $workingTimeDefault['morningInSetting']->format('H:i')),
                                'morningOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $workingTimeDefault['morningOutSetting']->format('H:i')),
                                'afternoonInSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $workingTimeDefault['afternoonInSetting']->format('H:i')),
                                'afternoonOutSetting' =>  Carbon::createFromFormat('Y-m-d H:i', $dateKey . $workingTimeDefault['afternoonOutSetting']->format('H:i')),
                            ];
                        }
                        $workingTimeQuaterDate[$itemEmp->id][$startDateWT->format('Y-m-d')] = $objView->getTimeWorkingQuater($itemEmp, $teamCodePrefix, $startDateWT->format('Y-m-d'));
                        $startDateWT->addDay();
                    }
                }
            }
            $dataEmpdate = [];
            while (strtotime($timekeepingTableStartDate) <= strtotime($timekeepingTableEndDate)) {
                $dateTimekeeping = Carbon::parse($timekeepingTableStartDate);
                $dateKeySet = $dateTimekeeping->format('Y-m-d');
                $isHoliday = ManageTimeCommon::isHoliday($dateTimekeeping, $annualHolidays, $specialHolidays, $teamCodePrefix);
                $isWeekend = ManageTimeCommon::isWeekend($dateTimekeeping, $compensationDays);
                $isWeekendOrHoliday = $isWeekend || $isHoliday;
                if (count($businessTripRegister)) {
                    foreach ($businessTripRegister as $item) {
                        if ($item->leave_date && Carbon::parse($item->leave_date)->lt($dateTimekeeping)) {
                            continue;
                        }
                        if ($teamCodePrefix == Team::CODE_PREFIX_JP) {
                            continue;
                        }

                        $workingTime = $this->setWorkingTimeOfEmployee($workingTimeDefault);
                        if (isset($workingTimeDate[$item->employee_id]) &&
                            isset($workingTimeDate[$item->employee_id][$dateKeySet])) {
                            $workingTime = $this->setWorkingTimeOfEmployee($workingTimeDate[$item->employee_id][$dateKeySet]);
                        }

                        $dateTimeStartWorking = clone $dateTimekeeping;
                        $dateTimeStartWorking->setTime($workingTime['morningInSetting']->hour, $workingTime['morningInSetting']->minute);
                        $dateTimeEndWorking = clone $dateTimekeeping;
                        $dateTimeEndWorking->setTime($workingTime['afternoonOutSetting']->hour, $workingTime['afternoonOutSetting']->minute);
                        $businessTripDateStart = Carbon::parse($item->start_at);
                        $businessTripDateEnd = Carbon::parse($item->end_at);
                        $getDate = ManageTimeView::setDateApplicationByTableType(
                            $timeKeepingTable->type,
                            $businessTripDateStart,
                            $businessTripDateEnd,
                            $item->trial_date,
                            $item->offcial_date
                        );
                        if ($getDate['continue']) {
                            continue;
                        }
                        $businessTripDateStart = $getDate['start'];
                        $businessTripDateEnd = $getDate['end'];
                        if (($dateKeySet == $businessTripDateStart->format('Y-m-d') && !$this->isTimeInWorkingTime($businessTripDateStart->format('H:i'), $workingTime)) ||
                            ($dateKeySet == $businessTripDateEnd->format('Y-m-d') && !$this->isTimeInWorkingTime($businessTripDateEnd->format('H:i'), $workingTime))) {
                                continue;
                            }
                        if ($businessTripDateStart < $dateTimeEndWorking && $businessTripDateEnd > $dateTimeStartWorking) {
                            $dataInsertBusinessTrip = [];
                            $dataInsertBusinessTrip['timekeeping_table_id'] = $timekeepingTableId;
                            if ($isWeekendOrHoliday) {
                                continue;
                            }
                            $timeBusinessTrip = 0;
                            $businessTripCreator = $item->employee_id;
                            $keyBusinessTrip = $businessTripCreator . '-' . $dateTimekeeping->format('Y-m-d');

                            $dataInsertBusinessTrip['employee_id'] = $businessTripCreator;
                            $dataInsertBusinessTrip['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');

                            $diffAndSession = static::getDiffTimesOfTimeKeeping($businessTripDateStart, $businessTripDateEnd, $dateTimekeeping, $teamCodePrefix, $workingTime);
                            $timeBusinessTrip = $diffAndSession['diff'];
                            $dataInsertBusinessTrip['has_business_trip'] = $diffAndSession['session'];

                            if (isset($dataBussinessTrip[$keyBusinessTrip])) {
                                $dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'] = $dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'] + $timeBusinessTrip ;
                                if ($dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'] >= 1) {
                                    $dataBussinessTrip[$keyBusinessTrip]['has_business_trip'] = ManageTimeConst::HAS_BUSINESS_TRIP_FULL_DAY;
                                }
                            } else {
                                $dataInsertBusinessTrip['register_business_trip_number'] = $timeBusinessTrip;
                                $dataBussinessTrip[$keyBusinessTrip] = $dataInsertBusinessTrip;
                            }
                            if (isset($dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'])) {
                                $dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'] = round($dataBussinessTrip[$keyBusinessTrip]['register_business_trip_number'], 2);
                            }
                        }
                    }
                }

                //Day off analysis
                if (count($leaveDayRegister)) {
                    foreach ($leaveDayRegister as $item) {
                        if ($item->leave_date && Carbon::parse($item->leave_date)->lt($dateTimekeeping)) {
                            continue;
                        }

                        $workingTime = $this->setWorkingTimeOfEmployee($workingTimeDefault);
                        if (isset($workingTimeDate[$item->creator_id]) &&
                            isset($workingTimeDate[$item->creator_id][$dateKeySet])) {
                            $workingTime = $this->setWorkingTimeOfEmployee($workingTimeDate[$item->creator_id][$dateKeySet]);
                        }
                        $leaveDayDateStart = Carbon::parse($item->date_start);
                        $leaveDayDateEnd = Carbon::parse($item->date_end);
                        $dateTimeStartWorking = clone $dateTimekeeping;
                        $dateTimeStartWorking->setTime($workingTime['morningInSetting']->hour, $workingTime['morningInSetting']->minute);
                        $dateTimeEndWorking = clone $dateTimekeeping;
                        $dateTimeEndWorking->setTime($workingTime['afternoonOutSetting']->hour, $workingTime['afternoonOutSetting']->minute);
                        $getDate = ManageTimeView::setDateApplicationByTableType(
                            $timeKeepingTable->type,
                            $leaveDayDateStart,
                            $leaveDayDateEnd,
                            $item->trial_date,
                            $item->offcial_date
                        );
                        if ($getDate['continue']) {
                            continue;
                        }
                        $leaveDayDateStart = $getDate['start'];
                        $leaveDayDateEnd = $getDate['end'];

                        if ($leaveDayDateStart < $dateTimeEndWorking && $leaveDayDateEnd > $dateTimeStartWorking) {
                            $dataInsertLeaveDay = [];
                            $dataInsertLeaveDay['timekeeping_table_id'] = $timekeepingTableId;
                            $leaveDayCreator = $item->creator_id;
                            $keyLeaveDay = $leaveDayCreator . '-' . $dateTimekeeping->format('Y-m-d');
                            $dataInsertLeaveDay['employee_id'] = $leaveDayCreator;
                            $dataInsertLeaveDay['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');
                            if ($isWeekendOrHoliday) {
                                // no salary holiday
                                if (in_array($item->reason_id, $idLeaveReasonNoSalaries) && $isHoliday) {
                                    $dataLeaveDay[$keyLeaveDay] = $dataInsertLeaveDay;
                                    $dataLeaveDay[$keyLeaveDay]['no_salary_holiday'] = ManageTimeConst::FULL_TIME;
                                }
                                continue;
                            }

                            if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                if (isset($workingTimeQuaterDate[$item->creator_id]) &&
                                    isset($workingTimeQuaterDate[$item->creator_id][$dateKeySet])) {
                                        $timeQuater = $workingTimeQuaterDate[$item->creator_id][$dateKeySet];
                                } else {
                                    $timeQuater = $objView->getTimeWorkingQuater([], $teamCodePrefix, $dateKeySet);
                                }
                                if (($dateKeySet == $leaveDayDateStart->format('Y-m-d') && !$this->isTimeInWorkingTime($leaveDayDateStart->format('H:i'), $timeQuater['timeIn'])) ||
                                    ($dateKeySet == $leaveDayDateEnd->format('Y-m-d') && !$this->isTimeInWorkingTime($leaveDayDateEnd->format('H:i'), $timeQuater['timeOut']))) {
                                        continue;
                                    }
                                $diffAndSession = with(new TimekeepingController())->getDiffTimesOfTimeKeepingRegisterWTK($leaveDayDateStart, $leaveDayDateEnd, $dateKeySet, $workingTime, $timekeepingTableId, $item->creator_id, $timeQuater);
                                $diffAndSession['timekeeping_number_register'] = round($diffAndSession['timekeeping_number_register'], 2);
                                // fix một số TH sẽ cần làm tròn và một số TH ko cần làm tròn
                                // lấy các TH nghỉ 1/4 và có đăng ký thay đổi giờ
                                if (//(!empty($diffAndSession['timekeeping_number_register'])) &&  || bỏ vì áp dụng cho TH phép sáng và BSC chiều và ngược lại
                                    $diffAndSession['timekeeping_number_register'] != 0.25 &&
                                    $diffAndSession['timekeeping_number_register'] != 0.75) {
                                    $dataEmpdate[$item->creator_id][] = $dateKeySet;
                                }

                                $workingTime = $this->setWorkingTimeOfEmployee($workingTimeDefault);
                                if (isset($workingTimeDate[$item->creator_id]) &&
                                    isset($workingTimeDate[$item->creator_id][$dateKeySet])) {
                                    $workingTime = $this->setWorkingTimeOfEmployee($workingTimeDate[$item->creator_id][$dateKeySet]);
                                }
                            } else {
                                $diffAndSession = static::getDiffTimesOfTimeKeeping($leaveDayDateStart, $leaveDayDateEnd, $dateTimekeeping, $teamCodePrefix, $workingTime);
                            }
                            $timeLeaveDay = $diffAndSession['diff'];
                            if ($teamCodePrefix == Team::CODE_PREFIX_JP) {
                                if ($timeLeaveDay < 1 && $timeLeaveDay > 0) {
                                    $timeLeaveDay = 0.5;
                                } elseif ($timeLeaveDay >= 1) {
                                    $timeLeaveDay = 1;
                                }
                            }

                            if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                //Gọi hàm chỉ để kiểm tra lại số phút đi muộn
                                //Bằng cách truyền vào tham số $onlyCheckLate = true
                                $diffAndSessionCheckLate = with(new TimekeepingController())->getDiffTimesOfTimeKeepingRegisterWTK($leaveDayDateStart, $leaveDayDateEnd, $dateKeySet, $workingTime, $timekeepingTableId, $item->creator_id, $timeQuater, true);
                                if (isset($diffAndSessionCheckLate['timeLateStart'])) {
                                    if ($diffAndSessionCheckLate['timeLateStart'] === ManageTimeConst::RESET) {
                                        $dataInsertLeaveDay['late_start_shift'] = 0;
                                    } elseif ($diffAndSessionCheckLate['timeLateStart'] != 0) {
                                        $diffAndSessionCheckLate['late_start_shift'] = $diffAndSessionCheckLate['timeLateStart'];
                                        if (isset($dataNotLate[$item->email]) && isset($dataNotLate[$item->email][$dateTimekeeping->format('Y-m-d')]) &&
                                            $diffAndSessionCheckLate['timeLateStart'] > 0 &&
                                            $diffAndSessionCheckLate['timeLateStart'] <= ManageTimeConst::MAX_TIME_LATE_IN_EARLY_OUT) {
                                            $dataInsertLeaveDay['late_start_shift'] = 0;
                                        }
                                    }
                                }
                                if (isset($diffAndSession['timeLateMid'])) {
                                    if ($diffAndSession['timeLateMid'] === ManageTimeConst::RESET) {
                                        $dataInsertLeaveDay['late_mid_shift'] = 0;
                                    } elseif ($diffAndSession['timeLateMid'] !== 0) {
                                        $dataInsertLeaveDay['late_mid_shift'] = $diffAndSession['timeLateMid'];
                                    }
                                }
                                if (isset($diffAndSession['timeEarlyMid'])) {
                                    if ($diffAndSession['timeEarlyMid'] === ManageTimeConst::RESET) {
                                        $dataInsertLeaveDay['early_mid_shift'] = 0;
                                    } elseif ($diffAndSession['timeEarlyMid'] != 0) {
                                        $dataInsertLeaveDay['early_mid_shift'] = $diffAndSession['timeEarlyMid'];
                                    } else {
                                    }
                                }
                                if (isset($diffAndSession['timeEarlyEnd'])) {
                                    if ($diffAndSession['timeEarlyEnd'] === ManageTimeConst::RESET) {
                                        $dataInsertLeaveDay['early_end_shift'] = 0;
                                    } elseif ($diffAndSession['timeEarlyEnd'] != 0) {
                                        $dataInsertLeaveDay['early_end_shift'] = $diffAndSession['timeEarlyEnd'];
                                    }
                                }
                            }
                            if (isset($dataLeaveDay[$keyLeaveDay])) {
                                if ($diffAndSession['session'] < 0.5 && $leaveDayDateEnd->hour <= $workingTime['morningOutSetting']->hour) {
                                    $sessions = ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF;
                                } elseif ($diffAndSession['session'] < 0.5 && $leaveDayDateEnd->hour > $workingTime['morningOutSetting']->hour) {
                                    $sessions = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF;
                                } else {
                                   $sessions = $diffAndSession['session'];
                                }
                                if (ManageTimeView::isMorningTime($leaveDayDateStart ->hour)) {
                                    $sign = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
                                } else {
                                    $sign = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
                                }
                                if ($item->salary_rate != ManageTimeConst::NOT_SALARY) {
                                    if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'])) {
                                        if ($timeLeaveDay < 0.5 &&
                                            $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] < 0.5 &&
                                            $dataLeaveDay[$keyLeaveDay]['sign_leave_day_salary'] == $sign) {
                                            $dataLeaveDay[$keyLeaveDay]['has_leave_day'] = $sign;
                                        } else {
                                            $dataLeaveDay[$keyLeaveDay]['has_leave_day'] = $diffAndSession['session'];
                                        }

                                        if ($item->type_reasons == LeaveDayReason::BASIC_TYPE) {
                                            if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_basic_salary'])) {
                                                $dataLeaveDay[$keyLeaveDay]['register_leave_basic_salary'] = $dataLeaveDay[$keyLeaveDay]['register_leave_basic_salary'] + $timeLeaveDay;
                                            } else {
                                                $dataLeaveDay[$keyLeaveDay]['register_leave_basic_salary'] = $timeLeaveDay;
                                            }

                                        }

                                        $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] = $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] + $timeLeaveDay;
                                        if ($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] >= 1) {
                                            $dataLeaveDay[$keyLeaveDay]['has_leave_day'] = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
                                        }
                                    } else {
                                        $dataLeaveDay[$keyLeaveDay]['has_leave_day'] = $sessions;

                                        if ($item->type_reasons == LeaveDayReason::BASIC_TYPE) {
                                            $dataLeaveDay[$keyLeaveDay]['register_leave_basic_salary'] = $timeLeaveDay;
                                        }

                                        $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] = $timeLeaveDay;
                                        if (ManageTimeView::isMorningTime($leaveDayDateStart ->hour)) {
                                            $dataLeaveDay[$keyLeaveDay]['sign_leave_day_salary'] = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
                                        } else {
                                            $dataLeaveDay[$keyLeaveDay]['sign_leave_day_salary'] = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
                                        }
                                    }
                                } else {
                                    if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'])) {
                                        if ($timeLeaveDay < 0.5 &&
                                            $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] < 0.5 &&
                                            $dataLeaveDay[$keyLeaveDay]['sign_leave_day_no_salary'] == $sign) {
                                            $dataLeaveDay[$keyLeaveDay]['has_leave_day_no_salary'] = $sign;
                                        } else {
                                            $dataLeaveDay[$keyLeaveDay]['has_leave_day_no_salary'] = $diffAndSession['session'];
                                        }
                                        $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] = $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] + $timeLeaveDay;
                                        if ($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] >= 1) {
                                            $dataLeaveDay[$keyLeaveDay]['has_leave_day_no_salary'] = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
                                        }
                                    } else {
                                        $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] = $timeLeaveDay;
                                        $dataLeaveDay[$keyLeaveDay]['has_leave_day_no_salary'] = $sessions;
                                        if (ManageTimeView::isMorningTime($leaveDayDateStart ->hour)) {
                                            $dataLeaveDay[$keyLeaveDay]['sign_leave_day_no_salary'] = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
                                        } else {
                                            $dataLeaveDay[$keyLeaveDay]['sign_leave_day_no_salary'] = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
                                        }
                                    }
                                }
                                if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                    if (isset($dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'])) {
                                        $dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'] = $dataLeaveDay[$keyLeaveDay]['timekeeping_number_register'] + $diffAndSession['timekeeping_number_register'];
                                    } else {
                                        $dataInsertLeaveDay['timekeeping_number_register'] = $diffAndSession['timekeeping_number_register'];
                                    }
                                }
                                if (isset($dataInsertLeaveDay['late_start_shift'])) {
                                    $dataLeaveDay[$keyLeaveDay]['late_start_shift'] = $dataInsertLeaveDay['late_start_shift'];
                                }
                                if (isset($dataInsertLeaveDay['late_mid_shift'])) {
                                    $dataLeaveDay[$keyLeaveDay]['late_mid_shift'] = $dataInsertLeaveDay['late_mid_shift'];
                                }
                                if (isset($dataInsertLeaveDay['early_mid_shift'])) {
                                    $dataLeaveDay[$keyLeaveDay]['early_mid_shift'] = $dataInsertLeaveDay['early_mid_shift'];
                                }
                                if (isset($dataInsertLeaveDay['early_end_shift'])) {
                                    $dataLeaveDay[$keyLeaveDay]['early_end_shift'] = $dataInsertLeaveDay['early_end_shift'];
                                }
                            } else {
                                if ($diffAndSession['session'] < 0.5 && $leaveDayDateEnd->hour <= $workingTime['morningOutSetting']->hour) {
                                    $sessions = ManageTimeConst::HAS_LEAVE_DAY_MORNING_HALF;
                                } elseif ($diffAndSession['session'] < 0.5 && $leaveDayDateEnd->hour > $workingTime['morningOutSetting']->hour) {
                                    $sessions = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON_HALF;
                                 } else {
                                    $sessions = $diffAndSession['session'];
                                 }
                                // if ($item->used_leave_day == ManageTimeConst::USED_LEAVE_DAY) {
                                if ($item->salary_rate != ManageTimeConst::NOT_SALARY) {
                                    $dataInsertLeaveDay['has_leave_day'] = $sessions;

                                    if ($item->type_reasons == LeaveDayReason::BASIC_TYPE) {
                                        $dataInsertLeaveDay['register_leave_basic_salary'] = $timeLeaveDay;
                                    }
                                    $dataInsertLeaveDay['register_leave_has_salary'] = $timeLeaveDay;
                                    if (ManageTimeView::isMorningTime($leaveDayDateStart ->hour)) {
                                        $dataInsertLeaveDay['sign_leave_day_salary'] =  ManageTimeConst::HAS_LEAVE_DAY_MORNING;;
                                    } else {
                                        $dataInsertLeaveDay['sign_leave_day_salary'] =  ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;;
                                    }
                                } else {
                                    $dataInsertLeaveDay['has_leave_day_no_salary'] = $sessions;
                                    $dataInsertLeaveDay['register_leave_no_salary'] = $timeLeaveDay;
                                    if (ManageTimeView::isMorningTime($leaveDayDateStart ->hour)) {
                                        $dataInsertLeaveDay['sign_leave_day_no_salary'] =  ManageTimeConst::HAS_LEAVE_DAY_MORNING;;
                                    } else {
                                        $dataInsertLeaveDay['sign_leave_day_no_salary'] =  ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;;
                                    }
                                }
                                if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                    if (isset($diffAndSession['timekeeping_number_register'])) {
                                        $dataInsertLeaveDay['timekeeping_number_register'] = $diffAndSession['timekeeping_number_register'];
                                    }
                                }
                                $dataLeaveDay[$keyLeaveDay] = $dataInsertLeaveDay;
                            }
                            if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'])) {
                                if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                    $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] = round($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'], 2);
                                } else {
                                    $dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'] = round($dataLeaveDay[$keyLeaveDay]['register_leave_has_salary'], 1);
                                }
                            }
                            if (isset($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'])) {
                                if (CoreConfigData::checkBranchRegister(Employee::getEmpById($item->creator_id))) {
                                    $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] = round($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'], 2);
                                } else {
                                    $dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'] = round($dataLeaveDay[$keyLeaveDay]['register_leave_no_salary'], 1);
                                }
                            }
                        }
                    }
                }

                // Supplement analysis
                if (count($supplementRegister)) {
                    foreach ($supplementRegister as $item) {
                        if ($item->leave_date && Carbon::parse($item->leave_date)->lt($dateTimekeeping)) {
                            continue;
                        }
                        
                        $workingTime = $this->setWorkingTimeOfEmployee($workingTimeDefault);
                        if (isset($workingTimeDate[$item->employee_id]) &&
                            isset($workingTimeDate[$item->employee_id][$dateKeySet])) {
                            $workingTime = $this->setWorkingTimeOfEmployee($workingTimeDate[$item->employee_id][$dateKeySet]);
                        }

                        $dateTimeStartWorking = clone $dateTimekeeping;
                        $dateTimeStartWorking->setTime($workingTime['morningInSetting']->hour, $workingTime['morningInSetting']->minute);
                        $dateTimeEndWorking = clone $dateTimekeeping;
                        $dateTimeEndWorking->setTime($workingTime['afternoonOutSetting']->hour, $workingTime['afternoonOutSetting']->minute);
                        $supplementDateStart = Carbon::parse($item->start_at);
                        $supplementDateEnd = Carbon::parse($item->end_at);

                        $getDate = ManageTimeView::setDateApplicationByTableType(
                            $timeKeepingTable->type,
                            $supplementDateStart,
                            $supplementDateEnd,
                            $item->trial_date,
                            $item->offcial_date
                        );
                        if ($getDate['continue']) {
                            continue;
                        }
                        $supplementDateStart = $getDate['start'];
                        $supplementDateEnd = $getDate['end'];
                        if (($dateKeySet == $supplementDateStart->format('Y-m-d') && !$this->isTimeInWorkingTime($supplementDateStart->format('H:i'), $workingTime)) ||
                            ($dateKeySet == $supplementDateEnd->format('Y-m-d') && !$this->isTimeInWorkingTime($supplementDateEnd->format('H:i'), $workingTime))) {
                                continue;
                            }
                        if ($supplementDateStart < $dateTimeEndWorking && $supplementDateEnd > $dateTimeStartWorking) {
                            $dataInsertSupplement = [];
                            $dataInsertSupplement['timekeeping_table_id'] = $timekeepingTableId;
                            if ($isWeekendOrHoliday) {
                                continue;
                            }
                            $timeSupplement = 0;
                            $supplementCreator = $item->employee_id;
                            $keySupplement = $supplementCreator . '-' . $dateTimekeeping->format('Y-m-d');

                            $dataInsertSupplement['employee_id'] = $supplementCreator;
                            $dataInsertSupplement['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');

                            $diffAndSession = static::getDiffTimesOfTimeKeeping($supplementDateStart, $supplementDateEnd, $dateTimekeeping, $teamCodePrefix, $workingTime);
                            $timeSupplement = $diffAndSession['diff'];
                            $dataInsertSupplement['has_supplement'] = $diffAndSession['session'];

                            if (isset($dataSupplement[$keySupplement])) {
                                $dataSupplement[$keySupplement]['register_supplement_number'] = $dataSupplement[$keySupplement]['register_supplement_number'] + $timeSupplement;
                                if ($dataSupplement[$keySupplement]['register_supplement_number'] >= 1) {
                                    $dataSupplement[$keySupplement]['has_supplement'] = ManageTimeConst::HAS_SUPPLEMENT_FULL_DAY;
                                }
                            } else {
                                $dataInsertSupplement['register_supplement_number'] = $timeSupplement;
                                $dataSupplement[$keySupplement] = $dataInsertSupplement;
                            }
                            if (isset($dataSupplement[$keySupplement]['register_supplement_number'])) {
                                $dataSupplement[$keySupplement]['register_supplement_number'] = round($dataSupplement[$keySupplement]['register_supplement_number'], 2);
                            }
                        }
                    }
                }

                // OT analysis
                if (count($overtimeRegister)) {
                    foreach ($overtimeRegister as $item) {
                        if ($item->leave_date && Carbon::parse($item->leave_date)->lt($dateTimekeeping)) {
                            continue;
                        }
                        $overtimeDateStart = Carbon::parse($item->start_at);
                        $overtimeDateEnd = Carbon::parse($item->end_at);
                        $getDate = ManageTimeView::setDateApplicationByTableType(
                            $timeKeepingTable->type,
                            $overtimeDateStart,
                            $overtimeDateEnd,
                            $item->trial_date,
                            $item->offcial_date
                        );
                        if ($getDate['continue']) {
                            continue;
                        }
                        $overtimeDateStart = $getDate['start'];
                        $overtimeDateEnd = $getDate['end'];

                        if ($overtimeDateStart->format('Y-m-d') <= $dateTimekeeping->format('Y-m-d')
                                && $dateTimekeeping->format('Y-m-d') <= $overtimeDateEnd->format('Y-m-d')) {
                            $overtimeEmployee = $item->employee_id;
                            $keyOvertime = $overtimeEmployee . '-' . $dateTimekeeping->format('Y-m-d');

                            // Get timekeeping of employee in table timekeeping
                            $month = $dateTimekeeping->format('Y-m') . '-01';
                            $timekeepingOfEmployee = Timekeeping::select(
                                    'manage_time_timekeepings.id as timekeeping_id',
                                    'start_time_morning_shift',
                                    'end_time_morning_shift',
                                    'start_time_afternoon_shift',
                                    'end_time_afternoon_shift',
                                    DB::raw('null as start_time1'),
                                    DB::raw('null as end_time1'),
                                    DB::raw('null as start_time2'),
                                    DB::raw('null as end_time2')
                                )
                                ->where('timekeeping_table_id', $timekeepingTableId)
                                ->where('manage_time_timekeepings.employee_id', $overtimeEmployee)
                                ->where('timekeeping_date', $dateTimekeeping->format('Y-m-d'))
                                ->first();
                            if (!$timekeepingOfEmployee) {
                                continue;
                            }
                            $timeSettingOfEmp = $this->setWorkingTimeOfEmployee($workingTimeDefault);
                            if (isset($workingTimeDate[$item->employee_id]) &&
                                isset($workingTimeDate[$item->employee_id][$dateKeySet])) {
                                $timeSettingOfEmp = $this->setWorkingTimeOfEmployee($workingTimeDate[$item->employee_id][$dateKeySet]);
                            }

                            if (empty($timekeepingOfEmployee->start_time_morning_shift) && 
                                !empty($timekeepingOfEmployee->end_time_morning_shift) &&
                                !empty($timekeepingOfEmployee->end_time_afternoon_shift)) {
                                    $timekeepingOfEmployee->start_time_afternoon_shift = $timeSettingOfEmp['afternoonInSetting']->format('H:i');
                            }
                                
                            if (isset($workingTimeDate[$overtimeEmployee]) &&
                                isset($workingTimeDate[$overtimeEmployee][$dateTimekeeping->format('Y-m-d')])) {
                                    $wt = $workingTimeDate[$overtimeEmployee][$dateTimekeeping->format('Y-m-d')];
                                    $timekeepingOfEmployee->start_time1 = $wt['morningInSetting']->format('H:i');
                                    $timekeepingOfEmployee->end_time1 = $wt['morningOutSetting']->format('H:i');
                                    $timekeepingOfEmployee->start_time2 = $wt['afternoonInSetting']->format('H:i');
                                    $timekeepingOfEmployee->end_time2 = $wt['afternoonOutSetting']->format('H:i');
                            } else {
                                $timekeepingOfEmployee->start_time1 = $workingTimeDefault['morningInSetting']->format('H:i');
                                $timekeepingOfEmployee->end_time1 = $workingTimeDefault['morningOutSetting']->format('H:i');
                                $timekeepingOfEmployee->start_time2 = $workingTimeDefault['afternoonInSetting']->format('H:i');
                                $timekeepingOfEmployee->end_time2 = $workingTimeDefault['afternoonOutSetting']->format('H:i');
                            }
                            // $timeSettingOfEmp = ManageTimeView::findTimeSetting($timekeepingOfEmployee, $teamCodePrefix, $rangeTimes);

                            $dataInsertOT = [];
                            $dataInsertOT['timekeeping_table_id'] = $timekeepingTableId;
                            $dataInsertOT['employee_id'] = $overtimeEmployee;
                            $dataInsertOT['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');
                            $timeOvertime = 0;
                            $registerOvertime = 0;
                            $overtimeTimeBreak = 0;
                            $overtimeIsPaid = 0;
                            $timeAddLeaveDay = 0;
                            // Check Supplement for OT
                            if (!empty($supplementOTGroup[$overtimeEmployee])) {
                                $dateCompare = Carbon::parse($dateTimekeeping->format('Y-m-d') . ' ' . $timeSettingOfEmp['morningOutSetting']->format('H:i:s'));
                                foreach ($supplementOTGroup[$overtimeEmployee] as $itemSupOt) {
                                    $dateSupOtStart = Carbon::parse($itemSupOt->start_at);
                                    $dateSupOtEnd = Carbon::parse($itemSupOt->end_at);

                                    $getDate = ManageTimeView::setDateApplicationByTableType(
                                        $timeKeepingTable->type,
                                        $dateSupOtStart,
                                        $dateSupOtEnd,
                                        $item->trial_date,
                                        $item->offcial_date
                                    );
                                    if ($getDate['continue']) {
                                        continue;
                                    }
                                    $dateSupOtStart = $getDate['start'];
                                    $dateSupOtEnd = $getDate['end'];

                                    if ($dateSupOtStart->format('Y-m-d') <= $dateTimekeeping->format('Y-m-d') && $dateSupOtEnd->format('Y-m-d') >= $dateTimekeeping->format('Y-m-d')) {
                                        if ($dateSupOtStart->format('Y-m-d') < $dateTimekeeping->format('Y-m-d')) {
                                            $timekeepingOfEmployee->start_time_morning_shift = $timeSettingOfEmp['morningInSetting']->format('H:i');
                                        } else {
                                            if ($dateSupOtStart->format('Y-m-d H:i:s') < $dateCompare->format('Y-m-d H:i:s')) {
                                                if (empty($timekeepingOfEmployee->start_time_morning_shift) || $timekeepingOfEmployee->start_time_morning_shift > $dateSupOtStart->format('H:i')) {
                                                    $timekeepingOfEmployee->start_time_morning_shift = $dateSupOtStart->format('H:i');
                                                }
                                            } else {
                                                if (empty($timekeepingOfEmployee->start_time_afternoon_shift) || $timekeepingOfEmployee->start_time_afternoon_shift > $dateSupOtStart->format('H:i')) {
                                                    $timekeepingOfEmployee->start_time_afternoon_shift = $dateSupOtStart->format('H:i');
                                                }
                                            }
                                        }
                                        if ($dateSupOtEnd->format('Y-m-d') > $dateTimekeeping->format('Y-m-d')) {
                                            if (empty($timekeepingOfEmployee->end_time_afternoon_shift) || $timekeepingOfEmployee->end_time_afternoon_shift < $timeSettingOfEmp['afternoonOutSetting']->format('H:i')) {
                                                $timekeepingOfEmployee->end_time_afternoon_shift = $timeSettingOfEmp['afternoonOutSetting']->format('H:i');
                                            }
                                        } else {
                                            if ($dateSupOtEnd->format('Y-m-d H:i:s') <= $dateCompare->format('Y-m-d H:i:s')) {
                                                if (empty($timekeepingOfEmployee->end_time_morning_shift) || $timekeepingOfEmployee->end_time_morning_shift < $dateSupOtEnd->format('H:i')) {
                                                    $timekeepingOfEmployee->end_time_morning_shift = $dateSupOtEnd->format('H:i');
                                                }
                                            } else {
                                                if (empty($timekeepingOfEmployee->end_time_afternoon_shift) || $timekeepingOfEmployee->end_time_afternoon_shift < $dateSupOtEnd->format('H:i')) {
                                                    $timekeepingOfEmployee->end_time_afternoon_shift = $dateSupOtEnd->format('H:i');
                                                }
                                            }
                                        }
                                        Timekeeping::where('id', $timekeepingOfEmployee->timekeeping_id)
                                            ->update([
                                                'start_time_morning_shift' => $timekeepingOfEmployee->start_time_morning_shift,
                                                'end_time_morning_shift' => $timekeepingOfEmployee->end_time_morning_shift,
                                                'start_time_afternoon_shift' => $timekeepingOfEmployee->start_time_afternoon_shift,
                                                'end_time_afternoon_shift' => $timekeepingOfEmployee->end_time_afternoon_shift,
                                            ]);
                                    }
                                }
                            }

                            if ($item->is_onsite) {
                                $startTimeMorningShift = $overtimeDateStart->format('H:i');
                                $endTimeMorningShift = null;
                                $startTimeAfternoonShift = null;
                                $endTimeAfternoonShift = $overtimeDateEnd->format('H:i');
                            } else {
                                $startTimeMorningShift = $timekeepingOfEmployee->start_time_morning_shift;
                                $endTimeMorningShift = $timekeepingOfEmployee->end_time_morning_shift;
                                $startTimeAfternoonShift = $timekeepingOfEmployee->start_time_afternoon_shift;
                                $endTimeAfternoonShift = $timekeepingOfEmployee->end_time_afternoon_shift;
                            }

                            $overtimeStartAt = $overtimeDateStart->toTimeString();
                            if ($dateTimekeeping->format('Y-m-d') !== $overtimeDateStart->format('Y-m-d')) {
                                if ($isWeekendOrHoliday) {
                                    $overtimeStartAt = $timekeepingOfEmployee->start_time1 . ':00';
                                } else {
                                    $dateEndOver = Carbon::createFromFormat("H:i", $timekeepingOfEmployee->end_time2);
                                    $overtimeStartAt = $dateEndOver->addHour()->format('H:i:s');
                                }
                            }
                            $overtimeEndAt = $overtimeDateEnd->toTimeString();
                            if ($dateTimekeeping->format('Y-m-d') !== $overtimeDateEnd->format('Y-m-d')) {
                                $overtimeEndAt = "22:00:00";
                            }

                            $overtimeTimeBreak = $item->time_break;
                            $overtimeIsPaid = $item->is_paid;
                            $overtimeStartAtStrtotime = strtotime($overtimeStartAt);
                            $overtimeEndAtStrtotime = strtotime($overtimeEndAt);
                            $startTimeMorningShiftStrtotime = 0;
                            $endTimeMorningShiftStrtotime = 0;
                            $startTimeAfternoonShiftStrtotime = 0;
                            $endTimeAfternoonShiftStrtotime = 0;

                            if ($startTimeMorningShift) {
                                $startTimeMorningShiftStrtotime = strtotime($startTimeMorningShift);
                            }
                            if ($endTimeMorningShift) {
                                $endTimeMorningShiftStrtotime = strtotime($endTimeMorningShift);
                            }
                            if ($startTimeAfternoonShift) {
                                $startTimeAfternoonShiftStrtotime = strtotime($startTimeAfternoonShift);
                            }
                            if ($endTimeAfternoonShift) {
                                $endTimeAfternoonShiftStrtotime = strtotime($endTimeAfternoonShift);
                            }

                            if ($isWeekendOrHoliday) {
                                if ((!$startTimeMorningShift && !$startTimeAfternoonShift) || (!$endTimeMorningShift && !$endTimeAfternoonShift && !$startTimeAfternoonShift)) {
                                    $timeOvertime = 0;
                                    continue;
                                } else {
                                    if (($endTimeAfternoonShiftStrtotime > 0) && ($endTimeAfternoonShiftStrtotime < $overtimeStartAtStrtotime)) {
                                        $timeOvertime = 0;
                                        continue;
                                    } else {
                                        if ((!$startTimeMorningShift) && (!$startTimeAfternoonShift)) {
                                            $timeOvertime = 0;
                                            continue;
                                        } elseif ($startTimeMorningShift) {
                                            if ($overtimeStartAtStrtotime < $startTimeMorningShiftStrtotime) {
                                                $overtimeStartAtStrtotime = $startTimeMorningShiftStrtotime;
                                            }
                                            if ($endTimeAfternoonShift) {
                                                if ($overtimeEndAtStrtotime > $endTimeAfternoonShiftStrtotime) {
                                                    $overtimeEndAtStrtotime = $endTimeAfternoonShiftStrtotime;
                                                }
                                            } elseif ($startTimeAfternoonShift) {
                                                if ($overtimeEndAtStrtotime > $startTimeAfternoonShiftStrtotime) {
                                                    $overtimeEndAtStrtotime = $startTimeAfternoonShiftStrtotime;
                                                }
                                            } elseif ($endTimeMorningShift) {
                                                if ($overtimeEndAtStrtotime > $endTimeMorningShiftStrtotime) {
                                                    $overtimeEndAtStrtotime = $endTimeMorningShiftStrtotime;
                                                }
                                            }
                                            $timeOvertime = ($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600;
                                        } elseif ((!$startTimeMorningShift) && $startTimeAfternoonShift) {
                                            if ($startTimeAfternoonShiftStrtotime > $overtimeStartAtStrtotime) {
                                                $overtimeStartAtStrtotime = $startTimeAfternoonShiftStrtotime;
                                            }
                                            if ($overtimeEndAtStrtotime > $endTimeAfternoonShiftStrtotime) {
                                                $overtimeEndAtStrtotime = $endTimeAfternoonShiftStrtotime;
                                            }
                                            $timeOvertime = ($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600;
                                        }
                                        if ($timeOvertime) {
                                            if ($isHoliday) {
                                                $registerOvertime = ManageTimeConst::IS_OT_ANNUAL_SPECIAL_HOLIDAY;
                                            } else {
                                                $registerOvertime = ManageTimeConst::IS_OT_WEEKEND;
                                            }
                                        }
                                    }
                                }
                            } else {
                                if (!$endTimeAfternoonShift) {
                                    $timeOvertime = 0;
                                    continue;
                                } else {
                                    if ($endTimeAfternoonShiftStrtotime < $overtimeStartAtStrtotime) {
                                        $timeOvertime = 0;
                                        continue;
                                    } else {
                                        if ((!$startTimeMorningShift) && (!$startTimeAfternoonShift)) {
                                            $timeOvertime = 0;
                                            continue;
                                        } elseif ($startTimeMorningShift) {
                                            if ($overtimeEndAtStrtotime > $endTimeAfternoonShiftStrtotime) {
                                                $overtimeEndAtStrtotime = $endTimeAfternoonShiftStrtotime;
                                            }
                                            $timeOvertime = ($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600;
                                            if ($timeOvertime) {
                                                $registerOvertime = ManageTimeConst::IS_OT;
                                            }
                                        } elseif ((!$startTimeMorningShift) && $startTimeAfternoonShift) {
                                            if ($startTimeAfternoonShiftStrtotime > $overtimeStartAtStrtotime) {
                                                $overtimeStartAtStrtotime = $startTimeAfternoonShiftStrtotime;
                                            }
                                            if ($overtimeEndAtStrtotime > $endTimeAfternoonShiftStrtotime) {
                                                $overtimeEndAtStrtotime = $endTimeAfternoonShiftStrtotime;
                                            }
                                            $timeOvertime = ($overtimeEndAtStrtotime - $overtimeStartAtStrtotime) / 3600;
                                            if ($timeOvertime) {
                                                $registerOvertime = ManageTimeConst::IS_OT;
                                            }
                                        }
                                    }
                                }
                            }

                            if ($isWeekendOrHoliday && $overtimeTimeBreak) {
                                $breakTime = OtBreakTime::select('break_time')
                                    ->where('ot_register_id', $item->id)
                                    ->where('employee_id', $item->employee_id)
                                    ->whereDate('ot_date', "=", $dateTimekeeping->format('Y-m-d'))
                                    ->first();

                                if ($breakTime) {
                                    $timeOvertime = $timeOvertime - $breakTime->break_time;
                                }
                            }
                            $timeOvertime = round($timeOvertime, 1);
                            if ($timeOvertime <= 0) {
                                $timeOvertime = 0;
                                $registerOvertime = ManageTimeConst::IS_NOT_OT;
                            } else {
                                // Check if is weekend then add 50% or holiday then add 150% time OT to leave day ot
                                if ($isWeekendOrHoliday && $overtimeIsPaid) {
                                    if ($isHoliday) {
                                        $timeAddLeaveDay = ($timeOvertime * 150) / (100 * 8);
                                    } else {
                                        $timeAddLeaveDay = ($timeOvertime * 50) / (100 * 8);
                                    }
                                    $timeAddLeaveDay = round($timeAddLeaveDay, 1);
                                    if ($timeAddLeaveDay < 0) {
                                        $timeAddLeaveDay = 0;
                                    }
                                }
                            }

                            if (isset($dataOT[$keyOvertime])) {
                                if ($overtimeIsPaid) {
                                    if (isset($dataOT[$keyOvertime]['register_ot_has_salary'])) {
                                        $dataOT[$keyOvertime]['register_ot_has_salary'] += $timeOvertime;
                                    } else {
                                        $dataOT[$keyOvertime]['register_ot_has_salary'] = $timeOvertime;
                                    }
                                    if (isset($dataOT[$keyOvertime]['leave_day_added'])) {
                                        $dataOT[$keyOvertime]['leave_day_added'] += $timeAddLeaveDay;
                                    } else {
                                        $dataOT[$keyOvertime]['leave_day_added'] = $timeAddLeaveDay;
                                    }
                                } else {
                                    if (isset($dataOT[$keyOvertime]['register_ot_no_salary'])) {
                                        $dataOT[$keyOvertime]['register_ot_no_salary'] += $timeOvertime;
                                    } else {
                                        $dataOT[$keyOvertime]['register_ot_no_salary'] = $timeOvertime;
                                    }
                                }
                            } else {
                                $dataInsertOT['register_ot'] = $registerOvertime;
                                if ($overtimeIsPaid) {
                                    $dataInsertOT['register_ot_has_salary'] = $timeOvertime;
                                    $dataInsertOT['leave_day_added'] = $timeAddLeaveDay;
                                } else {
                                    $dataInsertOT['register_ot_no_salary'] = $timeOvertime;
                                }
                                $dataOT[$keyOvertime] = $dataInsertOT;
                            }
                            //Floor OT hours if this is a timekeeping of japan
                            if ($teamCodePrefix === 'japan') {
                                if (isset($dataOT[$keyOvertime]['register_ot_has_salary'])) {
                                    $dataOT[$keyOvertime]['register_ot_has_salary'] = View::floorToFraction($dataOT[$keyOvertime]['register_ot_has_salary'], ManageTimeConst::FLOOR_OT_HOUR_JAPAN);
                                }
                                if (isset($dataOT[$keyOvertime]['register_ot_no_salary'])) {
                                    $dataOT[$keyOvertime]['register_ot_no_salary'] = View::floorToFraction($dataOT[$keyOvertime]['register_ot_no_salary'], ManageTimeConst::FLOOR_OT_HOUR_JAPAN);
                                }
                            }
                        }
                    }
                }

                //time join company
                if (count($getTimeJoinCompany)) {
                    foreach ($getTimeJoinCompany as $item) {
                        if (strtotime($dateTimekeeping->format('Y-m-d')) < strtotime($item->join_date)) {
                            $keyLeaveDay = $item->id . '-' . $dateTimekeeping->format('Y-m-d');
                            $dataJoinCompany[$keyLeaveDay]['employee_id'] = $item->id;
                            $dataJoinCompany[$keyLeaveDay]['timekeeping_date'] = $dateTimekeeping->format('Y-m-d');
                            $dataJoinCompany[$keyLeaveDay]["timekeeping_table_id"] = $timekeepingTableId;

                            foreach ($nameColumnTable as $value) {
                                $dataJoinCompany[$keyLeaveDay][$value] = 0;
                                if ($value == 'sign_fines') {
                                    $dataJoinCompany[$keyLeaveDay][$value] = '-';
                                }
                                
                            }
                        }
                    }
                }
                $timekeepingTableStartDate = date("Y-m-d", strtotime("+1 day", strtotime($timekeepingTableStartDate)));
            }

            //Check đi muộn về sớm
            ManageTimeView::updateEarlyLate($timeKeepingTable);

            if (count($dataBussinessTrip)) {
                $dataTotal = $dataBussinessTrip;
                unset($dataBussinessTrip);
            }
            if (count($dataSupplement)) {
                $dataTotal = static::mergeDataTotalTimeKeeping($dataSupplement, $dataTotal);
                unset($dataSupplement);
            }
            if (count($dataLeaveDay)) {
                foreach ($dataLeaveDay as $key => $value) {
                    if (isset($value['sign_leave_day_salary'])) {
                        unset($dataLeaveDay[$key]['sign_leave_day_salary']);
                    }
                    if (isset($value['sign_leave_day_no_salary'])) {
                        unset($dataLeaveDay[$key]['sign_leave_day_no_salary']);
                    }
                }
                $dataTotal = static::mergeDataTotalTimeKeeping($dataLeaveDay, $dataTotal);
                unset($dataLeaveDay);
            }
            if (count($dataOT)) {
                $dataTotal = static::mergeDataTotalTimeKeeping($dataOT, $dataTotal);
                unset($dataOT);
            }
            if (count($dataJoinCompany)) {
                $dataTotal = static::mergeDataTotalTimeKeeping($dataJoinCompany, $dataTotal, false);
                unset($dataJoinCompany);
            }

            if (count($dataTotal)) {
                // kiểm tra và tính lại giá trị làm tròn khi có nghỉ 1/4 và có đăng ký thay đổi giờ
                if ($dataEmpdate) {
                    $dataTK = $this->getTimeKeepingByarray($dataEmpdate);
                    $dataTotal = $this->setAgainDataTk($dataTotal, $dataTK);
                }

                $viewTimeKeeping =  new ViewTimeKeeping();
                $viewTimeKeeping->updateDataCron($dataTotal, $timekeepingTableId);
            }

            if (count($empsIdOfTimeKeeping) && count($empsIdOfTimeKeeping) < $numberEmployeeAllow) {
                //Tổng hợp công
                $data = [
                    'timekeeping_table_id' => $timekeepingTableId,
                    'team_code' => $teamCodePrefix,
                ];
                $request = new Request($data);
                self::updateTimekeepingAggregate($request, $empsIdOfTimeKeeping);
                with(new Timekeeping())->updateSignTimekeeping($empsIdOfTimeKeeping, $arrDate, $timekeepingTableId, $compensationDays, $arrHolidays);
            }

            // lưu lại thời gian làm việc
            if ($timeKeepingTable->lock_up == TimekeepingTable::CLOSE_LOCK_UP && $empsIdOfTimeKeeping) {
                with(new TimekeepingWorkingTime())->insertWKT($timekeepingTableId, $empsIdOfTimeKeeping, $timekeepingTableStartDate, $timekeepingTableEndDate);
            }

            $objLockHistory = new TimekeepingLockHistories();
            $objLockHistory->updateStartLockHistory($leaveDayRegister->lists('id')->toArray(), $empsIdOfTimeKeeping, TimekeepingLockHistories::TYPE_P);
            $objLockHistory->updateStartLockHistory($supplementRegister->lists('id')->toArray(), $empsIdOfTimeKeeping, TimekeepingLockHistories::TYPE_BSC);
            $objLockHistory->updateStartLockHistory($supplementRegisterOT->lists('id')->toArray(), $empsIdOfTimeKeeping, TimekeepingLockHistories::TYPE_BSC_OT);
            $objLockHistory->updateStartLockHistory(collect($overtimeRegister)->lists('id')->toArray(), $empsIdOfTimeKeeping, TimekeepingLockHistories::TYPE_OT);
            $objLockHistory->updateStartLockHistory($businessTripRegister->lists('id')->toArray(), $empsIdOfTimeKeeping, TimekeepingLockHistories::TYPE_CT);
            DB::commit();

            if ($sendEmail && $creator = $timeKeepingTable->getCreatorInfo()) {
                $dataInsertEmail = [];
                $templateEmail = 'manage_time::template.timekeeping.mail_update_related';
                $dataInsertEmail['mail_to'] = $creator->email;
                $dataInsertEmail['receiver_name'] = $creator->name;
                $dataInsertEmail['timekeeping_table_name'] = $timeKeepingTable->timekeeping_table_name;
                $dataInsertEmail['month'] = $timeKeepingTable->month;
                $dataInsertEmail['year'] = $timeKeepingTable->year;
                $dataInsertEmail['link'] = route('manage_time::timekeeping.timekeeping-detail', ['timekeepingTableId' => $timekeepingTableId]);

                $dataInsertEmail['mail_title'] = Lang::get('manage_time::message.[Notification][Timekeeping][Update related data success] :subject', ['subject' => $timeKeepingTable->timekeeping_table_name]);
                $dataInsertEmail['content'] = Lang::get('manage_time::message.Process update related data into timekeeping successfuly.');
                ManageTimeCommon::pushEmailToQueue($dataInsertEmail, $templateEmail);
            }
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            if ($creator = $timeKeepingTable->getCreatorInfo()) {
                $dataInsertEmail = [];
                $templateEmail = 'manage_time::template.timekeeping.mail_update_related';
                $dataInsertEmail['mail_to'] = $creator->email;
                $dataInsertEmail['receiver_name'] = $creator->name;
                $dataInsertEmail['timekeeping_table_name'] = $timeKeepingTable->timekeeping_table_name;
                $dataInsertEmail['month'] = $timeKeepingTable->month;
                $dataInsertEmail['year'] = $timeKeepingTable->year;
                $dataInsertEmail['link'] = route('manage_time::timekeeping.timekeeping-detail', ['timekeepingTableId' => $timekeepingTableId]);

                $dataInsertEmail['mail_title'] = Lang::get('manage_time::message.[Notification][Timekeeping][Update related data fail] :subject', ['subject' => $timeKeepingTable->timekeeping_table_name]);
                $dataInsertEmail['content'] = Lang::get('manage_time::message.Process update related data into timekeeping fail.');
                ManageTimeCommon::pushEmailToQueue($dataInsertEmail, $templateEmail);
            }
        }
    }

    /**
     * [getDiffTimesOfTimeKeepingRegisterWTK description]
     * @param  [type] $dateStart          [description]
     * @param  [type] $dateEnd            [description]
     * @param  [type] $dateTimekeeping    [description]
     * @param  [type] $timeSettingOfEmp   [description]
     * @param  [type] $timeKeepingTableId [description]
     * @param  [type] $employeeId         [description]
     * @param  [type] $timeQuater         [description]
     * @return [type]                     [description]
     */
    public function getDiffTimesOfTimeKeepingRegisterWTK($dateStart, $dateEnd, $dateTimekeeping, $timeSettingOfEmp, $timeKeepingTableId, $employeeId, $timeQuater, $onlyCheckDate = false)
    {
        $data = [
            'diff' => 0,
            'session' => null,
            'timekeeping_number_register' => 0,
        ];
        if (!in_array($dateStart->format('H:i'), $timeQuater['timeIn']) ||
            !in_array($dateEnd->format('H:i'), $timeQuater['timeOut'])) {
            return $data;
        }
        $twLeaveDay = $this->getTimeWorkingByQuater($timeQuater);
        if ($dateStart->format('Y-m-d') < $dateTimekeeping && $dateTimekeeping < $dateEnd->format('Y-m-d')) {
            $data['diff'] = 1;
            $data['session'] = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
        } elseif ($dateStart->format('Y-m-d') < $dateTimekeeping && $dateTimekeeping == $dateEnd->format('Y-m-d')) {
            if ($dateEnd->hour == $twLeaveDay['afternoonOut']->hour) {
                $data['diff'] = 1;
                $data['session'] = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
            } elseif ($dateEnd->hour == $twLeaveDay['morningOut']->hour) {
                $data['diff'] = $this->getDiffTimesRegisterCarbon($twLeaveDay['morningIn'], $twLeaveDay['morningOut'], $timeSettingOfEmp);
                $data['session'] = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
            } else {
                $diff = $this->getDiffTimesRegisterCarbon($twLeaveDay['morningIn'], $dateEnd, $timeSettingOfEmp);
                $timeReset = self::getTimeResetEndEqual($timeKeepingTableId, $employeeId, $dateTimekeeping, $dateEnd, $timeSettingOfEmp);
                $data = [
                    'diff' => $diff,
                    'session' => $diff,
                    'timekeeping_number_register' => $timeReset['time'],
                    'timeLateStart' => $timeReset['timeLateStart'],
                    'timeLateMid' => $timeReset['timeLateMid'],
                ];
            }
        } elseif ($dateStart->format('Y-m-d') == $dateTimekeeping && $dateTimekeeping < $dateEnd->format('Y-m-d')) {
            if ($dateStart->hour == $twLeaveDay['morningIn']->hour) {
                $data['diff'] = 1;
                $data['session'] = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
            } elseif ($dateStart->hour == $twLeaveDay['afternoonIn']->hour) {
                $data['diff'] = $this->getDiffTimesRegisterCarbon($twLeaveDay['afternoonIn'], $twLeaveDay['afternoonOut'], $timeSettingOfEmp);
                $data['session'] = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
            } else {
                $diff = $this->getDiffTimesRegisterCarbon($dateStart, $twLeaveDay['afternoonOut'], $timeSettingOfEmp);
                $timeReset = self::getTimeResetStartEqual($timeKeepingTableId, $employeeId, $dateTimekeeping, $dateStart, $timeSettingOfEmp);
                $data = [
                    'diff' => $diff,
                    'session' => $diff,
                    'timekeeping_number_register' => $timeReset['time'],
                    'timeLateStart' => $timeReset['timeLateStart'],
                    'timeEarlyMid' => $timeReset['timeEarlyMid'],
                    'timeEarlyEnd' => $timeReset['timeEarlyEnd']
                ];
            }
        } else {
            $diff = $this->getDiffTimesRegisterCarbon($dateStart, $dateEnd, $timeSettingOfEmp);
            if ($diff >= 1) {
                $session = ManageTimeConst::HAS_LEAVE_DAY_FULL_DAY;
            } elseif ($dateStart->hour == $twLeaveDay['morningIn']->hour &&
                $dateEnd->hour == $twLeaveDay['morningOut']->hour) {
                $session = ManageTimeConst::HAS_LEAVE_DAY_MORNING;
            } elseif ($dateStart->hour == $twLeaveDay['afternoonIn']->hour &&
                $dateEnd->hour == $twLeaveDay['afternoonOut']->hour) {
                $session = ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON;
            } else {
                $timeKeeping = Timekeeping::getTimekeeping($timeKeepingTableId, $employeeId, $dateTimekeeping);
                $timeReset = $this->getTimeResetToDay($timeKeeping, $dateStart, $dateEnd, $twLeaveDay, $timeQuater, $onlyCheckDate);
                return [
                    'diff' => $diff,
                    'session' => $diff,
                    'timekeeping_number_register' => $timeReset['time'],
                    'timeLateStart' => $timeReset['timeLateStart'],
                    'timeLateMid' => $timeReset['timeLateMid'],
                    'timeEarlyMid' => $timeReset['timeEarlyMid'],
                    'timeEarlyEnd' => $timeReset['timeEarlyEnd']
                ];
            }
            return [
                'diff' => $diff,
                'session' => $session,
                'timekeeping_number_register' => 0,
            ];
        }
        return $data;
    }

    /**
     * [getTimeWorkingByQuater]
     * @param  [array] $timeQuater [value carbon]
     * @return [array]
     */
    public function getTimeWorkingByQuater($timeQuater)
    {
        if (count($timeQuater['timeIn']) > 2) {
            return [
                'morningIn' => $timeQuater['timeInMor'][0],
                'morningOut' => $timeQuater['timeOutMor'][1],
                'afternoonIn' => $timeQuater['timeInAfter'][0],
                'afternoonOut' => $timeQuater['timeOutAfter'][1],
            ];
        }
        return [
            'morningIn' => $timeQuater['timeInMor'][0],
            'morningOut' => $timeQuater['timeOutMor'][0],
            'afternoonIn' => $timeQuater['timeInAfter'][0],
            'afternoonOut' => $timeQuater['timeOutAfter'][0],
        ];
    }

    public function getTimeResetToDay($timeKeeping, $dateStart, $dateEnd, $timeWorking, $timeQuater, $onlyCheckLate = false)
    {
        $timeLateStart = 0;
        $timeLateMid = 0;
        $timeEarlyMid = 0;
        $timeEarlyEnd = 0;

        if (!$onlyCheckLate) {
            if (!$timeKeeping ||
                (empty($timeKeeping->end_time_morning_shift) &&
                    empty($timeKeeping->end_time_afternoon_shift))
                || count($timeQuater['timeIn']) == 2) {
                return [
                    'time' => 0,
                    'timeLateStart' => $timeLateStart,
                    'timeLateMid' => $timeLateMid,
                    'timeEarlyMid' => $timeEarlyMid,
                    'timeEarlyEnd' => $timeEarlyEnd,
                ];
            }
        }

        $timeStartLeave = $dateStart->hour * 60 + $dateStart->minute;
        $timeEndLeave = $dateEnd->hour * 60 + $dateEnd->minute;
        switch (array_search($dateStart->format('H:i'), $timeQuater['timeIn'])) {
            case 1:
                if (empty($timeKeeping->end_time_morning_shift) &&
                    empty($timeKeeping->start_time_afternoon_shift) &&
                    empty($timeKeeping->end_time_afternoon_shift)) {
                    $time = 0;
                    break;
                }
                if ($dateEnd->format('H:i') == $timeQuater['timeOut'][1]) {
                    if (!empty($timeKeeping->start_time_morning_shift)) {
                        if ($timeKeeping->start_time_morning_shift >= $timeQuater['timeIn'][1]) {
                            $time = 0;
                        } else {
                            if (!empty($timeKeeping->end_time_morning_shift) &&
                                $timeKeeping->end_time_morning_shift <= $timeQuater['timeIn'][1] &&
                                empty($timeKeeping->start_time_afternoon_shift) &&
                                empty($timeKeeping->end_time_afternoon_shift)) {
                                    $time = $this->getTimeMinuteDiff($dateStart, $timeQuater['timeInMor'][0]);
                            } elseif (!empty($timeKeeping->end_time_morning_shift) ||
                                !empty($timeKeeping->start_time_afternoon_shift) ||
                                !empty($timeKeeping->end_time_afternoon_shift)) {
                                    $time = $this->getTimeMinuteDiff($dateStart, $timeQuater['timeOut'][1]);
                            } else {
                                $time = 0;
                            }
                        }
                        $setTimes = self::getTimeEarlyMid($timeKeeping, $timeStartLeave);
                        $timeEarlyMid = $setTimes['lateEarly'];
                        $timeEarlyMid = $timeEarlyMid ? $timeEarlyMid : ManageTimeConst::RESET;
                        $time = $time * $setTimes['time'];
                    } else {
                        $time = 0;
                    }
                } elseif ($dateEnd->format('H:i') == $timeQuater['timeOut'][3]) {
                    $timeMor = 0;
                    if (!empty($timeKeeping->start_time_morning_shift) &&
                        $timeKeeping->start_time_morning_shift < $timeQuater['timeIn'][1]) {
                        $timeMor = $this->getTimeMinuteDiff($dateStart, $timeQuater['timeInMor'][0]);

                        $setTimes = self::getTimeEarlyMid($timeKeeping, $timeStartLeave);
                        $timeEarlyMid = $setTimes['lateEarly'];
                        $timeEarlyMid = $timeEarlyMid ? $timeEarlyMid : ManageTimeConst::RESET;
                    }

                    if ($timeMor > 0 && $timeKeeping->timekeeping_number > 0) {
                        $time = - $timeKeeping->timekeeping_number * 480 + $timeMor;
                        $timeEarlyEnd = ManageTimeConst::RESET;
                    } elseif ($timeMor > 0 && $timeKeeping->timekeeping_number == 0
                        || $timeMor < 0 && empty($timeKeeping->end_time_afternoon_shift)) {
                        $time = $timeMor;
                    } elseif ($timeMor < 0 && !empty($timeKeeping->end_time_afternoon_shift)) {
                        if ($timeKeeping->timekeeping_number == 1) {
                            $time = - 480 - $timeMor;
                        } else {
                            $time = $timeMor;
                        }
                    } elseif ($timeMor == 0) {
                        $time = - $timeKeeping->timekeeping_number * 480;
                    } else {
                        $time = 0;
                    }
                } else {
                    if ($timeKeeping->timekeeping_number == 1) { //-
                        $time1 = $this->getTimeMinuteDiff($dateStart, $timeQuater['timeOutMor'][1]);
                        $time2 = $this->getTimeMinuteDiff($dateEnd, $timeQuater['timeInAfter'][0]);
                        $time = - ($time1 + $time2);
                    } else {
                        $timeAfter = 0;
                        if (!empty($timeKeeping->end_time_afternoon_shift)) {
                            if ($timeKeeping->end_time_afternoon_shift > $timeQuater['timeOut'][2]) {
                                $timeAfter = $this->getTimeMinuteDiff($timeQuater['timeInAfter'][0], $dateEnd);
                            }
                        }
                        $setTimes = self::getTimeLateMid($timeKeeping, $timeEndLeave);
                        $timeLateMid = $setTimes['lateEarly'];
                        $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                        $timeAfter = $timeAfter * $setTimes['time'];

                        $timeMor = 0;
                        if (!empty($timeKeeping->start_time_morning_shift) &&
                            $timeKeeping->start_time_morning_shift <= $timeQuater['timeIn'][1]) {
                            $timeMor = $this->getTimeMinuteDiff($timeQuater['timeInMor'][1], $dateStart);
                        }
                        $setTimes = self::getTimeEarlyMid($timeKeeping, $timeStartLeave);
                        $timeEarlyMid = $setTimes['lateEarly'];
                        $timeEarlyMid = $timeEarlyMid ? $timeEarlyMid : ManageTimeConst::RESET;
                        $timeMor = $timeMor * $setTimes['time'];

                        $time = $timeMor + $timeAfter;
                    }
                }
                break;
            case 2:
                $timeEndSub = clone $timeQuater['timeOutAfter'][1];
                $timeEndSub = $timeEndSub->subHours(2);
                if (empty($timeKeeping->start_time_morning_shift) &&
                    empty($timeKeeping->end_time_morning_shift) && 
                    empty($timeKeeping->start_time_afternoon_shift)) {
                        $time = 0;
                } elseif(!empty($timeKeeping->end_time_afternoon_shift) &&
                    $timeKeeping->end_time_afternoon_shift >= $timeEndSub->format('H:i') &&
                    $dateEnd->format('H:i') == $timeQuater['timeOut'][2]) {
                        if (!empty($timeKeeping->start_time_morning_shift)) {
                            $time = $this->getTimeMinuteDiff($dateStart, $dateEnd);
                        } else {
                            $time = $this->getTimeMinuteDiff($dateEnd, $timeQuater['timeOutAfter'][1]);
                        }
                } else {
                    $time = 0;
                }
                $setTimes = self::getTimeLateMid($timeKeeping, $timeEndLeave);
                $timeLateMid = $setTimes['lateEarly'];
                $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                $time = $time * $setTimes['time'];
                break;
            case 3:
                $timeEndSub = clone $timeQuater['timeOutAfter'][1];
                $timeEndSub = $timeEndSub->subHours(2);
                $time120End = $timeEndSub->hour * 60 + $timeEndSub->minute;
                if (!empty($timeKeeping->end_time_afternoon_shift) &&
                    (!empty($timeKeeping->start_time_morning_shift) || !empty($timeKeeping->start_time_afternoon_shift))) {
                    if (!empty($timeKeeping->start_time_afternoon_shift) && $timeKeeping->start_time_afternoon_shift >= $timeQuater['timeOutAfter'][1]->format('H:i')) {
                        $time = 0;
                    } elseif ($timeKeeping->end_time_afternoon_shift <= $timeEndSub->format('H:i')) {
                        $time = $this->getTimeMinuteDiff($timeQuater['timeInAfter'][0], $dateStart);
                    } elseif ($timeKeeping->end_time_afternoon_shift > $timeEndSub->format('H:i')) {
                        $time = $this->getTimeMinuteDiff($dateStart, $dateEnd);
                    } else{
                        $time = 0;
                    }
                } else {
                    $time = 0;
                }
                $setTimes = self::getTimeEarlyEnd($timeKeeping, $timeStartLeave, $time120End);
                $timeEarlyEnd = $setTimes['lateEarly'];
                $timeEarlyEnd = $timeEarlyEnd ? $timeEarlyEnd : ManageTimeConst::RESET;
                $time = $time * $setTimes['time'];
                break;
            default:
                if (empty($timeKeeping->start_time_morning_shift) &&
                    empty($timeKeeping->start_time_afternoon_shift) &&
                    empty($timeKeeping->end_time_afternoon_shift)) {
                    $time = 0;
                    break;
                }
                $dateStartAdd = clone $dateStart;
                $dateStartAdd = $dateStartAdd->addHours(2);
                $timeStartLeaveAdd = $dateStartAdd->hour * 60 + $dateStartAdd->minute;
                if ($dateEnd->format('H:i') == $timeQuater['timeOut'][0] && !empty($timeKeeping->start_time_morning_shift)) {
                    if ($timeKeeping->start_time_morning_shift < $dateStartAdd->format('H:i')) {
                        $time =  - ($this->getTimeMinuteDiff($dateStart, $dateEnd));
                        // $timeLateStart = ManageTimeConst::RESET;
                    } elseif ($timeKeeping->start_time_morning_shift >= $dateStartAdd->format('H:i')) {
                        $time = $this->getTimeMinuteDiff($dateEnd, $timeQuater['timeOutMor'][1]);
                        // $setTimes = self::getTimeLateStart($timeKeeping, $timeEndLeave);
                        // $timeLateStart = $setTimes['lateEarly'];
                        // $timeLateStart = $timeLateStart ? $timeLateStart : ManageTimeConst::RESET;
                    } else {
                        $time = 0;
                    }
                    if ($time) {
                        $setTimes = self::getTimeLateStart($timeKeeping, $timeEndLeave);
                        $timeLateStart = $setTimes['lateEarly'];
                        $timeLateStart = $timeLateStart ? $timeLateStart : ManageTimeConst::RESET;
                    }
                } elseif ($dateEnd->format('H:i') == $timeQuater['timeOut'][2] && !empty($timeKeeping->end_time_afternoon_shift)) {
                    if (!empty($timeKeeping->start_time_morning_shift)) {
                        $timeLateStart = ManageTimeConst::RESET;
                    }
                    if ($timeKeeping->end_time_afternoon_shift <= $timeQuater['timeOut'][2]) {
                        $time = -$timeKeeping->timekeeping_number * 480;
                        $timeLateMid = ManageTimeConst::RESET;
                    } elseif (!empty($timeKeeping->start_time_morning_shift) && $timeKeeping->end_time_afternoon_shift <= $timeQuater['timeOut'][2]) {
                        $time = - $timeKeeping->timekeeping_number * 480;
                        $timeEarlyEnd = ManageTimeConst::RESET;
                    } elseif (!empty($timeKeeping->start_time_morning_shift) && $timeKeeping->end_time_afternoon_shift > $timeQuater['timeOut'][2]) {
                        $tempolary = $this->getTimeMinuteDiff($dateEnd, $timeQuater['timeOutAfter'][1]);
                        $time = - 480 + $tempolary;

                        $setTimes = self::getTimeLateMid($timeKeeping, $timeEndLeave);
                        $timeLateMid = $setTimes['lateEarly'];
                        $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                    } elseif (empty($timeKeeping->start_time_morning_shift) && !empty((float)$timeKeeping->timekeeping_number)) {
                        $time = - $this->getTimeMinuteDiff($dateEnd, $timeQuater['timeInAfter'][0]);
                        $timeLateMid = ManageTimeConst::RESET;
                    } else {
                        $time = $this->getTimeMinuteDiff($dateEnd, $timeQuater['timeOutAfter'][1]);

                        $setTimes = self::getTimeLateMid($timeKeeping, $timeEndLeave);
                        $timeLateMid = $setTimes['lateEarly'];
                        $timeLateMid = $timeLateMid ? $timeLateMid : ManageTimeConst::RESET;
                        $time = $time * $setTimes['time'];
                    }
                } elseif ($dateEnd->format('H:i') == $timeQuater['timeOut'][2] && empty($timeKeeping->end_time_afternoon_shift)) {
                    $time = - $timeKeeping->timekeeping_number * 480;
                    $timeLateStart = ManageTimeConst::RESET;
                    $timeLateMid = ManageTimeConst::RESET;
                } else {
                    $time = 0;
                }
                break;
        }
        return [
            'time' => $time / 480,
            'timeLateStart' => $timeLateStart,
            'timeLateMid' => $timeLateMid,
            'timeEarlyMid' => $timeEarlyMid,
            'timeEarlyEnd' => $timeEarlyEnd,
        ];
    }

    public function getTimeMinuteDiff($time1, $time2)
    {
        $diff = $time1->diffInSeconds($time2);
        $diff = Carbon::createFromFormat('H:i', gmdate('H:i', $diff));
        return $diff->hour * 60 + $diff->minute;
    }

    /**
     * update status Lock timekeeping
     * @param  Request $request
     * @return [type]
     */
    public function updateLockUp(Request $request)
    {
        $tableId = $request->timekeeping_table_id;
        $now = Carbon::now();
        DB::beginTransaction();
        try {
            $timeKeepingTable = TimekeepingTable::findOrFail($tableId);
            if (!$timeKeepingTable) {
                return redirect()->back()->with('messages', ['errors'=> [Lang::get('team::messages.Not found item.')]]);
            }
            if ($timeKeepingTable->lock_up == TimekeepingTable::OPEN_LOCK_UP) {
                $timeKeepingTable->lock_up = TimekeepingTable::CLOSE_LOCK_UP;
                $messages = Lang::get('manage_time::message.Close lock timekeeping success');
                TimekeepingLock::create([
                    'timekeeping_table_id' => $timeKeepingTable->id,
                    'time_close_lock' => $now,
                ]);
                // lưu giờ vào ra của nhân viên
                $empIds = Timekeeping::selectRaw('distinct(employee_id)')
                    ->where('timekeeping_table_id', $tableId)->lists('employee_id')->toArray();
                if ($empIds) { //van co th bang cong ko co nhan vien
                    with(new TimekeepingWorkingTime())->insertWKT($tableId, $empIds, $timeKeepingTable->start_date, $timeKeepingTable->end_date);
                }
            } else {
                $timeKeepingTable->lock_up = TimekeepingTable::OPEN_LOCK_UP;
                $messages = Lang::get('manage_time::message.Open lock timekeeping success');
                $objLock = new TimekeepingLock();
                $objLock->updateLockOpen($timeKeepingTable->id,  $now);
            }
            $timeKeepingTable->lock_up_time = $now;
            $timeKeepingTable->update();
            DB::commit();
            return redirect()->back()->with('messages', ['success' => [$messages]]);
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
            return redirect()->back()->with('messages', ['errors' => ['Error. Please try again.']]);
        }
    }

    /**
     * insert and get all application: leave days, supplement, mission register after lock timekeeping
     *
     * @param int $idTable
     */
    public function getEmpAfterLock($idTable, Request $request)
    {
        $idLock = $request->get('id-lock');
        $tkTable = TimekeepingTable::getTimekeepingTable($idTable);
        if (!$tkTable) {
            return redirect()->route('manage_time::timekeeping.manage-timekeeping-table')->with('messages', [
                'errors'=> [
                    Lang::get('team::messages.Not found item.'),
                ]
            ]);
        }
        $objLockHistory = new TimekeepingLockHistories();
        $tkTableLock = $tkTable->timekeepingLock()->orderBy('id', 'DESC')->get();
        $arrIdLock = $tkTableLock->lists('id')->toArray();
        if (!count($tkTableLock)) {
            $params = [
                'infoTkTable' => $tkTable,
                'arrIdLock' => $arrIdLock,
                'arrInfoEmpAfterLock' => [],
                'idLockActive' => '',
                'infoLock' => '',
            ];
            return view('manage_time::timekeeping.list_employee_after_lock')->with($params);
        }
        if (empty($idLock) || !in_array($idLock, $arrIdLock)) {
            $idLock = $tkTableLock->first()->id;
        }
        if ($tkTable->type == TimekeepingTable::OFFICIAL) {
            $official = true;
        } else {
            $official = false;
        }
        $this->insertLockHistory($idTable, $official, $idLock);
        $infoEmpAfterLock = $objLockHistory->getEmpAfterLock($idLock);
        $arrInfoEmpAfterLock = $objLockHistory->getArrayEmpAfterLock($infoEmpAfterLock);
        $params = [
            'infoTkTable' => $tkTable,
            'arrIdLock' => $arrIdLock,
            'arrInfoEmpAfterLock' => $arrInfoEmpAfterLock,
            'idLockActive' => $idLock,
            'infoLock' => TimekeepingLock::findOrFail($idLock),
        ];
        return view('manage_time::timekeeping.list_employee_after_lock')->with($params);
    }

    /**
     * insert application: leave days, supplement, mission register after lock timekeeping
     * @param int $idTable
     * @param boolean $official
     * @param int $idLock
     */
    public function insertLockHistory($idTable, $official, $idLock)
    {
        $objTKlock = new TimekeepingLock();
        $tkLock = $objTKlock->getInforFirst($idTable, $idLock);

        if (!count($tkLock)) {
            return;
        }

        $objViewkeeping = new ViewTimeKeeping();
        $leavedays = $objViewkeeping->getLeaveDayAfterLock($idTable, $idLock);
        $supplements = $objViewkeeping->getSuppAfterLock($idTable, $idLock);
        $suppBusiness = $objViewkeeping->getBusinessAfterLock($idTable, $idLock);
        $registerOT = $objViewkeeping->getRegisterOTAfterLock($idTable, $idLock);

        $objLockHistory = new TimekeepingLockHistories();
        $infoHistories = $objLockHistory->getInfoById($idTable);
        $arrHistories = $objLockHistory->getInfoIdLock($infoHistories);

        DB::beginTransaction();
        try {
            $arrInfoId = [];
            if ($tkLock->lockHistories()->count() && array_key_exists($tkLock->id, $arrHistories)) {
                $arrInfoId = $arrHistories[$tkLock->id];
            }
            $dataInsert = [];
            $dataleavedays = $this->getDataInsertLockHistory($leavedays, $official, TimekeepingLockHistories::TYPE_P, $arrInfoId);
            $dataSupp = $this->getDataInsertLockHistory($supplements, $official, TimekeepingLockHistories::TYPE_BSC, $arrInfoId);
            $dataBusiness = $this->getDataInsertLockHistory($suppBusiness, $official, TimekeepingLockHistories::TYPE_CT, $arrInfoId);
            $dataRegiterOT = $this->getDataInsertLockHistory($registerOT, $official, TimekeepingLockHistories::TYPE_OT, $arrInfoId);
            $dataInsert = array_merge($dataInsert, $dataleavedays);
            $dataInsert = array_merge($dataInsert, $dataSupp);
            $dataInsert = array_merge($dataInsert, $dataBusiness);
            $dataInsert = array_merge($dataInsert, $dataRegiterOT);

            if (count($dataInsert)) {
                $objLockHistory->insertData($tkLock, $dataInsert);
            }
            DB::commit();
        } catch(Exception $ex) {
            DB::rollBack();
            Log::info($ex);
        }
    }

    /**
     * set data insert table timekeeping_lock_histories
     *
     * @param $colletions
     * @param $official
     * @param $type
     * @param $arrInfoId
     * @return array
     */
    public function getDataInsertLockHistory($colletions, $official, $type, $arrInfoId)
    {
        $dataInsert = [];
        if (count($colletions)) {
            foreach ($colletions as $item) {
                $end = Carbon::parse($item->date_end);
                $start = Carbon::parse($item->date_start);
                if (!empty($item->join_date) && $item->join_date > $end->format('Y-m-d')) {
                    continue;
                }
                if ($official) {
                    if ((!empty($item->trial_date) && $item->trial_date > $end->format('Y-m-d')) ||
                        (empty($item->trial_date) && !empty($item->offcial_date) &&
                            $item->offcial_date > $end->format('Y-m-d'))) {
                        continue;
                    }
                } else {
                    if ((!empty($item->trial_date) && $item->trial_date < $start->format('Y-m-d')) ||
                        (empty($item->trial_date) && !empty($item->offcial_date) &&
                            $item->offcial_date < $start->format('Y-m-d'))) {
                        continue;
                    }
                }
                if (!empty($item->is_ot)) {
                    $type = TimekeepingLockHistories::TYPE_BSC_OT;
                }
                if (!array_key_exists($type, $arrInfoId) ||
                    !in_array($item->registerId, $arrInfoId[$type])) {
                    $data = [
                        'timekeeping_lock_id' => $item->lockId,
                        'inform_id' => $item->registerId,
                        'employee_id' => $item->employee_id,
                        'status' => TimekeepingLockHistories::STATUS_NOT_UPDATE,
                        'type' => $type,
                    ];
                    $dataInsert[] = $data;
                }
            }
        }
        return $dataInsert;
    }

    //=========== start team view timekeeping D lead ===========

    /**
     * get list timekeeping aggregates, get with team permission
     *
     * @param null $tkTableId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getTimekeepingAggregates($tkTableId = null)
    {
        $objPermission = new TimekeepingPermission();
        $teamIdAllow = $objPermission->getTeamViewTk();
        if (!$teamIdAllow) {
            View::viewErrorPermission();
        }
        $teamParents = with(new Team())->getParentByTeam($teamIdAllow);
        $teamAll = array_merge($teamIdAllow, $teamParents);
        if (!$tkTableId) {
            $yearCurrent = Carbon::now()->year;
            $typeFilter = CookieCore::getRaw('filter_type_timekeeping_table');
            $typeFilter =  $typeFilter ? $typeFilter : TimeKeepingTable::OFFICIAL;
            $timeKeepingTable = with(new TimekeepingTable())->getTkTableByYearTeamsType($yearCurrent, $teamAll, $typeFilter);
            if (!$timeKeepingTable) {
                return redirect()->back()->with('messages', ['errors' => [Lang::get('team::messages.Not found item.')]]);
            }
            $tkTableId = $timeKeepingTable->id;
            //
            $url = route('manage_time::division.list-tk-aggregates') . '/';
            $filter = Form::getFilterData(null, null, $url);
            $urlEncode =  route('manage_time::division.list-tk-aggregates', ['id' => $tkTableId]) . '/';
            CookieCore::forgetRaw('filter.' . $urlEncode);
            CookieCore::forgetRaw('filter_pager.' . $urlEncode);
            CookieCore::setRaw('filter.' . $urlEncode, $filter);
        } else {
            $timeKeepingTable = TimekeepingTable::getTimekeepingTable($tkTableId);
        }
        if (!$timeKeepingTable) {
            return redirect()->back()->with('messages', ['errors' => [Lang::get('team::messages.Not found item.')]]);
        }
        if (!in_array($timeKeepingTable->team_id, $teamAll)) {
            View::viewErrorPermission();
        }
        ViewTimeKeeping::cronRelatedPerson();
        $yearFilter = CookieCore::getRaw('filter_year_timekeeping_table');
        $yearFilter =  $yearFilter ? $yearFilter : $timeKeepingTable->year;
        $typeFilter = CookieCore::getRaw('filter_type_timekeeping_table');
        $typeFilter =  $typeFilter ? $typeFilter : $timeKeepingTable->type;
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $route = RequestUrl::url() . '/';

        $current = Permission::getInstance()->getEmployee();
        $projFilter = \request()->get('projd');
        $objProj = new Project();
        $projects = $objProj->getProjectByEmpId($current->id);
        $arrIdProject = [];
        if (count($projects)) {
            $arrIdProject = $projects->lists('id')->toArray();
        }
        if ($projFilter && !in_array($projFilter, $arrIdProject)) {
            $projFilter = '';
        }
        $objTKAggregate = new TimekeepingAggregate();
        if ($projFilter) {
            $collectionModel = $objTKAggregate->getTimekeepingAggregatesProject($tkTableId, $teamIdAllow, $dataFilter, $projFilter, $route);
        } else {
            $collectionModel = $objTKAggregate->getTimekeepingAggregates($tkTableId, $teamIdAllow, $dataFilter, $route);
        }
        $officialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_official_salary");
        $trialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_trial_salary");
        $collectionModel = TimekeepingAggregate::filterTotalSalary($route, $collectionModel, $timeKeepingTable, $officialSalaryFilter, $trialSalaryFilter);
        $timekeepingTablesList = TimekeepingTable::getTimekeepingTablesList($teamAll, $yearFilter);

        $params = [
            'collectionModel'  => $collectionModel,
            'timeKeepingTable' => $timeKeepingTable,
            'yearFilter' => $yearFilter,
            'timekeepingTablesList' => $timekeepingTablesList,
            'empIdInList' => $collectionModel->lists('employee_id')->toArray(),
            'optionsCompare' => ManageTimeCommon::optionsCompare(),
            'keyFilter' => ManageTimeCommon::keysFilter(),
            'teamIdAllow' => $teamIdAllow,
            'typeFilter' => $typeFilter,
            'projects' => $projects,
            'projFilter' => $projFilter,
        ];
        return view('manage_time::team.timekeeping_aggregate', $params);
    }


    /**
     * export list timekeeping aggregates
     * @param $tkTableId
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function leadExportAggregate($tkTableId)
    {
        $objPermission = new TimekeepingPermission();
        $teamIdAllow = $objPermission->getTeamViewTk();
        if (!$teamIdAllow) {
            View::viewErrorPermission();
        }
        $teamParents = with(new Team())->getParentByTeam($teamIdAllow);
        $teamAll = array_merge($teamIdAllow, $teamParents);

        $timeKeepingTable = TimekeepingTable::getTimekeepingTable($tkTableId);
        if (!$timeKeepingTable) {
            return redirect()->back()->with('messages', ['errors' => [Lang::get('team::messages.Not found item.')]]);
        }
        if (!in_array($timeKeepingTable->team_id, $teamAll)) {
            View::viewErrorPermission();
        }
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        $route = route('manage_time::division.list-tk-aggregates', ['id' => $tkTableId]) . '/';

        $current = Permission::getInstance()->getEmployee();
        $projFilter = \request()->get('projd');
        $objProj = new Project();
        $projects = $objProj->getProjectByEmpId($current->id);
        $arrIdProject = [];
        if (count($projects)) {
            $arrIdProject = $projects->lists('id')->toArray();
        }
        if ($projFilter && !in_array($projFilter, $arrIdProject)) {
            $projFilter = '';
        }
        $objTkAggregate = new TimekeepingAggregate();
        if ($projFilter) {
            $collectionModel = $objTkAggregate->getTimekeepingAggregatesProject($tkTableId, $teamIdAllow, $dataFilter, $projFilter, $route, true);
        } else {
            $collectionModel = $objTkAggregate->getTimekeepingAggregates($tkTableId, $teamIdAllow, $dataFilter, $route, true);
        }
        $officialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_official_salary", $route);
        $trialSalaryFilter = Form::getFilterData('except', "manage_time_timekeeping_aggregates.total_trial_salary", $route);
        $collectionModel = TimekeepingAggregate::filterTotalSalary($route, $collectionModel, $timeKeepingTable, $officialSalaryFilter, $trialSalaryFilter);
        if(!count($collectionModel)) {
            $messages = [
                'errors'=> [
                    Lang::get('manage_time::view.No data to export'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        }
        return  $this->exportAggregates($collectionModel, $timeKeepingTable);
    }

    /*
     * Ajax get timekeeping table
     */
    public function ajaxGetTKTables(Request $request)
    {
        $year = $request->year;
        $type = $request->type_id;
        $projId = $request->proj_id;
        CookieCore::setRaw('filter_year_timekeeping_table', $year);
        CookieCore::setRaw('filter_type_timekeeping_table', $type);

        $objPermission = new TimekeepingPermission();
        $teamIdAllow = $objPermission->getTeamViewTk();
        $teamParents = with(new Team())->getParentByTeam($teamIdAllow);
        $teamAll = array_merge($teamIdAllow, $teamParents);
        $params = [
            'timekeepingTablesList' => TimekeepingTable::getTimekeepingTablesList($teamAll, $year, $type),
            'timekeepingTableId' => $request->timekeepingTableId,
        ];
        $objTKTable = new TimeKeepingTable();
        $arrLabelType = $objTKTable->getArrLabelTypeTKTable();
        $html = '';
        foreach ($params['timekeepingTablesList'] as $item) {
            $dateStart = Carbon::parse($item->start_date)->format('d/m/Y');
            $dateEnd = Carbon::parse($item->end_date)->format('d/m/Y');
            $name = $item->team_name
                . ' {' . trans('manage_time::view.From')
                . ' ' . $dateStart
                . ' ' . trans('manage_time::view.to')
                . ' ' . $dateEnd
                . '} - {' . $arrLabelType[$item->type]
                .'}';
            $html = $html . '<option value="'
                . route('manage_time::division.list-tk-aggregates', ['id' => $item->timekeeping_table_id])
                . "?projd=" . $projId
                . '"'
                . ($item->timekeeping_table_id == $request->timekeepingTableId ? 'selected' : '')
                . '>'
                . $name . '</option>';
        }
        $html = "<option>&nbsp;</option>" . $html;

        return response()->json(['html' => $html]);
    }

    /**
     * @param $idTable
     * @param $idEmp
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function getTkgDetailByEmployee($idTable, $idEmp)
    {
        $objPermission = new TimekeepingPermission();
        $teamIdAllow = $objPermission->getTeamViewTk();
        if (!$teamIdAllow) {
            View::viewErrorPermission();
        }
        $teamParents = with(new Team())->getParentByTeam($teamIdAllow);
        $teamAll = array_merge($teamIdAllow, $teamParents);

        ViewTimeKeeping::cronRelatedPerson($idEmp);
        $timekeepingTable = TimekeepingTable::getTimekeepingDetailByEmp($idTable, $idEmp);
        if (!$timekeepingTable) {
            $messages = ['errors'=> [Lang::get('team::messages.Not found item.')]];
            return redirect()->route('manage_time::division.list-tk-aggregates')->with('messages', $messages);
        }
        Breadcrumb::add('Team');
        Breadcrumb::add(trans('manage_time::view.Timesheets'), 
            route('manage_time::division.list-tk-aggregates', ['idTable' => $idTable]));

        $objTk = new Timekeeping();
        $timeKeepingList = $objTk->getTKDetail($idTable, [$idEmp], $teamAll);
        if (!in_array($timekeepingTable->team_id, $teamAll) || !count($timeKeepingList)) {
            View::viewErrorPermission();
        }
        $timekeepingAggregate = TimekeepingAggregate::getTimekeepingAggregateByEmp($idTable, $idEmp);
        $dataKeeping = [];
        foreach ($timeKeepingList as $keepingItem) {
            $dataKeeping[$keepingItem->timekeeping_date] = $keepingItem;
        }
        $userCurrent = Employee::getEmpById($idEmp);
        $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($userCurrent);
        $compensationDays = CoreConfigData::getCompensatoryDays($teamCodePrefix);
        $arrHolidays = CoreConfigData::getHolidayTeam($teamCodePrefix);

        $objWorkingTime = new WorkingTime();
        $workingTimdDate = $objWorkingTime->getStrWorkingTime([$idEmp], $timekeepingTable, $teamCodePrefix);

        $params = [
            'timekeepingTable' => $timekeepingTable,
            'timekeepingAggregate' => $timekeepingAggregate,
            'dataKeeping' => $dataKeeping,
            'userCurrent' => $userCurrent,
            'teamCodePrefix' => $teamCodePrefix,
            'compensationDays' => $compensationDays,
            'arrHolidays' => $arrHolidays,
            'idTimeKeepingMax' =>  $timekeepingTable ? $timekeepingTable->timekeeping_table_id : '',
            'workingTimdDate' =>  $workingTimdDate[$idEmp],
        ];
        return view('manage_time::team.personal.timekeeping_detail')->with($params);
    }
    //=========== end team view timekeeping D lead ===========
        
    /**
     * isTimeInWorkingTime
     *
     * @param  string $time H:i
     * @param  array $workingTime
     * @return boolean
     */
    public function isTimeInWorkingTime($time, $workingTime)
    {
        $arrTime = $workingTime;
        if (isset($workingTime['morningInSetting'])) {     
            $arrTime = [
                $workingTime['morningInSetting']->format('H:i'),
                $workingTime['morningOutSetting']->format('H:i'),
                $workingTime['afternoonInSetting']->format('H:i'),
                $workingTime['afternoonOutSetting']->format('H:i')
            ];
        }
        return in_array($time, $arrTime);
    }
    
    /**
     * Create a file with name is id of timekeeping aggregate updated wfh
     *
     * @param Request $request
     * @return Response redirect
     */
    public static function getDataFromAggregateModulesWfh(Request $request)
    {
        try {
            $dataRequest = $request->only('timekeeping_table_id', 'empids');
            if (!Storage::exists(ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE)) {
                Storage::makeDirectory(ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE, ManageTimeView::ACCESS_FOLDER);
            }
            @chmod(storage_path('app/' . ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE), ManageTimeView::ACCESS_FOLDER);
            if (!Storage::exists('process')) {
                Storage::makeDirectory('process');
            }
            $files = Storage::files(ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE);
            $empIdOld = [];
            if ($files) {
                foreach ($files as $file) {
                    $excel = [];
                    if ($file == ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE . '/' . $dataRequest['timekeeping_table_id'] . '.csv') {
                        $excel = Excel::selectSheetsByIndex(0)->load(storage_path(self::FOLDER_APP . $file), function ($reader) {
                        })->get()->toArray();
                        if (count($excel)) {
                            if ($excel[0]['emp_ids'] != '') {
                                $empIdOld['emp_ids'] = explode('_', $excel[0]['emp_ids']);
                            } else {
                                $empIdOld['emp_ids'] = [];
                            }
                        }
                    }
                }
            }
            @chmod(storage_path('app/process'), ManageTimeView::ACCESS_FOLDER);

            // Move file to folder
            $fileName = $dataRequest['timekeeping_table_id'] . '.csv';
            $folderPath = storage_path('app/' . ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE);
            Excel::create($dataRequest['timekeeping_table_id'], function ($excel) use ($dataRequest, $empIdOld) {
                    $excel->sheet('Sheet 1', function ($sheet) use ($dataRequest, $empIdOld) {
                        $data = [];
                        $data[0] = ['timekeeping_table_id', 'emp_ids'];
                        $data[1] = array_values($dataRequest);
                        if (isset($data[1][1])) {
                            $data[1][1] = implode('_', $data[1][1]);
                            if (count($empIdOld)) {
                                if (count($empIdOld['emp_ids'])) {
                                    $data[1][1] = implode('_', array_unique(array_merge($dataRequest['empids'], $empIdOld['emp_ids'])));
                                } else {
                                    $data[1][1] = '';
                                }
                                unset($empIdOld);
                            }
                        }
                        $sheet->fromArray($data, null, 'A1', true, false);
                    });
                })->store('csv', storage_path('app/' . ManageTimeView::FOLDER_SALARY_RATE_AGGREGATE));

            @chmod($folderPath . '/'. $fileName, ManageTimeView::ACCESS_FOLDER);

            $messages = [
                'success' => [
                    Lang::get('manage_time::view.Data will be updated within 5 minutes'),
                ]
            ];
            return redirect()->back()->with('messages', $messages);
        } catch (Exception $ex) {
            Log::info($ex);
            return redirect()->back()->with('messages', [
                'errors' => ['Lỗi. Vui lòng liên hệ với người quản trị để được hỗ trợ.']
            ]);
        }
    }
    
    public function setWorkingTimeOfEmployee($workingTimeDefault)
    {
        return [
            'morningInSetting' => clone $workingTimeDefault['morningInSetting'],
            'morningOutSetting' => clone $workingTimeDefault['morningOutSetting'],
            'afternoonInSetting' => clone $workingTimeDefault['afternoonInSetting'],
            'afternoonOutSetting' => clone $workingTimeDefault['afternoonOutSetting'],
        ];
    }

    //========== start late minute report ==========
    /**
     * report late minute of employee
     *
     * @param  Request $request
     * @return view
     */
    public function reportMinuteLate(Request $request)
    {
        Breadcrumb::add('Team');
        if (!Permission::getInstance()->isAllow()) {
            View::viewErrorPermission();
        }
        $filter = $this->getFilterReportMinute();
        $userCurrent = Permission::getInstance()->getEmployee();
        $objProject = new Project();

        $arrEmpPermission = $this->getEmployeeByPermission($userCurrent->id, $filter);
        $collectionModel = $this->getMinuteLate($arrEmpPermission['emp_ids'], $arrEmpPermission['cate'], $filter);
        $teamsOption = $arrEmpPermission['teams'];
        $dateStart = substr($filter['start_month'], 3, 4) . '-' . substr($filter['start_month'], 0, 2) . '-01';
        $projects = $objProject->getProjectByPermission($arrEmpPermission['cate'], $userCurrent->id, $dateStart);

        $param = [
            'startMonth' => $filter['start_month'] ,
            'endMonth' => $filter['end_month'],
            'collectionModel' => $collectionModel,
            'projects' => $projects,
            'teamsOption' => $teamsOption,
            'filter' => $filter,
            'optionsCompare' => ManageTimeCommon::optionsCompare(),
        ];
        return view('manage_time::team.report_minute_late', $param);
    }
    
    /**
     * getFilterReportMinute
     *
     * @param  string $route
     * @return array
     */
    public function getFilterReportMinute($route = null)
    {
        $filter = Form::getFilterData(null, null, $route);
        if (isset($filter['except']) && isset($filter['except']['start_month'])) {
            $startMonth = $filter['except']['start_month'];
            $endMonth = $filter['except']['end_month'];
        } else {
            $now = Carbon::now();
            $startMonth = $now->format('m-Y');
            $endMonth = $now->format('m-Y');
        }
        $filter['start_month'] = $startMonth;
        $filter['end_month'] = $endMonth;
        return $filter;
    }
    
    /**
     * get minute late by table timekeeping
     *
     * @param  array $empIds
     * @param  string $strPermission
     * @param  array $filter
     * @param  boolean $isExport
     * @return collection
     */
    public function getMinuteLate($empIds, $strPermission, $filter, $isExport = false)
    {
        $dateStart = substr($filter['start_month'], 3, 4) . '-' . substr($filter['start_month'], 0, 2) . '-01';
        $dateEnd = substr($filter['end_month'], 3, 4) . '-' . substr($filter['end_month'], 0, 2) . '-31';
        $objTimeKeeping = new TimeKeeping();

        if ($empIds || (!$empIds && $strPermission == 'company' &&
            (!isset($filter['except']['project_id']) && !isset($filter['except']['team_id'])))
        ) {
            $empTimekepping = $objTimeKeeping->getTimekeepingMinuteLate($dateStart, $dateEnd, $empIds, $isExport);
        } else {
            $empTimekepping = $objTimeKeeping->getTimekeepingMinuteLate($dateStart, $dateEnd, [-1], $isExport);
        }
        return $empTimekepping;
    }

    /**
     * get array employee by permission and filter
     *
     * @param  int $empId
     * @param  array $filter
     * @return array [cate => '', teams = [], emp_ids => []]
     */
    public function getEmployeeByPermission($empId, $filter = [])
    {
        $route = 'manage_time::division.late-minute-report';
        $objView = new ManageTimeView();

        $arrEmpPermission = $objView->getEmployeeByPermission($route, $empId);
        $empIdCombined = [];
        $teamIds = [];
        $filterExcept = isset($filter['except']) ? $filter['except'] : [];

        if (isset($filterExcept['project_id']) || isset($filterExcept['team_id'])) {
            if (isset($filterExcept['team_id']) && $filterExcept['team_id']) {    
                $teamChilds = TeamList::getTeamChildIds($filterExcept['team_id']);
                if ($arrEmpPermission['cate'] != 'company') {
                    $teamIds = array_intersect($arrEmpPermission['team_ids'], $teamChilds);
                } else {
                    $teamIds = $teamChilds;
                }
            }
            $empCombined = $objView->getEmpByProjectTeam(isset($filterExcept['project_id']) ? [$filterExcept['project_id']] : [], $teamIds);
            $empIdCombined = $empCombined->pluck('id')->toArray();
        }
        if (!isset($filterExcept['project_id']) && !isset($filterExcept['team_id'])) {
        } elseif ($arrEmpPermission['cate'] != 'company') {
            $arrEmpPermission['emp_ids'] = array_intersect($empIdCombined, $arrEmpPermission['emp_ids']);
        } elseif (isset($filterExcept['project_id']) || isset($filterExcept['team_id'])) {
            $arrEmpPermission['emp_ids'] = $empIdCombined;
        } else {}

        $teamsOption=[];
        $teamsOptionAll = TeamList::toOption(null, true, false);
        if ($arrEmpPermission['cate'] == 'company') {
            $teamsOption = $teamsOptionAll;
        }elseif ($arrEmpPermission['cate'] == 'team') {
            foreach($teamsOptionAll as $item) {
                if (in_array($item['value'], $arrEmpPermission['team_ids'])) {
                    $teamsOption[] = $item;
                }
            }
        } else{}
        $arrEmpPermission['teams'] = $teamsOption;
        return $arrEmpPermission;
    }
    
    /**
     * exportMinuteLate
     *
     * @param  Request $request
     * @return json
     */
    public function exportMinuteLate(Request $request)
    {
        try {
            $empIds = $request->arrItem;
            $route = route('manage_time::division.late-minute-report') . '/';
            $userCurrent = Permission::getInstance()->getEmployee();

            $heading = [
                trans('manage_time::view.Employee code'),
                trans('manage_time::view.Employee name'),
                trans('education::view.Division'),
                trans('manage_time::view.Total number of late trips'),
                trans('manage_time::view.Total late minutes'),
                trans('manage_time::view.Total fines late in')
            ];
            
            $filter = $this->getFilterReportMinute($route);
            $arrEmpPermission = $this->getEmployeeByPermission($userCurrent->id, $filter);
            if (!$empIds) {
                $empIds = $arrEmpPermission['emp_ids'];
            }
            $collectionModel = $this->getMinuteLate($empIds, $arrEmpPermission['cate'], $filter, true);
            return response()->json([
                'status' => 1,
                'data' => $collectionModel,
                'heading' => $heading
            ]);
        } catch(Exception $ex) {
            Log::info($ex);
            return response()->json([
                'status' => 0,
                'message' => trans('core::message.Error system, please try later!'),
            ]);
        }
    }
    //========== end late minute report ==========
    
    /**
     * lấy giá trị trong manage_time_timekeepings để làm tính tròn số trong một số TH nghỉ 1/4
     *
     * @param  mixed $dataEmpDate
     * @return void
     */
    public function getTimeKeepingByarray($dataEmpDate)
    {
        $collections = TimeKeeping::select(
            'timekeeping_date',
            'employee_id',
            'timekeeping',
            'timekeeping_number',
            'register_leave_no_salary',
            'register_leave_has_salary'
            )
            ->join('manage_time_timekeeping_tables', 'manage_time_timekeeping_tables.id', '=', 'manage_time_timekeepings.timekeeping_table_id')
            ->whereNull('manage_time_timekeeping_tables.deleted_at')
            ->where(function($query) use ($dataEmpDate) {
                foreach($dataEmpDate as $empId => $dates) {
                    $query->orWhere(function($q) use ($empId, $dates) {
                        $q->where('employee_id', $empId);
                        $q->whereIn('timekeeping_date', $dates);
                    });
                }
            });
        return $collections->get();
    }

    /**
     * tinh lại làm tròn cột timekeeping_number_register khi có TH nghỉ 1/4 và đăng ký thay đổi giờ
     * đúng ra ta cần làm tròn cột  'timekeeping_number' nhưng ta có thể làm tròn cột timekeeping_number_register
     * làm tròn timekeeping_number_register or bsc
     * 
     * @param  array $dataLeaveDay
     * @param  collection $dataTK
     * @return array
     */
    public function setAgainDataTk($datas, $dataTK)
    {
        foreach ($dataTK as $item) {
            $key = $item->employee_id . '-' . $item->timekeeping_date;
            if (isset($datas[$key])) {
                $leaveNoSalary = isset($datas[$key]['register_leave_no_salary']) ? $datas[$key]['register_leave_no_salary'] : 0;
                $leaveHasSalary = isset($datas[$key]['register_leave_has_salary']) ? $datas[$key]['register_leave_has_salary'] : 0;
                $leaveBasicSalary = isset($datas[$key]['register_leave_basic_salary']) ? $datas[$key]['register_leave_basic_salary'] : 0;
                $total = $datas[$key]['timekeeping_number_register'] + $leaveNoSalary + $leaveHasSalary + $leaveBasicSalary + $item->timekeeping_number;
                if ($total >= 0.8 && $total != 1 && isset($datas[$key]['has_leave_day']) && !in_array($datas[$key]['has_leave_day'], [ManageTimeConst::HAS_LEAVE_DAY_MORNING, ManageTimeConst::HAS_LEAVE_DAY_AFTERNOON])) {
                    $datas[$key]['timekeeping_number_register'] += (1 - $total);
                }
                
                $supplement = isset($datas[$key]['register_supplement_number']) ? $datas[$key]['register_supplement_number'] : 0;
                $total = $supplement + $leaveNoSalary + $leaveHasSalary + $leaveBasicSalary;
                if ($supplement && empty((float)$item->timekeeping_number) && $total > 1) {
                    $datas[$key]['register_supplement_number'] += (1 - $total);
                }
                 if (isset($datas[$key]['has_supplement']) && $datas[$key]['has_supplement'] == 1
                        || (isset($datas[$key]['has_supplement']) && $datas[$key]['has_supplement'] == 3) && isset($datas[$key]['has_leave_day']) && $datas[$key]['has_leave_day'] == 2) {
                    $datas[$key]['timekeeping_number_register'] = 0;
                }
            }
        }
        return $datas;
    }

    /**
     * @param $collectionModel
     * @param $timeKeepingTable
     */
    public function exportAggregates($collectionModel, $timeKeepingTable)
    {
        $tkTableId = $timeKeepingTable->id;
        $data = [];
        $data[0] = [
            Lang::get('manage_time::view.Employee code'),
            Lang::get('manage_time::view.Employee fullname'),
            Lang::get('manage_time::view.The total of official working days'),
            Lang::get('manage_time::view.The total of trial working days'),
            Lang::get('manage_time::view.Overtime on weekdays'),
            Lang::get('manage_time::view.Overtime on weekends'),
            Lang::get('manage_time::view.Overtime on holidays'),
            Lang::get('manage_time::view.Total number of late in'),
            Lang::get('manage_time::view.Total number of early out'),
            Lang::get('manage_time::view.CT'),
            Lang::get('manage_time::view.P'),
            'LCB',
            Lang::get('manage_time::view.KL'),
            Lang::get('manage_time::view.BS'),
            Lang::get('manage_time::view.L'),
        ];
        $data[0][] = Lang::get('manage_time::view.M1');
        $headCells = 'A1:AA1';
        $itemCells = 'C2:AA1';
        $data[0] = array_merge($data[0], [
            Lang::get('manage_time::view.OTKL'),
            Lang::get('manage_time::view.number days compensation'),
            Lang::get('manage_time::view.OT of official working'),
            Lang::get('manage_time::view.OT of trial working'),
            Lang::get('manage_time::view.The total of official working days to salary'),
            Lang::get('manage_time::view.The total of trial working days to salary'),
            Lang::get('manage_time::view.Total basic salary'),
        ]);

        foreach ($collectionModel as $key => $item) {
            $arrayTemp = [
                $item->employee_code,
                $item->employee_name,
                $item->total_official_working_days,
                $item->total_trial_working_days,
                $item->totalOTWeekdays,
                $item->totalOTWeekends,
                $item->totalOTHolidays,
                $item->total_number_late_in,
                $item->total_number_early_out,
                $item->totalRegisterBusinessTrip,
                $item->totalLeaveDayHasSalary,
                $item->totalLeaveDayBasic,
                $item->total_leave_day_no_salary,
                $item->totalRegisterSupplement,
                $item->totalHoliday,
            ];

            $arrayTemp[] = $item->total_late_start_shift;
            $arrayTemp = array_merge($arrayTemp, [
                $item->total_ot_no_salary,
                $item->total_num_com,
                $item->totalOTOfficial,
                $item->totalOTTrial,
            ]);
            
            $arrayTemp = array_merge($arrayTemp, [
                $item->total_working_officail,
                $item->total_working_trial,
                $item->total_official_leave_basic_salary,
            ]);
            $data[] = $arrayTemp;
        }

        Excel::create(Lang::get('manage_time::view.Timekeeping aggregate month :month', ['month' => $timeKeepingTable->month]), function($excel) use($data, $headCells, $itemCells) {
            $excel->sheet('Sheet 1', function($sheet) use($data, $headCells, $itemCells) {
                $sheet->fromArray($data, null, 'A1', true, false);
                $sheet->cells($headCells, function($cells) {
                    $cells->setFontWeight('bold');
                    $cells->setBackground('#D3D3D3');
                    $cells->setAlignment('center');
                    $cells->setValignment('center');
                });

                $countData = count($data);
                $sheet->cells("{$itemCells}{$countData}", function($cells) {
                    $cells->setAlignment('right');
                    $cells->setValignment('center');
                });
                $sheet->cells("A2:B{$countData}", function($cells) {
                    $cells->setAlignment('left');
                    $cells->setValignment('center');
                });

                $sheet->setHeight([
                ]);
                $sheet->setAutoSize(['A', 'B']);
                $sheet->setWidth([
                    'J' => 10,
                    'K' => 10,
                    'L' => 10,
                    'M' => 10,
                    'N' => 10,
                    'O' => 10,
                    'P' => 10,
                ]);

                $sheet->setBorder($headCells, 'thin');
            });
            $excel->getActiveSheet()->getStyle('A1:AL1')->getAlignment()->setWrapText(true);
        })->download('xlsx');
    }

}
