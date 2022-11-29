<?php

namespace Rikkei\Resource\View;

use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Resource\Model\Programs;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\View\CheckpointPermission;
use Rikkei\Core\View\Form;
use DB;
use Rikkei\Resource\Model\CandidateLanguages;
use Rikkei\Resource\Model\CandidateProgramming;
use Rikkei\Team\Model\Team;
use Illuminate\Support\Facades\Schema;
use Rikkei\Test\Models\Type as TestType;
use Rikkei\Resource\Model\ResourceRequest;
use Lang;
use Rikkei\Core\View\View as CoreView;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\View\getOptions;

class CandidatePermission
{
    
    /**
     * store this object
     * @var object
     */
    protected static $instance;

    /**
     * Singleton instance
     *
     * @return \Rikkei\Team\View\CheckpointPermission
     */
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     * Get Candidate list by permission
     *
     * @param string $order
     * @param string $dir
     *
     * @return Candidate collection
     */
    public function getList($order = null, $dir = null, $listCandidate = true)
    {
        $emp = Permission::getInstance()->getEmployee();
        $model = new Candidate();
        $filter = Form::getFilterData(null, null, route('resource::candidate.list'). '/');
        $dataFilter = isset($filter['except']) ? $filter['except'] : [];

        if (Permission::getInstance()->isScopeCompany()) {
            $list = $model->getList($order, $dir, null, null, $dataFilter, !$listCandidate);
        } elseif ($teamIds = Permission::getInstance()->isScopeTeam()) {
            $list = $model->getList($order, $dir, $emp->id, $teamIds, $dataFilter, !$listCandidate);
        } else {
            $list = $model->getList($order, $dir, $emp->id, null, $dataFilter, !$listCandidate);
        }

        return $list;
    }

    /**
     * Check permission candidate detail page
     *
     * @param Candidate $candidate
     * @param Employee $curEmp
     *
     * @return boolean
     */
    public static function detailPermission($candidate, $curEmp)
    {
        //Scope company
        if (Permission::getInstance()->isScopeCompany()) {
            return true;
        }

        //Scope team
        if ($teamIds= Permission::getInstance()->isScopeTeam()) {
            $teamOfCandi = Candidate::getTeams($candidate->id);
            foreach ($teamOfCandi as $team) {
                if (in_array($team->id, $teamIds)) {
                    return true;
                }
            }
            if (self::getInstance()->detailScopeSelf($candidate, $curEmp)) {
                return true;
            }
        }

        //Scope self
        return self::getInstance()->detailScopeSelf($candidate, $curEmp);
    }

