<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Employee;
use Rikkei\Sales\Model\Company;
use Rikkei\Sales\Model\Customer;
use Rikkei\Resource\Model\RecruitPlan;
use Rikkei\Resource\Model\TeamFeature;
use Rikkei\Project\Model\TeamEffort;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\SaleProject;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MrBillable extends CoreModel
{
    protected $table = 'proj_billable_report';
    protected $fillable = [
        'code',
        'customer_company',
        'project_name',
        'project_code',
        'project_type',
        'team_id',
        'estimated',
        'member',
        'role',
        'effort',
        'parent_id',
        'start_at',
        'end_at',
        'status',
        'released_date',
        'price',
        'price_unit',
        'saleman',
        'is_running'
    ];

    /**
     * get billable relationship
     * @return collection
     */
    public function billables()
    {
        return $this->hasMany('\Rikkei\Project\Model\MrBillableTime', 'report_id', 'id');
    }

    /**
     * create or update data
     * @param array $data
     * @return object
     */
    public static function insertOrUpdate($data)
    {
        $item = self::where('project_name', $data['project_name'])
                ->where('member', $data['member'])
                ->where('role', $data['role'])
                ->where('start_at', $data['start_at'])
                ->where('end_at', $data['end_at'])
                ->first();
        if ($item) {
            $item->update($data);
        } else {
            $item = self::create($data);
        }
        return $item;
    }

    /**
     * get data to export template sample file
     * @param type $data
     * @return type
     */
    public static function getDataTemplate($data = [])
    {
        $teamId = $data['team_id'];
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];

        $tblProjMember = ProjectMember::getTableName();
        $tblProj = Project::getTableName();
        $tblProjTeam = TeamProject::getTableName();
        $tblEmployee = Employee::getTableName();

        return ProjectMember::from($tblProjMember . ' as pjmb')
            ->join($tblProj . ' as proj', 'pjmb.project_id', '=', 'proj.id')
            ->join($tblProjTeam . ' as pjteam', 'pjmb.project_id', '=', 'pjteam.project_id')
            ->join($tblEmployee . ' as emp', function ($join) use ($toMonth) {
                $join->on('pjmb.employee_id', '=', 'emp.id')
                    ->whereNull('emp.deleted_at')
                    ->where(function ($query) use ($toMonth) {
                        $query->whereNull('emp.leave_date')
                            ->orWhere('emp.leave_date', '>', $toMonth->toDateTimeString());
                    });
            })
            ->join(ProjQuality::getTableName() . ' as pjqty', 'proj.id', '=', 'pjqty.project_id')
            ->leftJoin(Customer::getTableName() . ' as cust', function ($join) {
                $join->on('proj.cust_contact_id', '=', 'cust.id')
                    ->whereNull('proj.deleted_at');
            })
            ->leftJoin(Company::getTableName() . ' as comp', function ($join) {
                $join->on('cust.company_id', '=', 'comp.id')
                    ->whereNull('comp.deleted_at');
            })
            ->leftJoin(SaleProject::getTableName() . ' as pjsale', 'proj.id', '=', 'pjsale.project_id')
            ->leftJoin($tblEmployee . ' as sale', 'sale.id', '=', 'pjsale.employee_id')
            ->where('pjteam.team_id', $teamId)
            ->where(function ($query) use ($fromMonth, $toMonth) {
                $query->whereBetween('pjmb.start_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()])
                    ->orWhereBetween('pjmb.end_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()]);
            })
            ->whereNotIn('proj.state', [Project::STATE_CLOSED, Project::STATE_CLOSED])
            ->where('pjmb.status', ProjectMember::STATUS_APPROVED)
            ->groupBy('pjmb.id')
            ->orderBy('proj.end_at', 'desc')
            ->orderBy('proj.name', 'asc')
            ->select(
                'pjmb.id',
                DB::raw('NULL as code'),
                'comp.company as customer_company',
                'proj.name as project_name',
                'proj.project_code_auto as project_code',
                'proj.type as project_type',
                DB::raw('SUBSTRING(emp.email, 1, LOCATE("@", emp.email) - 1) as member'),
                'pjmb.type as role',
                'proj.type_mm',
                DB::raw('IFNULL(pjqty.billable_effort, pjqty.plan_effort) as estimated'),
                'pjmb.effort',
                DB::raw('DATE(pjmb.start_at) as start_at'),
                DB::raw('DATE(pjmb.end_at) as end_at'),
                DB::raw('DATE(proj.end_at) as released_date'),
                'proj.state as status',
                DB::raw('SUBSTRING(sale.email, 1, LOCATE("@", sale.email) - 1) as saleman')
            )
            ->get();
    }

    /**
     * get billable data to export
     * @param type $data
     * @return type
     */
    public static function getDataExport($data = [], $isRunning = 0)
    {
        $teamId = $data['team_id'];
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];

        return self::with(['billables' => function ($query) {
                $query->select(
                    'report_id',
                    'billable',
                    'allocate',
                    'approved_cost',
                    'note',
                    DB::raw('DATE_FORMAT(time, "%m-%Y") as month')
                );
            }])
            ->whereHas('billables', function ($query) use ($fromMonth, $toMonth) {
                $query->whereBetween('time', [$fromMonth->toDateString(), $toMonth->toDateString()]);
            })
            ->where('is_running', $isRunning)
            ->select(
                'id',
                'code',
                'customer_company',
                'project_name',
                'project_code',
                'project_type',
                DB::raw('LOWER(member) as member'),
                'role',
                DB::raw(Project::MM_TYPE . ' as type_mm'),
                'estimated',
                'effort',
                'start_at',
                'end_at',
                'released_date',
                'status',
                DB::raw('LOWER(saleman) as saleman')
            )
            ->where('team_id', $teamId)
            ->orderBy('released_date', 'desc')
            ->orderBy('project_name', 'asc')
            ->get();
    }

    /**
     * get list bilaable
     * @return type
     */
    public function listBillables()
    {
        $billables = $this->billables;
        if ($billables->isEmpty()) {
            return [];
        }
        return $billables->lists('billable', 'month');
    }

    /**
     * get list allocate
     * @return type
     */
    public function listAllocates()
    {
        $allocates = $this->billables;
        if ($allocates->isEmpty()) {
            return [];
        }
        return $allocates->lists('allocate', 'month');
    }

    /**
     * get list project
     * @param array $data
     * @return collection
     */
    public static function getListProjects($data = [])
    {
        $teamId = $data['team_id'];
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];
        $tblProj = Project::getTableName();

        return Project::select($tblProj.'.id', $tblProj.'.name', $tblProj.'.project_code_auto as project_code')
            ->join(TeamProject::getTableName() . ' as pjteam', $tblProj . '.id', '=', 'pjteam.project_id')
            ->where(function ($query) use ($fromMonth, $toMonth, $tblProj) {
                $query->whereBetween($tblProj.'.start_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()])
                    ->orWhereBetween($tblProj.'.end_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()]);
            })
            ->where('pjteam.team_id', $teamId)
            ->whereNotIn($tblProj.'.state', [Project::STATE_CLOSED, Project::STATE_CLOSED])
            ->where($tblProj.'.status', Project::STATUS_APPROVED)
            ->groupBy($tblProj.'.id')
            ->orderBy($tblProj.'.name', 'asc')
            ->get();
    }

    /**
     * get list companies
     * @param type $data
     * @return type
     */
    public static function getListCompanies($data = [])
    {
        $teamId = $data['team_id'];
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];
        $tblCompany = Company::getTableName();
        return Company::select($tblCompany.'.company')
            ->join(Customer::getTableName() . ' as cust', $tblCompany.'.id', '=', 'cust.company_id')
            ->join(Project::getTableName() . ' as proj', 'cust.id', '=', 'proj.cust_contact_id')
            ->join(TeamProject::getTableName() . ' as pjteam', 'proj.id', '=', 'pjteam.project_id')
            ->where(function ($query) use ($fromMonth, $toMonth) {
                $query->whereBetween('proj.start_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()])
                    ->orWhereBetween('proj.end_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()]);
            })
            ->where('pjteam.team_id', $teamId)
            ->whereNotIn('proj.state', [Project::STATE_CLOSED, Project::STATE_CLOSED])
            ->where('proj.status', Project::STATUS_APPROVED)
            ->groupBy($tblCompany.'.id')
            ->orderBy($tblCompany.'.company', 'asc')
            ->lists('company')
            ->toArray();
    }

    /**
     * get list member of team in range month
     * @param type $data
     * @return type
     */
    public static function getListMembers($data = [])
    {
        $teamId = $data['team_id'];
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];

        $tblProjMember = ProjectMember::getTableName();
        $tblProj = Project::getTableName();
        $tblProjTeam = TeamProject::getTableName();
        $tblEmployee = Employee::getTableName();

        $members = ProjectMember::from($tblProjMember . ' as pjmb')
            ->join($tblProj . ' as proj', 'pjmb.project_id', '=', 'proj.id')
            ->join($tblProjTeam . ' as pjteam', 'pjmb.project_id', '=', 'pjteam.project_id')
            ->join($tblEmployee . ' as emp', function ($join) use ($toMonth) {
                $join->on('pjmb.employee_id', '=', 'emp.id')
                    ->whereNull('emp.deleted_at')
                    ->where(function ($query) use ($toMonth) {
                        $query->whereNull('emp.leave_date')
                            ->orWhere('emp.leave_date', '>', $toMonth->toDateTimeString());
                    });
            })
            ->where('pjteam.team_id', $teamId)
            ->where(function ($query) use ($fromMonth, $toMonth) {
                $query->whereBetween('proj.start_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()])
                    ->orWhereBetween('proj.end_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()]);
            })
            ->whereNotIn('proj.state', [Project::STATE_CLOSED, Project::STATE_CLOSED])
            ->where('pjmb.status', ProjectMember::STATUS_APPROVED)
            ->where('proj.status', Project::STATUS_APPROVED)
            ->groupBy('emp.id')
            ->orderBy('emp.email', 'asc')
            ->select('emp.id', 'emp.email')
            ->get();

        if ($members->isEmpty()) {
            return [];
        }
        $results = [];
        foreach ($members as $email) {
            $results[] = ucfirst(preg_replace('/@.*/', '', $email->email));
        }
        return $results;
    }

    /**
     * get billables in range month
     * @param type $year
     * @param type $startMonth
     * @param type $endMonth
     * @return type
     */
    public static function getBillablesPlanning($year, $startMonth, $endMonth)
    {
        $dateStart = $year . '-' . ($startMonth < 10 ? '0' . $startMonth : $startMonth) . '-01';
        $dateEnd = $year . '-' . ($endMonth < 10 ? '0' . $endMonth : $endMonth) . '-01';
        $collection = self::select(
            'billable.team_id',
            DB::raw('YEAR(billtime.time) as year'),
            DB::raw('DATE_FORMAT(billtime.time, "%m") as month'),
            DB::raw('DATE_FORMAT(billtime.time, "%Y-%m") as month_year'),
            DB::raw('SUM(CASE WHEN billable.parent_id IS NULL '
                    . 'AND billable.is_running = 0 THEN billtime.billable END) as total_billable'),
            DB::raw('SUM(CASE WHEN billable.parent_id IS NULL THEN billtime.approved_cost END) as total_approved_cost')
        )
        ->from(self::getTableName() . ' as billable')
        ->join(MrBillableTime::getTableName() . ' as billtime', 'billable.id', '=', 'billtime.report_id')
        ->whereBetween('billtime.time', [$dateStart, $dateEnd])
        ->groupBy('team_id', 'month_year')
        ->get();

        if ($collection->isEmpty()) {
            return [
                'billables' => [],
                'approved_cost' => []
            ];
        }
        $arrayBillables = [];
        $arrayApprovedCost = [];
        $collection = $collection->groupBy('team_id');
        foreach ($collection as $teamId => $billables) {
            if (!$billables->isEmpty()) {
                $arrayBillables[$teamId] = [];
                foreach ($billables as $item) {
                    $arrayBillables[$teamId][intval($item->month)] = $item->total_billable;
                    $arrayApprovedCost[$teamId][intval($item->month)] = $item->total_approved_cost;
                }
            }
        }
        return [
            'billables' => $arrayBillables,
            'approved_cost' => $arrayApprovedCost
        ];
    }

    /*
     * get list hr plan
     */
    public static function getListHrData($data = [])
    {
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];
        $hrPlans = RecruitPlan::select(
            DB::raw('CONCAT(IF(plan.month < 10, CONCAT("0", plan.month), plan.month), "-", year) as month_year'),
            'plan.number',
            'team.team_alias as team_id'
        )
            ->from(RecruitPlan::getTableName() . ' as plan')
            ->join(TeamFeature::getTableName() . ' as team', 'plan.team_id', '=', 'team.id')
            ->where(function ($query) use ($fromMonth) {
                $query->where('plan.year', '>', $fromMonth->year)
                        ->orWhere(function ($query1) use ($fromMonth) {
                            $query1->where('plan.year', '=', $fromMonth->year)
                                ->where('plan.month', '>=', $fromMonth->month);
                        });
            })
            ->where(function ($query) use ($toMonth) {
                $query->where('plan.year', '<', $toMonth->year)
                        ->orWhere(function ($query1) use ($toMonth) {
                            $query1->where('plan.year', '=', $toMonth->year)
                                ->where('plan.month', '<=', $toMonth->month);
                        });
            })
            ->whereNotNull('team.team_alias')
            ->orderBy('month_year', 'asc')
            ->get();
        if ($hrPlans->isEmpty()) {
            return [];
        }
        $dataPlans = [];
        foreach ($hrPlans as $plan) {
            $idxMonth = $plan->month_year;
            if (!isset($dataPlans[$idxMonth])) {
                $dataPlans[$idxMonth] = [];
            }
            if (!isset($dataPlans[$idxMonth][$plan->team_id])) {
                $dataPlans[$idxMonth][$plan->team_id] = $plan->number;
            } else {
                $dataPlans[$idxMonth][$plan->team_id] += $plan->number;
            }
        }
        $hrActuals = TeamEffort::getEffortData($data);
        $dataActuals = [];
        if (!$hrActuals->isEmpty()) {
            $dataActuals = $hrActuals->lists('effort_data', 'month')->toArray();
        }
        return ['plan' => $dataPlans, 'actual' => $dataActuals];
    }

    /**
     * get total billable by team
     * @return type
     */
    public static function getTotalBillableOfTeam($data = [])
    {
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];
        $collect = self::from(self::getTableName() . ' as mrb')
            ->join(MrBillableTime::getTableName() . ' as mrtime', 'mrb.id', '=', 'mrtime.report_id')
            ->whereBetween('mrtime.time', [$fromMonth->toDateString(), $toMonth->toDateString()])
            ->groupBy('mrb.team_id', 'month_year')
            ->select(
                'mrb.team_id',
                DB::raw('DATE_FORMAT(mrtime.time, "%m-%Y") as month_year'),
                DB::raw('SUM(mrtime.billable) as total_budget')
            )
            ->get();
        if ($collect->isEmpty()) {
            return [];
        }
        $result = [];
        foreach ($collect as $item) {
            if (!isset($result[$item->team_id])) {
                $result[$item->team_id] = [];
            }
            $result[$item->team_id][$item->month_year] = $item->total_budget;
        }
        return $result;
    }

    /**
     * get availabel project member
     * @return collection
     */
    public static function getAvailbleData()
    {
        $tblProjMember = ProjectMember::getTableName();
        $tblProj = Project::getTableName();
        $tblProjTeam = TeamProject::getTableName();
        $tblEmployee = Employee::getTableName();

        return ProjectMember::from($tblProjMember . ' as pjmb')
            ->join($tblProj . ' as proj', 'pjmb.project_id', '=', 'proj.id')
            ->join($tblProjTeam . ' as pjteam', 'pjmb.project_id', '=', 'pjteam.project_id')
            ->join($tblEmployee . ' as emp', function ($join) {
                $join->on('pjmb.employee_id', '=', 'emp.id')
                    ->whereNull('emp.deleted_at');
            })
            ->join(ProjQuality::getTableName() . ' as pjqty', 'proj.id', '=', 'pjqty.project_id')
            ->leftJoin(Customer::getTableName() . ' as cust', function ($join) {
                $join->on('proj.cust_contact_id', '=', 'cust.id')
                    ->whereNull('proj.deleted_at');
            })
            ->leftJoin(Company::getTableName() . ' as comp', function ($join) {
                $join->on('cust.company_id', '=', 'comp.id')
                    ->whereNull('comp.deleted_at');
            })
            ->leftJoin(SaleProject::getTableName() . ' as pjsale', 'proj.id', '=', 'pjsale.project_id')
            ->leftJoin($tblEmployee . ' as sale', 'sale.id', '=', 'pjsale.employee_id')
            ->whereNotIn('proj.state', [Project::STATE_CLOSED, Project::STATE_CLOSED])
            ->where('pjmb.status', ProjectMember::STATUS_APPROVED)
            ->groupBy('pjmb.id')
            ->orderBy('proj.end_at', 'desc')
            ->orderBy('proj.name', 'asc')
            ->select(
                'pjmb.id',
                'comp.company as customer_company',
                'proj.name as project_name',
                'proj.project_code_auto as project_code',
                'proj.type as project_type',
                DB::raw('SUBSTRING(emp.email, 1, LOCATE("@", emp.email) - 1) as member'),
                'pjmb.type as role',
                'proj.type_mm',
                DB::raw('IFNULL(pjqty.billable_effort, pjqty.plan_effort) as estimated'),
                'pjmb.effort',
                DB::raw('DATE(pjmb.start_at) as start_at'),
                DB::raw('DATE(pjmb.end_at) as end_at'),
                DB::raw('DATE(proj.end_at) as released_date'),
                'proj.state as status',
                DB::raw('SUBSTRING(sale.email, 1, LOCATE("@", sale.email) - 1) as saleman')
            )
            ->get();
    }
}

