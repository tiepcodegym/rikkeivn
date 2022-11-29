<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class GratefulEmployeeOnsite extends CoreModel
{
    use SoftDeletes;

    protected $table = 'grateful_employee_onsite';

    protected $fillable = [
        'employee_id',
        'number',
        'date_grateful',
        'created_by',
        'note',
        'deleted_at',
    ];

    /**
     * find employee by employee id and number year
     *
     * @param  int $empId
     * @param  int $year
     * @return collection|null
     */
    public function findEmployee($empId, $year)
    {
        return static::where('employee_id', $empId)
            ->where('number', $year)
            ->first();
    }

    public function getEmployeeGratefulEmployee($empIds, $years)
    {
        return static::whereIn('employee_id', $empIds)
            ->whereIn('number', $years)
            ->get();
    }
}
