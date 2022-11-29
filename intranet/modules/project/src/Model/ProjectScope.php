<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class ProjectScope extends CoreModel
{
    protected $table = 'project_scope';
    protected $fillable = ['proj_scope', 'is_other_type'];

    CONST SCOPE_REQUIREMENT = 1;
    CONST SCOPE_DESIGN = 2;
    CONST SCOPE_CODING = 3;
    CONST SCOPE_UT = 4;
    CONST SCOPE_IT = 5;
    CONST SCOPE_ST = 6;
    CONST SCOPE_OTHER = 7;

    public static function labelScope()
    {
        return [
            self::SCOPE_REQUIREMENT => "Requirement",
            self::SCOPE_DESIGN => "Design",
            self::SCOPE_CODING => "Coding",
            self::SCOPE_UT => "UT",
            self::SCOPE_IT => "IT",
            self::SCOPE_ST => "ST",
            self::SCOPE_OTHER => "Other"
        ];
    }

    public static function getAllScope()
    {
        return [self::SCOPE_REQUIREMENT, self::SCOPE_DESIGN, self::SCOPE_CODING, self::SCOPE_UT, self::SCOPE_IT, self::SCOPE_ST, self::SCOPE_OTHER];
    }
}