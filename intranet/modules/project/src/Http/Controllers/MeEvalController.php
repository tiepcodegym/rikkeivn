<?php

namespace Rikkei\Project\Http\Controllers;

/**
 * Description of MeEvalController
 *
 * @author lamnv
 */
use Rikkei\Core\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\Project;
use Rikkei\Project\Model\MeAttribute;
use Rikkei\Me\Model\ME as MeEvaluation;
use Rikkei\Project\Model\MeComment;
use Rikkei\Project\Model\MeHistory;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\View\View;
use Rikkei\Team\View\Permission;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Team\View\TeamList;
use Rikkei\Core\Model\EmailQueue;
use Validator;
use DB;
use Illuminate\Support\Facades\Lang;
use Rikkei\Project\View\View as ViewProject;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Project\Model\MeEvaluated;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Me\View\View as MeView;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Session;

class MeEvalController extends Controller {

    public function _construct() {
        Breadcrumb::add(trans('project::me.Monthly Evaluation'), route('project::project.eval.index'));
        if (request() && request()->route()) {
            $routeName = request()->route()->getName();
        } else {
            $routeName = null;
        }
        if ($routeName == 'project::project.profile.confirm') {
            Menu::setActive('profile');
        } else {
            Menu::setActive('team');
        }
        //app()->setLocale('vi');
    }

    /**
     * render create view
     * @return type
     */
    public function index()
    {
        $projects = MeEvaluation::getProjectsOfCurrentManager();
        $normalAttrs = MeAttribute::getNormalAttrs();
        $performAttrs = MeAttribute::getPerformAttrs();
        $statuses = MeEvaluation::arrayStatus();
        Breadcrumb::add(trans('project::me.Create'));
        return view('project::me.index', compact('projects', 'normalAttrs', 'performAttrs', 'statuses', 'teams'));
    }

