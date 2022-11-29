<?php

namespace Rikkei\Resource\View;

use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Project\View\View as pView;
use DateTime;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\Model\RequestTeam;
use Lang;
use Rikkei\Resource\Model\Candidate;
use DateInterval;
use DatePeriod;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\Model\CandidatePosition;
use Rikkei\Core\Model\EmailQueue;
use Illuminate\Support\Facades\Log;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Team\Model\Team;
use Carbon\Carbon;

class View
{
    /**
     *  store this object
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
     * Get Month number by number
     * @param int $num
     * @return int
     */
    public function getMonthByNum($num)
    {
        if ($num > 12) {
            $num = $num - 12;
            $num = $this->getMonthByNum($num);
        }
        return $num;
    }

    public function getYearMonth($num, $date)
    {
        $year = date('Y', strtotime($date));
        $month = $this->getMonthByNum($num);
        $year = (int)$year + (int)ceil($num/12) - 1;
        return ["year" => $year, "month" => $month];
    }

    public function getWeekNumber($num)
    {
        if ($num > 52) {
            return ["week" => $num - 52, "year" => date('Y', '+1 year')];
        }
        return ["week" => $num, "year" => date('Y')];
    }

    /**
     * Get count months between 2 dates
     * @param string|date $date1
     * @param string|date $date2
     * @return int
     */
    public function getMonthDiff($date1, $date2)
    {
        $ts1 = strtotime($date1);
        $ts2 = strtotime($date2);

        $year1 = date('Y', $ts1);
        $year2 = date('Y', $ts2);

        $month1 = date('m', $ts1);
        $month2 = date('m', $ts2);

        return (($year2 - $year1) * 12) + ($month2 - $month1);
    }

    /**
     * Get first date, last date of week
     * @param int $week
     * @param int $year
     * @return array
     */
    public function getStartAndEndDate($week, $year)
    {
        $date = new DateTime();
        $date->setISODate($year, $week);
        $return[0] = $date->format('Y-m-d');
        $date->setISODate($year, $week, 7);
        $return[1] = $date->format('Y-m-d');
        return $return;
    }

    /**
     * Get effort of wekk
     * @param int $week
     * @param int $year
     * @param int $effort
     * @param datetime $startDate
     * @param datetime $endDate
     * @return float
     */
    public function getEffortOfWeek($week, $year, $effort, $startDate, $endDate, $joinDate = null)
    {
        $startEndDate = $this->getStartAndEndDate($week, $year);
        $startOfWeek = $startEndDate[0];
        $endOfWeek = $startEndDate[1];
        if (date('Y-m-d', strtotime($joinDate)) > date('Y-m-d', strtotime($endDate))) {
            return 0;
        }
        if ($joinDate && $startOfWeek < $joinDate) {
            $startOfWeek = $joinDate;
        }
        if ($joinDate && $startDate < $joinDate) {
            $startDate = $joinDate;
        }
        $actualDays = pView::getMM($startOfWeek, $endOfWeek, 2);
        $realDays = $this->getRealDaysOfWeek($week, $year, $startDate, $endDate);

        return $actualDays == 0 ? 0 : round($effort*$realDays/$actualDays, 2);
    }

    /**
     * Get real days works in week
     * @param int $week
     * @param int $year
     * @param datetime $startDate
     * @param datetime $endDate
     * @return int
     */
    public function getRealDaysOfWeek($week , $year, $startDate, $endDate)
    {
        $periodWeek = $this->getStartAndEndDate($week, $year);
        $start = $startDate;
        $end = $endDate;

        if (strtotime($startDate) > strtotime($periodWeek[1]) || strtotime($endDate) < strtotime($periodWeek[0]) ) {
            return 0;
        }
        if (strtotime($startDate) <= strtotime($periodWeek[0]) ) {
            $start = $periodWeek[0];
        }
        if (strtotime($endDate) >= strtotime($periodWeek[1]) ) {
            $end = $periodWeek[1];
        }

        $realDays = pView::getMM($start, $end, 2);

        return $realDays;
    }

