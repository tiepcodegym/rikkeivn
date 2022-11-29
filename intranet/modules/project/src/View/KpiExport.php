<?php

namespace Rikkei\Project\View;

use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\CoreQB;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\View\getOptions;

class KpiExport
{
    /**
     * get data
     *
     * @param date $from
     * @param date $to
     * @return boolean
     */
    public function getData($from, $to)
    {
        $teams = $this->getTeams();
        if (isset($teams['leaf'])) {
            $teams = array_keys($teams['leaf']);
        } else {
            $teams = [];
        }
        $this->from = $from->modify('-1 day')->endOfDay();
        $this->to = $to->modify('+1 day')->startOfDay();
        $this->fromString = $from->__toString();
        $this->toString = $to->__toString();
        $response = [];
        $response['projs'] = $this->queryBaseline();
        $response['hrOutNotTried'] = $this->queryEmplOutNotTried();
        $response['hrOutTry'] = $this->queryEmplOutTry();
        $response['hrJoin'] = $this->queryEmplJoin();
        $response['hrTeams'] = $this->queryEmplTeams($teams);
        $response['hrOutAll'] = $this->queryEmplOutAll();
        $response['hrEmplInfo'] = $this->queryEmplOutInfo();
        $response['hrEmplJoinInfo'] = $this->queryEmplJoinInfo();
        return $response;
    }

    /**
     * query baseline all project
     *
     * @return array
     */
    protected function queryBaseline()
    {
        $query = '
SELECT 
proj.id, proj.name, 
bl.cost_billable_effort as `cost_bill`,
bl.cost_plan_effort_total as `cost_plan_total`,
bl.cost_plan_effort_total_point as `cost_plan_point`,
bl.cost_plan_effort_current as `cost_plan_current`,
bl.cost_resource_allocation_total as `cost_resource_total`,
bl.cost_resource_allocation_current as `cost_resource_current`,
bl.cost_actual_effort as `cost_actual`,
bl.cost_effort_effectiveness as `cost_effectiveness`,
bl.cost_effort_effectiveness_point as `cost_effec_point`,
bl.cost_effort_efficiency2 as `cost_effi`,
bl.cost_effort_efficiency2_point as `cost_effi_point`,
bl.cost_busy_rate as `cost_busy_rate`,
bl.cost_busy_rate_point as `cost_busy_rate_point`,
bl.qua_leakage_errors as `qua_number_leakage`,
bl.qua_leakage as `qua_leakage_value`,
bl.qua_leakage_point as `qua_leakage_point`,
bl.qua_defect_errors as `qua_number_defect`,
bl.qua_defect as `qua_defect_value`,
bl.qua_defect_point as `qua_defect_point`,
bl.tl_schedule as `tl_sche_value`,
bl.tl_schedule_point as `tl_sche_point`,
bl.tl_deliver as `tl_deli_value`,
bl.tl_deliver_point as `tl_deli_point`,
bl.proc_compliance as `pro_nc_value`,
bl.proc_compliance_point as `pro_nc_point`,
bl.proc_report as `pro_report_value`,
bl.proc_report_point as `pro_report_point`,
bl.css_css as `css_value`,
bl.css_css_point as `css_point`,
bl.point_total as `sumary_point`,
bl.created_at as `baseline_at`,
emp.name as pm_name, emp.email as pm_email,
proj.type as `type`,
proj.start_at as `start_date`,
proj.end_at as `end_date`,
GROUP_CONCAT(DISTINCT(programming_languages.name) SEPARATOR ", ") as `pl`,
GROUP_CONCAT(DISTINCT(teams.id) SEPARATOR "-") as `team_ids`,
proj.type_mm
from proj_point_baselines as bl
join projs as proj on proj.id = bl.project_id
join (
    select max(bl_tmp.id) as bl_tmp_id 
    from proj_point_baselines as bl_tmp 
    join projs as proj_tmp on proj_tmp.id = bl_tmp.project_id
    where date(bl_tmp.created_at) > "'.$this->fromString.'" and
    date(bl_tmp.created_at) < "'.$this->toString.'" and
    proj_tmp.state = 4 and
    proj_tmp.type in (1,2)
    group by proj_tmp.id
) as bl_max_tmp on bl_max_tmp.bl_tmp_id = bl.id
join employees as emp on emp.id = proj.manager_id
left join proj_prog_langs on proj_prog_langs.project_id = proj.id
left join programming_languages on programming_languages.id = proj_prog_langs.prog_lang_id
left join team_projs on team_projs.project_id = proj.id
left join teams on teams.id = team_projs.team_id
where date(bl.created_at) > "'.$this->from->__toString().'" and
date(bl.created_at) < "'.$this->to->__toString().'" and
proj.state = 4 and
proj.type in (1,2)
group by proj.id
order by proj.id asc';
        return DB::select($query);
    }

