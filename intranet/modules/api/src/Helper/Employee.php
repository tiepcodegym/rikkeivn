<?php

namespace Rikkei\Api\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Rikkei\Api\Helper\Base as BaseHelper;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\View\View as ProjectView;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Tag\Model\Tag;
use Rikkei\Tag\View\TagConst;
use Rikkei\Team\Model\Employee as EmployeeModel;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\View\View;
use Rikkei\Resource\Model\Dashboard;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Api\View\Utilization;
use Rikkei\Project\Model\Project;
use Rikkei\Resource\Model\Programs;
use Rikkei\Project\Model\ProjectProgramLang;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Project\Model\ProjectMemberProgramLang;
use Rikkei\Resource\View\getOptions;
use Exception;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\RecruitProcess;
use Rikkei\ManageTime\Model\LeaveDay;
use Rikkei\Core\Model\User;
use Rikkei\Team\Model\EmployeeContractHistory;
use Rikkei\Team\Model\Country;
use Rikkei\Team\Model\Role;
use Rikkei\Team\View\TeamList;
use Rikkei\ManageTime\Model\LeaveDayBack;
use Rikkei\ManageTime\View\LeaveDayPermission;
use Rikkei\ManageTime\Model\LeaveDayHistories;
use Rikkei\Core\View\CacheBase;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\PublishQueueToJob;
use Rikkei\Team\View\TeamConst;

class Employee extends BaseHelper
{
    const PREFIX_EMP_TABLE = 'emp';
    const PREFIX_EMP_WORK_TABLE = 'work';
    const PREFIX_EMP_CONTACT_TABLE = 'contact';
    const MAX_DATE = '9999-12-31';
    const CONCAT = ",,";
    const GROUP_CONCAT = ";;";

    public function __construct()
    {
        $this->model = EmployeeModel::class;
    }

    /**
     * get total employees
     * @param array $data
     * @return int
     */
    public function getTotal($data = [])
    {
        $date = !empty($data['date']) ? $data['date'] : Carbon::now()->toDateString();

        // not isset team_id => get total employees of company
        if (empty($data['team_id'])) {
            return EmployeeModel::whereDate("join_date", '<=', $date)
                ->where(function ($query) use ($date) {
                    $query->whereNull('leave_date')
                        ->orWhereDate("leave_date", '>=', $date);
                })
                ->count();
        }
        if (isset($data['include_child_team']) && (int)$data['include_child_team'] === 1) {
            $teamIds = Team::teamChildIds($data['team_id']);
        } else {
            $teamIds = [$data['team_id']];
        }

        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();
        $tblEmp = EmployeeModel::getTableName();
        $emps = EmployeeModel::join($tblEmpTeamHistory, function ($query) use ($tblEmp, $tblEmpTeamHistory, $date, $teamIds) {
            $query->on("{$tblEmpTeamHistory}.employee_id", '=', "{$tblEmp}.id")
                ->where(function ($query1) use ($tblEmpTeamHistory, $date) {
                    $query1->where("{$tblEmpTeamHistory}.end_at", '>=', $date)
                        ->orWhereNull("{$tblEmpTeamHistory}.end_at");
                })
                ->whereIn('team_id', $teamIds);
        })
            ->whereDate("{$tblEmpTeamHistory}.start_at", '<=', $date)
            ->whereDate("{$tblEmp}.join_date", '<=', $date)
            ->where(function ($query) use ($tblEmp, $date) {
                $query->whereDate("{$tblEmp}.leave_date", '>=', $date)
                    ->orWhereNull("{$tblEmp}.leave_date");
            })
            ->groupBy("{$tblEmp}.id")
            ->get();

        return $emps->count();
    }

    /**
     * get employee info
     * @param array $data
     * @return mixed
     */
    public function getInfo($data = [])
    {
        if (empty($data['employee_id'])) {
            return (object)[];
        }

        $tblEmp = EmployeeModel::getTableName();
        $tblEmpWork = EmployeeWork::getTableName();
        $tblEmpContact = EmployeeContact::getTableName();
        $prefixes = [
            self::PREFIX_EMP_TABLE,
            self::PREFIX_EMP_WORK_TABLE,
            self::PREFIX_EMP_CONTACT_TABLE,
        ];
        $tables = [
            self::PREFIX_EMP_TABLE => $tblEmp,
            self::PREFIX_EMP_WORK_TABLE => $tblEmpWork,
            self::PREFIX_EMP_CONTACT_TABLE => $tblEmpContact,
        ];
        $columns = [];
        $hasTables = [];

        if (empty($data['fields']) || !is_array($data['fields'])) {
            $selectedFields = [
                "{$tblEmp}.name",
                "{$tblEmp}.email",
                "{$tblEmp}.gender",
            ];
        } else {
            $selectedFields = [];
            foreach ($data['fields'] as $field) {
                $fieldExplode = explode('.', $field);
                // format prefix.suffix
                if (count($fieldExplode) === 2) {
                    list($prefix, $suffix) = $fieldExplode;
                } elseif (count($fieldExplode) === 1) { // field from table employees
                    $prefix = self::PREFIX_EMP_TABLE;
                    $suffix = $fieldExplode[0];
                } else {
                    continue;
                }
                // prefix not match prefixes
                if (!in_array($prefix, $prefixes)) {
                    continue;
                }

                if (!isset($columns[$prefix])) {
                    $columns[$prefix] = Schema::getColumnListing($tables[$prefix]);
                }
                // suffix is not the same as the field in table
                if (!in_array($suffix, $columns[$prefix])) {
                    continue;
                }

                $hasTables[$prefix] = true;
                $selectedFields[] = "{$tables[$prefix]}.{$suffix}";
            }
        }

        // not select field
        if (empty($selectedFields)) {
            return (object)[];
        }

        $collection = EmployeeModel::where("{$tblEmp}.id", $data['employee_id']);
        if (!empty($hasTables[self::PREFIX_EMP_CONTACT_TABLE])) {
            $collection->leftJoin($tblEmpContact, "{$tblEmpContact}.employee_id", '=', "{$tblEmp}.id");
        }
        if (!empty($hasTables[self::PREFIX_EMP_WORK_TABLE])) {
            $collection->leftJoin($tblEmpWork, "{$tblEmpWork}.employee_id", '=', "{$tblEmp}.id");
        }
        $info = $collection->select($selectedFields)
            ->first();
        return $info;
    }

    /*
     * get all skills of all employees
     */
    public function getSkills()
    {
        $tblEmp = EmployeeModel::getTableName();
        $tblEmpSkill = EmployeeSkill::getTableName();
        $tblTag = Tag::getTableName();
        $mapKeys = [
            'language' => 'programming_language',
            'frame' => 'framework_ide',
            'database' => 'database',
            'os' => 'os',
        ];

        $employees = EmployeeModel::leftJoin($tblEmpSkill, function ($q) use ($tblEmpSkill, $tblEmp) {
            $q->on("{$tblEmpSkill}.employee_id", '=', "{$tblEmp}.id")
                ->whereIn("{$tblEmpSkill}.type", ['language', 'database', 'frame', 'os']);
        })
            ->leftJoin($tblTag, function ($q) use ($tblTag, $tblEmpSkill) {
                $q->on("{$tblTag}.id", '=', "{$tblEmpSkill}.tag_id")
                    ->whereNull("{$tblTag}.deleted_at")
                    ->where("{$tblTag}.status", '=', TagConst::TAG_STATUS_APPROVE);
            })
            ->where(function ($q) use ($tblEmp) {
                $q->whereNull("{$tblEmp}.leave_date")
                    ->orWhereDate("{$tblEmp}.leave_date", '>=', Carbon::now()->toDateString());
            })
            ->select([
                "{$tblEmp}.name",
                "{$tblEmp}.email",
                "{$tblEmpSkill}.type",
                "{$tblEmpSkill}.exp_y",
                "{$tblEmpSkill}.exp_m",
                "{$tblTag}.value",
            ])
            ->get();
        $empSkills = [];
        foreach ($employees as $emp) {
            if (!isset($empSkills[$emp->email])) {
                $empSkills[$emp->email] = [
                    'name' => $emp->name,
                    $mapKeys['language'] => [],
                    $mapKeys['frame'] => [],
                    $mapKeys['database'] => [],
                    $mapKeys['os'] => [],
                ];
            }
            if ($emp->type !== null && $emp->value !== null) {
                $empSkills[$emp->email][$mapKeys[$emp->type]][] = [
                    'name' => $emp->value,
                    'experience' => ($emp->exp_y + round($emp->exp_m / 12, 2)) . ' ' . Lang::get('api::view.years'),
                ];
            }
        }
        return $empSkills;
    }

