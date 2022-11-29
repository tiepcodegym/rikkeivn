<?php

namespace Rikkei\News\Model;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\EmployeeTeamHistory;
use Rikkei\Team\Model\Team;

class ViewManage extends CoreModel
{
    const LIKE = 1;
    
    protected $table = 'blog_view_manage';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['employee_id', 'post_id'];

    /**
    * get total view
    */ 
    public static function getTotalView($postId)
    {
    	return self::where('post_id','=',$postId)->count();
    }

    /**
    * user view post
    */ 
    public static function view($postId, $userId)
    {
    	$countOldView = self::select(['employee_id'])
            ->where('post_id', '=', $postId)
            ->where('employee_id', '=', $userId)
            ->first();
        if($countOldView) {
            return true;
        }
        self::create([
            'employee_id' => $userId,
            'post_id' => $postId
        ]);
        BlogMeta::increCount($postId, 'view', 1);
    }

    /**
     * Get view by branch code
     * @return array
     */
    public static function getViewByBranch()
    {
        $viewTbl = self::getTableName();
        $eplHisTbl = EmployeeTeamHistory::getTableName();
        $teamTbl = Team::getTableName();
        $branch = Team::listPrefixByRegion();
        $response = array();
        $sql = '';

        foreach ($branch as $location) {
            $sql .= " SELECT COUNT({$viewTbl}.employee_id) AS count, post_id, branch_code
                FROM
                    {$viewTbl}
                        LEFT JOIN
                    {$eplHisTbl} ON {$eplHisTbl}.employee_id = {$viewTbl}.employee_id
                        LEFT JOIN
                    {$teamTbl} ON {$teamTbl}.id = {$eplHisTbl}.team_id
                WHERE
                    branch_code = '{$location}' AND is_working = 1
                GROUP BY post_id UNION";
        }
        $sql = rtrim($sql, 'UNION');
        $data = DB::select(DB::raw($sql));
        foreach ($data as $value) {
            $response[$value->branch_code][$value->post_id] = $value->count;
        }

        return $response;
    }
}
