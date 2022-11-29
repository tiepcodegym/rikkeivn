<?php

namespace Rikkei\ManageTime\Model;

use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Log;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Form;
use Rikkei\ManageTime\Model\LeaveDayBaseline;
use Rikkei\ManageTime\Model\LeaveDayHistories;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\View\LeaveDayPermission;
use Rikkei\ManageTime\View\ManageLeaveDay;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission as PermissionView;
use Rikkei\Team\View\Permission;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Rikkei\Core\View\TimeHelper;
use Rikkei\Core\Model\EmailQueue;

class LeaveDay extends CoreModel
{
    const MAX_LEAVE_DAY_CURRENT_YEAR = 12;
    const DATE_CALCULATE_SOCIAL_INSURANCE = 17;
    const SENIORITY_YEAR = 3;

    const MAX_LEAVE_DAY_JAPAN = 20;
    const LEAVE_SIX_MONTH = 10;
    const NUMBER_LEAVE_DAYS_JAPAN_NEW = 2;

    const LEAVE_DAY_JAPAN_NOTICE = 5;
    const DATE_CHANGE_LEAVE_DAY = '2020-03-01';

    // Thời gian nghỉ dài hạn 
    const LEAVE_DAY_UNPAID_LIMIT = 30;
    const DAYS_OF_MONTH = 30;

    // Định nghĩa số tháng làm việc từ lúc vào công ty
    const WORKING_MONTH_0_MONTH = 0; // Mới vào công ty làm việc
    const WORKING_MONTH_2_MONTH = 2; // Làm 2 tháng
    const WORKING_MONTH_4_MONTH = 4; // Làm 4 tháng
    const WORKING_MONTH_6_MONTH = 6; // Làm 6 tháng
    const WORKING_MONTH_18_MONTH = 18; // Làm 1 năm 6 tháng

    use SoftDeletes;
    
    protected $table = 'leave_days';
    public $timestamps = false;

