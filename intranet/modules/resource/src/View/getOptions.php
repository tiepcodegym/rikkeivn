<?php

namespace Rikkei\Resource\View;

use Lang;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\EnrollmentAdvice;
use Rikkei\Core\View\Form;
use Illuminate\Pagination\LengthAwarePaginator;
use Rikkei\Team\Model\Employee;

class getOptions {
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    const ROLE_DEV = 1;
    const ROLE_SQA = 2;
    const ROLE_PQA = 3;
    const ROLE_TEAM_LEADER = 4;
    const ROLE_BRSE = 5;
    const ROLE_SUB_BRSE = 51;
    const ROLE_COMTOR = 6;
    const ROLE_PM = 10;
    const ROLE_COO = 11;
    const ROLE_QALEAD = 12;
    const ROLE_RECRUITER = 13;
    const ROLE_SALER = 14;
    const ROLE_ACCOUNTANT = 15;
    const ROLE_FRESHER = 16;
    const ROLE_TEACHER = 17;
    const ROLE_IT = 18;
    const ROLE_PR = 19;
    const ROLE_HCTH = 20;
    const ROLE_TECH_LEADER = 21;
    const ROLE_BA = 22;
    const ROLE_DESIGNER = 23;
    const ROLE_BPO = 24;
    const ROLE_MARKETING = 25;
    const ROLE_SAP = 26;
    const ROLE_DBA = 27;
    const ROLE_SQL_SERVER = 28;
    const ROLE_CYBER_SECURITY = 29;
    const ROLE_WEB_HOSTING = 30;
    const ROLE_DEVOPS = 31;
    const ROLE_SALESFORCE = 32;
    const ROLE_DEV_FRESHER = 101;
    const ROLE_DEV_JUNIOR = 102;
    const ROLE_DEV_SENIOR = 103;
    
    //effort fulltime or parttime
    const EFFORT_FULL = 1;
    const EFFORT_PART = 2;
    
    //onsite on or off
    const ONSITE_OFF = 1;
    const ONSITE_ON = 2;
    
    //status of request
    const STATUS_INPROGRESS = 1;
    const STATUS_CLOSE = 2;
    const STATUS_CANCEL = 3;
    
    // status of approve
    const APPROVE_YET = 3;
    const APPROVE_OFF = 1;
    const APPROVE_ON = 2;

    //status of publish
    const STATUS_PUBLISH = 1;
    const STATUS_DISPUBLISH = 0;

    //Type of approve
    const TYPE_UTILIZE_RESOURCE = 1;
    const TYPE_RECRUIT = 2;
    
    const RESULT_DEFAULT = 0;
    const RESULT_PASS = 1;
    const RESULT_FAIL = 2;
    const RESULT_WORKING = 3;
    
    // Status of candidate
    const CONTACTING = 1;
    const ENTRY_TEST = 2;
    const INTERVIEWING = 3;
    const OFFERING = 4;
    const END = 5;
    const FAIL = 6;
    const FAIL_CONTACT = 61;
    const FAIL_TEST = 62;
    const FAIL_INTERVIEW = 63;
    const FAIL_OFFER = 64;
    const SELF_ELECTED = 7;
    const WORKING = 8;
    const PASS_OTHER_REQUEST = 9;
    const DRAFT = 10;
    const PREPARING = 11;
    const FAIL_CDD = 12; //candidate fail in tab employee information
    const LEAVED_OFF = 70;
    
    //type of working
    const WORKING_PROBATION = 1;
    const WORKING_INTERNSHIP = 2;
    const WORKING_PARTTIME = 3;
    const WORKING_OFFICIAL = 4;
    const WORKING_UNLIMIT = 5;
    const WORKING_BORROW = 6;

    /*
     * use in resource utilization
     */
    const DASHBOARD_EFFORT_GRAY = 1;
    const DASHBOARD_EFFORT_YELLOW = 2;
    const DASHBOARD_EFFORT_GREEN = 3;
    const DASHBOARD_EFFORT_RED = 4;

    //receive push notification firebase
    const NOT_RECEIVE_PUSH = 1;


    const ROUTE_MORE_INFO = 'resource::candidate.detail.more_infor';

    /*
     * candidate type
     */
    const CDD_FRESHER = 1;
    const CDD_JUNIOR = 2;
    const CDD_SENIOR = 3;
    const CDD_MIDDLE = 4;

    // max device token

    const MAX_TOKEN = 1000;

    //
    const DATE_NOW = 1;
    const DATE_CUSTOM = 2;

    // screen type in follow candidate
    const TYPE_REMIND_SEND_MAIL_OFFER = 'send-mail';
    // screen type in interested candidate
    const TYPE_BIRTHDAY_CANDIDATE_LIST = 'birthday';

    const INTERESTED_NOT = 0; // không quan tâm
    const INTERESTED_LESS = 1; // ít quan tâm
    const INTERESTED_NORMAL = 2; // quan tâm
    const INTERESTED_SPECIAL = 3; // quan tâm đặc biệt

    const PER_PAGE = 50; // paginator recruitment monthly report

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

    public static function typeEmployeeOfficial()
    {
        return [
            self::WORKING_OFFICIAL,
            self::WORKING_UNLIMIT,
            self::WORKING_PROBATION,
        ];
    }

