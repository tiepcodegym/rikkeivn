<?php
namespace Rikkei\Welfare\Http\Controllers;

use Illuminate\Http\Request;
use Rikkei\Welfare\View\TeamList;
use \Rikkei\Core\View\Form;
use Rikkei\Team\Model\Employee;
use Yajra\Datatables\Datatables;
use Rikkei\Welfare\Model\WelEmployee;
use Rikkei\Welfare\Model\Event;
use Rikkei\Welfare\Model\WelEmployeeAttachs;


class ParticipantController extends \Rikkei\Core\Http\Controllers\Controller
{
    public function index()
    {
        $teamTreeHtml = TeamList::getTreeHtml(Form::getData('id'));
        return view('welfare::participant.index',[
            'teamTreeHtml' => $teamTreeHtml,
        ]);
    }

    /**
     * get list employee by team
     */
    public function getEmployeeParticipants($id,$event) {

        $employee = WelEmployee::getAllEmployeesOfTeam($id,$event);
        $table = Datatables::of($employee)
            ->addColumn('action', function ($table) {
                return '<input type="checkbox" class="check_item" data-id="'.
                    $table->id.'"/>';
            })
            ->setRowAttr([
                'data-id' => function ($data) {
                    return $data->id;
                },
            ])
            ->editColumn('action', function ($table) {
                $html='<input class="check_item" type="checkbox" data-id="'.
                    $table->id.'"';
                $checked='';
                if ($table->wel_id != null) {
                    $checked = ' checked';
                }
                $html=$html.$checked.'/>';
                return $html;
            })
            ->make(true);
            return $table;
    }

    /**
     * save employee participants
     */
    public function saveEmployeeParticipants(Request $request)
    {
        $data = $request->all();
        $welEmployee = WelEmployee::where('wel_id', $data['event']);
        $welEmployeeAttachs = WelEmployeeAttachs::where('welfare_id', $data['event']);
        $event = Event::find($data['event']);
        if (!isset($data['data'])) {
            $welEmployee->delete();
            $welEmployeeAttachs->delete();
            $allow = WelEmployee::checkWel($data['event']);
            return response()->json([
                'status' => true,
                'allow' => $allow,
            ]);
        }
        $welEmployeeAttachs->whereNotIn('employee_id', $data['data'])->delete();
        $event->welfareEmployee()->sync($data['data']);
        $allow = WelEmployee::checkWel($data['event']);
        return response()->json([
            'status' => true,
            'allow' => $allow,
        ]);
    }

    /**
     * get employee team
     */
    public function getTeamEmployee(Request $request)
    {
        $idEmployee['employee'] = WelEmployee::getAllIdEmployeesOfTeam($request->idTeam);
        $idEmployee['type'] = $request->type;
        return response()->json($idEmployee);
    }

}

