<?php

namespace Rikkei\Test\Models;

use Rikkei\Core\View\View as CoreView;
use Rikkei\Core\Model\CoreModel;
use DB;

class Assignee extends CoreModel
{

    protected $table = 'ntest_assignee';
    const UPDATED_AT = null;
    const CREATED_AT = null;
    /*
     * The attributes that are mass assignable.
     */
    protected $fillable = ['id', 'test_id', 'team_id', 'employee_id', 'time_from', 'time_to'];

	/**
     * get assignee by test_id
     * @param Request $request
     * @return type
     */
    public static function getAssigneeByTestId($testId) {
        $colection = self::select('id', 'test_id', 'team_id', 'employee_id', 'time_from', 'time_to')
        			->where('test_id', $testId)
        			->get();
        return $colection;
    }

    // /**
    //  * get assignee by test_id, team_id
    //  * @param Request $request
    //  * @return type
    //  */
    // public function getAssigneeByTestId($testId) {
    //     $colection = self::select('team_id')
    //     			->where('test_id', $testId)
    //     			->get();
    //     return $colection;
    // }
}