    /**
     * get list employee (if choose project then show member in project)
     *
     * @param array $filter
     * @return Collection
     */
    public function getListEmp($filter = [])
    {
        $tblEmp = EmployeeModel::getTableName();
        $tblPM = ProjectMember::getTableName();

        $selectedFields = [
            "{$tblEmp}.id",
            "{$tblEmp}.name",
            "{$tblEmp}.email",
        ];

        $filterStatus = isset($filter['status']) ? $filter['status'] : null;
        $filterProject = isset($filter['project_ids']) ? $filter['project_ids'] : null;
        $branch = isset($filter['branch']) ? $filter['branch'] : null;
        $month = isset($filter['month']) ? $filter['month'] . '-01' : null;

        $collection = EmployeeModel::select($selectedFields);

        if ($filterStatus) {
            $currentDay = date("Y-m-d");

            if ($filterStatus == Team::WORKING) {
                $collection->where(function ($query) use ($tblEmp, $currentDay) {
                    $query->orWhereDate("{$tblEmp}.leave_date", ">=", $currentDay)
                        ->orWhereNull("{$tblEmp}.leave_date");
                });
            } else {
                $collection->where(function ($query) use ($tblEmp, $currentDay) {
                    $query->whereNotNull("{$tblEmp}.leave_date")
                        ->whereDate("{$tblEmp}.leave_date", "<", $currentDay);
                });
            }
        }
        if (!empty($filterProject)) {
            $strFilterProject = implode(",", $filterProject);
            $collection->join(DB::raw("(SELECT * FROM $tblPM WHERE project_id IN ($strFilterProject) AND deleted_at IS NULL) as projMember"), "{$tblEmp}.id", "=", "projMember.employee_id");
        }

        if (!empty($filter['keyword'])) {
            $keyword = $filter['keyword'];
            $collection->where(function ($query) use ($keyword) {
                $query->orWhere('employees.email', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('employees.name', 'LIKE', '%' . $keyword . '%');
            });
        }

        if (!empty($branch)) {
            $now = Carbon::now()->toDateString();
            $collection->join('employee_team_history', 'employee_team_history.employee_id', '=', "{$tblEmp}.id")
                ->join('teams', 'teams.id', '=', 'employee_team_history.team_id')
                ->where('teams.branch_code', $branch)
                ->where('employee_team_history.is_working', EmployeeTeamHistory::IS_WORKING)
                ->whereNull('employee_team_history.deleted_at')
                ->where(function ($query) use ($now) {
                    $query->whereDate('employee_team_history.end_at', '>=', $now)
                        ->orWhereNull('employee_team_history.end_at');
                });
        }

        if (!empty($month)) {
            $filterMonth = Carbon::parse($month)->lastOfMonth()->toDateString();
            $collection->where(function ($query) use ($tblEmp, $filterMonth) {
                $query->whereNull("{$tblEmp}.leave_date")
                    ->orWhereDate("{$tblEmp}.leave_date", "<=", $filterMonth);
            });
        }

        return $collection
            ->groupBy("{$tblEmp}.id")
            ->orderBy("{$tblEmp}.id")
            ->get();
    }

    /**
     * get info employee
     * team và branch_code lấy theo hiện tại chứ ko lấy theo mức độ ưu tiên
     *
     * @param string $email
     * @return void
     */
    public function getInfoFull($email)
    {
        if (!$email) {
            return null;
        }
        return EmployeeModel::select(
            'employees.id',
            'employees.name',
            'employees.email',
            'employees.birthday',
            'employees.join_date',
            'contact.mobile_phone',
            'contact.skype',
            'user.avatar_url',
            'team.id as team_id',
            'team.name as team_name',
            'team.branch_code as team_branch_code'
            
        )
            ->leftJoin('users as user', 'user.employee_id', '=', 'employees.id')
            ->leftJoin('employee_team_history as empTeamHis', 'empTeamHis.employee_id', '=', 'employees.id')
            ->leftJoin('employee_contact as contact', 'contact.employee_id', '=', 'employees.id')
            ->leftJoin('teams as team', 'empTeamHis.team_id', '=', 'team.id')
            ->where('user.email', '=', $email)
            ->where('empTeamHis.is_working', 1) // 1 là team hiện tại
            ->whereNull('empTeamHis.deleted_at')
            ->where(function ($query) {
                $query->WhereDate("empTeamHis.end_at", '>=', Carbon::now()->format('Y-m-d'))
                    ->orWhereNull("empTeamHis.end_at");
            })
            ->first();
    }

    /**
     * get info employee
     * team và branch_code lấy theo hiện tại chứ ko lấy theo mức độ ưu tiên
     *
     * @param array $emails
     * @return void
     */
    public function getInfoFullList($emails)
    {
        if (!$emails) {
            return [];
        }
        return EmployeeModel::select(
            'employees.id',
            'employees.name',
            'employees.email',
            'employees.birthday',
            'contact.mobile_phone',
            'contact.skype',
            'user.avatar_url',
            'team.id as team_id',
            'team.name as team_name',
            'team.branch_code as team_branch_code'
        )
            ->join('users as user', 'user.employee_id', '=', 'employees.id')
            ->leftJoin('employee_team_history as empTeamHis', 'empTeamHis.employee_id', '=', 'employees.id')
            ->leftJoin('employee_contact as contact', 'contact.employee_id', '=', 'employees.id')
            ->leftJoin('teams as team', 'empTeamHis.team_id', '=', 'team.id')
            ->whereIn('user.email', $emails)
            ->where('empTeamHis.is_working', 1) // 1 là team hiện tại
            ->whereNull('empTeamHis.deleted_at')
            ->where(function ($query) {
                $query->WhereDate("empTeamHis.end_at", '>=', Carbon::now()->format('Y-m-d'))
                    ->orWhereNull("empTeamHis.end_at");
            })
            ->groupBy('employees.id')
            ->get();
    }

    /**
     * get employees in Japan have working times (exist overlap date) with period time
     * @param array $params {contain month from and month to}
     * @return array
     */
    public function listEmployeesOnsiteJapan($params)
    {
        $params['month_from'] = "{$params['month_from']}-01";
        $params['month_to'] = date('Y-m-t', strtotime($params['month_to']));
        $tblTeam = Team::getTableName();
        $tblEmp = EmployeeModel::getTableName();
        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();
        // get all employees has branch code is 'japan'
        $collection = EmployeeModel::join($tblEmpTeamHistory, "{$tblEmpTeamHistory}.employee_id", '=', "{$tblEmp}.id")
            ->join($tblTeam, "{$tblTeam}.id", '=', "{$tblEmpTeamHistory}.team_id")
            ->where("{$tblTeam}.branch_code", Team::CODE_PREFIX_JP)
            ->where("{$tblTeam}.is_soft_dev", 1)
            ->whereNull("{$tblTeam}.deleted_at")
            ->whereNull("{$tblEmpTeamHistory}.deleted_at")
            ->whereDate("{$tblEmpTeamHistory}.start_at", '<=', $params['month_to'])
            ->where(function ($query) use ($tblEmpTeamHistory, $params) {
                $query->whereDate("{$tblEmpTeamHistory}.end_at", '>=', $params['month_from'])
                    ->orWhereNull("{$tblEmpTeamHistory}.end_at");
            });
        if (!empty($params['team_id'])) {
            if (is_array($params['team_id'])) {
                $collection->whereIn("{$tblTeam}.id", $params['team_id']);
            }
            else {
                $teamChildIds = Team::teamChildIds([$params['team_id']]);
                $collection->whereIn("{$tblTeam}.id", $teamChildIds);
            }
        }
        $collection->select([
            "{$tblEmp}.email",
            "{$tblEmpTeamHistory}.team_id",
            DB::raw("DATE({$tblEmpTeamHistory}.start_at) AS start_at"),
            DB::raw("DATE({$tblEmpTeamHistory}.end_at) AS end_at"),
        ])
            ->orderBy('start_at', 'ASC');
        $data = [];
        foreach ($collection->get() as $item) {
            $data[$item->email][$item->team_id][] = [
                'start_at' => $item->start_at,
                'end_at' => isset($item->end_at) ? $item->end_at : self::MAX_DATE,
            ];
        }
        return $data;
    }

    public function listEmployeesOnsiteInMonth($params)
    {
        $month = $params['month'];
        $year = $params['year'];
        $timeStart = $year.'-'.$month.'-01';
        $timeEnd = new Carbon($timeStart);
        $timeEnd = $timeEnd->endOfMonth()->toDateString();

        $onsites = DB::table('business_trip_registers')
            ->select(
                'business_trip_employees.employee_id as employee_id',
                'employees.name',
                'employees.employee_code',
                DB::raw("GROUP_CONCAT(DATE_FORMAT(business_trip_registers.date_start, '%Y-%m-%d') SEPARATOR ',') AS date_start"),
                DB::raw("GROUP_CONCAT(DATE_FORMAT(business_trip_registers.date_end, '%Y-%m-%d') SEPARATOR ',') AS date_end")
            )
            ->join('business_trip_employees', 'business_trip_employees.register_id', '=', "business_trip_registers.id")
            ->join('employees', 'employees.id', '=', "business_trip_employees.employee_id")
            ->where('status', BusinessTripRegister::STATUS_APPROVED)
            ->whereDate("business_trip_registers.date_start", '<=', $timeEnd)
            ->whereDate("business_trip_registers.date_end", '>=', $timeStart)
            ->whereNull("business_trip_registers.deleted_at")
            // ->groupBy("business_trip_employees.register_id")
            ->groupBy("business_trip_employees.employee_id")
            ->get();

        foreach ($onsites as $key => $onsite) {
            $dateStarts = explode(',', $onsite->date_start);
            $dateEnds = explode(',', $onsite->date_end);
            $time = [];
            foreach ($dateStarts as $key => $item) {
                $value = [
                    'from_date' => $dateStarts[$key],
                    'to_date' => $dateEnds[$key],
                ];
                $time[] = $value;
            }
            $onsite->onsites = $time;
            unset($onsite->date_start);
            unset($onsite->date_end);
        }

        return [
            'count' => count($onsites),
            'details' => $onsites
        ];
    }

    public function listEmployeesOnsiteVietNam($filter = [])
    {
        $data = [];
        $monthFromRequest = $filter['month_from'];
        $monthToRequest = $filter['month_to'];
        $monthFrom = explode("-", $monthFromRequest);
        $monthTo = explode("-", $monthToRequest);
        $year = $monthFrom[0];
        $dateFormat = 'Y-m-d';

        for ($month = $monthFrom[1]; $month <= $monthTo[1]; $month++) {
            $monthOfYear = $year . "-" . sprintf("%02d", $month);
            $employeeData = [];
            $employeeMonth = [];
            $data[$monthOfYear] = $employeeMonth;

            // Get date working
            $firstAndLastDayOfMonth = ResourceView::getInStance()->getFirstLastDaysOfMonth($month, $year);

            $dateOfWorkInMonth = ProjectView::getMM($firstAndLastDayOfMonth[0], $firstAndLastDayOfMonth[1], 2);

            // Get employee VietNam onsite
            $bussinessTripRegisters = $this->_getEmployeeBussinessTripRegisterByMonth($monthOfYear);

            foreach ($bussinessTripRegisters as $v) {

                // Total work days in month of employee
                $dateOfOnsiteInMonth = ResourceView::getInStance()->getRealDaysOfMonth($month, $year, $v['onsite_start_at'], $v['onsite_end_at']);
                if ($dateOfOnsiteInMonth > 0) {
                    $onsiteRatioInMonth = number_format($dateOfOnsiteInMonth / $dateOfWorkInMonth, 2, '.', '');
                    $key = $v['id'] . '-' . $v['team_id'];
                    if (array_key_exists($key, $employeeMonth)) {
                        $onsiteRatioInMonth = $onsiteRatioInMonth + $employeeMonth[$key]['employee_onsite'];
                    }

                    $employeeMonth[$key] = [
                        "employee_id" => $v['id'],
                        "employee_name" => $v['name'],
                        "employee_email" => $v['email'],
                        "employee_onsite" => (float)$onsiteRatioInMonth,
                        "onsite_start_at" => Carbon::parse($v['onsite_start_at'])->format($dateFormat),
                        "onsite_end_at" => Carbon::parse($v['onsite_end_at'])->format($dateFormat),
                        "team_id" => (int)$v['team_id']
                    ];
                }
            }
            foreach ($employeeMonth as $employeeItem) {
                $employeeData[] = $employeeItem;
            }
            // Add data
            $data[$monthOfYear] = $employeeData;
        }

        return $data;
    }

    private function _getEmployeeBussinessTripRegisterByMonth($monthFrom)
    {
        $dateFormat = '%Y-%m';
        $tblEmp = EmployeeModel::getTableName();
        $tblTeam = Team::getTableName();
        $tblBusinessTripRegisters = BusinessTripRegister::getTableName();
        $tblBusinessTripEmployees = BusinessTripEmployee::getTableName();
        $data = EmployeeModel::join($tblBusinessTripEmployees, "{$tblBusinessTripEmployees}.employee_id", '=', "{$tblEmp}.id")
            ->join($tblBusinessTripRegisters, "{$tblBusinessTripRegisters}.id", '=', "{$tblBusinessTripEmployees}.register_id")
            ->join($tblTeam, "{$tblTeam}.id", '=', "{$tblBusinessTripEmployees}.team_id")
            ->where("{$tblTeam}.branch_code", '!=', TEAM::CODE_PREFIX_JP)
            ->where("{$tblBusinessTripRegisters}.status", '=', BusinessTripRegister::STATUS_APPROVED)
            ->whereNull("{$tblBusinessTripRegisters}.deleted_at")
            ->whereNull("{$tblBusinessTripRegisters}.parent_id")
            ->whereRaw(DB::raw("date_format(start_at, '{$dateFormat}') <= '{$monthFrom}'"))
            ->whereRaw(DB::raw("date_format(end_at, '{$dateFormat}') >='{$monthFrom}'"))
            ->select([
                "{$tblEmp}.id", "{$tblEmp}.name", "{$tblEmp}.email", "{$tblTeam}.name as team_name",
                "{$tblBusinessTripEmployees}.start_at as onsite_start_at", "{$tblBusinessTripEmployees}.end_at as onsite_end_at", "{$tblBusinessTripEmployees}.team_id as team_id"
            ])->get()->toArray();

        return $data;
    }

    public function utilization($filter)
    {
        $filter['limit'] = (isset($filter['limit']) && $filter['limit'] != null) ? $filter['limit'] : 50;
        $filter['page'] = (isset($filter['page']) && $filter['page'] != null) ? $filter['page'] : 1;
        $filter['viewMode'] = (isset($filter['viewMode']) && $filter['viewMode'] != null) ? $filter['viewMode'] : 'week';
        // $filter['startDate'] = (isset($filter['startDate']) && $filter['startDate'] != null) ? $filter['startDate'] : View::getInstance()->setDefautDateFilter()[0];
        // $filter['endDate'] = (isset($filter['endDate']) && $filter['endDate']  != null) ? $filter['endDate'] : View::getInstance()->setDefautDateFilter()[1];
        $filter['effort'] = (isset($filter['effort']) && $filter['effort'] != null) ? $filter['effort'] : 0;
        $updated_from = isset($filter['updated_from']) ? $filter['updated_from'] : '';
        $updated_to = isset($filter['updated_to']) ? $filter['updated_to'] : '';
        $filterEmpIds = (isset($filter['empIds']) && !empty($filter['empIds'])) ? $filter['empIds'] : [];

        //get employee updated
        if ($updated_from || $updated_to) {
            $empIds = $this->_getEmpUpdated($updated_from, $updated_to, $filterEmpIds);
            if (empty($empIds)) {
                $empIds[] = -1;
            }
            $filter['empIds'] = $empIds;
        }

        $dashboard = $this->_getDashboard($filter);

        $utilizationView = new Utilization();
        $dataView = $utilizationView->getDataForView($dashboard['dashboard'], $filter);
        
        return $dataView;
    }

    private function _getEmpUpdated($updated_from, $updated_to, $empIds)
    {
        $employees = ProjectMember::select('id', 'employee_id', 'updated_at')
            ->when($empIds, function ($query) use ($empIds) {
                return $query->whereIn('employee_id', $empIds);
            })->when($updated_from, function ($query) use ($updated_from) {
                return $query->whereDate('updated_at', '>=', $updated_from);
            })->when($updated_to, function ($query) use ($updated_to) {
                return $query->whereDate('updated_at', '<=', $updated_to);
            })->get()->pluck('employee_id')->toArray();

        return array_unique($employees);
    }

    private function _getDashboard($filter)
    {
        $projId = isset($filter['projId']) ? $filter['projId'] : '';
        $projStatus = isset($filter['projStatus']) ? $filter['projStatus'] : '';
        $programs = isset($filter['programs']) ? $filter['programs'] : null;
        $teamId = isset($filter['teamId']) ? $filter['teamId'] : '';
        $empFilter = isset($filter['empIds']) ? $filter['empIds'] : '';
        // $updated_from = isset($filter['updated_from']) ? $filter['updated_from'] : '';
        // $updated_to = isset($filter['updated_to']) ? $filter['updated_to'] : '';
        // $startDate = $filter['startDate'];
        // $endDate = $filter['endDate'];
        $limit = $filter['limit'];
        $page = $filter['page'];

        $result = $this->_empDashboard($projId, $projStatus, $teamId, null, null, $empFilter, $programs, $filter['viewMode']);
        $result = $result->withTrashed()->get();

        $dashboard = [];
        $nicknames = [];
        $today = date('Y-m-d');
        foreach ($result as $item) {
            if (!empty($item->cols)) {
                $rows = explode(Dashboard::GROUP_CONCAT, $item->cols);
                if (count($rows)) {
                    foreach ($rows as $row) {
                        $proj = explode(Dashboard::CONCAT, $row);
                        if (!isset($proj[0]) || !$proj[0] || !isset($proj[1]) || !$proj[1]) {
                            continue;
                        }
                        $start = $this->_getWeek($proj[0]);
                        $end = $this->_getWeek($proj[1]);
                        $dashboard[$item->email.CoreModel::GROUP_CONCAT.$item->id.CoreModel::GROUP_CONCAT.$item->leave_date.CoreModel::GROUP_CONCAT.$item->join_date.CoreModel::GROUP_CONCAT.$item->team][] = 
                        [
                            'proj_id' => isset($proj[4]) ? $proj[4] : '',
                            'proj_name' => isset($proj[3]) ? $proj[3] : '',
                            'start' => $start,
                            'end' => $end,
                            'start_date' => date('Y-m-d', strtotime($proj[0])),
                            'end_date' => date('Y-m-d', strtotime($proj[1])),
                            'start_year' => date('Y', strtotime($proj[0])),
                            'end_year' => date('Y', strtotime($proj[1])),
                            'effort' => isset($proj[2]) ? $proj[2] : 0,
                            // 'deleted' => isset($proj[5]) ? $proj[5] : 0,
                        ];
                    }
                }
            } else {
                $dashboard[$item->email.CoreModel::GROUP_CONCAT.$item->id.CoreModel::GROUP_CONCAT.$item->leave_date.CoreModel::GROUP_CONCAT.$item->join_date.CoreModel::GROUP_CONCAT.$item->team][] = 
                [
                    'proj_id' => null,
                    'proj_name' => null,
                    'start' => null,
                    'end' => null,
                    'start_date' => null,
                    'end_date' => null,
                    'start_year' => null,
                    'end_year' => null,
                    'effort'    => null,
                    // 'deleted'    => null
                ];
            }
        }

        return [
            "dashboard" => $dashboard,
            "result" => $result
        ];
    }

    public function _empDashboard(
            // $startMonthFilter,
            // $endMonthFilter,
            $projId = null,
            $projStatus = null,
            $teamIds = null,
            $empId = null,
            $teamsOfEmp = null,
            $empFilter = null,
            $programs = null,
            $viewMode = 'week'
    )
    {
        $empTable = EmployeeModel::getTableName();
        $projMemTable = ProjectMember::getTableName();
        $projTable = Project::getTableName();
        $programTable = Programs::getTableName();
        $projectProgramLangTable = ProjectProgramLang::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $teamProjTable = TeamProject::getTableName();
        $teamTable = Team::getTableName();
        $employeeTeamHistoryTbl = EmployeeTeamHistory::getTableName();
        $employeeSkill = EmployeeSkill::getTableName();
        $tagTable = Tag::getTableName();
        $projectMemberProgramLangTable = ProjectMemberProgramLang::getTableName();
        $concat = self::CONCAT;
        $groupConcat = self::GROUP_CONCAT;

        // $dateFilter = self::getStartEndFilter($startMonthFilter,$endMonthFilter);
        // $startAt = $dateFilter[0];
        // $endAt = $dateFilter[1];
        //Get actual date filter
        $where = " WHERE $projMemTable.status = ? AND $projMemTable.deleted_at IS NULL";
        $data = [ProjectMember::STATUS_APPROVED];
        // $whereFilterDate = " AND (($projMemTable.start_at >= ? AND $projMemTable.start_at <= ?) OR ($projMemTable.end_at >= ? AND $projMemTable.end_at <= ?) OR ($projMemTable.start_at <= ? AND $projMemTable.end_at >= ?))";
        // $where .= $whereFilterDate;
        // $filterDate = [$startAt, $endAt, $startAt, $endAt, $startAt, $endAt];
        // $data = array_merge($data, $filterDate);

        if ($projId) {
        $where .= " AND $projMemTable.project_id = ?";
        $data[] = $projId;
        }

        if ($projStatus) {
            $where .= " AND pj.status = ?";
            $data[] = $projStatus;
        }

        // if ($updated_from) {
        //     $where .= " AND DATE($projMemTable.updated_at) >= ?";
        //     $data[] = $updated_from;
        // }
        // if ($updated_to) {
        //     $where .= " AND DATE($projMemTable.updated_at) <= ?";
        //     $data[] = $updated_to;
        // }
        /** 
         * End filter table2 
         */

        $result = EmployeeModel::select(
                    "{$empTable}.id",
                    "{$empTable}.email",
                    "{$empTable}.leave_date",
                    "{$empTable}.join_date",
                    DB::raw("(SELECT group_concat(DISTINCT {$teamTable}.name ORDER BY {$teamTable}.name ASC SEPARATOR ', ') FROM {$teamTable} inner join {$teamMemberTable} on {$teamTable}.id =  {$teamMemberTable}.team_id WHERE {$teamMemberTable}.employee_id = {$empTable}.id) AS team"),
                    "table2.cols");
        $result->leftJoin(DB::raw("(SELECT e.email,
        GROUP_CONCAT(concat($projMemTable.start_at,'$concat', $projMemTable.end_at, '$concat', $projMemTable.effort, '$concat', pj.name, '$concat', pj.id) SEPARATOR '$groupConcat') as cols,
                        $projMemTable.status,
                        GROUP_CONCAT(concat( $projMemTable.project_id ) SEPARATOR ',') as project_ids
                FROM $empTable e INNER JOIN $projMemTable ON e.id = $projMemTable.employee_id
                INNER JOIN $projTable pj ON $projMemTable.project_id = pj.id
                $where group by e.id) AS table2"), "{$empTable}.email", "=", "table2.email");
        $result->leftJoin("{$employeeTeamHistoryTbl}", "{$employeeTeamHistoryTbl}.employee_Id", "=", "{$empTable}.id");
        // $result->whereRaw("(DATE({$employeeTeamHistoryTbl}.start_at) <= DATE(?) or {$employeeTeamHistoryTbl}.start_at is null) and (DATE({$employeeTeamHistoryTbl}.end_at) >= DATE(?) or {$employeeTeamHistoryTbl}.end_at is null)");
        // $data = array_merge($data, [$endAt, $startAt]);
        /**
         * Filter by project
         * Show all employee of project (contains leave job)
         */
        if ($projId && !empty($projId)) { 
            $result->whereRaw("FIND_IN_SET(?,table2.project_ids)");
            $data[] = $projId;
        }

        // if ($updated_from || $updated_to) {
        //     $result->leftJoin("{$projMemTable}", "{$projMemTable}.employee_Id", "=", "{$empTable}.id");
        //     if ($updated_from) {
        //         $result->whereDate("{$projMemTable}.updated_at", '>=', $updated_from);
        //         $data[] = $updated_from;
        //     }
        //     if ($updated_to) {
        //         $result->whereDate("{$projMemTable}.updated_at", '<=', $updated_from);
        //         $data[] = $updated_to;
        //     }
        // }
        
        /** 
         * Filter by team
         * Show employee of team
         */
        if ($teamIds) {
            $teamSysIds = Team::where('type', Team::TEAM_TYPE_SYSTENA)->find($teamIds)->pluck('id')->toArray();
            $teamNotSysIds = Team::where('type', '!=', Team::TEAM_TYPE_SYSTENA)->find($teamIds)->pluck('id')->toArray();
            if ($teamSysIds) {
                $result->join("{$projMemTable}", "{$projMemTable}.employee_Id", "=", "{$empTable}.id");
                $result->join("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$projMemTable}.project_id");
            }

            $result->where(function ($q) use ($teamSysIds, $employeeTeamHistoryTbl, $teamNotSysIds) {
                if ($teamSysIds) {
                    // $strTeamIds = '(' . implode($teamSysIds, ', ') . ')';
                    // $q->orWhere(function ($q1) use ($employeeTeamHistoryTbl, $strTeamIds, $teamProjTable, $whereFilterDate, $filterDate, &$data) {
                    //     $q1->whereRaw("{$employeeTeamHistoryTbl}.team_id IN $strTeamIds")
                    //         ->orWhereRaw("{$teamProjTable}.team_id IN $strTeamIds" . $whereFilterDate);
                    //     $data = array_merge($data, $filterDate);
                    // });
                }
                if ($teamNotSysIds) {
                    $q->orWhereRaw("{$employeeTeamHistoryTbl}.team_id IN (" . implode(',', $teamNotSysIds) . ')');
                }
            });
        }

        /**
         * Filter by employee
         */
        if ($empFilter) {
            $result->whereIn("{$empTable}.id", $empFilter);
            $data[] = $empFilter;
            // if (ctype_digit($empFilter)) {
            //     $result->where("{$empTable}.id", $empFilter);
            //     $data[] = $empFilter;
            // } else { 
            //     $result->where(function ($query) use ($empFilter, $empTable) {
            //         $query->whereRaw("{$empTable}.email like  '%$empFilter%'")
            //             ->orWhereRaw("{$empTable}.name like  '%$empFilter%'");
            //     });
            // }
        }

        /** 
         * Filter by permission
         * Scope self
         */
        // if ($empId) { 
        //     $result->where("{$empTable}.id", $empId);
        //     $data[] = $empId;
        // }

        /**
         * Filter by permission
         * Scope team
         * Show employee of team and employee join project of team
         * If scope is team but filter by project then show all employees of project
         */
        if ($teamsOfEmp  && !$projId) {
            $teamSysIds = [];
            foreach ($teamsOfEmp as $teamOfEmpId) {
                $team = Team::getTeamById($teamOfEmpId);
                if ($team->type == Team::TEAM_TYPE_SYSTENA) {
                    $teamSysIds[] = $team->id;
                }
            }
            if (count($teamSysIds)) {
                // if (empty($teamIds)) {
                //     $result->leftJoin("{$projMemTable}", "{$projMemTable}.employee_Id", "=", "{$empTable}.id");
                //     $result->leftJoin("{$teamProjTable}", "{$teamProjTable}.project_id", "=", "{$projMemTable}.project_id");
                //     $result->where(function ($query) use ($teamsOfEmp, $teamSysIds, $employeeTeamHistoryTbl, $teamProjTable, $whereFilterDate) {
                //         $query->whereIn("{$employeeTeamHistoryTbl}.team_id", $teamsOfEmp)
                //             ->orWhereRaw("{$teamProjTable}.team_id IN (?)" . $whereFilterDate);
                //     });
                //     $data[] = $teamsOfEmp;
                //     $data[] = $teamSysIds;
                //     $data = array_merge($data, $filterDate);
                // }
            } else {
                $result->whereIn("{$employeeTeamHistoryTbl}.team_id", $teamsOfEmp);
                $data[] = $teamsOfEmp;
            }
        }

        // Filter employee leave job. Only show if start date filter <= leave date
        // $result->where(function ($query) use ($empTable, $startMonthFilter, $viewMode) {
        //     $query->whereNull("{$empTable}.leave_date");
        //     if ($viewMode === 'day') {
        //         $query->orWhereDate("{$empTable}.leave_date", '>=', $startMonthFilter);
        //     } elseif ($viewMode === 'month') {
        //         $query->orWhereRaw("MONTH({$empTable}.leave_date) >= MONTH(?)", [$startMonthFilter]);
        //     } else {
        //         $query->orWhereRaw("WEEK({$empTable}.leave_date) >= WEEK(?)", [$startMonthFilter]);
        //     }
            
        // });
        // $data[] = $startMonthFilter;

        //Show only team is software development
        $result->leftJoin("{$teamTable}", "{$employeeTeamHistoryTbl}.team_id" , "=", "{$teamTable}.id"); 
        $result->where("{$teamTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT);
        $data[] = Team::IS_SOFT_DEVELOPMENT;
        // $result->whereRaw("DATE(employees.join_date) <= DATE(?)", [$endAt]);
        // $data[] = $endAt;

        //Not show employee has working_type is WORKING_INTERNSHIP
        $result->where("{$empTable}.working_type", "!=", getOptions::WORKING_INTERNSHIP);
        $data[] = getOptions::WORKING_INTERNSHIP;

        $result->setBindings($data);
        $result->groupBy('employees.email', 'cols');
        $result->orderBy('employees.email', 'asc');

        //Filter by programming language follow skillsheet and project.
        if ($programs) {
            $result->leftJoin($employeeSkill, "{$employeeSkill}.employee_id", '=', "{$empTable}.id")
                    ->leftJoin($tagTable, "{$tagTable}.id", '=', "{$employeeSkill}.tag_id")
                    ->leftJoin("{$projMemTable}", "{$projMemTable}.employee_id", '=', "{$empTable}.id")
                    ->leftJoin("{$projectMemberProgramLangTable}", "{$projMemTable}.id", '=', "{$projectMemberProgramLangTable}.proj_member_id")
                    ->leftJoin("{$programTable}", "{$projectMemberProgramLangTable}.prog_lang_id", "=", "{$programTable}.id");

            $result->where(function ($query) use ($programTable, $programs, $tagTable) {
                    $query->whereIn("{$tagTable}.value", $programs)
                        ->orwhereIn("{$programTable}.name", $programs);
            });
        }
        return $result;
    }

    private static function getStartEndFilter($startMonthFilter, $endMonthFilter)
    {
        $startMonth = explode('-',$startMonthFilter)[1];
        $startYear = explode('-',$startMonthFilter)[0];
        $endMonth = explode('-',$endMonthFilter)[1];
        $endYear = explode('-',$endMonthFilter)[0];

        $firstDayOfStartMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($startMonth,$startYear)[0];
        $endDayOfEndMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($endMonth,$endYear)[1];

        $startAt = ResourceView::getInstance()->getFirstDayOfWeek($firstDayOfStartMonth);
        $endAt = ResourceView::getInstance()->getLastDayOfWeek($endDayOfEndMonth);
        return [$startAt,$endAt];
    }

    /**
     * Get week number of time
     * @param string|datetime $time
     * @return int week number
     */
    private function _getWeek($time)
    {
        $w=(int)date('W', strtotime($time));
        $m=(int)date('n', strtotime($time));
        $w=$w==1?($m==12?53:1):($w>=51?($m==1?0:$w):$w);
        return $w;
    }

    public function updateResignation($filter) {
        $employee = EmployeeModel::where('email', $filter['employee_email'])->first();
        if ($employee) {
            $employee->update([
                'leave_date' => $filter['leave_date']
            ]);
        }
    }

    public function createEmployee($params, $empDeleted = null) {
        DB::beginTransaction();
        try {
            if (isset($params['country']) && $params['country']) {
                $country = Country::where('country_code', $params['country'])->first();
                if (!$country) {
                    throw new Exception('The selected country is invalid.');
                }
            }

            $curTeamId = 0;
            foreach ($params['team'] as $value) {
                if (isset($value['is_working']) && $value['is_working'] == 1) {
                    $curTeamId = $value['team_id'];
                }
            }
            if (!$curTeamId) {
                throw new Exception('You need to choose only a team with a working status.');
            }

            if (isset($params['is_old_employee']) && $params['is_old_employee'] == 1) {
                $employeeCode = !empty($params['employee_code']) ? $params['employee_code'] : $empDeleted->employee_code;
            } else {
                $employeeCode = $params['employee_code'];
            }

            $empId = $empDeleted ? $empDeleted->id : null;
            //check exists employee_code
            $exitsCddEmp = EmployeeModel::checkExistsEmpCode($employeeCode, $empId);
            if ($exitsCddEmp > 0) {
                throw new Exception('The Employee code has already been taken.');
            }

            $arrTeams = $params['team'];
            $dataValidate = $this->validateSaveTeamPosition($arrTeams);
            $arrEmpData = [
                "name" => $params['name'],
                "email" => $params['email'],
                "gender" => $params['gender'],
                "birthday" => (isset($params['id_card_number']) && $params['id_card_number']) ? $params['id_card_number'] : null,
                "employee_code" => $employeeCode,
                "id_card_number" => isset($params['id_card_number']) ? $params['id_card_number'] : '',
                "id_card_date" => (isset($params['id_card_date']) && $params['id_card_date']) ? $params['id_card_date'] : null,
                "id_card_place" => isset($params['id_card_place']) ? $params['id_card_place'] : '',
                "passport_number" => isset($params['passport_number']) ? $params['passport_number'] : '',
                "passport_addr" => isset($params['passport_addr']) ? $params['passport_addr'] : '',
                "passport_date_start" => (isset($params['passport_date_start']) && $params['passport_date_start']) ? $params['passport_date_start'] : null,
                "passport_date_exprie" => (isset($params['passport_date_exprie']) && $params['passport_date_exprie']) ? $params['passport_date_exprie'] : null,
                "folk" => isset($params['folk']) ? $params['folk'] : '',
                "religion" => isset($params['religion']) ? $params['religion'] : '',
                "marital" => (isset($params['marital']) && $params['marital'] != '') ? $params['marital'] : null,
                "country_id" => isset($country) ? $country->id : ''
            ];

            if ($empDeleted) {
                // TH1
                $candidate = Candidate::where('employee_id', $empDeleted->id)->first();
                if ($candidate) {
                    $arrEmpData['account_status'] = getOptions::WORKING;
                    if ($candidate->status && (int)$candidate->status !== getOptions::WORKING) {
                        $candidate->status = getOptions::WORKING;
                        $candidate->status_update_date = Carbon::now()->toDateString();
                        $candidate->save();
                    }
                } else {
                    $arrEmpData['account_status'] = $params['status'];
                }
                $resEmp = $this->updateEmployee($params, $arrEmpData, $empDeleted, $curTeamId, $dataValidate);
                if (!empty($resEmp)) {
                    $data['employee_id'] = $resEmp->id;
                    $leaveDay = LeaveDay::where('employee_id', $data['employee_id'])->withTrashed()->first();
                    $now = Carbon::now();
                    if (!$leaveDay) {
                        $leaveDay = new LeaveDay();
                        $leaveDay->employee_id = $data['employee_id'];
                        $leaveDay->created_at = $now;
                    }
                    $leaveDay->updated_at = $now;
                    $leaveDay->deleted_at = null;
                    $leaveDay->save();

                    // save email for user if changing and delete that user session
                    User::saveEmail($resEmp);
                }

                $accounts = explode('@', $resEmp->email);
                $response = [
                    'id' => $resEmp->id,
                    'account' => $accounts[0],
                    'email' => $resEmp->email,
                    'employee_code' => $resEmp->employee_code,
                ];
            } else {
                //TH2                
                $arrEmpData['account_status'] = $params['status'];
                $objEmpData = new EmployeeModel();
                $objEmpData->setData($arrEmpData);
                $objEmpData->save();
    
                // update team
                $this->saveTeamPosition($arrTeams, $objEmpData, $dataValidate);
                User::checkExistUser($objEmpData);
    
                $accounts = explode('@', $objEmpData->email);
                $response = [
                    'id' => $objEmpData->id,
                    'account' => $accounts[0],
                    'email' => $objEmpData->email,
                    'employee_code' => $objEmpData->employee_code,
                ];
            }

            DB::commit();
            return $response;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public function updateEmployee($params, $employeeData, $empDeleted, $curTeamId, $dataValidate)
    {
        //get employee
        list($inputTeamIds, $teamPositions, $aryTeam, $teamIdRemoves) = $dataValidate;
        
        $employee = EmployeeModel::find($empDeleted->id);
        $oldEmployee = clone $employee;
        $oldTeamNames = $oldEmployee ? $oldEmployee->getTeamNames() : null;

        $employee->leave_date = null;
        $employee->deleted_at = null;
        $employee->setData($employeeData);
        $employee->save();

        
        //employee team history
        $empTeamHis = EmployeeTeamHistory::where('employee_id', $empDeleted->id)->get();
        $dateNow = Carbon::now()->format('Y-m-d H:i:s');
        foreach ($empTeamHis as $teamHistory) {
            if ($teamHistory->end_at) {
                $teamHistory->end_at = $dateNow;
            }
            $teamHistory->is_working = EmployeeTeamHistory::END_WORK;
            $teamHistory->save();
        }
        foreach ($teamPositions as $key => $item) {
            $dataIns = [
                'employee_id' => $empDeleted->id,
                'team_id' => $item['team_id'],
                'start_at' => $item['start_at'],
                'end_at' => !empty($item['team_id']) ? $item['team_id'] : null,
                'role_id' => $item['position'],
                'is_working' => $item['is_working'],
            ];
            EmployeeTeamHistory::create($dataIns);
        }

        //Team members
        $oldTeamMembers = TeamMember::getTeamMembersByEmployees($employee->id, ['team_id','employee_id', 'role_id']);
        // set null leader id before set leader new
        Team::where('leader_id', $empDeleted->id)->update(['leader_id' => null]);
        $leaderPosId = (string) Role::getPositionLeader(); // id role team leader

        $aryNewTeamMember = []; // final records in table team members
        $now = Carbon::now()->toDateString();
        $oldTeamHistory = EmployeeTeamHistory::getCurrentTeamsHistory($empDeleted->id);
        $aryInsertTeamHistory = [];
        foreach ($teamPositions as $teamPostion) {
            $teamId = $teamPostion['team'];
            $position = $teamPostion['position'];
            $endAt = $teamPostion['_end_at'];
            $startAt = $teamPostion['start_at'];
            $isWorking = $teamPostion['is_working'];
            $team = $aryTeam[$teamId];

            if ($position === $leaderPosId && $endAt >= $now) { //position is leader
                if ($team->leader_id === (string) $empDeleted->id) {
                    $team->original['leader_id'] = null;
                    $team->leader_id = null;
                }
                $teamLeader = $team->getLeader();
                if (Team::MAX_LEADER === 1 && $teamLeader && $teamLeader->id != $empDeleted->id) { //flag team only have 1 leader
                    throw new Exception(Lang::get('team::messages.Team :name had :nameleader leader!', ['name' => htmlentities($team->name), 'nameleader' => htmlentities($team->leaderInfo->name)]), EmployeeModel::ERROR_CODE_EXCEPTION);
                }
                if (!$teamLeader) { //save leader for team
                    $team->leader_id = $empDeleted->id;
                    $team->save();
                }
            }
            $dataTeamMember = ['team_id' => $teamId, 'employee_id' => $empDeleted->id, 'role_id' => $position];
            if ($endAt > $now && !in_array($dataTeamMember, $aryNewTeamMember)) {
                $aryNewTeamMember[] = $dataTeamMember;
            }
            // check exist team history
            $flgEqual = true;
            foreach ($oldTeamHistory as $key => $item) {
                $teamEndAt = $item['end_at'] === null ? EmployeeModel::DATE_MAX : substr($item['end_at'], 0, 10);
                $teamStartAt = substr($item['start_at'], 0, 10);
                if ($teamEndAt === $endAt && $teamStartAt === $startAt && $item->role_id === $position
                    && $item->is_working === $isWorking && $item->team_id === $teamId) {
                    $flgEqual = false;
                    $oldTeamHistory->forget($key);
                    break;
                }
            }
            if ($flgEqual) {
                $dataTeamMember['start_at'] = $startAt;
                $dataTeamMember['end_at'] = $teamPostion['end_at'];
                $dataTeamMember['is_working'] = $isWorking;
                $aryInsertTeamHistory[] = $dataTeamMember;
            }
        }
        $this->updateEmpTeamHistory($oldTeamHistory, $aryInsertTeamHistory);
        $this->updateTeamMember($oldTeamMembers->toArray(), $aryNewTeamMember, $empDeleted->id);

        CacheBase::forgetFile(CacheBase::EMPL_PERMIS, $employee->id);
        CacheBase::forgetFilePrefix(CacheBase::MENU_USER, $employee->id);

        //Remove cache Team code prefix
        CacheHelper::forget(Team::CACHE_TEAM_CODE_PREFIX, $employee->id);
        CacheHelper::forget(CacheHelper::CACHE_TIME_SETTING_PREFIX, $employee->id);
        CacheHelper::forget(CacheHelper::CACHE_TIME_QUATER, $employee->id);

        //check update employee contract
        if ($oldEmployee) {
            // check save contract history
            $oldEmployee->come_back = true;
            $oldEmployee->contract_type = $oldEmployee->getItemRelate('work')->contract_type;
            $oldEmployee->team_name = $oldTeamNames;
            //new
            $employee->contract_type = 0;
            EmployeeContractHistory::insertItem($employee, $oldEmployee);
        }

        return $employee;
    }

    public function saveTeamPosition($arrTeam = null, $employee, $dataValidate)
    {
        // $dataValidate = $this->validateSaveTeamPosition($arrTeam);
        list($inputTeamIds, $teamPositions, $aryTeam, $teamIdRemoves) = $dataValidate;
        $inputTeamIds = array_diff($inputTeamIds, $teamIdRemoves);

        DB::beginTransaction();
        try {
            // update table leave_days when employee change team to japan
            $objTeamList = new TeamList();
            $teamIds = array_diff($inputTeamIds, $objTeamList->getDeletedTeamIds($inputTeamIds));
            
            $oldTeamMembers = TeamMember::getTeamMembersByEmployees($employee->id, ['team_id','employee_id', 'role_id']);
            $teamIdOlds = $oldTeamMembers->pluck('team_id')->toArray();
            $teamIdDiffs = array_diff($teamIds, $teamIdOlds);
            // if change team
            if ($teamIdDiffs || array_diff($teamIdOlds, $teamIds)) {
                event(new \Rikkei\Core\Events\DBEvent('updated', EmployeeModel::getTableName(), [
                    'id' => $employee->id,
                    'old' => [],
                    'new' => ['change_team' => true]
                ], false));
            }
            $teamPrefix = Team::getOnlyOneTeamCodePrefixChange(EmployeeModel::find($employee->id));
            if ($teamIdOlds && $teamPrefix != Team::CODE_PREFIX_JP &&
            EmployeeModel::checkExistTeam($teamIdDiffs, Team::CODE_PREFIX_JP)) {
                $leaveDay = LeaveDay::where('employee_id', '=', $employee->id)->first();
                if ($leaveDay) {
                    $remainDay = $leaveDay->day_last_transfer + $leaveDay->day_current_year + $leaveDay->day_seniority + $leaveDay->day_ot - $leaveDay->day_used;
                    if ($remainDay > LeaveDay::LEAVE_SIX_MONTH) {
                        $remainDayNew = LeaveDay::LEAVE_SIX_MONTH;
                    } else {
                        $remainDayNew = $remainDay;
                    }
                    $data = [
                        "leave_day_id" => $leaveDay->id,
                        "day_last_year" => $leaveDay->day_last_year,
                        "day_last_transfer" => $leaveDay->day_last_transfer,
                        "day_current_year" => $leaveDay->day_current_year,
                        "day_seniority" => $leaveDay->day_seniority,
                        "day_ot" => $leaveDay->day_ot,
                        "day_used" => $leaveDay->day_used,
                        "note" => $leaveDay->note,
                        "created_at" => $leaveDay->created_at,
                        "updated_at" => $leaveDay->updated_at,
                    ];
                    $leaveBack = LeaveDayBack::where('leave_day_id', $leaveDay->id)->first();
                    if ($leaveBack) {
                        $leaveBack->update($data);
                    } else {
                        LeaveDayBack::insert($data);
                    }

                    $leaveDayPermis = new LeaveDayPermission();
                    $change['day_vietnam_japan'] = [
                        "old" => $remainDay,
                        "new" => $remainDayNew
                    ];
                    $leaveDayPermis->saveHistory($employee->id, $change, LeaveDayHistories::TYPE_VIETNAM_JAPAN);

                    $leaveDay->day_last_year = 0.0;
                    $leaveDay->day_last_transfer = 0.0;
                    $leaveDay->day_current_year = $remainDayNew;
                    $leaveDay->day_seniority = 0.0;
                    $leaveDay->day_ot = 0.0;
                    $leaveDay->day_used = 0.0;
                    $leaveDay->note = Lang::get('team::view.Team vietnam change team japan');
                    $leaveDay->save();
                }
            }

            // set null leader id before set leader new
            Team::where('leader_id', $employee->id)->update(['leader_id' => null]);
            $leaderPosId = (string) Role::getPositionLeader(); // id role team leader

            $aryNewTeamMember = []; // final records in table team members
            $now = Carbon::now()->toDateString();
            $oldTeamHistory = EmployeeTeamHistory::getCurrentTeamsHistory($employee->id);
            $aryInsertTeamHistory = [];
            foreach ($teamPositions as $teamPostion) {
                $teamId = $teamPostion['team'];
                $position = $teamPostion['position'];
                $endAt = $teamPostion['_end_at'];
                $startAt = $teamPostion['start_at'];
                $isWorking = $teamPostion['is_working'];
                $team = $aryTeam[$teamId];

                if ($position === $leaderPosId && $endAt >= $now) { //position is leader
                    if ($team->leader_id === (string) $employee->id) {
                        $team->original['leader_id'] = null;
                        $team->leader_id = null;
                    }
                    $teamLeader = $team->getLeader();
                    if (Team::MAX_LEADER === 1 && $teamLeader && $teamLeader->id != $employee->id) { //flag team only have 1 leader
                        throw new Exception(Lang::get('team::messages.Team :name had :nameleader leader!', ['name' => htmlentities($team->name), 'nameleader' => htmlentities($team->leaderInfo->name)]), EmployeeModel::ERROR_CODE_EXCEPTION);
                    }
                    if (!$teamLeader) { //save leader for team
                        $team->leader_id = $employee->id;
                        $team->save();
                    }
                }
                $dataTeamMember = ['team_id' => $teamId, 'employee_id' => $employee->id, 'role_id' => $position];
                if ($endAt > $now && !in_array($dataTeamMember, $aryNewTeamMember)) {
                    $aryNewTeamMember[] = $dataTeamMember;
                }
                // check exist team history
                $flgEqual = true;
                foreach ($oldTeamHistory as $key => $item) {
                    $teamEndAt = $item['end_at'] === null ? EmployeeModel::DATE_MAX : substr($item['end_at'], 0, 10);
                    $teamStartAt = substr($item['start_at'], 0, 10);
                    if ($teamEndAt === $endAt && $teamStartAt === $startAt && $item->role_id === $position
                        && $item->is_working === $isWorking && $item->team_id === $teamId) {
                        $flgEqual = false;
                        $oldTeamHistory->forget($key);
                        break;
                    }
                }
                if ($flgEqual) {
                    $dataTeamMember['start_at'] = $startAt;
                    $dataTeamMember['end_at'] = $teamPostion['end_at'];
                    $dataTeamMember['is_working'] = $isWorking;
                    $aryInsertTeamHistory[] = $dataTeamMember;
                }
            }
            $this->updateEmpTeamHistory($oldTeamHistory, $aryInsertTeamHistory);
            $this->updateTeamMember($oldTeamMembers->toArray(), $aryNewTeamMember, $employee->id);

            CacheBase::forgetFile(CacheBase::EMPL_PERMIS, $employee->id);
            CacheBase::forgetFilePrefix(CacheBase::MENU_USER, $employee->id);
            DB::commit();

            //Remove cache Team code prefix
            CacheHelper::forget(Team::CACHE_TEAM_CODE_PREFIX, $employee->id);
            CacheHelper::forget(CacheHelper::CACHE_TIME_SETTING_PREFIX, $employee->id);
            CacheHelper::forget(CacheHelper::CACHE_TIME_QUATER, $employee->id);
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * Update team history
     *
     * @param array $oldData
     * @param array $newData
     * @return void
     */
    public function updateEmpTeamHistory($oldData, $newData)
    {
        // reuse team for update
        foreach ($newData as $newKey => $newValue) {
            foreach ($oldData as $oldKey => $oldValue) {
                if ($newValue['team_id'] === $oldValue->team_id) {
                    $oldValue->update($newValue);
                    $oldData->forget($oldKey);
                    unset($newData[$newKey]);
                }
            }
        }
        $now = Carbon::now()->toDateTimeString();
        // insert team history
        if ($newData) {
            foreach ($newData as $key => $item) {
                $newData[$key]['created_at'] = $now;
                $newData[$key]['updated_at'] = $now;
            }
            EmployeeTeamHistory::insert($newData);
        }
        // delete team history
        foreach ($oldData as $oldKey => $teamHistory) {
            $teamHistory->deleted_at = $now;
            if (empty($teamHistory->end_at)) {
                $teamHistory->end_at = $now;
            }
            $teamHistory->save();
        }
    }

    /**
     * Update team member
     *
     * @param array $oldData
     * @param array $newData
     * @return void
     */
    public function updateTeamMember($oldData, $newData, $empId)
    {
        // insert new record team member
        $dataInsert = $this->diffArray2D($newData, $oldData);
        if ($dataInsert) {
            foreach ($dataInsert as $key => $item) {
                $now = Carbon::now()->toDateTimeString();
                $dataInsert[$key]['created_at'] = $now;
                $dataInsert[$key]['updated_at'] = $now;
            }
            TeamMember::insert($dataInsert);
        }
        // delete record team member
        $dataDelete = $this->diffArray2D($oldData, $newData);
        if ($dataDelete) {
            $collection = TeamMember::where('employee_id', $empId);
            $collection->where(function ($q1) use ($dataDelete) {
                foreach ($dataDelete as $item) {
                    $q1->orWhere(function ($q2) use ($item) {
                        $q2->where('team_id', $item['team_id'])
                            ->where('role_id', $item['role_id']);
                    });
                }
            });
            $collection->delete();
        }
    }

    // computes the difference of array 2D
    public function diffArray2D($aryX, $aryY)
    {
        foreach ($aryX as $key => $valueX) {
            if (in_array($valueX, $aryY)) {
                unset($aryX[$key]);
            }
        }
        return $aryX;
    }

    public function validateSaveTeamPosition($teamPositions)
    {
        // remove team have _end_at < dateNow
        $dateNow = Carbon::now();
        $teamIdRemoves = [];
        foreach($teamPositions as $key => $item) {
            if (!empty($item['end_at']) && $item['end_at'] <= $dateNow->format("Y-m-d")) {
                $teamIdRemoves[] = $item['team_id'];
            }
        }

        $keys = [];
        $inputTeamIds = [];
        $formatDate = 'Y-m-d';
        //check miss data and custom data
        foreach ($teamPositions as $key => $team) {
            $teamId = isset($team['team_id']) ? $team['team_id'] : null;
            $position = isset($team['position']) ? $team['position'] : null;
            $startAt = isset($team['start_at']) ? $team['start_at'] : null;
            $endAt = isset($team['end_at']) ? $team['end_at'] : null;

            $startAt = trim($startAt);
            $objStartAt = \DateTime::createFromFormat($formatDate, $startAt);
            $endAt = trim($endAt);
            $objEndAt = $endAt ? \DateTime::createFromFormat($formatDate, $endAt) : null;
            // date not format
            if (!$objStartAt || $objStartAt->format($formatDate) !== $startAt
                || $endAt && (!$objEndAt || $objEndAt->format($formatDate) !== $endAt)) {
                throw new Exception('Date data is incorrect');
            }

            /* custom data */
            $team['team'] = trim($teamId);
            $team['position'] = trim($position);
            if (empty($team['is_working'])) {
                $team['is_working'] = '0';
            }
            $team['end_at'] = null;
            $team['_end_at'] = EmployeeModel::DATE_MAX;
            if (!empty($endAt)) {
                $team['end_at'] = $endAt;
                $team['_end_at'] = $endAt;
            }
            $teamPositions[$key] = $team;
            $keys[] = $key;
            $inputTeamIds[] = $teamId;
        }

        //check data team not same
        $teamLength = count($teamPositions);
        for ($i = 0; $i < $teamLength; $i++) {
            $prevTeam = $teamPositions[$keys[$i]];
            if ($prevTeam['_end_at'] <= $prevTeam['start_at']) {
                throw new Exception('The end date at must be after start date.');
            }
            for ($j = $i + 1; $j < $teamLength; $j++) {
                $nextTeam = $teamPositions[$keys[$j]];
                if ($prevTeam['team_id'] !== $nextTeam['team_id']) {
                    continue;
                }
                if ($prevTeam['_end_at'] === EmployeeModel::DATE_MAX && $nextTeam['_end_at'] === EmployeeModel::DATE_MAX) {
                    throw new Exception('Team same data.');
                }
                // period time overlap
                if ($prevTeam['start_at'] <= $nextTeam['_end_at'] && $prevTeam['_end_at'] >= $nextTeam['start_at']) {
                    throw new Exception('Date data is incorrect.');
                }
            }
        }

        $inputTeamIds = array_unique($inputTeamIds);
        $teams = Team::withTrashed()->withoutGlobalScope(SmScope::class)->whereIn('id', $inputTeamIds)->get();
        // exists a team not found
        if (count($inputTeamIds) > count($teams)) {
            throw new Exception('There exists a team not found.');
        }

        $aryTeam = [];
        // team not is function
        foreach ($teams as $team) {
            if (!$team->isFunction()) {
                throw new Exception('Team is not function.');
            }
            $aryTeam[$team->id] = $team;
        }

        return [$inputTeamIds, $teamPositions, $aryTeam, $teamIdRemoves];
    }

    public static function createOrUpdateEmployee($dataRequest, $candidate, $data, $curEmp, $iptEmail, $contractTeamID)
    {
        //checked old employee
        $oldEmployeeId = isset($data['old_employee_id']) ? $data['old_employee_id'] : null;
        //get data
        $isWorkingTypeExternel = in_array($candidate->working_type, getOptions::workingTypeExternal());
        $empOfficialDate = null;
        if (in_array($candidate->working_type, [
            getOptions::WORKING_UNLIMIT,
            getOptions::WORKING_OFFICIAL,
        ])) {
            $empOfficialDate = $candidate->start_working_date;
        }
        if (in_array($candidate->working_type, [getOptions::WORKING_PROBATION])) {
            $empOfficialDate = $candidate->official_date;
        }
        $dataEmployee = $dataRequest;
        $dataEmployee['name'] = $candidate->fullname;
        if (!$candidate->fullname) {
            $iptEmail = explode('@', $iptEmail);
            $dataEmployee['name'] = $iptEmail[0];
        }
        $dataEmployee['join_date'] = $candidate->start_working_date;
        $dataEmployee['offcial_date'] = $empOfficialDate;
        $dataEmployee['trial_date'] = $candidate->trial_work_start_date && !$isWorkingTypeExternel
                ? Carbon::parse($candidate->trial_work_start_date)->format('Y-m-d') : null;
        $dataEmployee['gender'] = $candidate->gender ? $candidate->gender : EmployeeModel::GENDER_MALE;
        $dataEmployee['birthday'] = $candidate->birthday;
        $dataEmployee['mobile_phone'] = $candidate->mobile;
        $dataEmployee['home_town'] = $candidate->home_town;
        $dataEmployee['working_type'] = $candidate->working_type ? $candidate->working_type : 0;
        if ($candidate->contract_length) {
            $dataEmployee['contract_length'] = $candidate->contract_length;
        }
        $dataEmployee['trial_end_date'] = $candidate->trial_work_end_date && !$isWorkingTypeExternel
                ? $candidate->trial_work_end_date : null;
        $dataEmployee['account_status'] = $candidate->status;
        $dataEmployee['created_by'] = $curEmp->id;
        //employee contact
        $employeeContact = isset($dataRequest['contact']) ? $dataRequest['contact'] : [];
        //get employee
        $oldEmployee = null;
        if ($oldEmployeeId) {
            $employee = EmployeeModel::find($oldEmployeeId);
            if (!$employee) {
                throw new \Exception(trans('resource::message.Item not found'), 404);
            }
            $oldEmployee = clone $employee;
            $employee->leave_date = null;
        } else {
            $employee = $candidate->employee;
        }
        // check if user is candidate
        $needLogOut = false;
        $updateEmp = false;
        if ($employee) {
            $loggingUser = $curEmp->email;
            $emEmail = $employee->email;
            $needLogOut = ($loggingUser === $emEmail && $emEmail !== $dataRequest['email']);
            $updateEmp = true;
        }
        //not update exists
        $isSaveEmp = true;
        if (!$employee) {
            if ($candidate->status == getOptions::FAIL_CDD) {
                $isSaveEmp = false;
            }
            $employee = new EmployeeModel();
        }
        $aryEmpField = array_only($dataEmployee, $employee->getFillable());
        foreach ($aryEmpField as $key => $value) {
            $employee->{$key} = $value;
        }
        $employee->deleted_at = null;
        if ($candidate->status == getOptions::FAIL_CDD) {
            if (!$oldEmployeeId) {
                $employee->deleted_at = Carbon::now()->toDateTimeString();
            } else {
                $oldLeaveDate = Carbon::now()->toDateString();
                $oldContract = EmployeeContractHistory::where('employee_id', $oldEmployeeId)
                        ->whereNotNull('leave_date')
                        ->orderBy('leave_date', 'desc')
                        ->first();
                if ($oldContract) {
                    $oldLeaveDate = $oldContract->leave_date;
                }
                $employee->leave_date = $oldLeaveDate;
            }
            //reject request asset
            $employee->rejectRequestAsset();
        }
        if ($isSaveEmp) {
            $employee->save();
        }

        //check old employee and delete
        if ($oldEmployee) {
            Candidate::where('employee_id', $oldEmployee->id)
                    ->where('id', '!=', $candidate->id)
                    ->update(['employee_id' => null]);
        }
        if (!$candidate->employee_id || $oldEmployee) {
            $candidate->employee_id = $employee->id;
            $candidate->save();
        }
        $oldTeamNames = $oldEmployee ? $oldEmployee->getTeamNames() : null;

        if ($isSaveEmp) {
            if ($candidate->team_id) {
                $employee->teams()->sync([$candidate->team_id => ['role_id' => Team::ROLE_MEMBER]]);
                //Save team history of employee
                $dateNow = Carbon::now()->format('Y-m-d H:i:s');
                if ($updateEmp) {
                    $currentTeam = EmployeeTeamHistory::getCurrentTeams($candidate->employee_id);
                    $arrCurTeamId = [];
                    $arrCurRoleId = [];
                    foreach ($currentTeam as $t) {
                        $arrCurTeamId[] = $t->team_id;
                        $arrCurRoleId[] = $t->position;
                    }
                    $newTeamId = $candidate->team_id;
    
                    foreach ($arrCurTeamId as $teamId) {
                        if ((int)$teamId !== (int)$newTeamId) {
                            $teamHistory = EmployeeTeamHistory::getCurrentByTeamEmployee($teamId, $candidate->employee_id);
                            $teamHistory->end_at = $dateNow;
                            $teamHistory->is_working = EmployeeTeamHistory::END_WORK;
                            $teamHistory->save();
                        }
                    }
    
                    //TH nhân viên có nhiều team và bị sửa team đang có end_at là team hiện tại 
                    $currentTeamWroking = EmployeeTeamHistory::getCurrentTeamsWorking($candidate->employee_id);
                    foreach($currentTeamWroking as $item) {
                        if (!in_array($newTeamId, $arrCurTeamId) && $item->end_at) {
                            $item->is_working = EmployeeTeamHistory::END_WORK;
                            $item->save();
                        }
                    }
    
                    if (!in_array($newTeamId, $arrCurTeamId)) {
                        $teamHistory = new EmployeeTeamHistory();
                        $teamHistory->team_id = $newTeamId;
                        $teamHistory->employee_id = $candidate->employee_id;
                        $teamHistory->start_at = $candidate->start_working_date;
                        $teamHistory->role_id = Team::ROLE_MEMBER;
                        $teamHistory->is_working = EmployeeTeamHistory::IS_WORKING;
                        $teamHistory->save();
                    }
                } else {
                    $empTeamHistory = new EmployeeTeamHistory;
                    $empTeamHistory->employee_id = $candidate->employee_id;
                    $empTeamHistory->team_id = $candidate->team_id;
                    $empTeamHistory->start_at = $candidate->start_working_date;
                    $empTeamHistory->role_id = Team::ROLE_MEMBER;
                    $empTeamHistory->is_working = EmployeeTeamHistory::IS_WORKING;
                    $empTeamHistory->save();
                }
            }
            
            //update candiate contact
            $empContactModel = $employee->contact;
            $employeeContact['mobile_phone'] = $candidate->mobile;
            $employeeContact['skype'] = $candidate->skype;
            $employeeContact['personal_email'] = $candidate->email;
            if (!$empContactModel) {
                $empContactModel = new EmployeeContact();
                $employeeContact['employee_id'] = $employee->id;
            } else {
                if (!$empContactModel->native_country && isset($employeeContact['tempo_country'])) {
                    $empContactModel->native_country = $employeeContact['tempo_country'];
                }
            }
            $empContactModel->setData($employeeContact);
            $empContactModel->save();

            $employee->generateEmpCode($contractTeamID, null, $candidate->working_type ? $candidate->working_type : null);

            //update employee work
            $dataEmpWork = [];
            if ($candidate->working_type) {
                $dataEmpWork['working_type'] = $candidate->working_type;
            }
            if ($candidate->contract_length) {
                $dataEmpWork['contract_length'] = $candidate->contract_length;
            }
            if ($dataEmpWork) {
                Candidate::updateEmployeeWork($employee, [
                    'working_type' => $candidate->working_type,
                    'contract_length' => $candidate->contract_length
                ]);
            }
        }

        //check update employee contract
        if ($oldEmployee) {
            // check save contract history
            $oldEmployee->come_back = true;
            $oldEmployee->contract_type = $oldEmployee->getItemRelate('work')->contract_type;
            $oldEmployee->team_name = $oldTeamNames;
            //new
            $employee->contract_type = $candidate->working_type;
            EmployeeContractHistory::insertItem($employee, $oldEmployee);
        }

        if (!$isSaveEmp) {
            $employee = null;
        }

        return ['employee' => $employee, 'logout' => $needLogOut];
    }

    public function updateContractTeamId($contractTeamID, Candidate $candidate)
    {
        $candidate->contract_team_id = $contractTeamID;
        $candidate->save();
    }

    public function generateCode($prefixCode, $suffixCode)
    {
        $code = '';
        $countNumber = 1;
        $divNumber = $suffixCode;
        while ($divNumber >= 10) {
            $divNumber = $divNumber / 10;
            $countNumber++;
        }
        switch ($countNumber) {
            case 1:
                $code = $prefixCode . '000000' . $suffixCode;
                break;
            case 2:
                $code = $prefixCode . '00000' . $suffixCode;
                break;
            case 3:
                $code = $prefixCode . '0000' . $suffixCode;
                break;
            case 4:
                $code = $prefixCode . '000' . $suffixCode;
                break;
            case 5:
                $code = $prefixCode . '00' . $suffixCode;
                break;
            case 6:
                $code = $prefixCode . '0' . $suffixCode;
                break;
            default:
                $code = $prefixCode . $suffixCode;
                break;
        }
        return $code;
    }

    public function getEmpNotInProject($time)
    {
        $date = date('Y-m-1',strtotime($time));
        $listEmpInProject = DB::table('project_members')->where('start_at','<=',$date)->where('end_at','>=',$date)->groupBy('employee_id')->lists('employee_id');
        $listEmp = DB::table('employees')
                        ->where('leave_date',null)
                        ->orWhere('leave_date','>=',$date)
                        ->join('team_members','team_members.employee_id','=','employees.employee_card_id')
                        ->whereNotIn('employees.employee_card_id',$listEmpInProject)
                        ->select('team_members.employee_id','team_members.team_id','team_members.role_id','employees.name',
                        'employees.email')
                        ->get();
        return $listEmp;
    }

    public function getEmployeeRole($params)
    {
        $arrRole = $params['role_name'];
        $listEmpRole = DB::table('employees')
            ->select(
                'employees.name',
                'employees.email',
                DB::raw("GROUP_CONCAT(roles.role SEPARATOR ',') AS roles")
            )
            ->join('employee_roles', 'employee_roles.employee_id', '=', 'employees.id')
            ->join('roles', 'roles.id', '=',  'employee_roles.role_id')
            ->whereIn('roles.role', $arrRole)
            ->whereNull('employees.deleted_at')
            ->whereNull('employees.leave_date')
            ->where(function ($query) {
                $query->whereNull('employees.leave_date')
                    ->orWhereRaw('DATE(employees.leave_date) > CURDATE()');
            });
        if (!empty($params['updated_from'])) {
            $listEmpRole = $listEmpRole->whereDate("employee_roles.updated_at", ">=", $params['updated_from']);
        }
        $listEmpRole = $listEmpRole->groupBy("employees.id")->get();

        return $listEmpRole;
    }

    public function getTeamsLeader($data = NULL)
    {        
        // if($params['branch']){
        //     return TRUE;
        // };
        // dd($params['branch']);
        // $arrBranch = $params['branch'];
        // dd($arrBranch);
        $listTeamsLeader = DB::table('team_members')

            ->select(
                'team_members.employee_id',
                'employees.name',
                'employees.email',
                'roles.role',
                'team_members.team_id as division_id',
                'teams.name as division'
            )
            ->join('roles','roles.id','=','team_members.role_id')
            ->join('employees', 'employees.id', '=', 'team_members.employee_id')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->where('teams.is_soft_dev','=','1')
            ->whereIN('team_members.role_id',[1,2])
            ->where('teams.follow_team_id','!=','0');
            if(isset($data['branch'])){
                $listTeamsLeader = $listTeamsLeader->whereIN('teams.branch_code',$data['branch']);
            };
            $listTeamsLeader = $listTeamsLeader->get();


        return $listTeamsLeader;
    }

}
