<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;

class EmployeeWorkPlace extends CoreModel
{
    protected $table = 'employee_work_places';
    protected $fillable = ['employee_code', 'code_place', 'email', 'start_date', 'end_date'];
}