    public static function getEffortPeriodOptions()
    {
        return [
            self::DASHBOARD_EFFORT_GRAY => 'effort = 0%',
            self::DASHBOARD_EFFORT_YELLOW => '0% < effort <= 70%',
            self::DASHBOARD_EFFORT_GREEN => '70% < effort <= 120%',
            self::DASHBOARD_EFFORT_RED => 'effort > 120%',
        ];
    }

    public function getRoles($sepDev = false)
    {
        $results = [
            self::ROLE_TECH_LEADER => Lang::get('resource::view.Tech Leader'),
        ];
        if (!$sepDev) {
            $results += [
                self::ROLE_DEV => Lang::get('resource::view.Dev')
            ];
        } else {
            $results += [
                self::ROLE_DEV_FRESHER => Lang::get('resource::view.Dev - Fresher'),
                self::ROLE_DEV_JUNIOR => Lang::get('resource::view.Dev - Junior'),
                self::ROLE_DEV_SENIOR => Lang::get('resource::view.Dev - Senior'),
            ];
        }
        $results += [
            self::ROLE_SQA => Lang::get('resource::view.SQA'),
            self::ROLE_PQA => Lang::get('resource::view.PQA'),
            self::ROLE_TEAM_LEADER => Lang::get('resource::view.Team leader'),
            self::ROLE_BRSE => Lang::get('resource::view.BrSE'),
            self::ROLE_SUB_BRSE => Lang::get('resource::view.Sub-BrSE'),
            self::ROLE_COMTOR => Lang::get('resource::view.Comtor'),
            self::ROLE_PM => Lang::get('resource::view.PM'),
            self::ROLE_COO => Lang::get('resource::view.COO'),
            self::ROLE_RECRUITER => Lang::get('resource::view.Recruiter'),
            self::ROLE_SALER => Lang::get('resource::view.Saler'),
            self::ROLE_ACCOUNTANT => Lang::get('resource::view.Accountant')
        ];
        if (!$sepDev) {
            $results += [
                self::ROLE_FRESHER => Lang::get('resource::view.Fresher')
            ];
        }
        $results += [
            self::ROLE_TEACHER => Lang::get('resource::view.Teacher'),
            self::ROLE_IT => Lang::get('resource::view.IT'),
            self::ROLE_PR => Lang::get('resource::view.PR'),
            self::ROLE_HCTH => Lang::get('resource::view.HC - TH'),
            self::ROLE_BA => Lang::get('resource::view.BA'),
            self::ROLE_DESIGNER => Lang::get('resource::view.Designer'),
            self::ROLE_BPO => Lang::get('resource::view.BPO'),
            self::ROLE_MARKETING => Lang::get('resource::view.Marketing'),
            self::ROLE_SALER => Lang::get('resource::view.Marketing'),
            self::ROLE_SAP => Lang::get('resource::view.SAP'),
            self::ROLE_DBA => Lang::get('resource::view.DBA'),
            self::ROLE_SQL_SERVER => Lang::get('resource::view.SQL Server'),
            self::ROLE_CYBER_SECURITY => Lang::get('resource::view.Cyber Security'),
            self::ROLE_WEB_HOSTING => Lang::get('resource::view.Web hosting'),
            self::ROLE_DEVOPS => Lang::get('resource::view.Devops'),
            self::ROLE_SALESFORCE => Lang::get('resource::view.Salesforce'),
        ];
        return $results;
    }

    /**
     * get label of type member
     * 
     * @param int $key
     * @return array
     */
    public function getRole($role)
    {
        switch ($role) {
            case self::ROLE_DEV: return Lang::get('resource::view.Dev');
            case self::ROLE_SQA: return Lang::get('resource::view.SQA');
            case self::ROLE_PQA: return Lang::get('resource::view.PQA');
            case self::ROLE_PM: return Lang::get('resource::view.PM');
            case self::ROLE_TEAM_LEADER: return Lang::get('resource::view.Team leader');
            case self::ROLE_BRSE: return Lang::get('resource::view.BrSE');
            case self::ROLE_COMTOR: return Lang::get('resource::view.Comtor');
            case self::ROLE_COO: return Lang::get('resource::view.COO');
            case self::ROLE_RECRUITER: return Lang::get('resource::view.Recruiter');
            case self::ROLE_SALER: return Lang::get('resource::view.Saler');
            case self::ROLE_ACCOUNTANT: return Lang::get('resource::view.Accountant');
            case self::ROLE_FRESHER: return Lang::get('resource::view.Fresher');
            case self::ROLE_TEACHER: return Lang::get('resource::view.Teacher');
            case self::ROLE_IT: return Lang::get('resource::view.IT');
            case self::ROLE_PR: return Lang::get('resource::view.PR');
            case self::ROLE_HCTH: return Lang::get('resource::view.HC - TH');
            case self::ROLE_SUB_BRSE: return Lang::get('resource::view.Sub-BrSE');
            case self::ROLE_TECH_LEADER: return Lang::get('resource::view.Tech Leader');
            case self::ROLE_BA: return Lang::get('resource::view.BA');
            case self::ROLE_DESIGNER: return Lang::get('resource::view.Designer');
            case self::ROLE_BPO: return Lang::get('resource::view.BPO');
            case self::ROLE_MARKETING: return Lang::get('resource::view.Marketing');
            case self::ROLE_SAP: return Lang::get('resource::view.SAP');
            case self::ROLE_DBA: return Lang::get('resource::view.DBA');
            case self::ROLE_SQL_SERVER: return Lang::get('resource::view.SQL Server');
            case self::ROLE_CYBER_SECURITY: return Lang::get('resource::view.Cyber Security');
            case self::ROLE_WEB_HOSTING: return Lang::get('resource::view.Web hosting');
            case self::ROLE_DEVOPS: return Lang::get('resource::view.Devops');
            case self::ROLE_SALESFORCE: return Lang::get('resource::view.Salesforce');
            default: return '';
        }
    }

