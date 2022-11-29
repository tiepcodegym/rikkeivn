<?php

namespace Rikkei\Team\Model;

use Exception;
use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Employee;

class SkillSheetComment extends CoreModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'skillsheet_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content', 'employee_id', 'created_by'];
    public $timestamps = true;

    const TYPE_FEEDBACK = 1;

    /**
     * get grid data comment of skillsheet.
     *
     * @param $employeeId: id of employee skillsheet.
     */
    public static function getGridData($employeeId)
    {
        $pager = Config::getPagerDataQuery();
        $empTable = Employee::getTableName();
        $commentTable = self::getTableName();

        $collection = self::select(
                                "{$commentTable}.created_at",
                                'content',
                                "{$empTable}.name",
                                "{$empTable}.email",
                                "{$commentTable}.type"
                                )
                            ->leftJoin($empTable, "{$empTable}.id", '=', "{$commentTable}.created_by")
                            ->leftJoin("{$empTable} as empTable2", "empTable2.id", '=', "{$commentTable}.employee_id")
                            ->orderBy("{$commentTable}.created_at", 'desc')
                            ->where("empTable2.id", '=', $employeeId);

        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
}
