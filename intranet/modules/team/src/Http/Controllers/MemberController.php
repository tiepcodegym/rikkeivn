<?php

namespace Rikkei\Team\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Lang;
use Rikkei\Core\View\Breadcrumb;
use Rikkei\Core\View\CookieCore;
use Rikkei\Core\View\Form;
use Rikkei\Core\View\Menu;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Role;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\Permission;
use Rikkei\Team\View\UploadMember;
use URL;
use Rikkei\Core\Http\Controllers\Controller;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\View\ExportMember;
use Rikkei\Team\Model\EmplCvAttrValue;
use Rikkei\Team\Model\PqaResponsibleTeam;
use Rikkei\Team\Model\EmployeeRelationship;
use Maatwebsite\Excel\Facades\Excel;
use Rikkei\Team\Model\RelationNames;

class MemberController extends Controller
{
    /**
     * construct more
     */
    protected function _construct()
    {
        Breadcrumb::add('Team');
        Breadcrumb::add('Member', URL::route('team::team.member.index'));
        Menu::setActive('team');
    }

    /**
     * list member
     * @param null $statusWork
     * @param null $teamIds
     * @return Factory|\Illuminate\View\View
     */
    public function index($statusWork = null, $teamIds = null)
    {
        $urlFilter = route('team::team.member.index') . '/';
        $teamIdsAvailable = null;
        $teamTreeAvailable = [];
        $route = 'team::team.member.index';
        //scope company => view all team
        if (Permission::getInstance()->isScopeCompany(null, $route)) {
            $teamIds = Form::getFilterData('except', 'team_ids', $urlFilter);
            if (is_array($teamIds)) {
                $teamIds = array_filter(array_values($teamIds));
                $teamIds = implode($teamIds, ', ');
            }
            $teamIdsAvailable = true;
        } else {// permission team or self profile.
            $teamIdsAvailable = [];
            if (($scopeTeamIds = Permission::getInstance()->isScopeTeam(null, $route))) {
                $teamIdsAvailable = is_array($scopeTeamIds) ? $scopeTeamIds : [];
            }
            // get list team_id responsible by pqa.
            $curEmp = Permission::getInstance()->getEmployee();
            $teamIdsResponsibleByPqa = PqaResponsibleTeam::getListTeamIdResponsibleTeam($curEmp->id);
            if (!$teamIdsResponsibleByPqa->isEmpty()) {
                $teamIdsAvailable = array_merge($teamIdsAvailable, $teamIdsResponsibleByPqa->pluck('team_id')->toArray());
            }
            if (!$teamIdsAvailable || !Permission::getInstance()->isAllow($route)) {
                View::viewErrorPermission();
            }
            $teamIdsAvailable = array_unique($teamIdsAvailable);
            //ignore team childs
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
            if ($idFilters = Form::getFilterData('except', 'team_ids', $urlFilter)) {
                $teamIds = implode($idFilters, ', ') . ', ' . $teamIds;
                $teamIds = array_intersect(array_map('intval', explode(',', $teamIds)), $teamIdsAvailable);
                if (!array_intersect($teamIds, $teamIdsAvailable)) {
                    $checkReturn = CookieCore::get(Team::CACHE_TEAM_MEMBER_LIST);
                    if ($checkReturn < 1 || Permission::getInstance()->isScopeTeam($teamIds, $route)) {
                        Form::forgetFilter($urlFilter);
                        CookieCore::set(Team::CACHE_TEAM_MEMBER_LIST, 1);
                        return redirect()->route($route);
                    }
                    View::viewErrorPermission();
                }
                $teamIds = implode($teamIds, ', ');
            }
            if (!$teamIds) {
                $teamIds = implode($teamIdsAvailable, ', ');
                $flagNoCheck = true;
            }
            if (is_array($teamIdsAvailable) && count($teamIdsAvailable) == 1) {
                $teamIdsAvailable = Team::select('name')
                    ->find($teamIds);
            }
        }
        switch ($statusWork) {
            case 'leave':
                $collectionModel = Team::getMemberGridData($teamIds, Team::END_WORK, $urlFilter, ['isListPage' => true]);
                break;
            case 'all':
                $collectionModel = Team::getMemberGridData($teamIds, null, $urlFilter, ['isListPage' => true]);
                break;
            default: // work
                $collectionModel = Team::getMemberGridData($teamIds, Team::WORKING, $urlFilter, ['isListPage' => true]);
                $statusWork = 'work';
                break;
        }
        if (Permission::getInstance()->isScopeCompany(null, Employee::ROUTE_VIEW_SKILLSHEET) || Permission::getInstance()->isScopeTeam(null, Employee::ROUTE_VIEW_SKILLSHEET)) {
            $displayButtonViewSkill = '';
        } else {
            $displayButtonViewSkill = 'hidden';
        }
        if (Permission::getInstance()->isScopeCompany(null, 'team::member.profile.index') || Permission::getInstance()->isScopeTeam(null, 'team::member.profile.index')) {
            $displayButtonViewProfile = '';
        } else {
            $displayButtonViewProfile = 'hidden';
        }
        if (isset($flagNoCheck) && $flagNoCheck) {
            $teamIds = null;
        }
        $data = [
            'collectionModel' => $collectionModel,
            'teamIdCurrent' => $teamIds,
            'teamIdsAvailable' => $teamIdsAvailable,
            'teamTreeAvailable' => $teamTreeAvailable,
            'optionRoles' => Role::toOptionRoles(),
            'statusWork' => $statusWork,
            'optionsWorkContract' => EmployeeWork::getAllTypeContract(),
            'urlFilter' => $urlFilter,
            'optionWorkingStatus' => Team::listEmployeeStatus(),
            'optionStatusSkillSheet' => EmplCvAttrValue::getValueStatus(),
            'displayButtonViewSkill' => $displayButtonViewSkill,
            'displayButtonViewProfile' => $displayButtonViewProfile
        ];

        return view('team::member.index')->with($data);
    }