    /**
     * count employee of team not tried work
     */
    protected function queryEmplOutNotTried()
    {
        $query = '
select COUNT(*) as count, t_tm.team_id,
    Date_format(employees.leave_date, "%Y-%m") as month
from employees
join (select employee_id, team_id from team_members group by employee_id) as t_tm 
on t_tm.employee_id = employees.id
join teams on t_tm.team_id = teams.id
join employee_works as t_ew on t_ew.employee_id = employees.id and t_ew.deleted_at is null
where employees.leave_date is not null
    AND date(employees.leave_date) > "'.$this->fromString.'"
AND date(employees.leave_date) < "'.$this->toString.'"
AND (employees.offcial_date is not null AND date(employees.leave_date) >= date(employees.offcial_date))
AND t_ew.contract_type != '.getOptions::WORKING_BORROW.' 
AND teams.deleted_at is null
AND employees.deleted_at is null
GROUP BY team_id, month
ORDER BY team_id asc, month asc
';
        return DB::select($query);
    }

    /**
     * count employee of team try work leave
     */
    protected function queryEmplOutTry()
    {
        $query = '
select COUNT(*) as count, t_tm.team_id,
    Date_format(employees.leave_date, "%Y-%m") as month
from employees
join (select employee_id, team_id from team_members group by employee_id) as t_tm 
on t_tm.employee_id = employees.id
join teams on t_tm.team_id = teams.id
join employee_works as t_ew on t_ew.employee_id = employees.id and t_ew.deleted_at is null
where employees.leave_date is not null
    AND date(employees.leave_date) > "'.$this->fromString.'"
AND date(employees.leave_date) < "'.$this->toString.'"
AND (employees.offcial_date is null OR date(employees.leave_date) < date(employees.offcial_date))
AND t_ew.contract_type != '.getOptions::WORKING_BORROW.' 
AND teams.deleted_at is null
AND employees.deleted_at is null
GROUP BY team_id, month
ORDER BY team_id asc, month asc
';
        return DB::select($query);
    }

    /**
     * count employee of team all status
     */
    protected function queryEmplOutAll()
    {
        $query = '
select COUNT(*) as count, t_tm.team_id,
    Date_format(employees.leave_date, "%Y-%m") as month
from employees
join (select employee_id, team_id from team_members group by employee_id) as t_tm  
on t_tm.employee_id = employees.id
join teams on t_tm.team_id = teams.id
join employee_works as t_ew on t_ew.employee_id = employees.id and t_ew.deleted_at is null
where employees.leave_date is not null
AND date(employees.leave_date) > "'.$this->fromString.'"
AND date(employees.leave_date) < "'.$this->toString.'"
AND t_ew.contract_type != '.getOptions::WORKING_BORROW.' 
AND teams.deleted_at is null
AND employees.deleted_at is null
GROUP BY team_id, month
ORDER BY team_id asc, month asc
';
        return DB::select($query);
    }

