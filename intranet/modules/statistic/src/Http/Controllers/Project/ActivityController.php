<?php

namespace Rikkei\Statistic\Http\Controllers\Project;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Statistic\Models\STEmplLoc;
use Rikkei\Statistic\Models\STEmplBug;
use Rikkei\Statistic\Models\STProjBug;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\View\OptionCore;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Statistic\Helpers\STProjDeliverHelper;

class ActivityController extends Controller
{
    /**
     * list chart
     */
    public function index($ids = null)
    {
        if (!Permission::getInstance()->isAllow('project::statistic.dashboard')) {
            return CoreView::viewErrorPermission();
        }
        Breadcrumb::add('Project activity');
        return view('statistic::project.activity.index', [
            'teamPathTree' => Team::getTeamPathTree(),
            'team' => $ids,
        ]);
    }

    /**
     * list slide
     */
    public function slideView($ids = null)
    {
        if ($this->hasPermission()) {
            Breadcrumb::add('Project activity');
            return view('statistic::project.activity.slide', [
                'teamPathTree' => Team::getTeamPathTree(),
                'team' => $ids,
            ]);
        }
        return view('statistic::project.activity.slide_pass');
    }

    /**
     * slide password post
     */
    public function slidePassPost()
    {
        $pass = Input::get('password');
        $passConfig = CoreConfigData::getValueDb('project.production.slide.pass');
        if ($pass === $passConfig) {
            Session::put('project.production.slide.pass', $pass);
            return redirect()->route('statistic::project.activity.slide.view');
        }
        return redirect()->route('statistic::project.activity.slide.view')
            ->withErrors(trans('statistic::view.Password incorrect'));
    }

    /**
     * get info chart with parammaster
     *
     * @param string $action
     */
    public function getInfo($action = null)
    {
        if (!$this->hasPermission()) {
            return CoreView::viewErrorPermission();
        }
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        if (!$action) {
            $action = 'emplLoc';
        }
        if (!$action || !method_exists($this, $action)) {
            $response = [];
            $response['status'] = 0;
            return response()->json($response, 500);
        }
        $response = $this->{$action}(Input::get('team'));
        return response()->json($response);
    }

    /**
     * get project open
     */
    protected function projOpen()
    {
        $response['status'] = 1;
        return $response;
    }

    /**
     * get project open
     */
    protected function emplLoc($ids = null)
    {
        OptionCore::setMemoryMax();
        $from = CoreView::createDateFromFormat(Input::get('from'));
        $to = CoreView::createDateFromFormat(Input::get('to'));
        if ($from && $to) {
            if ($to < $from) {
                $from = null;
            }
        } else {
            if (!$to) {
                $to = Carbon::now();
            }
            if (!$from) {
                $from = clone $to;
                $from->modify('-30 days');
            }
        }
        if ($ids) {
            $ids = explode('-', $ids);
        } else {
            $ids = [];
        }
        $response['emplLoc'] = STEmplLoc::getEmplLocPeriod($from, $to, [
            'employeeId' => Input::get('employee'),
            'team' => $ids
        ]);
        if (!Input::get('employee')) {
            $response['emplBug'] = STEmplBug::getEmplBugPeriod($from, $to, [
                'type' => STProjBug::TYPE_BUG_DEFECT,
                'team' => $ids
            ]);
            $response['emplBuglea'] = STEmplBug::getEmplBugPeriod($from, $to, [
                'type' => STProjBug::TYPE_BUG_LEAKAGE,
                'team' => $ids
            ]);
            $response['emplBuglefix'] = STEmplBug::getEmplBugPeriod($from, $to, [
                'type' => STProjBug::TYPE_FIX_BUG_LE,
                'team' => $ids
            ]);
            $response['emplBugdefix'] = STEmplBug::getEmplBugPeriod($from, $to, [
                'type' => STProjBug::TYPE_FIX_BUG_DE,
                'team' => $ids
            ]);
            $response['projDeli'] = STProjDeliverHelper::getSTProjDeliver($from, $to);
        }
        if (!Input::get('projsExists')) {
            $response['projName'] = STEmplLoc::getProjName([
                $response['emplLoc'],
                $response['emplBug'],
                $response['projDeli'],
            ]);
        }
        $response['status'] = 1;
        return $response;
    }

    /**
     * has permission view slide
     *
     * @return boolean
     */
    private function hasPermission()
    {
        if (Permission::getInstance()->isAllow('project::statistic.dashboard')) {
            return true;
        }
        if (Session::get('project.production.slide.pass') ===
            CoreConfigData::getValueDb('project.production.slide.pass'))
        {
            return true;
        }
        return false;
    }
}
