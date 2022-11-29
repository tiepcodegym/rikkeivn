<?php
namespace Rikkei\Welfare\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rikkei\Welfare\Model\WelEmployee;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Welfare\Model\Event;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Rikkei\Team\Model\Employee;
use Rikkei\Welfare\Model\WelAttachFee;
use Session;
use Rikkei\Welfare\View\EmployeeAttach;
use Rikkei\Welfare\Model\RelationName;

class WelRelativeAttachController extends Controller
{
    public function getBasic()
    {
        return view('datatables.eloquent.basic');
    }

    public function getBasicData($id)
    {
        $users = WelEmployeeAttachs::getGridData($id);
        $data = Datatables::of($users)
            ->editColumn('joined', function ($user) {
                return '<input class="edit-checkbox" type="checkbox" name="is_joined"
                 value="1"' . ($user->joined == 1 ? 'checked ' : '') . (date('Y-m-d H:i:s')< $user->end_at_exec ? ' disabled' : '').'>';
            })
            ->editColumn('gender', function($user) {
                if ($user->gender == WelEmployeeAttachs::GENDER_MALE) {
                    return trans('team::view.Male');
                } else {
                    return trans('team::view.Female');
                }
            })
            ->setRowAttr([
                'id' => function($user) {
                    return $user->id;
                },
                'wel_id' => function($user){
                    return $user->wel_id;
                },
                'employee_id' => function($user){
                    return $user->employee_id;
                },
                'emplname' => function($user) {
                    return $user->empname;
                }
            ])
            ->removeColumn('wel_id')
            ->removeColumn('employee_id')
            ->make();
        return $data;
    }

