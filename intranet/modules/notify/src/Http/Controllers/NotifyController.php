<?php

namespace Rikkei\Notify\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Notify\Model\NotifyReciever;
use Rikkei\Notify\Model\Notification;
use Rikkei\Notify\View\NotifyView;
use Rikkei\Core\View\Breadcrumb;

class NotifyController extends Controller
{
    /*
     * show all notifications
     */
    public function index(Request $request)
    {
        Breadcrumb::add(trans('notify::view.Notification'));

        $perPage = NotifyView::ALL_PER_PAGE;
        $data = ['per_page' => $perPage];
        $collection = Notification::getByUser(array_merge($data, $request->all()));
        return view('notify::index', compact('collection', 'perPage'));
    }

    /*
     * ajax load notify data
     */
    public function loadNotify(Request $request)
    {
        $collection = Notification::getByUser($request->all(), true);
        return [
            'notify_list' => $collection->items(),
            'total' => $collection->total(),
            'next_page_url' => $collection->appends($request->except('page'))->nextPageUrl(),
            'current_page' => $collection->currentPage()
        ];
    }

    /**
     * set read notify
     */
    public function read(Request $request)
    {
        $isReadAll = $request->get('read_all');
        if ($isReadAll) {
            return NotifyReciever::setReadAll();
        }
        $notifyId = $request->get('notify_id');
        if ($notifyId) {
            return NotifyReciever::setRead($notifyId, auth()->id());
        }
        $notifyUrl = $request->get('url');
        if ($notifyUrl) {
            return NotifyReciever::setReadUrl($notifyUrl, auth()->id());
        }
        return response()->json(trans('notify::message.Not found item'), 422);
    }

    /*
     * reset notify number
     */
    public function resetNotiNum()
    {
        return response()->json(NotifyReciever::resetNotiNum(auth()->id()));
    }

    /**
     * update new data
     */
    public function refreshData(Request $request)
    {
        $lastId = $request->get('last_id');
        if ($lastId === '' || !is_numeric($lastId)) {
            return response()->json(false, 422);
        }
        $collection = Notification::getByUser(['last_id' => $lastId], true);
        return [
            'notify_list' => $collection,
            'num_noti' => auth()->user()->notify_num
        ];
    }
}

