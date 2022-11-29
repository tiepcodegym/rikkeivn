<?php

namespace Rikkei\Project\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use Rikkei\Core\Model\CoreModel;

class CronjobEmployeePoints extends CoreModel
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'cronjob_employee_points';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['employee_id', 'month', 'email', 'team_id', 'contract_type', 'point'];
    public $timestamps = true;

}
