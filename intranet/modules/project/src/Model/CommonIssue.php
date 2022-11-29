<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class CommonIssue extends CoreModel
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'common_issue';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['issue_type', 'issue_source', 'issue_description', 'cause', 'action'];

    /*
     * get all issue
     * @param int
     * @return collection
     */
    public static function getAllCommonIssue($columns = ['*'], $statusList = null, $typeRisk = null)
    {
        return self::select($columns);
    }

    public static function getAllCommonIssueExport($columns = ['*'], $conditions = [], $order = 'common_issue.id', $dir = 'desc')
    {
        $issue = self::select($columns);
        $issue->orderBy($order, $dir);
        return $issue;
    }
    
    /**
     * Save common issue
     * 
     * @param array $data
     * @return boolean
     */
    public static function store($data) {
        if (isset($data['id'])) {
            $commonIssueId = $data['id'];
            $commonIssue = CommonIssue::find($commonIssueId);
        } else {
            $commonIssue = new CommonIssue();
        }
        DB::beginTransaction();
        try {
            $commonIssue->fill($data);
            $commonIssue->save();
            DB::commit();
            return $commonIssue;
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
        
    }

    /*
     * delete common issue
     * @param array
     * @return boolean
     */
    public static function deleteCommonIssue($input)
    {
        $risk = self::find($input['id']);
        if ($risk) {
            if ($risk->delete()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Risk by id
     * 
     * @param int $riskId
     * @return Risk 
     */
    public static function getById($riskId)
    {
        $item = self::where('id', $riskId)
            ->select('*')
            ->first();
        return $item;
    }
}