    public static function getGridData($export = null, $month = null)
    {
        $leaveDayTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $url = route('manage_time::admin.manage-day-of-leave.index').'/';
        $model = self::class;
        if ($month) {
            $model = LeaveDayBaseline::class;
            $leaveDayTable = LeaveDayBaseline::getTableName();
            $firstDay = $month.'-01';
        } else {
            $firstDay = date('Y-m-01');
        }

        $getTotalDay = DB::raw($leaveDayTable . '.day_last_transfer + ' . $leaveDayTable . '.day_current_year +' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot');

        $getRemainDay = DB::raw($leaveDayTable. '.day_last_transfer + ' . $leaveDayTable .'.day_current_year + ' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot - ' . $leaveDayTable . '.day_used');

        $collection = $model::select(
            "{$leaveDayTable}.id",
            "{$leaveDayTable}.employee_id",
            "{$leaveDayTable}.day_last_year",
            "{$leaveDayTable}.day_last_transfer",
            "{$leaveDayTable}.day_current_year",
            "{$leaveDayTable}.day_seniority",
            "{$leaveDayTable}.day_ot",
            "{$leaveDayTable}.day_used",
            "{$leaveDayTable}.created_at",
            "{$leaveDayTable}.updated_at",
            "{$leaveDayTable}.note",
            "{$employeeTable}.employee_code",
            "{$employeeTable}.name",
        	DB::raw("{$getTotalDay} as total_day"),
        	DB::raw("{$getRemainDay} as remain_day")
        );

        $now = Carbon::now();
        $collection->join("{$employeeTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
            ->whereNotIn("{$employeeTable}.account_status", [getOptions::PREPARING, getOptions::FAIL_CDD])
            ->where(function ($query) use ($employeeTable, $now, $firstDay) {
                $query->whereNull("{$employeeTable}.leave_date")
                    // ->orWhereDate("{$employeeTable}.leave_date", '>=', $now->format('Y-m-d'));
                    ->orWhereDate("{$employeeTable}.leave_date", '>=', $firstDay);
            });

        //filter month
        if ($month) {
            $collection->where($leaveDayTable . '.month', $month);
        }

        $totalDayFilter = Form::getFilterData('spec', 'total_day', $url);
        if (isset($totalDayFilter)) {
            $collection->where($getTotalDay, '=', $totalDayFilter);
        }

        $remainDayFilter = Form::getFilterData('spec','remain_day', $url);
        if (isset($remainDayFilter)) {
            $collection->where($getRemainDay, '=', $remainDayFilter);
        }

        //Filter by permission
        $curEmp = Auth::user();
        if (Permission::getInstance()->isScopeCompany()) {
            
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $teamsOfEmp = Permission::getInstance()->isScopeTeam(null, 'manage_time::admin.manage-day-of-leave.index');
            $teamMemTbl = TeamMember::getTableName();
            $collection->join("{$teamMemTbl}", "{$teamMemTbl}.employee_id", "=", "{$employeeTable}.id")
                       ->whereIn("{$teamMemTbl}.team_id", $teamsOfEmp);
        }

        $pager = Config::getPagerData();
        $collection->groupBy('employees.id');
        if (Form::getFilterPagerData('order', $url)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }

        $tblFilter = View::getFilterLeaveDayTable();
        if ($tblFilter && $tblFilter !== $leaveDayTable) {
            $filter = Form::getFilterData(null, null, $url);
            if (!empty($filter)) {
                if (isset($filter['number']) && is_array($filter['number'])) {
                    $filterNumber = [];
                    foreach ($filter['number'] as $col => $filterValue) {
                        $colChange = preg_replace("/{$tblFilter}/", $leaveDayTable, $col);
                        $filterNumber[$colChange] = $filterValue;
                        $collection = $collection->where($colChange, $filterValue);
                    }
                    $filter['number'] = $filterNumber;
                }
                if (isset($filter["{$tblFilter}.note"])) {
                    $filterValue = $filter["{$tblFilter}.note"];
                    $collection = $collection->where("{$leaveDayTable}.note", 'LIKE', "%{$filterValue}%");
                    unset($filter["{$tblFilter}.note"]);
                    $filter["{$leaveDayTable}.note"] = $filterValue;
                }
                CookieCore::setRaw('filter.' . $url, $filter);
            }
        } else {
            static::filterGrid($collection, [], $url, 'LIKE');
        }

        if($export == null) {
            self::pagerCollection($collection, $pager['limit'], $pager['page']);
        }

        return $collection;
    }

    /**
     * Get leave day of employee by id
     *
     * @param id
     * @return collection
     */
    public static function getLeaveDayById($id)
    {
        $leaveDayTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $model = self::class;
        $getTotalDay = DB::raw($leaveDayTable . '.day_last_transfer + ' . $leaveDayTable . '.day_current_year +' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot');

        $getRemainDay = DB::raw($leaveDayTable. '.day_last_transfer + ' . $leaveDayTable .'.day_current_year + ' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot - ' . $leaveDayTable . '.day_used');

        $offcial_date = DB::table('employees')->where("id", $id)->first()->offcial_date;
        $date = Carbon::parse($offcial_date);
        $now = Carbon::now();
        $diff = $date->diffInYears($now);
        if ((int)$diff < 1 ) {
            $getRemainDayFeatureNow = $getRemainDay;
            $getRemainDay =  DB::raw($leaveDayTable. '.day_last_transfer + ' . $leaveDayTable .'.day_current_year + ' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot - ' . $leaveDayTable . '.day_used'. ' + 12 - '. Carbon::now()->month);
        } else {
            $getRemainDayFeatureNow = DB::raw($leaveDayTable. '.day_last_transfer + ' . $leaveDayTable .'.day_current_year + ' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot - ' . $leaveDayTable . '.day_used'. ' - 12 +'. Carbon::now()->month);
        }
       
       
        $collection = $model::select(
            "{$leaveDayTable}.id",
            "{$leaveDayTable}.employee_id",
            "{$leaveDayTable}.day_last_year",
            "{$leaveDayTable}.day_last_transfer",
            "{$leaveDayTable}.day_current_year",
            "{$leaveDayTable}.day_seniority",
            "{$leaveDayTable}.day_ot",
            "{$leaveDayTable}.day_used",
            "{$leaveDayTable}.created_at",
            "{$leaveDayTable}.updated_at",
            "{$leaveDayTable}.note",
            "{$employeeTable}.employee_code",
            "{$employeeTable}.name",
            DB::raw("{$getTotalDay} as total_day"),
            DB::raw("{$getRemainDay} as remain_day"),
            DB::raw("{$getRemainDayFeatureNow} as remain_day_feature_now")
        )->where("{$employeeTable}.id", $id);

        $now = Carbon::now();
        $collection->join("{$employeeTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
            ->whereNotIn("{$employeeTable}.account_status", [getOptions::PREPARING, getOptions::FAIL_CDD]);
        $collection->groupBy('employees.id');

        return $collection->first();
    }

    public static function getInformationLeaveDayOfEmp($employeeId)
    {
        $leaveDayTable = self::getTableName();
        $employeeTable = Employee::getTableName();

        $remainDays = DB::raw($leaveDayTable.'.day_last_transfer + '.$leaveDayTable.'.day_current_year + ' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot - ' . $leaveDayTable . '.day_used');

        $informationLeaveDay = self::select(
            "{$leaveDayTable}.id",
            "{$leaveDayTable}.day_used",
            DB::raw("{$remainDays} as remain_day"),
            "{$leaveDayTable}.employee_id"
        );
        return $informationLeaveDay->join("{$employeeTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
            ->where("{$employeeTable}.id", $employeeId)
            ->first();
    }

    public static function updateAndDelete($employeeId)
    {
        $leaveDay = self::where('employee_id', $employeeId)->first();
        if ($leaveDay) {
            $leaveDay->day_last_year = 0;
            $leaveDay->day_last_transfer = 0;
            $leaveDay->day_current_year = 0;
            $leaveDay->day_seniority = 0;
            $leaveDay->day_ot = 0;
            $leaveDay->day_used = 0;
            $leaveDay->note = null;
            $leaveDay->save();
            $leaveDay->delete();
        }
    }

    /**
     * Run on january 1st every year to update day off for all employees
     */
    public static function cronJobUpdateLeaveDayYearly()
    {
        DB::beginTransaction();
        try {
            static::updateYearly();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }

    public static function updateYearly()
    {
        $leaveDayTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $data = [];
        $dateLastYear = Carbon::now()->subYears(1);
        $empModel = new Employee();
        $empIdsInJapan = $empModel->getAllEmpIdInJapan();
        $getRemainDay = DB::raw($leaveDayTable.'.day_last_transfer + '.$leaveDayTable.'.day_current_year + ' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot - ' . $leaveDayTable . '.day_used');
        $now = Carbon::now();

        $leaveDayEmployees = Employee::select(
                "{$leaveDayTable}.id",
                "{$employeeTable}.id as employee_id",
                "{$leaveDayTable}.day_last_year",
                "{$leaveDayTable}.day_last_transfer",
                "{$leaveDayTable}.day_current_year",
                "{$leaveDayTable}.day_seniority",
                "{$leaveDayTable}.day_used",
                "{$employeeTable}.offcial_date",
                DB::raw("{$getRemainDay} as remain_day")
            )
            ->leftJoin("{$leaveDayTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
            ->where(function ($query) use ($employeeTable, $now) {
                $query->whereNull("{$employeeTable}.leave_date")
                    ->orWhereDate("{$employeeTable}.leave_date", '>=', $now->format('Y-m-d'));
            })
            ->whereNotNull("join_date")
            ->where(function ($query) {
                $query->whereNotNull("trial_date")
                      ->orWhereNotNull("offcial_date");
            })
            ->whereNotIn("{$employeeTable}.id", $empIdsInJapan)
            ->get();
        // get employee have register leave day in year new
        $leaveDayEmployeesYearNew = static::getLeaveDayReister();

        if (count($leaveDayEmployees)) {
            $dataInsertHistories = [];
            foreach ($leaveDayEmployees as $item) {
                $employeeId = $item->employee_id;
                $leaveDay = LeaveDay::where('employee_id', $employeeId)->first();
                if (!$leaveDay) {
                    $leaveDay = new LeaveDay();
                    $leaveDay->employee_id = $employeeId;
                }
                $newData = [];

                //Calculate normal day off
                if (!empty($item->offcial_date)) {
                    $offcialDate = Carbon::parse($item->offcial_date)->addDays(LeaveDayRegister::getUnpaidLeaveDay($employeeId, $item->offcial_date));
                    if (strtotime(Carbon::parse($offcialDate)->format('Y-m-d')) <= strtotime($dateLastYear->format('Y-m-d'))) {
                        $newData['day_current_year'] = 12;
                    } elseif (strtotime(Carbon::parse($offcialDate)->format('Y-m-d')) < strtotime(date('Y-m-d'))) {
                        $newData['day_current_year'] = 1;
                    } else {
                        $newData['day_current_year'] = 0;
                    }
                } else {
                    $newData['day_current_year'] = 0;
                }

                //Calculate seniority day off
                if (!empty($item->offcial_date)) {
                    $offcialDate = Carbon::parse($item->offcial_date)->addDays(LeaveDayRegister::getUnpaidLeaveDay($employeeId, $item->offcial_date));
                    $nowDate = date_create(date("Y-m-d"));
                    $diff=date_diff($offcialDate,$nowDate);
                    $diffYear = intval($diff->format("%y"));
                    $newData['day_seniority'] = intval($diffYear / self::SENIORITY_YEAR);
                } else {
                    $newData['day_seniority'] = 0;
                }

                //Calculate day transfer from last year to this year
                if (!isset($newData['day_seniority'])) {
                    $newData['day_seniority'] = 0;
                }
                if ($newData['day_seniority'] + $newData['day_current_year'] >= ManageTimeConst::MAX_DAY) {
                    $newData['day_seniority'] -=  $newData['day_seniority'] + $newData['day_current_year'] - ManageTimeConst::MAX_DAY;
                }
                $totalDayOff = $item->remain_day + $newData['day_seniority'] + $newData['day_current_year'];
                $item->remain_day = empty($item->remain_day) ? 0 : $item->remain_day;
                $newData['day_last_transfer'] = static::getDayLastTransfer($totalDayOff, $item->remain_day);

                $newData['day_last_year'] = $item->remain_day;
                $newData['day_used'] = 0;
                $newData['day_ot'] = 0;
                // reset time when have leave register in year
                if (array_key_exists($employeeId, $leaveDayEmployeesYearNew)) {
                    $newData['day_used'] = $leaveDayEmployeesYearNew[$employeeId];
                }

                //Save histories
                $leaveDayPermis = new LeaveDayPermission();
                $change = $leaveDayPermis->findChanges($leaveDay, $newData);
                if (count($change)) {
                    $leaveDayHistory = new LeaveDayHistories();
                    $dataInsertHistories[] = [
                        'id' => $leaveDayHistory->id,
                        'employee_id' => $employeeId,
                        'content' => json_encode($change),
                        'type' => LeaveDayHistories::TYPE_AUTO,
                        'created_by' => Auth::id(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }

                $leaveDay->setData($newData);
                $leaveDay->save();
            }
            if (count($dataInsertHistories)) {
                LeaveDayHistories::insert($dataInsertHistories);
            }
        }
    }

    /**
     * Calculate days transfer from last year
     *
     * @param float $totalDayOff
     * @param float $remainDays
     *
     * @return float
     */
    public static function getDayLastTransfer($totalDayOff, $remainDays)
    {
        if ($totalDayOff <= ManageTimeConst::MAX_DAY) {
            return $remainDays;
        }

        return $remainDays - ($totalDayOff - ManageTimeConst::MAX_DAY);
    }

    /**
     * Run first of month to add days off for employee less 1 year
     * Without employees're working in Japan
     */
    public static function cronJobUpdateLeaveDayMonthly()
    {
        DB::beginTransaction();
        try {
            $dateCurrent = Carbon::now();
            $dayCurrent = $dateCurrent->day;
            $monthCurrent = $dateCurrent->month;
            $leaveDayTable = self::getTableName();
            $employeeTable = Employee::getTableName();
            $listIdEmpLeaveUnpaid = LeaveDayRegister::getEmpUnpaidLeaveDay()
                ->lists("creator_id")
                ->toArray(); 
            if ($dayCurrent == 1 && $monthCurrent != 1) {
                $empModel = new Employee();
                $empIdsInJapan = $empModel->getAllEmpIdInJapan();
                $empExist = LeaveDay::withTrashed()->lists('employee_id')->toArray();
                $builder = self::rightJoin("{$employeeTable} as {$employeeTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
                    ->whereNull("{$employeeTable}.leave_date")
                    ->whereDate("{$employeeTable}.offcial_date", "<", $dateCurrent->toDateString())
                    ->whereDate("{$employeeTable}.offcial_date", ">", $dateCurrent->subYears(1)->toDateString())
                    // thêm sql not in - xét các đơn nghỉ dài hạn trong tháng quá số ngày thì không cộng phép
                    ->whereNotIn("{$employeeTable}.id", $listIdEmpLeaveUnpaid)
                    ->whereNotIn("{$employeeTable}.id", $empIdsInJapan)
                    ->withTrashed();

                //Save histories
                $leaveDayInfo = $builder->select('leave_days.day_current_year', 'employees.id')->get();
                $dataInsertHistories = static::getDataInsertHistories($leaveDayInfo, ['day_current_year']);
                if (count($dataInsertHistories)) {
                    LeaveDayHistories::insert($dataInsertHistories);
                }

                $builder->update([
                    "{$leaveDayTable}.day_current_year" => DB::raw("{$leaveDayTable}.day_current_year + 1"),
                    "{$leaveDayTable}.updated_at" => Carbon::now(),
                ]);
                static::insertNotExistNotJaPan($builder, $empExist, 1);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }

    /**
     * Run daily to add days off for employees
     * Normal: if working time = 1 year then add 12 - days off had.
     */
    public static function cronJobUpdateLeaveDayDaily()
    {
        DB::beginTransaction();
        try {
            $dateCurrent = Carbon::now();
            $dayCurrent = $dateCurrent->day;
            $monthCurrent = $dateCurrent->month;
            $numberDayAdd = 12 - $monthCurrent;
            if ($dateCurrent->day == 1) {
                $numberDayAdd += 1;
            }
            if (!($dayCurrent == 1 && $monthCurrent == 1)) {
                $empModel = new Employee();
                $empIdsInJapan = $empModel->getAllEmpIdInJapan();
                $leaveDayTable = self::getTableName();
                $employeeTable = Employee::getTableName();
                
                $now = Carbon::now();
                // Lấy danh sách nhân viên có đơn nghỉ dài hạn tính đến khi đc 1 năm
                $listEmpUnpaid = LeaveDayRegister::getEmpUnpaidLeaveDay()
                ->get(); 

                // Lấy theo list Id
                $listIdEmpLeaveUnpaid = LeaveDayRegister::getEmpUnpaidLeaveDay()
                ->lists("creator_id")
                ->toArray(); 

                // Init builder
                $builder = self::join("{$employeeTable} as {$employeeTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
                    ->where(function ($query) use ($employeeTable, $now) {
                        $query->whereNull("{$employeeTable}.leave_date")
                            ->orWhereDate("{$employeeTable}.leave_date", '>=', $now->format('Y-m-d'));
                    })
                    ->where(DB::raw("DATE_ADD({$employeeTable}.offcial_date, INTERVAL 1 YEAR)"), '=', Carbon::now()->toDateString())
                    ->whereNotIn("{$employeeTable}.id", $empIdsInJapan);
                
                // Get changes for employees exist in table leave_days
                $dayAdded = $builder->select('leave_days.day_current_year', 'employees.id')->get();
                $fieldsSave = ['day_current_year'];
                $dataInsertHistories = static::getDataInsertHistories($dayAdded, $fieldsSave, $numberDayAdd);

                //Update for employees exist in Viet Nam have not unpaid leave day
                $builder->whereNotIn("{$employeeTable}.id", $listIdEmpLeaveUnpaid)->update([
                        "{$leaveDayTable}.day_current_year" => DB::raw("{$leaveDayTable}.day_current_year + {$numberDayAdd}"),
                        "{$leaveDayTable}.updated_at" => Carbon::now(),
                ]);

                // Update for employees exist in Viet Nam has unpaid leave day
                foreach ($listEmpUnpaid as $emp) {
                    // convert data type
                    $emp->sub_days_leave = (int)$emp->sub_days_leave;
                    // Update
                    $builder->where("{$employeeTable}.id", $emp->creator_id)->update([
                        "{$leaveDayTable}.day_current_year" => DB::raw("{$leaveDayTable}.day_current_year + {$numberDayAdd} - {$emp->sub_days_leave}"),
                        "{$leaveDayTable}.updated_at" => Carbon::now(),
                ]);
                }
                //save history
                if (count($dataInsertHistories)) {
                    LeaveDayHistories::insert($dataInsertHistories);
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex);
        }
    }

    public static function updateLeaveDayDailyJapan()
    {
        $empTeamHistoryTable = EmployeeTeamHistory::getTableName();
        $leaveDayTable = self::getTableName();
        $employeeTable = Employee::getTableName();

        //Variable store changes
        $dataInsertHistories = [];
        $fieldsSave = ['day_current_year'];

        $levelsLeaveJapan = static::levelsLeaveJapan();
        
        $employeeIds = [];
        $empExist = LeaveDay::withTrashed()->lists('employee_id')->toArray();
        foreach ($levelsLeaveJapan as $itemLevel) {
            $dayAdded = $itemLevel['value'];
            $dayAddedNew = $itemLevel['value'];
            $dayAddedNew2 = $itemLevel['value'];

            // Init builder Jp
            $condition = [
                'where' => 'whereDate',
                'field' => "{$empTeamHistoryTable}.start_at",
                'comparation' => $itemLevel['comparation'],
                'value' => $itemLevel['months'],
            ];
            if (isset($itemLevel['more'])) {
                $condition['more'] = $itemLevel['more'];
            }
            $builder = LeaveDay::getJapanBuilder($condition, $employeeIds);

            $emps1YearJpDayAdded = $builder->select(
                'leave_days.day_last_year',
                'leave_days.day_last_transfer',
                'leave_days.day_current_year',
                'leave_days.day_seniority',
                'leave_days.day_ot',
                'leave_days.day_used',
                "{$empTeamHistoryTable}.employee_id as id",
                "{$empTeamHistoryTable}.start_at"
            )
            ->get();
            foreach ($emps1YearJpDayAdded as $itemBuilder) {
                if (!in_array($itemBuilder->id, $employeeIds)) {
                    $employeeIds[] = $itemBuilder->id;
                }
            }

            $dataInsertHistories = array_merge($dataInsertHistories, static::getDataInsertHistories($emps1YearJpDayAdded, $fieldsSave, $dayAdded, true, $itemLevel['is6Months'], $itemLevel["months"]));

            $builderNew = clone $builder;
            $builderNew2 = clone $builder;
            $builder = $builder->where(DB::raw("date({$empTeamHistoryTable}.start_at)"), '<', '2019-07-01');
            $builderNew = $builderNew->where(DB::raw("date({$empTeamHistoryTable}.start_at)"), '>=', '2019-07-01')
                ->where(DB::raw("date({$empTeamHistoryTable}.start_at)"), '<', '2020-03-01');
            $builderNew2 = $builderNew2->where(DB::raw("date({$empTeamHistoryTable}.start_at)"), '>=', '2020-03-01');

            if ($builder->count()) {
                if ($itemLevel['months'] == 6) {
                    $dayAdded = 10;
                    $builder->update([
                        "{$leaveDayTable}.day_current_year" => $dayAdded,
                        "{$leaveDayTable}.updated_at" => Carbon::now(),
                    ]);
                } elseif ($itemLevel['months'] > 7) {
                    $builder = static::updateLeaveDayJapan($builder, $dayAdded, $itemLevel['months']);
                } else {
                    $builder->where("{$employeeTable}.id", "=", -1);
                    //do not some thing
                }
            }

            if ($builderNew->count()) {
                if ($itemLevel['is6Months']) {
                    $builderNew->update([
                        "{$leaveDayTable}.day_current_year" => $dayAddedNew,
                        "{$leaveDayTable}.updated_at" => Carbon::now(),
                    ]);
                } else {
                    $builderNew = static::updateLeaveDayJapan($builderNew, $dayAddedNew, $itemLevel['months']);
                }
            }

            if ($builderNew2->count()) {
                if ($itemLevel['months'] == 6) {
                    $dayAddedNew2 = 4;
                    $builderNew2 = static::updateLeaveDayJapan($builderNew2, $dayAddedNew2, $itemLevel['months']);
                } elseif ($itemLevel['months'] == 7) {
                    $builderNew2->where("{$employeeTable}.id", "=", -1);
                } else {
                    $builderNew2 = static::updateLeaveDayJapan($builderNew2, $dayAddedNew2, $itemLevel['months']);
                }
            }

            // Update for employees japan not exist
            static::insertNotExist($builder, $empExist, $dayAdded);
            static::insertNotExist($builderNew, $empExist, $dayAddedNew);
            static::insertNotExist($builderNew2, $empExist, $dayAddedNew2);
        }

        //save history
        if (count($dataInsertHistories)) {
            LeaveDayHistories::insert($dataInsertHistories);
        }

        //cộng ngày phép cho japan khi vừa vào hoặc chuyển team sang japan
        if (Carbon::now()->format('Y-m-d') > '2020-03-01') {
            $objLeaveDay = new LeaveDay();
            $empJoinJaPanNew = $objLeaveDay->getEmpJoinJanPanMin(Carbon::now()->subDay());
            $objLeaveDay->updateInsertLeaveDay($empJoinJaPanNew, $empExist, self::NUMBER_LEAVE_DAYS_JAPAN_NEW);
        }
    }

    /**
     * store levels leave day in Japan
     * months: number of working months in Japan
     * value: number leave day added
     * comparation: =, <, >, <=, >=
     * is6Months: check case months is 6
     * more: check case months > 78
     *
     * @return array
     */
    public static function levelsLeaveJapan()
    {
        return [
            [
                'months' => 78,
                'value' => 20,
                'comparation' => '=',
                'is6Months' => false,
                'more' => true,
            ],
            [
                'months' => 66,
                'value' => 18,
                'comparation' => '=',
                'is6Months' => false,
            ],
            [
                'months' => 54,
                'value' => 16,
                'comparation' => '=',
                'is6Months' => false,
            ],
            [
                'months' => 42,
                'value' => 14,
                'comparation' => '=',
                'is6Months' => false,
            ],
            [
                'months' => 30,
                'value' => 12,
                'comparation' => '=',
                'is6Months' => false,
            ],
            [
                'months' => 18,
                'value' => 11,
                'comparation' => '=',
                'is6Months' => false,
            ],
            [
                'months' => 7,
                'value' => 4,
                'comparation' => '=',
                'is6Months' => false,
            ],
            [
                'months' => 6,
                'value' => 2,
                'comparation' => '=',
                'is6Months' => false,
            ],
            [
                'months' => 4,
                'value' => 2,
                'comparation' => '=',
                'is6Months' => false,
            ],
            [
                'months' => 2,
                'value' => 2,
                'comparation' => '=',
                'is6Months' => true,
            ],
        ];
    }

    /**
     * Builder japan cron add leave day
     * @param array $condition  condition for every case in function levelsLeaveJapan()
     * @return builder
     */
    public static function getJapanBuilder($condition, $employeeIds = [])
    {
        $leaveDayTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $empTeamHistoryTable = EmployeeTeamHistory::getTableName();
        $now = Carbon::now();
        $builder = self::rightJoin("{$employeeTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
            ->rightJoin("{$empTeamHistoryTable}", "{$empTeamHistoryTable}.employee_id", "=", "{$leaveDayTable}.employee_id")
            ->join('teams', 'teams.id', '=', "{$empTeamHistoryTable}.team_id")
            ->where(function ($query) {
                $query->where("teams.code", 'LIKE', 'japan%')
                    ->orWhereRaw(DB::raw("teams.parent_id IN (
                            SELECT id 
                            FROM teams
                            WHERE teams.code = 'japan')"))
                    ->groupBy('teams.id');
            })
            ->where(function ($query) use ($employeeTable, $now) {
                $query->whereNull("{$employeeTable}.leave_date")
                    ->orWhereDate("{$employeeTable}.leave_date", '>=', $now->format('Y-m-d'));
            })
            ->whereNull("{$empTeamHistoryTable}.deleted_at");
        // add 30/1  1month  28/2 -- add 28, 29, 30 và 31 đều bằng 28
        // sub 28/2  1month 28/1
        // vao lam 29, 30, 31 thang 1 ???

        $where = $condition['where'];
        $field = $condition['field'];
        $comparation = $condition['comparation'];
        $value = $condition['value'];
        $tblJapan = "(SELECT  MAX(eth1.id) as MAXID, eth1.employee_id FROM employee_team_history as eth1
            LEFT JOIN teams ON eth1.team_id = teams.id
            WHERE (teams.code LIKE 'japan%'  OR teams.parent_id IN (SELECT id FROM teams WHERE teams.code = 'japan'))
            AND eth1.end_at IS NULL
            AND eth1.deleted_at IS NULL
            GROUP BY eth1.employee_id)";
        $sql = "{$empTeamHistoryTable}.id IN
            (SELECT MIN(eth3.id) FROM employee_team_history as eth3
                LEFT JOIN
                (
                    SELECT MAX(eth2.id) as idMax, eth2.employee_id FROM employee_team_history as eth2
                    LEFT JOIN teams ON eth2.team_id = teams.id
                    INNER JOIN $tblJapan AS japan ON eth2.id <= japan.MAXID
                    WHERE (teams.code NOT LIKE 'japan%' OR (teams.parent_id NOT IN (SELECT id FROM teams WHERE teams.code = 'japan') AND teams.code IS NULL))
                    AND eth2.employee_id = japan.employee_id
                    AND eth2.deleted_at IS NULL
                    GROUP BY eth2.employee_id
                ) AS nojapan ON eth3.employee_id = nojapan.employee_id
                INNER JOIN $tblJapan AS japan2 ON japan2.employee_id = eth3.employee_id
                WHERE (eth3.id > nojapan.idMax OR nojapan.employee_id IS NULL)
                    AND eth3.deleted_at IS NULL
                GROUP BY eth3.employee_id
            )";

        if (!isset($condition['more'])) {
            $builder->whereRaw(DB::raw("$sql"))
            ->where(DB::raw("DATE_ADD(date(employee_team_history.start_at), INTERVAL " . $value . " MONTH)"), "=", Carbon::now()->toDateString());
        } else {
            $builder->whereRaw(DB::raw("$sql "
            . "AND abs(MONTH(employee_team_history.start_at) - MONTH(CURDATE())) = 6 "
            . "AND ((date(employee_team_history.start_at) <= '" . Carbon::now()->subMonths($value)->toDateString() . "' "
                . "AND DAY({$empTeamHistoryTable}.start_at) = DAY(CURDATE())) "
            . "OR (DATE_ADD(date(employee_team_history.start_at), INTERVAL " . $value . " MONTH) = '" . Carbon::now()->toDateString() . "') "
                . "AND DAY({$empTeamHistoryTable}.start_at) >= DAY(CURDATE())) "));
        }
        if (count($employeeIds)) {
            $builder->whereNotIn("{$empTeamHistoryTable}.employee_id", $employeeIds);
        }
        return $builder->withTrashed();
    }

    /**
     * Get records insert into table leave_day_histories
     *
     * @param LeaveDay collection $list
     * @param array $fieldsSave     fields change
     * @param int $dayAdd   default value: 1
     * @param boolean $isJp6Months
     *
     * @return array
     */
    public static function getDataInsertHistories($list, $fieldsSave, $dayAdd = 1, $isJp = false, $isJp6Months = false, $months = false)
    {
        $dataInsert = [];
        foreach ($list as $item) {
            if ($isJp && Carbon::parse($item->start_at)->format('Y-m-d') < '2019-07-01') {
                if ($months && $months == 6) {
                    $isJp6Months = true;
                    $dayAdd = 10;
                } elseif ($months && $months <= 7) {
                    continue;
                } else {
                    //do not some thing
                }
            }
            foreach ($fieldsSave as $field) {
                if ($isJp) {
                    if ($isJp6Months) {
                        $new = $dayAdd;
                    } else {
                        //if old day_current_year + day added > 20 then set 20
                        //else set day_current_year + day added
                        $new = $item->$field + $dayAdd > self::MAX_LEAVE_DAY_JAPAN ? self::MAX_LEAVE_DAY_JAPAN : $item->$field + $dayAdd;
                    }
                } else {
                    $new = $item->$field + $dayAdd;
                }

                $fields[$field] = [
                    'old' => $item->$field,
                    'new' => $new,
                ];
            }
            $change = LeaveDayPermission::getFieldsChanged($item->id, $fields);
            if ($change) {
                $leaveDayHistory = new LeaveDayHistories();
                $dataInsert[] = [
                    'id' => $leaveDayHistory->id,
                    'employee_id' => $item->id,
                    'content' => json_encode($change),
                    'type' => LeaveDayHistories::TYPE_AUTO,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        }
        return $dataInsert;
    }

    /**
     * insert lealve day of employees when employee not japan and
     * employee not exist table leave_day_histories
     * @param  [builder] $builder
     * @param  [array] $empExist [id employee]
     * @param  [int] $numberAdd
     */
    public static function insertNotExistNotJaPan($builder, $empExist, $numberAdd)
    {
        $empTeamHistoryTable = EmployeeTeamHistory::getTableName();
        $leaveDayTable = self::getTableName();

        $builder = $builder->rightJoin("{$empTeamHistoryTable}", "{$empTeamHistoryTable}.employee_id", "=", "{$leaveDayTable}.employee_id");
        static::insertNotExist($builder, $empExist, $numberAdd);
    }

    /**
     * insert lealve day of employees when employee not exist table leave_day_histories
     * @param  [builder] $builder
     * @param  [array] $empExist
     * @param  [int] $numberAdd
     */
    public static function insertNotExist($builder, $empExist, $numberAdd)
    {
        $empTeamHistoryTable = EmployeeTeamHistory::getTableName();
        $empIds = $builder->select("{$empTeamHistoryTable}.employee_id as id")->lists('id')->toArray();

        $empsNotExist = [];
        foreach ($empIds as $empId) {
            if (!in_array($empId, $empExist) && $empId) {
                $empsNotExist[] = $empId;
            }
        }
        if (count($empsNotExist)) {
            $dataInsert = [];
            foreach ($empsNotExist as $empIdNot) {
                $dataInsert[] = [
                    'employee_id' => $empIdNot,
                    'day_current_year' => $numberAdd,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
            LeaveDay::insert($dataInsert);
        }
    }

    /**
     * Run daily to add leave day on day_seniority with employee than 3 years
     */
    public static function cronJobUpdateLeaveDaySeniority()
    {
        DB::beginTransaction();
        try {
            $dateCurrent = Carbon::now();
            $leaveDayTable = self::getTableName();
            $employeeTable = Employee::getTableName();
    
            $dayCurrent = $dateCurrent->day;
            $monthCurrent = $dateCurrent->month;
            if (!($dayCurrent == 1 && $monthCurrent == 1)) {
                $empModel = new Employee();
                $empIdsInJapan = $empModel->getAllEmpIdInJapan();
                $now = Carbon::now();
                $builder = self::join("{$employeeTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
                    ->where(function ($query) use ($employeeTable, $now) {
                        $query->whereNull("{$employeeTable}.leave_date")
                            ->orWhereDate("{$employeeTable}.leave_date", '>=', $now->format('Y-m-d'));
                    })
                    ->whereNull("{$employeeTable}.deleted_at")
                    ->whereNotIn("{$employeeTable}.id", $empIdsInJapan)
                    //Find employees with seniority mod 3 = 0 and offcial_date <> today
                    ->whereRaw(DB::raw("DAY(offcial_date) = DAY(CURDATE()) AND MONTH(offcial_date) = MONTH(CURDATE()) AND YEAR(CURDATE()) > YEAR(offcial_date) AND MOD(YEAR(CURDATE()) - YEAR(offcial_date), ".self::SENIORITY_YEAR.") = 0"));

                //Save histories
                $leaveDayInfo = $builder->select('leave_days.day_seniority', 'employees.id')->get();
                $dataInsertHistories = static::getDataInsertHistories($leaveDayInfo, ['day_seniority']);
                if (count($dataInsertHistories)) {
                    LeaveDayHistories::insert($dataInsertHistories);
                }

                // Update leave day
                $builder->update([
                    "{$leaveDayTable}.day_seniority" => DB::raw("{$leaveDayTable}.day_seniority + 1"),
                    "{$leaveDayTable}.updated_at" => Carbon::now(),
                ]);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }

    /**
     * Run daily to add leave day on day_current_year when employee tranfer trial to offcial
     */
    public static function cronJobUpdateLeaveDayTrialToOffcial($employeeIdChange = null)
    {
        DB::beginTransaction();
        try {
            $dateCurrent = Carbon::now();
            $tblEmployee = Employee::getTableName();
            $empModel = new Employee();
            $empIdsInJapan = $empModel->getAllEmpIdInJapan();
            $employees = Employee::select("{$tblEmployee}.id", "{$tblEmployee}.offcial_date", "{$tblEmployee}.trial_date", "{$tblEmployee}.trial_end_date")
                ->where(function ($query) use ($dateCurrent) {
                    $query->whereNull("leave_date")
                        ->orWhereDate("leave_date", '>=', $dateCurrent->format('Y-m-d'));
                })
                ->whereDate("offcial_date", "=", $dateCurrent->toDateString())
                ->whereNotIn("{$tblEmployee}.id", $empIdsInJapan);
            if ($employeeIdChange && is_numeric($employeeIdChange)) {
                $employees->where('id', '=', $employeeIdChange);
            }
            $employees = $employees->get();
            if (count($employees)) {
                foreach ($employees as $item) {
                    // check employee uses to auto add leave day?
                    if (LeaveDayHistories::select(['employee_id'])
                        ->where('employee_id', '=', $item->id)
                        ->where('type', '=', LeaveDayHistories::TYPE_AUTO)
                        ->whereDate('created_at', '=', $dateCurrent->format('Y-m-d'))
                        ->first()
                    ) {
                        continue;
                    }
                    $trialStartDate = null;
                    if (!empty($item ->trial_date)) {
                        $trialStartDate = Carbon::createFromFormat('Y-m-d', $item ->trial_date);
                    }
                    $officialDate = Carbon::createFromFormat('Y-m-d', $item ->offcial_date);
                    $leaveDayCurrent = self::calculateLeaveDayTrialToOffcial($trialStartDate, $officialDate);

                    $leaveDay = self::where('employee_id', $item->id)->withTrashed()->first();
                    $now = Carbon::now();
                    if (!$leaveDay) {
                        $leaveDay = new self();
                        $leaveDay->employee_id = $item->id;
                        $leaveDay->created_at = $now;
                    }
                    if (!$leaveDay->day_current_year) {
                        $leaveDay->day_current_year = 0;
                    }
                    $leaveDayCurrent += $leaveDay->day_current_year;
                    //Save histories
                    $newData = ['day_current_year' => $leaveDayCurrent];
                    $leaveDayPermis = new LeaveDayPermission();
                    $changes = $leaveDayPermis->findChanges($leaveDay, $newData);
                    if (count($changes)) {
                        $leaveDayPermis->saveHistory($leaveDay->employee_id, $changes, LeaveDayHistories::TYPE_AUTO);
                    }

                    //Update leave day current year
                    $leaveDay->day_current_year = $leaveDayCurrent;
                    $leaveDay->updated_at = $now;
                    $leaveDay->deleted_at = null;
                    $leaveDay->save();
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }


    /**
     * Calculate month trail date and add leave (official) when employee official
     * @param $trialDate
     * @param $officialDate
     * @return int
     */
    public static function calculateLeaveDayTrialToOffcial($trialDate, $officialDate)
    {
        if (!$trialDate) {
            if ($officialDate->day <= self::DATE_CALCULATE_SOCIAL_INSURANCE) {
                return 1;
            } else {
                return 0;
            }
        }
        $day = $trialDate->format('Y-m') . '-' . $officialDate->day;
        $trialStartDate = Carbon::createFromFormat('Y-m-d', $day);
        $leaveDayCurrent = $officialDate->diffInMonths($trialStartDate);
        if ($trialDate->day <= self::DATE_CALCULATE_SOCIAL_INSURANCE) {
            $leaveDayCurrent += 1;
        }

        if ($leaveDayCurrent > self::MAX_LEAVE_DAY_CURRENT_YEAR) {
            return self::MAX_LEAVE_DAY_CURRENT_YEAR;
        }

        return $leaveDayCurrent;
    }

    /**
     * udpate leave day of emplyee japan
     * @param  [builder] $builder
     * @param  [int] $dayAdd
     * @param  [int] $month
     * @return [builder]
     */
    public static function updateLeaveDayJapan($builder, $dayAdd, $month)
    {
        $leaveDayTable = self::getTableName();
        $maxLeaveDay = self::MAX_LEAVE_DAY_JAPAN;

        if ($month <= 7) {
            $builder->update([
                "{$leaveDayTable}.day_current_year" =>  DB::raw(
                    "CASE
                        WHEN
                            {$leaveDayTable}.day_current_year
                            THEN {$leaveDayTable}.day_current_year + {$dayAdd}
                            ELSE {$dayAdd}
                    END"
                ),
                "{$leaveDayTable}.updated_at" => Carbon::now(),
            ]);
        } else {
            $addSeniority = 0;
            //if old day_current_year + day added > 20 then set 20
            //else set day_current_year + day added
            $temporary = "{$leaveDayTable}.day_last_transfer +
                            {$leaveDayTable}.day_current_year +
                            {$leaveDayTable}.day_seniority +
                            {$leaveDayTable}.day_ot -
                            {$leaveDayTable}.day_used";
            if ($dayAdd > 10) {
                $addSeniority = $dayAdd - 10;
                $dayAdd = 10;
            }
            $builder->update([
                "{$leaveDayTable}.day_last_year" => $temporary,
                "{$leaveDayTable}.day_last_transfer" => DB::raw(
                    "CASE
                        WHEN
                            {$temporary} + $dayAdd > {$maxLeaveDay}
                            THEN {$maxLeaveDay} - {$dayAdd}
                            ELSE {$temporary}
                    END"
                ),
                "{$leaveDayTable}.day_current_year" => $dayAdd,
                "{$leaveDayTable}.day_seniority" => $addSeniority,
                "{$leaveDayTable}.day_ot" => 0,
                "{$leaveDayTable}.day_used" => 0,
                "{$leaveDayTable}.updated_at" => Carbon::now(),
            ]);
        }
        return $builder;
    }

    /**
     * get all register leave day in year, no team japan
     * @return [array]
     */
    public static function getLeaveDayReister()
    {
        $leaveDayTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $empModel = new Employee();
        $empIdsInJapan = $empModel->getAllEmpIdInJapan();
        $now = Carbon::now();
        $leaveDayRegiser = LeaveDayRegister::getTableName();
        $leaveReason = LeaveDayReason::getTableName();

        $leaveDayEmployeesYearNew = Employee::select(
            "{$leaveDayTable}.id",
            "{$employeeTable}.id as employee_id",
            "leaveRegis.id as register_id",
            "leaveRegis.date_start",
            "leaveRegis.date_end",
            "leaveRegis.number_days_off",
            "leaveRegis.status"
        )
        ->leftJoin("{$leaveDayTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
        ->leftJoin("{$leaveDayRegiser} as leaveRegis", "{$employeeTable}.id", "=", "leaveRegis.creator_id")
        ->leftJoin("{$leaveReason} as leaveReason", "leaveReason.id", "=", "leaveRegis.reason_id")
        ->where(function ($query) use ($employeeTable, $now) {
            $query->whereNull("{$employeeTable}.leave_date")
                ->orWhereDate("{$employeeTable}.leave_date", '>=', $now->format('Y-m-d'));
        })
        ->whereNotNull("join_date")
        ->where(function ($query) {
            $query->whereNotNull("trial_date")
                  ->orWhereNotNull("offcial_date");
        })
        ->whereNotIn("{$employeeTable}.id", $empIdsInJapan) //japan not add leave day 01-01
        ->whereYear("leaveRegis.date_end", "=", $now->year)
        ->where("leaveRegis.status", "=", LeaveDayRegister::STATUS_APPROVED)
        ->where("leaveReason.used_leave_day", "!=", 0)
        ->whereNull("leaveRegis.deleted_at")
        ->groupBy("leaveRegis.id")
        ->get();

        $date = '01-01-' . $now->year;
        $leaveDayRegister = [];
        foreach ($leaveDayEmployeesYearNew as $key => $emp) {
            if (strtotime($emp->date_start) == strtotime($emp->date_end)) {
                continue;
            }
            if (strtotime($emp->date_start) < strtotime($date)) {
                $workTimeStart = Employee::getTimeWorkEmployeeDate($now->year . '-01-01', $emp);
                $emp->date_start = $workTimeStart['morningInSetting']->toDateTimeString();
            }
            $time = ManageLeaveDay::getTimeLeaveDay($emp->date_start, $emp->date_end, Employee::getEmpById($emp->employee_id));
            $leaveDayRegister[$emp->employee_id] = $time;
        }
        return  $leaveDayRegister;
    }


    /**
     * Cộng phép cho nhân viên japan khi vừa vào
     * @param  [Carbon] $cbDate
     * @return [type]
     */
    public function getEmpJoinJanPanMin($cbDate)
    {
        $empTeamHistory = EmployeeTeamHistory::getTableName();
        $employeeTable = Employee::getTableName();
        $lealveDay = LeaveDay::getTableName();

        return EmployeeTeamHistory::selectRaw("
                min(employee_team_history.id) as minID,
                {$empTeamHistory}.employee_id,
                {$lealveDay}.day_current_year
            ")
            ->leftJoin("{$employeeTable}", "{$employeeTable}.id", "=", "{$empTeamHistory}.employee_id")
            ->leftJoin("{$lealveDay}", "{$lealveDay}.employee_id", "=", "{$empTeamHistory}.employee_id")
            ->leftJoin('teams', 'teams.id', '=', "{$empTeamHistory}.team_id")
            ->where(function ($query) {
                $query->where("teams.code", 'LIKE', 'japan%')
                    ->orWhereRaw(DB::raw("teams.parent_id IN (SELECT id FROM teams WHERE teams.code = 'japan')"))
                    ->groupBy('teams.id');
            })
            ->where(function ($query) use ($employeeTable, $cbDate) {
                $query->whereNull("{$employeeTable}.leave_date")
                    ->orWhereDate("{$employeeTable}.leave_date", '>=', $cbDate->format('Y-m-d'));
            })
            ->whereDate("{$empTeamHistory}.created_at", '=', $cbDate->format('Y-m-d'))
            ->where(function ($query) use ($empTeamHistory, $cbDate) {
                $query->whereNull("{$empTeamHistory}.end_at")
                    ->orWhereDate("{$empTeamHistory}.end_at", '>', $cbDate->format('Y-m-d'));
            })
            ->groupBy("{$empTeamHistory}.employee_id")
            ->get();
    }

    /**
     * [updateInsertLeaveDay description]
     * @param [collection] $empJoinJaPanNew
     * @param [array] $empExist
     * @param [int] $dayAddedNew
     */
    public function updateInsertLeaveDay($empJoinJaPanNew, $empExist, $numberAdd)
    {
        $arrInsert = [];
        $dataInsertHistories = [];
        $now = Carbon::now();
        try {
            foreach ($empJoinJaPanNew as $item) {
                if (in_array($item->employee_id, $empExist)) {
                    $dataUpdate = [
                        'employee_id' => $item->employee_id,
                        'day_current_year' => $item->day_current_year + $numberAdd,
                        'updated_at' => $now,
                    ];
                    LeaveDay::where('employee_id', $item->employee_id)->update($dataUpdate);
                    $fields['day_current_year'] = [
                        'old' => $item->day_current_year,
                        'new' => $dataUpdate['day_current_year'],
                    ];
                } else {
                    $arrInsert[] = [
                        'employee_id' => $item->employee_id,
                        'day_current_year' => $numberAdd,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $fields['day_current_year'] = [
                        'old' => 0,
                        'new' => $numberAdd,
                    ];
                }
                $change = LeaveDayPermission::getFieldsChanged($item->employee_id, $fields);
                if ($change) {
                    $leaveDayHistory = new LeaveDayHistories();
                    $dataInsertHistories[] = [
                        'id' => $leaveDayHistory->id,
                        'employee_id' => $item->employee_id,
                        'content' => json_encode($change),
                        'type' => LeaveDayHistories::TYPE_AUTO,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
            if (count($arrInsert)) {
                LeaveDay::insert($arrInsert);
            }

            //Save histories
            if (count($dataInsertHistories)) {
                LeaveDayHistories::insert($dataInsertHistories);
            }
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }
 
    /**
     * getGridDataWithProject
     *
     * @param  date $month (Y-m|null)
     * @param  string $url
     * @param  array $empIds
     * @return collection
     */
    public function getGridDataWithProject($month, $url, $empIds = [])
    {
        $leaveDayTable = self::getTableName();
        $employeeTable = Employee::getTableName();
        $model = self::class;
        if ($month) {
            $model = LeaveDayBaseline::class;
            $leaveDayTable = LeaveDayBaseline::getTableName();
        }
        $now = Carbon::now();
        $getTotalDay = DB::raw($leaveDayTable . '.day_last_transfer + ' . $leaveDayTable . '.day_current_year +' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot');
        $getRemainDay = DB::raw($leaveDayTable. '.day_last_transfer + ' . $leaveDayTable .'.day_current_year + ' . $leaveDayTable . '.day_seniority + ' . $leaveDayTable . '.day_ot - ' . $leaveDayTable . '.day_used');

        $collection = $model::select(
            "{$leaveDayTable}.id",
            "{$leaveDayTable}.employee_id",
            "{$leaveDayTable}.day_last_year",
            "{$leaveDayTable}.day_last_transfer",
            "{$leaveDayTable}.day_current_year",
            "{$leaveDayTable}.day_seniority",
            "{$leaveDayTable}.day_ot",
            "{$leaveDayTable}.day_used",
            "{$leaveDayTable}.created_at",
            "{$leaveDayTable}.updated_at",
            "{$leaveDayTable}.note",
            "{$employeeTable}.employee_code",
            "{$employeeTable}.name",
        	DB::raw("{$getTotalDay} as total_day"),
        	DB::raw("{$getRemainDay} as remain_day")
        )
        ->join("{$employeeTable}", "{$employeeTable}.id", "=", "{$leaveDayTable}.employee_id")
        ->whereNotIn("{$employeeTable}.account_status", [getOptions::PREPARING, getOptions::FAIL_CDD])
        ->where(function ($query) use ($employeeTable, $now) {
            $query->whereNull("{$employeeTable}.leave_date")
                ->orWhereDate("{$employeeTable}.leave_date", '>=', $now->format('Y-m-d'));
        })
        ->whereNull("{$employeeTable}.deleted_at");

        //filter month
        if ($month) {
            $collection->where($leaveDayTable . '.month', $month);
        }

        $totalDayFilter = Form::getFilterData('spec', 'total_day', $url);
        if (isset($totalDayFilter)) {
            $collection->where($getTotalDay, '=', $totalDayFilter);
        }
        $remainDayFilter = Form::getFilterData('spec','remain_day', $url);
        if (isset($remainDayFilter)) {
            $collection->where($getRemainDay, '=', $remainDayFilter);
        }
        
        if ($empIds) {
            $collection->whereIn("{$employeeTable}.id", $empIds);
        }

        $pager = Config::getPagerData();
        $collection->groupBy('employees.id');
        if (Form::getFilterPagerData('order', $url)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }

        $tblFilter = View::getFilterLeaveDayTable();
        if ($tblFilter && $tblFilter !== $leaveDayTable) {
            $filter = Form::getFilterData(null, null, $url);
            if (!empty($filter)) {
                if (isset($filter['number']) && is_array($filter['number'])) {
                    $filterNumber = [];
                    foreach ($filter['number'] as $col => $filterValue) {
                        $colChange = preg_replace("/{$tblFilter}/", $leaveDayTable, $col);
                        $filterNumber[$colChange] = $filterValue;
                        $collection = $collection->where($colChange, $filterValue);
                    }
                    $filter['number'] = $filterNumber;
                }
                if (isset($filter["{$tblFilter}.note"])) {
                    $filterValue = $filter["{$tblFilter}.note"];
                    $collection = $collection->where("{$leaveDayTable}.note", 'LIKE', "%{$filterValue}%");
                    unset($filter["{$tblFilter}.note"]);
                    $filter["{$leaveDayTable}.note"] = $filterValue;
                }
                CookieCore::setRaw('filter.' . $url, $filter);
            }
        } else {
            static::filterGrid($collection, [], $url, 'LIKE');
        }
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * get employees in Japan for N years and paid leave less than 5 day
     * @return array
     */
    public static function getEmpsNYearsInJapanAndPaidLeave()
    {
        $tblLeaveDayRegister = LeaveDayRegister::getTableName();
        $tblLeaveDayReason = LeaveDayReason::getTableName();
        $empsNYearsInJapan = Employee::getEmpsNYearsInJapan();
        $empIdsNYearsInJapan = [];
        $arrTotalLeaveDayInYear = [];
        $minStartAt = null;
        $maxEndAt = null;
        foreach ($empsNYearsInJapan as $empId => $emp) {
            $empIdsNYearsInJapan[] = $empId;
            $arrTotalLeaveDayInYear[$empId] = 0.0;
            $minStartAt = $minStartAt < $emp['start_at_N_year'] && $minStartAt !== null ? $minStartAt : $emp['start_at_N_year'];
            $maxEndAt = max($maxEndAt, $emp['end_at_N_year']);
        }

        $leaveDayRegisterList = [];
        if (count($empIdsNYearsInJapan)) {
            $selectedFields = [
                $tblLeaveDayRegister.'.creator_id',
                $tblLeaveDayRegister.'.date_start',
                $tblLeaveDayRegister.'.date_end',
                $tblLeaveDayRegister.'.number_days_off',
            ];
            $leaveDayRegisterList = LeaveDayRegister::select($selectedFields)
                ->join($tblLeaveDayReason, function ($query) use ($tblLeaveDayReason, $tblLeaveDayRegister) {
                    $query->on($tblLeaveDayReason.'.id', '=', $tblLeaveDayRegister.'.reason_id')
                        ->where($tblLeaveDayReason.'.used_leave_day', '=', 1)
                        ->whereNull($tblLeaveDayReason.'.deleted_at');
                })->whereIn($tblLeaveDayRegister.'.creator_id', $empIdsNYearsInJapan)
                ->where($tblLeaveDayRegister.'.status', LeaveDayRegister::STATUS_APPROVED)
                ->whereDate($tblLeaveDayRegister.'.date_end', '>=', $minStartAt)
                ->whereDate($tblLeaveDayRegister.'.date_start', '<', $maxEndAt)
                ->get();
        }
        foreach ($leaveDayRegisterList as $item) {
            if ($arrTotalLeaveDayInYear[$item->creator_id] >= LeaveDay::LEAVE_DAY_JAPAN_NOTICE) {
                continue;
            }
            $empId = $item->creator_id;
            /**
             * ngày bắt đầu nghỉ phép < ngày bắt đầu của năm làm việc tại Nhật
             * ngày kết thúc nghỉ phép >= ngày bắt đầu của năm làm việc tại Nhật
             * => lấy số ngày nghỉ phép từ ngày bắt đầu của năm làm việc tại Nhật đến ngày kết thúc nghỉ phép
             * nếu chỉ nghỉ buổi sáng của ngày kết thúc nghỉ phép => trừ buổi chiều (0.5 ngày)
             */
            if ($item->date_start < $empsNYearsInJapan[$empId]['start_at_N_year']
                && $item->date_end >= $empsNYearsInJapan[$empId]['start_at_N_year']
            ) {
                $dateEnd = Carbon::parse($item->date_end);
                $startAtNYears = Carbon::parse($empsNYearsInJapan[$empId]['start_at_N_year']);
                $arrTotalLeaveDayInYear[$empId] += $dateEnd->diffInWeekdays($startAtNYears);
                if ($dateEnd->format('H:i') < '13:00') {
                    $arrTotalLeaveDayInYear[$empId] -= 0.5;
                }
            }
            /**
             * ngày bắt đầu nghỉ phép >= ngày bắt đầu của năm làm việc tại Nhật
             * ngày kết thúc nghỉ phép < ngày kết thúc của năm làm việc tại Nhật
             * => lấy số ngày nghỉ phép từ 'number_days_off' trong DB
             */
            if ($item->date_start >= $empsNYearsInJapan[$empId]['start_at_N_year']
                && $item->date_end < $empsNYearsInJapan[$empId]['end_at_N_year']
            ) {
                $arrTotalLeaveDayInYear[$empId] += (float)$item->number_days_off;
            }
            /**
             * ngày bắt đầu nghỉ phép < ngày kết thúc của năm làm việc tại Nhật
             * ngày kết thúc nghỉ phép >= ngày kết thúc của năm làm việc tại Nhật
             * => lấy số ngày nghỉ phép từ ngày bắt đầu nghỉ phép đến ngày kết thúc của năm làm việc tại Nhật
             * nếu chỉ xin nghỉ từ buổi chiều của ngày bắt đầu nghỉ phép => trừ sáng (0.5 ngày)
             */
            if ($item->date_start < $empsNYearsInJapan[$empId]['end_at_N_year']
                && $item->date_end >= $empsNYearsInJapan[$empId]['end_at_N_year']
            ) {
                $dateStart = Carbon::parse($item->date_start);
                $endAtNYears = Carbon::parse($empsNYearsInJapan[$empId]['end_at_N_year']);
                $arrTotalLeaveDayInYear[$empId] += $endAtNYears->subDay(-1)->diffInWeekdays($dateStart);
                // paid leave half day
                if ($dateStart->format('H:i') >= '13:00') {
                    $arrTotalLeaveDayInYear[$empId] -= 0.5;
                }
            }
        }
        $empsNYearsInJapanAndLeaveDay = [];
        // Loại bỏ nhân viên có số ngày nghỉ phép >= 5
        foreach ($arrTotalLeaveDayInYear as $key => $value) {
            if ($value < LeaveDay::LEAVE_DAY_JAPAN_NOTICE) {
                $empsNYearsInJapanAndLeaveDay[$key] = $empsNYearsInJapan[$key];
            }
        }
        return [
            'emps_japan_leave_day' => $empsNYearsInJapanAndLeaveDay,
            'total_leave_day' => $arrTotalLeaveDayInYear,
        ];
    }

    /**
     * Run daily to notice of Japan leave if do not take 5 full leave days in a year
     */
    public static function cronJobNoticeJapanLeave()
    {
        $empsNYearsInJapanAndLeaveDay = self::getEmpsNYearsInJapanAndPaidLeave();
        $arrTotalLeaveDayInYear = $empsNYearsInJapanAndLeaveDay['total_leave_day'];
        $empsNYearsInJapanAndLeaveDay = $empsNYearsInJapanAndLeaveDay['emps_japan_leave_day'];
        $empIdsNYearsInJapanAndLeaveDay = [];
        foreach ($empsNYearsInJapanAndLeaveDay as $key => $emps) {
            $empIdsNYearsInJapanAndLeaveDay[] = $key;
        }
        $employees = Employee::select('id', 'email', 'name', 'leave_date')
            ->whereIn('id', $empIdsNYearsInJapanAndLeaveDay)
            ->get();
        $template = 'manage_time::template.leave.mail_notify.mail_notice_of_japan_leave';
        $subject = trans('[Intranet] Nhắc nhở nghỉ đủ số phép tối thiểu - 有給休暇取得日数についてのお知らせ.');
        $dataInsert = [];
        foreach ($employees as $emp) {
            if ($emp->leave_date === null || $emp->leave_date >= $empsNYearsInJapanAndLeaveDay[$emp->id]['end_at_N_year']) {
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($emp->email, $emp->name)
                    ->setSubject($subject)
                    ->setTemplate($template, [
                        'name' => $emp->name,
                        'dateFrom' => $empsNYearsInJapanAndLeaveDay[$emp->id]['start_at_N_year'],
                        'dateTo1Year' => $empsNYearsInJapanAndLeaveDay[$emp->id]['end_at_N_year'],
                        'totalLeaveDay' => $arrTotalLeaveDayInYear[$emp->id],
                        'leaveDayMin' => LeaveDay::LEAVE_DAY_JAPAN_NOTICE,
                    ]);
                $emailQueue->addCc('admin-jp@rikkeisoft.com');
                $dataInsert[] = $emailQueue->getValue();
            }
        }
        if (!empty($dataInsert)) {
            EmailQueue::insert($dataInsert);
        }
    }

    
    /**
     * get grant date for employee japan
     * @param $joinDate join date to company
     * @return Array lastGrantDate nextGrantDate
     */
    public static function getGrantDateEmployeeJp($joinDate, $compareDate = null) 
    {
        $lastGrantDate = '';
        $nextGrantDate = '';
        $now = Carbon::now();
        if ($compareDate) {
            $now = Carbon::parse($compareDate);
        }
        $startAt = Carbon::parse($joinDate);
        $nextAt = clone $startAt;
        $workingMonths = $now->diffInMonths($startAt);
        $workingYears = intval($workingMonths / 12);
 
        if ($workingMonths >= LeaveDay::WORKING_MONTH_0_MONTH && $workingMonths < LeaveDay::WORKING_MONTH_2_MONTH) { // Ngày đầu tiên vào công ty hoặc dưới 2 tháng
            $lastGrantDate = Carbon::parse($joinDate)->format('Y-m-d');
            $nextGrantDate = $startAt->addMonthsNoOverflow(LeaveDay::WORKING_MONTH_2_MONTH)->addDays(-1)->format('Y-m-d');
        } else if ($workingMonths >= LeaveDay::WORKING_MONTH_2_MONTH && $workingMonths < LeaveDay::WORKING_MONTH_4_MONTH) { // Từ 2 đến 4 tháng
            $lastGrantDate = $startAt->addMonthsNoOverflow(LeaveDay::WORKING_MONTH_2_MONTH)->format('Y-m-d');
            $nextGrantDate = $nextAt->addMonthsNoOverflow(LeaveDay::WORKING_MONTH_4_MONTH)->addDays(-1)->format('Y-m-d');
        } else if ($workingMonths >= LeaveDay::WORKING_MONTH_4_MONTH && $workingMonths < LeaveDay::WORKING_MONTH_6_MONTH) { // Từ 4 đến 6 tháng
            $lastGrantDate = $startAt->addMonthsNoOverflow(LeaveDay::WORKING_MONTH_4_MONTH)->format('Y-m-d');
            $nextGrantDate = $nextAt->addMonthsNoOverflow(LeaveDay::WORKING_MONTH_6_MONTH)->addDays(-1)->format('Y-m-d');
        } else if ($workingMonths >= LeaveDay::WORKING_MONTH_6_MONTH && $workingMonths < LeaveDay::WORKING_MONTH_18_MONTH) { // // Từ 6 đến 18 tháng
            $lastGrantDate = $startAt->addMonthsNoOverflow(LeaveDay::WORKING_MONTH_6_MONTH)->format('Y-m-d');
            $nextGrantDate = $nextAt->addMonthsNoOverflow(LeaveDay::WORKING_MONTH_18_MONTH)->addDays(-1)->format('Y-m-d');
        } else { // trên 1 năm 6 tháng
            $lastGrantDate = $startAt->addYear($workingYears)->addMonthsNoOverflow(6)->format('Y-m-d');
            if ($lastGrantDate > $now) {
                $startAt = clone $nextAt;
                $lastGrantDate = $startAt->addYear($workingYears - 1)->addMonthsNoOverflow(6)->format('Y-m-d');
                $nextGrantDate = $nextAt->addYear($workingYears)->addMonthsNoOverflow(6)->addDays(-1)->format('Y-m-d');
            } else {
                $nextGrantDate = $nextAt->addYear($workingYears + 1)->addMonthsNoOverflow(6)->addDays(-1)->format('Y-m-d');
            }
        }

        return [$lastGrantDate, $nextGrantDate];
    }

    /**
     * Get first data join japan and continues
     * @param $EmpId
     * @return
     *  $flgJapan : true ở nhật liên tục, false chuyển đổi vn-japn
     *  $firstTeamStartAt : Ngày vào team japan và ở liên tục
     */
    public static function getJoinFistTeamInJapan($EmpId)
    {
        $tblTeam = Team::getTableName();
        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();
        $flgJapan = true; // Nhân viên liên tục ở Japan
        $firstTeamStartAt = ''; // Ngày vào team japan, đầu tiên và liên tục.

        // Lấy toàn bộ lịch sử tham gia các bộ phận của nhân viên.
        $employeeTeamHistory = EmployeeTeamHistory::select($tblEmpTeamHistory . '.start_at', $tblEmpTeamHistory . '.end_at', $tblTeam . '.branch_code')
            ->join($tblTeam, $tblEmpTeamHistory . '.team_id', '=', $tblTeam . '.id')
            ->whereNull($tblEmpTeamHistory . '.deleted_at')
            ->where($tblEmpTeamHistory . '.employee_id', $EmpId)
            ->orderBy($tblEmpTeamHistory . '.start_at', 'asc')
            ->get();

        foreach ($employeeTeamHistory as $teamId => $team) {
            // Nếu không phải japan thì reset giá trị.
            if ($team['branch_code'] != Team::CODE_PREFIX_JP){
                $flgJapan = false;
                $firstTeamStartAt = ''; // reset value
            }
            // Nếu chưa có giá trị mà thuộc japan thì lấy ngày vào japan để tính phép.
            elseif($firstTeamStartAt == ''){
                $firstTeamStartAt = $team['start_at'];
            }
        }
        return [ $flgJapan, $firstTeamStartAt ];
    }

    /*
     * get grant date for employee japan by nendo
     * @param $joinDate join date to company
     * @return Array lastGrantDate nextGrantDate
     */
    public static function getGrantDateEmployeeJpByNendo($joinDate, $compareDate = null) 
    {
        $lastGrantDate = '';
        $nextGrantDate = '';
        $now = Carbon::now();
        if ($compareDate) {
            $now = Carbon::parse($compareDate);
        }
        $startAt = Carbon::parse($joinDate);
        $nextAt = clone $startAt;
        $workingMonths = $now->diffInMonths($startAt);
        $workingYears = intval($workingMonths / 12);
        if ($workingMonths >= LeaveDay::WORKING_MONTH_0_MONTH && $workingMonths < LeaveDay::WORKING_MONTH_18_MONTH) { // Ngày đầu tiên vào công ty đến 1 năm 6 tháng
            $lastGrantDate = Carbon::parse($joinDate)->format('Y-m-d');
            $nextGrantDate = $nextAt->addMonthsNoOverflow(LeaveDay::WORKING_MONTH_18_MONTH)->addDays(-1)->format('Y-m-d');
        } else { // trên 1 năm 6 tháng
            $lastGrantDate = $startAt->addYear($workingYears)->addMonthsNoOverflow(6)->format('Y-m-d');
            if ($lastGrantDate > $now) {
                $startAt = clone $nextAt;
                $lastGrantDate = $startAt->addYear($workingYears - 1)->addMonthsNoOverflow(6)->format('Y-m-d');
                $nextGrantDate = $nextAt->addYear($workingYears)->addMonthsNoOverflow(6)->addDays(-1)->format('Y-m-d');
            } else {
                $nextGrantDate = $nextAt->addYear($workingYears + 1)->addMonthsNoOverflow(6)->addDays(-1)->format('Y-m-d');
            }
        }

        return [$lastGrantDate, $nextGrantDate];
    }

    /**
     * Lấy ra danh sách tất cả các nhân viên đang làm việc ở Nhật Bản theo thời gian tìm kiếm
     * @param startDate ngày bắt đầu
     * @param teamId team.id
     * @param arrEmployee employees.id
     * @return Array
     */
    public static function getAllEmployeeJapanWorkingByDateTime($startDate, $teamIds = null, $arrEmployee = null, $searchRequest)
    {
        // Thêm giờ phút giây để đảm bảo lấy ra đủ nhân viên tại thời điểm tìm kiếm
        $startDate = $startDate . ' 00:00:00';
        $tblEmployee = Employee::getTableName();
        $tblTeam = Team::getTableName();
        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();

        // get all employees has branch code is 'japan'
        $collection = Employee::select($tblEmployee . '.id', $tblEmployee . '.working_type', $tblEmployee . '.employee_code', $tblEmployee . '.join_date', $tblEmployee . '.email', $tblEmployee . '.name',
            $tblEmployee . '.leave_date', $tblEmpTeamHistory . '.start_at', $tblEmpTeamHistory . '.end_at', $tblTeam . '.name as team_name')
            ->join($tblEmpTeamHistory, $tblEmpTeamHistory . '.employee_id', '=', $tblEmployee . '.id')
            ->join($tblTeam, $tblEmpTeamHistory . '.team_id', '=', $tblTeam . '.id')
            ->where($tblTeam . '.branch_code', Team::CODE_PREFIX_JP)
            ->where(function ($query) use ($tblEmpTeamHistory, $tblEmployee, $startDate) {
                $query->Where(function ($q2) use ($tblEmpTeamHistory, $startDate) {
                    $q2->orWhereNull($tblEmpTeamHistory . '.end_at')
                        ->Where($tblEmpTeamHistory . '.start_at', '<=', $startDate)
                        ->whereNull($tblEmpTeamHistory . '.deleted_at');
                })
                ->orWhere(function ($q3) use ($tblEmpTeamHistory, $tblEmployee, $startDate) {
                    $q3->orwhereNotNull($tblEmployee . '.leave_date')
                        ->Where($tblEmpTeamHistory . '.start_at', '<=', $startDate)
                        ->whereNull($tblEmpTeamHistory . '.deleted_at');
                });
            });
        if ($teamIds) 
        {
            $teamId = explode(",", $teamIds);
            $collection->whereIn($tblTeam . '.id', $teamId);
        }
        if ($arrEmployee)
        {
            $collection->whereIn($tblEmployee . '.id', $arrEmployee);
        }
        if ($searchRequest['employee_code'])
        {
            $collection->where($tblEmployee . '.employee_code', 'like', '%' . $searchRequest['employee_code'] . '%');
        }

        if ($searchRequest['employee_name'])
        {
            $collection->where($tblEmployee . '.name', 'like', '%' . $searchRequest['employee_name'] . '%');
        }

        $collection = $collection->orderBy($tblEmployee . '.employee_code', 'asc')
            ->orderBy($tblEmployee . '.name', 'asc')
            ->withTrashed()
            ->groupby($tblEmployee . '.id')
            ->distinct()
            ->get();

        return $collection;
    }
}
