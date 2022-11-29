<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Illuminate\Support\Facades\DB;
use Exception;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Recruitment\Model\CddMailSent;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\Model\CandidateLanguages;
use Rikkei\Resource\Model\CandidateProgramming;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\View\getOptions;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Resource\Model\Languages;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Test\Models\Type;
use Lang;
use DateTime;
use Illuminate\Support\Facades\Config as SupportConfig;
use Rikkei\Resource\Model\CandidateRequest;
use Rikkei\Resource\Model\CandidateTeam;
use Rikkei\Resource\Model\CandidatePosition;
use Rikkei\Team\Model\Team;
use Rikkei\Test\Models\Test;
use Rikkei\Test\Models\Result;
use Rikkei\Resource\Model\Channels;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\EmployeeContact;
use Rikkei\Assets\Model\RequestAsset;
use Carbon\Carbon;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\EmployeeContractHistory;
use Illuminate\Support\Facades\Log;
use Rikkei\Resource\Model\RicodeTest;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Resource\Model\ChannelCostLog;
use Illuminate\Support\Facades\Session;
use Rikkei\Core\View\CoreLang;

class Candidate extends CoreModel
{
    protected $table = 'candidates';

    use SoftDeletes;

    const KEY_CACHE = 'candidates';
    const UPLOAD_CV_FOLDER = 'resource/candidate/cv/';

    /**
     * email company
     */
    const EMAIL_COMPANY = '@rikkeisoft.com';
    const ATTACH_FOLDER = 'resource/candidate/attach/';
    const CV_FOLDER = 'resource/candidate/cv/';

    /**
     * File tutorial new employee
     */
    const FILE_NAME_TUTORIAL = 'Huong_dan_nhan_vien_moi_Rikkeisoft.pdf';
    const FILE_NAME_INVITE = 'Thu_moi_lam_viec';

    /**
     * Candidate status class
     */
    const CLASS_CONTACTING = 'bg-yellow';
    const CLASS_ENTRY_TEST = 'bg-teal';
    const CLASS_INTERVIEWING = 'bg-aqua';
    const CLASS_OFFERING = 'bg-light-blue';
    const CLASS_END = 'bg-green';
    const CLASS_FAIL = 'bg-red';
    const CLASS_DEFAULT = 'bg-gray';
    const CLASS_WORKING = 'bg-blue';
    const CLASS_PREPARING = 'bg-aqua';

    /**
     * Type of candidate
     */
    const FRESHER = 1;
    const JUNIOR = 2;
    const SENIOR = 3;
    const MIDDLE = 4;

    /**
     * Max length of request title in create|edit candidate page
     */
    const SUB_TITLE_LEN = 50;

    const STATUS_OFFER_FAIL = 2;
    const STATUS_EMPLOYEE_FAIL = 12;
    /**
     * Rate salary trial
     */
    const SALARY_RATE_TRIAL = '85%';

    /**
     * Count month(s) work trial
     */
    const TRIAL_TIME = 2;

    /**
     * Mail type
     */
    const MAIL_OFFER = 1;
    const MAIL_OFFER_HH3 = 11;
    const MAIL_OFFER_HH4 = 12;
    const MAIL_OFFER_DN = 13;
    const MAIL_OFFER_HCM = 14;
    const MAIL_OFFER_JP = 15;
    const MAIL_OFFER_HANDICO = 16;
    const MAIL_INTERVIEW = 2;
    const MAIL_TEST = 3;
    const MAIL_CONTACT = 4;
    const MAIL_INTERVIEW_TEST_HH3 = 21;
    const MAIL_INTERVIEW_TEST_HH4 = 22;
    const MAIL_INTERVIEW_TEST_DN = 23;
    const MAIL_INTERVIEW_TEST_HCM = 24;
    const MAIL_INTERVIEW_TEST_JP = 25;
    const MAIL_INTERVIEW_TEST_HANDICO = 26;
    const MAIL_INTERVIEW_CONFIRM_JP = 35;
    const MAIL_INTERVIEW_FAIL = 7;
    const MAIL_INTERVIEW_FAIL_JP = 45;
    const MAIL_INTERVIEW_FAIL_HN = 46;
    const MAIL_INTERVIEW_FAIL_DN = 47;
    const MAIL_INTERVIEW_FAIL_HCM = 48;
    const MAIL_RECRUITER = 5;
    const MAIL_THANKS = 6;

    /**
     * RESULT
     */
    const PASS = 'PASS';
    const FAIL = 'FAIL';

    /**
     *format date time
     */
    const FORMAT_DATE = '/^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((1[6-9]|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((1[6-9]|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((1[6-9]|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/';

    /**
     * test mark
     */
    const GMAT = 'GMAT';
    const SPECIALIZE = 'Other';

    /**
     * Gender
     */
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 0;

    /**
     * CV or attach file
     */
    const TYPE_CV = 0;
    const TYPE_ATTACH = 1;

    /*
     * Store last time columns selected in search advance page
     */
    const COOKIE_COLUMN_SEARCH_ADVANCE = 'column_selected_search';

    /**
     * Opearators in candidate advance search
     */
    const COLUMN_DEFAULT = 1;
    const COMPARE_EQUAL = '=';
    const COMPARE_GREATER = '>';
    const COMPARE_SMALLER = '<';
    const COMPARE_GREATER_EQUAL = '>=';
    const COMPARE_SMALLER_EQUAL = '<=';
    const COMPARE_NOT_EQUAL = '<>';
    const COMPARE_LIKE = 'LIKE %%';
    const COMPARE_IS_NULL = 'IS NULL';
    const COMPARE_IS_NOT_NULL = 'IS NOT NULL';

    /**
     * Type candidate(from webvn or intranet)
     */
    const TYPE_FROM_INTRANET = 1;
    const TYPE_FROM_WEBVN = 2;
    const TYPE_FROM_PRESENTER = 3;
    const TYPE_FROM_WEBVN_INTEREST = 4;
    const TYPE_FROM_WEBVN_INTEREST_NOT_EMAIL = 5;

    /**
     * Columns in candidate advance search
     */
    const COLUMN_SEARCH = ['id', 'fullname', 'email', 'request', 'group_of_request', 'position_apply', 'interested', 'status_update_date',
        'channel', 'birthday', 'mobile', 'university', 'certificate', 'old_company', 'experience', 'received_cv_date',
        'test_plan', 'test_mark', 'test_result', 'interview_plan', 'interview2_plan', 'interview_result',
        'created_by', 'test_note', 'created_at', 'updated_at', 'status', 'interview_note', 'offer_date',
        'offer_result', 'offer_feedback_date', 'offer_note', 'contact_result', 'interviewer',
        'recruiter', 'presenter_id', 'found_by', 'start_working_date', 'trial_work_end_date', 'screening',
        'type', 'identify', 'skype', 'other_contact', 'gender', 'language', 'program', 'group', 'working_type', 'type_candidate', 'programming_language_id', 'cost'
    ];

    const ROUTE_EXPORT_SEARCH = 'resource::candidate.export_search';

    /**
     * store this object
     * @var object
     */
    protected static $instance;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['request_id', 'channel_id', 'fullname', 'birthday', 'team_id', 'note', 'interested', 'status_update_date',
        'email', 'mobile', 'position_apply', 'university', 'certificate',
        'experience', 'received_cv_date', 'test_plan', 'interview_plan', 'presenter_id',
        'test_date', 'test_result', 'interview_email_date', 'interview_calling_date', 'interview_date',
        'interview_result', 'created_by', 'test_note', 'status', 'interview_note',
        'offer_date', 'offer_salary', 'offer_result', 'offer_feedback_date', 'found_by',
        'offer_note', 'contact_result', 'test_mark', 'cv', 'interviewer', 'recruiter', 'trial_work_start_date',
        'trial_work_end_date', 'start_working_date', 'end_working_date', 'screening', 'type',
        'interview2_plan', 'interview2_date', 'test_mark_specialize',
        'identify','issued_date', 'issued_place', 'home_town', 'offer_start_date', 'had_worked', 'relative_worked',
        'test_gmat_point', 'test_option_type_ids',
        'employee_id', 'gender', 'official_date', 'parent_id',
        'position_apply_input', 'channel_input', 'offer_salary_input',
        'skype', 'other_contact', 'contract_length', 'working_type', 'old_company', 'type_candidate', 'cost',
        'calendar_id', 'event_id', 'contact_note', 'trainee_start_date', 'trainee_end_date', 'is_old_employee', 'programming_language_id', 'comment'
    ];

    public function getTypeOffer()
    {
        return [
                self::MAIL_OFFER,
                self::MAIL_OFFER_HH3,
                self::MAIL_OFFER_HH4,
                self::MAIL_OFFER_DN,
                self::MAIL_OFFER_HCM,
                self::MAIL_OFFER_JP,
                self::MAIL_OFFER_HANDICO,
            ];
    }

    /**
     * The users that belong to the action.
     */
    public function candidateLang()
    {
        $tableCandidateLang = CandidateLanguages::getTableName();
        return $this->belongsToMany('Rikkei\Resource\Model\Languages', $tableCandidateLang, 'candidate_id', 'lang_id');
    }

    /**
     * The users that belong to the action.
     */
    public function candidateProgramming()
    {
        $tableCandidateProgramming = CandidateProgramming::getTableName();
        return $this->belongsToMany('Rikkei\Resource\Model\Programs', $tableCandidateProgramming, 'candidate_id', 'programming_id');
    }

    /**
     * The users that belong to the action.
     */
    public function candidateRequest()
    {
        $tableCandidateRequest = CandidateRequest::getTableName();
        return $this->belongsToMany('Rikkei\Resource\Model\ResourceRequest', $tableCandidateRequest, 'candidate_id', 'request_id');
    }

    /**
     * The users that belong to the action.
     */
    public function candidateTeam() {
        $tableCandidateTeam = CandidateTeam::getTableName();
        return $this->belongsToMany('Rikkei\Team\Model\Team', $tableCandidateTeam, 'candidate_id', 'team_id');
    }

    public function ricodeTest() {
        return $this->hasOne(RicodeTest::class, 'candidate_id', 'id');
    }

    /**
     * Get candidate with employeeId
     */
    public static function getCandidate($employeeId)
    {
        return self::select(['id'])->where('employee_id', '=', $employeeId)->whereNull('deleted_at')->first();
    }

    /**
     * get list
     *
     * @return objects
     */
    public function getList($order, $dir, $empId = null, $teamIds = null, $dataFilter, $candidateList = true)
    {
        $collection = self::leftJoin('requests', 'requests.id', '=', 'candidates.request_id')
                        ->leftJoin('teams', 'teams.id', '=', 'candidates.team_id')
                        ->leftJoin('candidate_programming', 'candidates.id', '=', 'candidate_programming.candidate_id')
                        ->leftJoin('candidate_pos', 'candidates.id', '=', 'candidate_pos.candidate_id')
                        ->leftJoin('candidate_request', 'candidates.id', '=', 'candidate_request.candidate_id')
                        ->leftJoin('candidate_team', 'candidates.id', '=', 'candidate_team.candidate_id')
                        ->leftJoin('employees', 'candidates.employee_id', '=', 'employees.id');

        $curEmp = Permission::getInstance()->getEmployee();
        $isMemberHr = Team::isMemberHr($curEmp->id);
        if ($empId && !$teamIds) {
            $collection->where(function ($query) use ($empId, $isMemberHr) {
                $emp = Employee::getEmpById($empId);
                if (!$emp) {
                    $emp = new Employee();
                }
                $query->where('candidates.created_by',$empId)
                      ->orWhere('candidates.found_by', $empId)
                      ->orWhere('candidates.recruiter', $emp->email)
                      ->orWhereRaw('(candidates.interviewer IS NOT NULL AND FIND_IN_SET('.$empId.',candidates.interviewer))');
                if ($isMemberHr) {
                    $query->orWhere('candidates.status', getOptions::DRAFT);
                }
            });
        }
        if ($teamIds) {
            $collection->where(function ($query) use ($empId, $teamIds, $isMemberHr) {
                $emp = Employee::getEmpById($empId);
                if (!$emp) {
                    $emp = new Employee();
                }
                $query->whereIn('candidate_team.team_id',$teamIds)
                      ->orWhere('candidates.created_by',$empId)
                      ->orWhere('candidates.found_by', $empId)
                      ->orWhere('candidates.recruiter', $emp->email )
                      ->orWhereRaw('candidates.interviewer IS NOT NULL AND FIND_IN_SET('.$empId.',candidates.interviewer)');
                if ($isMemberHr) {
                    $query->orWhere('candidates.status', getOptions::DRAFT);
                }
            });
        }
        if (isset($dataFilter['request'])) {
            $collection->where('candidate_request.request_id', $dataFilter['request']);
        }
        if (isset($dataFilter['teams.id'])) {
            $collection->whereIn('candidate_team.team_id', Team::teamChildIds($dataFilter['teams.id']));
        }
        if (isset($dataFilter['team.selected'])) {
            $collection->where('candidates.team_id', $dataFilter['team.selected']);
        }
        if (isset($dataFilter['candidates.position'])) {
            $collection->where('candidate_pos.position_apply', $dataFilter['candidates.position']);
        }
        if (isset($dataFilter['position.selected'])) {
            $collection->where('candidates.position_apply', $dataFilter['position.selected']);
        }
        if (isset($dataFilter['candidates.recruiter'])) {
            $collection->where('candidates.recruiter', $dataFilter['candidates.recruiter']);
        }
        if (isset($dataFilter['candidates.brse'])) {
            $brseFilter = $dataFilter['candidates.brse'];
            $collection->where(function ($query) use ($brseFilter) {
                $query->where('brse_emp_intro', 'LIKE', $brseFilter.'%')
                      ->orWhere('brse_outside_intro', 'LIKE', $brseFilter.'%')
                      ->orWhere('brse_emp', 'LIKE', $brseFilter.'%');
            });
        }
        if (isset($dataFilter['candidate_programming.programming_id'])) {
            $collection->where('candidate_programming.programming_id', $dataFilter['candidate_programming.programming_id']);
        }

        // Filter by candidate status
        if (isset($dataFilter['candidates.status'])) {
            self::filterStatus($collection, $dataFilter['candidates.status']);
        }

        if (isset($dataFilter['candidates.type_candidate'])) {
            $collection->where('candidates.type_candidate', $dataFilter['candidates.type_candidate']);
        }
        if ($order && $dir) {
            $collection->orderBy($order,$dir);
        }
        $collection->groupBy('candidates.id');
        $concat = self::CONCAT;
        $groupConcat = self::GROUP_CONCAT;
        $collection->select('candidates.*', 'employees.working_type',
                    'teams.name as team_selected',
                    DB::raw("(SELECT GROUP_CONCAT(concat( name ) SEPARATOR ', ') 
                                FROM programming_languages 
                                        inner join candidate_programming on programming_languages.id = candidate_programming.programming_id 
                                        where candidate_programming.candidate_id = candidates.id
                                ) AS programs_name"),
                    DB::raw("(SELECT GROUP_CONCAT(concat( requests.title, '$concat', requests.id ) SEPARATOR '$groupConcat') 
                                FROM requests 
                                        inner join candidate_request on requests.id = candidate_request.request_id 
                                        where candidate_request.candidate_id = candidates.id
                                ) AS requests"),
                    DB::raw("(SELECT GROUP_CONCAT(concat( name ) SEPARATOR ', ') 
                                FROM teams 
                                        inner join candidate_team on teams.id = candidate_team.team_id 
                                        where candidate_team.candidate_id = candidates.id
                                ) AS team_name"),
                    DB::raw("(SELECT GROUP_CONCAT(concat( position_apply ) SEPARATOR ',') 
                                FROM candidate_pos 
                                where candidate_id = candidates.id
                                ) AS positions")
                );
        if (!$candidateList) {
            $collection->whereRaw("candidates.id IN (SELECT MAX(id) FROM candidates WHERE deleted_at IS NULL GROUP BY email)");
        }
        return $collection;
    }

