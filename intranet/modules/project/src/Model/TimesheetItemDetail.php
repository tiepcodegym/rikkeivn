<?php

namespace Rikkei\Project\Model;

use Rikkei\Core\Model\CoreModel;

class TimesheetItemDetail extends CoreModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'timesheet_item_details';
    protected $fillable = [
        'timesheet_item_id',
        'date',
        'checkin',
        'checkout',
        'break_time',
        'working_hour',
        'ot_hour',
        'overnight',
        'holiday',
        'note'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
