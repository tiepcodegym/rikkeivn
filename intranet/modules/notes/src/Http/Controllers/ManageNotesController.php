<?php

namespace Rikkei\Notes\Http\Controllers;

use Illuminate\Http\Request;
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Notes\Model\ReleaseNotes;
use Rikkei\Core\View\Breadcrumb;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Rikkei\Notify\View\NotifyView;
use Rikkei\Team\Model\Employee;
use RkNotify;
use Yajra\Datatables\Datatables;
use Auth;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Cache;

class ManageNotesController extends Controller {

    protected function checkAllow() {
        if (!Permission::getInstance()->isAllow('notes::manage.release.notes')) {
            \Rikkei\Core\View\View::viewErrorPermission();
        }
    }

    /**
     * list release notes
     * get data
     */
    public function index()
    {
        $this->checkAllow();
        return view('notes::manage.index', [
            'titleHeadPage' => Lang::get('notes::view.List notes')
        ]);
    }

    public function anyData() {
        $this->checkAllow();
        $releaseNotes = ReleaseNotes::select(['version', 'created_by', 'release_at', 'status', 'id']);
        return Datatables::of($releaseNotes)->make(true);
    }

    /**
     * create note
     */
    public function create() {
        $this->checkAllow();
        Breadcrumb::add('Notes', URL::route('notes::manage.notes.getIndex'));
        Breadcrumb::add('Create notes');
        $notes = new ReleaseNotes();
        $notes->status = ReleaseNotes::STATUS_ENABLE;
        return view('notes::manage.edit', [
            'notes' => $notes,
            'titleHeadPage' => Lang::get('notes::view.Create note'),
            'optionStatus' => ReleaseNotes::getAllStatus()
        ]);
    }

    /**
     * save data
     */
    public function save(Request $request) {
        $this->checkAllow();
        $notes = new ReleaseNotes;
        $data = $request->all();

        $response = array();
        $id = (isset($data['id'])) ? $data['id'] : false;
        $data = $data['notes'];

        if ($id) {
            $checkID = $notes->find($id);
            if (!$checkID) {
                $response['message'] = Lang::get('notes::message.Not found item');
                Session::flash(
                        'messages', [
                    'errors' => [
                        $response['message']
                    ]
                        ]
                );
                return back();
            }
        }

        $allStatus = implode(',', array_keys(ReleaseNotes::getAllStatus()));

        $validator = Validator::make($data, [
                    'version' => 'required|string|max:50',
                    'status' => 'required|in:' . $allStatus,
                    'content' => 'required'
        ]);

        if ($validator->fails()) {
            $response['message'] = Lang::get('notes::message.Error input data!');
            Session::flash(
                    'messages', [
                'errors' => [
                    $response['message']
                ]
                    ]
            );
            return back();
        }

        $currrentTime = Carbon::now();
        $currrentTime = $currrentTime->toDateTimeString();

        try {
            if ($id) {
                $release_at = ($data['release_at'] == "") ? $currrentTime : $data['release_at'];
                $notes->where('id', $id)
                    ->update([
                        'version' => $data['version'],
                        'status' => $data['status'],
                        'updated_at' => $currrentTime,
                        'release_at' => $release_at,
                        'content' => $data['content']
                    ]);
            } else {
                $notes->version = $data['version'];
                $notes->status = $data['status'];
                $notes->created_by = Auth::user()->employee_id;
                $notes->content = $data['content'];
                $notes->release_at = ($data['release_at'] == "") ? $currrentTime : $data['release_at'];
                $notes->created_at = $currrentTime;
                $notes->updated_at = $currrentTime;

                $notes->save();

                $id = $notes->id;
            }
            if (CacheHelper::get(ReleaseNotes::CACHE_LAST_VERSION)) {
                CacheHelper::forget(ReleaseNotes::CACHE_LAST_VERSION);
            }
            if ((int)$data['status'] === ReleaseNotes::STATUS_ENABLE && $request->has_notify === 'on') {
                $recieverIds = Employee::whereNull('leave_date')
                    ->orWhereDate('leave_date', '>=', Carbon::now()->toDateString())
                    ->pluck('id')->toArray();
                $content = $data['content'];
                $data = [
                    'type' => NotifyView::TYPE_POPUP,
                    'icon' => 'notify.png',
                    'category_id' => \Rikkei\Notify\Classes\RkNotify::CATEGORY_PERIODIC,
                ];
                RkNotify::put($recieverIds, $content, null, $data);
            }
            $response['success'] = 1;
            $response['message'] = Lang::get('notes::message.Save success');
            $response['popup'] = 1;
            Session::flash(
                    'messages', [
                'success' => [
                    $response['message']
                ]
                    ]
            );
            return redirect()->route('notes::manage.notes.edit', ['id' => $id]);
        } catch (Exception $e) {
            $response['message'] = Lang::get('notes::message.Error system, please try later!');
            Log::info($e);
            Session::flash(
                    'messages', [
                'errors' => [
                    $response['message']
                ]
                    ]
            );
            return back();
        }
    }

    public function edit($id) {
        $this->checkAllow();
        $notes = new ReleaseNotes;

        $data = $notes->find($id);

        if (!$data) {
            $response['message'] = Lang::get('notes::message.Not found item');
            Session::flash(
                    'messages', [
                'errors' => [
                    $response['message']
                ]
                    ]
            );
            return redirect()->route('notes::manage.notes.getIndex');
        }
        Breadcrumb::add('Notes', URL::route('notes::manage.notes.getIndex'));
        Breadcrumb::add('Notes detail');
        return view('notes::manage.edit', [
            'notes' => $data,
            'titleHeadPage' => Lang::get('notes::view.Notes edit'),
            'optionStatus' => ReleaseNotes::getAllStatus()
        ]);
    }
}
