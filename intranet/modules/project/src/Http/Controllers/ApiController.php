<?php

namespace Rikkei\Project\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Rikkei\Project\View\ProjDbHelp;
use Carbon\Carbon;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Form as CoreForm;

class ApiController extends Controller
{
    public function workingDays()
    {
        $year = Input::get('year');
        $month = Input::get('month');
        $response = [];
        if (!$year || !$month || !is_numeric($year) || !is_numeric($month) ||
            $month < 1 || $month > 12 || strlen($year) !== 4
        ) {
            $response['days'] = null;
            return response()->json($response);
        }
        $year = (int) $year;
        $month = (int) $month;
        $response['days'] = ProjDbHelp::getWorkDay(Carbon::parse("{$year}-{$month}-01"));
        return response()->json($response);
    }

    /**
     * setting project
     */
    public function setting()
    {
        if (app('request')->ajax()) {
            return CoreForm::coreSaveSettingGeneral();
        }
        Breadcrumb::add('Project setting');
        Menu::setActive(null, null, 'project');
        return view('project::setting.general');
    }
}
