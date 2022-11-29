<?php

namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;

class EnrollmentAdvice extends CoreModel
{
    protected $table = 'enrollment_advice';
    protected $fillable = ['name', 'email', 'phone', 'language'];
    
    const STATE_OPEN = 2;
    const STATE_CLOSE = 1;
}
