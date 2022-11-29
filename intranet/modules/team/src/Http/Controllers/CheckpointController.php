<?php

namespace Rikkei\Team\Http\Controllers;

use Rikkei\Core\Http\Controllers\Controller as Controller;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\Model\Checkpoint;
use Rikkei\Team\Model\CheckpointType;
use Rikkei\Team\Model\CheckpointTime;
use Rikkei\Team\Model\CheckpointCategory;
use Rikkei\Team\Model\CheckpointQuestion;
use Rikkei\Team\Model\CheckpointMail;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\Menu;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Employee;
use Lang;
use Illuminate\Http\Request;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\CheckpointResult;
use Rikkei\Team\Model\CheckpointResultDetail;
use Rikkei\Sales\View\View as CssView;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\EmailQueue;
use URL;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;

class CheckpointController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Team');
        Breadcrumb::add('Checkpoint' );
        Menu::setActive('team', 'team');
    }

    static $perPage = 10;
    static $perPageCss = 10;

    /**
     * Create Check page 
     */
    public function create() 
    {
        Breadcrumb::add(Lang::get('team::view.Checkpoint.Create.Title'));
        $employee = Permission::getInstance()->getEmployee();

        if (Permission::getInstance()->isScopeTeam() || Permission::getInstance()->isScopeCompany()) {
            $checkpoint = new Checkpoint();
            $checkpoint->start_date = '';
            $checkpoint->end_date   = '';
            $checkpoint->checkpoint_type_id = 1;
            $teamList = self::getTeamList();

            return view('team::checkpoint.create', [
                'checkpoint'        => $checkpoint,
                'employee'          => $employee,
                'save'              => 'create',
                'checkpointTime'    => CheckpointTime::orderBy('created_at', 'DESC')->get(),
                'checkpointType'    => CheckpointType::all(),
                'rikker_relate'     => [],
                'teamIdsAvailable' => $teamList['teamIdsAvailable'],
                'teamTreeAvailable' => $teamList['teamTreeAvailable']
            ]);
        }

        // If hasn't permission
        return view(
            'core::errors.permission_denied'
        ); 
    }

    public function getTeamList()
    {
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany()) {
            $teamIdsAvailable = true;
        } elseif (Permission::getInstance()->isScopeTeam()){
            //scope team => check
            $employeeCurrent = Permission::getInstance()->getEmployee();
            $teamIdsAvailable = (array) CheckpointPermission::getArrTeamIdByEmployee($employeeCurrent->id);

            //check scope comany of each team
            foreach ($teamIdsAvailable as $key => $teamId) {
                if (! Permission::getInstance()->isScopeTeam($teamId)) {
                    unset($teamIdsAvailable[$key]);
                }
            }
            if (! $teamIdsAvailable) {
                View::viewErrorPermission();
            }

            //get team and all child avaliable
            $teamIdsChildAvailable = [];
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable)) {
                $teamPathTree = Team::getTeamPath();
                foreach ($teamIdsAvailable as $teamId) {
                    if (isset($teamPathTree[$teamId]) && $teamPathTree[$teamId]) {
                        if (isset($teamPathTree[$teamId]['child'])) {
                            $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]['child']);
                            $teamIdsChildAvailable = array_merge($teamIdsChildAvailable, $teamPathTree[$teamId]['child']);
                            unset($teamPathTree[$teamId]['child']);
                        }
                        $teamTreeAvailable = array_merge($teamTreeAvailable, $teamPathTree[$teamId]);
                    }
                    $teamTreeAvailable = array_merge($teamTreeAvailable, [$teamId]);
                }
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsChildAvailable);
            }

            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
               // $teamIdsAvailable = Team::find($id);
            }
        }

        return [
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable
        ];
    }

    /**
     * Update Check point page
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function update($id)
    {
        Breadcrumb::add(Lang::get('team::view.Checkpoint.Update.Title'));
        $checkpoint = Checkpoint::getCheckpointById($id);

        //get employee create css
        $employee = Employee::getEmpById($checkpoint->employee_id);
        if (!$employee) {
            $employee = new Employee();
        }
        //get current employee
        $emp = Permission::getInstance()->getEmployee();

        if (Permission::getInstance()->isScopeCompany()
            || (Permission::getInstance()->isScopeTeam() && CheckpointPermission::getInstance()->checkTeam($emp, $checkpoint->token, $id))) {
            if ($employee->id == $emp->id || Permission::getInstance()->isRoot()) {
                //format css date
                $checkpoint->start_date = date('Y/m/d',strtotime($checkpoint->start_date));
                $checkpoint->end_date   = date('Y/m/d',strtotime($checkpoint->end_date));
                $rikkerRelate = [];
                if($checkpoint->rikker_relate && $checkpoint->rikker_relate != ''){
                    $arrTemp = explode(',', $checkpoint->rikker_relate);
                    $employeeRelated = Employee::getEmpByEmails($arrTemp, ['email', 'name', 'id']);

                    foreach($arrTemp as $email){
                        $employee = array_first($employeeRelated, function ($index, $employee) use($email) {
                            return ($employee->email == $email);
                        });

                        if(!empty($employee)) {
                            $rikkerRelate[] = [
                                'email'  => $employee->email,
                                'name'  => ($employee->name === '') ? $email : $employee->name
                            ];
                        } else {
                            $rikkerRelate[] = [
                                'email'  => $email,
                                'name'  => $email
                            ];
                        }
                    }
                }

                $teamList = self::getTeamList();

                //Get evaluators
                $evaluators = [];
                $evaluatorsSelected = [];
                $evaluatedSelected = [];
                if ($checkpoint->evaluators && !empty($checkpoint->evaluators)) {
                    $evaluators = json_decode($checkpoint->evaluators);
                    foreach ($evaluators as $item) {
                        $evaluatorsSelected[] = $item->evaluatorId;
                        if (is_array($item->evaluated)) {
                            $evaluatedSelected = array_merge($evaluatedSelected,$item->evaluated);
                        }

                    }
                }

                $empOfTeam = Employee::getEmpForCheckpoint($checkpoint->team_id, $checkpoint->start_date, $checkpoint->end_date);
                $empAll = Employee::getAllEmployee('id', 'asc', true);
                return view(
                    'team::checkpoint.create', 
                    [
                        'checkpoint'        => $checkpoint, 
                        'employee'          => $employee,
                        'save'              => 'update',
                        'checkpointTime'    => CheckpointTime::orderBy('created_at', 'DESC')->get(),
                        'checkpointType'    => CheckpointType::all(),
                        'rikker_relate'     => $rikkerRelate,
                        'teamIdsAvailable'  => $teamList['teamIdsAvailable'],
                        'teamTreeAvailable' => $teamList['teamTreeAvailable'],
                        'evaluators'        => $evaluators,
                        'empOfTeam'         => $empOfTeam,
                        'empAll'            => $empAll,
                        'evaluatorsSelected' => $evaluatorsSelected,
                        'evaluatedSelected'  => $evaluatedSelected
                    ]
                );
            }
        }
        
        // If hasn't permission
        return view(
            'core::errors.permission_denied'
        );
    }

    /**
     * Save Check point (insert or update)
     */
    public function save(Request $request)
    {
        $start_date = date('Y-m-d', strtotime($request->input('start_date')));
        $end_date = date('Y-m-d', strtotime($request->input('end_date')));
        
        if ($request->input("create_or_update") == 'create') {
            $checkpoint = new Checkpoint();
        } else {
            $checkpointId = $request->input("checkpoint_id");
            $checkpoint = Checkpoint::getCheckpointById($checkpointId);
            // Clear cache before update
            CacheHelper::forget(Checkpoint::KEY_CACHE, $checkpointId);
            CacheHelper::forget(Checkpoint::KEY_CACHE, $checkpointId . '_' . $checkpoint->token);
        }

        $checkpoint->employee_id = $request->input("employee_id");
        if ($request->input("rikker_relate")) {
            $checkpoint->rikker_relate = implode(',', $request->input("rikker_relate"));
        } else {
            $checkpoint->rikker_relate = '';
        }
        $checkpoint->start_date = $start_date;
        $checkpoint->end_date = $end_date;
        $checkpoint->checkpoint_type_id = $request->input("checkpoint_type_id");
        $checkTimeId = $request->input("check_time");
        $checkpoint->checkpoint_time_id = $checkTimeId;
        $checkPeriod = CheckpointTime::find($checkTimeId);
        if (!$checkPeriod) {
            $response['popup'] = 1;
            $response['success'] = 0;
            $response['message_error'] = Lang::get('team::messages.Checkpoint cannot create because checkpoint time not exist!');

            return response()->json($response);
        }
        $checkpoint->team_id = $request->input("set_team");
        //$checkpoint->evaluator_id = $request->input("evaluator");

        if ($request->input("create_or_update") == 'create') {
            $checkpoint->token = md5(rand());
        }

        //Evaluators
        $evaluators = $request->input('evaluator');
        $eva = [];
        if ($evaluators) { 
            $evaluators = array_filter($evaluators);
            $evaluated = $request->input('evaluated');
            $evaluated = array_filter($evaluated);
            foreach ($evaluators as $key => $empId) {
                if (!empty($empId)) {
                    $eva[] = [
                        'evaluatorId' => $empId,
                        'evaluated' => $evaluated[$key]
                    ];
                }
            }
            $checkpoint->evaluator_id = implode(',', $evaluators);
            
            foreach ($evaluated as $item) {
                foreach ($item as $itemChild) {
                    $evaluatedTemp[] =  $itemChild;
                }
            }
            $checkpoint->evaluated_id = implode(',', $evaluatedTemp);
        }

        $checkpoint->evaluators = json_encode($eva);
        $checkpoint->save();
        CacheHelper::forget(Checkpoint::KEY_CACHE, $checkpoint->id);

        //Save mail to queue
        $team = Team::find($checkpoint->team_id);
        $leader = $team->getLeader();
        $template = 'team::checkpoint.createCheckpointMail';

        $curEmp = Permission::getInstance()->getEmployee();
        $checkpointTime = CheckpointTime::find($checkpoint->checkpoint_time_id);
        $data = [
            'urlWelcome' => route('team::checkpoint.welcome', ['token' => $checkpoint->token, 'id' => $checkpoint->id]),
            'start'      => date('Y/m/d', strtotime($checkpoint->start_date)),
            'end'        => date('Y/m/d', strtotime($checkpoint->end_date)),
            'team'       => $team->name,
            'checkTime' => $checkpointTime->check_time
        ];

        $evaluatedsRaw = [];
        foreach ($evaluated as $item) {
            $evaluatedsRaw = array_merge($evaluatedsRaw, $item);
        }

        $evaluateds = [];
        $evaluateds = array_diff($evaluatedsRaw, CheckpointMail::getEmpIdfollowCheckpoint($checkpoint->id));

        if ($evaluateds && count($evaluateds)) {
            $getEmpFromEvaluateds = Employee::getEmpByIds($evaluateds);
            if (count($getEmpFromEvaluateds)) {
                foreach ($getEmpFromEvaluateds as $item) {
                    $subject = Lang::get('team::view.Checkpoint.Create.Mail subject',
                                [
                                    'checkpoint_time' => $data['checkTime'],
                                    "team" => $team->name, 
                                    'empName' => CheckpointPermission::getNickName($item->email)
                                ]);
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($item->email)
                        ->setFrom('intranet@rikkeisoft.com', 'Rikkeisoft intranet')
                        ->setSubject($subject)
                        ->setTemplate($template, $data);
                    $emailQueue->save();

                    $checkpoint_email = new CheckpointMail();
                    $checkpoint_email->employee_id = $item->id;
                    $checkpoint_email->checkpoint_id = $checkpoint->id;
                    $checkpoint_email->save();
                }
                //set notify
                \RkNotify::put(
                    $getEmpFromEvaluateds->lists('id')->toArray(),
                    Lang::get('team::view.Checkpoint.Create.Notify subject', ['checkpoint_time' => $data['checkTime'], 'team' => $team->name]),
                    $data['urlWelcome'], ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                );
            }
        }

        $response['url'] = route('team::checkpoint.preview', ['token' => $checkpoint->token, 'id' => $checkpoint->id]);
        $response['success'] = 1;
        return response()->json($response);
    }

    /**
     * Preview page
     * @param string $token
     * @param int $id
     * @return objects
     */
    public function preview($token, $id)
    {
        Breadcrumb::add(Lang::get('team::view.Checkpoint.Preview.Title'));
        $data = self::getCategory($id, $token);

        if ($data) {
            $checkTime = CheckpointTime::find($data['checkpoint']->checkpoint_time_id);
            //ME point last 6 month
            $avgMePoint = Checkpoint::getAvgMePoint($data['employee']->id, $checkTime->check_time);
            return view('team::checkpoint.preview', [
                'checkpoint' => $data['checkpoint'],
                'employee' => $data['employee'],
                'cate' => $data['cate'],
                'emp' => Permission::getInstance()->getEmployee(),
                'hrefMake' => url("/team/checkpoint/welcome/$token/$id"),
                'hrefUpdateCss' => url("/team/checkpoint/update/$id"),
                'checktime' => $checkTime,
                'avgMePoint' => $avgMePoint
            ]);
        } else {
            return redirect("/");
        }
    }

    /**
     * Get Category list with question
     * @param int $id
     * @param string|null $token
     * @return array | null
     */
    public function getCategory($id, $token = null)
    {
        $questionModel = new CheckpointQuestion();
        $categoryModel = new CheckpointCategory();
        $checkpointModel = new Checkpoint();
        if ($token) {
            $checkpoint = $checkpointModel->getCheckpointByIdAndToken($id, $token);
        } else {
            $checkpoint = $checkpointModel->getCheckpointById($id);
        }

        if (count($checkpoint)) {
            $employee = Employee::getEmpById($checkpoint->employee_id);
            if (!$employee) {
                $employee = new Employee();
            }
            $rootCategory = $categoryModel->getRootCategory($checkpoint->checkpoint_type_id);
            $category = $categoryModel->getCategoryByParent($rootCategory->id);
            $cateIds = $category->pluck('id')->toArray();

            $categoryChilds = $categoryModel->getCategoryByParents($cateIds, ['id', 'name', 'sort_order', 'parent_id']);
            $questions = $questionModel->getQuestionByCategories($cateIds);

            if (count($category) > 0) {
                foreach ($category as $item) {
                    $childs = array_where($categoryChilds, function ($index, $cateChild) use ($item) {
                        return ($cateChild->parent_id == $item->id);
                    });

                    $cateChild = array();
                    if (!empty($childs)) {
                        $childIds = array_pluck($childs, 'id');
                        $questionChilds = $questionModel->getQuestionByCategories($childIds);

                        foreach ($childs as $itemChild) {
                            $questionChild = array_where($questionChilds, function ($index, $item) use ($itemChild) {
                                return ($itemChild->id == $item->category_id);
                            });
                            $cateChild[] = array(
                                "id" => $itemChild->id,
                                "name" => $itemChild->name,
                                "parent_id" => $item->id,
                                "sort_order" => $itemChild->sort_order,
                                "questionsChild" => $questionChild,
                            );
                        }
                    }

                    $question = array_where($questions, function ($index, $question) use ($item) {
                        return ($item->id == $question->category_id);
                    });

                    $cate[] = array(
                        "id" => $item->id,
                        "name" => $item->name,
                        "sort_order" => View::romanic_number($item->sort_order,true),
                        "cateChild" => $cateChild,
                        "questions" => $question,
                    );
                }
            }

            return [
                'checkpoint' => $checkpoint,
                'employee' => $employee,
                'cate' => $cate,
            ];
        } else {
            return null;
        }    
    }

    /**
     * Welcome page
     * @param string $token
     * @param int $id
     * @param Request $request
     */
    public function welcome($token, $id, Request $request)
    {
        $cpModel = new Checkpoint();
        $checkpoint = $cpModel->getCheckpointByIdAndToken($id,$token);
        if (count($checkpoint)) {
            $user = Permission::getInstance()->getEmployee();
            return view('team::checkpoint.welcome', [
                    'checkpoint' => $checkpoint,
                    'token' => $token,
                    'id'    => $id,
                    'user'  => $user,
                    'href'  => url("/team/checkpoint/make/$token/$id")
            ]);
        }

        return view(
            'core::errors.404'
        );
    }
    
    /**
     * Make Checkpoint page
     * @param string $token token
     * @param int $id checkpoint_id
     * @return array
     */
    public function make($token, $id, Request $request)
    {
        $emp = Permission::getInstance()->getEmployee();
        // Check employee of team
        if (!CheckpointPermission::getInstance()->checkTeam($emp, $token, $id)) {
            return view(
                'core::errors.permission_denied'
            );
        }

        // Check employee has permission make this checkpoint
        if (!CheckpointPermission::hasUpdateCheckpoint($id, $emp->id)) {
            return view(
                'team::checkpoint.error', ['message' => Lang::get('team::messages.Checkpoint has been reviewed by the leader')]
            );
        }

        $data = self::getCategory($id, $token);
        if ($data) {
            $checkpoint = $data['checkpoint'];
            //Find evaluator
            $evaluator = CheckpointPermission::getInstance()->findEvalutor($checkpoint->evaluators, $emp->id);
            //Check current user have in list make this checkpoint
            if (!$evaluator) {
                return view(
                    'team::checkpoint.error',['message' => Lang::get('team::view.Checkpoint.Error.Not in list')]
                );
            }

            $curDate = date('Y-m-d');
            //Check start date, end date of checkpoint
            if($checkpoint['start_date'] > $curDate || $checkpoint['end_date'] < $curDate) {
                return view(
                    'team::checkpoint.error', ['message' => Lang::get('team::view.Checkpoint.Error date.Notify date checkpoint')]
                );
            }
            $result = CheckpointResult::getResultOfEmployee($emp->id, $id);

            $checkTime = CheckpointTime::find($data['checkpoint']->checkpoint_time_id);
            //ME point last 6 month
            $avgMePoint = Checkpoint::getAvgMePoint($emp->id, $checkTime->check_time);
            return view('team::checkpoint.make', [
                'result' => $result,
                'checkpoint' => $data['checkpoint'],
                "employee" => $data['employee'],
                'evaluator' => $evaluator,
                "cate" => $result ? CheckpointPermission::getQuestionWithPoint($result, $emp->id, $data['cate']) : $data['cate'],
                "emp"  => Permission::getInstance()->getEmployee(),
                'checktime' => $checkTime,
                'avgMePoint' => $avgMePoint
            ]);
        } else {
            return view(
                'core::errors.404'
            );
        }
    }

    /**
     * Insert Checkpoit result into database
     * @return void
     */
    public function saveResult(Request $request)
    {
        $arrayQuestion  = $request->input('arrayQuestion');
        $totalPoint     = $request->input('totalPoint');
        $proposed       = $request->input('proposed');
        $checkpointId   = $request->input('id');
        $team_id = $request->input('team_id');
        $user = Permission::getInstance()->getEmployee();

        $dataResult = [
            'checkpoint_id' => $checkpointId,
            'total_point' => $totalPoint,
            'comment' => $proposed,
            'employee_id' => $user->id,
            'team_id' => $team_id
        ];

        $result = CheckpointResult::getResultOfEmployee($user->id, $checkpointId);
        $resultModel = new CheckpointResult();
        $resultId = $resultModel->saveData($dataResult, $arrayQuestion, $result);

        $checkpoint = Checkpoint::getCheckpointById($checkpointId); 
        //Find evaluator
        $evaluator = CheckpointPermission::getInstance()->findEvalutor($checkpoint->evaluators, $user->id); 
        $email = $evaluator->email; 
        $relateEmails = [];
        if (!empty($checkpoint->rikker_relate)) {
            $relateEmails = explode(',', $checkpoint->rikker_relate); 
        }
        $checkpointType = CheckpointType::find($checkpoint->checkpoint_type_id);

        $data = array(
            'href'      => url("/team/checkpoint/detail/" . $resultId) ,
            'startDate' => date('d/m/Y', strtotime($checkpoint['start_date'])),
            'endDate'   => date('d/m/Y', strtotime($checkpoint['end_date'])),
            'result'    => $dataResult,
            'empName'  => $evaluator->name,
            'email'     => $email,
            'totalPoint'  => $totalPoint,
            'name'      => $user->name,
            'checkType' => $checkpointType
        );

        //Save mail to queue
        $template = 'team::checkpoint.sendMail';
        $subject = Lang::get('team::messages.Checkpoint.Make.Mail subject',
                                ["name" => $user->name, "point" => $totalPoint]);
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($email)
            ->setFrom('pqa@rikkeisoft.com', 'Rikkeisoft intranet')
            ->setSubject($subject)
            ->setTemplate($template, $data)
            ->setNotify($evaluator->id, null, $data['href'], [
                'category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE,
                'content_detail' => RkNotify::renderSections($template, $data)
            ]);
        $emailQueue->save();
    }

    /**
     * Success page
     */
    public function success()
    {
        return view('team::checkpoint.success'); 
    }

    /**
     * Detail page
     */
    public function detail($resultId)
    {
        $result = CheckpointResult::getResultById($resultId);
        if( count($result) ) {
            $emp = Permission::getInstance()->getEmployee();
            $empMake = Employee::getEmpById($result->employee_id);
            if (!$empMake) {
                $empMake = new Employee();
            }
            // If has permission
            if(CheckpointPermission::getInstance()->isAllowDetail($emp,$result)) {
                $data = self::getCategory($result->checkpoint_id);
                if ($data) {
                    //Find evaluator
                    $evaluator = CheckpointPermission::getInstance()->findEvalutor($data['checkpoint']->evaluators, $result->employee_id);
                    $canEdit = CheckpointPermission::getInstance()->isEvaluatorOfResult($emp,$result);

                    //Is leader of team of checkpoint
                    $canCmt = Permission::getInstance()->isAllow('team::checkpoint.cmt');
                    $isLeader = CheckpointPermission::getInstance()->isLeader($emp, $result->checkpoint_id);
                    $checkTime = CheckpointTime::find($data['checkpoint']->checkpoint_time_id);
                    //ME point last 6 month
                    $avgMePoint = Checkpoint::getAvgMePoint($result->employee_id, $checkTime->check_time);

                    return view('team::checkpoint.detail', [
                        'result'    => $result,
                        'checkpoint' => $data['checkpoint'],
                        "employee" => $data['employee'],
                        'evaluator' => $evaluator,
                        'cate' => CheckpointPermission::getQuestionWithPoint($result, $emp->id, $data['cate']),
                        'emp'  => $emp,
                        'checktime' => $checkTime,
                        'canEdit'   => $canEdit,
                        'empMake'  => $empMake,
                        'canCmt' => $canCmt,
                        'isLeader' => $isLeader,
                        'avgMePoint' => $avgMePoint
                    ]);
                } else {
                    return view(
                        'core::errors.404'
                    );
                }
            }
            // If hasn't permission
            return view(
                'core::errors.permission_denied'
            );
        }

        // If data empty return 404 page
        return view(
            'core::errors.404'
        );
    }

    /**
     * Save result leader
     */
    public function saveResultLeader(Request $request)
    {
        $arrayQuestion  = $request->input('arrayQuestion');
        $totalPoint     = $request->input('totalPoint');
        $leaderComment  = $request->input('proposed');
        $resultId   = $request->input('resultId');
        $result = CheckpointResult::getResultById($resultId);
        $emp = Permission::getInstance()->getEmployee();
        $isLeader = CheckpointPermission::getInstance()->isLeader($emp, $result->checkpoint_id);
        $checkpoint = Checkpoint::getCheckpointById($result->checkpoint_id);
        $evaluator = CheckpointPermission::getInstance()->findEvalutor($checkpoint->evaluators, $result->employee_id);
        //is evaluator when $canEdit = true
        $canEdit = CheckpointPermission::getInstance()->canEdit($emp,$evaluator);
        $canCmt = Permission::getInstance()->isAllow('team::checkpoint.cmt');
        // If has permission then update checkpoint result
        if ($canEdit || $canCmt) {
            if ($canEdit) {
                $result->leader_total_point = $totalPoint;
            }
            $result->leader_comment = $leaderComment;
            $result->updateResult();
            CheckpointResultDetail::updateDetail($resultId,$arrayQuestion);
            if ($canEdit) {
                //Send mail to employee who made checkpoint
                $evaluated = Employee::getEmpById($result->employee_id);
                if ($evaluated) {
                    $checkpointTime = CheckpointTime::find($checkpoint->checkpoint_time_id);
                    $data['checkTime'] = $checkpointTime->check_time;
                    $subject = Lang::get('team::messages.Checkpoint.Review.Mail subject',
                                            ["name" => $emp->name, "point" => $totalPoint]);
                    $template = 'team::checkpoint.mailAfterMake';
                    $data = array(
                        'href'      => route("team::checkpoint.detail", ['id' => $resultId]) ,
                        'startDate' => date('d/m/Y', strtotime($checkpoint['start_date'])),
                        'endDate'   => date('d/m/Y', strtotime($checkpoint['end_date'])),
                        'totalPoint' => $result->total_point,
                        'leaderTotalPoint'  => $totalPoint,
                        'name'      => $evaluated->name,
                        'checkTime' => $checkpointTime->check_time,
                        'reviewerName' => $emp->name
                    );
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($evaluated->email)
                        ->setFrom('pqa@rikkeisoft.com', 'Rikkeisoft intranet')
                        ->setSubject($subject)
                        ->setTemplate($template, $data)
                        ->setNotify($evaluated->id, null, $data['href'], ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]);
                    $emailQueue->save();
                }
            }
        }
    }

    public function listself()
    {
        Breadcrumb::add(Lang::get('team::view.Checkpoint.List.Danh sách checkpoint của mình'));
        $list = Checkpoint::getListSelf();
        return view('team::checkpoint.listself', [
            'collectionModel' => $list,
            'curEmp' => Permission::getInstance()->getEmployee()
        ]);
    }

    /**
     * List of checkpoint
     */
    public function grid() 
    {
        Breadcrumb::add(Lang::get('team::view.Checkpoint.List.checkpoint'));
        $pager = Config::getPagerData();
        $pagerFilter = (array) Form::getFilterPagerData();
        $pagerFilter = array_filter($pagerFilter);
        $order = 'checkpoint.created_at';
        $dir = 'desc';
        if ($pagerFilter) {
            $order = $pager['order'];
            $dir = $pager['dir'];
        }
        $list = CheckpointPermission::getInstance()->getList($order, $dir);
        if (count($list) > 0) {
            $list = CoreModel::filterGrid($list);
            $list = CoreModel::pagerCollection($list, $pager['limit'], $pager['page']);

            $evaluatorIds = [];
            foreach ($list as $key => $item) {
                $employeeIds = explode(',', $item->evaluator_id);
                $evaluatorIds = array_merge($evaluatorIds, $employeeIds) ;
            }
            $evaluatorIds = array_filter(array_unique($evaluatorIds));
            $employees = Employee::select(['id', 'email', 'leave_date'])->whereIn('id', $evaluatorIds)->get();

            foreach ($list as $key => &$item) {
                $evaluatorId = explode(',', $item->evaluator_id);
                $emptyEvaluated = true;
                $emps = array_where($employees, function ($index, $item) use ($evaluatorId) {
                    return in_array($item->id, $evaluatorId);
                });

                foreach ($emps as $emp) {
                    if (!empty($emp) && (!$emp->leave_date || (substr($emp->leave_date, 0, 10) > $item->start_date))) {
                        $emptyEvaluated = false;
                        break;
                    }
                }
                if ($emptyEvaluated) {
                    unset($list[$key]);
                } else {
                    $item->start_date = date('Y/m/d',strtotime($item->start_date));
                    $item->end_date = date('Y/m/d',strtotime($item->end_date));
                    $item->created_date = date('Y/m/d',strtotime($item->created_at));
                    $item->url =  URL::route('team::checkpoint.welcome',['token' => $item->token, 'id' => $item->id]);
                    $item->hrefPreview = URL::route('team::checkpoint.preview',['token' => $item->token, 'id' => $item->id]);
                    $item->hrefMake = URL::route('team::checkpoint.made', ['id' => $item->id]);
                    $item->hrefEdit = URL::route('team::checkpoint.update', ['id' => $item->id]);

                    $tempEva = [];
                    if ($emps) {
                        foreach ($emps as $emp) {
                            $tempEva[] = CheckpointPermission::getNickName($emp->email);
                        }
                    }
                    if (count($tempEva)) {
                        $item->eva = implode(', ', $tempEva);
                    } else {
                        $item->eva = '';
                    }

                    //get total evaluated
                    $item->countEvaluated = CheckpointPermission::getInstance()->getCountEvaluatedOfCheckpoint($item->evaluated_id);
                }
            }
        }
        $per = new Permission();
        return view(
            'team::checkpoint.list', [
                'collectionModel' => $list,
                'isRoot' => $per->isRoot(),
                'checkpointTime'    => CheckpointTime::orderBy('created_at', 'DESC')->get(),
            ]
        );
    }

    /**
     * List employees make(made) of checkpoint page
     *
     * @param int $checkpointId
     * @param Request $request
     * @return Response View
     */
    public function made($checkpointId, Request $request)
    {
        Breadcrumb::add(Lang::get('sales::view.Css list result'));
        $checkpoint = Checkpoint::getCheckpointById($checkpointId);
        $emp = Permission::getInstance()->getEmployee();
     
        //If hasn't permission
        if (!CheckpointPermission::getInstance()->isAllow($emp, $checkpointId)){
            return view(
                'core::errors.permission_denied'
            );
        }

        if (count($checkpoint)) {
            $pager = Config::getPagerData();
            $dataOrder = Form::getFilterPagerData('order');
            $order = 'id';
            $dir = 'desc';
            if ($dataOrder) {
                $order = $pager['order'];
                $dir = $pager['dir'];
            } 

            $filter = Form::getFilterData()['except'];
            $results = CheckpointResult::getResultByCheckpointId($checkpointId, $order, $dir, $filter);
            
            $team = Team::find($checkpoint->team_id);
            $checkpointTime = CheckpointTime::find($checkpoint->checkpoint_time_id);
            
            //Get list evaluated
            $evaluatedSelected = [];
            if ($checkpoint->evaluators && !empty($checkpoint->evaluators)) {
                $evaluators = json_decode($checkpoint->evaluators);
                foreach ($evaluators as $item) {
                    if (is_array($item->evaluated)) {
                        $evaluatedSelected = array_merge($evaluatedSelected,$item->evaluated);
                    }
                    
                }
            }

            $startDate = $checkpoint->start_date;
            $cpEvaluator = $checkpoint->evaluators ? json_decode($checkpoint->evaluators, true) : [];
            $evaluated = null;
            if ($filter && isset($filter['checkpoint.evaluator_id'])) {
                $evaluated = CheckpointPermission::getInstance()->getEvaluatedByEvaluator($checkpoint->evaluators, $filter['checkpoint.evaluator_id']);
            }
            $evaluatorIds = [];
            $results = $results->filter(function ($item, $key) use (
                $startDate,
                &$evaluatorIds,
                $cpEvaluator,
                $evaluated
            ) {
                if ($item->leave_date && ($item->leave_date < $startDate)) {
                    return false;
                }
                if ($cpEvaluator) {
                    foreach ($cpEvaluator as $evaItem) {
                        foreach ($evaItem['evaluated'] as $empId) {
                            if ($item->emp_id == $empId) {
                                $evaluatorIds[$item->emp_id] = $evaItem['evaluatorId'];
                                break;
                            }
                        }
                    }
                }
                if ($evaluated) {
                    if (!in_array($item->emp_id, $evaluated)) {
                        return false;
                    }
                }
                return true;
            });
            $listEvaNames = [];
            if ($evaluatorIds) {
                $listEvaNames = Employee::whereIn('id', $evaluatorIds)->lists('name', 'id')->toArray();
            }
            $results = $results->map(function ($item) use ($evaluatorIds, $listEvaNames) {
                $item->eva = isset($evaluatorIds[$item->emp_id]) && isset($listEvaNames[$evaluatorIds[$item->emp_id]]) 
                        ? $listEvaNames[$evaluatorIds[$item->emp_id]] : null;
                return $item;
            });
            //sort by evaluator
            if ($order == 'evaluator_id') {
                if ($dir == 'asc') {
                    $results = $results->sortBy('eva');
                } else {
                    $results = $results->sortByDesc('eva');
                }
            }

            return view('team::checkpoint.made', [
                'collectionModel' => $results,
                'checkpoint' => $checkpoint,
                'team'  => $team,
                'checkpointTime' => $checkpointTime,
                'evaluatedSelected' => $evaluatedSelected,
                'evalutors' => $listEvaNames,
                'evaluatorIds' => $evaluatorIds,
            ]);
        }

        // If data empty return 404 page
        return view(
            'core::errors.404'
        );
    }

    /**
     * Clear all Checkpoint
     */
    public function reset()
    {
        $per = new \Rikkei\Team\View\Permission;
        // Root only has permission
        if ($per->isRoot()) {
            Checkpoint::clearAll();
        }
    }

    public function setEmp(Request $request)
    {
        $teamId = $request->input('teamId');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        if ($teamId) {
            $empOfTeam = Employee::getEmpForCheckpoint($teamId, $startDate, $endDate);
            if ($empOfTeam) {
                foreach ($empOfTeam as &$emp) {
                    $emp->nickname = CssView::getAccName($emp->email);
                }
            }
            $empAll = Employee::getAllEmpForCheckpoint($startDate);
            if ($empAll) {
                foreach ($empAll as &$emp) {
                    $emp->nickname = CssView::getAccName($emp->email);
                }
            }
            return response()->json(['empOfTeam' => $empOfTeam, 'empAll' => $empAll]);
        } else {
            return 0;
        }
    }

    public function listPeriod ()
    {
        $collectionModel = CheckpointTime::gridCheckPointPeriod();
        if (!Permission::getInstance()->isAllow('team::checkpoint.period.list')) {
            return view('errors.permission');
        }
        return view(
            'team::checkpoint.list_period_checkpoint', [
                'collectionModel' => $collectionModel,
                'checkpointTime'    => CheckpointTime::orderBy('created_at', 'DESC')->get(),
            ]
        );
    }

    /*
     * save imformation period checkpoint.create or edit.
     *
     * @param $periodId.
     * @return void.
     */
    public static function savePeriodCheckpoint($periodId = null)
    {
        $period = Input::get('period-checkpoint');
        $yearCheckpoint = Input::get('year-checkpoint');
        $periodId = Input::get('idCheckpoint');
        if (!$period || !$yearCheckpoint) {
            return redirect(route('team::checkpoint.period.list'))->with('status-error', trans('team::messages.Period or year checkpoint error!'));
        }
        if (!$periodId) {
            $stringTime = $period . '/'. $yearCheckpoint;
            // check period isset.
            $collection = CheckpointTime::where('check_time', '=', $stringTime)->get();
            if (count($collection)) {
                return redirect(route('team::checkpoint.period.list'))->with('status-error', trans('team::messages.Period checkpoint was created!'));
            }
            DB::beginTransaction();
            try {
                $checkpointTime = new CheckpointTime();
                $checkpointTime->check_time = $stringTime;
                $checkpointTime->save();
                DB::commit();
                return redirect(route('team::checkpoint.period.list'))->with('status-success', trans('team::messages.Save period success!'));
            } catch (Exception $ex) {
                DB::rollback();
                Log::info($ex);
            }
        } else {
            // only edit period checkpoint not yet use.
            $item = CheckpointTime::isCheckpointTimeNotYetUse($periodId);
            if ($item) {
                $checkpointTime = CheckpointTime::where('id', $periodId);
                $stringTime = $period . '/'. $yearCheckpoint;
                $collection = CheckpointTime::where('check_time', '=', $stringTime)->get();
                if (count($collection)) {
                    return redirect(route('team::checkpoint.period.list'))->with('status-error', trans('team::messages.Period checkpoint was created!'));
                }
                $checkpointTime->update(['check_time' => $stringTime]);
                return redirect(route('team::checkpoint.period.list'))->with('status-success', trans('team::messages.Edit period success!'));
            } else {
                return redirect(route('team::checkpoint.period.list'))->with('status-error', trans('team::messages.Checkpoint time cannot edit because used!'));
            }
        }
    }

    /*
     * delete checkpoint time.
     *
     * @return void.
     */
    public static function deletePeriod()
    {
        $idPeriod = Input::get('id');
        if ($idPeriod) {
            $item = CheckpointTime::isCheckpointTimeNotYetUse($idPeriod);
            if ($item) {
                CheckpointTime::where('id', '=', $idPeriod)->delete();
                $response['success'] = 1;
                $response['message'] = Lang::get('team::messages.Checkpoint delete success!');
            } else {
                $response['success'] = 0;
                $response['message_error'] = Lang::get('team::messages.Checkpoint time cannot delete because used!');
            }
            $response['popup'] = 1;
            return response()->json($response);
        }
    }
}
