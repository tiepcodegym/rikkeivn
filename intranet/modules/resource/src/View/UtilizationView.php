<?php

namespace Rikkei\Resource\View;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\View\View;
use Rikkei\Core\View\PaginatorHelp;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

class UtilizationView
{
    /**
     * Get data by view mode
     *
     * @param array $data
     * @param string $viewMode
     * @param string|date $startDate
     * @param string|date $endDate
     * @return array
     */
    public function getDataForView($data, $filter, $paginate = true)
    {
        $utilizationView = new UtilizationView();
        switch ($filter['viewMode']) {
            case 'day':
                $dataConvert = $utilizationView->viewDate($data, $filter['startDate'], $filter['endDate'], $filter['effort']);
                break;
            case 'month':
                $dataConvert = $utilizationView->viewMonth($data, $filter['startDate'], $filter['endDate'], $filter['effort']);
                break;
            default:
                $dataConvert = $utilizationView->viewWeek($data, $filter['startDate'], $filter['endDate'], $filter['effort']);
                break;
        }
        $paginator = new PaginatorHelp();
        if ($paginate) {
            return $paginator->paginate($dataConvert, $filter['limit'], $filter['page']);
        } else {
            return $dataConvert;
        }
    }

    /**
     * Get data by view mode is day
     *
     * @param array $data
     * @param string|date $startDate
     * @param string|date $endDate
     * @return array
     */
    public function viewDate($data, $startDate, $endDate, $filterEffort)
    {
        $utilizationView = new UtilizationView();
        $dates = $utilizationView->getDates($startDate, $endDate);

        $dashboard = [];
        foreach ($data as $nicknameId => $value) {
            $userInfo = explode(CoreModel::GROUP_CONCAT, $nicknameId); 
            $dashboard[$userInfo[0]]['userInfo'] = [
                'id' => $userInfo[1],
                'email' => $userInfo[0],
                'leave_date' => $userInfo[2],
                'join_date' => $userInfo[3],
                'team' => $userInfo[4],
            ];
            $totalEffort = 0;
            foreach ($dates as $date) {
                foreach ($value as $projInfo) {
                    $listDateFilter = View::getInstance()->getDays($projInfo['start_date'], $projInfo['end_date']);
                    if ($listDateFilter) {
                        $effort = in_array($date['number'], $listDateFilter) ? $projInfo['effort'] : 0;
                    } else {
                        $effort = 0;
                    }
                    $totalEffort += $effort;
                    $dashboard[$userInfo[0]]['effortInfo'][$date['number']][] = [
                        'effort' => $effort,
                        'projId' => $projInfo['proj_id'],
                        'projName' => $projInfo['proj_name'],
                        'number' => $date['number'],
                        'year'=> $date['year'],
                    ];
                }
            }
            $avgEffort = round($totalEffort/count($dates), 2);
            $this->filterByEffort($dashboard, $userInfo[0], $avgEffort, $filterEffort);
        }
        return $dashboard;
    }

    /**
     * Get data by view mode is month
     *
     * @param array $data
     * @param string|date $startDate
     * @param string|date $endDate
     * @return array
     */
    public function viewMonth($data, $startDate, $endDate, $filterEffort)
    {
        $utilizationView = new UtilizationView();
        $months = $utilizationView->getMonths($startDate, $endDate);

        $dashboard = [];
        foreach ($data as $nicknameId => $value) {
            $userInfo = explode(CoreModel::GROUP_CONCAT, $nicknameId); 
            $dashboard[$userInfo[0]]['userInfo'] = [
                'id' => $userInfo[1],
                'email' => $userInfo[0],
                'leave_date' => $userInfo[2],
                'join_date' => $userInfo[3],
                'team' => $userInfo[4],
            ];
            $totalEffort = 0;
            foreach ($months as $month) {
                foreach ($value as $projInfo) {
                    $effort = View::getInstance()->getEffortOfMonth($month['month'], $month['year'], $projInfo['effort'], $projInfo['start_date'], $projInfo['end_date'], $userInfo[3]);
                    $totalEffort += $effort;
                    $dashboard[$userInfo[0]]['effortInfo'][$month['number']][] = [
                        'effort' => $effort,
                        'projId' => $projInfo['proj_id'],
                        'projName' => $projInfo['proj_name'],
                        'number' => $month['number'],
                        'year' => $month['year'],
                        'startDate' => $month['start'],
                        'endDate' => $month['end'],
                    ];
                }
            }
            $avgEffort = round($totalEffort/count($months), 2);
            $this->filterByEffort($dashboard, $userInfo[0], $avgEffort, $filterEffort);
        }
        return $dashboard;
    }

