<?php

namespace Rikkei\Recruitment\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Resource\Model\Candidate;
use Rikkei\Resource\Model\CandidateRequest;
use Rikkei\Resource\Model\ResourceRequest;
use Rikkei\Resource\Model\RequestTeam;
use Rikkei\Team\Model\Team;
use Rikkei\Resource\Model\CandidatePosition;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Rikkei\Resource\Model\CandidateProgramming;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\View\Form as FormView;
use Rikkei\Team\View\Permission;
use Rikkei\Resource\Model\CandidateTeam;
use Rikkei\Resource\View\getOptions;
use Carbon\Carbon;

class CddMailSent extends CoreModel
{
    protected $table = 'candidate_email_marketing';
    protected $fillable = ['candidate_id', 'request_id', 'sent_date', 'type'];

    const TYPE_MAIL_FOLLOW = 1;
    const TYPE_MAIL_BIRTHDAY = 2;
    // filter mail type in tab special in interested candidate list
    const TYPE_MAIL_MARKETING = 1;
    const TYPE_MAIL_INTERESTED = 2;
    // filter mail status in tab birthday in interested candidate list
    const STATUS_NOT_CMSN = 1;
    const STATUS_CMSN = 2;

    /*
     * get list candidate sent/not send email
     */
    public static function getCandidates($data = [])
    {
        $opts = [
            'select' => [
                'cdd.id',
                'cdd.interested',
                'cdd.fullname',
                'cdd.email',
                'cdd.status',
                'cdd.type',
                DB::raw('GROUP_CONCAT(DISTINCT(CONCAT(req.id, "||", req.title)) SEPARATOR ", ") as request_titles'),
                DB::raw('GROUP_CONCAT(DISTINCT(team_req.name) SEPARATOR ", ") as team_req_names'),
                DB::raw('GROUP_CONCAT(DISTINCT(cdd_pos.position_apply) SEPARATOR ", ") as position_applies'),
                DB::raw('GROUP_CONCAT(DISTINCT(cdd_prog.programming_id) SEPARATOR ", ") as prog_ids'),
                'cdd.offer_result',
                'cdd.interview_result',
                'cdd.test_result',
                'cdd.contact_result',
                'cdd.recruiter',
                'cdd.type',
                DB::raw('IFNULL(cdd.status_update_date, DATE(cdd.updated_at)) as updated_date'),
                'cdd_mail.sent_date',
            ],
            'is_sent' => null,
            'request_id' => null,
            'positions' => [],
            'dev_types' => [],
            'prog_ids' => [],
            'per_page' => 50,
            'page' => 1,
            'orderby' => [
                'cdd.created_at' => 'desc'
            ],
            'filter' => [],
        ];
        $opts = array_merge($opts, array_filter($data, function ($val) {
            return ($val !== null || $val !== '');
        }));
        $cddTbl = Candidate::getTableName();
        $cddRqTbl = CandidateRequest::getTableName();
        $cddPosTbl = CandidatePosition::getTableName();
        $cddProgTbl = CandidateProgramming::getTableName();
        $rqTeamTbl = RequestTeam::getTableName();
        $teamTbl = Team::getTableName();

        $collection = Candidate::withoutGlobalScope(new SoftDeletingScope)
            ->select($opts['select'])
            ->from($cddTbl . ' as cdd')
            ->leftJoin(self::getTableName() . ' as cdd_mail', function ($join) {
                $join->on('cdd_mail.candidate_id', '=', 'cdd.id')
                        ->whereNotNull('cdd_mail.request_id');
            })
            ->leftJoin($cddRqTbl . ' as cdd_req', 'cdd_req.candidate_id', '=', 'cdd.id')
            ->leftJoin($cddPosTbl . ' as cdd_pos', 'cdd_pos.candidate_id', '=', 'cdd.id')
            ->leftJoin($cddProgTbl . ' as cdd_prog', 'cdd_prog.candidate_id', '=', 'cdd.id')
            ->whereNull('cdd.deleted_at')
            ->groupBy('cdd.id');

        if ($opts['is_sent'] !== null) {
            if ($opts['is_sent']) {
                $collection->leftJoin(ResourceRequest::getTableName() . ' as req', 'req.id', '=', 'cdd_req.request_id')
                    ->whereNotNull('cdd_mail.sent_date');
            } else {
                $collection->where(function ($query) {
                    $query->whereNull('cdd_mail.candidate_id')
                        ->orWhereNull('cdd_mail.sent_date');
                });
                //only request progress, approved, published, and not enough amount
                $collection->join(DB::raw(
                    '(SELECT request.id, request.title, request.status, request.approve, request.published '
                    . 'FROM ' . ResourceRequest::getTableName() . ' as request '
                    . 'LEFT JOIN ' . Candidate::getTableName() . ' as scdd '
                        . 'ON scdd.request_id = request.id '
                        . 'AND scdd.status IN ('. getOptions::END .', '. getOptions::WORKING .') '
                        . 'AND scdd.deleted_at IS NULL '
                    . 'WHERE request.status = ' . getOptions::STATUS_INPROGRESS . ' '
                    . 'AND request.approve = ' . getOptions::APPROVE_ON . ' '
                    . 'AND request.published = ' . ResourceRequest::PUBLISHED . ' '
                    . 'GROUP BY request.id '
                    . 'HAVING COUNT(DISTINCT(scdd.id)) < ('
                        . 'SELECT SUM(number_resource) FROM ' . RequestTeam::getTableName()
                        . ' WHERE request_id = request.id'
                    . ')) AS req'
                ), 'req.id', '=', 'cdd_req.request_id');

                $collection->where(function ($query) {
                    //status update date null or between before 6 month - 1 year
                    $query->where(function ($subQuery) {
                        $subQuery->whereNotNull('cdd.status_update_date')
                            ->whereBetween('cdd.status_update_date', [
                                Carbon::now()->subYear()->toDateString(),
                                Carbon::now()->subMonthNoOverflow(6)->toDateString()
                            ]);
                    })
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNull('cdd.status_update_date')
                            ->whereBetween('cdd.updated_at', [
                                Carbon::now()->subYear()->toDateString(),
                                Carbon::now()->subMonthNoOverflow(6)->toDateString()
                            ]);
                    });
                });
            }

