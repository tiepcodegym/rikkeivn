<?php

namespace Rikkei\Project\Http\Controllers;

use Rikkei\Core\View\Menu;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Project\Model\ProjectPoint;
use Exception;
use Illuminate\Support\Facades\Input;
use Rikkei\Project\View\KpiExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\View as CoreView;

class KpiController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('project', 'project/dashboard');
    }    

    /**
     * kpi export view
     */
    public function index()
    {
        if (!Permission::getInstance()->isAllow('project::report.kpi.index')) {
            return CoreView::viewErrorPermission();
        }
        if (app('request')->ajax()) {
            return $this->getData();
        }
        $export = new KpiExport();
        return view('project::kpi.index', [
            // spec ptpm dn - remove after
            'teamParent' => [
                35 => [42,43,44]
            ], // not show team child, replace to sum team parent
            'divisions' => $export->getTeams(),
            'evaluation' => ProjectPoint::evaluationLabel(),
        ]);
    }

    /**
     * export excel
     */
    public function getData()
    {
        if (!Permission::getInstance()->isAllow('project::report.kpi.index')) {
            return CoreView::viewErrorPermission();
        }
        $route = 'project::kpi.flag';
        $from = Input::get('from');
        $to = Input::get('to');
        $response = [];
        if (!$from || !$to) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.Error input data!');
            return response()->json($response, 500);
        }
        try {
           $from = Carbon::createFromFormat('Y-m', $from);
           $from->day = 1;
           $from->startOfDay();
           $to = Carbon::createFromFormat('Y-m', $to);
           $to->lastOfMonth();
           if ($from->gt($to)) {
               $response['status'] = 0;
                $response['message'] = trans('core::message.Error input data!');
                return response()->json($response, 500);
           }
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.Error input data!');
            return response()->json($response, 500);
        }
        try {
            $export = new KpiExport();
            $response = $export->getData($from, $to);
            $response['status'] = 1;
            return response()->json($response);
        } catch (Exception $ex) {
            Log::error($ex);
            $response['status'] = 0;
            $response['message'] = trans('core::message.Error system');
            return response()->json($response, 500);
        }
    }
}
