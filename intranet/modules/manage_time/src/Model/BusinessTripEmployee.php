<?php

namespace Rikkei\ManageTime\Model;

use DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\Form;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\ManageTime\View\View;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\View\Config;

class BusinessTripEmployee extends CoreModel 
{
    protected $table = 'business_trip_employees';
    protected $fillable = ['register_id', 'employee_id', 'start_at', 'end_at', 'note', 'team_id'];

    public $timestamps = false;

    /**
     * Get all employees in a supplement register
     * Columns: employee_id, employee_code, employee_name, start_at, end_at
     *
     * @param int $registerId
     *
     * @return SupplementEmployee join Employee collection
     */
    public static function getEmployees($registerId) 
    {
        $businessEmployeeTbl = self::getTableName();
        $empTbl = Employee::getTableName();
        return self::join("{$empTbl}", "{$empTbl}.id", "=", "{$businessEmployeeTbl}.employee_id")
            ->select(
                "{$businessEmployeeTbl}.employee_id",
                "{$empTbl}.employee_code",
                "{$empTbl}.name",
                "{$businessEmployeeTbl}.start_at",
                "{$businessEmployeeTbl}.end_at"
            )
            ->where("register_id", $registerId)
            ->get();
    }

    public static function removeAllEmp($registerId)
    {
        self::where("register_id", $registerId)->delete();
    }

    public static function getRegistersOfEmployee($employeeId, $startDate, $endDate)
    {
        return BusinessTripRegister::join("business_trip_employees", "business_trip_employees.register_id", "=", "business_trip_registers.id")
                ->select('business_trip_employees.employee_id', 'business_trip_employees.start_at', 'business_trip_employees.end_at')
                ->where('status', '=', BusinessTripRegister::STATUS_APPROVED)
                ->whereDate('business_trip_employees.start_at', '<=', $endDate)
                ->whereDate('business_trip_employees.end_at', '>=', $startDate)
                ->where('business_trip_employees.employee_id', $employeeId)
                ->get();
    }

    /**
     * get register business when employee after join company
     * @param  [int] $employeeId
     * @param  [date|datetime] $startDate
     * @param  [date|datetime] $endDate
     * @return [collection]
     */
    public static function getRegistersOfEmployeeJoinDate($employeeId, $startDate, $endDate)
    {
        $tblEmp = Employee::getTableName();
        return BusinessTripRegister::join("business_trip_employees", "business_trip_employees.register_id", "=", "business_trip_registers.id")
            ->leftJoin("{$tblEmp} as tblEmp", 'business_trip_employees.employee_id', '=', 'tblEmp.id')
            ->select('business_trip_employees.employee_id', 'business_trip_employees.start_at', 'business_trip_employees.end_at')
            ->where('status', '=', BusinessTripRegister::STATUS_APPROVED)
            ->where(DB::raw("date(business_trip_employees.start_at)"), '<=', DB::raw('date("' . $endDate .'")'))
            ->where(DB::raw("date(business_trip_employees.end_at)"), '>=', DB::raw('date("' . $startDate . '")'))
            ->where(DB::raw("date(tblEmp.join_date)"), '<=', DB::raw("date(business_trip_employees.start_at)"))
            ->where('business_trip_employees.employee_id', $employeeId)
            ->get();
    }
        
    /**
     * insert team id when register
     *
     * @param  string $strEmpIds
     * @param  int $registerId
     * @return
     */
    public static function insertTeamId($strEmpIds, $registerId)
    {
        if (is_numeric($registerId)) {
            $isWoking = EmployeeTeamHistory::IS_WORKING;
            $strEmpIds = strip_tags(addslashes($strEmpIds));
            DB::statement("update business_trip_employees as e, (
                select *
                    from employee_team_history as eth
                    where eth.is_working = {$isWoking}
                        and eth.employee_id in ({$strEmpIds})
                ) as tbl
                set e.team_id = tbl.team_id
                where e.employee_id = tbl.employee_id
                    and e.register_id = {$registerId}");
        }
        return;
    }
    
