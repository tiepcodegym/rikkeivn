<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\Model\Channels;
use Rikkei\Resource\View\getOptions;
use Rikkei\Core\View\Form as FormView;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use DB;
use Rikkei\Resource\Model\TeamFeature;
use Illuminate\Support\Facades\Auth;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\EmployeeWork;
use Rikkei\Team\Model\Role;

class RecruitPlan extends CoreModel
{
    
    protected $table = 'recruitment_plans';
    protected $fillable = ['team_id', 'month', 'year', 'number'];
    
    /**
     * define list detail per page
     */
    const PER_PAGE = 50;
    
    /**
     * get number check same null as 0
     * @return int
     */
    public function getNumber() {
        if ($this->number === null) {
            return 0;
        }
        return $this->number;
    }
    
    /**
     * get plan of year lists by month
     * @param type $year
     * @return type
     */
    public static function getPlanOfYear($year) {
        return self::where('year', $year)
                ->groupBy('month')
                ->select('month', DB::raw('SUM(IFNULL(number, 0)) as total'))
                ->lists('total', 'month');
    }
    
    /**
     * get actual employee of year separate by month
     * @param type $currYear
     * @return type
     */
    public static function getActualOfYear($currYear, $contractTypes = []) {
        $empTbl = Employee::getTableName();
        $candidateTbl = Candidate::getTableName();

        $actualsMonth = DB::table($empTbl)
            ->leftJoin($candidateTbl . ' as cdd', function ($join) use ($empTbl) {
                $join->on($empTbl . '.id', '=', 'cdd.employee_id')
                        ->whereNull('cdd.deleted_at');
            })
            ->leftJoin(EmployeeWork::getTableName() . ' as empw', $empTbl . '.id', '=', 'empw.employee_id')
            ->where(function ($query) {
                $query->whereNull('cdd.id')
                        ->orWhereIn('cdd.status', ['?', '?']);
            })
            ->whereNull($empTbl.'.deleted_at');

        if ($contractTypes) {
            $strContractTypes = array_map(function ($item) {
                return '?';
            }, $contractTypes);
            $actualsMonth->where(function ($query) use ($strContractTypes) {
                $query->whereNull('empw.contract_type')
                        ->orWhereIn('empw.contract_type', $strContractTypes);
            });
        }

        $bindingsParams = [];
        for ($month = 1; $month <= 12; $month++) {
            //actual
            $yearMonth = $currYear . '-' . ($month < 10 ? '0' . $month : $month);
            $actualsMonth->addSelect(DB::raw('SUM(CASE '
                        . 'WHEN (DATE_FORMAT(join_date, "%Y-%m") < ?) AND '
                        . '(leave_date is NULL OR DATE_FORMAT(leave_date, "%Y-%m") > ? ) '
                    . 'THEN 1 ELSE 0 END) as month_' . $month));
            //join
            $actualsMonth->addSelect(DB::raw('SUM(CASE '
                        . 'WHEN DATE_FORMAT(join_date, "%Y-%m") = ? '
                        . 'AND (leave_date is NULL OR DATE_FORMAT(leave_date, "%Y-%m") >= ?) '
                    . 'THEN 1 ELSE 0 END) as join_month_' . $month));
            //leave
            $actualsMonth->addSelect(DB::raw('SUM(CASE '
                        . 'WHEN leave_date is NOT NULL '
                        . 'AND DATE_FORMAT(leave_date, "%Y-%m") = ? '
                    . 'THEN 1 ELSE 0 END) as leave_month_' . $month));
            array_push($bindingsParams,
                    $yearMonth, $yearMonth, //actual
                    $yearMonth, $yearMonth, //join
                    $yearMonth);//leave
        }
        array_push($bindingsParams, getOptions::WORKING, getOptions::LEAVED_OFF);
        if ($contractTypes) {
            $bindingsParams = array_merge($bindingsParams, $contractTypes);
        }
        $actualsMonth->setBindings($bindingsParams);
        return (array) $actualsMonth->first();
    }

    /**
     * get total candidate pass of month in year
     * @param type $year
     * @param type $status
     * @return type
     */
    public static function getCandidatesSepByMonth(
        $year,
        $status = [],
        $workingTypes = [],
        $type = 'in'
    )
    {
        if (!$status) {
            $status = [getOptions::END, getOptions::PREPARING];
        }
        $fieldDate = 'CASE WHEN working_type = ' . getOptions::WORKING_INTERNSHIP . ' THEN trainee_start_date ELSE start_working_date END';
        if ($type == 'out') {
            $fieldDate = 'CASE WHEN working_type = ' . getOptions::WORKING_INTERNSHIP . ' THEN trainee_end_date ELSE NULL END';
        }
        $collect = Candidate::select(DB::raw('MONTH('. $fieldDate .') as month'), DB::raw('COUNT(id) as total'))
                ->whereIn('status', $status)
                ->whereNotNull(DB::raw($fieldDate))
                ->where(DB::raw($fieldDate), '!=', '0000-00-00 00:00:00')
                ->where(DB::raw('YEAR('. $fieldDate.')'), $year)
                ->groupBy('month');
        if ($workingTypes) {
            $collect->whereIn('working_type', $workingTypes);
        }
        return $collect->lists('total', 'month')->toArray();
    }
    
    /**
     * get total candidate passed of year before
     * @param type $year
     * @return integer
     */
    public static function totalCandidatePassedBefore($year, $workingTypes = [])
    {
        $fieldDate = 'CASE WHEN working_type = ' . getOptions::WORKING_INTERNSHIP . ' THEN trainee_start_date ELSE start_working_date END';
        $collect = Candidate::whereIn('status', [getOptions::END, getOptions::PREPARING])
                ->whereNotNull(DB::raw($fieldDate))
                ->where(DB::raw($fieldDate), '!=', '0000-00-00 00:00:00')
                ->where(DB::raw('YEAR('. $fieldDate .')'), '<', $year)
                ->where(function ($query) use ($year) {
                    $query->where('working_type', '!=', getOptions::WORKING_INTERNSHIP)
                            ->orWhere(DB::raw('YEAR(trainee_end_date)'), '>=', $year);
                });
        if ($workingTypes) {
            $collect->whereIn('working_type', $workingTypes);
        }
        return $collect->get()->count();
    }

    /*
     * get time start or time end of month or year
     */
    public static function getRangeTime($time, $timeType = 'month')
    {
        if ($timeType == 'month') {
            $timeStart = $time->startOfMonth()->toDateString();
            $timeEnd = $time->endOfMonth()->toDateString();
        } else {
            $timeStart = $time->startOfYear()->toDateString();
            $timeEnd = $time->endOfYear()->toDateString();
        }
        return ['start' => $timeStart, 'end' => $timeEnd];
    }

