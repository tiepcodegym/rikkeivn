<?php

namespace Rikkei\Resource\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Resource\Model\EmployeeBaseline;
use Rikkei\Resource\Model\RecruitPlan;
use Rikkei\Resource\View\Statistics;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\TeamList;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

class StaffStatController extends Controller
{
    public function _construct()
    {
        Breadcrumb::add(trans('resource::view.stat.staff_statistics'));
        Menu::setActive('resource');
    }

    /*
     * render index
     */
    public function index($timeType, Request $request)
    {
        $month = $request->get('month');
        $year = (int)$request->get('year');
        $now = Carbon::now();
        if (!$month) {
            $month = Carbon::now()->month;
        }
        if (!$year) {
            $year = Carbon::now()->year;
        }
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
        $roles = getOptions::getInstance()->getRoles(true);
        $contracts = getOptions::listWorkingTypeInternal() + getOptions::listWorkingTypeExternal();
        $isExact = false; // tháng và năm truyền vào lớn hơn hoặc bằng tháng và năm hiện tại => thống kế nhân viên đến ngày hiện tại
        if ($timeType === 'month') {
            if ($time->format('Y-m') < $now->format('Y-m')) {
                $staffStatisticsBaseline = EmployeeBaseline::where('month', $time->format('Y-m'))
                    ->first();
            } else {
                $isExact = true;
            }
        }
        if ($timeType === 'year') {
            if ($year < $now->year) {
                $monthCompare = Carbon::parse($time)->lastOfYear()->format('Y-m');
                $staffStatisticsBaseline = EmployeeBaseline::where('month', $monthCompare)
                    ->first();
            } else {
                $isExact = true;
            }
        }

        if (isset($staffStatisticsBaseline)) {
            $staffStatisticsBaseline = unserialize($staffStatisticsBaseline->data);
            return view('resource::statistics.staff', compact(
                'arrayTeams',
                'roles',
                'contracts',
                'timeType',
                'year',
                'month',
                'staffStatisticsBaseline'
            ));
        }

        $statistics = Statistics::getTotal([], $time, $timeType, $isExact);
        $total = $statistics['total'];
        $dateEnd = $statistics['rangeTime']['end'];
        return view('resource::statistics.staff', compact(
            'total',
            'arrayTeams',
            'roles',
            'contracts',
            'dateEnd',
            'timeType',
            'year',
            'month'
        ));
    }
}