    /**
     * get sql employee onsite with same branch
     *
     * @param  date $date Y-m-d
     * @return string
     */
    public function getTablEmployeeOnisteSameBranch($date)
    {
        $viewTableName = 'view_business_trip_approve';
        $dayAllowed = Project::DAY_ALLOWED;
        $sql = "SELECT 
                group_concat(concat(c.register_id) ORDER BY c.register_id ASC separator ', ') as group_id,
                group_concat(DISTINCT concat(c.start_at , '|', c.register_id) ORDER BY c.start_at ASC separator ', ') as group_start_at_id,
                group_concat(DISTINCT concat(c.start_at, '->', c.end_at_now) ORDER BY c.start_at ASC separator '; ') as group_date,
                c.employee_id as employee_id,
                c.employee_code as employee_code,
                c.name as employee_name,
                c.email as employee_email,
                MIN(c.start_at) AS start_at,
                MAX(c.end_at_now) AS end_at_now,
                MAX(c.end_at) AS end_at,
                sum(onsite_days) as 'onsite_days',
                c.province_id,
                (case 
                    when pro.province = 'Hà Nội' then 'hanoi'
                    when pro.province = 'Đà Nẵng' then 'danang'
                    when pro.province = 'Thành phố Hồ Chí Minh' then 'hcm'
                    else pro.province
                end) as branch_business
            FROM 
                (SELECT c0.*, (@rn:=@rn + COALESCE(startflag, 0)) AS cumestarts
                FROM 
                    (SELECT v1.register_id,
                        v1.employee_id,
                        v1.employee_code,
                        v1.name,
                        v1.email,
                        v1.location,
                        v1.company_customer,
                        v1.purpose,
                        DATE_FORMAT(v1.start_at, '%Y-%m-%d') as start_at,
                        DATE_FORMAT((case 
                                when date(v1.start_at) <= '{$date}' and date(v1.end_at) >= '{$date}' then '{$date}' 
                                when date(v1.start_at) <= '{$date}' and date(v1.end_at) <= '{$date}' then v1.end_at 
                                else ''
                            end), '%Y-%m-%d') as end_at_now,
                        v1.end_at,
                        (NOT EXISTS(SELECT 1
                            FROM {$viewTableName} v2
                            where v1.employee_id = v2.employee_id
                                AND v1.start_at > v2.end_at
                                AND v1.start_at <= v2.end_at + INTERVAL {$dayAllowed} DAY
                                AND v2.province_id is not null
                                AND v2.province_id = v1.province_id
                            ORDER BY v2.employee_id, v2.start_at
                        )) AS startflag,
                        (case
                            when (DATEDIFF( DATE_FORMAT((case 
                                    when date(v1.end_at) >= '{$date}' then '{$date}'
                                    else v1.end_at
                                end), '%Y-%m-%d') , DATE_FORMAT(v1.start_at, '%Y-%m-%d')) + 1) > 0 then
                                    DATEDIFF( DATE_FORMAT((case 
                                    when date(v1.end_at) >= '{$date}' then '{$date}'
                                    else v1.end_at
                                end), '%Y-%m-%d') , DATE_FORMAT(v1.start_at, '%Y-%m-%d')) + 1
                            else 0
                        end) as 'onsite_days',
                        v1.province_id
                    FROM {$viewTableName} v1
                    where v1.province_id is not null
                    ORDER BY v1.employee_id, v1.start_at
                    ) c0
                CROSS JOIN (SELECT @rn:=0) params
                ORDER BY c0.employee_id, c0.start_at
                ) c
            left join lib_province pro on pro.id = c.province_id
            GROUP BY c.employee_id, c.cumestarts
        ";
        return $sql;
    }

