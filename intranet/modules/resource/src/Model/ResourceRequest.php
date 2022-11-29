<?php
namespace Rikkei\Resource\Model;

use Carbon\Carbon;
use Rikkei\Core\Model\CoreModel;
use DB;
use Rikkei\Notify\Classes\RkNotify;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\Model\RequestLanguages;
use Rikkei\Resource\Model\RequestProgramming;
use Rikkei\Resource\Model\RequestChannel;
use Rikkei\Core\Model\CoreConfigData;
use Rikkei\Resource\View\getOptions;
use Lang;
use Rikkei\Team\Model\Employee;
use Rikkei\Core\Model\EmailQueue;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Core\View\View;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Resource\Model\RequestType;

class ResourceRequest extends CoreModel
{

    protected $table = 'requests';

    const KEY_CACHE = 'requests';
    const SUFFIXKEY_CREATE = 'create';
    const SUFFIXKEY_EDIT = 'edit';

    const CLASS_CANCEL = 'callout-warning';
    const CLASS_INPROGRESS = 'callout-info';
    const CLASS_CLOSE = 'callout-default';

    const BENEFITS = '
- LƯƠNG: Tối thiểu 13 tháng lương/năm. Xét TĂNG 2 lần/năm.
- THƯỞNG: thưởng Tết, thưởng dự án, thưởng ngày lễ, thưởng giới thiệu nhân sự...
- Phụ cấp Chứng chỉ Tiếng Nhật (JLPT từ N3) và tiếng Anh (IELTS từ 6.0) theo yêu cầu của Trưởng Bộ phận.
- Phụ cấp thâm niên ( thời gian làm việc từ 2 năm trở lên).
- Tham gia các hoạt động học tập, đào tạo trong và ngoài công ty, tích điểm học tập, phát triển G-Point trên hệ thống Quản lý của công ty.
- Nghỉ thứ 7, chủ nhật + 12 ngày phép/ năm (Cộng dồn tối đa 20 ngày phép/năm, các ngày phép còn thừa sau cộng dồn sẽ được trả 500.000đ/ngày)
- Câu lạc bộ và nhiều hoạt động văn hóa - thể thao - nghệ thuật được công ty tài trợ hoặc hỗ trợ (Ví dụ: Bóng đá, bóng bàn, cầu lông, bơi lội, âm nhạc, tiếng anh, game...)
- Đảm bảo sức khỏe: Khám sức khỏe định kỳ, hỗ trợ mua bảo hiểm sức khỏe chất lượng cao...
- Tham gia Chương trình bảo hiểm sức khỏe Rikkei Care.
- Thoải mái tinh thần: Phát nhạc theo yêu cầu mỗi ngày, hoa quả tươi, các hoạt động teambuilding gắn kết... Đi chơi gần xa khắp nơi.
- Cơ hội làm việc tại nước ngoài và du lịch khắp nơi.';

    const PUBLISHED = 1;
    const UNPUBLISH = 0;

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
    protected $fillable = ['description', 'request_date', 'group_id', 'team_id',
                            'saler', 'interviewer', 'type', 'recruiter', 'customer',
                            'number_resource', 'role', 'deadline', 'start_working',
                            'end_working', 'effort', 'onsite', 'salary', 'note',
                            'created_by', 'status', 'approve', 'title', 'benefits', 'job_qualifi', 'location', 'is_hot', 'priority_id'];

    protected $hidden = ['interviewer_email'];

    protected $appends = ['interviewer_email'];

    public function getInterviewerEmailAttribute()
    {
        $interviewerIds = explode(',', $this->interviewer);

        return Employee::getEmpByIds($interviewerIds, ['id', 'email']);
    }
    /**
     * The users that belong to the action.
     */
    public function requestLang()
    {
        $tableRequestLang = RequestLanguages::getTableName();
        return $this->belongsToMany('Rikkei\Resource\Model\Languages', $tableRequestLang, 'request_id', 'lang_id');
    }

    /**
     * The users that belong to the action.
     */
    public function requestProgramming()
    {
        $tableRequestProgramming = RequestProgramming::getTableName();
        return $this->belongsToMany('Rikkei\Resource\Model\Programs', $tableRequestProgramming, 'request_id', 'programming_id');
    }

