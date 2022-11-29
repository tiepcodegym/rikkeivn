<?php

namespace Rikkei\Contract\Model;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Rikkei\Contract\View\Config;
use Rikkei\Core\Model\CoreModel;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\Permission as PermissionModel;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamConst;

class ContractModel extends CoreModel
{
    protected $table = 'contracts';

    use SoftDeletes;
    /*
     * The attributes that are mass assignable.
     */

    protected $primaryKey = 'id';

    protected $fillable = [
        'employee_id',
        'type',
        'start_at',
        'end_at',
        'hrm_contract_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'status'
    ];

    protected $appends = ['employee_info', 'employee_job'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function employee()
    {
        return $this->hasOne(EmployeeModel::class, 'id', 'employee_id');
    }

    public function userCreated()
    {
        return $this->hasOne(EmployeeModel::class, 'id', 'created_id');
    }

    public function confirmExpire()
    {
        return $this->hasOne(ContractConfirmExpire::class, 'contract_id', 'id');
    }

    /**
     * Employee info by contract active
     * @return \Rikkei\Contract\Model\EmployeeModel
     */
    public function getEmployeeInfoAttribute()
    {
        return $this->employee()->first();
    }

    /**
     * Get employee job
     * @return string
     */
    public function getEmployeeJobAttribute()
    {
        return $this->employee()->join('team_members', 'team_members.employee_id', '=', 'employees.id')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->join('roles', 'roles.id', '=', 'team_members.role_id')
            ->selectRaw("GROUP_CONCAT(DISTINCT CONCAT(teams.name, ' - ', roles.role) ORDER BY roles.role DESC SEPARATOR ';<br/> ') as employee_job")->value('employee_job');
    }

    /**
     * Create or update contract
     * @param array $data [sel_employee_id,sel_contract_type,txt_start_at,txt_end_at]
     * @param int $id
     * @return \Rikkei\Contract\Model\ContractModel
     * @throws Exception
     */
    public static function saveContract(array $data = array(), $id = 0)
    {
        DB::beginTransaction();
        try {
            if ($id > 0) {
                $modelContract = self::getContractById($id);
                if (!$modelContract) {
                    throw new Exception('contract::message.Contract not found');
                }
            } else {
                $modelContract = new ContractModel();
                $modelContract->employee_id = $data['sel_employee_id'];
            }
            $modelContract->type = $data['sel_contract_type'];
            $modelContract->created_id = Auth::user()->employee_id;

            ###
            $startAt = isset($data['txt_start_at']) && trim($data['txt_start_at']) != '' ? Carbon::parse($data['txt_start_at']) : null;
            $modelContract->start_at = $startAt;
            if (isset($data['txt_end_at']) && trim($data['txt_end_at']) != '') {
                $endAt = Carbon::parse($data['txt_end_at']);
            } else {
                $endAt = null;
            }
            $modelContract->end_at = $endAt;
            $resp = $modelContract->save();
            if (!$resp) {
                throw new Exception('System error');
            }
            $modelContract->saveHistory();
            DB::commit();
            return $modelContract;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * Delete contract
     * @param int $id id contract
     * @return boolean
     * @throws Exception
     */
    public static function deleteContract($id)
    {
        $collectionModel = self::getContractById($id);
        if (!$collectionModel) {
            throw new Exception(trans('contract::message.Employee not found'));
        }
        DB::beginTransaction();
        try {
            $collectionModel->delete();
            $collectionModel->saveHistory();
            DB::commit();
            return true;
        } catch (Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    public static function getContractsWithConfirm()
    {
        return self::query()->withTrashed()->with('confirmExpire')
            ->select('id', 'employee_id', 'type', 'start_at', 'end_at', 'deleted_at')->get();
    }
    /**
     *
     * @return boolean
     */
    public function saveHistory()
    {
        if (!$this->id) {
            throw new Exception(trans('contract::message.Employee not found'));
        }
        return ContractHistoryModel::saveHistory($this);
    }

    /**
     * Combines SQL and its bindings
     *
     * @param \Eloquent $query
     * @return string
     */
    public static function getEloquentSqlWithBindings($query)
    {
        return vsprintf(str_replace('?', '%s', $query->toSql()), collect($query->getBindings())->map(function ($binding) {
            $binding = addslashes($binding);
            return is_numeric($binding) ? $binding : "'{$binding}'";
        })->toArray());
    }

    /**
     * @param array $dataFilter
     * @param null $type
     * @param bool $getAll
     * @return \Rikkei\Core\Model\collection|\Rikkei\Core\Model\model
     */
    public static function getAllContract(array $dataFilter = [], $type = null, $getAll = false)
    {
        $employeeTable = EmployeeModel::getTableName();
        $contractTable = self::getTableName();
        $roleTable = Role::getTableName();
        $teamTable = Team::getTableName();
        $teamMember = TeamMember::getTableName();
        //chức vụ, bộ phận chuwa lam
        $collection = self::select([
            "{$contractTable}.id",
            "{$employeeTable}.name as employee_name",
            "{$employeeTable}.employee_code",
            "{$employeeTable}.email as employee_email",
            "{$contractTable}.type as contract_type",
            "{$contractTable}.employee_id",
            "{$contractTable}.start_at",
            "{$contractTable}.end_at",
            DB::raw("(SELECT GROUP_CONCAT(
                                DISTINCT CONCAT($teamTable.name, ' - ', $roleTable.role) 
                                ORDER BY $roleTable.role DESC SEPARATOR ';<br/> '
                              ) FROM team_members
                               JOIN `$teamTable` 
                                ON `$teamTable`.`id` = `$teamMember`.`team_id` 
                               JOIN `$roleTable` 
                                ON `$roleTable`.`id` = `$teamMember`.`role_id` 
                                 WHERE $teamMember.`employee_id` = $employeeTable.id)
                                 AS employee_job")
        ]);
        if ($type === 'none') {
            #Right join employee
            $allEmpsNotContract = EmployeeModel::whereNotExists(function ($sql) use ($contractTable, $employeeTable) {
                $sql->selectRaw(DB::raw(1))
                    ->from($contractTable)
                    ->whereRaw("$contractTable.employee_id = $employeeTable.id");
            })
                ->where(function ($sql) use ($employeeTable) {
                    $sql->where("{$employeeTable}.leave_date", '>=', date('Y-m-d H:i:s'))
                        ->orWhereNull("{$employeeTable}.leave_date");
                });
            $collection->rightJoin(DB::Raw('(' . self::getEloquentSqlWithBindings($allEmpsNotContract) . ')as ' . $employeeTable), "{$employeeTable}.id", '=', "{$contractTable}.employee_id")
                ->select("{$employeeTable}.name as employee_name", "{$employeeTable}.employee_code", "{$employeeTable}.email as employee_email", "{$employeeTable}.id as employee_id")
                ->whereNull("$contractTable.id")
                ->withTrashed()//Hợp đồng bị xóa được gán như là chưa được tạo
                ->groupBy("$employeeTable.id");
        } elseif ($type === 'not-yet-extended') {
            $collection->join($employeeTable, "{$employeeTable}.id", '=', "{$contractTable}.employee_id")
                ->whereNull("{$employeeTable}.deleted_at")
                ->where(function ($sql) use ($employeeTable) {
                    $sql->where("{$employeeTable}.leave_date", '>=', date('Y-m-d H:i:s'))
                        ->orWhereNull("{$employeeTable}.leave_date");
                });
            $collection->whereIn("{$contractTable}.end_at", function ($query) use ($contractTable) {
                $query->select(DB::raw("MAX(end_at)"))
                    ->from("{$contractTable}")
                    ->groupBy("employee_id");
            });
            $collection->where("{$contractTable}.end_at", "<", date('Y-m-d 00:00:00'))
                ->whereNull("{$contractTable}.deleted_at");
        } else {
            #Join employee
            $collection->join($employeeTable, "{$employeeTable}.id", '=', "{$contractTable}.employee_id")
                ->whereNull("{$employeeTable}.deleted_at")
                ->where(function ($sql) use ($employeeTable) {
                    $sql->where("{$employeeTable}.leave_date", '>=', date('Y-m-d H:i:s'))
                        ->orWhereNull("{$employeeTable}.leave_date");
                });
            if ($type === 'about-to-expire') {
                $fromExpireDate = !empty($dataFilter['except']['start_at_expire']) ? (string)$dataFilter['except']['start_at_expire'] : '';
                $toExpireDate = !empty($dataFilter['except']['end_at_expire']) ? (string)$dataFilter['except']['end_at_expire'] : '';
                self::findBetweenExpireDate($collection, "{$contractTable}.end_at", $fromExpireDate, $toExpireDate);
            }
        }
        $collection->leftJoin($teamMember, "{$employeeTable}.id", '=', "{$teamMember}.employee_id")
            ->leftJoin($teamTable, "{$teamTable}.id", '=', "{$teamMember}.team_id")
            ->leftJoin($roleTable, "{$roleTable}.id", '=', "{$teamMember}.role_id");
        ##Filter start at or end at
        $columnEndAtFilter = "{$contractTable}.end_at";
        $columnStartAtFilter = "{$contractTable}.start_at";
        if (!empty($dataFilter['except'][$columnEndAtFilter])) {
            $endAtFilter = $dataFilter['except'][$columnEndAtFilter];
            self::filterDate($collection, $columnEndAtFilter, $endAtFilter);
        }
        if (!empty($dataFilter['except'][$columnStartAtFilter])) {
            $startAtFilter = $dataFilter['except'][$columnStartAtFilter];
            self::filterDate($collection, $columnStartAtFilter, $startAtFilter);
        }
        ##Filter job or team
        if (!empty($dataFilter['except']['employee_job'])) {
            $jobFilter = trim($dataFilter['except']['employee_job']);
            $findFilter = strrpos($jobFilter, ' - ');
            $collection->where(function ($sql) use ($roleTable, $teamTable, $jobFilter, $findFilter) {
                $sql->orWhere("{$teamTable}.name", 'like', "%{$jobFilter}%")
                    ->orWhere("{$roleTable}.role", 'like', "%{$jobFilter}%");
                if ($findFilter) {
                    $nameFilter = substr($jobFilter, 0, $findFilter);
                    $roleFilter = trim(substr($jobFilter, $findFilter), ' - ');
                    $sql->orWhere("{$teamTable}.name", 'like', "{$nameFilter}%")
                        ->where("{$roleTable}.role", 'like', "%{$roleFilter}");
                }
            });
        }
        ##Filter by roles
        $scopeRole = Permission::getInstance()->getScopeCurrentOfRole();
        if (!empty($scopeRole['scope']) && $scopeRole['scope'] == PermissionModel::SCOPE_COMPANY) {
            $scopeRole = PermissionModel::SCOPE_COMPANY;
        } else {
            //Lấy scope lớn nhất thuộc team bất kỳ
            $scopeRoleOfTeam = Permission::getInstance()->getScopeCurrentOfTeam();
            if (!empty($scopeRoleOfTeam['max_scope'])) {
                $scopeRole = (int)$scopeRoleOfTeam['max_scope'] > (int)$scopeRole['scope'] ? (int)$scopeRoleOfTeam['max_scope'] : (int)$scopeRole['scope'];
            } else {
                $scopeRole = !empty($scopeRole['scope']) ? (int)$scopeRole['scope'] : PermissionModel::SCOPE_NONE;
            }
        }
        if ($scopeRole != PermissionModel::SCOPE_COMPANY) {
            /**
             * - Quyền cá nhân + quyền team: Chỉ xem được danh sách hợp đồng trong các division mình có quyền quản lý
             */
            $teamIds = Permission::getInstance()->isScopeTeam();
            $collection->whereIn("{$teamTable}.id", $teamIds)
                ->whereNull("{$teamMember}.deleted_at");
        }
        if ($type === 'about-to-expire') {
            $pager = Config::getPagerData(request()->url(), ['order' => "{$contractTable}.end_at", 'dir' => 'DESC']);
        } else {
            $pager = Config::getPagerData(request()->url(), ['order' => "{$contractTable}.start_at", 'dir' => 'DESC']);
        }

        $collection->groupBy("{$contractTable}.id");
        $collection = $collection->orderBy($pager['order'], $pager['dir']);


        if ($getAll) {
            if (empty($dataFilter['url_filter'])) {
                $dataFilter['url_filter'] = request()->url();
            }
            $collection = self::filterGrid($collection, [], $dataFilter['url_filter'], 'LIKE');
            return $collection->get();
        }

        $collection = self::filterGrid($collection, [], request()->url(), 'LIKE');
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * @param $collection
     * @param $columnName
     */
    public static function findWhereExpireDate(&$collection, $columnName)
    {
        $now = Carbon::now();
        $startAtTmpDefinite = clone $now;
        $toDateDefinite = $startAtTmpDefinite->addDay(LIMIT_DAY_FILTER_CONTRACT_BY_DEFINITE);
        $startAtTmpTemporary = clone $now;
        $toDateTemporary = $startAtTmpTemporary->addDay(LIMIT_DAY_FILTER_CONTRACT_BY_TEMPORARY);

        $toDateDefinite = $toDateDefinite->toDateString();
        $toDateTemporary = $toDateTemporary->toDateString();

        $collection->where(function ($sql) use ($columnName, $toDateDefinite, $toDateTemporary) {
            $typeOfficial = getOptions::WORKING_OFFICIAL;
            $sql->whereRaw("if(contracts.type = $typeOfficial,  $columnName = '$toDateDefinite' , $columnName  = '$toDateTemporary')");
        });
    }

    /**
     * @param $employeeId
     * @return bool
     */
    public static function getCountContractWorkingOff($employeeId, $contractId)
    {
        $collection = self::where('type', getOptions::WORKING_OFFICIAL)
            ->where('employee_id', $employeeId)
            ->whereNull('deleted_at')
            ->orderBy('end_at', 'ASC')
            ->get();
        if (isset($collection)) {
            foreach ($collection as $key => $item) {
                if ($item->id == $contractId) {
                    return '-' . ($key + 1);
                }
            }
        }
        return false;
    }

    /**
     * @param $collection
     * @param $columnName
     */
    public static function findBetweenExpireDate(&$collection, $columnName, $fromExpireDate = '', $toExpireDate = '')
    {
        $now = Carbon::now();
        if (trim($fromExpireDate) == '') {
            $startAt = $now;
        } else {
            $startAt = Carbon::parse($fromExpireDate);
        }
        if (trim($toExpireDate) == '') {
            $startAtTmpDefinite = clone $now;
            $toDateDefinite = $startAtTmpDefinite->addDay(LIMIT_DAY_FILTER_CONTRACT_BY_DEFINITE);
            $startAtTmpTemporary = clone $now;
            $toDateTemporary = $startAtTmpTemporary->addDay(LIMIT_DAY_FILTER_CONTRACT_BY_TEMPORARY);

        } else {
            $toDateDefinite = Carbon::parse($toExpireDate);
            $toDateTemporary = Carbon::parse($toExpireDate);
        }
        $startAt = $startAt->toDateString();
        $toDateDefinite = $toDateDefinite->toDateString();
        $toDateTemporary = $toDateTemporary->toDateString();

        $collection->where(function ($sql) use ($startAt, $columnName, $toDateDefinite, $toDateTemporary) {
            $typeOfficial = getOptions::WORKING_OFFICIAL;
            $sql->whereRaw("if(contracts.type = $typeOfficial,  $columnName between '$startAt' and '$toDateDefinite' , $columnName  between '$startAt' and '$toDateTemporary')");
        });
    }

    /**
     * Filter date
     * @param object $collection
     * @param string $columnName
     * @param string $valueFilter
     */
    public static function filterDate(&$collection, $columnName, $valueFilter)
    {
        $valueFilter = trim($valueFilter);
        $expvalueFilter = explode('-', $valueFilter);
        $countExpValue = count($expvalueFilter);

        if ($countExpValue == 3) {
            $collection->whereRaw("DATE_FORMAT($columnName,'%d-%m-%Y') = '$valueFilter'");
        } elseif ($countExpValue == 2) {
            $collection->where(function ($query) use ($columnName, $valueFilter) {
                $query->whereRaw("DATE_FORMAT({$columnName},'%d-%m') = '{$valueFilter}' or DATE_FORMAT({$columnName},'%m-%Y') = '{$valueFilter}'");
            });
        } elseif ($countExpValue == 1) {
            $collection->where(function ($query) use ($columnName, $valueFilter) {
                $query->orWhere(function ($sql) use ($columnName, $valueFilter) {
                    $sql->whereDate($columnName, '=', $valueFilter);
                });
                $query->orWhere(function ($sql) use ($columnName, $valueFilter) {
                    $sql->whereYear($columnName, '=', $valueFilter);
                });
                $query->orWhere(function ($sql) use ($columnName, $valueFilter) {
                    $sql->whereMonth($columnName, '=', $valueFilter);
                });
                $query->orWhere(function ($sql) use ($columnName, $valueFilter) {
                    $sql->whereDay($columnName, '=', $valueFilter);
                });
            });
        } else {
            //todo
        }
    }

    public static function getContractById($id)
    {
        return self::with('employee')->where('id', $id)->whereNull('deleted_at')->first();
    }

    /**
     * Get all working type
     * @return array array working type
     */
    public static function getAllTypeContract()
    {
        return EmployeeWork::getAllTypeContract();
    }

    /**
     * Get working name
     * @return string working name
     */
    public function getContractLabel()
    {
        $allContract = self::getAllTypeContract();
        return isset($allContract[$this->type]) ? $allContract[$this->type] : '';
    }

    /**
     * get join date of employee
     * @param type $empId
     * @return boolean
     */
    public static function getJonInDate($empId)
    {
        $empInfo = EmployeeModel::where([
            ['id', '=', $empId],
        ])
            ->whereNull('deleted_at')
            ->first();
        if ($empInfo) {
            return $empInfo->join_date;
        }
        return false;
    }

    /**
     * Kiem tra thoi gian $datetime da ton tai ho so
     * @param int $empId Employee id
     * @param string $datetime Y-m-d H:i:s
     * @return boolean TRUE or FALSE
     */
    public static function checkTimeIsBusy($empId, $datetime, $id = 0)
    {
        $collection = ContractModel::where('employee_id', $empId)
            ->where(function ($sql) use ($datetime) {
                $sql->where([
                    ['start_at', '<=', $datetime],
                    ['end_at', '>=', $datetime],
                ])
                    ->orWhere(function ($sql) use ($datetime) {
                        $sql->where([['start_at', '<=', $datetime]])
                            ->whereNull('end_at');
                    });
            });
        if ($id > 0) {
            $collection->where('id', '<>', $id);
        }
        $count = $collection->count();
        if ((int)$count > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get Contract last by employee ID
     * @param int $empId
     * @return \Rikkei\Contract\Model\ContractModel || null
     */
    public static function getContractLast($empId)
    {
        return ContractModel::where('employee_id', $empId)
            ->whereNull('deleted_at')
            ->orderBy('start_at', 'desc')
            ->first();
    }

    public function isContractLast()
    {
        if ((int)$this->id == 0 || (int)$this->employee_id == 0) {
            throw new Exception('Contract not found');
        }
        $empLastInfo = $this->getContractLast($this->employee_id);
        if (!$empLastInfo || $empLastInfo->id == $this->id) {
            return true;
        }
        return FALSE;
    }

    public static function getContractByEmpId($empId)
    {
        return self::where('employee_id', $empId)->orderBy('start_at', 'DESC')->get();
    }


    /**
     * Lấy danh sách nhân viên nhận thông báo hợp đồng sắp hết hạn
     * Get all email receive notify contract expire date
     * @return array
     */
    public function getAllEmployeeReceiveNotify()
    {
        if (!$this->employee || !$this->employee->id) {
            // throw new Exception('Employee not found');
            return [];
        }
        $teamId = TeamMember::where('employee_id', $this->employee->id)->pluck('team_id')->toArray();
        // load danh sách nhân viên có quyền nhận notify và quyền quản lý hợp đồng
        $permission = new \Rikkei\Team\Model\Permission();
        $collection = $permission->getEmployeeByActionName('receive.notify.contract', $teamId);

        PermissionModel::addAllowAction($collection, 'manage.contract');

        return $collection->pluck('email')->toArray();
    }

    /**
     * Mốt thời gian xét hợp đồng hết hạn
     * @param type $datetime
     */
    public static function getAllContractExpireDate($startDate, $endDate)
    {
        $employeeTable = EmployeeModel::getTableName();
        $contractTable = self::getTableName();
        $collection = self::select([
            "$employeeTable.name",
            "$employeeTable.employee_code",
            "$contractTable.employee_id",
            "$employeeTable.email",
            "$contractTable.type",
            "$contractTable.start_at",
            "$contractTable.end_at",
            "$contractTable.team_id",
            "$contractTable.id",
        ])
            ->leftJoin("$employeeTable", "$employeeTable.id", '=', "$contractTable.employee_id")
            ->where("$contractTable.end_at", '>=', $startDate)
            ->where("$contractTable.end_at", '<=', $endDate)
            ->whereNotNull("$contractTable.end_at")
            ->whereNull("$contractTable.deleted_at")
            ->whereNull("$employeeTable.deleted_at")
            ->where(function ($sql) use ($employeeTable) {
                $sql->whereNull("$employeeTable.leave_date")
                    ->orWhere("$employeeTable.leave_date", '>=', date('Y-m-d 23:59:59'));
            });
        return $collection->get();
    }

    public static function pubToProfile($contract_id)
    {
        $contractInfo = self::getContractById($contract_id);
        if (!$contractInfo) {
            throw new Exception('Contract not found');
        }
        $employeeModel = $contractInfo->employee;
        if (!$employeeModel) {
            throw new Exception('Contract not found');
        }
        $employeeModel->updateContract($contractInfo);
    }

    /**
     * Export Contract
     *
     * @param $dataFilter
     * @param $type
     * @return array
     */
    public static function export($dataFilter, $type)
    {
        $result = self::getAllContract($dataFilter, $type, true);

        //Set header file excel
        $data[] = [
            trans('contract::vi.employee code'),
            trans('contract::vi.employee name'),
            trans('contract::vi.email'),
            trans('contract::vi.job'),
            trans('contract::vi.contract type'),
            trans('contract::vi.start at'),
            trans('contract::vi.end at')
        ];

        $allTypeContract = ContractModel::getAllTypeContract();
        if (!empty($result)) {
            foreach ($result as $item) {
                $data[] = [
                    $item->employee_code,
                    $item->employee_name,
                    $item->employee_email,
                    str_replace('<br/>', '', $item->employee_job),
                    array_get($allTypeContract, $item->contract_type) . self::getCountContractWorkingOff($item->employee_id, $item->id),
                    Carbon::parse($item->start_at)->format('Y-m-d'),
                    Carbon::parse($item->end_at)->format('Y-m-d'),
                ];
            }
        }

        return $data;
    }

    /**
     * [getEmpByContractType description]
     * @param  [collection] $timekeepingTable
     * @param  [array] $contractType
     * @return [collection]
     */
    public function getEmpByContractType($timekeepingTable, $contractType)
    {
        if (!$timekeepingTable || !count($contractType)) {
            return;
        }
        $tblEmp = Employee::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $tblContract = self::getTableName();
        $empWorkTbl = EmployeeWork::getTableName();

        $teamId = $timekeepingTable->team_id;
        $teamIds = [];
        $teamIds[] = (int) $teamId;
        ManageTimeCommon::getTeamChildRecursive($teamIds, $teamId);
        $teamHN = Team::select('id')->where('code', TeamConst::CODE_HANOI)->first();

        if ($teamHN && $teamHN->id == $teamId) {
            // Add team BOD and PQA
            $team = new Team();
            $teamIds = array_unique(array_merge($teamIds, $team->getTeamBODPQA()));
        }
        $teamIds = array_values($teamIds);
        $monthOfKeeping = $timekeepingTable->month;
        $yearOfKeeping = $timekeepingTable->year;
        $month = Carbon::parse($yearOfKeeping . '-' . $monthOfKeeping . '-01');

        return TeamMember::select(
            "{$tblEmp}.id as employee_id",
            "{$tblEmp}.offcial_date",
            "{$tblEmp}.trial_date",
            "{$tblEmp}.leave_date"
        )
        ->join("{$tblEmp}", "{$tblEmp}.id", "=", "{$tblTeamMember}.employee_id")
        ->join("{$empWorkTbl}", "{$tblEmp}.id", "=", "{$empWorkTbl}.employee_id")
        ->leftJoin("{$tblContract}", "{$tblEmp}.id", "=", "{$tblContract}.employee_id")
        ->whereIn("{$tblTeamMember}.team_id", $teamIds)
        ->where(function ($query) use ($tblEmp, $timekeepingTable, $month) {
            $query->whereNull("{$tblEmp}.leave_date")
                ->orWhereDate("{$tblEmp}.leave_date", '>=', $month->startOfMonth()->format('Y-m-d'));
        })
        ->whereNull("{$tblEmp}.deleted_at")
        ->whereDate("{$tblEmp}.join_date", "<=", $timekeepingTable->end_date)
        ->whereNotIn("{$tblEmp}.account_status", [getOptions::FAIL_CDD])
        ->whereIn("{$tblContract}.type", $contractType)
        ->whereDate("{$tblContract}.start_at", '<=', $timekeepingTable->end_date)
        ->whereDate("{$tblContract}.end_at", '>=', $timekeepingTable->start_date)
        ->whereNull("{$tblContract}.deleted_at")
        ->groupBy("{$tblEmp}.id")
        ->get();
    }
}