    public static function getAllRoles()
    {
        return [
            self::ROLE_DEV => Lang::get('resource::view.Dev'),
            self::ROLE_SQA => Lang::get('resource::view.SQA'),
            self::ROLE_PQA => Lang::get('resource::view.PQA'),
            self::ROLE_PM => Lang::get('resource::view.PM'),
            self::ROLE_TEAM_LEADER => Lang::get('resource::view.Team leader'),
            self::ROLE_BRSE => Lang::get('resource::view.BrSE'),
            self::ROLE_COMTOR => Lang::get('resource::view.Comtor'),
            self::ROLE_COO => Lang::get('resource::view.COO'),
            self::ROLE_RECRUITER => Lang::get('project::view.Sub-PM'),
            self::ROLE_SALER => Lang::get('resource::view.Saler'),
            self::ROLE_IT => Lang::get('resource::view.IT'),
            self::ROLE_HCTH => Lang::get('resource::view.HC - TH'),
            self::ROLE_SUB_BRSE => Lang::get('resource::view.Sub-BrSE'),
            self::ROLE_TECH_LEADER => Lang::get('resource::view.Tech Leader'),
            self::ROLE_BA => Lang::get('resource::view.BA'),
            self::ROLE_DESIGNER => Lang::get('resource::view.Designer'),
            self::ROLE_BPO => Lang::get('resource::view.BPO'),
        ];
    }
    
    public function getEffort() {
        return [
            [
                'id' => self::EFFORT_FULL,
                'name' => Lang::get('resource::view.Request.Create.Fulltime')
            ],
            [
                'id' => self::EFFORT_PART,
                'name' => Lang::get('resource::view.Request.Create.Parttime')
            ]
        ];
    }

    public function getEffortByKey($effort) {
        switch ($effort) {
            case self::EFFORT_FULL: 
                return Lang::get('resource::view.Request.Create.Fulltime');
            case self::EFFORT_PART: 
                return Lang::get('resource::view.Request.Create.Parttime');
        }
    }
    
    /**
     * Get gender by key
     * 
     * @param int $key
     * @return string
     */
    public static function getGender($key) {
        switch ($key) {
            case Candidate::GENDER_MALE: 
                return Lang::get('resource::view.Male');
            case Candidate::GENDER_FEMALE: 
                return Lang::get('resource::view.Female');
            default:
                return '';
        }
    }
    
    public function getOnsiteOption() {
        return [
            [
                'id' => self::ONSITE_OFF,
                'name' => Lang::get('resource::view.Request.Create.No')
            ],
            [
                'id' => self::ONSITE_ON,
                'name' => Lang::get('resource::view.Request.Create.Yes')
            ]
        ];
    }
    
    public function getOnsiteByKey($onsite) {
        switch ($onsite) {
            case self::ONSITE_OFF: 
                return Lang::get('resource::view.Request.Create.No');
            case self::ONSITE_ON: 
                return Lang::get('resource::view.Request.Create.Yes');
        }
    }
    
    public function getStatusOption() {
        return [
            [
                'id' => self::STATUS_INPROGRESS,
                'name' => Lang::get('resource::view.Request.Create.Inprogress')
            ],
            [
                'id' => self::STATUS_CANCEL,
                'name' => Lang::get('resource::view.Request.Create.Cancel')
            ]
        ];
    }
    
    public function getStatusApproveOption() {
        return [
            [
                'id' => self::STATUS_INPROGRESS,
                'name' => Lang::get('resource::view.Request.Create.Inprogress')
            ],
            [
                'id' => self::STATUS_CANCEL,
                'name' => Lang::get('resource::view.Request.Create.Cancel')
            ],
            [
                'id' => self::STATUS_CLOSE,
                'name' => Lang::get('resource::view.Request.Create.Close')
            ]
        ];
    }
    
    public function getApproveOption() {
        return [
            [
                'id' => self::APPROVE_YET,
                'name' => Lang::get('resource::view.Request.List.Approve yet')
            ],
            [
                'id' => self::APPROVE_OFF,
                'name' => Lang::get('resource::view.Request.List.Disapprove')
            ],
            [
                'id' => self::APPROVE_ON,
                'name' => Lang::get('resource::view.Request.List.Approve')
            ]
        ];
    }
    
    public function getTypeOption() {
        return [
            [
                'id' => self::TYPE_RECRUIT,
                'name' => Lang::get('resource::view.Request.Create.Recruit')
            ],
            [
                'id' => self::TYPE_UTILIZE_RESOURCE,
                'name' => Lang::get('resource::view.Request.Create.Utilize resource')
            ]
        ];
    }
    
