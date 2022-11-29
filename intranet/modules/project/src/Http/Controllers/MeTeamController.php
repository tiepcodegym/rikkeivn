<?php

namespace Rikkei\Project\Http\Controllers;

/**
 * Description of MeEvalController
 *
 * @author lamnv
 */
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Project\Model\MeEvaluation;
use Rikkei\Project\Model\MeHistory;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\TeamList;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\Team\Model\TeamMember;
use Carbon\Carbon;
use Validator;
use DB;

class MeTeamController extends Controller {
    
    /**
     * construct
     */
    public function _construct() {
        Breadcrumb::add(trans('project::me.Monthly Evaluation'), route('project::project.eval.index'));
        Menu::setActive('team');
//        app()->setLocale('vi');
    }
    
    /**
     * create view
     * @return type
     */
    public function create() {
        Breadcrumb::add(trans('project::me.Create'));
        $statuses = MeEvaluation::arrayStatus();
        $normalAttrs = MeAttribute::getNormalAttrs();
        $performAttrs = MeAttribute::getPerformAttrs();
        
        $scope = Permission::getInstance();
        $currUser = $scope->getEmployee();
        $teamTbl = Team::getTableName();
        $fullTeam = false;
        if ($scope->isScopeCompany(null, 'project::team.eval.create')) {
            $teamList = TeamList::toOption(null, false, false);
            $fullTeam = true;
        } elseif ($scope->isScopeTeam(null, 'project::team.eval.create')) {
            $teamList = Team::whereIn('id', function ($query) use ($currUser) {
                $query->select('team_id')
                        ->from(TeamMember::getTableName())
                        ->where('employee_id', $currUser->id);
            })->get();
        } elseif ($scope->isScopeSelf(null, 'project::team.eval.create')) {
            $teamList = Team::where($teamTbl.'.leader_id', '=', $currUser->id)
                        ->select($teamTbl . '.id', $teamTbl . '.name')
                        ->get();
        }
        //list months
        $selectMonths = [];
        $endMonth = Carbon::now()->startOfMonth();
        $startMonth = clone $endMonth;
        $startMonth->subMonthNoOverflow()->startOfMonth();
        while ($startMonth->lte($endMonth)) {
            array_unshift($selectMonths, ['string' => $startMonth->format('Y-m'), 'timestamp' => $startMonth->format('Y-m')]);
            $startMonth->addMonth();
        }
        $prevMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth()->format('Y-m');
        return view('project::me.create_team', compact('teamList', 'fullTeam', 'normalAttrs', 'performAttrs', 'selectMonths', 'prevMonth', 'statuses'));
    }
    
    /**
     * load member item
     * @param Request $request
     * @return string|int
     */
    public function loadMembers(Request $request) {
        $valid = Validator::make($request->all(), [
            'team_id' => 'required',
            'month' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('project::me.No result'), 422);
        }
        $month = $request->get('month');
        $teamId = $request->get('team_id');
        $team = Team::find($teamId);
        if (!$team) {
            return response()->json(trans('project::me.No result'), 422);
        }
        $scope = Permission::getInstance();
        $currUser = $scope->getEmployee();
        $leader = $currUser;
        $leaderId = $leader->id;

        $dataMembers = MeEvaluation::getMembersOfTeam($teamId, $month, $leader->isLeader() ? [$leaderId] : []);
        $members = $dataMembers['members'];
        $rangeTime = $dataMembers['range_time'];
        $attributes = MeAttribute::getAll();
        $result = [
            'eval_items' => '',
            'option_leaders' => '<option value="0">'.trans('project::me.Selection').'</option>',
            'status' => 1
        ];
        //return range time
        $result['range_time'] = array_map(function ($element) {
            return $element->format('Y-m-d');
        }, $rangeTime);
        if (count($members) < 1) {
            $result['eval_items'] = trans('project::me.No result');
            $result['status'] = 0;
            return $result;
        }
        $time = $request->get('month');
        $result['submited'] = false;
        $existsMonth = TimekeepingTable::checkExistsMonth($time, true);
        $meValidIds = [];
        foreach ($members as $member) {
            $item = MeEvaluation::createOrFindItemTeam($member, $time, $currUser, $existsMonth);
            $result['eval_items'] .= view(
                'project::me.template.team_eval_item',
                compact('item', 'member', 'attributes', 'currUser', 'existsMonth')
            )->render();
            if (in_array($item->status, [MeEvaluation::STT_DRAFT, MeEvaluation::STT_FEEDBACK])) {
                if (!$result['submited']) {
                    $result['submited'] = true;
                }
            }
            $meValidIds[] = $item->id;
        }
        //delete invalide me item
        MeEvaluation::delInvalidItems($teamId, $time, $meValidIds, 'team');

        $result['option_leaders'] = '<option value="'. $leader->id .'">'. e($leader->name) .'</option>';
        return $result;
    }
    