    /**
     * filter candidate status
     * @param collection $collection
     * @param integer $statuses
     * @param string $table
     */
    public static function filterStatus(&$collection, $statuses, $table = 'candidates')
    {
        $statuses = !is_array($statuses) ? [$statuses] : $statuses;
        //where in array like or where each element
        $collection->where(function ($query) use ($statuses, $table) {
            foreach ($statuses as $status) {
                $query->orWhere(function ($subQuery) use ($status, $table) {
                    if (in_array($status, [
                        getOptions::FAIL_CONTACT,
                        getOptions::FAIL_TEST,
                        getOptions::FAIL_INTERVIEW,
                        getOptions::FAIL_OFFER
                    ])) {
                        $subQuery->where($table.'.status', getOptions::FAIL);
                        switch ($status) {
                            case getOptions::FAIL_CONTACT:
                                $subQuery->where($table.'.contact_result', getOptions::RESULT_FAIL);
                                break;
                            case getOptions::FAIL_TEST:
                                $subQuery->where($table.'.test_result', getOptions::RESULT_FAIL);
                                break;
                            case getOptions::FAIL_INTERVIEW:
                                $subQuery->where($table.'.interview_result', getOptions::RESULT_FAIL);
                                break;
                            case getOptions::FAIL_OFFER:
                                $subQuery->where($table.'.offer_result', getOptions::RESULT_FAIL);
                                break;
                            default:
                                break;
                        }
                    } elseif ($status == getOptions::FAIL) {
                        $subQuery->whereIn($table.'.status', [getOptions::FAIL, getOptions::FAIL_CDD]);
                    } else {
                        $subQuery->where($table.'.status', $status);
                    }
                });
            }
        });
    }