    /*
    public function edit($id, Request $request)
    {
        // log out if email is changed
        if(isset($request->needLogOut) && $request->needLogOut) {
            User::forceLogOut();
        }
        $model = Employee::where('working_type', '!=', getOptions::WORKING_INTERNSHIP)->find($id);
        if (!$model) {
            return redirect()->route('team::team.member.index')->withErrors(Lang::get('team::messages.Not found item.'));
        }
        $route = 'team::team.member.index';
        $employeeScopeCompany = Permission::getInstance()->isScopeCompany(null, $route);
        $employeeScopeTeam = Permission::getInstance()->isScopeTeam(null, $route);
        $permissionTeam = false;
        if (!$employeeScopeCompany && $employeeScopeTeam) {
            $teamIdsOfAuth = (array) Permission::getInstance()->getTeams();
            if (!$teamIdsOfAuth) {
                View::viewErrorPermission();
            }
            $employeeTeams = $model->getTeamPositons();
            foreach ($employeeTeams as $item) {
                if (in_array($item->team_id, $teamIdsOfAuth)) {
                    $permissionTeam = true;
                    break;
                }
            }
            if (!$permissionTeam) {
                View::viewErrorPermission();
            }
        } elseif (!$employeeScopeCompany) {
            View::viewErrorPermission();
        } else {
            // nothing
        }
        // get permssion company and leader greater
        if ($employeeScopeCompany) {
            $employeePermission = true;
        } else {
            $employeePermission = false;
        }
        Breadcrumb::add($model->name, URL::route('team::team.member.edit', ['id' => $id]));
        $presenter = null;
        if ($model->recruitment_apply_id) {
            $presenter = RecruitmentApplies::getPresenterName($model->recruitment_apply_id, false);
            if ($presenter) {
                Form::setData([
                    'recruitment.present' => $presenter
                ]);
            }
        }
        Form::setData($model, 'employee');
        //Get programming list
        $programs = Programs::getInstance()->getList();
        return view('team::member.edit', [
            'employeeTeamPositions' => $model->getTeamPositons(),
            'employeeRoles' => $model->getRoles(),
            'recruitmentPresent' => $presenter,
            'employeeGreaterLeader' => false,
            'employeeModelItem' => $model,
            'programs' => $programs,
            'isCreatePage' => false
        ]);
    }
    public function save()
    {
        Menu::removeActive();
        $permissionCreate = Permission::getInstance()->isAllow('team::team.member.create');
        $data = Input::all();
        
        $isProfile = (int)Input::get('is_profile');
        {
            $id = Input::get('id');
            if ($id || $isProfile) {
                if($id == 0) {
                    $model = Permission::getInstance()->getEmployee();
                    $employeeGreater = $model->isLeader();
                } else {
                    $model = Employee::find($id);
                }
                if (! $model) {
                    if ($id == 0) { // check self employee
                        return redirect()->route('team::member.profile',['employeeId' => $id])
                            ->withErrors(Lang::get('team::messages.Not found item.'))
                            ->send();
                    }
                    return redirect()->route('team::team.member.index')->withErrors(Lang::get('team::messages.Not found item.'));
                }
                //check permission save edit
                $employeeScopeCompany = 
                    Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit') ||
                    Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit.team.position') ||
                    Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit.role');
                $employeeGreater = Permission::getInstance()->getEmployee()->isGreater($model, true);
                
                $employeeScope = Permission::getInstance()->isScopeSelf(null , 'team::team.member.edit');
                if (!$employeeScopeCompany && !$employeeGreater && !($isProfile && $employeeScope)) {
                    View::viewErrorPermission();
                }            
            } else {
                //check permission creation
                if (!$permissionCreate) {
                    View::viewErrorPermission();
                }
                $model = new Employee();
            }
        }
        $dataEmployee = (array) Input::get('employee');
        $teamPostions = (array) Input::get('team');

        //Check edit employee_code permission
        if (Permission::getInstance()->isAllow('team::team.member.editEmployeeCode')) {
            if (isset($dataEmployee['employee_code']) && Employee::checkEmployeeCodeExist($dataEmployee['employee_code'], $id)) {
                if ($id) {
                    return redirect()->route('team::team.member.edit', ['id' => $id])->withErrors(Lang::get('team::messages.Employee code :code already exist', ['code' => $dataEmployee['employee_code']]));
                }
                return redirect()->route('team::team.member.create')->withErrors(Lang::get('team::messages.Employee code :code already exist', ['code' => $dataEmployee['employee_code']]));
            }
        } else {
            if ($model->employee_code && isset($dataEmployee['employee_code'])) {
                unset($dataEmployee['employee_code']);
            }
        }

        if (isset($teamPostions[0])) {
            unset($teamPostions[0]);
        }
        if(!isset($dataEmployee['employee_card_id'])) {
            $dataEmployee['employee_card_id'] = $model->employee_card_id;
        }
        if(!isset($dataEmployee['name'])) {
            $dataEmployee['name'] = $model->name;
        }
        if(!isset($dataEmployee['email'])) {
            $dataEmployee['email'] = $model->email;
        }
        // set old data form
        Form::setData($dataEmployee, 'employee');
        Form::setData($teamPostions, 'employee_team');
        Form::setData(['data' => Input::get('employee_skill')], 
            'employee_skill');
        Form::setData(['data' => Input::get('employee_skill_change')], 
            'employee_skill_change');
        
        
        //upload avatar
        $upload = isset($data['avatar_url']) ? $data['avatar_url'] : null;
        if($upload) {
                $fileName = View::uploadFile(
                    $upload, 
                    Config::get('general.upload_storage_public_folder') . 
                        '/' . Employee::AVATAR_FOLDER .$model->id,
                    Config::get('services.file.image_allow'),
                    Config::get('services.file.image_max'),
                    false
                );
                
                //upload false
                if (!$fileName) {
                    return redirect()->back()->with('messages', [
                        'errors' => [
                            Lang::get('core::message.Upload fails!'),
                        ],
                    ])->send();
                }
                $pathFolder = Config::get('general.upload_folder') .  '/' . Employee::AVATAR_FOLDER .$model->id;
                $image_path = trim($pathFolder, '/').'/'.$fileName;
                $dataEmployee['avatar_url'] = URL::asset($image_path);
        }
        if ($dataEmployee) {
            if (isset($dataEmployee['employee_card_id'])) {
                $dataEmployee['employee_card_id'] = (int) $dataEmployee['employee_card_id'];
            }
            $validator = Validator::make($dataEmployee, [
                'employee_card_id' => 'required|integer',
                'name' => 'required|max:255',
                'id_card_number' => 'required|max:255',
                'email' => 'required|max:255|email|unique:employees,email,' . $model->id,
                'join_date' => 'required|max:255',
            ]);
            if ($validator->fails()) {
                if (Input::get('is_profile')) {
                    return redirect()->route('team::member.profile',['employeeId' =>  $id])
                        ->withErrors($validator)
                        ->send();
                }
                if ($id) {
                    return redirect()->route('team::team.member.edit', ['id' => $id])->withErrors($validator);
                }
                return redirect()->route('team::team.member.create')->withErrors($validator);
            }

            //check email of rikkei
            if (! View::isEmailAllow($dataEmployee['email'])) {
                $message = Lang::get('team::messages.Please enter email of Rikkeisoft');
                if (Input::get('is_profile')) {
                    return redirect()->route('team::member.profile')
                        ->withErrors($message)
                        ->send();
                }
                if ($id) {
                    return redirect()->route('team::team.member.edit', ['id' => $id])->withErrors($message);
                }
                return redirect()->route('team::team.member.create')->withErrors($message);
            }
        }

        //process team
        $employeePermissionTeam = Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit.team.position') ||
            Permission::getInstance()->isScopeTeam(null, 'team::team.member.edit.team.position');

        if ($permissionCreate || $employeePermissionTeam || $employeeGreater) {
            if (!$teamPostions || !count($teamPostions)) {
                if (Input::get('is_profile')) {
                    return redirect()->route('team::member.profile', ['employeeId' => $id])
                        ->withErrors(Lang::get('team::view.Employee must belong to at least one team'))
                        ->send();
                }
                if ($id) {
                    return redirect()->route('team::team.member.edit', ['id' => $id])
                            ->withErrors(Lang::get('team::view.Employee must belong to at least one team'));
                }
                return redirect()->route('team::team.member.create')
                    ->withErrors(Lang::get('team::view.Employee must belong to at least one team'));
            }
        }

        //save model
        //checkpermission
        if ($id) {
            $allInput = Input::all();
            // not allow update information
            if (!Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit') &&
                !Permission::getInstance()->isScopeTeam(null, 'team::team.member.edit') &&
                !Permission::getInstance()->isScopeSelf(null, 'team::team.member.edit')) {
                $dataEmployee = [];
            }
            //not allow update team
            if (! Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit.team.position') && 
                ( ! Permission::getInstance()->isScopeTeam(null, 'team::team.member.edit.team.position') ||
                ! $employeeGreater )
            ) {
                unset($allInput['team']);
                Input::replace($allInput);
            }
            //not allow update role
            if (Permission::getInstance()->isScopeCompany(null, 'team::team.member.edit.role')) {
                $roles = (array) Input::get('role');
                $employeeRole = Role::select('id as role_id', 'role')
                    ->whereIn('id', $roles)
                    ->where('special_flg', Role::FLAG_ROLE)
                    ->orderBy('role')
                    ->get();
                if (count($employeeRole)) {
                    Form::setData($employeeRole, 'employee_role');
                }
            } else {
                unset($allInput['role']);
                Input::replace($allInput);
            }
        }
        // check if users change their emails.
        $needLogOut = false;
        if($model->email && isset($dataEmployee['email']) && $dataEmployee['email']) {
            $loggingUser = auth()->user()->email;
            $emEmail = $model->email;
            $needLogOut = ($loggingUser === $emEmail && $emEmail !== $dataEmployee['email']);
        }
        $arrayDate = [
            'passport_date_start',
            'passport_date_exprie',
            'leave_date',
            'offcial_date',
            'join_date',
            'birthday',
            'trial_date'
        ];
        foreach ($arrayDate as $item) {
            if (!isset($dataEmployee[$item])) {
                continue;
            }
            if ($valueItem = trim($dataEmployee[$item])) {
                $dataEmployee[$item] = $valueItem;
            } else {
                $dataEmployee[$item] = null;
            }
        }
        $model->setData($dataEmployee);
        $model->save();
        // save email for user if changing and delete that user session
        User::saveEmail($model);
        $messages = [
                'success'=> [
                    Lang::get('team::messages.Save data success!'),
                ]
        ];

        //if Candidate exist then update join date to start working of candidate
        $candidate = Candidate::getCandidateByEmployee($model->id);
        if ($candidate) {
            $candidate->start_working_date = $model->join_date;
            $candidate->save();
        }

        if (Input::get('is_profile')) {
            return redirect()->route('team::member.profile', ['needLogOut' => $needLogOut ,'employeeId' => $id])
                ->with('messages', $messages)
                ->send();
        }
        return redirect()->route('team::team.member.edit', ['id' => $model->id, 'needLogOut' => $needLogOut])->with('messages', $messages);
    }
    */
    