    public function submit(Request $request) {
        $valid = Validator::make($request->all(), [
            'eval_ids' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('project::me.No data'), 422);
        }
        
        $eval_ids = $request->get('eval_ids');
        if ($eval_ids) {
            $has_feedback = MeEvaluation::hasStatus($eval_ids, MeEvaluation::STT_FEEDBACK);
            $has_submited = MeEvaluation::hasSubmited($eval_ids);
            DB::beginTransaction();
            try {
                $results = [];
                $scope = Permission::getInstance();
                $currentUser = $scope->getEmployee();

                $new_assigne = $currentUser->id;
                foreach ($eval_ids as $id) {
                    $eval_item = MeEvaluation::find($id);
                    $old_assignee = $eval_item->assignee;
                    if (!$eval_item) {
                        throw new \Exception(trans('project::me.No data'));
                    }
                    $team = $eval_item->team;
                    if (!$team) {
                        return response()->json(trans('project::me.No data'), 422);
                    }
                    if ($team) {
                        $teamLeader = $currentUser;
                    }
                    //check status if feedback must do some action
                    if ($eval_item->status == MeEvaluation::STT_FEEDBACK) {
                        $checkCommentOrChange = MeHistory::checkUserAction($id, [MeHistory::AC_COMMENT, MeHistory::AC_NOTE, MeHistory::AC_CHANGE_POINT], $currentUser->id);
                        if (!$checkCommentOrChange) {
                            throw new \Exception(trans('project::me.You must comment or change point to submit'));
                        }
                    }
                    if (!in_array($eval_item->status, [MeEvaluation::STT_APPROVED, MeEvaluation::STT_CLOSED, MeEvaluation::STT_SUBMITED])) {
                        $eval_item->status = MeEvaluation::STT_APPROVED;
                        if ($eval_item->employee_id == $currentUser->id) {
                            $eval_item->status = MeEvaluation::STT_CLOSED;
                        }
                        $eval_item->increment('version');
                        MeHistory::create([
                            'eval_id' => $id,
                            'employee_id' => $currentUser->id,
                            'version' => $eval_item->version,
                            'action_type' => MeHistory::AC_APPROVED,
                            'type_id' => $eval_item->employee_id
                        ]);
                        MeHistory::create([
                            'eval_id' => $id,
                            'employee_id' => $currentUser->id,
                            'version' => $eval_item->version,
                            'action_type' => MeHistory::AC_SUBMIT,
                            'type_id' => $teamLeader->id
                        ]);
                    }
                    $eval_item->assignee = $eval_item->employee_id;
                    $eval_item->save();
                    $results[$id] = ['status' => $eval_item->status, 'can_change' => $eval_item->canChangePoint()];
                    
                    //check to send email
                    if ( (($new_assigne != $old_assignee) && $has_submited) || (!$has_submited) ) {
                        $stEmp = Employee::find($eval_item->employee_id);
                        if ($stEmp) {
                            $data['st_name'] = $stEmp->name;
                            $data['time'] = $eval_item->eval_time;
                            $data['pm_name'] = $currentUser->name;
                            $data['accept_link'] = route('me::profile.confirm', ['team_id' => $eval_item->team_id, 'time' => $eval_item->eval_time->format('Y-m')]);

                            $stEmail = $stEmp->email;
                            $emailQueue = new EmailQueue();
                            $emailQueue->setFrom('rikkeisoft@gmail.com', config('mail.name'))
                                    ->setTo($stEmail)
                                    ->setSubject(trans('project::me.Rikkei Monthly Evaluation'))
                                    ->setTemplate('project::me.mail.gl-accept', $data)
                                    ->setNotify(
                                        $stEmp->id,
                                        trans('project::me.Rikkei Monthly Evaluation') . ' created on Team ' . $team->name,
                                        $data['accept_link'], ['category_id' => RkNotify::CATEGORY_PERIODIC]
                                    )
                                    ->save();
                        }
                    }
                }
              
                DB::commit();
                return response()->json(['message' => trans('project::me.Accepted successful'), 'status' => MeEvaluation::STT_APPROVED, 'results' => $results]);
            } catch (\Exception $ex) {
                DB::rollback(); 
                return response()->json($ex->getMessage(), 422);
            }
        }
    }
    
}