    /*
     * insert or update
     * @param array
     */
    public function insertOrUpdate($input)
    {
        DB::beginTransaction();
        try {
            if (isset($input['candidate_id']) && $input['candidate_id']) {
                $candidate = self::find($input['candidate_id']);
            } else {
                $candidate = new Candidate();
            }

            $candidate->type_candidate = isset($candidate->type_candidate) ? $candidate->type_candidate : self::TYPE_FROM_INTRANET;
            if (isset($input['interviewer'])) {
                $input['interviewer'] = implode(',', $input['interviewer']);
            }
            if (isset($input['channel_id']) && $input['channel_id']) {
                $channel = Channels::find($input['channel_id']);
                if (!$channel) {
                    throw new Exception(trans('resource::message.Create candidate error'));
                }
                $channelFeesTbl = ChannelFee::getTableName();
                if (!$channel->is_presenter) {
                    $input['presenter_id'] = null;
                }

                $input['cost'] = $input['cost'] && $input['cost'] >= 0 ? str_replace(Channels::PRICE, '', $input['cost']) : 0;
                $input['cost'] = str_replace(' ', '', $input['cost']);

                if (isset($candidate->start_working_date)) {
                    $channelFee = ChannelFee::where($channelFeesTbl . '.channel_id', $input['channel_id'])
                        ->whereDate($channelFeesTbl . '.start_date', '<=', date('Y-m-d', strtotime($candidate->start_working_date)))
                        ->whereDate($channelFeesTbl . '.end_date', '>=', date('Y-m-d', strtotime($candidate->start_working_date)))
                        ->first();

                    if ($channel->type == Channels::COST_FIXED) {
                        // Kênh tuyển dụng có chi phí cố định
                        if ($input['channel_id'] != $candidate->channel_id) {
                            $oldChannel = Channels::find($candidate->channel_id);
                            if (!$oldChannel) {
                                throw new Exception(trans('resource::message.Create candidate error'));
                            }

                            if ($oldChannel->type == Channels::COST_CHANGE) {
                                $oldChannel->update([
                                    'cost' => $oldChannel->cost - $input['cost']
                                ]);
                            }
                        }
                        $input['cost'] = 0;
                    } else {
                        // Kênh tuyển dụng có chi phí thay đổi
                        if ($channelFee) {
                            $newCost = $channelFee->cost - $candidate->cost + intval($input['cost']);

                            if ($newCost < 0) {
                                throw new Exception(trans('resource::message.Create candidate error'));
                            }

                            $channelFee->update(['cost' => $newCost]);
                        }

                        if ($input['channel_id'] != $candidate->channel_id) {
                            $oldChannel = Channels::find($candidate->channel_id);

                            if ($oldChannel->type == Channels::COST_CHANGE) {
                                $oldChannel->update([
                                    'cost' => (int)$oldChannel->cost - (int)$candidate->cost
                                ]);
                            }
                        }
                    }

                    // Nếu thay đổi chi phí hoặc kênh tuyển dụng thì lưu lịch sử
                    if ($input['cost'] != $candidate->cost || $input['channel_id'] != $candidate->channel_id) {
                        ChannelCostLog::insert([
                            'candidate_id' => $candidate->id,
                            'channel_id' => $input['channel_id'],
                            'cost' => $input['cost'],
                            'working_date' => date('Y-m-d', strtotime($candidate->start_working_date)),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                } else {
                    // ứng viên chưa đi làm thì ko ghi vào bảng chi phí tuyển dụng
                }
            }
            $arrayPassValues = [getOptions::RESULT_PASS, getOptions::RESULT_WORKING];
            if (isset($input['offer_result']) && (!in_array($input['offer_result'], $arrayPassValues) || !isset($input['check_request']))) {
                $input['request_id'] = null;
            }
            if (isset($input['offer_result']) && !in_array($input['offer_result'], $arrayPassValues)) {
                $input['programming_language_id'] = null;
            }
            if (isset($input['offer_result']) && !in_array($input['offer_result'], $arrayPassValues)) {
                $input['team_id'] = null;
            }
            if (isset($input['offer_result']) && !in_array($input['offer_result'], $arrayPassValues)) {
                $input['position_apply'] = null;
            }
            if (isset($input['test_mark'])) {
                $input['test_gmat_point'] = $input['test_mark'];
            }
             //check status if is working
            $statusEmpUpdateable = getOptions::statusEmpUpdateable();
            unset($statusEmpUpdateable[array_search(getOptions::END, $statusEmpUpdateable)]);
            if (isset($input['status']) && !in_array($input['status'], $statusEmpUpdateable)
                    && in_array($candidate->status, $statusEmpUpdateable)) {
                unset($input['status']);
            }
            //is old member
            if (!isset($input['is_old_employee'])) {
                $input['is_old_employee'] = null;
            }
            // change status of candidate
            if ($candidate->id && isset($input['status']) && (int)$candidate->status !== (int)$input['status']) {
                $candidate->status_update_date = Carbon::now()->toDateString();
            }

            $candidate->fill($input);
            if ($candidate->request_id == 0) {
                $candidate->request_id = null;
            }
            if ($candidate->programming_language_id == 0) {
                $candidate->programming_language_id = null;
            }
            if ($candidate->found_by == 0) {
                $candidate->found_by = null;
            }
            if (empty($candidate->test_plan)) {
                $candidate->test_plan = null;
            }
            if (empty($candidate->test_date)) {
                $candidate->test_date = null;
            }
            if (empty($candidate->interview_plan)) {
                $candidate->interview_plan = null;
            }
            if (empty($candidate->interview_date)) {
                $candidate->interview_date = null;
            }
            if (empty($candidate->offer_date)) {
                $candidate->offer_date = null;
            }
            if (empty($candidate->offer_feedback_date)) {
                $candidate->offer_feedback_date = null;
            }
            if (empty($candidate->start_working_date)) {
                $candidate->start_working_date = null;
            }
            if (empty($candidate->trial_work_start_date)) {
                $candidate->trial_work_start_date = null;
            }
            if (empty($candidate->trial_work_end_date)) {
                $candidate->trial_work_end_date = null;
            }
            if (isset($input['cost'])) {
                $candidate->cost = $input['cost'];
            }

            $candidate->save();
            if (isset($input['candidate_id']) && $input['candidate_id'] && !isset($input['detail'])) {
                //delete old langs
                $langOld = self::getAllLangOfCandidate($candidate);
                $candidate->candidateLang()->detach($langOld);
                //delete old programming langs
                $proOld = self::getAllProgramOfCandidate($candidate);
                $candidate->candidateProgramming()->detach($proOld);
                //delete old request
                $requestOld = self::getAllRequestOfCandidate($candidate);
                $candidate->candidateRequest()->detach($requestOld);
                //delete old team
                $teamOld = self::getAllTeamOfCandidate($candidate);
                $candidate->candidateTeam()->detach($teamOld);
            }
            if (isset($input['detail']) && isset($input['tab_interview'])) {
                //delete old request
                $requestOld = self::getAllRequestOfCandidate($candidate);
                $candidate->candidateRequest()->detach($requestOld);
            }
            if (isset($input['languages'])) {
                CandidateLanguages::saveData($input['languages'], $candidate->id);
            }
            if (isset($input['programs']) && isset($input['inputYear'])) {
                $arrayProgramLanguage = [];
                foreach ($input['programs'] as $key => $value) {
                    $input['inputYear'][$key] = str_replace(',', '.', $input['inputYear'][$key]);
                    $arrayProgramLanguage[$value] = [
                        'exp_year'=> $input['inputYear'][$key],
                        'programming_id'=> $value,
                    ];
                }
                $candidate->candidateProgramming()->sync($arrayProgramLanguage);
            }
            if (isset($input['requests'])) {
                $candidate->candidateRequest()->attach($input['requests']);
            }
            if (isset($input['teams'])) {
                $candidate->candidateTeam()->attach($input['teams']);
            }
            if (isset($input['positions'])) {
                self::insertPostions($candidate, $input['positions']);
            }

            //After save history then clear note
            $temp['note'] = '';
            $candidate->fill($temp);
            $candidate->save();
            //update employee contact
            $employee = $candidate->employee;
            if ($employee) {
                $employeeContact = $employee->getItemRelate('contact');
                $employeeContact->setData([
                    'employee_id' => $employee->id,
                    'mobile_phone' => $candidate->mobile,
                    'skype' => $candidate->skype
                ]);
                $employeeContact->save();
                CacheHelper::forget(Employee::KEY_CACHE, $employee->id);
            }
            DB::commit();
            CacheHelper::forget(self::KEY_CACHE);
            return $candidate;
        } catch (QueryException $ex) {
            DB::rollback();
            throw $ex;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public function setEndWorkingDateAttribute($value)
    {
        if (!$value || $value == '0000-00-00') {
            $value = null;
        }
        $this->attributes['end_working_date'] = $value;
    }

    public function isWorking($status = null)
    {
        if (!$status) {
            $status = $this->status;
        }
        $statusEmpUpdateable = getOptions::statusEmpUpdateable();
        unset($statusEmpUpdateable[array_search(getOptions::END, $statusEmpUpdateable)]);
        return in_array($status, $statusEmpUpdateable);
    }

    public function isWorkingOrEndOrLeave()
    {
        return in_array($this->status, getOptions::statusWorkingOrEndOrLeave());
    }

    public static function getPrograms($candidate)
    {
        return $candidate->candidateProgramming;
    }

    public static function getLangs($candidate)
    {
        return $candidate->candidateLang;
    }

    /**
     * Get all langs of candidate
     * @parram Candidate $candidate
     * @return array
     */
    public static function getAllLangOfCandidate($candidate)
    {
        $langs = array();
        foreach ($candidate->candidateLang as $lang) {
            array_push($langs, $lang->id);
        }
        return $langs;
    }

    public static function changeStatusToFail($request) {
        if (isset($request->candidateId)) {
            $candidate = self::find($request->candidateId);
            $candidate->offer_result = $request->statusFail;
            $candidate->start_working_date = null;
            $candidate->trial_work_start_date = null;
            $candidate->trial_work_end_date = null;
            $candidate->official_date = null;
            $candidate->save();
            return $candidate;
        }
        return false;
    }

    /**
     * Get all programming languages of candidate
     * @parram Candidate $candidate
     * @return array
     */
    public static function getAllProgramOfCandidate($candidate)
    {
        $pros = array();
        foreach ($candidate->candidateProgramming as $pro) {
            array_push($pros, $pro->id);
        }
        return $pros;
    }

    /**
     * Get all requests of candidate
     *
     * @parram Candidate $candidate
     * @return array
     */
    public static function getAllRequestOfCandidate($candidate)
    {
        $requests = array();
         $cddRequests = $candidate->candidateRequest;
        foreach ($cddRequests  as $rq) {
            array_push($requests, $rq->id . "");
        }
        return $requests;
    }

    /**
     * Get all teams of candidate
     *
     * @parram Candidate $candidate
     * @return array
     */
    public static function getAllTeamOfCandidate($candidate)
    {
        $teams = array();
        foreach ($candidate->candidateTeam as $team) {
            array_push($teams, $team->id);
        }
        return $teams;
    }

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    public static function checkCountCandidateOfRequest($requestId)
    {
        $count = self::where([['status', getOptions::END], ['request_id', $requestId]])
                    ->select('id')->count();
        $rq = ResourceRequest::find($requestId);
        if ((int)$count < (int)$rq->number_resource) {
            return false;
        }
        return true;
    }

    public function closeCandidates($requestId)
    {
        DB::beginTransaction();
        try {
            self::where([
                    ['request_id', '=', $requestId],
                    ['status', '<>', getOptions::END]
                ])
                ->update(['status' => getOptions::FAIL]);
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public function getCountCandidateOfRequest($requestId, $status = null)
    {
        $count = CandidateRequest::where('request_id', $requestId);
        if ($status) {
            $count->where('status', $status);
        }

        return $count->count();
    }

    public static function checkExist($email, $id)
    {
        $arrayId = [];
        //Find child
        if ($id) {
            $candidate = Candidate::find($id);
            if ($candidate->parent_id) {
                $arrayId[] = $candidate->parent_id;
                $allRecordOfCandidate = self::where('parent_id', $candidate->parent_id)->select('id')->get();
            } else {
                $arrayId[] = $candidate->id;
                $allRecordOfCandidate = self::where('parent_id', $candidate->id)->select('id')->get();
            }
            if ($allRecordOfCandidate) {
                foreach ($allRecordOfCandidate as $record) {
                    $arrayId[] = $record->id;
                }
            }
        }

        return self::where('email', $email)
                ->whereNotIn('id', $arrayId)
                ->count();
    }

    public static function getBrse($brseEmp, $brseEmpIntro, $brseOutsideIntro)
    {
        if (!empty ($brseEmp)) {
            return $brseEmp;
        } elseif (!empty ($brseEmpIntro)) {
            return $brseEmpIntro;
        } else {
            return $brseOutsideIntro;
        }
    }

    /**
     * get list email candidate check email
     * @return true or false
     */
    public static function getListEmailCandidate($check)
    {
        $list = self::where('email',trim($check))->first();
        if ($list) {
            return false;
        }
        return true;
    }

    /**
     *fortmat date time to string
     */
    public static function fortmatDateTime($date)
    {
        $date = explode(',',$date);
        $date = trim($date[0]);
        if (preg_match(self::FORMAT_DATE, $date)){
                if(is_string($date)) {
                    $time = DateTime::createFromFormat('d/m/Y', $date)->format('Y-m-d');
                if ($time) {
                    return $time;
                }
            }
        }
        return "";
    }

    /**
     *insert data to database
     */
    public static function insertCv($value)
    {
        DB::beginTransaction();
        try {
            $check = self::getListEmailCandidate(self::checkEmail($value['email']));
            $insertCandidate = new Candidate;
            $a = SupportConfig::get('general.format_excel');
            $columNomal = SupportConfig::get('general.format_excel.nomal');
            $columDate = SupportConfig::get('general.format_excel.date');
            foreach ($columNomal as $keyColumNomal => $valueColumNomal) {
                if (isset($value[$keyColumNomal]) && $value[$keyColumNomal]) {
                    $insertCandidate->$valueColumNomal = trim($value[$keyColumNomal]);
                }
            }
            foreach ($columDate as $keyColumDate => $valueComlumDate) {
                if (isset($value[$keyColumDate]) && $value[$keyColumDate]) {
                    $insertCandidate->$valueComlumDate = self::fortmatDateTime($value[$keyColumDate]);
                }
            }
            if (isset($value['mobile']) && $value['mobile']) {
               $insertCandidate->mobile = preg_replace("/(\s|\-|\.)/", '', $value['mobile']);
            }
            if (isset($value['email']) && $value['email']) {
                $insertCandidate->email = self::checkEmail($value['email']);
            }
            // presenter
            if (isset($value['nguoi_gioi_thieu']) && !empty($value['nguoi_gioi_thieu'])) {
                 if (Employee::getIdByNickName($value['nguoi_gioi_thieu'])) {
                        $insertCandidate->presenter_id = Employee::getIdByNickName($value['nguoi_gioi_thieu']);
                    } else {
                        $insertCandidate->presenter_text = $value['nguoi_gioi_thieu'];
                    }
                if (isset($value['chanel']) && $value['chanel']) {
                    if (Channels::getIdChanel($value['chanel']) != null
                        && Channels::getIdChanel($value['chanel'])['is_presenter'] == Channels::PRESENTER_YES) {
                        $insertCandidate->channel_id = Channels::getIdChanel($value['chanel'])['id'];
                    }
                }
            } else {
                if (isset($value['chanel']) && $value['chanel']
                    && Channels::getIdChanel($value['chanel'])['is_presenter'] == Channels::PRESENTER_NO) {
                    $insertCandidate->channel_id = Channels::getIdChanel($value['chanel'])['id'];
                } else {
                    $insertCandidate->channel_id = 11; // ung vien tu do
                }
            }
            // insert result test interview offer
            if (isset($value['test_date']) && $value['test_date']) {
                $insertCandidate->contact_result = getOptions::RESULT_PASS;
            }
            if (isset($value['interview_date']) && $value['interview_date']) {
                $insertCandidate->test_result = getOptions::RESULT_PASS;
                $insertCandidate->contact_result = getOptions::RESULT_PASS;
            } else {
                if (isset($value['test_result']) && !empty($value['test_result'])) {
                    if (trim(strtoupper($value['test_result'])) == self::PASS) {
                        $insertCandidate->test_result = getOptions::RESULT_PASS;
                    }
                    if (trim(strtoupper($value['test_result'])) == self::FAIL) {
                        $insertCandidate->test_result = getOptions::RESULT_FAIL;
                    }
                }
            }
            if (isset($value['interview_result']) && !empty($value['interview_result'])) {
                if (trim(strtoupper($value['interview_result'])) == self::PASS) {
                    $insertCandidate->interview_result = getOptions::RESULT_PASS;
                }
                if (trim(strtoupper($value['interview_result'])) == self::FAIL){
                    $insertCandidate->interview_result = getOptions::RESULT_FAIL;
                }
            }
            if (isset($value['result']) && !empty($value['result'])) {
                if (trim(strtoupper($value['result'])) == self::PASS) {
                    $insertCandidate->offer_result = getOptions::RESULT_PASS;
                }
                if (trim(strtoupper($value['result'])) == self::FAIL){
                    $insertCandidate->offer_result = getOptions::RESULT_FAIL;
                }
            }
            $insertCandidate->status = self::checkStatus($insertCandidate->interview_result ,$insertCandidate->offer_result,
                $insertCandidate->interview_date,$insertCandidate->contact_result,$insertCandidate->test_result);
            //insert mark candidate
            if (isset($value['test_result']) && $value['test_result']) {
                $insertCandidate->test_mark = self::insertTestMark($value['test_result'])['markGmat'];
                $insertCandidate->test_mark_specialize = self::insertTestMark($value['test_result'])['markSpecial'];
            }
            if (isset($value['interviewer']) && $value['interviewer']) {
                $insertCandidate->interviewer       = Employee::getIdByNickName($value['interviewer']);
            }
            if (isset($value['recruiter']) && $value['recruiter']) {
                $insertCandidate->recruiter         = strtolower($value['recruiter']).self::EMAIL_COMPANY;
            }
            if (isset($value['skype']) && $value['skype']) {
                $insertCandidate->skype         = $value['skype'];
            }
            if (isset($value['gender']) && $value['gender']) {
                $insertCandidate->gender         = $value['gender'];
            }
            $insertCandidate->received_cv_date = date('Y-m-d');

            if ($check && isset($value['full_name']) && $value['full_name']) {
                if ($insertCandidate->save()){
                    if (isset($value['language']) && $value['language']){
                        $arrayLanguage = array_map("trim", explode(",",$value['language'] ));
                        $candidateLang = Languages::getIdByName($arrayLanguage);
                        if($candidateLang) {
                            foreach ($candidateLang as $keyId) {
                                $arrayCandidateLang[] = [
                                    'candidate_id' => $insertCandidate->id,
                                    'lang_id'      => $keyId,
                                ];
                            }
                            CandidateLanguages::insert($arrayCandidateLang);
                        }
                    }
                    if (isset($value['program_language']) && $value['program_language']){
                        $arrayProgramLang = array_map("trim",explode(",",$value['program_language']));
                        $candidateProgramLang = Programs::getIdByName($arrayProgramLang);
                        if ($candidateProgramLang) {
                            foreach ($candidateProgramLang as $valuelang) {
                                $arrayCandidateProgramLang[] = [
                                    'candidate_id' => $insertCandidate->id,
                                    'programming_id' => $valuelang,
                                ];
                            }
                            CandidateProgramming::insert($arrayCandidateProgramLang);
                        }
                    }
                    if (isset($value['position_apply']) && $value['position_apply']) {
                        $positionsInsert = [];
                        foreach (getOptions::getInstance()->getRoles() as $keyGetRoles1 => $valueGetRoles1) {
                            if (strtoupper(trim($value['position_apply'])) == strtoupper($valueGetRoles1)) {
                                $positionsInsert = [$keyGetRoles1];
                                CandidatePosition::insertPostions($insertCandidate->id, $positionsInsert);
                            }
                        }
                        if (!count($positionsInsert)) {
                            $position = explode(",", $value['position_apply']);
                            foreach ($position as $keyPosition) {
                                foreach (getOptions::getInstance()->getRoles() as $keyGetRoles => $valueGetRoles) {
                                    if (strtoupper($keyPosition) == strtoupper($valueGetRoles)) {
                                        $positionsInsert[] = $keyGetRoles;
                                    }
                                }
                            }
                            if (count($positionsInsert)) {
                                CandidatePosition::insertPostions($insertCandidate->id, $positionsInsert);
                            }
                        }
                    }
                }
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            \Log::info($ex);
        }
    }

    /**
     * Get request by id
     * @param type $id
     * @return ResourceRequest
     */
    public static function getCandidateProgrammingById($id)
    {
        $concat = self::CONCAT;
        $groupConcat = self::GROUP_CONCAT;
        return self::where('candidates.id', $id)
                ->select(
                    DB::raw("(SELECT GROUP_CONCAT(concat( programming_languages.name ) SEPARATOR ', ')
                                FROM programming_languages
                                        inner join candidate_programming on programming_languages.id = candidate_programming.programming_id
                                        where candidate_programming.candidate_id = candidates.id
                                ) AS programs_name")
                )
                ->first();
    }

    /**
     * Get exp year by candidate_id, programming_id
     * @param type $id, $program
     * @return ResourceRequest
     */
    public static function getExpYear($id, $program)
    {
        $tableCandidateProgramming = CandidateProgramming::getTableName();
        $year = CandidateProgramming::select('exp_year')->where('candidate_id', $id)->where('programming_id', $program)->first();
        return floatval($year->exp_year);
    }

    /**
     * Get request by id, store cache
     * @param type $id
     * @return ResourceRequest
     */
    public static function getCandidateById($id)
    {
        $concat = self::CONCAT;
        $groupConcat = self::GROUP_CONCAT;
        return self::where('candidates.id', $id)
                ->select(
                    "candidates.*",
                    DB::raw("(SELECT GROUP_CONCAT(IF(candidate_lang.lang_level_id is not null, concat( languages.name, ' - ', language_level.name ), languages.name) SEPARATOR ', ') 
                                FROM languages 
                                        inner join candidate_lang on languages.id = candidate_lang.lang_id 
                                        left join language_level on language_level.id = candidate_lang.lang_level_id
                                        where candidate_lang.candidate_id = candidates.id
                                ) AS lang_name"),
                    DB::raw("(SELECT GROUP_CONCAT(concat( programming_languages.name,' - ', candidate_programming.exp_year) SEPARATOR ' , ')
                                FROM programming_languages
                                        inner join candidate_programming on programming_languages.id = candidate_programming.programming_id
                                        where candidate_programming.candidate_id = candidates.id
                                ) AS programs_name"),
                    DB::raw("(SELECT GROUP_CONCAT(concat( requests.title, '$concat', requests.id ) SEPARATOR '$groupConcat') 
                                FROM requests 
                                        inner join candidate_request on requests.id = candidate_request.request_id 
                                        where candidate_request.candidate_id = candidates.id
                                ) AS requests"),
                    DB::raw("(SELECT GROUP_CONCAT(concat( name ) SEPARATOR ', ') 
                                FROM teams 
                                        inner join candidate_team on teams.id = candidate_team.team_id 
                                        where candidate_team.candidate_id = candidates.id
                                ) AS team_name"),
                    DB::raw("(SELECT GROUP_CONCAT(concat( position_apply ) SEPARATOR ',') 
                                FROM candidate_pos 
                                where candidate_id = candidates.id
                                ) AS positions")
                )
                ->first();
    }

    /**
     * get count candidate pass by position of request
     * @param int $requestId
     * @param int $position
     * @return int
     */
    public static function countCandidatePass($requestId, $position, $teamId)
    {
        return self::where('request_id', $requestId)
                    ->where('position_apply', $position)
                    ->where('team_id', $teamId)
                    ->whereIn('status', [getOptions::END, getOptions::WORKING])
                    ->count();
    }

    /**
     * Check if count candidate pass > number of request (same team and position apply)
     * @param ResourceRequest $request
     * @return boolean
     */
    public static function checkOverload($request)
    {
        $requestTeam = RequestTeam::getRequestTeam($request->id);
        if ($requestTeam && count($requestTeam)) {
            foreach ($requestTeam as $rt) {
                $countPass = self::countCandidatePass($request->id, $rt->position_apply, $rt->team_id);
                if ($countPass > $rt->number_resource) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function checkFull($request)
    {
        $requestTeam = RequestTeam::getRequestTeam($request->id);
        if ($requestTeam && count($requestTeam)) {
            foreach ($requestTeam as $rt) {
                $countPass = static::countCandidatePass($request->id, $rt->position_apply, $rt->team_id);
                if ($countPass < $rt->number_resource) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Option type candidate list
     * @return array
     */
    public static function getTypeOptions()
    {
        return [
            ['id' => self::FRESHER, 'name' => Lang::get('resource::view.Fresher')],
            ['id' => self::JUNIOR, 'name' => Lang::get('resource::view.Junior')],
            ['id' => self::MIDDLE, 'name' => Lang::get('resource::view.Middle')],
            ['id' => self::SENIOR, 'name' => Lang::get('resource::view.Senior')],
        ];
    }

    public static function listTypes()
    {
        return getOptions::getInstance()->getDevTypeOptions();
    }

    /**
     * Get type candidate by key
     * @param int $key
     * @return string
     */
    public static function getType($key)
    {
        switch ($key) {
            case self::FRESHER: return Lang::get('resource::view.Fresher');
            case self::JUNIOR: return Lang::get('resource::view.Junior');
            case self::MIDDLE: return Lang::get('resource::view.Middle');
            case self::SENIOR: return Lang::get('resource::view.Senior');
            default: return '';
        }
    }

    /**
     * get team that belongs to
     * @return type
     */
    public function team()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Team', 'team_id', 'id');
    }

    /**
     * get channel that belongs to
     * @return type
     */
    public function channel()
    {
        return $this->belongsTo('\Rikkei\Resource\Model\Channels', 'channel_id', 'id');
    }

    /**
     *Check format file excel
     *@param array
     *@return true false
     */
    public static function checkFormatEcxel($data)
    {return true;
        if (isset($data[0])) {
            $getNameColum = array_keys($data[0]->toArray());
            $columFormat = SupportConfig::get('general.format_excel.format');
            $checkFormat = false;
            foreach ($columFormat as $colum) {
                if (in_array($colum, $getNameColum, true)) {
                    $checkFormat = true;
                } else {
                    $checkFormat = false;
                    break;
                }
            }
        } else {
            $checkFormat = false;
        }
        return $checkFormat;
    }

    /**
     * check status candidate
     * input result interview offer date test
     */
    public static function checkStatus($valueInter, $valueOffer, $valueDate, $valueContact, $valueTest)
    {
        if (($valueInter && $valueOffer) || ($valueInter && !$valueOffer)) {
            if ($valueInter == 1 && !$valueOffer)
                return getOptions::OFFERING;
            if ($valueInter == 1 && $valueOffer == 1)
                return getOptions::END;
            if ($valueOffer == 2 || $valueInter == 2)
                return getOptions::FAIL;
        } else {
            if ($valueTest == 1) return getOptions::INTERVIEWING;
            if ($valueContact) return getOptions::ENTRY_TEST;
            if ($valueDate) {
                return getOptions::ENTRY_TEST;
            } else {
                return getOptions::CONTACTING;
            }
        }
        return getOptions::CONTACTING;
    }

    /**
     * insert test mark to database
     */
    public static function insertTestMark($valueTestResult)
    {
        $typeTest = Type::getListTypeName()->toArray();
        $array = preg_split("/\\r\\n|\\r|\\n|\,/", trim($valueTestResult));
        $markSpecial = null;
        $markGmat = null;
        foreach ($array as $keyArray) {
            $arrayMark = explode(':', $keyArray);
            foreach ($arrayMark as $keyMark) {
                if (trim(strtoupper($keyMark)) == self::GMAT) {
                        $markGmat = preg_replace('/.*.:/', '', $keyArray);
                        break;
                }
                foreach ($typeTest as $keyTypeTest) {
                    if (trim(strtoupper($keyMark)) == strtoupper($keyTypeTest['name'])) {
                        $markSpecial = preg_replace('/.*.:/', '', $keyArray);
                        break;
                    }
                }
            }
        }
        return [
                'markGmat' => $markGmat,
                'markSpecial' => $markSpecial
        ];
    }

    public static function checkEmail($value)
    {
        $email = preg_replace("/(\/|\-)/", ' ', $value);
        $arrayEmail = explode(' ', $email);
        foreach ($arrayEmail as $valueEmail) {
            if (filter_var($valueEmail, FILTER_VALIDATE_EMAIL)) {
                return $valueEmail;
            }
        }
        return "";
    }

    /**
     * get position apply name as attribute
     * @return string
     */
    public function getPositionApplyNameAttribute()
    {
        $positions = getOptions::getInstance()->getRoles();
        if (isset($positions[$this->position_apply])) {
            return $positions[$this->position_apply];
        }
        return null;
    }

    /**
     * get channel name as attribute
     * @param type $dataRequest
     * @param type $candidate
     * @return type
     */
    public function getChannelNameAttribute()
    {
        $channel = $this->channel;
        if ($channel) {
            return $channel->name;
        }
        return null;
    }

    /**
     * get test option type
     * @return type
     */
    public function testType()
    {
        return $this->belongsTo('\Rikkei\Test\Models\Type', 'test_option_type_id', 'id');
    }

    /**
     * get test type name as attributes
     * @return type
     */
    public function listTestTypes()
    {
        $testTypeIds = $this->test_option_type_ids;
        if ($testTypeIds) {
            $langCode = Session::get('locale');
            $allLang = CoreLang::changeOrder($langCode);
            $result = Type::whereIn('ntest_types.id', $testTypeIds)
                    ->select('ntest_types.id', 'ntest_types.code');
            $addSelect = [];
            foreach ($allLang as $langKey => $langText) {
                $result->leftJoin("ntest_types_meta as nt_{$langKey}", function($join) use ($langKey) {
                    $join->on("nt_{$langKey}.type_id", '=', 'ntest_types.id');
                    $join->where("nt_{$langKey}.lang_code", '=', "{$langKey}");
                });
                $addSelect[] = "nt_{$langKey}.name";
            }
            $strAddSelect = implode(',', $addSelect);
            $result->addSelect(DB::raw("coalesce({$strAddSelect}) as name"))
                ->groupBy('ntest_types.id');
            return $result->get();
        }
        return null;
    }

    /**
     * get option type id as array
     * @param type $value
     * @return type
     */
    public function getTestOptionTypeIdsAttribute($value)
    {
        if (!$value) {
            return [];
        }
        return unserialize($value);
    }

    /**
     * set option type id as string
     * @param type $value
     * @param type $candidate
     * @return type
     */
    public function setTestOptionTypeIdsAttribute($value)
    {
        $this->attributes['test_option_type_ids'] = $value ? serialize($value) : null;
    }

    /**
     * get employee that belongs to
     */
    public function employee()
    {
        return $this->belongsTo('\Rikkei\Team\Model\Employee', 'employee_id', 'id')
                ->withTrashed();
    }

    /**
     * create employee from cadidate
     * @param type $dataRequest
     * @param type $candidate
     * @return type
     */
    public static function createOrUpdateEmployee($dataRequest, $candidate, $data)
    {
        //checked old employee
        $oldEmployeeId = isset($data['old_employee_id']) ? $data['old_employee_id'] : null;
        //get data
        $isWorkingTypeExternel = in_array($candidate->working_type, getOptions::workingTypeExternal());
        $empOfficialDate = null;
        if (in_array($candidate->working_type, [
            getOptions::WORKING_UNLIMIT,
            getOptions::WORKING_OFFICIAL,
        ])) {
            $empOfficialDate = $candidate->start_working_date;
        }
        if (in_array($candidate->working_type, [getOptions::WORKING_PROBATION])) {
            $empOfficialDate = $candidate->official_date;
        }
        $dataEmployee = $dataRequest;
        $dataEmployee['name'] = $candidate->fullname;
        $dataEmployee['join_date'] = $candidate->start_working_date;
        $dataEmployee['offcial_date'] = $empOfficialDate;
        $dataEmployee['trial_date'] = $candidate->trial_work_start_date && !$isWorkingTypeExternel
                ? Carbon::parse($candidate->trial_work_start_date)->format('Y-m-d') : null;
        $dataEmployee['gender'] = $candidate->gender;
        $dataEmployee['birthday'] = $candidate->birthday;
        $dataEmployee['mobile_phone'] = $candidate->mobile;
        $dataEmployee['home_town'] = $candidate->home_town;
        $dataEmployee['working_type'] = $candidate->working_type;
        $dataEmployee['contract_length'] = $candidate->contract_length;
        $dataEmployee['trial_end_date'] = $candidate->trial_work_end_date && !$isWorkingTypeExternel
                ? $candidate->trial_work_end_date : null;
        $dataEmployee['account_status'] = $candidate->status;
        $dataEmployee['created_by'] = auth()->id();
        //employee contact
        $employeeContact = isset($dataRequest['contact']) ? $dataRequest['contact'] : [];
        //get employee
        $oldEmployee = null;
        if ($oldEmployeeId) {
            $employee = Employee::find($oldEmployeeId);
            if (!$employee) {
                throw new \Exception(trans('resource::message.Item not found'), 404);
            }
            $oldEmployee = clone $employee;
            $employee->leave_date = null;
        } else {
            $employee = $candidate->employee;
        }
        // check if user is candidate
        $needLogOut = false;
        $updateEmp = false;
        if ($employee) {
            $loggingUser = auth()->user()->email;
            $emEmail = $employee->email;
            $needLogOut = ($loggingUser === $emEmail && $emEmail !== $dataRequest['email']);
            $updateEmp = true;
        }
        //not update exists
        $isSaveEmp = true;
        if (!$employee) {
            if ($candidate->status == getOptions::FAIL_CDD) {
                $isSaveEmp = false;
            }
            $employee = new Employee();
        }
        $aryEmpField = array_only($dataEmployee, $employee->getFillable());
        foreach ($aryEmpField as $key => $value) {
            $employee->{$key} = $value;
        }
        $employee->deleted_at = null;
        if ($candidate->status == getOptions::FAIL_CDD) {
            if (!$oldEmployeeId) {
                $employee->deleted_at = Carbon::now()->toDateTimeString();
            } else {
                $oldLeaveDate = Carbon::now()->toDateString();
                $oldContract = EmployeeContractHistory::where('employee_id', $oldEmployeeId)
                        ->whereNotNull('leave_date')
                        ->orderBy('leave_date', 'desc')
                        ->first();
                if ($oldContract) {
                    $oldLeaveDate = $oldContract->leave_date;
                }
                $employee->leave_date = $oldLeaveDate;
            }
            //reject request asset
            $employee->rejectRequestAsset();
        }
        if ($isSaveEmp) {
            $employee->save();
        }

        //check old employee and delete
        if ($oldEmployee) {
            self::where('employee_id', $oldEmployee->id)
                    ->where('id', '!=', $candidate->id)
                    ->update(['employee_id' => null]);
        }
        if (!$candidate->employee_id || $oldEmployee) {
            $candidate->employee_id = $employee->id;
            $candidate->save();
        }
        $oldTeamNames = $oldEmployee ? $oldEmployee->getTeamNames() : null;
        if ($candidate->team_id) {
            if ($isSaveEmp) {
                $employee->teams()->sync([$candidate->team_id => ['role_id' => Team::ROLE_MEMBER]]);
                //Save team history of employee
                $dateNow = Carbon::now()->format('Y-m-d H:i:s');
                if ($updateEmp) {
                    $currentTeam = EmployeeTeamHistory::getCurrentTeams($candidate->employee_id);
                    $arrCurTeamId = [];
                    $arrCurRoleId = [];
                    foreach ($currentTeam as $t) {
                        $arrCurTeamId[] = $t->team_id;
                        $arrCurRoleId[] = $t->position;
                    }
                    $newTeamId = $candidate->team_id;

                    foreach ($arrCurTeamId as $teamId) {
                        if ((int)$teamId !== (int)$newTeamId) {
                            $teamHistory = EmployeeTeamHistory::getCurrentByTeamEmployee($teamId, $candidate->employee_id);
                            $teamHistory->end_at = $dateNow;
                            $teamHistory->is_working = EmployeeTeamHistory::END_WORK;
                            $teamHistory->save();
                        }
                    }

                    //TH nhân viên có nhiều team và bị sửa team đang có end_at là team hiện tại 
                    $currentTeamWroking = EmployeeTeamHistory::getCurrentTeamsWorking($candidate->employee_id);
                    foreach($currentTeamWroking as $item) {
                        if (!in_array($newTeamId, $arrCurTeamId) && $item->end_at) {
                            $item->is_working = EmployeeTeamHistory::END_WORK;
                            $item->save();
                        }
                    }

                    if (!in_array($newTeamId, $arrCurTeamId)) {
                        $teamHistory = new EmployeeTeamHistory();
                        $teamHistory->team_id = $newTeamId;
                        $teamHistory->employee_id = $candidate->employee_id;
                        $teamHistory->start_at = $candidate->start_working_date;
                        $teamHistory->role_id = Team::ROLE_MEMBER;
                        $teamHistory->is_working = EmployeeTeamHistory::IS_WORKING;
                        $teamHistory->save();
                    }

                } else {
                    $empTeamHistory = new EmployeeTeamHistory;
                    $empTeamHistory->employee_id = $candidate->employee_id;
                    $empTeamHistory->team_id = $candidate->team_id;
                    $empTeamHistory->start_at = $candidate->start_working_date;
                    $empTeamHistory->role_id = Team::ROLE_MEMBER;
                    $empTeamHistory->is_working = EmployeeTeamHistory::IS_WORKING;
                    $empTeamHistory->save();
                }
                //update candiate contact
                $empContactModel = $employee->contact;
                $employeeContact['mobile_phone'] = $candidate->mobile;
                $employeeContact['skype'] = $candidate->skype;
                $employeeContact['personal_email'] = $candidate->email;
                if (!$empContactModel) {
                    $empContactModel = new EmployeeContact();
                    $employeeContact['employee_id'] = $employee->id;
                } else {
                    if (!$empContactModel->native_country && isset($employeeContact['tempo_country'])) {
                        $empContactModel->native_country = $employeeContact['tempo_country'];
                    }
                }
                $empContactModel->setData($employeeContact);
                $empContactModel->save();

                $employee->generateEmpCode($candidate->team_id, null, $candidate->working_type);

                //update employee work
                self::updateEmployeeWork($employee, [
                    'working_type' => $candidate->working_type,
                    'contract_length' => $candidate->contract_length
                ]);
            }
        }

        //check update employee contract
        if ($oldEmployee) {
            // check save contract history
            $oldEmployee->come_back = true;
            $oldEmployee->contract_type = $oldEmployee->getItemRelate('work')->contract_type;
            $oldEmployee->team_name = $oldTeamNames;
            //new
            $employee->contract_type = $candidate->working_type;
            EmployeeContractHistory::insertItem($employee, $oldEmployee);
        }

        if (!$isSaveEmp) {
            $employee = null;
        }

        return ['employee' => $employee, 'logout' => $needLogOut];
    }


    /** Insert candidate positions
     *
     * @param Candidate $candidate
     * @param array $positions
     */
    public static function insertPostions($candidate, $positions)
    {
        CandidatePosition::deleteByCandidate($candidate->id);
        CandidatePosition::insertPostions($candidate->id, $positions);
    }

    /**
     * Get requests of candidate
     *
     * @param int $candidateId
     * @return ResourceRequest id, title
     */
    public static function getRequests($candidateId)
    {
        $candidateRequestTable = CandidateRequest::getTableName();
        $requestTable = ResourceRequest::getTableName();
        return ResourceRequest::join("{$candidateRequestTable}", "{$candidateRequestTable}.request_id", "=", "{$requestTable}.id")
                ->where("{$candidateRequestTable}.candidate_id", $candidateId)
                ->select(
                    "{$requestTable}.id",
                    "{$requestTable}.title",
                    "{$requestTable}.interviewer",
                    DB::raw("(SELECT GROUP_CONCAT(concat( team_id, ',', position_apply ) SEPARATOR ';') 
                        FROM request_team 
                        WHERE request_team.request_id = {$requestTable}.id
                    ) AS team_pos")
                )
                ->get();
    }

    /**
     * get Teams of candidate
     *
     * @param int $candidateId
     * @return Team id, name
     */
    public static function getTeams($candidateId)
    {
        $candidateTeamTable = CandidateTeam::getTableName();
        $teamTable = Team::getTableName();
        return Team::join("{$candidateTeamTable}", "{$candidateTeamTable}.team_id", "=", "{$teamTable}.id")
                    ->where("{$candidateTeamTable}.candidate_id", $candidateId)
                    ->select("{$teamTable}.id", "{$teamTable}.name")
                    ->get();
    }

    /**
     * Get candidate data
     *
     * @param array $columns columns to select
     * @param array $filter filter conditions
     * @return type
     */
    public static function advanceSearch($columns, $filter, $selectCase = false)
    {
        $candidateTable = self::getTableName();
        $collection = self::select(self::selectSearch($columns, $selectCase));

        //Filter
        if ($filter && count($filter)) {
            foreach ($filter as $field => $data) {
                if (isset($data['except'])) {
                    continue;
                }
                if (isset($data['joinTable']) && isset($data['joinField']) && isset($data['joinToField'])) {
                    $collection->leftJoin($data['joinTable'], $data['joinField'], '=', $data['joinToField']);
                }
                switch ($data['compare']) {
                    case self::COMPARE_LIKE:
                        $collection->where($field, 'like', '%' . $data['value'] . '%');
                        break;
                    case self::COMPARE_IS_NULL:
                        $collection->whereNull($field);
                        break;
                    case self::COMPARE_IS_NOT_NULL:
                        $collection->whereNotNull($field);
                        break;
                    default:
                        if ($field == 'candidates.team_id' && $data['compare'] == self::COMPARE_EQUAL) {
                            $children = Team::teamChildIds(trim($data['value']));
                            $collection->whereIn(DB::raw("trim(REPLACE($field ,'\r\n' ,''))"), array_keys($children));
                        }
                        else {
                            $collection->where(DB::raw("trim(REPLACE($field ,'\r\n' ,''))"), $data['compare'], trim($data['value']));
                        }
                }
                if (isset($data['extra']) && !empty($data['extra']) && isset($data['fieldExtra'])) {
                    if ($data['compare'] != self::COMPARE_IS_NULL && $data['compare'] != self::COMPARE_IS_NOT_NULL) {
                        $collection->where($data['fieldExtra'], $data['compare'], $data['extra']);
                    }
                }
            }
        }
        $collection->whereRaw("candidates.id IN (SELECT MAX(id) FROM candidates GROUP BY email)");
        self::searchExcept($collection, $filter);
        //Join others tables
        self::joinTables($collection, $columns, $filter);
        //Permission filter
        self::permissionFilter($collection);
        $collection->groupBy("{$candidateTable}.id");
        return $collection;
    }

    /**
     * Filer list result by permission
     *
     * @param collection $collection
     */
    public static function permissionFilter($collection)
    {
        if (Permission::getInstance()->isScopeCompany()) {

        } else {
            $collection->where(function ($query) {
                $emp = Permission::getInstance()->getEmployee();
                $query->where('candidates.created_by',$emp->id)
                      ->orWhere('candidates.found_by', $emp->id)
                      ->orWhere('candidates.recruiter', $emp->email )
                      ->orWhereRaw('(candidates.interviewer IS NOT NULL AND FIND_IN_SET('.$emp->id.',candidates.interviewer))');
                if ($teamIds = Permission::getInstance()->isScopeTeam()) {
                    $query->orWhereIn('candidate_team.team_id',$teamIds);
                }
            });
        }
    }

    /**
     * add join tables with Some special cases
     * @param Candidate collection $collection
     * @param array $columns -> columns selected
     * @param array $filter
     */
    public static function joinTables($collection, $columns, $filter)
    {
        $channelTable = Channels::getTableName();
        $candidateTable = self::getTableName();
        $empTable = Employee::getTableName();
        $candidateRequestTable = CandidateRequest::getTableName();
        $requestTable = ResourceRequest::getTableName();
        $teamTable = Team::getTableName();
        $candidateTeamTable = CandidateTeam::getTableName();
        $candidatePosTable = CandidatePosition::getTableName();
        $candidateLangTable = CandidateLanguages::getTableName();
        $langTable = Languages::getTableName();
        $langLevelTable = LanguageLevel::getTableName();
        $canProTable = CandidateProgramming::getTableName();
        $proLangTable = Programs::getTableName();

        $collection->leftJoin("{$candidateTeamTable}", "{$candidateTeamTable}.candidate_id", "=", "{$candidateTable}.id");
        $collection->leftJoin("{$teamTable}", "{$candidateTeamTable}.team_id", "=", "{$teamTable}.id");

        if (in_array("{$channelTable}.name as channel_name_fix", $columns)) {
            $collection->leftJoin("{$channelTable}", "{$channelTable}.id", "=", "{$candidateTable}.channel_id");
        }
        if (in_array("{$empTable}.email as presenter_email", $columns)) {
            $collection->leftJoin("{$empTable}", "{$empTable}.id", "=", "{$candidateTable}.presenter_id");
        }
        if (in_array("request_name_search", $columns)) {
            if (!isset($filter["{$candidateRequestTable}.request_id"])) {
                $collection->leftJoin("{$candidateRequestTable}", "{$candidateRequestTable}.candidate_id", "=", "{$candidateTable}.id");
            }
            $collection->leftJoin("{$requestTable}", "{$candidateRequestTable}.request_id", "=", "{$requestTable}.id");
        }
        if (in_array("team_candidate.name as team_of_candidate", $columns)) {
            $collection->leftJoin("{$teamTable} as team_candidate", "{$candidateTable}.team_id", "=", "team_candidate.id");
        }
        if (in_array("position_apply", $columns)) {
            if (!isset($filter["{$candidatePosTable}.position_apply"])) {
                $collection->leftJoin("{$candidatePosTable}", "{$candidatePosTable}.candidate_id", "=", "{$candidateTable}.id");
            }
        }
        if (in_array("test_mark", $columns)) {
            if (!isset($filter["test_mark"])) {
                $collection->leftJoin('ntest_results', 'candidates.email', '=', 'ntest_results.employee_email');
                $collection->leftJoin('ntest_tests', 'ntest_tests.id', '=', 'ntest_results.test_id');
                $collection->leftJoin('ntest_types', 'ntest_tests.type_id', '=', 'ntest_types.id');
            }
        }
        if (in_array("language", $columns)) {
            if (!isset($filter["language"])) {
                $collection->leftJoin("{$candidateLangTable}", "{$candidateTable}.id", '=', "{$candidateLangTable}.candidate_id");
                $collection->leftJoin("{$langTable}", "{$langTable}.id", '=', "{$candidateLangTable}.lang_id");
                $collection->leftJoin("{$langLevelTable}", "{$langLevelTable}.id", '=', "{$candidateLangTable}.lang_level_id");
            }
        }
        if (in_array("program", $columns)) {
            if (!isset($filter["program"])) {
                $collection->leftJoin("{$canProTable}", "{$candidateTable}.id", '=', "{$canProTable}.candidate_id");
                $collection->leftJoin("{$proLangTable}", "{$proLangTable}.id", '=', "{$canProTable}.programming_id");
            }
        }
        if (in_array("emp_created.email as created_by", $columns)) {
            $empTable = Employee::getTableName();
            $collection->leftJoin("{$empTable} as emp_created", "emp_created.id", '=', "{$candidateTable}.created_by");
        }
        if (in_array("emp_found.email as found_by", $columns)) {
            $empTable = Employee::getTableName();
            $collection->leftJoin("{$empTable} as emp_found", "emp_found.id", '=', "{$candidateTable}.found_by");
        }
    }

    /**
     * Get select columns in search advance
     *
     * @param array $columns
     * @return array
     */
    public static function selectSearch($columns, $selectCase = false)
    {
        $candidateTable = self::getTableName();
        $selectColumns = $columns;
        if (in_array("candidates.status", $columns)) {
            $selectColumns = array_merge($selectColumns, [
                'offer_result',
                'interview_result',
                'test_result',
                'contact_result',
            ]);
        }
        if (in_array("request_name_search", $columns)) {
            $candidateRequestTable = CandidateRequest::getTableName();
            $requestTable = ResourceRequest::getTableName();
            $selectColumns = array_diff($selectColumns, ["request_name_search"]);
            $selectColumns [] = DB::raw("
                (
                    select group_concat(distinct {$requestTable}.title separator ', ') 
                    from {$requestTable}
                        inner join {$candidateRequestTable} on {$candidateRequestTable}.request_id = {$requestTable}.id 
                    where {$candidateRequestTable}.candidate_id = {$candidateTable}.id  
                ) as request_name_search
            ");
        }
        if (in_array("team_name_search", $columns)) {
            $teamTable = Team::getTableName();
            $candidateTeamTable = CandidateTeam::getTableName();
            $selectColumns = array_diff($selectColumns, ["team_name_search"]);
            $selectColumns [] = DB::raw("
                (
                    select group_concat(distinct {$teamTable}.name separator ', ') 
                    from {$teamTable} 
                        inner join {$candidateTeamTable} on {$candidateTeamTable}.team_id = {$teamTable}.id 
                    where {$candidateTeamTable}.candidate_id = {$candidateTable}.id  
                ) as team_name_search
            ");
        }
        if (in_array("position_apply", $columns)) {
            $candidatePosTable = CandidatePosition::getTableName();
            $selectColumns = array_diff($selectColumns, ["position_apply"]);
            if (!$selectCase) {
                $selectColumns [] = DB::raw("
                    group_concat(distinct {$candidatePosTable}.position_apply order by {$candidatePosTable}.position_apply) as position_apply
                ");
            } else {
                $selectColumns[] = DB::raw(
                    'GROUP_CONCAT(DISTINCT('. getOptions::selectCase($candidatePosTable.'.position_apply', getOptions::getInstance()->getRoles()) .') '
                        . 'ORDER BY '. $candidatePosTable .'.position_apply) as position_apply'
                );
            }
        }
        if (in_array("{$candidateTable}.interviewer", $columns)) {
            $empTable = Employee::getTableName();
            $selectColumns = array_diff($selectColumns, ["{$candidateTable}.interviewer"]);
            $selectColumns [] = DB::raw("(SELECT GROUP_CONCAT(LEFT(email,LOCATE('@',email) - 1) ORDER BY email separator ', ') FROM {$empTable} WHERE FIND_IN_SET (id, {$candidateTable}.interviewer)) AS interviewer");
        }
        if (in_array("test_mark", $columns)) {
            $testResultTable = Result::getTableName();
            $selectColumns = array_diff($selectColumns, ["test_mark"]);
            $caseType = "(case when (ntest_tests.type_id is not null) THEN ntest_types.name ELSE 'GMAT' END)";
            $countQuestion = "(SELECT COUNT(question_id) FROM ntest_test_question where test_id = ntest_tests.id)";
            $caseTotalQuestion = "(case when (ntest_results.total_question is not null) THEN ntest_results.total_question ELSE {$countQuestion} END)";
            $selectColumns [] = DB::raw(
                "CONCAT(
                    (case 
                       when ((select count(id) from ntest_results where employee_email = candidates.email) > 0) 
                       THEN GROUP_CONCAT(
                                distinct concat( {$caseType}, ': ',total_corrects, '/', {$caseTotalQuestion} ) 
                                order by ntest_results.created_at desc 
                                separator '<br>'
                            )
                       ELSE '' 
                    END)
                    ,
                    (case 
                       when ((select count(id) from ntest_results where employee_email = candidates.email) > 0) 
                       THEN '<br>'
                       ELSE '' 
                    END),
                    (case when (candidates.test_mark is not null AND candidates.test_mark != '') THEN concat('GMAT: ', candidates.test_mark) ELSE '' END),
                    (case when (candidates.test_mark is not null AND candidates.test_mark != '') THEN '<br>' ELSE '' END),
                    (case when (candidates.test_mark_specialize is not null AND candidates.test_mark_specialize != '') THEN concat('Other: ', candidates.test_mark_specialize) ELSE '' END)
                 ) as test_mark
            ");
        }
        if (in_array("language", $columns)) {
            $langTable = Languages::getTableName();
            $candidatelang = CandidateLanguages::getTableName();
            $langLevelTable = LanguageLevel::getTableName();
            $selectColumns = array_diff($selectColumns, ["language"]);
            $case = "(case when ({$candidatelang}.lang_level_id is not null) THEN {$langLevelTable}.name ELSE '' END)";
            $selectColumns [] = DB::raw(
                   "GROUP_CONCAT(
                        distinct concat({$langTable}.name, ' ', {$case} )
                        order by {$langTable}.name 
                        separator ', '
                    ) as language
            ");
        }
        if (in_array("program", $columns)) {
            $proLangTable = Programs::getTableName();
            $selectColumns = array_diff($selectColumns, ["program"]);
            $selectColumns [] = DB::raw(
                   "GROUP_CONCAT(
                        distinct {$proLangTable}.name 
                        order by {$proLangTable}.name 
                        separator ', '
                    ) as program
            ");
        }
        //custom select case
        if ($selectCase) {
            $listResults = getOptions::listResults();
            if (in_array($candidateTable . '.status', $columns)) {
                $col = $candidateTable . '.status';
                $selectColumns = array_diff($selectColumns, [$col]);
                $selectColumns[] = DB::raw(getOptions::selectCase($col, getOptions::getAllStatues()) . ' AS ' . 'status');
            }
            if (in_array($candidateTable . '.contact_result', $columns)) {
                $col = $candidateTable . '.contact_result';
                $selectColumns = array_diff($selectColumns, [$col]);
                $listResults[getOptions::RESULT_DEFAULT] = Lang::get('resource::view.Contacting');
                $selectColumns[] = DB::raw(getOptions::selectCase($col, $listResults) . ' AS contact_result');
            }
            if (in_array($candidateTable . '.test_result', $columns)) {
                $col = $candidateTable . '.test_result';
                $selectColumns = array_diff($selectColumns, [$col]);
                $listResults[getOptions::RESULT_DEFAULT] = Lang::get('resource::view.Testing');
                $selectColumns[] = DB::raw(getOptions::selectCase($col, $listResults) . ' AS test_result');
            }
            if (in_array($candidateTable . '.interview_result', $columns)) {
                $col = $candidateTable . '.interview_result';
                $selectColumns = array_diff($selectColumns, [$col]);
                $listResults[getOptions::RESULT_DEFAULT] = Lang::get('resource::view.Interviewing');
                $selectColumns[] = DB::raw(getOptions::selectCase($col, $listResults) . ' AS interview_result');
            }
            if (in_array($candidateTable . '.offer_result', $columns)) {
                $col = $candidateTable . '.offer_result';
                $selectColumns = array_diff($selectColumns, [$col]);
                $listResults[getOptions::RESULT_DEFAULT] = Lang::get('resource::view.Offering');
                $selectColumns[] = DB::raw(getOptions::selectCase($col, $listResults) . ' AS offer_result');
            }
            if (in_array($candidateTable . '.gender', $columns)) {
                $col = $candidateTable . '.gender';
                $selectColumns = array_diff($selectColumns, [$col]);
                $selectColumns[] = DB::raw(getOptions::selectCase($col, getOptions::listGender()) . ' AS gender');
            }
            if (in_array($candidateTable . '.type', $columns)) {
                $col = $candidateTable . '.type';
                $selectColumns = array_diff($selectColumns, [$col]);
                $selectColumns[] = DB::raw(getOptions::selectCase($col, Candidate::listTypes()) . ' AS type');
            }
            if (in_array($candidateTable . '.type_candidate', $columns)) {
                $col = $candidateTable . '.type_candidate';
                $selectColumns = array_diff($selectColumns, [$col]);
                $selectColumns[] = DB::raw(getOptions::selectCase($col, Candidate::getAllTypeCandidate()) . ' AS type_candidate');
            }
            if (in_array($candidateTable . '.working_type', $columns)) {
                $col = $candidateTable . '.working_type';
                $selectColumns = array_diff($selectColumns, [$col]);
                $selectColumns[] = DB::raw(getOptions::selectCase($col, array_merge(getOptions::listWorkingTypeExternal(), getOptions::listWorkingTypeInternal())) . ' AS working_type');
            }
        }
        return $selectColumns;
    }

    /**
     * Get candidate data. Custom filter
     * @param type $collection
     * @param type $filter
     */
    public static function searchExcept($collection, $filter)
    {
        $candidateTable = Candidate::getTableName();
        $candidateRequestTable = CandidateRequest::getTableName();
        $candidateTeamTable = CandidateTeam::getTableName();
        $candidatePosTable = CandidatePosition::getTableName();
        $candidateLangTable = CandidateLanguages::getTableName();
        $langTable = Languages::getTableName();
        $langLevelTable = LanguageLevel::getTableName();
        $canProTable = CandidateProgramming::getTableName();
        $proLangTable = Programs::getTableName();
        $arrayField = [
            "{$candidateRequestTable}.request_id",
            "{$candidateTeamTable}.team_id",
            "{$candidatePosTable}.position_apply"
        ];
        //Filter by request, group, position apply
        if (count($filter)) {
            foreach ($filter as $field => $data) {
                if (in_array($field, $arrayField)) {
                    $value = $data['value'];
                    $joinTable = $data['joinTable'];
                    if ($joinTable !== 'candidate_team') {
                        $collection->leftJoin($joinTable, $data['joinField'], '=', $data['joinToField']);
                    }
                    $compare = $data['compare'];
                    switch ($compare) {
                        case self::COMPARE_NOT_EQUAL:
                            $collection->whereRaw("{$candidateTable}.id NOT IN (SELECT candidate_id FROM {$joinTable} WHERE {$field} = ?) AND {$candidateTable}.id IN (SELECT candidate_id FROM {$joinTable})", [$value]);
                            break;
                        case self::COMPARE_IS_NULL:
                            $collection->whereRaw("{$candidateTable}.id NOT IN (SELECT candidate_id FROM {$joinTable} )");
                            break;
                        case self::COMPARE_IS_NOT_NULL:
                            $collection->whereNotNull($field);
                            break;
                        case self::COMPARE_EQUAL:
                            $collection->whereRaw("{$candidateTable}.id IN (SELECT candidate_id FROM {$joinTable} WHERE {$field} = ?)", [$value]);
                            break;
                        default:
                            $collection->where($field, $compare, $value);
                    }
                }
            }
        }
        //Filter by test mark
        if (isset($filter['test_mark'])) {
            $collection->leftJoin('ntest_results', 'candidates.email', '=', 'ntest_results.employee_email');
            $collection->leftJoin('ntest_tests', 'ntest_tests.id', '=', 'ntest_results.test_id');
            $collection->leftJoin('ntest_types', 'ntest_tests.type_id', '=', 'ntest_types.id');
            $compare = $filter['test_mark']['compare'];
            $typeTest = $filter['test_mark']['extra'];
            $valueFilter = explode('/', $filter['test_mark']['value']);
            if (isset($valueFilter[1])) {
                $value = (empty($valueFilter[1])) ? 0 : round($valueFilter[0]/$valueFilter[1], 2);
            } else {
                $value = round($valueFilter[0], 2);
            }
            //$value = isset($valueFilter[1]) ? round($valueFilter[0]/$valueFilter[1], 2) : (float)$valueFilter[0];
            $testMarkInCandiTblIsNull = "candidates.test_mark IS NULL OR candidates.test_mark = ''";
            $testMarkInCandiTblIsNotNull = "candidates.test_mark IS NOT NULL AND candidates.test_mark != ''";
            $testMarkSpeCandiTblIsNull = "candidates.test_mark_specialize IS NULL OR candidates.test_mark_specialize = ''";
            $testMarkSpeCandiTblIsNotNull = "candidates.test_mark_specialize IS NOT NULL AND candidates.test_mark_specialize != ''";
            switch ($compare) {
                case self::COMPARE_IS_NULL:
                    if (!empty($typeTest)) {
                        if ($typeTest == self::GMAT) {
                            $collection->whereRaw("(candidates.email NOT IN (SELECT employee_email FROM ntest_results INNER JOIN ntest_tests ON ntest_results.test_id = ntest_tests.id WHERE ntest_tests.type = ?) AND ({$testMarkInCandiTblIsNull}))", [Test::TYPE_GMAT]);
                        } elseif ($typeTest == self::SPECIALIZE) {
                            $collection->whereRaw("({$testMarkSpeCandiTblIsNull})");
                        }
                        else {
                            $collection->whereRaw("candidates.email NOT IN (SELECT employee_email FROM ntest_results INNER JOIN ntest_tests ON ntest_results.test_id = ntest_tests.id WHERE ntest_tests.type <> ? AND ntest_tests.type_id = ?)", [Test::TYPE_GMAT, $filter['test_mark']['extra']]);
                        }
                    } else {
                        $collection->whereRaw("((SELECT COUNT(id) FROM ntest_results WHERE employee_email = candidates.email) = 0 AND ({$testMarkInCandiTblIsNull}) AND ({$testMarkSpeCandiTblIsNull}))");
                    }
                    break;
                case self::COMPARE_IS_NOT_NULL:
                    if (!empty($typeTest)) {
                        if ($typeTest == self::GMAT) {
                            $collection->whereRaw("(candidates.email IN (SELECT employee_email FROM ntest_results INNER JOIN ntest_tests ON ntest_results.test_id = ntest_tests.id WHERE ntest_tests.type = ?) OR ({$testMarkInCandiTblIsNotNull}))", [Test::TYPE_GMAT]);
                        } elseif ($typeTest == self::SPECIALIZE) {
                            $collection->whereRaw("({$testMarkSpeCandiTblIsNotNull})");
                        } else {
                            $collection->whereRaw("candidates.email IN (SELECT employee_email FROM ntest_results INNER JOIN ntest_tests ON ntest_results.test_id = ntest_tests.id WHERE ntest_tests.type <> ? AND ntest_tests.type_id = ?)", [Test::TYPE_GMAT, $filter['test_mark']['extra']]);
                        }
                    } else {
                        $collection->whereRaw("((SELECT COUNT(id) FROM ntest_results WHERE employee_email = candidates.email) > 0  OR ({$testMarkInCandiTblIsNotNull}) OR ({$testMarkSpeCandiTblIsNotNull}))");
                    }
                    break;
                default:
                    $binding = [];
                    $whereRaw = "(candidates.email IN (SELECT employee_email FROM ntest_results INNER JOIN ntest_tests ON ntest_results.test_id = ntest_tests.id ";
                    $whereRaw .= "WHERE ";
                    if (empty($typeTest)) {
                        $whereRaw .= "(";
                    }
                    $whereRaw .= "(ROUND((case when (ntest_results.total_question is not null) THEN (total_corrects/ntest_results.total_question) ELSE (total_corrects/(SELECT COUNT(question_id) FROM ntest_test_question where test_id = ntest_tests.id)) END), 2) $compare ?  ";
                    $binding [] = [$value];
                    if (empty($typeTest)) {
                        $whereRaw .= " OR CAST(if(test_mark LIKE '%/%', SUBSTRING_INDEX(test_mark, '/', 1) / SUBSTRING_INDEX(test_mark, '/', -1) , test_mark) as  decimal(10,2) ) $compare ? ";
                        $whereRaw .= " OR CAST(if(test_mark_specialize LIKE '%/%', SUBSTRING_INDEX(test_mark_specialize, '/', 1) / SUBSTRING_INDEX(test_mark_specialize, '/', -1) , test_mark_specialize) as  decimal(10,2) ) $compare ? ";
                        $whereRaw .= "))";
                        $binding [] = [$value, $value];
                    } else {
                        $binding [] = Test::TYPE_GMAT;
                        if ($typeTest == self::GMAT) {
                            $whereRaw .= "AND ntest_tests.type = ?) OR CAST(if(test_mark LIKE '%/%', SUBSTRING_INDEX(test_mark, '/', 1) / SUBSTRING_INDEX(test_mark, '/', -1) , test_mark) as  decimal(10,2) ) $compare ? ";
                            $binding [] = [$value];
                        } elseif ($typeTest == self::SPECIALIZE) {
                            $whereSpecialize = " CAST(if(test_mark_specialize LIKE '%/%', SUBSTRING_INDEX(test_mark_specialize, '/', 1) / SUBSTRING_INDEX(test_mark_specialize, '/', -1) , test_mark_specialize) as  decimal(10,2) ) $compare ? AND {$testMarkSpeCandiTblIsNotNull}";
                            $bindingSpecialize = [$value];
                        }
                        else {
                            $whereRaw .= "AND ntest_tests.type <> ? AND ntest_tests.type_id = ? )";
                            $binding [] = $typeTest;
                        }
                    }
                    $whereRaw .= "))";
                    if (empty($typeTest) || $typeTest != self::SPECIALIZE) {
                        $collection->whereRaw($whereRaw, $binding);
                    } else {
                        $collection->whereRaw($whereSpecialize, $bindingSpecialize);
                    }
            }
        }
        //Filter by interviewer
        if (isset($filter['interviewer'])) {
            $compare = $filter['interviewer']['compare'];
            switch ($compare) {
                case self::COMPARE_NOT_EQUAL:
                case self::COMPARE_GREATER:
                case self::COMPARE_SMALLER:
                    $collection->whereRaw("candidates.interviewer IS NOT NULL AND NOT FIND_IN_SET(?,candidates.interviewer)",
                        [$filter['interviewer']['value']]);
                    break;
                case self::COMPARE_IS_NULL:
                    $collection->whereRaw("candidates.interviewer IS NULL");
                    break;
                case self::COMPARE_IS_NOT_NULL:
                    $collection->whereRaw("candidates.interviewer IS NOT NULL");
                    break;
                default:
                    $collection->whereRaw("candidates.interviewer IS NOT NULL AND FIND_IN_SET(?,candidates.interviewer)",
                        [$filter['interviewer']['value']]);
            }
        }
        //Filter by language
        if (isset($filter['language'])) {
            $collection->leftJoin("{$candidateLangTable}", "{$candidateTable}.id", '=', "{$candidateLangTable}.candidate_id");
            $collection->leftJoin("{$langTable}", "{$langTable}.id", '=', "{$candidateLangTable}.lang_id");
            $collection->leftJoin("{$langLevelTable}", "{$langLevelTable}.id", '=', "{$candidateLangTable}.lang_level_id");
            $compare = $filter['language']['compare'];
            //extra condition: when compare not in (is null && is not null) and has language level compare
            $extraCondition = !in_array($compare, [self::COMPARE_IS_NULL, self::COMPARE_IS_NOT_NULL])
                                && !empty($filter['language']['extra']);
            switch ($compare) {
                case self::COMPARE_IS_NULL:
                    $collection->whereRaw("(SELECT COUNT(id) FROM {$candidateLangTable} WHERE candidate_id = {$candidateTable}.id) = 0");
                    break;
                case self::COMPARE_IS_NOT_NULL:
                    $collection->whereRaw("(SELECT COUNT(id) FROM {$candidateLangTable} WHERE candidate_id = {$candidateTable}.id) > 0");
                    break;
                case self::COMPARE_EQUAL:
                    if (!$extraCondition) {
                        $collection->whereRaw("{$candidateTable}.id IN (SELECT candidate_id FROM {$candidateLangTable} WHERE lang_id = ?)", [$filter['language']['value']]);
                    }
                    break;
                default:
                    if (!$extraCondition) {
                        $collection->whereRaw("{$candidateTable}.id NOT IN (SELECT candidate_id FROM {$candidateLangTable} WHERE lang_id = ?) AND {$candidateTable}.id IN (SELECT candidate_id FROM {$candidateLangTable})", [$filter['language']['value']]);
                    }
                    break;
            }
            //Filter by language level
            if ($extraCondition) {
                if ($compare == self::COMPARE_EQUAL) {
                    $collection->whereRaw("{$candidateTable}.id IN (SELECT candidate_id FROM {$candidateLangTable} WHERE lang_id = ? AND lang_level_id = ?)", [$filter['language']['value'], $filter['language']['extra']]);
                } else {
                    $collection->whereRaw("{$candidateTable}.id NOT IN (SELECT candidate_id FROM {$candidateLangTable} WHERE lang_id = ? AND lang_level_id = ?) AND {$candidateTable}.id IN (SELECT candidate_id FROM {$candidateLangTable})", [$filter['language']['value'], $filter['language']['extra']]);
                }
            }
        }
        //Filter by programming language
        if (isset($filter['program'])) {
            $collection->leftJoin("{$canProTable}", "{$candidateTable}.id", '=', "{$canProTable}.candidate_id");
            $collection->leftJoin("{$proLangTable}", "{$proLangTable}.id", '=', "{$canProTable}.programming_id");
            $compare = $filter['program']['compare'];
            switch ($compare) {
                case self::COMPARE_IS_NULL:
                    $collection->whereRaw("(SELECT COUNT(id) FROM {$canProTable} WHERE candidate_id = {$candidateTable}.id) = 0");
                    break;
                case self::COMPARE_IS_NOT_NULL:
                    $collection->whereRaw("(SELECT COUNT(id) FROM {$canProTable} WHERE candidate_id = {$candidateTable}.id) > 0");
                    break;
                case self::COMPARE_EQUAL:
                    $collection->whereRaw("{$candidateTable}.id IN (SELECT candidate_id FROM {$canProTable} WHERE programming_id = ?)", [$filter['program']['value']]);
                    break;
                default:
                    $collection->whereRaw("{$candidateTable}.id NOT IN (SELECT candidate_id FROM {$canProTable} WHERE programming_id = ?) AND {$candidateTable}.id IN (SELECT candidate_id FROM {$canProTable})", [$filter['program']['value']]);
                    break;
            }
        }
        //Filter by status
        if (isset($filter['candidates.status'])) {
            $compare = $filter['candidates.status']['compare'];
            switch ($compare) {
                case self::COMPARE_EQUAL:
                    static::filterStatus($collection, $filter['candidates.status']['value']);
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Get compare list
     * @return array
     */
    public static function getCompareList()
    {
        return [
            self::COMPARE_LIKE, self::COMPARE_EQUAL, self::COMPARE_GREATER, self::COMPARE_SMALLER,
            self::COMPARE_GREATER_EQUAL, self::COMPARE_SMALLER_EQUAL,
            self::COMPARE_NOT_EQUAL, self::COMPARE_IS_NULL,
            self::COMPARE_IS_NOT_NULL
        ];
    }

    /**
     * Get columns of seaech advance
     *
     * @return array two multidimensional
     */
    public static function getColumns()
    {
        $candidateTable = Candidate::getTableName();
        $candidateTeamTable = CandidateTeam::getTableName();
        $candidatePosTable = CandidatePosition::getTableName();
        $resultTable = Result::getTableName();
        $candidateLangtable = CandidateLanguages::getTableName();
        $canProTable = CandidateProgramming::getTableName();
        $channelTable = Channels::getTableName();
        $empTable = Employee::getTableName();
        $requestTable = ResourceRequest::getTableName();
        return [
            ['field' => "{$candidateTable}.id", 'label' => Lang::get('resource::view.Id'), 'data' => 'id', 'default' => self::COLUMN_DEFAULT],
            ['field' => "{$candidateTable}.fullname", 'label' => Lang::get('resource::view.Full name'), 'data' => 'fullname', 'default' => self::COLUMN_DEFAULT],
            ['field' => "{$candidateTable}.email", 'label' => Lang::get('resource::view.Email'), 'data' => 'email', 'default' => self::COLUMN_DEFAULT],
            ['field' => "request_name_search", 'label' => Lang::get('resource::view.Request'), 'data' => 'request_name_search', 'default' => self::COLUMN_DEFAULT],
            ['field' => "team_name_search", 'label' => Lang::get('resource::view.Groups of request'), 'data' => 'team_name_search', 'default' => self::COLUMN_DEFAULT],
            ['field' => "position_apply", 'label' => Lang::get('resource::view.Position'), 'data' => 'position_apply', 'default' => self::COLUMN_DEFAULT],
            ['field' => "{$channelTable}.name as channel_name_fix", 'label' => Lang::get('resource::view.Channel'), 'data' => "channel_name_fix"],
            ['field' => "{$candidateTable}.birthday", 'label' => Lang::get('resource::view.Birthday'), 'data' => 'birthday'],
            ['field' => "{$candidateTable}.mobile", 'label' => Lang::get('resource::view.Mobile'), 'data' => 'mobile'],
            ['field' => "{$candidateTable}.university", 'label' => Lang::get('resource::view.University'), 'data' => 'university'],
            ['field' => "{$candidateTable}.certificate", 'label' => Lang::get('resource::view.Certificate'), 'data' => 'certificate'],
            ['field' => "{$candidateTable}.old_company", 'label' => Lang::get('resource::view.Old company'), 'data' => 'old_company'],
            ['field' => "{$candidateTable}.experience", 'label' => Lang::get('resource::view.Experience'), 'data' => 'experience'],
            ['field' => "{$candidateTable}.received_cv_date", 'label' => Lang::get('resource::view.Received cv date'), 'data' => 'received_cv_date'],
            ['field' => "{$candidateTable}.contact_result", 'label' => Lang::get('resource::view.Contact result'), 'data' => 'contact_result'],
            ['field' => "{$candidateTable}.test_plan", 'label' => Lang::get('resource::view.Test plan'), 'data' => 'test_plan'],
            ['field' => "test_mark", 'label' => Lang::get('resource::view.Test mark'), 'data' => 'test_mark'],
            ['field' => "{$candidateTable}.test_result", 'label' => Lang::get('resource::view.Test result'), 'data' => 'test_result'],
            ['field' => "{$candidateTable}.interview_plan", 'label' => Lang::get('resource::view.Interview plan'), 'data' => 'interview_plan'],
            ['field' => "{$candidateTable}.interview2_plan", 'label' => Lang::get('resource::view.Interview plan 2'), 'data' => 'interview2_plan'],
            ['field' => "{$candidateTable}.interview_result", 'label' => Lang::get('resource::view.Interview result'), 'data' => 'interview_result'],
            ['field' => "emp_created.email as created_by", 'label' => Lang::get('resource::view.Created by'), 'data' => 'created_by'],
            ['field' => "{$candidateTable}.test_note", 'label' => Lang::get('resource::view.Test note'), 'data' => 'test_note'],
            ['field' => "{$candidateTable}.created_at", 'label' => Lang::get('resource::view.Created at'), 'data' => 'created_at'],
            ['field' => "{$candidateTable}.updated_at", 'label' => Lang::get('resource::view.Updated at'), 'data' => 'updated_at'],
            ['field' => "{$candidateTable}.status", 'label' => Lang::get('resource::view.Status'), 'data' => 'status', 'default' => self::COLUMN_DEFAULT],
            ['field' => "{$candidateTable}.interview_note", 'label' => Lang::get('resource::view.Interview note'), 'data' => 'interview_note'],
            ['field' => "{$candidateTable}.offer_date", 'label' => Lang::get('resource::view.Offer date'), 'data' => 'offer_date'],
            ['field' => "{$candidateTable}.offer_result", 'label' => Lang::get('resource::view.Offer result'), 'data' => 'offer_result'],
            ['field' => "{$candidateTable}.offer_feedback_date", 'label' => Lang::get('resource::view.Offer feedback date'), 'data' => 'offer_feedback_date'],
            ['field' => "{$candidateTable}.offer_note", 'label' => Lang::get('resource::view.Offer note'), 'data' => 'offer_note'],
            ['field' => "{$candidateTable}.interviewer", 'label' => Lang::get('resource::view.Interviewer'), 'data' => 'interviewer'],
            ['field' => "{$candidateTable}.recruiter", 'label' => Lang::get('resource::view.Recruiter'), 'data' => 'recruiter', 'default' => self::COLUMN_DEFAULT],
            ['field' => "{$empTable}.email as presenter_email", 'label' => Lang::get('resource::view.Presenter'), 'data' => 'presenter_email'],
            ['field' => "emp_found.email as found_by", 'label' => Lang::get('resource::view.Found by'), 'data' => 'found_by'],
            ['field' => "{$candidateTable}.start_working_date", 'label' => Lang::get('resource::view.Start working date'), 'data' => 'start_working_date'],
            ['field' => "{$candidateTable}.trial_work_end_date", 'label' => Lang::get('resource::view.Trial work end date'), 'data' => 'trial_work_end_date'],
            ['field' => "{$candidateTable}.screening", 'label' => Lang::get('resource::view.Screening'), 'data' => 'screening'],
            ['field' => "{$candidateTable}.type", 'label' => Lang::get('resource::view.Type'), 'data' => 'type', 'default' => self::COLUMN_DEFAULT],
            ['field' => "{$candidateTable}.type_candidate", 'label' => Lang::get('resource::view.Type_candidate'), 'data' => 'type_candidate', 'default' => self::COLUMN_DEFAULT],
            ['field' => "{$candidateTable}.identify", 'label' => Lang::get('resource::view.Identify'), 'data' => 'identify'],
            ['field' => "{$candidateTable}.skype", 'label' => Lang::get('resource::view.Skype'), 'data' => 'skype'],
            ['field' => "{$candidateTable}.other_contact", 'label' => Lang::get('resource::view.Other contact'), 'data' => 'other_contact'],
            ['field' => "{$candidateTable}.gender", 'label' => Lang::get('resource::view.Gender'), 'data' => 'gender'],
            ['field' => "language", 'label' => Lang::get('resource::view.Language'), 'data' => 'language'],
            ['field' => "program", 'label' => Lang::get('resource::view.Program'), 'data' => 'program'],
            ['field' => "team_candidate.name as team_of_candidate", 'label' => Lang::get('resource::view.Group'), 'data' => 'team_of_candidate'],
            ['field' => "{$candidateTable}.working_type", 'label' => Lang::get('resource::view.Working type'), 'data' => 'working_type'],
        ];
    }

    /**
     * Get field of columns selected
     *
     * @param array $columns
     * @return array
     */
    public static function getFieldOfColumnsSelected($columns)
    {
        $fieldSelected = [];
        foreach ($columns as $column) {
            $fieldSelected[] = $column['field'];
        }
        return $fieldSelected;
    }

    /**
     * gen suggest email from name
     * @param string $name
     * @return string
     */
    public static function genSuggestEmail($name)
    {
        $arrStr = explode('-', str_slug($name));
        $email = $arrStr[0];
        $len = count($arrStr);
        if (count($arrStr) > 1) {
            $email = $arrStr[$len - 1];
            array_pop($arrStr);
            foreach ($arrStr as $str) {
                $email .= substr($str, 0, 1);
            }
        }
        $numExists = Employee::select('email')
                ->where('email', 'like', $email . '%')
                ->orderBy('email', 'DESC')
                ->get();
        $foxNumber = 0;
        if (!$numExists->isEmpty()) {
            $emailLen = strlen($email);
            foreach ($numExists as $emp) {
                $account = explode('@', $emp->email)[0];
                $accLen = strlen($account);
                $number = substr($account, $emailLen, $accLen - $emailLen);
                if ($foxNumber == 0 && strlen($number) == 0) {
                    $foxNumber = 1;
                }
                $number = (int) $number;
                if ($number > $foxNumber) {
                    $foxNumber = $number;
                }
            }
        }
        if ($foxNumber > 0) {
            $email .= ($foxNumber + 1) . '';
        }
        return $email . '@rikkeisoft.com';
    }

    /**
     * Get candidate by employee_id
     *
     * @param int $empId
     * @param array $selectCols
     * @return Candidate object
     */
    public static function getCandidateByEmployee($empId, $selectCols = ['*'])
    {
        return self::where('employee_id', $empId)->select($selectCols)->first();
    }

    /**
     * Get list Candidate by conditions
     * @param array $fields
     * @param array $col
     * @param array $orderBy
     * @return Candidate collection
     */
    public static function getCandidates($conditions, $col = ['*'])
    {
        $list = self::select($col);
        if (is_array($conditions)) {
            foreach ($conditions as $field => $value) {
                $list->where($field, $value);
            }
        }
        $list->orderBy('id', 'desc');
        return $list->get();
    }

    /**
     * Get all type candidate(from intranet or webvn)
     * @return array
     */
    public static function getAllTypeCandidate()
    {
        return [
            self::TYPE_FROM_WEBVN => Lang::get('resource::view.WebVN'),
            self::TYPE_FROM_WEBVN_INTEREST => Lang::get('resource::view.WebVNInterest'),
            self::TYPE_FROM_WEBVN_INTEREST_NOT_EMAIL => Lang::get('resource::view.WebVNInterestNotEmail'),
            self::TYPE_FROM_INTRANET => Lang::get('resource::view.Intranet'),
        ];
    }

    /**
     * Get type candidate from key
     * @param int $key
     * @return string
     */
    public static function getTypeCandidate($key)
    {
        switch ($key) {
            case self::TYPE_FROM_WEBVN: return Lang::get('resource::view.WebVN');
            case self::TYPE_FROM_WEBVN_INTEREST: return Lang::get('resource::view.WebVNInterest');
            case self::TYPE_FROM_WEBVN_INTEREST_NOT_EMAIL: return Lang::get('resource::view.WebVNInterestNotEmail');
            case self::TYPE_FROM_INTRANET: return Lang::get('resource::view.Intranet');
            default: return '';
        }
    }

    /** set test gmat point attibute when candidate save
     * @param type $value
     */
    public function setTestGmatPointAttribute()
    {
        if ($this->test_mark) {
            $point = trim($this->test_mark);
            $arrMark = explode('/', $point);
            if (count($arrMark) === 2) {
                $divide = intval(trim($arrMark[1]));
                if ($divide == 0) {
                    $point = 0;
                } else {
                    $point = intval(trim($arrMark[0])) / $divide * 10;
                }
            }
            $this->attributes['test_gmat_point'] = $point;
        }
    }

    /**
     * udpate employee that belongs to
     * @param type $data
     */
    public function updateEmployee($data = [])
    {
        $employee = $this->employee;
        if (!$employee) {
            return;
        }
        $employee->working_type = isset($data['working_type']) ? $data['working_type'] : '';
        $employee->contract_length = isset($data['contract_length']) ? $data['contract_length'] : '';
        if (isset($data['start_working_date'])) {
            $employee->join_date = $data['start_working_date'];
        }

        // working type = PROBATION => update trial_date and trial_end_date by trial_work_start_date and trial_work_end_date of candidate
        if (isset($data['working_type']) && (int) $data['working_type'] === getOptions::WORKING_PROBATION) {
            if (isset($data['trial_work_start_date'])) {
                $employee->trial_date = $data['trial_work_start_date'];
            }
            if (isset($data['trial_work_end_date'])) {
                $employee->trial_end_date = $data['trial_work_end_date'];
            }
        }
        // working type = OFFICIAL or UNLIMIT => update offcial_date of employee by start_working_date of candidate
        if (isset($data['working_type']) && in_array($data['working_type'], getOptions::workingTypeOfficial())) {
            if (isset($data['start_working_date'])) {
                $employee->offcial_date = $data['start_working_date'];
            }
        } else {
            if (!empty($data['official_date'])) {
                $employee->offcial_date = $data['official_date'];
            }
        }
        $employee->save();
        //update work
        static::updateEmployeeWork($employee, $data);
    }

    /*
     * update employee work
     */
    public static function updateEmployeeWork($employee = null, $data = [])
    {
        if (!$employee || !isset($data['working_type'])) {
            return;
        }
        //update employee work
        $employeeWork = $employee->getItemRelate('work');
        $employeeWork->employee_id = $employee->id;
        $employeeWork->setData(['contract_type' => $data['working_type']]);
        $employeeWork->save();
        CacheHelper::forget(Employee::KEY_CACHE, $employee->id);
    }

    /*
     * request asset
     */
    public function requestAsset()
    {
        return $this->belongsTo('\Rikkei\Assets\Model\RequestAsset', 'request_asset_id');
    }

    /*
     * create request asset
     */
    public function updateRequestAsset($employee = null)
    {
        $requestAsset = $this->requestAsset;
        if (!$requestAsset) {
            RequestAsset::createDefaultCdd($this, $employee ? $employee : $this->employee);
        }
    }

    /*
     * cron job update leaved off status
     */
    public static function cronUpdateLeavedOff()
    {
        $timeNow = Carbon::now()->startOfDay();
        $cddTbl = self::getTableName();
        $collect = self::select($cddTbl . '.id', $cddTbl . '.status')
                ->join(Employee::getTableName() . ' as emp', $cddTbl . '.employee_id', '=', 'emp.id')
                ->whereNull('emp.deleted_at')
                ->whereNotNull('emp.leave_date')
                ->where(DB::raw('DATE(emp.leave_date)'), '<=', $timeNow->toDateString())
                ->where($cddTbl . '.status', '!=', getOptions::LEAVED_OFF)
                ->lists($cddTbl . '.id')
                ->toArray();

        if ($collect) {
            Candidate::whereIn('id', $collect)->update(['status' => getOptions::LEAVED_OFF]);
        }
    }


    public function updateContractTeamId(Team $teamInfo)
    {
        $this->contract_team_id = $teamInfo->id;
        $this->save();
        //Get all email receive notify create contract
        $empReceives  = Employee::getEmpByTeam($teamInfo->id);
        if(isset($empReceives) && count($empReceives) >0)
        {
            foreach ($empReceives as $empInfo)
            {
                //remove emp not permission
                $permission = $empInfo->hasPermission('contract::manage.contract.create');
                Log::info(['contract::manage.contract'=>$permission,'email'=>$empInfo->email]);
                if(!$permission)
                {
                    continue;
                }
                $emailQueue = new EmailQueue();
                $emailQueue->setFrom(Config('mail.username'), Config('mail.name'))
                            ->setTo($empInfo->email, $empInfo->name)
                            ->setSubject(Lang::get('resource::view.【Rikkeisoft】 The candidate :name has just been updated status to preparing',
                                ['name' => $this->fullname]))
                            ->setTemplate('resource::candidate.mail.notify_set_contract', [
                                'name' => CoreView::getNickName($empInfo->email),
                                'candidateName' => $this->fullname,
                                'urlToCandidate' => route('resource::candidate.detail', $this->id)
                            ])
                            ->setNotify(
                                $empInfo->id,
                                Lang::get('resource::view.【Rikkeisoft】 The candidate :name has just been updated status to preparing', ['name' => $this->fullname]),
                                route('resource::candidate.detail', $this->id), ['category_id', RkNotify::CATEGORY_HUMAN_RESOURCE]
                            )
                            ->save();

            }
        }
    }

    /**
     * check status is fail
     */
    public function isFail()
    {
        return in_array($this->status, [
            getOptions::FAIL_CONTACT,
            getOptions::FAIL_TEST,
            getOptions::FAIL_INTERVIEW,
            getOptions::FAIL_OFFER,
            getOptions::FAIL_CDD,
            getOptions::FAIL,
        ]);
    }

    /**
     * Count all candidates' records that have not failed or left
     * @return int
     */
    public function countActiveStatus()
    {
        return self::where('email', $this->email)
            ->whereNotIn('status', getOptions::getFailOrLeaveOptions())
            ->count();
    }

    /**
     * get follow candidates list
     *
     * @param string $type
     * @param bool $isScopeTeam
     * @return mixed
     */
    public function getFollowList($type, $isScopeTeam)
    {
        if ($type === getOptions::TYPE_REMIND_SEND_MAIL_OFFER) {
            $pager = Config::getPagerData(null, ['order' => 'candidates.status_update_date', 'dir' => 'asc']);
        } else {
            $pager = Config::getPagerData(null, ['order' => 'date_interview', 'dir' => 'asc']);
        }
        //filter and paginate
        $filter = Form::getFilterData();
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        if (!$isScopeTeam) {
            $dataFilter['candidates.recruiter'] = Permission::getInstance()->getEmployee()->email;
        }
        $collection = $this->getList($pager['order'], $pager['dir'], null, null, $dataFilter);
        $tblCandidate = self::getTableName();
        if ($type === getOptions::TYPE_REMIND_SEND_MAIL_OFFER) {
            $tblCandidateMail = CandidateMail::getTableName();
            $collection->leftJoin($tblCandidateMail, function ($query) use ($tblCandidate, $tblCandidateMail) {
                $query->on("{$tblCandidateMail}.candidate_id", '=', "{$tblCandidate}.id")
                    ->where(function ($subQuery) use ($tblCandidateMail, $tblCandidate) {
                        $subQuery
                            ->where(function ($subQuery2) use ($tblCandidateMail, $tblCandidate) {
                                $subQuery2->where("{$tblCandidate}.status", '=', getOptions::OFFERING)
                                    ->whereIn("{$tblCandidateMail}.type", self::listMailOffers());
                            })
                            ->orWhere(function ($subQuery2) use ($tblCandidate, $tblCandidateMail) {
                                $subQuery2->where("{$tblCandidate}.interview_result", '=', getOptions::RESULT_FAIL)
                                    ->whereIn("{$tblCandidateMail}.type", self::listMailInterviewFails());
                            });
                    });
            })
                ->whereNull("{$tblCandidateMail}.type")
                ->where(function ($query) use ($tblCandidate) {
                    $query->where("{$tblCandidate}.status", '=', getOptions::OFFERING)
                        ->orWhere("{$tblCandidate}.interview_result", '=', getOptions::RESULT_FAIL);
                })
                ->whereDate("{$tblCandidate}.status_update_date", '<=', Carbon::now()->subDay(2)->toDateString());
            CoreModel::filterGrid($collection, [], null, 'LIKE');
        } else {
            $date4DaysAgo = Carbon::now()->subDay(4)->toDateString();
            $collection->whereRaw('CASE'
                . " WHEN {$tblCandidate}.interview2_plan IS NOT NULL AND DATE({$tblCandidate}.interview2_plan) <> '0000-00-00'"
                . " THEN DATE({$tblCandidate}.interview2_plan) <= '{$date4DaysAgo}'"
                . " ELSE {$tblCandidate}.interview_plan IS NOT NULL
                            AND DATE({$tblCandidate}.interview_plan) <> '0000-00-00'
                            AND DATE({$tblCandidate}.interview_plan) <= '{$date4DaysAgo}'"
                . ' END'
            )
                ->where("{$tblCandidate}.status", getOptions::INTERVIEWING)
                ->addSelect(DB::raw("CASE
                    WHEN {$tblCandidate}.interview2_plan IS NOT NULL AND DATE({$tblCandidate}.interview2_plan) <> '0000-00-00'
                    THEN DATE({$tblCandidate}.interview2_plan)
                    ELSE DATE({$tblCandidate}.interview_plan) END AS date_interview")
                );
            CoreModel::filterGrid($collection, ["{$tblCandidate}.status_update_date"], null, 'LIKE');
        }

        CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * list mail offers
     * @return array
     */
    public static function listMailOffers()
    {
        return [
            self::MAIL_OFFER_HH3,
            self::MAIL_OFFER_HH4,
            self::MAIL_OFFER_DN,
            self::MAIL_OFFER_HCM,
            self::MAIL_OFFER_JP,
            self::MAIL_OFFER_HANDICO,
        ];
    }

    /**
     * list mail interview fail
     * @return array
     */
    public static function listMailInterviewFails()
    {
        return [
            self::MAIL_INTERVIEW_FAIL_HN,
            self::MAIL_INTERVIEW_FAIL_DN,
            self::MAIL_INTERVIEW_FAIL_HCM,
            self::MAIL_INTERVIEW_FAIL_JP,
        ];
    }

    /**
     * find interested candidate
     * @param $id
     * @return mixed
     */
    public static function findInterestedCandidateById($id)
    {
        return self::whereIn('status', [getOptions::FAIL, getOptions::FAIL_CDD])
            ->whereIn('interested', [getOptions::INTERESTED_LESS, getOptions::INTERESTED_NORMAL, getOptions::INTERESTED_SPECIAL])
            ->find($id);
    }

    /*
     * builder collection interested candidates
     */
    public function buildCollectionInterested($dataFilter = [])
    {
        $tblCandidate = Candidate::getTableName();
        $collection = Candidate::leftJoin('candidate_programming', 'candidates.id', '=', 'candidate_programming.candidate_id')
            ->leftJoin('candidate_pos', 'candidates.id', '=', 'candidate_pos.candidate_id')
            ->whereIn("{$tblCandidate}.status", [getOptions::FAIL, getOptions::FAIL_CDD])
            ->whereIn("{$tblCandidate}.interested", [getOptions::INTERESTED_LESS, getOptions::INTERESTED_NORMAL, getOptions::INTERESTED_SPECIAL]);
        if (!empty($dataFilter)) {
            if (isset($dataFilter['candidates.recruiter'])) {
                $collection->where('candidates.recruiter', $dataFilter['candidates.recruiter']);
            }
            if (isset($dataFilter['candidate_programming.programming_id'])) {
                $collection->where('candidate_programming.programming_id', $dataFilter['candidate_programming.programming_id']);
            }
            if (isset($dataFilter['candidates.status'])) {
                if (in_array($dataFilter['candidates.status'], [
                    getOptions::FAIL_CONTACT,
                    getOptions::FAIL_TEST,
                    getOptions::FAIL_INTERVIEW,
                    getOptions::FAIL_OFFER
                ])) {
                    switch ($dataFilter['candidates.status']) {
                        case getOptions::FAIL_CONTACT:
                            $collection->where('candidates.contact_result', getOptions::RESULT_FAIL);
                            break;
                        case getOptions::FAIL_TEST:
                            $collection->where('candidates.test_result', getOptions::RESULT_FAIL);
                            break;
                        case getOptions::FAIL_INTERVIEW:
                            $collection->where('candidates.interview_result', getOptions::RESULT_FAIL);
                            break;
                        case getOptions::FAIL_OFFER:
                            $collection->where('candidates.offer_result', getOptions::RESULT_FAIL);
                            break;
                        default:
                            break;
                    }
                } elseif ($dataFilter['candidates.status'] == getOptions::FAIL) {
                    $collection->whereIn('candidates.status', [getOptions::FAIL, getOptions::FAIL_CDD]);
                } else {
                    $collection->where('candidates.status', $dataFilter['candidates.status']);
                }
            }
            if (isset($dataFilter['candidates.type_candidate'])) {
                $collection->where('candidates.type_candidate', $dataFilter['candidates.type_candidate']);
            }
            if (isset($dataFilter['candidates.position'])) {
                $collection->where('candidate_pos.position_apply', $dataFilter['candidates.position']);
            }
        }
        $collection->groupBy("{$tblCandidate}.id");
        $collection->select([
            "{$tblCandidate}.id",
            "{$tblCandidate}.interested",
            "{$tblCandidate}.fullname",
            "{$tblCandidate}.birthday",
            "{$tblCandidate}.email",
            "{$tblCandidate}.recruiter",
            "{$tblCandidate}.experience",
            "{$tblCandidate}.status",
            "{$tblCandidate}.contact_result",
            "{$tblCandidate}.test_result",
            "{$tblCandidate}.interview_result",
            "{$tblCandidate}.offer_result",
            "{$tblCandidate}.status_update_date",
            "{$tblCandidate}.type",
            DB::raw("(SELECT GROUP_CONCAT(CONCAT(name) SEPARATOR ', ')
                FROM programming_languages
                    INNER JOIN candidate_programming ON programming_languages.id = candidate_programming.programming_id
                WHERE candidate_programming.candidate_id = candidates.id
                ) AS programs_name"),
            DB::raw("(SELECT GROUP_CONCAT(CONCAT(position_apply) SEPARATOR ',')
                FROM candidate_pos
                WHERE candidate_id = candidates.id
                ) AS positions"),
        ]);

        return $collection;
    }

    /**
     * get interested candidates list
     * @param int $type
     * @param bool$isScopeTeam
     * @return mixed
     */
    public function getInterestedList($type, $isScopeTeam)
    {
        $isTabBirthday = $type === getOptions::TYPE_BIRTHDAY_CANDIDATE_LIST;
        $filter = Form::getFilterData();
        $order = $isTabBirthday ? 'birthday_now' : 'candidates.interested';
        $dir = $isTabBirthday ? 'ASC' : 'DESC';
        $pager = Config::getPagerData(null, ['order' => $order, 'dir' => $dir]);
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];
        if (!$isScopeTeam) {
            $dataFilter['candidates.recruiter'] = Permission::getInstance()->getEmployee()->email;
        }
        $tblCandidate = Candidate::getTableName();
        $tblCddEmailMarketing = CddMailSent::getTableName();
        $collection = $this->buildCollectionInterested($dataFilter);
        $exceptFilter = $isTabBirthday ? ["{$tblCandidate}.status_update_date"] : ["{$tblCandidate}.birthday"];
        CoreModel::filterGrid($collection, $exceptFilter, null, 'LIKE');
        $collection->orderBy($pager['order'], $pager['dir']);
        if ($isTabBirthday) {
            $now = Carbon::now()->toDateString();
            $curYear = Carbon::now()->year;
            $nextYear = $curYear + 1;
            $bd = "{$tblCandidate}.birthday";
            $collection->whereRaw("CASE
                    WHEN DATE_FORMAT({$bd}, '%m-%d') <= '01-07'
                        THEN DATE_FORMAT({$bd}, '{$nextYear}-%m-%d') <= DATE_ADD('{$now}', INTERVAL 7 DAY)
                    ELSE
                        CASE
                        WHEN DATE_FORMAT({$bd}, '%m-%d') = '02-29' AND ($curYear % 4)
                            THEN DATE('{$curYear}-3-1') >= '{$now}' AND DATE('{$curYear}-3-1') <= DATE_ADD('{$now}', INTERVAL 7 DAY)
                        ELSE DATE_FORMAT({$bd}, '{$curYear}-%m-%d') >= '{$now}' AND DATE_FORMAT({$bd}, '{$curYear}-%m-%d') <= DATE_ADD('{$now}', INTERVAL 7 DAY)
                        END
                    END"
                )
                ->whereDate($bd, '<', $now)
                // select for sort by birthday
                ->addSelect(DB::raw("(CASE
                        WHEN DATE_FORMAT({$bd}, '%m-%d') <= '01-07'
                            THEN DATE_FORMAT({$bd}, '{$nextYear}-%m-%d')
                        ELSE
                            CASE
                            WHEN DATE_FORMAT({$bd}, '%m-%d') = '02-29' AND ($curYear % 4)
                                THEN DATE('{$curYear}-3-1')
                            ELSE DATE_FORMAT({$bd}, '{$curYear}-%m-%d')
                            END
                        END) AS birthday_now")
                );
            // get send mail CMSN in 7 days ago
            $collection->leftJoin($tblCddEmailMarketing, function ($q) use ($tblCandidate, $tblCddEmailMarketing) {
                    $q->on("{$tblCandidate}.id", '=', "{$tblCddEmailMarketing}.candidate_id")
                        ->where("{$tblCddEmailMarketing}.type", '=', CddMailSent::TYPE_MAIL_BIRTHDAY)
                        ->where(DB::raw("DATE({$tblCddEmailMarketing}.sent_date)"), '>=', Carbon::now()->subDays(7)->toDateString());
                })
                ->addSelect(DB::raw("DATE({$tblCddEmailMarketing}.sent_date) AS max_sent_date"));
            if ($filterMailStatus = (int)Form::getFilterData('except', 'candidates.mail_status')) {
                if ($filterMailStatus === CddMailSent::STATUS_CMSN) {
                    $collection->whereNotNull("{$tblCddEmailMarketing}.sent_date");
                } else {
                    $collection->whereNull("{$tblCddEmailMarketing}.sent_date");
                }
            }
        } else {
            $typeMarketing = CddMailSent::TYPE_MAIL_MARKETING;
            $typeInterested = CddMailSent::TYPE_MAIL_INTERESTED;
            $collection->leftJoin($tblCddEmailMarketing, function ($q) use ($tblCandidate, $tblCddEmailMarketing) {
                    $q->on("{$tblCandidate}.id", '=', "{$tblCddEmailMarketing}.candidate_id");
                })
                ->addSelect([
                    DB::raw("DATE(MAX({$tblCddEmailMarketing}.sent_date)) AS max_sent_date"),
                    DB::raw("(SELECT GROUP_CONCAT(CONCAT(IF({$tblCddEmailMarketing}.request_id IS NULL, {$typeInterested}, {$typeMarketing})) SEPARATOR ',')
                            FROM {$tblCddEmailMarketing}
                            WHERE {$tblCddEmailMarketing}.candidate_id = {$tblCandidate}.id
                        ) AS mail_type"),
                ]);
            $filterSentDate = Form::getFilterData('except', 'candidates.max_sent_date');
            $filterMailType = Form::getFilterData('except', 'candidates.mail_type');
            if ($filterSentDate || $filterMailType) {
                $sql = $collection->toSql();
                foreach ($collection->getBindings() as $binding) {
                    $value = is_numeric($binding) ? $binding : "'" . $binding . "'";
                    $sql = preg_replace('/\?/', $value, $sql, 1);
                }
                $collection = DB::table(DB::raw("($sql) AS candidateAS"));
            }
            if ($filterSentDate) {
                $collection->where('max_sent_date', '=', $filterSentDate);
            }
            if ($filterMailType) {
                $collection->where('mail_type', 'like', "%{$filterMailType}%");
            }
        }
        $collection->orderBy('id', 'ASC');
        CoreModel::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }

    /**
     * Get list recommend candidate by employees
     * @return mixed
     */
    public static function getListRecommend()
    {
        $curEmp = Permission::getInstance()->getEmployee();
        $pager = Config::getPagerData(null, ['order' => 'candidates.id', 'dir' => 'desc']);

        $collection = self::where('found_by', $curEmp->id);

        $collection->select(
            'candidates.id',
            'candidates.fullname',
            'candidates.email',
            'candidates.mobile',
            'candidates.university',
            'candidates.certificate',
            'candidates.status',
            'candidates.recruiter',
            'candidates.experience',
            'candidates.offer_result',
            'candidates.interview_result',
            'candidates.test_result',
            'candidates.contact_result',
            DB::raw("(SELECT GROUP_CONCAT(concat( name ) SEPARATOR ', ')
                FROM programming_languages
                        inner join candidate_programming on programming_languages.id = candidate_programming.programming_id
                        where candidate_programming.candidate_id = candidates.id
                ) AS programs_name")
        );
        $collection->whereRaw("candidates.id IN (SELECT MAX(id) FROM candidates WHERE deleted_at IS NULL AND found_by = {$curEmp->id} GROUP BY email)");
        $filterProgram = Form::getFilterData('except', 'candidate_programming.programming_id');
        $filterStatus = Form::getFilterData('except', 'candidates.status');
        $filterRecruiter = Form::getFilterData('except', 'candidates.recruiter');

        if (!empty($filterProgram)) {
            $collection->Join('candidate_programming', 'candidates.id', '=', 'candidate_programming.candidate_id')
                ->where('candidate_programming.programming_id', $filterProgram);
        }
        if ($filterStatus) {
            self::filterStatus($collection, $filterStatus);
        }
        if ($filterRecruiter) {
            $collection->where('candidates.recruiter', $filterRecruiter);
        }
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('candidates.id', 'desc');
        }

        $collection->groupBy('candidates.email');
        self::filterGrid($collection, [], null, 'LIKE');
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }

    public static function checkExistRecommend($email, $id)
    {
        $arrayIds = [];
        $listFailStatus = getOptions::getFailOrLeaveOptions();

        //Find child
        if ($id) {
            $candidate = Candidate::find($id);
            $arrayIds = [$candidate->id];
            if ($candidate->parent_id) {
                $arrayIds[] = $candidate->parent_id;
            }
        }

        $response = self::where('email', $email)
            ->whereNotIn('id', function ($query) use ($arrayIds) {
                $query->select('id')
                    ->from(self::getTableName())
                    ->whereIn('parent_id', $arrayIds);
            })
            ->WhereNotIn('status', $listFailStatus);
        if ($id) {
            $response->Where('id', '!=', $id);
        }

        return $response->count();
    }

    /**
     * List language with level
     *
     * @param Languages left join LanguageLevel collection $langs
     * @return array
     */
    public static function langWithLevel($langs)
    {
        $langArray = [];
        foreach ($langs as $item) {
            if ($item->language_level) {
                $arrayLevel = explode(CoreModel::GROUP_CONCAT, $item->language_level);
                if (is_array($arrayLevel)) {
                    foreach ($arrayLevel as $level) {
                        $level = explode(CoreModel::CONCAT, $level);
                        if (is_array($level)) {
                            $langId = $level[0];
                            $langName = $level[1];
                            $langArray[$item->id][$langId] = $langName;
                        }
                    }
                }
            }
        }
        return $langArray;
    }

    /*
     * get list interested candidate for validate
     */
    public function getInterestedListByIds($candidateIds, $type)
    {
        if (empty($candidateIds)) {
            return [];
        }
        $tblCandidate = Candidate::getTableName();
        $collection = Candidate::select([
                "{$tblCandidate}.id",
                "{$tblCandidate}.fullname",
                "{$tblCandidate}.email",
            ])
            // candidate fail with interested
            ->whereIn("{$tblCandidate}.status", [getOptions::FAIL, getOptions::FAIL_CDD])
            ->whereIn("{$tblCandidate}.interested", [getOptions::INTERESTED_LESS, getOptions::INTERESTED_NORMAL, getOptions::INTERESTED_SPECIAL])
            // query by params
            ->whereIn("{$tblCandidate}.id", $candidateIds);
        $objPermission = Permission::getInstance();
        $route = 'resource::candidate.interested';
        if (!$objPermission->isScopeCompany(null, $route) && !$objPermission->isScopeTeam(null, $route)) {
            $collection->where("{$tblCandidate}.recruiter", $objPermission->getEmployee()->email);
        }
        if ($type === CddMailSent::TYPE_MAIL_FOLLOW) {
            return $collection->get();
        }

        $tblCddEmailMarketing = CddMailSent::getTableName();
        $now = Carbon::now()->toDateString();
        $curYear = Carbon::now()->year;
        $nextYear = $curYear + 1;
        $bd = "{$tblCandidate}.birthday";
        return $collection->whereRaw("CASE
                WHEN DATE_FORMAT({$bd}, '%m-%d') <= '01-07'
                    THEN DATE_FORMAT({$bd}, '{$nextYear}-%m-%d') <= DATE_ADD('{$now}', INTERVAL 7 DAY)
                ELSE
                    CASE
                    WHEN DATE_FORMAT({$bd}, '%m-%d') = '02-29' AND ($curYear % 4)
                        THEN DATE('{$curYear}-3-1') >= '{$now}' AND DATE('{$curYear}-3-1') <= DATE_ADD('{$now}', INTERVAL 7 DAY)
                    ELSE DATE_FORMAT({$bd}, '{$curYear}-%m-%d') >= '{$now}' AND DATE_FORMAT({$bd}, '{$curYear}-%m-%d') <= DATE_ADD('{$now}', INTERVAL 7 DAY)
                    END
                END"
            )
            ->whereDate($bd, '<', $now)
            // query check sent mail in 7 days ago
            ->leftJoin($tblCddEmailMarketing, function ($q) use ($tblCandidate, $tblCddEmailMarketing) {
                $q->on("{$tblCandidate}.id", '=', "{$tblCddEmailMarketing}.candidate_id")
                    ->where("{$tblCddEmailMarketing}.type", '=', CddMailSent::TYPE_MAIL_BIRTHDAY)
                    ->where(DB::raw("DATE({$tblCddEmailMarketing}.sent_date)"), '>=', Carbon::now()->subDays(7)->toDateString());
            })
            ->whereNull("{$tblCddEmailMarketing}.sent_date")
            ->groupBy("{$tblCandidate}.id")
            ->get();
    }

    public static function findByEmail($email)
    {
        return self::where('email', $email)->first();
    }
}