    /**
     * create member
     */
    /*public function create()
    {
        Menu::setActive('hr', '/');
        Breadcrumb::add(Lang::get('team::view.Create new'), 
            URL::route('team::team.member.create'));
        //Get programming list
        $programs = Programs::getInstance()->getList();
        return view('team::member.edit', [
            'employeeGreaterLeader' => true,
            'programs' => $programs,
            'isCreatePage' => true
        ]);
    }*/

    /**
     * get upload member
     * @return view
     */
    public function getUploadMember()
    {
        if (Permission::getInstance()->isAllow('team::team.member.get-upload-member')) {
            Breadcrumb::add(trans('team::view.Upload member'));
            return view('team::member.upload');
        } else {
            View::viewErrorPermission();
        }
    }

    /**
     * post upload memeber
     * @param array
     * @return view
     */
    public function postUploadMember(Request $request)
    {
        if (Permission::getInstance()->isAllow('team::team.member.get-upload-member')) {
            $validator = Validator::make($request->all(), [
                'excel_file' => 'file',
            ]);
            if ($validator->fails()) {
                return redirect()->back()->withErrors(Lang::get('core::message.Only allow file csv'));
            }
            $file = $request->file('excel_file');
            if (!$file) {
                return redirect()->back()
                    ->withErrors(Lang::get('core::message.Please choose file to upload'));
            }
            if ($file) {
                $extension = $file->getClientOriginalExtension();
                if (!in_array($extension, [
                        'csv',
                        'xlsx',
                        'xls'
                    ])
                ) {
                    return redirect()->back()
                        ->withErrors(Lang::get('core::message.Only allow file csv or excel'));
                }
            }
            //check processing
            if (UploadMember::getInstance()->checkProcessing()) {
                return redirect()->back()
                        ->with('messages', ['errors' => [trans('team::view.error_previous_uploading')]]);
            }

            try {
                $rowCount = 0;
                $dataReader = null;
                Excel::selectSheetsByIndex(0)->load($file->getRealPath(),  function ($reader) use (&$rowCount, &$dataReader){
                    $rowCount = $reader->get()->count();
                    $dataReader = $reader;
                });

                //if number of row < 100 then update now else run cronjob to update
                if ($rowCount < 100) {
                    $errors = [];
                    $count = 0;
                    UploadMember::getInstance()->excuteFile($dataReader, $errors, $count);
                    if ($errors) {
                        return redirect()->back()
                                ->with('messages', ['errors' => $errors]);
                    }
                    return redirect()->back()
                            ->with('messages', ['success' => [trans('team::view.Upload successful: :number record', ['number' => $count])]]);
                }

                UploadMember::getInstance()->storeFile($file);
                return redirect()->back()
                        ->with('upload_file', true)
                        ->with('messages', ['success' => [trans('team::view.uploading_member')]]);
            } catch (\Exception $ex) {
                Log::info($ex);
                return redirect()->back()->with('messages', ['errors' => 
                    [$ex->getMessage()]
                ]);
            }
        } else {
           View::viewErrorPermission(); 
        }
    }

