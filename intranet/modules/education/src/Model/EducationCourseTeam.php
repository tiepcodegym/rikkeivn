<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;
use Rikkei\Team\Model\Team;

class EducationCourseTeam extends CoreModel
{
    const ROLE_TEACHER = 2;
    protected $table = 'education_course_teams';
    protected $fillable = [
        'id', 'course_id', 'team_id'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}