<?php

namespace Rikkei\Resource\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\ChannelFee;
use Rikkei\Team\View\Permission;
use Illuminate\Support\Facades\Validator;
use Rikkei\Resource\Model\Channels;
use Lang;
use Rikkei\Core\View\Menu;
use Illuminate\Support\Facades\Input;
use Rikkei\Core\View\CacheHelper;

class ChannelController extends Controller {
    
    /**
     * construct more
     */
    protected function _construct()
    {
        Menu::setActive('resource');
        Breadcrumb::add('Recruit channel' , route('resource::channel.list'));
    }
    
    /**
     * Create customer page view
     * @return view
     */
    public function create() {
        Breadcrumb::add(Lang::get('resource::view.Channel.Create.Create channel'));
        return view('resource::channel.create', [
            'channel' => new Channels()
        ]);
    }

    /**
     * store customer
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        $flagUpdate = false;
        $data = $request->all();
        $tableChannel = Channels::getTableName();
        $messages = [
            'name.required' => trans('resource::message.Channel name is required field'),
            'name.max' => trans('sales::view.Channel.Create.Name greater than', ['number' => 255]),
            'name.unique' => trans('resource::message.Channel name is unique field'),
            'data.*.cost.numeric' => trans('resource::message.Cost is invalid'),
        ];
        $rules['data.*.cost'] = 'min:0|numeric';
        if (isset($data['channel_id'])) {
            $rules['name'] = 'required|max:255|unique:'.$tableChannel. ',name,'.(int)$data['channel_id'].',id';
        } else {
            $rules['name'] = 'required|max:255|unique:'.$tableChannel. ',name';
        }
        if (isset($data['data'])) {
            foreach ($data['data'] as $key => $val) {
                $data['data'][$key]['cost'] = str_replace(Channels::PRICE, '', $data['data'][$key]['cost']);
            }
        }
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        if (isset($data['channel_id']) && $data['channel_id']) {
            $flagUpdate = true;
            $channel = Channels::getChannelById($data['channel_id']);
        } else {
            $channel = new Channels();
        }
        $data['status'] = $data['status'] == Channels::ENABLED ? Channels::ENABLED : Channels::DISABLED;
        $recruitChannel = [
            'name' => $data['name'],
            'is_presenter' => $data['is_presenter'],
            'type' => $data['cost_type'],
            'status' => $data['status'],
        ];

        DB::beginTransaction();
        try {
            $channel->fill($recruitChannel);
            $channel->save();
            ChannelFee::saveFee($channel, $data['data'], $flagUpdate);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return redirect()->back()->withErrors($exception->getMessage());
        }

        $data['status'] = $data['status'] == Channels::ENABLED ? Channels::ENABLED : Channels::DISABLED;
        $channel->fill($data);
        $channel->save();
        CacheHelper::forget(Channels::KEY_CACHE, Channels::KEY_DETAIL . $channel->id);
        
        if(isset($data['channel_id']) && $data['channel_id']) {
            $msg = Lang::get('resource::view.Update channel success');
        } else {
            $msg = Lang::get('resource::view.Create channel success');
        }
        $messages = [
                'success'=> [
                    $msg,
                ]
        ];
        return redirect()->route('resource::channel.edit', ['id' => $channel ->id])->with('messages', $messages);
    }

    /*
     * list channel
     */
    public function grid() {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        Breadcrumb::add(Lang::get('resource::view.Channel.List'));
        $collectionModel = Channels::getGridData();
        return view('resource::channel.index', [
            'collectionModel' => $collectionModel
        ]);
    }

    /*
     * edit channel
     * @param int $id
     * @return view
     */
    public function edit($id) {
        if (! Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        $channel = Channels::getChannelById($id);
        Breadcrumb::add(Lang::get('resource::view.Channel.Create.Update channel'));
        return view('resource::channel.create', compact(['channel']));
    }
    
    /*
     * delete customer
     */
    public function deleteChannel()
    {
        if (!Permission::getInstance()->isAllow()) {
            echo view('errors.permission');
            exit;
        }
        $id = Input::get('id');
        $channel = Channels::getChannelById($id);
        if (!$channel) {
            return redirect()->route('resource::channel.list')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $checkUsed = Candidate::where('channel_id', $id)->count();
        if ($checkUsed >= 1) {
            return redirect()->route('resource::channel.list')->withErrors(trans('resource::view.Channel.cant delete this channel'));
        }
        $channel->delete();
        $messages = [
            'success' => [
                Lang::get('team::messages.Delete item success!'),
            ]
        ];
        return redirect()->route('resource::channel.list')->with('messages', $messages);
    }

    public function ajaxToggleStatus()
    {
        $channelId = Input::get('channel_id');
        $channel = Channels::where('id', $channelId)->first();
        $status = $channel->status == Channels::ENABLED ? Channels::DISABLED : Channels::ENABLED;
        $channel->update(['status' => $status]);

        return response()->json(['status' => $channel->status]);
    }

    /*
     * change background color of channel
     */
    public function changeColor(Request $request)
    {
        $channel = Channels::find($request->channelId);
        if ($channel === null) {
            return response()->json([
                'status' => 0,
                'message' => Lang::get('Channel not exist'),
            ]);
        }
        $pattern = '/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/';
        if (preg_match($pattern, $request->color) === false) {
            return response()->json([
                'status' => 0,
                'message' => Lang::get('resource::message.Color code is invalid'),
            ]);
        }

        $channel->update(['color' => $request->color]);
        return response()->json([
            'status' => 1,
        ]);
    }
}