    public function getTypeByKey($type) {
        switch ($type) {
            case self::TYPE_UTILIZE_RESOURCE: 
                return Lang::get('resource::view.Request.Create.Utilize resource');
            case self::TYPE_RECRUIT: 
                return Lang::get('resource::view.Request.Create.Recruit');
        }
    }
    
    public function getResultOption() {
        return [
            [
                'id' => self::RESULT_FAIL,
                'name' => Lang::get('resource::view.Candidate.Detail.Fail')
            ],
            [
                'id' => self::RESULT_PASS,
                'name' => Lang::get('resource::view.Candidate.Detail.Pass')
            ]
        ];
    }
    
    public function getCandidateStatusOptionsAll($candidate = null) {
        $array = [
            [
                'id' => self::CONTACTING,
                'name' => Lang::get('resource::view.Candidate.Detail.Contacting')
            ],
            [
                'id' => self::ENTRY_TEST,
                'name' => Lang::get('resource::view.Candidate.Detail.Entry test')
            ],
            [
                'id' => self::INTERVIEWING,
                'name' => Lang::get('resource::view.Candidate.Detail.Interviewing')
            ],
            [
                'id' => self::OFFERING,
                'name' => Lang::get('resource::view.Candidate.Detail.Offering')
            ],
            [
                'id' => self::END,
                'name' => Lang::get('resource::view.Candidate.Detail.End')
            ],
            [
                'id' => self::PREPARING,
                'name' => Lang::get('resource::view.Candidate.Detail.Preparing')
            ],
            [
                'id' => self::WORKING,
                'name' => Lang::get('resource::view.Candidate.Detail.Working')
            ],
            [
                'id' => self::FAIL,
                'name' => Lang::get('resource::view.Candidate.Detail.Fail')
            ],
            [
                'id' => self::FAIL_CONTACT,
                'name' => Lang::get('resource::view.Contact fail'),
            ],
            [
                'id' => self::FAIL_TEST,
                'name' => Lang::get('resource::view.Test fail'),
            ],
            [
                'id' => self::FAIL_INTERVIEW,
                'name' => Lang::get('resource::view.Interview fail'),
            ],
            [
                'id' => self::FAIL_OFFER,
                'name' => Lang::get('resource::view.Offer fail'),
            ],
            [
                'id' => self::DRAFT,
                'name' => Lang::get('resource::view.Candidate.Detail.Draft')
            ],
            [
                'id' => self::LEAVED_OFF,
                'name' => Lang::get('resource::view.Candidate.Detail.Leaved off')
            ]
        ];  
        
        return $array;
    }


    public function getCandidateResultOptions($candidate) {
        $array[] = [
                'id' => self::CONTACTING,
                'name' => Lang::get('resource::view.Candidate.Detail.Contacting')
            ];
        if ($candidate->status == getOptions::ENTRY_TEST 
            || $candidate->status == getOptions::INTERVIEWING 
            || $candidate->status == getOptions::OFFERING 
            || $candidate->status == getOptions::END
            || $candidate->status == getOptions::WORKING
            || $candidate->contact_result == self::RESULT_PASS) {
            $array[] = [
                'id' => self::ENTRY_TEST,
                'name' => Lang::get('resource::view.Candidate.Detail.Entry test')
            ];
        }
        if ($candidate->status == getOptions::INTERVIEWING 
            || $candidate->status == getOptions::OFFERING 
            || $candidate->status == getOptions::END
            || $candidate->status == getOptions::WORKING
            || $candidate->contact_result == self::RESULT_PASS     
            || $candidate->test_result == self::RESULT_PASS) {
            $array[] = [
                'id' => self::INTERVIEWING,
                'name' => Lang::get('resource::view.Candidate.Detail.Interviewing')
            ];
        }
        if ($candidate->status == getOptions::OFFERING 
            || $candidate->status == getOptions::END
            || $candidate->status == getOptions::WORKING
            || $candidate->interview_result == self::RESULT_PASS) {
            $array[] = [
                'id' => self::OFFERING,
                'name' => Lang::get('resource::view.Candidate.Detail.Offering')
            ];
        }
        if ($candidate->status == getOptions::END
            || $candidate->status == getOptions::WORKING
            || $candidate->status == getOptions::OFFERING
            && $candidate->offer_result == self::RESULT_PASS) {
            $array[] = [
                'id' => self::END,
                'name' => Lang::get('resource::view.Candidate.Detail.End')
            ];
        }
        if ($candidate->status == getOptions::WORKING
            || $candidate->status == getOptions::END
            && $candidate->offer_result == self::RESULT_WORKING) {
            $array[] = [
                'id' => self::WORKING,
                'name' => Lang::get('resource::view.Candidate.Detail.Working')
            ];
        }
        if ($candidate->status == self::LEAVED_OFF) {
            $array[] = [
                'id' => self::LEAVED_OFF,
                'name' => Lang::get('resource::view.Candidate.Detail.Leaved off')
            ];
        }
        if ($candidate->status == self::PREPARING) {
            $array[] = [
                'id' => self::PREPARING,
                'name' => Lang::get('resource::view.Candidate.Detail.Preparing')
            ];
        }
        $array[] = [
                'id' => self::FAIL,
                'name' => Lang::get('resource::view.Candidate.Detail.Fail')
            ];
        $array[] = [
                'id' => self::FAIL_CONTACT,
                'name' => Lang::get('resource::view.Contact fail')
            ];
        $array[] = [
                'id' => self::FAIL_TEST,
                'name' => Lang::get('resource::view.Test fail')
            ];
        $array[] = [
                'id' => self::FAIL_INTERVIEW,
                'name' => Lang::get('resource::view.Interview fail')
            ];
        $array[] = [
                'id' => self::FAIL_OFFER,
                'name' => Lang::get('resource::view.Offer fail')
            ];
        $array[] = [
            'id' => self::LEAVED_OFF,
            'name' => Lang::get('resource::view.Candidate.Detail.Leaved off')
        ];
        $array[] = [
            'id' => self::FAIL_CDD,
            'name' => trans('resource::view.Candidate.Detail.Fail')
        ];
        return $array;
    }