    /**
     * get candidate list detail by month of year (time)
     * @param type $time
     * @return type
     */
    public static function employeeIn(
        $time,
        $timeType = 'month',
        $workingTypes = [],
        $teamFilter = null,
        $urlFilter = null,
        $export = false
    ) 
    {
        $teamTbl = Team::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        $empTbl = Employee::getTableName();
        $channelTbl = Channels::getTableName();
        $candidateTbl = Candidate::getTableName();
        $candidateProgTbl = CandidateProgramming::getTableName();
        $rangeTime = self::getRangeTime($time, $timeType);

        //employee joined
        $listJoined = Employee::leftJoin(DB::raw('(SELECT tmb1.employee_id, team.id as team_id, team.name as team_name, role.role as role_name '
                        . 'FROM ' . $teamMemberTbl . ' AS tmb1 '
                        . 'INNER JOIN '. $teamTbl .' AS team ON tmb1.team_id = team.id '
                        . 'INNER JOIN '. Role::getTableName() . ' AS role ON tmb1.role_id = role.id AND role.special_flg = '. Role::FLAG_POSITION .') '
                        . 'AS tmb'), $empTbl . '.id', '=', 'tmb.employee_id')
                ->leftJoin(EmployeeWork::getTableName() . ' as empw', $empTbl. '.id', '=', 'empw.employee_id')
                ->leftJoin($candidateTbl . ' as cdd', function ($join) use ($empTbl) {
                    $join->on($empTbl . '.id', '=', 'cdd.employee_id')
                            ->whereNull('cdd.deleted_at');
                })
                ->leftjoin($channelTbl.' as channel', 'cdd.channel_id', '=', 'channel.id')
                ->leftjoin($empTbl.' as emp', 'cdd.presenter_id', '=', 'emp.id')
                ->where(function ($query) {
                    $query->whereNull('cdd.id')
                            ->orWhereIn('cdd.status', [getOptions::WORKING, getOptions::LEAVED_OFF]);
                })
                ->whereNull($empTbl.'.deleted_at');
        if ($workingTypes) {
            $listJoined->where(function ($query) use ($workingTypes) {
                    $query->whereNull('empw.contract_type')
                            ->orWhereIn('empw.contract_type', $workingTypes);
                });
        }
        $listJoined->where(function ($query) use ($empTbl) {
                    $query->where(DB::raw('DATE('. $empTbl .'.join_date)'), '<=', DB::raw('DATE('. $empTbl .'.leave_date)'))
                            ->orWhereNull($empTbl .'.leave_date');
                })
                ->where(DB::raw('DATE('. $empTbl .'.join_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('. $empTbl .'.join_date)'), '<=', $rangeTime['end'])
                ->select(
                    $empTbl.'.id',
                    $empTbl.'.name',
                    $empTbl.'.email',
                    $empTbl.'.join_date',
                    'empw.contract_type as working_type',
                    $empTbl.'.contract_length',
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(tmb.team_name, " - " , tmb.role_name)) SEPARATOR ", ") as team_names'),
                    DB::raw('1 as type'),
                    'channel.name as cnname',
                    'emp.name as empname',
                    'cdd.recruiter',
                    'cdd.type as level_type',
                    DB::raw("(SELECT GROUP_CONCAT(programming_id) FROM {$candidateProgTbl} WHERE {$candidateProgTbl}.candidate_id = cdd.id) as prog_id")
                )
                ->groupBy($empTbl.'.id');
                
        //get candidate passed
        $candidatePassed = Candidate::leftJoin($teamTbl . ' as team', $candidateTbl . '.team_id', '=', 'team.id')
                ->leftjoin($channelTbl.' as channel', $candidateTbl.'.channel_id', '=', 'channel.id')
                ->leftjoin($empTbl.' as emp', $candidateTbl.'.presenter_id', '=', 'emp.id')
                ->whereIn($candidateTbl . '.status', [getOptions::END, getOptions::PREPARING])
                ->whereIn($candidateTbl . '.working_type', $workingTypes)
                ->where(DB::raw('DATE('. $candidateTbl . '.start_working_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('. $candidateTbl . '.start_working_date)'), '<=', $rangeTime['end'])
                ->select(
                    $candidateTbl.'.id',
                    $candidateTbl.'.fullname as name',
                    $candidateTbl.'.email',
                    $candidateTbl.'.start_working_date as join_date',
                    $candidateTbl.'.working_type',
                    $candidateTbl.'.contract_length',
                    DB::raw('CASE WHEN team.name IS NULL '
                                . 'THEN team.name ELSE '
                                . 'CONCAT(team.name, " - ", '. getOptions::selectCase($candidateTbl .'.position_apply', getOptions::getInstance()->getRoles()) .') '
                                . 'END AS team_names'),
                    DB::raw('2 as type'),
                    'channel.name as cnname',
                    'emp.name as empname',
                    $candidateTbl.'.recruiter',
                    $candidateTbl.'.type as level_type',
                    DB::raw("(SELECT GROUP_CONCAT(programming_id) FROM {$candidateProgTbl} WHERE {$candidateProgTbl}.candidate_id = {$candidateTbl}.id) as prog_id")
                )
                ->groupBy($candidateTbl.'.id');
        
        //filter data
        if ($teamFilter) {
            if ($teamFilter == -1) {
                $listJoined->whereNull('tmb.team_id');
                $candidatePassed->whereNull($candidateTbl.'.team_id');
            } else {
                $teamFilter = Team::teamChildIds($teamFilter);
                $listJoined->whereIn('tmb.team_id', $teamFilter);
                $candidatePassed->whereIn($candidateTbl.'.team_id', $teamFilter);
            }
        }

        $filterData = FormView::getFilterData('candidate', null, $urlFilter);
        if ($filterData) {
            foreach ($filterData as $key => $value) {
                if($key === 'channel'){
                    $listJoined->where('channel.name', 'REGEXP', addslashes("$value"));
                    $candidatePassed->where('channel.name', 'REGEXP', addslashes("$value"));
                }
                elseif ($key === 'working_type' || $key === 'contract_length'){
                    $listJoined->where('empw.contract_type', 'REGEXP', addslashes("$value"));
                    $candidatePassed->where($candidateTbl . '.' . $key, 'REGEXP', addslashes("$value"));
                }
                elseif ($key === 'presenter') {
                    $candidatePassed->where('emp.name', 'REGEXP', addslashes("$value"));
                    $listJoined->whereNull($empTbl.'.id');
                } elseif ($key === 'type') {
                    $candidatePassed->where($candidateTbl.'.type', 'REGEXP', addslashes("$value"));
                    $listJoined->where('cdd.type', 'REGEXP', addslashes("$value"));
                } else{
                    $listJoined->where($empTbl . '.' . $key, 'REGEXP', addslashes("$value"));
                    $field = $candidateTbl . '.' . ($key == 'name' ? 'fullname' : $key);
                    $candidatePassed->where($field, 'REGEXP', addslashes("$value"));
                }
            }
        }
        $listJoined->union($candidatePassed);
        $listJoined = $listJoined->orderBy('join_date', 'desc')
                            ->get();
        if ($export) {
            return $listJoined;
        }

        $listJoined = $listJoined->toArray();
        $page = request()->get('page', 1);
        $perPage = self::PER_PAGE;
        $slice = array_slice($listJoined, $perPage * ($page - 1), $perPage);
        $data = new Paginator($slice, count($listJoined), $perPage);
        $data->setPath(request()->url());
        return $data;
    }

    /**
     * get detail employees list by month of year (time)
     * @param type $time
     * @return type
     */
    public static function employeeOut(
        $time,
        $timeType = 'month',
        $workingType = [],
        $teamFilter = null,
        $urlFilter = null,
        $export = false
    )
    {
        $teamMbTbl = TeamMember::getTableName();
        $empTbl = Employee::getTableName();
        $candidateTbl = Candidate::getTableName();
        $teamTbl = Team::getTableName();
        $rangeTime = self::getRangeTime($time, $timeType);
        $contractTypeInternal = EmployeeWork::contractTypeInternal();
        $listLeaved = Employee::leftJoin(DB::raw('(SELECT tmb1.employee_id, team.id as team_id, team.name as team_name, role.role as role_name '
                        . 'FROM ' . $teamMbTbl . ' AS tmb1 '
                        . 'INNER JOIN '. $teamTbl .' AS team ON tmb1.team_id = team.id '
                        . 'INNER JOIN '. Role::getTableName() . ' AS role ON tmb1.role_id = role.id AND role.special_flg = '. Role::FLAG_POSITION .') '
                        . 'AS tmb'), $empTbl . '.id', '=', 'tmb.employee_id')
                ->leftJoin($candidateTbl . ' as cdd', $empTbl . '.id', '=', 'cdd.employee_id')
                ->leftJoin(EmployeeWork::getTableName() . ' as empw', $empTbl. '.id', '=', 'empw.employee_id')
                ->where(function ($query) use ($contractTypeInternal) {
                    $query->whereNull('empw.contract_type')
                            ->orWhereIn('empw.contract_type', $contractTypeInternal);
                })
                ->where(function ($query) {
                    $query->whereNull('cdd.id')
                            ->orWhereIn('cdd.status', [getOptions::WORKING, getOptions::LEAVED_OFF]);
                })
                ->whereNull($empTbl.'.deleted_at')
                ->where(DB::raw('DATE('. $empTbl .'.leave_date)'), '>=', DB::raw('DATE('. $empTbl .'.join_date)'))
                ->where(DB::raw('DATE('. $empTbl .'.leave_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('. $empTbl .'.leave_date)'), '<=', $rangeTime['end'])
                ->select(
                    $empTbl . '.id',
                    $empTbl . '.name',
                    $empTbl . '.leave_date',
                    $empTbl . '.leave_reason',
                    'empw.contract_type as working_type',
                    $empTbl . '.contract_length',
                    $empTbl . '.leader_approved',
                    $empTbl . '.account_status',
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(tmb.team_name, " - " , tmb.role_name)) SEPARATOR ", ") as team_names'),
                    'cdd.type as level_type'
                )
                ->groupBy($empTbl.'.id')
                ->orderBy('leave_date', 'asc');
        //filter data
        if ($teamFilter) {
            if ($teamFilter == -1) {
                $listLeaved->whereNull('tmb.team_id');
            } else {
                $listLeaved->whereIn('tmb.team_id', Team::teamChildIds($teamFilter));
            }
        }

        $empFilter = FormView::getFilterData('candidate', null, $urlFilter);
        if ($empFilter) {
            foreach ($empFilter as $key => $value) {
                $listLeaved->where('cdd.' . $key, 'REGEXP', addslashes("$value"));
            }
        }
        $empFilter = FormView::getFilterData('employee', null, $urlFilter);
        if ($empFilter) {
            foreach ($empFilter as $key => $value) {
                if ($key == 'working_type') {
                    $listLeaved->where('empw.contract_type', $value);
                } else {
                    $listLeaved->where($empTbl . '.' . $key, 'REGEXP', addslashes("$value"));
                }
            }
        }
        if ($export) {
            return $listLeaved->get();
        }
        return $listLeaved->paginate(self::PER_PAGE);
    }

    /*
     * get list employee + candidate as external type
     */
    public static function employeeTotalIn(
        $time,
        $timeType = 'month',
        $workingType = [],
        $teamFilter = null,
        $urlFilter = null,
        $export = false
    )
    {
        $teamTbl = Team::getTableName();
        $teamMemberTbl = TeamMember::getTableName();
        $empTbl = Employee::getTableName();
        $channelTbl = Channels::getTableName();
        $candidateTbl = Candidate::getTableName();
        $candidateProgTbl = CandidateProgramming::getTableName();
        $rangeTime = self::getRangeTime($time, $timeType);
        //employee joined
        $listJoined = Employee::leftJoin(DB::raw('(SELECT tmb1.employee_id, team.id as team_id, team.name as team_name, role.role as role_name '
                        . 'FROM ' . $teamMemberTbl . ' AS tmb1 '
                        . 'INNER JOIN '. $teamTbl .' AS team ON tmb1.team_id = team.id '
                        . 'INNER JOIN '. Role::getTableName() . ' AS role ON tmb1.role_id = role.id AND role.special_flg = '. Role::FLAG_POSITION .') '
                        . 'AS tmb'), $empTbl . '.id', '=', 'tmb.employee_id')
                ->leftJoin(EmployeeWork::getTableName() . ' as empw', $empTbl. '.id', '=', 'empw.employee_id')
                ->leftJoin($candidateTbl . ' as cdd', function ($join) use ($empTbl) {
                    $join->on($empTbl . '.id', '=', 'cdd.employee_id')
                            ->whereNull('cdd.deleted_at');
                })
                ->leftjoin($channelTbl.' as channel', 'cdd.channel_id', '=', 'channel.id')
                ->leftjoin($empTbl.' as emp', 'cdd.presenter_id', '=', 'emp.id')
                ->where(function ($query) {
                    $query->whereNull('cdd.id')
                            ->orWhereIn('cdd.status', [getOptions::WORKING, getOptions::LEAVED_OFF]);
                })
                ->whereNull($empTbl.'.deleted_at')
                ->where(function ($query) use ($empTbl) {
                    $query->where(DB::raw('DATE('. $empTbl .'.join_date)'), '<=', DB::raw('DATE('. $empTbl .'.leave_date)'))
                            ->orWhereNull($empTbl .'.leave_date');
                })
                ->where(DB::raw('DATE('. $empTbl .'.join_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('. $empTbl .'.join_date)'), '<=', $rangeTime['end'])
                ->select(
                    $empTbl.'.id',
                    $empTbl.'.name',
                    $empTbl.'.email',
                    $empTbl.'.join_date',
                    'empw.contract_type as working_type',
                    $empTbl.'.contract_length',
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(tmb.team_name, " - " , tmb.role_name)) SEPARATOR ", ") as team_names'),
                    DB::raw('1 as type'),
                    'channel.name as cnname',
                    'emp.name as empname',
                    'cdd.recruiter',
                    'cdd.type as level_type',
                    DB::raw("(SELECT GROUP_CONCAT(programming_id) FROM {$candidateProgTbl} WHERE {$candidateProgTbl}.candidate_id = cdd.id) as prog_id")
                )
                ->groupBy($empTbl.'.id');

        //get candidate passed
        $candidatePassed = Candidate::leftJoin($teamTbl . ' as team', $candidateTbl . '.team_id', '=', 'team.id')
                ->leftJoin($channelTbl.' as channel', $candidateTbl.'.channel_id', '=', 'channel.id')
                ->leftJoin($empTbl.' as emp', $candidateTbl.'.presenter_id', '=', 'emp.id')
                ->whereIn($candidateTbl . '.status', [getOptions::END, getOptions::PREPARING])
                ->where(function ($query) use ($candidateTbl, $rangeTime) {
                    $query->where(function ($query1) use ($candidateTbl, $rangeTime) {
                        $query1->where($candidateTbl.'.working_type', getOptions::WORKING_INTERNSHIP)
                                ->where(DB::raw('DATE('. $candidateTbl . '.trainee_start_date)'), '>=', $rangeTime['start'])
                                ->where(DB::raw('DATE('. $candidateTbl . '.trainee_start_date)'), '<=', $rangeTime['end']);
                    })
                    ->orWhere(function ($query2) use ($candidateTbl, $rangeTime) {
                        $query2->where($candidateTbl.'.working_type', '!=', getOptions::WORKING_INTERNSHIP)
                                ->where(DB::raw('DATE('. $candidateTbl . '.start_working_date)'), '>=', $rangeTime['start'])
                                ->where(DB::raw('DATE('. $candidateTbl . '.start_working_date)'), '<=', $rangeTime['end']);
                    });
                })
                ->select(
                    $candidateTbl.'.id',
                    $candidateTbl.'.fullname as name',
                    $candidateTbl.'.email',
                    DB::raw('CASE WHEN '.$candidateTbl.'.working_type = '.getOptions::WORKING_INTERNSHIP.' THEN ' . $candidateTbl . '.trainee_start_date ELSE '
                            .$candidateTbl.'.start_working_date END AS join_date'),
                    $candidateTbl.'.working_type',
                    $candidateTbl.'.contract_length',
                    DB::raw('CASE WHEN team.name IS NULL '
                                . 'THEN team.name ELSE '
                                . 'CONCAT(team.name, " - ", '. getOptions::selectCase($candidateTbl .'.position_apply', getOptions::getInstance()->getRoles()) .') '
                                . 'END AS team_names'),
                    DB::raw('2 as type'),
                    'channel.name as cnname',
                    'emp.name as empname',
                    $candidateTbl.'.recruiter',
                    $candidateTbl.'.type as level_type',
                    DB::raw("(SELECT GROUP_CONCAT(programming_id) FROM {$candidateProgTbl} WHERE {$candidateProgTbl}.candidate_id = {$candidateTbl}.id) as prog_id")
                )
                ->groupBy($candidateTbl.'.id');
        
        //filter data
         if ($teamFilter) {
            if ($teamFilter == -1) {
                $listJoined->whereNull('tmb.team_id');
                $candidatePassed->whereNull($candidateTbl.'.team_id');
            } else {
                $teamFilter = Team::teamChildIds($teamFilter);
                $listJoined->whereIn('tmb.team_id', $teamFilter);
                $candidatePassed->whereIn($candidateTbl.'.team_id', $teamFilter);
            }
        }

        $filterData = FormView::getFilterData('candidate', null, $urlFilter);
        if ($filterData) {
            foreach ($filterData as $key => $value) {
                if ($key === 'channel') {
                    $listJoined->where('channel.name', 'REGEXP', addslashes("$value"));
                    $candidatePassed->where('channel.name', 'REGEXP', addslashes("$value"));
                } elseif ($key === 'working_type') {
                    $listJoined->where('empw.contract_type', $value);
                    $candidatePassed->where($candidateTbl . '.working_type', $value);
                } elseif ($key === 'presenter') {
                    $candidatePassed->where('emp.name', 'REGEXP', addslashes("$value"));
                    $listJoined->whereNull($empTbl.'.id');
                } elseif ($key === 'type') {
                    $candidatePassed->where($candidateTbl.'.type', 'REGEXP', addslashes("$value"));
                    $listJoined->where('cdd.type', 'REGEXP', addslashes("$value"));
                } else {
                    $listJoined->where($empTbl . '.' . $key, 'REGEXP', addslashes("$value"));
                    $field = $candidateTbl . '.' . ($key == 'name' ? 'fullname' : $key);
                    $candidatePassed->where($field, 'REGEXP', addslashes("$value"));
                }
            }
        }
        $listJoined->union($candidatePassed);
        $listJoined = $listJoined->orderBy('join_date', 'desc')
                            ->get();

        if ($export) {
            return $listJoined;
        }

        $listJoined = $listJoined->toArray();
        $page = request()->get('page', 1);
        $perPage = self::PER_PAGE;
        $slice = array_slice($listJoined, $perPage * ($page - 1), $perPage);
        $data = new Paginator($slice, count($listJoined), $perPage);
        $data->setPath(request()->url());
        return $data;
    }

    /*
     * get list detail external employee out
     */
    public static function employeeTotalOut(
        $time,
        $timeType = 'month',
        $workingTypes = [],
        $teamFilter = null,
        $urlFilter = null,
        $export = false
    )
    {
        $teamMbTbl = TeamMember::getTableName();
        $empTbl = Employee::getTableName();
        $candidateTbl = Candidate::getTableName();
        $teamTbl = Team::getTableName();
        $rangeTime = self::getRangeTime($time, $timeType);
        $listLeaved = Employee::leftJoin(
            DB::raw(
                '(SELECT tmb1.employee_id, team.id as team_id, team.name as team_name, role.role as role_name '
                . 'FROM ' . $teamMbTbl . ' AS tmb1 '
                . 'INNER JOIN '. $teamTbl .' AS team ON tmb1.team_id = team.id '
                . 'INNER JOIN '. Role::getTableName() . ' AS role ON tmb1.role_id = role.id AND role.special_flg = '. Role::FLAG_POSITION .') '
                . 'AS tmb'
            ),
            $empTbl . '.id',
            '=',
            'tmb.employee_id'
        )
                ->leftJoin($candidateTbl . ' as cdd', $empTbl . '.id', '=', 'cdd.employee_id')
                ->leftJoin(EmployeeWork::getTableName() . ' as empw', $empTbl. '.id', '=', 'empw.employee_id');
        if ($workingTypes) {
            $listLeaved->where(function ($query) use ($workingTypes) {
                    $query->whereNotNull('empw.contract_type')
                            ->whereIn('empw.contract_type', $workingTypes);
                });
        }
        $listLeaved->where(function ($query) {
                    $query->whereNull('cdd.id')
                            ->orWhereIn('cdd.status', [getOptions::WORKING, getOptions::LEAVED_OFF]);
                })
                ->whereNull($empTbl.'.deleted_at')
                ->where(DB::raw('DATE('. $empTbl .'.leave_date)'), '>=', DB::raw('DATE('. $empTbl .'.join_date)'))
                ->where(DB::raw('DATE('. $empTbl .'.leave_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('. $empTbl .'.leave_date)'), '<=', $rangeTime['end'])
                ->select(
                    DB::raw('1 as type'),
                    $empTbl . '.id',
                    $empTbl . '.name',
                    $empTbl . '.leave_date',
                    $empTbl . '.leave_reason',
                    'empw.contract_type as working_type',
                    $empTbl . '.contract_length',
                    $empTbl . '.leader_approved',
                    $empTbl . '.account_status',
                    DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(tmb.team_name, " - " , tmb.role_name)) SEPARATOR ", ") as team_names'),
                    'cdd.type as level_type'
                )
                ->groupBy($empTbl.'.id');
        //trainee leave in time
        $candidateLeaved = Candidate::leftJoin(Team::getTableName() . ' as team', $candidateTbl.'.team_id', '=', 'team.id')
                ->where($candidateTbl . '.working_type', getOptions::WORKING_INTERNSHIP)
                ->whereIn($candidateTbl . '.status', [getOptions::END, getOptions::PREPARING])
                ->where(DB::raw('DATE('.$candidateTbl.'.trainee_end_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('.$candidateTbl.'.trainee_end_date)'), '<=', $rangeTime['end'])
                ->select(
                    DB::raw('2 as type'),
                    $candidateTbl . '.id',
                    $candidateTbl . '.fullname as name',
                    $candidateTbl . '.trainee_end_date as leave_date',
                    DB::raw('NULL as leave_reason'),
                    $candidateTbl . '.working_type',
                    DB::raw('NULL as contract_length'),
                    DB::raw('NULL as leader_approved'),
                    DB::raw('NULL as account_status'),
                    DB::raw('CASE WHEN team.name IS NULL '
                                . 'THEN team.name ELSE '
                                . 'CONCAT(team.name, " - ", '. getOptions::selectCase($candidateTbl .'.position_apply', getOptions::getInstance()->getRoles()) .') '
                                . 'END AS team_names'),
                    $candidateTbl.'.type as level_type'
                )
                ->groupBy($candidateTbl . '.id');

        //filter data
        if ($teamFilter) {
            if ($teamFilter == -1) {
                $listLeaved->whereNull('tmb.team_id');
                $candidateLeaved->whereNull($candidateTbl.'.team_id');
            } else {
                $teamFilter = Team::teamChildIds($teamFilter);
                $listLeaved->whereIn('tmb.team_id', $teamFilter);
                $candidateLeaved->whereIn($candidateTbl.'.team_id', $teamFilter);
            }
        }

        $empFilter = FormView::getFilterData('candidate', null, $urlFilter);
        if ($empFilter) {
            foreach ($empFilter as $key => $value) {
                if ($key == 'type') {
                    $listLeaved->where('cdd.type', 'REGEXP', addslashes("$value"));
                    $candidateLeaved->where($candidateTbl . '.type', 'REGEXP', addslashes("$value"));
                } else {
                    $listLeaved->where('cdd.' . $key, 'REGEXP', addslashes("$value"));
                    $candidateLeaved->where($candidateTbl . '.'.$key, 'REGEXP', addslashes("$value"));
                }
            }
        }
        $empFilter = FormView::getFilterData('employee', null, $urlFilter);
        if ($empFilter) {
            foreach ($empFilter as $key => $value) {
                if ($key == 'team_id') {
                    if ($value == -1) {
                        $listLeaved->whereNull('tmb.team_id');
                        $candidateLeaved->whereNull($candidateTbl.'.team_id');
                    } else {
                        $listLeaved->where('tmb.team_id', $value);
                        $candidateLeaved->where($candidateTbl.'.team_id', $value);
                    }
                } elseif ($key == 'working_type') {
                    $listLeaved->where('empw.contract_type', $value);
                    $candidateLeaved->where($candidateTbl.'.working_type', $value);
                } elseif ($key == 'name') {
                    $listLeaved->where($empTbl . '.name', 'REGEXP', addslashes($value));
                    $candidateLeaved->where($candidateTbl . '.fullname', 'REGEXP', addslashes($value));
                } else {
                    $listLeaved->where($empTbl . '.' . $key, 'REGEXP', addslashes("$value"));
                }
            }
        }

        $listLeaved->union($candidateLeaved);
        $listLeaved = $listLeaved->orderBy('leave_date', 'asc')
                ->get();

        if ($export) {
            return $listLeaved;
        }

        $page = request()->get('leave_page', 1);
        $perPage = self::PER_PAGE;
        $slice = $listLeaved->slice($perPage * ($page - 1), $perPage);
        $data = new Paginator($slice, $listLeaved->count(), $perPage, $page, ['pageName' => 'leave_page']);
        $data->setPath(request()->url());
        return $data;
    }

    /**
     * get candidate passed by time separate by teams
     * @param type $time
     * @return type
     */
    public static function getEmpJoinedSepByTeam($time, $timeType = 'month', $workingTypes = [])
    {
        $teamTbl = Team::getTableName();
        $teamMbTbl = TeamMember::getTableName();
        $candidateTbl = Candidate::getTableName();
        $empTbl = Employee::getTableName();
        $rangeTime = self::getRangeTime($time, $timeType);
        $collect = Employee::leftJoin(DB::raw('(SELECT tmb1.employee_id, team.id as team_id, team.name as team_name '
                        . 'FROM ' . $teamMbTbl . ' AS tmb1 '
                        . 'INNER JOIN '. $teamTbl .' AS team ON tmb1.team_id = team.id) '
                        . 'AS tmb'), $empTbl . '.id', '=', 'tmb.employee_id')
                ->leftJoin($candidateTbl . ' as cdd', function ($join) use ($empTbl) {
                    $join->on($empTbl . '.id', '=', 'cdd.employee_id')
                            ->whereNull('cdd.deleted_at');
                })
                ->leftJoin(EmployeeWork::getTableName() . ' as empw', $empTbl. '.id', '=', 'empw.employee_id');
        if ($workingTypes) {
            $collect->where(function ($query) use ($workingTypes) {
                    $query->whereNull('empw.contract_type')
                            ->orWhereIn('empw.contract_type', $workingTypes);
                });
        }
        return $collect->where(function ($query) {
                    $query->whereNull('cdd.id')
                            ->orWhereIn('cdd.status', [getOptions::WORKING, getOptions::LEAVED_OFF]);
                })
                ->whereNull($empTbl.'.deleted_at')
                ->where(function ($query) use ($empTbl) {
                    $query->where(DB::raw('DATE('. $empTbl .'.join_date)'), '<=', DB::raw('DATE('. $empTbl .'.leave_date)'))
                            ->orWhereNull('leave_date');
                })
                ->where(DB::raw('DATE('.$empTbl.'.join_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('.$empTbl.'.join_date)'), '<=', $rangeTime['end'])
                ->select(
                    DB::raw('IFNULL(tmb.team_id, -1) as team_id'),
                    $empTbl . '.id as emp_id'
                )
                ->groupBy('team_id', 'emp_id')
                ->get()
                ->groupBy('team_id');
    }
    
    /**
     * get candidate passed sep by team
     * @param type $time
     * @return type
     */
    public static function getCandidatePassedSepByTeam(
        $time,
        $timeType = 'month',
        $workingTypes = [],
        $fieldDate = 'start_working_date'
    )
    {
        $rangeTime = self::getRangeTime($time, $timeType);
        $status = [getOptions::END, getOptions::PREPARING];
        //status working => employee, not include here
        $teamTbl = Team::getTableName();
        $candidateTbl = Candidate::getTableName();
        $collect = Candidate::leftJoin($teamTbl . ' as team', $candidateTbl . '.team_id', '=', 'team.id')
                ->whereIn($candidateTbl . '.working_type', $workingTypes)
                ->where(DB::raw('DATE('.$candidateTbl . '.'. $fieldDate .')'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('.$candidateTbl . '.'. $fieldDate .')'), '<=', $rangeTime['end'])
                ->select(DB::raw('IFNULL('. $candidateTbl .'.team_id, -1) as team_id'), DB::raw('COUNT(DISTINCT('. $candidateTbl .'.id)) as total'))
                ->groupBy($candidateTbl.'.team_id');
        if ($workingTypes) {
            $collect->whereIn($candidateTbl . '.status', $status);
        }
        return $collect->lists('total', 'team_id')->toArray();
    }

    /**
     * get employees by time separate by teams
     * @param type $time
     * @return type
     */
    public static function getEmpLeavedSepByTeam($time, $timeType = 'month', $workingTypes = [])
    {
        $teamMbTbl = TeamMember::getTableName();
        $empTbl = Employee::getTableName();
        $candidateTbl = Candidate::getTableName();
        $teamTbl = Team::getTableName();
        $rangeTime = self::getRangeTime($time, $timeType);
        $collect = Employee::leftJoin(DB::raw('(SELECT tmb1.employee_id, team.id as team_id, team.name as team_name '
                        . 'FROM ' . $teamMbTbl . ' AS tmb1 '
                        . 'INNER JOIN '. $teamTbl .' AS team ON tmb1.team_id = team.id) '
                        . 'AS tmb'), $empTbl . '.id', '=', 'tmb.employee_id')
                ->leftJoin($candidateTbl . ' as cdd', $empTbl . '.id', '=', 'cdd.employee_id')
                ->leftJoin(EmployeeWork::getTableName() . ' as empw', $empTbl . '.id', '=', 'empw.employee_id');
        if ($workingTypes) {
            $collect->where(function ($query) use ($workingTypes) {
                    $query->whereNull('empw.contract_type')
                            ->orWhereIn('empw.contract_type', $workingTypes);
                });
        }
        return $collect->where(function ($query) {
                    $query->whereNull('cdd.id')
                            ->orWhereIn('cdd.status', [getOptions::WORKING, getOptions::LEAVED_OFF]);
                })
                ->where(DB::raw('DATE('.$empTbl.'.join_date)'), '<=', DB::raw('DATE('.$empTbl.'.leave_date)'))
                ->where(DB::raw('DATE('.$empTbl.'.leave_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('.$empTbl.'.leave_date)'), '<=', $rangeTime['end'])
                ->select(DB::raw('IFNULL(tmb.team_id, -1) as team_id'), DB::raw('COUNT(DISTINCT('. $empTbl .'.id)) as total'),
                        DB::raw('GROUP_CONCAT(DISTINCT('. $empTbl .'.id) SEPARATOR ",") as employee_ids'))
                ->groupBy('team_id')
                ->lists('employee_ids', 'team_id');
    }

    /**
     * Get total count development in month
     * 
     * @param int $year
     * @param int $month
     */
    public static function getCountDevInMonth($year, $month, $teamIds) {
        $teamFeatureTable = TeamFeature::getTableName();
        $RecruiPlanTable = self::getTableName();
        $result = self::join("{$teamFeatureTable}", "{$teamFeatureTable}.id", "=", "{$RecruiPlanTable}.team_id")
                   ->where("{$teamFeatureTable}.is_soft_dev", Team::IS_SOFT_DEVELOPMENT)
                   ->where("month", $month)
                   ->where("year", $year)
                   ->selectRaw("sum(number) as number_plan");
        if ($teamIds) {
            $result->whereIn("{$teamFeatureTable}.team_alias", $teamIds);
        }  
        return $result->first();
    }
    
    /**
     * Get total number of CV received in a year
     * @param int $year
     * @param string $recruiter 
     * @return array month => total
     */
    public static function getCVInYear($year, $recruiter) {
        $cvResults = Candidate::select(DB::raw('MONTH(received_cv_date) as month'), DB::raw('COUNT(id) as total'))             
                     ->whereNotNull('received_cv_date')
                     ->where('received_cv_date', '!=', '0000-00-00 00:00:00')
                     ->where(DB::raw('YEAR(received_cv_date)'), $year);
        if ($recruiter) {             
            $cvResults = $cvResults->where('recruiter', $recruiter);        
        }
        if(!Permission::getInstance()->isScopeTeam(null, 'resource::candidate.indexCandidate') 
        && !Permission::getInstance()->isScopeCompany(null, 'resource::candidate.indexCandidate')){
            $cvResults = $cvResults->where('recruiter', Permission::getInstance()->getEmployee()->email);
        }
        $cvResults = $cvResults->groupBy('month')->lists('total', 'month');
        
        return $cvResults;
    }
    
    /**
     * Get results of test, interview and offer of a year
     * @param int $year selected year
     * @param string $stage (test | interview | offer)
     * @param string $recruiter recruiter of candidates
     * @return array [month, total, pass, fail]
     */
    public static function getCandidateResultInYear($year, $stage, $recruiter){
        //set stage, plan and result variable
        if ($stage == 'test' || $stage == 'interview'){
            $plan = $stage.'_plan';
            $res = $stage.'_result';            
            if ($stage == 'test') {
                $status = getOptions::ENTRY_TEST;
            }
            else {
                $status = getOptions::INTERVIEWING;
            }
        }
        else {
            $plan = $stage.'_date';
            $res = $stage.'_result';
            $note = $stage.'_note';
            $status = getOptions::OFFERING;
        }
        $testResultsArr = [];
        switch ($stage) {
            case 'interview':
                $plan2 = 'interview2_plan';
                $note = 'interview_note';
                $testResults = Candidate::select(
                            DB::raw('(case when `'.$plan2.'` is not null and `'.$plan2.'` != \'0000-00-00 00:00:00\' then MONTH(`'.$plan2.'`) else MONTH(`'.$plan.'`) end) month'),
                            DB::raw('sum(case when `'.$res.'` = 0 then 1 else 0 end) WaitCount'),
                            DB::raw('sum(case when `'.$res.'` = 2 and (`'.$note.'` = "" or `'.$note.'` is null) then 1 else 0 end) AbsenceCount'),
                            DB::raw('sum(case when `'.$res.'` = 1 then 1 else 0 end) PassCount'),
                            DB::raw('sum(case when `'.$res.'` = 2 and `'.$note.'` != "" then 1 else 0 end) FailCount'))
                            ->where(function($query) use ($plan, $plan2, $year){
                                $query->where(DB::raw('YEAR('.$plan.')'), $year);
                                $query->orWhere(DB::raw('YEAR('.$plan2.')'), $year);
                            });
                break;
            case 'test':
                $mark = 'test_mark';
                $mark_spec = 'test_mark_specialize';
                $testResults = Candidate::select(
                        DB::raw('MONTH(`'.$plan.'`) as month'),
                        DB::raw('sum(case when `'.$res.'` = 0 then 1 else 0 end) WaitCount'),
                        DB::raw('sum(case when `'.$res.'` = 2 and (`'.$mark.'` is null or `'.$mark.'` = "" or `'.$mark_spec.'` is null or `'.$mark_spec.'` = "") then 1 else 0 end) AbsenceCount'),
                        DB::raw('sum(case when `'.$res.'` = 1 then 1 else 0 end) PassCount'),
                        DB::raw('sum(case when `'.$res.'` = 2 and `'.$mark.'` is not null and `'.$mark.'` != "" and `'.$mark_spec.'` is not null and `'.$mark_spec.'` != "" then 1 else 0 end) FailCount'))
                        ->where(DB::raw('YEAR('.$plan.')'), $year);
                break;
            case 'offer':
                $testResults = Candidate::select(
                        DB::raw('MONTH(`'.$plan.'`) as month'),
                        DB::raw('sum(case when `'.$res.'` = 0 then 1 else 0 end) WaitCount'),
                        DB::raw('sum(case when `'.$res.'` = 1 or `'.$res.'` = 3 then 1 else 0 end) PassCount'),
                        DB::raw('sum(case when `'.$res.'` = 2 then 1 else 0 end) FailCount'))
                        ->where(DB::raw('YEAR('.$plan.')'), $year);
                break;
        }
    
        //if there is recruiter
        if ($recruiter){            
            $testResults = $testResults->where('recruiter', $recruiter);            
        }        
        if(!Permission::getInstance()->isScopeTeam(null, 'resource::candidate.indexCandidate') 
        && !Permission::getInstance()->isScopeCompany(null, 'resource::candidate.indexCandidate')){
            $testResults = $testResults->where('recruiter', Permission::getInstance()->getEmployee()->email);
        }        
        $testResults = $testResults->groupBy('month')->get()->toArray();        
        //set array
        foreach ($testResults as $result){
            if ($stage == 'offer') {
                $testResultsArr[$result['month']] = ['WaitCount' => $result['WaitCount'], 'PassCount' => $result['PassCount'], 'FailCount' => $result['FailCount']];
            } else {
                $testResultsArr[$result['month']] = ['WaitCount' => $result['WaitCount'], 'AbsenceCount' => $result['AbsenceCount'], 'PassCount' => $result['PassCount'], 'FailCount' => $result['FailCount']];
            }
            
        }
        return $testResultsArr;
    }
    
    /**
     * Get HR plan in year
     * Group by team, month, year
     * 
     * @param int|null $year
     * @return RecruitPlan collection
     */
    public static function getRecruitPlanGroupTeam($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }
        return self::where('year', $year)
                ->groupBy('team_id')
                ->groupBy('month')
                ->groupBy('year')
                ->selectRaw('month, `year`, IFNULL(number, 0) as number, (select team_alias from teams_feature where id = team_id) as team_id')
                ->get();
    }

    /**
     * get candidates test by time (month, year)
     * @param type $time
     * @param type $timeType
     * @return type
     */
    public static function employeeTest($time, $timeType, $wkTypes = [], $teamFilter = null)
    {
        $rangeTime = self::getRangeTime($time, $timeType);
        $cddTbl = Candidate::getTableName();
        $collection = self::cddQueryData('', $teamFilter)
                ->addSelect(
                    DB::raw('DATE('.$cddTbl.'.test_plan) as plan_date'),
                    $cddTbl.'.test_result as result'
                )
                ->where(DB::raw('DATE('.$cddTbl.'.test_plan)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('.$cddTbl.'.test_plan)'), '<=', $rangeTime['end'])
                ->orderBy($cddTbl.'.test_plan', 'desc');
        //filter data
        $result = FormView::getFilterData('excerpt', 'result');
        if (is_numeric($result)) {
            $collection->where($cddTbl.'.test_result', $result);
        }

        return $collection->paginate(self::PER_PAGE);
    }

    /**
     * get candidates interview by time (month, year)
     * @param type $time
     * @param type $timeType
     * @return type
     */
    public static function employeeInterview($time, $timeType, $wkTypes = [], $teamFilter = null)
    {
        $rangeTime = self::getRangeTime($time, $timeType);
        $cddTbl = Candidate::getTableName();
        $collection = self::cddQueryData('', $teamFilter)
                ->addSelect(
                    DB::raw('DATE('.$cddTbl.'.interview_plan) as plan_date'),
                    $cddTbl.'.interview_result as result'
                )
                ->where(DB::raw('DATE('.$cddTbl.'.interview_plan)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('.$cddTbl.'.interview_plan)'), '<=', $rangeTime['end']);

        //filter data
        $result = FormView::getFilterData('excerpt', 'result');
        if (is_numeric($result)) {
            $collection->where($cddTbl.'.interview_result', $result);
        }

        return $collection->paginate(self::PER_PAGE);
    }

    /**
     * get candidates offer by time (month, year)
     * @param type $time
     * @param type $timeType
     * @return type
     */
    public static function employeeOffer($time, $timeType, $wkTypes = [], $teamFilter = null)
    {
        $rangeTime = self::getRangeTime($time, $timeType);
        $cddTbl = Candidate::getTableName();
        $collection = self::cddQueryData('', $teamFilter)
                ->addSelect(
                    DB::raw('DATE('.$cddTbl.'.offer_date) as plan_date'),
                    $cddTbl.'.offer_result as result'
                )
                ->where(DB::raw('DATE('.$cddTbl.'.offer_date)'), '>=', $rangeTime['start'])
                ->where(DB::raw('DATE('.$cddTbl.'.offer_date)'), '<=', $rangeTime['end']);

        //filter data
        $result = FormView::getFilterData('excerpt', 'result');
        if (is_numeric($result)) {
            $collection->where($cddTbl.'.offer_result', $result);
        }
        $collection->orderBy('plan_date', 'desc');

        return $collection->paginate(self::PER_PAGE);
    }

    /**
     * query candidate data
     * @param type $groupBy
     * @return type
     */
    public static function cddQueryData($groupBy = '', $teamFilter = null)
    {
        $cddTbl = Candidate::getTableName();
        $teamTbl = Team::getTableName();
        $empTbl = Employee::getTableName();
        $channelTbl = Channels::getTableName();

        $collection = Candidate::select(
                    $cddTbl.'.id',
                    $cddTbl.'.fullname',
                    $cddTbl.'.email',
                    $cddTbl.'.recruiter',
                    'channel.name as cnname',
                    'emp.name as empname',
                    DB::raw('GROUP_CONCAT(DISTINCT(IFNULL(pl.id, IF(cddpos.position_apply IS NULL, -1, CONCAT("p_", cddpos.position_apply))))) AS prog_id'),
                    DB::raw('CASE WHEN team.name IS NULL '
                                . 'THEN team.name ELSE '
                                . 'CONCAT(team.name, " - ", '. getOptions::selectCase($cddTbl .'.position_apply', getOptions::getInstance()->getRoles()) .') '
                                . 'END AS team_names')
                )
                ->leftJoin($teamTbl . ' as team', $cddTbl . '.team_id', '=', 'team.id')
                ->leftjoin($channelTbl.' as channel', $cddTbl.'.channel_id', '=', 'channel.id')
                ->leftjoin($empTbl.' as emp', $cddTbl.'.presenter_id', '=', 'emp.id')
                ->leftJoin(CandidateProgramming::getTableName() . ' as cddprog', 'cddprog.candidate_id', '=', $cddTbl . '.id')
                ->leftJoin(Programs::getTableName() . ' as pl', 'cddprog.programming_id', '=', 'pl.id')
                ->leftJoin(CandidatePosition::getTableName() . ' as cddpos', 'cddpos.candidate_id', '=', $cddTbl . '.id')
                ->groupBy($groupBy ? $groupBy : $cddTbl . '.id');
        //filter data
        if ($teamFilter) {
            if ($teamFilter == -1) {
                $collection->whereNull('team.id');
            } else {
                $teamFilter = Team::teamChildIds($teamFilter);
                $collection->leftJoin($teamTbl . ' as team_filter', $cddTbl.'.team_id', '=', 'team_filter.id')
                        ->whereIn('team_filter.id', $teamFilter);
            }
        }
        self::filterGrid($collection, [], null, 'LIKE');
        return $collection;
    }

    /*
     * get candidate list with programming language and position
     */
    public static function employeeDevPosition($time, $timeType, $wkTypes = [], $teamFilter = null)
    {
        $cddTbl = Candidate::getTableName();
        $rangeTime = self::getRangeTime($time, $timeType);
        $collection = Candidate::select($cddTbl.'.id', $cddTbl.'.team_id', $cddTbl.'.type', $cddTbl.'.programming_language_id as prog_id')
                ->whereIn($cddTbl.'.status', [getOptions::END, getOptions::PREPARING, getOptions::WORKING])
                ->where(function ($query) use ($cddTbl, $rangeTime) {
                    $query->where(function ($query1) use ($cddTbl, $rangeTime) {
                        $query1->where($cddTbl.'.working_type', getOptions::WORKING_INTERNSHIP)
                                ->where(DB::raw('DATE('. $cddTbl . '.trainee_start_date)'), '>=', $rangeTime['start'])
                                ->where(DB::raw('DATE('. $cddTbl . '.trainee_start_date)'), '<=', $rangeTime['end']);
                    })
                    ->orWhere(function ($query2) use ($cddTbl, $rangeTime) {
                        $query2->where($cddTbl.'.working_type', '!=', getOptions::WORKING_INTERNSHIP)
                                ->where(DB::raw('DATE('. $cddTbl . '.start_working_date)'), '>=', $rangeTime['start'])
                                ->where(DB::raw('DATE('. $cddTbl . '.start_working_date)'), '<=', $rangeTime['end']);
                    });
                })
                ->where($cddTbl.'.position_apply', getOptions::ROLE_DEV);
        if ($teamFilter) {
            $teamIds = Team::teamChildIds($teamFilter);
            $collection->whereIn($cddTbl.'.team_id', $teamIds);
        }
        return $collection->get();
    }

}
