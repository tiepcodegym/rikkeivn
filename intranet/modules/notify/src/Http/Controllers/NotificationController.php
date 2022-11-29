<?php

namespace Rikkei\Notify\Http\Controllers;

use Carbon\Carbon;
use DB;
use Auth;
use Lang;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\View\Form;
use Rikkei\HomeMessage\Helper\Helper;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Notify\Http\Requests\NotifyStoreRequest;
use Rikkei\Notify\Model\Job;
use Rikkei\Notify\Model\NotifyMobile;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\View\Config;

class NotificationController extends Controller
{
    /*
     * show all notifications
     */
    public function index()
    {
        $filter = Form::getFilterData();
        $collection = NotifyMobile::makeInstance();
        $pager = Config::getPagerData(null, ['order' => 'created_at', 'dir' => 'desc']);
        $pager = Helper::pageParser($filter, $pager);
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        $collection = NotifyMobile::filterGrid($collection, [], null, 'LIKE');
        $collection = NotifyMobile::pagerCollection($collection, $pager['limit'], $pager['page']);
        $allStatus = (new NotifyMobile())->getAllType();
        return view('notify::admin.index', compact('collection', 'allStatus'));
    }

    /**
     * show form register notification
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('notify::admin.create');
    }

    public function store(NotifyStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $notifyMobile = [
                'title' => $request->get('title'),
                'content' => $request->get('content'),
                'created_by' => Auth::id(),
                'category_id' => RkNotify::CATEGORY_ADMIN
            ];
            if ($request->get('date_type') == getOptions::DATE_NOW) {
                $notifyMobile['available_at'] = Carbon::now()->toDateTimeString();
            } else {
                $notifyMobile['available_at'] = Carbon::parse($request->get('available_at'))->toDateTimeString();
            }
            $listIdTeam = explode(',', $request->get('team_list'));
            $listIdTeams = [];
            foreach ($listIdTeam as $key => $team) {
                $listIdTeams[$key] = array_unique(ManageTimeCommon::getTeamChild($team));
            }
            $unique = [];
            foreach ($listIdTeams as $value) {
                $unique = array_merge($unique, $value);
            }
            $receiverIds = DB::table('team_members AS a')
                ->select('c.id')
                ->leftJoin('teams AS b', 'a.team_id', '=', 'b.id')
                ->join('employees AS c', 'a.employee_id', '=', 'c.id')
                ->whereIn('a.team_id', $unique)
                ->whereNull('c.leave_date')
                ->whereNull('c.deleted_at')
                ->groupBy('c.id')
                ->pluck('c.id');
            $availableAt = Carbon::parse($notifyMobile['available_at']);
            $delay = Carbon::now()->diffInSeconds($availableAt);
            $dataFirebase = [
                'message' => $request->get('content'),
                'receiver_ids' => $receiverIds,
                'category_id' => RkNotify::CATEGORY_ADMIN,
                'delay' => $delay,
                'queue' => 'mobile',
                'title' => $request->get('title'),
                'is_admin' => true,
                'admin_id' => Auth::id()

            ];
            $jobId = \RkNotify::sendNotification($dataFirebase);
            $notifyMobile['job_id'] = $jobId;
            $notify = NotifyMobile::create($notifyMobile);
            $notify->teams()->attach($listIdTeam);
            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Register success'),
                ]
            ];
            return redirect()->route('notify::admin.notify.index')->with('messages', $messages);
        } catch (\Exception $exception) {
            DB::rollback();
            \Log::info($exception->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $notifyMobile = NotifyMobile::with('teams')->where('status', getOptions::RESULT_DEFAULT)
            ->where('id', $id)->where('created_by', Auth::id())->first();
        if (!$notifyMobile) {
            return redirect()->route('notify::admin.notify.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $teamIds = [];
        $teamNames = [];
        foreach ($notifyMobile->teams as $key => $notify) {
            $teamNames[$key] = $notify->name;
            $teamIds[$key] = $notify->id;
        }
        $notifyMobile->available_at = Carbon::parse($notifyMobile->available_at)->format('d-m-Y H:i');
        return view('notify::admin.edit', compact('notifyMobile', 'teamIds', 'teamNames'));
    }

    /**
     * @param $id
     * @param NotifyStoreRequest $request
     */
    public function update($id, NotifyStoreRequest $request)
    {
        DB::beginTransaction();
        try {
            $notifyMobileData = NotifyMobile::where('id', $id)->where('status', getOptions::RESULT_DEFAULT)->where('created_by', Auth::id())->first();
            $notifyMobile = [
                'title' => $request->get('title'),
                'content' => $request->get('content'),
            ];
            if ($request->get('date_type') == getOptions::DATE_NOW) {
                $notifyMobile['available_at'] = Carbon::now()->toDateTimeString();
            } else {
                $notifyMobile['available_at'] = Carbon::parse($request->get('available_at'))->toDateTimeString();
            }
            $listIdTeam = explode(',', $request->get('team_list'));
            $receiverIds = DB::table('team_members AS a')
                ->select('c.id')
                ->leftJoin('teams AS b', 'a.team_id', '=', 'b.id')
                ->leftJoin('employees AS c', 'a.employee_id', '=', 'c.id')
                ->whereIn('a.team_id', $listIdTeam)
                ->whereNull('c.leave_date')
                ->whereNull('c.deleted_at')
                ->groupBy('c.email')
                ->pluck('c.id');
            $availableAt = Carbon::parse($request->get('available_at'));
            $delay = Carbon::now()->diffInSeconds($availableAt);
            $dataFirebase = [
                'message' => $request->get('content'),
                'receiver_ids' => $receiverIds,
                'category_id' => RkNotify::CATEGORY_ADMIN,
                'delay' => $delay,
                'queue' => 'mobile',
                'title' => $request->get('title'),
            ];
            $jobId = \RkNotify::sendNotification($dataFirebase);
            $notifyMobile['job_id'] = $jobId;
            Job::destroy($notifyMobileData->job_id);
            $notifyMobileData->update($notifyMobile);
            $notifyMobileData->teams()->sync($listIdTeam);
            DB::commit();
            $messages = [
                'success' => [
                    Lang::get('manage_time::message.Update success'),
                ]
            ];
            return redirect()->route('notify::admin.notify.index')->with('messages', $messages);
        } catch (\Exception $exception) {
            DB::rollback();
            \Log::info($exception->getMessage());
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('manage_time::message.An error occurred')]]);
        }
    }

    /**
     * delete a record notify mobile
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            $notify = NotifyMobile::find($id);
            Job::destroy($notify->job_id);
            $notify->teams()->detach();
            $notify->delete();
        } catch (\Exception $exception) {
            \Log::info($exception->getMessage());
        }
    }
}

