<?php

namespace Rikkei\Api\View;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\View\View;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Rikkei\Core\Model\CoreConfigData;

class Utilization
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
    public function getDataForView($data, $filter)
    {
        $utilizationView = new Utilization();
        switch ($filter['viewMode']) {
            case 'day':
                $dataConvert = $utilizationView->viewDate($data, $filter['effort']);
                break;
            case 'month':
                $dataConvert = $utilizationView->viewMonth($data, $filter['startDate'], $filter['endDate'], $filter['effort']);
                break;
            default:
                $dataConvert = $utilizationView->viewWeek($data, $filter['startDate'], $filter['endDate'], $filter['effort']);
                break;
        }

        return $this->paginate($dataConvert, $filter['limit'], $filter['page']);
    }

    /**
     * Get data by view mode is day
     *
     * @param array $data
     * @param string|date $startDate
     * @param string|date $endDate
     * @return array
     */
    public function viewDate($data, $filterEffort)
    {
        $utilizationView = new Utilization();

        $dashboard = [];
        $specialHolidays = CoreConfigData::getSpecialHolidays(2);
        $annualHolidays = CoreConfigData::getAnnualHolidays(2);
        foreach ($data as $nicknameId => $value) {
            $userInfo = explode(CoreModel::GROUP_CONCAT, $nicknameId); 
            $dashboard[$userInfo[1]] = [
                'email' => $userInfo[0],
                'leave_date' => $userInfo[2],
                'join_date' => $userInfo[3],
                'team' => $userInfo[4],
            ];
            $totalEffort = 0;
            $dates = [];
            $dataTemp = [];
            foreach ($value as $projInfo) {
                if (empty($projInfo['start_date']) || empty($projInfo['end_date']) || $projInfo['start_date'] > $projInfo['end_date']) {
                    continue;
                }
                $dates = $utilizationView->getDates($projInfo['start_date'], $projInfo['end_date']);
                foreach ($dates as $date) {
                    $effort = !empty($projInfo['effort']) ? (float) $projInfo['effort'] : 0;
                    $totalEffort += $effort;
                    if (in_array($date['number'], $specialHolidays) || in_array(date('m-d', strtotime($date['number'])), $annualHolidays)) {
                        $effort = 0;
                    }
                    if (!isset($dataTemp[$date['number']])) {
                        $dataTemp[$date['number']] = [
                            'time' => $date['number'],
                            'effort' => $effort,
                            'projId' => $projInfo['proj_id'],
                            'projName' => $projInfo['proj_name']
                        ];
                    } else {
                        $dataTemp[$date['number']]['effort'] += $effort;
                        $dataTemp[$date['number']]['projId'] .= ','.$projInfo['proj_id'];
                        $dataTemp[$date['number']]['projName'] .= ','.$projInfo['proj_name'];
                    }
                }
            }
            sort($dataTemp);
            $dashboard[$userInfo[1]]['effortInfo'] = $dataTemp;
            if (count($dates)) {
                $avgEffort = round($totalEffort/count($dates), 2);
                $this->filterByEffort($dashboard, $userInfo[1], $avgEffort, $filterEffort);
            }
        }

        $result = $this->convertData($dashboard);
        return $result;
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
        $utilizationView = new Utilization();
        $months = $utilizationView->getMonths($startDate, $endDate);

        $dashboard = [];
        foreach ($data as $nicknameId => $value) {
            $userInfo = explode(CoreModel::GROUP_CONCAT, $nicknameId); 
            $dashboard[$userInfo[1]] = [
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
                    if (!isset($dashboard[$userInfo[1]]['effortInfo'][$month['number']])) {
                        $dashboard[$userInfo[1]]['effortInfo'][$month['number']] = [
                            'effort' => $effort,
                            'projId' => $projInfo['proj_id'],
                            'projName' => $projInfo['proj_name'],
                        ];
                    } else {
                        $dashboard[$userInfo[1]]['effortInfo'][$month['number']]['effort'] += $effort;
                    }
                }
            }
            $avgEffort = round($totalEffort/count($months), 2);
            $this->filterByEffort($dashboard, $userInfo[1], $avgEffort, $filterEffort);
        }
        $result = $this->convertData($dashboard);
        return $result;
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
        $utilizationView = new Utilization();
        $weeks = $utilizationView->getWeeks($startDate, $endDate);

        $dashboard = [];
        foreach ($data as $nicknameId => $value) {            
            $userInfo = explode(CoreModel::GROUP_CONCAT, $nicknameId);
            $dashboard[$userInfo[1]] = [
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
                    if (!isset($dashboard[$userInfo[1]]['effortInfo'][$week['number']])) {
                        $dashboard[$userInfo[1]]['effortInfo'][$week['number']] = [
                            'effort' => $effort,
                            'projId' => $projInfo['proj_id'],
                            'projName' => $projInfo['proj_name'],
                        ];
                    } else {
                        $dashboard[$userInfo[1]]['effortInfo'][$week['number']]['effort'] += $effort;
                    }
                }
            }
            $avgEffort = round($totalEffort/count($weeks), 2);
            $this->filterByEffort($dashboard, $userInfo[1], $avgEffort, $filterEffort);
        }
        $result = $this->convertData($dashboard);
        return $result;
    }

    private function convertData($dashboard)
    {
        $data = [];
        foreach ($dashboard as $key => $item) {
            $dataRoot = [
                'employee_id' => $key,
                'email' => $item['email'],
                'leave_date' => $item['leave_date'],
                'join_date' => $item['join_date'],
                'team' => $item['team'],
                'effort_info' => [],
            ];
            if (isset($item['effortInfo']) && count($item['effortInfo'])) {
                foreach ($item['effortInfo'] as $projM) {
                    $projId = array_unique(explode(',', $projM['projId']));
                    $projIdText = implode(', ', $projId);
                    $projName = array_unique(explode(',', $projM['projName']));
                    $projNameText = implode(', ', $projName);
                    $dataItem = [
                        'time' => $projM['time'],
                        'effort' => $projM['effort'],
                        'projs_id' => $projIdText,
                        'projs_name' => $projNameText,
                    ];
                    $dataRoot['effort_info'][] = $dataItem;
                }
            }
            $data[] = $dataRoot;
        }
        return $data;
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
        if ($startDate == $endDate) {
            $result[] = [
                'number' => $startDate,
                'year' => date('Y', strtotime($startDate)),
            ];
            return $result;
        }
        $dates = View::getInstance()->getDays($startDate, $endDate);
        if (empty($dates)) {
            \Log::info('=== date 1 = '.$startDate . '   ======== date 2 = '.$endDate);
            \Log::info('=== dates = '.json_encode($dates));
        }
        $result = [];
        foreach ($dates as $date) {
            //Store only dates are not weekend
            ini_set('memory_limit', '-1');
            if (!View::getInstance()->isWeekend($date)) {
                $result[] = [
                    'number' => $date,
                    'year' => date('Y', strtotime($date)),
                ];
            }
        }
        return $result;
    }

    public function paginate($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, $options);
    }
}
