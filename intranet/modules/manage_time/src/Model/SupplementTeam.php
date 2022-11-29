<?php

namespace Rikkei\ManageTime\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\Model\Team;

class SupplementTeam extends CoreModel
{
    protected $table = 'supplement_teams';

    public static function getTeams($registerId)
    {
    	$registerTable = SupplementRegister::getTableName();
        $registerTeamTable = self::getTableName();
        $teamTable = Team::getTableName();

        return self::join("{$registerTable}", "{$registerTable}.id", "=", "{$registerTeamTable}.register_id")
        	->join("{$teamTable}", "{$teamTable}.id", "=", "{$registerTeamTable}.team_id")
            ->where("{$registerTeamTable}.register_id", $registerId)
            ->select("{$teamTable}.id as team_id", "{$teamTable}.name as team_name")
            ->get();
    }
}