    public function getStatus($status) {
        switch ($status) {
            case self::STATUS_INPROGRESS: 
                return Lang::get('resource::view.Request.Create.Inprogress');
            case self::STATUS_CLOSE: 
                return Lang::get('resource::view.Request.Create.Close');
            case self::STATUS_CANCEL: 
                return Lang::get('resource::view.Request.Create.Cancel');
            default: 
                return Lang::get('resource::view.Request.Create.Inprogress');
        }
    }
    
    public function getApprove($approve) {
        switch ($approve) {
            case self::APPROVE_YET: 
                return Lang::get('resource::view.Request.List.Approve yet');
            case self::APPROVE_OFF: 
                return Lang::get('resource::view.Request.List.Disapprove');
            case self::APPROVE_ON: 
                return Lang::get('resource::view.Request.List.Approve');
            default: 
                return Lang::get('resource::view.Request.List.Approve yet');
        }
    }

    /**
     *
     * @param string $result
     * @return string
     */
    public function getResult($result) {
        switch ($result) {
            case self::RESULT_DEFAULT: 
                return Lang::get('resource::view.Candidate.Detail.Not choose');
            case self::RESULT_FAIL: 
                return Lang::get('resource::view.Candidate.Detail.Fail');
            case self::RESULT_PASS: 
                return Lang::get('resource::view.Candidate.Detail.Pass');
            case self::RESULT_WORKING:
                return Lang::get('resource::view.Candidate.Detail.Working');
            default: 
                return Lang::get('resource::view.Candidate.Detail.Fail');
        }
    }
    
    public function getCandidateStatus($status, $candidate = null) {
        switch ($status) {
            case self::CONTACTING:
                return Lang::get('resource::view.Candidate.Detail.Contacting');
            case self::ENTRY_TEST:
                return Lang::get('resource::view.Candidate.Detail.Entry test');
            case self::INTERVIEWING:
                return Lang::get('resource::view.Candidate.Detail.Interviewing');
            case self::OFFERING:
                return Lang::get('resource::view.Candidate.Detail.Offering');
            case self::END:
                return Lang::get('resource::view.Candidate.Detail.End');
            case self::FAIL:
                if ($candidate) {
                    return $this->getCandidateStatusFail($candidate);
                } else {
                    return Lang::get('resource::view.Candidate.Detail.Fail');
                }
            case self::WORKING:
                return Lang::get('resource::view.Candidate.Detail.Working');
            case self::PASS_OTHER_REQUEST:
                return Lang::get('resource::view.Fail (Pass other request)');
            case self::DRAFT:
                return Lang::get('resource::view.Draft');
            case self::PREPARING:
                return Lang::get('resource::view.Candidate.Detail.Preparing');
            case self::FAIL_CDD:
                return Lang::get('resource::view.Candidate.Detail.Fail');
            case self::LEAVED_OFF:
                return Lang::get('resource::view.Candidate.Detail.Leaved off');
            default: 
                return Lang::get('resource::view.Candidate.Detail.Contacting');
        }
    }

    public function getCandidateStatusFail($candidate)
    {
        if ($candidate->offer_result == getOptions::RESULT_FAIL) {
            return Lang::get('resource::view.Offer fail');
        } else if ($candidate->interview_result == getOptions::RESULT_FAIL) {
            return Lang::get('resource::view.Interview fail');
        } else if ($candidate->test_result == getOptions::RESULT_FAIL) {
            return Lang::get('resource::view.Test fail');
        } else if ($candidate->contact_result == getOptions::RESULT_FAIL) {
            return Lang::get('resource::view.Contact fail');
        } else {
            return Lang::get('resource::view.Candidate.Detail.Fail');
        }
    }

    public function getSelectedCandidateStatus($candidate)
    {
        if ($candidate->status == self::FAIL) {
            if ($candidate->offer_result == getOptions::RESULT_FAIL) {
                return self::FAIL_OFFER;
            } else if ($candidate->interview_result == getOptions::RESULT_FAIL) {
                return self::FAIL_INTERVIEW;
            } else if ($candidate->test_result == getOptions::RESULT_FAIL) {
                return self::FAIL_TEST;
            } else if ($candidate->contact_result == getOptions::RESULT_FAIL) {
                return self::FAIL_CONTACT;
            } else {
                return self::FAIL;
            }
        } else {
            return $candidate->status;
        }
    }

