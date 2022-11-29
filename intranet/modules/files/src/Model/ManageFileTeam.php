<?php

namespace Rikkei\Files\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;
use DB;

class ManageFileTeam extends CoreModel
{
    protected $table = 'manage_file_team';

    public static function getGroupTeam($registerId)
    {
        return self::where('manage_file_team.register_id', $registerId)
        ->join('employees', 'manage_file_team.relater_id', '=', 'employees.id')
        ->join('manage_file_text', 'manage_file_team.register_id', '=', 'manage_file_text.id')
        ->select('employees.name', 'employees.id')->get();
    }
}
