<?php

namespace Rikkei\Resource\View;

use Rikkei\Core\View\CoreQB;
use Carbon\Carbon;
use DatePeriod;
use DateInterval;
use Rikkei\Core\View\OptionCore;

class BusyHelper
{
    protected $coreQB;
    protected $filter;

    /**
     * constructor
     */
    public function __construct($filter)
    {
        OptionCore::setMemoryMax();
        $this->coreQB = new CoreQB();
        $this->filter = $filter;
        $this->filter['start'] = Carbon::parse($filter['start']);
        $this->filter['end'] = Carbon::parse($filter['end']);
        $this->resetCount();
    }

    /**
     * main query select all member of company follow filter
     */
    protected function mainQuery()
    {
        $bindings = [];
        $mainQuery = ''.
'SELECT employees.id as employee_id, employees.email, employees.name,
t_empl_effort.max_effort,
GROUP_CONCAT(DISTINCT teams.name SEPARATOR "-") AS teams
FROM employees
join team_members on team_members.employee_id = employees.id and team_members.role_id = 3
join teams on teams.id = team_members.team_id and teams.is_soft_dev = 1
    and teams.deleted_at is null ';

    // filter team
    if (isset($this->filter['t']) && $this->filter['t']) {
        $mainQuery .= ' and teams.id in ('
            . $this->coreQB->convertArraySymPDO($this->filter['t']) . ') ';
        $bindings = $this->filter['t'];
    }
    // filter language
    if (isset($this->filter['s']) && $this->filter['s']) {
        $querySkills = ' join employee_skill_levels as t_es on t_es.employee_id = employees.id and (';
        $isFilterTag = false;
        $bindSkills = [];
        foreach ($this->filter['s'] as $index => $tagId) {
            if (!$tagId) {
                continue;
            }
            $isFilterTag = true;
            $querySkills .= '(t_es.tag_id = ? ';
            $bindSkills[] = $tagId;
            if (isset($this->filter['e'][$index]) && $this->filter['e'][$index]) {
                $querySkills .= 'and t_es.exp_y >= ? ';
                $bindSkills[] = $this->filter['e'][$index];
            }
            $querySkills .= ') or ';
        }
        $querySkills = substr($querySkills, 0, -3);
        $querySkills .= ') ';
        if ($isFilterTag) {
            $mainQuery .= $querySkills;
            $bindings = array_merge($bindings, $bindSkills);
        }
    }

    // main table
    $mainQuery .=
'left join (
    SELECT employee_id, MAX(effort) as max_effort FROM `project_members` 
    WHERE status = 1 AND date(project_members.start_at) >= ? 
    AND date(project_members.end_at) <= ? GROUP by employee_id
) as t_empl_effort ON t_empl_effort.employee_id = employees.id
where employees.deleted_at is NULL and employees.leave_date is null
GROUP BY employees.id
ORDER by t_empl_effort.max_effort ASC, t_empl_effort.employee_id ASC ';
            //. $this->coreQB->getLimitOffset();
        $bindings[] = $this->filter['start']->format('Y-m-d');
        $bindings[] = $this->filter['end']->format('Y-m-d');
        return [
            'query' => $mainQuery,
            'b' => $bindings
        ];
    }

    /**
     * query string
     *
     * @return array
     */
    protected function query()
    {
        // main query - filter in this subquery
        $mainQuery = $this->mainQuery();
        $query = '' . 
'SELECT t_e_me.*, project_members.start_at, project_members.end_at, project_members.effort 
FROM ('.$mainQuery['query'].') as t_e_me
left join project_members on project_members.employee_id = t_e_me.employee_id
AND date(project_members.start_at) >= ? AND date(project_members.end_at) <= ? 
AND project_members.status = 1
ORDER by t_e_me.max_effort ASC, t_e_me.employee_id ASC';
        $bindings = $mainQuery['b'];
        $bindings[] = $this->filter['start']->format('Y-m-d');
        $bindings[] = $this->filter['end']->format('Y-m-d');
        return $this->coreQB->execQueryString($query, $bindings);
    }

    /**
     * get all period 1 week follow start - end filter
     *
     * @return DatePeriod
     */
    protected function periods()
    {
        // get start of week - start filter and end of week - end filter
        $periodWeek = new DatePeriod($this->filter['start'], new DateInterval('P1D'), $this->filter['end']);
        $result = [];
        foreach($periodWeek as $dt) {
            $result[] = [
                'format' => $dt->format('Y-m-d'),
                'obj' => $dt
            ];
        }
        $dt->modify('+1 day');
        $result[] = [
            'format' => $dt->format('Y-m-d'),
            'obj' => $dt
        ];
        /*
        $lastMonth = clone $dt;
        $lastMonth->lastOfMonth();
        // add more 1 week to check
        if (!$dt->eq($lastMonth)) {
            $dt->modify('+1 week');
            $result[] = [
                'format' => $dt->format('Y-m-d'),
                'obj' => $dt
            ];
        }*/
        return [
            'data' => $result,
            'last' => $dt
        ];
    }

    /**
     * group employee id, employee all work in start - end filter
     *
     * @param collection $collection
     * @return array
     */
    protected function groupEmployeeId($collection)
    {
        $result = [];
        foreach ($collection as $item) {
            $key = 'e-' . $item->employee_id;
            if (!isset($result[$key]['data'])) {
                $result[$key]['data'] = [
                    'email' => $item->email,
                    'name' => $item->name,
                    'teams' => $item->teams,
                    'id' => $item->employee_id,
                ];
            }
            $result[$key]['period'][] = [
                'start' => $item->start_at,
                'end' => $item->end_at,
                'effort' => $item->effort,
                'id' => $item->employee_id,
            ];
        }
        return $result;
    }

    /**
     * get employee and period connection
     *
     * @param collection $collection
     * @param array $periods
     * @return array
     */
    protected function employeeAllPeriod($collection, $periods)
    {
        $result = [];
        $lastPeriod = $periods['last'];
        $periods = $periods['data'];
        $this->resetCount();
        foreach ($collection as $emplyeeId => $employeeGroup) {
            $result[$emplyeeId]['data'] = $employeeGroup['data'];
            $result[$emplyeeId]['period'] = [];
            $lastTimeEmployee = null;
            $lastTimeIndexEmployee = 0;
            $isEffort = [
                1 => 0, // white
                2 => 0, // yellow
                3 => 0, // green
                4 => 0, // red
            ];
            foreach ($employeeGroup['period'] as $employeeData) {
                // employee not in any project
                if ($employeeData['effort'] === null ||
                    $employeeData['start'] === null ||
                    $employeeData['end'] === null
                ) {
                    foreach ($periods as $period) {
                        $result[$emplyeeId]['period'][$period['format']] = 0;
                    }
                    break;
                }
                $emplStart = Carbon::parse($employeeData['start']);
                $emplEnd = Carbon::parse($employeeData['end']);
                foreach ($periods as $periodIndex => $period) {
                    if (!isset($result[$emplyeeId]['period'][$period['format']])) {
                        $result[$emplyeeId]['period'][$period['format']] = 0;
                    }
                    // last period => break
                    if (!isset($periods[$periodIndex + 1])) {
                        break;
                    }
                    // period > end of employee project -> break
                    if ($period['obj']->gt($emplEnd)) {
                        break;
                    }
                    // period < start of employee project -> continue period
                    if ($periods[$periodIndex + 1]['obj']->lte($emplStart)) {
                        continue;
                    }
                    $result[$emplyeeId]['period'][$period['format']] += $employeeData['effort'];
                    if (!$isEffort[4]) { // if max efffort > 100, not check
                        $isEffort[$this->getTypeEffort($result[$emplyeeId]['period'][$period['format']])] = 1;
                    }
                }
                if ($periodIndex > $lastTimeIndexEmployee) {
                    $lastTimeIndexEmployee = $periodIndex + 1;
                    $lastTimeEmployee = $period['obj'];
                }
            }
            if ($lastTimeEmployee && $lastTimeEmployee->lt($lastPeriod)) {
                foreach (array_slice($periods, $lastTimeIndexEmployee) as $period) {
                    if (!isset($result[$emplyeeId]['period'][$period['format']])) {
                        $result[$emplyeeId]['period'][$period['format']] = 0;
                    }
                }
            }
            $this->count[$this->getTypeMaxEffort($isEffort)]++;
        }
        return $result;
    }

    /**
     * exec data
     *
     * @return type
     */
    public function exec()
    {
        $collection = $this->query();
        if (!count($collection)) {
            return [];
        }
        return $this->employeeAllPeriod($this->groupEmployeeId($collection), $this->periods());
    }

    /**
     * reset count employee follow effort
     *
     * @return $this
     */
    public function resetCount()
    {
        $this->count = [
            1 => 0, // white
            2 => 0, // yellow
            3 => 0, // green
            4 => 0, // red
        ];
        return $this;
    }

    /**
     * get count employee
     *
     * @return array
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * get type effort - color
     *
     * @param number $effort
     * @return int
     */
    protected function getTypeEffort($effort)
    {
        if ($effort > 100) {
            return 4; // red
        }
        if ($effort > 80) {
            return 3; // green
        }
        if ($effort > 0) {
            return 2; // yellow
        }
        return 1; //whitte
    }

    /**
     * get type max effort
     *
     * @param array $types
     * @return int
     */
    protected function getTypeMaxEffort(array $types)
    {
        for ($i = 4; $i > 0; $i--) {
            if ($types[$i]) {
                return $i;
            }
        }
        return 1;
    }
}