    /**
     * get sql employee onsite
     *
     * @param  date $date Y-m-d
     * @return string
     */
    public function getTablEmployeeOniste($date, $isSameBranch = false)
    {
        $viewTableName = 'view_business_trip_approve';
        $dayAllowed = Project::DAY_ALLOWED;
        $sql = "SELECT 
                group_concat(concat(c.register_id) ORDER BY c.register_id ASC separator ', ') as group_id,
                group_concat(DISTINCT concat(c.start_at , '|', c.register_id) ORDER BY c.start_at ASC separator ', ') as group_start_at_id,
                group_concat(DISTINCT concat(c.start_at, '->', c.end_at_now) ORDER BY c.start_at ASC separator '; ') as group_date,
                c.employee_id as employee_id,
                c.employee_code as employee_code,
                c.name as employee_name,
                c.email as employee_email,
                MIN(c.start_at) AS start_at,
                MAX(c.end_at_now) AS end_at_now,
                MAX(c.end_at) AS end_at,
                sum(onsite_days) as 'onsite_days'
            FROM 
                (SELECT c0.*, (@rn:=@rn + COALESCE(startflag, 0)) AS cumestarts
                FROM 
                    (SELECT v1.register_id,
                        v1.employee_id,
                        v1.employee_code,
                        v1.name,
                        v1.email,
                        v1.location,
                        v1.company_customer,
                        v1.purpose,
                        DATE_FORMAT(v1.start_at, '%Y-%m-%d') as start_at,
                        DATE_FORMAT((case 
                            when date(v1.start_at) <= '{$date}' and date(v1.end_at) >= '{$date}' then '{$date}' 
                                when date(v1.start_at) <= '{$date}' and date(v1.end_at) <= '{$date}' then v1.end_at 
                                else ''
                            end), '%Y-%m-%d') as end_at_now,
                        v1.end_at,
                        (NOT EXISTS(SELECT 1
                            FROM {$viewTableName} v2
                            where v1.employee_id = v2.employee_id
                                AND v1.start_at > v2.end_at
                                AND v1.start_at <= v2.end_at + INTERVAL {$dayAllowed} DAY
                            ORDER BY v2.employee_id, v2.start_at
                        )) AS startflag,
                        (case
                            when (DATEDIFF( DATE_FORMAT((case 
                                    when date(v1.end_at) >= '{$date}' then '{$date}'
                                    else v1.end_at
                                end), '%Y-%m-%d') , DATE_FORMAT(v1.start_at, '%Y-%m-%d')) + 1) > 0 then
                                    DATEDIFF( DATE_FORMAT((case 
                                    when date(v1.end_at) >= '{$date}' then '{$date}'
                                    else v1.end_at
                                end), '%Y-%m-%d') , DATE_FORMAT(v1.start_at, '%Y-%m-%d')) + 1
                            else 0
                        end) as 'onsite_days'
                    FROM {$viewTableName} v1
                    ORDER BY v1.employee_id, v1.start_at
                    ) c0
                CROSS JOIN (SELECT @rn:=0) params
                ORDER BY c0.employee_id, c0.start_at
                ) c
            GROUP BY c.employee_id, c.cumestarts
        ";
        return $sql;
    }

