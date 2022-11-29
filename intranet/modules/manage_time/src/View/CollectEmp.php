<?php

namespace Rikkei\ManageTime\View;

use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\Model\LeaveDayRegister;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Role;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Rikkei\ManageTime\View\ManageTimeConst;

class CollectEmp
{
    /**
     * list registration type options
     * @return array
     */
    public static function aryTypeTable()
    {
        return [
            ManageTimeConst::TYPE_LEAVE_DAY => [
                'table' => LeaveDayRegister::getTableName(),
                'employee' => 'creator_id',
                'start_date' => 'date_start',
                'end_date' => 'date_end',
                'approve_status' => LeaveDayRegister::STATUS_APPROVED,
            ],
            ManageTimeConst::TYPE_MISSION => [
                'table' => BusinessTripEmployee::getTableName(),
                'join' => BusinessTripRegister::getTableName(),
                'employee' => 'employee_id',
                'start_date' => 'start_at',
                'end_date' => 'end_at',
                'approve_status' => BusinessTripRegister::STATUS_APPROVED,
                'has_parent' => true
            ]
        ];
    }

    /**
     * list employee registration times
     * @param array $data
     * @param array $options
     * @return collection
     */
    public static function listRegistrationTimes($data = [], $options = [])
    {
        $empTbl = Employee::getTableName();
        $tmbTbl = EmployeeTeamHistory::getTableName();
        $teamTbl = Team::getTableName();

        $options = array_merge([
            'select' => [
                $empTbl.'.id as employeeid',
                $empTbl.'.name as fullname',
                $empTbl.'.email',
                $empTbl.'.employee_code',
                $empTbl.'.employee_card_id',
                DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(role.role, " - ", team.name)) SEPARATOR ", ") as position')
            ],
        ], $options);
        $date = Carbon::createFromFormat('d/m/Y', $data['date'])->toDateString();

        $collect = Employee::select($options['select'])
                ->join($tmbTbl.' as tmb', 'tmb.employee_id', '=', $empTbl.'.id')
                ->join($teamTbl.' as team', 'tmb.team_id', '=', 'team.id')
                ->join(Role::getTableName().' as role', 'tmb.role_id', '=', 'role.id')
                ->where(function($query) use ($empTbl, $date) {
                    $query->whereNull($empTbl.'.leave_date')
                            ->orWhere($empTbl.'.leave_date', '>', $date);
                })
                ->where(function ($query) use ($date) {
                    $query->whereNull('tmb.start_at')
                            ->orWhere(DB::raw('DATE(tmb.start_at)'), '<=', $date);
                })
                ->where(function ($query) use ($date) {
                    $query->whereNull('tmb.end_at')
                            ->orWhere(DB::raw('DATE(tmb.end_at)'), '>=', $date);
                })
                ->whereNull('tmb.deleted_at')
                ->groupBy($empTbl.'.id');

        if (isset($data['team_code'])) {
            $collect->where('team.code', 'LIKE', $data['team_code'] . '%');
        }

        if (isset($data['exclude_types'])) {
            $aryTypes = self::aryTypeTable();
            foreach ($aryTypes as $type => $dataType) {
                if (!in_array($type, $data['exclude_types'])) {
                    continue;
                }
                $collect->whereNotIn($empTbl.'.id', function($query) use ($dataType, $date) {
                    $query->select($dataType['employee'])
                            ->from($dataType['table'])
                            ->where(DB::raw('DATE('. $dataType['start_date'] .')'), '<=', $date)
                            ->where(DB::raw('DATE('. $dataType['end_date'] .')'), '>=', $date)
                            ->whereNull('deleted_at');

                    if (isset($dataType['join'])) {
                        $query->join($dataType['join'], $dataType['join'].'.id', '=', $dataType['table'].'.register_id');
                    }
                    if (isset($dataType['has_parent'])) {
                        $query->whereNull('parent_id');
                    }
                    $query->where('status', $dataType['approve_status']);
                });
            }
        }

        $pager = [
            'limit' => 50,
            'order' => 'employeeid',
            'dir' => 'asc',
            'page' => 1
        ];
        $pager = array_merge($pager, array_only($data, ['order', 'dir', 'page']));

        try {
            $collect->orderBy($pager['order'], $pager['dir']);
        } catch (\Exception $ex) {
            $collect->orderBy('employeeid', 'asc');
        }

        if (isset($data['page']) && $data['page']) {
            Employee::pagerCollection($collect, $pager['limit'], $pager['page']);
            return $collect;
        }

        return $collect->get();
    }
}

