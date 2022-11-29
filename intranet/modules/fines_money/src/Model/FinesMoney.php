<?php

namespace Rikkei\FinesMoney\Model;

use Carbon\Carbon;
use DB;
use Rikkei\Assets\View\AssetView;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\View;
use Rikkei\FinesMoney\View\ImportFinesMoney;
use Rikkei\HomeMessage\Helper\Constant;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\View\TeamList;

class FinesMoney extends CoreModel
{
    const STATUS_PAID = 1; //Đã nộp tiền phạt
    const STATUS_UN_PAID = 0; // Chưa nộp tiền phạt
    const STATUS_UPDATE_MONEY = 2; // Khi tien hanh cap nhat so tien phat

    const TYPE_LATE = 0; // Type đi muộn
    const TYPE_TURN_OFF = 1; // Type quên tắt máy

    const UNCHECK = 0; //type action
    const CHECKED = 1;
    const UPDATE_MONEY = 2;
    const ROUTE_VIEW_LIST_FINES_MONEY = 'fines-money::fines-money.manage.list';
    const ROUTE_VIEW_HISTORY_FINES_MONEY = 'fines-money::fines-money.manage.history';
    
    const FORMAT_CURRENCY_VND = '#,##0"đ"';

    protected $table = 'fines_money';

    protected $fillable = [
        'employee_id', 'amount', 'status_amount', 'count', 'type', 'month', 'note', 'year'
    ];
    public $timestamps = true;

