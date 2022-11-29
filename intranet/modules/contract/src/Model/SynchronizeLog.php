<?php

namespace Rikkei\Contract\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Employee;

class SynchronizeLog extends CoreModel
{

    protected $table = 'synchronize_log';

    /*
     * The attributes that are mass assignable.
     */

    protected $primaryKey = 'id';
    protected $fillable = [
        'employee_id',
        'employee_old',
        'employee_new',
        'created_at',
        'updated_at',
    ];

    public function getUserSynchronize()
    {
        return Employee::getEmpById($this->employee_id);
    }

}