    public function saveAjax()
    {
        $record = WelEmployeeAttachs::where('employee_id', Input::get('employee_id'))
            ->where('welfare_id', Input::get('wel_id'))
            ->update([Input::get('name') => Input::get('value')]);
        return 'users-table-emp-att';
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $relativeAttach = WelEmployeeAttachs::infoAttached($request->id);

        return response()->json($relativeAttach);
    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $relation = isset($request->relatives) ? $request->relatives : $request->all();
        $requests = array_merge($relation, ['card_id' => $relation['relative_card_id'], 'employee_id' => $relation['relative_employee_id']]);
        $welfare = Event::find($requests['welid']);

        if(!$welfare) {
            return back()->withInput()->withErrors(trans('welfare::view.Not Found Welfare'));
        }

        $rules = [
            'name' => 'required',
            'relation_name_id' => 'required|integer',
            'phone' => 'max:15'
        ];
        $validator = Validator::make ($requests, $rules );

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->messages());
        }
        $checkPriority = WelAttachFee::checkPriority($requests);
        if (!$checkPriority) {
            return response()->json([
                'status' => 0,
            ]);
        }
        if (isset($requests['id']) && $requests['id'] != '') {
            $relativeAttach = WelEmployeeAttachs::find($requests['id']);
        } else {
            $relativeAttach = new WelEmployeeAttachs();
        }
        $birthday = (isset($requests['birthday']) && $requests['birthday'] != '') ? Carbon::createFromFormat('Y-m-d', $requests['birthday']) : Null;
        $employeeId = (isset($requests['relative_employee_id']) && $requests['relative_employee_id'] != '') ? $requests['relative_employee_id'] : Auth::id();

        $relativeAttach->fill($requests);
        $relativeAttach->birthday = $birthday;
        $relativeAttach->welfare_id = $requests['welid'];
        $relativeAttach->employee_id = $employeeId;

        $relativeAttach->save();

        if (isset($requests['relative_employee_id']) && $requests['relative_employee_id'] != '') {
            return response()->json([
                'status' => 'ok',
                'count' => WelEmployeeAttachs::checkRelativeAttachByWelId($requests['welid']),
            ]);
        } else {
            return ['status' => 'raletion'];
        }

    }

    /**
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function delete(Request $request)
    {
        $relativeAttach = WelEmployeeAttachs::find($request->welid);

        try {
            $relativeAttach->delete();
        } catch (Exception $ex) {
            return redirect()->route('welfare::welfare.event.index')->withErrors($ex);
        }
        return response()->json([
                'status' => 'ok',
                'count' => WelEmployeeAttachs::checkRelativeAttachByWelId($relativeAttach->welfare_id),
        ]);
    }

    /**
     * show view employee attach
     */
    public function reviewEmployeeAttach(Request $req) {
        $data = WelEmployeeAttachs::getEmployeeAttach($req->idEmployee,$req->event);
        $htmlData = view('welfare::event.include.table_employee_attach',compact('data'))->render();
        return response()->json($htmlData);
    }

     /**
     *
     *
     */
    public function editAttachEmployee(Request $request) {
        if(isset($request->idEmployee) && $request->idEmployee) {

            $relativeAttach = new WelEmployeeAttachs();
            $relativeAttach['employeeId'] = $request->idEmployee;
            $relativeAttach['employeeName'] = Employee::getNameEmpById($request->idEmployee);
            $relativeAttach['welfare_id'] = $request->welfare_id;
            $htmlData = view('welfare::event.include.form_edit_attach',compact('relativeAttach'))->render();
        } else {
            $relativeAttach = WelEmployeeAttachs::getEmployeeAttachById($request->id);
            $htmlData = view('welfare::event.include.form_edit_attach',compact('relativeAttach'))->render();
        }
        return response()->json($htmlData);
    }

    /**
     * Save data edit attach employee
     */
    public function saveAttachEmployee(Request $req) {
        $data = $req->all();
        $data['card_id'] = $data['inforcar'];
        if(WelAttachFee::checkEmployAttachFavorable($data)) {
            if ($data['id'] != null) {
                $editData = WelEmployeeAttachs::find($data['id']);
                if($editData) {
                    $editData->update($data);
                    $json['type'] = 1;
                    $json['status'] = true;
                    $json['data'] = WelEmployeeAttachs::getEmployeeAttachById($data['id']);
                    return response()->json($json);
                } else {
                    $json['type'] = 1;
                    $json['status'] = false;
                    return response()->json($json);
                }
            } else {
                $editData = new WelEmployeeAttachs();
                $data['welfare_id'] = $req->welfare_id;
                $editData->fill($data);
                $editData->save();
                $json['type'] = 2;
                $json['status'] = true;
                return response()->json($json);
            }
        } else {
            $json['type'] = 3;
            $json['status'] = false;
            return response()->json($json);
        }
    }

    /**
     * show relative employee attach
     * @param Request $request
     * @return id relative
     */
    public function checkFavorable(Request $request) {
        $data = $request->all();
        $relative = WelAttachFee::checkFavorable($data);
        $option['data'] = view('welfare::event.include.ajax_relative', compact('relative'))->render();
        return response()->json($option);
    }

    /**
     * Add info person attach into session
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function addSessionAttached(Request $request)
    {
        $req        = array_merge($request->all(), ['card_id' => $request->relative_card_id, 'employee_id' => Auth::id()]);
        $oldSession = $request->session()->has('attached') ? $request->session()->get('attached') : null;
         $welFeeAttach = WelAttachFee::where('wel_id', $req['welid'])->first();
        if (isset($request->key) && $request->key != "") {
            $count = (int) $request->key;
        } else {
            $count = ($oldSession == null) ? 0 : count($oldSession->items);
        }

        $employeeAtt = new EmployeeAttach($oldSession);

        $employeeAtt->add($req, $count);

        // check number attached full
        if ($req['support_cost'] == WelAttachFee::Fee_0) {
            if ($employeeAtt->numberFeeFree > $welFeeAttach->fee_free_count) {
                return ['status' => 'fail'];
            }
        }
        if ($req['support_cost'] == WelAttachFee::Fee_50) {
            if ($employeeAtt->numberFee50 > $welFeeAttach->fee50_count) {
                return ['status' => 'fail'];
            }
        }
        if ($req['support_cost'] == WelAttachFee::Fee_100) {
            if ($employeeAtt->numberFee100 > $welFeeAttach->fee100_count) {
                return ['status' => 'fail'];
            }
        }

        $request->session()->put('attached', $employeeAtt);
        $request->session()->save();

        return ['status' => 'raletion'];
    }

    /**
     * Edit info person attach in session
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function editSessionAttached(Request $request)
    {
        $key = (int) $request->key;
        if (Session::has('attached')) {
            $attached      = Session::get('attached');
            $relation_name = RelationName::getNameById($attached->items[$key]['relation_name_id']);
            $respone       = array_merge($attached->items[$key], ['key' => $key, 'relation_name' => $relation_name]);
            return $respone;
        }
    }

    /**
     * Delete info person attach in session
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function deleteSessionAttached(Request $request)
    {
        $key = (int) $request->welid;
        if (Session::has('attached')) {
            $attached = Session::get('attached');
            if (isset($attached->items[$key])) {
                if($attached->items[$key]['support_cost'] == WelAttachFee::Fee_0) {
                    $attached->numberFeeFree--;
                }
                if($attached->items[$key]['support_cost'] == WelAttachFee::Fee_50) {
                    $attached->numberFee50--;
                }
                if($attached->items[$key]['support_cost'] == WelAttachFee::Fee_100) {
                    $attached->numberFee100--;
                }
                unset($attached->items[$key]);
            }

            $request->session()->put('attached', $attached);
            $request->session()->save();

            return response()->json([
                    'status' => 'ok',
            ]);
        }
    }

    /**
     * Get list option relation
     *
     * @param int $welId
     * @param int $supportCost
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function selectAjax($welId, $supportCost, Request $request)
    {
        $relationId = WelAttachFee::where('wel_id', $welId)->first();

        if ($supportCost == WelAttachFee::Fee_0) {
            $arrayId  = explode(',', $relationId->fee_free_relative);
        }
        if ($supportCost == WelAttachFee::Fee_50) {
            $arrayId  = explode(',', $relationId->fee50_relative);
        }
        if ($supportCost == WelAttachFee::Fee_100) {
            $arrayId  = explode(',', $relationId->fee100_relative);
        }
        if ($supportCost == 0) {
            $arrayId = RelationName::pluck('id')->toArray();
        }

        $result = RelationName::select('id', 'name')
            ->where('name','LIKE',"%$request->q%")
            ->whereIn('id', $arrayId)
            ->get();

        return response()->json($result);
    }
}
