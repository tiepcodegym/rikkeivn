<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Rikkei\ManageTime\Model\WorkingTime;
use Rikkei\ManageTime\Model\WktComment;
use Rikkei\ManageTime\View\ManageTimeConst as MTConst;
use Rikkei\ManageTime\View\ManageTimeCommon as MTCommont;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\View\View as CoreView;
use Rikkei\ManageTime\Model\WktLog;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\Model\CoreConfigData;
use Carbon\Carbon;
use Validator;
use Rikkei\Team\Model\Team;

class WorkingTimeController extends Controller
{
    /*
     * constructer
     */
    public function _construct()
    {
        parent::_construct();
    }

    /*
     * register form
     */
    public function register(Request $request, $id = null)
    {
        WorkingTime::permissRegister();
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Register working time'));

        $item = null;
        $draftItem = null;
        $hasOtherTime = false;
        if ($id) {
            $item = WorkingTime::findOrFail($id);
            $hasOtherTime = WorkingTime::hasOtherTimeRegister($item);
            $draftItem = $item->draftItem();
            if (!$draftItem) {
                $draftItem = $item;
            }
            $employee = $item->employee;
        } else {
            $employee = Permission::getInstance()->getEmployee();
        }
        $permiss = WorkingTime::getPermisison($item);
        //check permission
        if (!$permiss['view']) {
            CoreView::viewErrorPermission();
        }
        $teamCode = WorkingTime::getTeamCode($item, $employee);
        if (Employee::checkExistTeam(Team::getIdsTeam(), Team::CODE_PREFIX_JP)) {
            $teamCode = Team::CODE_PREFIX_JP;
        }
        $defaultTimes = MTCommont::defaultWorkingTime($teamCode);
        $listComments = $item ? WktComment::getGridData($item->id) : collect();
        $relator = null;
        if (MTCommont::isTeamCodeVn($teamCode)) {
            $relatorEmail = CoreConfigData::getValueDb('working_time_relator_vn');
            if ($relatorEmail) {
                $relator = Employee::getEmpByEmail($relatorEmail);
            }
        }
        return view(
            'manage_time::working-time.register',
            compact('employee', 'item', 'defaultTimes', 'permiss', 'draftItem', 'listComments', 'hasOtherTime', 'relator')
        );
    }

