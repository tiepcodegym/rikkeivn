<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class TimesheetItem extends CoreModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'timesheet_items';
    protected $fillable = [
        'timesheet_id',
        'name',
        'roles',
        'level',
        'employee_id',
        'line_item_id',
        'day_of_leave',
        'division_id',
        'working_from',
        'working_to',
        'min_hour',
        'max_hour',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * Relationship one-many with TimesheetItemDetail
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details()
    {
        return $this->hasMany('Rikkei\Project\Model\TimesheetItemDetail');
    }
}
