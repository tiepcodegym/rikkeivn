<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\CoreModel;

class CronjobProjectAllocations extends CoreModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cronjob_project_allocations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['year', 'team_id', 'branch_code', 'allocation_serialize'];
}
