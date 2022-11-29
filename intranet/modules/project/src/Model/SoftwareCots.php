<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class SoftwareCots extends CoreModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'software_costs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name_software', 'department', 'start_date', 'end_date', 'project_using', 'cost', 'average_cost'];
}
