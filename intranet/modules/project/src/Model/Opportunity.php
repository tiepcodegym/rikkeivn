<?php

namespace Rikkei\Project\Model;

use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\ProjQuality;
use Rikkei\Project\Model\ProjectMeta;
use Rikkei\Project\Model\ProjectMember;
use Rikkei\Resource\Model\Programs;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\Employee;
use Rikkei\Sales\Model\Customer;
use Rikkei\Project\View\ProjDbHelp;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Sales\Model\Company;
use Carbon\Carbon;

class Opportunity extends Project
{
    protected $fillable = ['cust_contact_id', 'name', 'manager_id', 'leader_id', 'start_at', 'state', 'end_at', 'type', 'type_mm', 'status', 'created_by', 'description'];

    /**
     * get grid data
     */
    public static function getOpportunity()
    {
        $pager = Config::getPagerData();
        $opTbl = self::getTableName();
        $collection = self::select(
            $opTbl . '.id',
            $opTbl . '.name',
            'quality.approved_cost',
            'quality.billable_effort',
            $opTbl . '.type_mm',
            DB::raw('DATE('. $opTbl . '.start_at) as start_date'),
            DB::raw('DATE('. $opTbl . '.end_at) as end_date'),
            DB::raw('GROUP_CONCAT(DISTINCT(team.name) SEPARATOR ", ") as team_names'),
            'cust.name as cust_name',
            DB::raw('GROUP_CONCAT(DISTINCT(sale.email)) as sale_emails'),
            'creator.email as creator_email'
        )
            ->join(TeamProject::getTableName() . ' as teampj', $opTbl . '.id', '=', 'teampj.project_id')
            ->join(Team::getTableName() . ' as team', 'teampj.team_id', '=', 'team.id')
            ->join(ProjQuality::getTableName() . ' as quality', function ($join) use ($opTbl) {
                $join->on($opTbl . '.id', '=', 'quality.project_id')
                        ->where('quality.status', '=', ProjQuality::STATUS_APPROVED);
            })
            ->leftJoin(SaleProject::getTableName() . ' as salepj', $opTbl . '.id', '=', 'salepj.project_id')
            ->leftJoin(Employee::getTableName() . ' as sale', 'salepj.employee_id', '=', 'sale.id')
            ->leftJoin(Employee::getTableName() . ' as creator', $opTbl . '.created_by', '=', 'creator.id')
            ->leftJoin(Customer::getTableName() . ' as cust', $opTbl . '.cust_contact_id', '=', 'cust.id')
            ->where($opTbl . '.status', self::STATUS_OPPORTUNITY)
            ->groupBy($opTbl . '.id');
        //permission
        if (Permission::getInstance()->isScopeCompany()) {
            //get all
        } elseif (Permission::getInstance()->isScopeTeam()) {
            $currUser = Permission::getInstance()->getEmployee();
            $teamIds = TeamMember::where('employee_id', $currUser->id)
                    ->lists('team_id')
                    ->toArray();
            $collection->join(TeamProject::getTableName() . ' as teampm', $opTbl . '.id', '=', 'teampm.project_id')
                    ->where(function ($query) use ($teamIds, $opTbl, $currUser) {
                        $query->whereIn('teampm.team_id', $teamIds)
                                ->orWhere($opTbl . '.created_by', $currUser->id);
                    });
        } elseif (Permission::getInstance()->isScopeSelf()) {
            $collection->where($opTbl . '.created_by', Permission::getInstance()->getEmployee()->id);
        } else {
            //none permission
        }
        //filter grid
        self::filterGrid($collection, [], null, 'LIKE');
        //sort order
        if (CoreForm::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy($opTbl.'.created_at', 'desc');
        }
        //paginate
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    public static function checkExists($input) {
        if ($input['projectId']) {
            return self::where($input['name'], $input['value'])
                            ->where('status', self::STATUS_OPPORTUNITY)
                            ->whereNotIn('id', [$input['projectId']])->count() == Project::PROJECT_EXISTS ? 'true' : 'false';
        } else {
            return self::where($input['name'], $input['value'])
                            ->where('status', self::STATUS_OPPORTUNITY)
                            ->count() == Project::PROJECT_EXISTS ? 'true' : 'false';
        }
    }

    /**
     * insert or update opportunity
     * @param type $data
     * @param type $id
     * @return type
     */
    public static function insertOrUpdate($data, $id = null)
    {
        $data['status'] = self::STATUS_OPPORTUNITY;
        if ($id) {
            $opportunity = self::find($id);
            if ($opportunity) {
                $data = array_only($data, $opportunity->getFillable());
                $opportunity->update($data);
                return $opportunity;
            }
        }
        $currUserId = auth()->id();
        $data['created_by'] = $currUserId;
        $data['type'] = self::TYPE_OPPORTUNITY;
        $data['state'] = self::STATE_OPPORTUNITY;
        $data['manager_id'] = $currUserId;
        $data['leader_id'] = $currUserId;
        $opportunity = self::create($data);
        $opportunity->renderProjectCodeAuto();
        return $opportunity;
    }

    /**
     * insert or update quality
     * @param type $data
     * @param type $id
     * @return type
     */
    public static function insertOrupdateQuality($data, $id)
    {
        $quality = ProjQuality::where('project_id', $id)
                ->where('status', ProjQuality::STATUS_APPROVED)
                ->first();
        $nullFields = ['billable_effort', 'cost_approved_production', 'approved_cost'];
        foreach ($nullFields as $field) {
            if (!isset($data[$field]) || !$data[$field]) {
                $data[$field] = null;
            }
        }
        if ($quality) {
            $quality->update(array_only($data, $quality->getFillable()));
            return $quality;
        }
        $data['project_id'] = $id;
        $data['status'] = ProjQuality::STATUS_APPROVED;
        $data['created_by'] = auth()->id();
        return ProjQuality::create($data);
    }

    /**
     * get quality
     * @return type
     */
    public function quality()
    {
        return $this->hasOne('\Rikkei\Project\Model\ProjQuality', 'project_id', 'id')
                ->where('status', ProjQuality::STATUS_APPROVED);
    }

    /**
     * get scope & object
     * @return type
     */
    public function scopeObject()
    {
        return $this->hasOne('Rikkei\Project\Model\ProjectMeta', 'project_id');
    }

    /**
     * insert or update scope object
     * @param type $data
     * @param type $oppId
     * @return ProjectMeta
     */
    public static function insertOrUpdateScope($data, $oppId)
    {
        $scopeObject = null;
        $opportunity = self::find($oppId);
        if ($opportunity) {
            $scopeObject = $opportunity->scopeObject;
        }
        if ($scopeObject) {
            $scopeObject->setData($data);
            $scopeObject->save();
        } else {
            $data['project_id'] = $oppId;
            $scopeObject = new ProjectMeta();
            $scopeObject->setData($data);
            $scopeObject->save();
        }
        return $scopeObject;
    }

    /**
     * get tab content
     * @param type $data
     * @return boolean
     */
    public static function getTabContent($data)
    {
        $response = [];
        $response['status'] = true;
        $project = self::getProjectById($data['projectId']);
        if (!$project) {
            $response['status'] = false;
            return $response;
        }

        switch ($data['type']) {
            case 'team_allocation':
                $response['data']['member'] = ProjectMember::getAllMemberAvai($project);
                $response['data']['lang'] = Programs::getListOption();
                $response['data']['type'] = ProjectMember::getTypeMember();
                $response['data']['team'] = ProjDbHelp::getTeamOfMembers($response['data']['member']);
                break;
            case 'risk':
                $response['content'] = Risk::getContentTable($project);
                break;
            default:
                break;
        }
        return $response;
    }

    /**
     * git list opportunity in month
     * @param type $month
     * @param type $year
     * @return type
     */
    public static function getBillalbeEffortInMonth($month, $year)
    {
        $dateStart = Carbon::now()->setDate($year, $month, 1)->startOfMonth();
        $dateEnd = Carbon::now()->setDate($year, $month, 1)->endOfMonth();
        $oppTbl = self::getTableName();

        return self::select(
            'teampj.team_id',
            DB::raw('SUM(CASE WHEN '.$oppTbl.'.type_mm = '.self::MD_TYPE.' THEN '
                    . 'quality.billable_effort/20 ELSE quality.billable_effort END) as total_billable_effort'),
            DB::raw('SUM(CASE WHEN '.$oppTbl.'.type_mm = '.self::MD_TYPE.' THEN '
                    . 'quality.cost_approved_production/20 ELSE quality.cost_approved_production END) as total_cost_approved_production'),
            DB::raw('SUM(CASE WHEN '.$oppTbl.'.type_mm = '.self::MD_TYPE.' THEN '
                    . 'quality.approved_cost/20 ELSE quality.approved_cost END) as total_approved_cost')
        )
            ->leftJoin(ProjQuality::getTableName() . ' as quality', $oppTbl.'.id', '=', 'quality.project_id')
            ->join(TeamProject::getTableName() . ' as teampj', $oppTbl.'.id', '=', 'teampj.project_id')
            ->where(DB::raw("DATE({$oppTbl}.start_at)"), '<=', $dateEnd->toDateString())
            ->where(DB::raw("DATE({$oppTbl}.end_at)"), '>=', $dateStart->toDateString())
            ->where($oppTbl.'.status', self::STATUS_OPPORTUNITY)
            ->groupBy('teampj.team_id')
            ->get();
    }

    public static function labelTypeProject()
    {
        return parent::labelTypeProject() + [self::STATUS_OPPORTUNITY => 'Opportunity'];
    }

    /**
     * get data export excel file
     * @param type $data
     * @param type $status
     * @return type
     */
    public static function getDataExport($data = [], $status = null)
    {
        $teamId = $data['team_id'];
        $fromMonth = $data['from_month'];
        $toMonth = $data['to_month'];

        $collection = ProjectMember::from(ProjectMember::getTableName() . ' as pjmb')
            ->join(self::getTableName() . ' as proj', 'pjmb.project_id', '=', 'proj.id')
            ->join(TeamProject::getTableName() . ' as pjteam', 'pjmb.project_id', '=', 'pjteam.project_id')
            ->join(Employee::getTableName() . ' as emp', function ($join) use ($toMonth) {
                $join->on('pjmb.employee_id', '=', 'emp.id')
                    ->whereNull('emp.deleted_at')
                    ->where(function ($query) use ($toMonth) {
                        $query->whereNull('emp.leave_date')
                            ->orWhere('emp.leave_date', '>', $toMonth->toDateTimeString());
                    });
            })
            ->leftJoin(ProjQuality::getTableName() . ' as pjqty', 'proj.id', '=', 'pjqty.project_id')
            ->leftJoin(Customer::getTableName() . ' as cust', function ($join) {
                $join->on('proj.cust_contact_id', '=', 'cust.id')
                    ->whereNull('proj.deleted_at');
            })
            ->leftJoin(Company::getTableName() . ' as comp', function ($join) {
                $join->on('cust.company_id', '=', 'comp.id')
                    ->whereNull('comp.deleted_at');
            })
            ->leftJoin(SaleProject::getTableName() . ' as pjsale', 'proj.id', '=', 'pjsale.project_id')
            ->leftJoin(Employee::getTableName() . ' as sale', 'sale.id', '=', 'pjsale.employee_id')
            ->where('pjteam.team_id', $teamId)
            ->where(function ($query) use ($fromMonth, $toMonth) {
                $query->whereBetween('pjmb.start_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()])
                    ->orWhereBetween('pjmb.end_at', [$fromMonth->toDateTimeString(), $toMonth->toDateTimeString()]);
            })
            ->whereNotIn('proj.state', [self::STATE_CLOSED, self::STATE_REJECT])
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
                DB::raw('SUBSTRING(sale.email, 1, LOCATE("@", sale.email) - 1) as saleman'),
                'pjqty.cost_approved_production',
                'pjqty.approved_cost',
                'proj.description'
            );
        if ($status) {
            $collection->where('proj.status', $status);
        } else {
            $collection->whereNotIn('proj.status', [self::STATUS_OPPORTUNITY]);
        }
        return $collection->get();
    }
}