    /**
     * Get first day, last day of month
     */
    public function getFirstLastDaysOfMonth($month, $year)
    {
       $first = date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
       $last = date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
       return [$first,$last];
    }

    /**
     * Get real days works in month
     * @param int $month
     * @param int $year
     * @param datetime $startDate
     * @param datetime $endDate
     * @return int
     */
    public function getRealDaysOfMonth($month , $year, $startDate, $endDate)
    {
        $periodMonth = $this->getFirstLastDaysOfMonth($month, $year);
        $start = $startDate;
        $end = $endDate;

        if (strtotime($startDate) > strtotime($periodMonth[1]) || strtotime($endDate) < strtotime($periodMonth[0])) {
            return 0;
        }

        if (strtotime($startDate) <= strtotime($periodMonth[0]) ) {
            $start = $periodMonth[0];
        }
        if (strtotime($endDate) >= strtotime($periodMonth[1]) ) {
            $end = $periodMonth[1];
        }

        $realDays = pView::getMM($start, $end, 2);

        return $realDays;
    }

    /**
     * Get effort in month
     * @param int $week
     * @param int $year
     * @param int $effort
     * @param datetime $startDate
     * @param datetime $endDate
     * @return float
     */
    public function getEffortOfMonth($month, $year, $effort, $startDate, $endDate, $joinDate = null)
    {
        $startEndDate = $this->getFirstLastDaysOfMonth($month, $year);
        $startOfMonth = $startEndDate[0];
        $endOfMonth = $startEndDate[1];
        if (date('Y-m-d', strtotime($joinDate)) > date('Y-m-d', strtotime($endDate))) {
            return 0;
        }
        if ($joinDate && $startOfMonth < $joinDate) {
            $startOfMonth = $joinDate;
        }
        if ($joinDate && $startDate < $joinDate) {
            $startDate = $joinDate;
        }

        $actualDays = pView::getMM($startOfMonth, $endOfMonth, 2);
        $realDays = $this->getRealDaysOfMonth($month, $year, $startDate, $endDate);

        return $actualDays == 0 ? 0 : round($effort*$realDays/$actualDays, 2);
    }

    /**
     * Format price
     * @param string|int|float $price
     * @return type
     */
    public function priceFormat($price)
    {
        return number_format($price, 0, '.', ',');
    }

    /**
     * Get week number by date
     * @param string|date $ddate
     * @return type
     */
    public function getWeekNumberByDate($ddate)
    {
        $date = new DateTime($ddate);
        return $date->format("W");
    }

    /**
     * Get first day of week
     * @param string $date format Y-m-d
     * @return string
     */
    public function getFirstDayOfWeek($date)
    {
        return date("Y-m-d", strtotime('monday this week', strtotime($date)));
    }

    /**
     * Get last day of week
     * @param string $date format Y-m-d
     * @return string
     */
    public function getLastDayOfWeek($date)
    {
        return date("Y-m-d", strtotime('sunday this week', strtotime($date)));
    }

    /**
     * Get weeks of month
     * @param int $start start week
     * @param int $end end week
     * @param int $year
     * @return array int
     */
    public function getWeeks($start, $end, $year)
    {
        $weeks = [];
        if ($start <= $end) {
            for ($i=$start; $i<=$end; $i++) {
                $weeks[] = [
                    'week' => (int)$i,
                    'year' => $year
                ];
            }
        } else {
            $lastWeek = $this->getIsoWeeksInYear($year);
            for ($i=$start;$i<=$lastWeek; $i++) {
                $weeks[] = [
                    'week' => (int)$i,
                    'year' => $year - 1
                ];
            }
            for ($i=1; $i<=$end; $i++) {
                $weeks[] = [
                    'week' => (int)$i,
                    'year' => $year
                ];
            }
        }

        return $weeks;
    }

    public function getIsoWeeksInYear($year)
    {
        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }

