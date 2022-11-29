<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;

class WorkPlace extends CoreModel
{
    protected $table = 'manage_work_places';
    protected $fillable = ['name', 'code', 'is_surcharge', 'is_status'];
}
