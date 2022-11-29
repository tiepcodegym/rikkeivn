<?php

namespace Rikkei\ManageTime\Model;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Lang;
use mysql_xdevapi\Collection;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Ot\Model\OtEmployee;
use Rikkei\Ot\Model\OtRegister;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

class TimekeepingLockHistories extends CoreModel
{
    protected $fillable = [
        'timekeeping_lock_id',
        'inform_id',
        'employee_id',
        'type',
        'status',
        'updated_status',
    ];

    const TYPE_CT = 2;
    const TYPE_P = 3;
    const TYPE_BSC = 4;
    const TYPE_BSC_OT = 5;
    const TYPE_OT = 6;

    const STATUS_NOT_UPDATE = 1;
    const STATUS_UPDATED = 2;

    /**
     * get information lock history by timekeeping_lock_id
     *
     * @param int $idLock
     * @return object
     */
    public function getInfoById($idTable)
    {
        $tblLock = TimekeepingLock::getTableName();
        $tblLH = self::getTableName();
        return self::select(
            'timekeeping_lock_id',
            'inform_id',
            'type',
            'status',
            'updated_status'
        )
        ->leftJoin("{$tblLock}", "{$tblLock}.id", "=", "{$tblLH}.timekeeping_lock_id")
        ->where("{$tblLock}.timekeeping_table_id", $idTable)
        ->get();
    }

    /**
     * get array info id with key timekeeping_lock_id
     * @param Collection $infoHistories
     * @return array
     */
    public function getInfoIdLock($infoHistories)
    {
        $array = [];
        foreach ($infoHistories as $item) {
            $array[$item->timekeeping_lock_id][$item->type][] = $item->inform_id;
        }
        return $array;
    }

    /**
     * insert data array
     * @param  collection $tkLock [Model]
     * @param  array $dataInsert
     */
    public function insertData($tkLock, $dataInsert)
    {
        $data = [];
        foreach ($dataInsert as $value) {
            $data[] = new TimekeepingLockHistories($value);
        }
        $tkLock->lockHistories()->saveMany($data);
        return;
    }

    /**
     * get information all application register after lock
     *
     * @param $idLock
     * @return mixed
     */
    public function getEmpAfterLock($idLock)
    {
        $filter = Form::getFilterData();
        $tblLH = self::getTableName();
        $tblEmp = Employee::getTableName();
        $tblLeaveDay = LeaveDayRegister::getTableName();
        $tblSupp = SupplementRegister::getTableName();
        $tblBusiness = BusinessTripRegister::getTableName();
        $tblbusinessEmp = BusinessTripEmployee::getTableName();
        $tblOT = OtRegister::getTableName();
        $tblOTEmp = OtEmployee::getTableName();

        $collections =  self::select(
            "{$tblLH}.timekeeping_lock_id",
            "emp.id as emp_id",
            "emp.name as emp_name",
            "emp.employee_code as emp_code",
            DB::raw("GROUP_CONCAT(teams.name) as team_name"),
            "ld.id as leave_day_id",
            "ld.date_start as leave_day_date_start",
            "ld.date_end as leave_day_date_end",
            "ld.updated_at as leave_updated_at",
            "ld.deleted_at as leave_deleted_at",
            "ld.status as leave_status",
            "business.id as business_id",
            "businessEmp.start_at as business_start_at",
            "businessEmp.end_at as business_end_at",
            "business.updated_at as bus_updated_at",
            "business.deleted_at as bus_deleted_at",
            "business.status as business_status",
            "supp.id as supp_id",
            "supp.is_ot",
            "supp.date_start as supp_date_start",
            "supp.date_end as supp_date_end",
            "supp.updated_at as supp_updated_at",
            "supp.deleted_at as supp_deleted_at",
            "supp.status as supp_status",
            "ot.id as ot_id",
            "registerOTEmp.start_at as ot_date_start",
            "registerOTEmp.end_at as ot_date_end",
            "ot.updated_at as ot_updated_at",
            "ot.deleted_at as ot_deleted_at",
            "ot.status as ot_status",
            "{$tblLH}.type",
            "{$tblLH}.status",
            "{$tblLH}.updated_status"
        )
        ->leftJoin("{$tblEmp} as emp", 'emp.id', '=', "{$tblLH}.employee_id")
        ->leftJoin('team_members as tm', 'tm.employee_id', '=', 'emp.id')
        ->leftJoin('teams', 'teams.id', '=', 'tm.team_id')

        ->leftJoin("{$tblLeaveDay} as ld", function($join) use ($tblLH) {
            $join->on('ld.id', '=', "{$tblLH}.inform_id")
                ->where("{$tblLH}.type", '=', self::TYPE_P);
        })

        ->leftJoin("{$tblSupp} as supp", function($join) use ($tblLH) {
            $join->on('supp.id', '=', "{$tblLH}.inform_id")
                ->whereIn("{$tblLH}.type", [self::TYPE_BSC, self::TYPE_BSC_OT]);
        })

        ->leftJoin("{$tblBusiness} as business", function($join) use ($tblLH) {
            $join->on('business.id', '=', "{$tblLH}.inform_id")
                ->where("{$tblLH}.type", '=', self::TYPE_CT);
        })
        ->leftJoin("{$tblbusinessEmp} as businessEmp", function($join) use ($tblLH) {
            $join->on('business.id', '=', "businessEmp.register_id")
                ->on('businessEmp.employee_id', '=', "{$tblLH}.employee_id");
        })

        ->leftJoin("{$tblOT} as ot", function($join) use ($tblLH) {
            $join->on('ot.id', '=', "{$tblLH}.inform_id")
                ->where("{$tblLH}.type", '=', self::TYPE_OT);
        })
        ->leftJoin("{$tblOTEmp} as registerOTEmp", function($join) use ($tblLH) {
            $join->on('ot.id', '=', "registerOTEmp.ot_register_id")
                ->on('registerOTEmp.employee_id', '=', "{$tblLH}.employee_id");
        })
        ->where("{$tblLH}.timekeeping_lock_id", $idLock);
        if (!empty($filter['code'])) {
            $collections->where("emp.employee_code", 'like', '%' . trim($filter['code']) . '%');
        }
        if (!empty($filter['name'])) {
            $collections->where("emp.name", 'like', '%' . trim($filter['name']) . '%');
        }

        return $collections->groupBy("{$tblLH}.id")
            ->orderBy("{$tblLH}.type")
            ->get();
    }