    /**
     * set variables value
     * @param int $month
     * @param date(Y-m) $startFilter
     * @param int $weeks
     * @param int $nMonth
     * @param int $nYear
     * @param int $pMonth
     * @param int $pYear
     * @param array $firstLastNextMonth
     * @param int $startWNextMonth
     * @param array $firstLastPreviousMonth
     * @param int $endWPreviousMonth
     */
    public function setVariables($month, &$weeks, &$startWNextMonth, &$endWPreviousMonth)
    {
        $firstLast = self::getInstance()->getFirstLastDaysOfMonth($month['month'], $month['year']);

        //get weeks of month
        $startW = self::getInstance()->getWeekNumberByDate($firstLast[0]);
        $endW = self::getInstance()->getWeekNumberByDate($firstLast[1]);
        $weeks = self::getInstance()->getWeeks($startW, $endW, (int)$month['year']);

        //get next month, next year
        if ($month['month'] == 12) {
            $nMonth = 1;
            $nYear = (int)$month['year'] + 1;
        } else {
            $nMonth = (int)$month['month'] + 1;
            $nYear = (int)$month['year'];
        }
        $firstLastNextMonth = self::getInstance()->getFirstLastDaysOfMonth($nMonth, $nYear);
        $startWNextMonth = self::getInstance()->getWeekNumberByDate($firstLastNextMonth[0]);

        //get pre month , pre year
        if ($month['month'] == 1) {
            $pMonth = 12;
            $pYear = (int)$month['year'] - 1;
        } else {
            $pMonth = (int)$month['month'] - 1;
            $pYear = (int)$month['year'];
        }
        $firstLastPreviousMonth = self::getInstance()->getFirstLastDaysOfMonth($pMonth, $pYear);
        $endWPreviousMonth = self::getInstance()->getWeekNumberByDate($firstLastPreviousMonth[1]);
    }

    /**
     * get days between two date
     * @param string|date $sStartDate format Y-m-d
     * @param string|date $sEndDate format Y-m-d
     * @return array days
     */
    public function getDays($sStartDate, $sEndDate)
    {
        // Firstly, format the provided dates.
        // This function works best with YYYY-MM-DD
        // but other date formats will work thanks
        // to strtotime().
        $sStartDate = date("Y-m-d", strtotime($sStartDate));
        $sEndDate = date("Y-m-d", strtotime($sEndDate));

        // Start the variable off with the start date
        $aDays[] = $sStartDate;

        // Set a 'temp' variable, sCurrentDate, with
        // the start date - before beginning the loop
        $sCurrentDate = $sStartDate;

        if ($sCurrentDate >= $sEndDate) {
            return;
        }
        // While the current date is less than the end date
        while ($sCurrentDate < $sEndDate) {
            // Add a day to the current date
            $sCurrentDate = date('Y-m-d',strtotime($sCurrentDate . "+1 days"));

            // Add this new day to the aDays array
            $aDays[] = $sCurrentDate;
        }

        // Once the loop has finished, return the
        // array of days.
        return $aDays;
    }

    /**
     * get day by number
     * @param int $number 0 - 6
     * @return string
     */
    public function getDayByNumber($number)
    {
        switch($number) {
            case 0: return "Monday";
            case 1: return "Tuesday";
            case 2: return "Wednesday";
            case 3: return "Thursday";
            case 4: return "Friday";
            case 5: return "Saturday";
            case 6: return "Sunday";
        }
    }

    /**
     * Get nickname from email
     *
     * @param string $email
     * @return string
     */
    public function getNickname($email)
    {
        return substr($email, 0, strpos($email, '@'));
    }

    public function setDefautDateFilter()
    {
        $thisMonth = (int)date('m');
        $thisYear = (int)date('Y');
        $firstLastThisMonth = self::getInstance()->getFirstLastDaysOfMonth($thisMonth, $thisYear);
        return [
            $firstLastThisMonth[0],
            $firstLastThisMonth[1],
        ];
    }

