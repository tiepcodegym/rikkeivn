<?php

namespace Rikkei\ManageTime\Model;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View as CoreView;
use Rikkei\ManageTime\Model\WorkingTimeDetail;
use Rikkei\ManageTime\View\WorkingTime as WorkingTimeView;
use Rikkei\Notify\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;

class WorkingTimeRegister extends CoreModel
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id',
        'employee_id',
        'approver_id',
        'updated_by',
        'team_id',
        'from_date',
        'to_date',
        'key_working_time',
        'key_working_time_half',
        'proj_id',
        'status',
        'related_ids',
        'reason',
    ];

    const STATUS_UNAPPROVE = 1;
    const STATUS_APPROVE = 2;
    const STATUS_REJECT = 3;

    const STR_REGISTER = 'register';
    const STR_RELATED = 'related';
    const STR_APPROVE = 'approve';
    const STR_MANAGER = 'manage';

    /*
     * get employee that belongs to
     */
    public function employee()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'employee_id');
    }

    /*
     * get approver that belongs to
     */
    public function approver()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'approver_id');
    }

    /*
     * get team that belongs to
     */
    public function team()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Team', 'team_id');
    }

    /*
     *  Get the detail for the working time.
     */
    public function workingTimeDetails()
    {
        return $this->hasMany(WorkingTimeDetail::class, 'working_time_id')->orderBy('employee_id');
    }
    
    /*
     * get from date attribute
     */
    public function getFromDate()
    {
        return Carbon::parse($this->from_date)->format('d-m-Y');
    }

    /*
     * get to date attribute
     */
    public function getToDate()
    {
        return Carbon::parse($this->to_date)->format('d-m-Y');
    }

    /*
     * check item deleteable
     */
    public function isDelete()
    {
        $curEmp = Permission::getInstance()->getEmployee();
        return $this->status != static::STATUS_APPROVE && $this->employee_id == $curEmp->id;
    }

    /*
     * get related ids (array)
     */
    public function getRelatedIds()
    {
        if (!$this->related_ids) {
            return [];
        }
        return explode(',', $this->related_ids);
    }

    public function getWorkingTimeById($id)
    {
        return static::where('id', $id)->first();
    }

    public function insertOrUpdate($data, $wtRegister)
    {
        if (isset($wtRegister->id)) {
            $wtRegister->update($data);
        } else {
            $wtRegister = static::create($data);
        }
        return $wtRegister;
    }
    
    
    /*
     * list my register
     */
    public function listRegister($status = null, $type = 'register')
    {
        $pager = Config::getPagerData();
        $curEmp = Permission::getInstance()->getEmployee();
        $empId = $curEmp->id;
        $tbl = static::getTableName();
        $filter = Form::getFilterData();
        $collection = static::select(
            "{$tbl}.id",
            "{$tbl}.parent_id",
            "{$tbl}.employee_id",
            "{$tbl}.approver_id",
            "{$tbl}.updated_by",
            "{$tbl}.proj_id",
            "{$tbl}.from_date",
            "{$tbl}.to_date",
            "{$tbl}.key_working_time",
            "{$tbl}.key_working_time_half",
            "{$tbl}.status",
            "{$tbl}.reason",
            "{$tbl}.created_at"
        );
        switch ($type) {
            case static::STR_REGISTER:
                $collection->where("{$tbl}.employee_id", $curEmp->id);
                break;
            case static::STR_RELATED:
                $collection->where("{$tbl}.related_ids", 'like', '%' . $curEmp->id . '%');
                break;
            case static::STR_APPROVE:
                $collection->where("{$tbl}.approver_id", $curEmp->id);
                break;
            case static::STR_MANAGER:
                $route = WorkingTimeView::ROUTE_MANAGE;
                    $collection->leftJoin(Team::getTableName() . ' as team', "{$tbl}.team_id", '=', 'team.id')
                        ->addSelect(
                            DB::raw('team.name AS team_names')
                        );
                    //permission
                    if (Permission::getInstance()->isScopeCompany(null, $route)) {
                        //get all
                    } elseif (Permission::getInstance()->isScopeTeam(null, $route)) {
                        $teamIds = Permission::getInstance()->isScopeTeam(null, $route);
                        $collection->where(function ($query) use ($teamIds, $empId, $tbl) {
                            $query->whereIn("{$tbl}.team_id", $teamIds)
                                    ->orWhere("{$tbl}.approver_id", $empId)
                                    ->orWhere("{$tbl}.employee_id", $empId)
                                    ->orWhere("{$tbl}.related_ids", 'like', '%"'. $empId .'"%');
                        });
                    } elseif (Permission::getInstance()->isScopeSelf(null, $route)) {
                        $collection->where(function ($query) use ($empId, $tbl) {
                            $query->orWhere("{$tbl}.approver_id", $empId)
                                    ->orWhere("{$tbl}.employee_id", $empId)
                                    ->orWhere("{$tbl}.related_ids", 'like', '%"'. $empId .'"%');
                        });
                    } else {
                        CoreView::viewErrorPermission();
                    }
                    break;
            default:
                break;
        }
        if  ($status) {
            $collection->where("{$tbl}.status", $status);
        }
        if ($filter) {     
            $collection->leftJoin('working_time_details as wtDetail', 'wtDetail.working_time_id', '=', "{$tbl}.id");
            $collection->join('employees as employeeDtail', 'employeeDtail.id', '=', "wtDetail.employee_id");
        }
        // filter data
        $collection->with('workingTimeDetails');
        $collection->with('employee');
        $collection->with('approver');
        try {
            if (isset($filter['wtr.employee_name'])) {
                $collection->whereHas('employee', function ($query) use($filter) {
                    $query->where('employees.name', 'like', '%' . trim($filter['wtr.employee_name']) . '%');
                    $query->orWhere('employeeDtail.name', 'like', '%' . trim($filter['wtr.employee_name']) . '%');
                });
            }
            if (isset($filter['wtr.date_start'])) {
                $startDate = Carbon::parse($filter['wtr.date_start'])->format('Y-m-d');
                $collection->where(function($query) use ($tbl, $startDate) {
                    $query->whereDate("{$tbl}.to_date", '>=', $startDate)
                        ->orWhere("wtDetail.to_date", '>=', $startDate);
                });
            }
            if (isset($filter['wtr.date_end'])) {
                $endDate = Carbon::parse($filter['wtr.date_end'])->format('Y-m-d');
                $collection->where(function($query) use ($tbl, $endDate) {
                    $query->whereDate("{$tbl}.from_date", '<=', $endDate)
                        ->orWhere("wtDetail.from_date", '<=', $endDate);
                });
            }
            if (isset($filter['wtr.working_time'])) {
                $collection->where(function($query) use ($tbl, $filter) {
                    $query->where("{$tbl}.key_working_time", '=', trim($filter['wtr.working_time']))
                    ->orWhere("wtDetail.key_working_time", '=', trim($filter['wtr.working_time']));
                });
            }
            if (isset($filter['wtr.working_time_half'])) {
                $collection->where(function($query) use ($tbl, $filter) {
                    $query->where("{$tbl}.key_working_time_half", '=', trim($filter['wtr.working_time_half']))
                    ->orWhere("wtDetail.key_working_time_half", '=', trim($filter['wtr.working_time_half']));
                });
            }
            if (isset($filter['wtr.created_date'])) {
                $createDate = Carbon::parse($filter['wtr.created_date'])->format('Y-m-d');
                $collection->whereDate("{$tbl}.created_at", '=', $createDate);
            }
            if (isset($filter['wtr.status'])) {
                $collection->where("{$tbl}.status", '=', trim($filter['wtr.status']));
            }
            if (isset($filter['wtr.employee_name_approve'])) {
                $collection->whereHas('approver', function ($query) use($filter) {
                    $query->where('employees.name', 'like', '%' . trim($filter['wtr.employee_name_approve']) . '%');
                });
            }
        } catch (Exception $ex) {
            Log::info($ex);
        }
        $collection->groupBy("{$tbl}.id");
        $collection->orderBy("{$tbl}.status", 'asc');
        $collection->orderBy("{$tbl}.created_at", 'desc');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /*
     * my register statistic
     */
    public function myStatistic()
    {
        $objWorkingTime = new WorkingTimeView();
        $listStatues = $objWorkingTime->listWTStatusesWithIcon();
        $tbl = static::getTableName();
        $empId = Permission::getInstance()->getEmployee()->id;
        $collect = static::from("{$tbl}");

        foreach (array_keys($listStatues) as $status) {
            $sqlStatus = '';
            $asStatus = '';
            if ($status) {
                $sqlStatus = ' AND '. $tbl . '.status = ' . $status;
                $asStatus = '_' . $status;
            }
            //register
            $collect->addSelect(DB::raw('SUM(CASE WHEN ' . $tbl . '.employee_id = '. $empId . $sqlStatus .' THEN 1 ELSE 0 END) AS register' . $asStatus));
            //related
            $collect->addSelect(DB::raw('SUM(CASE WHEN ' . $tbl . '.related_ids like "%'. $empId .'%"' . $sqlStatus .' THEN 1 ELSE 0 END) AS related' . $asStatus));
            //approve
            $collect->addSelect(DB::raw('SUM(CASE WHEN ' . $tbl . '.approver_id = '. $empId . $sqlStatus .' THEN 1 ELSE 0 END) AS approve' . $asStatus));
        }

        return $collect->first()->toArray();
    }
        
    /**
     * updateStatusItem
     *
     * @param  collection $item
     * @param  int $status
     * @return collection
     */
    public function updateStatusItem($item, $status)
    {
        $currUser = Permission::getInstance()->getEmployee();
        $dataUpdate = [
            'status' => $status,
            'updated_by' => $currUser->id,
        ];
        if ($status === WorkingTimeRegister::STATUS_APPROVE && $item->parent_id) {
            $dataUpdate['parent_id'] = null;
        }
        return $item->update($dataUpdate);
    }
    
    /**
     * Lấy danh sách đơn đăng ký thay đổi thời gian làm việc của nhân viên đã được approved
     *
     * @param $empId
     * @return collection
     */
    public function getWorkingTimeList($empId)
    {
        $tblDetail = WorkingTimeDetail::getTableName();
        $tblWorking = static::getTableName();
        return static::select(
            "{$tblDetail}.working_time_id",
            "{$tblDetail}.employee_id",
            "{$tblDetail}.team_id",
            "{$tblDetail}.from_date",
            "{$tblDetail}.to_date",
            "{$tblDetail}.start_time1",
            "{$tblDetail}.end_time1",
            "{$tblDetail}.start_time2",
            "{$tblDetail}.end_time2",
            "{$tblDetail}.half_morning",
            "{$tblDetail}.half_afternoon",
            "{$tblDetail}.created_at",
            "{$tblWorking}.approver_id",
            "{$tblWorking}.reason",
            "{$tblWorking}.status"
        )
        ->join("{$tblDetail}", "{$tblWorking}.id", '=', "{$tblDetail}.working_time_id")
        ->where("{$tblDetail}.employee_id", $empId)
        ->where("{$tblWorking}.status", static::STATUS_APPROVE)
        ->whereNull("{$tblDetail}.deleted_at")
        ->groupBy("{$tblDetail}.working_time_id")
        ->get();
    }

    /**
     * get list reason disapprove
     * @param integer $registerId
     * @return array
     */
    public function getReasonDisapprove($registerId)
    {
        $tblRegisterComment = WktRegisterComment::getTableName();
        $tblEmployee = Employee::getTableName();
        $tblUser = 'users';
        $comments = WktRegisterComment::join($tblEmployee, "{$tblEmployee}.id", '=', "{$tblRegisterComment}.created_by")
            ->leftJoin($tblUser, "{$tblUser}.employee_id", '=', "{$tblRegisterComment}.created_by")
            ->where("{$tblRegisterComment}.wkt_id", $registerId)
            ->where("{$tblRegisterComment}.type", WorkingTimeRegister::STATUS_REJECT)
            ->select([
                "{$tblRegisterComment}.content",
                "{$tblRegisterComment}.type",
                "{$tblRegisterComment}.created_at",
                "{$tblEmployee}.name",
                "{$tblUser}.avatar_url",
            ]);
        return $comments->get();
    }
}