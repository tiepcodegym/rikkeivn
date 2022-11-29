<?php

namespace Rikkei\Vote\Model;

use Rikkei\Core\Model\CoreModel;
use Rikkei\Team\View\Permission;
use Rikkei\Team\Model\Team;
use Rikkei\Team\Model\TeamMember;
use Rikkei\Team\View\Config;
use Rikkei\Core\View\Form;
use Rikkei\Vote\View\VoteConst;

class Vote extends CoreModel {
    
    protected $table = 'votes';
    protected $dates = ['deleted_at', 'nominate_start_at', 'nominate_end_at', 'vote_start_at', 'vote_end_at'];
    protected $fillable = ['title', 'slug', 'content', 'status', 'nominate_start_at', 'nominate_end_at', 'vote_start_at', 'vote_end_at', 
                            'nominee_max', 'vote_max', 'created_by'];
    
    use \Illuminate\Database\Eloquent\SoftDeletes;
    
    /**
     * get votes data
     * @return type
     */
    public static function getGridData() {
        $currentUser = Permission::getInstance()->getEmployee();
        
        $pager = Config::getPagerData();
        $collection = self::select('*');
        
        if (Permission::getInstance()->isScopeCompany()) {
            //do nothing
        } else if (Permission::getInstance()->isScopeTeam()) {
            $teamTbl = Team::getTableName();
            $teamMbTbl = TeamMember::getTableName();
            //get employee ids of team
            $employeeIds = TeamMember::select('employee_id')
                    ->whereRaw('team_id IN (SELECT team.id '
                    . 'FROM '. $teamTbl .' AS team '
                    . 'INNER JOIN '. $teamMbTbl .' AS tmb ON team.id = tmb.team_id '
                    . 'AND tmb.employee_id = '. $currentUser->id .' '
                    . 'GROUP BY team.id)')
                    ->groupBy('employee_id')
                    ->lists('employee_id')
                    ->toArray();
            
            $collection->where(function ($query) use ($employeeIds) {
                $query->whereNotNull('created_by')
                    ->whereIn('created_by', $employeeIds)
                    ->orWhereNull('created_by');
            });
        } else if (Permission::getInstance ()->isScopeSelf ()) {
            $collection->where(function ($query) use ($currentUser) {
                $query->whereNotNull('created_by')
                    ->where('created_by', $currentUser->id)
                    ->orWhereNull('created_by');
            });
        }
        self::filterGrid($collection);
        if (Form::getFilterPagerData('order')) {
            $collection->orderBy($pager['order'], $pager['dir']);
        } else {
            $collection->orderBy('created_at', 'desc');
        }
        $collection->orderBy($pager['order'], $pager['dir']);
        self::pagerCollection($collection, $pager['limit'], $pager['page']);

        return $collection;
    }
    
    /**
     * find vote by slug
     * @param type $slug
     * @return type
     */
    public static function findBySlug($slug) {
        return self::where('slug', $slug)->first();
    }
    
    /**
     * set nominate_start_at before save/update as attribute
     * @param type $value
     */
    public function setNominateStartAtAttribute($value) {
        if (in_array($value, ['', '0000-00-00 00:00:00'])) {
            $value = null;
        }
        $this->attributes['nominate_start_at'] = $value;
    }
    
    /**
     * set nominate_end_at before save/update as attribute
     * @param type $value
     */
    public function setNominateEndAtAttribute($value) {
        if (in_array($value, ['', '0000-00-00 00:00:00'])) {
            $value = null;
        }
        $this->attributes['nominate_end_at'] = $value;
    }
    
    /**
     * check unique slug before save, update
     * @param string $value
     */
    public function setSlugAttribute ($value) {
        $listCount = self::where('slug', 'like', $value . '%');
        if ($this->id) {
            $listCount->where('id', '!=', $this->id);
        }
        $listCount = $listCount->get()->count();
        if ($listCount > 0) {
            $value .= '-' . $listCount;
        }
        $this->attributes['slug'] = $value;
    }
    
    /**
     * set nominee_max before save, update
     * @param type $value
     */
    public function setNomineeMaxAttribute ($value) {
        if (!is_numeric($value)) {
            $value = null;
        }
        $this->attributes['nominee_max'] = $value;
    }
    
    /**
     * set vote_max before save, update
     * @param type $value
     */
    public function setVoteMaxAttribute ($value) {
        if (!is_numeric($value)) {
            $value = null;
        }
        $this->attributes['vote_max'] = $value;
    }
    
    public function getStatusLabel () {
        $statuses = VoteConst::getVoteStatuses();
        if (isset($statuses[$this->status])) {
            return $statuses[$this->status];
        }
        return $this->status;
    }
    
}

