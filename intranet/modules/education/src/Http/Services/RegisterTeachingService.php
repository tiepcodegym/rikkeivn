<?php

namespace Rikkei\Education\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Rikkei\Education\Model\EducationCourse;
use Rikkei\Education\Model\EducationClass;
use Rikkei\Education\Model\EducationClassShift;
use Rikkei\Education\Model\EducationTeacherTime;
use Rikkei\Education\Model\EducationTeacher;
use Rikkei\Education\Model\SettingAddressMail;
use Rikkei\Team\Model\Action;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Permission as TeamPermission;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form as CoreForm;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Permission as PerModel;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Education\Http\Helper\CommonHelper;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Team\Model\EmployeeRole;
use Lang;
use Rikkei\Team\View\Permission as PermissonView;

class RegisterTeachingService
{
    protected $modelEducationCourses;

    protected $modelEducationClass;

    protected $modelEducationClassShift;

    protected $modelEducationTeacherTime;

    protected $modelEducationTeacher;

    protected $helper;


    public function __construct(
        EducationCourse $modelEducationCourses,
        EducationClass $modelEducationClass,
        EducationClassShift $modelEducationClassShift,
        EducationTeacherTime $modelEducationTeacherTime,
        EducationTeacher $modelEducationTeacher,
        CommonHelper $helper
    )
    {
        $this->modelEducationCourses = $modelEducationCourses;
        $this->modelEducationClass = $modelEducationClass;
        $this->modelEducationClassShift = $modelEducationClassShift;
        $this->modelEducationTeacherTime = $modelEducationTeacherTime;
        $this->modelEducationTeacher = $modelEducationTeacher;
        $this->helper = $helper;
    }

    public function getCourseByCourseTypeId($type_id)
    {
        return $this->modelEducationCourses->where('type', $type_id)->pluck('name', 'id');
    }

    public function getClassByCourseId($course_id)
    {
        return $this->modelEducationClass
            ->where('related_id', EducationClass::CLASS_NOT_TEACHER)
            ->where('course_id', $course_id)->pluck('class_name', 'id');
    }

    public function getClassDetailId($class_id)
    {
        return $this->modelEducationClassShift
            ->where('class_id', $class_id)
            ->get();
    }

