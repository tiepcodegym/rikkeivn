<?php

namespace Rikkei\Music\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Music\Model\MusicOrder;
use Rikkei\Music\Model\MusicOffice;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;
use Rikkei\Core\View\Breadcrumb;
use Illuminate\Support\Facades\URL;
use Session;

class ManageMusicController  extends Controller
{
    /**
    * list all music order
    */ 
    public function order()
    {
        return view('music::manage.order.order',[
                'collectionModel' => MusicOrder::getGridData(),
                'titleHeadPage' => Lang::get('music::view.Music order')
                ]);
    }

    /**
    * delete music order
    */
    public function deleteOrder($orderId)
    {
        $order = MusicOrder::where('id', $orderId)->get();
        if(!count($order)){
            return redirect()->route('music::manage.order')->with('error',Lang::get('music::view.Not found'));
        }
        MusicOrder::delOrder($orderId);
        return redirect()->route('music::manage.order')->with('delSuccess',Lang::get('music::view.Delete success'));
    }

    /**
    * list all music offices
    */ 
    public function offices()
    {
        return view('music::manage.office.office',[
                'collectionModel' => MusicOffice::getGridData(),
                'titleHeadPage' => Lang::get('music::view.Music office')
                ]);
    }

    /**
    * delete music office
    */
    public function deleteOffice($officeId)
    {
        MusicOffice::delOffice($officeId);
        if(Input::get('del-hidden')){
            return redirect()->route('music::manage.offices.create')->with('delSuccess',Lang::get('music::view.Office delete success'));
        }
        return redirect()->route('music::manage.offices')->with('delSuccess',Lang::get('music::view.Delete success'));
    }

    /**
    * create office
    */
    public function createOffice()
    {
        Breadcrumb::add('Offices', URL::route('music::manage.offices'));
        Breadcrumb::add('Create office');
        return view('music::manage.office.edit',[
            'titleHeadPage' => Lang::get('music::view.Office create'),
            'office' => new MusicOffice()
            ]);
    }

    public function editOffice($officeId)
    {
        Breadcrumb::add('Offices', URL::route('music::manage.offices'));
        Breadcrumb::add('Edit office');
        $office = MusicOffice::getOfficeFollowId($officeId);
        if($office){
            return view('music::manage.office.edit',[
            'titleHeadPage' => Lang::get('music::view.Office create'),
            'office' => $office
            ]);
        }else {
            return redirect()->route('music::manage.offices.create')->with('error',Lang::get('music::view.Office edit error'));
        }
    }

    /**
    * save office
    */ 
    public function saveOffice()
    {
        $id = Input::get('id');
        if($id) {
            $music_office = MusicOffice::find($id);
            if(!$music_office) {
                return redirect()->route('music::manage.offices.create')->with('error',Lang::get('core::message.Error input data!'));
            }
        }else {
            $music_office = new MusicOffice();
        }
        
        $dataOffice = Input::get('music_offices');
        if(!isset($dataOffice['employee_noti'])) {
            $dataOffice['employee_noti'] = null;
        }

        $allStatus = implode(',', array_keys(MusicOffice::getAllStatus()));
        $allNoti = implode(',',MusicOffice::getAllIdNoti());
        $validator = Validator::make($dataOffice, [
            'name' => 'required|max:50|unique:music_offices,name,'.$id.',id,deleted_at,NULL',
            'status' => 'required|in:' . $allStatus,
            'sort_order' =>'numeric|min: 1',
            'employee_noti' => 'numeric|min:1|in:'.$allNoti,
        ]);

        if(Input::get('time')) {
            $times = array_diff(Input::get('time'),array(''));
        }
       
        if ($validator->fails()||(isset($times)&&count($times)!=count(array_unique($times)))) {
            return redirect()->route('music::manage.offices.create')->with('error',Lang::get('core::message.Error input data!'));
        }
        $music_office->setData($dataOffice);

        $officeTime = array ();
        if(isset($times)) {
            for($i = 0; $i < count($times); $i++) {
                if(isset($times[$i])&&$times[$i]!="") {
                    $officeTime[] = $times[$i];
                }
            }
        }
        
        try {
            $music_office->save();
            $music_office->saveTime($officeTime);
            return redirect()->route('music::manage.offices.edit',$music_office->id)->with('saveSuccess',Lang::get('core::message.Save success'));
        }catch (Exception $ex) {
            Log::info($ex);
            return redirect()->route('music::manage.offices.create')->with('error',Lang::get('core::message.Error system, please try later!'));
        }
    }

    /**
    * check nam office by ajax
    */ 
    public function checkName() 
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        
        if(MusicOffice::countOfficebyName(trim(Input::get('name')),Input::get('edit'))>0) {
            return 0;
        }else {
            return 1;
        }
    }

    /**
    * delete many Order
    */
    public function delManyOrder()
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        MusicOrder::delMany(Input::get('orderIds'));
        Session::flash('delSuccess',Lang::get('music::view.Delete success'));
        return response()->json([
            'delSuccess' => Lang::get('music::view.Delete success')
        ]);
    }
}