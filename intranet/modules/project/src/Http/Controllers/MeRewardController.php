<?php

namespace Rikkei\Project\Http\Controllers;

/**
 * Description of MeRewardController
 *
 * @author lamnv
 */

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Rikkei\Project\Http\Requests\CreateOsdcImportExcel;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View as CoreView;
use Rikkei\ManageTime\Model\BusinessTripEmployee;
use Rikkei\ManageTime\Model\BusinessTripRegister;
use Rikkei\Me\Model\Comment as MeComment;
use Rikkei\Me\Model\ME as MeEvaluation;
use Rikkei\Me\View\View as MeView;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\Model\MeReward;
use Rikkei\Project\Model\Project;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Province;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\Permission;
use Exception;
class MeRewardController extends Controller {
    const ACCESS_FOLDER = '0777';
    const FOLDER_LOG = 'mereward_import_log';

    /**
     * construct
     */
    public function _construct() {
        Breadcrumb::add(trans('project::me.Monthly Evaluation'), route('project::project.eval.list_by_leader'));
        Breadcrumb::add(trans('project::me.OSDC Reward'));
        Menu::setActive('team');
//        app()->setLocale('vi');
    }

    /**
     * create view
     * @return type
     */
    public function showEdit($scopeRoute = 'project::me.reward.edit', $data = [])
    {
        $scope = Permission::getInstance();
        $teamList = MeEvaluation::getInstance()->getTeamPermissOptions($scopeRoute);
        $isPQATeam = false;
        $this->preFilterData($scopeRoute, $teamList, $data, $isPQATeam);
        $filterMonth = $data['filter_month'];
        $defaultTeamId = $data['default_team_id'];

        //get const
        $projectTypeLabels = Project::labelChartTypeProject();
        $statuses = MeEvaluation::filterStatus();
        if ($filterMonth && $filterMonth != MeEvaluation::VAL_ALL
                && $filterMonth->format('Y-m') > config('project.me_sep_month')) {
            $contributes = MeView::getInstance()->listContributeLabels();
        } else {
            $contributes = MeView::getInstance()->listOldContributeLabels();
        }
        $rewardStatuses = MeReward::listStatuses();
        //get data;
        $collectionModel = MeReward::getMERewardData($scopeRoute, $data);
        //permission edit
        $permissEditApprove = $scope->isAllow('project::me.reward.approve');
        $permissEditSubmit = $scope->isAllow('project::me.reward.submit');
        $isReview = isset($data['is_review']) ? $data['is_review'] : false;
        //filter project
        $filterProjectCode = '';
        if (isset($data['project_id'])) {
            $filterProject = Project::find($data['project_id'], ['name']);
            if ($filterProject) {
                $filterProjectCode = $filterProject->name;
            }
        }
        //filter project type
        $filterProjType = isset($data['project_type']) ? $data['project_type'] : null;
        $listRangeMonths = MeView::listRangeBaselineDate(array_keys($collectionModel->pluck('id', 'eval_month')->toArray()));
        $commentClasses = MeComment::getInstance()->getEvalCommentClass($collectionModel->pluck('id')->toArray(), 'eval');

        $empIds = $collectionModel->pluck('emp_id')->toArray();
        $employeeOnsite = $this->getEmployeesOsite($filterMonth, $empIds);
        $inforEmployeeOnsite = $this->handlingEmployeeOnsite($employeeOnsite);

        return view('project::me.reward', compact('collectionModel', 'teamList',
                'statuses', 'contributes', 'projectTypeLabels', 'rewardStatuses', 'listRangeMonths',
                'permissEditApprove', 'permissEditSubmit', 'isReview', 'filterProjectCode', 'commentClasses',
                'defaultTeamId', 'createReward', 'filterProjType', 'filterMonth', 'inforEmployeeOnsite'));
    }

    /**
     * prefilter request data
     * @param string $scopeRoute route permission
     * @param collection $teamList list team has permisison
     * @param bool $isPQATeam
     * @param array $data request data
     * @param boolean $refreshFiter
     * @return mixed integer|null
     */
    public function preFilterData(
        $scopeRoute,
        $teamList,
        &$data,
        &$isPQATeam,
        $urlFilter = null,
        $refreshFiter = true
    )
    {
        $scope = Permission::getInstance();
        $defaultTeamId = null;
        if ($scope->isScopeCompany(null, $scopeRoute)) {
            if (!isset($data['project_id'])) {
                $teamLeadIds = $scope->getEmployee()->getTeamIdIsLeader();
                if ($teamLeadIds) {
                    $defaultTeamId = $teamLeadIds[0];
                }
            }
        } elseif ($teamIds = $scope->isScopeTeam(null, $scopeRoute)) {
            $teamIds = is_array($teamIds) ? $teamIds : [];
            $isPQATeam = in_array(Team::TEAM_TYPE_PQA, Team::whereIn('id', $teamIds)->lists('type')->toArray());
            if ($teamList && !isset($data['project_id'])) {
                $defaultTeamId = $teamList[0]['value'];
            }
        } else {
            //none permission
        }
        $data['default_team_id'] = $defaultTeamId;

        //filter month
        if (isset($data['time']) && $refreshFiter) {
            FormView::forgetFilter($urlFilter);
            $data['project_type'] = null;
            $filterMonth = Carbon::parse($data['time']);
        } else {
            //filter project type
            $projType = FormView::getFilterData('excerpt', 'type', $urlFilter);
            if (!$projType && !$isPQATeam) {
                $projType = null;
            }
            $data['project_type'] = $projType;

            $filterMonth = FormView::getFilterData('excerpt', 'eval_time', $urlFilter);
            if (!$filterMonth) {
                $filterMonth = MeView::defaultFilterMonth();
            }
            if ($filterMonth != MeEvaluation::VAL_ALL) {
                $filterMonth = Carbon::parse($filterMonth);
            }
        }
        $data['filter_month'] = $filterMonth->startOfMonth();
    }

