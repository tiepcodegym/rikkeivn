<?php
namespace Rikkei\Resource\Model;

use Rikkei\Resource\View\getOptions;
use Rikkei\Core\Model\CoreModel;
use DB;
use Rikkei\Team\Model\Team;

class RequestTeam extends CoreModel
{
    
    protected $table = 'request_team';

    protected $appends = ['actual_number_resource'];


    /**
     *  store this object
     * @var object
     */
    protected static $instance;

    public function getActualNumberResourceAttribute()
    {
        return Candidate::where('request_id', $this->request_id)
            ->where('position_apply', $this->position_apply)
            ->where('team_id',  $this->team_id)
            ->whereIn('status', [getOptions::END, getOptions::WORKING])
            ->count();
    }

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new static;
        }
        return self::$instance;
    }
    
    public static function removeOldTeamOfRequest($requestId) {
        self::where('request_id', $requestId)->delete();
    }
    
    public static function insertData($data) {
        self::insert($data);
    }
    
    public static function getTeamByRequest($requestId) {
        return self::select('teams.name as team_name', DB::raw("team_id, "
                . "group_concat(concat(position_apply,',', number_resource) SEPARATOR ';') as col, "
                . "group_concat(position_apply SEPARATOR ',') as pos_selected, "
                . "(select group_concat(DISTINCT team_id SEPARATOR ',') from request_team where request_id = $requestId ) as team_selected"))
            ->join('teams', 'teams.id', '=', 'request_team.team_id')
            ->where('request_id', $requestId)
            ->groupBy('team_id')
            ->get();
    }
    
    public static function getTeamSelectedLabel($requestId) {
        $teamTableName = Team::getTableName();
        return self::join("{$teamTableName}", "{$teamTableName}.id", "=", "request_team.team_id")
                    ->where('request_id', $requestId)
                    ->select(DB::raw("group_concat(distinct {$teamTableName}.name SEPARATOR ', ') as team_selected"))
                    ->first();
    }
    
    /**
     * Get teams by request
     * @param int $requestId
     * @return RequestTeam collection
     */
    public static function getRequestTeam($requestId) {
        return self::where('request_id', $requestId)
                    ->select('*')
                    ->get();
    }
    
    /**
     * Get positions by team of request
     * @param int $requestId
     * @param int $teamId
     * @return RequestTeam collection
     */
    public static function getPositionByTeam($requestId, $teamId) {
        return self::where('request_id', $requestId)
                    ->where('team_id', $teamId)
                    ->select('*')
                    ->get();
    }
    
    /**
     * Get all team by requests
     * Columns select: team_id
     * 
     * @param array $requestIds
     * @return RequestTeam collection 
     */
    public static function getTeamsByRequests($requestIds) {
        $teamIds = [];
        if (is_array($requestIds)) {
            $result = self::whereIn('request_id', $requestIds)
                        ->selectRaw('DISTINCT(team_id)')
                        ->get();
            foreach ($result as $item) {
                $teamIds[] = $item->team_id;
            }
        }
        return $teamIds;
    }
    
    /**
     * Get all team by request
     * Columns select: team_id
     * 
     * @param int $requestId
     * @return RequestTeam collection 
     */
    public static function getTeamsByRequest($requestId) {
        $teamIds = [];
        $result = self::where('request_id', $requestId)
                    ->selectRaw('DISTINCT(team_id)')
                    ->get();
        foreach ($result as $item) {
            $teamIds[] = $item->team_id;
        }
        
        return $teamIds;
    }
   
    /**
     * Get all postions by teams and requests
     * Columns select: position_apply
     * 
     * @param array $requestIds
     * @param array $teamIds
     * @return RequestTeam collection 
     */
    public static function getPosesByTeamsAndRequests($requestIds, $teamIds) {
        $posIds = [];
        if (is_array($requestIds) && is_array($teamIds)) {
            $result = self::whereIn('request_id', $requestIds)
                        ->whereIn('team_id', $teamIds)
                        ->selectRaw('DISTINCT(position_apply)')
                        ->get();
            foreach ($result as $item) {
                $posIds[] = $item->position_apply;
            }
        }
        return $posIds;
    }
    
    /**
     * Get all postions by team and request
     * Columns select: position_apply
     * 
     * @param int $requestId
     * @param int $teamId
     * @return RequestTeam collection 
     */
    public static function getPosesByTeamAndRequest($requestId, $teamId) {
        $posIds = [];
        $result = self::where('request_id', $requestId)
                    ->where('team_id', $teamId)
                    ->selectRaw('DISTINCT(position_apply)')
                    ->get();
        foreach ($result as $item) {
            $posIds[] = $item->position_apply;
        }
        
        return $posIds;
    }
}