    /**
     * report employee onsite with year
     * 1 year, 2 year ...
     * @param  carbon $timeStart
     * @param  carbon $timeEnd
     * @param  int|null $year
     * @return collection
     */
    public function reportOnsiteWithYear($timeStart, $timeEnd, $year = null, $isExport = false)
    {
        $viewTableName = 'view_business_trip_approve';
        $numberYear = View::NUMBER_YEAR;
        $dayYear = View::DAY_YEAR;
        $date = $timeEnd->format('Y-m-d');
        $dateStart = $timeStart->format('Y-m-d');
        $dateEnd = $timeEnd->format('Y-m-d');
        $countDay = $timeEnd->diffInDays($timeStart) + 1;

        $i = 0;
        if (!$year) {
            $sqlYear = 'tbl.onsite_days between ' . $dayYear . ' and ' . ($dayYear + $countDay) . "\n";
            $dayYear += $dayYear;
            do {
                $sqlYear .= ' or tbl.onsite_days between ' . $dayYear . ' and ' . ($dayYear + $countDay) . "\n";
                $dayYear += $dayYear;
                $i++;
            } while($i < $numberYear);
        } else {
            $dayYear = $dayYear * $year;
            $sqlYear = 'tbl.onsite_days between ' . $dayYear . ' and ' . ($dayYear + $countDay);
        }
        $sqlYear = trim($sqlYear);
        $filter = Form::getFilterData();
        $pager = Config::getPagerData();
        $start= ($pager['page'] - 1) * $pager['limit'];
        $sqlEmployeeOnsite = $this->getTablEmployeeOniste($date);
        $sql = "SELECT tbl.*,
                business_view.id,
                business_view.location,
                business_view.company_customer,
                business_view.purpose,
                teams.name as team_name,
                roles.role as role_name,
                group_concat(DISTINCT concat( proj_company.proj_id) separator ', ') as proj_id,
                group_concat(DISTINCT concat( proj_company.company_name) separator ', ') as company_name,
                group_concat(DISTINCT concat( proj_company.contacts_name) separator ', ') as contacts_name,
                group_concat(DISTINCT concat( proj_company.sale_employee) separator ', ') as sale_employee
            FROM 
                ({$sqlEmployeeOnsite}) tbl
            left join employee_team_history eth on eth.employee_id = tbl.employee_id
            left join teams on teams.id = eth.team_id
            left join roles on roles.id = eth.role_id
            left join {$viewTableName} as business_view on (business_view.employee_id = tbl.employee_id and business_view.end_at = tbl.end_at)
            left join (
                SELECT
                    projs.id as proj_id,
                    projs.name as proj_name,
                    projs.company_id as proj_company_id,
                    project_members.employee_id as employee_id,
                    project_members.start_at as emp_start_at,
                    project_members.end_at as emp_end_at,
                    cust_companies.company as company_name,
                    empCompany.id as sale_id,
                    empCompany.name as sale_employee,
                    cust_contacts.name as contacts_name
                FROM projs
                left join project_members on project_members.project_id = projs.id
                left join cust_contacts on projs.cust_contact_id = cust_contacts.id
                left join cust_companies on projs.company_id = cust_companies.id
                inner join employees as empCompany on empCompany.id = cust_companies.manager_id
                where projs.deleted_at is null
                    and project_members.deleted_at is null
                    and project_members.status = 1
                group by projs.id, project_members.id
            ) as proj_company on (proj_company.employee_id = tbl.employee_id
                    and proj_company.emp_start_at <= business_view.end_at 
                    and proj_company.emp_end_at >= business_view.start_at)
            where eth.is_working = 1 
                and ({$sqlYear})
        ";
        $sql .= "  and tbl.end_at_now between '{$dateStart}' and '{$dateEnd}' \n";

        if (isset($filter['report_business_onsite.employee_code'])) {
            $empCode = trim($filter['report_business_onsite.employee_code']);
            $sql .= " and tbl.employee_code = '{$empCode}' \n";
        }
        if (isset($filter['report_business_onsite.name'])) {
            $empName = trim($filter['report_business_onsite.name']);
            $sql .= " and tbl.employee_name like '%{$empName}%' \n";
        }
        if (isset($filter['report_business_onsite.email'])) {
            $empEmail = trim($filter['report_business_onsite.email']);
            $sql .= " and tbl.employee_email like '%{$empEmail}%' \n";
        }
        if (isset($filter['report_business_onsite.team_id'])) {
            $teamId = trim($filter['report_business_onsite.team_id']);
            $sql .= " and teams.id = '{$teamId}' \n";
        }
        if (isset($filter['report_business_onsite.location'])) {
            $location = trim($filter['report_business_onsite.location']);
            $sql .= " and business_view.location like '%{$location}%' \n";
        }
        if (isset($filter['report_business_onsite.contacts_name'])) {
            $contactsName = trim($filter['report_business_onsite.contacts_name']);
            $sql .= " and proj_company.contacts_name like '%{$contactsName}%' \n";
        }
        if (isset($filter['report_business_onsite.company_name'])) {
            $companyName = trim($filter['report_business_onsite.company_name']);
            $sql .= " and proj_company.company_name like '%{$companyName}%' \n";
        }
        if (isset($filter['report_business_onsite.sale_name'])) {
            $saleName = trim($filter['report_business_onsite.sale_name']);
            $sql .= " and proj_company.sale_employee like '%{$saleName}%' \n";
        }

        $sql .= "  group by tbl.employee_id, tbl.group_id";
        $emplyeeOnsite = DB::select($sql);
        if (!$isExport) {
            $sql .= "  limit {$start}, {$pager['limit']}";
            return [
                'total' => count($emplyeeOnsite),
                'per_page' => $pager['limit'],
                'current_page' => (int)$pager['page'],
                'last_page' => (int)ceil(count($emplyeeOnsite)/$pager['limit']),
                'data' => DB::select($sql)
            ];
        }
        return $emplyeeOnsite;
    }
}