    /**
     * Check request of team self
     * @param ResourceRequest $request
     * @param array $teamIds
     * @return boolean
     */
    public static function isRequestOfTeamSelf($request, $teamIds)
    {
        if (!$request) {
            return false;
        }
        if (is_array($teamIds) && count($teamIds)) {
            $teamOfRequest = ResourceRequest::getAllTeamOfRequest($request);
            if (!is_array($teamOfRequest) || !count($teamOfRequest)) {
                return false;
            }
            foreach ($teamOfRequest as $id) {
                if (in_array($id, $teamIds)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get array team_id of request
     * @param int $requestId
     * @return array
     */
    public static function getTeamByRequest($requestId)
    {
        $requestTeam = RequestTeam::getRequestTeam($requestId);
        $teamIds = [];
        if ($requestTeam && count($requestTeam)) {
            foreach ($requestTeam as $item) {
                $teamIds[] = (int)$item->team_id;
            }
        }
        return $teamIds;
    }

    /**
     * Get array position by team of request
     * @param int $requestId
     * @param int $teamId
     * @return array
     */
    public static function getPositionByTeam($requestId, $teamId)
    {
        $requestTeamPosition = RequestTeam::getPositionByTeam($requestId, $teamId);
        $positions = [];
        if ($requestTeamPosition && count($requestTeamPosition)) {
            foreach ($requestTeamPosition as $item) {
                $positions[] = (int)$item->position_apply;
            }
        }
        return $positions;
    }

    /**
     * sub string by length
     * @param string $string
     * @param int $length
     * @return string
     */
    public static function subString($string, $length)
    {
        $stringLength = strlen($string);
        if ($stringLength > $length) {
            $string = mb_substr($string, 0, (int)$length, 'utf-8') . '...';
        }
        return $string;
    }

    /**
     * In Request detail page
     * Get bg color of count pass
     * @param boolean $checkOverload
     * @param boolean $checkFull
     * @return string
     */
    public static function getBgColor($checkOverload, $checkFull)
    {
        if ($checkOverload && $checkFull) {
            $bgPass = 'bg-purple';
        } else if (!$checkOverload && $checkFull) {
            $bgPass = 'bg-green';
        } else if ($checkOverload && !$checkFull) {
            $bgPass = 'bg-navy';
        } else {
            $bgPass = 'bg-blue';
        }
        return $bgPass;
    }

    /**
     * In Request detail page
     * Get content of modal warning
     * @param boolean $checkOverload
     * @param boolean $checkFull
     * @return string
     */
    public static function getContentWarning($checkOverload, $checkFull)
    {
        if ($checkOverload && $checkFull) {
            $content = Lang::get('resource::message.Đã đủ số lượng request. </br> Có 1 hoặc nhiều vị trí vượt số lượng request.');
        } else if (!$checkOverload && $checkFull) {
            $content = Lang::get('resource::message.Đã đủ số lượng request.');
        } else if ($checkOverload && !$checkFull) {
            $content = Lang::get('resource::message.Có 1 hoặc nhiều vị trí vượt số lượng request.');
        } else {
            $content = '';
        }
        return $content;
    }

    /**
     *
     * @param boolean $checkOverload
     * @param boolean $checkFull
     * @return boolean
     */
    public static function showModalWarning($checkOverload, $checkFull)
    {
        return $checkOverload || $checkFull;
    }

    /** Have effort in week
     * @param int $startWeek start week work
     * @param int $startYear end year work
     * @param int $endWeek end week work
     * @param int $endYear end year work
     * @param int $week week check
     * @param int $year year check
     * @param float $effort effort
     * @return boolean
     */
    public function isEffortWeek($startWeek, $startYear, $endWeek, $endYear, $week, $year, $effort)
    {
        return ((($startWeek <= $week && $startYear == $year) || $startYear < $year) && (($endWeek >= $week && $endYear == $year) || $endYear > $year)) && $effort;
    }

    /**
     * Format date
     * @param string|date $date
     * @param string $format
     * @return string
     */
    public static function getDate($date, $format)
    {
        if ($date) {
            return date($format, strtotime($date));
        }
        return '';
    }

    public static function checkArrayEmptyValue($array)
    {
        if ($array && count($array)) {
            foreach ($array as $item) {
                if (empty($item)) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * Get 12 months in year
     *
     * @param int $year
     * @return array
     */
    public static function getMonthsInYear($year = null)
    {
        $months = array();
        if (!$year) {
            $year = date('Y');
        }
        for ($i = 1; $i <= 12; $i++) {
            $date = strtotime("$year/$i/1");
            array_push($months, [(int)date('m', $date), (int)date('Y', $date)]);
        }

        return $months;
    }

    public static function getLastSixMonth()
    {
        $first  = strtotime('first day this month');
        $months = array();

        for ($i = 6; $i >= 1; $i--) {
          array_push($months, [date('m', strtotime("-$i month", $first)), date('Y', strtotime("-$i month", $first))]);
        }

        return $months;
    }

    /**
     * Get invite letter file name
     *
     * @param string $email
     * @return string|null
     */
    public static function getInviteLeterName($email)
    {
        if ($email) {
            return Candidate::FILE_NAME_INVITE . '_' . md5($email) . '.pdf';
        }
        return null;
    }

    /** Get months between two date
     *
     * @param string|date $start
     * @param string|date $end
     * @return array
     */
    public static function getMonthsBetweenDate($start, $end)
    {
        $start    = (new DateTime($start))->setTime(0, 0)->modify('first day of this month');
        $end      = (new DateTime($end))->setTime(0, 0)->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period   = new DatePeriod($start, $interval, $end);
        $months = [];
        foreach ($period as $dt) {
            $months[] = [
                'month' => $dt->format("m"),
                'year'  => $dt->format("Y")
            ];
        }
        return $months;
    }
    
    /** Get months between two date
     *
     * @param string|date $start
     * @param string|date $end
     * @return array
     */
    public static function getDateBetweenDate($start, $end)
    {
        $start    = (new DateTime($start))->setTime(0, 0)->modify('first day of this month');
        $end      = (new DateTime($end))->setTime(0, 0)->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period   = new DatePeriod($start, $interval, $end);
        $months = [];
        foreach ($period as $dt) {
            $months[] = [
                'date' => $dt->format("d"),
                'month' => $dt->format("m"),
                'year'  => $dt->format("Y")
            ];
        }
        return $months;
    }

    /**
     * Get month, year of previous month
     *
     * @param int $month
     * @param int $year
     * @return array
     */
    public static function getPreviousMonth($month, $year)
    {
        $datestring="$year-$month-01 first day of last month";
        $dt=date_create($datestring);
        return [
            'month' => $dt->format('m'),
            'year' => $dt->format('Y')
        ];
    }

    /**
     * suggest email from name
     * @param type $name
     * @return type
     */
    public static function suggestEmail($name)
    {
        return Candidate::genSuggestEmail($name);
    }

    public static function defaultSubjectMailInterviewer($candidate, $isMailTitle = true)
    {
        if ($isMailTitle) {
            $subject = Lang::get('resource::view.Invitation to interview candidate :name', ['name' => $candidate->fullname]);
        } else {
            $subject = Lang::get('resource::view.[Intranet] Candidate interview schedule :name', ['name' => $candidate->fullname]);
        }

        $positionStr = static::getPositionOfCandidate($candidate);
        if (!empty($positionStr)) {
            $subject .= ' - ' . $positionStr;
        }

        return $subject;
    }

    /**
     *
     * @param array $mailsTosend
     * @param string $mailContent
     * @param Candidate $candidate
     * @param Employee $curEmp
     * @return void
     */
    public static function sendMailToInterviewer($personsId, $candidate, $curEmp, $dataInsert)
    {
        if (is_array($personsId) && count($personsId)) {
            try {
                if (count($personsId)) {
                    $persons = Employee::getEmpByIds($personsId);
                    $subject = static::defaultSubjectMailInterviewer($candidate);

                    foreach ($persons as $person) {
                        $emailQueue = new EmailQueue();
                        $emailQueue->setTo($person->email)
                            ->setFrom($curEmp->email, $curEmp->name)
                            ->setSubject($subject)
                            ->setTemplate("resource::candidate.mail.interviewers", [
                                'urlToCandidate' => route('resource::candidate.detail', $candidate->id),
                                'candidateName' => $candidate->fullname,
                                'startDate' => $dataInsert['startDate'],
                                'endDate' => $dataInsert['endDate'],
                                'location' => $dataInsert['location'],
                                'positionOfCandidate' => View::getPositionOfCandidate($candidate),
                                'hrName' => $curEmp->name,
                                'hrPhone' => $curEmp->phone,
                                'hrEmail' => $curEmp->email,
                                'hrSkype' => $curEmp->skype,
                                'interviewerName' => \Rikkei\Core\View\View::getNickName($person->email),
                            ]);
                        $emailQueue->setNotify(
                            $person->id,
                            $subject . ' @ ' . $dataInsert['startDate'] . ' - ' . $dataInsert['endDate'] . ' (' . $dataInsert['location'] . ')',
                            route('resource::candidate.detail', $candidate->id), ['category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE]
                        );
                        $emailQueue->save();
                    }
                }
            } catch (Exception $ex) {
                Log::info($ex);
            }

        }
    }

    /**
     * get string positions of candidate
     * @param Candidate $candidate
     * @return string
     */
    public static function getPositionOfCandidate($candidate)
    {
        $positionStr = '';
        $positionsOfCandidate = CandidatePosition::getPositionIds($candidate->id);
        if (count($positionsOfCandidate)) {
            foreach ($positionsOfCandidate as $position) {
                $positionStr .= getOptions::getInstance()->getRole($position);
                if ($position == getOptions::ROLE_DEV) {
                    $programsOfCandidate = $candidate->candidateProgramming;
                    if ($programsOfCandidate) {
                        $positionStr .= '(';
                        $proString = [];
                        foreach ($programsOfCandidate as $prog) {
                            $proString[] = $prog->name;
                        }
                        $positionStr .= implode(', ', $proString);
                        $positionStr .= ')';
                    }
                }
                $positionStr .= ', ';
            }
            $positionStr = rtrim($positionStr, ', ');
        }
        return $positionStr;
    }

    /**
     * Group employee by team parent and team child
     * @param collection $employees
     * @return array
     */
    public static function groupEmployeesByTeam($employees)
    {
        $teams = [];
        $teamsOptionAll = Team::getTeamPath();
        foreach ($employees as $emp) {
            $teams[$emp->team_id][] = [
                'empEmail' => $emp->email,
                'empName' => $emp->name,
                'teamName' => $emp->team_name,
            ];
            foreach ($teamsOptionAll as $tmp => $team) {
                if (!$team) {
                    continue;
                }
                if ((isset($team['child']) && in_array($emp->team_id, $team['child']))) {
                    $teams[$tmp][] = [
                        'empEmail' => $emp->email,
                        'empName' => $emp->name,
                        'teamName' => $emp->team_name,
                    ];
                }
            }
        }
        return $teams;
    }

    public function isWeekend($date)
    {
        $weekDay = date('w', strtotime($date));
        return ($weekDay == 0 || $weekDay == 6);
    }

    public function isHoliday($date)
    {
        return in_array($date, CoreConfigData::getSpecialHolidays(2))
                    || in_array(date('m-d', strtotime($date)), CoreConfigData::getAnnualHolidays(2));
    }

    /**
     * Setting curl with webvn
     * @param array $data
     * @param string $url
     * @return mixed
     */
    public static function postData($data, $url)
    {
        $jsonData = json_encode($data);
        $curl = curl_init();
        curl_setopt_array($curl, array(
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_HEADER => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array('Content-Type:application/json', 'Content-Length: ' . strlen($jsonData)))
        );
        $result = curl_exec($curl);
        if ($errno = curl_errno($curl)) {
            throw new \Exception ('CURL error : ' . curl_strerror($errno));
        }
        curl_close($curl);
        return $result;
    }

    /*
     * get list countries
     */
    public static function getListCountries()
    {
        return \Rikkei\Team\Model\Country::getAll();
    }

    /*
     * candidate validate employee data
     */
    public static function validCandidateEmployee($candidate, $employeeData)
    {
        $cddEmpId = $employeeData['employee']['old_employee_id'] ? $employeeData['employee']['old_employee_id'] : $candidate->employee_id;
        $ruleEmployee = [];
        if (!empty($employeeData['employee']['status']) && in_array($employeeData['employee']['status'], [getOptions::PREPARING, getOptions::WORKING])) {
            $ruleEmployee = [
                'employee.email' => 'required|email|unique:employees,email'.
                    ($cddEmpId ? ',' . $cddEmpId : ',NULL') . ',id,deleted_at,NULL'
                    . '|regex:/@rikkeisoft.com$/',
                'employee.employee_card_id' => 'required|numeric',
                'employee.id_card_number' => 'required',
                'employee.id_card_date' => 'required',
                'employee.id_card_place' => 'required',
                'employee.contact.native_addr' => 'required',
            ];
        }
        $validMesses = [
            'employee.email.required' => trans('validation.required', ['attribute' => 'Email Rikkei']),
            'employee.email.email' => trans('validation.email', ['attribute' => 'Email Rikkei']),
            'employee.email.regex' => trans('validation.company_email', ['attribute' => 'Email Rikkei']),
            'employee.email.unique' => trans('validation.unique', ['attribute' => 'Email Rikkei']),
            'employee.employee_card_id.numeric' => trans('validation.numeric', ['attribute' => 'Employee card ID']),
            'employee.employee_card_id.required' => trans('validation.required', ['attribute' => 'Employee card ID']),
            'employee.id_card_number.required' => trans('validation.required', ['attribute' => 'Employee card number']),
            'employee.id_card_date.required' => trans('validation.required', ['attribute' => 'Employee card date']),
            'employee.id_card_place.required' => trans('validation.required', ['attribute' => 'Employee card place']),
            'employee.contact.native_addr.required' => trans('validation.required', ['attribute' => 'Address']),
        ];
        return \Validator::make($employeeData, $ruleEmployee, $validMesses);
    }

    /**
     * Get the reminder date for HR
     * @param DateTime $statusUpdateDate
     * @param int $day
     * @return array
     */
    public static function getReminderDateForHR($statusUpdateDate, $day = 0)
    {
        $dateAfter4Days = Carbon::parse($statusUpdateDate)->addDay($day);
        // saturday or sunday
        if (in_array($dateAfter4Days->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
            return [
                $dateAfter4Days->startOfWeek()->addWeek(1)->toDateString(), // monday
                Carbon::parse($dateAfter4Days)->addDay(1)->toDateString(), // tuesday
            ];
        }
        // friday
        if ($dateAfter4Days->dayOfWeek === Carbon::FRIDAY) {
            return [
                $dateAfter4Days->toDateString(), // friday
                Carbon::parse($dateAfter4Days)->addDay(3)->toDateString(), // monday
            ];
        }
        return [
            $dateAfter4Days->toDateString(),
            Carbon::parse($dateAfter4Days)->addDay(1)->toDateString(),
        ];
    }

    /**
     * get notify interested date for HR
     * @param string $date
     * @param int $interested
     * @return Carbon
     */
    public static function getNotifyInterestedDate(string $date, int $interested)
    {
        $months = [
            getOptions::INTERESTED_SPECIAL => 3,
            getOptions::INTERESTED_NORMAL => 5,
            getOptions::INTERESTED_LESS => 6,
        ];
        $now = Carbon::now();
        $date = Carbon::parse($date);
        $distanceMonths = $now->diffInMonths($date);
        // chưa đủ số tháng quan tâm
        if ($distanceMonths < $months[$interested]) {
            return self::addMonth($date, $months[$interested]);
        }

        for ($i = 1; $i <= $distanceMonths; $i += $months[$interested]) {
            $date = self::addMonth($date, $months[$interested]);
            // trường hợp cộng tháng không bị ảnh hưởng
            if ($date->day <= 28) {
                break;
            }
        }
        // tìm tháng mà hiệu của nó với status_update_date chia hết cho số tháng quan tâm
        $distanceMonths = $now->diffInMonths($date);
        for ($j = 0; $j < $months[$interested]; $j++) {
            if ($distanceMonths % $months[$interested] === 0) {
                return ($date->addMonth($distanceMonths)->toDateString() >= $now->subDay(2)->toDateString()) ? $date : $date->addMonth($months[$interested]); // subday 2: Saturday or sunday
            }
            $distanceMonths += 1;
        }
        return $date;
    }

    /**
     * custom add month
     * @param Carbon $date
     * @param int $n
     * @return Carbon
     */
    public static function addMonth($date, int $n)
    {
        $nMonthLater = Carbon::create($date->year, $date->month, 1)->addMonth($n);
        if ($date->day > $nMonthLater->daysInMonth) {
            return $nMonthLater->addMonth(1);
        }
        return Carbon::parse($date)->addMonth($n);
    }

    /**
     * generate list dates in period time
     * @param string $start - format YYYY-mm-dd
     * @param string $end - format YYYY-mm-dd
     * @return array
     */
    function generateDatesInPeriod($start, $end)
    {
        if (!$this->checkIsDate($start) || !$this->checkIsDate($end) || $start > $end) {
            return [];
        }
        list ($y, $m, $d) = explode('-', $start);
        $d = (int) $d;
        $aryDaysInMonth = [
            '01' => 31, '03' => 31, '05' => 31, '07' => 31, '08' => 31, '10' => 31, '12' => 31,
            '04' => 30, '06' => 30, '09' => 30, '11' => 30,
        ];
        $aryDaysInMonth['02'] = ($y % 4 === 0 && $y % 100 !== 0 || $y % 400 === 0) ? 29 : 28;
        $dateArray = [];
        while ($start <= $end) {
            $dateArray[] = $start;
            if (++$d > $aryDaysInMonth[$m]) {
                $d = 1;
                if (++$m > 12) {
                    $m = 1;
                    $y++;
                    if ($y === 10000) {
                        break;
                    }
                    $aryDaysInMonth['02'] = ($y % 4 === 0 && $y % 100 !== 0 || $y % 400 === 0) ? 29 : 28;
                    $y = sprintf('%04d', $y);
                }
                $m < 10 && $m = "0{$m}";
            }
            $start = $d < 10 ? "{$y}-{$m}-0{$d}" : "{$y}-{$m}-{$d}";
        }
        return $dateArray;
    }

    /**
     * check variable is date format YYYY-mm-dd
     * @param string $date
     * @return boolean
     */
    public function checkIsDate($date) {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
            return false;
        }
        list ($y, $m, $d) = explode('-', $date);
        if ($m !== '02') {
            $aryDaysInMonth = [
                '01' => 31, '03' => 31, '05' => 31, '07' => 31, '08' => 31, '10' => 31, '12' => 31,
                '04' => 30, '06' => 30, '09' => 30, '11' => 30,
            ];
            return isset($aryDaysInMonth[$m]) && $d <= $aryDaysInMonth[$m];
        }
        $daysInMonth = ($y % 4 === 0 && $y % 100 !== 0 || $y % 400 === 0) ? 29 : 28;
        return $d <= $daysInMonth;
    }
}
