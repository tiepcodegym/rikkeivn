<?php

namespace Rikkei\HomeMessage\Http\Controllers;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Rikkei\Api\Sync\BaseSync;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\HomeMessage\Helper\Constant;
use Rikkei\HomeMessage\Helper\Helper;
use Rikkei\HomeMessage\Http\Request\InsertHomeMessageRequest;
use Session;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Rikkei\HomeMessage\Model\HomeMessage;
use Rikkei\HomeMessage\Model\HomeMessageGroup;
use Rikkei\HomeMessage\View\FileUploader;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\TeamList;

class HomeMessageController extends BaseController
{
    protected $lang;


    public function __construct()
    {
        $this->lang = Session::get('locale');
        Menu::setFlagActive('message');
    }


    /**
     * List all home message
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAll(Request $request)
    {
        $filter = Form::getFilterData(null, null, null); 
        $collection = HomeMessage::makeInstance()->select('home_messages.*');
        $pager = Config::getPagerData(null, ['order' => "updated_at", 'dir' => 'DESC']);
        $pager = Helper::pageParser($filter, $pager);
       
        $collection = $collection->orderBy($pager['order'], $pager['dir']);
        if (isset($filter['except']) && isset($filter['except']['team_id'])) {
            //load all id team by branch
            $teamInfo = Team::where('id', $filter['except']['team_id'])->first();
            if ($teamInfo) {
                $allGroup = Team::where('branch_code', $teamInfo->branch_code)->pluck('id')->toArray();
                $collection->whereIn('group_id', $allGroup);
            }
        }
        
        $collection->leftjoin('m_home_message_groups', 'm_home_message_groups.id', '=', 'home_messages.group_id')->whereNull("m_home_message_groups.deleted_at");
        $collection = HomeMessage::filterGrid($collection, [], null, 'LIKE');
        $collection = HomeMessage::pagerCollection($collection, $pager['limit'], $pager['page']);
        $collection->each(function ($raw) {
            $raw->message = $raw->{"message_" . $this->lang};
        });
        $allGroup = HomeMessageGroup::all()->each(function ($raw) {
            $this->name = $raw->{"name_" . $this->lang};
            if (trim($this->name) == '') {
                $this->name = $raw->name_vi;
                if (trim($this->name) == '') {
                    $this->name = $raw->name_en;
                }
                if (trim($this->name) == '') {
                    $this->name = $raw->name_jp;
                }
            }

        });
        $allBranch = (new HomeMessage())->getAllBranch();
        return view('HomeMessage::home_message.index', compact('collection', 'allGroup', 'allType', 'allBranch'));
    }

    /**
     * Insert new home message
     * @param InsertHomeMessageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function insert(InsertHomeMessageRequest $request)
    {
        try {
            DB::beginTransaction();
            $dataRequest = $this->requestParser($request);
            $dataRequest = $this->fileUpload($request, $dataRequest);
            $homeMessage = HomeMessage::makeInstance()->create($dataRequest);
            $this->relationSync($request, $dataRequest, $homeMessage);
            DB::commit();
            $this->resetHomeMessageCacheApi();
            return redirect()->route('HomeMessage::home_message.all-home-message')
                ->with('messages', ['success' => [Lang::get('HomeMessage::message.Update success')]]);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::error($exception);
            return back()->withInput()->withErrors($exception->getMessage());
        }
    }

    /**
     * Edit by id or create home message
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function single($id)
    {
        if ($id == 0) {
            $collection = new HomeMessage();
        } else {
            $collection = HomeMessage::findOrFail($id);
        }

        $allGroup = HomeMessageGroup::orderBy('priority', 'ASC')->get();
        $teamsOption = TeamList::toOption(null, true, false);
        $allIconOld = Storage::disk('public')->files('/home-message/');
        if (is_array($allIconOld) && count($allIconOld) > 0) {
            foreach ($allIconOld as &$item) {
                $item = explode('/', $item);
                $item = '/storage/home-message/' . end($item);
            }
        } else {
            $allIconOld = [];
        }
        $data = [
            'collection' => $collection,
            'allGroup' => $allGroup,
            'teamsOption' => $teamsOption,
            'allIconOld' => $allIconOld,
        ];

        return view('HomeMessage::home_message.single', $data);
    }

    /**
     * Update home message by id
     * @param $id
     * @param InsertHomeMessageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, InsertHomeMessageRequest $request)
    {
        try {
            DB::beginTransaction();
            $homeMessage = HomeMessage::findOrFail($id);
            $dataRequest = $this->requestParser($request);
            $dataRequest = $this->fileUpload($request, $dataRequest);
            $homeMessage->update($dataRequest);
            $this->relationSync($request, $dataRequest, $homeMessage);
            DB::commit();
            $this->resetHomeMessageCacheApi();
            return redirect()->route('HomeMessage::home_message.all-home-message')
                ->with('messages', ['success' => [Lang::get('HomeMessage::message.Update success')]]);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::error($exception);
            return back()->withInput()->withErrors($exception->getMessage());
        }
    }

    /**
     * Delete home message by id
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();
            HomeMessage::findOrFail($id)->delete();
            DB::commit();
            $this->resetHomeMessageCacheApi();
            return redirect()->back()->with('messages', ['success' => [Lang::get('HomeMessage::message.Delete success')]]);
        } catch (\Exception $exception) {
            DB::rollback();
            Log::error($exception);
            return back()->withErrors($exception->getMessage());
        }
    }

    /**
     * Parser request field
     * @param $request
     * @return array
     */
    private function requestParser($request)
    {
        return $request->only([
            'message_vi',
            'message_en',
            'message_jp',
            'icon_url',
            'group_id',
            'icon_url',
            'txt_date_apply',
            'start_at',
            'end_at',
            'icon_url_old',
        ]);
    }