    /*
     * save register form
     */
    public function postRegister(Request $request)
    {
        WorkingTime::permissRegister();
        $data = $request->except('_token');
        $register = null;
        if (isset($data['id'])) {
            $register = WorkingTime::findOrFail($data['id']);
        }
        //check permission
        $permiss = WorkingTime::getPermisison($register);
        if (!$permiss['edit']) {
            CoreView::viewErrorPermission();
        }

        $rulesValid = [
            'approver_id' => 'required',
            'from_month' => 'required|date_format:m-Y',
            'to_month' => 'required|date_format:m-Y',
            'start_time1' => 'required|date_format:H:i',
            'end_time1' => 'required|date_format:H:i',
            'start_time2' => 'required|date_format:H:i',
            'end_time2' => 'required|date_format:H:i',
            'reason' => 'required'
        ];
        if ($register) {
            $rulesValid = array_only($rulesValid, array_keys($data));
            $objOther = clone $register;
            if (isset($data['from_month'])) {
                $objOther->from_month = Carbon::createFromFormat('m-Y', $data['from_month'])->startOfMonth()->toDateString();
            }
            if (isset($data['to_month'])) {
                $objOther->to_month = Carbon::createFromFormat('m-Y', $data['to_month'])->startOfMonth()->toDateString();
            }
        } else {
            $objOther = new \stdClass();
            $objOther->employee_id = Permission::getInstance()->getEmployee()->id;
            $objOther->from_month = Carbon::createFromFormat('m-Y', $data['from_month'])->startOfMonth()->toDateString();
            $objOther->to_month = Carbon::createFromFormat('m-Y', $data['to_month'])->startOfMonth()->toDateString();
        }
        $valid = Validator::make($data, $rulesValid);
        if ($valid->fails()) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors($valid->errors());
        }
        //check totol time
        $validTotalTime = WorkingTime::validTotalTime($data);
        if (!$validTotalTime['status']) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [$validTotalTime['message']]]);
        }
        //check valid month
        $validMonth = WorkingTime::validExistMonth($data, $register ? $register->employee_id : null);
        if (!$validMonth['status']) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('is_render', true)
                    ->with('messages', ['errors' => [$validMonth['message']]]);
        }
        if ($register) {
            $oldData = array_only($register->getAttributes(), $register->getFillable());
            $oldData['from_month'] = $register->getFromMonth();
            $oldData['to_month'] = $register->getToMonth();
            $data = array_merge($oldData, $data);
            unset($data['status']);
        }
        DB::beginTransaction();
        try {
            $item = WorkingTime::insertOrUpdate($data, $register);
            DB::commit();

            $message = isset($data['id']) ? trans('manage_time::message.Update success') : trans('manage_time::message.Register success');
            return redirect()->to(route('manage_time::wktime.register', ['id' => $item->id]))
                    ->with('messages', ['success' => [$message]]);
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('manage_time::message.Error. Please try again later.')]]);
        }
    }

    /*
     * approve or unapprove item
     */
    public function approveRegister(Request $request)
    {
        $ids = $request->get('ids');
        $status = (int) $request->get('status');
        $listStatus = MTConst::listWorkingTimeStatuses();
        if (!$ids || !isset($listStatus[$status])) {
            return redirect()->back()->with('messages', ['errors' => [trans('manage_time::message.invalid_input_data')]]);
        }
        $rejectContent = trim($request->get('reject_reason'));
        if ($status == MTConst::STT_WK_TIME_REJECT) {
            if (!$rejectContent) {
                return redirect()->back()->with('messages', ['errors' => [trans('manage_time::message.invalid_input_data')]]);
            }
        }

        DB::beginTransaction();
        try {
            $ids = explode(',', $ids);
            foreach ($ids as $id) {
                $item = WorkingTime::findOrFail($id);
                $permiss = WorkingTime::getPermisison($item);
                if (!$permiss['approve']) {
                    DB::commit();
                    CoreView::viewErrorPermission();
                }
                WorkingTime::updateStatusItem($item, $status);
                WktComment::insertData($item->id, $request->all());

                CacheHelper::forget(CacheHelper::CACHE_TIME_SETTING_PREFIX, $item->employee_id);
                CacheHelper::forget(CacheHelper::CACHE_TIME_QUATER, $item->employee_id);
            }
            DB::commit();

            return redirect()->back()->with('messages', ['success' => [trans('manage_time::message.Update success')]]);
        } catch (\Exception $ex) {
            \Log::info($ex);
            DB::rollback();
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('manage_time::message.Error. Please try again later.')]]);
        }
    }

    /*
     * list my register
     */
    public function listRegister($status = null)
    {
        WorkingTime::permissRegister();
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Register working time'));

        $pageTitle = trans('manage_time::view.My working time register');
        $listStatuses = MTConst::listWTStatusesWithIcon();
        $pageTitle .= isset($listStatuses[$status]) ?  ': ' . $listStatuses[$status]['title'] : null;
        $collectionModel = WorkingTime::listRegister($status, 'register');
        return view(
            'manage_time::working-time.list',
            compact('collectionModel', 'pageTitle', 'status')
        );
    }

    /*
     * list my register related
     */
    public function listRegisterRelated($status = null)
    {
        WorkingTime::permissRegister();
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Register working time'));

        $pageTitle = trans('manage_time::view.Working time register relates to me');
        $listStatuses = MTConst::listWTStatusesWithIcon();
        $pageTitle .= isset($listStatuses[$status]) ?  ': ' . $listStatuses[$status]['title'] : null;
        $collectionModel = WorkingTime::listRegister($status, 'related');
        return view(
            'manage_time::working-time.list',
            compact('collectionModel', 'pageTitle', 'status')
        );
    }

    /*
     * list my register related
     */
    public function listRegisterApprove($status = null)
    {
        WorkingTime::permissRegister();
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Register working time'));

        $pageTitle = trans('manage_time::view.I approve working time register');
        $listStatuses = MTConst::listWTStatusesWithIcon();
        $pageTitle .= isset($listStatuses[$status]) ?  ': ' . $listStatuses[$status]['title'] : null;
        $collectionModel = WorkingTime::listRegister($status, 'approve');
        return view(
            'manage_time::working-time.list',
            compact('collectionModel', 'pageTitle', 'status')
        );
    }

    /*
     * delete register
     */
    public function deleteRegister($id)
    {
        $item = WorkingTime::findOrFail($id);
        if (!$item->delable()) {
            return redirect()->back()->with('messages', ['errors' => [trans('manage_time::message.Fill was approved, can not delete')]]);
        }
        //check permission
        $permiss = WorkingTime::getPermisison($item);
        if (!$permiss['edit']) {
            CoreView::viewErrorPermission();
        }
        $item->delete();
        return redirect()->back()->with('messages', ['success' => [trans('manage_time::message.Delete data success')]]);
    }

    /*
     * list items manage
     */
    public function listManage()
    {
        Menu::setActive('admin');
        Breadcrumb::add('Admin');
        Breadcrumb::add(trans('manage_time::view.Manage register working time'));

        $collectionModel = WorkingTime::listRegister(null, 'manage');
        $teamList = TeamList::toOption(null, true, false);
        return view(
            'manage_time::working-time.manage.index',
            compact('collectionModel', 'teamList')
        );
    }

    /*
     * list log in/out time of employee
     */
    public function listLogTimes(Request $request)
    {
        Menu::setActive('admin');
        Breadcrumb::add('Admin');
        Breadcrumb::add(trans('manage_time::view.View working time in/out'));

        $month = $request->get('month');
        $urlFilter = $request->url() . '/';
        $keyView = auth()->id() .'_view_' . $urlFilter;
        $firstView = CacheHelper::get($keyView);
        if (!$firstView) {
            CacheHelper::put($keyView, 1, null, true, 24 * 60 * 60); //store one day
            if (!$month) {
                $month = Carbon::now()->addMonthNoOverflow()->format('m-Y');
            }
        }
        $collectionModel = WorkingTime::listLogTimes($month);
        $teamList = TeamList::toOption(null, true, false);
        return view('manage_time::working-time.manage.log-times', compact('month', 'collectionModel', 'teamList'));
    }

    /*
     * ajax search approver
     */
    public function searchApprover(Request $request)
    {
        return WorkingTime::getListApprovers($request->get('q'), $request->except('search'));
    }

    /*
     * fill working time logs
     */
    public function logWorkingTime(Request $request)
    {
        if (!Permission::getInstance()->isAllow(WorkingTime::ROUTE_MANAGE) &&
                !Permission::getInstance()->isAllow(WorkingTime::ROUTE_LOG_TIME)) {
            CoreView::viewErrorPermission();
        }
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Working time in/out'));

        $month = $request->get('month');
        if (!$month) {
            $month = Carbon::now()->format('m-Y');
        }
        $employeeId = $request->get('employee_id');
        $employee = null;
        if ($employeeId) {
            if ($employeeId == auth()->id()) {
                $employeeId = null;
            } else {
                $employee = Employee::findOrFail($employeeId);
            }
        }
        $dataLogs = WktLog::listByMonth($month, $employeeId);
        $holidays = CoreConfigData::getSpecialHolidays(2, 'japan');
        return view('manage_time::working-time.log-times', compact('month', 'dataLogs', 'employeeId', 'employee', 'holidays'));
    }

    /*
     * save working time logs
     */
    public function saveLogWorkingTime(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'month' => 'required|date_format:m-Y'
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }

        DB::beginTransaction();
        try {
            WktLog::insertOrUpdate($request->get('month'), $request->except('month'));
            DB::commit();

            return redirect()->back()->with('messages', ['success' => [trans('manage_time::message.Save success')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /*
     * list my times
     */
    public function index()
    {
        WorkingTime::permissRegister();
        Menu::setActive('profile');
        Breadcrumb::add(trans('manage_time::view.Working time'));

        $collectionModel = WorkingTime::listMyTimes();
        return view('manage_time::working-time.index', compact('collectionModel'));
    }
}