    /**
     * convert collections to array with information all application register
     *
     * @param $collections
     * @return array
     */
    public function getArrayEmpAfterLock($collections)
    {
        if (!count($collections)) {
            return [];
        }
        $result = [];
        foreach ($collections as $item) {
            $dataHistory = [
                'isOT' => $item->is_ot,
                'status' => $item->status,
                'updatedStatus' => $item->updated_status,
            ];
            $infor = [
                'empId' => $item->emp_id,
                'empName' => $item->emp_name,
                'empCode' => $item->emp_code,
                'empTeam' => $item->team_name,
            ];
            switch ($item->type) {
                case self::TYPE_P:
                    $dataType = [
                        'id' => $item->leave_day_id,
                        'startAt' => $item->leave_day_date_start,
                        'endAt' => $item->leave_day_date_end,
                        'type' => $item->type,
                        'updatedAt' => $item->leave_updated_at,
                        'deletedAt' => $item->leave_deleted_at,
                        'statusApp' => $item->leave_status,
                    ];
                    break;
                case self::TYPE_CT:
                    $dataType = [
                        'id' => $item->business_id,
                        'startAt' => $item->business_start_at,
                        'endAt' => $item->business_end_at,
                        'type' => $item->type,
                        'updatedAt' => $item->bus_updated_at,
                        'deletedAt' => $item->bus_deleted_at,
                        'statusApp' => $item->business_status,
                    ];
                    break;
                case self::TYPE_OT:
                    $dataType = [
                        'id' => $item->ot_id,
                        'startAt' => $item->ot_date_start,
                        'endAt' => $item->ot_date_end,
                        'type' => $item->type,
                        'updatedAt' => $item->ot_updated_at,
                        'deletedAt' => $item->ot_deleted_at,
                        'statusApp' => $item->ot_status,
                    ];
                    break;
                default:
                    $dataType = [
                        'id' => $item->supp_id,
                        'startAt' => $item->supp_date_start,
                        'endAt' => $item->supp_date_end,
                        'type' => $item->type,
                        'status' => $item->status,
                        'updatedAt' => $item->supp_updated_at,
                        'deletedAt' => $item->supp_deleted_at,
                        'statusApp' => $item->supp_status,
                    ];
                    break;
            }
            if (!empty($dataType['deletedAt']) || empty($dataType['startAt'])) {
                continue;
            }
            $data = array_merge($dataType, $dataHistory);
            $result[$item->emp_id]['infoEmp'] = $infor;
            $result[$item->emp_id]['app'][] = $data;
            $result[$item->emp_id]['infoEmp']['number'] = count($result[$item->emp_id]['app']);
        }
        return $result;
    }

    /**
     * get label column type in table timekeeping_lock_histories
     * @return array
     */
    public function getLabelType()
    {
        return [
            self::TYPE_CT => Lang::get('manage_time::view.Business trip register'),
            self::TYPE_P => Lang::get('manage_time::view.Leave day register'),
            self::TYPE_BSC => Lang::get('manage_time::view.Supplement register'),
            self::TYPE_BSC_OT => Lang::get('manage_time::view.Supplement register') . 'OT',
            self::TYPE_OT => Lang::get('manage_time::view.OT register'),
        ];
    }

    /**
     * get label column status in table timekeeping_lock_histories
     *
     * @return array
     */
    public function getlabelStatus()
    {
        return [
            self::STATUS_UPDATED => Lang::get('manage_time::view.Updated'),
            self::STATUS_NOT_UPDATE => Lang::get('manage_time::view.Not update'),
        ];
    }

    /**
     * update status in table lock history
     *
     * @param array $appIds
     * @param array $empsIdOfTimeKeeping
     * @param int $type
     */
    public function updateStartLockHistory($appIds, $empsIdOfTimeKeeping, $type)
    {
        $lockIds = self::select('id')
            ->whereIn('inform_id', $appIds)
            ->where('type', $type)
            ->groupBy('id')
            ->get()->lists('id')->toArray();
        if (!$lockIds || !count($empsIdOfTimeKeeping)) {
            return;
        }
        foreach ($empsIdOfTimeKeeping as $empId) {
            self::whereIn('id', $lockIds)
                ->where('employee_id', $empId)
                ->update([
                'status' => self::STATUS_UPDATED,
                'updated_status' => Carbon::now(),
            ]);
        }
        return;
    }
}