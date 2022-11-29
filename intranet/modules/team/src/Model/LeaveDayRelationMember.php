<?php

namespace Rikkei\Team\Model;

use Rikkei\Core\Model\CoreModel;

class LeaveDayRelationMember extends CoreModel
{

    protected $table = 'leave_day_relation_member';

    protected $fillable = ['leave_day_registers_id', 'employee_relationship_id', 'status'];

    public static function getMemberRela($memberIds)
    {
        return self::whereIn('employee_relationship_id', $memberIds)->select('*')->get();
    }
}