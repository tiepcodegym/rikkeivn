<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectKind extends ProjectWOBase
{
    use SoftDeletes;

    const KIND_OFFSHORE_VN = 1;
    const KIND_OFFSHORE_JP = 2;
    const KIND_OFFSHORE_EN = 3;
    const KIND_ONSITE_JP = 4;
    const KIND_INTERNAL = 5;
    const KIND_OTHER = 6;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'project_kind';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['kind_name', 'is_other_type', 'status'];
}