    /**
     * Get data by view mode is week
     *
     * @param array $data
     * @param string|date $startDate
     * @param string|date $endDate
     * @return array
     */
    public function viewWeek($data, $startDate, $endDate, $filterEffort)
    {
        $utilizationView = new UtilizationView();
        $weeks = $utilizationView->getWeeks($startDate, $endDate);

        $dashboard = [];
        foreach ($data as $nicknameId => $value) {
            $userInfo = explode(CoreModel::GROUP_CONCAT, $nicknameId); 
            $dashboard[$userInfo[0]]['userInfo'] = [
                'id' => $userInfo[1],
                'email' => $userInfo[0],
                'leave_date' => $userInfo[2],
                'join_date' => $userInfo[3],
                'team' => $userInfo[4],
            ];
            $totalEffort = 0;
            foreach ($weeks as $week) {
                foreach ($value as $projInfo) {
                    $effort = View::getInstance()->getEffortOfWeek(strtok($week['number'], '/'), $week['year'], $projInfo['effort'], $projInfo['start_date'], $projInfo['end_date'], $userInfo[3]);
                    $totalEffort += $effort;
                    $dashboard[$userInfo[0]]['effortInfo'][$week['number']][] = [
                        'effort' => $effort,
                        'projId' => $projInfo['proj_id'],
                        'projName' => $projInfo['proj_name'],
                        'number' => $week['number'],
                        'year' => $week['year'],
                        'startDate' => $week['start'],
                        'endDate' => $week['end'],
                    ];
                }
            }
            $avgEffort = round($totalEffort/count($weeks), 2);
            $this->filterByEffort($dashboard, $userInfo[0], $avgEffort, $filterEffort);
        }
        return $dashboard;
    }

    /**
     * Filter by effort
     * Unset item out of effort
     *
     * @param array $dashboard
     * @param string $dashboardKey
     * @param float $avgEffort
     * @param int $filterEffort
     *
     * @return void
     */
    public function filterByEffort(&$dashboard, $dashboardKey, $avgEffort, $filterEffort)
    {
        switch ($filterEffort) {
            case getOptions::DASHBOARD_EFFORT_GRAY:
                if ($avgEffort > 0) {
                    unset($dashboard[$dashboardKey]);
                }
                break;
            case getOptions::DASHBOARD_EFFORT_YELLOW:
                if ($avgEffort == 0 || $avgEffort > 70) {
                    unset($dashboard[$dashboardKey]);
                }
                break;
            case getOptions::DASHBOARD_EFFORT_GREEN:
                if ($avgEffort <= 70 || $avgEffort > 120) {
                    unset($dashboard[$dashboardKey]);
                }
                break;
            case getOptions::DASHBOARD_EFFORT_RED:
                if ($avgEffort <= 120) {
                    unset($dashboard[$dashboardKey]);
                }
                break;
            default:
                break;
        }
    }

    /**
     * Get list weeks from $startDate to $endDate
     *
     * @param date|string $startDate
     * @param date|string $endDate
     * @return array
     */
    public function getWeeks($startDate, $endDate)
    {
        $weeks = [];

        while ($startDate < $endDate) {
            $date = Carbon::parse($startDate);
            $startOfWeek = $date->copy()->startOfWeek();
            $endOfWeek = $date->copy()->endOfWeek();
            $week = $date->format('W');
            $year = $week == 1 ? $endOfWeek->format('Y') : $startOfWeek->format('Y');
            $weeks[] = [
                'number' => $week . '/' . $year,
                'year' => $year,
                'start' => $startOfWeek,
                'end' => $endOfWeek,
            ];
            $startDate = date('Y-m-d', strtotime($startDate . ' +1 week'));
        }
        if (end($weeks)['end'] < $endDate) {
            $date = Carbon::parse($endDate);
            $SundayOfWeek = $date->endOfWeek();
            $week = $date->format('W');
            $year = $SundayOfWeek->format('Y');
            $rangeOfWeek = View::getInstance()->getStartAndEndDate($week, $year);
            $weeks[] = [
                'number' => $week . '/' . $year,
                'year' => $year,
                'start' => $rangeOfWeek[0],
                'end' => $rangeOfWeek[1],
            ];
        }

        return $weeks;
    }

    /**
     * Get list months from $startDate to $endDate
     *
     * @param date|string $startDate
     * @param date|string $endDate
     * @return array
     */
    public function getMonths($startDate, $endDate)
    {
        $months = View::getMonthsBetweenDate($startDate, $endDate);
        foreach ($months as &$month) {
            $periodMonth = View::getInstance()->getFirstLastDaysOfMonth($month['month'], $month['year']);
            $month['start'] = $periodMonth[0];
            $month['end'] = $periodMonth[1];
            $month['number'] = $month['month'] . '/' . $month['year'];
        }
        return $months;
    }

    /**
     * Get list dates from $startDate to $endDate
     * Store only dates are not weekend
     *
     * @param date|string $startDate
     * @param date|string $endDate
     * @return array
     */
    public function getDates($startDate, $endDate)
    {
        $dates = View::getInstance()->getDays($startDate, $endDate);
        $result = [];
        foreach ($dates as $date) {
            //Store only dates are not weekend
            if (!View::getInstance()->isWeekend($date)) {
                $result[] = [
                    'number' => $date,
                    'year' => date('Y', strtotime($date)),
                ];
            }
        }
        return $result;
    }
}
