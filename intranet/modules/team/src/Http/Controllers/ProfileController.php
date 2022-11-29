<?php

namespace Rikkei\Team\Http\Controllers;

use Carbon\Carbon;
use Excel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config as SupportConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View as ViewLaravel;
use PHPExcel_Worksheet_Drawing;
use PHPExcel_Worksheet_MemoryDrawing;
use PHPMailer;
use Rikkei\Contract\Model\ContractModel;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\User;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CacheBase;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\PublishQueueToJob;
use Rikkei\Core\View\ValidatorExtendCore;
use Rikkei\Core\View\View;
use Rikkei\ManageTime\Model\Timekeeping;
use Rikkei\ManageTime\Model\TimekeepingAggregate;
// use Rikkei\ManageTime\Model\TimekeepingAggregateSalaryRate;
use Rikkei\ManageTime\Model\TimekeepingTable;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\getOptions;
use Rikkei\Tag\Model\Tag;
use Rikkei\Team\Model\Certificate;
use Rikkei\Team\Model\Country;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Team\Model\EmplProjExperTag;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\EmployeeAttach;
use Rikkei\Team\Model\EmployeeAttachFile;
use Rikkei\Team\Model\EmployeeBusiness;
use Rikkei\Team\Model\EmployeeCertificate;
use Rikkei\Team\Model\EmployeeCertificateImage;
use Rikkei\Team\Model\EmployeeComexper;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Team\Model\EmployeeContractHistory;
use Rikkei\Team\Model\EmployeeCostume;
use Rikkei\Team\Model\EmployeeEducation;
use Rikkei\Team\Model\EmployeeHealth;
use Rikkei\Team\Model\EmployeeMilitary;
use Rikkei\Team\Model\EmployeePrize;
use Rikkei\Team\Model\EmployeeProjExper;
use Rikkei\Team\Model\EmployeeRelationship;
use Rikkei\Team\Model\EmployeeSchool;
use Rikkei\Team\Model\EmployeeSetting;
use Rikkei\Team\Model\EmployeeSkill;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\EmployeeWantOnsite;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\Faculty;
use Rikkei\Team\Model\Major;
use Rikkei\Team\Model\MilitaryArm;
use Rikkei\Team\Model\MilitaryPosition;
use Rikkei\Team\Model\MilitaryRank;
use Rikkei\Team\Model\PartyPosition;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\QualityEducation;
use Rikkei\Team\Model\RelationNames;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\School;
use Rikkei\Team\Model\Skill;
use Rikkei\Team\Model\SkillSheetComment;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\UnionPosition;
use Rikkei\Team\View\ExportCv;
use Rikkei\Team\View\Permission as PermissionView;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\ProfileImportHelper;
use Rikkei\Team\View\TeamList;
use Rikkei\Welfare\Model\Event;
use Rikkei\ManageTime\View\ManageTimeCommon;
use Rikkei\ManageTime\Model\ManageTimeSettingUser;
use Rikkei\Team\Model\EmployeeRole;

class ProfileController extends Controller
{

    protected $employee;
    protected $isScopeCompany = false;
    protected $isScopeTeam = false;
    protected $isScopeCompanyView = false;
    protected $isScopeTeamView = false;
    protected $user;
    protected $isSelfProfile = false;

    /**
     * construct more
     */
    public function __construct()
    {
        parent::__construct();
        Menu::setActive('profile');
    }

    /**
     * pre process action
     *
     * @param int $employeeId
     * @return boolean
     */
    public function preExec(
        $employeeId = null, $isCheckView = true, $isCheckEditTeam = false, $routeAccess = 'team::member.profile.save'
    )
    {
        if ($employeeId) {
            $this->user = User::where('employee_id', $employeeId)->first();
            $this->employee = Employee::find($employeeId);
            if (!$this->employee) {
                return false;
            }
            $currentEmpoyee = Permission::getInstance()->getEmployee();
            if ($currentEmpoyee->id == $this->employee->id) {
                $this->isSelfProfile = true;
            }
        } else {
            $this->user = Auth::user();
            $this->employee = Permission::getInstance()->getEmployee();
            $this->isSelfProfile = true;
        }
        if (!$this->employee) {
            return false;
        }
        if (!$this->user) {
            $this->user = new User();
        }
        if ($isCheckView) {
            $this->preExecViewAccess($employeeId);
        }
        $this->isScopeCompany = Permission::getInstance()
            ->isScopeCompany(null, $routeAccess);
        if ($this->isScopeCompany) {
            $this->isScopeTeam = true;
            return true;
        }
        if (!$isCheckEditTeam) {
            return true;
        }
        $this->preAccessTeam($employeeId, $routeAccess);
    }

    /**
     * check access view profile
     *
     * @param int $employeeId
     * @return boolean
     */
    protected function preExecViewAccess($employeeId)
    {
        $this->isScopeCompanyView = Permission::getInstance()
            ->isScopeCompany(null, 'team::member.profile.index');
        if ($this->isScopeCompanyView) {
            $this->isScopeTeamView = true;
            return true;
        }

        $teamAuth = Permission::getInstance()->getTeams();
        $teamTree = Team::getTeamPathTree();
        $scopeTeamView = Permission::getInstance()->isScopeTeam(null, 'team::member.profile.index');
        $result = [];
        if (!is_array($scopeTeamView)) {
            foreach ($teamAuth as $itemId) {
                $result[] = $itemId;
                if (isset($teamTree[$itemId]['child']) && $teamTree[$itemId]['child']) {
                    $result = array_merge($teamTree[$itemId]['child'], $result);
                }
            }
        } else {
            $result = $scopeTeamView;
        }

        // Check team's permission of employee
        $flagScopeTeam = $scopeTeamView && TeamMember::isEmployeeOfTeam($employeeId, $result);

        // get list array team_id of employee profile.
        $listTeamIdOfEmp = Team::getListTeamOfEmp($employeeId);
        $teamIdOfEmp = [];
        foreach ($listTeamIdOfEmp as $listTeam) {
            $teamIdOfEmp[] = $listTeam->id;
        }

        // Check pqa's permission
        $flagPqa = false;
        if (Permission::getInstance()->isScopeCompany(null, 'team::member.profile.index')) {
            $flagPqa = true;
        } else if (Permission::getInstance()->isScopeTeam(null, 'team::member.profile.index')) {
            $curEmp = Permission::getInstance()->getEmployee();
            $listTeamIdResponsibleOfCurrEmp = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if ($listTeamIdResponsibleOfCurrEmp) {
                foreach ($listTeamIdResponsibleOfCurrEmp as $listTeam) {
                    if (in_array($listTeam->team_id, $teamIdOfEmp)) {
                        $flagPqa = true;
                        break;
                    }
                }
            }
        } else {
            $flagPqa = false;
        }

        $this->isScopeTeamView = $flagScopeTeam || $flagPqa;
    }

    /**
     * is access team edit
     *
     * @param int $employeeId
     * @param string $routeAccess
     * @return boolean
     */
    protected function preAccessTeam($employeeId, $routeAccess = 'team::member.profile.save')
    {
        $teamIds = Permission::getInstance()->isScopeTeam(null, $routeAccess);
        $this->isScopeTeam = (bool)$teamIds;
        if (!$this->isScopeTeam) {
            return true;
        }
        if (!is_array($teamIds)) {
            $teamAuth = Permission::getInstance()->getTeams();
        } else {
            $teamAuth = $teamIds;
        }
        $this->isScopeTeam = TeamMember::isEmployeeOfTeam($employeeId, $teamAuth);
    }

    /**
     * getEmployee followId [$id = 0| null => current employee]
     *
     * @param int $id
     * @return Employee
     */
    /* protected function getEmployee($id = null)
      {
      if($id) {
      return Employee::where('working_type', '!=', getOptions::WORKING_INTERNSHIP)->find($id);
      } else {
      return $model = Permission::getInstance()->getEmployee();
      }
      } */

    /**
     * create profile view
     */
    public function create()
    {
        if (!Permission::getInstance()->isScopeCompany(null, 'team::member.profile.save')) {
            View::viewErrorPermission();
        }
        Menu::setActive('hr', '/');
        Breadcrumb::add(
            Lang::get('team::view.Create profile new'), URL::route('team::member.profile.create')
        );
        $emmployee = new Employee();
        $emmployee->id = 0;
        return view('team::member.edit.profile_base', [
            'employeeModelItem' => $emmployee,
            'isScopeCompany' => true,
            'isScopeTeam' => true,
            'teamScope' => false,
            'userItem' => new User(),
            'tabType' => 'base',
            'tabTitle' => trans('team::view.Personal Information'),
            'tabTitleIcon' => 'fa fa-user',
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => false,
            'isAccessDeleteEmployee' => false,
            'postionsOption' => Role::toOptionPosition(),
            'teamsOption' => TeamList::toOption(null, true, false),
            'rolesOption' => Role::getAllRole(),
            'employeeTeamPositions' => null,
            'employeeRoles' => null,
            'isCompanyDisableInput' => '',
            'teamsBranch' => Team::getListBranchMainTeam(),
            'listTeamJP' => [],
        ]);
    }

    /**
     * view/edit profile base
     */
    public function profile($employeeId = null, $type = null, $typeId = null)
    {
        if ($employeeId && !is_numeric($employeeId)) {
            $type = $employeeId;
            $employeeId = null;
        }
        $this->preExec($employeeId);
        if ($this->isSelfProfile) {
            Breadcrumb::add('My Profile', URL::route('team::member.profile.index', ['employeeId' => $employeeId]));
        } else {
            Breadcrumb::add('Profile', URL::route('team::member.profile.index', ['employeeId' => $employeeId]));
        }

        if (!$this->employee) {
            return redirect()
                ->route('team::team.member.index')
                ->withErrors(Lang::get('team::messages.Not found item.'));
        }

        $checkRole = EmployeeRole::hasRole(Role::ROLE_VIEW_PROFILE_2_NAME);
        if ($checkRole) {
            $empCheck = Employee::checkStatusEmployeeIsWorking($this->employee->email);
            if (!$empCheck) {
                View::viewErrorPermission();
            }
        }

        if (!$this->isScopeCompanyView &&
            !$this->isScopeTeamView &&
            !$this->isSelfProfile
        ) {
            if ($type === 'cv') {
                return $this->cv();
            }
            if ($type == 'certificate') {
                if (!Permission::getInstance()->isScopeTeam(null, 'team::member.profile.save')) {
                    View::viewErrorPermission();
                } else {
                    if (!empty($typeId)) {
                        return $this->editItemcertificate($typeId);
                    } else {
                        return $this->certificate();
                    }
                }
            }
            View::viewErrorPermission();
        }

        if ($typeId) {
            if ($typeId === 'create') {
                if ($type == 'certificate') {
                    View::viewErrorPermission();
                }
                $method = 'createItem' . ucfirst($type);
            } else {
                $method = 'editItem' . ucfirst($type);
            }
        } else {
            $method = $type;
        }
        if (!method_exists($this, $method)) {
            return $this->base();
        }
        return $this->{$method}($typeId);
    }