    public function insert($request)
    {
        if (!$request->detail_class_choose) {
            return true;
        }

        DB::beginTransaction();
        try {

            $request->merge([
                'employee_id' => auth()->user()->employee_id,
                'status' => EducationTeacher::STATUS_NEW // New
            ]);
            $lastId = $this->modelEducationTeacher->create($request->except(['detail_class_choose']))->id;
            foreach ($request->detail_class_choose as $value) {
                $value['education_teacher_id'] = $lastId;
                $this->modelEducationTeacherTime->create($value);
            }

            DB::commit();

            return $lastId;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public function listItem()
    {
        $pager = Config::getPagerData();
        $urlFilter = URL::route('education::education.teaching.teachings.index') . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        $arrEmployeeIds = $this->checkPermission('education::education.teaching.teachings.index');
        if (!$arrEmployeeIds && !Permission::getInstance()->isScopeCompany()) {
            $arrEmployeeIds = [Permission::getInstance()->getEmployee()->id];
        }
        $dataSearch['employee_id'] = $arrEmployeeIds;
        $collection = $this->searchForm($dataSearch)->orderBy('created_at', 'desc');

        return CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    public function checkPermission($router)
    {
        $employeeIds = null;
        $route = $router;
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $employeeIds = [];
        } else {// permission team or self profile.
            if (Permission::getInstance()->isScopeTeam(null, $route)) {
                $teamIdsAvailable = Permission::getInstance()->isScopeTeam(null, $route);
                // employee leader []
                $teamdIds = $this->getEmployeeIdFromTeamId($teamIdsAvailable);
                // select branch Code
                $employeeIds = array_column(TeamMember::whereIn('team_id', $teamdIds)->select('employee_id')->get()->toArray(), 'employee_id');
            } else {
                if(Permission::getInstance()->isScopeSelf(null, $route)) {
                    $employeeIds[] = Permission::getInstance()->getEmployee()->id;
                }
            }
        }

        return $employeeIds;
    }

    public function searchForm($dataSearch)
    {
        if (count($dataSearch['employee_id']) > 0) {
            $collection = $this->modelEducationTeacher->with(['educationCourses', 'user', 'employee'])->OfEmployeeIds($dataSearch['employee_id']);
        } else {
            $collection = $this->modelEducationTeacher->with(['educationCourses', 'user', 'employee']);
        }
        if (isset($dataSearch['scope'])) {
            $collection = $collection->OfScope($dataSearch['scope']);
        }
        if (isset($dataSearch['type'])) {
            $collection = $collection->OfType($dataSearch['type']);
        }
        if (isset($dataSearch['status'])) {
            $collection = $collection->OfStatus($dataSearch['status']);
        }

        if (isset($dataSearch['courses_id'])) {
            $collection = $collection->OfCoursesId($dataSearch['courses_id']);
        }
        if (isset($dataSearch['tranning_manage_id'])) {
            $collection = $collection->OfTranningManageId($dataSearch['tranning_manage_id']);
        }

        return $collection;
    }

    public function findEducationTeacher($id)
    {
        return $this->modelEducationTeacher
            ->with(['teacherTime', 'educationCourses', 'Classes'])
            ->where('id', $id)
            ->first();
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {
            $collection = $this->modelEducationTeacher->find($id);
            if ($collection->status == EducationTeacher::STATUS_NEW) {
                $request->merge([
                    'status' => EducationTeacher::STATUS_NEW // New
                ]);
            } else {
                $request->merge([
                    'status' => EducationTeacher::STATUS_UPDATE // update
                ]);
            }
            if ($collection) {
                $collection->fill($request->except(['detail_class_choose']));
                $collection->save();
            }
            $this->modelEducationTeacherTime->where('education_teacher_id', $id)->delete();

            foreach ($request->detail_class_choose as $value) {
                $value['education_teacher_id'] = $id;
                $this->modelEducationTeacherTime->create($value);
            }

            DB::commit();

            return;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public function listHrManaTeachings()
    {
        $pager = Config::getPagerData();
        $urlFilter = URL::route('education::education.teaching.hr.index') . '/';
        $dataSearch = CoreForm::getFilterData('search', null, $urlFilter);
        $arrEmployeeIds = $this->checkPermission('education::education.teaching.hr.index');
        $dataSearch['employee_id'] = $arrEmployeeIds;
        if (Permission::getInstance()->isScopeCompany(null, 'education::education.teaching.hr.index')) {
            $collection = $this->searchForm($dataSearch)->where('status','!=', EducationTeacher::STATUS_NEW)->orderBy('created_at', 'desc');
        } else {
            if (count($dataSearch) > 1) {
                $collection = $this->searchForm($dataSearch)->where('status','!=', EducationTeacher::STATUS_NEW)->orderBy('created_at', 'desc');
            } else {
                $collection = $this->searchForm($dataSearch)->where('status','!=', EducationTeacher::STATUS_NEW)->orWhere('tranning_manage_id', auth()->user()->employee_id)->orderBy('created_at', 'desc');
            }
        }

        return CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);
    }

    public function updateIdCurator($id)
    {
        $item = $this->modelEducationTeacher->find($id);
        $item->tranning_manage_id = auth()->user()->employee_id;

        return $item->save();
    }

    public function updateStatusReject($request, $id)
    {
        $request->merge([
            'status' => EducationTeacher::STATUS_REJECT // REJECT
        ]);
        $item = $this->modelEducationTeacher->find($id);
        $item->fill($request->except(['_method', '_token']));
        $item->save();

        $subject = ['subject' => Lang::get('education::view.[Register in teaching] Teaching registration is denied')];
        $data['global_subject'] = Lang::get('education::view.[Register in teaching] Teaching registration is denied');
        $data['global_view']    =  'education::template-mail.register_teaching_denied';
        $data['global_content'] =  $item->reject;
        $globalItem = Employee::select(['id', 'name', 'email'])->where('id', $item->employee_id)->get()->toArray();
        $dataHr = array_reduce($globalItem, function($carry, $item) use ($subject) {
            $carry[] = array_merge($item, $subject);
            return $carry;
        });
        $data['global_item'] = $dataHr;
        $data['global_link']    =  '';
        $patternsArr = [];
        $replacesArr = [];
        if (isset($data['global_item']) && !empty($data['global_item'])) {
            $this->pushNotificationAndEmail($data, $patternsArr, $replacesArr);
        }

        return true;
    }

    public function getEmployeeIdFromTeamId($teamIds = null) {
        $teamArr = Team::select(['id', 'name', 'parent_id', 'leader_id'])->get()->toArray();
        $childTeamIds = [];
        foreach ($teamIds as $teamId) {
            $recursiveArr = $this->helper->getTeamIdRecursive($teamArr, $teamId);
            $childTeamIds = array_merge($childTeamIds, $this->helper->getKeyArray($recursiveArr));
        }
        $teamIds = array_merge($childTeamIds, $teamIds);

        return array_unique($teamIds);
    }

    public function listUserAssignee($actionName, $teamIds = null, $isSelf = false)
    {
        $employee = Employee::getTableName();
        $teamMemTable = TeamMember::getTableName();
        $teamTable = Team::getTableName();
        $permTable = PerModel::getTableName();
        $action = Action::getTableName();
        $selectField = ["{$employee}.id", "{$employee}.name", "{$employee}.email"];
        $result = Employee::select($selectField)
            ->join("{$teamMemTable}", "{$teamMemTable}.employee_id", '=', "{$employee}.id")
            ->join("{$permTable}", "{$permTable}.role_id", '=', "{$teamMemTable}.role_id")
            ->join("{$action}", "{$action}.id", '=', "{$permTable}.action_id")
            ->where("{$action}.name", $actionName);
        if ($isSelf == null) {
            $result->where("{$permTable}.scope", '!=', \Rikkei\Team\Model\Permission::SCOPE_NONE);
        } elseif ($isSelf == true) {
            $result->where("{$permTable}.scope", '=', \Rikkei\Team\Model\Permission::SCOPE_SELF);
        } else {
            $result->where("{$permTable}.scope", '=', \Rikkei\Team\Model\Permission::SCOPE_TEAM);
        }
        if ($teamIds) {
            $result = $result->join("{$teamTable}", "{$teamTable}.id", '=', "{$teamMemTable}.team_id")
                ->whereIn("{$teamTable}.id", array_values($teamIds));
        }
        return $result->groupBy("id")
            ->get();
    }

    public function getCourseContent($id) {
        return $this->modelEducationCourses->find($id);
    }

    public function pushNotificationAndEmail(array $data, array $patternsArr, array $replacesArr) {
        try {
            $dataInsert = [];
            $receiverIds = [];
            $receiverEmails = [];
            $newReplaceArr = [];
            foreach ($replacesArr as $index) {
                if (array_key_exists($index, $data)) {
                    $newReplaceArr[] = $data[$index];
                }
            }
            $subject = preg_replace($patternsArr, $newReplaceArr, $data['global_subject']);
            $dataShort = $data;
            unset($dataShort['global_item']);

            foreach ($data['global_item'] as $item) {
                $receiverIds[] = isset($item['id']) ? $item['id'] : null;

                // Not send email when define email for employees
                if(isset($item['email']) && !empty($item['email'])) {
                    $receiverEmails[] = $item['email'];
                    $templateData = [
                        'data' => $data
                    ];
                    $emailQueue = new EmailQueue();
                    $emailQueue->setTo($item['email'], isset($item['name']) ? $item['name'] : substr($item['email'], 0, strpos($item['email'], '@')))
                        ->setSubject($subject)
                        ->setTemplate($data['global_view'], $templateData);
                    $dataInsert[] = $emailQueue->getValue();
                }
            }

            // Send notification
            \Rikkei\Notify\Facade\RkNotify::put(
                $receiverIds,
                $subject,
                $data['global_link'],
                ['actor_id' => null, 'icon' => 'reward.png']
            );

            EmailQueue::insert($dataInsert);

            return true;
        } catch (Exception $ex) {
            Log::info($ex);
        }

        return false;
    }

    public function getEmployeeWithScopeCompany() {
        $emailArr = SettingAddressMail::lists('email');
        $result = Employee::whereIn('email', $emailArr)->select(['id', 'name', 'email'])->get()->toArray();

        return $result;
    }

//    public function getEmployeeWithScopeDivision() {
//        $teams = Team::getTeamOfEmployee(auth()->user()->employee_id);
//        foreach ($teams as $item) {
//            $teamIds[] = [
//                $item->leader_id
//            ];
//        }
//
//        $result = Employee::whereIn('id', $teamIds)->select(['id', 'name', 'email'])->get()->toArray();
//
//        return $result;
//    }

    public function getEmployeeWithScopeBranch($scopeArr, $is_mail = false) {
        $selectField = ['id', 'name'];
        if ($is_mail) {
            $selectField = ['id', 'name', 'email'];
        }
        $branchCode = Team::whereIn('id', array_values($scopeArr))->groupBy('branch_code')->lists('branch_code');
        $idBranchCode = Team::whereIn('code', $branchCode)->lists('id');
        $branchMail = SettingAddressMail::whereIn('team_id', $idBranchCode)->lists('email');

        return Employee::whereIn('email', $branchMail)->select($selectField)->get()->toArray();
    }

    public function getEmployeeIdWithBranch($employeeArr, $teams) {
        $employee = Employee::getTableName();
        $teamMemTable = TeamMember::getTableName();
        $teamTable = Team::getTableName();
        $selectField = ["{$employee}.id", "{$employee}.name", "{$employee}.email"];
        $employeeIds = [];
        foreach ($employeeArr as $item) {
            $employeeIds[] = [
                $item->id
            ];
        }
        foreach ($teams as $item) {
            $branchCodes[] = [
                $item->branch_code
            ];
        }

        $result = Employee::select($selectField)
            ->join("{$teamMemTable}", "{$teamMemTable}.employee_id", '=', "{$employee}.id")
            ->join("{$teamTable}", "{$teamTable}.id", '=', "{$teamMemTable}.team_id")
            ->whereIn("{$teamMemTable}.employee_id", $employeeIds)
            ->whereIn("{$teamTable}.branch_code", $branchCodes)
            ->groupBy("email");
        return $result->get()->toArray();
    }

    public function getCourseId(array $config = [])
    {
        $result = [];
        $configDefault = [
            'page' => 1,
            'limit' => 10,
        ];
        $config = array_merge($configDefault, $config);
        if (!empty($config['query']) && isset($config['query'])) {
            $collection = EducationCourse::select('id', 'name')->where('name', 'LIKE', "%{$config['query']}%");
        }
//        EducationCourses::pagerCollection($collection, $config['limit'], $config['page']);
        CoreModel::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => $item->name
            ];
        }

        return $result;
    }

    public function send($request, $id, $api = false)
    {
        DB::beginTransaction();
        try {
            $collection = $this->modelEducationTeacher->find($id);
            $educationTeacherTime = EducationTeacherTime::where('education_teacher_id', $collection->id)->get()->toArray();
            $educationTeacherTimeRender = array_reduce($educationTeacherTime, function ($carry, $item) {
                $carry[] = $item['name'] . ': ' . $item['start_date'] . ' -> ' . $item['end_date'];
                return $carry;
            });
            $educationRequestService = new EducationRequestService();
            $divisionEmp = $educationRequestService->getDivision($collection->employee_id);
            $data['global_content'] = $collection->content;
            $data['global_time'] = $educationTeacherTimeRender;
            $data['global_team'] = $divisionEmp->division;
            $data['global_title'] = $collection->title;
            $data['global_name'] = $divisionEmp->employee_name;
            $data['global_link'] = '';
            $patternsArr = [];
            $replacesArr = [];
            if ($collection->tranning_manage_id) {
                $data['global_subject'] = Lang::get('education::view.[Register in teaching] [{{ title }}]');
                $data['global_view']    =  'education::template-mail.education_register_teaching_create';
                $globalItem = Employee::select(['id', 'name', 'email'])->where('id', $collection->tranning_manage_id)->get()->toArray();
                $data['global_item'] = $globalItem;
                $patternsArr = ['/\{\{\stitle\s\}\}/'];
                $replacesArr = ['global_title'];
            } else {
//                $teamPermission = new TeamPermission();
                $globalItem = array();
                $teamIds = array();
                $employeeId = $api ? $request->get('employee_id') : auth()->user()->employee_id;
                $teams = Team::getTeamOfEmployee($employeeId);
                foreach ($teams as $item) {
                    $teamIds[] = $item->id;
                }
                $branchCode = $teams->pluck('branch_code')->toArray();
                $idBranchCode = Team::whereIn('code', array_filter($branchCode))->lists('id');
                $branchMail = SettingAddressMail::whereIn('team_id', $idBranchCode)->lists('email')->toArray();
                $listGroupMails = array();
                foreach($branchMail as $mail) {
                    $listGroupMails[] = ['email' => $mail];
                }
                $data['global_subject'] = Lang::get('education::view.[Register in teaching] There is a requirement to register for teaching');
                $data['global_view'] = 'education::template-mail.education_register_teaching_create';
                // Check scope company
                if ($collection->scope == EducationTeacher::SCOPE_COMPANY) {
                    $globalItem = $this->getEmployeeWithScopeCompany();
                    $teamIds = array();
                }

                // Check scope branch
                if ($collection->scope == EducationTeacher::SCOPE_BRANCH) {
                    $globalItem = $this->getEmployeeWithScopeBranch($teamIds, true);
                }

                // Check scope division
                if ($collection->scope == EducationTeacher::SCOPE_DIVISION) {
                    $isScopeSelf = true;
                    // Check exists admin of division
                    $globalItem = $this->listUserAssignee(EducationTeacher::ROUTER_REGISTER_HR, $teamIds, $isScopeSelf)->toArray();
                }
//                $allEmailHasPermission = $teamPermission->getEmployeeByActionName(EducationTeacher::ROUTER_REGISTER_HR, $teamIds)->get();
//                if (count($allEmailHasPermission)) {
//                    $globalItem = array_merge($globalItem, $allEmailHasPermission->toArray());
//                }
                $data['global_item'] = array_unique(array_merge($globalItem, $listGroupMails), SORT_REGULAR);
            }

            if (isset($data['global_item']) && !empty($data['global_item'])) {
                \Log::info($data['global_item']);
                $this->pushNotificationAndEmail($data, $patternsArr, $replacesArr);
            }
            $collection->status = EducationTeacher::STATUS_SEND;
            $collection->save();
            DB::commit();

            return;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }
}