    public static function getClassRequestStatus($status) {
        switch ($status) {
            case self::STATUS_CANCEL: return ResourceRequest::CLASS_CANCEL;
            case self::STATUS_INPROGRESS: return ResourceRequest::CLASS_INPROGRESS;
            case self::STATUS_CLOSE: return ResourceRequest::CLASS_CLOSE;
        }
    }

    public static function getClassCandidateStatus($status) {
        switch ($status) {
            case self::CONTACTING: return Candidate::CLASS_CONTACTING;
            case self::ENTRY_TEST: return Candidate::CLASS_ENTRY_TEST;
            case self::INTERVIEWING: return Candidate::CLASS_INTERVIEWING;
            case self::OFFERING: return Candidate::CLASS_OFFERING;
            case self::END: return Candidate::CLASS_END;
            case self::FAIL:
            case self::LEAVED_OFF:
            case self::FAIL_CDD: return Candidate::CLASS_FAIL;
            case self::WORKING: return Candidate::CLASS_WORKING;
            case self::PREPARING: return Candidate::CLASS_PREPARING;
            default: return Candidate::CLASS_DEFAULT;
        }
    }
    
    /**
     * get list of all working type
     * @return array list of working type
     */
    public function getWorkingType(){
        return [
            [
                'id' => self::WORKING_INTERNSHIP,
                'name' => Lang::get('resource::view.Internship')
            ],
            [
                'id' => self::WORKING_PARTTIME,
                'name' => Lang::get('resource::view.Parttime')
            ],
            [
                'id' => self::WORKING_PROBATION,
                'name' => Lang::get('resource::view.Trainee')
            ],
            [
                'id' => self::WORKING_OFFICIAL,
                'name' => Lang::get('resource::view.Official')
            ],
            [
                'id' => self::WORKING_UNLIMIT,
                'name' => Lang::get('resource::view.Unlimit time')
            ],
            [
                'id' => self::WORKING_BORROW,
                'name' => Lang::get('resource::view.Borrow')
            ]
        ];
    }
    
    /**
     * Get contract type by type
     * 
     * @param int $type
     * @return string
     */
    public static function getContractTypeByType($type)
    {
        switch ($type) {
            case self::WORKING_PROBATION:
                return Lang::get('resource::view.Trainee');
            case self::WORKING_INTERNSHIP:
                return Lang::get('resource::view.Internship');
            case self::WORKING_PARTTIME:
                return Lang::get('resource::view.Parttime');
            case self::WORKING_OFFICIAL:
                return Lang::get('resource::view.Official');
            case self::WORKING_UNLIMIT:
                return Lang::get('resource::view.Unlimit time');
            case self::WORKING_BORROW:
                return Lang::Get('resource::view.Borrow');
            default: 
                return Lang::get('resource::view.No contract');
        }
    }

    /**
     * get label of working_type
     * @param int $id working_type static value
     * @return String label of Working Type
     */
    public static function getWorkingTypeLabel($id){
        $list = self::getInstance()->getWorkingType();
        foreach ($list as $ele){
            if($ele['id'] == $id){
                return $ele['name'];
            }
        }
        return '';
    }

    /*
     * list employee statues
     */
    public static function listEmployeeStatus()
    {
        return [
            self::FAIL_CDD => trans('resource::view.Candidate.Detail.Fail'),
            self::PREPARING => trans('resource::view.Candidate.Detail.Preparing'),
            self::WORKING => trans('resource::view.Candidate.Detail.Working')
        ];
    }

    /*
     * get status that can update employee
     */
    public static function statusEmpUpdateable()
    {
        return [self::END, self::PREPARING, self::FAIL_CDD, self::WORKING, self::LEAVED_OFF];
    }

    public static function statusWorkingOrEndOrLeave()
    {
        return [self::END, self::WORKING, self::LEAVED_OFF];
    }

    /*
     * get list working type internal
     */
    public static function listWorkingTypeInternal($locale = null)
    {
        return [
            self::WORKING_PROBATION => Lang::get('resource::view.Trainee', [], $locale),
            self::WORKING_OFFICIAL => Lang::get('resource::view.Official', [], $locale),
            self::WORKING_UNLIMIT => Lang::get('resource::view.Unlimit time', [], $locale),
        ];
    }
    /*
     * get array working type internal
     */
    public static function workingTypeInternal()
    {
        return array_keys(self::listWorkingTypeInternal());
    }

    /*
     * get list working type external
     */
    public static function listWorkingTypeExternal()
    {
        return [
            self::WORKING_INTERNSHIP => Lang::get('resource::view.Internship'),
            self::WORKING_PARTTIME => Lang::get('resource::view.Parttime'),
            self::WORKING_BORROW => Lang::get('resource::view.Borrow')
        ];
    }

    /*
     * get array working type external
     */
    public static function workingTypeExternal()
    {
        return array_keys(self::listWorkingTypeExternal());
    }

    /*
     * working type official, unlimited
     */
    public static function workingTypeOfficial()
    {
        return [
            self::WORKING_OFFICIAL,
            self::WORKING_UNLIMIT
        ];
    }

    /*
     * sql switch case by array
     */
    public static function selectCase($col, $list)
    {
        $sql = 'CASE ';
        foreach ($list as $key => $label) {
            $sql .= 'WHEN ' . $col . ' = "' . $key . '" THEN "' . $label .'" ';
        }
        return $sql .' END';
    }

