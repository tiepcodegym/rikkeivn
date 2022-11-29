<?php
namespace Rikkei\Resource\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\Input;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\View\CacheHelper;
use Rikkei\Core\View\Form;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\View\Config;
use Rikkei\Resource\Model\RequestChannel;
use Rikkei\Resource\View\getOptions;
use Rikkei\Resource\Model\Candidate;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class Channels extends CoreModel
{
    
    protected $table = 'recruit_channel';
    
    const KEY_CACHE = 'recruit_channel';
    const KEY_LIST = 'channel_list';
    const KEY_DETAIL = 'detail';
    const PRESENTER_YES = 1;
    const PRESENTER_NO = 0;
    const PRICE = ',';
    // Trạng thái kênh tuyển dụng
    const ENABLED = 1;
    const DISABLED = 2;
    // Loại chi phí
    const COST_FIXED = 1;
    const COST_CHANGE = 0;

    use SoftDeletes;
    
    /**
     *  store this object
     * @var object
     */
    protected static $instance;
    
    protected $fillable = ['name', 'created_at', 'updated_at', 'cost', 'is_presenter', 'status', 'type', 'color'];

    /**
     * get list channel isActive
     * @return objects
     */
    public function getList()
    {
        if ($items = CacheHelper::get(self::KEY_LIST . date('Ymh'))) {
            return $items;
        }
        $channelTbl = self::getTableName();
        $channelFeesTbl = ChannelFee::getTableName();
        $items = self::select($channelTbl . '.id', $channelTbl . '.name', $channelTbl . '.is_presenter', $channelTbl . '.type')
            ->leftJoin($channelFeesTbl, $channelFeesTbl . '.channel_id', '=', $channelTbl . '.id')
            ->where($channelTbl . '.status', Channels::ENABLED)
            ->orderBy('name', 'asc')
            ->groupBy($channelTbl . '.id')
            ->get();
        CacheHelper::put(self::KEY_LIST . date('Ymh'), $items);

        return $items;
    }
    
    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    public static function getChannelById($id) {
        if ($channel = CacheHelper::get(self::KEY_CACHE, self::KEY_DETAIL . $id)) {
            return $channel;
        }
        $channelTbl = self::getTableName();
        $channel = self::with('channelFees')
            ->where($channelTbl . '.id', $id)
            ->withTrashed()
            ->first();

        CacheHelper::put(self::KEY_CACHE, $channel, self::KEY_DETAIL . $id);
        return $channel;
    }

    /*
     * get collection to show grid data
     * @return collection model
     */
    public static function getGridData()
    {
        $filter = Form::getFilterData();
        $pager = Config::getPagerData();
        $candidateTableName = Candidate::getTableName();
        $empTableName= Employee::getTableName();
        $channelTableName = self::getTableName();
        $leave = getOptions::LEAVED_OFF;
        $working = getOptions::WORKING;
        $preparing = getOptions::PREPARING;
        $now = Carbon::now();
        $startTime = isset($filter['search']['candidates.created_at']) ? $filter['search']['candidates.created_at'] : $now->firstOfMonth()->format('Y-m-d');
        $endTime = isset($filter['search']['candidates.end_at']) ? $filter['search']['candidates.end_at'] : $now->lastOfMonth()->format('Y-m-d');
        $endTime  = date('Y-m-d',strtotime($endTime . "+1 days"));
        $sql = "(SELECT COUNT(DISTINCT {$candidateTableName}.id)"
            . " FROM {$candidateTableName}"
            . " LEFT JOIN {$empTableName} ON {$empTableName}.id = {$candidateTableName}.employee_id"
            . " WHERE ({$candidateTableName}.status = {$leave} OR {$candidateTableName}.status = {$working} OR {$candidateTableName}.status = {$preparing})"
            . " AND {$candidateTableName}.channel_id = {$channelTableName}.id"
            . " AND {$empTableName}.join_date >= '{$startTime}'"
            . " AND {$empTableName}.join_date < '{$endTime}'"
            . " AND {$candidateTableName}.deleted_at IS NULL"
            . " ) as count";
        $sql2 = "(SELECT SUM({$candidateTableName}.cost)"
            . " FROM {$candidateTableName}"
            . " LEFT JOIN {$empTableName} ON {$empTableName}.id = {$candidateTableName}.employee_id"
            . " WHERE ({$candidateTableName}.status = {$leave} OR {$candidateTableName}.status = {$working} OR {$candidateTableName}.status = {$preparing})"
            . " AND {$candidateTableName}.channel_id = {$channelTableName}.id"
            . " AND {$empTableName}.join_date >= '{$startTime}'"
            . " AND {$empTableName}.join_date < '{$endTime}'"
            . " AND {$candidateTableName}.deleted_at IS NULL"
            . " ) as cost";
        $collection = Self::with('channelFees')
            ->select
            (
                "{$channelTableName}.id",
                "{$channelTableName}.name",
                "{$channelTableName}.is_presenter",
                "{$channelTableName}.type",
                "{$channelTableName}.status",
                "{$channelTableName}.created_at",
                "{$channelTableName}.updated_at",
                DB::raw($sql),
                DB::raw($sql2)
            )
            ->groupBy("{$channelTableName}.id")
            ->orderBy("{$channelTableName}.status", 'ASC')
            ->orderBy($pager['order'], $pager['dir']);

        $collection = self::filterGrid($collection);
        $collection = self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    public static function getContentTable($request) {
        $channels = self::getInstance()->getList();
        $channelsOfRequest = self::getInstance()->getChannels($request->id);
        
        return view('resource::request.tab_detail.recruiment_cost', 
                    [
                        'channels' => $channels, 
                        'channelsOfRequest' => $channelsOfRequest, 
                        'request' => $request,
                    ])->render();
    }

    /**
     * Channels of request
     * @param $requestId
     * @return mixed
     */
    public function getChannels($requestId) {
        $channelTableName = self::getTableName();
        $requestChannelTableName = RequestChannel::getTableName();
        return self::join("{$requestChannelTableName}", "{$channelTableName}.id", "=", "{$requestChannelTableName}.channel_id")
                    ->where("{$requestChannelTableName}.request_id", $requestId)
                    ->select("{$channelTableName}.*", 
                            "{$requestChannelTableName}.url",
                            "{$requestChannelTableName}.cost",
                            "{$requestChannelTableName}.id AS rc_id")
                    ->withTrashed()
                    ->get();
    }

    /**
     * Channels candidate of request
     * @param $requestId
     * @return mixed
     */
    public function getChannelsCandidate($requestId) {
        $channelTableName = self::getTableName();
        $candidateTableName = Candidate::getTableName();
        $candidateRequestTableName = CandidateRequest::getTableName();
        $draft = getOptions::DRAFT;
        $contacting = getOptions::CONTACTING;
        $entryTest = getOptions::ENTRY_TEST;
        $interviewing = getOptions::INTERVIEWING;
        $offering = getOptions::OFFERING;
        $pass = getOptions::END;
        $fail = getOptions::FAIL;
        $result = Candidate::leftJoin("{$channelTableName}", "{$channelTableName}.id", "=", "{$candidateTableName}.channel_id")
                    ->leftJoin("{$candidateRequestTableName}", "{$candidateRequestTableName}.candidate_id", "=", "{$candidateTableName}.id")
                    ->where("{$candidateRequestTableName}.request_id", $requestId)
                    ->select
                     (
                         "{$channelTableName}.name",
                         DB::raw($this->getSqlCountByStatus($requestId, $draft, "countDraft")),
                         DB::raw($this->getSqlCountByStatus($requestId, $contacting, "countContact")),  
                         DB::raw($this->getSqlCountByStatus($requestId, $entryTest, "countEntryTest")),          
                         DB::raw($this->getSqlCountByStatus($requestId, $interviewing, "countInterview")),  
                         DB::raw($this->getSqlCountByStatus($requestId, $offering, "countOffer")),
                         DB::raw($this->getSqlCountByStatus($requestId, $pass, "countPass")),  
                         DB::raw($this->getSqlCountByStatus($requestId, $fail, "countFail"))        
                     )
                    ->groupBy("{$channelTableName}.id")
                    ->orderBy("{$channelTableName}.name", "ASC");
        
        if (Candidate::isUseSoftDelete()) {
            $result->whereNull("{$candidateTableName}.deleted_at");
        }
        return $result->get();
                   
    }
    
    private function getSqlCountByStatus($requestId, $status, $alias) 
    {
        $channelTableName = self::getTableName();
        $candidateTableName = Candidate::getTableName();
        $pass = getOptions::END;
        $fail = getOptions::FAIL;
        $working = getOptions::WORKING;
        switch ($status) {
            case $pass:
                $sql = "(SELECT COUNT(DISTINCT {$candidateTableName}.id) "
                    . " FROM candidates "
                    . " WHERE candidates.request_id = $requestId "
                        . " AND (candidates.status = {$pass} OR candidates.status = {$working})"
                        . " AND candidates.channel_id = {$channelTableName}.id";
                break;
            case $fail:
                $sql = "(SELECT COUNT(DISTINCT {$candidateTableName}.id) "
                    . " FROM candidates INNER JOIN candidate_request on candidates.id = candidate_request.candidate_id "
                    . " WHERE ((candidate_request.request_id = $requestId AND candidates.status = {$fail}) "
                                . "OR (candidates.id IN (SELECT id FROM {$candidateTableName} WHERE id IN (SELECT candidate_id FROM candidate_request WHERE request_id = {$requestId}) and status = {$pass} and request_id <> {$requestId})))"
                        . " AND candidates.channel_id = {$channelTableName}.id";
                break;
            default:
                $sql = "(SELECT COUNT(DISTINCT {$candidateTableName}.id) "
                    . " FROM candidates INNER JOIN candidate_request on candidates.id = candidate_request.candidate_id "
                    . " WHERE candidate_request.request_id = $requestId "
                        . " AND candidates.status = {$status}"
                        . " AND candidates.channel_id = {$channelTableName}.id";
        }
        
        if (Candidate::isUseSoftDelete()) {
            $sql .= " AND candidates.deleted_at IS NULL";
        }   
        
        $sql .= " ) as $alias";
        return $sql;
    }
    
    /**
    * get id chanels from name chanel
    */
    public static function getIdChanel($value) {
        $valueChannel = trim($value);
        $idChannel = self::where('name',$value)->select('id','is_presenter')->first();
        if ($idChannel) {
            return $idChannel;
        }
        return null;
    }

    /**
     * rewrite save model
     * 
     * @param array $options
     */
    public function save(array $options = array()) {
        try {
            CacheHelper::forget(self::KEY_LIST . date('Ymh'));
            CacheHelper::forget(self::KEY_CACHE);
            return parent::save($options);
        } catch (Exception $ex) {

        }
        
    }

    public function channelFees()
    {
        return $this->hasMany('Rikkei\Resource\Model\ChannelFee', 'channel_id', 'id');
    }

    /**
     * get channels by array channel id
     *
     * @param null|array $ids
     * @param string $selectedFields
     * @return mixed
     */
    public function getChannelsByIds($ids = null, $selectedFields = '[*]')
    {
        $collection = self::select($selectedFields);
        if (is_array($ids)) {
            $collection->whereIn('id', $ids);
        }
        return $collection->get();
    }

    /**
     * get list recommend candidates by channel id with status working or leaved_off
     * @param $id
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public static function listRecommendByChannelId($id, $startTime, $endTime)
    {
        $channelTableName = self::getTableName();
        $candidateTableName = Candidate::getTableName();
        $empTableName = Employee::getTableName();
        $endTime  = date('Y-m-d',strtotime($endTime . "+1 days"));

        return self::select(
            $channelTableName . '.name',
            $channelTableName . '.type',
            $empTableName . '.name as employee_name',
            $empTableName . '.employee_code',
            $empTableName . '.email',
            $empTableName . '.join_date',
            $candidateTableName . '.cost')
            ->leftjoin($candidateTableName, $channelTableName . '.id', "=", $candidateTableName . '.channel_id')
            ->leftjoin($empTableName, $empTableName . '.id', "=", $candidateTableName . '.employee_id')
            ->where($channelTableName . '.id', $id)
            ->whereDate($empTableName . '.join_date','>=', $startTime)
            ->whereDate($empTableName . '.join_date', '<', $endTime)
            ->whereIn($candidateTableName . '.status', [getOptions::WORKING, getOptions::LEAVED_OFF, getOptions::PREPARING])
            ->whereNull($candidateTableName . '.deleted_at')
            ->orderBy($candidateTableName . '.id', 'desc')
            ->groupBy($candidateTableName . '.id')
            ->get();
    }

    /**
     * monthly recruitment report
     *
     * @param array $channelIds
     * @param string $month
     * @return collection
     */
    public function getMonthlyReportList(array $channelIds, $month)
    {
        $tblCandidate = Candidate::getTableName();
        $tblChannel = self::getTableName();
        $collection = self::leftJoin($tblCandidate, "{$tblCandidate}.channel_id", '=', "{$tblChannel}.id")
            ->whereIn("{$tblChannel}.id", $channelIds)
            ->where(function ($query) use ($tblCandidate, $month) {
                $query
                    ->where(function ($subQuery) use ($tblCandidate, $month) {
                        $subQuery->where("{$tblCandidate}.offer_result", getOptions::RESULT_PASS)
                            ->where(DB::raw("DATE_FORMAT({$tblCandidate}.offer_date, '%Y-%m')"), $month);
                    })
                    ->orWhere(function ($subQuery) use ($tblCandidate, $month) {
                        $subQuery->whereIn("{$tblCandidate}.interview_result", [getOptions::RESULT_PASS, getOptions::RESULT_FAIL])
                            ->whereRaw('CASE'
                                . " WHEN {$tblCandidate}.interview2_plan IS NOT NULL AND DATE({$tblCandidate}.interview2_plan) <> '0000-00-00'"
                                . " THEN DATE_FORMAT({$tblCandidate}.interview2_plan, '%Y-%m') = '{$month}'"
                                . " ELSE DATE_FORMAT({$tblCandidate}.interview_plan, '%Y-%m') = '{$month}'"
                                . ' END'
                            );
                    });
            })
            ->select([
                "{$tblChannel}.name",
                "{$tblChannel}.color",
                "{$tblCandidate}.id",
                "{$tblCandidate}.channel_id",
                "{$tblCandidate}.recruiter",
                "{$tblCandidate}.interview_result",
                "{$tblCandidate}.offer_result",
                DB::raw("DATE_FORMAT({$tblCandidate}.offer_date, '%Y-%m') AS offer_month"),
                DB::raw('(CASE'
                    . " WHEN {$tblCandidate}.interview2_plan IS NOT NULL AND DATE({$tblCandidate}.interview2_plan) <> '0000-00-00'"
                    . " THEN DATE_FORMAT({$tblCandidate}.interview2_plan, '%Y-%m')"
                    . " ELSE DATE_FORMAT({$tblCandidate}.interview_plan, '%Y-%m')"
                    . ' END) AS interview_month'
                ),
            ]);
        return $collection->get();
    }
}
