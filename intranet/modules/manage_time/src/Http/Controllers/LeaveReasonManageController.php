<?php

namespace Rikkei\ManageTime\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\ManageTime\Model\LeaveDayReason;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class LeaveReasonManageController extends Controller
{
    /**
    * list reason
    */ 
    public function listReason() 
    {
        return view('manage_time::leave.manage.list_reason', [
            'collectionModel' => LeaveDayReason::getGridData()
        ]);
    }

    /**
    * save reason
    */ 
    public function saveReason()
    {
        $data = Input::get();
        $id = $data['id'];
        if ($id) {
            $reason = LeaveDayReason::find($id);
            if (!$reason) {
                return redirect()->route('manage_time::admin.manage-reason-leave.index')->with('flash_error',Lang::get('manage_time::view.Error input data!'));
            }
        } else {
            $reason = new LeaveDayReason();
        }

        $reasonData['name'] = $data['name'];
        if ($data['sort_order'] == '') {
            $reasonData['sort_order'] = 1;
        } else {
            $reasonData['sort_order'] = $data['sort_order'];
        }
        $reasonData['salary_rate'] = $data['salary_rate'];
        $reasonData['used_leave_day'] = $data['used_leave_day'];

        $validator = Validator::make($reasonData, [
            'name' => 'required|max:255|unique:leave_day_reasons,name,'.$id.',id,deleted_at,NULL',
            'sort_order' =>'numeric|min: 0|integer',
            'salary_rate' => 'numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->route('manage_time::admin.manage-reason-leave.index')->with('flash_error',Lang::get('manage_time::view.Error input data!'));
        }
        
        $reason->setData($reasonData);
        $reason->save();
        return redirect()->route('manage_time::admin.manage-reason-leave.index')->with('flash_success', Lang::get('manage_time::view.Save success message'));
    }

    /**
    * delete music reason
    */
    public function deleteReason($reasonId)
    {
        $order = LeaveDayReason::find($reasonId);
        if (!$order){
            return redirect()->route('manage_time::admin.manage-reason-leave.index')->with('flash_error',Lang::get('manage_time::view.Error not found'));
        }
        $order->delete();
        return redirect()->route('manage_time::admin.manage-reason-leave.index')->with('flash_success',Lang::get('manage_time::view.Delete success'));
    }

    /**
    * check nam reason by ajax
    */ 
    public function checkName() 
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        if(LeaveDayReason::countReasonbyName(trim(Input::get('name')),Input::get('edit'))>0) {
            echo "false";
        }else {
            echo "true";
        }
    }
}