    /**
     * base view
     */
    public function base()
    {
        $listTeamId = [];
        $listTeamIdResponsible = PqaResponsibleTeam::getListTeamIdResponsibleTeam($this->employee->id);
        if ($listTeamIdResponsible) {
            foreach ($listTeamIdResponsible as $list) {
                $listTeamId[] = $list->team_id;
            }
        }

        $teamIdOfEmp = Team::getListTeamOfEmp($this->employee->id);
        $arrayTeamIdOfEmp = [];
        $listTeamJP = Team::where('branch_code', Team::CODE_PREFIX_JP)->pluck('id')->toArray();
        $isJP = array_intersect($teamIdOfEmp->pluck('id')->toArray(), $listTeamJP) ? 1 : 0;
        foreach ($teamIdOfEmp as $team) {
            $arrayTeamIdOfEmp[] = $team->id;
        }

        // session require team if not change team in profile base
        session(['requireTeam' => $teamIdOfEmp ? '0' : '1']);

        $route = $this->isScopeCompany ?
            route('help::display.help.view', ['id' => 'profile-phan-cho-hcth-nhan-su-thong-tin-chung']) :
            route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-chung']);
        $teamScope = Permission::getInstance()->isScopeTeam(null, 'team::member.profile.save');
        $isScopeTeam = count($teamScope);

        return view('team::member.edit.profile_base', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'isScopeTeam' => $isScopeTeam,
            'teamScope' => $teamScope,
            'isCompanyDisableInput' => $this->isScopeCompany || $isScopeTeam ? '' : ' disabled',
            'userItem' => $this->user,
            'tabType' => 'base',
            'tabTitle' => trans('team::view.Personal Information'),
            'helpLink' => $route,
            'isAccessSubmitForm' => $this->isSelfProfile || $this->isScopeCompany,
            'disabledInput' => $this->isSelfProfile || $this->isScopeCompany ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'isAccessDeleteEmployee' => Permission::getInstance()->isScopeCompany(null, 'team::team.member.delete'),
            'postionsOption' => Role::toOptionPosition(),
            'teamsOption' => TeamList::toOption(null, true, false, null, true),
            'rolesOption' => Role::getAllRole(),
            'employeeTeamPositions' => EmployeeTeamHistory::getTeamHistory($this->employee->id),
            'employeeRoles' => $this->employee->getRoles(),
            'listTeamId' => $listTeamId,
            'arrayTeamIdOfEmp' => $arrayTeamIdOfEmp,
            'listTeamJP' => $listTeamJP,
            'isJP' => $isJP,
        ]);
    }

    /**
     * save profile
     */
    public function save($employeeId = null, $type = null, $typeId = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        DB::beginTransaction();
        try {
            if (!$employeeId) {
                $this->isScopeCompany = true;
                DB::commit();
                $result = $this->saveBase(true);
                if (isset($result['employee_id']) && $result['employee_id'] > 0) {
                    PublishQueueToJob::makeInstance()->cacheRole($result['employee_id']);
                }
                return $result;
            }
            $this->preExec($employeeId, false, true);
            if (!$this->employee) {
                $response['status'] = 1;
                $response['redirect'] = route('team::team.member.index');
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($typeId || $typeId === '0') {
                $method = 'saveItem' . ucfirst($type);
            } else {
                $method = 'save' . ucfirst($type);
            }
            if (!method_exists($this, $method)) {
                $result = $this->saveBase();
            } else {
                $result = $this->{$method}($typeId);
            }
            DB::commit();
            PublishQueueToJob::makeInstance()->cacheRole($employeeId);
            CacheBase::forget(EmployeeTeamHistory::KEY_CACHE_API_HRM_PROFILE_EMPLOYEE_IDS);
            CacheBase::forget(EmployeeTeamHistory::KEY_CACHE_API_HRM_PROFILE_EMPLOYEES);
            return $result;
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            DB::rollback();
            Log::error($ex);
            return $response;
        }
    }

    /**
     * delete item relate of employee
     *
     * @param int $employeeId
     * @param string $type
     * @param int $itemId
     */
    public function deleteItemRelate($employeeId, $type, $itemId = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        if (!$employeeId) {
            $response['status'] = 0;
            $response['redirect'] = route('team::team.member.index');
            $response['message'] = trans('team::messages.Not found item.');
            return $response;
        }
        $this->preExec($employeeId, false, true);
        if (!$this->employee) {
            $response['status'] = 0;
            $response['redirect'] = route('team::team.member.index');
            $response['message'] = trans('team::messages.Not found item.');
            return $response;
        }
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        switch ($type) {
            case 'experience':
                $typeClass = 'ProjExper';
                break;
            case 'doc':
                $typeClass = 'DocExpire';
                break;
            case 'wonsite':
                $typeClass = 'WantOnsite';
                break;
            default:
                $typeClass = $type;
                break;
        }
        $class = 'Rikkei\Team\Model\Employee' . ucfirst($typeClass);
        if (!class_exists($class)) {
            $response['status'] = 0;
            $response['message'] = trans('team::messages.Not found item.');
            return $response;
        }
        if (!$itemId) {
            $itemId = Input::get('id');
        }
        $model = $class::find($itemId);
        if (!$model) {
            $response['status'] = 0;
            $response['message'] = trans('team::messages.Not found item.');
            return $response;
        }
        if ($model->employee_id != $this->employee->id) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        DB::beginTransaction();
        try {
            if ($type == 'certificate') {
                $items = EmployeeCertificateImage::where('employee_certies_id', $model->id)->get();
                if (!empty($items->toArray())) {
                    foreach ($items as $item) {
                        $this->deleteImage($item->image);
                        EmployeeCertificateImage::where('id', $item->id)->delete();
                    }
                }
            }
            $model->delete();
            $response['status'] = 1;
            $response['redirect'] = route('team::member.profile.index', [
                'employeeId' => $this->employee->id,
                'type' => $type
            ]);
            Session::flash(
                'messages', [
                    'success' => [
                        trans('team::messages.Delete item success!'),
                    ]
                ]
            );
            DB::commit();
            return $response;
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            DB::rollback();
            Log::error($ex);
            return $response;
        }
    }

    /**
     * save profile
     */
    public function deleteItem2($employeeId = null, $type = null, $typeId = null, $itemId = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        if (!$employeeId) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $this->preExec($employeeId, false, true);
        if (!$this->employee) {
            $response['status'] = 1;
            $response['redirect'] = route('team::team.member.index');
            $response['message'] = trans('team::messages.Not found item.');
            return $response;
        }
        if (!$typeId || !$itemId) {
            $response['status'] = 1;
            $response['message'] = trans('team::messages.Not found item.');
            return $response;
        }
        $method = 'deleteItem2' . ucfirst($type);
        if (!method_exists($this, $method)) {
            $response['status'] = 1;
            $response['message'] = trans('core::message.Error system');
            return $response;
        }
        DB::beginTransaction();
        try {
            $result = $this->{$method}($typeId, $itemId);
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            DB::rollback();
            Log::error($ex);
            return $response;
        }
    }

    /**
     * save base info
     */
    public function saveBase($isCreator = false, $isCv = false, $dataEmployee = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile && !$isCv) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        if ($isCreator) {
            $this->employee = new Employee();
        }
        if (!$dataEmployee) {
            $dataEmployee = (array)Input::get('employee');
        }
        $rules = [
            'name' => 'required|max:45',
            'birthday' => 'date',
            'id_card_number' => 'string|max:255|unique:employees,id_card_number,'
                . $this->employee->id . ',id,deleted_at,NULL',
            'id_card_date' => 'date',
            'id_card_place' => 'string|max:255',
            'passport_number' => 'string|max:50|unique:employees,passport_number,'
                . $this->employee->id . ',id,deleted_at,NULL',
            'passport_date_start' => 'date',
            'passport_date_exprie' => 'date',
            'passport_addr' => 'string|max:255',
            'japanese_name' => 'string|max:255',
            'email' => 'email|max:100|email_rk|unique:employees,email,'
                . $this->employee->id . ',id',
        ];
        if ($isCv || !$this->isScopeCompany) {
            unset($rules['name']);
        }
        if (!empty($dataEmployee)) {
            $status = EmplCvAttrValue::getValueSingleAttr($this->employee->id, 'status');
            $accessView = $this->accessViewSS($status);
            // $accessApprover = $accessView['approver'];
            $accessEdit = $this->accessEditSS($status, $accessView);
            $curEmp = Permission::getInstance()->getEmployee();
            if ($accessEdit['edit'] || TeamMember::isLeaderOrSubleader($curEmp->id, $this->employee->id) || $this->isScopeCompany || $this->isSelfProfile) {
                ValidatorExtendCore::addEmailRK();
                $validator = Validator::make($dataEmployee, $rules, [
                    'employee_code.unique' => trans('validation.The :attribute has already been taken.', ['attribute' => trans('team::view.Employee code')]),
                    'employee_card_id.unique' => trans('validation.The :attribute has already been taken.', ['attribute' => trans('team::view.Employee card id')]),
                    'email.unique' => trans('validation.The :attribute has already been taken.', ['attribute' => 'Email']),
                    'email.email_rk' => trans('team::messages.Please enter email of Rikkeisoft'),
                    'id_card_number.unique' => trans('validation.The :attribute has already been taken.', ['attribute' => trans('team::view.Identity card number')]),
                    'passport_number.unique' => trans('validation.The :attribute has already been taken.', ['attribute' => trans('team::view.Passport')]),
                ]);
                if ($validator->fails()) {
                    $response['status'] = 0;
                    $response['message'] = $validator->errors()->all();
                    return $response;
                }
                Form::filterEmptyValue($dataEmployee, [
                    'birthday',
                    'id_card_date',
                    'passport_date_start',
                    'passport_date_exprie',
                ]);
                if ($this->isScopeCompany) {
                    $this->employee->setData($dataEmployee);
                } else {
                    if (isset($dataEmployee['japanese_name'])) {
                        $this->employee->japanese_name = $dataEmployee['japanese_name'];
                    }
                }
                $this->employee->save();
                //not create file mail password, create when running cronjob
                /*if ($isCreator) {
                    $keyPassFile = EmployeeSetting::KEY_PASS_FILE;
                    EmployeeSetting::create([
                        'is_current' => 1,
                        'employee_id' => $this->employee->id,
                        'key' => $keyPassFile,
                        'value' => encrypt(str_random(8))
                    ]);
                }*/
            }
        }

        // update team or role
        if (!$isCv && $this->isScopeCompany) {
            // change team or employee has not a team
            if (Input::get('is_change_team') === '1' || session('requireTeam') !== '0') {
                $this->employee->saveTeamPosition();

                $responsibleTeam = (array)Input::get('team-responsible');
                $listTeamId = [];
                $teamPQA = [];
                if (!empty($responsibleTeam)) {
                    // get list team_id of employee profile.
                    $listTeamId = Team::getListTeamOfEmp($this->employee->id)->pluck('id')->toArray();
                    $teamPQA = Team::getTeamPQAByType()->pluck('id')->toArray();
                }
                if (!empty($responsibleTeam) && array_intersect($teamPQA, $listTeamId)) {
                    // get employee responsible team follow team check and check condition a team responsible by 1 employee.
//                    $employeeIdReponsibleCheck = PqaResponsibleTeam::getEmpIdResponsibleTeam($responsibleTeam);
//                    if ($employeeIdReponsibleCheck) {
//                        foreach ($employeeIdReponsibleCheck as $item) {
//                            if ($this->employee->id != $item->employee_id) {
//                                $emp = Employee::where('id', $item->employee_id)->where(function ($q) {
//                                    $q->orWhere('leave_date', '!=', null)->where('leave_date', '<=', Carbon::today());
//                                })->first();
//                                if (!$emp) {
//                                    $namePQAResonsibleTeamCheck = Employee::getNameEmpById($item->employee_id);
//                                    $response['message'] = Lang::get('team::messages.The team has pqa in responsible', ['name' => $namePQAResonsibleTeamCheck]);
//                                    $response['popup'] = 1;
//                                    return response()->json($response);
//                                }
//                            }
//                        }
//                    }
                    DB::beginTransaction();
                    try {
                        //delete all team position of employee before insert new
                        PqaResponsibleTeam::where('employee_id', $this->employee->id)->where('type', PqaResponsibleTeam::TYPE_REVIEWED)->delete();
                        foreach ($responsibleTeam as $item) {
                            $pqaResponsibleTeam[] = [
                                'team_id' => $item,
                                'type' => PqaResponsibleTeam::TYPE_REVIEWED,
                                'employee_id' => $this->employee->id,
                                'created_at' => Carbon::now()->__toString(),
                            ];
                        }
                        PqaResponsibleTeam::insert($pqaResponsibleTeam);
                        DB::commit();
                    } catch (Exception $ex) {
                        DB::rollback();
                        throw $ex;
                    }
                } else {
                    PqaResponsibleTeam::where('employee_id', $this->employee->id)->where('type', PqaResponsibleTeam::TYPE_REVIEWED)->delete();
                }
            }
            if (Input::get('is_change_role') === '1') {
                $this->employee->saveRoles();
            }
        }

        // upload avatar
        $resulUpload = User::uploadAvatar(Input::file('avatar_url'), $this->employee);
        if (!$resulUpload['status']) {
            $response['message'][] = $resulUpload['message'];
        } elseif ($resulUpload['filePath']) {
            $response['avatar_url'] = $resulUpload['filePath'];
        } else {
            // no thing
        }

        $response['message'][] = trans('core::message.Save success');
        $response['status'] = 1;
        if ($isCreator) {
            $response['employee_id'] = $this->employee->id;
        }

        //Remove cache Team code prefix
        CacheHelper::forget(Team::CACHE_TEAM_CODE_PREFIX, $this->employee->id);
        CacheHelper::forget(CacheHelper::CACHE_TIME_SETTING_PREFIX, $this->employee->id);
        CacheHelper::forget(CacheHelper::CACHE_TIME_QUATER, $this->employee->id);

        return $response;
    }

    /**
     * delete employee
     */
    public function delete($id)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        if (!$id) {
            $response['status'] = 0;
            $response['message'] = trans('team::messages.Not found item.');
            return response()->json($response);
        }
        $this->preExec($id, false, false, 'team::team.member.delete');
        if (!$this->employee) {
            $response['status'] = 0;
            $response['message'] = trans('team::messages.Not found item.');
            return response()->json($response);
        }
        if (!$this->isScopeCompany) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        try {
            if ($this->user && $this->user->id) {
                $this->user->delete();
            }
            $this->employee->delete();

            //Send mail
            $teamMember = TeamMember::with('getTeam')->where('employee_id', $id)->first();
            $branch = isset($teamMember->getTeam->branch_code) ? $teamMember->getTeam->branch_code : '';
            if ($branch) {
                //Data email
                $employeeRelativeItem = $this->employee->getItemRelate('work');
                if ($employeeRelativeItem) {
                    $optionsWorkContract = EmployeeWork::getAllTypeContract();
                    $contractType = in_array($employeeRelativeItem->contract_type, array_keys($optionsWorkContract)) ? $optionsWorkContract[$employeeRelativeItem->contract_type] : '';
                }
                $data['mail_title'] = Lang::get('manage_time::view.[Rikkei Intranet] Employee :name has just been removed from the rikkei.vn system', ['name' => $this->employee->name]);
                $data['name'] = $this->employee->name;
                $data['employee_code'] = $this->employee->employee_code;
                $data['employee_email'] = $this->employee->email;
                $data['contract_type'] = $contractType ? $contractType : '';
                $template = 'manage_time::template.mail_timekeeping_management';
                $persons = ManageTimeSettingUser::where('branch', $branch)->get();
                foreach ($persons as $key => $person) {
                    $data['admin_name'] = $person->employee_name;
                    $data['mail_to'] = $person->employee_email;
                    ManageTimeCommon::pushEmailToQueue($data, $template);
                }
            }

            PqaResponsibleTeam::where('employee_id', $this->employee->id)->delete();
            $this->deleteEmployeeTimkeeping([$this->employee->id]);
            $response['status'] = 1;
            $response['redirect'] = route('team::team.member.index');
            Session::flash(
                'messages', [
                    'success' => [
                        trans('team::messages.Delete item success!'),
                    ]
                ]
            );
            return response()->json($response);
        } catch (Exception $ex) {
            Log::info($ex);
            $response['status'] = 0;
            $response['message'] = trans('core::message.Error system');
            return response()->json($response);
        }
    }

    /**
     * show information workInfo
     */
    public function work()
    {
        $route = $this->isScopeCompany ?
            route('help::display.help.view', ['id' => 'profile-phan-cho-hcth-nhan-su-thong-tin-cong-viec']) :
            route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-cong-viec']);
        return view('team::member.edit.profile_work_info', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'work',
            'tabTitle' => trans('team::profile.Work Info'),
            'helpLink' => $route,
            'isAccessSubmitForm' => $this->isScopeCompany,
            'disabledInput' => $this->isScopeCompany ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeRelativeItem' => $this->employee->getItemRelate('work'),
            'optionsWorkContract' => EmployeeWork::getAllTypeContract(),
        ]);
    }

    /**
     * save work info
     */
    public function saveWork()
    {
        $response = [];
        if (!$this->isScopeCompany) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        ValidatorExtendCore::addEmailRK();
        $dataEmployee = Input::get('employee');
        $dataWork = Input::get('e_w');
        $validator = Validator::make($dataEmployee, [
            'employee_code' => 'required|unique:employees,employee_code,'
                . $this->employee->id . ',id,deleted_at,NULL',
            'employee_card_id' => 'numeric|digits_between:1,10',
            //|unique:employees,employee_card_id,'. $this->employee->id . ',id,deleted_at,NULL',
            'email' => 'required|email|max:100|email_rk|unique:employees,email,'
                . $this->employee->id . ',id,deleted_at,NULL',
            'join_date' => 'required|date',
            'offcial_date' => 'date',
            'trial_date' => 'date',
            'leave_date' => 'date',
            'trial_end_date' => 'date',
        ], [
            'employee_code.unique' => trans('validation.The :attribute has already been taken.', ['attribute' => trans('team::view.Employee code')]),
            'employee_card_id.unique' => trans('validation.The :attribute has already been taken.', ['attribute' => trans('team::view.Employee card id')]),
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        Form::filterEmptyValue($dataEmployee, [
            'leave_date',
            'join_date',
            'offcial_date',
            'trial_date',
            'trial_end_date',
            'employee_card_id',
        ]);
        Form::filterEmptyValue($dataWork, [
            'insurrance_h_date',
            'insurrance_h_expire',
            'insurrance_ratio'
        ]);
        if (isset($dataWork['contract_type'])) {
            $dataEmployee['working_type'] = $dataWork['contract_type'];
        }

        // status of candidate is preparing => not update
        $candidate = Candidate::getCandidateByEmployee($this->employee->id);
        if ($candidate && (int)$candidate->status === getOptions::PREPARING) {
            $response['message'] = trans('team::messages.You need to update the status of the candidate is working');
            $response['status'] = 0;
            return $response;
        }
        // old data
        $oldEmployee = clone $this->employee;

        $email['email'] = $dataEmployee['email'];
        //check email exists in User table
        if (User::where('email', $email['email'])->where('employee_id', '!=', $this->employee->id)->first()) {
            $response['message'] = trans('validation.The :attribute has already been taken.', ['attribute' => 'Email']);
        } else {
            $this->employee->setData($dataEmployee)->save();
            $email['employee_id'] = $this->employee->id;
            $this->user->setData($email)->save();
            // save more work
            $relative = $this->employee->getItemRelate('work');
            // check save contract history
            $newEmployee = $this->employee;
            $newEmployee->contract_type = $dataWork['contract_type'];
            $oldEmployee->contract_type = $relative->contract_type;
            EmployeeContractHistory::insertItem($newEmployee, $oldEmployee);

            $relative->setData($dataWork);
            $relative->employee_id = $this->employee->id;
            $relative->save();
            $response['message'] = trans('core::message.Save success');
            $response['status'] = 1;
        }

        return $response;
    }

    /**
     *
     * @return type
     */
    public function contractHistory()
    {

        $contractHistories = EmployeeContractHistory::getByEmp($this->employee->id);
        return view('team::member.contract.history', ['contractHistories' => $contractHistories])->render();
    }

    /**
     * Contact Info page
     */
    public function contact()
    {
        return view('team::member.edit.profile_contact', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'contact',
            'tabTitle' => trans('team::view.Personal Contact'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-lien-he']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'relationOptions' => RelationNames::getAllRelations(),
            'employeeContact' => $this->employee->getItemRelate('contact'),
            'libCountry' => Country::getAll(),
        ]);
    }

    /**
     * save contact
     */
    public function saveContact()
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $dataEmployee = (array)Input::get('employeeContact');
        $validator = Validator::make($dataEmployee, [
            'mobile_phone' => 'required|max:20',
            'office_phone' => 'max:20',
            'home_phone' => 'max:20',
            'other_phone' => 'max:20',
            'personal_email' => 'email|max:100',
            'other_email' => 'email|max:100',
            'yahoo' => 'max:100',
            'skype' => 'max:100',
            'facebook' => 'string|max:100',
            'native_addr' => 'max:255',
            'native_province' => 'max:100',
            'native_district' => 'max:100',
            'native_ward' => 'max:100',
            'tempo_addr' => 'max:255',
            'tempo_province' => 'max:100',
            'tempo_district' => 'max:100',
            'emergency_contact_name' => 'max:100',
            'emergency_mobile' => 'max:20',
            'emergency_contact_mobile' => 'max:20',
            'emergency_addr' => 'max:255',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        $contact = $this->employee->getItemRelate('contact');
        $contact->setData($dataEmployee);
        $contact->employee_id = $this->employee->id;
        $contact->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        return $response;
    }

    /**
     * Health Info page
     */
    public function health()
    {
        return view('team::member.edit.profile_health', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'health',
            'tabTitle' => trans('team::profile.Current health info'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'bloodTypesOption' => EmployeeHealth::toOptionBloodType(),
            'employeeHealth' => $this->employee->getItemRelate('health'),
        ]);
    }

    /**
     * save contact
     */
    public function saveHealth()
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $dataEmployee = (array)Input::get('employeeHealth');
        $validator = Validator::make($dataEmployee, [
            'height' => 'numeric',
            'weigth' => 'numeric',
            'health_status' => 'max:255',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        $itemRelate = $this->employee->getItemRelate('health');
        if (!isset($dataEmployee['is_disabled'])) {
            $dataEmployee['is_disabled'] = 0;
        }
        $itemRelate->setData($dataEmployee);
        $itemRelate->employee_id = $this->employee->id;
        $itemRelate->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        return $response;
    }

    /**
     * hobby page
     */
    public function hobby()
    {
        return view('team::member.edit.profile_hobby', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'hobby',
            'tabTitle' => trans('team::profile.Hobby Info'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeHobby' => $this->employee->getItemRelate('hobby'),
        ]);
    }

    /**
     * save hobby
     */
    public function saveHobby()
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $dataEmployee = (array)Input::get('employeeHobby');
        $validator = Validator::make($dataEmployee, [
            'personal_goal' => 'max:5000',
            'forte' => 'max:5000',
            'hobby' => 'max:5000',
            'weakness' => 'max:5000',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        $itemRelate = $this->employee->getItemRelate('hobby');
        $itemRelate->setData($dataEmployee);
        $itemRelate->employee_id = $this->employee->id;
        $itemRelate->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        return $response;
    }

    /**
     * costume page
     */
    public function costume()
    {
        return view('team::member.edit.profile_costume', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'costume',
            'tabTitle' => trans('team::profile.Size ready-to-wear'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeCostume' => $this->employee->getItemRelate('costume'),
            'asiaSizesOption' => EmployeeCostume::toOptionsAsiaSize(),
            'europeSizesOption' => EmployeeCostume::toOptionsEuropeSize(),
        ]);
    }

    /**
     * save costume
     */
    public function saveCostume()
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $dataEmployee = (array)Input::get('employeeCostume');
        $validator = Validator::make($dataEmployee, [
            'shoudler_width' => 'numeric',
            'round_butt' => 'numeric',
            'long_sleeve' => 'numeric',
            'long_pants' => 'numeric',
            'long_shirt' => 'numeric',
            'long_skirt' => 'numeric',
            'round_chest' => 'numeric',
            'round_thigh' => 'numeric',
            'round_waist' => 'numeric',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        $itemRelate = $this->employee->getItemRelate('costume');
        $itemRelate->setData($dataEmployee);
        $itemRelate->employee_id = $this->employee->id;
        $itemRelate->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        return $response;
    }

    /**
     * politic page
     */
    public function politic()
    {
        return view('team::member.edit.profile_politic', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'politic',
            'tabTitle' => trans('team::profile.Politic Info'),
            'tabTitleIcon' => 'fa fa-user',
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeePolitic' => $this->employee->getItemRelate('politic'),
            'partyOptions' => PartyPosition::getAll(),
            'unionOptions' => UnionPosition::getAll(),
        ]);
    }

    /**
     * save politic
     */
    public function savePolitic()
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $dataEmployee = (array)Input::get('employeePolitic');
        $validator = Validator::make($dataEmployee, [
            'party_join_place' => 'max:255',
            'union_join_place' => 'max:255',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        if (!isset($dataEmployee['is_party_member'])) {
            $dataEmployee['is_party_member'] = 0;
            $dataEmployee['party_join_date'] = null;
            $dataEmployee['party_position'] = null;
            $dataEmployee['party_join_place'] = null;
        }
        if (!isset($dataEmployee['is_union_member'])) {
            $dataEmployee['is_union_member'] = 0;
            $dataEmployee['union_join_date'] = null;
            $dataEmployee['union_poisition'] = null;
            $dataEmployee['union_join_place'] = null;
        }
        Form::filterEmptyValue($dataEmployee, [
            'party_join_date',
            'union_join_date',
        ]);
        $itemRelate = $this->employee->getItemRelate('politic');
        $itemRelate->setData($dataEmployee);
        $itemRelate->employee_id = $this->employee->id;
        $itemRelate->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        return $response;
    }

    /**
     * military page
     */
    public function military()
    {
        return view('team::member.edit.profile_military', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'military',
            'tabTitle' => trans('team::profile.Military Info'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeMilitary' => $this->employee->getItemRelate('military'),
            'positionOptions' => MilitaryPosition::getAll(),
            'rankOptions' => MilitaryRank::getAll(),
            'armOptions' => MilitaryArm::getAll(),
            'level' => EmployeeMilitary::toOptionSoldierLevel(),
        ]);
    }

    /**
     * save military
     */
    public function saveMilitary()
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $dataEmployee = (array)Input::get('employeeMilitary');
        $validator = Validator::make($dataEmployee, [
            'join_date' => 'date',
            'branch' => 'max:255',
            'left_date' => 'date',
            'left_reason' => 'max:255',
            'revolution_join_date' => 'date',
            'num_disability_rate' => 'numeric|min:0|max:100'
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        if (!isset($dataEmployee['is_service_man'])) {
            $dataEmployee['is_service_man'] = 0;
            $dataEmployee['join_date'] = null;
            $dataEmployee['rank'] = null;
            $dataEmployee['arm'] = null;
            $dataEmployee['branch'] = null;
            $dataEmployee['left_date'] = null;
            $dataEmployee['left_reason'] = null;
        }
        if (!isset($dataEmployee['is_wounded_soldier'])) {
            $dataEmployee['is_wounded_soldier'] = 0;
            $dataEmployee['revolution_join_date'] = null;
            $dataEmployee['wounded_soldier_level'] = null;
            $dataEmployee['num_disability_rate'] = null;
            $dataEmployee['is_martyr_regime'] = 0;
        }
        if (!isset($dataEmployee['is_martyr_regime'])) {
            $dataEmployee['is_martyr_regime'] = 0;
        }
        Form::filterEmptyValue($dataEmployee, [
            'join_date',
            'left_date',
            'revolution_join_date',
        ]);
        $itemRelate = $this->employee->getItemRelate('military');
        $itemRelate->setData($dataEmployee);
        $itemRelate->employee_id = $this->employee->id;
        $itemRelate->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        return $response;
    }

    /**
     * list all relationship gridview
     */
    public function relationship()
    {
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'relationship', 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
        } else {
            $btnMore = [];
        }
        return view('team::member.relations.index', [
            'collectionModel' => EmployeeRelationship::gridRelationsViewByEmpl($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => 'relationship',
            'tabTitle' => trans('team::profile.List People'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-gia-dinh']),
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
            'toOptionsRelation' => RelationNames::getAllRelations(),
        ]);
    }

    /**
     * create Relation ship
     */
    public function createItemRelationship()
    {
        $employeeItemRelative = new EmployeeRelationship();
        $employeeItemRelative->id = 0;
        return view('team::member.relations.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'relationship',
            'tabTitle' => trans('team::profile.Relationship Info'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-gia-dinh']),
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'toOptionsRelation' => RelationNames::getAllRelations(),
            'libCountry' => Country::getAll(),
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this relations?'),
        ]);
    }

    /**
     * save Relationship
     *
     * @param int $relationsId
     */
    public function saveItemRelationship($relationsId = null)
    {
        $response = [];
        if (!$relationsId) {
            $employeeItemRelations = new EmployeeRelationship();
        } else {
            $employeeItemRelations = EmployeeRelationship::find($relationsId);
            if (!$employeeItemRelations) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelations->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $dataEmployee = (array)Input::get('relative');
        $validator = Validator::make($dataEmployee, [
            'name' => 'required|max:255',
            'note' => 'max:5000',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        Form::filterEmptyValue($dataEmployee, [
            'date_of_birth',
            'deduction_start_date',
            'deduction_end_date'
        ]);
        $employeeItemRelations->setData($dataEmployee);
        $employeeItemRelations->employee_id = $this->employee->id;
        if (!isset($dataEmployee['is_dependent'])) {
            $employeeItemRelations->is_dependent = 0;
            $employeeItemRelations->deduction_start_date = null;
            $employeeItemRelations->deduction_end_date = null;
        }
        if (!isset($dataEmployee['is_die'])) {
            $employeeItemRelations->is_die = 0;
        }
        $employeeItemRelations->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        if (!$relationsId) {
            $response['urlReplace'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'relationship', 'typeId' => $employeeItemRelations->id]);
            $response['urlFormSubmitChange'] = route('team::member.profile.save', ['employeeId' => $this->employee->id, 'type' => 'relationship', 'typeId' => $employeeItemRelations->id]);
            $response['urlFormDeleteItem'] = route('team::member.profile.item.relate.delete', ['employeeId' => $this->employee->id, 'type' => 'relationship', 'typeId' => $employeeItemRelations->id]);
        }
        return $response;
    }

    /**
     * edit relationship
     * @param type $id
     * @param type $view
     * @return type
     */
    public function editItemRelationship($relationid)
    {
        $employeeItemRelative = EmployeeRelationship::find($relationid);
        if (!$employeeItemRelative) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'relationship'])
                ->withErrors(trans('team::messages.Not found item.'));
        }
        if ($employeeItemRelative->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        return view('team::member.relations.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'relationship',
            'tabTitle' => trans('team::profile.Relationship Info'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-gia-dinh']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => $relationid,
            'isAccessDeleteEmployee' => false,
            'toOptionsRelation' => RelationNames::getAllRelations(),
            'libCountry' => Country::getAll(),
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this relations?'),
        ]);
    }

    /**
     * list all education
     * @return View Description
     */
    public function education()
    {
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'education', 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
        } else {
            $btnMore = [];
        }
        return view('team::member.education.index', [
            'collectionModel' => EmployeeEducation::getAllEducation($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => 'education',
            'tabTitle' => trans('team::profile.Education Process'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-qua-trinh-hoc-tap']),
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
            'educationQualities' => QualityEducation::getAll(),
            'educationDegree' => EmployeeSchool::listDegree(),
            'educationList' => School::getSchoolList(),
            'majorList' => Major::getMajorList(),
        ]);
    }

    /**
     * create new record education
     * @return View
     */
    public function createItemEducation()
    {
        $employeeItemRelative = new EmployeeEducation();
        $employeeItemRelative->id = 0;
        return view('team::member.education.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'education',
            'tabTitle' => trans('team::profile.Education Process'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-qua-trinh-hoc-tap']),
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this education?'),
            'educationQualities' => QualityEducation::getAll(),
            'educationDegree' => EmployeeSchool::listDegree(),
            'educationType' => EmployeeSchool::listEduType(),
            'libCountry' => Country::getAll(),
            'educationList' => School::getSchoolList(),
            'majorList' => Major::getMajorList(),
            'facultyList' => Faculty::getFacultyList(),
        ]);
    }

    /**
     * validate and save data
     *
     * @param int $eduId
     * @return type
     * @throws Exception
     */
    public function saveItemEducation($eduId = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        if (!$eduId) {
            $employeeItemRelative = new EmployeeEducation();
        } else {
            $employeeItemRelative = EmployeeEducation::find($eduId);
            if (!$employeeItemRelative) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelative->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        $dataEmployee = (array)Input::get('edu');
        $validator = Validator::make($dataEmployee, [
            'school_id' => 'required',
            'start_at' => 'required|date',
            'end_at' => 'date',
            'country' => 'required',
            'province' => 'required|max:255',
            'faculty_id' => 'required',
            'major_id' => 'required',
            'quality' => 'required',
            'type' => 'required',
            'awarded_date' => 'date',
            'note' => 'max:5000',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        Form::filterEmptyValue($dataEmployee, [
            'awarded_date',
            'end_at',
        ]);
        if (!isset($dataEmployee['is_graduated'])) {
            $dataEmployee['is_graduated'] = 0;
            $dataEmployee['degree'] = null;
            $dataEmployee['awarded_date'] = null;
        }
        $employeeItemRelative->setData($dataEmployee);
        $employeeItemRelative->employee_id = $this->employee->id;
        $employeeItemRelative->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        if (!$eduId) {
            $response['urlReplace'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'education', 'typeId' => $employeeItemRelative->id]);
            $response['urlFormSubmitChange'] = route('team::member.profile.save', ['employeeId' => $this->employee->id, 'type' => 'education', 'typeId' => $employeeItemRelative->id]);
            $response['urlFormDeleteItem'] = route('team::member.profile.item.relate.delete', ['employeeId' => $this->employee->id, 'type' => 'education', 'typeId' => $employeeItemRelative->id]);
        }
        return $response;
    }

    /**
     * edit Education
     */
    public function editItemEducation($eduId)
    {
        $employeeItemRelative = EmployeeEducation::find($eduId);
        if (!$employeeItemRelative) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'education'])
                ->withErrors(Lang::get('team::messages.Not found item.'));
        }
        if ($employeeItemRelative->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        return view('team::member.education.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'education',
            'tabTitle' => trans('team::profile.Education Process'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-qua-trinh-hoc-tap']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => $eduId,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this education?'),
            'educationQualities' => QualityEducation::getAll(),
            'educationDegree' => EmployeeSchool::listDegree(),
            'educationType' => EmployeeSchool::listEduType(),
            'libCountry' => Country::getAll(),
            'educationList' => School::getSchoolList(),
            'majorList' => Major::getMajorList(),
            'facultyList' => Faculty::getFacultyList(),
        ]);
    }

    /*
     * show list business trips
     */

    public function business()
    {
        $employee = $this->employee;
        $collectionModel = EmployeeBusiness::getGridData($employee->id);
        return view('team::member.business.index', [
            'collectionModel' => $collectionModel,
            'employeeModelItem' => $employee,
            'tabType' => 'business',
            'isSelfProfile' => $this->isSelfProfile,
            'tabTitle' => trans('team::profile.Business Trips'),
            'tabTitleSub' => trans('team::profile.Companies had worked'),
        ]);
    }

    /**
     * save business trips
     * @param type $typeId (typeId allow format, don't use)
     * @return type
     */
    public function saveBusiness($typeId = null)
    {
        $data = Input::all();
        $valid = Validator::make($data, [
            'work_place' => 'required',
            'start_at' => 'required|date_format:Y-m-d',
            'end_at' => 'date_format:Y-m-d',
            'position' => 'required',
        ]);
        if ($valid->fails()) {
            return [
                'status' => 0,
                'message' => trans('team::messages.Invalid input data')
            ];
        }
        $data['employee_id'] = $this->employee->id;
        $item = EmployeeBusiness::insertOrUpdate($data);
        return [
            'status' => 1,
            'item' => $item
        ];
    }

    /**
     * list all certificate
     */
    public function certificate()
    {
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'certificate', 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
            $btnMore = []; // hidden btn add
        } else {
            $btnMore = [];
        }
        return view('team::member.certificate.index', [
            'collectionModel' => EmployeeCertificate::getAllCertificate($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => 'certificate',
            'tabTitle' => trans('team::profile.List Certificate'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-chung-chi']),
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
            'certificateTypes' => Certificate::labelAllType(),
        ]);
    }

    /**
     * create new record education
     * @return View
     */
    public function createItemCertificate()
    {
        $employeeItemRelative = new EmployeeCertificate();
        $employeeItemRelative->id = 0;
        $certificates = Certificate::whereNull('deleted_at')->orderBy('name', 'asc')->get(['name', 'id', 'type'])->toArray();
        $teamOfemployee = TeamMember::getTeamEmployee($this->employee->id);
        $approver = [];
        foreach ($teamOfemployee->toArray() as $item) {
            $listEmployee = TeamMember::listIsScopeTeamofEmployee($item, 'edit.profile.v1');
            $approver = $approver + $listEmployee;
        }
        return view('team::member.certificate.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'certificate',
            'tabTitle' => trans('team::profile.Certificate'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-chung-chi']),
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this certificate?'),
            'certificateTypes' => Certificate::labelAllType(),
            'languageLevels' => View::getLanguageLevel(),
            'certificates' => $certificates,
            'listApprover' => $approver,
        ]);
    }

    /**
     * save data Certificate
     *
     * @param int $cerId
     */
    public function saveItemCertificate($cerId = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        if (!$cerId) {
            $employeeItemRelative = new EmployeeCertificate();
        } else {
            $employeeItemRelative = EmployeeCertificate::find($cerId);
            if (!$employeeItemRelative) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelative->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        $dataEmployee = (array)Input::get('cer');
        $cer = Certificate::find($dataEmployee['certificate_id']);
        if (!$cer) {
            $response['message'] = 'Chứng chỉ đã bị xóa hoặc không tồn tại!';
            $response['status'] = 0;
            return $response;
        }
        $dataEmployee['type'] = $cer->type;
        $dataEmployee['name'] = $cer->name;
        $validator = Validator::make($dataEmployee, [
            'certificate_id' => 'required|max:255',
            'level_other' => 'string|max:255',
            'place' => 'required|max:255',
            'start_at' => 'date',
            'end_at' => 'date',
            'p_sum' => 'max:255',
            'p_listen' => 'numeric',
            'p_speak' => 'numeric',
            'p_read' => 'numeric',
            'p_write' => 'numeric',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        Form::filterEmptyValue($dataEmployee, [
            'start_at',
            'end_at',
            'image-certificate',
            'p_listen',
            'image-certificate',
            'p_speak',
            'p_read',
            'p_write',
        ]);
        if ($dataEmployee['type'] != Certificate::TYPE_LANGUAGE) {
            $dataEmployee['level'] = $dataEmployee['level_other'];
            $dataEmployee['p_listen'] = null;
            $dataEmployee['p_speak'] = null;
            $dataEmployee['p_read'] = null;
            $dataEmployee['p_write'] = null;
        }
        unset($dataEmployee['level_other']);
        $employeeItemRelative->setData($dataEmployee);
        $employeeItemRelative->employee_id = $this->employee->id;
        $employeeItemRelative->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        $response['cer'] = 1;
        $response['url'] = route('team::member.profile.save.image', ['employeeId' => $this->employee->id, 'type' => 'certificate', 'typeId' => $employeeItemRelative->id]);
        if (!$cerId) {
            $response['urlReplace'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'certificate', 'typeId' => $employeeItemRelative->id]);
            $response['urlFormSubmitChange'] = route('team::member.profile.save', ['employeeId' => $this->employee->id, 'type' => 'certificate', 'typeId' => $employeeItemRelative->id]);
            $response['urlFormDeleteItem'] = route('team::member.profile.item.relate.delete', ['employeeId' => $this->employee->id, 'type' => 'certificate', 'typeId' => $employeeItemRelative->id]);
        }
        return $response;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function saveImage(Request $request)
    {
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $id = $request->route('typeId');
            foreach ($files as $file) {
                $this->saveEmployeeCertificateImage($id, $file);
            }
            $response['message'] = trans('core::message.Save success');
            $response['status'] = 1;
        }
        if (!empty($request->all()['file_id'])) {
            $files = json_decode($request->all()['file_id']);
            foreach ($files as $item) {
                $this->deleteImage($item->name);
                EmployeeCertificateImage::where('id', $item->id)->delete();
            }
            $response['message'] = trans('core::message.Save success');
            $response['status'] = 1;
        }
        return $response;

    }

    /**
     * @param string $fileName
     */
    private function deleteImage($fileName)
    {
        $storage = Storage::disk();
        if ($storage->exists('public' . $fileName)) {
            $storage->delete('public' . $fileName);
        }
    }

    /**
     * Save data.
     * @param $data
     */
    public function saveEmployeeCertificateImage($id, $data)
    {
        try {
            $fileName = $this->uploadImage($data);
            $file = new EmployeeCertificateImage;
            $file->image = $fileName;
            $file->employee_certies_id = $id;
            $file->save();
        } catch (Exception $ex) {
            Log::info($ex);
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return string
     * @throws \Exception
     */
    private function uploadImage(UploadedFile $uploadedFile)
    {
        $fileName = rand(0, 30) . $uploadedFile->hashName();
        $dirPath = '/public/resource/employee/certificate';
        $dirPath_img = '/resource/employee/certificate';
        $filePath = $dirPath . DIRECTORY_SEPARATOR . $fileName;
        Storage::put($filePath, file($uploadedFile));
        return $dirPath_img . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Save Employee Status.
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function saveEmployeeStatus(Request $request)
    {
        if (!empty($request->route('typeId'))) {
            $employeeItemRelative = EmployeeCertificate::find($request->route('typeId'));
            if (!$employeeItemRelative) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if (!empty($request->get('cancel'))) {
                $validator = Validator::make($request->all(), [
                    'comment' => 'required',
                ]);
                if ($validator->fails()) {
                    $response['status'] = 0;
                    $response['message'] = $validator->errors()->all();
                    return $response;
                }
                $dataEmployee['status'] = Certificate::STATUS_CANCEL;
            } else if (!empty($request->get('request'))) {
                if ($request->all()['status'] == 0 || $request->all()['status'] == 3) {
                    $validator = Validator::make($request->all(), [
                        'approver' => 'required',
                    ]);
                    if ($validator->fails()) {
                        $response['status'] = 0;
                        $response['message'] = $validator->errors()->all();
                        return $response;
                    }
                    $dataEmployee['status'] = Certificate::STATUS_PROCESSING;
                    $dataEmployee['approver'] = $request->all()['approver'];
                }
                if ($request->all()['status'] == Certificate::STATUS_PROCESSING) $dataEmployee['status'] = Certificate::STATUS_COMPLETE;
            }
            $dataEmployee['confirm_date'] = Carbon::now()->format('Y-m-d');
            $employeeItemRelative->setData($dataEmployee);
            $employee = Employee::where('id', $request->route('employeeId'))->first();
            $leader = Employee::select('id', 'email', 'name')->where('id', $employeeItemRelative->approver)->first();

            $teamOfemployee = TeamMember::getTeamEmployee($employee->id);
            $approver = [];
            foreach ($teamOfemployee->toArray() as $item) {
                $listEmployee = TeamMember::listIsScopeTeamofEmployee($item, 'edit.profile.v1');
                $approver = $approver + $listEmployee;
            }

            if (!$approver) {
                $response['message'] = trans("team::profile.Member hasn't D-leader");
                $response['status'] = 0;
                return $response;
            }
            $employeeItemRelative->save();
            if ($employeeItemRelative) {
                $emailQueue = new EmailQueue();
                $mail = $employee->email;
                $name = $employee->name;
                $idNoti = $employeeItemRelative->approver;
                if ($employeeItemRelative->status == Certificate::STATUS_PROCESSING) {
                    $mail = $leader->email;
                    $name = $leader->name;
                }
                $status = '';
                $subject = trans('team::view.[Rikkeisoft Intranet] Browse certificate allowance');
                $reason = !empty($request->all()['comment']) ? $request->all()['comment'] : '';
                if ($employeeItemRelative->status == Certificate::STATUS_CANCEL) {
                    $status = 'reject';
                    $subject = trans('team::view.[Rikkeisoft Intranet] Your certificate allowance is not approved');
                    $idNoti = $employee->id;
                } elseif ($employeeItemRelative->status == Certificate::STATUS_COMPLETE) {
                    $status = 'approve';
                    $subject = trans('team::view.[Rikkeisoft Intranet] Your certificate allowance has been approved');
                    $idNoti = $employee->id;
                }
                if (!empty($request->all()['approver']) && $request->all()['approver'] == $request->route('employeeId')) {
                    $response['message'] = trans('core::message.Save success');
                    $response['status'] = 1;
                    return $response;
                }
                // gửi noti và mail
                \RkNotify::put(
                    $idNoti,
                    $employeeItemRelative->status == Certificate::STATUS_CANCEL ? $subject . ' - ' . $reason : $subject,
                    route('team::member.profile.index', ['employeeId' => $employee->id, 'type' => 'certificate', 'typeId' => $employeeItemRelative->id])
                );
                $emailQueue->setTo($mail, $name)
                    ->setTemplate('team::mail.approvecertificate', [
                        'dear_name' => $name,
                        'to_name' => $leader->name,
                        'from_name' => $employee->name,
                        'approved' => $leader->name,
                        'reason' => $reason,
                        'status' => $status,
                        'time' => Carbon::now()->__toString(),
                        'link' => route('team::member.profile.index', ['employeeId' => $employee->id, 'type' => 'certificate', 'typeId' => $employeeItemRelative->id])
                    ])
                    ->setSubject($subject)
                    ->addBcc(Certificate::GROUP_MAIL_DAO_TAO)
                    ->save();
                $response['message'] = trans('core::message.Save success');
                $response['status'] = 1;
                return $response;
            }
        } else {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function changeEmployeeApprover(Request $request)
    {
        if (!empty($request->all()['approver']) && ($request->all()['status'] == 0 || $request->all()['status'] == 3)) {
            $employee = Employee::where('id', Auth::id())->first();
            if (!empty($request->all()['approver']) && $employee->id == $request->all()['approver']) {
                $response['status'] = 1;
                $response['button'] = '<button type="button" class="btn btn-primary" id="confirm-button" >' . trans("team::view.Approve") . '</button>';
                return $response;
            } else {
                $response['status'] = 0;
                return $response;
            }
        }
    }

    /**
     * edit Education
     */
    public function editItemCertificate($cerId)
    {
        $employeeItemRelative = EmployeeCertificate::find($cerId);
        if (!$employeeItemRelative) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'certificate'])
                ->withErrors(Lang::get('team::messages.Not found item.'));
        }
        if ($employeeItemRelative->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        $certificates = Certificate::whereNull('deleted_at')->get(['name', 'id', 'type'])->toArray();
        $certificatesImage = EmployeeCertificateImage::where('employee_certies_id', $cerId)->get()->toArray();
        $status_certificate = Certificate::getOptionStatus();
        $leader = TeamMember::isLeaderOrSubleader(Auth::id(), $this->employee->id);
        $isScopeTeam = Permission::getInstance()->isAllow('team::member.profile.save');
        $checkPermission = false;
        if (Permission::getInstance()->isRoot() || ($leader && $isScopeTeam)) {
            $checkPermission = true;
        }
        $teamOfemployee = TeamMember::getTeamEmployee($this->employee->id);
        $approver = [];
        foreach ($teamOfemployee->toArray() as $item) {
            $listEmployee = TeamMember::listIsScopeTeamofEmployee($item, 'edit.profile.v1');
            $approver = $approver + $listEmployee;
        }

        return view('team::member.certificate.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'certificate',
            'tabTitle' => trans('team::profile.Certificate'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-chung-chi']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'isScopeTeam' => $isScopeTeam,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'certificatesImage' => $certificatesImage,
            'employeeItemTypeId' => $cerId,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this certificate?'),
            'certificateTypes' => Certificate::labelAllType(),
            'languageLevels' => View::getLanguageLevel(),
            'certificates' => $certificates,
            'status_certificate' => $status_certificate,
            'checkPermission' => $checkPermission,
            'listApprover' => $approver,
        ]);
    }

    /**
     * list gridView skills
     */
    public function skill()
    {
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'skill', 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
        } else {
            $btnMore = [];
        }
        return view('team::member.skill.index', [
            'collectionModel' => EmployeeSkill::getAllSkill($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => 'skill',
            'tabTitle' => trans('team::profile.List Skills'),
            'tabTitleIcon' => 'fa fa-asterisk',
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
            'skillTypes' => Skill::typeLabel(),
        ]);
    }

    /**
     * create new record skill
     */
    public function createItemSkill()
    {
        $employeeItemRelative = new EmployeeSkill();
        $employeeItemRelative->id = 0;
        return view('team::member.skill.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'skill',
            'tabTitle' => trans('team::profile.Skill Info'),
            'tabTitleIcon' => 'fa fa-asterisk',
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this skill?'),
            'skillTypes' => Skill::typeLabel(),
        ]);
    }

    /**
     * validate and save record to database
     *
     * @param int $skillId
     */
    public function saveItemSkill($skillId = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        if (!$skillId) {
            $employeeItemSkill = new EmployeeSkill();
        } else {
            $employeeItemSkill = EmployeeSkill::find($skillId);
            if (!$employeeItemSkill) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemSkill->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        $dataEmployee = (array)Input::get('skill');
        $dataEmployeeMore = (array)Input::get('ski_mo');
        $validator = Validator::make($dataEmployee, [
            'name' => 'required|max:255',
            'type' => 'required',
            'level' => 'required',
            'exp_y' => 'digits_between:0,100',
            'exp_m' => 'digits_between:0,12',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        $employeeItemSkill->setData($dataEmployee);
        $employeeItemSkill->employee_id = $this->employee->id;
        $employeeItemSkill->experience = View::getValueArray($dataEmployeeMore, ['exp_y']) . '-'
            . View::getValueArray($dataEmployeeMore, ['exp_m']);
        $employeeItemSkill->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        if (!$skillId) {
            $response['urlReplace'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'skill', 'typeId' => $employeeItemSkill->id]);
            $response['urlFormSubmitChange'] = route('team::member.profile.save', ['employeeId' => $this->employee->id, 'type' => 'skill', 'typeId' => $employeeItemSkill->id]);
            $response['urlFormDeleteItem'] = route('team::member.profile.item.relate.delete', ['employeeId' => $this->employee->id, 'type' => 'skill', 'typeId' => $employeeItemSkill->id]);
        }
        return $response;
    }

    /**
     * edit skillRecord
     * @param int $type
     * @param int $id
     */
    public function editItemSkill($skillId)
    {
        $employeeItemRelative = EmployeeSkill::find($skillId);
        if (!$employeeItemRelative) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'skill'])
                ->withErrors(Lang::get('team::messages.Not found item.'));
        }
        if ($employeeItemRelative->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        $employeeItemRelative->loadExper();
        return view('team::member.skill.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'skill',
            'tabTitle' => trans('team::profile.Skill Info'),
            'tabTitleIcon' => 'fa fa-asterisk',
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => $skillId,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this skill?'),
            'skillTypes' => Skill::typeLabel(),
        ]);
    }

    /**
     * list attach
     */
    public function attach()
    {
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'attach', 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
        } else {
            $btnMore = [];
        }
        return view('team::member.attach.index', [
            'collectionModel' => EmployeeAttach::getAllAttach($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => 'attach',
            'tabTitle' => trans('team::profile.Scan doc'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-scan-giay-to']),
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
        ]);
    }

    /**
     * create new Attach file
     * @params int $employeeId
     */
    public function createItemAttach()
    {
        $employeeItemRelative = new EmployeeAttach();
        $employeeItemRelative->id = 0;
        return view('team::member.attach.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'attach',
            'tabTitle' => trans('team::profile.Scan doc'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-scan-giay-to']),
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this attachment?'),
            'attachFiles' => [],
        ]);
    }

    /**
     * validate + save to db
     *
     * @param int $attachId
     */
    public function saveItemAttach($attachId = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        if (!$attachId) {
            $employeeItemRelations = new EmployeeAttach();
        } else {
            $employeeItemRelations = EmployeeAttach::find($attachId);
            if (!$employeeItemRelations) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelations->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        $dataEmployee = Input::get('attach');
        $validator = Validator::make($dataEmployee, [
            'title' => 'required|max:255',
            'note' => 'max:5000',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        $employeeItemRelations->fill($dataEmployee);
        $employeeItemRelations->employee_id = $this->employee->id;
        $employeeItemRelations->save();
        $filesJson = Input::get('file_json');
        if (!$attachId && $filesJson) {
            $filesJson = trim($filesJson, ',');
            $filesJson = trim($filesJson);
            if ($filesJson) {
                try {
                    $filesJson = json_decode('[' . $filesJson . ']', true);
                    if ($filesJson) {
                        foreach ($filesJson as $fileJson) {
                            EmployeeAttachFile::insertAttachFile($employeeItemRelations->id, $fileJson);
                        }
                    }
                } catch (Exception $ex) {

                }
            }
        }
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        if (!$attachId) {
            Session::flash(
                'messages', [
                    'success' => [
                        trans('core::message.Save success'),
                    ]
                ]
            );
            $response['redirect'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'attach', 'typeId' => $employeeItemRelations->id]);
        }
        return $response;
    }

    /**
     * upload file + save to db
     *
     * @param int $attachId
     */
    public function saveItemAttachFile($attachId = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return response()->json($response, 500);
        }
        if (!$attachId) {
            $employeeItemRelations = new EmployeeAttach();
        } else {
            $employeeItemRelations = EmployeeAttach::find($attachId);
            if (!$employeeItemRelations) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return response()->json($response, 500);
            }
            if ($employeeItemRelations->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return response()->json($response, 500);
            }
        }
        $validator = Validator::make(Input::all(), [
            'qqfile' => 'file',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return response()->json($response, 500);
        }
        $fileUpload = Input::file('qqfile');
        try {
            $uploadResult = View::uploadFileInfo(
                $fileUpload, config('general.upload_storage_public_folder') .
                '/' . Employee::ATTACH_FOLDER . $this->employee->id, [
                    'max_size' => 5,
                    'file_mimes' => [
                        'jpeg', 'jpg', 'gif', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx'
                    ]
                ]
            );
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            Log::error($ex);
            return $response;
        }
        if (!$uploadResult) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.Upload file error');
            return response()->json($response, 500);
        }
        $path = Employee::ATTACH_FOLDER
            . $this->employee->id . '/'
            . $uploadResult['new'] . '.' . $uploadResult['extension'];
        $responseUpload = [
            'file_name' => $uploadResult['name'] . '.' . $uploadResult['extension'],
            'path' => $path,
            'type' => $uploadResult['type'],
            'file_size' => $uploadResult['size'],
        ];
        if (!$attachId) {
            $response['file_upload'] = $responseUpload;
        } else {
            $response['item2_id'] = EmployeeAttachFile::insertAttachFile($employeeItemRelations->id, $responseUpload);
        }
        $response['item2_path'] = $path;
        $response['success'] = 1;
        return response()->json($response, 200);
    }

    /**
     * delete file + save to db
     *
     * @param int $attachId
     */
    public function deleteItem2AttachFile($attachId, $fileId)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return response()->json($response, 500);
        }
        $employeeItemRelations = EmployeeAttach::find($attachId);
        if (!$employeeItemRelations) {
            $response['status'] = 0;
            $response['message'] = trans('team::messages.Not found item.');
            return response()->json($response, 500);
        }
        if ($employeeItemRelations->employee_id != $this->employee->id) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return response()->json($response, 500);
        }
        try {
            EmployeeAttachFile::deleteAttachFile($attachId, $fileId);
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            Log::error($ex);
            return response()->json($response, 500);
        }
        $response['message'] = trans('core::message.Save success');
        $response['success'] = 1;
        return response()->json($response, 200);
    }

    /**
     * edit record attach file
     *
     * @param int $attachId
     * @return View
     */
    public function editItemAttach($attachId)
    {
        $employeeItemRelative = EmployeeAttach::find($attachId);
        if (!$employeeItemRelative) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'attach'])
                ->withErrors(trans('team::messages.Not found item.'));
        }
        if ($employeeItemRelative->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        return view('team::member.attach.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'attach',
            'tabTitle' => trans('team::profile.Scan doc'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-scan-giay-to']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => $attachId,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this attachment?'),
            'attachFiles' => EmployeeAttachFile::getFiles($attachId),
        ]);
    }

    /**
     * downloadAttach by filename
     *
     * @param string $attachId
     * @return type
     */
    public function editItemAttachdownload($attachId)
    {
        $employeeItemRelative = EmployeeAttach::find($attachId);
        if (!$employeeItemRelative) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'attach'])
                ->withErrors(trans('team::messages.Not found item.'));
        }
        if ($employeeItemRelative->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        $pathFile = storage_path('app/' . SupportConfig::get('general.upload_storage_public_folder')
            . '/' . $employeeItemRelative->path);
        if (!$employeeItemRelative->path || !file_exists($pathFile)) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'attach'])
                ->withErrors(trans('team::profile.File is not exists'));
        }
        return response()->download($pathFile, $employeeItemRelative->file_name, [
            'Content-Disposition: attachment;',
            'Content-Type: ' . ($employeeItemRelative->type ? $employeeItemRelative->type : 'application/octet-stream')
        ]);
    }

    /**
     * Gridview list prize
     * @param $employeeId
     */
    public function prize()
    {
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'prize', 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
        } else {
            $btnMore = [];
        }
        return view('team::member.prize.index', [
            'collectionModel' => EmployeePrize::gridViewByEmpl($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => 'prize',
            'tabTitle' => trans('team::profile.Prize Info'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
        ]);
    }

    /**
     * create new Attach file
     * @param int $employeeId
     */
    public function createItemPrize()
    {
        $employeeItemPrize = new EmployeePrize();
        $employeeItemPrize->id = 0;
        return view('team::member.prize.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'prize',
            'tabTitle' => trans('team::profile.Prize Info'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemPrize,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this prize?'),
        ]);
    }

    /**
     * validate + upload file + save to db
     *
     * @param int $prizeId
     * @return type
     */
    public function saveItemPrize($prizeId = null)
    {
        $response = [];
        if (!$prizeId) {
            $employeeItemPrize = new EmployeePrize();
        } else {
            $employeeItemPrize = EmployeePrize::find($prizeId);
            if (!$employeeItemPrize) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemPrize->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        $dataEmployee = (array)Input::get('employeeItemPrize');
        $validator = Validator::make($dataEmployee, [
            'name' => 'required|max:255',
            'level' => 'max:255',
            'issue_date' => 'required|date',
            'expire_date' => 'date',
            'place' => 'max:255',
            'note' => 'max:5000',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        Form::filterEmptyValue($dataEmployee, [
            'expire_date',
        ]);
        $employeeItemPrize->setData($dataEmployee);
        $employeeItemPrize->employee_id = $this->employee->id;
        $upload = Input::file('image');
        //upload attach
        if ($upload) {
            try {
                $employeeItemPrize->image = View::uploadFile(
                    $upload, SupportConfig::get('general.upload_storage_public_folder') .
                    '/' . Employee::ATTACH_FOLDER, SupportConfig::get('services.file.attach_allow'), SupportConfig::get('services.file.attach_max'), false
                );
            } catch (Exception $ex) {
                Log::error($ex);
            }
        }
        $employeeItemPrize->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        if (!$prizeId) {
            $response['urlReplace'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'prize', 'typeId' => $employeeItemPrize->id]);
            $response['urlFormSubmitChange'] = route('team::member.profile.save', ['employeeId' => $this->employee->id, 'type' => 'prize', 'typeId' => $employeeItemPrize->id]);
            $response['urlFormDeleteItem'] = route('team::member.profile.item.relate.delete', ['employeeId' => $this->employee->id, 'type' => 'prize', 'typeId' => $employeeItemPrize->id]);
        }
        return $response;
    }

    /**
     * edit record attach file
     *
     * @param int $prizeId
     * @return View
     */
    public function editItemPrize($prizeId)
    {
        $employeeItemPrize = EmployeePrize::find($prizeId);
        if (!$employeeItemPrize) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'prize'])
                ->withErrors(Lang::get('team::messages.Not found item.'));
        }
        if ($employeeItemPrize->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        return view('team::member.prize.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'prize',
            'tabTitle' => trans('team::profile.Prize Info'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemPrize,
            'employeeItemTypeId' => $prizeId,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this prize?'),
        ]);
    }

    /**
     * list all company experience
     *
     * @return View Description
     */
    public function comexper()
    {
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'comexper', 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
        } else {
            $btnMore = [];
        }
        return view('team::member.experience.company.index', [
            'collectionModel' => EmployeeComexper::getAllCompanyExper($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => 'comexper',
            'tabTitle' => trans('team::profile.Company worked'),
            'tabTitleIcon' => 'fa fa-building-o',
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
        ]);
    }

    /**
     * create new record education
     * @return View
     */
    public function createItemComexper()
    {
        $employeeItemRelative = new EmployeeComexper();
        $employeeItemRelative->id = 0;
        return view('team::member.experience.company.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'comexper',
            'tabTitle' => trans('team::profile.Company worked'),
            'tabTitleIcon' => 'fa fa-building-o',
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this company?'),
        ]);
    }

    /**
     * validate and save data
     *
     * @param int $eduId
     * @return type
     * @throws Exception
     */
    public function saveItemComexper($eduId = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        if (!$eduId) {
            $employeeItemRelative = new EmployeeComexper();
        } else {
            $employeeItemRelative = EmployeeComexper::find($eduId);
            if (!$employeeItemRelative) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelative->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        $dataEmployee = (array)Input::get('com');
        $validator = Validator::make($dataEmployee, [
            'name' => 'required|max:255',
            'position' => 'required|max:255',
            'address' => 'required|max:255',
            'start_at' => 'date',
            'end_at' => 'date',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        Form::filterEmptyValue($dataEmployee, [
            'start_at',
            'end_at',
        ]);
        $employeeItemRelative->setData($dataEmployee);
        $employeeItemRelative->employee_id = $this->employee->id;
        $employeeItemRelative->save();
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        if (!$eduId) {
            $response['urlReplace'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'comexper', 'typeId' => $employeeItemRelative->id]);
            $response['urlFormSubmitChange'] = route('team::member.profile.save', ['employeeId' => $this->employee->id, 'type' => 'comexper', 'typeId' => $employeeItemRelative->id]);
            $response['urlFormDeleteItem'] = route('team::member.profile.item.relate.delete', ['employeeId' => $this->employee->id, 'type' => 'comexper', 'typeId' => $employeeItemRelative->id]);
        }
        return $response;
    }

    /**
     * edit Education
     */
    public function editItemComexper($comId)
    {
        $employeeItemRelative = EmployeeComexper::find($comId);
        if (!$employeeItemRelative) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'comexper'])
                ->withErrors(Lang::get('team::messages.Not found item.'));
        }
        if ($employeeItemRelative->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        return view('team::member.experience.company.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'comexper',
            'tabTitle' => trans('team::profile.Company worked'),
            'tabTitleIcon' => 'fa fa-building-o',
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => $comId,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this company?'),
        ]);
    }

    /**
     * list all experience
     *
     * @return View Description
     */
    public function experience()
    {
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'experience', 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
        } else {
            $btnMore = [];
        }
        return view('team::member.experience.index', [
            'collectionModel' => EmployeeProjExper::getAllProjExper($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => 'experience',
            'tabTitle' => trans('team::profile.Experiences'),
            'tabTitleIcon' => 'fa fa-tripadvisor',
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
        ]);
    }

    /**
     * show japan experience list and edit
     * @param Request $request
     */
    public function createItemExperience()
    {
        $employeeItemRelative = new EmployeeProjExper();
        $employeeItemRelative->id = 0;
        return view('team::member.experience.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'experience',
            'tabTitle' => trans('team::profile.Project info'),
            'tabTitleIcon' => 'fa fa-tripadvisor',
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this project?'),
            'employeeCompany' => EmployeeComexper::getCompanyOfEmployee($this->employee->id),
            'projExperLangs' => [],
            'projExperOs' => [],
            'projExperDb' => [],
        ]);
    }

    /**
     * validate and save form experience
     * @param int $employeeId
     * @param Request $request
     */
    public function saveItemExperience($expId = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        if (!$expId) {
            $employeeItemRelative = new EmployeeProjExper();
        } else {
            $employeeItemRelative = EmployeeProjExper::find($expId);
            if (!$employeeItemRelative) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelative->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        $dataEmployee = (array)Input::get('exp');
        $dataEmployeeMore = (array)Input::get('ex_mo');
        $validator = Validator::make(array_merge((array)$dataEmployee, (array)$dataEmployeeMore), [
            'name' => 'required|max:255',
            'position' => 'required|max:255',
            'customer' => 'max:255',
            'start_at' => 'date',
            'end_at' => 'date',
            'no_member' => 'integer',
            'env' => 'max:255',
            'per_y' => 'digits_between:0,100',
            'per_m' => 'digits_between:0,12',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        Form::filterEmptyValue($dataEmployee, [
            'start_at',
            'end_at',
        ]);
        $employeeItemRelative->setData($dataEmployee);
        $employeeItemRelative->employee_id = $this->employee->id;
        $employeeItemRelative->period = View::getValueArray($dataEmployeeMore, ['per_y']) . '-'
            . View::getValueArray($dataEmployeeMore, ['per_m']);
        DB::beginTransaction();
        try {
            $employeeItemRelative->save();
            EmplProjExperLang::saveProjExperLang($employeeItemRelative->id, Input::get('ex_mo.lang'));
            EmplProjExperOs::saveProjExperOs($employeeItemRelative->id, Input::get('ex_mo.os'));
            EmplProjExperDb::saveProjExperDb($employeeItemRelative->id, Input::get('ex_mo.db'));
            DB::commit();
        } catch (Exception $ex) {
            Log::error($ex);
            DB::rollback();
            $response['message'] = $ex->getMessage();
            $response['status'] = 0;
            return $response;
        }
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        if (!$expId) {
            $response['urlReplace'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'experience', 'typeId' => $employeeItemRelative->id]);
            $response['urlFormSubmitChange'] = route('team::member.profile.save', ['employeeId' => $this->employee->id, 'type' => 'experience', 'typeId' => $employeeItemRelative->id]);
            $response['urlFormDeleteItem'] = route('team::member.profile.item.relate.delete', ['employeeId' => $this->employee->id, 'type' => 'experience', 'typeId' => $employeeItemRelative->id]);
        }
        return $response;
    }

    /**
     * edit skillRecord
     * @param int $type
     * @param int $id
     */
    public function editItemExperience($expId)
    {
        $employeeItemRelative = EmployeeProjExper::find($expId);
        if (!$employeeItemRelative) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'experience'])
                ->withErrors(Lang::get('team::messages.Not found item.'));
        }
        if ($employeeItemRelative->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        $employeeItemRelative->loadPeriod();
        return view('team::member.experience.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'experience',
            'tabTitle' => trans('team::profile.Project info'),
            'tabTitleIcon' => 'fa fa-asterisk',
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => $expId,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this project?'),
            'employeeCompany' => EmployeeComexper::getCompanyOfEmployee($this->employee->id),
            'projExperLangs' => EmplProjExperLang::getLangOfProjExper($expId),
            'projExperOs' => EmplProjExperOs::getOsOfProjExper($expId),
            'projExperDb' => EmplProjExperDb::getDbOfProjExper($expId),
        ]);
    }

    /**
     * list attach
     */
    public function wonsite()
    {
        $type = 'wonsite';
        $isAccessSubmitForm = $this->isScopeCompany || $this->isSelfProfile ? '' : ' hidden';
        if (!$isAccessSubmitForm) {
            $btnMore = [
                'create' => [
                    'label' => 'Add',
                    'label_prefix' => '<i class="fa fa-plus"></i> ',
                    'class' => 'btn btn-primary',
                    'disabled' => false,
                    'url' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => $type, 'typeId' => 'create']),
                    'type' => 'link'
                ],
            ];
        } else {
            $btnMore = [];
        }
        return view('team::member.want-onsite.index', [
            'collectionModel' => EmployeeWantOnsite::getItemsWantOnsite($this->employee->id),
            'employeeModelItem' => $this->employee,
            'tabType' => $type,
            'tabTitle' => trans('team::profile.Want onsite'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-mong-muon-onsite']),
            'isAccessSubmitForm' => $isAccessSubmitForm,
            'isSelfProfile' => $this->isSelfProfile,
            'buttonActionMore' => $btnMore,
        ]);
    }

    /**
     * create new Attach file
     * @params int $employeeId
     */
    public function createItemWonsite()
    {
        $employeeItemRelative = new EmployeeWantOnsite();
        $employeeItemRelative->id = 0;
        return view('team::member.want-onsite.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => 'wonsite',
            'tabTitle' => trans('team::profile.Want onsite'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-mong-muon-onsite']),
            'isAccessSubmitForm' => true,
            'disabledInput' => '',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelative,
            'employeeItemTypeId' => 0,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this want?'),
            'attachFiles' => [],
        ]);
    }

    /**
     * validate + save to db
     *
     * @param int $attachId
     */
    public function saveItemWonsite($itemId = null)
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return $response;
        }
        if (!$itemId) {
            $employeeItemRelations = new EmployeeWantOnsite();
        } else {
            $employeeItemRelations = EmployeeWantOnsite::find($itemId);
            if (!$employeeItemRelations) {
                $response['status'] = 0;
                $response['message'] = trans('team::messages.Not found item.');
                return $response;
            }
            if ($employeeItemRelations->employee_id != $this->employee->id) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return $response;
            }
        }
        $dataEmployee = Input::get('ons');
        $validator = Validator::make($dataEmployee, [
            'place' => 'required|max:255',
            'start_at' => 'required|date',
            'end_at' => 'date',
            'reason' => 'max:5000',
            'note' => 'max:5000',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->all();
            return $response;
        }
        $employeeItemRelations->fill($dataEmployee);
        $employeeItemRelations->employee_id = $this->employee->id;
        $originData = $employeeItemRelations->getOriginal();
        $employeeItemRelations->save();
        $this->sendMailSaveOnsite($originData, $employeeItemRelations);
        $response['message'] = trans('core::message.Save success');
        $response['status'] = 1;
        if (!$itemId) {
            $type = 'wonsite';
            $response['urlReplace'] = route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => $type, 'typeId' => $employeeItemRelations->id]);
            $response['urlFormSubmitChange'] = route('team::member.profile.save', ['employeeId' => $this->employee->id, 'type' => $type, 'typeId' => $employeeItemRelations->id]);
            $response['urlFormDeleteItem'] = route('team::member.profile.item.relate.delete', ['employeeId' => $this->employee->id, 'type' => $type, 'typeId' => $employeeItemRelations->id]);
            $response['relative_id'] = $employeeItemRelations->id;
        }
        return $response;
    }

    /**
     * edit item want onsite
     *
     * @param type $itemId
     * @return type
     */
    public function editItemWonsite($itemId)
    {
        $employeeItemRelaitive = EmployeeWantOnsite::find($itemId);
        $type = 'wonsite';
        if (!$employeeItemRelaitive) {
            return redirect()
                ->route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => $type])
                ->withErrors(Lang::get('team::messages.Not found item.'));
        }
        if ($employeeItemRelaitive->employee_id != $this->employee->id) {
            View::viewErrorPermission();
        }
        return view('team::member.want-onsite.edit', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => $this->isScopeCompany,
            'userItem' => $this->user,
            'tabType' => $type,
            'tabTitle' => trans('team::profile.Want onsite'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-mong-muon-onsite']),
            'isAccessSubmitForm' => $this->isScopeCompany || $this->isSelfProfile,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => $this->isSelfProfile,
            'employeeItemMulti' => $employeeItemRelaitive,
            'employeeItemTypeId' => $itemId,
            'isAccessDeleteEmployee' => false,
            'deleteConfirmNoti' => trans('team::profile.Are you sure delete this want?'),
        ]);
    }

    /**
     * skill sheet
     */
    public function cv()
    {
        $curEmp = Permission::getInstance()->getEmployee();

        $employeeCvEav = EmplCvAttrValue::getAllValueCV($this->employee->id);
        $status = $employeeCvEav->getVal('status');
        $accessView = $this->accessViewSS($status);
        $accessApprover = $accessView['approver'];
        $accessEdit = $this->accessEditSS($status, $accessView);

        if ($accessEdit['approver']) {
            $approver = $accessEdit['approver'];
        } elseif ($accessView['approver']) {
            $approver = $accessView['approver'];
        } else {
            $approver = new Employee();
        }

        if (!$this->isSelfProfile) {
            if (Permission::getInstance()->isScopeCompany(null, Employee::ROUTE_VIEW_SKILLSHEET)) {
                // no something.
            } elseif (Permission::getInstance()->isScopeTeam(null, Employee::ROUTE_VIEW_SKILLSHEET)) {
                $listTeamOfEmpCheckConvertArray = \Rikkei\Sales\View\CssPermission::getArrTeamIdByEmployee($this->employee->id);
                // get list employeeId responsible team of employee check.
                $listEmpIdResiponsibleTeam = PqaResponsibleTeam::getEmpIdResponsibleTeam($listTeamOfEmpCheckConvertArray);
                $listEmpIdResiponsibleTeamConvertArray = [];
                foreach ($listEmpIdResiponsibleTeam as $item) {
                    $listEmpIdResiponsibleTeamConvertArray[] = $item->employee_id;
                }
                if (!Team::checkSameTeam($curEmp->id, $this->employee->id) && !in_array($curEmp->id, $listEmpIdResiponsibleTeamConvertArray)) {
                    View::viewErrorPermission();
                }
            } else {
                if ($curEmp->id != $approver->id) {
                    View::viewErrorPermission();
                }
            }
        }

        $collection = EmployeeProjExper::getProjExpersInCv($this->employee->id);
        $skillProjIds = EmplProjExperTag::getSkillIdsProjInCv($collection);
        $skillPersonIds = EmployeeSkill::getSkillIdsInCv($this->employee->id);
        $collectionModel = SkillSheetComment::getGridData($this->employee->id);
        $arryNumber = [];
        foreach ($collection as $iteam) {
            $arryNumber['proj_' . $iteam->id . '_number_' . $iteam->lang_code] = $iteam->number;
        }
        $employeeCvEav->eav = array_merge($employeeCvEav->eav, $arryNumber);
        //=================
        return view('team::member.skill-sheet.index', [
            'employeeModelItem' => $this->employee,
            'userItem' => $this->user,
            'projsExper' => $collection,
            'skillsProj' => $skillProjIds['data'],
            'skillsPerson' => $skillPersonIds,
            'employeeCvEav' => $employeeCvEav,
            'tagData' => Tag::getTagDataProj(),
            'tabType' => 'cv',
            'isSelfProfile' => $this->isSelfProfile,
            'langsLevel' => View::getLangLevelSplit(),
            'projPosition' => EmployeeProjExper::getResponsiblesDefine(),
            'isAccess' => $this->isScopeCompany || $this->isScopeTeam || $this->isSelfProfile,
            'isCompanyDisableInput' => $this->isScopeCompany ? '' : ' disabled',
            'disabledInput' => TeamMember::isLeaderOrSubleader($curEmp->id, $this->employee->id) || $this->isScopeCompany || $this->isSelfProfile || $accessEdit['edit'] ? '' : ' disabled',
            'isAccessTeamEdit' => $accessEdit['edit'],
            'approver' => $approver,
            'accessApprover' => $accessApprover,
            'collectionModel' => $collectionModel,
            'accessView' => $accessView,
            'roles' => \Rikkei\Resource\View\getOptions::getInstance()->getRoles(true),
        ]);
    }

    /**
     * save data synthesis general
     *
     * @param int $id
     */
    public function saveCv()
    {
        $responseFunc = [];
        $response = [
            'message' => null,
        ];
        $saveType = Input::get('save_type');
        $isAccessTeamEdit = false;
        $saveApprove = false;
        $empCurrent = Permission::getInstance()->getEmployee();
        $messageSuccess = trans('team::messages.Save data success!');
        if ($empCurrent->id == $this->employee->id) {
            if (!$saveType) {
                $saveType = EmplCvAttrValue::STATUS_SAVE;
            }
        }

        if ($saveType == EmplCvAttrValue::STATUS_APPROVE) {
            $saveApprove = true;
            $accessEdit = $this->accessEditSS($saveType);
            $isAccessTeamEdit = $accessEdit['edit'];
            $messageSuccess = trans('team::messages.Approve skill sheet success');
        }
        if ((!$this->isScopeCompany && !$this->isScopeTeam && !$this->isSelfProfile) && ($saveApprove && !$accessEdit['edit'])) {
            $response['status'] = 0;
            $response['message'] = trans('core::message.You don\'t have access');
            return response()->json($response, 500);
        }
        $langCur = Input::get('cv_view_lang');
        $response['item_id'] = null;
        DB::beginTransaction();
        try {
            if ($saveType && in_array($saveType, EmplCvAttrValue::getStatusSave())) {
                EmplCvAttrValue::insertOneEav($this->employee->id, 'status', $saveType);
                $this->sendMailSubmitCV($saveType);
            }

            $allLangs = EmplCvAttrValue::lang();
            if ($allLangs[0] == $langCur) {// $allLangs[0] = ja
                $lang = $allLangs[1];
            } else {
                $lang = $allLangs[0];
            }
            if (Input::get('remove.proj') && count(Input::get('remove.proj'))) {
                $removeIds = (array)Input::get('remove.proj');
                // remove project cung project khac ngon ngu
                $expres = EmployeeProjExper::findMany($removeIds);
                $arrEmp = [];
                foreach ($expres as $key => $expre) {
                    $proj = EmployeeProjExper::getProjExperByNumber($expre->employee_id, $expre->proj_number, $lang);
                    if ($proj) {
                        $arrEmp[] = $proj->id;
                    }
                }
                $removeIds = array_merge($arrEmp, $removeIds);
                EmployeeProjExper::removeBulk($removeIds);
                $response['delete'] = $removeIds;
            } else {
                $removeIds = [];
            }

            $inputPro = Input::get('pro');
            $inputSki = Input::get('ski');
            $inputRemoveSki = Input::get('remove.ski');
            $employee = $this->employee;
            $res = [];
            $keyEmp = [];
            $inputProOld = [];
            // check exits project number
            $collection = EmployeeProjExper::getProjExpersInCv($this->employee->id);
            if (count($inputPro)) {
                foreach ($inputPro as $key => $input) {
                    // check create or update
                    $response['action'] = is_numeric($key) ? 'update' : 'create';

                    if (empty($input['proj_number']) || !is_numeric($input['proj_number'])) {
                        $response['status'] = 0;
                        $response['message'] = trans('team::messages.Number project not empty or must integer');
                        return response()->json($response, 400);
                    }
                    foreach ($collection as $item) {
                        if (is_numeric($key) && $key == $item->id) {
                            continue;
                        }
                        if ($item->lang_code == $langCur && $item->number == $input['proj_number']) {
                            $response['status'] = 0;
                            $response['message'] = trans('team::messages.Number projcet exists');
                            return response()->json($response, 412);
                        }
                        if ($item->lang_code != $langCur && $item->number == $input['proj_number']) {
                            $response['action'] = 'update';
                            $inputProOld[$item->id] = $input;
                        }
                    }

                    //reset response for
                    if (!empty($input['res'])) {
                        foreach ($input['res'] as $keyRes => $res) {
                            $inputPro[$key]['res'][$keyRes] = str_replace('n-', '', $inputPro[$key]['res'][$keyRes]);
                        }

                        $res = static::changeReposeSkillProj($inputPro[$key]['res'], $langCur);
                        $inputPro[$key]['res'] = $res;
                    }
                }
            }
            $proSkill = static::saveProjSkill($langCur, $inputPro, $inputSki, $inputRemoveSki, $employee, $removeIds);
            $response['item_id'] = $proSkill[0];
            $responseFunc = $proSkill[1];
            if (Input::get('employee') && count(Input::get('employee'))) {
                $responseFunc['employee'] = $this->saveBase(false, true);
            }
            // ===== synchrony ====
            $inputProSyn = [];
            if (count($inputPro)) {
                foreach ($inputPro as $key => $items) {
                    if (!is_numeric($key) && $response['action'] == 'create') {
                        $inputProSyn = $inputPro;
                        foreach ($inputProSyn as $proSyn) {
                            $inputProSyn[$key]['name'] = '';
                            $inputProSyn[$key]['description'] = '';
                        }
                        break;
                    } else {
                        $keyEmp[] = $key;
                    }
                }
            }
            // cap nhat dong bo
            $idProExits = [];
            if (count($keyEmp)) {
                $expres = EmployeeProjExper::findMany($keyEmp);
                foreach ($expres as $expre) {
                    $proj = EmployeeProjExper::getProjExperByNumber($expre->employee_id, $expre->proj_number, $lang);
                    if ($proj) {
                        if (array_key_exists($expre->id, $inputPro)) {
                            $arrExpre = $inputPro[$expre->id];
                        } else {
                            $arrExpre = $inputProOld[$expre->id];
                        }
                        $inputProSyn[$proj->id] = $arrExpre;
                        $collection = EmplCvAttrValue::getAllValueCV($expre->employee_id);
                        if ($collection) {
                            $eav = $collection->eav;
                            $inputProSyn[$proj->id]['name'] = $eav['proj_' . $proj->id . '_name_' . $lang];
                            $inputProSyn[$proj->id]['description'] = $eav['proj_' . $proj->id . '_description_' . $lang];
                        }
                    } else {
                        $idProExits[] = $expre->id;
                    }
                }
            }
            //project i có bên A mà không có bên B: cập nhật project i ở bên A thì bên A được cập nhật, bên B được thêm mới
            if (!count($inputProSyn) && count($idProExits)) {
                foreach ($idProExits as $idPro) {
                    if (array_key_exists($idPro, $inputPro)) {
                        $key = 'new_item_fg_' . $idPro;
                        $inputProSyn[$key] = $inputPro[$idPro];
                        $inputProSyn[$key]['name'] = '';
                        $inputProSyn[$key]['description'] = '';
                        $response['action'] = 'create';
                    }
                }
            }
            if (count($inputProSyn)) {
                $addNewProjSkill = static::saveProjSkill($lang, $inputProSyn, $inputSki, $inputRemoveSki, $employee, $removeIds);
                $response['projnewId'] = $addNewProjSkill[0];
                $response['lang'] = $lang;
                $response['res'] = $res;
            }
            DB::commit();
        } catch (Exception $ex) {
            Log::error($ex);
            DB::rollback();
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            return response()->json($response, 500);
        }
        foreach ($responseFunc as $r) {
            if (isset($r['status']) && !$r['status']) {
                $response['status'] = 0;
                $response['message'] = array_merge((array)$r['message'], (array)$response['message']);
            }
        }
        if (isset($response['status']) && !$response['status']) {
            return response()->json($response, 500);
        }
        $response['status'] = 1;
        if (in_array($saveType, [EmplCvAttrValue::STATUS_SUBMIT, EmplCvAttrValue::STATUS_APPROVE])) {
            Session::flash(
                'messages', [
                    'success' => [
                        $messageSuccess,
                    ]
                ]
            );
            $response['reload'] = 1;
        } else {
            $response['message'] = $messageSuccess;
        }
        $response['save'] = $saveType;
        return response()->json($response, 200);
    }

    /**
     * Export cv skillsheet
     * @param integer $employeeId
     * @param Request $request
     */
    public function exportCv($employeeId, Request $request)
    {
        $locale = $request->get('lang');
        if (!$locale) {
            $locale = 'en';
        }
        set_time_limit(300);
        $this->preExec($employeeId);
        $projects = EmployeeProjExper::getProjExpersInCv($this->employee->id, $locale);
        $dataSkillProj = EmplProjExperTag::getSkillIdsProjInCv($projects, $locale);
        $dataSkillPerson = EmployeeSkill::getSkillIdsInCv($this->employee->id, false);
        $skillPerson = collect();
        $skillTagIds = [];
        foreach ($dataSkillPerson as $idx => $skill) {
            $prevSkill = isset($dataSkillPerson[$idx - 1]) ? $dataSkillPerson[$idx - 1] : null;
            if (!$prevSkill || $prevSkill->type != $skill->type) {
                $typeItem = new \stdClass();
                $typeItem->type = $skill->type;
                $typeItem->text = Lang::get('team::cv.' . $skill->type, [], $locale);
                $skillPerson->push($typeItem);
            }
            $skillPerson->push($skill);
            if (!in_array($skill->tag_id, $skillTagIds)) {
                $skillTagIds[] = $skill->tag_id;
            }
        }
        $tagIds = array_unique(array_merge($dataSkillProj['tag_ids'], $skillTagIds));
        $user = User::where('employee_id', $this->employee->id)->first();
        if ($user) {
            $avatar = $user->avatar_url;
        } else {
            $avatar = null;
        }

        $fileName = 'Rikkeisoft_Skill_Sheet_' . ExportCv::convertFullName($this->employee->name) . '_' . $locale;
        Excel::create($fileName, function ($excel) use ($locale, $projects, $dataSkillProj, $skillPerson, $tagIds, $avatar) {
            $excel->sheet('skillsheet', function ($sheet) use ($locale, $projects, $dataSkillProj, $skillPerson, $tagIds, $avatar) {
                $numRows = 4; // total tag <tr> for project item
                $countSkill = $skillPerson->count();
                $countProj = $projects->count();
                // max count = max (total skill - 4, total tag <tr> for projects list + 4 tag <tr> for reference)
                $maxCount = max($countProj * $numRows + 4, $countSkill - 4);
                $offsetHead = 13;
                $sheet->loadView('team::member.skill-sheet.export.cv', [
                    'employee' => $this->employee,
                    'locale' => $locale,
                    'projects' => $projects,
                    'skillProjIds' => $dataSkillProj['data'],
                    'skillPersonIds' => $skillPerson,
                    'tagData' => Tag::listTagsByIds($tagIds),
                    'cvEav' => EmplCvAttrValue::getAllValueCV($this->employee->id)->eav,
                    'projPosition' => EmployeeProjExper::getResponsiblesDefine(),
                    'projRoles' => EmployeeProjExper::listRoles(),
                    'countSkill' => $countSkill,
                    'countProj' => $countProj,
                    'maxCount' => $maxCount,
                    'offsetHead' => $offsetHead,
                    'numRows' => $numRows,
                ]);
                $colFromProj = 'B';
                $colToProj = 'C';
                //set datavalidation
                $colsRank = ['L', 'M', 'N', 'O', 'P'];
                $standAvatar = 'I4';
                // merge cell personal summary
                $sheet->mergeCells('A9:B11');
                $sheet->mergeCells('C9:I11');

                $rowFromProj = $offsetHead + 1;
                $rowToProj = $rowFromProj + $countProj * $numRows;
                for ($i = $rowFromProj; $i < $rowToProj; $i += $numRows) {
                    $offsetHead += $numRows;
                    $sheet->mergeCells($colFromProj . ($i) . ':' . $colToProj . ($i));
                    $sheet->mergeCells($colFromProj . ($i + 1) . ':' . $colToProj . ($i + 3));
                }

                // merge cell reference
                $offsetHead += 2;
                $sheet->mergeCells("A{$offsetHead}:B" . ($offsetHead + 2));
                $sheet->mergeCells("C{$offsetHead}:I" . ($offsetHead + 2));

                $rowFromRank = $rowFromProj;
                $langLevel = View::getLangLevelSplit();
                $colsData = [
                    'H9' => implode(',', $langLevel['ja'])
                ];
                if ($countSkill > 0) {
                    foreach ($skillPerson as $sKey => $skill) {
                        if ($skill->text) {
                            continue;
                        }
                        foreach ($colsRank as $col) {
                            $colsData[$col . ($rowFromRank + $sKey)] = ',●';
                        }
                    }
                }
                foreach ($colsData as $cell => $colData) {
                    $objValid = $sheet->getCell($cell)->getDataValidation();
                    $objValid->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST);
                    $objValid->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION);
                    $objValid->setAllowBlank(false);
                    $objValid->setShowInputMessage(true);
                    $objValid->setShowErrorMessage(true);
                    $objValid->setShowDropDown(true);
                    $objValid->setErrorTitle('Input error');
                    $objValid->setError('Value is not in list.');
                    $objValid->setPromptTitle('Pick from list');
                    $objValid->setFormula1('"' . $colData . '"');
                }
                //set avatar
                if ($avatar) {
                    $urlStorage = 'storage/resource/employee/avatar/';
                    $cellImgHeight = 160;
                    $imgWidth = 100;
                    if (strpos($avatar, $urlStorage) != false) {
                        $avatar = strstr($avatar, $urlStorage, false);
                        $path = public_path($avatar);
                        if (file_exists($path)) {
                            list($width, $height) = getimagesize($path);
                            $imgHeight = round($height * $imgWidth / $width);
                            $offsetY = max(($cellImgHeight - $imgHeight) / 2, 0);
                            $objDrawing = new PHPExcel_Worksheet_Drawing;
                            $objDrawing->setPath(public_path($avatar));
                            $objDrawing->setCoordinates($standAvatar);
                            $objDrawing->setWidth($imgWidth);
                            $objDrawing->setOffsetX(2.5);
                            $objDrawing->setOffsetY($offsetY);
                            $objDrawing->setWorksheet($sheet);
                        }
                    } else {
                        if (@fopen($avatar, "r")) {
                            $avatar = preg_replace('/\?(sz=)(\d+)/i', '', $avatar);
                            $typeImage = getimagesize($avatar);
                            switch (strtolower($typeImage['mime'])) {
                                case 'image/jpeg':
                                case 'image/jpg':
                                    $imageAvatar = imagecreatefromjpeg($avatar);
                                    break;
                                case 'image/png':
                                    $imageAvatar = imagecreatefrompng($avatar);
                                    break;
                                case 'image/gif':
                                    $imageAvatar = imagecreatefromgif($avatar);
                                    break;
                                default:
                                    break;
                            }
                            if (isset($imageAvatar)) {
                                $imgHeight = round(imagesy($imageAvatar) * $imgWidth / imagesx($imageAvatar));
                                $offsetY = max(($cellImgHeight - $imgHeight) / 2, 0);
                                $objDrawing = new PHPExcel_Worksheet_MemoryDrawing;
                                $objDrawing->setImageResource($imageAvatar);
                                $objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                                $objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                                $objDrawing->setWidth($imgWidth);
                                $objDrawing->setOffsetX(2.5);
                                $objDrawing->setOffsetY($offsetY);
                                $objDrawing->setCoordinates($standAvatar);
                                $objDrawing->setWorksheet($sheet);
                            }
                        }
                    }
                }
            });
        })->download('xlsx');
    }

    /**
     * save csv info
     */
    /* public function saveCvEav($dataEav, $isText = true, $isLang = true)
      {
      $response = [];
      if (!$isText) {
      $rules = array_fill_keys(array_keys($dataEav), 'string|max:255');
      $validator = Validator::make($dataEav, $rules);
      if ($validator->fails()) {
      $response['status'] = 0;
      $response['message'] = $validator->errors()->all();
      return $response;
      }
      }
      if (!$this->isScopeCompany) {
      unset($dataEav['name']);
      }
      if (!$isLang) {
      EmplCvAttrValueText::insertEav($this->employee->id, $dataEav, null);
      } else {
      if ($isText) {
      EmplCvAttrValueText::insertEav($this->employee->id, $dataEav, Input::get('cv_view_lang'));
      } else {
      EmplCvAttrValue::insertEav($this->employee->id, $dataEav, Input::get('cv_view_lang'));
      }
      }
      $response['status'] = 1;
      return $response;
      } */

    /**
     * Contact Info page
     */
    public function api()
    {
        if (!$this->isSelfProfile) {
            View::viewErrorPermission();
        }
        return view('team::member.edit.profile_api', [
            'employeeModelItem' => $this->employee,
            'isScopeCompany' => true,
            'userItem' => $this->user,
            'tabType' => 'api',
            'tabTitle' => trans('team::profile.Connect Api'),
            'helpLink' => route('help::display.help.view', ['id' => 'profile-khai-bao-thong-tin-ca-nhan-thong-tin-khac']),
            'isAccessSubmitForm' => true,
            'disabledInput' => $this->isScopeCompany || $this->isSelfProfile ? '' : ' disabled',
            'isSelfProfile' => true,
            'contactOption' => EmployeeContact::getContactOption($this->employee->id),
        ]);
    }

    /*
     * save api
     */

    public function saveApi()
    {
        if (!$this->isSelfProfile) {
            View::viewErrorPermission();
        }
        $this->employee->setData(Input::except('_token'))->save();
        return response()->json([
            'status' => 1,
            'message' => trans('team::messages.Save data success!')
        ]);
    }

    /*
     * save employee setting
     */

    public function saveSetting()
    {
        if (!$this->isSelfProfile) {
            View::viewErrorPermission();
        }
        $dataInput = Input::except('_token');
        $data = Input::get('emp_setting');
        $keyPassFile = EmployeeSetting::KEY_PASS_FILE;
        try {
            if (isset($dataInput['can_show_phone']) && isset($dataInput['can_show_birthday'])) {
                $dataContact = [];
                $showBirthdayOptions = [(string)EmployeeContact::NOT_SHOW_BIRTHDAY, (string)EmployeeContact::SHOW_ONLY_YEAR];
                if ($dataInput['can_show_phone'] === (string)EmployeeContact::NOT_SHOW_PHONE) {
                    $dataContact['can_show_phone'] = EmployeeContact::NOT_SHOW_PHONE;
                } else {
                    $dataContact['can_show_phone'] = EmployeeContact::SHOW_PHONE;
                }

                if (in_array($dataInput['can_show_birthday'], $showBirthdayOptions)) {
                    $dataContact['can_show_birthday'] = (int)$dataInput['can_show_birthday'];
                } else {
                    $dataContact['can_show_birthday'] = EmployeeContact::SHOW_BIRTHDAY;
                }

                if ($dataInput['dont_receive_system_mail'] === (string)EmployeeContact::DONT_RECEIVE_SYSTEM_MAIL) {
                    $dataContact['dont_receive_system_mail'] = EmployeeContact::DONT_RECEIVE_SYSTEM_MAIL;
                } else {
                    $dataContact['dont_receive_system_mail'] = EmployeeContact::RECEIVE_SYSTEM_MAIL;
                }
                $employeeContact = EmployeeContact::find($this->employee->id);
                if ($employeeContact) {
                    $employeeContact->setData($dataContact);
                    $employeeContact->save();
                } else {
                    $dataContact['employee_id'] = $this->employee->id;
                    EmployeeContact::insert($dataContact);
                }
            }
            if (isset($data[$keyPassFile])) {
                $valid = Validator::make($dataInput, [
                    'emp_setting.' . $keyPassFile => 'required|min:4|max:20',
                    'new_password' => 'required|min:4|max:20|confirmed'
                ]);
                if ($valid->fails()) {
                    return response()->json([
                        'status' => 0,
                        'message' => trans('team::messages.Invalid input data')
                    ]);
                }
                $itemPassFile = EmployeeSetting::getKeyItem($this->employee->id, $keyPassFile);
                if ($itemPassFile && $data[$keyPassFile] != decrypt($itemPassFile->value)) {
                    return response()->json([
                        'status' => 0,
                        'message' => trans('team::messages.Error input password')
                    ]);
                }
                if ($itemPassFile) {
                    //save old password
                    $itemPassFile->is_current = 0;
                    $itemPassFile->timestamps = false;
                    $itemPassFile->save();
                }
                //insert new item password
                EmployeeSetting::create([
                    'is_current' => 1,
                    'employee_id' => $this->employee->id,
                    'key' => $keyPassFile,
                    'value' => encrypt($dataInput['new_password'])
                ]);
            } else {
                EmployeeSetting::insertOrUpdate($this->employee->id, $data);
            }
            return response()->json([
                'status' => 1,
                'message' => trans('team::messages.Save data success!')
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'status' => 0,
                'message' => trans('team::messages.Error save data, please try again!')
            ]);
        }
    }

    /**
     * check exists employee attribute
     * @param type $employeeId
     * @param type $type
     * @return type
     */
    public function checkExists($employeeId, $type)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }

        $this->preExec($employeeId, false, true);
        $response = [
            'status' => 0,
            'message' => trans('team::messages.Not found item.'),
        ];
        if (!$this->employee) {
            $response['redirect'] = route('team::team.member.index');
            return $response;
        }

        $method = 'checkExists' . ucfirst($type);
        if (!method_exists($this, $method)) {
            return $response;
        }
        return $this->{$method}();
    }

    /**
     * check exists setting
     * @return type
     */
    public function checkExistsSetting()
    {
        if (!$this->isSelfProfile) {
            View::viewErrorPermission();
        }
        $key = Input::get('key');
        $value = Input::get('value');
        $itemSetting = EmployeeSetting::getKeyItem($this->employee->id, $key);
        $history = Input::get('history');
        $hasTrueKey = !$itemSetting || $value == decrypt($itemSetting->value);
        if (!$history) {
            return response()->json($hasTrueKey);
        }
        return response()->json([
            'trueKey' => $hasTrueKey,
            'histories' => EmployeeSetting::getKeyValHistory($this->employee->id, $key)
        ]);
    }

    /**
     * save contact
     */
    public function saveImport()
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isSelfProfile) {
            $status = EmplCvAttrValue::getValueSingleAttr($this->employee->id, 'status');
            if ($status == EmplCvAttrValue::STATUS_SUBMIT || $status == EmplCvAttrValue::STATUS_APPROVE) {
                $accessEdit = $this->accessEditSS($status);
                if (!$accessEdit['edit']) {
                    $response['status'] = 0;
                    $response['message'] = trans('core::message.You don\'t have access');
                    return response()->json($response, 500);
                }
            }
        }
        $validator = Validator::make(Input::all(), [
            'data' => 'required',
        ]);
        // check validator
        $data = json_decode(Input::get('data'), true);
        if (isset($data['proj'])) {
            $arryNumber = [];
            foreach ($data['proj'] as $key => $value) {
                if (empty($value['proj_number'])) {
                    $response['status'] = 0;
                    $response['message'] = trans('team::messages.Number project is not empty');
                    return response()->json($response, 412);
                }
                if (in_array($value['proj_number'], $arryNumber)) {
                    $response['status'] = 0;
                    $response['message'] = trans('team::messages.Number projcet exists');
                    return response()->json($response, 412);
                } else {
                    $arryNumber[] = $value['proj_number'];
                }
                if (count($value) && empty($value['name'])) {
                    $response['status'] = 0;
                    $response['message'] = trans('team::messages.Name project is not empty');
                    return response()->json($response, 412);
                }
            }
        }
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 500);
        }
        DB::beginTransaction();
        try {
            $profileImport = new ProfileImportHelper($this->employee, Input::get('lang'), ['classProfile' => $this]);
            $profileImport->import($data);
            DB::commit();
        } catch (Exception $ex) {
            Log::error($ex);
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            DB::rollback();
            return response()->json($response, 500);
        }
        Session::flash(
            'messages', [
                'success' => [
                    trans('team::messages.Import Skillsheet success'),
                ]
            ]
        );
        $response['status'] = 1;
        $response['reload'] = 1;
        return $response;
    }

    /**
     * send mail submit cv to leader
     *
     * @param type $status
     * @return boolean
     */
    protected function sendMailSubmitCV($status)
    {
        if ($status == EmplCvAttrValue::STATUS_SUBMIT) {
            return $this->sendMailWhenSubmit();
        }
        if ($status == EmplCvAttrValue::STATUS_APPROVE) {
            return $this->sendMailWhenapprove();
        }
    }

    /**
     * send mail to leader when employee submit
     */
    protected function sendMailWhenSubmit()
    {
        // submit -> sent mail for leader or assigne
        $leader = EmplCvAttrValue::findApproverSS($this->employee->id);
        if (!$leader) {
            $leader = TeamMember::getLeaderOfEmployee($this->employee);
        }
        if (!$leader ||
            $leader->leader_id == Auth::id()
        ) {
            return true;
        }
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($leader->email, $leader->name)
            ->setSubject(trans('team::view.[Rikkeisoft Intranet] Review skill sheet of :account', ['account' => View::getNickName($this->employee->email)]))
            ->setTemplate('team::mail.submitcv', [
                'dear_name' => $leader->name,
                'employee_name' => $this->employee->name,
                'employee_account' => View::getNickName($this->employee->email),
                'time' => Carbon::now()->__toString(),
                'link' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'cv'])
            ])
            ->save();
    }

    /**
     * send mail to leader when employee submit
     */
    protected function sendMailWhenapprove()
    {
        // submit -> sent mail for leader
        if ($this->employee->id == Auth::id()) {
            return true;
        }
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($this->employee->email, $this->employee->name)
            ->setSubject(trans('team::view.[Rikkeisoft Intranet] Your skills sheet have been approved'))
            ->setTemplate('team::mail.approvecv', [
                'dear_name' => $this->employee->name,
                'approver' => Auth::user()->name,
                'time' => Carbon::now()->__toString(),
                'link' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'cv'])
            ])
            ->save();
    }

    /**
     * send mail to leader when employee submit
     */
    protected function sendMailSaveOnsite($originData, $onsiteItemModel)
    {
        $newData = $onsiteItemModel->getAttributes();
        if (!View::isdiffArray($originData, $newData, ['place', 'start_at', 'end_at'])) {
            return true;
        }
        $leader = TeamMember::getLeaderOfEmployee($this->employee);
        if (!$leader ||
            $leader->leader_id == Auth::id()
        ) {
            return true;
        }
        $emailQueue = new EmailQueue();
        $emailQueue->setTo($leader->email, $leader->name)
            ->setSubject(trans('team::view.[Rikkeisoft Intranet] :account want to go onsite, please review it', ['account' => View::getNickName($this->employee->email)]))
            ->setTemplate('team::mail.onsite_sendleader', [
                'dear_name' => $leader->name,
                'employee_name' => $this->employee->name,
                'employee_account' => View::getNickName($this->employee->email),
                'time' => Carbon::now()->__toString(),
                'link' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'wonsite', 'typeId' => $onsiteItemModel->id])
            ])
            ->save();
    }

    public function saveChangeApprover()
    {
        $response = [];
        if (!$this->isScopeCompany && !$this->isScopeTeam) {
            // not assigneee => not access
            $assgineeOld = EmplCvAttrValue::findApproverSS($this->employee->id);
            if ($assgineeOld && $assgineeOld->id != Auth::id()) {
                $response['status'] = 0;
                $response['message'] = trans('core::message.You don\'t have access');
                return response()->json($response, 500);
            }
        }
        $validator = Validator::make(Input::get(), [
            'approverId' => 'required',
        ]);
        if ($validator->fails()) {
            $response['status'] = 0;
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 500);
        }
        $approver = Employee::find(Input::get('approverId'));
        if (!$approver) {
            $response['status'] = 0;
            $response['message'] = trans('team::messages.Not found item.');
            return response()->json($response, 500);
        }
        DB::beginTransaction();
        try {
            // save approver
            EmplCvAttrValue::saveApproverSS($this->employee->id, $approver->id);
            // send mail to approver
            $userAction = Auth::user();
            $emailQueue = new EmailQueue();
            $emailQueue->setTo($approver->email, $approver->name)
                ->setSubject(trans('team::view.[Rikkeisoft Intranet] Skillsheet of :account change approver to you', ['account' => View::getNickName($this->employee->email)]))
                ->setTemplate('team::mail.change_approver_ss', [
                    'dear_name' => $approver->name,
                    'employee_name' => $this->employee->name,
                    'employee_account' => View::getNickName($this->employee->email),
                    'time' => Carbon::now()->__toString(),
                    'link' => route('team::member.profile.index', ['employeeId' => $this->employee->id, 'type' => 'cv']),
                    'username' => $userAction->name,
                    'useraccount' => View::getNickName($userAction->email),
                ])
                ->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::error($ex);
            $response['status'] = 0;
            $response['message'] = $ex->getMessage();
            DB::rollback();
            return response()->json($response, 500);
        }
        $response['status'] = 1;
        $response['message'] = trans('team::messages.Change approver success');
        return $response;
    }

    /**
     * check asset view skill sheet
     *
     * @param int $status
     * @return boolean
     */
    protected function accessViewSS($status)
    {
        $approver = false;
        $return = [
            'approver' => null,
            'view' => false,
        ];
        if (!$this->isScopeCompanyView &&
            !$this->isScopeTeamView &&
            !$this->isSelfProfile
        ) {
            $approver = EmplCvAttrValue::findApproverSS($this->employee->id);
            if (!$approver) {
                $approver = TeamMember::getLeaderOfEmployee($this->employee);
            }
            if (!$approver || $approver->id != Auth::id()) {
                if (Permission::getInstance()->isAllow(Employee::ROUTE_VIEW_SKILLSHEET)) {
                    return $return;
                }
                View::viewErrorPermission();
                return $return;
            }
            $return['view'] = true;
            $return['approver'] = $approver;
            return $return;
        }
        $return['view'] = true;
        return $return;
    }

    /**
     * check asset edit skill sheet
     *
     * @param int $status
     * @param array $returnView
     * @return type
     */
    protected function accessEditSS($status, $returnView = [])
    {
        $approver = false;
        $return = [
            'approver' => isset($returnView['approver']) && $returnView['approver'] ? $returnView['approver'] : null,
            'edit' => false,
        ];
        // if status submmited, approved => allow edit
        //if ($status == EmplCvAttrValue::STATUS_SUBMIT || $status == EmplCvAttrValue::STATUS_APPROVE) {
        $this->preAccessTeam($this->employee->id);
        if (!$return['approver']) {
            $return['approver'] = EmplCvAttrValue::findApproverSS($this->employee->id);
        }
        if (!$return['approver']) {
            $return['approver'] = TeamMember::getLeaderOfEmployee($this->employee);
        }
        $return['edit'] = $this->isScopeCompany || $this->isScopeTeam || ($return['approver'] && $return['approver']->id == Auth::id());
        //}
        return $return;
    }

    /**
     * save comment when assigner feedback skillsheet.
     *
     * @param $employeeId : id of employee submit skillsheet.
     * @return array.
     */
    public function feedbackSkillSheet($employeeId)
    {
        $employeeId = Input::get('employeeId');
        $this->preExec($employeeId, false, true);
        $saveType = Input::get('save_type');
        $fbContent = Input::get('fb');
        $commentContent = Input::get('content');
        $employeeCvEav = EmplCvAttrValue::getAllValueCV($employeeId);
        $status = $employeeCvEav->getVal('status');
        $accessView = Input::get('accessView');
        $accessApprover = $accessView['approver'];
        $curEmp = Permission::getInstance()->getEmployee();
        DB::BeginTransaction();
        try {
            if (in_array($saveType, EmplCvAttrValue::getStatusSave())) {
                EmplCvAttrValue::insertOneEav($employeeId, 'status', $saveType);
                $skillSheetComment = new SkillSheetComment();
                $skillSheetComment->employee_id = $employeeId;
                $skillSheetComment->content = $fbContent;
                $skillSheetComment->created_by = Permission::getInstance()->getEmployee()->id;
                $skillSheetComment->type = SkillSheetComment::TYPE_FEEDBACK;
                $skillSheetComment->save();

                // Send mail feedback to employee.
                $this->sendMailWhenFB($employeeId, Permission::getInstance()->getEmployee()->id);
                \Session::flash('messages', ['success' => [Lang::get('team::messages.Feedback successful')]]);
            } else {
                $skillSheetComment = new SkillSheetComment();
                $skillSheetComment->employee_id = $employeeId;
                $skillSheetComment->content = $commentContent;
                $skillSheetComment->created_by = Permission::getInstance()->getEmployee()->id;
                $skillSheetComment->save();
            }
            DB::Commit();
            if (in_array($saveType, EmplCvAttrValue::getStatusSave())) {
                return redirect()->away(app('request')->fullUrl());
            } else {
                $response['success'] = 1;
                $response['message'] = Lang::get('project::message.Save');
                $response['popup'] = 1;
                $response['created_at'] = $skillSheetComment->created_at->format('Y-m-d H:i:s');
                $response['content'] = $skillSheetComment->content;
                $response['name'] = $curEmp->name;
                $response['email'] = preg_replace('/@.*/', '', $curEmp->email);
                return response()->json($response);
            }
        } catch (Exception $ex) {
            $response['status'] = 0;
            $response['message'] = Lang::get('team::messages.Feedback error');
            DB::rollback();
            Log::error($ex);
            return $response;
        }
    }

    /**
     * send mail to employee when assigner feedback cv.
     *
     * @param $employeeId : id of employee submit skillsheet.
     * @param $assignerId : id of assigner feedback skillsheet.
     * @return void.
     */
    protected function sendMailWhenFB($employeeId, $assignerId)
    {
        $employeeName = Employee::getNameEmailById($employeeId)->name;
        $assignerName = Employee::getNameEmailById($assignerId)->name;
        $emailQueue = new EmailQueue();
        $emailQueue->setTo(Employee::getEmailEmpById($employeeId))
            ->setSubject(Lang::get('team::messages.Your skillsheet has been Feedback'))
            ->setTemplate('team::mail.feedback_cv', [
                'dear_name' => $employeeName,
                'assigner' => $assignerName,
                'link' => route('team::member.profile.index', ['employeeId' => $employeeId, 'type' => 'cv'])
            ])->save();
    }

    /**
     * show list comment by ajax
     */
    public static function commentListAjax($employeeId)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        $employee = Employee::find($employeeId);
        if (!$employee) {
            $response['error'] = 1;
            $response['message'] = Lang::get('team::messages.Not found item.');
            return response()->json($response);
        }
        $response['success'] = 1;
        $response['html'] = ViewLaravel::make('team::include.comment.comment_list', [
            'collectionModel' => SkillSheetComment::getGridData($employeeId)
        ])->render();
        return response()->json($response);
    }

    /**
     * show information welfare.
     */
    public function welfare()
    {
        $curEmp = Permission::getInstance()->getEmployee();
        Breadcrumb::add('Welfare information', URL::route('team::member.profile.welfare'));
        return view('team::member.edit.profile_welfare_info', [
            'employeeModelItem' => Employee::find($curEmp->id),
            'tabType' => 'welfare',
            'isSelfProfile' => $this->isSelfProfile,
            'tabTitle' => trans('welfare::view.List welfare'),
            'collectionModel' => Event::getGridData($curEmp->id),
            'status' => Event::getOptionStatus(),
        ]);
    }

    /**
     * [saveProjSkill description]
     * @param  [type]  $langCur        [description]
     * @param  [type]  $inputPro       [description]
     * @param  [type]  $inputSki       [description]
     * @param  [type]  $inputRemoveSki [description]
     * @param  [type]  $employee       [description]
     * @param  [type]  $removeIds      [description]
     * @param boolean $synchronized [description]
     * @return [type]                  [description]
     */
    public static function saveProjSkill($langCur, $inputPro, $inputSki, $inputRemoveSki, $employee, $removeIds)
    {
        $eavMore = [];
        $eavTextMore = [];
        $eavMoreKeys = [
            'proj' => [
                'name',
                //'responsible',
            ],
            'proj_t' => [
                'description',
            ]
        ];
        $responseFunc = [];
        $idIteam = null;
        $profileImportHelper = new ProfileImportHelper($employee, $langCur);
        if ($inputPro && count($inputPro)) {
            foreach ($inputPro as $id => $dataProjExper) {
                if (in_array($id, $removeIds)) {
                    continue;
                }
                $responseFunc['pro'] = $profileImportHelper->saveCvProjItem($id, $dataProjExper, false);
                foreach ($eavMoreKeys['proj'] as $key) {
                    $v = View::getValueArray($dataProjExper, [$key]);
                    if ($v !== null) {
                        if (is_array($v)) {
                            $v = implode('-', $v);
                        }
                        $eavMore[sprintf('proj_%s_%s', $responseFunc['pro']['proj_id'], $key)] = $v;
                    }
                }
                foreach ($eavMoreKeys['proj_t'] as $key) {
                    $v = View::getValueArray($dataProjExper, [$key]);
                    if ($v !== null) {
                        $eavTextMore[sprintf('proj_%s_%s', $responseFunc['pro']['proj_id'], $key)] = $v;
                    }
                }
                $idIteam = $responseFunc['pro']['proj_id'];
            }
        }

        if ($inputRemoveSki && count($inputRemoveSki)) {
            $removeIds = (array)$inputRemoveSki;
            EmployeeSkill::removeBulk($removeIds);
        } else {
            $removeIds = [];
        }
        if ($inputSki && count($inputSki)) {
            foreach ($inputSki as $id => $dataProjExper) {
                if (in_array($id, $removeIds)) {
                    continue;
                }
                $responseFunc['ski'] = $profileImportHelper->saveCvSkillItem($id, $dataProjExper);
                if (!$responseFunc['ski']['status']) {
                    break;
                }
                $idIteam = $responseFunc['ski']['skill_id'];
            }
        }
        $eavMore = array_merge($eavMore, (array)Input::get('eav'));
        if ($eavMore) {
            $responseFunc['eav'] = $profileImportHelper->saveCvEav($eavMore, false, true);
        }
        $eavTextMore = array_merge($eavTextMore, (array)Input::get('eav_t'));
        if ($eavTextMore) {
            $responseFunc['eav_text'] = $profileImportHelper->saveCvEav($eavTextMore, true, true);
        }
        if (Input::get('eav_s')) {
            $responseFunc['eav'] = $profileImportHelper->saveCvEav(Input::get('eav_s'), false, false);
        }
        return [$idIteam, $responseFunc];
    }

    /**
     * change reponse for in skill sheet tab project
     * @param  [type] $arrRes [description]
     * @param  [type] $langCur   [description]
     * @return [type]         [description]
     */
    public static function changeReposeSkillProj($arrRes, $langCur)
    {
        $arr = [];
        $projPosition = EmployeeProjExper::getResponsiblesDefine();
        foreach ($arrRes as $value) {
            if (is_numeric($value)) {
                $value = (int)$value;
            }
            if (in_array($value, $projPosition[$langCur])) {
                $arr[] = array_search($value, $projPosition[$langCur]);
            } else {
                $arr[] = $value;
            }
        }
        return $arr;
    }

    /**
     * Check AppPass send mail
     * @return JsonResponse
     * @throws phpmailerException
     */
    public function checkAppPass()
    {
        $mail = new PHPMailer();
        $curEmp = Permission::getInstance()->getEmployee();
        $appPass = Input::get('appPass');

        // setting value to check appPass
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->Username = $curEmp->email;
        $mail->Password = isset($appPass) ? $appPass : null;

        if ($mail->smtpConnect()) {
            return response()->json(true);
        }
        return response()->json(false);
    }

    /**
     * @return bool
     */
    public function isScopeViewProfile()
    {
        return $this->isScopeCompanyView || $this->isScopeTeamView;
    }

    /**
     * @return collection
     */
    public function getEmployee()
    {
       return $this->employee;
    }

    /**
     * xóa nhân viên trong bảng công
     *
     * @param  array $empIds
     * @return
     */
    public function deleteEmployeeTimkeeping($empIds)
    {
        $now = Carbon::now();

        $tktable = TimekeepingTable::getTableName();
        $idTkTables = TimekeepingTable::select(
            "{$tktable}.id"
        )
        ->where("{$tktable}.start_date", '<=', $now->format('Y-m-d'))
        ->where("{$tktable}.end_date", '>=', $now->format('Y-m-d'))
        ->get()->toArray();

        Timekeeping::whereIn('employee_id', $empIds)
            ->whereIn('timekeeping_table_id', $idTkTables)
            ->update(['deleted_at' => $now]);
        TimekeepingAggregate::whereIn('employee_id', $empIds)
            ->whereIn('timekeeping_table_id', $idTkTables)
            ->update(['deleted_at' => $now]);
        // TimekeepingAggregateSalaryRate::whereIn('employee_id', $empIds)
        //     ->whereIn('timekeeping_table_id', $idTkTables)
        //     ->update(['deleted_at' => $now]);
        return true;
    }
}
