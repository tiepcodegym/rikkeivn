<?php

namespace Rikkei\Api\Helper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\Languages;
use Rikkei\Resource\Model\Programs;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\View\getOptions;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

/**
 * Description of Hrm Recruitment
 *
 * @author duydv
 */
class HrmRecruitment extends HrmBase
{

    /**
     * STT Pass CV
     */
    const PASS_CV = 11;
    /**
     * STT Fail CV
     */
    const FAIL_CV = 10;
    /**
     * STT Pass INTERVIEW
     */
    const PASS_INTERVIEW = 21;
    /**
     * STT Fail INTERVIEW
     */
    const FAIL_INTERVIEW = 20;
    /**
     * STT Pass OFFER
     */
    const PASS_OFFER = 31;
    /**
     * STT Fail OFFER
     */
    const FAIL_OFFER = 30;
    /**
     *  STT Probation time
     */
    const PROBATION_TIME = 41;
    /**
     * STT Internship
     */
    const INTERNSHIP_TIME = 42;
    /**
     * STT Fail Probation/Internship
     */
    const FAIL_PROBATION_INTERNSHIP = 40;
    /**
     *  STT Official Employee
     */
    const OFFICIAL_EMPLOYEE = 51;
    /**
     * STT Fail Official Employee
     */
    const FAIL_OFFICIAL_EMPLOYEE = 50;

    /**
     * Get Candidate Status
     *
     * @return array
     */
    public function getStatus() {
        return [
            self::OFFICIAL_EMPLOYEE => 'Vào chính thức',
            self::FAIL_OFFICIAL_EMPLOYEE => 'Fail chính thức',
            self::PROBATION_TIME => 'Đến thử việc',
            self::INTERNSHIP_TIME => 'Đến thực tập',
            self::FAIL_PROBATION_INTERNSHIP => 'Fail đến thực tập/thử việc',
            self::PASS_OFFER => 'Chốt Offer',
            self::FAIL_OFFER => 'Fail Offer',
            self::PASS_INTERVIEW => 'Pass phỏng vấn',
            self::FAIL_INTERVIEW => 'Fail phỏng vấn',
            self::PASS_CV => 'Pass Cv',
            self::FAIL_CV => 'Fail Cv',
        ];
    }


    /**
     * Generate Query In statement
     *
     * @param $arrayValues
     * @return string
     */
    protected function _generateQueryIn($arrayValues)
    {
        return '(' . implode(',', $arrayValues) . ')';
    }


    /**
     * Generate condition Query status base on candidate Status
     *
     * @param $type
     * @return string|null
     */
    protected function _generateQueryStatusCondition($type)
    {
        $candidateTbl = Candidate::getTableName();

        $query = null;
        switch ($type){
            case self::PASS_CV:
                $statusIn = $this->_generateQueryIn([getOptions::CONTACTING, getOptions::ENTRY_TEST, getOptions::INTERVIEWING]);

                $query = "{$candidateTbl}.status IN {$statusIn}";
                break;
            case self::FAIL_CV:
                $status = getOptions::RESULT_FAIL;

                $query = "{$candidateTbl}.contact_result = {$status}";
                break;
            case self::PASS_INTERVIEW:
                $statusIn = $this->_generateQueryIn([getOptions::OFFERING]);

                $query = "{$candidateTbl}.status IN {$statusIn}";
                break;
            case self::FAIL_INTERVIEW:
                $status = getOptions::RESULT_FAIL;

                $query = "{$candidateTbl}.interview_result = {$status} OR {$candidateTbl}.test_result = {$status}";
                break;
            case self::PASS_OFFER:
                $statusIn = $this->_generateQueryIn([getOptions::PREPARING, getOptions::END]);

                $query = "{$candidateTbl}.status IN {$statusIn}";
                break;
            case self::FAIL_OFFER:
                $status = getOptions::RESULT_FAIL;

                $query = "{$candidateTbl}.offer_result  = {$status}";
                break;
            case self::PROBATION_TIME:
                $statusIn = $this->_generateQueryIn([getOptions::WORKING]);
                $workingType = getOptions::WORKING_PROBATION;

                $query = "{$candidateTbl}.status IN {$statusIn} AND {$candidateTbl}.working_type = {$workingType}";
                break;
            case self::INTERNSHIP_TIME:
                $statusIn = $this->_generateQueryIn([getOptions::WORKING]);
                $workingType = getOptions::WORKING_INTERNSHIP;

                $query = "{$candidateTbl}.status IN {$statusIn} AND {$candidateTbl}.working_type = {$workingType}";
                break;
            case self::FAIL_PROBATION_INTERNSHIP:
                $statusIn = $this->_generateQueryIn([getOptions::FAIL_CDD, getOptions::LEAVED_OFF]);
                $workingTypeIn = $this->_generateQueryIn([getOptions::WORKING_PROBATION, getOptions::WORKING_INTERNSHIP]);

                $query = "{$candidateTbl}.status IN {$statusIn} AND {$candidateTbl}.working_type IN {$workingTypeIn}";
                break;
            case self::OFFICIAL_EMPLOYEE:
                $statusIn = $this->_generateQueryIn([getOptions::WORKING]);
                $workingTypeIn = $this->_generateQueryIn([getOptions::WORKING_OFFICIAL, getOptions::WORKING_UNLIMIT]);

                $query = "{$candidateTbl}.status IN {$statusIn} AND {$candidateTbl}.working_type IN {$workingTypeIn}";
                break;
            case self::FAIL_OFFICIAL_EMPLOYEE:
                $statusIn = $this->_generateQueryIn([getOptions::FAIL_CDD, getOptions::LEAVED_OFF]);
                $workingTypeIn = $this->_generateQueryIn([getOptions::WORKING_OFFICIAL, getOptions::WORKING_UNLIMIT]);

                $query = "{$candidateTbl}.status IN {$statusIn} AND {$candidateTbl}.working_type IN {$workingTypeIn}";
                break;
        }

        return $query;
    }
}
