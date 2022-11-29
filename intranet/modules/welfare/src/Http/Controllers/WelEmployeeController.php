<?php

namespace Rikkei\Welfare\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rikkei\Welfare\Model\WelEmployee;
use Rikkei\Welfare\Model\WelfareParticipantTeam;
use Yajra\Datatables\Datatables;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Welfare\Model\Event;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Welfare\Model\RelationName;
use Rikkei\Welfare\Model\WelEmployeeAttachs;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Employee;
use Illuminate\Support\Facades\Lang;
use DB;
use Rikkei\Welfare\Model\WelfareFee;
use Session;
use Rikkei\Welfare\View\EmployeeAttach;
use Rikkei\Welfare\Model\WelAttachFee;

class WelEmployeeController extends Controller
{

    public function getBasicData($id)
    {
        $filter = ['emp_code', 'emp_name', 'is_confirm', 'is_joined'];
        foreach ($filter as $field) {
            $options[$field] = Input::get($field);
        }
        $options['wel_id'] = $id;
        $users = WelEmployee::getGridData($options);
        $data = Datatables::of($users)
            ->editColumn('confirm', function ($user) {
                $html='<input class="edit-checkbox" type="checkbox" name="is_confirm" value="1" ';
                $checked='';$disabled=' disabled';
                if ($user->confirm == 1) {
                    $checked = ' checked';
                }
                $now = Carbon::now()->format('Y-m-d');
                if ($user->start_at_register <= $now && $now <= $user->end_at_register) {
                    $disabled = '';
                }
                $html=$html.$checked.$disabled.'>';
                return $html;
            })
            ->editColumn('joined', function ($user) {
                $html='<input class="edit-checkbox" type="checkbox" name="is_joined" value="1" ';
                $checked='';$disabled='';
                if ($user->joined == 1) {
                    $checked = ' checked';
                }
                if (date('Y-m-d H:i:s') < $user->end_at_exec){
                    $disabled=' disabled';
                }
                $html=$html.$checked.$disabled.'>';

                return $html;
            })
            ->editColumn('empFee', function ($user) {
                if ($user->confirm == 1) {
                    return number_format($user->cost_employee);
                }
                return 0;
            })
            ->editColumn('comFee', function ($user) {
                if ($user->confirm == 1) {
                    return number_format($user->cost_company);
                }
                return 0;
            })
            ->addColumn('action', function ($user) {
                if ($user->confirm == 1) {
                    return number_format($user->cost_company + $user->cost_employee);
                }
                return 0;
            })->setRowAttr([
                'wel_id' => function($user) {
                    return $user->wel_id;
                },
                'employee_id' => function($user) {
                    return $user->employee_id;
                }
            ])
            ->removeColumn('wel_id')
            ->removeColumn('employee_id')
            ->removeColumn('end_at_exec')
            ->removeColumn('end_at_register')
            ->removeColumn('cost_employee')
            ->removeColumn('cost_company')
            ->removeColumn('start_at_register')
            ->make(true);

        return $data;
    }

    /**
     * Save employee information for welfare
     *
     * @return string
     */
    public function saveAjax()
    {
        $data = Input::all();
        $checkDate = Event::find(Input::get('wel_id'));
        $costEvent = WelfareFee::getFeeByWelfare(Input::get('wel_id'));
        $updateCostEvent = WelEmployee::updateCostEmployeeJoin($data,$costEvent);
        return response()->json(['status'=>true]);
    }

    /**
     * Show employee welfare information to confirm participation
     *
     * @param int $id
     * @return \Illuminate\Support\Facades\Response
     */
    public function viewConfirm($id)
    {
        $welfare            = Event::getBasicInformation($id);
        $relation           = RelationName::pluck('name', 'id')->toArray();
        $welRelativeAttachs = WelEmployeeAttachs::getRelativeAttachByWelId($id, Auth::id());
        $empIsConfirm      = WelEmployee::checkEmployeeConfirm($id, Auth::id());
        $listAttachFee      = WelAttachFee::getLisfeeWellAttach();
        $attachFee          = WelAttachFee::infoWelAttachFee($id);
        if (Session::has('attached')) {
            $attach = Session::get('attached');
            $check = true;
        } else {
            $check = false;
        }
        Breadcrumb::add(trans('welfare::view.Registration for participation in the event'));
        return view('welfare::event.confirm', [
            'welfare' => $welfare,
            'relation' => $relation,
            'welRelativeAttachs' => $welRelativeAttachs,
            'isConfirm' => $empIsConfirm,
            'attach' => isset($attach->items)  ? $attach->items : null,
            'check' => $check,
            'listAttachFee' => $listAttachFee,
            'attachFee' => $attachFee,
        ]);
    }