    /*
     * load month of project
     */
    public function loadMonthsOfProject(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'project_id' => 'required'
        ]);
        $results = [];
        if ($valid->fails()) {
            return $results;
        }
        $response['data'] = MeEvaluation::listProjectMonths($request->get('project_id'));
        $allTeamOfProject = Project::getAllTeamOfProject($request->get('project_id'));
        if(!$allTeamOfProject) {
            $response['team'] = '';
        } else {
            $allTeamName = Team::getAllTeam();
            $response['team'] = Lang::get('project::me.Team') . ': ' . ViewProject::getLabelTeamOfProject($allTeamName, $allTeamOfProject);
        }
        return $response;
    }

    /**
     * render project and members template
     * @param Request $request
     * @return int
     */
    public function getProjectAndMembers(Request $request) {
        $valid = Validator::make($request->all(), [
            'project_id' => 'required',
            'month' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('project::me.No result'), 422);
        }
        $projectId = $request->get('project_id');
        $time = $request->get('month');
        $project = Project::getProjectById($projectId);
        if (!$project) {
            return response()->json(trans('project::me.No result'), 422);
        }
        $groupLeader = $project->groupLeader;
        if (!$groupLeader) {
            return response()->json(trans('project::me.Project not have group directory'), 422);
        }
        $dataMembers = MeEvaluation::getStaffsOfProject($project, $time);
        $rangeTime = $dataMembers['range_time'];
        $members = $dataMembers['members'];
        $attributes = MeAttribute::getAll();
        $currUser = Permission::getInstance()->getEmployee();

        $result = [
            'eval_items' => '',
            'option_leaders' => '<option value="0">'.trans('project::me.Selection').'</option>',
            'status' => 1
        ];
        //return range time
        $result['range_time'] = array_map(function ($element) {
            return $element->format('Y-m-d');
        }, $rangeTime);

        if ($members->isEmpty()) {
            $result['eval_items'] = trans('project::me.No result');
            $result['status'] = 0;
            return $result;
        }

        $projectPoint = MeEvaluation::getProjectPointLastMonth($projectId, $time);
        $option_leaders = $result['option_leaders'];

        $result['submited'] = false;
        $existsMonth = TimekeepingTable::checkExistsMonth($time, true);
        $meValidIds = [];
        foreach ($members as $member) {
            $item = MeEvaluation::createOrFindItem($member, $project, $time, $currUser, $projectPoint, $existsMonth);
            $result['eval_items'] .= view(
                'project::me.template.eval_item',
                compact('project', 'item', 'attributes', 'projectPoint', 'option_leaders', 'currUser', 'existsMonth')
            )->render();
            if (in_array($item->status, [MeEvaluation::STT_DRAFT, MeEvaluation::STT_FEEDBACK])) {
                if (!$result['submited']) {
                    $result['submited'] = true;
                }
            }
            $meValidIds[] = $item->id;
        }
        if ($groupLeader) {
            $result['option_leaders'] = '<option value="'.$groupLeader->id.'">'.e($groupLeader->name).'</option>';
        }
        //delete not avalid me item
        MeEvaluation::delInvalidItems($projectId, $time, $meValidIds);

        return $result;
    }

    /**
     * add point by attribute id
     * @param Request $request
     * @return type
     */
    public function addAttrPoint(Request $request) {
        $valid = Validator::make($request->all(), [
            'data_evals' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('project::me.Save error, please try again laster'), 422);
        }

        $dataEvals = $request->get('data_evals');
        $results = [];
        foreach ($dataEvals as $eval) {
            $eval_item = MeEvaluation::addAttrPoint($eval['eval_id'], $eval['attr_id'], $eval['point'], $eval['avg_point']);
            if ($eval_item) {
                if ($eval_item->status == MeEvaluation::STT_FEEDBACK) {
                    MeHistory::create([
                        'eval_id' => $eval_item->id,
                        'employee_id' => auth()->id(),
                        'version' => $eval_item->version,
                        'action_type' => MeHistory::AC_CHANGE_POINT,
                        'type_id' => $eval_item->id
                    ]);
                }
                array_push($results, ['id' => $eval_item->id, 'attr_id' => $eval['attr_id'], 'contribute_label' => $eval_item->contribute_label]);
            }
        }
        return $results;
    }

    /**
     * update avg point
     * @param Request $request
     * @return array
     */
    public function updateAvgPoint(Request $request) {
        $dataEvals = $request->get('data_evals');
        if (!$dataEvals || !is_array($dataEvals)) {
            return response()->json(trans('project::me.Save error, please try again laster'), 422);
        }
        $result = [];
        foreach ($dataEvals as $eval) {
            $evalItem = MeEvaluation::find($eval['eval_id']);
            if (!$evalItem || !$evalItem->canChangePoint()) {
                continue;
            }
            $evalItem->avg_point = $eval['value'];
            $evalItem->save();
            array_push($result, ['id' => $evalItem->id, 'label' => $evalItem->contribute_label]);
        }
        return $result;
    }

    /**
     * add a comment
     * @param Request $request
     * @return type
     */
    public function addComment(Request $request) {
        $valid = Validator::make($request->all(), [
            'eval_ids' => 'required',
            'attr_id' => 'required_without:comment_type',
            'comment_text' => 'required',
            'comment_type' => 'numeric|min:'.MeComment::TYPE_COMMENT.'|max:'.MeComment::TYPE_NOTE
        ]);
        if ($valid->fails()) {
            return response()->json(trans('project::me.Save error, please try again laster'), 422);
        }
        $eval_ids = $request->get('eval_ids');
        $attr_id = $request->has('attr_id') ? $request->get('attr_id') : null;
        $project_id = $request->get('project_id');
        $comment_text = $request->get('comment_text');
        $current_user = Permission::getInstance()->getEmployee();

        DB::beginTransaction();
        try {
            $auth = Auth()->user();
            $results = [];
            foreach ($eval_ids as $eval_id) {
                $eval_item = MeEvaluation::find($eval_id, ['version', 'status', 'id']);
                if (!$eval_item) {
                    throw new \Exception(trans('project::me.No data'));
                }
                if (!$project_id && $current_user->isLeader()) {
                    $type = MeComment::GL_TYPE;
                } else {
                    $type = MeComment::getCurrentUserInProjectType($request->get('project_id'), $current_user);
                    if ($request->has('is_leader') && $request->get('is_leader')) {
                        $type = MeComment::GL_TYPE;
                    }
                    if ($request->has('is_staff') && $request->get('is_staff')) {
                        $type = MeComment::ST_TYPE;
                    }
                }
                $comment_type = $request->has('comment_type') ? $request->get('comment_type') : MeComment::TYPE_COMMENT;
                $comment = MeComment::create([
                    'eval_id' => $eval_id,
                    'attr_id' => $attr_id,
                    'employee_id' => $current_user->id,
                    'employee_name' => $current_user->name,
                    'type' => $type,
                    'content' => $comment_text,
                    'comment_type' => $comment_type
                ]);
                $comment->avatar_url = $auth->avatar_url;
                $comment->google_id = $auth->google_id;
                $comment->name = $current_user->name;

                if ($request->get('return_item')) {
                    $comment_item = $comment;
                } else {
                    $comment_item = view('project::me.template.comment_item', compact('comment'))->render();
                }

                MeHistory::create([
                    'eval_id' => $eval_id,
                    'employee_id' => $current_user->id,
                    'version' => $eval_item->version,
                    'action_type' => $comment_type == MeComment::TYPE_COMMENT ? MeHistory::AC_COMMENT : MeHistory::AC_NOTE,
                    'type_id' => $comment->id
                ]);

                $results[$eval_id] = [
                    'id' => $eval_id,
                    'comment_item' => $comment_item,
                    'td_type' => 'td'.$comment->type_class,
                    'change_status' => [
                        'feedback' => $eval_item->canChangeStatus(MeEvaluation::STT_FEEDBACK),
                        'approved' => $eval_item->canChangeStatus(MeEvaluation::STT_APPROVED),
                        'closed' => $eval_item->canChangeStatus(MeEvaluation::STT_CLOSED)
                    ]
                ];
            }
            DB::commit();
            return $results;
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(trans('project::me.An error occurred'), 422);
        }
    }

    /**
     * remove comment
     * @param type $id
     * @param Request $request
     * @return type
     */
    public function removeComment($id, Request $request){
        $comment = MeComment::find($id);
        $results = [
            'comment_id' => $id,
            'change_status' => null,
            'has_comment' => null
        ];
        if (!$comment) {
            return response()->json(trans('project::me.No data'), 422);
        }
        $user_id = auth()->id();
        $eval_id = $request->get('eval_id');
        $attr_id = $request->get('attr_id');
        if ($comment->employee_id != $user_id || !$comment->delete()) {
            return response()->json(trans('project::me.An error occurred'), 422);
        }
        MeHistory::where('action_type', MeHistory::AC_COMMENT)
                ->where('type_id', $id)
                ->where('employee_id', $user_id)
                ->delete();
        if ($eval_id) {
            $eval_item = MeEvaluation::find($eval_id);
            if ($eval_item) {
                $results['change_status'] = [
                    'feedback' => $eval_item->canChangeStatus(MeEvaluation::STT_FEEDBACK),
                    'approved' => $eval_item->canChangeStatus(MeEvaluation::STT_APPROVED),
                    'closed' => $eval_item->canChangeStatus(MeEvaluation::STT_CLOSED)
                ];
                $results['td_class'] = $eval_item->hasComments($attr_id);
            }
        }
        return $results;
    }

    /**
     * load comments by attribute id
     * @param Request $request
     * @return type
     */
    public function loadAttrComments(Request $request) {
        $valid = Validator::make($request->all(), [
            'eval_id' => 'required',
            'attr_id' => 'required_without:comment_type'
        ]);
        if ($valid->fails()) {
            return response()->json(trans('project::me.No result'), 422);
        }
        $evalId = $request->get('eval_id');
        $attrId = $request->get('attr_id');
        $comments = MeComment::getByEvalAttr($evalId, $attrId);

        $result = [
            'comment_html' => '',
            'next_page_url' => $comments->nextPageUrl(),
            'current_user_comment' => $attrId ? MeComment::isUserComment($evalId, $attrId) : 0
        ];
        if (!$comments->isEmpty()) {
            foreach ($comments as $comment) {
                $result['comment_html'] .= view('project::me.template.comment_item', compact('comment'))->render();
            }
        }
        return $result;
    }

    /**
     * update status and assignee evaluation
     * @param Request $request
     * @return type
     */
    public function update(Request $request) {
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
                $first_eval = MeEvaluation::find(is_array($eval_ids) ? $eval_ids[0] : $eval_ids);
                $project = $first_eval->project;
                $leader = $project->groupLeader;
                $old_assignee = $first_eval->assignee;
                if (!$leader) {
                    throw new \Exception(trans('project::me.Project not have group directory'));
                }

                $attrs = [
                    'status' => MeEvaluation::STT_SUBMITED,
                    'assignee' => $leader->id
                ];

                $current_user = Permission::getInstance()->getEmployee();
                foreach ($eval_ids as $id) {
                    $eval_item = MeEvaluation::find($id);
                    if (!$eval_item) {
                        throw new \Exception(trans('project::me.No data'));
                    }
                    if ($eval_item->status == MeEvaluation::STT_FEEDBACK) {
                        $checkCommentOrChange = MeHistory::checkUserAction($id, [MeHistory::AC_COMMENT, MeHistory::AC_NOTE, MeHistory::AC_CHANGE_POINT], $current_user->id);
                        if (!$checkCommentOrChange) {
                            throw new \Exception(trans('project::me.You must comment or change point to submit'));
                        }
                    }
                    if (!in_array($eval_item->status, [MeEvaluation::STT_APPROVED, MeEvaluation::STT_CLOSED, MeEvaluation::STT_SUBMITED])) {
                        $eval_item->status = $attrs['status'];
                        $eval_item->increment('version');
                        if ($current_user->id == $leader->id) { // if PM is Leader
                            $eval_item->status = MeEvaluation::STT_APPROVED;
                            if ($eval_item->employee_id == $current_user->id) { // if PM is Leader and staff
                                $eval_item->status = MeEvaluation::STT_CLOSED;
                            } else {
                                //send email to staft
                                $stEmp = Employee::find($eval_item->employee_id, ['id', 'name', 'email']);
                                if ($stEmp) {
                                    $data['st_name'] = $stEmp->name;
                                    $data['accept_link'] = route('me::profile.confirm', ['project_id' => $project->id, 'time' => $eval_item->eval_time->format('Y-m')]);
                                    $data['pm_name'] = $current_user->name;
                                    $data['project_name'] = $project->name;
                                    $data['time'] = $eval_item->eval_time;

                                    $st_email = $stEmp->email;
                                    $contentDetail = RkNotify::renderSections('project::me.mail.gl-accept', $data);
                                    $emailQueue = new EmailQueue();
                                    $emailQueue->setFrom('rikkeisoft@gmail.com', config('mail.name'))
                                            ->setTo($st_email)
                                            ->setSubject(trans('project::me.Rikkei Monthly Evaluation'))
                                            ->setTemplate('project::me.mail.gl-accept', $data)
                                            ->setNotify(
                                                $stEmp->id,
                                                trans('project::me.Rikkei Monthly Evaluation') . ' ' . $data['pm_name'] . ' created on project "'. $project->name .'"',
                                                $data['accept_link'], ['category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => $contentDetail]
                                            )
                                            ->save();
                                }
                            }
                            //create approved history
                            MeHistory::create([
                                'eval_id' => $id,
                                'employee_id' => $current_user->id,
                                'version' => $eval_item->version,
                                'action_type' => MeHistory::AC_APPROVED,
                                'type_id' => $eval_item->employee_id
                            ]);
                        }
                        //create submit history
                        MeHistory::create([
                            'eval_id' => $id,
                            'employee_id' => $current_user->id,
                            'version' => $eval_item->version,
                            'action_type' => MeHistory::AC_SUBMIT,
                            'type_id' => $leader->id
                        ]);
                    }
                    $eval_item->assignee = $attrs['assignee'];
                    if ($current_user->id == $leader->id) {
                        $eval_item->assignee = $eval_item->employee_id;
                    }
                    $eval_item->save();
                    $results[$id] = ['status' => $eval_item->status, 'can_change' => $eval_item->canChangePoint()];
                }

                //check send mail submit to leader
                if ($current_user->id != $leader->id && !$has_submited) {
                    $data = [];
                    $leaderEmail = $leader->email;
                    $data['leader_name'] = $leader->name;

                    $data['time'] = $first_eval->eval_time;
                    $data['project_name'] = $project->name;

                    $teams = $project->getTeams();
                    $team_name = '';
                    if (!$teams->isEmpty()) {
                        foreach ($teams as $team) {
                            $team_name .= $team->name.', ';
                        }
                    }
                    $data['team_name'] = trim($team_name, ', ');
                    $data['review_link'] = route('me::review.list', ['project_id' => $project->id, 'time' => $first_eval->eval_time->format('Y-m')]);
                    $data['pm_name'] = $current_user->name;
                    $data['has_feedback'] = $has_feedback;

                    $contentDetail = RkNotify::renderSections('project::me.mail.pm-submit', $data);
                    $mailQueue = new EmailQueue();
                    $mailQueue->setTo($leaderEmail)
                            ->setSubject(trans('project::me.Rikkei Monthly Evaluation'))
                            ->setTemplate('project::me.mail.pm-submit', $data)
                            ->setNotify(
                                $leader->id,
                                trans('project::me.Rikkei Monthly Evaluation') . ' ' . $data['pm_name'] . ' submited on project "'. $project->name .'"',
                                $data['review_link'], ['category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => $contentDetail]
                            )
                            ->save();
                }

                if ($attrs['assignee'] != $old_assignee) {
                    MeHistory::delByLeader($old_assignee, $project->id);
                }

                DB::commit();
                return response()->json(['message' => trans('project::me.Submited successful'), 'status' => $attrs['status'], 'results' => $results]);
            } catch (\Exception $ex) {
                DB::rollback();
                return response()->json($ex->getMessage(), 422);
            }
        }
    }

    /**
     * list item by leader
     * @return type
     */
    public function listByLeader(Request $request)
    {
        $evalTable = MeEvaluation::getTableName();
        $dataFilter = $request->all();

        $urlFilter = $request->url() . '/';
        $keyView = auth()->id() .'_view_' . $urlFilter;
        $firstView = CacheHelper::get($keyView);
        if (!$firstView) {
            CacheHelper::put($keyView, 1, null, true, 24 * 60 * 60); //store one day
            if (!Form::getFilterData('excerpt', 'month')) {
                $defaultMonth = MeView::defaultFilterMonth();
                $dataFilter = array_merge(['time' => $defaultMonth->format('m-Y')], $dataFilter);
                Form::setFilterData('excerpt', $defaultMonth->format('m-Y'), 'month', $urlFilter);
            }
        }

        $filterMonth = (isset($dataFilter['time']) && $dataFilter['time']) ?
                $dataFilter['time'] : Form::getFilterData('excerpt', 'month');
        $filterProjectId = (isset($dataFilter['project_id']) && $dataFilter['project_id']) ?
                $dataFilter['project_id'] : Form::getFilterData('excerpt', $evalTable.'.project_id');
        $filterEmployee = Form::getFilterData('number', $evalTable.'.employee_id');
        //get filter employee account
        $filterEmployeeName = MeEvaluation::findEmployeeName($filterEmployee);
        //get filter project name;
        $filterProjectName = MeEvaluation::findProjectOrTeamName($filterProjectId);

        $dataFilter['month'] = $filterMonth;
        $dataFilter['project_id'] = $filterProjectId;
        $dataFilter['employee_id'] = $filterEmployee;
        //save temp dataFilter
        Session::put(MeView::KEY_REVIEW_FILTER, $dataFilter);

        $normalAttrs = MeAttribute::getNormalAttrs();
        $performAttrs = MeAttribute::getPerformAttrs();
        $filterTeams = TeamList::toOption(null, false, false);
        //check permiss create ME team
        $hasPermissCreateTeam = Permission::getInstance()->isAllow('project::team.eval.create');

        Breadcrumb::add(trans('project::me.Review'));

        return view(
            'project::me.leader_review',
            compact(
                'normalAttrs',
                'performAttrs',
                'filterMonth',
                'filterEmployee',
                'filterProjectId',
                'filterTeams',
                'hasPermissCreateTeam',
                'filterEmployeeName',
                'filterProjectName'
            )
        );
    }

    /**
     * Leader update status
     * @param type $eval_id
     * @param type $status
     * @return type
     */
    public function leaderUpdate($evalId, Request $request)
    {
        $valid = Validator::make($request->all(), [
            'status' => 'required|numeric|min:'.MeEvaluation::STT_DRAFT.'|max:'.MeEvaluation::STT_CLOSED
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid->errors());
        }
        $status = $request->get('status');
        $scope = Permission::getInstance();
        $scopeRoute = 'project::project.eval.leader_update';
        $currentUser = $scope->getEmployee();
        if ($scope->isScopeCompany(null, $scopeRoute)) {
            return $this->updateStatus($evalId, $status, $currentUser->id, $scope);
        }
        if ($scope->isScopeTeam(null, $scopeRoute)) {
            //me team
            $teamIds = TeamMember::where('employee_id', $currentUser->id)
                    ->lists('team_id')->toArray();

            $hasEval = MeEvaluation::where(function ($query) use ($currentUser, $teamIds) {
                        $query->whereIn('project_id', function ($subQuery) use ($currentUser, $teamIds) {
                            $projTeamTbl = TeamProject::getTableName();
                            $subQuery->select($projTeamTbl.'.project_id')
                                ->from($projTeamTbl)
                                ->join(Project::getTableName() . ' as proj', $projTeamTbl . '.project_id', '=', 'proj.id')
                                ->whereIn('team_id', $teamIds)
                                ->orWhere('proj.leader_id', $currentUser->id)
                                ->groupBy($projTeamTbl . '.project_id');
                        })
                        ->orWhereIn('team_id', $teamIds);
                    })
                    ->whereIn('status', [MeEvaluation::STT_SUBMITED, MeEvaluation::STT_CLOSED])
                    ->where('id', $evalId)
                    ->first();

            if (!$hasEval) {
                return View::viewErrorPermission();
            }
            return $this->updateStatus($evalId, $status, $currentUser->id, $scope);
        }
        if ($scope->isScopeSelf(null, $scopeRoute)) {
            $hasEval = MeEvaluation::where('assignee', $currentUser->id)
                    ->where('id', $evalId)
                    ->first();
            if (!$hasEval) {
                return View::viewErrorPermission();
            }
            return $this->updateStatus($evalId, $status, $currentUser->id, $scope);
        }
    }

    /**
     * staff update status
     * @param type $eval_id
     * @param type $status
     * @return type
     */
    public function staffUpdate($eval_id, Request $request) {
        $valid = Validator::make($request->all(), [
            'status' => 'required|numeric|min:'.MeEvaluation::STT_DRAFT.'|max:'.MeEvaluation::STT_CLOSED
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withErrors($valid->errors());
        }
        $status = $request->get('status');
        $scope = Permission::getInstance();
        $eval_item = MeEvaluation::find($eval_id, ['assignee', 'project_id', 'team_id']);
        if (!$eval_item) {
            return redirect()->back()->withErrors(trans('project::me.No data'));
        }
        return $this->updateStatus($eval_id, $status, false, $scope);
    }

    /**
     * update status
     * @param type $eval_id
     * @param type $status
     * @return type
     */
    public function updateStatus($evalId, $status, $isLeader = false, $scope = null)
    {
        if (!$scope) {
            $scope = Permission::getInstance();
        }
        //check request ajax
        $isReturnJson = request()->ajax() || request()->wantsJson();
        $currentUser = $scope->getEmployee();
        $evalItem = MeEvaluation::find($evalId);
        if ($status == MeEvaluation::STT_FEEDBACK) {
            if (!$evalItem->canChangeStatus($status)) {
                $error = trans('project::me.You must add a comment before feedback');
                if ($isReturnJson) {
                    return response()->json([
                        'success' => 0,
                        'message' => $error
                    ]);
                }
                return redirect()->back()->with('messages', ['errors' => [$error]]);
            }
        }
        //bỏ ràng buộc comment xong không cho accept or approve
        /*if ($status == MeEvaluation::STT_APPROVED || $status == MeEvaluation::STT_CLOSED) {
            $stt_label = trans('project::me.Accept');
            if ($status == MeEvaluation::STT_CLOSED) {
                $stt_label = trans('project::me.OK');
            }
            $error = trans('project::me.You can not do this action after comment', ['action' => $stt_label]);
            if (!$evalItem->canChangeStatus($status)) {
                if ($isReturnJson) {
                    return response()->json([
                        'success' => 0,
                        'message' => $error
                    ]);
                }
                return redirect()->back()->with('messages', ['errors' => [$error]]);
            }
        }*/

        DB::beginTransaction();
        try {
            $evalUpdate = MeEvaluation::updateStatus($evalId, $status, $isLeader, $scope->isAllow('project::me.coo_edit_point'));
            if (!$evalUpdate){
                if ($isReturnJson) {
                    return response()->json([
                        'success' => 0,
                        'message' => trans('project::me.No data')
                    ]);
                }
                return redirect()->back()->withErrors(trans('project::me.No data'));
            }
            if ($evalUpdate->team_id) {
                $team = $evalUpdate->team;
                $leaderInfo = $evalUpdate->creator;
                if (!$leaderInfo) {
                    if ($isReturnJson) {
                        return response()->json([
                            'success' => 0,
                            'message' => trans('project::me.No leader')
                        ]);
                    }
                    return redirect()->back()->with('messages', ['errors' => [trans('project::me.No leader')]]);
                }
                $currentUserType = MeComment::ST_TYPE;
                if ($currentUser->id == $leaderInfo->id) {
                    $currentUserType = MeComment::GL_TYPE;
                }
                $pm = $leaderInfo;
                $pmId = $leaderInfo->id;
            } else {
                $currentUserType =  MeComment::getCurrentUserInProjectType($evalItem->project_id, $currentUser);
                $pmId = $evalUpdate->manager_id;
                $pm = Employee::find($pmId);
                if (!$pm) {
                    $pm = new Employee();
                }
            }

            $data = [];
            $data['pm_name'] = $pm->name;
            $project = $evalUpdate->project;
            $data['project_name'] = $project ? $project->name : null;
            $teams = $project ? $project->getTeams() : null;
            $team_name = '';
            if ($teams && !$teams->isEmpty()) {
                foreach ($teams as $team) {
                    $team_name .= $team->name.', ';
                }
            }
            $data['team_name'] = trim($team_name, ', ');
            $data['time'] = $evalUpdate->eval_time;

            if ($evalUpdate->status == MeEvaluation::STT_FEEDBACK) {
                $evalUpdate->incrementVersion();
                if ($evalUpdate->team_id) {
                    $data['project_name'] = 'Team ' . $evalUpdate->team->name;
                    $data['feedback_link'] = route('me::team.edit', ['team_id' => $evalUpdate->team_id, 'month' => $evalUpdate->eval_time->format('Y-m')]);
                } else {
                    $data['feedback_link'] = route('me::proj.edit', ['project_id' => $project->id, 'month' => $evalUpdate->eval_time->format('Y-m')]);
                }

                if ($currentUserType == MeComment::ST_TYPE || ($currentUserType == MeComment::PM_TYPE && $currentUser->id != $pm->id)) {
                    $data['st_name'] = $currentUser->name;

                    $pm_email = $pm->email;
                    $contentDetail = RkNotify::renderSections('project::me.mail.st-feedback', $data);
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($pm_email)
                            ->setSubject(trans('project::me.Rikkei Monthly Evaluation'))
                            ->setTemplate('project::me.mail.st-feedback', $data)
                            ->setNotify(
                                $pm->id,
                                trans('project::me.Rikkei Monthly Evaluation') . ' ' . $data['st_name'] . ' feedbacked on ' . $data['project_name'],
                                $data['feedback_link'], ['category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => $contentDetail]
                            )
                            ->save();
                } elseif (in_array($currentUserType, [MeComment::GL_TYPE, MeComment::COO_TYPE])
                        && !$evalUpdate->team_id) {
                    $data['st_name'] = $evalUpdate->employee->name;
                    $data['leader_name'] = $currentUser->name;

                    $pm_email = $pm->email;
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($pm_email)
                            ->setSubject(trans('project::me.Rikkei Monthly Evaluation'))
                            ->setTemplate('project::me.mail.gl-feedback', $data)
                            ->setNotify(
                                $pm->id,
                                trans('project::me.Rikkei Monthly Evaluation') . ' ' . $data['leader_name'] . ' feedbacked on ' . $data['project_name'],
                                $data['feedback_link'], ['category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => RkNotify::renderSections('project::me.mail.gl-feedback', $data)]
                            )
                            ->save();
                } else {
                    //none
                }

            } elseif ($status == MeEvaluation::STT_APPROVED) {
                $evalUpdate->incrementVersion();
                MeHistory::create([
                    'eval_id' => $evalUpdate->id,
                    'employee_id' => $currentUser->id,
                    'version' => $evalUpdate->version,
                    'action_type' => MeHistory::AC_APPROVED,
                    'type_id' => $evalUpdate->employee_id
                ]);

                $stEmp = Employee::find($evalUpdate->employee_id);
                if ($stEmp) {
                    $data['approved_time'] = \Carbon\Carbon::now()->toDateString();
                    $data['st_name'] = $stEmp->name;
                    $data['accept_link'] = route('me::profile.confirm', ['project_id' => $project->id, 'time' => $evalUpdate->eval_time->format('Y-m')]);

                    $stEmail = $stEmp->email;
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($stEmail)
                            ->setSubject(trans('project::me.Rikkei Monthly Evaluation'))
                            ->setTemplate('project::me.mail.gl-accept', $data)
                            ->setNotify(
                                $stEmp->id,
                                trans('project::me.Rikkei Monthly Evaluation') . ' ' . $data['pm_name'] . ' approved on ' . $data['project_name'],
                                $data['accept_link'],
                                ['actor_id' => $pm->id, 'category_id' => RkNotify::CATEGORY_PERIODIC, 'content_detail' => RkNotify::renderSections('project::me.mail.gl-accept', $data)]
                            )
                            ->save();
                }
            } else {
                //none
            }

            DB::commit();
            $message = trans('project::me.Updated successful');
            if ($isReturnJson) {
                return response()->json([
                    'success' => 1,
                    'message' => $message,
                    'status_label' => $evalUpdate->getStatusLabelAttribute()
                ]);
            }
            return redirect()->back()->with('messages', ['success' => [$message]]);
        } catch (\Exception $ex) {
            DB::rollback();
            $error = trans('project::me.Save error, please try again laster');
            if ($isReturnJson) {
                return response()->json([
                    'success' => 0,
                    'message' => $error
                ]);
            }
            return redirect()->back()->with('messages', ['errors' => [$error]]);
        }
    }

    /**
     * list by staft
     * @return type
     */
    public function listByStaft(Request $request) {
        $evalTbl = MeEvaluation::getTableName();
        $collectStaft = MeEvaluation::collectByStaft(null, $request->all());
        $collection = MeEvaluation::getByStaft(null, $request->all());
        $collectionModel = $collection['collection'];
        $normalAttrs = MeAttribute::getNormalAttrs();
        $performAttrs = MeAttribute::getPerformAttrs();

        $filter_projects = clone $collectStaft;
        $filter_projects = $filter_projects->groupBy('proj.id')
                ->select('proj.name', 'proj.id as project_id', 'proj.created_at')
                ->orderBy('proj.created_at', 'desc')
                ->get();

        $filterMonths = clone $collectStaft;
        $filterMonths = $filterMonths->groupBy($evalTbl.'.eval_time')
                ->select($evalTbl.'.eval_time', DB::raw('DATE_FORMAT('.$evalTbl.'.eval_time, "%Y-%m") as eval_month'))
                ->orderBy($evalTbl.'.eval_time', 'desc')
                ->get();
        $filter_month = $collection['filter_month'];
        $listRangeMonths = MeView::listRangeBaselineDate(array_keys($filterMonths->lists('id', 'eval_month')->toArray()));
        Breadcrumb::add(trans('project::me.Evaluation'));
        return view('project::me.staft_confirm', compact('collectionModel', 'normalAttrs', 'performAttrs', 'filter_projects', 'filterMonths', 'filter_month', 'collection', 'listRangeMonths'));
    }

    /*
     * multi action me items
     */
    public function multiActions(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'eval_ids' => 'required',
            'action' => 'required'
        ]);
        if ($valid->fails()) {
            return response()->json(['message' => trans('project::me.No data')], 422);
        }

        $eval_ids = $request->get('eval_ids');
        $action = $request->get('action');
        $eval_items = MeEvaluation::whereIn('id', $eval_ids)->get();
        if ($eval_items->isEmpty()) {
            return response()->json(['message' => trans('project::me.No data')], 422);
        }

        $current_user = Permission::getInstance()->getEmployee();
        DB::beginTransaction();
        try {
            switch ($action) {
                case MeEvaluation::STT_FEEDBACK:
                    $projects = [];
                    foreach ($eval_items as $item) {
                        if (!$item->canChangeStatus(MeEvaluation::STT_FEEDBACK)) {
                            return response()->json([
                                'message' => trans('project::me.You must add a comment before feedback'),
                                'eval_id' => $item->id
                            ], 422);
                        }
                        $projects[$item->project_id.'_'.$item->eval_time->format('m_Y')][] =  $item;
                    }

                    foreach ($projects as $items) {
                        $firstItem = $items[0];
                        $pmId = $firstItem->manager_id;
                        $pm = Employee::find($pmId);
                        $project = $firstItem->project;
                        $teams = $project ? $project->getTeams() : null;
                        $team_name = '';
                        if ($teams && !$teams->isEmpty()) {
                            foreach ($teams as $team) {
                                $team_name .= $team->name.', ';
                            }
                        }

                        $st_name = [];
                        foreach ($items as $item) {
                            array_push($st_name, $item->employee->name);
                            $item->status = MeEvaluation::STT_FEEDBACK;
                            $item->assignee = $pmId;
                            $item->last_user_updated = $current_user->id;
                            $item->is_leader_updated = MeEvaluation::LEADER_UPDATED;
                            $item->save();
                            $item->incrementVersion();
                        }
                        if (count(MeEvaluation::getDevsOfProject($firstItem->project_id, $firstItem->eval_time)) == count($items)) {
                            $st_name = null;
                        }
                        if ($pm) {
                            $itemTeam = $firstItem->team;
                            $data = [
                                'pm_name' => $pm->name,
                                'project_name' => $project ? $project->name : ($itemTeam ? 'Team: ' . $itemTeam->name : null),
                                'team_name' => trim($team_name, ', '),
                                'time' => $firstItem->eval_time,
                                'feedback_link' => $project ? route('me::proj.edit', [
                                    'project_id' => $project->id,
                                    'month' => $firstItem->eval_time->format('Y-m')
                                ]) : route('me::team.edit', [
                                    'team_id' => $firstItem->team_id,
                                    'month' => $firstItem->eval_time->format('Y-m')
                                ]),
                                'leader_name' => $current_user->name,
                                'st_name' => $st_name
                            ];

                            $pm_email = $pm->email;
                            $emailQueue = new EmailQueue();
                            $emailQueue->setFrom('rikkeisoft@gmail.com', config('mail.name'))
                                    ->setTo($pm_email)
                                    ->setSubject(trans('project::me.Rikkei Monthly Evaluation'))
                                    ->setTemplate('project::me.mail.gl-feedback', $data)
                                    ->setNotify(
                                        $pm->id,
                                        trans('project::me.Rikkei Monthly Evaluation') . ' ' . $data['leader_name'] . ' feedbacked on ' . $data['project_name'],
                                        $data['feedback_link'], ['category_id' => RkNotify::CATEGORY_PERIODIC,
                                        'content_detail' => RkNotify::renderSections('project::me.mail.gl-feedback', $data)]
                                    )
                                    ->save();
                        }
                    }

                    break;
                case MeEvaluation::STT_APPROVED:
                    foreach ($eval_items as $item) {
                        /*if (!$item->canChangeStatus(MeEvaluation::STT_APPROVED)) {
                            $stt_label = trans('project::me.Accept');
                            return response()->json([
                                    'message' => trans('project::me.You can not do this action after comment', ['action' => $stt_label]),
                                    'eval_id' => $item->id
                                ], 422);
                        }*/
                        $item->status = MeEvaluation::STT_APPROVED;
                        if (MeEvaluation::isPMOfProject($item->employee_id, $item->project_id) || $current_user->id == $item->employee_id) {
                            $item->status = MeEvaluation::STT_CLOSED;
                        }
                        $item->assignee = $item->employee_id;
                        $item->save();
                        $item->incrementVersion();

                        MeHistory::create([
                            'eval_id' => $item->id,
                            'employee_id' => $current_user->id,
                            'version' => $item->version,
                            'action_type' => MeHistory::AC_APPROVED,
                            'type_id' => $item->employee_id
                        ]);

                        $pmId = $item->manager_id;
                        $pm = Employee::find($pmId);
                        $project = $item->project;
                        $teams = $project ? $project->getTeams() : null;
                        $team_name = '';
                        if ($teams && !$teams->isEmpty()) {
                            foreach ($teams as $team) {
                                $team_name .= $team->name.', ';
                            }
                        }

                        $st_emp = Employee::find($item->employee_id);
                        if ($st_emp && $pm) {
                            $itemTeam = $item->team;
                            $data = [
                                'pm_name' => $pm->name,
                                'project_name' => $project ? $project->name : ($itemTeam ? 'Team: ' . $itemTeam->name : null),
                                'team_name' => trim($team_name, ', '),
                                'time' => $item->eval_time,
                                'accept_link' => route('me::profile.confirm', [
                                    $project ? 'project_id' : 'team_id' => $project ? $project->id : $item->team_id,
                                    'time' => $item->eval_time->format('Y-m')
                                ]),
                                'st_name' => $st_emp->name
                            ];

                            $st_email = $st_emp->email;
                            $emailQueue = new EmailQueue();
                            $emailQueue->setFrom('rikkeisoft@gmail.com', config('mail.name'))
                                    ->setTo($st_email)
                                    ->setSubject(trans('project::me.Rikkei Monthly Evaluation'))
                                    ->setTemplate('project::me.mail.gl-accept', $data)
                                    ->setNotify(
                                        $item->employee_id,
                                        trans('project::me.Rikkei Monthly Evaluation') . ' ' . $data['pm_name'] . ' approved on ' . $data['project_name'],
                                        $data['accept_link'],
                                        ['actor_id' => $pm->id, 'category_id' => RkNotify::CATEGORY_PERIODIC]
                                    )
                                    ->save();
                        }
                    }

                    break;
            }

            DB::commit();
            return response()->json(trans('project::me.Updated successful'));
        } catch (\Exception $ex) {
            DB::rollback();
            return response()->json(['message' => trans('project::me.Save error, please try again laster')], 422);
        }
    }

    public function help() {
        Breadcrumb::add(trans('project::me.Help'));
        return view('project::me.help');
    }

    /**
     * leader view member of team
     * @param array
     * @return view
     */
    public function leaderViewMemberOfTeam(Request $request)
    {
        $evalTbl = MeEvaluation::getTableName();
        $data = $request->all();
        $projectId = null;
        if (!isset($data['project_id'])) {
            $projectId = Form::getFilterData('excerpt', $evalTbl.'.project_id');
            if ($projectId) {
                $data['project_id'] = $projectId;
            }
        }
        $collectionModel = MeEvaluation::leaderViewMemberOfTeam($data);
        $normalAttrs = MeAttribute::getNormalAttrs();
        $performAttrs = MeAttribute::getPerformAttrs();
        $filterTeams = TeamList::toOption(null, false, false);
        //filter
        if ($projectId && is_numeric($projectId)) {
            $allTeamOfProject = Project::getAllTeamOfProject($projectId);
            $allTeamName = Team::getAllTeam();
            $teamName = Lang::get('project::me.Team') . ': ' . ViewProject::getLabelTeamOfProject($allTeamName, $allTeamOfProject);
        } else {
            $teamName = '';
        }
        $projectOrTeamName = null;
        if ($projectId) {
            $projectOrTeamName = MeEvaluation::findProjectOrTeamName($projectId);
        }
        Breadcrumb::add(trans('project::me.View member of team'));
        $isScopeCompany = Permission::getInstance()->isScopeCompany(null, 'project::project.eval.leader_view_of_team');
        return view(
            'project::me.leader_view_member_of_team',
            compact('collectionModel', 'normalAttrs', 'performAttrs', 'filterTeams', 'teamName', 'isScopeCompany', 'projectOrTeamName')
        );
    }

    /**
     * view evaluated
     * @return type
     */
    public function viewEvaluated(Request $request) {
        $data = $request->all();
        Breadcrumb::add(trans('project::me.Evaluated'));
        $results = MeEvaluated::collectEvaluated(null, $data);
        $results['filterTeams'] = MeEvaluation::getInstance()->getTeamPermissOptions('project::me.view.evaluated');
        $projsNotEval = MeEvaluation::getProjectNotEval(null, 'project::me.view.evaluated');
        $results['projsNotEval'] = $projsNotEval;
        $results['totalMember'] = MeEvaluation::getTotalMemberOfLeader(null, 'project::me.view.evaluated');
        return view('project::me.view_evaluated', $results);
    }

    /**
     * view not evaluate
     * @return type
     */
    public function notEvaluate(Request $request) {
        $data = $request->all();
        Breadcrumb::add(trans('project::me.Not.Evaluate'));
        $results = MeEvaluated::collectNotEvaluate($data);
        $results['filterTeams'] = MeEvaluation::getInstance()->getTeamPermissOptions('project::me.view.not_evaluate');
        return view('project::me.not_evaluate', $results);
    }

    /**
     * delete item
     */
    public function delete($id, Request $request) {
        $item = MeEvaluation::find($id);
        $isReturnJson = $request->ajax() || $request->wantsJson();
        if (!$item) {
            if ($isReturnJson) {
                return response()->json([
                    'success' => 0,
                    'message' => trans('project::me.No data')
                ]);
            }
            abort(404);
        }
        $item->delete();
        if ($isReturnJson) {
            return response()->json([
                'success' => 1,
                'message' => trans('project::me.Delete success')
            ]);
        }
        return redirect()->back()->with('messages', ['success' => [trans('project::me.Delete success')]]);
    }

    /**
     * config data
     * @return type
     */
    public function configData () {
        Breadcrumb::add(trans('project::me.Config data'));
        $configRewards = CoreConfigData::getValueDb('me.config.reward');
        $configNewRewards = CoreConfigData::getValueDb('me.new.config.reward');
        $configNew2Rewards = CoreConfigData::getValueDb('me.new2.config.reward');
        $configRewardsOnsite = CoreConfigData::getValueDb('me.config.reward_onsite');

        $contributes = [
            MeEvaluation::TH_EXCELLENT . '' => trans('project::me.Excellent'),
            MeEvaluation::TH_GOOD . '' => trans('project::me.Good'),
            MeEvaluation::TH_FAIR . '' => trans('project::me.Fair'),
            MeEvaluation::TH_SATIS . '' => trans('project::me.Satisfactory'),
            MeEvaluation::TH_UNSATIS . '' => trans('project::me.Unsatisfactory')
        ];
        $newContributes = MeView::getInstance()->arrayContriValLabels(true);
        return view('project::me.config-data', compact('configRewards', 'configNewRewards', 'configNew2Rewards', 'contributes', 'newContributes', 'configRewardsOnsite'));
    }

    /**
     * save config data
     * @param Request $request
     * @return type
     */
    public function saveConfig(Request $request)
    {
        $item = $request->get('item');
        $response = [];
        if (!$item) {
            $response['success'] = 1;
            $response['message'] = Lang::get('core::message.Save success');
            return response()->json($response);
        }
        foreach ($item as $key => $value) {
            $item = CoreConfigData::getItem($key);
            if (!$item) {
                $item = new CoreConfigData();
                $item->key = $key;
            }
            if (is_array($value)) {
                $value = serialize($value);
            }
            $item->value = $value;
            $item->save();
        }
        $response['success'] = 1;
        $response['message'] = Lang::get('core::message.Save success');
        //check if need forget cache
        if ($keyCache = $request->get('key_config_cache')) {
            CacheHelper::forget($keyCache);
        }
        return response()->json($response);
    }

    /*
     * search ajax project name or team name
     */
    public function searchProjectOrTeam(Request $request)
    {
        return response()->json(
            MeEvaluation::searchProjectOrTeamAjax($request->get('q'), [
                'page' => $request->get('page')
            ])
        );
    }

    public function reviewStatistic()
    {
        return MeEvaluation::reviewStatistic();
    }
}