    /**
     * File upload handle
     * @param $request
     * @param $dataRequest
     * @return array
     * @throws \Exception
     */
    private function fileUpload($request, $dataRequest)
    {
        if ($request->hasFile('icon_url')) {
            $uploadIcon = new FileUploader('icon_url', '/home-message/', []);
            $respUploadIcon = $uploadIcon->upload();
            if ($respUploadIcon && $respUploadIcon['isSuccess'] && isset($respUploadIcon['files'][0]['file'])) {
                $dataRequest['icon_url'] = $respUploadIcon['files'][0]['file'];
            }
        } else {
            $dataRequest['icon_url'] = $dataRequest['icon_url_old'];
        }
        return $dataRequest;
    }

    /**
     * Update relation of home message
     * @param $request
     * @param $dataRequest
     * @param $homeMessage
     */
    private function relationSync($request, $dataRequest, $homeMessage)
    {
        $homeMessage->updateTeamReceive($request->get('team_id', []));
        $parseDataHomeMessageDay = $this->parseDataHomeMessageDay($request);
        $homeMessageDay = $homeMessage->homeMessageDay;
        $homeMessage->touch();
        if (!empty($homeMessageDay)) {
            $homeMessage->homeMessageDay()->update($parseDataHomeMessageDay);
        } else {
            $homeMessage->homeMessageDay()->create($parseDataHomeMessageDay);
        }
    }

    /**
     * Parse day of week to array
     * @param $request
     * @return array
     */
    private function parseDataHomeMessageDay($request)
    {
        $groupId = $request->get('group_id');
        if ($groupId != Constant::HOME_MESSAGE_GROUP_DISPLAY_DEFINED_TIME_IN_WEEK) {
            $data = [
                'permanent_day' => $request->get('txt_date_apply'),
                'type' => Constant::HOME_MESSAGE_DAY_TYPE_DEFINED_DAY_IN_YEAR,
                'is_sun' => 0,
                'is_mon' => 0,
                'is_tues' => 0,
                'is_wed' => 0,
                'is_thur' => 0,
                'is_fri' => 0,
                'is_sar' => 0,
            ];

        } else {
            $dayOfWeek = array_map(function ($value) {
                return (int)$value;
            }, $request->get('week_days', []));
            $data = [
                'permanent_day' => null,
                'type' => Constant::HOME_MESSAGE_DAY_TYPE_DEFINED_DAY_IN_WEEK,
                'is_sun' => (int)in_array(Constant::SUNDAY, $dayOfWeek),
                'is_mon' => (int)in_array(Constant::MONDAY, $dayOfWeek),
                'is_tues' => (int)in_array(Constant::TUESDAY, $dayOfWeek),
                'is_wed' => (int)in_array(Constant::WEDNESDAY, $dayOfWeek),
                'is_thur' => (int)in_array(Constant::THURSDAY, $dayOfWeek),
                'is_fri' => (int)in_array(Constant::FRIDAY, $dayOfWeek),
                'is_sar' => (int)in_array(Constant::SATURDAY, $dayOfWeek),
            ];
        }
        return $data;
    }

    private function resetHomeMessageCacheApi()
    {
        $url = config('services.home_message.reset_cache');
        BaseSync::callApi($url,'get');
    }
}
