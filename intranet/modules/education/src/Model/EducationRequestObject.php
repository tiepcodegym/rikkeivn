<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;

class EducationRequestObject extends CoreModel
{
    protected $fillable = [
        'education_request_id', 'education_object_id'
    ];
    protected $primaryKey = 'education_request_id';
    protected $table = 'education_request_objects';
    public $timestamps = false;
}
