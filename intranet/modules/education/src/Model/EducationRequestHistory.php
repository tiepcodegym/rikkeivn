<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Core\Model\User;

class EducationRequestHistory extends CoreModel
{
    protected $table = 'education_request_histories';
    protected $fillable = [
        'id', 'education_request_id', 'hr_id', 'status', 'description'
    ];
    public $timestamps = true;

    public function hr()
    {
        return $this->belongsTo(User::class, 'hr_id', 'employee_id');
    }
}