    /**
     * Check has scope self in candidate detail page
     *
     * @param Candidate $candidate
     * @param Employee $curEmp
     * @return boolean
     */
    public function detailScopeSelf($candidate, $curEmp)
    {
        if ($candidate->created_by == $curEmp->id 
        || $candidate->recruiter == $curEmp->email
        || $candidate->found_by == $curEmp->id) {
            return true;
        }
        if ($candidate->interviewer) {
            $arrInterviewer = explode(',', $candidate->interviewer);
            if (in_array($curEmp->id, $arrInterviewer)) {
                return true;
            }
        }
        $requestOfCandi = Candidate::getRequests($candidate->id);
        if (count($requestOfCandi)) {
            foreach ($requestOfCandi as $request) {
                if ($request->interviewer) {
                    $arrInterviewerRequest = explode(',', $request->interviewer);
                    if (is_array($arrInterviewerRequest) && in_array($curEmp->id, $arrInterviewerRequest)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Check edit candidate permission
     * @param Candidate $candidate
     * @param Employee $curEmp
     * @return boolean
     */
    public static function editPermission($candidate, $curEmp)
    {
        if (Permission::getInstance()->isScopeCompany()) {
            return true;
        }
        if (Permission::getInstance()->isScopeTeam() || Permission::getInstance()->isScopeSelf()) {
            if ($candidate->created_by == $curEmp->id || $candidate->recruiter == $curEmp->email) {
                return true;
            }
        }
        $isMemberHr = Team::isMemberHr($curEmp->id);
        if ($isMemberHr && $candidate->status == getOptions::DRAFT) {
            return true;
        }
        return false;
    }

    /**
     * Check in candidate detail page
     * Check candidate status to decide whether to show or not tab test
     *
     * @param Candidate $candidate
     */
    public static function isShowTabTest($candidate)
    {
        return static::isShowTabInterview($candidate)
                || ($candidate->status == getOptions::FAIL && $candidate->contact_result == getOptions::RESULT_PASS);
    }

    /**
     * Check in candidate detail page
     * Check candidate status to decide whether to show or not tab interview
     *
     * @param Candidate $candidate
     */
    public static function isShowTabInterview($candidate)
    {
        return static::isShowTabOffer($candidate)
                || ($candidate->status == getOptions::ENTRY_TEST
                    || $candidate->status == getOptions::INTERVIEWING
                    || ($candidate->status == getOptions::FAIL &&
                            ($candidate->test_result == getOptions::RESULT_PASS
                                || ($candidate->contact_result == getOptions::RESULT_PASS && $candidate->test_result == getOptions::RESULT_DEFAULT)
                            )
                        )
                );
    }

    /**
     * Check in candidate detail page
     * Check candidate status to decide whether to show or not tab offer
     *
     * @param Candidate $candidate
     */
    public static function isShowTabOffer($candidate)
    {
        return $candidate->status == getOptions::OFFERING
                || $candidate->status == getOptions::END
                || $candidate->status == getOptions::WORKING
                || $candidate->status == getOptions::PREPARING
                || $candidate->status == getOptions::FAIL_CDD
                || $candidate->status == getOptions::LEAVED_OFF
                || ($candidate->status == getOptions::FAIL
                        && $candidate->offer_result != null
                        && $candidate->offer_result != getOptions::RESULT_DEFAULT);
    }

    public function exceptColumnsCopy()
    {
        return [
            'request_id', 'position_apply', 'team_id', 'received_cv_date',
            'test_plan', 'test_date', 'test_result', 'test_note', 'test_mark',
            'interview_plan', 'interview_date', 'interview_result', 'interview_note',
            'offer_date', 'offer_salary', 'offer_result', 'offer_feedback_date',
            'offer_note', 'contact_result', 'interviewer', 'note', 'start_working_date',
            'trial_work_end_date', 'interview_calling_date', 'interview_email_date',
            'screening', 'interview2_plan', 'interview2_date', 'test_mark_specialize',
            'offer_start_date', 'test_option_gmat', 'employee_id', 'test_option_type_ids',
            'position_apply_input', 'channel_input', 'offer_salary_input', 'working_type',
            'contract_length', 'type_candidate', 'employee_card_id', 'calendar_id', 'event_id'
        ];
    }

    /**
     * Clone the candidate into a new, non-existing instance.
     *
     * @param Candidate $candidate
     * @param  array|null  $except
     * @return Candidate
     */
    public function copyCandidate($candidate, array $except = null)
    {
        DB::beginTransaction();
        try {
            $newCandidate = $candidate->replicate($except);
            $newCandidate->status = getOptions::CONTACTING;
            $newCandidate->received_cv_date = date('Y-m-d');
            $newCandidate->request_asset_id = null;
            if ($candidate->parent_id) {
                $newCandidate->parent_id = $candidate->parent_id;
            } else {
                $newCandidate->parent_id = $candidate->id;
            }
            $newCandidate->save();

            //insert language
            $listLang = CandidateLanguages::getListByCandidate($candidate->id)->toArray();
            if ($listLang && count($listLang)) {
                foreach ($listLang as &$lang) {
                    unset($lang['id']);
                    $lang['candidate_id'] = $newCandidate->id;
                }
            }
            CandidateLanguages::insert($listLang);

            //insert language
            $listProgram = CandidateProgramming::getListByCandidate($candidate->id)->toArray();
            if ($listProgram && count($listProgram)) {
                foreach ($listProgram as &$program) {
                    unset($program['id']);
                    $program['candidate_id'] = $newCandidate->id;
                }
            }
            CandidateProgramming::insert($listProgram);

            DB::commit();
            return $newCandidate;
        } catch (Exception $ex) {
            DB::rollback();
            Log::info($ex->getMessage());
        }
    }

    public static function reApplyPermission($candidate, $curEmp)
    {
        $leaderHr = static::getLeaderHr();
        return ($leaderHr && $leaderHr->id == $curEmp->id)
                || $candidate->created_by == $curEmp->id
                || $candidate->recruiter == $curEmp->email;
    
    }

    /**
     * Get leader of team HR
     * @return type
     */
    public static function getLeaderHr()
    {
        $teamHr = Team::getTeamByType(Team::TEAM_TYPE_HR);
        $leaderHr = null;
        if ($teamHr) {
            $leaderHr = $teamHr->getLeader();
        }
        return $leaderHr;
    }

    /**
     * Send mail to recruiter and interviewer when candidate changed from last time
     * Check only form candidate detail
     *
     * @param Candidate $candidate
     * @param array $dataInput
     * @return void
     */
    public static function sendMailWhenCandidateChange($candidate, $dataInput)
    {
        //get persons receive mail
        $receives = self::getRelatedInterview($candidate);

        //Send mail
        if (count($receives)) {
            $statusEmpUpdateable = getOptions::statusEmpUpdateable();
            unset($statusEmpUpdateable[array_search(getOptions::END, $statusEmpUpdateable)]);
            if (isset($dataInput['status']) && !in_array($dataInput['status'], $statusEmpUpdateable)
                    && in_array($candidate->status, $statusEmpUpdateable)) {
                unset($dataInput['status']);
            }
            if (isset($dataInput['working_type'])) {
                $dataInput['working_type'] = $dataInput['working_type'] != '' ? $dataInput['working_type'] : 0;
            }
            if (isset($dataInput['programming_language_id'])) {
                $dataInput['programming_language_id'] = $dataInput['programming_language_id'] == 0 ? '' : $dataInput['programming_language_id'];
            }
            $changes = static::findEditChange($candidate, $dataInput);
            if (isset($changes['programming_language_id'])) {
                $language = new Programs();
                if ($changes['programming_language_id']['new']) {
                    $changes['programming_language_id']['new'] = $language->getNamesByIds($changes['programming_language_id']['new'])[0];
                }
            }
            if (count($changes)) {
                $fieldsChanged = [];
                foreach ($changes as $field => $value) {
                    $fieldsChanged[] = Lang::get('resource::view.' . $field);
                }
                foreach ($receives as $receive) {
                    $emailQueue = new \Rikkei\Core\Model\EmailQueue();
                    $emailQueue->setTo($receive['email'])
                        ->setFrom(Config('mail.username'), Config('mail.name'))
                        ->setSubject(Lang::get('resource::view.【Rikkeisoft】 The candidate :name has just been updated information: :info',
                            ['name' => $candidate->fullname, 'info' => implode(', ', $fieldsChanged)]))
                        ->setTemplate('resource::candidate.mail.changed', [
                            'name' => CoreView::getNickName($receive['email']), 
                            'candidateName' => $candidate->fullname,
                            'urlToCandidate' => route('resource::candidate.detail', $candidate->id),
                            'changes' => $changes,
                        ])
                        ->setNotify(
                            $receive['id'],
                            Lang::get('resource::view.Information of candidate :name has just been updated', ['name' => $candidate->fullname]),
                            route('resource::candidate.detail', $candidate->id), ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                        );
                    $emailQueue->save();
                }
            }
        }
    }

    /*
     * get employee related interview
     */
    public static function getRelatedInterview($candidate, $excerpt = [])
    {
        $list = [];
        if ($excerpt && !is_array($excerpt)) {
            $excerpt = [$excerpt];
        }
        if ($candidate->recruiter) {
            $recruiter = Employee::select('id', 'email', 'name')
                    ->where('email', $candidate->recruiter)
                    ->first();
            if ($recruiter && !in_array($recruiter->id, $excerpt)) {
                $list[] = $recruiter->toArray();
            }
            $excerpt[] = $recruiter['id'];
        }//dd($list);
            return $list;
    }

    /**
     * Find fields of candidate changed from last time
     * Check only form candidate detail
     *
     * @param Candidate $candidate
     * @param array $data
     * @return array
     */
    public static function findEditChange($candidate, $data)
    {
        $changes = [];
        $fieldsIgnore = [
            'contact_result',
            'test_result',
            'interview_result',
            'offer_result',
            'calendar_id',
            'event_id',
            'had_worked',
            'employee',
            'employee_id',
        ];
        $colCandidateTbl = $candidate->getFillable();      
        $colCandidateTbl[] = 'employee_code'; //add column employee_code to send mail
        if (is_array($data)) {
            foreach ($data as $field => $value) {
                if (in_array($field, $fieldsIgnore)) {
                    continue;
                }
                if (in_array($field, $colCandidateTbl) && $candidate->$field != $value) {
                    if ($field == 'interviewer') {
                        $arrayField = explode(',', $candidate->$field);
                        if (!count(array_diff($arrayField, $value)) && !count(array_diff($value, $arrayField))) {
                            continue;
                        }
                    }
                    if ($candidate->$field == '0000-00-00 00:00:00') {
                        if ($value == '') {
                            continue;
                        }
                        $candidate->$field = '';
                    }
                    if (is_string($value)
                        && (strtotime($value) && strtotime($value) == strtotime($candidate->$field))
                    ) {
                        continue;
                    }
                    $changes[$field] = [
                        'old' => static::getChanges($field, $candidate->$field),
                        'new' => static::getChanges($field, $value),
                    ];
                }
            }
        }
        return $changes;
    }

    /**
     * Convert old value, new value of candidate to show
     *
     * @param string|array $field
     * @param string|array $value
     * @return string
     */
    public static function getChanges($field, $value)
    {
        switch ($field) {
            case 'status':
                return getOptions::getInstance()->getCandidateStatus($value);
            case 'test_option_type_ids':
                if (is_array($value)) {
                    return implode(', ', TestType::getListNameByIds($value));
                } else {
                    return '';
                }
            case 'working_type':
                return getOptions::getContractTypeByType($value);
            case 'request_id':
                $resourceRequest = ResourceRequest::find($value);
                if ($resourceRequest) {
                    return $resourceRequest->title;
                }
                return '';
            case 'team_id':
                $team = Team::getTeamById($value);
                if ($team) {
                    return $team->name;
                }
                return '';
            case 'position_apply':
                return getOptions::getInstance()->getRole($value);
            case 'interviewer':
                if (!is_array($value)) {
                    $value = explode(',', $value);
                }
                $interviewersEmail = Employee::whereIn('id', $value)->lists('email')->toArray();
                return implode(', ', $interviewersEmail);
            case 'interested':
                $interestedOptions = getOptions::listInterestedOptions();
                if (isset($interestedOptions[$value])) {
                    return $interestedOptions[$value]['label'];
                }
                return '';
            default:
                return $value;
        }
    }

    /**
     * Check candidate status fail
     *
     * @param int $status status of candidate
     *
     * @return boolean
     */
    public static function isCandidateFail($status)
    {
        return in_array($status, [
            getOptions::FAIL,
            getoptions::FAIL_CONTACT,
            getOptions::FAIL_TEST,
            getOptions::FAIL_INTERVIEW,
            getOptions::FAIL_OFFER,
        ]);
    }

    /*
     * check candidate can Re-Apply by status
     */
    public static function canReApply($status)
    {
        return in_array($status, [
            getOptions::FAIL,
            getoptions::FAIL_CONTACT,
            getOptions::FAIL_TEST,
            getOptions::FAIL_INTERVIEW,
            getOptions::FAIL_OFFER,
            getOptions::FAIL_CDD,
            getOptions::LEAVED_OFF
        ]);
    }
}
