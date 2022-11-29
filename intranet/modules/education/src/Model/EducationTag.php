<?php

namespace Rikkei\Education\Model;

use Rikkei\Core\Model\CoreModel;

class EducationTag extends CoreModel
{
    protected $table = 'education_tags';
    protected $fillable = [
        'id', 'name'
    ];
    public $timestamps = false;

    /**
     * The education requests that belong to the tag.
     */
    public function educationRequests()
    {
        return $this->belongsToMany(EducationRequest::class, 'education_request_tag', 'tag_id', 'education_request_id');
    }
}