            $collection->leftJoin($rqTeamTbl . ' as req_team', 'req_team.request_id', '=', 'req.id')
                ->leftJoin($teamTbl . ' as team_req', 'team_req.id', '=', 'req_team.team_id');
        }
        $collection->whereRaw('cdd.id IN (SELECT MAX(id) FROM '. $cddTbl .' WHERE deleted_at IS NULL GROUP BY email)');

        //permission
        $currEmp = Permission::getInstance()->getEmployee();
        $routePermiss = 'recruitment::email.index';
        if (Permission::getInstance()->isScopeCompany(null, $routePermiss)) {
            //get all
        } elseif (($teamIds = Permission::getInstance()->isScopeTeam(null, $routePermiss))) {
            $teamIds = is_array($teamIds) ? $teamIds : [];
            $collection->leftJoin(CandidateTeam::getTableName() . ' as cdd_team', 'cdd_team.candidate_id', '=', 'cdd.id');
            $collection->where(function ($query) use ($teamIds, $currEmp) {
                $query->whereIn('cdd_team.team_id', $teamIds)
                    ->orWhere('cdd.created_by', $currEmp->id)
                    ->orWhere('cdd.found_by', $currEmp->id)
                    ->orWhere('cdd.recruiter', $currEmp->email )
                    ->orWhereRaw('cdd.interviewer IS NOT NULL AND FIND_IN_SET('. $currEmp->id .', cdd.interviewer)');
            });
        } else {
            $collection->where(function ($query) use ($currEmp) {
                $query->where('cdd.created_by', $currEmp->id)
                      ->orWhere('cdd.found_by', $currEmp->id)
                      ->orWhere('cdd.recruiter', $currEmp->email)
                      ->orWhereRaw('(cdd.interviewer IS NOT NULL AND FIND_IN_SET('. $currEmp->id .', cdd.interviewer))');
            });
        }

        $dataFilter = $opts['filter'];
        if ($requestId = FormView::getFilterData('except', 'request_id', $dataFilter)) {
            $collection->join($cddRqTbl . ' as ft_cdd_req', 'ft_cdd_req.candidate_id', '=', 'cdd.id')
                    ->where('ft_cdd_req.request_id', $requestId);
        }
        if ($positions = FormView::getFilterData('except', 'positions', $dataFilter)) {
            $collection->join($cddPosTbl . ' as ft_cdd_pos', 'ft_cdd_pos.candidate_id', '=', 'cdd.id')
                    ->whereIn('ft_cdd_pos.position_apply', $positions);
        }
        if ($devTypes = FormView::getFilterData('except', 'dev_types', $dataFilter)) {
            $collection->whereIn('cdd.type', $devTypes);
        }
        if ($progIds = FormView::getFilterData('except', 'prog_ids', $dataFilter)) {
            $collection->join($cddProgTbl . ' as ft_cdd_prog', 'ft_cdd_prog.candidate_id', '=', 'cdd.id')
                    ->whereIn('ft_cdd_prog.programming_id', $progIds);
        }
        if ($status = FormView::getFilterData('except', 'status', $dataFilter)) {
            Candidate::filterStatus($collection, $status, 'cdd');
        }
        if ($reqTeamName = FormView::getFilterData('except', 'req_team', $dataFilter)) {
            $collection->join($rqTeamTbl . ' as ft_rq_team', 'ft_rq_team.request_id', '=', 'req.id')
                ->join($teamTbl . ' as ft_team', 'ft_team.id', '=', 'ft_rq_team.team_id')
                ->where('ft_team.name', 'LIKE', '%' . $reqTeamName . '%');
        }
        //orderby
        foreach ($opts['orderby'] as $orderby => $order) {
            $collection->orderBy($orderby, $order);
        }
        //filter data
        self::filterGrid($collection, ['except'], $dataFilter, 'LIKE');
        //paginate
        return $collection->paginate($opts['per_page'], ['*'], 'page', $opts['page']);
    }

    /*
     * get list emails by ids
     */
    public static function getCandidateByIds($ids = [])
    {
        return Candidate::select('id', 'email', 'fullname')
                ->whereIn('id', $ids)
                ->get();
    }

    /*
     * create or update data
     */
    public static function createOrUpdateData($data)
    {
        if (!isset($data['candidate_id']) || !isset($data['request_id'])) {
            return false;
        }
        $item = self::where(array_only($data, ['candidate_id', 'request_id']))->first();
        if (!$item) {
            $item = self::create($data);
        } else {
            $data = array_except($data, ['candidate_id', 'request_id']);
            $item->update($data);
        }
        return $item;
    }

}
