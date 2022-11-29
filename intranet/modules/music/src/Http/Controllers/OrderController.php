<?php

namespace Rikkei\Music\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Pagination\Paginator;
use Rikkei\Music\Model\MusicOrder;
use Rikkei\Music\Model\MusicOffice;
use Rikkei\Music\Model\MusicOrderVote;
use Illuminate\Support\Facades\Input;
use Rikkei\Music\View\ViewMusic;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Rikkei\Team\View\Permission;
use Cookie;
use Exception;
use Auth;

class OrderController extends Controller
{

    /**
     * List order music
     * 
     * @return type
     */
    public function index()
    {
        $office_id = Cookie::get('office_id');
        if (empty($office_id)) {
            $office = MusicOffice::where('status', MusicOffice::ENABLE_STATUS)->first();
            if (!empty($office)) {
                $office_id = $office->id;
            }
        }

        if (!empty($office_id)) {
            return redirect()->route('music::order.office', $office_id);
        }
        
        return view('music::frontend.index', [
            'collectionModel' => [],
            'office_id'       => '',
            'isAdmin'         => false,
        ]);
    }
    
    /**
     * List order music
     * 
     * @return type
     */
    public function office($office_id = null)
    {
        $office = MusicOffice::getOffice($office_id);
        if(empty($office)){
            return redirect()->route('music::order')->with('error',Lang::get('music::view.Office does not exit!'))->withCookie(Cookie::forget('office_id'));
        }
        $orders = MusicOrder::getOrderData($office_id)->paginate(5);
        Cookie::queue('office_id', $office_id, 14400);
        return view('music::frontend.index', [
            'collectionModel' => $orders,
            'office_id'       => $office_id,
            'isAdmin'         => MusicOffice::isAdmin($office_id)
        ]);
    }

    /**
     * Save order music
     * 
     * @return json
     */
    public function save()
    {
        $order     = new MusicOrder();
        $dataOrder = Input::get('music');
        $validator = Validator::make($dataOrder, [
                    'link'    => 'required|url',
                    'name'    => 'required',
                    'message' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('music::order.office', $dataOrder['office_id'])->with('error', Lang::get('core::message.Error input data!'));
        }

        if (empty($dataOrder['office_id'])) {
            return redirect()->route('music::order')->with('error', Lang::get('music::view.Office does not exit!'));
        }
        $dataOrder['link'] = htmlspecialchars($dataOrder['link']);
        $dataOrder['name'] = htmlspecialchars($dataOrder['name']);
        $dataOrder['sender'] = htmlspecialchars($dataOrder['sender']);
        $dataOrder['receiver'] = htmlspecialchars($dataOrder['receiver']);
        $dataOrder['message'] = htmlspecialchars($dataOrder['message']);
        $dataOrder['message'] = preg_replace('/\r?\n/', '<br />', $dataOrder['message']);
        $order->setData($dataOrder);
        try {
            $order->save();
            return redirect()->route('music::order.office', $order->office_id)->with('saveSuccess', Lang::get('music::view.Save success'));
        } catch (Exception $ex) {
            Log::info($ex);
            return redirect()->route('music::order.office', $dataOrder['office_id'])->with('error', Lang::get('music::view.Error system, please try later!'));
        }
    }
    
    /**
     * Music vote
     */
    public function vote()
    {
        $greater   = false;
        $totalReal = 0;

        $orderId   = Input::get('order_id');
        MusicOrderVote::vote($orderId);
        $voteCount = ViewMusic::compactTotal(MusicOrderVote::getTotalVote($orderId), $totalReal, $greater);
        return ["total_vote" => $voteCount,
            "totalReal"  => $totalReal
        ];
    }
    
    /**
     * Update is_play order
     * 
     * @return 
     */
    public function play()
    {
        $orderId = Input::get('order_id');
        try {
            MusicOrder::isPlay($orderId);
            return ["message" => 'success'];
        } catch (Exception $ex) {
            return ["message" => 'error'];
        }
    }

}
