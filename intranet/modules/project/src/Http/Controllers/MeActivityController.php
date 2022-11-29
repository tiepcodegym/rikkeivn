<?php

namespace Rikkei\Project\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeActivity;
use Carbon\Carbon;
use Validator;

class MeActivityController extends Controller
{
    public function _construct()
    {
        Breadcrumb::add(trans('project::me.Monthly Evaluation'));
    }

    /*
     * view/edit activities
     */
    public function activity(Request $request)
    {
        Menu::setActive('profile');
        Breadcrumb::add('Activities');

        $month = $request->get('month');
        try {
            $month = Carbon::parse($month)->format('Y-m');
        } catch (\Exception $ex) {
            $month = Carbon::now()->format('Y-m');
        }
        $sepMonth = config('project.me_sep_month');
        if ($month <= $sepMonth) {
            $activityFields = MeAttribute::getFieldActivity();
        } else {
            $activityFields = MeAttribute::getFieldActivity([MeAttribute::TYPE_NEW_PRO_ACTIVITY]);
        }
        $activities = MeActivity::getByEmpId($month);
        $isEditable = MeActivity::checkEditable($month);
        return view('project::me.profile.activity', compact('activityFields', 'month', 'activities', 'isEditable'));
    }

    /*
     * save activities
     */
    public function saveActivity(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'month' => 'required|date_format:Y-m'
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $month = $request->get('month');
        if (!MeActivity::checkEditable($month)) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::me.me_had_approved_not_editable')]]);
        }

        DB::beginTransaction();
        try {
            MeActivity::insertOrUpdate($month, $request->except(['_token', 'month']));
            DB::commit();
            return redirect()->back()->with('messages', ['success' => [trans('project::me.Saved successful')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('project::me.An error occurred')]]);
        }
    }

    /*
     * view member activities
     */
    public function viewMembers(Request $request)
    {
         Menu::setActive('team');
        Breadcrumb::add('Activities');
        $month = $request->get('month');
        if (!$month) {
            $month = Carbon::now()->format('Y-m');
        }
        $collectionModel = MeActivity::getDataGrid($month);
        $sepMonth = config('project.me_sep_month');
        if ($month <= $sepMonth) {
            $activityFields = MeAttribute::getFieldActivity();
        } else {
            $activityFields = MeAttribute::getFieldActivity([MeAttribute::TYPE_NEW_PRO_ACTIVITY]);
        }
        return view('project::me.profile.member-activities', compact('collectionModel', 'month', 'activityFields'));
    }
}

