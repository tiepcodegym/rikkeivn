<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;

class EducationType extends CoreModel
{
    protected $table = 'education_types';
    protected $fillable = [
        'id', 'code', 'name'
    ];
}
