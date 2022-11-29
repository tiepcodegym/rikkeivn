<?php

namespace Rikkei\Team\Model;

use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Lang;
use Rikkei\Assets\Model\RequestAsset;
use Rikkei\Assets\Model\RequestAssetHistory;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\Model\User;
use Rikkei\Core\View\CacheBase;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\TimeHelper;
use Rikkei\Core\View\View;
use Rikkei\Education\Model\EducationClassDetail;
use Rikkei\Education\Model\EducationClassShift;
use Rikkei\ManageTime\Model\LeaveDay;
use Rikkei\ManageTime\Model\LeaveDayBack;
use Rikkei\ManageTime\Model\LeaveDayHistories;
use Rikkei\ManageTime\Model\WorkingTimeDetail;
use Rikkei\ManageTime\Model\WorkingTimeRegister;
use Rikkei\ManageTime\View\LeaveDayPermission;
use Rikkei\ManageTime\View\ManageTimeConst;
use Rikkei\ManageTime\View\View as ManageTimeView;
use Rikkei\Project\Model\TaskAssign;
use Rikkei\Project\Model\TeamProject;
use Rikkei\Recruitment\Model\RecruitmentApplies;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\View\View as ResourceView;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Team;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Config;
use Rikkei\Team\View\EmpLib;
use Rikkei\Team\View\Permission as PermissionView;
use Rikkei\Team\View\TeamConst;
use Rikkei\Team\View\TeamList;
use Symfony\Component\Routing\Route;
use Rikkei\Team\Scopes\SmScope;

class Employee extends CoreModel
{

    use SoftDeletes;

    /*
     * flag value gender
     */

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 0;

    /*
     * flag value marital
     */
    const MARITAL_SINGLE = 0;
    const MARITAL_MARRIED = 1;
    const MARITAL_WIDOWED = 2;
    const MARITAL_SEPARATED = 3;

    /*
     * flag employee code data
     */
    const CODE_PREFIX = 'RK';
    const CODE_LENGTH = 5;

    /*
     * key store cache
     */
    const KEY_CACHE = 'employee';
    const KEY_CACHE_PERMISSION_TEAM_ROUTE = 'team_rule_route';
    const KEY_CACHE_PERMISSION_TEAM_ACTION = 'team_rule_action';
    const KEY_CACHE_PERMISSION_ROLE_ROUTE = 'role_rule_route';
    const KEY_CACHE_PERMISSION_ROLE_ACTION = 'role_rule_action';
    const KEY_CACHE_LEADER_PQA = 'leader_pqa';
    const KEY_CACHE_COUNT_EMP_MONTH = 'count_emp_month';
    const KEY_CACHE_LIB_FOLK = 'lib_folk';

    /**
     * Type exclude when search employee by ajax
     * function searchAjax()
     */
    const EXCLUDE_REVIEWER = 'reviewer';
    const EXCLUDE_UTILIZATION = 'utilization';

    /**
     *
     * path attach folder
     */
    const ATTACH_FOLDER = 'resource/employee/attach/';
    const AVATAR_FOLDER = 'resource/employee/avatar/';

    /**
     * prefix CV
     * @var type
     */
    const PRE_CV = 'CV';

    /*
     * define route.
     */
    const ROUTE_VIEW_SKILLSHEET = 'team::member.profile.skillsheet';
    const ROUTE_EDIT_ROLE = 'team::member.profile.edit.roles';

    const DATE_MAX = '9999-12-31';
    /**
     * Status validity
     */
    const STATUS_VALIDITY_ALL = 1;
    const STATUS_VALIDITY = 2;
    const STATUS_INVALIDITY = 3;
    const STATUS_NOT_INPUT = 4;

    // Role employee is trainer
    const ROLE_TEACHER = 2;
    // Role employee is student
    const ROLE_STUDENT = 1;
    // All role student adn teacher
    const ROLE_All = 0;

    const MINUTE_OF_HOURS = 60;