    /**
     * Confirm employee participation welfare
     *
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response;
     */
    public function confirm(Request $request)
    {
        $welfare = Event::find($request->welid);
        if (!$welfare) {
            return redirect()->back()->withInput()
                    ->withErrors(Lang::get('welfare::view.Not Found Welfare'));
        }

        $endRegister = Carbon::createFromFormat('Y-m-d H:i:s', $welfare->end_at_register)->setTime(23, 59, 59);

        if ($endRegister->lte(Carbon::now())) {
            return redirect()->back()->withInput()
                    ->withErrors(Lang::get('welfare::view.Event registration time has expired'));
        }
        $emplId = Auth::id();

        if (!$request->is_register_relatives) {
            $keys = WelEmployeeAttachs::getListRelativeAttachByWelEmpl($request->welid, $emplId);
            WelEmployeeAttachs::whereIn('id', $keys)->delete();
        }
        $costEvent = WelfareFee::getFeeByWelfare($request->welid);
        $data = [
            'employee_id' => $emplId,
            'wel_id' => $request->welid,
            'name' => 'is_confirm',
        ];

        if ($request->has('submit_destroy') && $request->submit_destroy != '') {
            $keys = WelEmployeeAttachs::getListRelativeAttachByWelEmpl($request->welid, $emplId);
            WelEmployeeAttachs::whereIn('id', $keys)->delete();

            $data['value'] = WelEmployee::UN_CONFIRM;
            WelEmployee::updateCostEmployeeJoin($data, $costEvent);
            if (Session::has('attached')) {
                Session::forget('attached');
            }
            return [
                'status' => 'confirm',
                'messages' => Lang::get('welfare::message.You unfollow event'),
            ];
        } else {
            $data['value'] = WelEmployee::IS_CONFIRM;
            WelEmployee::updateCostEmployeeJoin($data, $costEvent);
            if (Session::has('attached')) {
                $attached = Session::get('attached');
                if (isset($attached->items)) {
                    WelEmployeeAttachs::saveAttachedWithSession($attached->items);
                }
                Session::forget('attached');
            }
            return back()->with('messages', ['success' => [Lang::get('welfare::view.Confirm Success')]]);
        }
    }

    public function previewConfirm($id)
    {
        $welfare            = Event::getBasicInformation($id);
        $relation           = RelationName::pluck('name', 'id')->toArray();
        $welRelativeAttachs = WelEmployeeAttachs::getRelativeAttachByWelId($id, Auth::id());
        $attachFee          = WelAttachFee::infoWelAttachFee($id);

        return view('welfare::event.include.confirm_preview', [
            'welfare' => $welfare,
            'relation' => $relation,
            'welRelativeAttachs' => $welRelativeAttachs,
            'attachFee' => $attachFee,
        ]);
    }

    public function getMemberByTeam($id)
    {
        $teamPath = Team::getTeamPath();
        if ($teamPath[$id]) {
            $teamIds = isset($teamPath[$id]['child']) ? $teamPath[$id]['child'] : $teamPath[$id];
        }
        $emplOfTeam = TeamMember::whereIn('team_id', $teamIds)->lists('employee_id')->toArray();

        $infoEmpl = WelEmployee::getInfoEmployeeByEmplIds($emplOfTeam);

        return response()->json($infoEmpl);
    }

    /**
     *  cancel employee event
     */
    public function deleteEmployeeParticipants($event,$id) {
        $employeeCancel = WelEmployee::where('employee_id',$id)
            ->where('wel_id',$event);
        $employeeAtachs = WelEmployeeAttachs::where('welfare_id',$event)
            ->where('employee_id',$id);
        DB::beginTransaction();
        try {
            if($employeeCancel) {
                $employeeCancel->delete();
            }
            if (count($employeeAtachs) > 0) {
                $employeeAtachs->delete();
            }
        DB::commit();
        return response()->json(['status'=>true]);
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
            return response()->json(['status'=>false]);

        }
    }

    public function showEmployeeCost(Request $request) {
        $dataCostEmployee = WelEmployee::getCostEvent($request->idEmployee,$request->event);
        if($dataCostEmployee != null) {
            $costEmployee['em'] = $dataCostEmployee->cost_employee;
            $costEmployee['com'] = $dataCostEmployee->cost_company;
            $costEmployee['name'] = Employee::getNameEmpById($request->idEmployee);
            $viewHtml = view('welfare::event.include.form_edit_cost', compact('costEmployee'))->render();
        } else {
            $viewHtml = null;
        }
        return response()->json($viewHtml);
    }

    /**
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveEmployeeCost(Request $request)
    {
        $emFee = str_replace(',', '', $request->emFee);
        $comFee = str_replace(',', '', $request->comFee);
        $dataCostEmployee = WelEmployee::updateCostEvent($request, $emFee, $comFee);
        return response()->json($dataCostEmployee);
    }

    /**
     *
     * @param int $welId
     * @param Request $request
     * @return \Illuminate\Support\Facades\Response
     */
    public function searchAjax($welId, Request $request)
    {
        $result = WelEmployee::getOptionEmployee($welId, $request->q);
        return response()->json($result);
    }

    /**
     * Save all employee join
     */
    public function saveAllEmployeeJoin(Request $request) {
        $data = $request->all();
        $checkDate = Event::find($data['id']);
        if(date('Y-m-d H:i:s') > $checkDate->end_at_exec) {
            $status = WelEmployee::updateEmployeeJoined($data);
        } else {
            $status = false;
        }
        return response()->json($status);
    }

    /**
     * Edit confirm welfare of employee
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function editConfirm($id)
    {
        $welRelariveAttach = WelEmployeeAttachs::where([
                ['welfare_id', $id],
                ['employee_id', Auth::id()],
            ])->get();

        if (isset($welRelariveAttach) && count($welRelariveAttach)) {
            $key         = 0;
            $employeeAtt = null;
            foreach ($welRelariveAttach as $item) {
                $arrayAtt    = array_merge($item->toArray(), ['welid' => $item->welfare_id]);
                $employeeAtt = new EmployeeAttach($employeeAtt);
                $employeeAtt->add($arrayAtt, $key);
                $key++;
            }
            Session::put('attached', $employeeAtt);
            Session()->save();
        } else {
            Session::put('attached', []);
            Session()->save();
        }

        return redirect()->route('welfare::welfare.confirm.welfare', ['id' => $id]);
    }

}