    /**
     * The users that belong to the action.
     */
    public function requestChannel()
    {
        $tableRequestChannel = RequestChannel::getTableName();
        return $this->belongsToMany('Rikkei\Resource\Model\Channels', $tableRequestChannel, 'request_id', 'channel_id');
    }

    /**
     * The users that belong to the action.
     */
    public function requestTeam()
    {
        $tableRequestTeam = RequestTeam::getTableName();
        return $this->belongsToMany('Rikkei\Team\Model\Team', $tableRequestTeam, 'request_id', 'team_id');
    }

    public function requestTypes()
    {
        return $this->hasMany('\Rikkei\Resource\Model\RequestType', 'request_id', 'id');
    }

    public function requestCandidates()
    {
        $tableCandidateRequest = CandidateRequest::getTableName();
        return $this->belongsToMany(Candidate::class, $tableCandidateRequest, 'request_id', 'candidate_id');
    }

    /**
     * get list
     *
     * @return objects
     */
    public function getList($order, $dir, $filterTeam, $filterProLangs, $emp = null, $teamIds = null, $filterTitle = null, $filterRecruiter = null)
    {
        if (count($filterTeam) > 0) {
            $teamArray = array_map('intval', explode(',', $filterTeam));
        }
        $collection = self::join('employees', 'employees.id', '=', 'requests.created_by')
                ->leftJoin('request_team', 'request_team.request_id', '=', 'requests.id')
                ->leftJoin('candidates', 'requests.id', '=', 'candidates.request_id')
                ->leftJoin('request_programming', 'requests.id', '=', 'request_programming.request_id')
                ->leftJoin('programming_languages', 'programming_languages.id', '=', 'request_programming.programming_id')
                ->leftJoin('candidate_request', 'requests.id', '=', 'candidate_request.request_id');

        //Filter by team
        if ($filterTeam) {
            $collection->whereIn('request_team.team_id', $teamArray);
        }

        if ($filterTitle) {
            $collection->where('requests.title', 'LIKE', '%' . $filterTitle . '%');
        }

        if ($filterRecruiter) {
            $collection->where('requests.recruiter', 'LIKE', '%' . $filterRecruiter . '%');
        }

        //Filter by programming languages
        if ($filterProLangs) {
            $collection->whereIn('programming_languages.id', $filterProLangs);
        }

        if ($filterTitle) {
            $collection->where('requests.title', 'LIKE', '%' . $filterTitle . '%');
        }

        //scope self
        if ($emp && !$teamIds) {
            $collection->where(function ($query) use ($emp) {
                $query->where('requests.created_by',$emp->id)
                      ->orWhere('requests.saler', $emp->id )
                      ->orWhereRaw('FIND_IN_SET('.$emp->id.',requests.interviewer)')
                      ->orWhere('requests.recruiter', $emp->email);
            });
        }

        //scope team
        if ($teamIds) {
            $collection->where(function ($query) use ($teamIds, $emp) {
                $query->whereIn('request_team.team_id',$teamIds)
                      ->orWhere('requests.created_by',$emp->id)
                      ->orWhere('requests.saler', $emp->id )
                      ->orWhereRaw('FIND_IN_SET('.$emp->id.',requests.interviewer)')
                      ->orWhere('requests.recruiter', $emp->email);
             });
        }

        $collection->orderBy('requests.status', 'asc');
        $collection->orderBy('requests.deadline', 'desc');
        $collection->groupBy('requests.id');
        $candidateEndStatus = getOptions::END;
        $candidateWorkingStatus = getOptions::WORKING;
        $collection->select('requests.*',
                    'employees.name as owner',
                    DB::raw('(select UNIX_TIMESTAMP(requests.deadline)) as deadlineTime'),
                    DB::raw(
                        '(select COUNT(candidate_request.id) from candidate_request where request_id = requests.id ) as countAllCandidate '),
                    DB::raw('(select COUNT(candidates.id) from candidates where candidates.request_id = requests.id AND candidates.status IN(?,?) ) as countCandidatePass' ),
                    DB::raw(
                        '(select COUNT(candidates.id) from candidates where request_id = requests.id) as countCandidate'),
                    DB::raw("(select group_concat(distinct teams.name SEPARATOR ', ') from request_team inner join teams on teams.id = request_team.team_id where request_team.request_id = requests.id) as team_name"),
                    DB::raw("group_concat(distinct programming_languages.name separator ', ') as pro_lang_names"),
                    DB::raw('
                        (select SUM(request_team.number_resource) from request_team where 
                        request_team.request_id = requests.id) as sumOfOneResource')
                )->addBinding([$candidateEndStatus, $candidateWorkingStatus], 'select');
        return $collection;
    }

    /**
     * get sum request having inprogress
     *
     * @return objects
     */
    public function countAllResourceRequest($filterTeam, $filterProLangs, $emp = null, $teamIds = null)
    {
        if (count($filterTeam) > 0) {
            $teamArray = array_map('intval', explode(',', $filterTeam));
        }

        $collection = self::where('requests.status', getOptions::STATUS_INPROGRESS)
                ->leftJoin('request_team', 'request_team.request_id', '=', 'requests.id')
                ->leftJoin('request_programming', 'requests.id', '=', 'request_programming.request_id')
                ->leftJoin('programming_languages', 'programming_languages.id', '=', 'request_programming.programming_id');

        //Filter by team
        if ($filterTeam) {
            $collection->whereIn('request_team.team_id', $teamArray);
        }

        //Filter by programming languages
        if ($filterProLangs) {
            $collection->whereIn('programming_languages.id', $filterProLangs);
        }

        //scope self
        if ($emp && !$teamIds) {
            $collection->where(function ($query) use ($emp) {
                $query->where('requests.created_by',$emp->id)
                      ->orWhere('requests.saler', $emp->id )
                      ->orWhereRaw('FIND_IN_SET('.$emp->id.',requests.interviewer)')
                      ->orWhere('requests.recruiter', $emp->email);
            });

        }

        //scope team
        if ($teamIds) {
            $collection->where(function ($query) use ($teamIds, $emp) {
                $query->whereIn('request_team.team_id',$teamIds)
                      ->orWhere('requests.created_by',$emp->id)
                      ->orWhere('requests.saler', $emp->id )
                      ->orWhereRaw('FIND_IN_SET('.$emp->id.',requests.interviewer)')
                      ->orWhere('requests.recruiter', $emp->email);
             });
        }
        //sum of all resource request having proccessing status
        $collection->groupBy('request_team.id');
        return $collection;
    }

    /*
     * insert or update
     * @param array
     */
    public function insertOrUpdateRequest($input)
    {
        $dataSendMail = [];
        $input = array_filter($input, function($value) {
            return ($value !== null && $value !== false && $value !== '');
        });

        DB::beginTransaction();
        try {
            //Check auto approve request
            $configApprove = CoreConfigData::getAccountToEmail(1, CoreConfigData::AUTO_APPROVE_KEY);
            if (isset($input['request_id']) && $input['request_id']) {
                $rq = self::find($input['request_id']);
            } else {
                $rq = new ResourceRequest();
                $rq->status = getOptions::STATUS_INPROGRESS;
                //Get email address to send
                if ($configApprove == CoreConfigData::AUTO_APPROVE) {
                    $dataSendMail[] = [
                        'emailTo' => $input['recruiter'],
                        'name' => View::getNickName($input['recruiter']),
                    ];
                } else {
                    $tblEmployee = Employee::getTableName();
                    $tblTeam = Team::getTableName();
                    $tblTeamMember = TeamMember::getTableName();
                    $membersTeamHR = TeamMember::select("{$tblEmployee}.id", "{$tblEmployee}.name", "{$tblEmployee}.email")
                        ->join("{$tblEmployee}", "{$tblEmployee}.id", "=", "{$tblTeamMember}.employee_id")
                        ->join("{$tblTeam}", "{$tblTeam}.id", "=", "{$tblTeamMember}.team_id")
                        ->where("{$tblTeam}.type", Team::TEAM_TYPE_HR)
                        ->distinct("{$tblEmployee}.id")
                        ->get();

                    if (count($membersTeamHR)) {
                        foreach ($membersTeamHR as $key => $item) {
                            $dataSendMail[] = [
                                'emailTo' => $item->email,
                                'name' => View::getNickName($item->email),
                            ];
                        }
                    }
                }
                if (isset($input['interviewer'])) {
                    $interviewers = Employee::getEmpByIds($input['interviewer']);
                }
            }
            if ($configApprove == CoreConfigData::AUTO_APPROVE) {
                //Auto approve
                $rq->approve = getOptions::APPROVE_ON;
                $rq->recruiter = $input['recruiter'];
                $rq->type = getOptions::TYPE_RECRUIT;
            }
            if (isset($input['interviewer'])) {
                $input['interviewer'] = implode(',', $input['interviewer']);
            }
            if (isset($input['skill'])) {
                $rq->skill = $input['skill'];
            }
            if (isset($input['priority'])) {
                $rq->priority_id = $input['priority'];
            }
            $rq->fill($input);
            $rq->created_by = Permission::getInstance()->getEmployee()->id;
            if (!isset($input['end_working'])) {
                $rq->end_working = null;
            }
            //If no saler
            if (!isset($input['saler'])) {
                $rq->saler = null;
            }
            // If rq is_hot = 1 then reset last request has is_hot = 1 to is_hot = 0
            if (isset($input['is_hot']) && $input['is_hot']) {
                $this->resetRequestIsHot();
            }
            $rq->save();

            if (isset($input['request_id']) && $input['request_id']) {
                //delete old langs
                $langOld = self::getAllLangOfRequest($rq);
                $rq->requestLang()->detach($langOld);
                //delete old programming langs
                $proOld = self::getAllProgramOfRequest($rq);
                $rq->requestProgramming()->detach($proOld);
                if (isset($input['teams'])) {
                    //delete old teams
                    RequestTeam::removeOldTeamOfRequest($rq->id);
                }
                //delete old type candidate of request
                RequestType::removeOldTypeOfRequest($rq->id);
            } else { // Send mail if create
                $subjectMail = Lang::get('resource::view.【Intranet】Have new a resource request has been created');
                if (count($dataSendMail)) {
                    $template = 'resource::request.sendMail';
                    //set data notify
                    $arrayEmails = [];
                    foreach ($dataSendMail as $data) {
                        $data['href'] = route('resource::request.detail', ['id' => $rq->id]);
                        $data['title'] = $rq->title;
                        $data['deadline'] = Carbon::createFromFormat('Y-m-d', $rq->deadline)->format('d/m/Y');
                        $emailQueue = new EmailQueue();
                        $emailQueue->setTo($data['emailTo'])
                            ->setFrom('intranet@rikkeisoft.com', 'Rikkeisoft intranet')
                            ->setSubject($subjectMail)
                            ->setTemplate($template, $data);
                        $emailQueue->save();
                        $arrayEmails[] = $data['emailTo'];
                        //set notify
                        $employeeId = Employee::where('email', $data['emailTo'])->first(['id']);
                        if ($employeeId) {
                            \RkNotify::put(
                                $employeeId->id,
                                $subjectMail,
                                route('resource::request.detail', ['id' => $rq->id,
                                    'category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE,
                                    'content_detail' => RkNotify::renderSections($template, $data)
                                ])
                            );
                        }
                    }
                }
                $dataMailToRelatedPerson = [];
                if (isset($input['saler'])) {
                    $saler = Employee::getEmpById($input['saler']);
                    if ($saler) {
                        if (!isset($dataMailToRelatedPerson[$saler->email])) {
                            $dataMailToRelatedPerson[$saler->email] = [
                                'emailTo' => $saler->email,
                                'name' => View::getNickName($saler->email),
                            ];
                        }
                    }
                }
                if (isset($input['interviewer'])) {
                    foreach ($interviewers as $interviewer) {
                        if (isset($dataMailToRelatedPerson[$interviewer->email])) {
                            continue;
                        }
                        $dataMailToRelatedPerson[$interviewer->email] = [
                            'emailTo' => $interviewer->email,
                            'name' => View::getNickName($interviewer->email),
                        ];
                    }
                }
                if (count($dataMailToRelatedPerson)) {
                    $template = 'resource::request.send_mail_related_person';
                    //set data notify
                    $arrayEmails = [];
                    foreach ($dataMailToRelatedPerson as $data) {
                        $data['href'] = route('resource::request.detail', ['id' => $rq->id]);
                        $data['title'] = $rq->title;
                        $data['deadline'] = Carbon::createFromFormat('Y-m-d', $rq->deadline)->format('d/m/Y');
                        $emailQueue = new EmailQueue();
                        $emailQueue->setTo($data['emailTo'])
                            ->setFrom('intranet@rikkeisoft.com', 'Rikkeisoft intranet')
                            ->setSubject($subjectMail)
                            ->setTemplate($template, $data);
                        $emailQueue->save();
                        $arrayEmails[] = $data['emailTo'];
                    }
                    //set notify
                    \RkNotify::put(
                        Employee::whereIn('email', $arrayEmails)->lists('id')->toArray(),
                        $subjectMail,
                        route('resource::request.detail', ['id' => $rq->id, 'category_id' => RkNotify::CATEGORY_HUMAN_RESOURCE])
                    );
                }
            }
            if (isset($input['languages'])) {
                $rq->requestLang()->attach($input['languages']);
            }
            if (isset($input['programs'])) {
                $rq->requestProgramming()->attach($input['programs']);
            }
            if (isset($input['teams'])) {
                $data = [];
                foreach ($input['teams'] as $teamId => $position) {
                    foreach ($position as $positionId => $number) {
                        $data[] = [
                            'request_id' => $rq->id,
                            'team_id' => $teamId,
                            'position_apply' => $positionId,
                            'number_resource' => $number
                        ];
                    }
                }
                RequestTeam::insertData($data);
            }
            if (isset($input['typecandidate'])) {
                $data = [];
                foreach ($input['typecandidate'] as $typeId) {
                    $data[] = [
                        'request_id' => $rq->id,
                        'type' => $typeId,
                    ];
                }
                RequestType::insertData($data);
            }
            DB::commit();

            return $rq->id;
        } catch (QueryException $ex) {
            DB::rollback();
            throw $ex;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public function approve($input)
    {
        DB::beginTransaction();
        try {
            $rq = self::find($input['request_id']);
            $rq->fill($input);
            $rq->save();

            if (isset($input['recruiter'])) {
                $data = [
                    "name" => View::getNickName($input['recruiter']),
                    "href" => route('resource::request.detail',['id' => $rq->id])
                ];
                //Save mail to queue
                $template = 'resource::request.sendMail';
                $emailQueue = new EmailQueue();
                $emailQueue->setTo($input['recruiter'])
                    ->setFrom('intranet@rikkeisoft.com', 'Rikkeisoft intranet')
                    ->setSubject(Lang::get('resource::view.【Intranet】Have new a resource request has been created'))
                    ->setTemplate($template, $data);
                //set notify
                $recruiter = Employee::where('email', $input['recruiter'])->first();
                if ($recruiter) {
                    $emailQueue->setNotify($recruiter->id, null, $data['href'], ['category_id', RkNotify::CATEGORY_HUMAN_RESOURCE]);
                }
                $emailQueue->save();
            }

            DB::commit();

            return $rq->id;
        } catch (QueryException $ex) {
            DB::rollback();
            throw $ex;
        } catch (Exception $ex) {
            DB::rollback();
            throw $ex;
        }
    }

    public static function getAllChannelRequest($rq)
    {
        return $rq->requestChannel;
    }

    public static function getProgramByRequest($rq)
    {
        return $rq->requestProgramming;
    }

    /*
     * Get all langs of request
     * @parram ResourceRequest $rq
     * @return array
     */
    public static function getAllLangOfRequest($rq)
    {
        $langs = array();
        foreach ($rq->requestLang as $lang) {
            array_push($langs, $lang->id);
        }
        return $langs;
    }

    /*
     * Get all programming languages of request
     * @parram ResourceRequest $rq
     * @return array
     */
    public static function getAllProgramOfRequest($rq)
    {
        $pros = array();
        foreach ($rq->requestProgramming as $pro) {
            array_push($pros, $pro->id);
        }
        return $pros;
    }

    /*
     * Get all team_id of request
     * @parram ResourceRequest $rq
     * @return array
     */
    public static function getAllTeamOfRequest($rq)
    {
        $teams = array();
        foreach ($rq->requestTeam as $team) {
            array_push($teams, $team->id);
        }
        return $teams;
    }


    /*
     * Get all langs of request
     * @parram ResourceRequest $rq
     * @return array
     */
    public static function getAllTypeOfRequest($rq)
    {
        $typeArray = [];
        $rqType = RequestType::where('request_id', $rq->id)->get();
        foreach ($rqType as $type) {
            $typeArray[] = $type->type;
        }
        return $typeArray;
    }

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    public function getAllList($where=null, $orWhere=null, $isCheckExpired = false)
    {
        $rq = self::orderBy('requests.id', 'asc')
                ->join('request_team', 'request_team.request_id', '=', 'requests.id');
        if (!$isCheckExpired) {
            $rq->whereDate('requests.deadline', '>=', Carbon::now()->format('Y-m-d'));
        }
        if ($where) {
            $rq->where($where);
        }
        if ($orWhere) {
            $rq->orWhere($orWhere);
        }
        $rq->select(
                'requests.id',
                'requests.title',
                'requests.deadline',
                'request_team.team_id',
                DB::raw("(SELECT GROUP_CONCAT(concat( team_id, ',', position_apply ) SEPARATOR ';') 
                            FROM request_team 
                            WHERE request_team.request_id = requests.id
                        ) AS team_pos"));
        $rq->groupBy('requests.id');
        $rq = $rq->get();

        return $rq;
    }

    /**
     * Get request by id, store cache
     * @param type $id
     * @return ResourceRequest
     */
    public static function getRequestById($id)
    {
        return self::leftJoin('request_team', 'request_team.request_id', '=', 'requests.id')
                ->leftJoin('teams','teams.id','=','request_team.team_id')
                ->where('requests.id', $id)
                ->select(
                        "requests.*",
                        DB::Raw("(select group_concat(distinct teams.name SEPARATOR ', ') from request_team inner join teams on  teams.id = request_team.team_id where request_team.request_id = requests.id) as team_name")
                        )
                ->first();
    }

    /**
     * Close requests deadline < curdate
     */
    public static function closeRequest()
    {
        self::where('deadline', '<', date('Y-m-d'))
              ->update(['status' => getOptions::STATUS_CLOSE]);
    }

    /**
     * Check request can or can't edit
     * @param ResourceRequest $request
     * @return boolean
     */
    public static function checkCanEdit($request)
    {
        if (Permission::getInstance()->isScopeCompany() || $teamsId = Permission::getInstance()->isScopeTeam()) {
            return true;
        }

        $curEmp = Permission::getInstance()->getEmployee();
        return $request->created_by == $curEmp->id;
    }

    /**
     * search by ajax
     *
     * @param string $title
     * @param array $config
     * @return ResourceRequest collection
     */
    public static function searchAjax($title, array $config = [])
    {
        $result = [];
        $arrayDefault = [
            'page' => 1,
            'limit' => 20,
            'typeExclude' => null
        ];
        $config = array_merge($arrayDefault, $config);
        $rqTbl = self::getTableName();
        $collection = self::select([$rqTbl . '.id', $rqTbl . '.title as text', DB::raw('1 as loading')])
                    ->where($rqTbl . '.status', '<>', getOptions::STATUS_CANCEL)
                    ->where($rqTbl . '.title', 'LIKE', '%' . $title . '%')
                    ->orderBy($rqTbl . '.title');

        if (isset($config['status'])) {
            $collection->where($rqTbl.'.status', $config['status']);
        }
        if (isset($config['approve'])) {
            $collection->where($rqTbl.'.approve', $config['approve']);
        }
        if (isset($config['published'])) {
            $collection->where($rqTbl.'.published', $config['published']);
        }
        if (isset($config['not_enough_amount'])) {
            $collection->leftJoin(Candidate::getTableName() . ' as cdd', function ($join) use ($rqTbl) {
                $join->on($rqTbl . '.id', '=', 'cdd.request_id')
                        ->whereIn('cdd.status', [getOptions::END, getOptions::WORKING])
                        ->whereNull('cdd.deleted_at');
            })
            ->havingRaw('COUNT(DISTINCT(cdd.id)) < (SELECT SUM(number_resource) FROM '. RequestTeam::getTableName() .' WHERE request_id = '. $rqTbl .'.id)')
            ->groupBy($rqTbl. '.id');
        }

        self::pagerCollection($collection, $config['limit'], $config['page']);
        $result['items'] = $collection->items();
        $result['total_count'] = $collection->total();
        $result['incomplete_results'] = true;
        return $result;
    }

    /**
     * Get requests name by requests id
     *
     * @param array $ids array request_id
     * @return array
     */
    public function getTitlesByIds($ids)
    {
        $requests = self::whereIn('id', $ids)->select('title')->get();
        $result = [];
        foreach ($requests as $request) {
            $result[] = trim($request->title);
        }

        return $result;
    }

    static function getTeamOfRequest($requestStatus = null)
    {
        $result = self::join("request_team", "request_team.request_id", "=", "requests.id");
        if ($requestStatus) {
            $result->where('requests.status', $requestStatus);
        }
        $result->where('requests.approve', getOptions::APPROVE_ON)
                ->where('requests.type', getOptions::TYPE_RECRUIT);
        $result->select('request_team.*');
        return $result->get();
    }

    /**
     * Get collection
     *
     * @return collection
     */
    public function getCollection($collection)
    {
        return $collection->get();
    }

    /**
     * API get data from intranet
     * @return mixed
     */
    public function getDataRequest($id = null)
    {
        $collection =  self::join('request_team', 'request_team.request_id', '=', 'requests.id')
            ->join('teams', 'teams.id', '=', 'request_team.team_id')
            ->leftJoin('request_programming', 'request_programming.request_id', '=', 'requests.id')
            ->leftJoin('programming_languages', 'request_programming.programming_id', '=', 'programming_languages.id')
            ->leftJoin('work_places', 'work_places.id', '=', 'requests.location')
            ->select('request_team.*', 'teams.name', 'teams.id as team_id', 'requests.team_id', 'requests.request_date',
                'requests.deadline', 'requests.salary', 'requests.description', 'requests.benefits', 'requests.job_qualifi',
                'requests.title', 'programming_languages.id as language', 'requests.id as id', 'requests.location'
            );
        if ($id) {
            $collection->where('id', $id);
        }
        return $collection->get();
    }

    /**
     * Reset Request Is Hot
     * @return mixed
     */
    public function resetRequestIsHot()
    {
        if (self::where('is_hot', 1)->count() > 5) {
            $lastHotRequest = self::where('is_hot', 1)->orderBy('updated_at', 'ASC')->first();
            if ($lastHotRequest) {
                $lastHotRequest->is_hot = 0;
                $lastHotRequest->save();
            }
            return $lastHotRequest;
        }
    }

    /**
     * API get data request approved from intranet
     */
    public function getDataRequestApproved()
    {
        $collection = self::select(
            'requests.id as request_id',
            'requests.is_hot as hot',
            'requests.title as name',
            'requests.deadline as expired',
            'requests.request_date as start_date',
            'requests.location as place',
            'requests.salary',
            'requests.description',
            'requests.benefits',
            'requests.job_qualifi as qualifications',
            'requests.status as status_request',
            'request_team.position_apply',
            'request_team.number_resource',
            DB::raw("group_concat(distinct request_programming.programming_id separator ',') as programs"),
            DB::raw("group_concat(distinct request_type.type separator ',') as types")
        )
            ->leftJoin('request_team', 'request_team.request_id', '=', 'requests.id')
            ->leftJoin('request_programming', 'request_programming.request_id', '=', 'requests.id')
            ->leftJoin('request_type', 'request_type.request_id', '=', 'requests.id')
            ->where('requests.approve', 'REGEXP', addslashes(getOptions::APPROVE_ON));

        $collection->groupBy('requests.id');
        return $collection->get();
    }

    public function formatDataRequestApproved($dataRequest)
    {
        $result = array();
        foreach ($dataRequest as $key => $rq) {
            $result[$key] = $rq;
            $result[$key]['slug'] = str_slug($rq['name']) . time();
            $result[$key]['short_description'] = str_limit(strip_tags($rq['description']), 300);
            $result[$key]['programs'] = !empty($rq['programs']) ? explode(',', $rq['programs']) : [];
            $result[$key]['types'] = !empty($rq['types']) ? explode(',', $rq['types']) : [];
            $result[$key]['positions'] = array(
                'id' => $rq['position_apply'],
                'number' => $rq['number_resource'],
            );
            unset($result[$key]['position_apply']);
            unset($result[$key]['number_resource']);
        }
        return $result;
    }
}