    /**
     * count employee of team not tried work
     */
    protected function queryEmplJoin()
    {
        $query = '
select COUNT(*) as count, t_tm.team_id,
    Date_format(employees.join_date, "%Y-%m") as month
from employees
join (select employee_id, team_id from team_members group by employee_id) as t_tm 
on t_tm.employee_id = employees.id
join teams on t_tm.team_id = teams.id
join employee_works as t_ew on t_ew.employee_id = employees.id and t_ew.deleted_at is null
where employees.join_date is not null
AND date(employees.join_date) > "'.$this->fromString.'"
AND date(employees.join_date) < "'.$this->toString.'"
AND t_ew.contract_type != '.getOptions::WORKING_BORROW.' 
AND teams.deleted_at is null
AND employees.deleted_at is null
GROUP BY t_tm.team_id, month
ORDER BY t_tm.team_id asc, month asc
';
        return DB::select($query);
    }

    /**
     * count employee of team not tried work
     */
    protected function queryEmplTeams($teams)
    {
        $coreQB = new CoreQB();
        $dataQB = $coreQB->convertArrayData($teams);
        $query = '
select COUNT(DISTINCT(t_eth.employee_id)) as count, t_eth.team_id,
    Date_format(t_eth.start_at, "%Y-%m") as start_at,
    Date_format(t_eth.end_at, "%Y-%m") as end_at
from employee_team_history as t_eth
join employees on t_eth.employee_id = employees.id
join employee_works as t_ew on t_ew.employee_id = employees.id and t_ew.deleted_at is null
where employees.join_date is not null
AND employees.join_date < "'.$this->toString.'"
AND employees.deleted_at is null
AND (start_at < "'.$this->toString.'" OR start_at is null)
AND (end_at > "'.$this->fromString.'" OR end_at is null)
AND t_eth.team_id IN '.$dataQB['data'].'
AND t_ew.contract_type != '.getOptions::WORKING_BORROW.' 
GROUP BY team_id, start_at, end_at
ORDER BY t_eth.team_id asc, start_at asc, end_at asc
';
        return DB::select($query);
    }

    /**
     * count employee of team not tried work
     */
    protected function queryEmplOutInfo()
    {
        $query = '
select t_tm.team_id, employees.name,
employees.email, Date_format(employees.leave_date, "%Y-%m-%d") as date,
employees.leave_reason as reason
from employees
join (select employee_id, team_id from team_members group by employee_id) as t_tm 
on t_tm.employee_id = employees.id
join teams on t_tm.team_id = teams.id
join employee_works as t_ew on t_ew.employee_id = employees.id and t_ew.deleted_at is null
where employees.leave_date is not null
AND date(employees.leave_date) > "'.$this->fromString.'"
AND date(employees.leave_date) < "'.$this->toString.'"
AND t_ew.contract_type != '.getOptions::WORKING_BORROW.' 
AND teams.deleted_at is null
AND employees.deleted_at is null
ORDER BY team_id asc, date asc
';
        return DB::select($query);
    }

    /**
     * count employee of team not tried work
     */
    protected function queryEmplJoinInfo()
    {
        $query = '
select t_tm.team_id, employees.name, employees.email, 
Date_format(employees.join_date, "%Y-%m-%d") as date
from employees
join (select employee_id, team_id from team_members group by employee_id) as t_tm 
on t_tm.employee_id = employees.id
join teams on t_tm.team_id = teams.id
join employee_works as t_ew on t_ew.employee_id = employees.id and t_ew.deleted_at is null
where employees.join_date is not null
AND date(employees.join_date) > "'.$this->fromString.'"
AND date(employees.join_date) < "'.$this->toString.'"
AND t_ew.contract_type != '.getOptions::WORKING_BORROW.' 
AND teams.deleted_at is null
AND employees.deleted_at is null
ORDER BY t_tm.team_id asc, date asc
';
        return DB::select($query);
    }

    /**
     * get division
     */
    public function getTeams()
    {
        return TeamList::getTeamLeaf();
    }
}