    /**
     * Get data Fines money
     *
     * @param null $employeeId
     * @return mixed
     */
    public function getGridData($employeeId = null)
    {
        $pager = Config::getPagerData();
        $collection = self::select('id', 'amount', 'count', 'status_amount', 'type', 'month', 'year', 'note')
            ->where('employee_id', $employeeId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->orderBy($pager['order'], $pager['dir']);

        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * Tính toán tiền phạt theo employee
     *
     * @param $employeeId
     * @return mixed
     */
    public function calculateFinesMoneyByEmployeeId($employeeId = null)
    {
        $paidStatus = self::STATUS_PAID;
        $totalBuilder = FinesMoney::query()->selectRaw('SUM(amount)');
        $totalBuilder = self::filterGrid($totalBuilder, [], null, '=');

        if (!empty($employeeId)) {
            $totalBuilder->where('employee_id', $employeeId);
            $totalBuilder->groupBy('employee_id');
        }

        $paidBuilder = clone $totalBuilder;
        $paidBuilder = $paidBuilder->where('status_amount', $paidStatus);

        $totalQuery = $this->builderToSql($totalBuilder);
        $paidQuery = $this->builderToSql($paidBuilder);

        return self::select(DB::raw("($totalQuery) as total"), DB::raw("($paidQuery) as paid"))->first();
    }

    /**
     * Get types
     * @return array
     */
    public function getTypes()
    {
        return [
            self::TYPE_LATE => trans('fines_money::view.type_late'),
            self::TYPE_TURN_OFF => trans('fines_money::view.type_turn_off'),
        ];
    }

    /**
     * Get status
     * @return array
     */
    public function getStatus()
    {
        return [
            self::STATUS_PAID => trans('fines_money::view.status_paid'),
            self::STATUS_UN_PAID => trans('fines_money::view.status_un_paid'),
            self::STATUS_UPDATE_MONEY => trans('fines_money::view.status_updated_money'),
        ];
    }

    /**
     * get data by list conditions
     *
     * @param $options
     * @param null $tab
     * @param null $urlFilter
     * @return mixed
     */
    public function getDataByDate($options, $tab = null, $urlFilter = null, $getQuery = false)
    {
        $pager = Config::getPagerData($urlFilter);

        $collection = DB::table('fines_money')
            ->leftJoin('employees', 'fines_money.employee_id', '=', 'employees.id')
            ->leftJoin('employee_team_history', 'fines_money.employee_id', '=', 'employee_team_history.employee_id')
            ->leftJoin('teams', 'teams.id', '=', 'employee_team_history.team_id')
            ->whereNull('employees.deleted_at')
            ->whereNull('employee_team_history.deleted_at')
            ->where(function ($query) {
                $query->orWhere('fines_money.type', FinesMoney::TYPE_LATE)
                    ->orWhere('fines_money.amount', '>', 0);
            })
            ->select([
                'employees.name', 'employees.employee_code', 'fines_money.amount',
                'fines_money.type', 'status_amount',
                'fines_money.month', 'fines_money.id', 'note',
                'fines_money.employee_id', 'fines_money.year'
            ]);
        if (!empty($options['startMonth'])) {
            $collection->where(DB::raw("date(concat(`year`, '-', `month`, '-1'))"), '>=', "{$options['startMonth']}-01");
        }
        if (!empty($options['endMonth'])) {
            $lastDay = date("Y-m-t", strtotime($options['endMonth']));
            $collection->where(DB::raw("date(concat(`year`, '-', `month`, '-1'))"), '<=', $lastDay);
        }
        if (!empty($options['team_id'])) {
            $teamIds = [];
            $teamIds[] = (int) $options['team_id'];
            AssetView::getTeamChildRecursive($teamIds, $options['team_id']);
            $collection->whereIn("teams.id", $teamIds);
            $collection->where("employee_team_history.is_working", EmployeeTeamHistory::IS_WORKING);
        }
        if (isset($options['is_note']) && $options['is_note']) {
            $collection->whereNotNull('note');
        } elseif (isset($options['is_note']) && !$options['is_note']) {
            $collection->whereNull('note');
        }
        //employee resign
        $collection->whereNull('employees.deleted_at');
        //check export all or not all
        if (isset($options['export_all']) && !$options['export_all']) {
            $itemIds = isset($options['itemsChecked']) ? explode(',', $options['itemsChecked']) : '';
            $collection->whereIn('fines_money.id', $itemIds);
        }
        if (Form::getFilterPagerData('order', $urlFilter)) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('fines_money.year', 'desc')
                        ->orderBy('fines_money.month', 'desc');
        }
        if ($tab == 'paid') {
            $collection->where('status_amount', self::STATUS_PAID);
        } else if ($tab == 'unpaid') {
            $collection->whereIn('status_amount', [self::STATUS_UN_PAID, self::STATUS_UPDATE_MONEY]);
        }
        $this->checkPermissionFineMoney($collection, self::ROUTE_VIEW_LIST_FINES_MONEY);
        $collection->groupBy(['fines_money.employee_id', 'year', 'month', 'fines_money.type']);
        self::filterGrid($collection, [], $urlFilter, 'LIKE');
        if (!empty($options['export'])) {
            return $collection->get();
        }
        if ($getQuery) {
            return $collection;
        }
    }

    // detail Fines Money
    public function historyFinesMoney()
    {
        $pager = Config::getPagerData();
        $collection = DB::table('fines_action_history as fch')
            ->leftJoin('employees as employees', 'fch.object_fines', '=', 'employees.id')
            ->leftJoin('employees as checker', 'fch.checker_id', '=', 'checker.id')
            ->leftJoin('fines_money', 'fines_money.id', '=', 'fch.fines_money_id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'fch.object_fines')
            ->leftjoin('teams', 'teams.id', '=', 'team_members.team_id')
            ->leftJoin('team_members as tmc', 'fch.checker_id', '=', 'tmc.employee_id')
            ->join('teams as tc', 'tc.id', '=', 'tmc.team_id')
            ->select('employees.name', 'checker.name as nameChecker', 'employees.employee_code',
                'object_fines as employee_id', 'fines_money.status_amount', 'fines_money.id',
                'fch.month', 'fch.amount', 'fch.action', 'fch.content',
                'fch.type', 'fch.checked_date', 'fch.year', 'fines_money.note'
            );
        $checker = Form::getFilterData(Employee::getTableName().'.checker');
        if (isset($checker) && $checker) {
            $collection->where('checker.name', 'LIKE', '%'.trim($checker).'%');
        }
        $currentUser = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, self::ROUTE_VIEW_HISTORY_FINES_MONEY)) {

        } elseif (Permission::getInstance()->isScopeTeam(null, self::ROUTE_VIEW_HISTORY_FINES_MONEY)) {
            $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($currentUser->id);
            $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($currentUser);
            $collection->where(function ($query) use ($teamsOfEmp, $teamCodePrefix) {
                $query->orwhereIn('teams.id', $teamsOfEmp)
                    ->orwhere('teams.branch_code', '=',  $teamCodePrefix)
                    ->orwhereIn('tc.id',  $teamsOfEmp)
                    ->orwhere('tc.branch_code', '=',  $teamCodePrefix);
            });
        } else {
            View::viewErrorPermission();
        }

        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('fch.checked_date', 'desc');
        }
        $collection->groupBy('fch.id');
        self::filterGrid($collection, ['employees.checker'], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * @param int $employeeId
     * @param int $month
     * @param int $year
     * @param null|int $type
     * @return collection
     */
    public function getItemByCondition($employeeId, $month, $year, $type)
    {
        return self::where('employee_id', $employeeId)
            ->where('month', $month)
            ->where('year', $year)
            ->where('type', $type);
    }

    /**
     * @param $collection
     * @param string $urlRoute
     * @return mixed
     */
    public function checkPermissionFineMoney($collection, $urlRoute)
    {
        $currentUser = Permission::getInstance()->getEmployee();
        if (Permission::getInstance()->isScopeCompany(null, $urlRoute)) {

        } elseif (Permission::getInstance()->isScopeTeam(null, $urlRoute)) {
            $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($currentUser->id);
            $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($currentUser);
            $collection->where(function ($query) use ($teamsOfEmp, $teamCodePrefix) {
                $query->orwhereIn('teams.id', $teamsOfEmp)
                    ->orwhere('teams.code', 'like',  $teamCodePrefix. '%');
                if ($teamCodePrefix == Team::CODE_PREFIX_HN) {
                    $query->orWhereIn('teams.code', [Team::CODE_PREFIX_AI, TeamConst::CODE_PQA]);
                }
            });
        } else {
            View::viewErrorPermission();
        }
    }

    /**
     * Get color row table by status
     *
     * @param string $status
     * @return string
     */
    public function getColorByStatus($status)
    {
        switch ($status) {
            case self::STATUS_PAID:
                return '#dddddd';
            case self::STATUS_UN_PAID:
            case self::STATUS_UPDATE_MONEY:
                return '#ffffff';
        }
    }

    /**
     * insert fines money working late
     * @param  [date] $date [Y-m-d]
     * @param  [int] $empIds
     * @return [type]
     */
    public static function insertFinesWorkLate($date, $empIds)
    {
        $timeKeepingEmp = TimekeepingAggregate::getEmpTimekeeping($date, $empIds);
        $date = Carbon::parse($date);

        if (!$timeKeepingEmp) {
            return;
        }
        $data = [];
        $teamDN = TeamList::getTeamCode(Team::CODE_PREFIX_DN);
        $teamHCM = TeamList::getTeamCode(Team::CODE_PREFIX_HCM);

        //get tiền phạt trong tháng
        $oldFineMoneys = static::where('month', '=', $date->format("m"))
            ->where('year', '=', $date->format("Y"))
            ->where('type', '=', static::TYPE_LATE)
            ->lists('employee_id')->toArray();

        $dataEmp = [];
        foreach ($timeKeepingEmp as $item) {
            if (empty((float)$item->total_late_start_shift)) {
                continue;
            }
            $fineMoney = with(new FinesMoney())->getBlockFinesMoney($item->team_id, $teamDN, $teamHCM);
            $count = (int)$item->total_late_start_shift / ManageTimeConst::TIME_LATE_IN_PER_BLOCK;
            if (array_key_exists($item->employee_id, $data)) {
                $data[$item->employee_id]['amount'] += $count * $fineMoney;
                $data[$item->employee_id]['count'] += $count;
            } else {
                $data[$item->employee_id] = [
                    'employee_id' => $item->employee_id,
                    'amount' => $count * $fineMoney,
                    'status_amount' => static::STATUS_UN_PAID,
                    'count' => $count,
                    'type' => static::TYPE_LATE,
                    'month' => $item->month,
                    'year' => $item->year,
                ];
                $dataEmp[] = $item->employee_id;
            }
        }

        foreach ($data as $item) {
            $fines = self::where('employee_id', '=', $item["employee_id"])
                ->where('type', '=', static::TYPE_LATE)
                ->where('month', '=', $item["month"])
                ->where('year', '=', $item["year"])
                ->first();
            if ($fines) {
                if ($fines->status_amount == static::STATUS_UN_PAID) {
                    $fines->update($item);
                }
            } else {
                self::create($item);
            }
        }

        //delete update fines money amount = 0
        if (count($oldFineMoneys)) {
            $empIdNotFineyMoney = array_diff($oldFineMoneys, $dataEmp);
            $empIdNotFineyMoney = array_intersect($empIdNotFineyMoney, $empIds);
            static::where('month', $date->format("m"))
            ->where('year', $date->format("Y"))
            ->where('type', static::TYPE_LATE)
            ->where('status_amount', static::STATUS_UN_PAID)
            ->whereIn('employee_id', $empIdNotFineyMoney)
            ->delete();
        }
        return;
    }

    /**
     * @param object $fineMoney
     * @param array $newData
     * @return mixed
     */
    public function updateFinesMoney($fineMoney, $newData)
    {
        $newData['amount'] = !empty($newData['amount']) ?  str_replace([',', '.'], '', $newData['amount']) : 0;
        $newData['note'] = !empty($newData['note']) ? $newData['note'] : null;

        if ($fineMoney) {
            $contentHis = (new FinesActionHistory())->getContentHistory($fineMoney->toArray(), $newData);
            $importObj = new ImportFinesMoney();
            $prefix = $importObj->getPrefixEmployees([$fineMoney->employee_id]); // list prefix of employee
            $onlyPrefix = isset($prefix[$fineMoney->employee_id]) ? $prefix[$fineMoney->employee_id] : Team::CODE_PREFIX_HN;

            if ($fineMoney->type == FinesMoney::TYPE_LATE //check type di muon va chi thay doi so tien => chuyen status = 2
                && $newData['amount'] != $fineMoney->amount
                && $fineMoney->status_amount != FinesMoney::STATUS_PAID
                && $fineMoney->status_amount == $newData['status_amount']) {
                $newData['status_amount'] = FinesMoney::STATUS_UPDATE_MONEY;
            }
            if ($fineMoney->type == FinesMoney::TYPE_TURN_OFF) {
                unset($newData['amount']);
            }
            //tinh lai block vi pham
            if (isset($newData['amount']) && $newData['amount']) {
                $newData['count'] = $importObj->getBlogByPrefixAndMoney($newData['amount'], $onlyPrefix);
            }
            $fineMoney->fill($newData);

            if ($fineMoney->save()) {
                (new FinesActionHistory())->saveFineHis($fineMoney, $contentHis);
            }
        }
        return $fineMoney;
    }

    /**
     * calculation fines money
     * @param  [collection] $collection
     * @return [array]
     */
    public static function getFinesMoney($collection)
    {
        $data = [];
        $teamDN = TeamList::getTeamCode(Team::CODE_PREFIX_DN);
        $teamHCM = TeamList::getTeamCode(Team::CODE_PREFIX_HCM);
        foreach ($collection as $item) {
            if (empty((float)$item->total_late_start_shift)) {
                continue;
            }
            $fineMoney = with(new FinesMoney())->getBlockFinesMoney($item->team_id, $teamDN, $teamHCM);
            $count = (int)$item->total_late_start_shift / ManageTimeConst::TIME_LATE_IN_PER_BLOCK;
            if (array_key_exists($item->employee_id, $data)) {
                $data[$item->employee_id]['amount'] += $count * $fineMoney;
                $data[$item->employee_id]['count'] += $count;
            } else {
                $data[$item->employee_id] = [
                    'employee_id' => $item->employee_id,
                    'amount' => $count * $fineMoney,
                    'status_amount' => static::STATUS_UN_PAID,
                    'count' => $count,
                    'type' => static::TYPE_LATE,
                    'month' => $item->month,
                    'year' => $item->year,
                ];
            }
        }
        return $data;
    }

    /**
     * save fine mmoney late_start_shift
     * @param  [array] $data
     * @return [type]
     */
    public static function saveFinesMoneyLate($data)
    {
        foreach ($data as $item) {
            $fines = self::where('employee_id', '=', $item["employee_id"])
                ->where('type', '=', static::TYPE_LATE)
                ->where('month', '=', $item["month"])
                ->where('year', '=', $item["year"])
                ->first();
            if ($fines) {
                if ($fines->status_amount == static::STATUS_UN_PAID) {
                    $fines->update($item);
                }
            } else {
                self::create($item);
            }
        }
    }

    /**
     * @param string $money
     * @return string
     */
    public function formatMoney($money)
    {
        return (is_null($money) || !is_numeric($money)) ? '' : number_format($money);
    }

    /**
     * Format data export
     *
     * @param collection $data
     * @param $formatKey string
     * @return array
     */
    public function buildDataExport($data)
    {
        $status = $this->getStatus();
        $types = $this->getTypes();
        $results = [];
        $results[] = [
            trans('fines_money::view.label_no'),
            trans('fines_money::view.Code employees'),
            trans('fines_money::view.Name employees'),
            trans('fines_money::view.month'),
            trans('fines_money::view.year'),
            trans('fines_money::view.type'),
            trans('fines_money::view.amount'),
            trans('fines_money::view.status_amount'),
            trans('fines_money::view.Note')
        ];
        foreach ($data as $order => $item) {
            $results[] = [
                $order + 1,
                $item->employee_code,
                $item->name,
                $item->month,
                $item->year,
                data_get($types, $item->type),
                (int)$item->amount,
                data_get($status, $item->status_amount),
                $item->note,
            ];
        }
        return $results;
    }

    /**
     * [getBlockFinesMoney description]
     * @param  [int] $idTeam
     * @param  [array] $teamDN
     * @param  [array] $teamHCM
     * @return [int]
     */
    private function getBlockFinesMoney($idTeam, $teamDN, $teamHCM)
    {
        if (in_array($idTeam, $teamDN)) {
            return ManageTimeConst::FINES_LATE_IN_PER_BLOCK_DN;
        }
        if (in_array($idTeam, $teamHCM)) {
            return ManageTimeConst::FINES_LATE_IN_PER_BLOCK_HCM;
        }
        return ManageTimeConst::FINES_LATE_IN_PER_BLOCK;
    }

    /**
     * nhân viên không phạt đi muộn
     * @param  [type] $email
     * @return [type]
     */
    public function empNotFinesMoney()
    {
        return [
            'hoa.dang@rikkeisoft.com',
            'sonbx@rikkeisoft.com',
            'luanhh@rikkeisoft.com',
            'thanhlvt@rikkeisoft.com',
            'anhptl@rikkeisoft.com',
            'dung.phan@rikkeisoft.com',
            'tung.ta@rikkeisoft.com',
            'manhlk@rikkeisoft.com',
            'bauhm@rikkeisoft.com',
            'hoannv@rikkeisoft.com',
            'quynhnh@rikkeisoft.com',
            'quangnv@rikkeisoft.com',
            'dungtm@rikkeisoft.com',
            'minhln@rikkeisoft.com',
            'longnh2@rikkeisoft.com',
            'tannm@rikkeisoft.com',
            'manhnt@rikkeisoft.com',
            'tungvt2@rikkeisoft.com',
            'hainv@rikkeisoft.com',
            'lamnv3@rikkeisoft.com',
            'trangntq@rikkeisoft.com',
        ];
    }

    /**
     * nhân viên không bị phạt thứ 4
     * @param  [date|carbon] $date [Y-m-d]
     * @param  [type] $email
     * @return [type]
     */
    public function empNotFinesMoneyWednesday($date)
    {
        if (!($date instanceof Carbon)) {
            $date = Carbon::createFromFormat('Y-m-d', $date);
        }
        if ($date->dayOfWeek != Constant::WEDNESDAY) {
            return [];
        }
        return [
            'hoant2@rikkeisoft.com',
        ];
    }

    /**
     * check nhân viên không bị phạt đi muộn
     * @param  [type] $date
     * @param  [type] $email
     * @return [type]
     */
    public function checkEmpNotFinesMoney($date, $email)
    {
        return (in_array($email, $this->empNotFinesMoney()) ||
            in_array($email, $this->empNotFinesMoneyWednesday($date)));
    }
}