    protected $table = 'employees';

    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'employee_card_id',
        'employee_code',
        'name',
        'japanese_name',
        'birthday',
        'nickname',
        'email',
        'join_date',
        'trial_date',
        'trial_end_date',
        'offcial_date',
        'leave_date',
        'leave_reason',
        'personal_email',
        'mobile_phone',
        'home_phone',
        'gender',
        'address',
        'home_town',
        'id_card_number',
        'id_card_place',
        'id_card_date',
        'passport_number',
        'passport_date_start',
        'passport_date_exprie',
        'passport_addr',
        'recruitment_apply_id',
        'skype',
        'marital',
        'folk',
        'religion',
        'state',
        'working_type',
        'contract_length',
        'account_status',
        'country_id',
        'cache_version'
    ];

    public function familyRelationships()
    {
        return $this->hasMany(EmployeeRelationship::class, 'employee_id', 'id');
    }

    public function educations()
    {
        return $this->hasMany(EmployeeEducation::class, 'employee_id', 'id');
    }
    public function businesses()
    {
        return $this->hasMany(EmployeeBusiness::class, 'employee_id', 'id');
    }

    public function scanExhibits()
    {
        return $this->hasMany(EmployeeAttach::class, 'employee_id', 'id');
    }

    public function rewards()
    {
        return $this->hasMany(EmployeePrize::class, 'employee_id', 'id');
    }


    public static function updatePersonalEmail()
    {
        $dataEmail = EmployeeContact::join('candidates', 'candidates.employee_id', '=', 'employee_contact.employee_id')
                ->whereNotNull('candidates.email')
                ->whereNotNull('candidates.employee_id')
                ->join('employees', 'employees.id', '=', 'employee_contact.employee_id')
                ->whereNull('employees.deleted_at')
                ->select([
                    'candidates.email',
                    'employee_contact.personal_email',
                    'employees.id',
                ])
                ->limit(10)->get();
        foreach ($dataEmail as $item) {
            $contact = EmployeeContact::where('employee_id', $item->id)->first();
            if ($contact) {
                $contact->personal_email = $item->email;
                $contact->save();
            }
        }
    }

    /**
     * get collection to show grid
     *
     * @return type
     */
    public static function getGridData()
    {
        $pager = Config::getPagerData();
        $collection = self::select('id', 'name', 'email', 'employee_code')
                ->orderBy($pager['order'], $pager['dir']);
        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * rewrite save model employee
     *
     * @param array $options
     */
    public function save(array $options = array(), $config = [])
    {
        DB::beginTransaction();
        try {
//            $this->beforeSave();
            $result = parent::save($options);

            $candidate = Candidate::getCandidateByEmployee($this->id);
            if ($candidate) {
//                if (!$candidate->is_old_employee) {
//                    $isWorkingTypeExternel = in_array($candidate->working_type, getOptions::workingTypeExternal());
//                    if (!$isWorkingTypeExternel) {
//                        $candidate->trial_work_start_date = $this->trial_date ? Carbon::parse($this->trial_date)->toDateString() : null;
//                        $candidate->trial_work_end_date = $this->trial_end_date ? Carbon::parse($this->trial_end_date)->toDateString() : null;
//                    }
//                    if (in_array($candidate->working_type, getOptions::workingTypeOfficial())) {
//                        $candidate->official_date = $this->join_date ? Carbon::parse($this->join_date)->toDateString() : null;
//                    }
//                }
                //update leaved off status
                $isWorking = false;
                if ($this->leave_date) {
                    $leaveData = Carbon::parse($this->leave_date);
                    $dateNow = Carbon::now()->startOfDay();
                    if ($dateNow->gt($leaveData)) {
                        $candidate->status = getOptions::LEAVED_OFF;
                    } else {
                        $isWorking = true;
                    }
                } else {
                    $isWorking = true;
                }
                if ($isWorking && $candidate->status == getOptions::LEAVED_OFF) {
                    $candidate->status = getOptions::WORKING;
                }
                if ($candidate->working_type == getOptions::WORKING_BORROW) {
                    $candidate->end_working_date = $this->leave_date;
                }
                $candidate->save();
            }
            LeaveDay::cronJobUpdateLeaveDayTrialToOffcial($this->id);
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE);
            return $result;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * before save employee
     */
    public function beforeSave()
    {
        if (!isset($this->original['join_date']) ||
                (isset($this->original['join_date']) &&
                isset($this->attributes['join_date']) &&
                $this->join_date &&
                $this->attributes['join_date'] !== $this->original['join_date'])
        ) {
            $candidate = Candidate::select(['id', 'start_working_date', 'employee_id'])
                    ->where('employee_id', $this->id)
                    ->first();
            if ($candidate) {
                $candidate->start_working_date = $this->join_date;
                $candidate->save();
            }
        }
    }

    /**
     * save recruitment apply id follow phone
     *
     * @return Employee
     */
    public function saveRecruitmentAppyId()
    {
        if ($this->recruitment_apply_id || !$this->mobile_phone) {
            return;
        }
        $recruitment = RecruitmentApplies::select('id')->where('phone', $this->mobile_phone)->first();
        if ($recruitment) {
            $this->recruitment_apply_id = $recruitment->id;
        }
        return $this;
    }

    /**
     * save team position for member
     *
     * @param array $teamPostions
     * @return void
     * @throws Exception
     */
    public function saveTeamPosition(array $teamPostions = [])
    {
        if (!$this->id) {
            return;
        }

        $dataValidate = $this->validateSaveTeamPosition($teamPostions);
        if (is_string($dataValidate)) {
            throw new Exception($dataValidate, self::ERROR_CODE_EXCEPTION);
        }
        list($inputTeamIds, $teamPositions, $aryTeam, $teamIdRemoves) = $dataValidate;
        $inputTeamIds = array_diff($inputTeamIds, $teamIdRemoves);

        DB::beginTransaction();
        try {
            // update table leave_days when employee change team to japan
            $objTeamList = new TeamList();
            $teamIds = array_diff($inputTeamIds, $objTeamList->getDeletedTeamIds($inputTeamIds));

            $oldTeamMembers = TeamMember::getTeamMembersByEmployees($this->id, ['team_id','employee_id', 'role_id']);
            $teamIdOlds = $oldTeamMembers->pluck('team_id')->toArray();
            $teamIdDiffs = array_diff($teamIds, $teamIdOlds);
            // if change team
            if ($teamIdDiffs || array_diff($teamIdOlds, $teamIds)) {
                event(new \Rikkei\Core\Events\DBEvent('updated', self::getTableName(), [
                    'id' => $this->id,
                    'old' => [],
                    'new' => ['change_team' => true]
                ], false));
            }
            $teamPrefix = Team::getOnlyOneTeamCodePrefixChange(Employee::find($this->id));
            if ($teamIdOlds && $teamPrefix != Team::CODE_PREFIX_JP &&
            Employee::checkExistTeam($teamIdDiffs, Team::CODE_PREFIX_JP)) {
                $leaveDay = LeaveDay::where('employee_id', '=', $this->id)->first();
                if ($leaveDay) {
                    $remainDay = $leaveDay->day_last_transfer + $leaveDay->day_current_year + $leaveDay->day_seniority + $leaveDay->day_ot - $leaveDay->day_used;
                    if ($remainDay > LeaveDay::LEAVE_SIX_MONTH) {
                        $remainDayNew = LeaveDay::LEAVE_SIX_MONTH;
                    } else {
                        $remainDayNew = $remainDay;
                    }
                    $data = [
                        "leave_day_id" => $leaveDay->id,
                        "day_last_year" => $leaveDay->day_last_year,
                        "day_last_transfer" => $leaveDay->day_last_transfer,
                        "day_current_year" => $leaveDay->day_current_year,
                        "day_seniority" => $leaveDay->day_seniority,
                        "day_ot" => $leaveDay->day_ot,
                        "day_used" => $leaveDay->day_used,
                        "note" => $leaveDay->note,
                        "created_at" => $leaveDay->created_at,
                        "updated_at" => $leaveDay->updated_at,
                    ];
                    $leaveBack = LeaveDayBack::where('leave_day_id', $leaveDay->id)->first();
                    if ($leaveBack) {
                        $leaveBack->update($data);
                    } else {
                        LeaveDayBack::insert($data);
                    }

                    $leaveDayPermis = new LeaveDayPermission();
                    $change['day_vietnam_japan'] = [
                        "old" => $remainDay,
                        "new" => $remainDayNew
                    ];
                    $leaveDayPermis->saveHistory($this->id, $change, LeaveDayHistories::TYPE_VIETNAM_JAPAN);

                    $leaveDay->day_last_year = 0.0;
                    $leaveDay->day_last_transfer = 0.0;
                    $leaveDay->day_current_year = $remainDayNew;
                    $leaveDay->day_seniority = 0.0;
                    $leaveDay->day_ot = 0.0;
                    $leaveDay->day_used = 0.0;
                    $leaveDay->note = Lang::get('team::view.Team vietnam change team japan');
                    $leaveDay->save();
                }
            }

            // set null leader id before set leader new
            Team::where('leader_id', $this->id)->update(['leader_id' => null]);
            $leaderPosId = (string) Role::getPositionLeader(); // id role team leader

            $aryNewTeamMember = []; // final records in table team members
            $now = Carbon::now()->toDateString();
            $oldTeamHistory = EmployeeTeamHistory::getCurrentTeamsHistory($this->id);
            $aryInsertTeamHistory = [];
            foreach ($teamPositions as $teamPostion) {
                $teamId = $teamPostion['team'];
                $position = $teamPostion['position'];
                $endAt = $teamPostion['_end_at'];
                $startAt = $teamPostion['start_at'];
                $isWorking = $teamPostion['is_working'];
                $team = $aryTeam[$teamId];

                if ($position === $leaderPosId && $endAt >= $now) { //position is leader
                    if ($team->leader_id === (string) $this->id) {
                        $team->original['leader_id'] = null;
                        $team->leader_id = null;
                    }
                    $teamLeader = $team->getLeader();
                    if (Team::MAX_LEADER === 1 && $teamLeader && $teamLeader->id != $this->id) { //flag team only have 1 leader
                        throw new Exception(Lang::get('team::messages.Team :name had :nameleader leader!', ['name' => htmlentities($team->name), 'nameleader' => htmlentities($team->leaderInfo->name)]), self::ERROR_CODE_EXCEPTION);
                    }
                    if (!$teamLeader) { //save leader for team
                        $team->leader_id = $this->id;
                        $team->save();
                    }
                }
                $dataTeamMember = ['team_id' => $teamId, 'employee_id' => $this->id, 'role_id' => $position];
                if ($endAt > $now && !in_array($dataTeamMember, $aryNewTeamMember)) {
                    $aryNewTeamMember[] = $dataTeamMember;
                }
                // check exist team history
                $flgEqual = true;
                foreach ($oldTeamHistory as $key => $item) {
                    $teamEndAt = $item['end_at'] === null ? self::DATE_MAX : substr($item['end_at'], 0, 10);
                    $teamStartAt = substr($item['start_at'], 0, 10);
                    if ($teamEndAt === $endAt && $teamStartAt === $startAt && $item->role_id === $position
                        && $item->is_working === $isWorking && $item->team_id === $teamId) {
                        $flgEqual = false;
                        $oldTeamHistory->forget($key);
                        break;
                    }
                }
                if ($flgEqual) {
                    $dataTeamMember['start_at'] = $startAt;
                    $dataTeamMember['end_at'] = $teamPostion['end_at'];
                    $dataTeamMember['is_working'] = $isWorking;
                    $aryInsertTeamHistory[] = $dataTeamMember;
                }
            }
            $this->updateEmpTeamHistory($oldTeamHistory, $aryInsertTeamHistory);
            $this->updateTeamMember($oldTeamMembers->toArray(), $aryNewTeamMember);

            CacheBase::forgetFile(CacheBase::EMPL_PERMIS, $this->id);
            CacheBase::forgetFilePrefix(CacheBase::MENU_USER, $this->id);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * validate data before save
     *
     * @param array $teamPositions
     * @return array|string - {array data|string exception}
     */
    public function validateSaveTeamPosition($teamPositions)
    {
        if (!$teamPositions) {
            $teamPositions = (array) Input::get('team');
            if (isset($teamPositions[0])) {
                unset($teamPositions[0]);
            }
            if (!$teamPositions) {
                return Lang::get('team::view.Employee must belong to at least one team');
            }
        }
        // remove team have _end_at < dateNow
        $dateNow = Carbon::now();
        $teamIdRemoves = [];
        foreach($teamPositions as $key => $item) {
            if ($item['end_at'] && $item['end_at'] <= $dateNow->format("Y-m-d")) {
                $teamIdRemoves[] = $item['team'];
            }
        }

        $keys = [];
        $inputTeamIds = [];
        $roleIds = Role::where('special_flg', Role::FLAG_POSITION)->pluck('id', 'id')->toArray();
        $formatDate = 'Y-m-d';
        $countIsWorking = 0;

        //check miss data and custom data
        foreach ($teamPositions as $key => $team) {
            $teamId = isset($team['team']) ? $team['team'] : null;
            $position = isset($team['position']) ? $team['position'] : null;
            $startAt = isset($team['start_at']) ? $team['start_at'] : null;
            $endAt = isset($team['end_at']) ? $team['end_at'] : null;
            // type not format
            if (!is_string($teamId) || !is_string($position) || !is_string($startAt) || !is_string($endAt)) {
                return Lang::get('team::messages.Data is incorrect');
            }

            $startAt = trim($startAt);
            $objStartAt = \DateTime::createFromFormat($formatDate, $startAt);
            $endAt = trim($endAt);
            $objEndAt = $endAt ? \DateTime::createFromFormat($formatDate, $endAt) : null;
            // date not format
            if (!$objStartAt || $objStartAt->format($formatDate) !== $startAt
                || $endAt && (!$objEndAt || $objEndAt->format($formatDate) !== $endAt)) {
                return Lang::get('team::messages.Date data is incorrect');
            }

            if (empty($teamId) || empty($position)) {
                return Lang::get('team::view.Miss data team or position');
            }
            $position = trim($position);
            if (!isset($roleIds[$position])) {
                return Lang::get('team::messages.There exists a position not found');
            }
            /* custom data */
            $team['team'] = trim($teamId);
            $team['position'] = $position;
            if (!empty($team['is_working']) && $team['is_working'] === 'on') {
                $team['is_working'] = '1';
                $countIsWorking++;
            } else {
                $team['is_working'] = '0';
            }
            $team['end_at'] = null;
            $team['_end_at'] = self::DATE_MAX;
            if (!empty($endAt)) {
                $team['end_at'] = $endAt;
                $team['_end_at'] = $endAt;
            }
            $teamPositions[$key] = $team;
            $keys[] = $key;
            $inputTeamIds[] = $teamId;
        }

        // not team working or many team working
        if ($countIsWorking !== 1) {
            return Lang::get('team::messages.You need to choose a team with a working status');
        }

        //check data team not same
        $teamLength = count($teamPositions);
        for ($i = 0; $i < $teamLength; $i++) {
            $prevTeam = $teamPositions[$keys[$i]];
            if ($prevTeam['_end_at'] <= $prevTeam['start_at']) {
                return Lang::get('manage_time::view.The end date at must be after start date');
            }
            for ($j = $i + 1; $j < $teamLength; $j++) {
                $nextTeam = $teamPositions[$keys[$j]];
                if ($prevTeam['team'] !== $nextTeam['team']) {
                    continue;
                }
                if ($prevTeam['_end_at'] === self::DATE_MAX && $nextTeam['_end_at'] === self::DATE_MAX) {
                    return Lang::get('team::view.Team same data');
                }
                // period time overlap
                if ($prevTeam['start_at'] <= $nextTeam['_end_at'] && $prevTeam['_end_at'] >= $nextTeam['start_at']) {
                    return Lang::get('team::messages.Date data is incorrect');
                }
            }
        }

        $inputTeamIds = array_unique($inputTeamIds);
        $teams = Team::withTrashed()->withoutGlobalScope(SmScope::class)->whereIn('id', $inputTeamIds)->get();
        // exists a team not found
        if (count($inputTeamIds) > count($teams)) {
            return Lang::get('team::messages.There exists a team not found');
        }

        $aryTeam = [];
        // team not is function
        foreach ($teams as $team) {
            if (!$team->isFunction()) {
                return Lang::get('team::messages.Team :name isnot function', ['name' => $team->name]);
            }
            $aryTeam[$team->id] = $team;
        }

        return [$inputTeamIds, $teamPositions, $aryTeam, $teamIdRemoves];
    }

    /**
     * Update team member
     *
     * @param array $oldData
     * @param array $newData
     * @return void
     */
    public function updateTeamMember($oldData, $newData)
    {
        // insert new record team member
        $dataInsert = $this->diffArray2D($newData, $oldData);
        if ($dataInsert) {
            foreach ($dataInsert as $key => $item) {
                $now = Carbon::now()->toDateTimeString();
                $dataInsert[$key]['created_at'] = $now;
                $dataInsert[$key]['updated_at'] = $now;
            }
            TeamMember::insert($dataInsert);
        }
        // delete record team member
        $dataDelete = $this->diffArray2D($oldData, $newData);
        if ($dataDelete) {
            $collection = TeamMember::where('employee_id', $this->id);
            $collection->where(function ($q1) use ($dataDelete) {
                foreach ($dataDelete as $item) {
                    $q1->orWhere(function ($q2) use ($item) {
                        $q2->where('team_id', $item['team_id'])
                            ->where('role_id', $item['role_id']);
                    });
                }
            });
            $collection->delete();
        }
    }

    /**
     * Update team history
     *
     * @param array $oldData
     * @param array $newData
     * @return void
     */
    public function updateEmpTeamHistory($oldData, $newData)
    {
        // reuse team for update
        foreach ($newData as $newKey => $newValue) {
            foreach ($oldData as $oldKey => $oldValue) {
                if ($newValue['team_id'] === $oldValue->team_id) {
                    $oldValue->update($newValue);
                    $oldData->forget($oldKey);
                    unset($newData[$newKey]);
                }
            }
        }
        $now = Carbon::now()->toDateTimeString();
        // insert team history
        if ($newData) {
            foreach ($newData as $key => $item) {
                $newData[$key]['created_at'] = $now;
                $newData[$key]['updated_at'] = $now;
            }
            EmployeeTeamHistory::insert($newData);
        }
        // delete team history
        foreach ($oldData as $oldKey => $teamHistory) {
            $teamHistory->deleted_at = $now;
            if (empty($teamHistory->end_at)) {
                $teamHistory->end_at = $now;
            }
            $teamHistory->save();
        }
    }

    /**
     * Update team history
     *
     * @param array $teamPostions
     *
     * @return void
     */
    /*
    public function updateTeamHistory($teamPostions)
    {
        $currentTeam = EmployeeTeamHistory::getCurrentTeamsHistory($this->id);
        $arrCurId = [];
        foreach ($currentTeam as $t) {
            $arrCurId[] = (int)$t->id;
        }
        $arrNewId = [];
        foreach ($teamPostions as $newT) {
            $arrNewId[] = (int)$newT['id'];
        }
        foreach ($arrCurId as $curId) {
            if (!in_array($curId, $arrNewId)) {
                $teamHistory = EmployeeTeamHistory::getCurrentHistoryById($curId);
                $teamHistory->deleted_at = Carbon::now()->format('Y-m-d H:i:s');
                if (empty($teamHistory->end_at)) {
                    $teamHistory->end_at = $teamHistory->deleted_at;
                }
                $teamHistory->save();
            }
        }
        $arrNew = [];
        foreach ($teamPostions as $key => $roleTeam) {
            if (!in_array($roleTeam['id'], $arrCurId)) {
                $teamHistory = new EmployeeTeamHistory();
                $teamHistory->team_id = $roleTeam['team'];
                $teamHistory->role_id = $roleTeam['position'];
                $teamHistory->employee_id = $this->id;
                $teamHistory->start_at = empty($roleTeam['start_at']) ? Carbon::now()->format('Y-m-d H:i:s') : $roleTeam['start_at'];
                $teamHistory->end_at = empty($roleTeam['end_at']) ? null : $roleTeam['end_at'];
                $teamHistory->is_working = (array_key_exists('is_working', $roleTeam) || count($teamPostions) == 1) ? EmployeeTeamHistory::IS_WORKING : EmployeeTeamHistory::END_WORK;
                $teamHistory->save();
                $arrNew[] = ['number' => $key, 'id' => $teamHistory->id, 'team' => $teamHistory->team_id];
            } else {
                $teamHistory = EmployeeTeamHistory::getEmpTeamHistoryById($roleTeam['id']);
                if ($teamHistory) {
                    $teamHistory->role_id = $roleTeam['position'];
                    $teamHistory->start_at = empty($roleTeam['start_at']) ? Carbon::now()->format('Y-m-d H:i:s') : $roleTeam['start_at'];
                    $teamHistory->end_at = empty($roleTeam['end_at']) ? null : $roleTeam['end_at'];
                    $teamHistory->is_working = (array_key_exists('is_working', $roleTeam) || count($teamPostions) == 1) ? EmployeeTeamHistory::IS_WORKING : EmployeeTeamHistory::END_WORK;
                    $teamHistory->save();
                } else {
                    $teamHistory = new EmployeeTeamHistory();
                    $teamHistory->team_id = $roleTeam['team'];
                    $teamHistory->role_id = $roleTeam['position'];
                    $teamHistory->employee_id = $this->id;
                    $teamHistory->start_at = empty($roleTeam['start_at']) ? Carbon::now()->format('Y-m-d H:i:s') : $roleTeam['start_at'];
                    $teamHistory->end_at = empty($roleTeam['end_at']) ? null : $roleTeam['end_at'];
                    $teamHistory->is_working = (array_key_exists('is_working', $roleTeam) || count($teamPostions) == 1) ? EmployeeTeamHistory::IS_WORKING : EmployeeTeamHistory::END_WORK;
                    $teamHistory->save();
                    $arrNew[] = ['number' => $key, 'id' => $teamHistory->id, 'team' => $teamHistory->team_id];
                }
            }
        }
        return $arrNew;
    }
    */

    /**
     * sendMail Employees UnSubmit SkillSheet
     */
    public static function sendMailEmployeesUnSubmitSkillSheet()
    {
        $tableEmplCvAttrValue = EmplCvAttrValue::getTableName();
        $tableTeamMember = TeamMember::getTableName();
        $tableTeam = Team::getTableName();
        $employeeTable = 'employees';
        $currentDay = date("Y-m-d");
        $collections = self::select("{$employeeTable}.email", "{$employeeTable}.name", "{$employeeTable}.id")
            ->whereNull("{$employeeTable}.deleted_at")
            ->whereNotIn("{$employeeTable}.account_status", [getOptions::FAIL_CDD]);
        $collections = $collections->where(function ($query) use ($employeeTable, $currentDay) {
            $query->orWhereDate("{$employeeTable}.leave_date", ">=", $currentDay)
                  ->orWhereNull("{$employeeTable}.leave_date");
        });
        $collections = $collections->leftJoin("{$tableEmplCvAttrValue}", function ($q) use ($employeeTable, $tableEmplCvAttrValue) {
            $q->on("{$employeeTable}.id", '=', "{$tableEmplCvAttrValue}.employee_id")
                ->where("{$tableEmplCvAttrValue}.code", '=', 'status');
        });
        $collections = $collections->leftJoin("{$tableTeamMember}", "{$tableTeamMember}".'.employee_id', '=', "{$employeeTable}.id");
        $collections = $collections->leftJoin("{$tableTeam}","{$tableTeam}.id", '=', "{$tableTeamMember}.team_id")->where("{$tableTeam}.is_soft_dev", '=', Team::IS_SOFT_DEVELOPMENT);
        $collections = $collections->where(function ($query) use ($tableEmplCvAttrValue) {
            $query->whereIn("{$tableEmplCvAttrValue}.value", [0,1])
                  ->orWhere("{$tableEmplCvAttrValue}.value", '=', null);
        })->groupBy('employees.id')->get();
        // send mail
        foreach ($collections as $employee) {
            $emailQueue = new EmailQueue();
            $subject = trans('team::email.Submit skillsheet title');
            $emailQueue->setTo($employee->email, $employee->name)
                ->setTemplate('team::mail.unsubmit_skillsheet', $employee)
                ->setSubject($subject)
                ->save();
        }
    }


    /**
     * save role for employee
     *
     * @param array $roles
     * @throws Exception
     */
    public function saveRoles(array $roles = [])
    {
        if (!$this->id) {
            return;
        }
        if (!$roles) {
            $roles = (array) Input::get('role');
        }
        //if not root or admin then not save role admin
        $roles = array_unique($roles);
        if (!PermissionView::getInstance()->isRootOrAdmin()) {
            if (($key = array_search(Role::roleAdminId(), $roles)) !== false) {
                unset($roles[$key]);
            }
        }

        $oldRoleIds = EmployeeRole::where('employee_id', $this->id)->pluck('role_id', 'role_id')->toArray();
        $countRole = Role::whereIn('id', $roles)->where('special_flg', Role::FLAG_ROLE)->count();
        if (count($roles) > $countRole) {
            throw new Exception(Lang::get('There exists a role not found'), self::ERROR_CODE_EXCEPTION);
        }
        $aryRoleInsert = [];
        $now = Carbon::now()->toDateTimeString();
        foreach ($roles as $roleId) {
            if (isset($oldRoleIds[$roleId])) {
                unset($oldRoleIds[$roleId]);
            } else {
                $aryRoleInsert[] = [
                    'role_id' => $roleId,
                    'employee_id' => $this->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::beginTransaction();
        try {
            // insert new roles
            if ($aryRoleInsert) {
                EmployeeRole::insert($aryRoleInsert);
            }
            // delete roles remove tick
            if ($oldRoleIds) {
                EmployeeRole::where('employee_id', $this->id)->whereIn('role_id', $oldRoleIds)->delete();
            }

            CacheBase::forgetFile(CacheBase::EMPL_PERMIS, $this->id);
            CacheBase::forgetFilePrefix(CacheBase::MENU_USER, $this->id);
            //forget list roles
            CacheHelper::forget(Role::KEY_CACHE_ROLE, $this->id);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    /**
     * set code for employee
     *
     * @return Employee
     */
    public function saveCode($code = null)
    {
        $year = strtotime($this->join_date);
        $year = date('y', $year);
        if ($code) {
            $code = (int) $code;
            if ($code) {
                $lengthCodeCurrent = strlen($code);
                for ($i = 0; $i < self::CODE_LENGTH - $lengthCodeCurrent; $i++) {
                    $code = '0' . $code;
                }
                $code = self::CODE_PREFIX . $year . $code;
                $this->employee_code = $code;
                return $this;
            }
        }
        if ($this->employee_code || !$this->join_date) {
            return;
        }
        $codeLast = self::select('employee_code')
                ->where('employee_code', 'like', self::CODE_PREFIX . $year . '%')
                ->orderBy('employee_code', 'DESC')
                ->first();
        if (!$codeLast) {
            $codeEmployee = self::CODE_PREFIX . $year;
            for ($i = 0; $i < self::CODE_LENGTH - 1; $i++) {
                $codeEmployee .= '0';
            }
            $codeEmployee .= '1';
        } else {
            $codeLast = $codeLast->employee_code;
            $codeEmployee = preg_replace('/^' . self::CODE_PREFIX . $year . '/', '', $codeLast);
            $codeEmployee = (int) $codeEmployee + 1;
            $codeEmployee = (string) $codeEmployee;
            $lengthCodeCurrent = strlen($codeEmployee);
            for ($i = 0; $i < self::CODE_LENGTH - $lengthCodeCurrent; $i++) {
                $codeEmployee = '0' . $codeEmployee;
            }
            $codeEmployee = self::CODE_PREFIX . $year . $codeEmployee;
        }
        $this->employee_code = $codeEmployee;
        return $this;
    }

    /**
     * save skill and experience
     */
    protected function saveSkills()
    {
        if (!$this->id) {
            return;
        }
        $skillsAll = Input::all();
        $skills = array_get($skillsAll, 'employee_skill');
        $skillsChage = array_get($skillsAll, 'employee_skill_change');
        if (!$skills || !$skillsChage) {
            return;
        }
        $skillsArray = [];
        $skillsChageArray = [];
        parse_str($skills, $skillsArray);
        parse_str($skillsChage, $skillsChageArray);

        if (PermissionView::getInstance()->isAllow('team::team.member.edit.skill')) {
            //save school
            if (isset($skillsArray['schools'][0])) {
                unset($skillsArray['schools'][0]);
            }
            if (isset($skillsArray['schools']) &&
                    isset($skillsChageArray['schools']) && $skillsChageArray['schools']) {
                $this->saveSchools($skillsArray['schools']);
            }

            // save language
            if (isset($skillsArray['languages'][0])) {
                unset($skillsArray['languages'][0]);
            }
            if (isset($skillsArray['languages']) &&
                    isset($skillsChageArray['languages']) && $skillsChageArray['languages']) {
                $this->saveCetificateType($skillsArray['languages'], Certificate::TYPE_LANGUAGE);
            }

            // save cetificate
            if (isset($skillsArray['cetificates'][0])) {
                unset($skillsArray['cetificates'][0]);
            }
            if (isset($skillsArray['cetificates']) &&
                    isset($skillsChageArray['cetificates']) && $skillsChageArray['cetificates']) {
                $this->saveCetificateType($skillsArray['cetificates'], Certificate::TYPE_CETIFICATE);
            }

            // save skill
            if (isset($skillsArray['programs'][0])) {
                unset($skillsArray['programs'][0]);
            }
            if (isset($skillsArray['programs']) &&
                    isset($skillsChageArray['programs']) && $skillsChageArray['programs']) {
                //delete old programs
                $proOld = self::getAllProgramOfEmployee($this);
                $this->employeePro()->detach($proOld);
                //insert new programs
                $dataInsert = [];
                foreach ($skillsArray['programs'] as $item) {
                    $empProgram = $item['employee_program'];
                    $program = $item['program'];
                    $dataInsert[] = [
                        'employee_id' => $this->id,
                        'programming_id' => $program['id'],
                        'level' => $empProgram['level'],
                        'experience' => $empProgram['experience'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ];
                }
                if (count($dataInsert)) {
                    EmployeeProgram::saveData($dataInsert);
                }
            }

            if (isset($skillsArray['oss'][0])) {
                unset($skillsArray['oss'][0]);
            }
            if (isset($skillsArray['oss']) &&
                    isset($skillsChageArray['oss']) && $skillsChageArray['oss']) {
                $this->saveSkillItem($skillsArray['oss'], Skill::TYPE_OS);
            }

            if (isset($skillsArray['databases'][0])) {
                unset($skillsArray['databases'][0]);
            }
            if (isset($skillsArray['databases']) &&
                    isset($skillsChageArray['databases']) && $skillsChageArray['databases']) {
                $this->saveSkillItem($skillsArray['databases'], Skill::TYPE_DATABASE);
            }
        }

        if (PermissionView::getInstance()->isAllow('team::team.member.edit.exerience')) {
            //save work experience
            if (isset($skillsArray['work_experiences'][0])) {
                unset($skillsArray['work_experiences'][0]);
            }
            if (isset($skillsArray['work_experiences']) &&
                    isset($skillsChageArray['work_experiences']) && $skillsChageArray['work_experiences']) {
                $this->saveWorkExperience($skillsArray['work_experiences']);
            }

            //save project experience
            if (isset($skillsArray['project_experiences'][0])) {
                unset($skillsArray['project_experiences'][0]);
            }
            if (isset($skillsArray['project_experiences']) &&
                    isset($skillsChageArray['project_experiences']) && $skillsChageArray['project_experiences']) {
                $this->saveProjectExperience($skillsArray['project_experiences']);
            }
        }
    }

    /**
     * The users that belong to the action.
     */
    public function employeePro()
    {
        $tableEmployeePro = EmployeeProgram::getTableName();
        return $this->belongsToMany('Rikkei\Resource\Model\Programs', $tableEmployeePro, 'employee_id', 'programming_id');
    }

    /**
     * Get all program of employee
     *
     * @parram Employee $employee
     * @return array
     */
    public static function getAllProgramOfEmployee($employee)
    {
        $pros = array();
        foreach ($employee->employeePro as $pro) {
            array_push($pros, $pro->id);
        }
        return $pros;
    }

    /**
     * save schools item
     *
     * @param array $schools
     * @return array
     */
    protected function saveSchools($schools = [])
    {
        $schoolIds = School::saveItems($schools);
        $this->saveEmployeeSchools($schoolIds, $schools);
    }

    /**
     * save employee school
     *
     * @param type $schoolIds
     * @param type $schools
     * @return type
     */
    protected function saveEmployeeSchools($schoolIds = [], $schools = [])
    {
        return EmployeeSchool::saveItems($this->id, $schoolIds, $schools);
    }

    /**
     * save certificate item
     *
     * @param array $cetificatesType
     * @param type $type
     * @return type
     */
    protected function saveCetificateType($cetificatesType = [], $type = null)
    {
        $cetificatesTypeIds = Certificate::saveItems($cetificatesType, $type);
        $this->saveEmployeeCetificateType($cetificatesTypeIds, $cetificatesType, $type);
    }

    /**
     * save employee cetificate
     *
     * @param type $cetificatesTypeIds
     * @param type $cetificatesType
     * @return type
     */
    protected function saveEmployeeCetificateType($cetificatesTypeIds = [], $cetificatesType = [], $type = null)
    {
        return EmployeeCertificate::saveItems($this->id, $cetificatesTypeIds, $cetificatesType, $type);
    }

    /**
     * save skills
     *
     * @param array $skills
     * @param type $type
     * @return type
     */
    protected function saveSkillItem($skills = [], $type = null, $profile = false)
    {
        $skillIds = Skill::saveItems($skills, $type);
        $this->saveEmployeeSkillItem($skillIds, $skills, $type, $profile);
    }

    /**
     * save employee skills
     *
     * @param type $cetificatesTypeIds
     * @param type $cetificatesType
     * @return type
     */
    protected function saveEmployeeSkillItem($skillIds = [], $skills = [], $type = null, $profile = false)
    {
        return EmployeeSkill::saveItems($this->id, $skillIds, $skills, $type, $profile);
    }

    /**
     * save work experience for employee
     *
     * @param type $workExperienceData
     * @return type
     */
    protected function saveWorkExperience($workExperienceData)
    {
        return WorkExperience::saveItems($this->id, $workExperienceData);
    }

    /**
     * save project experience
     *
     * @param type $projectExperienceData
     * @return type
     */
    protected function saveProjectExperience($projectExperienceData)
    {
        return ProjectExperience::saveItems($this->id, $projectExperienceData);
    }

    /**
     * get team and position of employee
     *
     * @return collection
     */
    public function getTeamPositons()
    {
        return TeamMember::select('team_id', 'role_id')
                        ->where('employee_id', $this->id)
                        ->get();
    }

    /**
     * get team permission of this employee
     * @return array
     */
    public function getTeamPermissFollow()
    {
        return TeamMember::select(
                'team.id as team_id',
                DB::raw('IF(team.follow_team_id IS NULL OR team.follow_team_id = 0, team.id, team.follow_team_id) as team_permiss_id'),
                'tmb.role_id',
                'team.is_function'
            )
            ->withoutGlobalScope(SmScope::class)
            ->from(TeamMember::getTableName() . ' as tmb')
            ->join(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
            ->where('tmb.employee_id', $this->id)
            ->groupBy('tmb.team_id', 'tmb.employee_id')
            ->get();
    }

    /**
     * get schools of employee
     *
     * @return model
     */
    public function getSchools()
    {
        if ($employeeSchools = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSchools;
        }
        $employeeSchools = EmployeeSchool::getItemsFollowEmployee($this->id);
        CacheHelper::put(self::KEY_CACHE, $employeeSchools, $this->id);
        return $employeeSchools;
    }

    /**
     * get language of employee
     *
     * @return model
     */
    public function getLanguages()
    {
        if ($employeeLanguages = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeLanguages;
        }
        $employeeLanguages = EmployeeCertificate::getItemsFollowEmployee($this->id, Certificate::TYPE_LANGUAGE);
        CacheHelper::put(self::KEY_CACHE, $employeeLanguages, $this->id);
        return $employeeLanguages;
    }

    /**
     * get cetificate of employee
     *
     * @return model
     */
    public function getCetificates()
    {
        if ($employeeCetificates = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeCetificates;
        }
        $employeeCetificates = EmployeeCertificate::getItemsFollowEmployee($this->id, Certificate::TYPE_CETIFICATE);
        CacheHelper::put(self::KEY_CACHE, $employeeCetificates, $this->id);
        return $employeeCetificates;
    }

    /**
     * get programs of employee
     *
     * @return model
     */
    public function getPrograms()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeSkill::getItemsFollowEmployee($this->id, Skill::TYPE_PROGRAM);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }

    /**
     * get database of employee
     *
     * @return model
     */
    public function getDatabases()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeSkill::getItemsFollowEmployee($this->id, Skill::TYPE_DATABASE);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }

    /**
     * get os of employee
     *
     * @return model
     */
    public function getOss()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeSkill::getItemsFollowEmployee($this->id, Skill::TYPE_OS);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }

    /**
     * get work experience of employee
     *
     * @return model
     */
    public function getWorkExperience()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = WorkExperience::getItemsFollowEmployee($this->id);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }

    /**
     * get project experience of employee
     *
     * @return model
     */
    public function getProjectExperience()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = ProjectExperience::getItemsFollowEmployee($this->id);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }

    /**
     * get roles of employee
     *
     * @return collection
     */
    public function getRoles()
    {
        if ($employeeRole = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeRole;
        }
        $employeeRole = EmployeeRole::select('role_id', 'role')
                ->join('roles', 'roles.id', '=', 'employee_roles.role_id')
                ->where('employee_id', $this->id)
                ->orderBy('role')
                ->get();
        if ($this->id) {
            CacheHelper::put(self::KEY_CACHE, $employeeRole, $this->id);
        }
        return $employeeRole;
    }

    /**
     * get roles of employee
     *
     * @return collection
     */
    public function getRoleIds()
    {
        /* if ($employeeRole = CacheHelper::get(self::KEY_CACHE, $this->id)) {
          return $employeeRole;
          } */
        $employeeRole = EmployeeRole::select('role_id')
                ->where('employee_id', $this->id)
                ->get();
        /* if ($this->id) {
          CacheHelper::put(self::KEY_CACHE, $employeeRole, $this->id);
          } */
        return $employeeRole;
    }

    /**
     * get model item relate of employee
     *
     * @param type $type
     * @return \Rikkei\Team\Model\class
     */
    public function getItemRelate($type)
    {
        $class = 'Rikkei\Team\Model\Employee' . ucfirst($type);
        if (!class_exists($class)) {
            $class = EmployeeContact::class;
        }
        if ($item = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $item;
        }
        $item = $class::find($this->id);
        if (!$item) {
            $item = new $class();
        }
        CacheHelper::put(self::KEY_CACHE, $item, $this->id);
        return $item;
    }

    /*
     * get field type of employee
     */

    public function getFieldVal($type, $field)
    {
        $typeModel = $this->getItemRelate($type);
        if (!$typeModel) {
            return null;
        }
        return $typeModel->{$field};
    }

    /**
     * get Hobby by employeeId
     * @return model
     */
    public function getHobby()
    {
        $employeeHobby = EmployeeHobby::getItemFollowEmployee($this->id);
        return $employeeHobby;
    }

    /**
     * get Costume by employeeId
     * @return EmployeeCostume Description
     */
    public function getCostume()
    {
        $employeeCostume = EmployeeCostume::getEmpById($this->id);
        return $employeeCostume;
    }

    /**
     * get Politic by employeeId
     * @return EmployeePolitic Description
     */
    public function getPolitic()
    {
        $employeePolitic = EmployeePolitic::getEmpById($this->id);
        return $employeePolitic;
    }

    /**
     * get Military model by employeeId
     * @return EmployeeMilitary Description
     */
    public function getMilitary()
    {
        $employeeMilitary = EmployeeMilitary::getModelByEmplId($this->id);
        return $employeeMilitary;
    }

    /**
     * get relationships of employee
     *
     * @return model
     */
    public function getRelations()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeSkill::getItemsFollowEmployee($this->id, Skill::TYPE_PROGRAM);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }

    /**
     * convert collection model to array with key is name column
     *
     * @param model $collection
     * @param string $collection
     * @return array
     */
    protected static function formatArray($collection, $key = null)
    {
        if (!$collection instanceof Arrayable) {
            return [];
        }
        $collectionArray = $collection->toArray();
        if (!$key) {
            return $collectionArray;
        }
        $result = [];
        foreach ($collectionArray as $item) {
            $result[$item[$key]] = $item;
        }
        return $result;
    }

    /**
     * Check employee has permission
     *
     * @param string|null $route     route check permission
     *
     * @return boolean
     */
    public function hasPermission($route = null)
    {
        if (!$route) {
            $route = Route::getCurrentRoute()->getName();
        }
        $permissions = $this->getPermission();
        if (!count($permissions)) {
            return false;
        }
        if (isset($permissions['team']['route'])) {
            foreach ($permissions['team']['route'] as $teamRoute) {
                if (array_key_exists($route, $teamRoute)) {
                    return true;
                }
            }
        }
        return isset($permissions['role']['route']) && array_key_exists($route, $permissions['role']['route']);
    }

    /**
     * get permission of employee
     * result = array route name and action id allowed follow each team
     *
     * @return array
     */
    public function getPermission($withTrashed = false)
    {
        $permissionTeam = $this->getPermissionTeam($withTrashed);
        $permissionRole = $this->getPermissionRole($withTrashed);
        $result = [];
        if ($permissionTeam) {
            $result['team'] = $permissionTeam;
        }
        if ($permissionRole) {
            $result['role'] = $permissionRole;
        }
        return $result;
    }

    /**
     * get permission team of employee
     *
     * @return array
     */
    public function getPermissionTeam($withTrashed = false)
    {
        $teams = $this->getTeamPermissFollow();
        if (!$teams || !count($teams)) {
            return [];
        }
        $routesAllow = [];
        $actionIdsAllow = [];
        $actionTable = Action::getTableName();
        $permissionTable = Permission::getTableName();
        foreach ($teams as $teamMember) {
            if (!$teamMember->is_function) {
                continue;
            }
            $teamIdOrgin = $teamMember->team_id;
            //get permission of team member
            $teamPermission = Permission::select('action_id',  'route', 'scope')
                    ->join($actionTable, $actionTable . '.id', '=', $permissionTable . '.action_id')
                    ->where('team_id', $teamMember->team_permiss_id)
                    ->where('role_id', $teamMember->role_id)
                    ->get();

            $teamChildIds = Team::teamChildIds($teamIdOrgin, null, $withTrashed);
            if (count($teamPermission)) {
                if (!isset($actionIdsAllow[$teamIdOrgin]['permissScopes'])) {
                    $actionIdsAllow[$teamIdOrgin]['permissScopes'] = [];
                }
                foreach ($teamPermission as $item) {
                    if (!$item->scope) {
                        continue;
                    }
                    if ($item->action_id) {
                        $actionIdsAllow[$teamIdOrgin]['permissScopes'][$item->action_id] = $item->scope;
                    }
                }
                $actionIdsAllow[$teamIdOrgin]['childs'] = $teamChildIds;
            }
            //get scope of route name from action id
            if (!isset($actionIdsAllow[$teamIdOrgin]['permissScopes']) || !count($actionIdsAllow[$teamIdOrgin]['permissScopes'])) {
                continue;
            }
            $actionIds = $actionIdsAllow[$teamIdOrgin]['permissScopes'];
            $actionIds = array_keys($actionIds);
            $routes = Action::getRouteChildren($actionIds);
            if (count($routes)) {
                if (!isset($routesAllow[$teamIdOrgin]['permissScopes'])) {
                    $routesAllow[$teamIdOrgin]['permissScopes'] = [];
                }
                foreach ($routes as $route => $valueIds) {
                    if ($valueIds['id'] && isset($actionIdsAllow[$teamIdOrgin]['permissScopes'][$valueIds['id']])) {
                        $routesAllow[$teamIdOrgin]['permissScopes'][$route] = $actionIdsAllow[$teamIdOrgin]['permissScopes'][$valueIds['id']];
                    } else if ($valueIds['parent_id'] && isset($actionIdsAllow[$teamIdOrgin]['permissScopes'][$valueIds['parent_id']])) {
                        $routesAllow[$teamIdOrgin]['permissScopes'][$route] = $actionIdsAllow[$teamIdOrgin]['permissScopes'][$valueIds['parent_id']];
                    }
                }
                $routesAllow[$teamIdOrgin]['childs'] = $teamChildIds;
            }
        }

        if (!$routesAllow && !$actionIdsAllow) {
            return [];
        }
        return [
            'route' => $routesAllow,
            'action' => $actionIdsAllow,
        ];
    }

    /**
     * get acl role of rule
     *
     * @return array
     */
    protected function getPermissionRole($withTrashed = false)
    {
        $roles = $this->getRoleIds();
        if (!$roles || !count($roles)) {
            return [];
        }
        $routesAllow = [];
        $actionIdsAllow = [];
        $routesAllowOfRole = [];
        $actionIdsAllowOfRole = [];
        $actionsTeamIds = [];
        $routesTeamIds = [];
        $actionTable = Action::getTableName();
        $permissionTable = Permission::getTableName();

        $roleIds = $roles->pluck('role_id')->toArray();
        $rolePermission = Permission::select('action_id',  'route', 'scope', 'scope_team_ids')
            ->join($actionTable, $actionTable . '.id', '=', $permissionTable . '.action_id')
            ->where('team_id', null)
            ->whereIn('role_id', $roleIds)
            ->get();

        $teamMemberIds = null;
        foreach ($rolePermission as $item) {
            if (!$item->scope) {
                continue;
            }
            if ($item->action_id) {
                //old scope greater than current item scope --> not update
                if (!isset($actionIdsAllowOfRole[$item->action_id]) ||
                        $actionIdsAllowOfRole[$item->action_id] < $item->scope) {
                    $actionIdsAllowOfRole[$item->action_id] = (int) $item->scope;
                }
                if ($item->scope == Permission::SCOPE_TEAM) {
                    if (!isset($actionsTeamIds[$item->action_id])) {
                        $actionsTeamIds[$item->action_id] = [];
                    }
                    $hasScopeTeamIds = false;
                    if ($item->scope_team_ids) {
                        $scopeTeamIds = json_decode($item->scope_team_ids, true);
                        if (is_array($scopeTeamIds) && count($scopeTeamIds) > 0) {
                            $actionsTeamIds[$item->action_id] = array_merge($actionsTeamIds[$item->action_id], $scopeTeamIds);
                            $hasScopeTeamIds = true;
                        }
                    }
                    //if not check team in special role then get team member ids
                    if (!$hasScopeTeamIds) {
                        if ($teamMemberIds === null) {
                            $teamMemberIds = TeamMember::where('employee_id', $this->id)
                                ->pluck('team_id')
                                ->toArray();
                        }
                        if (count($teamMemberIds) > 0) {
                            $actionsTeamIds[$item->action_id] = array_merge(
                                $actionsTeamIds[$item->action_id],
                                array_diff($teamMemberIds, $actionsTeamIds[$item->action_id])
                            );
                        }
                    }
                }
            }
        }

        //get scope of route name from action id
        if ($actionIdsAllowOfRole) {
            $actionIds = array_keys($actionIdsAllowOfRole);
            $routes = Action::getRouteChildren($actionIds);
            foreach ($routes as $route => $valueIds) {
                if ($valueIds['id'] && isset($actionIdsAllowOfRole[$valueIds['id']])) {
                    $routesAllowOfRole[$route] = $actionIdsAllowOfRole[$valueIds['id']];
                } else if ($valueIds['parent_id'] && isset($actionIdsAllowOfRole[$valueIds['parent_id']])) {
                    $routesAllowOfRole[$route] = $actionIdsAllowOfRole[$valueIds['parent_id']];
                }
                //list route team ids
                if ($valueIds['id'] && isset($actionsTeamIds[$valueIds['id']])) {
                    $routesTeamIds[$route] = $actionsTeamIds[$valueIds['id']];
                } elseif ($valueIds['parent_id'] && isset($actionsTeamIds[$valueIds['parent_id']])) {
                    $routesTeamIds[$route] = $actionsTeamIds[$valueIds['parent_id']];
                } else {
                    //
                }
            }
        }

        //get scope greater of role for user
        foreach ($actionIdsAllowOfRole as $actionId => $scope) {
            if (isset($actionIdsAllow[$actionId]) && $actionIdsAllow[$actionId] > $scope) {
                continue;
            }
            $actionIdsAllow[$actionId] = $scope;
        }

        foreach ($routesAllowOfRole as $route => $scope) {
            if (isset($routesAllow[$route]) && $routesAllow[$route] > $scope) {
                continue;
            }
            $routesAllow[$route] = $scope;
        }

        return [
            'route' => $routesAllow,
            'action' => $actionIdsAllow,
            'route_team' => $routesTeamIds,
            'action_team' => $actionsTeamIds,
        ];
    }

    /**
     * gender to option
     *
     * @return array
     */
    public static function toOptionGender()
    {
        return [
            [
                'value' => self::GENDER_MALE,
                'label' => Lang::get('team::view.Male')
            ],
            [
                'value' => self::GENDER_FEMALE,
                'label' => Lang::get('team::view.Female')
            ]
        ];
    }

    public static function labelGender()
    {
        return [
            self::GENDER_FEMALE => Lang::get('resource::view.Female'),
            self::GENDER_MALE => Lang::get('resource::view.Male'),
        ];
    }

    /**
     * get list Marital
     * @return array (label => '' , value => '')
     */
    public static function toOptionMarital()
    {
        return [
            [
                'value' => self::MARITAL_SINGLE,
                'label' => Lang::get('team::view.Single'),
            ],
            [
                'value' => self::MARITAL_MARRIED,
                'label' => Lang::get('team::view.Married'),
            ],
            [
                'value' => self::MARITAL_WIDOWED,
                'label' => Lang::get('team::view.Widowed'),
            ],
            [
                'value' => self::MARITAL_SEPARATED,
                'label' => Lang::get('team::view.Separated'),
            ],
        ];
    }

    /**
     * array marital (value => label)
     * @return array
     */
    public static function labelMarital()
    {
        return [
            self::MARITAL_SINGLE => Lang::get('team::view.Single'),
            self::MARITAL_MARRIED => Lang::get('team::view.Married'),
            self::MARITAL_WIDOWED => Lang::get('team::view.Widowed'),
            self::MARITAL_SEPARATED => Lang::get('team::view.Separated'),
        ];
    }

    /**
     * check employee allow login
     *
     * @return boolean
     */
    public function isAllowLogin()
    {
        $joinDate = strtotime($this->join_date);
        $leaveDate = strtotime($this->leave_date);
        $nowDate = date('Y-m-d');
        if (date('Y-m-d', $joinDate) > $nowDate || ($leaveDate && date('Y-m-d', $leaveDate) < $nowDate)) {
            return false;
        }
        return true;
    }

    /**
     * check is leader of a team
     *
     * @return boolean
     */
    public function isLeader()
    {
        if ($employeeLeader = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return self::flagToBoolean($employeeLeader);
        }
        $positions = $this->getTeamPositons();
        foreach ($positions as $position) {
            $employeeLeader = Role::isPositionLeader($position->role_id);
            if ($employeeLeader) {
                break;
            }
        }
        CacheHelper::put(
            self::KEY_CACHE,
            self::booleanToFlag($employeeLeader),
            $this->id
        );
        return $employeeLeader;
    }

    /**
     * get ids of team that employee is leader
     *
     * @return array
     */
    public function getTeamIdIsLeader()
    {
        if ($teamIds = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $teamIds;
        }
        $teamIds = [];
        $positions = $this->getTeamPositons();
        foreach ($positions as $position) {
            $employeeLeader = Role::isPositionLeader($position->role_id);
            if ($employeeLeader) {
                $teamIds[] = $position->team_id;
            }
        }
        CacheHelper::put(self::KEY_CACHE, $teamIds, $this->id);
        return $teamIds;
    }

    /**
     * check permission greater with another employee
     *
     * @param model $employee
     * @param boolean $checkIsLeader
     * @return boolean
     */
    public function isGreater($employee, $checkIsLeader = false)
    {
        if ($employeeGreater = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return self::flagToBoolean($employeeGreater);
        }
        if (is_numeric($employee)) {
            $employee = Employee::find($employee);
        }
        $employeeGreater = null;
        if (!$employee) {
            $employeeGreater = false;
        } elseif ($this->id == $employee->id) {
            if ($checkIsLeader) {
                if ($this->isLeader()) {
                    $employeeGreater = true;
                } else {
                    $employeeGreater = false;
                }
            } else {
                $employeeGreater = false;
            }
        } else {
            $thisTeam = $this->getTeamPositons();
            $anotherTeam = $employee->getTeamPositons();
            $teamPaths = Team::getTeamPath();
            if (!count($thisTeam) || !count($anotherTeam) || !count($teamPaths)) {
                $employeeGreater = false;
            }
        }
        if ($employeeGreater === null) {
            $employeeGreater = $this->isTeamPositionGreater(
                    $thisTeam, $anotherTeam, $teamPaths, $checkIsLeader
            );
        }
        CacheHelper::put(self::KEY_CACHE, self::booleanToFlag($employeeGreater), $this->id);
        return $employeeGreater;
    }

    /**
     * check team greater, position greater of 2 employee
     *
     * @param model $thisTeam
     * @param model $anotherTeam
     * @param array $teamPaths
     * @return boolean
     */
    protected function isTeamPositionGreater(
        $thisTeam,
        $anotherTeam,
        $teamPaths,
        $checkIsLeader = false
    )
    {
        foreach ($anotherTeam as $anotherTeamItem) {
            foreach ($thisTeam as $thisTeamItem) {
                // this team is team root
                if (!isset($teamPaths[$anotherTeamItem->team_id]) ||
                        !$teamPaths[$anotherTeamItem->team_id]) {
                    continue;
                }
                // team greater
                if (in_array($thisTeamItem->team_id, $teamPaths[$anotherTeamItem->team_id])) {
                    return true;
                }
                // 2 team diffirent branch
                if ($thisTeamItem->team_id != $anotherTeamItem->team_id) {
                    continue;
                }
                // same team, compare position
                $thisPosition = Role::find($thisTeamItem->role_id);
                $anotherPosition = Role::find($anotherTeamItem->role_id);
                if (!$thisPosition ||
                        !$anotherPosition ||
                        !$thisPosition->isPosition() ||
                        !$anotherPosition->isPosition()) {
                    continue;
                }
                if ($checkIsLeader) {
                    if ($thisPosition->isLeader()) {
                        return true;
                    }
                    return false;
                }
                if ($thisPosition->sort_order < $anotherPosition->sort_order) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check employee_code exist
     *
     * @param string $code
     * @return boolean
     */
    public static function checkEmployeeCodeExist($code, $employeeId)
    {
        $count = self::where('employee_code', $code);
        if ($employeeId) {
            $count->where('id', '<>', $employeeId);
        }
        return $count->count() > 0;
    }

    public static function checkEmailExist($email)
    {
        $count = self::where('email', $email)->withTrashed()->count();
        return $count > 0;
    }

    public static function checkNicknameExist($nickname)
    {
        $count = self::where('nickname', $nickname)->withTrashed()->count();
        return $count > 0;
    }

    /**
     * get employee follow email
     *
     * @param string $chart
     * @return colleciton
     */
    public static function getEmpLikeEmail($chart)
    {
        return self::select('employees.name', 'employees.email', 'japanese_name', 'users.avatar_url')
                        ->join('users', 'users.employee_id', '=', 'employees.id')
                        ->where('employees.email', 'like', "$chart%")
                        ->orderBy('nickname')
                        ->take(5)
                        ->get();
    }

    /**
     * Get employee = email
     *
     * @param $email
     * @param array $column
     * @return mixed
     */
    public static function getEmpByEmail($email, $column = ['*'])
    {
        return self::where('email', '=', $email)
            ->select($column)
            ->first();
    }

    public static function getEmployByEmail($email)
    {
        return self::where('employees.email', '=', $email)
            ->join('users', 'users.email', '=', 'employees.email')
            ->select('employees.id', 'employees.employee_code', 'employees.email', 'employees.name',
                'employees.japanese_name', 'employees.birthday', 'employees.address',
                'employees.gender', 'users.avatar_url')
            ->first();
    }

    /**
     * Get employees from email list
     *
     * @param array $emails
     * @param array $colsSelected
     * @return Employee collection
     */
    public static function getEmpByEmails($emails, $colsSelected = ['*'])
    {
        return self::whereIn('email', $emails)
            ->select($colsSelected)
            ->get();
    }

    /**
     * getEmpByEmailsWithContracts
     *
     * @param  array $emails
     * @param  date $dateStart Y-m-d
     * @param  date $dateEnd Y-m-d
     * @param  array $colsSelected
     * @return array
     */
    public static function getEmpByEmailsWithContracts($emails, $dateStart, $dateEnd, $colsSelected = ['*'])
    {
        $statusApprove = WorkingTimeRegister::STATUS_APPROVE;
        return self::join('employee_works', 'employee_works.employee_id', '=', 'employees.id')
            ->leftJoin(DB::raw("(SELECT
                    wktd.working_time_id,
                    wktd.employee_id, 
                    wktd.from_date,
                    wktd.to_date,
                    wktd.half_morning,
                    wktd.half_afternoon,
                    wktd.start_time1,
                    wktd.end_time1, 
                    wktd.start_time2,
                    wktd.end_time2
                FROM working_time_details as wktd
                INNER JOIN working_time_registers AS wkt ON wkt.id = wktd.working_time_id
                WHERE wkt.status = {$statusApprove} 
                    AND wkt.deleted_at IS NULL
                    AND date(wktd.from_date) <= '{$dateEnd}'
                    AND date(wktd.to_date) >= '{$dateStart}') AS working_time
                "), 'working_time.employee_id', '=', 'employees.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'employees.id')
            ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id')
            ->whereIn('employees.email', $emails)
            ->select($colsSelected)
            ->addSelect(
                'working_time.working_time_id as wtk_id',
                'working_time.from_date as wtk_from_date',
                'working_time.to_date as wtk_to_date',
                'working_time.half_morning as half_morning',
                'working_time.half_afternoon as half_afternoon'
            )
            ->groupBy('working_time.working_time_id', 'employees.id')
            ->get()->groupBy('id');
    }

    /**
     * getEmpWithContracts
     *
     * @param  array $EmpIds
     * @param  date $dateStart Y-m-d
     * @param  date $dateEnd Y-m-d
     * @param  array $colsSelected
     * @return array
     */
    public static function getEmpWithContracts($EmpIds, $dateStart, $dateEnd, $colsSelected = ['*'])
    {
        $statusApprove = WorkingTimeRegister::STATUS_APPROVE;
        return self::join('employee_works', 'employee_works.employee_id', '=', 'employees.id')
            ->leftJoin(DB::raw("(SELECT
                    wktd.working_time_id,
                    wktd.employee_id, 
                    wktd.from_date,
                    wktd.to_date,
                    wktd.half_morning,
                    wktd.half_afternoon,
                    wktd.start_time1,
                    wktd.end_time1, 
                    wktd.start_time2,
                    wktd.end_time2
                FROM working_time_details as wktd
                INNER JOIN working_time_registers AS wkt ON wkt.id = wktd.working_time_id
                WHERE wkt.status = {$statusApprove} 
                    AND wkt.deleted_at IS NULL
                    AND date(wktd.from_date) <= '{$dateEnd}'
                    AND date(wktd.to_date) >= '{$dateStart}') AS working_time
                "), 'working_time.employee_id', '=', 'employees.id')
            ->leftJoin('team_members', 'team_members.employee_id', '=', 'employees.id')
            ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id')
            ->whereIn('employees.id', $EmpIds)
            ->select($colsSelected)
            ->addSelect(
                'working_time.working_time_id as wtk_id',
                'working_time.from_date as wtk_from_date',
                'working_time.to_date as wtk_to_date',
                'working_time.half_morning as half_morning',
                'working_time.half_afternoon as half_afternoon'
            )
            ->groupBy('working_time.working_time_id', 'employees.id')
            ->get()->groupBy('id');
    }

    /**
     * get all employee
     */
    public static function getAllEmployee($order = null, $dir = null, $leave = false)
    {
        if ($employee = CacheHelper::get(self::KEY_CACHE)) {
            return $employee;
        }
        if (!$order) {
            $order = 'name';
        }
        if (!$dir) {
            $dir = 'asc';
        }
        $employee = self::select(['id', 'name', 'email'])
                ->orderby($order, $dir);
        if (!$leave) {
            $employee->whereNull('leave_date');
        }
        CacheHelper::put(self::KEY_CACHE, $employee->get());
        return $employee->get();
    }

    /**
     * get employee follow id
     *
     * @param $id
     * @return \Rikkei\Core\View\type|null
     */
    public static function getEmpById($id)
    {
        if ($emp = CacheHelper::get(self::KEY_CACHE, $id)) {
            return $emp;
        }
        $emp = self::find($id);
        CacheHelper::put(self::KEY_CACHE, $emp, $id);
        return $emp;
    }

    /**
     * get id employee by email
     *
     * @param string $email
     * @param int $type
     * @return Employee|null
     */
    public static function getIdEmpByEmail($email, $type = 1)
    {
        if (is_array($email)) {
            $employee = self::whereIn('email', $email);
        } else {
            $employee = self::where('email', $email);
        }
        $employee = $employee->select('id')
            ->first();
        switch ($type) {
            case 2: // return object
                return $employee;
            default: // return id
                if ($employee) {
                    return $employee->id;
                }
                return null;
        }
    }

    /**
     * get employee by emails
     *
     * @param type $email
     * @return type
     */
    public static function getEmpItemByEmail($email)
    {
        $collection = self::select(
            '*',
            'employees.id as employee_id'
        );
        if (is_array($email)) {
            $collection->whereIn('email', $email);
        } else {
            $collection->where('email', $email);
        }
        return $collection->first();
    }

    /**
     * get name employee by id
     *
     * @param int $id
     * @return string|null
     */
    public static function getNameEmpById($id)
    {
        if (!$id) {
            return;
        }
        $employee = self::find($id);
        if (!$employee) {
            return null;
        }
        return $employee->name;
    }

    /**
     * get email employee by id
     *
     * @param int $id
     * @return string|null
     */
    public static function getEmailEmpById($id)
    {
        if (!$id) {
            return;
        }
        $employee = self::find($id);
        if ($employee) {
            return $employee->email;
        }
        return null;
    }

    public function teams()
    {
        return $this->belongsToMany('\Rikkei\Team\Model\Team', 'team_members', 'employee_id', 'team_id');
    }

    /**
     * check current employee is COO
     * @return boolean
     */
    public function isCOO()
    {
        $coo_accs = CoreConfigData::getCOOAccount();
        return in_array($this->email, $coo_accs);
    }

    /**
     * check current employee is Leader PQA
     * @return type
     */
    public function isLeaderPQA()
    {
        $pqaAccount = CoreConfigData::getQAAccount();
        return in_array($this->email, $pqaAccount);
    }

    /**
     * get employee by email
     * @param string
     * @return array
     */
    public static function getEmployeeByEmail($email, $select = ['id', 'email'])
    {
        return self::select($select)
            ->withTrashed()
            ->where('email', $email)
            ->first();
    }

    /**
     * check nick name exists
     * @param string
     * @return boolean
     */
    public static function checkNickNameExists($nickname)
    {
        return self::where('nickname', $nickname)->first() ? true : false;
    }

    /**
     * seach employee by email
     *
     * @param $email
     * @param array $config
     * @param array $ignorAccountStatus
     * @return array
     */
    public static function searchAjax($email, array $config = [], array $ignorAccountStatus = [])
    {
        if (empty($ignorAccountStatus)) {
            $ignorAccountStatus = [getOptions::PREPARING, getOptions::FAIL_CDD];
        }
        $getLeaver = isset($config['get-leaver']) ? $config['get-leaver'] : false;
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'typeExclude' => null
        ];
        $email = trim($email);
        $config = array_merge($arrayDefault, $config);
        $includeLeave = isset($config['has_leave']) ? $config['has_leave'] : false;
        $collection = self::select('employees.id', 'employees.email', 'employees.email as text', 'employees.name', 'users.avatar_url')
            ->where(function($query) use ($email) {
                $query->orWhere('employees.email', 'LIKE', '%' . $email . '%')
                    ->orWhere('employees.name', 'LIKE', '%' . $email . '%');
            })
            ->whereNotIn("employees.account_status", $ignorAccountStatus)
            ->orderBy('email');
        switch ($config['typeExclude']) {
            case self::EXCLUDE_REVIEWER: // not show reviewer
                if (!Input::get('task')) {
                    break;
                }
                $collection->whereNotIn('id', function($query) {
                    $query->select('employee_id')
                        ->from(TaskAssign::getTableName())
                        ->where('task_id', Input::get('task'))
                        ->where('role', TaskAssign::ROLE_REVIEWER);
                });
                if (!$getLeaver) {
                    if (!$includeLeave) {
                        $now = Carbon::now();
                        $collection->where(function ($query) use ($now) {
                            $query->orWhereNull('leave_date')
                                ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
                        });
                    } else {
                        $collection->whereNotNull('leave_date');
                    }
                }
                break;

            case self::EXCLUDE_UTILIZATION:
                if (PermissionView::getInstance()->isScopeTeam(null, 'resource::dashboard.utilization')) {
                    $curEmp = PermissionView::getInstance()->getEmployee();
                    $teamsOfEmp = CheckpointPermission::getArrTeamIdByEmployee($curEmp->id);
                    $teamMemberTable = TeamMember::getTableName();
                    $empTable = self::getTableName();
                    $collection->join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$empTable}.id")
                               ->whereIn("{$teamMemberTable}.team_id", $teamsOfEmp);
                }
                break;
            case 'not': // show full all employee of company
                break;
            default: // show employee not leave
                if (!$getLeaver) {
                    if (!$includeLeave) {
                        $now = Carbon::now();
                        $collection->where(function ($query) use ($now) {
                            $query->orWhereNull('leave_date')
                                ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
                        });
                    } else {
                        $collection->whereNotNull('leave_date');
                    }
                }
                break;
        }
        $isJoinTeam = false;
        if (isset($config['projectId']) && $config['projectId']) {
            $isJoinTeam = true;
            $projectId = $config['projectId'];
            $projectTeamIds = TeamProject::getTeamIds($projectId);
            $collection->leftJoin('team_members', 'employees.id', '=', 'team_members.employee_id')
                        ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id')
                        ->leftJoin('team_projs', 'teams.id', '=', 'team_projs.team_id')
                        ->leftJoin('projs', 'team_projs.project_id', '=', 'projs.id')
                        ->where(function ($query) use ($projectId, $projectTeamIds) {
                            $query->where('projs.id', '=', $projectId)
                                    ->whereIn('teams.id', $projectTeamIds)
                                    ->orWhere('teams.type', '=', Team::TEAM_TYPE_PQA)
                                    ->orWhere('projs.parent_id', '=', $projectId);
                        });
        }
        //team type
        if (isset($config['team_type']) || isset($config['team_id'])) {
            if (!$isJoinTeam) {
                $isJoinTeam = true;
                $collection->leftJoin('team_members', 'employees.id', '=', 'team_members.employee_id')
                        ->leftJoin('teams', 'teams.id', '=', 'team_members.team_id');
            }
            if (isset($config['team_type'])) {
                $collection->where('teams.type', $config['team_type']);
            }
            if (isset($config['team_id'])) {
                $teamIds = Team::teamChildIds($config['team_id']);
                $collection->whereIn('team_members.team_id', $teamIds);
            }
        }
        $collection->leftJoin('users', 'users.employee_id', '=', 'employees.id')
                    ->groupBy('employees.id');
        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        $result['items'] = [];

        if(isset($config['fullName']) && $config['fullName']) {
            foreach ($collection as $item) {
                $result['items'][] = [
                    'id' => $item->id,
                    'text' => strip_tags($item->name) . ' (' . View::getNickName($item->email) . ')',
                    'avatar' => $item->avatar_url,
                    'dataMore' => [
                        'email' => $item->email,
                        'name' => $item->name
                    ]
                ];
            }
            return $result;
        }
        foreach ($collection as $item) {
            $result['items'][] = [
                'id' => $item->id,
                'text' => View::getNickName($item->email),
                'avatar' => $item->avatar_url,
            ];
        }
        return $result;
    }

    /**
     * seach employee by email
     *
     * @param string $email
     * @param int $page
     * @param ing $limit
     * @return array
     */
    public static function searchAjaxExternal($email, array $config = [])
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'type' => null,
            'fullName' => true
        ];
        $config = array_merge($arrayDefault, $config);
        $collection = self::select('employees.id', 'employees.email', 'employees.name')
                ->where(function($query) use ($email) {
                    $query->orWhere('employees.email', 'LIKE', '%' . $email . '%')
                    ->orWhere('employees.name', 'LIKE', '%' . $email . '%');
                })
                ->orderBy('email');
        switch ($config['type']) {
            case 1: // show not me
                $collection->where('id', '!=', Auth::id());
                break;
            case 'all': // show full all employee of company
                break;
            default:
                break;
        }
        if ($config['type'] !== 'all') { // show employee not leave
            $now = Carbon::now();
            $collection->where(function($query) use ($now) {
                $query->orWhereNull('leave_date')
                        ->orWhereDate('leave_date', '>=', $now->format('Y-m-d'));
            });
        }
        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result['total'] = $collection->total();
        $result['data'] = [];
        if (isset($config['fullName']) && $config['fullName']) {
            foreach ($collection as $item) {
                $result['data'][] = [
                    'id' => $item->id,
                    'text' => ($item->name) . ' ('
                    . View::getNickName($item->email) . ')',
                    'dataMore' => [
                        'email' => $item->email,
                        'name' => $item->name
                    ]
                ];
            }
            return $result;
        }
        foreach ($collection as $item) {
            $result['data'][] = [
                'id' => $item->id,
                'text' => View::getNickName($item->email),
            ];
        }
        return $result;
    }

    /**
     * get employee by team id
     *
     * @param int $teamId
     * @return object
     */
    public static function getEmpByTeam($teamId)
    {
        $empTable = self::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$empTable}.id")
                        ->where("{$teamMemberTable}.team_id", $teamId)
                        ->whereNull("leave_date")
                        ->select("{$empTable}.id", "{$empTable}.email", "{$empTable}.name")
                        ->get();
    }

    /**
     * get list employee for checkpoint by team id, start date, end date of checkpoint
     *
     * @param int $teamId team of checkpoint
     * @param string|date $startDate start date of checkpoint
     * @param string|date $endDate end date of checkpoint
     * @return Employee collection
     */
    public static function getEmpForCheckpoint($teamId, $startDate, $endDate)
    {
        $empTable = self::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        $empTeamHistory = EmployeeTeamHistory::getTableName();
        return self::join("{$empTeamHistory}", "{$empTeamHistory}.employee_id", "=", "{$empTable}.id")
                        ->whereRaw('employees.id in ('
                                . 'select employee_team_history.employee_id '
                                . 'from employee_team_history join employees on employee_team_history.employee_id = employees.id '
                                . 'WHERE (employees.leave_date is null or employees.leave_date > ?) '
                                . 'AND (employee_team_history.team_id = ? AND (DATE(employee_team_history.start_at) <= DATE(?) or employee_team_history.start_at is null) AND (DATE(employee_team_history.end_at) >= DATE(?) or employee_team_history.end_at is null)))', [$startDate, $teamId, $endDate, $startDate])
                        ->where("{$empTable}.working_type", "!=", getOptions::WORKING_INTERNSHIP)
                        ->select("{$empTable}.id", "{$empTable}.email", "{$empTable}.name")
                        ->groupBy("{$empTable}.id")
                        ->get();
    }

    /**
     * get all employee for checkpoint follow start date checkpoint
     */
    public static function getAllEmpForCheckpoint($startDate, $order = null, $dir = null)
    {
        if ($employee = CacheHelper::get(self::KEY_CACHE)) {
            return $employee;
        }
        if (!$order) {
            $order = 'name';
        }
        if (!$dir) {
            $dir = 'asc';
        }
        $employee = self::select(['id', 'name', 'email'])
                ->whereRaw('leave_date is null or leave_date > ?', [$startDate])
                ->orderby($order, $dir);
        CacheHelper::put(self::KEY_CACHE, $employee->get());
        return $employee->get();
    }

    /**
     * get employee by team ids
     *
     * @param int $teamIds
     * @return object
     */
    public static function getEmpByTeams($teamIds, $order = 'email', $dir = 'asc')
    {
        $empTable = self::getTableName();
        $teamMemberTable = TeamMember::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$empTable}.id")
            ->whereIn("{$teamMemberTable}.team_id", $teamIds)
            ->where(function ($query) use ($empTable) {
                $query->whereNull("{$empTable}.leave_date")
                ->orWhereRaw("DATE({$empTable}.leave_date) > CURDATE()");
            })
            ->groupBy("{$empTable}.id")
            ->orderBy($order, $dir)
            ->select("{$empTable}.id", "{$empTable}.email", "{$empTable}.name")
            ->get();
    }

    /*
     * get employee by team type
     */

    public static function getByTeamTypes($types, $config = [], $whereField = 'type')
    {
        $types = is_array($types) ? $types : [$types];
        $whereField = ($whereField == 'type') ? 'team.type' : 'team.code';
        $empTbl = self::getTableName();
        $configDefault = [
            'select' => [$empTbl . '.id', $empTbl . '.name', $empTbl . '.email'],
            'orderby' => 'email',
            'order' => 'asc'
        ];
        $config = array_merge($configDefault, $config);
        return self::join(TeamMember::getTableName() . ' as tmb', $empTbl . '.id', '=', 'tmb.employee_id')
                        ->join(Team::getTableName() . ' as team', 'tmb.team_id', '=', 'team.id')
                        ->whereIn($whereField, $types)
                        ->where(function ($query) use ($empTbl) {
                            $query->whereNull("{$empTbl}.leave_date")
                            ->orWhereRaw("DATE({$empTbl}.leave_date) > CURDATE()");
                        })
                        ->groupBy("{$empTbl}.id")
                        ->orderBy($config['orderby'], $config['order'])
                        ->select($config['select'])
                        ->get();
    }

    /*
     * get member of team hr
     */

    public static function getMembersOfHr($returnTupe = 'email')
    {
        $members = self::getByTeamTypes(Team::TEAM_TYPE_HR);
        if ($returnTupe == 'email') {
            return $members->lists('email')->toArray();
        }
        return $members;
    }

    /*
     * get all emails of team hr (các nhân viên đang và đã từng ở team HR)
     */
    public static function getAllEmailsOfHr()
    {
        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();
        $tblTeam = Team::getTableName();
        $tblEmp = self::getTableName();
        return self::join($tblEmpTeamHistory, "{$tblEmpTeamHistory}.employee_id", '=', "{$tblEmp}.id")
            ->join($tblTeam, "{$tblTeam}.id", '=', "{$tblEmpTeamHistory}.team_id")
            ->where("{$tblTeam}.type", Team::TEAM_TYPE_HR)
            ->groupBy("{$tblEmp}.id")
            ->orderBy("{$tblEmp}.email", 'DESC')
            ->pluck("{$tblEmp}.email")->toArray();
    }

    /**
     * get employee by ids
     *
     * @param type $ids
     * @return type
     */
    public static function getEmpByIds(array $ids, $col = ['*'])
    {
        return self::whereIn('id', $ids)->select($col)->get();
    }

    /**
     * get employee by ids
     *
     * @param type $ids
     * @return type
     */
    public static function getEmpEmailById($id)
    {
        return self::where('id', $id)->select('email')->first();
    }

    /**
     * get employee by ids
     *
     * @param type $ids
     * @return type
     */
    public static function getEmpNameById($id)
    {
        return self::where('id', $id)->select('name')->first();
    }

    public static function getEmpByIdsNotLeave(array $ids, $col = ['*'])
    {
        return self::whereIn('id', $ids)->where(function ($q) {
            $q->where('leave_date', null)
            ->orWhere('leave_date', '>=', Carbon::now()->format('Y-m-d H:i:s'));
        })
            ->select($col)
            ->get();
    }

    /*
     * get name employee by id
     *
     * @param int
     * return string
     */

    public static function getNameEmailById($id)
    {
        $item = self::select('name', 'email')
                ->where('id', $id)
                ->first();
        return $item;
    }

    /**
     * get all employee of team (and self child)
     *
     * @param int $teamId
     * @param array $where
     * @return object
     */
    public static function getAllEmployeesOfTeam($teamId, array $where = [])
    {
        $teamPath = Team::getTeamPath();
        if (isset($teamPath[$teamId]['child'])) {
            $teams = $teamPath[$teamId]['child'];
            $teams[] = $teamId;
        } else {
            $teams[] = $teamId;
        }
        $tableEmployee = self::getTableName();
        $tableTeamEmployee = TeamMember::getTableName();

        $collection = self::select($tableEmployee . '.id', $tableEmployee . '.email', $tableEmployee . '.name')
                ->whereNull($tableEmployee . '.leave_date')
                ->join($tableTeamEmployee, $tableTeamEmployee . '.employee_id', '=', $tableEmployee . '.id')
                ->whereIn($tableTeamEmployee . '.team_id', $teams)
                ->groupBy($tableEmployee . '.id');
        if (isset($where['gender'])) {
            $collection->where('gender', $where['gender']);
        }
        return $collection->get();
    }

    /**
     * get all employee active
     *
     * @return collection
     */
    public static function getEmailNameEmployeeJoin()
    {
        return self::select('id', 'email', 'name')
                        ->whereNull('leave_date')
                        ->get();
    }

    /**
     * get all employee is pm or leader
     * @return array
     */
    public static function getAllEmployeeIsPmOrLeader()
    {
        $tableEmployeeRole = EmployeeRole::getTableName();
        $tableRole = Role::getTableName();
        $tableEmployee = self::getTableName();
        $tableTeam = Team::getTableName();
        $listPm = self::join($tableEmployeeRole, $tableEmployee . '.id', '=', $tableEmployeeRole . '.employee_id')
                ->join($tableRole, $tableEmployeeRole . '.role_id', '=', $tableRole . '.id')
                ->where($tableRole . '.role', '=', 'pm')
                ->select([$tableEmployee . '.id', $tableEmployee . '.name', $tableEmployee . '.email'])
                ->orderBy($tableEmployee . '.name');
        return self::join($tableTeam, $tableEmployee . '.id', '=', $tableTeam . '.leader_id')
                        ->select([$tableEmployee . '.id', $tableEmployee . '.name', $tableEmployee . '.email'])
                        ->orderBy($tableEmployee . '.name')
                        ->union($listPm)
                        ->groupBy($tableEmployee . '.id')
                        ->get();
    }

    /**
     * Count employee by team
     *
     * @param array $teamIds
     * @return int
     */
    public static function countEmployeeByTeams($teamIds)
    {
        $teamMemberTable = TeamMember::getTableName();
        $empTable = self::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$empTable}.id")
                        ->whereIn('team_id', $teamIds)
                        ->whereNull("{$empTable}.leave_date")
                        ->select(DB::raw("distinct {$empTable}.id"))
                        ->count();
    }

    /**
     * Get $id employees by nickname
     * @param string $nickname
     * @return array id employees
     */
    public static function getIdByNickName($nickName)
    {
        $arrayNickName = array_map('trim', explode(",", $nickName));
        $arrayIdEmploy = self::select('email', 'id')->get()->toArray();
        $arrayId = array();
        foreach ($arrayIdEmploy as $valueIdEmploy) {
            foreach ($arrayNickName as $valueNickName) {
                if (strtolower(View::getNickName($valueIdEmploy['email'])) == strtolower($valueNickName)) {
                    array_push($arrayId, $valueIdEmploy['id']);
                }
            }
        }
        $stringId = "";
        foreach ($arrayId as $keyId => $valueId) {
            if ($keyId == 0)
                $stringId = $valueId;
            else
                $stringId = $stringId . "," . $valueId;
        }
        if (!empty($stringId)) {
            return $stringId;
        } else {
            return null;
        }
    }

    /**
     * Get count new employee in month
     *
     * @param int $year
     * @param int $month
     * @return int
     */
    public static function getNewEmpInMonth($year, $month, $teamIds)
    {
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $teamMemberTable = TeamMember::getTableName();
        $EmployeeTable = self::getTableName();
        $teamTable = Team::getTableName();
        $result = self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$EmployeeTable}.id")
                ->join("{$teamTable}", "{$teamTable}.id", "=", "{$teamMemberTable}.team_id")
                ->whereBetween('join_date', [$start, $end])
                ->where("is_soft_dev", Team::IS_SOFT_DEVELOPMENT);
        if ($teamIds) {
            $result->whereIn("{$teamMemberTable}.team_id", $teamIds);
        }
        return $result->count();
    }

    public static function getEmpOutInMonth($year, $month, $teamId)
    {
        $firstLastMonth = ResourceView::getInstance()->getFirstLastDaysOfMonth($month, $year);
        $start = $firstLastMonth[0];
        $end = $firstLastMonth[1];
        $teamMemberTable = TeamMember::getTableName();
        $EmployeeTable = self::getTableName();
        return self::join("{$teamMemberTable}", "{$teamMemberTable}.employee_id", "=", "{$EmployeeTable}.id")
                        ->whereBetween('leave_date', [$start, $end])
                        ->where("{$teamMemberTable}.team_id", $teamId)
                        ->count();
    }

    /**
     * get name employee by email
     *
     * @param string $email
     * @return Employee
     */
    public static function getNameByEmail($email)
    {
        return self::select('name', 'id')
                        ->where('email', $email)
                        ->first();
    }

    /**
     * get Role and team employee
     */
    public static function getRoleById($id)
    {
        $role = TeamMember::where('employee_id', $id)
                        ->leftJoin('roles', 'team_members.role_id', '=', 'roles.id')
                        ->leftJoin('teams', 'team_members.team_id', '=', 'teams.id')
                        ->select('roles.role', 'teams.name', 'teams.id')->get();
        if ($role) {
            return $role;
        }
        return null;
    }

    /**
     * @param array email employee
     * @return array info emplpyee in company
     * get array email in company
     */
    public static function getArrayEmail($email, $type)
    {
        $arrayEmail = array();
        foreach ($email as $valueIt) {
            array_push($arrayEmail, trim($valueIt));
        }
        $arrayEmailIt = Employee::whereIn('email', $arrayEmail)->select($type)->get()->toArray();
        return $arrayEmailIt;
    }

    /**
     * @param int email's id
     * @return string Name Email
     */
    public static function getNickNameById($id)
    {
        $employee = self::find($id);
        if (!$employee) {
            return null;
        }
        return strstr($employee->email, '@', true);
    }

    public static function searchAjaxWithTeamName($email, $config = [])
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 10,
            'typeExclude' => null
        ];
        $config = array_merge($arrayDefault, $config);
        $empTable = self::getTableName();
        $teamMemTable = TeamMember::getTableName();
        $teamTable = Team::getTableName();
        $collection = self::join("{$teamMemTable}", "{$teamMemTable}.employee_id", "=", "{$empTable}.id")
                ->join("{$teamTable}", "{$teamTable}.id", "=", "{$teamMemTable}.team_id")
                ->where("{$teamTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT)
                ->where(function ($query) use ($email, $empTable) {
                    $query->where("{$empTable}.email", 'LIKE', '%' . $email . '%')
                    ->orWhere("{$empTable}.name", 'LIKE', '%' . $email . '%');
                })
                ->orderBy('email')
                ->groupBy("{$empTable}.id")
                ->select(
                "{$empTable}.id", "{$empTable}.email", "{$empTable}.name", DB::raw("group_concat({$teamTable}.name SEPARATOR ', ') AS team")
        );
        self::pagerCollection($collection, $config['limit'], $config['page']);
        foreach ($collection as $item) {
            $result[] = [
                'data' => $item->id,
                'value' => View::getNickName($item->email) . ' - ' . $item->name . ' (' . $item->team . ')',
            ];
        }
        return $result;
    }

    /**
     * get all pm of system
     *
     * @return array
     */
    public static function getAllPM()
    {
        if ($result = CacheHelper::get(self::KEY_CACHE)) {
            return $result;
        }
        $tableEmployeeRole = EmployeeRole::getTableName();
        $tableRole = Role::getTableName();
        $tableEmployee = self::getTableName();
        $pmRoleName = 'pm';

        $collection = self::select([$tableEmployee . '.id',
                    $tableEmployee . '.name', $tableEmployee . '.email'])
                ->join($tableEmployeeRole, $tableEmployee . '.id', '=', $tableEmployeeRole . '.employee_id')
                ->join($tableRole, $tableEmployeeRole . '.role_id', '=', $tableRole . '.id')
                ->where($tableRole . '.role', '=', $pmRoleName)
                ->orderBy($tableEmployee . '.name')
                ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $item->id = (int) $item->id;
            $result[(int) $item->id] = [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
            ];
        }
        CacheHelper::put(self::KEY_CACHE, $result);
        return $result;
    }

    /**
     * update employee code from employee card id
     * @param type $teamId
     * @param type $cardId
     */
    public function generateEmpCode($teamId, $cardId = null, $contractType = null)
    {
        if (!$cardId) {
            $cardId = $this->employee_card_id;
        }

        $borrowCode = getOptions::extraEmpPrefix()[getOptions::WORKING_BORROW];
        $employeeCode = static::getCodeFromCardId($cardId, $teamId, $contractType);
        // working type is not borrow => max string length = 10
        if (strlen($employeeCode) > 10 && !preg_match("/$borrowCode/", $employeeCode)) {
            $employeeCode = substr($employeeCode, 0, 10);
        }
        $this->employee_code = $employeeCode;
        $this->save();
    }

    /**
     * Get code prefix of employee
     *
     * @param int $teamId
     * @return string
     */
    public static function getCodePrefix($teamId, $contractType = null)
    {
        if ($contractType) {
            $workingCodes = getOptions::extraEmpPrefix();
            if (isset($workingCodes[$contractType])) {
                return $workingCodes[$contractType];
            }
        }
        $prefix = 'NV';
        $excerptTeams = static::getExcerptTeams();
        foreach ($excerptTeams as $teamArr) {
            $team = Team::where('code', $teamArr['code'])->select('id')->first();
            if (!$team) {
                continue;
            }
            $teamChildIds = TeamList::getTeamChildIds($team->id);
            if (in_array($teamId, $teamChildIds)) {
                $prefix = $teamArr['prefix'];
                break;
            }
        }
        return $prefix;
    }

    public static function getExcerptTeams()
    {
        return [
            [
                'code' => TeamConst::CODE_HANOI,
                'prefix' => 'NV'
            ],
            [
                'code' => TeamConst::CODE_JAPAN,
                'prefix' => 'JP'
            ],
            [
                'code' => TeamConst::CODE_DANANG,
                'prefix' => 'DN'
            ],
            [
                'code' => TeamConst::CODE_HCM,
                'prefix' => "HCM"
            ],
            [
                'code' => TeamConst::CODE_AI,
                'prefix' => "NV"
            ],
            [
                'code' => TeamConst::CODE_RS,
                'prefix' => "RS"
            ],
        ];
    }

    /**
     * Get code from cardId and team
     *
     * @param int $cardId
     * @param int $teamId
     * @return string
     */
    public static function getCodeFromCardId($cardId, $teamId, $contractType = null)
    {
        $workingCodes = getOptions::extraEmpPrefix();
        if (isset($workingCodes[$contractType])) {
            $numCard = $cardId;
        } else {
            $numCard = sprintf('%07d', intval($cardId));
        }
        return static::getCodePrefix($teamId, $contractType) . $numCard;
    }

    /**
     * generate suggest card id by team
     * @param int $teamId
     * @return int
     */
    public static function genSuggestCardId($teamId, $prefix = null, $contractType = null)
    {
        if (!$prefix) {
            $prefix = self::getCodePrefix($teamId, $contractType);
        }
        $maxCarIdEmp = (int) self::where('employee_code', 'like', $prefix . '%')->max('employee_card_id');
        return $maxCarIdEmp + 1;
    }

    /**
     * check exists employee code
     * @param type $empCode
     * @param type $employeeId
     * @return integer
     */
    public static function checkExistsEmpCode($empCode, $employeeId = null)
    {
        $existCode = Employee::where('employee_code', $empCode);
        if ($employeeId) {
            $existCode->where('id', '!=', $employeeId);
        }
        return $existCode->get()->count();
    }

    /**
     * Get employee update leave_at at today
     *
     * @return Employee collection
     */
    public static function getEmpUpdatedToday()
    {
        return self::whereRaw("DATE(updated_at) = CURDATE()")
                        ->select('id', 'leave_date')
                        ->get();
    }

    /**
     * Get employee join or leave in month
     *
     * @param int $month
     * @param int $year
     * @return Employee collection
     */
    public static function getEmpJoinOrLeaveInMonth($month, $year)
    {
        return self::whereRaw("MONTH(join_date) = ? AND YEAR(join_date) = ?", [$month, $year])
                        ->orWhereRaw("MONTH(leave_date) = ? AND YEAR(leave_date) = ?", [$month, $year])
                        ->select('id', 'join_date', 'leave_date')
                        ->get();
    }

    /**
     * get account of employee
     */
    public function getAccount()
    {
        return preg_replace('/\s|@.*/', '', $this->email);
    }

    /**
     * get Id by employee email
     * @param type $email
     * @return mixed
     */
    public static function getIdByEmail($email)
    {
        if ($id = CacheHelper::get('cache_employee_ids', $email)) {
            return $id;
        }
        $employee = self::where('email', $email)->first();
        if ($employee) {
            CacheHelper::put('cache_employee_ids', $employee->id, $email);
            return $employee->id;
        }
        return null;
    }

    /*
     * Option folk lib
     */

    public static function toOptionFolk()
    {
        $folk = EmpLib::getInstance()->folk();
        $newArr = [];
        foreach ($folk as $key => $value) {
            $a = [
                'value' => $key,
                'label' => $value,
            ];
            $newArr[] = $a;
        }
        return $newArr;
    }

    /**
     * get list option religion
     * @return array
     */
    public static function toOptionReligion()
    {
        $folk = EmpLib::getInstance()->relig();
        $newArr = [];
        foreach ($folk as $key => $value) {
            $a = [
                'value' => $key,
                'label' => $value,
            ];
            $newArr[] = $a;
        }
        return $newArr;
    }

    /**
     * get work experience of employee
     *
     * @return model
     */
    public function getWorkExperienceJapan()
    {
        if ($employeeSkills = CacheHelper::get(self::KEY_CACHE, $this->id)) {
            return $employeeSkills;
        }
        $employeeSkills = EmployeeWorkExperienceJapan::getItemsFollowEmployee($this->id);
        CacheHelper::put(self::KEY_CACHE, $employeeSkills, $this->id);
        return $employeeSkills;
    }

    /**
     * get projectExperience by employeeId and experienceId
     * @return $model
     */
    public function getProjectExperienceGroupWork($workIds = array())
    {
        $employeeSkills = ProjectExperience::getItemsFollowExperience($this->id, $workIds);
        return $employeeSkills;
    }

    /**
     * get all employee to check employee code
     *
     * @return array
     */
    public static function getAllEmployeeToCheck()
    {
        $collection = DB::table(self::getTableName())
                ->select(['id', 'employee_code'])
                ->get();
        if (!count($collection)) {
            return [];
        }
        $result = [];
        foreach ($collection as $item) {
            $result[$item->employee_code] = $item->id;
        }
        return $result;
    }

    /**
     * get old of employee
     *
     * @return int
     */
    public function getOld()
    {
        if (!$this->birthday) {
            return null;
        }
        $now = Carbon::now();
        $birthday = Carbon::parse($this->birthday);
        if ($now < $birthday) {
            $old = 0;
        } else {
            $old = $now->diffInYears($birthday);
        }
        return $old;
    }

    /*
     * get employee contact
     */

    public function contact()
    {
        return $this->hasOne('\Rikkei\Team\Model\EmployeeContact', 'employee_id', 'id');
    }

    /**
     * Get info employee by list code
     *
     * @param array $empCodes
     * @return mixed
     */
    public static function getEmployeeByListCode($empCodes = [])
    {
        return Employee::whereIn('employee_code', $empCodes)->pluck('id', 'employee_code')->toArray();
    }

    /**
     * Get new team by employeeId
     *
     * @param int $empId
     * @param array $selected
     * @return mixed
     */
    public static function getTeamNewByEmpId($empId, $selected = ['team_id', 'employee_id', 'role_id'])
    {
        return EmployeeTeamHistory::select($selected)->where('employee_id', $empId)
                        ->whereNull('end_at')
                        ->orderBy('start_at', 'desc')
                        ->first();
    }

    /*
     * get newest team
     */

    public function newestTeam($withRole = false)
    {
        return static::getTeamNewest($this->id, $withRole);
    }

    /*
     * get newest team by employee id
     */

    public static function getTeamNewest($empId, $withRole = false)
    {
        $teamTbl = Team::getTableName();
        $team = Team::select($teamTbl . '.*')
                ->join(TeamMember::getTableName() . ' as th', $teamTbl . '.id', '=', 'th.team_id');
        if ($withRole) {
            $team->join(Role::getTableName() . ' as role', 'th.role_id', '=', 'role.id')
                    ->addSelect('role.role as role_name', 'role.id as role_id');
        }
        return $team->where('th.employee_id', $empId)
                        ->orderBy('th.created_at', 'desc')
                        ->first();
    }

    /*
     * get newest team code of employee
     */

    public static function getNewestTeamCode($empId)
    {
        $teamNewest = self::getTeamNewest($empId);
        $teamHasCode = Team::getFirstHasCode($teamNewest);
        if ($teamHasCode) {
            return $teamHasCode->code;
        }
        return null;
    }

    /*
     * get newest team code
     */

    public function newestTeamCode()
    {
        return static::getNewestTeamCode($this->id);
    }

    /*
     * get me activity
     */

    public function meActivities()
    {
        return $this->hasMany('\Rikkei\Project\Model\MeActivity', 'employee_id', 'id');
    }

    /*
     * get nick name
     */

    public function getNickName()
    {
        return View::getNickName($this->email);
    }

    /**
     * Get all id of employee in Japan
     *
     * @return array: array ids of employees
     */
    public function getAllEmpIdInJapan()
    {
        $emTbl = self::getTableName();
        $teamTbl = Team::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        return self::join("{$teamMemberTbl}", "{$teamMemberTbl}.employee_id", "=", "{$emTbl}.id")
                        ->join("{$teamTbl}", "{$teamMemberTbl}.team_id", "=", "{$teamTbl}.id")
                        ->where("{$teamTbl}.code", 'LIKE', 'japan%')
                        ->selectRaw("distinct({$emTbl}.id)")
                        ->lists("{$emTbl}.id")
                        ->toArray();
    }

    /*
     * reject request asset
     */

    public function rejectRequestAsset($note = null)
    {
        $candidate = Candidate::where('employee_id', $this->id)->first();
        if ($candidate) {
            $requestAsset = $candidate->requestAsset;
            if ($requestAsset && in_array($requestAsset->status, [RequestAsset::STATUS_INPROGRESS, RequestAsset::STATUS_REVIEWED])) {
                $requestAsset->status = RequestAsset::STATUS_REJECT;
                $requestAsset->save();
                RequestAssetHistory::create([
                    'action' => RequestAssetHistory::ACTION_REJECT,
                    'request_id' => $requestAsset->id,
                    'employee_id' => auth()->id()
                ]);
            }
        }
    }

    /*
     * delete function
     */

    public function delete()
    {
        //before delete, if has request asset then reject
        $this->rejectRequestAsset();
        parent::delete();
    }

    /**
     * check and get avatar url
     * @return string
     */
    public function getAvatarUrl()
    {
        if ($this->avatar_url) {
            return $this->avatar_url;
        }
        return asset('common/images/noavatar.png');
    }

    /**
     * get current setting value
     * @param key: string
     * @return string
     */
    public function getSetting($key)
    {
        return EmployeeSetting::getKeyValue($this->id, $key);
    }

    /**
     * get employee key history
     * @param type $key
     * @return type
     */
    public function getSettingHistory($key)
    {
        return EmployeeSetting::getKeyValHistory($this->id, $key);
    }

    /**
     * check exist one team in array teams
     * @param [array] $teamIds
     * @param [string] $teamCheck
     * @return boolean
     */
    public static function checkExistTeam($teamIds, $teamCheck)
    {
        $treeTeam = Team::getTeamPathTree();
        $teamCheckId = Team::where('code', '=', $teamCheck)->first()->id;
        if (in_array($teamCheckId, $teamIds)) {
            return true;
        }
        foreach ($teamIds as $value) {
            if (isset($treeTeam[$value]) && in_array($teamCheckId, $treeTeam[$value]["parent"])) {
                return true;
            }
        }
        return false;
    }

    /**
     * get time woking in team of employee, follow timeKeeping table
     * @param [int] $monthOfTimeKeeping
     * @param [array] $empsIdOfTimeKeeping, $teamList
     * @return collection
     */
    public static function getTimeWorkOfTimeKeeping($monthOfTimeKeeping, $empsIdOfTimeKeeping, $teamList)
    {
        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();
        $tblEmp = self::getTableName();
        return self::select(
                                "tblEmpTeam.id as id_history", "tblEmpTeam.employee_id", "tblEmpTeam.start_at", "tblEmpTeam.end_at"
                        )
                        ->leftJoin("{$tblEmpTeamHistory} as tblEmpTeam", "tblEmpTeam.employee_id", '=', "$tblEmp.id")
                        ->where(function ($query) use ($monthOfTimeKeeping) {
                            $query->whereMonth("end_at", '=', $monthOfTimeKeeping)
                            ->orWhereNull('end_at');
                        })
                        ->whereIn("tblEmpTeam.employee_id", $empsIdOfTimeKeeping)
                        ->whereIn("tblEmpTeam.team_id", $teamList)
                        ->get();
    }

    /**
     * get class intance permission skillsheet.
     *
     * @return boolean.
     */
    public function isIntanceSkillsheet()
    {
        return PermissionView::getInstance()->isAllow(self::ROUTE_VIEW_SKILLSHEET);
    }

    /**
     * get class intance permission view profile.
     *
     * @return boolean.
     */
    public function isIntanceViewProfile()
    {
        if (PermissionView::getInstance()->isScopeTeam(null, 'team::member.profile.index') ||
                PermissionView::getInstance()->isScopeCompany(null, 'team::member.profile.index')) {
            return true;
        }
        return false;
    }

    /**
     * get time work of employee when employee register  change working times
     * @param  [collection] $employee
     * @param null|string $teamCodePrefix
     * @return array
     */
    public static function getTimeWorkEmployeeDate($date, $employee = null, $teamCodePrefix = null)
    {
        if (!$employee) {
            $employee = PermissionView::getInstance()->getEmployee();
        }
        if (!$date) {
            $date = Carbon::now()->format('Y-m-01');
        } else {
            $date = Carbon::parse($date)->format('Y-m-d');
        }
        if ($teamCodePrefix === null) {
            $teamCodePrefix = Team::getOnlyOneTeamCodePrefix($employee);
        }

        $workingTimeSettingOfEmp = (new WorkingTimeDetail())->getWorkingTimeInfo($employee->id, $date);
        return ManageTimeView::findTimeSettingDate($workingTimeSettingOfEmp, $teamCodePrefix, $date);
    }


    /**
     * get role and team name
     * @return string concat <role> - <team name>
     */
    public function getRoleAndTeams()
    {
        $roles = TeamMember::where('employee_id', $this->id)
                ->join('roles', 'team_members.role_id', '=', 'roles.id')
                ->join('teams', 'team_members.team_id', '=', 'teams.id')
                ->select('roles.role', 'teams.name', 'teams.id')
                ->get();

        $result = '';
        if (!$roles->isEmpty()) {
            foreach ($roles as $role) {
                $result .= $role->role . ' - ' . $role->name . ', ';
            }
        }

        return trim($result, ', ');
    }

    /**
     * get all special roles of employee
     * @return collection
     */
    public function specialRoles()
    {
        return $this->belongsToMany('\Rikkei\Team\Model\Role', 'employee_roles', 'employee_id', 'role_id');
    }

    /**
     * check if this employee has role Administartor
     * @return boolean
     */
    public function isAdmin()
    {
        return (bool) $this->specialRoles()
            ->where('role', Role::ROLE_ADMIN_NAME)
            ->first();
    }

    /**
     * Work info
     * @return \Rikkei\Team\Model\EmployeeWork
     */
    public function getWorkInfo()
    {
        $r = EmployeeWork::where('employee_id', $this->id)->first();
        if ($r) {
            return $r;
        }
        return new EmployeeWork();
    }

    /**
     * get list team names
     * @return string
     */
    public function getTeamNames()
    {
        $teams = $this->teams;
        if ($teams->isEmpty()) {
            return null;
        }
        return $teams->implode('name', ', ');
    }

    /**
     * get time join company of employees when time join == time salary calculation
     * @param [int] $dateTimeKeeping
     * @param [int] $empsIdOfTimeKeeping [ids employee in salary calculation]
     */
    public static function getTimeJoinCompany($dateTimeKeeping, $empsIdOfTimeKeeping)
    {
        return self::select(
            "id",
            "employee_code",
            DB::raw("DATE(join_date) as join_date")
        )
        ->whereIn("id", $empsIdOfTimeKeeping)
        ->WhereDate('join_date', '>=', $dateTimeKeeping)
        ->get();
    }

    /**
     * get Employees Leave Day
     *
     * @return collection
     */
    public static function getEmployeesLeaveDay()
    {
        $now = Carbon::now()->toDate();
        $tableLeaveDays = LeaveDay::getTableName();
        $tableEmployee = self::getTableName();

        return self::join($tableLeaveDays, $tableEmployee . '.id', '=', $tableLeaveDays . '.employee_id')
            ->whereNotNull($tableEmployee . '.leave_date')
            ->whereDate($tableEmployee . '.leave_date', '<', $now->format('Y-m-d'))
            ->select("{$tableEmployee}.id as employeeId")
            ->get();
    }

    /**
     * get Employees
     * @param id employee
     * @return collection
     */
    public static function getEmployeesById($ids = null)
    {
        $pager = Config::getPagerData();
        $employeeTable = Employee::getTableName();
        $selectFields = [
            "{$employeeTable}.id as id",
            "{$employeeTable}.employee_code as employee_code",
            "{$employeeTable}.name as name",
            "{$employeeTable}.email as email",
            "user.avatar_url"
        ];

        return Employee::select($selectFields)
            ->leftJoin(User::getTableName() . ' as user', $employeeTable . '.id', '=', 'user.employee_id')
            ->whereIn('id', $ids)->get();
    }

    /**
     * get Employees
     * @param id employee
     * @return collection
     */
    public static function getEmployeesByGridData($teamId = null, $name = null, $email = null)
    {
        $employeeTable = self::getTableName();
        $teamTable = Team::getTableName();
        $teamMember = TeamMember::getTableName();
        $currentDay = date("Y-m-d");
        $selectFields = [
            "{$employeeTable}.id as id",
            "{$employeeTable}.employee_code as employee_code",
            "{$employeeTable}.name as name",
            "{$employeeTable}.email as email",
            "user.avatar_url"
        ];
        $collectionModel = Employee::select($selectFields)
                    ->leftJoin(User::getTableName() . ' as user', $employeeTable . '.id', '=', 'user.employee_id')
                    ->whereNull("{$employeeTable}.deleted_at");
        $collectionModel->where(function($query) use ($employeeTable, $currentDay) {
            $query->whereDate("{$employeeTable}.leave_date", ">=", $currentDay)
                ->orWhereNull("{$employeeTable}.leave_date");
        });
        if ($teamId) {
            $collectionModel->leftJoin($teamMember . ' as teamMember', $employeeTable. '.id', '=', 'teamMember.employee_id')
                ->whereIn('teamMember.team_id', $teamId);
        }
        if ($name) {
            $name = trim($name);
            $collectionModel->where("{$employeeTable}.name", "LIKE", "%{$name}%");
        }
        if ($email) {
            $email = trim($email);
            $collectionModel->where("{$employeeTable}.email", "LIKE", "%{$email}%");
        }

        return $collectionModel->paginate(10, ['*'], 'productsEmployee');
    }

    /**
     * get all employees trail date or offcial
     * @param  [date] $date [Y-m-d]
     * @param  [array] $teamIds
     * @return [collection]
     */
    public function getEmpTrialOrOffcial($date, $teamIds)
    {
        $cbDate = Carbon::parse($date);
        $tblEmp = Employee::getTableName();
        $tblTeamMember = TeamMember::getTableName();
        $empWorkTbl = EmployeeWork::getTableName();

        return TeamMember::select(
            "{$tblEmp}.id as employee_id",
            "{$tblEmp}.offcial_date",
            "{$tblEmp}.trial_date",
            "{$tblEmp}.leave_date"
        )
        ->join("{$tblEmp}", "{$tblEmp}.id", "=", "{$tblTeamMember}.employee_id")
        ->join("{$empWorkTbl}", "{$tblEmp}.id", "=", "{$empWorkTbl}.employee_id")
        ->whereIn("{$tblTeamMember}.team_id", $teamIds)
        ->where(function ($query) use ($tblEmp, $cbDate) {
            $query->whereNull("{$tblEmp}.leave_date")
                ->orWhereDate("{$tblEmp}.leave_date", '>=', $cbDate->startOfMonth()->format('Y-m-d'));
        })
        ->whereNull("{$tblEmp}.deleted_at")
        ->whereDate("{$tblEmp}.join_date", "<=", $date)
        ->whereNotIn("{$tblEmp}.account_status", [getOptions::FAIL_CDD])
        ->where(function ($query) use ($tblEmp, $date) {
            $query->whereDate("{$tblEmp}.trial_date", '<=', $date)
                ->orWhereDate("{$tblEmp}.offcial_date", '<=', $date);
        })
        ->groupBy("{$tblEmp}.id")
        ->get();
    }

    /**
     * get suggest employee code of employee
     * @param string $empCode
     * @param type $empId
     * @return int
     */
    public static function getSuggestCardId($empCode, $empId = null)
    {
        preg_match("/\d/", $empCode, $matches, PREG_OFFSET_CAPTURE );
        $prefix = '';
        if (count($matches) && count($matches[0])) {
            $prefix = substr($empCode, 0, $matches[0][1]);
        }
        $collection = Employee::where('employee_code', '>=', $empCode)
            ->where('employee_code', 'LIKE', "{$prefix}%")
            ->whereRaw('LENGTH(employee_code) = ' . strlen($empCode));
        if ($empId) {
            $collection->where('id', '!=', $empId);
        }
        $empCodes = $collection->groupBy('employee_code')
            ->orderBy('employee_code', 'ASC')
            ->get()->pluck('employee_code')->toArray();
        // not found employee codes
        if (count($empCodes) === 0) {
            return 0;
        }
        // not exist employee code
        if ($empCodes[0] !== $empCode) {
            return 0;
        }
        $curEmpCode = (int) substr($empCode, strlen($prefix));
        foreach ($empCodes as $eC) {
            if ($eC !== $prefix . sprintf('%07d', intval($curEmpCode))) {
                return $curEmpCode;
            }
            $curEmpCode++;
        }
        return $curEmpCode;
    }

    /**
     * get suggest email of employee
     * @param string $email
     * @param type $empId
     * @return string
     */
    public static function getSuggestEmail($email, $empId = null)
    {
        $emailExplode = explode('@', $email);
        if (count($emailExplode) !== 2) {
            return '';
        }
        $prefix = $emailExplode[0];
        $suffixes = $emailExplode[1];
        // split text and number in email
        preg_match("/\d+$/", $prefix, $matches, PREG_OFFSET_CAPTURE);
        if (count($matches) && count($matches[0])) {
            $curEmailIndex = (int) $matches[0][0];
            $prefixEmail = substr($prefix, 0, $matches[0][1]);
        } else {
            $curEmailIndex = 1;
            $prefixEmail = $prefix;
        }
        $collection = Employee::where('email', 'LIKE', "{$prefixEmail}%")
            ->where('email', 'LIKE', "%{$suffixes}")
            ->where('email', '>=', $email);
        if ($empId) {
            $collection->where('id', '!=', $empId);
        }
        $emailEmps = $collection->groupBy('email')
            ->orderBy('email', 'ASC')
            ->get()->pluck('email')->toArray();
        // not found emails
        if (count($emailEmps) === 0) {
            return '';
        }
        // not exist email
        if ($emailEmps[0] !== $email) {
            return '';
        }
        while (true) {
            if (!in_array($prefixEmail . $curEmailIndex . '@' . $suffixes, $emailEmps)) {
                break;
            }
            $curEmailIndex++;
        }
        return $prefixEmail . $curEmailIndex . '@' . $suffixes;
    }

    /**
     * check exists employee card id in a branch
     * @param $teamId
     * @param $empCardId
     * @param $empId
     * @return int
     */
    public static function checkExistsEmpCardId($teamId, $empCardId, $empId = null)
    {
        $teamCandidate = Team::find($teamId);
        $branchCodeCandidate = ($teamCandidate && $teamCandidate->branch_code) ? $teamCandidate->branch_code : TeamConst::CODE_HANOI;
        $tblCandidate = Candidate::getTableName();
        $tblEmployee = self::getTableName();
        $tblTeam = Team::getTableName();
        $collection = self::join($tblCandidate, "{$tblCandidate}.employee_id", '=', "{$tblEmployee}.id")
            ->leftJoin($tblTeam, "{$tblTeam}.id", '=', "{$tblCandidate}.team_id")
            ->where("{$tblEmployee}.employee_card_id", '=', $empCardId)
            ->where("{$tblTeam}.branch_code", $branchCodeCandidate);
        if ($empId) {
            $collection->where("{$tblEmployee}.id", '<>', $empId);
        }
        return $collection->count();
    }

    public static function changeCacheVersion($employeeId = null)
    {
        if($employeeId){
            $employeeInfo = self::find($employeeId);
            if($employeeInfo){
                $newVersion = (int)$employeeInfo->cache_version + 1;
                $employeeInfo->update(['cache_version'=>$newVersion]);
            }
        }
        else
        {
            Employee::where(function ($sql){
                   $sql->whereNull('leave_date')->orWhere('leave_date','>=',Carbon::now()->toDateTimeString());
                })
            ->update([
                'cache_version'=>DB::raw('IF(ISNULL(cache_version),1,cache_version + 1)')
            ]);
        }
    }

    /**
     * get employee is working by id
     *
     * @param int $id
     * @return $collection
     */
    public static function getEmpIsWorking($id)
    {
        $currentDay = date("Y-m-d");
        $employee = self::where('id', '=', $id)
            ->where(function ($query) use ($currentDay) {
                $query->whereDate('leave_date', '>=', $currentDay)
                    ->orWhereNull('leave_date');
            })
            ->whereNull('deleted_at')->first();
        return $employee;
    }

    // computes the difference of array 2D
    public function diffArray2D($aryX, $aryY)
    {
        foreach ($aryX as $key => $valueX) {
            if (in_array($valueX, $aryY)) {
                unset($aryX[$key]);
            }
        }
        return $aryX;
    }

    /**
     * Get list employee and certificates
     *
     * @return mixed
     */

    public function employeeCerties()
    {
        return self::hasMany(EmployeeCertificate::class, 'employee_id', 'id')
            ->leftJoin('employee_certies_image', 'employee_certies_image.employee_certies_id', '=', 'employee_certies.id')
            ->select('employee_certies.*', 'employee_certies_image.image as image');
    }

    /**
     * Get team of employee
     *
     * @return mixed
     */
    public function getTeamMember()
    {
        return self::hasMany(TeamMember::class, 'employee_id', 'id')
            ->join('teams', 'team_members.team_id', '=', 'teams.id')
            ->select('teams.name', 'team_members.*');
    }

    /**
     * Get status list validity
     *
     * @return array
     */
    public static function getStatusListValidity()
    {
        return [
            self::STATUS_VALIDITY_ALL => 'level status all',
            self::STATUS_VALIDITY => 'level status valid',
            self::STATUS_INVALIDITY => 'level status invalid',
            self::STATUS_NOT_INPUT => 'not input'
        ];
    }

    /**
     * Get team of member
     *
     * @return mixed
     */
    public function getTeamOfEmployee()
    {
        return self::hasMany(TeamMember::class, 'employee_id', 'id')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->select('teams.name as team_name', 'teams.leader_id as leader_id', 'team_members.*');
    }

    public function getLeaderOfTeam()
    {
        $collection = self::hasMany(TeamMember::class, 'employee_id', 'id')
            ->join('teams', 'teams.id', '=', 'team_members.team_id')
            ->select('teams.name as team_name', 'teams.leader_id as leader_id', 'team_members.*');
        $collection = $collection->join('employees', 'employees.id', '=', 'leader_id');
        $collection = $collection->select('employees.name');
        return $collection;
    }

    /**
     * Get education detail
     *
     * @return mixed
     */
    public function educationClassDetail()
    {
        $educationClassDetailTable = EducationClassDetail::getTableName();
        $educationClassShiftTable = EducationClassShift::getTableName();
        return self::hasMany(EducationClassDetail::class, 'employee_id', 'id')
            ->join("{$educationClassShiftTable}", "{$educationClassShiftTable}.id", '=', "{$educationClassDetailTable}.shift_id")
            ->select("{$educationClassDetailTable}.*",
                "{$educationClassShiftTable}.name as shift_name",
                \DB::raw('COUNT(*) as count_study'),
                \DB::raw("SUM(TIMESTAMPDIFF(MINUTE, {$educationClassShiftTable}.start_date_time, {$educationClassShiftTable}.end_date_time)) as sum_time")
            )->groupBy("{$educationClassDetailTable}.role", "{$educationClassDetailTable}.employee_id");
    }

    /**
     * Get employee is working
     *
     * @param $id
     * @return mixed
     */
    public static function getEmployeeWorkingById($id)
    {
        return self::where('id', $id)->where('leave_date', null)->first();
    }

    /**
     * Get list position education
     *
     * @return array
     */
    public static function getRolesPosition()
    {
        return [
            self::ROLE_STUDENT => 'role_student',
            self::ROLE_TEACHER => 'role_teacher'
        ];
    }

    /**
     *
     *
     * @param array $empCodes
     * @return mixed
     */
    /**
     * Get list employee by list code and lis team id
     *
     * @param $empCodes
     * @param $teamIdAllow
     * @return mixed
     */
    public function getEmpByCodeTeams($empCodes, $teamIdAllow)
    {
        $tblTM = TeamMember::getTableName();
        $tblEmp = self::getTableName();

        return Employee::select(
            "{$tblEmp}.id",
            "{$tblEmp}.employee_code",
            "{$tblEmp}.name"
        )
        ->leftJoin("{$tblTM} as tm", 'tm.employee_id', "=", "{$tblEmp}.id")
        ->whereIn('employee_code', $empCodes)
        ->whereIn('tm.team_id', $teamIdAllow)
        ->get();
    }

    /**
     * Get employeeID like name or email
     * @param $name
     * @return array
     */
    public static function getEmployeeIDByNameOrEmail($name)
    {
        return Employee::where("name", 'Like', '%' . trim($name) . '%')
            ->orWhere("email", 'Like', '%' . trim($name) . '%')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Get employeeID like email
     * @param $email
     * @return mixed
     */
    public static function getEmployeeIDLikeEmail($email)
    {
        return Employee::where("email", 'Like', '%' . trim($email) . '%')
            ->pluck('id')
            ->toArray();
    }

    /**
     * Danh sách ID nhân viên đã nghỉ việc
     * @return array
     */
    public static function getLeaverIds()
    {
        $now = Carbon::now();
        return Employee::where(function ($query) use ($now) {
            $query->orWhereNotNull('leave_date')
                ->orWhere('leave_date', '<=', $now);
        })
            ->pluck('id')
            ->toArray();
    }

    /**
     * Lấy nhân viên đã nghỉ việc theo email
     * @return mixed
     */
    public static function getLeaverByEmail($email)
    {
        return DB::table('employees')->where('email', $email)
            ->where(function ($sql) {
                $sql->whereNotNull('deleted_at')
                    ->orWhere('leave_date', '<', date('Y-m-d H:i:s'));
            })
            ->first();
    }

    public static function getListSpecialDate()
    {
        $year = Carbon::now()->format('Y');
        $month = Carbon::now()->format('m');
        $date = Carbon::now()->format('d');
        $currentDay = date("Y-m-d");
        $response = self::select(
            'id',
            'name',
            'email',
            DB::raw("coalesce(trial_date, offcial_date) as date")
        )
            ->where(function ($query) use ($year, $month, $date) {
                $query->orWhere(function ($sql) use ($year, $month, $date) {
                    $sql->whereNotNull('trial_date')
                        ->where(DB::raw("YEAR(trial_date)"), '!=', $year)
                        ->where(DB::raw("MONTH(trial_date)"), $month)
                        ->where(DB::raw("DAY(trial_date)"), $date);
                })->orWhere(function ($sql) use ($year, $month, $date) {
                    $sql->whereNull('trial_date')
                        ->whereNotNull('offcial_date')
                        ->where(DB::raw("YEAR(offcial_date)"), '!=', $year)
                        ->where(DB::raw("MONTH(offcial_date)"), $month)
                        ->where(DB::raw("DAY(offcial_date)"), $date);
                });
            });

        if ($year % 4 != 0 && Carbon::now()->format('m-d') == '03-01') {
            $response->orWhere(function ($query) use ($year) {
                $query->orWhere(function ($sql) use ($year) {
                    $sql->whereNotNull('trial_date')
                        ->where(DB::raw("YEAR(trial_date)"), '!=', $year)
                        ->where(DB::raw("MONTH(trial_date)"), 2)
                        ->where(DB::raw("DAY(trial_date)"), 29);
                })->orWhere(function ($sql) use ($year) {
                    $sql->whereNull('trial_date')
                        ->whereNotNull('offcial_date')
                        ->where(DB::raw("YEAR(offcial_date)"), '!=', $year)
                        ->where(DB::raw("MONTH(offcial_date)"), 2)
                        ->where(DB::raw("DAY(offcial_date)"), 29);
                });
            });
        }

        return $response
            ->where(function ($query) use ($currentDay) {
                $query->orWhereDate("leave_date", ">=", $currentDay)
                    ->orWhereNull("leave_date");
            })
            ->get()
            ->toArray();
    }

    /**
     * get employees in Japan for N years
     * @return array $empsNYearInJapan
     */
    public static function getEmpsNYearsInJapan()
    {
        $tblTeam = Team::getTableName();
        $tblEmpTeamHistory = EmployeeTeamHistory::getTableName();
        // get all employees has branch code is 'japan'
        $empsInJapan = Team::select($tblEmpTeamHistory . '.employee_id', $tblEmpTeamHistory . '.start_at', $tblEmpTeamHistory . '.end_at')
            ->join($tblEmpTeamHistory, $tblEmpTeamHistory . '.team_id', '=', $tblTeam . '.id')
            ->whereNull($tblEmpTeamHistory . '.deleted_at')
            ->where($tblTeam . '.branch_code', Team::CODE_PREFIX_JP)
            ->orderBy($tblEmpTeamHistory . '.start_at', 'asc')
            ->get();
        $empsInJapan = $empsInJapan->groupBy('employee_id')->toArray();
        $empsNYearInJapan = [];
        $now = Carbon::now();
        foreach ($empsInJapan as $empId => $teamsInJapan) {
            $emp = [];
            foreach ($teamsInJapan as $key => $team) {
                if ($key === 0) {
                    $emp['start_at'] = $team['start_at'];
                    $emp['end_at'] = $team['end_at'];
                } else {
                    // Not finished in team Japan
                    if ($emp['end_at'] === null) {
                        break;
                    }
                    // thời gian kết thúc tại team trước < thời gian bắt đầu của team hiện tại => cập nhật lại theo team hiện tại
                    if ($emp['end_at'] < $team['start_at']) {
                        $emp['start_at'] = $team['start_at'];
                        $emp['end_at'] = $team['end_at'];
                    } else {
                        $emp['end_at'] = $team['end_at'] === null ? null : max($team['end_at'], $emp['end_at']);
                    }
                }
            }

            $startAt = Carbon::parse($emp['start_at']);
            // Sau ngày thay đổi phép: 01/03/2020 => ngày bắt đầu tính phép là (start_at + 1 ngày)
//            if ($startAt->format('Y-m-d') >= LeaveDay::DATE_CHANGE_LEAVE_DAY) {
//                $timeStartLeaveDay = $startAt->addDay(1);
//            } else {
            // Ngày bắt đầu tính phép
            $timeStartLeaveDay = TimeHelper::addMonth($startAt, 6);
//            }
            // $distanceYear: số năm làm việc tại Nhật: năm 1 tính từ ngày bắt đầu có phép ($timeStartLeaveDay) đến 1 năm sau
            $distanceYear = $now->year - $timeStartLeaveDay->year;
            $distanceYear += $now->month > $timeStartLeaveDay->month ? 1 : 0;
            // năm đầu tiên tính phép => start_at không thay đổi
            if ($distanceYear === 1) {
                $emp['start_at_N_year'] = $timeStartLeaveDay->toDateString();
            } else {
                $emp['start_at_N_year'] = Carbon::parse($timeStartLeaveDay)->addYear($distanceYear - 2)->addYear(1)->toDateString();
            }
            $emp['end_at_N_year'] = Carbon::parse($emp['start_at_N_year'])->addYear(1)->toDateString();
            if ($emp['end_at'] === null) $emp['end_at'] = '9999-12-31';
            // Chưa có kết thúc ở team Japan hoặc có ngày kết thúc lớn hơn (ngày bắt đầu tính phép + 12*n tháng)
            // Thêm nv theo mốc 16-28.... tháng
            if (($emp['end_at'] >= $emp['end_at_N_year']
                    && TimeHelper::addMonth(Carbon::parse($emp['start_at_N_year']), 10)->format('m-d') === $now->format('m-d'))
                || ($emp['end_at'] >= TimeHelper::addMonth(Carbon::parse($emp['start_at_N_year']), 6)->toDateString()
                    && TimeHelper::addMonth(Carbon::parse($emp['start_at_N_year']), 6)->format('m-d') === $now->format('m-d'))
            ) {
                $empsNYearInJapan[$empId] = $emp;
            }
        }
        return $empsNYearInJapan;
    }

    /**
     * get leader of team
     * @param string $branchCode
     * @return collection
     */
    public function getEmpLeaderPQAByBranch($branchCode)
    {
        $code = $branchCode . '_pqa';
        return Team::select(
            'employees.id',
            'employees.id as employee_id',
            'employees.name',
            'employees.email',
            'teams.id as team_id',
            'teams.name as team_name'
        )
        ->leftjoin('employees', 'teams.leader_id', '=', 'employees.id')
        ->where('code', $code)
        ->whereNull('teams.deleted_at')
        ->first();
    }

    public static function findByEmail($email, $checkLeaveDay = false)
    {
        $employee = Employee::where('email', $email);
        if ($checkLeaveDay) {
            $employee = $employee->where(function($query) {
                $query->whereNull('leave_date')
                    ->orWhere('leave_date', '>', Carbon::now()->format('Y-m-d'));
            });
        }
        return $employee->first();
    }

    public static function checkStatusEmployeeIsWorking($email)
    {
        $employee = Employee::where('email', $email)
            ->where(function($query) {
                $query->whereNull('leave_date')
                    ->orWhere('leave_date', '>', Carbon::now()->format('Y-m-d'));
            })->first();
        if (!$employee) {
            return null;
        }
        
        $candidate = Candidate::where('employee_id', $employee->id)->first();
        if (isset($candidate) && $candidate->status != getOptions::WORKING) {
            return null;
        }

        return $employee;
    }

}
