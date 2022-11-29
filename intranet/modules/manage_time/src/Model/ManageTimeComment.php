<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManageTimeComment extends CoreModel
{
    protected $table = 'manage_time_comments';

    use SoftDeletes;

    /**
     * [getReasonDisapprove: get list reason disapprove]
     * @param  [int|null] $registerId
     * @param  [int|null] $type
     * @return [array]
     */
    public static function getReasonDisapprove($registerId = null, $type = null)
    {
        $registerCommentTable = self::getTableName();
        $registerCommentTableAs = $registerCommentTable;
    	$registerTable = SupplementRegister::getTableName();
        $registerTableAs = 'business_trip_register_table';
        $employeeTable = Employee::getTableName();
        $employeeTableAs = 'employee_table';
        $userTable = 'users';
        $userTableAs = 'user_table';

    	$comments = self::select(
                "{$registerCommentTableAs}.register_id as register_id", 
                "{$registerCommentTableAs}.comment as comment", 
                "{$registerCommentTableAs}.type as type", 
        		"{$registerCommentTableAs}.created_at as created_at",
                "{$employeeTableAs}.name as name",
                "{$userTableAs}.avatar_url as avatar_url"
            )->join("{$employeeTable} as {$employeeTableAs}", "{$employeeTableAs}.id", '=', "{$registerCommentTableAs}.created_by")
        	->leftJoin("{$userTable} as {$userTableAs}", "{$userTableAs}.employee_id", '=', "{$registerCommentTableAs}.created_by");

            if ($registerId) {
                $comments = $comments->where("{$registerCommentTableAs}.register_id", $registerId);
            }
            if ($type) {
                $comments = $comments->where("{$registerCommentTableAs}.type", $type);
            }
			$comments = $comments->orderBy("{$registerCommentTableAs}.created_at", 'DESC')->get();

    	return $comments;
    }
}