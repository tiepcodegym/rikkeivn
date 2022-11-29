<?php

namespace Rikkei\Core\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\Model\EmployeeBaseline;
use Rikkei\Team\View\TeamList;
use Rikkei\Resource\View\Statistics;

class StatsBaseline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:baseline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Thống kê nhân viên';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $month = Carbon::now()->month - 1;
        $year = Carbon::now()->year;
        $time = Carbon::createFromDate($year, $month, 1)->setTime(0, 0, 0);
        $arrayTeams = [
            -1 => [
                'id' => -1,
                'name' => trans('resource::view.stat.All company'),
                'parent_id' => null,
                'depth' => 0
            ]
        ];

        $arrayTeams += TeamList::sortParentChilds(TeamList::getList());
        $contracts = getOptions::listWorkingTypeInternal() + getOptions::listWorkingTypeExternal();
        $workedMonths = getOptions::listWorkedMonth();

        $statistics = Statistics::getTotal([], $time, 'month');
        $total = $statistics['total'];
        $totalTeam = $total->groupBy('team_id')->toArray();

        $teamIds = [];
        foreach ($arrayTeams as $team) {
            $teamIds[] = $team['id'];
        }

        $nestedTeamList = [];
        $teamDataList = [];
        for ($i = count($teamIds) - 1; $i >= 0; $i--) {
            $teamId = $teamIds[$i];
            $nestedTeamList[$teamId] = isset($totalTeam[$teamId]) ? $totalTeam[$teamId] : [];
            // all company
            if ($teamId === -1) {
                $nestedTeamList[$teamId] = $total->toArray();
            } elseif ($teamId !== Team::TEAM_BOD_ID) {
                // get team chidren
                foreach ($arrayTeams as $team) {
                    if ((int)$team['parent_id'] === $teamId) {
                        // remember recursive
                        if (isset($nestedTeamList[$team['id']])) {
                            $nestedTeamList[$teamId] = array_merge($nestedTeamList[$teamId], $nestedTeamList[$team['id']]);
                        } else {
                            $nestedTeamList[$teamId] = Team::getTeamDataRecursive($arrayTeams, $totalTeam, $teamId);
                        }
                    }
                }
            } else {
                // nothing
            }

            $teamData['empIds'] = [];
            $teamData['roles'] = [];
            $teamData['workedMonths'] = [];
            $teamData['contracts'] = [];
            if (count($nestedTeamList[$teamId])) {
                foreach ($nestedTeamList[$teamId] as $team) {
                    $empId = $team['id'];
                    // total employee
                    if (!in_array($empId, $teamData['empIds'])) {
                        $teamData['empIds'][] = $empId;
                    }
                    // has roles
                    if ($team['roles']) {
                        $dataRoles = json_decode($team['roles']);
                        if (is_array($dataRoles)) {
                            foreach ($dataRoles as $roleId) {
                                if (!isset($teamData['roles'][$roleId])) {
                                    $teamData['roles'][$roleId] = [];
                                }
                                if (!in_array($empId, $teamData['roles'][$roleId])) {
                                    $teamData['roles'][$roleId][] = $empId;
                                }
                            }
                        } else {
                            $roleId = $dataRoles;
                            if (!isset($teamData['roles'][$roleId])) {
                                $teamData['roles'][$roleId] = [];
                            }
                            if (!in_array($empId, $teamData['roles'][$roleId])) {
                                $teamData['roles'][$roleId][] = $empId;
                            }
                        }
                    }
                    // worked months
                    $dateJoin = Carbon::parse($team['date_join']);
                    $workedMonth = ($year - $dateJoin->year) * 12 + ($month - $dateJoin->month + 1);
                    foreach ($workedMonths as $key => $item) {
                        if (!isset($teamData['workedMonths'][$key])) {
                            $teamData['workedMonths'][$key] = [];
                        }
                        if ($workedMonth >= $item['from'] && $workedMonth < $item['to'] && !in_array($empId, $teamData['workedMonths'][$key])) {
                            $teamData['workedMonths'][$key][] = $empId;
                        }
                    }

                    // contracts
                    $contractType = $team['contract_type'];
                    foreach ($contracts as $key => $item) {
                        if (!isset($teamData['contracts'][$key])) {
                            $teamData['contracts'][$key] = [];
                        }
                        if ((int)$contractType === $key && !in_array($empId, $teamData['contracts'][$key])) {
                            $teamData['contracts'][$key][] = $empId;
                        }
                    }
                }
            }
            $teamDataList[$teamId] = $teamData;
        }

        // count employee for total, roles, worked months and contracts
        $staffStatistics = [];
        foreach ($teamDataList as $teamId => $teamData) {
            $staffStatistics[$teamId]['total'] = count($teamData['empIds']);
            foreach ($teamData['roles'] as $roleId => $role) {
                $staffStatistics[$teamId]['roles'][$roleId] = count($role);
            }
            foreach ($teamData['workedMonths'] as $monthId => $workedMonth) {
                $staffStatistics[$teamId]['workedMonths'][$monthId] = count($workedMonth);
            }
            foreach ($teamData['contracts'] as $contractType => $contract) {
                $staffStatistics[$teamId]['contracts'][$contractType] = count($contract);
            }
        }

        $employeeBaseline = new EmployeeBaseline();
        $employeeBaseline->setData([
            'month' => $time->format('Y-m'),
            'data' => serialize($staffStatistics)
        ]);
        $employeeBaseline->save();
    }
}