    public function getUploadFamilyInfo()
    {
        if (Permission::getInstance()->isAllow('team::team.member.get-upload-member')) {
            Breadcrumb::add(trans('team::view.Import family info'));
            return view('team::member.upload_family_info');
        } else {
            View::viewErrorPermission();
        }
    }
    
    public function postUploadFamilyInfo(Request $request)
    {
        if (Permission::getInstance()->isAllow('team::team.member.get-upload-member')) {
            $validator = Validator::make($request->all(), [
                'excel_file' => 'file',
            ]);
            if ($validator->fails()) {
                return redirect()->back()->withErrors(Lang::get('core::message.Only allow file csv'));
            }
            $file = $request->file('excel_file');
            if (!$file) {
                return redirect()->back()->withErrors(Lang::get('core::message.Please choose file to upload'));
            }
            if ($file) {
                $extension = $file->getClientOriginalExtension();
                if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
                    return redirect()->back()->withErrors(Lang::get('core::message.Only allow file csv or excel'));
                }
            }

            try {
                $rowCount = 0;
                $dataReader = null;
                Excel::selectSheetsByIndex(0)->load($file->getRealPath(),  function ($reader) use (&$rowCount, &$dataReader){
                    $rowCount = $reader->get()->count();
                    $dataReader = $reader;
                });

                $errors = [];
                $count = 0;
                UploadMember::getInstance()->excuteFileFamilyInfo($dataReader, $errors, $count);
                if ($errors) {
                    return redirect()->back()->with('messages', ['errors' => $errors]);
                }
                return redirect()->back()->with('messages', ['success' => [trans('team::view.Upload successful: :number record', ['number' => $count])]]);
            } catch (\Exception $ex) {
                Log::info($ex);
                return redirect()->back()->with('messages', ['errors' => 
                    [$ex->getMessage()]
                ]);
            }
        } else {
           View::viewErrorPermission(); 
        }
    }

    /*
     * check uploaded member
     */
    public function checkUploadMember()
    {
        $results = UploadMember::getInstance()->checkUploaded();
        if (!$results) {
            return response()->json(['errors' => []], 422);
        }
        if ($results['errors']) {
            \Session::flash('messages', ['errors' => $results['errors']]);
        } else {
            \Session::flash('messages', ['success' => [trans('team::view.Upload successful: :number record', ['number' => $results['count']])]]);
        }
        return $results;
    }

    /**
     * search employee by ajax
     */
    public function listSearchAjax($type = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            Employee::searchAjax(Input::get('q'), array_merge([
                'typeExclude' => $type,
            ], Input::get()))
        );
    }

    /**
     * search employee by ajax
     */
    public function listSearchAjaxExternal($type = null)
    {
        if (!app('request')->ajax()) {
            return redirect('/');
        }
        return response()->json(
            Employee::searchAjaxExternal(Input::get('q'), Input::get())
        );
    }

    /*
     * export member
     */
    public function exportMembers(Request $request)
    {
        $columns = $request->get('columns');
        if (!$columns) {
            return redirect()
                    ->back()
                    ->withInput()
                    ->with('messages', ['errors' => [trans('team::export.chose_column_to_export')]]);
        }
        $dataExport = ExportMember::getDataExport($request->all());
        if (in_array('project_members.type', $columns)) {
            $dataExport = ExportMember::setNameRoles($dataExport);
        }

        try {
            return [
                'colsHead' => ExportMember::getColsHeading($request->get('columns')),
                'sheetsData' => [
                    'Members' => $dataExport
                ],
                'fileName' => 'Team_Members_' . \Carbon\Carbon::now()->now()->format('Y_m_d')
            ];
        } catch (\Exception $ex) {
            return response()->json(trans('team::export.An error occurred, please try again later!'), 500);
        }
    }

    /*
     * export member relationship family.
     *
     * @param $request.
     * @return void.
     */
    public function exportMemberRelationship(Request $request)
    {
        $idTeam = (int)$request->input('dataTeamId');
        $statusWork = $request->input('statusWork');
        $urlFilter = URL::route('team::team.member.index') . '/' . $statusWork . '/';
        $listEmployee = [];
        switch ($statusWork) {
            case 'leave':
                $listEmployee = Team::getMemberGridData($idTeam, Team::END_WORK, $urlFilter, ['isListPage' => true, 'export' => true]);
                break;
            case 'all':
                $listEmployee = Team::getMemberGridData($idTeam, null, $urlFilter, ['isListPage' => true, 'export' => true]);
                break;
            default: // work
                $listEmployee = Team::getMemberGridData($idTeam, Team::WORKING, $urlFilter, ['isListPage' => true, 'export' => true]);
                break;
        }
        $arrayEmpIdSelect = [];
        if ($listEmployee) {
            foreach ($listEmployee as $item) {
                $arrayEmpIdSelect[] = $item->id;
            }
        }
        if (!isset($arrayEmpIdSelect[0])) {
            return redirect(route('team::team.member.index'))->with('status', trans('team::messages.export error!'));
        }
        $toOptionsRelation = RelationNames::getAllRelations();
        if (isset($arrayEmpIdSelect)) {
            $listRelationship = EmployeeRelationship::getListRelationshipEmp($arrayEmpIdSelect);
            // change relationship.
            foreach ($listRelationship as $key => $item) {
                $listRelationship[$key]['r_relationship'] = View::getValueArray($toOptionsRelation, [$item['r_relationship']]);
            }
            Excel::create('relationship_family_employee', function ($excel) use ($listRelationship) {
                $excel->sheet('Sheet1', function ($sheet) use ($listRelationship) {
                    $data = [];
                    $data[0] = [
                        trans('team::export.employee_code'),
                        trans('team::export.name'),
                        trans('team::export.name_relationship'),
                        trans('team::export.relationship'),
                        trans('team::export.birthday'),
                        trans('team::export.contact.mobile_phone'),
                        trans('team::export.id_card_number')
                    ];
                    foreach ($listRelationship as $key => $item) {
                        $data[] = [
                            $item['employee_code'],
                            $item['name'],
                            $item['r_name'],
                            $item['r_relationship'],
                            $item['date_of_birth'],
                            $item['r_mobile'],
                            $item['r_id_number']
                        ];
                    }
                    $sheet->fromArray($data, null, 'A1', true, false);
                    $sheet->cells('A1:G1', function ($cells) {
                        $cells->setFontWeight('bold');
                        $cells->setBackground('#D3D3D3');
                        $cells->setAlignment('center');
                        $cells->setValignment('center');
                    });

                    $countData = count($data);
                    $sheet->cells("F3:G{$countData}", function ($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                    });
                    $sheet->cells("F2:G{$countData}", function ($cells) {
                        $cells->setAlignment('right');
                        $cells->setValignment('center');
                    });
                    $sheet->cells("B3:D{$countData}", function ($cells) {
                        $cells->setAlignment('left');
                        $cells->setValignment('center');
                    });

                    $sheet->setHeight([
                        1     =>  30,
                        2     =>  25
                    ]);
                    $sheet->setWidth(array(
                        'A' => 20,
                        'B' => 30,
                        'C' => 30,
                        'D' => 30,
                        'E' => 25,
                        'F' => 25,
                        'G' => 25,
                    ));

                    $sheet->setBorder('A1:G1', 'thin');
                });
                $excel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
            })->download('xlsx');
        }
    }


    /**
     * get employee information
     * @param Request $request
     * @return object
     */
    public function getEmployeeInfo(Request $request)
    {
        $cols = $request->get('cols');
        if (!$cols) {
            $cols = ['*'];
        }
        $employeeId = $request->get('employee_id');
        if (!$employeeId || !($employee = Employee::find($employeeId, $cols))) {
            return response()->json(trans('team::messages.Not found item'), 422);
        }

        return $employee;
    }
}