    /*
     * list all status
     */
    public static function getAllStatues()
    {
        $allStatuses = self::getInstance()->getCandidateStatusOptionsAll();
        $results = [];
        foreach ($allStatuses as $status) {
            $results[$status['id']] = $status['name'];
        }
        $results[self::FAIL_CDD] = trans('resource::view.Candidate.Detail.Fail');
        return $results;
    }

    /*
     * list all resule
     */
    public static function listResults()
    {
        return [
            self::RESULT_DEFAULT => Lang::get('resource::view.Candidate.Detail.Not choose'),
            self::RESULT_FAIL => Lang::get('resource::view.Candidate.Detail.Fail'),
            self::RESULT_PASS => Lang::get('resource::view.Candidate.Detail.Pass'),
            self::RESULT_WORKING => Lang::get('resource::view.Candidate.Detail.Working')
        ];
    }

    /*
     * list gender
     */
    public static function listGender()
    {
        return [
            Candidate::GENDER_MALE => Lang::get('resource::view.Male'),
            Candidate::GENDER_FEMALE => Lang::get('resource::view.Female')
        ];
    }

    /*
     * extra employee code prefix base on working type
     */
    public static function extraEmpPrefix()
    {
        return [
            self::WORKING_INTERNSHIP => 'TTS_',
            self::WORKING_PARTTIME => 'TTS_',
            self::WORKING_BORROW => 'Partner_'
        ];
    }

    public function getStatusEnrollmentAdvice() {
        return [
            [
                'id' => EnrollmentAdvice::STATE_CLOSE,
                'name' => Lang::get('resource::view.Enrollment Advice.List.Close')
            ],
            [
                'id' => EnrollmentAdvice::STATE_OPEN,
                'name' => Lang::get('resource::view.Enrollment Advice.List.Open')
            ],
        ];
    }

    /**
     * get program name or position from group concat string
     * @param strinng $strIds
     * @param array $programs
     * @param array $roles
     * @return type
     */
    public function getProgOrPosName($strIds, $programs, $roles)
    {
        if (!$strIds) {
            return null;
        }
        $aryIds = explode(',', $strIds);
        $results = [];
        foreach ($aryIds as $id) {
            if (is_numeric($id)) {
                if (isset($programs[$id])) {
                    $results[] = '<span>' . e($programs[$id]) . '</span>';
                }
            } else {
                $pId = explode('_', $id);
                if (count($pId) == 2) {
                    $id = $pId[1];
                    if (isset($roles[$id])) {
                        $results[] = '<span>' . e($roles[$id]) . '</span>';
                    }
                }
            }
        }
        return implode(', ', $results);
    }

    public function listArrayResults()
    {
        return [
            self::RESULT_FAIL => Lang::get('resource::view.Candidate.Detail.Fail'),
            self::RESULT_PASS => Lang::get('resource::view.Candidate.Detail.Pass')
        ];
    }

    /*
     * get dev type options
     */
    public function getDevTypeOptions()
    {
        return [
            self::CDD_FRESHER => Lang::get('resource::view.Fresher'),
            self::CDD_JUNIOR => Lang::get('resource::view.Junior'),
            self::CDD_MIDDLE => Lang::get('resource::view.Middle'),
            self::CDD_SENIOR => Lang::get('resource::view.Senior')
        ];
    }

    /*
     * get status options is fail or leave
     */
    public static function getFailOrLeaveOptions()
    {
        return [
            self::FAIL,
            self::FAIL_CONTACT,
            self::FAIL_TEST,
            self::FAIL_INTERVIEW,
            self::FAIL_OFFER,
            self::FAIL_CDD,
            self::LEAVED_OFF
        ];
    }

    public static function listWorkedMonth()
    {
        return [
            6 => [
                'from' => 0,
                'to' => 6,
                'title' => '0 - < 6 ' . trans('resource::view.month')
            ],
            12 => [
                'from' => 6,
                'to' => 12,
                'title' => '6 '. trans('resource::view.month') .' - 1 ' . trans('resource::view.year')
            ],
            36 => [
                'from' => 12,
                'to' => 36,
                'title' => '1 - 3 ' . trans('resource::view.year')
            ],
            1000 => [
                'from' => 36,
                'to' => 1000,
                'title' => '> 3 ' . trans('resource::view.year')
            ],
        ];
    }

    /*
     * list interested status
     */
    public static function listInterestedOptions()
    {
        return [
            self::INTERESTED_NOT => [
                'label' => Lang::get('resource::view.Candidate.Create.Not interested'),
                'class' => 'interested-not',
            ],
            self::INTERESTED_LESS => [
                'label' => Lang::get('resource::view.Candidate.Create.Less interested'),
                'class' => 'interested-less',
            ],
            self::INTERESTED_NORMAL => [
                'label' => Lang::get('resource::view.Candidate.Create.Interested'),
                'class' => 'interested-normal',
            ],
            self::INTERESTED_SPECIAL => [
                'label' => Lang::get('resource::view.Candidate.Create.Special interested'),
                'class' => 'interested-special',
            ],
        ];
    }