    /*
     * view edit
     */
    public function edit (Request $request) {
        Breadcrumb::add(trans('project::me.Create'));
        $data = $request->all();
        if (isset($data['reward_status'])) {
            if (!is_array($data['reward_status'])) {
                $data['reward_status'] = [$data['reward_status']];
            }
        }

        $data['create_reward'] = true;
        return $this->showEdit(null, $data);
    }

    /**
     * submit reward
     * @param Request $request
     * @return type
     */
    public function submit(Request $request) {
        $dataInput = $request->except(['filter', '_token']);
        $valid = Validator::make($dataInput, [
            'rewards.*.submit' => 'max:255',
            'rewards.*.comment' => 'max:500'
        ], [
            'rewards.*.submit.required' => trans('project::me.Reward number is required'),
            'rewards.*.submit.max' => trans('validation.max', ['attribute' => 'Reward number', 'max' => 255]),
            'rewards.*.comment.max' => trans('validation.max', ['attribute' => 'Comment', 'max' => 500])
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }

        DB::beginTransaction();
        try {
            //get filter data
            $filterTeam = $request->get('filter_team');
            $filterMonth = $request->get('filter_time');
            $filterType = $request->get('filter_type');

            $aryFilterReward = MeView::filterNewReward($request->get('rewards'));
            $dataRewards = $aryFilterReward['exists'];
            //create new ME
            if ($filterMonth && $filterMonth != '_all_') {
                $filterMonth = Carbon::parse($filterMonth)->startOfMonth();
            }
            if ($aryFilterReward['new']) {
                $newMeData = MeReward::createNewME($aryFilterReward['new'], $filterMonth, $filterTeam);
                $dataRewards += $newMeData;
            }

            $evalIds = array_keys($dataRewards);
            $collectRewards = MeReward::collectBySubmited($evalIds);
            if ($collectRewards->isEmpty()) {
                return redirect()->back()->withInput()->with('messages', ['errors' => [trans('project::me.Not found data')]]);
            }

            $currUser = Permission::getInstance()->getEmployee();
            $isSave = $request->get('is_save');

            //check if only save
            if ($isSave) {
                foreach ($collectRewards as $meReward) {
                    $evalId = $meReward->id;
                    $itemReward = $dataRewards[$evalId];
                    $itemReward['eval_id'] = $evalId;
                    MeReward::saveReward($itemReward, null, $filterType);
                }
                DB::commit();
                return redirect()->back()->with('messages', ['success' => [trans('project::me.Saved successful')]]);
            }

            if (!$filterTeam || $filterTeam == '_all_' || !$filterMonth || $filterMonth == '_all_') {
                return redirect()->back()->withInput()->with('messages', ['errors' => [trans('project::me.You must select team and month')]]);
            }
            $team = Team::find($filterTeam);
            if (!$team) {
                return redirect()->back()->with('messages', ['errors' => [trans('project::me.Team not found')]]);
            }

            //collect reward change to send mail
            $dataRewardChange = [];
            $collectLeaders = [];
            $isUpdateStatus = false;
            $isUpdateData = false;
            $dataSubmitChange = [];

            foreach ($collectRewards as $meReward) {
                $evalId = $meReward->id;
                $itemReward = $dataRewards[$evalId];
                $itemReward['eval_id'] = $evalId;
                $rewardSubmit = preg_replace('/\,|\s/', '', $itemReward['submit']);
                $rewardSuggest = isset($itemReward['is_new']) ? $rewardSubmit : preg_replace('/\,|\s/', '', $itemReward['reward_suggest']);
                $rewardSave = MeReward::saveReward($itemReward, MeReward::STT_SUBMIT, $filterType);
                if ($rewardSave['is_submit']) {
                    $isUpdateStatus = true;
                }
                if ($rewardSave['is_update']) {
                    $isUpdateData = true;
                }
                //check item change if resubmit
                if ($rewardSubmit != $rewardSuggest) {
                    $itemChagne = [
                        'account' => $meReward->name . ' ('. ucfirst(strtolower(preg_replace('/@.*/', '', $meReward->email))) .')',
                        'old_reward' => number_format($rewardSuggest, 0, '.', ','),
                        'new_reward' => number_format($rewardSubmit, 0, '.', ','),
                        'proj_name' => $meReward->proj_name
                    ];
                    array_push($dataSubmitChange, $itemChagne);
                }
                //collect leaders
                if ($meReward->leader_id) {
                    if (isset($collectLeaders[$meReward->leader_id])) {
                        $collectLeaders[$meReward->leader_id]['projects'][$meReward->proj_id] = $meReward->proj_name;
                    } else {
                        $collectLeaders[$meReward->leader_id] = [
                            'name' => $meReward->leader_name,
                            'email' => $meReward->leader_email,
                            'projects' => [
                                $meReward->proj_id => $meReward->proj_name
                            ]
                        ];
                    }

                    if ($rewardSubmit != $rewardSuggest) {
                        $dataRewardChange[$meReward->leader_id][] = $itemChagne;
                    }
                }
            }

            //sen mail noti submited to reviewer
            $emailReviewReward = CoreConfigData::getValueDb('project.account_approver_reward');
            if ($emailReviewReward && ($isUpdateStatus || $dataSubmitChange)) {
                $accountReview = Employee::where('email', $emailReviewReward)->first();
                $accountReviewName = null;
                if ($accountReview) {
                    $accountReviewName = $accountReview->name;
                }
                $emailData = [
                    'dear_name' => $accountReviewName,
                    'month_format' => $filterMonth->format('m-Y'),
                    'month' => $filterMonth->format('Y-m-d H:i:s'),
                    'team_name' => $team ? $team->name : null,
                    'team_id' => $filterTeam,
                    'submit_name' => $currUser->name,
                    'is_update' => $isUpdateData,
                    'data_change' => $dataSubmitChange,
                    'detail_link' => route(
                        'project::me.reward.review',
                        [
                            'team_id' => $filterTeam,
                            'reward_status' => MeReward::STT_SUBMIT,
                            'time' => $filterMonth->format('Y-m-d H:i:s')
                        ]
                    )
                ];

                $emailQueue = new EmailQueue();
                $emailQueue->setTo($emailReviewReward)
                        ->addCc($currUser->email, $currUser->name)
                        ->setSubject(trans('project::me.Subject mail reward submited', ['team' => $team ? $team->name : '', 'month' => $filterMonth->format('m-Y')]))
                        ->setTemplate('project::me.mail.reward_submitted', $emailData);
                if ($accountReview) {
                    $emailQueue->setNotify($accountReview->id, null, $emailData['detail_link'], [
                        'category_id' => RkNotify::CATEGORY_PROJECT,
                        'content_detail' => RkNotify::renderSections('project::me.mail.reward_submitted', $emailData)
                    ]);
                }
                $emailQueue->save();
            }

            //send mail noti to leaders
            if ($collectLeaders && $dataRewardChange) {
                foreach ($collectLeaders as $leaderId => $leader) {
                    if ($leaderId != $currUser->id) {
                        $leaderProjs = $leader['projects'];
                        $projNames = '';
                        foreach ($leaderProjs as $name) {
                            $projNames .= $name . ', ';
                        }
                        $projNames = trim($projNames, ', ');

                        $emailLeaderData = [
                            'dear_name' => $leader['name'],
                            'month_format' => $filterMonth->format('m-Y'),
                            'month' => $filterMonth->format('Y-m-d H:i:s'),
                            'proj_name' => $projNames,
                            'data_change' => isset($dataRewardChange[$leaderId]) ? $dataRewardChange[$leaderId] : [],
                            'submit_name' => $currUser->name
                        ];

                        $emailLeader = new EmailQueue();
                        $emailLeader->setTo($leader['email'])
                                ->setSubject(trans('project::me.Subject mail reward change submited', ['project' =>  $projNames, 'month' => $filterMonth->format('m-Y')]))
                                ->setTemplate('project::me.mail.reward_change', $emailLeaderData)
                                ->setNotify($leaderId, null, null, [
                                    'category_id' => RkNotify::CATEGORY_PROJECT,
                                    'content_detail' => RkNotify::renderSections('project::me.mail.reward_change', $emailLeaderData)
                                ])
                                ->save();
                    }
                }
            }

            DB::commit();
            if (Permission::getInstance()->isAllow('project::me.reward.review')) {
                $oldFilter = CookieCore::getRaw('filter.'. route('project::me.reward.edit').'/');
                CookieCore::setRaw('filter.' . route('project::me.reward.review').'/', $oldFilter);

                return redirect()->route('project::me.reward.review')->with('messages', ['success' => [trans('project::me.Updated successful')]]);
            }
            return redirect()->back()->with('messages', ['success' => [trans('project::me.Submited successful')]]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('project::me.Save error, please try again laster')]]);
        }
    }

    /*
     * view review
     */
    public function review (Request $request) {
        Breadcrumb::add(trans('project::me.OSDC Reward Review'));
        $data = [
            'reward_status' => [MeReward::STT_SUBMIT, MeReward::STT_APPROVE],
            'is_review' => true
        ];
        $data = array_merge($data, $request->all());
        if (!is_array($data['reward_status'])) {
            $data['reward_status'] = [$data['reward_status']];
        }

        return $this->showEdit('project::me.reward.approve', $data);
    }

    /**
     * submit approve
     * @param Request $request
     * @return type
     */
    public function approve (Request $request) {
        $dataInput = $request->except(['filter', '_token']);
        $valid = Validator::make($dataInput, [
            'rewards.*.approve' => 'max:255',
            'rewards.*.comment' => 'max:500'
        ], [
            'rewards.*.approve.required' => trans('project::me.Reward number is required'),
            'rewards.*.approve.max' => trans('validation.max', ['attribute' => 'Reward number', 'max' => 255]),
            'rewards.*.comment.max' => trans('validation.max', ['attribute' => 'Comment', 'max' => 500])
        ]);
        if ($valid->fails()) {
            return redirect()->back()->withInput()->withErrors($valid->errors());
        }
        $dataRewards = $request->get('rewards');
        //get filter data
        $teamId = $request->get('filter_team');
        $month = $request->get('filter_time');
        if (!$teamId || $teamId == '_all_' || !$month || $month == '_all_') {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('project::me.You must select team and month')]]);
        }
        //get collect reward
        $evalIds = array_keys($dataRewards);
        $collectRewards = MeReward::collectBySubmited($evalIds);
        if ($collectRewards->isEmpty()) {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('project::me.Not found data')]]);
        }

        $currUser = Permission::getInstance()->getEmployee();
        DB::beginTransaction();
        try {
            $team = Team::find($teamId);
            if (!$team) {
                return redirect()->back()->with('messages', ['errors' => [trans('project::me.Team not found')]]);
            }
            $month = Carbon::parse($month);
            $teamLeader = $team->leaderInfo;

            $dataRewardChange = [];
            $collectLeaders = [];

            $isChangeApprove = false;
            foreach ($collectRewards as $meReward) {
                $evalId = $meReward->id;
                $itemReward = $dataRewards[$evalId];
                $reward = MeReward::find($evalId);
                if (!$reward) {
                    continue;
                }
                if (!isset($itemReward['approve']) || !$itemReward['approve']) {
                    $itemReward['approve'] = 0;
                }
                $rewardApprove = preg_replace('/\,|\s/', '', $itemReward['approve']);
                $rewardSubmit = preg_replace('/\,|\s/', '', $itemReward['reward_submit']);
                if ($reward->approve_histories) {
                    $isChangeApprove = true;
                }
                $reward->approve_histories = $rewardApprove;
                $reward->reward_approve = $rewardApprove;
                $reward->status = MeReward::STT_APPROVE;
                $reward->comment = $itemReward['comment'];
                $reward->save();

                //collect data change leader
                $itemChange = [
                    'account' => $meReward->name . ' ('. ucfirst(strtolower(preg_replace('/@.*/', '', $meReward->email))) .')',
                    'old_reward' => number_format($rewardSubmit, 0, '.', ','),
                    'new_reward' => number_format($rewardApprove, 0, '.', ',')
                ];
                if ($teamLeader && $rewardApprove != $rewardSubmit) {
                    $dataRewardChange[$teamLeader->id][] = $itemChange;
                }

                //collect leaders
                if ($meReward->leader_id && (!$teamLeader || ($teamLeader->id != $meReward->leader_id))) {
                    if (isset($collectLeaders[$meReward->leader_id])) {
                        $collectLeaders[$meReward->leader_id]['projects'][$meReward->proj_id] = $meReward->proj_name;
                    } else {
                        $collectLeaders[$meReward->leader_id] = [
                            'name' => $meReward->leader_name,
                            'email' => $meReward->leader_email,
                            'projects' => [
                                $meReward->proj_id => $meReward->proj_name
                            ]
                        ];
                    }

                    if ($rewardApprove != $rewardSubmit) {
                        $itemChange['proj_name'] = $meReward->proj_name;
                        $dataRewardChange[$meReward->leader_id][] = $itemChange;
                    }
                }
            }

            //sendmail to team leader
            if ($teamLeader && (!$isChangeApprove || isset($dataRewardChange[$teamLeader->id]))) {
                $teamLeaderData = [
                    'dear_name' => $teamLeader->name,
                    'month_format' => $month->format('m-Y'),
                    'month' => $month->format('Y-m-d H:i:s'),
                    'team_name' => $team->name,
                    'team_id' => $teamId,
                    'submit_name' => $currUser->name,
                    'data_change' => isset($dataRewardChange[$teamLeader->id]) ? $dataRewardChange[$teamLeader->id] : [],
                    'is_change' => $isChangeApprove,
                    'detail_link' => route('project::me.reward.edit', [
                        'team_id' => $teamId,
                        'reward_status' => MeReward::STT_APPROVE,
                        'time' => $month->format('Y-m-d H:i:s')
                    ])
                ];
                $emailTeamLeader = new EmailQueue();
                $emailTeamLeader->setTo($teamLeader->email)
                        ->setSubject(trans('project::me.Subject mail reward approve', ['team' => $team->name, 'month' => $month->format('m-Y')]))
                        ->setTemplate('project::me.mail.reward_approved', $teamLeaderData)
                        ->setNotify($teamLeader->id, null, $teamLeaderData['detail_link'], [
                            'category_id' => RkNotify::CATEGORY_PROJECT,
                            'content_detail' => RkNotify::renderSections('project::me.mail.reward_approved', $teamLeaderData)
                        ])
                        ->save();
            }

            //send mail to project team leader
            if ($collectLeaders) {
                foreach ($collectLeaders as $leaderId => $leader) {
                    if (!$isChangeApprove || isset($dataRewardChange[$leaderId])) {
                        $leaderProjs = $leader['projects'];
                        $projNames = '';
                        foreach ($leaderProjs as $name) {
                            $projNames .= $name . ', ';
                        }
                        $projNames = trim($projNames, ', ');

                        $emailLeaderData = [
                            'dear_name' => $leader['name'],
                            'month_format' => $month->format('m-Y'),
                            'month' => $month->format('Y-m-d H:i:s'),
                            'proj_name' => $projNames,
                            'data_change' => isset($dataRewardChange[$leaderId]) ? $dataRewardChange[$leaderId] : [],
                            'submit_name' => $currUser->name,
                            'is_approved' => true,
                            'is_change' => $isChangeApprove
                        ];

                        $emailLeader = new EmailQueue();
                        $emailLeader->setTo($leader['email'])
                                ->setSubject(trans('project::me.Subject mail reward change approved', ['project' =>  $projNames, 'month' => $month->format('m-Y')]))
                                ->setTemplate('project::me.mail.reward_change', $emailLeaderData)
                                ->setNotify($leaderId, null, null, [
                                    'category_id' => RkNotify::CATEGORY_PROJECT,
                                    'content_detail' => RkNotify::renderSections('project::me.mail.reward_change', $emailLeaderData)
                                ])
                                ->save();
                    }
                }
            }

            DB::commit();
            return redirect()->route('project::me.reward.review')->with('messages', ['success' => [trans('project::me.Updated successful')]]);
        } catch (\Exception $ex) {
            Log::info($ex);
            DB::rollback();
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('project::me.Save error, please try again laster')]]);
        }
    }

    /*
     * get total money number of all list item
     */
    public function getTotalReward(Request $request)
    {
        $data = $request->except(['isReview', 'hasBtnSubmit', 'routeName']);
        $isReviewPage = $request->get('isReview');
        $hasBtnSubmit = $request->get('hasBtnSubmit');
        $scopeRoute = $request->get('routeName');
        $isPQATeam = false;
        $urlFilter = route($scopeRoute) . '/';
        $teamList = MeEvaluation::getInstance()->getTeamPermissOptions($scopeRoute);
        $this->preFilterData($scopeRoute, $teamList, $data, $isPQATeam, $urlFilter, false);
        if ($isReviewPage) {
            $data['reward_status'] = [MeReward::STT_SUBMIT, MeReward::STT_APPROVE];
            $data['is_review'] = true;
        }
        $collectionModel = MeReward::getMERewardData($scopeRoute, $data, $urlFilter, 'totalReward', false);
        $response = [
            'norm' => 0,
            'reward_suggest' => 0,
            'reward_submit' => 0,
            'reward_approve' => 0,
        ];
        if ($collectionModel->isEmpty()) {
            return $response;
        }

        $listRangeMonths = MeView::listRangeBaselineDate($collectionModel->pluck('eval_month')->toArray());

        $configRewardOnsite = MeView::listRewardsOnsite();
        $empIds = $collectionModel->pluck('emp_id')->toArray();
        $employeesOsite = $this->getEmployeesOsite($data['filter_month'], $empIds);
        $inforEmployeeOnsite = $this->handlingEmployeeOnsite($employeesOsite);

        foreach ($collectionModel as $item) {
            $configRewards = MeView::listRewards(Carbon::parse($item->eval_month));
            if ($item->proj_type == Project::TYPE_ONSITE) {
                $allowanceOnste = 0;
                $itemReward = MeView::getItemReward($item->avg_point, $configRewardOnsite);
                if (isset($inforEmployeeOnsite) &&
                    isset($inforEmployeeOnsite[$item->emp_id])) {
                    $monthOnsite = $inforEmployeeOnsite[$item->emp_id]['month'];
                    $allowanceOnste = MeView::getListAllowanceOnste($monthOnsite);
                }
                $itemReward += $allowanceOnste;
            } else {
                $itemReward = MeView::getItemReward($item->avg_point, $configRewards);
            }
            $itemEffort = $item->day_effort / MeView::getDaysOfMonthBaseline($item->eval_time, $listRangeMonths) * 100;
            $norm = 0;
            $rewardSuggest = 0;
            if ($item->status != MeEvaluation::STT_REWARD) {
                $norm = $itemReward;
                $rewardSuggest = $itemReward * min([$itemEffort, 100]) / 100;
            }
            $rewardSubmit = $item->reward_submit ? $item->reward_submit : 0;
            $rewardApprove = $item->reward_approve ? $item->reward_approve : 0;
            if ($hasBtnSubmit) {
                if (!$isReviewPage) {
                    $rewardSubmit = !is_null($rewardSubmit) ? $rewardSubmit : $rewardSuggest;
                } else {
                    $rewardApprove = $rewardApprove ? $rewardApprove : $rewardSubmit;
                }
            }
            $response['norm'] += $norm;
            $response['reward_suggest'] += $rewardSuggest;
            $response['reward_submit'] += $rewardSubmit;
            if ($isReviewPage) {
                $response['reward_approve'] += $rewardApprove;
            }
        }

        return array_map(function ($aryItem) {
            return number_format($aryItem, 0, '.', ',');
        }, $response);
    }

    /*
     * export ME reward ad reward page
     */
    public function exportData (Request $request)
    {
        $month = $request->get('month');
        if (!$month) {
            return response()->back()
                    ->with(['messages' => [
                        'errors' => [trans('me::view.Invalid data')]
                    ]]);
        }
        $isExportAll = $request->get('is_all');
        $itemIds = [];
        if (!$isExportAll) {
            $itemIds = $request->get('ids');
            if (count($itemIds) < 1) {
                return redirect()->back()
                        ->with(['messages' => [
                            'errors' => [trans('me::view.None item checked')]
                        ]]);
            }
        }
        $data = $request->all();
        // Trang review chỉ lấy các status đã submit hoặc đã duyệt
        if ($data['url_filter'] == route('project::me.reward.review')) {
            $data['reward_status'] = [MeReward::STT_SUBMIT, MeReward::STT_APPROVE];
        }
        $isPQATeam = false;
        $scopeRoute = 'project::me.reward.approve';
        $urlFilter = $request->get('url_filter');
        $urlFilter = $urlFilter ? $urlFilter . '/' : route('project::me.reward.edit') . '/';
        $teamList = MeEvaluation::getInstance()->getTeamPermissOptions($scopeRoute);
        $this->preFilterData($scopeRoute, $teamList, $data, $isPQATeam, $urlFilter);
        if (!$isExportAll && count($itemIds) > 0) {
            $data['item_ids'] = $itemIds;
        }
        $collectionModel = MeReward::getMERewardData($scopeRoute, $data, $urlFilter, 'export');
        if ($collectionModel->isEmpty()) {
            return redirect()->back()
                        ->with(['messages' => [
                            'errors' => [trans('me::view.Not found item')]
                        ]]);
        }

        //export data
        $evalMonth = Carbon::parse($data['filter_month'])->format('Y-m');
        $fileName = 'ME-Reward-' . $evalMonth;
        Excel::create($fileName, function ($excel) use ($collectionModel, $data, $fileName, $evalMonth) {
            $excel->setTitle($fileName);
            $excel->sheet($evalMonth, function ($sheet) use ($collectionModel, $data) {
                //set row header
                $rowHeader = [
                    'No.',
                    'ID',
                    trans('me::view.Name'),
                    trans('me::view.Email'),
                    trans('me::view.Team'),
                    trans('me::view.Project name'),
                    trans('me::view.Project type'),
                    trans('me::view.Contribution level'),
                    trans('me::view.Leader revise') . ' (vnđ)',
                    trans('me::view.COO approve') . ' (vnđ)',
                    trans('me::view.Comment'),
                    trans('me::view.ME comment'),
                ];
                $sheet->row(1, $rowHeader);

                $projectTypeLabels = Project::labelChartTypeProject();
                $sttReward = MeEvaluation::STT_REWARD;
                //set data
                foreach ($collectionModel as $order => $item) {
                    $rowData = [
                        $order + 1,
                        $item->employee_code,
                        $item->employee_name,
                        $item->email,
                        $item->team_member_name,
                        $item->proj_name ? $item->proj_name : 'Team: ' . $item->team_name,
                        isset($projectTypeLabels[$item->proj_type]) ? $projectTypeLabels[$item->proj_type] : 'N/A',
                        ($item->status != $sttReward) ? $item->contribute_label : 'N/A',
                        $item->reward_submit !== null ? number_format($item->reward_submit, 0, '.', ',') : null,
                        $item->reward_approve !== null ? number_format($item->reward_approve) : null,
                        $item->reward_comment,
                        $item->me_comments,
                    ];
                    $sheet->row($order + 2, $rowData);
                }
                //set customize style
                $sheet->getStyle('A1:L1')->applyFromArray([
                        'fill' => [
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => ['rgb' => '004e00']
                        ],
                        'font' => [
                            'color' => ['rgb' => 'ffffff'],
                        ]
                    ]
                );
                //set wrap text
                $sheet->getStyle('A2:L' . ($collectionModel->count() + 1))->getAlignment()->setWrapText(true);
            });
        })->export('xlsx');
    }

    /**
     * update paid status
     * @param Request $request
     * @return type
     */
    public function updatePaid(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'eval_ids.*' => 'required',
            'status' => 'required'
        ]);
        $returnAjax = $request->ajax() || $request->wantsJson();
        if ($valid->fails()) {
            if ($returnAjax) {
                return response()->json([
                    'message' => trans('project::me.No data')
                ], 422);
            }
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('project::me.No data')]]);
        }
        $evalIds = $request->get('eval_ids');
        $status = $request->get('status');
        //get filter data
        $teamId = $request->get('filter_team');
        $month = $request->get('filter_time');
        if (!$teamId || $teamId == '_all_' || !$month || $month == '_all_') {
            return redirect()->back()->withInput()->with('messages', ['errors' => [trans('project::me.You must select team and month')]]);
        }
        //get team
        $team = Team::find($teamId);
        if (!$team) {
            return redirect()->back()->with('messages', ['errors' => [trans('project::me.Team not found')]]);
        }
        $teamLeader = $team->leaderInfo;
        //get month
        $month = Carbon::parse($month);

        DB::beginTransaction();
        try {
            //check permission
            if (Permission::getInstance()->isScopeCompany()) {
                //all
            } elseif (Permission::getInstance()->isScopeTeam()) {
                $teamIds = TeamMember::where('employee_id', Permission::getInstance()->getEmployee()->id)
                        ->lists('team_id')->toArray();
                $meIds = MeEvaluation::from(MeEvaluation::getTableName() . ' as me')
                    ->join(TeamMember::getTableName() . ' as tmb', function ($join) use ($evalIds) {
                        $join->on('me.employee_id', '=', 'tmb.employee_id')
                                ->whereIn('me.id', $evalIds);
                    })
                    ->whereIn('tmb.team_id', $teamIds)
                    ->groupBy('me.id')
                    ->lists('me.id')
                    ->toArray();

                if (!$meIds) {
                    return CoreView::viewErrorPermission();
                }
                $evalIds = $meIds;
            } elseif (Permission::getInstance()->isScopeSelf()) {
                return CoreView::viewErrorPermission();
            }

            if ($status == MeView::STATE_PAID) {
                $collectReward = MeReward::collectBySubmited($evalIds, [MeReward::STT_APPROVE]);
                $collectLeaders = [];
                $rewardPaided = [];

                if ($teamLeader) {
                    $collectLeaders[$teamLeader->id] = [
                        'name' => $teamLeader->name,
                        'email' => $teamLeader->email,
                        'is_team_leader' => true,
                        'team_name' => $team->name
                    ];
                }

                if (!$collectReward->isEmpty()) {
                    foreach ($collectReward as $meReward) {
                        //not same team leader
                        if ($meReward->leader_id && (!$teamLeader || $teamLeader->id != $meReward->leader_id)) {
                            //collect leader id data
                            if (isset($collectLeaders[$meReward->leader_id])) {
                                $collectLeaders[$meReward->leader_id]['projects'][$meReward->proj_id] = $meReward->proj_name;
                            } else {
                                $collectLeaders[$meReward->leader_id] = [
                                    'name' => $meReward->leader_name,
                                    'email' => $meReward->leader_email,
                                    'is_team_leader' => false,
                                    'projects' => [
                                        $meReward->proj_id => $meReward->proj_name
                                    ]
                                ];
                            }
                        }
                        //status unpaid
                        if ($meReward->status_paid == MeView::STATE_UNPAID) {
                            $itemChange = [
                                'account' => $meReward->name . ' ('. ucfirst(strtolower(preg_replace('/@.*/', '', $meReward->email))) .')',
                                'project_name' => $meReward->proj_id ? $meReward->proj_name : '"Team: '.$meReward->team_name . '"'
                            ];
                            if ($meReward->leader_id && (!$teamLeader || $teamLeader->id != $meReward->leader_id)) {
                                $rewardPaided[$meReward->leader_id][] = $itemChange;
                            }
                            if ($teamLeader) {
                                $rewardPaided[$teamLeader->id][] = $itemChange;
                            }
                        }
                    }
                }

                //send email to team leader (project team leader and team leader)
                if ($collectLeaders && $rewardPaided) {
                    $currUser = Permission::getInstance()->getEmployee();

                    foreach ($collectLeaders as $leaderId => $leaderData) {
                        if ($leaderData['is_team_leader']) {
                            $projOrTeamName = $leaderData['team_name'];
                            $textSubject = 'team ' . $projOrTeamName;
                        } else {
                            $projOrTeamName = implode(', ', $leaderData['projects']);
                            $textSubject = 'project ' . $projOrTeamName;
                        }

                        $emailLeaderData = [
                            'dear_name' => $leaderData['name'],
                            'month_format' => $month->format('m-Y'),
                            'month' => $month->format('Y-m-d H:i:s'),
                            'proj_team_name' => $projOrTeamName,
                            'data_change' => isset($rewardPaided[$leaderId]) ? $rewardPaided[$leaderId] : [],
                            'is_team_leader' => $leaderData['is_team_leader'],
                            'submit_name' => $currUser->name,
                            'team_id' => $team->id,
                            'detail_link' => route('project::me.reward.edit', [
                                'team_id' => $team->id,
                                'status_paid' => 1,
                                'time' => $month->format('Y-m-d H:i:s')
                            ])
                        ];

                        $emailLeader = new EmailQueue();
                        $emailLeader->setTo($leaderData['email']);
                        if ($teamLeader && $teamLeader->id == $leaderId) {
                            //add cc to COO
                            $emailReviewReward = CoreConfigData::getValueToArrEmail('project.account_approver_reward');
                            if ($emailReviewReward) {
                                foreach ($emailReviewReward as $email) {
                                    $emailLeader->addCc($email);
                                }
                            }
                            // add cc to accountant
                            $emailAccountant = CoreConfigData::getValueToArrEmail('email_accountant');
                            if ($emailAccountant) {
                                foreach ($emailAccountant as $email) {
                                    $emailLeader->addCc($email);
                                }
                            }
                        }
                        $emailLeader->setSubject(trans('project::me.Subject mail reward paid', ['proj_team' =>  $textSubject, 'month' => $month->format('m-Y')]))
                            ->setTemplate('project::me.mail.reward_paid', $emailLeaderData)
                            ->setNotify($leaderId, null, $emailLeaderData['detail_link'], [
                                'category_id' => RkNotify::CATEGORY_PROJECT,
                                'content_detail' => RkNotify::renderSections('project::me.mail.reward_paid', $emailLeaderData)
                            ])
                            ->save();
                    }
                }
            }

            MeReward::updatePaidStatus($evalIds, $status);

            DB::commit();
            if ($returnAjax) {
                return response()->json([
                    'message' => trans('project::me.Updated successful')
                ]);
            }
            return redirect()->back()->withInput()
                    ->with('messages', ['success' => [trans('project::me.Updated successful')]]);
        } catch (\Exception $ex) {
            DB::rollback();
            \Log::info($ex);
            if ($returnAjax) {
                return response()->json([
                    'message' => trans('project::me.An error occurred')
                ]);
            }
            return redirect()->back()->withInput()
                    ->with('messages', ['errors' => [trans('project::me.An error occurred')]]);
        }
    }

    /*
     * delete me reward item
     */
    public function deleteItem(Request $request)
    {
        $id = $request->get('id');
        if (!$id || !($item = MeEvaluation::find($id))) {
            return response()->json(['message' => trans('project::me.Not found data')], 404);
        }
        $item->delete();
        return response()->json(['message' => trans('project::me.Delete success')]);
    }

    /**
     * getEmployeesOsite
     *
     * @param  carbon $date
     * @param  array $empIds
     * @return array
     */
    public function getEmployeesOsite($date, $empIds)
    {
        if (!count($empIds)) {
            return [];
        }
        $dateLast = clone $date;
        
        $yearMonth = $date->format('Y-m');
        $day = $dateLast->lastOfMonth()->format('Y-m-d');
        $empIds = implode(",", $empIds);
        $dayAllowed = Project::DAY_ALLOWED;
        try {
            $objBusinessTripEmployee = new BusinessTripEmployee();
            $sqlEmployeeOnsite =  $objBusinessTripEmployee->getTablEmployeeOnisteSameBranch($day, true);
            return DB::select("SELECT tbl.*, teams.branch_code
            FROM
                ({$sqlEmployeeOnsite}) tbl
            left join employee_team_history eth on tbl.employee_id = eth.employee_id
            inner join teams on teams.id = eth.team_id
            where eth.deleted_at is null
                and eth.is_working = 1
                and DATE_FORMAT(tbl.start_at, '%Y-%m') <= '{$yearMonth}'
                and DATE_FORMAT(tbl.end_at, '%Y-%m') >= '{$yearMonth}'
                and tbl.employee_id IN ($empIds)
                and tbl.branch_business = teams.branch_code
                and teams.branch_code <> 'japan'
        ");
        } catch (\Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * handlingEmployeeOnsite
     *
     * @param  array $empIds
     * @return array
     */
    public function handlingEmployeeOnsite($employeeOnsite)
    {
        $arrProjEmp = [];
        if (!count($employeeOnsite)) {
            return $arrProjEmp;
        }
        foreach($employeeOnsite as $item) {
            $arrStartId = explode(",", $item->group_start_at_id);
            $arr = [];
            foreach ($arrStartId as $startId) {
                $arrItem = explode("|", $startId);
                $arr[$arrItem[0]] = $arrItem[1];
            }
            $arrProjEmp[$item->employee_id] = [
                'groupId' => explode(",", $item->group_id),
                'groupStartId' => $arr,
                'groupDate' => explode(";", $item->group_date),
                'startAt' => $item->start_at,
                'endAt' => $item->end_at_now,
                'month' => round($item->onsite_days / Project::DAY_IN_MONTH, 2)
            ];
            if (substr($arrProjEmp[$item->employee_id]['startAt'], 0, -3) == substr($arrProjEmp[$item->employee_id]['endAt'], 0, -3)) {
                $cbDay = Carbon::createFromFormat('Y-m-d', $arrProjEmp[$item->employee_id]['startAt']);
                $day = $cbDay->lastOfMonth()->day;
                $arrProjEmp[$item->employee_id]['month'] = round($item->onsite_days / $day, 2);
            }
            
        }
        return $arrProjEmp;
    }

    public function importExcel(Request $request)
    {
        $getDate = $request->get('filterimport');
        $data = $request->file('fileToUpload');
        if (empty($data)) {
            return redirect()->back()
                ->withErrors(trans('contract::message.Not found item'));
        }
        $nameFileType = $data->getClientOriginalExtension();
        if (!in_array($nameFileType, ['xls', 'xlsx'])) {
            return redirect()->back()
                ->withErrors(Lang::get('contract::message.File is not formatted correctly [.xls,.xlsx]!'));
        }
        $configIndex = [
            'order' => 'A',
            'Name' => 'B',
            'Email' => 'C',
            'Me' => 'D',
            'Comment' => 'E',
            'response' => 'F'
        ];
        $countError = 0;
        $countSuccess = 0;
        try {
            $excel = Excel::load($data->getRealPath(), function ($reader) use ($configIndex, &$countError, &$countSuccess, &$getDate) {

                $doc = $reader->getSheet(0);
                $totalRow = $doc->getHighestRow();

                for ($i = 2; $i <= $totalRow; $i++) {
                    if ((int)$totalRow > 1001) {
                        throw new Exception(trans('contract::message.Limit max import contract support 1000 row'), 9999);
                    }
                    $order = $doc->getCell("{$configIndex['order']}$i")->getValue();
                    if (trim($order) == '') {
                        continue;
                    }
                    $empEmail = $doc->getCell("{$configIndex['Email']}$i")->getValue();
                    $empMe = $doc->getCell("{$configIndex['Me']}$i")->getValue();
                    $empCmt = $doc->getCell("{$configIndex['Comment']}$i")->getValue();

                    $empInfo = Employee::getEmpByEmail($empEmail);
                     if (!$empInfo) {
                        //Log error: emp not found
                        $countError++;
                       $doc->setCellValue("{$configIndex['response']}$i", "Địa chỉ email nhân viên không hợp lệ.");
                       continue;
                     }

                    $teamId = Team::getTeamByEmp($empInfo->id,false);

                    $dataImport = [
                        'sel_employee_id' => $empInfo->id,
                        'sel_employee_teamId' => $teamId->id,
                        'sel_employee_email' => $doc->getCell("{$configIndex['Email']}$i")->getValue(),
                        'sel_employee_me' => $doc->getCell("{$configIndex['Me']}$i")->getValue(),
                        'sel_employee_cmt' => $doc->getCell("{$configIndex['Comment']}$i")->getValue(),
                    ];

                    // $filterMonth = Carbon::now()->startOfMonth()->format('Y-m-d');

                    $filterMonth = $getDate; 
                    $filterMonth = Carbon::parse($filterMonth);

                    $empExist = MeEvaluation::checkExists($empInfo->id,null,$filterMonth);
                    if ($empExist) {
                        //Log error: emp exist
                        $countError++;
                        $doc->setCellValue("{$configIndex['response']}$i", "nhân viên đã có trong bảng ME.");
                        continue;
                    }

                    if(!$empMe > 0){
                        $countError++;
                        $doc->setCellValue("{$configIndex['response']}$i", "ME phải lớn > 0.");
                        continue;
                    }

                    $requestValid = new CreateOsdcImportExcel();
                    $validator = Validator::make($dataImport, $requestValid->rules($dataImport), $requestValid->messages());

                    if ($validator->fails()) {
                        $countError++;
                        $errors = $validator->errors()->all();
                        $message = is_array($errors) && count($errors) > 0 ? '- ' . implode("\n - ", $errors) : '- ' . $errors;
                        $doc->setCellValue("{$configIndex['response']}$i", $message);
                        continue;
                    }
                    try {

                        $newMeData = MeReward::createNewME([['employee_id' => $empInfo->id]], $filterMonth, $teamId->id);
                        $evlIds = array_keys($newMeData);
                        $evl_id = $evlIds[0];

                        $itemReward = [
                            'submit' => $dataImport['sel_employee_me'],
                            'comment' => $dataImport['sel_employee_cmt'],
                            'eval_id' => $evl_id
                        ];

                        MeReward::saveReward($itemReward, MeReward::STT_SUBMIT, 0);
                        $countSuccess++;
                        $doc->setCellValue("{$configIndex['response']}$i", 'Successfully');
                    } catch (Exception $ex) {
                        $countError++;
                        $doc->setCellValue("{$configIndex['response']}$i", 'Có lỗi xảy ra');
                        throw $ex;
                    }
                }
            });
        } catch (Exception $ex) {
            Log::error($ex->getTraceAsString());
            if ($ex->getCode() == 9999) {
                return redirect()->back()
                    ->with('warning',trans('project::message.Error system'));
            }
        }
        if (isset($excel)) {
            $fileLog = 'log_' . date('YmdHis');
            // @chmod(storage_path(self::FOLDER_LOG), self::ACCESS_FOLDER);
            $excel->setFilename($fileLog)->store('xls', storage_path(self::FOLDER_LOG));
        }

        if ($countError > 0 && isset($excel)) {
            return redirect()->route('project::me.reward.edit', ['tab' => 'all'])
                ->with('warning', trans('project::message.Import ME success :successCount and errors :errorCount. File respone :urlReponse', [
                    'successCount' => $countSuccess,
                    'errorCount' => $countError,
                    'urlReponse' => route('project::me.reward.download_excel', ['fileName' => $fileLog . '.xls'])
                ]));
        }
        return redirect()->back()->with('success', trans('project::message.Import ME success'));

    }

    public function download($fileName)
    {
        if (trim($fileName) == '') {
            Log::error('File download not found');
            return redirect()->back()->withErrors(trans('contract::message.Download file error'));
        }
        $fileName = str_replace('.tmp', '.xls', $fileName);
        try {
            @chmod(storage_path(self::FOLDER_LOG) . DIRECTORY_SEPARATOR . $fileName, self::ACCESS_FOLDER);
            Excel::load(storage_path(self::FOLDER_LOG) . DIRECTORY_SEPARATOR . $fileName)->download('xls');
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return redirect()->back()->withErrors(trans('contract::message.Download file error'));
        }
    }

    public function downloadFormatFile()
    {
        $fileName = 'File format';
        $teamCode = Team::where('code', '!=', '')->select('name', 'code')->get();
        try {
            Excel::create($fileName, function ($excel) use ($teamCode) {
                $excel->sheet('Sheet1', function ($sheet) {
                    //set row header
                    $rowHeader = [
                        'No',
                        'Name',
                        'email',
                        'ME',
                        'Comment'
                    ];
                    $sheet->row(1, $rowHeader);

                    //format data
                    $rowData = [
                        '1', 'Nguyễn Văn B', 'email_example@rikkeisoft.com', '5000000', 'Làm việc hiệu quả'
                    ];
                    $sheet->row(2, $rowData);
                });
            })->download('xlsx');
        } catch (Exception $exception) {
            Log::error($exception->getTraceAsString());
            return response()->json(['success' => false, 'message' => $exception->getMessage()]);
        }
    }

}
