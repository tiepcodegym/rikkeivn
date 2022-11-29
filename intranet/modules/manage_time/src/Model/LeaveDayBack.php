<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;

class LeaveDayBack extends CoreModel
{
    protected $table = 'leave_day_back';
    public $timestamps = true;

    protected $fillable = [
        "day_last_year",
        "day_last_transfer",
        "day_current_year",
        "day_seniority",
        "day_ot",
        "day_used",
        "note"
    ];
}