    /**
     * list fail candidate status (contact, test, interview, offer)
     */
    public function listFailCandidateStatus()
    {
        return [
            [
                'id' => self::FAIL_CONTACT,
                'name' => Lang::get('resource::view.Contact fail'),
            ],
            [
                'id' => self::FAIL_TEST,
                'name' => Lang::get('resource::view.Test fail'),
            ],
            [
                'id' => self::FAIL_INTERVIEW,
                'name' => Lang::get('resource::view.Interview fail'),
            ],
            [
                'id' => self::FAIL_OFFER,
                'name' => Lang::get('resource::view.Offer fail'),
            ],
            [
                'id' => self::FAIL_CDD,
                'name' => Lang::get('resource::view.Candidate.Detail.Fail'),
            ],
        ];
    }

    /*
     * list not fail candidate status
     */
    public function cddGeneralStatuses()
    {
        return [
            self::CONTACTING => Lang::get('resource::view.Candidate.Detail.Contacting'),
            self::ENTRY_TEST => Lang::get('resource::view.Candidate.Detail.Entry test'),
            self::INTERVIEWING => Lang::get('resource::view.Candidate.Detail.Interviewing'),
            self::OFFERING => Lang::get('resource::view.Candidate.Detail.Offering'),
            self::END => Lang::get('resource::view.Candidate.Detail.End'),
            self::WORKING => Lang::get('resource::view.Candidate.Detail.Working'),
            self::PASS_OTHER_REQUEST => Lang::get('resource::view.Fail (Pass other request)'),
            self::DRAFT => Lang::get('resource::view.Draft'),
            self::PREPARING => Lang::get('resource::view.Candidate.Detail.Preparing'),
            self::FAIL_CDD => Lang::get('resource::view.Candidate.Detail.Fail'),
            self::LEAVED_OFF => Lang::get('resource::view.Candidate.Detail.Leaved off')
        ];
    }

    /*
     * list fail status each step
     */
    public function cddFailStepStatuses()
    {
        return [
            self::FAIL_CONTACT => Lang::get('resource::view.Contact fail'),
            self::FAIL_TEST => Lang::get('resource::view.Test fail'),
            self::FAIL_INTERVIEW => Lang::get('resource::view.Interview fail'),
            self::FAIL_OFFER => Lang::get('resource::view.Offer fail'),
            self::FAIL_CDD => Lang::get('resource::view.Candidate.Detail.Fail'),
        ];
    }

    /**
     * processing data before render into view
     *
     * @param $collectionChannel
     * @param $collectionRecruiters
     * @param $month
     * @param bool $isExport
     * @return array
     */
    public function beforeRenderViewMonthlyReport($collectionChannel, &$collectionRecruiters, $month, $isExport = false)
    {
        $recruiters = [];
        foreach ($collectionRecruiters as $recruiter) {
            $recruiters[$recruiter->email] = $recruiter->email;
        }
        $dataReport = [];
        // filter channel
        foreach ($collectionChannel as $item) {
            if ($item->recruiter === '') {
                continue;
            }
            if (!isset($recruiters[$item->recruiter])) {
                $recruiters[$item->recruiter] = $item->recruiter;
                $objEmployee = new Employee();
                $objEmployee->name = $item->recruiter;
                $objEmployee->email = $item->recruiter;
                $collectionRecruiters->push($objEmployee);
            }
            if (!isset($dataReport[$item->channel_id])) {
                $channel = [
                    'id' => $item->channel_id,
                    'name' => $item->name,
                    'color' => $item->color,
                    'recruiters' => [],
                    'total' => 0,
                    'pass' => 0,
                    'fail' => 0,
                    'offer' => 0,
                ];
            } else {
                $channel = $dataReport[$item->channel_id];
            }

            if (!isset($channel['recruiters'][$item->recruiter])) {
                $channel['recruiters'][$item->recruiter] = [
                    'total' => 1,
                    'fail' => 0,
                    'pass' => 0,
                    'offer' => 0,
                ];
            } else {
                $channel['recruiters'][$item->recruiter]['total']++;
            }

            // interview fail
            if ((int)$item['interview_result'] === self::RESULT_FAIL && $item['interview_month'] === $month) {
                $channel['recruiters'][$item->recruiter]['fail']++;
                $channel['fail']++;
            }
            // interview pass
            if ((int)$item['interview_result'] === self::RESULT_PASS && $item['interview_month'] === $month) {
                $channel['recruiters'][$item->recruiter]['pass']++;
                $channel['pass']++;
            }
            //offer pass
            if ((int)$item['offer_result'] === self::RESULT_PASS && $item['offer_month'] === $month) {
                $channel['recruiters'][$item->recruiter]['offer']++;
                $channel['offer']++;
            }
            $channel['total']++;
            $dataReport[$item->channel_id] = $channel;
        }

        if ($isExport) {
            return $dataReport;
        }

        $dataPager = Form::getFilterPagerData();
        $page = !empty($dataPager['page']) ? $dataPager['page'] : 1;
        $perPage = !empty($dataPager['limit']) ? $dataPager['limit'] : self::PER_PAGE;
        $collectionRecruiters = new LengthAwarePaginator(
            $collectionRecruiters->forPage($page, $perPage),
            $collectionRecruiters->count(),
            $perPage,
            $page,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
        return $dataReport;
    }
}
