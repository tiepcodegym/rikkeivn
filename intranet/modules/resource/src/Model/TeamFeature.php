<?php
namespace Rikkei\Resource\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Config;
use Rikkei\Team\Model\Team;
use Rikkei\Core\View\Form;

class TeamFeature extends CoreModel
{
    
    protected $table = 'teams_feature';
    
    protected $fillable = ['name', 'sort_order', 'team_alias', 'is_soft_dev'];
    
    /**
     * get all teams
     * @return type
     */
    public static function getList() {
        return self::select('id', 'name', 'sort_order')
                ->orderBy('sort_order', 'asc')
                ->get();
    }
    
    /**
     * show grid teams
     * @return type
     */
    public static function getGridData() {
        $tblTeam = Team::getTableName();
        $tblFeature = self::getTableName();
        $pager = Config::getPagerData();
        $collection = self::from($tblFeature . ' as team_ft')
                ->select('team_ft.id','team_ft.name', 'team.name as alias_name', 'team_ft.sort_order')
                ->leftJoin($tblTeam . ' as team', 'team_ft.team_alias', '=', 'team.id');
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('team_ft.sort_order', 'asc');
        }
        $collection->orderBy('team_ft.created_at', 'desc')
                ->groupBy('team_ft.id');
        self::filterGrid($collection);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);
        return $collection;
    }
    
    /**
     * list team has mapped
     * @return type
     */
    public static function getTeamAliasIds($excerpt = null)
    {
        $ids = self::whereNotNull('team_alias');
        if ($excerpt) {
            $ids->whereNotIn('team_alias', (array) $excerpt);
        }
        return $ids->lists('team_alias')
                ->toArray();
    }
}