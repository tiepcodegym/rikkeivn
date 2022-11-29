<?php

namespace Rikkei\Statistic\Models;

use Rikkei\Core\Model\CoreModel;

/**
 * sum line of code of project
 */
class STProjLoc extends CoreModel
{
    protected $table = 'st_proj_loc';
    public $timestamps = false;
    protected $fillable = ['created_at', 'proj_id', 'value', 'team_id'];
}
